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
	public $categoryPageName='category';
	public $manufacturerPageName='manufacturer';
	public $attNameCategory='genre';
	public $attNameManufacturer='supplierid';
	
	public $optionsHooksHomePage= array
	(
		  array(
				'id_option' => 'displayHome',                
				'name' => 'Hook: displayHome - Called at the center of the homepage' 
			),
		array(
				'id_option' => 'displayOrderConfirmation',                
				'name' => 'Hook: displayOrderConfirmation - This hook is called on order confirmation page.' 
			),
		);
		
	public $optionsHooksAllPage= array
	(
		array(
		    'id_option' => 'displayHeader',
		    'name' => 'Hook: displayHeader - Called within the HTML <head> tags. Ideal location for adding JavaScript and CSS files.'
		  ),
		  array(
		    'id_option' => 'displayTop',
		    'name' => 'Hook: displayTop - Called in the page\'s header.'
		  ),
		  array(
		    'id_option' => 'displayLeftColumn',
		    'name' => 'Hook: displayLeftColumn - Called when loading the left column.'
		  ),
		  array(
		    'id_option' => 'displayRightColumn',
		    'name' => 'Hook: displayRightColumn - Called when loading the right column.'
		  ),
		  array(
		    'id_option' => 'displayFooter',
		    'name' => 'Hook: displayFooter - Called in the page\'s footer.'
		  ),
	);

    public $optionsHooksBlocklayeredPage= array
    (
        array(
            'id_option' => 'displayHeader',
            'name' => 'Hook: displayHeader - Called within the HTML <head> tags. Ideal location for adding JavaScript and CSS files.'
        ),
        array(
            'id_option' => 'displayTop',
            'name' => 'Hook: displayTop - Called in the page\'s header.'
        ),
        array(
            'id_option' => 'displayLeftColumn',
            'name' => 'Hook: displayLeftColumn - Called when loading the left column.'
        ),
        array(
            'id_option' => 'displayRightColumn',
            'name' => 'Hook: displayRightColumn - Called when loading the right column.'
        ),
        array(
            'id_option' => 'displayFooter',
            'name' => 'Hook: displayFooter - Called in the page\'s footer.'
        ),
    );
	
	public $optionsHooksProductPage= array
	(
		  array(
				'id_option' => 'displayLeftColumnProduct',                
				'name' => 'Hook: displayLeftColumnProduct - Called right before the "Print" link, under the picture.' 
			),
			array(
				'id_option' => 'displayRightColumnProduct',                
				'name' => 'Hook: displayRightColumnProduct - Called right after the block for the "Add to Cart" button.' 
			),
			array(
				'id_option' => 'displayProductButtons',                
				'name' => 'Hook: displayProductButtons - Called inside the block for the "Add to Cart" button, right after that button.' 
			),
			array(
				'id_option' => 'actionProductOutOfStock',                
				'name' => 'Hook: actionProductOutOfStock - Called inside the block for the "Add to Cart" button, right after the "Availability" information.' 
			),
			array(
				'id_option' => 'displayFooterProduct',                
				'name' => 'Hook: displayFooterProduct - Called right before the tabs.' 
			),
			array(
				'id_option' => 'displayProductTab',                
				'name' => 'Hook: displayProductTab - Called in tabs list, such as "More info", "Data sheet", "Accessories", etc.' 
			),
			array(
				'id_option' => 'displayProductTabContent',                
				'name' => 'Hook: displayProductTabContent - Called when a tab is clicked.' 
			),
		);
	
	public $optionsHooksBasketPage= array
	(
		  array(
				'id_option' => 'displayShoppingCart',                
				'name' => 'Hook: displayShoppingCart - Called after the cart\'s table of items, right above the navigation buttons.' 
			),
			array(
				'id_option' => 'displayShoppingCartFooter',                
				'name' => 'Hook: displayShoppingCartFooter - Called right below the cart items table.' 
			),
			array(
				'id_option' => 'displayOrderDetail',                
				'name' => 'Hook: displayOrderDetail - Displayed on order detail on front office.' 
			),
			array(
				'id_option' => 'displayBeforeCarrier',                
				'name' => 'Hook: displayBeforeCarrier - This hook is display before the carrier list on Front office.' 
			),
			array(
				'id_option' => 'displayCarrierList',                
				'name' => 'Hook: displayCarrierList - This hook is display during the carrier list on Front office.' 
			),
		);

	public $optionsHooksCategoryPage= array
	(
		array(
		    'id_option' => 'displayHeadercategory',
		    'name' => 'Hook: displayHeaderCategory - Called within the HTML <head> tags. Ideal location for adding JavaScript and CSS files.'
		  ),
		  array(
		    'id_option' => 'displayTopcategory',
		    'name' => 'Hook: displayTopCategory - Called in the  page\'s header on a category page, before the displayTop hook.'
		  ),
		  array(
		    'id_option' => 'displayLeftColumncategory',
		    'name' => 'Hook: displayLeftColumnCategory - Called when loading the left column on a category page, before the displayLeftColumn hook.'
		  ),
		  array(
		    'id_option' => 'displayRightColumncategory',
		    'name' => 'Hook: displayRightColumnCategory - Called when loading the right column on a category page, before the displayRightColumn hook.'
		  ),
		  array(
		    'id_option' => 'displayFootercategory',
		    'name' => 'Hook: displayFooterCategory - Called in the page\'s footer on a category page, before the displayFooter hook.'
		  ),
		  array(
		    'id_option' => 'displayHeadermanufacturer',
		    'name' => 'Hook: displayHeaderManufacturer - Called within the HTML <head> tags on a manufacturer page. Ideal location for adding JavaScript and CSS files.'
		  ),
		  array(
		    'id_option' => 'displayTopmanufacturer',
		    'name' => 'Hook: displayTopManufacturer - Called in the  page\'s header on a manufacturer page, before the displayTop hook.'
		  ),
		  array(
		    'id_option' => 'displayLeftColumnmanufacturer',
		    'name' => 'Hook: displayLeftColumnManufacturer - Called when loading the  left column on a manufacturer page, before the displayLeftColumn hook.'
		  ),
		  array(
		    'id_option' => 'displayRightColumnmanufacturer',
		    'name' => 'Hook: displayRightColumnManufacturer - Called when loading the  right column on a manufacturer page, before the displayRightColumn hook.'
		  ),
		  array(
		    'id_option' => 'displayFootermanufacturer',
		    'name' => 'Hook: displayFooterManufacturer - Called in the page\'s footer on a manufacturer page, before the displayFooter hook.'
		  ),
	);

    public $optionsHooksCustomerPage= array
    (
        array(
            'id_option' => 'displayCustomerAccount',
            'name' => 'Hook: displayCustomerAccount - Called on the client account homepage, after the list of available links. Ideal location to add a link to this list.'
        ),
        array(
            'id_option' => 'displayMyAccountBlock',
            'name' => 'Hook: displayMyAccountBlock - Called within the "My account" block, in the left column, below the list of available links. This is the ideal location to add a link to this list.'
        ),
        array(
            'id_option' => 'displayMyAccountBlockfooter',
            'name' => 'Hook: displayMyAccountBlockfooter - Displays extra information inside the "My account" block.'
        ),
    );


	/** @var ImportConfig Singleton Object */
	private static $instance;

	/** @var Context Singleton Object */
	private $oContext;

	/** @var array list of configuration variables */
	private $aConfs = array(
		'web_site_id' => array(
			'name' 				=> 'PREDIGGO_WEB_SITE_ID',
			'type'				=> 'text',
			'val' 				=> 'web_site_ID',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> false,
		),
		'web_site_id_checked'   => array(
			'name' 				=> 'PREDIGGO_WEB_SITE_ID_CHECKED',
			'type'				=> 'int',
			'val' 				=> '0',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> false,
		),
        'shop_name'             => array(
            'name' 				=> 'PREDIGGO_SHOP_NAME',
            'type'				=> 'text',
            'val' 				=> 'Shop_name',
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> false,
        ),
        'token_id'             => array(
            'name' 				=> 'PREDIGGO_TOKEN_ID',
            'type'				=> 'text',
            'val' 				=> 'Token_ID',
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> false,
        ),
        'server_url_recommendations' => array(
            'name' 				=> 'PREDIGGO_SERVER_URL_RECO',
            'type'				=> 'text',
            'val' 				=> 'http://monshop.prediggo.com',
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'gateway_profil_id'     => array(
            'name' 				=> 'PREDIGGO_PROFIL_ID',
            'type'				=> 'text',
            'val' 				=> 'Profil_ID',
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> false,
        ),
        'server_url_check' => array(
            'name' 				=> 'PREDIGGO_SERVER_URL_CHECK',
            'type'				=> 'text',
            'val' 				=> 'http://www.prediggo.com/prestakeys/',
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),

		/* EXPORT CONFIGURATION */
		// The FG Corresponds to FILE_GENERATION
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

        /* LOG CONFIGURATION */

        'logs_generation' => array(
            'name' 				=> 'PREDIGGO_LOGS',
            'type'				=> 'int',
            'val' 				=> 0,
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        /*'logs_reco_file_generation' => array(
            'name' 				=> 'PREDIGGO_RECO_LOGS',
            'type'				=> 'int',
            'val' 				=> 0,
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
        'logs_file_generation' => array(
            'name' 				=> 'PREDIGGO_LOGS_FG',
            'type'				=> 'int',
            'val' 				=> 0,
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> false,
        ),*/

		/* RECOMMENDATIONS CONFIGURATION */

        'hook_left_column' => array(
            'name' 				=> 'PREDIGGO_HOOK_LEFT_C',
            'type'				=> 'int',
            'val' 				=> 0,
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'hook_right_column' => array(
            'name' 				=> 'PREDIGGO_HOOK_RIGHT_C',
            'type'				=> 'int',
            'val' 				=> 0,
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'hook_footer' => array(
            'name' 				=> 'PREDIGGO_HOOK_HEADER_FOOTER',
            'type'				=> 'int',
            'val' 				=> 0,
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'hook_home' => array(
            'name' 				=> 'PREDIGGO_HOOK_HOME',
            'type'				=> 'int',
            'val' 				=> 0,
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'hook_footer_product' => array(
            'name' 				=> 'PREDIGGO_HOOK_FOOT_PROD',
            'type'				=> 'int',
            'val' 				=> 0,
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'hook_left_column_product' => array(
            'name' 				=> 'PREDIGGO_HOOK_LEFT_C_PROD',
            'type'				=> 'int',
            'val' 				=> 0,
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'hook_right_column_product' => array(
            'name' 				=> 'PREDIGGO_HOOK_RIGHT_C_PROD',
            'type'				=> 'int',
            'val' 				=> 0,
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'hook_shopping_cart_footer' => array(
            'name' 				=> 'PREDIGGO_HOOK_SHOP_CART_FOOT',
            'type'				=> 'int',
            'val' 				=> 0,
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'hook_shopping_cart' => array(
            'name' 				=> 'PREDIGGO_HOOK_SHOP_CART',
            'type'				=> 'int',
            'val' 				=> 0,
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'hook_order_detail' => array(
            'name' 				=> 'PREDIGGO_HOOK_ORDER_DET',
            'type'				=> 'int',
            'val' 				=> 0,
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'hook_product_tab' => array(
            'name' 				=> 'PREDIGGO_HOOK_PROD_TAB',
            'type'				=> 'int',
            'val' 				=> 0,
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'hook_before_carrier' => array(
            'name' 				=> 'PREDIGGO_HOOK_BEF_CAR',
            'type'				=> 'int',
            'val' 				=> 0,
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'hook_carrier_list' => array(
            'name' 				=> 'PREDIGGO_HOOK_CAR_LIST',
            'type'				=> 'int',
            'val' 				=> 0,
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),

		/* HOME PAGE CONFIGURATION - Block 0*/
		'home_0_activated' => array(
			'name' 				=> 'PREDIGGO_HOME_0_ACTIVATED',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'home_0_nb_items' => array(
			'name' 				=> 'PREDIGGO_HOME_0_NB_RECO',
			'type'				=> 'int',
			'val' 				=> 5,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'home_0_variant_id' => array(
			'name' 				=> 'PREDIGGO_HOME_0_VARIANT_ID',
			'type'				=> 'int',
			'val' 				=> 0,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'home_0_hook_name' => array(
			'name' 				=> 'PREDIGGO_HOME_0_HOOK_NAME',
			'type'				=> 'select',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'home_0_template_name' => array(
			'name' 				=> 'PREDIGGO_HOME_0_TEMPLATE_NAME',
			'type'				=> 'text',
			'size'     			=> 100,
			'val' 				=> 'list_recommendations.tpl',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'home_0_block_label' => array(
			'name' 				=> 'PREDIGGO_HOME_0_BLOCK_LABEL',
			'type'				=> 'text',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		
		/* HOME PAGE CONFIGURATION - Block 1*/
		'home_1_activated' => array(
			'name' 				=> 'PREDIGGO_HOME_1_ACTIVATED',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'home_1_nb_items' => array(
			'name' 				=> 'PREDIGGO_HOME_1_NB_RECO',
			'type'				=> 'int',
			'val' 				=> 5,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'home_1_variant_id' => array(
			'name' 				=> 'PREDIGGO_HOME_1_VARIANT_ID',
			'type'				=> 'int',
			'val' 				=> 0,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'home_1_hook_name' => array(
			'name' 				=> 'PREDIGGO_HOME_1_HOOK_NAME',
			'type'				=> 'select',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'home_1_template_name' => array(
			'name' 				=> 'PREDIGGO_HOME_1_TEMPLATE_NAME',
			'type'				=> 'text',
			'size'     			=> 100,
			'val' 				=> 'list_recommendations.tpl',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'home_1_block_label' => array(
			'name' 				=> 'PREDIGGO_HOME_1_BLOCK_LABEL',
			'type'				=> 'text',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),

		/* ALL PAGE CONFIGURATION - Block 0 */
		'allpage_0_activated' => array(
			'name' 				=> 'PREDIGGO_ALLPAGE_0_ACTIVATED',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'allpage_0_nb_items' => array(
			'name' 				=> 'PREDIGGO_ALLPAGE_0_NB_RECO',
			'type'				=> 'int',
			'val' 				=> 5,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'allpage_0_variant_id' => array(
			'name' 				=> 'PREDIGGO_ALLPAGE_0_VARIANT_ID',
			'type'				=> 'int',
			'val' 				=> 0,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'allpage_0_hook_name' => array(
			'name' 				=> 'PREDIGGO_ALLPAGE_0_HOOK_NAME',
			'type'				=> 'select',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'allpage_0_template_name' => array(
			'name' 				=> 'PREDIGGO_ALLPAGE_0_TEMPLATE_NAME',
			'type'				=> 'text',
			'size'     			=> 100,
			'val' 				=> 'list_recommendations.tpl',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'allpage_0_block_label' => array(
			'name' 				=> 'PREDIGGO_ALLPAGE_0_BLOCK_LABEL',
			'type'				=> 'text',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		
		/* ALL PAGE CONFIGURATION - Block 0 */
		'allpage_1_activated' => array(
			'name' 				=> 'PREDIGGO_ALLPAGE_1_ACTIVATED',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'allpage_1_nb_items' => array(
			'name' 				=> 'PREDIGGO_ALLPAGE_1_NB_RECO',
			'type'				=> 'int',
			'val' 				=> 5,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'allpage_1_variant_id' => array(
			'name' 				=> 'PREDIGGO_ALLPAGE_1_VARIANT_ID',
			'type'				=> 'int',
			'val' 				=> 0,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'allpage_1_hook_name' => array(
			'name' 				=> 'PREDIGGO_ALLPAGE_1_HOOK_NAME',
			'type'				=> 'select',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'allpage_1_template_name' => array(
			'name' 				=> 'PREDIGGO_ALLPAGE_1_TEMPLATE_NAME',
			'type'				=> 'text',
			'size'     			=> 100,
			'val' 				=> 'list_recommendations.tpl',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'allpage_1_block_label' => array(
			'name' 				=> 'PREDIGGO_ALLPAGE_1_BLOCK_LABEL',
			'type'				=> 'text',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),

		/* ALL PAGE  CONFIGURATION - Block 2*/
		'allpage_2_activated' => array(
			'name' 				=> 'PREDIGGO_ALLPAGE2_ACTIVATED',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'allpage_2_nb_items' => array(
			'name' 				=> 'PREDIGGO_ALLPAGE_2_NB_RECO',
			'type'				=> 'int',
			'val' 				=> 5,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'allpage_2_variant_id' => array(
			'name' 				=> 'PREDIGGO_ALLPAGE_2_VARIANT_ID',
			'type'				=> 'int',
			'val' 				=> 0,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'allpage_2_hook_name' => array(
			'name' 				=> 'PREDIGGO_ALLPAGE_2_HOOK_NAME',
			'type'				=> 'select',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'allpage_2_template_name' => array(
			'name' 				=> 'PREDIGGO_ALLPAGE_2_TEMPLATE_NAME',
			'type'				=> 'text',
			'size'     			=> 100,
			'val' 				=> 'list_recommendations.tpl',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'allpage_2_block_label' => array(
			'name' 				=> 'PREDIGGO_ALLPAGE_2_BLOCK_LABEL',
			'type'				=> 'text',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		
		/* PRODUCT PAGE  CONFIGURATION - Block 0*/
		'productpage_0_activated' => array(
			'name' 				=> 'PREDIGGO_PRODPG_0_ACTIVATED',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'productpage_0_nb_items' => array(
			'name' 				=> 'PREDIGGO_PRODPG_0_NB_RECO',
			'type'				=> 'int',
			'val' 				=> 5,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'productpage_0_variant_id' => array(
			'name' 				=> 'PREDIGGO_PRODPG_0_VARIANT_ID',
			'type'				=> 'int',
			'val' 				=> 0,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'productpage_0_hook_name' => array(
			'name' 				=> 'PREDIGGO_PRODPG_0_HOOK_NAME',
			'type'				=> 'select',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'productpage_0_template_name' => array(
			'name' 				=> 'PREDIGGO_PRODPG_0_TEMPLATE_NAME',
			'type'				=> 'text',
			'size'     			=> 100,
			'val' 				=> 'list_recommendations.tpl',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'productpage_0_block_label' => array(
			'name' 				=> 'PREDIGGO_PRODPG_0_BLOCK_LABEL',
			'type'				=> 'text',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		
		/* PRODUCT PAGE  CONFIGURATION - Block 1*/
		'productpage_1_activated' => array(
			'name' 				=> 'PREDIGGO_PRODPG_1_ACTIVATED',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'productpage_1_nb_items' => array(
			'name' 				=> 'PREDIGGO_PRODPG_1_NB_RECO',
			'type'				=> 'int',
			'val' 				=> 5,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'productpage_1_variant_id' => array(
			'name' 				=> 'PREDIGGO_PRODPG_1_VARIANT_ID',
			'type'				=> 'int',
			'val' 				=> 0,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'productpage_1_hook_name' => array(
			'name' 				=> 'PREDIGGO_PRODPG_1_HOOK_NAME',
			'type'				=> 'select',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'productpage_1_template_name' => array(
			'name' 				=> 'PREDIGGO_PRODPG_1_TEMPLATE_NAME',
			'type'				=> 'text',
			'size'     			=> 100,
			'val' 				=> 'list_recommendations.tpl',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'productpage_1_block_label' => array(
			'name' 				=> 'PREDIGGO_PRODPG_1_BLOCK_LABEL',
			'type'				=> 'text',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		
		/* PRODUCT PAGE  CONFIGURATION - Block 2*/
		'productpage_2_activated' => array(
			'name' 				=> 'PREDIGGO_PRODPG_2_ACTIVATED',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'productpage_2_nb_items' => array(
			'name' 				=> 'PREDIGGO_PRODPG_2_NB_RECO',
			'type'				=> 'int',
			'val' 				=> 5,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'productpage_2_variant_id' => array(
			'name' 				=> 'PREDIGGO_PRODPG_2_VARIANT_ID',
			'type'				=> 'int',
			'val' 				=> 0,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'productpage_2_hook_name' => array(
			'name' 				=> 'PREDIGGO_PRODPG_2_HOOK_NAME',
			'type'				=> 'select',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'productpage_2_template_name' => array(
			'name' 				=> 'PREDIGGO_PRODPG_2_TEMPLATE_NAME',
			'type'				=> 'text',
			'size'     			=> 100,
			'val' 				=> 'list_recommendations.tpl',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'productpage_2_block_label' => array(
			'name' 				=> 'PREDIGGO_PRODPG_2_BLOCK_LABEL',
			'type'				=> 'text',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		
		
		/* BASKET PAGE CONFIGURATION - Block 0*/
		'basket_0_activated' => array(
			'name' 				=> 'PREDIGGO_BASKET_0_ACTIVATED',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_0_nb_items' => array(
			'name' 				=> 'PREDIGGO_BASKET_0_NB_RECO',
			'type'				=> 'int',
			'val' 				=> 5,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_0_variant_id' => array(
			'name' 				=> 'PREDIGGO_BASKET_0_VARIANT_ID',
			'type'				=> 'int',
			'val' 				=> 0,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_0_hook_name' => array(
			'name' 				=> 'PREDIGGO_BASKET_0_HOOK_NAME',
			'type'				=> 'select',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_0_template_name' => array(
			'name' 				=> 'PREDIGGO_BASKET_0_TEMPLATE_NAME',
			'type'				=> 'text',
			'size'     			=> 100,
			'val' 				=> 'list_recommendations.tpl',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_0_block_label' => array(
			'name' 				=> 'PREDIGGO_BASKET_0_BLOCK_LABEL',
			'type'				=> 'text',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		
		/* HOME PAGE CONFIGURATION - Block 1*/
		'basket_1_activated' => array(
			'name' 				=> 'PREDIGGO_BASKET_1_ACTIVATED',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_1_nb_items' => array(
			'name' 				=> 'PREDIGGO_BASKET_1_NB_RECO',
			'type'				=> 'int',
			'val' 				=> 5,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_1_variant_id' => array(
			'name' 				=> 'PREDIGGO_BASKET_1_VARIANT_ID',
			'type'				=> 'int',
			'val' 				=> 0,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_1_hook_name' => array(
			'name' 				=> 'PREDIGGO_BASKET_1_HOOK_NAME',
			'type'				=> 'select',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_1_template_name' => array(
			'name' 				=> 'PREDIGGO_BASKET_1_TEMPLATE_NAME',
			'type'				=> 'text',
			'size'     			=> 100,
			'val' 				=> 'list_recommendations.tpl',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_1_block_label' => array(
			'name' 				=> 'PREDIGGO_BASKET_1_BLOCK_LABEL',
			'type'				=> 'text',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		
		/* BASKET PAGE CONFIGURATION - Block 2*/
		'basket_2_activated' => array(
			'name' 				=> 'PREDIGGO_BASKET_2_ACTIVATED',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_2_nb_items' => array(
			'name' 				=> 'PREDIGGO_BASKET_2_NB_RECO',
			'type'				=> 'int',
			'val' 				=> 5,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_2_variant_id' => array(
			'name' 				=> 'PREDIGGO_BASKET_2_VARIANT_ID',
			'type'				=> 'int',
			'val' 				=> 0,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_2_hook_name' => array(
			'name' 				=> 'PREDIGGO_BASKET_2_HOOK_NAME',
			'type'				=> 'select',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_2_template_name' => array(
			'name' 				=> 'PREDIGGO_BASKET_2_TEMPLATE_NAME',
			'type'				=> 'text',
			'size'     			=> 100,
			'val' 				=> 'list_recommendations.tpl',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_2_block_label' => array(
			'name' 				=> 'PREDIGGO_BASKET_2_BLOCK_LABEL',
			'type'				=> 'text',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		
		/* HOME PAGE CONFIGURATION - Block 3*/
		'basket_3_activated' => array(
			'name' 				=> 'PREDIGGO_BASKET_3_ACTIVATED',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_3_nb_items' => array(
			'name' 				=> 'PREDIGGO_BASKET_3_NB_RECO',
			'type'				=> 'int',
			'val' 				=> 5,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_3_variant_id' => array(
			'name' 				=> 'PREDIGGO_BASKET_3_VARIANT_ID',
			'type'				=> 'int',
			'val' 				=> 0,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_3_hook_name' => array(
			'name' 				=> 'PREDIGGO_BASKET_3_HOOK_NAME',
			'type'				=> 'select',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_3_template_name' => array(
			'name' 				=> 'PREDIGGO_BASKET_3_TEMPLATE_NAME',
			'type'				=> 'text',
			'size'     			=> 100,
			'val' 				=> 'list_recommendations.tpl',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_3_block_label' => array(
			'name' 				=> 'PREDIGGO_BASKET_3_BLOCK_LABEL',
			'type'				=> 'text',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		
		/* BASKET PAGE CONFIGURATION - Block 4*/
		'basket_4_activated' => array(
			'name' 				=> 'PREDIGGO_BASKET_4_ACTIVATED',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_4_nb_items' => array(
			'name' 				=> 'PREDIGGO_BASKET_4_NB_RECO',
			'type'				=> 'int',
			'val' 				=> 5,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_4_variant_id' => array(
			'name' 				=> 'PREDIGGO_BASKET_4_VARIANT_ID',
			'type'				=> 'int',
			'val' 				=> 0,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_4_hook_name' => array(
			'name' 				=> 'PREDIGGO_BASKET_4_HOOK_NAME',
			'type'				=> 'select',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_4_template_name' => array(
			'name' 				=> 'PREDIGGO_BASKET_4_TEMPLATE_NAME',
			'type'				=> 'text',
			'size'     			=> 100,
			'val' 				=> 'list_recommendations.tpl',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_4_block_label' => array(
			'name' 				=> 'PREDIGGO_BASKET_4_BLOCK_LABEL',
			'type'				=> 'text',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		
		/* HOME PAGE CONFIGURATION - Block 5*/
		'basket_5_activated' => array(
			'name' 				=> 'PREDIGGO_BASKET_5_ACTIVATED',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_5_nb_items' => array(
			'name' 				=> 'PREDIGGO_BASKET_5_NB_RECO',
			'type'				=> 'int',
			'val' 				=> 5,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_5_variant_id' => array(
			'name' 				=> 'PREDIGGO_BASKET_5_VARIANT_ID',
			'type'				=> 'int',
			'val' 				=> 0,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_5_hook_name' => array(
			'name' 				=> 'PREDIGGO_BASKET_5_HOOK_NAME',
			'type'				=> 'select',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_5_template_name' => array(
			'name' 				=> 'PREDIGGO_BASKET_5_TEMPLATE_NAME',
			'type'				=> 'text',
			'size'     			=> 100,
			'val' 				=> 'list_recommendations.tpl',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'basket_5_block_label' => array(
			'name' 				=> 'PREDIGGO_BASKET_5_BLOCK_LABEL',
			'type'				=> 'text',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		
		/* CATEGORY PAGE CONFIGURATION - Block 0 */
		'category_0_activated' => array(
			'name' 				=> 'PREDIGGO_CATPG_0_ACTIVATED',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'category_0_nb_items' => array(
			'name' 				=> 'PREDIGGO_CATPG_0_NB_RECO',
			'type'				=> 'int',
			'val' 				=> 5,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'category_0_variant_id' => array(
			'name' 				=> 'PREDIGGO_CATPG_0_VARIANT_ID',
			'type'				=> 'int',
			'val' 				=> 0,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'category_0_hook_name' => array(
			'name' 				=> 'PREDIGGO_CATPG_0_HOOK_NAME',
			'type'				=> 'select',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'category_0_template_name' => array(
			'name' 				=> 'PREDIGGO_CATPG_0_TEMPLATE_NAME',
			'type'				=> 'text',
			'size'     			=> 100,
			'val' 				=> 'list_recommendations.tpl',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'category_0_block_label' => array(
			'name' 				=> 'PREDIGGO_CATPG_0_BLOCK_LABEL',
			'type'				=> 'text',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		
		/* CATEGORY PAGE CONFIGURATION - Block 0 */
		'category_1_activated' => array(
			'name' 				=> 'PREDIGGO_CATPG_1_ACTIVATED',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'category_1_nb_items' => array(
			'name' 				=> 'PREDIGGO_CATPG_1_NB_RECO',
			'type'				=> 'int',
			'val' 				=> 5,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'category_1_variant_id' => array(
			'name' 				=> 'PREDIGGO_CATPG_1_VARIANT_ID',
			'type'				=> 'int',
			'val' 				=> 0,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'category_1_hook_name' => array(
			'name' 				=> 'PREDIGGO_CATPG_1_HOOK_NAME',
			'type'				=> 'select',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'category_1_template_name' => array(
			'name' 				=> 'PREDIGGO_CATPG_1_TEMPLATE_NAME',
			'type'				=> 'text',
			'size'     			=> 100,
			'val' 				=> 'list_recommendations.tpl',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'category_1_block_label' => array(
			'name' 				=> 'PREDIGGO_CATPG_1_BLOCK_LABEL',
			'type'				=> 'text',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),

		/* CATEGORY PAGE  CONFIGURATION - Block 2*/
		'category_2_activated' => array(
			'name' 				=> 'PREDIGGO_ALLPAGE2_ACTIVATED',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'category_2_nb_items' => array(
			'name' 				=> 'PREDIGGO_CATPG_2_NB_RECO',
			'type'				=> 'int',
			'val' 				=> 5,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'category_2_variant_id' => array(
			'name' 				=> 'PREDIGGO_CATPG_2_VARIANT_ID',
			'type'				=> 'int',
			'val' 				=> 0,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'category_2_hook_name' => array(
			'name' 				=> 'PREDIGGO_CATPG_2_HOOK_NAME',
			'type'				=> 'select',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'category_2_template_name' => array(
			'name' 				=> 'PREDIGGO_CATPG_2_TEMPLATE_NAME',
			'type'				=> 'text',
			'size'     			=> 100,
			'val' 				=> 'list_recommendations.tpl',
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'category_2_block_label' => array(
			'name' 				=> 'PREDIGGO_CATPG_2_BLOCK_LABEL',
			'type'				=> 'text',
			'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),

        /* CUSTOMER PAGE CONFIGURATION - Block 0 */
        'customer_0_activated' => array(
            'name' 				=> 'PREDIGGO_CUST_0_ACTIVATED',
            'type'				=> 'int',
            'val' 				=> 1,
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'customer_0_nb_items' => array(
            'name' 				=> 'PREDIGGO_CUST_0_NB_RECO',
            'type'				=> 'int',
            'val' 				=> 5,
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'customer_0_variant_id' => array(
            'name' 				=> 'PREDIGGO_CUST_0_VARIANT_ID',
            'type'				=> 'int',
            'val' 				=> 0,
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'customer_0_hook_name' => array(
            'name' 				=> 'PREDIGGO_CUST_0_HOOK_NAME',
            'type'				=> 'select',
            'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'customer_0_template_name' => array(
            'name' 				=> 'PREDIGGO_CUST_0_TEMPLATE_NAME',
            'type'				=> 'text',
            'size'     			=> 100,
            'val' 				=> 'list_recommendations.tpl',
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'customer_0_block_label' => array(
            'name' 				=> 'PREDIGGO_CUST_0_BLOCK_LABEL',
            'type'				=> 'text',
            'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
            'multilang'			=> true,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),

        /* CUSTOMER PAGE CONFIGURATION - Block 1 */
        'customer_1_activated' => array(
            'name' 				=> 'PREDIGGO_CUST_1_ACTIVATED',
            'type'				=> 'int',
            'val' 				=> 1,
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'customer_1_nb_items' => array(
            'name' 				=> 'PREDIGGO_CUST_1_NB_RECO',
            'type'				=> 'int',
            'val' 				=> 5,
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'customer_1_variant_id' => array(
            'name' 				=> 'PREDIGGO_CUST_1_VARIANT_ID',
            'type'				=> 'int',
            'val' 				=> 0,
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'customer_1_hook_name' => array(
            'name' 				=> 'PREDIGGO_CUST_1_HOOK_NAME',
            'type'				=> 'select',
            'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'customer_1_template_name' => array(
            'name' 				=> 'PREDIGGO_CUST_1_TEMPLATE_NAME',
            'type'				=> 'text',
            'size'     			=> 100,
            'val' 				=> 'list_recommendations.tpl',
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'customer_1_block_label' => array(
            'name' 				=> 'PREDIGGO_CUST_1_BLOCK_LABEL',
            'type'				=> 'text',
            'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
            'multilang'			=> true,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
		
		'home_footer_recommendations' => array(
		        'name' 				=> 'PREDIGGO_HOME_FOOTER_RECO',
		        'type'				=> 'int',
		        'val' 				=> 1,
		        'multilang'			=> false,
		        'multishopgroup'	=> false,
		        'multishop'			=> true,
		),
		'home_footer_nb_items' => array(
		        'name' 				=> 'PREDIGGO_HOME_FOOTER_NB_RECO',
		        'type'				=> 'int',
		        'val' 				=> 5,
		        'multilang'			=> false,
		        'multishopgroup'	=> false,
		        'multishop'			=> true,
		),
		'home_sales_block_title' => array(
		        'name' 				=> 'PREDIGGO_HOME_SALES_BLOCK_TITLE',
		        'type'				=> 'html',
		        'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
		        'multilang'			=> true,
		        'multishopgroup'	=> false,
		        'multishop'			=> true,
		),
		'home_views_block_title' => array(
		        'name' 				=> 'PREDIGGO_HOME_VIEWS_BLOCK_TITLE',
		        'type'				=> 'html',
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
			'val' 				=> 0,
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
			'val' 				=> 0,
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
			'val' 				=> 0,
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),

		'blocklayered_0_recommendations' => array(
			'name' 				=> 'PREDIGGO_LAY_0_RECO',
			'type'				=> 'int',
			'val' 				=> 1,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
		'blocklayered_0_nb_items' => array(
			'name' 				=> 'PREDIGGO_LAY_0_NB_RECO',
			'type'				=> 'int',
			'val' 				=> 3,
			'multilang'			=> false,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
        'blocklayered_0_variant_id' => array(
            'name' 				=> 'PREDIGGO_LAY_0_VARIANT_ID',
            'type'				=> 'int',
            'val' 				=> 0,
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
		'blocklayered_0_block_title' => array(
			'name' 				=> 'PREDIGGO_LAY_0_BLOCK_TITLE',
			'type'				=> 'text',
			'val' 				=> 0,
			'multilang'			=> true,
			'multishopgroup'	=> false,
			'multishop'			=> true,
		),
        'blocklayered_0_hook_name' => array(
            'name' 				=> 'PREDIGGO_LAY_0_HOOK_NAME',
            'type'				=> 'select',
            'val' 				=> array(1 => 'Prediggo', 2 => 'Prediggo', 3 => 'Prediggo', 4 => 'Prediggo', 5 => 'Prediggo'),
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'blocklayered_0_template_name' => array(
            'name' 				=> 'PREDIGGO_LAY_0_TEMPLATE_NAME',
            'type'				=> 'text',
            'size'     			=> 100,
            'val' 				=> 'list_recommendations.tpl',
            'multilang'			=> false,
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
        'pagination_template_name' => array(
            'name' 				=> 'PREDIGGO_PAGI_TEMPLATE_NAME',
            'type'				=> 'text',
            'size'     			=> 100,
            'val' 				=> 'pagination.tpl',
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'search_filters_sort_by_template_name' => array(
            'name' 				=> 'PREDIGGO_SFSB_TEMPLATE_NAME',
            'type'				=> 'text',
            'size'     			=> 100,
            'val' 				=> 'search_filters_sort_by.tpl',
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'search_filter_block_template_name' => array(
            'name' 				=> 'PREDIGGO_SFB_TEMPLATE_NAME',
            'type'				=> 'text',
            'size'     			=> 100,
            'val' 				=> 'search_filters_block.tpl',
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'prod_compare_template_name' => array(
            'name' 				=> 'PREDIGGO_PCOMP_TEMPLATE_NAME',
            'type'				=> 'text',
            'size'     			=> 100,
            'val' 				=> 'product_compare.tpl',
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'prod_list_template_name' => array(
            'name' 				=> 'PREDIGGO_PLIST_TEMPLATE_NAME',
            'type'				=> 'text',
            'size'     			=> 100,
            'val' 				=> 'product-list.tpl',
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
        'search_0_template_name' => array(
            'name' 				=> 'PREDIGGO_SERCH_0_TEMPLATE_NAME',
            'type'				=> 'text',
            'size'     			=> 100,
            'val' 				=> 'search_block.tpl',
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'search_main_template_name' => array(
            'name' 				=> 'PREDIGGO_SCHMAIN_TEMPLATE_NAME',
            'type'				=> 'text',
            'size'     			=> 100,
            'val' 				=> 'search.tpl',
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'autoc_template_name' => array(
            'name' 				=> 'PREDIGGO_AUTOC_TEMPLATE_NAME',
            'type'				=> 'text',
            'size'     			=> 100,
            'val' 				=> 'autocomplete_dum.tpl',
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'autop_template_name' => array(
            'name' 				=> 'PREDIGGO_AUTOP_TEMPLATE_NAME',
            'type'				=> 'text',
            'size'     			=> 100,
            'val' 				=> 'autocomplete_product.tpl',
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'autos_template_name' => array(
            'name' 				=> 'PREDIGGO_AUTOS_TEMPLATE_NAME',
            'type'				=> 'text',
            'size'     			=> 100,
            'val' 				=> 'autocomplete_suggest.tpl',
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'autocat_template_name' => array(
            'name' 				=> 'PREDIGGO_AUTOCAT_TEMPLATE_NAME',
            'type'				=> 'text',
            'size'     			=> 100,
            'val' 				=> 'autocomplete_attributes.tpl',
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
        //Category
        'category_active' => array(
            'name' 				=> 'PREDIGGO_CAT_ACTIVE',
            'type'				=> 'int',
            'val' 				=> 1,
            'multilang'			=> false,
            'multishopgroup'	=> false,
            'multishop'			=> true,
        ),
        'category_0_template_name' => array(
            'name' 				=> 'PREDIGGO_CAT_0_TEMPLATE_NAME',
            'type'				=> 'text',
            'size'     			=> 100,
            'val' 				=> 'category.tpl',
            'multilang'			=> false,
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
				2 => $aConf['type'] == 'html' ? true : false,
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