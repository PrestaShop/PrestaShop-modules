<?php
/*
* 2013 BluePay
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
*  @author BluePay Processing, LLC
*  @copyright  2013 BluePay Processing, LLC
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

if (!defined('_PS_VERSION_'))
	exit;

class BluePayOrder
{
	/**
	* Get specific order from order ID
	*/
	public static function getOrderById($order_id)
	{
		return Db::getInstance()->getRow(
			'SELECT * FROM `'._DB_PREFIX_.'bluepay_order`
			WHERE `order_id` = '.(int)$order_id
		);
	}

	/**
	* Get specific order from BluePay transaction ID
	*/
	public static function getIdOrderByTransactionId($transaction_id)
	{
		$sql = 'SELECT `order_id`
			FROM `'._DB_PREFIX_.'bluepay_order`
			WHERE `transaction_id` = \''.pSQL($transaction_id).'\'';

		$result = Db::getInstance()->getRow($sql);

		if ($result != false)
			return (int)$result['id_order'];
		return 0;
	}

	/**
	* Saves order into ps_bluepay_order table
	*/
	public static function saveOrder($order_id, $transaction)
	{
		$order = new Order((int)$order_id);
		$total_paid = (float)$transaction['total_paid'];
		if (!isset($transaction['payment_status']) || !$transaction['payment_status'])
			$transaction['payment_status'] = 'NULL';
		Db::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_.'bluepay_order`
			(`order_id`, `transaction_id`, `invoice_id`, `total_paid`, `transaction_type`, `payment_date`, `payment_type`, `payment_status`)
			VALUES ('.(int)$order->id.', \''.pSQL($transaction['transaction_id']).'\', \''.pSQL($transaction['invoice_id']).'\',
				\''.pSQL($total_paid).'\',
				\''.pSQL(Configuration::get('BP_TRANSACTION_TYPE')).'\',
				\''.pSQL($transaction['payment_date']).'\',
				\''.pSQL($transaction['payment_type']).'\',
				\''.pSQL($transaction['payment_status']).'\')'
		);
	}

	/**
	* Updates ps_bluepay_order table row
	*/
	public static function updateOrder($order_id, $transaction)
	{
		$total_paid = (float)$transaction['total_paid'];

		if (!isset($transaction['payment_status']) || !$transaction['payment_status'])
			$transaction['payment_status'] = 'NULL';

		$sql = 'UPDATE `'._DB_PREFIX_.'bluepay_order`
			SET `payment_status` = \''.pSQL($transaction['payment_status']).'\'
			WHERE `order_id` = \''.(int)$order_id.'\'
				AND `transaction_id` = \''.pSQL($transaction['transaction_id']).'\'
				AND `total_paid` = \''.pSQL($transaction['total_paid']).'\'';

		Db::getInstance()->Execute($sql);
	}
}
