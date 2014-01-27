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

