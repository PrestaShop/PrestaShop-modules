<?php

class EbayDeliveryTimeOptions
{
	public static function getAll()
	{
		return Db::getInstance()->ExecuteS('SELECT * 
			FROM '._DB_PREFIX_.'ebay_delivery_time_options');
	}
	
	public static function getTotal()
	{
		return Db::getInstance()->getValue('SELECT COUNT(*) AS nb 
			FROM '._DB_PREFIX_.'ebay_delivery_time_options');
	}
	
	public static function insert($data)
	{
		Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_delivery_time_options', $data, 'INSERT');
	}
}