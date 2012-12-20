<?php
/*
* 2007-2012 PrestaShop
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
*  @copyright  2007-2012 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

/*
 * PayPal notification fields
 */
define('ID_INVOICE', 'invoice');
define('ID_PAYER', 'payer_id');
define('ID_TRANSACTION', 'txn_id');
define('CURRENCY', 'mc_currency');
define('PAYER_EMAIL', 'payer_email');
define('PAYMENT_DATE', 'payment_date');
define('TOTAL_PAID', 'mc_gross');
define('SHIPPING', 'shipping');
define('VERIFY_SIGN', 'verify_sign');

class PayPalOrder
{
	/*
	 * Get PayPal order data
	 * - ID Order
	 * - ID Transaction
	 * - ID Invoice
	 * - Currency (ISO)
	 * - Total paid
	 * - Shipping
	 * - Capture (bool)
	 * - Payment date
	 * - Payment method (int)
	 * - Payment status
	 */
	
	public static function getTransactionDetails($ppec = false, $payment_status = false)
	{
		if ($ppec && $payment_status)
		{
			return array(
				'currency' => pSQL($ppec->result['PAYMENTINFO_0_CURRENCYCODE']),
				'id_invoice' => null,
				'id_transaction' => pSQL($ppec->result['PAYMENTINFO_0_TRANSACTIONID']),
				'total_paid' => (float)$ppec->result['PAYMENTINFO_0_AMT'],
				'shipping' => (float)$ppec->result['PAYMENTREQUEST_0_SHIPPINGAMT'],
				'payment_date' => pSQL($ppec->result['PAYMENTINFO_0_ORDERTIME']),
				'payment_status' => pSQL($payment_status)
			);
		}
		else
		{
			return array(
				'currency' => pSQL(Tools::getValue(CURRENCY)),
				'id_invoice' => pSQL(Tools::getValue(ID_INVOICE)),
				'id_transaction' => pSQL(Tools::getValue(ID_TRANSACTION)),
				'total_paid' => (float)Tools::getValue(TOTAL_PAID),
				'shipping' => (float)Tools::getValue(SHIPPING),
				'payment_date' => pSQL(Tools::getValue(PAYMENT_DATE)),
				'payment_status' => pSQL($payment_status)
			);
		}
	}
		
	public static function getOrderById($id_order)
	{
		return Db::getInstance()->getRow(
			'SELECT * FROM `'._DB_PREFIX_.'paypal_order`
			WHERE `id_order` = '.(int)$id_order
		);
	}

	public static function getIdOrderByTransactionId($id_transaction)
	{
		return Db::getInstance()->getRow('
			SELECT `id_order`
			FROM `'._DB_PREFIX_.'paypal_order`
			WHERE `id_transaction` = \''.pSQL($id_transaction).'\''
		);
	}

	public static function saveOrder($id_order, $transaction)
	{
		$order = new Order((int)$id_order);
		$total_paid = (float)$transaction['total_paid'];
			
		if (!isset($transaction['payment_status']) || !$transaction['payment_status'])
			$transaction['payment_status'] = 'NULL';

		Db::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_.'paypal_order`
			(`id_order`, `id_transaction`, `id_invoice`, `currency`, `total_paid`, `shipping`, `capture`, `payment_date`, `payment_method`, `payment_status`)
			VALUES ('.(int)$id_order.', \''.pSQL($transaction['id_transaction']).'\', \''.pSQL($transaction['id_invoice']).'\',
				\''.pSQL($transaction['currency']).'\',
				\''.$total_paid.'\',
				\''.(float)$transaction['shipping'].'\',
				\''.(int)Configuration::get('PAYPAL_CAPTURE').'\',
				\''.pSQL($transaction['payment_date']).'\',
				\''.(int)Configuration::get('PAYPAL_PAYMENT_METHOD').'\',
				\''.pSQL($transaction['payment_status']).'\')'
		);
	}
}
