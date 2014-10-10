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
    protected $oPrediggoConfig;
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
		//echo '<br><br>ENTERING NOTIFY PRDIGGO OF CLICK<br><br>'	;
        if(!$this->oPrediggoConfig->web_site_id_checked)
            return false;

		//echo '<br><br>NOTIFY PRDIGGO OF CLICK<br><br>'	;
			
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

        if($this->oPrediggoConfig->logs_generation)
            $this->setNotificationsLogFile($sType, $this->oPrediggoCall->getLogs());
    }

   
    
	/**
	* Make the home page recommendation ONLY if the hook matches
	*/
	public function homePageRecoIfHookMatch($sHookName, $params,$oPrediggoCall)
	{
		$aRecommendations = array();

		if ( (boolean)$this->oPrediggoConfig->home_0_activated and strcmp($this->oPrediggoConfig->home_0_hook_name,$sHookName)==0)
		{
			//echo 'Home Page Block #0 is active => computing recommendation<BR>';
			$params['nb_items'] = (int)$this->oPrediggoConfig->home_0_nb_items;
			$params['block_title'] = pSQL($this->oPrediggoConfig->home_0_block_label[(int)$params['cookie']->id_lang]);
			$params['variant_id'] = (int)$this->oPrediggoConfig->home_0_variant_id;
			$params['template_name'] = $this->oPrediggoConfig->home_0_template_name;
			
			$reco_block0 = $oPrediggoCall->getHomePageRecommendation($params);
			$reco_block0['block_title'] =  $params['block_title'];
			$reco_block0['block_template'] =  $params['template_name'];
			
			if($this->oPrediggoConfig->logs_generation)
				$this->setRecommendationsLogFile($sHookName, $oPrediggoCall->getLogs());
				
			array_push($aRecommendations,$reco_block0);
		}
		
		if ( (boolean)$this->oPrediggoConfig->home_1_activated and strcmp($this->oPrediggoConfig->home_1_hook_name,$sHookName)==0)
		{
			//echo 'Home Page Block #1 is active => computing recommendation<BR>';
			$params['nb_items'] = (int)$this->oPrediggoConfig->home_1_nb_items;
			$params['block_title'] = pSQL($this->oPrediggoConfig->home_1_block_label[(int)$params['cookie']->id_lang]);
			$params['variant_id'] = (int)$this->oPrediggoConfig->home_1_variant_id;
			$params['template_name'] = $this->oPrediggoConfig->home_1_template_name;
			
			$reco_block1 = $oPrediggoCall->getHomePageRecommendation($params);
			$reco_block1['block_title'] =  $params['block_title'];
			$reco_block1['block_template'] =  $params['template_name'];
			
			if($this->oPrediggoConfig->logs_generation)
				$this->setRecommendationsLogFile($sHookName, $oPrediggoCall->getLogs());
				
			array_push($aRecommendations,$reco_block1);
		}
		
		return $aRecommendations;
	}
	
	/**
	* Make the all page recommendation ONLY if the hook matches
	*/
	public function allPageRecoIfHookMatch($sHookName, $params,$oPrediggoCall)
	{
		$aRecommendations = array();
		
		if ( (boolean)$this->oPrediggoConfig->allpage_0_activated and strcmp($this->oPrediggoConfig->allpage_0_hook_name,$sHookName)==0)
		{
			//echo 'All Page Block #0 is active => computing recommendation<BR>';
			$params['nb_items'] = (int)$this->oPrediggoConfig->allpage_0_nb_items;
            $params['block_title'] = pSQL($this->oPrediggoConfig->allpage_0_block_label[(int)$params['cookie']->id_lang]);
			$params['variant_id'] = (int)$this->oPrediggoConfig->allpage_0_variant_id;
			$params['template_name'] = $this->oPrediggoConfig->allpage_0_template_name;
			
			$reco_block0 = $oPrediggoCall->getHomePageRecommendation($params);
			$reco_block0['block_title'] =  $params['block_title'];
			$reco_block0['block_template'] =  $params['template_name'];
			
			if($this->oPrediggoConfig->logs_generation)
				$this->setRecommendationsLogFile($sHookName, $oPrediggoCall->getLogs());
				
			array_push($aRecommendations,$reco_block0);
		}
		
		if ( (boolean)$this->oPrediggoConfig->allpage_1_activated and strcmp($this->oPrediggoConfig->allpage_1_hook_name,$sHookName)==0)
		{
			//echo 'All Page Block #1 is active => computing recommendation<BR>';
			$params['nb_items'] = (int)$this->oPrediggoConfig->allpage_1_nb_items;
            $params['block_title'] = pSQL($this->oPrediggoConfig->allpage_1_block_label[(int)$params['cookie']->id_lang]);
			$params['variant_id'] = (int)$this->oPrediggoConfig->allpage_1_variant_id;
			$params['template_name'] = $this->oPrediggoConfig->allpage_1_template_name;
			
			$reco_block1 = $oPrediggoCall->getHomePageRecommendation($params);
			$reco_block1['block_title'] =  $params['block_title'];
			$reco_block1['block_template'] =  $params['template_name'];
			
			if($this->oPrediggoConfig->logs_generation)
				$this->setRecommendationsLogFile($sHookName, $oPrediggoCall->getLogs());
			
			array_push($aRecommendations,$reco_block1);
		}
		
		if ( (boolean)$this->oPrediggoConfig->allpage_2_activated and strcmp($this->oPrediggoConfig->allpage_2_hook_name,$sHookName)==0)
		{
			//echo 'All Page Block #2 is active => computing recommendation<BR>';
			$params['nb_items'] = (int)$this->oPrediggoConfig->allpage_2_nb_items;
            $params['block_title'] = pSQL($this->oPrediggoConfig->allpage_2_block_label[(int)$params['cookie']->id_lang]);
			$params['variant_id'] = (int)$this->oPrediggoConfig->allpage_2_variant_id;
			$params['template_name'] = $this->oPrediggoConfig->allpage_2_template_name;
			
			$reco_block2 = $oPrediggoCall->getHomePageRecommendation($params);
			$reco_block2['block_title'] =  $params['block_title'];
			$reco_block2['block_template'] =  $params['template_name'];
			
			if($this->oPrediggoConfig->logs_generation)
				$this->setRecommendationsLogFile($sHookName, $oPrediggoCall->getLogs());
				
			array_push($aRecommendations,$reco_block2);
		}
		
		return $aRecommendations;
	}
	
	/**
	* Make the Product page recommendation ONLY if the hook matches
	*/
	public function productPageRecoIfHookMatch($sHookName, $params,$oPrediggoCall)
	{
		$aRecommendations = array();
		if ( (boolean)$this->oPrediggoConfig->productpage_0_activated and strcmp($this->oPrediggoConfig->productpage_0_hook_name,$sHookName)==0)
		{
			//echo 'Product Page Block #0 is active => computing recommendation<BR>';
			$params['nb_items'] = (int)$this->oPrediggoConfig->productpage_0_nb_items;
            $params['block_title'] = pSQL($this->oPrediggoConfig->productpage_0_block_label[(int)$params['cookie']->id_lang]);
			$params['variant_id'] = (int)$this->oPrediggoConfig->productpage_0_variant_id;
			$params['template_name'] = $this->oPrediggoConfig->productpage_0_template_name;
			
			$reco_block0 = $oPrediggoCall->getProductPageRecommendation($params);
			$reco_block0['block_title'] =  $params['block_title'];
			$reco_block0['block_template'] =  $params['template_name'];
			
			if($this->oPrediggoConfig->logs_generation)
				$this->setRecommendationsLogFile($sHookName, $oPrediggoCall->getLogs());
				
			array_push($aRecommendations,$reco_block0);
		}
		
		if ( (boolean)$this->oPrediggoConfig->productpage_1_activated and strcmp($this->oPrediggoConfig->productpage_1_hook_name,$sHookName)==0)
		{
			//echo 'Product Page Block #1 is active => computing recommendation<BR>';
			$params['nb_items'] = (int)$this->oPrediggoConfig->productpage_1_nb_items;
            $params['block_title'] = pSQL($this->oPrediggoConfig->productpage_1_block_label[(int)$params['cookie']->id_lang]);
			$params['variant_id'] = (int)$this->oPrediggoConfig->productpage_1_variant_id;
			$params['template_name'] = $this->oPrediggoConfig->productpage_1_template_name;
			
			//echo 'Template name'.$params['template_name'].'<BR>';
			$reco_block1 = $oPrediggoCall->getProductPageRecommendation($params);
			$reco_block1['block_title'] =  $params['block_title'];
			$reco_block1['block_template'] =  $params['template_name'];
			
			if($this->oPrediggoConfig->logs_generation)
				$this->setRecommendationsLogFile($sHookName, $oPrediggoCall->getLogs());
				
			array_push($aRecommendations,$reco_block1);
		}
		
		if ( (boolean)$this->oPrediggoConfig->productpage_2_activated and strcmp($this->oPrediggoConfig->productpage_2_hook_name,$sHookName)==0)
		{
			//echo 'Product Page Block #2 is active => computing recommendation<BR>';
			$params['nb_items'] = (int)$this->oPrediggoConfig->productpage_2_nb_items;
            $params['block_title'] = pSQL($this->oPrediggoConfig->productpage_2_block_label[(int)$params['cookie']->id_lang]);
			$params['variant_id'] = (int)$this->oPrediggoConfig->productpage_2_variant_id;
			$params['template_name'] = $this->oPrediggoConfig->productpage_2_template_name;
			
			$reco_block2 = $oPrediggoCall->getProductPageRecommendation($params);
			$reco_block2['block_title'] =  $params['block_title'];
			$reco_block2['block_template'] =  $params['template_name'];
			
			if($this->oPrediggoConfig->logs_generation)
				$this->setRecommendationsLogFile($sHookName, $oPrediggoCall->getLogs());
				
			array_push($aRecommendations,$reco_block2);
		}
		
		return $aRecommendations;
	}
	
	/**
	* Make the basket page recommendation ONLY if the hook matches
	*/
	public function basketPageRecoIfHookMatch($sHookName, $params,$oPrediggoCall)
	{
		$aRecommendations = array();
		
		
		if ( (boolean)$this->oPrediggoConfig->basket_0_activated and strcmp($this->oPrediggoConfig->basket_0_hook_name,$sHookName)==0)
		{
			//echo 'Home Page Block #0 is active => computing recommendation<BR>';
			$params['nb_items'] = (int)$this->oPrediggoConfig->basket_0_nb_items;
            $params['block_title'] = pSQL($this->oPrediggoConfig->basket_0_block_label[(int)$params['cookie']->id_lang]);
			$params['variant_id'] = (int)$this->oPrediggoConfig->basket_0_variant_id;
			$params['template_name'] = $this->oPrediggoConfig->basket_0_template_name;
			
			$reco_block0 = $oPrediggoCall->getBasketPageRecommendation($params);
			$reco_block0['block_title'] =  $params['block_title'];
			$reco_block0['block_template'] =  $params['template_name'];
			
			if($this->oPrediggoConfig->logs_generation)
				$this->setRecommendationsLogFile($sHookName, $oPrediggoCall->getLogs());
				
			array_push($aRecommendations,$reco_block0);
		}
		
		if ( (boolean)$this->oPrediggoConfig->basket_1_activated and strcmp($this->oPrediggoConfig->basket_1_hook_name,$sHookName)==0)
		{
			//echo 'Basket Page Block #1 is active => computing recommendation<BR>';
			$params['nb_items'] = (int)$this->oPrediggoConfig->basket_1_nb_items;
            $params['block_title'] = pSQL($this->oPrediggoConfig->basket_1_block_label[(int)$params['cookie']->id_lang]);
			$params['variant_id'] = (int)$this->oPrediggoConfig->basket_1_variant_id;
			$params['template_name'] = $this->oPrediggoConfig->basket_1_template_name;
			
			$reco_block1 = $oPrediggoCall->getBasketPageRecommendation($params);
			$reco_block1['block_title'] =  $params['block_title'];
			$reco_block1['block_template'] =  $params['template_name'];
			
			if($this->oPrediggoConfig->logs_generation)
				$this->setRecommendationsLogFile($sHookName, $oPrediggoCall->getLogs());
				
			array_push($aRecommendations,$reco_block1);
		}
		

		if ( (boolean)$this->oPrediggoConfig->basket_2_activated and strcmp($this->oPrediggoConfig->basket_2_hook_name,$sHookName)==0)
		{
			//echo 'Home Page Block #2 is active => computing recommendation<BR>';
			$params['nb_items'] = (int)$this->oPrediggoConfig->basket_2_nb_items;
            $params['block_title'] = pSQL($this->oPrediggoConfig->basket_2_block_label[(int)$params['cookie']->id_lang]);
			$params['variant_id'] = (int)$this->oPrediggoConfig->basket_2_variant_id;
			$params['template_name'] = $this->oPrediggoConfig->basket_2_template_name;
			
			$reco_block2 = $oPrediggoCall->getBasketPageRecommendation($params);
			$reco_block2['block_title'] =  $params['block_title'];
			$reco_block2['block_template'] =  $params['template_name'];
			
			if($this->oPrediggoConfig->logs_generation)
				$this->setRecommendationsLogFile($sHookName, $oPrediggoCall->getLogs());
				
			array_push($aRecommendations,$reco_block2);
		}
		
		if ( (boolean)$this->oPrediggoConfig->basket_3_activated and strcmp($this->oPrediggoConfig->basket_3_hook_name,$sHookName)==0)
		{
			//echo 'Basket Page Block #3 is active => computing recommendation<BR>';
			$params['nb_items'] = (int)$this->oPrediggoConfig->basket_3_nb_items;
            $params['block_title'] = pSQL($this->oPrediggoConfig->basket_3_block_label[(int)$params['cookie']->id_lang]);
			$params['variant_id'] = (int)$this->oPrediggoConfig->basket_3_variant_id;
			$params['template_name'] = $this->oPrediggoConfig->basket_3_template_name;
			
			$reco_block3 = $oPrediggoCall->getBasketPageRecommendation($params);
			$reco_block3['block_title'] =  $params['block_title'];
			$reco_block3['block_template'] =  $params['template_name'];
			
			if($this->oPrediggoConfig->logs_generation)
				$this->setRecommendationsLogFile($sHookName, $oPrediggoCall->getLogs());
				
			array_push($aRecommendations,$reco_block3);
		}
		
		if ( (boolean)$this->oPrediggoConfig->basket_4_activated and strcmp($this->oPrediggoConfig->basket_4_hook_name,$sHookName)==0)
		{
			//echo 'Basket Page Block #4 is active => computing recommendation<BR>';
			$params['nb_items'] = (int)$this->oPrediggoConfig->basket_4_nb_items;
            $params['block_title'] = pSQL($this->oPrediggoConfig->basket_4_block_label[(int)$params['cookie']->id_lang]);
			$params['variant_id'] = (int)$this->oPrediggoConfig->basket_4_variant_id;
			$params['template_name'] = $this->oPrediggoConfig->basket_4_template_name;
			
			$reco_block4 = $oPrediggoCall->getBasketPageRecommendation($params);
			$reco_block4['block_title'] =  $params['block_title'];
			$reco_block4['block_template'] =  $params['template_name'];
			
			if($this->oPrediggoConfig->logs_generation)
				$this->setRecommendationsLogFile($sHookName, $oPrediggoCall->getLogs());
				
			array_push($aRecommendations,$reco_block4);
		}
		
		if ( (boolean)$this->oPrediggoConfig->basket_5_activated and strcmp($this->oPrediggoConfig->basket_5_hook_name,$sHookName)==0)
		{
			//echo 'Basket Page Block #4 is active => computing recommendation<BR>';
			$params['nb_items'] = (int)$this->oPrediggoConfig->basket_5_nb_items;
			$params['block_title'] = pSQL($this->oPrediggoConfig->basket_5_block_label[(int)$params['cookie']->id_lang]);
			$params['variant_id'] = (int)$this->oPrediggoConfig->basket_5_variant_id;
			$params['template_name'] = $this->oPrediggoConfig->basket_5_template_name;
			
			$reco_block4 = $oPrediggoCall->getBasketPageRecommendation($params);
			$reco_block4['block_title'] =  $params['block_title'];
			$reco_block4['block_template'] =  $params['template_name'];
			
			if($this->oPrediggoConfig->logs_generation)
				$this->setRecommendationsLogFile($sHookName, $oPrediggoCall->getLogs());
				
			array_push($aRecommendations,$reco_block4);
		}
		
		return $aRecommendations;
	}
	
	/**
	* create the add condition for a category page or manufacturer page 
	* return an array of two dimension [0] att name [1] value
	*/
	public function createAddCondition()
	{
		$res = array();
		$value;
		if ($this->sPageName==$this->oPrediggoConfig->categoryPageName)
		{
			$value=new Category((int)Tools::getValue('id_category'), (int)$params['cookie']->id_lang);
			array_push($res,$this->oPrediggoConfig->attNameCategory);
			array_push($res,$value->getName((int)$params['cookie']->id_lang));
		}
		else if ($this->sPageName==$this->oPrediggoConfig->manufacturerPageName)
		{
			array_push($res,$this->oPrediggoConfig->attNameManufacturer);
			array_push($res,(int)Tools::getValue('id_manufacturer'));
		}
		
		return $res;
	}


	/**
	* Make the Category page recommendation ONLY if the hook matches
	*/
	public function categoryPageRecoIfHookMatch($sHookName, $params,$oPrediggoCall)
	{
		$aRecommendations = array();
		
		if ( (boolean)$this->oPrediggoConfig->category_0_activated and strcmp($this->oPrediggoConfig->category_0_hook_name,$sHookName)==0)
		{
			//echo 'Category Page Block #0 is active => computing recommendation,nb reco:'.(int)$this->oPrediggoConfig->category_0_nb_items.'<BR>';
			$params['nb_items'] = (int)$this->oPrediggoConfig->category_0_nb_items;
            $params['block_title'] = pSQL($this->oPrediggoConfig->category_0_block_label[(int)$params['cookie']->id_lang]);
			$params['variant_id'] = (int)$this->oPrediggoConfig->category_0_variant_id;
			$params['template_name'] = $this->oPrediggoConfig->category_0_template_name;
			
			//add the condition of the category or manufacturer
			$params['condition']= $this->createAddCondition();
			
			$reco_block0 = $oPrediggoCall->getCategoryPageRecommendation($params);
			$reco_block0['block_title'] =  $params['block_title'];
			$reco_block0['block_template'] =  $params['template_name'];
			
			if($this->oPrediggoConfig->logs_generation)
				$this->setRecommendationsLogFile($sHookName, $oPrediggoCall->getLogs());
				
			array_push($aRecommendations,$reco_block0);
		}
			
		if ( (boolean)$this->oPrediggoConfig->category_1_activated and strcmp($this->oPrediggoConfig->category_1_hook_name,$sHookName)==0)
		{
			//echo 'Category Page Block #1 is active => computing recommendation<BR>';
			$params['nb_items'] = (int)$this->oPrediggoConfig->category_1_nb_items;
            $params['block_title'] = pSQL($this->oPrediggoConfig->category_1_block_label[(int)$params['cookie']->id_lang]);
			$params['variant_id'] = (int)$this->oPrediggoConfig->category_1_variant_id;
			$params['template_name'] = $this->oPrediggoConfig->category_1_template_name;
			
			//add the condition of the category or manufacturer
			$params['condition']= $this->createAddCondition();
			
			$reco_block1 = $oPrediggoCall->getCategoryPageRecommendation($params);
			$reco_block1['block_title'] =  $params['block_title'];
			$reco_block1['block_template'] =  $params['template_name'];
			
			if($this->oPrediggoConfig->logs_generation)
				$this->setRecommendationsLogFile($sHookName, $oPrediggoCall->getLogs());
				
			array_push($aRecommendations,$reco_block1);
		}
		
		if ( (boolean)$this->oPrediggoConfig->category_2_activated and strcmp($this->oPrediggoConfig->category_2_hook_name,$sHookName)==0)
		{
			//echo 'Category Page Block #2 is active => computing recommendation<BR>';
			$params['nb_items'] = (int)$this->oPrediggoConfig->category_2_nb_items;
            $params['block_title'] = pSQL($this->oPrediggoConfig->category_2_block_label[(int)$params['cookie']->id_lang]);
			$params['variant_id'] = (int)$this->oPrediggoConfig->category_2_variant_id;
			$params['template_name'] = $this->oPrediggoConfig->category_2_template_name;
			
			//add the condition of the category or manufacturer
			$params['condition']= $this->createAddCondition();
			
			$reco_block2 = $oPrediggoCall->getCategoryPageRecommendation($params);
			$reco_block2['block_title'] =  $params['block_title'];
			$reco_block2['block_template'] =  $params['template_name'];
			
			if($this->oPrediggoConfig->logs_generation)
				$this->setRecommendationsLogFile($sHookName, $oPrediggoCall->getLogs());
				
			array_push($aRecommendations,$reco_block2);
		}
		
		return $aRecommendations;
	}

    /**
     * Make the Category page recommendation ONLY if the hook matches
     */
    public function customerPageRecoIfHookMatch($sHookName, $params, $oPrediggoCall)
    {
        $aRecommendations = array();
        $this->oPrediggoConfig->customer_0_hook_name = 'displayCustomerAccount';
        if ( (boolean)$this->oPrediggoConfig->customer_0_activated and strcmp($this->oPrediggoConfig->customer_0_hook_name,$sHookName)==0)
        {
            //echo 'Customer Page Block #0 is active => computing recommendation,nb reco:'.(int)$this->oPrediggoConfig->customer_0_nb_items.'<BR>';
            $params['nb_items'] = (int)$this->oPrediggoConfig->customer_0_nb_items;
            $params['block_title'] = pSQL($this->oPrediggoConfig->customer_0_block_label[(int)$params['cookie']->id_lang]);
            $params['variant_id'] = (int)$this->oPrediggoConfig->customer_0_variant_id;
            $params['template_name'] = $this->oPrediggoConfig->customer_0_template_name;

            //add the condition of the customer or manufacturer
            $params['condition']= $this->createAddCondition();

            $reco_block0 = $oPrediggoCall->getCustomerPageRecommendation($params);
            $reco_block0['block_title'] =  $params['block_title'];
            $reco_block0['block_template'] =  $params['template_name'];

            if($this->oPrediggoConfig->logs_generation)
                $this->setRecommendationsLogFile($sHookName, $oPrediggoCall->getLogs());

            array_push($aRecommendations,$reco_block0);
        }

        if ( (boolean)$this->oPrediggoConfig->customer_1_activated and strcmp($this->oPrediggoConfig->customer_1_hook_name,$sHookName)==0)
        {
            //echo 'Customer Page Block #1 is active => computing recommendation<BR>';
            $params['nb_items'] = (int)$this->oPrediggoConfig->customer_1_nb_items;
            $params['block_title'] = pSQL($this->oPrediggoConfig->customer_1_block_label[(int)$params['cookie']->id_lang]);
            $params['variant_id'] = (int)$this->oPrediggoConfig->customer_1_variant_id;
            $params['template_name'] = $this->oPrediggoConfig->customer_1_template_name;

            //add the condition of the customer or manufacturer
            $params['condition']= $this->createAddCondition();

            $reco_block1 = $oPrediggoCall->getCustomerPageRecommendation($params);
            $reco_block1['block_title'] =  $params['block_title'];
            $reco_block1['block_template'] =  $params['template_name'];

            if($this->oPrediggoConfig->logs_generation)
                $this->setRecommendationsLogFile($sHookName, $oPrediggoCall->getLogs());

            array_push($aRecommendations,$reco_block1);
        }

        if ( (boolean)$this->oPrediggoConfig->customer_2_activated and strcmp($this->oPrediggoConfig->customer_2_hook_name,$sHookName)==0)
        {
            //echo 'Customer Page Block #2 is active => computing recommendation<BR>';
            $params['nb_items'] = (int)$this->oPrediggoConfig->customer_2_nb_items;
            $params['block_title'] = pSQL($this->oPrediggoConfig->customer_2_block_label[(int)$params['cookie']->id_lang]);
            $params['variant_id'] = (int)$this->oPrediggoConfig->customer_2_variant_id;
            $params['template_name'] = $this->oPrediggoConfig->customer_2_template_name;

            //add the condition of the customer or manufacturer
            $params['condition']= $this->createAddCondition();

            $reco_block2 = $oPrediggoCall->getCustomerPageRecommendation($params);
            $reco_block2['block_title'] =  $params['block_title'];
            $reco_block2['block_template'] =  $params['template_name'];

            if($this->oPrediggoConfig->logs_generation)
                $this->setRecommendationsLogFile($sHookName, $oPrediggoCall->getLogs());

            array_push($aRecommendations,$reco_block2);
        }
        return $aRecommendations;
    }
	
     /**
     * Get the list of recommendations by hook
     *
     * @param string $sHookName Name of the hook
     * @param array $params list of specific parameters
     * @return array $aRecommendations list of products
     */
    public function getListOfRecommendationsWithDynamicTemplate($sHookName, $params)
    {
        if(!$this->oPrediggoConfig->web_site_id_checked)
		{
			//echo '<H1>Prediggo Module is not activated, please contact Prediggo</H1><br>';
            return false;
		}
        $this->oPrediggoCall = new PrediggoCall($this->oPrediggoConfig->web_site_id, $this->oPrediggoConfig->server_url_recommendations);

		$aRecommendations = false;

		//echo 'getListOfRecommendationsWithDynamicTemplate()- Page name:'.$this->sPageName.', hookName='.$sHookName.'<BR>';
		
		if($sHookName == 'displayHome' or $sHookName == 'displayOrderConfirmation') 
		{
			return $this->homePageRecoIfHookMatch($sHookName, $params,$this->oPrediggoCall);		
		}
		else if ($sHookName == 'displayRightColumn' or $sHookName == 'displayLeftColumn' or $sHookName == 'displayTop' or $sHookName == 'displayFooter')
		{
			return  $this->allPageRecoIfHookMatch($sHookName, $params,$this->oPrediggoCall);
		}
		else if ($sHookName == 'displayLeftColumnProduct' or $sHookName == 'displayRightColumnProduct' or $sHookName == 'displayProductButtons' or $sHookName == 'actionProductOutOfStock' or 
		$sHookName == 'displayFooterProduct' or $sHookName == 'displayProductTab' or $sHookName == 'displayProductTabContent' )
		{
			return  $this->productPageRecoIfHookMatch($sHookName, $params,$this->oPrediggoCall);
		}
		else if ($sHookName == 'displayShoppingCartFooter' or $sHookName == 'displayShoppingCart' or $sHookName == 'displayOrderDetail' or 
		$sHookName == 'displayBeforeCarrier' or $sHookName == 'displayCarrierList' )
		{
			return  $this->basketPageRecoIfHookMatch($sHookName, $params,$this->oPrediggoCall);
		}
		else if ($sHookName == 'displayRightColumncategory' or $sHookName == 'displayLeftColumncategory' or $sHookName == 'displayTopcategory' or $sHookName == 'displayFootercategory' or 
		$sHookName == 'displayRightColumnmanufacturer' or $sHookName == 'displayLeftColumnmanufacturer' or $sHookName == 'displayTopmanufacturer' or $sHookName == 'displayFootemanufacturer')
		{
			return  $this->categoryPageRecoIfHookMatch($sHookName, $params,$this->oPrediggoCall);
		}
        else if ($sHookName == 'displayCustomerAccount' or $sHookName == 'displayMyAccountBlock' or $sHookName == 'displayMyAccountBlockfooter')
        {
            return  $this->customerPageRecoIfHookMatch($sHookName, $params,$this->oPrediggoCall);
        }
        return $aRecommendations;
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
            $this->aPagesAccessible[] = 'manufacturer';
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
        $context = $this->oPrediggoConfig->getContext();
        $controller = $context->controller->php_self;
        $this->sPageName = $controller;
        //echo 'controller:'.$this->sPageName;
        return true;
    }

    /**
     * Update the current $sPageName
     */
    public function _setPageName($sPageName)
    {
         $this->sPageName = $sPageName;
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
			{
				fwrite($handle, $sLog."\n");
			}
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
     * Export the client Configuration
     *
     * @return Client export configuration file from Db
     */
    public function export_client_config()
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'configuration`'.' WHERE `name` LIKE \'PREDIGGO_%\'';
        $sql2 = 'SELECT cl.* FROM `'._DB_PREFIX_.'configuration_lang` cl, `'._DB_PREFIX_.'configuration` c '.'WHERE c.`id_configuration` = cl.`id_configuration` and c.`name` LIKE \'PREDIGGO_%\' ORDER BY cl.`id_configuration` ASC';
        $fp = fopen(_PS_MODULE_DIR_.'prediggo/xmlfiles/export_configuration.sql',"w");
        $fp2 = fopen(_PS_MODULE_DIR_.'prediggo/xmlfiles/export_configuration_lang.sql',"w");
        $aQueryResult = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql2);
            //fputs($fp2, 'INSERT INTO `'. _DB_PREFIX_.'configuration_lang`'.' (`id_configuration`, `id_lang`, `value`, `date_upd`)'.' VALUES'."\r");
            foreach ($aQueryResult as $row)
                if(empty($row['value']))
                    fputs($fp2,'UPDATE `'._DB_PREFIX_.'configuration_lang` SET `id_configuration` = '.$row['id_configuration'].',`id_lang` = '.$row['id_lang'].',`value` = \''."NULL".'\'' . ',`date_upd` = \''.$row['date_upd'].'\''.' WHERE `'._DB_PREFIX_.'configuration_lang`.`id_configuration` = '.$row['id_configuration'].' AND `'._DB_PREFIX_.'configuration_lang`.`id_lang` = '.$row['id_lang'].';'."\r");
                else
                    fputs($fp2,'UPDATE `'._DB_PREFIX_.'configuration_lang` SET `id_configuration` = '.$row['id_configuration'].',`id_lang` = '.$row['id_lang'].',`value` = \''.$row['value'].'\'' . ',`date_upd` = \''.$row['date_upd'].'\''.' WHERE `'._DB_PREFIX_.'configuration_lang`.`id_configuration` = '.$row['id_configuration'].' AND `'._DB_PREFIX_.'configuration_lang`.`id_lang` = '.$row['id_lang'].';'."\r");
        $aQueryResult = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
            //fputs($fp,'INSERT INTO `'._DB_PREFIX_.'configuration`'.' (`id_configuration`, `id_shop_group`, `id_shop`, `name`, `value`, `date_add`, `date_upd`)'.' VALUES'."\r");
            foreach ($aQueryResult as $row)
               if(empty($row['id_shop_group']) and empty($row['id_shop']) and empty($row['value']))
                   fputs($fp,'UPDATE `'._DB_PREFIX_.'configuration` SET `id_configuration` = '.$row['id_configuration'].',`id_shop_group` = '."NULL".',`id_shop` = '."NULL".',`name` = \''.$row['name'].'\''.',`value` = '."NULL".',`date_add` = \''.$row['date_add'].'\',`date_upd` = \''.$row['date_upd'].'\''.' WHERE `'._DB_PREFIX_.'configuration`.`id_configuration` = '.$row['id_configuration'].';'."\r");

               elseif (empty($row['id_shop_group']) and empty($row['id_shop']))
                   fputs($fp,'UPDATE `'._DB_PREFIX_.'configuration` SET `id_configuration` = '.$row['id_configuration'].',`id_shop_group` = '."NULL".',`id_shop` = '."NULL".',`name` = \''.$row['name'].'\''.',`value` = \''.$row['value'].'\',`date_add` = \''.$row['date_add'].'\',`date_upd` = \''.$row['date_upd'].'\''.' WHERE `'._DB_PREFIX_.'configuration`.`id_configuration` = '.$row['id_configuration'].';'."\r");

               elseif (empty($row['id_shop_group']) and empty($row['value']))
                   fputs($fp,'UPDATE `'._DB_PREFIX_.'configuration` SET `id_configuration` = '.$row['id_configuration'].',`id_shop_group` = '."NULL".',`id_shop` = '.$row['id_shop'].',`name` = \''.$row['name'].'\''.',`value` = '."NULL".',`date_add` = \''.$row['date_add'].'\',`date_upd` = \''.$row['date_upd'].'\''.' WHERE `'._DB_PREFIX_.'configuration`.`id_configuration` = '.$row['id_configuration'].';'."\r");

               elseif (empty($row['id_shop']) and empty($row['value']))
                   fputs($fp,'UPDATE `'._DB_PREFIX_.'configuration` SET `id_configuration` = '.$row['id_configuration'].',`id_shop_group` = '.$row['id_shop_group'].',`id_shop` = '."NULL".',`name` = \''.$row['name'].'\''.',`value` = '."NULL".',`date_add` = \''.$row['date_add'].'\',`date_upd` = \''.$row['date_upd'].'\''.' WHERE `'._DB_PREFIX_.'configuration`.`id_configuration` = '.$row['id_configuration'].';'."\r");

               elseif (empty($row['id_shop_group']))
                   fputs($fp,'UPDATE `'._DB_PREFIX_.'configuration` SET `id_configuration` = '.$row['id_configuration'].',`id_shop_group` = '."NULL".',`id_shop` = '.$row['id_shop'].',`name` = \''.$row['name'].'\''.',`value` = \''.$row['value'].'\',`date_add` = \''.$row['date_add'].'\',`date_upd` = \''.$row['date_upd'].'\''.' WHERE `'._DB_PREFIX_.'configuration`.`id_configuration` = '.$row['id_configuration'].';'."\r");

               elseif (empty($row['id_shop']))
                   fputs($fp,'UPDATE `'._DB_PREFIX_.'configuration` SET `id_configuration` = '.$row['id_configuration'].',`id_shop_group` = '.$row['id_shop_group'].',`id_shop` = '."NULL".',`name` = \''.$row['name'].'\''.',`value` = \''.$row['value'].'\',`date_add` = \''.$row['date_add'].'\',`date_upd` = \''.$row['date_upd'].'\''.' WHERE `'._DB_PREFIX_.'configuration`.`id_configuration` = '.$row['id_configuration'].';'."\r");

               elseif (empty($row['value']))
                   fputs($fp,'UPDATE `'._DB_PREFIX_.'configuration` SET `id_configuration` = '.$row['id_configuration'].',`id_shop_group` = '.$row['id_shop_group'].',`id_shop` = '.$row['id_shop'].',`name` = \''.$row['name'].'\''.',`value` = '."NULL".',`date_add` = \''.$row['date_add'].'\',`date_upd` = \''.$row['date_upd'].'\''.' WHERE `'._DB_PREFIX_.'configuration`.`id_configuration` = '.$row['id_configuration'].';'."\r");

               else
                   fputs($fp,'UPDATE `'._DB_PREFIX_.'configuration` SET `id_configuration` = '.$row['id_configuration'].',`id_shop_group` = '.$row['id_shop_group'].',`id_shop` = '.$row['id_shop'].',`name` = \''.$row['name'].'\''.',`value` = \''.$row['value'].'\',`date_add` = \''.$row['date_add'].'\',`date_upd` = \''.$row['date_upd'].'\''.' WHERE `'._DB_PREFIX_.'configuration`.`id_configuration` = '.$row['id_configuration'].';'."\r");
        fclose($fp);
        fclose($fp2);
        unset($aQueryResult);
    }

    /**
     * Import the client Configuration
     *
     * @param File For the Import
     * @return Client import configuration file from Db
     */
    public function import_client_config()
    {
        //$sql = 'DELETE FROM `'._DB_PREFIX_.'configuration`'.' WHERE `name` LIKE \'PREDIGGO_%\'';
        //$aQueryResult = Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);
            unset($aQueryResult);
            $fp = fopen(_PS_MODULE_DIR_ . 'prediggo/xmlfiles/import.sql', "r");
            //$fpr = "";
            while (!feof($fp)) {
                $fpr = fgets($fp);
                if (Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($fpr));
            }
    }

    /**
     * Import the client Configuration
     *
     * @param File For the Import
     * @return Client import configuration file from Db
     */
    public function import_client_config2()
    {
        //$sql2 = 'DELETE FROM `'._DB_PREFIX_.'configuration_lang`'.'WHERE `id_configuration` in (select `id_configuration` FROM `'._DB_PREFIX_.'configuration` WHERE `name` LIKE \'PREDIGGO_%\')';
        //$aQueryResult2 = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql2);
            unset($aQueryResult2);
            $fp2 = fopen(_PS_MODULE_DIR_.'prediggo/xmlfiles/import2.sql', "r");
            //$fpr2 = "";
            while (!feof($fp2)) {
                $fpr2 = fgets($fp2);
                if (Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($fpr2));
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
        $this->oPrediggoCall = new PrediggoCall($this->oPrediggoConfig->shop_name, $this->oPrediggoConfig->token_id, $this->oPrediggoConfig->server_url_check);
        return $this->oPrediggoCall->checkLicence();
    }
	
	
}
