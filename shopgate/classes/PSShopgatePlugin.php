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


class PSShopgatePlugin extends ShopgatePlugin {
	protected $id_lang = 1;
	protected $id_currency = 0;
	protected $currency_iso = false;

	const prefix = 'BD';

	public function startup(){
		include_once dirname(__FILE__).'/../backward_compatibility/backward.php';
		
		$this->id_lang = Configuration::get('SHOPGATE_LANGUAGE_ID');
		
		//Set configs that depends on prestashop settings
		require_once(dirname(__FILE__) . '/PSShopgateConfig.php');
		$this->config = new ShopgateConfigPresta();
		
		$this->shopgateModul = new ShopGate();
		
		$config = $this->config->toArray();

		$this->id_currency = $this->context->cookie->id_currency = Currency::getIdByIsoCode($config['currency']);
		$this->setCurrencyIso();

		$this->config->setUseStock( ! ((bool)Configuration::get('PS_ORDER_OUT_OF_STOCK')));
	}

	public function createPluginInfo() {
		return array(
			'PS Version' => _PS_VERSION_,
			'Plugin' => 'standard'
		);
	}
	
	protected function setCurrencyIso() {
		$this->currency_iso = Tools::strtoupper(Db::getInstance()->getValue('
			SELECT
				`iso_code`
			FROM
				`' . _DB_PREFIX_ . 'currency`
			WHERE
				`id_currency` = ' . (int)$this->id_currency)
		);
	}

	public function getCustomer($user, $pass){

			$id_customer = (int)Db::getInstance()->getValue('
				SELECT `id_customer`
				FROM `'._DB_PREFIX_.'customer`
				WHERE
				`active` AND
				`email` = \''.pSQL($user).'\' AND
				`passwd` = \''.Tools::encrypt($pass).'\' AND
				`deleted` = 0
				'.(version_compare(_PS_VERSION_, '1.4.1.0', '>=') ? ' AND `is_guest` = 0' : ''));

		if(!$id_customer)
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_WRONG_USERNAME_OR_PASSWORD, 'Username or password is incorrect');

		$customer = new Customer($id_customer);

		$gender = array(
			1 => 'm',
			2 => 'f',
			9 => null
		);

		$shopgateCustomer = new ShopgateCustomer();

		$shopgateCustomer->setCustomerId($customer->id);
		$shopgateCustomer->setCustomerNumber($customer->id);
		$shopgateCustomer->setCustomerGroup(Db::getInstance()->getValue('SELECT `name` FROM `'._DB_PREFIX_.'group_lang` WHERE `id_group`=\''.$customer->id_default_group.'\' AND `id_lang`='.$this->id_lang));
		$shopgateCustomer->setCustomerGroupId($customer->id_default_group);
		$shopgateCustomer->setFirstName($customer->firstname);
		$shopgateCustomer->setLastName($customer->lastname);
		$shopgateCustomer->setGender(isset($gender[$customer->id_gender]) ? $gender[$customer->id_gender] : null);
		$shopgateCustomer->setBirthday($customer->birthday);
		$shopgateCustomer->setMail($customer->email);
		$shopgateCustomer->setNewsletterSubscription($customer->newsletter);

		$addresses = array();
		foreach($customer->getAddresses($this->id_lang) as $a){
			$address = new ShopgateAddress();
			 
			$address->setId($a['id_address']);
			$address->setFirstName($a['firstname']);
			$address->setLastName($a['lastname']);
			$address->setCompany($a['company']);
			$address->setStreet1($a['address1']);
			$address->setStreet2($a['address2']);
			$address->setCity($a['city']);
			$address->setZipcode($a['postcode']) ;
			$address->setCountry($a['country']);
			$address->setState($a['state']);
			$address->setPhone($a['phone']);
			$address->setMobile($a['phone_mobile']);

			array_push($addresses, $address);
		}

		$shopgateCustomer->setAddresses($addresses);

		return $shopgateCustomer;
	}
	
	
	protected function getPSAddress(Customer $customer, ShopgateAddress $shopgateAddress){
		// Get country
		$id_country = Country::getByIso($shopgateAddress->getCountry());
		if (!$id_country) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_UNKNOWN_COUNTRY_CODE, 'Invalid country code:'.$id_country, true);
		}
		
		// Get state
		$id_state = 0;
		if ($shopgateAddress->getState()) {
			$id_state = (int)Db::getInstance()->getValue('SELECT `id_state` FROM `'._DB_PREFIX_.'state` WHERE `id_country` = '.$id_country.' AND `iso_code` = \''.pSQL(Tools::substr($shopgateAddress->getState(), 3, 2)).'\'');
		}
		
		// Create alias
		$alias = Tools::substr('Shopgate_'.$customer->id.'_'.sha1($customer->id.'-'.$shopgateAddress->getFirstName().
			'-'.$shopgateAddress->getLastName().'-'.$shopgateAddress->getCompany().
			'-'.$shopgateAddress->getStreet1().'-'.$shopgateAddress->getStreet2().
			'-'.$shopgateAddress->getZipcode().'-'. $shopgateAddress->getCity())
		, 0, 32);
		
		// Try getting address id by alias
		$id_address = Db::getInstance()->getValue('SELECT `id_address` FROM `'._DB_PREFIX_.'address` WHERE `alias` = \''.pSQL($alias).'\' AND `id_customer`='.$customer->id);
		
		// Get or create address
		$address = new Address($id_address ? $id_address : null);
		if(!$address->id){
			$address->id_customer = $customer->id;
			$address->id_country = $id_country;
			$address->id_state = $id_state;
			$address->country = Country::getNameById($this->id_lang, $address->id_country);
			$address->alias = $alias;
			$address->company = $shopgateAddress->getCompany();
			$address->lastname = $shopgateAddress->getLastName();
			$address->firstname = $shopgateAddress->getFirstName();
			$address->address1 = $shopgateAddress->getStreet1();
			$address->address2 = $shopgateAddress->getStreet2();
			$address->postcode = $shopgateAddress->getZipcode();
			$address->city = $shopgateAddress->getCity();
			$address->phone = $shopgateAddress->getPhone();
			$address->phone_mobile = $shopgateAddress->getMobile();
			if(!$address->add())
				throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_DATABASE_ERROR, 'Unable to create address', true);
		}

		return $address;
	}

	protected function getProductIdentifiers(ShopgateOrderItem $item){
		return explode('_', Tools::substr($item->getItemNumber(), Tools::strlen(self::prefix)));
	}

	protected function getOrderStateId($order_state_var) {
		return (int)(defined($order_state_var) ? constant($order_state_var) : (defined('_'.$order_state_var.'_') ? constant('_'.$order_state_var.'_') : Configuration::get($order_state_var)));
	}

	/**
	 * Fix for Prestashop version < 1.4.0.2
	 *
	 * @param float $shippingCosts
	 */
	protected function setShippingCosts($shippingCosts){
		$deliveryZones = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'delivery` WHERE `id_carrier` = '. (int)Configuration::get('SHOPGATE_CARRIER_ID'));
		
		// create for each zone delivery options
		foreach($deliveryZones as $deliveryZone){
			$deliveryRange = new Delivery($deliveryZone['id_delivery']);
			
			// PS_SHIPPING_HANDLING is a fix to decrease the shipping for the amount that is setted up in the shipping configuration
			$deliveryRange->price = $shippingCosts;

			if(!$deliveryRange->update()){
				return false;
			}
		}
		
		return true;
	}
	
	public function addOrder(ShopgateOrder $order){
		$this->log("PS start add_order", ShopgateLogger::LOGTYPE_DEBUG);
		
		$shopgateOrder = PSShopgateOrder::instanceByOrderNumber($order->getOrderNumber());
		if ($shopgateOrder->id) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_DUPLICATE_ORDER, 'external_order_id: '.$shopgateOrder->id_order, true);
		}
		
		$comments = array();
		
		// generate products array
		$products = $this->insertOrderItems($order);

		//Get or create customer
		$id_customer = Customer::customerExists($order->getMail(), true, false);
		$customer = new Customer($id_customer ? $id_customer : (int)$order->getExternalCustomerId());
		if(!$customer->id) {
			$customer = $this->createCustomer($customer, $order);
		}
		
		// prepare addresses: company has to be shorten. add mobile phone / telephone
		$this->prepareAddresses($order);
		
		//Get invoice and delivery addresses
		$invoiceAddress = $this->getPSAddress($customer, $order->getInvoiceAddress());
		$deliveryAddress = ($order->getInvoiceAddress() == $order->getDeliveryAddress()) ? $invoiceAddress : $this->getPSAddress($customer, $order->getDeliveryAddress());

		//Creating currency
		$this->log("PS setting currency", ShopgateLogger::LOGTYPE_DEBUG);
		$id_currency = $order->getCurrency() ? Currency::getIdByIsoCode($order->getCurrency()) : $this->id_currency;
		$currency = new Currency($id_currency ? $id_currency : $this->id_currency);

		//Creating new cart
		$this->log("PS set cart variables", ShopgateLogger::LOGTYPE_DEBUG);
		$cart = new Cart();
		$cart->id_lang = $this->id_lang;
		$cart->id_currency = $currency->id;
		$cart->id_address_delivery = $deliveryAddress->id;
		$cart->id_address_invoice = $invoiceAddress->id;
		$cart->id_customer = $customer->id;
		
		if(version_compare(_PS_VERSION_, '1.4.1.0', '>=')) {
			// id_guest is a connection to a ps_guest entry which includes screen width etc.
			// is_guest field only exists in Prestashop 1.4.1.0 and higher
			$cart->id_guest = $customer->is_guest;
		}
		
		$cart->recyclable = 0;
		$cart->gift = 0;
		$cart->id_carrier = (int)Configuration::get('SHOPGATE_CARRIER_ID');
		$cart->secure_key = $customer->secure_key;
		
		$this->log("PS try to create cart", ShopgateLogger::LOGTYPE_DEBUG);
		if(!$cart->add()){
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_DATABASE_ERROR, 'Unable to create cart', true);
		}
		
		//Adding items to cart
		$this->log("PS adding items to cart", ShopgateLogger::LOGTYPE_DEBUG);
		foreach($products as $p){
			$this->log("PS cart updateQty product id: ".  $p['id_product'], ShopgateLogger::LOGTYPE_DEBUG);
			$this->log("PS cart updateQty product quantity: ".  $p['quantity'], ShopgateLogger::LOGTYPE_DEBUG);
			$this->log("PS cart updateQty product quantity_difference: ".  $p['quantity_difference'], ShopgateLogger::LOGTYPE_DEBUG);
			$this->log("PS cart updateQty product id_product_attribute: ".  $p['id_product_attribute'], ShopgateLogger::LOGTYPE_DEBUG);
			$this->log("PS cart updateQty product delivery address: ".  $deliveryAddress->id, ShopgateLogger::LOGTYPE_DEBUG);
			
			//TODO deal with customizations
			$id_customization = false;
			
			if($p['quantity'] - $p['quantity_difference'] > 0){
				// only if the result of $p['quantity'] - $p['quantity_difference'] is higher then 0
				$cart->updateQty($p['quantity'] - $p['quantity_difference'], $p['id_product'], $p['id_product_attribute'], $id_customization, 'up', $deliveryAddress->id);
			}
			
			if ($p['quantity_difference'] > 0){
				$this->log("PS try to add cart message ", ShopgateLogger::LOGTYPE_DEBUG);
				$message = new Message();
				$message->id_cart = $cart->id;
				$message->private = 1;
				$message->message = 'Warning, wanted quantity for product "' . $p['name'] . '" was ' . $p['quantity'] . ' unit(s), however, the amount in stock is ' . $p['quantity_in_stock'] . ' unit(s). Only ' . $p['quantity_in_stock'] . ' unit(s) were added to the order';
				
				$message->save();
			}
		}

		$id_order_state = 0;

		$shopgate = new Shopgate();
		$payment_name = $shopgate->getTranslation('Mobile Payment');
		
		$this->log("PS map payment method", ShopgateLogger::LOGTYPE_DEBUG);
		if(!$order->getIsShippingBlocked()){
			$id_order_state = $this->getOrderStateId('PS_OS_PREPARATION');
			switch($order->getPaymentMethod()){
				case 'SHOPGATE': 	$payment_name = $shopgate->getTranslation('Shopgate'); break;
				case 'PREPAY':
					$payment_name = $shopgate->getTranslation('Bankwire');
					$id_order_state = $this->getOrderStateId('PS_OS_BANKWIRE');
					break;
				case 'COD': 		$payment_name = $shopgate->getTranslation('Cash on Delivery'); break;
				case 'PAYPAL': 		$payment_name = $shopgate->getTranslation('PayPal'); break;
				default: break;
			}
		} else {
			$id_order_state = $this->getOrderStateId('PS_OS_SHOPGATE');
			
			switch($order->getPaymentMethod()){
				case 'SHOPGATE':
					$payment_name = $shopgate->getTranslation('Shopgate');
					break;
				case 'PREPAY':
					$payment_name = $shopgate->getTranslation('Bankwire');
					break;
				case 'COD':
					$payment_name = $shopgate->getTranslation('Cash on Delivery');
					break;
				case 'PAYPAL':
					$id_order_state = $this->getOrderStateId('PS_OS_PAYPAL');
					$payment_name = $shopgate->getTranslation('PayPal');
					break;
				default:
					$id_order_state = $this->getOrderStateId('PS_OS_SHOPGATE');
				break;
			}
		}
		
		$shippingCosts = $order->getAmountShipping() + $order->getAmountShopPayment();
		
		//Creates shopgate order record and save shipping cost for future use
		$this->log("PS set PSShopgateOrder object variables", ShopgateLogger::LOGTYPE_DEBUG);
		$shopgateOrder = new PSShopgateOrder();
		$shopgateOrder->order_number = $order->getOrderNumber();
		$shopgateOrder->shipping_cost = $shippingCosts;
		$shopgateOrder->shipping_service = Configuration::get('SHOPGATE_SHIPPING_SERVICE');
		$shopgateOrder->id_cart = $cart->id;
		$shopgateOrder->shop_number = $this->config->getShopNumber();
		$shopgateOrder->comments = $this->jsonEncode($comments);
		
		if(version_compare(_PS_VERSION_, '1.4.0.2', '<')) {
			$this->log("PS lower 1.4.0.2: ", ShopgateLogger::LOGTYPE_DEBUG);
			// Fix: sets in database ps_delivery all zones of passed shippingCosts
			$this->setShippingCosts(0);
		}
		
		$this->log("PS try creating PSShopgateOrder object", ShopgateLogger::LOGTYPE_DEBUG);
		if(!$shopgateOrder->add()) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_DATABASE_ERROR, 'Unable to create shopgate order', true);
		}
		
		//PS 1.5 compatibility
		if(version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
			$this->log("PS 1.5.x.x: set cart context", ShopgateLogger::LOGTYPE_DEBUG);
			$this->context = Context::getContext();
			$this->context->cart = $cart;
			
			$this->log("PS 1.5.x.x: \$cart->setDeliveryOption(array(\$cart->id_address_delivery => \$cart->id_carrier.','))\n\n==============", ShopgateLogger::LOGTYPE_DEBUG);
			$cart->setDeliveryOption(array($cart->id_address_delivery => $cart->id_carrier.','));
			
			$this->log("PS 1.5.x.x: \$cart->update()", ShopgateLogger::LOGTYPE_DEBUG);
			$cart->update();
			$cart->id_carrier = (int)Configuration::get('SHOPGATE_CARRIER_ID');
		}

		$amountPaid = $order->getAmountComplete();
		if(version_compare(_PS_VERSION_, '1.4.0.2', '<')){
			// substract the shipping costs.
			$amountPaid -= $shippingCosts;
		}
		
		$this->log("\$shopgate->validateOrder(\$cart->id, \$id_order_state, \$amountPaid, \$payment_name, NULL, array(), NULL, false, \$cart->secure_key", ShopgateLogger::LOGTYPE_DEBUG);
		$this->log(
				"\$cart->id = ".var_export($cart->id, true).
				"\n\$id_order_state = ".var_export($id_order_state, true).
				"\n\$amountPaid = ".var_export($amountPaid, true).
				"\n\$payment_name = ".var_export($payment_name, true).
				"\n\$cart->secure_key".var_export($cart->secure_key, true)."\n==============",
		ShopgateLogger::LOGTYPE_DEBUG);
		
		try {
			
			$shopgate->validateOrder(
				$cart->id,
				$id_order_state,
				$amountPaid,
				$payment_name,
				NULL,
				array(),
				NULL,
				false,
				$cart->secure_key
			);
		
		} catch (Swift_Message_MimeException $ex){
			$this->log("\$shopgate->validateOrder(\$cart->id, \$id_order_state, \$amountPaid, \$payment_name, NULL, array(), NULL, false, \$cart->secure_key) FAILED with Swift_Message_MimeException", ShopgateLogger::LOGTYPE_ERROR);
			// catch Exception if there is a problem with sending mails
		}
		
		if(version_compare(_PS_VERSION_, '1.4.0.2', '<') && (int)$shopgate->currentOrder > 0){
			$this->log("PS < 1.4.0.2: update shipping and payment cost", ShopgateLogger::LOGTYPE_DEBUG);
			
			// in versions below 1.4.0.2 the shipping and payment costs must be updated after the order
			$updateShopgateOrder = new Order($shopgate->currentOrder);
			
			$updateShopgateOrder->total_paid = $order->getAmountComplete();
			$updateShopgateOrder->total_paid_real = $order->getAmountComplete();
			$updateShopgateOrder->total_products_wt = $order->getAmountItems();
			$updateShopgateOrder->total_shipping = $order->getAmountShipping() + $order->getAmountShopPayment();
			$updateShopgateOrder->update();
		}
		
		if ((int)$shopgate->currentOrder > 0)
		{
			$this->log("\$shopgateOrder->update()", ShopgateLogger::LOGTYPE_DEBUG);
			$shopgateOrder->id_order = $shopgate->currentOrder;
			$shopgateOrder->update();
			
			return array(
				'external_order_id' => $shopgate->currentOrder,
				'external_order_number' => $shopgate->currentOrder
			);
		}
		else
		{
			$this->log("\$shopgateOrder->delete()", ShopgateLogger::LOGTYPE_DEBUG);
			$shopgateOrder->delete();
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_DATABASE_ERROR, 'Unable to create order', true);
		}
	}
	
	protected function prepareAddresses(ShopgateOrder $order){
		$this->log("PS start prepareAddresses", ShopgateLogger::LOGTYPE_DEBUG);
		
		// shorten company names in addresses if necessary and add a comment
		if (Tools::strlen($order->getInvoiceAddress()->getCompany()) > 32) {
			$comments['Invoice address\' company name <b>%s</b> has been shortened.'] = $order->getInvoiceAddress()->getCompany();
			$order->getInvoiceAddress()->setCompany(Tools::substr($order->getInvoiceAddress()->getCompany(), 0, 26).'[...]');
		}
		if (Tools::strlen($order->getDeliveryAddress()->getCompany()) > 32) {
			$comments['Delivery address\' company name <b>%s</b> has been shortened.'] = $order->getDeliveryAddress()->getCompany();
			$order->getDeliveryAddress()->setCompany(Tools::substr($order->getDeliveryAddress()->getCompany(), 0, 26).'[...]');
		}
		
		// set the customer's telephone and mobile number for addresses if necessary
		$phone = $order->getInvoiceAddress()->getPhone();
		if (empty($phone)) {
			$order->getInvoiceAddress()->setPhone($order->getPhone());
		}
		$phone = $order->getDeliveryAddress()->getPhone();
		if (empty($phone)) {
			$order->getDeliveryAddress()->setPhone($order->getPhone());
		}
		
		$mobile = $order->getInvoiceAddress()->getMobile();
		if (empty($mobile)) {
			$order->getInvoiceAddress()->setMobile($order->getMobile());
		}
		$mobile = $order->getDeliveryAddress()->getMobile();
		if (empty($mobile)) {
			$order->getDeliveryAddress()->setMobile($order->getMobile());
		}
		$this->log("PS end prepareAddresses", ShopgateLogger::LOGTYPE_DEBUG);
	}
	
	protected function createCustomer($customer, ShopgateOrder $order){
		$this->log('start createCustomer()', ShopgateLogger::LOGTYPE_DEBUG);
		
		$birthday = $order->getInvoiceAddress()->getBirthday();
		$customer->lastname = $order->getInvoiceAddress()->getLastName();
		$customer->firstname = $order->getInvoiceAddress()->getFirstName();
		$customer->id_gender = ($order->getInvoiceAddress()->getGender() == 'm' ? 1 : ($order->getInvoiceAddress()->getGender() == 'f' ? 2 : 9));
		$customer->birthday = empty($birthday) ? '0000-00-00' : $birthday;
		$customer->email = $order->getMail();
		$customer->passwd = md5(_COOKIE_KEY_.time());
		if(version_compare(_PS_VERSION_, '1.4.1.0', '>=')) {
			// guest accounts flag only exists in Prestashop 1.4.1.0 and higher
			$customer->is_guest = (int)Configuration::get('PS_GUEST_CHECKOUT_ENABLED');
		}
			
		if(!$customer->add()){
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_DATABASE_ERROR, 'Unable to create customer', true);
		}
		$this->log('end createCustomer()', ShopgateLogger::LOGTYPE_DEBUG);
		
		return $customer;
	}
	
	protected function insertOrderItems(ShopgateOrder $order){
		$this->log('start insertOrderItems()', ShopgateLogger::LOGTYPE_DEBUG);
		
		$products = array();
		
		//Check product quantitys
		$settings = Configuration::getMultiple(array('SHOPGATE_MIN_QUANTITY_CHECK', 'SHOPGATE_OUT_OF_STOCK_CHECK'));
		
		// complete weight of the order
		foreach($order->getItems() as $i){
			list($id_product, $id_product_attribute) = $this->getProductIdentifiers($i);
				
			if ($id_product == 0)
			{
				continue;
			}
		
			$wantedQty = (int)$i->getQuantity();
			$product = new Product($id_product, true, (int)Configuration::get('PS_LANG_DEFAULT'));
			
			$minQty = 1;
			if((int)$id_product_attribute){
				$stockQty = (int)Product::getQuantity((int)$id_product, (int)$id_product_attribute);
				if(version_compare(_PS_VERSION_, '1.4.0.7', '>=')){
					// this attribute doesn't exist before 1.4.0.7
					$minQty = Attribute::getAttributeMinimalQty((int)$id_product_attribute);
				}
			} else {
				$stockQty = (int)Product::getQuantity((int)$id_product, NULL);
				if(version_compare(_PS_VERSION_, '1.4.0.2', '>=')){
					// this attribute doesn't exist before 1.4.0.2
					$minQty = (int)$product->minimal_quantity;
				}
			}
				
			$oos_available = Product::isAvailableWhenOutOfStock($product->out_of_stock);
				
			$qtyDifference = 0;
				
			if ( ! $oos_available && $wantedQty > $stockQty)
			{
				$qtyDifference = $wantedQty - $stockQty;
			}
		
			$p = array();
			$p['id_product'] = (int)$id_product;
			$p['id_product_attribute'] = (int)$id_product_attribute;
			$p['name'] = $product->name;
			$p['quantity'] = $wantedQty;
			$p['quantity_in_stock'] = $stockQty;
			$p['quantity_difference'] = $qtyDifference;
				
			if(empty($p['name'])){
				$p['name'] = $i->getName();
			}
		
			if ($oos_available)
			{
				$stockQty = $wantedQty;
			}
		
			if((bool)$settings['SHOPGATE_MIN_QUANTITY_CHECK'] && $wantedQty < $minQty)
				throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_DATABASE_ERROR, 'Minimum quantity required', true);
		
			if((bool)$settings['SHOPGATE_OUT_OF_STOCK_CHECK'] && $wantedQty > $stockQty)
				throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_DATABASE_ERROR, 'Out of stock', true);
		
			array_push($products, $p);
		}
		
		$this->log('end insertOrderItems()', ShopgateLogger::LOGTYPE_DEBUG);
		
		return $products;
	}

	public function cron($jobname, $params, &$message, &$errorcount){
		return;
	}
	
	public function updateOrder(ShopgateOrder $order){
		$shopgateOrder = PSShopgateOrder::instanceByOrderNumber($order->getOrderNumber());

		if(!Validate::isLoadedObject($shopgateOrder))
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_ORDER_NOT_FOUND, 'Order not found', true);
		
		$order_states = array();

		if($order->getUpdatePayment() && $order->getIsPaid())
			array_push($order_states, $this->getOrderStateId('PS_OS_PAYMENT'));

		if($order->getUpdateShipping() && !$order->getIsShippingBlocked())
			array_push($order_states, $this->getOrderStateId('PS_OS_PREPARATION'));

		if(count($order_states)){
			$ps_order = new Order($shopgateOrder->id_order);
			foreach($order_states as $id_order_state) {
				if(version_compare(_PS_VERSION_, '1.4.1.0', '<')){
					$history = new OrderHistory();
					$history->id_order = (int) ($shopgateOrder->id_order);
					$history->changeIdOrderState((int) $id_order_state, (int) ($shopgateOrder->id_order));
				} else {
					$ps_order->setCurrentState($id_order_state);
				}
			}
		}
		return array(
			'external_order_id' => $shopgateOrder->id_order,
			'external_order_number' => $shopgateOrder->id_order
		);
	}
	
	protected static function convertProductWeightToGrams($weight)
	{
		$ps_weight_unit = Tools::strtolower(Configuration::get('PS_WEIGHT_UNIT'));
		
		$multipliers = array(
			'kg' => 1000,
			'lbs' => 453.59237
		);
		
		if (array_key_exists($ps_weight_unit, $multipliers))
		{
			$weight*= $multipliers[$ps_weight_unit];
		}
		
		return $weight;
	}
	
	
	public static function getHighlightProducts(){
		static $prepared = null, $rootCategoryId = null;
		
		if(is_null($prepared)){
			$prepared = array();
			
			if(empty($rootCategoryId)){
				if(version_compare(_PS_VERSION_, '1.5.0.0', '<')){
					// lower than 1.5.0.0
					$rootCategoryId = 1;
				} else {
					$rootCategoryId = Db::getInstance()->getValue('SELECT `id_category` FROM `' . _DB_PREFIX_ . 'category` WHERE  `is_root_category` = 1');
				}
			}

			$result = Db::getInstance()->ExecuteS('SELECT `id_product` FROM `' . _DB_PREFIX_ . 'category_product` WHERE `id_category` = '.(int)$rootCategoryId);
			
			if ($result && sizeof($result))
			{
				foreach ($result as $product)
				{
					array_push($prepared, (int)$product['id_product']);
				}
			}
			
			$prepared = array_unique($prepared);
		}
		
		return $prepared;
	}
	
	protected static function roundPricesInArray(&$array, $keys_to_round) {
		if ( ! is_array($keys_to_round) || ! sizeof($keys_to_round)) {
			return false;
		}
		
		foreach ($keys_to_round as $round_key) {
			if (array_key_exists($round_key, $array)) {
				$array[$round_key] = Tools::ps_round($array[$round_key], 2);
			}
		}
	}

	protected function getCategoryMaxSortOrder(){
		static $maxSortOrderByCategoryNumber = null;
		
		if(is_null($maxSortOrderByCategoryNumber)){
			$maxSortOrderCategories = Db::getInstance()->ExecuteS('
				SELECT id_category, MAX(position) as max_position
				FROM `'._DB_PREFIX_.'category_product`
				GROUP BY `id_category`'
			);
			
			$maxSortOrderByCategoryNumber = array();
			foreach($maxSortOrderCategories as $sortOrderCategory){
				$maxSortOrderByCategoryNumber[$sortOrderCategory['id_category']] = $sortOrderCategory['max_position'];
			}
		}
		
		return $maxSortOrderByCategoryNumber;
	}

	protected function createItemsCsv(){
		
		$limit = Tools::getValue('limit', 0);
		$offset = Tools::getValue('offset', 0);
		
		$products = Db::getInstance((defined('_PS_USE_SQL_SLAVE_') ? _PS_USE_SQL_SLAVE_ : null))->ExecuteS
		('
			SELECT p.*,
				trg.id_tax_rules_group AS tax_class_id,
				trg.name AS tax_class_name,
				trg.active AS tax_class_active
			FROM `'._DB_PREFIX_.'product` p
			JOIN `'._DB_PREFIX_.'tax_rules_group` trg ON (p.id_tax_rules_group = trg.id_tax_rules_group)
			WHERE '. (version_compare(_PS_VERSION_, '1.4.0.2', '>=') ? '`available_for_order` = 1 AND' : '').' p.`active` = 1
			ORDER BY p.`id_product` DESC'.
			($limit ? ' LIMIT '.$offset.', '.$limit : '')
		);
		
		$additionalLoaders = array(
			'itemExportOptions',
			'itemExportAttributes',
			'itemExportInputFields',
		);
		
		$loaders = array_merge($this->getCreateItemsCsvLoaders(), $additionalLoaders);
		
		foreach($products as $p){
			$product = new Product($p['id_product'], true, $this->id_lang);
			
			$product->tax_class_id = $p['tax_class_id'];
			$product->tax_class_name = $p['tax_class_name'];
			$product->tax_class_active = $p['tax_class_active'];
			
			$row = $this->buildDefaultItemRow();
			$row = $this->executeLoaders( $loaders, $row, $product);
			
			$this->addItem($row);
		}
	}

	
	protected function itemExportItemNumber($row, $product) {
		$row['item_number'] = self::prefix.$product->id.'_0';
		return $row;
	}
	
	protected function itemExportItemName($row, $product) {
		$row['item_name'] = $product->name;
		return $row;
	}
	
	protected function itemExportUnitAmount($row, $product) {
		$row['unit_amount'] = Tools::ps_round($product->getPrice(true, NULL, 2), 2);
		return $row;
	}
	
	protected function itemExportCurrency($row, $product) {
		$row['currency'] = $this->currency_iso;
		return $row;
	}
	
	protected function itemExportTaxPercent($row, $product) {
		$row['tax_percent'] = $this->formatPriceNumber($product->tax_rate);
		return $row;
	}
	
	protected function itemExportDescription($row, $product) {
		$row['description'] = str_replace(array("\r", "\n"), '', $product->description);
		return $row;
	}
	
	protected function itemExportUrlsImages($row, $product) {
		$image_urls = array();
			
		if(version_compare(_PS_VERSION_, '1.4.1.0', '>=')){
			$image_ids = $product->getWsImages();
		} else {
			$image_ids = $product->getImages($this->id_lang);
			foreach($image_ids as &$i){
				$i['id'] = $i['id_image'];
			}
		}
			
		foreach($image_ids as $i) {
			if(version_compare(_PS_VERSION_, '1.5.0.0', '>=')){
				array_push($image_urls, $this->context->link->getImageLink($product->link_rewrite, $product->id.'-'.$i['id'], 'thickbox_default'));
			}elseif(version_compare(_PS_VERSION_, '1.5.0.0', '<') && version_compare(_PS_VERSION_, '1.4.0.0', '>=')){
				// lower than 1.5.0.0 higher than 1.4.0.0
				array_push($image_urls, $this->context->link->getImageLink($product->link_rewrite, $product->id.'-'.$i['id'], 'thickbox'));
			} else {
				// lower then 1.4.0.0
				array_push($image_urls, _PS_BASE_URL_.$this->context->link->getImageLink($product->link_rewrite, $product->id.'-'.$i['id'], 'thickbox'));
			}
		}
		$row['urls_images'] =	(string)implode('||', $image_urls);
		return $row;
	}
	
	protected function itemExportCategories($row, $product) {
		// Url in 1.5.2.0 wrong (thickbox_default)
		//Categories
		$category = new Category($product->id_category_default);
		$row['categories'] = $category->getName($this->id_lang);
		return $row;
	}
	
	protected function itemExportCategoryNumbers($row, $product) {
		$maxSortOrderByCategoryNumber = $this->getCategoryMaxSortOrder();
		
		$rootCategoryIds = array();
		if(version_compare(_PS_VERSION_, '1.5.0.0', '>=')){
			// equal greater than 1.5.0.0
			$rootCategoryIds = array_keys($this->getRootCategoriesByCategoryId());
		}
		
		if(version_compare(_PS_VERSION_, '1.5.0.0', '<')){
			// lower than 1.5.0.0
			$sortOrderCategories = Db::getInstance()->ExecuteS('SELECT `id_category`, `position` FROM `'._DB_PREFIX_.'category_product` WHERE `id_product` = '.$product->id.' AND `id_category` != 1');
		} else {
			$sortOrderCategories = Db::getInstance()->ExecuteS('SELECT `id_category`, `position` FROM `'._DB_PREFIX_.'category_product` WHERE `id_product` = '.$product->id.' AND `id_category` != 1 AND `id_category` NOT IN ('.implode(',',$rootCategoryIds).')');
		}
			
		$row['category_numbers'] = '';
		foreach($sortOrderCategories as $sortOrderCategory){
			if(!empty($row['category_numbers'])){
				$row['category_numbers'] .= '||';
			}
			$row['category_numbers'] .= $sortOrderCategory['id_category'].'=>'.(($maxSortOrderByCategoryNumber[$sortOrderCategory['id_category']] - $sortOrderCategory['position']) + 1);
		}
		return $row;
	}
	
	protected function itemExportIsAvailable($row, $product) {
		return $row;
	}
	
	protected function itemExportAvailableText($row, $product) {
		$availableText = '';
		if((version_compare(_PS_VERSION_, '1.4.0.2', '>=') && $product->available_for_order
		|| version_compare(_PS_VERSION_, '1.4.0.2', '<')) && $product->quantity > 0){
			$availableText = $product->available_now;
			$row['is_available'] = 1;
		} else {
			$availableText = $product->available_later;
			$row['is_available'] = 0;
		}
		$row['available_text'] = $availableText;
		return $row;
	}
	
	protected function itemExportManufacturer($row, $product) {
		$row['manufacturer'] = $product->manufacturer_name;
		return $row;
	}
	
	protected function itemExportManufacturerItemNumber($row, $product) {
		$row['manufacturer_item_number'] = $product->id_manufacturer;
		return $row;
	}
	
	protected function itemExportUrlDeeplink($row, $product) {
		$row['url_deeplink'] = $this->context->link->getProductLink($product->id, $product->link_rewrite, $product->category, $product->ean13, $this->id_lang);
		return $row;
	}
	
	protected function itemExportItemNumberPublic($row, $product) {
		$row['item_number_public'] = !empty($product->reference) ? $product->reference : '';
		return $row;
	}
	
	protected function itemExportOldUnitAmount($row, $product) {
		$reduction = (float)$product->getPrice(true, NULL, 2, NULL, true);
		$row['old_unit_amount'] = Tools::ps_round($reduction != 0 ? $product->getPrice(true, NULL, 2, NULL, false, false) : 0, 2);
		return $row;
	}
	
	protected function itemExportProperties($row, $product) {
		//Features
		$features = $product->getFrontFeatures($this->id_lang);
		$properties = array();
		foreach($features as $f){
			array_push($properties, $f['name'].'=>'.$f['value']);
		}
		$row['properties'] = implode('||', $properties);
		return $row;
	}
	
	protected function itemExportMsrp($row, $product) {
		return $row;
	}
	
	protected function itemExportShippingCostsPerOrder($row, $product) {
		return $row;
	}
	
	protected function itemExportAdditionalShippingCostsPerUnit($row, $product) {
		// TODO: maybe tax must to be added
		$row['additional_shipping_costs_per_unit'] = Tools::ps_round($product->additional_shipping_cost, 2); // TODO
		return $row;
	}
	
	protected function itemExportIsFreeShipping($row, $product) {
		return $row;
	}
	
	protected function itemExportBasicPrice($row, $product) {
		if(version_compare(_PS_VERSION_, '1.4.1.0', '>=') && !empty($product->unity) && $product->unit_price_ratio > 0.000000){
			$row['basic_price'] = Tools::displayPrice($product->getPrice(true, NULL, 2)/$product->unit_price_ratio).' '.$this->shopgateModul->l('per').' '.$product->unity;
		}
		return $row;
	}
	
	protected function itemExportUseStock($row, $product) {
		if(version_compare(_PS_VERSION_, '1.5.0.0', '>=')){
			$return = (int)$product->out_of_stock == 2 ? (int)!(bool)Configuration::get('PS_ORDER_OUT_OF_STOCK'): (int)!(bool)$product->out_of_stock;
			$row['use_stock'] = (int)!Configuration::get('PS_STOCK_MANAGEMENT') ? 0 : $return;
		} else {
			if($product->out_of_stock == 2){
				$row['use_stock'] =	(int)!(bool)Configuration::get('PS_ORDER_OUT_OF_STOCK');
			} else {
				$row['use_stock'] = (int)!(bool)$product->out_of_stock;
			}
		}
		return $row;
	}
	
	protected function itemExportStockQuantity($row, $product) {
		$row['stock_quantity'] = $product->quantity;
		return $row;
	}
	
	protected function itemExportActiveStatus($row, $product) {
		return $row;
	}
	
	protected function itemExportMinimumOrderQuantity($row, $product) {
		if(version_compare(_PS_VERSION_, '1.4.0.2', '>=')){
			$row['minimum_order_quantity'] = $product->minimal_quantity;
		}
		return $row;
	}
	
	protected function itemExportMaximumOrderQuantity($row, $product) {
		return $row;
	}
	
	protected function itemExportMinimumOrderAmount($row, $product) {
		return $row;
	}
	
	protected function itemExportEan($row, $product) {
		$row['ean'] = $product->ean13;
		return $row;
	}
	
	protected function itemExportIsbn($row, $product) {
		return $row;
	}
	
	protected function itemExportPzn($row, $product) {
		return $row;
	}
	
	protected function itemExportUpc($row, $product) {
		return $row;
	}
	
	protected function itemExportLastUpdate($row, $product) {
		$row['last_update'] = Tools::substr($product->date_upd, 0, 10);
		return $row;
	}
	
	protected function itemExportTags($row, $product) {
		$row['tags'] = implode(',', isset($product->tags[$this->id_lang]) ? $product->tags[$this->id_lang] : array());
		return $row;
	}
	
	protected function itemExportSortOrder($row, $product) {
		return $row;
	}
	
	protected function itemExportIsHighlight($row, $product) {
		$highlights = self::getHighlightProducts();
		$isHighlight = in_array($product->id, $highlights) || $product->on_sale;
		$row['is_highlight'] = (int)$isHighlight;
		
		return $row;
	}
	
	protected function itemExportHighlightOrderIndex($row, $product) {
		static $highlightIndex = 1;
		
		$highlights = self::getHighlightProducts();
		$isHighlight = in_array($product->id, $highlights) || $product->on_sale;
		if ($isHighlight){
			$row['highlight_order_index'] = $highlightIndex++;
		}
		
		return $row;
	}
	
	protected function itemExportMarketplace($row, $product) {
		return $row;
	}
	
	protected function itemExportInternalOrderInfo($row, $product) {
		return $row;
	}
	
	protected function itemExportRelatedShopItemNumbers($row, $product) {
		$row['related_shop_item_numbers'] = 	Db::getInstance()->getValue('SELECT GROUP_CONCAT(`id_product_2` SEPARATOR \'||\') FROM `'._DB_PREFIX_.'accessory` WHERE `id_product_1` = '.$product->id.' GROUP BY `id_product_1`');
		return $row;
	}
	
	protected function itemExportAgeRating($row, $product) {
		return $row;
	}
	
	protected function itemExportWeight($row, $product) {
		$row['weight'] = self::convertProductWeightToGrams($product->weight);
		return $row;
	}
	
	protected function itemExportBlockPricing($row, $product) {
		return $row;
	}
	
	protected function itemExportHasChildren($row, $product) {
		$row['has_children'] = $product->hasAttributes() ? 1 : 0;
		return $row;
	}
	
	protected function itemExportParentItemNumber($row, $product) {
		return $row;
	}
	
	protected function itemExportOptions($row, $product) {
		return $row;
	}
	
	protected function itemExportAttributes($row, $product) {
		
		//Prodcut attributes
		if($product->hasAttributes()){
			$attributes = $product->getAttributeCombinaisons($this->id_lang);
			$images = $product->getCombinationImages($this->id_lang);
		
			$combinations = array();
			$attribute_groups = array();
			foreach($attributes as $a){
				$combinations[$a['id_product_attribute']][$a['id_attribute_group']] = $a;
				$attribute_groups[$a['id_attribute_group']] = $a['group_name'];
			}
		
			$i = 1;
			foreach($attribute_groups as $id => $name){
				$row['attribute_'.($i++)] = $name;
			}

			$r = $row;
			foreach($combinations as $id => $c){
				$combination = current($c);
				
					
				//Images
				$image_urls = array();
				if(isset($images[$id]) && is_array($images[$id])){
					foreach($images[$id] as $i){
						if(version_compare(_PS_VERSION_, '1.5.0.0', '>=')){
							array_push($image_urls, $this->context->link->getImageLink($product->link_rewrite, $product->id.'-'.$i['id_image'], 'thickbox_default'));
						}elseif(version_compare(_PS_VERSION_, '1.5.0.0', '<') && version_compare(_PS_VERSION_, '1.4.0.0', '>=')){
							// lower than 1.5.0.0 higher than 1.4.0.0
							array_push($image_urls, $this->context->link->getImageLink($product->link_rewrite, $product->id.'-'.$i['id_image'], 'thickbox'));
						} else {
							// lower then 1.4.0.0
							array_push($image_urls, _PS_BASE_URL_.$this->context->link->getImageLink($product->link_rewrite, $product->id.'-'.$i['id_image'], 'thickbox'));
						}
					}
				}
					
				if((version_compare(_PS_VERSION_, '1.4.0.2', '>=') && $product->available_for_order && $combination['quantity'] > 0 || version_compare(_PS_VERSION_, '1.4.0.2', '<')) && $combination['quantity'] > 0){
					$availableText = $product->available_now;
					$r['is_available'] = 1;
				} else {
					$availableText = $product->available_later;
					$r['is_available'] = 0;
				}
		
				$r['item_number'] = 	self::prefix.$product->id.'_'.$id;
				$r['has_children'] = 	0;
				$r['parent_item_number'] = 	$row['item_number'];
				$r['urls_images'] =		implode('||', $image_urls);
				$reduction = (float)$product->getPrice(true, (int)$id, 2, NULL, true);
				$r['old_unit_amount'] = Tools::ps_round($reduction != 0 ? $product->getPrice(true, (int)$id, 2, NULL, false, false) : 0, 2);
				$r['unit_amount'] = 	Tools::ps_round($product->getPrice(true, (int)$id, 2), 2);
					
				if(version_compare(_PS_VERSION_, '1.4.1.0', '>=') && !empty($product->unity) && $product->unit_price_ratio > 0.000000){
					$r['basic_price'] = Tools::displayPrice($product->getPrice(true, (int)$id, 2)/$product->unit_price_ratio).' '.$this->shopgateModul->l('per').' '.$product->unity;
				}
				
				$r['stock_quantity'] = 	$combination['quantity'];
				$r['ean'] = 		$combination['ean13'];
				$r['weight'] = 		$row['weight'] + self::convertProductWeightToGrams($combination['weight']);
				if(version_compare(_PS_VERSION_, '1.4.0.2', '>=')){
					$r['minimum_order_quantity'] = $combination['minimal_quantity'];
				}
				$r['available_text'] = $availableText;
				$r['item_number_public'] = 	(array_key_exists('reference', $combination) && !empty($combination['reference'])) ? $combination['reference'] : '';
				
				$i = 1;
				foreach($attribute_groups as $id => $name){
					$r['attribute_'.($i++)] = $c[$id]['attribute_name'];
				}
					
				$this->addItem($r);
			}
		}
		return $row;
	}
	
	protected function itemExportInputFields($row, $product) {
		//Product customizations
		if($product->customizable){
			$row['has_input_fields'] = 1;
			$cfields = $product->getCustomizationFields($this->id_lang);
		
			$i = 1;
			foreach($cfields as $f){
				$row['input_field_'.$i.'_type'] = ($f['type'] == 1) ? 'text' : 'image';
				$row['input_field_'.$i.'_label'] = $f['name'];//$f['id_customization_field'];
				//		    $row['input_field_'.$i.'_infotext'] = $f['name'];
				$row['input_field_'.$i.'_required'] = (bool)$f['required'];
				$i++;
			}
		}
		
		return $row;
	}

	protected function createCategoriesCsv(){
		$maxSortOrder = Db::getInstance((defined('_PS_USE_SQL_SLAVE_') ? _PS_USE_SQL_SLAVE_ : null))->ExecuteS(
			'SELECT id_parent, MAX(position) as max_position FROM `'._DB_PREFIX_.'category` GROUP BY id_parent'
		);
		
		$maxSortOrderByCategoryNumber = array();
		foreach($maxSortOrder as $sortOrder){
			$maxSortOrderByCategoryNumber[$sortOrder['id_parent']] = $sortOrder['max_position'];
		}
		
		$rootCategoriesByCategoryId = array();
		if(version_compare(_PS_VERSION_, '1.5.0.0', '<')){
			// lower than 1.5.0.0
			$cats = Db::getInstance((defined('_PS_USE_SQL_SLAVE_') ? _PS_USE_SQL_SLAVE_ : null))->ExecuteS('SELECT c.*, cl.`name`, cl.`link_rewrite` FROM `'._DB_PREFIX_.'category` c NATURAL LEFT JOIN `'._DB_PREFIX_.'category_lang` cl WHERE c.`id_category`!=1 AND cl.`id_lang` = '.$this->id_lang);
		} else {
			$cats = Db::getInstance((defined('_PS_USE_SQL_SLAVE_') ? _PS_USE_SQL_SLAVE_ : null))->ExecuteS('SELECT c.*, cl.`name`, cl.`link_rewrite` FROM `'._DB_PREFIX_.'category` c NATURAL LEFT JOIN `'._DB_PREFIX_.'category_lang` cl WHERE c.`id_parent`!=0 AND c.`is_root_category`!=1 AND cl.`id_lang` = '.$this->id_lang);
			$rootCategoriesByCategoryId = $this->getRootCategoriesByCategoryId();
		}
		
		foreach($cats as $c){
			$cat = $this->buildDefaultCategoryRow();
			
			$cat['category_number'] =	(int)$c['id_category'];
			$cat['category_name'] = 	(string)$c['name'];
			$cat['parent_id'] = 	($c['id_parent'] == 1 || !empty($rootCategoriesByCategoryId[(int)$c['id_parent']])) ? '' : (int)$c['id_parent'];
			if(version_compare(_PS_VERSION_, '1.5.0.0', '<')){
				// lower than 1.5.0.0
				$cat['url_image'] = 	(string)_PS_BASE_URL_.$this->context->link->getCatImageLink($c['link_rewrite'], $c['id_category'], 'large');
			} else {
				$cat['url_image'] = 	(string)$this->context->link->getCatImageLink($c['link_rewrite'], $c['id_category'], 'category_default');
			}
			$cat['order_index'] = 	(int)($maxSortOrderByCategoryNumber[$c['id_parent']]-$c['position'])+1;
			$cat['is_active'] = 	(bool)$c['active'];
			$cat['url_deeplink'] = 	(string)$this->context->link->getCategoryLink($c['id_category'], $c['link_rewrite'], $this->id_lang);
			 
			$this->addItem($cat);
		}
	}
	
	/**
	 * used in Prestashop 1.5.x.x to find root categories
	 */
	protected function getRootCategoriesByCategoryId(){
		static $rootCategoriesByCategoryId = null;
		
		if(is_null($rootCategoriesByCategoryId)){
			$rootCategories = Db::getInstance((defined('_PS_USE_SQL_SLAVE_') ? _PS_USE_SQL_SLAVE_ : null))->ExecuteS('SELECT c.* FROM `'._DB_PREFIX_.'category` c NATURAL LEFT JOIN `'._DB_PREFIX_.'category_lang` cl WHERE (c.`is_root_category`=1 OR c.`id_category` = 1) AND cl.`id_lang` = '.$this->id_lang);
			$rootCategoriesByCategoryId = array();
			foreach($rootCategories as $rootCategory){
				$rootCategoriesByCategoryId[(int)$rootCategory['id_category']] = $rootCategory;
			}
		}
		return $rootCategoriesByCategoryId;
	}

	protected function createReviewsCsv()
	{

	}
	
	public function checkCart(ShopgateCart $shopgateCart) {
		
	}
	
	public function redeemCoupons(ShopgateCart $shopgateCart) {
		
	}
	
	public function getSettings() {
	
	}
	public function getRedirect()
	{
		return $this->builder->buildRedirect();
	}
}

class PSShopgatePluginUS extends PSShopgatePlugin {
	
	
	protected function itemExportBasicPrice($row, $product) {
		if(version_compare(_PS_VERSION_, '1.4.1.0', '>=') && !empty($product->unity) && $product->unit_price_ratio > 0.000000){
			//TODO:? getPrice(false...) then we get only the net amount
// 			$row['basic_price'] = Tools::displayPrice($product->getPrice(false, NULL, 2)/$product->unit_price_ratio).' '.$this->shopgateModul->l('per').' '.$product->unity;
			$row['basic_price'] = Tools::displayPrice($product->getPrice(true, NULL, 2)/$product->unit_price_ratio).' '.$this->shopgateModul->l('per').' '.$product->unity;
		}
		return $row;
	}
	
	protected function itemExportTaxClass($row, $product) {
		$p = array();
		$p['tax_class_active'] = $product->tax_class_active;
		$p['tax_class_name'] = $product->tax_class_name;
		$p['tax_class_id'] = $product->tax_class_id;
		if (!empty($p['tax_class_active']) && !empty($p['tax_class_name']) && !empty($p['tax_class_id'])) {
			$row['tax_class'] = $p['tax_class_id'].'=>'.$p['tax_class_name'];
		}
	
		return $row;
	}
	
	/**
	 *
	 * @param array $aItem
	 * @param Mage_Catalog_Model_Product $oProduct
	 * @param Mage_Catalog_Model_Product $parentItem
	 */
	protected function itemExportUnitAmountNet($row, $product) {
		// getPrice(false...) then we get only the net amount
		$row['unit_amount_net'] = $product->getPrice(false, null, 2);
		return $row;
	}
	
	/**
	 *
	 * @param array $aItem
	 * @param Mage_Catalog_Model_Product $oProduct
	 */
	protected function itemExportOldUnitAmountNet($row, $product) {
		$reduction = (float)$product->getPrice(false, NULL, 2, NULL, true);
		$row['old_unit_amount'] = Tools::ps_round($reduction != 0 ? $product->getPrice(false, NULL, 2, NULL, false, false) : 0, 2);
		return $row;
	}
	
	protected function itemExportAttributes($row, $product) {
		
		//Prodcut attributes
		if($product->hasAttributes()){
			$attributes = $product->getAttributeCombinaisons($this->id_lang);
			$images = $product->getCombinationImages($this->id_lang);
		
			$combinations = array();
			$attribute_groups = array();
			foreach($attributes as $a){
				$combinations[$a['id_product_attribute']][$a['id_attribute_group']] = $a;
				$attribute_groups[$a['id_attribute_group']] = $a['group_name'];
			}
		
			$i = 1;
			foreach($attribute_groups as $id => $name){
				$row['attribute_'.($i++)] = $name;
			}

			$r = $row;
			foreach($combinations as $id => $c){
				$combination = current($c);
				
					
				//Images
				$image_urls = array();
				if(isset($images[$id]) && is_array($images[$id])){
					foreach($images[$id] as $i){
						if(version_compare(_PS_VERSION_, '1.5.0.0', '>=')){
							array_push($image_urls, $this->context->link->getImageLink($product->link_rewrite, $product->id.'-'.$i['id_image'], 'thickbox_default'));
						}elseif(version_compare(_PS_VERSION_, '1.5.0.0', '<') && version_compare(_PS_VERSION_, '1.4.0.0', '>=')){
							// lower than 1.5.0.0 higher than 1.4.0.0
							array_push($image_urls, $this->context->link->getImageLink($product->link_rewrite, $product->id.'-'.$i['id_image'], 'thickbox'));
						} else {
							// lower then 1.4.0.0
							array_push($image_urls, _PS_BASE_URL_.$this->context->link->getImageLink($product->link_rewrite, $product->id.'-'.$i['id_image'], 'thickbox'));
						}
					}
				}
					
				if((version_compare(_PS_VERSION_, '1.4.0.2', '>=') && $product->available_for_order && $combination['quantity'] > 0 || version_compare(_PS_VERSION_, '1.4.0.2', '<')) && $combination['quantity'] > 0){
					$availableText = $product->available_now;
					$r['is_available'] = 1;
				} else {
					$availableText = $product->available_later;
					$r['is_available'] = 0;
				}
				
				$r['item_number'] = 	self::prefix.$product->id.'_'.$id;
				$r['has_children'] = 	0;
				$r['parent_item_number'] = 	$row['item_number'];
				$r['urls_images'] =		implode('||', $image_urls);
				// getPrice(false...) then we get only the net amount
				$reduction = (float)$product->getPrice(false, (int)$id, 2, NULL, true);
				// getPrice(false...) then we get only the net amount
				$r['old_unit_amount_net'] = Tools::ps_round($reduction != 0 ? $product->getPrice(false, (int)$id, 2, NULL, false, false) : 0, 2);
				// getPrice(false...) then we get only the net amount
				$r['unit_amount_net'] = 	Tools::ps_round($product->getPrice(false, (int)$id, 2), 2);
					
				if(version_compare(_PS_VERSION_, '1.4.1.0', '>=') && !empty($product->unity) && $product->unit_price_ratio > 0.000000){
					// getPrice(false...) then we get only the net amount
					$r['basic_price'] = Tools::displayPrice($product->getPrice(false, (int)$id, 2)/$product->unit_price_ratio).' '.$this->shopgateModul->l('per').' '.$product->unity;
				}
				
				$r['stock_quantity'] = 	$combination['quantity'];
				$r['ean'] = 		$combination['ean13'];
				$r['weight'] = 		$row['weight'] + self::convertProductWeightToGrams($combination['weight']);
				if(version_compare(_PS_VERSION_, '1.4.0.2', '>=')){
					$r['minimum_order_quantity'] = $combination['minimal_quantity'];
				}
				$r['available_text'] = $availableText;
				$r['item_number_public'] = 	(array_key_exists('reference', $combination) && !empty($combination['reference'])) ? $combination['reference'] : '';
				
				$i = 1;
				foreach($attribute_groups as $id => $name){
					$r['attribute_'.($i++)] = $c[$id]['attribute_name'];
				}
					
				$this->addItem($r);
			}
		}
		return $row;
	}
	
	public function createPluginInfo() {
		return array(
			'PS Version' => _PS_VERSION_,
			'Plugin' => 'US'
		);
	}
	
	protected function buildDefaultItemRow() {
		$row = parent::buildDefaultItemRow();
	
		// remove old fileds
		unset($row["unit_amount"]);
		unset($row["old_unit_amount"]);
		unset($row["tax_percent"]);
	
		$newFields = array(
			"tax_class" => "", /** $this->itemExportTaxClass */
			"unit_amount_net" => "0", /** $this->itemExportUnitAmountNet */
			"old_unit_amount_net" => "", /** $this->itemExportOldUnitAmountNet */
		);
	
		$row = array_slice($row, 0, 3, true) +
		$newFields +
		array_slice($row, 3, count($row)-3, true);
	
		return $row;
	}
}

?>