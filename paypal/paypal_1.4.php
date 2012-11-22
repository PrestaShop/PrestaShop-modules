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

class PayPal extends PayPalAbstract
{
	public function validateOrder($id_cart, $id_order_state, $amountPaid, $paymentMethod = 'Unknown', $message = null, $transaction = array(), $currency_special = null, $dont_touch_amount = false, $secure_key = false)
	{
		if ($this->active)
		{
			// Set transaction details if pcc is defined in PaymentModule class_exists
			if (isset($this->pcc))
				$this->pcc->transaction_id = (isset($transaction['transaction_id']) ? $transaction['transaction_id'] : '');

			parent::validateOrder((int)$id_cart, (int)$id_order_state, (float)$amountPaid, $paymentMethod, $message, $transaction, $currency_special, $dont_touch_amount, $secure_key);

			if (count($transaction) > 0)
				PayPalOrder::saveOrder((int)$this->currentOrder, $transaction);
		}
	}
}
