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

class Log {

	private static $typesLog = array("debug"=>0, "info"=>1, "warn"=>2, "error"=>3);
	private static $defaultType = "info";
	private static $defaultDirectory = "logs/";

	/*
	 * Write log on screen
	 * @param String $str  contains log
	 * @param String $type contains type of log
	 */
	public static function write($str, $type) {
		if(self::canPrint($type)){
			echo '<br />'.$type.' > '.$str;
		}
	}
	public static function setDefaultType($type) {
		self::$defaultType = $type;
	}
	public static function setDirectory($directory) {
		self::$defaultDirectory = $directory;
	}


	/*
	 * Check that rights are quite high to display logs
	 * @param unknown_type $type
	 */
	public static function canPrint($type) {
		if(array_key_exists($type, self::$typesLog) && self::$typesLog[$type] >=  self::$typesLog[self::$defaultType]) {
			return true;
		} else {
			return false;
		}
	}


	/*
	 * Return label for a code response.
	 * @param $codeResponse
	 */
	public static function responseCodeLabel($codeResponse) {
		if($codeResponse == 404){
			return "Introuvable.";
		} else if($codeResponse == 400) {
			return "Mauvaise requête.";
		} else if($codeResponse == 401 || $codeResponse == 403) {
			return "Erreur d'authentification HTTP.";
		}  else if($codeResponse == 405) {
			return "Méthode non autorisée.";
		} else if($codeResponse == 408) {
			return "Temps d'attente d'une réponse du serveur écoulé.";
		} else if($codeResponse == 413) {
			return "Traitement abandonné dû à une requête trop importante.";
		}  else if($codeResponse == 414) {
			return "URI trop longue.";
		} else if($codeResponse == 424) {
			return "Une méthode de la transaction a échoué.";
		} else if($codeResponse == 500) {
			return "Erreur interne de servlet.";
		} else if($codeResponse == 501) {
			return "Fonctionnalité réclamée non supportée par le serveur.";
		} else if($codeResponse == 502) {
			return "Mauvaise réponse envoyée à un serveur intermédiaire par un autre serveur.";
		} else if($codeResponse == 503) {
			return "Service temporairement indisponible ou en maintenance.";
		} else if($codeResponse == 504) {
			return "Temps d'attente d'une réponse d'un serveur à un serveur intermediaire écoulé.";
		} else if($codeResponse == 505) {
			return "Version HTTP non gérée par le serveur.";
		} else {
			return "Traduction d'erreur non définie.";
		}
	}
}