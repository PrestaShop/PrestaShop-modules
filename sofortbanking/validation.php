<?php
/**
 * $Id$
 *
 * sofortbanking Module
 *
 * Copyright (c) 2009 touchdesign
 *
 * @category Payment
 * @version 2.0
 * @copyright 19.08.2009, touchdesign
 * @author Christin Gruber, <www.touchdesign.de>
 * @link http://www.touchdesign.de/loesungen/prestashop/sofortueberweisung.htm
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
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
 *
 */

require dirname(__FILE__).'/../../config/config.inc.php';
require dirname(__FILE__).'/sofortbanking.php';

$order_state = Configuration::get('SOFORTBANKING_OS_ERROR');
$password = Configuration::get('SOFORTBANKING_NOTIFY_PW')
	? Configuration::get('SOFORTBANKING_NOTIFY_PW')
	: Configuration::get('SOFORTBANKING_PROJECT_PW');

$request = array('transaction' => Tools::getValue('transaction'), 'user_id' => Tools::getValue('user_id'),
	'project_id' => Tools::getValue('project_id'), 'sender_holder' => Tools::getValue('sender_holder'),
	'sender_account_number' => Tools::getValue('sender_account_number'), 'sender_bank_code' => Tools::getValue('sender_bank_code'),
	'sender_bank_name' => Tools::getValue('sender_bank_name') , 'sender_bank_bic' => Tools::getValue('sender_bank_bic'),
	'sender_iban' => Tools::getValue('sender_iban'), 'sender_country_id' => Tools::getValue('sender_country_id'),
	'recipient_holder' => Tools::getValue('recipient_holder'), 'recipient_account_number' => Tools::getValue('recipient_account_number'),
	'recipient_bank_code' => Tools::getValue('recipient_bank_code') , 'recipient_bank_name' => Tools::getValue('recipient_bank_name'),
	'recipient_bank_bic' => Tools::getValue('recipient_bank_bic'), 'recipient_iban' => Tools::getValue('recipient_iban'),
	'recipient_country_id' => Tools::getValue('recipient_country_id'), 'international_transaction' => Tools::getValue('international_transaction'),
	'amount' => Tools::getValue('amount'), 'currency_id' => Tools::getValue('currency_id'), 'reason_1' => Tools::getValue('reason_1'),
	'reason_2' => Tools::getValue('reason_2'), 'security_criteria' => Tools::getValue('security_criteria'),
	'user_variable_0' => Tools::getValue('user_variable_0'), 'user_variable_1' => Tools::getValue('user_variable_1'),
	'user_variable_2' => Tools::getValue('user_variable_2'), 'user_variable_3' => Tools::getValue('user_variable_3'),
	'user_variable_4' => Tools::getValue('user_variable_4'), 'user_variable_5' => Tools::getValue('user_variable_5'),
	'created' => Tools::getValue('created'), 'project_password' => $password);

$cart = new Cart((int)Tools::getValue('user_variable_1'));

if (class_exists('Context'))
{
	if (empty(Context::getContext()->link))
		Context::getContext()->link = new Link();
	Context::getContext()->language = new Language($cart->id_lang);
	Context::getContext()->currency = new Currency($cart->id_currency);
}

$sofortbanking = new Sofortbanking();

/* Validate submited post vars */
if (Tools::getValue('hash') != sha1(implode('|', $request)))
	die($sofortbanking->l('Fatal Error (1)'));
elseif (!is_object($cart) || !$cart)
	die($sofortbanking->l('Fatal Error (2)'));
else
	$order_state = Configuration::get('SOFORTBANKING_OS_ACCEPTED');

$customer = new Customer((int)$cart->id_customer);

/* Validate this card in store */
$sofortbanking->validateOrder($cart->id, $order_state, (float)number_format($cart->getOrderTotal(true, 3), 2, '.', ''),
	$sofortbanking->displayName, $sofortbanking->l('Directebanking transaction id: ').Tools::getValue('transaction'),
	null, null, false, $customer->secure_key, null);

?>