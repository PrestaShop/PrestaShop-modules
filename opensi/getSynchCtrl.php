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
if (file_exists(dirname(__FILE__).'/dao.class.php'))
	include_once("dao.class.php");

if (file_exists(dirname(__FILE__).'/getActionRequest.class.php'))
	include_once("getActionRequest.class.php");

$dao = new Dao();
$getOSIReq = new GetActionRequests(GlobalConfig::getWsUrl(), GlobalConfig::getWsPort(), 20, GlobalConfig::getWsLogin(), GlobalConfig::getWsPasswd(), GlobalConfig::getServiceCode(), GlobalConfig::getWebSiteCode());


/* WebService infos values */
$wSOInfosList = Scheduler::getWsInfosList();


/* GET requests */
if(ForceRequest::isForcedRequest()) {

	/* Send only one GET request */
	$wsName = ForceRequest::getWsName();

	$dateFrom = ForceRequest::getDateFrom();
	$dateTo = ForceRequest::getDateTo();

	doGetSynch($wsName, $dateFrom, $dateTo);

} else {

	/* Send all GET requests in function of the scheduler permissions */
	$wSOList = Scheduler::getWsNameGetList();
	foreach($wSOList as $wsName) {
		$dateFrom = $wSOInfosList[$CONF_LastrequestLbl.$wsName];
		$dateFrom = DATE::timestamp2Date($dateFrom);

		$dateTo = Scheduler::getTimeOnInit();
		$dateTo = DATE::timestamp2Date($dateTo);

		doGetSynch($wsName, $dateFrom, $dateTo);
	}

}


/* Get synchronisations */
function doGetSynch($wsName, $dateFrom, $dateTo) {

	GLOBAL $dao, $getOSIReq, $CONF_LastrequestLbl;

	/* Check if allow to get this request */
	$isAllow = Scheduler::isAllowedToRequest($wsName);
	if(!ForceRequest::isForcedRequest() && !$isAllow){
		return;
	}

	switch ($wsName) {

		/*
		 * WSO-G002
		 * GET /article/stock
		 */
		case "WSO-G002":
			Log::write("Do request ".$wsName." ------", "info");
			$response = $getOSIReq->wso_g002($dateFrom, $dateTo);
			$xmlObject = Xml::parseToObject($response->getResponse());

			$isOkResponse = false;

			if(isset($xmlObject) && isOkGetResult($response->getCode())){
				foreach($xmlObject->Stock_Article as $xmlStockArticleObject) {
					$quantity = $xmlStockArticleObject['Stock_Dispo'];
					$ref = $xmlStockArticleObject['Reference'];
					$dao->setStock($ref, $quantity);
					$dao->setStockAttributes($ref, $quantity);
					Log::write("Treatment for reference =  ".$xmlStockArticleObject['Reference'], "info");
				}
				$isOkResponse = true;
			}

			if($isOkResponse && !ForceRequest::isForcedRequest()){
				Configuration::updateValue($CONF_LastrequestLbl.$wsName, Scheduler::getTimeOnInit());
			}

			Log::write("End request  ".$wsName." ------<br /><br />", "info");
		break;


		/*
		 * WSO-G003
		 * GET /expedition/colis
		 */
		case "WSO-G003":
			Log::write("Do request ".$wsName." ------", "info");
			$response = $getOSIReq->wso_g003($dateFrom, $dateTo);
			$xmlObject = Xml::parseToObject($response->getResponse());

			$isOkResponse = false;

			if(isset($xmlObject) && isOkGetResult($response->getCode())){
				foreach($xmlObject->Colis_Expedition  as $xmlColisExpeditionObject) {
					$orderId = $xmlColisExpeditionObject['Num_Com_Web'];
					$trackingCode = $xmlColisExpeditionObject['Num_Colis'];
					$dao->setOrderTrackingCode($orderId, $trackingCode);
					Log::write("Treatment for order =  ".$orderId, "info");
				}
				$isOkResponse = true;
			}

			if($isOkResponse && !ForceRequest::isForcedRequest()){
				Configuration::updateValue($CONF_LastrequestLbl.$wsName, Scheduler::getTimeOnInit());
			}

			Log::write("End request  ".$wsName." ------<br /><br />", "info");
		break;


		/*
		 * WSO-G008
		 * GET /affaire/commande/etat
		 */
		case "WSO-G008":
			Log::write("Do request ".$wsName." ------", "info");
			$response = $getOSIReq->wso_g008($dateFrom, $dateTo);
			$xmlObject = Xml::parseToObject($response->getResponse());

			$isOkResponse = false;

			if(isset($xmlObject) && isOkGetResult($response->getCode())){
				foreach($xmlObject->Etat_Commande as $xmlStateObject) {
					$payState = $xmlStateObject['Statut_Paiement'];
					$logisticState = $xmlStateObject['Statut_Logistique'];
					$orderState = $xmlStateObject['Etat'];
					$orderNumWeb = $xmlStateObject['Num_Com_Web'];

					$idState = Mapping::osiState2PsStateId($logisticState, $payState, $orderState);
					if(isset($idState)){
						$dateAdd = Date::timestamp2DateTimeBdd(time());
						if($payState == 'P' || $payState == 'T') {
							Db::getInstance()->Execute('
								UPDATE `'._DB_PREFIX_.'opensi_order` set paid = "1", date_paid = "'.date('Y-m-d H:i:s').'" WHERE id_order = "'.$orderNumWeb.'" and paid = "0"
							');
						}
						$alwaysInserted = $dao->isAlwaysInThisState($orderNumWeb, $idState);
						if(!$alwaysInserted){
							$dao->setState($orderNumWeb, $idState, $dateAdd);
						}
					}
					Log::write("Treatment for num webOrder =  ".$orderNumWeb, "info");
				}
				$isOkResponse = true;
			}

			if($isOkResponse && !ForceRequest::isForcedRequest()){
				Configuration::updateValue($CONF_LastrequestLbl.$wsName, Scheduler::getTimeOnInit());
			}

			Log::write("End request  ".$wsName." ------<br /><br />", "info");


			/* After state orders, invoices are recovered if OpenSi invoices are enabled */
			if(Configuration::get('OSI_INVOICE') == 1) {
				/*
				 * WSO-G037
				 * GET /article/stock
				 */
				Log::write("Do request WSO-G037 ------", "info");
				$response = $getOSIReq->wso_g037($dateFrom, $dateTo);
				$xmlObject = Xml::parseToObject($response->getResponse());

				if(isset($xmlObject) && isOkGetResult($response->getCode())){
					foreach($xmlObject->Numero_Facture_Vente as $xmlInvoiceObject) {
						$invoice_number = $xmlInvoiceObject['Numero'];
						$type = $xmlInvoiceObject['Type']; // F = Facturé, A = Avoir
						$order = $xmlInvoiceObject['Num_Com_Web'];
						$dao->setInvoiceNumber(GlobalConfig::getServiceCode(), $order, $invoice_number, $type);
						Log::write("Treatment for number invoice = ".$xmlInvoiceObject['Numero']." (Order : ".$xmlInvoiceObject['Num_Com_Web'].")", "info");
					}
				}

				Log::write("End request  WSO-G037 ------<br /><br />", "info");
			}
		break;


		/*
		 * WSO-G009
		 * GET /article/prix
		 */
		case  "WSO-G009":
			Log::write("Do request ".$wsName." ------", "info");
			$response = $getOSIReq->wso_g009($dateFrom, $dateTo);
			$xmlObject = Xml::parseToObject($response->getResponse());

			$isOkResponse = false;

			if(isset($xmlObject) && isOkGetResult($response->getCode())){
				foreach($xmlObject->Prix_Article as $xmlPriceObject) {
					// values
					$ref = $xmlPriceObject['Reference'];
					$priceTtc = $xmlPriceObject['Tarif_TTC_'.GlobalConfig::getDefaultPrice()];
					$priceTtc = doubleval($priceTtc);
					$rate = $xmlPriceObject["Taux_TVA"];
					$rate = doubleval($rate);
					$purchaseHt = $xmlPriceObject['Prix_Achat'];
					$purchaseHt = doubleval($purchaseHt);
					$priceHt = $priceTtc/(1+($rate/100));

					/*
					 * Prestashop 1.3 => $tax = id_tax
					 * Prestashop 1.4 => $tax = id_tax_rules_group
					 */
					if(substr(_PS_VERSION_, 0, 3) > 1.3) {
						$tax = $dao->getTaxRuleGroupId($rate, GlobalConfig::getDefaultCountryId());
						if($tax == null){
							$dao->addTax($rate, GlobalConfig::getDefaultCountryId());
							$tax = $dao->getTaxRuleGroupId($rate, GlobalConfig::getDefaultCountryId());
						}
					} else {
						$tax = $dao->getTaxId($rate);
						if($tax == null){
							$dao->addTax($rate);
							$tax = $dao->getTaxId($rate);
						}
					}

					/* Update of the prices */
					$dao->setPrice($ref, $priceHt, $purchaseHt, $tax);
					$dao->setPriceAttribute($ref, $priceTtc, $priceHt, $purchaseHt, $tax, GlobalConfig::getDefaultCountryId());
					Log::write("Treatment for reference =  ".$ref, "info");
				}
				$isOkResponse = true;
			}

			if($isOkResponse && !ForceRequest::isForcedRequest()){
				Configuration::updateValue($CONF_LastrequestLbl.$wsName, Scheduler::getTimeOnInit());
			}
			Log::write("End request  ".$wsName." ------<br /><br />", "info");
		break;

	}
}


function isOkGetResult($codeResult) {
	/*
	 * Result code 200 => Ok
	 */
	if($codeResult == 200){
		return true;
	} else {
		Log::write("The HTTP response code is not ok (valid => 200). result code = ".$codeResult, "warn");
		return false;
	}
}