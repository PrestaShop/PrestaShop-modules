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

class PrediggoConfig
{
	/** @var ImportConfig Singleton Object */
	private static $instance;
	
	/** @var Context Singleton Object */
	private $oContext;
	
	/** @var array list of configuration variables */
	private $aConfs = array(
		'web_site_id' => array(
			'name' 				=> 'PREDIGGO_WEB_SITE_ID',
			'type'				=> 'text',
			'val' 				=> 'WineDemo_Fake_Shop_ID_123456789',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> false,
		),
		'web_site_id_checked' => array(
			'name' 				=> 'PREDIGGO_WEB_SITE_ID_CHECKED',
			'type'				=> 'int',
			'val' 				=> '0',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> false,
		),
			
		/* EXPORT CONFIGURATION */
		// The FG Corresponds to FILE_GENERATION
		'logs_file_generation' => array(
			'name' 				=> 'PREDIGGO_LOGS_FG',
			'type'				=> 'int',
			'val' 				=> 0,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> false,
		),
		'products_file_generation' => array(
			'name' 				=> 'PREDIGGO_PRODUCTS_FG',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'orders_file_generation' => array(
			'name' 				=> 'PREDIGGO_ORDERS_FG',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'customers_file_generation' => array(
			'name' 				=> 'PREDIGGO_CUSTOMERS_FG',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'export_product_image' => array(
			'name' 				=> 'PREDIGGO_EXPORT_PRODUCT_IMG',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'export_product_description' => array(
			'name' 				=> 'PREDIGGO_EXPORT_PRODUCT_DESC',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'export_product_min_quantity' => array(
			'name' 				=> 'PREDIGGO_EXPORT_PRODUCT_MIN_QTY',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'nb_days_order_valide' => array(
			'name' 				=> 'PREDIGGO_NB_DAYS_ORDER',
			'type'				=> 'int',
			'val' 				=> 180,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'nb_days_customer_last_visit_valide' => array(
			'name' 				=> 'PREDIGGO_NB_DAYS_CUSTOMER',
			'type'				=> 'int',
			'val' 				=> 180,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'attributes_groups_ids' => array(
			'name' 				=> 'PREDIGGO_ATTRIBUTES_GROUPS_IDS',
			'type'				=> 'array',
			'val' 				=> '',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'features_ids' => array(
			'name' 				=> 'PREDIGGO_FEATURES_IDS',
			'type'				=> 'array',
			'val' 				=> '',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'products_ids_not_recommendable' => array(
			'name' 				=> 'PREDIGGO_PRODUCTS_NOT_RECO',
			'type'				=> 'array',
			'val' 				=> '',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'products_ids_not_searchable' => array(
			'name' 				=> 'PREDIGGO_PRODUCTS_NOT_SEARCH',
			'type'				=> 'array',
			'val' 				=> '',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		
		/* PROTECTION CONFIGURATION */
		'htpasswd_user' => array(
			'name' 				=> 'PREDIGGO_HTPASSWD_USER',
			'type'				=> 'text',
			'val' 				=> 'user',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> false,
		),
		'htpasswd_pwd' => array(
			'name' 				=> 'PREDIGGO_HTPASSWD_PWD',
			'type'				=> 'text',
			'val' 				=> 'pwd',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> false,
		),
		
		/* RECOMMENDATIONS CONFIGURATION */
		'logs_reco_file_generation' => array(
			'name' 				=> 'PREDIGGO_RECO_LOGS_FG',
			'type'				=> 'int',
			'val' 				=> 0,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'server_url_recommendations' => array(
			'name' 				=> 'PREDIGGO_SERVER_URL_RECO',
			'type'				=> 'text',
			'val' 				=> 'http://demo.prediggo.com:8091',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'home_recommendations' => array(
			'name' 				=> 'PREDIGGO_HOME_RECO',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'home_nb_items' => array(
			'name' 				=> 'PREDIGGO_HOME_NB_RECO',
			'type'				=> 'int',
			'val' 				=> 5,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'home_block_title' => array(
			'name' 				=> 'PREDIGGO_HOME_BLOCK_TITLE',
			'type'				=> 'text',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		
		'error_recommendations' => array(
			'name' 				=> 'PREDIGGO_ERROR_RECO',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'error_nb_items' => array(
			'name' 				=> 'PREDIGGO_ERROR_NB_RECO',
			'type'				=> 'int',
			'val' 				=> 6,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'error_block_title' => array(
			'name' 				=> 'PREDIGGO_ERROR_BLOCK_TITLE',
			'type'				=> 'text',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		
		'product_recommendations' => array(
			'name' 				=> 'PREDIGGO_PRODUCT_RECO',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'product_nb_items' => array(
			'name' 				=> 'PREDIGGO_PRODUCT_NB_RECO',
			'type'				=> 'int',
			'val' 				=> 6,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'product_block_title' => array(
			'name' 				=> 'PREDIGGO_PRODUCT_BLOCK_TITLE',
			'type'				=> 'text',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		
		'category_recommendations' => array(
			'name' 				=> 'PREDIGGO_CATEGORY_RECO',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'category_nb_items' => array(
			'name' 				=> 'PREDIGGO_CATEGORY_NB_RECO',
			'type'				=> 'int',
			'val' 				=> 6,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'category_block_title' => array(
			'name' 				=> 'PREDIGGO_CATEGORY_BLOCK_TITLE',
			'type'				=> 'text',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		
		'customer_recommendations' => array(
			'name' 				=> 'PREDIGGO_CUSTOMER_RECO',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'customer_nb_items' => array(
			'name' 				=> 'PREDIGGO_CUSTOMER_NB_RECO',
			'type'				=> 'int',
			'val' 				=> 6,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'customer_block_title' => array(
			'name' 				=> 'PREDIGGO_CUSTOMER_BLOCK_TITLE',
			'type'				=> 'text',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		
		'cart_recommendations' => array(
			'name' 				=> 'PREDIGGO_CART_RECO',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'cart_nb_items' => array(
			'name' 				=> 'PREDIGGO_CART_NB_RECO',
			'type'				=> 'int',
			'val' 				=> 6,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'cart_block_title' => array(
			'name' 				=> 'PREDIGGO_CART_BLOCK_TITLE',
			'type'				=> 'text',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		
		'best_sales_recommendations' => array(
			'name' 				=> 'PREDIGGO_BEST_SALES_RECO',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'best_sales_nb_items' => array(
			'name' 				=> 'PREDIGGO_BEST_SALES_NB_RECO',
			'type'				=> 'int',
			'val' 				=> 6,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'best_sales_block_title' => array(
			'name' 				=> 'PREDIGGO_BEST_SALES_BLOCK_TITLE',
			'type'				=> 'text',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		
		'blocklayered_recommendations' => array(
			'name' 				=> 'PREDIGGO_LAYERED_RECO',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'blocklayered_nb_items' => array(
			'name' 				=> 'PREDIGGO_LAYERED_NB_RECO',
			'type'				=> 'int',
			'val' 				=> 3,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'blocklayered_block_title' => array(
			'name' 				=> 'PREDIGGO_LAYERED_BLOCK_TITLE',
			'type'				=> 'text',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		
		/* SEARCHS CONFIGURATION */
		'search_active' => array(
			'name' 				=> 'PREDIGGO_SEARCH_ACTIVE',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'search_nb_items' => array(
			'name' 				=> 'PREDIGGO_SEARCH_NB_ITEMS',
			'type'				=> 'int',
			'val' 				=> 10,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'search_nb_min_chars' => array(
			'name' 				=> 'PREDIGGO_SEARCH_NB_MIN_CHARS',
			'type'				=> 'int',
			'val' 				=> 3,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'logs_search_file_generation' => array(
			'name' 				=> 'PREDIGGO_SEARCH_LOGS_FG',
			'type'				=> 'int',
			'val' 				=> 0,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'server_url_search' => array(
			'name' 				=> 'PREDIGGO_SERVER_URL_SEARCH',
			'type'				=> 'text',
			'val' 				=> 'http://demo.prediggo.com:8091',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'searchandizing_active' => array(
			'name' 				=> 'PREDIGGO_SEARCHANDIZING_ACTIVE',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'layered_navigation_active' => array(
			'name' 				=> 'PREDIGGO_LAYERED_NAV_ACTIVE',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'autocompletion_active' => array(
			'name' 				=> 'PREDIGGO_AUTOCOMPLETION_ACTIVE',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'autocompletion_nb_items' => array(
			'name' 				=> 'PREDIGGO_AUTOCOMPLETION_NB_ITEMS',
			'type'				=> 'int',
			'val' 				=> 6,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'suggest_active' => array(
			'name' 				=> 'PREDIGGO_SUGGEST_ACTIVE',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'suggest_words' => array(
			'name' 				=> 'PREDIGGO_SUGGEST_WORDS',
			'type'				=> 'text',
			'val' 				=> array(1 => 'iPad 2, iPhone 4S, iPhone', 2 => 'iPad 2, iPhone 4S, iPhone', 3 => 'iPad 2, iPhone 4S, iPhone', 4 => 'iPad 2, iPhone 4S, iPhone', 5 => 'iPad 2, iPhone 4S, iPhone'),
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		
	);

    /**
	  * Initialise the object variables
	  *
	  */
	public function __construct($oContext = false)
	{
		if(is_object($oContext)
		&& get_class($oContext) == 'Context')
			$this->oContext = $oContext;
		
		$aLanguages = Language::getLanguages(false);
		
		foreach($this->aConfs as $var => $aConf)
		{
			$aParams = array(
				0 => $aConf['name'],
				1 => false,
				2 => false,
				3 => false,
			);
			
			if($this->oContext)
			{
				if((int)$aConf['multishopgroup'])
					$aParams[2] = (int)$this->oContext->shop->id_shop_group;
				if((int)$aConf['multishop'])
					$aParams[3] = (int)$this->oContext->shop->id;
			}
				
			switch($aConf['type'])
			{
				case 'int' :
					$this->{$var} = (int)call_user_func_array(array('Configuration', 'get'), $aParams);
				break;
				default :
					if($this->oContext && (int)$aConf['multilang'])
					{
						// Set the multilingual configurations
						foreach($aLanguages as $aLanguage)
						{
							$aParams[1] = (int)$aLanguage['id_lang'];
							$this->{$var}[(int)$aLanguage['id_lang']] = pSQL(call_user_func_array(array('Configuration', 'get'), $aParams));
						}
					}
					else
						$this->{$var} = pSQL(call_user_func_array(array('Configuration', 'get'), $aParams));
				break;
			}
		}
	}

	/**
	 * Set the Import Configuration vars, executed by the main module object once its installation
	 *
	 * @return bool success or false
	 */
	public function install()
	{
		foreach($this->aConfs as $var => $aConf)
		{
			$aParams = array(
				0 => $aConf['name'],
				1 => $aConf['val'],
				2 => false,
				3 => false,
				4 => false,
			);
				
			if($this->oContext)
			{
				if((int)$aConf['multishopgroup'])
					$aParams[3] = (int)$this->oContext->shop->id_shop_group;
				if((int)$aConf['multishop'])
					$aParams[4] = (int)$this->oContext->shop->id;
			}
			
			if(!call_user_func_array(array('Configuration', 'updateValue'), $aParams))
				return false;
		}
		return true;
	}

	/**
	 * Delete the Import Configuration vars, executed by the main module object once its uninstallation
	 *
	 * @return bool success or false
	 */
	public function uninstall()
	{
		foreach($this->aConfs as $var => $aConf)
			if(!Configuration::deleteByName($aConf['name']))
				return false;
		return true;
	}

	/**
	 * Update the Import Configuration vars, executed by the main module object once its uninstallation
	 *
	 * @return bool success or false
	 */
	public function save()
	{
		foreach($this->aConfs as $var => $aConf)
		{
			$aParams = array(
				0 => $aConf['name'],
				1 => $this->{$var},
				2 => false,
				3 => false,
				4 => false,
			);
			
			if($this->oContext)
			{
				if((int)$aConf['multishopgroup'])
					$aParams[3] = (int)$this->oContext->shop->id_shop_group;
				if((int)$aConf['multishop'])
					$aParams[4] = (int)$this->oContext->shop->id;
			}
			
			switch($aConf['type'])
			{
				case 'int' :
					$aParams[1] = (int)$this->{$var};
					if(!call_user_func_array(array('Configuration', 'updateValue'), $aParams))
						return false;
				break;
				default :
					if(!call_user_func_array(array('Configuration', 'updateValue'), $aParams))
						return false;
				break;
			}
		}
		return true;
	}
	
	public function getContext()
	{
		return $this->oContext;	
	}
}