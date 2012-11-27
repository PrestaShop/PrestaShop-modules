<?php

/*
 * 2007-2011 PrestaShop
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
 *  @copyright  2007-2011 PrestaShop SA
 *  @version  Release: $Revision: 1.4 $
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

// Security
if (!defined('_PS_VERSION_'))
     exit;

// Loading eBay Class Request
if (file_exists(dirname(__FILE__) . '/eBayRequest.php'))
     require_once(dirname(__FILE__) . '/eBayRequest.php');


// Checking compatibility with older PrestaShop and fixing it
if (!defined('_MYSQL_ENGINE_'))
     define('_MYSQL_ENGINE_', 'MyISAM');

class Ebay extends Module {

     private $_html = '';
     private $_postErrors = array();
     private $_shippingMethod = array();
     private $_webserviceTestResult = '';
     private $_webserviceError = '';
     private $_fieldsList = array();
     private $_moduleName = 'ebay';
     private $id_lang;
     private $country;
     private $createShopUrl;

     /**
      * Construct Method
      *
      **/
     public function __construct()
     {
          $this->name = 'ebay';
          $this->tab = 'market_place';
          $this->version = '1.3.6';
          $this->author = 'PrestaShop';
          parent::__construct();

          /** Backward compatibility */
          require(_PS_MODULE_DIR_ . $this->name . '/backward_compatibility/backward.php');

          $this->displayName = $this->l('eBay');
          $this->description = $this->l('Easily export your products from PrestaShop to eBay, the biggest market place, to acquire new customers and realize more sales.');
          $this->module_key = '7a6b007a219bab59c1611254347f21d5';

          // Check the country and ask the bypass if not 'fr' nor 'it'
          if (!$this->checkCountry())
          {
               $this->warning = $this->l('eBay module currently works only for eBay.fr and eBay.it');
               return false;
          }

          if (strtolower($this->country->iso_code) == 'it')
               $this->createShopUrl = 'http://cgi3.ebay.it/ws/eBayISAPI.dll?CreateProductSubscription&&productId=3&guest=1';
          else 
               $this->createShopUrl = 'http://cgi3.ebay.fr/ws/eBayISAPI.dll?CreateProductSubscription&&productId=3&guest=1';

          $this->id_lang = Language::getIdByIso($this->country->iso_code);

          // Checking Extension
          if (!extension_loaded('curl') || !ini_get('allow_url_fopen')) 
          {
               if (!extension_loaded('curl') && !ini_get('allow_url_fopen'))
                    $this->warning = $this->l('You must enable cURL extension and allow_url_fopen option on your server if you want to use this module.');
               else if (!extension_loaded('curl'))
                    $this->warning = $this->l('You must enable cURL extension on your server if you want to use this module.');
               else if (!ini_get('allow_url_fopen'))
                    $this->warning = $this->l('You must enable allow_url_fopen option on your server if you want to use this module.');
               return false;
          }

          // Checking compatibility with older PrestaShop and fixing it
          if (!Configuration::get('PS_SHOP_DOMAIN'))
               $this->setConfiguration('PS_SHOP_DOMAIN', $_SERVER['HTTP_HOST']);

          // Generate eBay Security Token if not exists
          if (!Configuration::get('EBAY_SECURITY_TOKEN'))
               $this->setConfiguration('EBAY_SECURITY_TOKEN', Tools::passwdGen(30));

          // For 1.4.3 and less compatibility
          $updateConfig = array('PS_OS_CHEQUE' => 1, 'PS_OS_PAYMENT' => 2, 'PS_OS_PREPARATION' => 3, 'PS_OS_SHIPPING' => 4, 'PS_OS_DELIVERED' => 5, 'PS_OS_CANCELED' => 6,
              'PS_OS_REFUND' => 7, 'PS_OS_ERROR' => 8, 'PS_OS_OUTOFSTOCK' => 9, 'PS_OS_BANKWIRE' => 10, 'PS_OS_PAYPAL' => 11, 'PS_OS_WS_PAYMENT' => 12);
          foreach ($updateConfig as $u => $v)
               if (!Configuration::get($u) || (int) Configuration::get($u) < 1) 
               {
                    if (defined('_' . $u . '_') && (int) constant('_' . $u . '_') > 0)
                         $this->setConfiguration($u, constant('_' . $u . '_'));
                    else
                         $this->setConfiguration($u, $v);
               }

          // Check if installed
          if (self::isInstalled($this->name)) 
          {
               // Upgrade eBay module
               if (Configuration::get('EBAY_VERSION') != $this->version)
                    $this->upgrade();

               // Generate warnings
               if (!Configuration::get('EBAY_API_TOKEN'))
                    $this->warning = $this->l('You must register your module on eBay.');

               // Loading Shipping Method
               $this->loadShippingMethod();

               // Warning uninstall
               $this->confirmUninstall = $this->l('Are you sure you want uninstall this module ? All your configuration will be lost.');
          }
     }

     public function loadShippingMethod() 
     {
          if (strtolower($this->country->iso_code) == 'fr')
               $this->_shippingMethod = array(
                   7104 => array('description' => 'Colissimo', 'shippingService' => 'FR_ColiposteColissimo', 'shippingServiceID' => '7104'),
                   7112 => array('description' => 'Ecopli', 'shippingService' => 'FR_Ecopli', 'shippingServiceID' => '7112'),
                   57104 => array('description' => 'La Poste - Courrier International Prioritaire', 'shippingService' => 'FR_LaPosteInternationalPriorityCourier', 'shippingServiceID' => '57104'),
                   7101 => array('description' => 'Lettre', 'shippingService' => 'FR_PostOfficeLetter', 'shippingServiceID' => '7101'),
                   57105 => array('description' => 'La Poste - Courrier International Economique', 'shippingService' => 'FR_LaPosteInternationalEconomyCourier', 'shippingServiceID' => '57105'),
                   57106 => array('description' => 'La Poste - Colissimo International', 'shippingService' => 'FR_LaPosteColissimoInternational', 'shippingServiceID' => '57106'),
                   7102 => array('description' => 'Lettre avec suivi', 'shippingService' => 'FR_PostOfficeLetterFollowed', 'shippingServiceID' => '7102'),
                   57107 => array('description' => 'La Poste - Colis Economique International', 'shippingService' => 'FR_LaPosteColisEconomiqueInternational', 'shippingServiceID' => '57107'),
                   7103 => array('description' => 'Lettre recommand&eacute;e', 'shippingService' => 'FR_PostOfficeLetterRecommended', 'shippingServiceID' => '7103'),
                   7121 => array('description' => 'Lettre Max', 'shippingService' => 'FR_LaPosteLetterMax', 'shippingServiceID' => '7121'),
                   7113 => array('description' => 'Coli&eacute;co', 'shippingService' => 'FR_Colieco', 'shippingServiceID' => '7113'),
                   57108 => array('description' => 'La Poste - Colissimo Emballage International', 'shippingService' => 'FR_LaPosteColissimoEmballageInternational', 'shippingServiceID' => '57108'),
                   57114 => array('description' => 'Chronopost Express International', 'shippingService' => 'FR_ChronopostExpressInternational', 'shippingServiceID' => '57114'),
                   7106 => array('description' => 'Colissimo Recommand&eacute;', 'shippingService' => 'FR_ColiposteColissimoRecommended', 'shippingServiceID' => '7106'),
                   57109 => array('description' => 'Chronopost Classic International', 'shippingService' => 'FR_ChronopostClassicInternational', 'shippingServiceID' => '57109'),
                   57110 => array('description' => 'Chronopost Premium International', 'shippingService' => 'FR_ChronopostPremiumInternational', 'shippingServiceID' => '57110'),
                   7117 => array('description' => 'Chronopost - Chrono Relais', 'shippingService' => 'FR_ChronopostChronoRelais', 'shippingServiceID' => '7117'),
                   57111 => array('description' => 'UPS Standard', 'shippingService' => 'FR_UPSStandardInternational', 'shippingServiceID' => '57111'),
                   7111 => array('description' => 'Autre mode d\'envoi de courrier', 'shippingService' => 'FR_Autre', 'shippingServiceID' => '7111'),
                   57112 => array('description' => 'UPS Express', 'shippingService' => 'FR_UPSExpressInternational', 'shippingServiceID' => '57112'),
                   7114 => array('description' => 'Autre mode d\'envoi de colis', 'shippingService' => 'FR_AuteModeDenvoiDeColis', 'shippingServiceID' => '7114'),
                   57113 => array('description' => 'DHL', 'shippingService' => 'FR_DHLInternational', 'shippingServiceID' => '57113'),
                   57101 => array('description' => 'Frais de livraison internationale fixes', 'shippingService' => 'FR_StandardInternational', 'shippingServiceID' => '57101'),
                   7116 => array('description' => 'Chronopost', 'shippingService' => 'FR_Chronopost', 'shippingServiceID' => '7116'),
                   57102 => array('description' => 'Frais fixes pour livraison internationale express', 'shippingService' => 'FR_ExpeditedInternational', 'shippingServiceID' => '57102'),
                   57103 => array('description' => 'Autres livraisons internationales (voir description)', 'shippingService' => 'FR_OtherInternational', 'shippingServiceID' => '57103'),
                   7118 => array('description' => 'Chrono 10', 'shippingService' => 'FR_Chrono10', 'shippingServiceID' => '7118'),
                   7119 => array('description' => 'Chrono 13', 'shippingService' => 'FR_Chrono13', 'shippingServiceID' => '7119'),
                   7120 => array('description' => 'Chrono 18', 'shippingService' => 'FR_Chrono18', 'shippingServiceID' => '7120'),
                   7105 => array('description' => 'Coliposte - Colissimo Direct', 'shippingService' => 'FR_ColiposteColissimoDirect', 'shippingServiceID' => '7105'),
                   7107 => array('description' => 'Chronoposte - Chrono Classic International', 'shippingService' => 'FR_ChronoposteInternationalClassic', 'shippingServiceID' => '7107'),
                   7108 => array('description' => 'DHL - Express Europack', 'shippingService' => 'FR_DHLExpressEuropack', 'shippingServiceID' => '7108'),
                   7109 => array('description' => 'UPS - Standard', 'shippingService' => 'FR_UPSStandard', 'shippingServiceID' => '7109'),
               );
          else
               $this->_shippingMethod = array(
                   10101 => array('description' => 'Posta ordinaria', 'shippingService' => 'IT_RegularMail', 'shippingServiceID' => '10101'),
                   60101 => array('description' => 'Spedizione internazionale standard a prezzo fisso', 'shippingService' => 'IT_StandardInternational', 'shippingServiceID' => '60101'),
                   10102 => array('description' => 'Posta prioritaria', 'shippingService' => 'IT_PriorityMail', 'shippingServiceID' => '10102'),
                   60102 => array('description' => 'Spedizione internazionale celere a prezzo fisso', 'shippingService' => 'IT_ExpeditedInternational', 'shippingServiceID' => '60102'),
                   60103 => array('description' => 'Altre spedizioni internazionali (vedi descrizione)', 'shippingService' => 'IT_OtherInternational', 'shippingServiceID' => '60103'),
                   10103 => array('description' => 'Posta raccomandata', 'shippingService' => 'IT_MailRegisteredLetter', 'shippingServiceID' => '10103'),
                   10104 => array('description' => 'Posta raccomandata con contrassegno', 'shippingService' => 'IT_MailRegisteredLetterWithMark', 'shippingServiceID' => '10104'),
                   10105 => array('description' => 'Posta assicurata', 'shippingService' => 'IT_InsuredMail', 'shippingServiceID' => '10105'),
                   10106 => array('description' => 'Posta celere', 'shippingService' => 'IT_QuickMail', 'shippingServiceID' => '10106'),
                   10107 => array('description' => 'Pacco ordinario', 'shippingService' => 'IT_RegularPackage', 'shippingServiceID' => '10107'),
                   10108 => array('description' => 'Pacco celere 1', 'shippingService' => 'IT_QuickPackage1', 'shippingServiceID' => '10108'),
                   10109 => array('description' => 'Pacco celere 3', 'shippingService' => 'IT_QuickPackage3', 'shippingServiceID' => '10109'),
                   10111 => array('description' => 'Paccocelere Maxi', 'shippingService' => 'IT_ExpressPackageMaxi', 'shippingServiceID' => '10111'),
                   10110 => array('description' => 'Corriere espresso', 'shippingService' => 'IT_ExpressCourier', 'shippingServiceID' => '10110'),
                   10151 => array('description' => 'Ritiro in zona', 'shippingService' => 'IT_Pickup', 'shippingServiceID' => '10151'),
                   10112 => array('description' => 'Spedizione economica dall’estero', 'shippingService' => 'IT_EconomyDeliveryFromAbroad', 'shippingServiceID' => '10112'),
                   10113 => array('description' => 'Spedizione standard dall’estero', 'shippingService' => 'IT_StandardDeliveryFromAbroad', 'shippingServiceID' => '10113'),
                   10114 => array('description' => 'Spedizione espressa dall’estero', 'shippingService' => 'IT_ExpressDeliveryFromAbroad', 'shippingServiceID' => '10114'),
               );
     }

     /**
      * Install / Uninstall Methods
      *
      **/
     public function install() 
     {
          global $cookie;
          // Install SQL
          include(dirname(__FILE__) . '/sql-install.php');
          foreach ($sql as $s)
               if (!Db::getInstance()->execute($s))
                    return false;

          // Install Module
          if (!parent::install() ||
                  !$this->registerHook('addproduct') ||
                  !$this->registerHook('updateproduct') ||
                  !$this->registerHook('updateProductAttribute') ||
                  !$this->registerHook('deleteproduct') ||
                  !$this->registerHook('newOrder') ||
                  !$this->registerHook('backOfficeTop') ||
                  !$this->registerHook('backOfficeFooter') ||
                  !$this->registerHook('header') ||
                  !$this->registerHook('updateOrderStatus'))
               return false;

          // Generate Product Template
          $content = file_get_contents(dirname(__FILE__) . '/template/ebay.tpl');
          if($this->isVersionOneDotFive())
            $content = str_replace('{SHOP_LOGO}', 
              'http://' . Configuration::get('PS_SHOP_DOMAIN') . '/' . __PS_BASE_URI__ . '/'._PS_IMG_.Configuration::get('PS_LOGO').'?'.Configuration::get('PS_IMG_UPDATE_TIME'),
               $content);
          else
            $content = str_replace('{SHOP_LOGO}', 
              'http://' . Configuration::get('PS_SHOP_DOMAIN') . '/' . __PS_BASE_URI__ . '/logo.jpg',
               $content);
          $content = str_replace('{SHOP_NAME}', Configuration::get('PS_SHOP_NAME'), $content);
          $content = str_replace('{SHOP_URL}', 'http://' . Configuration::get('PS_SHOP_DOMAIN') . '/' . __PS_BASE_URI__ . '/', $content);
          $content = str_replace('{MODULE_URL}', 'http://' . Configuration::get('PS_SHOP_DOMAIN') . '/' . __PS_BASE_URI__ . '/modules/ebay/', $content);
          $content = str_replace('See our ratings', $this->l('See our ratings'), $content);
          $content = str_replace('Add this shop to my favorites', $this->l('Add this shop to my favorites'), $content);
          $content = str_replace('Availability', $this->l('Availability'), $content);
          $content = str_replace('in stock', $this->l('in stock'), $content);
          $this->setConfiguration('EBAY_PRODUCT_TEMPLATE', '');
          $this->setConfiguration('EBAY_PRODUCT_TEMPLATE', $content, true);
          $this->setConfiguration('EBAY_ORDER_LAST_UPDATE', date('Y-m-d') . 'T' . date('H:i:s') . '.000Z');
          $this->setConfiguration('EBAY_INSTALL_DATE', date('Y-m-d') . 'T' . date('H:i:s') . '.000Z');


          // Init
          $this->setConfiguration('EBAY_VERSION', $this->version);

          return true;
     }

     public function uninstall() 
     {
          // Uninstall Config
          foreach ($this->_fieldsList as $keyConfiguration => $name)
               if (!Configuration::deleteByName($keyConfiguration))
                    return false;

          // Uninstall SQL
          include(dirname(__FILE__) . '/sql-uninstall.php');
          foreach ($sql as $s)
               if (!Db::getInstance()->execute($s))
                    return false;
          Configuration::deleteByName('EBAY_API_SESSION');
          Configuration::deleteByName('EBAY_API_USERNAME');
          Configuration::deleteByName('EBAY_API_TOKEN');
          Configuration::deleteByName('EBAY_IDENTIFIER');
          Configuration::deleteByName('EBAY_SHOP');
          Configuration::deleteByName('EBAY_PAYPAL_EMAIL');
          Configuration::deleteByName('EBAY_SHIPPING_CARRIER_ID');
          Configuration::deleteByName('EBAY_SHIPPING_COST');
          Configuration::deleteByName('EBAY_SHIPPING_COST_CURRENCY');
          Configuration::deleteByName('EBAY_SHOP_POSTALCODE');
          Configuration::deleteByName('EBAY_CATEGORY_LOADED');
          Configuration::deleteByName('EBAY_CATEGORY_LOADED_DATE');
          Configuration::deleteByName('EBAY_PRODUCT_TEMPLATE');
          Configuration::deleteByName('EBAY_SYNC_MODE');
          Configuration::deleteByName('EBAY_ORDER_LAST_UPDATE');
          Configuration::deleteByName('EBAY_VERSION');
          Configuration::deleteByName('EBAY_SECURITY_TOKEN');
          Configuration::deleteByName('EBAY_INSTALL_DATE');
          Configuration::deleteByName('EBAY_COUNTRY_DEFAULT');
          // Uninstall Module
          if (!parent::uninstall() ||
                  !$this->unregisterHook('addproduct') ||
                  !$this->unregisterHook('updateproduct') ||
                  !$this->unregisterHook('updateProductAttribute') ||
                  !$this->unregisterHook('deleteproduct') ||
                  !$this->unregisterHook('newOrder') ||
                  !$this->unregisterHook('backOfficeTop') ||
                  //!$this->unregisterHook('backOfficeFooter') ||
                  !$this->unregisterHook('header') ||
                  !$this->unregisterHook('updateOrderStatus'))
               return false;

          // Clean Cookie
          $this->context->cookie->eBaySession = '';
          $this->context->cookie->eBayUsername = '';

          return true;
     }

     public function upgrade()
     {
          $version = Configuration::get('EBAY_VERSION');
          if ($version == '1.1' || empty($version))
          {
               // Upgrade SQL
               include(dirname(__FILE__) . '/sql-upgrade-1-2.php');
               foreach ($sql as $s)
                    if (!Db::getInstance()->execute($s))
                         return false;
               $this->setConfiguration('EBAY_VERSION', $this->version);
          }
     }

     /**
	  * Hook Methods
	  *
      **/
     public function hookNewOrder($params)
     {
          if ((int) $params['cart']->id < 1)
               return false;

          $sql = '`id_product` IN (SELECT `id_product` FROM `' . _DB_PREFIX_ . 'cart_product` WHERE `id_cart` = ' . (int) $params['cart']->id . ')';
          if (Configuration::get('EBAY_SYNC_MODE') == 'A') 
          {
               // Retrieve product list for eBay (which have matched categories) AND Send each product on eBay
               $productsList = Db::getInstance()->executeS('SELECT `id_product` FROM `' . _DB_PREFIX_ . 'product` WHERE ' . $sql . ' AND `active` = 1 AND `id_category_default` IN (SELECT `id_category` FROM `' . _DB_PREFIX_ . 'ebay_category_configuration` WHERE `id_category` > 0 AND `id_ebay_category` > 0)');
               foreach ($productsList as $k => $v)
                    $productList[$k]['noPriceUpdate'] = 1;
               //if ($productsList)
               //	$this->_syncProducts($productsList);
          }
          else if (Configuration::get('EBAY_SYNC_MODE') == 'B') 
          {
               // Select the sync Categories and Retrieve product list for eBay (which have matched and sync categories) AND Send each product on eBay
               $productsList = Db::getInstance()->executeS('SELECT `id_product` FROM `' . _DB_PREFIX_ . 'product` WHERE ' . $sql . ' AND `active` = 1 AND `id_category_default` IN (SELECT `id_category` FROM `' . _DB_PREFIX_ . 'ebay_category_configuration` WHERE `id_category` > 0 AND `id_ebay_category` > 0 AND `sync` = 1)');
               foreach ($productsList as $k => $v)
                    $productList[$k]['noPriceUpdate'] = 1;
               if ($productsList)
                    $this->_syncProducts($productsList);
          }
     }

     public function hookAddProduct($params) 
     {

          if (!isset($params['product']->id))
               return false;
          $id_product = $params['product']->id;
          if ((int) $id_product < 1)
               return false;

          if (Configuration::get('EBAY_SYNC_MODE') == 'A') 
          {
               // Retrieve product list for eBay (which have matched categories) AND Send each product on eBay
               $productsList = Db::getInstance()->executeS('SELECT `id_product` FROM `' . _DB_PREFIX_ . 'product` WHERE `id_product` = ' . (int) $id_product . ' AND `active` = 1 AND `id_category_default` IN (SELECT `id_category` FROM `' . _DB_PREFIX_ . 'ebay_category_configuration` WHERE `id_category` > 0 AND `id_ebay_category` > 0)');

               if ($productsList)
                    $this->_syncProducts($productsList);
          }
          else if (Configuration::get('EBAY_SYNC_MODE') == 'B') 
          {
               // Select the sync Categories and Retrieve product list for eBay (which have matched and sync categories) AND Send each product on e
               $productsList = Db::getInstance()->executeS('SELECT `id_product` FROM `' . _DB_PREFIX_ . 'product` WHERE `id_product` = ' . (int) $id_product . ' AND `active` = 1 AND `id_category_default` IN (SELECT `id_category` FROM `' . _DB_PREFIX_ . 'ebay_category_configuration` WHERE `id_category` > 0 AND `id_ebay_category` > 0 AND `sync` = 1)');
               if ($productsList)
                    $this->_syncProducts($productsList);
          }
     }

     public function hookheader($params) 
     {
          //Change context Shop to be default
          if ($this->isVersionOneDotFive() && Shop::isFeatureActive()) 
          {
               $oldContextShop = $this->getContextShop();
               $this->setContextShop();
          }
          //End of change 
          // Check if the module is configured
          if (!Configuration::get('EBAY_PAYPAL_EMAIL'))
               return false;

          // Fix hook update product attribute
          $this->hookupdateProductAttributeEbay();

          // init date to check from
          if (Configuration::get('EBAY_INSTALL_DATE') < date('Y-m-d', strtotime('-30 days')) . 'T' . date('H:i:s', strtotime('-30 days')))
          {
               //If it is more than 30 days that we installed the module			
               $dateToCheckFrom = Configuration::get('EBAY_ORDER_LAST_UPDATE');
               $dateToCheckFromArray = explode('T', $dateToCheckFrom);

               $dateToCheckFrom = date("Y-m-d", strtotime($dateToCheckFromArray[0] . " -30 day"));
               $dateToCheckFrom .= 'T' . $dateToCheckFromArray[1];
          } 
          else 
          {
               //If it is less than 30 days that we installed the module
               $dateToCheckFrom = Configuration::get('EBAY_INSTALL_DATE');
               $dateToCheckFromArray = explode('T', $dateToCheckFrom);

               $dateToCheckFrom = date("Y-m-d", strtotime($dateToCheckFromArray[0] . " -1 day"));
               $dateToCheckFrom .= 'T' . $dateToCheckFromArray[1];
          }

          if (Configuration::get('EBAY_ORDER_LAST_UPDATE') < date('Y-m-d', strtotime('-30 minutes')) . 'T' . date('H:i:s', strtotime('-30 minutes')) . '.000Z') 
          {

              $dateNew = date('Y-m-d') . 'T' . date('H:i:s') . '.000Z';
              $this->setConfiguration('EBAY_ORDER_LAST_UPDATE', $dateNew);
               // eBay Request
               $ebay = new eBayRequest();

               $page = 1;
               $orderList = array();
               $orderCount = 0;
               $orderCountTmp = 100;
               while ($orderCountTmp == 100 && $page < 10) 
               {
                    $orderListTmp = $ebay->getOrders($dateToCheckFrom, $dateNew, $page);
                    $orderCountTmp = count($orderListTmp);
                    $orderList = array_merge((array) $orderList, (array) $orderListTmp);
                    $orderCount += $orderCountTmp;
                    $page++;
               }

               // Lock




               if ($orderList) 
               {
                    foreach ($orderList as $korder => $order) 
                    {
                         if ($order['status'] == 'Complete' && $order['amount'] > 0.1 && isset($order['product_list']) && count($order['product_list'])) 
                         {
                              if (!Db::getInstance()->getValue('SELECT `id_ebay_order` FROM `' . _DB_PREFIX_ . 'ebay_order` WHERE `id_order_ref` = \'' . pSQL($order['id_order_ref']) . '\'')) 
                              {
                                   // Check for empty name
                                   $order['firstname'] = trim($order['firstname']);
                                   $order['familyname'] = trim($order['familyname']);
                                   if (empty($order['familyname']))
                                        $order['familyname'] = $order['firstname'];
                                   if (empty($order['firstname']))
                                        $order['firstname'] = $order['familyname'];
                                   if (empty($order['phone']) || !Validate::isPhoneNumber($order['phone']))
                                        $order['phone'] = '0100000000';

                                   if (Validate::isEmail($order['email']) && !empty($order['firstname']) && !empty($order['familyname'])) 
                                   {
                                        // Getting the customer
                                        $id_customer = (int) Db::getInstance()->getValue('SELECT `id_customer` FROM `' . _DB_PREFIX_ . 'customer` WHERE `active` = 1 AND `email` = \'' . pSQL($order['email']) . '\' AND `deleted` = 0' . (substr(_PS_VERSION_, 0, 3) == '1.3' ? '' : ' AND `is_guest` = 0'));

                                        // Add customer if he doesn't exist
                                        if ($id_customer < 1)
                                        {
                                             $customer = new Customer();
                                             $customer->id_gender = 0;
                                             $customer->id_default_group = 1;
                                             $customer->secure_key = md5(uniqid(rand(), true));
                                             $customer->email = $order['email'];
                                             $customer->passwd = md5(pSQL(_COOKIE_KEY_ . rand()));
                                             $customer->last_passwd_gen = pSQL(date('Y-m-d H:i:s'));
                                             $customer->newsletter = 0;
                                             $customer->lastname = pSQL($order['familyname']);
                                             $customer->firstname = pSQL($order['firstname']);
                                             $customer->active = 1;
                                             $customer->add();
                                             $id_customer = $customer->id;
                                        }

                                        // Search if address exists
                                        $id_address = (int) Db::getInstance()->getValue('SELECT `id_address` FROM `' . _DB_PREFIX_ . 'address` WHERE `id_customer` = ' . (int) $id_customer . ' AND `alias` = \'eBay\'');
                                        if ($id_address > 0)
                                             $address = new Address((int) $id_address);
                                        else 
                                        {
                                             $address = new Address();
                                             $address->id_customer = (int) $id_customer;
                                        }
                                        $address->id_country = (int) Country::getByIso($order['country_iso_code']);
                                        $address->alias = 'eBay';
                                        $address->lastname = pSQL($order['familyname']);
                                        $address->firstname = pSQL($order['firstname']);
                                        $address->address1 = pSQL($order['address1']);
                                        $address->address2 = pSQL($order['address2']);
                                        $address->postcode = pSQL($order['postalcode']);
                                        $address->city = pSQL($order['city']);
                                        $address->phone = pSQL($order['phone']);
                                        $address->active = 1;
                                        if ($id_address > 0 && Validate::isLoadedObject($address))
                                             $address->update();
                                        else
                                             $address->add();
                                        $id_address = $address->id;

                                        $flag = 1;
                                        foreach ($order['product_list'] as $product)
                                        {
                                             if ((int) $product['id_product'] < 1 || !Db::getInstance()->getValue('SELECT `id_product` FROM `' . _DB_PREFIX_ . 'product` WHERE `id_product` = ' . (int) $product['id_product']))
                                                  $flag = 0;
                                             if (isset($product['id_product_attribute']) && $product['id_product_attribute'] > 0 && !Db::getInstance()->getValue('SELECT `id_product_attribute` FROM `' . _DB_PREFIX_ . 'product_attribute` WHERE `id_product` = ' . (int) $product['id_product'] . ' AND `id_product_attribute` = ' . (int) $product['id_product_attribute']))
                                                  $flag = 0;
                                        }

                                        if ($flag == 1) 
                                        {
                                             //Create a Cart for the order
                                             $cartNbProducts = 0;
                                             $cartAdd = new Cart();
                                             Context::getContext()->customer = new Customer($id_customer);
                                             $cartAdd->id_customer = $id_customer;
                                             $cartAdd->id_address_invoice = $id_address;
                                             $cartAdd->id_address_delivery = $id_address;
                                             $cartAdd->id_carrier = 0;
                                             $cartAdd->id_lang = $this->id_lang;
                                             $cartAdd->id_currency = Currency::getIdByIsoCode('EUR');
                                             $cartAdd->recyclable = 0;
                                             $cartAdd->gift = 0;
                                             $cartAdd->add();

                                             $id_lang = (int) Configuration::get('PS_LANG_DEFAULT');

                                             foreach ($order['product_list'] as $product)
                                             {
                                                  $prod = new Product($product['id_product'], false, $id_lang);
                                                  // Qty of product or attribute
                                                  if (isset($product['id_product_attribute']) && !empty($product['id_product_attribute']))
                                                       $minimalQty = (int) Attribute::getAttributeMinimalQty($product['id_product_attribute']);
                                                  else
                                                       $minimalQty = $prod->minimal_quantity;
                                                  
                                                  if ($product['quantity'] >= $minimalQty) 
                                                  {
                                                       if ($this->isVersionOneDotFive())
                                                       {
                                                            $update = $cartAdd->updateQty(
                                                                    (int) ($product['quantity']), (int) ($product['id_product']), ((isset($product['id_product_attribute']) && $product['id_product_attribute'] > 0) ? $product['id_product_attribute'] : NULL), false, 'up', 0, new Shop(Configuration::get('PS_SHOP_DEFAULT')));
                                                            if ($update === TRUE)
                                                                 $cartNbProducts++;


                                                       }
                                                       elseif ($cartAdd->updateQty((int) ($product['quantity']), (int) ($product['id_product']), ((isset($product['id_product_attribute']) && $product['id_product_attribute'] > 0) ? $product['id_product_attribute'] : NULL)))
                                                                 $cartNbProducts++;
                                                  }
                                                  else
                                                  {
                                                       $templateVars = array(
                                                           '{name_product}' => $prod->name,
                                                           '{min_qty}' => $minimalQty,
                                                           '{cart_qty}' => $product['quantity']
                                                       );
                                                       Mail::Send(
                                                               (int) Configuration::get('PS_LANG_DEFAULT'), 'alertEbay', Mail::l('Product quantity', $id_lang), $templateVars, strval(Configuration::get('PS_SHOP_EMAIL')), NULL, strval(Configuration::get('PS_SHOP_EMAIL')), strval(Configuration::get('PS_SHOP_NAME')), NULL, NULL, dirname(__FILE__) . '/mails/');
                                                  }

                                             }

                                             $cartAdd->update();

                                             // Check number of products in the cart and check if order has already been taken
                                             if ($cartNbProducts > 0 && !Db::getInstance()->getValue('SELECT `id_ebay_order` FROM `' . _DB_PREFIX_ . 'ebay_order` WHERE `id_order_ref` = \'' . pSQL($order['id_order_ref']) . '\'')) 
                                             {
                                                  // Fix on sending e-mail
                                                  Db::getInstance()->autoExecute(_DB_PREFIX_ . 'customer', array('email' => 'NOSEND-EBAY'), 'UPDATE', '`id_customer` = ' . (int) $id_customer);
                                                  $customerClear = new Customer();
                                                  if (method_exists($customerClear, 'clearCache'))
                                                       $customerClear->clearCache(true);
                                                  $paiement = new eBayPayment();
                                                  // Validate order
                                                  if ($this->isVersionOneDotFive()) 
                                                  {
                                                       $customer = new Customer($id_customer);
                                                       $paiement->validateOrder(intval($cartAdd->id), Configuration::get('PS_OS_PAYMENT'), floatval($cartAdd->getOrderTotal(true, 3)), 'eBay ' . $order['payment_method'] . ' ' . $order['id_order_seller'], NULL, array(), intval($cartAdd->id_currency), false, $customer->secure_key, new Shop(Configuration::get('PS_SHOP_DEFAULT')));
                                                  } 
                                                  else 
                                                  {
                                                       $customer = new Customer($id_customer);
                                                       $paiement->validateOrder(intval($cartAdd->id), Configuration::get('PS_OS_PAYMENT'), floatval($cartAdd->getOrderTotal(true, 3)), 'eBay ' . $order['payment_method'] . ' ' . $order['id_order_seller'], NULL, array(), intval($cartAdd->id_currency), false, $customer->secure_key
                                                       );
                                                  }
                                                  $id_order = $paiement->currentOrder;

                                                  // Fix on date
                                                  Db::getInstance()->autoExecute(_DB_PREFIX_ . 'orders', array('date_add' => pSQL($order['date_add'])), 'UPDATE', '`id_order` = ' . (int) $id_order);

                                                  // Fix on sending e-mail
                                                  Db::getInstance()->autoExecute(_DB_PREFIX_ . 'customer', array('email' => pSQL($order['email'])), 'UPDATE', '`id_customer` = ' . (int) $id_customer);

                                                  // Update price (because of possibility of price impact)
                                                  foreach ($order['product_list'] as $product) 
                                                  {
                                                       $tax_rate = Db::getInstance()->getValue('SELECT `tax_rate` FROM `' . _DB_PREFIX_ . 'order_detail` WHERE `id_order` = ' . (int) $id_order . ' AND `product_id` = ' . (int) $product['id_product'] . ' AND `product_attribute_id` = ' . (int) $product['id_product_attribute']);
                                                       Db::getInstance()->autoExecute(_DB_PREFIX_ . 'order_detail', array('product_price' => floatval($product['price'] / (1 + ($tax_rate / 100))), 'reduction_percent' => 0), 'UPDATE', '`id_order` = ' . (int) $id_order . ' AND `product_id` = ' . (int) $product['id_product'] . ' AND `product_attribute_id` = ' . (int) $product['id_product_attribute']);
                                                  }
                                                  $updateOrder = array(
                                                      'total_paid' => floatval($order['amount']),
                                                      'total_paid_real' => floatval($order['amount']),
                                                      'total_products' => floatval(Db::getInstance()->getValue('SELECT SUM(`product_price`) FROM `' . _DB_PREFIX_ . 'order_detail` WHERE `id_order` = ' . (int) $id_order)),
                                                      'total_products_wt' => floatval($order['amount'] - $order['shippingServiceCost']),
                                                      'total_shipping' => floatval($order['shippingServiceCost']),
                                                  );
                                                  Db::getInstance()->autoExecute(_DB_PREFIX_ . 'orders', $updateOrder, 'UPDATE', '`id_order` = ' . (int) $id_order);

                                                  // Register the ebay order ref
                                                  Db::getInstance()->autoExecute(_DB_PREFIX_ . 'ebay_order', array('id_order_ref' => pSQL($order['id_order_ref']), 'id_order' => (int) $id_order), 'INSERT');

                                                  if (!$this->isVersionOneDotFive()) 
                                                  {//Fix on eBay not updating
                                                       $params = array();
                                                       foreach ($order['product_list'] as $product) 
                                                       {
                                                            $params['product'] = new Product((int) ($product['id_product']));
                                                            $this->hookaddproduct($params);
                                                       }
                                                  }
                                             } 
                                             else 
                                             {
                                                  $cartAdd->delete();
                                                  $orderList[$korder]['errors'][] = $this->l('Could not add product to cart (maybe your stock quantity is 0)');
                                             }
                                        }
                                        else
                                             $orderList[$korder]['errors'][] = $this->l('Could not found products in database');
                                   }
                                   else
                                        $orderList[$korder]['errors'][] = $this->l('Invalid e-mail');
                              }
                              else
                                   $orderList[$korder]['errors'][] = $this->l('Order already imported');
                         }
                         else
                              $orderList[$korder]['errors'][] = $this->l('Status not complete or amount less than 0.1 or no product matching');
                    }
                    file_put_contents(dirname(__FILE__) . '/log/orders.php', "<?php\n\n" . '$dateLastImport = ' . "'" . date('d/m/Y H:i:s') . "';\n\n" . '$orderList = ' . var_export($orderList, true) . ";\n\n");
               }
          }
          // Set old Context Shop
          if ($this->isVersionOneDotFive() && Shop::isFeatureActive())
               $this->setContextShop($oldContextShop);
     }

     // Alias
     public function hookupdateproduct($params) 
     {
          $this->hookaddproduct($params);
     }

     public function hookupdateProductAttributeEbay() 
     {
          if (isset($_POST['submitProductAttribute']) && isset($_POST['id_product_attribute'])) 
          {
               $params = array();
               $params['id_product_attribute'] = (int) $_POST['id_product_attribute'];
               if ($params['id_product_attribute'] > 0) 
               {
                    $id_product = Db::getInstance()->getValue('SELECT `id_product` FROM `' . _DB_PREFIX_ . 'product_attribute` WHERE `id_product_attribute` = ' . (int) $params['id_product_attribute']);
                    $params['product'] = new Product($id_product);
                    $this->hookaddproduct($params);
               }
          }
     }

     public function hookdeleteproduct($params) 
     {
          $this->hookaddproduct($params);
     }

     public function hookbackOfficeTop($params) 
     {
          if (!((version_compare(_PS_VERSION_, '1.5.1', '>=') && version_compare(_PS_VERSION_, '1.5.2', '<')) && !Shop::isFeatureActive()))
               $this->hookheader($params);
     }


     /**
	  * Main Form Methods
      *
      **/
     public function getContent() 
     {
          //Change context Shop to be default
          $oldContextShop = $this->getContextShop();
          $this->setContextShop();
          //End of change 
          // Checking Country
          if (Tools::getValue('ebay_country_default_fr') == 'ok')
               $this->setConfiguration('EBAY_COUNTRY_DEFAULT', 8);

          if (!$this->checkCountry())
               return $this->_html . '<center>' . $this->displayError($this->l('eBay module currently works only for eBay.fr and eBay.it') . '.<br /><a href="' . Tools::safeOutput($_SERVER['REQUEST_URI']) . '&ebay_country_default_fr=ok">' . $this->l('Continue anyway ?') . '</a>') . '</center>';

          // Checking Extension
          if (!extension_loaded('curl') || !ini_get('allow_url_fopen')) 
          {
               if (!extension_loaded('curl') && !ini_get('allow_url_fopen'))
                    return $this->_html . $this->displayError($this->l('You must enable cURL extension and allow_url_fopen option on your server if you want to use this module.'));
               else if (!extension_loaded('curl'))
                    return $this->_html . $this->displayError($this->l('You must enable cURL extension on your server if you want to use this module.'));
               else if (!ini_get('allow_url_fopen'))
                    return $this->_html . $this->displayError($this->l('You must enable allow_url_fopen option on your server if you want to use this module.'));
          }

          // If isset Post Var, post process else display form
          if (!empty($_POST) && (Tools::isSubmit('submitSave') || Tools::isSubmit('submitSave1') || Tools::isSubmit('submitSave2'))) 
          {
               $this->_postValidation();
               if (!sizeof($this->_postErrors))
                    $this->_postProcess();
               else
                    foreach ($this->_postErrors AS $err)
                         $this->_html .= '<div class="alert error"><img src="../modules/ebay/forbbiden.gif" alt="nok" />&nbsp;' . $err . '</div>';
          }
          $this->_displayForm();
          // Set old Context Shop
          $this->setContextShop($oldContextShop);

          return $this->_html;
     }

     private function _displayForm() 
     {
          $alert = $this->getAlert();

          // Displaying Information from Prestashop
          $stream_context = @stream_context_create(array('http' => array('method' => "GET", 'timeout' => 2)));
          $prestashopContent = @file_get_contents('http://api.prestashop.com/partner/modules/ebay.php?version=' . $this->version . '&shop=' . urlencode(Configuration::get('PS_SHOP_NAME')) . '&registered=' . ($alert['registration'] == 1 ? 'no' : 'yes') . '&url=' . urlencode($_SERVER['HTTP_HOST']) . '&iso_country=' . Tools::strtolower($this->country->iso_code) . '&iso_lang=' . Tools::strtolower($this->context->language->iso_code) . '&id_lang=' . (int) $this->context->language->id . '&email=' . urlencode(Configuration::get('PS_SHOP_EMAIL')) . '&security=' . md5(Configuration::get('PS_SHOP_EMAIL') . _COOKIE_IV_), false, $stream_context);
          if (!Validate::isCleanHtml($prestashopContent))
               $prestashopContent = '';

          if (strtolower($this->country->iso_code) == 'it')
               $imgStats = 'ebay_stats-it.png';
          else
               $imgStats = 'ebay_stats.png';

          $this->_html .= '
		<fieldset>
			<center><img src="' . $this->_path . $imgStats . '" alt="eBay stats"/></center>
			<br />
			<u><a href="' . $this->l('http://pages.ebay.fr/professionnels/index.html') . '" target="_blank">' . $this->l('Be informed in professional section on eBay.fr') . '</a></u>
		</fieldset>
		<br />';

          // Displaying page
          $this->_html .= '<fieldset>
		<legend><img src="' . $this->_path . 'logo.gif" alt="" /> ' . $this->l('eBay Module Status') . '</legend>';
          $this->_html .= '<div style="float: left; width: 45%">';
          if (!count($alert)) 
          {
               $this->_html .= '<img src="../modules/ebay/valid.png" /><strong>' . $this->l('eBay Module is configured and online!') . '</strong>';
               if ($this->isVersionOneDotFive()) 
               {
                    if ((version_compare(_PS_VERSION_, '1.5.1', '>=') && version_compare(_PS_VERSION_, '1.5.2', '<')) && !Shop::isFeatureActive()) 
                    {
                         $this->_html .= '<br/><img src="../modules/ebay/warn.png" /><strong>' . $this->l('You\'re using version 1.5.1 of PrestaShop. We invite you to upgrade to version 1.5.2  so you can use the eBay module properly.') . '</strong>';
                         $this->_html .= '<br/><strong>' . $this->l('To synchronize your sell coming from eBay you need to go to the front office.') . '</strong>';
                    } 
                    else if (Shop::isFeatureActive())
                         $this->_html .= '<br/><strong>' . $this->l('The eBay Module is not configurable for multishop yet. Stocks and categories used are the ones of the default shop') . '</strong>';
               }
          } 
          else 
          {
               $this->_html .= '<img src="../modules/ebay/warn.png" /><strong>' . $this->l('eBay Module is not configured yet, you must:') . '</strong>';
               $this->_html .= '<br />' . (isset($alert['registration']) ? '<img src="../modules/ebay/warn.png" />' : '<img src="../modules/ebay/valid.png" />') . ' 1) ' . $this->l('Register the module on eBay');
               $this->_html .= '<br />' . (isset($alert['allowurlfopen']) ? '<img src="../modules/ebay/warn.png" />' : '<img src="../modules/ebay/valid.png" />') . ' 2) ' . $this->l('Allow url fopen');
               $this->_html .= '<br />' . (isset($alert['curl']) ? '<img src="../modules/ebay/warn.png" />' : '<img src="../modules/ebay/valid.png" />') . ' 3) ' . $this->l('Enable cURL');
               $this->_html .= '<br />' . (isset($alert['SellerBusinessType']) ? '<img src="../modules/ebay/warn.png" />' : '<img src="../modules/ebay/valid.png" />') . ' 4) ' . $this->l('Your account type must be Business type');
               $this->_html .= '<br />' . (isset($alert['StoreOwner']) ? '<img src="../modules/ebay/warn.png" />' : '<img src="../modules/ebay/valid.png" />') . ' 5) ' . $this->l('You must have an eBay Store to use the module');
          }

          $this->_html .= '</div><div style="float: right; width: 45%">' . $prestashopContent . '<br>' . $this->l('Connection to eBay.') . strtolower($this->country->iso_code) . '</div>';

          $this->_html .= '</fieldset><div class="clear">&nbsp;</div>';

          if (!Configuration::get('EBAY_API_TOKEN'))
               $this->_html .= $this->_displayFormRegister();
          else
               $this->_html .= $this->_displayFormConfig();
     }

     private function _postValidation()
     {
          if (!Configuration::get('EBAY_API_TOKEN'))
               $this->_postValidationRegister();
          else if (Tools::getValue('section') == 'parameters')
               $this->_postValidationParameters();
          else if (Tools::getValue('section') == 'category')
               $this->_postValidationCategory();
          else if (Tools::getValue('section') == 'template')
               $this->_postValidationTemplateManager();
          else if (Tools::getValue('section') == 'sync')
               $this->_postValidationEbaySync();
     }

     private function _postProcess() {
          if (!Configuration::get('EBAY_API_TOKEN'))
               $this->_postProcessRegister();
          else if (Tools::getValue('section') == 'parameters')
               $this->_postProcessParameters();
          else if (Tools::getValue('section') == 'category')
               $this->_postProcessCategory();
          else if (Tools::getValue('section') == 'template')
               $this->_postProcessTemplateManager();
          else if (Tools::getValue('section') == 'sync')
               $this->_postProcessEbaySync();
     }
     
     public function _postValidationRegister()
     {
	     
     }

     /**
      * Register Form Config Methods
      *
      **/
     private function _displayFormRegister() 
     {
          $ebay = new eBayRequest();

          if (!empty($this->context->cookie->eBaySession) && isset($_GET['action']) && $_GET['action'] == 'logged') 
          {
               if (isset($_POST['eBayUsername'])) 
               {
                    $this->context->cookie->eBayUsername = $_POST['eBayUsername'];
                    $this->setConfiguration('EBAY_API_USERNAME', $_POST['eBayUsername']);
                    $this->setConfiguration('EBAY_IDENTIFIER', $_POST['eBayUsername']);
               }
               $ebay->session = $this->context->cookie->eBaySession;
               $ebay->username = $this->context->cookie->eBayUsername;

               $html = '
            			<script>
            				function checkToken()
            				{
            					$.ajax({
            					  url: \'' . _MODULE_DIR_ . 'ebay/ajax/checkToken.php?token=' . Configuration::get('EBAY_SECURITY_TOKEN') . '&time=' . pSQL(date('Ymdhis')) . '\',
            					  success: function(data)
            					  {
            						if (data == \'OK\')
            							window.location.href = \'index.php?' . (($this->isVersionOneDotFive()) ? 'controller=' . Tools::safeOutput($_GET['controller'])  : 'tab=' . Tools::safeOutput($_GET['tab'])) . '&configure=' . Tools::safeOutput($_GET['configure']) . '&token=' . Tools::safeOutput($_GET['token']) . '&tab_module=' . Tools::safeOutput($_GET['tab_module']) . '&module_name=' . Tools::safeOutput($_GET['module_name']) . '&action=validateToken\';
            						else
            							setTimeout ("checkToken()", 5000);
            					  }
            					});
            				}
            				checkToken();
            			</script>';
               $html .= '<fieldset><legend><img src="' . $this->_path . 'logo.gif" alt="" title="" />' . $this->l('Register the module on eBay') . '</legend>';
               $html .= '<p align="center"><img src="' . $this->_path . 'loading.gif" alt="' . $this->l('Loading') . '" title="' . $this->l('Loading') . '" /></p>';
               $html .= '<p align="center">' . $this->l('Once you sign in from the new eBay window, the module will automatically finish the installation a few seconds later') . '</p>';
               $html .= '</fieldset>';
          } 
          else 
          {
               if (empty($this->context->cookie->eBaySession)) 
               {
                    $ebay->login();
                    $this->context->cookie->eBaySession = $ebay->session;
                    $this->setConfiguration('EBAY_API_SESSION', $ebay->session);
               }

               $html = '
            			<style>
            				.ebay_dl {margin: 0 0 10px 40px}
            				.ebay_dl > * {float: left; margin: 10px 0 0 10px}
            				.ebay_dl > dt {min-width: 100px; display: block; clear: both; text-align: left}
            				#ebay_label {font-weight: normal; float: none}
            				#button_ebay{background-image:url(' . $this->_path . 'ebay.png);background-repeat:no-repeat;background-position:center 90px;width:385px;height:191px;cursor:pointer;padding-bottom:70px;font-weight:bold;font-size:22px}
            			</style>
            			<script>
            				$(document).ready(function() {
            					$(\'#button_ebay\').click(function() {

            						if ($(\'#eBayUsername\').val() == \'\')
            						{
            							alert("' . $this->l('You have to set your eBay User ID') . '");
            							return false;
            						}
            						else
            							window.open(\'' . $ebay->getLoginUrl() . '?SignIn&runame=' . $ebay->runame . '&SessID=' . $this->context->cookie->eBaySession . '\');
            					});
            				});
            			</script>
            			<form action="' . Tools::safeOutput($_SERVER['REQUEST_URI']) . '&action=logged" method="post">
            				<fieldset>
            					<legend><img src="' . $this->_path . 'logo.gif" alt="" title="" />' . $this->l('Register the module on eBay') . '</legend>
            					<strong>' . $this->l('You already have a professional eBay account ?') . '</strong>

            					<dl class="ebay_dl">
            						<dt><label for="eBayUsername" id="ebay_label">' . $this->l('eBay User ID') . '</label></dt>
            						<dd><input id="eBayUsername" type="text" name="eBayUsername" value="' . $this->context->cookie->eBayUsername . '" /></dd>
            						<dt>&nbsp;</dt>
            						<dd><input type="submit" id="button_ebay" class="button" value="' . $this->l('Register the module on eBay') . '" /></dd>
            					</dl>

            					<br class="clear" />
            					<br />

            					<strong>' . $this->l('You do not have a professional eBay account yet ?') . '</strong><br />

            					<dl class="ebay_dl">
            						<dt><u><a href="' . $this->l('https://scgi.ebay.com/ws/eBayISAPI.dll?RegisterEnterInfo') . '" target="_blank">' . $this->l('Subscribe as a professional seller on eBay') . '</a></u></dt>
            						<dd></dd>
            					</dl>
            					<br /><br />
            					<br /><u><a href="' . $this->l('http://pages.ebay.com/help/sell/businessfees.html') . '" target="_blank">' . $this->l('Consult eBay business fees') . '</a></u>
            					<br />' . $this->l('Consult our "Help" section for more information') . '
            				</fieldset>
            			</form>';
          }

          return $html;
     }

     /**
      * Parameters Form Config Methods
      *
      **/
     private function _displayFormConfig() 
     {
          $html = '
		<ul id="menuTab">
				<li id="menuTab1" class="menuTabButton selected">1. ' . $this->l('Parameters') . '</li>
				<li id="menuTab2" class="menuTabButton">2. ' . $this->l('Categories settings') . '</li>
				<li id="menuTab3" class="menuTabButton">3. ' . $this->l('Template manager') . '</li>
				<li id="menuTab4" class="menuTabButton">4. ' . $this->l('eBay Sync') . '</li>
				<li id="menuTab5" class="menuTabButton">5. ' . $this->l('Orders history') . '</li>
				<li id="menuTab6" class="menuTabButton">6. ' . $this->l('Help') . '</li>
			</ul>
			<div id="tabList">
				<div id="menuTab1Sheet" class="tabItem selected">' . $this->_displayFormParameters() . '</div>
				<div id="menuTab2Sheet" class="tabItem">' . $this->_displayFormCategory() . '</div>
				<div id="menuTab3Sheet" class="tabItem">' . $this->_displayFormTemplateManager() . '</div>
				<div id="menuTab4Sheet" class="tabItem">' . $this->_displayFormEbaySync() . '</div>
				<div id="menuTab5Sheet" class="tabItem">' . $this->_displayOrdersHistory() . '</div>
				<div id="menuTab6Sheet" class="tabItem">' . $this->_displayHelp() . '</div>
			</div>
			<br clear="left" />
			<br />
			<style>
				#menuTab { float: left; padding: 0; margin: 0; text-align: left; }
				#menuTab li { text-align: left; float: left; display: inline; padding: 5px; padding-right: 10px; background: #EFEFEF; font-weight: bold; cursor: pointer; border-left: 1px solid #EFEFEF; border-right: 1px solid #EFEFEF; border-top: 1px solid #EFEFEF; }
				#menuTab li.menuTabButton.selected { background: #FFF6D3; border-left: 1px solid #CCCCCC; border-right: 1px solid #CCCCCC; border-top: 1px solid #CCCCCC; }
				#tabList { clear: left; }
				.tabItem { display: none; }
				.tabItem.selected { display: block; background: #FFFFF0; border: 1px solid #CCCCCC; padding: 10px; padding-top: 20px; }
			</style>
			<script>
				$(".menuTabButton").click(function () {
				  $(".menuTabButton.selected").removeClass("selected");
				  $(this).addClass("selected");
				  $(".tabItem.selected").removeClass("selected");
				  $("#" + this.id + "Sheet").addClass("selected");
				});
			</script>
		';
          if (isset($_GET['id_tab']))
               $html .= '<script>
          				  $(".menuTabButton.selected").removeClass("selected");
          				  $("#menuTab' . Tools::safeOutput($_GET['id_tab']) . '").addClass("selected");
          				  $(".tabItem.selected").removeClass("selected");
          				  $("#menuTab' . Tools::safeOutput($_GET['id_tab']) . 'Sheet").addClass("selected");
          			</script>';
          return $html;
     }

     private function _displayFormParameters() 
     {
          // Loading config currency
          $configCurrency = new Currency((int) (Configuration::get('PS_CURRENCY_DEFAULT')));

          // Display Form
          $html = '<form action="index.php?' . (($this->isVersionOneDotFive()) ? 'controller=' . Tools::safeOutput($_GET['controller'])  : 'tab=' . Tools::safeOutput($_GET['tab'])) . '&configure=' . Tools::safeOutput($_GET['configure']) . '&token=' . Tools::safeOutput($_GET['token']) . '&tab_module=' . Tools::safeOutput($_GET['tab_module']) . '&module_name=' . Tools::safeOutput($_GET['module_name']) . '&id_tab=1&section=parameters" method="post" class="form" id="configForm1">
				<fieldset style="border: 0">
					<h4>' . $this->l('To export your products on eBay, you have to create a pro account on eBay (see Help) and configure your eBay-Prestashop module.') . '</h4>
					<label>' . $this->l('eBay Identifier') . ' : </label>
					<div class="margin-form">
						<input type="text" size="20" name="ebay_identifier" value="' . Tools::safeOutput(Tools::getValue('ebay_identifier', Configuration::get('EBAY_IDENTIFIER'))) . '" ' . ((Tools::getValue('ebay_identifier', Configuration::get('EBAY_IDENTIFIER')) != '') ? ' readonly="readonly"' : ')') . '/>
						<p>' . (Configuration::get('EBAY_IDENTIFIER') ? '<a href="http://shop.ebay.fr/' . Configuration::get('EBAY_IDENTIFIER') . '/m.html?_ipg=50&_sop=12&_rdc=1" target="_blank">' . $this->l('Your products on eBay') . '</a>' : $this->l('Your eBay identifier')) . '</p>
					</div>
					<label>' . $this->l('eBay shop') . ' : </label>
					<div class="margin-form">
						<input type="text" size="20" name="ebay_shop" value="' . Tools::safeOutput(Tools::getValue('ebay_shop', Configuration::get('EBAY_SHOP'))) . '" /> <b>' . $this->l('The use of eBay\'s module require to open an eBay store') . '</b>
						<p>' . (Configuration::get('EBAY_SHOP') ? '<a href="http://stores.ebay.fr/' . Configuration::get('EBAY_SHOP') . '" target="_blank">' . $this->l('Your shop on eBay') . '</a>' : '<a href="' . $this->createShopUrl . '" style="color:#7F7F7F;">' . $this->l('Open your shop')) . '</a></p>
					</div>
					<label>' . $this->l('Paypal Identifier (e-mail)') . ' : </label>
					<div class="margin-form">
						<input type="text" size="20" name="ebay_paypal_email" value="' . Tools::safeOutput(Tools::getValue('ebay_paypal_email', Configuration::get('EBAY_PAYPAL_EMAIL'))) . '" />
						<p>' . $this->l('You have to set your PayPal e-mail account, it\'s the only payment available with this module') . '</p>
					</div>
					<label>' . $this->l('Shipping method') . ' : </label>
					<div class="margin-form">
						<select name="ebay_shipping_carrier_id">';
          foreach ($this->_shippingMethod as $id => $val)
               $html .= '<option value="' . $id . '" ' . (Tools::getValue('ebay_shipping_carrier_id', Configuration::get('EBAY_SHIPPING_CARRIER_ID')) == $id ? 'selected="selected"' : '') . '>' . $val['description'] . '</option>';
          $html .= '</select>
						<p>' . $this->l('Shipping cost configuration for your products on eBay') . '</p>
					</div>
					<label>' . $this->l('Shipping cost') . ' : </label>
					<div class="margin-form">
						<input type="text" size="20" name="ebay_shipping_cost" value="' . Tools::safeOutput(Tools::getValue('ebay_shipping_cost', Configuration::get('EBAY_SHIPPING_COST'))) . '" /> ' . $configCurrency->sign . '
						<p>' . $this->l('Shipping cost configuration for your products on eBay') . '</p>
					</div>
					<label>' . $this->l('Shop postal code') . ' : </label>
					<div class="margin-form">
						<input type="text" size="20" name="ebay_shop_postalcode" value="' . Tools::safeOutput(Tools::getValue('ebay_shop_postalcode', Configuration::get('EBAY_SHOP_POSTALCODE'))) . '" />
						<p>' . $this->l('Your shop\'s postal code') . '</p>
					</div>
				</fieldset>
				<div class="margin-form" id="buttonEbayParameters"><input class="button" name="submitSave" type="submit" id="save_ebay_parameters" value="' . $this->l('Save') . '" /></div>
				<div class="margin-form" id="categoriesProgression" style="font-weight: bold;"></div>
			</form>';

          if (!Configuration::get('EBAY_CATEGORY_LOADED')) 
          {
               $html .= '
          			<script>
          				percent = 0;
          				function checkCategories()
          				{
          					percent++;
          					if (percent > 100)
          						percent = 100;
          					$("#categoriesProgression").html("' . $this->l('Categories loading') . ' : " + percent + " %");
          					if (percent < 100)
          						setTimeout ("checkCategories()", 1000);
          					
          				}
          				$(document).ready(function() {
          					
          					$("#save_ebay_parameters").click(function() {
          						$("#buttonEbayParameters").hide();
          						checkCategories();
          					});
          				});
          			</script>';
          }

          return $html;
     }

     private function _postValidationParameters() 
     {
          // Check configuration values
          if (Tools::getValue('ebay_identifier') == NULL)
               $this->_postErrors[] = $this->l('Your eBay identifier account is not specified or is invalid');
          if (Tools::getValue('ebay_shop') == NULL)
               $this->_postErrors[] = $this->l('Your ebay shop must be specified');
          if (Tools::getValue('ebay_paypal_email') == NULL OR !Validate::isEmail(Tools::getValue('ebay_paypal_email')))
               $this->_postErrors[] = $this->l('Your Paypal E-mail account is not specified or is invalid');
          if (Tools::getValue('ebay_shipping_cost') == '' OR !is_numeric(Tools::getValue('ebay_shipping_cost')))
               $this->_postErrors[] = $this->l('Your shipping cost is not specified or is invalid');
          if (Tools::getValue('ebay_shop_postalcode') == '' OR !Validate::isPostCode(Tools::getValue('ebay_shop_postalcode')))
               $this->_postErrors[] = $this->l('Your shop\'s postal code is not specified or is invalid');
     }

     private function _postProcessParameters() 
     {
          // Saving new configurations
          if ($this->setConfiguration('EBAY_PAYPAL_EMAIL', pSQL(Tools::getValue('ebay_paypal_email'))) &&
                  $this->setConfiguration('EBAY_IDENTIFIER', pSQL(Tools::getValue('ebay_identifier'))) &&
                  $this->setConfiguration('EBAY_SHOP', pSQL(Tools::getValue('ebay_shop'))) &&
                  $this->setConfiguration('EBAY_SHIPPING_COST', (float) (Tools::getValue('ebay_shipping_cost'))) &&
                  $this->setConfiguration('EBAY_SHIPPING_COST_CURRENCY', (int) (Configuration::get('PS_CURRENCY_DEFAULT'))) &&
                  $this->setConfiguration('EBAY_SHIPPING_CARRIER_ID', pSQL(Tools::getValue('ebay_shipping_carrier_id'))) &&
                  $this->setConfiguration('EBAY_SHOP_POSTALCODE', pSQL(Tools::getValue('ebay_shop_postalcode'))))
               $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
          else
               $this->_html .= $this->displayError($this->l('Settings failed'));
     }

     /**
	  * Category Form Config Methods
	  *
	  **/
     private function _getChildCategories($categories, $id, $path = array(), $pathAdd = '') 
     {
          $categoryTab = array();
          if ($pathAdd != '')
               $path[] = $pathAdd;

          if (isset($categories[$id]))
               foreach ($categories[$id] as $idc => $cc) 
               {
                    $name = '';
                    if ($path)
                         foreach ($path as $p)
                              $name .= $p . ' > ';
                    $name .= $cc['infos']['name'];
                    $categoryTab[] = array('id_category' => $cc['infos']['id_category'], 'name' => $name);
                    $categoryTmp = $this->_getChildCategories($categories, $idc, $path, $cc['infos']['name']);
                    $categoryTab = array_merge($categoryTab, $categoryTmp);
               }
          return $categoryTab;
     }

     private function _displayFormCategory() 
     {
          // Check if the module is configured
          if (!Configuration::get('EBAY_PAYPAL_EMAIL'))
               return '<p><b>' . $this->l('You have to configure "General Settings" tab before using this tab.') . '</b></p><br />';

          // Load categories only if necessary
          if (Db::getInstance()->getValue('SELECT COUNT(`id_ebay_category_configuration`) FROM `' . _DB_PREFIX_ . 'ebay_category_configuration`') >= 1 && Tools::getValue('section') != 'category')
               return '<p align="center"><b>' . $this->l('Your categories have already been configured.') . '</b></p>
			<form action="index.php?' . (($this->isVersionOneDotFive()) ? 'controller=' . Tools::safeOutput($_GET['controller'])  : 'tab=' . Tools::safeOutput($_GET['tab'])) . '&configure=' . Tools::safeOutput($_GET['configure']) . '&token=' . Tools::safeOutput($_GET['token']) . '&tab_module=' . Tools::safeOutput($_GET['tab_module']) . '&module_name=' . Tools::safeOutput($_GET['module_name']) . '&id_tab=2&section=category" method="post" class="form">
			<p align="center"><input class="button" name="submitSave" type="submit" value="' . $this->l('See Categories') . '" /></p></form>';

          // Display eBay Categories
          if (!Configuration::get('EBAY_CATEGORY_LOADED')) 
          {
               $ebay = new eBayRequest();
               $ebay->saveCategories();
               $this->setConfiguration('EBAY_CATEGORY_LOADED', 1);
               $this->setConfiguration('EBAY_CATEGORY_LOADED_DATE', date('Y-m-d H:i:s'));
          }


          // Loading categories
          $categoryConfigList = array();
          $categoryConfigListTmp = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'ebay_category_configuration`');
          foreach ($categoryConfigListTmp as $c)
               $categoryConfigList[$c['id_category']] = $c;
          $categoryList = $this->_getChildCategories(Category::getCategories($this->context->language->id), ($this->isVersionOneDotFive()) ? 1 : 0);

          $eBayCategoryList = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'ebay_category` WHERE `id_category_ref` = `id_category_ref_parent`');

          $tabHelp = "&id_tab=6";
          // Display header
          $html = $this->getAlertCategories();

          $html .= '<p><b>' . $this->l('To export your products on eBay, you have to associate each one of your shop categories to an eBay category. You can also define an impact of your price on eBay.') . '</b></p><br />

		<form action="index.php?' . (($this->isVersionOneDotFive()) ? 'controller=' . Tools::safeOutput($_GET['controller'])  : 'tab=' . Tools::safeOutput($_GET['tab'])) . '&configure=' . Tools::safeOutput($_GET['configure']) . '&token=' . Tools::safeOutput($_GET['token']) . '&tab_module=' . Tools::safeOutput($_GET['tab_module']) . '&module_name=' . Tools::safeOutput($_GET['module_name']) . '&id_tab=2&section=category&action=suggestCategories" method="post" class="form" id="configForm2SuggestedCategories">
			<p><b>' . $this->l('You can use the button below to associate automatically the categories which have no association for the moment with an eBay suggested category.') . '</b>
			<input class="button" name="submitSave" type="submit" value="' . $this->l('Suggest Categories') . '" />
			</p><br />
		</form>

		<form action="index.php?' . (($this->isVersionOneDotFive()) ? 'controller=' . Tools::safeOutput($_GET['controller'])  : 'tab=' . Tools::safeOutput($_GET['tab'])) . '&configure=' . Tools::safeOutput($_GET['configure']) . '&token=' . Tools::safeOutput($_GET['token']) . '&tab_module=' . Tools::safeOutput($_GET['tab_module']) . '&module_name=' . Tools::safeOutput($_GET['module_name']) . '&id_tab=2&section=category" method="post" class="form" id="configForm2">
		<table class="table tableDnD" cellpadding="0" cellspacing="0" width="100%">
			<thead>
				<tr class="nodrag nodrop">
					<th style="width:110px">' . $this->l('Category') . ' <br/>(' . $this->l('Quantity in stock') . ')</th>
					<th>' . $this->l('eBay Category') . '</th>
					<th style="width:128px">' . $this->l('Price impact') . '<a title="' . $this->l('Help') . '" href="' . $_SERVER['REQUEST_URI'] . $tabHelp . '" ><img src="' . $this->_path . 'help.png" width="25" alt="help_picture"/></a></th>
				</tr>
			</thead>
			<tbody>';


          // Displaying categories
          if (!$categoryList)
               $html .= '<tr><td colspan="4">' . $this->l('No category found.') . '</td></tr>';
          foreach ($categoryList as $k => $c) 
          {

               if ($this->isVersionOneDotFive())
                    $rq_getCatInStock = '
					SELECT SUM( s.quantity ) AS instockProduct
					FROM `' . _DB_PREFIX_ . 'product` AS p
					INNER JOIN `' . _DB_PREFIX_ . 'stock_available` AS s ON p.id_product = s.id_product
					WHERE p.id_category_default =' . $c['id_category'] . $this->addSqlRestrictionOnLang('s')
                    ;
               else
                    $rq_getCatInStock = 'SELECT SUM( quantity ) AS instockProduct FROM `' . _DB_PREFIX_ . 'product`	WHERE id_category_default = ' . $c['id_category'];

               $getCatInStock = Db::getInstance()->ExecuteS($rq_getCatInStock);

               // Display line
               $alt = 0;
               if ($k % 2 != 0)
                    $alt = ' class="alt_row"';
               $html .= '<tr' . $alt . '>
				<td>' . $c['name'] . ' (' . $getCatInStock[0]['instockProduct'] . ')</td>
				<td id="categoryPath' . $c['id_category'] . '">
					<select name="category' . $c['id_category'] . '" id="categoryLevel1-' . $c['id_category'] . '" rel="' . $c['id_category'] . '" style="font-size: 12px; width: 160px;" OnChange="changeCategoryMatch(1, ' . $c['id_category'] . ');">
						<option value="0">' . $this->l('No category selected') . '</option>';
               foreach ($eBayCategoryList as $ec)
                    $html .= '<option value="' . $ec['id_ebay_category'] . '">' . $ec['name'] . ($ec['is_multi_sku'] == 1 ? ' *' : '') . '</option>';
               $html .= '</select>';
               if (isset($categoryConfigList[$c['id_category']]))
                    $html .= '<script>$(document).ready(function() { loadCategoryMatch(' . $c['id_category'] . '); });</script>';
               $html .= '</td>
				<td><select name="percent' . $c['id_category'] . '" id="percent' . $c['id_category'] . '" rel="' . $c['id_category'] . '" style="font-size: 12px;">';
               for ($i = 5; $i >= -80; $i--)
                    $html .= '<option value="' . $i . '" ' . ((isset($categoryConfigList[$c['id_category']]) && $categoryConfigList[$c['id_category']]['percent'] == $i) || (!isset($categoryConfigList[$c['id_category']]) && $i == 0) ? 'selected="selected"' : '') . '>' . (($i >= 0) ? $i : '- ' . ($i * -1)) . ' %</option>';
               $html .= '</select></td></tr>';
          }

          $html .= '
			</tbody>
		</table><br />
		<div class="margin-form"><input class="button" name="submitSave" type="submit" value="' . $this->l('Save') . '" /></div>
		</form>

		<p><b>' . $this->l('Warning : Only defaults products categories are used for the configuration.') . '</b></p><br />

		<p align="left">
			* ' . $this->l('Some categories benefit from eBay\'s multi-version from which allows to publish one product with multiple versions.') . '<br />
			' . $this->l('For the categories which do not gain this functionality, one ad will be added for each version of the product.') . '<br />
			<a href="http://sellerupdate.ebay.fr/autumn2012/improvements-multi-variation-listings" target="_blank">' . $this->l('Click here for more informations on multi-variation listings') . '</a>
		</p><br /><br />

		<script>
			function loadCategoryMatch(id_category)
			{
				$.ajax({
				  async: false,
				  url: "' . _MODULE_DIR_ . 'ebay/ajax/loadCategoryMatch.php?token=' . Configuration::get('EBAY_SECURITY_TOKEN') . '&id_category=" + id_category + "&time=' . pSQL(date('Ymdhis')) . '",
				  success: function(data) { $("#categoryPath" + id_category).html(data); }
				});
			}
			function changeCategoryMatch(level, id_category)
			{
				var levelParams = "&level1=" + $("#categoryLevel1-" + id_category).val();
				if (level > 1) levelParams += "&level2=" + $("#categoryLevel2-" + id_category).val();
				if (level > 2) levelParams += "&level3=" + $("#categoryLevel3-" + id_category).val();
				if (level > 3) levelParams += "&level4=" + $("#categoryLevel4-" + id_category).val();
				if (level > 4) levelParams += "&level5=" + $("#categoryLevel5-" + id_category).val();

				$.ajax({
				  url: "' . _MODULE_DIR_ . 'ebay/ajax/changeCategoryMatch.php?token=' . Configuration::get('EBAY_SECURITY_TOKEN') . '&time=' . pSQL(date('Ymdhis')) . '&id_category=" + id_category + "&level=" + level + levelParams,
				  success: function(data) { $("#categoryPath" + id_category).html(data); }
				});
			}
		</script>';
          return $html;
     }

     private function _postValidationCategory()
     {
          
     }

     private function _postProcessCategory() 
     {
          // Init Var
          $date = date('Y-m-d H:i:s');
          $services = Tools::getValue('service');

          if (Tools::getValue('action') == 'suggestCategories') 
          {
               // Loading categories
               $ebay = new eBayRequest();
               $categoryConfigList = array();
               $categoryConfigListTmp = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'ebay_category_configuration`');
               foreach ($categoryConfigListTmp as $c)
                    $categoryConfigList[$c['id_category']] = $c;
               $categoryList = Db::getInstance()->executeS('SELECT `id_category`, `name` FROM `' . _DB_PREFIX_ . 'category_lang` WHERE `id_lang` = ' . (int) $this->id_lang . ' ' . (_PS_VERSION_ >= '1.5' ? $this->context->shop->addSqlRestrictionOnLang('cl') : ''));

               foreach ($categoryList as $k => $c)
                    if (!isset($categoryConfigList[$c['id_category']])) 
                    {
                         $productTest = Db::getInstance()->getRow('
					SELECT pl.`name`, pl.`description`
					FROM `' . _DB_PREFIX_ . 'product` p LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (pl.`id_product` = p.`id_product` AND pl.`id_lang` = ' . (int) $this->id_lang . ' ' . (_PS_VERSION_ >= '1.5' ? $this->context->shop->addSqlRestrictionOnLang('pl') : '') . ')
					WHERE `id_category_default` = ' . (int) $c['id_category']);
                         $id_category_ref_suggested = $ebay->getSuggestedCategories($c['name'] . ' ' . $productTest['name']);
                         $id_ebay_category_suggested = Db::getInstance()->getValue('SELECT `id_ebay_category` FROM `' . _DB_PREFIX_ . 'ebay_category` WHERE `id_category_ref` = ' . (int) $id_category_ref_suggested);
                         if ((int) $id_ebay_category_suggested > 0)
                              Db::getInstance()->autoExecute(_DB_PREFIX_ . 'ebay_category_configuration', array('id_country' => 8, 'id_ebay_category' => (int) $id_ebay_category_suggested, 'id_category' => (int) $c['id_category'], 'percent' => 0, 'date_add' => pSQL($date), 'date_upd' => pSQL($date)), 'INSERT');
                    }

               $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
               return true;
          }


          // Sort post datas
          $postValue = array();
          foreach ($_POST as $k => $v) 
          {
               if (strlen($k) > 8 && substr($k, 0, 8) == 'category')
                    $postValue[substr($k, 8, strlen($k) - 8)]['id_ebay_category'] = $v;
               if (strlen($k) > 7 && substr($k, 0, 7) == 'percent')
                    $postValue[substr($k, 7, strlen($k) - 7)]['percent'] = $v;
          }

          // Insert and update configuration
          foreach ($postValue as $id_category => $tab) 
          {
               $arraySQL = array();
               $date = date('Y-m-d H:i:s');
               if ($tab['id_ebay_category'])
                    $arraySQL = array('id_country' => 8, 'id_ebay_category' => (int) $tab['id_ebay_category'], 'id_category' => (int) $id_category, 'percent' => pSQL($tab['percent']), 'date_upd' => pSQL($date));
               $id_ebay_category_configuration = Db::getInstance()->getValue('SELECT `id_ebay_category_configuration` FROM `' . _DB_PREFIX_ . 'ebay_category_configuration` WHERE `id_category` = ' . (int) $id_category);
               if ($id_ebay_category_configuration > 0) 
               {
                    if ($arraySQL)
                         Db::getInstance()->autoExecute(_DB_PREFIX_ . 'ebay_category_configuration', $arraySQL, 'UPDATE', '`id_category` = ' . (int) $id_category);
                    else
                         Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'ebay_category_configuration` WHERE `id_category` = ' . (int) $id_category);
               }
               elseif ($arraySQL) 
               {
                    $arraySQL['date_add'] = $date;
                    Db::getInstance()->autoExecute(_DB_PREFIX_ . 'ebay_category_configuration', $arraySQL, 'INSERT');
               }
          }

          $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
     }

     /**
	  * Template Manager Form Config Methods
      *
      **/
     private function _displayFormTemplateManager() 
     {
          // Check if the module is configured
          if (!Configuration::get('EBAY_PAYPAL_EMAIL'))
               return '<p><b>' . $this->l('You have to configure "General Settings" tab before using this tab.') . '</b></p><br />';

          $iso = $this->context->language->iso_code;
          $isoTinyMCE = (file_exists(_PS_ROOT_DIR_ . '/js/tiny_mce/langs/' . $iso . '.js') ? $iso : 'en');
          $ad = dirname($_SERVER["PHP_SELF"]);

          // Display Form
          $forbiddenJs = array('textarea', 'script', 'onmousedown', 'onmousemove', 'onmmouseup', 'onmouseover', 'onmouseout', 'onload', 'onunload', 'onfocus', 'onblur', 'onchange', 'onsubmit', 'ondblclick', 'onclick', 'onkeydown', 'onkeyup', 'onkeypress', 'onmouseenter', 'onmouseleave', 'onerror');
          $html = '<form action="index.php?' . (($this->isVersionOneDotFive()) ? 'controller=' . Tools::safeOutput($_GET['controller'])  : 'tab=' . Tools::safeOutput($_GET['tab'])) . '&configure=' . Tools::safeOutput($_GET['configure']) . '&token=' . Tools::safeOutput($_GET['token']) . '&tab_module=' . Tools::safeOutput($_GET['tab_module']) . '&module_name=' . Tools::safeOutput($_GET['module_name']) . '&id_tab=3&section=template" method="post" class="form" id="configForm3">
				<fieldset style="border: 0">
					<h4>' . $this->l('You can customise the template for your products page on eBay') . ' :</h4>
					<p>' . $this->l('On eBay, your products are presented in templates that you have to prepare yourself. A good template will:') . '</p>
					<ul style="padding-left:15px;">
						<li>' . $this->l('Bring more professionalism towards your buyers') . '</li>
						<li>' . $this->l('Give your customers all needed information to continue their purchase.') . '</li>
					</ul>
					<br/>
					<textarea class="rte" cols="100" rows="50" name="ebay_product_template">' . str_replace($forbiddenJs, '', Tools::getValue('ebay_product_template', Configuration::get('EBAY_PRODUCT_TEMPLATE'))) . '</textarea><br />';

          if (substr(_PS_VERSION_, 0, 3) == '1.3') 
          {
               $html .= '<script type="text/javascript" src="' . __PS_BASE_URI__ . 'js/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
					<script type="text/javascript">
					tinyMCE.init({
						mode : "textareas",
						theme : "advanced",
						plugins : "safari,pagebreak,style,layer,table,advimage,advlink,inlinepopups,media,searchreplace,contextmenu,paste,directionality,fullscreen",
						// Theme options
						theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
						theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,,|,forecolor,backcolor",
						theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,media,|,ltr,rtl,|,fullscreen",
						theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,pagebreak",
						theme_advanced_toolbar_location : "top",
						theme_advanced_toolbar_align : "left",
						theme_advanced_statusbar_location : "bottom",
						theme_advanced_resizing : false,
						content_css : "' . __PS_BASE_URI__ . 'themes/' . _THEME_NAME_ . '/css/global.css",
						document_base_url : "' . __PS_BASE_URI__ . '",
						width: "850",
						height: "800",
						font_size_style_values : "8pt, 10pt, 12pt, 14pt, 18pt, 24pt, 36pt",
						// Drop lists for link/image/media/template dialogs
						template_external_list_url : "lists/template_list.js",
						external_link_list_url : "lists/link_list.js",
						external_image_list_url : "lists/image_list.js",
						media_external_list_url : "lists/media_list.js",
						elements : "nourlconvert",
						convert_urls : false,
						language : "' . (file_exists(_PS_ROOT_DIR_ . '/js/tinymce/jscripts/tiny_mce/langs/' . $iso . '.js') ? $iso : 'en') . '"
					});
					</script>';
          } 
          else if ($this->isVersionOneDotFive()) 
          {
               $html .= '<script type="text/javascript">	
							var iso = \'' . (file_exists(_PS_ROOT_DIR_ . '/js/tiny_mce/langs/' . $iso . '.js') ? $iso : 'en') . '\' ;
							var pathCSS = \'' . _THEME_CSS_DIR_ . '\' ;
							var ad = \'' . dirname($_SERVER['PHP_SELF']) . '\' ;
						</script>
						<script type="text/javascript" src="' . __PS_BASE_URI__ . 'js/tiny_mce/tiny_mce.js"></script>
						<script type="text/javascript" src="' . __PS_BASE_URI__ . 'js/tinymce.inc.js"></script>

						<script type="text/javascript">
                         $(document).ready(function(){
   
                              tinySetup({
                               editor_selector :"rte",
                               theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull|cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,undo,redo",
                               theme_advanced_buttons2 : "link,unlink,anchor,image,cleanup,code,|,forecolor,backcolor,|,hr,removeformat,visualaid,|,charmap,media,|,ltr,rtl,|,fullscreen",
                               theme_advanced_buttons3 : "",
                               theme_advanced_buttons4 : ""
                              });

                        });
                   </script>
						';
          } 
          else 
          {
               $html .= '<script type="text/javascript">
						var iso = \'' . $isoTinyMCE . '\';
						var pathCSS = \'' . _THEME_CSS_DIR_ . '\';
						var ad = \'' . $ad . '\';
						</script>
						<script type="text/javascript" src="' . _PS_JS_DIR_ . '/tiny_mce/tiny_mce.js"></script>
						<script type="text/javascript" src="' . _PS_JS_DIR_ . '/tinymce.inc.js"></script>
						<script>
							tinyMCE.settings.width = 850;
							tinyMCE.settings.height = 800;
							tinyMCE.settings.extended_valid_elements = "iframe[id|class|title|style|align|frameborder|height|longdesc|marginheight|marginwidth|name|scrolling|src|width]";
							tinyMCE.settings.extended_valid_elements = "link[href|type|rel|id|class|title|style|align|frameborder|height|longdesc|marginheight|marginwidth|name|scrolling|src|width]";
						</script>';
          }

          $html .= '</fieldset>
				<div class="margin-form"><input class="button" name="submitSave" value="' . $this->l('Save') . '" type="submit"></div>
			</form>';


          return $html;
     }

     private function _postValidationTemplateManager() 
     {
          
     }

     private function _postProcessTemplateManager() 
     {
          // Saving new configurations
          if ($this->setConfiguration('EBAY_PRODUCT_TEMPLATE', Tools::getValue('ebay_product_template'), true))
               $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
          else
               $this->_html .= $this->displayError($this->l('Settings failed'));
     }

     /**
	  * Ebay Sync Form Config Methods
	  *
      **/

     private function _displayFormEbaySync() 
     {
          // Check if the module is configured
          if (!Configuration::get('EBAY_PAYPAL_EMAIL'))
               return '<p><b>' . $this->l('You have to configure "General Settings" tab before using this tab.') . '</b></p><br />';
          if (Db::getInstance()->getValue('SELECT COUNT(`id_ebay_category_configuration`) as nb FROM `' . _DB_PREFIX_ . 'ebay_category_configuration`') < 1)
               return '<p><b>' . $this->l('You have to configure "Categories Settings" tab before using this tab.') . '</b></p><br />';

          if ($this->isVersionOneDotFive()) 
          {
               $nbProductsModeA = Db::getInstance()->getValue('
			SELECT COUNT( * ) FROM (
				SELECT COUNT( p.id_product ) AS nb
					FROM  `' . _DB_PREFIX_ . 'product` AS p
					INNER JOIN  `' . _DB_PREFIX_ . 'stock_available` AS s ON p.id_product = s.id_product
					WHERE s.`quantity` >0
					AND  `active` =1
					AND  `id_category_default`
					IN (

						SELECT  `id_category` 
						FROM  `' . _DB_PREFIX_ . 'ebay_category_configuration` 
						WHERE  `id_ebay_category` >0
					)
					' . $this->addSqlRestrictionOnLang('s') . '
					GROUP BY p.id_product
			)TableReponse');
               $nbProductsModeB = Db::getInstance()->getValue('
			SELECT COUNT( * ) FROM (
				SELECT COUNT( p.id_product ) AS nb
					FROM  `' . _DB_PREFIX_ . 'product` AS p
					INNER JOIN  `' . _DB_PREFIX_ . 'stock_available` AS s ON p.id_product = s.id_product
					WHERE s.`quantity` >0
					AND  `active` =1
					AND  `id_category_default`
					IN (

						SELECT  `id_category` 
						FROM  `' . _DB_PREFIX_ . 'ebay_category_configuration` 
						WHERE  `id_ebay_category` >0 AND `sync` = 1
					)' . $this->addSqlRestrictionOnLang('s') . '
					GROUP BY p.id_product
			)TableReponse');
          } 
          else 
          {
               $nbProductsModeA = Db::getInstance()->getValue('
			SELECT COUNT(`id_product`) as nb
			FROM `' . _DB_PREFIX_ . 'product`
			WHERE `quantity` > 0 AND `active` = 1
			AND `id_category_default` IN (SELECT `id_category` FROM `' . _DB_PREFIX_ . 'ebay_category_configuration` WHERE `id_ebay_category` > 0)');
               $nbProductsModeB = Db::getInstance()->getValue('
			SELECT COUNT(`id_product`) as nb
			FROM `' . _DB_PREFIX_ . 'product`
			WHERE `quantity` > 0 AND `active` = 1
			AND `id_category_default` IN (SELECT `id_category` FROM `' . _DB_PREFIX_ . 'ebay_category_configuration` WHERE `id_ebay_category` > 0 AND `sync` = 1)');
          }
          $nbProducts = $nbProductsModeA;
          if (Configuration::get('EBAY_SYNC_MODE') == 'B')
               $nbProducts = $nbProductsModeB;

          if (($nbProducts == 0) || ($nbProducts == 1))
               $prod_nb = $this->l('product');
          else
               $prod_nb = $this->l('products');
          // Display Form
          $html = '<style> 
			#button_ebay_sync1{background-image:url(' . $this->_path . 'ebay.png);background-repeat:no-repeat;background-position:center 90px;width:500px;height:191px;cursor:pointer;padding-bottom:100px;font-weight:bold;font-size:25px;}
			#button_ebay_sync2{background-image:url(' . $this->_path . 'ebay.png);background-repeat:no-repeat;background-position:center 90px;width:500px;height:191px;cursor:pointer;padding-bottom:100px;font-weight:bold;font-size:15px;}
		</style>
		<script>
			var nbProducts = ' . $nbProducts . ';
			var nbProductsModeA = ' . $nbProductsModeA . ';
			var nbProductsModeB = ' . $nbProductsModeB . ';
			$(document).ready(function() {
				$(".categorySync").click(function() {

					var params = "";
					if ($(this).attr("value") > 0)
						params = "&id_category=" + $(this).attr("value");
					if ($(this).attr("checked"))
						params = params + "&action=1";
					else
						params = params + "&action=0";

					$.ajax({
						url: "' . _MODULE_DIR_ . 'ebay/ajax/getNbProductsSync.php?token=' . Configuration::get('EBAY_SECURITY_TOKEN') . '&time=' . pSQL(date('Ymdhis')) . '" + params,
						success: function(data) {
					  		nbProducts = data;
					  		nbProductsModeB = data;
							$("#button_ebay_sync1").attr("value", "' . $this->l('Sync with eBay') . '\n(" + data + " ' . $prod_nb . ')");
							$("#button_ebay_sync2").attr("value", "' . $this->l('Sync with eBay') . '\n' . $this->l('and update') . '\n(" + data + " ' . $prod_nb . ')");
						}
					});
				});
			});


			$(document).ready(function() {
				$("#ebay_sync_mode1").click(function() {
					nbProducts = nbProductsModeA;
					$("#catSync").hide("slow");
					$("#button_ebay_sync1").attr("value", "' . $this->l('Sync with eBay') . '\n(" + nbProducts + " ' . $this->l('products') . ')");
					$("#button_ebay_sync2").attr("value", "' . $this->l('Sync with eBay') . '\n' . $this->l('and update') . '\n(" + nbProducts + " ' . $prod_nb . ')");
				});
				$("#ebay_sync_mode2").click(function() {
					nbProducts = nbProductsModeB;
					$("#catSync").show("slow");
					$("#button_ebay_sync1").attr("value", "' . $this->l('Sync with eBay') . '\n(" + nbProducts + " ' . $prod_nb . ')");
					$("#button_ebay_sync2").attr("value", "' . $this->l('Sync with eBay') . '\n' . $this->l('and update') . '\n(" + nbProducts + " ' . $prod_nb . ')");
				});
			});

			function eBaySync(option)
			{
				$(".categorySync").attr("disabled", "true");
				$("#ebay_sync_mode1").attr("disabled", "true");
				$("#ebay_sync_mode2").attr("disabled", "true");
				$("#ebay_sync_option_resync").attr("disabled", "true");
				$("#button_ebay_sync1").attr("disabled", "true");
				$("#button_ebay_sync1").css("background-color", "#D5D5D5");
				$("#button_ebay_sync2").attr("disabled", "true");
				$("#button_ebay_sync2").css("background-color", "#D5D5D5");
				$("#resultSync").html("<img src=\"../modules/ebay/loading-small.gif\" border=\"0\" />");
				eBaySyncProduct(option);
			}

			function reableSyncProduct(){
				$(".categorySync").removeAttr("disabled", "disabled");
				$("#ebay_sync_mode1").removeAttr("disabled", "disabled");
				$("#ebay_sync_mode2").removeAttr("disabled", "disabled");
				$("#ebay_sync_option_resync").removeAttr("disabled", "disabled");
				$("#button_ebay_sync1").removeAttr("disabled", "disabled");
				$("#button_ebay_sync1").css("background-color", "#FFFAC6");
				$("#button_ebay_sync2").removeAttr("disabled", "disabled");
				$("#button_ebay_sync2").css("background-color", "#FFFAC6");
			}
			var counter = 0;
			function eBaySyncProduct(option)
			{
				counter++;
				$.ajax({
				  url: \'' . _MODULE_DIR_ . 'ebay/ajax/eBaySyncProduct.php?token=' . Configuration::get('EBAY_SECURITY_TOKEN') . '&option=\'+option+\'&time=' . pSQL(date('Ymdhis')) . '\'+counter,
				  success: function(data)
				  {
					tab = data.split("|");
					$("#resultSync").html(tab[1]);
					if (tab[0] != "OK")
						eBaySyncProduct(option);
					else
						reableSyncProduct();
				  }
				});
			}
		</script>

		<div id="resultSync" style="text-align: center; font-weight: bold; font-size: 14px;"></div>

		<form action="index.php?' . (($this->isVersionOneDotFive()) ? 'controller=' . Tools::safeOutput($_GET['controller'])  : 'tab=' . Tools::safeOutput($_GET['tab'])) . '&configure=' . Tools::safeOutput($_GET['configure']) . '&token=' . Tools::safeOutput($_GET['token']) . '&tab_module=' . Tools::safeOutput($_GET['tab_module']) . '&module_name=' . Tools::safeOutput($_GET['module_name']) . '&id_tab=4&section=sync" method="post" class="form" id="configForm4">
				<fieldset style="border: 0">
					<h4>' . $this->l('You will now push your products on eBay.') . ' <b></h4>
					<label style="width: 250px;">' . $this->l('Sync Mode') . ' : </label><br clear="left" /><br /><br />
					<div class="margin-form">
						<input type="radio" size="20" name="ebay_sync_mode" id="ebay_sync_mode1" value="A" checked="checked" /> ' . $this->l('Option A') . ' : ' . $this->l('Sync all your products with eBay') . '
					</div>
					<div class="margin-form">
						<input type="radio" size="20" name="ebay_sync_mode" id="ebay_sync_mode2" value="B" /> ' . $this->l('Option B') . ' : ' . $this->l('Sync the products only from selected categories') . '
					</div>
					<label style="width: 250px;">' . $this->l('Option') . ' : </label><br clear="left" /><br /><br />
					<div class="margin-form">
						<input type="checkbox" size="20" name="ebay_sync_option_resync" id="ebay_sync_option_resync" value="1" ' . (Configuration::get('EBAY_SYNC_OPTION_RESYNC') == 1 ? 'checked="checked"' : '') . ' /> ' . $this->l('When update a product, resync only price and quantity') . '
					</div>
					<div style="display: none;" id="catSync">';


          // Loading categories
          $categoryConfigList = array();
          $categoryConfigListTmp = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'ebay_category_configuration`');
          foreach ($categoryConfigListTmp as $c)
               $categoryConfigList[$c['id_category']] = $c;
          $categoryList = $this->_getChildCategories(Category::getCategories($this->context->language->id), 0);
          $html .= '<table class="table tableDnD" cellpadding="0" cellspacing="0" width="90%">
			<thead>
				<tr class="nodrag nodrop">
					<th>' . $this->l('Select') . '</th>
					<th>' . $this->l('Category') . '</th>
				</tr>
			</thead>
			<tbody>';
          if (!$categoryList)
               $html .= '<tr><td colspan="2">' . $this->l('No category found.') . '</td></tr>';
          $i = 0;
          foreach ($categoryList as $k => $c) 
          {
               // Display line
               $alt = 0;
               if ($i % 2 != 0)
                    $alt = ' class="alt_row"';
               if (isset($categoryConfigList[$c['id_category']]['id_ebay_category']) && $categoryConfigList[$c['id_category']]['id_ebay_category'] > 0) 
               {
                    $html .= '<tr' . $alt . '><td><input type="checkbox" class="categorySync" name="category[]" value="' . $c['id_category'] . '" ' . ($categoryConfigList[$c['id_category']]['sync'] == 1 ? 'checked="checked"' : '') . ' /><td>' . $c['name'] . '</td></tr>';
                    $i++;
               }
          }
          $html .= '</tbody></table>';
          if (Tools::getValue('section') == 'sync' && Tools::getValue('submitSave1') != '')
               $html .= '<script>$(document).ready(function() { eBaySync(1); });</script>';
          if (Tools::getValue('section') == 'sync' && Tools::getValue('submitSave2') != '')
               $html .= '<script>$(document).ready(function() { eBaySync(2); });</script>';

          if (Configuration::get('EBAY_SYNC_MODE') == 'B')
          {
               $html .= '<script>
					$(document).ready(function() {
						$("#catSync").show("slow");
						$("#ebay_sync_mode2").attr("checked", true);
					});
				</script>';
          }

          $html .= '
					</div>
				</fieldset>
				<h4>' . $this->l('Beware ! If some of your categories are not multi sku compliant, some of your products may create more than one product on eBay.') . '</h4>

				<table>
					<tr>
						<td style="color: #268CCD"><h4>' . $this->l('"Sync with eBay" option will only sync the products that are not already sync with eBay : ') . '</h4></td>
						<td style="width: 50px">&nbsp;</td>
						<td style="color: #268CCD"><h4>' . $this->l('"Sync and update with eBay" will sync the products that are not already sync with eBay and update the products already sync with eBay : ') . '</h4></td>
					</tr>
					<tr>
						<td><input id="button_ebay_sync1" class="button" name="submitSave1" value="' . $this->l('Sync with eBay') . "\n" . '(' . $nbProducts . ' ' . $prod_nb . ')" OnClick="return confirm(\'' . $this->l('You will push') . ' \' + nbProducts + \' ' . $this->l('products on eBay. Do you want to confirm ?') . '\');" type="submit"></td>
						<td style="width: 50px">&nbsp;</td>
						<td><input id="button_ebay_sync2" class="button" name="submitSave2" value="' . $this->l('Sync with eBay') . "\n" . $this->l('and update') . "\n" . '(' . $nbProducts . ' ' . $prod_nb . ')" OnClick="return confirm(\'' . $this->l('You will push') . ' \' + nbProducts + \' ' . $this->l('products on eBay. Do you want to confirm ?') . '\');" type="submit"></td>
					</tr>
				</table>
				<br />
			</form>';


          return $html;
     }

     private function _postValidationEbaySync() 
     {
          
     }

     private function _postProcessEbaySync() 
     {
          // Update Sync Option
          $this->setConfiguration('EBAY_SYNC_OPTION_RESYNC', (Tools::getValue('ebay_sync_option_resync') == 1 ? 1 : 0));

          // Empty error result
          $this->setConfiguration('EBAY_SYNC_LAST_PRODUCT', 0);

          @unlink(dirname(__FILE__) . '/log/syncError.php');

          if ($_POST['ebay_sync_mode'] == 'A')
               $this->setConfiguration('EBAY_SYNC_MODE', 'A');
          else 
          {
               $this->setConfiguration('EBAY_SYNC_MODE', 'B');

               // Select the sync Categories and Retrieve product list for eBay (which have matched and sync categories)
               if (isset($_POST['category'])) 
               {
                    Db::getInstance()->autoExecute(_DB_PREFIX_ . 'ebay_category_configuration', array('sync' => 0), 'UPDATE', '');
                    foreach ($_POST['category'] as $id_category)
                         Db::getInstance()->autoExecute(_DB_PREFIX_ . 'ebay_category_configuration', array('sync' => 1), 'UPDATE', '`id_category` = ' . (int) $id_category);
               }
          }
     }

     public function ajaxProductSync() 
     {
          $whereOption1 = 'AND `id_product` NOT IN (SELECT `id_product` FROM `' . _DB_PREFIX_ . 'ebay_product`)';

          if ($this->isVersionOneDotFive()) 
          {
               $whereOption1 = 'AND p.`id_product` NOT IN (SELECT `id_product` FROM `' . _DB_PREFIX_ . 'ebay_product`)';
               if (Configuration::get('EBAY_SYNC_MODE') == 'A') 
               {
                    // Retrieve total nb products for eBay (which have matched categories)
                    $nbProductsTotal = Db::getInstance()->getValue('
				SELECT COUNT( * ) FROM (
					SELECT COUNT(p.id_product) AS nb
						FROM  `' . _DB_PREFIX_ . 'product` AS p
						INNER JOIN  `' . _DB_PREFIX_ . 'stock_available` AS s ON p.id_product = s.id_product
						WHERE s.`quantity` >0
						AND  `active` =1
						AND  `id_category_default` 
						IN (

							SELECT  `id_category` 
							FROM  `' . _DB_PREFIX_ . 'ebay_category_configuration` 
							WHERE  `id_ebay_category` > 0
							 AND `id_ebay_category` > 0
						)
						' . $this->addSqlRestrictionOnLang('s') . '
						GROUP BY p.id_product
				)TableReponse');

                    // Retrieve products list for eBay (which have matched categories)
                    $productsList = Db::getInstance()->executeS('
					SELECT p.id_product
					FROM  `' . _DB_PREFIX_ . 'product` AS p
						INNER JOIN  `' . _DB_PREFIX_ . 'stock_available` AS s ON p.id_product = s.id_product
					WHERE s.`quantity` >0
					  AND  `active` =1
					  AND  `id_category_default` 
						IN (SELECT  `id_category` 
							FROM  `' . _DB_PREFIX_ . 'ebay_category_configuration` 
							WHERE  `id_category` >0
							  AND  `id_ebay_category` >0
						)
					  ' . (Tools::getValue('option') == 1 ? $whereOption1 : '') . '
				      AND p.`id_product` >  ' . (int) Configuration::get('EBAY_SYNC_LAST_PRODUCT') . '
				      ' . $this->addSqlRestrictionOnLang('s') . '
					ORDER BY  p.`id_product` 
					LIMIT 1');


                    // How Many Product Less ?
                    $nbProductsLess = Db::getInstance()->getValue('
				SELECT COUNT(id_supplier) FROM(
					SELECT id_supplier 
						FROM  `' . _DB_PREFIX_ . 'product` AS p
							INNER JOIN  `' . _DB_PREFIX_ . 'stock_available` AS s ON p.id_product = s.id_product
						WHERE s.`quantity` >0
						  AND  `active` =1
						  AND  `id_category_default` 
						  IN (
							SELECT  `id_category` 
							FROM  `' . _DB_PREFIX_ . 'ebay_category_configuration` 
							WHERE  `id_category` >0
							AND  `id_ebay_category` >0
						  )
						  ' . (Tools::getValue('option') == 1 ? $whereOption1 : '') . '
						  AND p.`id_product` >' . (int) Configuration::get('EBAY_SYNC_LAST_PRODUCT') . '
						  ' . $this->addSqlRestrictionOnLang('s') . '
						  GROUP BY p.id_product
				)TableRequete');
               } 
               else 
               {
                    // Retrieve total nb products for eBay (which have matched categories)
                    $nbProductsTotal = Db::getInstance()->getValue('
				SELECT COUNT( * ) FROM (
					SELECT COUNT(p.id_product) AS nb
						FROM  `' . _DB_PREFIX_ . 'product` AS p
						INNER JOIN  `' . _DB_PREFIX_ . 'stock_available` AS s ON p.id_product = s.id_product
						WHERE s.`quantity` >0
						AND  `active` =1
						AND  `id_category_default` 
						IN (

							SELECT  `id_category` 
							FROM  `' . _DB_PREFIX_ . 'ebay_category_configuration` 
							WHERE  `id_ebay_category` > 0
							 AND `id_ebay_category` > 0
							 AND `sync` = 1
						)
						' . $this->addSqlRestrictionOnLang('s') . '
						GROUP BY p.id_product
				)TableReponse');

                    // Retrieve products list for eBay (which have matched categories)
                    $productsList = Db::getInstance()->executeS('
					SELECT p.id_product
					FROM  `' . _DB_PREFIX_ . 'product` AS p
						INNER JOIN  `' . _DB_PREFIX_ . 'stock_available` AS s ON p.id_product = s.id_product
					WHERE s.`quantity` >0
					  AND  `active` =1
					  AND  `id_category_default` 
						IN (SELECT  `id_category` 
							FROM  `' . _DB_PREFIX_ . 'ebay_category_configuration` 
							WHERE  `id_category` >0
							  AND  `id_ebay_category` >0
							  AND `sync` = 1
						)
					  ' . (Tools::getValue('option') == 1 ? $whereOption1 : '') . '
				      AND p.`id_product` >  ' . (int) Configuration::get('EBAY_SYNC_LAST_PRODUCT') . '
				      ' . $this->addSqlRestrictionOnLang('s') . '
					ORDER BY  `id_product` 
					LIMIT 1');

                    // How Many Product Less ?
                    $nbProductsLess = Db::getInstance()->getValue('
				SELECT COUNT(id_supplier) FROM(
					SELECT id_supplier
						FROM  `' . _DB_PREFIX_ . 'product` AS p
							INNER JOIN  `' . _DB_PREFIX_ . 'stock_available` AS s ON p.id_product = s.id_product
						WHERE s.`quantity` >0
						  AND  `active` =1
						  AND  `id_category_default` 
						  IN (
							SELECT  `id_category` 
							FROM  `' . _DB_PREFIX_ . 'ebay_category_configuration` 
							WHERE  `id_category` >0
							AND  `id_ebay_category` >0
							AND `sync` = 1
						  )
						  ' . (Tools::getValue('option') == 1 ? $whereOption1 : '') . '
						  AND p.`id_product` >' . (int) Configuration::get('EBAY_SYNC_LAST_PRODUCT') . '
						  ' . $this->addSqlRestrictionOnLang('s') . '
						  GROUP BY p.id_product
				)TableRequete');
               }
          } 
          else 
          {
               if (Configuration::get('EBAY_SYNC_MODE') == 'A') 
               {
                    // Retrieve total nb products for eBay (which have matched categories)
                    $nbProductsTotal = Db::getInstance()->getValue('
				SELECT COUNT(`id_product`)
				FROM `' . _DB_PREFIX_ . 'product`
				WHERE `quantity` > 0 AND `active` = 1
				AND `id_category_default` IN (SELECT `id_category` FROM `' . _DB_PREFIX_ . 'ebay_category_configuration` WHERE `id_category` > 0 AND `id_ebay_category` > 0)');

                    // Retrieve products list for eBay (which have matched categories)
                    $productsList = Db::getInstance()->executeS('
				SELECT `id_product` FROM `' . _DB_PREFIX_ . 'product`
				WHERE `quantity` > 0 AND `active` = 1
				AND `id_category_default` IN (SELECT `id_category` FROM `' . _DB_PREFIX_ . 'ebay_category_configuration` WHERE `id_category` > 0 AND `id_ebay_category` > 0)
				' . (Tools::getValue('option') == 1 ? $whereOption1 : '') . '
				AND `id_product` > ' . (int) Configuration::get('EBAY_SYNC_LAST_PRODUCT') . '
				ORDER BY `id_product`
				LIMIT 1');

                    // How Many Product Less ?
                    $nbProductsLess = Db::getInstance()->getValue('
				SELECT COUNT(`id_product`) FROM `' . _DB_PREFIX_ . 'product`
				WHERE `quantity` > 0 AND `active` = 1
				AND `id_category_default` IN (SELECT `id_category` FROM `' . _DB_PREFIX_ . 'ebay_category_configuration` WHERE `id_category` > 0 AND `id_ebay_category` > 0)
				' . (Tools::getValue('option') == 1 ? $whereOption1 : '') . '
				AND `id_product` > ' . (int) Configuration::get('EBAY_SYNC_LAST_PRODUCT'));
               } else {
                    // Retrieve total nb products for eBay (which have matched categories)
                    $nbProductsTotal = Db::getInstance()->getValue('
				SELECT COUNT(`id_product`)
				FROM `' . _DB_PREFIX_ . 'product`
				WHERE `quantity` > 0 AND `active` = 1
				AND `id_category_default` IN (SELECT `id_category` FROM `' . _DB_PREFIX_ . 'ebay_category_configuration` WHERE `id_category` > 0 AND `id_ebay_category` > 0 AND `sync` = 1)');

                    // Retrieve products list for eBay (which have matched categories)
                    $productsList = Db::getInstance()->executeS('
				SELECT `id_product` FROM `' . _DB_PREFIX_ . 'product`
				WHERE `quantity` > 0 AND `active` = 1
				AND `id_category_default` IN (SELECT `id_category` FROM `' . _DB_PREFIX_ . 'ebay_category_configuration` WHERE `id_category` > 0 AND `id_ebay_category` > 0 AND `sync` = 1)
				' . (Tools::getValue('option') == 1 ? $whereOption1 : '') . '
				AND `id_product` > ' . (int) Configuration::get('EBAY_SYNC_LAST_PRODUCT') . '
				ORDER BY `id_product`
				LIMIT 1');

                    // How Many Product Less ?
                    $nbProductsLess = Db::getInstance()->getValue('
				SELECT COUNT(`id_product`) FROM `' . _DB_PREFIX_ . 'product`
				WHERE `quantity` > 0 AND `active` = 1
				AND `id_category_default` IN (SELECT `id_category` FROM `' . _DB_PREFIX_ . 'ebay_category_configuration` WHERE `id_category` > 0 AND `id_ebay_category` > 0 AND `sync` = 1)
				' . (Tools::getValue('option') == 1 ? $whereOption1 : '') . '
				AND `id_product` > ' . (int) Configuration::get('EBAY_SYNC_LAST_PRODUCT'));
               }
          }
          // Send each product on eBay
          if (count($productsList) >= 1) 
          {
               $this->setConfiguration('EBAY_SYNC_LAST_PRODUCT', (int) $productsList[0]['id_product']);

               // Sync product
               $this->_syncProducts($productsList);

               echo 'KO|<br /><br /> <img src="../modules/ebay/loading-small.gif" border="0" /> ' . $this->l('Products') . ' : ' . ($nbProductsTotal - $nbProductsLess + 1) . ' / ' . $nbProductsTotal . '<br /><br />';
          } 
          else 
          {
               echo 'OK|' . $this->displayConfirmation($this->l('Settings updated') . ' (' . $this->l('Option') . ' ' . Configuration::get('EBAY_SYNC_MODE') . ' : ' . ($nbProductsTotal - $nbProductsLess) . ' / ' . $nbProductsTotal . ' ' . $this->l('product(s) sync with eBay') . ')');
               if (file_exists(dirname(__FILE__) . '/log/syncError.php')) 
               {
                    global $tab_error;
                    include(dirname(__FILE__) . '/log/syncError.php');
                    foreach ($tab_error as $error) 
                    {
                         $productsDetails = '<br /><u>' . $this->l('Product(s) concerned') . ' :</u>';
                         foreach ($error['products'] as $product)
                              $productsDetails .= '<br />- ' . $product;
                         echo $this->displayError($error['msg'] . '<br />' . $productsDetails);
                    }
                    echo '<style>#content .alert { text-align: left; width: 875px; }</style>';
                    @unlink(dirname(__FILE__) . '/log/syncError.php');
               }
          }
     }

     public function findIfCategoryParentIsMultiSku($id_category_ref) 
     {
          $row = Db::getInstance()->getRow('SELECT `id_category_ref_parent`, `is_multi_sku` FROM `' . _DB_PREFIX_ . 'ebay_category` WHERE `id_category_ref` = ' . (int) $id_category_ref);
          if ($row['id_category_ref_parent'] != $id_category_ref)
               return $this->findIfCategoryParentIsMultiSku($row['id_category_ref_parent']);
          return $row['is_multi_sku'];
     }

     private function _syncProducts($productsList) 
     {

          $fees = 0;
          $count = 0;
          $count_success = 0;
          $count_error = 0;
          $tab_error = array();
          $date = date('Y-m-d H:i:s');
          $ebay = new eBayRequest();
          $categoryDefaultCache = array();

          // Get errors back
          if (file_exists(dirname(__FILE__) . '/log/syncError.php')) 
          {
               global $tab_error;
               include(dirname(__FILE__) . '/log/syncError.php');
          }

          // Up the time limit
          @set_time_limit(3600);

          // Run the products list
          foreach ($productsList as $p) 
          {
               // Product instanciation
               $product = new Product((int) $p['id_product'], true, $this->id_lang);
               if (!$this->isVersionOneDotFive()) 
               {
                    $productQuantity = new Product((int) $p['id_product']);
                    $quantityProduct = $productQuantity->quantity;
               }
               else
                    $quantityProduct = $product->quantity;

               if (Validate::isLoadedObject($product) && $product->id_category_default > 0) 
               {
                    // Load default category matched in cache
                    if (!isset($categoryDefaultCache[$product->id_category_default]))
                         $categoryDefaultCache[$product->id_category_default] = Db::getInstance()->getRow('SELECT ec.`id_category_ref`, ec.`is_multi_sku`, ecc.`percent` FROM `' . _DB_PREFIX_ . 'ebay_category` ec LEFT JOIN `' . _DB_PREFIX_ . 'ebay_category_configuration` ecc ON (ecc.`id_ebay_category` = ec.`id_ebay_category`) WHERE ecc.`id_category` = ' . (int) $product->id_category_default);
                    if ($categoryDefaultCache[$product->id_category_default]['is_multi_sku'] != 1)
                         $categoryDefaultCache[$product->id_category_default]['is_multi_sku'] = $this->findIfCategoryParentIsMultiSku($categoryDefaultCache[$product->id_category_default]['id_category_ref']);

                    // Load Pictures
                    $pictures = array();
                    $picturesMedium = array();
                    $picturesLarge = array();
                    $prefix = (substr(_PS_VERSION_, 0, 3) == '1.3' ? 'http://' . Configuration::get('PS_SHOP_DOMAIN') . '/' : '');
                    $images = $product->getImages($this->id_lang);
                    foreach ($images as $image) 
                    {
                         $pictures[] = str_replace('https://', 'http://', $prefix . $this->context->link->getImageLink('ebay', $product->id . '-' . $image['id_image'], 'large' . ($this->isVersionOneDotFive() ? '_default' : '')));
                         $picturesMedium[] = str_replace('https://', 'http://', $prefix . $this->context->link->getImageLink('ebay', $product->id . '-' . $image['id_image'], 'medium' . ($this->isVersionOneDotFive() ? '_default' : '')));
                         $picturesLarge[] = str_replace('https://', 'http://', $prefix . $this->context->link->getImageLink('ebay', $product->id . '-' . $image['id_image'], 'large' . ($this->isVersionOneDotFive() ? '_default' : '')));
                    }
                    // Load Variations
                    $variations = array();
                    $variationsList = array();
                    $combinations = $product->getAttributeCombinaisons($this->id_lang);
                    if (isset($combinations))
                         foreach ($combinations as $c) 
                         {
                              $variationsList[$c['group_name']][$c['attribute_name']] = 1;
                              $variations[$c['id_product'] . '-' . $c['id_product_attribute']]['id_attribute'] = $c['id_product_attribute'];
                              $variations[$c['id_product'] . '-' . $c['id_product_attribute']]['reference'] = $c['reference'];
                              $variations[$c['id_product'] . '-' . $c['id_product_attribute']]['quantity'] = $c['quantity'];
                              $variations[$c['id_product'] . '-' . $c['id_product_attribute']]['variations'][] = array('name' => $c['group_name'], 'value' => $c['attribute_name']);
                              $variations[$c['id_product'] . '-' . $c['id_product_attribute']]['price_static'] = Product::getPriceStatic((int) $c['id_product'], true, (int) $c['id_product_attribute']);

                              $price = $variations[$c['id_product'] . '-' . $c['id_product_attribute']]['price_static'];
                              $price_original = $price;
                              if ($categoryDefaultCache[$product->id_category_default]['percent'] > 0)
                                   $price *= (1 + ($categoryDefaultCache[$product->id_category_default]['percent'] / 100));
                              else if ($categoryDefaultCache[$product->id_category_default]['percent'] < 0)
                                   $price *= (1 - ($categoryDefaultCache[$product->id_category_default]['percent'] / (-100)));

                              $variations[$c['id_product'] . '-' . $c['id_product_attribute']]['price'] = round($price, 2);
                              if ($categoryDefaultCache[$product->id_category_default]['percent'] < 0) 
                              {
                                   $variations[$c['id_product'] . '-' . $c['id_product_attribute']]['price_original'] = round($price_original, 2);
                                   $variations[$c['id_product'] . '-' . $c['id_product_attribute']]['price_percent'] = round($categoryDefaultCache[$product->id_category_default]['percent']);
                              }
                         }

                    // Load Variations Pictures
                    $combinationsImages = $product->getCombinationImages(2);
                    if (isset($combinationsImages) && !empty($combinationsImages) && count($combinationsImages) > 0)
                         foreach ($combinationsImages as $ci)
                              foreach ($ci as $i)
                                   $variations[$product->id . '-' . $i['id_product_attribute']]['pictures'][] = $prefix . $this->context->link->getImageLink('ebay', $product->id . '-' . $i['id_image'], 'large' . ($this->isVersionOneDotFive() ? '_default' : ''));


                    // Load basic price
                    $price = Product::getPriceStatic((int) $product->id, true);
                    $price_original = $price;
                    if ($categoryDefaultCache[$product->id_category_default]['percent'] > 0)
                         $price *= (1 + ($categoryDefaultCache[$product->id_category_default]['percent'] / 100));
                    else if ($categoryDefaultCache[$product->id_category_default]['percent'] < 0)
                         $price *= (1 - ($categoryDefaultCache[$product->id_category_default]['percent'] / (-100)));
                    $price = round($price, 2);


                    // Loading Shipping Method
                    if (!isset($this->_shippingMethod[Configuration::get('EBAY_SHIPPING_CARRIER_ID')]['shippingService']))
                         $this->loadShippingMethod();

                    // Generate array and try insert in database
                    $datas = array(
                        'id_product' => $product->id,
                        'reference' => $product->reference,
                        'name' => str_replace('&', '&amp;', $product->name),
                        'brand' => $product->manufacturer_name,
                        'description' => $product->description,
                        'description_short' => $product->description_short,
                        'price' => $price,
                        'quantity' => $quantityProduct,
                        'categoryId' => $categoryDefaultCache[$product->id_category_default]['id_category_ref'],
                        'shippingService' => $this->_shippingMethod[Configuration::get('EBAY_SHIPPING_CARRIER_ID')]['shippingService'],
                        'shippingCost' => Configuration::get('EBAY_SHIPPING_COST'),
                        'variationsList' => $variationsList,
                        'variations' => $variations,
                        'pictures' => $pictures,
                        'picturesMedium' => $picturesMedium,
                        'picturesLarge' => $picturesLarge,
                    );

                    // Fix hook update product
                    if (isset($this->context->employee) && $this->context->employee->id > 0 && isset($_POST['submitProductAttribute']) && isset($_POST['id_product_attribute']) && isset($_POST['attribute_mvt_quantity']) && isset($_POST['id_mvt_reason']))
     {
                         if (substr(_PS_VERSION_, 0, 3) == '1.3') 
                         {
                              $id_product_attribute_fix = (int) $_POST['id_product_attribute'];
                              $quantity_fix = (int) $_POST['attribute_quantity'];
                              if ($id_product_attribute_fix > 0 && $quantity_fix > 0 && isset($datas['variations'][$product->id . '-' . $id_product_attribute_fix]['quantity']))
                                   $datas['variations'][$product->id . '-' . $id_product_attribute_fix]['quantity'] = (int) $quantity_fix;
                         }
                         else 
                         {
                              $action = Db::getInstance()->getValue('SELECT `sign` FROM `' . _DB_PREFIX_ . 'stock_mvt_reason` WHERE `id_stock_mvt_reason` = ' . (int) $_POST['id_mvt_reason']);
                              $id_product_attribute_fix = (int) $_POST['id_product_attribute'];
                              $quantity_fix = (int) $_POST['attribute_mvt_quantity'];
                              if ($id_product_attribute_fix > 0 && $quantity_fix > 0 && isset($datas['variations'][$product->id . '-' . $id_product_attribute_fix]['quantity'])) {
                                   if ($action > 0)
                                        $datas['variations'][$product->id . '-' . $id_product_attribute_fix]['quantity'] += (int) $quantity_fix;
                                   if ($action < 0)
                                        $datas['variations'][$product->id . '-' . $id_product_attribute_fix]['quantity'] -= (int) $quantity_fix;
                              }
                         }
                    }

                    // Price Update
                    if (isset($p['noPriceUpdate']))
                         $datas['noPriceUpdate'] = $p['noPriceUpdate'];

                    // Save percent and price discount
                    if ($categoryDefaultCache[$product->id_category_default]['percent'] < 0) 
                    {
                         $datas['price_original'] = round($price_original, 2);
                         $datas['price_percent'] = round($categoryDefaultCache[$product->id_category_default]['percent']);
                    }


                    // Load eBay Description
                    $features = $product->getFrontFeatures((int) ($this->id_lang));
                    $featuresHtml = '';
                    if (isset($features))
                         foreach ($features as $f)
                              $featuresHtml .= '<b>' . $f['name'] . '</b> : ' . $f['value'] . '<br/>';
                    $datas['description'] = str_replace(
                            array('{DESCRIPTION_SHORT}', '{DESCRIPTION}', '{FEATURES}', '{EBAY_IDENTIFIER}', '{EBAY_SHOP}', '{SLOGAN}', '{PRODUCT_NAME}'), array($datas['description_short'], $datas['description'], $featuresHtml, Configuration::get('EBAY_IDENTIFIER'), Configuration::get('EBAY_SHOP'), '', $product->name), Configuration::get('EBAY_PRODUCT_TEMPLATE')
                    );

                    
                    // Export on eBay
                    if (count($datas['variations']) > 0) 
                    {
                         // Variations Case
                         if ($categoryDefaultCache[$product->id_category_default]['is_multi_sku'] == 1) 
                         {
                              // Load eBay Description
                              $datas['description'] = str_replace(
                                      array('{MAIN_IMAGE}', '{MEDIUM_IMAGE_1}', '{MEDIUM_IMAGE_2}', '{MEDIUM_IMAGE_3}', '{PRODUCT_PRICE}', '{PRODUCT_PRICE_DISCOUNT}'), array(
                                  (isset($datas['picturesLarge'][0]) ? '<img src="' . $datas['picturesLarge'][0] . '" class="bodyMainImageProductPrestashop" />' : ''),
                                  (isset($datas['picturesMedium'][1]) ? '<img src="' . $datas['picturesMedium'][1] . '" class="bodyFirstMediumImageProductPrestashop" />' : ''),
                                  (isset($datas['picturesMedium'][2]) ? '<img src="' . $datas['picturesMedium'][2] . '" class="bodyMediumImageProductPrestashop" />' : ''),
                                  (isset($datas['picturesMedium'][3]) ? '<img src="' . $datas['picturesMedium'][3] . '" class="bodyMediumImageProductPrestashop" />' : ''),
                                  '',
                                  ''
                                      ), $datas['description']
                              );

                              // Multi Sku case
                              // Check if product exists on eBay
                              $itemID = Db::getInstance()->getValue('SELECT `id_product_ref` FROM `' . _DB_PREFIX_ . 'ebay_product` WHERE `id_product` = ' . (int) $product->id);
                              if ($itemID) 
                              {
                                   // Update
                                   $datas['itemID'] = $itemID;
                                   if ($ebay->reviseFixedPriceItemMultiSku($datas))
                                        Db::getInstance()->autoExecute(_DB_PREFIX_ . 'ebay_product', array('date_upd' => pSQL($date)), 'UPDATE', '`id_product_ref` = ' . (int) $itemID);

                                   // if product not on eBay we add it
                                   if ($ebay->errorCode == 291) 
                                   {
                                        // We delete from DB and Add it on eBay
                                        Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'ebay_product` WHERE `id_product_ref` = \'' . pSQL($datas['itemID']) . '\'');
                                        $ebay->addFixedPriceItemMultiSku($datas);
                                        if ($ebay->itemID > 0)
                                             Db::getInstance()->autoExecute(_DB_PREFIX_ . 'ebay_product', array('id_country' => 8, 'id_product' => (int) $product->id, 'id_attribute' => 0, 'id_product_ref' => pSQL($ebay->itemID), 'date_add' => pSQL($date), 'date_upd' => pSQL($date)), 'INSERT');
                                   }
                              }
                              else 
                              {
                                   // Add
                                   $ebay->addFixedPriceItemMultiSku($datas);
                                   if ($ebay->itemID > 0)
                                        Db::getInstance()->autoExecute(_DB_PREFIX_ . 'ebay_product', array('id_country' => 8, 'id_product' => (int) $product->id, 'id_attribute' => 0, 'id_product_ref' => pSQL($ebay->itemID), 'date_add' => pSQL($date), 'date_upd' => pSQL($date)), 'INSERT');
                              }
                         }
                         else 
                         {
                              // No Multi Sku case
                              foreach ($datas['variations'] as $v) 
                              {
                                   $datasTmp = $datas;
                                   if (isset($v['pictures']) && count($v['pictures']) > 0)
                                        $datasTmp['pictures'] = $v['pictures'];
                                   if (isset($v['picturesMedium']) && count($v['picturesMedium']) > 0)
                                        $datasTmp['picturesMedium'] = $v['picturesMedium'];
                                   if (isset($v['picturesLarge']) && count($v['picturesLarge']) > 0)
                                        $datasTmp['picturesLarge'] = $v['picturesLarge'];
                                   foreach ($v['variations'] as $vLabel) 
                                   {
                                        $datasTmp['name'] .= ' ' . $vLabel['value'];
                                        $datasTmp['attributes'][$vLabel['name']] = $vLabel['value'];
                                   }
                                   $datasTmp['price'] = $v['price'];
                                   if (isset($v['price_original'])) 
                                   {
                                        $datasTmp['price_original'] = $v['price_original'];
                                        $datasTmp['price_percent'] = $v['price_percent'];
                                   }
                                   $datasTmp['quantity'] = $v['quantity'];
                                   $datasTmp['id_attribute'] = $v['id_attribute'];
                                   unset($datasTmp['variations']);
                                   unset($datasTmp['variationsList']);

                                   // Load eBay Description
                                   $datasTmp['description'] = str_replace(
                                           array('{MAIN_IMAGE}', '{MEDIUM_IMAGE_1}', '{MEDIUM_IMAGE_2}', '{MEDIUM_IMAGE_3}', '{PRODUCT_PRICE}', '{PRODUCT_PRICE_DISCOUNT}'), array(
                                       (isset($datasTmp['picturesLarge'][0]) ? '<img src="' . $datasTmp['picturesLarge'][0] . '" class="bodyMainImageProductPrestashop" />' : ''),
                                       (isset($datasTmp['picturesMedium'][1]) ? '<img src="' . $datasTmp['picturesMedium'][1] . '" class="bodyFirstMediumImageProductPrestashop" />' : ''),
                                       (isset($datasTmp['picturesMedium'][2]) ? '<img src="' . $datasTmp['picturesMedium'][2] . '" class="bodyMediumImageProductPrestashop" />' : ''),
                                       (isset($datasTmp['picturesMedium'][3]) ? '<img src="' . $datasTmp['picturesMedium'][3] . '" class="bodyMediumImageProductPrestashop" />' : ''),
                                       Tools::displayPrice($datasTmp['price']),
                                       (isset($datasTmp['price_original']) ? 'au lieu de <del>' . Tools::displayPrice($datasTmp['price_original']) . '</del> (remise de ' . round($datasTmp['price_percent']) . '%)' : ''),
                                           ), $datas['description']
                                   );

                                   $datasTmp['id_product'] = (int) $product->id . '-' . (int) $datasTmp['id_attribute'];

                                   // Check if product exists on eBay
                                   $itemID = Db::getInstance()->getValue('SELECT `id_product_ref` FROM `' . _DB_PREFIX_ . 'ebay_product` WHERE `id_product` = ' . (int) $product->id . ' AND `id_attribute` = ' . (int) $datasTmp['id_attribute']);
                                   if ($itemID) 
                                   {
                                        // Get Item ID
                                        $datasTmp['itemID'] = $itemID;

                                        // Delete or Update
                                        if ($datasTmp['quantity'] < 1) 
                                        {
                                             // Delete
                                             if ($ebay->endFixedPriceItem($datasTmp))
                                                  Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'ebay_product` WHERE `id_product_ref` = \'' . pSQL($datasTmp['itemID']) . '\'');
                                        }
                                        else 
                                        {
                                             // Update
                                             if ($ebay->reviseFixedPriceItem($datasTmp))
                                                  Db::getInstance()->autoExecute(_DB_PREFIX_ . 'ebay_product', array('date_upd' => pSQL($date)), 'UPDATE', '`id_product_ref` = ' . (int) $itemID);

                                             // if product not on eBay we add it
                                             if ($ebay->errorCode == 291) 
                                             {
                                                  // We delete from DB and Add it on eBay
                                                  Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'ebay_product` WHERE `id_product_ref` = \'' . pSQL($datasTmp['itemID']) . '\'');
                                                  $ebay->addFixedPriceItem($datasTmp);
                                                  if ($ebay->itemID > 0)
                                                       Db::getInstance()->autoExecute(_DB_PREFIX_ . 'ebay_product', array('id_country' => 8, 'id_product' => (int) $product->id, 'id_attribute' => (int) $datasTmp['id_attribute'], 'id_product_ref' => pSQL($ebay->itemID), 'date_add' => pSQL($date), 'date_upd' => pSQL($date)), 'INSERT');
                                             }
                                        }
                                   }
                                   else 
                                   {
                                        // Add
                                        $ebay->addFixedPriceItem($datasTmp);
                                        if ($ebay->itemID > 0)
                                             Db::getInstance()->autoExecute(_DB_PREFIX_ . 'ebay_product', array('id_country' => 8, 'id_product' => (int) $product->id, 'id_attribute' => (int) $datasTmp['id_attribute'], 'id_product_ref' => pSQL($ebay->itemID), 'date_add' => pSQL($date), 'date_upd' => pSQL($date)), 'INSERT');
                                   }
                              }
                         }
                    }
                    else 
                    {
                         // No variations case
                         // Load eBay Description
                         $datas['description'] = str_replace(
                                 array('{MAIN_IMAGE}', '{MEDIUM_IMAGE_1}', '{MEDIUM_IMAGE_2}', '{MEDIUM_IMAGE_3}', '{PRODUCT_PRICE}', '{PRODUCT_PRICE_DISCOUNT}'), array(
                             (isset($datas['picturesLarge'][0]) ? '<img src="' . $datas['picturesLarge'][0] . '" class="bodyMainImageProductPrestashop" />' : ''),
                             (isset($datas['picturesMedium'][1]) ? '<img src="' . $datas['picturesMedium'][1] . '" class="bodyFirstMediumImageProductPrestashop" />' : ''),
                             (isset($datas['picturesMedium'][2]) ? '<img src="' . $datas['picturesMedium'][2] . '" class="bodyMediumImageProductPrestashop" />' : ''),
                             (isset($datas['picturesMedium'][3]) ? '<img src="' . $datas['picturesMedium'][3] . '" class="bodyMediumImageProductPrestashop" />' : ''),
                             Tools::displayPrice($datas['price']),
                             (isset($datas['price_original']) ? 'au lieu de <del>' . Tools::displayPrice($datas['price_original']) . '</del> (remise de ' . round($datas['price_percent']) . '%)' : ''),
                                 ), $datas['description']
                         );
                         // Check if product exists on eBay
                         $itemID = Db::getInstance()->getValue('SELECT `id_product_ref` FROM `' . _DB_PREFIX_ . 'ebay_product` WHERE `id_product` = ' . (int) $product->id);
                      
                         if ($itemID) 
                         {
                              // Get Item ID
                              $datas['itemID'] = $itemID;

                              // Delete or Update
                              if ($datas['quantity'] < 1) 
                              {
                                   // Delete
                                   if ($ebay->endFixedPriceItem($datas))
                                        Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'ebay_product` WHERE `id_product_ref` = \'' . pSQL($datas['itemID']) . '\'');
                              }
                              else 
                              {
                                   // Update
                                   if ($ebay->reviseFixedPriceItem($datas))
                                        Db::getInstance()->autoExecute(_DB_PREFIX_ . 'ebay_product', array('date_upd' => pSQL($date)), 'UPDATE', '`id_product_ref` = ' . (int) $itemID);

                                   // if product not on eBay we add it
                                   if ($ebay->errorCode == 291) {
                                        // We delete from DB and Add it on eBay
                                        Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'ebay_product` WHERE `id_product_ref` = \'' . pSQL($datas['itemID']) . '\'');
                                        $ebay->addFixedPriceItem($datas);
                                        if ($ebay->itemID > 0)
                                             Db::getInstance()->autoExecute(_DB_PREFIX_ . 'ebay_product', array('id_country' => 8, 'id_product' => (int) $product->id, 'id_attribute' => 0, 'id_product_ref' => pSQL($ebay->itemID), 'date_add' => pSQL($date), 'date_upd' => pSQL($date)), 'INSERT');
                                   }
                              }
                         }
                         else 
                         {
                              // Add
                              $ebay->addFixedPriceItem($datas);
                              if ($ebay->itemID > 0)
                                   Db::getInstance()->autoExecute(_DB_PREFIX_ . 'ebay_product', array('id_country' => 8, 'id_product' => (int) $product->id, 'id_attribute' => 0, 'id_product_ref' => pSQL($ebay->itemID), 'date_add' => pSQL($date), 'date_upd' => pSQL($date)), 'INSERT');
                         }
                    }



                    // Check error
                    if (!empty($ebay->error)) 
                    {
                         $tab_error[md5($ebay->error)]['msg'] = $ebay->error;
                         if (!isset($tab_error[md5($ebay->error)]['products']))
                              $tab_error[md5($ebay->error)]['products'] = array();
                         if (count($tab_error[md5($ebay->error)]['products']) < 10)
                              $tab_error[md5($ebay->error)]['products'][] = $datas['name'];
                         if (count($tab_error[md5($ebay->error)]['products']) == 10)
                              $tab_error[md5($ebay->error)]['products'][] = '...';
                         $count_error++;
                    }
                    else
                         $count_success++;
                    $count++;
               }
          }

          if ($count_error > 0)
               file_put_contents(dirname(__FILE__) . '/log/syncError.php', '<?php $tab_error = ' . var_export($tab_error, true) . '; ?>');
     }

     /**
	  * Orders History Methods
      *
      **/
     private function _displayOrdersHistory() 
     {
          // Check if the module is configured
          if (!Configuration::get('EBAY_PAYPAL_EMAIL'))
               return '<p><b>' . $this->l('You have to configure "General Settings" tab before using this tab.') . '</b></p><br />';


          $dateLastImport = '-';
          if (file_exists(dirname(__FILE__) . '/log/orders.php'))
               include(dirname(__FILE__) . '/log/orders.php');
          $html = '<h2>' . $this->l('Last orders Importation') . ' :</h2><p><b>' . $dateLastImport . '</b></p><br /><br />
		          <h2>' . $this->l('Orders History') . ' :</h2>';

          if (isset($orderList) && count($orderList) > 0)
               foreach ($orderList as $order) 
               {
                    $html .= '<style>
						.orderImportTd1 {border-right:1px solid #000}
						.orderImportTd2 {border-right:1px solid #000;border-top:1px solid #000}
						.orderImportTd3 {border-top:1px solid #000}
					</style>
					<p>
					<b>' . $this->l('Order Ref eBay') . ' :</b> ' . $order['id_order_ref'] . '<br />
					<b>' . $this->l('Id Order Seller') . ' :</b> ' . $order['id_order_seller'] . '<br />
					<b>' . $this->l('Amount') . ' :</b> ' . $order['amount'] . '<br />
					<b>' . $this->l('Status') . ' :</b> ' . $order['status'] . '<br />
					<b>' . $this->l('Date') . ' :</b> ' . $order['date'] . '<br />
					<b>' . $this->l('E-mail') . ' :</b> ' . $order['email'] . '<br />
					<b>' . $this->l('Products') . ' :</b><br />';
                    if (isset($order['product_list']) && count($order['product_list']) > 0) 
                    {
                         $html .= '<table border="0" cellpadding="4" cellspacing="0"><tr>
						<td class="orderImportTd1"><b>' . $this->l('Id Product') . '</b></td>
						<td class="orderImportTd1"><b>' . $this->l('Id Product Attribute') . '</b></td>
						<td class="orderImportTd1"><b>' . $this->l('Quantity') . '</b></td><td><b>' . $this->l('Price') . '</b></td></tr>';
                         foreach ($order['product_list'] as $product)
                              $html .= '<tr><td class="orderImportTd2">' . $product['id_product'] . '</td>
							  <td class="orderImportTd2">' . $product['id_product'] . '</td>
							  <td class="orderImportTd2">' . $product['quantity'] . '</td>
							  <td class="orderImportTd3">' . $product['price'] . '</td></tr>';
                         $html .= '</table>';
                    }
                    if (isset($order['errors']) && count($order['errors']) > 0) 
                    {
                         $html .= '<b>' . $this->l('Status Import') . ' :</b> KO<br />';
                         $html .= '<b>' . $this->l('Failure details') . ' :</b><br />';
                         foreach ($order['errors'] as $error)
                              $html .= $error . 't<br />';
                    }
                    else
                         $html .= '<b>' . $this->l('Status Import') . ' :</b> OK';
                    $html .= '</p><br />';
               }


          return $html;
     }

     /**
      * Help Config Methods
      *
      **/
     private function _displayHelp() 
     {
          $helpFile = dirname(__FILE__) . '/help/help-' . strtolower($this->country->iso_code) . '.html';
          if (!file_exists($helpFile))
               $helpFile = dirname(__FILE__) . '/help/help-fr.html';

          return file_get_contents($helpFile);
     }

     /**
	  * Tools Methods
	  *
	  * Set country + sends back true or false
      **/
     private function checkCountry() 
     {
          if (!is_object($this->country))
               $this->country = new Country((int) Configuration::get('EBAY_COUNTRY_DEFAULT'));

          if (strtolower($this->country->iso_code) == 'fr' || strtolower($this->country->iso_code) == 'it')
               return true;

          $this->country = new Country((int) Configuration::get('PS_COUNTRY_DEFAULT'));
          if (strtolower($this->country->iso_code) == 'fr' || strtolower($this->country->iso_code) == 'it') 
          {
               $this->setConfiguration('EBAY_COUNTRY_DEFAULT', $this->country->id);
               return true;
          }

          return false;
     }

     private function isVersionOneDotFive() 
     {
          return version_compare(_PS_VERSION_, '1.5', '>');
     }

     private function getAlert() 
     {
          // Test alert
          $alert = array();
          if (!Configuration::get('EBAY_API_TOKEN'))
               $alert['registration'] = 1;
          if (!ini_get('allow_url_fopen'))
               $alert['allowurlfopen'] = 1;
          if (!extension_loaded('curl'))
               $alert['curl'] = 1;

          //Search if user is a store owner
          $ebay = new eBayRequest();
          $ebay->username = Configuration::get('EBAY_API_USERNAME');//$this->context->cookie->eBayUsername;
          $userProfile = $ebay->getUserProfile();

          if ($userProfile[0]['SellerBusinessType'][0] != 'Commercial')
               $alert['SellerBusinessType'] = 1;
          if ($userProfile[0]['StoreName'][0] == '' && $userProfile[0]['StoreUrl'][0] == '')
               $alert['StoreOwner'] = 1;

          return $alert;
     }

     //Get alert to see if some multi variation product on PrestaShop were added to a non multi sku categorie on ebay
	private function getAlertCategories()
	{
		$alert = '';
		$sql_getCatNonMultiSku = 
			"SELECT * FROM "._DB_PREFIX_."ebay_category_configuration AS ecc
			INNER JOIN "._DB_PREFIX_."ebay_category AS ec ON ecc.id_ebay_category = ec.id_ebay_category";
		
		$CatNonMultiSku = Db::getInstance()->ExecuteS($sql_getCatNonMultiSku);
		$catWithProblem = array();
		foreach ($CatNonMultiSku as $cat) 
		{
			if($cat['is_multi_sku'] != 1 && $this->findIfCategoryParentIsMultiSku($cat['id_category_ref']) != 1)
			{
				$categorie = new Category($cat['id_category']);
				$products = $categorie->getProductsWs($this->id_lang, 0, 300);
				$catProblem = 0;
				foreach ($products as $productArray)
				{
					$product = new Product($productArray['id']);
					$combinations = $product->getAttributeCombinaisons($this->id_lang);
					if(count($combinations) > 0 && $catProblem == 0)
					{
						$catWithProblem[] = $cat['name'];
						$catProblem = 1;
					}
				}
			}
		}
	
		$var = '';
		$j = 0;
		foreach ($catWithProblem as $cat) 
		{
			if($j != 0)
				$var .= ', ';
			
			$var .= $cat;
			$j++;
		}
		
		if(count($catWithProblem) > 0)
		{
			if(count($catWithProblem == 1))
				$alert = '<b>'.$this->l('You have chosen eBay category : ').$var.$this->l(' which does not support multivariation products. Each variation of a product will generate a new product in eBay').'</b>';
			else
				$alert = '<b>'.$this->l('You have chosen eBay categories : ').$var.$this->l(' which do not support multivariation products. Each variation of a product will generate a new product in eBay').'</b>';
		}
		
		return $alert;
	}
      

     private function setConfiguration($configName, $configValue, $html = false) 
     {
          if ($this->isVersionOneDotFive())
               return Configuration::updateValue($configName, $configValue, $html, 0, 0);
          else
               return Configuration::updateValue($configName, $configValue, $html);
     }

     private function getContextShop() 
     {
          $contextShop = NULL;
          if ($this->isVersionOneDotFive()) 
          {
               $contextShop[] = Shop::getContext();
               if ($contextShop[0] == Shop::CONTEXT_SHOP)
                    array_push($contextShop, Shop::getContextShopID());
               else if ($contextShop[0] == Shop::CONTEXT_GROUP)
                    array_push($contextShop, Shop::getContextShopGroupID());
               else
                    array_push($contextShop, NULL);
          }
          return $contextShop;
     }

     /**
      * $newContextShop = array
      * @param int $type Shop::CONTEXT_ALL | Shop::CONTEXT_GROUP | Shop::CONTEXT_SHOP
      * @param int $id ID shop if CONTEXT_SHOP or id shop group if CONTEXT_GROUP
      *
      **/
     private function setContextShop($newContextShop = NULL) 
     {
          if ($this->isVersionOneDotFive()) 
          {
               if ($newContextShop == NULL)
                    Shop::setContext(Shop::CONTEXT_SHOP, Configuration::get('PS_SHOP_DEFAULT'));
               else
                    Shop::setContext($newContextShop[0], $newContextShop[1]);
          }
     }

     private function addSqlRestrictionOnLang($alias) 
     {
          if ($this->isVersionOneDotFive())
               Shop::addSqlRestrictionOnLang($alias);
     }

}

