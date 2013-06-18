<?php

class EbayProductConfiguration
{
	
	public static function getByProductIds($product_ids)
	{
		return Db::getInstance()->executeS('SELECT `blacklisted`, `extra_images`
			FROM `'._DB_PREFIX_.'ebay_product_configuration`
			WHERE `id_product` IN ('.implode(',', $product_ids).')');
	}

	public static function getBlacklistedProductIds()
	{
		$res = Db::getInstance()->executeS('SELECT `id_product` 
			FROM `'._DB_PREFIX_.'ebay_product_configuration`
			WHERE `blacklisted` = 1');
		return array_map(function($row) {return $row['id_product'];}, $res);
	}

	public static function insertOrUpdate($product_id, $data)
	{
		if (!count($data))
			return;
		$sql = 'INSERT INTO `'._DB_PREFIX_.'ebay_product_configuration` (`id_product`, `'.implode('`,`', array_keys($data)).'`)
			VALUES ('.$product_id.', '.implode(',', $data).')
			ON DUPLICATE KEY UPDATE ';
		$fields_strs = array();
		foreach ($data as $field => $value)
			$fields_strs[] = '`'.$field.'` = '.$value;
		$sql .= implode(',', $fields_strs);
		
		return Db::getInstance()->execute($sql);
	}
	
	/*
	public static function insert($data)
	{
		Db::getInstance()->insert('ebay_product_configuration', $data, false, true, Db::REPLACE);
	}
	*/
	
}