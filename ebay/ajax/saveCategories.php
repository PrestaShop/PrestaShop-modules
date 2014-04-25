<?php

include(dirname(__FILE__).'/../../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../../init.php');
include('../ebay.php');

if (!Tools::getValue('token') || Tools::getValue('token') != Configuration::get('EBAY_SECURITY_TOKEN'))
	die('ERROR: Invalid Token');

$ebay = new eBay();
$ebay->_postProcessCategory();