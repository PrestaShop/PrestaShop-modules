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
 *
 * User: awesselburg
 * Date: 28.01.14
 * Time: 10:21
 *
 * File: PSShopgateCheckCart.php
 */

class PSShopgateCheckCart
{

	/**
	 * default dummy first name
	 */
	const DEFAULT_CUSTOMER_FIRST_NAME = 'shopgate';

	/**
	 * default dummy last name
	 */
	const DEFAULT_CUSTOMER_LAST_NAME = 'shopgate';

	/**
	 * default dummy email
	 */
	const DEFAULT_CUSTOMER_EMAIL = 'example@shopgate.com';

	/**
	 * default dummy password
	 */
	const DEFAULT_CUSTOMER_PASSWD = '123shopgate';

	/**
	 * default dummy alias
	 */
	const DEFAULT_ADDRESS_ALIAS = 'shopgate_check_cart';

	/** @var \Context Context */
	protected $_context;

	/**
	 * @var ShopgateCart
	 */
	protected $_shopgateCart;

	/**
	 * @var AddressCore
	 */
	protected $_deliveryAddress;

	/**
	 * @var AddressCore
	 */
	protected $_invoiceAddress;

	/**
	 * @var array
	 */
	protected $_externalCoupons = array ();

	/**
	 * @var array
	 */
	protected $_resultItems = array ();

	/**
	 * @var array
	 */
	protected $_resultCarriers = array ();

	/**
	 * @var array
	 */
	protected $_resultPayments = array ();

	/**
	 * @var array
	 */
	protected $_resultExternalCoupons = array ();

	/**
	 * @var int
	 */
	protected $_itemCurrencyId = 1;

	/**
	 * @var null
	 */
	protected $_currentCurrency = null;

	/**
	 * @var bool
	 */
	protected $_customerDummyCreated = false;

	/**
	 * @param ShopgateCart $shopgateCart
	 */
	public function __construct(ShopgateCart $shopgateCart)
	{
		$this->_context      = Context::getContext();
		$this->_shopgateCart = $shopgateCart;
	}

	/**
	 * creates and fills the cart
	 */
	protected function _run()
	{
		/**
		 * set item currency id
		 */
		$this->_setItemCurrencyId();

		/**
		 * create cart
		 */
		$this->_createCart();

		/**
		 * create dummy customer
		 */
		$this->_createCustomer();

		/**
		 * create delivery address
		 */
		$this->_createDeliveryAddress();

		/**
		 * create invoice address
		 */
		$this->_createInvoiceAddress();

		/**
		 * validate cart items
		 */
		$this->_validateCartItems();

		/**
		 * create carriers
		 */
		$this->_createCarriers();

		/**
		 * create payments
		 */
		$this->_createPayments();

		/**
		 * create external coupons
		 */
		$this->_createExternalCoupons();
	}

	/**
	 * set currency for items
	 */
	protected function _setItemCurrencyId()
	{
		$this->_itemCurrencyId  = $this->_context->cart->id_currency;
		$this->_currentCurrency = $this->_context->currency->iso_code;
	}

	/**
	 * create external coupon
	 */
	protected function _createExternalCoupons()
	{
		foreach ($this->_shopgateCart->getExternalCoupons() as $coupon)
		{

			$resultExternalCouponItem = $this->_createResultExternalCoupon($coupon);

			/**
			 * load coupon
			 */
			$cartRule = new CartRule(CartRule::getIdByCode($coupon->getCode()));

			/** @var CartRuleCore $cartRule */
			if ($cartRule && Validate::isLoadedObject($cartRule))
			{

				/**
				 * set defaults
				 */
				$resultExternalCouponItem->setName($cartRule->getFieldByLang('name'), $this->_context->language->id);
				$resultExternalCouponItem->setDescription($cartRule->description);

				switch ($cartRule->reduction_tax)
				{

					case 1 :
						$resultExternalCouponItem->setTaxType(TranslateCore::getAdminTranslation('not_taxable'));
						$resultExternalCouponItem->setAmountNet($cartRule->getContextualValue(false, $this->_context));
						break;

					case 0 :
						$resultExternalCouponItem->setTaxType(TranslateCore::getAdminTranslation('auto'));
						$resultExternalCouponItem->setAmountGross($cartRule->getContextualValue(true, $this->_context));
						break;
				}

				$resultExternalCouponItem->setIsFreeShipping((bool)$cartRule->free_shipping);

				/**
				 * validate coupon
				 */
				if ($validateException = $cartRule->checkValidity($this->_context, false, true))
				{
					$resultExternalCouponItem->setIsValid(false);
					$resultExternalCouponItem->setNotValidMessage($validateException);
				}

			}
			else
			{

				/**
				 * invalid code
				 */
				$resultExternalCouponItem->setIsValid(false);
				$resultExternalCouponItem->setNotValidMessage(Tools::displayError('This voucher does not exists.'));
			}

			array_push($this->_resultExternalCoupons, $resultExternalCouponItem);
		}
	}

	/**
	 * create delivery address
	 */
	protected function _createDeliveryAddress()
	{
		if ($this->_shopgateCart->getDeliveryAddress())
		{

			$this->_deliveryAddress              = $this->_createAddress($this->_shopgateCart->getDeliveryAddress());
			$this->_deliveryAddress->id_customer = $this->_context->customer->id;

			try
			{
				$this->_deliveryAddress->save();
			} catch(Exception $e)
			{
				$this->_addException(ShopgateLibraryException::UNKNOWN_ERROR_CODE, '_createDeliveryAddress : '.$e->getMessage());
			}

			/**
			 * add delivery address id to cart
			 */
			$this->_context->cart->id_address_delivery = $this->_deliveryAddress->id;
			$this->_context->cart->save();
		}
	}

	/**
	 * create invoice address
	 */
	protected function _createInvoiceAddress()
	{
		if ($this->_shopgateCart->getInvoiceAddress())
		{

			$this->_invoiceAddress              = $this->_createAddress($this->_shopgateCart->getInvoiceAddress());
			$this->_invoiceAddress->id_customer = $this->_context->customer->id;

			try
			{
				$this->_invoiceAddress->save();
			} catch(Exception $e)
			{
				$this->_addException(ShopgateLibraryException::UNKNOWN_ERROR_CODE, '_createInvoiceAddress : '.$e->getMessage());
			}

			/**
			 * add invoice address id to cart
			 */
			$this->_context->cart->id_address_invoice = $this->_invoiceAddress->id;
			$this->_context->cart->save();
		}
	}

	/**
	 * create dummy customer
	 */
	protected function _createCustomer()
	{
		if ($this->_shopgateCart->getExternalCustomerId())
		{
			/**
			 * load exist customer
			 */
			$this->_context->customer = new Customer($this->_shopgateCart->getExternalCustomerId());
			if (!Validate::isLoadedObject($this->_context->customer))
			{
				/**
				 * _addException
				 */
				$this->_addException(ShopgateLibraryException::COUPON_INVALID_USER);
			}
		}
		else
		{
			/**
			 * create dummy customer
			 */
			$this->_context->customer            = new Customer();
			$this->_context->customer->lastname  = self::DEFAULT_CUSTOMER_LAST_NAME;
			$this->_context->customer->firstname = self::DEFAULT_CUSTOMER_FIRST_NAME;
			$this->_context->customer->email     = self::DEFAULT_CUSTOMER_EMAIL;
			$this->_context->customer->passwd    = self::DEFAULT_CUSTOMER_PASSWD;
			$this->_context->customer->add();
			$this->_customerDummyCreated = true;
		}

		$this->_context->cart->id_customer = $this->_context->customer->id;
		$this->_context->cart->save();
	}

	/**
	 * create carriers
	 */
	protected function _createCarriers()
	{
		if ($this->_deliveryAddress)
		{
			/** @var CarrierCore $carrierModel */
			$carrierModel = new Carrier();
			foreach ($carrierModel->getCarriersForOrder(Address::getZoneById($this->_deliveryAddress->id), null, $this->_context->cart) as $carrier)
				array_push($this->_resultCarriers, $this->_createResultCarrier($carrier));
		}
	}

	/**
	 * create payments
	 */
	protected function _createPayments()
	{
		foreach (PaymentModule::getPaymentModules() as $payment)
			array_push($this->_resultPayments, $this->_createResultPayment($payment));
	}

	/**
	 * add a item exception
	 *
	 * @param ShopgateCartItem $item
	 * @param                  $code
	 * @param                  $message
	 */
	protected function _addItemException(ShopgateCartItem $item, $code, $message)
	{
		$item->setError($code);
		$item->setErrorText($message);
	}

	/**
	 * @param ShopgateOrderItem $item
	 *
	 * @return array
	 */
	protected function getProductIdentifiers(ShopgateOrderItem $item)
	{
		return Tools::substr($item->getItemNumber(), 0, 2) == PSShopgatePlugin::PREFIX
			? explode('_', Tools::substr($item->getItemNumber(), Tools::strlen(PSShopgatePlugin::PREFIX)))
			: explode('_', $item->getItemNumber());
	}

	/**
	 * validate cart items
	 */
	protected function _validateCartItems()
	{
		/** @var ShopgateOrderItem $sGItem */

		foreach ($this->_shopgateCart->getItems() as $sGItem)
		{
			$identifiers = $this->getProductIdentifiers($sGItem);

			$productId   = false;
			$attributeId = null;

			if (is_array($identifiers))
			{
				$productId   = array_key_exists(0, $identifiers) ? $identifiers[0] : false;
				$attributeId = array_key_exists(1, $identifiers) ? $identifiers[1] : null;
			}

			/**
			 * load the product
			 */
			/** @var ProductCore $pSProduct */
			$pSProduct = new Product($productId, true, $this->_context->language->id);

			/**
			 * validate attribute id
			 */
			$attributeId = $attributeId ? $this->_validateAttributeId($productId, $attributeId) : null;

			/** @var ShopgateCartItem $_resultItem */
			$resultItem = $this->_createResultCartItem($pSProduct, $sGItem, $attributeId);

			if ($attributeId === false)
			{
				$this->_addItemException($resultItem, ShopgateLibraryException::UNKNOWN_ERROR_CODE, 'attribute ID not available');
				$resultItem->setIsBuyable(0);
				array_push($this->_resultItems, $resultItem);
				continue;
			}

			if ($pSProduct->id)
			{
				$resultItem->setIsBuyable((int)$this->_context->cart->updateQty($sGItem->getQuantity(), $pSProduct->id, $attributeId == 0 ? false : $attributeId, false, 'up', ($this->_deliveryAddress && $this->_deliveryAddress->id) ? $this->_deliveryAddress->id : 0));

				switch ($resultItem->getIsBuyable())
				{
					/**
					 * requested qty lower than required minimum qty
					 */
					case -1 :
						$resultItem->setIsBuyable(0);
						$resultItem->setQtyBuyable(Attribute::getAttributeMinimalQty($attributeId));
						$this->_addItemException(
							$resultItem, ShopgateLibraryException::CART_ITEM_REQUESTED_QUANTITY_UNDER_MINIMUM_QUANTITY,
							'requested quantity is lower than required minimum quantity');
						break;

					/**
					 * item available
					 */
					case 1 :
						$resultItem->setIsBuyable(1);
						$resultItem->setQtyBuyable($sGItem->getQuantity());
						break;

					/**
					 * requested quantity is not available
					 */
					default :
						$resultItem->setIsBuyable(0);
						$resultItem->setQtyBuyable($pSProduct->getQuantity($pSProduct->id, $attributeId));
						$this->_addItemException(
							$resultItem,
							ShopgateLibraryException::CART_ITEM_REQUESTED_QUANTITY_NOT_AVAILABLE, 'requested quantity is not available');
						break;
				}
			}
			else
			{
				/**
				 * product not available
				 */
				$resultItem->setIsBuyable(0);
				$this->_addItemException($resultItem, ShopgateLibraryException::CART_ITEM_PRODUCT_NOT_FOUND, 'product not available');
			}

			array_push($this->_resultItems, $resultItem);
		}

	}

	/**
	 * create empty cart
	 */
	protected function _createCart()
	{
		$this->_context->cart = new Cart();

		$this->_context->cart->id_currency = $this->_itemCurrencyId;
		$this->_context->cart->id_lang     = $this->_context->language->id;

		$this->_context->cart->save();
	}

	/**
	 * create result cart items
	 *
	 * @param Product           $product
	 * @param ShopgateOrderItem $sGItem
	 * @param bool              $id_product_attribute
	 *
	 * @return ShopgateCartItem
	 */
	protected function _createResultCartItem(Product $product, ShopgateOrderItem $sGItem, $id_product_attribute = false)
	{
		$resultItem = new ShopgateCartItem();

		$availableQty = $product->getQuantity($product->id, $id_product_attribute);

		$resultItem->setItemNumber($sGItem->getItemNumber());
		$resultItem->setQtyBuyable($availableQty);

		$resultItem->setUnitAmount($product->getPrice(false, $id_product_attribute));
		$resultItem->setUnitAmountWithTax($product->getPrice(true, $id_product_attribute));

		$resultItem->setOptions($sGItem->getOptions());
		$resultItem->setAttributes($sGItem->getAttributes());
		$resultItem->setInputs($sGItem->getInputs());

		return $resultItem;
	}

	/**
	 * create payment
	 *
	 * @param array $payment
	 *
	 * @return stdClass
	 */
	protected function _createResultPayment(array $payment)
	{
		$_resultPayment = new ShopgatePaymentMethod();

		$_resultPayment->setId($payment['name']);
		$_resultPayment->setAmount(0.00);
		$_resultPayment->setAmountWithTax(0.00);

		return $_resultPayment;
	}

	/**
	 * create coupon
	 *
	 * @param ShopgateExternalCoupon $coupon
	 *
	 * @return ShopgateExternalCoupon
	 */
	protected function _createResultExternalCoupon(ShopgateExternalCoupon $coupon)
	{
		$_resultExternalCoupon = new ShopgateExternalCoupon();

		$_resultExternalCoupon->setIsValid(true);
		$_resultExternalCoupon->setCode($coupon->getCode());

		return $_resultExternalCoupon;
	}

	/**
	 * create carrier
	 *
	 * @param array $carrier
	 *
	 * @return stdClass
	 */
	protected function _createResultCarrier(array $carrier)
	{
		/** @var CarrierCore $_carrier */
		$_carrier = new Carrier($carrier['id_carrier']);

		/** @var TaxRulesGroupCore $tx */
		$_taxRulesGroup = new TaxRulesGroup($_carrier->id_tax_rules_group);

		$resultCarrier = new ShopgateShippingMethod();

		$resultCarrier->setId($carrier['id_carrier']);
		$resultCarrier->setTitle($carrier['name']);
		$shippinggroupMapping = Configuration::get('SHOPGATE_CARRIER_MAPPING_'.$carrier['id_carrier']);
		if ($shippinggroupMapping)
			$resultCarrier->setShippingGroup($shippinggroupMapping);
		$resultCarrier->setDescription($carrier['delay']);
		$resultCarrier->setSortOrder($carrier['position']);
		$resultCarrier->setAmount($carrier['price_tax_exc']);
		$resultCarrier->setAmountWithTax($carrier['price']);
		$resultCarrier->setTaxClass($_taxRulesGroup->name);
		$resultCarrier->setTaxPercent($_carrier->getTaxesRate($this->_deliveryAddress));
		$resultCarrier->setInternalShippingInfo(serialize(array ('carrierId' => $carrier['id_carrier'])));

		return $resultCarrier;

	}

	/**
	 * create the result
	 *
	 * @return array
	 * @throws Exception
	 */
	public function createResult()
	{
		try
		{
			$this->_run();
		} catch(Exception $e)
		{
			$this->__destruct();
			throw $e;
		}

		return array (
			'items'            => (array)$this->_resultItems,
			'shipping_methods' => (array)$this->_resultCarriers,
			'payment_methods'  => (array)$this->_resultPayments,
			'external_coupons' => (array)$this->_resultExternalCoupons,
			'currency'         => $this->_currentCurrency);
	}

	/**
	 * validate the attribute id
	 *
	 * @param $productId
	 * @param $attributeId
	 *
	 * @return bool
	 */
	protected function _validateAttributeId($productId, $attributeId)
	{
		if ($this->_inArrayR($attributeId, Product::getProductAttributesIds($productId)))
			return $attributeId;

		return false;

	}

	/**
	 * returns a new address
	 *
	 * @param ShopgateAddress $address
	 *
	 * @return AddressCore
	 */
	protected function _createAddress(ShopgateAddress $address)
	{
		/** @var AddressCore $_resultAddress */
		$_resultAddress = new Address();

		$_resultAddress->id_country = $this->_getCountryIdByIsoCode($address->getCountry());
		$_resultAddress->alias      = self::DEFAULT_ADDRESS_ALIAS;

		$_resultAddress->firstname    = $address->getFirstName();
		$_resultAddress->lastname     = $address->getLastName();
		$_resultAddress->address1     = $address->getStreet1();
		$_resultAddress->postcode     = $address->getZipcode();
		$_resultAddress->city         = $address->getCity();
		$_resultAddress->country      = $address->getCountry();
		$_resultAddress->phone        = $address->getPhone() ? $address->getPhone() : 1;
		$_resultAddress->phone_mobile = $address->getMobile() ? $address->getMobile() : 1;

		/**
		 * check is state iso code available
		 */
		if ($address->getState() != '')
			$_resultAddress->id_state = $this->_getStateIdByIsoCode($address->getState());

		$_resultAddress->company = $address->getCompany();

		return $_resultAddress;
	}

	/**
	 * returns the country id by iso code
	 *
	 * @param string $isoCode
	 *
	 * @return int
	 */
	protected function _getCountryIdByIsoCode($isoCode)
	{
		if ($isoCode && $countryId = Country::getByIso($isoCode))
			return $countryId;
		else
			$this->_addException(ShopgateLibraryException::UNKNOWN_ERROR_CODE, ' invalid or empty iso code #'.$isoCode);
	}

	/**
	 * returns the state id by iso code (US-FL|FL)
	 *
	 * @param $isoCode
	 *
	 * @return int
	 */
	protected function _getStateIdByIsoCode($isoCode)
	{
		if ($isoCode)
		{
			$stateParts = explode('-', $isoCode);
			if (is_array($stateParts))
			{
				if (count($stateParts) == 2)
				{
					$stateId = State::getIdByIso(
						$stateParts[1],
						$this->_getCountryIdByIsoCode($stateParts[0])
					);
				}
				else
					$stateId = State::getIdByIso($stateParts[0]);
			}

			if ($stateId)
				return $stateId;
			else
				$this->_addException(ShopgateLibraryException::UNKNOWN_ERROR_CODE, ' invalid or empty iso code #'.$isoCode);
		}
	}

	/**
	 * add exception
	 *
	 * @param int  $errorCoded
	 * @param bool $message
	 * @param bool $writeLog
	 *
	 * @throws ShopgateLibraryException
	 */
	protected function _addException($errorCoded = ShopgateLibraryException::UNKNOWN_ERROR_CODE, $message = false, $writeLog = false)
	{
		throw new ShopgateLibraryException($errorCoded, $message, true, $writeLog);
	}

	/**
	 * remove data from database
	 */
	public function __destruct()
	{
		foreach ($this->getCustomersByEmail(PSShopgateCheckCart::DEFAULT_CUSTOMER_EMAIL) as $customer)
		{
			$currentCustomer = new Customer($customer['id_customer']);
			$currentCustomer->delete();
		}
	}

	/**
	 * Retrieve customers by email address
	 *
	 * @static
	 *
	 * @param $email
	 *
	 * @return array
	 */
	public function getCustomersByEmail($email)
	{
		$query = 'SELECT id_customer FROM '._DB_PREFIX_.'customer WHERE email = "'.pSQL($email).'"';

		return Db::getInstance()->ExecuteS($query);
	}

	/**
	 * in array recursive
	 *
	 * @param      $needle
	 * @param      $haystack
	 * @param bool $strict
	 *
	 * @return bool
	 */
	protected function _inArrayR($needle, $haystack, $strict = false)
	{
		foreach ($haystack as $item)
		{
			if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->_inArrayR($needle, $item, $strict)))
				return true;
		}

		return false;
	}
}