<?php

class FidbagWebService
{

	private $_code;
	private $_certificat;
	private $_file;

	public function __construct()
	{
		$this->_code = Configuration::get('FIDBAG_MERCHANT_CODE');
		$this->_certificat = Configuration::get('FIDBAG_MERCHANT_CERTIFICAT');
		
		if ((bool)Configuration::get('FIDBAG_TEST_ENVIRONMENT') === false)
			$this->_file = 'http://services.partner.fidbag.com/Website.asmx?wsdl';
		else
			$this->_file = 'http://preprod.services.partner.fidbag.com/Website.asmx?wsdl';
	}

	public function getClient()
	{
		if (!extension_loaded('soap'))
			return null;

		try
		{
			return new SoapClient($this->_file, array('trace' => 1));
		}
		catch(SoapFault $e){
			return null;
		}
		catch(Exception $e){
			return null;
		}
	}

	public function action($action, $arg = array(), $chif = null)
	{
		if ($chif != null)
			$arg['Signature'] = $this->encryptSha1($chif);
		else
			$arg['Signature'] = $this->encryptSha1($arg);

		try
		{
			$client = $this->getClient();
			
			if ($client == null)
				return null;

			return call_user_func(array($client, $action), $arg);
		}
		catch(Exception $e )
		{
			d($e->getMessage());
			return $e->getMessage();
		}
	}

	public function encryptSha1($param = array())
	{
		$str = implode(";", $param);
		return sha1($str.";".$this->_certificat);
	}
}

?>