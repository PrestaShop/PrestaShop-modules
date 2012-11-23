<?php
/*
* 2007-2011 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/*
	1. Check if the array of products in cart sent with AJAX is already cached
	2. If not cached: retrieve it from Avalara and put it in the cache table
*/

include(dirname(__FILE__).'/../../config/config.inc.php');
include(_PS_ROOT_DIR_.'/init.php');
include(_PS_MODULE_DIR_.'avalaratax/avalaratax.php');

$avalaraModule = new AvalaraTax();
if (!Validate::isLoadedObject($avalaraModule) || !$avalaraModule->active)
	die('{"hasError" : true, "errors" : ["Error while loading Avalara module"]}');

$timeout = Configuration::get('AVALARATAX_TIMEOUT');
ini_set('max_execution_time', (int)$timeout > 0 ? (int)$timeout : 120);

// Check if the AJAX call is valid
if (!isset($_POST['id_cart']) || !isset($_POST['id_address']) || !isset($_POST['ajax']) || !isset($_POST['token']) ||
	!(int)$_POST['id_cart'] || !(int)$_POST['id_address'] || $_POST['ajax'] != 'getProductTaxRate' ||
	md5(_COOKIE_KEY_.Configuration::get('PS_SHOP_NAME')) != $_POST['token'])
	die('{"hasError":true, "errors":["Invalid ajax call"]}');

// Make the products list
$cart = new Cart((int)$_POST['id_cart']);
if (!Validate::isLoadedObject($cart))
	die('{"hasError":true, "errors":["Error while loading Cart"]}');

$ids_product = array();
foreach ($cart->getProducts() as $product)
	$ids_product[] = (int)$product['id_product'];

// Stop if cart is empty
if (!count($ids_product))
	die('{"hasError":true, "errors":["Cart is empty"]}');

$ids_product = implode(', ', $ids_product);

$address = new Address((int)$_POST['id_address']);
if (!Validate::isLoadedObject($address))
	die('{"hasError":true, "errors":["Error while loading Address"]}');

$region = null;
if ((int)$address->id_state)
{
	$state = new State((int)$address->id_state);
	if (!Validate::isLoadedObject($state))
		die('{"hasError":true, "errors":["Error while loading State"]}');

	$region = $state->iso_code;
}

$taxable = true;
//check if it is outside the state and if we are in united state and if conf AVALARATAX_TAX_OUTSIDE IS ENABLE
if ($region && !Configuration::get('AVALARATAX_TAX_OUTSIDE') && $region != Configuration::get('AVALARATAX_STATE'))
	$taxable = false;

// Check cache before asking Avalara webservice
$pc = CacheTools::checkProductCache($ids_product, $region, $cart);
$cc = CacheTools::checkCarrierCache($cart);

if (!$pc && !$cc)
	die('{"hasError":false, "cached_tax":true}');

if ($pc)
	CacheTools::updateProductsTax($avalaraModule, $cart, (int)$_POST['id_address'], $region, $taxable);
if ($cc)
	CacheTools::updateCarrierTax($avalaraModule, $cart, (int)$_POST['id_address'], $taxable);

die('{"hasError":false, "cached_tax":false, "total_tax":"'.Tools::displayPrice($cart->getOrderTotal() - $cart->getOrderTotal(false)).'"}');
