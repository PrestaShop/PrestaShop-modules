<?php

/*
 * 2007-2013 PrestaShop
 *
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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2013 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

$config_path = dirname(__FILE__).'/../../../config/config.inc.php';
if (file_exists($config_path))
{
	include($config_path);
	include(dirname(__FILE__).'/../classes/EbayCategorySpecific.php');
//	if (!Tools::getValue('token') || Tools::getValue('token') != Configuration::get('EBAY_SECURITY_TOKEN'))
//		die('ERROR : INVALID TOKEN');

	/* Fix for limit db sql request in time */
	sleep(1);
	
	$ebay_category = (int)Tools::getValue('ebay_category');
		
	// TODO: check is needs to use the API to update
	EbayCategorySpecific::loadCategorySpecifics($ebay_category);
	
	$db = DB::getInstance();

	$sql = 'SELECT DISTINCT(e.`name`), e.`required`, e.`selection_mode`, a.`name` as attribute_name, f.`name` as feature_name, ec.`value` as specific_value, e.`id_ebay_category_specific` as id
		FROM `'._DB_PREFIX_.'ebay_category_specific` e
		LEFT JOIN `'._DB_PREFIX_.'attribute_lang` a
		ON e.`id_attribute` = a.`id_attribute`
		AND a.`id_lang` = '.(int)Tools::getValue('id_lang').'
		LEFT JOIN `'._DB_PREFIX_.'feature_lang` f
		ON e.`id_feature` = f.`id_feature`
		AND f.`id_lang` = '.(int)Tools::getValue('id_lang').'
		LEFT JOIN `'._DB_PREFIX_.'ebay_category_specific_value` ec
		ON e.`id_ebay_category_specific_value` = ec.`id_ebay_category_specific_value`
		WHERE e.`id_category_ref` = '.$ebay_category;
	$item_specifics = $db->executeS($sql);
	
	$item_specifics_ids = array_map(function($row) {return $row['id'];}, $item_specifics);
	
	if (count($item_specifics_ids))
	{
		$sql = 'SELECT `id_ebay_category_specific_value` as id, `id_ebay_category_specific` as specific_id, `value`
			FROM `'._DB_PREFIX_.'ebay_category_specific_value`
			WHERE `id_ebay_category_specific` in ('.implode(',', $item_specifics_ids) .')';
		$item_specifics_values = $db->executeS($sql);		
	} else
		$item_specifics_values = array();
	
	foreach($item_specifics as &$item_specific)
		foreach ($item_specifics_values as $value)
			if ($item_specific['id'] == $value['specific_id'])
				$item_specific['values'][] = $value;
	
	echo json_encode($item_specifics);
}
else
	echo 'ERROR';

