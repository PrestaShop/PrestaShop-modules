<?php
/*
* Adyen Payment Module
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
*  @author Rik ter Beek <rikt@adyen.com>
*  @copyright  Copyright (c) 2013 Adyen (http://www.adyen.com)
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class AdyenValidationModuleFrontController extends ModuleFrontController
{
	/**
	 *
	 * @see FrontController::postProcess()
	 */
	public function postProcess()
	{
		$cart = $this->context->cart;
		if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
			Tools::redirect('index.php?controller=order&step=1');
			
			// Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
		$authorized = false;
		foreach (Module::getPaymentModules() as $module)
			if ($module['name'] == 'adyen')
			{
				$authorized = true;
				break;
			}
		if (!$authorized)
			die($this->module->l('This payment method is not available.', 'validation'));
		
		$customer = new Customer($cart->id_customer);
		if (!Validate::isLoadedObject($customer))
			Tools::redirect('index.php?controller=order&step=1');
			
			// get the selected currency
		$currency = $this->context->currency;
		
		$total = (float)$cart->getOrderTotal(true, Cart::BOTH);
		
		// validate order
		// $payment_method name must be the same as module name otherwise successurl won't show
		$this->module->validateOrder($cart->id, Configuration::get('ADYEN_NEW_STATUS'), $total, 'Adyen', null, array (), (int)$currency->id, false, $customer->secure_key);
		Logger::addLog('Adyen module: order is validated for id_order '.$cart->id);
		
		// go to form adyen post values (submitted automatically)
		$config = Configuration::getMultiple(array (
				'ADYEN_MERCHANT_ACCOUNT',
				'ADYEN_MODE',
				'ADYEN_SKIN_CODE',
				'ADYEN_HMAC_TEST',
				'ADYEN_HMAC_LIVE',
				'ADYEN_NOTI_USERNAME',
				'ADYEN_NOTI_PASSWORD',
				'ADYEN_DAYS_DELIVERY',
				'PS_SSL_ENABLED',
				'ADYEN_COUNTRY_CODE_ISO',
				'ADYEN_LANGUAGE_LOCALE'
		));
		
		$customer = new Customer((int)$cart->id_customer);
		$address = new Address((int)$cart->id_address_invoice);
		$country = new Country((int)($address->id_country));
		$language = Language::getIsoById((int)$cart->id_lang);
		
		if (!Validate::isLoadedObject($address) || !Validate::isLoadedObject($customer) || !Validate::isLoadedObject($currency))
		{
			Logger::addLog('Adyen module: invalid address, customer, or currency for id_order '.$cart->id, 4);
			return $this->module->l('Adyen error: (invalid address, customer, or currency)');
		}
		
		$merchant_account = (string)$config['ADYEN_MERCHANT_ACCOUNT'];
		$skin_code = (string)$config['ADYEN_SKIN_CODE'];
		$currency_code = (string)$currency->iso_code;
		$shopper_email = (string)$customer->email;
		$merchant_reference = (int)$this->module->currentOrder; // set when order is validated
		
		$payment_amount = number_format($cart->getOrderTotal(true, 3), 2, '', '');
		
		$shopper_reference = (string)$customer->secure_key;
		
		if ($config['ADYEN_COUNTRY_CODE_ISO'] != '')
			$country_code = (string)$config['ADYEN_COUNTRY_CODE_ISO'];
		else
			$country_code = (string)$country->iso_code;
			
			// Locale (language) to present to shopper (e.g. en_US, nl, fr, fr_BE)
		if ($config['ADYEN_LANGUAGE_LOCALE'] != '')
			$shopper_locale = (string)$config['ADYEN_LANGUAGE_LOCALE'];
		else
			$shopper_locale = (string)$language;
		
		$recurring_contract = 'ONECLICK';
		$ship_before_date = date('Y-m-d', mktime(date('H'), date('i'), date('s'), date('m'), date('j') + (isset($config['ADYEN_DAYS_DELIVERY']) ? $config['ADYEN_DAYS_DELIVERY'] : 5), date('Y'))); // example: ship in 5 days
		$session_validity = date(DATE_ATOM, mktime(date('H') + 1, date('i'), date('s'), date('m'), date('j'), date('Y')));
		
		// presentation of the shopping basket.
		$tax_calculation_method = Group::getPriceDisplayMethod((int)Group::getCurrent()->id);
		$use_tax = !($tax_calculation_method == PS_TAX_EXC);
		
		$shipping_cost = Tools::displayPrice($cart->getOrderTotal($use_tax, Cart::ONLY_SHIPPING), $currency);

		$prod_details = sprintf('Shipment cost: %s <br />', $shipping_cost);
		$prod_details .= 'Order rows: <br />';
		
		// get order items
		foreach ($cart->getProducts() as $product)
		{
			$name = $product['name'];
			$qty_ordered = (int)$product['cart_quantity'];
			$row_total = Tools::ps_round($product['total_wt'], 2);
			$prod_details .= sprintf('%s ( Qty: %s ) ( Price: %s %s ) <br />', $name, $qty_ordered, $row_total, $currency_code);
		}
		
		$order_data = base64_encode(gzencode($prod_details));
		
		// for elv and cc can be mutliple values seperate by comma(,)
		$blocked_methods = '';
		
		$hmac_data = $payment_amount.$currency_code.$ship_before_date.$merchant_reference.$skin_code.$merchant_account.$session_validity.$shopper_email.$shopper_reference.$recurring_contract.$blocked_methods;
		
		$merchant_sig = base64_encode(pack('H*', $this->module->getHmacsha1($this->module->getHmac(), $hmac_data)));
		
		$brand_code = '';
		$ideal_issuer_id = '';
		$skip_selection = '';
		
		if (Tools::getValue('payment_type') != '')
			$brand_code = (string)Tools::getValue('payment_type');
		
		if (Tools::getValue('ideal_type') != '')
		{
			$ideal_issuer_id = (int)Tools::getValue('ideal_type');
			$skip_selection = 'true';
		}
		
		$this->context->smarty->assign(array (
				'merchantAccount' => $merchant_account,
				'skinCode' => $skin_code,
				'currencyCode' => $currency_code,
				'shopperEmail' => $shopper_email,
				'merchantReference' => $merchant_reference,
				'paymentAmount' => $payment_amount,
				'shopperReference' => $shopper_reference,
				'shipBeforeDate' => $ship_before_date,
				'sessionValidity' => $session_validity,
				'shopperLocale' => $shopper_locale,
				'countryCode' => $country_code,
				'orderData' => $order_data,
				'recurringContract' => $recurring_contract,
				'merchantSig' => $merchant_sig,
				'adyenUrl' => $this->getAdyenUrl($brand_code, $ideal_issuer_id),
				'resURL' => ($config['PS_SSL_ENABLED'] ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'index.php?controller=order-confirmation&key='.$customer->secure_key.'&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.(int)$this->module->currentOrder,
				'brandCode' => $brand_code, // for ideal payment
				'skipSelection' => $skip_selection, // for ideal payment
				'idealIssuerId' => $ideal_issuer_id // for ideal payment
		));
	}
	public function initContent()
	{
		parent::initContent();
		$this->setTemplate('adyen.tpl');
	}
	public function getAdyenUrl($brand_code, $ideal_issuer_id)
	{
		if ($brand_code == 'ideal' && $ideal_issuer_id != '')
		{
			if (Configuration::get('ADYEN_MODE') == 'live')
				$main_url = 'https://live.adyen.com/hpp/redirectIdeal.shtml';
			else
				$main_url = 'https://test.adyen.com/hpp/redirectIdeal.shtml';
		} elseif ($brand_code != '')
		{
			if (Configuration::get('ADYEN_MODE') == 'live')
				$main_url = 'https://live.adyen.com/hpp/details.shtml?brandCode='.$brand_code;
			else
				$main_url = 'https://test.adyen.com/hpp/details.shtml?brandCode='.$brand_code;
		} elseif (Configuration::get('ADYEN_PAYMENT_FLOW') == 'single')
		{
			if (Configuration::get('ADYEN_MODE') == 'live')
				$main_url = 'https://live.adyen.com/hpp/pay.shtml';
			else
				$main_url = 'https://test.adyen.com/hpp/pay.shtml';
		} else
		{
			if (Configuration::get('ADYEN_MODE') == 'live')
				$main_url = 'https://live.adyen.com/hpp/select.shtml';
			else
				$main_url = 'https://test.adyen.com/hpp/select.shtml';
		}
		return $main_url;
	}
}
