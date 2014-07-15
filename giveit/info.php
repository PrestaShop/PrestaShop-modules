<?php
/**
 * 2013 Give.it
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@give.it so we can send you a copy immediately.
 *
 * @author    JSC INVERTUS www.invertus.lt <help@invertus.lt>
 * @copyright 2013 Give.it
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of Give.it
 */
require_once('sdk/sdk.php');
include_once(dirname(__FILE__).'/../../config/config.inc.php');

$sdk_version = GiveItSdk::VERSION;
$module_instance = Module::getInstanceByName('giveit');
$module_version = $module_instance->version;

$info = array(
	'sdk' => $sdk_version,
	'module' => $module_version,
	'prestashop' => _PS_VERSION_,
	'php' => phpversion(),
	'php-sapi' => php_sapi_name()
);

$info = Tools::jsonEncode($info);
$private_key = Configuration::get(GiveIt::PRIVATE_KEY);

if (!$private_key)
	die('ERROR: private key must be submitted.');

$crypt = new GiveItSdkCrypt();
$info = $crypt->encode($info, $private_key);

echo $info;
exit;