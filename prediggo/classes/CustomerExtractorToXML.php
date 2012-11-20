<?php

/**
 * @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
 * @copyright Croissance NET
 * @version 1.0
 */

require_once(_PS_MODULE_DIR_.'prediggo/classes/DataExtractorToXML.php');

class CustomerExtractorToXML extends DataExtractorToXML
{
	/** @var integer Number of days to define that a customer can be exported since its last visit */
	private $nbDaysCustomerValid;

	/**
	  * Initialise the object variables
	  *
	  * @param string $sRepositoryPath path of the XML repository
	  * @param array $params Specific parameters of the object
	  */
	public function __construct($sRepositoryPath, $params)
	{
		$this->sRepositoryPath = $sRepositoryPath;
		$this->_logs = array();
		$this->_errors = array();
		$this->_confirmations = array();
		$this->sEntity = 'user';
		$this->sFileNameBase = 'users';
		$this->sEntityRoot = 'users';

		$this->nbDaysCustomerValid = (int)$params['nbDaysCustomerValid'];
	}

	/**
	  * Get the list of entities by a sql result
	  *
	  * @return Object SQL Result
	  */
	public function getEntities()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT `id_customer`
		FROM `'._DB_PREFIX_.'customer`
		ORDER BY `id_customer` ASC', false);
	}

	/**
	  * Convert the entities data into an xml object and return the xml object as a string
	  *
	  * @param array $aEntity Entity data
	  */
	public function formatEntityToXML($aEntity)
	{
		$dom = new DOMDocument('1.0', 'utf-8');
		// Set the root of the XML
		$root = $dom->createElement($this->sEntity);
		$dom->appendChild($root);

		$oCustomer = new Customer((int)$aEntity['id_customer']);

		// Check if the customer has visited the website since a specific number of days $this->nbDaysCustomerValid
		$aLastConnection = $oCustomer->getLastConnections();
		if($aLastConnection[0]['date_add'] < date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d')-((int)$this->nbDaysCustomerValid), date('Y'))))
		{
			$this->nbEntitiesTreated--;
			$this->nbEntities--;
			return ' ';
		}
		unset($aLastConnection);

		$id = $dom->createElement('id', (int)$oCustomer->id);
		$root->appendChild($id);

		if(($sBirthday = strtotime($oCustomer->birthday))
		&& !empty($sBirthday))
		{
			$dobyear = $dom->createElement('dobyear', date('Y', $sBirthday));
			$root->appendChild($dobyear);
		}

		$gender = $dom->createElement('gender', ((int)$oCustomer->id_gender == 1 ? 'male' : 'female'));
		$root->appendChild($gender);

		$oAddress = new Address((int)Address::getFirstCustomerAddressId((int)$oCustomer->id));

		unset($oCustomer);

		if(($sLocation = trim($oAddress->city))
		&& !empty($sLocation))
		{
			$location = $dom->createElement('location', $sLocation);
			$root->appendChild($location);
		}

		if(($sCountry = Country::getIsoById((int)$oAddress->id_country))
		&& !empty($sCountry))
		{
			$country = $dom->createElement('country', $sCountry);
			$root->appendChild($country);
		}

		$sReturn = $dom->saveHTML();

		unset($oAddress);
		unset($dom);

		return $sReturn;
	}
}