<?php

/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @version  Release: $Revision: 9702 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

// Method for oAuth process
function authenticateClient($clientId, $clientSecret) {
	// Receive access token via oAuth
	if(extension_loaded('curl')) {
		$header[] = 'Authorization: OAuth Content-Type: application/x-www-form-urlencoded';
		$ibCurl = curl_init();
		curl_setopt($ibCurl, CURLOPT_HEADER, false);
		curl_setopt($ibCurl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ibCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ibCurl, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ibCurl, CURLOPT_URL, PS_ITEMBASE_SERVER_OAUTH);
		curl_setopt($ibCurl, CURLOPT_POST, true);
		curl_setopt($ibCurl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ibCurl, CURLOPT_POSTFIELDS, array(
			'client_id' => $clientId,
			'client_secret' => $clientSecret,
			'response_type' => 'token',
			'grant_type' => 'client_credentials'
		));
		$jsonResponse = curl_exec($ibCurl);
		if($jsonResponse === FALSE) itembaseErrorHandler(0, curl_error($ibCurl), __FILE__, __LINE__ - 1);
		curl_close($ibCurl);
	} else {
		$opts = array('http' => array('ignore_errors' => true));
		$context = stream_context_create($opts);
		$jsonResponse = file_get_contents(PS_ITEMBASE_SERVER_OAUTH.'?client_id='.$clientId.'&client_secret='.$clientSecret.'&response_type=token&grant_type=client_credentials', false, $context);
		if($jsonResponse === FALSE) itembaseErrorHandler(0, 'file_get_contents', __FILE__, __LINE__ - 1);
	}
	return $jsonResponse;
}

// Method for preparing UTF-8 data for Itembase
$encodings;
function utf8EncodeRecursive(&$data) {
	if(is_array($data) || is_object($data)) {
		settype($data, 'array');
		foreach($data as &$_val)
			utf8EncodeRecursive($_val);
	} else {
		$data = strip_tags(html_entity_decode($data));
		if(extension_loaded('mbstring')) {
			global $encodings;
			if(!$encodings) {
				$encodings = array();
				foreach(explode(',', 'UTF-8,ISO-8859-1,ISO-8859-2,ISO-8859-3,ISO-8859-4,ISO-8859-5,ISO-8859-6,ISO-8859-7,ISO-8859-8,ISO-8859-9,ISO-8859-10,ISO-8859-13,ISO-8859-14,ISO-8859-15,ISO-8859-16,Windows-1252,Windows-1250,Windows-1251,Windows-1254') as $encoding) {
					if(in_array($encoding, mb_list_encodings())) {
						$encodings[] = $encoding;
					}
				}
				mb_detect_order(array_merge($encodings, mb_list_encodings()));
			}
			if(($encoding = mb_detect_encoding($data, null, true)) != 'UTF-8') {
				mb_convert_variables('UTF-8', $encoding, $data);
			}
		} elseif(!preg_match('%^(?:
			[\x09\x0A\x0D\x20-\x7E] # ASCII
			| [\xC2-\xDF][\x80-\xBF] # non-overlong 2-byte
			| \xE0[\xA0-\xBF][\x80-\xBF] # excluding overlongs
			| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
			| \xED[\x80-\x9F][\x80-\xBF] # excluding surrogates
			| \xF0[\x90-\xBF][\x80-\xBF]{2} # planes 1-3
			| [\xF1-\xF3][\x80-\xBF]{3} # planes 4-15
			| \xF4[\x80-\x8F][\x80-\xBF]{2} # plane 16
			)*$%xs', $data)
		) {
			if(extension_loaded('iconv')) {
				$data = iconv(iconv_get_encoding('internal_encoding'), 'UTF-8//IGNORE', $data);
			} else {
				$data = utf8_encode($data);
			}
		}
	}
}
