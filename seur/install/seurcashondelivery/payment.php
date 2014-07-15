<?php
/**
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2014 PrestaShop SA
*  @version  Release: 0.4.4
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/* SSL Management */
$useSSL = true;

include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../header.php');
include_once(dirname(__FILE__).'/seurcashondelivery.php');

if (!class_exists('SeurLib'))
        include_once(_PS_MODULE_DIR_.'seur/classes/SeurLib.php');

if(version_compare(_PS_VERSION_, "1.5", "<")){ require_once(_PS_MODULE_DIR_.'seurcashondelivery/backward_compatibility/backward.php'); }

if (version_compare(_PS_VERSION_, "1.5", ">="))
{
        $context = Context::getContext();
        $customer = new Customer((int)$context->cart->id_customer);

        if (    Configuration::get('PS_TOKEN_ENABLE') == 1 AND
                strcmp(Tools::getToken(false), Tools::encrypt($context->cart->id_customer.$customer->passwd.false)) AND
                $context->customer->isLogged() === true)

            Tools::redirect('authentication.php?back=order.php');
}
else
{
		$context = Context::getContext();
		$cookie = $context->cookie;
        if (    Configuration::get('PS_TOKEN_ENABLE') == 1 AND
                strcmp(Tools::getToken(false), Tools::encrypt($cookie->id_customer.$cookie->passwd.false)) AND
                $cookie->isLogged() === true)

           Tools::redirect('authentication.php?back=order.php');
}

$seurcashondelivery = new SeurCashOnDelivery();

// BEGIN ??
$context = Context::getContext();
$cart = $context->cart;
// END ??

echo($seurcashondelivery->execPayment($cart));

include_once(dirname(__FILE__).'/../../footer.php');