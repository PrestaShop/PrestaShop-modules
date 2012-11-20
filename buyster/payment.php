<?php
$useSSL = true;
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
require_once(_PS_MODULE_DIR_."/buyster/classes/BuysterOperation.php");
require_once(_PS_MODULE_DIR_."/buyster/classes/BuysterWebService.php");

if (Tools::getValue('payment'))
	$typePayment = htmlentities(Tools::getValue('payment'));

$cartId = $cart->id;
$operation = new BuysterOperation($cartId);

if (isset($typePayment) && $typePayment == 'multiple')
	$operation->setOperation('paymentN');
else
	$operation->setOperation(Configuration::get('BUYSTER_PAYMENT_TRANSACTION_TYPE'));

$buyster_token = substr(md5(rand()), 0, 6);
	
$ref = "BuysterRef".date('Ymdhis').$buyster_token.$cart->id; // be carefull the reference must be under this request : [BuysterRef][YYYYMMDDhhmmss][token][cartId]
$operation->setReference($ref);
$operation->setToken($buyster_token);

//call webservice buyster
$webService = new BuysterWebService();
$url = $webService->getUrl($cart->getOrderTotal(), $_SERVER["REMOTE_ADDR"], $cart->id, $ref, $operation->getOperation(), $cart->id_customer); //amount, address ip, orderid, transactionRef , type , customerId

if ($url['responseCode'] == '00' && isset($url['redirectionURL']) && $url['redirectionURL'] != '')
{
	header("location:".$url['redirectionURL']);
	echo '<p>Si vous n\'&ecirc;tes pas redirig&eacute;s dans un petit instant, Veuillez cliquer <a href="'.$url['redirectionURL'].'">ici</a></p>';
}
else
	echo 'Problem : '.(isset($url['responseDescription']) ? $url['responseDescription'] : '');
include(dirname(__FILE__).'/../../footer.php');
?>