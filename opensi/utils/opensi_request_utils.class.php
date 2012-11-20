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

/* Loading class */
if (file_exists(dirname(__FILE__).'/curl_request_utils.class.php'))
	include_once('curl_request_utils.class.php');

class OpenSiRequest extends curlRequest { 

	var $service_id;
	var $website_code;

    /*
     * Constructor
     * @param $url
     * @param $port
     * @param $timeout
     * @param $site_id
     * @param $client_id
     */
	function OpenSiRequest($url, $port, $timeout, $httpLogin, $httpPasswd, $service_id, $website_code) {
		parent::CurlRequest($url, $port, $timeout, $httpLogin, $httpPasswd);

		$this->service_id = $service_id;
		$this->website_code = $website_code;
	}


	/*
     * send a request
	 *
	 *	if error return -1
	 *	else return response
     */
	protected function doRequest($methode) {
		parent::addParamData("service_id", $this->service_id);
		parent::addParamData("code_site_web", $this->website_code);
		return parent::doRequest($methode);
	}

}