<?php

require_once(_PS_MODULE_DIR_."/buyster/lib/nusoap.php");

class BuysterWebService
{
	private $_merchantId;
	private $_password;
	private $_signature;
	private $authvars;
	private $header;
	private $file;
	
	private $_requestVersion = '1.0';
	private $_currencyCode = 978;
	private $_customerLanguage = 'Fr';
	
	private $_returnUrl;
	
	private $_order;
		
	public	function __construct()
	{
		$this->_merchantId = Configuration::get('BUYSTER_PAYMENT_ID');
		$this->_password = Configuration::get('BUYSTER_PAYMENT_PASSWORD');
		$this->_signature = Configuration::get('BUYSTER_PAYMENT_SIGNATURE');
		$this->_returnUrl = Configuration::get('BUYSTER_PAYMENT_RETURN_URL');
		
		if (Configuration::get('BUYSTER_PAYMENT_PRODUCTION'))
			$this->_file = 'https://www.inscripaiement.buyster.fr/net.atos.mnop.ServicePayment?wsdl';
		else
			$this->_file = 'https://mnop-acceptor.aw.atosorigin.com/net.atos.mnop.ServicePayment?wsdl';

		$this->_header = '<urn:authenticationHeader>
								<urn:merchantSignature>'.htmlspecialchars($this->_signature).'</urn:merchantSignature>
								<urn:merchantPassword>'.htmlspecialchars($this->_password).'</urn:merchantPassword>
								<urn:merchantId>'.htmlspecialchars($this->_merchantId).'</urn:merchantId>
							</urn:authenticationHeader>';
	}
	
	public function setOrder($order)
	{
		$this->_order = $order;
	}
	
	public function getUrl($amount, $ip, $id, $ref, $operation, $customerId)
	{
		$transactionParameters = '';
		if ($operation == 'paymentDelayed')
			$transactionParameters = 'captureDelay='.Configuration::get('BUYSTER_PAYMENT_DAYS_DELAYED').';';
		elseif ($operation == 'paymentValidation')
			$transactionParameters = 'validationDelay='.Configuration::get('BUYSTER_PAYMENT_VALIDATION_DAYS').';';
		elseif ($operation == 'paymentN')
			$transactionParameters = 'paymentNumber='.Configuration::get('BUYSTER_PAYMENT_TIME_PAYMENT').';
			period='.Configuration::get('BUYSTER_PAYMENT_PERIOD_PAYMENT').';
			initialAmount='.Configuration::get('BUYSTER_PAYMENT_INITIAL_AMOUNT').';captureDelayed='.Configuration::get('BUYSTER_PAYMENT_DELAYED_SEVERAL').';';
		try {
			$client = new nusoap_client($this->_file);
			$result = $client->send('<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:mnop:acceptor:contract">
										<soapenv:Header>
											'.$this->_header.'
										</soapenv:Header>
										<soapenv:Body>
										<urn:initializePaymentRequest>
										 <urn:amount>'.(int)((float)$amount * 100).'</urn:amount>
										 <urn:automaticResponseUrl>http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/buyster/validation.php</urn:automaticResponseUrl>
										 <urn:context></urn:context>
										 <urn:currencyCode>'.$this->_currencyCode.'</urn:currencyCode>
										 <urn:customerId></urn:customerId>
										 <urn:customerIpAddress>'.$ip.'</urn:customerIpAddress>
										 <urn:customerLanguage>'.$this->_customerLanguage.'</urn:customerLanguage>
										 <urn:merchantSessionId></urn:merchantSessionId>
										 <urn:merchantTransactionDateTime>'.date('Y-m-d\Th:i:s').'</urn:merchantTransactionDateTime>
										 <urn:orderChannel></urn:orderChannel>
										 <urn:orderId>'.$id.'</urn:orderId>
										 <urn:requestVersion>'.$this->_requestVersion.'</urn:requestVersion>
										 <urn:returnUrl>http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'history.php</urn:returnUrl>
										 <urn:stylesheet></urn:stylesheet>
										 <urn:transactionOrigin></urn:transactionOrigin>
										 <urn:transactionParameters>'.$transactionParameters.'</urn:transactionParameters>
										 <urn:transactionReference>'.$ref.'</urn:transactionReference>
										 <urn:transactionType>'.$operation.'</urn:transactionType>
										</urn:initializePaymentRequest>
									</soapenv:Body>
									</soapenv:Envelope>');
			}
		catch(SoapFault $e)
		{
			echo $e->faultstring;
		}
		catch( Exception $e )
		{
			var_dump($e);
		}
		return ($result);
	}
	
	public function operation($type, $ref, $price = NULL, $parameters = NULL)
	{
		try 
		{
			$client = new nusoap_client($this->_file);
			$result = $client->send(
			'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:mnop:acceptor:contract">
				<soapenv:Header>'.$this->_header.'</soapenv:Header>
				<soapenv:Body>
					<urn:cashManagementOperationRequest>
					 <urn:amount>'.(int)((float)$price * 100).'</urn:amount>
					 <urn:merchantSessionId></urn:merchantSessionId>
					 <urn:operationName>'.$type.'</urn:operationName>
					 <urn:operationSequence></urn:operationSequence>
					 <urn:operationOrigin></urn:operationOrigin>
					 <urn:requestVersion>'.$this->_requestVersion.'</urn:requestVersion>
					 <urn:operationParameters>'.$parameters.'</urn:operationParameters>
					 <urn:transactionReference>'.$ref.'</urn:transactionReference>
					</urn:cashManagementOperationRequest>
				</soapenv:Body>
			</soapenv:Envelope>');
		}
		catch(SoapFault $e)
		{
			echo $e->faultstring;
		}
		catch( Exception $e )
		{
			var_dump($e);
		}
		
		return ($result);
	}
	
}