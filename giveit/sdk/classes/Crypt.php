<?php
/**
 * @copyright    give.it 2013
 * @author       David Kelly
 *
 * required:
 * - PHP > 5.3.0
 * - libmcrypt >= 2.4.x
 */

class GiveItSdkCrypt extends GiveItSdkBase {
	private $cipher = false;
	private $ciphers = array('rijndael-128', 'blowfish');

	public $debug = false;

	protected static $instance = null;

	public function __construct()
	{
		$this->registerInstance();

		if (!function_exists('mcrypt_encrypt'))
		{
			$this->addError('mcrypt functions not available');
			return false;
		}

		if (!$this->getCipher())
			return false;

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
			self::$instance = new GiveItSdkCrypt;

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

	/*
	 * Returns the mcrypt constant identifying the first available cipher from $this->ciphers,
	 * or false if none are in the mcrypt algorithms list.
	 */
	private function getCipher()
	{
		if ($this->cipher !== false)
			return $this->cipher;

		$available = mcrypt_list_algorithms();

		foreach ($this->ciphers as $cipher)
		{
			if (in_array($cipher, $available))
			{
				$this->cipher = $cipher;
				return constant('MCRYPT_'.Tools::strtoupper(str_replace('-', '_', $cipher)));
			}
		}

		$this->addError('no available cipher');

		return false;
	}

	/**
	 * Encode data into a single string
	 *
	 * @param    string  $data
	 * @return   string
	 */
	public function encode($plain_text, $key = null)
	{
		$cipher = $this->getCipher();

		if ($cipher == false)
			return false;

		if ($key == null)
		{
			$sdk = GiveItSdk::getInstance();
			$key = $sdk->data_key;
		}

		if ($key == null)
		{
			$this->addError('missing key for encryption');
			return false;
		}

		$iv = mcrypt_create_iv(mcrypt_get_iv_size($cipher, MCRYPT_MODE_CBC), MCRYPT_RAND);
		$td = mcrypt_module_open($cipher, '', MCRYPT_MODE_CBC, '');

		mcrypt_generic_init($td, $key, $iv);

		$text = $this->pkcs5Pad($plain_text, mcrypt_enc_get_block_size($td));
		// manually pad the data since mcrypt doesn't do this properly
		$encrypted = mcrypt_generic($td, $text);
		$crypt = base64_encode($iv.$encrypted);
		$crypt = urlencode($crypt);

		if ($this->debug)
		{
			echo "\n---- cipher ---\n".$this->cipher;
			echo "\n---- text  ----\n".$text;
			echo "\n---- crypt ----\n".$crypt;
			echo "\n---------------\n";
		}

		return $crypt;
	}

	/**
	 * Encode data into a single string
	 *
	 * @param    string  $data
	 * @return   string
	 */
	public function decode($text, $key = null)
	{
		$cipher = $this->getCipher();

		if ($cipher == false)
			return false;

		if ($key == null)
		{
			$this->addError('missing key for encryption');
			return false;
		}

		$crypt = base64_decode(urldecode($text));
		$iv_size = mcrypt_get_iv_size($cipher, MCRYPT_MODE_CBC);
		$iv = Tools::substr($crypt, 0, $iv_size);
		$text = Tools::substr($crypt, $iv_size);
		mcrypt_module_open($cipher, '', MCRYPT_MODE_CBC, '');
		$plain = mcrypt_decrypt($cipher, $key, $text, MCRYPT_MODE_CBC, $iv);

		return $plain;
	}

	private function pkcs5Pad($text, $blocksize)
	{
		$pad = $blocksize - (Tools::strlen($text) % $blocksize);
		return $text.str_repeat(chr($pad), $pad);
	}

}
