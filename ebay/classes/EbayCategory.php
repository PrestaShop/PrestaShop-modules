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

class EbayCategory
{
	private $id_category; /* PrestaShop category id */
	private $id_category_ref; /* eBay Category id */
	private $is_multi_sku;

	private $percent;

	private $items_specific_values;
	private $conditions_values;

	public function __construct($id_category_ref, $id_category = null)
	{
		if ($id_category_ref)
			$this->id_category_ref = (int)$id_category_ref;
		if ($id_category)
			$this->id_category = (int)$id_category;
	}

	private function _loadFromDb()
	{
		$sql = 'SELECT ecc.`id_category`, ec.`id_category_ref`, ec.`is_multi_sku`, ecc.`percent` FROM `'._DB_PREFIX_.'ebay_category` ec
			LEFT JOIN `'._DB_PREFIX_.'ebay_category_configuration` ecc
			ON (ecc.`id_ebay_category` = ec.`id_ebay_category`)
			WHERE ';

		if ($this->id_category_ref)
			$sql .= 'ec.`id_category_ref` = '.(int)$this->id_category_ref;
		else
			$sql .= 'ecc.`id_category` = '.(int)$this->id_category;

		$res = Db::getInstance()->getRow($sql);

		foreach ($res as $attribute => $value)
			$this->$attribute = $value;
	}

	public function getIdCategoryRef()
	{
		if ($this->id_category_ref === null)
			$this->_loadFromDb();

		return $this->id_category_ref;
	}

	public function isMultiSku()
	{
		if ($this->is_multi_sku === null)
			$this->_loadFromDb();

		if ($this->is_multi_sku === null)
			$this->is_multi_sku = EbayCategory::getInheritedIsMultiSku((int)$this->id_category_ref);

		return $this->is_multi_sku;
	}

	public function getPercent()
	{
		if ($this->percent === null)
			$this->_loadFromDb();

		return $this->percent;
	}

	/*
	 * returns the percent, without the percent sign if there is one
	 *
	 */
	public function getCleanPercent()
	{
		return preg_replace('#%$#is', '', $this->percent);
	}

	/**
	 *
	 * Returns the items specifics with the PrestaShop attribute group / feature / of eBay specifics matching
	 *
	 */
	public function getItemsSpecifics()
	{
		$sql = 'SELECT e.`name`, e.`id_ebay_category_specific` as id, e.`required`, e.`selection_mode`, e.`id_attribute_group`, e.`id_feature`, e.`id_ebay_category_specific_value` as id_specific_value, e.`is_brand`, e.`can_variation`
			FROM `'._DB_PREFIX_.'ebay_category_specific` e
			WHERE e.`id_category_ref` = '.(int)$this->id_category_ref;

		return DB::getInstance()->executeS($sql);
	}

	/**
	 *
	 * Returns the items specifics with the full value for ebay_specific_attributes
	 *
	 */
	public function getItemsSpecificValues()
	{
		if (!$this->items_specific_values)
		{
			if (!$this->id_category_ref)
				$this->_loadFromDb();

			$sql = 'SELECT e.`name`, e.`can_variation`, e.`id_attribute_group`, e.`id_feature`, ec.`value` AS specific_value, e.`is_brand`
				FROM `'._DB_PREFIX_.'ebay_category_specific` e
				LEFT JOIN `'._DB_PREFIX_.'ebay_category_specific_value` ec
				ON e.`id_ebay_category_specific_value` = ec.`id_ebay_category_specific_value`
				WHERE e.`id_category_ref` = '.(int)$this->id_category_ref;

			$this->items_specific_values = Db::getInstance()->executeS($sql);
		}

		return $this->items_specific_values;
	}

	/**
	 *
	 * Returns the category conditions with the corresponding types on PrestaShop if set
	 *
	 */
	public function getConditionsWithConfiguration()
	{
		$sql = 'SELECT e.`id_condition_ref` AS id, e.`name`, ec.`condition_type` as type
			FROM `'._DB_PREFIX_.'ebay_category_condition` e
			LEFT JOIN `'._DB_PREFIX_.'ebay_category_condition_configuration` ec
			ON e.`id_category_ref` = ec.`id_category_ref`
			AND e.`id_condition_ref` = ec.`id_condition_ref`
			WHERE e.`id_category_ref` = '.(int)$this->id_category_ref;

		$res = Db::getInstance()->executeS($sql);

		$ret = array();

		foreach ($res as $row)
		{
			if (!isset($ret[$row['id']]))
			{
				$ret[$row['id']] = array(
					'name' => $row['name'],
					'types' => array($row['type'])
				);
			}
			else
				$ret[$row['id']]['types'][] = $row['type'];
		}

		return $ret;
	}

	/**
	 *
	 * Returns an array with the condition_type and corresponding ConditionID on eBay
	 *
	 */
	public function getConditionsValues()
	{
		if (!$this->conditions_values)
		{
			$sql = 'SELECT e.condition_type, e.id_condition_ref as condition_id
				FROM '._DB_PREFIX_.'ebay_category_condition_configuration e
				WHERE e.id_category_ref = '.(int)$this->id_category_ref;

			$res = Db::getInstance()->executeS($sql);

			$ret = array(
				'new' => null,
				'used' => null,
				'refurbished' => null
			);

			foreach ($res as $row)
				$ret[EbayCategoryConditionConfiguration::getPSConditions($row['condition_type'])] = $row['condition_id'];

			$this->conditions_values = $ret;
		}

		return $this->conditions_values;
	}

	public static function getEbayCategoryByCategoryId($id_category)
	{
		return Db::getInstance()->getRow('SELECT ec.`id_category_ref`, ec.`is_multi_sku`, ecc.`percent`
			FROM `'._DB_PREFIX_.'ebay_category` ec
			LEFT JOIN `'._DB_PREFIX_.'ebay_category_configuration` ecc
			ON (ecc.`id_ebay_category` = ec.`id_ebay_category`)
			WHERE ecc.`id_category` = '.(int)$id_category);
	}

	public static function insertCategories($categories, $categories_multi_sku)
	{
		$db = Db::getInstance();

		foreach ($categories as $category)
			$db->autoExecute(_DB_PREFIX_.'ebay_category', array(
				'id_category_ref' => pSQL($category['CategoryID']),
				'id_category_ref_parent' => pSQL($category['CategoryParentID']),
				'id_country' => '8',
				'level' => pSQL($category['CategoryLevel']),
				'is_multi_sku' => isset($categories_multi_sku[$category['CategoryID']]) ? $categories_multi_sku[$category['CategoryID']] : null,
				'name' => pSQL($category['CategoryName'])
			), 'INSERT', '', 0, true, true);
	}

	public static function updateCategoryTable($categories_multi_sku)
	{
		$db = Db::getInstance();
		$categories = $db->ExecuteS('SELECT * FROM '._DB_PREFIX_.'ebay_category');
		
		foreach ($categories as $category)
		{
			$db->autoExecute(_DB_PREFIX_.'ebay_category', array(
				'is_multi_sku' => isset($categories_multi_sku[$category['id_category_ref']]) ? $categories_multi_sku[$category['id_category_ref']] : null,
			), 'UPDATE', '`id_category_ref` = '.(int)$category['id_category_ref'], 0, true, true);
		}

		Configuration::updateValue('EBAY_CATEGORY_MULTI_SKU_UPDATE', 1, false, 0, 0);
	}

	/*
	 * Climbs up the categories hierarchy until finding the value inherited for is_multi_sku
	 *
	 */
	public static function getInheritedIsMultiSku($id_category_ref)
	{
		$row = Db::getInstance()->getRow('SELECT `id_category_ref_parent`, `is_multi_sku`
			FROM `'._DB_PREFIX_.'ebay_category`
			WHERE `id_category_ref` = '.(int)$id_category_ref);

		if ($row['is_multi_sku'] !== null)
			return $row['is_multi_sku'];

		if ((int)$row['id_category_ref_parent'] != (int)$id_category_ref)
			return EbayCategory::getInheritedIsMultiSku($row['id_category_ref_parent']);

		return $row['is_multi_sku']; // RArbuz: shall we not return the category default in this case?
	}

}