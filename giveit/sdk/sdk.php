<?php
/**
 * @copyright    give.it 2013
 * @author       David Kelly, Rene Pot
 *
 * required:
 * - PHP > 5.2.0
 *
 * This is a custom version of the Give.it SDK for PrestaShop
 */


require_once dirname(__FILE__).'/classes/Base.php';
require_once dirname(__FILE__).'/classes/Callback.php';
require_once dirname(__FILE__).'/classes/Crypt.php';
require_once dirname(__FILE__).'/classes/Product.php';
require_once dirname(__FILE__).'/classes/SDK.php';
require_once dirname(__FILE__).'/classes/Client.php';
require_once dirname(__FILE__).'/classes/Collection.php';
require_once dirname(__FILE__).'/classes/Object.php';
require_once dirname(__FILE__).'/classes/Sale.php';
require_once dirname(__FILE__).'/classes/Payment.php';
require_once dirname(__FILE__).'/classes/Option.php';
require_once dirname(__FILE__).'/classes/Choice.php';

if (_MODULE_DIR_ == 'giveit_external_class')
	$token = Tools::getToken(false);
