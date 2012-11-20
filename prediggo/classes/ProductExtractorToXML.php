<?php

/**
 * @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
 * @copyright Croissance NET
 * @version 1.0
 */

require_once(_PS_MODULE_DIR_.'prediggo/classes/DataExtractorToXML.php');

class ProductExtractorToXML extends DataExtractorToXML
{
	/** @var array list of the active languages */
	private $aLanguages;

	/** @var bool is Product picture url included into the export */
	private $imageInExport;

	/** @var bool is Product description included into the export */
	private $descInExport;

	/** @var integer Number minimum of stock to export a product */
	private $productMinQuantity;

	/** @var array List of attributes groups which can be exported */
	private $aAttributesGroupsIds;

	/** @var array List of features which can be exported */
	private $aFeaturesIds;

	/** @var array List of products not allowed to be retrieved as recommendations */
	private $aProductsNotRecommendable;

	/** @var array List of products not allowed to be retrieved as search result */
	private $aProductsNotSearchable;

	/**
	  * Initialise the object variables
	  *
	  * @param string $sRepositoryPath path of the XML repository
	  * @param array $params Specific parameters of the object
	  */
	public function __construct($sRepositoryPath, $params)
	{
		$this->sRepositoryPath = $sRepositoryPath;
		$this->_logs = array();
		$this->_errors = array();
		$this->_confirmations = array();
		$this->sEntity = 'item';
		$this->sFileNameBase = 'items';
		$this->sEntityRoot = 'items';

		$this->imageInExport = (int)$params['imageInExport'];
		$this->descInExport = (int)$params['descInExport'];
		$this->productMinQuantity = (int)$params['productMinQuantity'];
		$this->aAttributesGroupsIds = (array)$params['aAttributesGroupsIds'];
		$this->aFeaturesIds = (array)$params['aFeaturesIds'];
		$this->aProductsNotRecommendable = (array)$params['aProductsNotRecommendable'];
		$this->aProductsNotSearchable = (array)$params['aProductsNotSearchable'];

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
		SELECT `id_product`
		FROM `'._DB_PREFIX_.'product`
		ORDER BY `id_product` ASC', false);
	}

	/**
	  * Convert the entities data into an xml object and return the xml object as a string
	  *
	  * @param array $aEntity Entity data
	  */
	public function formatEntityToXML($aEntity)
	{
		global $link;

		$sReturn = '';

		$dom = new DOMDocument('1.0', 'utf-8');

		$oProduct = new Product((int)$aEntity['id_product'], true);
		if((int)$oProduct->quantity < (int)$this->productMinQuantity)
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
			//$root->setAttribute('timestamp', date('c',strtotime($oProduct->date_add)));
			$root->setAttribute('timestamp', (int)strtotime($oProduct->date_add));

			$id = $dom->createElement('id', (int)$oProduct->id);
			$root->appendChild($id);

			$profile = $dom->createElement('profile', $id_lang);
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
			if($this->descInExport
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

			$recommendable = $dom->createElement('recommendable', in_array((int)$oProduct->id, $this->aProductsNotRecommendable)?'false':'true');
			$root->appendChild($recommendable);

			$searchable = $dom->createElement('searchable', in_array((int)$oProduct->id, $this->aProductsNotSearchable)?'false':'true');
			$root->appendChild($searchable);

			// Set product URL
			$attribute = $dom->createElement('attribute');
			$root->appendChild($attribute);

			$attName = $dom->createElement('attName', 'producturl');
			$attribute->appendChild($attName);

			$attValue = $dom->createElement('attValue');
			$attValue->appendChild($dom->createCDATASection($link->getProductLink((int)$oProduct->id, $oProduct->link_rewrite[$id_lang], Category::getLinkRewrite((int)$oProduct->id_category_default, $id_lang), NULL, $id_lang)));
			$attribute->appendChild($attValue);

			// Set product picture
			if($this->imageInExport)
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
			$aProductCombinations = $oProduct->getAttributeCombinaisons($id_lang);
			if(sizeof($aProductCombinations))
				foreach($aProductCombinations as $aProductCombination)
				{
					if(!is_array($this->aAttributesGroupsIds)
					|| in_array((int)$aProductCombination['id_attribute_group'], $this->aAttributesGroupsIds))
					{
						$attribute = $dom->createElement('attribute');
						$root->appendChild($attribute);

						$attName = $dom->createElement('attName');
						$attName->appendChild($dom->createCDATASection($aProductCombination['group_name']));
						$attribute->appendChild($attName);

						$attValue = $dom->createElement('attValue');
						$attValue->appendChild($dom->createCDATASection($aProductCombination['attribute_name']));
						$attribute->appendChild($attValue);
					}
				}
			unset($aProductCombinations);

			// Set features
			$aProductFeatures = $oProduct->getFrontFeatures($id_lang);
			if(sizeof($aProductFeatures))
				foreach($aProductFeatures as $aProductFeature)
				{
					if(!is_array($this->aFeaturesIds)
					|| in_array((int)$aProductFeature['id_feature'], $this->aFeaturesIds))
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
					$attValue->appendChild($dom->createCDATASection($aAccessory['reference']));
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