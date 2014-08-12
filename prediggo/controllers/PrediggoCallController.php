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

require_once(_PS_MODULE_DIR_.'prediggo/classes/PrediggoConfig.php');
require_once(_PS_MODULE_DIR_.'prediggo/classes/PrediggoCall.php');

class PrediggoCallController
{
    /** @var PrediggoConfig Object PrediggoConfig */
    private $oPrediggoConfig;
    /** @var array list of the page where prediggo will be displayed */
    private $aPagesAccessible;
    /** @var string current page name */
    protected $sPageName;
    /** @var PrediggoCall Object PrediggoCall */
    private $oPrediggoCall;
    /** @var string path of the log repository */
    private $sRepositoryPath;
    /** @var string current Hook name */
    private $sHookName;

    /**
     * Initialise the object variables
     */
    public function __construct()
    {
        $this->oPrediggoConfig = new PrediggoConfig(Context::getContext());

        $this->sRepositoryPath = _PS_MODULE_DIR_.'prediggo/logs/';

        $this->setPagesAccessible();

        $this->setPageName();
    }

    /**
     * Execute a notification to prediggo
     *
     * @param string $sType Type of the notification
     * @param array $params list of specific parameters
     */
    public function notifyPrediggo($sType, $params)
    {
        if(!$this->oPrediggoConfig->server_id_checked)
            return false;

        $this->oPrediggoCall = new PrediggoCall($this->oPrediggoConfig->web_site_id, $this->oPrediggoConfig->server_url_recommendations);

        switch($sType)
        {
            case 'user' :
                $this->oPrediggoCall->setUserRegistered($params);
                break;

            case 'product' :
                $this->oPrediggoCall->setProductNotification($params);
                break;

            default : break;
        }

        if($this->oPrediggoConfig->logs_reco_file_generation)
            $this->setNotificationsLogFile($sType, $this->oPrediggoCall->getLogs());
    }

    /**
     * Get the list of recommendations by hook
     *
     * @param string $sHookName Name of the hook
     * @param array $params list of specific parameters
     * @return array $aRecommendations list of products
     */
    public function getListOfRecommendations($sHookName, $params, $iVariantId)
    {
        if(!$this->oPrediggoConfig->server_id_checked
            || !$this->isPageAccessible())
            return false;

        $this->oPrediggoCall = new PrediggoCall($this->oPrediggoConfig->web_site_id, $this->oPrediggoConfig->server_url_recommendations);

        $aRecommendations = false;

        switch($this->sPageName)
        {

            case 'index' :
                $params['nb_items'] = (int)$this->oPrediggoConfig->home_nb_items;
                $params['block_title'] = pSQL($this->oPrediggoConfig->home_block_title[(int)$params['cookie']->id_lang]);
                $aRecommendations = $this->oPrediggoCall->getNewLandingPage($params,0);
                break;

            case 'product' :
                $params['id_product'] = (int)Tools::getValue('id_product');
                $aRecommendations = $this->oPrediggoCall->getProductRecommendations($params,$iVariantId);
            break;

            case '404' :
                $params['nb_items'] = (int)$this->oPrediggoConfig->error_nb_items;
                $params['block_title'] = pSQL($this->oPrediggoConfig->error_block_title[(int)$params['cookie']->id_lang]);
                $aRecommendations = $this->oPrediggoCall->getNewLandingPage($params,0);
                break;

            case 'category' :
                $params['category'] = new Category((int)Tools::getValue('id_category'), (int)$params['cookie']->id_lang);
                $aRecommendations = $this->oPrediggoCall->getCategoryRecommendations($params,$iVariantId);
                break;

            case 'blocklayered' :
                $params['nb_items'] = (int)$this->oPrediggoConfig->blocklayered_nb_items;
                $params['block_title'] = pSQL($this->oPrediggoConfig->blocklayered_block_title[(int)$params['cookie']->id_lang]);
                $params['category'] = new Category((int)$params['id_category_layered'], (int)$params['cookie']->id_lang);
                $iVariantId = (int)$this->oPrediggoConfig->category_variant_ID;
                $aRecommendations = $this->oPrediggoCall->getBlockLayeredRecommendations($params,$iVariantId);
                break;

            case 'my-account' :
            case 'addresses' :
            case 'history' :
            case 'order-return' :
                $params['nb_items'] = (int)$this->oPrediggoConfig->customer_nb_items;
                $params['block_title'] = pSQL($this->oPrediggoConfig->customer_block_title[(int)$params['cookie']->id_lang]);
                $aRecommendations = $this->oPrediggoCall->getCustomerRecommendations($params);
                break;

            case 'order' :
                $params['nb_items'] = (int)$this->oPrediggoConfig->cart_nb_items;
                $params['block_title'] = pSQL($this->oPrediggoConfig->cart_block_title[(int)$params['cookie']->id_lang]);
                $aRecommendations = $this->oPrediggoCall->getCartRecommendations($params);
                break;

            case 'order-opc' :
                $params['nb_items'] = (int)$this->oPrediggoConfig->cart_nb_items;
                $params['block_title'] = pSQL($this->oPrediggoConfig->cart_block_title[(int)$params['cookie']->id_lang]);
                $aRecommendations = $this->oPrediggoCall->getCartRecommendations($params);
                break;

            case 'best-sales' :
                $params['nb_items'] = (int)$this->oPrediggoConfig->best_sales_nb_items;
                $params['block_title'] = pSQL($this->oPrediggoConfig->best_sales_block_title[(int)$params['cookie']->id_lang]);
                $aRecommendations = $this->oPrediggoCall->getBestSalesRecommendations($params);
                break;

            default : break;
        }

        if($this->oPrediggoConfig->logs_reco_file_generation)
            $this->setRecommendationsLogFile($sHookName, $this->oPrediggoCall->getLogs());

        return $aRecommendations;
    }

    /**
     * Function chooseVariantId : Choose the good variant ID
     *
     * @iVariantId return the variant ID
     */
    public function chooseVariantId($sHookName){

        switch($sHookName)
        {
            case 'displayLeftColumn' :
                    $iVariantId = (int)$this->oPrediggoConfig->hook_left_column;
                break;

            case 'displayRightColumn' :
                    $iVariantId = (int)$this->oPrediggoConfig->hook_right_column;
                break;

            case 'displayFooter' :
                    $iVariantId = (int)$this->oPrediggoConfig->hook_footer;
                break;

            case 'displayHome' :
                    $iVariantId = (int)$this->oPrediggoConfig->hook_home;
                break;

            case 'displayFooterProduct' :
                    $iVariantId = (int)$this->oPrediggoConfig->hook_footer_product;
                break;

            case 'displayLeftColumnProduct' :
                    $iVariantId = (int)$this->oPrediggoConfig->hook_left_column_product;
                break;

            case 'displayRightColumnProduct' :
                    $iVariantId = (int)$this->oPrediggoConfig->hook_right_column_product;
                break;

            case 'displayShoppingCartFooter' :
                    $iVariantId = (int)$this->oPrediggoConfig->hook_shopping_cart_footer;
                break;

            case 'displayShoppingCart' :
                    $iVariantId = (int)$this->oPrediggoConfig->hook_shopping_cart;
                break;

            case 'displayOrderDetail' :
                    $iVariantId = (int)$this->oPrediggoConfig->hook_order_detail;
                break;

            case 'displayProductTab' :
                    $iVariantId = (int)$this->oPrediggoConfig->hook_product_tab;
                break;

            case 'displayBeforeCarrier' :
                    $iVariantId = (int)$this->oPrediggoConfig->hook_before_carrier;
                break;

            case 'displayCarrierList' :
                    $iVariantId = (int)$this->oPrediggoConfig->hook_carrier_list;
                break;

            default : break;
        }

        return $iVariantId;
    }

    /**
     * Set the array $aPagesAccessible with the page accessible
     */
    public function setPagesAccessible()
    {
        $this->aPagesAccessible = array();

        if($this->oPrediggoConfig->home_recommendations)
            $this->aPagesAccessible[] = 'index';
        if($this->oPrediggoConfig->error_recommendations)
            $this->aPagesAccessible[] = '404';
        if($this->oPrediggoConfig->product_recommendations)
            $this->aPagesAccessible[] = 'product';
        if($this->oPrediggoConfig->category_recommendations)
            $this->aPagesAccessible[] = 'category';
        if($this->oPrediggoConfig->customer_recommendations)
        {
            $this->aPagesAccessible[] = 'my-account';
            $this->aPagesAccessible[] = 'addresses';
            $this->aPagesAccessible[] = 'history';
            $this->aPagesAccessible[] = 'order-return';
        }
        if($this->oPrediggoConfig->cart_recommendations)
        {
            $this->aPagesAccessible[] = 'order';
            $this->aPagesAccessible[] = 'order-opc';
        }
        if($this->oPrediggoConfig->best_sales_recommendations)
            $this->aPagesAccessible[] = 'best-sales';
        if($this->oPrediggoConfig->blocklayered_recommendations)
            $this->aPagesAccessible[] = 'blocklayered';
    }

    /**
     * Set the current page name and store it to the object var $sPageName
     * You must update this function base on your URL rewrite policy (you must write it in the PrediggoCallControllerOverride)
     * The page name is then used to select which type of recommendation bloc to use (GetItemReco,...)
     */
    public function setPageName()
    {
        /*$pos = strpos($_SERVER['REQUEST_URI'],'controller=category');
        if ($pos==true || $pos>=1)
        {
            $this->sPageName ='category';
            return true;
        }
        $pos = strpos($_SERVER['REQUEST_URI'],'controller=product');
        if ($pos==true || $pos>=1)
        {
            $this->sPageName ='product';
            return true;
        }
        $pos = strpos($_SERVER['REQUEST_URI'],'controller=order');
        if ($pos==true || $pos>=1)
        {
            $this->sPageName ='order';
            return true;
        }
        $this->sPageName = basename(preg_replace('/\.php$/', '', $_SERVER['PHP_SELF']));
        if (preg_match('#^'.__PS_BASE_URI__.'modules/([a-zA-Z0-9_-]+?)/(.*)$#', $_SERVER['REQUEST_URI'], $m))
            $this->sPageName = 'module-'.$m[1].'-'.str_replace(array('.php', '/'), array('', '-'), $m[2]);
        */
    }

    /**
     * Update the current $sPageName
     */
    public function _setPageName($sPageName)
    {
         //$this->sPageName = $sPageName;
    }

    /**
     * You must update this function base on your module of pop-in cart if you have one
     */
    public function popInCart()
    {

    }

    public function variantId(){

    }

    /**
     * Check if the current page is accessible (prediggo blocks can be displayed ?)
     * @return bool is accessible or not
     */
    public function isPageAccessible()
    {
        return in_array($this->sPageName, $this->aPagesAccessible);
    }

    /**
     * Add the new logs list to the recommendations log file
     *
     * @param string $sHookName Name of the hook
     * @param array $aLogs list of logs
     */
    private function setRecommendationsLogFile($sHookName, $aLogs)
    {
        if(!count($aLogs))
            return false;

        $sEntityLogFileName = $this->sRepositoryPath.'log_fo-'.$this->sPageName.'.txt';
        $aLogs[0] .= ' {'.$sHookName.'}';
        if($handle = fopen($sEntityLogFileName, 'a'))
        {
            foreach($aLogs as $sLog)
                fwrite($handle, $sLog."\n");
            fclose($handle);
        }
    }

    /**
     * Add the new logs list to the notifications log file
     *
     * @param string $sHookName Name of the hook
     * @param array $aLogs list of logs
     */
    private function setNotificationsLogFile($sName, $aLogs)
    {
        $sEntityLogFileName = $this->sRepositoryPath.'log_notification-'.$sName.'.txt';
        $aLogs[0] .= ' {'.$sName.'}';
        if($handle = fopen($sEntityLogFileName, 'a'))
        {
            foreach($aLogs as $sLog)
                fwrite($handle, $sLog."\n");
            fclose($handle);
        }
    }

    /**
     * Get the current page name
     */
    public function getPageName()
    {
        return $this->sPageName;
    }

    /**
     * Check the client web site id
     */
    public function checkWebSiteId()
    {
        // Check if default web_site_id
        if($this->oPrediggoConfig->web_site_id == 'WineDemo_Fake_Shop_ID_123456789')
            return false;
        $this->oPrediggoConfig = new PrediggoConfig(Context::getContext());
        $this->oPrediggoCall = new PrediggoCall($this->oPrediggoConfig->web_site_id, $this->oPrediggoConfig->server_url_recommendations);
        return $this->oPrediggoCall->checkWebSiteId();
    }

    /**
     * Check the client web site id
     */

    public function checkLicence()
    {
        $this->oPrediggoConfig = new PrediggoConfig(Context::getContext());
        $this->oPrediggoCall = new PrediggoCall();
        $url = $this->oPrediggoConfig->server_url_check.'/'.$this->oPrediggoConfig->shop_name.'/'.$this->oPrediggoConfig->token_id.'.file';
        return $this->oPrediggoCall->checkLicence($url);
    }
}