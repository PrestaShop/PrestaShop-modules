<?php
require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
require_once(dirname(__FILE__).'/kwixo.php');
if (!class_exists('HashMD5'))
	require_once(dirname(__FILE__).'/fianet_key_64bits.php'); 

$payment = new kwixo();

$rnp_md5 = new HashMD5();
$verification_hash = $rnp_md5->hash(Configuration::get('RNP_MERCHID').Tools::getValue('RefID').Tools::getValue('TransactionID'));

if (!Tools::getValue('Tag') AND Tools::getValue('Tag')!=1)
	$errors .= $payment->displayName.' '.$payment->l('payment canceled')."\n";

if (!Tools::getValue('HashControl'))
	$errors .= $payment->displayName.' '.$payment->l('hash control not specified')."\n";	
else
	$hashControl = Tools::getValue('HashControl');
	
if (!Tools::getValue('custom'))
	$errors .= $payment->displayName.' '.$payment->l('key "custom" not specified, can\'t rely to cart')."\n";
else
	$id_cart = intval(Tools::getValue('custom'));

if (!Tools::getValue('id_module'))
	$errors .= $payment->displayName.' '.$payment->l('key "module" not specified, can\'t rely to payment module')."\n";
else
	$id_module = intval(Tools::getValue('id_module'));
	
if (!isset($_POST['amount']))
	$errors .= $payment->displayName.' '.$payment->l('"amount" not specified, can\'t control the amount paid')."\n";
else
	$amount = floatval(Tools::getValue('amount'));
	
if (empty($errors))
{
	$cart = new Cart($id_cart);
	if (!$cart->id)
		$errors = $payment->l('cart not found')."\n";
	elseif (Order::getOrderByCartId($id_cart))
		$errors = $payment->l('order already exists')."\n";
	else
	{
		$feedback = $payment->l('Transaction OK:').' RefID='.Tools::getValue('RefID').' & TransactionID='.Tools::getValue('TransactionID');		
		$payment->validateOrder(intval($cart->id), intval(Configuration::get('RNP_ID_ORDERSTATE')), $amount, 'kwixo', $feedback, NULL, $cart->id_currency);
		if ($cookie->id_cart == intval($cookie->last_id_cart))

		unset($cookie->id_cart);
	}
}
else
	$errors .= $payment->l('One or more error occured during the validation')."\n";
	
if (!empty($errors) AND isset($id_cart) AND isset($amount))
{

	if ($verification_hash == $hashControl)
		$errors .= $payment->displayName.$payment->l('hash control invalid (data do not come from Receive&Pay)')."\n";
		
	$payment->validateOrder($id_cart, intval(_PS_OS_CANCELED_), $amount, $payment->displayName, $errors);
	if ($cookie->id_cart == intval($cookie->last_id_cart))
		unset($cookie->id_cart);
}
$url = 'order-confirmation.php?';
if (!empty($errors) OR !$id_cart OR !$id_module)
	$url.= 'error=true';
else
{
	$customer = new Customer(intval($cart->id_customer));
	$url.= 'id_cart='.$id_cart.'&id_module='.$id_module.'&key='.$customer->secure_key;
}
Tools::redirect($url);	
			
?>
