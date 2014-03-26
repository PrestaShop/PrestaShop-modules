<?php
/**
 * @copyright    give.it 2013
 * @author       David Kelly
 *
 * required:
 *
 * PHP > 5.3.0 with modules:
 * - mcrypt
 * - curl
 * - json
 */

class GiveItSdk extends GiveItSdkBase {
	const VERSION = '1.1.4-PrestaShop';

	public $data_key = null;
	public $public_key = null;
	public $private_key = null;
	public $debug = false;

	private $environment = 'live';
	private $js_output = false;
	private $sales = null;
	private $payments = null;

	private $urls = array(
			'live'      => array(
					'api'       => 'https://api.give.it',
					'widget'    => '//widget.give.it'
			),
			'sandbox'   => array(
					'api'       => 'https://api.sandbox.give.it',
					'widget'    => '//widget.sandbox.give.it'
			)
	);


	protected static $instance = null;

	public function __construct($settings = array())
	{
		$this->registerInstance();

		if (is_array($settings))
		{

			$allowed = array('public_key', 'private_key', 'data_key', 'environment', 'debug');

			foreach ($allowed as $type)
			{
				if (isset($settings[$type]))
					$this->$type = $settings[$type];
			}
		}

		// fall back to constants if nothing defined
		$this->getKeysFromConstants();

		return true;
	}

	private function getKeysFromConstants()
	{
		if ($this->public_key == null && defined('GIVEIT_PUBLIC_KEY'))
			$this->public_key = GIVEIT_PUBLIC_KEY;

		if ($this->private_key == null && defined('GIVEIT_PRIVATE_KEY'))
			$this->private_key = GIVEIT_PRIVATE_KEY;

		if ($this->data_key == null && defined('GIVEIT_DATA_KEY'))
			$this->data_key = GIVEIT_DATA_KEY;

		return true;
	}

	/**
	 * Get the singleton
	 *
	 * @return object
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
			self::$instance = new GiveItSdkSDK;

		return self::$instance;
	}

	/**
	 * Register the singleton
	 *
	 * @return boolean
	 */
	protected function registerInstance()
	{
		self::$instance = $this;

		return true;
	}

	private function setupRenderer()
	{
		$this->renderer = new GiveItSdkRenderer;
	}

	public function setEnvironment($environment)
	{
		if (!isset($this->urls[$environment]))
			return false;

		$this->environment = $environment;

		return true;
	}

	public function getEnvironment()
	{
		return $this->environment;
	}

	public function getURL($type)
	{
		return $this->urls[$this->environment][$type];
	}

	public function getButtonJS()
	{
		$text = Tools::file_get_contents(dirname(__FILE__).'/../../js/widget.js');
		$text = str_replace('$widgetUrl', $this->urls[$this->environment]['widget'], $text);
		$text = str_replace('$public_api_key', $this->public_key, $text);

		return $text;
	}

	/**
	 * This function outputs the JS inclusion for the button
	 * @return boolean
	 */
	public function outputButtonJS()
	{
		if ($this->js_output)
			return true;

		echo $this->getButtonJS();

		$this->js_output = true;

		return true;
	}

	public function getCallbackType($post_data)
	{
		return $post_data['type'];
	}

	public function parseCallback($post_data)
	{
		$type = Tools::ucfirst($post_data['type']);
		$class = 'GiveItSdkCallback\\'.$type;
		$callback = new $class;
		$parsed = $callback->parse($post_data);

		return $parsed;
	}

	public function sales()
	{
		if (!$this->sales)
			$this->sales = new GiveItSdkCollection('Sale');

		return $this->sales;
	}

	public function payments()
	{
		if (!$this->payments)
			$this->payments = new GiveItSdkCollection('Payment');

		return $this->payments;
	}

	public function verifyKeys()
	{
		$client = GiveItSdkClient::getInstance();
		$authenticated = $client->authenticate();

		if (!$authenticated)
		{
			$this->addError('unable to log in with private key');
			return false;
		}

		$result = $client->sendGET('/retailers/me');

		if (isset($result->errors))
		{
			foreach ($result->errors as $error)
				$this->addError($error);

			return false;
		}

		// at this point we can assume that the private key is okay, verify the other two

		if ($result->public_api_key != $this->public_key)
		{
			$this->addError('incorrect public key');
			return false;
		}

		if ($result->data_key != $this->data_key)
		{
			$this->addError('incorrect data key');
			return false;
		}

		return true;

	}

}
