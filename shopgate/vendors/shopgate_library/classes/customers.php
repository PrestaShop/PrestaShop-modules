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

class ShopgateCustomer extends ShopgateContainer {
	const MALE = "m";
	const FEMALE = "f";
	
	protected $customer_id;
	protected $customer_number;
	protected $customer_token;
	
	protected $customer_groups;

	protected $tax_class_key;
	protected $tax_class_id;
	
	protected $first_name;
	protected $last_name;
	
	protected $gender;
	protected $birthday;
	
	protected $phone;
	protected $mobile;
	protected $mail;
	
	protected $custom_fields;
	
	protected $newsletter_subscription;
	
	protected $addresses;
	
	/**
	 * @deprecated
	 */
	protected $customer_group;
	
	/**
	 * @deprecated
	 */
	protected $customer_group_id;

	public function accept(ShopgateContainerVisitor $v) {
		$v->visitCustomer($this);
	}
	

	
	##########
	# Setter #
	##########
	
	/**
	 * @param string $value
	 */
	public function setCustomerId($value) {
		$this->customer_id = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setCustomerNumber($value) {
		$this->customer_number = $value;
	}
	
	/**
	 * @param string $value
	 * @deprecated
	 */
	public function setCustomerGroup($value) {
		$this->customer_group = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setCustomerToken($value) {
		$this->customer_token = $value;
	}
	
	/**
	 * @param string $value
	 * @deprecated
	 */
	public function setCustomerGroupId($value) {
		$this->customer_group_id = $value;
	}

	/**
	 * @param ShopgateCustomerGroup[] $value
	 */
	public function setCustomerGroups($value) {
		$this->customer_groups = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setTaxClassKey($value) {
		$this->tax_class_key = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setTaxClassId($value) {
		$this->tax_class_id = $value;
	}

	/**
	 * @param string $value
	 */
	public function setFirstName($value) {
		$this->first_name = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setLastName($value) {
		$this->last_name = $value;
	}
	
	/**
	 * @param string $value <ul><li>"m" = Male</li><li>"f" = Female</li></ul>
	 */
	public function setGender($value) {
		if (empty($value)) return;
		
		if (($value != self::MALE) && ($value != self::FEMALE)) {
			$this->gender = null;
		} else {
			$this->gender = $value;
		}
	}
	
	/**
	 * @param string $value Format: yyyy-mm-dd (1983-02-17)
	 */
	public function setBirthday($value) {
		if (empty($value)) {
			$this->birthday = null;
			return;
		}
		
		$matches = null;
		if (!preg_match('/^([0-9]{4}\-[0-9]{2}\-[0-9]{2})/', $value, $matches)) {
			$this->birthday = null;
		} else {
			$this->birthday = $matches[1];
		}
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
	 * @param string $value
	 */
	public function setMail($value) {
		$this->mail = $value;
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
	 * @param bool $value
	 */
	public function setNewsletterSubscription($value) {
		$this->newsletter_subscription = $value;
	}
	
	/**
	 * @param ShopgateAddress[] $value List of customer's addresses.
	 */
	public function setAddresses($value) {
		if (!is_array($value)) {
			$this->addresses = null;
		}
		
		foreach ($value as $index => &$element) {
			if ((!is_object($element) || !($element instanceof ShopgateAddress)) && !is_array($element)) {
				unset($value[$index]);
				continue;
			}
			
			if (is_array($element)) {
				$element = new ShopgateAddress($element);
			}
		}
		
		$this->addresses = $value;
	}
	
	
	##########
	# Getter #
	##########
	
	/**
	 * @return string
	 */
	public function getCustomerId() {
		return $this->customer_id;
	}
	
	/**
	 * @return string
	 */
	public function getCustomerNumber() {
		return $this->customer_number;
	}
	
	/**
	 * @return string
	 * @deprecated
	 */
	public function getCustomerGroup() {
		return $this->customer_group;
	}

	/**
	 * @return string
	 */
	public function getCustomerToken() {
		return $this->customer_token;
	}
	
	/**
	 * @return string
	 * @deprecated
	 */
	public function getCustomerGroupId() {
		return $this->customer_group_id;
	}

	/**
	 * @return ShopgateCustomerGroup[]
	 */
	public function getCustomerGroups() {
		return $this->customer_groups;
	}
	
	/**
	 * @return string
	 */
	public function getTaxClassKey() { return $this->tax_class_key; }
	
	/**
	 * @return string
	 */
	public function getTaxClassId() { return $this->tax_class_id; }

	/**
	 * @return string
	 */
	public function getFirstName() {
		return $this->first_name;
	}
	
	/**
	 * @return string
	 */
	public function getLastName() {
		return $this->last_name;
	}
	
	/**
	 * @return string <ul><li>"m" = Male</li><li>"f" = Female</li></ul>
	 */
	public function getGender() {
		return $this->gender;
	}
	
	/**
	 * @return string Format: yyyy-mm-dd (1983-02-17)
	 */
	public function getBirthday() {
		return $this->birthday;
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
	 * @return string
	 */
	public function getMail() {
		return $this->mail;
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
	 * @return bool
	 */
	public function getNewsletterSubscription() {
		return (bool) $this->newsletter_subscription;
	}

	/**
	 * @param int $type <ul><li>ShopgateAddress::BOTH</li><li>ShopgateAddress::INVOICE</li><li>ShopgateAddress::DELIVERY</li></ul>
	 * @return ShopgateAddress[] List of customer's addresses, filtered by $type.
	 */
	public function getAddresses($type = ShopgateAddress::BOTH) {
		if (empty($this->addresses)) return array();
		
		$addresses = array();
		
		foreach ($this->addresses as $address) {
			if (($address->getAddressType() & $type) == $address->getAddressType()) {
				$addresses[] = $address;
			}
		}

		return $addresses;
	}
}

/**
 * Class ShopgateCustomerGroup
 */
class ShopgateCustomerGroup extends ShopgateContainer {
	protected $id;
	protected $name;

	##########
	# Setter #
	##########
	
	/**
	 * @param string $value
	 */
	public function setId($value) {
		$this->id = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setName($value) {
		$this->name = $value;
	}

	##########
	# Getter #
	##########
	
	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param ShopgateContainerVisitor $v
	 */
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitPlainObject($this);
	}
}

class ShopgateAddress extends ShopgateContainer {
	const MALE = "m";
	const FEMALE = "f";

	const INVOICE  = 0x01;
	const DELIVERY = 0x10;
	const BOTH     = 0x11;

	protected $id;
	protected $is_invoice_address;
	protected $is_delivery_address;

	protected $first_name;
	protected $last_name;

	protected $gender;
	protected $birthday;

	protected $company;
	protected $street_1;
	protected $street_2;
	protected $zipcode;
	protected $city;
	protected $country;
	protected $state;

	protected $phone;
	protected $mobile;
	protected $mail;
	
	protected $custom_fields;

	/**
	 * Checks if two ShopgateAddress objects are equal.
	 *
	 * Two addresses are equal when following fields contain the same value:
	 * 'gender','first_name','last_name','street_1','street_2','zipcode','city','country'
	 *
	 * @param ShopgateAddress $address
	 * @return bool
	 */
	public function equals(ShopgateAddress $address){
		$whiteList = array('gender','first_name','last_name','street_1','street_2','zipcode','city','country');
		return $this->compare($this, $address,$whiteList);
	}

	##########
	# Setter #
	##########

	/**
	 * @param string $value
	 */
	public function setId($value) {
		$this->id = $value;
	}

	/**
	 * @param int $value ShopgateAddress::BOTH or ShopgateAddress::INVOICE or ShopgateAddress::DELIVERY
	 */
	public function setAddressType($value) {
		$this->is_invoice_address  = (bool) ($value & self::INVOICE);
		$this->is_delivery_address = (bool) ($value & self::DELIVERY);
	}

	/**
	 * @param bool $value
	 */
	public function setIsInvoiceAddress($value) {
		$this->is_invoice_address = (bool) $value;
	}

	/**
	 * @param bool $value
	 */
	public function setIsDeliveryAddress($value) {
		$this->is_delivery_address = (bool) $value;
	}

	/**
	 * @param string $value
	 */
	public function setFirstName($value) {
		$this->first_name = $value;
	}

	/**
	 * @param string $value
	 */
		public function setLastName($value) {
		$this->last_name = $value;
	}

	/**
	 * @param string $value <ul><li>"m" = Male</li><li>"f" = Female</li></ul>
	 */
	public function setGender($value = null) {
		if (empty($value)) return;

		if (($value != self::MALE) && ($value != self::FEMALE)) {
			$this->gender = null;
		} else {
			$this->gender = $value;
		}
	}

	/**
	 * @param string $value Format: yyyy-mm-dd (1983-02-17)
	 */
	public function setBirthday($value) {
		if (empty($value)) {
			$this->birthday = null;
			return;
		}

		$matches = null;
		if (!preg_match('/^([0-9]{4}\-[0-9]{2}\-[0-9]{2})/', $value, $matches)) {
			$this->birthday = null;
		} else {
			$this->birthday = $matches[1];
		}
	}

	/**
	 * @param string $value
	 */
	public function setCompany($value) {
		$this->company = $value;
	}

	/**
	 * @param string $value
	 */
	public function setStreet1($value) {
		$this->street_1 = $value;
	}

	/**
	 * @param string $value
	 */
	public function setStreet2($value) {
		$this->street_2 = $value;
	}

	/**
	 * @param string $value
	 */
	public function setCity($value) {
		$this->city = $value;
	}

	/**
	 * @param string $value
	 */
	public function setZipcode($value) {
		$this->zipcode = $value;
	}

	/**
	 * @see http://en.wikipedia.org/wiki/ISO_3166-1#Current_codes
	 * @param string $value Country as ISO-3166-1
	 */
	public function setCountry($value) {
		$this->country = $value;
	}

	/**
	 * @see http://en.wikipedia.org/wiki/ISO_3166-2#Current_codes
	 * @param string $value State as ISO-3166-2
	 */
	public function setState($value) {
		$this->state = $value;
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
	 * @param string $value
	 */
	public function setMail($value) {
		$this->mail = $value;
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


	##########
	# Getter #
	##########

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	* @return bool
	*/
	public function getIsInvoiceAddress() { return (bool) $this->is_invoice_address; }

	/**
	 * @return bool
	 */
	public function getIsDeliveryAddress() { return (bool) $this->is_delivery_address; }

	/**
	 * @return int ShopgateAddress::BOTH or ShopgateAddress::INVOICE or ShopgateAddress::DELIVERY
	 */
	public function getAddressType() {
		return (int) (
			($this->getIsInvoiceAddress()  ? self::INVOICE  : 0) |
			($this->getIsDeliveryAddress() ? self::DELIVERY : 0)
		);
	}

	/**
	 * @return string
	 */
	public function getFirstName() {
		return $this->first_name;
	}

	/**
	 * @return string
	 */
	public function getLastName() {
		return $this->last_name;
	}

	/**
	 * @return string <ul><li>"m" = Male</li><li>"f" = Female</li></ul>
	 */
	public function getGender() {
		return $this->gender;
	}

	/**
	 * @return string Format: yyyy-mm-dd (1983-02-17)
	 */
	public function getBirthday() {
		return $this->birthday;
	}

	/**
	 * @return string
	 */
	public function getCompany() {
		return $this->company;
	}

	/**
	 * @return string
	 */
	public function getStreet1() {
		return $this->street_1;
	}

	/**
	 * @return string
	 */
	public function getStreet2() {
		return $this->street_2;
	}

	/**
	 * @return string
	 */
	public function getStreetName1() {
		return $this->splitStreetData($this->getStreet1(), "street");
	}
	
	/**
	 * @return string
	 */
	public function getStreetNumber1() {
		return $this->splitStreetData($this->getStreet1(), "number");
	}
	
	/**
	 * @return string
	 */
	public function getCity() {
		return $this->city;
	}

	/**
	 * @return string
	 */
	public function getZipcode() {
		return $this->zipcode;
	}

	/**
	 * @see http://en.wikipedia.org/wiki/ISO_3166-1#Current_codes
	 * @return string Country as ISO-3166-1
	 */
	public function getCountry() {
		return $this->country;
	}

	/**
	 * @see http://en.wikipedia.org/wiki/ISO_3166-2#Current_codes
	 * @return string State as ISO-3166-2
	 */
	public function getState() {
		return $this->state;
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
	 * @return string
	 */
	public function getMail() {
		return $this->mail;
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

	public function accept(ShopgateContainerVisitor $v) {
		$v->visitAddress($this);
	}
	
	/**
	 * @param string $street
	 * @param string $type [street|name]
	 *
	 * @return string
	 */
	protected function splitStreetData($street, $type = 'street') {
		$splittedArray = array();
		$street = trim($street);
		$street = str_replace("\n", '', $street);
		
		//contains only digits OR no digits at all --> don't split
		if (preg_match("/^[0-9]+$/i", $street)  || preg_match("/^[^0-9]+$/i", $street)) {
			return ($type == 'street') ? $street : "";
		}
		
		//number at the end ("Schlossstr. 10", "Schlossstr. 10a", "Schlossstr. 10a+b"...)
		if (preg_match("/^([^0-9]+)([0-9]+ ?[a-z]?([ \-\&\+]+[a-z])?)$/i", $street, $matches)) {
			return trim(($type == 'street') ? $matches[1] : $matches[2]);
		}
		
		//number at the end ("Schlossstr. 10-12", "Schlossstr. 10 & 12"...)
		if (preg_match("/^([^0-9]+)([0-9]+([ \-\&\+]+[0-9]+)?)$/i", $street, $matches)) {
			return trim(($type == 'street') ? $matches[1] : $matches[2]);
		}
		
		//number at the beginning (e.g. "2225 E. Bayshore Road", "2225-2227 E. Bayshore Road")
		if (preg_match("/^([0-9]+([ \-\&\+]+[0-9]+)?)([^0-9]+.*)$/i", $street, $matches)) {
			return trim(($type == 'street') ? $matches[3] : $matches[1]);
		}
		
		if(!preg_match("/^(.+)\s(.*[0-9]+.*)$/is", $street, $splittedArray)) {
			// for "My-Little-Street123"
			preg_match("/^(.+)([0-9]+.*)$/isU", $street, $splittedArray);
		}
		
		$value = $street;
		switch($type) {
			case 'street':
				if(isset($splittedArray[1])){
					$value = $splittedArray[1];
				}
				break;
			case 'number':
				if(isset($splittedArray[2])){
					$value = $splittedArray[2];
				}else{
					$value = "";
				}
				break;
		}
		
		return $value;
	}
}