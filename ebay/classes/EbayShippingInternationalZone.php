<?php

class EbayShippingInternationalZone
{
	public static function getIdEbayZonesByIdEbayShipping($id_ebay_shipping)
	{
		return Db::getInstance()->ExecuteS('SELECT id_ebay_zone 
			FROM '._DB_PREFIX_.'ebay_shipping_international_zone 
			WHERE id_ebay_shipping = "'.(int)$id_ebay_shipping.'"');
	}
	
	public static function insert($id_ebay_shipping, $id_ebay_zone)
	{
		$sql = 'INSERT INTO '._DB_PREFIX_.'ebay_shipping_international_zone 
			VALUES(\''.(int)$id_ebay_shipping.'\', \''.pSQL($id_ebay_zone).'\')';
		DB::getInstance()->Execute($sql);
	}
}