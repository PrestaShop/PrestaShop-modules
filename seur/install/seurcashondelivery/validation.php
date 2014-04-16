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

include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../header.php');
include_once(dirname(__FILE__).'/seurcashondelivery.php');

$reembolsocargo = new SeurCashOnDelivery();


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

            Tools::redirectLink(__PS_BASE_URI__.'order.php?step=1');
}
else
{
		$context = Context::getContext();
		$cookie = $context->cookie;
        if (    Configuration::get('PS_TOKEN_ENABLE') == 1 AND
                strcmp(Tools::getToken(false), Tools::encrypt($cookie->id_customer.$cookie->passwd.false)) AND
                $cookie->isLogged() === true)

           Tools::redirectLink(__PS_BASE_URI__.'order.php?step=1');
}

// BEGIN ??
$context = Context::getContext();
$cart = $context->cart;
// END ??

if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$reembolsocargo->active)
	Tools::redirectLink(__PS_BASE_URI__.'order.php?step=1');
$customer = new Customer((int)$cart->id_customer);

if (!Validate::isLoadedObject($customer))
	Tools::redirectLink(__PS_BASE_URI__.'order.php?step=1');

$currency = new Currency(Tools::getValue('currency_payement', false) ? Tools::getValue('currency_payement') : $cookie->id_currency);

$coste = (float)(abs($cart->getOrderTotal(true, Cart::BOTH)));
$cargo = number_format($reembolsocargo->getCargo($cart, false) , 2, '.', '');
$vales = (float)(abs($cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS)));

$total = $coste - $vales + $cargo;

$mailVars = array(
	'{bankwire_owner}' => Configuration::get('SEUR_TRANSCAR_OWNER'),
	'{bankwire_details}' => nl2br(Configuration::get('SEUR_TRANSCAR_DETAILS')),
	'{bankwire_address}' => nl2br(Configuration::get('SEUR_TRANSCAR_ADDRESS'))
);

$reembolsocargo->validateOrderFORWEBS_v4((int)$cart->id, 3, (float)$total, $reembolsocargo->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);
$order = new Order((int)$reembolsocargo->currentOrder);
Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?id_cart='.(int)$cart->id.'&id_module='.(int)$reembolsocargo->id.'&id_order='.(int)$reembolsocargo->currentOrder.'&key='.urlencode($customer->secure_key));