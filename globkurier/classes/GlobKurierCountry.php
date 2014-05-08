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
 *  @license	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class GlobKurierCountry extends ObjectModel {

	public $id_globkurier_country;
	public $name;
	public $road;
	public $ue;
	public $zone_fly;
	public $zone_road;
	public $time_fly;
	public $time_road;
	public $country_code;

	protected $table = 'globkurier_country';
	protected $identifier = 'id_globkurier_country';

	protected $fieldsValidate = array('country_code' => 'isString', 'name' => 'isString', );

	public function getFields()
	{
		parent::validateFields();
		$fields['id_globkurier_country'] = (int)$this->id;
		$fields['name'] = (int)$this->name;
		$fields['road'] = (int)$this->road;
		$fields['ue'] = (int)$this->ue;
		$fields['zone_fly'] = (string)$this->zone_fly;
		$fields['zone_road'] = (string)$this->zone_road;
		$fields['time_fly'] = (string)$this->time_fly;
		$fields['time_road'] = (string)$this->time_road;
		$fields['country_code'] = (string)$this->country_code;
		return $fields;
	}

	/**
	 * Given all country to list
	 *
	 * @param void
	 * @return boolean|objResult
	 */
	public static function getAllCountry()
	{
		$result = Db::getInstance()->ExecuteS('
		SELECT c.* 
		FROM `'._DB_PREFIX_.'globkurier_country` c');
		return $result;
	}

	/**
	 * Return a country by id
	 *
	 * @param int $id_globkurier_country'
	 * @return boolean|rowResult
	 */
	public static function getById($id_globkurier_country)
	{
		$result = Db::getInstance()->getRow('
			SELECT c.* 
			FROM `'._DB_PREFIX_.'globkurier_country` c
			WHERE c.`id_globkurier_country` = '.(int)$id_globkurier_country);
		return $result;
	}
}