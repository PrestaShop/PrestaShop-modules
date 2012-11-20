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

class Xml {

	/*
	 * Parse xml to object 
	 * return boolean false if no input xml 
	 * @param $xml
	 */
	public static function parseToObject($xml) {
		return simplexml_load_string($xml);
	}

	/*
	 * Create new response xml (DomDocument)
	 */
	private static function createResponseXml() {
		$docXml = new DomDocument('1.0', 'UTF-8');
		$docXml->formatOutput = true;

		return $docXml;
	} 

	/*
	 * WSO_P010 &&  WSO_P025
	 * new customer
	 * @param $customer
	 */
	public static function createCustomerXml($customer) {
		$docXml = self::createResponseXml();
		$request = $docXml->createElement("request");

		$customerElem = $docXml->createElement("Client_Web");

		$customerElem->setAttribute('Code_Site_Web', GlobalConfig::getWebSiteCode());
		$customerElem->setAttribute('Login', $customer['id_customer']);
		$customerElem->setAttribute('Civilite', Mapping::psCivility2OsiCivility($customer['id_gender']));
		$customerElem->setAttribute('Nom', $customer['customerLastname']);
		$customerElem->setAttribute('Prenom', $customer['customerFirstname']);
		$customerElem->setAttribute('Societe', $customer['company']);
		$customerElem->setAttribute('Adresse_1', $customer['address1']);
		$customerElem->setAttribute('Adresse_2', $customer['address2']);
		$customerElem->setAttribute('Code_Postal', $customer['postcode']);
		$customerElem->setAttribute('Ville', $customer['city']);
		$customerElem->setAttribute('Code_Pays', $customer['iso_code']);
		$phone = ($customer["phone"] != "")?($customer["phone"]):($customer["phone_mobile"]);
		$customerElem->setAttribute('Telephone', $phone);
		$customerElem->setAttribute('Email', $customer['email']);

		$request->appendChild($customerElem);

		$docXml->appendChild($request);
		return $docXml->saveXML();
	}


	/*
	 * WSO_P005 && WSO_P006
	 * new product && update product
	 * @param $product (the main product)
	 * @param $isMainProduct (boolean, define it's the main product or child)
	 * @param $attributesMap (a map with attributes)
	 * @param $featuresMap (a map with features)
	 * @param $productChildInfos (only used for child;  contains more infos about child)
	 */
	public static function createProductXml($product, $isMainProduct=true, $attributesMap, $featuresMap, $productChildInfos) {
		$docXml = self::createResponseXml();
		$request = $docXml->createElement("request");

		$ProductElem = $docXml->createElement("Article");

		$ProductElem->setAttribute('Reference', trim($product['reference']));
		$ProductElem->setAttribute('Designation', self::cut($product['product_name'],100));

		if($product['description_short']!=""){
			$description1 = $product['description_short'];
			$description1 = self::convertToUTF8($description1);
			$ProductElem->setAttribute('Description_1', $description1);
		}

		if ($product['description']!="") {
			$description2 = $product['description'];
			$description2 = self::convertToUTF8($description2);
			$ProductElem->setAttribute('Description_2', $description2);
		}

		if($product['id_manufacturer'] != 0 ){
			//if this Id == 0, manufacturer not exists
			$ProductElem->setAttribute('Marque', $product['manufacturer_name']);
		}

		if($product['rate'] != null){
			$tax = round(floatval($product['rate']) ,4);
		} else {
			$tax = 0;
		}

		if($product['price'] != null) {

			$ProductElem->setAttribute('Taux_Tva', $tax);

			if(substr(_PS_VERSION_, 0, 3) > 1.3) {
				/* Prestashop 1.4 */
				if(!$isMainProduct){
					$ht = round(floatval($product['price']) + floatval($productChildInfos['price']) ,4);
					$ttc = round(floatval($product['price']) + ($product['price']*$tax/100), 2) + round(floatval($productChildInfos['price']) + floatval($productChildInfos['price']*$tax/100) ,2);
				} else {
					$ht =  round(floatval($product['price']) ,4);
					$ttc = round(floatval($ht + ($ht*$tax/100)) ,4);
				}
			} else {
				/* Prestashop 1.3 */
				$ht =  round(floatval($product['price']) ,4);
				$ttc = round(floatval($ht + ($ht*$tax/100)) ,4);

				/* Set the price for child article (attribute article) */
				if(!$isMainProduct){
					$ttc += round(floatval($productChildInfos['price']) ,4);
				}
			}

			if(GlobalConfig::getDefaultPrice() == 1) {
				$ProductElem->setAttribute('Tarif_TTC_1', $ttc);
			} else {
				$ProductElem->setAttribute('Tarif_TTC_1', 0);
				$ProductElem->setAttribute('Tarif_TTC_'.GlobalConfig::getDefaultPrice(), $ttc);
			}

			$purchasePrice = round(floatval($product['wholesale_price']),4);

			/* Set the wholesale price for child article (attribute article) */
			if(!$isMainProduct && $productChildInfos['wholesale_price'] != 0){
				$purchasePrice = round(floatval($productChildInfos['wholesale_price']) ,4);
			}			

			if($purchasePrice != 0) {
				$ProductElem->setAttribute('Prix_Achat', $purchasePrice);	
			}

		}

		$weight = $product['weight'];
		if(!$isMainProduct && $productChildInfos['weight'] != 0) {
			$weight += $productChildInfos['weight'];
		}
		$ProductElem->setAttribute('Poids', $weight);


		$ean13 = $product['ean13']; 
		if (!$isMainProduct && $productChildInfos['ean13'] != "") {
			/* If child ean13 not defined, used main product ean13 */
			$ean13 = $productChildInfos['ean13'];
		}
		if ($ean13 != "") {
			$ProductElem->setAttribute('Code_Barre', $ean13);
		}

		/* Families */
		if($product['families'] != null)
			$ProductElem->setAttribute('Famille_1', 'NC');

		/* Init attributes and features */
		$attAndFeatures = array("Attribut_1"=>"","Attribut_2"=>"","Attribut_3"=>"","Attribut_4"=>"","Attribut_5"=>"","Attribut_6"=>"","Volume"=>"0");

		/* Set attributes */
		if(is_array($attributesMap)){
			foreach($attributesMap as $attributKey => $attributeName) {
					$attAndFeatures[$attributKey] = $attributeName;
			}
		}

		/* Set features */
		if(is_array($featuresMap)){
			foreach($featuresMap as $key => $value) {
				$attAndFeatures[$key] = $value;
				$ProductElem->setAttribute($key, self::convertToUTF8($value));
			}
		}

		/* Add xml for attributes and features */
		$featNumeric = array("Volume");
		foreach($attAndFeatures as $key => $value) {
			if(in_array($key, $featNumeric)) {
				if(is_numeric($value)) {
					$ProductElem->setAttribute($key, $value);
				} else {
					$ProductElem->setAttribute($key, 0);
				}
			} else {
				$ProductElem->setAttribute($key, $value);
			}
		}

		/* Add width, height & lenght for Prestashop 1.4 */
		if(substr(_PS_VERSION_, 0, 3) > 1.3) {
			/* Prestashop 1.4 */
			$ProductElem->setAttribute('Hauteur', $product['height']);
			$ProductElem->setAttribute('Largeur', $product['width']);
			$ProductElem->setAttribute('Longueur', $product['depth']);
		}

		/* Hide main product (into OPENSI) when this product has child */
		if($isMainProduct && $product['has_child']) {
			$ProductElem->setAttribute('Actif', "false");
		} else {
			$ProductElem->setAttribute('Actif', "true");
		}

		$ProductElem->setAttribute('Date_M', Date::dateTimeBdd2DateTimeFr($product['product_date_upd']));
		$ProductElem->setAttribute('Date_C', Date::dateTimeBdd2DateTimeFr($product['product_date_add']));
		$request->appendChild($ProductElem);

		$docXml->appendChild($request);
		return $docXml->saveXML();
	}


	/*
	 * WSO_P011
	 * new order
	 * @param $order
	 * @param $orderDetails
	 */
	public static function createOrderXml($order, $orderDetails) {
		$docXml = self::createResponseXml();
		$request = $docXml->createElement("request");

		$OrderElem = $docXml->createElement("Commande_Web");

		/* Order node */
		$OrderElem->setAttribute('Num_Com_Web', $order['id_order']);
		$OrderElem->setAttribute('Code_Site_Web', GlobalConfig::getWebSiteCode());
		$OrderElem->setAttribute('Login', $order['id_customer']);
		$OrderElem->setAttribute('Date_Commande', Date::dateTimeBdd2DateTimeFr($order['date_add']));
		$OrderElem->setAttribute('Edition_TTC', "true");
		$OrderElem->setAttribute('Frais_Port', round($order['total_shipping'],2));
		if($order['carrier_tax_rate']!= null){
			$OrderElem->setAttribute('Taux_Tva_Port', round($order['carrier_tax_rate'],2));
		} else {
			$OrderElem->setAttribute('Taux_Tva_Port', 0);
		}

		if($order['total_discounts'] == 0){
			$discount = 0;
		} else {
			$discount = $order['total_discounts'];
		}
		$OrderElem->setAttribute('Remise_Montant', $discount);
		$OrderElem->setAttribute('Mode_Reglement', $order['payment']);
		if($order['carrier_name'] == "0"){
			$carrierName = "retrait au magasin";
		} else {
			$carrierName = $order['carrier_name'];
		}
		/* Get delivery mode So Colissimo */
		if(isset($order['delivery_mode']) != null) {
			$OrderElem->setAttribute('Mode_Expedition', $order['delivery_mode'].' - '.$carrierName);
		} else {
			$OrderElem->setAttribute('Mode_Expedition', $carrierName);
		}
		$OrderElem->setAttribute('Civ_Fact', 0);
		$OrderElem->setAttribute('Civ_Liv', 0);
		$OrderElem->setAttribute('Nom_Fact', $order['invoice_name']);
		$OrderElem->setAttribute('Prenom_Fact', $order['invoice_firstname']);
		$OrderElem->setAttribute('Societe_Fact', $order['invoice_company']);
		$OrderElem->setAttribute('Nom_Liv', $order['delivery_name']);
		$OrderElem->setAttribute('Prenom_Liv', $order['delivery_firstname']);

		/* So Colissimo : change delivery name, delivery firstname & society */
		if($order['delivery_mode'] == 'BPR' || $order['delivery_mode'] == 'ACP' || $order['delivery_mode'] == 'CIT' || $order['delivery_mode'] == 'A2P') {
			$OrderElem->setAttribute('Societe_Liv', $order['prcompladress']);
		} else {
			$OrderElem->setAttribute('Societe_Liv', $order['delivery_company']);
		}

		$OrderElem->setAttribute('Adresse_1_Fact', $order['invoice_address1']);
		$OrderElem->setAttribute('Adresse_1_Liv', $order['delivery_address1']);
		if($order['invoice_address2'] != "" && $order['invoice_address2'] != "NULL"){
			$OrderElem->setAttribute('Adresse_2_Fact', $order['invoice_address2']);
		}
		if($order['delivery_address2'] != "" && $order['delivery_address2'] != "NULL"){
			$OrderElem->setAttribute('Adresse_2_Liv', $order['delivery_address2']);
		}
		if($order['delivery_other'] != "" && $order['delivery_other'] != "NULL"){
			$OrderElem->setAttribute('Adresse_3_Liv', $order['delivery_other']);
		}
		$OrderElem->setAttribute('Code_Postal_Fact', $order['invoice_postcode']);
		$OrderElem->setAttribute('Code_Postal_Liv', $order['delivery_postcode']);
		$OrderElem->setAttribute('Ville_Fact', $order['invoice_city']);
		$OrderElem->setAttribute('Ville_Liv', $order['delivery_city']);
		if(isset($order['invoice_phone']) && $order['invoice_phone'] != null){
			$OrderElem->setAttribute('Tel_Fact', $order['invoice_phone']);
		} else if(isset($order['invoice_phone_mobile']) && $order['invoice_phone_mobile'] != "") {
			$OrderElem->setAttribute('Tel_Fact', $order['invoice_phone_mobile']);
		}
		/* Get phone from So Colissimo */
		if(isset($order['cephonenumber']) && $order['cephonenumber'] != null && $order['cephonenumber'] != '') {
			$OrderElem->setAttribute('Tel_Liv', $order['cephonenumber']);
		} else if(isset($order['delivery_phone']) && $order['delivery_phone'] != null){
			$OrderElem->setAttribute('Tel_Liv', $order['delivery_phone']);
		} else if(isset($order['delivery_phone_mobile']) && $order['delivery_phone_mobile'] != "") {
			$OrderElem->setAttribute('Tel_Liv', $order['delivery_phone_mobile']);
		}
		$OrderElem->setAttribute('Code_Pays_Fact', $order['invoice_iso_code']);
		$OrderElem->setAttribute('Code_Pays_Liv', $order['delivery_iso_code']);
		$OrderElem->setAttribute('Email_Fact', $order['customer_email']);

		/* Get email from So Colissimo */
		if(isset($order['ceemail']) && $order['ceemail'] != null && $order['ceemail'] != '') {
			$OrderElem->setAttribute('Email_Liv', $order['ceemail']);
		} else {
			$OrderElem->setAttribute('Email_Liv', $order['customer_email']);
		}

		$isGift = ($order['gift'] == 0)?("false"):("true");
		$OrderElem->setAttribute('Paquet_Cadeau', $isGift);
		$message = "";

		if($order['message'] != "") {
			$message .= $order['message'];
			$message = self::convertToUTF8($message);
		}
		if($order['gift_message'] != "") {
			if($order['message'] != "")
				$message .= "\n\n";
			$message .= utf8_encode("Message d'accompagnement à votre commande : ").$order['gift_message'];
			$message = $message;
		}

		$OrderElem->setAttribute('Commentaires', $message);

		/* Delivery infos So Colissimo */
		if(isset($order['cedoorcode1']) && isset($order['cedoorcode2']) && isset($order['cedeliveryinformation'])) {
			$delivery_info = '';
			if($order['cedoorcode1'] != null && $order['cedoorcode1'] != '')
				$delivery_info .= 'Code porte 1 : '.$order['cedoorcode1']."\n";
			if($order['cedoorcode2'] != null && $order['cedoorcode2'] != '')
				$delivery_info .= 'Code porte 2 : '.$order['cedoorcode2']."\n";
			if($order['cedeliveryinformation'] != null && $order['cedeliveryinformation'] != '')
				$delivery_info .= $order['cedeliveryinformation'];
			$OrderElem->setAttribute('Comp_Info_Liv', $delivery_info);
		}
		/* Delivery point So Colissimo */
		if(isset($order['prid']) && $order['prid'] != null && $order['prid'] != '')
			$OrderElem->setAttribute('Point_Retrait', $order['prid']);

		/* Details order nodes */
		foreach($orderDetails as $orderDetail) {
			$OrderDetailElem = $docXml->createElement("Ligne_Commande_Client");
			
			if($orderDetail['product_attribute_id'] != 0 && $orderDetail['attribute_reference'] != "" ){
				$OrderDetailElem->setAttribute('Reference', $orderDetail['attribute_reference']);
			} else {
				$OrderDetailElem->setAttribute('Reference', $orderDetail['reference']);
			}

			$description = $orderDetail['product_name'];
			$description = self::convertToUTF8($description);
			$OrderDetailElem->setAttribute('Designation', self::cut($description, 100));
			$OrderDetailElem->setAttribute('Quantite', $orderDetail['product_quantity']);
			$tax = round(floatval($orderDetail['tax_rate']) ,2);
			$OrderDetailElem->setAttribute('Taux_Tva', $tax);
			$priceUnit = round(floatval($orderDetail['product_price']) ,4);
			if($orderDetail['reduction_percent'] != 0){
				$priceUnit = $priceUnit - ($priceUnit * floatval($orderDetail['reduction_percent']) / 100);
				$priceUnit = round($priceUnit, 4);
			}
			if($orderDetail['reduction_amount'] != 0) {
				$reduction_amount = $orderDetail['reduction_amount'] / (1 + $tax / 100);
				$priceUnit = $priceUnit - floatval($reduction_amount);
				$priceUnit = round($priceUnit, 4);
			}
			if($orderDetail['group_reduction'] > 0) {
				// discount group of customer
				$priceUnit = $priceUnit * (1 - $orderDetail['group_reduction'] /100);
				$priceUnitTTC = round(floatval($priceUnit + ($priceUnit * $tax / 100)), 4);
			} else {
				$priceUnitTTC = round(floatval($priceUnit + ($priceUnit * $tax / 100)), 4);
			}
			$OrderDetailElem->setAttribute('Prix_Unitaire', $priceUnitTTC);
			$OrderDetailElem->setAttribute('Remise_Pourc', 0);
			$OrderElem->appendChild($OrderDetailElem);
		}

		$request->appendChild($OrderElem);
		$docXml->appendChild($request);
		return $docXml->saveXML();
	}


	/*
	 * WSO_P015
	 * create transaction web
	 * @param $product
	 */
	public static function createTransactionWebXml($order) {
		$docXml = self::createResponseXml();
		$request = $docXml->createElement("request");

		$invpermElem = $docXml->createElement("Transaction_Com_Web");
		$invpermElem->setAttribute('Code_Site_Web', GlobalConfig::getWebSiteCode());
		$invpermElem->setAttribute('Num_Com_Web', $order['id_order']);
		$invpermElem->setAttribute('Num_Transaction', $order['id_order']);
		$invpermElem->setAttribute('Mode_Reglement', $order['payment']);
		$invpermElem->setAttribute('Date_Transaction', Date::dateTimeBdd2DateTimeFr($order['date_add']));
		$invpermElem->setAttribute('Montant', round(floatval($order['total_paid_real']),2));

		$request->appendChild($invpermElem);
		$docXml->appendChild($request);
		return $docXml->saveXML();
	}


	/*
	 * WSO_P018
	 * create stock product
	 * @param $product
	 */
	public static function createStockProductXml($product) {
		$docXml = self::createResponseXml();
		$request = $docXml->createElement("request");

		$invpermElem = $docXml->createElement("Invperm_Article");
		$invpermElem->setAttribute('Code_Depot', GlobalConfig::getDepositCode());
		$invpermElem->setAttribute('Reference', $product['reference']);
		$invpermElem->setAttribute('Stock_Reel', $product['quantity']);

		$request->appendChild($invpermElem);
		$docXml->appendChild($request);
		return $docXml->saveXML();
	}


	/*
	 * WSO_P034
	 * create publication state article
	 * @param $product
	 */
	public static function createPublicationProductXml($product) {
		$docXml = self::createResponseXml();
		$request = $docXml->createElement("request");

		$publicationElem = $docXml->createElement("Pubweb_Article");
		$publicationElem->setAttribute('Code_Site_Web', GlobalConfig::getWebSiteCode());
		$publicationElem->setAttribute('Reference', $product['reference']);
		$isActivated = ($product['active'] == 1)?("true"):("false");
		$publicationElem->setAttribute('Publication', $isActivated);

		$request->appendChild($publicationElem);
		$docXml->appendChild($request);
		return $docXml->saveXML();
	}


	/*
	 * Delete HTML tags from a string
	 * @param $str is the string to cleen
	 */
	function convertToUTF8($str) {
		$enc = mb_detect_encoding($str);
		if ($enc && $enc != 'UTF-8') {
			return trim(strip_tags(html_entity_decode(utf8_encode($str), ENT_NOQUOTES, 'UTF-8')));
		} else {
			return trim(strip_tags(html_entity_decode($str, ENT_NOQUOTES, 'UTF-8')));
		}
	}


	/* 
	 * Cut too long string and delete HTML tags
	 * @param $str is the string to cleen
	 * @param $length
	 */
	private static function cut($str, $length){
		return substr($str, 0, $length);
	}

}