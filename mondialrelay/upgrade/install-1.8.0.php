<?php

// File Example for upgrade

if (!defined('_PS_VERSION_'))
	exit;

// object module ($this) available
function upgrade_module_1_8_0($object)
{
	$upgrade_version = '1.8.0';

	$object->upgrade_detail[$upgrade_version] = array();

	// Add new table to handle multi-shop for a carrier
	$query = '
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mr_method_shop` (
		`id_mr_method_shop` int(10) unsigned NOT NULL auto_increment,
		`id_mr_method` int(10) unsigned NOT NULL,
		`id_shop` int(10) unsigned NOT NULL,
		PRIMARY KEY  (`id_mr_method_shop`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';

	if (!Db::getInstance()->execute($query))
		$object->upgrade_detail[$upgrade_version][] = $object->l('Can\'t create method shop table');

	// Refacto name
	$query = '
		ALTER TABLE  `'._DB_PREFIX_.'mr_method` CHANGE  `id_mr_method`  `id_mr_method` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
		CHANGE  `mr_Name`  `name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
		CHANGE  `mr_Pays_list`  `country_list` VARCHAR( 1000 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
		CHANGE  `mr_ModeCol`  `col_mode` VARCHAR( 3 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
		CHANGE  `mr_ModeLiv`  `dlv_mode` VARCHAR( 3 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
		CHANGE  `mr_ModeAss`  `insurance` VARCHAR( 3 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  \'0\',
		CHANGE  `id_carrier`  `id_carrier` INT( 10 ) NOT NULL';

	if (!Db::getInstance()->execute($query))
		$object->upgrade_detail[$upgrade_version][] = $object->l('Can\'t change name of the method table');

	$query = 'RENAME TABLE  `'._DB_PREFIX_.'mr_historique` TO  `'._DB_PREFIX_.'mr_history`';

	if (!Db::getInstance()->execute($query))
		$object->upgrade_detail[$upgrade_version][] = $object->l('Can\'t rename the history table');

	$object->account_shop['MR_ENSEIGNE_WEBSERVICE'] = Configuration::get('MR_ENSEIGNE_WEBSERVICE');
	$object->account_shop['MR_CODE_MARQUE'] = Configuration::get('MR_CODE_MARQUE');
	$object->account_shop['MR_KEY_WEBSERVICE'] = Configuration::get('MR_KEY_WEBSERVICE');
	$object->account_shop['MR_LANGUAGE'] = Configuration::get('MR_LANGUAGE');
	$object->account_shop['MR_WEIGHT_COEFFICIENT'] = Configuration::get('MR_WEIGHT_COEF');
	$object->account_shop['MR_ORDER_STATE'] = Configuration::get('MONDIAL_RELAY_ORDER_STATE');
	$object->updateAccountShop();

	Configuration::deleteByName('MONDIAL_RELAY_INSTALL_UPDATE');
	Configuration::deleteByName('MONDIAL_RELAY_ORDER_STATE');
	Configuration::deleteByName('MR_ENSEIGNE_WEBSERVICE');
	Configuration::deleteByName('MR_CODE_MARQUE');
	Configuration::deleteByName('MR_KEY_WEBSERVICE');
	Configuration::deleteByName('MR_WEIGHT_COEF');
	Configuration::deleteByName('MR_LANGUAGE');
	Configuration::deleteByName('MONDIAL_RELAY_1_4');
	Configuration::deleteByName('MONDIAL_RELAY_INSTALL_UPDATE_1');

	Configuration::updateValue('MONDIAL_RELAY', $upgrade_version);

	$methods = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'mr_method`');
	if (count($methods))
	{
		$query = '
			INSERT INTO `'._DB_PREFIX_.'mr_method_shop`
			(id_mr_method, id_shop) VALUES ';

		foreach ($methods as $method)
			$query .= '('.(int)$method['id_mr_method'].', '.(int)$object->account_shop['id_shop'].'),';
		$query = trim($query, ',');
		if (!Db::getInstance()->execute($query))
			$object->upgrade_detail[$upgrade_version][] = $object->l('Can\'t update table mr_method_shop');

	}

	if (!empty($object->installed_version))
	{
		if ($object->installed_version < '1.4')
			Db::getInstance()->execute('
				UPDATE `'._DB_PREFIX_.'carrier`
				SET
					`shipping_external` = 0,
					`need_range` = 1,
					`external_module_name` = "mondialrelay",
					`shipping_method` = 1
				WHERE `id_carrier`
				IN (SELECT `id_carrier`
						FROM `'._DB_PREFIX_.'mr_method`)');
	}

	// Try to register the new hook since 1.7
	if (!$object->isRegisteredInHook('newOrder'))
		$object->registerHook('newOrder');
	if (!$object->isRegisteredInHook('BackOfficeHeader'))
		$object->registerHook('BackOfficeHeader');

	if (!$object->isRegisteredInHook('header'))
		$object->registerHook('header');

	return (bool)count($object->upgrade_detail[$upgrade_version]);
}