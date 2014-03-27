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

include dirname(__FILE__).'/../../config/config.inc.php';
include dirname(__FILE__).'/../../init.php';
include dirname(__FILE__).'/class/importProduct.class.php';
include dirname(__FILE__).'/class/reference.class.php';
include dirname(__FILE__).'/class/catalog.class.php';
include dirname(__FILE__).'/class/log.class.php';
$import = new importerProduct();
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

$time = time();
$etp = (int)Tools::getValue('etp');
$total = (int)Tools::getValue('total');
$typ = (int)Tools::getValue('typ');
$id_shop = $catalog->getInfoEco('ID_SHOP');
$id_lang = $catalog->getInfoEco('ID_LANG');

if ($catalog->tabConfig['UPDATE_PRODUCT'] == 1)
{
	$lst_Sup = Db::getInstance()->execute('SELECT `reference`
											FROM `'._DB_PREFIX_.'ec_ecopresto_product_deleted`
											WHERE status=0');
	if (isset($lst_Sup) && $lst_Sup[0])
	{
		$supp = array();
	
		foreach ($lst_Sup as $tab_Sup)
			$supp[] = '"'.pSQL($tab_Sup).'"';

		$supp = implode(',', $supp);
	}
	else
		$supp = '99999999999999999999';
}

if ($etp == 0)
{
	Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'ec_ecopresto_product_imported`');
	if ($catalog->tabConfig['UPDATE_PRODUCT'] == 0)
		Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'ec_ecopresto_product_imported` SELECT * FROM `'._DB_PREFIX_.'ec_ecopresto_product_shop` WHERE `id_shop`='.(int)$id_shop);
	else
		Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'ec_ecopresto_product_imported` SELECT * FROM `'._DB_PREFIX_.'ec_ecopresto_product_shop` WHERE `id_shop`='.(int)$id_shop.' AND `reference` NOT IN ('.$supp.')');
}


$maxR = (($total-$etp)>20?40:$total-$etp);

$lstPdt = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT *, c.`reference` AS thereference
											FROM `'._DB_PREFIX_.'ec_ecopresto_catalog` c, `'._DB_PREFIX_.'ec_ecopresto_product_shop` ps
											WHERE c.`reference` = ps.`reference`
											AND `id_shop`='.(int)$id_shop.'
											'.($catalog->tabConfig['UPDATE_PRODUCT']==1?' AND ps.`reference` NOT IN ('.$supp.')':'').'
											LIMIT '.(int)$etp.','.(int)$maxR);

$lstTax = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT `rate`, `id_tax_rules_group`
													FROM `'._DB_PREFIX_.'ec_ecopresto_tax_shop` ts, `'._DB_PREFIX_.'ec_ecopresto_tax` t
													WHERE ts.id_tax_eco = t.id_tax_eco
													AND `id_shop`='.(int)$id_shop);
foreach ($lstTax as $tax)
{
	$tabTax['id_tax'][$tax['rate']] = $tax['id_tax_rules_group'];
	$tabTax['rate'][$tax['rate']] = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `rate` FROM `'._DB_PREFIX_.'tax` WHERE id_tax='.(int)$tax['id_tax_rules_group']);
}

$lstLang = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT `id_lang_eco`, `id_lang`
													FROM `'._DB_PREFIX_.'ec_ecopresto_lang_shop`
													WHERE `id_shop`='.(int)$id_shop);

foreach ($lstLang as $lang)
	$tabLang[$lang['id_lang']] = $lang['id_lang_eco'];

$lstAttr = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT `value`, `id_attribute`
													FROM `'._DB_PREFIX_.'ec_ecopresto_attribute_shop` s, `'._DB_PREFIX_.'ec_ecopresto_attribute` a
													WHERE s.`id_attribute_eco` = a.`id_attribute_eco`
													AND `id_shop`='.(int)$id_shop);

foreach ($lstAttr as $Attr)
	$tabAtt[$Attr['value']] = $Attr['id_attribute'];

foreach ($lstPdt as $pdt)
{
	$pdt_final = array();
	if (time() - $time <= $catalog->limitMax)
	{
		$reference = new importerReference($pdt['thereference']);
		$pdt_final['id_product'] = (int)$reference->id_product;

		if ($pdt['imported'] == 1 && $pdt_final['id_product'])
		{
			$import->deleteProduct($reference->id_product, $pdt['thereference'] , $pdt['id_shop']);
			Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ec_ecopresto_product_imported` WHERE `reference`= "'.pSQL($pdt['thereference']).'"');
			$etp++;
		}
		elseif ($pdt['imported'] == 1 && !$reference->id_product)
		{
			Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ec_ecopresto_product_imported` WHERE `reference`= "'.pSQL($pdt['thereference']).'"');
			Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'ec_ecopresto_product_shop` SET `imported`=2 WHERE `reference`= "'.pSQL($pdt['thereference']).'" AND `id_shop`='.(int)$pdt['id_shop']);
			$etp++;
		}
		elseif ($reference->id_product && $catalog->tabConfig['PARAM_MAJ_NEWPRODUCT'] == 1 && !$reference->getShopProduct($pdt['id_shop'], $reference->id_product))
		{
			$etp++;
		}
		else
		{
			$tempRate = $pdt['rate']/1;
			$id_tax = (isset($tabTax['id_tax'][(string)$tempRate])?(int)$tabTax['id_tax'][(string)$tempRate]:0);

			$pdt_final['upd_index'] = (int)$catalog->tabConfig['PARAM_INDEX'];
			$pdt_final['upd_img'] = (int)$catalog->tabConfig['UPDATE_IMAGE'];
			$pdt_final['id_manufacturer'] = (int)$import->getManufacturer($pdt['manufacturer']);
			$pdt_final['id_shop_default'] = (int)$pdt['id_shop'];
			$pdt_final['shop'] = (int)$pdt['id_shop'];
			$idC = (int)$import->getCategory($pdt['category_1'], 0, $pdt['id_shop'], 0);
			$idSSC = (int)$import->getCategory($pdt['ss_category_1'], $idC, $pdt['id_shop'], 1);
			$pdt_final['categories'] = (int)$idC;
			$pdt_final['sscategories'] = (int)$idSSC;
			$pdt_final['id_category_default'] = (int)$idSSC;
			$pdt_final['id_supplier'] = (int)$catalog->tabConfig['PARAM_SUPPLIER'];

			$pdt_final['supplier_reference'] = (string)$pdt['thereference'];
			$pdt_final['date_upd'] = date('Y-m-d h:m:s');

			if ($catalog->tabConfig['UPDATE_EAN'] == 1)
				$pdt_final['ean13'] = (isset($pdt['ean13'])?(int)$pdt['ean13']:0);

			$pdt_final['weight'] = (isset($pdt['weight'])?(int)$pdt['weight']:0);

			if ($catalog->tabConfig['UPDATE_IMAGE'] || !$reference->id_product )
			{
				if ($pdt['image_1'])
					$pdt_final['images']['url'][] = (string)$pdt['image_1'];
				if ($pdt['image_2'])
					$pdt_final['images']['url'][] = (string)$pdt['image_2'];
				if ($pdt['image_3'])
					$pdt_final['images']['url'][] = (string)$pdt['image_3'];
				if ($pdt['image_4'])
					$pdt_final['images']['url'][] = (string)$pdt['image_4'];
				if ($pdt['image_5'])
					$pdt_final['images']['url'][] = (string)$pdt['image_5'];
				if ($pdt['image_6'])
					$pdt_final['images']['url'][] = (string)$pdt['image_6'];
			}

			foreach
			($tabLang as $langPS => $langEco)
				if ($catalog->tabConfig['UPDATE_NAME_DESCRIPTION'] == 1 || !$reference->id_product || !$reference->getShopProduct($pdt['id_shop'], $reference->id_product))
				{
					$pdt_final['description'][$langPS] = $pdt['description_'.$langEco];
					$pdt_final['description_short'][$langPS] = $import->tronkCar($pdt['description_short_'.$langEco]);
					$pdt_final['name'][$langPS] = $pdt['name_'.$langEco];
					$pdt_final['link_rewrite'][$langPS] = Tools::link_rewrite($pdt['name_'.$langEco]);
				}
			else
			{
				$resPS = Db::getInstance()->getRow('SELECT `name`, `link_rewrite` FROM `'._DB_PREFIX_.'product_lang` WHERE `id_product` = '.(int)$pdt_final['id_product'].' AND `id_lang` = '.(int)$langPS);
				$pdt_final['link_rewrite'][$langPS] = (isset($resPS['link_rewrite']) && $resPS['link_rewrite'] != ''?$resPS['link_rewrite']:Tools::link_rewrite($pdt['name_'.$langEco]));
				$pdt_final['name'][$langPS] = (isset($resPS['name']) && $resPS['name'] != ''?$resPS['name']:$pdt['name_'.$langEco]);
			}


			$pdt_final['majName'] = $catalog->tabConfig['UPDATE_NAME_DESCRIPTION'];

			if (!$reference->id_product)
			{
				$pdt_final['active'] = (int)$catalog->tabConfig['PARAM_NEWPRODUCT'];
				$pdt_final['indexed'] = (int)$catalog->tabConfig['PARAM_INDEX']; //moteur de recherche oui ou non
				$pdt_final['date_add'] = date('Y-m-d h:m:s');
				$pdt_final['price'] = (float)$pdt['pmvc'];
				$pdt_final['wholesale_price'] = (float)round(($catalog->tabConfig['PA_TAX'] == 0 && $id_tax != 0?$pdt['price']*(1+($tabTax['rate'][(string)$tempRate]/100)):$pdt['price']), 6);
				$pdt_final['reference'] = (string)$pdt['thereference'];

				if ($catalog->tabConfig['PMVC_TAX'] == 0)
					$pdt_final['id_tax_rules_group'] = (int)$id_tax;
				else
					$pdt_final['id_tax_rules_group'] = 0;


			}
			else
			{
				if ($catalog->tabConfig['UPDATE_PRICE'] == 1)
				{
					$pdt_final['wholesale_price'] = (float)round(($catalog->tabConfig['PA_TAX'] == 0 && $id_tax != 0?$pdt['price']*(1+($tabTax['rate'][(string)$tempRate]/100)):$pdt['price']), 6);
					$pdt_final['price'] = (float)$pdt['pmvc'];

					if ($catalog->tabConfig['PMVC_TAX'] == 0)
						$pdt_final['id_tax_rules_group'] = (int)$id_tax;
					else
						$pdt_final['id_tax_rules_group'] = 0;
				}
				else
					$pdt_final['price'] = Db::getInstance()->getValue('SELECT `price` FROM `'._DB_PREFIX_.'product` WHERE `supplier_reference`="'.pSQL($pdt['thereference']).'"');
			}

			$lstAtt = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT `reference_attribute`, `price`, `pmvc`, `ean13`, `weight`, `attribute_1`
													FROM `'._DB_PREFIX_.'ec_ecopresto_catalog_attribute`
													WHERE `reference` = "'.pSQL($pdt['thereference']).'"');
			$tem=0;

			foreach ($lstAtt as $att)
			{
				$explodeAtt = explode('|', $att['attribute_1']);
				$pdt_final_att2 = array();
				foreach ($explodeAtt as $lstExpAtt)
				{
					list($name_att, $val_att) = explode(':', $lstExpAtt);
					if (isset($tabAtt[trim($name_att)]))
						$idA = $tabAtt[trim($name_att)];
					else
						$idA = 0;

					$pdt_final_att2[] = array('id_attribute' => $import->getAttribute($idA, trim($val_att), trim($name_att), $pdt['id_shop'], $att['reference_attribute']));
					if ($idA == 0)
					{
						$lstAttr = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT `value`, `id_attribute`
																				FROM `'._DB_PREFIX_.'ec_ecopresto_attribute_shop` s, `'._DB_PREFIX_.'ec_ecopresto_attribute` a
																				WHERE s.`id_attribute_eco` = a.`id_attribute_eco`
																				AND `id_shop`='.(int)$id_shop);
						foreach ($lstAttr as $Attr)
							$tabAtt[$Attr['value']] = (int)$Attr['id_attribute'];
					}
				}

				if (($catalog->tabConfig['UPDATE_PRICE']==1 || !$reference->id_product) && ($reference->getShopProduct($pdt['id_shop'], $reference->id_product) == TRUE))
					$pdt_final_att[] = array('wholesale_price' => (float)round(($catalog->tabConfig['PMVC_TAX']==0&&$id_tax!=0?$att['price']*(1+($tabTax['rate'][(string)$tempRate]/100)):$att['price']), 6),
						'reference' => (string)$att['reference_attribute'],
						'supplier_reference' => (string)$att['reference_attribute'],
						'ean13' => (int)$att['ean13'],
						'price' => (float)($att['pmvc']- $pdt['pmvc']),
						'weight' => (float)($att['weight']-$pdt['weight']),
						'default_on' => ($tem==0?1:0),
						'id_supplier' => (int)$catalog->tabConfig['PARAM_SUPPLIER'],
						'id_shop' => (int)$pdt['id_shop'],
						'id_attribute' => $pdt_final_att2);
				else
					$pdt_final_att[] = array('wholesale_price' => (float)round(($catalog->tabConfig['PMVC_TAX']==0&&$id_tax!=0?$att['price']*(1+($tabTax['rate'][(string)$tempRate]/100)):$att['price']), 6),
						'reference' => (string)$att['reference_attribute'],
						'supplier_reference' => (string)$att['reference_attribute'],
						'ean13' => (int)$att['ean13'],
						'weight' => (float)($att['weight']-$pdt['weight']),
						'default_on' => ($tem==0?1:0),
						'id_supplier' => (int)$catalog->tabConfig['PARAM_SUPPLIER'],
						'id_shop' => (int)$pdt['id_shop'],
						'id_attribute' => $pdt_final_att2);
				$tem++;
			}

			$idP = $import->execImport($import->array_to_object($pdt_final));
			if ($tem > 0)
				$import->execImportAttribute($import->array_to_object($pdt_final_att), $idP);

			Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ec_ecopresto_product_imported` WHERE `reference`= "'.pSQL($pdt['thereference']).'"');
			$etp++;
		}
	}
	else
		break;
}

echo $etp.','.$total.','.$typ;
