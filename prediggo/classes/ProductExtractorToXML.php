<?php

/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2014 PrestaShop SA
* @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/

require_once(_PS_MODULE_DIR_.'prediggo/classes/DataExtractorToXML.php');

class ProductExtractorToXML extends DataExtractorToXML
{
	/** @var array list of the active languages */
	private $aLanguages;

	/** @var array List of Prediggo configuration by shop */
	private $aPrediggoConfigs;

	/**
	  * Initialise the object variables
	  *
	  * @param string $sRepositoryPath path of the XML repository
	  * @param array $params Specific parameters of the object
	  */
	public function __construct($sRepositoryPath, $params, $bLogEnable)
	{
		$this->sRepositoryPath 	= $sRepositoryPath;
		$this->bLogEnable 		= (int)$bLogEnable;
		$this->_logs 			= array();
		$this->_errors 			= array();
		$this->_confirmations 	= array();
		$this->sEntity 			= 'item';
		$this->sFileNameBase 	= 'items';
		$this->sEntityRoot 		= 'items';

		$this->aPrediggoConfigs = $params['aPrediggoConfigs'];

		$this->aLanguages = Language::getLanguages(true);

	}

	/**
	  * Get the list of entities by a sql result
	  *
	  * @return Object SQL Result
	  */
	public function getEntities()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT p.`id_product`, ps.`id_shop`
		FROM `'._DB_PREFIX_.'product` p
		INNER JOIN ps_product_shop ps ON (ps.`id_product` = p.`id_product` AND ps.`id_shop` IN('.join(',',array_keys($this->aPrediggoConfigs)).'))
		ORDER BY p.`id_product` ASC', false);
	}

	/**
	  * Convert the entities data into an xml object and return the xml object as a string
	  *
	  * @param array $aEntity Entity data
	  */
	public function formatEntityToXML($aEntity)
	{
		$sReturn = '';

		$dom = new DOMDocument('1.0', 'utf-8');
		
		$bUseRoutes = (bool)Configuration::get('PS_REWRITING_SETTINGS');
		
		$oDispatcher = Dispatcher::getInstance();
		
		// Force the dispatcher to use custom routes because the use of custom routes is disabled in the BO Context 
		foreach($oDispatcher->default_routes as $route_id => $route_data)
			if($custom_route = Configuration::get('PS_ROUTE_'.$route_id))
				foreach(Language::getLanguages() as $lang)
					$oDispatcher->addRoute(
						$route_id,
						$custom_route,
						$route_data['controller'],
						$lang['id_lang'],
						$route_data['keywords'],
						isset($route_data['params']) ? $route_data['params'] : array()
					);

		$oPrediggoConfig = $this->aPrediggoConfigs[(int)$aEntity['id_shop']];
		
		$link = $oPrediggoConfig->getContext()->link;
				
		$oProduct = new Product((int)$aEntity['id_product'], true, null, (int)$aEntity['id_shop'], $oPrediggoConfig->getContext());
		if((int)StockAvailable::getQuantityAvailableByProduct((int)$aEntity['id_product'], 0, (int)$aEntity['id_shop']) < (int)$oPrediggoConfig->export_product_min_quantity)
		{
			$this->nbEntitiesTreated--;
			$this->nbEntities--;
			return ' ';
		}

		$ps_tax = (int)Configuration::get('PS_TAX');

		foreach($this->aLanguages as $aLanguage)
		{
			$id_lang = (int)$aLanguage['id_lang'];

			// Set the root of the XML
			$root = $dom->createElement($this->sEntity);
			$dom->appendChild($root);

			$root->setAttribute('timestamp', (int)strtotime($oProduct->date_add));

			$id = $dom->createElement('id', (int)$oProduct->id);
			$root->appendChild($id);

			$profile = $dom->createElement('profile', (int)$aEntity['id_shop']);
			$root->appendChild($profile);

			$name = $dom->createElement('name');
			$name->appendChild($dom->createCDATASection($oProduct->name[$id_lang]));
			$root->appendChild($name);

			$oCategory = new Category((int)$oProduct->id_category_default);
			$aCategories = $oCategory->getParentsCategories($id_lang);

			if(is_array($aCategories) && count($aCategories)>0)
				foreach($aCategories as $aCategory)
				{
					$oCategoryTmp = new Category((int)$aCategory['id_category'], $id_lang);
					if(!empty($oCategoryTmp->name))
					{
						$genre = $dom->createElement('genre');
						$genre->appendChild($dom->createCDATASection($oCategoryTmp->name));
						$root->appendChild($genre);
					}
					unset($oCategoryTmp);
				}

			unset($aCategories);
			unset($oCategory);

			if(!empty($oProduct->ean13))
			{
				$ean = $dom->createElement('ean');
				$ean->appendChild($dom->createCDATASection($oProduct->ean13));
				$root->appendChild($ean);
			}

			$price = $dom->createElement('price', number_format($oProduct->getPrice($ps_tax), 2, '.', ''));
			$root->appendChild($price);

			if(isset($oProduct->tags[$id_lang])
			&& $aTags = $oProduct->tags[$id_lang])
			{
				$tag = $dom->createElement('tag');
				$tag->appendChild($dom->createCDATASection(join(',',$aTags)));
				$root->appendChild($tag);
			}

			$sDesc = trim(strip_tags($oProduct->description[$id_lang]));
			if($oPrediggoConfig->export_product_description
			&& !empty($sDesc))
			{
				$description = $dom->createElement('description');
				$description->appendChild($dom->createCDATASection($sDesc));
				$root->appendChild($description);
			}

			if(!empty($oProduct->id_manufacturer))
			{
				$supplierid = $dom->createElement('supplierid', (int)$oProduct->id_manufacturer);
				$root->appendChild($supplierid);
			}

			$recommendable = $dom->createElement('recommendable', in_array((int)$oProduct->id, explode(',',$oPrediggoConfig->products_ids_not_recommendable))?'false':'true');
			$root->appendChild($recommendable);

			$searchable = $dom->createElement('searchable', in_array((int)$oProduct->id, explode(',',$oPrediggoConfig->products_ids_not_searchable))?'false':'true');
			$root->appendChild($searchable);

			// Set product URL
			$attribute = $dom->createElement('attribute');
			$root->appendChild($attribute);

			$attName = $dom->createElement('attName', 'producturl');
			$attribute->appendChild($attName);

			$attValue = $dom->createElement('attValue');
			$attValue->appendChild($dom->createCDATASection($link->getProductLink((int)$oProduct->id, $oProduct->link_rewrite[$id_lang], Category::getLinkRewrite((int)$oProduct->id_category_default, $id_lang), NULL, $id_lang, (int)$aEntity['id_shop'], 0, $bUseRoutes)));
			$attribute->appendChild($attValue);

			// Set product picture
			if($oPrediggoConfig->export_product_image)
			{
				$attribute = $dom->createElement('attribute');
				$root->appendChild($attribute);

				$attName = $dom->createElement('attName', 'imageurl');
				$attribute->appendChild($attName);

				$aCover = $oProduct->getCover((int)$oProduct->id);
				$attValue = $dom->createElement('attValue');
				$attValue->appendChild($dom->createCDATASection($link->getImageLink($oProduct->link_rewrite[$id_lang], (int)$aCover['id_image'], 'large')));
				$attribute->appendChild($attValue);
			}

			// Set combinations
			$aProductCombinations = Product::getAttributesInformationsByProduct((int)$oProduct->id);
			if(sizeof($aProductCombinations))
				foreach($aProductCombinations as $aProductCombination)
				{
					if(!empty($oPrediggoConfig->attributes_groups_ids)
					&& in_array((int)$aProductCombination['id_attribute_group'], explode(',',$oPrediggoConfig->attributes_groups_ids)))
					{
						$attribute = $dom->createElement('attribute');
						$root->appendChild($attribute);

						$attName = $dom->createElement('attName');
						$attName->appendChild($dom->createCDATASection($aProductCombination['group']));
						$attribute->appendChild($attName);

						$attValue = $dom->createElement('attValue');
						$attValue->appendChild($dom->createCDATASection($aProductCombination['attribute']));
						$attribute->appendChild($attValue);
					}
				}
			unset($aProductCombinations);

			// Set features
			$aProductFeatures = $oProduct->getFrontFeatures($id_lang);
			if(sizeof($aProductFeatures))
				foreach($aProductFeatures as $aProductFeature)
				{
					if(!empty($oPrediggoConfig->features_ids)
					&& in_array((int)$aProductFeature['id_feature'], explode(',',$oPrediggoConfig->features_ids)))
					{
						$attribute = $dom->createElement('attribute');
						$root->appendChild($attribute);

						$attName = $dom->createElement('attName');
						$attName->appendChild($dom->createCDATASection($aProductFeature['name']));
						$attribute->appendChild($attName);

						$attValue = $dom->createElement('attValue');
						$attValue->appendChild($dom->createCDATASection($aProductFeature['value']));
						$attribute->appendChild($attValue);
					}
				}
			unset($aProductFeatures);

			$aAccessories = Product::getAccessoriesLight($id_lang, (int)$oProduct->id);
			if(sizeof($aAccessories))
				foreach($aAccessories as $aAccessory)
				{
					$attribute = $dom->createElement('attribute');
					$root->appendChild($attribute);

					$attName = $dom->createElement('attName');
					$attName->appendChild($dom->createCDATASection('accessory'));
					$attribute->appendChild($attName);

					$attValue = $dom->createElement('attValue');
					$attValue->appendChild($dom->createCDATASection((int)$aAccessory['id_product']));
					$attribute->appendChild($attValue);
				}
			unset($aAccessories);

			$sReturn .= $dom->saveXML($root);
		}

		unset($dom);
		unset($oProduct);

		return $sReturn;
	}
}