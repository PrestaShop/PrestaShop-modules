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

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_category_specific` (
			`id_ebay_category_specific` int(11) NOT NULL AUTO_INCREMENT,
			`id_category_ref` int(16) NOT NULL,
			`name` varchar(40) NOT NULL,
			`required` tinyint(1) NOT NULL,
			`can_variation` tinyint(1) NOT NULL,
			`selection_mode` tinyint(1) NOT NULL,
			`id_attribute_group` int(16) NULL,
			`id_feature` int(16) NULL,
			`id_ebay_category_specific_value` int(16) NULL,
			`is_brand` tinyint(1) NULL,
			UNIQUE(`id_category_ref`, `name`),
			PRIMARY KEY (`id_ebay_category_specific`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_category_specific_value` (
			`id_ebay_category_specific_value` int(11) NOT NULL AUTO_INCREMENT,
			`id_ebay_category_specific` int(11) NOT NULL,
			`value` varchar(50) NOT NULL,
			UNIQUE(`id_ebay_category_specific`, `value`),
			PRIMARY KEY (`id_ebay_category_specific_value`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_category_condition` (
			`id_ebay_category_condition` int(11) NOT NULL AUTO_INCREMENT,
			`id_category_ref` int(11) NOT NULL,
			`id_condition_ref` int(11) NOT NULL,
			`name` varchar(256) NOT NULL,
			PRIMARY KEY (`id_ebay_category_condition`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_category_condition_configuration` (
			`id_ebay_category_condition_configuration` int(11) NOT NULL AUTO_INCREMENT,
			`id_category_ref` int(11) NOT NULL,
			`condition_type` int(11) NOT NULL,
			`id_condition_ref` int(11) NOT NULL,
			UNIQUE(`id_category_ref`, `condition_type`),
			PRIMARY KEY (`id_ebay_category_condition_configuration`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ebay_product_configuration` (
			`id_ebay_product_configuration` int(11) NOT NULL AUTO_INCREMENT,
			`id_product` int(16),
			`blacklisted` tinyint(1) NOT NULL,
			`extra_images` int(4) NOT NULL,
			UNIQUE(`id_product`),
			PRIMARY KEY (`id_ebay_product_configuration`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8';

$sql[] = 'ALTER TABLE '._DB_PREFIX_.'ebay_category MODIFY `is_multi_sku` tinyint(1)';

$sql[] = 'ALTER TABLE '._DB_PREFIX_.'ebay_shipping MODIFY `extra_fee` float(8,2)';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_product_image` (
			`id_ebay_product_image` int(11) NOT NULL AUTO_INCREMENT,
			`ps_image_url` varchar(255),
			`ebay_image_url` varchar(255),
			UNIQUE(`ps_image_url`),
			PRIMARY KEY (`id_ebay_product_image`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8';