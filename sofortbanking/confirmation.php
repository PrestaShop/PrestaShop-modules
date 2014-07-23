<?php
/**
 * sofortbanking Module
 *
 * Copyright (c) 2009 touchdesign
 *
 * @category  Payment
 * @author    Christin Gruber, <www.touchdesign.de>
 * @copyright 19.08.2009, touchdesign
 * @link      http://www.touchdesign.de/loesungen/prestashop/sofortueberweisung.htm
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * Description:
 *
 * Payment module sofortbanking
 *
 * --
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@touchdesign.de so we can send you a copy immediately.
 */

require dirname(__FILE__).'/../../config/config.inc.php';
require dirname(__FILE__).'/sofortbanking.php';

$order_state = Configuration::get('SOFORTBANKING_OS_ERROR');
$cart = new Cart((int)Tools::getValue('user_variable_1'));

if (class_exists('Context'))
{
	if (empty(Context::getContext()->link))
		Context::getContext()->link = new Link();
	Context::getContext()->language = new Language($cart->id_lang);
	Context::getContext()->currency = new Currency($cart->id_currency);
}

$sofortbanking = new Sofortbanking();

/* If valid hash, set order state as accepted */
if (is_object($cart) && Tools::getValue('hash') == sha1(Tools::getValue('user_variable_1').Configuration::get('SOFORTBANKING_PROJECT_PW')))
	$order_state = Configuration::get('SOFORTBANKING_OS_ACCEPTED');

$customer = new Customer((int)$cart->id_customer);

/* Validate this card in store if needed */
if (!Order::getOrderByCartId($cart->id) && ($order_state == Configuration::get('SOFORTBANKING_OS_ACCEPTED')
	|| $order_state == Configuration::get('SOFORTBANKING_OS_ERROR')))
{
	$sofortbanking->validateOrder($cart->id, $order_state, (float)number_format($cart->getOrderTotal(true, 3), 2, '.', ''),
		$sofortbanking->displayName, null, null, null, false, $customer->secure_key, null);
	Configuration::updateValue('SOFORTBANKING_CONFIGURATION_OK', true);
}

$order_id = Order::getOrderByCartId($cart->id);

$order = new Order($order_id);

/* Init Frontend variables for redirect */
$controller = new FrontController();
$controller->init();

if (version_compare(_PS_VERSION_, '1.5', '>='))
	Tools::redirect('index.php?controller=order-confirmation&id_cart='.$order->id_cart
		.'&id_module='.$sofortbanking->id.'&id_order='.$order_id
		.'&key='.$order->secure_key);
else
	Tools::redirect('order-confirmation.php?id_cart='.$order->id_cart
		.'&id_module='.$sofortbanking->id.'&id_order='.$order_id
		.'&key='.$order->secure_key);

?>