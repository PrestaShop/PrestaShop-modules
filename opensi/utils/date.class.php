<?php
/*
 * OpenSi Connect for Prestashop
 *
 * NOTICE OF LICENSE
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation; either version 2 of the License, or (at your
 * option) any later version.
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
 * for more details.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author Speedinfo SARL
 * @copyright 2003-2012 Speedinfo SARL
 * @contact contact@speedinfo.fr
 * @url http://www.speedinfo.fr
 *
 */

class Date {

	/*
	 * Convert a timeStamp to a dateTime (used to request openSi)
	 * @param String $time  is the timeStamp to convert
	 */
	public static function timestamp2DateTime($time) {
		// ex => 2010-08-12 22:57:41
		return date('d-m-Y H:i:s', $time); 
	}

	/*
	* Convert a timeStamp to a date
	* @param String $time  is the timeStamp to convert
	*/
	public static function timestamp2Date($time) {
		// ex => 2010-08-12
		return date('d-m-Y', $time); 
	}

	/*
	 * Convert a timeStamp to a dateTime (used in prestashop database)
	 * @param String $time  is the timeStamp to convert
	 */
	public static function timestamp2DateTimeBdd($time) {
		//ex => 2010-08-12 22:57:41
		return date('Y-m-d H:i:s', $time);
	}

	/*
	 * Convert date (french format) to date format used into database
	 * dd-mm-yyyy => yyyy-mm-dd
	 * @param unknown_type $dateFr
	 */
	public static function dateFr2DateBdd($dateFr) {
		$array = preg_split("/-/", $dateFr);
		return($array[2]."-".$array[1]."-".$array[0]);	
	}

	/*
	 * Convert datetime (english format) to datetime (french format)
	 * yyyy-mm-dd  hh:mm:ss => dd-mm-yyyy hh:mm:ss
	 * @param unknown_type $dateTimeEn
	 */
	public static function dateTimeBdd2DateTimeFr($dateTimeEn) {
		$date = preg_split("/ /", $dateTimeEn);
		$array = preg_split("/-/", $date[0]);
		return($array[2]."-".$array[1]."-".$array[0]." ".$date[1]);	
	}

	/*
	 * Convert datetime (french format) to datetime (english format)
	 * dd-mm-yyyy hh:mm:ss => yyyy-mm-dd  hh:mm:ss
	 * @param unknown_type $dateTimeFr
	 */
	public static function dateTimeFr2DateTimeBdd($dateTimeFr) {
		//the same as reverse
		return self::dateTimeBdd2DateTimeFr($dateTimeFr);
	}

}