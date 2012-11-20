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
	include_once('dao.class.php');

if (file_exists(dirname(__FILE__).'/postActionRequest.class.php'))
	include_once('postActionRequest.class.php');

$dao = new Dao();
$postOSIReq = new PostActionRequests(GlobalConfig::getWsUrl(), GlobalConfig::getWsPort(), 20, GlobalConfig::getWsLogin(), GlobalConfig::getWsPasswd(), GlobalConfig::getServiceCode(), GlobalConfig::getWebSiteCode());


/* WebService infos values */
$wSOInfosList = Scheduler::getWsInfosList();


/* POST requests */
if(ForceRequest::isForcedRequest()) {

	/* Send only one POST request */
	$wsName = ForceRequest::getWsName();

	$dateFrom = ForceRequest::getDateFrom()." ".ForceRequest::getTimeFrom();
	$dateFrom =	date::dateTimeFr2DateTimeBdd($dateFrom);

	$dateTo = ForceRequest::getDateTo()." ".ForceRequest::getTimeTo();
	$dateTo = date::dateTimeFr2DateTimeBdd($dateTo);

	doPostSynch($wsName, $dateFrom, $dateTo);

} else {

	/* Send all POST requests in function of the scheduler permissions */
	$wSOList = Scheduler::getWsNamePostList();
	foreach($wSOList as $wsName) {
		$dateFrom = $wSOInfosList[$CONF_LastrequestLbl.$wsName];
		$dateFrom = DATE::timestamp2DateTimeBdd($dateFrom);

		$dateTo = Scheduler::getTimeOnInit();
		$dateTo = DATE::timestamp2DateTimeBdd($dateTo);

		doPostSynch($wsName, $dateFrom, $dateTo);
	}
}


/* POST synchronisations */
function doPostSynch($wsName, $dateFrom, $dateTo) {

	GLOBAL $dao, $postOSIReq, $CONF_LastrequestLbl, $isOkResponses, $nbChildrenProducts, $nbResponse200, $nbResponseError,$nbResponse409, $nbNoReference;

	/* Check if allow to get this request */
	$isAllow = Scheduler::isAllowedToRequest($wsName);
	if(!ForceRequest::isForcedRequest() && !$isAllow){
		return;
	}

	switch ($wsName) {

		/*
		 * WSO-P005
		 * POST /article
		 */
		case "WSO-P005":
			Log::write("###### Do request ".$wsName." - action=create_article ######", "info");

			$offset = 0;
			$limit = 1000;

			do {

				$newProducts = $dao->getLastCreateProducts($dateFrom, $dateTo, GlobalConfig::getDefaultLangId(), GlobalConfig::getDefaultCountryId(), $offset, $limit);
				$isOkResponses = true;

				$nbMainProducts = 0;
				$nbChildrenProducts = 0;
				$nbResponse200 = 0;
				$nbResponseError = 0;
				$nbResponse409 = 0;
				$nbNoReference = 0;

				if($newProducts) {
					$nbMainProducts = count($newProducts);

					foreach($newProducts as $product) {
						/*
						 * Create main product
						 * @param $product => array
						 * @param create child products
						 * @param send publication
						 */
						 createMainProduct($product, true, true);
					}

					Log::write("Nb total main products = ".$nbMainProducts, "info");
					Log::write("Nb total children products = ".$nbChildrenProducts, "info");
					Log::write("Nb without reference = ".$nbNoReference, "info");
					Log::write("Nb sent products = ".($nbMainProducts+$nbChildrenProducts-$nbNoReference), "info");
					Log::write("Nb response 200 (ok) = ".$nbResponse200, "info");
					Log::write("Nb response 409 (duplicate) = ".$nbResponse409, "info");
					Log::write("Nb response error = ".$nbResponseError, "info");

				} else {
					Log::write("No request to send", "info");
				}

				$offset += $limit;

			} while (count($newProducts) == $limit);

			if($isOkResponses && !ForceRequest::isForcedRequest()){
				Configuration::updateValue($CONF_LastrequestLbl.$wsName, Scheduler::getTimeOnInit());
			}

			Log::write("End request  ".$wsName." ------<br /><br />", "info");
		break;


		/*
		 * WSO-P006
		 * POST /article/$reference$
		 */
		case "WSO-P006":
			Log::write("###### Do request ".$wsName." - action=update_article ######", "info");

			$offset = 0;
			$limit = 1000;

			do {

				$updateProducts = $dao->getLastUpdateProducts($dateFrom, $dateTo, GlobalConfig::getDefaultLangId(), GlobalConfig::getDefaultCountryId(), $offset, $limit);
				$isOkResponses = true;

				$nbMainProducts = 0;
				$nbChildrenProducts = 0;
				$nbResponse200 = 0;
				$nbResponseError = 0;
				$nbResponse409 = 0;
				$nbNoReference = 0;

				if($updateProducts) {
					$nbMainProducts = count($updateProducts);

					foreach($updateProducts as $product) {
						updateMainProduct($product);
					}

					Log::write("Nb total main products = ".$nbMainProducts, "info");
					Log::write("Nb total children products = ".$nbChildrenProducts, "info");
					Log::write("Nb without reference = ".$nbNoReference, "info");
					Log::write("Nb sent products = ".($nbMainProducts+$nbChildrenProducts-$nbNoReference), "info");
					Log::write("Nb response 200 (ok) = ".$nbResponse200, "info");
					Log::write("Nb response 409 (duplicate) = ".$nbResponse409, "info");
					Log::write("Nb response error = ".$nbResponseError, "info");

				} else {
					Log::write("No request to send", "info");
				}

				$offset += $limit;

			} while (count($updateProducts) == $limit);

			if($isOkResponses && !ForceRequest::isForcedRequest()){
				Configuration::updateValue($CONF_LastrequestLbl.$wsName, Scheduler::getTimeOnInit());
			}

			Log::write("End request  ".$wsName." ------<br /><br />", "info");
		break;


		/*
		 * WSO-P010
		 * POST /client/web
		 */
		case "WSO-P010":
			Log::write("###### Do request ".$wsName." - action=create_client_web ######", "info");
			$newCustomers = $dao->getLastCustomers($dateFrom, $dateTo);

			$isOkResponses = true;

			if($newCustomers) {
				foreach($newCustomers as $customer) {
					$xmlCustomer = XML::createCustomerXml($customer);
					Log::write("Send request for login =  ".$customer['id_customer']." - ", "info");
					$response = $postOSIReq->wso_p010($xmlCustomer);

					if(!isOkPostResult($response->getCode())){
						$isOkResponses = false;
					}
				}
			} else {
				Log::write("No request to send", "info");
			}

			if($isOkResponses && !ForceRequest::isForcedRequest()){
				Configuration::updateValue($CONF_LastrequestLbl.$wsName, Scheduler::getTimeOnInit());
			}

			Log::write("End request  ".$wsName." ------<br /><br />", "info");
		break;


		/*
		 * WSO-P011
		 * POST /affaire/commande/web
		 */
		case "WSO-P011":
			Log::write("###### Do request ".$wsName." - action=create_commande_web ######", "info");
			/* Check if So Colissimo is installed */
			$tableSoColissimo = $dao->socolissimo_exists(_DB_NAME_);
			if($tableSoColissimo > 0) {
				/* So Colissimo is installed */
				$orders = $dao->getLastOrdersSoColissimo($dateFrom, $dateTo, GlobalConfig::getDefaultCountryId());
			} else {
				/* So Colissimo is not installed */
				$orders = $dao->getLastOrders($dateFrom, $dateTo, GlobalConfig::getDefaultCountryId());
			}
			$giftTax = $dao->getGiftTax();

			$isOkResponses = true;

			if($giftTax != null){
				$giftTax = floatval($giftTax);
			} else {
				$giftTax = 0;
			}

			if($orders) {
				foreach($orders as $order) {
					$orderDetails = $dao->getOrderDetails($order['id_order'], GlobalConfig::getDefaultLangId());

					if($orderDetails) {
						/* Add virtual article for gift */
						if($order['gift'] == "1") {
							$virtualArticle['reference'] = '_EMB';
							$virtualArticle['product_name'] = 'Emballage cadeau';
							$virtualArticle['product_quantity'] = 1;
							$virtualArticle['tax_rate'] = $giftTax;
							$virtualArticle['Remise_Pourc'] = 0;
							$virtualArticle['id_order'] = $order['id_order'];
							$virtualArticle['product_price'] = $order['total_wrapping'] / (1 + (floatval($giftTax) / 100));
							$orderDetails[] = $virtualArticle;
						}

						$xmlProduct = XML::createOrderXml($order, $orderDetails);
						$orderNumber = $order['id_order'];
						Log::write("Send request for order = ".$orderNumber." - ", "info");
						$response = $postOSIReq->wso_p011($xmlProduct, $reference);

						if(!isOkPostResult($response->getCode())){
							$isOkResponses = false;
						} else {
							$now = date('Y-m-d H:i:s');
							Db::getInstance()->Execute('
								INSERT INTO `'._DB_PREFIX_.'opensi_order` (id_order, date_order_synchro, transaction, date_transaction, paid, date_paid)
								VALUES ("'.$order['id_order'].'", "'.$now.'", 0, 0, 0, 0);
							');
						}
					} else {
						Log::write("ERROR - Order ID ".$order['id_order']." - Error during synchronisation of the order : No products found for this order !", "ERROR");
					}
				}
			} else {
				Log::write("No request to send", "info");
			}

			/* Update OSI_LASTREQUEST_WSO-P011 value */
			if($isOkResponses && !ForceRequest::isForcedRequest()){
				Configuration::updateValue($CONF_LastrequestLbl.$wsName, Scheduler::getTimeOnInit());
			}

			Log::write("End request  ".$wsName." ------<br /><br />", "info");
		break;


		/*
		 * WSO-P015
		 * POST /affaire/commande/web/transaction
		 */
		case "WSO-P015":
			Log::write("###### Do request ".$wsName." - action=create_transaction_bancaire ######", "info");
			$orders = $dao->getLastCreateOrUpdateOrders($dateFrom, $dateTo);

			$isOkResponses = true;

			if($orders) {
				foreach($orders as $order) {
					if(($order['valid'] == 1) && ($order['total_paid_real'] != 0)) {
						$xmlTransaction = XML::createTransactionWebXml($order);
						$idOrder = $order['id_order'];
						Log::write("Send request for order =  ".$idOrder." - ", "info");
						$response = $postOSIReq->wso_p015($xmlTransaction);

						if(!isOkPostResult($response->getCode())){
							$isOkResponses = false;
							Log::write("...PROBLEME AVEC ".$idOrder."...", "ERROR");
						} else {
							$now = date('Y-m-d H:i:s');
							Db::getInstance()->Execute('
								UPDATE `'._DB_PREFIX_.'opensi_order` set transaction = "1", date_transaction = "'.$now.'" WHERE id_order = "'.$idOrder.'";
							');
						}
					}
				}
			} else {
				Log::write("No request to send", "info");
			}

			if($isOkResponses && !ForceRequest::isForcedRequest()){
				Configuration::updateValue($CONF_LastrequestLbl.$wsName, Scheduler::getTimeOnInit());
			}
			Log::write("End request  ".$wsName." ------<br /><br />", "info");
		break;


		/*
		 * WSO-P025
		 * POST /client/web/$code_site_web$/$login$
		 */
		case "WSO-P025":
			Log::write("###### Do request ".$wsName." - action=update_client_web ######", "info");
			$editCustomers = $dao->getLastUpdateCustomers($dateFrom, $dateTo);

			$isOkResponses = true;

			if($editCustomers) {
				foreach($editCustomers as $customer) {
					$xmlCustomer = XML::createCustomerXml($customer);
					$login = $customer["id_customer"];
					Log::write("Send request for login =  ".$login." - ", "info");
					$response = $postOSIReq->wso_p025($xmlCustomer, $login);

					if(!isOkPostResult($response->getCode())){
						$isOkResponses = false;
					}
				}
			} else {
				Log::write("No request to send", "info");
			}

			if($isOkResponses && !ForceRequest::isForcedRequest()){
				Configuration::updateValue($CONF_LastrequestLbl.$wsName, Scheduler::getTimeOnInit());
			}
			Log::write("End request  ".$wsName." ------<br /><br />", "info");
		break;
	}
}


function isOkPostResult($codeResult) {
	/*
	 * 200 => Ok
	 * 201 => Created
	 * 409 => Conflict (Already exists into openSi / can not duplicate element)
	 */
	if($codeResult == 200 || $codeResult == 201 || $codeResult == 409){
		return true;
	} else {
		Log::write("The HTTP response code is not ok (valid => 200, 201, 409). result code = ".$codeResult, "warn");
		return false;
	}
}


/*
 * Generate a map with attributes
 * The key is used on xml product
 */
function generateAttributesMap($attributes) {
	$attributesMap;
	foreach($attributes as $attribute) {
		if(GlobalConfig::getLinkAttribute1_isfeature() == 0 && $attribute['id_attribute_group'] == GlobalConfig::getLinkAttribute1()) {
			$attributesMap["Attribut_1"] = $attribute['name'];
		} else if(GlobalConfig::getLinkAttribute2_isfeature() == 0 && $attribute['id_attribute_group'] == GlobalConfig::getLinkAttribute2()) {
			$attributesMap["Attribut_2"] = $attribute['name'];
		} else if(GlobalConfig::getLinkAttribute3_isfeature() == 0 && $attribute['id_attribute_group'] == GlobalConfig::getLinkAttribute3()) {
			$attributesMap["Attribut_3"] = $attribute['name'];
		} else if(GlobalConfig::getLinkAttribute4_isfeature() == 0 && $attribute['id_attribute_group'] == GlobalConfig::getLinkAttribute4()) {
			$attributesMap["Attribut_4"] = $attribute['name'];
		} else if(GlobalConfig::getLinkAttribute5_isfeature() == 0 && $attribute['id_attribute_group'] == GlobalConfig::getLinkAttribute5()) {
			$attributesMap["Attribut_5"] = $attribute['name'];
		} else if(GlobalConfig::getLinkAttribute6_isfeature() == 0 && $attribute['id_attribute_group'] == GlobalConfig::getLinkAttribute6()) {
			$attributesMap["Attribut_6"] = $attribute['name'];
		}
	}
	return $attributesMap;
}


/*
 * Generate a map with features
 * The key is used on xml product
 */
function generateFeaturesMap($features) {
	$featuresMap;
	foreach($features as $feature) {
		$id_feature = $feature['id_feature'];
		if($id_feature == GlobalConfig::getLinkFeatureVolume()) {
			$featuresMap["Volume"] = $feature['feature_value'];
		} else if(GlobalConfig::getLinkAttribute1_isfeature() == 1 && $id_feature == GlobalConfig::getLinkAttribute1()){
			$featuresMap["Attribut_1"] = $feature['feature_value'];
		} else if(GlobalConfig::getLinkAttribute2_isfeature() == 1 && $id_feature == GlobalConfig::getLinkAttribute2()){
			$featuresMap["Attribut_2"] = $feature['feature_value'];
		} else if(GlobalConfig::getLinkAttribute3_isfeature() == 1 && $id_feature == GlobalConfig::getLinkAttribute3()){
			$featuresMap["Attribut_3"] = $feature['feature_value'];
		} else if(GlobalConfig::getLinkAttribute4_isfeature() == 1 && $id_feature == GlobalConfig::getLinkAttribute4()){
			$featuresMap["Attribut_4"] = $feature['feature_value'];
		} else if(GlobalConfig::getLinkAttribute5_isfeature() == 1 && $id_feature == GlobalConfig::getLinkAttribute5()){
			$featuresMap["Attribut_5"] = $feature['feature_value'];
		} else if(GlobalConfig::getLinkAttribute6_isfeature() == 1 && $id_feature == GlobalConfig::getLinkAttribute6()){
			$featuresMap["Attribut_6"] = $feature['feature_value'];
		}
	}
	return $featuresMap;
}


function sortAttributesByRef($attributes) {
	/* Group attributes by unique reference */
	$attributesByReference = array();
	foreach($attributes as $attribute) {
		$ref = $attribute['reference'];
		if($ref != ""){
			$attributesByReference[$ref][] = $attribute;
		}
	}
	return $attributesByReference;
}


/* Create main products */
function createMainProduct($product, $createChild, $sendPub) {
	GLOBAL $dao, $postOSIReq, $CONF_LastrequestLbl, $isOkResponses, $nbChildrenProducts, $nbResponse200, $nbResponseError,$nbResponse409, $nbNoReference;

	$attributes = $dao->getAttributesProduct($product['id_product'], GlobalConfig::getDefaultLangId());

	$features = $dao->getCategoriesProduct($product['id_product'], GlobalConfig::getDefaultLangId());
	$featuresMap = generateFeaturesMap($features);

	$product['families'] = true;

	/* Determine if this main product as child (attributes) */
	$product['has_child'] = ($attributes!=null && is_array($attributes))?(true):(false);

	/* Only if the reference is not empty */
	if($product['reference'] != "") {
		/* Set product (original product without attributes) */
		$xmlProduct = XML::createProductXml($product, true, "", $featuresMap, "");
		Log::write("Send request for creating reference =  ".$product['reference']." - ", "info");

		$response = $postOSIReq->wso_p005($xmlProduct);
		if($response->getCode() == 200) {
			$nbResponse200++;
			$xmlStockProduct = XML::createStockProductXml($product);
			$postOSIReq->wso_p018($xmlStockProduct); //set new stock
		} else if($response->getCode() == 409) {
			$nbResponse409++;
		} else {
			$nbResponseError++;
		}

		if($sendPub) {
			$xmlPublicationProduct = XML::createPublicationProductXml($product);
			$response = $postOSIReq->wso_p034($xmlPublicationProduct, $product['reference']); //set state (activated/not activated)
		}

		if(!isOkPostResult($response->getCode())){
			$isOkResponses = false;
		}
	} else {
		Log::write("No reference found for product ID ".$product['id_product'], "error");
		$nbNoReference++;
	}

	if($createChild) {
		createChildProduct($product, $attributes, $featuresMap, '');
	}
}


/* Create child products */
function createChildProduct($product, $attributes, $featuresMap, $onlyThisReference, $sendPub) {
	GLOBAL $dao, $postOSIReq, $CONF_LastrequestLbl, $isOkResponses, $nbChildrenProducts, $nbResponse200, $nbResponseError, $nbResponse409, $nbNoReference;

	/* Group attributes by unique reference */
	$attributesByReference = sortAttributesByRef($attributes);

	$product['families'] = true;

	/* Set derivated products (same product with attributes - the sons) */
	foreach($attributesByReference as $productReference => $attributesGrouped) {

		if($onlyThisReference == '') {
			$nbChildrenProducts++;

			/* Set the reference of this product */
			$product['reference'] = $productReference;

			/* Only if the reference is not empty */
			if($product['reference'] != "") {
				/* Set child more informations (child infos) */
				if(count($attributesGrouped >0)){
					$productChildInfos = $attributesGrouped[0];
				} else {
					$productChildInfos = "";
				}

				$attributesMap = generateAttributesMap($attributesGrouped);

				$xmlProduct = XML::createProductXml($product, false, $attributesMap, $featuresMap, $productChildInfos);
				Log::write("Send request for creating child reference =  ".$product['reference']." - ", "info");

				$response = $postOSIReq->wso_p005($xmlProduct);
				$childProduct = $product;
				$childProduct['quantity'] = $productChildInfos['quantity'];
				if($response->getCode() == 200) {
					$nbResponse200++;
					$xmlStockProduct = XML::createStockProductXml($childProduct);
					$postOSIReq->wso_p018($xmlStockProduct); //set new stock
				} else if($response->getCode() == 409) {
					$nbResponse409++;
				} else {
					$nbResponseError++;
				}

				$xmlPublicationProduct = XML::createPublicationProductXml($product);
				$response = $postOSIReq->wso_p034($xmlPublicationProduct, $product['reference']); //set state (activated/not activated)

				if(!isOkPostResult($response->getCode())){
					$isOkResponses = false;
				}
			} else {
				Log::write("No reference found for child product ID ".$product['id_product'], "error");
				$nbNoReference++;
			}
		} else {
			if($productReference == $onlyThisReference) {
				/* Set the reference of this product */
				$product['reference'] = $productReference;

				/* Only if the reference is not empty */
				if($product['reference'] != "") {
					/* Set child more informations (child infos) */
					if(count($attributesGrouped >0)){
						$productChildInfos = $attributesGrouped[0];
					} else {
						$productChildInfos = "";
					}

					$attributesMap = generateAttributesMap($attributesGrouped);

					$xmlProduct = XML::createProductXml($product, false, $attributesMap, $featuresMap, $productChildInfos);
					Log::write("Send request for child reference =  ".$product['reference'], "info");

					$response = $postOSIReq->wso_p005($xmlProduct);
					$childProduct = $product;
					$childProduct['quantity'] = $productChildInfos['quantity'];
					if($response->getCode() == 200) {
						$nbResponse200++;
						$xmlStockProduct = XML::createStockProductXml($childProduct);
						$postOSIReq->wso_p018($xmlStockProduct); //set new stock
					} else if($response->getCode() == 409) {
						$nbResponse409++;
					} else {
						$nbResponseError++;
					}

					if($sendPub) {
						$xmlPublicationProduct = XML::createPublicationProductXml($product);
						$response = $postOSIReq->wso_p034($xmlPublicationProduct, $product['reference']); //set state (activated/not activated)
					}

					if(!isOkPostResult($response->getCode())){
						$isOkResponses = false;
					}
				} else {
					Log::write("No reference found for child product ID ".$product['id_product'], "error");
					$nbNoReference++;
				}
			}
		}
	}
}


/* Update main products */
function updateMainProduct($product) {
	GLOBAL $dao, $postOSIReq, $CONF_LastrequestLbl, $isOkResponses, $nbChildrenProducts, $nbResponse200, $nbResponseError,$nbResponse409, $nbNoReference;

	$attributes = $dao->getAttributesProduct($product['id_product'], GlobalConfig::getDefaultLangId());

	$features = $dao->getCategoriesProduct($product['id_product'], GlobalConfig::getDefaultLangId());
	$featuresMap = generateFeaturesMap($features);

	/*
	 * Do not send price for update
	 * Firstly, save creation informations if needed
	 */
	$createPrice = $product['price'];
	$createRate = $product['rate'];
	$createQuantity = $product['quantity'];

	/* Secondly, do not send price, rate for update */
	$product['price'] = null;
	$product['rate'] = null;

	/* Determine if this main product as child (attributes) */
	$product['has_child'] = ($attributes!=null && is_array($attributes))?(true):(false);

	/* Only if the  reference is not empty */
	if($product['reference'] != "") {
		/* Set product (original product without attributes) */
		$xmlProduct = XML::createProductXml($product, true, "", $featuresMap, "");
		Log::write("Send request for updating reference =  ".$product['reference']." - ", "info");

		$response = $postOSIReq->wso_p006($xmlProduct, $product['reference']);
		if($response->getCode() == 200) {
			$nbResponse200++;
		} else if($response->getCode() == 404) {
			/*
			 * Create product
			 */
			if(Configuration::get('OSI_ACTIVE_WSO-P005') == 1) {
				Log::write("Update not possible, the product was not found => Send request for creation of the product ".$product['reference']." (Id ".$product['id_product'].") - ", "info");
				$product['price'] = $createPrice;
				$product['rate'] = $createRate;
				$product['quantity'] = $createQuantity;
				createMainProduct($product, false, false);
			} else {
				Log::write("Update not possible, the product was not found => No creation made (webservice disabled) - ", "info");
			}
		} else if($response->getCode() == 409) {
			$nbResponse409++;
		} else {
			$nbResponseError++;
		}

		$xmlPublicationProduct = XML::createPublicationProductXml($product);
		$response = $postOSIReq->wso_p034($xmlPublicationProduct, $product['reference']); //set state (activated/not activated)

		if(!isOkPostResult($response->getCode())){
			$isOkResponses = false;
		}
	} else {
		Log::write("No reference found for product ID ".$product['id_product'], "error");
		$nbNoReference++;
	}

	// Set the prices, TVA if needed
	$product['price'] = $createPrice;
	$product['rate'] = $createRate;
	$product['quantity'] = $createQuantity;

	/* Update child product */
	updateChildProduct($product, $attributes, $featuresMap);
}


/* Update child products */
function updateChildProduct($product, $attributes, $featuresMap) {
	GLOBAL $dao, $postOSIReq, $CONF_LastrequestLbl, $isOkResponses, $nbChildrenProducts, $nbResponse200, $nbResponseError,$nbResponse409, $nbNoReference;

	/* Group attributes by unique reference */
	$attributesByReference = sortAttributesByRef($attributes);

	/*
	 * Do not send price for update
	 * Firstly, save creation informations if needed
	 */
	$createPrice = $product['price'];
	$createRate = $product['rate'];
	$createQuantity = $product['quantity'];

	/* Set derivated products (same product with attributes - the sons) */
	foreach($attributesByReference as $productReference => $attributesGrouped) {

		$nbChildrenProducts++;

		/* Set the reference of this product */
		$product['reference'] = $productReference;

		/* Secondly, do not send price, rate for update */
		$product['price'] = null;
		$product['rate'] = null;


		/* Only if the reference is not empty */
		if($product['reference'] != "") {
			/* Set child more informations (child infos) */
			if(count($attributesGrouped >0)){
				$productChildInfos = $attributesGrouped[0];
			} else {
				$productChildInfos = "";
			}

			$attributesMap = generateAttributesMap($attributesGrouped);

			$xmlProduct = XML::createProductXml($product, false, $attributesMap, $featuresMap, $productChildInfos);
			Log::write("Send request for updating child reference =  ".$product['reference']." - ", "info");

			$response = $postOSIReq->wso_p006($xmlProduct, $product['reference']);
			if($response->getCode() == 200) {
				$nbResponse200++;
			} else if($response->getCode() == 404) {
				/*
				 * Create product
				 */
				if(Configuration::get('OSI_ACTIVE_WSO-P005') == 1) {
					Log::write("Update not possible, the child product was not found => Send request for creation of the child product ".$product['reference']." (Id ".$product['id_product'].") - ", "info");
					$product['price'] = $createPrice;
					$product['rate'] = $createRate;
					$product['quantity'] = $createQuantity;
					createChildProduct($product, $attributes, $featuresMap, $product['reference']);
				} else {
					Log::write("Update not possible, the child product was not found => no creation made (webservice disabled) - ", "info");
				}
			} else if($response->getCode() == 409) {
				$nbResponse409++;
			} else {
				$nbResponseError++;
			}

			$xmlPublicationProduct = XML::createPublicationProductXml($product);
			$response = $postOSIReq->wso_p034($xmlPublicationProduct, $product['reference']); //set state (activated/not activated)

			if(!isOkPostResult($response->getCode())){
				$isOkResponses = false;
			}
		} else {
			Log::write("No reference found for child product ID ".$product['id_product'], "error");
			$nbNoReference++;
		}
	}
}