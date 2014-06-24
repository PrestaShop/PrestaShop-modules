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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @version  Release: $Revision: 16986 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
require_once(dirname(__FILE__).'/IMondialRelayWSMethod.php');

/*
 * Allow to create tickets - 'WSI2_CreationEtiquette'
 */
class MRDownloadPDF implements IMondialRelayWSMethod
{
	public $class_name = __CLASS__;

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


	private $_mondialrelay = null;
	private $_fieldsList = array();
	private $_webServiceKey =	'';
	private $_markCode = '';
	private $Expeditions = '';

	private $_resultList = array(
		'error' => array(),
		'success' => array());

	private $_webserviceURL = '';

	public function __construct($params, $object)
	{
		$this->Expeditions = $params['Expeditions'];
		$this->_mondialrelay = $object;
		$this->_webServiceKey = $this->_mondialrelay->account_shop['MR_KEY_WEBSERVICE'];
		$this->_markCode = $this->_mondialrelay->account_shop['MR_CODE_MARQUE'];
		$this->class_name = Tools::strtolower($this->class_name);
		$this->_webserviceURL = MondialRelay::MR_URL.'webservice/Web_Services.asmx?WSDL';
	}

	public function __destruct()
	{
		unset($this->_mondialrelay);
	}

	/*
	 * Initiate the data needed to be send properly
	 * Can manage a list of data for multiple request
	 */
	public function init() 
	{
		$this->_fields['list']['Enseigne']['value'] = $this->_mondialrelay->account_shop['MR_ENSEIGNE_WEBSERVICE'];
		$this->_fields['list']['Expeditions']['value'] = $this->Expeditions;
		$this->_fields['list']['Langue']['value'] = $this->_mondialrelay->account_shop['MR_LANGUAGE'];
		$this->_fieldsList[] = $this->_fields;
		$this->_generateMD5SecurityKey();
	}

	/*
	 * Generate the MD5 key for each param list
	 */
	private function _generateMD5SecurityKey()
	{
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
						$this->_resultList['error'][] = $error;
					}
				}
			$concatenationValue .= $this->_webServiceKey;
			$rootCase['list']['Security']['value'] = Tools::strtoupper(md5($concatenationValue));	
		}
	}

	/*
	 * Manage the return value of the webservice, handle the errors or build the
	 * succeed message
	 */
	private function _parseResult($client, $result, $params)
	{
		$errors = &$this->_resultList['error'][];
		$success = &$this->_resultList['success'][];
		$result = $result->WSI2_GetEtiquettesResult;
		if (($errorNumber = $result->STAT) != 0)
		{
			$errors[] = $this->_mondialrelay->l('There is an error number : ', $this->class_name).$errorNumber;
			$errors[] = $this->_mondialrelay->l('Details : ', $this->class_name).
				$this->_mondialrelay->getErrorCodeDetail($errorNumber);
		}
		else
		{
			$baseURL = 'http://www.mondialrelay.fr';
			$success['URL_PDF_A4'] = $baseURL.$result->URL_PDF_A4;
			$success['URL_PDF_A5'] = $baseURL.$result->URL_PDF_A5;
			$success['URL_PDF_10x15'] = $baseURL.str_replace('format=A4', 'format=10x15', $result->URL_PDF_A4);
		}
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
	 * Return the fields list
	 */
	public function getFieldsList()
	{
		return $this->_fieldsList['list'];
	}

	/*
	 * Return the result of one or multiple sent requests
	 */
	public function getResult()
	{
		return $this->_resultList;
	}
	
	private function _getSimpleParamArray($fields)
	{
		$params = array();
		
		foreach ($fields as $keyName => $valueDetailed)
			$params[$keyName] = $valueDetailed['value'];
		return $params;
	}
}
