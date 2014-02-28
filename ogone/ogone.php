<?php
/*
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
	
if (!defined('_PS_VERSION_'))
	exit;

class Ogone extends PaymentModule
{
	private $_ignoreKeyList = array('secure_key');

	public function __construct()
	{
		$this->name = 'ogone';
		$this->tab = 'payments_gateways';
		$this->version = '2.6';
		$this->author = 'PrestaShop';
		$this->module_key = '787557338b78e1705f2a4cb72b1dbb84';

		parent::__construct();

		$this->displayName = 'Ogone';
		$this->description = $this->l('With over 80 different payment methods and 200+ acquirer connections, Ogone helps you manage, collect and secure your online or mobile payments, help prevent fraud and drive your business!');

		/* For 1.4.3 and less compatibility */
		$updateConfig = array('PS_OS_CHEQUE', 'PS_OS_PAYMENT', 'PS_OS_PREPARATION', 'PS_OS_SHIPPING', 'PS_OS_CANCELED', 'PS_OS_REFUND', 'PS_OS_ERROR', 'PS_OS_OUTOFSTOCK', 'PS_OS_BANKWIRE', 'PS_OS_PAYPAL', 'PS_OS_WS_PAYMENT');
		if (!Configuration::get('PS_OS_PAYMENT'))
			foreach ($updateConfig as $u)
				if (!Configuration::get($u) && defined('_'.$u.'_'))
					Configuration::updateValue($u, constant('_'.$u.'_'));

		/** Backward compatibility */
		require(_PS_MODULE_DIR_.'ogone/backward_compatibility/backward.php');
	}
	
	public function install()
	{
		return (parent::install() &&
				$this->registerHook('payment') &&
				$this->registerHook('orderConfirmation') &&
				$this->registerHook('backOfficeHeader'));
	}

	public function hookBackOfficeHeader()
	{
		if ((int)strcmp((_PS_VERSION_ < '1.5' ? Tools::getValue('configure') : Tools::getValue('module_name')), $this->name) == 0)
		{
			if (_PS_VERSION_ < '1.5')
				return '<script type="text/javascript" src="'.__PS_BASE_URI__.'js/jquery/jquery-ui-1.8.10.custom.min.js"></script>
					<script type="text/javascript" src="'.__PS_BASE_URI__.'js/jquery/jquery.fancybox-1.3.4.js"></script>
					<link type="text/css" rel="stylesheet" href="'.__PS_BASE_URI__.'css/jquery.fancybox-1.3.4.css" />';
			else
			{
				$this->context->controller->addJquery();
				$this->context->controller->addJQueryPlugin('fancybox');
			}
		}
		return '';
	}

	public function getContent()
	{
		if (!isset($this->_html) || empty($this->_html))
			$this->_html = '';
		
		if (Tools::isSubmit('submitOgone'))
		{
			Configuration::updateValue('OGONE_PSPID', Tools::getValue('OGONE_PSPID'));
			Configuration::updateValue('OGONE_SHA_IN', Tools::getValue('OGONE_SHA_IN'));
			Configuration::updateValue('OGONE_SHA_OUT', Tools::getValue('OGONE_SHA_OUT'));
			Configuration::updateValue('OGONE_MODE', (int)Tools::getValue('OGONE_MODE'));
			$dataSync = (($pspid = Configuration::get('OGONE_PSPID'))
				? '<img src="http://api.prestashop.com/modules/ogone.png?pspid='.urlencode($pspid).'&mode='.(int)Tools::getValue('OGONE_MODE').'" style="float:right" />'
				: ''
			);
			$this->_html .= '<div class="conf">'.$this->l('Configuration updated').$dataSync.'</div>';
		}
		
		if ($this->context->language->iso_code == 'fr')
			$account_creation_link = 'https://secure.ogone.com/ncol/test/new_account.asp?BRANDING=ogone&ISP=OFR&SubID=3&SOLPRO=&MODE=STD&ACountry=FR&Lang=2';
		elseif ($this->context->language->iso_code == 'de')
			$account_creation_link = 'https://secure.ogone.com/ncol/test/new_account.asp?BRANDING=ogone&ISP=ODE&SubID=5&SOLPRO=&MODE=STD&ACountry=DE&Lang=5';
		else 
			$account_creation_link = 'https://secure.ogone.com/ncol/test/new_account.asp?BRANDING=ogone&ISP=OFR&SubID=3&SOLPRO=&MODE=STD&ACountry=FR&Lang=1';
		
		return $this->_html.'
		<fieldset><legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->l('Help').'</legend>
			<p>
				<img src="../modules/'.$this->name.'/ogone.png" alt="ogone logo" style="float: left; margin: 0 5px 5px 0;"/>
				'.$this->l('Ogone Payment Services is a leading European Payment Service Provider with international reach.').'
				'.sprintf($this->l('More than %1$s35,000 businesses worldwide%2$s trust Ogone to manage and secure their online payments, help prevent fraud and drive their business.'), '<span style="color: #127ac1; font-weight: bold">', '</span>').'
				'.sprintf($this->l('Ogone is connected through certified links with more than %1$s200 different banks and acquirers%2$s and hence is able to provide over %1$s80 international, alternative and prominent local payment methods%2$s in Europe, Asia, Latin America and the Middle East.'), '<span style="color: #127ac1; font-weight: bold">', '</span>').'
				<div class="clear">&nbsp;</div>
			</p>
			<p style="color: #127ac1; font-weight: bold; font-size: 1.2em; margin: 20px 0">'.sprintf($this->l('To activate your account, you need a %1$sMID (Merchant Identification) contract%2$s with an acquiring bank.'), '<span style="text-decoration: underline;">', '</span>').'</p>
			
			<p style="margin-top: 15px">1. <span style="color: #127ac1;">'.$this->l('Create your free test account by clicking on the link below:').'</span></p>
			<p style="float: right; padding: 5px;"><a style="color: white; background: #127ac1; padding: 10px; border-radius: 10px; font-size: 15px; font-weight: bold;" href="'.$account_creation_link.'">'.$this->l('Create your free Test Account!').'</a></p>
			<p>
				'.sprintf($this->l('This test account will allow you to create your %1$sPSPID (Ogone Identification)%2$s, to request administrative information, to activate the desired payment methods and to install the %1$sSHA-in and SHA-out signatures%2$s which you are sought for the configuration of your PrestaShop account below.'), '<b>', '</b>').'
				'.$this->l('You can also perform test payments.').'
			</p>
			
			<p style="margin-top: 15px">2. <span style="color: #127ac1;">'.$this->l('Transfer your Ogone test account into production:').'</span></p>
			<p>
				'.$this->l('This second part will allow you to complete your billing information, insert the UID number your acquirer has communicated to you and obtain your Ogone contract.').'
			</p>
			
			<p style="margin-top: 15px">3. <span style="color: #127ac1;">'.$this->l('Activate your account on Prestashop!').'</span></p>
			<p>
				'.$this->l('Simply insert the following information below: your PSPID as well as the SHA-in and SHA-out signatures set on your Ogone account.').'
			</p>
			<p style="margin-top: 15px; color: #127ac1;">'.$this->l('For all commercial, technical or administrative question, please do not hesitate to contact us on 0203 147 4966.').'</p>
			
			<div class="clear">&nbsp;</div>
		</fieldset>
		<div class="clear">&nbsp;</div>
		<form action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" method="post">
			<fieldset><legend><img src="../img/admin/contact.gif" /> '.$this->l('Settings').'</legend>
				<div style="float: left; width: 48%; margin: 1%;">
					<label for="pspid">'.$this->l('PSPID').'</label>
					<div class="margin-form">
						<input type="text" id="pspid" size="20" name="OGONE_PSPID" value="'.Tools::safeOutput(Tools::getValue('OGONE_PSPID', Configuration::get('OGONE_PSPID'))).'" />
					</div>
					<div class="clear">&nbsp;</div>
					<label for="sha-in">'.$this->l('SHA-in signature').'</label>
					<div class="margin-form">
						<input type="text" id="sha-in" size="20" name="OGONE_SHA_IN" value="'.Tools::safeOutput(Tools::getValue('OGONE_SHA_IN', Configuration::get('OGONE_SHA_IN'))).'" />
					</div>
					<div class="clear">&nbsp;</div>
					<label for="sha-out">'.$this->l('SHA-out signature').'</label>
					<div class="margin-form">
						<input type="text" id="sha-out" size="20" name="OGONE_SHA_OUT" value="'.Tools::safeOutput(Tools::getValue('OGONE_SHA_OUT', Configuration::get('OGONE_SHA_OUT'))).'" />
					</div>
					<div class="clear">&nbsp;</div>
					<label>'.$this->l('Mode').'</label>
					<div class="margin-form">
						<span style="display:block;float:left;margin-top:3px;"><input type="radio" id="test" name="OGONE_MODE" value="0" style="vertical-align:middle;display:block;float:left;margin-top:2px;margin-right:3px;"
							'.(!Tools::getValue('OGONE_MODE', Configuration::get('OGONE_MODE')) ? 'checked="checked"' : '').' />
						<label for="test" style="color:#900;display:block;float:left;text-align:left;width:60px;">'.$this->l('Test').'</label>&nbsp;</span>
						<span style="display:block;float:left;margin-top:3px;">
						<input type="radio" id="production" name="OGONE_MODE" value="1" style="vertical-align:middle;display:block;float:left; margin-top:2px;margin-right:3px;"
							'.(Tools::getValue('OGONE_MODE', Configuration::get('OGONE_MODE')) ? 'checked="checked"' : '').' />
						<label for="production" style="color:#080;display:block;float:left;text-align:left;width:85px;">'.$this->l('Production').'</label></span>
					</div>
					<div class="clear">&nbsp;</div>
					<input type="submit" name="submitOgone" value="'.$this->l('Update settings').'" class="button" />
				</div>
				<div style="float: left; width: 48%; margin: 1%;">
					<ol>
						<li><a class="ogone_screenshot" href="'._MODULE_DIR_.$this->name.'/screenshots/en1.png" title="'.$this->l('Step').'1">'.$this->l('Screenshot').' <u>'.$this->l('Step').' 1</u></a></li>
						<li><a class="ogone_screenshot" href="'._MODULE_DIR_.$this->name.'/screenshots/en2.png" title="'.$this->l('Step').'2">'.$this->l('Screenshot').' <u>'.$this->l('Step').' 2</u></a></li>
						<li><a class="ogone_screenshot" href="'._MODULE_DIR_.$this->name.'/screenshots/en3.png" title="'.$this->l('Step').'3">'.$this->l('Screenshot').' <u>'.$this->l('Step').' 3</u></a></li>
						<li><a class="ogone_screenshot" href="'._MODULE_DIR_.$this->name.'/screenshots/en4.png" title="'.$this->l('Step').'4">'.$this->l('Screenshot').' <u>'.$this->l('Step').' 4</u></a></li>
						<li><a class="ogone_screenshot" href="'._MODULE_DIR_.$this->name.'/screenshots/en5.png" title="'.$this->l('Step').'5">'.$this->l('Screenshot').' <u>'.$this->l('Step').' 5</u></a></li>
					</ol>
				</div>
			</fieldset>
		</form>
		<div class="clear">&nbsp;</div>
		<script type="text/javascript">
			$(document).ready(function() {
				$(".ogone_screenshot").fancybox({
					helpers : {
						title : {
							type : \'over\'
						}
					}
				});
			});
		</script>';
	}
	
	public function getIgnoreKeyList()
	{
		return $this->_ignoreKeyList;
	}
	
	public function hookPayment($params)
	{
		$currency = new Currency((int)($params['cart']->id_currency));
		$lang = new Language((int)($params['cart']->id_lang));
		$customer = new Customer((int)($params['cart']->id_customer));
		$address = new Address((int)($params['cart']->id_address_invoice));
		$country = new Country((int)($address->id_country), (int)($params['cart']->id_lang));
		
		$ogoneParams = array();
		$ogoneParams['PSPID'] = Configuration::get('OGONE_PSPID');
		$ogoneParams['OPERATION'] = 'SAL';
		$ogoneParams['ORDERID'] = pSQL($params['cart']->id);

		$ogoneParams['AMOUNT'] = number_format((float)(number_format($params['cart']->getOrderTotal(true, Cart::BOTH), 2, '.', '')), 2, '.', '') * 100;
		$ogoneParams['CURRENCY'] = $currency->iso_code;
		$ogoneParams['LANGUAGE'] = $lang->iso_code.'_'.strtoupper($lang->iso_code);
		$ogoneParams['CN'] = $customer->lastname;
		$ogoneParams['EMAIL'] = $customer->email;
		$ogoneParams['OWNERZIP'] = $address->postcode;
		$ogoneParams['OWNERADDRESS'] = ($address->address1);
		$ogoneParams['OWNERCTY'] = $country->iso_code;
		$ogoneParams['OWNERTOWN'] = $address->city;
		$ogoneParams['PARAMPLUS'] = 'secure_key='.$params['cart']->secure_key;
		if (!empty($address->phone))
			$ogoneParams['OWNERTELNO'] = $address->phone;

		ksort($ogoneParams);
		$shasign = '';
		foreach ($ogoneParams as $key => $value)
			$shasign .= strtoupper($key).'='.$value.Configuration::get('OGONE_SHA_IN');
		$ogoneParams['SHASign'] = strtoupper(sha1($shasign));
		
		$this->context->smarty->assign('ogone_params', $ogoneParams);
		$this->context->smarty->assign('OGONE_MODE', Configuration::get('OGONE_MODE'));
		
		return $this->display(__FILE__, 'ogone.tpl');
    }
	
	public function hookOrderConfirmation($params)
	{
		if ($params['objOrder']->module != $this->name)
			return;
		
		if ($params['objOrder']->valid)
			$this->context->smarty->assign(array('status' => 'ok', 'id_order' => $params['objOrder']->id));
		else
			$this->context->smarty->assign('status', 'failed');

		$link = method_exists('Link', 'getPageLink') ? $this->context->link->getPageLink('contact', true) : Tools::getHttpHost(true).'contact';
		$this->context->smarty->assign('ogone_link', $link);
		return $this->display(__FILE__, 'hookorderconfirmation.tpl');
	}
	
	public function validate($id_cart, $id_order_state, $amount, $message = '', $secure_key)
	{
		$this->validateOrder((int)$id_cart, $id_order_state, $amount, $this->displayName, $message, NULL, NULL, true, pSQL($secure_key));		
	}
}
