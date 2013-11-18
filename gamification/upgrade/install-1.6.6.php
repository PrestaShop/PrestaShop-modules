<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_6_6($object)
{
	return Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'advice` ADD `start_date` INT NULL DEFAULT 0 , ADD `stop_date` INT NULL DEFAULT 0');
}