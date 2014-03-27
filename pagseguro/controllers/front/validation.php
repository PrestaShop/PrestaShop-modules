<?php
/*
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class PagSeguroValidationModuleFrontController extends ModuleFrontController
{
	private $_payment_request;

	/**
	*  Post data process function
	*/
	public function postProcess()
	{
		$this->_verifyPaymentOptionAvailability();
		$this->_validateCart();
		$this->_generatePagSeguroRequestData();
		$additional_infos = $this->_validateOrder();
		$this->_setAdditionalRequestData($additional_infos);
		$this->_setNotificationUrl();
		$this->_performPagSeguroRequest();
	}

	/**
	* Set additional infos to PagSeguroPaymentRequest object
	* @param array $additional_infos
	*/
	private function _setAdditionalRequestData(Array $additional_infos)
	{
		/* Setting reference */
		$this->_payment_request->setReference($additional_infos['id_order']);

		/* Setting redirect URL */
		if (Tools::isEmpty($this->_payment_request->getRedirectURL()))
			$this->_payment_request->setRedirectURL($this->_generateRedirectUrl($additional_infos));
	}

	/**
	* set notification url
	*/
	private function _setNotificationUrl()
	{
		$obj_ps = new PagSeguro();
		$this->_payment_request->setNotificationURL($obj_ps->getNotificationUrl());
	}

	/**
	*  Verify if PagSeguro payment module still available
	*/
	private function _verifyPaymentOptionAvailability()
	{
		$authorized = false;
		foreach (Module::getPaymentModules() as $module)
			if ($module['name'] == 'pagseguro')
			{
				$authorized = true;
				break;
			}

		if (!$authorized)
			die($this->module->l('Este método de pagamento não está disponível', 'validation'));
	}

	/**
	*  Validate Cart
	*/
	private function _validateCart()
	{
		if ($this->context->cart->id_customer == 0 || $this->context->cart->id_address_delivery == 0 || $this->context->cart->id_address_invoice == 0 || !$this->module->active)
			Tools::redirect('index.php?controller=order&step=1');
	}

	/**
	*  Validate order
	*/
	private function _validateOrder()
	{
		$customer = new Customer($this->context->cart->id_customer);
		if (!Validate::isLoadedObject($customer))
			Tools::redirect('index.php?controller=order&step=1');

		$this->module->validateOrder((int)$this->context->cart->id, Configuration::get('PS_OS_PAGSEGURO'), (float)$this->context->cart->getOrderTotal(true, Cart::BOTH), $this->module->displayName, null, null, (int)$this->context->currency, false, $customer->secure_key);

		return array('id_cart' => (int)$this->context->cart->id, 'id_module' => (int)$this->module->id, 'id_order' => $this->module->currentOrder, 'key' => $customer->secure_key);
	}

	/**
	*  After system and PagSeguro validations and notification about order,
	*  client will be redirected to order confirmation view with a button that
	*  allows client to access PagSeguro and perform him order payment
	* 
	* @param array $arrayData
	*/
	private function _generateRedirectUrl(Array $arrayData)
	{
		return _PS_BASE_URL_.__PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.$arrayData['id_cart'].'&id_module='.$arrayData['id_module'].'&id_order='.$arrayData['id_order'].'&key='.$arrayData['key'];
	}

	/**
	*  Perform PagSeguro request and return url from PagSeguro
	*  if ok, $this->module->pagSeguroReturnUrl is created with url returned from Pagseguro
	*/
	private function _performPagSeguroRequest()
	{
		try
		{
			/* Retrieving PagSeguro configurations */
			$this->_retrievePagSeguroConfiguration();

			/* Set PagSeguro Prestashop module version */
			$this->_setPagSeguroModuleVersion();

			/* Set PagSeguro PrestaShop CMS version */
			$this->_setPagSeguroCMSVersion();

			/* Performing request */
			$credentials = new PagSeguroAccountCredentials(Configuration::get('PAGSEGURO_EMAIL'), Configuration::get('PAGSEGURO_TOKEN'));
			$url = $this->_payment_request->register($credentials);

			/* Redirecting to PagSeguro */
			if (Validate::isUrl($url))
				Tools::redirectLink (Tools::truncate($url, 255, ''));
		}
		catch(PagSeguroServiceException $e)
		{
			die($e->getMessage());
		}
	}

	/**
	* Retrieve PagSeguro data configuration from database
	*/
	private function _retrievePagSeguroConfiguration()
	{
		/* Retrieving configurated default charset */
		PagSeguroConfig::setApplicationCharset(Configuration::get('PAGSEGURO_CHARSET'));

		/* Retrieving configurated default log info */
		if (Configuration::get('PAGSEGURO_LOG_ACTIVE'))
			PagSeguroConfig::activeLog(_PS_ROOT_DIR_.Configuration::get('PAGSEGURO_LOG_FILELOCATION'));
	}

	/**
	* Set PagSeguro PrestaShop module version
	*/
	private function _setPagSeguroModuleVersion()
	{
		PagSeguroLibrary::setModuleVersion('prestashop-v.'.$this->module->version);
	}

	/**
	* Set PagSeguro CMS version
	*/
	private function _setPagSeguroCMSVersion()
	{
		PagSeguroLibrary::setCMSVersion('prestashop-v.'._PS_VERSION_);
	}

	/**
	*  Generates PagSeguro request data
	*/
	private function _generatePagSeguroRequestData()
	{
		$payment_request = new PagSeguroPaymentRequest();
		$payment_request->setCurrency(PagSeguroCurrencies::getIsoCodeByName('Real')); /* Currency */
		$payment_request->setExtraAmount($this->_getExtraAmountValues()); /* Extra amount */
		$payment_request->setItems($this->_generateProductsData()); /* Products */
		$payment_request->setSender($this->_generateSenderData()); /* Sender */
		$payment_request->setShipping($this->_generateShippingData()); /* Shipping */
		if (!Tools::isEmpty(Configuration::get('PAGSEGURO_URL_REDIRECT'))) /* Redirect URL */
			$payment_request->setRedirectURL(Configuration::get('PAGSEGURO_URL_REDIRECT'));
		$this->_payment_request = $payment_request;
	}

	/**
	* Gets extra amount values for order
	* @return float
	*/
	private function _getExtraAmountValues()
	{
		return Tools::convertPrice($this->_getCartRulesValues() + $this->_getWrappingValues());
	}

	/**
	* Gets cart rules values
	* @return float
	*/
	private function _getCartRulesValues()
	{
		$rules_values = (float)0;

		$cart_rules = $this->context->cart->getCartRules();
		if (count($cart_rules) > 0)
			foreach ($cart_rules as $rule)
				$rules_values += $rule['value_real'];

		return number_format(Tools::ps_round($rules_values, 2), 2, '.', '') * -1;
	}

	/**
	* Gets wrapping values for order
	* @return float
	*/
	private function _getWrappingValues()
	{
		return number_format(Tools::ps_round($this->context->cart->getOrderTotal(true, Cart::ONLY_WRAPPING), 2), 2, '.', '');
	}

	/**
	*  Generates products data to PagSeguro transaction
	* 
	*  @return Array PagSeguroItem
	*/
	private function _generateProductsData()
	{
		$pagseguro_items = array();

		$cont = 1;

		foreach ($this->context->cart->getProducts() as $product)
		{
			$pagSeguro_item = new PagSeguroItem();
			$pagSeguro_item->setId($cont++);
			$pagSeguro_item->setDescription(Tools::truncate($product['name'], 255));
			$pagSeguro_item->setQuantity($product['quantity']);
			$pagSeguro_item->setAmount($product['price_wt']);
			$pagSeguro_item->setWeight($product['weight'] * 1000); /* Defines weight in grams */

			if ($product['additional_shipping_cost'] > 0)
				$pagSeguro_item->setShippingCost($product['additional_shipping_cost']);

			array_push($pagseguro_items, $pagSeguro_item);
		}

		return $pagseguro_items;
	}

	/**
	*  Generates sender data to PagSeguro transaction
	* 
	*  @return PagSeguroSender
	*/
	private function _generateSenderData()
	{
		$sender = new PagSeguroSender();

		if (isset($this->context->customer) && !is_null($this->context->customer))
		{
			$sender->setEmail($this->context->customer->email);
			$name = $this->_generateName($this->context->customer->firstname).' '.$this->_generateName($this->context->customer->lastname);
			$sender->setName(Tools::truncate($name, 50));
		}

		return $sender;
	}

	/**
	* Generate name 
	* @param type $value
	* @return string
	*/
	private function _generateName($value)
	{
		$name = '';
		$cont = 0;
		$customer = explode(' ', $value );
		foreach ($customer as $first)
		{
			if (!Tools::isEmpty($first))
				if ($cont == 0)
				{
					$name .= ($first);
					$cont++;
				}
				else 
					$name .= ' '.($first);
		}

		return $name;
	}

	/**
	*  Generates shipping data to PagSeguro transaction
	* 
	*  @return PagSeguroShipping
	*/
	private function _generateShippingData()
	{
		$shipping = new PagSeguroShipping();
		$shipping->setAddress($this->_generateShippingAddressData());
		$shipping->setType($this->_generateShippingType());
		$shipping->setCost(number_format($this->context->cart->getOrderTotal(true, Cart::ONLY_SHIPPING), 2));

		return $shipping;
	}

	/**
	*  Generate shipping type data to PagSeguro transaction
	* 
	*  @return PagSeguroShippingType
	*/
	private function _generateShippingType()
	{
		$shipping_type = new PagSeguroShippingType();
		$shipping_type->setByType('NOT_SPECIFIED');

		return $shipping_type;
	}

	/**
	*  Generates shipping address data to PagSeguro transaction
	* 
	*  @return PagSeguroAddress
	*/
	private function _generateShippingAddressData()
	{
		$address = new PagSeguroAddress();
		$delivery_address = new Address((int)$this->context->cart->id_address_delivery);

		if (!is_null($delivery_address))
		{
			$address->setCity($delivery_address->city);
			$address->setPostalCode($delivery_address->postcode);
			$address->setStreet($delivery_address->address1);
			$address->setDistrict($delivery_address->address2);
			$address->setComplement($delivery_address->other);
			$address->setCity($delivery_address->city);

			$country = new Country((int)$delivery_address->id_country);
			$address->setCountry($country->iso_code);

			$state = new State((int)$delivery_address->id_state);
			$address->setState($state->iso_code);
		}

		return $address;
	}
    
}
