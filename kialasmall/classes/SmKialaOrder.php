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
*  @version  Release: $Revision: 11467 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class SmKialaOrder extends ObjectModel
{
	public $id_customer;
	public $id_order;
	public $id_cart;
	public $id_country_pickup;
	public $id_country_delivery;
	public $point_short_id;
  	public $point_alias;
	public $point_name;
	public $point_street;
	public $point_location_hint;
	public $point_zip;
	public $point_city;
	public $orderNumber;
	public $commercialValue;
	public $parcelVolume;
	public $parcelDescription;
	public $tracking_number;

	public $date_upd;

	/** @var max lifetime of empty kiala orders in hours */
	public static $empty_orders_lifetime = 24;

	protected $table = 'sm_kiala_order';
	protected $identifier = 'id_sm_kiala_order';

	protected	$fieldsValidate = array(
		'point_short_id' => 'isString',
	);

	public function getFields()
	{
		parent::validateFields();
		$fields['id_sm_kiala_order'] = (int)$this->id;
		$fields['id_customer'] = (int)$this->id_customer;
		$fields['id_cart'] = (int)$this->id_cart;
		$fields['id_order'] = (int)$this->id_order;
		$fields['id_country_pickup'] = (int)$this->id_country_pickup;
		$fields['id_country_delivery'] = (int)$this->id_country_delivery;
		$fields['point_short_id'] = pSQL($this->point_short_id);
	  	$fields['point_alias'] = pSQL($this->point_alias);
		$fields['point_name'] = pSQL($this->point_name);
		$fields['point_street'] = pSQL($this->point_street);
		$fields['point_location_hint'] = pSQL($this->point_location_hint);
		$fields['point_zip'] = pSQL($this->point_zip);
		$fields['point_city'] = pSQL($this->point_city);
		$fields['commercialValue'] = (float)$this->commercialValue;
		$fields['parcelVolume'] = (float)$this->parcelVolume;
		$fields['parcelDescription'] = pSQL($this->parcelDescription);
		$fields['date_upd'] = pSQL($this->date_upd);
		$fields['tracking_number'] = pSQL($this->tracking_number);
		return $fields;
	}

	public static function getLatestByCustomer($id_customer)
	{
		$result = Db::getInstance()->getRow('
			SELECT k.*
			FROM `'._DB_PREFIX_.'sm_kiala_order` k
			WHERE k.`id_customer` = '.(int)$id_customer.'
			ORDER by k.`date_upd` DESC');

		if (!$result)
			return false;
		$kiala_order = new self();
		$kiala_order->setData($result);

		return $kiala_order;
	}

	/**
	 * Return a completed kiala order object given an id_order
	 *
	 * @param int $id_order
	 * @return boolean|SmKialaOrder
	 */
	public static function getByOrder($id_order)
	{
		$result = Db::getInstance()->getRow('
			SELECT k.*
			FROM `'._DB_PREFIX_.'sm_kiala_order` k
			WHERE k.`id_order` = '.(int)$id_order);

		if (!$result)
			return false;
		$kiala_order = new self();
		$kiala_order->setData($result);

		return $kiala_order;
	}

	/**
	 * Get an empty kiala order for the given cart if it exists, or a new kiala order
	 * The empty kiala order stores the kiala point selected by the customer before the order is made
	 *
	 * @param int $id_cart
	 * @return SmKialaOrder
	 */
	public static function getEmptyKialaOrder($id_cart)
	{
		$result = Db::getInstance()->getRow('
			SELECT k.*
			FROM `'._DB_PREFIX_.'sm_kiala_order` k
			WHERE k.`id_cart` = '.(int)($id_cart).'
			AND k.`id_order` = 0');

		$kiala_order = new self();

		if ($result)
			$kiala_order->setData($result);

		return $kiala_order;
	}

	/**
	 * Init an empty object with data from db
	 *
	 * @param array $result row from db
	 */
	public function setData($result)
	{
		$this->id = $result['id_sm_kiala_order'];
		$this->id_customer = $result['id_customer'];
		$this->id_cart = $result['id_cart'];
		$this->id_order = $result['id_order'];
		$this->id_country_pickup = $result['id_country_pickup'];
		$this->id_country_delivery = $result['id_country_delivery'];
		$this->point_short_id = $result['point_short_id'];
	  	$this->point_alias = $result['point_alias'];
		$this->point_name = $result['point_name'];
		$this->point_street = $result['point_street'];
		$this->point_location_hint = $result['point_location_hint'];
		$this->point_zip = $result['point_zip'];
		$this->point_city = $result['point_city'];
		$this->commercialValue = $result['commercialValue'];
		$this->parcelVolume = $result['parcelVolume'];
		$this->parcelDescription = $result['parcelDescription'];
		$this->date_upd = $result['date_upd'];
		$this->tracking_number = $result['tracking_number'];
	}

	public static function cleanEmptyOrders()
	{
		$result = Db::getInstance()->execute('
			DELETE FROM `'._DB_PREFIX_.'sm_kiala_order`
			WHERE `id_order` = 0 AND `date_upd` < DATE_SUB(NOW(), INTERVAL '.(int)self::$empty_orders_lifetime.' HOUR)');
	}
}