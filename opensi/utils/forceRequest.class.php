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

class ForceRequest {

	private static $isForced = false;	//if true, only send this webservice request

	private static $wsName;	//name of webservice request (ex: WSO-G002)
	private static $wsType;	//GET or POST
	private static $wsDateFrom;	//start date for request
	private static $wsTimeFrom;	//start time fors request
	private static $wsDateTo;	//end date for request
	private static $wsTimeTo;	//end time for request

	/*
	 * Constructor
	 * Init force request
	 */
	public static function initForceRequest($wsNameGetList, $wsNamePostList) {

		/*
		 * GLOBAL PARAMETERS
		 * $_GET["action"]
		 * $_GET["date_debut"]
		 * $_GET["date_fin"]
		 */
		$wsReqList = array_merge($wsNameGetList, $wsNamePostList);

		/* Check for actions */
		if(isset($_GET["action"]) && in_array($_GET["action"], $wsReqList)) {

			self::$isForced = true;
			self::$wsName = $_GET["action"];

			if(in_array($_GET["action"], $wsNameGetList)) {
				self::$wsType = "GET";
			} else {
				self::$wsType = "POST";
			}

			if(isset($_GET["date_debut"]) && isset($_GET["time_debut"])){
				self::$wsDateFrom =  $_GET["date_debut"];
				self::$wsTimeFrom =  $_GET["time_debut"];
			} else {
				Log::write("Veuillez ajouter les paramètres date_debut et time_debut [ex => URL?action=".$_GET["action"]."&date_debut=jj-mm-aaaa&time_debut=hh:mm:ss]", "error");
				die();
			}

			if(isset($_GET["date_fin"]) && isset($_GET["time_fin"])){
				self::$wsDateTo =  $_GET["date_fin"];
				self::$wsTimeTo =  $_GET["time_fin"];
			} else {
				Log::write("Veuillez ajouter les paramètres date_fin et time_fin [ex => URL?action=".$_GET["action"]."&date_fin=jj-mm-aaaa&time_fin=hh:mm:ss]", "error");
				die();
			}

		} else if(Configuration::get('OSI_ACTIVE_'.$_GET["action"]) == 0 && Configuration::get('OSI_ACTIVE_'.$_GET["action"]) !='') {	
			Log::write("Ce webservice est désactivé", "error");
			die();

		} else if(isset($_GET["action"])){
			Log::write("Cette action est invalide", "error");
			die();
		}
	}


	/*
	 * Send only on webservice request ?
	 * Return Boolean
	 */
	public static function isForcedRequest(){
		return self::$isForced;
	}


	/*
	 * The name of webservice request to send
	 * Return a string 
	 */
	public static function getWsName(){
		return self::$wsName;
	}


	/*
	 * The type of request ("GET" or "POST")
	 * Return a string 
	 */
	public static function getWsType(){
		return self::$wsType;
	}


	/*
	 * Return the start date parameter for the request
	 */
	public static function getDateFrom(){
		return self::$wsDateFrom;
	}


	/*
	 * Return the end date parameter for the request
	 */
	public static function getDateTo(){
		return self::$wsDateTo;
	}

	/*
	 * Return the start time parameter for the request
	 */
	public static function getTimeFrom(){
		return self::$wsTimeFrom;
	}


	/*
	 * Return the end time parameter for the request
	 */
	public static function getTimeTo(){
		return self::$wsTimeTo;
	}

}