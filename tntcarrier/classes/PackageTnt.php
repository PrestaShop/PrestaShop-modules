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

class PackageTnt
{
	private $_idOrder;
	private $_order;
	
	public	function __construct($id_order)
	{
		$this->_idOrder = $id_order;
		$this->_order = new Order((int)($this->_idOrder));
	}
	
	public function setShippingNumber($number)
	{
		if ($this->_order->shipping_number == '')
		{
			$this->_order->shipping_number = $number;
			$this->_order->update();
		}
		$this->insertSql($number);	
	}
	
	public function getShippingNumber()
	{
		$tab = Db::getInstance()->ExecuteS('SELECT `shipping_number` FROM `'._DB_PREFIX_.'tnt_carrier_shipping_number` WHERE `id_order` = "'.(int)($this->_idOrder).'"');
		return ($tab);
	}
	
	public function insertSql($number)
	{
		Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'tnt_carrier_shipping_number` (`id_order`, `shipping_number`) 
			VALUES ("'.(int)($this->_idOrder).'", "'.pSQL($number).'")');
	}
	
	public function getOrder()
	{
		return ($this->_order);
	}
}
