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
class PSShopgatePlugin extends ShopgatePlugin
{
	protected $id_lang = 1;
	protected $id_currency = 0;
	protected $currency_iso = false;
	protected $context = null;
	protected $shopgateModule = null;
	protected $shippingServiceList = array();

	const PREFIX = 'BD';

	const PS_CONST_IMAGE_TYPE_LARGE = 'large';
	const PS_CONST_IMAGE_TYPE_CATEGORY_DEFAULT = 'category%sdefault';

	/**
	 * default no taxable class name
	 */
	const DEFAULT_NO_TAXABLE_CLASS_NAME = 'Not Taxable';

	public function startup()
	{
		//Set configs that depends on prestashop settings
		require_once(dirname(__FILE__).'/PSShopgateConfig.php');
		include_once dirname(__FILE__).'/../backward_compatibility/backward.php';

		$this->id_lang = Configuration::get('SHOPGATE_LANGUAGE_ID');

		/**
		 * read config from db
		 */
		$this->config = new ShopgateConfigPresta(
			Configuration::get('SHOPGATE_CONFIG') ?
				unserialize(Configuration::get('SHOPGATE_CONFIG')) :
				array()
		);
		$this->shopgateModule = new ShopGate();

		$config = $this->config->toArray();
		$this->id_currency = $this->context->cookie->id_currency = Currency::getIdByIsoCode($config['currency']);
		$this->setCurrencyIso();
		$this->config->setUseStock(!((bool)Configuration::get('PS_ORDER_OUT_OF_STOCK')));
		$this->shippingServiceList = array
		(
			'OTHER',
			'DHL',
			'DHLEXPRESS',
			'DP',
			'DPD',
			'FEDEX',
			'GLS',
			'HLG',
			'TNT',
			'TOF',
			'UPS',
			'LAPOSTE',
		);
	}

	/**
	 * @return array|mixed[]
	 */
	public function createPluginInfo()
	{
		return array(
			'PS Version' => _PS_VERSION_,
			'Plugin' => 'standard'
		);
	}

	protected function setCurrencyIso()
	{
		$this->currency_iso = Tools::strtoupper(Db::getInstance()->getValue('
			SELECT
				`iso_code`
			FROM
				`'._DB_PREFIX_.'currency`
			WHERE
				`id_currency` = '.(int)$this->id_currency)
		);
	}

	/**
	 * @param string $user
	 * @param string $pass
	 *
	 * @return ShopgateCustomer
	 * @throws ShopgateLibraryException
	 */
	public function getCustomer($user, $pass)
	{
		$id_customer = (int)Db::getInstance()->getValue('
			SELECT `id_customer`
			FROM `'._DB_PREFIX_.'customer`
			WHERE
			`active` AND
			`email` = \''.pSQL($user).'\' AND
			`passwd` = \''.Tools::encrypt($pass).'\' AND
			`deleted` = 0
			'.(version_compare(_PS_VERSION_, '1.4.1.0', '>=') ? ' AND `is_guest` = 0' : ''));

		if (!$id_customer)
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_WRONG_USERNAME_OR_PASSWORD, 'Username or password is incorrect');

		/** @var CustomerCore $customer */
		$customer = new Customer($id_customer);
		$gender = array(
			1 => 'm',
			2 => 'f',
			9 => null
		);

		$shopgateCustomer = new ShopgateCustomer();

		$shopgateCustomer->setCustomerId($customer->id);
		$shopgateCustomer->setCustomerNumber($customer->id);
		$shopgateCustomer->setFirstName($customer->firstname);
		$shopgateCustomer->setLastName($customer->lastname);
		$shopgateCustomer->setGender(isset($gender[$customer->id_gender]) ? $gender[$customer->id_gender] : null);
		$shopgateCustomer->setBirthday($customer->birthday);
		$shopgateCustomer->setMail($customer->email);
		$shopgateCustomer->setNewsletterSubscription($customer->newsletter);

		$addresses = array();
		foreach ($customer->getAddresses($this->id_lang) as $a)
		{
			$address = new ShopgateAddress();

			$address->setId($a['id_address']);
			$address->setFirstName($a['firstname']);
			$address->setLastName($a['lastname']);
			$address->setCompany($a['company']);
			$address->setStreet1($a['address1']);
			$address->setStreet2($a['address2']);
			$address->setCity($a['city']);
			$address->setZipcode($a['postcode']);
			$address->setCountry($a['country']);
			$address->setState($a['state']);
			$address->setPhone($a['phone']);
			$address->setMobile($a['phone_mobile']);

			array_push($addresses, $address);
		}

		$shopgateCustomer->setAddresses($addresses);

		/**
		 * customer groups
		 */
		$customerGroups = array();

		if (is_array($customer->getGroups()))
		{
			foreach ($customer->getGroups() as $customerGroupId)
			{
				$groupItem = new Group(
					$customerGroupId,
					$this->id_lang,
					$this->context->shop->id ? $this->context->shop->id : false
				);
				$group = new ShopgateCustomerGroup();
				$group->setId($groupItem->id);
				$group->setName($groupItem->name);
				array_push($customerGroups, $group);
			}
		}

		$shopgateCustomer->setCustomerGroups($customerGroups);


		return $shopgateCustomer;
	}

	/**
	 * @param CustomerCore $customer
	 * @param ShopgateAddress $shopgateAddress
	 *
	 * @return AddressCore
	 * @throws ShopgateLibraryException
	 */
	protected function getPSAddress($customer, ShopgateAddress $shopgateAddress)
	{
		// Get country
		$id_country = Country::getByIso($shopgateAddress->getCountry());
		if (!$id_country)
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_UNKNOWN_COUNTRY_CODE, 'Invalid country code:'.$id_country, true);

		// Get state
		$id_state = 0;
		if ($shopgateAddress->getState())
			$id_state = (int)Db::getInstance()->getValue('SELECT `id_state` FROM `'._DB_PREFIX_.'state` WHERE `id_country` = '.$id_country.' AND `iso_code` = \''.pSQL(Tools::substr($shopgateAddress->getState(), 3, 2)).'\'');

		// Create alias
		$alias = Tools::substr('Shopgate_'.$customer->id.'_'.sha1($customer->id.'-'.$shopgateAddress->getFirstName().'-'.$shopgateAddress->getLastName().'-'.$shopgateAddress->getCompany().'-'.$shopgateAddress->getStreet1().'-'.$shopgateAddress->getStreet2().'-'.$shopgateAddress->getZipcode().'-'.$shopgateAddress->getCity()), 0, 32);

		// Try getting address id by alias
		$id_address = Db::getInstance()->getValue('SELECT `id_address` FROM `'._DB_PREFIX_.'address` WHERE `alias` = \''.pSQL($alias).'\' AND `id_customer`='.$customer->id);

		// Get or create address
		/** @var AddressCore $address */
		$address = new Address($id_address ? $id_address : null);
		if (!$address->id)
		{
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
			if (!$address->add())
				throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_DATABASE_ERROR, 'Unable to create address', true);
		}

		return $address;
	}

	/**
	 * @param ShopgateOrderItem $item
	 *
	 * @return array
	 */
	protected function getProductIdentifiers(ShopgateOrderItem $item)
	{
		return explode('_', Tools::substr($item->getItemNumber(), Tools::strlen(self::PREFIX)));
	}

	/**
	 * @param $order_state_var
	 *
	 * @return int
	 */
	protected function getOrderStateId($order_state_var)
	{
		return (int)(defined($order_state_var) ? constant($order_state_var) : (defined('_'.$order_state_var.'_') ? constant('_'.$order_state_var.'_') : Configuration::get($order_state_var)));
	}

	/**
	 * @param $shippingCosts
	 *
	 * @return bool
	 */
	protected function setShippingCosts($shippingCosts)
	{
		$deliveryZones = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'delivery` WHERE `id_carrier` = '.(int)Configuration::get('SHOPGATE_CARRIER_ID'));

		// create for each zone delivery options
		foreach ($deliveryZones as $deliveryZone)
		{
			/** @var DeliveryCore $deliveryRange */
			$deliveryRange = new Delivery($deliveryZone['id_delivery']);

			// PS_SHIPPING_HANDLING is a fix to decrease the shipping for the amount that is set up in the shipping configuration
			$deliveryRange->price = $shippingCosts;

			if (!$deliveryRange->update())
				return false;
		}

		return true;
	}

	/**
	 * @param ShopgateOrder $order
	 *
	 * @return array
	 * @throws ShopgateLibraryException
	 */
	public function addOrder(ShopgateOrder $order)
	{
		$this->log('PS start add_order', ShopgateLogger::LOGTYPE_DEBUG);

		$shopgateOrder = PSShopgateOrder::instanceByOrderNumber($order->getOrderNumber());
		if ($shopgateOrder->id)
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_DUPLICATE_ORDER, 'external_order_id: '.$shopgateOrder->id_order, true);

		$comments = array();

		// generate products array
		$products = $this->insertOrderItems($order);

		//Get or create customer
		$id_customer = Customer::customerExists($order->getMail(), true, false);
		/** @var CustomerCore $customer */
		$customer = new Customer($id_customer ? $id_customer : (int)$order->getExternalCustomerId());
		if (!$customer->id)
			$customer = $this->createCustomer($customer, $order);

		// prepare addresses: company has to be shorten. add mobile phone / telephone
		$this->prepareAddresses($order);

		//Get invoice and delivery addresses
		$invoiceAddress = $this->getPSAddress($customer, $order->getInvoiceAddress());
		$deliveryAddress = ($order->getInvoiceAddress() == $order->getDeliveryAddress())
			? $invoiceAddress
			: $this->getPSAddress($customer, $order->getDeliveryAddress());

		//Creating currency
		$this->log('PS setting currency', ShopgateLogger::LOGTYPE_DEBUG);
		$id_currency = $order->getCurrency() ? Currency::getIdByIsoCode($order->getCurrency()) : $this->id_currency;
		/** @var CurrencyCore $currency */
		$currency = new Currency($id_currency ? $id_currency : $this->id_currency);

		//Creating new cart
		$this->log('PS set cart variables', ShopgateLogger::LOGTYPE_DEBUG);
		$cart = new Cart();
		$cart->id_lang = $this->id_lang;
		$cart->id_currency = $currency->id;
		$cart->id_address_delivery = $deliveryAddress->id;
		$cart->id_address_invoice = $invoiceAddress->id;
		$cart->id_customer = $customer->id;

		if (version_compare(_PS_VERSION_, '1.4.1.0', '>='))
		{
			// id_guest is a connection to a ps_guest entry which includes screen width etc.
			// is_guest field only exists in Prestashop 1.4.1.0 and higher
			$cart->id_guest = $customer->is_guest;
		}

		$cart->recyclable = 0;
		$cart->gift = 0;
		$cart->id_carrier = (int)Configuration::get('SHOPGATE_CARRIER_ID');
		$internalShippingInfo = $order->getShippingInfos()->getInternalShippingInfo();
		if ($internalShippingInfo)
		{
			$internalShippingInfo = unserialize($internalShippingInfo);
			if (array_key_exists('carrierId', $internalShippingInfo))
				$cart->id_carrier = $internalShippingInfo['carrierId'];
		}
		$cart->secure_key = $customer->secure_key;

		$this->log('PS try to create cart', ShopgateLogger::LOGTYPE_DEBUG);
		if (!$cart->add())
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_DATABASE_ERROR, 'Unable to create cart', true);

		//Adding items to cart
		$this->log('PS adding items to cart', ShopgateLogger::LOGTYPE_DEBUG);
		foreach ($products as $p)
		{
			$this->log('PS cart updateQty product id: '.$p['id_product'], ShopgateLogger::LOGTYPE_DEBUG);
			$this->log('PS cart updateQty product quantity: '.$p['quantity'], ShopgateLogger::LOGTYPE_DEBUG);
			$this->log('PS cart updateQty product quantity_difference: '.$p['quantity_difference'], ShopgateLogger::LOGTYPE_DEBUG);
			$this->log('PS cart updateQty product id_product_attribute: '.$p['id_product_attribute'], ShopgateLogger::LOGTYPE_DEBUG);
			$this->log('PS cart updateQty product delivery address: '.$deliveryAddress->id, ShopgateLogger::LOGTYPE_DEBUG);

			//TODO deal with customizations
			$id_customization = false;

			if ($p['quantity'] - $p['quantity_difference'] > 0)
			{
				// only if the result of $p['quantity'] - $p['quantity_difference'] is higher then 0
				$cart->updateQty($p['quantity'] - $p['quantity_difference'], $p['id_product'], $p['id_product_attribute'], $id_customization, 'up', $deliveryAddress->id);
			}

			if ($p['quantity_difference'] > 0)
			{
				$this->log('PS try to add cart message ', ShopgateLogger::LOGTYPE_DEBUG);
				/** @var MessageCore $message */
				$message = new Message();
				$message->id_cart = $cart->id;
				$message->private = 1;
				$message->message = 'Warning, wanted quantity for product "'.$p['name'].'" was '.$p['quantity'].' unit(s), however, the amount in stock is '.$p['quantity_in_stock'].' unit(s). Only '.$p['quantity_in_stock'].' unit(s) were added to the order';

				$message->save();
			}
		}

		$this->log('Add Coupons', ShopgateLogger::LOGTYPE_DEBUG);
		$coupons = $order->getExternalCoupons();
		if (!empty($coupons))
		{
			foreach ($coupons as $coupon)
			{
				/** @var $coupon ShopgateExternalCoupon */
				$code = $coupon->getCode();
				$this->context = Context::getContext();
				$this->context->cart = $cart;
				if (($cartRule = new CartRule(CartRule::getIdByCode($code))) && Validate::isLoadedObject($cartRule))
				{
					/** @var CartRuleCore $cartRule */
					if ($cartRule->checkValidity($this->context, false, true))
						$this->log('Coupon not valid '.$code, ShopgateLogger::LOGTYPE_DEBUG);
					else
						$cart->addCartRule($cartRule->id);
				}
			}
		}

		$shopgate = new ShopGate();
		$payment_name = $shopgate->getTranslation('Mobile Payment');

		$this->log('PS map payment method', ShopgateLogger::LOGTYPE_DEBUG);
		if (!$order->getIsShippingBlocked())
		{
			$id_order_state = $this->getOrderStateId('PS_OS_PAYMENT');
			switch ($order->getPaymentMethod())
			{
				case 'SHOPGATE':
					$payment_name = $shopgate->getTranslation('Shopgate');
					break;
				case 'PREPAY':
					$payment_name = $shopgate->getTranslation('Bankwire');
					$id_order_state = $this->getOrderStateId('PS_OS_BANKWIRE');
					break;
				case 'COD':
					$payment_name = $shopgate->getTranslation('Cash on Delivery');
					$id_order_state = $this->getOrderStateId('PS_OS_PREPARATION');
					break;
				case 'PAYPAL':
					$id_order_state = $this->getOrderStateId('PS_OS_PAYMENT');
					$payment_name = $shopgate->getTranslation('PayPal');
					break;
				default:
					break;
			}
		}
		else
		{
			$id_order_state = $this->getOrderStateId('PS_OS_SHOPGATE');

			switch ($order->getPaymentMethod())
			{
				case 'SHOPGATE':
					$payment_name = $shopgate->getTranslation('Shopgate');
					break;
				case 'PREPAY':
					$payment_name = $shopgate->getTranslation('Bankwire');
					break;
				case 'COD':
					$payment_name = $shopgate->getTranslation('Cash on Delivery');
					$id_order_state = $this->getOrderStateId('PS_OS_PREPARATION');
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
		$this->log('PS set PSShopgateOrder object variables', ShopgateLogger::LOGTYPE_DEBUG);
		$shopgateOrder = new PSShopgateOrder();
		$shopgateOrder->order_number = $order->getOrderNumber();
		$shopgateOrder->shipping_cost = $shippingCosts;
		$shippingService = Configuration::get('SHOPGATE_SHIPPING_SERVICE');
		if (in_array($order->getShippingGroup(), $this->shippingServiceList))
			$shippingService = $order->getShippingGroup();
		$shopgateOrder->shipping_service = $shippingService;
		$shopgateOrder->id_cart = $cart->id;
		$shopgateOrder->shop_number = $this->config->getShopNumber();
		$shopgateOrder->comments = $this->jsonEncode($comments);

		if (version_compare(_PS_VERSION_, '1.4.0.2', '<'))
		{
			$this->log('PS lower 1.4.0.2: ', ShopgateLogger::LOGTYPE_DEBUG);
			// Fix: sets in database ps_delivery all zones of passed shippingCosts
			$this->setShippingCosts(0);
		}

		$this->log('PS try creating PSShopgateOrder object', ShopgateLogger::LOGTYPE_DEBUG);
		if (!$shopgateOrder->add())
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_DATABASE_ERROR, 'Unable to create shopgate order', true);

		//PS 1.5 compatibility
		if (version_compare(_PS_VERSION_, '1.5.0.0', '>='))
		{
			$this->log('PS 1.5.x.x: set cart context', ShopgateLogger::LOGTYPE_DEBUG);
			$this->context = Context::getContext();
			$this->context->cart = $cart;

			$this->log("PS 1.5.x.x: \$cart->setDeliveryOption(array(\$cart->id_address_delivery => \$cart->id_carrier.','))\n\n==============", ShopgateLogger::LOGTYPE_DEBUG);
			$cart->setDeliveryOption(array($cart->id_address_delivery => $cart->id_carrier.','));

			$this->log('PS 1.5.x.x: $cart->update()', ShopgateLogger::LOGTYPE_DEBUG);
			$cart->update();
		}

		$amountPaid = $order->getAmountComplete();
		if (version_compare(_PS_VERSION_, '1.4.0.2', '<'))
		{
			//subtracts the shipping costs.
			$amountPaid -= $shippingCosts;
		}

		$this->log('$shopgate->validateOrder($cart->id, $id_order_state, $amountPaid, $payment_name, NULL, array(), NULL, false, $cart->secure_key', ShopgateLogger::LOGTYPE_DEBUG);
		$this->log(
			'cart->id = '.var_export($cart->id, true).
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
				null,
				array(),
				null,
				false,
				$cart->secure_key
			);
		} catch (Swift_Message_MimeException $ex) {
			$this->log('$shopgate->validateOrder($cart->id, $id_order_state, $amountPaid, $payment_name, NULL, array(), NULL, false, $cart->secure_key) FAILED with Swift_Message_MimeException', ShopgateLogger::LOGTYPE_ERROR);
			// catch Exception if there is a problem with sending mails
		}

		if (version_compare(_PS_VERSION_, '1.4.0.2', '<') && (int)$shopgate->currentOrder > 0)
		{
			$this->log('PS < 1.4.0.2: update shipping and payment cost', ShopgateLogger::LOGTYPE_DEBUG);

			// in versions below 1.4.0.2 the shipping and payment costs must be updated after the order
			/** @var OrderCore $updateShopgateOrder */
			$updateShopgateOrder = new Order($shopgate->currentOrder);

			$updateShopgateOrder->total_paid = $order->getAmountComplete();
			$updateShopgateOrder->total_paid_real = $order->getAmountComplete();
			$updateShopgateOrder->total_products_wt = $order->getAmountItems();
			$updateShopgateOrder->total_shipping = $order->getAmountShipping() + $order->getAmountShopPayment();
			$updateShopgateOrder->update();
		}

		if ((int)$shopgate->currentOrder > 0)
		{
			$this->log('$shopgateOrder->update()', ShopgateLogger::LOGTYPE_DEBUG);
			$shopgateOrder->id_order = $shopgate->currentOrder;
			$shopgateOrder->update();

			return array(
				'external_order_id' => $shopgate->currentOrder,
				'external_order_number' => $shopgate->currentOrder
			);
		}
		else
		{
			$this->log('$shopgateOrder->delete()', ShopgateLogger::LOGTYPE_DEBUG);
			$shopgateOrder->delete();
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_DATABASE_ERROR, 'Unable to create order', true);
		}
	}

	/**
	 * @param ShopgateOrder $order
	 */
	protected function prepareAddresses(ShopgateOrder $order)
	{
		$this->log('PS start prepareAddresses', ShopgateLogger::LOGTYPE_DEBUG);
		$comments = array();

		// shorten company names in addresses if necessary and add a comment
		if (Tools::strlen($order->getInvoiceAddress()->getCompany()) > 32)
		{
			$comments['Invoice address\' company name <b>%s</b> has been shortened.'] = $order->getInvoiceAddress()->getCompany();
			$order->getInvoiceAddress()->setCompany(Tools::substr($order->getInvoiceAddress()->getCompany(), 0, 26).'[...]');
		}
		if (Tools::strlen($order->getDeliveryAddress()->getCompany()) > 32)
		{
			$comments['Delivery address\' company name <b>%s</b> has been shortened.'] = $order->getDeliveryAddress()->getCompany();
			$order->getDeliveryAddress()->setCompany(Tools::substr($order->getDeliveryAddress()->getCompany(), 0, 26).'[...]');
		}

		// set the customer's telephone and mobile number for addresses if necessary
		$phone = $order->getInvoiceAddress()->getPhone();
		if (empty($phone))
			$order->getInvoiceAddress()->setPhone($order->getPhone());
		$phone = $order->getDeliveryAddress()->getPhone();
		if (empty($phone))
			$order->getDeliveryAddress()->setPhone($order->getPhone());

		$mobile = $order->getInvoiceAddress()->getMobile();
		if (empty($mobile))
			$order->getInvoiceAddress()->setMobile($order->getMobile());
		$mobile = $order->getDeliveryAddress()->getMobile();
		if (empty($mobile))
			$order->getDeliveryAddress()->setMobile($order->getMobile());

		/**
		 * quick fix add mobile numbers
		 */
		foreach ($this->getRequiredAddressFields() as $field)
		{

			$key = $field['field_name'];

			/**
			 * phone
			 */
			if ($key == 'phone' && $order->getDeliveryAddress()->getPhone() == '')
				$order->getDeliveryAddress()->setPhone(1);
			if ($key == 'phone' && $order->getInvoiceAddress()->getPhone() == '')
				$order->getInvoiceAddress()->setPhone(1);

			/**
			 * mobile
			 */
			if ($key == 'phone_mobile' && $order->getDeliveryAddress()->getMobile() == '')
				$order->getDeliveryAddress()->setMobile(1);
			if ($key == 'phone_mobile' && $order->getInvoiceAddress()->getMobile() == '')
				$order->getInvoiceAddress()->setMobile(1);

		};


		$this->log('PS end prepareAddresses', ShopgateLogger::LOGTYPE_DEBUG);
	}

	/**
	 * returns the required fields from database
	 *
	 * @return mixed
	 */
	protected function getRequiredAddressFields()
	{
		$address = new Address();
		return $address->getFieldsRequiredDatabase();
	}

	/**
	 * @param CustomerCore $customer
	 * @param ShopgateOrder $order
	 *
	 * @return mixed
	 * @throws ShopgateLibraryException
	 */
	protected function createCustomer($customer, ShopgateOrder $order)
	{
		$this->log('start createCustomer()', ShopgateLogger::LOGTYPE_DEBUG);

		$birthday = $order->getInvoiceAddress()->getBirthday();
		$customer->lastname = $order->getInvoiceAddress()->getLastName();
		$customer->firstname = $order->getInvoiceAddress()->getFirstName();
		$customer->id_gender = ($order->getInvoiceAddress()->getGender() == 'm' ? 1 : ($order->getInvoiceAddress()->getGender() == 'f' ? 2 : 9));
		$customer->birthday = empty($birthday) ? '0000-00-00' : $birthday;
		$customer->email = $order->getMail();
		$customer->passwd = md5(_COOKIE_KEY_.time());
		$customer->newsletter = Configuration::get('SHOPGATE_SUBSCRIBE_NEWSLETTER') ? true : false;
		$customer->optin = false;
		if (version_compare(_PS_VERSION_, '1.4.1.0', '>='))
		{
			// guest accounts flag only exists in Prestashop 1.4.1.0 and higher
			$customer->addGroups(array(Configuration::get('PS_GUEST_GROUP')));
			$customer->is_guest = (int)Configuration::get('PS_GUEST_CHECKOUT_ENABLED');
		}

		if (!$customer->add())
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_DATABASE_ERROR, 'Unable to create customer', true);
		$this->log('end createCustomer()', ShopgateLogger::LOGTYPE_DEBUG);

		return $customer;
	}

	/**
	 * @param ShopgateOrder $order
	 *
	 * @return array
	 * @throws ShopgateLibraryException
	 */
	protected function insertOrderItems(ShopgateOrder $order)
	{
		$this->log('start insertOrderItems()', ShopgateLogger::LOGTYPE_DEBUG);

		$products = array();

		//Check product quantities
		$settings = Configuration::getMultiple(array('SHOPGATE_MIN_QUANTITY_CHECK', 'SHOPGATE_OUT_OF_STOCK_CHECK'));

		// complete weight of the order
		foreach ($order->getItems() as $i)
		{
			list($id_product, $id_product_attribute) = $this->getProductIdentifiers($i);

			if ($id_product == 0)
				continue;

			$wantedQty = (int)$i->getQuantity();
			/** @var ProductCore $product */
			$product = new Product($id_product, true, (int)Configuration::get('PS_LANG_DEFAULT'));

			$minQty = 1;
			if ((int)$id_product_attribute)
			{
				$stockQty = (int)Product::getQuantity((int)$id_product, (int)$id_product_attribute);
				if (version_compare(_PS_VERSION_, '1.4.0.7', '>='))
				{
					// this attribute doesn't exist before 1.4.0.7
					$minQty = Attribute::getAttributeMinimalQty((int)$id_product_attribute);
				}
			}
			else
			{
				$stockQty = (int)Product::getQuantity((int)$id_product, null);
				if (version_compare(_PS_VERSION_, '1.4.0.2', '>='))
				{
					// this attribute doesn't exist before 1.4.0.2
					$minQty = (int)$product->minimal_quantity;
				}
			}

			$oos_available = Product::isAvailableWhenOutOfStock($product->out_of_stock);
			$qtyDifference = 0;

			if (!$oos_available && $wantedQty > $stockQty)
				$qtyDifference = $wantedQty - $stockQty;

			$p = array();
			$p['id_product'] = (int)$id_product;
			$p['id_product_attribute'] = (int)$id_product_attribute;
			$p['name'] = $product->name;
			$p['quantity'] = $wantedQty;
			$p['quantity_in_stock'] = $stockQty;
			$p['quantity_difference'] = $qtyDifference;

			if (empty($p['name']))
				$p['name'] = $i->getName();

			if ($oos_available)
				$stockQty = $wantedQty;

			if ((bool)$settings['SHOPGATE_MIN_QUANTITY_CHECK'] && $wantedQty < $minQty)
				throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_DATABASE_ERROR, 'Minimum quantity required', true);

			if ((bool)$settings['SHOPGATE_OUT_OF_STOCK_CHECK'] && $wantedQty > $stockQty)
				throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_DATABASE_ERROR, 'Out of stock', true);

			array_push($products, $p);
		}

		$this->log('end insertOrderItems()', ShopgateLogger::LOGTYPE_DEBUG);

		return $products;
	}

	/**
	 * @param string $jobname
	 * @param        $params
	 * @param string $message
	 * @param int $errorcount
	 */
	public function cron($jobname, $params, &$message, &$errorcount)
	{
		return;
	}

	/**
	 * @param ShopgateOrder $order
	 *
	 * @return array
	 * @throws ShopgateLibraryException
	 */
	public function updateOrder(ShopgateOrder $order)
	{
		$shopgateOrder = PSShopgateOrder::instanceByOrderNumber($order->getOrderNumber());

		if (!Validate::isLoadedObject($shopgateOrder))
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_ORDER_NOT_FOUND, 'Order not found', true);

		$order_states = array();

		if ($order->getUpdatePayment() && $order->getIsPaid())
			array_push($order_states, $this->getOrderStateId('PS_OS_PAYMENT'));

		if ($order->getUpdateShipping() && !$order->getIsShippingBlocked())
			array_push($order_states, $this->getOrderStateId('PS_OS_PREPARATION'));

		if (count($order_states))
		{
			/** @var OrderCore $ps_order */
			$ps_order = new Order($shopgateOrder->id_order);
			foreach ($order_states as $id_order_state)
			{
				if (version_compare(_PS_VERSION_, '1.4.1.0', '<'))
				{
					/** @var OrderHIstoryCore $history */
					$history = new OrderHistory();
					$history->id_order = (int)($shopgateOrder->id_order);
					$history->changeIdOrderState((int)$id_order_state, (int)($shopgateOrder->id_order));
				}
				else
					$ps_order->setCurrentState($id_order_state);
			}
		}
		return array(
			'external_order_id' => $shopgateOrder->id_order,
			'external_order_number' => $shopgateOrder->id_order
		);
	}

	/**
	 * @param $weight
	 *
	 * @return mixed
	 */
	protected static function convertProductWeightToGrams($weight)
	{
		$ps_weight_unit = Tools::strtolower(Configuration::get('PS_WEIGHT_UNIT'));
		$multipliers = array(
			'kg' => 1000,
			'lbs' => 453.59237
		);

		if (array_key_exists($ps_weight_unit, $multipliers))
			$weight *= $multipliers[$ps_weight_unit];

		return $weight;
	}

	/**
	 * @return array
	 */
	public static function getHighlightProducts()
	{
		static $prepared = null, $rootCategoryId = null;

		if (is_null($prepared))
		{
			$prepared = array();

			if (empty($rootCategoryId))
			{
				if (version_compare(_PS_VERSION_, '1.5.0.0', '<'))
				{
					// lower than 1.5.0.0
					$rootCategoryId = 1;
				}
				else
					$rootCategoryId = Db::getInstance()->getValue('SELECT `id_category` FROM `'._DB_PREFIX_.'category` WHERE  `is_root_category` = 1');
			}

			$result = Db::getInstance()->ExecuteS('SELECT `id_product` FROM `'._DB_PREFIX_.'category_product` WHERE `id_category` = '.(int)$rootCategoryId);

			if ($result && count($result))
				foreach ($result as $product)
					array_push($prepared, (int)$product['id_product']);

			$prepared = array_unique($prepared);
		}

		return $prepared;
	}

	/**
	 * @param $array
	 * @param $keys_to_round
	 *
	 * @return bool
	 */
	protected static function roundPricesInArray(&$array, $keys_to_round)
	{
		if (!is_array($keys_to_round) || !count($keys_to_round))
			return false;

		foreach ($keys_to_round as $round_key)
			if (array_key_exists($round_key, $array))
				$array[$round_key] = Tools::ps_round($array[$round_key], 2);
	}

	protected function createItemsCsv()
	{
		$limit = Tools::getValue('limit', 0);
		$offset = Tools::getValue('offset', 0);

		$products = Product::getProducts($this->id_lang, $offset, $limit, 'id_product', 'DESC', false, true);

		$additionalLoaders = array(
			'itemExportOptions',
			'itemExportAttributes',
			'itemExportInputFields',
		);

		$loaders = array_merge($this->getCreateItemsCsvLoaders(), $additionalLoaders);

		foreach ($products as $product)
		{
			/** @var ProductCore $productModel */
			$productModel = new Product($product['id_product'], true, $this->id_lang);
			$row = $this->buildDefaultItemRow();
			$row = $this->executeLoaders($loaders, $row, $productModel);
			$this->addItemRow($row);
		}
	}

	/**
	 * @return array
	 */
	protected function getCategoryMaxSortOrder()
	{
		static $maxSortOrderByCategoryNumber = null;

		if (is_null($maxSortOrderByCategoryNumber))
		{
			$maxSortOrderCategories = Db::getInstance()->ExecuteS('
				SELECT id_category, MAX(position) as max_position
				FROM `'._DB_PREFIX_.'category_product`
				GROUP BY `id_category`'
			);

			$maxSortOrderByCategoryNumber = array();
			foreach ($maxSortOrderCategories as $sortOrderCategory)
				$maxSortOrderByCategoryNumber[$sortOrderCategory['id_category']] = $sortOrderCategory['max_position'];
		}

		return $maxSortOrderByCategoryNumber;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportItemNumber($row, $product)
	{
		$row['item_number'] = self::PREFIX.$product->id.'_0';
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportItemName($row, $product)
	{
		$row['item_name'] = $product->name;
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportUnitAmount($row, $product)
	{
		$row['unit_amount'] = Tools::ps_round($product->getPrice(true, null, 2), 2);
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportCurrency($row, $product)
	{
		$row['currency'] = $this->currency_iso;
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportTaxPercent($row, $product)
	{
		$row['tax_percent'] = $this->formatPriceNumber($product->tax_rate);
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportDescription($row, $product)
	{
		$descriptionSetting = Configuration::get('SHOPGATE_PRODUCT_DESCRIPTION');
		switch ($descriptionSetting)
		{
			case ShopGate::PRODUCT_EXPORT_SHORT_DESCRIPTION:
				$description = $product->description_short;
				break;
			case ShopGate::PRODUCT_EXPORT_BOTH_DESCRIPTIONS:
				$break = !empty($product->description_short) && !empty($product->description) ? '<br />' : '';
				$description = $product->description_short.$break.$product->description;
				break;
			case ShopGate::PRODUCT_EXPORT_DESCRIPTION:
			default:
				$description = $product->description;
				break;
		}
		$row['description'] = str_replace(array("\r", "\n"), '', $description);
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportUrlsImages($row, $product)
	{
		$image_urls = array();

		if (version_compare(_PS_VERSION_, '1.4.1.0', '>='))
			$image_ids = $product->getWsImages();
		else
			$image_ids = $product->getImages($this->id_lang);

		foreach ($image_ids as $i)
		{
			$image_id = array_key_exists('id_image', $i) ? $i['id_image'] : $i['id'];
			if (version_compare(_PS_VERSION_, '1.5.0.0', '>='))
				array_push($image_urls, $this->context->link->getImageLink($product->link_rewrite, $product->id.'-'.$image_id));
			elseif (version_compare(_PS_VERSION_, '1.5.0.0', '<') && version_compare(_PS_VERSION_, '1.4.0.0', '>='))
			{
				// lower than 1.5.0.0 higher than 1.4.0.0
				array_push($image_urls, $this->context->link->getImageLink($product->link_rewrite, $product->id.'-'.$image_id));
			}
			else
			{
				// lower then 1.4.0.0
				array_push($image_urls, _PS_BASE_URL_.$this->context->link->getImageLink($product->link_rewrite, $product->id.'-'.$image_id));
			}
		}
		$row['urls_images'] = (string)implode('||', $image_urls);
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportCategories($row, $product)
	{
		/** @var CategoryCore $category */
		$category = new Category($product->id_category_default);
		$row['categories'] = $category->getName($this->id_lang);
		return $row;
	}

	/**
	 * @param $row
	 * @param $product ProductCore
	 *
	 * @return mixed
	 */
	protected function itemExportCategoryNumbers($row, $product)
	{
		$categoryOrder = array();
		$maxSortOrderByCategoryNumber = $this->getCategoryMaxSortOrder();

		$categoryIds = array();

		/**
		 * check version < 1.5
		 */
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			foreach (Product::getIndexedCategories($product->id) as $key => $value)
				array_push($categoryIds, $value['id_category']);
		}
		else
			$categoryIds = $product->getCategories();

		foreach ($categoryIds as $categoryId)
		{
			$categoryModel = new Category($categoryId);
			$maxPosition = $maxSortOrderByCategoryNumber[$categoryId];
			array_push($categoryOrder, $categoryId.'=>'.($maxPosition - $categoryModel->position));
		}
		$row['category_numbers'] = implode('||', $categoryOrder);

		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportIsAvailable($row, $product)
	{
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportAvailableText($row, $product)
	{
		if ((version_compare(_PS_VERSION_, '1.4.0.2', '>=') && $product->available_for_order || version_compare(_PS_VERSION_, '1.4.0.2', '<')) && $product->quantity > 0)
		{
			$availableText = $product->available_now;
			$row['is_available'] = 1;
		}
		else
		{
			$availableText = $product->available_later;
			$row['is_available'] = 0;
		}
		$row['available_text'] = $availableText;
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportManufacturer($row, $product)
	{
		$row['manufacturer'] = $product->manufacturer_name;
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportManufacturerItemNumber($row, $product)
	{
		$row['manufacturer_item_number'] = $product->id_manufacturer;
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportUrlDeeplink($row, $product)
	{
		$row['url_deeplink'] = $this->context->link->getProductLink($product->id, $product->link_rewrite, $product->category, $product->ean13, $this->id_lang);
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportItemNumberPublic($row, $product)
	{
		$row['item_number_public'] = !empty($product->reference) ? $product->reference : '';
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportOldUnitAmount($row, $product)
	{
		$reduction = (float)$product->getPrice(true, null, 2, null, true);
		$row['old_unit_amount'] = Tools::ps_round($reduction != 0 ? $product->getPrice(true, null, 2, null, false, false) : 0, 2);
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportProperties($row, $product)
	{
		$features = $product->getFrontFeatures($this->id_lang);
		$properties = array();
		foreach ($features as $f)
			array_push($properties, $f['name'].'=>'.$f['value']);
		$row['properties'] = implode('||', $properties);
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportMsrp($row, $product)
	{
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportShippingCostsPerOrder($row, $product)
	{
		return $row;
	}

	/**
	 * @param array $row
	 * @param ProductCore $product
	 *
	 * @return array
	 */
	protected function itemExportAdditionalShippingCostsPerUnit($row, $product)
	{
		// TODO: maybe tax must to be added
		if (property_exists($product, 'additional_shipping_cost'))
			$row['additional_shipping_costs_per_unit'] = Tools::ps_round($product->additional_shipping_cost, 2);
		return $row;
	}

	/**
	 * @param $row
	 * @param $product
	 *
	 * @return mixed
	 */
	protected function itemExportIsFreeShipping($row, $product)
	{
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportBasicPrice($row, $product)
	{
		if (version_compare(_PS_VERSION_, '1.4.1.0', '>=') && !empty($product->unity) && $product->unit_price_ratio > 0.000000)
			$row['basic_price'] = Tools::displayPrice($product->getPrice(true, null, 2) / $product->unit_price_ratio).' '.$this->shopgateModule->l('per').' '.$product->unity;
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportUseStock($row, $product)
	{
		if (version_compare(_PS_VERSION_, '1.5.0.0', '>='))
		{
			$return = (int)$product->out_of_stock == 2 ? (int)!(bool)Configuration::get('PS_ORDER_OUT_OF_STOCK') : (int)!(bool)$product->out_of_stock;
			$row['use_stock'] = (int)!Configuration::get('PS_STOCK_MANAGEMENT') ? 0 : $return;
		}
		else
		{
			if ($product->out_of_stock == 2)
				$row['use_stock'] = (int)!(bool)Configuration::get('PS_ORDER_OUT_OF_STOCK');
			else
				$row['use_stock'] = (int)!(bool)$product->out_of_stock;
		}
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportStockQuantity($row, $product)
	{
		$row['stock_quantity'] = $product->quantity;
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportActiveStatus($row, $product)
	{
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportMinimumOrderQuantity($row, $product)
	{
		if (version_compare(_PS_VERSION_, '1.4.0.2', '>='))
			$row['minimum_order_quantity'] = $product->minimal_quantity;
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportMaximumOrderQuantity($row, $product)
	{
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportMinimumOrderAmount($row, $product)
	{
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportEan($row, $product)
	{
		$row['ean'] = $product->ean13;
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportIsbn($row, $product)
	{
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportPzn($row, $product)
	{
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportUpc($row, $product)
	{
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportLastUpdate($row, $product)
	{
		$row['last_update'] = Tools::substr($product->date_upd, 0, 10);
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportTags($row, $product)
	{
		$row['tags'] = implode(',', isset($product->tags[$this->id_lang]) ? $product->tags[$this->id_lang] : array());
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportSortOrder($row, $product)
	{
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportIsHighlight($row, $product)
	{
		$highlights = self::getHighlightProducts();
		$isHighlight = in_array($product->id, $highlights) || $product->on_sale;
		$row['is_highlight'] = (int)$isHighlight;

		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportHighlightOrderIndex($row, $product)
	{
		static $highlightIndex = 1;

		$highlights = self::getHighlightProducts();
		$isHighlight = in_array($product->id, $highlights) || $product->on_sale;
		if ($isHighlight)
			$row['highlight_order_index'] = $highlightIndex++;

		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportMarketplace($row, $product)
	{
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportInternalOrderInfo($row, $product)
	{
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportRelatedShopItemNumbers($row, $product)
	{
		$row['related_shop_item_numbers'] = Db::getInstance()->getValue('SELECT GROUP_CONCAT(`id_product_2` SEPARATOR \'||\') FROM `'._DB_PREFIX_.'accessory` WHERE `id_product_1` = '.$product->id.' GROUP BY `id_product_1`');
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportAgeRating($row, $product)
	{
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportWeight($row, $product)
	{
		$row['weight'] = self::convertProductWeightToGrams($product->weight);
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportBlockPricing($row, $product)
	{
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportHasChildren($row, $product)
	{
		$row['has_children'] = $product->hasAttributes() ? 1 : 0;
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportParentItemNumber($row, $product)
	{
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportOptions($row, $product)
	{
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportAttributes($row, $product)
	{
		if ($product->hasAttributes())
		{
			if (version_compare(_PS_VERSION_, '1.5.0.10', '>='))
				$attributes = $product->getAttributeCombinations($this->id_lang);
			else
				$attributes = $product->getAttributeCombinaisons($this->id_lang);

			$combinations = array();
			$attribute_groups = array();
			foreach ($attributes as $a)
			{
				$combinations[$a['id_product_attribute']][$a['id_attribute_group']] = $a;
				$attribute_groups[$a['id_attribute_group']] = $a['group_name'];
			}

			$i = 1;
			foreach ($attribute_groups as $name)
				$row['attribute_'.($i++)] = $name;

			$r = $row;
			foreach ($combinations as $id => $c)
			{
				$combination = current($c);

				if ((version_compare(_PS_VERSION_, '1.4.0.2', '>=') && $product->available_for_order && $combination['quantity'] > 0 || version_compare(_PS_VERSION_, '1.4.0.2', '<')) && $combination['quantity'] > 0)
				{
					$availableText = $product->available_now;
					$r['is_available'] = 1;
				}
				else
				{
					$availableText = $product->available_later;
					$r['is_available'] = 0;
				}

				$r['item_number'] = self::PREFIX.$product->id.'_'.$id;
				$r['has_children'] = 0;
				$r['parent_item_number'] = $row['item_number'];
				$r['urls_images'] = implode('||', $this->getImageUrls($product, $id));
				$reduction = (float)$product->getPrice(true, (int)$id, 2, null, true);
				$r['old_unit_amount'] = Tools::ps_round($reduction != 0 ? $product->getPrice(true, (int)$id, 2, null, false, false) : 0, 2);
				$r['unit_amount'] = Tools::ps_round($product->getPrice(true, (int)$id, 2), 2);

				if (version_compare(_PS_VERSION_, '1.4.1.0', '>=') && !empty($product->unity) && $product->unit_price_ratio > 0.000000)
					$r['basic_price'] = Tools::displayPrice($product->getPrice(true, (int)$id, 2) / $product->unit_price_ratio).' '.$this->shopgateModule->l('per').' '.$product->unity;

				$r['stock_quantity'] = $combination['quantity'];
				$r['ean'] = $combination['ean13'];
				$r['weight'] = $row['weight'] + self::convertProductWeightToGrams($combination['weight']);
				if (version_compare(_PS_VERSION_, '1.4.0.2', '>='))
					$r['minimum_order_quantity'] = $combination['minimal_quantity'];
				$r['available_text'] = $availableText;
				$r['item_number_public'] = (array_key_exists('reference', $combination) && !empty($combination['reference'])) ? $combination['reference'] : '';

				$i = 1;
				foreach ($attribute_groups as $id => $name)
					$r['attribute_'.($i++)] = $c[$id]['attribute_name'];

				$this->addItem($r);
			}
		}
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportInputFields($row, $product)
	{
		//Product customizations
		if ($product->customizable)
		{
			$row['has_input_fields'] = 1;
			$c_fields = $product->getCustomizationFields($this->id_lang);
			$i = 1;
			foreach ($c_fields as $f)
			{
				$row['input_field_'.$i.'_type'] = ($f['type'] == 1) ? 'text' : 'image';
				$row['input_field_'.$i.'_label'] = $f['name'];
				$row['input_field_'.$i.'_required'] = (bool)$f['required'];
				$i++;
			}
		}

		return $row;
	}

	public function createShopInfo()
	{
		$shopInfo = array(
			'category_count' => count(Category::getSimpleCategories($this->id_lang)),
			'item_count' => count(Product::getSimpleProducts($this->id_lang)),
		);

		if($this->config->getEnableGetReviewsCsv())
		{
			$shopInfo['review_count'] = 0;
		}

		if($this->config->getEnableGetMediaCsv())
		{
			$shopInfo['media_count'] = array();
		}

		$shopInfo['plugins_installed'] = array();

		foreach (Module::getModulesOnDisk() as $module)
		{
			array_push($shopInfo['plugins_installed'], array(
				'id' => $module->id,
				'name' => $module->name,
				'version' => $module->version,
				'active' => $module->active ? 1 : 0
			));
		}

		return $shopInfo;
	}

	/**
	 * prepare categories
	 *
	 * @return array
	 */
	protected function prepareCategories()
	{
		$categoryItems = array();
		$result = array();

		$exportRootCategories = Configuration::get('SHOPGATE_EXPORT_ROOT_CATEGORIES') == 1 ? true : false;

		$skippedRootCategories = array();

		foreach (Category::getSimpleCategories($this->id_lang) as $category)
		{
			$cat = $this->buildDefaultCategoryRow();
			/** @var CategoryCore $categoryInfo */
			$categoryInfo = new Category($category['id_category']);
			$categoryLinkRewrite = $categoryInfo->getLinkRewrite($categoryInfo->id_category, $this->id_lang);

			version_compare(_PS_VERSION_, '1.5.0.0', '<')
				? $isRootCategory = ($categoryInfo->id_category == 1 ? true : false)
				: $isRootCategory = $categoryInfo->is_root_category;

			version_compare(_PS_VERSION_, '1.5.0.0', '<')
				? $idShop = false
				: $idShop = $categoryInfo->getShopID();

			if ($isRootCategory && !$exportRootCategories)
			{
				array_push($skippedRootCategories, $categoryInfo->id_category);
				continue;
			}

			/**
			 * fix no parent category available
			 */
			if ($parentCategory = new Category($categoryInfo->id_parent, $this->id_lang, $idShop ? $idShop : false))
			{
				if (!$parentCategory->id_category && !$isRootCategory)
					continue;
			}

			$cat['category_number'] = $categoryInfo->id_category;
			$cat['category_name'] = $categoryInfo->getName($this->id_lang);
			$cat['parent_id'] = $isRootCategory || in_array($categoryInfo->id_category, $skippedRootCategories) ? '' : $categoryInfo->id_parent;
			$cat['is_active'] = $categoryInfo->active;
			$cat['url_deeplink'] =
				$this->context->link->getCategoryLink(
					$categoryInfo->id_category,
					$categoryLinkRewrite,
					$this->id_lang
				);

			$categoryImageUrl = $this->context->link->getCatImageLink(
				$categoryLinkRewrite,
				$categoryInfo->id_category,
				version_compare(_PS_VERSION_, '1.5.0.0', '<')
					? PSShopgatePlugin::PS_CONST_IMAGE_TYPE_LARGE
					: sprintf(PSShopgatePlugin::PS_CONST_IMAGE_TYPE_CATEGORY_DEFAULT, '_')
			);

			version_compare(_PS_VERSION_, '1.5.0.0', '<')
				? $cat['url_image'] = _PS_BASE_URL_.$categoryImageUrl
				: $cat['url_image'] = $categoryImageUrl;

			version_compare(_PS_VERSION_, '1.5.0.0', '>')
				? $cat['order_index'] = $categoryInfo->position
				: $cat['order_index'] = 0;

			array_push($categoryItems, $cat);
		}

		/**
		 * clean root categories
		 */
		if (!$exportRootCategories)
		{
			foreach ($categoryItems as $key => $categoryItem)
			{
				if (in_array($categoryItem['parent_id'], $skippedRootCategories))
					$categoryItems[$key]['parent_id'] = '';
			}
		}

		$categoryPositionData = array();
		foreach ($categoryItems as $categoryItem)
		{
			$key = $categoryItem['parent_id'] == '' ? 'root' : $categoryItem['parent_id'];
			if (!array_key_exists($key, $categoryPositionData))
				$categoryPositionData[$key] = 0;
			else
				$categoryPositionData[$key]++;
		}

		$categoryNewPositionData = array();
		foreach ($categoryItems as $categoryItem)
		{
			$key = $categoryItem['parent_id'] == '' ? 'root' : $categoryItem['parent_id'];
			if (!array_key_exists($key, $categoryNewPositionData))
				$categoryNewPositionData[$key] = 0;
			else
				$categoryNewPositionData[$key]++;
			$categoryItem['order_index'] = $categoryPositionData[$key] - $categoryNewPositionData[$key];
			array_push($result, $categoryItem);
		}

		return $result;
	}

	protected function createCategoriesCsv()
	{
		foreach ($this->prepareCategories() as $categoryItem)
			$this->addCategoryRow($categoryItem);
	}

	protected function createReviewsCsv()
	{
		// TODO: Implement createReviewsCsv() method.
	}

	/**
	 * check cart
	 *
	 * @param ShopgateCart $shopgateCart
	 *
	 * @return array
	 */
	public function checkCart(ShopgateCart $shopgateCart)
	{
		$checkCart = new PSShopgateCheckCart($shopgateCart);
		return $checkCart->createResult();
	}

	/**
	 * @param ShopgateCart $shopgateCart
	 *
	 * @return array
	 */
	public function redeemCoupons(ShopgateCart $shopgateCart)
	{
		//coupon redemption will be done automatically in add_order action 
		$result = array();

		foreach ($shopgateCart->getExternalCoupons() as $coupon)
			$result[] = $coupon;

		return $result;
	}

	/**
	 * set settings
	 *
	 * @return array
	 */
	public function getSettings()
	{
		$result = array();

		/**
		 * customer groups
		 */
		$customerGroupsItems = Group::getGroups(
			$this->id_lang,
			$this->context->shop->id ? $this->context->shop->id : false
		);

		$customerGroups = array();

		if (is_array($customerGroupsItems))
		{
			foreach ($customerGroupsItems as $customerGroupsItem)
			{
				$group = array();
				$group['id'] = $customerGroupsItem['id_group'];
				$group['name'] = $customerGroupsItem['name'];
				$group['is_default'] = false;
				array_push($customerGroups, $group);
			}
		}

		$result['customer_groups'] = $customerGroups;

		/**
		 * product tax
		 */
		$productTaxClassItems = Tax::getTaxes($this->id_lang);
		$productTaxClasses = array();

		if (is_array($productTaxClassItems) && Configuration::get('PS_TAX') == 1)
		{
			foreach ($productTaxClassItems as $productTaxClassItem)
			{
				$taxClass = array();
				$taxClass['id'] = $productTaxClassItem['id_tax'];
				$taxClass['key'] = $productTaxClassItem['name'];
				array_push($productTaxClasses, $taxClass);
			}
		}

		$result['tax']['product_tax_classes'] = $productTaxClasses;

		/**
		 * customer tax classes
		 */
		$result['tax']['customer_tax_classes'] = array();

		/**
		 * tax rates
		 */
		$taxRuleGroups = TaxRulesGroup::getTaxRulesGroups(true);
		$taxRules = array();

		/**
		 * >= 1.5
		 */
		if (version_compare(_PS_VERSION_, '1.5.0.0', '>='))
		{
			foreach ($taxRuleGroups as $taxRuleGroup)
			{
				foreach (TaxRule::getTaxRulesByGroupId($this->id_lang, $taxRuleGroup['id_tax_rules_group']) as $taxRuleItem)
				{

					/** @var TaxRuleCore $taxRuleItem */
					$taxRuleItem = new TaxRule($taxRuleItem['id_tax_rule']);

					/** @var TaxCore $taxItem */
					$taxItem = new Tax($taxRuleItem->id_tax, $this->id_lang);

					$country = $this->getCountryById($taxRuleItem->id_country)->iso_code;
					$state = $this->getStateById($taxRuleItem->id_state)->iso_code;

					$resultTaxRule = array();
					$resultTaxRule['id'] = $taxRuleItem->id_tax_rules_group;

					if ($state)
						$resultTaxRule['key'] = $taxItem->name."-".$country."-".$state;
					else
						$resultTaxRule['key'] = $taxItem->name."-".$country;

					$resultTaxRule['key'] .= "-".$taxRuleItem->id_tax_rules_group;

					$resultTaxRule['display_name'] = $taxItem->name;
					$resultTaxRule['tax_percent'] = $taxItem->rate;
					$resultTaxRule['country'] = $country;
					$resultTaxRule['state'] = (!empty($state)) ? $country."-".$state : null;

					$resultTaxRule['zipcode_type'] = 'range';
					$resultTaxRule['zipcode_range_from'] = $taxRuleItem->zipcode_from ? $taxRuleItem->zipcode_from : null;
					$resultTaxRule['zipcode_range_to'] = $taxRuleItem->zipcode_to ? $taxRuleItem->zipcode_to : null;

					if($taxItem->active && Configuration::get('PS_TAX') == 1)
						array_push($taxRules, $resultTaxRule);
				}
			}
		}
		else
		{
			foreach ($taxRuleGroups as $taxRuleGroup)
			{
				foreach ($this->getTaxRulesByGroupId($taxRuleGroup['id_tax_rules_group']) as $taxRuleItem) {

					/** @var TaxCore $taxItem */
					$taxItem = new Tax($taxRuleItem['id_tax'], $this->id_lang);
					$country = $this->getCountryById($taxRuleItem['id_country'])->iso_code;
					$state = $this->getStateById($taxRuleItem['id_state'])->iso_code;
					$resultTaxRule = array();
					$resultTaxRule['id'] = $taxRuleItem['id_tax_rule'];
					if ($state)
						$resultTaxRule['key'] = $taxItem->name."-".$country."-".$state;
					else
						$resultTaxRule['key'] = $taxItem->name."-".$country;
					$resultTaxRule['key'] .= "-".$taxRuleItem['id_tax_rule'];

					$resultTaxRule['display_name'] = $taxItem->name;
					$resultTaxRule['tax_percent'] = $taxItem->rate;
					$resultTaxRule['country'] = $country;
					$resultTaxRule['state'] = (!empty($state)) ? $country."-".$state : null;

					if($taxItem->active && Configuration::get('PS_TAX') == 1)
						array_push($taxRules, $resultTaxRule);
				}
			}
		}


		$result['tax']['tax_rates'] = $taxRules;

		/**
		 * tax rules
		 */
		$result['tax']['tax_rules'] = array();
		$taxRuleGroups = TaxRulesGroup::getTaxRulesGroups(true);
		if (version_compare(_PS_VERSION_, '1.5.0.0', '>='))
		{
			foreach ($taxRuleGroups as $taxRuleGroup)
			{

				$rule = array(
					'id' => $taxRuleGroup['id_tax_rules_group'],
					'name' => $taxRuleGroup['name'],
					'priority' => 0
				);

				$rule['product_tax_classes'] = array(
					'id' => $taxRuleGroup['id_tax_rules_group'],
					'key' => $taxRuleGroup['name']
				);
				$rule['customer_tax_classes'] = array();
				$rule['tax_rates'] = array();

				foreach ($this->getTaxRulesByGroupId($taxRuleGroup['id_tax_rules_group']) as $taxRuleItem)
				{
					/** @var TaxCore $taxItem */
					$taxItem = new Tax($taxRuleItem['id_tax'], $this->id_lang);
					$country = $this->getCountryById($taxRuleItem['id_country'])->iso_code;
					$state = $this->getStateById($taxRuleItem['id_state'])->iso_code;
					$resultTaxRule = array();
					$resultTaxRule['id'] = $taxRuleItem['id_tax_rule'];
					if ($state)
						$resultTaxRule['key'] = $taxItem->name."-".$country."-".$state;
					else
						$resultTaxRule['key'] = $taxItem->name."-".$country;
					$resultTaxRule['key'] .= "-".$taxRuleItem['id_tax_rule'];
					array_push($rule['tax_rates'], $resultTaxRule);
				}

				if($taxItem->active && Configuration::get('PS_TAX') == 1)
					array_push($result['tax']['tax_rules'], $rule);
			}

		}
		else
		{
			foreach ($taxRuleGroups as $taxRuleGroup)
			{
				$rule = array(
					'id' => $taxRuleGroup['id_tax_rules_group'],
					'name' => $taxRuleGroup['name'],
					'priority' => 0
				);

				$rule['product_tax_classes'] = array(
					'id' => $taxRuleGroup['id_tax_rules_group'],
					'key' => $taxRuleGroup['name']
				);
				$rule['customer_tax_classes'] = array();
				$rule['tax_rates'] = array();
				foreach ($this->getTaxRulesByGroupId($taxRuleGroup['id_tax_rules_group']) as $taxRuleItem)
				{
					/** @var TaxCore $taxItem */
					$taxItem = new Tax($taxRuleItem['id_tax'], $this->id_lang);
					$country = $this->getCountryById($taxRuleItem['id_country'])->iso_code;
					$state = $this->getStateById($taxRuleItem['id_state'])->iso_code;
					$resultTaxRule = array();
					$resultTaxRule['id'] = $taxRuleItem['id_tax_rule'];
					if ($state)
						$resultTaxRule['key'] = $taxItem->name."-".$country."-".$state;
					else
						$resultTaxRule['key'] = $taxItem->name."-".$country;
					$resultTaxRule['key'] .= "-".$taxRuleItem['id_tax_rule'];
					array_push($rule['tax_rates'], $resultTaxRule);
				}

				if($taxItem->active && Configuration::get('PS_TAX') == 1)
					array_push($result['tax']['tax_rules'], $rule);
			}
		}

		return $result;
	}

	/**
	 * @param $id_group
	 *
	 * @return array
	 */
	protected function getTaxRulesByGroupId($id_group)
	{
		if (empty($id_group))
			die(Tools::displayError());

		return Db::getInstance()->ExecuteS('
        SELECT *
        FROM `'._DB_PREFIX_.'tax_rule`
        WHERE `id_tax_rules_group` = '.(int)$id_group);
	}

	/**
	 * @param int $id
	 *
	 * @return CountryCore
	 */
	protected function getCountryById($id)
	{
		return new Country($id, $this->id_lang);
	}

	/**
	 * @param int $id
	 *
	 * @return StateCore
	 */
	protected function getStateById($id)
	{
		return new State($id);
	}

	/**
	 * @param string           $user
	 * @param string           $pass
	 * @param ShopgateCustomer $customer
	 *
	 * @throws ShopgateLibraryException
	 */
	public function registerCustomer($user, $pass, ShopgateCustomer $customer)
	{
		/** @var CustomerCore | Customer $customerModel */
		$customerModel = new Customer();
		if($customerModel->getByEmail($user))
			throw new ShopgateLibraryException(ShopgateLibraryException::REGISTER_USER_ALREADY_EXISTS);

		$gender = array(
			'm' => 1,
			'f' => 2
		);

		$customerModel->active = 1;
		$customerModel->lastname = $customer->getLastName();
		$customerModel->firstname = $customer->getFirstName();
		$customerModel->email = $user;
		$customerModel->passwd = Tools::encrypt($pass);
		$customerModel->id_gender = array_key_exists($customer->getGender(), $gender) ? $gender[$customer->getGender()] : false;
		$customerModel->birthday = $customer->getBirthday();
		$customerModel->newsletter = $customer->getNewsletterSubscription();

		$validateMessage = $customerModel->validateFields(false, true);

		if($validateMessage !== true)
			throw new ShopgateLibraryException(ShopgateLibraryException::REGISTER_FAILED_TO_ADD_USER, $validateMessage, true);

		$customerModel->save();

		/**
		 * addresses
		 */
		foreach ($customer->getAddresses() as $address) {
			$this->createAddress($address, $customerModel);
		}
	}

	/**
	 * @param ShopgateAddress $address
	 * @param CustomerCore $customer
	 *
	 * @throws ShopgateLibraryException
	 */
	protected function createAddress($address, $customer)
	{
		$shopgateShopgate = $this->shopgateModule;

		/** @var AddressCore | Address $addressModel */
		$addressModel = new Address();

		$addressModel->id_customer = $customer->id;
		$addressModel->lastname = $address->getLastName();
		$addressModel->firstname = $address->getFirstName();

		if($address->getCompany())
			$addressModel->company = $address->getCompany();

		$addressModel->address1 = $address->getStreet1();

		if($address->getStreet2())
			$addressModel->address2 = $address->getStreet2();

		$addressModel->city = $address->getCity();
		$addressModel->postcode = $address->getZipcode();

		if(!Validate::isLanguageIsoCode($address->getCountry()))
		{
			$customer->delete();
			throw new ShopgateLibraryException(ShopgateLibraryException::REGISTER_FAILED_TO_ADD_USER, 'invalid country code: ' . $address->getCountry(), true);
		}

		$addressModel->id_country =  Country::getByIso($address->getCountry());

		if($address->getState() && !Validate::isStateIsoCode($address->getState()))
		{
			$customer->delete();
			throw new ShopgateLibraryException(ShopgateLibraryException::REGISTER_FAILED_TO_ADD_USER, 'invalid state code: ' . $address->getState(), true);
		}
		else
		{
			$addressModel->id_state = self::stateGetIdByIso($address->getState());
		}

		$addressModel->alias = $address->getIsDeliveryAddress() ? $shopgateShopgate->l('Default delivery address') : $shopgateShopgate->l('Default');
		$addressModel->alias = $address->getIsInvoiceAddress() ? $shopgateShopgate->l('Default invoice address') : $shopgateShopgate->l('Default');

		$addressModel->phone = $address->getPhone();
		$addressModel->phone_mobile = $address->getMobile();

		$validateMessage = $addressModel->validateFields(false, true);

		if($validateMessage !== true)
		{
			$customer->delete();
			throw new ShopgateLibraryException(ShopgateLibraryException::REGISTER_FAILED_TO_ADD_USER, $validateMessage, true);
		}

		$addressModel->save();
	}

	/**
	 * @param $iso_code
	 * @param null $id_country
	 *
	 * @return mixed
	 */
	public static function stateGetIdByIso($iso_code, $id_country = null)
	{
		return Db::getInstance()->getValue('
		SELECT `id_state`
		FROM `'._DB_PREFIX_.'state`
		WHERE `iso_code` = \''.pSQL($iso_code).'\'
		'.($id_country ? 'AND `id_country` = '.(int)$id_country : ''));
	}

	/**
	 * @return ShopgateMobileRedirect
	 */
	public function getRedirect()
	{
		return $this->builder->buildRedirect();
	}

	/**
	 * Checks the items array and returns stock quantity for each item.
	 *
	 *
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_check_cart#API_Response
	 *
	 * @param ShopgateCart $cart The ShopgateCart object to be checked and validated.
	 *
	 * @return array(
	 *          'items' => array(...), # list of item changes
	 * )
	 * @throws ShopgateLibraryException if an error occurs.
	 */
	public function checkStock(ShopgateCart $cart)
	{
		// TODO: Implement checkStock() method.
	}

	/**
	 * Loads the Media file information to the products of the shop system's database and passes them to the buffer.
	 *
	 * Use ShopgatePlugin::buildDefaultMediaRow() to get the correct indices for the field names in a Shopgate media csv and
	 * use ShopgatePlugin::addMediaRow() to add it to the output buffer.
	 *
	 * @see http://wiki.shopgate.com/CSV_File_Media#Sample_Media_CSV_file
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_media_csv
	 *
	 * @throws ShopgateLibraryException
	 */
	protected function createMediaCsv()
	{
		// TODO: Implement createMediaCsv() method.
	}

	/**
	 * @param ProductCore $product
	 * @param int $combination
	 *
	 * @return array
	 */
	protected function getImageUrls($product, $combination)
	{
		$imageUrls = array();
		$images = $product->getCombinationImages($this->id_lang);
		if (isset($images[$combination]) && is_array($images[$combination]))
		{
			foreach ($images[$combination] as $i)
			{
				if (version_compare(_PS_VERSION_, '1.5.0.0', '>='))
					array_push($imageUrls, $this->context->link->getImageLink($product->link_rewrite, $product->id.'-'.$i['id_image']));
				elseif (version_compare(_PS_VERSION_, '1.5.0.0', '<') && version_compare(_PS_VERSION_, '1.4.0.0', '>='))
				{
					// lower than 1.5.0.0 higher than 1.4.0.0
					array_push($imageUrls, $this->context->link->getImageLink($product->link_rewrite, $product->id.'-'.$i['id_image']));
				}
				else
				{
					// lower then 1.4.0.0
					array_push($imageUrls, _PS_BASE_URL_.$this->context->link->getImageLink($product->link_rewrite, $product->id.'-'.$i['id_image']));
				}
			}
		}
		return $imageUrls;
	}

	/**
	 * Loads the products of the shop system's database and passes them to the buffer.
	 *
	 * @param int $limit     pagination limit; if not null, the number of exported items must be <= $limit
	 * @param int $offset    pagination; if not null, start the export with the item at position $offset
	 * @param string[] $uids a list of item UIDs that should be exported
	 *
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_items
	 *
	 * @throws ShopgateLibraryException
	 */
	protected function createItems($limit = null, $offset = null, array $uids = array())
	{
		$products = Product::getProducts($this->id_lang, $offset, $limit, 'id_product', 'DESC', false, true);

		foreach ($products as $product)
		{

			if (count($uids) > 0 && !in_array($product['id_product'], $uids))
				continue;

			/** @var ProductCore $productModel */
			$productModel = new Product($product['id_product'], true, $this->id_lang);
			if (version_compare(_PS_VERSION_, '1.5.0.0', '>='))
				$productModel->tax_name = $product['tax_name'];
			else
				$productModel->tax_name = $this->getTaxClassFromDb($product);

			$row = new PluginModelItemObject($this->context);
			$this->addItemModel($row->setItem($productModel)->generateData());
		}
	}

	/**
	 * returns the current tax class
	 *
	 * @return mixed
	 */
	protected function getTaxClassFromDb($product)
	{
		$select = sprintf(
			'SELECT name from %stax_lang WHERE id_tax = %s AND id_lang = %s',
			_DB_PREFIX_,
			$product['id_tax'],
			$this->id_lang
		);

		return Db::getInstance()->getValue($select);
	}

	/**
	 * Loads the product categories of the shop system's database and passes them to the buffer.
	 *
	 * @param int $limit     pagination limit; if not null, the number of exported categories must be <= $limit
	 * @param int $offset    pagination; if not null, start the export with the categories at position $offset
	 * @param string[] $uids a list of categories UIDs that should be exported
	 *
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_categories
	 *
	 * @throws ShopgateLibraryException
	 */
	protected function createCategories($limit = null, $offset = null, array $uids = array())
	{
		foreach ($this->prepareCategories() as $categoryItem)
		{
			if (count($uids) > 0 && !in_array($categoryItem['id_category'], $uids))
				continue;
			$row = new PluginModelCategoryObject();
			$this->addCategoryModel($row->setItem($categoryItem)->generateData());
		}
	}
}

class PSShopgatePluginUS extends PSShopgatePlugin
{
	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportBasicPrice($row, $product)
	{
		if (version_compare(_PS_VERSION_, '1.4.1.0', '>=') && !empty($product->unity) && $product->unit_price_ratio > 0.000000)
		{
			//TODO:? getPrice(false...) then we get only the net amount
			$row['basic_price'] = Tools::displayPrice($product->getPrice(true, null, 2) / $product->unit_price_ratio).' '.$this->shopgateModule->l('per').' '.$product->unity;
		}
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportTaxClass($row, $product)
	{
		$p = array();
		$p['tax_class_active'] = $product->tax_class_active;
		$p['tax_class_name'] = $product->tax_class_name;
		$p['tax_class_id'] = $product->tax_class_id;
		if (!empty($p['tax_class_active']) && !empty($p['tax_class_name']) && !empty($p['tax_class_id']))
			$row['tax_class'] = $p['tax_class_id'].'=>'.$p['tax_class_name'];

		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportUnitAmountNet($row, $product)
	{
		$row['unit_amount_net'] = $product->getPrice(false, null, 2);
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportOldUnitAmountNet($row, $product)
	{
		$reduction = (float)$product->getPrice(false, null, 2, null, true);
		$row['old_unit_amount'] = Tools::ps_round($reduction != 0 ? $product->getPrice(false, null, 2, null, false, false) : 0, 2);
		return $row;
	}

	/**
	 * @param $row
	 * @param ProductCore $product
	 *
	 * @return mixed
	 */
	protected function itemExportAttributes($row, $product)
	{
		if ($product->hasAttributes())
		{
			if (version_compare(_PS_VERSION_, '1.5.0.10', '>='))
				$attributes = $product->getAttributeCombinations($this->id_lang);
			else
				$attributes = $product->getAttributeCombinaisons($this->id_lang);

			$combinations = array();
			$attribute_groups = array();
			foreach ($attributes as $a)
			{
				$combinations[$a['id_product_attribute']][$a['id_attribute_group']] = $a;
				$attribute_groups[$a['id_attribute_group']] = $a['group_name'];
			}

			$i = 1;
			foreach ($attribute_groups as $name)
				$row['attribute_'.($i++)] = $name;

			$r = $row;
			foreach ($combinations as $id => $c)
			{
				$combination = current($c);

				if ((version_compare(_PS_VERSION_, '1.4.0.2', '>=') && $product->available_for_order && $combination['quantity'] > 0 || version_compare(_PS_VERSION_, '1.4.0.2', '<')) && $combination['quantity'] > 0)
				{
					$availableText = $product->available_now;
					$r['is_available'] = 1;
				}
				else
				{
					$availableText = $product->available_later;
					$r['is_available'] = 0;
				}

				$r['item_number'] = self::PREFIX.$product->id.'_'.$id;
				$r['has_children'] = 0;
				$r['parent_item_number'] = $row['item_number'];
				$r['urls_images'] = implode('||', $this->getImageUrls($product, $id));
				$reduction = (float)$product->getPrice(false, (int)$id, 2, null, true);
				$r['old_unit_amount_net'] = Tools::ps_round($reduction != 0 ? $product->getPrice(false, (int)$id, 2, null, false, false) : 0, 2);
				$r['unit_amount_net'] = Tools::ps_round($product->getPrice(false, (int)$id, 2), 2);

				if (version_compare(_PS_VERSION_, '1.4.1.0', '>=') && !empty($product->unity) && $product->unit_price_ratio > 0.000000)
					$r['basic_price'] = Tools::displayPrice($product->getPrice(false, (int)$id, 2) / $product->unit_price_ratio).' '.$this->shopgateModule->l('per').' '.$product->unity;

				$r['stock_quantity'] = $combination['quantity'];
				$r['ean'] = $combination['ean13'];
				$r['weight'] = $row['weight'] + self::convertProductWeightToGrams($combination['weight']);
				if (version_compare(_PS_VERSION_, '1.4.0.2', '>='))
					$r['minimum_order_quantity'] = $combination['minimal_quantity'];

				$r['available_text'] = $availableText;
				$r['item_number_public'] = (array_key_exists('reference', $combination) && !empty($combination['reference'])) ? $combination['reference'] : '';

				$i = 1;
				foreach ($attribute_groups as $id => $name)
					$r['attribute_'.($i++)] = $c[$id]['attribute_name'];

				$this->addItem($r);
			}
		}
		return $row;
	}

	/**
	 * @return array|mixed[]
	 */
	public function createPluginInfo()
	{
		return array(
			'PS Version' => _PS_VERSION_,
			'Plugin' => 'US'
		);
	}

	/**
	 * @return array|string[]
	 */
	protected function buildDefaultItemRow()
	{
		$row = parent::buildDefaultItemRow();

		// remove old fields
		unset($row['unit_amount']);
		unset($row['old_unit_amount']);
		unset($row['tax_percent']);

		$newFields = array(
			'tax_class' => '', /** $this->itemExportTaxClass */
			'unit_amount_net' => '0', /** $this->itemExportUnitAmountNet */
			'old_unit_amount_net' => '',/** $this->itemExportOldUnitAmountNet */
		);

		$row = array_slice($row, 0, 3, true) +
			$newFields +
			array_slice($row, 3, count($row) - 3, true);

		return $row;
	}
}