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

class GlobKurierSaveOrder extends ObjectModel {

	const PS_METHOD = 'SAVE';
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
	private $str_pickup_date;
	private $str_pickup_time_from;
	private $str_pickup_time_to;
	private $str_base_service;

	private $arr_addons = array();
	private $flo_cod_amount;
	private $str_cod_account;
	private $flo_insurance_amount;
	private $flo_declared_value;

	private $str_payment_type;
	private $str_sender_name;
	private $str_sender_address_1;
	private $str_sender_address_2;
	private $str_sender_zip_code;
	private $str_sender_city;
	private $str_sender_country;
	private $str_sender_phone;
	private $str_sender_mail;
	private $str_recipient_name;
	private $str_recipient_address_1;
	private $str_recipient_address_2;
	private $str_recipient_zip_code;
	private $str_recipient_city;
	private $str_recipient_country;
	private $str_recipient_phone;
	private $str_recipient_mail;
	private $str_content;
	private $flo_length;
	private $flo_width;
	private $flo_height;
	private $flo_weight;
	private $int_parcels;
	private $str_comments;
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

	public function setStrLogin($str_login)
	{
		$this->str_login = $str_login;
	}

	protected function getStrLogin()
	{
		return $this->str_login;
	}

	public function setStrPasswd($str_passwd)
	{
		$this->str_passwd = $str_passwd;
	}

	protected function getStrPasswd()
	{
		return $this->str_passwd;
	}

	public function setStrApiKey($str_api_key)
	{
		$this->str_api_key = $str_api_key;
	}

	protected function getStrApiKey()
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

	public function setStrSource($str_source)
	{
		$this->str_source = $str_source;
	}

	protected function getStrSource()
	{
		return $this->str_source;
	}

	public function setIntIdCart($int_id_cart)
	{
		$this->int_id_cart = $int_id_cart;
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

	public function setStrPickupDate($str_pickup_date)
	{
		$this->str_pickup_date = $str_pickup_date;
	}

	protected function getStrPickupDate()
	{
		return $this->str_pickup_date;
	}

	public function setStrPickupTimeFrom($str_pickup_time_from)
	{
		$this->str_pickup_time_from = $str_pickup_time_from;
	}

	protected function getStrPickupTimeFrom()
	{
		return $this->str_pickup_time_from;
	}

	public function setStrPickupTimeTo($str_pickup_time_to)
	{
		$this->str_pickup_time_to = $str_pickup_time_to;
	}

	protected function getStrPickupTimeTo()
	{
		return $this->str_pickup_time_to;
	}

	public function setStrBaseService($str_base_service)
	{
		$this->str_base_service = $str_base_service;
	}

	protected function getStrBaseService()
	{
		return $this->str_base_service;
	}

	public function setArrAddons($arr_addons)
	{
		$this->arr_addons  = $arr_addons;
	}

	protected function getArrAddons()
	{
		return $this->arr_addons;
	}

	public function setFloCodAmount($flo_cod_amount)
	{
		$this->flo_cod_amount = $flo_cod_amount;
	}

	protected function getFloCodAmount()
	{
		return $this->flo_cod_amount;
	}

	public function setStrCodAccount($str_cod_account)
	{
		$this->str_cod_account = $str_cod_account;
	}

	protected function getStrCodAccount()
	{
		return $this->str_cod_account;
	}

	public function setFloInsuranceAmount($flo_insurance_amount)
	{
		$this->flo_insurance_amount = $flo_insurance_amount;
	}

	protected function getFloInsuranceAmount()
	{
		return $this->flo_insurance_amount;
	}

	public function setFloDeclaredValue($flo_declared_value)
	{
		$this->flo_declared_value = $flo_declared_value;
	}

	protected function getFloDeclaredValue()
	{
		return $this->flo_declared_value;
	}

	public function setPaymentType($str_payment_type)
	{
		$this->str_payment_type = $str_payment_type;
	}

	protected function getStrPaymentType()
	{
		return $this->str_payment_type;
	}

	public function setStrSenderName($str_sender_name)
	{
		$this->str_sender_name = $str_sender_name;
	}

	protected function getStrSenderName()
	{
		return $this->str_sender_name;
	}

	public function setStrSenderAddress1($str_sender_address_1)
	{
		$this->str_sender_address_1 = $str_sender_address_1;
	}

	protected function getStrSenderAddress1()
	{
		return $this->str_sender_address_1;
	}

	public function setStrSenderAddress2($str_sender_address_2)
	{
		$this->str_sender_address_2 = $str_sender_address_2;
	}

	protected function getStrSenderAddress2()
	{
		return $this->str_sender_address_2;
	}

	public function setStrSenderZipCode($str_sender_zip_code)
	{
		$this->str_sender_zip_code = $str_sender_zip_code;
	}

	protected function getStrSenderZipCode()
	{
		return $this->str_sender_zip_code;
	}

	public function setStrSenderCity($str_sender_city)
	{
		$this->str_sender_city = $str_sender_city;
	}

	protected function getStrSenderCity()
	{
		return $this->str_sender_city;
	}

	public function setStrSenderCountry($str_sender_country)
	{
		$this->str_sender_country = $str_sender_country;
	}

	protected function getStrSenderCountry()
	{
		return $this->str_sender_country;
	}

	public function setStrSenderPhone($str_sender_phone)
	{
		$this->str_sender_phone = $str_sender_phone;
	}

	protected function getStrSenderPhone()
	{
		return $this->str_sender_phone;
	}

	public function setStrSenderMail($str_sender_mail)
	{
		$this->str_sender_mail = $str_sender_mail;
	}

	protected function getStrSenderMail()
	{
		return $this->str_sender_mail;
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

	public function setStrRecipientCountry($str_recipient_country)
	{
		$this->str_recipient_country = $str_recipient_country;
	}

	protected function getStrRecipientCountry()
	{
		return $this->str_recipient_country;
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

	public function setStrContent($str_content)
	{
		$this->str_content = $str_content;
	}

	protected function getStrContent()
	{
		return $this->str_content;
	}

	public function setFloLength($flo_length)
	{
		$this->flo_length = $flo_length;
	}

	protected function getFloLength()
	{
		return $this->flo_length;
	}

	public function setFloWidth($flo_width)
	{
		$this->flo_width = $flo_width;
	}

	protected function getFloWidth()
	{
		return $this->flo_width;
	}

	public function setFloHeight($flo_height)
	{
		$this->flo_height = $flo_height;
	}

	protected function getFloHeight()
	{
		return $this->flo_height;
	}

	public function setFloWeight($flo_weight)
	{
		$this->flo_weight = $flo_weight;
	}

	protected function getFloWeight()
	{
		return $this->flo_weight;
	}

	public function setIntParcels($int_parcels)
	{
		$this->int_parcels = $int_parcels;
	}

	protected function getIntParcels()
	{
		return $this->int_parcels;
	}

	public function setStrComments($str_comments)
	{
		$this->str_comments = $str_comments;
	}

	protected function getStrComments()
	{
		return $this->str_comments;
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
				<login>'.GlobKurierTools::gkDecryptString($this->getStrLogin()).'</login>
				<password>'.GlobKurierTools::gkDecryptString($this->getStrPasswd()).'</password>
				<apikey>'.GlobKurierTools::gkDecryptString($this->getStrApiKey()).'</apikey>
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
						<sent_date>'.$this->getStrPickupDate().'</sent_date>
						<time_from>'.$this->getStrPickupTimeFrom().'</time_from>
						<time_to>'.$this->getStrPickupTimeTo().'</time_to>
						<base_service>'.$this->getStrBaseService().'</base_service>
						<additional_services>';
		if(is_array($this->getArrAddons()))
		{
			foreach ($this->getArrAddons() as $a)
				$xml .= '<additional_service>'.$a.'</additional_service>';
		}
		$xml .= '</additional_services>
						<cod_amount>'.$this->getFloCodAmount().'</cod_amount>
						<cod_account>'.$this->getStrCodAccount().'</cod_account>
						<insurance_amount>'.$this->getFloInsuranceAmount().'</insurance_amount>
						<declared_value>'.$this->getFloDeclaredValue().'</declared_value>
						<payment_type>'.$this->getStrPaymentType().'</payment_type>
						<content>'.$this->getStrContent().'</content>
						<length>'.$this->getFloLength().'</length>
						<width>'.$this->getFloWidth().'</width>
						<height>'.$this->getFloHeight().'</height>
						<weight>'.$this->getFloWeight().'</weight>
						<number_of_parcels>'.$this->getIntParcels().'</number_of_parcels>
						<comments>'.$this->getStrComments().'</comments>
						<sender>
							<name>'.$this->getStrSenderName().'</name>
							<street>'.$this->getStrSenderAddress1().'</street>
							<house_number>'.$this->getStrSenderAddress2().'</house_number>
							<apartment_number></apartment_number>
							<postal_code>'.$this->getStrSenderZipCode().'</postal_code>
							<city>'.$this->getStrSenderCity().'</city>
							<country>'.$this->getStrSenderCountry().'</country>
							<contact_person>'.$this->getStrSenderName().'</contact_person>
							<phone>'.$this->getStrSenderPhone().'</phone>
							<email>'.$this->getStrSenderMail().'</email>
						</sender>
						<receiver>
							<name>'.$this->getStrRecipientName().'</name>
							<street>'.$this->getStrRecipientAddress1().'</street>
							<house_number>'.$this->getStrRecipientAddress2().'</house_number>
							<apartment_number></apartment_number>
							<postal_code>'.$this->getStrRecipientZipCode().'</postal_code>
							<city>'.$this->getStrRecipientCity().'</city>
							<country>'.$this->getStrRecipientCountry().'</country>
							<contact_person>'.$this->getStrRecipientName().'</contact_person>
							<phone>'.$this->getStrRecipientPhone().'</phone>
							<email>'.$this->getStrRecipientMail().'</email>
						</receiver>
					</order>
				</orders>
				<token>'.$this->getStrToken().'</token>
			</globkurier>';
		return (string)$xml;
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