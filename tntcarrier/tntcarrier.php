<?php
/*
* 2007-2014 PrestaShop
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
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

// Avoid direct access to the file
require_once(_PS_MODULE_DIR_."/tntcarrier/classes/PackageTnt.php");
require_once(_PS_MODULE_DIR_."/tntcarrier/classes/TntWebService.php");
require_once(_PS_MODULE_DIR_."/tntcarrier/classes/OrderInfoTnt.php");
require_once(_PS_MODULE_DIR_."/tntcarrier/classes/serviceCache.php");
require_once(_PS_MODULE_DIR_."/tntcarrier/classes/CodePostal.php");

if (!defined('_PS_VERSION_'))
	exit;

class TntCarrier extends CarrierModule
{
	public  $id_carrier;

	private $_html = '';
	private $_postErrors = array();
	private $_moduleName = 'tntcarrier';
	private $_fieldsList = array();

	/*
	** Construct Method
	**
	*/
	public function __construct()
	{
		$this->name = 'tntcarrier';
		$this->tab = 'shipping_logistics';
		$this->version = '1.9.7';
		$this->author = 'PrestaShop';
		$this->limited_countries = array('fr');
		$this->module_key = 'd4dcfde9937b67002235598ac35cbdf8';

		parent::__construct ();

		$this->displayName = $this->l('TNT Express');
		$this->description = $this->l('Offer your customers, different delivery methods with TNT');

		if (self::isInstalled($this->name))
		{
			global $cookie;
			$warning = array();
			$this->loadingVar();
			$carriers = Carrier::getCarriers($cookie->id_lang, true, false, false, null, PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);

			foreach ($this->_fieldsList as $keyConfiguration => $name)
				if (!Configuration::get($keyConfiguration) && !empty($name))
					$warning[] = '\''.$name.'\' ';

			// Saving id carrier list
			$id_carrier_list = array();
			foreach($carriers as $carrier)
				$id_carrier_list[] .= $carrier['id_carrier'];

			if (count($warning) > 1)
				$this->warning .= implode(' , ',$warning).$this->l('must be configured to use this module correctly.').' ';
			if (count($warning) == 1)
				$this->warning .= implode(' , ',$warning).$this->l('has to be configured to use this module correctly.').' ';
		}
	}

	public function bqSQL($string)
	{
		return str_replace('`', '\`', pSQL($string));
	}

	public function loadingVar()
	{
		// Loading Fields List
		$this->_fieldsList = array(
			'TNT_CARRIER_LOGIN' => $this->l('TNT Login'),
			'TNT_CARRIER_PASSWORD' => $this->l('TNT Password'),
			'TNT_CARRIER_NUMBER_ACCOUNT' => $this->l('TNT Number Account'),
			'TNT_CARRIER_SHIPPING_COMPANY' => '',
			'TNT_CARRIER_SHIPPING_LASTNAME' => '',
			'TNT_CARRIER_SHIPPING_FIRSTNAME' => '',
			'TNT_CARRIER_SHIPPING_ADDRESS1' => '',
			'TNT_CARRIER_SHIPPING_ADDRESS2' => '',
			'TNT_CARRIER_SHIPPING_ZIPCODE' => '',
			'TNT_CARRIER_SHIPPING_CITY' => '',
			'TNT_CARRIER_SHIPPING_EMAIL' => '',
			'TNT_CARRIER_SHIPPING_PHONE' => '',
			'TNT_CARRIER_SHIPPING_CLOSING' => '',
			'TNT_CARRIER_SHIPPING_DELIVERY' => '',
			'TNT_CARRIER_SHIPPING_COLLECT' => '',
			'TNT_CARRIER_SHIPPING_PEX' => '',
			'TNT_CARRIER_PRINT_STICKER' => '',
			'TNT_CARRIER_CORSE_OVERCOST' => '',
			'TNT_CARRIER_TOKEN' => ''
		);

		$option = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'tnt_carrier_option`');
		foreach($option as $k => $v)
		{
			$this->_fieldsList['TNT_CARRIER_'.$v['option'].'_ID'] = (float)($v['id_carrier']);
			$this->_fieldsList['TNT_CARRIER_'.$v['option'].'_OVERCOST'] = Configuration::get('TNT_CARRIER_'.$v['option'].'_OVERCOST');
		}
	}
	/*
	** Install / Uninstall Methods
	**
	*/

	public function install()
	{
		// Install SQL
		include(dirname(__FILE__).'/sql-install.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->Execute($s))
				return false;
		// Install Module
		if (!parent::install() OR !$this->registerHook('updateCarrier') OR !$this->registerHook('orderDetailDisplayed') OR !$this->registerHook('adminOrder') or !$this->registerHook('extraCarrier') or !$this->registerHook('newOrder'))
			return false;
		if (file_exists('../modules/'.$this->_moduleName.'/serviceBase.xml'))
		{
			$serviceList = simplexml_load_file('../modules/'.$this->_moduleName.'/serviceBase.xml');
			if ($serviceList == false)
				return false;
		}

		foreach($serviceList as $k => $v)
		{
			$carrierConfig = array(
				'name' => $v->name,
				'id_tax_rules_group' => 0,
				'deleted' => ($v->option == 'JS' ? 1 : 0),
				'shipping_handling' => false,
				'range_behavior' => 0,
				'delay' => array('fr' => $v->descriptionfr, 'en' => $v->description),
				'id_zone' => 1,
				'is_module' => true,
				'shipping_external' => true,
				'external_module_name' => $this->_moduleName,
				'need_range' => true,
				'active' => true
			);
			$id_carrier = $this->installExternalCarrier($carrierConfig);
			Configuration::updateValue('TNT_CARRIER_'.$v->option.'_ID', (int)($id_carrier));
			Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'tnt_carrier_option` (`option`, `id_carrier`) VALUES ("'.pSQL($v->option).'", "'.(int)$id_carrier.'")');
			if (substr($v->option, 1, 1) == 'Z')
				copy(dirname(__FILE__).'/logo_24h_chezmoi_RVB.jpg', _PS_SHIP_IMG_DIR_.'/'.(int)$id_carrier.'.jpg');
			else if (substr($v->option, 1, 1) == 'D')
				copy(dirname(__FILE__).'/logo_24h_RELAISCOLIS_RVB.jpg', _PS_SHIP_IMG_DIR_.'/'.(int)$id_carrier.'.jpg');
			else if (strlen($v->option) == 1 || substr($v->option, 1, 1) == 'S')
				copy(dirname(__FILE__).'/logo_24h_ENTREPRISE.jpg', _PS_SHIP_IMG_DIR_.'/'.(int)$id_carrier.'.jpg');
			else
				copy(dirname(__FILE__).'/carrier.jpg', _PS_SHIP_IMG_DIR_.'/'.(int)$carrier->id.'.jpg');
		}

		Configuration::updateValue('TNT_CARRIER_TOKEN', md5(rand()));

		return true;
	}

	public static function installExternalCarrier($config)
	{
		$carrier = new Carrier();
		$carrier->name = $config['name'];
		$carrier->id_tax_rules_group = $config['id_tax_rules_group'];
		$carrier->id_zone = $config['id_zone'];
		$carrier->active = $config['active'];
		$carrier->deleted = $config['deleted'];
		$carrier->delay = $config['delay'];
		$carrier->shipping_handling = $config['shipping_handling'];
		$carrier->range_behavior = $config['range_behavior'];
		$carrier->is_module = $config['is_module'];
		$carrier->shipping_external = $config['shipping_external'];
		$carrier->external_module_name = $config['external_module_name'];
		$carrier->need_range = $config['need_range'];
		$carrier->active = $config['active'];

		$languages = Language::getLanguages(true);
		foreach ($languages as $language)
		{
			if (($language['iso_code'] == 'fr') || ($language['iso_code'] == 'en'))
				$carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
			if ($language['iso_code'] == Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')) && isset($config['delay'][$language['iso_code']]))
				$carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
			elseif ($language['iso_code'] == Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')) && isset($config['delay']['en']))
				$carrier->delay[(int)$language['id_lang']] = $config['delay']['en'];
		}

		if ($carrier->add())
		{
			$groups = Group::getGroups(true);
			foreach ($groups as $group)
				Db::getInstance()->autoExecute(_DB_PREFIX_.'carrier_group', array('id_carrier' => (int)($carrier->id), 'id_group' => (int)($group['id_group'])), 'INSERT');

			$rangePrice = new RangePrice();
			$rangePrice->id_carrier = $carrier->id;
			$rangePrice->delimiter1 = '0';
			$rangePrice->delimiter2 = '10000';
			$rangePrice->add();

			$rangeWeight = new RangeWeight();
			$rangeWeight->id_carrier = $carrier->id;
			$rangeWeight->delimiter1 = '0';
			$rangeWeight->delimiter2 = '10000';
			$rangeWeight->add();

			Db::getInstance()->autoExecute(_DB_PREFIX_.'carrier_zone', array('id_carrier' => (int)($carrier->id), 'id_zone' => (int)($carrier->id_zone)), 'INSERT');
			Db::getInstance()->autoExecuteWithNullValues(_DB_PREFIX_.'delivery', array('id_carrier' => (int)($carrier->id), 'id_range_price' => (int)($rangePrice->id), 'id_range_weight' => null, 'id_zone' => (int)($carrier->id_zone), 'price' => '0'), 'INSERT');
			Db::getInstance()->autoExecuteWithNullValues(_DB_PREFIX_.'delivery', array('id_carrier' => (int)($carrier->id), 'id_range_price' => null, 'id_range_weight' => (int)($rangeWeight->id), 'id_zone' => (int)($carrier->id_zone), 'price' => '0'), 'INSERT');

			// Copy Logo
			if (!copy(dirname(__FILE__).'/carrier.jpg', _PS_SHIP_IMG_DIR_.'/'.(int)$carrier->id.'.jpg'))
				return false;

			// Return ID Carrier
			return (int)($carrier->id);
		}

		return false;
	}

	public function uninstall()
	{
		// Uninstall Carriers
		// 1.5 id_shop !!
		Db::getInstance()->autoExecute(_DB_PREFIX_.'carrier', array('deleted' => 1), 'UPDATE', '`external_module_name` = \'tntcarrier\'');
		// Uninstall Config
		foreach ($this->_fieldsList as $keyConfiguration => $name)
		{
			Configuration::deleteByName($keyConfiguration);
			/*if (!Configuration::deleteByName($keyConfiguration))
				return false;*/
		}
		// Uninstall SQL
		include(dirname(__FILE__).'/sql-uninstall.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->Execute($s))
				return false;

		// Uninstall Module
		if (!parent::uninstall() OR !$this->unregisterHook('updateCarrier'))
			return false;
		return true;
	}

	/*
	** Form Config Methods
	**
	*/

	public function getContent()
	{
		$this->_html .= '<h2><a href="https://www.tnt.fr/public/utilisateurs/adminExt/new.do"><img src="'.$this->_path.'carrier.jpg" alt="' . $this->l('TNT Carrier').'" /></a></h2>';
		if (!empty($_POST) AND Tools::isSubmit('submitSave'))
		{
			$this->_postValidation();
			if (!sizeof($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors AS $err)
					$this->_html .= '<div class="error"><img src="'._PS_IMG_.'admin/forbbiden.gif" alt="nok" />&nbsp;'.$err.'</div>';
		}
		$this->_displayForm();
		return $this->_html;
	}

	private function _displayForm()
	{
		global $smarty;

		$shop = (_PS_VERSION_ >= 1.5 ? Context::getContext()->shop->id : '');

		$globalVar = array(
			'tab' => htmlentities(Tools::getValue('tab')),
			'configure' => htmlentities(Tools::getValue('configure')),
			'token' => htmlentities(Tools::getValue('token')),
			'tab_module' => htmlentities(Tools::getValue('tab_module')),
			'module_name' => htmlentities(Tools::getValue('module_name')),
			'tnt_token' => Configuration::get('TNT_CARRIER_TOKEN'),
			'shop' => $shop);

		$smarty->assign('glob', $globalVar);

		$this->_html .= '<fieldset>
		<legend>'.$this->l('TNT Carrier Module Status').'</legend>';

		$alert = array();
		if (!Configuration::get('TNT_CARRIER_LOGIN') || !Configuration::get('TNT_CARRIER_PASSWORD') || !Configuration::get('TNT_CARRIER_NUMBER_ACCOUNT'))
		{
			$smarty->assign('account_set', false);
			$alert['account'] = 1;
		}
		if (
			!Configuration::get('TNT_CARRIER_SHIPPING_ADDRESS1') ||
			!Configuration::get('TNT_CARRIER_SHIPPING_ZIPCODE') ||
			!Configuration::get('TNT_CARRIER_SHIPPING_CITY') ||
			!Configuration::get('TNT_CARRIER_SHIPPING_EMAIL') ||
			!Configuration::get('TNT_CARRIER_SHIPPING_PHONE'))
			$alert['shipping'] = 1;
		if ((Db::getInstance()->getValue('SELECT * FROM `'._DB_PREFIX_.'carrier` WHERE `external_module_name` = "'.pSQL($this->_moduleName).'" AND deleted = "0"')) < 1)
			$alert['service'] = 1;
		if (!extension_loaded('soap'))
			$alert['soap'] = 1;
		if (count($alert) < 4)
		{
			$this->_html .= '<img src="'._PS_IMG_.'admin/module_install.png" /><strong>'.$this->l('The following parameters are correctly configured and activated on your online store :').'</strong>';
			$this->_html .= '<ul>';
			$this->_html .= (!isset($alert['account']) ? '<li>'.$this->l('TNT account. (Account setting tab)').'</li>' : '');
			$this->_html .= (!isset($alert['shipping']) ? '<li>'.$this->l('Shipping address. (Shipping settings tab)').'</li>' : '');
			$this->_html .= (!isset($alert['service']) ? '<li>'.$this->l('Choice of specific TNT delivery mode you want to offer to your customers. (Service settings tab)').'</li>' : '');
			$this->_html .= (!isset($alert['soap']) ? '<li>'.$this->l('Soap is enable').'</li>' : '');
			$this->_html .= '</ul>';
		}
		if (count($alert) > 0)
		{
			$this->_html .= '<img src="'._PS_IMG_.'admin/warn2.png" /><strong>'.$this->l('The following parameters have to be configured to be able to use correctly the TNT module :').'</strong>';
			$this->_html .= '<ul>';
			$this->_html .= (isset($alert['account']) ? '<li>'.$this->l('TNT account. (Account setting tab)').'</li>' : '');
			$this->_html .= (isset($alert['shipping']) ? '<li>'.$this->l('Shipping address. (Shipping settings tab)').'</li>' : '');
			$this->_html .= (isset($alert['service']) ? '<li>'.$this->l('Choice of specific TNT delivery mode you want to offer to your customers. (Service settings tab)').'</li>' : '');
			$this->_html .= (isset($alert['soap']) ? '<li>'.$this->l('Soap is disable').'</li>' : '');
			$this->_html .= '</ul>';
		}

		$this->_html .= '</fieldset><div class="clear">&nbsp;</div>';
		$this->_html .= $this->_displayFormConfig();
	}

	private function _displayFormConfig()
	{
		global $smarty;
		
		$var = array('account' => $this->_displayFormAccount(), 'shipping' => $this->_displayFormShipping(), 'service' => $this->_displayService(),
					 'country' => $this->_displayCountry('Corse'), 'info' => $this->_displayInfo('weight'));
		$smarty->assign('varMain', $var);
		$html = $this->display( __FILE__, 'tpl/main.tpl' );
		
		if (isset($_GET['id_tab']))
			$html .= '<script>
				$(".menuTabButton.selected").removeClass("selected");
				$("#menuTab'.htmlentities(Tools::getValue('id_tab')).'").addClass("selected");
				$(".tabItem.selected").removeClass("selected");
				$("#menuTab'.htmlentities(Tools::getValue('id_tab')).'Sheet").addClass("selected");
			</script>';

		return $html;
	}

	private function _displayFormAccount()
	{
		global $smarty;
		$var = array('login' => Tools::getValue('tnt_carrier_login', Configuration::get('TNT_CARRIER_LOGIN')), 'password' => Tools::getValue('tnt_carrier_password', Configuration::get('TNT_CARRIER_PASSWORD')),
					 'account' => str_replace(' ', '', Tools::getValue('tnt_carrier_number_account', Configuration::get('TNT_CARRIER_NUMBER_ACCOUNT'))));
		$smarty->assign('varAccount', $var);
		return $this->display( __FILE__, 'tpl/accountForm.tpl' );
	}

	private function _displayFormShipping()
	{
		global $cookie, $smarty;

		$var = array(
			'moduleName' => $this->_moduleName,
			'collect' => Configuration::get('TNT_CARRIER_SHIPPING_COLLECT'),
			'pex' => Configuration::get('TNT_CARRIER_SHIPPING_PEX'),
			'company' => Configuration::get('TNT_CARRIER_SHIPPING_COMPANY'),
			'lastName' => Configuration::get('TNT_CARRIER_SHIPPING_LASTNAME'),
			'firstName' => Configuration::get('TNT_CARRIER_SHIPPING_FIRSTNAME'),
			'address1' => Configuration::get('TNT_CARRIER_SHIPPING_ADDRESS1'),
			'address2' => Configuration::get('TNT_CARRIER_SHIPPING_ADDRESS2'),
			'zipCode' => Configuration::get('TNT_CARRIER_SHIPPING_ZIPCODE'),
			'city' => Configuration::get('TNT_CARRIER_SHIPPING_CITY'),
			'email' => Configuration::get('TNT_CARRIER_SHIPPING_EMAIL'),
			'phone' => Configuration::get('TNT_CARRIER_SHIPPING_PHONE'),
			'closing' => Configuration::get('TNT_CARRIER_SHIPPING_CLOSING') ? Configuration::get('TNT_CARRIER_SHIPPING_CLOSING') : '17:00',
			'delivery' => Configuration::get('TNT_CARRIER_SHIPPING_DELIVERY'),
			'sticker' => Configuration::get('TNT_CARRIER_PRINT_STICKER'));
		$smarty->assign('varShipping', $var);
		$smarty->assign('soap', (!extension_loaded('soap') ? $this->l('Soap is disable') : ''));
		return $this->display( __FILE__, 'tpl/shippingForm.tpl' );
	}

	private function _displayService()
	{
		global $smarty;
		if (Tools::getValue('action') == 'del' && Tools::getValue('service') != '')
		{
			$id = htmlentities(Tools::getValue('service'));
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'carrier` SET `deleted` = "1" WHERE `id_carrier` = '.(int)($id).'');
			$option = Db::getInstance()->getRow('SELECT `option` FROM `'._DB_PREFIX_.'tnt_carrier_option` WHERE `id_carrier` = "'.(int)($id).'"');
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'tnt_carrier_option` WHERE `id_carrier` = '.(int)($id).'');
			Configuration::deleteByName('TNT_CARRIER_'.pSQL($option['option']).'_ID');
			Configuration::deleteByName('TNT_CARRIER_'.pSQL($option['option']).'_OVERCOST');
		}
		$irow = 0;
		$serviceList = Db::getInstance()->ExecuteS('SELECT c.deleted, c.name, cl.delay, o.option
													FROM `'._DB_PREFIX_.'carrier` c, `'._DB_PREFIX_.'carrier_lang` cl, `'._DB_PREFIX_.'tnt_carrier_option` o , `'._DB_PREFIX_.'lang` l
													WHERE c.external_module_name = "'.pSQL($this->_moduleName).'" AND c.id_carrier = cl.id_carrier AND cl.id_lang = l.id_lang AND l.iso_code = "'.pSQL(Language::getIsoById(Configuration::get('PS_LANG_DEFAULT'))).'" AND o.id_carrier = c.id_carrier');

		foreach ($serviceList as $k => $v)
		{
			$serviceList[$k]['optionId'] = Configuration::get('TNT_CARRIER_'.pSQL($v['option']).'_ID');
			$serviceList[$k]['optionOvercost'] = (Configuration::get('TNT_CARRIER_'.pSQL($v['option']).'_OVERCOST') ? Configuration::get('TNT_CARRIER_'.pSQL($v['option']).'_OVERCOST') : '0');
		}

		$var = array('serviceList' => $serviceList,
					 'action' => htmlentities(Tools::getValue('action')),
					 'section' => htmlentities(Tools::getValue('section')),
					 'form' => $this->_displayFormService(htmlentities(Tools::getValue('service'))));
		$smarty->assign('varService', $var);
		return $this->display( __FILE__, 'tpl/service.tpl' );
	}

	private function _displayInfo($cat)
	{
		if (Tools::getValue('action') == 'del' && Tools::getValue($cat) != '')
		{
			$id = Tools::getValue($cat);
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'tnt_carrier_'.$this->bqSQL($cat).'` WHERE `id_'.$this->bqSQL($cat).'` = '.(int)$id.'');
		}

		$html = '
		<a href="index.php?tab='.htmlentities(Tools::getValue('tab')).'&configure='.htmlentities(Tools::getValue('configure')).'&token='.htmlentities(Tools::getValue('token')).'&tab_module='.htmlentities(Tools::getValue('tab_module')).'&module_name='.htmlentities(Tools::getValue('module_name')).'&id_tab=3&section='.htmlentities($cat).'&action=new">
		<img src="../img/admin/add.gif" alt="add"/> '.$this->l('Add additional charges depending on the package weight').'</a></br><br/>
		<table class="table" cellspacing="0" cellpading="0">
			<tr>
				<!--<th>'.$this->l('ID').'</th>-->
				<th>'.$this->l('Weight Min').'</th><th>'.$this->l('Weight Max').'</th><th>'.$this->l('Additionnal charge (Euros)').'</th><th></th>
			</tr>';
		$List = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'tnt_carrier_'.$this->bqSQL($cat).'` ORDER BY `id_'.$this->bqSQL($cat).'`');
		$irow = 0;
		foreach ($List as $v)
		{
			$html .= '<tr '.($irow++ % 2 ? 'class="alt_row"' : '').'>
			<!--<td>'.$v['id_'.$cat.''].'</td>-->
			<td>'.$v[''.$cat.'_min'].'</td>
			<td>'.((float)$v[''.$cat.'_max'] == 0 ? '&infin;' : $v[''.$cat.'_max']).'</td>
			<td>'.$v['additionnal_charges'].'</td>
			<td>
			<a href="index.php?tab='.htmlentities(Tools::getValue('tab')).'&configure='.htmlentities(Tools::getValue('configure')).'&token='.htmlentities(Tools::getValue('token')).'&tab_module='.htmlentities(Tools::getValue('tab_module')).'&module_name='.htmlentities(Tools::getValue('module_name')).'&id_tab=3&section='.$cat.'&action=edit&'.$cat.'='.$v['id_'.$cat.''].'">
				<img src="../img/admin/edit.gif" alt="edit" title="'.$this->l('Edit').'"/></a>
				<a href="index.php?tab='.htmlentities(Tools::getValue('tab')).'&configure='.htmlentities(Tools::getValue('configure')).'&token='.htmlentities(Tools::getValue('token')).'&tab_module='.htmlentities(Tools::getValue('tab_module')).'&module_name='.htmlentities(Tools::getValue('module_name')).'&id_tab=3&section='.$cat.'&action=del&'.$cat.'='.$v['id_'.$cat.''].'">
				<img src="../img/admin/delete.gif" alt="delete" title="'.$this->l('Delete').'"/></a></td>
			</tr>';
		}
		$html .= '
		</table><br/>
		<div id="divForm'.htmlentities($cat).'Service">'.((Tools::getValue('action') == 'edit' || Tools::getValue('action') == 'new') && Tools::getValue('section') == $cat ? $this->_displayFormInfo($cat, htmlentities(Tools::getValue($cat))) : '').'</div>
		';

		return $html;
	}

	private function _displayCountry($country)
	{
		global $smarty;

		$var = array(
			'country' => $country,
			'overcost' => (Configuration::get('TNT_CARRIER_'.strtoupper(pSQL($country)).'_OVERCOST') ? Configuration::get('TNT_CARRIER_'.strtoupper(pSQL($country)).'_OVERCOST') : '0'),
			'action' => htmlentities(Tools::getValue('action')),
			'section' => htmlentities(Tools::getValue('section')),
			'getCountry' => htmlentities(Tools::getValue('country')),
			'form' => (htmlentities(Tools::getValue('country')) != '' ? $this->_displayFormCountry(htmlentities(Tools::getValue('country'))) : '')
		);
		$smarty->assign('varCountry', $var);
		return $this->display( __FILE__, 'tpl/country.tpl' );
	}

	private function _displayFormService($id = null)
	{
		global $smarty;
		$name = '';
		$description = '';
		$code = '';
		$charge = '';
		$display = '';

		if ($id != null)
		{
			$service = Db::getInstance()->getRow('SELECT c.deleted, c.name, l.delay, o.option, o.additionnal_charges
													FROM `'._DB_PREFIX_.'carrier` c, `'._DB_PREFIX_.'carrier_lang` l, `'._DB_PREFIX_.'tnt_carrier_option` o
													WHERE c.id_carrier = "'.(int)$id.'" AND c.id_carrier = l.id_carrier AND l.id_lang = "1" AND o.id_carrier = c.id_carrier');
			if ($service != NULL)
			{
				$name = $service['name'];
				$description = $service['delay'];
				$code = $service['option'];
				$charge = $service['additionnal_charges'];
				$display = $service['deleted'];
			}
		}
		$var = array('id' => $id,'name' => $name, 'description' => $description, 'code' => $code, 'charge' => $charge, 'display' => $display);
		$smarty->assign('varServiceForm', $var);
		return $this->display( __FILE__, 'tpl/serviceForm.tpl' );
	}

	private function _displayFormInfo($cat, $id = null)
	{
		$info_min = '';
		$info_max = '';
		$charge = '';

		if ($id != null)
		{
			$info = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'tnt_carrier_'.$this->bqSQL($cat).'` WHERE `id_'.$this->bqSQL($cat).'` = "'.(int)$id.'"');
			$info_min = $info[$cat.'_min'];
			$info_max = ((float)$info[$cat.'_max'] == 0 ? '': $info[$cat.'_max']);
			$charge = $info['additionnal_charges'];
		}

		$html = '
		<form action="index.php?tab='.htmlentities(Tools::getValue('tab')).'&configure='.htmlentities(Tools::getValue('configure')).'&token='.htmlentities(Tools::getValue('token')).'&tab_module='.htmlentities(Tools::getValue('tab_module')).'&module_name='.htmlentities(Tools::getValue('module_name')).'&id_tab=3&section='.$cat.'&action=new" method="post" class="form" id="configForm'.$cat.'">
			'.($id != null ? '<input type="hidden" name="'.$cat.'_id" value="'.$id.'"/>' : '').'
			<table class="table" cellspacing="0" cellpadding="0">
				<tr>
					<th>'.$this->l('Weight min').'</th><th>'.$this->l('Weight max (can be empty)').'</th><th>'.$this->l('Additionnal charge').'</th><th></th>
				</tr>
				<tr>
					<td><input type="text" name="tnt_carrier_'.$cat.'_min" size="20" value="'.$info_min.'"/></td>
					<td><input type="text" name="tnt_carrier_'.$cat.'_max" size="20" value="'.$info_max.'"/></td>
					<td><input type="text" name="tnt_carrier_'.$cat.'_charge" size="10" value="'.$charge.'"/></td>
					<td><input class="button" name="submitSave" type="submit" value="'.$this->l('save').'"></td>
				</tr>
			</table>
		</form>';

		return $html;
	}

	private function _displayFormCountry($country)
	{
		global $smarty;
		$var = array(
			'country' => htmlentities($country),
			'overcost' => Configuration::get('TNT_CARRIER_'.strtoupper(pSQL($country)).'_OVERCOST')
		);
		$smarty->assign('varCountryForm', $var);
		return $this->display( __FILE__, 'tpl/countryForm.tpl' );
	}

	private function _postValidation()
	{
		if (htmlentities(Tools::getValue('section')) == 'account')
			$this->_postValidationAccount();
		elseif (htmlentities(Tools::getValue('section')) == 'shipping')
			$this->_postValidationShipping();
		elseif (htmlentities(Tools::getValue('section')) == 'service')
			$this->_postValidationService();
		elseif (htmlentities(Tools::getValue('section')) == 'weight')
			$this->_postValidationInfo(htmlentities(Tools::getValue('section')));
		elseif (htmlentities(Tools::getValue('section')) == 'country')
			$this->_postValidationCountry();
	}

	private function _postProcess()
	{

	}

	private function _postValidationAccount()
	{
		$login = pSQL(Tools::getValue('tnt_carrier_login'));
		$password = pSQL(Tools::getValue('tnt_carrier_password'));
		$number = pSQL(str_replace(' ', '', Tools::getValue('tnt_carrier_number_account')));
		if (!$login || !$password || !$number)
			$this->_postErrors[] = $this->l('All the fields are required');
		Configuration::updateValue('TNT_CARRIER_LOGIN', $login);
		Configuration::updateValue('TNT_CARRIER_PASSWORD', $password);
		Configuration::updateValue('TNT_CARRIER_NUMBER_ACCOUNT', $number);
	}

	private function _postValidationShipping()
	{
		$collect = (Tools::getValue('tnt_carrier_shipping_collect') == 'on' ? 1 : 0);
		$company = pSQL(Tools::getValue('tnt_carrier_shipping_company'));
		$pex = pSQL(Tools::getValue('tnt_carrier_shipping_pex'));
		$lname = pSQL(Tools::getValue('tnt_carrier_shipping_last_name'));
		$fname = pSQL(Tools::getValue('tnt_carrier_shipping_first_name'));
		$address1 = pSQL(Tools::getValue('tnt_carrier_shipping_address1'));
		$address2 = pSQL(Tools::getValue('tnt_carrier_shipping_address2'));
		$postal_code = pSQL(Tools::getValue('tnt_carrier_shipping_postal_code'));
		$city = pSQL(Tools::getValue('tnt_carrier_shipping_city'));
		$email = pSQL(Tools::getValue('tnt_carrier_shipping_email'));
		$phone = pSQL(Tools::getValue('tnt_carrier_shipping_phone'));
		$closing = pSQL(Tools::getValue('tnt_carrier_shipping_closing'));
		$delivery = pSQL(Tools::getValue('tnt_carrier_shipping_delivery'));
		$print = pSQL(Tools::getValue('tnt_carrier_print_sticker'));

		if (!Configuration::get('TNT_CARRIER_LOGIN') || !Configuration::get('TNT_CARRIER_PASSWORD') || !Configuration::get('TNT_CARRIER_NUMBER_ACCOUNT'))
			$this->_postErrors[] = $this->l('You need a TNT account to complete your shipping address');
		/*if (!$collect && $pex == '')
			$this->_postErrors[] = $this->l('The pex code is missing');*/
		if ($collect && $company == '')
			$this->_postErrors[] = $this->l('Company name is missing');
		if ($collect && !$lname)
			$this->_postErrors[] = $this->l('Contact last name is missing');
		if ($collect && !$fname)
			$this->_postErrors[] = $this->l('Contact first name is missing');
		if (!$address1)
			$this->_postErrors[] = $this->l('Address is missing');
		if (isset($address2) && strlen($address2) >= 32)
			$this->_postErrors[] = $this->l('Address line 2 must be under 32 characters');
		if (!$postal_code)
			$this->_postErrors[] = $this->l('Postal code is missing');
		if (!$email)
			$this->_postErrors[] = $this->l('Contact email address is missing');
		if (!$phone)
			$this->_postErrors[] = $this->l('Contact phone number is missing');
		if ($collect && $closing == '')
			$this->_postErrors[] = $this->l('Company closing time is missing');

		if (Configuration::get('TNT_CARRIER_LOGIN') && Configuration::get('TNT_CARRIER_PASSWORD') && Configuration::get('TNT_CARRIER_NUMBER_ACCOUNT'))
		{
			if (strpos($city, 'PARIS') === 0 || strpos($city, 'MARSEILLE') === 0 || strpos($city, 'LYON') === 0)
			{
				$department = substr($postal_code, 0, 2);
				$code = substr($city, -2);
				$postal_code = $department.'0'.$code;
			}
			$tntWebService = new TntWebService();
			try
			{
				$verif = $tntWebService->verifCity($postal_code, $city);
			}
			catch (SoapFault $e)
			{
				$this->_postErrors[] = $this->l('Verify your authentication');
			}
			if (!isset($verif) || !$verif)
				$this->_postErrors[] = $this->l('The city is not compatible with the postal code');
		}

		Configuration::updateValue('TNT_CARRIER_SHIPPING_COLLECT', $collect);
		Configuration::updateValue('TNT_CARRIER_SHIPPING_COMPANY', $company);
		Configuration::updateValue('TNT_CARRIER_SHIPPING_LASTNAME', $lname);
		Configuration::updateValue('TNT_CARRIER_SHIPPING_FIRSTNAME', $fname);
		Configuration::updateValue('TNT_CARRIER_SHIPPING_ADDRESS1', $address1);
		Configuration::updateValue('TNT_CARRIER_SHIPPING_ADDRESS2', $address2);
		Configuration::updateValue('TNT_CARRIER_SHIPPING_ZIPCODE', $postal_code);
		Configuration::updateValue('TNT_CARRIER_SHIPPING_CITY', $city);
		Configuration::updateValue('TNT_CARRIER_SHIPPING_EMAIL', $email);
		Configuration::updateValue('TNT_CARRIER_SHIPPING_PHONE', $phone);
		Configuration::updateValue('TNT_CARRIER_SHIPPING_CLOSING', $closing);
		Configuration::updateValue('TNT_CARRIER_SHIPPING_DELIVERY', $delivery);
		Configuration::updateValue('TNT_CARRIER_SHIPPING_PEX', $pex);
		Configuration::updateValue('TNT_CARRIER_PRINT_STICKER', $print);
	}

	private function _postValidationService()
	{
		if (htmlentities(Tools::getValue('action')) == 'new' && htmlentities(Tools::getValue('service_id')) != null )
			$this->_postValidationEditService();
		elseif (htmlentities(Tools::getValue('action')) == 'new' && htmlentities(Tools::getValue('service_id')) == null)
			$this->_postValidationNewService();
	}

	private function _postValidationInfo($cat)
	{
		if (htmlentities(Tools::getValue('action')) == 'new' && htmlentities(Tools::getValue($cat.'_id')) != null )
			$this->_postValidationEditInfo($cat);
		elseif (htmlentities(Tools::getValue('action')) == 'new' && htmlentities(Tools::getValue($cat.'_id')) == null)
			$this->_postValidationNewInfo($cat);
	}

	private function _postValidationNewService()
	{
		$name = pSQL(Tools::getValue('tnt_carrier_service_name'));
		$description = pSQL(Tools::getValue('tnt_carrier_service_description'));
		$code = pSQL(Tools::getValue('tnt_carrier_service_code'));
		$charge = pSQL(Tools::getValue('tnt_carrier_service_charge'));
		$display = pSQL(Tools::getValue('tnt_carrier_service_display'));

		if ($name == '')
			$this->_postErrors[]  = $this->l('You have to give a name service');
		if ($code == '')
			$this->_postErrors[]  = $this->l('You have to give a code service');
		if ($description == '')
			$this->_postErrors[]  = $this->l('You have to give a description of the service');
		if (Configuration::get('TNT_CARRIER_'.$code.'_ID'))
			$this->_postErrors[]  = $this->l('The code service is already assign to one of your services');
		if ($display == '1')
			$delete = false;
		else
			$delete = true;

		if (!$this->_postErrors)
		{
			$carrierConfig = array(
				'name' => $name,
				'id_tax_rules_group' => 0,
				'active' => true,
				'deleted' => $delete,
				'shipping_handling' => false,
				'range_behavior' => 0,
				'delay' => array('fr' => $description, 'en' => $description, Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')) => $description),
				'id_zone' => 1,
				'is_module' => true,
				'shipping_external' => true,
				'external_module_name' => $this->_moduleName,
				'need_range' => true
			);
			$id_carrier = $this->installExternalCarrier($carrierConfig);

			Db::getInstance()->autoExecute(_DB_PREFIX_.'tnt_carrier_option',
				array('id_carrier' => (int)($id_carrier),
					'option' => $code,
					'additionnal_charges' => (float)($charge)),'INSERT');
			Configuration::updateValue('TNT_CARRIER_'.$code.'_ID', (int)($id_carrier));
			Configuration::updateValue('TNT_CARRIER_'.$code.'_OVERCOST', (float)($charge));
			$this->_fieldsList['TNT_CARRIER_'.$code.'_OVERCOST'] = (float)($charge);
			$this->_fieldsList['TNT_CARRIER_'.$code.'_ID'] = (float)($id_carrier);
			$this->_html .= $this->displayConfirmation($this->l('Service updated'));
		}
	}

	private function _postValidationEditService()
	{
		$id = (int)(Tools::getValue('service_id'));
		$name = pSQL(Tools::getValue('tnt_carrier_service_name'));
		$description = pSQL(Tools::getValue('tnt_carrier_service_description'));
		$code = pSQL(Tools::getValue('tnt_carrier_service_code'));
		$charge = pSQL(Tools::getValue('tnt_carrier_service_charge'));
		$display = pSQL(Tools::getValue('tnt_carrier_service_display'));

		if ($name == '')
			$this->_postErrors[]  = $this->l('You have to give a name service');
		if ($code == '')
			$this->_postErrors[]  = $this->l('You have to give a code service');
		if ($description == '')
			$this->_postErrors[]  = $this->l('You have to give a description of the service');
		if ($display == '1')
			$display = '0';
		else
			$display = '1';

		if (!$this->_postErrors)
		{
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'carrier` SET `name` = "'.$name.'", `deleted` = "'.(int)($display).'" WHERE `id_carrier` = '.(int)($id).'');
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'carrier_lang` SET `delay` = "'.$description.'" WHERE `id_carrier` = '.(int)($id).'');
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'tnt_carrier_option` SET `option` = "'.$code.'" WHERE `id_carrier` = '.(int)($id).'');
			Configuration::updateValue('TNT_CARRIER_'.$code.'_OVERCOST', (float)($charge));
			Configuration::updateValue('TNT_CARRIER_'.$code.'_ID', (int)($id));
			$this->_fieldsList['TNT_CARRIER_'.$code.'_OVERCOST'] = (float)($charge);
			$this->_fieldsList['TNT_CARRIER_'.$code.'_ID'] = (float)($id);
			$this->_html .= $this->displayConfirmation($this->l('Service updated'));
		}
	}

	private function _postValidationNewInfo($cat)
	{
		$info_min = Tools::getValue('tnt_carrier_'.$cat.'_min');
		$info_max = Tools::getValue('tnt_carrier_'.$cat.'_max');
		$charge = Tools::getValue('tnt_carrier_'.$cat.'_charge');
		Db::getInstance()->autoExecute(_DB_PREFIX_.'tnt_carrier_'.$cat.'',
			array(
				''.$cat.'_min' => (float)($info_min),
				''.$cat.'_max' => (float)($info_max),
				'additionnal_charges' => (float)($charge)),'INSERT');
		$this->_html .= $this->displayConfirmation($this->l('Service updated'));
	}

	private function _postValidationEditInfo($cat)
	{
		$id = (int)Tools::getValue($cat.'_id');
		$info_min = Tools::getValue('tnt_carrier_'.$cat.'_min');
		$info_max = Tools::getValue('tnt_carrier_'.$cat.'_max');
		$charge = Tools::getValue('tnt_carrier_'.$cat.'_charge');

		Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'tnt_carrier_'.$cat.'`
									SET `'.$cat.'_min` = "'.(float)($info_min).'",
									`'.$cat.'_max` = "'.(float)($info_max).'",
									`additionnal_charges` = "'.(float)$charge.'"
									WHERE `id_'.$cat.'` = '.(int)($id).'');

		$this->_html .= $this->displayConfirmation($this->l('Service updated'));
	}

	private function _postValidationCountry()
	{
		$country = pSQL(Tools::getValue('tnt_carrier_country'));
		$overcost = pSQL(Tools::getValue('tnt_carrier_'.$country.'_overcost'));

		Configuration::updateValue('TNT_CARRIER_'.strtoupper($country).'_OVERCOST', $overcost);
	}

	public function get_followup($shipping_number)
	{
		return false;
	}

	/*
	** Hook update carrier
	**
	*/

	public function hooknewOrder($params)
	{
		if (!$this->active)
			return ;
		$cart = new Cart($params['cart']->id);
		$order = $params['order'];
		$option = Db::getInstance()->getValue('SELECT `option`
												FROM `'._DB_PREFIX_.'tnt_carrier_option`
												WHERE `id_carrier` = "'.(int)$params['cart']->id_carrier.'"');
		if (isset($option) && strpos($option, 'D') !== false)
		{
			$dropOff = Db::getInstance()->getRow('SELECT `code`, `name`, `address`, `zipcode`, `city`
												FROM `'._DB_PREFIX_.'tnt_carrier_drop_off`
												WHERE `id_cart` = "'.(int)$params['cart']->id.'"');
			$alias = "tntRelaisColis".$order->id;
			$id_address = Db::getInstance()->getValue('SELECT id_address FROM `'._DB_PREFIX_.'address` WHERE `id_customer` = "'.(int)($params['cart']->id_customer).'" AND `alias` = "'.$alias.'"');
			if ($id_address > 0)
				$address_new = new Address($id_address);
			else
			{
				$address_old = new Address($cart->id_address_delivery);
				$address_new = new Address();
				$address_new->id_customer = $address_old->id_customer;
				$address_new->id_country = $address_old->id_country;
				$address_new->id_state = $address_old->id_state;
				$address_new->id_manufacturer = $address_old->id_manufacturer;
				$address_new->id_supplier = $address_old->id_supplier;
				$address_new->lastname = $address_old->lastname;
				$address_new->firstname = $address_old->firstname;
				$address_new->phone = $address_old->phone;
				$address_new->phone_mobile = $address_old->phone_mobile;
				$address_new->alias = $alias;
			}

			if (strlen($dropOff['name']) >= 32)
				$address_new->company = substr($dropOff['name'], 0, 31);
			else
				$address_new->company = $dropOff['name'];
			$address_new->address1 = $dropOff['address'];
			$address_new->postcode = $dropOff['zipcode'];
			$address_new->city = $dropOff['city'];
			$address_new->deleted = 1;
			$address_new->save();

			$cart->id_address_delivery = $address_new->id;
			$cart->save();

			$order->id_address_delivery = $address_new->id;
			$order->save();
		}
	}

	public function hookextraCarrier($params)
	{
		if (!$this->active)
			return ;
		if (!Configuration::get('TNT_CARRIER_LOGIN') || !Configuration::get('TNT_CARRIER_PASSWORD') || !Configuration::get('TNT_CARRIER_NUMBER_ACCOUNT'))
			return ;
		global $smarty;
		$id_cart = $params['cart']->id;
		$city = $this->putCityInNormeTnt($params['address']->city);
		$postal_code = $params['address']->postcode;

		if (serviceCache::getError($id_cart))
		{
			$smarty->assign('error', $this->l('The postal Code entered does not correspond to the city.').'<br/>'.$this->l('Please confirm the city below :'));
			$smarty->assign('postalCode', $postal_code);
			$postal = $postal_code;
			$cities = array();

			if ($postal == '75000')
			{
				for ($i = 1; $i <= 20; $i++)
				{
					if ($i < 10)
						$nb = '0'.$i;
					else
						$nb = $i;
					$cities[] = "PARIS ".$nb;
				}
			}
			else if ($postal == '69000')
			{
				for ($i = 1; $i < 10; $i++)
					$cities[] = "LYON ".$i;
			}
			else if ($postal == '13000')
			{
				for ($i = 1; $i <= 16; $i++)
				{
					if ($i < 10)
						$nb = '0'.$i;
					else
						$nb = $i;
					$cities[] = "MARSEILLE ".$nb;
				}
			}
			else
			{
				try
				{
					$tntWebService = new TntWebService();
					$city = $tntWebService->getCity($postal);
				}
				catch( SoapFault $e )
				{
					$erreur = $e->faultstring;
				}
				catch( Exception $e )
				{
					$erreur = "Problem : follow failed";
				}
				if (!isset($erreur) && isset($city->City))
				{
					if (is_array($city->City))
					{
						foreach ($city->City as $v)
							$cities[] = $v->name;
					}
					else
						$cities[] = $city->City->name;
				}
			}
			$link = new Link();
			$redirect = $link->getPageLink('order.php', false, null, 'step=2');
			$smarty->assign('redirect' , $redirect);
			if (!sizeof($cities))
				$smarty->assign('cityError', $this->l('your shipping address zipcode is not correct.'));
			$smarty->assign('cities', $cities);
		}
		$services = Db::getInstance()->ExecuteS('SELECT `id_carrier`, `option` FROM `'._DB_PREFIX_.'tnt_carrier_option`');
		$dueDate = serviceCache::getDueDate($id_cart, $services);
		
		$smarty->assign(
			array(
				'shop_url' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__,
				'id_cart' => $id_cart,
				'tnt_token' => Configuration::get('TNT_CARRIER_TOKEN'),
				'version' => _PS_VERSION_,
				'services' => $services,
				'dueDate' => $dueDate,
			)
		);
		
		$output = null;
		if (isset($this->context) && method_exists($this->context->controller, 'addJS'))
		{
			$this->context->controller->addJS('http://maps.google.com/maps/api/js?sensor=true');
			$this->context->controller->addJS($this->_path.'js/relais.js');
			$this->context->controller->addJS($this->_path.'js/jquery-ui-1.8.10.custom.min.js');
		}
		else
			$smarty->assign('js_include', true);
		
		return $output.$this->display(__FILE__, 'tpl/relaisColis.tpl');
	}

	public function hookadminOrder($params)
	{
		if (!$this->active)
			return false;
		global $currentIndex, $smarty;
		$table = 'order';
		$token = Tools::safeOutput(Tools::getValue('token'));
		$errorShipping = 0;
		if ($currentIndex == '')
			$currentIndex = 'index.php?controller='.Tools::safeOutput(Tools::getValue('controller'));
		$currentIndex .= "&id_order=".(int)($params['id_order']);
		$carrierName = Db::getInstance()->getRow('SELECT c.external_module_name FROM `'._DB_PREFIX_.'carrier` as c, `'._DB_PREFIX_.'orders` as o WHERE c.id_carrier = o.id_carrier AND o.id_order = "'.(int)($params['id_order']).'"');
		if ($carrierName!= null && $carrierName['external_module_name'] != $this->_moduleName)
			return false;
		if (!Configuration::get('TNT_CARRIER_LOGIN') || !Configuration::get('TNT_CARRIER_PASSWORD') || !Configuration::get('TNT_CARRIER_NUMBER_ACCOUNT'))
		{
			$var = array("error" => $this->l("You don't have a TNT account"),
						 'shipping_numbers' => '',
						 'sticker' => '');
			$smarty->assign('var', $var);
			return $this->display( __FILE__, 'tpl/shippingNumber.tpl' );
		}
		if (!Configuration::get('TNT_CARRIER_SHIPPING_COMPANY') || !Configuration::get('TNT_CARRIER_SHIPPING_ADDRESS1') || !Configuration::get('TNT_CARRIER_SHIPPING_ZIPCODE') || !Configuration::get('TNT_CARRIER_SHIPPING_CITY') || !Configuration::get('TNT_CARRIER_SHIPPING_EMAIL')
			|| !Configuration::get('TNT_CARRIER_SHIPPING_PHONE') || !Configuration::get('TNT_CARRIER_SHIPPING_CLOSING'))
			$errorShipping = 1;

		if ($errorShipping)
		{
			$var = array("error" => $this->l("You didn't give a collect address in the TNT module configuration"),
						 'shipping_numbers' => '',
						 'sticker' => '');
			$smarty->assign('var', $var);
			return $this->display( __FILE__, 'tpl/shippingNumber.tpl' );
		}
		$order = new Order($params['id_order']);

		$orderInfoTnt = new OrderInfoTnt((int)($params['id_order']));
		$info = $orderInfoTnt->getInfo();
		if (is_array($info) && isset($info[3]) && (strlen($info[3]['option']) == 1 || substr($info[3]['option'], 1, 1) == 'S'))
			$smarty->assign('weight', '30');
		else
			$smarty->assign('weight', '20');

		$products = $order->getProducts();
		$productWeight = array();

		foreach ($products as $product)
		{
			$p = new Product($product['product_id']);
			if ((float)$p->weight == 0 && (!isset($_POST['product_weight_'.$product['product_id']]) || (float)$_POST['product_weight_'.$product['product_id']] <= 0))
				$productWeight[] = array('id' => $product['product_id'], 'name' => $product['product_name']);
			else if (isset($_POST['product_weight_'.$product['product_id']]) && (float)$_POST['product_weight_'.$product['product_id']] > 0)
			{
				$p->weight = (float)$_POST['product_weight_'.$product['product_id']];
				$p->update();
			}
		}

		if (count($productWeight) > 0)
		{
			$var = array('currentIndex' => $currentIndex, 'table' => $table, 'token' => $token);
			$smarty->assign('var', $var);
			$smarty->assign('productWeight', $productWeight);
			return $this->display( __FILE__, 'tpl/weightForm.tpl' );
		}

		if (!is_array($info) && $info != false)
		{
			$var = array("error" => $info, "date" => '', "dateHidden" => '1', 'currentIndex' => $currentIndex, 'table' => $table, 'token' => $token);
			$smarty->assign('var', $var);
			return $this->display( __FILE__, 'tpl/formerror.tpl' );
		}

		$pack = new PackageTnt((int)($params['id_order']));
		if ($info[0]['shipping_number'] == '' && $pack->getOrder()->hasBeenShipped())
		{
			$tntWebService = new TntWebService();
			try
			{
				if (!isset($_POST['dateErrorOrder']))
					$orderInfoTnt->getDeleveryDate((int)$params['id_order'], $info);
				$package = $tntWebService->getPackage($info);
			}
			catch(SoapFault $e)
			{
				$errorFriendly = '';
				if (strrpos($e->faultstring, "shippingDate"))
					$dateError = date("Y-m-d");
				if (strrpos($e->faultstring, "receiver"))
				{
					$receiverError = 1;
					$errorFriendly = $this->l('Can you please modify the field').' '.substr($e->faultstring, strpos($e->faultstring, "receiver") + 9, strpos($e->faultstring, "'", strpos($e->faultstring, "receiver" ) - strpos($e->faultstring, "receiver")) + 1).' '.$this->l('in the box "shipping address" below.');
				}
				if (strrpos($e->faultstring, "sender"))
				{
					$senderError = 1;
					$errorFriendly = $this->l('Can you please modify the field').' '.substr($e->faultstring, strpos($e->faultstring, "sender") + 7, strpos($e->faultstring, "'", strpos($e->faultstring, "sender" ) - strpos($e->faultstring, "sender")) + 1).' '.$this->l('in your tnt module configuration.');
				}

				$error = $this->l("Problem : ") . $e->faultstring;
				$var = array("error" => $error, "errorFriendly" => $errorFriendly, "date" => (isset($dateError) ? $dateError : ''), 'currentIndex' => $currentIndex, 'table' => $table, 'token' => $token);
				$smarty->assign('var', $var);
				return $this->display( __FILE__, 'tpl/formerror.tpl' );
			}
			if (isset($package->Expedition->parcelResponses->parcelNumber))
			{
				$pack->setShippingNumber($package->Expedition->parcelResponses->parcelNumber);
				Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'tnt_package_history` (`id_order`, `pickup_date`) VALUES ("'.(int)$params['id_order'].'", "'.pSQL($info[2]['delivery_date']).'")');
			}
			else
				foreach ($package->Expedition->parcelResponses as $k => $v)
					$pack->setShippingNumber($v->parcelNumber);
			file_put_contents("../modules/".$this->_moduleName.'/pdf/'.$pack->getOrder()->shipping_number.'.pdf', $package->Expedition->PDFLabels);
		}
		if ($pack->getShippingNumber() != '')
		{
			$var = array(
				'error' => '',
				'shipping_numbers' => $pack->getShippingNumber(),
				'sticker' => "../modules/".$this->_moduleName.'/pdf/'.$pack->getOrder()->shipping_number.'.pdf',
				'date' => Db::getInstance()->getValue('SELECT `pickup_date` FROM `'._DB_PREFIX_.'tnt_package_history` WHERE `id_order` = "'.(int)$params['id_order'].'"'),
				'relay' => (isset($info[4]) ? $info[4]['name'].'<br/>'.$info[4]['address'].'<br/>'.$info[4]['zipcode'].' '.$info[4]['city']: ''),
				'place' => Configuration::get('TNT_CARRIER_SHIPPING_ADDRESS1')." ".Configuration::get('TNT_CARRIER_SHIPPING_ADDRESS2')."<br/>".Configuration::get('TNT_CARRIER_SHIPPING_ZIPCODE')." ".$this->putCityInNormeTnt(Configuration::get('TNT_CARRIER_SHIPPING_CITY')));
			$smarty->assign('var', $var);
			return $this->display( __FILE__, 'tpl/shippingNumber.tpl' );
		}
		return false;
	}

	public function hookorderDetailDisplayed($params)
	{
		if (!$this->active)
			return ;
		global $smarty;

		$tab = $params['order']->getFields();
		$shipping_number = $tab['shipping_number'];
		$id_carrier = $tab['id_carrier'];
		$erreur = null;
		$follow = array();
		$carrierName = Db::getInstance()->getRow('SELECT external_module_name FROM `'._DB_PREFIX_.'carrier` WHERE `id_carrier` = "'.(int)($id_carrier).'"');

		if ($carrierName != null && $carrierName['external_module_name'] == $this->_moduleName && $shipping_number != '')
		{
			$pack = new PackageTnt($params['order']->id);
			$numbers = $pack->getShippingNumber();
			$smarty->assign('numbers', $numbers);
			return $this->display( __FILE__, 'tpl/waitingFollow.tpl' );
		}
	}

	public function hookupdateCarrier($params)
	{
		if ((int)($params['id_carrier']) != (int)($params['carrier']->id))
		{
			$serviceSelected = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'tnt_carrier_option` WHERE `id_carrier` = '.(int)$params['id_carrier']);
			Configuration::updateValue('TNT_CARRIER_'.$serviceSelected['option'].'_ID', (int)($params['carrier']->id));
			$update = array('id_carrier' => (int)($params['carrier']->id));
			Db::getInstance()->autoExecute(_DB_PREFIX_.'tnt_carrier_option', $update, 'UPDATE', '`id_carrier` = '.(int)$params['id_carrier']);
		}
	}

	/*
	** Front Methods
	**
	** If you set need_range at true when you created your carrier (in install method), the method called by the cart will be getOrderShippingCost
	** If not, the method called will be getOrderShippingCostExternal
	**
	** $params var contains the cart, the customer, the address
	** $shipping_cost var contains the price calculated by the range in carrier tab
	**
	*/

	public function getOrderShippingCost($params, $shipping_cost)
	{
		if (!$this->active)
			return false;
		if (!Configuration::get('TNT_CARRIER_LOGIN') || !Configuration::get('TNT_CARRIER_PASSWORD') || !Configuration::get('TNT_CARRIER_NUMBER_ACCOUNT'))
			return false;
		if (!Configuration::get('TNT_CARRIER_SHIPPING_ADDRESS1') || !Configuration::get('TNT_CARRIER_SHIPPING_ZIPCODE') || !Configuration::get('TNT_CARRIER_SHIPPING_CITY'))
			return false;
		if (!extension_loaded('soap'))
			return false;
		
		$product = $params->getProducts();
		$weight = 0;
		$add = 0;
		$id_customer = $params->id_customer;
		$id_adress_delivery = $params->id_address_delivery;
		$info = Db::getInstance()->getRow('SELECT postcode, city, company FROM `'._DB_PREFIX_.'address` WHERE `id_address` = "'.(int)($id_adress_delivery).'"');
		foreach($product as $k => $v)
			$weight += (float)($v['weight'] * (int)$v['cart_quantity']);
		$serviceCache = new serviceCache($params->id, $info['postcode'], $info['city'], $info['company'], Configuration::get('TNT_CARRIER_SHIPPING_ZIPCODE'), Configuration::get('TNT_CARRIER_SHIPPING_CITY'));
		$serviceCache->clean();

		if (!$serviceCache->getFaisabilityAtThisTime())
		{
			$serviceCache->deletePreviousServices();
			$tntWebService = new TntWebService();
			$typeDestinataire = array();
			$typeDestinataire[] = 'INDIVIDUAL';
			$typeDestinataire[] = 'DROPOFFPOINT';
			if ($info['company'] != '')
				$typeDestinataire[] = 'ENTERPRISE';

			$faisability = $tntWebService->getFaisability($typeDestinataire, $info['postcode'], $this->putCityInNormeTnt($info['city']), date("Y-m-d", strtotime("now")));//"2012-05-02");
			if (!is_array($faisability) && strrpos($faisability, "(zip code / city)") === 0)
				$serviceCache->errorCodePostal();
			else if (is_array($faisability))
				$serviceCache->putInCache($faisability);
			if ($faisability == null)
				return false;
		}
		$service = $serviceCache->getServices();
		if ($service != NULL)
			foreach ($service as $v)
			{
				if (Configuration::get('TNT_CARRIER_'.pSQL($v['code']).'_ID'))
				{
					if (Configuration::get('TNT_CARRIER_'.pSQL($v['code']).'_ID') == $this->id_carrier)
						$priceCarrier = Configuration::get('TNT_CARRIER_'.pSQL($v['code']).'_OVERCOST');
				}
				else if (Configuration::get('TNT_CARRIER_'.substr(pSQL($v['code']), 0, 2).'_ID'))
					if (Configuration::get('TNT_CARRIER_'.substr(pSQL($v['code']), 0, 2).'_ID') == $this->id_carrier)
						$priceCarrier = Configuration::get('TNT_CARRIER_'.substr(pSQL($v['code']), 0, 2).'_OVERCOST');
			}
		$zero = 0;
		$weightLimit = Db::getInstance()->getRow('SELECT additionnal_charges FROM `'._DB_PREFIX_.'tnt_carrier_weight` WHERE `weight_min` < "'.(float)($weight).'" AND (`weight_max` > "'.(float)($weight).'" OR `weight_max` = "'.(float)$zero.'")');
		$currency = Db::getInstance()->getRow('SELECT conversion_rate FROM `'._DB_PREFIX_.'currency` WHERE `id_currency` = "'.(int)($params->id_currency).'"');
		if ($weightLimit != null)
			$add += (float)($weightLimit['additionnal_charges']);
		if (substr($info['postcode'], 0, 2) == "20")
			$add += (float)(Configuration::get('TNT_CARRIER_CORSE_OVERCOST'));
		if (isset($priceCarrier))
			return ((($priceCarrier + $add) * $currency['conversion_rate']) + $shipping_cost);
		return false;
	}

	public function getOrderShippingCostExternal($params)
	{
		return getOrderShippingCost($params, null);
	}

	public function putCityInNormeTnt($city)
	{
		$table = array('Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
						 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
						 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
						 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B');
	  $city =  mb_strtoupper($city);
		$city = strtr($city, $table);
		$old = array("SAINT", "-");
		$new = array("ST", " ");
		return (str_replace($old, $new, $city));
	}
}
