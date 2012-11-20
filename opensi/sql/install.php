<?php
/*
* OpenSi Connect for Prestashop
*
* NOTICE OF LICENSE
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; either version 2 of the License, or (at your
* option) any later version.
* This program is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
* or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
* for more details.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author Speedinfo SARL
* @copyright 2003-2012 Speedinfo SARL
* @contact contact@speedinfo.fr
* @url http://www.speedinfo.fr
*
*/

/* Init */
$sql = array();

/* Create databases */
$sql[] = '
	CREATE TABLE `'._DB_PREFIX_.'opensi_order` (
	`id_osi_order` INT(11) NOT NULL AUTO_INCREMENT,
	`id_order` INT( 10 ) NOT NULL,
	`date_order_synchro` DATETIME NOT NULL,
	`transaction` TINYINT( 1 ) NOT NULL,
	`date_transaction` DATETIME NOT NULL,
	`paid` TINYINT( 1 ) NOT NULL,
	`date_paid` DATETIME NOT NULL,
	PRIMARY KEY (`id_osi_order`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
';

$sql[] = '
	CREATE TABLE `'._DB_PREFIX_.'opensi_invoice` (
	 `id_invoice` int(11) NOT NULL AUTO_INCREMENT,
	 `id_order` int(10) NOT NULL,
	 `number_invoice` varchar(20) NOT NULL,
	 `type` varchar(1) NOT NULL,
	 `url_key` TEXT NOT NULL,
	 `date_synchro` datetime NOT NULL,
	 PRIMARY KEY (`id_invoice`),
	 UNIQUE KEY `id_order` (`id_order`,`number_invoice`,`type`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
';