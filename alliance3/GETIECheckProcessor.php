<?php
/*
* 2007-2013 PrestaShop
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
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class ECheckProcessor {

	public $debug;
	public $debugXML;
	public $XML;

	public function __construct($OPTIONS,$soapClient,$debug=false)
	{
		$this->UserName = $OPTIONS['GETI']['UserName'];
		$this->Password = $OPTIONS['GETI']['Password'];
		$this->TerminalID = $OPTIONS['GETI']['TerminalID'];

		$this->soapClient = $soapClient;
		$this->debug = $debug;
		$this->debugXML = false;
		$this->debugResultXML = null;
	}
	
	
	public function process($PaymentInfo,$amount)
	{
		if ($this->debugXML)
			$XML = $this->debugXML;
		else
			$XML = $this->MakeDataPacket($PaymentInfo,$amount);

		if ($this->debug)
			echo $XML;

		$rawResultXML = $this->ProcessCheck($XML,$this->debug);
		$resultObj = $this->ParsePaymentResult($rawResultXML);
		$resultObj->Amount = $amount;
		return $resultObj;
	}

	public function ParsePaymentResult($rawResultXML)
	{
		if ($this->debugResultXML)
			$XML = $this->debugResultXML;
		else
			$XML = $rawResultXML;

		/* Your code to parse the XML into $PaymentResult */
		/* parse the XML response into an object */
		return simplexml_load_string($XML);
	}
	
	private function LogResult($resultObj,$User=null)
	{
		if ($User)
		{
			echo '<pre>';
			print_r($User);
			echo '</pre>';
		}
		
		echo '<pre>';
		print_r($resultObj);
		echo '</pre>';
	}

	private function MakeDataPacket($PaymentInfo,$amount)
	{
		$Dom = new DOMDocument('1.0','ISO-8859-1');
		$Dom->formatOutput = true;

		$AuthElem = $Dom->createElement('AUTH_GATEWAY');
		$AuthElem->appendChild(new DOMAttr('REQUEST_ID',$PaymentInfo->RequestID));

		$Transaction = $Dom->createElement('TRANSACTION');
		$TransactionID = $Dom->createElement('TRANSACTION_ID',$PaymentInfo->TransactionID);

		$Transaction->appendChild($TransactionID);

		$MerchantElem = $Dom->createElement('MERCHANT');
		$MerchantElem->appendChild($Dom->createElement('TERMINAL_ID',$this->TerminalID));
		$Transaction->appendChild($MerchantElem);

		$PacketElem = $Dom->createElement('PACKET');
		$Identifier = (string)'A';

		$IdentifierElem = $Dom->createElement('IDENTIFIER',$Identifier);
		$PacketElem->appendChild($IdentifierElem);

		$AccountElem = $Dom->createElement('ACCOUNT');
		$AccountElem->appendChild($Dom->createElement('ROUTING_NUMBER',$PaymentInfo->RoutingNumber));
		$AccountElem->appendChild($Dom->createElement('ACCOUNT_NUMBER',$PaymentInfo->AccountNumber));
		$AccountElem->appendChild($Dom->createElement('ACCOUNT_TYPE','Checking')); //Will possibly need to be dynamic
		$PacketElem->appendChild($AccountElem);

		$ConsumerElem = $Dom->createElement('CONSUMER');
		$ConsumerElem->appendChild($Dom->createElement('FIRST_NAME',$PaymentInfo->FirstName));
		$ConsumerElem->appendChild($Dom->createElement('LAST_NAME',$PaymentInfo->LastName));
		$ConsumerElem->appendChild($Dom->createElement('ADDRESS1',$PaymentInfo->Address1));
		$ConsumerElem->appendChild($Dom->createElement('ADDRESS2',$PaymentInfo->Address2));
		$ConsumerElem->appendChild($Dom->createElement('CITY',$PaymentInfo->City));
		$ConsumerElem->appendChild($Dom->createElement('STATE',$PaymentInfo->State));
		$ConsumerElem->appendChild($Dom->createElement('ZIP',$PaymentInfo->Zip));
		$ConsumerElem->appendChild($Dom->createElement('PHONE_NUMBER',$PaymentInfo->PhoneNumber));
		$ConsumerElem->appendChild($Dom->createElement('DL_STATE',$PaymentInfo->DLState));
		$ConsumerElem->appendChild($Dom->createElement('DL_NUMBER',$PaymentInfo->DLNumber));
		$ConsumerElem->appendChild($Dom->createElement('COURTESY_CARD_ID'));

		if ((isset($PaymentInfo->SSN4) || isset($PaymentInfo->DOB_YEAR)))
		{
			$IdentityElem  = $Dom->createElement('IDENTITY');
			if ($PaymentInfo->SSN4)
				$IdentityElem->appendChild($Dom->createElement('SSN4',$PaymentInfo->SSN4));
			else
				$IdentityElem->appendChild($Dom->createElement('DOB_YEAR',$PaymentInfo->DOB_YEAR));

			$ConsumerElem->appendChild($IdentityElem);
		}

		$PacketElem->appendChild($ConsumerElem);

		$CheckElem = $Dom->createElement('CHECK');
		$CheckElem->appendChild($Dom->createElement('CHECK_AMOUNT',$amount));
		$PacketElem->appendChild($CheckElem);

		$Transaction->appendChild($PacketElem);
		$AuthElem->appendChild($Transaction);
		
		return $Dom->saveXML($AuthElem);
	}

	public function IsCertified($XML)
	{
		$sXML = new SimpleXMLElement($XML);
		return ($sXML->EXCEPTION) ? false : true;
	}

	private function ProcessCheck($XML,$debug=false)
	{
		$params = array();
		$params['DataPacket'] = $XML;

		$terminalSettignsFNC = 'GetTerminalSettings';
		$processFNC = 'ProcessSingleCheck';
		
		if ($debug)
		{
			$terminalSettignsFNC = 'GetCertificationTerminalSettings';
			$processFNC = 'ProcessSingleCertificationCheck';
		}
		
		$result0 = $this->soapClient->$terminalSettignsFNC();

		if (!$this->IsCertified($result0->{$terminalSettignsFNC.'Result'}))
			return $result0->{$terminalSettignsFNC.'Result'};

		$result1 = $this->soapClient->$processFNC($params);

		return $result1->{$processFNC.'Result'};
	}
}

class GETISoapClient extends SoapClient {

	public function __call($func, $args)
	{
        return $this->__soapCall($func, $args);
    }
    
    public function __soapCall($function, $arguments, $options = array(), $input_headers = null, &$output_headers = null)
    {
        return parent::__soapCall($function, $arguments, $options, $input_headers, $output_headers);
    }

    public function __doRequest($request, $location, $action, $version)
    {
    	return parent::__doRequest($request,$location, $action, $version);
  	}
}

class GETISoapClientFactory {

	public function Create($OPTIONS)
	{
		$soapClient = new GETISoapClient($OPTIONS['GETI']['Server']);
		$headerparameters = array('UserName'=>$OPTIONS['GETI']['UserName'], 'Password' => $OPTIONS['GETI']['Password'], 'TerminalID' => $OPTIONS['GETI']['TerminalID']);

		$headers = new SoapHeader($OPTIONS['GETI']['NameSpace'], 'AuthGatewayHeader', $headerparameters);
		$soapClient->__setSoapHeaders(array($headers));
		return $soapClient;
	}
}

class ECheckProcessorFactory {

	/* SystemSettings parses a  configuration to get credentials for authenticating with GETI. */
	public function Create($OPTIONS=null)
	{
		if (!$OPTIONS)
			$OPTIONS = SystemSettings::get();

		$soapClient = GETISoapClientFactory::Create($OPTIONS);
		return new ECheckProcessor($OPTIONS,$soapClient);
	}
}

class ECheckProcessorTest {

	public function testProcess($params, $ChargeAmount)
	{
		// set default values for most of the parameters
		// in the live script, these values will have to be submitted as part of the form.  they only get set here for testing
		$defaultParams = array(
			'RequestID'			=> '1',
			'TransactionID'		=> date('U'),
			'RoutingNumber'		=> Tools::safeOutput(Tools::getValue('routingnumber')),
			'AccountNumber'		=> Tools::safeOutput(Tools::getValue('accountnumber')),
			'CheckNumber'		=> Tools::safeOutput(Tools::getValue('checknumber')),
			'FirstName'			=> Tools::safeOutput(Tools::getValue('firstname')),
			'LastName'			=> Tools::safeOutput(Tools::getValue('lastname')),
			'Address1'			=> Tools::safeOutput(Tools::getValue('address1')),
			'Address2'			=> Tools::safeOutput(Tools::getValue('address2')),
			'City'				=> Tools::safeOutput(Tools::getValue('city')),
			'State'				=> Tools::safeOutput(Tools::getValue('state')),
			'Zip'				=> Tools::safeOutput(Tools::getValue('zip')),
			'PhoneNumber'		=> Tools::safeOutput(Tools::getValue('phonenumber')),
			'DLState'			=> Tools::safeOutput(Tools::getValue('dlstate')),
			'DLNumber'			=> Tools::safeOutput(Tools::getValue('dlnumber')),
			'Identifier'		=> Tools::safeOutput(Tools::getValue('identifier')),
		);

		if (Tools::safeOutput(Tools::getValue('identifier')) != '')
			$defaultParams['DOB_YEAR'] = Tools::safeOutput(Tools::getValue('identifier'));

		//CA//D1929239
		// merge the incoming parameters with the default parameters set above, and add them to the $PaymentInfo object
		$values = array_merge($defaultParams, $params);
		$PaymentInfo = new stdClass();
		
		foreach ($values as $key => $val)
			$PaymentInfo->$key =  $val;

		$config = array();
		$config['GETI']['UserName'] = Configuration::get('ALLIANCEACH_LOGIN');
		$config['GETI']['Password'] = Configuration::get('ALLIANCEACH_PASS');
		$config['GETI']['TerminalID'] = Configuration::get('ALLIANCEACH_TERMINAL');
		$config['GETI']['Server'] ='https://demo.eftchecks.com/webservices/authgateway.asmx?wsdl';
		$config['GETI']['NameSpace'] ='http://tempuri.org/GETI.eMagnus.WebServices/AuthGateway';

		$ECheckProcessor = ECheckProcessorFactory::Create($config);
		$ECheckProcessor->debug = true;
		$resObj = $ECheckProcessor->process($PaymentInfo, $ChargeAmount);
		
		// check to see if the validation passed
		if (isset($resObj->VALIDATION_MESSAGE) && isset($resObj->VALIDATION_MESSAGE->RESULT) && $resObj->VALIDATION_MESSAGE->RESULT == 'Passed')
			$passed = true;
		else
			$passed = false;
		
		// create the result object -- add the raw webservice result to it for debugging.
		$result = new stdClass();
		$result->rawResult = $resObj;
		$result->passed = $passed;
		$result->resultCode = $resObj->AUTHORIZATION_MESSAGE->RESULT_CODE;
		$result->identifier = $PaymentInfo->Identifier;
		
		return $result;
	}
}
