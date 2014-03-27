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

class EbayProductConfiguration
{

	public static function getByProductIds($product_ids)
	{
		foreach ($product_ids as &$product_id)
			$product_id = (int)$product_id;
		
		$res = Db::getInstance()->executeS('SELECT `id_product`, `blacklisted`, `extra_images`
			FROM `'._DB_PREFIX_.'ebay_product_configuration`
			WHERE `id_product` IN ('.implode(',', $product_ids).')');

		$ret = array();

		foreach ($res as $row)
			$ret[$row['id_product']] = $row;

		return $ret;
	}

	public static function getBlacklistedProductIds()
	{
		$res = Db::getInstance()->executeS(EbayProductConfiguration::getBlacklistedProductIdsQuery());

		return array_map(array('EbayProductConfiguration', 'getBlacklistedProductIdsMap'), $res);
	}

	public static function getBlacklistedProductIdsMap($row)
	{
		return $row['id_product'];
	}

	public static function getBlacklistedProductIdsQuery()
	{
		return 'SELECT `id_product`
			FROM `'._DB_PREFIX_.'ebay_product_configuration`
			WHERE `blacklisted` = 1';
	}

	public static function insertOrUpdate($product_id, $data)
	{
		if (!count($data))
			return;
		
		$to_insert = array();
		$fields_strs = array();
		foreach($data as $key => $value) {
			$to_insert[bqSQL($key)] = '"'.pSQL($value).'"';
			$fields_strs[] = '`'.bqSQL($key).'` = "'.pSQL($value).'"';
		}

		$sql = 'INSERT INTO `'._DB_PREFIX_.'ebay_product_configuration` (`id_product`, `'.implode('`,`', array_keys($to_insert)).'`)
			VALUES ('.(int)$product_id.', '.implode(',', $to_insert).')
			ON DUPLICATE KEY UPDATE ';

		$sql .= implode(',', $fields_strs);

		return Db::getInstance()->execute($sql);
	}

}