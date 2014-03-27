<?php
/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class Toolbox
{
	private static $_log ;
	protected static $handle_instance = false;

	public static function manageError($e, $type_error)
	{
		Toolbox::writeLog(true, $type_error." => ".$e);
	}

	public static function writeLog($is_error = false, $message = "")
	{
		if (!self::$handle_instance)
				self::$handle_instance = @fopen(dirname(__FILE__).'/../logs/logs-'.date('Y-m-d').'.txt', 'a+');
		
		if (!empty(self::$_log) && !$is_error)
		{
			if (self::$handle_instance)
				fwrite(self::$handle_instance, self::$_log);

			self::$_log = '';
		}

		if ($is_error)
		{
			if (self::$handle_instance)
				fwrite(self::$handle_instance, date('Y-m-d H:i:s').' - '.$message."\n");
		}
	}

	public static function addLogLine($string, $time = true)
	{
		self::$_log .= ($time ? date('Y-m-d H:i:s').' - ' : "\t").$string."\n";
	}

	public static function numericFilter($string)
	{
		return preg_replace("/[^0-9]/u", "", $string);
	}

	public static function stringFilter($string)
	{
		return preg_replace("/[^àáâãäåçèéêëìíîïðòóôõöùúûüýÿa-zA-Z- ]/u", " ", $string);
	}

	public static function stringWithNumericFilter($string)
	{
		return preg_replace("/[^àáâãäåçèéêëìíîïðòóôõöùúûüýÿa-zA-Z0-9- ]/u", " ", $string);
	}

	public static function existAddress($order_infos, $id_country, $id_customer)
	{
		$addr = Db::getInstance()->getRow('
			SELECT * FROM '._DB_PREFIX_.'address WHERE
			address1 = "'.pSQL($order_infos->Address1).'" AND
			address2 = "'.pSQL($order_infos->Address2).'" AND
			city = "'.pSQL($order_infos->CityName).'" AND
			firstname = "'.pSQL($order_infos->FirstName).'" AND
			lastname = "'.pSQL($order_infos->LastName).'" AND
			postcode = "'.pSQL($order_infos->PostalCode).'" AND
			phone = "'.pSQL(Toolbox::numericFilter($order_infos->Phone)).'" AND
			phone_mobile = "'.pSQL(Toolbox::numericFilter($order_infos->Mobile)).'" AND
			id_country = '.(int)($id_country).' AND
			id_customer = '.(int)($id_customer));

		if ($addr)
			return $addr["id_address"];
		else
			return false;
	}
	
	public static function removeAccents($str, $charset = 'utf-8')
	{
		$str = htmlentities($str, ENT_NOQUOTES, $charset);
		
		$str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#u', '\1', $str);
		$str = preg_replace('#&([A-za-z]{2})(?:lig);#u', '\1', $str);
		$str = preg_replace('#&[^;]+;#u', '', $str);
		
		return $str;
	}
	
	public static function setNetEvenCategories($display = false)
	{
		$neteven_features_dirname = dirname(__FILE__).'/../neteven_features/';
		$files = scandir($neteven_features_dirname);
		
		foreach ($files as $file)
		{
			if ($file != '..' && $file != '.' && $file != 'index.php')
			{
				if (($handle = fopen($neteven_features_dirname.$file, 'r')) !== false)
				{
					$row = 0;

					while (($data = fgetcsv($handle, 1000, ';')) !== false)
					{
						if ($row != 0)
						{
							if(empty($data[0]) || empty($data[1]) || empty($data[2]))
								continue;

							if (Db::getInstance()->getValue('SELECT COUNT(*) FROM `'._DB_PREFIX_.'orders_gateway_feature` WHERE `value` = "'.pSQL($data[2]).'" AND `category` = "'.pSQL($data[0]).'" '))
								continue;
							
							Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'orders_gateway_feature` (`name`, `value`, `category`) VALUES ("'.pSQL($data[1]).'", "'.pSQL($data[2]).'", "'.pSQL($data[0]).'")');
							
							if($display)
								echo 'Add '.$data[1].' into '.$data[2].'<br/>';
						}
						$row++;
					}
					fclose($handle);
				}
			}
		}
	}
	
	public static function displayDebugMessage($message, $error = false)
	{
		echo ($error ? '<span style="color:red;">' : '').$message.($error ? '</span>' : '').'<br />';
	}
}