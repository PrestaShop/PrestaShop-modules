<?php
/**
* 2007-2014 PrestaShop
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
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2014 PrestaShop SA
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class Desjardins extends PaymentModule
{
	public $limited_countries = array('ca');

	public function __construct()
	{
		$this->name = 'desjardins';
		$this->tab = 'payments_gateways';
		$this->version = '0.3.8';
		$this->author = 'PrestaShop';
		$this->need_instance = 0;
		$this->bootstrap = true;
		$this->display = 'view';
		$this->meta_title = $this->l('Desjardins');

		parent::__construct();

		$this->displayName = $this->l('Desjardins');
		$this->description = $this->l('Accept payments by Credit Card with Desjardins (Visa, Mastercard, Amex, Discover and Diners Club)');
		$this->ps_versions_compliancy = array('min' => '1.4', 'max' => _PS_VERSION_);
	}

	public function install()
	{
		return parent::install() && $this->registerHook('payment') && $this->registerHook('orderConfirmation')
		&& $this->registerHook('moduleRoutes') && Configuration::updateValue('DESJARDINS_MODE', 0);
	}

	public function uninstall()
	{
		return Configuration::deleteByName('DESJARDINS_TPE') && Configuration::deleteByName('DESJARDINS_CLE')
		&& Configuration::deleteByName('DESJARDINS_CODE_SOCIETE') && Configuration::deleteByName('DESJARDINS_MODE') && parent::uninstall();
	}

	public function getContent()
	{
		/* Loading CSS and JS files */
		$this->context->controller->addCSS(array($this->_path.'/css/desjardins.css'));

		$output = '';
		if (Tools::isSubmit('submitDesjardins'))
		{
			if (!Tools::getValue('DESJARDINS_TPE') || !Tools::getValue('DESJARDINS_CLE') || !Tools::getValue('DESJARDINS_CODE_SOCIETE'))
				$output .= $this->displayError($this->l('Please fill the required fields to update your configuration.'));
			elseif (Tools::getValue('DESJARDINS_TPE') && Configuration::updateValue('DESJARDINS_TPE', Tools::getValue('DESJARDINS_TPE'))
			&& Configuration::updateValue('DESJARDINS_CLE', Tools::getValue('DESJARDINS_CLE'))
			&& Configuration::updateValue('DESJARDINS_CODE_SOCIETE', Tools::getValue('DESJARDINS_CODE_SOCIETE'))
			&& Configuration::updateValue('DESJARDINS_MODE', Tools::getValue('DESJARDINS_MODE'))
			&& Configuration::updateValue('DESJARDINS_VERSION', '3.0'))
					$output .= $this->displayConfirmation($this->l('Congratulations, your configuration was updated successfully'));
		}

		$this->smarty->assign(array('admin_lang' => Context::getContext()->language->iso_code,
		'desjardins_url' => $this->context->link->getModuleLink('desjardins', 'validation', array(), false)));

		return $this->display(__FILE__, 'views/templates/admin/configuration.tpl').$output.$this->renderForm();
	}

	public function renderForm()
	{
		$fields_form = array(
		'form' => array(
			'legend' => array(
				'title' => $this->l('Configuration'),
				'icon' => 'icon-cogs'
			),
			'input' => array(
				array(
					'type' => 'switch',
					'label' => $this->l('Live mode'),
					'name' => 'DESJARDINS_MODE',
					'is_bool' => true,
					'desc' => $this->l('Production mode should be used only if your store is live'),
					'values' => array(
						array(
							'id' => 'active_on',
							'value' => 1,
							'label' => $this->l('Production')
						),
						array(
							'id' => 'active_off',
							'value' => 0,
							'label' => $this->l('Test')
						)
					),
				),
				array(
					'type' => 'text',
					'label' => $this->l('TPE'),
					'name' => 'DESJARDINS_TPE',
					'required' => true,
					'class' => 'fixed-width-xxl',
					'desc' => $this->l('Your Desjardins Electronic Payment Terminal ID'),
				),
				array(
					'type' => 'text',
					'label' => $this->l('Key'),
					'name' => 'DESJARDINS_CLE',
					'required' => true,
					'class' => 'fixed-width-xxl',
					'desc' => $this->l('Your Desjardins Secret Key'),
				),
				array(
					'type' => 'text',
					'label' => $this->l('Company code'),
					'name' => 'DESJARDINS_CODE_SOCIETE',
					'required' => true,
					'class' => 'fixed-width-xxl',
					'desc' => $this->l('Your Desjardins Company Code'),
				),
			),
			'submit' => array(
				'title' => $this->l('Save settings'),
				'class' => 'btn btn-default pull-right')
			)
		);

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();

		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitDesjardins';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
		.'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => Configuration::getMultiple(array('DESJARDINS_TPE', 'DESJARDINS_CLE', 'DESJARDINS_MODE', 'DESJARDINS_CODE_SOCIETE')),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}

	/*
	 * * The seal used in the payment form is calculated using the following encryption hashing function
	 * * We combine data with the secret key in accordance with RFC 2104 specifications
	 */

	public function generateHash($params)
	{
		return Tools::strtolower(hash_hmac('sha1',
								$params['TPE'].'*'.$params['date'].'*'.$params['montant'].'*'.$params['reference'].'*'.
								$params['texte-libre'].'*'.$params['version'].'*'.$params['lgue'].'*'.$params['societe'].'*'.$params['mail'].'**********',
								$this->getKey()));
	}

	public function getKey()
	{
		$hex_str_key = Tools::substr(Configuration::get('DESJARDINS_CLE'), 0, 38);
		$hex_final = Tools::substr(Configuration::get('DESJARDINS_CLE'), 38, 2).'00';
		$cca0 = ord($hex_final);

		if ($cca0 > 70 && $cca0 < 97)
				$hex_str_key .= Tools::chr($cca0 - 23).Tools::substr($hex_final, 1, 1);
		else
				$hex_str_key .= (Tools::substr($hex_final, 1, 1) == 'M') ? Tools::substr($hex_final,
							0, 1).'0' : Tools::substr($hex_final, 0, 2);

		return pack('H*', $hex_str_key);
	}


	public function hookPayment()
	{
		$params = array();
		$params['version'] = Configuration::get('DESJARDINS_VERSION');
		$params['TPE'] = Configuration::get('DESJARDINS_TPE');
		$params['date'] = date('d/m/Y:H:i:s');
		$params['montant'] = number_format($this->context->cart->getOrderTotal(), 2, '.', '').Tools::strtoupper($this->context->currency->iso_code);
		$params['reference'] = (int)$this->context->cart->id;
		$params['texte-libre'] = 'PrestaShop';
		$params['mail'] = $this->context->customer->email;
		$params['lgue'] = in_array($this->context->language->iso_code, array('EN', 'FR')) ? Tools::strtoupper($this->context->language->iso_code) : 'EN';
		$params['societe'] = Configuration::get('DESJARDINS_CODE_SOCIETE');
		$params['url_retour'] = $this->context->link->getPageLink('order');
		$params['url_retour_ok'] = version_compare(_PS_VERSION_, '1.4', '<') ? (Configuration::get('PS_SSL_ENABLED') ?
		Tools::getShopDomainSsl(true) : Tools::getShopDomain(true)).__PS_BASE_URI__.'order-confirmation.php?id_cart='.
		(int)$this->context->cart->id.'&id_module='.(int)$this->id.'&key='.$this->context->customer->secure_key :
		$this->context->link->getPageLink('order-confirmation.php', null, null,
		array('id_cart' => (int)$this->context->cart->id, 'key' => $this->context->customer->secure_key, 'id_module' => $this->id));
		$params['url_retour_err'] = $params['url_retour_ok'];
		$params['MAC'] = $this->generateHash($params);
		$params['api_url'] = Configuration::get('DESJARDINS_MODE') ? 'https://paiement.e-i.com/desjardins/paiement.cgi' :
		'https://paiement.e-i.com/desjardins/test/paiement.cgi';

		$this->smarty->assign('desjardins_params', $params);

		if (version_compare(_PS_VERSION_, '1.6', '<'))
			return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
		return $this->display(__FILE__, 'views/templates/hook/payment_16.tpl');
	}

	public function displayAdminHomeQuickLinks()
	{
		return $this->display(__FILE__, 'views/templates/hook/back-office-home.tpl');
	}

	public function hookModuleRoutes()
	{
		/* The IPN sent by Desjardins can take a few seconds to create the order */
		if ((Tools::getIsset('controller') && Tools::getValue('controller') == 'order-confirmation')
		&& (Tools::getIsset('id_module') && Tools::getValue('id_module') == $this->id))
			sleep(3);
	}

	public function hookOrderConfirmation($params)
	{
		if (!Tools::getIsset($params['objOrder']) || ($params['objOrder']->module != $this->name))
				return false;

		if ($params['objOrder'] && Validate::isLoadedObject($params['objOrder']) && Tools::getIsset($params['objOrder']->valid))
				$this->smarty->assign('desjardins_order',
					array('reference' => Tools::getIsset($params['objOrder']->reference) ? $params['objOrder']->reference : '#'.sprintf('%06d',
								$params['objOrder']->id), 'valid' => $params['objOrder']->valid));

		return $this->display(__FILE__, 'order-confirmation.tpl');
	}
}
