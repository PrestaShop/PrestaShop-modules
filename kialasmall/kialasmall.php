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

require_once(_PS_MODULE_DIR_.'kialasmall/classes/SmKialaRequest.php');
require_once(_PS_MODULE_DIR_.'kialasmall/classes/SmExportFormat.php');
require_once(_PS_MODULE_DIR_.'kialasmall/classes/SmKialaOrder.php');
require_once(_PS_MODULE_DIR_.'kialasmall/classes/SmKialaCountry.php');

class Kialasmall extends Module
{
	protected $default_preparation_delay = 2;
	protected $countries = array('BE', 'ES', 'NL');
	/**
	 * @var bool $compatibility_mode set to true if for version 1.4
	 */
	protected $compatibility_mode;

	protected $_config = array(
		'name' => 'kialasmall',
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
		'external_module_name'=> 'kialasmall',
		'need_range' => true,
		'price' => 2.99,
		);

	public function __construct()
	{
		$this->name		= 'kialasmall';
		$this->tab		= 'shipping_logistics';
		$this->version	= '1.4.1';
		$this->compatibility_mode = version_compare(_PS_VERSION_, '1.5.0.0', '<');
		$this->author = 'PrestaShop';
		$this->need_instance = false;
		$this->module_key = '25f29a96e4beacf0477f93993aa86087';

		parent::__construct();

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Kiala Light webservice integration');
		$this->description = $this->l('Offer delivery choice and savings to your customers. Activate the Kiala collection Point delivery option.');
		$this->register_link = 'http://www.kiala.com';
		$this->ws_url = 'http://packandship-ws.kiala.com/psws/order?wsdl';
	}

	public function install()
	{
		if (!parent::install() ||
			!$this->installExternalCarrier($this->_config) ||
			!Configuration::updateValue('EXTERNAL_CARRIER_OVERCOST', 5) ||
			!$this->registerHook('updateCarrier') ||
			!$this->registerHook('extraCarrier') ||
			!$this->registerHook('newOrder') ||
			!$this->registerHook('processCarrier') ||
			!$this->registerHook('orderDetailDisplayed') ||
			!$this->registerHook('updateOrderStatus') ||
			!Configuration::updateValue('KIALASMALL_VERSION', $this->version) ||
			!Configuration::updateValue('KIALASMALL_SECURITY_TOKEN', Tools::passwdGen(30)) ||
			!Configuration::updateValue('KIALASMALL_WS_URL', $this->ws_url) ||
			!Configuration::updateValue('KIALASMALL_SEARCH_BY', 'order')
		)
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
				$kiala_country = new SmKialaCountry();
				$kiala_country->id_country = $id_country;
				$kiala_country->preparation_delay = $this->default_preparation_delay;
				$kiala_country->active = 0;
				// Is this the merchant home country?
				if ($id_country == Country::getByIso('ES'))
				{
					$kiala_country->pickup_country = 1;
					$kiala_country->dspid = '34600160';
				}
				$kiala_country->save();
			}
		}

		return true;
	}

	/*
	 * Generate configuration page
	 */
	public function getContent()
	{
		global $cookie, $currentIndex;

		$this->_html .= '<h2>' . $this->l('Kiala Light webservice integration').'</h2>';

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
        if (!extension_loaded('soap'))
            return $this->_html.$this->displayError($this->l('You must enable SOAP extension on your server if you want to use this module.'));

		// Only one Kiala module can be active at any time
		$kiala = Module::getInstanceByName('kiala');
		if (Validate::isLoadedObject($kiala) && $kiala->active)
		{
			$this->active = false;
			return $this->_html.$this->displayError($this->l('You must deactivate the Kiala module before you can configure this module.'));
		}

		// Process POST & GET
		$this->_postProcess();

		if (Tools::isSubmit('editCountry') && Tools::getValue('id_sm_kiala_country'))
		{
			$this->_html .= $this->_displayEditCountry(Tools::getValue('id_sm_kiala_country'));
			return $this->_html;
		}

		$this->_html .= '<a href="'.$this->register_link.'"><img src="'.$this->_path.'logo.png"></a><br /><br />';

		$this->_displayStatus();
		$this->_displayDescription();
		$countries = SmKialaCountry::getKialaCountries();
		$this->_displayCountries($countries, $cookie->id_lang);
		$this->_displayForm();

		return $this->_html;
	}

	/**
	 * Display configuration status
	 */
	private function _displayStatus()
	{
		global $cookie;
		// Test alert
		$alert = array();

		if (!SmKialaCountry::getKialaCountries(true))
			$alert['registration'] = 1;
		if (!ini_get('allow_url_fopen'))
			$alert['allowurlfopen'] = 1;
		if (!extension_loaded('curl'))
			$alert['curl'] = 1;

		// Displaying page
		$this->_html .= '<fieldset>	<legend><img src="'.$this->_path.'logo.gif" alt="" /> '.$this->l('Kiala Module Status').'</legend>';
		$this->_html .= '<div style="float: left; width: 80%">';
		if (!count($alert))
			$this->_html .= '<img src="'.$this->_path.'valid.png" /><strong>'.$this->l('Kiala Module is configured and online!').'</strong>';
		else
		{
			$this->_html .= '<img src="'.$this->_path.'warn.png" /><strong>'.$this->l('Kiala Module is not configured yet, you must:').'</strong>';
			$this->_html .= '<br />'.(isset($alert['registration']) ? '<img src="'.$this->_path.'warn.png" />'
				: '<img src="'.$this->_path.'valid.png" />').' 1) <a href="http://www.kiala.com" style="text-decoration:underline">'
				.$this->l('Register an account with Kiala').'</a> '.$this->l(' then configure and activate Kiala delivery for your country (see ').' <a href="#country_settings" style="text-decoration:underline">country settings</a>'.$this->l(')');
			$this->_html .= '<br />'.(isset($alert['allowurlfopen']) ? '<img src="'.$this->_path.'warn.png" />' : '<img src="'.$this->_path.'valid.png" />').' 2) '.$this->l('Allow url fopen');
			$this->_html .= '<br />'.(isset($alert['curl']) ? '<img src="'.$this->_path.'warn.png" />' : '<img src="'.$this->_path.'valid.png" />').' 3) '.$this->l('Enable cURL');
		}
		$this->_html .= '</div>';
		$this->_html .= '</fieldset><div class="clear">&nbsp;</div>';
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
					<ul>
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
						$this->l('The Kiala module can be activated for Spain (soon in Belgium, France, Luxembourg, Netherlands).').'<br /><br />'.
						$this->l('You will need to register on the Kiala website, within the shipment section. If you currently ship more than 10 parcels per day, you may be interested to get in touch with Kiala in order to activate the other more comprehensive Kiala Addons, “Kiala Comprehensive datafile integration – Kiala contract holders only”').'
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
						<label class="t" for="kialaPrefix">'.$this->l('Prefix for order and parcel number').'&nbsp;&nbsp;</label>
						<input id="kialaPrefix" type="text" name="kialaPrefix" size="16" value="'.htmlentities(Configuration::get('KIALASMALL_NUMBER_PREFIX'), ENT_NOQUOTES, 'UTF-8').'" />
					</div>
					<br class="clear"/>
					<div class="margin-form">
						<label class="t">'.$this->l('Parcel tracking criterion?').'&nbsp;&nbsp;</label>
						<input id="searchByCustomer" type="radio" '.(Configuration::get('KIALASMALL_SEARCH_BY') == 'customer' ? 'checked="checked"' : '').' name="kialaSearchBy" value="customer" />
						<label class="t" for="searchByCustomer">'.$this->l('Customer').'</label>
						<input id="searchByOrder" type="radio" '.(Configuration::get('KIALASMALL_SEARCH_BY') == 'customer' ? '' : 'checked="checked"').' name="kialaSearchBy" value="order" />
						<label class="t" for="searchByOrder">'.$this->l('Order').'</label>
					</div>';
					/*<div class="margin-form">
						<label class="t" for="kialaWsUrl">'.$this->l('URL to use for the webservice').'&nbsp;&nbsp;</label>
						<input id="kialaWsUrl" type="text" name="kialaWsUrl" size="80" value="'.htmlentities(Configuration::get('KIALASMALL_WS_URL'), ENT_NOQUOTES, 'UTF-8').'" />
					</div>*/
					$this->_html .=	'<br />
					<div align="center">
						<input type="submit" name="settings" id="button_kiala" class="button" value="'.$this->l('Save settings').'" />
					</div>
				</fieldset>
			</form>';
	}

	public function hookUpdateCarrier($params)
	{
		Configuration::updateValue('KIALASMALL_CARRIER_ID', (int)($params['carrier']->id));
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
			if ($language['iso_code'] == 'en')
				$carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
			if ($language['iso_code'] == 'es')
				$carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
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

			Configuration::updateValue('KIALASMALL_CARRIER_ID', (int)($carrier->id));
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
        $kiala_country = SmKialaCountry::getByIdCountry($address->id_country);
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
		$success = Db::getInstance()->autoExecute(_DB_PREFIX_.'carrier', array('deleted' => 1), 'UPDATE', '`external_module_name` = \'kialasmall\'');

		// Uninstall Config
		$success &= Configuration::deleteByName('KIALASMALL_VERSION')
			&& Configuration::deleteByName('KIALASMALL_CARRIER_ID')
			&& Configuration::deleteByName('KIALASMALL_SECURITY_TOKEN')
			&& Configuration::deleteByName('KIALASMALL_NUMBER_PREFIX')
			&& Configuration::deleteByName('KIALASMALL_SEARCH_BY');

		// Uninstall SQL
		include(dirname(__FILE__).'/sql-uninstall.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->Execute($s))
				$success = false;

		// Uninstall Module
		$success &= parent::uninstall()
			&& $this->unregisterHook('updateCarrier')
			&& $this->unregisterHook('extraCarrier')
			&& $this->unregisterHook('newOrder')
			&& $this->unregisterHook('orderDetailDisplayed')
			&& $this->unregisterHook('hookUpdateOrderStatus');

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

		if ($this->compatibility_mode)
			$page_name = basename($_SERVER['PHP_SELF']);
		else
		{
			if (Configuration::get('PS_ORDER_PROCESS_TYPE'))
				$page_name = 'order-opc';
			else
				$page_name = 'order';
		}

		if ($cart->id_carrier == Configuration::get('KIALASMALL_CARRIER_ID'))
			$content = $this->displayPoint($page_name);

		$smarty->assign(array(
			'ks_kiala_module_dir' => _MODULE_DIR_.$this->name.'/',
			'ks_kiala_carrier_id' => Configuration::get('KIALASMALL_CARRIER_ID'),
			'ks_content' => isset($content) ? $content : '',
			'ks_kiala_token' => Configuration::get('KIALASMALL_SECURITY_TOKEN'),
			'ks_page_name' => Tools::safeOutput($page_name),
			'ks_is_opc' => Configuration::get('PS_ORDER_PROCESS_TYPE'),
			'ks_compatibility_mode' => $this->compatibility_mode
		));
		return $this->display(__FILE__, 'kialasmall.tpl');
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

		$kiala_order = SmKialaOrder::getEmptyKialaOrder($cart->id);
		$kiala_order->point_short_id = (string)$point->short_id;
		$kiala_order->point_name = $point->name;
		$kiala_order->point_street = $point->street;
		$kiala_order->point_zip = $point->zip;
		$kiala_order->point_city = $point->city;
		$kiala_order->point_location_hint = $point->location_hint;
		$kiala_order->id_cart = (int)$cart->id;
		$kiala_order->save();

		$address = new Address($cart->id_address_delivery);
		$kiala_request = new SmKialaRequest();
		$page_link = $link->getPageLink(strip_tags($page_name)).'?';

		// Only for 5-steps checkout
		if (!Configuration::get('PS_ORDER_PROCESS_TYPE'))
			$page_link .= 'step=2';
		$url = $kiala_request->getSearchRequest($address, $cart->id_lang, $page_link);

		$smarty->assign(array(
			'ks_kiala_module_dir' => _MODULE_DIR_.$this->name.'/',
			'point' => $point,
			'ks_search_link' => Tools::safeOutput($url),
			'ks_id_customer' => (int)$cart->id_customer,
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
	 * @return boolean|SmKialaPoint Kiala point or false
	 */
	public function getPointFromPost()
	{
		global $smarty, $cart;
		if (!$short_id = Tools::getValue('shortkpid'))
			return false;
		$kiala_request = new SmKialaRequest();

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
	 * @return boolean|SmKialaPoint Kiala point or false
	 */
	public function getPointFromWs()
	{
		global $smarty, $cart;

		$kiala_request = new SmKialaRequest();

		// If the customer used Kiala before, get the latest point he selected
		$existing_point = SmKialaOrder::getLatestByCustomer($cart->id_customer);

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
				$smarty->assign('ks_unavailable_point_name', Tools::safeOutput($points[0]->name));
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
		$kiala_order = SmKialaOrder::getEmptyKialaOrder($params['cart']->id);

		if (!Validate::isLoadedObject($kiala_order) || !$kiala_order->point_short_id)
			return;

		// If the kiala carrier was selected at some point, but another carrier was the final choice, delete the uncomplete kiala order
		if ($params['cart']->id_carrier != Configuration::get('KIALASMALL_CARRIER_ID'))
		{
			$kiala_order->delete();
			return;
		}

		$kiala_order->id_customer = $params['customer']->id;
		$kiala_order->id_cart = $params['cart']->id;
		$kiala_order->id_order = $params['order']->id;

		$kiala_country_pickup = SmKialaCountry::getPickupCountry();

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

		$kiala_order->save();
	}


	/**
	 * Check if the order is validated for the first time to send a creation request to Kiala WS
	 *
	 * @param $params
	 * @return bool
	 */
	public function hookUpdateOrderStatus($params)
	{
		$kiala_order = SmKialaOrder::getByOrder($params['id_order']);
		// if order did not use Kiala, stop here
		if (!$kiala_order)
			return true;

		// if the payment been validated, send order creation request to Kiala WS
		$new_state = $params['newOrderStatus'];
		if ($new_state->logable == 1 && $kiala_order->tracking_number == '')
		{
			$export_format = new SmExportFormat($this);
			$request = new SmKialaRequest();
			$data = $export_format->initRecordData($kiala_order, null);
			$params = $request->getCreateOrderRequest($data, $kiala_order);
			$tracking_number = $request->makeRequestSoap($params);
			if ($tracking_number){
				$kiala_order->tracking_number = $tracking_number;
				$kiala_order->save();
			}
		}
		return true;
	}
	/**
	 * Handle post data
	 */
	protected function _postProcess()
	{
		$errors = array();
		if (Tools::isSubmit('settings'))
		{
			Configuration::updateValue('KIALASMALL_NUMBER_PREFIX', Tools::getValue('kialaPrefix'));
			Configuration::updateValue('KIALASMALL_SEARCH_BY', Tools::getValue('kialaSearchBy'));
		}
		elseif (Tools::isSubmit('submitEditCountry'))
		{
			$kiala_country = new SmKialaCountry(Tools::getValue('id_sm_kiala_country'));
			if (Validate::isLoadedObject($kiala_country))
			{
				if (Tools::getValue('dspid'))
					$kiala_country->dspid = Tools::getValue('dspid');
                if (Tools::getValue('sender_id'))
                    $kiala_country->sender_id = Tools::getValue('sender_id');
                if (Tools::getValue('password'))
                    $kiala_country->password = Tools::getValue('password');
				if (Tools::getValue('preparation_delay') && Validate::isUnsignedInt(Tools::getValue('preparation_delay')))
					$kiala_country->preparation_delay = (int)Tools::getValue('preparation_delay');
				else
					$errors[] = $this->l('Preparation delay is not valid.');

				$kiala_country->active = (int)Tools::getValue('kiala_active');
				$kiala_country->save();
			}
		}
		elseif (Tools::getValue('active_country') !== false && Tools::getValue('id_sm_kiala_country'))
		{
			$kiala_country = new SmKialaCountry(Tools::getValue('id_sm_kiala_country'));
			if (Validate::isLoadedObject($kiala_country))
			{
				$kiala_country->active = Tools::getValue('active_country') ? 1 : 0;
				$kiala_country->save();
			}
			else
				$errors[] = $this->l('Invalid country.');
		}

		if (empty($errors))
			if (Tools::isSubmit('settings') || Tools::isSubmit('submitEditCountry') || Tools::getValue('active_country') !== false)
			{
				$this->_html .= '<div class="conf confirm">';
				if ($this->compatibility_mode)
					$this->_html .= '<img src="'._PS_ADMIN_IMG_.'/ok.gif" alt="" />';
				$this->_html .= $this->l('Settings updated').'</div>';
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
				<th width="80px" class="center">'.$this->l('Country').'</th>
				<th width="80px" class="center">'.$this->l('User ID').'</th>
				<th width="200px" class="center">'.$this->l('Sender ID').'</th>
				<th width="100px" class="center">'.$this->l('Preparation delay (days)').'</th>
				<th width="30px" class="center">'.$this->l('Active').'</th>
				<th width="30px" class="center">'.$this->l('Edit').'</th>
			</tr>
		</thead>
		<tbody>';

		$irow = 0;
		$base_url = 'index.php?tab='.Tools::getValue('tab').'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.Tools::getValue('tab_module').'&module_name='.Tools::getValue('module_name');
		foreach ($countries as $country)
		{
			$country_name = Country::getNameById($id_lang, $country['id_country']);
			$active_url = Tools::safeOutput($base_url.'&active_country='.($country['active'] ? '0' : '1').'&id_sm_kiala_country='.(int)$country['id_sm_kiala_country']);
			$this->_html .= '
				<tr id="tr_0_'.$country['id_sm_kiala_country'].'" '.($irow++ % 2 ? 'class="alt_row"' : '').'>
					<td class="center">'.Tools::safeOutput($country_name).'</td>
					<td class="center">'.Tools::safeOutput($country['dspid']).'</td>
					<td class="center">'.Tools::safeOutput($country['sender_id']).'</td>
					<td class="center">'.(int)$country['preparation_delay'].'</td>
					<td class="center">
						<a href="'.$active_url.'">
	        				<img src="../img/admin/'.($country['active'] ? 'enabled.gif' : 'disabled.gif').'"
	        					alt="'.($country['active'] ? $this->l('Enabled') : $this->l('Disabled')).'" title="'.($country['active'] ? $this->l('Enabled') : $this->l('Disabled')).'" />
	        			</a>
	        		</td>
					<td class="center">
						<a href="'.Tools::safeOutput($base_url.'&editCountry&id_sm_kiala_country='.(int)$country['id_sm_kiala_country']).'" title="'.$this->l('Edit').'"><img src="'._PS_ADMIN_IMG_.'edit.gif" alt="" /></a>
					</td>
				</tr>';
		}
		$this->_html .= '
			</tbody>
			</table></div>';
		$this->_html .= '</fieldset><br />';
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

		$kiala_order = SmKialaOrder::getByOrder($params['order']->id);
		if (!Validate::isLoadedObject($kiala_order))
			return false;

		$address = new Address($params['order']->id_address_delivery);
		$kiala_country = SmKialaCountry::getByIdCountry($address->id_country);
		$search_by = Configuration::get('KIALASMALL_SEARCH_BY');
		if ($search_by == 'customer')
			$id = $kiala_order->id_customer;
		elseif ($search_by == 'order')
			$id = Configuration::get('KIALASMALL_NUMBER_PREFIX').$kiala_order->id;
		else
			return false;

		$kiala_request = new SmKialaRequest();
		$url = $kiala_request->getTrackingRequest($address, $kiala_country, $params['order']->id_lang, $id, $search_by);

		$smarty->assign('url_tracking', Tools::safeOutput($url));
		return $this->display(__FILE__, 'orderDetail.tpl');
	}

	/**
	 * Display edit country settings page
	 *
	 * @param int $id_sm_kiala_country
	 * @return string
	 */
	function _displayEditCountry($id_sm_kiala_country)
	{
		global $cookie, $currentIndex;
		$kiala_country = new SmKialaCountry($id_sm_kiala_country);
		return '
		<form action="index.php?tab='.htmlentities(Tools::getValue('tab')).'&configure='.Tools::getValue('configure').'&token='.Tools::getValue('token').'&tab_module='.Tools::getValue('tab_module').'&module_name='.Tools::getValue('module_name').'" method="post">
			<fieldset>
				<legend><img src="../img/admin/picture.gif" />'.Country::getNameById($cookie->id_lang, $kiala_country->id_country).'</legend><br />
				<label>'.$this->l('Kiala User ID:').' </label>
				<div class="margin-form">
					<input type="text" name="dspid" id="dspid" size="30" value="'.htmlentities($kiala_country->dspid, ENT_COMPAT, 'UTF-8').'" />
				</div>
				<label>'.$this->l('Kiala Sender ID:').' </label>
				<div class="margin-form">
					<input type="text" name="sender_id" id="sender_id" size="30" value="'.htmlentities($kiala_country->sender_id, ENT_COMPAT, 'UTF-8').'" />
				</div>
				<label>'.$this->l('Kiala password:').' </label>
				<div class="margin-form">
					<input type="password" name="password" id="password" size="30" value="'.htmlentities($kiala_country->password, ENT_COMPAT, 'UTF-8').'" />
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
				<input type="hidden" name="id_sm_kiala_country" value="'.(int)$id_sm_kiala_country.'"/>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitEditCountry" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>
		<br /><br /><a href="index.php?tab='.Tools::safeOutput(Tools::getValue('tab').'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.Tools::getValue('tab_module').'&module_name='.Tools::getValue('module_name')).'"><img src="../img/admin/arrow2.gif" /> '.$this->l('Back to list').'</a><br />';
	}
}
