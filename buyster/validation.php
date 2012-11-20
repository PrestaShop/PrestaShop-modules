<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');

require_once(_PS_MODULE_DIR_."/buyster/buyster.php");
require_once(_PS_MODULE_DIR_."/buyster/classes/BuysterOperation.php");
require_once(_PS_MODULE_DIR_."/buyster/classes/BuysterWebService.php");

$buyster = new Buyster();

$cartId = substr(htmlentities($_POST['transactionReference']), 30); //[BuysterRef][YYYYMMDDhhmmss][token][cartId]
$cart = new Cart($cartId);

$post_token = substr($_POST['transactionReference'], 24, 6);
$payment_token = BuysterOperation::getTokenId($cart->id);

if ($post_token != $payment_token || $payment_token == '')
	die('Invalid Token');

$ref = BuysterOperation::getReferenceId($cart->id);
$webService = new BuysterWebService();
$result = $webService->operation("DIAGNOSTIC", $ref);

if ($cart->id_customer == 0 OR $cart->id_address_delivery == 0 OR $cart->id_address_invoice == 0 OR !$buyster->active)
	Tools::redirectLink(__PS_BASE_URI__.'order.php?step=1');

BuysterOperation::setStatusId($cart->id, htmlentities($_POST['status']));
$operation = BuysterOperation::getOperationId($cart->id);
if (isset($_POST['responseDescription']))
	$responseDescription = str_replace('+', ' ', Tools::safeOutput($_POST['responseDescription']));
if (htmlentities($_POST['responseCode']) != '00')
$buyster->validateOrder($cart->id,
						Configuration::get('PS_OS_ERROR'), 
						0,
						$buyster->name, 
						$responseDescription,
						array(), 
						NULL, 
						false, 
						$cart->secure_key);
else
{
	if ($operation == 'paymentValidation' && $result['status'] == 'TO_VALIDATE')
		$buyster->validateOrder($cart->id,
						Configuration::get('BUYSTER_PAYMENT_STATE_VALIDATION'), 
						(float)($result['amount'] / 100),
						$buyster->name, 
						$responseDescription,
						array(), 
						NULL, 
						false, 
						$cart->secure_key);
	else if ($result['status'] == 'TO_CAPTURE')
		$buyster->validateOrder($cart->id,
						Configuration::get('PS_OS_PAYMENT'),
						(float)($result['amount'] / 100),
						$buyster->name, 
						$responseDescription,
						array(), 
						NULL, 
						false, 
						$cart->secure_key);
	else
		$buyster->validateOrder($cart->id,
						Configuration::get('PS_OS_ERROR'), 
						0, 
						$buyster->name, 
						$responseDescription,
						array(), 
						NULL, 
						false, 
						$cart->secure_key);
}
?>
