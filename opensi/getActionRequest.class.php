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

/* Loading OpenSi class request */
if (file_exists(dirname(__FILE__).'/utils/opensi_request_utils.class.php'))
	include_once('utils/opensi_request_utils.class.php');


/* Class */
class GetActionRequests extends OpenSiRequest  {

	private $isInit = false;

	/*
	 * Constructor
	 * @param $url
	 * @param $port
	 * @param $timeout
	 * @param $service_id
	 * @param $website_code
	 */
	function GetActionRequests($url, $port=80, $timeout=20, $httpLogin, $httpPasswd, $service_id, $website_code) {
		try {
			parent::OpenSiRequest($url, $port, $timeout, $httpLogin, $httpPasswd, $service_id, $website_code);
			$this->isInit = true;
		} catch (Exception $e) {
		    $this->isInit = false;
		}
	}


	/*
	 * Call the webService WSO-G002
	 * Return the stock of products
	 */
	function wso_g002($startDate, $endDate) {
		if($this->isInit) {
			parent::clearParamsData();
			parent::addParamData("action", "get_stock_article");
    		parent::addParamData('code_depot', GlobalConfig::getDepositCode());
    		parent::addParamData('date_debut', $startDate);
    		parent::addParamData('date_fin', $endDate);

    		return parent::doRequest("GET");
		}
	}


	/*
	 * Call the webService WSO-G003
	 * Return the tracking code from orders
	 */
	function wso_g003($startDate, $endDate) {
		if($this->isInit) {
			parent::clearParamsData();
			parent::addParamData("action", "get_tracking_colis");
    		parent::addParamData('date_debut', $startDate);
    		parent::addParamData('date_fin', $endDate);

    		return parent::doRequest("GET");
		}
	}


	/*
	 * Call the webService WSO-G008
	 * Return the stock of products
	 */
	function wso_g008($startDate, $endDate) {
		if($this->isInit) {
			parent::clearParamsData();
			parent::addParamData("action", "get_etat_commande");
    		parent::addParamData('date_debut', $startDate);
    		parent::addParamData('date_fin', $endDate);

    		return parent::doRequest("GET");
		}
	}


	/*
	 * Call the webService WSO-G009
	 * Return the price of products
	 */
	function wso_g009($startDate, $endDate) {
		if($this->isInit) {
			parent::clearParamsData();
			parent::addParamData("action", "get_prix_article");
    		parent::addParamData('date_debut', $startDate);
    		parent::addParamData('date_fin', $endDate);

    		return parent::doRequest("GET");
		}
	}


	/*
	 * Call the webService WSO-G037
	 * Return OpenSi number invoices
	 */
	function wso_g037($startDate, $endDate) {
		if($this->isInit) {
			parent::clearParamsData();
			parent::addParamData("action", "get_numeros_factures");
    		parent::addParamData('date_debut', $startDate);
    		parent::addParamData('date_fin', $endDate);

    		return parent::doRequest("GET");
		}
	}

}