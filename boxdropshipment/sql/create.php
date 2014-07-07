<?php
	/**
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
	 * @author    boxdrop Group AG
	 * @copyright boxdrop Group AG
	 * @license   http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
	 * International Registered Trademark & Property of boxdrop Group AG
	 */

	if (!defined('_PS_VERSION_'))
		exit;

	$statements = array();
	$statements[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.BoxdropOrder::$definition['table'].'` (`boxdrop_order_id` bigint(20) unsigned NOT NULL 
	AUTO_INCREMENT, `id_cart` int(10) unsigned NOT NULL, `boxdrop_shop_id` bigint(20) unsigned NOT NULL, `id_customer` int(10) unsigned NOT NULL, 
	`id_order` int(10) unsigned NOT NULL, `created_at` DATETIME NOT NULL, PRIMARY KEY (`boxdrop_order_id`), KEY `id_cart` (`id_cart`,`id_order`) 
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;';
	$statements[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.BoxdropOrderShipment::$definition['table'].'` ( `boxdrop_order_shipment_id` bigint(20) 
	unsigned NOT NULL AUTO_INCREMENT, `boxdrop_order_id` bigint(20) unsigned NOT NULL, `id_order` int(10) unsigned NOT NULL, `id_order_carrier` 
	int(10) unsigned NOT NULL, `shipment_mode` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT \'\', `airwaybill` varchar(100) COLLATE utf8_bin NOT 
	NULL DEFAULT \'\', `pickup_date` date NOT NULL, `label` varchar(250) COLLATE utf8_bin NOT NULL,  `shipping_weight` decimal(6,2) unsigned NOT 
	NULL, `parcel_count` int(10) unsigned NOT NULL, `current_status` int(10) unsigned NOT NULL, `created_at` datetime NOT NULL, `created_by` int(10) 
	unsigned NOT NULL,  PRIMARY KEY (`boxdrop_order_shipment_id`), KEY `boxdrop_order_id` (`boxdrop_order_id`,`id_order`)) ENGINE=InnoDB DEFAULT 
	CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;';
	$statements[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.BoxdropOrderShipmentParcel::$definition['table'].'`(`boxdrop_order_shipment_parcel_id` 
	bigint(20) unsigned NOT NULL AUTO_INCREMENT, `boxdrop_order_shipment_id` bigint(20) unsigned NOT NULL, `depth` int(10) unsigned NOT NULL 
	DEFAULT \'0\', `length` int(10) unsigned NOT NULL DEFAULT \'0\', `width` int(10) unsigned NOT NULL DEFAULT \'0\', `volumetric_weight` float(8,3) 
	unsigned NOT NULL DEFAULT \'0.000\', `weight` float(8,3) unsigned NOT NULL DEFAULT \'0.000\', `shipment_weight` float(8,3) unsigned NOT NULL 
	DEFAULT \'0.000\', `content` longtext COLLATE utf8_bin NOT NULL,`created_at` datetime NOT NULL,PRIMARY KEY (`boxdrop_order_shipment_parcel_id`), 
	KEY `boxdrop_order_shipment_id` (`boxdrop_order_shipment_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;';
	$statements[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'boxdrop_order_shipment_parcel_has_order_detail` (`boxdrop_order_shipment_parcel_id` 
	bigint(20) unsigned NOT NULL, `order_detail_id` int(10) unsigned NOT NULL, KEY `boxdrop_order_shipment_parcel_id` 
	(`boxdrop_order_shipment_parcel_id`,`order_detail_id`), KEY `order_detail_id` (`order_detail_id`,`boxdrop_order_shipment_parcel_id`) 
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;';
