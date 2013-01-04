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

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(_PS_MODULE_DIR_.'alliedwallet/alliedwallet.php');

$allied = new AlliedWallet();

/* First we need to check that this script is called by an authorized IP address (from Allied Wallet) */
$ch = curl_init('https://sale.alliedwallet.com/ip_list.txt');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$content = curl_exec($ch);
curl_close($ch);

if (!in_array($_SERVER['REMOTE_ADDR'], explode('|', $content)))
{
	Logger::AddLog('[AlliedWallet] Hack attempt: Someone tried to validate a payment - '.Tools::safeOutput($_SERVER['REMOTE_ADDR']), 2);
	die($allied->l('Forbidden Action.'));
}

$siteId = Tools::getValue('SiteID');
if ($siteId != Configuration::get('ALLIEDWALLET_SITE_ID'))
{
	Logger::AddLog('[AlliedWallet] Hack attempt: Someone tried to validate a payment with a different site ID - '.Tools::safeOutput($siteId), 2);
	die($allied->l('Forbidden Action.'));
}

/* Then we load the current Shopping cart */
if (_PS_VERSION_ >= 1.5)
	Context::getContext()->cart = new Cart((int)Tools::getValue('MerchantReference'));
$cart = _PS_VERSION_ >= 1.5 ? Context::getContext()->cart : new Cart((int)Tools::getValue('MerchantReference'));

if (Validate::isLoadedObject($cart))
	$allied->validateOrder((int)$cart->id, Configuration::get('PS_OS_PAYMENT'), (float)Tools::getValue('Amount'), $allied->name, NULL, array(), NULL, false,	$cart->secure_key);
else
	Logger::AddLog('[AlliedWallet] The Shopping cart #'.(int)Tools::getValue('MerchantReference').' was not found during the payment validation step.', 2);