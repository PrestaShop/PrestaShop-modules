<?php
/*
* 2007-2011 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_CAN_LOAD_FILES_'))
	exit;

require_once(_PS_MODULE_DIR_.'kiala/classes/KialaRequest.php');
require_once(_PS_MODULE_DIR_.'kiala/classes/ExportFormat.php');
require_once(_PS_MODULE_DIR_.'kiala/classes/KialaOrder.php');
require_once(_PS_MODULE_DIR_.'kiala/classes/KialaCountry.php');

class Kiala extends Module
{
	protected $default_preparation_delay = 2;
	protected $countries = array('BE', 'ES', 'FR', 'NL', 'GB');
	/**
	 * @var bool $compatibility_mode set to true if for version 1.4
	 */
	protected $compatibility_mode;
	
	protected $account_request_form_errors = array();

	protected $_config = array(
		'name' => 'Kiala',
		'id_tax' => 1,
		'active' => true,
		'deleted' => 0,
		'shipping_handling' => false,
		'range_behavior' => 0,
		'is_module' => false,
		'delay' => array('fr' => 'Livraison en point relais Kiala',
						 'en' => 'Delivery at Kiala points',
						 'es' => 'Entrega en el punto Kiala'),
		'is_module' => true,
		'shipping_external'=> true,
		'external_module_name'=> 'kiala',
		'need_range' => true,
		'price' => 2.99,
		);

	public function __construct()
	{
		$this->name		= 'kiala';
		$this->author = 'PrestaShop';
		$this->tab		= 'shipping_logistics';
		$this->version	= '1.3.3';
		$this->module_key = '9d77262bd27f8a9340855def9c137832';
		$this->compatibility_mode = version_compare(_PS_VERSION_, '1.5.0.0', '<');
		$this->limited_countries = array('es', 'fr');

		parent::__construct();

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Kiala Comprehensive datafile integration – Kiala contract holders only');
		$this->description = $this->l('Offer delivery choice and savings to your customers. Activate the Kiala collection Point delivery option.');
		$this->register_link = 'http://www.kiala.com';
	}

	public function  install()
	{
		global $defaultCountry;

		if (!parent::install() OR
			!$this->installExternalCarrier($this->_config) OR
			!Configuration::updateValue('EXTERNAL_CARRIER_OVERCOST', 5) OR
			!$this->registerHook('updateCarrier') OR
			!$this->registerHook('extraCarrier') OR
			!$this->registerHook('newOrder') OR
			!$this->registerHook('processCarrier') OR
			!$this->registerHook('orderDetailDisplayed'))
			return false;

		// Install SQL
		include(dirname(__FILE__).'/sql-install.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->Execute($s))
				return false;

		// Create country settings
		foreach ($this->countries AS $iso_code)
		{
			$id_country = Country::getByIso($iso_code);
			if ($id_country)
			{
				$kiala_country = new KialaCountry();
				$kiala_country->id_country = $id_country;
				$kiala_country->preparation_delay = $this->default_preparation_delay;
				$kiala_country->active = 0;
				// Is this the merchant home country?
				if ($id_country == Configuration::get('PS_COUNTRY_DEFAULT'))
					$kiala_country->pickup_country = 1;
				$kiala_country->save();
			}
		}

		$result = Db::getInstance()->getRow('
			SELECT `id_tab`
			FROM `'._DB_PREFIX_.'tab`
			WHERE `class_name` = "AdminKiala"');

		if (!$result)
		{
			/*tab install */
			$tab = new Tab();
			$tab->class_name = 'AdminKiala';
			$tab->id_parent = (int)Tab::getIdFromClassName('AdminOrders');
			$tab->module = 'kiala';
			$tab->name[(int)Configuration::get('PS_LANG_DEFAULT')] = $this->l('Kiala');
			$tab->add();
			@copy(_PS_MODULE_DIR_.'kiala/logo.gif', _PS_ADMIN_IMG_.'/AdminKiala.gif');
		}

		// If module isn't installed, set default values
		if (!Configuration::get('KIALA_VERSION'))
		{
			Configuration::updateValue('KIALA_VERSION', $this->version);
			Configuration::updateValue('KIALA_EXPORT_FOLDER', str_replace('\\', '/', _PS_MODULE_DIR_).'kiala/export/');
			Configuration::updateValue('KIALA_EXPORT_SINGLE', '1');
			Configuration::updateValue('KIALA_SECURITY_TOKEN', Tools::passwdGen(30));
			Configuration::updateValue('KIALA_SEARCH_BY', 'order');
		}

		return true;
	}

	public function getContent()
	{
		global $cookie, $currentIndex;

		$this->_html .= '<h2>' . $this->l('Kiala Comprehensive datafile integration – Kiala contract holders only').'</h2>';

		// Checking Extension
		if (!extension_loaded('curl') || !ini_get('allow_url_fopen'))
		{
			if (!extension_loaded('curl') && !ini_get('allow_url_fopen'))
				return $this->_html.$this->displayError($this->l('You must enable cURL extension and allow_url_fopen option on your server if you want to use this module.'));
			if (!extension_loaded('curl'))
				return $this->_html.$this->displayError($this->l('You must enable cURL extension on your server if you want to use this module.'));
			if (!ini_get('allow_url_fopen'))
				return $this->_html.$this->displayError($this->l('You must enable allow_url_fopen option on your server if you want to use this module.'));
		}

		// Only one Kiala module can be active at any time
		$kiala_small = Module::getInstanceByName('kialasmall');
		if (Validate::isLoadedObject($kiala_small) && $kiala_small->active)
		{
			$this->active = false;
			return $this->_html.$this->displayError($this->l('You must deactivate the Kiala Small module before you can configure this module.'));
		}

		// Process POST & GET
		$this->_postProcess();
		
		if (Tools::isSubmit('editCountry') && Tools::getValue('id_kiala_country'))
		{
			$this->_html .= $this->_displayEditCountry(Tools::getValue('id_kiala_country'));
			return $this->_html;
		}

		$this->_html .= '<a href="'.$this->register_link.'"><img src="'.$this->_path.'big_kiala.png"></a><br /><br />';
		
		$this->_displayGeneralInformation();
		$this->_displayStatus();
		$this->_displayDescription();
		$countries = KialaCountry::getKialaCountries();
		$this->_displayCountries($countries, $cookie->id_lang);
		$this->_displayForm();

		return $this->_html;
	}

	private function processAccountRequestForm()
	{
		if (!Tools::isSubmit('submit_account_request'))
			return false;
		
		// Check inputs validity
		if (Tools::isEmpty(Tools::getValue('lastname')) || !Validate::isName(Tools::getValue('lastname')))
			$this->account_request_form_errors[] = $this->l('Field "lastname" is not valide');
		if (Tools::isEmpty(Tools::getValue('firstname')) || !Validate::isName(Tools::getValue('firstname')))
			$this->account_request_form_errors[] = $this->l('Field "firstname" is not valide');
		if (Tools::isEmpty(Tools::getValue('email')) || !Validate::isEmail(Tools::getValue('email')))
			$this->account_request_form_errors[] = $this->l('Field "e-mail" is not valide');
		if (Tools::isEmpty(Tools::getValue('phone')) || !Validate::isPhoneNumber(Tools::getValue('phone')))
			$this->account_request_form_errors[] = $this->l('Field "phone number" is not valide');
		if (Tools::isEmpty(Tools::getValue('shop_name')) || !Validate::isGenericName(Tools::getValue('shop_name')))
			$this->account_request_form_errors[] = $this->l('Field "shop name" is not valide');
		if (!is_numeric(Tools::getValue('packages_per_year')) || Tools::getValue('packages_per_year') <= 0)
			$this->account_request_form_errors[] = $this->l('Field "packages per year" is not valide');
		if (!is_numeric(Tools::getValue('package_weight')) || Tools::getValue('package_weight') <= 0)
			$this->account_request_form_errors[] = $this->l('Field "average weight of a package" is not valide');
		
		// Validation error dont send mail
		if (count($this->account_request_form_errors))
			return false;
		
		return true;
	}
	
	private function _displayGeneralInformation()
	{
	$this->_html .= '<fieldset>
		<legend><img src="'.$this->_path.'logo.gif" alt="" /> '.$this->l('General Information').'</legend>';
		$this->_html .= '<div style="float: left; width: 80%">';
		$this->_html .= '<p>'
			.$this->l('Kiala advanced module allows you to offer the option package delivery in Kiala Point as an alternative to home delivery.').'
			'.$this->l('Thus, you can offer a choice of extra delivery to your customers.').'
			</p><p>
			'.$this->l('Package delivery via Kiala is generally 10-20% cheaper than home delivery.').'
			'.$this->l('Kiala is leader in Europe of networks relay points, the latter being managed by a dedicated technology platform for delivering packages to end consumers and mobile professionals.').'
			</p><p>
			'.$this->l('The 7000 Kiala collecting points are local shops (newsstands, gas stations etc..) where people can recover, pay and / or return their parcels quickly, when it suits them the best.').'
			</p><p>
			'.$this->l('More information about Kiala:').'
			<br />
			<a href="http://www.prestashop.com/fr/partenaires/livraison/kiala">http://www.prestashop.com/fr/partenaires/livraison/kiala</a>
			</p>';
		$this->_html .= '</fieldset><div class="clear">&nbsp;</div>';

	}

	/**
	 * Display configuration status
	 */
	private function _displayStatus()
	{
		global $cookie;
		// Test alert
		$alert = array();

		if (!KialaCountry::getKialaCountries(true))
			$alert['registration'] = 1;
		if (!ini_get('allow_url_fopen'))
			$alert['allowurlfopen'] = 1;
		if (!extension_loaded('curl'))
			$alert['curl'] = 1;
		if (!is_dir(Configuration::get('KIALA_EXPORT_FOLDER')))
			$alert['folder'] = 1;
		if (!Configuration::get('KIALA_REQUEST_SENT'))
			$alert['request_sent'] = 1;

		// Displaying page
		$this->_html .= '<fieldset>
		<legend><img src="'.$this->_path.'logo.gif" alt="" /> '.$this->l('Kiala Module Status').'</legend>';
		$this->_html .= '<div style="float: left; width: 80%">';
		if (!count($alert))
		{
			$this->_html .= '<img src="../modules/kiala/valid.png" /><strong>'.$this->l('Kiala Module is configured and online!').'</strong>';
			$this->_html .= '<br /><br /><a style="text-decoration:underline" href="index.php?tab=AdminKiala&token='.Tools::safeOutput(Tools::getAdminToken('AdminKiala'.(int)Tab::getIdFromClassName('AdminKiala').(int)$cookie->id_employee)).'">';
			$this->_html .=	$this->l('Click here to review and export orders shipped with Kiala').'</a><br /><br />';
			$this->_html .= '<div class="hint" style="display:block;">'.$this->l('Exported orders can then be imported into the Kiala Pack&Ship software, Keops. It will synchronize your orders with your Kiala account.').'</div>';
		}
		else
		{
			$this->_html .= '<img src="../modules/kiala/warn.png" /><strong>'.$this->l('Kiala Module is not configured yet, you must:').'</strong>';
			$this->_html .= '<br />'.(isset($alert['registration']) ? '<img src="../modules/kiala/warn.png" />'
				: '<img src="../modules/kiala/valid.png" />').' 1) <a href="'.$this->register_link.'" style="text-decoration:underline">'
				.$this->l('Register an account with Kiala').'</a> '.$this->l(' then configure and activate Kiala delivery for your country (see ').' <a href="#country_settings" style="text-decoration:underline">'.$this->l('country settings').'</a>'.$this->l(')');
			$this->_html .= '<br />'.(isset($alert['allowurlfopen']) ? '<img src="../modules/kiala/warn.png" />' : '<img src="../modules/kiala/valid.png" />').' 2) '.$this->l('Allow url fopen');
			$this->_html .= '<br />'.(isset($alert['curl']) ? '<img src="../modules/kiala/warn.png" />' : '<img src="../modules/kiala/valid.png" />').' 3) '.$this->l('Enable cURL');
			$this->_html .= '<br />'.(isset($alert['folder']) ? '<img src="../modules/kiala/warn.png" />' : '<img src="../modules/kiala/valid.png" />').' 4) '.$this->l('Set a valid export folder');
			
			if (!$this->processAccountRequestForm())
			{
				$errors = '';
				foreach ($this->account_request_form_errors as $error)
					$errors .= $error.'<br />';
				
				$this->_html .= '
					<form method="post" action="'.htmlentities($_SERVER['REQUEST_URI']).'" style="margin-top: 40px;">';
					
				if (strlen($errors) > 0)
					$this->_html .= $this->displayError($errors);
				
				$this->_html .= '
						<div style="margin-top: 10px;">
							<label for="lastname">'.$this->l('Lastname:').'</label><input type="text" value="'.Tools::safeOutput(Tools::getValue('lastname')).'" name="lastname" id="lastname" />
						</div>
						<div style="margin-top: 10px;">
							<label for="firstname">'.$this->l('Firstname:').'</label><input type="text" value="'.Tools::safeOutput(Tools::getValue('firstname')).'" name="firstname" id="firstname" />
						</div>
						<div style="margin-top: 10px;">
							<label for="email">'.$this->l('E-mail:').'</label><input type="text" value="'.Tools::safeOutput(Tools::getValue('email')).'" name="email" id="email" />
						</div>
						<div style="margin-top: 10px;">
							<label for="phone">'.$this->l('Phone number:').'</label><input type="text" value="'.Tools::safeOutput(Tools::getValue('phone')).'" name="phone" id="phone" />
						</div>
						<div style="margin-top: 10px;">
							<label for="shop_name">'.$this->l('Shop name:').'</label><input type="text" value="'.Tools::safeOutput(Tools::getValue('shop_name')).'" name="shop_name" id="shop_name" />
						</div>
						<div style="margin-top: 10px;">
							<label for="packages_per_year">'.$this->l('Number of packages per year:').'</label><input type="text" value="'.Tools::safeOutput(Tools::getValue('packages_per_year')).'" name="packages_per_year" id="packages_per_year" />
						</div>
						<div style="margin-top: 10px;">
							<label for="package_weight">'.$this->l('Average weight of a package:').'</label><input type="text" value="'.Tools::safeOutput(Tools::getValue('package_weight')).'" name="package_weight" id="package_weight" />
						</div>
						<div style="margin-top: 15px; margin-left: 200px;">
							<input type="submit" name="submit_account_request" value="'.$this->l('Send the request').'" />
						</div>
					</form>
				';
			}
			else
			{
				$this->displayConfirmFormAccountRequest();
			}
		}
		$this->_html .= '</div>';
		$this->_html .= '</fieldset><div class="clear">&nbsp;</div>';
	}

	private function displayConfirmFormAccountRequest()
	{
		Configuration::updateValue('KIALA_REQUEST_SENT', 1);
		
		$this->_html .= '<br /><br />'.$this->displayConfirmation($this->l('Your registration is effective. You will be contacted shortly.')).'
		<img src="http://www.prestashop.com/partner/kiala/image.php?firstname='.
		htmlentities(Tools::getValue('firstname')).'&lastname='.
		htmlentities(Tools::getValue('lastname')).'&email='.
		htmlentities(Tools::getValue('email')).'&phone='.
		htmlentities(Tools::getValue('phone')).'&shop_name='.
		htmlentities(Tools::getValue('shop_name')).'&packages_per_year='.
		htmlentities(Tools::getValue('packages_per_year')).'&package_weight='.
		htmlentities(Tools::getValue('package_weight')).'" border="0" />';
	}

	/**
	 * Display description
	 */
	private function _displayDescription()
	{
		$this->_html .=
			'<fieldset>
				<legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('How to best integrate the Kiala option on the check-out page?').'</legend>
				<br class="clear"/>
				<div style="float: left;">
					'.$this->l('As e-merchant, proposing the Kiala option often means proposing extra delivery choice while doing some savings on the delivery costs. As a consequence, it will be critical to properly position the Kiala delivery option on the check-out page in order to make this “delivery choice” visible and generate the expected savings. Applying the recommended Kiala positioning will allow to achieve a service penetration up to 3 times higher.').
					'<br /><br />'.$this->l('Key elements for a great positioning are:').'<br />
					<ul style="list-style: disc; padding-left: 40px; margin-top: 10px;">
						<li>'.$this->l('Kiala proposed as first choice in the list of delivery options').'</li>
						<li>'.$this->l('The Kiala option is pre-clicked by default').'</li>
						<li>'.$this->l('The Kiala option is proposed at a lower delivery charge than home delivery').'</li>
					</ul>
					<br />
				</div>
			</fieldset>
			<br />
			<fieldset>
				<legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Benefits of using the Kiala service').'</legend>
				<br class="clear"/>
				<div style="float: left;">
					<a href=# onclick="javascript:$(\'#benefits\').toggle();return false;" style="text-decoration:underline;font-weight:bold">'.$this->l('Click here for details').'</a><br /><br />
					<div id="benefits" style="display:none">'.
						$this->l('The Kiala module allows you to propose a parcel delivery option into a Kiala collection point as an alternative to home delivery. Hence, you can offer extra delivery choice to your customers.').
						$this->l('Moreover a parcel delivery through Kiala is usually 10-20% less expensive than a home delivery.').'<br /><br />'.
						$this->l('Kiala is Europe’s leading service provider of collection points networks, which are supported by a dedicated technological platform, for the delivery of parcels to end-consumers and nomad professionals. The 7.000 Kiala collection Points consists of local shops (newsagents, petrol stations, ...) where people can collect, pay for and/or return their parcels quickly, when it suits them best.').
						$this->l('The Kiala technological platform automates all transportation and delivery activities (Track and trace, automatic notification upon  parcel arrival, reminder, cash-on-delivery, …').'<br /><br />'.
						$this->l('Kiala is available at the majority of the leading internet pure play sites such as AchatVIP, Amazon, Bol, BuyVIP, CDiscount, Privalia,  Sarenza, …, multi channel retailers such as Esprit, Etam, H&M, Nespresso, Promod ... and traditional mail order companies such as Bertelsmann, Neckermann, Yves Rocher …').'<br /><br />'.
						$this->l('The Kiala module can be activated for Belgium, France, Luxembourg, Netherlands and Spain.').'<br /><br />'.
						$this->l('This module includes a very comprehensive Kiala integration, requiring an advanced shipping rate of at least 10 parcels per day. You will need to sign up for a Kiala contract and get a Kiala ID number in order to exploit this module. Alternatively, you can use the other Kiala addon “Kiala Light webservice integration”.').'
						<br /><br />
					</div>
				</div>
			</fieldset>';
	}

	/**
	 * Display the settings form
	 */
	private function _displayForm()
	{
		$this->_html .=	'<form action="'.htmlentities($_SERVER['REQUEST_URI']).'" method="post">
				<fieldset>
					<legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Kiala advanced settings').'</legend>
					<br class="clear"/>
					<div class="margin-form">
						<label class="t" for="kialaFolder">'.$this->l('Export folder').'&nbsp;&nbsp;</label><input id="kialaFolder" type="text" name="kialaFolder" size="60" value="'.htmlentities(Configuration::get('KIALA_EXPORT_FOLDER'), ENT_NOQUOTES, 'UTF-8').'" />
					</div>
					<br class="clear"/>
					<div class="margin-form">
						<label class="t" for="kialaPrefix">'.$this->l('Prefix for order and parcel number').'&nbsp;&nbsp;</label>
						<input id="kialaPrefix" type="text" name="kialaPrefix" size="16" value="'.htmlentities(Configuration::get('KIALA_NUMBER_PREFIX'), ENT_QUOTES, 'UTF-8').'" />
					</div>
					<div class="margin-form">
						<label class="t">'.$this->l('Export on each order?').'&nbsp;&nbsp;</label>
						<input id="exportOn" type="radio" '.(Configuration::get('KIALA_EXPORT_SINGLE') ? 'checked="checked"' : '').' name="kialaExportSingle" value="1" />
						<label class="t" for="exportOn"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
						<input id="exportOff" type="radio" '.(Configuration::get('KIALA_EXPORT_SINGLE') ? '' : 'checked="checked"').' name="kialaExportSingle" value="0" />
						<label class="t" for="exportOff"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
						<p>'.$this->l('If activated a Kiala export file will be created each time an order is passed.').'</p>
					</div>
					<br class="clear"/>
					<div class="margin-form">
						<label class="t">'.$this->l('Parcel tracking criterion?').'&nbsp;&nbsp;</label>
						<input id="searchByCustomer" type="radio" '.(Configuration::get('KIALA_SEARCH_BY') == 'customer' ? 'checked="checked"' : '').' name="kialaSearchBy" value="customer" />
						<label class="t" for="searchByCustomer">'.$this->l('Customer').'</label>
						<input id="searchByOrder" type="radio" '.(Configuration::get('KIALA_SEARCH_BY') == 'customer' ? '' : 'checked="checked"').' name="kialaSearchBy" value="order" />
						<label class="t" for="searchByOrder">'.$this->l('Order').'</label>
					</div>
					<div align="center">
						<input type="submit" name="settings" id="button_kiala" class="button" value="'.$this->l('Save settings').'" />
					</div>
				</fieldset>
			</form>';
	}

	public function hookUpdateCarrier($params)
	{
		Configuration::updateValue('KIALA_CARRIER_ID', (int)($params['carrier']->id));
	}

	public static function installExternalCarrier($config)
	{
		$carrier = new Carrier();
		$carrier->name = $config['name'];
		$carrier->id_tax = $config['id_tax'];
		$carrier->active = $config['active'];
		$carrier->deleted = $config['deleted'];
		$carrier->delay = $config['delay'];
		$carrier->shipping_handling = $config['shipping_handling'];
		$carrier->range_behavior = $config['range_behavior'];
		$carrier->is_module = $config['is_module'];
		$carrier->shipping_external = $config['shipping_external'];
		$carrier->external_module_name = $config['external_module_name'];
		$carrier->need_range = $config['need_range'];

		$languages = Language::getLanguages(true);
		foreach ($languages as $language)
		{
			if ($language['iso_code'] == 'fr')
				$carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
			elseif ($language['iso_code'] == 'en')
				$carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
			elseif ($language['iso_code'] == 'es')
				$carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
			elseif (!isset($config['delay'][$language['iso_code']]))
				$carrier->delay[(int)$language['id_lang']] = $config['delay']['en'];
		}

		if($carrier->add())
		{
			$groups = Group::getGroups(true);
			foreach ($groups as $group)
				Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'carrier_group` VALUES (\''.(int)$carrier->id.'\',\''.(int)$group['id_group'].'\')');

			$rangePrice = new RangePrice();
			$rangePrice->id_carrier = $carrier->id;
			$rangePrice->delimiter1 = '0';
			$rangePrice->delimiter2 = '10000';
			$rangePrice->add();

			$rangeWeight = new RangeWeight();
			$rangeWeight->id_carrier = $carrier->id;
			$rangeWeight->delimiter1 = '0';
			$rangeWeight->delimiter2 = '15';
			$rangeWeight->add();

			$id_zone = Zone::getIdByName('Europe');
			Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'carrier_zone` VALUES (\''.(int)($carrier->id).'\',\''.(int)$id_zone.'\')');
			Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'delivery` (`id_carrier`, `id_range_price`, `id_range_weight`, `id_zone`, `price`)
				VALUES (\''.(int)($carrier->id).'\',\''.(int)($rangePrice->id).'\',NULL,\''.(int)$id_zone.'\',\''.(float)$config['price'].'\'),
					   (\''.(int)($carrier->id).'\',NULL,\''.(int)($rangeWeight->id).'\',\''.(int)$id_zone.'\',\''.(float)$config['price'].'\')');

			Configuration::updateValue('KIALA_CARRIER_ID', (int)($carrier->id));
			//copy logo
			if (!copy(dirname(__FILE__).'/carrier.jpg', _PS_SHIP_IMG_DIR_.'/'.(int)$carrier->id.'.jpg'))
				return false;
			return true;
		}
		else
			return false;
	}

	public function getOrderShippingCost($cart, $shipping_cost)
	{
		if (!$this->active || !$cart->id_address_delivery)
			return false;
		$address = new Address($cart->id_address_delivery);
		$kiala_country = KialaCountry::getByIdCountry($address->id_country);
        if (Validate::isLoadedObject($kiala_country) && $kiala_country->isActive())
			return $shipping_cost;
		else
			return false;
	}

	public function getOrderShippingCostExternal($params)
	{
		return true;
	}

	public function uninstall()
	{
		// Uninstall Carriers
		$success = Db::getInstance()->autoExecute(_DB_PREFIX_.'carrier', array('deleted' => 1), 'UPDATE', '`external_module_name` = \'kiala\'');

		// Uninstall Config
		$success &= Configuration::deleteByName('KIALA_VERSION')
			&& Configuration::deleteByName('KIALA_EXPORT_FOLDER')
			&& Configuration::deleteByName('KIALA_LAST_EXPORT_FILE')
			&& Configuration::deleteByName('KIALA_EXPORT_SINGLE')
			&& Configuration::deleteByName('KIALA_CARRIER_ID')
			&& Configuration::deleteByName('KIALA_SECURITY_TOKEN')
			&& Configuration::deleteByName('KIALA_SEARCH_BY');

		// Uninstall SQL
		include(dirname(__FILE__).'/sql-uninstall.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->Execute($s))
				$success = false;

		// Uninstall tab
		$tab = new Tab(Tab::getIdFromClassName('AdminKiala'));
		$success &= $tab->delete();

		// Uninstall Module
		$success &= parent::uninstall()
			&& $this->unregisterHook('updateCarrier')
			&& $this->unregisterHook('extraCarrier')
			&& $this->unregisterHook('newOrder');

		return $success;
	}

	/**
	 * When carriers are displayed add the Kiala block
	 *
	 * @param array $params
	 * @return string
	 */
	public function hookExtraCarrier($params)
	{
		global $smarty;
		$cart = $params['cart'];

		// Cart::carrierIsSelected() added in version 1.5
		if (method_exists($cart, 'carrierIsSelected'))
			if (!$cart->carrierIsSelected(Configuration::get('KIALA_CARRIER_ID'), $params['address']->id))
				return;

		if ($this->compatibility_mode)
			$page_name = basename($_SERVER['PHP_SELF']);
		else
		{
			if (Configuration::get('PS_ORDER_PROCESS_TYPE'))
				$page_name = 'order-opc';
			else
				$page_name = 'order';
		}

		if ($cart->id_carrier == Configuration::get('KIALA_CARRIER_ID'))
			$content = $this->displayPoint($page_name);

		$smarty->assign(array(
			'kiala_module_dir' => _MODULE_DIR_.$this->name.'/',
			'kiala_carrier_id' => Configuration::get('KIALA_CARRIER_ID'),
			'kiala_content' => isset($content) ? $content : '',
			'kiala_token' => Configuration::get('KIALA_SECURITY_TOKEN'),
			'kiala_page_name' => Tools::safeOutput($page_name),
			'kiala_is_opc' => Configuration::get('PS_ORDER_PROCESS_TYPE'),
			'kiala_compatibility_mode' => $this->compatibility_mode
		));

		return $this->display(__FILE__, 'kiala.tpl');
	}

	/**
	 * Get the relevant Kiala Point and return the display block
	 *
	 * @param string $page_name name of the calling page
	 * @return string block to be displayed
	 */
	public function displayPoint($page_name)
	{
		global $smarty, $cart, $link;
		if (!$point = $this->getPointFromPost())
			$point = $this->getPointFromWs();
		if (!$point)
			return $this->l('No Kiala point was found');

		$kiala_order = KialaOrder::getEmptyKialaOrder($cart->id);
		$kiala_order->point_short_id = (string)$point->short_id;
		$kiala_order->point_name = $point->name;
		$kiala_order->point_street = $point->street;
		$kiala_order->point_zip = $point->zip;
		$kiala_order->point_city = $point->city;
		$kiala_order->point_location_hint = $point->location_hint;
		$kiala_order->id_cart = (int)$cart->id;
		$kiala_order->save();

		$address = new Address($cart->id_address_delivery);
		$kiala_request = new KialaRequest();

		$page_link = $link->getPageLink(strip_tags($page_name)).'?';

		// Only for 5-steps checkout
		if (!Configuration::get('PS_ORDER_PROCESS_TYPE'))
			$page_link .= 'step=2';

		$url = $kiala_request->getSearchRequest($address, $cart->id_lang, $page_link);

		$smarty->assign(array(
			'kiala_module_dir' => _MODULE_DIR_.$this->name.'/',
			'point' => $point,
			'search_link' => Tools::safeOutput($url),
			'id_customer' => (int)$cart->id_customer,
		));

		// Load a different TPL for version 1.4
		if ($this->compatibility_mode)
			$point_tpl = 'point-1-4.tpl';
		else
			$point_tpl = 'point.tpl';

		return $this->display(__FILE__, $point_tpl);
	}

	/**
	 * Return Kiala point filled with data from POST
	 *
	 * @return boolean|KialaPoint Kiala point or false
	 */
	public function getPointFromPost()
	{
		global $smarty, $cart;
		if (!$short_id = Tools::getValue('shortkpid'))
			return false;
		$kiala_request = new KialaRequest();

		$points = $kiala_request->getPointList($short_id);
		$point = $points[0];
		if (isset ($point->short_id) && $point->short_id == $short_id)
		{
			$point->status = 'selection';
			return $point;
		}

		return false;
	}

	/**
	 * Return the Kiala point returned by the Kiala webservice
	 *
	 * @return boolean|KialaPoint Kiala point or false
	 */
	public function getPointFromWs()
	{
		global $smarty, $cart;

		$kiala_request = new KialaRequest();

		// If the customer used Kiala before, get the latest point he selected
		$existing_point = KialaOrder::getLatestByCustomer($cart->id_customer);

		if (Validate::isLoadedObject($existing_point))
			$points = $kiala_request->getPointList($existing_point->point_short_id);
		else
			$points = $kiala_request->getPointList();

		if (!$points || !is_array($points))
			return false;

		// If the customer used a Kiala point before, is the point still available?
		if (Validate::isLoadedObject($existing_point) && $points[0]->short_id == $existing_point->point_short_id)
		{
			if ($points[0]->available == "1")
			{
				$point = $points[0];
				$point->status = 'point_already_selected';
			}
			elseif (count($points) == 2)
			{
				$point = $points[1];
				$point->status = 'point_unavailable';
				$smarty->assign('unavailable_point_name', Tools::safeOutput($points[0]->name));
			}
		}
		else
		{
			$point = $points[0];
			$point->status = 'new_point';
		}

		return $point;
	}

	/**
	 * Each time a new order with Kiala delivery is passed, create the Kiala order in database
	 *
	 * @param array $params
	 */
	public function hookNewOrder($params)
	{
		// Get the kiala order created when the user selected a Kiala point
		$kiala_order = KialaOrder::getEmptyKialaOrder($params['cart']->id);

		if (!Validate::isLoadedObject($kiala_order) || !$kiala_order->point_short_id)
			return;

		// If the kiala carrier was selected at some point, but another carrier was the final choice, delete the uncomplete kiala order
		if ($params['cart']->id_carrier != Configuration::get('KIALA_CARRIER_ID'))
		{
			$kiala_order->delete();
			return;
		}

		$kiala_order->id_customer = $params['customer']->id;
		$kiala_order->id_cart = $params['cart']->id;
		$kiala_order->id_order = $params['order']->id;

		$kiala_country_pickup = KialaCountry::getPickupCountry();

		$kiala_order->id_country_pickup = $kiala_country_pickup->id_country;

		// Get delivery country using the customer delivery address (not the kiala point address)
		$delivery_address = new Address($params['order']->id_address_delivery);
		$kiala_order->id_country_delivery = $delivery_address->id_country;

		// Create a new address with the Kiala point location
		$point_address = new Address();
		$point_address->id_customer = $kiala_order->id_customer;
		$point_address->id_country = $kiala_order->id_country_delivery;
		// Set id_state in case the merchant added this field to the required fields list
		$point_address->id_state = 0;
		$point_address->lastname = $delivery_address->lastname;
		$point_address->firstname = $delivery_address->firstname;
		$point_address->address1 = substr($kiala_order->point_name.' - '.$kiala_order->point_street, 0, 128);
		$point_address->postcode = $kiala_order->point_zip;
		$point_address->city = $kiala_order->point_city;
		$point_address->address2 = $kiala_order->point_location_hint;
		$point_address->alias = 'Kiala point - '.date('d-m-Y');
		$point_address->deleted = true;
		$point_address->save();

		// Assign the kiala point address as delivery address in order
		if ($point_address->id)
		{
			$order = $params['order'];
			$order->id_address_delivery = $point_address->id;
			$order->update();
		}

		if (Configuration::get('KIALA_EXPORT_SINGLE'))
		{
			$export = new ExportFormat($this);
			$export->export($kiala_order);
			$kiala_order->exported = 1;
		}
		$kiala_order->save();
	}

	/**
	 * Handle post data
	 */
	protected function _postProcess()
	{
		$errors = array();
		if (Tools::isSubmit('settings'))
		{
			if (isset($_POST['kialaFolder']) && is_dir(pSQL(str_replace('\\', '/', $_POST['kialaFolder']))))
				Configuration::updateValue('KIALA_EXPORT_FOLDER', pSQL(str_replace('\\', '/', $_POST['kialaFolder'])));
			else
				$errors[] = $this->l('Export folder cannot be located.');

			Configuration::updateValue('KIALA_EXPORT_SINGLE', (int)Tools::getValue('kialaExportSingle'));
			Configuration::updateValue('KIALA_NUMBER_PREFIX', Tools::getValue('kialaPrefix'));
			Configuration::updateValue('KIALA_SEARCH_BY', Tools::getValue('kialaSearchBy'));
		}
		elseif (Tools::isSubmit('submitEditCountry'))
		{
			$kiala_country = new KialaCountry(Tools::getValue('id_kiala_country'));
			if (Validate::isLoadedObject($kiala_country))
			{
				if (Tools::getValue('dspid'))
					$kiala_country->dspid = Tools::getValue('dspid');
				if (Tools::getValue('preparation_delay') && Validate::isUnsignedInt(Tools::getValue('preparation_delay')))
					$kiala_country->preparation_delay = (int)Tools::getValue('preparation_delay');
				else
					$errors[] = $this->l('Preparation delay is not valid.');

				$kiala_country->active = (int)Tools::getValue('kiala_active');
				$kiala_country->save();
			}
		}
		elseif (Tools::getValue('active_country') !== false && Tools::getValue('id_kiala_country'))
		{
			$kiala_country = new KialaCountry(Tools::getValue('id_kiala_country'));
			if (Validate::isLoadedObject($kiala_country))
			{
				$kiala_country->active = Tools::getValue('active_country') ? 1 : 0;
				$kiala_country->save();
			}
			else
				$errors[] = $this->l('Invalid country.');
		}

		if (empty($errors))
		{
			if (Tools::isSubmit('settings') || Tools::isSubmit('submitEditCountry') || Tools::getValue('active_country') !== false)
				$this->_html .= '<div class="conf confirm"><img src="'._PS_ADMIN_IMG_.'/ok.gif" alt="" /> '.$this->l('Settings updated').'</div>';
		}
		else
			foreach ($errors AS $err)
				$this->_html .= '<div class="alert error"><img src="../modules/kiala/forbbiden.gif" alt="nok" />&nbsp;'.$err.'</div>';
	}


	/**
	 * Display the Kiala countries table
	 *
	 * @param array $countries array of arrays
	 * @param int $id_lang
	 */
	public function _displayCountries($countries, $id_lang)
	{
		$this->_html .=	'<br />
				<fieldset id="country_settings">
					<legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Country settings').'</legend>
						<br class="clear"/>';
		$this->_html .= '<div class="margin-form"><table class="table" cellspacing="0" cellpadding="0" id="table_left" class="tableDnD">
		<thead>
			<tr class="nodrag nodrop">
				<th width="100px" class="center">'.$this->l('Country').'</th>
				<th width="80px" class="center">'.$this->l('User ID').'</th>
				<th width="100px" class="center">'.$this->l('Preparation delay (days)').'</th>
				<th width="30px" class="center">'.$this->l('Active').'</th>
				<th width="30px" class="center">'.$this->l('Edit').'</th>
			</tr>
		</thead>
		<tbody>';

		$irow = 0;
		$base_url = 'index.php?tab='.Tools::getValue('tab').'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.Tools::safeOutput(Tools::getValue('tab_module')).'&module_name='.Tools::safeOutput(Tools::getValue('module_name'));
		foreach ($countries as $country)
		{
			$country_name = Country::getNameById($id_lang, $country['id_country']);
			if (Country::getIsoById($country['id_country']) == 'BE')
			{
				$luxemburg = new Country(Country::getByIso('LU'), $id_lang);
				$country_name .= ' / '.$luxemburg->name;
			}
			$active_url = Tools::safeOutput($base_url.'&active_country='.($country['active'] ? '0' : '1').'&id_kiala_country='.(int)$country['id_kiala_country']);
			
			$this->_html .= '
				<tr id="tr_0_'.$country['id_kiala_country'].'" '.($irow++ % 2 ? 'class="alt_row"' : '').'>
					<td class="center">'.Tools::safeOutput($country_name).'</td>
					<td class="center">'.Tools::safeOutput($country['dspid']).'</td>
					<td class="center">'.(int)$country['preparation_delay'].'</td>
					<td class="center">
						<a href="'.$active_url.'">
	        				<img src="../img/admin/'.($country['active'] ? 'enabled.gif' : 'disabled.gif').'"
	        					alt="'.($country['active'] ? $this->l('Enabled') : $this->l('Disabled')).'" title="'.($country['active'] ? $this->l('Enabled') : $this->l('Disabled')).'" />
	        			</a>
					</td>
					<td class="center">
						<a href="index.php?tab='.Tools::safeOutput(Tools::getValue('tab').'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.Tools::getValue('tab_module').'&module_name='.Tools::safeOutput(Tools::getValue('module_name')).'&editCountry&id_kiala_country='.(int)$country['id_kiala_country']).'" title="'.$this->l('Edit').'"><img src="'._PS_ADMIN_IMG_.'edit.gif" alt="" /></a>
					</td>
				</tr>';
		}
		$this->_html .= '
			</tbody>
			</table></div>
			</fieldset><br />';
	}

	/**
	 * Display order tracking info
	 *
	 * @param array $params (order, cart)
	 * @return string template rendering
	 */
	public function hookOrderDetailDisplayed($params)
	{
		global $smarty;

		$kiala_order = KialaOrder::getByOrder($params['order']->id);
		if (!Validate::isLoadedObject($kiala_order))
			return false;
		$address = new Address($params['order']->id_address_delivery);
		$kiala_country = KialaCountry::getByIdCountry($address->id_country);
		$search_by = Configuration::get('KIALA_SEARCH_BY');

		if ($search_by == 'customer')
			$id = $kiala_order->id_customer;
		elseif ($search_by == 'order')
			$id = Configuration::get('KIALA_NUMBER_PREFIX').$kiala_order->id;
		else
			return false;

		$kiala_request = new KialaRequest();
		$url = $kiala_request->getTrackingRequest($address, $kiala_country, $params['order']->id_lang, $id, $search_by);

		$smarty->assign('url_tracking', Tools::safeOutput($url));
		return $this->display(__FILE__, 'orderDetail.tpl');
	}

	/**
	 * Display edit country settings page
	 *
	 * @param int $id_kiala_country
	 * @return string
	 */
	function _displayEditCountry($id_kiala_country)
	{
		global $cookie;
		$kiala_country = new KialaCountry($id_kiala_country);
		return '
		<form action="index.php?tab='.htmlentities(Tools::getValue('tab')).'&configure='.Tools::getValue('configure').'&token='.Tools::getValue('token').'&tab_module='.Tools::getValue('tab_module').'&module_name='.Tools::safeOutput(Tools::getValue('module_name')).'" method="post">
			<fieldset>
				<legend><img src="../img/admin/picture.gif" />'.Country::getNameById($cookie->id_lang, $kiala_country->id_country).'</legend><br />
				<label>'.$this->l('Kiala User ID:').' </label>
				<div class="margin-form">
					<input type="text" name="dspid" id="dspid" size="30" value="'.htmlentities($kiala_country->dspid, ENT_COMPAT, 'UTF-8').'" />
				</div>
				<label>'.$this->l('Preparation delay:').' </label>
				<div class="margin-form">
					<input type="text" name="preparation_delay" id="preparation_delay" size="3" value="'.(int)$kiala_country->preparation_delay.'" />
				</div>
				<label>'.$this->l('Active:').' </label>
				<div class="margin-form">
					<input type="radio" name="kiala_active" id="active_on" value="1" '.($kiala_country->active ? 'checked="checked" ' : '').'/>
					<label class="t" for="active_on"><img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="kiala_active" id="active_off" value="0" '.(!$kiala_country->active ? 'checked="checked" ' : '').'/>
					<label class="t" for="active_off"><img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" /></label>
					<p>'.$this->l('If not active Kiala will not be available for customers of this country').'</p>
				</div>
				<input type="hidden" name="id_kiala_country" value="'.(int)$id_kiala_country.'"/>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitEditCountry" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>
		<br /><br /><a href="index.php?tab='.Tools::safeOutput(Tools::getValue('tab').'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.Tools::getValue('tab_module').'&module_name='.Tools::getValue('module_name')).'"><img src="../img/admin/arrow2.gif" /> '.$this->l('Back to list').'</a><br />';
	}
}
