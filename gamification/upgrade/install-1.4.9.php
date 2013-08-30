<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_4_9($object)
{
	return Db::getInstance()->execute(
			'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'tab_advice` (
			  `id_tab` int(11) NOT NULL,
			  `id_advice` int(11) NOT NULL,
			  PRIMARY KEY (`id_tab`, `id_advice`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;');
}