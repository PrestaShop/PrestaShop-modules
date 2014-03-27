<?php
/*
* Shopgate GmbH
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file AFL_license.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/AFL-3.0
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to interfaces@shopgate.com so we can send you a copy immediately.
*
* @author Shopgate GmbH, Schloßstraße 10, 35510 Butzbach <interfaces@shopgate.com>
* @copyright  Shopgate GmbH
* @license   http://opensource.org/licenses/AFL-3.0 Academic Free License ("AFL"), in the version 3.0
*/

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/shopgate.php');
require_once(dirname(__FILE__).'/override/classes/Cart.php');

if(version_compare(_PS_VERSION_, '1.4.0.2', '>=')){
	$controller = new FrontController();
	$controller->init();
	$modules = ModuleCore::getModulesInstalled();
} else {
	// in versions before 1.4.0.2 FrontController doesn't exist
	require_once(dirname(__FILE__).'/../../init.php');
	$modules = Module::getModulesInstalled();
}

$moduleIsActive = 0;
foreach($modules as $key => $module){
	if($module['name'] == 'shopgate' && !empty($module['active'])){
		$moduleIsActive = 1;
	}
}
if($moduleIsActive == 0){
	throw new ShopgateLibraryException(ShopgateLibraryException::UNKNOWN_ERROR_CODE, 'shopgate module is not installed!');
	exit;
}

// needed for compatiblitiy
function getOrderStateId($order_state_var) {
	return (int)(defined($order_state_var) ? constant($order_state_var) : (defined('_'.$order_state_var.'_') ? constant('_'.$order_state_var.'_') : Configuration::get($order_state_var)));
}

// select the correct plugin
if(getOrderStateId('PS_COUNTRY_DEFAULT') && ($countryId = getOrderStateId('PS_COUNTRY_DEFAULT')) !== 0 || getOrderStateId('PS_SHOP_COUNTRY_ID') && ($countryId = getOrderStateId('PS_SHOP_COUNTRY_ID')) !== 0){
	$countryIsoCode = Tools::strtoupper(Db::getInstance()->getValue('SELECT `iso_code` FROM `' . _DB_PREFIX_ . 'country` WHERE `id_country` = '. (int)$countryId));
} elseif(Configuration::get('PS_LOCALE_COUNTRY')) {
	$countryIsoCode = Configuration::get('PS_LOCALE_COUNTRY');
}

if(in_array(Tools::strtoupper($countryIsoCode), array('US'))){
	$plugin = new PSShopgatePluginUS();
} else {
	$plugin = new PSShopgatePlugin();
}

$response = $plugin->handleRequest($_POST);
?>