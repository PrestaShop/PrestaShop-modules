<?php
/*
* 2007-2012 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 8783 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/*
 * Interface
 */
require_once(dirname(__FILE__).'/IMondialRelayWSMethod.php');

/*
 * Allow to retrieve relay point details - 'WSI2_DetailPointRelais'
 */
class MRRelayDetail implements IMondialRelayWSMethod
{
	private $_fields = array(
		'list' => array(
			'Enseigne'			=> array(
						'required'				=> true,
						'value'						=> '',
						'regexValidation' => '#^[0-9A-Z]{2}[0-9A-Z ]{6}$#'),
			'Num'		=> array(
						'required'				=> true,
						'value'						=> '',
						'regexValidation' => '#^[0-9]{6}$#'),
			'Pays'				=> array(
						'required'				=> true,
						'value'						=> '',
						'regexValidation' => '#^[A-Z]{2}$#'),
			'Security'			=> array(
						'required'				=> true,
						'value'						=> '',
						'regexValidation' => '#^[0-9A-Z]{32}$#')));
	
	private $_relayPointNumList = array(); 
	private $_id_address_delivery = 0;
	private $_webServiceKey = '';
	private $_mondialrelay = null;
	private $_markCode = '';
	
	private $_resultList = array(
		'error' => array(),
		'success' => array());
	
	private $_webserviceURL = '';
	
	public function __construct($params, $object)	
	{
		$this->_mondialrelay = $object;

		$this->_relayPointNumList = $params['relayPointNumList'];
		$this->_id_address_delivery = (int)($params['id_address_delivery']);
		$this->_webServiceKey = $this->_mondialRelay->account_shop['MR_KEY_WEBSERVICE'];
		$this->_markCode = $this->_mondialRelay->account_shop['MR_CODE_MARQUE'];
		$this->_webserviceURL = MondialRelay::MR_URL.'webservice/Web_Services.asmx?WSDL';
	}
	
	public function __destruct()
	{
		unset($this->_mondialrelay);
	}
	
	public function init()
	{
		$address = new Address($this->_id_address_delivery);
		
		if (!$address)
			throw new Exception($this->_mondialrelay->l('Customer address can\'t be found'));
		
		$this->_fields['list']['Enseigne']['value'] = $this->_mondialRelay->account_shop['MR_ENSEIGNE_WEBSERVICE'];
		$this->_fields['list']['Pays']['value'] = Country::getIsoById($address->id_country);
		
		foreach ($this->_relayPointNumList as $num)
		{
			// Storage temporary
			$base = $this->_fields;
			$tmp = &$base['list'];
			
			$tmp['Num']['value'] = $num;
			$this->_fieldsList[] = $base;
		}
		$this->_generateMD5SecurityKey();
		unset($address);
	}
	
	/*
	 * Generate the MD5 key for each param list
	 */
	private function _generateMD5SecurityKey()
	{
		// RootCase is the array case where the main information are stored
		// it's an array containing id_mr_selected and an array with the necessary fields
		foreach ($this->_fieldsList as &$rootCase)
		{
			$concatenationValue = '';
			foreach ($rootCase['list'] as $paramName => &$valueDetailed)
				if ($paramName != 'Texte' && $paramName != 'Security')
				{
					// Mac server make an empty string instead of a cleaned string
					// TODO : test on windows and linux server
					$cleanedString = MRTools::removeAccents($valueDetailed['value']);
					$valueDetailed['value'] = !empty($cleanedString) ? Tools::strtoupper($cleanedString) : Tools::strtoupper($valueDetailed['value']);
					
					// Call a pointer function if exist to do different test
					if (isset($valueDetailed['methodValidation']) && method_exists('MRTools', $valueDetailed['methodValidation']) && isset($valueDetailed['params']) && MRTools::$valueDetailed['methodValidation']($valueDetailed['value'], $valueDetailed['params']))
						$concatenationValue .= $valueDetailed['value'];
					// Use simple Regex test given by MondialRelay
					else if (isset($valueDetailed['regexValidation']) && preg_match($valueDetailed['regexValidation'], $valueDetailed['value'], $matches))
						$concatenationValue .= $valueDetailed['value'];
					// If the key is required, we set an error, else it's skipped 
					elseif ((!Tools::strlen($valueDetailed['value']) && $valueDetailed['required']) || Tools::strlen($valueDetailed['value']))
					{
						if (empty($valueDetailed['value']))
							$error = $this->_mondialrelay->l('This key').' ['.$paramName.'] '.$this->_mondialrelay->l('is empty and need to be filled');
						else
							$error = 'This key ['.$paramName.'] hasn\'t a valid value format : '.$valueDetailed['value']; 
						$this->_resultList['error'][$rootCase['list']['Num']['value']] = $error;
					}
				}
			$concatenationValue .= $this->_webServiceKey;
			$rootCase['list']['Security']['value'] = Tools::strtoupper(md5($concatenationValue));
		}
	}

	/*
	 * Get the values with associated fields name
	 * @fields : array containing multiple values information
	 */
	private function _getSimpleParamArray($fields)
	{
		$params = array();
		
		foreach ($fields as $keyName => $valueDetailed)
			$params[$keyName] = $valueDetailed['value'];
		return $params;
	}
	
	/*
	 * Manage the return value of the webservice, handle the errors or build the
	 * succeed message
	 */
	private function _parseResult($client, $result, $params)
	{
		$errors = array();
		$result = $result->WSI2_DetailPointRelaisResult;
		if (($errorNumber = $result->STAT) != 0)
		{
			$errors[] = $this->_mondialrelay->l('There is an error number : ').$errorNumber;
			$errors[] = $this->_mondialrelay->l('Details : ').
				$this->_mondialrelay->getErrorCodeDetail($errorNumber);
		}
		else
		{
			$HDayList = array(
				'Horaires_Lundi' => $this->_mondialrelay->l('Monday'),
				'Horaires_Mardi' => $this->_mondialrelay->l('Tuesday'),
				'Horaires_Mercredi' => $this->_mondialrelay->l('Wednesday'),
				'Horaires_Jeudi' => $this->_mondialrelay->l('Thursday'),
				'Horaires_Vendredi' => $this->_mondialrelay->l('Friday'),
				'Horaires_Samedi' => $this->_mondialrelay->l('Saturday'),
				'Horaires_Dimanche' => $this->_mondialrelay->l('Sunday'));
		
			$orderedDate = array();
			// Format hour properly
			$priority = 0;
			foreach ($HDayList as $day => $tradDayName)
			{
				$mr_day = $result->{$day};
				foreach ($mr_day['string'] as $num => &$value)
					if ($value == '0000')
						$value = '';
					else
						$value = implode('h', str_split($value, 2));
				$orderedDate[$priority++] = array('name' => $tradDayName, 'list' => $mr_day);
				unset($result[$day]);
			}
			$result->orderedDate = $orderedDate;
			$this->_resultList['success'][$result->Num] = $result;
		}	
		$this->_resultList['error'][] = $errors;
	}
	
	/*
	* Send one or multiple request to the webservice
	*/
	public function send()
	{
		if ($client = new SoapClient($this->_webserviceURL))
		{
			$client->soap_defencoding = 'UTF-8';
			$client->decode_utf8 = false;
			
			foreach ($this->_fieldsList as $rootCase)
			{
				$params = $this->_getSimpleParamArray($rootCase['list']);
				$result = $client->WSI2_DetailPointRelais($params);				
				$this->_parseResult($client, $result, $params);
			}
			unset($client);
		}
		else
			throw new Exception($this->_mondialrelay->l('The Mondial Relay webservice isn\'t currently reliable'));
	}
	
	/*
	** Generate a list of perma link
	*/
	public static function getPermaLink($relayList, $id_address_delivery)
	{
		if (!($address = new Address($id_address_delivery)))
			return array();

		$mondialrelay = new MondialRelay();
		
		$list = array();
		$iso = Tools::strtoupper(Country::getIsoById($address->id_country));
		$ens = $mondialrelay->account_shop['MR_ENSEIGNE_WEBSERVICE'].$mondialrelay->account_shop['MR_CODE_MARQUE'];
		$url = 'http://www.mondialrelay.com/public/permanent/details_relais.aspx?ens='.$ens;
		foreach ($relayList as $relayNum)
		{
			$crc = Tools::strtoupper(md5('<'.Tools::strtoupper($ens).'>'.$relayNum.$iso.'<'.$mondialrelay->account_shop['MR_KEY_WEBSERVICE'].'>'));
			$list[$relayNum] = $url.'&num='.$relayNum.'&pays='.$iso.'&crc='.$crc;
		}
		unset($address, $mondialrelay);
		return $list;
	}
	
	/*
	 * Get the values with associated fields name
	 */
	public function getFieldsList()
	{
		return $this->_fieldsList['list'];
	}
	
	/*
	 * Get the result of one or multiple send request
	 */
	public function getResult()
	{
		return $this->_resultList;
	}
}