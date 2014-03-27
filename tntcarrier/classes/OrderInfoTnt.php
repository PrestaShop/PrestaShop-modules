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

class OrderInfoTnt
{
	private $_idOrder;
	
	public	function __construct($id_order)
	{
		$this->_idOrder = $id_order;
	}
	
	public function getInfo()
	{
		$info = Db::getInstance()->ExecuteS('SELECT o.shipping_number, a.lastname, a.firstname, a.address1, a.address2, a.postcode, a.city, a.phone, a.phone_mobile, c.email, c.id_customer, a.company
			FROM `'._DB_PREFIX_.'orders` as o, `'._DB_PREFIX_.'address` as a, `'._DB_PREFIX_.'customer` as c
			WHERE o.id_order = "'.(int)$this->_idOrder.'" AND a.id_address = o.id_address_delivery AND c.id_customer = o.id_customer');

		if (!$info)
			return false;
			
		$weight = Db::getInstance()->ExecuteS('SELECT p.weight, o.product_quantity
			FROM `'._DB_PREFIX_.'order_detail` as o, `'._DB_PREFIX_.'product` as p
			WHERE o.id_order = "'.(int)$this->_idOrder.'" AND p.id_product = o.product_id');
			
		$option = Db::getInstance()->getRow('SELECT t.option
			FROM `'._DB_PREFIX_.'tnt_carrier_option` as t , `'._DB_PREFIX_.'orders` as o
			WHERE t.id_carrier = o.id_carrier AND o.id_order = "'.(int)$this->_idOrder.'"');
			
		if ($option != null && strpos($option['option'], "D") !== false)
			$drop_off = Db::getInstance()->getRow('SELECT d.code, d.name, d.address, d.zipcode, d.city, d.due_date
				FROM `'._DB_PREFIX_.'tnt_carrier_drop_off` as d , `'._DB_PREFIX_.'orders` as o
				WHERE d.id_cart = o.id_cart AND o.id_order = "'.(int)$this->_idOrder.'"');

		$drop_date = Db::getInstance()->getValue('SELECT d.due_date FROM `'._DB_PREFIX_.'tnt_carrier_drop_off` as d, `'._DB_PREFIX_.'orders` as o WHERE o.id_order = "'.(int)$this->_idOrder.'" AND d.id_cart = o.id_cart');
		$w = 0;
		$tooBig = false;
		foreach ($weight as $key => $val)
		{
			while ($val['product_quantity'] > 0)
			{
				if ((int)($val['weight']) > 20)
					return "Un ou plusieurs articles sont sup&eacute;rieurs &agrave; 20 Kg<br/>Vous devez contacter votre commercial TNT";
				if ($w + $val['weight'] > 20)
				{
					$info[1]['weight'][] = (string)($w);
					$w = $val['weight'];
				}
				else
					$w += $val['weight'];
				$val['product_quantity']--;
			}
		}

		$info[1]['weight'][] = (string)($w);

		$info[5] = array('saturday' => false);

		if ($drop_date != null)
		{
			if (date("w", strtotime($drop_date)) == 6 && date('w', strtotime('now')) == 5)
				$next_day = date("Y-m-d", strtotime('now'));
			else if (date("w", strtotime($drop_date)) == 6)
				$next_day = date("Y-m-d", strtotime($drop_date.' + 2 days'));
			else if (date("w", strtotime($drop_date)) == 0)
				$next_day = date("Y-m-d", strtotime($drop_date.' + 1 day'));
			else
				$next_day = date("Y-m-d", strtotime($drop_date));
		}
		else
			$next_day = '';
		$newDate = Tools::getValue('dateErrorOrder');
		$info[2] = array('delivery_date' => ($newDate != '' ? $newDate : $next_day));


		$info[3]['option'] = '';
		if ($option)
		{
			if (strpos($option['option'], 'S') !== false)
				$info[3] = array('option' => str_replace('S', '', $option['option']));
			else
				$info[3] = array('option' => $option['option']);
		}
		if (isset($drop_off) && !empty($drop_off))
			$info[4] = $drop_off;
		else
			$info[4] = null;

		$info[0]['id_customer'] = $this->_idOrder;
		$info[0]['phone'] = str_replace('.', '', $info[0]['phone']);
		$info[0]['phone_mobile'] = str_replace('.', '', $info[0]['phone_mobile']);
		return $info;
	}

	public function isFirstPackage()
	{
		$id_order = Db::getInstance()->getValue('SELECT `id_order` FROM `'._DB_PREFIX_.'tnt_package_history` WHERE `pickup_date` = NOW()');
		if ($id_order)
			return false;
		return true;
	}

	public function getDeleveryDate($idOrder, &$info)
	{

		$option = Db::getInstance()->getRow('SELECT t.option
			FROM `'._DB_PREFIX_.'tnt_carrier_option` as t , `'._DB_PREFIX_.'orders` as o
			WHERE t.id_carrier = o.id_carrier AND o.id_order = "'.(int)$idOrder.'"');

		if (Configuration::get('TNT_CARRIER_SHIPPING_COLLECT') == 1)
		{
			if (date('w') == 5)
			{
				if ((date('H') < date('H', strtotime(Configuration::get('TNT_CARRIER_SHIPPING_CLOSING'))) && !$this->isFirstPackage()) || (date('H') < 15 && $this->isFirstPackage()))
				{
					$delivery_day = date("Y-m-d");
					if ($option['option'] == 'JS')
						$info[5] = array('saturday' => true);
				}
				else
					date("Y-m-d", strtotime(' + 2 days'));
			}
			else if (date("w") == 6)
				$delivery_day = date("Y-m-d", strtotime(' + 2 days'));
			else if ((date('H') < date('H', strtotime(Configuration::get('TNT_CARRIER_SHIPPING_CLOSING'))) && !$this->isFirstPackage() && date('H') >= 15)	|| date('H') < 15)
				$delivery_day = date("Y-m-d");
			else
				$delivery_day = date("Y-m-d", strtotime(' + 1 day'));
		}
		else
		{
			if (date('w') == 5 && date('H') < date('H', strtotime(Configuration::get('TNT_CARRIER_SHIPPING_CLOSING'))))
			{
				$delivery_day = date("Y-m-d");
				if ($option['option'] == 'JS')
					$info[5] = array('saturday' => true);
			}
			else if ((date('w') == 5 && date('H') >= date('H', strtotime(Configuration::get('TNT_CARRIER_SHIPPING_CLOSING')))) || date("w") == 6)
				$delivery_day = date("Y-m-d", strtotime(' + 2 days'));
			else if (date('H') < date('H', strtotime(Configuration::get('TNT_CARRIER_SHIPPING_CLOSING'))))
				$delivery_day = date("Y-m-d");
			else
				$delivery_day = date("Y-m-d", strtotime(' + 1 day'));
		}
		$info[2] = array('delivery_date' => $delivery_day);
	}
}
