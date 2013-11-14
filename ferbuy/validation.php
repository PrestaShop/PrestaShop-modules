<?php
/**
 * Ferbuy payment extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @category	Payment
 * @package	 Ferbuy
 * @author	  FerBuy, <info@ferbuy.com>
 * @copyright   FerBuy Copyright (c) 2013 (http://www.ferbuy.com)
 * @version	 1.3.0
 * @license	 http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/ferbuy.php');

if (!defined('_PS_VERSION_'))
	exit;

$data = $_POST;

/* Check data */
if (!$data
	|| !array_key_exists('reference', $data)
	|| !array_key_exists('transaction_id', $data)
	|| !array_key_exists('status', $data)
	|| !array_key_exists('currency', $data)
	|| !array_key_exists('amount', $data)
	|| !array_key_exists('checksum', $data)
	|| !array_key_exists('checksum', $data))
	exit('Incomplete data!');

$msgs = array();
$ferbuy = new FerBuy();
$callback_amount = (int)$data['amount'];

/* Prepare cart object */
$cart = new Cart((int)$data['reference']);
if (version_compare(_PS_VERSION_, '1.5', '>='))
	Context::getContext()->cart = $cart;

/* Validate amount only if status is transaction is successful */
if ($data['status'] == 200)
{
	$amount_in_cents = (int)sprintf('%.0f', $cart->getOrderTotal() * 100);
	if (($amount_in_cents != $callback_amount) && (abs($callback_amount - $amount_in_cents) > 1))
		$msgs[] = 'Possible hack attempt: Order total amount does not match FerBuy\'s gross total amount!';
}

/* Validate checksum - token */
$verify = join('&', array(
	$ferbuy->mode,
	$data['reference'],
	$data['transaction_id'],
	$data['status'],
	$data['currency'],
	$data['amount'],
	$ferbuy->secret
));

/* Verify checksum */
if (sha1($verify) != $data['checksum'])
	exit('Possible hack attempt: Calculated verification signature is incorrect!');

/* Change status */
switch ($data['status'])
{
	case '200':
		$msgs[] = 'Transaction complete.';
		$status = Configuration::get('PS_OS_PAYMENT');
		$ferbuy->setTransactionDetail($_POST);
		break;

	case '400':
		$msgs[] = 'Transaction failed.';
		$status = Configuration::get('PS_OS_ERROR');
		break;

	case '408':
		$msgs[] = 'Transaction timed out.';
		$status = Configuration::get('PS_OS_ERROR');
		break;

	case '410':
		$msgs[] = 'Transaction canceled by user.';
		$status = Configuration::get('PS_OS_CANCELED');
		break;

	default:
		$msgs[] = 'Transaction failed.';
		$status = Configuration::get('PS_OS_ERROR');
		break;
}

/* Get message */
$message = '';
foreach ($msgs as $msg)
	$message .= $msg.' ';

$message = nl2br(strip_tags($message));

/* Validate order */
$ferbuy->validateOrder(
	(int)$data['reference'],
	$status,
	($callback_amount / 100),
	$ferbuy->displayName,
	$message,
	array('transaction_id' => $data['transaction_id']),
	null,
	false,
	$data['extra']
);

echo $data['transaction_id'].'.'.$data['status'];