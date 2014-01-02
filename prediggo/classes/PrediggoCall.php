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

require_once(_PS_MODULE_DIR_.'prediggo/classes_prediggo/PrediggoService.php');

class PrediggoCall
{
	/** @var array Recommendations parameters  */
	private $oRecoParam;

	/** @var string Web site id */
	private $sShopId;

	/** @var string Server URL (can be the search or recommendation server URL) */
	private $sServerUrl;

	/** @var float Execution time */
	private $execTime;

	/** @var array list of logs */
	private $_logs;

	/**
	  * Initialise the object variables
	  *
	  * @param string $sShopId Web site Id
	  * @param string $sServerUrl Server URL
	  */
	public function __construct($sShopId, $sServerUrl)
	{
		$this->sShopId = $sShopId;
		$this->sServerUrl = $sServerUrl;

		$this->execTime = microtime(true);

		$this->_logs = array();
	}

	/**
	  * Get Landing pages Recommendations, used for home & 404 pages
	  *
	  * @param array $params list of specific params
	  * @return array list of Product Recommendations
	  */
	public function getLandingPageRecommendations($params)
	{
		$this->oRecoParam = new GetLandingPageRecommendationParam();
		$this->oRecoParam->setRefererUrl(!empty($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'');

		return $this->getRecommendations($params, 'getLandingPageRecommendation');
	}

	/**
	  * Get Product pages Recommendations
	  *
	  * @param array $params list of specific params
	  * @return array list of Product Recommendations
	  */
	public function getProductRecommendations($params)
	{
		$this->oRecoParam = new GetItemRecommendationParam();
		$this->oRecoParam->getItemInfo()->setItemId((int)$params['id_product']);

		return $this->getRecommendations($params, 'getItemRecommendation');
	}

	/**
	  * Get Category page Recommendations
	  *
	  * @param array $params list of specific params
	  * @return array list of Product Recommendations
	  */
	public function getCategoyRecommendations($params)
	{
		$this->oRecoParam = new GetCategoryRecommendationParam();
		$this->oRecoParam->addCondition('genre', $params['category']->name);

		return $this->getRecommendations($params, 'getCategoryRecommendation');
	}

	/**
	  * Get Block layered Recommendations
	  *
	  * @param array $params list of specific params
	  * @return array list of Product Recommendations
	  */
	public function getBlockLayeredRecommendations($params)
	{
		$this->oRecoParam = new GetCategoryRecommendationParam();
		$this->oRecoParam->addCondition('genre', $params['category']->name);
		if(!empty($params['filters']))
			$this->addConditions($params['filters'], (int)$params['cookie']->id_lang);

		return $this->getRecommendations($params, 'getCategoryRecommendation');
	}

	/**
	  * Set block layered filters as prediggo conditions
	  *
	  * @param array $aFilters list of block layered filters
	  * @return integer $id_lang Lang id
	  */
	public function addConditions($aFilters = false, $id_lang)
	{
		if(!$aFilters
		|| !is_array($aFilters))
			return false;

		foreach($aFilters as $k => $aFilterValues)
		{
			if(is_array($aFilterValues)
			&& sizeof($aFilterValues))
			{
				switch($k)
				{
					case 'id_attribute_group' :
						foreach($aFilterValues as $aFilterValue)
						{
							$aIDS = explode('_', $aFilterValue);
							$oAttributeGroup = new AttributeGroup((int)$aIDS[0], (int)$id_lang);
							$oAttribute = new Attribute((int)$aIDS[1], (int)$id_lang);
							$this->oRecoParam->addCondition($oAttributeGroup->name, $oAttribute->name);
						}
					break;

					case 'id_feature' :
						foreach($aFilterValues as $aFilterValue)
						{
							$aIDS = explode('_', $aFilterValues);
							$oFeature = new Feature((int)$aIDS[0], (int)$id_lang);
							$oFeatureValue = new FeatureValue((int)$aIDS[1], (int)$id_lang);
							$this->oRecoParam->addCondition($oFeature->name, $oFeatureValue->name);
						}
					break;

					case 'manufacturer' :
						$this->oRecoParam->addCondition('supplierid', $aFilterValues[0]);
					break;


					case 'price' :
						$this->oRecoParam->addCondition('sellingprice', $aFilterValues[0]);
						//$this->oRecoParam->addCondition('sellingprice_max', $aFilterValues[1]);
					break;

					default : break;
				}
			}
		}
	}

	/**
	  * Get Customer pages Recommendations
	  *
	  * @param array $params list of specific params
	  * @return array list of Product Recommendations
	  */
	public function getCustomerRecommendations($params)
	{
		$this->oRecoParam = new GetUserRecommendationParam();
		$this->oRecoParam->setRecommendationMethodToUse( RecMethodConstants::MOST_RECENT_ITEMS );

		return $this->getRecommendations($params, 'getUserRecommendation');
	}

	/**
	  * Get Cart page Recommendations
	  *
	  * @param array $params list of specific params
	  * @return array list of Product Recommendations
	  */
	public function getCartRecommendations($params)
	{
		if(!isset($params['cart'])
		|| !(int)$params['cart']->id)
			return false;

		$this->oRecoParam = new GetBasketRecommendationParam();
		$aProducts = $params['cart']->getProducts();
		if(!count($aProducts))
			return false;

		foreach($params['cart']->getProducts() as $aProduct)
			$this->oRecoParam->addBasketItem((int)$params['cookie']->id_lang, $aProduct['id_product']);
		return $this->getRecommendations($params, 'getBasketRecommendation');

	}

	/**
	  * Get Best sales page Recommendations
	  *
	  * @param array $params list of specific params
	  * @return array list of Product Recommendations
	  */
	public function getBestSalesRecommendations($params)
	{
		$this->oRecoParam = new GetTopNSalesParam();

		return $this->getRecommendations($params, 'getTopNSales');
	}

	/**
	  * Set the main prediggo call params
	  *
	  * @param array $params list of specific params
	  */
	private function setMainParamData($params)
	{
		// If no session exists
		if (!isset($_SESSION))
			session_start();
			
		$this->oRecoParam->setServerUrl($this->sServerUrl);
		$this->oRecoParam->setShopId($this->sShopId);
		$this->oRecoParam->setSessionId(md5(session_id()));
		if(method_exists($this->oRecoParam, 'setLanguageCode'))
			$this->oRecoParam->setLanguageCode(Language::getIsoById((int)$params['cookie']->id_lang));
		return true;
	}

	/**
	  * Set the recommendations call params
	  *
	  * @param array $params list of specific params
	  */
	private function setRecommendationParamData($params)
	{
		if(!$this->setMainParamData($params))
			return false;

		$this->oRecoParam->setNbRecommendation((int)$params['nb_items']);
		$this->oRecoParam->setShowAds(false);
		$this->oRecoParam->setProfileMapId((int)Context::getContext()->shop->id);
		$this->oRecoParam->setUserId(($params['customer']->isLogged())?(int)$params['customer']->id:'0');
	}

	/**
	  * Get the product recommendations
	  *
	  * @param array $params list of specific params
	  * @param string $sFunction name of the prediggo function to call depending the type of page
	  * @return array containing the title of the block and the products
	  */
	private function getRecommendations($params, $sFunction)
	{
		$this->_logs[] = '[BEGIN] ['.date('c').'] ACTION : '.$sFunction;

		$this->setRecommendationParamData($params);

		$aItems = array();
		try
		{
			$this->_logs[] = '[LAUNCH] : '.$sFunction;
			if($oResult = call_user_func(array('PrediggoService', $sFunction), $this->oRecoParam))
			{
				$this->_logs[] = '[NB ITEMS ASKED] : '.(int)$params['nb_items'];
				$aItems = $this->getProducts($oResult, (int)$params['cookie']->id_lang);
				unset($oResult);
			}
		}
		catch(PrediggoException $ex)
		{
			$this->_logs[] = '[ERROR] : '.$ex->getMessage();
		}

		$this->execTime = number_format(microtime(true) - $this->execTime, 3, '.', '');

		$this->_logs[] = '[EXEC TIME] : '.$this->execTime;
		$this->_logs[] = '[END] ACTION : '.$sFunction;

		return array(
			'block_title' => $params['block_title'],
			'items' => $aItems
		);
	}

	/**
	  * Launch a notification to prediggo when a user is registered
	  *
	  * @param array $params list of specific params
	  */
	public function setUserRegistered($params)
	{
		$this->oRecoParam = new RegisterUserParam();
		$this->oRecoParam->setUserId((int)$params['customer']->id);

		$this->setNotification($params, 'RegisterUser');
	}

	/**
	  * Launch a notification to prediggo when a product recommendations is clicked
	  *
	  * @param array $params list of specific params
	  */
	public function setProductNotification($params)
	{
		$this->oRecoParam = new NotifyPrediggoParam();
		$this->oRecoParam->setNotificationId($params['notificationId']);

		$this->setNotification($params, 'notifyPrediggo');
	}

	/**
	  * Launch a notification to prediggo
	  *
	  * @param array $params list of specific params
	  * @param string $sFunction name of the prediggo function to call depending the type of notification
	  */
	private function setNotification($params, $sFunction)
	{
		$this->_logs[] = '[BEGIN] ['.date('c').'] ACTION : '.$sFunction;

		if(!$this->setMainParamData($params))
			return false;

		try
		{
			$this->_logs[] = '[LAUNCH] : '.$sFunction;
			$this->_logs[] = '[CUSTOMER/GUEST] : '.(($params['customer']->isLogged())?'customer'.(int)$params['customer']->id:'guest'.(int)$params['customer']->id_guest);
			$this->_logs[] = '[SESSIONID] : '.$this->oRecoParam->getSessionId();

			if($result = call_user_func(array('PrediggoService', $sFunction), $this->oRecoParam))
				$this->_logs[] = '[OK] Notification: '.$sFunction;
			else
				$this->_logs[] = '[ERROR] : '.$sFunction;
			unset($result);
		}
		catch(PrediggoException $ex)
		{
			$this->_logs[] = '[ERROR] : '.$ex->getMessage();
		}

		$this->execTime = number_format(microtime(true) - $this->execTime, 3, '.', '');

		$this->_logs[] = '[EXEC TIME] : '.$this->execTime;
		$this->_logs[] = '[END] ACTION : '.$sFunction;
	}

	/**
	  * Get the prediggo autocomplete function for the search block
	  *
	  * @param array $params list of specific params
	  * @return object PrediggoService
	  */
	public function getAutoComplete($params)
	{
		$this->oRecoParam = new AutoCompleteParam();

		if(!empty($params['filters']) && is_array($params['filters']))
			foreach($params['filters'] as $filter => $val)
				$this->oRecoParam->addCondition($filter, $val);

		$this->oRecoParam->setInputQuery($params['query']);
		return $this->setSearch($params, 'autoComplete');
	}

	/**
	  * Get the prediggo search products
	  *
	  * @param array $params list of specific params
	  * @return object PrediggoService
	  */
	public function getSearch($params)
	{
		$this->oRecoParam = new GetSearchPageRecommendationParam();

		if(!empty($params['filters']) && is_array($params['filters']))
			foreach($params['filters'] as $filter => $val)
				$this->oRecoParam->addCondition($filter, $val);

		$this->oRecoParam->setSearchString($params['query']);
		$this->oRecoParam->setNbRecommendation(0);
		$this->oRecoParam->setMaxNbResultsPerPage((int)$params['nb_items']);
		if(!empty($params['option']))
			$this->oRecoParam->setSearchRefiningOption($params['option']);

		return $this->setSearch($params, 'getSearchPageRecommendation');
	}

	/**
	  * Execute a prediggo search or autocompletion call
	  *
	  * @param array $params list of specific params
	  * @param string $sFunction name of the prediggo function to call depending the type of search
	  * @return object PrediggoService
	  */
	public function setSearch($params, $sFunction)
	{
		$this->_logs[] = '[BEGIN] ['.date('c').'] ACTION : '.$sFunction;

		if(!$this->setMainParamData($params))
			return false;

		$oResult = false;
		try
		{
			$this->_logs[] = '[LAUNCH] : '.$sFunction;
			$this->_logs[] = '[CUSTOMER/GUEST] : '.(($params['customer']->isLogged())?'customer'.(int)$params['customer']->id:'guest'.(int)$params['customer']->id_guest);
			$this->_logs[] = '[SESSIONID] : '.$this->oRecoParam->getSessionId();
			
			if($oResult = call_user_func(array('PrediggoService', $sFunction), $this->oRecoParam))
				$this->_logs[] = '[OK] : '.$sFunction;
			else
				$this->_logs[] = '[FAIL] : '.$sFunction;
		}
		catch(PrediggoException $ex)
		{
			$this->_logs[] = '[ERROR] : '.$ex->getMessage();
		}

		$this->execTime = number_format(microtime(true) - $this->execTime, 3, '.', '');

		$this->_logs[] = '[EXEC TIME] : '.$this->execTime;
		$this->_logs[] = '[END] ACTION : '.$sFunction;

		return $oResult;
	}

	/**
	  * Get the current extraction last
	  *
	  * @return float seconds
	  */
	public function getExecTime()
	{
		return 	$this->execTime;
	}

	/**
	  * Get the current extraction logs
	  *
	  * @return array _logs
	  */
	public function getLogs()
	{
		return $this->_logs;
	}

	/**
	  * Get the prediggo products suggestions (in autocompletion)
	  *
	  * @param PrediggoService $oResult prediggo object
	  * @param integer $id_lang Lang ID
	  * @param integer Number of products to display
	  * @return array of product data from Db
	  */
	public function getSuggestedProducts($oResult, $id_lang, $nb_items)
	{
		$aRecommendedItems = $oResult->getSuggestedProducts();

		if(empty($aRecommendedItems))
			return false;

		$this->_logs[] = '[NB ITEMS RECEIVED] : '.(int)sizeof($aRecommendedItems);

		$aRecommendedItems = array_slice($aRecommendedItems, 0, $nb_items);

		$aIds = array();

		foreach($aRecommendedItems as $oItem)
		{
			$iIDProduct = (int)$oItem->getProductId();
			$aIds[] = (int)$iIDProduct;
			$this->_logs[] = '[ITEM ID] : '.(int)$iIDProduct;
		}

		$sql = 'SELECT p.*, pa.`id_product_attribute`, pl.`description`, pl.`description_short`, pl.`available_now`, pl.`available_later`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, i.`id_image`, il.`legend`, m.`name` AS manufacturer_name, tl.`name` AS tax_name, t.`rate`, cl.`name` AS category_default, DATEDIFF(p.`date_add`, DATE_SUB(NOW(), INTERVAL '.(Validate::isUnsignedInt((int)Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? (int)Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY)) > 0 AS new,
					(p.`price` * IF(t.`rate`,((100 + (t.`rate`))/100),1)) AS orderprice
				FROM `'._DB_PREFIX_.'category_product` cp
				LEFT JOIN `'._DB_PREFIX_.'product` p ON p.`id_product` = cp.`id_product`
				LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (p.`id_product` = pa.`id_product` AND default_on = 1)
				LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (p.`id_category_default` = cl.`id_category` AND cl.`id_lang` = '.(int)($id_lang).')
				LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.(int)($id_lang).')
				LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
				LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)($id_lang).')
				LEFT JOIN `'._DB_PREFIX_.'tax_rule` tr ON (p.`id_tax_rules_group` = tr.`id_tax_rules_group`
				                                           AND tr.`id_country` = '.(int)Context::getContext()->country->id.'
			                                           	   AND tr.`id_state` = 0)
			    LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = tr.`id_tax`)
				LEFT JOIN `'._DB_PREFIX_.'tax_lang` tl ON (t.`id_tax` = tl.`id_tax` AND tl.`id_lang` = '.(int)($id_lang).')
				LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON m.`id_manufacturer` = p.`id_manufacturer`
				WHERE p.`id_product` IN ( '.join(',',$aIds).') GROUP BY p.`id_product`';

		$aQueryResult = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
		$aItemsTmp = Product::getProductsProperties($id_lang, $aQueryResult);
		$aItems = array();
		foreach($aRecommendedItems as $l => $oItem)
			foreach($aItemsTmp as $k => $aItem)
				if((int)$aItem['id_product'] == (int)$oItem->getProductId())
				{
					$aItemsTmp[$k]['notificationId'] = $oItem->getNotificationId();
					$aItems[$l] = $aItemsTmp[$k];
				}
		unset($sql);
		unset($aRecommendedItems);
		unset($aQueryResult);
		return $aItems;
	}

	/**
	  * Get the products data from the prestashop Db by the retrieved recommendations Ids
	  *
	  * @param PrediggoService $oResult prediggo object
	  * @param integer $id_lang Lang ID
	  * @return array of product data from Db
	  */
	public function getProducts($oResult, $id_lang)
	{
		$aRecommendedItems = $oResult->getRecommendedItems();

		if(empty($aRecommendedItems))
			return false;

		$this->_logs[] = '[NB ITEMS RECEIVED] : '.(int)sizeof($aRecommendedItems);

		$aIds = array();
		foreach($aRecommendedItems as $oItem)
		{
			$iIDProduct = (int)$oItem->getItemId();
			$aIds[] = (int)$iIDProduct;
			$this->_logs[] = '[ITEM ID] : '.(int)$iIDProduct;
		}

		$sql = 'SELECT p.*, pa.`id_product_attribute`, pl.`description`, pl.`description_short`, pl.`available_now`, pl.`available_later`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, i.`id_image`, il.`legend`, m.`name` AS manufacturer_name, tl.`name` AS tax_name, t.`rate`, cl.`name` AS category_default, DATEDIFF(p.`date_add`, DATE_SUB(NOW(), INTERVAL '.(Validate::isUnsignedInt((int)Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? (int)Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY)) > 0 AS new,
					(p.`price` * IF(t.`rate`,((100 + (t.`rate`))/100),1)) AS orderprice
				FROM `'._DB_PREFIX_.'category_product` cp
				LEFT JOIN `'._DB_PREFIX_.'product` p ON p.`id_product` = cp.`id_product`
				LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (p.`id_product` = pa.`id_product` AND default_on = 1)
				LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (p.`id_category_default` = cl.`id_category` AND cl.`id_lang` = '.(int)($id_lang).')
				LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.(int)($id_lang).')
				LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
				LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)($id_lang).')
				LEFT JOIN `'._DB_PREFIX_.'tax_rule` tr ON (p.`id_tax_rules_group` = tr.`id_tax_rules_group`
				                                           AND tr.`id_country` = '.(int)Context::getContext()->country->id.'
			                                           	   AND tr.`id_state` = 0)
			    LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = tr.`id_tax`)
				LEFT JOIN `'._DB_PREFIX_.'tax_lang` tl ON (t.`id_tax` = tl.`id_tax` AND tl.`id_lang` = '.(int)($id_lang).')
				LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON m.`id_manufacturer` = p.`id_manufacturer`
				WHERE p.`id_product` IN ( '.join(',',$aIds).') GROUP BY p.`id_product`';

		$aQueryResult = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
		$aItemsTmp = Product::getProductsProperties($id_lang, $aQueryResult);
		$aItems = array();
		foreach($aRecommendedItems as $l => $oItem)
			foreach($aItemsTmp as $k => $aItem)
				if((int)$aItem['id_product'] == (int)$oItem->getItemId())
				{
					$aItemsTmp[$k]['notificationId'] = $oItem->getNotificationId();
					$aItems[$l] = $aItemsTmp[$k];
				}
		unset($sql);
		unset($aRecommendedItems);
		unset($aQueryResult);
		return $aItems;

	}
	
	/**
	 * Check the client web site id with a light call
	 */
	public function checkWebSiteId()
	{
		try
		{
			$oRecoParam = new GetCategoryRecommendationParam();
			$oRecoParam->setServerUrl($this->sServerUrl);
			$oRecoParam->setShopId($this->sShopId);
			$oRecoParam->setSessionId(md5(session_id()));
			$oRecoParam->addCondition('genre', 'Home');
			if(call_user_func(array('PrediggoService', 'getCategoryRecommendation'), $oRecoParam))
				return true;
		}
		catch(PrediggoException $ex)
		{
			return false;
		}
	}
}