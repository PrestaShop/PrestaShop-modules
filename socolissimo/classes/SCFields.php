<?php

require_once(dirname(__FILE__).'/SCError.php');

// Inherit of Socolissimo to have acces to the module method and objet model method
class SCFields extends SCError
{
	// Restriction
	const REQUIRED = 1;
	const NOT_REQUIRED = 2;
	const UNKNOWN = 3;	// Not specified on the documentation
	const IGNORED = 4;
	const ALL = 5;

	// Delivery type
	const HOME_DELIVERY = 0;
	const RELAY_POINT = 1;
	const API_REQUEST = 2;

	public $context;

	// List of the available restriction type
	public $restriction_list = array(
		SCFields::REQUIRED,
		SCFields::NOT_REQUIRED,
		SCFields::UNKNOWN,
		SCFields::IGNORED,
		SCFields::ALL
	);

	// List of the available delivery type
	public $delivery_list = array(
		SCFields::HOME_DELIVERY => array('DOM', 'RDV'),
		SCFields::RELAY_POINT => array('BPR', 'A2P', 'MRL', 'CIT', 'ACP', 'CDI'),
		SCFields::API_REQUEST => array('API')
	);

	// By default, use the home delivery
	public $delivery_mode = SCFields::HOME_DELIVERY;

	// Available returned fields for HOME_DELIVERY and RELAY POINT, fields ordered.
	private $fields = array(
		SCFields::HOME_DELIVERY => array(
			'PUDOFOID' => SCFields::REQUIRED,
			'CENAME' => SCFields::REQUIRED,
			'DYPREPARATIONTIME' => SCFields::REQUIRED,
			'DYFORWARDINGCHARGES'=> SCFields::REQUIRED,
			'TRCLIENTNUMBER' => SCFields::UNKNOWN,
			'TRORDERNUMBER' => SCFields::UNKNOWN,
			'ORDERID' => SCFields::REQUIRED,
			'CECIVILITY' => SCFields::REQUIRED,
			'CEFIRSTNAME' => SCFields::REQUIRED,
			'CECOMPANYNAME' => SCFields::NOT_REQUIRED,
			'CEADRESS1' => SCFields::UNKNOWN,
			'CEADRESS2' => SCFields::UNKNOWN,
			'CEADRESS3' => SCFields::REQUIRED,
			'CEADRESS4' => SCFields::UNKNOWN,
			'CEZIPCODE' => SCFields::REQUIRED,
			'CETOWN' => SCFields::REQUIRED,
			'DELIVERYMODE' => SCFields::REQUIRED,
			'CEDELIVERYINFORMATION' => SCFields::UNKNOWN,
			'CEEMAIL' => SCFields::REQUIRED,
			'CEPHONENUMBER' => SCFields::NOT_REQUIRED,
			'CEDOORCODE1' => SCFields::UNKNOWN,
			'CEDOORCODE2' => SCFields::UNKNOWN,
			'CEENTRYPHONE' => SCFields::UNKNOWN,
			'TRPARAMPLUS' => SCFields::UNKNOWN,
			'TRADERCOMPANYNAME' => SCFields::REQUIRED,
			'ERRORCODE' => SCFields::UNKNOWN,

			// Error required if specific error exist (handle it has not required for now)
			'ERR_CENAME' => SCFields::NOT_REQUIRED,
			'ERR_CEFIRSTNAME' => SCFields::NOT_REQUIRED,
			'ERR_CECOMPANYNAME' => SCFields::NOT_REQUIRED,
			'ERR_CEADRESS1' => SCFields::NOT_REQUIRED,
			'ERR_CEADRESS2' => SCFields::NOT_REQUIRED,
			'ERR_CEADRESS3' => SCFields::NOT_REQUIRED,
			'ERR_CEADRESS4' => SCFields::NOT_REQUIRED,
			'ERR_CETOWN' => SCFields::NOT_REQUIRED,
			'ERR_CEDOORCODE1' => SCFields::NOT_REQUIRED,
			'ERR_CEDOORCODE2' => SCFields::NOT_REQUIRED,
			'ERR_CEENTRYPHONE' => SCFields::NOT_REQUIRED,
			'ERR_CEDELIVERYINFORMATION' => SCFields::NOT_REQUIRED,
			'ERR_CEEMAIL' => SCFields::NOT_REQUIRED,
			'ERR_CEPHONENUMBER' => SCFields::NOT_REQUIRED,
			'ERR_TRCLIENTNUMBER' => SCFields::NOT_REQUIRED,
			'ERR_TRORDERNUMBER' => SCFields::NOT_REQUIRED,
			'ERR_TRPARAMPLUS' => SCFields::NOT_REQUIRED,
			'ERR_CECIVILITY' => SCFields::NOT_REQUIRED,
			'ERR_DYWEIGHT' => SCFields::NOT_REQUIRED,
			'ERR_DYPREPARATIONTIME' => SCFields::NOT_REQUIRED,
			'TRRETURNURLKO' => SCFields::REQUIRED,

			'SIGNATURE' => SCFields::IGNORED
		),
		SCFields::RELAY_POINT => array(
			'PUDOFOID' => SCFields::REQUIRED,
			'CENAME' => SCFields::REQUIRED,
			'DYPREPARATIONTIME' => SCFields::REQUIRED,
			'DYFORWARDINGCHARGES' => SCFields::REQUIRED,
			'TRCLIENTNUMBER' => SCFields::UNKNOWN,
			'TRORDERNUMBER' => SCFields::UNKNOWN,
			'ORDERID' => SCFields::REQUIRED,
			'CECIVILITY' => SCFields::REQUIRED,
			'CEFIRSTNAME' => SCFields::REQUIRED,
			'CECOMPANYNAME' => SCFields::NOT_REQUIRED,
			'DELIVERYMODE' => SCFields::REQUIRED,
			'PRID' => SCFields::REQUIRED,
			'PRNAME' => SCFields::REQUIRED,
			'PRCOMPLADRESS' => SCFields::UNKNOWN,
			'PRADRESS1' => SCFields::REQUIRED,
			'PRADRESS2' => SCFields::UNKNOWN,
			'PRZIPCODE' => SCFields::REQUIRED,
			'PRTOWN' => SCFields::REQUIRED,
			'LOTACHEMINEMENT' => SCFields::UNKNOWN,
			'DISTRIBUTIONSORT' => SCFields::UNKNOWN,
			'VERSIONPLANTRI' => SCFields::UNKNOWN,
			'CEEMAIL' => SCFields::REQUIRED,
			'CEPHONENUMBER' => SCFields::REQUIRED,
			'TRPARAMPLUS' => SCFields::UNKNOWN,
			'TRADERCOMPANYNAME' => SCFields::REQUIRED,
			'ERRORCODE' => SCFields::UNKNOWN,

			// Error required if specific error exist (handle it has not required for now)
			'ERR_CENAME' => SCFields::NOT_REQUIRED,
			'ERR_CEFIRSTNAME' => SCFields::NOT_REQUIRED,
			'ERR_CECOMPANYNAME' => SCFields::NOT_REQUIRED,
			'ERR_CEADRESS1' => SCFields::NOT_REQUIRED,
			'ERR_CEADRESS2' => SCFields::NOT_REQUIRED,
			'ERR_CEADRESS3' => SCFields::NOT_REQUIRED,
			'ERR_CEADRESS4' => SCFields::NOT_REQUIRED,
			'ERR_CETOWN' => SCFields::NOT_REQUIRED,
			'ERR_CEDOORCODE1' => SCFields::NOT_REQUIRED,
			'ERR_CEDOORCODE2' => SCFields::NOT_REQUIRED,
			'ERR_CEENTRYPHONE' => SCFields::NOT_REQUIRED,
			'ERR_CEDELIVERYINFORMATION' => SCFields::NOT_REQUIRED,
			'ERR_CEEMAIL' => SCFields::NOT_REQUIRED,
			'ERR_CEPHONENUMBER' => SCFields::NOT_REQUIRED,
			'ERR_TRCLIENTNUMBER' => SCFields::NOT_REQUIRED,
			'ERR_TRORDERNUMBER' => SCFields::NOT_REQUIRED,
			'ERR_TRPARAMPLUS' => SCFields::NOT_REQUIRED,
			'ERR_CECIVILITY' => SCFields::NOT_REQUIRED,
			'ERR_DYWEIGHT' => SCFields::NOT_REQUIRED,
			'ERR_DYPREPARATIONTIME' => SCFields::NOT_REQUIRED,
			'TRRETURNURLKO' => SCFields::REQUIRED,

			'SIGNATURE' => SCFields::IGNORED
		),
		SCFields::API_REQUEST => array(
			'pudoFOId' => SCFields::REQUIRED,
			'ceName' => SCFields::NOT_REQUIRED,
			'dyPreparationTime' => SCFields::NOT_REQUIRED,
			'dyForwardingCharges' => SCFields::REQUIRED,
			'trClientNumber' => SCFields::NOT_REQUIRED,
			'trOrderNumber' => SCFields::NOT_REQUIRED,
			'orderId' => SCFields::REQUIRED,
			'numVersion' => SCFields::REQUIRED,
			'ceCivility' => SCFields::NOT_REQUIRED,
			'ceFirstName' => SCFields::NOT_REQUIRED,
			'ceCompanyName' => SCFields::NOT_REQUIRED,
			'ceAdress1' => SCFields::NOT_REQUIRED,
			'ceAdress2' => SCFields::NOT_REQUIRED,
			'ceAdress3' => SCFields::NOT_REQUIRED,
			'ceAdress4' => SCFields::NOT_REQUIRED,
			'ceZipCode' => SCFields::NOT_REQUIRED,
			'ceTown' => SCFields::NOT_REQUIRED,
			'ceEntryPhone' => SCFields::NOT_REQUIRED,
			'ceDeliveryInformation' => SCFields::NOT_REQUIRED,
			'ceEmail' => SCFields::NOT_REQUIRED,
			'cePhoneNumber' => SCFields::NOT_REQUIRED,
			'ceDoorCode1' => SCFields::NOT_REQUIRED,
			'ceDoorCode2' => SCFields::NOT_REQUIRED,
			'dyWeight' => SCFields::NOT_REQUIRED,
			'trFirstOrder' => SCFields::NOT_REQUIRED,
			'trParamPlus' => SCFields::NOT_REQUIRED,
			'trReturnUrlKo' => SCFields::REQUIRED,
			'trReturnUrlOk' => SCFields::NOT_REQUIRED
		)

	);

	public function __construct($delivery = 'DOM')
	{
		parent::__construct();

		include(dirname(__FILE__).'/../backward_compatibility/backward.php');

		$this->setDeliveryMode($delivery);
	}

	/**
	 * Check if the field exist for Socolissimp
	 *
	 * @param $name
	 * @return bool
	 */
	public function isAvailableFields($name)
	{
		return array_key_exists(strtoupper(trim($name)), $this->fields[$this->delivery_mode]);
	}

	/**
	 * Get field for a given restriction
	 *
	 * @param int $type
	 * @return mixed
	 */
	public function getFields($restriction = SCFields::ALL)
	{
		$tab = array();

		if (in_array($restriction, $this->restriction_list))
		{
			foreach($this->fields[$this->delivery_mode] as $key => $value)
				if ($value == $restriction || $restriction == SCFields::ALL)
					$tab[] = $key;
		}

		return $tab;
	}

	/**
	 * Check if the fields is required
	 *
	 * @param $name
	 * @return bool
	 */
	public function isRequireField($name)
	{
		return (in_array(strtoupper($name), $this->fields[$this->delivery_mode]) &&
			$this->fields[$this->delivery_mode] == SCFields::REQUIRED);
	}


	/**
	 * Set delivery mode
	 *
	 * @param $delivery
	 * @return bool
	 */
	public function setDeliveryMode($delivery)
	{
		if ($delivery)
		{
			foreach($this->delivery_list as $delivery_mode => $list)
			{
				if (in_array($delivery, $list))
				{
					$this->delivery_mode = $delivery_mode;
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Check if the returned key is proper to the generated one.
	 *
	 * @param $key
	 * @param $params
	 * @return bool
	 */
	public function isCorrectSignKey($sign, $params)
	{
		$tab = array();
		foreach($this->fields[$this->delivery_mode] as $key => $value)
		{
			if ($value == SCFields::IGNORED)
				continue;

			$key = trim($key);
			if (isset($params[$key]))
				$tab[$key] = $params[$key];
		}
		return ($sign == $this->generateKey($tab));
	}
}