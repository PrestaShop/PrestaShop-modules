<?php
/**
* 2014 Affinity-Engine
*
* NOTICE OF LICENSE
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade AffinityItems to newer
* versions in the future. If you wish to customize AffinityItems for your
* needs please refer to http://www.affinity-engine.fr for more information.
*
*  @author    Affinity-Engine SARL <contact@affinity-engine.fr>
*  @copyright 2014 Affinity-Engine SARL
*  @license   http://www.gnu.org/licenses/gpl-2.0.txt GNU GPL Version 2 (GPLv2)
*  International Registered Trademark & Property of Affinity Engine SARL
*/

class ProductSynchronize extends AbstractModuleSynchronize {

	const ORDER = 1;

	public function __construct() { 
		parent::__construct(new ProductRepository());
	}
	
	public function getCountElementToSynchronize($clause) { 
			$countElement = 0;
			if($tmp = AEAdapter::countProduct($clause)) {
				$countElement = (int)$tmp[0]['cproduct'];
			}
			return $countElement;
	}

	public function updateNumberElementSynchronized() { 

	}

	public function syncNewElement() {
		$clause = AEAdapter::newProductClause();
		$countProduct = $this->getCountElementToSynchronize($clause);
		if(!AELibrary::isNull($countProduct)) {
			$countPage = ceil($countProduct/parent::BULK_PACKAGE);
			for($cPage = 0; $cPage <= ($countPage - 1); $cPage++) {
				$content = $this->syncProduct($clause);
				$request = new ProductRequest($content);
				if($request->post()) {
					$content = AELibrary::castArray($content);
					$this->getRepository()->insert($content);
				}
			}
		}
	}

	public function syncUpdateElement() {
		$clause = AEAdapter::updateProductClause();
		$countProduct = $this->getCountElementToSynchronize($clause);
		if(!AELibrary::isNull($countProduct)) {
			$countPage = ceil($countProduct/parent::BULK_PACKAGE);
			for($cPage = 0; $cPage <= ($countPage - 1); $cPage++) {
				$content = $this->syncProduct($clause);
				$request = new ProductRequest($content);
				if($request->put()) {
					$content = AELibrary::castArray($content);
					$this->getRepository()->update($content);
				}
			}
		}
	}


	/*
	 * TODO : Improve !empty(...) ;) 
	*/
	public function syncDeleteElement() {
		$aeproductList = array();
		$sProductList = AEAdapter::deleteProductClause();
		if(count($sProductList) > 0) {
			foreach ($sProductList as $productId) {
				$product = new stdClass();
				$product->productId = $productId["id_product"];
				if(count($sProductList) > 1) {
					array_push($aeproductList, $product);
				}
			}
			if(!empty($aeproductList)) {
				$content = $aeproductList;
			}
			else {			
				$content = $product;
			}
			$request = new ProductRequest($content);
			if($request->delete()) {
					$content = AELibrary::castArray($content);
					$this->getRepository()->delete($content);
			}
		}
	}

	public function syncProduct($clause) {

		$aeproductList = array();

		$productList = AEAdapter::getProductList($clause, parent::BULK_PACKAGE);

		foreach ($productList as $product) {
			$localizationList = array();
			$categoryList = array();
			$priceList = array();

			$localizations = AEAdapter::getProductsLocalizations((int)$product["id_product"]);

			foreach ($localizations as $localization) {
				$tagList = array();
				$attributeList = array();
				$featureList = array();

				$tagList = $this->getProductTags($product["id_product"], $localization["iso_code"]);
				$attributeList = $this->getProductAttributes($product["id_product"], $localization["iso_code"]);
				$featureList = $this->getProductFeatures($product["id_product"], $localization["iso_code"]);

				$plocalization = new stdClass();
				$plocalization->language = $localization["iso_code"];
				$plocalization->name = $localization["name"];
				$plocalization->shortDescription = $localization["description_short"];
				$plocalization->description = $localization["description"];

				$plocalization->manufacturer = $localization["mname"];
				$plocalization->supplier = $localization["sname"];
				$plocalization->tags = $tagList;
				$plocalization->attributes = $attributeList;
				$plocalization->features = $featureList;

				array_push($localizationList, $plocalization);
			}

			$priceList = $this->getProductPrices($product["id_product"]);
			$categoryList = $this->getCategories($product["id_product"]);

			$aeproduct = new stdClass();
			$aeproduct->productId = (int)$product["id_product"];
			$aeproduct->updateDate = $product["date_upd"];
			$aeproduct->categoryIds = $categoryList;
			$aeproduct->recommendable = (bool)$product["active"];			
			$aeproduct->localizations = $localizationList;
			$aeproduct->prices = $priceList;

			if(count($productList) > 1) {
				array_push($aeproductList, $aeproduct);
			}
		}

		if(!empty($aeproductList)) {
			return $aeproductList;
		}
		else {			
			return $aeproduct;
		}
	}
	
	public function getCategories($productId) {
		$categoryList = array();

		if (!$tmp = AEAdapter::getProductCategories((int)$productId)) {
			return array();
		}

		foreach ($tmp as $category) {
			array_push($categoryList, (string)$category['id_category']);
		}

		return $categoryList;
	}

	public function getProductTags($productId, $isoCode) {
		$listTag = array();
	 	if (!$tags = AEAdapter::getProductTags($productId, $isoCode)) {
	 		return array();
		}
		foreach ($tags as $tag) {
	 		array_push($listTag, $tag['name']);
	 	}
	 	return $listTag;
	}

	public static function getProductPrices($productId){
	 	$listPrice = array();
		if (!$prices = AEAdapter::getProductPrices($productId)) {
			return array();
		}
	 	foreach ($prices as $pprice){
	 		$price = new stdClass();
	 		$price->currency = $pprice['iso_code'];
	 		$price->amount = $pprice['price'];
	 		array_push($listPrice, $price);
	 	}
	 	return $listPrice;
	}


	public static function getProductAttributes($productId, $isoCode){
	 	$listAttribute = array();
		$tmpAttribute = array();

		if (!$tmp = AEAdapter::getProductAttributes($productId, $isoCode)) {
			return array();
		}

	 	foreach ($tmp as $attribute){
	 		$tmpAttribute[$attribute['groupname']][] = array("characteristicId" => $attribute['id_attribute'], "name" => $attribute['name']);
	 	}
	 	foreach ($tmpAttribute as $key => $value) {
	 		$group = new stdClass();
	 		$group->name = $key;
	 		$group->values = $value;

	 		array_push($listAttribute, $group);
	 	}
	 	return $listAttribute;
	}

	public static function getProductFeatures($productId, $isoCode) {
		$listFeature = array();
		$tmpFeature = array();

		if (!$tmp = AEAdapter::getProductFeatures($productId, $isoCode)) {
			return array();
		}

		foreach ($tmp as $feature){
	 		$tmpFeature[$feature['name']][] = array("characteristicId" => $feature['id_feature_value'], "name" => $feature['value']);
	 	}

	 	foreach ($tmpFeature as $key => $value) {
	 		$group = new stdClass();
	 		$group->name = $key;
	 		$group->values = $value;
	 		array_push($listFeature, $group);
	 	}

	 	return $listFeature;
	}
	
}

?>