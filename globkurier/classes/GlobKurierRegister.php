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

class GlobKurierRegister {

	private $str_email;
	private $str_password;
	private $int_customer_type;
	private $str_company;
	private $str_nip;
	private $str_name;
	private $str_surname;
	private $str_street;
	private $str_house;
	private $str_local;
	private $str_city;
	private $str_zip;
	private $str_phone;

	public function __construct()
	{

	}

	public function setStrEmail($str_email)
	{
		$this->str_email = $str_email;
	}

	protected function getStrEmail()
	{
		return $this->str_email;
	}

	public function setStrPassword($str_password)
	{
		$this->str_password = $str_password;
	}

	protected function getStrPassword()
	{
		return $this->str_password;
	}

	public function setIntCustomerType($int_customer_type)
	{
		$this->int_customer_type = $int_customer_type;
	}

	protected function getIntCustomerType()
	{
		return $this->int_customer_type;
	}

	public function setStrCompany($str_company)
	{
		$this->str_company = $str_company;
	}

	protected function getStrCompany()
	{
		return $this->str_company;
	}

	public function setStrNip($str_nip)
	{
		$this->str_nip = $str_nip;
	}

	protected function getStrNip()
	{
		return $this->str_nip;
	}

	public function setStrName($str_name)
	{
		$this->str_name = $str_name;
	}

	protected function getStrName()
	{
		return $this->str_name;
	}

	public function setStrSurname($str_surname)
	{
		$this->str_surname = $str_surname;
	}

	protected function getStrSurname()
	{
		return $this->str_surname;
	}

	public function setStrStreet($str_street)
	{
		$this->str_street = $str_street;
	}

	protected function getStrStreet()
	{
		return $this->str_street;
	}

	public function setStrHouse($str_house)
	{
		$this->str_house = $str_house;
	}

	protected function getStrHouse()
	{
		return $this->str_house;
	}

	public function setStrLocal($str_local)
	{
		$this->str_local = $str_local;
	}

	protected function getStrLocal()
	{
		return $this->str_local;
	}

	public function setStrCity($str_city)
	{
		$this->str_city = $str_city;
	}

	protected function getStrCity()
	{
		return $this->str_city;
	}

	public function setStrZip($str_zip)
	{
		$this->str_zip = $str_zip;
	}

	protected function getStrZip()
	{
		return $this->str_zip;
	}

	public function setStrPhone($str_phone)
	{
		$this->str_phone = $str_phone;
	}

	protected function getStrPhone()
	{
		return $this->str_phone;
	}

	/**
	 * Get data to post requert
	 * 
	 * @param void
	 * @return array
	 */
	protected function getAccountData()
	{
		$this->arr_post = array(
					'EMAIL' => $this->getStrEmail(),
					'PASSWORD' => $this->getStrPassword(),
					'COMPANY' => $this->getStrCompany(),
					'TYPE' => $this->getIntCustomerType(),
					'NIP' => $this->getStrNip(),
					'NAME' => $this->getStrName(),
					'SURNAME' => $this->getStrSurname(),
					'STREET' => $this->getStrStreet(),
					'HOUSE' => $this->getStrHouse(),
					'LOCAL' => $this->getStrLocal(),
					'CITY' => $this->getStrCity(),
					'ZIP' => $this->getStrZip(),
					'PHONE' => $this->getStrPhone(),
					'TOKEN' => sha1($this->getStrEmail().$this->getStrName().$this->getStrSurname().$this->getStrCity()),
					'SOURCE' => 1);
		return $this->arr_post;
	}

	/**
	 * Send data to webservice over POST method
	 * 
	 * @param void
	 * @return http response
	 * @throws GlobKurierExceptions
	 */
	public function sendData()
	{
		$fields_string = null;
		$response = null;

		foreach ($this->getAccountData() as $key => $value)
			$fields_string .= $key.'='.$value.'&';

		if (!empty($fields_string))
		{
			$arr_params = array(
				'http' => array(
							'method' => 'POST',
							'content' => $fields_string,
							'header' => "Content-type: application/x-www-form-urlencoded\r\n".'Content-Length: '.Tools::strlen($fields_string)."\r\n")
				);
			$sctxt = stream_context_create($arr_params);
			$url = GlobKurierConfig::PS_GK_URL_PROTOCOL;
			$url .= GlobKurierConfig::PS_GK_WS_LOGIN.':'.GlobKurierConfig::PS_GK_WS_PASSWD;
			$url .= '@'.GlobKurierConfig::PS_GK_URL_REGISTER;
			$fp = fopen($url, 'rb', false, $sctxt);
			if (!$fp)
				throw new GlobKurierException("Problem with $url, $php_errormsg");

			$response = stream_get_contents($fp);
			if (!$response)
				throw new GlobKurierException("Problem reading data from $url, $php_errormsg");
		}
		return $response;
	}
}