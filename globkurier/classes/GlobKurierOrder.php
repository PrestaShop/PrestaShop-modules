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

class GlobKurierOrder extends ObjectModel {

	const PS_ORDER_NOT_SYNC = 0;
	const PS_ORDER_SYNC = 1;
	const PS_ORDER_GK_PROCESS = 2;
	const PS_ORDER_GK_DONE = 3;
	const PS_ORDER_CANCELED = 4;

	public $id_order;
	public $id_cart;
	public $id_customer;
	public $order_number;
	public $gk_number;
	public $flag;

	protected $table = 'globkurier_order';
	protected $identifier = 'id_globkurier_order';

	protected $fieldsValidate = array('order_number' => 'isString', );

	/**
	 * Given array fields
	 * 
	 * @param void
	 * @return array
	 */
	public function getFields()
	{
		parent::validateFields();
		$fields['id_globkurier_order'] = (int)$this->id;
		$fields['id_order'] = (int)$this->id_order;
		$fields['id_cart'] = (int)$this->id_cart;
		$fields['id_customer'] = (int)$this->id_customer;
		$fields['order_number'] = (string)$this->order_number;
		$fields['gk_number'] = (string)$this->gk_number;
		$fields['flag'] = (int)$this->flag;
		return $fields;
	}

	/**
	 * Get an empty globkurier order for the given cart if it exists, or a new globkurier order
	 *
	 * @param int $id_cart
	 * @return GlobkurierOrder
	 */
	public static function getEmptyGlobKurierOrderOrder($id_cart)
	{
		$result = Db::getInstance()->getRow('
			SELECT g.*
			FROM `'._DB_PREFIX_.'globkurier_order` g
			WHERE g.`id_cart` = '.(int)$id_cart.'
			AND g.`id_order` = 0');

		$globkurier_order = new self();

		if ($result)
			$globkurier_order->setData($result);

		return $globkurier_order;
	}

	/**
	 * Return a completed globkurier order object given an id_order
	 *
	 * @param int $id_order
	 * @return boolean|GlobKurierOrder
	 */
	public static function getByIdOrder($id_order)
	{
		$result = Db::getInstance()->getRow('
			SELECT g.*
			FROM `'._DB_PREFIX_.'globkurier_order` g
			WHERE g.`id_order` = '.(int)$id_order);
		if (!$result)
			return false;
		$globkurier_order = new self();
		$globkurier_order->setData($result);
		return $globkurier_order;
	}

	/**
	 * Init an empty object with data from db
	 *
	 * @param array $result row from db
	 * @return void
	 */
	public function setData($result)
	{
		$this->id = (int)$result['id_globkurier_order'];
		$this->id_order = (int)$result['id_order'];
		$this->id_cart = (int)$result['id_cart'];
		$this->id_customer = (int)$result['id_customer'];
		$this->order_number = (string)$result['order_number'];
		$this->gk_number = (string)$result['gk_number'];
		$this->flag = (int)$result['flag'];
	}
}