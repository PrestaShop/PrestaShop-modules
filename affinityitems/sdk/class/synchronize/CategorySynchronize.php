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

class CategorySynchronize extends AbstractModuleSynchronize {

	const ORDER = 0;

	public function __construct() { 
		parent::__construct(new CategoryRepository());
	}
	
	public function getCountElementToSynchronize($clause) { 
			$countElement = 0;
			if($tmp = AEAdapter::countCategory($clause)) {
				$countElement = (int)$tmp[0]['ccategory'];
			}
			return $countElement;
	}

	public function updateNumberElementSynchronized() { 

	}

	public function syncNewElement() {
		$clause = AEAdapter::newCategoryClause();
		$countCategory = $this->getCountElementToSynchronize($clause);
		if(!AELibrary::isNull($countCategory)) {
			$countPage = ceil($countCategory/parent::BULK_PACKAGE);
			for($cPage = 0; $cPage <= ($countPage - 1); $cPage++) {
				$content = $this->syncCategory($clause);
				$request = new CategoryRequest($content);
				if($request->post()) {
					$content = AELibrary::castArray($content);
					$this->getRepository()->insert($content);
				}
			}
		}
	}

	public function syncUpdateElement() {
		$clause = AEAdapter::updateCategoryClause();
		$countCategory = $this->getCountElementToSynchronize($clause);
		if(!AELibrary::isNull($countCategory)) {
			$countPage = ceil($countCategory/parent::BULK_PACKAGE);
			for($cPage = 0; $cPage <= ($countPage - 1); $cPage++) {
				$content = $this->syncCategory($clause);
				$request = new CategoryRequest($content);
				if($request->put()) {
					$content = AELibrary::castArray($content);
					$this->getRepository()->update($content);
				}
			}
		}
	}

	public function syncDeleteElement() {
		$aecategoryList = array();
		$sCategoryList = AEAdapter::deleteCategoryClause();
		if(count($sCategoryList) > 0) {
			foreach ($sCategoryList as $categoryId) {
				$category = new stdClass();
				$category->categoryId = $categoryId["id_category"];
				if(count($sCategoryList) > 1) {
					array_push($aecategoryList, $category);
				}
			}
			if(!empty($aecategoryList)) {
				$content = $aecategoryList;
			}
			else {			
				$content = $category;
			}
			$request = new CategoryRequest($content);
			if($request->delete()) {
					$content = AELibrary::castArray($content);
					$this->getRepository()->delete($content);
			}
		}
	}

	public function syncCategory($clause) {
		$categoryList = array();

		$categories = AEAdapter::getCategoryList($clause, parent::BULK_PACKAGE);

		foreach ($categories as $pcategory) {
			$featureList = array();

			$featureList = $this->getFeatureList($pcategory['id_category']);

			$category = new stdClass();
			$category->categoryId = (int)$pcategory['id_category'];
			$category->parentId = (int)$pcategory['id_parent'];
			$category->updateDate = $pcategory['date_upd'];
			$category->localizations = $featureList;

			if(count($categories) > 1){
				array_push($categoryList, $category);
			}
		}
		
		if(!empty($categoryList)) {
			return $categoryList;
		}
		else {
			return $category;
		}

	}

	public function getFeatureList($categoryId) {
		$featureList = array();

		if (!$tmp = AEAdapter::getCategoryFeatures($categoryId)) {
			return array();
		}

		foreach ($tmp as $feature) {
			array_push($featureList, array("language" => $feature['iso_code'], "name" => $feature['name'], "description" => $feature['description']));
		}
	 	
	 	return $featureList;
	}

}

?>