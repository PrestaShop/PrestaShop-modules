<?php

// Init
$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'sm_kiala_order` (
		  `id_sm_kiala_order` int(10) NOT NULL AUTO_INCREMENT,
		  `id_customer` int(10) NOT NULL,
		  `id_cart` int(10) NOT NULL,
		  `id_order` int(10) NOT NULL,
		  `id_country_pickup` int(10) NOT NULL,
		  `id_country_delivery` int(10) NOT NULL,
		  `point_short_id` varchar(10) NOT NULL,
  		  `point_alias` varchar(32) NOT NULL,
		  `point_name` varchar(32) NOT NULL,
  		  `point_street` varchar(128) NOT NULL,
		  `point_location_hint` varchar(128) DEFAULT NULL,
		  `point_zip` varchar(12) DEFAULT NULL,
  		  `point_city` varchar(64) NOT NULL,
		  `commercialValue` float unsigned NOT NULL,
		  `parcelDescription` varchar(70) NOT NULL,
		  `parcelVolume` float unsigned NOT NULL,
		  `date_upd` datetime NOT NULL,
		  `tracking_number` varchar(32) NOT NULL,
		  PRIMARY KEY (`id_sm_kiala_order`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'sm_kiala_country` (
		  `id_sm_kiala_country` int(10) NOT NULL AUTO_INCREMENT,
		  `id_country` int(10) NOT NULL,
		  `dspid` varchar(12) NOT NULL,
		  `sender_id` varchar(32) NOT NULL,
		  `password` varchar(32) NOT NULL,
		  `preparation_delay` int(2) NOT NULL,
		  `active` tinyint(1) NOT NULL,
		  `pickup_country` tinyint(1) NOT NULL,
		  PRIMARY KEY (`id_sm_kiala_country`),
		  UNIQUE KEY `id_country` (`id_country`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';