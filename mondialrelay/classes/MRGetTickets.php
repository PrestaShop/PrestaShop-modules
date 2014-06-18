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
*  @version  Release: $Revision: 16117 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/*
 * Interface
 */
require_once(dirname(__FILE__).'/IMondialRelayWSMethod.php');

/*
 * Allow to retrieve tickets - 'WSI2_GetEtiquettes'
 */
class MRGetTickets implements IMondialRelayWSMethod
{
	private $_fields = array(
		'list' => array(
			'Enseigne'			=> array(
						'required'				=> true,
						'value'						=> '',
						'regexValidation' => '#^[0-9A-Z]{2}[0-9A-Z ]{6}$#'),
			'Expeditions'		=> array(
						'required'				=> true,
						'value'						=> '',
						'regexValidation' => '#^[0-9]{8}(;[0-9]{8})*$#'),
			'Langue'				=> array(
						'required'				=> true,
						'value'						=> '',
						'regexValidation' => '#^[A-Z]{2}$#'),
			'Security'			=> array(
						'required'				=> true,
						'value'						=> '',
						'regexValidation' => '#^[0-9A-Z]{32}$#')));
	
	private $_detailedExpeditionList = array();
	private $_webServiceKey = '';
	private $_mondialrelay = null;
	
	private $_resultList = array(
		'error' => array(),
		'success' => array());
	
	private $_webserviceURL;
	
	public function __construct($params, $object)	
	{
		$this->_mondialrelay = $object;
		$this->_detailedExpeditionList = $params['detailedExpeditionList'];
		$this->_webServiceKey = $this->_mondialrelay->account_shop['MR_KEY_WEBSERVICE'];
		$this->_webserviceURL = MondialRelay::MR_URL.'webservice/Web_Services.asmx?WSDL';
	}
	
	public function __destruct()
	{
		unset($this->_mondialrelay);
	}
	
	public function init()
	{
		$this->_fields['list']['Enseigne']['value'] = $this->_mondialrelay->account_shop['MR_ENSEIGNE_WEBSERVICE'];
		$this->_fields['list']['Langue']['value'] = $this->_mondialrelay->account_shop['MR_LANGUAGE'];
		
		foreach ($this->_detailedExpeditionList as $detailedExpedition)
		{
			// Storage temporary
			$base = $this->_fields;
			$tmp = &$base['list'];
			
			$tmp['Expeditions']['value'] = $detailedExpedition['expeditionNumber'];
			$this->_fieldsList[] = $base;
		}
		$this->_generateMD5SecurityKey();
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
					$valueDetailed['value'] = Tools::strtoupper($valueDetailed['value']);
					if (preg_match($valueDetailed['regexValidation'], $valueDetailed['value'], $matches))
						$concatenationValue .= $valueDetailed['value'];
					elseif ((!Tools::strlen($valueDetailed['value']) && $valueDetailed['required']) || Tools::strlen($valueDetailed['value']))
					{
						$error = $this->_mondialrelay->l('This key').' ['.$paramName.'] '.$this->_mondialrelay->l('hasn\'t a valide value format').' : '.$valueDetailed['value'];
						$id_order = $this->_getOrderIdWithExpeditionNumber($rootCase['list']['Expeditions']['value']);
						$this->_resultList['error'][$id_order][] = $error;
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
	 * Update the history tables
	 */
	private function _updateTable($id_order, $expeditionNumber, $URLA4, $URLA5, &$success)
	{
		$query = '
			SELECT id FROM `'._DB_PREFIX_.'mr_history`
			WHERE `order`='.(int)$id_order;
		
		$row = Db::getInstance()->getRow($query);
		if ($row)
		{
			$query = '
				UPDATE `'._DB_PREFIX_.'mr_history`
  			SET 
  				`exp` = \''.(int)$expeditionNumber.'\',
  				`url_a4` = \''.pSQL((string)$URLA4).'\',
  				`url_a5` = \''.pSQL((string)$URLA5).'\'
  			WHERE `order` = '.(int)$id_order;
		}
		else
		{
			$query = '
				INSERT INTO '._DB_PREFIX_.'mr_history
				(`order`, `exp`, `url_a4`, `url_a5`)
				VALUES (
					'.(int)$id_order.',
					'.(int)$expeditionNumber.',
					\''.pSQL((string)$URLA4).'\',
					\''.pSQL((string)$URLA5).'\')';
		}
		Db::getInstance()->execute($query);
		$success['id_mr_history'] = isset($row['id']) ? $row['id'] : Db::getInstance()->Insert_ID();
	}
	
	/*
	 * Manage the return value of the webservice, handle the errors or build the
	 * succeed message
	 */
	private function _parseResult($client, $result, $params)
	{
		$errors = array();
		$success = array();
		
		$id_order = $this->_getOrderIdWithExpeditionNumber($params['Expeditions']);
		$result = $result->WSI2_GetEtiquettesResult;
		if (($errorNumber = $result->STAT) != 0)
		{
			$errors[] = $this->_mondialrelay->l('There is an error number : ').$errorNumber;
			$errors[] = $this->_mondialrelay->l('Details : ').
				$this->_mondialrelay->getErrorCodeDetail($errorNumber);
		}
		else
		{
			$baseURL = 'http://www.mondialrelay.fr';
			$URLPDF_A4 = $baseURL.$result->URL_PDF_A4;
			$URLPDF_A5 = $baseURL.$result->URL_PDF_A5;
			
			$success['id_order'] = $id_order;
			$success['expeditionNumber'] = $params['Expeditions'];
			$success['URLPDF_A4'] = $URLPDF_A4;
			$success['URLPDF_A5'] = $URLPDF_A5;
			$this->_updateTable($id_order, $params['Expeditions'], $URLPDF_A4, $URLPDF_A5, $success);
		}
		$this->_resultList['error'][$id_order] = $errors;
		$this->_resultList['success'][$id_order] = $success;
	}
	
	/*
	 * Get the order id using the expedition number
	 */
	private function _getOrderIdWithExpeditionNumber($expeditionNumber)
	{
		foreach ($this->_detailedExpeditionList as $detailedExpedition)
			if ($detailedExpedition['expeditionNumber'] == $expeditionNumber)
				return $detailedExpedition['id_order'];
		return 0;
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
				$result = $client->WSI2_GetEtiquettes($params);
				$this->_parseResult($client, $result, $params);
			}
			unset($client);
		}
		else
			throw new Exception($this->_mondialrelay->l('The Mondial Relay webservice isn\'t currently reliable'));
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