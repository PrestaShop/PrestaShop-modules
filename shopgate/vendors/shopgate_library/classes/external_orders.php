<?php
class ShopgateExternalOrder extends ShopgateContainer {
	protected $order_number;
	protected $external_order_number;
	protected $external_order_id;

	protected $created_time;
	
	protected $mail;
	protected $phone;
	protected $mobile;
	
	
	protected $custom_fields;
	protected $invoice_address;
	protected $delivery_address;
	
	protected $currency;
	protected $amount_complete;
	protected $is_paid;
	protected $payment_method;
	protected $payment_time;
	protected $payment_transaction_number;
	
	protected $is_shipping_completed;
	protected $shipping_completed_time;

	protected $delivery_notes;

	protected $order_taxes;
	protected $extra_costs;
	protected $external_coupons;
	protected $items;

	/**
	 * @param string $value
	 */
	public function setOrderNumber($value) {
		$this->order_number = $value;
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
	public function setCreatedTime($value) {
		$this->created_time = $value;
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
	 * @param ShopgateOrderCustomField[] $value
	 */
	public function setCustomFields($value) {
		if (!is_array($value)) {
			$this->custom_fields = array();
		}

		foreach ($value as $index => &$element) {
			if ((!is_object($element) || !($element instanceof ShopgateOrderCustomField)) && !is_array($element)) {
				unset($value[$index]);
				continue;
			}

			if (is_array($element)) {
				$element = new ShopgateOrderCustomField($element);
			}
		}

		$this->custom_fields = $value;
	}

	/**
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
	 * @param float $value
	 */
	public function setAmountComplete($value) {
		$this->amount_complete = $value;
	}
	
	/**
	 * @param bool $value
	 */
	public function setIsShippingCompleted($value) {
		$this->is_shipping_completed = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setShippingCompletedTime($value) {
		$this->shipping_completed_time = $value;
	}
	
	/**
	 * @param bool $value
	 */
	public function setIsPaid($value) {
		$this->is_paid = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setPaymentMethod($value) {
		$this->payment_method = $value;
	}
	
	/**
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
	 * @param ShopgateDeliveryNote[]|array[string, mixed] $value
	 */
	public function setDeliveryNotes($value) {
		if (empty($value) || !is_array($value)) {
			$this->delivery_notes = array();
			return;
		}

		$deliveryNotes = array();
		foreach ($value as $index => $element) {
			if (!($element instanceof ShopgateDeliveryNote) && !is_array($element)) {
				continue;
			}

			if (is_array($element)) {
				$deliveryNotes[] = new ShopgateDeliveryNote($element);
			} else {
				$deliveryNotes[] = $element;
			}
		}

		$this->delivery_notes = $deliveryNotes;
	}

	/**
	 * @param ShopgateExternalOrderTax[]|array[string, mixed] $value
	 */
	public function setOrderTaxes($value) {
		if (empty($value) || !is_array($value)) {
			$this->order_taxes = array();
			return;
		}

		$orderTaxes = array();
		foreach ($value as $index => $element) {
			if (!($element instanceof ShopgateExternalOrderTax) && !is_array($element)) {
				continue;
			}

			if (is_array($element)) {
				$orderTaxes[] = new ShopgateExternalOrderTax($element);
			} else {
				$orderTaxes[] = $element;
			}
		}

		$this->order_taxes = $orderTaxes;
	}

	/**
	 * @param ShopgateExternalOrderExtraCost[]|array[string, mixed] $value
	 */
	public function setExtraCosts($value) {
		if (empty($value) || !is_array($value)) {
			$this->extra_costs = array();
			return;
		}

		$extraCosts = array();
		foreach ($value as $index => $element) {
			if (!($element instanceof ShopgateExternalOrderExtraCost) && !is_array($element)) {
				continue;
			}

			if (is_array($element)) {
				$extraCosts[] = new ShopgateExternalOrderExtraCost($element);
			} else {
				$extraCosts[] = $element;
			}
		}

		$this->extra_costs = $extraCosts;
	}

	/**
	 * @param ShopgateExternalOrderItem[]|array[string, mixed] $value
	 */
	public function setItems($value) {
		if (!is_array($value)) {
			$this->items = null;

			return;
		}

		foreach ($value as $index => &$element) {
			if ((!is_object($element) || !($element instanceof ShopgateExternalOrderItem)) && !is_array($element)) {
				unset($value[$index]);
				continue;
			}

			if (is_array($element)) {
				$element = new ShopgateExternalOrderItem($element);
			}
		}

		$this->items = $value;
	}
	
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
	public function getCreatedTime() {
		return $this->created_time;
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
	 * @return ShopgateOrderCustomField[]
	 */
	public function getCustomFields() {
		if(!is_array($this->custom_fields)) {
			$this->custom_fields = array();
		}
		return $this->custom_fields;
	}

	/**
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
	 * @return float
	 */
	public function getAmountComplete() {
		return $this->amount_complete;
	}
	
	/**
	 * @return bool
	 */
	public function getIsShippingCompleted() {
		return $this->is_shipping_completed;
	}

	/**
	 * @return string
	 */
	public function getShippingCompletedTime() {
		return $this->shipping_completed_time;
	}
	
	/**
	 * @return bool
	 */
	public function getIsPaid() {
		return $this->is_paid;
	}
	
	/**
	 * @return string
	 */
	public function getPaymentMethod() {
		return $this->payment_method;
	}

	/**
	 * @return string
	 */
	public function getPaymentTime() {
		return $this->payment_time;
	}

	/**
	 * @return string
	 */
	public function getPaymentTransactionNumber() {
		return $this->payment_transaction_number;
	}

	/**
	 * @return ShopgateDeliveryNote[]
	 */
	public function getDeliveryNotes() {
		return $this->delivery_notes;
	}

	/**
	 * @return ShopgateOrderTax[]
	 */
	public function getOrderTaxes() {
		return $this->order_taxes;
	}

	/**
	 * @return ShopgateOrderExtraCost[]
	 */
	public function getExtraCosts() {
		return $this->extra_costs;
	}

	/**
	 * @return ShopgateExternalOrderItem[]
	 */
	public function getItems() {
		return $this->items;
	}
	
	/**
	 * @return ShopgateExternalOrderItem
	 */
	protected function getOrderItem(array $options) {
		return new ShopgateExternalOrderItem($options);
	}

	/**
	 * @see ShopgateContainer::accept()
	 */
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitExternalOrder($this);
	}
}

class ShopgateExternalOrderItem extends ShopgateContainer {
	protected $item_number;
	protected $item_number_public;

	protected $quantity;

	protected $name;

	protected $unit_amount;
	protected $unit_amount_with_tax;

	protected $tax_percent;

	protected $currency;

	protected $description;

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
	
	public function setDescription($value) {
		$this->description = $value;
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
	 * @param string $value
	 */
	public function setCurrency($value) {
		$this->currency = $value;
	}

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
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
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
	public function getCurrency() {
		return $this->currency;
	}

	public function accept(ShopgateContainerVisitor $v) {
		$v->visitExternalOrderItem($this);
	}
}

class ShopgateExternalOrderExtraCost extends ShopgateContainer {
	const TYPE_SHIPPING = 'shipping';
	const TYPE_PAYMENT = 'payment';
	const TYPE_MISC = 'misc';

	protected $type;
	protected $tax_percent;
	protected $amount;
	protected $label;

	/**
	 * @param string $value
	 */
	public function setType($value) {
		if (
		self::TYPE_SHIPPING != $value &&
		self::TYPE_PAYMENT != $value &&
		self::TYPE_MISC != $value
		) {
			$value = null;
		}

		$this->type = $value;
	}

	/**
	 * @param float $value
	 */
	public function setTaxPercent($value) {
		$this->tax_percent = $value;
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
	public function setLabel($value) {
		$this->label = $value;
	}


	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return float
	 */
	public function getTaxPercent() {
		return $this->tax_percent;
	}

	/**
	 *
	 * @return float
	 */
	public function getAmount() {
		return $this->amount;
	}
	
	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @see ShopgateContainer::accept()
	 */
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitExternalOrderExtraCost($this);
	}
}

class ShopgateExternalOrderTax extends ShopgateContainer {
	protected $label;
	protected $tax_percent;
	protected $amount;

	/**
	 *
	 * @param string $value
	 */
	public function setLabel($value){
		$this->label = $value;
	}

	/**
	 *
	 * @param float $value
	 */
	public function setTaxPercent($value){
		$this->tax_percent = $value;
	}

	/**
	 *
	 * @param float $value
	 */
	public function setAmount($value){
		$this->amount = $value;
	}

	/**
	 *
	 * @return string
	 */
	public function getLabel(){
		return $this->label;
	}

	/**
	 *
	 * @return float
	 */
	public function getTaxPercent(){
		return $this->tax_percent;
	}

	/**
	 *
	 * @return float
	 */
	public function getAmount(){
		return $this->amount;
	}

	/**
	 * @see ShopgateContainer::accept()
	 */
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitExternalOrderTax($this);
	}
}