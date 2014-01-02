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
	
if (!defined('_PS_VERSION_'))
	exit;

define('TRUSTLY_TEST_MODE', 0);
define('TRUSTLY_LIVE_MODE', 1);

class Trustly extends PaymentModule
{
	public static $_mode = TRUSTLY_LIVE_MODE;

	protected $json_rpc_target_host = 'trustly.com';
	protected $signup_target_host = 'trustly.com';

	protected $json_rpc_target_path = '/api/1';
	protected $json_rpc_target_path_legacy = '/api/Legacy';

	public function __construct()
	{
		$this->name = 'trustly';
		$this->tab = 'payments_gateways';
		$this->version = '1.3.1';
		$this->limited_countries = array('es');

		$this->currencies = true;
		$this->currencies_mode = 'radio';

		$this->json_rpc_target_host = (self::$_mode == TRUSTLY_TEST_MODE ? 'test.' : null).$this->json_rpc_target_host;
		$this->signup_target_host = (self::$_mode == TRUSTLY_TEST_MODE ? 'test.' : null).$this->signup_target_host;

		parent::__construct();

		$this->displayName = 'Trustly';
		$this->description = $this->l('Allow your customers to pay from their online bank account directly in your web-shop. The only payment method in Spain with 90% bank coverage.');

		/* For 1.4.3 and prior compatibility */
		$array_name = array('PS_OS_CHEQUE', 'PS_OS_PAYMENT', 'PS_OS_PREPARATION', 'PS_OS_SHIPPING', 'PS_OS_CANCELED', 'PS_OS_REFUND', 'PS_OS_ERROR', 'PS_OS_OUTOFSTOCK', 'PS_OS_BANKWIRE', 'PS_OS_PAYPAL', 'PS_OS_WS_PAYMENT');
		if (!Configuration::get('PS_OS_PAYMENT'))
			foreach ($array_name as $name)
				if (!Configuration::get($name) && defined('_'.$name.'_'))
					Configuration::updateValue($name, constant('_'.$name.'_'));

		/* Backward compatibility */
		require(_PS_MODULE_DIR_.'trustly/backward_compatibility/backward.php');
	}
	
	public function install()
	{
		if (Tools::getValue('redirect') == "config")
			return parent::install() && $this->registerHook('payment') && $this->registerHook('paymentReturn');
		return parent::install() && $this->registerHook('payment') && $this->registerHook('paymentReturn') && $this->generateKeyPair();
	}
	
	public function getContent()
	{
		$html = '';
		
		// Check the requirements
		$allow_url_fopen = ini_get('allow_url_fopen');
		$openssl = extension_loaded('openssl');
		$ping = ($allow_url_fopen AND $openssl AND $fd = fsockopen($this->json_rpc_target_host, 443) AND fclose($fd));
		$online = (in_array(Tools::getRemoteAddr(), array('127.0.0.1', '::1')) ? false : true);

		// If the requirements are not met, display a warning
		if (!$allow_url_fopen OR !$openssl OR !$ping OR !$online)
		{
			$html .= '
			<div class="warn">
				'.($allow_url_fopen ? '' : '<h3>'.$this->l('You are not allowed to open external URLs. \'allow__url_fopen\' should be turned on.').'</h3>').'
				'.($openssl ? '' : '<h3>'.$this->l('OpenSSL is not enabled').'</h3>').'
				'.(($allow_url_fopen AND $openssl AND !$ping) ? '<h3>'.$this->l('Cannot access payment gateway:').' '.$this->json_rpc_target_host.':443 ('.$this->l('check your firewall or ask your hosting service to do it for you').')</h3>' : '').'
				'.($online ? '' : '<h3>'.$this->l('Your shop is not online').'</h3>').'
			</div>';
		}
		
		if (Tools::isSubmit('submitPersonalSave'))
			$this->savePreactivationRequest();
		elseif (Tools::isSubmit('submitPersonalCancel'))
			Configuration::updateValue('TRUSTLY_PHONE', '');
	
		// Handle the form submission and display a confirmation message
		if (Tools::isSubmit('submitTrustly') || Tools::isSubmit('submitTrustlyGenerateKeyPair'))
		{
			$change = false;
			if (Configuration::get('TRUSTLY_USERNAME') != Tools::getValue('TRUSTLY_USERNAME'))
				Configuration::updateValue('TRUSTLY_USERNAME', trim(Tools::getValue('TRUSTLY_USERNAME'))) && $change = true;
			if (Configuration::get('TRUSTLY_PASSWORD') != Tools::getValue('TRUSTLY_PASSWORD'))
				Configuration::updateValue('TRUSTLY_PASSWORD', trim(Tools::getValue('TRUSTLY_PASSWORD'))) && $change = true;
			if (Configuration::get('TRUSTLY_MERCHANT_PRIVATE_KEY') != Tools::getValue('TRUSTLY_MERCHANT_PRIVATE_KEY'))
				Configuration::updateValue('TRUSTLY_MERCHANT_PRIVATE_KEY', trim(Tools::getValue('TRUSTLY_MERCHANT_PRIVATE_KEY'))) && $change = true;
			if (Tools::getValue('TRUSTLY_MERCHANT_PUBLIC_KEY') !== false && Configuration::get('TRUSTLY_MERCHANT_PUBLIC_KEY') != Tools::getValue('TRUSTLY_MERCHANT_PUBLIC_KEY'))
				Configuration::updateValue('TRUSTLY_MERCHANT_PUBLIC_KEY', trim(Tools::getValue('TRUSTLY_MERCHANT_PUBLIC_KEY'))) && $change = true;

			try {
				$this->testUserConfiguration();
			} catch(Exception $e) {
				$html .= '<div class="warn">'.$e->getMessage().'</div>';
			}
			
			if ($change)
				$html .= $this->displayConfirmation($this->l('Configuration updated'));
		}
		
		$this->context->smarty->assign('shop_phone', Configuration::get('PS_SHOP_PHONE'));
	
		// A small javascript snippet that enables the user to display/hide his password
		$html .= '
		<script type="text/javascript">
			function trustly_togglePasswordView()
			{
				if ($("#TRUSTLY_PASSWORD").attr("type") == "password")
					$("#TRUSTLY_PASSWORD").get(0).type = "text";
				else
					$("#TRUSTLY_PASSWORD").get(0).type = "password";
			}
		</script>';
		
		// Subscription form
		$html .= '
		<fieldset style="margin:auto">
			<legend><img src="../img/admin/contact.gif" />'.$this->l('Open a Trustly account').'</legend>';
		if (Tools::isSubmit('submitTrustlyNewAccount'))
		{
			$params = http_build_query(array(
				'LegalSignatoryFirstname' => $this->context->employee->firstname,
				'LegalSignatoryLastname' => $this->context->employee->lastname,
				'LegalSignatoryEmail' => $this->context->employee->email,
				'LegalSignatoryStreet' => Configuration::get('PS_SHOP_ADDR1'),
				'LegalSignatoryZipCode' => Configuration::get('PS_SHOP_CODE'),
				'LegalSignatoryCity' => Configuration::get('PS_SHOP_CITY'),
				'MerchantPublicKey' => Configuration::get('TRUSTLY_MERCHANT_PUBLIC_KEY'),
			), '&');
			
			$html .= '<iframe id="trustly_iframe" src="https://trustly.com/signup/prestashop?'.$params.'" style="width:100%;height:2250px;border:0;"></iframe>';
		}
		else
		{
			$html .= '
			<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post">
				<p><input type="submit" class="button" name="submitTrustlyNewAccount" style="cursor:pointer" value="Open a new account" /></p>
			</form>';
		}
	
		$html .= '
			</fieldset>
			<div class="clear">&nbsp;</div>';
		
		// Configuration form
		$html .= '
		<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post">
			<fieldset style="margin:auto"><legend><img src="../modules/'.Tools::safeOutput($this->name).'/logo.gif" style="vertical-align:middle" /> '.$this->l('Trustly settings').'</legend>
				
				<div  style="margin:2%;width:46%;float:left">
					<p style="font-weight:bold;">'.$this->l('Don\'t have a Trustly account?').'<br />'.$this->l('Continue with the following steps:').'</p>
					<ol style="list-style:decimal;padding-left:0;margin-left:25px">
						<li style="margin-bottom:8px">'.$this->l('Register for a Trustly account by pressing "Open a new account" and fill out the Signup form').'</li>
						<li style="margin-bottom:8px">'.$this->l('Enter the Username and Password you have created in the Signup form into the corresponding fields below').'</li>
						<li style="margin-bottom:8px">'.$this->l('Press "Update settings"').'</li>
					</ol>
				</div>
				<div  style="margin:2%;width:46%;float:left">
					<p style="font-weight:bold;">'.$this->l('Already signed up for a Trustly account?').'<br />'.$this->l('Continue with the following steps:').'</p>
					<ol style="list-style:decimal;padding-left:0;margin-left:25px;">
						<li style="margin-bottom:8px">'.$this->l('Enter the Username and Password you have created in the Signup form into the corresponding fields below').'</li>
						<li style="margin-bottom:8px">'.$this->l('Replace the existing Private key in the field below with the Private key you received from your sign up').'</li>
						<li style="margin-bottom:8px">'.$this->l('Press "Update settings"').'</li>
					</ol>
				</div>
			
				<div style="margin:20px">
					<p>'.$this->l('You can monitor your transactions and get support in the Trustly backoffice.').'<br />'.$this->l('Login with your Username and Password at :').' <strong><a href="http://www.trustly.com/backoffice" target="_blank">'.$this->l('http://www.trustly.com/backoffice').'</a></strong></p>
				</div>
				<label for="TRUSTLY_USERNAME">'.$this->l('Username').'</label>
				<div class="margin-form">
					<input type="text" id="TRUSTLY_USERNAME" name="TRUSTLY_USERNAME" value="'.Tools::safeOutput(Tools::getValue('TRUSTLY_USERNAME', Configuration::get('TRUSTLY_USERNAME'))).'" />
				</div>
				<label for="TRUSTLY_PASSWORD">'.$this->l('Password').'</label>
				<div class="margin-form">
					<input type="password" id="TRUSTLY_PASSWORD" name="TRUSTLY_PASSWORD" value="'.Tools::safeOutput(Tools::getValue('TRUSTLY_PASSWORD', Configuration::get('TRUSTLY_PASSWORD'))).'" />
					<img src="../modules/'.Tools::safeOutput($this->name).'/eye.png" style="vertical-align:middle;cursor:pointer" onclick="trustly_togglePasswordView()" />
				</div>
				<div class="clear">&nbsp;</div>';
		$html .= '
				<label for="TRUSTLY_MERCHANT_PRIVATE_KEY">'.$this->l('Your private key').'</label>
				<div class="margin-form" style="font-size:1em;">
					<textarea id="TRUSTLY_MERCHANT_PRIVATE_KEY" name="TRUSTLY_MERCHANT_PRIVATE_KEY" style="width:49em;height:360px;font-family: \'Courier New\', Courier, \'Liberation Mono\', monospace;font-size:.95em;">'.Tools::safeOutput(Tools::getValue('TRUSTLY_MERCHANT_PRIVATE_KEY', Configuration::get('TRUSTLY_MERCHANT_PRIVATE_KEY'))).'</textarea>
				</div>
				<div class="clear">&nbsp;</div>';
		if (!Configuration::get('TRUSTLY_MERCHANT_PRIVATE_KEY'))
			$html .= '
				<label for="TRUSTLY_MERCHANT_PUBLIC_KEY">'.$this->l('Your public key').'</label>
				<div class="margin-form" style="font-size:1em;">
					<textarea id="TRUSTLY_MERCHANT_PUBLIC_KEY" name="TRUSTLY_MERCHANT_PUBLIC_KEY" style="width:49em;height:140px;font-family: \'Courier New\', Courier, \'Liberation Mono\', monospace;font-size:.95em;">'.Tools::safeOutput(Tools::getValue('TRUSTLY_MERCHANT_PUBLIC_KEY', Configuration::get('TRUSTLY_MERCHANT_PUBLIC_KEY'))).'</textarea>
				</div>';
		$html .= '
				<div class="clear">&nbsp;</div>
				<input type="submit" name="submitTrustly" value="'.$this->l('Update settings').'" class="button" style="margin-left:210px;margin-bottom:20px" />
			</fieldset>
		</form>
		<div class="clear">&nbsp;</div>';
		
		if (Configuration::get('TRUSTLY_PHONE') === false)
			return $html.$this->context->smarty->fetch(_PS_MODULE_DIR_.$this->name.'/views/templates/admin/fancybox.tpl');
		else
			return $html;
	}
	
	protected function savePreactivationRequest()
	{
		$phone = Tools::getValue('TRUSTLY_PHONE');

		if (empty($phone))
		{
			$this->context->smarty->assign('phone_error', true);
			return null;
		}
		
		Configuration::updateValue('TRUSTLY_PHONE', $phone);
		
		$employee = new Employee((int) Context::getContext()->cookie->id_employee);

		$data = array(
			'iso_lang' => strtolower($this->context->language->iso_code),
			'iso_country' => strtoupper($this->context->country->iso_code),
			'host' => $_SERVER['HTTP_HOST'],
			'ps_version' => _PS_VERSION_,
			'ps_creation' => _PS_CREATION_DATE_,
			'partner' => $this->name,
			'firstname' => $employee->firstname,
			'lastname' => $employee->lastname,
			'email' => $employee->email,
			'shop' => Configuration::get('PS_SHOP_NAME'),
			'type' => 'home',
			'phone' => $phone,
		);

		$query = http_build_query($data);

		return @Tools::file_get_contents('http://api.prestashop.com/partner/premium/set_request.php?' . $query);
	}
	
	public function hookPaymentReturn($params)
	{
		return $this->l('Thank you!').'<br />'.$this->l('Your payment was successful! ').'<br />'.$this->l('Always choose Trustly to ensure maximum security for your online payments.').'<br />';
	}
	
	public function hookPayment($params)
	{
		// Trustly only handle Euros
		if (Tools::strtoupper(Context::getContext()->currency->iso_code) != 'EUR')
			return;
		
		if (method_exists('Link', 'getModuleLink'))
			$this->context->smarty->assign('trustly_url', $this->context->link->getModuleLink('trustly', 'iframe', array(), true));
		else
			$this->context->smarty->assign('trustly_url', __PS_BASE_URI__.'modules/'.$this->name.'/controllers/front/iframe.php?compat14');
		$this->context->smarty->assign('module_dir', __PS_BASE_URI__.((int)Configuration::get('PS_REWRITING_SETTINGS') && isset($smarty->ps_language) && !empty($smarty->ps_language) ? $smarty->ps_language->iso_code.'/' : '').'modules/'.$this->name.'/');
		
		// This template is just a text and a logo that redirect to a new page that contains Trustly's iframe
		return $this->context->smarty->fetch(dirname(__FILE__).'/views/templates/hook/payment.tpl');
	}
	
	protected function testUserConfiguration()
	{
		// Deprecated at the moment, but may be useful in the future
		return true;
		
		$api_params = array(
			'Username' => Configuration::get('TRUSTLY_USERNAME'),
			'Password' => Configuration::get('TRUSTLY_PASSWORD'),
			'MerchantUsername' => Configuration::get('TRUSTLY_USERNAME'),
			'MerchantPassword' => Configuration::get('TRUSTLY_PASSWORD'),
			'MerchantIPRange' => $_SERVER['SERVER_ADDR'].'/32',
			'MerchantPublicKey' => Configuration::get('TRUSTLY_MERCHANT_PUBLIC_KEY')
		);
		$payload_array = array(
			'method' => 'SignupMerchant',
			'params' => $api_params,
			'version' => '1.1'
		);
		$payload_json = Tools::jsonEncode($payload_array);
		
		$stream_context_opts = array(
			'http' => array(
				'method'  => 'POST',
				'header'  => 'Content-type:application/json',
				'content' => $payload_json,
				'user_agent' => 'PrestaShop/'._PS_VERSION_,
				'timeout' => 10,
				'ignore_errors', true
			)
		);
		
		// Everything is now sent to trustly and the response handled
        $context  = stream_context_create($stream_context_opts);
        if (!($fd = fopen('https://'.$this->signup_target_host.$this->json_rpc_target_path_legacy, 'r', false, $context)))
			throw new Exception($this->l('Unable to connect to Trustly server'));
        if (!($http_response = stream_get_contents($fd)))
			throw new Exception($this->l('Unable to read the response from Trustly server'));
		fclose($fd);
		
		$api_result_object = Tools::jsonDecode($http_response, true);
		if (isset($api_result_object['error']))
			throw new Exception($this->l('Error').' '.$api_result_object['error']['code'].' - '.$api_result_object['error']['message']);
		
		return true;
	}
	
	/** Call a web service hosted by Trustly and returns the URL of the iframe that must be displayed to the customer */
	public function retrievePaymentUrl()
	{
		$context = Context::getContext();
		if (Tools::strtoupper($context->currency->iso_code) != 'EUR')
			return;

		if (!$context->country->id)
			$context->country = new Country((int)Configuration::get('PS_COUNTRY_DEFAULT'));

		$address = new Address($context->cart->id_address_invoice);
	
		$api_method = 'Deposit';
		$api_version = '1.1';
		
		// Those are the data that Trustly need to process the payment
		$api_attributes = array(
			'Locale' => $context->language->iso_code.'_'.$context->country->iso_code,
			'Amount' => number_format($context->cart->getOrderTotal(), 2, '.', ''),
			'Currency' => $context->currency->iso_code,
			'Country' => $context->country->iso_code,
			'IP' => Tools::getRemoteAddr(),
			'SuccessURL' => Tools::getHttpHost(true, false).__PS_BASE_URI__.'order-confirmation.php?id_cart='.$context->cart->id.'&id_module='.$this->id.'&key='.$context->customer->secure_key,
			'MobilePhone' => (empty($address->phone) ? $address->phone_mobile : $address->phone),
			'Firstname' => $address->firstname,
			'Lastname' => $address->lastname,
			//'NationalIdentificationNumber' => '' // Not needed
		);
		
		// Those are the data needed by the Web Service and the ones that will be returned to the merchant website in the notification call
		$api_data = array(
			'Username' => Configuration::get('TRUSTLY_USERNAME'),
			'Password' => Configuration::get('TRUSTLY_PASSWORD'),
			'NotificationURL' => Tools::getHttpHost(true, false).__PS_BASE_URI__.'modules/'.$this->name.'/validation.php', // Todo: change for a random URL
			'EndUserID' => $context->cart->id_customer,
			'MessageID' => uniqid($context->cart->id.'-', true),
			'Attributes' => $api_attributes
		);
		
		// The couple UUID and MessageID must really be unique each time
		$api_uuid = $this->generateUUID();
		$api_signature = $this->trustly_sign($api_method, $api_uuid, $api_data);
		
		// JSON-RPC parameters
		$api_params = array(
			'Signature' => $api_signature,
			'UUID' => $api_uuid,
			'Data' => $api_data
		);
		
		// Now we can construct the POST payload that will be sent to Trustly
		$payload_array = array(
			'method' => $api_method,
			'params' => $api_params,
			'version' => $api_version
		);
		
		// Final touch: the payload must be in a JSON format
		$payload_json = Tools::jsonEncode($payload_array);

		$stream_context_opts = array(
			'http' => array(
				'method'  => 'POST',
				'header'  => 'Content-type:application/json',
				'content' => $payload_json,
				'user_agent' => 'PrestaShop/'._PS_VERSION_,
				'timeout' => 10
			)
		);
		
		// Everything is now sent to trustly and the response handled
        $context  = stream_context_create($stream_context_opts);
        if (!($fd = fopen('https://'.$this->json_rpc_target_host.$this->json_rpc_target_path, 'r', false, $context)))
			throw new Exception($this->l('Unable to connect to Trustly server'));
        if (!($http_response = stream_get_contents($fd)))
			throw new Exception($this->l('Unable to read the response from Trustly server'));
		fclose($fd);

		// The raw (json) response is converted to something more manageable 
		$api_result_object = Tools::jsonDecode($http_response, true);
		if (!isset($api_result_object['result']))
		{
			$message = Tools::safeOutput($this->getErrorMessage($api_result_object));
			throw new Exception($this->l('An error occured during the communication with Trustly web service').'<br/>'.$message);
		}
		if (!$this->trustly_verify($api_result_object['result']['method'], $api_result_object['result']['uuid'], $api_result_object['result']['data'], $api_result_object['result']['signature']))
			throw new Exception($this->l('The response from Trustly server cannot be verified'));
			
		// Eventually, we can return the URL of the iframe!
		return $api_result_object['result']['data']['url'];
	}
	
	public function getErrorMessage($result_object)
	{
		if (is_array($result_object) && isset($result_object['error']))
			return $this->l('Error').' '.$result_object['error']['code'].' : '.$result_object['error']['message'];
		return null;
	}
	
	public function processNotification($http_raw_post_data)
	{
		// First we convert the raw post data to something that we can read
		$http_post_object = Tools::jsonDecode($http_raw_post_data, true);

		if (!isset($http_post_object['method']))
			return false;
		
		// Errors are managed with exceptions, so if there is not any exception then the status is OK
		$status = 'OK';
		try {
			if ($http_post_object['method'] != 'credit')
				throw new Exception($this->l('Method not supported'));
			if (!$this->trustly_verify($http_post_object['method'], $http_post_object['params']['uuid'], $http_post_object['params']['data'], $http_post_object['params']['signature']))
				throw new Exception($this->l('The response from Trustly server cannot be verified'));

			// We can save the order in the database, PrestaShop handle everything by itself
			$id_cart = intval($http_post_object['params']['data']['messageid']);
			$cart = new Cart($id_cart);
			$id_currency = (int)Currency::getIdByIsoCode($http_post_object['params']['data']['currency']);

			if ((bool)$id_currency === false)
				$payment_status = Configuration::get('PS_OS_ERROR');
			else
				$payment_status = Configuration::get('PS_OS_PAYMENT');
				
			if (!$cart->orderExists())
			{
				$customer = new Customer($http_post_object['params']['data']['enduserid']);
				$this->validateOrder(
					$id_cart,
					$payment_status, 
					$http_post_object['params']['data']['amount'],
					$this->displayName,
					print_r($http_post_object['params']['data'], true),
					array(),
					$id_currency,
					false,
					$customer->secure_key
				);
			}
		} catch(Exception $e) {
			// This status will be returned to Trustly
			$status = 'FAILED';
		}

		// The response is prepared then returned
		$response_api_method = $http_post_object['method'];
		$response_api_version = '1.1';
		$response_api_uuid = $http_post_object['params']['uuid'];
		$response_api_data = array(
			'status' => $status
		);
		$response_api_signature = $this->trustly_sign($response_api_method, $response_api_uuid, $response_api_data);
		$response_api_result = array(
			'method' => $response_api_method,
			'signature' => $response_api_signature,
			'uuid' => $response_api_uuid,
			'data' => $response_api_data
		);
		$response_array = array(
			'result' => $response_api_result,
			'version' => $response_api_version
		);
		$response_json = Tools::jsonEncode($response_array);
		return $response_json;
	}

	protected function generateUUID()
	{
		// The risk of collisions is low enough with a MD5
		$md5 = md5(uniqid('', true));
		return substr($md5, 0, 8).'-'.substr($md5, 8, 4).'-'.substr($md5, 12, 4).'-'.substr($md5, 16, 4).'-'.substr($md5, 20, 12);
	}
	
	protected function generateKeyPair()
	{
		include(dirname(__FILE__).'/phpseclib/Crypt/RSA.php');
		$rsa = new Crypt_RSA();
		extract($rsa->createKey(2048));
		
		// Override POST data
		$_POST['TRUSTLY_MERCHANT_PRIVATE_KEY'] = trim($privatekey);
		$_POST['TRUSTLY_MERCHANT_PUBLIC_KEY'] = trim($publickey);
		Configuration::updateValue('TRUSTLY_MERCHANT_PRIVATE_KEY', $_POST['TRUSTLY_MERCHANT_PRIVATE_KEY']);
		Configuration::updateValue('TRUSTLY_MERCHANT_PUBLIC_KEY', $_POST['TRUSTLY_MERCHANT_PUBLIC_KEY']);
		return true;
	}
	
	/* Original function: http://www.trustly.com/en/developer/api/#/signature */
	protected function trustly_serialize_data($object)
	{
		$serialized = '';
		if (is_array($object))
		{
			ksort($object); // Sort keys
			foreach($object as $key => $value)
				if (is_numeric($key)) // Array
					$serialized .= $this->trustly_serialize_data($value);
				else // Hash
					$serialized .= $key.$this->trustly_serialize_data($value);
		}
		else
			return $object; // Scalar
		return $serialized;
	}

	/* Original function: http://www.trustly.com/en/developer/api/#/signature */
	protected function trustly_sign($method, $uuid, $data)
	{
		$merchant_private_key = openssl_get_privatekey(Configuration::get('TRUSTLY_MERCHANT_PRIVATE_KEY'));
		$plaintext = $method.$uuid.$this->trustly_serialize_data($data);
		openssl_sign($plaintext, $signature, $merchant_private_key);
		return base64_encode($signature);
	}
	
	/* Original function: http://www.trustly.com/en/developer/api/#/signature */
	protected function trustly_verify($method, $uuid, $data, $signature_from_trustly)
	{
		$key_content = file_get_contents(dirname(__FILE__).'/keys/trustly_public_key_'.(self::$_mode == TRUSTLY_TEST_MODE ? 'test' : 'live').'.pem');
		$trustly_public_key = openssl_get_publickey($key_content);
		$plaintext = $method.$uuid.$this->trustly_serialize_data($data);
		return openssl_verify($plaintext, base64_decode($signature_from_trustly), $trustly_public_key);
	}
}
