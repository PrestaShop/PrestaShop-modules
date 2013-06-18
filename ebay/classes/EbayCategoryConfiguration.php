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
	 * Returns the product ids of all product for which the category is matched with an eBay category
	 *
	 */
	public static function getAllProductIds()
	{
		$res = Db::getInstance()->executeS('SELECT `id_product` 
			FROM `'._DB_PREFIX_.'category_product` c
			WHERE c.`id_category` 
			IN (
				SELECT e.`id_category`
				FROM `'._DB_PREFIX_.'ebay_category_configuration` e
			)');
		return array_map(function($row) { return $row['id_product']; }, $res);					
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