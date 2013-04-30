<?php

	// Init
	$sql = array();
		
	$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'fidbag_user` (
			  `id_user` int(10) NOT NULL AUTO_INCREMENT,
			  `id_customer` int(10) DEFAULT NULL,
			  `login` varchar(64) DEFAULT NULL,
			  `password` varchar(64) DEFAULT NULL,
			  `id_cart` int(10) DEFAULT NULL,
			  `card_number` varchar(64) DEFAULT NULL,
			  `payed` tinyint(1) unsigned NOT NULL DEFAULT "0",
			  PRIMARY KEY  (`id_user`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
?>
