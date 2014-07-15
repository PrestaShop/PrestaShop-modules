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

class PrediggoSearchModuleFrontController extends ModuleFrontController
{
	/** @var PrediggoSearchConfig Object PrediggoSearchConfig */
	private $oPrediggoConfig;
	/** @var PrediggoCall Object PrediggoCall */
	private $oPrediggoCall;
	/** @var string Search query */
	private $sQuery;
	/** @var string Prediggo refine option */
	private $sRefineOption;
	/** @var string path of the log repository */
	private $sRepositoryPath;
	
	/**
	 * Initialise the object variables
	 */
	public function __construct()
	{
		parent::__construct();
	
		$this->oPrediggoConfig = new PrediggoConfig($this->context);
		if(!$this->oPrediggoConfig->search_active)
			Tools::redirect('index.php');
	
		$this->sRepositoryPath = _PS_MODULE_DIR_.'prediggo/logs/';
	
		$this->oPrediggoCall = new PrediggoCall($this->oPrediggoConfig->web_site_id, $this->oPrediggoConfig->server_url_search);
		$this->sQuery = Tools::getValue('q');
		$this->sRefineOption = Tools::getValue('refineOption');
	}
	
	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		
		if($oPrediggoResult = $this->launchSearch((int)$this->oPrediggoConfig->search_nb_items))
		{
			if(isset($this->context->cookie->id_compare))
				$this->context->smarty->assign('compareProducts', CompareProduct::getCompareProducts((int)$this->context->cookie->id_compare));
					
			$this->context->smarty->assign(array(
				'page_name' 					=> 'prediggo_search_page',
				'sPrediggoQuery' 				=> $this->sQuery,
				'aPrediggoProducts' 			=> $this->oPrediggoCall->getProducts($oPrediggoResult, (int)$this->context->cookie->id_lang),
				'aDidYouMeanWords' 				=> $oPrediggoResult->getDidYouMeanWords(),
				'aSortingOptions' 				=> $oPrediggoResult->getSortingOptions(),
				'aCancellableFiltersGroups' 	=> $oPrediggoResult->getCancellableFiltersGroups(),
				'aDrillDownGroups' 				=> $oPrediggoResult->getDrillDownGroups(),
				'aChangePageLinks' 				=> $oPrediggoResult->getChangePageLinks(),
				'oSearchStatistics' 			=> $oPrediggoResult->getSearchStatistics(),
				'bSearchandizingActive' 		=> $this->oPrediggoConfig->searchandizing_active,
				'aCustomRedirections' 			=> $oPrediggoResult->getCustomRedirections(),
				'comparator_max_item' 			=> (int)(Configuration::get('PS_COMPARATOR_MAX_ITEM')),
				'sImageType' 					=> (Tools::version_compare(_PS_VERSION_, '1.5.1', '>=')?'home_default':'home'),
				'bRewriteEnabled'				=> (int)Configuration::get('PS_REWRITING_SETTINGS'),
			));
		}
		
		parent::initContent();

		$this->setTemplate('search.tpl');
	}
	
	/**
	 * Set the Media (CSS / JS) of the page
	 */
	public function setMedia()
	{
		parent::setMedia();
		$this->addCSS(array(
		_THEME_CSS_DIR_.'product_list.css' => 'all'
				));
	
		if (Configuration::get('PS_COMPARATOR_MAX_ITEM') > 0)
			$this->addJS(_THEME_JS_DIR_.'products-comparison.js');
	}
	/**
	 * Set the search query
	 *
	 * @param string $sQuery Search query
	 */
	function setQuery($sQuery)
	{
		$this->sQuery = $sQuery;
	}
	
	/**
	 * Set the refine option
	 *
	 * @param string $sRefineOption Refine option
	 */
	function setRefineOption($sRefineOption)
	{
		$this->sRefineOption = $sRefineOption;
	}
	
	/**
	 * Set the refine option
	 *
	 * @return array $aItems Autocompletion items (suggestions, products)
	 */
	public function getAutocomplete()
	{
	
		parent::process();
	
		if(!$this->oPrediggoConfig->autocompletion_active)
			return '';
	
		$aItems = array();
	
		/* If $sQuery is empty return the prediggo suggestion and products */
		if(strlen($this->sQuery) >= $this->oPrediggoConfig->search_nb_min_chars
		&& $oPrediggoResult = $this->launchAutoComplete())
		{
			foreach($oPrediggoResult->getSuggestedWords() as $oSuggestedWords)
			{
				$this->context->smarty->assign(array('oSuggestedWords' => $oSuggestedWords));
				$aItems[] = array(
					'value' 			=> $this->module->displayAutocompleteDidYouMean($oSuggestedWords),
					'link' 				=> $this->context->link->getModuleLink('prediggo', 'search').'?q='.$oSuggestedWords->getWord(),
					'notificationId' 	=> '',
					'isRecommendation' 	=> false
				);
			}
	
			foreach($this->oPrediggoCall->getSuggestedProducts($oPrediggoResult, (int)$this->context->cookie->id_lang, (int)$this->oPrediggoConfig->autocompletion_nb_items) as $aRecommendation)
			{
				$this->context->smarty->assign(array('aRecommendation' => $aRecommendation));
				$aItems[] = array(
					'value' 			=> $this->module->displayAutocompleteProduct(),
					'link' 				=> $aRecommendation['link'],
					'notificationId' 	=> $aRecommendation['notificationId'],
					'isRecommendation' 	=> true
				);
			}
		}
		/* If $sQuery is empty return the suggestion words defined by the client in the BO */
		elseif(strlen($this->sQuery) == 0)
		{
			if($aSuggestWords = explode(',',$this->oPrediggoConfig->suggest_words[(int)$this->context->cookie->id_lang]))
			{
				foreach($aSuggestWords as $sSuggestWord)
				{
					$this->context->smarty->assign(array('sSuggestWord' => trim($sSuggestWord)));
					$aItems[] = array(
						'value' 			=> $this->module->displayAutocompleteSuggest(),
						'link' 				=> $this->context->link->getModuleLink('prediggo', 'search').'?q='.$sSuggestWord,
						'notificationId' 	=> '',
						'isRecommendation' 	=> false
					);
				}
			}
		}
		return $aItems;
	}
	
	/**
	 * Execute a prediggo search
	 *
	 * @param integer $nb_items Number of products
	 * @return PrediggoService $oResult Object containing all the search results
	 */
	public function launchSearch($nb_items = 0)
	{
		if(empty($this->sQuery))
			return false;
	
		$params = array(
			'customer' 	=> $this->context->customer,
			'cookie' 	=> $this->context->cookie,
			'cart' 		=> $this->context->cart,
			'query' 	=> $this->sQuery,
			'nb_items' 	=> (int)$nb_items,
			'option' 	=> $this->sRefineOption
		);
	
		$oResult = $this->oPrediggoCall->getSearch($params);
	
		if($this->oPrediggoConfig->logs_search_file_generation)
			$this->setSearchLogFile('Search', $this->oPrediggoCall->getLogs());
	
		return $oResult;
	}
	
	/**
	 * Execute a prediggo autocomplete
	 *
	 * @return PrediggoService $oResult Object containing all the autocomplete results
	 */
	public function launchAutoComplete()
	{
		if(empty($this->sQuery))
			return false;
	
		$params = array(
			'customer' 	=> $this->context->customer,
			'cookie' 	=> $this->context->cookie,
			'cart' 		=> $this->context->cart,
			'query' 	=> $this->sQuery
		);
	
		$oResult = $this->oPrediggoCall->getAutoComplete($params);
	
		if($this->oPrediggoConfig->logs_search_file_generation)
			$this->setSearchLogFile('Search', $this->oPrediggoCall->getLogs());
	
		return $oResult;
	}
	
	/**
	 * Get the current search products
	 *
	 * @return array list of products
	 */
	public function getProducts($oPrediggoResult)
	{
		return $this->oPrediggoCall->getProducts($oPrediggoResult, (int)$this->context->cookie->id_lang);
	}
	
	/**
	 * Add the new logs list to the search log file
	 *
	 * @param string $sHookName Name of the hook
	 * @param array $aLogs list of logs
	 */
	private function setSearchLogFile($sHookName, $aLogs)
	{
		$sEntityLogFileName = $this->sRepositoryPath.'log-fo_search.txt';
		$aLogs[0] .= ' {'.$sHookName.'}';
		if($handle = fopen($sEntityLogFileName, 'a'))
		{
			foreach($aLogs as $sLog)
				fwrite($handle, $sLog."\n");
			fclose($handle);
		}
	}
}
