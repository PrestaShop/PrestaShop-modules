<?php
	/**
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
	 * @author    boxdrop Group AG
	 * @copyright boxdrop Group AG
	 * @license   http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
	 * International Registered Trademark & Property of boxdrop Group AG
	 */

	if (!defined('_PS_VERSION_'))
		exit;

	require_once dirname(__FILE__).'/lib/BoxdropAjaxRequest.class.php';
	require_once dirname(__FILE__).'/lib/BoxdropCarrier.class.php';
	require_once dirname(__FILE__).'/lib/BoxdropHelper.class.php';
	require_once dirname(__FILE__).'/lib/BoxdropOrder.class.php';
	require_once dirname(__FILE__).'/lib/BoxdropOrderShipment.class.php';
	require_once dirname(__FILE__).'/lib/BoxdropOrderShipmentParcel.class.php';
	require_once dirname(__FILE__).'/lib/BoxdropOrderShipmentParcelHasOrderDetail.class.php';
	require_once dirname(__FILE__).'/sdk/boxdropPHPSDK/BoxdropSDK.class.php';
	/**
	 * BoxdropShipment
	 * Prestashop module offering DHL logistics in the selling progress
	 *
	 * @author  sweber <sw@boxdrop.com>
	 * @package BoxdropShipment
	 * @version 1.0.3
	 */
	class BoxdropShipment extends CarrierModule
	{
		const CONF_API_COUNTRY = 'BDSHIP_API_COUNTRY';
		const CONF_API_HMAC_KEY = 'BDSHIP_API_HMAC';
		const CONF_API_PASS = 'BDSHIP_API_PASS';
		const CONF_API_TEST_MODE = 'BDSHIP_API_TEST_MODE';
		const CONF_API_USER_ID = 'BDSHIP_API_UID';
		const CONF_API_VERSION = 'BDSHIP_API_V';
		const CONF_AUTO_DOWNLOAD = 'BDSHIP_AUTO_DOWNLOAD_LETTER';
		const CONF_SHIPPING_STATUS = 'BDSHIP_ORDER_SHP_STATE';
		const CONF_MODE_DIRECT_ECONOMY = 'BDSHIP_CARRIER_MD_DIRCT_ECO';
		const CONF_MODE_DIRECT_EXPRESS = 'BDSHIP_CARRIER_MD_DIRCT_EXP';
		const CONF_MODE_DROPOFF_ECONOMY = 'BDSHIP_CARRIER_MD_DROFF_ECO';
		const CONF_MODE_DROPOFF_EXPRESS = 'BDSHIP_CARRIER_MD_DROFF_EXP';
		const OLD_CARRIER_IDS_DIRECT_ECONOMY = 'BDSHIP_OCIDS_DIRCT_ECO';
		const OLD_CARRIER_IDS_DIRECT_EXPRESS = 'BDSHIP_OCIDS_DIRCT_EXPRESS';
		const OLD_CARRIER_IDS_DROPOFF_ECONOMY = 'BDSHIP_OCIDS_DROFF_ECO';
		const OLD_CARRIER_IDS_DROPOFF_EXPRESS = 'BDSHIP_OCIDS_DROFF_EXPRESS';
		const SHIP_MODE_ECONOMY = 'economy';
		const SHIP_MODE_EXPRESS = 'express';
		const SHIP_MODE_EXPRESS12 = 'express12';
		const SHIP_MODE_EXPRESS10 = 'express10';
		const SHIP_MODE_EXPRESS9 = 'express9';

		/**
		 * @var array $allowed_country_codes list of supported country codes to be installed in
		 */
		private $allowed_country_codes = array('IT');

		/**
		 * @var string $config_contents buffer for displaying the configuration page
		 */
		private $config_contents = '';

		/**
		 * @var boolean $old_mode true when version < 1.6.
		 */
		private $old_mode = false;

		/**
		 * Constructor.
		 *
		 * @author sweber <sw@boxdrop.com>
		 * @return boolean
		 */
		public function __construct()
		{
			$this->name = 'boxdropshipment';
			$this->tab = 'shipping_logistics';
			$this->version = '1.0.3';
			$this->author = 'boxdrop Group AG';
			$this->need_instance = 0;
			$this->dependencies = array('blockcart');
			$this->old_mode = version_compare(_PS_VERSION_, '1.6.0.0', '<');
			$this->bootstrap = true;
			parent::__construct();
			$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
			$this->description =
			$this->l('Offers an easy 360° shipment creation and handling integration into PrestaShop using DHL Express and Economy services.');
			$this->displayName = $this->l('boxdrop® eLogistics');
			$this->warning = '';
			if (!Configuration::get(self::CONF_API_PASS) || !Configuration::get(self::CONF_API_VERSION) || !Configuration::get(self::CONF_API_HMAC_KEY) ||
			!Configuration::get(self::CONF_API_USER_ID))
				$this->warning .= $this->l('Please complete the configuration of your "boxdropshipment" module');

			if (!extension_loaded('curl'))
				$this->warning .= $this->l('Could not find the cURL extension. Please install / activate cURL for PHP in order to use this module.');

			if (!extension_loaded('mcrypt'))
				$this->warning .= $this->l('Could not find the mcrypt extension. Please install / activate mcrypt for PHP in order to use this module.');

			if (!Configuration::get('PS_SHOP_NAME') || !Configuration::get('PS_SHOP_ADDR1') || !Configuration::get('PS_SHOP_CODE') ||
			!Configuration::get('PS_SHOP_CITY') || !Configuration::get('PS_SHOP_PHONE') || !Configuration::get('PS_SHOP_EMAIL'))
				$this->warning .=
				'<br />'.$this->l('Please complete your shop address data in the "boxdropshipment" module or the general store address configuration');
		}

		/**
		 * install handler
		 *
		 * @author sweber <sw@boxdrop.com>
		 * @return boolean
		 */
		public function install()
		{
			$install_sql = realpath(dirname(__FILE__).'/sql/').'/create.php';
			if (Shop::isFeatureActive())
				Shop::setContext(Shop::CONTEXT_ALL);

			if (!$this->checkShopIsSupported())
			{
				$this->_errors[] = $this->l('Your shop is not supported by this module. The shops country must be Italian.');
				return false;
			}

			if (!parent::install())
			{
				$this->_errors[] = $this->l('Something bad happened in the main installer');
				return false;
			}

			if (!BoxdropCarrier::setupCourier(self::CONF_MODE_DIRECT_ECONOMY, 'DHL Economy by boxdrop®') ||
			!BoxdropCarrier::setupCourier(self::CONF_MODE_DIRECT_EXPRESS, 'DHL Express by boxdrop®'))
			{
				$this->_errors[] = $this->l('Could not install carriers');
				return false;
			}

			if (!$this->registerHook('actionValidateOrder') || !$this->registerHook('displayCarrierList') || !$this->registerHook('updateCarrier'))
			{
				$this->_errors[] = $this->l('Could not register hooks');
				return false;
			}

			if (Configuration::updateValue(self::CONF_API_VERSION, 'v1_0') === false)
			{
				$this->_errors[] = $this->l('Could not setup configuration variables');
				return false;
			}

			if (!$this->loadAndExecuteDatabaseFile($install_sql))
			{
				$this->_errors[] = $this->l('Could not create database tables');
				return false;
			}

			return true;
		}

		/**
		 * Uninstall handler - cleans all our inserted stuff
		 *
		 * @author sweber <sw@boxdrop.com>
		 */
		public function uninstall()
		{
			$uninstall_sql = realpath(dirname(__FILE__).'/sql/').'/drop.php';
			if (!BoxdropCarrier::deleteCarrier(self::CONF_MODE_DIRECT_ECONOMY) || !BoxdropCarrier::deleteCarrier(self::CONF_MODE_DIRECT_EXPRESS))
			{
				$this->_errors[] = $this->l('Could not delete carriers');
				return false;
			}

			if (!Configuration::deleteByName(self::CONF_API_COUNTRY) || !Configuration::deleteByName(self::CONF_API_HMAC_KEY) ||
			!Configuration::deleteByName(self::CONF_API_PASS) || !Configuration::deleteByName(self::CONF_API_TEST_MODE) ||
			!Configuration::deleteByName(self::CONF_API_USER_ID) || !Configuration::deleteByName(self::CONF_API_VERSION) ||
			!Configuration::deleteByName(self::OLD_CARRIER_IDS_DIRECT_ECONOMY) || !Configuration::deleteByName(self::OLD_CARRIER_IDS_DIRECT_EXPRESS) ||
			!Configuration::deleteByName(self::OLD_CARRIER_IDS_DROPOFF_ECONOMY) || !Configuration::deleteByName(self::OLD_CARRIER_IDS_DROPOFF_EXPRESS))
			{
				$this->_errors[] = $this->l('Could not delete configuration variables');
				return false;
			}

			if (!$this->unregisterHook('actionValidateOrder') || !$this->unregisterHook('displayCarrierList') || !$this->unregisterHook('updateCarrier'))
			{
				$this->_errors[] = $this->l('Could not delete hooks');
				return false;
			}

			if (!$this->loadAndExecuteDatabaseFile($uninstall_sql))
			{
				$this->_errors[] = $this->l('Could not delete modules database tables');
				return false;
			}

			if (!parent::uninstall())
			{
				$this->_errors[] = $this->l('Main uninstaller has reported an error');
				return false;
			}

			return true;
		}

		/**
		 * Configuration page handler
		 * As there is no FormHelper in 1.4.x, we'll just use a regular template to keep things separated.
		 *
		 * @author sweber <sw@boxdrop.com>
		 * @return string
		 */
		public function getContent()
		{
			$smarty_data = array(
				'documentation_link' => 'data/doc-it.pdf',
				'form_url' => htmlentities($_SERVER['REQUEST_URI']),
				'icon' => $this->_path.'/img/logo-fieldset.png',
				'module_name' => $this->displayName,
				'show_api_warn' => !$this->hasAPICredentials(),
				'show_curl_warn' => !extension_loaded('curl'),
				'show_mcrypt_warn' => !extension_loaded('mcrypt'),
				'order_states' => OrderState::getOrderStates(Configuration::get('PS_LANG_DEFAULT')),
				'PS_SHOP_NAME' => Tools::getValue('PS_SHOP_NAME', Configuration::get('PS_SHOP_NAME')),
				'PS_SHOP_ADDR1' => Tools::getValue('PS_SHOP_ADDR1', Configuration::get('PS_SHOP_ADDR1')),
				'PS_SHOP_ADDR2' => Tools::getValue('PS_SHOP_ADDR2', Configuration::get('PS_SHOP_ADDR2')),
				'PS_SHOP_CODE' => Tools::getValue('PS_SHOP_CODE', Configuration::get('PS_SHOP_CODE')),
				'PS_SHOP_CITY' => Tools::getValue('PS_SHOP_CITY', Configuration::get('PS_SHOP_CITY')),
				'PS_SHOP_PHONE' => Tools::getValue('PS_SHOP_PHONE', Configuration::get('PS_SHOP_PHONE')),
				'PS_SHOP_EMAIL' => Tools::getValue('PS_SHOP_EMAIL', Configuration::get('PS_SHOP_EMAIL')),
				'API_USER_ID' => Tools::getValue('API_USER_ID', Configuration::get(self::CONF_API_USER_ID)),
				'API_PASS' => Tools::getValue('API_PASS', Configuration::get(self::CONF_API_PASS)),
				'API_HMAC_KEY' => Tools::getValue('API_HMAC_KEY', Configuration::get(self::CONF_API_HMAC_KEY)),
				'API_COUNTRY' => Tools::getValue('API_COUNTRY', Configuration::get(self::CONF_API_COUNTRY)),
				'API_TEST_MODE' => Tools::getValue('API_TEST_MODE', Configuration::get(self::CONF_API_TEST_MODE)),
				'AUTO_DOWNLOAD' => Tools::getValue('AUTO_DOWNLOAD', Configuration::get(self::CONF_AUTO_DOWNLOAD)),
				'SHIPPING_STATUS' => Tools::getValue('SHIPPING_STATUS', Configuration::get(self::CONF_SHIPPING_STATUS))
			);

			if (Tools::isSubmit('submitboxdropshipment'))
			{
				if ($this->configurationPageIsValid())
				{
					Configuration::updateValue('PS_SHOP_NAME', Tools::getValue('PS_SHOP_NAME'));
					Configuration::updateValue('PS_SHOP_ADDR1', Tools::getValue('PS_SHOP_ADDR1'));
					Configuration::updateValue('PS_SHOP_ADDR2', Tools::getValue('PS_SHOP_ADDR2'));
					Configuration::updateValue('PS_SHOP_CODE', Tools::getValue('PS_SHOP_CODE'));
					Configuration::updateValue('PS_SHOP_CITY', Tools::getValue('PS_SHOP_CITY'));
					Configuration::updateValue('PS_SHOP_PHONE', Tools::getValue('PS_SHOP_PHONE'));
					Configuration::updateValue('PS_SHOP_EMAIL', Tools::getValue('PS_SHOP_EMAIL'));
					Configuration::updateValue(self::CONF_API_USER_ID, Tools::getValue('API_USER_ID'));
					Configuration::updateValue(self::CONF_API_PASS, Tools::getValue('API_PASS'));
					Configuration::updateValue(self::CONF_API_HMAC_KEY, Tools::getValue('API_HMAC_KEY'));
					Configuration::updateValue(self::CONF_API_COUNTRY, Tools::getValue('API_COUNTRY'));
					Configuration::updateValue(self::CONF_API_TEST_MODE, Tools::getValue('API_TEST_MODE'));
					Configuration::updateValue(self::CONF_AUTO_DOWNLOAD, Tools::getValue('AUTO_DOWNLOAD'));
					Configuration::updateValue(self::CONF_SHIPPING_STATUS, Tools::getValue('SHIPPING_STATUS'));
					$this->config_contents .= $this->displayConfirmation($this->l('Settings updated!'));
				}

				$smarty_data['show_api_warn'] = !$this->hasAPICredentials();
			}

			$this->smarty->assign($smarty_data);
			if ($this->old_mode)
				$this->config_contents .= $this->display(__FILE__, 'configuration_old_be.tpl');
			else
				$this->config_contents .= $this->display(__FILE__, 'configuration.tpl');

			return $this->config_contents;
		}

		/**
		 * Validates the values given in the configuration page.
		 * A bit lengthy, but as there is no form handling and validation class (especially as for 1.4.x),
		 * thats more or less the only way to go. One could create an array and stuff. But that seems overloaded at this point.
		 *
		 * Added own "-" chars in the li-tags, as the defaults are being hidden by BackOffice CSS.
		 *
		 * @author sweber <sw@boxdrop.com>
		 * @return boolean
		 */
		private function configurationPageIsValid()
		{
			$errors = array();

			if (trim(Tools::getValue('PS_SHOP_NAME')) == '' || !Validate::isString(Tools::getValue('PS_SHOP_NAME')))
				array_push($errors, '<li>'.$this->l('Please check yout shop name.').'</li>');

			if (trim(Tools::getValue('PS_SHOP_ADDR1')) == '' || !Validate::isString(Tools::getValue('PS_SHOP_ADDR1')))
				array_push($errors, '<li>'.$this->l('Please check your shops address').'</li>');

			if (trim(Tools::getValue('PS_SHOP_CODE')) == '' || !Validate::isString(Tools::getValue('PS_SHOP_CODE')))
				array_push($errors, '<li>'.$this->l('Please check your shops zipcode').'</li>');

			if (trim(Tools::getValue('PS_SHOP_CITY')) == '' || !Validate::isString(Tools::getValue('PS_SHOP_CITY')))
				array_push($errors, '<li>'.$this->l('Please check your shops city').'</li>');

			if (trim(Tools::getValue('PS_SHOP_PHONE')) == '' || !Validate::isPhoneNumber(Tools::getValue('PS_SHOP_PHONE')))
				array_push($errors, '<li>'.$this->l('Please check your shops phone number').'</li>');

			if (trim(Tools::getValue('PS_SHOP_EMAIL')) == '' || !Validate::isEmail(Tools::getValue('PS_SHOP_EMAIL')))
				array_push($errors, '<li>'.$this->l('Please check your shops email address').'</li>');

			if (trim(Tools::getValue('API_USER_ID')) == '' || !Validate::isInt(Tools::getValue('API_USER_ID')))
				array_push($errors, '<li>'.$this->l('Please check your API User ID').'</li>');

			if (trim(Tools::getValue('API_PASS')) == '' || !Validate::isString(Tools::getValue('API_PASS')))
				array_push($errors, '<li>'.$this->l('Please check your API password').'</li>');

			if (trim(Tools::getValue('API_HMAC_KEY')) == '' || !Validate::isString(Tools::getValue('API_HMAC_KEY')))
				array_push($errors, '<li>'.$this->l('Please check your API HMAC key').'</li>');

			if (trim(Tools::getValue('API_COUNTRY')) == '' || !Validate::isString(Tools::getValue('API_COUNTRY')))
				array_push($errors, '<li>'.$this->l('Please check your API country').'</li>');

			if (trim(Tools::getValue('SHIPPING_STATUS')) == '' || !Validate::isString(Tools::getValue('SHIPPING_STATUS')))
				array_push($errors, '<li>'.$this->l('Please check the shipping status').'</li>');

			if (count($errors))
			{
				$this->config_contents .= $this->displayError($this->l('The following errors occured: ').'<ul>'.implode(' ', $errors).'</ul>');
				return false;
			}

			return true;
		}

		/**
		 * Checks if all API crentials are supplied & saved
		 *
		 * @author sweber <sw@boxdrop.com>
		 * @return boolean
		 */
		private function hasAPICredentials()
		{
			return (Configuration::get(self::CONF_API_USER_ID) != '' && Configuration::get(self::CONF_API_PASS) != '' &&
			Configuration::get(self::CONF_API_HMAC_KEY) != '' && Configuration::get(self::CONF_API_COUNTRY) != '');
		}

		/**
		 * Used to determine shipping costs if "needs_range" === true.
		 * Nothing to do here - suggestion is to return $shipping_cost parameter (see
		 * http://www.prestashop.com/forums/topic/78410-how-to-use-the-new-class-carriermodule-of-v14/?p=444480)
		 *
		 * @param  array $params
		 * @param  float $shipping_cost
		 * @return boolean
		 */
		public function getOrderShippingCost($params, $shipping_cost)
		{
			return $shipping_cost;
		}

		/**
		 * Used to determine shipping costs if "needs_range" === false.
		 * We are not using this, but required to implement.
		 *
		 * @param  array $params
		 * @return boolean
		 */
		public function getOrderShippingCostExternal($params)
		{
			return false;
		}

		/**
		 * Returns true if the current shop setup is supported by our module.
		 * For now, we are only checking if the shops country is italy
		 *
		 * @author sweber <sw@boxdrop.com>
		 * @return boolean
		 */
		private function checkShopIsSupported()
		{
			$lang_id = Configuration::get('PS_LANG_DEFAULT');
			$shop_country_id = Configuration::get('PS_COUNTRY_DEFAULT');
			$shop_country = new Country($shop_country_id, $lang_id);
			return in_array($shop_country->iso_code, $this->allowed_country_codes);
		}

		/**
		 * Hooking into the carrier list in the order process
		 *
		 * @author sweber <sw@boxdrop.com>
		 * @param  array  $params
		 * @return string
		 */
		public function hookDisplayCarrierList($params)
		{
			$carriers = array(
				'dropoff' => array(),
				'regular' => array(
					Configuration::get(self::CONF_MODE_DIRECT_ECONOMY),
					Configuration::get(self::CONF_MODE_DIRECT_EXPRESS)
				)
			);

			$address = $params['address'];
			$address_str = base64_encode($address->address1.' '.$address->address2.' '.$address->postcode.' '.$address->city);
			if ((boolean)Configuration::get(BoxdropShipment::CONF_API_TEST_MODE))
				$map_url = 'http://alphatest.boxdrop.com/mapApi.php/?address='.$address_str;
			else
				$map_url = 'https://api.boxdrop.com/mapApi.php/?address='.$address_str;

			$this->smarty->assign(array(
				'carriers' => Tools::jsonEncode($carriers),
				'display_mode' => 'overlay',
				'map_url' => $map_url
			));

			return $this->display(__FILE__, 'carrierList.tpl');
		}

		/**
		 * hooking into order creation where we are selected as the carrier
		 *
		 * @author sweber <sw@boxdrop.com>
		 * @param  array  $parameter
		 */
		public function hookActionValidateOrder($parameter)
		{
			$order = $parameter['order'];
			$order_address = new Address($order->id_address_delivery);
			$boxdrop_order = new BoxdropOrder($order->id_cart);
			$boxdrop_order->id_cart = $order->id_cart;
			$boxdrop_order->id_customer = $order->id_customer;
			$boxdrop_order->id_order = $order->id;
			$boxdrop_order->save();

			/*
			 * update orders delivery address, if in dropoff mode
			 */
			if ($boxdrop_order->boxdrop_shop_id != 0)
			{
				$sdk = BoxdropHelper::getBoxdropSDK();
				$response = $sdk->request('shops', 'show', array('shop_id' => $boxdrop_order->boxdrop_shop_id));
				$boxdrop_shop = $response->shop;
				$boxdrop_address = new Address();
				$boxdrop_address->id_customer = $order->id_customer;
				$boxdrop_address->id_country = $order_address->id_country;
				$boxdrop_address->id_state = 0;
				$boxdrop_address->address1 = $boxdrop_shop->street;
				$boxdrop_address->alias = 'boxdropDropOff - '.date('d-m-Y');
				$boxdrop_address->city = $boxdrop_shop->city;
				$boxdrop_address->company = $boxdrop_shop->company;
				$boxdrop_address->deleted = true;
				$boxdrop_address->firstname = $order_address->firstname;
				$boxdrop_address->lastname = $order_address->lastname;
				$boxdrop_address->postcode = $boxdrop_shop->zip;
				$boxdrop_address->save();
				$order->id_address_delivery = $boxdrop_address->id;
				$order->update();
			}
		}

		/**
		 * Sneaking into the admin order detail view. This will be displayed right under the shipment list in the shipment box.
		 * We'd really love to replace the whole box, but it is coupled too tight into the main template...
		 *
		 * @author sweber <sw@boxdrop.com>
		 * @return string
		 */
		public function displayInfoByCart()
		{
			$order_id = (int)Tools::getValue('id_order');
			if ($order_id == 0)
				return '';

			$this->smarty->assign(array(
				'order_id' => $order_id,
				'products' => BoxdropOrder::getProductsToBeShipped($order_id),
				'shipments' => BoxdropOrderShipment::getByOrderId($order_id),
				'tpl_path' => _PS_MODULE_DIR_.'boxdropshipment/views/templates/hook/'
			));

			return $this->display(__FILE__, 'adminOrderDetailShipping.tpl');
		}

		/**
		 * Loads a file containing SQL queries and executes them.
		 * Expects a $statements array to loop through in that file.
		 *
		 * @author sweber <sw@boxdrop.com>
		 * @param  string $sql_file
		 * @return boolean
		 */
		private function loadAndExecuteDatabaseFile($sql_file)
		{
			$statements = array();

			if (!file_exists($sql_file) || !is_readable($sql_file))
			{
				$this->_errors[] = $this->l('Could not find database file');
				return false;
			}

			include $sql_file;
			if (count($statements) == 0)
			{
				$this->_errors[] = $this->l('Database file does not offer table statements');
				return false;
			}

			foreach ($statements as $statement)
			{
				if (!Db::getInstance()->Execute($statement))
				{
					$this->_errors[] = Db::getInstance()->getMsgError().' '.$statement;
					return false;
				}
			}
			return true;
		}


		/**
		 * Updates carrier IDs upon change
		 * 
		 * @author sweber <sw@boxdrop.com>
		 * @param  array  $params
		 * @return void
		 */
		public function hookActionCarrierUpdate($params)
		{
			if ((int)$params['id_carrier'] == (int)BoxdropHelper::getCarrierId(self::CONF_MODE_DIRECT_ECONOMY))
			{
				BoxdropCarrier::updateUsedCarriers(self::OLD_CARRIER_IDS_DIRECT_ECONOMY, (int)$params['id_carrier']);
				BoxdropHelper::getCarrierId(self::CONF_MODE_DIRECT_ECONOMY, (int)$params['carrier']->id);
			}

			if ((int)$params['id_carrier'] == (int)BoxdropHelper::getCarrierId(self::CONF_MODE_DIRECT_EXPRESS))
			{
				BoxdropCarrier::updateUsedCarriers(self::OLD_CARRIER_IDS_DIRECT_EXPRESS, (int)$params['id_carrier']);
				BoxdropHelper::getCarrierId(self::CONF_MODE_DIRECT_EXPRESS, (int)$params['carrier']->id);
			}
		}
	}
