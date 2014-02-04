<?php
/* NOTICE OF LICENSE
*
* This source file is subject to a commercial license from SARL Ether Création
* Use, copy, modification or distribution of this source file without written
* license agreement from the SARL Ether Création is strictly forbidden.
* In order to obtain a license, please contact us: contact@ethercreation.com
* ...........................................................................
* INFORMATION SUR LA LICENCE D'UTILISATION
*
* L'utilisation de ce fichier source est soumise a une licence commerciale
* concedee par la societe Ether Création
* Toute utilisation, reproduction, modification ou distribution du present
* fichier source sans contrat de licence ecrit de la part de la SARL Ether Création est
* expressement interdite.
* Pour obtenir une licence, veuillez contacter la SARL Ether Création a l'adresse: contact@ethercreation.com
* ...........................................................................
* @package ec_ecopresto
* @copyright Copyright (c) 2010-2013 S.A.R.L Ether Création (http://www.ethercreation.com)
* @author Arthur R.
* @license Commercial license
*/

include_once dirname(__FILE__).'/../../config/config.inc.php';
include_once dirname(__FILE__).'/../../init.php';
include_once dirname(__FILE__).'/class/send.class.php';
include_once dirname(__FILE__).'/class/catalog.class.php';

$catalog = new catalog();

if (Tools::getValue('ec_token') != $catalog->getInfoEco('ECO_TOKEN'))
{
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");

	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	header("Location: ../");
	exit;
}

if (isset($idcS) && $idcS != 0)
	$idc = $idcS;
else
	$idc = (Tools::getValue('idc')?Tools::getValue('idc'):0);

$server = $catalog->getInfoEco('ECO_URL_COM');

if ($catalog->tabConfig['IMPORT_AUTO'] == 1 || (isset($idcS) && $idcS != 0))
{
	$commande = $catalog->getOrders($idc);

	foreach ($commande as $com)
	{
		$resu = '<gen>';
		$reqExp = array();
		$TotCom = Db::getInstance()->getRow('SELECT SUM(`product_quantity`) AS SPQ, `tax_rate`, SUM(`product_price`) AS SPP, SUM(`product_quantity`*`product_price`) AS STT
						FROM `'._DB_PREFIX_.'order_detail` od
                                            	LEFT JOIN `'._DB_PREFIX_.'ec_ecopresto_catalog_attribute` ca ON (od.`product_supplier_reference` = ca.`reference_attribute`)
                                            	LEFT JOIN `'._DB_PREFIX_.'ec_ecopresto_catalog` c ON (od.`product_supplier_reference` = c.`reference`)
						WHERE `id_order`='.(int)$com['id_order'].'
                                            	GROUP BY od.`product_supplier_reference`');

		$ComRef = Db::getInstance()->ExecuteS('SELECT `product_quantity`, `product_id`, `tax_rate`, `product_price`, `id_order`, `product_supplier_reference`
						FROM `'._DB_PREFIX_.'order_detail` od
                                            	LEFT JOIN `'._DB_PREFIX_.'ec_ecopresto_catalog_attribute` ca ON (od.`product_supplier_reference` = ca.`reference_attribute`)
                                            	LEFT JOIN `'._DB_PREFIX_.'ec_ecopresto_catalog` c ON (od.`product_supplier_reference` = c.`reference`)
						WHERE `id_order`='.(int)$com['id_order'].'
                                            	GROUP BY od.`product_supplier_reference`');

		$tem = 0;
		foreach ($ComRef as $cr)
		{
			if ($tem == 0)
			{
				$resu .= '<export_order>';
				$resu .= '<info_order>';
				$resu .= '<password><![CDATA['.Tools::safeOutput($catalog->tabConfig['ID_ECOPRESTO']).']]></password>';
				$resu .= '<domain><![CDATA['.Tools::safeOutput(Configuration::get('PS_SHOP_DOMAIN')).']]></domain>';
				$resu .= '<shop_name><![CDATA['.Tools::safeOutput(Configuration::get('PS_SHOP_NAME')).']]></shop_name>';
				$resu .= '<addr_ecom_1><![CDATA['.Tools::safeOutput(Configuration::get('PS_SHOP_ADDR1')).']]></addr_ecom_1>';
				$resu .= '<addr_ecom_2><![CDATA['.Tools::safeOutput(Configuration::get('PS_SHOP_ADDR2')).']]></addr_ecom_2>';
				$resu .= '<zip_ecom><![CDATA['.Tools::safeOutput(Configuration::get('PS_SHOP_CODE')).']]></zip_ecom>';
				$resu .= '<city_ecom><![CDATA['.Tools::safeOutput(Configuration::get('PS_SHOP_CITY')).']]></city_ecom>';
				$resu .= '<phone_ecom><![CDATA['.Tools::safeOutput(Configuration::get('PS_SHOP_PHONE')).']]></phone_ecom>';
				$resu .= '<email_ecom><![CDATA['.Tools::safeOutput(Configuration::get('PS_SHOP_EMAIL')).']]></email_ecom>';
				$resu .= '</info_order>';

				$resu .= '<customer>';
				$adC = Db::getInstance()->getRow('SELECT a.`company`, a.`lastname`, a.`firstname`, a.`address1`, a.`address2`, a.`postcode`, a.`city`, a.`id_country`, a.`phone`, c.`email`
													 FROM `'._DB_PREFIX_.'address` a, `'._DB_PREFIX_.'customer` c
													 WHERE `id_address`='.(int)$com['id_address_delivery'].'
													 AND c.`id_customer` = a.`id_customer`');
				$resu .= '<custom_company><![CDATA['.Tools::safeOutput($adC['company']).']]></custom_company>';
				$resu .= '<custom_first_name><![CDATA['.Tools::safeOutput($adC['firstname']).']]></custom_first_name>';
				$resu .= '<custom_last_name><![CDATA['.Tools::safeOutput($adC['lastname']).']]></custom_last_name>';
				$resu .= '<custom_addr_1><![CDATA['.Tools::safeOutput($adC['address1']).']]></custom_addr_1>';
				$resu .= '<custom_addr_2><![CDATA['.Tools::safeOutput($adC['address2']).']]></custom_addr_2>';
				$resu .= '<custom_zip><![CDATA['.Tools::safeOutput($adC['postcode']).']]></custom_zip>';
				$resu .= '<custom_city><![CDATA['.Tools::safeOutput($adC['city']).']]></custom_city>';
				$resu .= '<custom_country><![CDATA['.Country::getIsoById($adC['id_country']).']]></custom_country>';
				$resu .= '<custom_phone><![CDATA['.Tools::safeOutput($adC['phone']).']]></custom_phone>';
				$resu .= '<custom_mail><![CDATA['.Tools::safeOutput($adC['email']).']]></custom_mail>';
				$resu .= '</customer>';

				$resu .= '<order_head>';
				$resu .= '<idc><![CDATA['.Tools::safeOutput($com['id_order']).']]></idc>';
				$resu .= '<date><![CDATA['.Tools::safeOutput($com['DatI']).']]></date>';
				$resu .= '<tot_ht><![CDATA['.Tools::safeOutput($TotCom['STT']).']]></tot_ht>';
				$resu .= '<tot_tva><![CDATA['.Tools::safeOutput($TotCom['STT']*($TotCom['tax_rate']/100)).']]></tot_tva>';
				$resu .= '<tot_ttc><![CDATA['.Tools::safeOutput(($TotCom['STT']+($TotCom['STT']*$TotCom['tax_rate']/100))).']]></tot_ttc>';
				$resu .= '</order_head>';
				$resu .= '<all_detail>';
			}
			$tem=1;
			$resu .= '<order_item>';
			$resu .= '<sku><![CDATA['.Tools::safeOutput($cr['product_supplier_reference']).']]></sku>';
			$resu .= '<product_name></product_name>';
			$resu .= '<qty><![CDATA['.Tools::safeOutput($cr['product_quantity']).']]></qty>';
			$resu .= '</order_item>';

		}
		if ($tem>0)
			$resu .= '</all_detail></export_order>';

		$reqExp[] = 'INSERT INTO `'._DB_PREFIX_.'ec_ecopresto_export_com` (`id`,`id_order`) VALUES ("",'.(int)$com['id_order'].')';


		$resu .= '</gen>';

		if (isset($reqExp) && count($reqExp) > 0)
		{
			$send = new sendEco();
			$log = $send->sendInfo($server, $resu);
			if ($log == 1)
				foreach ($reqExp as $req)
					Db::getInstance()->execute($req);
		}
	}
}

$catalog->UpdateUpdateDate('DATE_ORDER');
$catalog->getInfoPdt();
