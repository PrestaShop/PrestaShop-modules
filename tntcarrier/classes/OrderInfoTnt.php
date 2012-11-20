<?php

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
			$dropOff = Db::getInstance()->getRow('SELECT d.code, d.name, d.address, d.zipcode, d.city, d.due_date
												FROM `'._DB_PREFIX_.'tnt_carrier_drop_off` as d , `'._DB_PREFIX_.'orders` as o
												WHERE d.id_cart = o.id_cart AND o.id_order = "'.(int)$this->_idOrder.'"');
		$dropDate = Db::getInstance()->getValue('SELECT d.due_date FROM `'._DB_PREFIX_.'tnt_carrier_drop_off` as d, `'._DB_PREFIX_.'orders` as o WHERE o.id_order = "'.(int)$this->_idOrder.'" AND d.id_cart = o.id_cart');
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

		if ($dropDate != null)
		{
			if (date("w", strtotime($dropDate)) == 6 && date('w', strtotime('now')) == 5)
			{
				$next_day = date("Y-m-d", strtotime('now'));
				$info[5] = array('saturday' => true);
			}
			else if (date("w", strtotime($dropDate)) == 6)
				$next_day = date("Y-m-d", strtotime($dropDate.' + 2 days'));
			else if (date("w", strtotime($dropDate)) == 0)
				$next_day = date("Y-m-d", strtotime($dropDate.' + 1 day'));
			else
				$next_day = date("Y-m-d", strtotime($dropDate));
		}
		else
			$next_day = '';
		$newDate = Tools::getValue('dateErrorOrder');
		$info[2] = array('delivery_date' => ($newDate != '' ? $newDate : $next_day));


		if ($option)
		{
			if (strpos($option['option'], 'S') !== false)
				$info[3] = array('option' => str_replace('S', '', $option['option']));
			else
				$info[3] = array('option' => $option['option']);
		}
		if (isset($dropOff) && !empty($dropOff))
			$info[4] = $dropOff;
		else
			$info[4] = null;
		//var_dump($info);
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
		if (Configuration::get('TNT_CARRIER_SHIPPING_COLLECT') == 1)
		{
			if (date('w') == 5)
			{
				if ((date('H') < date('H', strtotime(Configuration::get('TNT_CARRIER_SHIPPING_CLOSING'))) && !$this->isFirstPackage()) || (date('H') < 15 && $this->isFirstPackage()))
				{
					$delivery_day = date("Y-m-d");
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

?>
