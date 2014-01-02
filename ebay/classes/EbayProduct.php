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

class EbayProduct
{
	public static function getIdProductRefByIdProduct($id_product, $id_attribute = null)
	{
		$query = 'SELECT `id_product_ref`
			FROM `'._DB_PREFIX_.'ebay_product`
			WHERE `id_product` = '.(int)$id_product;
		
		if ($id_attribute)
			$query .= ' AND `id_attribute` = '.(int)$id_attribute;

		return Db::getInstance()->getValue($query);
	}


	public static function getProducts($not_update_for_days, $limit)
	{
		return Db::getInstance()->ExecuteS('SELECT ep.id_product_ref, ep.id_product
			FROM '._DB_PREFIX_.'ebay_product AS ep
			WHERE NOW() > DATE_ADD(ep.date_upd, INTERVAL '.(int)$not_update_for_days.' DAY)
			LIMIT '.$limit);
	}

	public static function insert($data)
	{
		return Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_product', $data, 'INSERT');
	}

	public static function updateByIdProductRef($id_product_ref, $data)
	{
		$to_insert = array();
		if(is_array($all_data) && count($all_data))
			foreach($all_data as $key => $data)
				$to_insert[pSQL($key)] = $data;

		return Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_product', $to_insert, 'UPDATE', '`id_product_ref` = '.pSQL($id_product_ref));
	}

	public static function deleteByIdProductRef($id_product_ref)
	{
		return Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ebay_product`
			WHERE `id_product_ref` = \''.pSQL($id_product_ref).'\'');
	}
}