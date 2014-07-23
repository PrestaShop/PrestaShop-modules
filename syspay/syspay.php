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

if (!defined('_PS_VERSION_'))
	exit;

class SysPay extends PaymentModule {
	private $ignore_key_list = array();

	public function __construct()
	{
		$this->name = 'syspay';
		$this->tab = 'payments_gateways';
		$this->version = '2.0.1';
		$this->author = 'SysPay';

		parent::__construct();

		$this->displayName = '123syspay.com';
		$this->description = $this->l('Bill the world ! No merchant account needed. Immediate activation. 2500e free processing to test us !');
		if (version_compare(_PS_VERSION_, '1.6', '>='))
			$this->bootstrap = true;

	/* Backward compatibility */
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
	}

	public function install()
	{
		include_once(_PS_MODULE_DIR_.$this->name.'/tools/install_tools.php');
		if (!SyspayInstallTools::setOrderState())
			return false;
		if (!SyspayInstallTools::setEmployee())
			return false;
		SyspayInstallTools::setConfigurationValue();
		if (!SyspayInstallTools::setDb())
			return false;

		return (parent::install() &&
			$this->registerHook('payment') &&
			$this->registerHook('orderConfirmation') &&
			$this->registerHook('adminOrder') &&
			$this->registerHook('updateOrderStatus') && SyspayInstallTools::setRestrictions($this->id));
	}

	public function uninstall()
	{
		include_once(_PS_MODULE_DIR_.$this->name.'/tools/install_tools.php');
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'syspay_rebill`');
		SyspayInstallTools::deleteOrderState();
		return parent::uninstall();
	}

	public function checkPaymentsRefunds($technical_checks) {
		if ($technical_checks['total'] == 'ko') {
			$this->context->smarty->assign('no_refunds', 1);
			$this->context->smarty->assign('no_payments', 1);
			return;
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

		$client = new Syspay_Merchant_Client($mid, $pass, $mode);
		$refund_list_request = new Syspay_Merchant_RefundListRequest();
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
				$refunds  = $client->request($refund_list_request);
			}
			catch (Syspay_Merchant_RequestException $s)
			{
				$this->context->smarty->assign('no_refunds', 1);
			}
			catch (Syspay_Merchant_UnexpectedResponseException $s)
			{
				$this->context->smarty->assign('no_refunds', 1);
			}
			try
			{
				$payments = $client->request($payment_list_request);
			}
			catch (Syspay_Merchant_RequestException $s)
			{
				$this->context->smarty->assign('no_payments', 1);
			}
			catch (Syspay_Merchant_UnexpectedResponseException $s)
			{
				$this->context->smarty->assign('no_payments', 1);
			}
			if (isset($refunds) && !$refunds || !$client)
				$this->context->smarty->assign('no_refunds', 1);
			if (isset($payments) && !$payments || !$client)
				$this->context->smarty->assign('no_payments', 1);
		}
	}


	public function getContent()
	{
		require_once(dirname(__FILE__).'/tools/loader.php');
		require_once(dirname(__FILE__).'/tools/syspay_tools.php');

		if (Tools::getValue('submitSyspay'))
			SyspayTools::updateSetting();
		if (Tools::getValue('export_refunds'))
			SyspayTools::processExportRefunds();
		if (Tools::getValue('export_transactions'))
			SyspayTools::processExportPayments();
		if (Tools::getValue('generate-cb'))
			SyspayTools::processGenerateCb();

		$technical_checks = array();
		$technical_checks['curl'] = (extension_loaded('curl') ? 'ok':'ko');
		$technical_checks['json'] = (extension_loaded('json') ? 'ok':'ko');
		$technical_checks['php']  = (version_compare(PHP_VERSION, '5.2', '>') ? 'ok':'ko');
		$mode = Configuration::get('SYSPAY_MODE');
		$test = (Configuration::get('SYSPAY_TEST_MID') != null && Configuration::get('SYSPAY_TEST_SHA1_PRIVATE') != null
				? 1:0);
		$live = (Configuration::get('SYSPAY_LIVE_MID') != null && Configuration::get('SYSPAY_LIVE_SHA1_PRIVATE') != null
				? 1:0);
		if (($mode == 0 && $test == 1) || ($mode == 1 && $live == 1))
			$technical_checks['settings'] = 'ok';
		else
			$technical_checks['settings'] = 'ko';

		if (!in_array('ko', $technical_checks))
			$technical_checks['total'] = 'ok';
		else
			$technical_checks['total'] = 'ko';
		$this->context->smarty->assign('checks', $technical_checks);
		$this->checkPaymentsRefunds($technical_checks);
		SyspayTools::assignVars();
		SyspayTools::assignOrderStates();
		if (version_compare(_PS_VERSION_, '1.6', '>='))
			return $this->display(__FILE__, '/views/templates/admin/bo-syspay-16.tpl');
		elseif (version_compare(_PS_VERSION_, '1.5', '>='))
			return $this->display(__FILE__, '/views/templates/admin/bo-syspay.tpl');
		else
			return $this->display(__FILE__, '/views/templates/admin/bo-syspay-14.tpl');
	}

	public function getIgnoreKeyList()
	{
		return $this->ignore_key_list;
	}

	public function getPaymentParams($params)
	{
		$currency = new Currency((int)$params['cart']->id_currency);
		$lang = new Language((int)$params['cart']->id_lang);
		$customer = new Customer((int)$params['cart']->id_customer);
		$mode = Configuration::get('SYSPAY_MODE');
		if ($mode == 0)
			$mid = Configuration::get('SYSPAY_TEST_MID');
		else
			$mid = Configuration::get('SYSPAY_LIVE_MID');

		$payment_params = array();
		$payment_params['method'] = 'PaymentRequest';
		$payment_params['application'] = $mid;
		$payment_params['amount'] = number_format(Tools::convertPrice((float)number_format($params['cart']->getOrderTotal(true, Cart::BOTH), 2, '.', ''),
			$currency), 2, '.', '');
		$payment_params['currency'] = $currency->iso_code;
		$payment_params['order_ref'] = $customer->id.date('YmdHis');
		$payment_params['language'] = Tools::strtoupper($lang->iso_code);
		$payment_params['layout'] = Configuration::get('SYSPAY_LAYOUT');
		$payment_params['email'] = $customer->email;
		$payment_params['extra'] = pSQL($params['cart']->id);

		ksort($payment_params);

		$payment_params['checksum'] = self::getChecksum($payment_params);

		return $payment_params;
	}

	public static function getChecksum($payment_params)
	{
		$mode = Configuration::get('SYSPAY_MODE');
		if ($mode == 0)
			$pass = Configuration::get('SYSPAY_TEST_SHA1_PRIVATE');
		else
			$pass = Configuration::get('SYSPAY_LIVE_SHA1_PRIVATE');
		$checksum = array();
		foreach ($payment_params as $key => $value) $checksum[] = $key.'='.$value.'&';

		$checksum = implode('', $checksum);
		$final_checksum = sha1($checksum.$pass);
		return $final_checksum;
	}
	public function hookPayment($params)
	{
		require_once(dirname(__FILE__).'/tools/loader.php');
		require_once(dirname(__FILE__).'/tools/syspay_tools.php');

		$technical_checks = array();
		$technical_checks['curl'] = (extension_loaded('curl') ? 'ok':'ko');
		$technical_checks['json'] = (extension_loaded('json') ? 'ok':'ko');
		$technical_checks['php']  = (version_compare(PHP_VERSION, '5.2', '>') ? 'ok':'ko');
		$mode = Configuration::get('SYSPAY_MODE');
		$test = (Configuration::get('SYSPAY_TEST_MID') != null && Configuration::get('SYSPAY_TEST_SHA1_PRIVATE') != null ? 1:0);
		$live = (Configuration::get('SYSPAY_LIVE_MID') != null && Configuration::get('SYSPAY_LIVE_SHA1_PRIVATE') != null ? 1:0);
		if (($mode == 0 && $test == 1) || ($mode == 1 && $live == 1))
			$technical_checks['settings'] = 'ok';
		else
			$technical_checks['settings'] = 'ko';

		if (in_array('ko', $technical_checks))
			return;
		$payment_params = $this->getPaymentParams($params);

		if (Configuration::get('SYSPAY_REBILL') == 1)
		{
			$card = SyspayTools::getRebillCardsByIdCustomer($this->context->customer->id);
			if ($card)
				$this->context->smarty->assign('card', $card);
		}

		if (Tools::getValue('err') && Tools::getValue('err') == 1)
		   $this->context->smarty->assign('err', '1');

		$this->context->smarty->assign(array(
			'syspay_params'				=> $payment_params,
			'SYSPAY_AUTHORIZED_PAYMENT' => Configuration::get('SYSPAY_AUTHORIZED_PAYMENT'),
			'SYSPAY_REBILL'			 => Configuration::get('SYSPAY_REBILL'),
			'syspay_link'				=> _MODULE_DIR_.'syspay/syspay-form.php',
			'restrictedIP'				=> ((Configuration::get('PS_SHOP_ENABLED') == 0 && in_array($_SERVER['REMOTE_ADDR'],
				explode(',', Configuration::get('PS_MAINTENANCE_IP')))) || Configuration::get('PS_SHOP_ENABLED') == 1 ? true : false)
		));
		if ($this->context->getMobileDevice() != false)
			return $this->display(__FILE__, '/views/templates/front/mobile/syspay.tpl');
		if (version_compare(_PS_VERSION_, '1.6', '>='))
			return $this->display(__FILE__, '/views/templates/hook/syspay-16.tpl');
		elseif (version_compare(_PS_VERSION_, '1.5', '>='))
			return $this->display(__FILE__, '/views/templates/hook/syspay.tpl');
		else
			return $this->display(__FILE__, '/views/templates/hook/syspay-14.tpl');
	}

	public function hookOrderConfirmation($params)
	{
		if ($params['objOrder']->module != $this->name)
			return;

		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			if ($params['objOrder']->valid || $params['objOrder']->getCurrentState() == Configuration::get('PS_OS_SYSPAY_AUTHORIZED'))
				$this->context->smarty->assign(array('status' => 'ok', 'id_order' => $params['objOrder']->id));
			else
				$this->context->smarty->assign('status', 'failed');
		}
		else
		{
			if ($params['objOrder']->valid || $params['objOrder']->getCurrentOrderState()->id == Configuration::get('PS_OS_SYSPAY_AUTHORIZED'))
				$this->context->smarty->assign(array('status' => 'ok', 'id_order' => $params['objOrder']->id));
			else
				$this->context->smarty->assign('status', 'failed');
		}
		$link = new Link();
		$this->context->smarty->assign('syspay_link', (method_exists($link, 'getPageLink') ? $link->getPageLink('contact-form.php', true) :
			Tools::getHttpHost(true).'contact-form.php'));
		return $this->display(__FILE__, '/views/templates/hook/hookorderconfirmation.tpl');
	}

	public function validate($id_cart, $id_order_state, $amount, $message = '', $payment_ref = '')
	{
		$extra_vars = array();
		$extra_vars['transaction_id'] = $payment_ref;
		$this->validateOrder((int)$id_cart, $id_order_state, $amount, $this->displayName, $message, $extra_vars, null, true);
		
		if (Configuration::get('SYSPAY_MODE') == 1)
			Configuration::updateValue('SYSPAY_CONFIGURATION_OK', true);
	}

	public function hookAdminOrder($params)
	{
		require_once(dirname(__FILE__).'/tools/loader.php');
		require_once(dirname(__FILE__).'/tools/syspay_tools.php');

		$back_params = $params;
		$params = $back_params;

		if (version_compare(_PS_VERSION_, '1.6', '<'))
			$this->context->controller->addJqueryPlugin('fancybox');

		$id_order = Tools::getValue('id_order');
		$order = new Order((int)$id_order);

		$infos_payment = SyspayTools::getPaymentInformations($id_order);
		if (!$infos_payment)
			return;
		if (!$order)
			die('Problem with ID order.');

		SyspayTools::processAdminForms($order, $infos_payment['id']);
		SyspayTools::assignPaymentDetails($infos_payment['id']);
		SyspayTools::assignRefundsDetails($order);

		$this->context->smarty->assign('id_order', $id_order);
		if (version_compare(_PS_VERSION_, '1.6', '>='))
			return $this->display(__FILE__, '/views/templates/hook/hookadminorder-16.tpl');
		elseif (version_compare(_PS_VERSION_, '1.5', '>='))
			return $this->display(__FILE__, 'views/templates/hook/hookadminorder.tpl');
		else
			return $this->display(__FILE__, 'views/templates/hook/hookadminorder-14.tpl');
	}

	public function hookUpdateOrderStatus($params)
	{
		require_once(dirname(__FILE__).'/tools/loader.php');

		$order_state = $params['newOrderStatus'];
		$id_order = $params['id_order'];
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
		if (!$client)
			return false;
		$original_payment_id = Db::getInstance()->getValue('
			SELECT sp.id_syspay_payment
			FROM '._DB_PREFIX_.'syspay_payment sp
			LEFT JOIN '._DB_PREFIX_.'orders o ON o.id_cart=sp.id_cart
			WHERE o.id_order='.$id_order);
		if (!$original_payment_id)
			return false;
		$info_request = new Syspay_Merchant_PaymentInfoRequest($original_payment_id);
		try {
			$payment = $client->request($info_request);
		} catch (Syspay_Merchant_RequestException $s) {
			return false;
		} catch (Syspay_Merchant_UnexpectedResponseException $s) {
			return false;
		}
		if (!$payment)
			return false;
		$id_status = $order_state->id;
		$id_fos = Db::getInstance()->getValue('
			SELECT `id_order_state`
			FROM `'._DB_PREFIX_.'order_history`
			WHERE `id_order` = '.(int)$id_order.'
			ORDER BY `date_add` ASC, `id_order_history` ASC');

		if ($id_fos == Configuration::get('PS_OS_SYSPAY_AUTHORIZED') && $id_status == Configuration::get('SYSPAY_CAPTURE_OS')
			&& $payment->getStatus() == 'AUTHORIZED')
		{
			$confirm_request = new Syspay_Merchant_ConfirmRequest();
			$confirm_request->setPaymentId($original_payment_id); // Returned to you on the initial payment request
			try {
				$client->request($confirm_request);
			} catch (Syspay_Merchant_RequestException $s) {
				return false;
			} catch (Syspay_Merchant_UnexpectedResponseException $s) {
				return false;
			}
		}
		return true;
	}
}

