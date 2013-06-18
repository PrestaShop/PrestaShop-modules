<?php

if (file_exists(dirname(__FILE__).'/EbayRequest.php'))
	require_once(dirname(__FILE__).'/EbayRequest.php');

class EbayConfiguration
{
	/**
	 * Updates Ebay API Token and stores it
	 *
	 * Returns true is sucessful, false otherwise
	 *
	 * @return boolean
	 */ 
	
	public static function updateAPIToken()
	{
		$request = new EbayRequest();
		if ($token = $request->fetchToken(Configuration::get('EBAY_API_USERNAME'), Configuration::get('EBAY_API_SESSION')))
		{
			Configuration::updateValue('EBAY_API_TOKEN', $token, false, 0, 0);
			return true;
		}
		return false;		
	}
}