<?php
require('./../../config/config.inc.php');

$id_cart = pSQL($_GET['id']);
$city = pSQL($_GET['city']);

if (Tools::getValue('token') == '' || Tools::getValue('token') != Configuration::get('TNT_CARRIER_TOKEN'))
	die('Invalid Token');

$cart = new Cart($id_cart);
$address = new Address($cart->id_address_delivery);
$address->city = $city;

if (strpos($city, 'PARIS') === 0 || strpos($city, 'MARSEILLE') === 0 || strpos($city, 'LYON') === 0)
	{
		$code = substr($city, -2);
		$address->postcode = substr($address->postcode, 0, 3).$code;
	}
if ($address->save())
	echo "ok";
else
	echo "null";

?>