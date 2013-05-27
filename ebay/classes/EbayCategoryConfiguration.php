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