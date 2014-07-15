<?php
/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class serviceCache
{
	private $_dateBefore;
	private $_dateNow;
	private $_idCard;
	private $_zipCode;
	private $_company;
	private $_shipZipCode;
	private $_city;
	private $_companyCity;
	
	public	function __construct($id_card, $zipcode, $city, $company, $shipZipCode, $companyCity)
	{
		$this->_dateBefore = date("Y-m-d H:i:s", mktime(date("H"), date("i") - 10, date("s"), date("m")  , date("d"), date("Y")));
		$this->_dateNow = date('Y-m-d H:i:s');
		$this->_idCard = (int)$id_card;
		$this->_zipCode = pSQL($zipcode);
		$this->_city = pSQL($city);
		$this->_company = pSQL($company);
		$this->_shipZipCode = pSQL($shipZipCode);
		$this->_companyCity = pSQL($companyCity);
	}
	
	public function getFaisabilityAtThisTime()
	{
		if (Db::getInstance()->getValue('SELECT * FROM `'._DB_PREFIX_.'tnt_carrier_cache_service` 
			WHERE `date` >= "'.$this->_dateBefore.'" AND `date` <= "'.$this->_dateNow.'" 
			AND id_card = "'.$this->_idCard.'" 
			AND zipcode = "'.$this->_zipCode.'" 
			AND city = "'.$this->_city.'" 
			AND company = "'.$this->_company.'" 
			AND company_city = "'.$this->_companyCity.'" 
			AND ship_zip_code = "'.$this->_shipZipCode.'"'))
			return true;
		return false;
	}
	
	public function deletePreviousServices()
	{
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'tnt_carrier_cache_service` WHERE `id_card` = "'.(int)($this->_idCard).'"');
	}
	
	public function clean()
	{
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'tnt_carrier_cache_service` WHERE `date` <= "'.$this->_dateBefore.'" ');
	}
	
	
	public static function deleteServices($idCard)
	{
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'tnt_carrier_cache_service` WHERE `id_card` = "'.(int)($idCard).'"');
	}
	
	public function errorCodePostal()
	{
		Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'tnt_carrier_cache_service` (`id_card`, `date`, `zipcode`, `city`, `company`, `company_city`, `ship_zip_code`, `error`) 
			VALUES ("'.(int)($this->_idCard).'", "'.$this->_dateNow.'", "'.$this->_zipCode.'", "'.$this->_city.'", "'.$this->_company.'", "'.$this->_companyCity.'","'.$this->_shipZipCode.'", "1")');
	}
	
	public static function getError($idCart)
	{
		return (Db::getInstance()->getValue('SELECT error FROM `'._DB_PREFIX_.'tnt_carrier_cache_service` WHERE `id_card` = "'.(int)($idCart).'"'));
	}

	public static function getDueDate($idCart, $services)
	{
		$duedate = array();
		foreach ($services as $key => $val)
		{
			$date  = Db::getInstance()->getValue('SELECT due_date FROM `'._DB_PREFIX_.'tnt_carrier_cache_service` WHERE `code` = "'.pSQL($val['option']).'"');
			$dateSaturday = Db::getInstance()->getValue('SELECT due_date FROM `'._DB_PREFIX_.'tnt_carrier_cache_service` WHERE `code` = "'.pSQL($val['option']).'S"');
			if ($dateSaturday == null)
				$duedate[$val['id_carrier']] = date("d/m/Y", strtotime($date));
			else
				$duedate[$val['id_carrier']] = date("d/m/Y", strtotime($dateSaturday));
		}
		return $duedate;
	}

	public function putInCache($s)
	{
		foreach ($s as $key => $val)
		{
			if (isset($val->Service) && is_array($val->Service))
				foreach ($val->Service as $k => $v)
				{
					if ($v->saturdayDelivery == '0')
						$serviceCode = $v->serviceCode;
					else
						$serviceCode = $v->serviceCode.'S';
					Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'tnt_carrier_cache_service` (`id_card`, `code`, `date`, `zipcode`, `city`, `company`, `company_city`, `ship_zip_code`, `due_date`)
						VALUES ("'.(int)($this->_idCard).'", "'.pSQL($serviceCode).'","'.$this->_dateNow.'", "'.$this->_zipCode.'", "'.$this->_city.'", "'.$this->_company.'", "'.$this->_companyCity.'","'.$this->_shipZipCode.'", "'.$v->dueDate.'")');
				}
			else
			{
				if (isset($val->Service))
				{
					if ($val->Service->saturdayDelivery == '0')
						$serviceCode = $val->Service->serviceCode;
					else
						$serviceCode = $val->Service->serviceCode.'S';
					Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'tnt_carrier_cache_service` (`id_card`, `code`, `date`, `zipcode`, `city`, `company`, `company_city`, `ship_zip_code`, `due_date`)
						VALUES ("'.(int)($this->_idCard).'", "'.pSQL($serviceCode).'","'.$this->_dateNow.'", "'.$this->_zipCode.'", "'.$this->_city.'", "'.$this->_company.'", "'.$this->_companyCity.'", "'.$this->_shipZipCode.'", "'.$val->Service->dueDate.'")');
				}
			}
		}
	}
	
	public function getServices()
	{
		return (Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'tnt_carrier_cache_service` WHERE id_card = "'.(int)($this->_idCard).'"'));
	}
	
	public static function getServicesCart($idCard)
	{
		return (Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'tnt_carrier_cache_service` WHERE id_card = "'.(int)($idCard).'"'));
	}
}
