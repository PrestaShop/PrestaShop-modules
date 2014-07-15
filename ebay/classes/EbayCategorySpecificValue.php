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

class EbayCategorySpecificValue
{
	public static function insertIgnore($data)
	{
		if (!$data)
			return false;

		$db = Db::getInstance();

		if (version_compare(_PS_VERSION_, '1.5', '>'))
			$db->insert('ebay_category_specific_value', $data, false, true, Db::INSERT_IGNORE);
		else
		{
			// Check if $data is a list of row
			$current = current($data);

			if (!is_array($current) || isset($current['type']))
				$data = array($data);

			$keys = array_keys($data[0]);
			$sql = 'INSERT IGNORE INTO `'._DB_PREFIX_.'ebay_category_specific_value`
				(`'.implode('`,`', $keys).'`) VALUES ';
			$rows = array();

			foreach ($data as $values)
				$rows[] = '(\''.implode('\',\'', $values).'\')';
			
			$sql .= implode(' , ', $rows);
			$db->execute($sql);
		}
	}

}