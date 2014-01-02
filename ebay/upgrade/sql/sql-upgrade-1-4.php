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
$sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'ebay_category_configuration` CHANGE `percent` `percent` VARCHAR(4) NOT NULL';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ebay_shipping` (
			`id_ebay_shipping` int(11) NOT NULL AUTO_INCREMENT,
			`ebay_carrier` varchar(256) NOT NULL,
			`ps_carrier` int(11) NOT NULL,
			`extra_fee` int(11) NOT NULL,
			`international` int(4) NOT NULL,
			PRIMARY KEY (`id_ebay_shipping`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ebay_shipping_zone_excluded` (
			`id_ebay_zone_excluded` int(11) NOT NULL AUTO_INCREMENT,
			`region` varchar(255) NOT NULL,
			`location` varchar(255) NOT NULL,
			`description` varchar(255) NOT NULL,
			`excluded` int(2) NOT NULL,
			PRIMARY KEY (`id_ebay_zone_excluded`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ebay_shipping_international_zone` (
			`id_ebay_shipping` int(11) NOT NULL,
			`id_ebay_zone` varchar(256) NOT NULL
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ebay_shipping_location` (
			`id_ebay_location` int(11) NOT NULL AUTO_INCREMENT,
			`location` varchar(256) NOT NULL,
			`description` varchar(256) NOT NULL,
			PRIMARY KEY  (`id_ebay_location`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ebay_delivery_time_options` (
			`id_delivery_time_option` int(11) NOT NULL AUTO_INCREMENT,
			`DispatchTimeMax` varchar(256) NOT NULL,
			`description` varchar(256) NOT NULL,
			PRIMARY KEY (`id_delivery_time_option`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ebay_shipping_service` (
			`id_shipping_service` int(11) NOT NULL AUTO_INCREMENT,
			`description` varchar(256) NOT NULL,
			`shippingService` varchar(256) NOT NULL,
			`shippingServiceID` varchar(256) NOT NULL,
			`InternationalService` varchar(256) NOT NULL,
			`ServiceType` varchar(256) NOT NULL,
			PRIMARY KEY (`id_shipping_service`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ebay_returns_policy` (
			`id_return_policy` int(11) NOT NULL AUTO_INCREMENT,
			`value` varchar(256) NOT NULL,
			`description` varchar(256) NOT NULL,
			PRIMARY KEY (`id_return_policy`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';


