<?php

/**
 * Shopgate GmbH
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file AFL_license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to interfaces@shopgate.com so we can send you a copy immediately.
 *
 * @author     Shopgate GmbH, Schloßstraße 10, 35510 Butzbach <interfaces@shopgate.com>
 * @copyright  Shopgate GmbH
 * @license    http://opensource.org/licenses/AFL-3.0 Academic Free License ("AFL"), in the version 3.0
 *
 * User: awesselburg
 * Date: 06.03.14
 * Time: 10:14
 *
 * File: PluginModelItemObject.php
 *
 */
class PluginModelItemObject
	extends Shopgate_Model_Catalog_Product {

	protected $context;

	/**
	 * set context
	 */
	public function __construct ($context) {

		$this->setContext($context);
		parent::__construct();
	}

	/**
	 * set context
	 *
	 * @param $context
	 */
	protected function setContext ($context) {
		$this->context = $context;
	}

	/**
	 * get context
	 *
	 * @return mixed
	 */
	protected function getContext () {
		return $this->context;
	}


	/**
	 * set item uid
	 */
	public function setUid () {
		parent::setUid($this->item->id);
	}

	/**
	 * set last update
	 *
	 * @todo
	 */
	public function setLastUpdate () {
		parent::setLastUpdate($this->item->date_upd . ' ' . date('T'));
	}

	/**
	 * set name
	 */
	public function setName () {
		parent::setName($this->item->name);
	}

	/**
	 * set tax percent
	 */
	public function setTaxPercent () {
		parent::setTaxPercent($this->item->tax_rate);
	}

	/**
	 * set tax class
	 */
	public function setTaxClass () {
		parent::setTaxClass($this->item->tax_name);
	}

	/**
	 * set currency
	 */
	public function setCurrency () {
		parent::setCurrency($this->getContext()->currency->iso_code);
	}

	/**
	 * set description
	 */
	public function setDescription () {
		parent::setDescription($this->item->description);
	}

	public function setPrice () {
		/**
		 * prepare price item
		 */
		$priceItem = new Shopgate_Model_Catalog_Price();

		$priceItem->setType(Shopgate_Model_Catalog_Price::DEFAULT_PRICE_TYPE_GROSS);
		/** @var $this->item ProductCore */
		$priceItem->setPrice($this->item->getPriceWithoutReduct(true));
		if ($this->item->wholesale_price != 0) {
			$priceItem->setCost($this->item->wholesale_price);
		}
		//$priceItem->setSalePrice();
		//$priceItem->setMsrp();
        if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
            $priceItem->setMinimumOrderAmount($this->item->minimal_quantity);
        }

		/**
		 * tier prices
		 */
		$tierPrices = $this->getTierPricesFromDb();

		if (is_array($tierPrices)) {
			foreach ($tierPrices as $tierPrice) {

				$tierPriceItem = new Shopgate_Model_Catalog_TierPrice();

				$tierPriceItem->setFromQuantity($tierPrice['from_quantity']);
				$tierPriceItem->setReductionType($this->mapTierPriceType($tierPrice['reduction_type']));

				$tierPriceItem->setReduction(
					$tierPriceItem->getReductionType() == Shopgate_Model_Catalog_TierPrice::DEFAULT_TIER_PRICE_TYPE_PERCENT
					? $tierPrice['reduction'] * 100
					: $tierPrice['reduction']
				);

				if(array_key_exists('id_group', $tierPrice) && $tierPrice['id_group'] != 0) {
					$tierPriceItem->setCustomerGroupUid($tierPrice['id_group']);
				}

				$priceItem->addTierPriceGroup($tierPriceItem);

			}
		}

		parent::setPrice($priceItem);
	}

	/**
	 * set weight unit
	 */
	public function setWeightUnit () {
		parent::setWeightUnit(Tools::strtolower(Configuration::get('PS_WEIGHT_UNIT')));
	}

	/**
	 * set weight
	 */
	public function setWeight () {
		parent::setWeight($this->item->weight);
	}

	/**
	 * set images
	 */
	public function setImages () {
		$result = array();

		if (!$this->hasCombinations()) {

			/** @var Product $product */
			$product = new Product($this->item->id);

			foreach ($product->getImages($this->getContext()->language->id) as $image) {
				$imageItem = new Shopgate_Model_Media_Image();
				$imageItem->setUid($image['id_image']);
				$imageItem->setUrl(
					$this->getContext()->link->getImageLink($this->item->link_rewrite, $product->id . '-' . $image['id_image'])
				);
				$imageItem->setSortOrder($image['position']);
				$imageInfo = $this->getImageInfo($image['id_image']);

				if (is_array($imageInfo) && array_key_exists(0, $imageInfo)) {
					$imageItem->setAlt($imageInfo[0]['legend']);
					$imageItem->setTitle($imageInfo[0]['legend']);
				}

				array_push($result, $imageItem);
			}
		}

		parent::setImages($result);
	}

	/**
	 * set categories
	 *
	 * @todo -> render path ?
	 */
	public function setCategoryPaths () {
		$result = array();
		foreach ($this->getCategoriesFromDb() as $category) {
			$categoryPathItem = new Shopgate_Model_Catalog_CategoryPath();
			$categoryPathItem->setUid($category['id_category']);
			$categoryPathItem->setSortOrder($category['position']);
			foreach ($this->getCategoryPathsFromModel($category['id_category']) as $path) {
				$categoryPathItem->addItem($path['level_depth'], $path['name']);
			}
			array_push($result, $categoryPathItem);
		}
		parent::setCategoryPaths($result);
	}

	/**
	 * set the product deep link
	 *
	 * @todo fix is mod_rewrite not enabled
	 */
	public function setDeepLink () {
		parent::setDeeplink(
			$this->getContext()
				->link->getProductLink(
					$this->item->id,
					$this->item->link_rewrite,
					$this->item->category,
					$this->item->ean13,
					$this->getContext()->language->id
				)
		);
	}

	/**
	 * set shipping
	 */
	public function setShipping () {
		$shippingItem = new Shopgate_Model_Catalog_Shipping();
        if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
            if ($this->item->additional_shipping_cost > 0) {
                $shippingItem->setAdditionalCostsPerUnit($this->item->additional_shipping_cost);
            }
        }
		//$shippingItem->setCostsPerOrder();
		//$shippingItem->setIsFree(true);
		parent::setShipping($shippingItem);
	}

	/**
	 * add manufacturer
	 */
	public function setManufacturer () {
		$manufacturerItem = new Shopgate_Model_Catalog_Manufacturer();
		$manufacturerItem->setUid($this->item->id_manufacturer);
		//$manufacturerItem->setItemNumber();
		$manufacturerItem->setTitle($this->item->manufacturer_name);
		parent::setManufacturer($manufacturerItem);
	}

	/**
	 * add properties
	 */
	public function setProperties () {
		$result = array();
		$properties = Product::getFrontFeaturesStatic($this->getContext()->language->id, $this->item->id);

		foreach ($properties as $property) {
			$propertyItemObject = new Shopgate_Model_Catalog_Property();
			$propertyItemObject->setUid($property['id_feature']);
			$propertyItemObject->setLabel($property['name']);
			$propertyItemObject->setValue($property['value']);
			array_push($result, $propertyItemObject);
		}

		parent::setProperties($result);
	}

	/**
	 * add visibility
	 */
	public function setVisibility () {
		$visibilityItem = new Shopgate_Model_Catalog_Visibility();
		//$visibilityItem->setMarketplace();
        if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
            $visibilityItem->setLevel($this->mapVisibility($this->item->visibility));
        }
		parent::setVisibility($visibilityItem);
	}

	/**
	 * @todo
	 */
	public function setStock () {
		$stockItem = new Shopgate_Model_Catalog_Stock();
		$stockItem->setAvailabilityText($this->item->available_now);
		//$stockItem->setBackorders(1);
		//$stockItem->setMaximumOrderQuantity();
        if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
            $stockItem->setIsSaleable($this->item->available_for_order);
		    $stockItem->setMinimumOrderQuantity($this->item->minimal_quantity);
        }
		$stockItem->setStockQuantity($this->item->quantity);
        if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
		    $stockItem->setUseStock($this->item->depends_on_stock);
        }
		parent::setStock($stockItem);
	}

	/**
	 * add identifiers
	 */
	public function setIdentifiers () {
		$result = array();

		/**
		 * UPC
		 */
        if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
            $identifierItem = new Shopgate_Model_Catalog_Identifier();
            $identifierItem->setUid(1);
            $identifierItem->setType('UPC');
            $identifierItem->setValue($this->item->upc);
            array_push($result, $identifierItem);
        }

		/**
		 * EAN13
		 */
		$identifierItem = new Shopgate_Model_Catalog_Identifier();
		$identifierItem->setUid(2);
		$identifierItem->setType('EAN13');
		$identifierItem->setValue($this->item->ean13);
		array_push($result, $identifierItem);

		/**
		 * reference
		 */
		$identifierItem = new Shopgate_Model_Catalog_Identifier();
		$identifierItem->setUid(3);
		$identifierItem->setType('reference');
		$identifierItem->setValue($this->item->reference);
		array_push($result, $identifierItem);


		parent::setIdentifiers($result);
	}

	/**
	 * add tags
	 */
	public function setTags () {
		$result = array();
		{
			if (isset($this->item->tags[$this->getContext()->language->id])) {
				foreach ($this->item->tags[$this->getContext()->language->id] as $number => $value) {
					$tagItem = new Shopgate_Model_Catalog_Tag();
					$tagItem->setUid($number);
					$tagItem->setValue($value);
					array_push($result, $tagItem);
				}
			}
		}

		parent::setTags($result);
	}

	/**
	 * add promotion sort order
	 */
	public function setPromotionSortOrder () {
	}

	/**
	 * add internal order info
	 */
	public function setInternalOrderInfo () {
	}

	/**
	 * add relations
	 */
	public function setRelations () {
	}

	/**
	 * add age rating
	 */
	public function setAgeRating () {
	}

	/**
	 * add attributes
	 */
	public function setAttributeGroups () {
		$result = array();

		if ($this->item->hasAttributes()) {

            if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
                $attributes = Product::getAttributesInformationsByProduct($this->item->id);
            } else {
                $attributes = $this->item->getAttributeCombinaisons($this->getContext()->language->id);
            }

			$addedGroup = array();
			foreach ($attributes as $attribute) {
				/**
				 * prestashop :-(
				 */
				if (!in_array($attribute['id_attribute_group'], $addedGroup)) {
					$attributeItem = new Shopgate_Model_Catalog_AttributeGroup();
					$attributeItem->setUid($attribute['id_attribute_group']);
                    if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
                        $attributeItem->setLabel($attribute['group']);
                    } else {
                        $attributeGroup = new AttributeGroup($attributeItem->getUid(), $this->getContext()->language->id);
                        $attributeItem->setLabel($attributeGroup->name);
                    }

					array_push($result, $attributeItem);
					array_push($addedGroup, $attribute['id_attribute_group']);
				}
			}
		}

		parent::setAttributeGroups($result);
	}

	/**
	 * add inputs
	 */
	public function setInputs () {
		$result = array();

		if ($this->item->customizable) {
			$customizationFields = $this->item->getCustomizationFields($this->getContext()->language->id);
			foreach ($customizationFields as $customizationField) {
				$inputItem = new Shopgate_Model_Catalog_Input();
				$inputItem->setUid($customizationField['id_customization_field']);
				$inputItem->setLabel($customizationField['name']);

				if ($customizationField['required'] == 1) {
					$inputItem->setRequired(true);
				}

				switch ($customizationField['type']) {
					case 0 :
						$inputItem->setType(Shopgate_Model_Catalog_Input::DEFAULT_INPUT_TYPE_FILE);
						break;
					case 1 :
						$inputItem->setType(Shopgate_Model_Catalog_Input::DEFAULT_INPUT_TYPE_TEXT);
						break;
				}

				array_push($result, $inputItem);
			}
		}

		parent::setInputs($result);
	}

	/**
	 * set children
	 */
	public function setChildren () {
		$result = array();

		if ($this->item->hasAttributes()) {

			$combination_images = $this->item->getCombinationImages($this->getContext()->language->id);
			$attributes = $this->item->getAttributeCombinaisons($this->getContext()->language->id);
			$combinations = array();

			$attribute_groups = array();

			foreach ($attributes as $a) {
				$combinations[$a['id_product_attribute']][$a['id_attribute_group']] = $a;
				$attribute_groups[$a['id_attribute_group']] = $a['group_name'];
			}

			foreach ($combinations as $id => $c) {
				$combination = current($c);

				/**
				 * global info
				 */
				$childItemItem = new Shopgate_Model_Catalog_Product();
				$childItemItem->setIsChild(true);
				$childItemItem->setUid($this->item->id . '_' . $id);
				//$childItemItem->setUid($this->item->id);

				/**
				 * id default child
				 */
				if (array_key_exists('default_on', $combination) && $combination['default_on'] == 1) {
					$childItemItem->setIsDefaultChild(true);
				}

				/**
				 * price
				 */
				$priceItem = new Shopgate_Model_Catalog_Price();
				if ($combination['wholesale_price'] > 0 && $combination['wholesale_price'] != $this->getPrice()->getCost()) {
					$priceItem->setCost($combination['wholesale_price']);
				}

				if (array_key_exists('minimal_quantity', $combination) && $combination['minimal_quantity'] != $this->getPrice()->getMinimumOrderAmount()) {
					$priceItem->setMinimumOrderAmount($combination['minimal_quantity']);
				}

				if ($combination['price'] != 0) {
					$priceItem->setPrice($this->getPrice()->getPrice() + $combination['price']);
				}

				$childItemItem->setPrice($priceItem);

				/**
				 * stock
				 */
				$stockItem = new Shopgate_Model_Catalog_Stock();
				$stockItem->setStockQuantity($combination['quantity']);
				$childItemItem->setStock($stockItem);

				/**
				 * identifier
				 *
				 * UPC
				 */
				if (array_key_exists('upc', $combination) && $combination['upc']) {
					$identifierItem = new Shopgate_Model_Catalog_Identifier();
					$identifierItem->setUid(1);
					$identifierItem->setType('UPC');
					$identifierItem->setValue($combination['upc']);
					$childItemItem->addIdentifier($identifierItem);
				}

				/**
				 * EAN13
				 */
				if ($combination['ean13']) {
					$identifierItem = new Shopgate_Model_Catalog_Identifier();
					$identifierItem->setUid(2);
					$identifierItem->setType('EAN13');
					$identifierItem->setValue($combination['ean13']);
					$childItemItem->addIdentifier($identifierItem);
				}

				/**
				 * reference
				 */
				if ($combination['reference']) {
					$identifierItem = new Shopgate_Model_Catalog_Identifier();
					$identifierItem->setUid(3);
					$identifierItem->setType('reference');
					$identifierItem->setValue($this->item->reference);
					$childItemItem->addIdentifier($identifierItem);
				}

				/**
				 * attribute options
				 */
				foreach ($c as $item) {
					$attributeItem = new Shopgate_Model_Catalog_Attribute();
					$attributeItem->setGroupUid($item['id_attribute_group']);
					$attributeItem->setUid($item['id_attribute']);
					$attributeItem->setLabel($item['attribute_name']);
					$childItemItem->addAttribute($attributeItem);
				}

				/**
				 * example visibility
				 */
				$v = new Shopgate_Model_Catalog_Visibility();
				$v->setLevel('catalog_and_search');

				$childItemItem->setVisibility($v);

				/**
				 * images
				 */
				/** @var Product $product */
				if (is_array($combination_images) && array_key_exists($id, $combination_images)) {
					$product = new Product($this->item->id);
					foreach ($combination_images[$id] as $combination_image) {
						$imageItem = new Shopgate_Model_Media_Image();
						$imageItem->setUid($combination_image['id_image']);
						$imageItem->setUrl(
							$this->getContext()->link->getImageLink(
								$this->item->link_rewrite, $product->id . '-' . $combination_image['id_image']
							)
						);
						$imageInfo = $this->getImageInfo($combination_image['id_image']);

						if (is_array($imageInfo) && array_key_exists(0, $imageInfo)) {
							$imageItem->setAlt($imageInfo[0]['legend']);
							$imageItem->setTitle($imageInfo[0]['legend']);
						}

						$childItemItem->addImage($imageItem);
					}
				}

				array_push($result, $childItemItem);
			}
		}

		parent::setChildren($result);
	}

	/**
	 * start helper
	 */

	/**
	 * returns the inputs data
	 *
	 * @return array
	 */
	protected function getInputsFromDb () {
		$select = sprintf(
			'SELECT
			  cf.required,
			  cf.type,
			  cf.id_customization_field,
			  cl.name
			FROM %scustomization_field as cf
			INNER JOIN %scustomization_field_lang as cl
			  ON cf.id_customization_field = cl.id_customization_field
			WHERE cf.id_product = %s AND cl.id_lang = %s;',
			_DB_PREFIX_,
			_DB_PREFIX_,
			$this->item->id,
			$this->getContext()->language->id
		);

		return Db::getInstance()->ExecuteS($select);
	}

	/**
	 * returns image info by id
	 *
	 * @param int $imageId
	 *
	 * @return array
	 */
	protected function getImageInfo ($imageId) {
		$select = sprintf(
			'SELECT * from %simage_lang WHERE id_image = %s AND id_lang = %s',
			_DB_PREFIX_,
			$imageId,
			$this->getContext()->language->id
		);

		return Db::getInstance()->ExecuteS($select);
	}


	/**
	 * @return array
	 */
	protected function getTierPricesFromDb () {
		$select = sprintf(
			'SELECT * from %sspecific_price WHERE id_product = %s',
			_DB_PREFIX_,
			$this->item->id
		);

		return Db::getInstance()->ExecuteS($select);
	}

	protected function getCategoriesFromDb () {
		$select = sprintf(
			'SELECT
						cp.id_category,
						cp.position,
						cl.name
						FROM %scategory_product AS cp
						LEFT JOIN %scategory_lang AS cl
						ON cp.id_category = cl.id_category
						WHERE cp.id_product = %s AND
						cl.id_lang = %s
						group by cp.id_category
						',
			_DB_PREFIX_,
			_DB_PREFIX_,
			$this->item->id,
			$this->getContext()->language->id
		);

		return Db::getInstance()->ExecuteS($select);
	}

	/**
	 * returns the parent categories
	 *
	 * @param int $categoryId
	 *
	 * @return array
	 */
	protected function getCategoryPathsFromModel ($categoryId) {
		$categoryModel = new Category($categoryId, $this->getContext()->language->id);
		return $categoryModel->getParentsCategories($this->getContext()->language->id);
	}

	/**
	 * @param $originalType
	 *
	 * @return string
	 */
	protected function mapTierPriceType ($originalType) {
		switch ($originalType) {
			case 'amount' :
				return Shopgate_Model_Catalog_TierPrice::DEFAULT_TIER_PRICE_TYPE_FIXED;
			case 'percentage' :
				return Shopgate_Model_Catalog_TierPrice::DEFAULT_TIER_PRICE_TYPE_PERCENT;
			case 'difference' :
				return Shopgate_Model_Catalog_TierPrice::DEFAULT_TIER_PRICE_TYPE_DIFFERENCE;
		}
	}

	/**
	 * @param $originalType
	 *
	 * @return string
	 */
	protected function mapVisibility ($originalType) {
		switch ($originalType) {
			case 'both' :
				return Shopgate_Model_Catalog_Visibility::DEFAULT_VISIBILITY_CATALOG_AND_SEARCH;
			case
			'catalog' :
				return Shopgate_Model_Catalog_Visibility::DEFAULT_VISIBILITY_CATALOG;
			case 'search' :
				return Shopgate_Model_Catalog_Visibility::DEFAULT_VISIBILITY_SEARCH;
			case 'none' :
				return Shopgate_Model_Catalog_Visibility::DEFAULT_VISIBILITY_NOT_VISIBLE;
		}
	}

	/**
	 * check has combinations
	 */
	protected function hasCombinations () {
		$combinations = $this->item->getAttributeCombinaisons($this->getContext()->language->id);

		return (is_array($combinations) && count($combinations) > 0) ? true : false;
	}
}