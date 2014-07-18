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

class   SyspayTools {
	public static function updateSetting()
	{
		Configuration::updateValue('SYSPAY_LIVE_MID', Tools::getValue('SYSPAY_LIVE_MID'));
		Configuration::updateValue('SYSPAY_LIVE_SHA1_PRIVATE', Tools::getValue('SYSPAY_LIVE_SHA1_PRIVATE'));
		Configuration::updateValue('SYSPAY_TEST_MID', Tools::getValue('SYSPAY_TEST_MID'));
		Configuration::updateValue('SYSPAY_TEST_SHA1_PRIVATE', Tools::getValue('SYSPAY_TEST_SHA1_PRIVATE'));
		Configuration::updateValue('SYSPAY_MODE', (int)Tools::getValue('SYSPAY_MODE'));
		Configuration::updateValue('SYSPAY_ERRORS', (int)Tools::getValue('SYSPAY_ERRORS'));
		Configuration::updateValue('SYSPAY_CAPTURE_OS', (int)Tools::getValue('SYSPAY_CAPTURE_OS'));
		Configuration::updateValue('SYSPAY_AUTHORIZED_PAYMENT', (int)Tools::getValue('SYSPAY_AUTHORIZED_PAYMENT'));
		Configuration::updateValue('SYSPAY_REBILL', (int)Tools::getValue('SYSPAY_REBILL'));
		Configuration::updateValue('SYSPAY_WEBSITE_ID', Tools::getValue('SYSPAY_WEBSITE_ID'));
	}

	public static function assignVars()
	{
		$technical_settings = array();
		$technical_settings['SYSPAY_LIVE_MID'] = Configuration::get('SYSPAY_LIVE_MID');
		$technical_settings['SYSPAY_LIVE_SHA1_PRIVATE'] = Configuration::get('SYSPAY_LIVE_SHA1_PRIVATE');
		$technical_settings['SYSPAY_TEST_MID'] = Configuration::get('SYSPAY_TEST_MID');
		$technical_settings['SYSPAY_TEST_SHA1_PRIVATE'] = Configuration::get('SYSPAY_TEST_SHA1_PRIVATE');
		$technical_settings['SYSPAY_MODE'] = Configuration::get('SYSPAY_MODE');
		$technical_settings['SYSPAY_ERRORS'] = Configuration::get('SYSPAY_ERRORS');
		$technical_settings['SYSPAY_CAPTURE_OS'] = Configuration::get('SYSPAY_CAPTURE_OS');
		$technical_settings['SYSPAY_AUTHORIZED_PAYMENT'] = Configuration::get('SYSPAY_AUTHORIZED_PAYMENT');
		$technical_settings['SYSPAY_REBILL'] = Configuration::get('SYSPAY_REBILL');
		$technical_settings['SYSPAY_WEBSITE_ID'] = Configuration::get('SYSPAY_WEBSITE_ID');
		$technical_settings['formTarget'] = Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']);
		$context = Context::getContext();
		$context->smarty->assign('settings', $technical_settings);
	}

	public static function assignOrderStates()
	{
		$context = Context::getContext();
		$order_states = OrderState::getOrderStates($context->cookie->id_lang);
		$states_list = array(
			Configuration::get('PS_OS_BANKWIRE'),
			Configuration::get('PS_OS_CANCELED'),
			Configuration::get('PS_OS_CHEQUE'),
			Configuration::get('PS_OS_ERROR'),
			Configuration::get('PS_OS_OUTOFSTOCK'),
			Configuration::get('PS_OS_PAYPAL'),
			Configuration::get('PS_OS_REFUND'),
			Configuration::get('PS_OS_SYSPAY'),
			Configuration::get('PS_OS_SYSPAY_AUTHORIZED'),
			Configuration::get('PS_OS_SYSPAY_CB'),
			Configuration::get('PAYPAL_OS_AUTHORIZATION'),
			Configuration::get('PS_OS_WS_PAYMENT')
		);
		$states = array();
		foreach ($order_states as $os)
		{
			if (!in_array($os['id_order_state'], $states_list))
				$states[] = $os;
		}
		if ($states)
			$context->smarty->assign('states', $states);
	}

	public static function isLuhnNum($num, $length = null)
	{
		if (empty($length))
			$length = Tools::strlen($num);
		$tot = 0;
		for ($i = $length - 1; $i >= 0; $i--)
		{
			$digit = Tools::substr($num, $i, 1);
			if ((($length - $i) % 2) == 0)
			{
				$digit = $digit * 2;
				if ($digit > 9)
					$digit = $digit - 9;
			}
			$tot += $digit;
		}
		return (($tot % 10) == 0);
	}


	public static function getLuhnNumNow()
	{
		$card_number = str_pad('4'.rand('10', '99').date('U'), 15, '0', STR_PAD_RIGHT);
		for ($i = 0; $i < 10; $i++)
		{
			if (self::isLuhnNum($card_number.$i))
				return $card_number.$i;
		}
		return null;
	}

	public static function processGenerateCb()
	{
		Context::getContext()->smarty->assign('cbtest', self::getLuhnNumNow());
		return true;
	}

	public static function getIdsRebillByIdCustomer($id_customer)
	{
		$sql = 'SELECT id_billing_agreement FROM '._DB_PREFIX_.'syspay_rebill WHERE id_customer='.$id_customer;
		return Db::getInstance()->getValue($sql);
	}

	public static function getRebillCardsByIdCustomer($id_customer)
	{
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

		$id = self::getIdsRebillByIdCustomer($id_customer);
		if (!$id)
			return false;

		$client = new Syspay_Merchant_Client($mid, $pass, $mode);
		$ba_request = new Syspay_Merchant_BillingAgreementInfoRequest($id);
		try
		{
			$ba = $client->request($ba_request);
		}
		catch (Syspay_Merchant_RequestException $s)
		{
			return false;
		}
		catch (Syspay_Merchant_UnexpectedResponseException $s)
		{
			return false;
		}
		if (!$ba)
			return false;
		if ($ba->getStatus() != 'ACTIVE')
			return false;
		$payment_method = $ba->getPaymentMethod();
		return array('id' => $id, 'display' => $payment_method->getDisplay());
	}

	public static function processExportPayments()
	{
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

		$client = new Syspay_Merchant_Client($mid, $pass, $mode);
		if (Tools::getValue('from'))
		{
			if (strpos(Tools::getValue('from'), '/'))
			{
				$tab = explode('/', Tools::getValue('from'));
				$ts_from = $tab[1].'-'.$tab[0].'-'.$tab[2];
			}
			else
			{
				$tab = explode('-', Tools::getValue('from'));
				$ts_from = $tab[10].'-'.$tab[1].'-'.$tab[2];
			}
		}
		if (Tools::getValue('to'))
		{
			if (strpos(Tools::getValue('to'), '/'))
			{
				$tab = explode('/', Tools::getValue('to'));
				$ts_to = $tab[1].'-'.$tab[0].'-'.$tab[2];
			}
			else
			{
				$tab = explode('-', Tools::getValue('to'));
				$ts_to = $tab[10].'-'.$tab[1].'-'.$tab[2];
			}
		}
		$payment_list_request = new Syspay_Merchant_PaymentListRequest();
		if (isset($ts_from))
			$payment_list_request->addFilter('start_date', strtotime($ts_from));
		if (isset($ts_to))
			$payment_list_request->addFilter('end_date', strtotime($ts_to));
		if ($client)
		{
			try
			{
				$payments = $client->request($payment_list_request);
			}
			catch (Syspay_Merchant_RequestException $s)
			{
				return false;
			}
			catch (Syspay_Merchant_UnexpectedResponseException $s)
			{
				return false;
			}

			$fd = fopen(dirname(__FILE__).'/export_payments.csv', 'w');
			if ($payments)
			{
				$plist = array();
				foreach ($payments as $p)
				{
					$tmp = array();
					$tmp['id'] = $p->getId();
					$tmp['reference'] = $p->getReference();
					$tmp['amount'] = number_format($p->getAmount() / 100, 2);
					$tmp['status'] = $p->getStatus();
					$tmp['currency'] = $p->getCurrency();
					$plist[] = $tmp;
				}
				if (isset($plist))
				{
					$str = "ID;REFERENCE;AMOUNT;CURRENCY;STATUS\r\n";
					foreach ($plist as $p)
						$str .= $p['id'].';'.$p['reference'].';'.$p['amount'].';'.$p['currency'].';'.$p['status']."\r\n";
				}
			}
			else
				$str = 'No payments for the selected period.';
			fwrite($fd, $str);
			fclose($fd);
			$website = SyspayTools::getWebsite(true, false);
			if (version_compare(_PS_VERSION_, '1.5', '>='))
				Tools::redirect($website._MODULE_DIR_.'syspay/tools/export_payments.csv');
			else
				Tools::redirectLink($website._MODULE_DIR_.'syspay/tools/export_payments.csv');
		}
	}

	public static function processExportRefunds()
	{
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

		$client = new Syspay_Merchant_Client($mid, $pass, $mode);
		$refund_list_request = new Syspay_Merchant_RefundListRequest();
		if ($client)
		{
			try
			{
				$refunds  = $client->request($refund_list_request);
			}
			catch (Syspay_Merchant_RequestException $s)
			{
				return false;
			}
			catch (Syspay_Merchant_UnexpectedResponseException $s)
			{
				return false;
			}

			$fd = fopen(dirname(__FILE__).'/export_refunds.csv', 'w');
			if ($refunds)
			{
				$rlist = array();
				foreach ($refunds as $r)
				{
					$tmp = array();
					$tmp['id'] = $r->getId();
					$tmp['reference'] = $r->getReference();
					$tmp['amount'] = number_format($r->getAmount() / 100, 2);
					$tmp['status'] = $r->getStatus();
					$tmp['currency'] = $r->getCurrency();
					$rlist[] = $tmp;
				}
				if (isset($rlist))
				{
					$str = "ID;REFERENCE;AMOUNT;CURRENCY;STATUS\r\n";
					foreach ($rlist as $r)
						$str .= $r['id'].';'.$r['reference'].';'.$r['amount'].';'.$r['currency'].';'.$r['status']."\r\n";
				}
			}
			else
				$str = 'No refunds.';
			fwrite($fd, $str);
			fclose($fd);
			$website = SyspayTools::getWebsite(true, false);
			if (version_compare(_PS_VERSION_, '1.5', '>='))
				Tools::redirect($website._MODULE_DIR_.'syspay/tools/export_refunds.csv');
			else
				Tools::redirectLink($website._MODULE_DIR_.'syspay/tools/export_refunds.csv');
		}
	}

	public static function getPaymentInformations($id_order)
	{
		$sql = '
			SELECT sp.id_syspay_payment, sp.type
			FROM '._DB_PREFIX_.'syspay_payment sp
			LEFT JOIN '._DB_PREFIX_.'orders o ON o.id_cart=sp.id_cart
			WHERE o.id_order='.$id_order;
		$res = Db::getInstance()->getRow($sql);
		if ($res)
			return array('id' => $res['id_syspay_payment'], 'type' => $res['type']);
		return false;
	}

	public static function assignPaymentDetails($id)
	{
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			$ind = AdminController::$currentIndex;
			Context::getContext()->smarty->assign('currentIndex', $ind);
		}

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

		try
		{
			$client = new Syspay_Merchant_Client($mid, $pass, $mode);
			$info_request = new Syspay_Merchant_PaymentInfoRequest($id);
			$payment = $client->request($info_request);
		}
		catch (Syspay_Merchant_RequestException $s)
		{
			return false;
		}
		catch (Syspay_Merchant_UnexpectedResponseException $s)
		{
			return false;
		}

		if ($payment)
		{
			$info_payment = array();
			$info_payment['id'] = $payment->getId();
			$info_payment['reference'] = $payment->getReference();
			$info_payment['amount'] = number_format($payment->getAmount() / 100, 2);
			$info_payment['currency'] = $payment->getCurrency();
			$info_payment['status'] = $payment->getStatus();
			$date = $payment->getProcessingTime();
			$info_payment['pt'] = ($date ? $date->format('d-m-Y H:i:s'):'');
			if ($payment->getStatus() == 'AUTHORIZED')
				Context::getContext()->smarty->assign('show_btn', 1);
			if ($payment->getStatus() == 'SUCCESS')
				Context::getContext()->smarty->assign('show_refund', 1);
			Context::getContext()->smarty->assign('info_payment', $info_payment);
		}
	}

	public static function assignRefundsDetails($order)
	{
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

		$client = new Syspay_Merchant_Client($mid, $pass, $mode);
		$ids_syspay_refund = Db::getInstance()->executeS('
				SELECT sr.id_syspay_refund
				FROM '._DB_PREFIX_.'syspay_refund sr
				WHERE sr.id_order='.$order->id
		);

		if ($ids_syspay_refund && !empty($ids_syspay_refund))
		{
			$tab_refunds = array();
			$sum_refunds = 0;
			foreach ($ids_syspay_refund as $id)
			{
				$info_request = new Syspay_Merchant_RefundInfoRequest($id['id_syspay_refund']);
				try
				{
					$refund = $client->request($info_request);
				}
				catch (Syspay_Merchant_RequestException $s)
				{
					return false;
				}
				catch (Syspay_Merchant_UnexpectedResponseException $s)
				{
					return false;
				}

				$tmp = array();
				$tmp['id'] = $refund->getId();
				$tmp['reference'] = $refund->getReference();
				$tmp['amount'] = number_format($refund->getAmount() / 100, 2);
				$sum_refunds += $tmp['amount'];
				$tmp['currency'] = $refund->getCurrency();
				$tmp['status'] = $refund->getStatus();
				$date = $refund->getProcessingTime();
				$tmp['pt'] = $date->format('d-m-Y H:i:s');
				$tmp['description'] = $refund->getDescription();
				$tmp['payment_id'] = $refund->getPayment()->getId();
				$tab_refunds[] = $tmp;
			}
			Context::getContext()->smarty->assign('info_refund', $tab_refunds);
		}
	}

	public static function processAdminForms($order, $id)
	{
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

		$client = new Syspay_Merchant_Client($mid, $pass, $mode);
		$info_request = new Syspay_Merchant_PaymentInfoRequest($id);
		try
		{
			$payment = $client->request($info_request);
		}
		catch (Syspay_Merchant_RequestException $s)
		{
			return false;
		}
		catch (Syspay_Merchant_UnexpectedResponseException $s)
		{
			return false;
		}

		if (!$payment)
			return false;
		if (Tools::getValue('sp_cancel_payment') == 2)
		{
			if (version_compare(_PS_VERSION_, '1.5', '<'))
				$current_state = $order->getCurrentState();
			else
				$current_state = $order->getCurrentOrderState()->id;
			if ($current_state == Configuration::get('PS_OS_SYSPAY_AUTHORIZED') && $payment->getStatus() == 'AUTHORIZED')
			{
				$void_request = new Syspay_Merchant_VoidRequest();
				$void_request->setPaymentId($id); // Returned to you on the initial payment request
				$client->request($void_request);
				if (version_compare(_PS_VERSION_, '1.5', '<'))
					Tools::redirectAdmin(AdminController::$currentIndex.'&vieworder&id_order='.$order->id.'&token='.Tools::getAdminTokenLite('AdminOrders'));
				else
					Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminOrders', true).'&vieworder&id_order='.$order->id);
			}
		}
		if (Tools::getValue('refund_form') == 2)
		{
			$ids_syspay_refund = Db::getInstance()->executeS('
				SELECT sr.id_syspay_refund
				FROM '._DB_PREFIX_.'syspay_refund sr
				WHERE sr.id_order='.$order->id
			);
			$original_payment = Db::getInstance()->getValue('
				SELECT sp.id_syspay_payment
				FROM '._DB_PREFIX_.'syspay_payment sp
				LEFT JOIN '._DB_PREFIX_.'orders o ON o.id_cart=sp.id_cart
				WHERE o.id_order='.$order->id
			);
			$payment_ir = new Syspay_Merchant_PaymentInfoRequest($original_payment);
			try
			{
				$payment_info = $client->request($payment_ir);
			}
			catch (Syspay_Merchant_RequestException $s)
			{
				return false;
			}
			catch (Syspay_Merchant_UnexpectedResponseException $s)
			{
				return false;
			}

			if (!$payment_info)
				return false;
			$total_paid = $payment_info->getAmount();
			$total_paid = $total_paid / 100;
			$total = 0;
			foreach ($ids_syspay_refund as $id)
			{
				$refund_ir = new Syspay_Merchant_RefundInfoRequest($id['id_syspay_refund']);
				$refund_info = $client->request($refund_ir);
				if (!$refund_info)
					return false;
				$amount = $refund_info->getAmount();
				$amount = $amount / 100;
				$total += $amount;
			}
			$val = Tools::getValue('refund_value');
			if ($val == '')
				return false;
			$val = str_replace(',', '.', $val);
			$val = number_format($val, 2, '.', '');
			$refund_value = $val * 100;
			if (($val + $total) > $total_paid)
				return false;
			$refund = new Syspay_Merchant_Entity_Refund();
			$refund->setReference(count($ids_syspay_refund).$payment->getReference()); // Your own reference for this refund
			$refund->setAmount($refund_value); // The amount to refund in *cents*
			$refund->setCurrency($payment->getCurrency()); // The currency of the refund. It must match the one of the original payment
			$refund->setDescription(Tools::getValue('refund_reason')); // An optional description for this refund
			$refund_request = new Syspay_Merchant_RefundRequest();
			$refund_request->setPaymentId($original_payment); // The payment id to refund
			$website = SyspayTools::getWebsite(true, false);
			$refund_request->setEmsUrl($website._MODULE_DIR_.'syspay/ems.php');
			$refund_request->setRefund($refund);
			try
			{
				$refund = $client->request($refund_request);
			}
			catch (Syspay_Merchant_RequestException $s)
			{
				return false;
			}
			catch (Syspay_Merchant_UnexpectedResponseException $s)
			{
				return false;
			}

				if (version_compare(_PS_VERSION_, '1.5', '<'))
					Tools::redirectAdmin(AdminController::$currentIndex.'&vieworder&id_order='.$order->id.'&token='.Tools::getAdminTokenLite('AdminOrders'));
				else
					Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminOrders', true).'&vieworder&id_order='.$order->id);

		}
	}

	public static function getWebsite($http = false, $entities = false)
	{
		if (method_exists('Tools', 'getShopDomainSsl'))
			return Tools::getShopDomainSsl($http, $entities);
		else
		{
			if (!($domain = Configuration::get('PS_SHOP_DOMAIN_SSL')))
				$domain = Tools::getHttpHost();
			if ($entities)
				$domain = htmlspecialchars($domain, ENT_COMPAT, 'UTF-8');
			if ($http)
				$domain = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$domain;
			return $domain;
		}
	}
}