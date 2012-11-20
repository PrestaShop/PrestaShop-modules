<?php
require_once(dirname(__FILE__).'/../../config/config.inc.php');

require_once(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/kwixo.php');
require_once(dirname(__FILE__).'/fianet_key_64bits.php'); 
$payment = new kwixo();

$orderState = _PS_OS_PAYMENT_;

if (!Tools::getValue('Tag'))
	$orderState  = _PS_OS_ERROR_;

if (!Tools::getValue('HashControl'))
	$orderState  = _PS_OS_ERROR_;
else
	$hashControl = Tools::getValue('HashControl');

if (!Tools::getValue('custom'))
	exit;
	
$id_cart = intval(Tools::getValue('custom'));
$amount = floatval(Tools::getValue('amount'));
$cart = new Cart($id_cart);
if (!$cart->id)
	exit;	

$rnp_md5 = new HashMD5();
$controlHash = $rnp_md5->hash(Configuration::get('RNP_CRYPTKEY').Tools::getValue('RefID').Tools::getValue('TransactionID'));
	
if ($controlHash != $hashControl)
{
	$orderState = _PS_OS_CANCELED_;
	$errors .= $payment->displayName.$payment->l('hash control invalid (data do not come from Receive&Pay)')."\n";
}

global $cookie, $cart;

if (!Order::getOrderByCartId($cart->id))
{
		$feedback = 'Order Create';		
		$payment->validateOrder(intval($cart->id), intval(Configuration::get('RNP_ID_ORDERSTATE')), $amount, 'kwixo', $feedback, NULL, $cart->id_currency);
		if ($cookie->id_cart == intval($cookie->last_id_cart))
			unset($cookie->id_cart);
}

if($id_order = Order::getOrderByCartId(intval($cart->id)))
	$order = new Order(intval($id_order));
	
switch(Tools::getValue('Tag'))
{
	case 0:
			$orderHistory = new OrderHistory();
			$orderHistory->id_order = intval($id_order);
			$orderHistory->id_order_state = _PS_OS_CANCELED_;
			$orderHistory->save();
			//$orderHistory->changeIdOrderState(intval($orderHistory->id), intval($order->id));	
		break;
	case 1:
	case 13:
	case 10:
		if ($order->getCurrentState() == intval(Configuration::get('RNP_ID_ORDERSTATE')))
		{
			$orderHistory = new OrderHistory();
			$orderHistory->id_order = intval($id_order);
			$orderHistory->id_order_state = _PS_OS_PAYMENT_;
			$orderHistory->save();
			//$orderHistory->changeIdOrderState(intval($orderHistory->id), intval($order->id));	
		}
		break;
	case 2:
		if ($order->getCurrentState() == intval(Configuration::get('RNP_ID_ORDERSTATE')))
		{
			$orderHistory = new OrderHistory();
			$orderHistory->id_order = intval($id_order);
			$orderHistory->id_order_state = _PS_OS_CANCELED_;
			$orderHistory->save();
			//$orderHistory->changeIdOrderState(intval($orderHistory->id), intval($order->id));	
		}
		break;
	case 3:
		$payment->validateOrder($id_cart, intval(Configuration::get('RNP_ID_ORDERSTATE')), $amount, $payment->displayName, $errors);
		break;
	case 11:
			$orderState  = _PS_OS_ERROR_;
		break;
	case 101 :
			$orderHistory = new OrderHistory();
			$orderHistory->id_order = intval($id_order);
			$orderHistory->id_order_state = _PS_OS_CANCELED_;
			$orderHistory->save();
			//$orderHistory->changeIdOrderState(intval($orderHistory->id), intval($order->id));	
		break;
	case 100:
			$orderHistory = new OrderHistory();
			$orderHistory->id_order = intval($id_order);
			$orderHistory->id_order_state = _PS_OS_DELIVERED_;
			$orderHistory->save();
			//$orderHistory->changeIdOrderState(intval($orderHistory->id), intval($order->id));
		break;
}

?>
