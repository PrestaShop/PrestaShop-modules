<?php
/*
* Shopgate GmbH
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file AFL_license.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/AFL-3.0
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to interfaces@shopgate.com so we can send you a copy immediately.
*
* @author Shopgate GmbH, Schloßstraße 10, 35510 Butzbach <interfaces@shopgate.com>
* @copyright  Shopgate GmbH
* @license   http://opensource.org/licenses/AFL-3.0 Academic Free License ("AFL"), in the version 3.0
*/

class PSShopgateOrder extends ObjectModel
{
	public		$id;

	public		$id_shopgate_order;
	public		$id_cart;
	public		$id_order;
	public		$order_number;
	public		$tracking_number;
	public		$shipping_service = 'OTHER';
	public		$shipping_cost;
	public		$shop_number;
	public		$comments;
	
	protected	$table = 'shopgate_order';
	protected	$identifier = 'id_shopgate_order';
	
	protected	$fieldsRequired = array('order_number', 'shipping_cost');
	protected	$fieldsValidate = array
	(
		'id_cart' => 'isUnsignedId',
		'id_order' => 'isUnsignedId',
		'order_number' => 'isString',
		'shipping_cost'=>'isPrice',
		'shipping_service' => 'isString',
		'tracking_number'=>'isString'
	);

	protected 	$fieldsSize = array
	(
		'tracking_number' => 32,
		'shipping_service' => 16,
		'order_number' => 16
	);
		
	public function __construct($id = NULL, $identifier = 'id_shopgate_order')
	{
		$this->identifier = $identifier;
		parent::__construct($id);
		$this->id = $this->id_shopgate_order;
		$this->identifier = 'id_shopgate_order';
	}
	
	public function getFields()
	{
		parent::validateFields();
		$fields['id_cart'] = (int)($this->id_cart);
		$fields['id_order'] = (int)($this->id_order);
		$fields['order_number'] = pSQL($this->order_number);
		$fields['tracking_number'] = pSQL($this->tracking_number);
		$fields['shipping_service'] = pSQL($this->shipping_service);
		$fields['shipping_cost'] = (float)($this->shipping_cost);
		$fields['shop_number'] = pSQL($this->shop_number);
		$fields['comments'] = pSQL($this->comments, true);
		return $fields;
	}
	
	public static function instanceByCartId($id_cart = 0)
	{
		return new PSShopgateOrder($id_cart, 'id_cart');
	}

	public static function instanceByOrderId($id_order = 0)
	{
		return new PSShopgateOrder($id_order, 'id_order');
	}

	public static function instanceByOrderNumber($order_number = 0)
	{
		return new PSShopgateOrder($order_number, 'order_number');
	}
}
