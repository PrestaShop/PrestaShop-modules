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
 *  @license	http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

$config_path = dirname(__FILE__).'/../../../config/config.inc.php';
if (file_exists($config_path))
{
	include_once ($config_path);
	include_once dirname(__FILE__).'/../classes/EbayCountrySpec.php';
	include_once dirname(__FILE__).'/../classes/EbaySyncBlacklistProduct.php';
	
	$ebay_country = new EbayCountrySpec();
	$id_lang = $ebay_country->getIdLang();
	
	$sql = 'SELECT p.id_product as id, pl.`name`, not ISNULL(es.`id_ebay_sync_product_blacklist`) as blacklisted
			FROM `'._DB_PREFIX_.'product` p
			'.Shop::addSqlAssociation('product', 'p').'
			LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
				ON (p.`id_product` = pl.`id_product`
				AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').')
			LEFT JOIN `'._DB_PREFIX_.'ebay_sync_blacklist_product` es
				ON p.`id_product` = es.`id_product`
			WHERE product_shop.`id_shop` = 1
				AND p.`id_category_default` = '.(int)Tools::getValue('category');
	echo json_encode(Db::getInstance()->ExecuteS($sql));

}