<?php
/*
* 2007-2013 PrestaShop
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
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class UspsCarrier extends CarrierModule
{
	public  $id_carrier;

	private $_html = '';
	private $_postErrors = array();
	private $_webserviceTestResult = '';
	private $_webserviceError = '';
	private $_fieldsList = array();
	private $_packagingSizeList = array();
	private $_packagingTypeList = array();
	private $_machinableList = array();
	private $_calculModeList = array();
	private $_dimensionUnit = '';
	private $_weightUnit = '';
	private $_dimensionUnitList = array('CM' => 'CM', 'IN' => 'IN', 'CMS' => 'CM', 'INC' => 'IN');
	private $_weightUnitList = array('KG' => 'KGS', 'KGS' => 'KGS', 'LBS' => 'LBS', 'LB' => 'LBS');
	private $_moduleName = 'uspscarrier';

	private $debug = false;

	public function __construct()
	{
		$this->name = 'uspscarrier';
		$this->tab = 'shipping_logistics';
		$this->version = '1.3.2';
		$this->author = 'PrestaShop';
		$this->limited_countries = array('us');
		$this->module_key = '9ac173da9614868dbd15c56cf8ad008a';

		parent::__construct ();

		$this->displayName = $this->l('U.S.P.S. Rate Calulator');
		$this->description = $this->l('Calculates shipping rates for United States Postal Service for Domestic shipping within the USA.');

		/** Backward compatibility 1.4 / 1.5 */
		require(dirname(__FILE__).'/backward_compatibility/backward.php');

		if (self::isInstalled($this->name))
		{
			// Loading Var
			$warning = array();
			$this->loadingVar();

			// Check Configuration Values
			foreach ($this->_fieldsList as $keyConfiguration => $tab)
				if (!Configuration::get($keyConfiguration) && isset($tab['name']))
				{
					if (isset($tab['default']))
						Configuration::updateValue($keyConfiguration, $tab['default']);
					else
						$warning[] = '\''.$tab['name'].'\' ';
				}

			// Checking Unit
			$this->_dimensionUnit = isset($this->_dimensionUnitList[strtoupper(Configuration::get('PS_DIMENSION_UNIT'))]) ? $this->_dimensionUnitList[strtoupper(Configuration::get('PS_DIMENSION_UNIT'))] : false;
			$this->_weightUnit = isset($this->_weightUnitList[strtoupper(Configuration::get('PS_WEIGHT_UNIT'))]) ? $this->_weightUnitList[strtoupper(Configuration::get('PS_WEIGHT_UNIT'))] : false;
			if (!$this->_weightUnit || !$this->_weightUnitList[$this->_weightUnit])
				$warning[] = $this->l('\'Weight Unit (LB or KG).\'').' ';
			if (!$this->_dimensionUnit || !$this->_dimensionUnitList[$this->_dimensionUnit])
				$warning[] = $this->l('\'Dimension Unit (CM or IN).\'').' ';

			// Generate Warnings
			if (count($warning))
				$this->warning .= implode(' , ',$warning).$this->l('must be configured to use this module correctly').' ';
		}
	}

	public function loadingVar()
	{
		// Loading Fields List
		$this->_fieldsList = array(
			'USPS_CARRIER_USER_ID' => array('name' => $this->l('USPS User ID')),
			'USPS_CARRIER_POSTAL_CODE' => array('name' => $this->l('USPS Zip Code')),
			'USPS_CARRIER_PACKAGING_WEIGHT' => array('name' => $this->l('Packaging weight'), 'default' => '0.00'),
			'USPS_CARRIER_HANDLING_FEE' => array('name' => $this->l('Handling fee'), 'default' => '0.00'),
			'USPS_CARRIER_PACKAGING_SIZE' => array('name' => $this->l('USPS Packaging Size'), 'default' => 'REGULAR'),
			'USPS_CARRIER_PACKAGING_TYPE' => array('name' => $this->l('USPS Packaging Type'), 'default' => 'VARIABLE'),
			'USPS_CARRIER_MACHINABLE' => array('name' => $this->l('USPS Machinable'), 'default' => 'False'),
			'USPS_CARRIER_CALCULATION_MODE' => array('name' => $this->l('USPS Calculation Mode'), 'default' => 'split'),
		);

		// Loading packaging size list
		$this->_packagingSizeList = array(
			'REGULAR' => $this->l('Regular'),
			'LARGE' => $this->l('Large')
		);

		// Loading packaging type list
		$this->_packagingTypeList = array(
			'VARIABLE' => $this->l('Variable'),
			'FLAT RATE BOX' => $this->l('Flat rate box'),
			'MD FLAT RATE BOX' => $this->l('MD flat rate box'),
			'FLAT RATE ENVELOPE' => $this->l('Flat rate envelope'),
			'SM FLAT RATE BOX' => $this->l('SM flat rate box'),
			'LG FLAT RATE BOX' => $this->l('LG flat rate box'),
			'RECTANGULAR' => $this->l('Rectangular'),
			'NONRECTANGULAR' => $this->l('Non rectangular')
		);

		// Loading machinable list
		$this->_machinableList = array(
			'FALSE' => $this->l('False'),
			'TRUE' => $this->l('True')
		);

		// Loading calculation mode list
		$this->_calculModeList = array(
			'onepackage' => $this->l('All items in one package'),
			'split' => $this->l('Split one item per package')
		);
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
			if (!Db::getInstance()->execute($s))
				return false;

		// Install Carriers
		$this->installCarriers();

		// Install Module
		if (!parent::install() OR !$this->registerHook('updateCarrier'))
			return false;

		return true;
	}

	public function uninstall()
	{
		// Uninstall Carriers
		$exists = Db::getInstance()->executeS('SHOW TABLES LIKE "'._DB_PREFIX_.'usps_rate_service_code"');
		if (count($exists))
			Db::getInstance()->autoExecute(_DB_PREFIX_.'carrier', array('deleted' => 1), 'UPDATE', '`external_module_name` = \'uspscarrier\' OR `id_carrier` IN (SELECT DISTINCT(`id_carrier`) FROM `'._DB_PREFIX_.'usps_rate_service_code`)');

		// Uninstall Config
		foreach ($this->_fieldsList as $keyConfiguration => $name)
			Configuration::deleteByName($keyConfiguration);

		// Uninstall SQL
		include(dirname(__FILE__).'/sql-uninstall.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->execute($s))
				return false;

		// Uninstall Module
		return parent::uninstall();
	}

	public function disable($forceAll = false)
	{
		// Disable Carriers
		$exists = Db::getInstance()->executeS('SHOW TABLES LIKE "'._DB_PREFIX_.'usps_rate_service_code"');
		if (count($exists))
			Db::getInstance()->autoExecute(_DB_PREFIX_.'carrier', array('active' => 0), 'UPDATE', '`external_module_name` = \'uspscarrier\' OR `id_carrier` IN (SELECT DISTINCT(`id_carrier`) FROM `'._DB_PREFIX_.'usps_rate_service_code`)');	
		return parent::disable($forceAll);
	}

	public function enable($forceAll = false)
	{
		// Disable Carriers
		$exists = Db::getInstance()->executeS('SHOW TABLES LIKE "'._DB_PREFIX_.'usps_rate_service_code"');
		if (count($exists))
			Db::getInstance()->autoExecute(_DB_PREFIX_.'carrier', array('active' => 1), 'UPDATE', '`external_module_name` = \'uspscarrier\' OR `id_carrier` IN (SELECT DISTINCT(`id_carrier`) FROM `'._DB_PREFIX_.'usps_rate_service_code`)');	
		return parent::enable($forceAll);
	}

	public function installCarriers()
	{
		// Unactive all USPS Carriers
		Db::getInstance()->autoExecute(_DB_PREFIX_.'usps_rate_service_code', array('active' => 0), 'UPDATE');

		// Get all services availables
		$rateServiceList = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'usps_rate_service_code`');
		foreach ($rateServiceList as $rateService)
			if (!$rateService['id_carrier'])
			{
				$config = array(
					'name' => $rateService['service'],
					'id_tax_rules_group' => 0,
					'active' => true,
					'deleted' => 0,
					'shipping_handling' => false,
					'range_behavior' => 0,
					'delay' => array('fr' => $rateService['service'], 'en' => $rateService['service'], Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')) => $rateService['service']),
					'id_zone' => 1,
					'is_module' => true,
					'shipping_external' => true,
					'external_module_name' => $this->_moduleName,
					'need_range' => true
				);
				$id_carrier = $this->installExternalCarrier($config);
				Db::getInstance()->autoExecute(_DB_PREFIX_.'usps_rate_service_code', array('id_carrier' => (int)($id_carrier), 'id_carrier_history' => (int)($id_carrier)), 'UPDATE', '`id_usps_rate_service_code` = '.(int)($rateService['id_usps_rate_service_code']));
			}
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

		$languages = Language::getLanguages(true);
		foreach ($languages as $language)
		{
			if ($language['iso_code'] == 'fr')
				$carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
			if ($language['iso_code'] == 'en')
				$carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
			if ($language['iso_code'] == Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')))
				$carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
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

			$zones = Zone::getZones(true);
			foreach ($zones as $zone)
			{
				Db::getInstance()->autoExecute(_DB_PREFIX_.'carrier_zone', array('id_carrier' => (int)($carrier->id), 'id_zone' => (int)($zone['id_zone'])), 'INSERT');
				Db::getInstance()->autoExecuteWithNullValues(_DB_PREFIX_.'delivery', array('id_carrier' => (int)($carrier->id), 'id_range_price' => (int)($rangePrice->id), 'id_range_weight' => NULL, 'id_zone' => (int)($zone['id_zone']), 'price' => '0'), 'INSERT');
				Db::getInstance()->autoExecuteWithNullValues(_DB_PREFIX_.'delivery', array('id_carrier' => (int)($carrier->id), 'id_range_price' => NULL, 'id_range_weight' => (int)($rangeWeight->id), 'id_zone' => (int)($zone['id_zone']), 'price' => '0'), 'INSERT');
			}

			// Copy Logo
			if (!copy(dirname(__FILE__).'/carrier.jpg', _PS_SHIP_IMG_DIR_.'/'.(int)$carrier->id.'.jpg'))
				return false;

			// Return ID Carrier
			return (int)($carrier->id);
		}

		return false;
	}



	/*
	** Global Form Config Methods
	**
	*/

	public function getContent()
	{
		$this->_html .= '<h2>' . $this->l('U.S.P.S. Rate Calculator').'</h2>';
		if (!empty($_POST) AND Tools::isSubmit('submitSave'))
		{
			$this->_postValidation();
			if (!sizeof($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors AS $err)
					$this->_html .= '<div class="alert error"><img src="'._PS_IMG_.'admin/forbbiden.gif" alt="nok" />&nbsp;'.$err.'</div>';
		}
		$this->_displayForm();
		return $this->_html;
	}

	private function _displayForm()
	{
		$this->_html .= '<fieldset>
		<legend><img src="'.$this->_path.'img/delivery.gif" alt="" /> '.$this->l('USPS Module Status').'</legend>';

		$alert = array();
		$this->_webserviceTestResult = $this->webserviceTest();
		if (!Configuration::get('USPS_CARRIER_USER_ID'))
			$alert['generalSettings'] = 1;
		if (Db::getInstance()->getValue('SELECT * FROM `'._DB_PREFIX_.'usps_rate_service_code` WHERE `active` = 1') < 1)
			$alert['deliveryServices'] = 1;
		if (!$this->_webserviceTestResult)
			$alert['webserviceTest'] = 1;


		if (!count($alert))
			$this->_html .= '<img src="'._PS_IMG_.'admin/module_install.png" /><strong>'.$this->l('U.S.P.S. Rate Calculator is configured and online!').'</strong>';
		else
		{
			$this->_html .= '<img src="'._PS_IMG_.'admin/warn2.png" /><strong>'.$this->l('U.S.P.S. Rate Calculator is not configured yet, you have to :').'</strong>';
			$this->_html .= '<br />'.(isset($alert['generalSettings']) ? '<img src="'._PS_IMG_.'admin/warn2.png" />' : '<img src="'._PS_IMG_.'admin/module_install.png" />').' 1) '.$this->l('Fill in USPS User ID and Orignation Zip Code on the "General Settings" form');
			$this->_html .= '<br />'.(isset($alert['deliveryServices']) ? '<img src="'._PS_IMG_.'admin/warn2.png" />' : '<img src="'._PS_IMG_.'admin/module_install.png" />').' 2) '.$this->l('Select your available Delivery Services');
			$this->_html .= '<br />'.(isset($alert['webserviceTest']) ? '<img src="'._PS_IMG_.'admin/warn2.png" />' : '<img src="'._PS_IMG_.'admin/module_install.png" />').' 3) '.$this->l('Webservice test connection').($this->_webserviceError ? ' : '.$this->_webserviceError : '');
		}

		$this->_html .= '</fieldset><div class="clear">&nbsp;</div>';
		$this->_html .= $this->_displayFormConfig();
	}

	private function _postValidation()
	{
		if (Tools::getValue('section') == 'general')
			$this->_postValidationGeneral();
		elseif (Tools::getValue('section') == 'category')
			$this->_postValidationCategory();
		elseif (Tools::getValue('section') == 'product')
			$this->_postValidationProduct();
	}

	private function _postProcess()
	{
		if (Tools::getValue('section') == 'general')
			$this->_postProcessGeneral();
		elseif (Tools::getValue('section') == 'category')
			$this->_postProcessCategory();
		elseif (Tools::getValue('section') == 'product')
			$this->_postProcessProduct();
	}




	/*
	** General Form Config Methods
	**
	*/

	private function _displayFormConfig()
	{
		$html = '
		<ul id="menuTab">
				<li id="menuTab1" class="menuTabButton selected">1. '.$this->l('General Settings').'</li>
				<li id="menuTab2" class="menuTabButton">2. '.$this->l('Categories Settings').'</li>
				<li id="menuTab3" class="menuTabButton">3. '.$this->l('Products Settings').'</li>
				<li id="menuTab4" class="menuTabButton">4. '.$this->l('Help').'</li>
			</ul>
			<div id="tabList">
				<div id="menuTab1Sheet" class="tabItem selected">'.$this->_displayFormGeneral().'</div>
				<div id="menuTab2Sheet" class="tabItem">'.$this->_displayFormCategory().'</div>
				<div id="menuTab3Sheet" class="tabItem">'.$this->_displayFormProduct().'</div>
				<div id="menuTab4Sheet" class="tabItem">'.$this->_displayHelp().'</div>
			</div>
			<br clear="left" />
			<br />
			<style>
				#menuTab { float: left; padding: 0; margin: 0; text-align: left; }
				#menuTab li { text-align: left; float: left; display: inline; padding: 5px; padding-right: 10px; background: #EFEFEF; font-weight: bold; cursor: pointer; border-left: 1px solid #EFEFEF; border-right: 1px solid #EFEFEF; border-top: 1px solid #EFEFEF; }
				#menuTab li.menuTabButton.selected { background: #FFF6D3; border-left: 1px solid #CCCCCC; border-right: 1px solid #CCCCCC; border-top: 1px solid #CCCCCC; }
				#tabList { clear: left; }
				.tabItem { display: none; }
				.tabItem.selected { display: block; background: #FFFFF0; border: 1px solid #CCCCCC; padding: 10px; padding-top: 20px; }
			</style>
			<script>
				$(".menuTabButton").click(function () {
				  $(".menuTabButton.selected").removeClass("selected");
				  $(this).addClass("selected");
				  $(".tabItem.selected").removeClass("selected");
				  $("#" + this.id + "Sheet").addClass("selected");
				});
			</script>
		';
		if (isset($_GET['id_tab']))
			$html .= '<script>
				  $(".menuTabButton.selected").removeClass("selected");
				  $("#menuTab'.Tools::getValue('id_tab').'").addClass("selected");
				  $(".tabItem.selected").removeClass("selected");
				  $("#menuTab'.Tools::getValue('id_tab').'Sheet").addClass("selected");
			</script>';
		return $html;
	}

	private function _displayFormGeneral()
	{
		$configCurrency = new Currency((int)Configuration::get('PS_CURRENCY_DEFAULT'));

		$html = '<script>
			$(document).ready(function() {
				var country = $("#usps_carrier_country");
				country.change(function() {
					if ($("#usps_carrier_state_" + country.val()))
					{
						$(".stateInput.selected").removeClass("selected");
						if ($("#usps_carrier_state_" + country.val()).size())
							$("#usps_carrier_state_" + country.val()).addClass("selected");
						else
							$("#usps_carrier_state_none").addClass("selected");
					}
				});

				$("#configForm").submit(function() {
					$("#usps_carrier_state").val($(".stateInput.selected").val());
				});
			});
			</script>
			<style>
				.stateInput { display: none; }
				.stateInput.selected { display: block; }
			</style>


			<form action="index.php?tab='.Tools::getValue('tab').'&configure='.Tools::getValue('configure').'&token='.Tools::getValue('token').'&tab_module='.Tools::getValue('tab_module').'&module_name='.Tools::getValue('module_name').'&id_tab=1&section=general" method="post" class="form" id="configForm">

				<fieldset style="border: 0px;">
					<h4>'.$this->l('General configuration').' :</h4>
					<label>'.$this->l('Your USPS User ID').' : </label>
					<div class="margin-form">
						<input type="text" size="20" name="usps_carrier_user_id" value="'.Tools::getValue('usps_carrier_user_id', Configuration::get('USPS_CARRIER_USER_ID')).'" />
						<p><a href="http://www.usps.com/webtools/" target="_blank">' . $this->l('Please click here to get your USPS API Key.') . '</a></p>
					</div>
					<label>'.$this->l('Origination Zip Code').' : </label>
					<div class="margin-form"><input type="text" size="20" name="usps_carrier_postal_code" value="'.Tools::getValue('usps_carrier_postal_code', Configuration::get('USPS_CARRIER_POSTAL_CODE')).'" /></div><br />
					<label>'.$this->l('Delivery Services').' : </label>
					<div class="margin-form">';
					$rateServiceList = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'usps_rate_service_code`');
				        foreach($rateServiceList as $rateService)
						$html .= '<input type="checkbox" name="service[]" value="'.$rateService['id_usps_rate_service_code'].'" '.(($rateService['active'] == 1) ? 'checked="checked"' : '').' /> '.$rateService['service'].' '.('COMMERCIAL' == substr($rateService['code'], -10) ? '['.$this->l('COMMERCIAL RATE').']' : '['.$this->l('REGULAR RATE').']').' '.($this->webserviceTest($rateService['code']) ? '('.$this->l('Available').')' : '('.$this->l('Not available').')').'<input type="hidden" name="service_validate['.$rateService['id_usps_rate_service_code'].']" value="'.$rateService['code'].'"><br />';
					$html .= '<p>'.$this->l('Choose the Delivery Services you want to be available to your customers.').'<br />
						'.$this->l('For First-Class, Priority and Express Mail you have the option of either COMMERCIAL RATE or REGULAR RATE.').'<br />
						'.$this->l('-If you pay for postage either online or with a postage meter then choose COMMERCIAL RATE.').'<br />
						'.$this->l('-If you pay for postage at the post office then choose REGULAR RATE.').'<br />
						'.$this->l('-Choose only the COMMERCIAL RATE or the REGULAR RATE. Do not choose both.').'<br />
						'.$this->l('For Parcel Post, Media Mail and Library Mail they will display as REGULAR RATE only since there is no difference in price.').'</p>
					</div>
					<label>'.$this->l('Packaging Weight').' : </label>
					<div class="margin-form">
						<input type="text" size="5" name="usps_carrier_packaging_weight" value="'.Tools::getValue('usps_carrier_packaging_weight', Configuration::get('USPS_CARRIER_PACKAGING_WEIGHT')).'" />
						'.Tools::getValue('ps_weight_unit', Configuration::get('PS_WEIGHT_UNIT')).'
					</div>
					<label>'.$this->l('Handling Fee').' : </label>
					<div class="margin-form">
						<input type="text" size="5" name="usps_carrier_handling_fee" value="'.Tools::getValue('usps_carrier_handling_fee', Configuration::get('USPS_CARRIER_HANDLING_FEE')).'" />
						'.$configCurrency->sign.'
					</div>
				</fieldset>

				<fieldset style="border: 0px;">
					<h4>'.$this->l('Localization configuration').' :</h4>
					<label>'.$this->l('Weight unit').' : </label>
					<div class="margin-form">
						<input type="text" size="20" name="ps_weight_unit" value="'.Tools::getValue('ps_weight_unit', Configuration::get('PS_WEIGHT_UNIT')).'" />
						<p>'.$this->l('The weight unit of your shop (eg. kg or lbs)').'</p>
					</div>
					<label>'.$this->l('Dimension unit').' : </label>
					<div class="margin-form">
						<input type="text" size="20" name="ps_dimension_unit" value="'.Tools::getValue('ps_dimension_unit', Configuration::get('PS_DIMENSION_UNIT')).'" />
						<p>'.$this->l('The dimension unit of your shop (eg. cm or in)').'</p>
					</div>
				</fieldset>

				<fieldset style="border: 0px;">
					<h4>'.$this->l('Service configuration').' :</h4>
					<label>'.$this->l('Packaging Size').' : </label>
						<div class="margin-form">
							<select name="usps_carrier_packaging_size">';
								foreach($this->_packagingSizeList as $kpickup => $vpickup)
									$html .= '<option value="'.$kpickup.'" '.($kpickup == (Tools::getValue('usps_carrier_packaging_size', Configuration::get('USPS_CARRIER_PACKAGING_SIZE'))) ? 'selected="selected"' : '').'>'.$vpickup.'</option>';
					$html .= '</select>
					<p>' . $this->l('Select packaging size from within the list.') . '</p>
					</div>
					<label>'.$this->l('Packaging Type').' : </label>
						<div class="margin-form">
							<select name="usps_carrier_packaging_type">';
								foreach($this->_packagingTypeList as $kpackaging => $vpackaging)
									$html .= '<option value="'.$kpackaging.'" '.($kpackaging == (Tools::getValue('usps_carrier_packaging_type', Configuration::get('USPS_CARRIER_PACKAGING_TYPE'))) ? 'selected="selected"' : '').'>'.$vpackaging.'</option>';
					$html .= '</select>
					<p>' . $this->l('Select packaging type from within the list.') . '</p>
					</div>
					<label>'.$this->l('Machinable').' : </label>
						<div class="margin-form">
							<select name="usps_carrier_machinable">';
								$idmachinables = array();
								foreach($this->_machinableList as $kmachinable => $vmachinable)
									$html .= '<option value="'.$kmachinable.'" '.($kmachinable == (Tools::getValue('usps_carrier_machinable', Configuration::get('USPS_CARRIER_MACHINABLE'))) ? 'selected="selected"' : '').'>'.$vmachinable.'</option>';
					$html .= '</select>
					<p>' . $this->l('Select if it is machinable or not by default.') . '</p>
					</div>
					<label>'.$this->l('Calculation Mode').' : </label>
						<div class="margin-form">
							<select name="usps_carrier_calculation_mode">';
								$idcalculmode = array();
								foreach($this->_calculModeList as $kcalculmode => $vcalculmode)
									$html .= '<option value="'.$kcalculmode.'" '.($kcalculmode == (Tools::getValue('usps_carrier_calculation_mode', Configuration::get('USPS_CARRIER_CALCULATION_MODE'))) ? 'selected="selected"' : '').'>'.$vcalculmode.'</option>';
					$html .= '</select>
					<p>' . $this->l('Using Calculation Mode "All items in one package" will automatically use default packaging size, packaging type and delivery services. Specific configurations for categories or products won\'t be used.') . '</p>
					</div>
				</fieldset>

				<div class="margin-form"><input class="button" name="submitSave" type="submit" value="Configure"></div>
			</form>

			<script>
				var id_country = '.(int)(Tools::getValue('usps_carrier_country', Configuration::get('USPS_CARRIER_COUNTRY'))).';
				if ($("#usps_carrier_state_" + id_country))
				{
					$(".stateInput.selected").removeClass("selected");
					if ($("#usps_carrier_state_" + id_country).size())
						$("#usps_carrier_state_" + id_country).addClass("selected");
					else
						$("#usps_carrier_state_none").addClass("selected");
				}
			</script>';
		return $html;
	}

	private function _postValidationGeneral()
	{
		// Check configuration values
		if (Tools::getValue('usps_carrier_user_id') == NULL)
			$this->_postErrors[]  = $this->l('Your USPS User ID is not specified');
		elseif (Tools::getValue('usps_carrier_postal_code') == NULL)
			$this->_postErrors[]  = $this->l('Your Origination Zip Code is not specified');
	        elseif (Tools::getValue('usps_carrier_postal_code') < 1 || Tools::getValue('usps_carrier_postal_code') > 99999 || !is_numeric(Tools::getValue('usps_carrier_postal_code')) || !ctype_digit(Tools::getValue('usps_carrier_postal_code')) || strlen(Tools::getValue('usps_carrier_postal_code')) != 5)
			$this->_postErrors[]  = $this->l('Your Origination Zip Code is not valid');
	        elseif (Tools::getValue('usps_carrier_packaging_weight') < 0 || !is_numeric(Tools::getValue('usps_carrier_packaging_weight')))
			$this->_postErrors[]  = $this->l('The Packaging Weight must be "equal to" or "greater than" 0');
		elseif (Tools::getValue('usps_carrier_handling_fee') < 0 || !is_numeric(Tools::getValue('usps_carrier_handling_fee')))
			$this->_postErrors[]  = $this->l('The Handling Fee must be "equal to" or "greater than" 0');
        	elseif (!Tools::getValue('service'))
			$this->_postErrors[]  = $this->l('You must choose at least one Delivery Service');

		// Validate the Delivery Services to make sure that only one of COMMERCIAL rate or REGULAR rate was chosen
		$usps_service_validate_all = Tools::getValue('service_validate');
		$usps_service_validate_picks = Tools::getValue('service');
		$usps_service_validate_error = false;
		if (count($usps_service_validate_picks) > 1)
		{
			// There has to be two or more services picked for there to be a problem
			foreach ($usps_service_validate_all as $usps_sva_id => $usps_sva_code)
			{
				// Loop thru list of all services
				$usps_was_picked = false;
				foreach ($usps_service_validate_picks as $usps_picked)
				{
					// See if it's one of the checked services
					if ($usps_sva_id == $usps_picked)
						$usps_was_picked = true;
				}
                		if ($usps_was_picked)
				{
					// If it was checked, then continue
					foreach ($usps_service_validate_picks as $usps_pick)
					{
						//Loop thru checked services only
						if ($usps_sva_id != $usps_pick)
						{
							// Don't compare it to itself
							$usps_str_len = strlen($usps_sva_code);
							$usps_pick_start = substr($usps_service_validate_all[$usps_pick], 0, $usps_str_len);
							// Compare the first part of the CODE to see if match (ex: PRIORITY == PRIORITY)
                            				if ($usps_pick_start == $usps_sva_code)
								$usps_service_validate_error = true;
						}
                       			}
                   		}
               		}
            	}
		if ($usps_service_validate_error)
			$this->_postErrors[]  = $this->l('You must pick either COMMERCIAL RATE or REGULAR RATE for each mail type. You cannot choose both.');



		// Check usps webservice availibity
		if (!$this->_postErrors)
		{
			// Unactive all USPS Carriers
			Db::getInstance()->autoExecute(_DB_PREFIX_.'usps_rate_service_code', array('active' => 0), 'UPDATE');

			// If no errors appear, the carrier is being activated, else, the carrier is being deactivated
			if (!$this->_postErrors)
			{
				// Get available services
				$serviceSelected = Tools::getValue('service');

				// Active available carrier
				if ($serviceSelected)
					foreach ($serviceSelected as $ss)
					{
						$id_carrier = Db::getInstance()->getValue('SELECT `id_carrier` FROM `'._DB_PREFIX_.'usps_rate_service_code` WHERE `id_usps_rate_service_code` = '.(int)($ss));
						Db::getInstance()->autoExecute(_DB_PREFIX_.'usps_rate_service_code', array('active' => 1), 'UPDATE', '`id_usps_rate_service_code` = '.(int)($ss));
					}
			}

			// All new configurations values are saved to be sure to test webservices with it
			Configuration::updateValue('USPS_CARRIER_USER_ID', Tools::getValue('usps_carrier_user_id'));
			Configuration::updateValue('USPS_CARRIER_PACKAGING_WEIGHT', Tools::getValue('usps_carrier_packaging_weight'));
			Configuration::updateValue('USPS_CARRIER_HANDLING_FEE', Tools::getValue('usps_carrier_handling_fee'));
			Configuration::updateValue('USPS_CARRIER_PACKAGING_SIZE', Tools::getValue('usps_carrier_packaging_size'));
			Configuration::updateValue('USPS_CARRIER_PACKAGING_TYPE', Tools::getValue('usps_carrier_packaging_type'));
			Configuration::updateValue('USPS_CARRIER_MACHINABLE', Tools::getValue('usps_carrier_machinable'));
			Configuration::updateValue('USPS_CARRIER_POSTAL_CODE', Tools::getValue('usps_carrier_postal_code'));
			Configuration::updateValue('USPS_CARRIER_CALCULATION_MODE', Tools::getValue('usps_carrier_calculation_mode'));
			Configuration::updateValue('PS_WEIGHT_UNIT', $this->_weightUnitList[strtoupper(Tools::getValue('ps_weight_unit'))]);
			Configuration::updateValue('PS_DIMENSION_UNIT', $this->_dimensionUnitList[strtoupper(Tools::getValue('ps_dimension_unit'))]);
			if (isset($this->_weightUnitList[strtoupper(Tools::getValue('ps_weight_unit'))]))
				$this->_weightUnit = $this->_weightUnitList[strtoupper(Tools::getValue('ps_weight_unit'))];
			if (isset($this->_dimensionUnitList[strtoupper(Tools::getValue('ps_dimension_unit'))]))
				$this->_dimensionUnit = $this->_dimensionUnitList[strtoupper(Tools::getValue('ps_dimension_unit'))];
			if (!$this->webserviceTest())
				$this->_postErrors[]  = $this->l('Prestashop could not connect to USPS webservices').' :<br />'.($this->_webserviceError ? $this->_webserviceError : $this->l('No error description found'));
		}
	}

	private function _postProcessGeneral()
	{
		// Saving new configurations
		if (Configuration::updateValue('USPS_CARRIER_USER_ID', Tools::getValue('usps_carrier_user_id')) AND
			Configuration::updateValue('USPS_CARRIER_PACKAGING_WEIGHT', Tools::getValue('usps_carrier_packaging_weight')) AND
			Configuration::updateValue('USPS_CARRIER_HANDLING_FEE', Tools::getValue('usps_carrier_handling_fee')) AND
			Configuration::updateValue('USPS_CARRIER_PACKAGING_SIZE', Tools::getValue('usps_carrier_packaging_size')) AND
			Configuration::updateValue('USPS_CARRIER_PACKAGING_TYPE', Tools::getValue('usps_carrier_packaging_type')) AND
			Configuration::updateValue('USPS_CARRIER_MACHINABLE', Tools::getValue('usps_carrier_machinable')) AND
			Configuration::updateValue('USPS_CARRIER_POSTAL_CODE', Tools::getValue('usps_carrier_postal_code')) AND
			Configuration::updateValue('USPS_CARRIER_CALCULATION_MODE', Tools::getValue('usps_carrier_calculation_mode')) AND
			Configuration::updateValue('PS_WEIGHT_UNIT', $this->_weightUnitList[strtoupper(Tools::getValue('ps_weight_unit'))]) AND
			Configuration::updateValue('PS_DIMENSION_UNIT', $this->_dimensionUnitList[strtoupper(Tools::getValue('ps_dimension_unit'))]))
			$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
		else
			$this->_html .= $this->displayErrors($this->l('Settings failed'));
	}



	/*
	** Category Form Config Methods
	**
	*/

	private function _getPathInTab($id_category)
	{
		$category = Db::getInstance()->getRow('
		SELECT id_category, level_depth, nleft, nright
		FROM '._DB_PREFIX_.'category
		WHERE id_category = '.(int)$id_category);

		if (isset($category['id_category']))
		{
			$categories = Db::getInstance()->executeS('
			SELECT c.id_category, cl.name, cl.link_rewrite
			FROM '._DB_PREFIX_.'category c
			LEFT JOIN '._DB_PREFIX_.'category_lang cl ON (cl.id_category = c.id_category'.(version_compare(_PS_VERSION_, '1.5.0') >= 0 ? ' '.$this->context->shop->addSqlRestrictionOnLang('cl') : '').')
			WHERE c.nleft <= '.(int)$category['nleft'].' AND c.nright >= '.(int)$category['nright'].' AND cl.id_lang = '.(int)($this->context->language->id).'
			ORDER BY c.level_depth ASC
			LIMIT '.(int)($category['level_depth'] + 1));

			$n = 1;
			$pathTab = array();
			$nCategories = (int)sizeof($categories);
			foreach ($categories AS $category)
				$pathTab[] = htmlentities($category['name'], ENT_NOQUOTES, 'UTF-8');

			return $pathTab;
		}
	}

	private function _getChildCategories($categories, $id, $path = array(), $pathAdd = '')
	{
		$html = '';
		if ($pathAdd != '')
			$path[] = $pathAdd;
		if (isset($categories[$id]))
			foreach ($categories[$id] as $idc => $cc)
			{
				$html .= '<option value="'.$cc['infos']['id_category'].'" '.($cc['infos']['id_category'] == (int)(Tools::getValue('id_category')) ? 'selected="selected"' : '').'>';
				if ($path)
					foreach ($path as $p)
						$html .= $p.' > ';
				$html .= $cc['infos']['name'];
				$html .= '</option>';
				$html .= $this->_getChildCategories($categories, $idc, $path, $cc['infos']['name']);
			}
		return $html;
	}

	private function _isPostCheck($id_usps_rate_service_code)
	{
		$services = Tools::getValue('service');
		if ($services)
			foreach ($services as $s)
				if ($s == $id_usps_rate_service_code)
					return 1;
		return 0;
	}

	private function _displayFormCategory()
	{
		// Check if the module is configured
		if (!$this->_webserviceTestResult)
			return '<p><b>'.$this->l('You must configure "General Settings" before using this tab.').'</b></p><br />';

		// Display header
		$html = '<p><b>'.$this->l('In this tab, you can set a specific configuration for each category.').'</b></p><br />
		<table class="table tableDnD" cellpadding="0" cellspacing="0" width="90%">
			<thead>
				<tr class="nodrag nodrop">
					<th>'.$this->l('ID Config').'</th>
					<th>'.$this->l('Category').'</th>
					<th>'.$this->l('Packaging type').'</th>
					<th>'.$this->l('Packaging size').'</th>
					<th>'.$this->l('Machinable').'</th>
					<th>'.$this->l('Additional charges').'</th>
					<th>'.$this->l('Services').'</th>
					<th>'.$this->l('Actions').'</th>
				</tr>
			</thead>
			<tbody>';

		// Loading config list
		$configCategoryList = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'usps_rate_config` WHERE `id_category` > 0');
		if (!$configCategoryList)
			$html .= '<tr><td colspan="6">'.$this->l('There is no specific USPS configuration for categories at this point.').'</td></tr>';
		foreach ($configCategoryList as $k => $c)
		{
			// Category Path
			$path = '';
			$pathTab = $this->_getPathInTab($c['id_category']);
			foreach ($pathTab as $p)
			{
				if (!empty($path)) { $path .= ' > '; }
				$path .= $p;
			}

			// Loading config currency
			$configCurrency = new Currency($c['id_currency']);

			// Loading services attached to this config
			$services = '';
			$servicesTab = Db::getInstance()->executeS('
			SELECT ursc.`service`
			FROM `'._DB_PREFIX_.'usps_rate_config_service` urcs
			LEFT JOIN `'._DB_PREFIX_.'usps_rate_service_code` ursc ON (ursc.`id_usps_rate_service_code` = urcs.`id_usps_rate_service_code`)
			WHERE urcs.`id_usps_rate_config` = '.(int)$c['id_usps_rate_config']);
			foreach ($servicesTab as $s)
				$services .= $s['service'].'<br />';

			// Display line
			$alt = 0;
			if ($k % 2 != 0)
				$alt = ' class="alt_row"';
			$html .= '
				<tr'.$alt.'>
					<td>'.$c['id_usps_rate_config'].'</td>
					<td>'.$path.'</td>
					<td>'.(isset($this->_packagingTypeList[$c['packaging_type_code']]) ? $this->_packagingTypeList[$c['packaging_type_code']] : '-').'</td>
					<td>'.(isset($this->_packagingSizeList[$c['packaging_size_code']]) ? $this->_packagingSizeList[$c['packaging_size_code']] : '-').'</td>
					<td>'.(isset($this->_machinableList[$c['machinable_code']]) ? $this->_machinableList[$c['machinable_code']] : '-').'</td>
					<td>'.$c['additional_charges'].' '.$configCurrency->sign.'</td>
					<td>'.$services.'</td>
					<td>
						<a href="index.php?tab='.Tools::getValue('tab').'&configure='.Tools::getValue('configure').'&token='.Tools::getValue('token').'&tab_module='.Tools::getValue('tab_module').'&module_name='.Tools::getValue('module_name').'&id_tab=2&section=category&action=edit&id_usps_rate_config='.(int)($c['id_usps_rate_config']).'" style="float: left;">
							<img src="'._PS_IMG_.'admin/edit.gif" />
						</a>
						<form action="index.php?tab='.Tools::getValue('tab').'&configure='.Tools::getValue('configure').'&token='.Tools::getValue('token').'&tab_module='.Tools::getValue('tab_module').'&module_name='.Tools::getValue('module_name').'&id_tab=2&section=category&action=delete&id_usps_rate_config='.(int)($c['id_usps_rate_config']).'&id_category='.(int)($c['id_category']).'" method="post" class="form" style="float: left;">
							<input name="submitSave" type="image" src="'._PS_IMG_.'admin/delete.gif" OnClick="return confirm(\''.$this->l('Are you sure you want to delete this specific USPS configuration for this category ?').'\');" />
						</form>
					</td>
				</tr>';
		}

		$html .= '
			</tbody>
		</table><br /><br />';

		// Add or Edit Category Configuration
		if (Tools::getValue('action') == 'edit' && Tools::getValue('section') == 'category')
		{
			// Loading config
			$configSelected = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'usps_rate_config` WHERE `id_usps_rate_config` = '.(int)(Tools::getValue('id_usps_rate_config')));

			// Category Path
			$path = '';
			$pathTab = $this->_getPathInTab($configSelected['id_category']);
			foreach ($pathTab as $p)
			{
				if (!empty($path)) { $path .= ' > '; }
				$path .= $p;
			}

			$html .= '<p align="center"><b>'.$this->l('Update a rule').' (<a href="index.php?tab='.Tools::getValue('tab').'&configure='.Tools::getValue('configure').'&token='.Tools::getValue('token').'&tab_module='.Tools::getValue('tab_module').'&module_name='.Tools::getValue('module_name').'&id_tab=2&section=category&action=add">'.$this->l('Add a rule').' ?</a>)</b></p>
					<form action="index.php?tab='.Tools::getValue('tab').'&configure='.Tools::getValue('configure').'&token='.Tools::getValue('token').'&tab_module='.Tools::getValue('tab_module').'&module_name='.Tools::getValue('module_name').'&id_tab=2&section=category&action=edit&id_usps_rate_config='.(int)(Tools::getValue('id_usps_rate_config')).'" method="post" class="form">
						<label>'.$this->l('Category').' :</label>
						<div class="margin-form" style="padding: 0.2em 0.5em 0 0; font-size: 12px;">'.$path.' <input type="hidden" name="id_category" value="'.(int)($configSelected['id_category']).'" /></div><br clear="left" />
						<label>'.$this->l('Packaging Type').' : </label>
							<div class="margin-form">
								<select name="packaging_type_code">
									<option value="0">'.$this->l('Select a packaging type ...').'</option>';
									foreach($this->_packagingTypeList as $kpackaging => $vpackaging)
										$html .= '<option value="'.$kpackaging.'" '.($kpackaging == pSQL(Tools::getValue('packaging_type_code', $configSelected['packaging_type_code'])) ? 'selected="selected"' : '').'>'.$vpackaging.'</option>';
						$html .= '</select>
						</div>
						<label>'.$this->l('Packaging Size').' : </label>
							<div class="margin-form">
								<select name="packaging_size_code">
									<option value="0">'.$this->l('Select a packaging size ...').'</option>';
									foreach($this->_packagingSizeList as $kpackaging => $vpackaging)
										$html .= '<option value="'.$kpackaging.'" '.($kpackaging == pSQL(Tools::getValue('packaging_size_code', $configSelected['packaging_size_code'])) ? 'selected="selected"' : '').'>'.$vpackaging.'</option>';
						$html .= '</select>
						</div>
						<label>'.$this->l('Machinable').' : </label>
							<div class="margin-form">
								<select name="machinable_code">
									<option value="0">'.$this->l('Select a machinable ...').'</option>';
									foreach($this->_machinableList as $kpackaging => $vpackaging)
										$html .= '<option value="'.$kpackaging.'" '.($kpackaging == pSQL(Tools::getValue('machinable_code', $configSelected['machinable_code'])) ? 'selected="selected"' : '').'>'.$vpackaging.'</option>';
						$html .= '</select>
						</div>
						<label>'.$this->l('Additional charges').' : </label>
						<div class="margin-form"><input type="text" size="20" name="additional_charges" value="'.Tools::getValue('additional_charges', $configSelected['additional_charges']).'" /></div><br />
						<label>'.$this->l('Delivery Service').' : </label>
							<div class="margin-form">';
								$rateServiceList = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'usps_rate_service_code`');
								foreach($rateServiceList as $rateService)
								{
									$configServiceSelected = Db::getInstance()->getValue('SELECT `id_usps_rate_service_code` FROM `'._DB_PREFIX_.'usps_rate_config_service` WHERE `id_usps_rate_config` = '.(int)(Tools::getValue('id_usps_rate_config')).' AND `id_usps_rate_service_code` = '.(int)($rateService['id_usps_rate_service_code']));
									$html .= '<input type="checkbox" name="service[]" value="'.$rateService['id_usps_rate_service_code'].'" '.(($this->_isPostCheck($rateService['id_usps_rate_service_code']) == 1 || $configServiceSelected > 0) ? 'checked="checked"' : '').' /> '.$rateService['service'].' '.('COMMERCIAL' == substr($rateService['code'], -10) ? '['.$this->l('COMMERCIAL RATE').']' : '['.$this->l('REGULAR RATE').']').'<br />';
								}
						$html .= '
						<p>' . $this->l('Choose the delivery service available to your customers.') . '</p>
						</div>
						<div class="margin-form"><input class="button" name="submitSave" type="submit"></div>
					</form>';
		}
		else
		{
			$html .= '<p align="center"><b>'.$this->l('Add a rule').'</b></p>
					<form action="index.php?tab='.Tools::getValue('tab').'&configure='.Tools::getValue('configure').'&token='.Tools::getValue('token').'&tab_module='.Tools::getValue('tab_module').'&module_name='.Tools::getValue('module_name').'&id_tab=2&section=category&action=add" method="post" class="form">
						<label>'.$this->l('Category').' : </label>
						<div class="margin-form">
							<select name="id_category">
								<option value="0">'.$this->l('Select a category ...').'</option>
								'.$this->_getChildCategories(Category::getCategories($this->context->language->id), 0).'
							</select>
						</div>
						<label>'.$this->l('Packaging Type').' : </label>
							<div class="margin-form">
								<select name="packaging_type_code">
									<option value="0">'.$this->l('Select a packaging type ...').'</option>';
									foreach($this->_packagingTypeList as $kpackaging => $vpackaging)
										$html .= '<option value="'.$kpackaging.'" '.($kpackaging === pSQL(Tools::getValue('packaging_type_code')) ? 'selected="selected"' : '').'>'.$vpackaging.'</option>';
						$html .= '</select>
						</div>
						<label>'.$this->l('Packaging Size').' : </label>
							<div class="margin-form">
								<select name="packaging_size_code">
									<option value="0">'.$this->l('Select a packaging size ...').'</option>';
									foreach($this->_packagingSizeList as $kpackaging => $vpackaging)
										$html .= '<option value="'.$kpackaging.'" '.($kpackaging == pSQL(Tools::getValue('packaging_size_code')) ? 'selected="selected"' : '').'>'.$vpackaging.'</option>';
						$html .= '</select>
						</div>
						<label>'.$this->l('Machinable').' : </label>
							<div class="margin-form">
								<select name="machinable_code">
									<option value="0">'.$this->l('Select a machinable ...').'</option>';
									foreach($this->_machinableList as $kpackaging => $vpackaging)
										$html .= '<option value="'.$kpackaging.'" '.($kpackaging == pSQL(Tools::getValue('machinable_code')) ? 'selected="selected"' : '').'>'.$vpackaging.'</option>';
						$html .= '</select>
						</div>
						<label>'.$this->l('Additional charges').' : </label>
						<div class="margin-form"><input type="text" size="20" name="additional_charges" value="'.Tools::getValue('additional_charges').'" /></div><br />
						<label>'.$this->l('Delivery Service').' : </label>
							<div class="margin-form">';
								$rateServiceList = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'usps_rate_service_code`');
								foreach($rateServiceList as $rateService)
									$html .= '<input type="checkbox" name="service[]" value="'.$rateService['id_usps_rate_service_code'].'" '.(($this->_isPostCheck($rateService['id_usps_rate_service_code']) == 1) ? 'checked="checked"' : '').' /> '.$rateService['service'].' '.('COMMERCIAL' == substr($rateService['code'], -10) ? '['.$this->l('COMMERCIAL RATE').']' : '['.$this->l('REGULAR RATE').']').'<br />';
						$html .= '
						<p>' . $this->l('Choose the delivery service available to your customers.') . '</p>
						</div>
						<div class="margin-form"><input class="button" name="submitSave" type="submit"></div>
					</form>';
		}

		return $html;
	}

	private function _postValidationCategory()
	{
		// Check post values
		if (Tools::getValue('id_category') == NULL)
			$this->_postErrors[]  = $this->l('You have to select a category.');

		if (!$this->_postErrors)
		{
			$id_usps_rate_config = Db::getInstance()->getValue('SELECT `id_usps_rate_config` FROM `'._DB_PREFIX_.'usps_rate_config` WHERE `id_category` = '.(int)Tools::getValue('id_category'));

			// Check if a config does not exist in Add case
			if ($id_usps_rate_config > 0 && Tools::getValue('action') == 'add')
				$this->_postErrors[]  = $this->l('This category already has a specific USPS configuration.');

			// Check if a config exists and if the IDs config correspond in Upd case
			if (Tools::getValue('action') == 'edit' && (!isset($id_usps_rate_config) || $id_usps_rate_config != Tools::getValue('id_usps_rate_config')))
				$this->_postErrors[]  = $this->l('An error occurred, please try again.');

			// Check if a config exists in Delete case
			if (Tools::getValue('action') == 'delete' && !isset($id_usps_rate_config))
				$this->_postErrors[]  = $this->l('An error occurred, please try again.');
		}
	}

	private function _postProcessCategory()
	{
		// Init Var
		$date = date('Y-m-d H:i:s');
		$services = Tools::getValue('service');

		// Add Script
		if (Tools::getValue('action') == 'add')
		{
			$addTab = array(
				'id_product' => 0,
				'id_category' => (int)(Tools::getValue('id_category')),
				'id_currency' => (int)(Configuration::get('PS_CURRENCY_DEFAULT')),
				'packaging_type_code' => pSQL(Tools::getValue('packaging_type_code')),
				'packaging_size_code' => pSQL(Tools::getValue('packaging_size_code')),
				'machinable_code' => pSQL(Tools::getValue('machinable_code')),
				'additional_charges' => pSQL(Tools::getValue('additional_charges')),
				'date_add' => pSQL($date),
				'date_upd' => pSQL($date)
			);
			Db::getInstance()->autoExecute(_DB_PREFIX_.'usps_rate_config', $addTab, 'INSERT');
			$id_usps_rate_config = Db::getInstance()->Insert_ID();
			foreach ($services as $s)
			{
				$addTab = array('id_usps_rate_service_code' => pSQL($s), 'id_usps_rate_config' => (int)$id_usps_rate_config, 'date_add' => pSQL($date), 'date_upd' => pSQL($date));
				Db::getInstance()->autoExecute(_DB_PREFIX_.'usps_rate_config_service', $addTab, 'INSERT');
			}

			// Display Results
			if ($id_usps_rate_config > 0)
				$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
			else
				$this->_html .= $this->displayErrors($this->l('Settings failed'));
		}

		// Update Script
		if (Tools::getValue('action') == 'edit' && Tools::getValue('id_usps_rate_config'))
		{
			$updTab = array(
				'id_currency' => (int)(Configuration::get('PS_CURRENCY_DEFAULT')),
				'packaging_type_code' => pSQL(Tools::getValue('packaging_type_code')),
				'packaging_size_code' => pSQL(Tools::getValue('packaging_size_code')),
				'machinable_code' => pSQL(Tools::getValue('machinable_code')),
				'additional_charges' => pSQL(Tools::getValue('additional_charges')),
				'date_upd' => pSQL($date)
			);
			$result = Db::getInstance()->autoExecute(_DB_PREFIX_.'usps_rate_config', $updTab, 'UPDATE', '`id_usps_rate_config` = '.(int)Tools::getValue('id_usps_rate_config'));
			Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'usps_rate_config_service` WHERE `id_usps_rate_config` = '.(int)Tools::getValue('id_usps_rate_config'));
			foreach ($services as $s)
			{
				$addTab = array('id_usps_rate_service_code' => pSQL($s), 'id_usps_rate_config' => (int)Tools::getValue('id_usps_rate_config'), 'date_add' => pSQL($date), 'date_upd' => pSQL($date));
				Db::getInstance()->autoExecute(_DB_PREFIX_.'usps_rate_config_service', $addTab, 'INSERT');
			}

			// Display Results
			if ($result)
				$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
			else
				$this->_html .= $this->displayErrors($this->l('Settings failed'));
		}

		// Delete Script
		if (Tools::getValue('action') == 'delete' && Tools::getValue('id_usps_rate_config'))
		{
			$result1 = Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'usps_rate_config` WHERE `id_usps_rate_config` = '.(int)Tools::getValue('id_usps_rate_config'));
			$result2 = Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'usps_rate_config_service` WHERE `id_usps_rate_config` = '.(int)Tools::getValue('id_usps_rate_config'));

			// Display Results
			if ($result1)
				$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
			else
				$this->_html .= $this->displayErrors($this->l('Settings failed'));
		}
	}



	/*
	** Product Form Config Methods
	**
	*/

	private function _displayFormProduct()
	{
		// Check if the module is configured
		if (!$this->_webserviceTestResult)
			return '<p><b>'.$this->l('You must configure "General Settings" before using this tab.').'</b></p><br />';

		// Display header
		$html = '<p><b>'.$this->l('In this tab, you can set a specific configuration for each product.').'</b></p><br />
		<table class="table tableDnD" cellpadding="0" cellspacing="0" width="90%">
			<thead>
				<tr class="nodrag nodrop">
					<th>'.$this->l('ID Config').'</th>
					<th>'.$this->l('Product').'</th>
					<th>'.$this->l('Packaging type').'</th>
					<th>'.$this->l('Packaging size').'</th>
					<th>'.$this->l('Machinable').'</th>
					<th>'.$this->l('Additional charges').'</th>
					<th>'.$this->l('Services').'</th>
					<th>'.$this->l('Actions').'</th>
				</tr>
			</thead>
			<tbody>';

		// Loading config list
		$configProductList = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'usps_rate_config` WHERE `id_product` > 0');
		if (!$configProductList)
			$html .= '<tr><td colspan="6">'.$this->l('There is no specific USPS configuration for products at this point.').'</td></tr>';
		foreach ($configProductList as $k => $c)
		{
			// Loading Product
			$product = new Product((int)$c['id_product'], false, (int)$this->context->language->id);

			// Loading config currency
			$configCurrency = new Currency($c['id_currency']);

			// Loading services attached to this config
			$services = '';
			$servicesTab = Db::getInstance()->executeS('
			SELECT ursc.`service`
			FROM `'._DB_PREFIX_.'usps_rate_config_service` urcs
			LEFT JOIN `'._DB_PREFIX_.'usps_rate_service_code` ursc ON (ursc.`id_usps_rate_service_code` = urcs.`id_usps_rate_service_code`)
			WHERE urcs.`id_usps_rate_config` = '.(int)$c['id_usps_rate_config']);
			foreach ($servicesTab as $s)
				$services .= $s['service'].'<br />';

			// Display line
			$alt = 0;
			if ($k % 2 != 0)
				$alt = ' class="alt_row"';
			$html .= '
				<tr'.$alt.'>
					<td>'.$c['id_usps_rate_config'].'</td>
					<td>'.$product->name.'</td>
					<td>'.(isset($this->_packagingTypeList[$c['packaging_type_code']]) ? $this->_packagingTypeList[$c['packaging_type_code']] : '-').'</td>
					<td>'.(isset($this->_packagingSizeList[$c['packaging_size_code']]) ? $this->_packagingSizeList[$c['packaging_size_code']] : '-').'</td>
					<td>'.(isset($this->_machinableList[$c['machinable_code']]) ? $this->_machinableList[$c['machinable_code']] : '-').'</td>
					<td>'.$c['additional_charges'].' '.$configCurrency->sign.'</td>
					<td>'.$services.'</td>
					<td>
						<a href="index.php?tab='.Tools::getValue('tab').'&configure='.Tools::getValue('configure').'&token='.Tools::getValue('token').'&tab_module='.Tools::getValue('tab_module').'&module_name='.Tools::getValue('module_name').'&id_tab=3&section=product&action=edit&id_usps_rate_config='.(int)($c['id_usps_rate_config']).'" style="float: left;">
							<img src="'._PS_IMG_.'admin/edit.gif" />
						</a>
						<form action="index.php?tab='.Tools::getValue('tab').'&configure='.Tools::getValue('configure').'&token='.Tools::getValue('token').'&tab_module='.Tools::getValue('tab_module').'&module_name='.Tools::getValue('module_name').'&id_tab=3&section=product&action=delete&id_usps_rate_config='.(int)($c['id_usps_rate_config']).'&id_product='.(int)($c['id_product']).'" method="post" class="form" style="float: left;">
							<input name="submitSave" type="image" src="'._PS_IMG_.'admin/delete.gif" OnClick="return confirm(\''.$this->l('Are you sure you want to delete this specific USPS configuration for this product ?').'\');" />
						</form>
					</td>
				</tr>';
		}

		$html .= '
			</tbody>
		</table><br /><br />';

		// Add or Edit Product Configuration
		if (Tools::getValue('action') == 'edit' && Tools::getValue('section') == 'product')
		{
			// Loading config
			$configSelected = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'usps_rate_config` WHERE `id_usps_rate_config` = '.(int)(Tools::getValue('id_usps_rate_config')));
			$product = new Product((int)$configSelected['id_product'], false, (int)$this->context->language->id);

			$html .= '<p align="center"><b>'.$this->l('Update a rule').' (<a href="index.php?tab='.Tools::getValue('tab').'&configure='.Tools::getValue('configure').'&token='.Tools::getValue('token').'&tab_module='.Tools::getValue('tab_module').'&module_name='.Tools::getValue('module_name').'&id_tab=3&section=product&action=add">'.$this->l('Add a rule').' ?</a>)</b></p>
					<form action="index.php?tab='.Tools::getValue('tab').'&configure='.Tools::getValue('configure').'&token='.Tools::getValue('token').'&tab_module='.Tools::getValue('tab_module').'&module_name='.Tools::getValue('module_name').'&id_tab=3&section=product&action=edit&id_usps_rate_config='.(int)(Tools::getValue('id_usps_rate_config')).'" method="post" class="form">
						<label>'.$this->l('Product').' :</label>
						<div class="margin-form" style="padding: 0.2em 0.5em 0 0; font-size: 12px;">'.$product->name.' <input type="hidden" name="id_product" value="'.(int)($configSelected['id_product']).'" /></div><br clear="left" />
						<label>'.$this->l('Packaging Type').' : </label>
							<div class="margin-form">
								<select name="packaging_type_code">
									<option value="0">'.$this->l('Select a packaging type ...').'</option>';
									foreach($this->_packagingTypeList as $kpackaging => $vpackaging)
										$html .= '<option value="'.$kpackaging.'" '.($kpackaging == pSQL(Tools::getValue('packaging_type_code', $configSelected['packaging_type_code'])) ? 'selected="selected"' : '').'>'.$vpackaging.'</option>';
						$html .= '</select>
						</div>
						<label>'.$this->l('Packaging Size').' : </label>
							<div class="margin-form">
								<select name="packaging_size_code">
									<option value="0">'.$this->l('Select a packaging size ...').'</option>';
									foreach($this->_packagingSizeList as $kpackaging => $vpackaging)
										$html .= '<option value="'.$kpackaging.'" '.($kpackaging == pSQL(Tools::getValue('packaging_size_code', $configSelected['packaging_size_code'])) ? 'selected="selected"' : '').'>'.$vpackaging.'</option>';
						$html .= '</select>
						</div>
						<label>'.$this->l('Machinable').' : </label>
							<div class="margin-form">
								<select name="machinable_code">
									<option value="0">'.$this->l('Select a machinable ...').'</option>';
									foreach($this->_machinableList as $kpackaging => $vpackaging)
										$html .= '<option value="'.$kpackaging.'" '.($kpackaging == pSQL(Tools::getValue('machinable_code', $configSelected['machinable_code'])) ? 'selected="selected"' : '').'>'.$vpackaging.'</option>';
						$html .= '</select>
						</div>
						<label>'.$this->l('Additional charges').' : </label>
						<div class="margin-form"><input type="text" size="20" name="additional_charges" value="'.Tools::getValue('additional_charges', $configSelected['additional_charges']).'" /></div><br />
						<label>'.$this->l('Delivery Service').' : </label>
							<div class="margin-form">';
								$rateServiceList = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'usps_rate_service_code`');
								foreach($rateServiceList as $rateService)
								{
									$configServiceSelected = Db::getInstance()->getValue('SELECT `id_usps_rate_service_code` FROM `'._DB_PREFIX_.'usps_rate_config_service` WHERE `id_usps_rate_config` = '.(int)(Tools::getValue('id_usps_rate_config')).' AND `id_usps_rate_service_code` = '.(int)($rateService['id_usps_rate_service_code']));
									$html .= '<input type="checkbox" name="service[]" value="'.$rateService['id_usps_rate_service_code'].'" '.(($this->_isPostCheck($rateService['id_usps_rate_service_code']) == 1 || $configServiceSelected > 0) ? 'checked="checked"' : '').' /> '.$rateService['service'].' '.('COMMERCIAL' == substr($rateService['code'], -10) ? '['.$this->l('COMMERCIAL RATE').']' : '['.$this->l('REGULAR RATE').']').'<br />';
								}
						$html .= '
						<p>' . $this->l('Choose the delivery service available to your customers.') . '</p>
						</div>
						<div class="margin-form"><input class="button" name="submitSave" type="submit"></div>
					</form>';
		}
		else
		{
			$html .= '<p align="center"><b>'.$this->l('Add a rule').'</b></p>
					<form action="index.php?tab='.Tools::getValue('tab').'&configure='.Tools::getValue('configure').'&token='.Tools::getValue('token').'&tab_module='.Tools::getValue('tab_module').'&module_name='.Tools::getValue('module_name').'&id_tab=3&section=product&action=add" method="post" class="form">
						<label>'.$this->l('Product').' : </label>
						<div class="margin-form">
							<select name="id_product">
								<option value="0">'.$this->l('Select a product ...').'</option>';
						$productsList = Db::getInstance()->executeS('
						SELECT pl.* FROM `'._DB_PREFIX_.'product` p
						LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.`id_product` = p.`id_product` AND pl.`id_lang` = '.(int)$this->context->language->id.(version_compare(_PS_VERSION_, '1.5.0') >= 0 ? ' '.$this->context->shop->addSqlRestrictionOnLang('pl') : '').')
						WHERE p.`active` = 1
						ORDER BY pl.`name`');
						foreach ($productsList as $product)
							$html .= '<option value="'.$product['id_product'].'" '.($product['id_product'] == (int)(Tools::getValue('id_product')) ? 'selected="selected"' : '').'>'.$product['name'].'</option>';
						$html .= '</select>
						</div>
						<label>'.$this->l('Packaging Type').' : </label>
							<div class="margin-form">
								<select name="packaging_type_code">
									<option value="0">'.$this->l('Select a packaging type ...').'</option>';
									foreach($this->_packagingTypeList as $kpackaging => $vpackaging)
										$html .= '<option value="'.$kpackaging.'" '.($kpackaging === pSQL(Tools::getValue('packaging_type_code')) ? 'selected="selected"' : '').'>'.$vpackaging.'</option>';
						$html .= '</select>
						</div>
						<label>'.$this->l('Packaging Size').' : </label>
							<div class="margin-form">
								<select name="packaging_size_code">
									<option value="0">'.$this->l('Select a packaging size ...').'</option>';
									foreach($this->_packagingSizeList as $kpackaging => $vpackaging)
										$html .= '<option value="'.$kpackaging.'" '.($kpackaging == pSQL(Tools::getValue('packaging_size_code')) ? 'selected="selected"' : '').'>'.$vpackaging.'</option>';
						$html .= '</select>
						</div>
						<label>'.$this->l('Machinable').' : </label>
							<div class="margin-form">
								<select name="machinable_code">
									<option value="0">'.$this->l('Select a machinable ...').'</option>';
									foreach($this->_machinableList as $kpackaging => $vpackaging)
										$html .= '<option value="'.$kpackaging.'" '.($kpackaging == pSQL(Tools::getValue('machinable_code')) ? 'selected="selected"' : '').'>'.$vpackaging.'</option>';
						$html .= '</select>
						</div>
						<label>'.$this->l('Additional charges').' : </label>
						<div class="margin-form"><input type="text" size="20" name="additional_charges" value="'.Tools::getValue('additional_charges').'" /></div><br />
						<label>'.$this->l('Delivery Service').' : </label>
							<div class="margin-form">';
								$rateServiceList = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'usps_rate_service_code`');
								foreach($rateServiceList as $rateService)
									$html .= '<input type="checkbox" name="service[]" value="'.$rateService['id_usps_rate_service_code'].'" '.(($this->_isPostCheck($rateService['id_usps_rate_service_code']) == 1) ? 'checked="checked"' : '').' /> '.$rateService['service'].' '.('COMMERCIAL' == substr($rateService['code'], -10) ? '['.$this->l('COMMERCIAL RATE').']' : '['.$this->l('REGULAR RATE').']').'<br />';
						$html .= '
						<p>' . $this->l('Choose the delivery service available to your customers.') . '</p>
						</div>
						<div class="margin-form"><input class="button" name="submitSave" type="submit"></div>
					</form>';
		}

		return $html;
	}

	private function _postValidationProduct()
	{
		// Check post values
		if (Tools::getValue('id_product') == NULL)
			$this->_postErrors[]  = $this->l('You have to select a product.');

		if (!$this->_postErrors)
		{
			$id_usps_rate_config = Db::getInstance()->getValue('SELECT `id_usps_rate_config` FROM `'._DB_PREFIX_.'usps_rate_config` WHERE `id_product` = '.(int)Tools::getValue('id_product'));

			// Check if a config does not exist in Add case
			if ($id_usps_rate_config > 0 && Tools::getValue('action') == 'add')
				$this->_postErrors[]  = $this->l('This product already has a specific USPS configuration.');

			// Check if a config exists and if the IDs config correspond in Upd case
			if (Tools::getValue('action') == 'edit' && (!isset($id_usps_rate_config) || $id_usps_rate_config != Tools::getValue('id_usps_rate_config')))
				$this->_postErrors[]  = $this->l('An error occurred, please try again.');

			// Check if a config exists in Delete case
			if (Tools::getValue('action') == 'delete' && !isset($id_usps_rate_config))
				$this->_postErrors[]  = $this->l('An error occurred, please try again.');
		}
	}

	private function _postProcessProduct()
	{
		// Init Var
		$date = date('Y-m-d H:i:s');
		$services = Tools::getValue('service');

		// Add Script
		if (Tools::getValue('action') == 'add')
		{
			$addTab = array(
				'id_product' => (int)(Tools::getValue('id_product')),
				'id_category' => 0,
				'id_currency' => (int)(Configuration::get('PS_CURRENCY_DEFAULT')),
				'packaging_type_code' => pSQL(Tools::getValue('packaging_type_code')),
				'packaging_size_code' => pSQL(Tools::getValue('packaging_size_code')),
				'machinable_code' => pSQL(Tools::getValue('machinable_code')),
				'additional_charges' => pSQL(Tools::getValue('additional_charges')),
				'date_add' => pSQL($date),
				'date_upd' => pSQL($date)
			);
			Db::getInstance()->autoExecute(_DB_PREFIX_.'usps_rate_config', $addTab, 'INSERT');
			$id_usps_rate_config = Db::getInstance()->Insert_ID();
			foreach ($services as $s)
			{
				$addTab = array('id_usps_rate_service_code' => pSQL($s), 'id_usps_rate_config' => (int)$id_usps_rate_config, 'date_add' => pSQL($date), 'date_upd' => pSQL($date));
				Db::getInstance()->autoExecute(_DB_PREFIX_.'usps_rate_config_service', $addTab, 'INSERT');
			}

			// Display Results
			if ($id_usps_rate_config > 0)
				$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
			else
				$this->_html .= $this->displayErrors($this->l('Settings failed'));
		}

		// Update Script
		if (Tools::getValue('action') == 'edit' && Tools::getValue('id_usps_rate_config'))
		{
			$updTab = array(
				'id_currency' => (int)(Configuration::get('PS_CURRENCY_DEFAULT')),
				'packaging_type_code' => pSQL(Tools::getValue('packaging_type_code')),
				'packaging_size_code' => pSQL(Tools::getValue('packaging_size_code')),
				'machinable_code' => pSQL(Tools::getValue('machinable_code')),
				'additional_charges' => pSQL(Tools::getValue('additional_charges')),
				'date_upd' => pSQL($date)
			);
			$result = Db::getInstance()->autoExecute(_DB_PREFIX_.'usps_rate_config', $updTab, 'UPDATE', '`id_usps_rate_config` = '.(int)Tools::getValue('id_usps_rate_config'));
			Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'usps_rate_config_service` WHERE `id_usps_rate_config` = '.(int)Tools::getValue('id_usps_rate_config'));
			foreach ($services as $s)
			{
				$addTab = array('id_usps_rate_service_code' => pSQL($s), 'id_usps_rate_config' => (int)Tools::getValue('id_usps_rate_config'), 'date_add' => pSQL($date), 'date_upd' => pSQL($date));
				Db::getInstance()->autoExecute(_DB_PREFIX_.'usps_rate_config_service', $addTab, 'INSERT');
			}

			// Display Results
			if ($result)
				$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
			else
				$this->_html .= $this->displayErrors($this->l('Settings failed'));
		}

		// Delete Script
		if (Tools::getValue('action') == 'delete' && Tools::getValue('id_usps_rate_config'))
		{
			$result1 = Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'usps_rate_config` WHERE `id_usps_rate_config` = '.(int)Tools::getValue('id_usps_rate_config'));
			$result2 = Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'usps_rate_config_service` WHERE `id_usps_rate_config` = '.(int)Tools::getValue('id_usps_rate_config'));

			// Display Results
			if ($result1)
				$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
			else
				$this->_html .= $this->displayErrors($this->l('Settings failed'));
		}
	}



	/*
	** Help Config Methods
	**
	*/

	private function _displayHelp()
	{
		return '<p><b>'.$this->l('Welcome to the PrestaShop USPS Module configurator.').'</b></p>
		<p>'.$this->l('This section will help you in understanding how to configure this module correctly.').'</p>
		<br />
		<p><b><u>1. '.$this->l('General Settings').'</u></b></p>
		<p>'.$this->l('See below for the description of each field :').'</p>
		<p><b>'.$this->l('Your USPS User ID').' :</b> '.$this->l('You must subscribe to the USPS website at this address').' <a href="http://www.usps.com/webtools/" target="_blank">http://www.usps.com/webtools/</a></p>
		<p><b>'.$this->l('Origination Zip Code').' :</b> '.$this->l('This field identifies the Zip Code of your package starting point.').'</p>
		<p><b>'.$this->l('Country').' :</b> '.$this->l('This field identifies the country of origin of your package.').'</p>
		<p><b>'.$this->l('Packaging Type').' :</b> '.$this->l('This field corresponds to the default packaging type (when there is no specific configuration for the product or the category product).').'</p>
		<p><b>'.$this->l('Delivery Service').' :</b> '.$this->l('These checkboxes correspond to the delivery services you want available (when there is no specific configuration for the product or the category product).').'</p>
		<br />
		<p><b><u>2. '.$this->l('Categories Settings').'</u></b></p>
		<p>'.$this->l('This section allows you to define a specific USPS configuration for each product category (such as Packaging Type and Additional charges).').'</p>
		<br />
		<p><b><u>3. '.$this->l('Products Settings').'</u></b></p>
		<p>'.$this->l('This section allows you to define a specific USPS configuration for each product (such as Packaging Type and Additional charges).').'</p>
		<br />
		';
	}

	public function hookupdateCarrier($params)
	{
		if ((int)($params['id_carrier']) != (int)($params['carrier']->id))
		{
			$serviceSelected = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'usps_rate_service_code` WHERE `id_carrier` = '.(int)$params['id_carrier']);
			$update = array('id_carrier' => (int)($params['carrier']->id), 'id_carrier_history' => pSQL($serviceSelected['id_carrier_history'].'|'.(int)($params['carrier']->id)));
			Db::getInstance()->autoExecute(_DB_PREFIX_.'usps_rate_service_code', $update, 'UPDATE', '`id_carrier` = '.(int)$params['id_carrier']);
		}
	}

	public function displayInfoByCart()
	{
	}



	/*
	** Front Methods
	**
	*/

	public function getCartCurrencyRate($id_currency_origin, $id_cart)
	{
		$conversionRate = 1;
		$cart = new Cart($id_cart);
		if ($cart->id_currency != $id_currency_origin)
		{
			$currencyOrigin = new Currency((int)$id_currency_origin);
			$conversionRate /= $currencyOrigin->conversion_rate;
			$currencySelect = new Currency((int)$cart->id_currency);
			$conversionRate *= $currencySelect->conversion_rate;
		}
		return $conversionRate;
	}

	public function getOrderShippingCostHash($wsParams)
	{
		$paramHash = '';
		$productHash = '';
		foreach ($wsParams['products'] as $product)
		{
			if (!empty($productHash))
				$productHash .= '|';
			$productHash .= $product['id_product'].':'.$product['id_product_attribute'].':'.$product['cart_quantity'];
		}
		foreach ($wsParams as $k => $v)
			if ($k != 'products')
				$paramHash .= '/'.$v;
		return md5($productHash.$paramHash.Configuration::get('USPS_CARRIER_CALCULATION_MODE'));
	}

	public function getOrderShippingCostCache($wsParams)
	{
		// Debug
		if ($this->debug == true)
			return false;

		// Get Cache
		$row = Db::getInstance()->getRow("
		SELECT * FROM `"._DB_PREFIX_."usps_cache`
		WHERE `id_cart` = ".(int)($wsParams['id_cart'])."
		AND `id_carrier` = ".(int)($this->id_carrier)."
		AND `hash` = '".pSQL($wsParams['hash'])."'");

		if ($row['id_currency'])
		{
			// Check Currency Rate And Calcul
			$conversionRate = $this->getCartCurrencyRate($row['id_currency'], $wsParams['id_cart']);
			$row['total_charges'] = $row['total_charges'] * $conversionRate;

			// Return Cache
			return $row;
		}

		return false;
	}

	public function saveOrderShippingCostCache($wsParams, $wscost)
	{
		$is_available = 1;
		if (!$wscost)
			$is_available = 0;
		$date = date('Y-m-d H:i:s');
		$cart = new Cart((int)$wsParams['id_cart']);
		$insert = array(
			'id_cart' => (int)($wsParams['id_cart']),
			'id_carrier' => (int)($this->id_carrier),
			'hash' => pSQL($wsParams['hash']),
			'id_currency' => (int)($cart->id_currency),
			'total_charges' => pSQL($wscost),
			'is_available' => (int)($is_available),
			'date_add' => pSQL($date),
			'date_upd' => pSQL($date)
		);
		Db::getInstance()->autoExecute(_DB_PREFIX_.'usps_cache', $insert, 'INSERT');
	}

	public function loadShippingCostConfig($product)
	{
		// Init var
		$config = array();

		// Check if there is a specific product configuration
		if ($product['id_product'] > 0)
		{
			$productConfiguration = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'usps_rate_config` WHERE `id_product` = '.(int)($product['id_product']));
			if ($productConfiguration['id_usps_rate_config'])
			{
				$servicesConfiguration = Db::getInstance()->executeS('
				SELECT urcs.*, ursc.`id_carrier`
				FROM `'._DB_PREFIX_.'usps_rate_config_service` urcs
				LEFT JOIN `'._DB_PREFIX_.'usps_rate_service_code` ursc ON (ursc.`id_usps_rate_service_code` = urcs.`id_usps_rate_service_code`)
				WHERE `id_usps_rate_config` = '.(int)($productConfiguration['id_usps_rate_config']));
				foreach ($servicesConfiguration as $service)
					$productConfiguration['services'][$service['id_usps_rate_service_code']] = $service;
				return $productConfiguration;
			}
		}

		// Check if there is a specific category configuration
		if ($product['id_category_default'] > 0)
		{
			$categoryConfiguration = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'usps_rate_config` WHERE `id_category` = '.(int)($product['id_category_default']));
			if ($categoryConfiguration['id_usps_rate_config'])
			{
				$servicesConfiguration = Db::getInstance()->executeS('
				SELECT urcs.*, ursc.`id_carrier`
				FROM `'._DB_PREFIX_.'usps_rate_config_service` urcs
				LEFT JOIN `'._DB_PREFIX_.'usps_rate_service_code` ursc ON (ursc.`id_usps_rate_service_code` = urcs.`id_usps_rate_service_code`)
				WHERE `id_usps_rate_config` = '.(int)($categoryConfiguration['id_usps_rate_config']));
				foreach ($servicesConfiguration as $service)
					$categoryConfiguration['services'][$service['id_usps_rate_service_code']] = $service;
				return $categoryConfiguration;
			}
		}

		// Return general config
		$config['packaging_type_code'] = Configuration::get('USPS_CARRIER_PACKAGING_TYPE');
		$servicesConfiguration = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'usps_rate_service_code` WHERE `active` = 1');
		foreach ($servicesConfiguration as $service)
			$config['services'][$service['id_usps_rate_service_code']] = $service;
		return $config;
	}

	public function getWebserviceShippingCost($wsParams)
	{
		// Init var
		$cost = 0;

		if (Configuration::get('USPS_CARRIER_CALCULATION_MODE') == 'onepackage')
		{
			$width = 0;
			$height = 0;
			$depth = 0;
			$weight = 0;

			foreach ($wsParams['products'] as $product)
			{
				if ($product['width'] && $product['width'] > $width) $width = $product['width'];
				if ($product['height'] && $product['height'] > $height) $height = $product['height'];
				if ($product['depth'] && $product['depth'] > $depth) $depth = $product['depth'];
				if ($product['weight'])
					$weight += ($product['weight'] * $product['quantity']);
			}
			$weight += Tools::getValue('usps_carrier_packaging_weight', Configuration::get('USPS_CARRIER_PACKAGING_WEIGHT'));

			// Get service in adequation with carrier and check if available
			$servicesConfiguration = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'usps_rate_service_code` WHERE `active` = 1');
			foreach ($servicesConfiguration as $service)
				$config['services'][$service['id_usps_rate_service_code']] = $service;
			$serviceSelected = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'usps_rate_service_code` WHERE `id_carrier` = '.(int)($this->id_carrier));
			if (!isset($config['services'][$serviceSelected['id_usps_rate_service_code']]))
				return false;

			$wsParams['service'] = $serviceSelected['code'];
			$wsParams['package_list'] = array();
			$wsParams['package_list'][] = array(
				'width' => ($width > 0 ? $width : 1),
				'height' => ($height > 0 ? $height : 1),
				'depth' => ($depth > 0 ? $depth : 1),
				'weight' => ($weight > 0 ? $weight : 0.5),
				'packaging_type' => Configuration::get('USPS_CARRIER_PACKAGING_TYPE'),
				'packaging_size' => Configuration::get('USPS_CARRIER_PACKAGING_SIZE'),
				'machinable' => Configuration::get('USPS_CARRIER_MACHINABLE'),
			);
		}
		else
		{
			// Getting shipping cost for each product
			foreach ($wsParams['products'] as $product)
			{
				// Load specific configuration
				$config = $this->loadShippingCostConfig($product);

				// Get service in adequation with carrier and check if available
				$serviceSelected = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'usps_rate_service_code` WHERE `id_carrier` = '.(int)($this->id_carrier));
				if (!isset($config['services'][$serviceSelected['id_usps_rate_service_code']]))
					return false;

				// Load param product
				$wsParams['service'] = $serviceSelected['code'];
				for ($qty = 0; $qty < $product['quantity']; $qty++)
					$wsParams['package_list'][] = array(
						'width' => ($product['width'] ? $product['width'] : 1),
						'height' => ($product['height'] ? $product['height'] : 1),
						'depth' => ($product['depth'] ? $product['depth'] : 1),
						'weight' => ($product['weight'] > 0 ? $product['weight'] : 0.5),
						'packaging_type' => (isset($config['packaging_type_code']) ? $config['packaging_type_code'] : Configuration::get('USPS_CARRIER_PACKAGING_TYPE')),
						'packaging_size' => (isset($config['packaging_size_code']) ? $config['packaging_size_code'] : Configuration::get('USPS_CARRIER_PACKAGING_SIZE')),
						'machinable' => (isset($config['machinable_code']) ? $config['machinable_code'] : Configuration::get('USPS_CARRIER_MACHINABLE')),
					);

				// If Additional charges
				if (isset($config['id_currency']) && isset($config['additional_charges']))
				{
					$conversionRate = 1;
					$conversionRate = $this->getCartCurrencyRate((int)($config['id_currency']), (int)$wsParams['id_cart']);
					$cost += ($config['additional_charges'] * $product['quantity']  * $conversionRate);
				}
			}
		}


		// If webservice return a cost, we add it, else, we return the original shipping cost
		$result = $this->getUspsShippingCost($wsParams);
		if ($result['connect'] && $result['cost'] > 0)
			return ($cost + $result['cost'] + Tools::getValue('usps_carrier_handling_fee', Configuration::get('USPS_CARRIER_HANDLING_FEE')));
		return false;
	}

	public function getOrderShippingCost($params, $shipping_cost)
	{
		// Init var
		$address = new Address($params->id_address_delivery);
		if (!Validate::isLoadedObject($address))
		{
			// If address is not loaded, we take data from shipping estimator module (if installed)
			$address->id_country = $this->context->cookie->id_country;
			$address->id_state = $this->context->cookie->id_state;
			$address->postcode = $this->context->cookie->postcode;
		}
		$recipient_country = Db::getInstance()->getRow('SELECT `iso_code` FROM `'._DB_PREFIX_.'country` WHERE `id_country` = '.(int)($address->id_country));
		$recipient_state = Db::getInstance()->getRow('SELECT `iso_code` FROM `'._DB_PREFIX_.'state` WHERE `id_state` = '.(int)($address->id_state));
		$shipper_country = Db::getInstance()->getRow('SELECT `iso_code` FROM `'._DB_PREFIX_.'country` WHERE `id_country` = '.(int)(Configuration::get('USPS_CARRIER_COUNTRY')));
		$shipper_state = Db::getInstance()->getRow('SELECT `iso_code` FROM `'._DB_PREFIX_.'state` WHERE `id_state` = '.(int)(Configuration::get('USPS_CARRIER_STATE')));
		$products = $params->getProducts();

		// Webservices Params
		$wsParams = array(
			'id_cart' => $params->id,
			'id_address_delivery' => $params->id_address_delivery,
			'recipient_address1' => $address->address1,
			'recipient_address2' => $address->address2,
			'recipient_postalcode' => $address->postcode,
			'recipient_city' => $address->city,
			'recipient_country_iso' => $recipient_country['iso_code'],
			'recipient_state_iso' => $recipient_state['iso_code'],
			'shipper_postalcode' => Configuration::get('USPS_CARRIER_POSTAL_CODE'),
			'products' => $params->getProducts()
		);
		$wsParams['hash'] = $this->getOrderShippingCostHash($wsParams);

		// Check cache
		$cache = $this->getOrderShippingCostCache($wsParams);
		if ($cache['id_usps_cache'] > 0)
		{
			if ($cache['is_available'] == 0)
				return false;
			if ($cache['total_charges'])
				return $cache['total_charges'];
		}

		// Get Webservices Cost and Cache it
		$wscost = $this->getWebserviceShippingCost($wsParams);
		$this->saveOrderShippingCostCache($wsParams, $wscost);

		if ($wscost > 0)
			return $wscost + $shipping_cost;
		return false;
	}

	public function getOrderShippingCostExternal($params)
	{
		return $this->getOrderShippingCost($params, 23);
	}



	/*
	** Webservices Methods
	**
	*/

	public function webserviceTest($service = '')
	{
		// Check API Key
		if (!Configuration::get('USPS_CARRIER_USER_ID'))
			return false;

		// Example Params for testing
		$shipper_country = Db::getInstance()->getRow('SELECT `iso_code` FROM `'._DB_PREFIX_.'country` WHERE `id_country` = '.(int)(Configuration::get('USPS_CARRIER_COUNTRY')));
		$shipper_state = Db::getInstance()->getRow('SELECT `iso_code` FROM `'._DB_PREFIX_.'state` WHERE `id_state` = '.(int)(Configuration::get('USPS_CARRIER_STATE')));
		$wsParams = array(
			'recipient_address1' => Configuration::get('USPS_CARRIER_ADDRESS1'),
			'recipient_address2' => Configuration::get('USPS_CARRIER_ADDRESS2'),
			'recipient_postalcode' => Configuration::get('USPS_CARRIER_POSTAL_CODE'),
			'recipient_city' => Configuration::get('USPS_CARRIER_CITY'),
			'recipient_country_iso' => $shipper_country['iso_code'],
			'recipient_state_iso' => $shipper_state['iso_code'],
			'shipper_address1' => Configuration::get('USPS_CARRIER_ADDRESS1'),
			'shipper_address2' => Configuration::get('USPS_CARRIER_ADDRESS2'),
			'shipper_postalcode' => Configuration::get('USPS_CARRIER_POSTAL_CODE'),
			'shipper_city' => Configuration::get('USPS_CARRIER_CITY'),
			'shipper_country_iso' => $shipper_country['iso_code'],
			'shipper_state_iso' => $shipper_state['iso_code'],
			'package_list' => array(
				array('width' => 10, 'height' => 3, 'depth' => 10, 'weight' => 0.75, 'packaging_size' => Configuration::get('USPS_CARRIER_PACKAGING_SIZE'), 'packaging_type' => Configuration::get('USPS_CARRIER_PACKAGING_TYPE'), 'machinable' => Configuration::get('USPS_CARRIER_MACHINABLE')),
				array('width' => 3, 'height' => 3, 'depth' => 3, 'weight' => 0.75, 'packaging_size' => Configuration::get('USPS_CARRIER_PACKAGING_SIZE'), 'packaging_type' => Configuration::get('USPS_CARRIER_PACKAGING_TYPE'), 'machinable' => Configuration::get('USPS_CARRIER_MACHINABLE')),
			),
		);

		// Unit or Large Test
		if (!empty($service))
			$servicesList = array(array('code' => $service));
		else
			$servicesList = Db::getInstance()->executeS('SELECT `code` FROM `'._DB_PREFIX_.'usps_rate_service_code`');

		// Testing Service
		foreach ($servicesList as $service)
		{
			// Sending Request
			$wsParams['service'] = $service['code'];
			$resultTab = Db::getInstance()->getValue('SELECT `result` FROM `'._DB_PREFIX_.'usps_cache_test` WHERE `hash` = \''.pSQL(md5(var_export($this->getXml($wsParams), true))).'\'');

			if ($resultTab && $this->debug != true)
				$resultTab = unserialize($resultTab);
			else
				$resultTab = $this->sendRequest($wsParams);

			// Return results
			if (isset($resultTab['Rate']))
			{
				Db::getInstance()->autoExecute(_DB_PREFIX_.'usps_cache_test', array('hash' => pSQL(md5(var_export($this->getXml($wsParams), true))), 'result' => pSQL(serialize($resultTab)), 'date_add' => pSQL(date('Y-m-d H:i:s')), 'date_upd' => pSQL(date('Y-m-d H:i:s'))), 'INSERT');
				return true;
			}

			if (isset($resultTab['Error']))
				$this->_webserviceError = $this->l('Error').' '.$resultTab['Error'];
			else
			{
				$this->_webserviceError = $this->l('USPS Webservice seems to be down, please wait a few minutes and try again.');
				return false;
			}
		}

		return false;
	}

	public function getUspsShippingCost($wsParams)
	{
		// Check Arguments
		if (!$wsParams)
			return array('connect' => false, 'cost' => 0);

		// Sending Request
		$resultTab = $this->sendRequest($wsParams);

		// Check currency
		$conversionRate = 1;
		if (isset($resultTab['Rate']))
			$conversionRate = $this->getCartCurrencyRate(Currency::getIdByIsoCode('USD'), $wsParams['id_cart']);

		// Return results
		if (isset($resultTab['Rate']))
			return array('connect' => true, 'cost' => $resultTab['Rate'] * $conversionRate);

		if (isset($resultTab['Error']))
			$this->_webserviceError = $this->l('Error').' '.$resultTab['Error'];
		else
			$this->_webserviceError = $this->l('USPS Webservice seems to be down, please wait a few minutes and try again.');

		return array('connect' => false, 'cost' => 0);
	}

	public function sendRequest($wsParams)
	{
		// POST Request
		$errno = $errstr = $result = '';
		$xmlTab = $this->getXml($wsParams);
		$resultTab = array();

		// Debug Xml
		if ($this->debug == true)
		{
			$fh_xml_date = date('Y-m-d H-i-s');
			$fh_xml = fopen(dirname(__FILE__).'/log/log_xml_'.$fh_xml_date.'.txt', 'a');
			fwrite($fh_xml, "\n========================================\nREQUEST: ".$fh_xml_date."\n----------------------------------------\n");
			fwrite($fh_xml, print_r($xmlTab, true));
			fwrite($fh_xml, "========================================\nRESPONSE: ".$fh_xml_date."  NOTE: If response is empty then an error occured\n----------------------------------------\n");
			fclose($fh_xml);
		}

		// Loop on Xml
		foreach ($xmlTab as $xml)
		{
			// Make the request
			$opts = array(
  				'http'=>array(
					'method'=> 'POST',
					'content' => 'API=RateV4&XML='.$xml,
					'header'  => 'Content-type: application/x-www-form-urlencoded',
					'timeout' => 5,
 				)
			);
			$context = stream_context_create($opts);
			$result = @file_get_contents('http://production.shippingapis.com/ShippingAPI.dll', false, $context);
			if (!$result)
				$this->_webserviceError = $this->l('Could not connect to USPS.com');


			// Get xml from HTTP Result
			$data = trim($result);
			if (strpos($result, '<?'))
				$data = strstr($result, '<?');

			// Parsing XML
			$resultTabTmp = simplexml_load_string($data);
			$resultTabTmpDebug[] = $resultTabTmp;

			if (!isset($resultTabTmp->Package) && isset($resultTabTmp->Description) && isset($resultTabTmp->Number))
			{
				if (!isset($resultTab['Error']))
					$resultTab['Error'] = '';
				$resultTab['Error'] .= '<b>'.(string)$resultTabTmp->Number.'</b> : '.(string)$resultTabTmp->Description."\n";
			}

			if (isset($resultTabTmp->Package))
				foreach ($resultTabTmp->Package as $package)
				{
					if (isset($package->Error))
					{
						if (!isset($resultTab['Error']))
							$resultTab['Error'] = '';
						$tmp = (array)$package;
						$resultTab['Error'] .= (isset($package->Error->Description) ? 'Error <b>'.(string)$package->Error->HelpContext.'</b> on package <b>'.(string)$tmp['@attributes']['ID'].'</b> : '.(string)$package->Error->Description : 'Error')."\n";
					}
					if (isset($package->Postage->Rate))
					{
						if (!isset($resultTab['Rate']))
							$resultTab['Rate'] = 0;
						$resultTab['Rate'] += (string)$package->Postage->Rate;
					}
				}
		}

		// Log
		if ($this->debug == true)
		{
			$fh_xml_data = str_replace("><", ">\n<", $data);
			$fh_xml = fopen(dirname(__FILE__).'/log/log_xml_'.$fh_xml_date.'.txt', 'a');
			fwrite($fh_xml, $fh_xml_data);
			fwrite($fh_xml, "resultTab ".print_r($resultTab, true));
			//fwrite($fh_xml, "resultTabTmpDebug ".print_r($resultTabTmpDebug, true));
			fclose($fh_xml);
		}

		return $resultTab;
	}


	public function getXml($wsParams = array())
	{
		// Template Xml Package List
		$count = 0;
		$xmlTab = array();
		$xmlPackageList = '';
		$xmlPackageTemplate = @file_get_contents(dirname(__FILE__).'/xml-package.tpl');

		foreach ($wsParams['package_list'] as $k => $p)
		{
			// KG, LB, OU conversions
			$p['weight_pounds'] = $p['weight'];
			if ($this->_weightUnit == 'KG' || $this->_weightUnit == 'KGS')
				$p['weight_pounds'] = round($p['weight'] * 2.20462262);
			$p['weight_ounces'] = $p['weight_pounds'] * 16;
			$p['weight_pounds'] = 0;

			// First class management
			if (substr($wsParams['service'], 0, 11) == 'FIRST CLASS')
				$wsParams['firstclassmailtype'] = '<FirstClassMailType>PARCEL</FirstClassMailType>';
			else
				$wsParams['firstclassmailtype'] = '';

			// Replace in template
			$search = array(
				'[[ID]]',
				'[[Service]]',
				'[[FirstClassMailType]]',
				'[[ZipOrigination]]',
				'[[ZipDestination]]',
				'[[Pounds]]',
				'[[Ounces]]',
				'[[Container]]',
				'[[Size]]',
				'[[Width]]',
				'[[Height]]',
				'[[Length]]',
				'[[Machinable]]'
			);
			$replace = array(
				$k + 1,
				$wsParams['service'],
				$wsParams['firstclassmailtype'],
				$wsParams['shipper_postalcode'],
				$wsParams['recipient_postalcode'],
				$p['weight_pounds'],
				$p['weight_ounces'],
 				$p['packaging_type'],
 				$p['packaging_size'],
				$p['width'],
				$p['height'],
				$p['depth'],
				$p['machinable']
			);
			$xmlPackageList .= str_replace($search, $replace, $xmlPackageTemplate);

			$count++;
			if ($count == 25)
			{
				// Template Xml
				$search = array('[[USERID]]', '[[PackageList]]');
				$replace = array(Configuration::get('USPS_CARRIER_USER_ID'), $xmlPackageList);
				$xmlTemplate = @file_get_contents(dirname(__FILE__).'/xml.tpl');
				$xmlTab[] = str_replace($search, $replace, $xmlTemplate);
				$xmlPackageList = '';
				$count = 0;
		}
		}


		// Template Xml
		if ($count > 0)
		{
		$search = array('[[USERID]]', '[[PackageList]]');
		$replace = array(Configuration::get('USPS_CARRIER_USER_ID'), $xmlPackageList);
		$xmlTemplate = @file_get_contents(dirname(__FILE__).'/xml.tpl');
			$xmlTab[] = str_replace($search, $replace, $xmlTemplate);
		}


		// Return
		return $xmlTab;
	}

}

