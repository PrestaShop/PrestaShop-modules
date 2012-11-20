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

class Dao{


	/* 
	 * =============================================  G E T    R E Q U E S T S  =============================================
	 */

	/*
	 * Get last orders between interval
	 * @param date $since
	 * @param date $to
	 * @param country $country (default 8 for FR)
	 */
	function getLastOrders($since, $to, $country=8){
		if(substr(_PS_VERSION_, 0, 3) > 1.3) {
			// Prestashop 1.4
			return Db::getInstance()->ExecuteS("SELECT o.*, a1.company as invoice_company, a2.company as delivery_company,
												a1.lastname as invoice_name, a2.lastname as delivery_name,	
												a1.firstname as invoice_firstname, a2.firstname as delivery_firstname,
												a1.address1 as invoice_address1, a2.address1 as delivery_address1,
												a1.address2 as invoice_address2, a2.address2 as delivery_address2,
												a1.postcode as invoice_postcode, a2.postcode as delivery_postcode,
												a1.city as invoice_city, a2.city as delivery_city,
												a1.phone as invoice_phone, a2.phone as delivery_phone,
												a1.phone_mobile as invoice_phone_mobile, a2.phone_mobile as delivery_phone_mobile,
												co1.iso_code as invoice_iso_code, co2.iso_code as delivery_iso_code,
												car.id_tax_rules_group as id_carrier_tax, tx.rate as carrier_tax_rate,
												msg.message as message, car.name as carrier_name,
												cust.email as customer_email
												FROM `"._DB_PREFIX_."address` as a1,  `"._DB_PREFIX_."address` as a2,
												`"._DB_PREFIX_."country` as co1, `"._DB_PREFIX_."country` as co2,
												`"._DB_PREFIX_."orders` as o
												LEFT JOIN `"._DB_PREFIX_."carrier` AS car ON car.id_carrier=o.id_carrier
												LEFT JOIN `"._DB_PREFIX_."tax_rule` AS tax ON tax.`id_tax_rules_group`=car.`id_tax_rules_group` and tax.`id_country` = \"".(int)$country."\"
												LEFT JOIN `"._DB_PREFIX_."tax` AS tx ON tx.id_tax = tax.id_tax
												LEFT JOIN `"._DB_PREFIX_."message` AS msg ON msg.id_order=o.id_order
												LEFT JOIN `"._DB_PREFIX_."customer` AS cust ON cust.id_customer=o.id_customer
												LEFT JOIN `"._DB_PREFIX_."opensi_order` as osi ON osi.id_order = o.id_order
												WHERE o.date_add>\"".pSQL($since)."\" AND o.date_add<=\"".pSQL($to)."\"
												AND a1.id_address = o.id_address_invoice AND a2.id_address = o.id_address_delivery 
												AND	co1.id_country = a1.id_country AND	co2.id_country = a2.id_country
												AND osi.id_order is null
												GROUP BY o.id_order
												");
		} else {
			// Prestashop 1.3
			return Db::getInstance()->ExecuteS("SELECT o.*, a1.company as invoice_company, a2.company as delivery_company,
												a1.lastname as invoice_name, a2.lastname as delivery_name,	
												a1.firstname as invoice_firstname, a2.firstname as delivery_firstname,
												a1.address1 as invoice_address1, a2.address1 as delivery_address1,
												a1.address2 as invoice_address2, a2.address2 as delivery_address2,
												a1.postcode as invoice_postcode, a2.postcode as delivery_postcode,
												a1.city as invoice_city, a2.city as delivery_city,
												a1.phone as invoice_phone, a2.phone as delivery_phone,
												a1.phone_mobile as invoice_phone_mobile, a2.phone_mobile as delivery_phone_mobile,
												co1.iso_code as invoice_iso_code, co2.iso_code as delivery_iso_code,
												car.id_tax as id_carrier_tax, tax.rate as carrier_tax_rate,
												msg.message as message, car.name as carrier_name,
												cust.email as customer_email
												FROM `"._DB_PREFIX_."address` as a1,  `"._DB_PREFIX_."address` as a2,
												`"._DB_PREFIX_."country` as co1, `"._DB_PREFIX_."country` as co2,
												`"._DB_PREFIX_."orders` as o
												LEFT JOIN `"._DB_PREFIX_."carrier` AS car ON car.id_carrier=o.id_carrier
												LEFT JOIN `"._DB_PREFIX_."tax` AS tax ON tax.id_tax=car.id_tax
												LEFT JOIN `"._DB_PREFIX_."message` AS msg ON msg.id_order=o.id_order
												LEFT JOIN `"._DB_PREFIX_."customer` AS cust ON cust.id_customer=o.id_customer
												LEFT JOIN `"._DB_PREFIX_."opensi_order` as osi ON osi.id_order = o.id_order
												WHERE o.date_add>\"".pSQL($since)."\" AND o.date_add<=\"".pSQL($to)."\"
												AND a1.id_address = o.id_address_invoice AND a2.id_address = o.id_address_delivery 
												AND	co1.id_country = a1.id_country AND	co2.id_country = a2.id_country
												AND osi.id_order is null
												GROUP BY o.id_order
												");
		}
	}


	/*
	 * Compatibility with So Colissimo
	 * Check if the table prefix_socolissimo_delivery_info exist
	 * Get last orders between interval
	 * @param date $since
	 * @param date $to
	 * @param country $country (default 8 for FR)
	 */
	function socolissimo_exists($db, $table) {
		$requete = 'SHOW TABLES FROM '.$db.' LIKE \''._DB_PREFIX_.'socolissimo_delivery_info\'';
		$exec = mysql_query($requete);
		return mysql_num_rows($exec);
	}

	function getLastOrdersSoColissimo($since, $to, $country=8){
		if(substr(_PS_VERSION_, 0, 3) > 1.3) {
			// Prestashop 1.4
			return Db::getInstance()->ExecuteS("SELECT o.*, a1.company as invoice_company, a2.company as delivery_company,
												a1.lastname as invoice_name, a2.lastname as delivery_name,	
												a1.firstname as invoice_firstname, a2.firstname as delivery_firstname,
												a1.address1 as invoice_address1, a2.address1 as delivery_address1,
												a1.address2 as invoice_address2, a2.address2 as delivery_address2,
												a2.other as delivery_other,
												a1.postcode as invoice_postcode, a2.postcode as delivery_postcode,
												a1.city as invoice_city, a2.city as delivery_city,
												a1.phone as invoice_phone, a2.phone as delivery_phone,
												a1.phone_mobile as invoice_phone_mobile, a2.phone_mobile as delivery_phone_mobile,
												co1.iso_code as invoice_iso_code, co2.iso_code as delivery_iso_code,
												car.id_tax_rules_group as id_carrier_tax, tx.rate as carrier_tax_rate,
												msg.message as message, car.name as carrier_name,
												car.name as carrier_name,
												cust.email as customer_email,
												socolissimo.delivery_mode, socolissimo.cedoorcode1, socolissimo.cedoorcode2, socolissimo.cedeliveryinformation, socolissimo.prid, socolissimo.cephonenumber, socolissimo.ceemail, socolissimo.prcompladress
												FROM `"._DB_PREFIX_."address` as a1,  `"._DB_PREFIX_."address` as a2,
												`"._DB_PREFIX_."country` as co1, `"._DB_PREFIX_."country` as co2,
												`"._DB_PREFIX_."orders` as o
												LEFT JOIN `"._DB_PREFIX_."carrier` AS car ON car.id_carrier=o.id_carrier
												LEFT JOIN `"._DB_PREFIX_."tax_rule` AS tax ON tax.`id_tax_rules_group`=car.`id_tax_rules_group` and tax.`id_country` = \"".(int)$country."\"
												LEFT JOIN `"._DB_PREFIX_."tax` AS tx ON tx.id_tax = tax.id_tax
												LEFT JOIN `"._DB_PREFIX_."message` AS msg ON msg.id_order=o.id_order
												LEFT JOIN `"._DB_PREFIX_."customer` AS cust ON cust.id_customer=o.id_customer
												LEFT JOIN `"._DB_PREFIX_."opensi_order` as osi ON osi.id_order = o.id_order
												LEFT JOIN `"._DB_PREFIX_."socolissimo_delivery_info` as socolissimo ON socolissimo.id_cart = o.Id_cart
												WHERE o.date_add>\"".pSQL($since)."\" AND o.date_add<=\"".pSQL($to)."\"
												AND a1.id_address = o.id_address_invoice AND a2.id_address = o.id_address_delivery 
												AND	co1.id_country = a1.id_country AND	co2.id_country = a2.id_country
												AND osi.id_order is null
												GROUP BY o.id_order
												");
		} else {
			// Prestashop 1.3
			return Db::getInstance()->ExecuteS("SELECT o.*, a1.company as invoice_company, a2.company as delivery_company,
												a1.lastname as invoice_name, a2.lastname as delivery_name,	
												a1.firstname as invoice_firstname, a2.firstname as delivery_firstname,
												a1.address1 as invoice_address1, a2.address1 as delivery_address1,
												a1.address2 as invoice_address2, a2.address2 as delivery_address2,
												a2.other as delivery_other,
												a1.postcode as invoice_postcode, a2.postcode as delivery_postcode,
												a1.city as invoice_city, a2.city as delivery_city,
												a1.phone as invoice_phone, a2.phone as delivery_phone,
												a1.phone_mobile as invoice_phone_mobile, a2.phone_mobile as delivery_phone_mobile,
												co1.iso_code as invoice_iso_code, co2.iso_code as delivery_iso_code,
												car.id_tax as id_carrier_tax, tax.rate as carrier_tax_rate,
												msg.message as message, car.name as carrier_name,
												car.name as carrier_name,
												cust.email as customer_email,
												socolissimo.delivery_mode, socolissimo.cedoorcode1, socolissimo.cedoorcode2, socolissimo.cedeliveryinformation, socolissimo.prid, socolissimo.cephonenumber, socolissimo.ceemail, socolissimo.prcompladress
												FROM `"._DB_PREFIX_."address` as a1,  `"._DB_PREFIX_."address` as a2,
												`"._DB_PREFIX_."country` as co1, `"._DB_PREFIX_."country` as co2,
												`"._DB_PREFIX_."orders` as o
												LEFT JOIN `"._DB_PREFIX_."carrier` AS car ON car.id_carrier=o.id_carrier
												LEFT JOIN `"._DB_PREFIX_."tax` AS tax ON tax.id_tax=car.id_tax
												LEFT JOIN `"._DB_PREFIX_."message` AS msg ON msg.id_order=o.id_order
												LEFT JOIN `"._DB_PREFIX_."customer` AS cust ON cust.id_customer=o.id_customer
												LEFT JOIN `"._DB_PREFIX_."opensi_order` as osi ON osi.id_order = o.id_order
												LEFT JOIN `"._DB_PREFIX_."socolissimo_delivery_info` as socolissimo ON socolissimo.id_cart = o.Id_cart
												WHERE o.date_add>\"".pSQL($since)."\" AND o.date_add<=\"".pSQL($to)."\"
												AND a1.id_address = o.id_address_invoice AND a2.id_address = o.id_address_delivery 
												AND	co1.id_country = a1.id_country AND	co2.id_country = a2.id_country
												AND osi.id_order is null
												GROUP BY o.id_order
												");
		}
	}


	/*
	 * Get details order
	 * @param $idOrder
	 */
	function getOrderDetails($idOrder, $langId=2){
		return Db::getInstance()->ExecuteS("SELECT p.id_supplier, pl.description_short, od.group_reduction, od.id_order_detail, od.id_order, od.product_id,
											od.product_name, od.product_quantity, od.product_price, od.product_quantity_discount, od.tax_rate,
											od.product_attribute_id, od.reduction_percent, od.reduction_amount, p.reference, pa.reference as attribute_reference
							    			FROM `"._DB_PREFIX_."product` AS p, `"._DB_PREFIX_."product_lang` AS pl, `"._DB_PREFIX_."order_detail` AS od
							    			LEFT JOIN `"._DB_PREFIX_."product_attribute` AS pa ON pa.id_product_attribute=od.product_attribute_id
											WHERE od.id_order=\"".(int)$idOrder."\" AND p.id_product=od.product_id AND pl.id_product = p.id_product
											AND pl.id_lang = \"".(int)$langId."\" 
											ORDER BY od.id_order_detail ASC");
	}


	/*
	 * Get last created or updated orders
	 * @param date $since
	 * @param date $to
	 */
	function getLastCreateOrUpdateOrders($since, $to){
		return Db::getInstance()->ExecuteS("SELECT distinct o.*
											FROM `"._DB_PREFIX_."orders` as o
											JOIN `"._DB_PREFIX_."order_detail` as art
											ON o.id_order=art.id_order
											LEFT JOIN `"._DB_PREFIX_."opensi_order` as osi
											ON osi.id_order = o.id_order
											WHERE ((o.date_add>\"".pSQL($since)."\" AND o.date_add<=\"".pSQL($to)."\") OR
											(o.date_upd>\"".pSQL($since)."\" AND o.date_upd<=\"".pSQL($to)."\"))
											AND osi.transaction = '0'
											AND osi.paid = '0'");
	}


	/*
	 * Get last inscription customers between interval
	 * @param $since
	 * @param $to
	 */
	function getLastCustomers($since, $to){
		return Db::getInstance()->ExecuteS("SELECT *, cu.lastname as customerLastname, cu.firstname as customerFirstname FROM `"._DB_PREFIX_."customer` AS cu, `"._DB_PREFIX_."address` AS a, `"._DB_PREFIX_."country` AS co
											WHERE cu.date_add>\"".pSQL($since)."\" AND cu.date_add<=\"".pSQL($to)."\" AND a.id_customer = cu.id_customer 
											AND a.id_country = co.id_country 
											GROUP BY cu.id_customer");
	}


	/*
	 * Get customers updated between interval 
	 * @param $since
	 * @param $to
	 */
	function getLastUpdateCustomers($since, $to){
		return Db::getInstance()->ExecuteS("SELECT *, cu.lastname as customerLastname, cu.firstname as customerFirstname FROM `"._DB_PREFIX_."customer` AS cu, `"._DB_PREFIX_."address` AS a, `"._DB_PREFIX_."country` AS co
											WHERE cu.date_upd>\"".pSQL($since)."\" AND cu.date_upd<=\"".pSQL($to)."\" AND cu.date_add<=\"".pSQL($since)."\"
											AND a.id_customer = cu.id_customer AND a.id_country = co.id_country 
											GROUP BY cu.id_customer");
	}


	/*
	 * Get last products created between interval
	 * @param $since
	 * @param $to
	 * @param $langId (default 2 for FR)
	 * @param $countryId (default 8 for FR)
	 */
	function getLastCreateProducts($since, $to, $langId=2, $countryId=8){
		if(substr(_PS_VERSION_, 0, 3) > 1.3) {
			// Prestashop 1.4
			return Db::getInstance()->ExecuteS("SELECT p.*, pl.*, t.*, tx.id_tax, tx.rate, m.name as manufacturer_name, p.date_add as product_date_add, p.date_upd as product_date_upd, pl.name as product_name
												FROM  `"._DB_PREFIX_."product_lang` AS pl, `"._DB_PREFIX_."product` AS p
												LEFT JOIN `"._DB_PREFIX_."manufacturer` AS m ON m.id_manufacturer = p.id_manufacturer
												LEFT JOIN `"._DB_PREFIX_."tax_rule` AS t ON t.id_tax_rules_group = p.id_tax_rules_group and t.id_country = \"".(int)$countryId."\"
												LEFT JOIN `"._DB_PREFIX_."tax` AS tx ON tx.id_tax = t.id_tax
												WHERE (p.date_add>\"".pSQL($since)."\" AND p.date_add<=\"".pSQL($to)."\") AND pl.id_product = p.id_product AND pl.id_lang = \"".(int)$langId."\"
												GROUP BY p.id_product");
		} else {
			// Prestashop 1.3
			return Db::getInstance()->ExecuteS("SELECT  *, m.name as manufacturer_name, p.date_add as product_date_add, p.date_upd as product_date_upd, pl.name as product_name
												FROM  `"._DB_PREFIX_."product_lang` AS pl, `"._DB_PREFIX_."product` AS p
												LEFT JOIN `"._DB_PREFIX_."manufacturer` AS m ON m.id_manufacturer = p.id_manufacturer
												LEFT JOIN `"._DB_PREFIX_."tax` AS t ON t.id_tax = p.id_tax
												WHERE (p.date_add>\"".pSQL($since)."\" AND p.date_add<=\"".pSQL($to)."\") AND pl.id_product = p.id_product AND pl.id_lang = \"".(int)$langId."\"
												GROUP BY p.id_product");
		}
	}


	/*
	 * Get last products updated between interval
	 * @param $since
	 * @param $to
	 * @param $langId (default 2 for FR)
	 * @param $countryId (default 8 for FR)
	 */
	function getLastUpdateProducts($since, $to, $langId=2, $countryId=8){
		if(substr(_PS_VERSION_, 0, 3) > 1.3) {
			// Prestashop 1.4
			return Db::getInstance()->ExecuteS("SELECT p.*, pl.*, t.*, tx.id_tax, tx.rate, m.name as manufacturer_name, p.date_add as product_date_add, p.date_upd as product_date_upd, pl.name as product_name
												FROM  `"._DB_PREFIX_."product_lang` AS pl, `"._DB_PREFIX_."product` AS p
												LEFT JOIN `"._DB_PREFIX_."manufacturer` AS m ON m.id_manufacturer = p.id_manufacturer
												LEFT JOIN `"._DB_PREFIX_."tax_rule` AS t ON t.id_tax_rules_group = p.id_tax_rules_group and t.id_country = \"".(int)$countryId."\"
												LEFT JOIN `"._DB_PREFIX_."tax` AS tx ON tx.id_tax = t.id_tax
												WHERE (p.date_upd>\"".pSQL($since)."\" AND p.date_upd<=\"".pSQL($to)."\") AND pl.id_product = p.id_product AND pl.id_lang = \"".(int)$langId."\"
												GROUP BY p.id_product");
		} else {
			// Prestashop 1.3
			return Db::getInstance()->ExecuteS("SELECT  *, m.name as manufacturer_name, p.date_add as product_date_add, p.date_upd as product_date_upd, pl.name as product_name
												FROM  `"._DB_PREFIX_."product_lang` AS pl, `"._DB_PREFIX_."product` AS p
												LEFT JOIN `"._DB_PREFIX_."manufacturer` AS m ON m.id_manufacturer = p.id_manufacturer
												LEFT JOIN `"._DB_PREFIX_."tax` AS t ON t.id_tax = p.id_tax
												WHERE (p.date_upd>\"".pSQL($since)."\" AND p.date_upd<=\"".pSQL($to)."\") AND pl.id_product = p.id_product AND pl.id_lang = \"".(int)$langId."\"
												GROUP BY p.id_product");
		}
	}


	/*
	 * Get attributes from a product
	 * @param $idProduct
	 * @param $langId (default 2 for FR)
	 */
	function getAttributesProduct($idProduct, $langId=2) {
		return Db::getInstance()->ExecuteS("SELECT al.name, pa.id_product, pa.id_product_attribute, pa.reference, pa.ean13, pa.price, pa.quantity, pa.weight,
											pa.wholesale_price, al.name as attribute_name, a.id_attribute_group
											FROM `"._DB_PREFIX_."attribute_lang` AS al,  `"._DB_PREFIX_."product_attribute` AS pa,
											`"._DB_PREFIX_."product_attribute_combination` AS pac, `"._DB_PREFIX_."attribute` a
											WHERE pa.id_product = \"".(int)$idProduct."\" AND pac.id_product_attribute = pa.id_product_attribute AND
											al.id_attribute = pac.id_attribute AND a.id_attribute=pac.id_attribute
											AND al.id_lang = \"".(int)$langId."\"");
	}


	/*
	 * Get categories from a product
	 * @param $idProduct
	 * @param $langId (default 2 for FR)
	 */
	function getCategoriesProduct($idProduct, $langId=2) {
		return Db::getInstance()->ExecuteS("SELECT fp.id_feature, fvl.value as feature_value
											FROM `"._DB_PREFIX_."feature_product` AS fp, `"._DB_PREFIX_."feature_value_lang` AS fvl
											WHERE fp.id_product = \"".(int)$idProduct."\" AND fvl.id_feature_value = fp.id_feature_value AND
											fvl.id_lang = \"".(int)$langId."\"");
	}


	/*
	 * Get OpenSi invoice
	 * @param $key
	 */
	function getInvoice($key) {
		return Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'opensi_invoice WHERE url_key = \''.pSQL($key).'\' LIMIT 1');
	}










	/* 
	 * =============================================  P O S T    R E Q U E S T S  =============================================
	 */

	/*
	 * Set the stock of a product
	 * @param $ref
	 * @param $quantity
	 */
	function setStock($ref, $quantity) {
		if($ref != ""){
			return Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET `quantity` = "'.intval($quantity).'" 
											 WHERE `reference` = "'.pSQL($ref).'"');
		}
	}


	/*
	 * Set the stock of a product attribute
	 * @param $ref
	 * @param $quantity
	 */
	function setStockAttributes($ref, $quantity) {
		if($ref != ""){
			return Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_attribute` SET `quantity` = "'.intval($quantity).'" 
											 WHERE `reference` = "'.pSQL($ref).'"');
		}
	}	


	/*
	 * Set the the order tracking code
	 * @param $ref
	 * @param $quantity
	 */
	function setOrderTrackingCode($idOrder, $trackingCode) {
		if($idOrder != ""){
			return Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'orders` SET `shipping_number` = "'.pSQL($trackingCode).'" 
											 WHERE `id_order` = "'.(int)$idOrder.'"');
		}
	}


	/*
	 * Set the state of an order
	 * @param $idOrder
	 * @param $idState
	 */
	function setState($idOrder, $idState, $date_add) {
		Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'order_history` 
									(`id_employee`, `id_order`, `id_order_state`, `date_add`) 
									VALUES ("0", "'.(int)$idOrder.'", "'.(int)$idState.'", "'.pSQL($date_add).'")');
		
	}


	/*
	 * Set the price of a product
	 * Prestashop 1.3 => $tax = id_tax
	 * Prestashop 1.4 => $tax = id_tax_rules_group
	 */
	function setPrice($ref, $priceHt, $purchaseHt, $tax) {
		if($ref != ""){
			if(substr(_PS_VERSION_, 0, 3) > 1.3) {
				// Prestashop 1.4
				return Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product`
													SET `price` = "'.floatval($priceHt).'", `id_tax_rules_group` = "'.pSQL($tax).'", `wholesale_price` = "'.floatval($purchaseHt).'"
													WHERE `reference` = "'.pSQL($ref).'"');
			} else {
				// Prestashop 1.3
				return Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product`
													SET `price` = "'.floatval($priceHt).'", `id_tax` = "'.pSQL($tax).'", `wholesale_price` = "'.floatval($purchaseHt).'"
													WHERE `reference` = "'.pSQL($ref).'"');
			}
		}
	}


	/*
	 * Set the price (impact) of a product attribute
	 * Prestashop 1.3 => $tax = id_tax
	 * Prestashop 1.4 => $tax = id_tax_rules_group
	 */
	function setPriceAttribute($ref, $priceTtc, $priceHt, $purchaseHt, $tax, $countryId=8) {
		if($ref != ""){
			if(substr(_PS_VERSION_, 0, 3) > 1.3) {
				// Prestashop 1.4
				return Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_attribute` as pa 
													SET pa.wholesale_price = "'.pSQL($purchaseHt).'", pa.price = ("'.pSQL($priceHt).'" - (SELECT p.price FROM `'._DB_PREFIX_.'product` as p WHERE pa.id_product = p.id_product))
													WHERE pa.reference = "'.pSQL($ref).'"');
			} else {
				// Prestashop 1.3
				return Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_attribute` as pa 
													SET pa.wholesale_price = "'.pSQL($purchaseHt).'", pa.price = ("'.pSQL($priceTtc).'" - (SELECT p.price  FROM `'._DB_PREFIX_.'product` as p WHERE pa.id_product = p.id_product) * (1 + (select rate from '._DB_PREFIX_.'tax where id_tax = "'.pSQL($tax).'")/100))
													WHERE pa.reference = "'.pSQL($ref).'"');
			}
		}
	}


	/*
	 * Set the product online
	 * @param $ref
	 * @param $isOnline
	 */
	function setProductOnline($ref, $isOnline) {
		if($ref != ""){
			return Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET `active` = "'.(int)(($isOnline)?(1):(0)).'"
											 WHERE `reference` = "'.pSQL($ref).'"');
		}
	}


	/*
	 * Set OpenSi invoices number
	 * @param $order
	 * @param $invoice_number
	 * @param $type (F = FACTURE, A + AVOIR)
	 */
	function setInvoiceNumber($service_id, $order, $invoice_number, $type) {
		$url_key = md5('https://webservices-test.opensi.eu/cows/Gateway?service_id='.$service_id.'&action=get_facture&facture_ref='.$invoice_number.'.pdf');
		Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'opensi_invoice` 
									(`id_order`, `number_invoice`, `type`, `url_key`, `date_synchro`) 
									VALUES ("'.pSQL($order).'", "'.pSQL($invoice_number).'", "'.pSQL($type).'", "'.pSQL($url_key).'", "'.pSQL(date('Y-m-d H:m:s')).'")');
	}










	/* 
	 * =============================================  O T H E R S    D A O   M E T H O D  =============================================
	 */

	/*
	 * Add new tax
	 * @param $rate
	 * @param $countryId (default 8 for FR)
	 */
	function addTax($rate, $countryId=8) {
		if(substr(_PS_VERSION_, 0, 3) > 1.3) {
			// Prestashop 1.4

			//add new tax
			Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'tax` (`rate`, `active`)
										VALUES ("'.pSQL($rate).'", "1")');

			// get tax rule group id
			$id_tax_rule_group = $this->getTaxRuleGroupId($rate, $countryId);

			// get tax id
			$id_tax = $this->getTaxId($rate);

			// tax_rules_group
			Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'tax_rules_group` (`name`, `active`)
										VALUES ("OpenSi ('.pSQL($rate).'%)", "1")');

			// tax_rule
			Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'tax_rule` (`id_tax_rules_group`, `id_country`, `id_state`, `id_county`, `id_tax`, `state_behavior`, `county_behavior`)
										VALUES ("'.(int)$id_tax_rule_group.'", "'.(int)$countryId.'", "0", "0", "'.(int)$id_tax.'", "0", "0")');
		} else {
			// Prestashop 1.3

			//add new tax
			Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'tax` (`rate`) VALUES ("'.pSQL($rate).'")');

			// get tax id
			$id_taxe = $this->getTaxId($rate);

			//add languages
			Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'tax_lang` (`id_tax`, `id_lang`, `name`)
										VALUES ("'.pSQL($id_taxe).'", "1", "tax '.pSQL($rate).'%")'); //en

			Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'tax_lang` (`id_tax`, `id_lang`, `name`)
										VALUES ("'.pSQL($id_taxe).'", "2", "taxe '.pSQL($rate).'%")'); //fr
		}
	}	


	/*
	 * Get Id of tax rule group
	 * @param unknown_type $rate, $countryId
	 */
	public function getTaxRuleGroupId($rate, $countryId=8) {
		$rate = Db::getInstance()->getRow("SELECT trule.id_tax_rules_group FROM  `"._DB_PREFIX_."tax` AS tax
											LEFT JOIN `"._DB_PREFIX_."tax_rule` AS trule ON tax.id_tax = trule.id_tax
											AND trule.id_country = \"".(int)$countryId."\"
											WHERE rate = \"".pSQL($rate)."\"");
		return $rate["id_tax_rules_group"];
	}


	/*
	 * Get Id of tax
	 * @param unknown_type $rate
	 */
	public function getTaxId($rate) {
		$rate = Db::getInstance()->getRow("SELECT * FROM  `"._DB_PREFIX_."tax`
											WHERE rate = \"".pSQL($rate)."\"");
		return $rate["id_tax"];
	}


	/*
	 * Is the state of the order already ok ?
	 */
	public function isAlwaysInThisState($idOrder, $idState) {
		$nb = Db::getInstance()->getRow("SELECT count(id_order_history) FROM  `"._DB_PREFIX_."order_history`
											WHERE id_order = \"".(int)$idOrder."\" AND id_order_state = \"".(int)$idState."\"");
		
		if($nb['count(id_order_history)'] <= 0){
			return false;
		} else {
			return true;
		}
	}


	/*
	 * Get the gift tax
	 */
	public function getGiftTax() {
		$rate = Db::getInstance()->getRow("SELECT tax.rate as gift_tax FROM  `"._DB_PREFIX_."configuration` as config
											LEFT JOIN `"._DB_PREFIX_."tax` AS tax ON tax.id_tax=config.value
											WHERE config.name = \"PS_GIFT_WRAPPING_TAX\"");
		return $rate["gift_tax"];
	}

}