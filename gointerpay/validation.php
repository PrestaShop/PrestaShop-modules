<?php
/*
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2014 PrestaShop SA
 *  @version  Release: $Revision: 1.7.4 $
 *
 *  International Registered Trademark & Property of PrestaShop SA
 */

include_once(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include_once(_PS_MODULE_DIR_.'gointerpay/gointerpay.php');

$interpay = new GoInterpay();
if ($interpay->active)
	$interpay->validation();