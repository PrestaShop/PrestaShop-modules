<?php

/*
 *  2007-2014 PrestaShop
 * 
 * NOTICE OF LICENSE
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php* If you did not receive a copy of the license and are unable to
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
 *  @copyright  2007-2014 PrestaShop SA
 *  @version  Release: $Revision: 7515 $
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

/*
 * This file can be called using a cron to generate Google Sitemap files automatically
 */

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
/* Check to security tocken */
if (substr(Tools::encrypt('gsitemap/cron'), 0, 10) != Tools::getValue('token') || !Module::isInstalled('gsitemap'))
	die('Bad token');

include(dirname(__FILE__).'/gsitemap.php');
$gsitemap = new Gsitemap();
/* Check if the module is enabled */
if ($gsitemap->active)
{
	/* Check if the requested shop exists */
	$shops = Db::getInstance()->ExecuteS('SELECT id_shop FROM `'._DB_PREFIX_.'shop`');
	$list_id_shop = array();
	foreach ($shops as $shop)
		$list_id_shop[] = (int)$shop['id_shop'];

	$id_shop = (isset($_GET['id_shop']) && in_array($_GET['id_shop'], $list_id_shop)) ? (int)$_GET['id_shop'] : (int)Configuration::get('PS_SHOP_DEFAULT');
	$gsitemap->cron = true;
	
	/* for the main run initiat the sitemap's files name stored in the database */
	if (!isset($_GET['continue']))
		$gsitemap->emptySitemap((int)$id_shop);

	/* Create the Google Sitemap's files */
	p($gsitemap->createSitemap((int)$id_shop));
	
} 