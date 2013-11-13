<?php
/**
 * Ferbuy payment extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @category	Payment
 * @package		Payment_FerBuy
 * @author		FerBuy, <info@ferbuy.com>
 * @copyright   Copyright (c) 2013 (http://www.ferbuy.com)
 * @version		1.3.0
 * @license		http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
if (!defined('_PS_VERSION_'))
	exit;

class FerBuy extends PaymentModule
{
	private $html = '';
	private $post_errors = array();
	public $errors = array();
	protected $backward = false;

	/**
	 * Construct
	 */
	public function __construct()
	{
		$this->name = 'ferbuy';
		$this->tab = 'payments_gateways';
		$this->version = '1.3';
		$this->author = 'FerBuy';

		parent::__construct();

		$this->page = basename(__FILE__, '.php');
		$this->displayName = 'FerBuy';
		$this->description = $this->l('Accept payments with FerBuy.');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your settings?');

		// Module settings
		$this->setModuleSettings();

		// Check module requirements
		$this->checkModuleRequirements();

		/** Backward compatibility 1.4 and 1.5 */
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			$this->backward_error = $this->l('The FerBuy module requires the backward compatibility module with at least v0.3 in PrestaShop v1.4.').'<br />'.
			$this->l('You can download this module for free here: http://addons.prestashop.com/en/modules-prestashop/6222-backwardcompatibility.html');

			if (file_exists(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php'))
			{
				include(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
				$this->backward = true;
			}
			else
				$this->warning = $this->backward_error;
		}
		$this->backward = true;
	}

	/**
	 * FerBuy module instalation
	 * @return boolean install result
	 */
	public function install()
	{
		if (!$this->backward && version_compare(_PS_VERSION_, '1.5', '<'))
		{
			echo '<div class="error">'.Tools::safeOutput($this->backward_error).'</div>';
			return false;
		}

		if (!parent::install()
			|| !Configuration::updateValue('FERBUY_MODE', '')
			|| !Configuration::updateValue('FERBUY_SITEID', '')
			|| !Configuration::updateValue('FERBUY_SECRET', '')
			|| !$this->registerHook('payment')
			|| !$this->registerHook('paymentReturn')
			|| !$this->registerHook('postUpdateOrderStatus'))
			return false;

		return true;
	}

	/**
	 * FerBuy module uninstallation
	 * @return boolean uninstall result
	 */
	public function uninstall()
	{
		if (!parent::uninstall() || !Configuration::deleteByName('FERBUY_MODE'))
			return false;
		return true;
	}

	/**
	 * Display the back-office interface of the FerBuy module
	 * @return string FerBuy template
	 */
	public function getContent()
	{
		if (Tools::isSubmit('ferbuy_updateSettings'))
		{
			Configuration::updateValue('FERBUY_MODE', Tools::getValue('mode'));
			Configuration::updateValue('FERBUY_SITEID', Tools::getValue('site_id'));
			Configuration::updateValue('FERBUY_SECRET', Tools::getValue('secret'));

			$this->setModuleSettings();
			$this->checkModuleRequirements();
		}

		$this->smarty->assign(array(
			'errors' => $this->errors,
			'version' => $this->version,
			'img_ferbuylogo' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__."modules/{$this->name}/img/ferbuy.png",
			'verification_url' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__."modules/{$this->name}/validation.php",
			'data_mode' => $this->mode,
			'data_site_id' => $this->site_id,
			'data_secret' => $this->secret,
		));

		return $this->display($this->name, 'views/templates/admin/config.tpl');
	}

	/**
	 * MarkOrderShipped and Refunds functionality
	 */
	public function hookPostUpdateOrderStatus($params)
	{
		/* If 1.4 and no backward, then leave */
		if (!$this->backward && version_compare(_PS_VERSION_, '1.5', '<'))
			return;

		// Check if it is active
		if (!$this->active)
			return;

		// Check if it was filled the data in the backoffice
		if (!Configuration::get('FERBUY_MODE') || !Configuration::get('FERBUY_SITEID') || !Configuration::get('FERBUY_SECRET'))
			return;

		// Get the id of shopping cart
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			$id_cart = $params['id_order'];
		else
			$id_cart = $params['cart']->id;

		// Assign the template
		switch ($params['newOrderStatus']->id)
		{
			case Configuration::get('PS_OS_SHIPPING'):
				$command = 'Not set';
				if ($this->apiCall('MarkOrderShipped', Configuration::get('FERBUY_SITEID'), $id_cart, Configuration::get('FERBUY_SECRET'), $command))
					$this->addNewPrivateMessage ($id_cart, $this->l('The order has been marked as shipped in FerBuy').' '.date('Y-m-d H:i:s'));
				else
					$this->addNewPrivateMessage ($id_cart, $this->l('We were unable to mark the order as shipped in FerBuy. Please login to https://my.ferbuy.com/ to manually mark the order as shipped.').' '.date('Y-m-d H:i:s'));
				break;

			case Configuration::get('PS_OS_REFUND'):
				$command = 'full';
				if ($this->apiCall('RefundTransaction', Configuration::get('FERBUY_SITEID'), $id_cart, Configuration::get('FERBUY_SECRET'), $command))
					$this->addNewPrivateMessage ($id_cart, $this->l('The order has been marked as refunded in FerBuy').' '.date('Y-m-d H:i:s'));
				else
					$this->addNewPrivateMessage ($id_cart, $this->l('We were unable to mark the order as refunded in FerBuy. Please login to https://my.ferbuy.com/ to manually mark the order as refunded.').' '.date('Y-m-d H:i:s'));
				break;
		}

	}

	/**
	 * Create an internal message
	 * @param string $id_order
	 * @param string $message
	 * @return boolean
	 */
	private function addNewPrivateMessage($id_order, $message)
	{
		if (!(bool)$id_order)
			return false;

		$new_message = new Message();
		$message = strip_tags($message, '<br>');

		if (!Validate::isCleanHtml($message))
			$message = $this->l('Payment message is not valid, please check your module.');

		$new_message->message = $message;
		$new_message->id_order = (int)$id_order;
		$new_message->date_add = date('Y-m-d H:i:s');
		$new_message->private = 1;

		return $new_message->add();
	}

	/**
	 * Display the FerBuy payment form
	 * @return string FerBuy template content
	 */
	public function hookPayment($params)
	{
		/* If 1.4 and no backward, then leave */
		if (!$this->backward && version_compare(_PS_VERSION_, '1.5', '<'))
			return;

		// Check if it is active
		if (!$this->active)
			return;

		// Check if it was filled the data in the backoffice
		if (!Configuration::get('FERBUY_MODE') || !Configuration::get('FERBUY_SITEID') || !Configuration::get('FERBUY_SECRET'))
			return;

		// Load objects
		$address = new Address((int)$params['cart']->id_address_delivery);
		$country_obj = new Country((int)$address->id_country, Configuration::get('PS_LANG_DEFAULT'));
		$customer = new Customer((int)$params['cart']->id_customer);
		$currency = new Currency((int)$params['cart']->id_currency);
		$env = $this->mode;

		$arr = array();

		// About the cart
		$arr['amount'] = sprintf('%.0f', $params['cart']->getOrderTotal() * 100);
		$arr['currency'] = $currency->iso_code;

		// About the customer
		$arr['first_name'] = $address->firstname;
		$arr['last_name'] = $address->lastname;
		$arr['email'] = $customer->email;
		$arr['address'] = $address->address1;
		$arr['address_line2'] = $address->address2;
		$arr['city'] = $address->city;
		$arr['country_iso'] = isset($this->_country[strtoupper($country_obj->iso_code)]) ? $this->_country[strtoupper($country_obj->iso_code)] : '';
		$arr['language'] = $this->context->language->iso_code;
		$arr['postal_code'] = $address->postcode;
		$arr['mobile_phone'] = !empty($address->phone_mobile) ? $address->phone_mobile : $address->phone;

		// About the merchant
		$arr['site_id'] = $this->site_id;
		$arr['reference'] = $params['cart']->id;
		$arr['extra'] = $params['cart']->secure_key;
		$arr['shop_version'] = 'Prestashop '._PS_VERSION_;
		$arr['plugin_name'] = 'Presta-Ferbuy';
		$arr['plugin_version'] = $this->version;
		$url_order_confirmation = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'index.php?controller=order-confirmation';
		$arr['return_url_ok'] = $url_order_confirmation.'&id_cart='.(int)$params['cart']->id.'&id_module='.(int)$this->id.'&key='.$customer->secure_key;
		$arr['return_url_cancel'] = $url_order_confirmation.'&id_cart='.(int)$params['cart']->id.'&id_module='.(int)$this->id.'&key='.$customer->secure_key;

		// Calc checksum
		$arr['checksum'] = sha1(join('&', array(
			$env,
			$arr['site_id'],
			$arr['reference'],
			$arr['currency'],
			$arr['amount'],
			$arr['first_name'],
			$arr['last_name'],
			$this->secret
		)));

		//Get shopping cart
		$arr['shopping_cart'] = $this->getShoppingCart($params['cart']);

		//Assign data to the template
		$this->smarty->assign(array(
			'this_path' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/',
			'gateway' => 'https://gateway.ferbuy.com/'.$env.'/',
			'fields' => $arr
		));

		return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
	}

	/**
	 * Display the FerBuy payment return page
	 * @return string FerBuy template of the confirmation content
	 */
	public function hookPaymentReturn($params)
	{
		/* If 1.4 and no backward, then leave */
		if (!$this->backward && version_compare(_PS_VERSION_, '1.5', '<'))
			return;

		// Check if it is active
		if (!$this->active)
			return;

		// Get reorder url
		$partial_reorder_url = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'index.php?controller=order&step=3';
		$reorder_url = $partial_reorder_url.'&id_order='.$params['objOrder']->id.'&submitReorder=Reorder';

		// Assign the template
		switch ($params['objOrder']->getCurrentState())
		{
			case Configuration::get('PS_OS_PAYMENT'):
				$this->smarty->assign(array('status' => 'ok'));
				break;

			case Configuration::get('PS_OS_CANCELED'):
				$this->smarty->assign(array(
					'status' => 'canceled',
					'reorder_url' => $reorder_url
				));
				break;

			case Configuration::get('PS_OS_ERROR'):
			default:
				$this->smarty->assign(array(
					'status' => 'failed',
					'reorder_url' => $reorder_url
				));
				break;
		}

		return $this->display(__FILE__, 'views/templates/hook/confirmation.tpl');
	}

	/**
	 * Set transaction detail
	 * @param type $response
	 */
	public function setTransactionDetail($response)
	{
		// If Exist we can store the details
		if (isset($this->pcc))
			$this->pcc->transaction_id = (string)$response['transaction_id'];
	}

	/**
	 * Check module requirements to make sure the FerBuy's module will work properly
	 * - If id and/or secret core are not set
	 */
	private function checkModuleRequirements()
	{
		$this->errors = array();

		if (!isset($this->site_id) || !isset($this->secret))
			$this->errors[] = $this->l('Your Site ID and/or Secret Code are not set. Please verify you have copied/pasted it correctly from our website.');

		if (!function_exists('curl_version'))
			$this->errors[] = $this->l('PHP cURL extension must be enabled on your server.');

		if (version_compare(_PS_VERSION_, '1.4', '<'))
			$this->errors[] = $this->l('Version higher than 1.4 of Prestashop is required.');

		if (version_compare(_PS_VERSION_, '1.5', '<') && !$this->backward)
			$this->errors[] = $this->l('You are not using the backward compatibility module. You need to enable to use FerBuy.');
	}

	/*
	 * Get the shopping cart, in FerBuy format
	 */
	private function getShoppingCart($cart)
	{
		// Get Items for Shopping Cart
		$items = array();
		foreach ($cart->getProducts() as $item)
			if ($item['cart_quantity'] > 0)
			{
				$new_item = array(
					'Name' => $item['name'],
					'Price' => round($item['price'] * 100, 0),
					'Quantity' => $item['cart_quantity']
				);
				if (array_key_exists('attributes', $item))
					$new_item['Description'] = $item['name'].' '.$item['attributes'];
				else
					$new_item['Description'] = $item['name'];
				$items[] = $new_item;
			}
		// Initilize subtotal
		$subtotal = 0;

		// Obtain subtotal by adding the price of every item
		foreach ($cart->getProducts() as $item)
			$subtotal += $item['price'] * $item['cart_quantity'];

		// Some details from the Cart
		$cart_details = $cart->getSummaryDetails(null, true);

		// Round off figures
		$shopping_cart = array();
		$shopping_cart['total'] = round($cart->getOrderTotal() * 100, 0);
		$shopping_cart['subtotal'] = round($subtotal * 100, 0);

		$shopping_cart['tax'] = round($cart_details['total_tax'] * 100, 0);
		$shopping_cart['discount'] = round($cart_details['total_discounts'] * -100, 0);
		$shopping_cart['items'] = $items;

		if (version_compare(_PS_VERSION_, '1.5', '<'))
			$shopping_cart['shipping'] = round($cart->getOrderShippingCost(null, false) * 100, 0);
		else
			$shopping_cart['shipping'] = round($cart->getTotalShippingCost(null, false) * 100, 0);

		// Encode the shopping cart
		return Tools::jsonEncode($shopping_cart);
	}

	/**
	 * Set module settings
	 */
	private function setModuleSettings()
	{
		$this->mode = Configuration::get('FERBUY_MODE');
		$this->site_id = Configuration::get('FERBUY_SITEID');
		$this->secret = Configuration::get('FERBUY_SECRET');
	}

	/**
	 * Connect to the Gateway to send the information about
	 * @param string $api
	 * @param string $site_id
	 * @param string $reference
	 * @param string $secret
	 * @param string $command
	 * @return boolean
	 */
	private function apiCall($api, $site_id, $reference, $secret, $command)
	{
		$url = 'https://gateway.ferbuy.com/api/'.$api;

		//Get the model for get the secret from the siteId
		$data = array();
		$data['output_type'] = 'json';
		$data['site_id'] = $site_id;
		$data['transaction_id'] = $reference;
		$data['command'] = $command;
		$data['checksum'] = sha1(join('&', array(
			$data['site_id'],
			$data['transaction_id'],
			$data['command'],
			$data['output_type'],
			$secret
		)));

		$result = Tools::jsonDecode($this->requestCurl($url, $data), true);

		if (array_key_exists('api', $result))
			return ((int)$result['api']['response']['code'] === 200);
		else
			return false;
	}

	/**
	 * Send CURL communication
	 * @param string $url
	 * @param array $data
	 * @return string with the result
	 */
	private function requestCurl($url, $data)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

		curl_setopt($ch, CURLOPT_TIMEOUT, 80);
		curl_setopt($ch, CURLOPT_SSLVERSION, 3);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

		$data = curl_exec($ch);

		if ($data === false)
			$data = ('Error: '.curl_error($ch));

		curl_close($ch);
		return $data;
	}
}
