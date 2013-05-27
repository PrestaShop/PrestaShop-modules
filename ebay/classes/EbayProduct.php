<?php

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
			WHERE NOW() > DATE_ADD(ep.date_upd, INTERVAL '.$not_update_for_days.' DAY)
			LIMIT '.$limit);		
	}
	
	public static function insert($data)
	{
		return Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_product', $data, 'INSERT');		
	}
	
	public static function updateByIdProductRef($id_product_ref, $data)
	{
		return Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_product', $data, 'UPDATE', '`id_product_ref` = '.$id_product_ref);
	}
	
	public static function deleteByIdProductRef($id_product_ref)
	{
		return Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ebay_product` 
			WHERE `id_product_ref` = \''.pSQL($id_product_ref).'\'');		
	}
}