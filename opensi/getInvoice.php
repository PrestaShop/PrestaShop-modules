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

/* Loading classes */
if (file_exists(dirname(__FILE__).'/../../config/config.inc.php'))
	include(dirname(__FILE__).'/../../config/config.inc.php');

if (file_exists(dirname(__FILE__).'/dao.class.php'))
	include_once("dao.class.php");


/* Variables */
$dao = new Dao();
$key = $_GET["key"];


/* Read header */
function readHeader($c, $str) {
	header($str."\n");
	return strlen($str);
}


/* Get OpenSi invoice */
$return = $dao->getInvoice($key);
if(isset($return[0]['number_invoice'])) {
	/* Call webservice - curl */
	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, Configuration::get('OSI_WS_URL').'?service_id='.Configuration::get('OSI_SERVICE_CODE').'&action=get_facture&facture_ref='.$return[0]['number_invoice'].'.pdf');
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($c, CURLOPT_HEADERFUNCTION, "readHeader");
	curl_setopt($c, CURLOPT_USERPWD, Configuration::get('OSI_WS_LOGIN').':'.Configuration::get('OSI_WS_PASSWD'));

	$output = curl_exec($c);
	if($output === false) {
		trigger_error("Erreur curl : ".curl_error($c), E_USER_WARNING);
	} else {
		header('Content-type: application/pdf');
		header('Content-Disposition: attachement; filename=facture_'.$return[0]['number_invoice'].'.pdf');
		echo $output;
	}
	curl_close($c);
} else {
	/* 404 Error */
	echo "<h2>404 Unauthorized Error</h2>You do not have permission to view this directory or page on the Web server.";
}