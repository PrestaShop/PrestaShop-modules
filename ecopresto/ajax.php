<?php
/**
 *  NOTICE OF LICENSE
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

require dirname(__FILE__).'/../../config/config.inc.php';
require dirname(__FILE__).'/../../init.php';

require 'class/catalog.class.php';
$catalog = new Catalog();

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

switch ((int)Tools::getValue('majsel'))
{
	case 1:
		$catalog->setSelectProduct(Tools::getValue('ref'), Tools::getValue('etp'));
		echo Tools::safeOutput(Tools::getValue('tot')).','.Tools::safeOutput(Tools::getValue('actu')).','.Tools::safeOutput(count($catalog->tabSelectProduct));
		break;
	
	case 2:
		$catalog->updateCategory(Tools::getValue('rel'), Tools::getValue('cat'));
		break;
	
	case 3:
		$row_etp = Tools::getValue('etp');
		$row_ref = Tools::getValue('ref');
		$tentative = Tools::getValue('tentative');
		$fichier_Actu = (int)Tools::getValue('fichierActu');
		$nb_Fichier = (int)Tools::getValue('nbFichier');
		$lignes_Tot = Tools::getValue('lignesTot');
	
		if ($fichier_Actu == 0 && $row_etp == 0)
			$catalog->deleteData();
	
		$fichier = 'files/csv/'.$catalog->fichierImport.'.00'.$fichier_Actu;
	
		$time = time();
		$row = 0;
		$row_Max = 0;
		$insert_C = 0;
		$flag_Attr = false;
		$flaf_Cat = 0;
		$flag_Fin = 0;
	
		$requete_cata = 'INSERT INTO `'._DB_PREFIX_.'ec_ecopresto_catalog` (';
		foreach ($catalog->tabInsert as $key => $value)
			$requete_cata .= '`'.$key.'`'.',';
		$requete_cata = Tools::substr($requete_cata, 0, -1).') VALUES ';
	
		$requete_attr = 'INSERT INTO `'._DB_PREFIX_.'ec_ecopresto_catalog_attribute` (';
		foreach ($catalog->tabInsert_attribute as $key => $value)
			$requete_attr .= '`'.$key.'`'.',';
		$requete_attr .= '`reference`) VALUES ';
	
		if (($handle = fopen($fichier, 'r')) !== false)
		{
			$tab_Attributes = array();
			while (($data = fgetcsv($handle, 10000, ';')) !== false)
			{
				if ($row == 0)
					$row++;
				else
				{
					if ($row >= $row_etp)
						if (time() - $time <= $catalog->limitMax && $row_Max < $catalog->limitRowMax)
						{
							$flaf_Cat = 1;
							$row_Max++;
							$row++;
							$requete_C = array();
							$requete_A = array();
							$flag_Fin++;
	
							if ($data[13] != '')
							{
								$att = $data[13];
								$explode_Att = explode('|', $att);
								foreach ($explode_Att as $lst_Att)
								{
									list($name_, $val_) = explode(':', $lst_Att);
									$name[] = ltrim(trim($name_));
								}
								$catalog->tabAttributes = array_unique($name);
							}
	
							if ($data[40] != '')
								array_push($catalog->tabTVA, $data[40]);
	
							foreach ($catalog->tabInsert as $key => $value)
								$requete_C[] = '"'.pSQL((isset($data[$value])?ltrim(trim($data[$value])):'')).'"';
	
							foreach ($catalog->tabInsert_attribute as $key => $value)
								if ($data[11] != '' && $data[10] != $data[11])
									$requete_A[] = '"'.pSQL((isset($data[$value])?ltrim(trim($data[$value])):'')).'"';
	
								if (str_replace('"', '', $requete_C[15]) != $row_ref)
								{
									$requete_cata .= '('.implode(',', $requete_C).'), ';
									$insert_C++;
									$row_ref = str_replace('"', '', $requete_C[15]);
								}
	
							if ($data[11] != '' && $data[10] != $data[11])
							{
								$requete_attr .= '('.implode(',', $requete_A).', '.$requete_C[15].'), ';
								$flag_Attr = true;
							}
						}
					else
					{
						fclose($handle);
						break;
					}
					else
						$row++;
				}
			}
			if ($flag_Fin == 0)
			{
				$fichier_Actu++;
				if ($fichier_Actu < $nb_Fichier)
				{
					$row = 0;
					$tentative = 0;
				}
			}
			else
			{
				if ($insert_C > 0)
					$requete_cata = Tools::substr($requete_cata, 0, -2).'; ';
				$requete_attr = Tools::substr($requete_attr, 0, -2).';';
	
				$catalog->insertAttributes();
				$catalog->matchAttributes();
	
				$tabtva = $catalog->tabTVA;
				$catalog->tabTVA = array_unique($tabtva);
				$catalog->matchTax();
	
				if ($flaf_Cat == 1)
				{
					if (!$flag_Attr)
					{
						if ($catalog->insertData($requete_cata) === true)
						{
							Configuration::updateValue('EC_ECOPRESTO_LINES', (int)$row);
							$tentative = 0;
						}
						else
							$tentative++;
					}
					else
					{
						if ($catalog->insertData($requete_cata) === true && $catalog->insertData($requete_attr) === true)
						{
							Configuration::updateValue('EC_ECOPRESTO_LINES', (int)$row);
							$tentative = 0;
						}
						else
							$tentative++;
					}
				}
				else
					$tentative++;
			}
			$catalog->UpdateUpdateDate('DATE_IMPORT_ECO');
			echo Tools::safeOutput($row).','.Tools::safeOutput($row_ref).','.Tools::safeOutput($tentative).','.Tools::safeOutput($fichier_Actu).','.Tools::safeOutput($nb_Fichier).','.Tools::safeOutput($lignes_Tot);
		}
		break;
	
	case 4:
		$catalog->UpdateUpdateDate('DATE_IMPORT_PS');
		echo Tools::safeOutput(Tools::getValue('nb')).','.Tools::safeOutput($catalog->getTotalMAJ());
		break;
	
	case 5:
		$catalog->updateCatalog(Tools::getValue('ref'));
		echo Tools::safeOutput(Tools::getValue('actu')).','.Tools::safeOutput(Tools::getValue('tot'));
		break;
	
	case 6:
		$catalog->getProdDelete();
		echo Tools::safeOutput(Tools::getValue('actu')).','.Tools::safeOutput(Tools::getValue('tot'));
		break;
	
	case 7 :
		$catalog->updateCatalogAll();
		echo '1,1';
		break;
	
	case 8 :
		$return = $catalog->GetFilecsv();
		echo $return;
		break;
	
	case 9:
		$return = $catalog->GetDereferencement();
		echo $return;
		break;
	
	case 10:
		$catalog->synchroManuelOrder(Tools::getValue('idc'), Tools::getValue('typ'));
		break;
	
	case 11:
		echo $catalog->SetDerefencement();
		break;
	
	case 12:
		include dirname(__FILE__).'/class/importProduct.class.php';
		include dirname(__FILE__).'/class/reference.class.php';
		$import = new importerProduct();
		$catalog->UpdateDereferencement(Tools::getValue('ref'));
		$reference = new importerReference(Tools::getValue('ref'));
		$import->deleteProduct($reference->id_product, Tools::getValue('ref'));
		$import->deleteProductShop();
		echo Tools::safeOutput(Tools::getValue('actu')).','.Tools::safeOutput(Tools::getValue('tot'));
		break;
	
	case 13:
		include dirname(__FILE__).'/class/importProduct.class.php';
		$import = new importerProduct();
		$import->deleteProductShop();
		break;
	
	default:
		break;
}

?>