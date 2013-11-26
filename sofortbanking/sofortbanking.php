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

if (!defined('_PS_VERSION_'))
	exit;

class Sofortbanking extends PaymentModule
{
	/** @var string HTML */
	private $html = '';

	/** @var string Supported languages */
	private $languages = array('en','de','es','fr','it','nl','pl','gb');

	/**
	 * Build module
	 *
	 * @see PaymentModule::__construct()
	 */
	public function __construct()
	{
		$this->name = 'sofortbanking';
		$this->tab = 'payments_gateways';
		$this->version = '2.0';
		$this->author = 'touchdesign';
		$this->module_key = '65af9f83d2ae6fbe6dbdaa91d21f952a';
		$this->currencies = true;
		$this->currencies_mode = 'radio';
		parent::__construct();
		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('sofortbanking');
		$this->description = $this->l('Accepts payments by sofortbanking');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
		/* Backward compatibility */
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
	}

	/**
	 * Install module
	 *
	 * @see PaymentModule::install()
	 */
	public function install()
	{
		if (!parent::install() || !Configuration::updateValue('SOFORTBANKING_USER_ID', '') || !Configuration::updateValue('SOFORTBANKING_PROJECT_ID', '')
			|| !Configuration::updateValue('SOFORTBANKING_PROJECT_PW', '') || !Configuration::updateValue('SOFORTBANKING_NOTIFY_PW', '')
			|| !Configuration::updateValue('SOFORTBANKING_BLOCK_LOGO', 'Y') || !Configuration::updateValue('SOFORTBANKING_CPROTECT', 'N')
			|| !Configuration::updateValue('SOFORTBANKING_OS_ERROR', 8) || !Configuration::updateValue('SOFORTBANKING_OS_ACCEPTED', 2)
			|| !Configuration::updateValue('SOFORTBANKING_REDIRECT', 'N') || !$this->registerHook('payment')
			|| !$this->registerHook('paymentReturn') || !$this->registerHook('leftColumn'))
			return false;
		return true;
	}

	/**
	 * Uninstall module
	 *
	 * @see PaymentModule::uninstall()
	 */
	public function uninstall()
	{
		if (!Configuration::deleteByName('SOFORTBANKING_USER_ID') || !Configuration::deleteByName('SOFORTBANKING_PROJECT_ID')
			|| !Configuration::deleteByName('SOFORTBANKING_PROJECT_PW') || !Configuration::deleteByName('SOFORTBANKING_NOTIFY_PW')
			|| !Configuration::deleteByName('SOFORTBANKING_BLOCK_LOGO') || !Configuration::deleteByName('SOFORTBANKING_OS_ERROR')
			|| !Configuration::deleteByName('SOFORTBANKING_OS_ACCEPTED') || !Configuration::deleteByName('SOFORTBANKING_CPROTECT')
			|| !Configuration::deleteByName('SOFORTBANKING_REDIRECT') || !parent::uninstall())
			return false;
		return true;
	}

	/**
	 * Validate submited data
	 */
	private function postValidation()
	{
		$this->_errors = array();
		if (Tools::getValue('submitUpdate'))
		{
			if (!Tools::getValue('SOFORTBANKING_USER_ID'))
				$this->_errors[] = $this->l('sofortueberweisung "user id" is required.');
			if (!Tools::getValue('SOFORTBANKING_PROJECT_ID'))
				$this->_errors[] = $this->l('sofortueberweisung "project id" is required.');
			if (!Tools::getValue('SOFORTBANKING_PROJECT_PW'))
				$this->_errors[] = $this->l('sofortueberweisung "project password" is required.');
		}
	}

	/**
	 * Update submited configurations
	 */
	public function getContent()
	{
		$this->html = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('submitUpdate'))
		{
			Configuration::updateValue('SOFORTBANKING_USER_ID', Tools::getValue('SOFORTBANKING_USER_ID'));
			Configuration::updateValue('SOFORTBANKING_PROJECT_ID', Tools::getValue('SOFORTBANKING_PROJECT_ID'));
			Configuration::updateValue('SOFORTBANKING_PROJECT_PW', Tools::getValue('SOFORTBANKING_PROJECT_PW'));
			Configuration::updateValue('SOFORTBANKING_NOTIFY_PW', Tools::getValue('SOFORTBANKING_NOTIFY_PW'));
			Configuration::updateValue('SOFORTBANKING_BLOCK_LOGO', Tools::getValue('SOFORTBANKING_BLOCK_LOGO'));
			Configuration::updateValue('SOFORTBANKING_CPROTECT', Tools::getValue('SOFORTBANKING_CPROTECT'));
			Configuration::updateValue('SOFORTBANKING_REDIRECT', Tools::getValue('SOFORTBANKING_REDIRECT'));
		}

		$this->postValidation();
		if (isset($this->_errors) && count($this->_errors))
			foreach ($this->_errors as $err)
				$this->html .= '<div class="alert error">'.$err.'</div>';
		elseif (Tools::getValue('submitUpdate') && !count($this->_errors))
			$this->getSuccessMessage();

		return $this->html.$this->displayForm();
	}

	/**
	 * Get success message for submited and updated datas
	 */
	public function getSuccessMessage()
	{
		$this->html .= '
		<div class="conf confirm">
			'.$this->l('Settings updated').'
		</div>';
	}

	/**
	 * Build and display admin form for configurations
	 */
	private function displayForm()
	{
		$dfl = array(
			'action' => $_SERVER['REQUEST_URI'],
			'img_path' => $this->_path.'img/'.$this->isSupportedLang($this->context->language->iso_code),
			'path' => $this->_path);

		$config = Configuration::getMultiple(array('SOFORTBANKING_USER_ID','SOFORTBANKING_PROJECT_ID','SOFORTBANKING_PROJECT_PW',
			'SOFORTBANKING_NOTIFY_PW','SOFORTBANKING_BLOCK_LOGO','SOFORTBANKING_CPROTECT','SOFORTBANKING_REDIRECT'));

		$link = array(
			'validation' => (Configuration::get('PS_SSL_ENABLED') == 1 ? 'https://' : 'http://')
				.$_SERVER['HTTP_HOST']._MODULE_DIR_.$this->name.'/validation.php',
			'success' => (Configuration::get('PS_SSL_ENABLED') == 1 ? 'https://' : 'http://')
				.$_SERVER['HTTP_HOST']._MODULE_DIR_.$this->name.'/confirmation.php?user_variable_1=-USER_VARIABLE_1-');

		if (version_compare(_PS_VERSION_, '1.5', '>='))
			$link['cancellation'] = (Configuration::get('PS_SSL_ENABLED') == 1 ? 'https://' : 'http://')
				.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'index.php?controller=order&step=3';
		else
			$link['cancellation'] = (Configuration::get('PS_SSL_ENABLED') == 1 ? 'https://' : 'http://')
				.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'order.php?step=3';

		$this->context->smarty->assign(array('sofort' => array('dfl' => $dfl, 'link' => $link, 'config' => $config)));

		return $this->display(__FILE__, 'views/templates/admin/display_form.tpl');
	}

	/**
	 * Check supported languages
	 *
	 * @param string $iso
	 * @return string iso
	 */
	private function isSupportedLang($iso = null)
	{
		if ($iso === null)
			$iso = Language::getIsoById((int)$this->context->cart->id_lang);
		if (in_array($iso, $this->languages))
			return $iso;
		else
			return 'en';
	}

	/**
	 * Build and display payment button
	 * 
	 * @param array $params
	 * @return string Templatepart
	 */
	public function hookPayment($params)
	{
		$this->context->smarty->assign('this_path', $this->_path);
		$this->context->smarty->assign('this_path_ssl', Tools::getHttpHost(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/');
		$this->context->smarty->assign('cprotect', Configuration::get('SOFORTBANKING_CPROTECT'));
		$this->context->smarty->assign('lang', Language::getIsoById((int)$params['cart']->id_lang));
		$this->context->smarty->assign('mod_lang', $this->isSupportedLang());

		return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
	}

	/**
	 * Build and display confirmation
	 *
	 * @param array $params
	 * @return string Templatepart
	 */
	public function hookPaymentReturn($params)
	{
		if (!$this->isPayment())
			return false;

		$state = $params['objOrder']->getCurrentState();
		if ($state == Configuration::get('SOFORTBANKING_OS_ACCEPTED'))
			$this->context->smarty->assign(array(
				'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
				'status' => 'accepted'
			)
		);

		return $this->display(__FILE__, 'views/templates/hook/payment_return.tpl');
	}

	/**
	 * Build and display left column banner
	 *
	 * @param array $params
	 * @return string Templatepart
	 */
	public function hookLeftColumn()
	{
		if (Configuration::get('SOFORTBANKING_BLOCK_LOGO') == 'N')
			return false;
		$this->context->smarty->assign('mod_lang', $this->isSupportedLang());
		return $this->display(__FILE__, 'views/templates/hook/left_column.tpl');
	}

	/**
	 * Check if payment is active
	 *
	 * @return boolean
	 */
	public function isPayment()
	{
		if (!$this->active)
			return false;

		if (!Configuration::get('SOFORTBANKING_USER_ID')
				|| !Configuration::get('SOFORTBANKING_PROJECT_ID')
				|| !Configuration::get('SOFORTBANKING_PROJECT_PW'))
			return false;
		return true;
	}

	/**
	 * Build and display payment page for PS 1.4
	 *
	 * This part is only for backward comatibility to PS 1.4 and 
	 * will be removed in one of the further module versions.
	 */
	public function backwardPaymentController()
	{
		$cart = $this->context->cart;

		if (!$this->isPayment())
			return false;

		$address = new Address((int)$cart->id_address_invoice);
		$customer = new Customer((int)$cart->id_customer);
		$currency = $this->getCurrency();
		$country = new Country((int)$address->id_country);

		if (!Configuration::get('SOFORTBANKING_USER_ID'))
			return $this->l($this->displayName.' Error: (invalid or undefined userId)');
		if (!Configuration::get('SOFORTBANKING_PROJECT_ID'))
			return $this->l($this->displayName.' Error: (invalid or undefined projectId)');
		if (!Validate::isLoadedObject($address)
				|| !Validate::isLoadedObject($customer)
				|| !Validate::isLoadedObject($currency))
			return $this->l($this->displayName.' Error: (invalid address or customer)');

		$parameters = array(
			'user_id' => Configuration::get('SOFORTBANKING_USER_ID'),'project_id' => Configuration::get('SOFORTBANKING_PROJECT_ID'),
			'sender_holder' => '','','','sender_country_id' => $country->iso_code,
			'amount' => number_format($cart->getOrderTotal(), 2, '.', ''),
			'currency_id' => $currency->iso_code,'reason_1' => $this->l('CartId:').' '.time().'-'.(int)$cart->id,
			'reason_2' => $customer->firstname.' '.Tools::ucfirst(Tools::strtolower($customer->lastname)),
			'user_variable_0' => $customer->secure_key,'user_variable_1' => (int)$cart->id,
			'user_variable_2' => '','user_variable_3' => '','user_variable_4' => '','user_variable_5' => '',
			'project_password' => Configuration::get('SOFORTBANKING_PROJECT_PW'),
		);

		$this->context->smarty->assign(array(
			'this_path' => $this->_path,
			'nbProducts' => $cart->nbProducts(),
			'total' => $cart->getOrderTotal(),
			'version' => _PS_VERSION_,
			'hash' => sha1(implode('|', $parameters)),
			'gateway' => 'https://www.sofortueberweisung.de/payment/start',
			'cprotect' => Configuration::get('SOFORTBANKING_CPROTECT'),
			'parameters' => $parameters,
			'mod_lang',$this->isSupportedLang()
		));

		return $this->display(__FILE__, (Configuration::get('SOFORTBANKING_REDIRECT') == 'Y'
			? 'views/templates/front/payment_redirect.tpl' : 'views/templates/front/payment_execution.tpl'));
	}
}

?>