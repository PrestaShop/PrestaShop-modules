<?php

class EbayShippingService
{
	public static function getAll()
	{
		return Db::getInstance()->ExecuteS('SELECT * 
			FROM '._DB_PREFIX_.'ebay_shipping_service');
	}
	
	public static function getTotal()
	{
		return Db::getInstance()->getValue('SELECT COUNT(*) AS nb 
			FROM '._DB_PREFIX_.'ebay_shipping_service');
	}
	
	public static function insert($data)
	{
		return Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_shipping_service', $data, 'INSERT');		
	}
}