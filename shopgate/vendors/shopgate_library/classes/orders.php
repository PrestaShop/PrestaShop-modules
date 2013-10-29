<?php
/*
* Shopgate GmbH
*
* URHEBERRECHTSHINWEIS
*
* Dieses Plugin ist urheberrechtlich geschützt. Es darf ausschließlich von Kunden der Shopgate GmbH
* zum Zwecke der eigenen Kommunikation zwischen dem IT-System des Kunden mit dem IT-System der
* Shopgate GmbH über www.shopgate.com verwendet werden. Eine darüber hinausgehende Vervielfältigung, Verbreitung,
* öffentliche Zugänglichmachung, Bearbeitung oder Weitergabe an Dritte ist nur mit unserer vorherigen
* schriftlichen Zustimmung zulässig. Die Regelungen der §§ 69 d Abs. 2, 3 und 69 e UrhG bleiben hiervon unberührt.
*
* COPYRIGHT NOTICE
*
* This plugin is the subject of copyright protection. It is only for the use of Shopgate GmbH customers,
* for the purpose of facilitating communication between the IT system of the customer and the IT system
* of Shopgate GmbH via www.shopgate.com. Any reproduction, dissemination, public propagation, processing or
* transfer to third parties is only permitted where we previously consented thereto in writing. The provisions
* of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain unaffected.
*
*  @author Shopgate GmbH <interfaces@shopgate.com>
*/

abstract class ShopgateCartBase extends ShopgateContainer {
	
	const SHOPGATE = "SHOPGATE";
	const PREPAY = "PREPAY";
	
	const DEBIT = "DEBIT";
	const COD = "COD";
	
	const INVOICE = "INVOICE";
	const KLARNA_INV = "KLARNA_INV";
	const BILLSAFE = "BILLSAFE";
	const MSTPAY_INV = "MSTPAY_INV";
	
	const PAYPAL = "PAYPAL";
	const MASTPAY_PP = "MASTPAY_PP";
	const SAGEPAY_PP = "SAGEPAY_PP";
	
	const CC = "CC";
	const DT_CC = "DT_CC";
	const AUTHN_CC = "AUTHN_CC";
	const FRSTDAT_CC = "FRSTDAT_CC";
	const MASTPAY_CC = "MASTPAY_CC";
	const BRAINTR_CC = "BRAINTR_CC";
	const CYBRSRC_CC = "CYBRSRC_CC";
	const DTCASH_CC = "DTCASH_CC";
	const OGONE_CC = "OGONE_CC";
	const SAGEPAY_CC = "SAGEPAY_CC";
	const EWAY_CC = "EWAY_CC";
	const PAYJUNC_CC = "PAYJUNC_CC";
	const PP_WSPP_CC = "PP_WSPP_CC";
	
	const PAYU = "PAYU";
	
	protected $customer_number;

	protected $external_order_number;
	protected $external_order_id;

	protected $external_customer_number;
	protected $external_customer_id;

	protected $mail;
	protected $phone;
	protected $mobile;

	protected $payment_method;
	protected $payment_group;

	protected $amount_items;
	protected $amount_shipping;
	protected $amount_shop_payment;
	protected $payment_tax_percent;
	protected $amount_shopgate_payment;
	protected $amount_complete;
	protected $currency;

	protected $invoice_address;
	protected $delivery_address;

	protected $external_coupons = array();
	protected $shopgate_coupons = array();
	
	protected $items = array();

	##########
	# Setter #
	##########
	
	/**
	 * @param string $value
	 */
	public function setCustomerNumber($value) {
		$this->customer_number = $value;
	}

	/**
	 * @param string $value
	 */
	public function setExternalOrderNumber($value) {
		$this->external_order_number = $value;
	}

	/**
	 * @param string $value
	 */
	public function setExternalOrderId($value) {
		$this->external_order_id = $value;
	}

	/**
	 * @param string $value
	 */
	public function setExternalCustomerNumber($value) {
		$this->external_customer_number = $value;
	}

	/**
	 * @param string $value
	 */
	public function setExternalCustomerId($value) {
		$this->external_customer_id = $value;
	}

	/**
	 * @param string $value
	 */
	public function setMail($value) {
		$this->mail = $value;
	}

	/**
	 * @param string $value
	 */
	public function setPhone($value) {
		$this->phone = $value;
	}

	/**
	 * @param string $value
	 */
	public function setMobile($value) {
		$this->mobile = $value;
	}

	/**
	 * @see http://wiki.shopgate.com/Merchant_API_payment_infos/
	 * @param string $value
	 */
	public function setPaymentGroup($value) {
		$this->payment_group = $value;
	}

	/**
	 * @see http://wiki.shopgate.com/Merchant_API_payment_infos/
	 * @param string $value
	 */
	public function setPaymentMethod($value) {
		$this->payment_method = $value;
	}

	/**
	 * @param float $value
	 */
	public function setAmountItems($value) {
		$this->amount_items = $value;
	}

	/**
	 * @param float $value
	 */
	public function setAmountShipping($value) {
		$this->amount_shipping = $value;
	}

	/**
	 * @param float $value
	 */
	public function setAmountShopPayment($value) {
		$this->amount_shop_payment = $value;
	}

	/**
	 * @param float $value
	 */
	public function setPaymentTaxPercent($value) {
		$this->payment_tax_percent = $value;
	}
	
	/**
	 * @param float $value
	 */
	public function setAmountShopgatePayment($value) {
		$this->amount_shopgate_payment = $value;
	}
	
	/**
	 * @param float $value
	 */
	public function setAmountComplete($value) {
		$this->amount_complete = $value;
	}

	/**
	 * @see http://de.wikipedia.org/wiki/ISO_4217
	 * @param string $value
	 */
	public function setCurrency($value) {
		$this->currency = $value;
	}

	/**
	 * @param ShopgateAddress|mixed[] $value
	 */
	public function setInvoiceAddress($value) {
		if (!is_object($value) && !($value instanceof ShopgateAddress) && !is_array($value)) {
			$this->invoice_address = null;
			return;
		}

		if (is_array($value)) {
			$value = new ShopgateAddress($value);
			$value->setIsDeliveryAddress(false);
			$value->setIsInvoiceAddress(true);
		}

		$this->invoice_address = $value;
	}

	/**
	 * @param ShopgateAddress|mixed[] $value
	 */
	public function setDeliveryAddress($value) {
		if (!is_object($value) && !($value instanceof ShopgateAddress) && !is_array($value)) {
			$this->delivery_address = null;
			return;
		}

		if (is_array($value)) {
			$value = new ShopgateAddress($value);
			$value->setIsDeliveryAddress(true);
			$value->setIsInvoiceAddress(false);
		}

		$this->delivery_address = $value;
	}

	/**
	 * @param ShopgateExternalCoupon[] $value
	 */
	public function setExternalCoupons($value) {
		if (!is_array($value)) {
			$this->external_coupons = null;
			return;
		}
	
		foreach ($value as $index => &$element) {
			if ((!is_object($element) || !($element instanceof ShopgateExternalCoupon)) && !is_array($element)) {
				unset($value[$index]);
				continue;
			}
	
			if (is_array($element)) {
				$element = new ShopgateExternalCoupon($element);
			}
		}
	
		$this->external_coupons = $value;
	}
	
	/**
	 * @param ShopgateShopgateCoupon[] $value
	 */
	public function setShopgateCoupons($value) {
		if (!is_array($value)) {
			$this->shopgate_coupons = null;
			return;
		}
	
		foreach ($value as $index => &$element) {
			if ((!is_object($element) || !($element instanceof ShopgateShopgateCoupon)) && !is_array($element)) {
				unset($value[$index]);
				continue;
			}
	
			if (is_array($element)) {
				$element = new ShopgateShopgateCoupon($element);
			}
		}
	
		$this->shopgate_coupons = $value;
	}
	
	/**
	 * @param ShopgateOrderItem[]|mixed[][] $value
	 */
	public function setItems($value) {
		if (!is_array($value)) {
			$this->items = null;
			return;
		}

		foreach ($value as $index => &$element) {
			if ((!is_object($element) || !($element instanceof ShopgateOrderItem)) && !is_array($element)) {
				unset($value[$index]);
				continue;
			}

			if (is_array($element)) {
				$element = new ShopgateOrderItem($element);
			}
		}

		$this->items = $value;
	}


	##########
	# Getter #
	##########
	
	/**
	 * @return string
	 */
	public function getCustomerNumber() {
		return $this->customer_number;
	}

	/**
	 * @return string
	 */
	public function getExternalCustomerNumber() {
		return $this->external_customer_number;
	}

	/**
	 * @return string
	 */
	public function getExternalCustomerId() {
		return $this->external_customer_id;
	}

	/**
	 * @return string
	 */
	public function getMail() {
		return $this->mail;
	}

	/**
	 * @return string
	 */
	public function getPhone() {
		return $this->phone;
	}

	/**
	 * @return string
	 */
	public function getMobile() {
		return $this->mobile;
	}

	/**
	 * @see http://wiki.shopgate.com/Merchant_API_payment_infos/
	 * @return string
	 */
	public function getPaymentMethod() {
		return $this->payment_method;
	}

	/**
	 * @see http://wiki.shopgate.com/Merchant_API_payment_infos/
	 * @return string
	 */
	public function getPaymentGroup() {
		return $this->payment_group;
	}

	/**
	 * @return float
	 */
	public function getAmountItems() {
		return $this->amount_items;
	}

	/**
	 * @return float
	 */
	public function getAmountShipping() {
		return $this->amount_shipping;
	}

	/**
	 * @return float
	 */
	public function getAmountShopPayment() {
		return $this->amount_shop_payment;
	}

	/**
	 * @return float
	 */
	public function getPaymentTaxPercent() {
		return $this->payment_tax_percent;
	}

	/**
	 * @return float
	 */
	public function getAmountShopgatePayment() {
		return $this->amount_shopgate_payment;
	}

	/**
	 * @return float
	 */
	public function getAmountComplete() {
		return $this->amount_complete;
	}

	/**
	 * @see http://de.wikipedia.org/wiki/ISO_4217
	 * @return string
	 */
	public function getCurrency() {
		return $this->currency;
	}

	/**
	 * @return ShopgateAddress
	 */
	public function getInvoiceAddress() {
		return $this->invoice_address;
	}

	/**
	 * @return ShopgateAddress
	 */
	public function getDeliveryAddress() {
		return $this->delivery_address;
	}

	/**
	 * @return ShopgateExternalCoupon[]
	 */
	public function getExternalCoupons() {
		return $this->external_coupons;
	}
	
	/**
	 * @return ShopgateShopgateCoupon[]
	 */
	public function getShopgateCoupons() {
		return $this->shopgate_coupons;
	}
	
	/**
	 * @return ShopgateOrderItem[]
	 */
	public function getItems() {
		return $this->items;
	}
}

class ShopgateCart extends ShopgateCartBase {
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitCart($this);
	}
}

class ShopgateOrder extends ShopgateCartBase {
	protected $order_number;
	
	protected $confirm_shipping_url;
	
	protected $shipping_group;
	protected $shipping_type;
	protected $shipping_infos;
	
	protected $created_time;
	
	protected $is_paid;
	
	protected $payment_time;
	protected $payment_transaction_number;
	protected $payment_infos;
	
	protected $is_shipping_blocked;
	protected $is_shipping_completed;
	protected $shipping_completed_time;
	
	protected $is_test;
	protected $is_storno;
	protected $is_customer_invoice_blocked;
	
	protected $update_shipping = false;
	protected $update_payment = false;
	
	protected $delivery_notes = array();
	
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitOrder($this);
	}

	##########
	# Setter #
	##########
	
	/**
	 * @param string $value
	 */
	public function setOrderNumber($value) {
		$this->order_number = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setConfirmShippingUrl($value) {
		$this->confirm_shipping_url = $value;
	}

	/**
	 *
	 * @param string $value
	 */
	public function setShippingGroup($value) {
		$this->shipping_group = $value;
	}
	
	/**
	 *
	 * @param string $value
	 */
	public function setShippingType($value) {
		$this->shipping_type = $value;
	}
	
	/**
	 *
	 * @param ShopgateShippingInfo $value
	 */
	public function setShippingInfos($value) {
		if (!is_object($value) && !($value instanceof ShopgateShippingInfo) && !is_array($value)) {
			$this->shipping_infos = null;
			return;
		}
	
		if (is_array($value)) {
			$value = new ShopgateShippingInfo($value);
		}
	
		$this->shipping_infos = $value;
	}
	
	/**
	 * @see http://www.php.net/manual/de/function.date.php
	 * @see http://en.wikipedia.org/wiki/ISO_8601
	 * @param string $value
	 */
	public function setCreatedTime($value) {
		$this->created_time = $value;
	}

	/**
	 * @param bool $value
	 */
	public function setIsPaid($value) {
		$this->is_paid = $value;
	}

	/**
	 * @see http://www.php.net/manual/de/function.date.php
	 * @see http://en.wikipedia.org/wiki/ISO_8601
	 * @param string $value
	 */
	public function setPaymentTime($value) {
		$this->payment_time = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setPaymentTransactionNumber($value) {
		$this->payment_transaction_number = $value;
	}

	/**
	 * @see http://wiki.shopgate.com/Merchant_API_payment_infos/
	 * @param mixed[] $value An array of additional information about the payment depending on the payment type
	 */
	public function setPaymentInfos($value) {
		$this->payment_infos = $value;
	}

	/**
	 * @param bool $value
	 */
	public function setIsShippingBlocked($value) {
		$this->is_shipping_blocked = $value;
	}
	
	/**
	 * @param bool $value
	 */
	public function setIsShippingCompleted($value) {
		$this->is_shipping_completed = $value;
	}
	
	/**
	 * @see http://www.php.net/manual/de/function.date.php
	 * @see http://en.wikipedia.org/wiki/ISO_8601
	 * @param string $value
	 */
	public function setShippingCompletedTime($value) {
		$this->shipping_completed_time = $value;
	}
	
	/**
	 * @param bool $value
	 */
	public function setIsTest($value) {
		$this->is_test = $value;
	}
	
	/**
	 * @param bool $value
	 */
	public function setIsStorno($value) {
		$this->is_storno = $value;
	}
	
	/**
	 * @param bool $value
	 */
	public function setIsCustomerInvoiceBlocked($value) {
		$this->is_customer_invoice_blocked = $value;
	}
	
	/**
	 * @param bool $value
	 */
	public function setUpdatePayment($value) {
		$this->update_payment = $value;
	}
	
	/**
	 * @param bool $value
	 */
	public function setUpdateShipping($value) {
		$this->update_shipping = $value;
	}
	
	/**
	 * @param ShopgateDeliveryNote[]|mixed[][] $value
	 */
	public function setDeliveryNotes($value) {
		if (empty($value)) {
			$this->delivery_notes = null;
			return;
		}
	
		if (!is_array($value)) {
			$this->delivery_notes = null;
			return;
		}
	
		foreach ($value as $index => &$element) {
			if ((!is_object($element) || !($element instanceof ShopgateDeliveryNote)) && !is_array($element)) {
				unset($value[$index]);
				continue;
			}
	
			if (is_array($element)) {
				$element = new ShopgateDeliveryNote($element);
			}
		}
	
		$this->delivery_notes = $value;
	}
	
	
	##########
	# Getter #
	##########
	
	/**
	 * @return string
	 */
	public function getOrderNumber() {
		return $this->order_number;
	}
	
	/**
	 * @return string
	 */
	public function getExternalOrderNumber() {
		return $this->external_order_number;
	}
	
	/**
	 * @return string
	 */
	public function getExternalOrderId() {
		return $this->external_order_id;
	}

	/**
	 * @return string
	 */
	public function getConfirmShippingUrl() {
		return $this->confirm_shipping_url;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getShippingGroup() {
		return $this->shipping_group;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getShippingType() {
		return $this->shipping_type;
	}
	
	/**
	 *
	 * @return ShopgateShippingInfo
	 */
	public function getShippingInfos() {
		return $this->shipping_infos;
	}
	
	/**
	 * @see http://www.php.net/manual/de/function.date.php
	 * @see http://en.wikipedia.org/wiki/ISO_8601
	 * @param string $format
	 * @return string
	 */
	public function getCreatedTime($format = "") {
		$time = $this->created_time;
		if(!empty($format)) {
			$timestamp = strtotime($time);
			$time = date($format, $timestamp);
		}
	
		return $time;
	}
	
	/**
	 * @return bool
	 */
	public function getIsPaid() {
		return (bool) $this->is_paid;
	}

	/**
	 * @see http://www.php.net/manual/de/function.date.php
	 * @see http://en.wikipedia.org/wiki/ISO_8601
	 * @param string format
	 * @return string
	 */
	public function getPaymentTime($format="") {
		$time = $this->payment_time;
		if(!empty($format)) {
			$timestamp = strtotime($time);
			$time = date($format, $timestamp);
		}

		return $time;
	}
	
	/**
	 * @return string
	 */
	public function getPaymentTransactionNumber() {
		return $this->payment_transaction_number;
	}
	
	/**
	 * @return mixed[]
	 */
	public function getPaymentInfos() {
		return $this->payment_infos;
	}
	
	/**
	 * @return bool
	 */
	public function getIsShippingBlocked() {
		return (bool) $this->is_shipping_blocked;
	}
	
	/**
	 * @return bool
	 */
	public function getIsShippingCompleted() {
		return (bool) $this->is_shipping_completed;
	}
	
	/**
	 * @see http://www.php.net/manual/de/function.date.php
	 * @see http://en.wikipedia.org/wiki/ISO_8601
	 * @param string format
	 * @return string
	 */
	public function getShippingCompletedTime($format='') {
		$time = $this->shipping_completed_time;
		if(!empty($format)) {
			$timestamp = strtotime($time);
			$time = date($format, $timestamp);
		}
	
		return $time;
	}
	
	/**
	 * @return bool
	 */
	public function getIsTest() {
		return (bool) $this->is_test;
	}
	
	
	/**
	 * @return bool
	 */
	public function getIsStorno() {
		return (bool) $this->is_storno;
	}
	
	/**
	 * @return bool
	 */
	public function getIsCustomerInvoiceBlocked() {
		return (bool) $this->is_customer_invoice_blocked;
	}
	
	/**
	 * @return bool
	 */
	public function getUpdatePayment() {
		return (bool) $this->update_payment;
	}
	
	/**
	 * @return bool
	 */
	public function getUpdateShipping() {
		return (bool) $this->update_shipping;
	}
	
	/**
	 * @return ShopgateDeliveryNote[]
	 */
	public function getDeliveryNotes() {
		return $this->delivery_notes;
	}
}

class ShopgateOrderItem extends ShopgateContainer {
	protected $item_number;
	protected $item_number_public;

	protected $quantity;

	protected $name;

	protected $unit_amount;
	protected $unit_amount_with_tax;

	protected $tax_percent;
	protected $tax_class_key;
	protected $tax_class_id;

	protected $currency;

	protected $internal_order_info;

	protected $options = array();

	protected $inputs = array();
	
	protected $attributes = array();


	##########
	# Setter #
	##########
	
	/**
	 * @param string $value
	 */
	public function setName($value) {
		$this->name = $value;
	}

	/**
	 * @param string $value
	 */
	public function setItemNumber($value) {
		$this->item_number = $value;
	}

	/**
	 * @param string $value
	 */
	public function setItemNumberPublic($value) {
		$this->item_number_public = $value;
	}

	/**
	 * @param float $value
	 */
	public function setUnitAmount($value) {
		$this->unit_amount = $value;
	}

	/**
	 * @param float $value
	 */
	public function setUnitAmountWithTax($value) {
		$this->unit_amount_with_tax = $value;
	}

	/**
	 * @param int $value
	 */
	public function setQuantity($value) {
		$this->quantity = $value;
	}

	/**
	 * @param float $value
	 */
	public function setTaxPercent($value) {
		$this->tax_percent = $value;
	}
	
	/**
	 * Sets the tax_class_key value
	 *
	 * @param string $value
	 */
	public function setTaxClassKey($value) {
		$this->tax_class_key = $value;
	}
	
	/**
	 * Sets the tax_class_id
	 *
	 * @param string $value
	 */
	public function setTaxClassId($value) {
		$this->tax_class_id = $value;
	}

	/**
	 * @param string $value
	 */
	public function setCurrency($value) {
		$this->currency = $value;
	}

	/**
	 * @param string $value
	 */
	public function setInternalOrderInfo($value) {
		$this->internal_order_info = $value;
	}

	/**
	 * @param ShopgateOrderItemOption[]|mixed[][] $value
	 */
	public function setOptions($value) {
		if (empty($value) || !is_array($value)) {
			$this->options = array();
			return;
		}

		// convert sub-arrays into ShopgateOrderItemOption objects if necessary
		foreach ($value as $index => &$element) {
			if ((!is_object($element) || !($element instanceof ShopgateOrderItemOption)) && !is_array($element)) {
				unset($value[$index]);
				continue;
			}

			if (is_array($element)) {
				$element = new ShopgateOrderItemOption($element);
			}
		}

		$this->options = $value;
	}

	/**
 	 * @param ShopgateOrderItemInput[]|mixed[][] $value
	 */
	public function setInputs($value) {
		if (empty($value) || !is_array($value)) {
			$this->inputs = array();
			return;
		}
		
		// convert sub-arrays into ShopgateOrderItemInputs objects if necessary
		foreach ($value as $index => &$element) {
			if ((!is_object($element) || !($element instanceof ShopgateOrderItemInput)) && !is_array($element)) {
				unset($value[$index]);
				continue;
			}
			
			if (is_array(($element))) {
				$element = new ShopgateOrderItemInput($element);
			}
		}
		
		$this->inputs = $value;
	}

	/**
 	 * @param ShopgateOrderItemAttribute[]|mixed[][] $value
	 */
	public function setAttributes($value) {
		if (empty($value) || !is_array($value)) {
			$this->attributes = array();
			return;
		}
		
		// convert sub-arrays into ShopgateOrderItemInputs objects if necessary
		foreach ($value as $index => &$element) {
			if ((!is_object($element) || !($element instanceof ShopgateOrderItemAttribute)) && !is_array($element)) {
				unset($value[$index]);
				continue;
			}
			
			if (is_array(($element))) {
				$element = new ShopgateOrderItemAttribute($element);
			}
		}
		
		$this->attributes = $value;
	}


	##########
	# Getter #
	##########
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getItemNumber() {
		return $this->item_number;
	}

	/**
	 * @return string
	 */
	public function getItemNumberPublic() {
		return $this->item_number_public;
	}

	/**
	 * @return float
	 */
	public function getUnitAmount() {
		return $this->unit_amount;
	}

	/**
	 * @return float
	 */
	public function getUnitAmountWithTax() {
		return $this->unit_amount_with_tax;
	}

	/**
	 * @return int
	 */
	public function getQuantity() {
		return $this->quantity;
	}

	/**
	 * @return float
	 */
	public function getTaxPercent() {
		return $this->tax_percent;
	}
	
	/**
	 * @return string
	 */
	public function getTaxClassKey() {
		return $this->tax_class_key;
	}
	
	/**
	 * Returns the tax_class_id
	 *
	 * @return string
	 */
	public function getTaxClassId() {
		return $this->tax_class_id;
	}

	/**
	 * @return string
	 */
	public function getCurrency() {
		return $this->currency;
	}

	/**
	 * @return string
	 */
	public function getInternalOrderInfo() {
		return $this->internal_order_info;
	}

	/**
	 * @return ShopgateOrderItemOption[]
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * @return ShopgateOrderItemInput[]
	 */
	public function getInputs() {
		return $this->inputs;
	}

	/**
	 * @return ShopgateOrderItemAttribute[]
	 */
	public function getAttributes() {
		return $this->attributes;
	}


	public function accept(ShopgateContainerVisitor $v) {
		$v->visitOrderItem($this);
	}
}

class ShopgateOrderItemOption extends ShopgateContainer {
	protected $name;
	protected $value;
	protected $additional_amount_with_tax;
	protected $value_number;
	protected $option_number;


	##########
	# Setter #
	##########
	
	/**
	 * @param string $value
	 */
	public function setName($value) {
		$this->name = $value;
	}

	/**
	 * @param string $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}

	/**
	 * @param string $value
	 */
	public function setAdditionalAmountWithTax($value) {
		$this->additional_amount_with_tax = $value;
	}

	/**
	 * @param string $value
	 */
	public function setValueNumber($value) {
		$this->value_number = $value;
	}

	/**
	 * @param string $value
	 */
	public function setOptionNumber($value) {
		$this->option_number = $value;
	}


	##########
	# Getter #
	##########
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @return int
	 */
	public function getAdditionalAmountWithTax() {
		return $this->additional_amount_with_tax;
	}

	/**
	 * @return string
	 */
	public function getValueNumber() {
		return $this->value_number;
	}

	/**
	 * @return string
	 */
	public function getOptionNumber() {
		return $this->option_number;
	}


	public function accept(ShopgateContainerVisitor $v) {
		$v->visitOrderItemOption($this);
	}
}

class ShopgateOrderItemInput extends ShopgateContainer {
	protected $input_number;
	protected $type;
	protected $additional_amount_with_tax;
	protected $label;
	protected $user_input;
	protected $info_text;
	
	
	##########
	# Setter #
	##########

	/**
	 * @param string $value
	 */
	public function setInputNumber($value) {
		$this->input_number = $value;
	}
	
	/**
	 * @param string $value "text"|"image"
	 */
	public function setType($value) {
		$this->type = $value;
	}
	
	/**
	 * @param float $value
	 */
	public function setAdditionalAmountWithTax($value) {
		$this->additional_amount_with_tax = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setLabel($value) {
		$this->label = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setUserInput($value) {
		$this->user_input = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setInfoText($value) {
		$this->info_text = $value;
	}
	
	##########
	# Getter #
	##########
	
	/**
	 * @return string
	 */
	public function getInputNumber() {
		return $this->input_number;
	}
	
	/**
	 * @return string "text"|"image"
	 */
	public function getType() {
		return $this->type;
	}
	
	/**
	 * @return float
	 */
	public function getAdditionalAmountWithTax() {
		return $this->additional_amount_with_tax;
	}
	
	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}
	
	/**
	 * @return string
	 */
	public function getUserInput() {
		return $this->user_input;
	}
	
	/**
	 * @return string
	 */
	public function getInfoText() {
		return $this->info_text;
	}
	
	
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitOrderItemInput($this);
	}
}

class ShopgateOrderItemAttribute extends ShopgateContainer {
	protected $name;
	protected $value;


	##########
	# Setter #
	##########
	
	/**
	 * @param string $value
	 */
	public function setName($value) {
		$this->name = $value;
	}

	/**
	 * @param string $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}


	##########
	# Getter #
	##########
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}


	public function accept(ShopgateContainerVisitor $v) {
		$v->visitOrderItemAttribute($this);
	}
}

class ShopgateShippingInfo extends ShopgateContainer {
	protected $name;
	protected $description;
	protected $amount;
	protected $weight;
	protected $api_response;
	
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitOrderShipping($this);
	}
	
	/**
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	/**
	 *
	 * @param string $value
	 */
	public function setName($value) {
		$this->name = $value;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}
	/**
	 *
	 * @param string $value
	 */
	public function setDescription($value) {
		$this->description = $value;
	}
	
	/**
	 *
	 * @return float
	 */
	public function getAmount() {
		return $this->amount;
	}
	/**
	 *
	 * @param float $value
	 */
	public function setAmount($value) {
		$this->amount = $value;
	}
	
	/**
	 *
	 * @return int
	 */
	public function getWeight() {
		return $this->weight;
	}
	/**
	 *
	 * @param int $value
	 */
	public function setWeight($value) {
		$this->weight = $value;
	}
	
	/**
	 *
	 * @return mixed[]
	 */
	public function getApiResponse() {
		return $this->api_response;
	}
	/**
	 *
	 * @param string|mixed[] $value
	 */
	public function setApiResponse($value) {
		if(is_string($value)) {
			$value = $this->jsonDecode($value, true);
		}
		
		$this->api_response = $value;
	}
}

class ShopgateDeliveryNote extends ShopgateContainer {
	// shipping groups
	const DHL			= "DHL"; // DHL
	const DHLEXPRESS	= "DHLEXPRESS"; // DHLEXPRESS
	const DP			= "DP"; // Deutsche Post
	const DPD			= "DPD"; // Deutscher Paket Dienst
	const FEDEX			= "FEDEX"; // FedEx
	const GLS			= "GLS"; // GLS
	const HLG			= "HLG"; // Hermes
	const OTHER			= "OTHER"; // Anderer Lieferant
	const TNT			= "TNT"; // TNT
	const TOF			= "TOF"; // Trnas-o-Flex
	const UPS			= "UPS"; // UPS
	const USPS			= "USPS"; // USPS

	// shipping types
	const MANUAL		= "MANUAL";
	const USPS_API_V1	= "USPS_API_V1";
	const UPS_API_V1	= "UPS_API_V1";
	
	protected $shipping_service_id = ShopgateDeliveryNote::DHL;
	protected $tracking_number = "";
	protected $shipping_time = null;

	##########
	# Setter #
	##########
	
	/**
	 * @param string $value
	 */
	public function setShippingServiceId($value) {
		$this->shipping_service_id = $value;
	}

	/**
	 * @param string $value
	 */
	public function setTrackingNumber($value) {
		$this->tracking_number = $value;
	}

	/**
	 * @param string $value
	 */
	public function setShippingTime($value) {
		$this->shipping_time = $value;
	}


	##########
	# Getter #
	##########
	
	/**
	 * @return string
	 */
	public function getShippingServiceId() {
		return $this->shipping_service_id;
	}

	/**
	 * @return string
	 */
	public function getTrackingNumber() {
		return $this->tracking_number;
	}

	/**
	 * @return string
	 */
	public function getShippingTime() {
		return $this->shipping_time;
	}


	public function accept(ShopgateContainerVisitor $v) {
		$v->visitOrderDeliveryNote($this);
	}
}

abstract class ShopgateCoupon extends ShopgateContainer {
	
	protected $order_index;
	
	protected $code;
	protected $name;
	protected $description;
	protected $amount;
	protected $currency;
	protected $is_free_shipping;
	protected $internal_info;
	
	##########
	# Setter #
	##########
	
	/**
	 * @param int $value
	 */
	public function setOrderIndex($value) {
		$this->order_index = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setCode($value) {
		$this->code = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setName($value) {
		$this->name = $value;
	}
	
	/**
	 *
	 * @param string $value
	 */
	public function setDescription($value) {
		$this->description = $value;
	}
	
	/**
	 * @param float $value
	 */
	public function setAmount($value) {
		$this->amount = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setCurrency($value) {
		$this->currency = $value;
	}
	
	/**
	 * @param bool $value
	 */
	public function setIsFreeShipping($value) {
		$this->is_free_shipping = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setInternalInfo($value) {
		$this->internal_info = $value;
	}
	
	##########
	# Getter #
	##########

	/**
	 * @return int
	 */
	public function getOrderIndex() {
		return $this->order_index;
	}
	
	/**
	 * @return string
	 */
	public function getCode() {
		return $this->code;
	}
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * @return float
	 */
	public function getAmount() {
		return $this->amount;
	}
	
	/**
	 * @return string
	 */
	public function getCurrency() {
		return $this->currency;
	}
	
	/**
	 * @return bool
	 */
	public function getIsFreeShipping() {
		return $this->is_free_shipping;
	}
	
	/**
	 * @return string
	 */
	public function getInternalInfo() {
		return $this->internal_info;
	}
	
}

class ShopgateExternalCoupon extends ShopgateCoupon {
	
	protected $is_valid;
	protected $not_valid_message;
	
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitExternalCoupon($this);
	}
	
	##########
	# Setter #
	##########
	
	/**
	 * @param bool $value
	 */
	public function setIsValid($value) {
		$this->is_valid = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setNotValidMessage($value) {
		$this->not_valid_message = $value;
	}
	
	##########
	# Getter #
	##########
	
	/**
	 * @return bool
	 */
	public function getIsValid() {
		return $this->is_valid;
	}
	
	/**
	 * @return string
	 */
	public function getNotValidMessage() {
		return $this->not_valid_message;
	}
}

class ShopgateShopgateCoupon extends ShopgateCoupon {

	public function accept(ShopgateContainerVisitor $v) {
		$v->visitCoupon($this);
	}
	
	##########
	# Setter #
	##########
	
	##########
	# Getter #
	##########

}