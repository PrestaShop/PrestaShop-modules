<?php
/*
* 2013 TextMaster
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to info@textmaster.com so we can send you a copy immediately.
*
* @author JSC INVERTUS www.invertus.lt <help@invertus.lt>
* @copyright 2013 TextMaster
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* International Registered Trademark & Property of TextMaster
*/

if (!defined('_PS_VERSION_'))
	exit;

if (!defined('TEXTMASTER_SANDBOX_ENVIRONMENT'))
	define('TEXTMASTER_SANDBOX_ENVIRONMENT', false);

if (!defined('TEXTMASTER_STAGIGN_ENVIRONMENT'))
	define('TEXTMASTER_STAGIGN_ENVIRONMENT', false); //if sandbox is enabled, staging is ignored even if enabled

if (!defined('TEXTMASTER_API_URI'))
{
	if (TEXTMASTER_SANDBOX_ENVIRONMENT)
		define('TEXTMASTER_API_URI', 'http://api.sandbox.textmaster.com');
	elseif (TEXTMASTER_STAGIGN_ENVIRONMENT)
		define('TEXTMASTER_API_URI', 'http://api.staging.textmaster.com');
	else
		define('TEXTMASTER_API_URI', 'http://api.textmaster.com');
}

if (!defined('TEXTMASTER_EU_URI'))
{
	if (TEXTMASTER_SANDBOX_ENVIRONMENT)
		define('TEXTMASTER_EU_URI', 'http://eu.sandbox.textmaster.com');
	elseif (TEXTMASTER_STAGIGN_ENVIRONMENT)
		define('TEXTMASTER_EU_URI', 'http://eu.staging.textmaster.com');
	else
		define('TEXTMASTER_EU_URI', 'http://eu.textmaster.com');
}

if (!defined('TEXTMASTER_CLIENT_ID'))
{
	if (TEXTMASTER_SANDBOX_ENVIRONMENT)
		define('TEXTMASTER_CLIENT_ID', '9874671043c114b4fce868162b240982ee1388532af653ee5ca1a8256df8dfc8');
	elseif (TEXTMASTER_STAGIGN_ENVIRONMENT)
		define('TEXTMASTER_CLIENT_ID', '9874671043c114b4fce868162b240982ee1388532af653ee5ca1a8256df8dfc8');
	else
		define('TEXTMASTER_CLIENT_ID', '9874671043c114b4fce868162b240982ee1388532af653ee5ca1a8256df8dfc8');
}

if (!defined('TEXTMASTER_CLIENT_SECRET'))
{
	if (TEXTMASTER_SANDBOX_ENVIRONMENT)
		define('TEXTMASTER_CLIENT_SECRET', '81924c0f34bd7bba24eeede295e6d993d11e216c9290e925f205908bde8ff5d6');
	elseif (TEXTMASTER_STAGIGN_ENVIRONMENT)
		define('TEXTMASTER_CLIENT_SECRET', '81924c0f34bd7bba24eeede295e6d993d11e216c9290e925f205908bde8ff5d6');
	else
		define('TEXTMASTER_CLIENT_SECRET', '81924c0f34bd7bba24eeede295e6d993d11e216c9290e925f205908bde8ff5d6');
}

define('TEXTMASTER_API_VERSION', 'v1');
define('TEXTMASTER_API_TIMEOUT_IN_SECONDS', 30);
define('TEXTMASTER_TRACKER_ID', '517e6cf34fe01600020006b2');
define('TEXTMASTER_PRICING_URL', 'http://eu.textmaster.com/clients/pricing');
define('TEXTMASTER_DEFAULT_LOCALE', 'en-EU');