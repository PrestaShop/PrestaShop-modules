<?php
require('../../../config/config.inc.php');

$id_cart = pSQL($_GET['id_cart']);
$phone = pSQL($_GET['phone']);

if (Tools::getValue('token') == '' || Tools::getValue('token') != Configuration::get('TNT_CARRIER_TOKEN'))
	die('Invalid Token');

if (preg_match("#^0[1-9]([-. ]?[0-9]{2}){4}$#", $phone))
{
	$cart = new Cart($id_cart);
	$address = new Address($cart->id_address_delivery);
	$address->phone_mobile = $phone;
	if ($address->save())
		echo "ok";
	else
		echo "null";
}

?>