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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2014 PrestaShop SA
 *  @license	http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

/* Security*/
if (!defined('_PS_VERSION_'))
	exit;

/* Loading eBay Class Request*/
$classes_to_load = array(
	'EbayRequest',
	'EbayCategory',
	'EbayCategoryConfiguration',
	'EbayDeliveryTimeOptions',
	'EbayOrder',
	'EbayProduct',
	'EbayReturnsPolicy',
	'EbayShipping',
	'EbayShippingLocation',
	'EbayShippingService',
	'EbayShippingZoneExcluded',
	'EbayShippingInternationalZone',
	'EbaySynchronizer',
	'EbayPayment',
	'EbayCategoryConditionConfiguration',
	'EbayCategorySpecific',
	'EbayProductConfiguration',
	'EbayProductImage',
	'EbayProfile',
	'EbayReturnsPolicyConfiguration',
	'EbayConfiguration',
    'EbayProductModified',
    'EbayLog',
    'EbayStat',
    'TotFormat',
    'EbayValidatorTab',
);

foreach ($classes_to_load as $classname)
	if (file_exists(dirname(__FILE__).'/classes/'.$classname.'.php'))
		require_once(dirname(__FILE__).'/classes/'.$classname.'.php');

if(!function_exists('bqSQL'))
{
	function bqSQL($string)
	{
		return str_replace('`', '\`', pSQL($string));
	}
}

/* Checking compatibility with older PrestaShop and fixing it*/
if (!defined('_MYSQL_ENGINE_'))
	define('_MYSQL_ENGINE_', 'MyISAM');

class Ebay extends Module
{
	private $html = '';
	private $ebay_country;
	
	public $ebay_profile;
    
    private $is_multishop;
    
    private $stats_version;

	/**
	 * Construct Method
	 *
	 **/
	public function __construct($id_ebay_profile = null)
	{
		$this->name = 'ebay';
		$this->tab = 'market_place';
		$this->version = '1.7';
        $this->stats_version = '1.0';

		$this->author = 'PrestaShop';

		parent::__construct();

		/** Backward compatibility */
        require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');

		$this->displayName = $this->l('eBay');
		$this->description = $this->l('Easily export your products from PrestaShop to eBay, the biggest market place, to acquire new customers and realize more sales.');
		$this->module_key = '7a6b007a219bab59c1611254347f21d5';

		// Checking Extension
		$this->_checkExtensionsLoading();

		// Checking compatibility with older PrestaShop and fixing it
		if (!Configuration::get('PS_SHOP_DOMAIN'))
			$this->setConfiguration('PS_SHOP_DOMAIN', $_SERVER['HTTP_HOST']);

		// Generate eBay Security Token if not exists
		if (!Configuration::get('EBAY_SECURITY_TOKEN'))
			$this->setConfiguration('EBAY_SECURITY_TOKEN', Tools::passwdGen(30));

		// For 1.4.3 and less compatibility
		$update_config = array(
			'PS_OS_CHEQUE' => 1,
			'PS_OS_PAYMENT' => 2,
			'PS_OS_PREPARATION' => 3,
			'PS_OS_SHIPPING' => 4,
			'PS_OS_DELIVERED' => 5,
			'PS_OS_CANCELED' => 6,
			'PS_OS_REFUND' => 7,
			'PS_OS_ERROR' => 8,
			'PS_OS_OUTOFSTOCK' => 9,
			'PS_OS_BANKWIRE' => 10,
			'PS_OS_PAYPAL' => 11,
			'PS_OS_WS_PAYMENT' => 12
		);

		foreach ($update_config as $key => $value)
			if (!Configuration::get($key))
			{
				$const_name = '_'.$key.'_';

				if ((int)constant($const_name))
                    $this->setConfiguration($key, constant($const_name));
				else
                    $this->setConfiguration($key, $value);
			}
            
        $this->is_multishop = (version_compare(_PS_VERSION_, '1.5', '>') && Shop::isFeatureActive());

		// Check if installed
		if (self::isInstalled($this->name))
		{
			// Upgrade eBay module
			if (Configuration::get('EBAY_VERSION') != $this->version)
				$this->_upgrade();

			if(class_exists('EbayCountrySpec'))
			{
                if ($id_ebay_profile)
                    $this->ebay_profile = new EbayProfile($id_ebay_profile);
                else
                    $this->ebay_profile = EbayProfile::getCurrent();

                if ($this->ebay_profile) {
    				// Check the country
    				$this->ebay_country = EbayCountrySpec::getInstanceByKey($this->ebay_profile->getConfiguration('EBAY_COUNTRY_DEFAULT'));

    				if (!$this->ebay_country->checkCountry())
    				{
    					$this->warning = $this->l('The eBay module currently works for eBay.fr, eBay.it, eBay.co.uk, eBay.pl, eBay.nl and eBay.es');
    					return false;
    				}
                                        
                } else {
                    return false;
                }
			}


			// Generate warnings
			if (!Configuration::get('EBAY_API_TOKEN', null, 0, 0))
				$this->warning = $this->l('You must register your module on eBay.');


			// Warning uninstall
			$this->confirmUninstall = $this->l('Are you sure you want to uninistall this module? All configuration settings will be lost');
			
		}
	}

	/**
	 * Test if the different php extensions are loaded
	 * and update the warning var
	 *
	 */
	private function _checkExtensionsLoading()
	{
		if (!extension_loaded('curl') || !ini_get('allow_url_fopen'))
		{
			if (!extension_loaded('curl') && !ini_get('allow_url_fopen'))
				$this->warning = $this->l('You must enable cURL extension and allow_url_fopen option on your server if you want to use this module.');
			elseif (!extension_loaded('curl'))
				$this->warning = $this->l('You must enable cURL extension on your server if you want to use this module.');
			elseif (!ini_get('allow_url_fopen'))
				$this->warning = $this->l('You must enable allow_url_fopen option on your server if you want to use this module.');
		}
	}

	/**
	* Install module
	*
	* @return boolean
	*/
	public function install()
	{
		// Install SQL
		include(dirname(__FILE__).'/sql/sql-install.php');

		foreach ($sql as $s)
			if (!Db::getInstance()->execute($s))
				return false;

		// Install Module
		if (!parent::install()
			|| !$this->registerHook('addProduct')
			|| !$this->registerHook('updateProduct') 
			|| !$this->registerHook('deleteProduct')
			|| !$this->registerHook('newOrder')
			|| !$this->registerHook('backOfficeTop')
			|| !$this->registerHook('header'))
			return false;

		$hook_update_quantity = version_compare(_PS_VERSION_, '1.5', '>') ? 'actionUpdateQuantity' : 'updateQuantity';

		if (!$this->registerHook($hook_update_quantity))
			return false;

		$this->createDefaultProfilesAndReturnsPolicies();
		$this->ebay_profile =  EbayProfile::getCurrent();
		
		$this->setConfiguration('EBAY_INSTALL_DATE', date('Y-m-d\TH:i:s.000\Z'));
		// Picture size
		self::installPicturesSettings($this);

		$this->installUpgradeOneFour();

		// Init
		$this->setConfiguration('EBAY_VERSION', $this->version);

		return true;
	}
	
	public function createDefaultProfilesAndReturnsPolicies()
	{
		$id_shops = version_compare(_PS_VERSION_, '1.5', '>') ? Shop::getShops(false, null, true) : array(Shop::getCurrentShop());
		
		foreach($id_shops as $id_shop)
		{
			if (!($profile = EbayProfile::getOneByIdShop($id_shop)))
			{
				$profile = new EbayProfile();
				$profile->id_shop = $id_shop;
			}

			$returns_policy_configuration = new EbayReturnsPolicyConfiguration();
			$returns_policy_configuration->save();
                        
			$profile->id_ebay_returns_policy_configuration = $returns_policy_configuration->id;
			$profile->save();
            
    		$profile->setConfiguration('EBAY_PRODUCT_TEMPLATE', ''); // fix to work around the PrestaShop bug when saving html for a configuration key that doesn't exist yet
    		$profile->setConfiguration('EBAY_PRODUCT_TEMPLATE', $this->_getProductTemplateContent(), true);
    		$profile->setConfiguration('EBAY_PRODUCT_TEMPLATE_TITLE', '{TITLE}');
    		$profile->setConfiguration('EBAY_ORDER_LAST_UPDATE', date('Y-m-d\TH:i:s.000\Z'));
    		$profile->setConfiguration('EBAY_DELIVERY_TIME', 2);
    		$profile->setConfiguration('EBAY_ACTIVATE_LOGS', '0');
    		$profile->setConfiguration('EBAY_ACTIVATE_MAILS', '0');
		}		
	}

	public function emptyEverything()
	{
		Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'configuration WHERE name LIKE  "%EBAY%"');
		Db::getInstance()->Execute('DROP TABLE IF EXISTS
			`'._DB_PREFIX_.'ebay_category` ,
			`'._DB_PREFIX_.'ebay_category_condition` ,
			`'._DB_PREFIX_.'ebay_category_condition_configuration` ,
			`'._DB_PREFIX_.'ebay_category_configuration` ,
			`'._DB_PREFIX_.'ebay_category_specific` ,
			`'._DB_PREFIX_.'ebay_category_specific_value` ,
			`'._DB_PREFIX_.'ebay_delivery_time_options` ,
			`'._DB_PREFIX_.'ebay_order` ,
			`'._DB_PREFIX_.'ebay_product` ,
			`'._DB_PREFIX_.'ebay_product_configuration` ,
			`'._DB_PREFIX_.'ebay_product_image` ,
			`'._DB_PREFIX_.'ebay_returns_policy` ,
			`'._DB_PREFIX_.'ebay_shipping` ,
			`'._DB_PREFIX_.'ebay_shipping_international_zone` ,
			`'._DB_PREFIX_.'ebay_shipping_location` ,
			`'._DB_PREFIX_.'ebay_shipping_service` ,
			`'._DB_PREFIX_.'ebay_shipping_zone_excluded` ,
			`'._DB_PREFIX_.'ebay_sync_history` ,
			`'._DB_PREFIX_.'ebay_sync_history_product`,
			`'._DB_PREFIX_.'ebay_profile`,
			`'._DB_PREFIX_.'ebay_configuration`,			
			`'._DB_PREFIX_.'ebay_returns_policy_configuration`');
	}

	public static function installPicturesSettings($module) {

		// Default
		if ($default = ImageType::getByNameNType('medium', 'products')) {
			$sizeMedium = (int) $default['id_image_type'];
		} 
		else if ($medium = ImageType::getByNameNType('medium_default', 'products')) {
			$sizeMedium = (int) $default['id_image_type'];
		}
		else {
			$sizeMedium = 0;
		}
		// Small
		if ($small = ImageType::getByNameNType('small', 'products')) {
			$sizeSmall = (int) $small['id_image_type'];
		} 
		else if ($small = ImageType::getByNameNType('small_default', 'products')) {
			$sizeSmall = (int) $small['id_image_type'];
		}
		else {
			$sizeSmall = 0;
		}
		// Large
		if ($large = ImageType::getByNameNType('large', 'products')) {
			$sizeBig = (int) $large['id_image_type'];
		} 
		else if ($large = ImageType::getByNameNType('large_default', 'products')) {
			$sizeBig = (int) $large['id_image_type'];
		}
		else {
			$sizeBig = 0;
		}

		$module->ebay_profile->setConfiguration('EBAY_PICTURE_SIZE_DEFAULT', $sizeMedium);
		$module->ebay_profile->setConfiguration('EBAY_PICTURE_SIZE_SMALL', $sizeSmall);
		$module->ebay_profile->setConfiguration('EBAY_PICTURE_SIZE_BIG', $sizeBig);
		$module->ebay_profile->setConfiguration('EBAY_PICTURE_PER_LISTING', 0);
	}

	/**
	* Returns product template
	*
	* @return string
	*/
	private function _getProductTemplateContent()
	{
		$logo_url = version_compare(_PS_VERSION_, '1.5', '>') ?  (Tools::getShopDomain(true).'/'.__PS_BASE_URI__.'/'._PS_IMG_.Configuration::get('PS_LOGO').'?'.Configuration::get('PS_IMG_UPDATE_TIME')) : (Tools::getShopDomain(true).'/'.__PS_BASE_URI__.'/img/logo.jpg');

		$this->smarty->assign(array(
			'shop_logo' => $logo_url,
			'shop_name' => Configuration::get('PS_SHOP_NAME'),
			'module_url' => $this->_getModuleUrl(),
		));

		return $this->display(__FILE__, 'ebay/ebay.tpl');
	}

	/**
	 * Returns the module url
	 *
   **/
	private function _getModuleUrl()
	{
		return Tools::getShopDomain(true).__PS_BASE_URI__.'modules/ebay/';
	}

	/**
	 * Uninstall module
	 *
	 * @return boolean
	 **/
	public function uninstall()
	{
		// Uninstall SQL
		include(dirname(__FILE__).'/sql/sql-uninstall.php');

		foreach ($sql as $s)
			if (!Db::getInstance()->execute($s))
				return false;

		Configuration::deleteByName('EBAY_API_TOKEN');

		// Uninstall Module
		if (!parent::uninstall()
			|| !$this->unregisterHook('addProduct')
			|| !$this->unregisterHook('updateProduct')
			|| !$this->unregisterHook('actionUpdateQuantity')
			|| !$this->unregisterHook('updateQuantity')
			|| !$this->unregisterHook('updateProductAttribute')
			|| !$this->unregisterHook('deleteProduct')
			|| !$this->unregisterHook('newOrder')
			|| !$this->unregisterHook('backOfficeTop')
			|| !$this->unregisterHook('header'))
			return false;

		// Clean Cookie
		$this->context->cookie->eBaySession = '';
		$this->context->cookie->eBayUsername = '';

		return true;
	}

	public function installUpgradeOneFour()
	{
		$this->ebay_profile->setConfiguration('EBAY_LISTING_DURATION', 'GTC');
		$this->ebay_profile->setConfiguration('EBAY_AUTOMATICALLY_RELIST', 'on');
		$this->ebay_profile->setConfiguration('EBAY_LAST_RELIST', date('Y-m-d'));
//		$this->ebay_profile->setConfiguration('EBAY_RETURNS_DESCRIPTION', '');
//		$this->ebay_profile->setConfiguration('EBAY_RETURNS_ACCEPTED_OPTION', 'ReturnsAccepted');
	}

	private function _upgrade()
	{

		$version = Configuration::get('EBAY_VERSION');

		if ($version == '1.1' || empty($version))
			if (version_compare(_PS_VERSION_, '1.5', '<'))
			{
				include_once(dirname(__FILE__).'/upgrade/Upgrade-1.2.php');
				upgrade_module_1_2($this);
			}

		if (version_compare($version, '1.4.0', '<'))
			if (version_compare(_PS_VERSION_, '1.5', '<'))
			{
				include_once(dirname(__FILE__).'/upgrade/Upgrade-1.4.php');
				upgrade_module_1_4($this);
			}

		if (version_compare($version, '1.5.0', '<'))
			if (version_compare(_PS_VERSION_, '1.5', '<'))
			{
				include_once(dirname(__FILE__).'/upgrade/Upgrade-1.5.php');
				upgrade_module_1_5($this);
			}

		if (version_compare($version, '1.6', '<')) {
			if (version_compare(_PS_VERSION_, '1.5', '<'))
			{
				include_once(dirname(__FILE__).'/upgrade/Upgrade-1.6.php');
				upgrade_module_1_6($this);
			}
		}
			
		if (version_compare($version, '1.7', '<')) {
			if (version_compare(_PS_VERSION_, '1.5', '<'))
			{
				include_once(dirname(__FILE__).'/upgrade/Upgrade-1.7.php');
				upgrade_module_1_7($this);
			}
		}
	}

	/**
	 * Called when a new order is placed
	 *
	 * @param array $params hook parameters
	 **/
	public function hookNewOrder($params)
	{
		if (!(int)$params['cart']->id)
			return false;

		$sql = 'SELECT cp.`id_product`, ep.`id_ebay_profile`
			FROM `'._DB_PREFIX_.'cart_product` cp
			INNER JOIN `'._DB_PREFIX_.'product` p
			ON p.`id_product` = cp.`id_product`
			INNER JOIN `'._DB_PREFIX_.'ebay_profile` ep
			ON cp.`id_shop` = ep.`id_shop`			
			WHERE cp.`id_cart` = '.(int)$params['cart']->id.'
			AND p.`active` = 1
			AND p.`id_category_default` IN
			('.EbayCategoryConfiguration::getCategoriesQuery($this->ebay_profile).')';
		
		if ($products = Db::getInstance()->executeS($sql)) {
            if (Configuration::get('EBAY_SYNC_PRODUCTS_BY_CRON'))
                foreach($products as $product)
                    EbayProductModified::addProduct($this->ebay_profile->id, $product['id_product']);
            else
                EbaySynchronizer::syncProducts($products, $this->context, $this->ebay_country->getIdLang());		    
		}
	}

	/**
	 * Called when a product is added to the shop
	 *
	 * @param array $params hook parameters
	 **/
	public function hookAddProduct($params)
	{
		if (!isset($params['product']->id))
			return false;

		if (!($id_product = (int)$params['product']->id))
			return false;
        
        if ($this->is_multishop)
        {
            // we don't synchronize the product if we are not in a shop
            $context_shop = $this->_getContextShop();
            if ($context_shop[0] != Shop::CONTEXT_SHOP)
                return false;
        }
		
		$sql = 'SELECT `id_product`, 
			\''.(int)$this->ebay_profile->id.'\' AS `id_ebay_profile`
			FROM `'._DB_PREFIX_.'product`
			WHERE `id_product` = '.$id_product.'
			AND `active` = 1
			AND `id_category_default` IN
			('.EbayCategoryConfiguration::getCategoriesQuery($this->ebay_profile).')';

		if ($products = Db::getInstance()->executeS($sql)) {
            if (Configuration::get('EBAY_SYNC_PRODUCTS_BY_CRON'))
                foreach($products as $product)
                    EbayProductModified::addProduct($this->ebay_profile->id, $product['id_product']);
		    else
    			EbaySynchronizer::syncProducts($products, $this->context, $this->ebay_country->getIdLang());                
		}
	}

	/**
	 *
	 *
	 * @param array $params hook parameters
	 **/
	public function hookHeader($params)
	{
		if(Tools::getValue('DELETE_EVERYTHING_EBAY') == 1)
			$this->emptyEverything();
        
		if (!$this->ebay_profile || !$this->ebay_profile->getConfiguration('EBAY_PAYPAL_EMAIL')) // if the module is not upgraded or not configured don't do anything
			return false;

		// if multishop, change context Shop to be default
		if ($this->is_multishop)
		{
			$old_context_shop = $this->_getContextShop();
			$this->_setContextShop();
		}

		$this->hookUpdateProductAttributeEbay(); // Fix hook update product attribute

        // update if not update for more than 30 min or EBAY_SYNC_ORDER = 1
		if (
            (!$this->is_multishop && ($this->ebay_profile->getConfiguration('EBAY_ORDER_LAST_UPDATE') < date('Y-m-d\TH:i:s', strtotime('-30 minutes')).'.000Z'))
            || Tools::getValue('EBAY_SYNC_ORDERS') == 1)
		{
    		$current_date = date('Y-m-d\TH:i:s').'.000Z';

    		$orders = $this->_getEbayLastOrders($current_date);

    		// we set the new last update date after retrieving the last orders
    		$this->ebay_profile->setConfiguration('EBAY_ORDER_LAST_UPDATE', $current_date);

    		if ($orders)
    			$this->importOrders($orders);
		}

		// Set old Context Shop
		if ($this->is_multishop)
			$this->_setContextShop($old_context_shop);

		$this->_relistItems();
	}
    
    public function cronProductsSync()
    {
        EbaySynchronizer::syncProducts(EbayProductModified::getAll(), Context::getContext(), $this->ebay_country->getIdLang(), 'CRON_PRODUCT');
        EbayProductModified::truncate();
    }
	
	public function cronOrdersSync()
	{
		$current_date = date('Y-m-d\TH:i:s').'.000Z';

		if ($orders = $this->_getEbayLastOrders($current_date))
			$this->importOrders($orders);

		// we set the new last update date after retrieving the last orders
		$this->ebay_profile->setConfiguration('EBAY_ORDER_LAST_UPDATE', $current_date);
	}	

	public function importOrders($orders)
	{
		$errors_email = array();
		foreach ($orders as $order)
		{
			$errors = array();

			if (!$order->isCompleted())
			{
				$message = $this->l('Status not complete, amount less than 0.1 or no matching product');
				$errors[] = $message;
				$order->addErrorMessage($message);
				continue;
			}

			if ($order->exists())
			{
				$message = $this->l('Order already imported');
				$errors[] = $message;
				$order->addErrorMessage($message);
				continue;
			}

			// no order in ebay order table with this order_ref
			if (!$order->hasValidContact())
			{
				$message = $this->l('Invalid e-mail');
				$errors[] = $message;
				$order->addErrorMessage($message);
				continue;
			}

			if (!$order->hasAllProductsWithAttributes())
			{
				$message = $this->l('Could not find the products in database');
				$errors[] = $message;
				$order->addErrorMessage($message);
				continue;
			}
            
			if ($this->is_multishop) 
			{
				$shops_data = $order->getProductsAndProfileByShop();
				$id_shops = array_keys($shops_data);
                if (count($id_shops) > 1)
                {
                    $product_ids = $order->getProductIds();
                    $first_id_shop = $id_shops[0];
                    if (version_compare(_PS_VERSION_, '1.5', '>'))
                        $sql = 'SELECT count(*)
                            FROM `'._DB_PREFIX_.'product_shop` ps
                            WHERE ps.`id_shop` = '.(int)$first_id_shop.'
                            AND ps.`active` = 1
                            AND ps.`id_product` IN ('.implode(',', $product_ids).')';
                    else
                        $sql = 'SELECT count(*)
                            FROM `'._DB_PREFIX_.'product` p
                            WHERE p.`active` = 1
                            AND p.`id_product` IN ('.implode(',', $product_ids).')';                        
                    $nb_products_in_shop = Db::getInstance()->getValue($sql);
                    if ($nb_products_in_shop == count($product_ids))
                    {
                        $id_shops = array($first_id_shop);
                        $has_shared_customers = true;
                    }
                    else
                    {
                        $sql = 'SELECT count(*)
                            FROM `'._DB_PREFIX_.'shop` s
                            INNER JOIN `'._DB_PREFIX_.'shop_group` sg
                            ON s.`id_shop_group` = sg.`id_shop_group`
                            AND sg.`share_customer` = 1';
                        $nb_shops_sharing = Db::getInstance()->getValue($sql);
                        $has_shared_customers = ($nb_shops_sharing == count($id_shops));
                    }
                } else
                    $has_shared_customers = true;
			}
			else
            {
				$id_shops = array(Configuration::get('PS_SHOP_DEFAULT'));
                $has_shared_customers = true;                
            }
            
            $customer_ids = array();
            if ($has_shared_customers)
            {
                $ebay_profile = EbayProfile::getCurrent();                
    			$id_customer = $order->getOrAddCustomer($ebay_profile);
    			$id_address = $order->updateOrAddAddress($ebay_profile);
                $customer_ids[] = $id_customer;

    			// Fix on sending e-mail
    			Db::getInstance()->autoExecute(_DB_PREFIX_.'customer', array('email' => 'NOSEND-EBAY'), 'UPDATE', '`id_customer` = '.(int)$id_customer);
    			$customer_clear = new Customer();
    			if (method_exists($customer_clear, 'clearCache'))
    				$customer_clear->clearCache(true);
            }
			
			foreach ($id_shops as $id_shop)
			{
				if ($this->is_multishop) 
                {
					$id_ebay_profile = (int)$shops_data[$id_shop]['id_ebay_profiles'][0];
					$ebay_profile = new EbayProfile($id_ebay_profile);				    
				} else
					$ebay_profile = EbayProfile::getCurrent();
                
                if (!$has_shared_customers)
                {
        			$id_customer = $order->getOrAddCustomer($ebay_profile);
        			$id_address = $order->updateOrAddAddress($ebay_profile);
                    
                    $customer_ids[] = $id_customer;                    

        			// Fix on sending e-mail
        			Db::getInstance()->autoExecute(_DB_PREFIX_.'customer', array('email' => 'NOSEND-EBAY'), 'UPDATE', '`id_customer` = '.(int)$id_customer);
        			$customer_clear = new Customer();
        			if (method_exists($customer_clear, 'clearCache'))
        				$customer_clear->clearCache(true);
                }
				
				$cart = $order->addCart($ebay_profile, $this->ebay_country); //Create a Cart for the order

				if (!$order->updateCartQuantities($ebay_profile)) // if products in the cart
				{
					$order->deleteCart($ebay_profile->id_shop);
					$message = $this->l('Could not add product to cart (maybe your stock quantity is 0)');
					$errors[] = $message;
					$order->addErrorMessage($message);
					continue;
				}
				
				// if the carrier is disabled, we enable it for the order validation and then disable it again
				$carrier = new Carrier((int)EbayShipping::getPsCarrierByEbayCarrier($ebay_profile->id, $order->shippingService));
				if (!$carrier->active)
				{
					$carrier->active = true;
					$carrier->save();
					$has_disabled_carrier = true;
				} else
					$has_disabled_carrier = false;

				// Validate order
				$id_order = $order->validate($ebay_profile->id_shop);
				// we now disable the carrier if required
				if ($has_disabled_carrier)
				{
					$carrier->active = false;
					$carrier->save();
				}

				// Update price (because of possibility of price impact)
				$order->updatePrice($ebay_profile);

			}
			
			$order->add();

			if (!version_compare(_PS_VERSION_, '1.5', '>'))
				foreach ($order->getProducts() as $product)
					$this->hookAddProduct(array('product' => new Product((int)$product['id_product'])));			

            foreach ($customer_ids as $id_customer)
            {
    			// Fix on sending e-mail
    			Db::getInstance()->autoExecute(_DB_PREFIX_.'customer', array('email' => pSQL($order->getEmail())), 'UPDATE', '`id_customer` = '.(int)$id_customer);                
            }
		}

		$orders_ar = array();

		foreach ($orders as $order)
		{
			$orders_ar[] = array(
				'id_order_ref' => $order->getIdOrderRef(),
				'id_order_seller' => $order->getIdOrderSeller(),
				'amount' => $order->getAmount(),
				'status' => $order->getStatus(),
				'date' => $order->getDate(),
				'email' => $order->getEmail(),
				'products' => $order->getProducts(),
				'error_messages' => $order->getErrorMessages()
			);
		}

		file_put_contents(dirname(__FILE__).'/log/orders.php', "<?php\n\n".'$dateLastImport = '.'\''.date('d/m/Y H:i:s')."';\n\n".'$orders = '.var_export($orders_ar, true).";\n\n");

		if (Configuration::get('EBAY_ACTIVATE_MAILS') && $errors_email)
		{
			$data = '';
			foreach($errors_email as $e)
			{
				$data .= '<p>Id order : <strong>'.$e['id_order_seller'].'</strong></p><ul>';
				foreach($e['messages'] as $m)
				{
					$data .= '<li>'.$m.'</li>';
				}
				$data .= '</ul><br/>';
			}
			Mail::Send(
				(int)Configuration::get('PS_LANG_DEFAULT'),
				'errorsImportEbay',
				Mail::l('Errors import', (int)Configuration::get('PS_LANG_DEFAULT')),
				array('{errors_email}' => $data),
				strval(Configuration::get('PS_SHOP_EMAIL')),
				null,
				strval(Configuration::get('PS_SHOP_EMAIL')),
				strval(Configuration::get('PS_SHOP_NAME')),
				null,
				null,
				dirname(__FILE__).'/views/templates/mails/'
			);
		}
	}


	/**
	 * Returns Ebay last passed orders as an array of EbayOrder objects
	 *
	 * @param string $until_date Date until which the orders should be retrieved
	 * @return array
	 **/
	private function _getEbayLastOrders($until_date)
	{
		if (Configuration::get('EBAY_INSTALL_DATE') < date('Y-m-d\TH:i:s', strtotime('-30 days')))
		{
			//If it is more than 30 days that we installed the module
			// check from 30 days before
			$from_date_ar = explode('T', $this->ebay_profile->getConfiguration('EBAY_ORDER_LAST_UPDATE'));
			$from_date = date('Y-m-d', strtotime($from_date_ar[0].' -30 day'));
			$from_date .= 'T'.(isset($from_date_ar[1]) ? $from_date_ar[1] : '');
		}
		else
		{
			//If it is less than 30 days that we installed the module
			// check from one day before
			$from_date_ar = explode('T', Configuration::get('EBAY_INSTALL_DATE'));
			$from_date = date('Y-m-d', strtotime($from_date_ar[0].' -1 day'));
			$from_date .= 'T'.(isset($from_date_ar[1]) ? $from_date_ar[1] : '');
		}

		$ebay = new EbayRequest();
		$page = 1;
		$orders = array();
		$nb_page_orders = 100;

		while ($nb_page_orders == 100 && $page < 10)
		{
			$page_orders = array();
			foreach ($ebay->getOrders($from_date, $until_date, $page) as $order_xml)
				$page_orders[] = new EbayOrder($order_xml);

			$nb_page_orders = count($page_orders);
			$orders = array_merge($orders, $page_orders);

			$page++;
		}

		return $orders;
	}

	/**
	* Called when a product is updated
	*
	*/
	public function hookUpdateProduct($params)
	{
//		$this->hookAddProduct($params);
		if (!isset($params['product']->id))
			return false;

		if (!($id_product = (int)$params['product']->id))
			return false;
        
        if ($this->is_multishop)
        {
    		$sql = 'SELECT p.`id_product`,  ep.`id_ebay_profile`
    			FROM `'._DB_PREFIX_.'product` p
                INNER JOIN `'._DB_PREFIX_.'ebay_product` ep
                ON p.`id_product` = ep.`id_product`
                WHERE p.`id_product` = '.$id_product.'
    			AND p.`active` = 1
    			AND p.`id_category_default` IN
    			('.EbayCategoryConfiguration::getCategoriesQuery($this->ebay_profile).')';
        }
        else
        {
    		$sql = 'SELECT `id_product`, '.$this->ebay_profile->id.' AS `id_ebay_profile`
    			FROM `'._DB_PREFIX_.'product`
    			WHERE `id_product` = '.$id_product.'
    			AND `active` = 1
    			AND `id_category_default` IN
    			('.EbayCategoryConfiguration::getCategoriesQuery($this->ebay_profile).')';            
        }

		if ($products = Db::getInstance()->executeS($sql)) {
            if (Configuration::get('EBAY_SYNC_PRODUCTS_BY_CRON'))
                foreach($products as $product)
                    EbayProductModified::addProduct($product['id_ebay_profile'], $product['id_product']);
            else
    			EbaySynchronizer::syncProducts($products, $this->context, $this->ebay_country->getIdLang());                
		}

	}

	/*
	 * for PrestaShop 1.4
	 *
	 */
	public function hookUpdateQuantity($params)
	{
		$this->hookUpdateProduct($params);
	}

	public function hookActionUpdateQuantity($params)
	{
		if (isset($params['id_product']))
		{
			$params['product'] = new Product($params['id_product']);
			//$this->hookAddProduct($params); RAPH
            $this->hookUpdateProduct($params);
		}
	}

	public function hookUpdateProductAttributeEbay()
	{
		if (Tools::getValue('submitProductAttribute')
			&& Tools::getValue('id_product_attribute')
			&& ($id_product_attribute = (int)Tools::getValue('id_product_attribute')))
		{
			$id_product = Db::getInstance()->getValue('SELECT `id_product`
				FROM `'._DB_PREFIX_.'product_attribute`
				WHERE `id_product_attribute` = '.(int)$id_product_attribute);

//			$this->hookAddProduct(array(
			$this->hookUpdateProduct(array(
				'id_product_attribute' => $id_product_attribute,
				'product' => new Product($id_product)
			));
		}
	}

	public function hookDeleteProduct($params)
	{
		if (!isset($params['product']->id))
			return false;
        
        $ebay_profile = EbayProfile::getCurrent();

		EbaySynchronizer::endProductOnEbay(new EbayRequest(), $ebay_profile, $this->context, $this->ebay_country->getIdLang(), null, $params['product']->id);
	}

	public function hookBackOfficeTop($params)
	{
        if (Configuration::get('EBAY_SEND_STATS') && (Configuration::get('EBAY_STATS_LAST_UPDATE') < date('Y-m-d\TH:i:s', strtotime('-1 day')).'.000Z'))
        {
            EbayStat::send();
            Configuration::updateValue('EBAY_STATS_LAST_UPDATE', date('Y-m-d\TH:i:s.000\Z'), false, 0, 0);
        }   
        
		if (!((version_compare(_PS_VERSION_, '1.5.1', '>=')
			&& version_compare(_PS_VERSION_, '1.5.2', '<'))
			&& !Shop::isFeatureActive()))
			$this->hookHeader($params);
	}
    
	/**
	* Main Form Method
	*
	*/
	public function getContent()
	{
		// if multishop, change context Shop to be default
		/* RAPH
		if (version_compare(_PS_VERSION_, '1.5', '>') && Shop::isFeatureActive())
		{
			$old_context_shop = $this->_getContextShop();
			$this->_setContextShop();
		}
		*/

		if (!Configuration::get('EBAY_CATEGORY_MULTI_SKU_UPDATE'))
		{
			$ebay = new EbayRequest();
			EbayCategory::updateCategoryTable($ebay->getCategoriesSkuCompliancy());
		}

		// Checking Extension
		if (!extension_loaded('curl') || !ini_get('allow_url_fopen'))
		{
			if (!extension_loaded('curl') && !ini_get('allow_url_fopen'))
				return $this->html.$this->displayError($this->l('You must enable cURL extension and allow_url_fopen option on your server if you want to use this module.'));
			elseif (!extension_loaded('curl'))
				return $this->html.$this->displayError($this->l('You must enable cURL extension on your server if you want to use this module.'));
			elseif (!ini_get('allow_url_fopen'))
				return $this->html.$this->displayError($this->l('You must enable allow_url_fopen option on your server if you want to use this module.'));
		}


		// If isset Post Var, post process else display form
		if (!empty($_POST) && (Tools::isSubmit('submitSave') || Tools::isSubmit('btnSubmitSyncAndPublish') || Tools::isSubmit('btnSubmitSync')))
		{
			$errors = $this->_postValidation();

			if (!count($errors))
				$this->_postProcess();
			else
				foreach ($errors as $error)
					$this->html .= '<div class="alert error"><img src="../modules/ebay/views/img/forbbiden.gif" alt="nok" />&nbsp;'.$error.'</div>';
            
            if (Configuration::get('EBAY_SEND_STATS')) {
                $ebay_stat = new EbayStat($this->stats_version, $this->ebay_profile);
                $ebay_stat->save();
            }
		}

		$this->html .= $this->_displayForm();

		// Set old Context Shop
		/* RAPH
		if (version_compare(_PS_VERSION_, '1.5', '>') && Shop::isFeatureActive())
			$this->_setContextShop($old_context_shop);
		*/

		return $this->html;
	}

	private function _displayForm()
	{
		$alerts = $this->_getAlerts();

		$stream_context = @stream_context_create(array('http' => array('method' => 'GET', 'timeout' => 2)));

		$url_data = array(
			'version' => $this->version,
			'shop' => urlencode(Configuration::get('PS_SHOP_NAME')),
			'registered' => in_array('registration', $alerts) ? 'no' : 'yes',
			'url' => urlencode($_SERVER['HTTP_HOST']),
			'iso_country' => Tools::strtolower($this->ebay_country->getIsoCode()),
			'iso_lang' => Tools::strtolower($this->context->language->iso_code),
			'id_lang' => (int)$this->context->language->id,
			'email' => urlencode(Configuration::get('PS_SHOP_EMAIL')),
			'security' => md5(Configuration::get('PS_SHOP_EMAIL')._COOKIE_IV_)
		);
		$url = 'http://api.prestashop.com/partner/modules/ebay.php?'.http_build_query($url_data);

		$prestashop_content = @Tools::file_get_contents($url, false, $stream_context);
		if (!Validate::isCleanHtml($prestashop_content))
			$prestashop_content = '';

		$this->smarty->assign(array(
			'img_stats' => $this->ebay_country->getImgStats(),
			'alert' => $alerts,
			'regenerate_token' => Configuration::get('EBAY_TOKEN_REGENERATE', null, 0, 0),
			'prestashop_content' => $prestashop_content,
			'path' => $this->_path,
			'multishop' => (version_compare(_PS_VERSION_, '1.5', '>') && Shop::isFeatureActive()),
			'site_extension' => $this->ebay_country->getSiteExtension(),
			'documentation_lang' => $this->ebay_country->getDocumentationLang(),
			'is_version_one_dot_five' => version_compare(_PS_VERSION_, '1.5', '>'),
			'is_version_one_dot_five_dot_one' => (version_compare(_PS_VERSION_, '1.5.1', '>=') && version_compare(_PS_VERSION_, '1.5.2', '<')),
			'css_file' => $this->_path . 'views/css/ebay_back.css',
			'tooltip' => $this->_path . 'views/js/jquery.tooltipster.min.js',
			'tips202' => $this->_path . 'views/js/202tips.js',
			'noConflicts' => $this->_path . 'views/js/jquery.noConflict.php?version=1.7.2',
			'ebayjquery' => $this->_path . 'views/js/jquery-1.7.2.min.js',
			'fancybox' => $this->_path . 'views/js/jquery.fancybox.min.js',
			'fancyboxCss' => $this->_path . 'views/css/jquery.fancybox.css'
		));
		
		// test if multishop Screen and all shops
        if (version_compare(_PS_VERSION_, '1.5', '>'))
            $is_all_shops = in_array(Shop::getContext(), array(Shop::CONTEXT_ALL, Shop::CONTEXT_GROUP));
        else
            $is_all_shops = false;
		
        if (Configuration::get('EBAY_SEND_STATS') === false)
            $template = $this->_displayFormStats();
		elseif (!Configuration::get('EBAY_API_TOKEN', null, 0, 0))
			$template = $this->_displayFormRegister();
		elseif($is_all_shops) 
			$template = $this->_displayMultishopsList();
		else
			$template = $this->_displayFormConfig();			
		return $this->display(__FILE__, 'views/templates/hook/form.tpl').$template;
	}

	private function _postValidation()
	{
		if (Tools::getValue('section') != 'parameters')
			return;

		$errors = array();

//		if (!Tools::getValue('ebay_identifier'))
//			$errors[] = $this->l('Your eBay user id is not specified or is invalid');

		if (!Validate::isEmail(Tools::getValue('ebay_paypal_email')))
			$errors[] = $this->l('Your PayPal email address is not specified or invalid');

		if (!Tools::getValue('ebay_shop_postalcode') || !Validate::isPostCode(Tools::getValue('ebay_shop_postalcode')))
			$errors[] = $this->l('Your shop\'s postal code is not specified or is invalid');

		return $errors;
	}

	private function _postProcess()
	{
		if (Tools::getValue('section') == '')
			$this->_postProcessStats();
		if (Tools::getValue('section') == 'parameters')
			$this->_postProcessParameters();
		elseif (Tools::getValue('section') == 'category')
			$this->_postProcessCategory();
		elseif (Tools::getValue('section') == 'specifics')
			$this->_postProcessSpecifics();
		elseif (Tools::getValue('section') == 'shipping')
			$this->_postProcessShipping();
		elseif (Tools::getValue('section') == 'template')
			$this->_postProcessTemplateManager();
		elseif (Tools::getValue('section') == 'sync')
			$this->_postProcessEbaySync();
	}
    
	/**
	 * Form Config Methods
	 *
	 **/
	private function _displayFormStats()
	{
		$smarty_vars = array(
		);

		$this->smarty->assign($smarty_vars);

		return $this->display(__FILE__, 'views/templates/hook/formStats.tpl');
	}    

	/**
	 * Register Form Config Methods
	 **/
	private function _displayFormRegister()
	{
		$ebay = new EbayRequest();

		$smarty_vars = array();

		if (Tools::getValue('relogin'))
		{
			$session_id = $ebay->login();
			$this->context->cookie->eBaySession = $session_id;
			Configuration::updateValue('EBAY_API_SESSION', $session_id, false, 0, 0);

			$smarty_vars = array_merge($smarty_vars, array(
				'relogin' => true,
				'redirect_url' => $ebay->getLoginUrl().'?SignIn&runame='.$ebay->runame.'&SessID='.$this->context->cookie->eBaySession,
			));
		}
		else
			$smarty_vars['relogin'] = false;

		$logged = (!empty($this->context->cookie->eBaySession) && Tools::getValue('action') == 'logged');
		$smarty_vars['logged'] = $logged;

		if ($logged)
		{
			if ($ebay_username = Tools::getValue('eBayUsername'))
			{
				$this->context->cookie->eBayUsername = $ebay_username;
				Configuration::updateValue('EBAY_API_USERNAME', $ebay_username, false, 0, 0);
//				$this->setConfiguration('EBAY_IDENTIFIER', $ebay_username);
				$this->ebay_profile->ebay_user_identifier = $ebay_username;
                $this->ebay_profile->save();
				$this->ebay_profile->setConfiguration('EBAY_COUNTRY_DEFAULT', Tools::getValue('ebay_country'));
			}

			$smarty_vars['check_token_tpl'] = $this->_displayCheckToken();
		}
		else // not logged yet
		{
			if (empty($this->context->cookie->eBaySession))
			{
				$session_id = $ebay->login();
				$this->context->cookie->eBaySession = $session_id;
				Configuration::updateValue('EBAY_API_SESSION', $session_id, false, 0, 0);
				$this->context->cookie->write();
			}

			$smarty_vars = array_merge($smarty_vars, array(
				'action_url' => Tools::safeOutput($_SERVER['REQUEST_URI']).'&action=logged',
				'ebay_username' => $this->context->cookie->eBayUsername,
				'window_open_url' => '?SignIn&runame='.$ebay->runame.'&SessID='.$this->context->cookie->eBaySession,
				'ebay_countries' => EbayCountrySpec::getCountries($ebay->getDev()),
				'default_country' => EbayCountrySpec::getKeyForEbayCountry()
			));

		}

		$this->smarty->assign($smarty_vars);

		return $this->display(__FILE__, 'views/templates/hook/formRegister.tpl');
	}

	/**
	 *
	 * Waiting screen when expecting eBay login to refresh the token
	 *
	 */

	private function _displayCheckToken()
	{
		$url_vars = array(
			'action' => 'validateToken',
			'path' => $this->_path
		);

		if (version_compare(_PS_VERSION_, '1.5', '>'))
			$url_vars['controller'] = Tools::safeOutput(Tools::getValue('controller'));
		else
			$url_vars['tab'] = Tools::safeOutput(Tools::getValue('tab'));

		$url = _MODULE_DIR_.'ebay/ajax/checkToken.php?'.http_build_query(
			array(
				'token' => Configuration::get('EBAY_SECURITY_TOKEN'),
				'time' => pSQL(date('Ymdhis'))
			));

		$smarty_vars = array(
			'window_location_href' => $this->_getUrl($url_vars),
			'url' => $url,
			'request_uri' => Tools::safeOutput($_SERVER['REQUEST_URI'])
		);

		$this->smarty->assign($smarty_vars);

		return $this->display(__FILE__, 'views/templates/hook/checkToken.tpl');

	}

	/**
	 * When no shop selected in multishops mode, display the shops list
	 *
	 **/
	private function _displayMultishopsList()
	{
		$url_base = $_SERVER['REQUEST_URI'].(($_SERVER['QUERY_STRING']) ? '&' : '?').'setShopContext=s-';
		$shops = Shop::getShops(false);
		foreach ($shops as $i => $shop)
		{
			$profile = EbayProfile::getOneByIdShop($shop['id_shop']);
			$shops[$i]['nb_products_synchronized'] = EbayProduct::getNbProducts($profile->id);
		}
		
		$smarty_vars = array(
			'shops' 	 => $shops,
			'url_base' => $url_base
		);
		$this->smarty->assign($smarty_vars);			
		return $this->display(__FILE__, 'views/templates/hook/shopsList.tpl');		
	}


	/**
	 * Form Config Methods
	 *
	 **/
	private function _displayFormConfig()
	{
		$smarty_vars = array(
			'class_general' => version_compare(_PS_VERSION_, '1.5', '>') ? 'uncinq' : 'unquatre',
			'form_parameters' => $this->_displayFormParameters(),
			'form_category' => $this->_displayFormCategory(),
			'form_items_specifics' => $this->_displayFormItemsSpecifics(),
			'form_shipping' => $this->_displayFormShipping(),
			'form_template_manager' => $this->_displayFormTemplateManager(),
			'form_ebay_sync' => $this->_displayFormEbaySync(),
			'orders_history' => $this->_displayOrdersHistory(),
			'help' => $this->_displayHelp(),
			'id_tab' => Tools::safeOutput(Tools::getValue('id_tab')),
			'ebay_listings' => $this->_displayEbayListings()
		);

		$this->smarty->assign($smarty_vars);

		return $this->display(__FILE__, 'views/templates/hook/formConfig.tpl');	
	}

	private function _displayFormParameters()
	{
		// Loading config currency
		$config_currency = new Currency((int)Configuration::get('PS_CURRENCY_DEFAULT'));

		$url_vars = array(
			'id_tab' => '1',
			'section' => 'parameters'
		);

		if (version_compare(_PS_VERSION_, '1.5', '>'))
			$url_vars['controller'] = Tools::safeOutput(Tools::getValue('controller'));
		else
			$url_vars['tab'] = Tools::safeOutput(Tools::getValue('tab'));

		$url = $this->_getUrl($url_vars);
		$ebay_identifier = Tools::safeOutput(Tools::getValue('ebay_identifier', $this->ebay_profile->ebay_user_identifier)).'" '.((Tools::getValue('ebay_identifier', $this->ebay_profile->ebay_user_identifier) != '') ? ' readonly="readonly"' : '');
		$ebayShop = $this->ebay_profile->getConfiguration('EBAY_SHOP') ? $this->ebay_profile->getConfiguration('EBAY_SHOP') : $this->StoreName;
		$ebayShopValue = Tools::safeOutput(Tools::getValue('ebay_shop', $ebayShop));
		$createShopUrl = 'http://cgi3.ebay.'.$this->ebay_country->getSiteExtension().'/ws/eBayISAPI.dll?CreateProductSubscription&&productId=3&guest=1';

		$ebay = new EbayRequest();
		$ebay_sign_in_url = $ebay->getLoginUrl().'?SignIn&runame='.$ebay->runame.'&SessID='.$this->context->cookie->eBaySession;
		
		$returns_policy_configuration = $this->ebay_profile->getReturnsPolicyConfiguration();
        
        $sync_products_by_cron_url = $this->_getModuleUrl().'synchronizeProducts_CRON.php';
        $sync_orders_by_cron_url = $this->_getModuleUrl().'synchronizeOrders_CRON.php';
		$returnsConditionAccepted = Tools::getValue('ebay_returns_accepted_option', Configuration::get('EBAY_RETURNS_ACCEPTED_OPTION'));
		
		$ebay_paypal_email = Tools::safeOutput(Tools::getValue('ebay_paypal_email', $this->ebay_profile->getConfiguration('EBAY_PAYPAL_EMAIL')));
		$shopPostalCode = Tools::safeOutput(Tools::getValue('ebay_shop_postalcode', $this->ebay_profile->getConfiguration('EBAY_SHOP_POSTALCODE')));
		$ebayListingDuration = $this->ebay_profile->getConfiguration('EBAY_LISTING_DURATION');
		$sizedefault = (int)$this->ebay_profile->getConfiguration('EBAY_PICTURE_SIZE_DEFAULT');
		$sizeBig = (int)$this->ebay_profile->getConfiguration('EBAY_PICTURE_SIZE_BIG');
		$sizesmall = (int)$this->ebay_profile->getConfiguration('EBAY_PICTURE_SIZE_SMALL');
		$picture_per_listing = (int)$this->ebay_profile->getConfiguration('EBAY_PICTURE_PER_LISTING');
		$user_profile = $ebay->getUserProfile(Configuration::get('EBAY_API_USERNAME'));


		$account_setting = 0;
		if (!empty($ebay_identifier) && !empty($ebayShopValue) && !empty($ebay_paypal_email) && !empty($shopPostalCode) && !empty($returns_policy_configuration) && !empty($ebayListingDuration) && !empty($sizedefault) && !empty($sizeBig) && !empty($sizesmall))
			$account_setting = 1;

		$smarty_vars = array(
			'url' => $url,
			'ebay_sign_in_url' => $ebay_sign_in_url,
			'ebay_token' => Configuration::get('EBAY_SECURITY_TOKEN'),
			'ebayIdentifier' => $ebay_identifier,
			'configCurrencysign' => $config_currency->sign,
			'policies' => $this->_getReturnsPolicies(),
			'catLoaded' => !Configuration::get('EBAY_CATEGORY_LOADED'),
			'createShopUrl' => $createShopUrl,
			'ebayCountry' => EbayCountrySpec::getInstanceByKey($this->ebay_profile->getConfiguration('EBAY_COUNTRY_DEFAULT')),
			'ebayReturns' => preg_replace('#<br\s*?/?>#i', "\n", $this->ebay_profile->getReturnsPolicyConfiguration()->ebay_returns_description),
			'ebayShopValue' => $ebayShopValue,
			'shopPostalCode' => Tools::safeOutput(Tools::getValue('ebay_shop_postalcode', $this->ebay_profile->getConfiguration('EBAY_SHOP_POSTALCODE'))),
			'listingDurations' => $this->_getListingDurations(),
			'ebayShop' => $this->ebay_profile->getConfiguration('EBAY_SHOP'),
			'ebay_paypal_email' => Tools::safeOutput(Tools::getValue('ebay_paypal_email', $this->ebay_profile->getConfiguration('EBAY_PAYPAL_EMAIL'))),
			'returnsConditionAccepted' => Tools::getValue('ebay_returns_accepted_option', $returns_policy_configuration->ebay_returns_accepted_option),
			'ebayListingDuration' => $this->ebay_profile->getConfiguration('EBAY_LISTING_DURATION'),
			'automaticallyRelist' => $this->ebay_profile->getConfiguration('EBAY_AUTOMATICALLY_RELIST'),
			'ebay_paypal_email' => $ebay_paypal_email,
			'returnsConditionAccepted' => $returnsConditionAccepted,
			'ebayListingDuration' => $ebayListingDuration,
			'automaticallyRelist' => Configuration::get('EBAY_AUTOMATICALLY_RELIST'),
			'sizes' => ImageType::getImagesTypes('products'),
			'sizedefault' => (int)$this->ebay_profile->getConfiguration('EBAY_PICTURE_SIZE_DEFAULT'),
			'sizebig' => (int)$this->ebay_profile->getConfiguration('EBAY_PICTURE_SIZE_BIG'),
			'sizesmall' => (int)$this->ebay_profile->getConfiguration('EBAY_PICTURE_SIZE_SMALL'),
            'sync_products_by_cron' => Configuration::get('EBAY_SYNC_PRODUCTS_BY_CRON'),
            'sync_products_by_cron_url' => $sync_products_by_cron_url,
            'is_multishop'  => $this->is_multishop,
            'sync_orders_by_cron_url' => $sync_orders_by_cron_url,
			'within_values' => unserialize(Configuration::get('EBAY_RETURNS_WITHIN_VALUES')),
			'within' => $returns_policy_configuration->ebay_returns_within,
			'whopays_values' => unserialize(Configuration::get('EBAY_RETURNS_WHO_PAYS_VALUES')),
			'whopays' => $returns_policy_configuration->ebay_returns_who_pays,
			'activate_logs' => Configuration::get('EBAY_ACTIVATE_LOGS'),
			'is_writable' => is_writable(_PS_MODULE_DIR_.'ebay/log/request.txt'),
			'log_file_exists' => file_exists(_PS_MODULE_DIR_.'ebay/log/request.txt'),
			'activate_mails' => Configuration::get('EBAY_ACTIVATE_MAILS'),
			'account_setting' => $account_setting,
			'picture_per_listing' => $picture_per_listing,
			'hasEbayBoutique' => isset($user_profile['StoreUrl']) && !empty($user_profile['StoreUrl']) ? true : false,
            'stats' => Configuration::get('EBAY_SEND_STATS')
		);

		if (Tools::getValue('relogin'))
		{
			$this->login();

			$smarty_vars = array_merge($smarty_vars, array(
				'relogin' => true,
				'redirect_url' => $ebay->getLoginUrl().'?SignIn&runame='.$ebay->runame.'&SessID='.$this->context->cookie->eBaySession,
			));
		}
		else
			$smarty_vars['relogin'] = false;

		if (Tools::getValue('action') == 'regenerate_token')
			$smarty_vars['check_token_tpl'] = $this->_displayCheckToken();

		$this->smarty->assign($smarty_vars);

		return $this->display(dirname(__FILE__), '/views/templates/hook/formParameters.tpl');
	}


	public function login()
	{
		$ebay = new EbayRequest();
		
		$session_id = $ebay->login();
		$this->context->cookie->eBaySession = $session_id;
		Configuration::updateValue('EBAY_API_SESSION', $session_id, false, 0, 0);

		return $session_id;
	}

	private function _getListingDurations()
	{
		return array(
			'Days_1' => $this->l('1 Day'),
			'Days_3' => $this->l('3 Days'),
			'Days_5' => $this->l('5 Days'),
			'Days_7' => $this->l('7 Days'),
			'Days_10' => $this->l('10 Days'),
			'Days_30' => $this->l('30 Days'),
			'GTC' => $this->l('Good \'Till Canceled')
		);
	}

	private function _postProcessStats()
	{
        if (Configuration::updateValue('EBAY_SEND_STATS', Tools::getValue('stats') ? 1 : 0, false, 0, 0))
    		$this->html .= $this->displayConfirmation($this->l('Settings updated'));
    	else
    		$this->html .= $this->displayError($this->l('Settings failed'));        
    }


	private function _postProcessParameters()
	{
		// Saving new configurations
		$picture_per_listing = (int)Tools::getValue('picture_per_listing');
		if ($picture_per_listing < 0)
			$picture_per_listing = 0;

		if ($this->ebay_profile->setConfiguration('EBAY_PAYPAL_EMAIL', pSQL(Tools::getValue('ebay_paypal_email')))
//			&& ($this->ebay_profile->ebay_user_identifier = pSQL(Tools::getValue('ebay_identifier')))
			&& $this->ebay_profile->setConfiguration('EBAY_SHOP', pSQL(Tools::getValue('ebay_shop')))
			&& $this->ebay_profile->setConfiguration('EBAY_SHOP_POSTALCODE', pSQL(Tools::getValue('ebay_shop_postalcode')))
			&& $this->ebay_profile->setConfiguration('EBAY_LISTING_DURATION', Tools::getValue('listingdurations'))
			&& $this->ebay_profile->setConfiguration('EBAY_PICTURE_SIZE_DEFAULT', (int)Tools::getValue('sizedefault'))
			&& $this->ebay_profile->setConfiguration('EBAY_PICTURE_SIZE_SMALL', (int)Tools::getValue('sizesmall'))
			&& $this->ebay_profile->setConfiguration('EBAY_PICTURE_SIZE_BIG', (int)Tools::getValue('sizebig'))
			&& $this->ebay_profile->setConfiguration('EBAY_AUTOMATICALLY_RELIST', Tools::getValue('automaticallyrelist'))
			&& $this->ebay_profile->setReturnsPolicyConfiguration(
				pSQL(Tools::getValue('returnswithin')),
				pSQL(Tools::getValue('returnswhopays')),
				(version_compare(_PS_VERSION_, '1.5', '>') ? Tools::nl2br(Tools::getValue('ebay_returns_description')) : nl2br2(Tools::getValue('ebay_returns_description'))),
				pSQL(Tools::getValue('ebay_returns_accepted_option'))
			)
            && Configuration::updateValue('EBAY_SYNC_PRODUCTS_BY_CRON', ('cron' === Tools::getValue('sync_products_mode')))
            && Configuration::updateValue('EBAY_SEND_STATS', Tools::getValue('stats') ? 1 : 0, false, 0, 0)
//			&& $this->ebay_profile->setConfiguration('EBAY_IDENTIFIER', pSQL(Tools::getValue('ebay_identifier')))
			&& $this->setConfiguration('EBAY_ACTIVATE_LOGS', Tools::getValue('activate_logs') ? 1 : 0)
			&& $this->setConfiguration('EBAY_ACTIVATE_MAILS', Tools::getValue('activate_mails') ? 1 : 0)
			&& $this->ebay_profile->setConfiguration('EBAY_PICTURE_PER_LISTING', $picture_per_listing)
		){
			if(Tools::getValue('activate_logs') == 0)
				if(file_exists(dirname(__FILE__).'/log/request.txt'))
					unlink(dirname(__FILE__).'/log/request.txt');
			$this->html .= $this->displayConfirmation($this->l('Settings updated'));
		}
		else
			$this->html .= $this->displayError($this->l('Settings failed'));
	}

	/**
		* Category Form Config Methods
		*
	*/
	public function getChildCategories($categories, $id, $path = array(), $path_add = '')
	{
		$category_tab = array();

		if ($path_add != '')
			$path[] = $path_add;
        
        if (isset($categories[$id])) {
            $cats = $categories[$id];
        } elseif (!$id) {
            $cats = reset($categories); // fix to deal with the case where the first element of categories has no key
        }

		if (isset($cats))
			foreach ($cats as $idc => $cc)
			{
				$name = '';
				if ($path)
					foreach ($path as $p)
						$name .= $p.' > ';

				$name .= $cc['infos']['name'];
				$category_tab[] = array('id_category' => $cc['infos']['id_category'], 'name' => $name);
				$categoryTmp = $this->getChildCategories($categories, $idc, $path, $cc['infos']['name']);
				$category_tab = array_merge($category_tab, $categoryTmp);
			}

		return $category_tab;
	}

	private function _displayFormCategory()
	{
		$is_one_dot_five = version_compare(_PS_VERSION_, '1.5', '>');

		// Load prestashop ebay's configuration
		$configs = Configuration::getMultiple(array('EBAY_CATEGORY_LOADED', 'EBAY_SECURITY_TOKEN'));

		// Check if the module is configured
		if (!$this->ebay_profile->getConfiguration('EBAY_PAYPAL_EMAIL'))
		{
			$this->smarty->assign('error_form_category', 'true');
			return $this->display(dirname(__FILE__), '/views/templates/hook/error_paypal_email.tpl');
		}

		// Load categories only if necessary
		if (EbayCategoryConfiguration::getTotalCategoryConfigurations($this->ebay_profile->id) && Tools::getValue('section') != 'category')
		{
			$this->smarty->assign(array(
				'isOneDotFive' => $is_one_dot_five,
				'controller' => Tools::getValue('controller'),
				'tab' => Tools::getValue('tab'),
				'configure' => Tools::getValue('configure'),
				'token' => Tools::getValue('token'),
				'tab_module' => Tools::getValue('tab_module'),
				'module_name' => Tools::getValue('module_name'),
				'form_categories' => EbaySynchronizer::getNbSynchronizableEbayCategorie()
			));

			return $this->display(dirname(__FILE__), '/views/templates/hook/pre_form_categories.tpl');
		}

		// Display eBay Categories
		if (!isset($configs['EBAY_CATEGORY_LOADED']) || !$configs['EBAY_CATEGORY_LOADED'])
		{
			$ebay = new EbayRequest();
			EbayCategory::insertCategories($ebay->getCategories(), $ebay->getCategoriesSkuCompliancy());
			$this->setConfiguration('EBAY_CATEGORY_LOADED', 1);
			$this->setConfiguration('EBAY_CATEGORY_LOADED_DATE', date('Y-m-d H:i:s'));
		}
        
		// Smarty
		$template_vars = array(
			'alerts' => $this->_getAlertCategories(),
			'tabHelp' => '&id_tab=7',
			'id_lang' => $this->context->cookie->id_lang,
			'id_ebay_profile' => $this->ebay_profile->id,
			'_path' => $this->_path,
			'configs' => $configs,
			'_module_dir_' => _MODULE_DIR_,
			'isOneDotFive' => $is_one_dot_five,
			'request_uri' => $_SERVER['REQUEST_URI'],
			'controller' => Tools::getValue('controller'),
			'tab' => Tools::getValue('tab'),
			'configure' => Tools::getValue('configure'),
			'token' => Tools::getValue('token'),
			'tab_module' => Tools::getValue('tab_module'),
			'module_name' => Tools::getValue('module_name'),
			'date' => pSQL(date('Ymdhis')),
			'form_categories' => EbaySynchronizer::getNbSynchronizableEbayCategorie(),
			'nb_categorie' => count(Category::getCategories($this->context->cookie->id_lang))
		);

		$this->smarty->assign($template_vars);

		return $this->display(dirname(__FILE__), '/views/templates/hook/form_categories.tpl');
	}

	private function _displayFormItemsSpecifics()
	{
		$is_one_dot_five = version_compare(_PS_VERSION_, '1.5', '>');

		// Smarty
		$template_vars = array(
			'id_tab' => Tools::safeOutput(Tools::getValue('id_tab')),
			'controller' => Tools::getValue('controller'),
			'tab' => Tools::getValue('tab'),
			'configure' => Tools::getValue('configure'),
			'tab_module' => Tools::getValue('tab_module'),
			'module_name' => Tools::getValue('module_name'),
			'token' => Tools::getValue('token'),
			'ebay_token' => Configuration::get('EBAY_SECURITY_TOKEN'),			
			'_module_dir_' => _MODULE_DIR_,
			'ebay_categories' => EbayCategoryConfiguration::getEbayCategories($this->ebay_profile->id),
			'id_lang' => $this->context->cookie->id_lang,
			'id_ebay_profile' => $this->ebay_profile->id,
			'_path' => $this->_path,
			'possible_attributes' => AttributeGroup::getAttributesGroups($this->context->cookie->id_lang),
			'possible_features' => Feature::getFeatures($this->context->cookie->id_lang, true),
			'date' => pSQL(date('Ymdhis')),
			'conditions' => $this->_translatePSConditions(EbayCategoryConditionConfiguration::getPSConditions()),
			'form_items_specifics' => EbaySynchronizer::getNbSynchronizableEbayCategoryCondition(),
			'form_items_specifics_mixed' => EbaySynchronizer::getNbSynchronizableEbayCategoryConditionMixed(),
			'isOneDotFive' => $is_one_dot_five
		);

		$this->smarty->assign($template_vars);

		return $this->display(dirname(__FILE__), '/views/templates/hook/formItemsSpecifics.tpl');
	}

	/*
	 * Method to call the translation tool properly on every version to translate the PrestaShop conditions
	 *
	 */
	private function _translatePSConditions($ps_conditions)
	{
		foreach ($ps_conditions as &$condition)
		{
			switch ($condition)
			{
				case 'new':
					$condition = $this->l('new');
					break;
				case 'used':
					$condition = $this->l('used');
					break;
				case 'refurbished':
					$condition = $this->l('refurbished');
					break;
			}
		}

		return $ps_conditions;
	}

	public function _postProcessCategory()
	{
		// Insert and update categories
		if (($percents = Tools::getValue('percent')) && ($ebay_categories = Tools::getValue('category')))
		{
			    
			foreach ($percents as $id_category => $percent)
			{
				$data = array();
				$date = date('Y-m-d H:i:s');
				if ($percent['value'] != '') {
					$percent_sign_type = explode(':', $percent['sign']);
					$percentValue = ($percent_sign_type[0] == '-' ? $percent_sign_type[0] : '') . $percent['value'] . ($percent_sign_type[1] == '%' ? $percent_sign_type[1] : '');
				} 
				else 
				{
					$percentValue = null;
				}
				if (isset($ebay_categories[$id_category]))
					$data = array(
						'id_ebay_profile' => $this->ebay_profile->id,
						'id_country' => 8,
						'id_ebay_category' => (int)$ebay_categories[$id_category],
						'id_category' => (int)$id_category,
						'percent' => pSQL($percentValue),
						'date_upd' => pSQL($date),
						'sync' => 0
					);
				    

				if (EbayCategoryConfiguration::getIdByCategoryId($this->ebay_profile->id, $id_category))
				{
					if ($data)
						EbayCategoryConfiguration::updateByIdProfileAndIdCategory($this->ebay_profile->id, $id_category, $data);
					else
						EbayCategoryConfiguration::deleteByIdCategory($this->ebay_profile->id, $id_category);
				}
				elseif ($data)
				{
					$data['date_add'] = $date;
					EbayCategoryConfiguration::add($data);
				}
			}

			// make sur the ItemSpecifics and Condition data are refresh when we load the dedicated config screen the next time
			$this->ebay_profile->deleteConfigurationByName('EBAY_SPECIFICS_LAST_UPDATE');
		}


		// update extra_images for all products
		if (($all_nb_extra_images = Tools::getValue('all-extra-images-value', -1)) != -1)
		{
			$product_ids = EbayCategoryConfiguration::getAllProductIds($this->ebay_profile->id);

			foreach ($product_ids as $product_id)
				EbayProductConfiguration::insertOrUpdate($product_id, array(
					'extra_images' => $all_nb_extra_images ? $all_nb_extra_images : 0
				));
		}

		// update products configuration
		if (is_array(Tools::getValue('showed_products')))
		{
			$showed_product_ids = array_keys(Tools::getValue('showed_products'));

			if (Tools::getValue('to_synchronize'))
				$to_synchronize_product_ids = array_keys(Tools::getValue('to_synchronize'));
			else
				$to_synchronize_product_ids = array();

			// TODO remove extra_images
			$extra_images = Tools::getValue('extra_images');

			foreach ($showed_product_ids as $product_id)
				EbayProductConfiguration::insertOrUpdate($product_id, array(
					'blacklisted' => in_array($product_id, $to_synchronize_product_ids) ? 0 : 1,
					//'extra_images' => $extra_images[$product_id] ? $extra_images[$product_id] : 0
					'extra_images' => 0
				));
		}

		if (Tools::getValue('ajax'))
		{
			die('{"valid" : true}');
		}

		$this->html .= $this->displayConfirmation($this->l('Settings updated'));
	}

	private function _postProcessSpecifics() 
	{
		// Save specifics
		if(Tools::getValue('specific'))
		{
			foreach (Tools::getValue('specific') as $specific_id => $data)
			{
				if ($data)
					list($data_type, $value) = explode('-', $data);
				else
					$data_type = null;

				$field_names = EbayCategorySpecific::getPrefixToFieldNames();
				$data = array_combine(array_values($field_names), array(null, null, null, null));

				if ($data_type)
					$data[$field_names[$data_type]] = $value;

				if (version_compare(_PS_VERSION_, '1.5', '>'))
					Db::getInstance()->update('ebay_category_specific', $data, 'id_ebay_category_specific = '.$specific_id);
				else
					Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_category_specific', $data, 'UPDATE', 'id_ebay_category_specific = '.$specific_id);
			}
		}

		// save conditions
		foreach (Tools::getValue('condition') as $category_id => $condition)
			foreach ($condition as $type => $condition_ref)
				EbayCategoryConditionConfiguration::replace(array('id_ebay_profile' => $this->ebay_profile->id, 'id_condition_ref' => $condition_ref, 'id_category_ref' => $category_id, 'condition_type' => $type));

		$this->html .= $this->displayConfirmation($this->l('Settings updated'));
	}

	private function _getExistingInternationalCarrier()
	{
		$existing_international_carriers = EbayShipping::getInternationalShippings($this->ebay_profile->id);

		foreach ($existing_international_carriers as $key => &$carrier)
			//get All shipping location associated
			$carrier['shippingLocation'] = DB::getInstance()->ExecuteS('SELECT *
				FROM '._DB_PREFIX_.'ebay_shipping_international_zone
				WHERE `id_ebay_profile` = '.(int)$this->ebay_profile->id.' 
                AND id_ebay_shipping = \''.(int)$carrier['id_ebay_shipping'].'\'');

		return $existing_international_carriers;
	}

	/**
	* Process entered data for the shipping screen
	*/
	private function _postProcessShipping()
	{
		//Update excluded location
		if (Tools::getValue('excludeLocationHidden'))
		{
			Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'ebay_shipping_zone_excluded 
				SET excluded = 0
				WHERE `id_ebay_profile` = '.(int)$this->ebay_profile->id);

			if ($exclude_locations = Tools::getValue('excludeLocation'))
			{
				$where = '(0 || ';

				foreach ($exclude_locations as $location => $on)
					//build update $where
					$where .= 'location = "'.pSQL($location).'" || ';

				$where .= ' 0)';
				$where .= ' AND `id_ebay_profile` = '.(int)$this->ebay_profile->id;

				if (version_compare(_PS_VERSION_, '1.5', '>'))
					DB::getInstance()->update('ebay_shipping_zone_excluded', array('excluded' => 1), $where);
				else
					Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_shipping_zone_excluded', array('excluded' => 1), 'UPDATE', $where );
			}
		}

		//Update global information about shipping (delivery time, ...)
		$this->ebay_profile->setConfiguration('EBAY_DELIVERY_TIME', Tools::getValue('deliveryTime'));
		//Update Shipping Method for National Shipping (Delete And Insert)
		EbayShipping::truncate($this->ebay_profile->id);

		if ($ebay_carriers = Tools::getValue('ebayCarrier'))
		{

			$ps_carriers = Tools::getValue('psCarrier');
			$extra_fees = Tools::getValue('extrafee');

			foreach ($ebay_carriers as $key => $ebay_carrier)
			{
				if (!empty($ebay_carrier) && !empty($ps_carriers[$key]))
				{
					//Get id_carrier and id_zone from ps_carrier
					$infos = explode('-', $ps_carriers[$key]); 
					EbayShipping::insert($this->ebay_profile->id, $ebay_carrier, $infos[0], $extra_fees[$key], $infos[1]);
				}
			}
		}

		Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'ebay_shipping_international_zone
			WHERE `id_ebay_profile` = '.(int)$this->ebay_profile->id);

		if ($ebay_carriers_international = Tools::getValue('ebayCarrier_international'))
		{
			$ps_carriers_international = Tools::getValue('psCarrier_international');
			$extra_fees_international = Tools::getValue('extrafee_international');
			$international_shipping_locations = Tools::getValue('internationalShippingLocation');
			$international_excluded_shipping_locations = Tools::getValue('internationalExcludedShippingLocation');

			foreach ($ebay_carriers_international as $key => $ebay_carrier_international)
			{//				
				if (!empty($ebay_carrier_international) && !empty($ps_carriers_international[$key]) && isset($international_shipping_locations[$key]))
				{
					$infos = explode('-', $ps_carriers_international[$key]); 
					EbayShipping::insert($this->ebay_profile->id, $ebay_carrier_international, $infos[0], $extra_fees_international[$key], $infos[1], true);
					$last_id = EbayShipping::getLastShippingId($this->ebay_profile->id);

					foreach (array_keys($international_shipping_locations[$key]) as $id_ebay_zone)
						EbayShippingInternationalZone::insert($this->ebay_profile->id, $last_id, $id_ebay_zone);
				}
			}
		}
	}

	/**
	 * Display form for the shipping screen
	 *
	 **/
	private function _displayFormShipping()
	{	
		$configKeys = array(
			'EBAY_CATEGORY_LOADED',
			'EBAY_SECURITY_TOKEN',
			'PS_LANG_DEFAULT'
		);
		// Load prestashop ebay's configuration
		$configs = Configuration::getMultiple($configKeys);
		
		$profile_configs = $this->ebay_profile->getMultiple(array(
			'EBAY_DELIVERY_TIME',
			'EBAY_ZONE_NATIONAL',
			'EBAY_ZONE_INTERNATIONAL',
		));
		
		// Check if the module is configured
		if (!$this->ebay_profile->getConfiguration('EBAY_PAYPAL_EMAIL'))
		{
			$this->smarty->assign('error_form_shipping', 'true');
			return $this->display(dirname(__FILE__), '/views/templates/hook/error_paypal_email.tpl');
		}

		$nb_shipping_zones_excluded = DB::getInstance()->getValue('SELECT COUNT(*) 
			FROM '._DB_PREFIX_.'ebay_shipping_zone_excluded
			WHERE `id_ebay_profile` = '.(int)$this->ebay_profile->id);

		if (!$nb_shipping_zones_excluded)
			$this->_loadEbayExcludedLocations();

		$module_filters = version_compare(_PS_VERSION_, '1.4.5', '>=') ? Carrier::CARRIERS_MODULE : 2;

		//INITIALIZE CACHE
		$psCarrierModule = $this->ebay_profile->getCarriers($configs['PS_LANG_DEFAULT'], false, false, false, null, $module_filters);

		$url_vars = array(
			'id_tab' => '3',
			'section' =>'shipping'
		);

		if (version_compare(_PS_VERSION_, '1.5', '>'))
			$url_vars['controller'] = Tools::safeOutput(Tools::getValue('controller'));
		else
			$url_vars['tab'] = Tools::safeOutput(Tools::getValue('tab'));

		$zones = Zone::getZones(true);
		foreach ($zones as &$zone)
			$zone['carriers'] = Carrier::getCarriers($this->context->language->id, true, false, $zone['id_zone']);

		$this->smarty->assign(array(
			'eBayCarrier' => $this->_getCarriers(),
//			'psCarrier' => Carrier::getCarriers($configs['PS_LANG_DEFAULT']),
			'psCarrier' => $this->ebay_profile->getCarriers($configs['PS_LANG_DEFAULT']),
			'psCarrierModule' => $psCarrierModule,
			'existingNationalCarrier' => EbayShipping::getNationalShippings($this->ebay_profile->id),
			'existingInternationalCarrier' => $this->_getExistingInternationalCarrier(),
			'deliveryTime' => $profile_configs['EBAY_DELIVERY_TIME'],
			'prestashopZone' => Zone::getZones(),
			'excludeShippingLocation' => $this->_cacheEbayExcludedLocation(),
			'internationalShippingLocations' => $this->_getInternationalShippingLocations(),
			'deliveryTimeOptions' => $this->_getDeliveryTimeOptions(),
			'formUrl' => $this->_getUrl($url_vars),
			'ebayZoneNational' => (isset($profile_configs['EBAY_ZONE_NATIONAL']) ? $profile_configs['EBAY_ZONE_NATIONAL'] : false),
			'ebayZoneInternational' => (isset($profile_configs['EBAY_ZONE_INTERNATIONAL']) ? $profile_configs['EBAY_ZONE_INTERNATIONAL'] : false),
			'ebay_token' => $configs['EBAY_SECURITY_TOKEN'],
            'id_ebay_profile' => $this->ebay_profile->id,	
			'newPrestashopZone' => $zones,
			'shipping_uiux' => EbaySynchronizer::getNbSynchronizableEbayShipping(),
			'shipping_international_uiux' => EbaySynchronizer::getNbSynchronizableEbayShippingInternational()
		));

		return $this->display(dirname(__FILE__), '/views/templates/hook/shipping.tpl');
	}

	/**
	 * Template Manager Form Config Methods
	 *
	 **/
	private function _displayFormTemplateManager()
	{
		// Check if the module is configured
		if (!$this->ebay_profile->getConfiguration('EBAY_PAYPAL_EMAIL'))
			return '<p class="error"><b>'.$this->l('Please configure the \'General settings\' tab before using this tab').'</b></p><br /><script type="text/javascript">$("#menuTab4").addClass("wrong")</script>';

		$iso = $this->context->language->iso_code;
		$iso_tiny_mce = (file_exists(_PS_ROOT_DIR_.'/js/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en');

		// Display Form
		$url_vars = array(
			'id_tab' => '4',
			'section' => 'template'
		);

		if (version_compare(_PS_VERSION_, '1.5', '>'))
			$url_vars['controller'] = Tools::safeOutput(Tools::getValue('controller'));
		else
			$url_vars['tab'] = Tools::safeOutput(Tools::getValue('tab'));

		$action_url = $this->_getUrl($url_vars);
		$forbiddenJs = array('textarea', 'script', 'onmousedown', 'onmousemove', 'onmmouseup', 'onmouseover', 'onmouseout', 'onload', 'onunload', 'onfocus', 'onblur', 'onchange', 'onsubmit', 'ondblclick', 'onclick', 'onkeydown', 'onkeyup', 'onkeypress', 'onmouseenter', 'onmouseleave', 'onerror');

		if (Tools::getValue('reset_template'))
			$ebay_product_template = str_replace($forbiddenJs, '', $this->_getProductTemplateContent());
		else
			$ebay_product_template = str_replace($forbiddenJs, '', Tools::getValue('ebay_product_template', $this->ebay_profile->getConfiguration('EBAY_PRODUCT_TEMPLATE')));
		$ebay_product_template_title = $this->ebay_profile->getConfiguration('EBAY_PRODUCT_TEMPLATE_TITLE');

		$smarty_vars = array(
			'action_url' => $action_url,
			'ebay_product_template' => $ebay_product_template,
			'ebay_product_template_title' => $ebay_product_template_title,
			'features_product' => Feature::getFeatures($this->context->language->id),
			'ad' => dirname($_SERVER['PHP_SELF']),
			'base_uri' => __PS_BASE_URI__,
			'is_one_dot_three' => (substr(_PS_VERSION_, 0, 3) == '1.3'),
			'is_one_dot_five' => version_compare(_PS_VERSION_, '1.5', '>'),
			'theme_css_dir' => _THEME_CSS_DIR_,
			'form_template_manager' => $ebay_product_template_title == '{TITLE}' ? 0 : 1
		);

		if (substr(_PS_VERSION_, 0, 3) == '1.3')
		{
			$smarty_vars['theme_name'] = _THEME_NAME_;
			$smarty_vars['language'] = file_exists(_PS_ROOT_DIR_.'/js/tinymce/jscripts/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en';
		}
		elseif (version_compare(_PS_VERSION_, '1.5', '>'))
			$smarty_vars['iso'] = (file_exists(_PS_ROOT_DIR_.'/js/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en');
		else
		{
			$smarty_vars['iso_type_mce'] = $iso_tiny_mce;
			$smarty_vars['ps_js_dir'] = _PS_JS_DIR_;
		}
        
		$this->smarty->assign($smarty_vars);

		return $this->display(dirname(__FILE__), '/views/templates/hook/formTemplateManager.tpl');
	}

	private function _postProcessTemplateManager()
	{
		$ebay_product_template = Tools::getValue('ebay_product_template');
		$ebay_product_template_title = Tools::getValue('ebay_product_template_title');
		if (empty($ebay_product_template_title))
			$ebay_product_template_title = '{TITLE}';

		// work around for the tinyMCE bug deleting the css line
		$css_line = '<link rel="stylesheet" type="text/css" href="'.$this->_getModuleUrl().'views/css/ebay.css" />';
		$ebay_product_template = $css_line.$ebay_product_template;

			// Saving new configurations
		if ($this->ebay_profile->setConfiguration('EBAY_PRODUCT_TEMPLATE', $ebay_product_template, true) && $this->ebay_profile->setConfiguration('EBAY_PRODUCT_TEMPLATE_TITLE', $ebay_product_template_title))
			$this->html .= $this->displayConfirmation($this->l('Settings updated'));
		else
			$this->html .= $this->displayError($this->l('Settings failed'));
	}

	/**
	 * Ebay Sync Form Config Methods
	 *
	 **/
	private function _displayFormEbaySync()
	{
		// Check if the module is configured
		if (!$this->ebay_profile->getConfiguration('EBAY_PAYPAL_EMAIL'))			
			return '<p class="error"><b>'.$this->l('Please configure the \'General settings\' tab before using this tab').'</b></p><br /><script type="text/javascript">$("#menuTab5").addClass("wrong")</script>';
		if (!EbayCategoryConfiguration::getTotalCategoryConfigurations($this->ebay_profile->id))
			return '<p class="error"><b>'.$this->l('Please configure the \'Category settings\' tab before using this tab').'</b></p><br /><script type="text/javascript">$("#menuTab5").addClass("wrong")</script>';

		if (version_compare(_PS_VERSION_, '1.5', '>'))
		{
			$sql = '
				SELECT COUNT( * ) FROM (
					SELECT COUNT(p.id_product) AS nb
						FROM  `'._DB_PREFIX_.'product` AS p
						INNER JOIN  `'._DB_PREFIX_.'stock_available` AS s 
                        ON p.id_product = s.id_product';
            if (version_compare(_PS_VERSION_, '1.5', '>'))
                $sql .= ' INNER JOIN  `'._DB_PREFIX_.'product_shop` AS ps 
                        ON p.id_product = ps.id_product 
                        AND ps.id_shop = '.(int)$this->ebay_profile->id_shop;
            $sql .= ' WHERE s.`quantity` > 0
						AND  p.`active` = 1
						AND  p.`id_category_default`
						IN (
							SELECT  `id_category`
							FROM  `'._DB_PREFIX_.'ebay_category_configuration`
							WHERE  `id_ebay_category` > 0
						)
						'.$this->addSqlRestrictionOnLang('s').'
						AND p.id_product NOT IN ('.EbayProductConfiguration::getBlacklistedProductIdsQuery().')
						GROUP BY p.id_product
				)TableReponse';
            $nb_products_mode_a = Db::getInstance()->getValue($sql);
            
            $sql = '
				SELECT COUNT( * ) FROM (
					SELECT COUNT( p.id_product ) AS nb
						FROM  `'._DB_PREFIX_.'product` AS p
						INNER JOIN  `'._DB_PREFIX_.'stock_available` AS s 
                        ON p.id_product = s.id_product';
            if (version_compare(_PS_VERSION_, '1.5', '>'))
                $sql .= ' INNER JOIN  `'._DB_PREFIX_.'product_shop` AS ps
                        ON p.id_product = ps.id_product 
                        AND ps.id_shop = '.(int)$this->ebay_profile->id_shop;
            $sql .= ' WHERE s.`quantity` > 0
						AND  p.`active` = 1
						AND  p.`id_category_default`
						IN (
							SELECT  `id_category`
							FROM  `'._DB_PREFIX_.'ebay_category_configuration`
							WHERE  `id_ebay_category` > 0 AND `sync` = 1
						)'.$this->addSqlRestrictionOnLang('s').'
						AND p.id_product NOT IN ('.EbayProductConfiguration::getBlacklistedProductIdsQuery().')
						GROUP BY p.id_product
				)TableReponse';
            $nb_products_mode_b = Db::getInstance()->getValue($sql);
		}
		else
		{
			$sql = '
				SELECT COUNT(`id_product`) as nb
				FROM `'._DB_PREFIX_.'product` AS p';
            if (version_compare(_PS_VERSION_, '1.5', '>'))
                $sql .= ' INNER JOIN  `'._DB_PREFIX_.'product_shop` AS ps
                ON p.id_product = ps.id_product 
                AND ps.id_shop = '.(int)$this->ebay_profile->id_shop;
            $sql .= ' WHERE p.`quantity` > 0
				AND p.`active` = 1
				AND p.`id_category_default` IN (
					SELECT `id_category`
					FROM `'._DB_PREFIX_.'ebay_category_configuration`
					WHERE `id_ebay_category` > 0)
				AND p.`id_product` NOT IN ('.EbayProductConfiguration::getBlacklistedProductIdsQuery().')';
            $nb_products_mode_a = Db::getInstance()->getValue($sql);

			$sql = 'SELECT COUNT(`id_product`) as nb
				FROM `'._DB_PREFIX_.'product` AS p';
            if (version_compare(_PS_VERSION_, '1.5', '>'))
                $sql .= ' INNER JOIN  `'._DB_PREFIX_.'product_shop` AS ps
                ON p.id_product = ps.id_product 
                AND ps.id_shop = '.(int)$this->ebay_profile->id_shop;
            $sql .= ' WHERE p.`quantity` > 0
				AND p.`active` = 1
				AND p.`id_category_default` IN (
					SELECT `id_category`
					FROM `'._DB_PREFIX_.'ebay_category_configuration`
					WHERE `id_ebay_category` > 0
					AND `sync` = 1)
				AND p.`id_product` NOT IN ('.EbayProductConfiguration::getBlacklistedProductIdsQuery().')';
            $nb_products_mode_b = Db::getInstance()->getValue($sql);
		}

		$nb_products = ($this->ebay_profile->getConfiguration('EBAY_SYNC_PRODUCTS_MODE') == 'B' ? $nb_products_mode_b : $nb_products_mode_a);
		$prod_nb = ($nb_products < 2 ? $this->l('product') : $this->l('products'));

		// Display Form
		$url_vars = array(
			'id_tab' => '5',
			'section' => 'sync'
		);

		if (version_compare(_PS_VERSION_, '1.5', '>'))
			$url_vars['controller'] = Tools::safeOutput(Tools::getValue('controller'));
		else
			$url_vars['tab'] = Tools::safeOutput(Tools::getValue('tab'));

		$action_url = $this->_getUrl($url_vars);

		// Loading categories
		$category_config_list = array();

		foreach (EbayCategoryConfiguration::getEbayCategoryConfigurations($this->ebay_profile->id) as $c)
			$category_config_list[$c['id_category']] = $c;

		$category_list = $this->getChildCategories(Category::getCategories($this->context->language->id), 0);
		$categories = array();

		if ($category_list)
		{
			$alt_row = false;
			foreach ($category_list as $category)
			{
				if (isset($category_config_list[$category['id_category']]['id_ebay_category'])
					&& $category_config_list[$category['id_category']]['id_ebay_category'] > 0)
				{
					$categories[] = array(
						'row_class' => $alt_row ? 'alt_row' : '',
						'value' => $category['id_category'],
						'checked' => ($category_config_list[$category['id_category']]['sync'] == 1 ? 'checked="checked"' : ''),
						'name' => $category['name']
					);

					$alt_row = !$alt_row;
				}
			}
		}

		$nb_products_sync_url = _MODULE_DIR_.'ebay/ajax/getNbProductsSync.php?token='.Configuration::get('EBAY_SECURITY_TOKEN').'&time='.pSQL(date('Ymdhis')).'&profile='.$this->ebay_profile->id;
		$sync_products_url = _MODULE_DIR_.'ebay/ajax/eBaySyncProduct.php?token='.Configuration::get('EBAY_SECURITY_TOKEN').'&option=\'+option+\'&profile='.$this->ebay_profile->id.'&time='.pSQL(date('Ymdhis'));

		$smarty_vars = array(
			'path' => $this->_path,
			'nb_products' => $nb_products ? $nb_products : 0,
			'nb_products_mode_a' => $nb_products_mode_a ? $nb_products_mode_a : 0,
			'nb_products_mode_b' => $nb_products_mode_b ? $nb_products_mode_b : 0,
			'nb_products_sync_url' => $nb_products_sync_url,
			'sync_products_url' => $sync_products_url,
			'action_url' => $action_url,
			'ebay_sync_option_resync' => $this->ebay_profile->getConfiguration('EBAY_SYNC_OPTION_RESYNC'),
			'categories' => $categories,
			'sync_1' => (Tools::getValue('section') == 'sync' && Tools::getValue('ebay_sync_mode') == "1" && Tools::getValue('btnSubmitSyncAndPublish')),
			'sync_2' => (Tools::getValue('section') == 'sync' && Tools::getValue('ebay_sync_mode') == "2" && Tools::getValue('btnSubmitSyncAndPublish')),
			'is_sync_mode_b' => ($this->ebay_profile->getConfiguration('EBAY_SYNC_PRODUCTS_MODE') == 'B'),
			'ebay_sync_mode' => (int)($this->ebay_profile->getConfiguration('EBAY_SYNC_MODE') ? $this->ebay_profile->getConfiguration('EBAY_SYNC_MODE') : 2),
			'prod_str' => $nb_products >= 2 ? $this->l('products') : $this->l('product')
		);

		$this->smarty->assign($smarty_vars);

		return $this->display(dirname(__FILE__), '/views/templates/hook/formEbaySync.tpl');
	}

	private function _postProcessEbaySync()
	{
		// Update Sync Option
		$this->ebay_profile->setConfiguration('EBAY_SYNC_OPTION_RESYNC', (Tools::getValue('ebay_sync_option_resync') == 1 ? 1 : 0));

		// Empty error result
		$this->ebay_profile->setConfiguration('EBAY_SYNC_LAST_PRODUCT', 0);

		if (file_exists(dirname(__FILE__).'/log/syncError.php'))
			@unlink(dirname(__FILE__).'/log/syncError.php');

		$this->setConfiguration('EBAY_SYNC_MODE', Tools::safeOutput(Tools::getValue('ebay_sync_mode')));

		if (Tools::getValue('ebay_sync_products_mode') == 'A')
			$this->ebay_profile->setConfiguration('EBAY_SYNC_PRODUCTS_MODE', 'A');
		else
		{
			$this->ebay_profile->setConfiguration('EBAY_SYNC_PRODUCTS_MODE', 'B');

			// Select the sync Categories and Retrieve product list for eBay (which have matched and sync categories)
			if (Tools::getValue('category'))
			{
				EbayCategoryConfiguration::updateByIdProfile($this->ebay_profile->id, array('sync' => 0));
				foreach (Tools::getValue('category') as $id_category)
					EbayCategoryConfiguration::updateByIdProfileAndIdCategory($this->ebay_profile->id, $id_category, array('id_ebay_profile' => $this->ebay_profile->id, 'sync' => 1));
			}
		}
	}

	public function ajaxProductSync()
	{
		$nb_products = EbaySynchronizer::getNbSynchronizableProducts($this->ebay_profile);
		$products = EbaySynchronizer::getProductsToSynchronize($this->ebay_profile, Tools::getValue('option'));
		$nb_products_less = EbaySynchronizer::getNbProductsLess($this->ebay_profile, Tools::getValue('option'), (int)$this->ebay_profile->getConfiguration('EBAY_SYNC_LAST_PRODUCT'));
        
		// Send each product on eBay
		if (count($products))
		{
			$this->ebay_profile->setConfiguration('EBAY_SYNC_LAST_PRODUCT', (int)$products[0]['id_product']);
			EbaySynchronizer::syncProducts($products, $this->context, $this->ebay_country->getIdLang());

			// we cheat a bit to display a consistent number of products done
			$nb_products_done = min($nb_products - $nb_products_less + 1, $nb_products);

			echo 'KO|<br /><br /> <img src="../modules/ebay/views/img/loading-small.gif" border="0" /> '.$this->l('Products').' : '.$nb_products_done.' / '.$nb_products.'<br /><br />';
		}
		else
		{
			echo 'OK|'.$this->displayConfirmation($this->l('Settings updated').' ('.$this->l('Option').' '.$this->ebay_profile->getConfiguration('EBAY_SYNC_PRODUCTS_MODE').' : '.($nb_products - $nb_products_less).' / '.$nb_products.' '.$this->l('product(s) sync with eBay').')');

			if (file_exists(dirname(__FILE__).'/log/syncError.php'))
			{
				global $all_error;
				include(dirname(__FILE__).'/log/syncError.php');

				foreach ($all_error as $error)
				{
					$products_details = '<br /><u>'.$this->l('Product(s) concerned').' :</u>';

					foreach ($error['products'] as $product)
						$products_details .= '<br />- '.$product;

					echo $this->displayError($error['msg'].'<br />'.$products_details);
				}

				if ($itemConditionError)
				{
					//Add a specific message for item condition error
					$message = $this->l('The item condition value defined in your  configuration is not supported in the eBay category.').'<br/>';
					$message .= $this->l('You can modify your item condition in the configuration settings (see supported conditions by categories here: http://pages.ebay.co.uk/help/sell/item-condition.html) ');
					$message .= $this->l('A later version of the module will allow you to specify item conditions by category');
					echo $this->displayError($message);
				}

				echo '<style>#content .alert { text-align: left; width: 875px; }</style>';
				@unlink(dirname(__FILE__).'/log/syncError.php');
			}
		}
	}
	
	private function _cacheEbayExcludedLocation()
	{
		$ebay_excluded_zones = EbayShippingZoneExcluded::getAll($this->ebay_profile->id);

		$all = array();
		$excluded = array();
		$regions = array();

		foreach ($ebay_excluded_zones as $key => $zone)
		{
			if (!in_array($zone['region'], $regions))
				$regions[] = $zone['region'];

			$all[$zone['region']]['country'][] = array(
				'location' => $zone['location'],
				'description' => $zone['description'],
				'excluded' => $zone['excluded']
			);
		}

		foreach ($ebay_excluded_zones as $key => $zone)
			if (in_array($zone['location'], $regions))
				$all[$zone['location']]['description'] = $zone['description'];

		unset($all['Worldwide']);

		foreach ($all as $key => $value)
			if (!isset($value['description']))
				$all[$key]['description'] = $key;

		//get real excluded location
		foreach (EbayShippingZoneExcluded::getExcluded($this->ebay_profile->id) as $zone)
			$excluded[] = $zone['location'];

		return array(
			'all' => $all,
			'excluded' => $excluded
		);
	}

	private function _loadEbayExcludedLocations()
	{
		$ebay_request = new EbayRequest();
		$excluded_locations = $ebay_request->getExcludeShippingLocations();

		foreach ($excluded_locations as &$excluded_location)
		{
			foreach ($excluded_location as &$field)
				$field = pSQL($field);

			$excluded_location['excluded'] = 0;
			$excluded_location['id_ebay_profile'] = $this->ebay_profile->id;
		}

		if (version_compare(_PS_VERSION_, '1.5', '>'))
			Db::getInstance()->insert('ebay_shipping_zone_excluded', $excluded_locations);
		else
			foreach ($excluded_locations as $location)
				EbayShippingZoneExcluded::insert($location);
	}

	private function _getInternationalShippingLocations()
	{
		if (EbayShippingLocation::getTotal())
			return EbayShippingLocation::getEbayShippingLocations();

		$ebay = new EbayRequest();
		$locations = $ebay->getInternationalShippingLocations();

		foreach ($locations as $location)
			EbayShippingLocation::insert(array_map('pSQL', $location));

		return $locations;
	}

	private function _getDeliveryTimeOptions()
	{
		if (EbayDeliveryTimeOptions::getTotal())
			return EbayDeliveryTimeOptions::getAll();

		$ebay = new EbayRequest();
		$delivery_time_options = $ebay->getDeliveryTimeOptions();

		foreach ($delivery_time_options as $delivery_time_option)
			EbayDeliveryTimeOptions::insert(array_map('pSQL', $delivery_time_option));

		return $delivery_time_options;
	}

	private function _getCarriers()
	{
		if (EbayShippingService::getTotal())
			return EbayShippingService::getAll();

		$ebay = new EbayRequest();
		$carriers = $ebay->getCarriers();

		foreach ($carriers as $carrier)
			EbayShippingService::insert(array_map('pSQL', $carrier));

		return $carriers;
	}

	private function _getReturnsPolicies()
	{
		// already in the DB
		if (EbayReturnsPolicy::getTotal())
			return EbayReturnsPolicy::getAll();

		$ebay = new EbayRequest();
		$policiesDetails = $ebay->getReturnsPolicies();

		foreach ($policiesDetails['ReturnsAccepted'] as $returns_policy)
			EbayReturnsPolicy::insert(array_map('pSQL', $returns_policy));

		$ReturnsWithin = array();
		foreach($policiesDetails['ReturnsWithin'] as $returns_within)
			$ReturnsWithin[] = array_map('pSQL', $returns_within);
		$this->setConfiguration('EBAY_RETURNS_WITHIN_VALUES', serialize($ReturnsWithin));

		$returnsWhoPays = array();
		foreach($policiesDetails['ReturnsWhoPays'] as $returns_within)
			$returnsWhoPays[] = array_map('pSQL', $returns_within);
		$this->setConfiguration('EBAY_RETURNS_WHO_PAYS_VALUES', serialize($returnsWhoPays));

		return $policiesDetails['ReturnsAccepted'];
	}

	private function _relistItems()
	{
		if ($this->ebay_profile->getConfiguration('EBAY_LISTING_DURATION') != 'GTC'
			&& $this->ebay_profile->getConfiguration('EBAY_AUTOMATICALLY_RELIST') == 'on')
		{
			//We do relist automatically each day
			$this->ebay_profile->setConfiguration('EBAY_LAST_RELIST', date('Y-m-d'));

			$ebay = new EbayRequest();
			$days = substr($this->ebay_profile->getConfiguration('EBAY_LISTING_DURATION'), 5);

			foreach (EbayProduct::getProducts($days, 10) as $item)
			{
				$new_item_id = $ebay->relistFixedPriceItem($item['itemID']);

				if (!$new_item_id)
					$new_item_id = $item['id_product_ref'];

				//Update of the product so that we don't take it in the next 10 products to relist !
				EbayProduct::updateByIdProductRef($item['id_product_ref'], array(
					'id_product_ref' => pSQL($new_item_id),
					'date_upd' => date('Y-m-d h:i:s')));
			}
		}
	}

	/**
	 * Orders History Methods
	 *
	 **/
	private function _displayOrdersHistory()
	{
		// Check if the module is configured
		if (!$this->ebay_profile->getConfiguration('EBAY_PAYPAL_EMAIL'))
			return '<p><b>'.$this->l('Please configure the \'General settings\' tab before using this tab').'</b></p><br />';

		$dateLastImport = '-';

		if (file_exists(dirname(__FILE__).'/log/orders.php'))
			include(dirname(__FILE__).'/log/orders.php');

		$this->smarty->assign(array(
			'date_last_import' => $dateLastImport,
			'orders' => isset($orders) ? $orders : array()
		));

		return $this->display(dirname(__FILE__), '/views/templates/hook/ordersHistory.tpl');
	}

	/**
	 * Help Config Methods
	 *
	 **/
	private function _displayHelp()
	{
		$help_file = dirname(__FILE__).'/help/help-'.strtolower($this->ebay_country->getDocumentationLang()).'.html';

		if (!file_exists($help_file))
			$help_file = dirname(__FILE__).'/help/help-en.html';

		return Tools::file_get_contents($help_file);
	}

	private function _getAlerts()
	{
		$alerts = array();

		if (!Configuration::get('EBAY_API_TOKEN', null, 0, 0))
			$alerts[] = 'registration';

		if (!ini_get('allow_url_fopen'))
			$alerts[] = 'allowurlfopen';

		if (!extension_loaded('curl'))
			$alerts[] = 'curl';

		$ebay = new EbayRequest();
		$user_profile = $ebay->getUserProfile(Configuration::get('EBAY_API_USERNAME', null, 0, 0));

		$this->StoreName = $user_profile['StoreName'];

		if ($user_profile['SellerBusinessType'][0] != 'Commercial')
			$alerts[] = 'SellerBusinessType';

		return $alerts;
	}

	/*Get alert to see if some multi variation product on PrestaShop were added to a non multi sku categorie on ebay*/
	private function _getAlertCategories()
	{
		$alert = '';
		$cat_with_problem = array();

		$sql_get_cat_non_multi_sku = 'SELECT * FROM '._DB_PREFIX_.'ebay_category_configuration AS ecc
			INNER JOIN '._DB_PREFIX_.'ebay_category AS ec ON ecc.id_ebay_category = ec.id_ebay_category';

		foreach (Db::getInstance()->ExecuteS($sql_get_cat_non_multi_sku) as $cat)
		{
			if ($cat['is_multi_sku'] != 1 && EbayCategory::getInheritedIsMultiSku($cat['id_category_ref']) != 1)
			{
				$catProblem = 0;
				$category = new Category($cat['id_category']);
				$products = $category->getProductsWs($this->ebay_country->getIdLang(), 0, 300);

				foreach ($products as $product_ar)
				{
					$product = new Product($product_ar['id']);
					$combinations = version_compare(_PS_VERSION_, '1.5', '>') ? $product->getAttributeCombinations($this->context->cookie->id_lang) : $product->getAttributeCombinaisons($this->context->cookie->id_lang);

					if (count($combinations) > 0 && !$catProblem)
					{
						$cat_with_problem[] = $cat['name'];
						$catProblem = 1;
					}
				}
			}
		}

		$var = implode(', ', $cat_with_problem);

		if (count($cat_with_problem) > 0)
		{
			if (count($cat_with_problem == 1)) // RAPH: pb here in the test. Potential typo
				$alert = '<b>'.$this->l('You have chosen eBay category : ').$var.$this->l(' which does not support multivariation products. Each variation of a product will generate a new product in eBay').'</b>';
			else
				$alert = '<b>'.$this->l('You have chosen eBay categories : ').$var.$this->l(' which do not support multivariation products. Each variation of a product will generate a new product in eBay').'</b>';
		}

		return $alert;
	}

	public function setConfiguration($config_name, $config_value, $html = false)
	{
		return Configuration::updateValue($config_name, $config_value, $html, 0, 0);
	}

	private function _getContextShop()
	{
		switch ($context_type = Shop::getContext())
		{
			case Shop::CONTEXT_SHOP:
				$context_id = Shop::getContextShopID();
				break;
			case Shop::CONTEXT_GROUP:
				$context_id = Shop::getContextShopGroupID();
				break;
		}

		return array(
			$context_type,
			isset($context_id) ? $context_id : null
		);
	}

	private function _getUrl($extra_vars = array())
	{
		$url_vars = array(
			'configure' => Tools::safeOutput(Tools::getValue('configure')),
			'token' => Tools::safeOutput(Tools::getValue('token')),
			'tab_module' => Tools::safeOutput(Tools::getValue('tab_module')),
			'module_name' => Tools::safeOutput(Tools::getValue('module_name')),
		);

		return 'index.php?'.http_build_query(array_merge($url_vars, $extra_vars));
	}

	/**
	 * $newContextShop = array
	 * @param int $type Shop::CONTEXT_ALL | Shop::CONTEXT_GROUP | Shop::CONTEXT_SHOP
	 * @param int $id ID shop if CONTEXT_SHOP or id shop group if CONTEXT_GROUP
	 *
	 **/
	private function _setContextShop($new_context_shop = null)
	{
		if ($new_context_shop)
			Shop::setContext($new_context_shop[0], $new_context_shop[1]);
		else
			Shop::setContext(Shop::CONTEXT_SHOP, Configuration::get('PS_SHOP_DEFAULT'));
	}

	public function addSqlRestrictionOnLang($alias)
	{
		if (version_compare(_PS_VERSION_, '1.5', '>'))
			Shop::addSqlRestrictionOnLang($alias);
	}

	/**
	 * used by loadTableCategories
	 *
	 */
	public function getPath()
	{
		return $this->_path;
	}

	/**
	 * used by loadTableCategories & suggestCategories
	 *
	 */
	public function getContext()
	{
		return $this->context;
	}

	public function ajaxPreviewTemplate($content, $id_lang)
	{
		// work around for the tinyMCE bug deleting the css line
		$css_line = '<link rel="stylesheet" type="text/css" href="'.$this->_getModuleUrl().'views/css/ebay.css" />';
		$content = $css_line.$content;

		// random product
		$category = Category::getRootCategory($id_lang);
		$product = $category->getProducts($id_lang, 0, 1, null, null, false, true, true, 1, false);
		$product = $product[0];

		// data
		$data = array(
			'price' => $product['price'],
			'price_without_reduction' => '',
			'reduction' => $product['reduction'],
			'name' => $product['name'],
			'description' => $product['description'],
			'description_short' => $product['description_short']
			);
		if ($data['reduction'] > 0)
			$data['price_without_reduction'] = $product['price_without_reduction'];

		// pictures product
		$product = new Product($product['id_product'], false, $id_lang);
		$pictures = EbaySynchronizer::_getPictures($product, $id_lang, $this->context, array());
		$data['large_pictures'] = $pictures['large'];
		$data['medium_pictures'] = $pictures['medium'];

		// features product
		$features_html = '';
		foreach ($product->getFrontFeatures($id_lang) as $feature)
			$features_html .= '<b>'.$feature['name'].'</b> : '.$feature['value'].'<br/>';
		$data['features'] = $features_html;

		$content = EbaySynchronizer::fillAllTemplate($data, $content);

		echo $content;
	}

	private function _displayEbayListings()
	{
		$this->smarty->assign(array(
			'id_employee' => $this->context->employee->id,
			'ebay_listings' => EbaySynchronizer::getNbSynchronizableProducts($this->ebay_profile)
			));
		return $this->display(__FILE__, 'views/templates/hook/ebay_listings.tpl');
	}

	public function displayEbayListingsAjax($id_employee = null)
	{
		$ebay = new EbayRequest();
		$employee = new Employee($id_employee);
		$this->context->employee = $employee;
		$link = $this->context->link;
		$id_lang = $this->context->language->id;
		$products_ebay_listings = array();
		$products = EbayProduct::getProductsWithoutBlacklisted($id_lang);
		$data = array(
			'id_lang' => $id_lang,
			'titleTemplate' => $this->ebay_profile->getConfiguration('EBAY_PRODUCT_TEMPLATE_TITLE')
			);

		foreach ($products as $p)
		{
			$data['real_id_product'] = (int)$p['id_product'];
			$data['name'] = $p['name'];
			$data['manufacturer_name'] = $p['manufacturer_name'];
			$data['reference'] = $p['reference'];
			$data['ean13'] = $p['ean13'];
			$reference_ebay = $p['id_product_ref'];
			$product = new Product((int)$p['id_product'], true, $id_lang);
			if((int)$p['id_attribute'] > 0)
			{
				// No Multi Sku case so we do multiple products from a multivariation product
				$combinaison = $this->_getAttributeCombinationsById($product, (int)$p['id_attribute'], $id_lang);
				$combinaison = $combinaison[0];

				$data['reference'] = $combinaison['reference'];
				$data['ean13'] = $combinaison['ean13'];
				$variation_specifics = EbaySynchronizer::_getVariationSpecifics($combinaison['id_product'], $combinaison['id_product_attribute'], $id_lang);
				foreach ($variation_specifics as $variation_specific)
					$data['name'] .= ' '.$variation_specific;

				$products_ebay_listings[] = array(
					'id_product' => $combinaison['id_product'].'-'.$combinaison['id_product_attribute'],
					'quantity' => $combinaison['quantity'],
					'prestashop_title' => $data['name'],
					'ebay_title' => EbayRequest::prepareTitle($data),
					'reference_ebay' => $reference_ebay,
					'link' => method_exists($link, 'getAdminLink') ? $link->getAdminLink('AdminProducts').'&id_product='.(int)$combinaison['id_product'].'&updateproduct' : $link->getProductLink((int)$combinaison['id_product']),
					'link_ebay' => EbayProduct::getEbayUrl($reference_ebay, $ebay->getDev())
					);
			}
			else
			{
				$products_ebay_listings[] = array(
					'id_product' => $data['real_id_product'],
					'quantity' => $product->quantity,
					'prestashop_title' => $data['name'],
					'ebay_title' => EbayRequest::prepareTitle($data),
					'reference_ebay' => $reference_ebay,
					'link' => method_exists($link, 'getAdminLink') ? $link->getAdminLink('AdminProducts').'&id_product='.(int)$data['real_id_product'].'&updateproduct' : $link->getProductLink((int)$data['real_id_product']),
					'link_ebay' => EbayProduct::getEbayUrl($reference_ebay, $ebay->getDev())
					);
			}
		}

		$this->smarty->assign('products_ebay_listings', $products_ebay_listings);

		echo $this->display(__FILE__, 'views/templates/hook/ebay_listings_ajax.tpl');
	}

	public function _getAttributeCombinationsById($product, $id_attribute, $id_lang)
	{
		if(method_exists($product, 'getATtributeCombinationsById'))
			return $product->getAttributeCombinationsById((int)$id_attribute, $id_lang);

		$sql = 'SELECT pa.*, pa.`quantity`, ag.`id_attribute_group`, ag.`is_color_group`, agl.`name` AS group_name, al.`name` AS attribute_name,
					a.`id_attribute`, pa.`unit_price_impact`
				FROM `'._DB_PREFIX_.'product_attribute` pa
				LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`
				LEFT JOIN `'._DB_PREFIX_.'attribute` a ON a.`id_attribute` = pac.`id_attribute`
				LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
				LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = '.(int)$id_lang.')
				LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = '.(int)$id_lang.')
				WHERE pa.`id_product` = '.(int)$product->id.'
				AND pa.`id_product_attribute` = '.(int)$id_attribute.'
				GROUP BY pa.`id_product_attribute`, ag.`id_attribute_group`
				ORDER BY pa.`id_product_attribute`';

		return Db::getInstance()->ExecuteS($sql);

	}

	private function __postProcessDownloadLog()
	{
		$full_path = _PS_MODULE_DIR_.'ebay/log/request.txt';
		if (file_exists($full_path))
		{
			die;
			$file_name = basename($full_path);

			$date = gmdate(DATE_RFC1123);

			header('Pragma: public');
			header('Cache-Control: must-revalidate, pre-check=0, post-check=0, max-age=0');

			header('Content-Tranfer-Encoding: none');
			header('Content-Length: '.filesize($full_path));
			header('Content-MD5: '.base64_encode(md5_file($full_path)));
			header('Content-Type: application/octetstream; name="'.$file_name.'"');
			header('Content-Disposition: attachment; filename="'.$file_name.'"');

			header('Date: '.$date);
			header('Expires: '.gmdate(DATE_RFC1123, time()+1));
			header('Last-Modified: '.gmdate(DATE_RFC1123, filemtime($full_path)));

			readfile($full_path);
			exit;
		}
	}
}

