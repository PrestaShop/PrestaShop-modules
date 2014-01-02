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

// Init
$sql = array();

// Create Category Table in Database
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_category` (
		  `id_ebay_category` int(16) NOT NULL AUTO_INCREMENT,
		  `id_category_ref` int(16) NOT NULL,
		  `id_category_ref_parent` int(16) NOT NULL,
		  `id_country` int(16) NOT NULL,
		  `level` tinyint(1) NOT NULL,
		  `is_multi_sku` tinyint(1),
		  `name` varchar(255) NOT NULL,
		  UNIQUE(`id_category_ref`),
		  PRIMARY KEY  (`id_ebay_category`)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';


// Create Configuration Table in Database
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_category_configuration` (
		  `id_ebay_category_configuration` int(16) NOT NULL AUTO_INCREMENT,
		  `id_country` int(16) NOT NULL,
		  `id_ebay_category` int(16) NOT NULL,
		  `id_category` int(16) NOT NULL,
		  `percent` varchar(4) NOT NULL,
		  `sync` tinyint(1) NOT NULL,
		  `date_add` datetime NOT NULL,
		  `date_upd` datetime NOT NULL,
		  PRIMARY KEY  (`id_ebay_category_configuration`),
		  KEY `id_ebay_category` (`id_ebay_category`),
		  KEY `id_category` (`id_category`)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';


// Create Category Table in Database
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_product` (
		  `id_ebay_product` int(16) NOT NULL AUTO_INCREMENT,
		  `id_country` int(16) NOT NULL,
	 	  `id_product` int(16) NOT NULL,
	 	  `id_attribute` int(16) NOT NULL,
		  `id_product_ref` varchar(32) NOT NULL,
		  `date_add` datetime NOT NULL,
		  `date_upd` datetime NOT NULL,
		  UNIQUE(`id_product_ref`),
		  PRIMARY KEY  (`id_ebay_product`),
		  KEY `id_product` (`id_product`)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';


// Create Order Table in Database
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_order` (
		  `id_ebay_order` int(16) NOT NULL AUTO_INCREMENT,
		  `id_order_ref` varchar(128) NOT NULL,
		  `id_order` int(16) NOT NULL,
		  UNIQUE(`id_order_ref`),
		  PRIMARY KEY  (`id_ebay_order`)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';


// Create Sync History Table in Database
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_sync_history` (
		  `id_ebay_sync_history` int(16) NOT NULL AUTO_INCREMENT,
		  `is_manual` tinyint(1) NOT NULL,
		  `datetime` datetime NOT NULL,
		  PRIMARY KEY  (`id_ebay_sync_history`)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';


// Create Sync History Product Table in Database
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_sync_history_product` (
		  `id_ebay_sync_history_product` int(16) NOT NULL AUTO_INCREMENT,
		  `id_ebay_sync_history` int(16),
		  `id_product` int(16),
		  KEY (`id_ebay_sync_history`),
		  PRIMARY KEY  (`id_ebay_sync_history_product`)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';


// SHIPPING CARRIER
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_shipping` (
		  `id_ebay_shipping` int(11) NOT NULL AUTO_INCREMENT,
		  `ebay_carrier` varchar(256) NOT NULL,
		  `ps_carrier` int(11) NOT NULL,
		  `extra_fee` float(8,2) NOT NULL,
		  `international` int(4) NOT NULL, 
		  PRIMARY KEY (`id_ebay_shipping`)
	) ENGINE='._MYSQL_ENGINE_.'  DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_shipping_zone_excluded` (
			  `id_ebay_zone_excluded` int(11) NOT NULL AUTO_INCREMENT,
			  `region` varchar(255) NOT NULL,
			  `location` varchar(255) NOT NULL,
			  `description` varchar(255) NOT NULL,
			  `excluded` int(2) NOT NULL,
			  PRIMARY KEY (`id_ebay_zone_excluded`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_shipping_international_zone` (
		  `id_ebay_shipping` int(11) NOT NULL,
		  `id_ebay_zone` varchar(256) NOT NULL
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_shipping_location` (
		  `id_ebay_location` int(11) NOT NULL AUTO_INCREMENT,
		  `location` varchar(256) NOT NULL,
		  `description` varchar(256) NOT NULL,
		  PRIMARY KEY (`id_ebay_location`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_delivery_time_options` (
		  `id_delivery_time_option` int(11) NOT NULL AUTO_INCREMENT,
		  `DispatchTimeMax` varchar(256) NOT NULL,
		  `description` varchar(256) NOT NULL,
		  PRIMARY KEY (`id_delivery_time_option`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_shipping_service` (
		  `id_shipping_service` int(11) NOT NULL AUTO_INCREMENT,
		  `description` varchar(256) NOT NULL,
		  `shippingService` varchar(256) NOT NULL,
		  `shippingServiceID` varchar(256) NOT NULL,
		  `InternationalService` varchar(256) NOT NULL,
		  `ServiceType` varchar(256) NOT NULL,
		  PRIMARY KEY (`id_shipping_service`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_returns_policy` (
		  `id_return_policy` int(11) NOT NULL AUTO_INCREMENT,
		  `value` varchar(256) NOT NULL,
		  `description` varchar(256) NOT NULL,
		  PRIMARY KEY (`id_return_policy`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

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
		
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_product_configuration` (
			  `id_ebay_product_configuration` int(11) NOT NULL AUTO_INCREMENT,
				`id_product` int(16),
				`blacklisted` tinyint(1) NOT NULL,
				`extra_images` int(4) NOT NULL,
				UNIQUE(`id_product`),				
			  PRIMARY KEY (`id_ebay_product_configuration`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8';
				
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_product_image` (
			  `id_ebay_product_image` int(11) NOT NULL AUTO_INCREMENT,
				`ps_image_url` varchar(255),
				`ebay_image_url` varchar(255),
				UNIQUE(`ps_image_url`),				
			  PRIMARY KEY (`id_ebay_product_image`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8';