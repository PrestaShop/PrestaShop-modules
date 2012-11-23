<?php
/*
* 2007-2012 PrestaShop
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
*  @copyright  2007-2012 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../init.php');

include_once(_PS_MODULE_DIR_.'paypal/paypal.php');
$paypal = new Paypal();
$paypal_order = new PayPalOrder();

if (!$transaction_id = Tools::getValue('txn_id'))
	die($paypal->l('No transaction id'));
if (!$id_order = $paypal_order->getIdOrderByTransactionId($transaction_id))
	die($paypal->l('No order'));

$order = new Order((int)$id_order);
if (!Validate::isLoadedObject($order) || !$order->id)
	die($paypal->l('Invalid order'));
if (!$amount = (float)Tools::getValue('mc_gross') || ($amount != $order->total_paid))
	die($paypal->l('Incorrect amount'));

if (!$status = (string)Tools::getValue('payment_status'))
	die($paypal->l('Incorrect order status'));

// Getting params
$params = 'cmd=_notify-validate';
foreach ($_POST as $key => $value)
	$params .= '&'.$key.'='.urlencode(stripslashes($value));

// Checking params by asking PayPal
include(_PS_MODULE_DIR_.'paypal/api/paypal_lib.php');
$paypalAPI = new PaypalLib();
$result = $paypalAPI->makeSimpleCall($paypal->getAPIURL(), $paypal->getAPIScript(), $params);
if (!$result || (Tools::strlen($result) < 8) || (!$status = substr($result, -8)) || $status != 'VERIFIED')
	die($paypal->l('Cannot verify PayPal order'));

// Getting order status
switch ($status)
{
	case 'Completed':
		$id_order_state = Configuration::get('PS_OS_PAYMENT');
		break;
	case 'Pending':
		$id_order_state = Configuration::get('PS_OS_PAYPAL');
		break;
	default:
		$id_order_state = Configuration::get('PS_OS_ERROR');
}

if ($order->getCurrentState() == $id_order_state)
	die($paypal->l('Same status'));

// Set order state in order history
$history = new OrderHistory();
$history->id_order = (int)$order->id;
$history->changeIdOrderState((int)$id_order_state, (int)$order->id);
$history->addWithemail(true, $extraVars);
