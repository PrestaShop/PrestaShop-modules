<?php

class EbaySyncBlacklistProduct
{
	public static function getBlacklistedProductIds()
	{
		$res = Db::getInstance()->ExecuteS('SELECT `id_product` from `'._DB_PREFIX_.'ebay_sync_blacklist_product`');
		return array_map(function($row) {return $row['id_product'];}, $res);
	}
	
	public static function insertProductIds($product_ids)
	{
		$data = array();
		foreach($product_ids as $product_id)
			$data[] = array('id_product' => $product_id);

		$db = Db::getInstance();
		$db->insert('ebay_sync_blacklist_product', $data, false, true, Db::INSERT_IGNORE);
	}

	public static function deleteByProductIds($product_ids)
	{
		if (!$product_ids)
			return;
		
		$db = Db::getInstance();
		$db->delete('ebay_sync_blacklist_product', '`id_product` in ('.implode(',', $product_ids).')');
	}
	
}