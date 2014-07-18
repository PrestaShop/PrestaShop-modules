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
require_once(dirname(__FILE__).'/tools/syspay_tools.php');


	$payment_type = array(0 => 'DIRECT', 1 => 'DEFERRED', 2 => 'BA');
	$type_of_payment = $payment_type[0];


	if (Configuration::get('SYSPAY_MODE') == 0)
	{
		$mode = Syspay_Merchant_Client::BASE_URL_SANDBOX;
		$mid = Configuration::get('SYSPAY_TEST_MID');
		$pass = Configuration::get('SYSPAY_TEST_SHA1_PRIVATE');
	}
	else
	{
		$mode = Syspay_Merchant_Client::BASE_URL_PROD;
		$mid = Configuration::get('SYSPAY_LIVE_MID');
		$pass = Configuration::get('SYSPAY_LIVE_SHA1_PRIVATE');
	}
	if (!Tools::isSubmit('rebill'))
	{
		$client = new Syspay_Merchant_Client($mid, $pass, $mode);
		$payment_request = new Syspay_Merchant_PaymentRequest(Syspay_Merchant_PaymentRequest::FLOW_BUYER);
		$payment_request->setMode(Syspay_Merchant_PaymentRequest::MODE_ONLINE);
		$website = SyspayTools::getWebsite(true, false);
		if (Configuration::get('SYSPAY_WEBSITE_ID'))
			$payment_request->setWebsite((int)Configuration::get('SYSPAY_WEBSITE_ID'));
		$payment_request->setEmsUrl($website._MODULE_DIR_.'syspay/ems.php');
		$payment_request->setRedirectUrl($website._MODULE_DIR_.'syspay/confirmation.php');

		if (Configuration::get('SYSPAY_REBILL') == 1 && Tools::getValue('SP_REBILL') == 'on')
		{
			$type_of_payment = $payment_type[2];
			$payment_request->setBillingAgreement(true);
		}

		$customer = new Syspay_Merchant_Entity_Customer();
		$customer->setEmail(Tools::getValue('email'));
		$customer->setLanguage(Tools::strtolower(Tools::getValue('language')));
		$payment_request->setCustomer($customer);

		$payment = new Syspay_Merchant_Entity_Payment();
		$payment->setReference(Tools::getValue('order_ref'));
		if (Configuration::get('SYSPAY_AUTHORIZED_PAYMENT') == 1 && (
				Configuration::get('SYSPAY_REBILL') == 0 || (Configuration::get('SYSPAY_REBILL') == 1 && Tools::getValue('SP_REBILL') != 'on')
			))
		{
			$type_of_payment = $payment_type[1];
			$payment->setPreauth(true);
		}
		$payment->setAmount(Tools::getValue('amount') * 100);
		$payment->setCurrency(Tools::getValue('currency'));
		$payment->setDescription(Tools::getValue('extra'));
		$payment->setExtra(Tools::jsonEncode(Tools::getValue('extra')));
		$payment_request->setPayment($payment);
		$sql = 'SELECT redirect_url FROM '._DB_PREFIX_.'syspay_payment WHERE order_ref = "'.Tools::getValue('order_ref').'"';
		$result = Db::getInstance()->getRow($sql);
		if ($result)
		{
			Tools::redirect($result['redirect_url']);
			return;
		}
		try {
			$payment = $client->request($payment_request);
		} catch (Syspay_Merchant_RequestException $s) {
			$website = SyspayTools::getWebsite(true, false);
			if (version_compare(_PS_VERSION_, '1.5', '>='))
				Tools::redirect('/index.php?controller=order&step=3&err=1');
			else
				Tools::redirectLink($website.'/order.php?step=3&err=1');
			return false;
		} catch (Syspay_Merchant_UnexpectedResponseException $s) {
			$website = SyspayTools::getWebsite(true, false);
			if (version_compare(_PS_VERSION_, '1.5', '>='))
				Tools::redirect('/index.php?controller=order&step=3&err=1');
			else
				Tools::redirectLink($website.'/order.php?step=3&err=1');
			return false;
		}
	}
	elseif (Tools::isSubmit('rebill'))
	{
		$client = new Syspay_Merchant_Client($mid, $pass, $mode);
		$id_rebill = Tools::getValue('rebill');
		if (version_compare(_PS_VERSION_, '1.5', '>='))
		{
			$customer = Customer::getCustomersByEmail(Tools::getValue('email'));
			$id = SyspayTools::getIdsRebillByIdCustomer($customer[0]['id_customer']);
		}
		else
		{
			$customer = new Customer();
			$customer = $customer->getByEmail(Tools::getValue('email'));
			$id = SyspayTools::getIdsRebillByIdCustomer($customer->id);
		}
		if ($id_rebill != $id)
		{
			$website = SyspayTools::getWebsite(true, false);
			if (version_compare(_PS_VERSION_, '1.5', '>='))
				Tools::redirect('/index.php?controller=order&step=3&err=1');
			else
				Tools::redirectLink($website.'/order.php?step=3&err=1');
			return;
		}
		$type_of_payment = $payment_type[2];
		$rebill_request = new Syspay_Merchant_RebillRequest($id);
		$rebill_request->setAmount(Tools::getValue('amount') * 100);
		$rebill_request->setCurrency(Tools::getValue('currency'));
		$rebill_request->setReference(Tools::getValue('order_ref'));
		$website = SyspayTools::getWebsite(true, false);
		$rebill_request->setEmsUrl($website._MODULE_DIR_.'syspay/ems.php');
		$rebill_request->setDescription(Tools::getValue('extra'));
		$rebill_request->setExtra(Tools::jsonEncode(Tools::getValue('extra')));
		try {
			$payment = $client->request($rebill_request);
		} catch (Syspay_Merchant_RequestException $s) {
			$website = SyspayTools::getWebsite(true, false);
			if (version_compare(_PS_VERSION_, '1.5', '>='))
				Tools::redirect('/index.php?controller=order&step=3&err=1');
			else
				Tools::redirectLink($website.'/order.php?step=3&err=1');
			return false;
		} catch (Syspay_Merchant_UnexpectedResponseException $s) {
			$website = SyspayTools::getWebsite(true, false);
			if (version_compare(_PS_VERSION_, '1.5', '>='))
				Tools::redirect('/index.php?controller=order&step=3&err=1');
			else
				Tools::redirectLink($website.'/order.php?step=3&err=1');
			return false;
		}
	}
	else
	{
		$website = SyspayTools::getWebsite(true, false);
		if (version_compare(_PS_VERSION_, '1.5', '>='))
			Tools::redirect('/index.php?controller=order&step=3&err=1');
		else
			Tools::redirectLink($website.'/order.php?step=3&err=1');
		return;
	}
	if ($payment)
	{
		$status = $payment->getStatus();
		if ($status == 'OPEN')
		{
			$sql = 'INSERT INTO '._DB_PREFIX_.'syspay_payment VALUES('.(int)$payment->getId().', '.$payment->getDescription().', 
				"'.Tools::getValue('order_ref').'", "'.$payment->getRedirect().'", "'.$type_of_payment.'")';
			Db::getInstance()->Execute($sql);
			if (version_compare(_PS_VERSION_, '1.5', '>='))
				Tools::redirect($payment->getRedirect());
			else
				Tools::redirectLink($payment->getRedirect());
			return;
		}
		elseif ($status == 'SUCCESS')
		{
			$sql = 'INSERT INTO '._DB_PREFIX_.'syspay_payment VALUES('.(int)$payment->getId().', '.$payment->getDescription().', 
				"'.Tools::getValue('order_ref').'", "'.$payment->getRedirect().'", "'.$type_of_payment.'")';
			Db::getInstance()->Execute($sql);
			$params = array('result' => base64_encode(Tools::jsonEncode($client->getData())), 'merchant' => $client->getUsername());
			$params['checksum'] = Syspay_Merchant_Utils::getChecksum($params['result'], $client->getSecret());
			$website = SyspayTools::getWebsite(true, false);
			$redirect = sprintf($website._MODULE_DIR_.'syspay/confirmation.php?%s', http_build_query($params));
			if (version_compare(_PS_VERSION_, '1.5', '>='))
				Tools::redirect($redirect);
			else
				Tools::redirectLink($redirect);
			return;
		}
		else
		{
			$website = SyspayTools::getWebsite(true, false);
			if (version_compare(_PS_VERSION_, '1.5', '>='))
				Tools::redirect('/index.php?controller=order&step=3&err=1');
			else
				Tools::redirectLink($website.'/order.php?step=3&err=1');
			return;
		}
	}
