<?php

require('../../../config/config.inc.php');
require('../../../init.php');

//$id_cart = (int)$_POST['id_cart'];
global $cookie;
$id_cart = (int)$cookie->id_cart;

$data = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'tnt_carrier_drop_off` WHERE `id_cart` = "'.(int)($id_cart).'"');

if (isset($_POST['due_date']))
{
	$date = pSQL($_POST['due_date']);
	$day = substr($date, 0, 2);
	$month = substr($date, 3, 2);
	$year = substr($date, 6, 4);
	$date = $year.'-'.$month.'-'.$day;

	if (count($data) > 0)
		Db::getInstance()->Execute(
			'UPDATE `'._DB_PREFIX_.'tnt_carrier_drop_off` SET `due_date` = "'.$date.'"
WHERE `id_cart` = "'.(int)($id_cart).'"');
	else
		Db::getInstance()->Execute(
			'INSERT INTO `'._DB_PREFIX_.'tnt_carrier_drop_off` (`id_cart`, `due_date`) 
VALUES ("'.(int)($id_cart).'", "'.$date.'")');

}
else
{
	$code = pSQL($_POST['tntRCSelectedCode']);
	$name = substr(pSQL($_POST['tntRCSelectedNom']), 0, 32);
	$address = pSQL($_POST['tntRCSelectedAdresse']);
	$zipcode = pSQL($_POST['tntRCSelectedCodePostal']);
	$city = pSQL($_POST['tntRCSelectedCommune']);
	if (count($data) > 0)
	{
		echo "ok";
		Db::getInstance()->Execute(
			'UPDATE `'._DB_PREFIX_.'tnt_carrier_drop_off` 
SET `code` = "'.$code.'", 
`name` = "'.$name.'",
`address` = "'.$address.'", 
`zipcode` = "'.$zipcode.'", 
`city` = "'.$city.'" 
WHERE `id_cart` = "'.(int)($id_cart).'"');
	}
	else
		Db::getInstance()->Execute(
			'INSERT INTO `'._DB_PREFIX_.'tnt_carrier_drop_off` 
(`id_cart`, `code`, `name`, `address`, `zipcode`, `city`) 
VALUES ("'.(int)($id_cart).'", "'.$code.'", "'.$name.'", "'.$address.'", "'.$zipcode.'", "'.$city.'")');
}


?>