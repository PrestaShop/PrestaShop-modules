<?php
/**
 * 	Riskified payments security module for Prestashop. Riskified reviews, approves & guarantees transactions you would otherwise decline.
 *
 *  @author    riskified.com <support@riskified.com>
 *  @copyright 2013-Now riskified.com
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Riskified
 */

if (!defined('_PS_VERSION_'))
	exit;

require_once _PS_MODULE_DIR_.'riskified/lib/includes/includes.inc.php';
require_once _PS_MODULE_DIR_.'riskified/lib/RiskifiedLogger.php';

class Riskified extends Module
{

	const INSTALL_SQL_FILE = 'install.sql';
	const RISKIFIED_TABLE_NAME = 'riskified_ip_info';
	private $html;

	public function __construct()
	{
		$this->name = 'riskified';
		$this->tab = 'payment_security';
		$this->version = '0.3.1';
		$this->author = 'Riskified.com';
		$this->secure_key = Tools::encrypt($this->name);
		parent::__construct();
		$this->displayName = 'Riskified';
		$this->description = $this->l('Riskified reviews, approves & guarantees transactions you would otherwise decline');
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
	}

	public function install()
	{
		$result = true;

		// We need CURL to function correctly
		if (!$this->curlExists())
		{
			$this->context->controller->errors[] = $this->l('Riskified require CURL to be installed and enabled.');
			$result = false;
		}

		if (!file_exists(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE))
			return false;
		else if (!$sql = Tools::file_get_contents(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE))
			return false;
		$sql = str_replace(array('PREFIX_', 'ENGINE_TYPE'), array(_DB_PREFIX_, _MYSQL_ENGINE_), $sql);
		$sql = preg_split("/;\s*[\r\n]+/", trim($sql));
		foreach ($sql as $query)
			if (!Db::getInstance()->execute(trim($query)))
				return false;

		if (!parent::install()
			|| !$this->registerHook('displayAdminOrder')
			|| !$this->registerHook('displayBackOfficeHeader')
			|| !$this->registerHook('displayBackOfficeTop')
			|| !$this->registerHook('actionValidateOrder')
			|| !$this->registerHook('header'))
			$result = false;

		RiskifiedLogger::insertLog(__METHOD__.' : '.__LINE__, 'Riskified::install() = '.$result);
		return $result;
	}

	public function hookBackOfficeHeader()
	{
		$this->context->controller->addCSS($this->_path.'css/riskified_overview.css', 'all');
		$this->context->controller->addJS($this->_path.'js/riskified.js', 'all');
	}

	public function hookDisplayBackOfficeTop()
	{
		$today = date('Ymd');
		$validation_token = Tools::getAdminToken('riskifiedAjax'.$today);
		$this->smarty->assign(
			array(
				'order_id' => Tools::getValue('id_order'),
				'base_url' => _PS_BASE_URL_,
				'base_uri' => __PS_BASE_URI__,
				'ps_version' => _PS_VERSION_,
				'token' => $validation_token
				)
			);
		return $this->display(__FILE__, './views/templates/admin/riskified.tpl');
	}

	public function hookDisplayAdminOrder()
	{
		return $this->display(__FILE__, './views/templates/admin/submit_to_riskified.tpl');
	}


	public function curlExists()
	{
		return function_exists('curl_version');
	}

	public function getRiskifiedUrl()
	{
		if (Configuration::get('RISKIFIED_MODE') == '0')
			return 'https://sandbox.riskified.com/webhooks/merchant_order_created';
		else
			return 'https://wh.riskified.com/webhooks/merchant_order_created';
	}

	private function fillLineItems(&$data, $order)
	{
// line items
		$products = $order->getProducts();
		if ((!is_null($products)) && is_array($products))
		{
			$i = 0;
			foreach ($products as $p)
			{
				$data['line_items'][$i]['fulfillment_service']	= null;
				$data['line_items'][$i]['fulfillment_status']	= null;
				$data['line_items'][$i]['grams']	= $p['weight'];
				$data['line_items'][$i]['id']	= $p['product_id'];
				$data['line_items'][$i]['price']	= $p['product_price'];
				$data['line_items'][$i]['product_id']	= $p['product_id'];
				$data['line_items'][$i]['quantity']	= $p['product_quantity'];
				$data['line_items'][$i]['requires_shipping']	= null;
				$data['line_items'][$i]['sku']	= null;
				$data['line_items'][$i]['title']	= $p['product_name'];
				$data['line_items'][$i]['variant_id']	= null;
				$data['line_items'][$i]['variant_title'] = null;
				$data['line_items'][$i]['vendor']	= null;
				$data['line_items'][$i]['name']	= null;
				$data['line_items'][$i]['variant_inventory_management'] = null;
				$data['line_items'][$i]['properties']	= null;
				$i++;
			}
		}
	}

	private function fillShippingDetails(&$data, $order, $carrier)
	{
		$data['shipping_lines'][]['code']	= $carrier->name;
		$data['shipping_lines'][]['price']	= $order->total_shipping;
		$data['shipping_lines'][]['source']	= $carrier->shipping_method;
		$data['shipping_lines'][]['title']	= $carrier->name;
		$data['tax_lines']	= null;
	}

	private function getPaymentDetails(&$data, $payments)
	{
		try {
			if (is_object($payments))
			{
				$data['payment_details']['avs_result_code']	= null;
				$data['payment_details']['credit_card_bin']	= $payments->credit_card_bin;
				$data['payment_details']['cvv_result_code']	= $payments->cvv_result_code;
				$data['payment_details']['credit_card_number']	= $payments->credit_card_number;
				$data['payment_details']['credit_card_company']	= $payments->credit_card_company;
			}
		} catch (Exception $e) {
			return;
		}
	}

	private function getBillingAddress(&$data, $address_invoice)
	{
		$data['billing_address']['first_name']	= $address_invoice->firstname;
		$data['billing_address']['last_name']	= $address_invoice->lastname;
		$data['billing_address']['name']		= $address_invoice->firstname;
		$data['billing_address']['address1']	= $address_invoice->address1;
		$data['billing_address']['address2']	= $address_invoice->address2;
		$data['billing_address']['city']		= $address_invoice->city;
		$data['billing_address']['company']		= $address_invoice->company;
		$data['billing_address']['country']		= $address_invoice->country;
//$data['billing_address']['country_code']= $billing_address->getCountryId();
		$data['billing_address']['phone']		= $address_invoice->phone;
		$data['billing_address']['province']	= null;
		$data['billing_address']['zip']			= $address_invoice->postcode;
		$data['billing_address']['province_code']	= null;
	}

	private function getShippingAddress(&$data, $address_shipping)
	{
		$data['shipping_address']['first_name'] = $address_shipping->firstname;
		$data['shipping_address']['last_name']	= $address_shipping->lastname;
		$data['shipping_address']['name']		    = $address_shipping->firstname;
		$data['shipping_address']['address1']	  = $address_shipping->address1;
		$data['shipping_address']['address2']	  = $address_shipping->address2;
		$data['shipping_address']['city']		    = $address_shipping->city;
		$data['shipping_address']['company']	  = $address_shipping->company;
		$data['shipping_address']['country']	  = $address_shipping->country;
//$data['shipping_address']['country_code'] = $shipping_address->getCountryId();
		$data['shipping_address']['phone']		  = $address_shipping->phone;
		$data['shipping_address']['province']	  = null;
		$data['shipping_address']['zip']		    = $address_shipping->postcode;
		$data['shipping_address']['province_code'] = null;
	}

	private function fillGeneralOrderInfo(&$data, $order_id, $order, $ip, $cart_id, $currency, $customer)
	{
		$data['id']				  = $order_id;
		$data['name']			  = $order_id;
		$data['email']			= $customer->email;
		$data['total_spent'] = $order->total_paid;
		$data['created_at']	= $order->date_add;
		$data['updated_at']	= $order->date_upd;
		$data['browser_ip']	= $ip;
		$data['cancel_reason']	= null;
		$data['cancelled_at']	= null;
		$data['cart_token']	= $cart_id;
		$data['closed_at'] = null;
		$data['currency']	= $currency->iso_code;
		$data['financial_status'] = null;
		$data['fulfillment_status']	= null;
		$data['landing_site']	= '/';
		$data['number']			= null;
		$data['reference']		= null;
		$data['referring_site']	= null;
		$data['source']			= null;
		$data['subtotal_price']	= 0;
		$data['taxes_included']	= true;
		$data['token']			= null;
		$data['total_discounts'] = 0;
		$data['total_line_items_price']	= 0;
		$data['total_price']	= $order->total_paid;
		$data['total_price_usd'] = $order->total_paid;
		$data['total_tax']		= 0;
		$data['total_weight']	= 0;
		$data['user_id']		= $order->id_customer;
		$data['landing_site_ref'] = null;
		$data['order_number']	= $order_id;
		$data['discount_codes']	= null;
		$data['note_attributes'] = null;
		$data['processing_method'] = $order->payment;
		$data['checkout_id']	= null;
	}

	private function fillCustomerInfo(&$data, $customer)
	{
		$data['customer']['created_at']       = $customer->date_add;
		$data['customer']['email']            = $customer->email;
		$data['customer']['first_name']       = $customer->firstname;
		$data['customer']['id']               = $customer->id;
		$data['customer']['last_name']        = $customer->lastname;
		$data['customer']['note']             = 'BDay: '.((string)$customer->birthday);
	}

	public function hookActionValidateOrder($params)
	{
		try {
			$ip = Tools::getRemoteAddr();

			$ip_forwarded_for = '';
			if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER))
				$ip_forwarded_for = $_SERVER['HTTP_X_FORWARDED_FOR'];

			$details = $params['order'];
			$cart_id = $details->id_cart;
			$order_id = Order::getOrderByCartId($cart_id);

			$currency  = new Currency($details->id_currency);
			$customer = new Customer((int)$details->id_customer);
			$my_order = new Order($order_id);
			$domain = Configuration::get('PS_SHOP_DOMAIN');
			$auth_token = Configuration::get('PS_AUTH_TOKEN');

			$data = array();
			$this->fillGeneralOrderInfo( $data, $order_id, $my_order, $ip, $cart_id, $currency, $customer );

			$data['note']	= null;
			if ($ip_forwarded_for)
				$data['note']	= 'forwarded for: '.$ip_forwarded_for;

			$this->fillLineItems( $data, $my_order );

			$carrier = new Carrier((int)$details->id_carrier, (int)$details->id_lang);
			$this->fillShippingDetails( $data, $my_order, $carrier );

			$payments = $my_order->getOrderPayments();
			$this->getPaymentDetails( $data, $payments );

			$address_invoice = new Address((int)$details->id_address_invoice);
			$this->getBillingAddress( $data, $address_invoice );

			$address_shipping = new Address((int)$details->id_address_delivery);
			$this->getShippingAddress( $data, $address_shipping );

			$this->storeIpAndRemoteForOrder( $order_id, $ip, $ip_forwarded_for );

			$this->fillCustomerInfo( $data, $customer );

			$data_string = Tools::jsonEncode($data);
			$hash_code = hash_hmac('sha256', $data_string, $auth_token);

			if ($this->curlExists())
			{
				$url = $this->getRiskifiedUrl();
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-Type: application/json',
					'Content-Length: '.Tools::strlen($data_string),
					'X_RISKIFIED_SHOP_DOMAIN:'.$domain,
					'X_RISKIFIED_HMAC_SHA256:'.$hash_code)
				);

				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_getinfo($ch);
				curl_exec($ch);

				if (Configuration::get('RISKIFIED_MODE') == '1')
					Configuration::updateValue('RISKIFIED_CONFIGURATION_OK', true);
			}
		} catch (Exception $e) {
			return;
		}
	}

	public function getData($order_id)
	{
		$my_order = new Order($order_id);
		$currency  = new Currency($my_order->id_currency);
		$customer = new Customer((int)$my_order->id_customer);

		list ($ip, $x_forwarded_for) = $this->getIpInfoForOrder( $order_id );

		$data = array();

		$this->fillGeneralOrderInfo( $data, $order_id, $my_order, $ip, null, $currency, $customer );
		if ($x_forwarded_for)
			$data['note']	= 'forwarded for: '.$x_forwarded_for;

		$this->fillLineItems( $data, $my_order );

		$carrier = new Carrier((int)$my_order->id_carrier, (int)$my_order->id_lang);
		$this->fillShippingDetails( $data, $my_order, $carrier );

		$payments = $my_order->getOrderPayments();
		$this->getPaymentDetails( $data, $payments );

		$address_invoice = new Address((int)$my_order->id_address_invoice);
		$this->getBillingAddress( $data, $address_invoice );

		$address_shipping = new Address((int)$my_order->id_address_delivery);
		$this->getShippingAddress( $data, $address_shipping );

		$this->fillCustomerInfo( $data, $customer );

		return $data;
	}/* End of getData() */

	public function uninstall()
	{
		if (!file_exists(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE))
			return false;
		if (!parent::uninstall() || !$this->deleteTables())
			return false;
		return true;
	} /* End of uninstall function */

	public function getContent()
	{
		$this->postProcess();
		return $this->displayConfiguration();
	}/* End of getContent function */

	private function displayConfiguration()
	{
		$this->smarty->assign(
			array(
				'order_id' => Tools::getValue('id_order'),
				'base_url' => _PS_BASE_URL_,
				'base_uri' => __PS_BASE_URI__,
				'ps_version' => _PS_VERSION_,
				'riskified_shop_domain' => Configuration::get('PS_SHOP_DOMAIN'),
				'riskified_auth_token' => Configuration::get('PS_AUTH_TOKEN'),
				'riskified_production_mode' => Configuration::get('RISKIFIED_MODE'),
				'riskified_api_settings' => 'Riskified API Settings',
				'post_action' => $_SERVER['REQUEST_URI']
				)
			);
		return $this->display(__FILE__, 'views/templates/admin/riskified_overview.tpl');
	} /* End of displayConfiguration() */

	public function hookDisplayHeader()
	{
		$this->smarty->assign('shop_url', Configuration::get('PS_SHOP_DOMAIN'));
		$this->smarty->assign('session_id', $this->context->cart->id );
		return $this->display(__FILE__, 'riskified_js.tpl');
	}

	public function postProcess()
	{
		if (Tools::isSubmit('submitSettings'))
		{
			Configuration::updateValue('PS_SHOP_DOMAIN', Tools::getValue('shop_domain'));
			Configuration::updateValue('PS_AUTH_TOKEN', Tools::getValue('auth_token'));
			Configuration::updateValue('RISKIFIED_MODE', Tools::getValue('riskified_mode'));
			if (Configuration::get('RISKIFIED_MODE') == '1')
				Logger::addLog('Riskified is in production mode ( '.Configuration::get('RISKIFIED_MODE').' )', 1);
			else
				Logger::addLog('Riskified is in sandbox mode ( '.Configuration::get('RISKIFIED_MODE').' )', 0);
			$this->html .= $this->displayConfirmation('API Settings updated');
		}

		if (count($this->_errors))
		{
			$err = '';
			foreach ($this->_errors as $error)
				$err .= $error.'<br />';
			$this->html .= $this->displayError($err);
		}
	}/* End of postProcess() */

	public function deleteTables()
	{
		return Db::getInstance()->execute('DROP TABLE IF EXISTS`'._DB_PREFIX_.self::RISKIFIED_TABLE_NAME.'`');
	}

	public function storeIpAndRemoteForOrder($order_id, $ip, $x_forwarded_for)
	{
		$sql = 'SELECT * FROM '._DB_PREFIX_.self::RISKIFIED_TABLE_NAME.' WHERE order_id ='.(int)$order_id;
		$row = Db::getInstance()->getRow($sql);
		if ($row == '')
			Db::getInstance()->insert(self::RISKIFIED_TABLE_NAME, array('order_id' => (int)$order_id,'remote_ip' => pSQL($ip),
				'x_forwarded_for' => pSQL($x_forwarded_for)));
	}

	public function getIpInfoForOrder($order_id)
	{
		$sql = 'SELECT * FROM '._DB_PREFIX_.self::RISKIFIED_TABLE_NAME.' WHERE order_id='.(int)$order_id;
		$row = Db::getInstance()->getRow($sql);
		if ($row != '')
		{
			$order_id = $row['order_id'];
			$ip = $row['remote_ip'];
			$x_forwarded_for = $row['x_forwarded_for'];
			return array( $ip, $x_forwarded_for );
		}
		return array( '', '' );
	}

}/* class */
?>
