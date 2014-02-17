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
*  @license	http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');

/* will include backward file */
include(dirname(__FILE__).'/alliance3.php');

$authorizeaim = new alliance3();

/* Does the cart exist and is valid? */
$cart = Context::getContext()->cart;
$x_invoice_num = Tools::getValue('x_invoice_num');

if (!isset($x_invoice_num))
{
	Logger::addLog('Missing x_invoice_num', 4);
	die('An unrecoverable error occured: Missing parameter');
}

if (!Validate::isLoadedObject($cart))
{
	Logger::addLog('Cart loading failed for cart '.(int)Tools::getValue('x_invoice_num'), 4);
	die('An unrecoverable error occured with the cart '.(int)Tools::getValue('x_invoice_num'));
}

if ($cart->id != Tools::getValue('x_invoice_num'))
{
	Logger::addLog('Conflit between cart id order and customer cart id');
	die('An unrecoverable conflict error occured with the cart '.(int)Tools::getValue('x_invoice_num'));
}

$customer = new Customer((int)$cart->id_customer);
$invoiceAddress = new Address((int)$cart->id_address_invoice);

if (!Validate::isLoadedObject($customer) || !Validate::isLoadedObject($invoiceAddress))
{
	Logger::addLog('Issue loading customer and/or address data');
	die('An unrecoverable error occured while retrieving you data');
}

//if auth starts
if (Tools::getValue('alliancepay') == 'achauth')
{
	if (!Tools::getValue('routingnumber'))
	{
		Logger::addLog('ACH selected without routing number');
		die('An unrecoverable conflict error occured with the cart (rn)');
	}

	require_once('GETIECheckProcessor.php');
	$isError = false;
	
	$_POST['RoutingNumber'] = Tools::getValue('routingnumber');
	$_POST['CheckNumber'] = Tools::getValue('checknumber');
	$_POST['AccountNumber'] = Tools::getValue('accountnumber');
	$_POST['Identifier'] = 'A';
	
	$reqParams = array('RoutingNumber', 'Identifier','AccountNumber');
	$params = array();
	$errMsgs = array();
	
	foreach ($reqParams as $key)
	{
		if (!Tools::getIsset(Tools::getValue($key)) || !Tools::strlen(trim(Tools::getValue($key))))
		{
			$validForm = false;
			$errMsgs[] = "Missing field: '$key'";
			break;
		}

		$params[$key] = Tools::getValue($key);
	}

	$validForm = true;

	if ($validForm)
		$result = ECheckProcessorTest::testProcess($params, number_format((float)$cart->getOrderTotal(true, 3), 2, '.', ''));
	else
		$isError = true;

	$payment_method = 'Alliance Processing ACH';
	$checkout_type = Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc' : 'order';

	if (isset($result) && $result->passed)
	{
		switch ($result->resultCode)
		{
			case '0':
				$authorizeaim->setTransactionDetail($result);
				$authorizeaim->validateOrder((int)$cart->id, Configuration::get('PS_OS_CHEQUE'), number_format((float)$cart->getOrderTotal(true, 3), 2, '.', ''),
				$payment_method, 'check approved -- perform appropriate action', NULL, NULL, false, $customer->secure_key);
				$url = 'index.php?controller=order-confirmation&';
				if (version_compare(_PS_VERSION_, '1.5', '<'))
					$url = 'order-confirmation.php?';
				Tools::redirect($url.'id_module='.(int)$authorizeaim->id.'&id_cart='.(int)$cart->id.'&key='.$customer->secure_key);
				break;
			case '136':
	 			$url = _PS_VERSION_ >= '1.5' ?'index.php?controller=order&' : $checkout_type.'.php?';
				$url .= 'step=3&cgv=1&aimerror=1&message=check limited exceeded';
				Tools::redirect($url, '');
				break;
			default :
				$url = _PS_VERSION_ >= '1.5' ? 'index.php?controller=order&' : $checkout_type.'.php?';
				$url .= 'step=3&cgv=1&aimerror=1&message=1'.$result->rawResult->AUTHORIZATION_MESSAGE->MESSAGE.'&code='.$result->resultCode;
		  		Tools::redirect($url, '');
				break;
		}
	}
	else
	{
		$url = _PS_VERSION_ >= '1.5' ? 'index.php?controller=order&' : $checkout_type.'.php?';
		$url .= 'step=3&cgv=1&aimerror=1&message='.$result->rawResult->VALIDATION_MESSAGE->VALIDATION_ERROR->MESSAGE.'&code='.$result->resultCode;
		Tools::redirect($url, '');
	}
}
elseif (Tools::getValue('alliancepay') == 'ccauth')
{
	$params = array(
		'x_test_request' => (bool)Configuration::get('ALLIANCE_DEMO'),
		'x_invoice_num' => (int)Tools::getValue('x_invoice_num'),
		'x_amount' => number_format((float)$cart->getOrderTotal(true, 3), 2, '.', ''),
		'x_exp_date' => Tools::safeOutput(Tools::getValue('x_exp_date_m').Tools::getValue('x_exp_date_y')),
		'x_address' => Tools::safeOutput($invoiceAddress->address1.' '.$invoiceAddress->address2),
		'x_zip' => Tools::safeOutput($invoiceAddress->postcode),
		'x_first_name' => Tools::safeOutput($customer->firstname),
		'x_last_name' => Tools::safeOutput($customer->lastname),
		'x_version' => '3.1',
		'x_delim_data' => true,
		'x_delim_char' => '|',
		'x_relay_response' => false,
		'x_type' => 'AUTH_CAPTURE',
		'x_method' => 'CC',
		'x_solution_id' => 'A1000006',
		'x_login' => Tools::safeOutput(Configuration::get('ALLIANCE_LOGIN_ID')),
		'x_tran_key' => Tools::safeOutput(Configuration::get('ALLIANCE_KEY')),
		'x_card_num' => Tools::safeOutput(Tools::getValue('x_card_num')),
		'x_card_code' => Tools::safeOutput(Tools::getValue('x_card_code')),
	);
	
	$postString = '';
	foreach ($params as $key => $value)
		$postString .= $key.'='.urlencode($value).'&';
	$postString = trim($postString, '&');
	
	if (Configuration::get('ALLIANCE_DEMO'))
	{
		$postString .= '&x_test_request=TRUE';
		$url = 'https://test.authorize.net/gateway/transact.dll';
	}
	else
		$url = 'https://secure.authorize.net/gateway/transact.dll';

	/* Do the CURL request ro Authorize.net */
	$request = curl_init($url);
	curl_setopt($request, CURLOPT_HEADER, 0);
	curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($request, CURLOPT_POSTFIELDS, $postString);
	curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE);
	$postResponse = curl_exec($request);
	curl_close($request);
	
	$response = explode('|', $postResponse);
	
	if (!isset($response[7]) || !isset($response[3]) || !isset($response[9]))
	{
		$msg = 'Authorize.net returned a malformed response for cart';
		
		if (isset($response[7]))
			$msg .= ' '.(int)$response[7];
		
		Logger::addLog($msg, 4);
		die('Authorize.net returned a malformed response, aborted.');
	}
	
	$message = $response[3];
	$payment_method = 'Alliance Processing AIM';
	
	switch ($response[0]) // Response code
	{
		case 1: // Payment accepted
			$authorizeaim->setTransactionDetail($response);
			$authorizeaim->validateOrder((int)$cart->id,
				Configuration::get('PS_OS_PAYMENT'), (float)$response[9],
				$payment_method, $message, NULL, NULL, false, $customer->secure_key);
			break;
	
		case 4: // Hold for review
			$authorizeaim->validateOrder((int)$cart->id,
				Configuration::get('ALLIANCE_HOLD_REVIEW_OS'), (float)$response[9],
				$authorizeaim->displayName, $response[3], NULL, NULL, false, $customer->secure_key);
			break;
	
		default:
			$error_message = (isset($response[3]) && !empty($response[3])) ? urlencode(Tools::safeOutput($response[3])) : '';
			$checkout_type = Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc' : 'order';
			$url = _PS_VERSION_ >= '1.5' ? 'index.php?controller='.$checkout_type.'&' : $checkout_type.'.php?';
			$url .= 'step=3&cgv=1&aimerror=1&message='.$error_message;
	
			if (!isset($_SERVER['HTTP_REFERER']) || strstr($_SERVER['HTTP_REFERER'], 'order'))
				Tools::redirect($url);
			else if (strstr($_SERVER['HTTP_REFERER'], '?'))
				Tools::redirect($_SERVER['HTTP_REFERER'].'&aimerror=1&message='.$error_message, '');
			else
				Tools::redirect($_SERVER['HTTP_REFERER'].'?aimerror=1&message='.$error_message, '');
			exit;
	}
	/* If auth ends */
}

$url = 'index.php?controller=order-confirmation&';

if (version_compare(_PS_VERSION_, '1.5', '<'))
	$url = 'order-confirmation.php?';

Tools::redirect($url.'id_module='.(int)$authorizeaim->id.'&id_cart='.(int)$cart->id.'&key='.$customer->secure_key);
