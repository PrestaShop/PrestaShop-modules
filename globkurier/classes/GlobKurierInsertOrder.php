<?php
/*
 * 2007-2014 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 *  @license	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class GlobKurierInsertOrder extends ObjectModel {

	const PS_METHOD = 'INSERT';
	const PS_SOURCE = 'PS';

	private $str_login;
	private $str_passwd;
	private $str_api_key;
	private $str_token;
	private $str_source;
	private $int_id_cart;
	private $str_reference;
	private $str_order_number;
	private $int_id_customer;
	private $str_secure_key;
	private $str_shop_key;
	private $str_date;
	private $str_recipient_name;
	private $str_recipient_address_1;
	private $str_recipient_address_2;
	private $str_recipient_zip_code;
	private $str_recipient_city;
	private $str_recipient_phone;
	private $str_recipient_mail;
	private $arr_post = array();

	/**
	 * Set data to logon in globkurier
	 * 
	 * @param string $str_login
	 * @param string $str_passwd
	 * @param string $str_api_key
	 * @return void
	 */
	public function __construct($str_login, $str_passwd, $str_api_key)
	{
		$this->str_login = $str_login;
		$this->str_passwd = $str_passwd;
		$this->str_api_key = $str_api_key;
	}

	/**
	 * Given encrypt login
	 * 
	 * @param void
	 * @return string
	 */
	protected function getLogin()
	{
		return $this->str_login;
	}

	protected function getPassword()
	{
		return $this->str_passwd;
	}

	protected function getApiKey()
	{
		return $this->str_api_key;
	}

	public function setStrToken($str_token)
	{
		$this->str_token = $str_token;
	}

	protected function getStrToken()
	{
		return $this->str_token;
	}

	public function setIntIdCart($int_id_cart)
	{
		$this->int_td_cart = $int_id_cart;
	}

	protected function getIntIdCart()
	{
		return $this->int_id_cart;
	}

	public function setStrReference($str_reference)
	{
		$this->str_reference = $str_reference;
	}

	protected function getStrReference()
	{
		return $this->str_reference;
	}

	public function setStrOrderNumber($str_order_number)
	{
		$this->str_order_number = $str_order_number;
	}

	protected function getStrOrderNumber()
	{
		return $this->str_order_number;
	}

	public function setIntIdCustomer($int_id_customer)
	{
		$this->int_id_customer = $int_id_customer;
	}

	protected function getIntIdCustomer()
	{
		return $this->int_id_customer;
	}

	public function setStrSecureKey($str_secure_key)
	{
		$this->str_secure_key = $str_secure_key;
	}

	protected function getStrSecureKey()
	{
		return $this->str_secure_key;
	}

	public function setStrShopKey($str_shop_key)
	{
		$this->str_shop_key = $str_shop_key;
	}

	protected function getStrShopKey()
	{
		return $this->str_shop_key;
	}

	public function setStrDate($str_date)
	{
		$this->str_date = $str_date;
	}

	protected function getStrDate()
	{
		return $this->str_date;
	}

	public function setStrRecipientName($str_recipient_name)
	{
		$this->str_recipient_name = $str_recipient_name;
	}

	protected function getStrRecipientName()
	{
		return $this->str_recipient_name;
	}

	public function setStrRecipientAddress1($str_recipient_address_1)
	{
		$this->str_recipient_address_1 = $str_recipient_address_1;
	}

	protected function getStrRecipientAddress1()
	{
		return $this->str_recipient_address_1;
	}

	public function setStrRecipientAddress2($str_recipient_address_2)
	{
		$this->str_recipient_address_2 = $str_recipient_address_2;
	}

	protected function getStrRecipientAddress2()
	{
		return $this->str_recipient_address_2;
	}

	public function setStrRecipientZipCode($str_recipient_zip_code)
	{
		$this->str_recipient_zip_code = $str_recipient_zip_code;
	}

	protected function getStrRecipientZipCode()
	{
		return $this->str_recipient_zip_code;
	}

	public function setStrRecipientCity($str_recipient_city)
	{
		$this->str_recipient_city = $str_recipient_city;
	}

	protected function getStrRecipientCity()
	{
		return $this->str_recipient_city;
	}

	public function setStrRecipientPhone($str_recipient_phone)
	{
		$this->str_recipient_phone = $str_recipient_phone;
	}

	protected function getStrRecipientPhone()
	{
		return $this->str_recipient_phone;
	}

	public function setStrRecipientMail($str_recipient_mail)
	{
		$this->str_recipient_mail = $str_recipient_mail;
	}

	protected function getStrRecipientMail()
	{
		return $this->str_recipient_mail;
	}

	/**
	 * Given array to rest post
	 * 
	 * @param void
	 * @return array:
	 */
	protected function getXmlData()
	{
		$xml = '<?xml version = "1.0" encoding = "UTF-8" standalone = "no"?>
		<globkurier>
			<login>'.GlobKurierTools::gkDecryptString($this->getLogin()).'</login>
			<password>'.GlobKurierTools::gkDecryptString($this->getPassword()).'</password>
			<apikey>'.GlobKurierTools::gkDecryptString($this->getApiKey()).'</apikey>
			<order_method>'.self::PS_METHOD.'</order_method>
			<order_source>'.self::PS_SOURCE.'</order_source>
			<orders>
				<order>
					<id_cart>'.$this->getIntIdCart().'</id_cart>
					<id_customer>'.$this->getIntIdCustomer().'</id_customer>
					<shop_key>'.$this->getStrShopKey().'</shop_key>
					<secure_key>'.$this->getStrSecureKey().'</secure_key>
					<reference>'.$this->getStrReference().'</reference>
					<order_number>'.$this->getStrOrderNumber().'</order_number>
					<sent_date>'.$this->getStrDate().'</sent_date>
					<receiver>
						<name>'.$this->getStrRecipientName().'</name>
						<street>'.$this->getStrRecipientAddress1().'</street>
						<house_number>'.$this->getStrRecipientAddress2().'</house_number>
						<postal_code>'.$this->getStrRecipientZipCode().'</postal_code>
						<city>'.$this->getStrRecipientCity().'</city>
						<country>0</country>
						<contact_person>'.$this->getStrRecipientName().'</contact_person>
						<phone>'.$this->getStrRecipientPhone().'</phone>
						<email>'.$this->getStrRecipientMail().'</email>
					</receiver>
				</order>
			</orders>
			<token>'.$this->getStrToken().'</token>
		</globkurier>';
		return $xml;
	}

	/**
	 * Send data to webservice over POST method
	 * 
	 * @param void
	 * @return http json response
	 */
	public function sendData()
	{
		$headers = array('Authorization: Basic '.base64_encode(GlobKurierConfig::PS_GK_WS_LOGIN.':'.GlobKurierConfig::PS_GK_WS_PASSWD));
		if (GlobKurierTools::isCurl())
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, GlobKurierConfig::PS_GK_URL_ORDER);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, 'DATA='.$this->getXmlData());
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$output = curl_exec($ch);
			curl_close($ch);
			return $output;
		}
		else
			die('ERROR: You need to enable curl php extension to use GlobKurier module.');
	}
}