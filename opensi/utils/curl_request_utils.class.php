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

class CurlRequest {

  	var $url;
    var $port;
    var $httpLogin;
    var $httpPasswd;
    var $timeout;
	var $arrParams;
	var $xmlToSend;

    /*
     * Constructor
     * @param $url
     * @param $port
     * @param $timeout
     * @param $httpLogin
     * @param $httpPasswd
     */
	function CurlRequest($url, $port, $timeout, $httpLogin, $httpPasswd) {

		if (!extension_loaded('curl')) {
			throw new Exception("The CURL extension is not enabled.");
		}

		$this->url = $url;
		$this->port = $port;
		$this->httpLogin = $httpLogin;
		$this->httpPasswd = $httpPasswd;
		$this->timeout = $timeout;
	}

	public function addParamData($nom_champ, $valeur) {
		if (!isset($this->arrParams[$nom_champ]) && !is_array($valeur) && $valeur!="") {
			$this->arrParams[$nom_champ] = $valeur;
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function clearParamsData() {
		$this->arrParams = "";
	}

	public function setXmlData($xml) {
		$this->xmlToSend = $xml;
	}

	public function sendPostRequest() {
		return $this->doRequest("POST");
	}

	public function sendGetRequest() {
		return $this->doRequest("GET");
	}

	public function sendPutRequest() {
		return $this->doRequest("PUT");
	}


	/*
     * send a request
	 *
	 *	if error return -1
	 *	else return response
     */
	protected function doRequest($methode) {

		/* Construct field string like ?var1=val1&var2=val2... */
		$fields_string = "";
		if (is_array($this->arrParams)) {
			$fields_string = http_build_query($this->arrParams); 
		}

		$isXmlToSend = (isset($this->xmlToSend) && $this->xmlToSend != "") ? (true) : (false);

		/* Init curl */
		if($methode == "GET") {

			if($fields_string != ""){
				$fields_string = "?".$fields_string;
			}
			echo "URL request [GET] : ".$this->url.$fields_string."<br /><br />";
			$process = curl_init($this->url.$fields_string);
			curl_setopt($process, CURLOPT_POST, 0); 

		} else if($methode == "PUT") {

			if($isXmlToSend) {
				if($fields_string != ""){
					$fields_string = "?".$fields_string;
				}
				$process = curl_init($this->url.$fields_string);	
				echo "URL request [PUT] : ".$this->url.$fields_string."<br /><br />";
			} else {
				$process = curl_init($this->url);
				curl_setopt($process, CURLOPT_POSTFIELDS, $fields_string); 
				curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($fields_string)));
			}
			curl_setopt($process, CURLOPT_CUSTOMREQUEST, 'PUT');

		} else {

			if($isXmlToSend) {
				if($fields_string != ""){
					$fields_string = "?".$fields_string;
				}
				$process = curl_init($this->url.$fields_string);
				echo "URL request [POST] : ".$this->url.$fields_string."<br /><br />";
			} else {
				$process = curl_init($this->url);
				curl_setopt($process, CURLOPT_POSTFIELDS, $fields_string); 
				curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($fields_string)));
			}
			curl_setopt($process, CURLOPT_POST, 1); 
		}

		/* Send XML into body */
		if($isXmlToSend){
			curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
			curl_setopt($process, CURLOPT_POSTFIELDS, $this->xmlToSend);

			echo "XML request : <br /><textarea rows='5' cols='100'>".$this->xmlToSend."</textarea><br /><br />";
		}

		curl_setopt($process, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($process, CURLOPT_CONNECTTIMEOUT, $this->timeout);
		curl_setopt($process, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($process, CURLOPT_USERPWD, $this->httpLogin.":".$this->httpPasswd);
		curl_setopt($process,CURLOPT_SSL_VERIFYPEER, false); //check certificat


		/* Test connection to webservices, 10x */
		for($i=0; $i < 10; $i++){
        	$resultReq = curl_exec($process);
        	if($resultReq === FALSE){
				/* Wait one second to retry the connection */
				sleep(1);
				$resultReq = null;
			} else {
				break;
			}
		}

		/* If you have not been able to connect 10 times, it throws an exception and stops */
		if (!isset($resultReq)) {
			throw new Exception("error curl request (method=".$methode.") : '" . curl_error($process) . "'");
		}

		$code = curl_getinfo($process, CURLINFO_HTTP_CODE);
		curl_close($process);

		$reqAction = $this->arrParams["action"];
		$responseObj = new CurlResponse($reqAction, $code, $resultReq);

		echo "ResponseObject : <br /><textarea rows='5' cols='100'>".$responseObj->getResponse()."</textarea><br />Code return ".$responseObj->getCode()."<br /><br />";
		return $responseObj;

    }	

}


/*
 * Response CURL Object
 *  
 * @date 20101027
 * @author S.Bignet (2AAS agency)
 *
 */
class CurlResponse {
	var $reqActon;
	var $code;
	var $response;

    /*
     * Constructor
     * @param $resCode
     * @param $res
     */
	function CurlResponse($reqAction, $resCode, $res) {
		$this->reqActon = $reqAction;
		$this->code = $resCode;
		$this->response = $res;
	}

	function getReqAction() {
		return $this->reqActon;
	}

	function getCode() {
		return $this->code;
	}

	function getResponse() {
		return $this->response;
	}
}