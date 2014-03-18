<?php
/*
* 2013 BluePay
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
*  @author BluePay Processing, LLC
*  @copyright  2013 BluePay Processing, LLC
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

if (!defined('_PS_VERSION_'))
	exit;

class BluePay extends PaymentModule
{
	public function __construct()
	{
		$this->name = 'bluepay';
		$this->tab = 'payments_gateways';
		$this->version = '1.1';
		$this->author = 'BluePay Processing, LLC';
		$this->need_instance = 0;

		parent::__construct();

		$this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
		$this->displayName = $this->l('BluePay');
		$this->description = $this->l('Accept Credit card & ACH payments today with BluePay');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
	}

	/**
	* Runs the BluePay installation process
	*/
	public function install()
	{
		include_once(_PS_MODULE_DIR_.'/bluepay/bluepay_install.php');
		if (Shop::isFeatureActive())
			Shop::setContext(Shop::CONTEXT_ALL);
		if (!function_exists('curl_version'))
			return 'Error: Curl not installed.';

		$bluepay_install = new BluePayInstall();
		$bluepay_install->createTables();
		$bluepay_install->updateConfiguration();
		$bluepay_install->createOrderState();

		return parent::install() &&
		$this->registerHook('leftColumn') &&
		$this->registerHook('displayHeader') &&
		$this->registerHook('displayBackOfficeHeader') &&
		$this->registerHook('displayPayment') &&
		$this->registerHook('displayOrderConfirmation') &&
		$this->registerHook('actionProductCancel') &&
		$this->registerHook('displayAdminOrder');
	}

	/**
	* Runs the BluePay uninstallation process
	*/
	public function uninstall()
	{
		include_once(_PS_MODULE_DIR_.'/bluepay/bluepay_uninstall.php');
		$bluepay_uninstall = new BluePayUninstall();
		$bluepay_uninstall->deleteConfiguration();
		return parent::uninstall();
	}

	/**
	* Creates the Back Office configuration page
	*/
	public function getContent()
	{
		$output = null;

		if (Tools::isSubmit('submit'.$this->name))
		{
			$account_id = (string)Tools::getValue('BP_ACCOUNT_ID');
			$secret_key = (string)Tools::getValue('BP_SECRET_KEY');
			$transaction_mode = (string)Tools::getValue('BP_TRANSACTION_MODE');
			$transaction_type = (string)Tools::getValue('BP_TRANSACTION_TYPE');
			$payment_type = (string)Tools::getValue('BP_PAYMENT_TYPE');
			$visa_enabled = (string)Tools::getValue('BP_CARD_TYPES_VISA');
			$mastercard_enabled = (string)Tools::getValue('BP_CARD_TYPES_MC');
			$amex_enabled = (string)Tools::getValue('BP_CARD_TYPES_AMEX');
			$discover_enabled = (string)Tools::getValue('BP_CARD_TYPES_DISC');
			$require_cvv2 = (string)Tools::getValue('BP_REQUIRE_CVV2');
			$checkout_iframe = (string)Tools::getValue('BP_CHECKOUT_IFRAME');
			$allow_stored_payments = (string)Tools::getValue('BP_ALLOW_STORED_PAYMENTS');
			$display_secure_logo = (string)Tools::getValue('BP_DISPLAY_LOGO');

			if (!$account_id || empty($account_id) || !Validate::isGenericName($account_id) ||
				Tools::strlen($account_id) != 12)
				$output .= $this->displayError($this->l('Invalid BluePay Account ID'));
			else if (!$secret_key || empty($secret_key) || !Validate::isGenericName($secret_key) ||
				Tools::strlen($secret_key) != 32)
				$output .= $this->displayError($this->l('Invalid BluePay Secret Key'));
			else if ($payment_type != 'ACH' && (empty($visa_enabled) && empty($mastercard_enabled) &&
				empty($amex_enabled) && empty($discover_enabled)))
				$output .= $this->displayError($this->l('Please specify at least one card type to accept'));
			else
			{
				if ($payment_type == 'ACH')
					$transaction_type = 'SALE';

				Configuration::updateValue('BP_ACCOUNT_ID', $account_id);
				Configuration::updateValue('BP_SECRET_KEY', $secret_key);
				Configuration::updateValue('BP_TRANSACTION_MODE', $transaction_mode);
				Configuration::updateValue('BP_TRANSACTION_TYPE', $transaction_type);
				Configuration::updatevalue('BP_PAYMENT_TYPE', $payment_type);
				Configuration::updateValue('BP_CARD_TYPES_VISA', $visa_enabled);
				Configuration::updateValue('BP_CARD_TYPES_MC', $mastercard_enabled);
				Configuration::updateValue('BP_CARD_TYPES_AMEX', $amex_enabled);
				Configuration::updateValue('BP_CARD_TYPES_DISC', $discover_enabled);
				Configuration::updateValue('BP_REQUIRE_CVV2', $require_cvv2);
				Configuration::updateValue('BP_CHECKOUT_IFRAME', $checkout_iframe);
				Configuration::updateValue('BP_ALLOW_STORED_PAYMENTS', $allow_stored_payments);
				Configuration::updateValue('BP_DISPLAY_LOGO', $display_secure_logo);
				$output .= $this->displayConfirmation($this->l('Settings updated'));
			}
		}
		return $output.$this->displayForm();
	}

	/**
	* Process a refund through BluePay
	*/
	public function processRefund($transaction_id, $amount = false)
	{
		if (!$transaction_id)
			die(Tools::displayError('Fatal Error: transaction_id is null'));

		if (!$amount)
			$params = array('TRANSACTION_ID' => $transaction_id, 'REFUND_TYPE' => 'Full');
		else
			$params = array('TRANSACTION_ID' => $transaction_id, 'REFUND_TYPE' => 'Partial', 'AMOUNT' => (float)$amount);
		$tps_string = md5(Configuration::get('BP_SECRET_KEY').Configuration::get('BP_ACCOUNT_ID').
			'REFUND'.$amount.$transaction_id);

		$params = array(
			'ACCOUNT_ID' => Configuration::get('BP_ACCOUNT_ID'),
			'TAMPER_PROOF_SEAL' => $tps_string,
			'MASTER_ID' => $transaction_id,
			'MODE' => Configuration::get('BP_TRANSACTION_MODE'),
			'AMOUNT' => $amount,
			'TRANS_TYPE' => 'REFUND',
			'VERSION' => '1'
		);
		$post_string = '';
		foreach ($params as $key => $value)
			$post_string .= $key.'='.urlencode($value).'&';
		$post_string = trim($post_string, '&');

		$url = 'https://secure.bluepay.com/interfaces/bp20post';
		// POST transaction data to BluePay
		$request = curl_init($url);
		curl_setopt($request, CURLOPT_HEADER, 0);
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($request, CURLOPT_POSTFIELDS, $post_string);
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
		$post_response = curl_exec($request);
		curl_close($request);

		return $post_response;
	}

	/**
	* Adds a new private message for the Admin
	*/
	public function addNewPrivateMessage($order_id, $message)
	{
		if (!(bool)$order_id)
			return false;
		$new_message = new Message();
		$message = strip_tags($message, '<br>');
		if (!Validate::isCleanHtml($message))
			$message = $this->l('Payment message is not valid, please check your module.');

		$new_message->message = $message;
		$new_message->id_order = $order_id;
		$new_message->private = 1;
		return $new_message->add();
	}

	/**
	* Processes a capture through BluePay
	*/
	private function capture($id_order)
	{
		include_once(_PS_MODULE_DIR_.'/bluepay/bluepay_orders.php');
		if (!$id_order)
			die(Tools::displayError('Fatal Error: order_id is null'));
		$bluepay_order = BluePayOrder::getOrderById((int)$id_order);
		if (!$bluepay_order)
			return false;

		$order = new Order((int)$id_order);

		$tps_string = md5(Configuration::get('BP_SECRET_KEY').Configuration::get('BP_ACCOUNT_ID').
			'CAPTURE'.$bluepay_order['transaction_id']);

		$params = array(
			'ACCOUNT_ID' => Configuration::get('BP_ACCOUNT_ID'),
			'TAMPER_PROOF_SEAL' => $tps_string,
			'MASTER_ID' => $bluepay_order['transaction_id'],
			'MODE' => Configuration::get('BP_TRANSACTION_MODE'),
			'TRANS_TYPE' => 'CAPTURE',
			'VERSION' => '1'
		);
		$post_string = '';
		foreach ($params as $key => $value)
			$post_string .= $key.'='.urlencode($value).'&';
		$post_string = trim($post_string, '&');

		$url = 'https://secure.bluepay.com/interfaces/bp20post';
		/* POST transaction details to BluePay */
		$request = curl_init($url);
		curl_setopt($request, CURLOPT_HEADER, 0);
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($request, CURLOPT_POSTFIELDS, $post_string);
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
		parse_str(curl_exec($request), $post_response);
		curl_close($request);

		$message = $post_response['MESSAGE'];
		$status = $post_response['STATUS'];
		$transaction_id = $post_response['TRANS_ID'];
		if ($status == 1 && $message != 'DUPLICATE')
		{
			$order_history = new OrderHistory();
			$order_history->id_order = (int)$id_order;
			$order_history->changeIdOrderState(Configuration::get('PS_OS_PAYMENT'), $order);
			$order_history->addWithemail();
		}

		if (!Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'bluepay_order`
			SET `payment_status` = \''.pSQL($message).'\',
				`transaction_id` = \''.pSQL($transaction_id).'\',
				`transaction_type` = \'SALE\'
			WHERE `order_id` = '.(int)$id_order))
			die(Tools::displayError('Error when updating BluePay database'));

		$this->addNewPrivateMessage((int)$id_order, $message);

		Tools::redirect($_SERVER['HTTP_REFERER'].'&result='.$status.'&message='.$message);
	}

	/**
	* Initiates refund
	*/
	private function refund($id_order)
	{
		include_once(_PS_MODULE_DIR_.'/bluepay/bluepay_orders.php');
		$bluepay_order = BluePayOrder::getOrderById((int)$id_order);
		if (!$bluepay_order)
			return false;

		$order = new Order((int)$id_order);
		if (!Validate::isLoadedObject($order))
		return false;

		$products = $order->getProducts();
		$currency = new Currency((int)$order->id_currency);
		if (!Validate::isLoadedObject($currency))
			$this->_errors[] = $this->l('Not a valid currency');

		if (count($this->_errors))
			return false;

		$decimals = (is_array($currency) ? (int)$currency['decimals'] : (int)$currency->decimals) * _PS_PRICE_DISPLAY_PRECISION_;

		// Amount for refund
		$amount = 0.00;

		foreach ($products as $product)
			$amount += (float)$product['product_price_wt'] * ($product['product_quantity'] - $product['product_quantity_refunded']);
		$amount += (float)$order->total_shipping + (float)$order->total_wrapping - (float)$order->total_discounts;
		// check if total or partial
		if (Tools::ps_round($order->total_paid_real, $decimals) == Tools::ps_round($amount, $decimals))
			$response = $this->processRefund($bluepay_order['transaction_id']);
		else
			$response = $this->processRefund($bluepay_order['transaction_id'], (float)$amt);
		parse_str($response, $post_response);
		$message = $post_response['MESSAGE'];
		$status = $post_response['STATUS'];
		if ($status == 1 && $message != 'DUPLICATE')
		{
			if (!Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'bluepay_order` SET `payment_status` = \''.pSQL($message).'\' 
				WHERE `order_id` = '.(int)$id_order))
				die(Tools::displayError('Error when updating BluePay database'));

			$history = new OrderHistory();
			$history->id_order = (int)$id_order;
			$history->changeIdOrderState((int)Configuration::get('PS_OS_REFUND'), $history->id_order);
			$history->addWithemail();
			$history->save();
		}

		$this->addNewPrivateMessage((int)$id_order, $message);

		Tools::redirect($_SERVER['HTTP_REFERER'].'&result='.$status.'&message='.$message);

		return $response;
	}

	/**
	* Check if order can be captured
	*/
	private function canCapture($id_order)
	{
		if (!(bool)$id_order)
			return false;

		$bluepay_order = Db::getInstance()->getRow('
		SELECT `transaction_type`, `payment_status`
		FROM `'._DB_PREFIX_.'bluepay_order`
		WHERE `order_id` = '.(int)$id_order);

		return ($bluepay_order['transaction_type'] == 'AUTH' && $bluepay_order['payment_status'] != 'Approved Capture');
	}

	/**
	* Check if order can be refunded
	*/
	private function canRefund($id_order)
	{
		if (!(bool)$id_order)
			return false;

		$bluepay_order = Db::getInstance()->getRow('
		SELECT `transaction_type`, `payment_status`
		FROM `'._DB_PREFIX_.'bluepay_order`
		WHERE `order_id` = '.(int)$id_order);

		return ($bluepay_order['transaction_type'] == 'SALE' && $bluepay_order['payment_status'] != 'Approved Refund' &&
			$bluepay_order['payment_status'] != 'Approved Void');
	}

	/**
	* Displays Back Office configuration page
	*/
	public function displayForm()
	{
		$account_id_instructions = 'a. Log in to your BluePay gateway account at https://secure.bluepay.com.
			b. Click on the Administration tab at the top and navigate to Accounts -> List.
			c. Click on the View icon located next to your account under Options.
			d. On the Account Admin page, copy the 12 digit value labeled as "Account ID" and paste this into your '.
			'PrestaShop configuration page.';
		$secret_key_instructions = 'a. Log in to your BluePay gateway account at https://secure.bluepay.com.
			b. Click on the Administration tab at the top and navigate to Accounts -> List.
			c. Click on the View icon located next to your account under Options.
			d. On the Account Admin page about half-way down, copy the 32 digit value labeled as "Secret Key" and paste this into your '.
			'PrestaShop configuration page.';
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
		$fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('BluePay Settings'),
				'image' => $this->_path.'img/bp-logo-small.png'
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('Account ID'),
					'name' => 'BP_ACCOUNT_ID',
					'suffix' => $account_id_instructions,
					'size' => 11,
					'maxlength' => 12
				),
				array(
					'type' => 'text',
					'label' => $this->l('Secret Key'),
					'name' => 'BP_SECRET_KEY',
					'suffix' => $secret_key_instructions,
					'size' => 40,
					'maxlength' => 32
				),
				array(
					'type' => 'select',
					'label' => $this->l('Transaction Mode'),
					'name' => 'BP_TRANSACTION_MODE',
					'options' => array(
						'query' => array(
							array(
								'name' => $this->l('TEST'),
								'id' => 'TEST'
							),
							array(
								'name' => $this->l('LIVE'),
								'id' => 'LIVE'
							)
						),
						'id' => 'id',
						'name' => 'name'
					)
				),
				array(
					'type' => 'select',
					'label' => $this->l('Payment Type'),
					'name' => 'BP_PAYMENT_TYPE',
					'onchange' => 'checkPaymentType()',
					'options' => array(
						'query' => array(
							array(
								'name' => $this->l('Credit Card'),
								'id' => 'CC'
							),
							array(
								'name' => $this->l('E-Check'),
								'id' => 'ACH'
							),
							array(
								'name' => $this->l('Credit Card & E-Check'),
								'id' => 'BOTH'
							)
						),
						'id' => 'id',
						'name' => 'name'
					)
				),
				array(
					'type' => 'select',
					'label' => $this->l('Transaction Type'),
					'name' => 'BP_TRANSACTION_TYPE',
					'desc' => 'Choose AUTH for only authorizing the customer\'s credit card on a successful order. If chosen, 
						you must later capture this authorization in the Back Office. Choose SALE to authorize and capture this payment in one step.',
					'options' => array(
						'query' => array(
							array(
								'name' => $this->l('AUTH'),
								'id' => 'AUTH'
							),
							array(
								'name' => $this->l('SALE'),
								'id' => 'SALE'
							)
						),
						'id' => 'id',
						'name' => 'name'
					)
				),
				array(
					'type' => 'checkbox',
					'label' => $this->l('Card Types Accepted'),
					'name' => 'BP_CARD_TYPES',
					'values' => array(
						'query' => array(
							array(
								'name' => $this->l('Visa'),
								'id' => 'VISA'
							),
							array(
								'name' => $this->l('MasterCard'),
								'id' => 'MC'
							),
							array(
								'name' => $this->l('American Express'),
								'id' => 'AMEX'
							),
							array(
								'name' => $this->l('Discover (Includes: Diners, JCB, China UnionPay, BC Card, and DinaCard)'),
								'id' => 'DISC'
							)
						),
						'id' => 'id',
						'name' => 'name'
					)
				),
				array(
					'type' => 'select',
					'label' => $this->l('Require CVV2?'),
					'name' => 'BP_REQUIRE_CVV2',
					'desc' => 'For extra security, select this option to require your customers to input their Card Security Code on every credit card purchase',
					'options' => array(
						'query' => array(
							array(
								'name' => $this->l('Yes'),
								'id' => 'Yes'
							),
							array(
								'name' => $this->l('No'),
								'id' => 'No'
							)
						),
						'id' => 'id',
						'name' => 'name'
					)
				),
				array(
					'type' => 'select',
					'label' => $this->l('Enable BluePay Secure Hosted Checkout Form/Iframe?'),
					'name' => 'BP_CHECKOUT_IFRAME',
					'desc' => 'The secure hosted iframe is used by merchants to reduce their PCI scope by allowing 
						BluePay to host the payment form within the checkout page.',
					'options' => array(
						'query' => array(
							array(
								'name' => $this->l('Yes'),
								'id' => 'Yes'
							),
							array(
								'name' => $this->l('No'),
								'id' => 'No'
							)
						),
						'id' => 'id',
						'name' => 'name'
					)
				),
				array(
					'type' => 'select',
					'label' => $this->l('Allow Storing of Saved Payments?'),
					'name' => 'BP_ALLOW_STORED_PAYMENTS',
					'desc' => 'If checked, this option will allow your customers to securely save their payment information for later purchases from your website.',
					'options' => array(
						'query' => array(
							array(
								'name' => $this->l('Yes'),
								'id' => 'Yes'
							),
							array(
								'name' => $this->l('No'),
								'id' => 'No'
							)
						),
						'id' => 'id',
						'name' => 'name'
					)
				),
				array(
					'type' => 'select',
					'label' => $this->l('Display BluePay Secure Logo on Checkout Page?'),
					'name' => 'BP_DISPLAY_LOGO',
					'options' => array(
						'query' => array(
							array(
								'name' => $this->l('Yes'),
								'id' => 'Yes'
							),
							array(
								'name' => $this->l('No'),
								'id' => 'No'
							)
						),
						'id' => 'id',
						'name' => 'name'
					)
				),
				array(
					'type' => 'free',
					'name' => 'SSL',
				)
			),
			'submit' => array(
				'title' => $this->l('Save'),
				'onclick' => 'check()',
				'class' => 'button'
			)
		);

		$helper = new HelperForm();

		// Module, token and currentIndex
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

		// Language
		$helper->default_form_language = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;

		// Title and toolbar
		//$helper->title = $this->displayName;
		$helper->show_toolbar = true;        // false -> remove toolbar
		$helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
		$helper->submit_action = 'submit'.$this->name;
		$helper->toolbar_btn = array(
			'save' => array(
				'desc' => $this->l('Save'),
				'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
				'&token='.Tools::getAdminTokenLite('AdminModules')
			),
			'back' => array(
				'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
				'desc' => $this->l('Back to list')
			)
		);

		// Load configuration values from ps_configuration table
		$helper->fields_value['BP_ACCOUNT_ID'] = Configuration::get('BP_ACCOUNT_ID');
		$helper->fields_value['BP_SECRET_KEY'] = Configuration::get('BP_SECRET_KEY');
		$helper->fields_value['BP_TRANSACTION_MODE'] = Configuration::get('BP_TRANSACTION_MODE');
		$helper->fields_value['BP_TRANSACTION_TYPE'] = Configuration::get('BP_TRANSACTION_TYPE');
		$helper->fields_value['BP_PAYMENT_TYPE'] = Configuration::get('BP_PAYMENT_TYPE');
		$helper->fields_value['BP_CARD_TYPES_VISA'] = Configuration::get('BP_CARD_TYPES_VISA');
		$helper->fields_value['BP_CARD_TYPES_MC'] = Configuration::get('BP_CARD_TYPES_MC');
		$helper->fields_value['BP_CARD_TYPES_AMEX'] = Configuration::get('BP_CARD_TYPES_AMEX');
		$helper->fields_value['BP_CARD_TYPES_DISC'] = Configuration::get('BP_CARD_TYPES_DISC');
		$helper->fields_value['BP_CARD_TYPES_DC'] = Configuration::get('BP_CARD_TYPES_DC');
		$helper->fields_value['BP_CARD_TYPES_JCB'] = Configuration::get('BP_CARD_TYPES_JCB');
		$helper->fields_value['BP_REQUIRE_CVV2'] = Configuration::get('BP_REQUIRE_CVV2');
		$helper->fields_value['BP_CHECKOUT_IFRAME'] = Configuration::get('BP_CHECKOUT_IFRAME');
		$helper->fields_value['BP_ALLOW_STORED_PAYMENTS'] = Configuration::get('BP_ALLOW_STORED_PAYMENTS');
		$helper->fields_value['BP_DISPLAY_LOGO'] = Configuration::get('BP_DISPLAY_LOGO');
		$helper->fields_value['SSL'] = 'An SSL Certificate is required to use the BluePay module.
			Don\'t have your own SSL certificate? ';

		return $helper->generateForm($fields_form);
	}

	/**
	* Queries the BluePay gateway using a specific transaction ID and matching invoice ID.
	* Returns the amount of the transaction, if found.
	*/
	public function validate($transaction_id, $invoice_id)
	{
		date_default_timezone_set('America/Chicago');
		$start_date = date('Y-m-d H:i:s', strtotime('-1 hour'));
		$end_date = date('Y-m-d H:i:s', strtotime('+1 hour'));
		$tps = Configuration::get('BP_SECRET_KEY').Configuration::get('BP_ACCOUNT_ID').$start_date.$end_date;
		$params = array(
			'ACCOUNT_ID' => Configuration::get('BP_ACCOUNT_ID'),
			'TAMPER_PROOF_SEAL' => md5($tps),
			'REPORT_START_DATE' => $start_date,
			'REPORT_END_DATE' => $end_date,
			'MODE' => Configuration::get('BP_TRANSACTION_MODE'),
			'id' => $transaction_id,
			'invoice_id' => $invoice_id,
			'status' => '1',
			'EXCLUDE_ERRORS' => '1'
		);
		$post_string = '';
		foreach ($params as $key => $value)
			$post_string .= $key.'='.urlencode($value).'&';
		$post_string = trim($post_string, '&');

		$url = 'https://secure.bluepay.com/interfaces/stq';
		// POST transaction data to BluePay
		$request = curl_init($url);
		curl_setopt($request, CURLOPT_HEADER, 0);
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($request, CURLOPT_POSTFIELDS, $post_string);
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
		parse_str(curl_exec($request), $post_response);
		curl_close($request);
		return $post_response['amount'];
	}

	/**
	* Hooks
	*/
	public function hookDisplayHeader()
	{
		if (Configuration::get('BP_CHECKOUT_IFRAME') == 'No')
			$this->context->controller->addJS($this->_path.'js/checkout.js');
		else
			$this->context->controller->addJS($this->_path.'js/checkout_iframe.js');
		$this->context->controller->addJS($this->_path.'js/easyXDM.min.js');
		$this->context->controller->addJqueryPlugin('validate-creditcard');
	}

	public function hookDisplayBackOfficeHeader()
	{
		if ((Tools::getValue('configure') != $this->name) && (Tools::getValue('module_name') != $this->name))
			return false;
		
		$this->context->controller->addCSS($this->_path.'css/bp.css');
		$this->context->controller->addJS($this->_path.'js/bp.js');
	}

	public function hookDisplayPayment($params)
	{
		include_once(_PS_MODULE_DIR_.'/bluepay/bluepay_customers.php');
		$cart = new Cart($this->context->cart->id);
		$cart_contents = '';

		foreach ($cart->getProducts() as $product)
			foreach ($product as $key => $value)
			{
				if ($key == 'cart_quantity')
					$cart_contents .= $value.' ';
				if ($key == 'name')
					$cart_contents .= $value.'|';
			}
		$address = new Address((int)$cart->id_address_invoice);
		$state = new State($address->id_state);
		$cards = array();
		$cards['visa'] = Configuration::get('BP_CARD_TYPES_VISA') == 'on';
		$cards['mastercard'] = Configuration::get('BP_CARD_TYPES_MC') == 'on';
		$cards['discover'] = Configuration::get('BP_CARD_TYPES_DISC') == 'on';
		$cards['amex'] = Configuration::get('BP_CARD_TYPES_AMEX') == 'on';
		$expiration_month = array();
		for ($i = 1; $i < 13; $i++)
		{
			$expiration_month[$i] = str_pad($i, 2, '0', STR_PAD_LEFT);
		}
		$expiration_year = array();
		for ($i = 13; $i < 25; $i++)
			$expiration_year[$i] = $i;
		$this->context->smarty->assign(
			array(
				'payment_type' => Configuration::get('BP_PAYMENT_TYPE'),
				'display_logo' => Configuration::get('BP_DISPLAY_LOGO'),
				'secret_key' => Configuration::get('BP_SECRET_KEY'),
				'account_id' => Configuration::get('BP_ACCOUNT_ID'),
				'transaction_type' => Configuration::get('BP_TRANSACTION_TYPE'),
				'payment_type' => Configuration::get('BP_PAYMENT_TYPE'),
				'mode' => Configuration::get('BP_TRANSACTION_MODE'),
				'customer' => $params['cookie']->customer_firstname.' '.$params['cookie']->customer_lastname,
				'customer_address' => Tools::safeOutput($address->address1.' '.$address->address2),
				'customer_city' => Tools::safeOutput($address->city),
				'customer_state' => $state->name,
				'customer_zip' => Tools::safeOutput($address->postcode),
				'customer_email' => $this->context->cookie->email,
				'customer_country' => $address->country,
				'invoice_id' => (int)$params['cart']->id,
				'cards' => $cards,
				'this_path' => $this->_path,
				'cart' => $cart_contents,
				'require_cvv2' => Configuration::get('BP_REQUIRE_CVV2'),
				'allow_stored_payments' => Configuration::get('BP_ALLOW_STORED_PAYMENTS'),
				'use_iframe' => Configuration::get('BP_CHECKOUT_IFRAME'),
				'has_saved_payment_information' => BluePayCustomer::customerHasStoredPayment($params['cookie']->id_customer),
				'has_saved_cc_payment_information' => BluePayCustomer::customerHasStoredCCPayment($params['cookie']->id_customer),
				'saved_payment_information' => BluePayCustomer::getCustomerById($params['cookie']->id_customer),
				'card_expiration_mm' => $expiration_month,
				'card_expiration_yy' => $expiration_year,
				'ach_account_types' => array(
					'C' => 'Checking',
					'S' => 'Savings'
					)
			)
		);

		if (Configuration::get('BP_CHECKOUT_IFRAME') == 'No')
			return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
		return $this->display(__FILE__, 'views/templates/hook/payment_iframe.tpl');
	}

	public function hookDisplayOrderConfirmation($params)
	{
		if ($params['objOrder']->module != $this->name)
			return;
		$query = 'SELECT `reference` FROM `'._DB_PREFIX_.'orders` WHERE `id_cart` = '.((int)$params['objOrder']->id_cart);
		$order_reference = Db::getInstance()->getValue($query);
		Db::getInstance()->update('order_payment',
			array(
				'card_number' => pSQL(Tools::getValue('masked_card_number')),
				'card_brand' => pSQL(Tools::getValue('card_type')),
				'card_expiration' => pSQL(Tools::getValue('card_expiration')),
				'card_holder' => pSQL(Tools::getValue('card_holder'))
			),
			'`order_reference` = \''.$order_reference.'\'',
			1
		);
		$this->context->smarty->assign(
			array(
				'status' => 'ok',
				'amount' => $params['total_to_pay'],
				'id_order' => (int)$params['objOrder']->id,
				'card_number' => Tools::getValue('masked_card_number'),
				'card_brand' => Tools::getValue('card_type'),
				'order_reference' => $order_reference
			)
		);
		return $this->display(__FILE__, 'views/templates/front/order_confirmation.tpl');
	}

	public function hookActionProductCancel($params)
	{
		include_once(_PS_MODULE_DIR_.'/bluepay/bluepay_orders.php');
		if ($params['order']->module != $this->name || !($order = $params['order']) || !Validate::isLoadedObject($order))
			return false;
		elseif (!$order->hasBeenPaid())
			return false;

		$order_detail = new OrderDetail((int)$params['id_order_detail']);
		if (!$order_detail || !Validate::isLoadedObject($order_detail))
			return false;

		$bluepay_order = BluePayOrder::getOrderById((int)Tools::getValue('id_order'));
		if (!$bluepay_order)
			return false;

		$products = $order->getProducts();
		$cancel_quantity = Tools::getValue('cancelQuantity');

		$amount = (float)$products[(int)$order_detail->id]['product_price_wt'] * (int)$cancel_quantity[(int)$order_detail->id];
		$refund = $this->processRefund($bluepay_order['transaction_id'], (int)$order->id, $amount);
		parse_str($refund, $post_response);

		$message = $post_response['MESSAGE'];
		$this->addNewPrivateMessage(Tools::getValue('id_order'), $message);
	}

	public function hookDisplayAdminOrder($params)
	{
		if (Tools::isSubmit('submitBluePayRefund'))
			$this->refund((int)$params['id_order']);
		elseif (Tools::isSubmit('submitBluePayCapture'))
			$this->capture((int)$params['id_order']);

		$order = new Order((int)$params['id_order']);
		$order_state = $order->current_state;
		$this->context->smarty->assign(
			array(
				'base_url' => _PS_BASE_URL_.__PS_BASE_URI__,
				'module_name' => $this->name,
				'order_state' => $order_state,
				'params' => $params,
				'ps_version' => _PS_VERSION_,
				'can_refund' => $this->canRefund((int)$order->id),
				'can_capture' => $this->canCapture((int)$order->id),
				'transaction_status' => Tools::getValue('result'),
				'transaction_message' => Tools::getValue('message')
			)
		);
		return $this->display(__FILE__, 'views/templates/hook/refund_capture.tpl');
	}
}
