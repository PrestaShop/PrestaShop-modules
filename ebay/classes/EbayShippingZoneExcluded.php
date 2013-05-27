<?php

class EbayShippingZoneExcluded
{
	public static function getAll()
	{
		return Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'ebay_shipping_zone_excluded`
			ORDER BY region, description');
	}

	public static function getExcluded()
	{
		return Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'ebay_shipping_zone_excluded`
			WHERE excluded = 1');
	}
	
	public static function insert($data)
	{
		return Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_shipping_zone_excluded', $data, 'INSERT');
	}
}