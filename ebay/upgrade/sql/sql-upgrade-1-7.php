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

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_profile` (
		  `id_ebay_profile` int(16) NOT NULL AUTO_INCREMENT,
		  `id_lang` int(10) NOT NULL,
		  `id_shop` int(11) NOT NULL,
		  `ebay_user_identifier` varchar(255) NOT NULL,
			`ebay_site_id` int(10) NOT NULL,
			`id_ebay_returns_policy_configuration` int(10) unsigned NOT NULL,
		  PRIMARY KEY  (`id_ebay_profile`)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_configuration` (
	  `id_configuration` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `id_ebay_profile` int(11) unsigned DEFAULT NULL,
	  `name` varchar(32) NOT NULL,
	  `value` text,
	  PRIMARY KEY (`id_configuration`),
		UNIQUE(`id_ebay_profile`, `name`),
	  KEY `name` (`name`)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_returns_policy_configuration` (
	  `id_ebay_returns_policy_configuration` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`ebay_returns_within` varchar(255) NOT NULL,
		`ebay_returns_who_pays` varchar(255) NOT NULL,
		`ebay_returns_description` text NOT NULL,
		`ebay_returns_accepted_option` varchar(255) NOT NULL,
	  PRIMARY KEY (`id_ebay_returns_policy_configuration`)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
	
$sql[] = 'ALTER TABLE `'._DB_PREFIX_.'ebay_category_configuration` 
	ADD `id_ebay_profile` INT( 16 ) NOT NULL AFTER `id_ebay_category_configuration`';
// TODO: that would be better to remove the previous indexes if possible
$sql[] = 'ALTER TABLE `'._DB_INDEX_.'ebay_category_configuration` ADD INDEX `ebay_category` (`id_ebay_profile` ,  `id_ebay_category`)';
$sql[] = 'ALTER TABLE `'._DB_INDEX_.'ebay_category_configuration` ADD INDEX `category` (`id_ebay_profile` ,  `id_category`)';

$sql[] = 'ALTER TABLE `'._DB_PREFIX_.'ebay_shipping_zone_excluded` 
	ADD `id_ebay_profile` INT( 16 ) NOT NULL AFTER `id_ebay_zone_excluded`';

$sql[] = 'ALTER TABLE `'._DB_PREFIX_.'ebay_shipping_international_zone` 
	ADD `id_ebay_profile` INT( 16 ) NOT NULL AFTER `id_ebay_zone`';

$sql[] = 'ALTER TABLE `'._DB_PREFIX_.'ebay_category_condition` 
	ADD `id_ebay_profile` INT( 16 ) NOT NULL AFTER `id_ebay_category_condition`';

$sql[] = 'ALTER TABLE `'._DB_PREFIX_.'ebay_category_condition_configuration` 
	ADD `id_ebay_profile` INT( 16 ) NOT NULL AFTER `id_ebay_category_condition_configuration`';

$sql[] = 'ALTER TABLE `'._DB_PREFIX_.'ebay_product` 
	ADD `id_ebay_profile` INT( 16 ) NOT NULL AFTER `id_ebay_product`';

$sql[] = 'ALTER TABLE `'._DB_PREFIX_.'ebay_shipping`
	ADD `id_ebay_profile` INT( 16 ) NOT NULL AFTER `id_ebay_shipping`';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_order_order` (
	`id_ebay_order_order` int(16) unsigned NOT NULL AUTO_INCREMENT,
	`id_ebay_order` int(16) NOT NULL,
	`id_order` int(16) NOT NULL,
	`id_shop` int(16) NOT NULL,
	PRIMARY KEY  (`id_ebay_order_order`),
    UNIQUE KEY  (`id_order`, `id_shop`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_product_modified` (
	`id_ebay_product_modified` int(16) unsigned NOT NULL AUTO_INCREMENT,
	`id_ebay_profile` int(16) NOT NULL,
    `id_product` int(16) NOT NULL
	PRIMARY KEY  (`id_ebay_product_modified`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_log` (
	`id_ebay_log` int(16) NOT NULL,
	`text` text NOT NULL,
	`type` varchar(40) NOT NULL,
    `date_add` datetime NOT NULL
	PRIMARY KEY  (`id_ebay_log`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_stat` (
	`id_ebay_stat` int(16) NOT NULL AUTO_INCREMENT,
	`id_ebay_profile` int(16) NOT NULL,
	`version` varchar(10) NOT NULL,
    `data` text,
    `date_add` datetime NOT NULL,
    `tries` TINYINT unsigned NOT NULL
	PRIMARY KEY  (`id_ebay_stat`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';