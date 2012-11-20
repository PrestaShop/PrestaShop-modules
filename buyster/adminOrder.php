<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
require_once(_PS_MODULE_DIR_.'/buyster/buyster.php');
require_once(_PS_MODULE_DIR_.'/buyster/classes/BuysterWebService.php');
require_once(_PS_MODULE_DIR_.'/buyster/classes/BuysterOperation.php');

global $smarty, $cookie;

if (Tools::getValue('token') == '' || Tools::getValue('token') != Configuration::get('BUYSTER_PAYMENT_TOKEN'))
	die('Invalid Token');

$cookie->id_lang = '2';

$orderId = (int)$_GET['id_order'];

$buyster = new Buyster(); 

$smarty->assign('content', $buyster->getContentAdminOrder($orderId));

$smarty->display(dirname(__FILE__).'/tpl/adminOrder.tpl');
?>