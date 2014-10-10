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

if (!defined('_PS_VERSION_'))
	exit;

require_once(dirname(__FILE__).'/classes/PrediggoConfig.php');
require_once(_PS_MODULE_DIR_.'prediggo/controllers/DataExtractorController.php');
require_once(_PS_MODULE_DIR_.'prediggo/controllers/PrediggoCallController.php');
require_once(_PS_MODULE_DIR_.'prediggo/custom/PrediggoCallControllerOverride.php');

class Prediggo extends Module
{
	/** @var array list of errors */
	public $_errors = array();

	/** @var array list of confirmations */
	public $_confirmations = array();

	/** @var array list of warnings */
	public $_warnings = array();

	/** @var PrediggoConfig Object PrediggoConfig */
	public $oPrediggoConfig;

	/** @var DataExtractorController Object DataExtractorController */
	public $oDataExtractorController;

	/** @var PrediggoCallController Object PrediggoCallController */
	public $oPrediggoCallController;

	/** @var array list of Products by hook */
	public $aRecommendations;

    /** @var string Hook Name */
    public $sHookName;

    /** @var int Variant ID */
    public $iVariantId;

	/**
	 * Constructor
	*/
	public function __construct()
	{
		$this->name = 'prediggo';
		$this->tab = 'advertising_marketing';
		$this->version = '1.5';
		$this->author = 'PrestaShop';
		$this->need_instance = 0;
		$this->_html = '';
		$this->multishop_context = true;
        $this->sHookName = '';

		parent::__construct();

		$this->displayName = $this->l('Prediggo');
		$this->description = $this->l('Offers interactive products recommendations in the front office');


		$this->_warnings = array();
		$this->_confirmations = array();
		$this->_errors = array();

		/* Set the Configuration Object */
		$this->oPrediggoConfig = new PrediggoConfig($this->context);

		/* Set the main controllers */
		$this->oDataExtractorController = new DataExtractorController($this);
		$this->oPrediggoCallController = new PrediggoCallControllerOverride();

		$this->aRecommendations = array();
	}

	/**
	 * Install Procedure
     * La liste des hooks se trouve en http://doc.prestashop.com/display/PS15/Hooks+in+PrestaShop+1.5
	 */
	public function install()
	{
		return 	($this->oPrediggoConfig->install()
				&& parent::install()
				&& $this->registerAllHooks()
		);
	}
	
	/**
	 * Registration Hook Procedure
     * La liste des hooks se trouve en http://doc.prestashop.com/display/PS15/Hooks+in+PrestaShop+1.5
	 */
	public function registerAllHooks()
	{
		return 	($this->registerHook('displayTop')
				&& $this->registerHook('displayHeader')
				&& $this->registerHook('displayLeftColumn')
				&& $this->registerHook('displayRightColumn')
				&& $this->registerHook('displayFooter')
				&& $this->registerHook('actionAuthentication')
				&& $this->registerHook('actionCustomerAccountAdd')
				&& $this->registerHook('displayPaymentTop')
				&& $this->registerHook('displayHome')
				&& $this->registerHook('displayFooterProduct')
                && $this->registerHook('displayLeftColumnProduct')
				&& $this->registerHook('displayRightColumnProduct')
                && $this->registerHook('displayShoppingCartFooter')
                && $this->registerHook('displayShoppingCart')
                && $this->registerHook('displayOrderDetail')
                && $this->registerHook('displayProductTab')
                && $this->registerHook('displayBeforeCarrier')
                && $this->registerHook('displayCarrierList')
				&& $this->registerHook('displayOrderConfirmation')
                && $this->registerHook('displayCustomerAccount')
                && $this->registerHook('displayMyAccountBlock')
                && $this->registerHook('displayMyAccountBlockfooter')
		);
	}

	/**
	 * Uninstall Procedure
	 */
	public function uninstall()
	{
		return	($this->oPrediggoConfig->uninstall()
				&& parent::uninstall()
		);
	}
	

	/**
	 * Hook Header : Add Media CSS & JS
	 *
	 * @param array $params list of specific data
	 */
	public function hookDisplayHeader($params)
	{
	
		if (!isset($params['cookie']->id_guest))
			Guest::setNewGuest($params['cookie']);

		if(!$this->oPrediggoConfig->web_site_id_checked)
			return false;
		
		
		//add the js for the autocomplete but also the notify
		if($this->oPrediggoCallController->isPageAccessible())
		{
			$this->context->controller->addJS(array(
				($this->_path).'js/front/'.($this->name).'.js'));
		}

		// Check if prediggo module can be executed in this page
		if($this->oPrediggoCallController->isPageAccessible()
		|| $this->oPrediggoCallController->getPageName() == 'prediggo_search'
		|| $this->oPrediggoConfig->search_active)
		{
			$this->context->controller->addCSS(($this->_path).'css/front/'.($this->name).'.css', 'all');
			$this->context->controller->addJS(array(
				($this->_path).'js/front/prediggo_autocomplete.js',
				($this->_path).'js/front/'.($this->name).'.js'
			));
		}
	}

	/**
	 * Hook Home : Display the recommendations
	 *
	 * @param array $params list of specific data
     * @int int to choose your Variant ID
	 */
	public function hookDisplayHome($params)
	{
		//this is usefull to register all the hooks again
		//$this->registerAllHooks();
		
		// Get list of recommendations
		//echo '<BR><BR>DISPLAY HOME PAGE<br>';
		return $this->displayRecommendationsWithDynamicTemplate('displayHome', $params);
	}
	
	/**
	 * Hook Right Column : Display the recommendations
	 *
	 * @param array $params list of specific data
     * @int int to choose your Variant ID
	 */
	public function hookDisplayRightColumn($params)
	{
		//echo '<BR><BR>DISPLAY RIGHT COLUMN - page name '.$this->oPrediggoCallController->getPageName().'<br>';
		//check if we are on a catgogry page, need to do this as no category hook In Presta 1.5:(
		if (strcmp ($this->oPrediggoCallController->getPageName(), $this->oPrediggoConfig->categoryPageName)==0)
		{
			return $this->displayRecommendationsWithDynamicTemplate('displayRightColumn'.$this->oPrediggoCallController->getPageName(), $params).$this->displayRecommendationsWithDynamicTemplate('displayRightColumn', $params);
		}
		else if (strcmp ($this->oPrediggoCallController->getPageName(), $this->oPrediggoConfig->manufacturerPageName)==0)
		{
			return $this->displayRecommendationsWithDynamicTemplate('displayRightColumn'.$this->oPrediggoCallController->getPageName(), $params).$this->displayRecommendationsWithDynamicTemplate('displayRightColumn', $params);
		}
		
		// Get list of recommendations
		return $this->displayRecommendationsWithDynamicTemplate('displayRightColumn', $params);
	}
	
	/**
	 * Hook Left Column : Display the recommendations
	 *
	 * @param array $params list of specific data
     * @int int to choose your Variant ID
	 */
	public function hookDisplayLeftColumn($params)
	{
		// Get list of recommendations
		//echo '<BR><BR>DISPLAY LEFT COLUMN<br>';
		//check if we are on a catgogry page, need to do this as no category hook In Presta 1.5:(


		if (strcmp ($this->oPrediggoCallController->getPageName(),$this->oPrediggoConfig->categoryPageName)==0)
		{
			return $this->displayRecommendationsWithDynamicTemplate('displayLeftColumn'.$this->oPrediggoCallController->getPageName(), $params).$this->displayRecommendationsWithDynamicTemplate('displayLeftColumn', $params).$this->displaySearchFilterBlock($params);
		}
		else if (strcmp ($this->oPrediggoCallController->getPageName(),$this->oPrediggoConfig->manufacturerPageName)==0)
		{
			return $this->displayRecommendationsWithDynamicTemplate('displayLeftColumn'.$this->oPrediggoCallController->getPageName(), $params).$this->displayRecommendationsWithDynamicTemplate('displayLeftColumn', $params).$this->displaySearchFilterBlock($params);
		}
		return $this->displayRecommendationsWithDynamicTemplate('displayLeftColumn', $params).$this->displaySearchFilterBlock($params);
	}
	/**
	 * Hook Top : Display the prediggo search block
	 *
	 * @param array $params list of specific data
	 */
	public function hookDisplayTop($params)
	{
		//echo '<BR><BR>DISPLAY TOP<br>';
		//check if we are on a catgogry page, need to do this as no category hook In Presta 1.5:(
		if (strcmp ($this->oPrediggoCallController->getPageName(),$this->oPrediggoConfig->categoryPageName)==0)
		{
			return $this->displayRecommendationsWithDynamicTemplate('displayTop'.$this->oPrediggoCallController->getPageName(), $params).$this->displayRecommendationsWithDynamicTemplate('displayTop', $params).$this->displaySearchBlock($params);
		}
		else if (strcmp ($this->oPrediggoCallController->getPageName(),$this->oPrediggoConfig->manufacturerPageName)==0)
		{
			return $this->displayRecommendationsWithDynamicTemplate('displayTop'.$this->oPrediggoCallController->getPageName(), $params).$this->displayRecommendationsWithDynamicTemplate('displayTop', $params).$this->displaySearchBlock($params);
		}
        //$this->displaySearchBlock($params).$this->displayRecommendationsWithDynamicTemplate('displayTop', $params);
		return $this->displayRecommendationsWithDynamicTemplate('displayTop', $params).$this->displaySearchBlock($params);
	}
	
	/**
	 * Hook Footer : Display the recommendations
	 *
	 * @param array $params list of specific data
     * @int int to choose your Variant ID
	 */
	public function hookDisplayFooter($params)
	{
	//check if we are on a catgogry page, need to do this as no category hook In Presta 1.5:(
		if (strcmp ($this->oPrediggoCallController->getPageName(),$this->oPrediggoConfig->categoryPageName)==0)
		{
			return $this->displayRecommendationsWithDynamicTemplate('displayRightColumn'.$this->oPrediggoCallController->getPageName(), $params).$this->displayRecommendationsWithDynamicTemplate('displayRightColumn', $params);
		}
		else if (strcmp ($this->oPrediggoCallController->getPageName(),$this->oPrediggoConfig->manufacturerPageName)==0)
		{
			return $this->displayRecommendationsWithDynamicTemplate('displayRightColumn'.$this->oPrediggoCallController->getPageName(), $params).$this->displayRecommendationsWithDynamicTemplate('displayRightColumn', $params);
		}
		//echo '<BR><BR>DISPLAY FOOTER<br>';
		return $this->displayRecommendationsWithDynamicTemplate('displayFooter', $params);
	}


	/**
	 * Hook Left Column Product : Display the recommendations
	 *
	 * @param array $params list of specific data
     * @int int to choose your Variant ID
	 */
	public function hookDisplayLeftColumnProduct($params)
	{
		// Get list of recommendations
		//echo '<BR><BR>DISPLAY displayLeftColumnProduct<br>';
		return $this->displayRecommendationsWithDynamicTemplate('displayLeftColumnProduct', $params);
	}

    /**
     * Hook Right Column Product : Display the recommendations
     *
     * @param array $params list of specific data
     * @int int to choose your Variant ID
     */
    public function hookDisplayRightColumnProduct($params)
    {
        // Get list of recommendations
		//echo '<BR><BR>DISPLAY displayRightColumnProduct<br>';
		return $this->displayRecommendationsWithDynamicTemplate('displayRightColumnProduct', $params);
    }

	/**
     * Hook Product Tab : Display the recommendations
     *
     * @param array $params list of specific data
     * @int int to choose your Variant ID
     */
    public function hookDisplayProductTab($params)
    {
		//echo '<BR><BR>DISPLAY getProductTablE<br>';
		return $this->displayRecommendationsWithDynamicTemplate('displayRightColumnProduct', $params);
    }

	
    /**
     * Hook Shopping Cart Footer : Display the recommendations
     *
     * @param array $params list of specific data
     * @int int to choose your Variant ID
     */
    public function hookDisplayShoppingCartFooter($params)
    {
        // Get list of recommendations
		//echo '<BR><BR>DISPLAY displayShoppingCartFooter<br>';
		return $this->displayRecommendationsWithDynamicTemplate('displayShoppingCartFooter', $params);
    }

    /**
     * Hook Shopping Cart : Display the recommendations
     *
     * @param array $params list of specific data
     * @int int to choose your Variant ID
     */
    public function hookDisplayShoppingCart($params)
    {
        // Get list of recommendations
		//echo '<BR><BR>DISPLAY displayShoppingCart<br>';
		return $this->displayRecommendationsWithDynamicTemplate('displayShoppingCart', $params);
    }

    /**
     * Hook Order Detail : Display the recommendations
     *
     * @param array $params list of specific data
     * @int int to choose your Variant ID
     */
    public function hookDisplayOrderDetail($params)
    {
		//echo '<BR><BR>DISPLAY displayOrderDetail<br>';
        // Get list of recommendations
		return $this->displayRecommendationsWithDynamicTemplate('displayOrderDetail', $params);
    }

    /**
     * Hook Before Carrier : Display the recommendations
     *
     * @param array $params list of specific data
     * @int int to choose your Variant ID
     */
    public function hookDisplayBeforeCarrier($params)
    {
		//echo '<BR><BR>DISPLAY displayBeforeCarrier<br>';
        // Get list of recommendations
		return $this->displayRecommendationsWithDynamicTemplate('displayBeforeCarrier', $params);
    }

    /**
     * Hook Carrier List : Display the recommendations
     *
     * @param array $params list of specific data
     * @int int to choose your Variant ID
     */
    public function hookDisplayCarrierList($params)
    {
		//echo '<BR><BR>DISPLAY displayCarrierList<br>';
		return $this->displayRecommendationsWithDynamicTemplate('displayCarrierList', $params);
    }
	
	/**
     * Hook Carrier List : Display the recommendations
     *
     * @param array $params list of specific data
     * @int int to choose your Variant ID
     */
    public function hookDisplayOrderConfirmation($params)
    {
		//echo '<BR><BR>DISPLAY order confirmation<br>';
		return $this->displayRecommendationsWithDynamicTemplate('displayOrderConfirmation', $params);
    }

    /**
     * Hook Customer Account : Display the recommendations
     *
     * @param array $params list of specific data
     * @int int to choose your Variant ID
     */
    public function hookDisplayCustomerAccount($params)
    {
        //echo '<BR><BR>DISPLAY order confirmation Customer Account<br>';
        $params['customer'] = $this->context->customer;
        return $this->displayRecommendationsWithDynamicTemplate('displayCustomerAccount', $params);
    }

    /**
     * Hook Customer Account : Display the recommendations
     *
     * @param array $params list of specific data
     * @int int to choose your Variant ID
     */
    public function hookDisplayMyAccountBlock($params)
    {
        //echo '<BR><BR>DISPLAY order confirmation My Account Block<br>';
        $params['customer'] = $this->context->customer;
        return $this->displayRecommendationsWithDynamicTemplate('displayMyAccountBlock', $params);
    }

    /**
     * Hook Customer Account : Display the recommendations
     *
     * @param array $params list of specific data
     * @int int to choose your Variant ID
     */
    public function hookDisplayMyAccountBlockfooter($params)
    {
        //echo '<BR><BR>DISPLAY order confirmation My Account Block Footer<br>';
        $params['customer'] = $this->context->customer;
        return $this->displayRecommendationsWithDynamicTemplate('displayMyAccountBlockfooter', $params);
    }

	/**
	 * Hook Authentication : Notify prediggo that the user is authenticated
	 *
	 * @param array $params list of specific data
	 */
	public function hookActionAuthentication($params)
	{
		if(!$this->oPrediggoConfig->web_site_id_checked)
			return false;
		$params['customer'] = $this->context->customer;
		$this->oPrediggoCallController->notifyPrediggo('user', $params);
	}

	/**
	 * Hook Payment Top : Notify prediggo that the user is authenticated
	 *
	 * @param array $params list of specific data
	 */
	public function hookDisplayPaymentTop($params)
	{
		if(!$this->oPrediggoConfig->web_site_id_checked)
			return false;

		$params['customer'] = $this->context->customer;
		$this->oPrediggoCallController->notifyPrediggo('user', $params);
	}

	/**
	 * Hook Create Account : Notify prediggo that the user is authenticated
	 *
	 * @param array $params list of specific data
	 */
	public function hookActionCustomerAccountAdd($params)
	{
		if(!$this->oPrediggoConfig->web_site_id_checked)
			return false;
		$params['customer'] = $this->context->customer;
		$this->oPrediggoCallController->notifyPrediggo('user', $params);
	}

	/**
	 * Display the recommendations by hook using dynamic templates
	 *
	 * @param string $sHookName Hook Name
	 * @param array $params list of specific data
     * @param int $iVariantId Id of the Variant
	 * @return string Html
	 */
	private function displayRecommendationsWithDynamicTemplate($sHookName, $params)
	{
		if(!$this->oPrediggoConfig->web_site_id_checked)
			return false;

		$params['customer'] = $this->context->customer;
		
		$this->aRecommendations[$sHookName] = $this->oPrediggoCallController->getListOfRecommendationsWithDynamicTemplate($sHookName, $params);

		if(!$this->aRecommendations[$sHookName] || count($this->aRecommendations[$sHookName])==0 || count($this->aRecommendations[$sHookName][0])==0)
		{
			//echo 'Call done BUT NO RESULTS FOUND<br>';
			return false;
		}
		
		// Display Main Configuration management
		$this->smarty->assign(array(
			'hook_name' 		=> $sHookName,
			'aRecommendations' 	=> $this->aRecommendations,
			'tax_enabled' 		=> (int)Configuration::get('PS_TAX'),
			'display_qties' 	=> (int)Configuration::get('PS_DISPLAY_QTIES'),
			'display_ht' 		=> !Tax::excludeTaxeOption(),
			'sImageType' 		=> (Tools::version_compare(_PS_VERSION_, '1.5.1', '>=')?'home_default':'home'),
		));

		return $this->display(__FILE__, $this->aRecommendations[$sHookName][0]['block_template']);
	}
	
	/**
	 * Display the recommendations by hook
	 *
	 * @param string $sHookName Hook Name
	 * @param array $params list of specific data
     * @param int $iVariantId Id of the Variant
	 * @return string Html
	 */
    /**
	private function displayRecommendations($sHookName, $params, $iVariantId)
	{
		if(!$this->oPrediggoConfig->web_site_id_checked)
			return false;

		$params['customer'] = $this->context->customer;
		
		$this->aRecommendations[$sHookName] = $this->oPrediggoCallController->getListOfRecommendations($sHookName, $params, $iVariantId);
		if(!$this->aRecommendations[$sHookName])
			return false;

		// Display Main Configuration management
		$this->smarty->assign(array(
			'hook_name' 		=> $sHookName,
			'aRecommendations' 	=> $this->aRecommendations,

			'tax_enabled' 		=> (int)Configuration::get('PS_TAX'),
			'display_qties' 	=> (int)Configuration::get('PS_DISPLAY_QTIES'),
			'display_ht' 		=> !Tax::excludeTaxeOption(),
			'sImageType' 		=> (Tools::version_compare(_PS_VERSION_, '1.5.1', '>=')?'home_default':'home'),
		));

		return $this->display(__FILE__, 'list_recommendations.tpl');

	}*/


	/**
	 * Hook Authentication : Notify prediggo that a recommendations has been clicked
	 *
	 * @param array $params list of specific data
	 */
	public function setProductNotification($params)
	{
		if(!$this->oPrediggoConfig->web_site_id_checked)
			return false;
		$params['customer'] = $this->context->customer;
		$this->oPrediggoCallController->notifyPrediggo('product', $params);
	}

	/**
	 * Display the search Filters block
	 *
	 * @param array $params list of specific data
	 * @return string Html
	 */
	private function displaySearchFilterBlock($params)
	{
		if($this->oPrediggoConfig->web_site_id_checked
		&& $this->oPrediggoConfig->layered_navigation_active)
            $template = $this->oPrediggoConfig->search_filter_block_template_name;
			return $this->display(__FILE__, $template);
	}

	/**
	 * Display the search block
	 *
	 * @param array $params list of specific data
	 * @return string Html
	 */
	private function displaySearchBlock($params)
	{
		if($this->oPrediggoConfig->web_site_id_checked
		&& $this->oPrediggoConfig->search_active)
            $template = $this->oPrediggoConfig->search_0_template_name;
			return $this->display(__FILE__, $template);
	}

	/**
	 * Display the did you mean suggestion
	 *
	 * @return string Html
	 */
	public function displayAutocompleteDidYouMean()
	{
		if($this->oPrediggoConfig->web_site_id_checked
		&& $this->oPrediggoConfig->search_active
		&& $this->oPrediggoConfig->autocompletion_active)
		{
			$this->smarty->assign(array(
				'sImageType' 		=> (Tools::version_compare(_PS_VERSION_, '1.5.1', '>=')?'home_default':'home'),
			));
            $template = 'views/templates/hook/'.$this->oPrediggoConfig->autoc_template_name;
			return $this->display(__FILE__, $template);
		}
	}

	/**
	 * Display the autocompletion products
	 *
	 * @return string Html
	 */
	public function displayAutocompleteProduct()
	{
		if($this->oPrediggoConfig->web_site_id_checked
		&& $this->oPrediggoConfig->search_active
		&& $this->oPrediggoConfig->autocompletion_active)
            $template = 'views/templates/hook/'.$this->oPrediggoConfig->autop_template_name;
			return $this->display(__FILE__, $template);
	}

    /**
     * Display the autocompletion categories
     *
     * @return string Html
     */
    public function displayAutocompleteAttributes()
    {
        if($this->oPrediggoConfig->web_site_id_checked
            && $this->oPrediggoConfig->search_active
            && $this->oPrediggoConfig->autocompletion_active)
            $template = 'views/templates/hook/'.$this->oPrediggoConfig->autocat_template_name;
        return $this->display(__FILE__, $template);
    }

	/**
	 * Display the autocompletion suggestions
	 *
	 * @return string Html
	 */
	public function displayAutocompleteSuggest()
	{
		if($this->oPrediggoConfig->web_site_id_checked
		&& $this->oPrediggoConfig->search_active
		&& $this->oPrediggoConfig->autocompletion_active)
            $template = 'views/templates/hook/'.$this->oPrediggoConfig->autos_template_name;
			return $this->display(__FILE__, $template);
	}

	/**
	 * Get the recommendations from the blocklayered filters
	 *
	 * @param array $params list of specific data
	 * @return array $aData containing the front office block
	 */
	public function getBlockLayeredRecommendations($params)
	{
		if(!$this->oPrediggoConfig->web_site_id_checked)
			return false;

		$sHookName = 'blocklayered';
		$this->oPrediggoCallControllerOverride->_setPageName($sHookName);
		$params['filters'] = $this->getSelectedFilters();
		$params['customer'] = $this->context->customer;

        $this->aRecommendations[$sHookName] = $this->oPrediggoCallController->getListOfRecommendations($sHookName, $params, 0);
		if(!$this->aRecommendations[$sHookName])
			return false;

		// Display Main Configuration management
		$this->smarty->assign(array(
			'hook_name' 		=> $sHookName,
			'page_name' 		=> 'category',
			'aRecommendations' 	=> $this->aRecommendations,
			'tax_enabled'		=> (int)Configuration::get('PS_TAX'),
			'display_qties' 	=> (int)Configuration::get('PS_DISPLAY_QTIES'),
			'display_ht' 		=> !Tax::excludeTaxeOption(),
			'sImageType' 		=> (Tools::version_compare(_PS_VERSION_, '1.5.1', '>=')?'home_default':'home'),
		));

		return $this->display(__FILE__, 'list_recommendations.tpl');
	}

    // Display the categories
    /*public function displayCategories($params){

        if(!$this->oPrediggoConfig->web_site_id_checked)
            return false;

        if (!$this->isCached('blockcategories.tpl', $this->getCacheId()))
        {
            // Get all groups for this customer and concatenate them as a string: "1,2,3..."
            $groups = implode(', ', Customer::getGroupsStatic((int)$this->context->customer->id));
            $maxdepth = Configuration::get('BLOCK_CATEG_MAX_DEPTH');
            if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
				SELECT DISTINCT c.id_parent, c.id_category, cl.name, cl.description, cl.link_rewrite
				FROM `'._DB_PREFIX_.'category` c
				INNER JOIN `'._DB_PREFIX_.'category_lang` cl ON (c.`id_category` = cl.`id_category` AND cl.`id_lang` = '.(int)$this->context->language->id.Shop::addSqlRestrictionOnLang('cl').')
				INNER JOIN `'._DB_PREFIX_.'category_shop` cs ON (cs.`id_category` = c.`id_category` AND cs.`id_shop` = '.(int)$this->context->shop->id.')
				WHERE (c.`active` = 1 OR c.`id_category` = '.(int)Configuration::get('PS_HOME_CATEGORY').')
				AND c.`id_category` != '.(int)Configuration::get('PS_ROOT_CATEGORY').'
				'.((int)$maxdepth != 0 ? ' AND `level_depth` <= '.(int)$maxdepth : '').'
				AND c.id_category IN (SELECT id_category FROM `'._DB_PREFIX_.'category_group` WHERE `id_group` IN ('.pSQL($groups).'))
				ORDER BY `level_depth` ASC, '.(Configuration::get('BLOCK_CATEG_SORT') ? 'cl.`name`' : 'cs.`position`').' '.(Configuration::get('BLOCK_CATEG_SORT_WAY') ? 'DESC' : 'ASC')))
                return;

            $resultParents = array();
            $resultIds = array();
            $isDhtml = (Configuration::get('BLOCK_CATEG_DHTML') == 1 ? true : false);

            foreach ($result as &$row)
            {
                $resultParents[$row['id_parent']][] = &$row;
                $resultIds[$row['id_category']] = &$row;
            }

            $blockCategTree = $this->getTree($resultParents, $resultIds, Configuration::get('BLOCK_CATEG_MAX_DEPTH'));
            unset($resultParents, $resultIds);

            $this->smarty->assign('blockCategTree', $blockCategTree);
            $this->smarty->assign('branche_tpl_path', _PS_MODULE_DIR_.'prediggo/views/templates/front/category-tree-branch.tpl');
            $this->smarty->assign('isDhtml', $isDhtml);
        }

        $id_category = (int)Tools::getValue('id_category');
        $id_product = (int)Tools::getValue('id_product');

        if (Tools::isSubmit('id_category'))
        {
            $this->context->cookie->last_visited_category = (int)$id_category;
            $this->smarty->assign('currentCategoryId', $this->context->cookie->last_visited_category);
        }

        if (Tools::isSubmit('id_product'))
        {
            if (!isset($this->context->cookie->last_visited_category)
                || !Product::idIsOnCategoryId($id_product, array('0' => array('id_category' => $this->context->cookie->last_visited_category)))
                || !Category::inShopStatic($this->context->cookie->last_visited_category, $this->context->shop))
            {
                $product = new Product((int)$id_product);
                if (isset($product) && Validate::isLoadedObject($product))
                    $this->context->cookie->last_visited_category = (int)$product->id_category_default;
            }
            $this->smarty->assign('currentCategoryId', (int)$this->context->cookie->last_visited_category);
        }

        $display = $this->display(__FILE__, 'views/templates/front/blockcategories.tpl', $this->getCacheId());
        return $display;
    }

    //get cache ID
    protected function getCacheId($name = null)
    {
        parent::getCacheId($name);

        $groups = implode(', ', Customer::getGroupsStatic((int)$this->context->customer->id));
        $id_product = (int)Tools::getValue('id_product', 0);
        $id_category = (int)Tools::getValue('id_category', 0);
        $id_lang = (int)$this->context->language->id;
        return 'blockcategories|'.(int)Tools::usingSecureMode().'|'.$this->context->shop->id.'|'.$groups.'|'.$id_lang.'|'.$id_product.'|'.$id_category;
    }

    //get category tree
    public function getTree($resultParents, $resultIds, $maxDepth, $id_category = null, $currentDepth = 0)
    {
        if (is_null($id_category))
            $id_category = $this->context->shop->getCategory();

        $children = array();
        if (isset($resultParents[$id_category]) && count($resultParents[$id_category]) && ($maxDepth == 0 || $currentDepth < $maxDepth))
            foreach ($resultParents[$id_category] as $subcat)
                $children[] = $this->getTree($resultParents, $resultIds, $maxDepth, $subcat['id_category'], $currentDepth + 1);
        if (!isset($resultIds[$id_category]))
            return false;
        $return = array('id' => $id_category, 'link' => $this->context->link->getCategoryLink($id_category, $resultIds[$id_category]['link_rewrite']),
            'name' => $resultIds[$id_category]['name'], 'desc'=> $resultIds[$id_category]['description'],
            'children' => $children);
        return $return;
    }*/

	/**
	 * Get the blocklayered filters
	 *
	 * @return array $selectedFilters list of filters
	 */
	private function getSelectedFilters()
	{
		$id_parent = (int)Tools::getValue('id_category', Tools::getValue('id_category_layered', 1));
		if ($id_parent == 1)
			return;

		// Force attributes selection (by url '.../2-mycategory/color-blue' or by get parameter 'selected_filters')
		if (basename($_SERVER['SCRIPT_FILENAME'], 'xhr.php') === false || Tools::getValue('selected_filters') !== false)
		{
			if (Tools::getValue('selected_filters'))
				$url = Tools::getValue('selected_filters');
			else
				$url = preg_replace('/\/(?:\w*)\/(?:[0-9]+[-\w]*)([^\?]*)\??.*/', '$1', Tools::safeOutput($_SERVER['REQUEST_URI'], true));

			$url_attributes = explode('/', ltrim($url, '/'));
			$selected_filters = array('category' => array());
			if (!empty($url_attributes))
			{
				foreach ($url_attributes as $url_attribute)
				{
					$url_parameters = explode('-', $url_attribute);
					$attribute_name  = array_shift($url_parameters);
					if ($attribute_name == 'page')
						$this->page = (int)$url_parameters[0];
					elseif (in_array($attribute_name, array('price', 'weight')))
						$selected_filters[$attribute_name] = array($url_parameters[0], $url_parameters[1]);
					else
					{
						foreach ($url_parameters as $url_parameter)
						{
							$data = Db::getInstance()->getValue('SELECT data FROM `'._DB_PREFIX_.'layered_friendly_url` WHERE `url_key` = \''.md5('/'.$attribute_name.'-'.$url_parameter).'\'');
							if ($data)
								foreach (self::unSerialize($data) as $key_params => $params)
								{
									if (!isset($selected_filters[$key_params]))
										$selected_filters[$key_params] = array();
									foreach ($params as $key_param => $param)
									{
										if (!isset($selected_filters[$key_params][$key_param]))
											$selected_filters[$key_params][$key_param] = array();
										$selected_filters[$key_params][$key_param] = $param;
									}
								}
						}
					}
				}
				return $selected_filters;
			}
		}

		/* Analyze all the filters selected by the user and store them into a tab */
		$selected_filters = array('category' => array(), 'manufacturer' => array(), 'quantity' => array(), 'condition' => array());
		foreach ($_GET as $key => $value)
			if (substr($key, 0, 8) == 'layered_')
			{
				preg_match('/^(.*)_([0-9]+|new|used|refurbished|slider)$/', substr($key, 8, strlen($key) - 8), $res);
				if (isset($res[1]))
				{
					$tmp_tab = explode('_', $value);
					$value = $tmp_tab[0];
					$id_key = false;
					if (isset($tmp_tab[1]))
						$id_key = $tmp_tab[1];
					if ($res[1] == 'condition' && in_array($value, array('new', 'used', 'refurbished')))
						$selected_filters['condition'][] = $value;
					elseif ($res[1] == 'quantity_all_versions' && (!$value || $value == 1))
						$selected_filters['quantity_all_versions'][] = $value;
					elseif (in_array($res[1], array('category', 'manufacturer')))
					{
						if (!isset($selected_filters[$res[1].($id_key ? '_'.$id_key : '')]))
							$selected_filters[$res[1].($id_key ? '_'.$id_key : '')] = array();
						$selected_filters[$res[1].($id_key ? '_'.$id_key : '')][] = (int)$value;
					}
					elseif (in_array($res[1], array('id_attribute_group', 'id_feature')))
					{
						if (!isset($selected_filters[$res[1]]))
							$selected_filters[$res[1]] = array();
						$selected_filters[$res[1]][(int)$value] = $id_key.'_'.(int)$value;
					}
					elseif ($res[1] == 'weight')
						$selected_filters[$res[1]] = $tmp_tab;
					elseif ($res[1] == 'price')
						$selected_filters[$res[1]] = $tmp_tab;
				}
			}
		return $selected_filters;
	}

	/**
	 * BO main function
	 *
	 * @return string Html
	 */
	public function getContent()
	{
		// Web site id verification for older version
		if(!$this->oPrediggoConfig->web_site_id_checked
		&& !Configuration::hasKey('PREDIGGO_WEB_SITE_ID_CHECKED'))
			$this->checkWebSiteId();

		if (count($_POST))
			$this->_postProcess();

		// Check Intermediary Database
		$this->checkModuleConstraints();

		// Display forms
		$this->_displayForm();

		return $this->_html;
	}

	/**
	 * Check the client web site id
	 */
	private function checkWebSiteId()
	{
		$this->oPrediggoConfig->web_site_id_checked = (int)$this->oPrediggoCallController->checkWebSiteId();
		if($this->oPrediggoConfig->web_site_id_checked == true)
			Configuration::updateValue('PREDIGGO_CONFIGURATION_OK', true);

		if(!$this->oPrediggoConfig->save())
			$this->_errors[] = $this->l('An error occurred while updating the main configuration settings');
	}

	/**
	 * Check the server configuration variables
	 */
	public function checkModuleConstraints()
	{
		if(!extension_loaded('dom'))
			$this->_errors[] = $this->l('Please activate the PHP extension "DOM" to allow the use of the module.');

		if(!extension_loaded('curl'))
			$this->_errors[] = $this->l('Please activate the PHP extension "curl" to allow the use of the module.');

		if(!$this->oPrediggoConfig->web_site_id_checked)
			$this->_warnings[] = $this->l('Please update the field "Web Site ID", in the "Main Configuration" tab.');

		// API can't be call if curl extension is not installed on PHP config.
		if((int)ini_get('max_execution_time') < 3000)
			$this->_warnings[] = $this->l('Please update the PHP option "max_execution_time" to a minimum of "3000". (Current value : ').(int)ini_get('max_execution_time').$this->l(')');

		if((int)ini_get('max_input_time') < 3000)
			$this->_warnings[] = $this->l('Please update the PHP option "max_input_time" to a minimum of "3000". (Current value : ').(int)ini_get('max_input_time').$this->l(')');

		if((int)ini_get('memory_limit') < 384)
			$this->_warnings[] = $this->l('Please update the PHP option "memory_limit" to a minimum of "384M". (Current value : ').ini_get('memory_limit').$this->l(')');

		if((int)(Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP))
			$this->_warnings[] = $this->l('Please select a shop on the top block to configure the specific settings.');
	}

    private function checkServerCheck(){
        $this->oPrediggoConfig->server_url_check 			= Tools::safeOutput(Tools::getValue('server_url_check'));
    }

	/**
	 * Set the data once an updated is processed in the BO
	 */
	private function _postProcess()
	{
		// Set the main configuration
		if(Tools::isSubmit('mainConfSubmit'))
		{
            $this->oPrediggoConfig->shop_name                           = Tools::safeOutput(Tools::getValue('shop_name'));
            $this->oPrediggoConfig->token_id         			        = Tools::safeOutput(Tools::getValue('token_id'));
            $this->oPrediggoConfig->gateway_profil_id                         = Tools::safeOutput(Tools::getValue('gateway_profil_id'));
			if($this->oPrediggoConfig->save()){
                $this->checkServerCheck();
				$this->_confirmations[] = $this->l('Main settings updated');
            }
			else
				$this->_errors[] = $this->l('An error occurred while updating the main configuration settings');

		}

        // Set the server configuration
        if(Tools::isSubmit('serverConfSubmit'))
        {
            $this->oPrediggoConfig->web_site_id                         = Tools::safeOutput(Tools::getValue('web_site_id'));
            $this->oPrediggoConfig->server_url_recommendations 			= Tools::safeOutput(Tools::getValue('server_url_recommendations'));

            if($this->oPrediggoConfig->save()) {
                $this->checkWebSiteId();
                $this->_confirmations[] = $this->l('Server settings updated');
            }
            else
                $this->_errors[] = $this->l('An error occurred while updating the export configuration settings');
        }

		// Set the export configuration
		if(Tools::isSubmit('exportConfSubmit'))
		{
			$this->oPrediggoConfig->products_file_generation 			= Tools::safeOutput(Tools::getValue('products_file_generation'));
			$this->oPrediggoConfig->orders_file_generation 				= Tools::safeOutput(Tools::getValue('orders_file_generation'));
			$this->oPrediggoConfig->customers_file_generation 			= Tools::safeOutput(Tools::getValue('customers_file_generation'));
			$this->oPrediggoConfig->export_product_image 				= Tools::safeOutput(Tools::getValue('export_product_image'));
			$this->oPrediggoConfig->export_product_description 			= Tools::safeOutput(Tools::getValue('export_product_description'));
			$this->oPrediggoConfig->export_product_min_quantity 		= Tools::safeOutput(Tools::getValue('export_product_min_quantity'));
			$this->oPrediggoConfig->nb_days_order_valide 				= Tools::safeOutput(Tools::getValue('nb_days_order_valide'));
			$this->oPrediggoConfig->nb_days_customer_last_visit_valide 	= Tools::safeOutput(Tools::getValue('nb_days_customer_last_visit_valide'));
			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Export settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the export configuration settings');
		}

		// Set the export configuration
		if(Tools::isSubmit('logsSubmit'))
		{
            $this->oPrediggoConfig->logs_generation 				= (int)Tools::safeOutput(Tools::getValue('logs_generation'));

            if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Logs settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the Logs configuration settings');
		}



		// Launch the file export
		if(Tools::isSubmit('manualExportSubmit')
		&& !sizeof($this->_errors))
		{
			$this->oDataExtractorController->launchExport();
		}

        // Launch the file Import
        if(Tools::isSubmit('ClientConfigurationImportSubmit')
            && !sizeof($this->_errors))
        {
            if($this->oPrediggoConfig->save()){
                $location = _PS_MODULE_DIR_.'prediggo/xmlfiles/import.sql';
                $location2 = _PS_MODULE_DIR_.'prediggo/xmlfiles/import2.sql';
                 if(copy($_FILES['Import']['tmp_name'],$location) and copy($_FILES['Import2']['tmp_name'],$location2)) {
                     $this->oPrediggoCallController->import_client_config2();
                     $this->oPrediggoCallController->import_client_config();
                 }
                 else
                     $this->_errors[] = $this->l('An error occurred while importing the client configuration');
            }
            else
                $this->_errors[] = $this->l('An error occurred while importing the client configuration');
        }

        // Launch the configuration export
        if(Tools::isSubmit('ClientConfigurationExportSubmit')
            && !sizeof($this->_errors))
        {
            if($this->oPrediggoConfig->save())
                $this->oPrediggoCallController->export_client_config();
            else
                $this->_errors[] = $this->l('An error occurred while exporting the client configuration');
        }

		// Set the export attributes
		if(Tools::isSubmit('exportPrediggoAttributesSubmit'))
		{
			if(is_array(Tools::getValue('attributes_groups_ids')))
				$this->oPrediggoConfig->attributes_groups_ids 	= Tools::safeOutput(join(',', array_map('intval',Tools::getValue('attributes_groups_ids'))));

			if(is_array(Tools::getValue('features_ids')))
				$this->oPrediggoConfig->features_ids 	= Tools::safeOutput(join(',', array_map('intval',Tools::getValue('features_ids'))));

			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Product attributes settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the product attributes configuration settings');
		}

		// Set the black list of recommendations
		if(Tools::isSubmit('exportNotRecoSubmit'))
		{
			$this->oPrediggoConfig->products_ids_not_recommendable = Tools::safeOutput(substr(Tools::getValue('input_products_ids_not_recommendable'), 0, -1));

			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Black list of recommendations updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the black list of recommendations');
		}

		// Set the black list of recommendations
		if(Tools::isSubmit('exportNotSearchSubmit'))
		{
			$this->oPrediggoConfig->products_ids_not_searchable = Tools::safeOutput(substr(Tools::getValue('input_products_ids_not_searchable'), 0, -1));

			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Black list of searchs updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the black list of searchs');
		}

		// Set the protection configuration
		if(Tools::isSubmit('exportProtectionConfSubmit'))
		{
			if($aIds = $this->oDataExtractorController->setRepositoryProtection(Tools::getValue('htpasswd_user'), Tools::getValue('htpasswd_pwd')))
			{
				if(empty($aIds['user']))
					$this->_confirmations[] = $this->l('Protection has been disactivated');
				else
					$this->_confirmations[] = $this->l('Protection has been activated');

				$this->oPrediggoConfig->htpasswd_user 	= Tools::safeOutput(Tools::getValue('htpasswd_user'));
				$this->oPrediggoConfig->htpasswd_pwd 	= Tools::safeOutput(Tools::getValue('htpasswd_pwd'));
				if($this->oPrediggoConfig->save())
					$this->_confirmations[] = $this->l('Protection settings updated');
				else
					$this->_errors[] = $this->l('An error occurred while updating the protection configuration settings');
			}
			else
				$this->_errors[] = $this->l('An error occurred when activating the protection');
		}

		// Set the recommendations main configuration
		if(Tools::isSubmit('mainRecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->server_url_recommendations 	= Tools::safeOutput(Tools::getValue('server_url_recommendations'));
			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Recommendations main configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the main configuration of recommendations settings');
		}

        // Set the recommendations main configuration
        if(Tools::isSubmit('registerAllHooks'))
        {
            if($this->oPrediggoConfig->save())
                $this->registerAllHooks();
            else
                $this->_errors[] = $this->l('An error occurred while launching the register of all Hooks');
        }

        // Set the homepage recommendations block configuration
        if(Tools::isSubmit('HookConfigurationSubmit'))
        {
            $this->oPrediggoConfig->hook_left_column = (int)Tools::safeOutput(Tools::getValue('hook_left_column'));
            $this->oPrediggoConfig->hook_right_column = (int)Tools::safeOutput(Tools::getValue('hook_right_column'));
            $this->oPrediggoConfig->hook_footer = (int)Tools::safeOutput(Tools::getValue('hook_footer'));
            $this->oPrediggoConfig->hook_home = (int)Tools::safeOutput(Tools::getValue('hook_home'));
            $this->oPrediggoConfig->hook_footer_product = (int)Tools::safeOutput(Tools::getValue('hook_footer_product'));
            $this->oPrediggoConfig->hook_left_column_product = (int)Tools::safeOutput(Tools::getValue('hook_left_column_product'));
            $this->oPrediggoConfig->hook_right_column_product = (int)Tools::safeOutput(Tools::getValue('hook_right_column_product'));
            $this->oPrediggoConfig->hook_shopping_cart_footer = (int)Tools::safeOutput(Tools::getValue('hook_shopping_cart_footer'));
            $this->oPrediggoConfig->hook_shopping_cart = (int)Tools::safeOutput(Tools::getValue('hook_shopping_cart'));
            $this->oPrediggoConfig->hook_product_comparison = (int)Tools::safeOutput(Tools::getValue('hook_product_comparison'));
            $this->oPrediggoConfig->hook_order_detail = (int)Tools::safeOutput(Tools::getValue('hook_order_detail'));
            $this->oPrediggoConfig->hook_product_tab = (int)Tools::safeOutput(Tools::getValue('hook_product_tab'));
            $this->oPrediggoConfig->hook_before_carrier = (int)Tools::safeOutput(Tools::getValue('hook_before_carrier'));
            $this->oPrediggoConfig->hook_carrier_list = (int)Tools::safeOutput(Tools::getValue('hook_carrier_list'));

            if($this->oPrediggoConfig->save())
                $this->_confirmations[] = $this->l('Hook configuration settings updated');
            else
                $this->_errors[] = $this->l('An error occurred while updating the hook configuration settings');
        }

		// Set the homepage recommendations block configuration
		if(Tools::isSubmit('exportHome0RecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->home_0_activated 	= Tools::safeOutput(Tools::getValue('home_0_activated'));

			$this->oPrediggoConfig->home_0_nb_items 			= (int)Tools::safeOutput(Tools::getValue('home_0_nb_items'));
			
			$this->oPrediggoConfig->home_0_variant_id 		= (int)Tools::safeOutput(Tools::getValue('home_0_variant_id'));
			
			$this->oPrediggoConfig->home_0_hook_name 			= Tools::safeOutput(Tools::getValue('home_0_hook_name'));

			$this->oPrediggoConfig->home_0_template_name 		= Tools::safeOutput(Tools::getValue('home_0_template_name'));

			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->home_0_block_label[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('home_0_block_label_'.(int)$aLanguage['id_lang']));
	
			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Homepage recommendations block #0 configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the homepage recommendations block configuration of recommendations settings');
		}
		
		if(Tools::isSubmit('exportHome1RecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->home_1_activated 	= Tools::safeOutput(Tools::getValue('home_1_activated'));

			$this->oPrediggoConfig->home_1_nb_items 			= (int)Tools::safeOutput(Tools::getValue('home_1_nb_items'));
			
			$this->oPrediggoConfig->home_1_variant_id 		= (int)Tools::safeOutput(Tools::getValue('home_1_variant_id'));
			
			$this->oPrediggoConfig->home_1_hook_name 			= Tools::safeOutput(Tools::getValue('home_1_hook_name'));

			$this->oPrediggoConfig->home_1_template_name 		= Tools::safeOutput(Tools::getValue('home_1_template_name'));

			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->home_1_block_label[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('home_1_block_label_'.(int)$aLanguage['id_lang']));
	
			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Homepage recommendations block #1 configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the homepage recommendations block configuration of recommendations settings');
		}
		
		// Set the homepage recommendations block configuration bloc  1
		if(Tools::isSubmit('exportAllPage0RecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->allpage_0_activated 	= Tools::safeOutput(Tools::getValue('allpage_0_activated'));

			$this->oPrediggoConfig->allpage_0_nb_items 			= (int)Tools::safeOutput(Tools::getValue('allpage_0_nb_items'));
			
			$this->oPrediggoConfig->allpage_0_variant_id 		= (int)Tools::safeOutput(Tools::getValue('allpage_0_variant_id'));
			
			$this->oPrediggoConfig->allpage_0_hook_name 			= Tools::safeOutput(Tools::getValue('allpage_0_hook_name'));

			$this->oPrediggoConfig->allpage_0_template_name 		= Tools::safeOutput(Tools::getValue('allpage_0_template_name'));

			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->allpage_0_block_label[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('allpage_0_block_label_'.(int)$aLanguage['id_lang']));

			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('All Page #0 recommendations block configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the homepage recommendations block configuration of recommendations settings');
		}
		
		// Set the homepage recommendations block configuration bloc  1
		if(Tools::isSubmit('exportAllPage1RecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->allpage_1_activated 	= Tools::safeOutput(Tools::getValue('allpage_1_activated'));

			$this->oPrediggoConfig->allpage_1_nb_items 			= (int)Tools::safeOutput(Tools::getValue('allpage_1_nb_items'));
			
			$this->oPrediggoConfig->allpage_1_variant_id 		= (int)Tools::safeOutput(Tools::getValue('allpage_1_variant_id'));
			
			$this->oPrediggoConfig->allpage_1_hook_name 			= Tools::safeOutput(Tools::getValue('allpage_1_hook_name'));

			$this->oPrediggoConfig->allpage_1_template_name 		= Tools::safeOutput(Tools::getValue('allpage_1_template_name'));

			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->allpage_1_block_label[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('allpage_1_block_label_'.(int)$aLanguage['id_lang']));

			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('All Page #1 recommendations block configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the homepage recommendations block configuration of recommendations settings');
		}
		
		// Set the homepage recommendations block configuration bloc  1
		if(Tools::isSubmit('exportAllPage2RecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->allpage_2_activated 	= Tools::safeOutput(Tools::getValue('allpage_2_activated'));

			$this->oPrediggoConfig->allpage_2_nb_items 			= (int)Tools::safeOutput(Tools::getValue('allpage_2_nb_items'));
			
			$this->oPrediggoConfig->allpage_2_variant_id 		= (int)Tools::safeOutput(Tools::getValue('allpage_2_variant_id'));
			
			$this->oPrediggoConfig->allpage_2_hook_name 			= Tools::safeOutput(Tools::getValue('allpage_2_hook_name'));

			$this->oPrediggoConfig->allpage_2_template_name 		= Tools::safeOutput(Tools::getValue('allpage_2_template_name'));

			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->allpage_2_block_label[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('allpage_2_block_label_'.(int)$aLanguage['id_lang']));

			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('All Page #2 recommendations block configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the homepage recommendations block configuration of recommendations settings');
		}
		
		// Set the Product recommendations block configuration bloc  1
		if(Tools::isSubmit('exportProductPage0RecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->productpage_0_activated 	= Tools::safeOutput(Tools::getValue('productpage_0_activated'));

			$this->oPrediggoConfig->productpage_0_nb_items 			= (int)Tools::safeOutput(Tools::getValue('productpage_0_nb_items'));
			
			$this->oPrediggoConfig->productpage_0_variant_id 		= (int)Tools::safeOutput(Tools::getValue('productpage_0_variant_id'));
			
			$this->oPrediggoConfig->productpage_0_hook_name 			= Tools::safeOutput(Tools::getValue('productpage_0_hook_name'));

			$this->oPrediggoConfig->productpage_0_template_name 		= Tools::safeOutput(Tools::getValue('productpage_0_template_name'));

			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->productpage_0_block_label[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('productpage_0_block_label_'.(int)$aLanguage['id_lang']));

			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Product Page #0 recommendations block configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the homepage recommendations block configuration of recommendations settings');
		}
		
		// Set the Product recommendations block configuration bloc  1
		if(Tools::isSubmit('exportProductPage1RecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->productpage_1_activated 	= Tools::safeOutput(Tools::getValue('productpage_1_activated'));

			$this->oPrediggoConfig->productpage_1_nb_items 			= (int)Tools::safeOutput(Tools::getValue('productpage_1_nb_items'));
			
			$this->oPrediggoConfig->productpage_1_variant_id 		= (int)Tools::safeOutput(Tools::getValue('productpage_1_variant_id'));
			
			$this->oPrediggoConfig->productpage_1_hook_name 			= Tools::safeOutput(Tools::getValue('productpage_1_hook_name'));

			$this->oPrediggoConfig->productpage_1_template_name 		= Tools::safeOutput(Tools::getValue('productpage_1_template_name'));

			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->productpage_1_block_label[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('productpage_1_block_label_'.(int)$aLanguage['id_lang']));

			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Product Page #1 recommendations block configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the homepage recommendations block configuration of recommendations settings');
		}
		
		// Set the Product recommendations block configuration bloc  1
		if(Tools::isSubmit('exportProductPage2RecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->productpage_2_activated 	= Tools::safeOutput(Tools::getValue('productpage_2_activated'));

			$this->oPrediggoConfig->productpage_2_nb_items 			= (int)Tools::safeOutput(Tools::getValue('productpage_2_nb_items'));
			
			$this->oPrediggoConfig->productpage_2_variant_id 		= (int)Tools::safeOutput(Tools::getValue('productpage_2_variant_id'));
			
			$this->oPrediggoConfig->productpage_2_hook_name 			= Tools::safeOutput(Tools::getValue('productpage_2_hook_name'));

			$this->oPrediggoConfig->productpage_2_template_name 		= Tools::safeOutput(Tools::getValue('productpage_2_template_name'));

			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->productpage_2_block_label[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('productpage_2_block_label_'.(int)$aLanguage['id_lang']));

			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Product Page #2 recommendations block configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the homepage recommendations block configuration of recommendations settings');
		}
		
		// Set the basket recommendations block configuration
		if(Tools::isSubmit('exportBasket0RecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->basket_0_activated 	= Tools::safeOutput(Tools::getValue('basket_0_activated'));

			$this->oPrediggoConfig->basket_0_nb_items 			= (int)Tools::safeOutput(Tools::getValue('basket_0_nb_items'));
			
			$this->oPrediggoConfig->basket_0_variant_id 		= (int)Tools::safeOutput(Tools::getValue('basket_0_variant_id'));
			
			$this->oPrediggoConfig->basket_0_hook_name 			= Tools::safeOutput(Tools::getValue('basket_0_hook_name'));

			$this->oPrediggoConfig->basket_0_template_name 		= Tools::safeOutput(Tools::getValue('basket_0_template_name'));

			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->basket_0_block_label[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('basket_0_block_label_'.(int)$aLanguage['id_lang']));
	
			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Basket recommendations block #0 configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the homepage recommendations block configuration of recommendations settings');
		}
		
		if(Tools::isSubmit('exportBasket1RecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->basket_1_activated 	= Tools::safeOutput(Tools::getValue('basket_1_activated'));

			$this->oPrediggoConfig->basket_1_nb_items 			= (int)Tools::safeOutput(Tools::getValue('basket_1_nb_items'));
			
			$this->oPrediggoConfig->basket_1_variant_id 		= (int)Tools::safeOutput(Tools::getValue('basket_1_variant_id'));
			
			$this->oPrediggoConfig->basket_1_hook_name 			= Tools::safeOutput(Tools::getValue('basket_1_hook_name'));

			$this->oPrediggoConfig->basket_1_template_name 		= Tools::safeOutput(Tools::getValue('basket_1_template_name'));

			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->basket_1_block_label[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('basket_1_block_label_'.(int)$aLanguage['id_lang']));
	
			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Basket recommendations block #1 configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the homepage recommendations block configuration of recommendations settings');
		}
		
		// Set the basket recommendations block configuration
		if(Tools::isSubmit('exportBasket2RecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->basket_2_activated 	= Tools::safeOutput(Tools::getValue('basket_2_activated'));

			$this->oPrediggoConfig->basket_2_nb_items 			= (int)Tools::safeOutput(Tools::getValue('basket_2_nb_items'));
			
			$this->oPrediggoConfig->basket_2_variant_id 		= (int)Tools::safeOutput(Tools::getValue('basket_2_variant_id'));
			
			$this->oPrediggoConfig->basket_2_hook_name 			= Tools::safeOutput(Tools::getValue('basket_2_hook_name'));

			$this->oPrediggoConfig->basket_2_template_name 		= Tools::safeOutput(Tools::getValue('basket_2_template_name'));

			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->basket_2_block_label[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('basket_2_block_label_'.(int)$aLanguage['id_lang']));
	
			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Basket recommendations block #2 configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the homepage recommendations block configuration of recommendations settings');
		}
		
		if(Tools::isSubmit('exportBasket3RecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->basket_3_activated 	= Tools::safeOutput(Tools::getValue('basket_3_activated'));

			$this->oPrediggoConfig->basket_3_nb_items 			= (int)Tools::safeOutput(Tools::getValue('basket_3_nb_items'));
			
			$this->oPrediggoConfig->basket_3_variant_id 		= (int)Tools::safeOutput(Tools::getValue('basket_3_variant_id'));
			
			$this->oPrediggoConfig->basket_3_hook_name 			= Tools::safeOutput(Tools::getValue('basket_3_hook_name'));

			$this->oPrediggoConfig->basket_3_template_name 		= Tools::safeOutput(Tools::getValue('basket_3_template_name'));

			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->basket_3_block_label[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('basket_3_block_label_'.(int)$aLanguage['id_lang']));
	
			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Basket recommendations block #3 configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the homepage recommendations block configuration of recommendations settings');
		}
		
		// Set the basket recommendations block configuration
		if(Tools::isSubmit('exportBasket4RecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->basket_4_activated 	= Tools::safeOutput(Tools::getValue('basket_4_activated'));

			$this->oPrediggoConfig->basket_4_nb_items 			= (int)Tools::safeOutput(Tools::getValue('basket_4_nb_items'));
			
			$this->oPrediggoConfig->basket_4_variant_id 		= (int)Tools::safeOutput(Tools::getValue('basket_4_variant_id'));
			
			$this->oPrediggoConfig->basket_4_hook_name 			= Tools::safeOutput(Tools::getValue('basket_4_hook_name'));

			$this->oPrediggoConfig->basket_4_template_name 		= Tools::safeOutput(Tools::getValue('basket_4_template_name'));

			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->basket_4_block_label[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('basket_4_block_label_'.(int)$aLanguage['id_lang']));
	
			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Basket recommendations block #4 configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the homepage recommendations block configuration of recommendations settings');
		}
		
		if(Tools::isSubmit('exportBasket5RecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->basket_5_activated 	= Tools::safeOutput(Tools::getValue('basket_5_activated'));

			$this->oPrediggoConfig->basket_5_nb_items 			= (int)Tools::safeOutput(Tools::getValue('basket_5_nb_items'));
			
			$this->oPrediggoConfig->basket_5_variant_id 		= (int)Tools::safeOutput(Tools::getValue('basket_5_variant_id'));
			
			$this->oPrediggoConfig->basket_5_hook_name 			= Tools::safeOutput(Tools::getValue('basket_5_hook_name'));

			$this->oPrediggoConfig->basket_5_template_name 		= Tools::safeOutput(Tools::getValue('basket_5_template_name'));

			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->basket_5_block_label[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('basket_5_block_label_'.(int)$aLanguage['id_lang']));
	
			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Basket recommendations block #3 configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the homepage recommendations block configuration of recommendations settings');
		}
		
			// Set the Product recommendations block configuration bloc  1
		if(Tools::isSubmit('exportCategory0RecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->category_0_activated 	= Tools::safeOutput(Tools::getValue('category_0_activated'));

			$this->oPrediggoConfig->category_0_nb_items 			= (int)Tools::safeOutput(Tools::getValue('category_0_nb_items'));
			
			$this->oPrediggoConfig->category_0_variant_id 		= (int)Tools::safeOutput(Tools::getValue('category_0_variant_id'));
			
			$this->oPrediggoConfig->category_0_hook_name 			= Tools::safeOutput(Tools::getValue('category_0_hook_name'));

			$this->oPrediggoConfig->category_0_template_name 		= Tools::safeOutput(Tools::getValue('category_0_template_name'));

			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->category_0_block_label[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('category_0_block_label_'.(int)$aLanguage['id_lang']));

			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Category Page #0 recommendations block configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the homepage recommendations block configuration of recommendations settings');
		}
		
		// Set the Product recommendations block configuration bloc  1
		if(Tools::isSubmit('exportCategory1RecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->category_1_activated 	= Tools::safeOutput(Tools::getValue('category_1_activated'));

			$this->oPrediggoConfig->category_1_nb_items 			= (int)Tools::safeOutput(Tools::getValue('category_1_nb_items'));
			
			$this->oPrediggoConfig->category_1_variant_id 		= (int)Tools::safeOutput(Tools::getValue('category_1_variant_id'));
			
			$this->oPrediggoConfig->category_1_hook_name 			= Tools::safeOutput(Tools::getValue('category_1_hook_name'));

			$this->oPrediggoConfig->category_1_template_name 		= Tools::safeOutput(Tools::getValue('category_1_template_name'));

			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->category_1_block_label[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('category_1_block_label_'.(int)$aLanguage['id_lang']));

			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Category Page #1 recommendations block configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the homepage recommendations block configuration of recommendations settings');
		}
		
		// Set the Product recommendations block configuration bloc  1
		if(Tools::isSubmit('exportCategory2RecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->category_2_activated 	= Tools::safeOutput(Tools::getValue('category_2_activated'));

			$this->oPrediggoConfig->category_2_nb_items 			= (int)Tools::safeOutput(Tools::getValue('category_2_nb_items'));
			
			$this->oPrediggoConfig->category_2_variant_id 		= (int)Tools::safeOutput(Tools::getValue('category_2_variant_id'));
			
			$this->oPrediggoConfig->productpage_2_hook_name 			= Tools::safeOutput(Tools::getValue('productpage_2_hook_name'));

			$this->oPrediggoConfig->productpage_2_template_name 		= Tools::safeOutput(Tools::getValue('productpage_2_template_name'));

			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->productpage_2_block_label[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('productpage_2_block_label_'.(int)$aLanguage['id_lang']));

			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Category Page #2 recommendations block configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the homepage recommendations block configuration of recommendations settings');
		}

        // Set the Product recommendations block configuration bloc  1
        if(Tools::isSubmit('exportCustomer0RecommendationConfSubmit'))
        {
            $this->oPrediggoConfig->customer_0_activated 	= Tools::safeOutput(Tools::getValue('customer_0_activated'));

            $this->oPrediggoConfig->customer_0_nb_items 			= (int)Tools::safeOutput(Tools::getValue('customer_0_nb_items'));

            $this->oPrediggoConfig->customer_0_variant_id 		= (int)Tools::safeOutput(Tools::getValue('customer_0_variant_id'));

            $this->oPrediggoConfig->customer_0_hook_name 			= Tools::safeOutput(Tools::getValue('customer_0_hook_name'));

            $this->oPrediggoConfig->customer_0_template_name 		= Tools::safeOutput(Tools::getValue('customer_0_template_name'));

            foreach($this->context->controller->getLanguages() as $aLanguage)
                $this->oPrediggoConfig->customer_0_block_label[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('customer_0_block_label_'.(int)$aLanguage['id_lang']));

            if($this->oPrediggoConfig->save())
                $this->_confirmations[] = $this->l('Customer Page #0 recommendations block configuration settings updated');
            else
                $this->_errors[] = $this->l('An error occurred while updating the homepage recommendations block configuration of recommendations settings');
        }

        // Set the Product recommendations block configuration bloc  1
        if(Tools::isSubmit('exportCustomer1RecommendationConfSubmit'))
        {
            $this->oPrediggoConfig->customer_1_activated 	= Tools::safeOutput(Tools::getValue('customer_1_activated'));

            $this->oPrediggoConfig->customer_1_nb_items 			= (int)Tools::safeOutput(Tools::getValue('customer_1_nb_items'));

            $this->oPrediggoConfig->customer_1_variant_id 		= (int)Tools::safeOutput(Tools::getValue('customer_1_variant_id'));

            $this->oPrediggoConfig->customer_1_hook_name 			= Tools::safeOutput(Tools::getValue('customer_1_hook_name'));

            $this->oPrediggoConfig->customer_1_template_name 		= Tools::safeOutput(Tools::getValue('customer_1_template_name'));

            foreach($this->context->controller->getLanguages() as $aLanguage)
                $this->oPrediggoConfig->customer_1_block_label[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('customer_1_block_label_'.(int)$aLanguage['id_lang']));

            if($this->oPrediggoConfig->save())
                $this->_confirmations[] = $this->l('Customer Page #1 recommendations block configuration settings updated');
            else
                $this->_errors[] = $this->l('An error occurred while updating the homepage recommendations block configuration of recommendations settings');
        }

        /*// Set the customers pages recommendations block configuration
		if(Tools::isSubmit('exportCustomerRecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->customer_recommendations 	= Tools::safeOutput(Tools::getValue('customer_recommendations'));
			$this->oPrediggoConfig->customer_nb_items 			= (int)Tools::safeOutput(Tools::getValue('customer_nb_items'));

			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->customer_block_title[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('customer_block_title_'.(int)$aLanguage['id_lang']));

			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Customers pages recommendations block configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the customers pages recommendations block configuration of recommendations settings');
		}*/


        // Set the 404 page recommendations block configuration
        if(Tools::isSubmit('export404RecommendationConfSubmit'))
        {
            $this->oPrediggoConfig->error_recommendations 	= Tools::safeOutput(Tools::getValue('error_recommendations'));
            $this->oPrediggoConfig->error_nb_items 			= (int)Tools::safeOutput(Tools::getValue('error_nb_items'));

            foreach($this->context->controller->getLanguages() as $aLanguage)
                $this->oPrediggoConfig->error_block_title[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('error_block_title_'.(int)$aLanguage['id_lang']));

            if($this->oPrediggoConfig->save())
                $this->_confirmations[] = $this->l('404 page recommendations block configuration settings updated');
            else
                $this->_errors[] = $this->l('An error occurred while updating the 404 page recommendations block configuration of recommendations settings');
        }

		// Set the blocklayered module recommendations block configuration
		if(Tools::isSubmit('exportBlocklayeredRecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->blocklayered_0_recommendations 	= Tools::safeOutput(Tools::getValue('blocklayered_0_recommendations'));
            $this->oPrediggoConfig->blocklayered_0_nb_items 			= (int)Tools::safeOutput(Tools::getValue('blocklayered_0_nb_items'));
            $this->oPrediggoConfig->blocklayered_0_variant_id 	= Tools::safeOutput(Tools::getValue('blocklayered_0_variant_id'));
            $this->oPrediggoConfig->blocklayered_0_hook_name 	= Tools::safeOutput(Tools::getValue('blocklayered_0_hook_name'));
            $this->oPrediggoConfig->blocklayered_0_template_name 	= Tools::safeOutput(Tools::getValue('blocklayered_0_template_name'));


			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->blocklayered_0_block_title[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('blocklayered_0_block_title_'.(int)$aLanguage['id_lang']));

			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Block layered module recommendations block configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the block layered module recommendations block configuration of recommendations settings');
		}

		// Set the searchs main configuration
		if(Tools::isSubmit('mainSearchConfSubmit'))
		{
			$this->oPrediggoConfig->search_active 					= (int)Tools::safeOutput(Tools::getValue('search_active'));
            $this->oPrediggoConfig->search_main_template_name 		    =      Tools::safeOutput(Tools::getValue('search_main_template_name'));
            $this->oPrediggoConfig->pagination_template_name = Tools::safeOutput(Tools::getValue('pagination_template_name'));
            $this->oPrediggoConfig->search_filter_block_template_name 		    =      Tools::safeOutput(Tools::getValue('search_filter_block_template_name'));
            $this->oPrediggoConfig->search_filters_sort_by_template_name = Tools::safeOutput(Tools::getValue('search_filters_sort_by_template_name'));
            $this->oPrediggoConfig->prod_compare_template_name = Tools::safeOutput(Tools::getValue('prod_compare_template_name'));
            $this->oPrediggoConfig->prod_list_template_name = Tools::safeOutput(Tools::getValue('prod_list_template_name'));
			$this->oPrediggoConfig->searchandizing_active 			= (int)Tools::safeOutput(Tools::getValue('searchandizing_active'));
			$this->oPrediggoConfig->layered_navigation_active 		= (int)Tools::safeOutput(Tools::getValue('layered_navigation_active'));

			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Main configuration settings of searchs updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the main configuration of searchs settings');
		}

        // Set the category main configuration
        if(Tools::isSubmit('mainCategoryConfSubmit'))
        {
            $this->oPrediggoConfig->category_active 					= (int)Tools::safeOutput(Tools::getValue('category_active'));
            $this->oPrediggoConfig->category_0_template_name 		    =      Tools::safeOutput(Tools::getValue('category_0_template_name'));

            if($this->oPrediggoConfig->save())
                $this->_confirmations[] = $this->l('Main configuration settings of Category updated');
            else
                $this->_errors[] = $this->l('An error occurred while updating the main configuration of Category settings');
        }

        // Set the searchs main configuration
        if(Tools::isSubmit('searchandizingSubmit'))
        {
            $this->oPrediggoConfig->searchandizing_active 			= (int)Tools::safeOutput(Tools::getValue('searchandizing_active'));
            $this->oPrediggoConfig->layered_navigation_active 		= (int)Tools::safeOutput(Tools::getValue('layered_navigation_active'));

            if($this->oPrediggoConfig->save())
                $this->_confirmations[] = $this->l('Main configuration settings of searchs updated');
            else
                $this->_errors[] = $this->l('An error occurred while updating the main configuration of searchs settings');
        }

		// Set the searchs autocompletion configuration
		if(Tools::isSubmit('exportSearchAutocompletionConfSubmit'))
		{
			$this->oPrediggoConfig->autocompletion_active 		= (int)Tools::safeOutput(Tools::getValue('autocompletion_active'));
            $this->oPrediggoConfig->search_nb_min_chars         = (int)Tools::safeOutput(Tools::getValue('search_nb_min_chars'));
			$this->oPrediggoConfig->autocompletion_nb_items 	= (int)Tools::safeOutput(Tools::getValue('autocompletion_nb_items'));
            $this->oPrediggoConfig->search_0_template_name 		    =      Tools::safeOutput(Tools::getValue('search_0_template_name'));
            $this->oPrediggoConfig->autoc_template_name         =      Tools::safeOutput(Tools::getValue('autoc_template_name'));
            $this->oPrediggoConfig->autop_template_name         =      Tools::safeOutput(Tools::getValue('autop_template_name'));
            $this->oPrediggoConfig->autocat_template_name         =      Tools::safeOutput(Tools::getValue('autocat_template_name'));
            $this->oPrediggoConfig->autos_template_name         =      Tools::safeOutput(Tools::getValue('autos_template_name'));

			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->suggest_words[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('suggest_words_'.(int)$aLanguage['id_lang']));

			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Main configuration settings of search autocompletion updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the main configuration of search autocompletion settings');
		}
	}

	/**
	 * Display the BO html with smart templates
	 */
	public function _displayForm()
	{
		// Add the specific jquery ui plugins, module JS & CSS
		$this->context->controller->addJqueryUI('ui.tabs');
		$this->context->controller->addJqueryPlugin('autocomplete');
		$this->context->controller->addJs(($this->_path).'js/admin/'.$this->name.'.js');
		$this->context->controller->addCss(array(
			($this->_path).'css/admin/'.$this->name.'.css' => 'all',
			_PS_JS_DIR_.'jquery/ui/themes/base/jquery.ui.all.css'
		));

		/* Display the errors / warnings / confirmations */
		$this->context->smarty->assign(array(
			'aPrediggoWarnings' 		=> $this->_warnings,
			'aPrediggoConfirmations' 	=> $this->_confirmations,
			'aPrediggoErrors' 			=> $this->_errors,
			'path'						=> $this->_path,
			'lang_iso' 					=> $this->context->language->iso_code,
		));

		// Display the errors
		$this->_html .= $this->display(__FILE__, 'views/templates/admin/errors.tpl');

		// Display the tabs
		$this->_html .= $this->display(__FILE__, 'views/templates/admin/tabs.tpl');

		$this->_display = 'index';

		$iShopContext = (int)(Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP);

		/*
		 * MAIN CONFIGURATION
		 */
		$this->fields_form['main_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Main Configuration'),
				'image' => _PS_ADMIN_IMG_.'employee.gif'
			),
			'input' => array(
                array(
                    'label' 	=> $this->l('Shop Name'),
                    'type' 		=> 'text',
                    'name' 		=> 'shop_name',
                    'size'		=> 50,
                    'required'	=> true,
                    'desc' 		=> $this->l('Shop Name of the current shop')
                ),
				array(
					'label' 	=> $this->l('Token ID'),
					'type' 		=> 'text',
					'name' 		=> 'token_id',
					'size'		=> 50,
                    'required'	=> true,
					'desc' 		=> $this->l('Token ID of the current shop')
				),
                array(
                    'label' 	=> $this->l('Profil ID'),
                    'type' 		=> 'text',
                    'name' 		=> 'gateway_profil_id',
                    'size'		=> 50,
                    'required'	=> true,
                    'desc' 		=> $this->l('Profile ID of the current shop')
                ),
				array(
					'type' 		=> 'button',
					'name' 		=> 'mainConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
				),
			),
		);

        $this->fields_value['shop_name'] 			= $this->oPrediggoConfig->shop_name;
        $this->fields_value['token_id'] 			= $this->oPrediggoConfig->token_id;
		$this->fields_value['gateway_profil_id'] 	= (int)$this->context->shop->id;

        /*
		 * SERVER CONFIGURATION
		 */
        $this->fields_form['server_conf']['form'] = array(
            'legend' => array(
                'title' => $this->l('Server Configuration'),
                'image' => _PS_ADMIN_IMG_.'manufacturers.gif'
            ),
            'input' => array(
                array(
                    'label' 	=> $this->l('Web Site ID'),
                    'type' 		=> 'text',
                    'name' 		=> 'web_site_id',
                    'size'		=> 50,
                    'required'	=> true,
                    'desc' 		=> $this->l('Login of the Prediggo solution')
                ),
                array(
                    'label' 	=> $this->l('URL of prediggo server:'),
                    'type' 		=> 'text',
                    'name' 		=> 'server_url_recommendations',
                    'size'		=> 50,
                    'required'	=> true,
                    'desc' 		=> $this->l('Url called to get the prediggo solution'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
                array(
                    'type' 		=> 'button',
                    'name' 		=> 'serverConfSubmit',
                    'class' 	=> 'button',
                    'title' 	=> $this->l('   Save   '),
                ),
            ),
        );

        $this->fields_value['web_site_id'] 			        = $this->oPrediggoConfig->web_site_id;
        $this->fields_value['server_url_recommendations'] 	= $this->oPrediggoConfig->server_url_recommendations;

        /*
        * IMPORT/EXPORT CLIENT CONFIGURATION
        */
        $this->fields_form['client_import_export_configuration']['form'] = array(
            'legend' => array(
                'title' => $this->l('Import/Export client configuration'),
                'image' => _PS_ADMIN_IMG_.'quick.gif'
            ),
            'input' => array(
                array(
                    'label' 	=> $this->l('Export Configuration:'),
                    'type' 		=> 'button',
                    'name' 		=> 'ClientConfigurationExportSubmit',
                    'class' 	=> 'button',
                    'title' 	=> $this->l('   Export Configuration   '),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
                array(
                    'label' => $this->l('Reload Configuration File:'),
                    'type' 	=> 'file',
                    'name' 	=> 'Import',
                ),
                array(
                    'label' => $this->l('Reload Configuration_lang File:'),
                    'type' 	=> 'file',
                    'name' 	=> 'Import2',
                ),
                'disabled'	=> ((int)($iShopContext)?'disabled':''),
                array(
                    'label' 	=> $this->l('Reload Configuration:'),
                    'type' 		=> 'button',
                    'name' 		=> 'ClientConfigurationImportSubmit',
                    'class' 	=> 'button',
                    'title' 	=> $this->l('   Reload Configuration   '),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
                array(
                    'label' 	=> $this->l('Reset All Hooks:'),
                    'type' 		=> 'button',
                    'name' 		=> 'registerAllHooks',
                    'class' 	=> 'button',
                    'title' 	=> $this->l('   Reset All Hooks   '),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
            ),
        );

        /*
         * LOG CONFIGURATION
         */
        $this->fields_form['logs_configuration']['form'] = array(
            'legend' => array(
                'title' => $this->l('Logs configuration'),
                'image' => _PS_ADMIN_IMG_.'quick.gif'
            ),
            'input' => array(
                array(
                    'label' 	=> $this->l('Logs activation'),
                    'type' 		=> 'radio',
                    'name' 		=> 'logs_generation',
                    'class' 	=> 't',
                    'is_bool' 	=> true,
                    'values' 	=> array(
                        array(
                            'id' 	=> 'logs_generation_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' 	=> 'logs_generation_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        ),
                    ),
                    'required'	=> true,
                    'desc' 		=> $this->l('The logs files are stored in the folder "logs" of the module'),
                ),
            array(
                'type' 		=> 'button',
                'name' 		=> 'logsSubmit',
                'class' 	=> 'button',
                'title' 	=> $this->l('   Save   '),
                'disabled'	=> ((int)($iShopContext)?'disabled':'')
            ),
            ),
        );

        $this->fields_value['logs_generation'] 				        = (int)$this->oPrediggoConfig->logs_generation;

		/*
		 * EXPORT MAIN CONFIGURATION
		 */
		$sCronFilePath	= Tools::getShopDomain(true).str_replace(_PS_ROOT_DIR_.'/', __PS_BASE_URI__, $this->_path).'crons/export.php?token='.Tools::getAdminToken('DataExtractorController');

		$this->fields_form['export_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Export Configuration'),
				'image' => _PS_ADMIN_IMG_.'cog.gif'
			),
			'input' => array(
				array(
					'label' 	=> $this->l('Products file generation activation'),
					'type' 		=> 'radio',
					'name' 		=> 'products_file_generation',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'products_file_generation_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'products_file_generation_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Define if the products can be exported into a zip file for the use of the prediggo solution'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Orders file generation activation'),
					'type' 		=> 'radio',
					'name' 		=> 'orders_file_generation',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'orders_file_generation_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'orders_file_generation_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Define if the orders can be exported into a zip file for the use of the prediggo solution'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Customers file generation activation'),
					'type' 		=> 'radio',
					'name' 		=> 'customers_file_generation',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'customers_file_generation_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'customers_file_generation_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Define if the customers can be exported into a zip file for the use of the prediggo solution'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Product\'s image cover export activation'),
					'type' 		=> 'radio',
					'name' 		=> 'export_product_image',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'export_product_image_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'export_product_image_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Define if the product\'s image covers are included in the export of the products'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Product\'s description export activation'),
					'type' 		=> 'radio',
					'name' 		=> 'export_product_description',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'export_product_description_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'export_product_description_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Define if the product\'s descriptions are included in the export of the products'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Product minimum quantity:'),
					'type' 		=> 'text',
					'name' 		=> 'export_product_min_quantity',
					'required'	=> true,
					'desc' 		=> $this->l('Minimum quantity of a product to be exported'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of days considering that an order can be exported:'),
					'type' 		=> 'text',
					'name' 		=> 'nb_days_order_valide',
					'required'	=> true,
					'desc' 		=> $this->l('Number of days to select orders into the export by their date of creation'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of days considering that a customer can be exported:'),
					'type' 		=> 'text',
					'name' 		=> 'nb_days_customer_last_visit_valide',
					'required'	=> true,
					'desc' 		=> $this->l('Number of days to select customers into the export by their date of creation'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'hint',
					'name' 		=> 'protection_xml_path',
					'content' 	=> $this->l('If you want to execute the export by a cron, use the following link:').
									' <a href="'.$sCronFilePath.'">'.$sCronFilePath.'</a>.',
				),
				array(
					'label' 	=> $this->l('Launch the files export by clicking on the following button:'),
					'type' 		=> 'button',
					'name' 		=> 'manualExportSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Export files   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':'')
				),
			)
		);

		$this->fields_value['products_file_generation'] 			= (int)$this->oPrediggoConfig->products_file_generation;
		$this->fields_value['orders_file_generation'] 				= (int)$this->oPrediggoConfig->orders_file_generation;
		$this->fields_value['customers_file_generation'] 			= (int)$this->oPrediggoConfig->customers_file_generation;
		$this->fields_value['export_product_image'] 				= (int)$this->oPrediggoConfig->export_product_image;
		$this->fields_value['export_product_description'] 			= (int)$this->oPrediggoConfig->export_product_description;
		$this->fields_value['export_product_min_quantity'] 			= (int)$this->oPrediggoConfig->export_product_min_quantity;
		$this->fields_value['nb_days_order_valide']			 		= (int)$this->oPrediggoConfig->nb_days_order_valide;
		$this->fields_value['nb_days_customer_last_visit_valide'] 	= (int)$this->oPrediggoConfig->nb_days_customer_last_visit_valide;

		/*
		 * ATTRIBUTE SELECTION
		 */
		$this->fields_form['attributes_selection']['form'] = array(
			'legend' => array(
				'title' => $this->l('Prediggo attributes selection'),
				'image' => _PS_ADMIN_IMG_.'quick.gif'
			),
			'input' => array(
				array(
					'label' => $this->l('Attributes:'),
					'type' 	=> 'attribute_selector',
					'name' 	=> 'attribute_selector',
					'names' 	=> array(
						'attributes' 	=> 'attributes_groups_ids[]',
						'features'		=> 'features_ids[]'
					),
					'values' 	=> array(
						'attributes' 	=> AttributeGroup::getAttributesGroups((int)$this->context->cookie->id_lang),
						'features'		=> Feature::getFeatures((int)$this->context->cookie->id_lang)
					),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportPrediggoAttributesSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save the prediggo attributes   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);

		$this->fields_value['attribute_selector'] 	= array(
			'attributes' 	=> explode(',',$this->oPrediggoConfig->attributes_groups_ids),
			'features'		=> explode(',',$this->oPrediggoConfig->features_ids),
		);

		/*
		 * RECOMMANDATIONS BLACK LIST
		 */
		$this->fields_form['black_list_reco']['form'] = array(
			'legend' => array(
				'title' => $this->l('Products not included in the recommendations'),
				'image' => _PS_ADMIN_IMG_.'nav-logout.gif'
			),
			'input' => array(
				array(
					'label' 	=> $this->l('Product:'),
					'type' 		=> 'autocomplete',
					'name' 		=> 'products_ids_not_recommendable',
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportNotRecoSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save the black list   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);

		$this->fields_value['products_ids_not_recommendable'] = array();
		if(!empty($this->oPrediggoConfig->products_ids_not_recommendable))
			foreach(explode(',',$this->oPrediggoConfig->products_ids_not_recommendable) as $iID)
			{
				$oProduct = new Product((int)$iID, false, (int)$this->context->cookie->id_lang, (int)$this->context->shop->id, $this->context);
				$this->fields_value['products_ids_not_recommendable'][] = array(
					'id' 	=> (int)$iID,
					'name' 	=> $oProduct->name
				);
				unset($oProduct);
			}

		/*
		 * SEARCH BLACK LIST
		 */
		$this->fields_form['black_list_search']['form'] = array(
				'legend' => array(
					'title' => $this->l('Products not included in the searchs'),
					'image' => _PS_ADMIN_IMG_.'nav-logout.gif'
				),
				'input' => array(
					array(
						'label' 	=> $this->l('Product:'),
						'type' 		=> 'autocomplete',
						'name' 		=> 'products_ids_not_searchable',
						'disabled'	=> ((int)($iShopContext)?'disabled':''),
					),
					array(
						'type' 		=> 'button',
						'name' 		=> 'exportNotSearchSubmit',
						'class' 	=> 'button',
						'title' 	=> $this->l('   Save the black list   '),
						'disabled'	=> ((int)($iShopContext)?'disabled':''),
					),
				),
		);

		$this->fields_value['products_ids_not_searchable'] = array();
		if(!empty($this->oPrediggoConfig->products_ids_not_searchable))
			foreach(explode(',',$this->oPrediggoConfig->products_ids_not_searchable) as $iID)
			{
				$oProduct = new Product((int)$iID, false, (int)$this->context->cookie->id_lang, (int)$this->context->shop->id, $this->context);
				$this->fields_value['products_ids_not_searchable'][] = array(
					'id' 	=> (int)$iID,
					'name' 	=> $oProduct->name
				);
				unset($oProduct);
			}

		/*
		 * HTACCESS PROTECTION
		 */
		$sExportRepositoryPath	= Tools::getShopDomain(true).str_replace(_PS_ROOT_DIR_.'/', __PS_BASE_URI__, $this->_path).'xmlfiles/';

		$this->fields_form['htaccess_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Export File protection'),
				'image' => _PS_ADMIN_IMG_.'access.png'
			),
			'input' => array(
				array(
					'type' 		=> 'hint',
					'name' 		=> 'export_cron',
					'content' 	=> $this->l('Your files are created into your PrestaShop plateform into the following folder :').
					' <a href="'.$sExportRepositoryPath.'">'.$sExportRepositoryPath.'</a>.<br/>'.
					$this->l('The data contained into these files are critical and require a protection.').
					$this->l('Please restrict the access to these files by applying a protection with an authentication requiring a login and a password!').'<br/>'.
					$this->l('The Basic Authentication module is not configured by default on Nginx web server. Please, refer to this documentation to configure it : http://wiki.nginx.org/HttpAuthBasicModule.'),
				),
				array(
					'label' 	=> $this->l('Login:'),
					'type' 		=> 'text',
					'name' 		=> 'htpasswd_user',
					'required'	=> true,
					'desc' 		=> $this->l('Login of the export folder protection')
				),
				array(
					'label' 	=> $this->l('Password:'),
					'type' 		=> 'text',
					'name' 		=> 'htpasswd_pwd',
					'required'	=> true,
					'desc' 		=> $this->l('Password of the export folder protection')
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportProtectionConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
				),
			),
		);

		$this->fields_value['htpasswd_user'] 	= $this->oPrediggoConfig->htpasswd_user;
		$this->fields_value['htpasswd_pwd'] 	= $this->oPrediggoConfig->htpasswd_pwd;


        /*
         * CATEGORY MAIN CONFIGURATION
         */
        /*$this->fields_form['category_conf']['form'] = array(
            'legend' => array(
                'title' => $this->l('Category settings'),
                'image' => _PS_ADMIN_IMG_.'search.gif'
            ),
            'input' => array(
                array(
                    'label' => $this->l('Display the category block:'),
                    'type' 		=> 'radio',
                    'name' 		=> 'category_active',
                    'class' 	=> 't',
                    'is_bool' 	=> true,
                    'values' 	=> array(
                        array(
                            'id' 	=> 'category_active_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' 	=> 'category_active_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        ),
                    ),
                    'required'	=> true,
                    'desc' 		=> $this->l('Enable the category block in the front office'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
                array(
                    'label' 	=> $this->l('Template category:'),
                    'type' 		=> 'text',
                    'size'		=> 100,
                    'name' 		=> 'category_0_template_name',
                    'lang' 		=> false,
                    'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                    'required' => true,
                ),
                array(
                    'type' 		=> 'button',
                    'name' 		=> 'mainCategoryConfSubmit',
                    'class' 	=> 'button',
                    'title' 	=> $this->l('   Save   '),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
            ),
        );

        $this->fields_value['category_active']				= (int)$this->oPrediggoConfig->category_active;
        $this->fields_value['category_0_template_name'] 	    = $this->oPrediggoConfig->category_0_template_name;*/

        /*
        * HOOK CONFIGURATION
        */
		/**
        $this->fields_form['hook_configuration']['form'] = array(
            'legend' => array(
                'title' => $this->l('Hook configuration with variant ID'),
                'image' => _PS_ADMIN_IMG_.'picture.gif'
            ),
            'input' => array(
                array(
                    'label' 	=> $this->l('Hook Display Left Column :'),
                    'type' 		=> 'text',
                    'name' 		=> 'hook_left_column',
                    'desc' 		=> $this->l('Choose the variant ID for the hook Display Left Column'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
                array(
                    'label' 	=> $this->l('Hook Display Right Column :'),
                    'type' 		=> 'text',
                    'name' 		=> 'hook_right_column',
                    'desc' 		=> $this->l('Choose the variant ID for the hook Display Right Column'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
                array(
                    'label' 	=> $this->l('Hook Display Footer :'),
                    'type' 		=> 'text',
                    'name' 		=> 'hook_footer',
                    'desc' 		=> $this->l('Choose the variant ID for the hook Display Footer'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
                array(
                    'label' 	=> $this->l('Hook Display Home :'),
                    'type' 		=> 'text',
                    'name' 		=> 'hook_home',
                    'desc' 		=> $this->l('Choose the variant ID for the hook Display Home'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
                array(
                    'label' 	=> $this->l('Hook Display Footer Product :'),
                    'type' 		=> 'text',
                    'name' 		=> 'hook_footer_product',
                    'desc' 		=> $this->l('Choose the variant ID for the hook Display Footer Product'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
                array(
                    'label' 	=> $this->l('Hook Display Left Column Product :'),
                    'type' 		=> 'text',
                    'name' 		=> 'hook_left_column_product',
                    'desc' 		=> $this->l('Choose the variant ID for the hook Display Left Column Product'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
                array(
                    'label' 	=> $this->l('Hook Display Right Column Product :'),
                    'type' 		=> 'text',
                    'name' 		=> 'hook_right_column_product',
                    'desc' 		=> $this->l('Choose the variant ID for the hook Display Right Column Product'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
                array(
                    'label' 	=> $this->l('Hook Display Shopping Cart Footer :'),
                    'type' 		=> 'text',
                    'name' 		=> 'hook_shopping_cart_footer',
                    'desc' 		=> $this->l('Choose the variant ID for the hook Display Shopping Cart Footer'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
                array(
                    'label' 	=> $this->l('Hook Display Shopping Cart :'),
                    'type' 		=> 'text',
                    'name' 		=> 'hook_shopping_cart',
                    'desc' 		=> $this->l('Choose the variant ID for the hook Display Shopping Cart'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
                array(
                    'label' 	=> $this->l('Hook Display Product Comparison :'),
                    'type' 		=> 'text',
                    'name' 		=> 'hook_product_comparison',
                    'desc' 		=> $this->l('Choose the variant ID for the hook Display Product Comparison'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
                array(
                    'label' 	=> $this->l('Hook Display Order Detail :'),
                    'type' 		=> 'text',
                    'name' 		=> 'hook_order_detail',
                    'desc' 		=> $this->l('Choose the variant ID for the hook Display Order Detail'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
                array(
                    'label' 	=> $this->l('Hook Display Product Tab :'),
                    'type' 		=> 'text',
                    'name' 		=> 'hook_product_tab',
                    'desc' 		=> $this->l('Choose the variant ID for the hook Display Product Tab'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
                array(
                    'label' 	=> $this->l('Hook Display Before Carrier :'),
                    'type' 		=> 'text',
                    'name' 		=> 'hook_before_carrier',
                    'desc' 		=> $this->l('Choose the variant ID for the hook Display Before Carrier'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
                array(
                    'label' 	=> $this->l('Hook Display Carrier List :'),
                    'type' 		=> 'text',
                    'name' 		=> 'hook_carrier_list',
                    'desc' 		=> $this->l('Choose the variant ID for the hook Display Carrier List'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
                array(
                    'type' 		=> 'button',
                    'name' 		=> 'HookConfigurationSubmit',
                    'class' 	=> 'button',
                    'title' 	=> $this->l('   Save   '),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
            ),
        );

        $this->fields_value['hook_left_column'] 		        = (int)$this->oPrediggoConfig->hook_left_column;
        $this->fields_value['hook_right_column'] 		        = (int)$this->oPrediggoConfig->hook_right_column;
        $this->fields_value['hook_footer'] 		                = (int)$this->oPrediggoConfig->hook_footer;
        $this->fields_value['hook_home'] 		                = (int)$this->oPrediggoConfig->hook_home;
        $this->fields_value['hook_footer_product'] 		        = (int)$this->oPrediggoConfig->hook_footer_product;
        $this->fields_value['hook_left_column_product'] 		= (int)$this->oPrediggoConfig->hook_left_column_product;
        $this->fields_value['hook_right_column_product'] 		= (int)$this->oPrediggoConfig->hook_right_column_product;
        $this->fields_value['hook_shopping_cart_footer'] 		= (int)$this->oPrediggoConfig->hook_shopping_cart_footer;
        $this->fields_value['hook_shopping_cart'] 		        = (int)$this->oPrediggoConfig->hook_shopping_cart;
        $this->fields_value['hook_product_comparison'] 		    = property_exists($this->oPrediggoConfig, 'hook_product_comparison')
                                                                    ? (int) $this->oPrediggoConfig->hook_product_comparison
                                                                    : 0;
        $this->fields_value['hook_order_detail'] 		        = (int)$this->oPrediggoConfig->hook_order_detail;
        $this->fields_value['hook_product_tab'] 		        = (int)$this->oPrediggoConfig->hook_product_tab;
        $this->fields_value['hook_before_carrier'] 		        = (int)$this->oPrediggoConfig->hook_before_carrier;
        $this->fields_value['hook_carrier_list'] 		        = (int)$this->oPrediggoConfig->hook_carrier_list;

		*/
		
		
		/*
		 * HOMEPAGE RECOMMENDATIONS - block 0
		 */ 
		 
		$this->fields_form['home_reco_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Home Page Block Configuration - #0'),
				'image' => _PS_ADMIN_IMG_.'picture.gif'
			),
			'input' => array(
				array(
					'label' => $this->l('Display the recommendations block:'),
					'type' 		=> 'radio',
					'name' 		=> 'home_0_activated',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'home_0_activated_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'home_0_activated_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Add a block of recommended products in the homepage of your website'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of items in the recommendations block:'),
					'type' 		=> 'text',
					'name' 		=> 'home_0_nb_items',
					'required' => true,
					'desc' 		=> $this->l('Number of recommended products in the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Variant ID:'),
					'type' 		=> 'text',
					'name' 		=> 'home_0_variant_id',
					'required' => true,
					'desc' 		=> $this->l('Variant ID as defined in the integration guide provided by Prediggo'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Hook:'),
					'type' 	=> 'select',                              // This is a <select> tag.
					'name' 		=> 'home_0_hook_name',
					'desc' 		=> $this->l('Select the position where the block should be displayed'),
					'options' => array(
						'query' => $this->oPrediggoConfig->optionsHooksHomePage,                           // $options contains the data itself.
						'id' => 'id_option',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
						'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
						),
					'required' => true,
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Template Home 0:'),
					'type' 		=> 'text',
					'size'		=> 100,
					'name' 		=> 'home_0_template_name',
					'lang' 		=> false,
					'required' => true,
					'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Title of the recommendation block :'),
					'type' 		=> 'text',
					'size'		=> 50,
					'name' 		=> 'home_0_block_label',
					'lang' 		=> true,
					'desc' 		=> $this->l('Title of the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportHome0RecommendationConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);

		$this->fields_value['home_0_activated'] 		= (int)$this->oPrediggoConfig->home_0_activated;
		$this->fields_value['home_0_nb_items'] 		= (int)$this->oPrediggoConfig->home_0_nb_items;
		$this->fields_value['home_0_variant_id'] 	= (int)$this->oPrediggoConfig->home_0_variant_id;
		$this->fields_value['home_0_hook_name'] 	= $this->oPrediggoConfig->home_0_hook_name;
		$this->fields_value['home_0_template_name'] = $this->oPrediggoConfig->home_0_template_name;
		$this->fields_value['home_0_block_label'] 	= $this->oPrediggoConfig->home_0_block_label;
		
			/*
		 * HOMEPAGE RECOMMENDATIONS - block 1
		 */ 
		 
		$this->fields_form['home_1_reco_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Home Page Block Configuration - #1'),
				'image' => _PS_ADMIN_IMG_.'picture.gif'
			),
			'input' => array(
				array(
					'label' => $this->l('Display the recommendations block:'),
					'type' 		=> 'radio',
					'name' 		=> 'home_1_activated',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'home_1_activated_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'home_1_activated_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Add a block of recommended products in the homepage of your website'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of items in the recommendations block:'),
					'type' 		=> 'text',
					'name' 		=> 'home_1_nb_items',
					'required' => true,
					'desc' 		=> $this->l('Number of recommended products in the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Variant ID:'),
					'type' 		=> 'text',
					'name' 		=> 'home_1_variant_id',
					'required' => true,
					'desc' 		=> $this->l('Variant ID as defined in the integration guide provided by Prediggo'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Hook:'),
					'type' 	=> 'select',                              // This is a <select> tag.
					'name' 		=> 'home_1_hook_name',
					'desc' 		=> $this->l('Select the position where the block should be displayed'),
					'options' => array(
						'query' => $this->oPrediggoConfig->optionsHooksHomePage,                           // $options contains the data itself.
						'id' => 'id_option',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
						'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
						),
					'required' => true,
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Template Home 1:'),
					'type' 		=> 'text',
					'size'		=> 100,
					'name' 		=> 'home_1_template_name',
					'lang' 		=> false,
					'required' => true,
					'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Title of the recommendation block :'),
					'type' 		=> 'text',
					'size'		=> 50,
					'name' 		=> 'home_1_block_label',
					'lang' 		=> true,
					'desc' 		=> $this->l('Title of the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportHome1RecommendationConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);

		$this->fields_value['home_1_activated'] 		= (int)$this->oPrediggoConfig->home_1_activated;
		$this->fields_value['home_1_nb_items'] 		= (int)$this->oPrediggoConfig->home_1_nb_items;
		$this->fields_value['home_1_variant_id'] 	= (int)$this->oPrediggoConfig->home_1_variant_id;
		$this->fields_value['home_1_hook_name'] 	= $this->oPrediggoConfig->home_1_hook_name;
		$this->fields_value['home_1_template_name'] = $this->oPrediggoConfig->home_1_template_name;
		$this->fields_value['home_1_block_label'] 	= $this->oPrediggoConfig->home_1_block_label;
		
		
		/*
		 * ALL PAGE RECOMMENDATIONS - block 0
		 */ 
		 
		$this->fields_form['allpage_0_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('All Page Block Configuration - #0'),
				'image' => _PS_ADMIN_IMG_.'picture.gif'
			),
			'input' => array(
				array(
					'label' => $this->l('Display the recommendations block:'),
					'type' 		=> 'radio',
					'name' 		=> 'allpage_0_activated',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'allpage_0_activated_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'allpage_0_activated_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Add a block of recommended products in all page of your website'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of items in the recommendations block:'),
					'type' 		=> 'text',
					'name' 		=> 'allpage_0_nb_items',
					'desc' 		=> $this->l('Number of recommended products in the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Variant ID:'),
					'type' 		=> 'text',
					'name' 		=> 'allpage_0_variant_id',
					'desc' 		=> $this->l('Variant ID as defined in the integration guide provided by Prediggo'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Hook:'),
					'type' 	=> 'select',                              // This is a <select> tag.
					'name' 		=> 'allpage_0_hook_name',
					'desc' 		=> $this->l('Select the position where the block should be displayed'),
					'options' => array(
						'query' => $this->oPrediggoConfig->optionsHooksAllPage,                           // $options contains the data itself.
						'id' => 'id_option',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
						'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
						),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Template All Page 0:'),
					'type' 		=> 'text',
					'size'		=> 100,
					'name' 		=> 'allpage_0_template_name',
					'lang' 		=> false,
					'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Title of the recommendation block :'),
					'type' 		=> 'text',
					'size'		=> 50,
					'name' 		=> 'allpage_0_block_label',
					'lang' 		=> true,
					'desc' 		=> $this->l('Title of the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportAllPage0RecommendationConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);

		$this->fields_value['allpage_0_activated'] 		= (int)$this->oPrediggoConfig->allpage_0_activated;
		$this->fields_value['allpage_0_nb_items'] 		= (int)$this->oPrediggoConfig->allpage_0_nb_items;
		$this->fields_value['allpage_0_variant_id'] 	= (int)$this->oPrediggoConfig->allpage_0_variant_id;
		$this->fields_value['allpage_0_hook_name'] 	= $this->oPrediggoConfig->allpage_0_hook_name;
		$this->fields_value['allpage_0_template_name'] = $this->oPrediggoConfig->allpage_0_template_name;
		$this->fields_value['allpage_0_block_label'] 	= $this->oPrediggoConfig->allpage_0_block_label;
		
		/*
		 * ALL PAGE RECOMMENDATIONS - block 1
		 */ 
		 
		$this->fields_form['allpage_1_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('All Page Block Configuration - #1'),
				'image' => _PS_ADMIN_IMG_.'picture.gif'
			),
			'input' => array(
				array(
					'label' => $this->l('Display the recommendations block:'),
					'type' 		=> 'radio',
					'name' 		=> 'allpage_1_activated',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'allpage_1_activated_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'allpage_1_activated_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Add a block of recommended products in all page of your website'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of items in the recommendations block:'),
					'type' 		=> 'text',
					'name' 		=> 'allpage_1_nb_items',
					'desc' 		=> $this->l('Number of recommended products in the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Variant ID:'),
					'type' 		=> 'text',
					'name' 		=> 'allpage_1_variant_id',
					'desc' 		=> $this->l('Variant ID as defined in the integration guide provided by Prediggo'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Hook:'),
					'type' 	=> 'select',                              // This is a <select> tag.
					'name' 		=> 'allpage_1_hook_name',
					'desc' 		=> $this->l('Select the position where the block should be displayed'),
					'options' => array(
						'query' => $this->oPrediggoConfig->optionsHooksAllPage,                           // $options contains the data itself.
						'id' => 'id_option',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
						'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
						),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Template All Page 1:'),
					'type' 		=> 'text',
					'size'		=> 100,
					'name' 		=> 'allpage_1_template_name',
					'lang' 		=> false,
					'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Title of the recommendation block :'),
					'type' 		=> 'text',
					'size'		=> 50,
					'name' 		=> 'allpage_1_block_label',
					'lang' 		=> true,
					'desc' 		=> $this->l('Title of the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportAllPage1RecommendationConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);

		$this->fields_value['allpage_1_activated'] 		= (int)$this->oPrediggoConfig->allpage_1_activated;
		$this->fields_value['allpage_1_nb_items'] 		= (int)$this->oPrediggoConfig->allpage_1_nb_items;
		$this->fields_value['allpage_1_variant_id'] 	= (int)$this->oPrediggoConfig->allpage_1_variant_id;
		$this->fields_value['allpage_1_hook_name'] 		= $this->oPrediggoConfig->allpage_1_hook_name;
		$this->fields_value['allpage_1_template_name'] 	= $this->oPrediggoConfig->allpage_1_template_name;
		$this->fields_value['allpage_1_block_label'] 	= $this->oPrediggoConfig->allpage_1_block_label;
		
		/*
		 * ALL PAGE RECOMMENDATIONS - block 2
		 */ 
		 
		$this->fields_form['allpage_2_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('All Page Block Configuration - #2'),
				'image' => _PS_ADMIN_IMG_.'picture.gif'
			),
			'input' => array(
				array(
					'label' => $this->l('Display the recommendations block:'),
					'type' 		=> 'radio',
					'name' 		=> 'allpage_2_activated',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'allpage_2_activated_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'allpage_2_activated_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Add a block of recommended products in all page of your website'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of items in the recommendations block:'),
					'type' 		=> 'text',
					'name' 		=> 'allpage_2_nb_items',
					'desc' 		=> $this->l('Number of recommended products in the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Variant ID:'),
					'type' 		=> 'text',
					'name' 		=> 'allpage_2_variant_id',
					'desc' 		=> $this->l('Variant ID as defined in the integration guide provided by Prediggo'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Hook:'),
					'type' 	=> 'select',                              // This is a <select> tag.
					'name' 		=> 'allpage_2_hook_name',
					'desc' 		=> $this->l('Select the position where the block should be displayed'),
					'options' => array(
						'query' => $this->oPrediggoConfig->optionsHooksAllPage,                           // $options contains the data itself.
						'id' => 'id_option',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
						'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
						),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Template All Page 2:'),
					'type' 		=> 'text',
					'size'		=> 100,
					'name' 		=> 'allpage_2_template_name',
					'lang' 		=> false,
					'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Title of the recommendation block :'),
					'type' 		=> 'text',
					'size'		=> 50,
					'name' 		=> 'allpage_2_block_label',
					'lang' 		=> true,
					'desc' 		=> $this->l('Title of the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportAllPage2RecommendationConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);

		$this->fields_value['allpage_2_activated'] 		= (int)$this->oPrediggoConfig->allpage_2_activated;
		$this->fields_value['allpage_2_nb_items'] 		= (int)$this->oPrediggoConfig->allpage_2_nb_items;
		$this->fields_value['allpage_2_variant_id'] 	= (int)$this->oPrediggoConfig->allpage_2_variant_id;
		$this->fields_value['allpage_2_hook_name'] 		= $this->oPrediggoConfig->allpage_2_hook_name;
		$this->fields_value['allpage_2_template_name'] 	= $this->oPrediggoConfig->allpage_2_template_name;
		$this->fields_value['allpage_2_block_label'] 	= $this->oPrediggoConfig->allpage_2_block_label;
		
		/*
		 * PRODUCT PAGE RECOMMENDATIONS - block 0
		 */  
		$this->fields_form['productpage_0_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Product Page Block Configuration - #0'),
				'image' => _PS_ADMIN_IMG_.'picture.gif'
			),
			'input' => array(
				array(
					'label' => $this->l('Display the recommendations block:'),
					'type' 		=> 'radio',
					'name' 		=> 'productpage_0_activated',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'productpage_0_activated_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'productpage_0_activated_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Add a block of recommended products in the product page of your website'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of items in the recommendations block:'),
					'type' 		=> 'text',
					'name' 		=> 'productpage_0_nb_items',
					'desc' 		=> $this->l('Number of recommended products in the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Variant ID:'),
					'type' 		=> 'text',
					'name' 		=> 'productpage_0_variant_id',
					'desc' 		=> $this->l('Variant ID as defined in the integration guide provided by Prediggo'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Hook:'),
					'type' 	=> 'select',                              // This is a <select> tag.
					'name' 		=> 'productpage_0_hook_name',
					'desc' 		=> $this->l('Select the position where the block should be displayed'),
					'options' => array(
						'query' => $this->oPrediggoConfig->optionsHooksProductPage,                           // $options contains the data itself.
						'id' => 'id_option',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
						'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
						),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Template Product Page 0:'),
					'type' 		=> 'text',
					'size'		=> 100,
					'name' 		=> 'productpage_0_template_name',
					'lang' 		=> false,
					'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Title of the recommendation block :'),
					'type' 		=> 'text',
					'size'		=> 50,
					'name' 		=> 'productpage_0_block_label',
					'lang' 		=> true,
					'desc' 		=> $this->l('Title of the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportProductPage0RecommendationConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);

		$this->fields_value['productpage_0_activated'] 		= (int)$this->oPrediggoConfig->productpage_0_activated;
		$this->fields_value['productpage_0_nb_items'] 		= (int)$this->oPrediggoConfig->productpage_0_nb_items;
		$this->fields_value['productpage_0_variant_id'] 	= (int)$this->oPrediggoConfig->productpage_0_variant_id;
		$this->fields_value['productpage_0_hook_name'] 	= $this->oPrediggoConfig->productpage_0_hook_name;
		$this->fields_value['productpage_0_template_name'] = $this->oPrediggoConfig->productpage_0_template_name;
		$this->fields_value['productpage_0_block_label'] 	= $this->oPrediggoConfig->productpage_0_block_label;
		
		/*
		 * PRODUCT PAGE RECOMMENDATIONS - block 1
		 */ 
		 
		$this->fields_form['productpage_1_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Product Page Block Configuration - #1'),
				'image' => _PS_ADMIN_IMG_.'picture.gif'
			),
			'input' => array(
				array(
					'label' => $this->l('Display the recommendations block:'),
					'type' 		=> 'radio',
					'name' 		=> 'productpage_1_activated',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'productpage_1_activated_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'productpage_1_activated_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Add a block of recommended products in the product page of your website'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of items in the recommendations block:'),
					'type' 		=> 'text',
					'name' 		=> 'productpage_1_nb_items',
					'desc' 		=> $this->l('Number of recommended products in the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Variant ID:'),
					'type' 		=> 'text',
					'name' 		=> 'productpage_1_variant_id',
					'desc' 		=> $this->l('Variant ID as defined in the integration guide provided by Prediggo'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Hook:'),
					'type' 	=> 'select',                              // This is a <select> tag.
					'name' 		=> 'productpage_1_hook_name',
					'desc' 		=> $this->l('Select the position where the block should be displayed'),
					'options' => array(
						'query' => $this->oPrediggoConfig->optionsHooksProductPage,                           // $options contains the data itself.
						'id' => 'id_option',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
						'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
						),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Template Product Page 1:'),
					'type' 		=> 'text',
					'size'		=> 100,
					'name' 		=> 'productpage_1_template_name',
					'lang' 		=> false,
					'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Title of the recommendation block :'),
					'type' 		=> 'text',
					'size'		=> 50,
					'name' 		=> 'productpage_1_block_label',
					'lang' 		=> true,
					'desc' 		=> $this->l('Title of the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportProductPage1RecommendationConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);

		$this->fields_value['productpage_1_activated'] 		= (int)$this->oPrediggoConfig->productpage_1_activated;
		$this->fields_value['productpage_1_nb_items'] 		= (int)$this->oPrediggoConfig->productpage_1_nb_items;
		$this->fields_value['productpage_1_variant_id'] 	= (int)$this->oPrediggoConfig->productpage_1_variant_id;
		$this->fields_value['productpage_1_hook_name'] 		= $this->oPrediggoConfig->productpage_1_hook_name;
		$this->fields_value['productpage_1_template_name'] 	= $this->oPrediggoConfig->productpage_1_template_name;
		$this->fields_value['productpage_1_block_label'] 	= $this->oPrediggoConfig->productpage_1_block_label;
		
		/*
		 * PRODUCT PAGE RECOMMENDATIONS - block 2
		 */ 
		 
		$this->fields_form['productpage_2_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Product Page Block Configuration - #2'),
				'image' => _PS_ADMIN_IMG_.'picture.gif'
			),
			'input' => array(
				array(
					'label' => $this->l('Display the recommendations block:'),
					'type' 		=> 'radio',
					'name' 		=> 'productpage_2_activated',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'productpage_2_activated_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'productpage_2_activated_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Add a block of recommended products in the product page of your website'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of items in the recommendations block:'),
					'type' 		=> 'text',
					'name' 		=> 'productpage_2_nb_items',
					'desc' 		=> $this->l('Number of recommended products in the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Variant ID:'),
					'type' 		=> 'text',
					'name' 		=> 'productpage_2_variant_id',
					'desc' 		=> $this->l('Variant ID as defined in the integration guide provided by Prediggo'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Hook:'),
					'type' 	=> 'select',                              // This is a <select> tag.
					'name' 		=> 'productpage_2_hook_name',
					'desc' 		=> $this->l('Select the position where the block should be displayed'),
					'options' => array(
						'query' => $this->oPrediggoConfig->optionsHooksProductPage,                           // $options contains the data itself.
						'id' => 'id_option',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
						'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
						),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Template Product Page 2:'),
					'type' 		=> 'text',
					'size'		=> 100,
					'name' 		=> 'productpage_2_template_name',
					'lang' 		=> false,
					'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Title of the recommendation block :'),
					'type' 		=> 'text',
					'size'		=> 50,
					'name' 		=> 'productpage_2_block_label',
					'lang' 		=> true,
					'desc' 		=> $this->l('Title of the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportProductPage2RecommendationConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);

		$this->fields_value['productpage_2_activated'] 		= (int)$this->oPrediggoConfig->productpage_2_activated;
		$this->fields_value['productpage_2_nb_items'] 		= (int)$this->oPrediggoConfig->productpage_2_nb_items;
		$this->fields_value['productpage_2_variant_id'] 	= (int)$this->oPrediggoConfig->productpage_2_variant_id;
		$this->fields_value['productpage_2_hook_name'] 		= $this->oPrediggoConfig->productpage_2_hook_name;
		$this->fields_value['productpage_2_template_name'] 	= $this->oPrediggoConfig->productpage_2_template_name;
		$this->fields_value['productpage_2_block_label'] 	= $this->oPrediggoConfig->productpage_2_block_label;
		
		/*
		 * BASKET RECOMMENDATIONS - block 0
		 */ 
		$this->fields_form['basket_0_reco_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Basket Page Block Configuration - #0'),
				'image' => _PS_ADMIN_IMG_.'picture.gif'
			),
			'input' => array(
				array(
					'label' => $this->l('Display the recommendations block:'),
					'type' 		=> 'radio',
					'name' 		=> 'basket_0_activated',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'basket_0_activated_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'basket_0_activated_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Add a block of recommended products in the basket of your website'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of items in the recommendations block:'),
					'type' 		=> 'text',
					'name' 		=> 'basket_0_nb_items',
					'required' => true,
					'desc' 		=> $this->l('Number of recommended products in the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Variant ID:'),
					'type' 		=> 'text',
					'name' 		=> 'basket_0_variant_id',
					'required' => true,
					'desc' 		=> $this->l('Variant ID as defined in the integration guide provided by Prediggo'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Hook:'),
					'type' 	=> 'select',                              // This is a <select> tag.
					'name' 		=> 'basket_0_hook_name',
					'desc' 		=> $this->l('Select the position where the block should be displayed'),
					'options' => array(
						'query' => $this->oPrediggoConfig->optionsHooksBasketPage,                           // $options contains the data itself.
						'id' => 'id_option',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
						'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
						),
					'required' => true,
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Template Basket 0:'),
					'type' 		=> 'text',
					'size'		=> 100,
					'name' 		=> 'basket_0_template_name',
					'lang' 		=> false,
					'required' => true,
					'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Title of the recommendation block :'),
					'type' 		=> 'text',
					'size'		=> 50,
					'name' 		=> 'basket_0_block_label',
					'lang' 		=> true,
					'desc' 		=> $this->l('Title of the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportBasket0RecommendationConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);

		$this->fields_value['basket_0_activated'] 		= (int)$this->oPrediggoConfig->basket_0_activated;
		$this->fields_value['basket_0_nb_items'] 		= (int)$this->oPrediggoConfig->basket_0_nb_items;
		$this->fields_value['basket_0_variant_id'] 	= (int)$this->oPrediggoConfig->basket_0_variant_id;
		$this->fields_value['basket_0_hook_name'] 	= $this->oPrediggoConfig->basket_0_hook_name;
		$this->fields_value['basket_0_template_name'] = $this->oPrediggoConfig->basket_0_template_name;
		$this->fields_value['basket_0_block_label'] 	= $this->oPrediggoConfig->basket_0_block_label;
		
			/*
		 * Basket RECOMMENDATIONS - block 1
		 */ 
		 
		$this->fields_form['basket_1_reco_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Basket Page Block Configuration - #1'),
				'image' => _PS_ADMIN_IMG_.'picture.gif'
			),
			'input' => array(
				array(
					'label' => $this->l('Display the recommendations block:'),
					'type' 		=> 'radio',
					'name' 		=> 'basket_1_activated',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'basket_1_activated_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'basket_1_activated_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Add a block of recommended products in the basket page of your website'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of items in the recommendations block:'),
					'type' 		=> 'text',
					'name' 		=> 'basket_1_nb_items',
					'required' => true,
					'desc' 		=> $this->l('Number of recommended products in the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Variant ID:'),
					'type' 		=> 'text',
					'name' 		=> 'basket_1_variant_id',
					'required' => true,
					'desc' 		=> $this->l('Variant ID as defined in the integration guide provided by Prediggo'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Hook:'),
					'type' 	=> 'select',                              // This is a <select> tag.
					'name' 		=> 'basket_1_hook_name',
					'desc' 		=> $this->l('Select the position where the block should be displayed'),
					'options' => array(
						'query' => $this->oPrediggoConfig->optionsHooksBasketPage,                           // $options contains the data itself.
						'id' => 'id_option',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
						'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
						),
					'required' => true,
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Template basket 1:'),
					'type' 		=> 'text',
					'size'		=> 100,
					'name' 		=> 'basket_1_template_name',
					'lang' 		=> false,
					'required' => true,
					'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Title of the recommendation block :'),
					'type' 		=> 'text',
					'size'		=> 50,
					'name' 		=> 'basket_1_block_label',
					'lang' 		=> true,
					'desc' 		=> $this->l('Title of the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportBasket1RecommendationConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);

		$this->fields_value['basket_1_activated'] 		= (int)$this->oPrediggoConfig->basket_1_activated;
		$this->fields_value['basket_1_nb_items'] 		= (int)$this->oPrediggoConfig->basket_1_nb_items;
		$this->fields_value['basket_1_variant_id'] 	= (int)$this->oPrediggoConfig->basket_1_variant_id;
		$this->fields_value['basket_1_hook_name'] 	= $this->oPrediggoConfig->basket_1_hook_name;
		$this->fields_value['basket_1_template_name'] = $this->oPrediggoConfig->basket_1_template_name;
		$this->fields_value['basket_1_block_label'] 	= $this->oPrediggoConfig->basket_1_block_label;
		
		/*
		 * BASKET RECOMMENDATIONS - block 2
		 */ 
		$this->fields_form['basket_2_reco_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Basket Page Block Configuration - #2'),
				'image' => _PS_ADMIN_IMG_.'picture.gif'
			),
			'input' => array(
				array(
					'label' => $this->l('Display the recommendations block:'),
					'type' 		=> 'radio',
					'name' 		=> 'basket_2_activated',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'basket_2_activated_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'basket_2_activated_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Add a block of recommended products in the basket of your website'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of items in the recommendations block:'),
					'type' 		=> 'text',
					'name' 		=> 'basket_2_nb_items',
					'required' => true,
					'desc' 		=> $this->l('Number of recommended products in the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Variant ID:'),
					'type' 		=> 'text',
					'name' 		=> 'basket_2_variant_id',
					'required' => true,
					'desc' 		=> $this->l('Variant ID as defined in the integration guide provided by Prediggo'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Hook:'),
					'type' 	=> 'select',                              // This is a <select> tag.
					'name' 		=> 'basket_2_hook_name',
					'desc' 		=> $this->l('Select the position where the block should be displayed'),
					'options' => array(
						'query' => $this->oPrediggoConfig->optionsHooksBasketPage,                           // $options contains the data itself.
						'id' => 'id_option',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
						'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
						),
					'required' => true,
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Template basket 2:'),
					'type' 		=> 'text',
					'size'		=> 100,
					'name' 		=> 'basket_2_template_name',
					'lang' 		=> false,
					'required' => true,
					'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Title of the recommendation block :'),
					'type' 		=> 'text',
					'size'		=> 50,
					'name' 		=> 'basket_2_block_label',
					'lang' 		=> true,
					'desc' 		=> $this->l('Title of the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportBasket2RecommendationConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);

		$this->fields_value['basket_2_activated'] 		= (int)$this->oPrediggoConfig->basket_2_activated;
		$this->fields_value['basket_2_nb_items'] 		= (int)$this->oPrediggoConfig->basket_2_nb_items;
		$this->fields_value['basket_2_variant_id'] 	= (int)$this->oPrediggoConfig->basket_2_variant_id;
		$this->fields_value['basket_2_hook_name'] 	= $this->oPrediggoConfig->basket_2_hook_name;
		$this->fields_value['basket_2_template_name'] = $this->oPrediggoConfig->basket_2_template_name;
		$this->fields_value['basket_2_block_label'] 	= $this->oPrediggoConfig->basket_2_block_label;
		
			/*
		 * Basket RECOMMENDATIONS - block 3
		 */ 
		 
		$this->fields_form['basket_3_reco_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Basket Page Block Configuration - #3'),
				'image' => _PS_ADMIN_IMG_.'picture.gif'
			),
			'input' => array(
				array(
					'label' => $this->l('Display the recommendations block:'),
					'type' 		=> 'radio',
					'name' 		=> 'basket_3_activated',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'basket_3_activated_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'basket_3_activated_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Add a block of recommended products in the basket page of your website'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of items in the recommendations block:'),
					'type' 		=> 'text',
					'name' 		=> 'basket_3_nb_items',
					'required' => true,
					'desc' 		=> $this->l('Number of recommended products in the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Variant ID:'),
					'type' 		=> 'text',
					'name' 		=> 'basket_3_variant_id',
					'required' => true,
					'desc' 		=> $this->l('Variant ID as defined in the integration guide provided by Prediggo'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Hook:'),
					'type' 	=> 'select',                              // This is a <select> tag.
					'name' 		=> 'basket_3_hook_name',
					'desc' 		=> $this->l('Select the position where the block should be displayed'),
					'options' => array(
						'query' => $this->oPrediggoConfig->optionsHooksBasketPage,                           // $options contains the data itself.
						'id' => 'id_option',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
						'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
						),
					'required' => true,
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Template basket 3:'),
					'type' 		=> 'text',
					'size'		=> 100,
					'name' 		=> 'basket_3_template_name',
					'lang' 		=> false,
					'required' => true,
					'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Title of the recommendation block :'),
					'type' 		=> 'text',
					'size'		=> 50,
					'name' 		=> 'basket_3_block_label',
					'lang' 		=> true,
					'desc' 		=> $this->l('Title of the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportBasket3RecommendationConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);

		$this->fields_value['basket_3_activated'] 		= (int)$this->oPrediggoConfig->basket_3_activated;
		$this->fields_value['basket_3_nb_items'] 		= (int)$this->oPrediggoConfig->basket_3_nb_items;
		$this->fields_value['basket_3_variant_id'] 	= (int)$this->oPrediggoConfig->basket_3_variant_id;
		$this->fields_value['basket_3_hook_name'] 	= $this->oPrediggoConfig->basket_3_hook_name;
		$this->fields_value['basket_3_template_name'] = $this->oPrediggoConfig->basket_3_template_name;
		$this->fields_value['basket_3_block_label'] 	= $this->oPrediggoConfig->basket_3_block_label;
		
		/*
		 * BASKET RECOMMENDATIONS - block 4
		 */ 
		$this->fields_form['basket_4_reco_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Basket Page Block Configuration - #4'),
				'image' => _PS_ADMIN_IMG_.'picture.gif'
			),
			'input' => array(
				array(
					'label' => $this->l('Display the recommendations block:'),
					'type' 		=> 'radio',
					'name' 		=> 'basket_4_activated',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'basket_4_activated_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'basket_4_activated_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Add a block of recommended products in the basket page of your website'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of items in the recommendations block:'),
					'type' 		=> 'text',
					'name' 		=> 'basket_4_nb_items',
					'required' => true,
					'desc' 		=> $this->l('Number of recommended products in the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Variant ID:'),
					'type' 		=> 'text',
					'name' 		=> 'basket_4_variant_id',
					'required' => true,
					'desc' 		=> $this->l('Variant ID as defined in the integration guide provided by Prediggo'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Hook:'),
					'type' 	=> 'select',                              // This is a <select> tag.
					'name' 		=> 'basket_4_hook_name',
					'desc' 		=> $this->l('Select the position where the block should be displayed'),
					'options' => array(
						'query' => $this->oPrediggoConfig->optionsHooksBasketPage,                           // $options contains the data itself.
						'id' => 'id_option',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
						'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
						),
					'required' => true,
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Template Basket 4:'),
					'type' 		=> 'text',
					'size'		=> 100,
					'name' 		=> 'basket_4_template_name',
					'lang' 		=> false,
					'required' => true,
					'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Title of the recommendation block :'),
					'type' 		=> 'text',
					'size'		=> 50,
					'name' 		=> 'basket_4_block_label',
					'lang' 		=> true,
					'desc' 		=> $this->l('Title of the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportBasket4RecommendationConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);

		$this->fields_value['basket_4_activated'] 		= (int)$this->oPrediggoConfig->basket_4_activated;
		$this->fields_value['basket_4_nb_items'] 		= (int)$this->oPrediggoConfig->basket_4_nb_items;
		$this->fields_value['basket_4_variant_id'] 	= (int)$this->oPrediggoConfig->basket_4_variant_id;
		$this->fields_value['basket_4_hook_name'] 	= $this->oPrediggoConfig->basket_4_hook_name;
		$this->fields_value['basket_4_template_name'] = $this->oPrediggoConfig->basket_4_template_name;
		$this->fields_value['basket_4_block_label'] 	= $this->oPrediggoConfig->basket_4_block_label;
		
			/*
		 * Basket RECOMMENDATIONS - block 5
		 */ 
		 
		$this->fields_form['basket_5_reco_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Basket Page Block Configuration - #5'),
				'image' => _PS_ADMIN_IMG_.'picture.gif'
			),
			'input' => array(
				array(
					'label' => $this->l('Display the recommendations block:'),
					'type' 		=> 'radio',
					'name' 		=> 'basket_5_activated',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'basket_5_activated_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'basket_5_activated_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Add a block of recommended products in the basket page of your website'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of items in the recommendations block:'),
					'type' 		=> 'text',
					'name' 		=> 'basket_5_nb_items',
					'required' => true,
					'desc' 		=> $this->l('Number of recommended products in the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Variant ID:'),
					'type' 		=> 'text',
					'name' 		=> 'basket_5_variant_id',
					'required' => true,
					'desc' 		=> $this->l('Variant ID as defined in the integration guide provided by Prediggo'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Hook:'),
					'type' 	=> 'select',                              // This is a <select> tag.
					'name' 		=> 'basket_5_hook_name',
					'desc' 		=> $this->l('Select the position where the block should be displayed'),
					'options' => array(
						'query' => $this->oPrediggoConfig->optionsHooksBasketPage,                           // $options contains the data itself.
						'id' => 'id_option',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
						'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
						),
					'required' => true,
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Template basket 5:'),
					'type' 		=> 'text',
					'size'		=> 100,
					'name' 		=> 'basket_5_template_name',
					'lang' 		=> false,
					'required' => true,
					'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Title of the recommendation block :'),
					'type' 		=> 'text',
					'size'		=> 50,
					'name' 		=> 'basket_5_block_label',
					'lang' 		=> true,
					'desc' 		=> $this->l('Title of the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportBasket5RecommendationConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);

		$this->fields_value['basket_5_activated'] 		= (int)$this->oPrediggoConfig->basket_5_activated;
		$this->fields_value['basket_5_nb_items'] 		= (int)$this->oPrediggoConfig->basket_5_nb_items;
		$this->fields_value['basket_5_variant_id'] 	= (int)$this->oPrediggoConfig->basket_5_variant_id;
		$this->fields_value['basket_5_hook_name'] 	= $this->oPrediggoConfig->basket_5_hook_name;
		$this->fields_value['basket_5_template_name'] = $this->oPrediggoConfig->basket_5_template_name;
		$this->fields_value['basket_5_block_label'] 	= $this->oPrediggoConfig->basket_5_block_label;
		
		/*
		 * CATEGORY PAGE RECOMMENDATIONS - block 0
		 */  
		$this->fields_form['category_0_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Category Page Block Configuration - #0'),
				'image' => _PS_ADMIN_IMG_.'picture.gif'
			),
			'input' => array(
				array(
					'label' => $this->l('Display the recommendations block:'),
					'type' 		=> 'radio',
					'name' 		=> 'category_0_activated',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'category_0_activated_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'category_0_activated_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Add a block of recommended products in the category page of your website'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of items in the recommendations block:'),
					'type' 		=> 'text',
					'name' 		=> 'category_0_nb_items',
					'desc' 		=> $this->l('Number of recommended products in the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Variant ID:'),
					'type' 		=> 'text',
					'name' 		=> 'category_0_variant_id',
					'desc' 		=> $this->l('Variant ID as defined in the integration guide provided by Prediggo'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Hook:'),
					'type' 	=> 'select',                              // This is a <select> tag.
					'name' 		=> 'category_0_hook_name',
					'desc' 		=> $this->l('Select the position where the block should be displayed'),
					'options' => array(
						'query' => $this->oPrediggoConfig->optionsHooksCategoryPage,                           // $options contains the data itself.
						'id' => 'id_option',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
						'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
						),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Template Category 0:'),
					'type' 		=> 'text',
					'size'		=> 100,
					'name' 		=> 'category_0_template_name',
					'lang' 		=> false,
					'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Title of the recommendation block :'),
					'type' 		=> 'text',
					'size'		=> 50,
					'name' 		=> 'category_0_block_label',
					'lang' 		=> true,
					'desc' 		=> $this->l('Title of the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportCategory0RecommendationConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);

		$this->fields_value['category_0_activated'] 	= (int)$this->oPrediggoConfig->category_0_activated;
		$this->fields_value['category_0_nb_items'] 		= (int)$this->oPrediggoConfig->category_0_nb_items;
		$this->fields_value['category_0_variant_id'] 	= (int)$this->oPrediggoConfig->category_0_variant_id;
		$this->fields_value['category_0_hook_name'] 	= $this->oPrediggoConfig->category_0_hook_name;
		$this->fields_value['category_0_template_name'] = $this->oPrediggoConfig->category_0_template_name;
		$this->fields_value['category_0_block_label'] 	= $this->oPrediggoConfig->category_0_block_label;
		
		/*
		 * CATEGORY PAGE RECOMMENDATIONS - block 1
		 */ 
		 
		$this->fields_form['category_1_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Category Page Block Configuration - #1'),
				'image' => _PS_ADMIN_IMG_.'picture.gif'
			),
			'input' => array(
				array(
					'label' => $this->l('Display the recommendations block:'),
					'type' 		=> 'radio',
					'name' 		=> 'category_1_activated',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'category_1_activated_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'category_1_activated_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Add a block of recommended products in the category page of your website'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of items in the recommendations block:'),
					'type' 		=> 'text',
					'name' 		=> 'category_1_nb_items',
					'desc' 		=> $this->l('Number of recommended products in the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Variant ID:'),
					'type' 		=> 'text',
					'name' 		=> 'category_1_variant_id',
					'desc' 		=> $this->l('Variant ID as defined in the integration guide provided by Prediggo'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Hook:'),
					'type' 	=> 'select',                              // This is a <select> tag.
					'name' 		=> 'category_1_hook_name',
					'desc' 		=> $this->l('Select the position where the block should be displayed'),
					'options' => array(
						'query' => $this->oPrediggoConfig->optionsHooksCategoryPage,                           // $options contains the data itself.
						'id' => 'id_option',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
						'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
						),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Template category 1:'),
					'type' 		=> 'text',
					'size'		=> 100,
					'name' 		=> 'category_1_template_name',
					'lang' 		=> false,
					'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Title of the recommendation block :'),
					'type' 		=> 'text',
					'size'		=> 50,
					'name' 		=> 'category_1_block_label',
					'lang' 		=> true,
					'desc' 		=> $this->l('Title of the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportCategory1RecommendationConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);

		$this->fields_value['category_1_activated'] 		= (int)$this->oPrediggoConfig->category_1_activated;
		$this->fields_value['category_1_nb_items'] 		= (int)$this->oPrediggoConfig->category_1_nb_items;
		$this->fields_value['category_1_variant_id'] 	= (int)$this->oPrediggoConfig->category_1_variant_id;
		$this->fields_value['category_1_hook_name'] 		= $this->oPrediggoConfig->category_1_hook_name;
		$this->fields_value['category_1_template_name'] 	= $this->oPrediggoConfig->category_1_template_name;
		$this->fields_value['category_1_block_label'] 	= $this->oPrediggoConfig->category_1_block_label;
		
		/*
		 * CATEGORY PAGE RECOMMENDATIONS - block 2
		 */ 
		 
		$this->fields_form['category_2_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Category Page Block Configuration - #2'),
				'image' => _PS_ADMIN_IMG_.'picture.gif'
			),
			'input' => array(
				array(
					'label' => $this->l('Display the recommendations block:'),
					'type' 		=> 'radio',
					'name' 		=> 'category_2_activated',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'category_2_activated_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'category_2_activated_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Add a block of recommended products in the category page of your website'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of items in the recommendations block:'),
					'type' 		=> 'text',
					'name' 		=> 'category_2_nb_items',
					'desc' 		=> $this->l('Number of recommended products in the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Variant ID:'),
					'type' 		=> 'text',
					'name' 		=> 'category_2_variant_id',
					'desc' 		=> $this->l('Variant ID as defined in the integration guide provided by Prediggo'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Hook:'),
					'type' 	=> 'select',                              // This is a <select> tag.
					'name' 		=> 'category_2_hook_name',
					'desc' 		=> $this->l('Select the position where the block should be displayed'),
					'options' => array(
						'query' => $this->oPrediggoConfig->optionsHooksCategoryPage,                           // $options contains the data itself.
						'id' => 'id_option',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
						'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
						),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Template Category 2:'),
					'type' 		=> 'text',
					'size'		=> 100,
					'name' 		=> 'category_2_template_name',
					'lang' 		=> false,
					'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
					'required' => true,
				),
				array(
					'label' 	=> $this->l('Title of the recommendation block :'),
					'type' 		=> 'text',
					'size'		=> 50,
					'name' 		=> 'category_2_block_label',
					'lang' 		=> true,
					'desc' 		=> $this->l('Title of the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportCategory2RecommendationConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);

		$this->fields_value['category_2_activated'] 		= (int)$this->oPrediggoConfig->category_2_activated;
		$this->fields_value['category_2_nb_items'] 		= (int)$this->oPrediggoConfig->category_2_nb_items;
		$this->fields_value['category_2_variant_id'] 	= (int)$this->oPrediggoConfig->category_2_variant_id;
		$this->fields_value['category_2_hook_name'] 		= $this->oPrediggoConfig->category_2_hook_name;
		$this->fields_value['category_2_template_name'] 	= $this->oPrediggoConfig->category_2_template_name;
		$this->fields_value['category_2_block_label'] 	= $this->oPrediggoConfig->category_2_block_label;



        /*
		 * CUSTOMER PAGE RECOMMENDATIONS - block 0
		 */
        $this->fields_form['customer_0_conf']['form'] = array(
            'legend' => array(
                'title' => $this->l('Customer Page Block Configuration - #0'),
                'image' => _PS_ADMIN_IMG_.'picture.gif'
            ),
            'input' => array(
                array(
                    'label' => $this->l('Display the recommendations block:'),
                    'type' 		=> 'radio',
                    'name' 		=> 'customer_0_activated',
                    'class' 	=> 't',
                    'is_bool' 	=> true,
                    'values' 	=> array(
                        array(
                            'id' 	=> 'customer_0_activated_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' 	=> 'customer_0_activated_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        ),
                    ),
                    'required'	=> true,
                    'desc' 		=> $this->l('Add a block of recommended products in the customer page of your website'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
                array(
                    'label' 	=> $this->l('Number of items in the recommendations block:'),
                    'type' 		=> 'text',
                    'name' 		=> 'customer_0_nb_items',
                    'desc' 		=> $this->l('Number of recommended products in the block'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                    'required' => true,
                ),
                array(
                    'label' 	=> $this->l('Variant ID:'),
                    'type' 		=> 'text',
                    'name' 		=> 'customer_0_variant_id',
                    'desc' 		=> $this->l('Variant ID as defined in the integration guide provided by Prediggo'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                    'required' => true,
                ),
                array(
                    'label' 	=> $this->l('Hook:'),
                    'type' 	=> 'select',                              // This is a <select> tag.
                    'name' 		=> 'customer_0_hook_name',
                    'desc' 		=> $this->l('Select the position where the block should be displayed'),
                    'options' => array(
                        'query' => $this->oPrediggoConfig->optionsHooksCustomerPage,                           // $options contains the data itself.
                        'id' => 'id_option',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
                        'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
                    ),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                    'required' => true,
                ),
                array(
                    'label' 	=> $this->l('Template Customer 0:'),
                    'type' 		=> 'text',
                    'size'		=> 100,
                    'name' 		=> 'customer_0_template_name',
                    'lang' 		=> false,
                    'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                    'required' => true,
                ),
                array(
                    'label' 	=> $this->l('Title of the recommendation block :'),
                    'type' 		=> 'text',
                    'size'		=> 50,
                    'name' 		=> 'customer_0_block_label',
                    'lang' 		=> true,
                    'desc' 		=> $this->l('Title of the block'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
                array(
                    'type' 		=> 'button',
                    'name' 		=> 'exportCustomer0RecommendationConfSubmit',
                    'class' 	=> 'button',
                    'title' 	=> $this->l('   Save   '),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
            ),
        );

        $this->fields_value['customer_0_activated'] 	= (int)$this->oPrediggoConfig->customer_0_activated;
        $this->fields_value['customer_0_nb_items'] 		= (int)$this->oPrediggoConfig->customer_0_nb_items;
        $this->fields_value['customer_0_variant_id'] 	= (int)$this->oPrediggoConfig->customer_0_variant_id;
        $this->fields_value['customer_0_hook_name'] 	= $this->oPrediggoConfig->customer_0_hook_name;
        $this->fields_value['customer_0_template_name'] = $this->oPrediggoConfig->customer_0_template_name;
        $this->fields_value['customer_0_block_label'] 	= $this->oPrediggoConfig->customer_0_block_label;

        /*
         * CUSTOMER PAGE RECOMMENDATIONS - block 0
         */
        $this->fields_form['customer_1_conf']['form'] = array(
            'legend' => array(
                'title' => $this->l('Customer Page Block Configuration - #1'),
                'image' => _PS_ADMIN_IMG_.'picture.gif'
            ),
            'input' => array(
                array(
                    'label' => $this->l('Display the recommendations block:'),
                    'type' 		=> 'radio',
                    'name' 		=> 'customer_1_activated',
                    'class' 	=> 't',
                    'is_bool' 	=> true,
                    'values' 	=> array(
                        array(
                            'id' 	=> 'customer_1_activated_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' 	=> 'customer_1_activated_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        ),
                    ),
                    'required'	=> true,
                    'desc' 		=> $this->l('Add a block of recommended products in the customer page of your website'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
                array(
                    'label' 	=> $this->l('Number of items in the recommendations block:'),
                    'type' 		=> 'text',
                    'name' 		=> 'customer_1_nb_items',
                    'desc' 		=> $this->l('Number of recommended products in the block'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                    'required' => true,
                ),
                array(
                    'label' 	=> $this->l('Variant ID:'),
                    'type' 		=> 'text',
                    'name' 		=> 'customer_1_variant_id',
                    'desc' 		=> $this->l('Variant ID as defined in the integration guide provided by Prediggo'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                    'required' => true,
                ),
                array(
                    'label' 	=> $this->l('Hook:'),
                    'type' 	=> 'select',                              // This is a <select> tag.
                    'name' 		=> 'customer_1_hook_name',
                    'desc' 		=> $this->l('Select the position where the block should be displayed'),
                    'options' => array(
                        'query' => $this->oPrediggoConfig->optionsHooksCustomerPage,                           // $options contains the data itself.
                        'id' => 'id_option',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
                        'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
                    ),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                    'required' => true,
                ),
                array(
                    'label' 	=> $this->l('Template Customer 1:'),
                    'type' 		=> 'text',
                    'size'		=> 100,
                    'name' 		=> 'customer_1_template_name',
                    'lang' 		=> false,
                    'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                    'required' => true,
                ),
                array(
                    'label' 	=> $this->l('Title of the recommendation block :'),
                    'type' 		=> 'text',
                    'size'		=> 50,
                    'name' 		=> 'customer_1_block_label',
                    'lang' 		=> true,
                    'desc' 		=> $this->l('Title of the block'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
                array(
                    'type' 		=> 'button',
                    'name' 		=> 'exportCustomer1RecommendationConfSubmit',
                    'class' 	=> 'button',
                    'title' 	=> $this->l('   Save   '),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
            ),
        );

        $this->fields_value['customer_1_activated'] 	= (int)$this->oPrediggoConfig->customer_1_activated;
        $this->fields_value['customer_1_nb_items'] 		= (int)$this->oPrediggoConfig->customer_1_nb_items;
        $this->fields_value['customer_1_variant_id'] 	= (int)$this->oPrediggoConfig->customer_1_variant_id;
        $this->fields_value['customer_1_hook_name'] 	= $this->oPrediggoConfig->customer_1_hook_name;
        $this->fields_value['customer_1_template_name'] = $this->oPrediggoConfig->customer_1_template_name;
        $this->fields_value['customer_1_block_label'] 	= $this->oPrediggoConfig->customer_1_block_label;

        /*
         * CUSTOMER PAGE RECOMMENDATIONS
         */
		/*$this->fields_form['customer_reco_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Customers pages configuration'),
				'image' => _PS_ADMIN_IMG_.'picture.gif'
			),
			'input' => array(
				array(
					'label' => $this->l('Display the recommendations block:'),
					'type' 		=> 'radio',
					'name' 		=> 'customer_recommendations',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'customer_recommendations_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'customer_recommendations_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Add a block of recommended products in the customers pages of your website'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of items in the recommendations block:'),
					'type' 		=> 'text',
					'name' 		=> 'customer_nb_items',
					'desc' 		=> $this->l('Number of recommended products in the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label'		=> $this->l('Title of the recommendation block :'),
					'type' 		=> 'text',
					'name' 		=> 'customer_block_title',
					'size'		=> 50,
					'lang' 		=> true,
					'desc' 		=> $this->l('Title of the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportCustomerRecommendationConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);*/

		//$this->fields_value['customer_recommendations']	= (int)$this->oPrediggoConfig->customer_recommendations;
		//$this->fields_value['customer_nb_items'] 		= (int)$this->oPrediggoConfig->customer_nb_items;
		//$this->fields_value['customer_block_title'] 	= $this->oPrediggoConfig->customer_block_title;

		/*
		 * BLOCK LAYERED MODULE RECOMMENDATIONS
		 */
		$this->fields_form['blocklayered_reco_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Block layered module configuration'),
				'image' => _PS_ADMIN_IMG_.'picture.gif'
			),
			'input' => array(
				array(
					'label' 	=> $this->l('Display the recommendations block:'),
					'type' 		=> 'radio',
					'name' 		=> 'blocklayered_0_recommendations',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'blocklayered_recommendations_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'blocklayered_recommendations_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Add a block of recommended products in the block layered module'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of items in the recommendations block:'),
					'type' 		=> 'text',
					'name' 		=> 'blocklayered_0_nb_items',
					'desc' 		=> $this->l('Number of recommended products in the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
                array(
                    'label' 	=> $this->l('Variant ID:'),
                    'type' 		=> 'text',
                    'name' 		=> 'blocklayered_0_variant_id',
                    'desc' 		=> $this->l('Variant ID as defined in the integration guide provided by Prediggo'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                    'required' => true,
                ),
                array(
                    'label' 	=> $this->l('Hook:'),
                    'type' 	=> 'select',                              // This is a <select> tag.
                    'name' 		=> 'blocklayered_0_hook_name',
                    'desc' 		=> $this->l('Select the position where the block should be displayed'),
                    'options' => array(
                        'query' => $this->oPrediggoConfig->optionsHooksBlocklayeredPage,                           // $options contains the data itself.
                        'id' => 'id_option',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
                        'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
                    ),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                    'required' => true,
                ),
                array(
                    'label' 	=> $this->l('Template Block Layered :'),
                    'type' 		=> 'text',
                    'size'		=> 100,
                    'name' 		=> 'blocklayered_0_template_name',
                    'lang' 		=> false,
                    'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                    'required' => true,
                ),
				array(
					'label'		=> $this->l('Title of the recommendation block :'),
					'type' 		=> 'text',
					'name' 		=> 'blocklayered_0_block_title',
					'size'		=> 50,
					'lang' 		=> true,
					'desc' 		=> $this->l('Title of the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportBlocklayeredRecommendationConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);

		$this->fields_value['blocklayered_0_recommendations']	= (int)$this->oPrediggoConfig->blocklayered_0_recommendations;
		$this->fields_value['blocklayered_0_nb_items'] 		= (int)$this->oPrediggoConfig->blocklayered_0_nb_items;
        $this->fields_value['blocklayered_0_variant_id'] 	= $this->oPrediggoConfig->blocklayered_0_variant_id;
        $this->fields_value['blocklayered_0_hook_name'] 	    = $this->oPrediggoConfig->blocklayered_0_hook_name;
        $this->fields_value['blocklayered_0_template_name'] 	= $this->oPrediggoConfig->blocklayered_0_template_name;
		$this->fields_value['blocklayered_0_block_title'] 	= $this->oPrediggoConfig->blocklayered_0_block_title;

		/*
		 * SEARCH MAIN CONFIGURATION
		 */
		$this->fields_form['search_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Main search settings'),
				'image' => _PS_ADMIN_IMG_.'search.gif'
			),
			'input' => array(
				array(
					'label' => $this->l('Display the search block:'),
					'type' 		=> 'radio',
					'name' 		=> 'search_active',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'search_active_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'search_active_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Enable the search block in the front office'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
                array(
                    'label' => $this->l('Searchandizing activation:'),
                    'type' 		=> 'radio',
                    'name' 		=> 'searchandizing_active',
                    'class' 	=> 't',
                    'is_bool' 	=> true,
                    'values' 	=> array(
                        array(
                            'id' 	=> 'searchandizing_active_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' 	=> 'searchandizing_active_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        ),
                    ),
                    'required'	=> true,
                    'desc' 		=> $this->l('Enable the searchandizing block in the front office'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
                array(
                    'label' => $this->l('Layered navigation activation:'),
                    'type' 		=> 'radio',
                    'name' 		=> 'layered_navigation_active',
                    'class' 	=> 't',
                    'is_bool' 	=> true,
                    'values' 	=> array(
                        array(
                            'id' 	=> 'layered_navigation_active_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' 	=> 'layered_navigation_active_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        ),
                    ),
                    'required'	=> true,
                    'desc' 		=> $this->l('Enable the prediggo layered navigation in the search page of the front office'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                ),
                array(
                    'label' 	=> $this->l('Template search:'),
                    'type' 		=> 'text',
                    'size'		=> 100,
                    'name' 		=> 'search_main_template_name',
                    'lang' 		=> false,
                    'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                    'required' => true,
                ),
                array(
                    'label' 	=> $this->l('Template Search Sort Block:'),
                    'type' 		=> 'text',
                    'size'		=> 100,
                    'name' 		=> 'search_filter_block_template_name',
                    'lang' 		=> false,
                    'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                    'required' => true,
                ),
                array(
                    'label' 	=> $this->l('Template Pagination:'),
                    'type' 		=> 'text',
                    'size'		=> 100,
                    'name' 		=> 'pagination_template_name',
                    'lang' 		=> false,
                    'desc' 		=> $this->l('Name of the template file. BE CAREFUL when you modify it because all the name change must be in the search.tpl too. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by modifying it in \modules\prediggo\views\templates\front'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                    'required' => true,
                ),
                array(
                    'label' 	=> $this->l('Template Search Sort By:'),
                    'type' 		=> 'text',
                    'size'		=> 100,
                    'name' 		=> 'search_filters_sort_by_template_name',
                    'lang' 		=> false,
                    'desc' 		=> $this->l('Name of the template file. BE CAREFUL when you modify it because all the name change must be in the search.tpl too. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by modifying it in \modules\prediggo\views\templates\front'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                    'required' => true,
                ),
                array(
                    'label' 	=> $this->l('Template Product Compare:'),
                    'type' 		=> 'text',
                    'size'		=> 100,
                    'name' 		=> 'prod_compare_template_name',
                    'lang' 		=> false,
                    'desc' 		=> $this->l('Name of the template file. BE CAREFUL when you modify it because all the name change must be in the search.tpl too. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                    'required' => true,
                ),
                array(
                    'label' 	=> $this->l('Template Product List:'),
                    'type' 		=> 'text',
                    'size'		=> 100,
                    'name' 		=> 'prod_list_template_name',
                    'lang' 		=> false,
                    'desc' 		=> $this->l('Name of the template file. BE CAREFUL when you modify it because all the name change must be in the search.tpl too. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
                    'disabled'	=> ((int)($iShopContext)?'disabled':''),
                    'required' => true,
                ),
				array(
					'type' 		=> 'button',
					'name' 		=> 'mainSearchConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);

		$this->fields_value['search_active']				= (int)$this->oPrediggoConfig->search_active;
        $this->fields_value['search_main_template_name'] = $this->oPrediggoConfig->search_main_template_name;
        $this->fields_value['pagination_template_name'] 	= $this->oPrediggoConfig->pagination_template_name;
        $this->fields_value['search_filters_sort_by_template_name'] 	= $this->oPrediggoConfig->search_filters_sort_by_template_name;
        $this->fields_value['search_filter_block_template_name'] 	= $this->oPrediggoConfig->search_filter_block_template_name;
        $this->fields_value['prod_compare_template_name'] 	= $this->oPrediggoConfig->prod_compare_template_name;
        $this->fields_value['prod_list_template_name'] 	= $this->oPrediggoConfig->prod_list_template_name;
        $this->fields_value['searchandizing_active'] 		= (int)$this->oPrediggoConfig->searchandizing_active;
        $this->fields_value['layered_navigation_active'] 	= (int)$this->oPrediggoConfig->layered_navigation_active;



		/*
		 * SEARCH AUTOCOMPLETION CONFIGURATION
		*/
		$this->fields_form['search_autocompletion_conf']['form'] = array(
				'legend' => array(
					'title' => $this->l('Autocompletion configuration'),
					'image' => _PS_ADMIN_IMG_.'download_page.png'
				),
				'input' => array(
					array(
						'label' => $this->l('Autocompletion activation:'),
						'type' 		=> 'radio',
						'name' 		=> 'autocompletion_active',
						'class' 	=> 't',
						'is_bool' 	=> true,
						'values' 	=> array(
							array(
								'id' 	=> 'autocompletion_active_on',
								'value' => 1,
								'label' => $this->l('Yes')
							),
							array(
								'id' 	=> 'autocompletion_active_off',
								'value' => 0,
								'label' => $this->l('No')
							),
						),
						'required'	=> true,
						'desc' 		=> $this->l('Enable the prediggo autocompletion to propose words when the customer is typing his search'),
						'disabled'	=> ((int)($iShopContext)?'disabled':''),
					),
                    array(
                        'label' 	=> $this->l('Minimum number of chars to launch a search:'),
                        'type' 		=> 'text',
                        'name' 		=> 'search_nb_min_chars',
                        'desc' 		=> $this->l('Minimum number of character to allow the user to execute a search'),
                        'disabled'	=> ((int)($iShopContext)?'disabled':''),
                    ),
					array(
						'label' 	=> $this->l('Number of items in the search autocompletion:'),
						'type' 		=> 'text',
						'name' 		=> 'autocompletion_nb_items',
						'desc' 		=> $this->l('Number of products displayed in the prediggo autocompletion'),
						'disabled'	=> ((int)($iShopContext)?'disabled':''),
					),
                    array(
                        'label' 	=> $this->l('Template search block:'),
                        'type' 		=> 'text',
                        'size'		=> 100,
                        'name' 		=> 'search_0_template_name',
                        'lang' 		=> false,
                        'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
                        'disabled'	=> ((int)($iShopContext)?'disabled':''),
                        'required' => true,
                    ),
                    array(
                        'label' 	=> $this->l('Template autocomplete Did you mean name:'),
                        'type' 		=> 'text',
                        'size'		=> 100,
                        'name' 		=> 'autoc_template_name',
                        'lang' 		=> false,
                        'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
                        'disabled'	=> ((int)($iShopContext)?'disabled':''),
                        'required' => true,
                    ),
                    array(
                        'label' 	=> $this->l('Template autocomplete Attribute name:'),
                        'type' 		=> 'text',
                        'size'		=> 100,
                        'name' 		=> 'autocat_template_name',
                        'lang' 		=> false,
                        'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
                        'disabled'	=> ((int)($iShopContext)?'disabled':''),
                        'required' => true,
                    ),
                    array(
                        'label' 	=> $this->l('Template autocomplete Product name:'),
                        'type' 		=> 'text',
                        'size'		=> 100,
                        'name' 		=> 'autop_template_name',
                        'lang' 		=> false,
                        'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
                        'disabled'	=> ((int)($iShopContext)?'disabled':''),
                        'required' => true,
                    ),
                    array(
                        'label' 	=> $this->l('Template autocomplete Suggest name:'),
                        'type' 		=> 'text',
                        'size'		=> 100,
                        'name' 		=> 'autos_template_name',
                        'lang' 		=> false,
                        'desc' 		=> $this->l('Name of the template file. Should finish with the extension ".tpl". <br>You can create your override Prediggo template by putting your own in them in \themes\YOUR_THEME\modules\prediggo\views\templates\hook'),
                        'disabled'	=> ((int)($iShopContext)?'disabled':''),
                        'required' => true,
                    ),
					array(
						'type' 		=> 'button',
						'name' 		=> 'exportSearchAutocompletionConfSubmit',
						'class' 	=> 'button',
						'title' 	=> $this->l('   Save   '),
						'disabled'	=> ((int)($iShopContext)?'disabled':''),
					),
				),
		);

		$this->fields_value['autocompletion_active']	= (int)$this->oPrediggoConfig->autocompletion_active;
        $this->fields_value['search_nb_min_chars'] 		= (int)$this->oPrediggoConfig->search_nb_min_chars;
		$this->fields_value['autocompletion_nb_items'] 	= (int)$this->oPrediggoConfig->autocompletion_nb_items;
        $this->fields_value['search_0_template_name'] 	= $this->oPrediggoConfig->search_0_template_name;
        $this->fields_value['autoc_template_name'] 		= $this->oPrediggoConfig->autoc_template_name;
        $this->fields_value['autocat_template_name'] 		= $this->oPrediggoConfig->autocat_template_name;
        $this->fields_value['autop_template_name'] 		= $this->oPrediggoConfig->autop_template_name;
        $this->fields_value['autos_template_name'] 		= $this->oPrediggoConfig->autos_template_name;

		$this->context->controller->getLanguages();
		$helper = $this->initForm();
		$helper->submit_action = '';

		$helper->title = $this->l('Prediggo configuration');
		if (Shop::getContext() == Shop::CONTEXT_SHOP)
			$helper->title .= ' [ '.$this->l('Shop').' : '.$this->context->shop->name.' ]';
		else
			$helper->title .= ' [ '.$this->l('All shops').' ]';



		$helper->fields_value = $this->fields_value;
		$this->_html .= $helper->generateForm($this->fields_form);
	}

	private function initForm()
	{
		$helper = new HelperForm();

		$helper->module = $this;
		$helper->name_controller = 'prediggo';
		$helper->identifier = $this->identifier;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->languages = $this->context->controller->_languages;
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		$helper->default_form_language = $this->context->controller->default_form_language;
		$helper->allow_employee_form_lang = $this->context->controller->allow_employee_form_lang;

		return $helper;
	}
	
	
	/**
	 * Display the recommendations by hook
	 *
	 * @param string $sHookName Hook Name
	 * @param array $params list of specific data
     * @param int $iVariantId Id of the Variant
	 * @return string Html
	 */
	 /**
	private function displayFooterRecommendations($sHookName, $params, $iVariantId)
	{
		if(!$this->oPrediggoConfig->web_site_id_checked)
			return false;

		$params['customer'] = $this->context->customer;
        $this->aRecommendations[$sHookName] = $this->oPrediggoCallController->getListOfRecommendations($sHookName, $params, $iVariantId);
        if(!$this->aRecommendations[$sHookName])
			return false;

		// Display Main Configuration management
		$this->smarty->assign(array(
			'hook_name' 		=> $sHookName,
			'aRecommendations' 	=> $this->aRecommendations,
			'tax_enabled' 		=> (int)Configuration::get('PS_TAX'),
			'display_qties' 	=> (int)Configuration::get('PS_DISPLAY_QTIES'),
			'display_ht' 		=> !Tax::excludeTaxeOption(),
			'sImageType' 		=> (Tools::version_compare(_PS_VERSION_, '1.5.1', '>=')?'home_default':'home'),
		));

		return $this->display(__FILE__, 'footer_recommendations.tpl');
	}
	*/
	
	
}