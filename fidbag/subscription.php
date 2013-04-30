<?php

require('../../config/config.inc.php');
require_once(dirname(__FILE__)."/class/fidbagWebService.php");

$token = Tools::encrypt(Tools::getValue('customer'));

if (Tools::getValue('token') !== $token)
	die ("0");
elseif (!Tools::getValue('Civility') || !Tools::getValue('LastName') || !Tools::getValue('FirstName') || !Tools::getValue('Email') || !Tools::getValue('ZipCode') || !Tools::getValue('Password') || !Tools::getValue('LanguageCode'))
	die("0");
else
{
	$arg = array();
	$chif = array();
	$arg['MerchantCode'] = Configuration::get('FIDBAG_MERCHANT_CODE');
	
	foreach ($_POST as $key => $value)
		$arg[$key] = $value;
		
	$chif[] = Configuration::get('FIDBAG_MERCHANT_CODE');
	$chif[] = Tools::safeOutput(Tools::getValue('Email'));
	$chif[] = Tools::safeOutput(Tools::getValue('LanguageCode'));
	
	$webService = new FidbagWebService();
	
	try
	{
		$return = $webService->action('CreateFidBagAccountWithTempCardAndFullAddressAndExternalToken', $arg, $chif);
		
		if ($return != null && isset($return->CreateFidBagAccountWithTempCardAndFullAddressAndExternalTokenResult))
		{
			$json_return = Tools::jsonDecode($return->CreateFidBagAccountWithTempCardAndFullAddressAndExternalTokenResult);
			die($return->CreateFidBagAccountWithTempCardAndFullAddressAndExternalTokenResult);
		}
		else
		{
			die("0");
		}
	}
	catch (Exception $e)
	{
		die($e->getMessage());
	}
	
}
?>
