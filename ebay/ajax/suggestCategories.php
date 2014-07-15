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
 *  @license	http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

include_once (dirname(__FILE__).'/../../../config/config.inc.php');
include_once dirname(__FILE__).'/../ebay.php';

$ebay = new Ebay();

if (!Configuration::get('EBAY_SECURITY_TOKEN') || Tools::getValue('token') != Configuration::get('EBAY_SECURITY_TOKEN'))
{
	echo Tools::safeOutput(Tools::getValue('not_logged_str'));
	return;
}

$ebay_request = new EbayRequest();

/* Loading categories */
$category_config_list = array();
$category_config_list_tmp = Db::getInstance()->executeS('SELECT *
	FROM `'._DB_PREFIX_.'ebay_category_configuration`');

foreach ($category_config_list_tmp as $category)
	$category_config_list[$category['id_category']] = $category;

/* Get categories */
$category_list = Db::getInstance()->executeS('SELECT `id_category`, `name`
	FROM `'._DB_PREFIX_.'category_lang`
	WHERE `id_lang` = '.(int)Tools::getValue('id_lang').' '.(_PS_VERSION_ >= '1.5' ? $ebay->getContext()->shop->addSqlRestrictionOnLang() : ''));

/* GET One Product by category */
$sql = 'SELECT pl.`name`, pl.`description`, p.`id_category_default`
	FROM `'._DB_PREFIX_.'product` p
	LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
	ON (pl.`id_product` = p.`id_product`
	AND pl.`id_lang` = '.(int)Tools::getValue('id_lang').'
	'.(_PS_VERSION_ >= '1.5' ? $ebay->getContext()->shop->addSqlRestrictionOnLang('pl') : '').')
	GROUP BY p.`id_category_default`';

$products = Db::getInstance()->executeS($sql);

/* Create array */
$product_test = array();

foreach ($products as $product)
	$product_test[$product['id_category_default']] = array(
		'description' => $product['description'],
		'name' => $product['name']);

/* cats ref */
$ref_cats = Db::getInstance()->executeS('SELECT `id_ebay_category`, `id_category_ref`
	FROM `'._DB_PREFIX_.'ebay_category` ');

if (!is_array($ref_cats) || !count($ref_cats))
	return;

foreach ($ref_cats as $cat)
	$ref_categories[$cat['id_category_ref']] = $cat['id_ebay_category'];

$i = 0;
$sql = 'REPLACE INTO `'._DB_PREFIX_.'ebay_category_configuration` (`id_country`, `id_ebay_category`, `id_category`, `percent`, `date_add`, `date_upd`) VALUES ';

if (is_array($category_list) && count($category_list))
{
	/* while categoryList */
	foreach ($category_list as $category)
		if (!isset($category_config_list[$category['id_category']]) || !$category_config_list[$category['id_category']]['id_ebay_category'])
		{
			if (isset($product_test[$category['id_category']]) && !empty($product_test[$category['id_category']]))
			{
				echo $category['id_category'].'$';
				$id_category_ref_suggested = $ebay_request->getSuggestedCategory($category['name'].' '.$product_test[$category['id_category']]['name']);
				$id_ebay_category_suggested = isset($ref_categories[$id_category_ref_suggested]) ? $ref_categories[$id_category_ref_suggested] : 1;

				if ((int)$id_ebay_category_suggested > 0)
				{
					if ($i)
						$sql .= ', ';
					$sql .= '(8, '.(int)$id_ebay_category_suggested.', '.(int)$category['id_category'].', 0, NOW(), NOW()) ';
					$i++;
				}
			}
		}
		
	if ($i)
		Db::getInstance()->execute($sql);

	echo $ebay->l('Settings updated');
}
