<?php
/*
* 2013 BluePay
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
*  @author BluePay Processing, LLC
*  @copyright  2013 BluePay Processing, LLC
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../init.php');
include_once(dirname(__FILE__).'/bluepay.php');
include_once(dirname(__FILE__).'/bluepay_orders.php');
include_once(dirname(__FILE__).'/bluepay_customers.php');

$bluepay = new BluePay();
$cart = Context::getContext()->cart;
$customer = Context::getContext()->customer;
$address = new Address((int)$cart->id_address_invoice);
$state = new State((int)$address->id_state);

/**
* Perform validations
*/
if (!$bluepay->active)
	die;
if (!Tools::getValue('invoice_id'))
{
	Logger::addLog('Missing invoice_id', 4);
	die('An unrecoverable error occured: Missing parameter');
}
if (!Validate::isLoadedObject($cart))
{
	Logger::addLog('Cart loading failed for cart '.(int)Tools::getValue('invoice_id'), 4);
	die('An unrecoverable error occured with the cart '.(int)Tools::getValue('invoice_id'));
}
if ($cart->id != Tools::getValue('invoice_id'))
{
	Logger::addLog('Conflict between cart id order and customer cart id');
	die('An unrecoverable conflict error occured with the cart '.(int)Tools::getValue('invoice_id'));
}
if (!Validate::isLoadedObject($customer) || !Validate::isLoadedObject($address))
{
	Logger::addLog('Issue loading customer and/or address data');
	die('An unrecoverable error occured while retrieving you data');
}

/**
* Check if transaction was processed and approved through the iframe checkout page
*/
if (Tools::getValue('iframe_transaction_approved') == '1')
{
	$transaction = array(
		'customer_id' => $customer->id,
		'transaction_id' => Tools::getValue('transaction_id'),
		'payment_status' => 'Approved',
		'invoice_id' => Tools::getValue('invoice_id'),
		'payment_type' => Tools::getValue('payment_type'),
		'payment_date' => date('Y-m-d H:i:s'),
		'total_paid' => Tools::getValue('amount'),
		'transaction_type' => (Tools::getValue('transaction_type') == 'SALE') ? Configuration::get('PS_OS_PAYMENT')
			: Configuration::get('BP_OS_AUTHORIZATION'),
		'name' => Tools::getValue('name'),
		'email' => Tools::getValue('email'),
		'payment_account' => Tools::getValue('payment_account'),
		'card_type' => Tools::getValue('card_type'),
		'expiration_date' => Tools::getValue('card_expiration')
	);
	/**
	* Check if amount processed through BluePay matches the cart total.
	* If amounts do not match, send the customer back
	* to the payment page.
	*/
	if ($bluepay->validate($transaction['transaction_id'], $transaction['invoice_id']) != $transaction['total_paid'])
	{
		$message = 'Amounts do not match. The order was not created successfully; please try again.';
		$url = 'index.php?controller=order&step=3&error='.$message;
		echo $url;
		exit;
	}
	$bluepay->validateOrder((int)$cart->id,
		$transaction['transaction_type'], $transaction['total_paid'],
		$bluepay->displayName, Tools::getValue('message'), $transaction, null, false, $customer->secure_key);
	$order_id = Order::getOrderByCartId((int)$cart->id);
	BluePayOrder::saveOrder((int)$order_id, $transaction);
	$url = 'index.php?controller=order-confirmation&';
	$data = $url.'id_module='.(int)$bluepay->id.'&id_cart='.
		(int)$cart->id.'&key='.$customer->secure_key.'&masked_card_number='.$transaction['payment_account'].
		'&card_expiration='.$transaction['expiration_date'].'&card_type='.$transaction['card_type'].'&card_holder='.
		$transaction['name'].'&routing_number='.Tools::getValue('routing_number').
		'&account_type='.Tools::getValue('account_type').'&payment_type='.$transaction['payment_type'];
	if (Tools::getValue('save_payment_information') == 'Yes' || Tools::getValue('stored_payment_account') != '')
	{
		if ($transaction['card_type'] == 'ACH')
			$transaction['payment_type'] = 'ACH';
		BluePayCustomer::saveCustomer($transaction);
	}
	echo $data;
	exit;
}

if (Tools::getValue('card_number') != '')
{
	$tps_string = Configuration::get('BP_SECRET_KEY').Configuration::get('BP_ACCOUNT_ID').
		Configuration::get('BP_TRANSACTION_TYPE').number_format((float)$cart->getOrderTotal(true, 3), 2, '.', '').
		Tools::getValue('master_id').Tools::safeOutput($customer->firstname).Tools::getValue('card_number');
}
else
{
	$tps_string = Configuration::get('BP_SECRET_KEY').Configuration::get('BP_ACCOUNT_ID').'SALE'.
		number_format((float)$cart->getOrderTotal(true, 3), 2, '.', '').Tools::getValue('master_id').Tools::safeOutput($customer->firstname).
		Tools::getValue('ach_account_type').':'.Tools::getValue('ach_routing').':'.Tools::getValue('ach_account');
}
$tps = md5($tps_string);

/**
* Build the HTTP POST array
*/
$params = array(
	'ACCOUNT_ID' => Configuration::get('BP_ACCOUNT_ID'),
	'TAMPER_PROOF_SEAL' => $tps,
	'MODE' => Configuration::get('BP_TRANSACTION_MODE'),
	'INVOICE_ID' => (int)Tools::getValue('invoice_id'),
	'AMOUNT' => number_format((float)$cart->getOrderTotal(true, 3), 2, '.', ''),
	'ADDR1' => Tools::safeOutput($address->address1.' '.$address->address2),
	'CITY' => Tools::safeOutput($address->city),
	'STATE' => Tools::safeOutput($state->name),
	'ZIP' => Tools::safeOutput($address->postcode),
	'COUNTRY' => Tools::safeOutput($address->country),
	'NAME1' => Tools::safeOutput($customer->firstname),
	'NAME2' => Tools::safeOutput($customer->lastname),
	'EMAIL' => Tools::safeOutput($customer->email),
	'VERSION' => '1'
);

if (Tools::getValue('card_number') != '')
{
	$params['TRANS_TYPE'] = Configuration::get('BP_TRANSACTION_TYPE');
	$params['PAYMENT_TYPE'] = 'CREDIT';
	$params['PAYMENT_ACCOUNT'] = Tools::getValue('card_number');
	$params['CARD_EXPIRE'] = (Tools::getValue('expiration_mm') != '') ? Tools::getValue('expiration_mm').Tools::getValue('expiration_yy') :
		Tools::getValue('card_expiration_mm').Tools::getValue('card_expiration_yy');
	$params['CARD_CVV2'] = Tools::getValue('cvv2');
}
else
{
	$params['TRANS_TYPE'] = 'SALE';
	$params['PAYMENT_TYPE'] = 'ACH';
	$params['DOC_TYPE'] = 'WEB';
	$params['PAYMENT_ACCOUNT'] = Tools::getValue('ach_account_type').':'.Tools::getValue('ach_routing').':'.Tools::getValue('ach_account');
}
if (Tools::getValue('master_id') != '')
	$params['MASTER_ID'] = Tools::getValue('master_id');

$post_string = '';
foreach ($params as $key => $value)
	$post_string .= $key.'='.urlencode($value).'&';
$post_string = trim($post_string, '&');
$url = 'https://secure.bluepay.com/interfaces/bp20post';

/* POST to BluePay */
$request = curl_init($url);
curl_setopt($request, CURLOPT_HEADER, 0);
curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($request, CURLOPT_POSTFIELDS, $post_string);
curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
parse_str(curl_exec($request), $post_response);
curl_close($request);

$status = $post_response['STATUS'];
$message = $post_response['MESSAGE'];
$transaction_id = $post_response['TRANS_ID'];
$payment_account_mask = $post_response['PAYMENT_ACCOUNT_MASK'];
$card_type = $post_response['CARD_TYPE'];
$payment_method = 'BluePay Credit Card';
/**
* Response
*/
switch ($status)
{
	/**
	* Transaction approved
	*/
	case '1':
		if ($message != 'DUPLICATE')
		{
			$transaction = array(
				'customer_id' => $customer->id,
				'transaction_id' => $transaction_id,
				'payment_status' => 'Approved',
				'payment_type' => $params['PAYMENT_TYPE'],
				'payment_date' => date('Y-m-d H:i:s'),
				'invoice_id' => Tools::getValue('invoice_id'),
				'total_paid' => number_format((float)$cart->getOrderTotal(true, 3), 2, '.', ''),
				'transaction_type' => ($params['TRANS_TYPE'] == 'SALE') ? Configuration::get('PS_OS_PAYMENT')
					: Configuration::get('BP_OS_AUTHORIZATION'),
				'name' => $params['NAME1'].' '.$params['NAME2'],
				'email' => $params['EMAIL'],
				'payment_account' => $payment_account_mask,
				'card_type' => $card_type,
				'expiration_date' => $params['CARD_EXPIRE']
			);
			/**
			* Check if amount processed through BluePay matches the cart total.
			* If amounts do not match, send the customer back
			* to the payment page.
			*/
			if ($bluepay->validate($transaction['transaction_id'], $transaction['invoice_id']) != $transaction['total_paid'])
			{
				$message = 'Amounts do not match. The order was not created successfully; please try again.';
				$url = 'index.php?controller=order&step=3&error='.$message;
				Tools::redirect($url);
				exit;
			}
			$bluepay->validateOrder((int)$cart->id,
				$transaction['transaction_type'], $transaction['total_paid'],
				$bluepay->displayName, $message, $transaction, null, false, $customer->secure_key);
			$order_id = Order::getOrderByCartId((int)$cart->id);
			BluePayOrder::saveOrder((int)$order_id, $transaction);
			if (Tools::getValue('save_payment_information') == 'Yes')
				BluePayCustomer::saveCustomer($transaction);
			break;
		}
		else
		{
			$url = 'index.php?controller=order&step=3&error=Duplicate Transaction';
			Tools::redirect($url);
			break;
		}

	/**
	* Transaction declined
	*/
	case '0':
		$url = 'index.php?controller=order&step=3&error=Declined';
		Tools::redirect($url);
		break;

	/**
	* Transaction errored
	*/
	case 'E':
		$url = 'index.php?controller=order&step=3&error='.$message;
		Tools::redirect($url);
		exit;

	default:
		$url = 'index.php?controller=order&step=3&error=General Error';
		Tools::redirect($url);
		exit;
}

/**
* Order was successful - redirect customer to order confirmation page
*/
$url = 'index.php?controller=order-confirmation&';
Tools::redirect($url.'id_module='.(int)$bluepay->id.'&id_cart='.
	(int)$cart->id.'&key='.$customer->secure_key.'&masked_card_number='.$payment_account_mask.
	'&card_expiration='.$params['CARD_EXPIRE'].'&card_type='.$card_type.'&card_holder='.
	$params['NAME1'].' '.$params['NAME2'].'&routing_number='.Tools::getValue('routing_number').
	'&account_type='.Tools::getValue('account_type').'&payment_type='.$params['PAYMENT_TYPE']);
