<?php

/*
 * 2007-2014 PrestaShop
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
 *  @copyright  2007-2014 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

include(dirname(__FILE__).'/../../../config/config.inc.php');
include(dirname(__FILE__).'/../classes/EbayProductConfiguration.php');

if (!Tools::getValue('token') || Tools::getValue('token') != Configuration::get('EBAY_SECURITY_TOKEN'))
	die('ERROR: Invalid Token');

Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_category_configuration', array('sync' => (int)Tools::getValue('action')), 'UPDATE', '`id_category` = '.(int)Tools::getValue('id_category'));

if (version_compare(_PS_VERSION_, '1.5', '>'))
{
	$nb_products = Db::getInstance()->getValue('SELECT COUNT(*) AS nb FROM(
		SELECT p.id_product
		FROM '._DB_PREFIX_.'product AS p
		INNER JOIN '._DB_PREFIX_.'stock_available AS s ON s.id_product = p.id_product
		WHERE s.`quantity` > 0 AND `active` = 1
		AND p.`id_category_default` IN (
			SELECT `id_category`
			FROM `'._DB_PREFIX_.'ebay_category_configuration`
			WHERE `id_ebay_category` > 0
			AND `sync` = 1)
		AND p.id_product NOT IN ('.EbayProductConfiguration::getBlacklistedProductIdsQuery().')
		GROUP BY p.id_product) TableRequete');
}
else
{
	$nb_products = Db::getInstance()->getValue('SELECT COUNT(`id_product`) as nb
		FROM `'._DB_PREFIX_.'product` AS p
		WHERE p.`quantity` > 0
		AND p.`active` = 1
		AND p.`id_category_default` IN (
			SELECT `id_category`
			FROM `'._DB_PREFIX_.'ebay_category_configuration`
			WHERE `id_ebay_category` > 0
			AND `sync` = 1)
		AND p.id_product NOT IN ('.EbayProductConfiguration::getBlacklistedProductIdsQuery().')');
}

echo $nb_products;