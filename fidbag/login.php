<?php

require('../../config/config.inc.php');
require_once(_PS_MODULE_DIR_."/fidbag/class/fidbagWebService.php");
require_once(_PS_MODULE_DIR_."/fidbag/class/fidbagUser.php");

$cart = new Cart((int)Tools::getValue('cart'));
$token = Tools::encrypt((int)Tools::getValue('customer'));

if ((Tools::getValue('token') !== $token) || !Tools::getValue('login') || !Tools::getValue('password') || ((int)$cart->id_customer != (int)Tools::getValue('customer')))
	die ("0");
else
{
	$webService = new FidbagWebService();
	
	try
	{
		$return = $webService->action('LoginUserWithMerchantCodeAndExternalToken',
			array('Login' => Tools::getValue('login'),
				'Password' => Tools::getValue('password'),
				'MerchantCode' => Configuration::get('FIDBAG_MERCHANT_CODE')),
			array('Login' => Tools::getValue('login'),
				'Password' => Tools::getValue('password'))
		);

		if ($return != null && isset($return->LoginUserWithMerchantCodeAndExternalTokenResult))
		{
			$json_return = Tools::jsonDecode($return->LoginUserWithMerchantCodeAndExternalTokenResult);
			if ($json_return->returnInfos->mCode != 0)
			{
				echo Tools::jsonEncode($json_return->returnInfos);
			}
			else
			{
				$fidbag_user = new FidbagUser((int)Tools::getValue('customer'));
				
				if (!$fidbag_user->getFidBagUser())
					$fidbag_user->createFidBagUser();

				$fidbag_user->setIdCart((int)Tools::getValue('cart'));
				$fidbag_user->setCartNumber($json_return->fidcardInformations->FidBagCardNumber);
				$fidbag_user->setPayed(false);

				$fidbag_user->setLoginPassword(Tools::getValue('login'), Tools::getValue('password'));
				die($return->LoginUserWithMerchantCodeAndExternalTokenResult);
			}
		}
		else
			die ("0");
	}
	catch (Exception $e)
	{
		die($e->getMessage());
	}
}

?>