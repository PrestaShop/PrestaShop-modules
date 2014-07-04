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

class SofortbankingPaymentModuleFrontController extends ModuleFrontController
{
	public $ssl = true;

	/** @var string Supported languages */
	private $languages = array('en','de','es','fr','it','nl','pl','gb','hu','cs','sk');

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
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		$this->display_column_left = false;
		parent::initContent();

		if (!$this->isTokenValid())
			die($this->module->l($this->module->displayName.' Error: (invalid token)'));

		$cart = $this->context->cart;

		$address = new Address((int)$cart->id_address_invoice);
		$customer = new Customer((int)$cart->id_customer);
		$currency = $this->context->currency;
		$country = new Country((int)$address->id_country);

		if (!Configuration::get('SOFORTBANKING_USER_ID'))
			die($this->module->l($this->module->displayName.' Error: (invalid or undefined userId)'));

		if (!Configuration::get('SOFORTBANKING_PROJECT_ID'))
			die($this->module->l($this->module->displayName.' Error: (invalid or undefined projectId)'));

		if (!Validate::isLoadedObject($address) || !Validate::isLoadedObject($customer)
			|| !Validate::isLoadedObject($currency))
			die($this->module->l($this->module->displayName.' Error: (invalid address or customer)'));

		$parameters = array(
			'user_id' => Configuration::get('SOFORTBANKING_USER_ID'),'project_id' => Configuration::get('SOFORTBANKING_PROJECT_ID'),
			'sender_holder' => '','','','sender_country_id' => $country->iso_code,
			'amount' => number_format($cart->getOrderTotal(), 2, '.', ''),
			'currency_id' => $currency->iso_code,'reason_1' => time().'-'.(int)$cart->id,
			'reason_2' => $customer->firstname.' '.Tools::ucfirst(Tools::strtolower($customer->lastname)),
			'user_variable_0' => $customer->secure_key,'user_variable_1' => (int)$cart->id,
			'user_variable_2' => '','user_variable_3' => '','user_variable_4' => '','user_variable_5' => '',
			'project_password' => Configuration::get('SOFORTBANKING_PROJECT_PW'),
		);

		$this->context->smarty->assign(array(
			'this_path' => $this->module->getPathUri(),
			'nbProducts' => $cart->nbProducts(),
			'total' => $cart->getOrderTotal(),
			'version' => _PS_VERSION_,
			'hash' => sha1(implode('|', $parameters)),
			'gateway' => 'https://www.sofortueberweisung.de/payment/start',
			'cprotect' => Configuration::get('SOFORTBANKING_CPROTECT'),
			'parameters' => $parameters,
			'mod_lang' => $this->isSupportedLang()
		));

		$this->setTemplate((Configuration::get('SOFORTBANKING_REDIRECT') == 'Y'
			? 'payment_redirect.tpl' : 'payment_execution.tpl'));
	}
}
