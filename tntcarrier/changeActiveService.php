<?php
include( '../../config/config.inc.php' );

$id = $_GET['id'];
$display = $_GET['deleted'];

if (Tools::getValue('token') == '' || Tools::getValue('token') != Configuration::get('TNT_CARRIER_TOKEN'))
	die('Invalid Token');

Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'carrier` SET `deleted` = "'.(int)($display).'" WHERE `id_carrier` = '.(int)($id).'');
?>