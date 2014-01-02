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

class GlobKurierApiKey {

	private $str_login;
	private $arr_post = array();

	public function setLogin($str_login)
	{
		$this->str_login = $str_login;
	}

	public function getLogin()
	{
		return $this->str_login;
	}

	/**
	 * Given array to rest post
	 * 
	 * @param void
	 * @return array:
	 * [login	 => email]
	 */
	public function getData()
	{
		$this->arr_post = array('LOGIN' => $this->getLogin());
		return $this->arr_post;
	}

	/**
	 * Send data to webservice over POST method
	 * 
	 * @param void
	 * @return http json response
	 * @throws GlobKurierExceptions
	 */
	public function sendData()
	{
		$fields_string = null;
		$response = null;
		foreach ($this->getData() as $key => $value)
			$fields_string .= $key.'='.$value.'&';

		if (!empty($fields_string))
		{
			$arr_params = array(
				'http' => array(
					'method'  => 'POST',
					'content'  => $fields_string,
					'header' => "Content-type: application/x-www-form-urlencoded\r\n".'Content-Length: '.Tools::strlen($fields_string)."\r\n")
				);
			$sctxt = stream_context_create($arr_params);
			$url = GlobKurierConfig::PS_GK_URL_PROTOCOL;
			$url .= GlobKurierConfig::PS_GK_WS_LOGIN.':'.GlobKurierConfig::PS_GK_WS_PASSWD;
			$url .= '@'.GlobKurierConfig::PS_GK_URL_APIKEY;
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