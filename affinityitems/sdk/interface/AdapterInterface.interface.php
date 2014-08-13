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

interface AdapterInterface {

	public static function countCategory($clause);

	public static function newCategoryClause();

	public static function updateCategoryClause();

	public static function deleteCategoryClause();

	public static function getCategoryList($clause, $bulk);

	public static function getCategoryFeatures($categoryId);

	// Repository

	public static function insertCategory($category);

	public static function updateCategory($category);

	public static function deleteCategory($category);

	/*
	 *	Product
	*/

	// Synchronize

	public static function countProduct($clause);

	public static function newProductClause();

	public static function updateProductClause();

	public static function deleteProductClause();

	public static function getProductList($clause, $bulk);

	public static function getProductsLocalizations($productId);

	public static function getProductCategories($productId);

	public static function getProductTags($productId, $isoCode);

	public static function getProductPrices($productId);

	public static function getProductAttributes($productId, $isoCode);

	public static function getProductFeatures($productId, $isoCode);

	// Repository

	public static function insertProduct($product);

	public static function updateProduct($product);

	public static function deleteProduct($product);
	
	/*
	 * Cart 
	*/

	public static function countCart($clause);

	public static function getCartList($sql);

	public static function getCartsProductAttributes($productIdAttribute);

	public static function newMemberCartClause($bulk);

	public static function updateMemberCartClause($bulk);

	public static function deleteMemberCartClause($bulk);

	public static function newGuestCartClause($cartId);

	public static function deleteGuestCartClause($cartId);

	public static function castGuestCartClause($guestId);

	/*
	 *Order
	*/

	public static function countOrder();

	public static function getOrderList($bulk);

	public static function getOrderLines($orderId);

	/*
	 * Action 
	*/

	// Synchronize

	public static function countAction();

	public static function getActionList($bulk);

	public static function getMemberActionList($memberId);

	public static function getGuestActionList($guestId);
	
	// Repository

	public static function insertAction($action);

	public static function deleteAction($action);

	public static function insertOrder($order);

	public static function insertCart($cart);

	public static function updateCart($cart);

	public static function deleteCart($cart);


	/*
	 * AB Testing
	*/

	public static function getMemberGroup($person);

	public static function setMemberGroup($person, $group);

	/*
	 * Recommendation 
	*/

	public static function getRecommendationSelect();

	public static function getRecommendationTax();

	public static function renderRecommendation($select, $tax, $productPool, $langId);

	public static function renderDegradedMod($select, $tax, $langId);

	/*
	 * Properties
	*/

	public static function getHost();

	public static function getPort();

	public static function getSiteId();

	public static function getSecurityKey();

	public static function getStartDate();

	public static function getEndDate();
	
	public static function getLock();

	public static function getStep();
	
	public static function authentication($email, $password, $siteId, $securityKey);

	public static function setStartDate($timestamp);

	public static function setEndDate($timestamp);

	public static function setLock($state);

	public static function setStep($step);

	public static function getShopName();

	public static function getSyncDiff();
	
}

?>