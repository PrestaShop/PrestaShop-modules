<?php

class EbayCategory
{
	// the eBay category id
	private $category_id;

	public function __construct($category_id)
	{
		$this->category_id = (int)$category_id;
	}
	
	/**
	 *
	 * Returns the items specifics with the PrestaShop attribute group / feature / of eBay specifics matching
	 *
	 */
	public function getItemsSpecifics()
	{
		$sql = 'SELECT e.`name`, e.`id_ebay_category_specific` as id, e.`required`, e.`selection_mode`, e.`id_attribute_group`, e.`id_feature`, e.`id_ebay_category_specific_value` as id_specific_value
			FROM `'._DB_PREFIX_.'ebay_category_specific` e
			WHERE e.`id_category_ref` = '.$this->category_id;		
		return DB::getInstance()->executeS($sql);
	}
	
	/**
	 *
	 * Returns the items specifics with the full value for ebay_specific_attributes
	 *
	 */
	public function getItemsSpecificValues()
	{
		$sql = 'SELECT e.`name`, e.`can_variation`, e.`id_attribute_group`, e.`id_feature`, ec.`value` AS specific_value
			FROM `'._DB_PREFIX_.'ebay_category_specific` e
			LEFT JOIN `'._DB_PREFIX_.'ebay_category_specific_value` ec
			ON e.`id_ebay_category_specific_value` = ec.`id_ebay_category_specific_value`		
			WHERE e.`id_category_ref` = '.$this->category_id;		
		return DB::getInstance()->executeS($sql);		
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
			WHERE e.`id_category_ref` = '.$this->category_id;
		$res = Db::getInstance()->executeS($sql);
		
		$ret = array();
		foreach ($res as $row)
		{
			if (!isset($ret[$row['id']]))
			{
				$ret[$row['id']] = array(
					'name'  => $row['name'],
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
		$sql = 'SELECT e.condition_type, e.id_condition_ref as condition_id
			FROM '._DB_PREFIX_.'ebay_category_condition_configuration e
			WHERE e.id_category_ref = '.$this->category_id;
		$res = Db::getInstance()->executeS($sql);
		
		$ret = array(
			'new' 				=> null,
			'used' 				=> null,
			'refurbished' => null
		);
		
		foreach($res as $row)
			$ret[EbayCategoryConditionConfiguration::getPSConditions($row['condition_type'])] = $row['condition_id'];
		
		return $ret;
	}	
	
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