<?php

	// Init
	$sql = array();
		
	$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'buyster_operation` (
			  `id_cart` int(10) NOT NULL,
			  `operation_name` varchar(20) DEFAULT NULL,
			  `status` varchar(20) DEFAULT NULL,
			  `reference` varchar(50),
			  `token` text,
			  PRIMARY KEY  (`id_cart`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
?>
