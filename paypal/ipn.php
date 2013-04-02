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

include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../init.php');
include_once(dirname(__FILE__).'/paypal_orders.php');

function getIPNTransactionDetails()
{
	$transaction_id = pSQL(Tools::getValue('txn_id'));
	return array(
		'id_invoice' => null,
		'id_transaction' => $transaction_id,
		'transaction_id' => $transaction_id,
		'currency' => pSQL(Tools::getValue('mc_currency')),
		'total_paid' => (float)Tools::getValue('mc_gross'),
		'shipping' => (float)Tools::getValue('mc_shipping'),
		'payment_date' => pSQL(Tools::getValue('payment_date')),
		'payment_status' => pSQL(Tools::getValue('payment_status')),
	);
}

if (Tools::getValue('payment_status') !== false)
{	
	$details = getIPNTransactionDetails();
	$id_order = PayPalOrder::getIdOrderByTransactionId($details['id_transaction']);
	PayPalOrder::updateOrder($id_order, $details);

	$history = new OrderHistory();
	$history->id_order = (int)$id_order;
	$history->changeIdOrderState((int)Configuration::get('PS_OS_PAYMENT'), $history->id_order);
	$history->addWithemail();
	$history->save();
}
