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
*  @copyright  2007-20131 PrestaShop SA
*  @version  Release: $Revision: 9844 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

require('../../config/config.inc.php');
require_once(dirname(__FILE__)."/class/fidbagWebService.php");
require_once(dirname(__FILE__)."/class/fidbagUser.php");

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
