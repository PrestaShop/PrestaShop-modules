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

if (!defined('_PS_VERSION_'))
	exit;

if (!defined('_GIVEIT_CLASSES_DIR_'))
	define('_GIVEIT_CLASSES_DIR_', dirname(__FILE__).'/classes/');

if (!defined('_GIVEIT_TPL_DIR_'))
	define('_GIVEIT_TPL_DIR_', dirname(__FILE__).'/views/templates/');

if (!defined('_GIVEIT_MODELS_DIR_'))
	define('_GIVEIT_MODELS_DIR_', dirname(__FILE__).'/models/');

if (!defined('_GIVEIT_CSS_URI_'))
	define('_GIVEIT_CSS_URI_', _MODULE_DIR_.'giveit/css/');

if (!defined('_GIVEIT_JS_URI_'))
	define('_GIVEIT_JS_URI_', _MODULE_DIR_.'giveit/js/');

if (!defined('_GIVEIT_AJAX_URL_'))
	define('_GIVEIT_AJAX_URL_', _MODULE_DIR_.'giveit/giveit.ajax.php');

if (!defined('_GIVE_IT_LOGIN_URI_'))
	define('_GIVE_IT_LOGIN_URI_', 'https://shops.give.it');

if (!defined('_GIVE_IT_DOCUMENTATION_URI_'))
	define('_GIVE_IT_DOCUMENTATION_URI_', 'http://shops.give.it/support/prestashop');

if (!defined('_GIVEIT_ENVIRONMENT_'))
	define('_GIVEIT_ENVIRONMENT_', 'live');

if (!defined('_GIVEIT_DEBUG_MODE_'))
	define('_GIVEIT_DEBUG_MODE_', true);

if (!defined('_GIVEIT_EXTERNAL_HELP_URI_'))
	define('_GIVEIT_EXTERNAL_HELP_URI_', 'https://shops.give.it/prestashop/help');

if (!defined('_GIVEIT_EXTERNAL_ACCESS_URI_'))
	define('_GIVEIT_EXTERNAL_ACCESS_URI_', 'https://shops.give.it/prestashop/install');

if (_MODULE_DIR_ == 'external_give_class')
	$token = Tools::getToken(false);
	
if (!function_exists('bqSQL'))
{
	function bqSQL($string)
	{
		return str_replace('`', '\`', pSQL($string));
	}
}