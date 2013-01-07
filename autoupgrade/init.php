<?php
/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/


// autoloader 1.3 / 1.4
ob_start();
$timerStart = microtime(true);

require_once(AUTOUPGRADE_MODULE_DIR.'classes/Tools14.php');
require_once(AUTOUPGRADE_MODULE_DIR.'AdminSelfUpgrade.php');

if (!class_exists('Tools',false))
	eval('class Tools extends Tools14{}');

require_once(_PS_ROOT_DIR_.'/modules/autoupgrade/classes/Upgrader.php');

if (!class_exists('Upgrader',false))
{
	if(file_exists(_PS_ROOT_DIR_.'/override/classes/Upgrader.php'))
		require_once(_PS_ROOT_DIR_.'/override/classes/Upgrader.php');
	else
		eval('class Upgrader extends UpgraderCore{}');
}

