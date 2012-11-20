<?php

if (!defined('_PS_VERSION_'))
	exit;

// object module ($this) available
function upgrade_module_1_8_3($object)
{
	$upgrade_version = '1.8.3';

	$object->upgrade_detail[$upgrade_version] = array();

	if (!Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'mr_method` ADD `is_deleted` INT NOT NULL'))
		$object->upgrade_detail[$upgrade_version][] = $object->l('Can\'t add new field in methodtable');

	Configuration::updateValue('MONDIAL_RELAY', $upgrade_version);
	return (bool)count($object->upgrade_detail[$upgrade_version]);
}