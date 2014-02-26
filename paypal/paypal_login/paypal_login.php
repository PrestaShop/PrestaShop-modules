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

class PayPalLogin
{
	private	$_logs = array();
	private $enable_log = true;
	
	private $paypal = null;
	
	public function __construct()
	{
		$this->paypal = new PayPal();
	}

	public function makeConnection($host, $body = false, $http_header = false)
	{
		$this->_logs[] = $this->paypal->l('Making new connection to').' \''.$host.'\'';

		$return = false;

		if (function_exists('curl_exec'))
			$return = $this->_connectByCURL($host, $body, $http_header);

		return $return;
	}

	public function getLogs()
	{
		return $this->_logs;
	}

	/************************************************************/
	/********************** CONNECT METHODS *********************/
	/************************************************************/
	private function _connectByCURL($url, $body = false, $http_header = false)
	{
		$ch = @curl_init();

		if (!$ch)
			$this->_logs[] = $this->paypal->l('Connect failed with CURL method');
		else
		{
			$this->_logs[] = $this->paypal->l('Connect with CURL method successful');
			$this->_logs[] = '<b>'.$this->paypal->l('Sending this params:').'</b>';
			$this->_logs[] = 'Post : ' . $body;
			$this->_logs[] = 'Header : ' . print_r($http_header, true);

			@curl_setopt($ch, CURLOPT_URL, 'https://'.$url);
			@curl_setopt($ch, CURLOPT_USERPWD, Configuration::get('PAYPAL_LOGIN_CLIENT_ID').':'.Configuration::get('PAYPAL_LOGIN_SECRET'));
			@curl_setopt($ch, CURLOPT_POST, true);
			if ($body)
			{
				@curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
			}	
			@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			@curl_setopt($ch, CURLOPT_HEADER, false);
			@curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			@curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			@curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			@curl_setopt($ch, CURLOPT_SSLVERSION, 3);
			@curl_setopt($ch, CURLOPT_VERBOSE, true);

			if ($http_header && is_array($http_header))
			{
				@curl_setopt($ch, CURLOPT_HTTPHEADER, $http_header);
			}

			$result = @curl_exec($ch);

			if (!$result)
				$this->_logs[] = $this->paypal->l('Send with CURL method failed ! Error:').' '.curl_error($ch);
			else
				$this->_logs[] = $this->paypal->l('Send with CURL method successful');

			@curl_close($ch);
		}
		return $result ? $result : false;
	}

	public static function getReturnLink()
	{
		// return 'http://requestb.in/1jlaizq1';
		return Context::getContext()->shop->getBaseUrl().'modules/paypal/paypal_login/paypal_login_token.php';
	}

	public function getAuthorizationCode()
	{
		unset($this->_logs);

		if (Context::getContext()->cookie->isLogged())
		{
			return $this->getRefreshToken();
		}

		if ( Configuration::get('PAYPAL_SANDBOX') )
			$host = 'www.sandbox.paypal.com/webapps/auth/protocol/openidconnect/v1/tokenservice';
		else
			$host = 'api.paypal.com/v1/identity/openidconnect/tokenservice';


		$params = array(
			'grant_type'   => 'authorization_code',
			'code'         => Tools::getValue('code'),
			'redirect_url' => PayPalLogin::getReturnLink()
		);

		$request = http_build_query($params, '', '&');
		$result = $this->makeConnection($host, $request);

		if ($this->enable_log === true)
		{
			$handle = fopen(dirname(__FILE__) . '/Results.txt', 'a+');
			fwrite($handle, "Request => " . print_r($request, true) . "\r\n");
			fwrite($handle, "Result => " . print_r($result, true) . "\r\n");
			fwrite($handle, "Journal => " . print_r($this->_logs, true."\r\n"));
			fclose($handle);
		}

		$result = json_decode($result);

		if ($result)
		{

			$login = new PayPalLoginUser();

			$customer = $this->getUserInformations($result->access_token, $login);

			if (!$customer)
				return false;

			$temp = PaypalLoginUser::getByIdCustomer((int)Context::getContext()->cookie->id_customer);

			if ($temp)
				$login = $temp;

			$login->id_customer = $customer->id;
			$login->token_type = $result->token_type;
			$login->expires_in = (string) (time() + (int)$result->expires_in);
			$login->refresh_token = $result->refresh_token;
			$login->id_token = $result->id_token;
			$login->access_token = $result->access_token;

			$login->save();

			return $login;
		}
	}

	public function getRefreshToken()
	{
		unset($this->_logs);
		$login = PaypalLoginUser::getByIdCustomer((int)Context::getContext()->cookie->id_customer);

		if (!is_object($login))
			return false;

		if ( Configuration::get('PAYPAL_SANDBOX') )
			$host = 'www.sandbox.paypal.com/webapps/auth/protocol/openidconnect/v1/tokenservice';
		else
			$host = 'api.paypal.com/v1/identity/openidconnect/tokenservice';

		$params = array(
			'grant_type'    => 'refresh_token',
			'refresh_token' => $login->refresh_token
		);

		$request = http_build_query($params, '', '&');
		$result = $this->makeConnection($host, $request);

		if ($this->enable_log === true)
		{
			$handle = fopen(dirname(__FILE__) . '/Results.txt', 'a+');
			fwrite($handle, "Request => " . print_r($request, true) . "\r\n");
			fwrite($handle, "Result => " . print_r($result, true) . "\r\n");
			fwrite($handle, "Journal => " . print_r($this->_logs, true."\r\n"));
			fclose($handle);
		}

		$result = json_decode($result);

		if ($result)
		{
			$login->access_token = $result->access_token;
			$login->expires_in = (string) (time() + $result->expires_in);
			$login->save();
			return $login;
		}

		return false;
	}

	private function getUserInformations($access_token, &$login)
	{

		unset($this->_logs);
		if ( Configuration::get('PAYPAL_SANDBOX') )
			$host = 'api.sandbox.paypal.com/v1/identity/openidconnect/userinfo';
		else
			$host = 'api.paypal.com/v1/identity/openidconnect/userinfo';

		$headers = array(
			// 'Content-Type:application/json',
			'Authorization: Bearer '.$access_token
		);

		$params = array(
			'schema' => 'openid'
		);

		$request = http_build_query($params, '', '&');
		$result = $this->makeConnection($host, $request, $headers);

		if ($this->enable_log === true)
		{
			$handle = fopen(dirname(__FILE__) . '/Results.txt', 'a+');
			fwrite($handle, "Request => " . print_r($request, true) . "\r\n");
			fwrite($handle, "Result => " . print_r($result, true) . "\r\n");
			fwrite($handle, "Headers => " . print_r($headers, true) . "\r\n");
			fwrite($handle, "Journal => " . print_r($this->_logs, true."\r\n"));
			fclose($handle);
		}

		$result = json_decode($result);

		if ($result)
		{
			$customer = new Customer();
			$customer = $customer->getByEmail($result->email);

			if (!$customer)
			{
				$customer = $this->setCustomer($result);
			}

			$login->account_type = $result->account_type;
			$login->user_id = $result->user_id;
			$login->verified_account = $result->verified_account;
			$login->zoneinfo = $result->zoneinfo;
			$login->age_range = $result->age_range;

			return $customer;
		}

		return false;
	}

	public function getAuthorizeSemlessCheckout($client_id)
	{

		unset($this->_logs);
		if ( Configuration::get('PAYPAL_SANDBOX') )
			$host = 'sandbox.paypal.com/webapps/auth/protocol/openidconnect/v1/authorize';
		else
			$host = 'paypal.com/webapps/auth/protocol/openidconnect/v1/authorize';

		$params = array(
			'client_id'     => $client_id,
			'response_type' => 'code',
			'redirect_url'  => 'http://requestb.in/1jlaizq1',
			'scope'         => 'https://uri.paypal.com/services/expresscheckout',
		);

		$request = http_build_query($params, '', '&');
		$result = $this->makeConnection($host, $request, $params);

		if ($this->enable_log === true)
		{
			$handle = fopen(dirname(__FILE__) . '/Results.txt', 'a+');
			fwrite($handle, "Request => " . print_r($request, true) . "\r\n");
			fwrite($handle, "Result => " . print_r($result, true) . "\r\n");
			fwrite($handle, "Headers => " . print_r($params, true) . "\r\n");
			fwrite($handle, "Journal => " . print_r($this->_logs, true."\r\n"));
			fclose($handle);
		}

		$result = json_decode($result);

		if ($result)
		{
			$customer = new Customer();
			$customer = $customer->getByEmail($result->email);

			if (!$customer)
			{
				$customer = $this->setCustomer($result);
			}

			$login->account_type = $result->account_type;
			$login->user_id = $result->user_id;
			$login->verified_account = $result->verified_account;
			$login->zoneinfo = $result->zoneinfo;
			$login->age_range = $result->age_range;

			return $customer;
		}

		return false;

	}

	private function setCustomer($result)
	{
		$customer = new Customer();
		$customer->firstname = $result->given_name;
		$customer->lastname = $result->family_name;
		$customer->id_lang = Language::getIdByIso(strstr($result->language, '_', true));
		$customer->birthday = $result->birthday;
		$customer->email = $result->email;
		$customer->passwd = Tools::encrypt(Tools::passwdGen());
		$customer->save();

		$result_address = $result->address;

		$address = new Address();
		$address->id_customer = $customer->id;
		$address->id_country = Country::getByIso($result_address->country);
		$address->alias = 'My address';
		$address->lastname = $customer->lastname;
		$address->firstname = $customer->firstname;
		$address->address1 = $result_address->street_address;
		$address->psotcode = $result_address->postal_code;
		$address->city = $result_address->locality;
		$address->phone = $result->phone_number;

		$address->save();

		return $customer;
	}
}
