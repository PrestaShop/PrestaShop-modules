<?php

class SceauControl extends SceauDOMDocument
{

	private $fianetmodule = 'api_prestashop_sceau';
	private $fianetmoduleversion = '4.4';

	public function __construct()
	{
		@parent::__construct('1.0', 'UTF-8');
		@$this->root = $this->appendChild(new SceauXMLElement('control'));
		$this->root->setAttribute('fianetmodule', $this->fianetmodule);
		$this->root->setAttribute('version', $this->fianetmoduleversion);
		$this->root->setAttribute('sceaumodule', '2.4');
	}

	/**
	 * creates an object SceauCustomer representing the element <utilisateur> then adds id as a child of root, then adds the sub-children given in param, then returns the child
	 * 
	 * @param string $type
	 * @param string $civility
	 * @param string $lastname
	 * @param string $firstname
	 * @param string $email
	 * @param string $society
	 * @param string $phone_mobile
	 * @param string $phone_home
	 * @param string $phone_office
	 * @param string $fax_number
	 * @return SceauCustomer
	 */
	public function createCustomer($type, $civility, $lastname, $firstname, $email, $society = null, $phone_mobile = null, $phone_home = null, $phone_office = null, $fax_number = null)
	{
		$customer = $this->root->appendChild(new SceauCustomer());
		if ($type != '')
		{
			$customer->addAttribute('type', $type);
			$customer->addAttribute('qualite', SceauCustomer::TYPE_PARTICULIER);
		}
		$customer->createChild('nom', $lastname, array('titre' => $civility));
		$customer->createChild('prenom', $firstname);
		$customer->createChild('email', $email);
		if (!is_null($society))
			$customer->createChild('societe', $society);
		if (!is_null($phone_mobile))
			$customer->createChild('telmobile', $phone_mobile);
		if (!is_null($phone_home))
			$customer->createChild('telhome', $phone_home);
		if (!is_null($phone_office))
			$customer->createChild('teloffice', $phone_office);
		if (!is_null($fax_number))
			$customer->createChild('telfax', $fax_number);

		return $customer;
	}

	/**
	 * creates an object SceauCustomer representing the element <utilisateur type='facturation'> then adds id as a child of root, then adds the sub-children given in param, then returns the child
	 * 
	 * @param string $type
	 * @param string $civility
	 * @param string $lastname
	 * @param string $firstname
	 * @param string $email
	 * @param string $society
	 * @param string $phone_mobile
	 * @param string $phone_home
	 * @param string $phone_office
	 * @param string $fax_number
	 * @return SceauCustomer
	 */
	public function createInvoiceCustomer($civility, $lastname, $firstname, $email, $society = null, $phone_mobile = null, $phone_home = null, $phone_office = null, $fax_number = null)
	{
		return $this->createCustomer('facturation', $civility, $lastname, $firstname, $email, $society, $phone_mobile, $phone_home, $phone_office, $fax_number);
	}

	/**
	 * creates an object SceauCustomer representing the element <utilisateur type='livraison'> then adds id as a child of root, then adds the sub-children given in param, then returns the child
	 * 
	 * @param string $type
	 * @param string $civility
	 * @param string $lastname
	 * @param string $firstname
	 * @param string $email
	 * @param string $society
	 * @param string $phone_mobile
	 * @param string $phone_home
	 * @param string $phone_office
	 * @param string $fax_number
	 * @return SceauCustomer
	 */
	public function createDeliveryCustomer($civility, $lastname, $firstname, $email, $society = null, $phone_mobile = null, $phone_home = null, $phone_office = null, $fax_number = null)
	{
		return $this->createCustomer('livraison', $civility, $lastname, $firstname, $email, $society, $phone_mobile, $phone_home, $phone_office, $fax_number);
	}

	/**
	 * creates an object SceauCustomer representing the element <adresse> then adds id as a child of root, then adds the sub-children given in param, then returns the child
	 * 
	 * @param string $type has to be 'livraison' or 'facturation'
	 * @param string $street main street of the address
	 * @param string $zipcode
	 * @param string $city
	 * @param string $country
	 * @param string $secondary_street secondary street or complement of the main street
	 * @return SceauAddress
	 */
	public function createAddress($type, $street, $zipcode, $city, $country, $secondary_street = null)
	{
		$address = $this->root->appendChild(new SceauAddress());
		$address->addAttribute('type', $type);
		$address->createChild('rue1', $street);
		if (!is_null($secondary_street))
			$address->createChild('rue2', $secondary_street);
		$address->createChild('cpostal', $zipcode);
		$address->createChild('ville', $city);
		$address->createChild('pays', $country);

		return $address;
	}

	/**
	 * creates an object SceauCustomer representing the element <adresse type='facturation'> then adds id as a child of root, then adds the sub-children given in param, then returns the child
	 * 
	 * @param string $type has to be 'livraison' or 'facturation'
	 * @param string $street main street of the address
	 * @param string $zipcode
	 * @param string $city
	 * @param string $country
	 * @param string $secondary_street secondary street or complement of the main street
	 * @return SceauAddress
	 */
	public function createInvoiceAddress($street, $zipcode, $city, $country, $secondary_street = null)
	{
		return $this->createAddress('facturation', $street, $zipcode, $city, $country, $secondary_street);
	}

	/**
	 * creates an object SceauCustomer representing the element <adresse type='livraison'> then adds id as a child of root, then adds the sub-children given in param, then returns the child
	 * 
	 * @param string $type has to be 'livraison' or 'facturation'
	 * @param string $street main street of the address
	 * @param string $zipcode
	 * @param string $city
	 * @param string $country
	 * @param string $secondary_street secondary street or complement of the main street
	 * @return SceauAddress
	 */
	public function createDeliveryAddress($street, $zipcode, $city, $country, $secondary_street = null)
	{
		return $this->createAddress('livraison', $street, $zipcode, $city, $country, $secondary_street);
	}

	/**
	 * creates an object SceauOrderDetails representing the element <infocommande> then adds it as a child of root, then adds the sub-children given in param, then returns the child
	 * 
	 * @param string $refid order reference
	 * @param int $siteid merchant ID given by fianet
	 * @param float $amount payment amount
	 * @param string $currency 
	 * @param string $ip IP address of the customer that passed the order
	 * @param string $timestamp date of the order. Format has to be Y-m-d H:i:s
	 * @return SceauOrderDetails
	 */
	public function createOrderDetails($refid, $siteid, $amount, $currency, $ip, $timestamp, $lang = null)
	{
		$order_details = $this->root->appendChild(new SceauOrderDetails());
		$order_details->createChild('refid', $refid);
		$order_details->createChild('siteid', $siteid);
		$order_details->createChild('montant', $amount, array('devise' => $currency));
		$order_details->createChild('ip', $ip, array('timestamp' => $timestamp));
		if (!is_null($lang))
			$order_details->createChild('langue', $lang);
		return $order_details;
	}

	/**
	 * creates a SceauOrderProducts object representing element <produits>, adds it to the current element, then returns it
	 * 
	 * @return SceauOrderProducts
	 */
	public function createOrderProducts()
	{
		$product_list = $this->addChild(new SceauOrderProducts());
		return $product_list;
	}

	/**
	 * creates an object SceauXMLElement representing the element <paiement> then adds it as a child of root, then adds the sub-children given in param, then returns the child
	 * 
	 * @param string $type payment type
	 * @param string $name name of the card carrier if $type=cb or $type=cb en n fois
	 * @param string $cb_number number of the bank card if $type=cb or $type=cb en n fois
	 * @param string $date_valid validity date of the card if $type=cb or $type=cb en n fois. Format has to be mm/yyyy
	 * @param string $bin six firsts digits of the card number if $type=cb or $type=cb en n fois
	 * @param string $bin4 four firsts digits of the card number if $type=cb or $type=cb en n fois
	 * @param string $bin42 four firsts and two lats digits of the card number if $type=cb or $type=cb en n fois
	 * @return SceauXMLElement
	 */
	public function createPayment($type, $name = null, $cb_number = null, $date_valid = null, $bin = null, $bin4 = null, $bin42 = null)
	{
		$payment = $this->root->appendChild(new SceauXMLElement('paiement'));

		if (!is_null($cb_number) OR !is_null($date_valid))
			$hash = new HashMD5 ();

		$payment->createChild('type', $type);
		if (!is_null($name))
			$payment->createChild('nom', $name);
		if (!is_null($cb_number))
		{
			$hash_cb = $hash->hash($cb_number);
			$payment->createChild('numcb', $hash_cb);
		}
		if (!is_null($date_valid))
		{
			$hash_date = $hash->hash($date_valid);
			$payment->createChild('dateval', $hash_date);
		}
		if (!is_null($bin))
			$payment->createChild('bin', $bin);
		if (!is_null($bin4))
			$payment->createChild('bin4', $bin4);
		if (!is_null($bin42))
			$payment->createChild('bin42', $bin42);

		return $payment;
	}

}