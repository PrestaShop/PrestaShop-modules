<?php
/**
 * Shopgate GmbH
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file AFL_license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to interfaces@shopgate.com so we can send you a copy immediately.
 *
 * @author    Shopgate GmbH, Schloßstraße 10, 35510 Butzbach <interfaces@shopgate.com>
 * @copyright Shopgate GmbH
 * @license   http://opensource.org/licenses/AFL-3.0 Academic Free License ("AFL"), in the version 3.0
 */

if (!defined('_PS_VERSION_')) exit;
/*
	//Translations
	$this->l('Shopgate order ID:');
*/
define('SHOPGATE_PLUGIN_VERSION', '2.7.0');
define('SHOPGATE_DIR', _PS_MODULE_DIR_.'shopgate/');

require_once(SHOPGATE_DIR.'vendors/shopgate_library/shopgate.php');
require_once(SHOPGATE_DIR.'classes/PSShopgatePlugin.php');
require_once(SHOPGATE_DIR.'classes/PSShopgateOrder.php');
require_once(SHOPGATE_DIR.'classes/PSShopgateConfig.php');
require_once(SHOPGATE_DIR.'classes/PluginModelItemObject.php');
require_once(SHOPGATE_DIR.'classes/PluginModelCategoryObject.php');
require_once(SHOPGATE_DIR.'classes/PSShopgateCheckCart.php');

#define('SHOPGATE_DEBUG', 1);

class ShopGate extends PaymentModule
{
	private $shopgate_trans = array();
	private $configurations = array(
		'SHOPGATE_CARRIER_ID' => 1,
		'PS_OS_SHOPGATE' => 0,
		'SHOPGATE_LANGUAGE_ID' => 0,
		'SHOPGATE_SHIPPING_SERVICE' => 'OTHER',
		'SHOPGATE_MIN_QUANTITY_CHECK' => 0,
		'SHOPGATE_OUT_OF_STOCK_CHECK' => 0,
		'SHOPGATE_PRODUCT_DESCRIPTION' => self::PRODUCT_EXPORT_DESCRIPTION,
		'SHOPGATE_SUBSCRIBE_NEWSLETTER' => 0,
	);

	const PRODUCT_EXPORT_DESCRIPTION = 'DESCRIPTION';
	const PRODUCT_EXPORT_SHORT_DESCRIPTION = 'SHORT';
	const PRODUCT_EXPORT_BOTH_DESCRIPTIONS = 'BOTH';

	private $shipping_service_list = array();
	private $product_export_descriptions = array();

	public function __construct()
	{
		$this->name = 'shopgate';
		if (version_compare(_PS_VERSION_, '1.5.0.0', '<'))
			$this->tab = 'market_place';
		else
			$this->tab = 'mobile';

		$this->version = '2.7.0';
		$this->author = 'Shopgate';
		$this->module_key = '';

		parent::__construct();

		$this->displayName = $this->l('Shopgate');
		$this->description = $this->l('Sell your products with your individual app and a website optimized for mobile devices.');

		//delivery service list
		$this->shipping_service_list = array
		(
			'OTHER' => $this->l('Other'),
			'DHL' => $this->l('DHL'),
			'DHLEXPRESS' => $this->l('DHL Express'),
			'DP' => $this->l('Deutsche Post'),
			'DPD' => $this->l('DPD'),
			'FEDEX' => $this->l('FedEx'),
			'GLS' => $this->l('GLS'),
			'HLG' => $this->l('Hermes'),
			'TNT' => $this->l('TNT'),
			'TOF' => $this->l('trans-o-flex'),
			'UPS' => $this->l('UPS'),
			'LAPOSTE' => $this->l('LA POSTE'),
		);

		$this->product_export_descriptions = array
		(
			self::PRODUCT_EXPORT_DESCRIPTION => $this->l('Description'),
			self::PRODUCT_EXPORT_SHORT_DESCRIPTION => $this->l('Short Description'),
			self::PRODUCT_EXPORT_BOTH_DESCRIPTIONS => $this->l('Short Description + Description'),
		);

		$this->shopgate_trans = array(
			'Bankwire' => $this->l('Bankwire'),
			'Cash on Delivery' => $this->l('Cash on Delivery'),
			'PayPal' => $this->l('PayPal'),
			'Mobile Payment' => $this->l('Mobile Payment'),
			'Shopgate' => $this->l('Shopgate')
		);
	}

	private function setCarrier(Carrier $carrier)
	{
		$carrier->name = 'Shopgate';
		$carrier->is_module = 1;
		$carrier->deleted = 1;
		$carrier->shipping_external = 1;

		// 			if(version_compare(_PS_VERSION_, '1.4.4.0', '<') && version_compare(_PS_VERSION_, '1.4.2.5', '>=')){
		// 				// fix a bug in Prestashop before version 1.4.4.0 classes/cart.php function isCarrierInRange() range behavior
		// 				$carrier->shipping_method = Carrier::SHIPPING_METHOD_FREE;
		// 			}
		if (version_compare(_PS_VERSION_, '1.4.0.1', '<='))
		{
			// calculating shipping costs in Prestashop before version 1.4.4.2
			//$carrier->shipping_method = Carrier::SHIPPING_METHOD_PRICE;
			$carrier->range_behavior = 1;
			$carrier->active = 1;
			$carrier->shipping_handling = 0;
		}

		$carrier->external_module_name = 'shopgate';
		foreach (Language::getLanguages() as $language)
			$carrier->delay[$language['id_lang']] = $this->l('Depends on Shopgate selected carrier');
	}


	private function log($message, $type)
	{
		ShopgateLogger::getInstance()->log($message, $type);
	}

	public function install()
	{
		ShopgateLogger::getInstance()->enableDebug();

		$this->log('INSTALLATION - checking for cURL', ShopgateLogger::LOGTYPE_DEBUG);
		if (!in_array('curl', get_loaded_extensions()))
		{
			$this->log('Installation failed. cURL is not installed or loaded.', ShopgateLogger::LOGTYPE_ERROR);
			return false;
		}

		$this->log('INSTALLATION - calling parent::install()', ShopgateLogger::LOGTYPE_DEBUG);
		$result = parent::install();
		if (!$result)
		{
			$this->log('parent::install() failed; return value: '.var_export($result, true), ShopgateLogger::LOGTYPE_ERROR);
			return false;
		}

		$this->log('INSTALLATION - registering hookpoints', ShopgateLogger::LOGTYPE_DEBUG);
		$hooks = array('header', 'adminOrder', 'updateOrderStatus');
		foreach ($hooks as $hook)
		{
			$this->log('INSTALLATION - registering hookpoint "'.$hook.'"', ShopgateLogger::LOGTYPE_DEBUG);
			$result = $this->registerHook($hook);
			if (!$result)
			{
				$this->log('$this->registerHook("'.$hook.'") failed; return value: '.var_export($result, true), ShopgateLogger::LOGTYPE_ERROR);
				return false;
			}
		}

		// fix for 1.5.x.x there is already a mobile Template. redirect to Shopgate on this template
		if (version_compare(_PS_VERSION_, '1.5.0.0', '>='))
		{
			$this->log('INSTALLATION - version is >= 1.5.0.0 - registering hookpoint "displayMobileHeader"', ShopgateLogger::LOGTYPE_DEBUG);
			$result = $this->registerHook('displayMobileHeader');
			if (!$result)
			{
				$this->log('$this->registerHook("displayMobileHeader") failed; return value: '.var_export($result, true), ShopgateLogger::LOGTYPE_ERROR);
				return false;
			}
		}

		// get ONE instance of the MySQLCore class and use the master database so PrestaShop doesn't get confused
		$this->log('INSTALLATION - fetching database object', ShopgateLogger::LOGTYPE_DEBUG);
		$db = Db::getInstance(true);

		// install or update the database structure
		$this->log('INSTALLATION - updating database', ShopgateLogger::LOGTYPE_DEBUG);
		if (!$this->updateDatabase($db))
		{
			$this->log('installation failed - unable to update database', ShopgateLogger::LOGTYPE_ERROR);
			return false;
		}

		// Create shopgate carrier if not exists
		$this->log('INSTALLATION - fetching Shopgate carrier with statement ...', ShopgateLogger::LOGTYPE_DEBUG);
		if (version_compare(_PS_VERSION_, '1.4.0.1', '<='))
		{
			$this->log('... for version <= 1.4.0.1 ...', ShopgateLogger::LOGTYPE_DEBUG);
			$query = 'SELECT `id_carrier` FROM `'._DB_PREFIX_.'carrier` WHERE `name` = \'Shopgate\'';
		}
		else
		{
			$this->log('... for version > 1.4.0.1 ...', ShopgateLogger::LOGTYPE_DEBUG);
			$query = 'SELECT `id_carrier` FROM `'._DB_PREFIX_.'carrier` WHERE `external_module_name` = \'shopgate\'';
		}
		$this->log('... '.$query, ShopgateLogger::LOGTYPE_DEBUG);
		$id_carrier = (int)$db->getValue($query);

		$this->log('INSTALLATION - creating carrier object with ID: '.var_export($id_carrier, true), ShopgateLogger::LOGTYPE_DEBUG);
		$carrier = new Carrier($id_carrier);
		$this->setCarrier($carrier);

		if (!Validate::isLoadedObject($carrier))
		{
			$this->log('INSTALLATION - adding carrier', ShopgateLogger::LOGTYPE_DEBUG);
			// add new carrier
			if (!$carrier->add())
			{
				$this->log('installation failed: unable to add carrier.', ShopgateLogger::LOGTYPE_ERROR);
				return false;
			}
		}
		else
		{
			$this->log('INSTALLATION - updating carrier', ShopgateLogger::LOGTYPE_DEBUG);
			// update carrier
			if (!$carrier->update())
			{
				$this->log('installation failed: unable to update carrier.', ShopgateLogger::LOGTYPE_ERROR);
				return false;
			}
		}

		if (version_compare(_PS_VERSION_, '1.4.0.1', '<='))
		{
			$this->log('INSTALLATION - checking carrier compatibility for version <= 1.4.0.1', ShopgateLogger::LOGTYPE_DEBUG);
			// fix a bug in Prestashop before version 1.4.4.0 classes/cart.php function isCarrierInRange() range behavior
			if (!$this->carrierCompatibility($carrier))
			{
				$this->log('installation failed: $this->carrierCompatibility returned false.', ShopgateLogger::LOGTYPE_ERROR);
				return false;
			}
		}

		// Creates new order states
		$this->log('INSTALLATION - adding order states', ShopgateLogger::LOGTYPE_DEBUG);
		$this->addOrderState('PS_OS_SHOPGATE', $this->l('Shipping blocked (Shopgate)'));

		// Save default configurations
		$this->log('INSTALLATION - setting config values', ShopgateLogger::LOGTYPE_DEBUG);
		$this->configurations['SHOPGATE_CARRIER_ID'] = $carrier->id;
		$this->configurations['SHOPGATE_LANGUAGE_ID'] = Configuration::get('PS_LANG_DEFAULT');

		$this->log('INSTALLATION - saving configuration values', ShopgateLogger::LOGTYPE_DEBUG);
		foreach ($this->configurations as $name => $value)
		{
			if (!Configuration::updateValue($name, $value))
			{
				$this->log('installation failed: unable to save configuration setting "'.var_export($name, true).'" with value "'.var_export($value, true).'".', ShopgateLogger::LOGTYPE_ERROR);
				return false;
			}
		}

		$shopgateConfig = new ShopgateConfigPresta(
			Configuration::get('SHOPGATE_CONFIG') ?
				unserialize(Configuration::get('SHOPGATE_CONFIG')) :
				array()

		);
		$shopgateConfig->registerPlugin();

		$this->log('INSTALLATION - installation was successful', ShopgateLogger::LOGTYPE_DEBUG);

		return true;
	}

	private function updateDatabase($db)
	{
		$sql_table = '
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'shopgate_order`
		(
			`id_shopgate_order` int(11) NOT NULL AUTO_INCREMENT,
			`id_cart` int(11) NOT NULL DEFAULT \'0\',
			`id_order` int(11) NOT NULL DEFAULT \'0\',
			`order_number` varchar(16) NOT NULL,
			`shop_number` varchar(16) NULL DEFAULT NULL,
			`tracking_number` varchar(32) NOT NULL DEFAULT \'\',
			`shipping_service` varchar(16) NOT NULL DEFAULT \'OTHER\',
			`shipping_cost` decimal(17,2) NOT NULL DEFAULT \'0.00\',
			`comments` text NULL DEFAULT NULL,
			PRIMARY KEY (`id_shopgate_order`),
			UNIQUE KEY `order_number` (`order_number`)
		)
		ENGINE=InnoDB DEFAULT CHARSET=utf8;';

		$this->log('INSTALLATION - adding table "shopgate_order" to database', ShopgateLogger::LOGTYPE_DEBUG);
		if (!$db->Execute($sql_table))
		{
			$this->log('installation failed: unable to add table "shopgate_order" to database. MySQL says: '.var_export($db->getMsgError(), true), ShopgateLogger::LOGTYPE_ERROR);
			return false;
		}

		$this->log('installation after first query', ShopgateLogger::LOGTYPE_ACCESS);

		// Update table with new fields if not existing yet
		$this->log('INSTALLATION - checking for field "comments" inside table "shopgate_order"', ShopgateLogger::LOGTYPE_DEBUG);
		$db->Execute('SHOW COLUMNS FROM `'._DB_PREFIX_.'shopgate_order` LIKE \'comments\';');
		if (!$db->NumRows())
		{
			$this->log('INSTALLATION - creating field "comments" inside table "shopgate_order"', ShopgateLogger::LOGTYPE_DEBUG);
			if ($db->Execute('ALTER TABLE `'._DB_PREFIX_.'shopgate_order` ADD `comments` text NULL DEFAULT NULL AFTER `shipping_cost`;') === false)
			{
				$this->log('installation failed: unable to add field "comments" to table "shopgate_order". MySQL says: '.var_export($db->getMsgError(), true), ShopgateLogger::LOGTYPE_ERROR);
				return false;
			}
		}

		$this->log('INSTALLATION - checking for field "shop_number" inside table "shopgate_order"', ShopgateLogger::LOGTYPE_DEBUG);
		$db->Execute('SHOW COLUMNS FROM `'._DB_PREFIX_.'shopgate_order` LIKE \'shop_number\';');
		if (!$db->NumRows())
		{
			$this->log('INSTALLATION - creating field "shop_number" inside table "shopgate_order"', ShopgateLogger::LOGTYPE_DEBUG);
			if ($db->Execute('ALTER TABLE `'._DB_PREFIX_.'shopgate_order` ADD `shop_number` varchar(16) NULL DEFAULT NULL AFTER `shipping_cost`;') === false)
			{
				$this->log('installation failed: unable to add field "shop_number" to table "shopgate_order". MySQL says: '.var_export($db->getMsgError(), true), ShopgateLogger::LOGTYPE_ERROR);
				return false;
			}
		}

		$this->log('INSTALLATION - database updates have been performed successfully', ShopgateLogger::LOGTYPE_DEBUG);
		return true;
	}

	private function carrierCompatibility($carrier)
	{
		// fix a bug in Prestashop before version 1.4.4.0 classes/cart.php function isCarrierInRange() range behavior
		$this->log('INSTALLATION - checking carrier compatibility; getting price range', ShopgateLogger::LOGTYPE_DEBUG);
		$rangePrices = RangePrice::getRanges($carrier->id);

		if (empty($rangePrices))
		{
			$this->log('INSTALLATION - price range was empty, creating new one', ShopgateLogger::LOGTYPE_DEBUG);
			$rangePrice = new RangePrice();
			$rangePrice->id_carrier = $carrier->id;
			$rangePrice->delimiter1 = 0.0;
			$rangePrice->delimiter2 = 1000000.0;

			$this->log('INSTALLATION - adding price range', ShopgateLogger::LOGTYPE_DEBUG);
			if (!$rangePrice->add())
			{
				$this->log('installation failed: unable to add price range.', ShopgateLogger::LOGTYPE_ERROR);
				return false;
			}
		}
		else
		{
			$this->log('INSTALLATION - price range was found', ShopgateLogger::LOGTYPE_DEBUG);
			$rangePrice = new RangeWeight($rangePrices[0]['id_range_price']);
		}

		$this->log('INSTALLATION - getting weight range', ShopgateLogger::LOGTYPE_DEBUG);
		$rangeWeights = RangeWeight::getRanges($carrier->id);

		if (empty($rangeWeights))
		{
			$this->log('INSTALLATION - weight range was empty, creating new one', ShopgateLogger::LOGTYPE_DEBUG);
			$rangeWeight = new RangeWeight();
			$rangeWeight->id_carrier = $carrier->id;
			$rangeWeight->delimiter1 = 0.0;
			$rangeWeight->delimiter2 = 1000000.0;

			if (!$rangeWeight->add())
			{
				$this->log('installation failed: unable to weight price range.', ShopgateLogger::LOGTYPE_ERROR);
				return false;
			}
		}
		else
		{
			$this->log('INSTALLATION - weight range was found', ShopgateLogger::LOGTYPE_DEBUG);
			$rangeWeight = new RangeWeight($rangeWeights[0]['id_range_weight']);
		}

		// Zones
		$this->log('INSTALLATION - getting zones', ShopgateLogger::LOGTYPE_DEBUG);
		$zones = Zone::getZones();

		foreach ($zones as $zone)
			$carrier->addZone($zone['id_zone']);

		// create for each zone delivery options
		$this->log('INSTALLATION - creating delivery options for zones', ShopgateLogger::LOGTYPE_DEBUG);
		foreach ($zones as $zone)
		{
			$this->log('INSTALLATION - creating delivery options by weight for zone '.var_export($zone, true), ShopgateLogger::LOGTYPE_DEBUG);
			$deliveryRangeWeight = new Delivery();
			$deliveryRangeWeight->id_carrier = $carrier->id;
			$deliveryRangeWeight->id_range_weight = $rangeWeight->id;
			$deliveryRangeWeight->id_range_price = 0;
			$deliveryRangeWeight->price = 0;
			$deliveryRangeWeight->id_zone = $zone['id_zone'];

			if (!$deliveryRangeWeight->add(true, true))
			{
				$this->log('installation failed: unable to create delivery options by weight for zone '.var_export($zone, true), ShopgateLogger::LOGTYPE_ERROR);
				return false;
			}

			$this->log('INSTALLATION - creating delivery options by price for zone '.var_export($zone, true), ShopgateLogger::LOGTYPE_DEBUG);
			$deliveryRangePrice = new Delivery();
			$deliveryRangePrice->id_carrier = $carrier->id;
			$deliveryRangePrice->id_range_price = $rangePrice->id;
			$deliveryRangePrice->id_range_weight = 0;
			$deliveryRangePrice->price = 0;
			$deliveryRangePrice->id_zone = $zone['id_zone'];

			if (!$deliveryRangePrice->add(true, true))
			{
				$this->log('installation failed: unable to create delivery options by price for zone '.var_export($zone, true), ShopgateLogger::LOGTYPE_ERROR);
				return false;
			}
		}

		return true;
	}

	public function uninstall()
	{
		$shopgateConfig = new ShopgateConfigPresta(
			Configuration::get('SHOPGATE_CONFIG') ?
				unserialize(Configuration::get('SHOPGATE_CONFIG')) :
				array());

		$carrier = Db::getInstance()->ExecuteS('SELECT `id_carrier` FROM `'._DB_PREFIX_.'carrier` WHERE `name` = "Shopgate"');

		if (!empty($carrier))
		{
			$shopgateCarrierId = (int)$carrier[0]['id_carrier'];

			// delete delivery options
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'delivery` WHERE `id_carrier` = '.$shopgateCarrierId);

			// delete price ranges
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'range_price` WHERE `id_carrier` = '.$shopgateCarrierId);

			// delete weight ranges
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'range_weight` WHERE `id_carrier` = '.$shopgateCarrierId);

			// delete carrier languages
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'carrier_lang` WHERE `id_carrier` = '.$shopgateCarrierId);

			// delete carrier zones
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'carrier_zone` WHERE `id_carrier` = '.$shopgateCarrierId);

			// dont delete carrier
			//$result = Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'carrier` WHERE `id_carrier` = '.$shopgateCarrierId);

		}

		// Disable shopgate api
		$shopgateConfig->setShopIsActive(false);
		try {
			$shopgateConfig->saveFile(array('shop_is_active'));
		} catch (ShopgateLibraryException $ex) {
			$exception = $ex;
		}

		// Keeps order states
		unset($this->configurations['PS_OS_SHOPGATE']);

		// Remove configurations
		foreach ($this->configurations as $name => $value)
		{
			if (!Configuration::deleteByName($name))
				return false;
		}

		// delete config from database
		Configuration::deleteByName('SHOPGATE_CONFIG');

		// delete myconfig.php
		$shopgateConfig->deleteFile();

		// Uninstall
		return parent::uninstall();
	}

	public function getTranslation($string)
	{
		return array_key_exists($string, $this->shopgate_trans) ? $this->shopgate_trans[$string] : $string;
	}


	private function addOrderState($state, $name)
	{
		$orderState = new OrderState((int)Configuration::get($state));
		if (!Validate::isLoadedObject($orderState))
		{
			//Creating new order state
			$orderState->color = 'lightblue';
			$orderState->unremovable = 1;
			$orderState->name = array();
			foreach (Language::getLanguages() as $language)
				$orderState->name[$language['id_lang']] = $name;
			if (!$orderState->add())
				return false;

			copy(dirname(__FILE__).'/logo.gif', dirname(__FILE__).'/../../img/os/'.(int)$orderState->id.'.gif');
		}

		return ($this->configurations[$state] = $orderState->id);
	}


	/**
	 * Carrie module methods
	 *
	 * @param $params
	 * @param $shipping_cost
	 *
	 * @return float
	 */
	public function getOrderShippingCost($params, $shipping_cost)
	{
		return (float)($this->getOrderShippingCostExternal($params) + $shipping_cost);
	}

	public function getOrderShippingCostExternal($cart)
	{
		$shopgateOrder = PSShopgateOrder::instanceByCartId($cart->id);

		return Validate::isLoadedObject($shopgateOrder) ? $shopgateOrder->shipping_cost : 0;
	}

	public function doMobileRedirect()
	{
		$indexFile = 'index.php';
		if (version_compare(_PS_VERSION_, '1.4.1.0', '>=') && Configuration::get('PS_HOMEPAGE_PHP_SELF') !== false)
			$indexFile = Configuration::get('PS_HOMEPAGE_PHP_SELF');

		$shopgateConfig = new ShopgateConfigPresta(
			Configuration::get('SHOPGATE_CONFIG') ?
				unserialize(Configuration::get('SHOPGATE_CONFIG')) :
				array()
		);

		// instantiate and set up redirect class
		$shopgateBuilder = new ShopgateBuilder($shopgateConfig);
		$shopgateRedirector = $shopgateBuilder->buildRedirect();

		/* redirect logic */
		$controller = Tools::getValue('controller');
		if ($id_product = Tools::getValue('id_product', 0))
		{
			$productId = PSShopgatePlugin::PREFIX.$id_product.'_0';
			$shopgateJsHeader = $shopgateRedirector->buildScriptItem($productId);
		}
		elseif ($id_category = Tools::getValue('id_category', 0))
			$shopgateJsHeader = $shopgateRedirector->buildScriptCategory($id_category);
		elseif (isset($_SERVER['SCRIPT_FILENAME']) && mb_strpos($_SERVER['SCRIPT_FILENAME'], $indexFile) !== false && empty($controller))
			// TODO: doesn't work yet!
			$shopgateJsHeader = $shopgateRedirector->buildScriptShop();
		else
			$shopgateJsHeader = $shopgateRedirector->buildScriptDefault();

		return $shopgateJsHeader;
	}

	public function hookHeader()
	{
		return $this->doMobileRedirect();
	}

	public function hookDisplayMobileHeader()
	{
		return $this->doMobileRedirect();
	}

	public function hookUpdateOrderStatus($params)
	{
		$id_order = $params['id_order'];
		$orderState = $params['newOrderStatus'];
		$shopgateOrder = PSShopgateOrder::instanceByOrderId($id_order);

		$shopgateConfig = new ShopgateConfigPresta(
			Configuration::get('SHOPGATE_CONFIG') ?
				unserialize(Configuration::get('SHOPGATE_CONFIG')) :
				array()
		);
		$shopgateBuilder = new ShopgateBuilder($shopgateConfig);
		$shopgateMerchantApi = $shopgateBuilder->buildMerchantApi();

		if (!Validate::isLoadedObject($shopgateOrder))
			return;

		try {
			switch ($orderState->id)
			{
				case _PS_OS_DELIVERED_:
					$shopgateMerchantApi->setOrderShippingCompleted($shopgateOrder->order_number);
					break;
				case _PS_OS_SHIPPING_:
					$shopgateMerchantApi->addOrderDeliveryNote($shopgateOrder->order_number, $shopgateOrder->shipping_service, $shopgateOrder->tracking_number, true, false);
					break;
				default:
					break;
			}
		} catch (ShopgateMerchantApiException $e) {
			$msg = new Message();
			$msg->message = $this->l('On order state').': '.$orderState->name.' - '.$this->l('Shopgate status was not updated because of following error').': '.$e->getMessage();
			$msg->id_order = $id_order;
			$msg->id_employee = isset($params['cookie']->id_employee) ? $params['cookie']->id_employee : 0;
			$msg->private = true;
			$msg->add();
		}
	}

	public function hookAdminOrder($params)
	{
		include_once dirname(__FILE__).'/backward_compatibility/backward.php';

		$id_order = $params['id_order'];

		$shopgateOrder = PSShopgateOrder::instanceByOrderId($id_order);

		if (Tools::isSubmit('updateShopgateOrder'))
		{
			$shopgate_order = Tools::getValue('shopgateOrder');
			$shippingService = $shopgate_order['shipping_service'];
			$trackingNumber = $shopgate_order['tracking_number'];

			if (isset($shippingService))
			{
				try {
					$shopgateConfig = new ShopgateConfigPresta(
						Configuration::get('SHOPGATE_CONFIG') ?
							unserialize(Configuration::get('SHOPGATE_CONFIG')) :
							array()
					);
					$shopgateBuilder = new ShopgateBuilder($shopgateConfig);
					$shopgateMerchantApi = $shopgateBuilder->buildMerchantApi();
					$shopgateMerchantApi->addOrderDeliveryNote($shopgateOrder->order_number, $shippingService, $trackingNumber, true, false);

				} catch (ShopgateMerchantApiException $e) {
					$error = $e->getMessage();
				}
				$shopgateOrder->shipping_service = $shippingService;
				$shopgateOrder->tracking_number = $trackingNumber;
				$shopgateOrder->update();
			}
		}

		if (!Validate::isLoadedObject($shopgateOrder))
			return '';

		$sOrder = new ShopgateOrder();
		$error = null;
		try {
			$shopgateConfig = new ShopgateConfigPresta(
				Configuration::get('SHOPGATE_CONFIG') ?
					unserialize(Configuration::get('SHOPGATE_CONFIG')) :
					array()
			);
			$shopgateBuilder = new ShopgateBuilder($shopgateConfig);
			$shopgateMerchantApi = $shopgateBuilder->buildMerchantApi();
			$orders = $shopgateMerchantApi->getOrders(array('order_numbers[0]' => $shopgateOrder->order_number));
			foreach ($orders->getData() as $o)
			{
				/* @var $o ShopgateOrder */
				if ($o->getOrderNumber() == $shopgateOrder->order_number)
					$sOrder = $o;
			}
		} catch (Exception $e) {
			$error = $e->getMessage();
		}

		$paymentInfoStrings = array(
			'shopgate_payment_name' => $this->l('Payment name'),
			'upp_transaction_id' => $this->l('Transaction ID'),
			'authorization' => $this->l('Authorization'),
			'settlement' => $this->l('Settlement'),
			'purpose' => $this->l('Purpose'),
			'billsafe_transaction_id' => $this->l('Transaction ID'),
			'reservation_number' => $this->l('Reservation number'),
			'activation_invoice_number' => $this->l('Invoice activation number'),
			'bank_account_holder' => $this->l('Account holder'),
			'bank_account_number' => $this->l('Account number'),
			'bank_code' => $this->l('Bank code'),
			'bank_name' => $this->l('Bank name'),
			'iban' => $this->l('IBAN'),
			'bic' => $this->l('BIC'),
			'transaction_id' => $this->l('Transaction ID'),
			'payer_id' => $this->l('Payer ID'),
			'payer_email' => $this->l('Payer email')
		);

		$sOrderDeliveryNotes = array();

		if (is_array($sOrder->getDeliveryNotes()))
		{
			foreach ($sOrder->getDeliveryNotes() as $notes)
			{
				$sOrderDeliveryNotes[] = array(
					'shipping_service_id' => $notes->getShippingServiceId(),
					'tracking_number' => $notes->getTrackingNumber(),
					'shipping_time' => $notes->getShippingTime(),
				);
			}
		}

		// build comments
		$comments = array();
		foreach ($sOrder->jsonDecode($shopgateOrder->comments) as $text => $information)
			$comments[] = sprintf($this->l($text), $information);

		$this->context->smarty->assign('sOrder', $sOrder);
		$this->context->smarty->assign('sOrderComments', $comments);
		$this->context->smarty->assign('sOrderPaymentInfos', $sOrder->getPaymentInfos());
		$this->context->smarty->assign('sOrderDeliveryNotes', $sOrderDeliveryNotes);
		$this->context->smarty->assign('sShopNumber', $shopgateOrder->shop_number);
		$this->context->smarty->assign('shopgate_error', $error);
		$this->context->smarty->assign('paymentInfoStrings', $paymentInfoStrings);
		$this->context->smarty->assign('shopgateOrder', $shopgateOrder);
		$this->context->smarty->assign('shippingInfos', $sOrder->getShippingInfos());
		$this->context->smarty->assign('shipping_service_list', $this->shipping_service_list);
		$this->context->smarty->assign('sModDir', $this->_path);
		$this->context->smarty->assign('api_url', $this->getApiUrl());

		return $this->display(__FILE__, 'views/templates/admin/admin_order.tpl');
	}

	public function getContent()
	{
		include_once dirname(__FILE__).'/backward_compatibility/backward.php';

		$output = '';
		$shopgateConfig = new ShopgateConfigPresta(
			Configuration::get('SHOPGATE_CONFIG') ?
				unserialize(Configuration::get('SHOPGATE_CONFIG')) :
				array()
		);

		$bools = array('true' => true, 'false' => false);

		/** @var CarrierCore $carrierModel */
		$carrierModel = new Carrier();
		$carrierCollection = $carrierModel->getCarriers($this->context->language->id);

		$settingKeys = array(
			'SHOPGATE_SHIPPING_SERVICE',
			'SHOPGATE_MIN_QUANTITY_CHECK',
			'SHOPGATE_OUT_OF_STOCK_CHECK',
			'SHOPGATE_PRODUCT_DESCRIPTION',
			'SHOPGATE_SUBSCRIBE_NEWSLETTER',
		);
		$carriers = array();

		foreach ($carrierCollection as $carrier)
		{
			$configKey = 'SHOPGATE_CARRIER_MAPPING_'.$carrier['id_carrier'];
			$carriers[$configKey] = $carrier;
			$settingKeys[] = $configKey;
		}

		if (Tools::isSubmit('saveConfigurations'))
		{
			$configs = Tools::getValue('configs', array());
			foreach ($configs as $name => $value)
			{
				if (isset($bools[$value]))
					$configs[$name] = $bools[$value];
				$configs[$name] = htmlentities($configs[$name]);
			}

			$configs['use_stock'] = !((bool)Configuration::get('PS_ORDER_OUT_OF_STOCK'));

			$settings = Tools::getValue('settings', array());

			foreach ($settings as $key => $value)
			{
				if (in_array($key, $settingKeys))
					Configuration::updateValue($key, htmlentities($value, ENT_QUOTES));
			}
			$languageID = Configuration::get('PS_LANG_DEFAULT');
			if (Validate::isLanguageIsoCode($configs['language']))
				$languageID = Language::getIdByIso($configs['language']);
			Configuration::updateValue('SHOPGATE_LANGUAGE_ID', $languageID);

			try {
				$shopgateConfig->loadArray($configs);
				$shopgateConfig->initFolders();
				$shopgateConfig->save(array_keys($configs));
				$output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Configurations updated').'</div>';
			} catch (ShopgateLibraryException $e) {
				$output .= '<div class="conf error"><img src="../img/admin/error.png" alt="'.$this->l('Error').'" />'.$this->l('Error').': '.$e->getAdditionalInformation().'</div>';
			}
		}

		$langs = array();
		foreach (Language::getLanguages() as $id => $l)
			$langs[Tools::strtoupper($l['iso_code'])] = $l['name'];

		$servers = array(
			'live' => $this->l('Live'),
			'pg' => $this->l('Playground'),
			'custom' => $this->l('Custom')
		);

		$enables = array();

		$settings = Configuration::getMultiple($settingKeys);
		/**
		 * read config from db
		 */
		$shopgateConfig = new ShopgateConfigPresta(
			Configuration::get('SHOPGATE_CONFIG') ?
				unserialize(Configuration::get('SHOPGATE_CONFIG')) :
				array()
		);
		$configs = $shopgateConfig->toArray();


		$this->context->smarty->assign('settings', $settings);
		$this->context->smarty->assign('shipping_service_list', $this->shipping_service_list);
		$this->context->smarty->assign('langs', $langs);
		$this->context->smarty->assign('currencies', Currency::getCurrencies());
		$this->context->smarty->assign('servers', $servers);
		$this->context->smarty->assign('enables', $enables);
		$this->context->smarty->assign('configs', $configs);
		$this->context->smarty->assign('mod_dir', $this->_path);
		$this->context->smarty->assign('api_url', $this->getApiUrl());
		$this->context->smarty->assign('offer_url', $this->getOfferLink());
		$this->context->smarty->assign('video_url', $this->getVideoLink());
		$this->context->smarty->assign('product_export_descriptions', $this->product_export_descriptions);
		$this->context->smarty->assign('carrier_list', $carriers);

		return $output.$this->display(__FILE__, 'views/templates/admin/configurations.tpl');
	}

	/**
	 * returns the api url
	 *
	 * @return string
	 */
	protected function getApiUrl()
	{
		$api_url = 'http://';

		/** @var ShopCore $shopModel */
		$shopModel = $this->context->shop;
		if ($shopModel->domain)
		{
			if ($shopModel->domain)
				$api_url = $api_url.$shopModel->domain;
			if ($shopModel->physical_uri)
				$api_url = $api_url.$shopModel->physical_uri;
			if ($shopModel->virtual_uri)
				$api_url = $api_url.$shopModel->virtual_uri;
		}
		else
			$api_url = _PS_BASE_URL_.__PS_BASE_URI__;

		return $api_url.'modules/shopgate/api.php';
	}

	/**
	 * returns the offer link by iso code
	 *
	 * @return string
	 */
	protected function getOfferLink()
	{
		$query = 'https://www.shopgate.com/%s/prestashop_offer';

		switch ($this->context->language->iso_code)
		{
			case 'de' :
				$country = 'de';
				break;
			case 'pl' :
				$country = 'pl';
				break;
			case 'fr' :
				$country = 'fr';
				break;
			default :
				$country = 'us';
		}

		return sprintf($query, $country);
	}

	protected function getVideoLink()
	{
		switch ($this->context->language->iso_code)
		{
			case 'de' :
				$url = '//www.youtube.com/embed/z7EY_nakQDc?controls=0&showinfo=0&rel=0';
				break;
			case 'pl' :
				$url = '//www.youtube.com/embed/nx6d2L2J4y8?controls=0&showinfo=0&rel=0';
				break;
			case 'fr' :
				$url = '//www.youtube.com/embed/0cbXcocbgkA?controls=0&showinfo=0&rel=0';
				break;
			default :
				$url = '//www.youtube.com/embed/I6UcmbGdZcw?controls=0&showinfo=0&rel=0';
		}

		return $url;
	}
}
