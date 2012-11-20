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
if (file_exists(dirname(__FILE__).'/utils/opensi_request_utils.class.php'))
	include_once('utils/opensi_request_utils.class.php');


class PostActionRequests extends OpenSiRequest {

	private $isInit = false;

	/*
	 * Constructor
	 * @param $url
	 * @param $port
	 * @param $timeout
	 * @param $service_id
	 * @param $website_code
	 */
	function PostActionRequests($url, $port=80, $timeout=20, $httpLogin, $httpPasswd, $service_id, $website_code) {
		try {
			parent::OpenSiRequest($url, $port, $timeout, $httpLogin, $httpPasswd, $service_id, $website_code);
			$this->isInit = true;
		} catch (Exception $e) {
		    Log::write("error during init OpenSiRequest ".$e->getMessage(), "error");
		    $this->isInit = false;
		}
	}


	/*
	 * Call the webService WSO-P005
	 * WSO-P005 = create new products
	 */
	function wso_p005($xml) {
		if($this->isInit) {
			parent::clearParamsData();
			parent::addParamData("action", "create_article");
    		parent::setXmlData($xml);

    		return parent::doRequest("POST");
		}
	}


	/*
	 * Call the webService WSO-P006
	 * WSO-P006 = update products
	 */
	function wso_p006($xml, $reference) {
		if($this->isInit) {
			parent::clearParamsData();
			parent::addParamData("action", "update_article");
			parent::addParamData("reference", $reference);
    		parent::setXmlData($xml);

    		return parent::doRequest("PUT");
		}
	}


	/*
	 * Call the webService WSO-P010
	 * WSO-P010 = create new customers
	 */
	function wso_p010($xml) {
		if($this->isInit) {
			parent::clearParamsData();
			parent::addParamData("action", "create_client_web");
    		parent::setXmlData($xml);

    		return parent::doRequest("POST");
		}
	}


	/*
	 * Call the webService WSO-P011
	 * WSO-P011 = create new orders
	 */
	function wso_p011($xml) {
		if($this->isInit) {
			parent::clearParamsData();
			parent::addParamData("action", "create_commande_web");
    		parent::setXmlData($xml);

    		return parent::doRequest("POST");
		}
	}


	/*
	 * Call the webService WSO-P015
	 * WSO-P015 = create new bank transactions
	 */
	function wso_p015($xml) {
	if($this->isInit) {
			parent::clearParamsData();
			parent::addParamData("action", "create_transaction_bancaire");
    		parent::setXmlData($xml);

    		return parent::doRequest("POST");
		}
	}


	/*
	 * Call the webService WSO-P018
	 * WSO-P018 = Set stock of the product
	 */
	function wso_p018($xml) {
	if($this->isInit) {
			parent::clearParamsData();
			parent::addParamData("action", "create_invperm_article");
    		parent::setXmlData($xml);

    		return parent::doRequest("POST");
		}
	}


	/*
	 * Call the webService WSO-P025
	 * WSO-P025 = update customers
	 */
	function wso_p025($xml, $login) {
	if($this->isInit) {
			parent::clearParamsData();
			parent::addParamData("action", "update_client_web");
			parent::addParamData("login", $login);
    		parent::setXmlData($xml);

    		return parent::doRequest("PUT");
		}
	}


	/*
	 * Call the webService WSO-P034
	 * WSO-P034 = Set state of the product (enable/disable)
	 */
	function wso_p034($xml, $reference) {
	if($this->isInit) {
			parent::clearParamsData();
			parent::addParamData("action", "update_pubweb_article");
			parent::addParamData("reference", $reference);
    		parent::setXmlData($xml);

    		return parent::doRequest("PUT");
		}
	}

}