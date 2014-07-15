<?php
/**
 * 2013 Give.it
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@give.it so we can send you a copy immediately.
 *
 * @author    JSC INVERTUS www.invertus.lt <help@invertus.lt>
 * @copyright 2013 Give.it
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of Give.it
 */

class GiveItSdkClient extends GiveItSdkBase {
	private $cookie_cache;
	private $curl;
	private $sdk;
	private $authenticated = false;

	protected static $instance = null;

	public function __construct()
	{
		$this->sdk = GiveItSdk::getInstance();
		$this->cookie_cache = fopen('php://memory', 'w+');

		$this->setupCurl();
	}

	public function __destruct()
	{

	}

	/**
	 * Get the singleton
	 *
	 * @return object
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
			self::$instance = new GiveItSdkClient;

		return self::$instance;
	}

	public function setupCurl()
	{
		$this->curl = curl_init();

		$options = array(CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $this->cookie_cache, CURLOPT_COOKIEFILE => $this->cookie_cache, );

		curl_setopt_array($this->curl, $options);
	}

	public function authenticate()
	{
		if (!$this->sdk->private_key)
			return false;

		$data = array('key' => $this->sdk->private_key);

		$result = $this->sendPOST('/auth/retailer', $data);

		if (!$result)
		{
			$this->addError('could not authenticate');

			return false;
		}

		if (isset($result->errors))
		{
			foreach ($result->errors as $error)
				$this->addError($error);

			return false;
		}

		if ($result->result == 'ok')
		{
			$this->authenticated = true;
			return true;
		}

		return false;
	}

	public function getSales()
	{
		if (!$this->authenticated)
			$this->authenticate();

		$result = $this->sendGET('/sales');

		return $result;

	}

	public function sendGET($url, $data = false)
	{
		if (!$this->authenticated && !$this->authenticate())
			return false;

		$this->setupCurl();

		if (is_array($data) && !empty($data))
		{

			if (strpos($url, '?') === false)
				$url .= '&';
			else
				$url .= '?';

			$url .= http_build_query($data);
		}

		curl_setopt($this->curl, CURLOPT_URL, $this->sdk->getURL('api').$url);

		$response = curl_exec($this->curl);

		// TODO: what if we don't get back JSON? need error handling
		// TODO: also check status code
		return Tools::jsonDecode($response);

	}

	public function sendPOST($url, $data = false)
	{
		$this->setupCurl();

		curl_setopt($this->curl, CURLOPT_URL, $this->sdk->getURL('api').$url);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);

		$response = curl_exec($this->curl);

		if ($response === false)
		{
			$this->addError(curl_error($this->curl));
			return false;
		}

		return Tools::jsonDecode($response);
	}

	public function sendPUT($url, $data = false)
	{
		$this->setupCurl();

		curl_setopt_array($this->curl, array(CURLOPT_URL => $this->sdk->getURL('api').$url, CURLOPT_CUSTOMREQUEST => 'PUT', CURLOPT_POSTFIELDS => $data, ));

		$response = curl_exec($this->curl);

		return Tools::jsonDecode($response);
	}

}
