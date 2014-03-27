<?php
/** NOTICE OF LICENSE
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

if (!defined('_PS_VERSION_'))
	exit;

class importerProduct
{
	public static function getManufacturer($manufacturer)
	{
		$idManufacturer = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT id_manufacturer FROM `'._DB_PREFIX_.'manufacturer` WHERE `name`="'.pSQL($manufacturer).'"');
		
		if ($idManufacturer)
			return $idManufacturer;
		else
		{
			Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'manufacturer` (`name`,`date_add`,`date_upd`,`active`) VALUES ("'.pSQL($manufacturer).'","'.pSQL(date('Y-m-d H:i:s')).'","'.pSQL(date('Y-m-d H:i:s')).'",1)');

			$idManufacturer = Db::getInstance()->Insert_ID();

			$all_lang = Language::getLanguages(true);

			foreach ($all_lang as $lang)
				Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'manufacturer_lang` (`id_manufacturer`,`id_lang`) VALUES ('.(int)($idManufacturer).','.(int)$lang['id_lang'].')');

			if (version_compare(_PS_VERSION_, '1.5', '>='))
			{
				$all_shop = Shop::getShops(false);

				foreach ($all_shop as $shop)
					Db::getInstance()->insert('manufacturer_shop', array(
							'id_manufacturer' => (int)($idManufacturer),
							'id_shop'=>(int)$shop['id_shop']));
			}
			return $idManufacturer;
		}
	}

	public function deleteProduct($idP, $ref)
	{
		$product = new Product($idP);
		$product->delete();
		self::deleteAttributePdt($ref);
		Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'ec_ecopresto_product_shop` SET `imported`=2 WHERE `reference`="'.pSQL($ref).'" AND `id_shop`='.(int)self::getInfoEco('ID_SHOP'));
		
		if (Db::getInstance()->execute('SELECT `reference` FROM `'._DB_PREFIX_.'ec_ecopresto_product_deleted` WHERE `reference`="'.pSQL($ref).'"'))
			Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'ec_ecopresto_product_deleted` SET status=1 WHERE `reference`="'.pSQL($ref).'"');
	}

	public static function getInfoEco($name)
	{
		return Db::getInstance()->getValue('SELECT `value` FROM `'._DB_PREFIX_.'ec_ecopresto_info` WHERE name="'.pSQL($name).'"');
	}

	public function deleteProductShop()
	{
		Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ec_ecopresto_product_shop` WHERE `imported`=2');
	}

	public static function getIdLangEcoCateg($name, $id_lang, $id_shop, $type)
	{
		$id_lang_eco = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_lang_eco`
											FROM `'._DB_PREFIX_.'ec_ecopresto_lang_shop`
											WHERE `id_shop` = '.(int)$id_shop.'
											AND `id_lang` = '.(int)$id_lang);
		if (!isset($id_lang_eco) || $id_lang_eco == '')
			$id_lang_eco = 1;
		
		if ($type == 1)
			$champ = 'ss_category_';
		else
			$champ = 'category_';

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `'.$champ.$id_lang_eco.'`
																FROM `'. _DB_PREFIX_.'ec_ecopresto_catalog`
																WHERE `'.$champ.'1` = "'.pSQL($name).'"');
	}

	public static function getCategory($categ, $categParent=0, $idShop, $type)
	{
		$idCategory = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_category`
																FROM `'._DB_PREFIX_.'ec_ecopresto_category_shop`
																WHERE `name` = "'.pSQL(base64_encode($categ)).'"
																AND `id_shop` = '.(int)self::getInfoEco('ID_SHOP'));

		if (!$idCategory)
		{
			if ($categParent == 0)
			{
				if (version_compare(_PS_VERSION_, '1.5', '>='))
					$categParent = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_category`
															FROM `'._DB_PREFIX_.'category`
															WHERE `is_root_category` = 1');
				else
					$categParent = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_category`
															FROM `'._DB_PREFIX_.'category`
															WHERE `id_parent` = 0');
				$level = 1;
			}
			else
			{
				$categLevel = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `level_depth`
															FROM `'._DB_PREFIX_.'category`
															WHERE `id_category` = '.(int)$categParent);
				$level = $categLevel + 1;
			}

			$posCateg = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT max(`position`)
														FROM `'._DB_PREFIX_.'category`
														WHERE `id_parent` = '.(int)$categParent);

			if (version_compare(_PS_VERSION_, '1.4', '>=') && version_compare(_PS_VERSION_, '1.5', '<'))
				Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'category` (`id_parent`,`active`,`level_depth`,`date_add`,`date_upd`,`position`)
					VALUES ('.(int)$categParent.',1,'.(int)$level.',"'.pSQL(date("Y-m-d H:i:s")).'","'.pSQL(date("Y-m-d H:i:s")).'",'.(int)($posCateg+1).')');
			else
				Db::getInstance()->insert('category', array(
					'id_parent' => (int)$categParent,
					'id_shop_default' => (int)$idShop,
					'active'=>1,
					'level_depth'=>(int)$level,
					'date_add'=>pSQL(date('Y-m-d H:i:s')),
					'date_upd'=>pSQL(date('Y-m-d H:i:s')),
					'position'=>(int)$posCateg+1));

			$idCategory = Db::getInstance()->Insert_ID();
			$all_lang = Language::getLanguages(true);

			foreach ($all_lang as $lang)
				if (version_compare(_PS_VERSION_, '1.5', '>='))
					Db::getInstance()->insert('category_lang', array(
							'id_category'=>(int)$idCategory,
							'id_shop'=>(int)($idShop),
							'id_lang'=>(int)$lang['id_lang'],
							'name'=>pSQL(self::getIdLangEcoCateg($categ, $lang['id_lang'], $idShop, $type)),
							'link_rewrite'=>Tools::str2url(self::getIdLangEcoCateg($categ, $lang['id_lang'], $idShop, $type))));
				else
					Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'category_lang` (`id_category`,`id_lang`,`name`,`link_rewrite`)
									VALUES ('.(int)$idCategory.','.(int)$lang['id_lang'].',"'.pSQL(self::getIdLangEcoCateg($categ, $lang['id_lang'], $idShop, $type)).'","'.Tools::str2url(self::getIdLangEcoCateg($categ, $lang['id_lang'], $idShop, $type)).'")');


				if (version_compare(_PS_VERSION_, '1.5', '>='))
				{
					$posCategShop = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT max(`position`)
						FROM `'._DB_PREFIX_.'category_shop`
						WHERE `id_shop`='.(int)$idShop);

					Db::getInstance()->insert('category_shop', array(
						'id_category' => (int)$idCategory,
						'id_shop'=>(int)$idShop,
						'position'=>(int)$posCategShop+1));
				}

			$all_group = Group::getGroups(self::getInfoEco('ID_LANG'));
			
			foreach ($all_group as $group)
				Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'category_group` (`id_category`,`id_group`) VALUES ('.(int)$idCategory.','.(int)$group['id_group'].')');

			if (version_compare(_PS_VERSION_, '1.5', '>='))
				Db::getInstance()->insert('ec_ecopresto_category_shop', array(
					'name'=>pSQL(base64_encode($categ)),
					'id_category'=>(int)$idCategory,
					'id_shop'=>(int)$idShop));
			else
				Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'ec_ecopresto_category_shop` (`name`,`id_category`,`id_shop`)
					VALUES ("'.pSQL(base64_encode($categ)).'",'.(int)$idCategory.','.(int)$idShop.')');

			Category::regenerateEntireNtree();
		}

		return $idCategory;
	}


	public static function tronkCar($string)
	{
		$lg_max = Configuration::get('PS_PRODUCT_SHORT_DESC_LIMIT');
		if (isset($lg_max) && Tools::strlen($string) > $lg_max && $lg_max > 0)
		{
			$chaine = Tools::substr($string, 0, $lg_max);
			$last_space = strrpos($chaine, " ");
			return Tools::substr($chaine, 0, $last_space).'...';
		}
		else
			return $string;
	}

	public function array_to_object($array)
	{
		$object = new stdClass();
		
		foreach ($array as $key => $value)
		{
			if (is_array($value))
				$object->$key = self::array_to_object($value);
			else
				$object->$key = $value;
		}
		
		return $object;
	}

	public static function execImport($pdt)
	{
		if ($pdt->id_product>0)
			$product = new Product($pdt->id_product);
		else
			$product = new Product();

		if (version_compare(_PS_VERSION_, '1.5', '>='))
		{
			foreach (Product::$definition['fields'] as $field => $value)
			{
				if (isset($pdt->$field))
					if (is_object($pdt->$field))
					{
						$fields = array();
						foreach ($pdt->$field as $key => $value)
							$fields[$key] = $value;
						$product->$field = $fields;
					}
				else
					$product->$field = $pdt->$field;
			}
		}
		else
		{
			$product->id_manufacturer = $pdt->id_manufacturer;
			$product->id_supplier = $pdt->id_supplier;

			if ($pdt->reference)
				$product->reference = $pdt->reference;

			$product->supplier_reference = $pdt->supplier_reference;
			$product->weight = $pdt->weight;
			$product->id_category_default = $pdt->id_category_default;

			if ($pdt->id_tax_rules_group)
				$product->id_tax_rules_group = $pdt->id_tax_rules_group;

			if ($pdt->price)
				$product->price = $pdt->price;

			if ($pdt->wholesale_price)
				$product->wholesale_price = $pdt->wholesale_price;

			if ($pdt->active)
				$product->active = $pdt->active;

			if ($pdt->date_add)
				$product->date_add = $pdt->date_add;

			$product->date_upd = $pdt->date_upd;

			if ($pdt->link_rewrite)
				foreach ($pdt->link_rewrite as $key => $value)
					$product->link_rewrite[$key] = $value;

			if ($pdt->name)
				foreach ($pdt->name as $key => $value)
					$product->name[$key] = $value;

			if ($pdt->description)
				foreach ($pdt->description as $key => $value)
					$product->description[$key] = $value;

			if ($pdt->description_short)
				foreach ($pdt->description_short as $key => $value)
					$product->description_short[$key] = $value;

			if ($pdt->ean13)
				$product->ean13 = $pdt->ean13;
		}

		if ($pdt->upd_index == 1)
			$product->indexed = 1;

		if (version_compare(_PS_VERSION_, '1.5', '>='))
			$product->id_shop_list[] = $pdt->shop;

		$product->id_category[] = $pdt->categories;
		$product->id_category[] = $pdt->sscategories;

		if ($pdt->id_product)
		{
			$category_data = Product::getProductCategories((int)$product->id);
			foreach ($category_data as $tmp)
				$product->id_category[] = $tmp;
		}
		
		$product->id_category = array_unique($product->id_category);

		$product->save();

		if ($pdt->upd_img == 1 || !$pdt->id_product)
		{
			self::execImages($pdt, $product);
			self::cleanUploadedImages($pdt, $product);
		}

		if (version_compare(_PS_VERSION_, '1.5', '>='))
			if (!$pdt->id_product)
				self::insertSupplierRef($product->id, 0, $pdt->id_supplier, $pdt->supplier_reference);

		if ($pdt->upd_index == 1)
			Search::indexation(false, $pdt->id_product);

		$product->updateCategories(array_map('intval', $product->id_category));

		return $product->id;
	}

	public static function insertSupplierRef($idP, $idA, $idS, $ref)
	{
		Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'product_supplier` (`id_product`,`id_product_attribute`,`id_supplier`,`product_supplier_reference`)
						VALUES ('.(int)$idP.','.(int)$idA.','.(int)$idS.',"'.pSQL($ref).'")');
	}

	public static function getIdLangEcoAttr($name, $id_lang, $id_shop)
	{
		$id_lang_eco = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_lang_eco`
			FROM `'._DB_PREFIX_.'ec_ecopresto_lang_shop`
			WHERE `id_shop` = '.(int)$id_shop.'
			AND `id_lang` = '.(int)$id_lang);
		if ($attLang = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `value`
				FROM `'._DB_PREFIX_.'ec_ecopresto_attribute`
				WHERE `value` = "'.pSQL($name).'"
				AND `id_lang` = '.(int)$id_lang_eco))
			return $attLang;
		else
			return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `value`
				FROM `'._DB_PREFIX_.'ec_ecopresto_attribute`
				WHERE `value` = "'.pSQL($name).'"
				AND `id_lang` = 1');

	}

	public static function getIdLangEcoAttrName($name, $id_lang, $id_shop, $ref)
	{
		$id_lang_eco = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_lang_eco`
			FROM `'._DB_PREFIX_.'ec_ecopresto_lang_shop`
			WHERE `id_shop` = '.(int)$id_shop.'
			AND `id_lang` = '.(int)$id_lang);

		if (!isset($id_lang_eco) || $id_lang_eco == 0)
			$id_lang_eco = 1;

		$resATT = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('SELECT `attribute_1`, `attribute_'.$id_lang_eco.'`
			FROM `'._DB_PREFIX_.'ec_ecopresto_catalog_attribute`
			WHERE `reference_attribute` = "'.pSQL($ref).'"');

		$explodeAtt = explode('|', $resATT['attribute_1']);
		$i = 0;
		
		foreach ($explodeAtt as $lstExpAtt)
		{
			if ($lstExpAtt)
			{
				list($name_att, $val_att) = explode(':', $lstExpAtt);
				if (trim($name_att) == trim($name))
				{
					$explodeAtt2 = explode('|', $resATT['attribute_'.$id_lang_eco]);
					$lol = 0;
					
					foreach ($explodeAtt2 as $lstExpAtt2)
						if ($lstExpAtt2)
						{
							if ($lol == $i)
							{
								list($nn, $vv) = explode(':', $lstExpAtt2);
								return trim($nn);
							}
							$lol++;
						}
				}
				$i++;
			}
		}
		
		return $name;
	}

	public function getAttribute($id_group_attribute, $value, $name, $idShop, $ref)
	{
		if ($id_group_attribute > 0)
		{
			$id_attribute = Db::getInstance()->getValue('SELECT a.`id_attribute`
				FROM `'._DB_PREFIX_.'attribute_lang` al, `'._DB_PREFIX_.'attribute` a
				WHERE al.`id_attribute` = a.`id_attribute`
				AND name="'.pSQL($value).'"
				AND id_attribute_group='.(int)$id_group_attribute);

			if ($id_attribute > 0)
				return $id_attribute;
			else
				return self::createAttributeValue($id_group_attribute, $value, $idShop, $name, $ref);
		}
		else
		{
			if (version_compare(_PS_VERSION_, '1.5', '>='))
			{
				$maxAttrGroup = Db::getInstance()->getValue('SELECT MAX(`position`) FROM `'._DB_PREFIX_.'attribute_group`');
				Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'attribute_group` (`position`) VALUES ('.(int)($maxAttrGroup+1).')');
			}
			else
				Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'attribute_group` (`id_attribute_group`) VALUES ("")');

			$id_attr_group = Db::getInstance()->Insert_ID();
			$all_lang = Language::getLanguages(true);

			foreach ($all_lang as $lang)
			{
				$nameF = self::getIdLangEcoAttrName($name, $lang['id_lang'], $idShop, $ref);
				Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'attribute_group_lang` (`id_attribute_group`,`id_lang`,`name`,`public_name`)
					VALUES ("'.(int)($id_attr_group).'","'.(int)$lang['id_lang'].'","'.pSQL($nameF).'","'.pSQL($nameF).'")');
			}

			if (version_compare(_PS_VERSION_, '1.5', '>='))
			{
				$all_shop = Shop::getShops(false);
				foreach ($all_shop as $shop)
					Db::getInstance()->insert('attribute_group_shop', array(
						'id_attribute_group' => (int)($id_attr_group),
						'id_shop'=>(int)$shop['id_shop']));
			}

			return self::createAttributeValue($id_attr_group, $value, $idShop, $name, $ref);
		}
	}

	public static function getIdLangEcoAttrValue($name, $id_lang, $id_shop, $value, $ref)
	{

		$id_lang_eco = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_lang_eco`
			FROM `'._DB_PREFIX_.'ec_ecopresto_lang_shop`
			WHERE `id_shop` = '.(int)$id_shop.'
			AND `id_lang` = '.(int)$id_lang);

		if (!isset($id_lang_eco) || $id_lang_eco == 0)
			$id_lang_eco = 1;

		$resATT = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('SELECT `attribute_1`, `attribute_'.$id_lang_eco.'`
			FROM `'._DB_PREFIX_.'ec_ecopresto_catalog_attribute`
			WHERE `reference_attribute` = "'.pSQL($ref).'"');

		$explodeAtt = explode('|', $resATT['attribute_1']);
		$i = 0;
		
		foreach ($explodeAtt as $lstExpAtt)
		{
			if ($lstExpAtt)
			{
				list($name_att, $val_att) = explode(':', $lstExpAtt);
				if (trim($name_att) == trim($name) && trim($val_att) == trim($value))
				{
					$explodeAtt2 = explode('|', $resATT['attribute_'.$id_lang_eco]);
					$lol = 0;
					foreach ($explodeAtt2 as $lstExpAtt2)
					{
						if ($lstExpAtt2)
						{
							if ($lol == $i)
							{
								list($nn, $vv) = explode(':', $lstExpAtt2);
								return trim($vv);
							}
							$lol++;
						}
					}
				}
				$i++;
			}
		}
		return $value;
	}

	public function createAttributeValue($id_attr_group, $value, $idShop, $name, $ref)
	{
		if (version_compare(_PS_VERSION_, '1.5', '>='))
		{
			$maxAttr = Db::getInstance()->getValue('SELECT MAX(`position`) FROM `'._DB_PREFIX_.'attribute`');
			Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'attribute` (`id_attribute_group`,`position`) VALUES ('.(int)$id_attr_group.','.(int)$maxAttr.')');
		}
		else
			Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'attribute` (`id_attribute_group`) VALUES ('.(int)$id_attr_group.')');

		$id_attr = Db::getInstance()->Insert_ID();
		$all_lang = Language::getLanguages(true);

		foreach ($all_lang as $lang)
		{
			$value = self::getIdLangEcoAttrValue($name, $lang['id_lang'], $idShop, $value, $ref);
			Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'attribute_lang` (`id_attribute`,`id_lang`,`name`) VALUES ('.(int)($id_attr).','.(int)$lang['id_lang'].',"'.pSQL(trim($value)).'")');
		}

		if (version_compare(_PS_VERSION_, '1.5', '>='))
		{
			$all_shop = Shop::getShops(false);
			foreach ($all_shop as $shop)
				Db::getInstance()->insert('attribute_shop', array(
					'id_attribute' => (int)($id_attr),
					'id_shop'=>(int)$shop['id_shop']));
		}

		$idAttEco = Db::getInstance()->getValue('SELECT `id_attribute_eco` FROM `'._DB_PREFIX_.'ec_ecopresto_attribute` WHERE `value`="'.pSQL($name).'" AND `id_lang`='.self::getInfoEco('ID_LANG'));
		$valEco = Db::getInstance()->getValue('SELECT count(`id_attribute`) FROM `'._DB_PREFIX_.'ec_ecopresto_attribute_shop` WHERE `id_shop`='.(int)$idShop.' AND `id_attribute_eco`='.(int)$idAttEco);

		if ($valEco > 0)
			Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'ec_ecopresto_attribute_shop` SET `id_attribute`= '.(int)$id_attr_group.'
				WHERE `id_attribute_eco`='.(int)$idAttEco.' AND `id_shop` = '.(int)$idShop);
		else
			Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'ec_ecopresto_attribute_shop` (`id_attribute_eco`,`id_attribute`,`id_shop`)
				VALUES ('.(int)$idAttEco.','.(int)$id_attr_group.','.(int)$idShop.')');

		return $id_attr;
	}

	public function deleteAttributePdt($ref)
	{
		$lst_ref = Db::getInstance()->ExecuteS('SELECT `reference_attribute` FROM `'._DB_PREFIX_.'ec_ecopresto_catalog_attribute` WHERE `reference`="'.pSQL($ref).'"');
		
		if (isset($lst_ref) && count($lst_ref) > 0)
			foreach ($lst_ref as $theref)
				Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ec_ecopresto_product_attribute` WHERE `reference` = "'.pSQL($theref['reference_attribute']).'"');

	}

	public static function execImportAttribute($pdt, $idP)
	{
		$product = new Product($idP);

		foreach ($pdt as $attributes)
		{

			$idPA = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_product_attribute` FROM `'._DB_PREFIX_.'ec_ecopresto_product_attribute` WHERE `id_shop` = '.(int)$attributes->id_shop.' AND `reference` = "'.pSQL($attributes->supplier_reference).'"');

			if (!$idPA)
			{
				Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'product_attribute` (`id_product`,`reference`,`supplier_reference`,`ean13`,`wholesale_price`,`price`,`weight`,`default_on`)
										VALUES ('.(int)$idP.',"'.pSQL($attributes->reference).'","'.pSQL($attributes->supplier_reference).'",'.(int)$attributes->ean13.',"'.pSQL($attributes->wholesale_price).'","'.pSQL($attributes->price).'","'.pSQL($attributes->weight).'",'.(int)$attributes->default_on.')');

				$idPA = Db::getInstance()->Insert_ID();

				if (version_compare(_PS_VERSION_, '1.5', '>='))
				{
					Db::getInstance()->insert('product_attribute_shop', array(
						'id_product_attribute'=>(int)$idPA,
						'id_shop'=>(int)$attributes->id_shop,
						'wholesale_price'=>pSQL($attributes->wholesale_price),
						'price'=>pSQL($attributes->price),
						'weight'=>pSQL($attributes->weight),
						'default_on'=>(int)$attributes->default_on));

					Db::getInstance()->insert('stock_available', array(
						'id_product'=>(int)$idP,
						'id_product_attribute'=>(int)$idPA,
						'id_shop'=>(int)$attributes->id_shop,
						'id_shop_group'=>(int)Shop::getGroupFromShop($attributes->id_shop),
						'quantity'=>0));
				}

				$attribute = '';

				foreach ($attributes->id_attribute as $attribute)
					Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'product_attribute_combination` (`id_attribute`,`id_product_attribute`) VALUES ('.(int)$attribute->id_attribute.','.(int)$idPA.')');

				if (version_compare(_PS_VERSION_, '1.5', '>='))
					if (!isset($pdt->id_product))
						self::insertSupplierRef($idP, $idPA, $attributes->id_supplier, $attributes->supplier_reference);

				Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'ec_ecopresto_product_attribute` (`reference`,`id_product_attribute`,`id_shop`) VALUES ("'.pSQL($attributes->supplier_reference).'",'.(int)$idPA.','.(int)$attributes->id_shop.')');

			}
			else
			{
				if (version_compare(_PS_VERSION_, '1.5', '>='))
					Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'product_attribute_shop`
						SET '.(isset($attributes->wholesale_price)?'`wholesale_price`= "'.pSQL($attributes->wholesale_price).'",':'').' '.(isset($attributes->price)?'`price`= "'.pSQL($attributes->price).'",':'').' `weight`= "'.pSQL($attributes->weight).'"
						WHERE `id_product_attribute`='.(int)$idPA.' AND `id_shop` = '.(int)$attributes->id_shop);

				Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'product_attribute`
					SET '.(isset($attributes->wholesale_price)?'`wholesale_price`= "'.pSQL($attributes->wholesale_price).'",':'').' '.(isset($attributes->price)?'`price`= "'.pSQL($attributes->price).'",':'').' `weight`= "'.pSQL($attributes->weight).'"
					WHERE `id_product_attribute`='.(int)$idPA);
			}

		}
	}


	public static function cleanUploadedImages($pdt, $product)
	{
		$images = array();
		
		if (isset($pdt->images))
			$images = (array)$pdt->images->url;
		else
			return;

		foreach ($images as &$img)
		{
			if (preg_match('/:\/\//', $img))
				continue;
			
			$img = _PS_ROOT_DIR_.$img;
			
			if (!file_exists($img))
				Log::notice($pdt->supplier_reference, 'File $img not found.');
		}

		if (isset($pdt->images->copy) && $pdt->images->copy == 'move')
		{
			foreach ($images as $url)
			{
				try
				{
					unlink($url);
				}
				catch(Exception $e)
				{
					Log::notice($pdt->supplier_reference, 'Cannot remove img $url. Please check the right on directory ' . dirname($url));
				}
			}
		}
	}


	public static function execImages($pdt, $product)
	{
		$images = array();
		
		if (isset($pdt->images))
			$images = (array)$pdt->images->url;
		else
			return;

		foreach ($images as &$img)
		{
			if (preg_match('/:\/\//', $img))
				continue;
				
			$img = _PS_ROOT_DIR_ . $img;
			
			if (!file_exists($img))
				Log::notice($pdt->supplier_reference, 'File $img not found.');
		}

		$product->deleteImages();
		$productHasImages = (bool)Image::getImages(self::getInfoEco('ID_LANG'), (int)$product->id);

		foreach ($images as $key => $url)
		{
			$url = str_replace(' ', '%20', $url);
			$image = new Image();
			$image->id_product = $product->id;
			$image->position = Image::getHighestPosition($product->id) + 1;
			$image->cover = (!$key && !$productHasImages) ? true : false;

			if ($image->add())
			{
				if (version_compare(_PS_VERSION_, '1.5', '>='))
					$image->associateTo($pdt->id_shop_default);
				if (!self::copyImg($product->id, $image->id, $url))
					Log::notice($pdt->supplier_reference, 'Error copying image: $url');
			}
			else
				Log::notice($pdt->supplier_reference, 'Cannot save image $url');
		}
	}


	/**
	 * From AdminImportController
	 */
	protected static function copyImg($id_entity, $id_image = null, $url, $entity = 'products')
	{
		$tmpfile = tempnam(_PS_TMP_IMG_DIR_, 'ps_import');
		$watermark_types = explode(',', Configuration::get('WATERMARK_TYPES'));

		switch ($entity)
		{
			case 'products':
				$image_obj = new Image($id_image);
				$path = $image_obj->getPathForCreation();
				break;
			case 'categories':
				$path = _PS_CAT_IMG_DIR_.(int)$id_entity;
				break;
			default:
				break;
		}
		
		$url = str_replace(' ', '%20', trim($url));

		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			include_once 'class/ImageManager.php';
			$imgSg = new ImageManagerCore();

			if (!$imgSg->checkImageMemoryLimit($url))
				return false;

			if (@copy($url, $tmpfile))
			{
				$imgSg->resize($tmpfile, $path.'.jpg');
				$images_types = ImageType::getImagesTypes($entity);
				foreach ($images_types as $image_type)
					$imgSg->resize($tmpfile, $path.'-'.Tools::stripslashes($image_type['name']).'.jpg', $image_type['width'], $image_type['height']);

			}
			elseif ($content = Tools::file_get_contents($url))
			{
				$fp = fopen($tmpfile, "w");
				fwrite($fp, $content);
				fclose($fp); 
				$imgSg->resize($tmpfile, $path.'.jpg');
				$images_types = ImageType::getImagesTypes($entity);
				foreach ($images_types as $image_type)
					$imgSg->resize($tmpfile, $path.'-'.Tools::stripslashes($image_type['name']).'.jpg', $image_type['width'], $image_type['height']);
			}
			else
			{
				unlink($tmpfile);
				return false;
			}
		}
		else
		{
			if (!ImageManager::checkImageMemoryLimit($url))
				return false;

			if (@copy($url, $tmpfile))
			{
				ImageManager::resize($tmpfile, $path.'.jpg');
				$images_types = ImageType::getImagesTypes($entity);
				foreach ($images_types as $image_type)
					ImageManager::resize($tmpfile, $path.'-'.Tools::stripslashes($image_type['name']).'.jpg', $image_type['width'], $image_type['height']);

				if (in_array($image_type['id_image_type'], $watermark_types))
					Hook::exec('actionWatermark', array('id_image' => $id_image, 'id_product' => $id_entity));
			}
			elseif ($content = Tools::file_get_contents($url))
			{
			    	$fp = fopen($tmpfile, "w");
                		fwrite($fp, $content);
		                fclose($fp); 
		                ImageManager::resize($tmpfile, $path.'.jpg');
				$images_types = ImageType::getImagesTypes($entity);
				foreach ($images_types as $image_type)
					ImageManager::resize($tmpfile, $path.'-'.Tools::stripslashes($image_type['name']).'.jpg', $image_type['width'], $image_type['height']);
	
				if (in_array($image_type['id_image_type'], $watermark_types))
					Hook::exec('actionWatermark', array('id_image' => $id_image, 'id_product' => $id_entity));
			}
			else
			{
				unlink($tmpfile);
				return false;
			}
		}
		
		unlink($tmpfile);
		return true;
	}
}
