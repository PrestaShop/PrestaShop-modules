<?php
/**
* 2014 Affinity-Engine
*
* NOTICE OF LICENSE
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade AffinityItems to newer
* versions in the future.If you wish to customize AffinityItems for your
* needs please refer to http://www.affinity-engine.fr for more information.
*
*  @author    Affinity-Engine SARL <contact@affinity-engine.fr>
*  @copyright 2014 Affinity-Engine SARL
*  @license   http://www.gnu.org/licenses/gpl-2.0.txt GNU GPL Version 2 (GPLv2)
*  International Registered Trademark & Property of Affinity Engine SARL
*/

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ae_log` (
	`id_log` int(16) NOT NULL AUTO_INCREMENT,
	`date_add` DATETIME NOT NULL,
	`severity` VARCHAR(50) NOT NULL,
	`message` VARCHAR(200) NOT NULL,
	`id_shop` int(11) NOT NULL,
	PRIMARY KEY  (`id_log`)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ae_product_repository` (
	`id_product` int(16) NOT NULL,
	`id_shop` int(16) NOT NULL,
	`date_upd` DATETIME,
	PRIMARY KEY  (`id_product`, `id_shop`)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ae_category_repository` (
	`id_category` int(16) NOT NULL,
	`id_shop` int(16) NOT NULL,
	`date_upd` DATETIME,
	PRIMARY KEY  (`id_category`, `id_shop`)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ae_order_repository` (
	`id_order` int(16) NOT NULL AUTO_INCREMENT,
	`date_add` DATETIME,
	`date_upd` DATETIME,
	PRIMARY KEY  (`id_order`)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ae_cart_repository` (
	`id_cart` int(16) NOT NULL,
	`id_product` int(16) NOT NULL,
	`id_product_attribute` int(16) NOT NULL,
	`quantity` int(16) NOT NULL,
	`date_add` DATETIME,
	PRIMARY KEY  (`id_cart`, `id_product`, `id_product_attribute`)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ae_guest_action_repository` (
	`id_guest` VARCHAR(200) NOT NULL,
	`action` VARCHAR(1000) NOT NULL
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ae_notification` (
	`id_notification` VARCHAR(200) NOT NULL,
	`date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`nread` tinyint(1) NOT NULL,
	PRIMARY KEY(id_notification)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ae_notification_lang` (
	`language` VARCHAR(10) NOT NULL,
	`title` VARCHAR(200),
	`text` VARCHAR(2000),
	`id_notification` VARCHAR(200) NOT NULL,
	PRIMARY KEY(language, id_notification),
	FOREIGN KEY(id_notification) REFERENCES `'._DB_PREFIX_.'ae_notification`(id_notification)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ae_cart_ab_testing` (
	`id_cart` int(16) NOT NULL,
	`id_guest` VARCHAR(200) NOT NULL,
	`cgroup` VARCHAR(1) NOT NULL,
	`date_add` DATE NULL,
	`ip` VARCHAR(200) NULL,
	PRIMARY KEY(id_cart)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';