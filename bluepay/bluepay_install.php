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

class BluePayInstall
{
	/**
	 * Create BluePay tables
	 */
	public function createTables()
	{
		if (!Db::getInstance()->Execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'bluepay_order` (
			`order_id` int(10) unsigned NOT NULL,
			`transaction_id` varchar(12) NOT NULL,
			`invoice_id` varchar(255) DEFAULT NULL,
			`currency` varchar(10) NOT NULL,
			`total_paid` varchar(50) NOT NULL,
			`transaction_type` varchar(4) NOT NULL,
			`payment_date` varchar(50) NOT NULL,
			`payment_type` varchar(6) NOT NULL,
			`payment_status` varchar(255) DEFAULT NULL,
			PRIMARY KEY (`order_id`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'))
			return false;

		if (!Db::getInstance()->Execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'bluepay_customer` (
			`customer_id` int(10) unsigned NOT NULL,
			`bluepay_customer_id` varchar(12) NOT NULL,
			`customer_name` varchar(255),
			`customer_email` varchar(255),
			`payment_type` varchar(6) NOT NULL,
			`masked_payment_account` varchar(20),
			`card_type` varchar(4),
			`expiration_date` varchar(4),
			PRIMARY KEY (`customer_id`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'))
			return false;
	}

	/**
	 * Set BluePay configuration table
	 */
	public function updateConfiguration()
	{
		Configuration::updateValue('BP_REQUIRE_CVV2', 'Yes');
		Configuration::updateValue('BP_CHECKOUT_IFRAME', 'Yes');
		Configuration::updateValue('BP_ALLOW_STORED_PAYMENTS', 'Yes');
	}

	/**
	* Create a new order state for authorizations
	 */
	public function createOrderState()
	{
		if (!Configuration::get('BP_OS_AUTHORIZATION'))
		{
			$order_state = new OrderState();
			$order_state->name = array();

			foreach (Language::getLanguages() as $language)
				$order_state->name[$language['id_lang']] = 'BluePay approved authorization';

			$order_state->send_email = false;
			$order_state->color = '#a2daff';
			$order_state->hidden = false;
			$order_state->delivery = false;
			$order_state->logable = true;
			$order_state->invoice = true;

			if ($order_state->add())
			{
				$source = dirname(__FILE__).'/logo.gif';
				$destination = dirname(__FILE__).'/../../img/os/'.(int)$order_state->id.'.gif';
				copy($source, $destination);
			}
			Configuration::updateValue('BP_OS_AUTHORIZATION', (int)$order_state->id);
		}
	}
}
