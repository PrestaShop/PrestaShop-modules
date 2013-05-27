<?php

class EbayCategory
{
	public static function getEbayCategoryByCategoryId($id_category)
	{
		return Db::getInstance()->getRow('SELECT ec.`id_category_ref`, ec.`is_multi_sku`, ecc.`percent` 
			FROM `'._DB_PREFIX_.'ebay_category` ec 
			LEFT JOIN `'._DB_PREFIX_.'ebay_category_configuration` ecc 
			ON (ecc.`id_ebay_category` = ec.`id_ebay_category`) 
			WHERE ecc.`id_category` = '.(int)$id_category);
	}
	
	public static function insertCategories($categories, $multi_sku_compliant_categories)
	{
		foreach ($categories as $category)
			EbayCategory::insert(array(
				'id_category_ref' 			 => pSQL($category['CategoryID']), 
				'id_category_ref_parent' => pSQL($category['CategoryParentID']), 
				'id_country' 						 => '8', 
				'level' 								 => pSQL($category['CategoryLevel']), 
				'is_multi_sku' 					 => in_array($category['CategoryID'], $multi_sku_compliant_categories) ? 1 : 0, 
				'name' 									 => pSQL($category['CategoryName'])
			));			
	}
	
	
	public static function insert($data)
	{
		return Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_category', $data, 'INSERT');
	}
}