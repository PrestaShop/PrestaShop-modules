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

class GlobKurierTools {

	/**
	 * Encrypt string (base64/rot13)
	 *
	 * @param   string $str_string
	 * @return  string Encrypt
	 */
	public static function gkEncryptString($str_string)
	{
		return base64_encode(str_rot13($str_string));
	}

	/**
	 * Decrypt string (rot13/base64)
	 *
	 * @param   string $str_string
	 * @return  string Decrypt
	 */
	public static function gkDecryptString($str_string)
	{
		return str_rot13(base64_decode($str_string));
	}

	/**
	 * Script to test if the CURL extension is installed on this server
	 * 
	 * @param void
	 * @return bool
	 */
	public static function isCurl()
	{
		if (function_exists('curl_version'))
			return true;
		else
			return false;
	}

	/**
	 * Generate tmp order number
	 *
	 * @param void
	 * @return string 23 digits
	 */
	public static function getOrderNumber()
	{
		$number = date('ymd').str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT).time();
		$arr_weights = array(10, 8, 4, 5);
		$weight_limit = count($arr_weights);
		$j = 0;
		$sum = 0;
		$ck = 0;
		$strl_number = Tools::strlen($number);
		for ($i = 0; $i < $strl_number; $i++)
		{
			if ($j >= $weight_limit)
				$j = 0;
			$sum += $number[$i] * $arr_weights[$j];
			$j++;
			$ck = $sum % 10;
		}
		return (string)$number.$ck;
	}

	/**
	 * Get correct pickup date
	 *
	 * @param void
	 * @return date YYYY-MM-DD
	 */
	public static function getCorrectDate()
	{
		$cur_date = date('Y-m-d');
		$cur_hour = date('H');
		if ($cur_hour > 11)
			$cur_date = date('Y-m-d', strtotime('+1 day'));

		$explode = explode('-', $cur_date);
		$day = mktime(0, 0, 0, $explode[1], $explode[2], $explode[0]);
		$weekend = date('N', $day);
		switch ($weekend)
		{
			case 6 :
				$day = strtotime('+2 day', $day);
				break;
			case 7 :
				$day = strtotime('+1 day', $day);
				break;
		}
		$date = date('Y-m-d', $day);
		return $date;
	}
}