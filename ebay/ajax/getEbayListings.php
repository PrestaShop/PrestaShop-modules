<?php

include(dirname(__FILE__).'/../../../config/config.inc.php');
include('../../../init.php');
include('../../../modules/ebay/ebay.php');

if (!Tools::getValue('token') || Tools::getValue('token') != Configuration::get('EBAY_SECURITY_TOKEN'))
	die('ERROR : INVALID TOKEN');

$ebay = new eBay();
$ebay->displayEbayListingsAjax((int)Tools::getValue('id_employee'));