<?php

class EbayCategoryConfiguration
{
	
	/**
	* Returns the query to retrieve the PrestaShop categories ready to be synchornized
	* Will only retrieve categories that have an Ebay equivalent
	* Depends on the sync mode
	*
	* @param array $params hook parameters
	*/		
	public static function getCategoriesQuery($sync_mode)
	{
		$sql = 'SELECT `id_category` 
				FROM `'._DB_PREFIX_.'ebay_category_configuration` 
				WHERE `id_category` > 0 
				AND `id_ebay_category` > 0';

		if ($sync_mode == 'B') 
			$sql .= ' AND `sync` = 1';

		return $sql;			
	}
	
	/**
	 *
	 * Returns the eBay category ids
	 *
	 */
	
	public static function getEbayCategoryIds()
	{
		$sql = 'SELECT 
			DISTINCT(ec.`id_category_ref`) as id
			FROM `'._DB_PREFIX_.'ebay_category_configuration` e
			LEFT JOIN `'._DB_PREFIX_.'ebay_category` ec
			ON e.`id_ebay_category` = ec.`id_ebay_category`';	
		$res = Db::getInstance()->executeS($sql);
		return array_map(function($row) {return $row['id'];}, $res);
	}
	
	
	/**
	 *
	 * Returns the eBay category id and the full name including the name of the parent and the grandparent category
	 *
	 */
	
	public static function getEbayCategories()
	{
		$sql = 'SELECT 
			DISTINCT(ec1.`id_category_ref`) as id, 
			CONCAT(
				IFNULL(ec3.`name`, \'\'), 
				IF (ec3.`name` is not null, \' > \', \'\'),
				IFNULL(ec2.`name`, \'\'), 
				IF (ec2.`name` is not null, \' > \', \'\'),
				ec1.`name`
			) as name 
			FROM `'._DB_PREFIX_.'ebay_category_configuration` e
			LEFT JOIN `'._DB_PREFIX_.'ebay_category` ec1
			ON e.`id_ebay_category` = ec1.`id_ebay_category`
			LEFT JOIN `'._DB_PREFIX_.'ebay_category` ec2
			ON ec1.`id_category_ref_parent` = ec2.`id_category_ref`
			AND ec1.`id_category_ref_parent` <> \'1\'
			and ec1.level <> 1
			LEFT JOIN `'._DB_PREFIX_.'ebay_category` ec3
			ON ec2.`id_category_ref_parent` = ec3.`id_category_ref`
			AND ec2.`id_category_ref_parent` <> \'1\'
			and ec2.level <> 1
			WHERE ec1.`id_category_ref` is not null';
		return Db::getInstance()->executeS($sql);
	}
	
	
	public static function add($data)
	{
		Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_category_configuration', $data, 'INSERT');
	}

	public static function updateAll($data)
	{
		Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_category_configuration', $data, 'UPDATE');	
	}
	
	public static function updateByIdCategory($id_category, $data)
	{
		Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_category_configuration', $data, 'UPDATE', '`id_category` = '.(int)$id_category);	
	}
	
	public static function deleteByIdCategory($id_category)
	{
		Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ebay_category_configuration` 
			WHERE `id_category` = '.(int)$id_category);		
	}
	
	public static function getEbayCategoryConfigurations()
	{
		return Db::getInstance()->executeS('SELECT * 
			FROM `'._DB_PREFIX_.'ebay_category_configuration`');		
	}
	
	public static function getTotalCategoryConfigurations()
	{
		return Db::getInstance()->getValue('SELECT COUNT(`id_ebay_category_configuration`) 
					FROM `'._DB_PREFIX_.'ebay_category_configuration`');
	}
	
	public static function getIdByCategoryId($id_category)
	{
		return Db::getInstance()->getValue('SELECT `id_ebay_category_configuration` 
						FROM `'._DB_PREFIX_.'ebay_category_configuration` 
						WHERE `id_category` = '.(int)$id_category);
	}	
}