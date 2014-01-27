<?php
/*
* Prestashop PaymentSense Re-Directed Payment Module
* Copyright (C) 2013 PaymentSense.
*
* This program is free software: you can redistribute it and/or modify it under the terms
* of the AFL Academic Free License as published by the Free Software Foundation, either
* version 3 of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
* without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
* See the AFL Academic Free License for more details. You should have received a copy of the
* AFL Academic Free License along with this program. If not, see <http://opensource.org/licenses/AFL-3.0/>.
*
*  @author PaymentSense <devsupport@paymentsense.com>
*  @copyright  2013 PaymentSense
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*

File Modified: 12/03/2013 - By Shaun Ponting - Opal Creations.
File Modified: 28/06/2013 - By Shaun Ponting - Opal Creations - Updated Licence, XSS.
File Modified: 16/07/2013 - By Lewis Ayres-Stephens - PaymentSense - Multi Currency
V1.8 - File Modified: 23/07/2013 - By Lewis Ayres-Stephens - PaymentSense - img file structure
V1.9 - File Modified: 17/08/2013 - By Adam Watkins - Opal Creations:
							-> updated all use of strlen, substr to use Tools::
							-> Fixed use of (INTVAL) to swap with (int)
							-> Also added index.php files
							-> altered folder file structure
							-> improved code formatting a little
V1.9.1 - File Modified: 28/08/2013 - By Adam Watkins - Opal Creations, see changelog provided to PaymentSense
V1.9.2 - File Modified: 09/10/2013 - By Lewis Ayres-Stephens - PaymentSense - replaced ‘global $smarty' with the context : ‘$this->context->smarty’
*/

if (!defined('_PS_VERSION_'))
	exit;

class PaymentSense extends PaymentModule
{
	public function __construct()
	{
		$this->name = 'paymentsense';
		$this->tab = 'payments_gateways';
		$this->version = '1.9.7';
		$this->author = 'PaymentSense';
		$this->module_key = '1e631b52ed3d1572df477b9ce182ccf9';

		$this->currencies = true;
		$this->currencies_mode = 'radio';
		parent::__construct();

		$this->displayName = $this->l('PaymentSense');
		$this->description = $this->l('Process transactions through the PaymentSense gateway.');
		$this->confirmUninstall = $this->l('Are you sure?');
	}

	public function install()
	{
		return (parent::install() && Configuration::updateValue('PAYMENTSENSE_GATEWAYID', '')
			&& Configuration::updateValue('PAYMENTSENSE_GATEWAYPASS', '')
			&& Configuration::updateValue('PAYMENTSENSE_PSK', '')
			&& Configuration::updateValue('PAYMENTSENSE_DEBUG', '')
			&& Configuration::updateValue('PAYMENTSENSE_TRANSACTION_TYPE', '')
			&& $this->registerHook('payment')
			&& $this->registerHook('paymentReturn'));
			/* Blank line to retain line numbers in changelog*/
			/* Blank line to retain line numbers in changelog*/
			/* Blank line to retain line numbers in changelog*/
	}

	public function uninstall()
	{
		return (Configuration::deleteByName('PAYMENTSENSE_GATEWAYID')
			&& Configuration::deleteByName('PAYMENTSENSE_GATEWAYPASS')
			&& Configuration::deleteByName('PAYMENTSENSE_PSK')
			&& Configuration::deleteByName('PAYMENTSENSE_DEBUG')
			&& Configuration::deleteByName('PAYMENTSENSE_TRANSACTION_TYPE')
			&& parent::uninstall());
			/* Blank line to retain line numbers in changelog*/
			/* Blank line to retain line numbers in changelog*/
			/* Blank line to retain line numbers in changelog*/
	}

	private function getSetting($name)
	{
		if (array_key_exists($name, $_POST))
			return Tools::getValue($name);
		elseif (Configuration::get($name))
			return Configuration::get($name);
	}

	private function validateTrueFalseString($value)
	{
		if ($value == 'True' || $value == 'False')
		return $value;
	}

	private function trueFalseOption($name, $label, $trueLabel = 'True', $falseLabel = 'False')
	{
		if ($this->getSetting($name) == 'True')
		{
			$trueSelected = ' selected';
			$falseSelected = '';
		}
		else
		{
			$trueSelected = '';
			$falseSelected = ' selected';
		}

		$html = '<strong>'.$this->l(Tools::safeOutput($label)).'</strong></td><td><select name="'.Tools::safeOutput($name).'">
		<option'.$trueSelected.' value="True">'.$this->l(Tools::safeOutput($trueLabel)).'</option>
		<option'.$falseSelected.' value="False">'.$this->l(Tools::safeOutput($falseLabel)).'</option>
		</select>';

		return $html;
	}

	private function salePreauthOption($name, $label, $trueLabel = 'SALE', $falseLabel = 'PREAUTH')
	{
		if ($this->getSetting($name) == 'SALE')
		{
			$trueSelected = ' selected';
			$falseSelected = '';
		}
		else
		{
			$trueSelected = '';
			$falseSelected = ' selected';
		}

		$html = '<strong>'.$this->l(Tools::safeOutput($label)).'</strong></td><td><select name="'.Tools::safeOutput($name).'">
		<option'.$trueSelected.' value="SALE">'.$this->l(Tools::safeOutput($trueLabel)).'</option>
		option'.$falseSelected.' value="PREAUTH">'.$this->l(Tools::safeOutput($falseLabel)).'</option>
		</select>';

		return $html;
	}

	public function getContent()
	{
		$this->_html .= '<table width="100%" cellspacing="30"><tr><td colspan="2" align="center">';
		// Validate + save their input
		$errors = '';
		if (Tools::getValue('paymentsense_SUBMIT') != '')
		{
			// Prestashop's pSQL prevents XSS and SQL injection for us using the pSQL function :)
			if (Tools::getValue('PAYMENTSENSE_GATEWAYID') == '')
			{
				$errors .= '<li><b>'.$this->l('Gateway ID').'</b> - '.
				$this->l('The Gateway ID field can\'t be left blank. Please check the correct value with PaymentSense if you\'re unsure.').'</li>';
			}
			else
			{
				Configuration::updateValue('PAYMENTSENSE_GATEWAYID', Tools::getValue('PAYMENTSENSE_GATEWAYID'));
				Configuration::updateValue('PAYMENTSENSE_GATEWAYPASS', Tools::getValue('PAYMENTSENSE_GATEWAYPASS'));
				Configuration::updateValue('PAYMENTSENSE_PSK', Tools::getValue('PAYMENTSENSE_PSK'));
				Configuration::updateValue('PAYMENTSENSE_DEBUG', Tools::getValue('PAYMENTSENSE_DEBUG'));
				Configuration::updateValue('PAYMENTSENSE_TRANSACTION_TYPE', Tools::getValue('PAYMENTSENSE_TRANSACTION_TYPE'));
			}
		}
		else
			$errors .= '<li>'.$this->l('Problem updating settings, invalid information. 
			If this problem persists get in touch, devsupport@paymentsense.com').'</li>';

		$image_url = 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/'.$this->name.'/img/';
		// Display the USP
		$this->_html .= '<table width="800px" cellspacing="10">
		<tr>
			<td valign="top"><img src="'.$image_url.'PaymentSenseLogo.png" width="181px" height="55px"></td>
			<td></td>
			<td></td>
			<td colspan="2" height="70" valign="middle">
				<img src="'.$image_url.'Tagline.png" width="221px" height="22px">
			</td>
		</tr>
		<tr>
			<td colspan="5" >
				<table style="width:751px; border-bottom: 1px solid #969aa2;">
					<tr>
						<td height="1px"></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="3" height="70px" style="font-size:16px; padding-top:10px;">
				'.$this->l('PaymentSense make it simple and affordable for you to take credit and debit card payments online').'
			</td>
			<td rowspan="2" colspan="2" align="center" style="padding-top:24px;">
				<a href="http://www.paymentsense.co.uk/prestashop/" target="blank">
					<img src="'.$image_url.'TelephoneBanner.png" width="217px" height="146px">
				</a>
			</td>
		</tr>
		<tr>
			<td colspan="3" valign="top" style="font-size:16px;">
				'.$this->l('As the UK&#39s largest Merchant Service Provider, thousands of small businesses use our card payment solutions, and here&#39s why:').'
			</td>
		</tr>
		<tr>
			<td rowspan="3" width="33%" align="center">
				<img src="'.$image_url.'ecomPlaceholder.jpg" width="250px" height="181px"></br>
			</td>
			</td>
			<td style="padding-left:10px;">
				</br>
				<img src="'.$image_url.'24hIcon.png" width="27px" height="28px">
			</td>
			<td width="33%" style="padding-right:10px; font-size:13px;">
				</br>
				'.$this->l('24 hour setup - fastest merchant service in the UK').'
			</td>
			<td>
				</br>
				<img src="'.$image_url.'EcommIcon.png" width="32px" height="30px">
			</td>
			<td width="33%" style="font-size:13px; padding-right:15px;">
				</br>
				'.$this->l('Zero downtime - 100% reliable e-commerce service').'
			</td>
		</tr>
		<tr>
			<td style="padding-left:10px;">
				<img src="'.$image_url.'PricinfIcon.png" width="31px" height="30px">
			</td>
			<td style="padding-right:10px; font-size:13px;">
				'.$this->l('Perfect Pricing - plans starting from just &pound10/month').'
			</td>
			<td>
				<img src="'.$image_url.'PCIIcon.png" width="24px" height="38px">
			</td>
			<td style="font-size:13px; padding-right:5px;">
				'.$this->l('PCI-DSS - our payment gateway is entirely PCI Tier 1 compliant').'
			</td>
		</tr>
		<tr>
			<td valign="top" style="padding-left:10px;">
				<img src="'.$image_url.'NoCommitmentIcon.png" width="22px" height="34px">
			</td>
			<td valign="top" style="padding-right:10px; font-size:13px;">
				'.$this->l('No commitment - no contract and no cancellation fees').'
			</td>
			<td valign="top">
				<img src="'.$image_url.'IntegrationIcon.png" width="32px" height="39px">
			</td>
			<td valign="top" style="font-size:13px; padding-right:10px;">
				'.$this->l('Quick and easy integration with PrestaShop').'
			</td>
		</tr>
		</table>
		<br>';

			// Display errors / confirmation
			if (Tools::getValue('paymentsense_SUBMIT') != '')
			{
				if (preg_match('/merchant/', Tools::getValue('PAYMENTSENSE_GATEWAYID')) || !preg_match( '/^([a-zA-Z0-9]{6})([-])([0-9]{7})$/', Tools::getValue('PAYMENTSENSE_GATEWAYID')))
					$errors .= '<li><b>'.$this->l('Invalid Gateway Merchant ID').'</b> - Your Gateway Merchant ID should contain 
					<strong>the first 6 characters of the company name followed by a hyphen (-) and 7 numbers</strong>';

				if (Tools::strlen(Tools::getValue('PAYMENTSENSE_GATEWAYPASS')) < 10)
					$errors .= '<li><b>'.$this->l('Invalid Gateway Password').'</b> - 
					Your gateway password is too short, this should contain 10 characters including 3 numbers. 
					This password does <strong>NOT</strong> contain a symbol';

				if ($errors)
					$this->_html .= '<div style="width:700px; margin-right:40px;" class="alert error"><ul>'.
					$errors.'</ul></div>';

				else
					$this->_html .= '<div style="width:700px; margin-right:40px;" class="conf confirm">'.
					$this->l('Changes have all been saved').'</div>';

			}

			// Display the form
			$this->_html .= '<form action="'.htmlentities($_SERVER['REQUEST_URI'], ENT_COMPAT, 'UTF-8').'" method="post">
			<table width="945px"><tr><td width="247px" align="right">';

			//Display options
			$this->_html .= '</tr></table>
			<fieldset style="height:360px; width:730px; margin-right:20px;">
			<table width="700px" cellspacing="20" align="center">
			<tr><td colspan="2" style="padding-bottom:10px; font-size:16px;">'.$this->l('Enter your gateway merchant details below and click save to begin taking payments.').'</td></tr>
			<tr><td align="right">
			<strong>'.htmlentities($this->l('Gateway MerchantID:'), ENT_COMPAT | ENT_HTML401, 'UTF-8').'</strong></td><td align="left"><input name="PAYMENTSENSE_GATEWAYID" type="text" value="'.htmlentities($this->getSetting('PAYMENTSENSE_GATEWAYID'), ENT_COMPAT | ENT_HTML401, 'UTF-8').'" />
			</td></tr><tr><td align="right">
			<strong>'.htmlentities($this->l('Gateway Password:'), ENT_COMPAT | ENT_HTML401, 'UTF-8').'</strong></td><td align="left"><input name="PAYMENTSENSE_GATEWAYPASS" type="text" value="'.htmlentities($this->getSetting('PAYMENTSENSE_GATEWAYPASS'), ENT_COMPAT | ENT_HTML401, 'UTF-8').'" />
			</td></tr><tr><td width="50%" align="right">
			<strong>'.htmlentities($this->l('Pre-Shared Key:'), ENT_COMPAT | ENT_HTML401, 'UTF-8').'</strong></td><td align="left"><input type="text" name="PAYMENTSENSE_PSK" value="'.htmlentities($this->getSetting('PAYMENTSENSE_PSK'), ENT_COMPAT | ENT_HTML401, 'UTF-8').'"/>
			</td></tr><tr><td colspan="" align="right">'.
			$this->trueFalseOption('PAYMENTSENSE_DEBUG', 'Debug Mode', 'On', 'Off').
			'</td></tr><tr><td colspan=""  align="right">'.
			$this->trueFalseOption('PAYMENTSENSE_TRANSACTION_TYPE', 'Transaction Type', 'SALE', 'PREAUTH').
			'</td></tr><tr><td colspan="2" align="center" style="padding-top:20px;">
			<input style="background: url('.$image_url.'BlueButtonBackground.png) no-repeat; border: none; cursor:pointer;
			cursor:hand; width:170px; height:38px; color:white; font-weight:bold;" type="submit" name="paymentsense_SUBMIT" id="paymentsense_SUBMIT" value="'.$this->l('Save your changes').'" /></form>
			</td></tr></table></fieldset>';

		$this->_html .= '</td></tr><tr><td colspan="2" align="center">
		<table width="900px">
		<tr>
		<td align="center"><a href="http://www.paymentsense.co.uk/prestashop/" target="blank"><font color="#0000FF"><strong>www.paymentsense.co.uk/prestashop/</strong></font></a></br>'.
		''.$this->l('Copyright &copy; 2013 PaymentSense Ltd. All rights reserved. PaymentSense, acting as an agent of FDR Limited, doing business as First Data Merchant Solutions, is registered with MasterCard / Visa as an Independent Sales Organisation and Member Service Provider of Bank of Scotland').
		'</td>
		</tr></table>';

		$this->_html .= '</td></tr></table>';
		return $this->_html;
	}

	public function hookPayment($params)
	{
		if (!$this->active)
		return;

		$address = new Address((int)($params['cart']->id_address_invoice));
		$customer = new Customer((int)($params['cart']->id_customer));
		$currency = $this->getCurrency();

		$psquery = 'SELECT id_currency FROM '._DB_PREFIX_.'module_currency WHERE id_module = '.(int)$this->id;
		$db = Db::getInstance();
		$queryresult = $db->getRow($psquery);
		$id1_currency = array_shift($queryresult);

		// get currency of current cart.
		$psquery1 = 'SELECT iso_code FROM '._DB_PREFIX_.'currency WHERE id_currency = '.(int)$params['cart']->id_currency;
		$queryresult1 = $db->getRow($psquery1);
		$cart_currency = array_shift($queryresult1);

		if (!$id1_currency || $id1_currency == -2)
			$id2_currency = Configuration::get('PS_CURRENCY_DEFAULT');
		elseif ($id1_currency == -1)
			$id2_currency = $params['cart']->id_currency;

		// get currency of current cart.
		$psquery2 = 'SELECT conversion_rate FROM '._DB_PREFIX_.'currency WHERE id_currency = '.(int)$params['cart']->id_currency;
		$queryresult2 = $db->getRow($psquery2);
		$cart_conversion_rate = array_shift($queryresult2);

		// Grab the order total and format it properly
		if ($params['cart']->id_currency != $id2_currency)
		{
			$price = $params['cart']->getOrderTotal(true, 3);
			$amount = number_format($price, 2, '.', '');
			$currencyps = $cart_currency;
		}
		else
		{
			$amount = number_format($params['cart']->getOrderTotal(true, 3), 2, '.', '');
			$currencyps = $cart_currency;
		}

		$amount = sprintf('%0.2f', $amount);
		$amount = preg_replace('/[^\d]+/', '', $amount);

		$orderTotal = $params['cart']->getOrderTotal(true, 3);

		$parameters = array();

		$module_url = 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/paymentsense/';

		$paymentsense_psk = $this->getSetting('PAYMENTSENSE_PSK');
		$paymentsense_gatewaypass = $this->getSetting('PAYMENTSENSE_GATEWAYPASS');
		if ($this->getSetting('PAYMENTSENSE_TRANSACTION_TYPE') == 'True')
			$paymentsense_transactiontype = 'SALE';

		else
			$paymentsense_transactiontype = 'PREAUTH';

		$datestamp = date('Y-m-d H:i:s O');
		$gatewayorderID = date('Ymd-His').'~'.$params['cart']->id;

		if ($address->phone != '')
			$PhoneNumber = $address->phone;

		else
			$PhoneNumber = $address->phone_mobile;

		switch ($currency->iso_code)
		{
			case 'GBP':
				$currencyISO = '826';
				break;
			case 'USD':
				$currencyISO = '840';
				break;
			case 'EUR':
				$currencyISO = '978';
				break;
			default:
				$currencyISO = htmlentities($currency->iso_code);
				break;
		}

		$HashString = 'PreSharedKey='.$paymentsense_psk;
		$HashString .= '&MerchantID='.$this->getSetting('PAYMENTSENSE_GATEWAYID');
		$HashString .= '&Password='.$paymentsense_gatewaypass;
		$HashString .= '&Amount='.$amount;
		$HashString .= '&CurrencyCode='.$this->getCurrencyISO($currencyps);
		$HashString .= '&EchoAVSCheckResult=True';
		$HashString .= '&EchoCV2CheckResult=True';
		$HashString .= '&EchoThreeDSecureAuthenticationCheckResult=True';
		$HashString .= '&EchoCardType=True';
		$HashString .= '&OrderID='.$gatewayorderID;
		$HashString .= '&TransactionType='.$paymentsense_transactiontype;
		$HashString .= '&TransactionDateTime='.$datestamp;
		$HashString .= '&CallbackURL='.$module_url.'success.php';
		$HashString .= '&OrderDescription='.$gatewayorderID;
		$HashString .= '&CustomerName='.$customer->firstname.' '.$customer->lastname;
		$HashString .= '&Address1='.$address->address1;
		$HashString .= '&Address2='.$address->address2;
		$HashString .= '&Address3=';
		$HashString .= '&Address4=';
		$HashString .= '&City='.$address->city;
		$HashString .= '&State=';
		$HashString .= '&PostCode='.$address->postcode;
		$HashString .= '&CountryCode='.$this->getCountryISO($address->country);
		$HashString .= '&EmailAddress='.$customer->email;
		$HashString .= '&PhoneNumber='.$PhoneNumber;
		$HashString .= '&EmailAddressEditable=False';
		$HashString .= '&PhoneNumberEditable=False';
		$HashString .= '&CV2Mandatory=True';
		$HashString .= '&Address1Mandatory=True';
		$HashString .= '&CityMandatory=True';
		$HashString .= '&PostCodeMandatory=True';
		$HashString .= '&StateMandatory=False';
		$HashString .= '&CountryMandatory=True';
		$HashString .= '&ResultDeliveryMethod=SERVER';
		$HashString .= '&ServerResultURL='.$module_url.'callback.php';
		$HashString .= '&PaymentFormDisplaysResult=False';
		$HashString .= '&ServerResultURLCookieVariables='.'';
		$HashString .= '&ServerResultURLFormVariables=orderTotal='.$orderTotal;
		$HashString .= '&ServerResultURLQueryStringVariables=';
		$HashDigest = sha1($HashString);

		$parameters['HashDigest'] = $HashDigest;
		$parameters['MerchantID'] = $this->getSetting('PAYMENTSENSE_GATEWAYID');
		$parameters['Amount'] = $amount;
		$parameters['CurrencyCode'] = $this->getCurrencyISO($currencyps);
		$parameters['EchoAVSCheckResult'] = 'True';
		$parameters['EchoCV2CheckResult'] = 'True';
		$parameters['EchoThreeDSecureAuthenticationCheckResult'] = 'True';
		$parameters['EchoCardType'] = 'True';
		$parameters['OrderID'] = $gatewayorderID;
		$parameters['TransactionType'] = $paymentsense_transactiontype;
		$parameters['TransactionDateTime'] = $datestamp;
		$parameters['CallbackURL'] = $module_url.'success.php';
		$parameters['OrderDescription'] = $gatewayorderID;
		$parameters['CustomerName'] = $customer->firstname.' '.$customer->lastname;
		$parameters['Address1'] = $address->address1;
		$parameters['Address2'] = $address->address2;
		$parameters['Address3'] = '';
		$parameters['Address4'] = '';
		$parameters['City'] = $address->city;
		$parameters['State'] = '';
		$parameters['PostCode'] = $address->postcode;
		$parameters['CountryCode'] = $this->getCountryISO($address->country);
		$parameters['EmailAddress'] = $customer->email;
		$parameters['PhoneNumber'] = $PhoneNumber;
		$parameters['EmailAddressEditable'] = 'False';
		$parameters['PhoneNumberEditable'] = 'False';
		$parameters['CV2Mandatory'] = 'True';
		$parameters['Address1Mandatory'] = 'True';
		$parameters['CityMandatory'] = 'True';
		$parameters['PostCodeMandatory'] = 'True';
		$parameters['StateMandatory'] = 'False';
		$parameters['CountryMandatory'] = 'True';
		$parameters['ResultDeliveryMethod'] = 'SERVER';
		$parameters['ServerResultURL'] = $module_url.'callback.php';
		$parameters['PaymentFormDisplaysResult'] = 'False';
		$parameters['ServerResultURLCookieVariables'] = '';
		$parameters['ServerResultURLFormVariables'] = 'orderTotal='.$orderTotal;
		$parameters['ServerResultURLQueryStringVariables'] = '';

		$parameters['ThreeDSecureCompatMode'] = 'false';
		$parameters['ServerResultCompatMode'] = 'false';

		$form_target = 'https://mms.paymentsensegateway.com/Pages/PublicPages/PaymentForm.aspx';

		$this->context->smarty->assign(array('parameters' => $parameters, 'form_target' => $form_target));
		return $this->display(__FILE__, '/views/templates/front/paymentsense.tpl');
	}

	public function generateorderdate()
	{
		$str = date('Ymd-His');
		return $str;
	}

	/* Helper functions */
	public function parseBoolString($boolString)
	{
		if (!$boolString || (strcasecmp($boolString, 'false') == 0) || $boolString == '0')
			return false;
		else
			return true;
	}

	public function formatAmount($amount, $minorUnits)
	{
		if (parseBoolString($minorUnits))
			$amount = $amount / 100;

		return (float)($amount);
	}

	public function currencySymbol($currencyCode)
	{
		switch ($currencyCode)
		{
			case 'GBP':
				return '&pound;';
				break;
			case 'USD':
				return '$';
				break;
			case 'EUR':
				return '&euro;';
				break;
			default:
				return htmlentities($currencyCode);
				break;
		}
	}

	public function checkParams($params)
	{
		return !((empty($params)) || (!array_key_exists('ps_merchant_reference', $params)) || (!array_key_exists('ps_payment_amount', $params)));
	}

	public function checkChecksum($secretKey, $amount, $currencyCode, $merchantRef, $paymentsenseRef, $paymentsenseChecksum)
	{
		if (empty($secretKey))
			return array(true, 'checksum ignored, no secretkey');
		else
		{
			if (empty($paymentsenseChecksum))
				return array(false, 'checksum expected but missing (check secret key)');
			else
			{
				$checksum = sha1($amount.$currencyCode.$merchantRef.$paymentsenseRef.$secretKey);
				if ($checksum != $paymentsenseChecksum)
					return array(false, 'checksum mismatch (check secret key)');
				else
					return array(true, 'checksum matched');
			}
		}
	}

	public function getCurrencyISO($currencyISO)
	{
		$currencies = array('ARS' => 32, 'AUD' => 36, 'BRL' => 986, 'CAD' => 124, 'CHF' => 756, 'CLP' => 152, 'CNY' => 156,
		'COP' => 170, 'CZK' => 203, 'DKK' => 208, 'EUR' => 978, 'GBP' => 826, 'HKD' => 344, 'HUF' => 348, 'IDR' => 360,
		'ISK' => 352, 'JPY' => 392, 'KES' => 404, 'KRW' => 410, 'MXN' => 484, 'MYR' => 458, 'NOK' => 578, 'NZD' => 554,
		'PHP' => 608, 'PLN' => 985, 'SEK' => 752, 'SGD' => 702, 'THB' => 764, 'TWD' => 901, 'USD' => 840, 'VND' => 704, 'ZAR' => 710);

		if ($currencies[strtoupper($currencyISO)])
			return $currencies[strtoupper($currencyISO)];

		return 'error - cannot find currency';
	}

	public function getCountryISO($country_long_name)
	{
		$countries = array('United Kingdom' => 826, 'United States' => 840, 'Australia' => 36, 'Canada' => 124, 'France' => 250, 'Germany' => 276,
		'Afghanistan' => 4, 'Åland Islands' => 248, 'Albania' => 8, 'Algeria' => 12, 'American Samoa' => 16, 'Andorra' => 20, 'Angola' => 24,
		'Anguilla' => 660, 'Antarctica' => 10, 'Antigua and Barbuda' => 28, 'Argentina' => 32, 'Armenia' => 51, 'Aruba' => 533, 'Austria' => 40,
		'Azerbaijan' => 31, 'Bahamas' => 44, 'Bahrain' => 48, 'Bangladesh' => 50, 'Barbados' => 52, 'Belarus' => 112, 'Belgium' => 56, 'Belize' => 84,
		'Benin' => 204, 'Bermuda' => 60, 'Bhutan' => 64, 'Bolivia' => 68, 'Bosnia and Herzegovina' => 70, 'Botswana' => 72, 'Bouvet Island' => 74,
		'Brazil Federative' => 76, 'British Indian Ocean Territory' => 86, 'Brunei' => 96, 'Bulgaria' => 100, 'Burkina Faso' => 854, 'Burundi' => 108,
		'Cambodia' => 116, 'Cameroon' => 120, 'Cape Verde' => 132, 'Cayman Islands' => 136, 'Central African Republic' => 140, 'Chad' => 148, 'Chile' => 152,
		'China' => 156, 'Christmas Island' => 162, 'Cocos (Keeling) Islands' => 166, 'Colombia' => 170, 'Comoros' => 174, 'Congo' => 180, 'Congo' => 178,
		'Cook Islands' => 184, 'Costa Rica' => 188, 'Côte d\'Ivoire' => 384, 'Croatia' => 191, 'Cuba' => 192, 'Cyprus' => 196, 'Czech Republic' => 203,
		'Denmark' => 208, 'Djibouti' => 262, 'Dominica' => 212, 'Dominican Republic' => 214, 'East Timor' => 626, 'Ecuador' => 218, 'Egypt' => 818,
		'El Salvador' => 222, 'Equatorial Guinea' => 226, 'Eritrea' => 232, 'Estonia' => 233, 'Ethiopia' => 231, 'Falkland Islands (Malvinas)' => 238,
		'Faroe Islands' => 234, 'Fiji' => 242, 'Finland' => 246, 'French Guiana' => 254, 'French Polynesia' => 258, 'French Southern Territories' => 260,
		'Gabon' => 266, 'Gambia' => 270, 'Georgia' => 268, 'Ghana' => 288, 'Gibraltar' => 292, 'Greece' => 300, 'Greenland' => 304, 'Grenada' => 308,
		'Guadaloupe' => 312, 'Guam' => 316, 'Guatemala' => 320, 'Guernsey' => 831, 'Guinea' => 324, 'Guinea-Bissau' => 624, 'Guyana' => 328, 'Haiti' => 332,
		'Heard Island and McDonald Islands' => 334, 'Honduras' => 340, 'Hong Kong' => 344, 'Hungary' => 348, 'India' => 352, 'Indonesia' => 360,
		'Iran' => 364, 'Iraq' => 368, 'Ireland' => 372, 'Isle of Man' => 833, 'Israel' => 376, 'Italy' => 380, 'Jamaica' => 388, 'Japan' => 392,
		'Jersey' => 832, 'Jordan' => 400, 'Kazakhstan' => 398, 'Kenya' => 404, 'Kiribati' => 296, 'Korea' => 410, 'Korea' => 408, 'Kuwait' => 414,
		'Kyrgyzstan' => 417, 'Lao' => 418, 'Latvia' => 428, 'Lebanon' => 422, 'Lesotho' => 426, 'Liberia' => 430, 'Libyan Arab Jamahiriya' => 434,
		'Liechtenstein' => 438,	'Lithuania' => 440, 'Luxembourg' => 442, 'Macau' => 446, 'Macedonia' => 807, 'Madagascar' => 450, 'Malawi' => 454,
		'Malaysia' => 458, 'Maldives' => 462, 'Mali' => 466, 'Malta' => 470, 'Marshall Islands' => 584, 'Martinique' => 474, 'Mauritania Islamic' => 478,
		'Mauritius' => 480, 'Mayotte' => 175, 'Mexico' => 484, 'Micronesia' => 583, 'Moldova' => 498, 'Monaco' => 492, 'Mongolia' => 496,
		'Montenegro' => 499, 'Montserrat' => 500, 'Morocco' => 504, 'Mozambique' => 508, 'Myanmar' => 104, 'Namibia' => 516, 'Nauru' => 520, 'Nepal' => 524,
		'Netherlands' => 528, 'Netherlands Antilles' => 530, 'New Caledonia' => 540, 'New Zealand' => 554, 'Nicaragua' => 558, 'Niger' => 562,
		'Nigeria' => 566, 'Niue' => 570, 'Norfolk Island' => 574, 'Northern Mariana Islands' => 580, 'Norway' => 578, 'Oman' => 512, 'Pakistan' => 586,
		'Palau' => 585, 'Palestine' => 275,	'Panama' => 591, 'Papua New Guinea' => 598, 'Paraguay' => 600, 'Peru' => 604, 'Philippines' => 608,
		'Pitcairn' => 612, 'Poland' => 616, 'Portugal' => 620, 'Puerto Rico' => 630, 'Qatar' => 634, 'Réunion' => 638, 'Romania' => 642,
		'Russian Federation' => 643, 'Rwanda' => 646, 'Saint Barthélemy' => 652, 'Saint Helena' => 654, 'Saint Kitts and Nevis' => 659, 'Saint Lucia' => 662,
		'Saint Martin (French part)' => 663, 'Saint Pierre and Miquelon' => 666, 'Saint Vincent and the Grenadines' => 670,	'Samoa' => 882,
		'San Marino' => 674, 'São Tomé and Príncipe Democratic' => 678, 'Saudi Arabia' => 682, 'Senegal' => 686, 'Serbia' => 688, 'Seychelles' => 690,
		'Sierra Leone' => 694, 'Singapore' => 702, 'Slovakia' => 703, 'Slovenia' => 705, 'Solomon Islands' => 90, 'Somalia' => 706, 'South Africa' => 710,
		'South Georgia and the South Sandwich Islands' => 239, 'Spain' => 724,	'Sri Lanka' => 144,	'Sudan' => 736,	'Suriname' => 740,
		'Svalbard and Jan Mayen' => 744, 'Swaziland' => 748, 'Sweden' => 752, 'Switzerland' => 756, 'Syrian Arab Republic' => 760, 'Taiwan' => 158,
		'Tajikistan' => 762, 'Tanzania' => 834, 'Thailand' => 764, 'Togo' => 768, 'Tokelau' => 772, 'Tonga' => 776,	'Trinidad and Tobago' => 780,
		'Tunisia' => 788, 'Turkey' => 792, 'Turkmenistan' => 795, 'Turks and Caicos Islands' => 796, 'Tuvalu' => 798, 'Uganda' => 800, 'Ukraine' => 804,
		'United Arab Emirates' => 784, 'United States Minor Outlying Islands' => 581, 'Uruguay Eastern' => 858, 'Uzbekistan' => 860, 'Vanuatu' => 548,
		'Vatican City State' => 336, 'Venezuela' => 862, 'Vietnam' => 704, 'Virgin Islands, British' => 92, 'Virgin Islands, U.S.' => 850,
		'Wallis and Futuna' => 876,	'Western Sahara' => 732, 'Yemen' => 887, 'Zambia' => 894, 'Zimbabwe' => 716);

		if (isset($countries[$country_long_name]))
			return $countries[$country_long_name];

		return 'error - cannot find country';
	}
}
