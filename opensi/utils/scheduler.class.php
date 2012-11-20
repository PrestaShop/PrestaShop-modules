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

class Scheduler {

	private static $timeOnInit;	//persistant time

	private static $cachetimeLbl = 'OSI_CACHETIME_';
	private static $lastrequestLbl = 'OSI_LASTREQUEST_';	

	private static $wsGetList = '';
	private static $wsPostList = '';

	private static $wsInfosList = '';


	/*
	 * Constructor
	 * Init scheduler for requests
	 */
	public static function initTimer($wsGetArray, $wsPostArray, $cachetimeLbl, $lastrequestLbl) {

		self::$timeOnInit = time(); 
		self::$wsGetList = $wsGetArray;
		self::$wsPostList = $wsPostArray;
		self::$cachetimeLbl = $cachetimeLbl;
		self::$lastrequestLbl = $lastrequestLbl;

		$completeWslist = array_merge(self::$wsGetList, self::$wsPostList);
		foreach($completeWslist as $ws) {
			// ex 'OSI_CACHETIME_WSO-G002'
			$WSOInfosList[] = self::$cachetimeLbl.$ws;
			// ex 'OSI_LASTREQUEST_WSO-G002'
			$WSOInfosList[] = self::$lastrequestLbl.$ws;
		}
		self::$wsInfosList = configuration::getMultiple($WSOInfosList);

	}


	/*
	 * Return infos about cachetime delay and last request for all WS
	 * Return an array
	 */
	public static function getWsInfosList(){
		return self::$wsInfosList;
	}

	/*
	 * Return the name of all WS (type GET)
	 * Return an array
	 */
	public static function getWsNameGetList(){
		return self::$wsGetList;
	}

	/*
	 * Return the name of all WS (type POST)
	 * Return an array
	 */
	public static function getWsNamePostList(){
		return self::$wsPostList;
	}

	/*
	 * Return time on init
	 */
	public static function getTimeOnInit(){
		return self::$timeOnInit;
	}

	/*
	 * Check if allowed to send request for a WS
	 * @param $wsName (like "WSO-G002")
	 */
	public static function isAllowedToRequest($wsName){
		$OsiCacheTime = self::$cachetimeLbl.$wsName;
		$OsiLastReq = self::$lastrequestLbl.$wsName;

		if(!array_key_exists($OsiCacheTime, self::$wsInfosList) || 
			!array_key_exists($OsiLastReq, self::$wsInfosList)) {
			//error : not in array
			return false;
		} else if(self::$wsInfosList[$OsiCacheTime] == -1) {
			//webservice not activate
			return false;
		} else {
			//check for delay last request
			return (self::$timeOnInit - self::$wsInfosList[$OsiLastReq] > (self::$wsInfosList[$OsiCacheTime]*60));
		}		
	}

	public static function setLastRequest($wsName) {
		$OsiLastReq = self::$lastrequestLbl.$wsName;

		if(!array_key_exists($OsiCacheTime, self::$wsInfosList) || 
			!array_key_exists($OsiLastReq, self::$wsInfosList)) {
			Configuration::updateValue($OsiLastReq.$wsName, self::$now);
		}
	}		

}