<?php

/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2014 PrestaShop SA
* @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/

require_once(_PS_MODULE_DIR_.'prediggo/classes/DataExtractorToXML.php');

class CustomerExtractorToXML extends DataExtractorToXML
{
	/** @var array List of Prediggo configuration by shop */
	private $aPrediggoConfigs;

	/**
	  * Initialise the object variables
	  *
	  * @param string $sRepositoryPath path of the XML repository
	  * @param array $params Specific parameters of the object
	  */
	public function __construct($sRepositoryPath, $params, $bLogEnable)
	{
		$this->sRepositoryPath 	= $sRepositoryPath;
		$this->bLogEnable 		= (int)$bLogEnable;
		$this->_logs 			= array();
		$this->_errors 			= array();
		$this->_confirmations 	= array();
		$this->sEntity 			= 'user';
		$this->sFileNameBase 	= 'users';
		$this->sEntityRoot 		= 'users';

		$this->aPrediggoConfigs 	= $params['aPrediggoConfigs'];
	}

	/**
	  * Get the list of entities by a sql result
	  *
	  * @return Object SQL Result
	  */
	public function getEntities()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT `id_customer`, `id_shop`
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

		// Check if the customer has visited the website since a specific number of days $this->aPrediggoConfigs[(int)$aEntity['id_shop']]->nb_days_customer_last_visit_valide
		$aLastConnection = $oCustomer->getLastConnections();
		if($aLastConnection && is_array($aLastConnection) && count($aLastConnection) && $aLastConnection[0]['date_add'] < date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d')-((int)$this->aPrediggoConfigs[(int)$aEntity['id_shop']]->nb_days_customer_last_visit_valide), date('Y'))))
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

		$sCountry = Country::getIsoById((int)$oAddress->id_country);
		if(!empty($sCountry))
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