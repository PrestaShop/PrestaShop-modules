<?php

class EbayShipping
{
	
	public static function getPsCarrierByEbayCarrier($ebay_carrier)
	{
		return Db::getInstance()->getValue('SELECT ps_carrier 
			FROM `'._DB_PREFIX_.'ebay_shipping` 
			WHERE `ebay_carrier` = \''.pSQL($ebay_carrier).'\'');		
	}
	
	public static function getNationalShippings()
	{
		return Db::getInstance()->ExecuteS('SELECT * 
			FROM '._DB_PREFIX_.'ebay_shipping WHERE international = 0');
	}	
	
	public static function getInternationalShippings()
	{
		return Db::getInstance()->ExecuteS('SELECT * 
			FROM '._DB_PREFIX_.'ebay_shipping WHERE international = 1');
	}
	
	public static function insert($ebay_carrier, $ps_carrier, $extra_fee, $international = false)
	{
		$sql = 'INSERT INTO '._DB_PREFIX_.'ebay_shipping 
			VALUES(\'\', 
		\''.pSQL($ebay_carrier).'\',
		\''.(int)$ps_carrier.'\', 
		\''.(int)$extra_fee.'\', 
		\''.(int)$international.'\')';
		DB::getInstance()->Execute($sql);		
	}
	
	public static function truncate()
	{
		return Db::getInstance()->Execute('TRUNCATE '._DB_PREFIX_.'ebay_shipping');
	}
	
	public static function getLastShippingId()
	{
		return Db::getInstance()->getValue('SELECT id_ebay_shipping 
			FROM '._DB_PREFIX_.'ebay_shipping 
			ORDER BY id_ebay_shipping DESC');
	}
}