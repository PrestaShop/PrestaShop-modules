<?php

/*
* 2007-2013 PrestaShop
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
*  @copyright  2007-2013 PrestaShop SA
*  @version  Release: $Revision: 14390 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_2_2($object, $install = false)
{
	if ($object->active || $install)
	{
		Configuration::updateValue('GSITEMAP_PRIORITY_HOME', 1.0);
		Configuration::updateValue('GSITEMAP_PRIORITY_PRODUCT', 0.9);
		Configuration::updateValue('GSITEMAP_PRIORITY_CATEGORY', 0.8);
		Configuration::updateValue('GSITEMAP_PRIORITY_MANUFACTURER', 0.7);
		Configuration::updateValue('GSITEMAP_PRIORITY_SUPPLIER', 0.6);
		Configuration::updateValue('GSITEMAP_PRIORITY_CMS', 0.5);
		Configuration::updateValue('GSITEMAP_FREQUENCY', 'weekly');
		Configuration::updateValue('GSITEMAP_LAST_EXPORT', false);

		return Db::getInstance()->Execute('DROP TABLE IF  EXISTS `'._DB_PREFIX_.'gsitemap_sitemap`') && Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'gsitemap_sitemap` (`link` varchar(255) DEFAULT NULL, `id_shop` int(11) DEFAULT 0) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;');
	}
	$object->upgrade_detail['2.2'][] = 'GSitemap upgrade error !';
	return false;
}