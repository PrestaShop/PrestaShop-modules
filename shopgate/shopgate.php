<?php
/*
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
* @author Shopgate GmbH, Schloßstraße 10, 35510 Butzbach <interfaces@shopgate.com>
* @copyright  Shopgate GmbH
* @license   http://opensource.org/licenses/AFL-3.0 Academic Free License ("AFL"), in the version 3.0
*/

if (!defined('_PS_VERSION_')) exit;
/*
	//Translations
	$this->l('Shopgate order ID:');
*/
define('SHOPGATE_PLUGIN_VERSION', '2.3.7');
define('SHOPGATE_DIR', _PS_MODULE_DIR_.'shopgate/');

require_once(SHOPGATE_DIR.'vendors/shopgate_library/shopgate.php');
require_once(SHOPGATE_DIR.'classes/PSShopgatePlugin.php');
require_once(SHOPGATE_DIR.'classes/PSShopgateOrder.php');
require_once(SHOPGATE_DIR.'classes/PSShopgateConfig.php');

class ShopGate extends PaymentModule {

    /**
     * default offer link format
     */
    const DEFAULT_OFFER_LINK_FORMAT = 'http://www.shopgate.com/%s/prestashop_offer';

    /**
     * offer link mapping
     *
     * @var array
     */
    private $_offer_mapping = array(
        'en-us'     => 'us',
        'de'        => 'de',
        'gb'        => 'uk',
        'fr'        => 'fr',
        'default'   => 'uk'
    );

	private $shopgate_trans = array();
	private $configurations = array(
		'SHOPGATE_CARRIER_ID' => 1,
		'PS_OS_SHOPGATE' => 0,
		'SHOPGATE_LANGUAGE_ID' => 0,
		'SHOPGATE_SHIPPING_SERVICE' => 'OTHER',
		'SHOPGATE_MIN_QUANTITY_CHECK' => 0,
		'SHOPGATE_OUT_OF_STOCK_CHECK' => 0,
	);
	
	private $shipping_service_list = array();
	
	function __construct() {
		$this->name = 'shopgate';
		if(version_compare(_PS_VERSION_, '1.5', '>='))
		{
			$this->tab = 'mobile';
		} else {
			$this->tab = 'market_place';
		}
		
		$this->version = SHOPGATE_PLUGIN_VERSION;
		$this->author = 'Shopgate';
		$this->module_key = "";
	
		parent::__construct();
	
		$this->displayName = $this->l('Shopgate');
		$this->description = $this->l('Sell your products with your individual app and a website optimized for mobile devices.');
	
		//delivery service list
		$this->shipping_service_list = array
		(
			'OTHER'		=> $this->l('Other'),
			'DHL'		=> $this->l('DHL'),
			'DHLEXPRESS'	=> $this->l('DHL Express'),
			'DP'		=> $this->l('Deutsche Post'),
			'DPD'		=> $this->l('DPD'),
			'FEDEX'		=> $this->l('FedEx'),
			'GLS'		=> $this->l('GLS'),
			'HLG'		=> $this->l('Hermes'),
			'TNT'		=> $this->l('TNT'),
			'TOF'		=> $this->l('trans-o-flex'),
			'UPS'		=> $this->l('UPS'),
		);
		
		$this->shopgate_trans = array(
			'Bankwire'		 => $this->l('Bankwire'),
			'Cash on Delivery' => $this->l('Cash on Delivery'),
			'PayPal'		   => $this->l('PayPal'),
			'Mobile Payment'   => $this->l('Mobile Payment'),
			'Shopgate'		 => $this->l('Shopgate')
		);
	}
	
	private function setCarrier(Carrier $carrier){
		$carrier->name = 'Shopgate';
		$carrier->is_module = 1;
		$carrier->deleted = 1;
		$carrier->shipping_external = 1;
			
		// 			if(version_compare(_PS_VERSION_, '1.4.4.0', '<') && version_compare(_PS_VERSION_, '1.4.2.5', '>=')){
		// 				// fix a bug in Prestashop before version 1.4.4.0 classes/cart.php function isCarrierInRange() range behavior
		// 				$carrier->shipping_method = Carrier::SHIPPING_METHOD_FREE;
		// 			}
		if(version_compare(_PS_VERSION_, '1.4.0.1', '<=')){
			// calculating shipping costs in Prestashop before version 1.4.4.2
			//$carrier->shipping_method = Carrier::SHIPPING_METHOD_PRICE;
			$carrier->range_behavior = 1;
			$carrier->active = 1;
			$carrier->shipping_handling = 0;
		}
		
		$carrier->external_module_name = 'shopgate';
		foreach (Language::getLanguages() as $language){
			$carrier->delay[$language['id_lang']] = $this->l('Depends on Shopgate selected carrier');
		}
	}
	
	
	private function log($message, $type){
		ShopgateLogger::getInstance()->log($message, $type);
	}
	
	function install() {
		$this->log('starting installation', ShopgateLogger::LOGTYPE_ACCESS);
		if(!($parent=parent::install()) || !$this->registerHook('header') || !$this->registerHook('adminOrder') || !$this->registerHook('updateOrderStatus')){
			$this->log('installation failed: parent::install(): #'. $parent.'# ', ShopgateLogger::LOGTYPE_ACCESS);
				return false;
		}
		
		if(!in_array('curl', get_loaded_extensions())){
			$this->log('installation curl extension isn\'t loaded', ShopgateLogger::LOGTYPE_ACCESS);
			return false;
		}
		
		// fix for 1.5.x.x there is already a mobile Template. redirect to Shopgate on this template
		if(version_compare(_PS_VERSION_, '1.5.0.0', '>=')){
			if(!$this->registerHook('displayMobileHeader')){
				$this->log('installation add hook "displayMobileHeader" failed', ShopgateLogger::LOGTYPE_ACCESS);
				return false;
			}
		}
		
		// get ONE instance of the MySQLCore class and use the master database so PrestaShop doesn't get confused
		$db = Db::getInstance(true);
		
		// install or update the database structure
		$this->updateDatabase($db);
		
		// Create shopgate carrier if not exists
		if(version_compare(_PS_VERSION_, '1.4.0.1', '<=')){
			$id_carrier = (int)$db->getValue('SELECT `id_carrier` FROM `'._DB_PREFIX_.'carrier` WHERE `name` = \'Shopgate\'');
		} else {
			$id_carrier = (int)$db->getValue('SELECT `id_carrier` FROM `'._DB_PREFIX_.'carrier` WHERE `external_module_name` = \'shopgate\'');
		}
		
		$carrier = new Carrier($id_carrier);
		$this->setCarrier($carrier);
		
		$this->log('installation create carrier object', ShopgateLogger::LOGTYPE_ACCESS);
		
		if(!Validate::isLoadedObject($carrier)) {
			// add new carrier
			if(!$carrier->add()){
				$this->log('installation adding carrier failed', ShopgateLogger::LOGTYPE_ACCESS);
				return false;
			}
		} else {
			// update carrier
			if(!$carrier->update()){
				$this->log('installation updating carrier failed', ShopgateLogger::LOGTYPE_ACCESS);
				return false;
			}
		}
		
		$this->log('installation after adding/updating carrier object', ShopgateLogger::LOGTYPE_ACCESS);
		
		if(version_compare(_PS_VERSION_, '1.4.0.1', '<=')){
			// fix a bug in Prestashop before version 1.4.4.0 classes/cart.php function isCarrierInRange() range behavior
			if($this->carrierCompatibility($carrier) == false){
				$this->log('installation carrierCompatibility() failed', ShopgateLogger::LOGTYPE_ACCESS);
				return false;
			}
		}
		
		$this->log('installation before adding order status', ShopgateLogger::LOGTYPE_ACCESS);
		
		// Creates new order states
		$this->addOrderState('PS_OS_SHOPGATE', $this->l('Shipping blocked (Shopgate)'));
		
		// Save default configurations
		$this->configurations['SHOPGATE_CARRIER_ID'] = $carrier->id;
		$this->configurations['SHOPGATE_LANGUAGE_ID'] = Configuration::get('PS_LANG_DEFAULT');
		
		$this->log('installation starting configurations update', ShopgateLogger::LOGTYPE_ACCESS);
		
		foreach($this->configurations as $name => $value) {
			if(!Configuration::updateValue($name, $value)) {
				$this->log('installation updating configuration values failed', ShopgateLogger::LOGTYPE_ACCESS);
				return false;
			}
		}
	
		return true;
	}
	
	private function updateDatabase($db){
		$this->log('installation start database update', ShopgateLogger::LOGTYPE_ACCESS);
		
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
		 
		if(!$db->Execute($sql_table)) {
			$this->log('installation database update failed', ShopgateLogger::LOGTYPE_ACCESS);
			return false;
		}
		
		$this->log('installation after first query', ShopgateLogger::LOGTYPE_ACCESS);
		
		// Update table with new fields if not existing yet
		$db->Execute('SHOW COLUMNS FROM `'._DB_PREFIX_.'shopgate_order` LIKE \'comments\';');
		if (!$db->NumRows()) {
			if ($db->Execute('ALTER TABLE `'._DB_PREFIX_.'shopgate_order` ADD `comments` text NULL DEFAULT NULL AFTER `shipping_cost`;') === false) {
				$this->log('installation adding field comments failed', ShopgateLogger::LOGTYPE_ACCESS);
				return false;
			}
		}
		
		$this->log('installation after second query', ShopgateLogger::LOGTYPE_ACCESS);
		
		$db->Execute('SHOW COLUMNS FROM `'._DB_PREFIX_.'shopgate_order` LIKE \'shop_number\';');
		if (!$db->NumRows()) {
			if ($db->Execute('ALTER TABLE `'._DB_PREFIX_.'shopgate_order` ADD `shop_number` varchar(16) NULL DEFAULT NULL AFTER `shipping_cost`;') === false) {
				$this->log('installation adding field shop_number failed', ShopgateLogger::LOGTYPE_ACCESS);
				return false;
			}
		}
		$this->log('installation end database update', ShopgateLogger::LOGTYPE_ACCESS);
	}
	
	private function carrierCompatibility($carrier){
		// fix a bug in Prestashop before version 1.4.4.0 classes/cart.php function isCarrierInRange() range behavior
		$rangePrices = RangePrice::getRanges($carrier->id);
		
		if(empty($rangePrices)){
			$rangePrice = new RangePrice();
			$rangePrice->id_carrier = $carrier->id;
			$rangePrice->delimiter1 = 0.0;
			$rangePrice->delimiter2 = 1000000.0;
		
			if(!$rangePrice->add()){
				$this->log('installation adding rangePrice failed', ShopgateLogger::LOGTYPE_ACCESS);
				return false;
			}
		} else {
			$rangePrice = new RangeWeight($rangePrices[0]['id_range_price']);
		}
			
		$rangeWeights = RangeWeight::getRanges($carrier->id);
			
		if(empty($rangeWeights)){
			$rangeWeight = new RangeWeight();
			$rangeWeight->id_carrier = $carrier->id;
			$rangeWeight->delimiter1 = 0.0;
			$rangeWeight->delimiter2 = 1000000.0;
		
			if(!$rangeWeight->add()){
				$this->log('installation adding rangeWeight failed', ShopgateLogger::LOGTYPE_ACCESS);
				return false;
			}
		} else {
			$rangeWeight = new RangeWeight($rangeWeights[0]['id_range_weight']);
		}
			
		// Zones
		$zones = Zone::getZones();
			
		foreach($zones as $zone){
			$carrier->addZone($zone['id_zone']);
		}
			
		// create for each zone delivery options
		foreach($zones as $zone){
			$deliveryRangeWeight = new Delivery();
			$deliveryRangeWeight->id_carrier = $carrier->id;
			$deliveryRangeWeight->id_range_weight = $rangeWeight->id;
			$deliveryRangeWeight->id_range_price = 0;
			$deliveryRangeWeight->price = 0;
			$deliveryRangeWeight->id_zone = $zone['id_zone'];
		
			if(!$deliveryRangeWeight->add(true, true)){
				$this->log('installation adding deliveryRangeWeight failed', ShopgateLogger::LOGTYPE_ACCESS);
				return false;
			}
		
			$deliveryRangePrice = new Delivery();
			$deliveryRangePrice->id_carrier = $carrier->id;
			$deliveryRangePrice->id_range_price = $rangePrice->id;
			$deliveryRangePrice->id_range_weight = 0;
			$deliveryRangePrice->price = 0;
			$deliveryRangePrice->id_zone = $zone['id_zone'];
			if(!$deliveryRangePrice->add(true, true)){
				$this->log('installation adding deliveryRangePrice failed', ShopgateLogger::LOGTYPE_ACCESS);
				return false;
			}
		}
	}
	
	function uninstall() {
		$shopgateConfig = new ShopgateConfigPresta();
		
		$carrier = Db::getInstance()->ExecuteS('SELECT `id_carrier` FROM `'._DB_PREFIX_.'carrier` WHERE `name` = "Shopgate"');

		if(!empty($carrier)){
			$shopgateCarrierId = (int)$carrier[0]['id_carrier'];
			
			// delete delivery options
			$result = Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'delivery` WHERE `id_carrier` = '.$shopgateCarrierId);
			
			// delete price ranges
			$result = Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'range_price` WHERE `id_carrier` = '.$shopgateCarrierId);

			// delete weight ranges
			$result = Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'range_weight` WHERE `id_carrier` = '.$shopgateCarrierId);
			
			// delete carrier languages
			$result = Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'carrier_lang` WHERE `id_carrier` = '.$shopgateCarrierId);
			
			// delete carrier zones
			$result = Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'carrier_zone` WHERE `id_carrier` = '.$shopgateCarrierId);
			
			// dont delete carrier
			//$result = Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'carrier` WHERE `id_carrier` = '.$shopgateCarrierId);
			
		}
		
		// Disable shopgate api
		$shopgateConfig->setShopIsActive(false);
		try {
			$shopgateConfig->saveFile(array('shop_is_active'));
		} catch(ShopgateLibraryException $ex){}
	
		// Keeps order states
		unset($this->configurations['PS_OS_SHOPGATE']);
	
		// Remove configurations
		foreach($this->configurations as $name => $value) {
		 	if(!Configuration::deleteByName($name)) {
				return false;
			}
		}
		// Uninstall
		return parent::uninstall();
	}
	
	public function getTranslation($string) {
		return array_key_exists($string, $this->shopgate_trans) ? $this->shopgate_trans[$string] : $string;
	}
	
	
	private function addOrderState($state, $name) {
		$orderState = new OrderState((int)Configuration::get($state));
		if(!Validate::isLoadedObject($orderState)) {
			//Creating new order state
			$orderState->color = 'lightblue';
			$orderState->unremovable = 1;
			$orderState->name = array();
			foreach (Language::getLanguages() as $language)
			$orderState->name[$language['id_lang']] = $name;
			if(!$orderState->add())
			return false;
			
			copy(dirname(__FILE__).'/logo.gif', dirname(__FILE__).'/../../img/os/'.(int)$orderState->id.'.gif');
		}
	
		return ($this->configurations[$state] = $orderState->id);
	}
	
	
	//Carrie module methods
	public function getOrderShippingCost($params, $shipping_cost) {
		return (float)($this->getOrderShippingCostExternal($params) + $shipping_cost);
	}
	public function getOrderShippingCostExternal($cart) {
		$shopgateOrder = PSShopgateOrder::instanceByCartId($cart->id);

		return Validate::isLoadedObject($shopgateOrder) ? $shopgateOrder->shipping_cost : 0;
	}
	
	public function doMobileRedirect() {
		$indexFile = 'index.php';
		if(version_compare(_PS_VERSION_, '1.4.1.0', '>=') && Configuration::get('PS_HOMEPAGE_PHP_SELF') !== false){
			$indexFile = Configuration::get('PS_HOMEPAGE_PHP_SELF');
		}

		$shopgateConfig = new ShopgateConfigPresta();
		
		// instantiate and set up redirect class
		$shopgateBuilder = new ShopgateBuilder($shopgateConfig);
		$shopgateRedirector = $shopgateBuilder->buildRedirect();

		/* redirect logic */
		$controller = Tools::getValue('controller');
		if ($id_product = Tools::getValue('id_product', 0)){
			$productId = PSShopgatePlugin::prefix.$id_product.'_0';
			$shopgateJsHeader = $shopgateRedirector->buildScriptItem($productId);
		} elseif ($id_category = Tools::getValue('id_category', 0)){
			$shopgateJsHeader = $shopgateRedirector->buildScriptCategory($id_category);
		} elseif(isset($_SERVER['SCRIPT_FILENAME']) && mb_strpos($_SERVER['SCRIPT_FILENAME'], $indexFile) !== false && empty($controller)) {
			// TODO: doesn't work yet!
			$shopgateJsHeader = $shopgateRedirector->buildScriptShop();
		} else{
			$shopgateJsHeader = $shopgateRedirector->buildScriptDefault();
		}
		
		return $shopgateJsHeader;
	}
	
	public function hookHeader() {
		return $this->doMobileRedirect();
	}
	
	public function hookDisplayMobileHeader() {
		return $this->doMobileRedirect();
	}
	
	public function hookUpdateOrderStatus($params) {
		$id_order = $params['id_order'];
		$orderState = $params['newOrderStatus'];
		$shopgateOrder = PSShopgateOrder::instanceByOrderId($id_order);
		
		$shopgateConfig = new ShopgateConfigPresta();
		$shopgateBuilder = new ShopgateBuilder($shopgateConfig);
		$shopgateMerchantApi = $shopgateBuilder->buildMerchantApi();
		
		if(!Validate::isLoadedObject($shopgateOrder)){
			return;
		}
		
		try {
			switch($orderState->id) {
				case _PS_OS_DELIVERED_:
					$shopgateMerchantApi->setOrderShippingCompleted($shopgateOrder->order_number);
					break;
				case _PS_OS_SHIPPING_:
					$shopgateMerchantApi->addOrderDeliveryNote($shopgateOrder->order_number, $shopgateOrder->shipping_service, $shopgateOrder->tracking_number, true, false);
					break;
				default:
					break;
			}
		} catch(ShopgateMerchantApiException $e){
			$msg = new Message();
			$msg->message = $this->l('On order state').': '.$orderState->name.' - '.$this->l('Shopgate status was not updated because of following error').': '.$e->getMessage();
			$msg->id_order = $id_order;
			$msg->id_employee = isset($params['cookie']->id_employee) ? $params['cookie']->id_employee : 0;
			$msg->private = true;
			$msg->add();
		}
	}
	
	public function hookAdminOrder($params) {
		include_once dirname(__FILE__).'/backward_compatibility/backward.php';
		
		$id_order = $params['id_order'];
		
		$shopgateOrder = PSShopgateOrder::instanceByOrderId($id_order);
		
		if(Tools::isSubmit('updateShopgateOrder')) {
			$shopgate_order = Tools::getValue('shopgateOrder');
			$shippingService = $shopgate_order['shipping_service'];
			$trackingNumber = $shopgate_order['tracking_number'];
			
			if(isset($shippingService)){
				try {
					$shopgateConfig = new ShopgateConfigPresta();
					$shopgateBuilder = new ShopgateBuilder($shopgateConfig);
					$shopgateMerchantApi = $shopgateBuilder->buildMerchantApi();
					$shopgateMerchantApi->addOrderDeliveryNote($shopgateOrder->order_number, $shippingService, $trackingNumber, true, false);
					
				} catch(ShopgateMerchantApiException $e) {
					$error = $e->getMessage();
				}
				$shopgateOrder->shipping_service = $shippingService;
				$shopgateOrder->tracking_number = $trackingNumber;
				$shopgateOrder->update();
			}
		}
		
		if(!Validate::isLoadedObject($shopgateOrder)){
			return '';
		}
		
		$sOrder = null;
		$error = null;
		try {
			$shopgateConfig = new ShopgateConfigPresta();
			$shopgateBuilder = new ShopgateBuilder($shopgateConfig);
			$shopgateMerchantApi = $shopgateBuilder->buildMerchantApi();
			$orders = $shopgateMerchantApi->getOrders(array('order_numbers[0]'=>$shopgateOrder->order_number));
			foreach($orders->getData() as $o){
				/* @var $o ShopgateOrder */
				if($o->getOrderNumber() == $shopgateOrder->order_number){
					$sOrder = $o;
				}
			}
		} catch(ShopgateMerchantApiException $e) {
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
		foreach($sOrder->getDeliveryNotes() as $notes){
			$sOrderDeliveryNotes[] = array(
				'shipping_service_id' => $notes->getShippingServiceId(),
				'tracking_number' => $notes->getTrackingNumber(),
				'shipping_time' => $notes->getShippingTime(),
			);
		}
		
		// build comments
		$comments = array();
		foreach ($sOrder->jsonDecode($shopgateOrder->comments) as $text => $information) {
			$comments[] = sprintf($this->l($text), $information);
		}
		
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
		$this->context->smarty->assign('api_url', Tools::getHttpHost(true, true).$this->_path.'api.php');
		
		return $this->display(__FILE__, 'views/templates/admin/admin_order.tpl');
	}
	
	public function getContent() {
		include_once dirname(__FILE__).'/backward_compatibility/backward.php';
		
		$output = '';
		$shopgateConfig = new ShopgateConfigPresta();
		
		$bools = array('true'=>true, 'false'=>false);
	
		if(Tools::isSubmit('saveConfigurations')) {
			$configs = Tools::getValue('configs', array());
			foreach($configs as $name => $value){
				if(isset($bools[$value])){
					$configs[$name] = $bools[$value];
				}
				$configs[$name] = htmlentities($configs[$name]);
			}

			$configs['use_stock'] = !((bool)Configuration::get('PS_ORDER_OUT_OF_STOCK'));
	
			$settings = Tools::getValue('settings', array());
			foreach($settings as $key => $value){
				if(in_array($key, array('SHOPGATE_SHIPPING_SERVICE', 'SHOPGATE_MIN_QUANTITY_CHECK', 'SHOPGATE_MIN_QUANTITY_CHECK', 'SHOPGATE_OUT_OF_STOCK_CHECK', 'SHOPGATE_OUT_OF_STOCK_CHECK'))){
					Configuration::updateValue($key, htmlentities($value, ENT_QUOTES));
				}
			}
			$languageID = Configuration::get('PS_LANG_DEFAULT');
			if(Validate::isLanguageIsoCode($configs["language"])){
				$languageID = Language::getIdByIso($configs["language"]);
			}
			Configuration::updateValue('SHOPGATE_LANGUAGE_ID', $languageID);
			
			try {
				$shopgateConfig->loadArray($configs);
				$shopgateConfig->saveFile(array_keys($configs));
				$output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Configurations updated').'</div>';
			} catch (ShopgateLibraryException $e) {
				$output .= '<div class="conf error"><img src="../img/admin/error.png" alt="'.$this->l('Error').'" />'.$this->l('Error').': '.$e->getAdditionalInformation().'</div>';
			}
		}
		
		$langs = array();
		foreach(Language::getLanguages() as $id => $l) {
			$langs[strtoupper($l['iso_code'])] = $l['name'];
		}
	
		$servers = array(
			'live'=>$this->l('Live'),
			'pg'=>$this->l('Playground'),
			'custom'=>$this->l('Custom')
		);
	
		$enables = array();
	
		$settings = Configuration::getMultiple(array('SHOPGATE_SHIPPING_SERVICE', 'SHOPGATE_MIN_QUANTITY_CHECK', 'SHOPGATE_OUT_OF_STOCK_CHECK'));
		$shopgateConfig = new ShopgateConfigPresta();
		$configs = $shopgateConfig->toArray();

		$this->context->smarty->assign('settings', $settings);
		$this->context->smarty->assign('shipping_service_list', $this->shipping_service_list);
		$this->context->smarty->assign('langs', $langs);
		$this->context->smarty->assign('currencies', Currency::getCurrencies());
		$this->context->smarty->assign('servers', $servers);
		$this->context->smarty->assign('enables', $enables);
		$this->context->smarty->assign('configs', $configs);
		$this->context->smarty->assign('mod_dir', $this->_path);
		$this->context->smarty->assign('api_url', Tools::getHttpHost(true, true).$this->_path.'api.php');
        $this->context->smarty->assign('shopgate_offer_url', $this->_getOfferLink(Context::getContext()->language->language_code));
		
		return $output.$this->display(__FILE__, 'views/templates/admin/configurations.tpl');
	}

    /**
     * returns the current offer link by language code
     *
     * @param string $languageCode
     * @return string
     */
    protected function _getOfferLink($languageCode)
    {
        if(array_key_exists($languageCode, $this->_offer_mapping)) {
            $languageCode = $this->_offer_mapping[$languageCode];
        } else {
            $languageCode = $this->_offer_mapping['default'];
        }

        return sprintf(self::DEFAULT_OFFER_LINK_FORMAT, $languageCode);
    }
}
