<?php
/**
* 2007-2011 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2011 PrestaShop SA
*  @version   Release: $Revision: 7732 $
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/syspay.php');
require_once(dirname(__FILE__).'/tools/loader.php');

	function addSyspayOrderHistory($id_order, $order_state, $use_existings_payment = false) {
		$history = new OrderHistory();
		$history->id_order = $id_order;
		$history->id_employee = (int)Configuration::get('SYSPAY_EMPLOYEE');
		$history->changeIdOrderState($order_state, $id_order, $use_existings_payment);
		$history->id_order_state = $order_state;
		$history->add(true);
	}



	$syspay = new SysPay();

	$mode = Configuration::get('SYSPAY_MODE');
	if ($mode == 0)
		$secrets = array(
			Configuration::get('SYSPAY_TEST_MID') => Configuration::get('SYSPAY_TEST_SHA1_PRIVATE')
		);
	else
		$secrets = array(
			Configuration::get('SYSPAY_LIVE_MID') => Configuration::get('SYSPAY_LIVE_SHA1_PRIVATE')
		);

	$ems = new Syspay_Merchant_EMS($secrets);

	try {
		$event = $ems->getEvent();
		$t = $event->getType();
		switch ($t)
		{
			case 'payment':
				$output = '<br /><br />'.$syspay->l('Received parameters:').'<br /><br />';
				$id_cart = (int)$event->getDescription();
				$cart = new Cart($id_cart);
				if (Validate::isLoadedObject($cart))
				{
					$output .= 'ID: '.$event->getId().'<br />Amount: '.$event->getAmount().'<br />Reason: '.$event->getFailureCategory();
					switch ($event->getStatus())
					{
						case 'SUCCESS':
							/* Payment OK */
							$id_order = Db::getInstance()->getValue('SELECT o.id_order
								FROM '._DB_PREFIX_.'orders o
								LEFT JOIN '._DB_PREFIX_.'syspay_payment sp ON o.id_cart=sp.id_cart
								WHERE sp.id_syspay_payment='.(int)$event->getId());
							$billing_agreement = $event->getBillingAgreement();
							if ($billing_agreement && $billing_agreement->getStatus() != 'CANCELLED' && $billing_agreement->getStatus() != 'ENDED')
							{
								$id_customer = Db::getInstance()->getValue('SELECT id_customer FROM '._DB_PREFIX_.'cart WHERE id_cart='.(int)$id_cart);
								$id_billing_agreement = Db::getInstance()->getValue('SELECT id_billing_agreement 
									FROM '._DB_PREFIX_.'syspay_rebill WHERE id_customer='.(int)$id_customer);
								if (!$id_billing_agreement)
								{
									$sql = 'INSERT INTO '._DB_PREFIX_.'syspay_rebill VALUES('.(int)$billing_agreement->getId().', '.(int)$id_customer.')';
									Db::getInstance()->execute($sql);
								}
							}
							if (!$id_order)
								$syspay->validate($id_cart, Configuration::get('PS_OS_PAYMENT'),
									(float)$event->getAmount() / 100, $syspay->l($event->getStatus()).'<br />'.$output, $event->getReference());
							break;
						case 'CANCELLED':
							$id_order = Db::getInstance()->getValue('SELECT o.id_order
								FROM '._DB_PREFIX_.'orders o
								LEFT JOIN '._DB_PREFIX_.'syspay_payment sp ON o.id_cart=sp.id_cart
								WHERE sp.id_syspay_payment='.(int)$event->getId());
							if ($id_order)
							{
								addSyspayOrderHistory($id_order, Configuration::get('PS_OS_CANCELED'));
							}
						break;
						case 'VOIDED':
							$id_order = Db::getInstance()->getValue('SELECT o.id_order
								FROM '._DB_PREFIX_.'orders o
								LEFT JOIN '._DB_PREFIX_.'syspay_payment sp ON o.id_cart=sp.id_cart
								WHERE sp.id_syspay_payment='.(int)$event->getId());
							if ($id_order)
							{
								addSyspayOrderHistory($id_order, Configuration::get('PS_OS_CANCELED'));
							}
						break;
						case 'FAILED':
							$id_order = Db::getInstance()->getValue('SELECT o.id_order
								FROM '._DB_PREFIX_.'orders o
								LEFT JOIN '._DB_PREFIX_.'syspay_payment sp ON o.id_cart=sp.id_cart
								WHERE sp.id_syspay_payment='.(int)$event->getId());
							if (!$id_order)
								$syspay->validate($id_cart, Configuration::get('PS_OS_ERROR'), 0, $syspay->l($event->getStatus()).'<br />'.$output);
							else
							{
								addSyspayOrderHistory($id_order, Configuration::get('PS_OS_ERROR'));
							}
						break;
						case 'AUTHORIZED':
							$billing_agreement = $event->getBillingAgreement();
							if ($billing_agreement)
							{
								$id_customer = Db::getInstance()->getValue('SELECT id_customer 
									FROM '._DB_PREFIX_.'cart WHERE id_cart='.(int)$id_cart);
								$id_billing_agreement = Db::getInstance()->getValue('SELECT id_billing_agreement 
									FROM '._DB_PREFIX_.'syspay_rebill WHERE id_customer='.(int)$id_customer);
								if (!$id_billing_agreement)
								{
									$sql = 'INSERT INTO '._DB_PREFIX_.'syspay_rebill VALUES('.(int)$billing_agreement->getId().', '.(int)$id_customer.')';
									Db::getInstance()->execute($sql);
								}
							}
							/* PAIEMENT DIFFERE */
							$syspay->validate($id_cart, Configuration::get('PS_OS_SYSPAY_AUTHORIZED'),
								(float)$event->getAmount() / 100, $syspay->l($event->getStatus()).'<br />'.$output, $event->getReference());
							break;
						default:
							$syspay->validate($id_cart, Configuration::get('PS_OS_ERROR'),
								(float)$event->getAmount() / 100, $syspay->l('Unknown status:').' '.$event->getStatus().$output);
						break;
					}

				}
				break;
			case 'refund':
					if ($event->getStatus() != 'SUCCESS')
						break;
					$id_order = Db::getInstance()->getValue('SELECT o.id_order
						FROM '._DB_PREFIX_.'orders o
						LEFT JOIN '._DB_PREFIX_.'syspay_payment sp ON o.id_cart=sp.id_cart
						WHERE sp.id_syspay_payment='.(int)$event->getPayment()->getId());
					$order = new Order($id_order);
					if (!$order)
						break;
					if (!Validate::isLoadedObject($order))
						return false;
					$res = Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'syspay_refund VALUES('.(int)$event->getId().', '.(int)$id_order.')');
					if (version_compare(_PS_VERSION_, '1.5', '>='))
					{
						$order_reference = Order::getUniqReferenceOf($id_order);
						$order_values = Db::getInstance()->getRow('SELECT op.*
							FROM '._DB_PREFIX_.'order_payment op
							WHERE op.order_reference LIKE "'.$order_reference.'" ORDER BY id_order_payment ASC');
						$order_has_invoice = $order->hasInvoice();
						if ($order_has_invoice)
						{
							$id_order_invoice = Db::getInstance()->getValue('SELECT id_order_invoice 
								FROM '._DB_PREFIX_.'order_invoice WHERE id_order='.(int)$id_order.' ORDER BY date_add ASC');
							$order_invoice = new OrderInvoice($id_order_invoice);
						}
						else
							$order_invoice = null;
						if (!$order->addOrderPayment(((($event->getAmount()) / 100) * -1), $order_values['payment_method'],
							$order_values['transaction_id'], null, null, $order_invoice))
							return false;
					}

					if (version_compare(_PS_VERSION_, '1.5', '<'))
						$current_state = $order->getCurrentState();
					else
						$current_state = $order->getCurrentOrderState()->id;
					if ($current_state == Configuration::get('PS_OS_DELIVERED'))
						$order_state = new OrderState((int)Configuration::get('PS_OS_SYSPAY_REFUND_DELIVERED'));
					elseif ($current_state == Configuration::get('PS_OS_PREPARATION'))
						$order_state = new OrderState((int)Configuration::get('PS_OS_SYSPAY_REFUND_PIP'));
					elseif ($current_state == Configuration::get('PS_OS_SHIPPED'))
						$order_state = new OrderState((int)Configuration::get('PS_OS_SYSPAY_REFUND_SHIPPED'));
					else
						$order_state = new OrderState((int)Configuration::get('PS_OS_REFUND'));
					if ($res)
					{
						if (!Validate::isLoadedObject($order_state))
							return false;
						if ($current_state != $order_state->id)
						{
							$use_existings_payment = false;
							if (!$order->hasInvoice())
								$use_existings_payment = true;
							addSyspayOrderHistory($order->id, (int)$order_state->id, $use_existings_payment);
						}
					}
				break;
			case 'chargeback':
				if ($event->getStatus() == 'SUCCESS')
				{
					$payment = $event->getPayment();
					$sql = 'SELECT id_cart FROM '._DB_PREFIX_.'syspay_payment WHERE id_syspay_payment='.(int)$payment->getId();
					$id_cart = Db::getInstance()->getValue($sql);
					if ($id_cart)
						$order = new Order(Order::getOrderByCartId($id_cart));
					if (!isset($order) || !$order)
						break;
					else
					{
						if (version_compare(_PS_VERSION_, '1.5', '<'))
							$current_order_state = $order->getCurrentState();
						else
							$current_order_state = $order->getCurrentOrderState()->id;
						if ($current_order_state == Configuration::get('PS_OS_PREPARATION'))
							$order_state = new OrderState((int)Configuration::get('PS_OS_SYSPAY_CB_PIP'));
						elseif ($current_order_state == Configuration::get('PS_OS_SHIPPED'))
							$order_state = new OrderState((int)Configuration::get('PS_OS_SYSPAY_CB_SHIPPED'));
						elseif ($current_order_state == Configuration::get('PS_OS_DELIVERED'))
							$order_state = new OrderState((int)Configuration::get('PS_OS_SYSPAY_CB_DELIVERED'));
						else
							$order_state = new OrderState((int)Configuration::get('PS_OS_SYSPAY_CB'));
						if (!Validate::isLoadedObject($order_state))
							break;

						if ($current_order_state != $order_state->id)
						{
							$use_existings_payment = false;
							if (!$order->hasInvoice())
								$use_existings_payment = true;
							addSyspayOrderHistory($order->id, $order_state->id, $use_existings_payment);
							$order->valid = false;
							$order->update();
						}
					}
				}
				break;
			case 'billing_agreement':
				if ($event->getStatus() == 'ENDED' || $event->getStatus() == 'CANCELLED')
				{
					$sql = 'DELETE FROM '._DB_PREFIX_.'syspay_rebill WHERE id_billing_agreement='.(int)$event->getId().' LIMIT 1';
					Db::getInstance()->execute($sql);
				}
				break;
		}
	} catch (Syspay_Merchant_EMSException $e) {
		header(':', true, 500);
		printf('Something went wrong while processing the message: (%d) %s',
					$e->getCode(), $e->getMessage());
	}
