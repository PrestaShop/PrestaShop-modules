<?php
/* 2007-2011 PrestaShop
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
*  @copyright  2007-2011 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class KialaCountry extends ObjectModel
{
	public $id_country;
	public $dspid;
	public $preparation_delay;
	public $active;
	public $pickup_country;

	protected $table = 'kiala_country';
	protected $identifier = 'id_kiala_country';

	public function getFields()
	{
		parent::validateFields();

		$fields['id_country'] = (int)$this->id_country;
		$fields['dspid'] = pSQL($this->dspid);
		$fields['preparation_delay'] = (int)$this->preparation_delay;
		$fields['active'] = (int)$this->active;
		$fields['pickup_country'] = (int)$this->pickup_country;
		return $fields;
	}

	/**
	 * Get all kiala countries
	 *
	 * @param int $active_only
	 * @return array of arrays
	 */
	public static function getKialaCountries($active_only = false)
	{
		$result = Db::getInstance()->ExecuteS('
			SELECT `id_kiala_country`, `id_country`, `dspid`, `preparation_delay`, `active`
			FROM `'._DB_PREFIX_.'kiala_country`'.
			($active_only ? 'WHERE `active` = 1' : ''));

		return $result;
	}

	/**
	 * Get KialaCountry object by id_country
	 *
	 * @param int $id_country
	 * @return boolean|KialaCountry fetched object or false
	 */
	public static function getByIdCountry($id_country)
	{
		// Luxemburg is grouped with Belgium by Kiala
		if (Country::getIsoById($id_country) == 'LU')
			$id_country = Country::getByIso('BE');

		$result = Db::getInstance()->getRow('
			SELECT `id_kiala_country`, `id_country`, `dspid`, `preparation_delay`, `active`
			FROM `'._DB_PREFIX_.'kiala_country`
			WHERE `id_country` = '.(int)$id_country);

		if (!$result)
			return false;

		$kiala_country = new self();
		$kiala_country->setData($result);

		return $kiala_country;
	}

	/**
	 * Set the object fields
	 *
	 * @param array $result
	 */
	protected function setData($result)
	{
		$this->id = $result['id_kiala_country'];
		$this->id_country = $result['id_country'];
		$this->dspid = $result['dspid'];
		$this->preparation_delay = $result['preparation_delay'];
		$this->active = $result['active'];
	}

	public function isActive()
	{
		return ($this->id && $this->dspid && $this->active && $this->preparation_delay);
	}

	/**
	 * Get the KialaCountry where the shipment is picked up
	 *
	 * @return boolean|KialaCountry
	 */
	public static function getPickupCountry()
	{
		$result = Db::getInstance()->getRow('
		SELECT `id_kiala_country`, `id_country`, `dspid`, `preparation_delay`, `active`
		FROM `'._DB_PREFIX_.'kiala_country`
		WHERE `pickup_country` = 1');

		if (!$result)
			return false;

		$kiala_country = new self();
		$kiala_country->setData($result);

		return $kiala_country;
	}
}