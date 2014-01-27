<?php

/*
 * 2007-2014 PrestaShop
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
 *  @copyright  2007-2014 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

include(dirname(__FILE__).'/../../../config/config.inc.php');

if (!Tools::getValue('token') || Tools::getValue('token') != Configuration::get('EBAY_SECURITY_TOKEN'))
	die('ERROR: Invalid Token');

/* Fix for limit db sql request in time */
sleep(1);

$current_path = Db::getInstance()->getRow('
	SELECT ecc.`id_ebay_category`, ec.`id_category_ref`, ec.`id_category_ref_parent`, ec.`level`
	FROM `'._DB_PREFIX_.'ebay_category_configuration` ecc
	LEFT JOIN `'._DB_PREFIX_.'ebay_category` ec ON (ec.`id_ebay_category` = ecc.`id_ebay_category`)
	WHERE ecc.`id_category` = '.(int)Tools::getValue('id_category'));

for ($levelStart = $current_path['level']; $levelStart > 1; $levelStart--)
{
	$current_path = Db::getInstance()->getRow('
		SELECT ec.`id_ebay_category`, ec.`id_category_ref`, ec.`id_category_ref_parent`, ec.`level`
		FROM `'._DB_PREFIX_.'ebay_category` ec
		LEFT JOIN `'._DB_PREFIX_.'ebay_category_configuration` ecc ON (ecc.`id_ebay_category` = ec.`id_ebay_category`)
		WHERE ec.`id_category_ref` = '.(int)$current_path['id_category_ref_parent']);
}

$level_exists = array();

for ($level = 0; $level <= 5; $level++)
	if (Tools::getValue('level') >= $level)
	{
		if ($level == 0)
			$ebay_category_list_level = Db::getInstance()->ExecuteS('SELECT *
				FROM `'._DB_PREFIX_.'ebay_category`
				WHERE `level` = 1
				AND `id_category_ref` = `id_category_ref_parent`');
		else
			$ebay_category_list_level = Db::getInstance()->ExecuteS('SELECT *
				FROM `'._DB_PREFIX_.'ebay_category`
				WHERE `level` = '.(int)($level + 1).'
				AND `id_category_ref_parent` IN (
					SELECT `id_category_ref`
					FROM `'._DB_PREFIX_.'ebay_category`
					WHERE `id_ebay_category` = '.(int)(Tools::getValue('level'.$level)).')');

		if ($ebay_category_list_level)
		{
			$level_exists[$level + 1] = true;

			echo '<select name="category['.(int)Tools::getValue('id_category').']" id="categoryLevel'.(int)($level + 1).'-'.(int)Tools::getValue('id_category').'" rel="'.(int)Tools::getValue('id_category').'" style="font-size: 12px; width: 160px;" OnChange="changeCategoryMatch('.(int)($level + 1).', '.(int)Tools::getValue('id_category').');">
				<option value="0">'.Tools::safeOutput(Tools::getValue('ch_cat_str')).'</option>';

			foreach ($ebay_category_list_level as $ec)
				echo '<option value="'.(int)$ec['id_ebay_category'].'" '.((Tools::getValue('level'.($level + 1)) && Tools::getValue('level'.($level + 1)) == $ec['id_ebay_category']) ? 'selected="selected"' : '').'>'.Tools::safeOutput($ec['name']).($ec['is_multi_sku'] == 1 ? ' *' : '').'</option>';
			echo '</select> ';
		}
	}

if (!isset($level_exists[Tools::getValue('level') + 1]))
	echo '<input type="hidden" name="category['.(int)Tools::getValue('id_category').']" value="'.(int)Tools::getValue('level'.Tools::getValue('level')).'" />';
