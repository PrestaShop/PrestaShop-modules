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

class GlobKurierAddons {

	private $int_client_id;

	/**
	 * Set default data
	 * 
	 * @param void
	 * @return void
	 */
	public function __construct()
	{
		$this->int_client_id = null;
	}

	public function setIntClientId($int_client_id)
	{
		$this->int_client_id = $int_client_id;
	}

	public function getIntClientId()
	{
		return $this->int_client_id;
	}

	/**
	 * Given array to rest post
	 * 
	 * @param void
	 * @return array:
	 */
	public function getData()
	{
		$this->arr_post = array('client_id' => $this->getIntClientId(), );
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
		foreach ($this->getData() as $key => $value)
			$fields_string .= $key.'='.$value.'&';
		if (GlobKurierTools::isCurl())
		{
			$c = curl_init();
			curl_setopt($c, CURLOPT_URL, GlobKurierConfig::PS_GK_URL_ADDONS.'?'.$fields_string);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			$page = curl_exec($c);
			curl_close($c);
			return $page;
		}
		else
			die('ERROR: You need to enable curl php extension to use GlobKurier module.');
	}
}