<?php

/**
 * @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
 * @copyright Croissance NET
 * @version 1.0
 */

require_once(_PS_MODULE_DIR_.'prediggo/classes/PrediggoConfig.php');
require_once(_PS_MODULE_DIR_.'prediggo/classes/PrediggoSearchConfig.php');
require_once(_PS_MODULE_DIR_.'prediggo/classes/PrediggoCall.php');

class PrediggoSearchController extends FrontController
{
	/** @var PrediggoSearchConfig Object PrediggoSearchConfig */
	private $oPrediggoSearchConfig;
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

		$this->oPrediggoSearchConfig = PrediggoSearchConfig::singleton();
		if(!$this->oPrediggoSearchConfig->search_active)
			return false;

		$this->oPrediggoConfig = PrediggoConfig::singleton();

		$this->sRepositoryPath = _PS_MODULE_DIR_.'prediggo/logs/';

		$this->oPrediggoCall = new PrediggoCall($this->oPrediggoConfig->web_site_id, $this->oPrediggoSearchConfig->server_url_search);
		$this->sQuery = Tools::getValue('q');
		$this->sRefineOption = Tools::getValue('refineOption');
	}

	/**
	  * Execute the Parent Run function
	  */
	public function run()
	{
		if($this->oPrediggoSearchConfig->search_active)
			parent::run();
		else
			header("Location: ../");
	}

	/**
	  * Set the Media (CSS / JS) of the page
	  */
	public function setMedia()
	{
		parent::setMedia();
		Tools::addCSS(array(
			_THEME_CSS_DIR_.'product_list.css' => 'all'
		));

		if (Configuration::get('PS_COMPARATOR_MAX_ITEM') > 0)
			Tools::addJS(_THEME_JS_DIR_.'products-comparison.js');
	}

	/**
	  * Set smarty vars
	  */
	public function preProcess()
	{
		parent::process();

		if($oPrediggoResult = $this->launchSearch((int)$this->oPrediggoSearchConfig->search_nb_items))
		{
			if(class_exists('CompareProduct'))
			{
				if(method_exists('CompareProduct','getIdCompareByIdCustomer'))
					self::$smarty->assign('compareProducts', CompareProduct::getIdCompareByIdCustomer((int)self::$cookie->id_customer));
				elseif (isset(self::$cookie->id_customer))
					self::$smarty->assign('compareProducts', CompareProduct::getCustomerCompareProducts((int)self::$cookie->id_customer));
				elseif (isset(self::$cookie->id_guest))
					self::$smarty->assign('compareProducts', CompareProduct::getGuestCompareProducts((int)self::$cookie->id_guest));
			}

			self::$smarty->assign(array(
				'page_name' => 'prediggo_search_page',
				'sPrediggoQuery' => $this->sQuery,
				'aPrediggoProducts' => $this->oPrediggoCall->getProducts($oPrediggoResult, (int)self::$cookie->id_lang),
				'aDidYouMeanWords' => $oPrediggoResult->getDidYouMeanWords(),
				'aSortingOptions' => $oPrediggoResult->getSortingOptions(),
				'aCancellableFiltersGroups' => $oPrediggoResult->getCancellableFiltersGroups(),
				'aDrillDownGroups' => $oPrediggoResult->getDrillDownGroups(),
				'aChangePageLinks' => $oPrediggoResult->getChangePageLinks(),
				'oSearchStatistics' => $oPrediggoResult->getSearchStatistics(),
				'bSearchandizingActive' => $this->oPrediggoSearchConfig->searchandizing_active,
				'aCustomRedirections' => $oPrediggoResult->getCustomRedirections(),
				'comparator_max_item' => (int)(Configuration::get('PS_COMPARATOR_MAX_ITEM'))
			));
		}
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
		global $link;

		parent::process();

		if(!$this->oPrediggoSearchConfig->autocompletion_active)
			return '';

		$aItems = array();

		/* If $sQuery is empty return the prediggo suggestion and products */
		if(strlen($this->sQuery) >= $this->oPrediggoSearchConfig->search_nb_min_chars
		&& $oPrediggoResult = $this->launchAutoComplete())
		{

			require_once(_PS_MODULE_DIR_.'prediggo/prediggo.php');
			$oModule = new Prediggo();

			foreach($oPrediggoResult->getSuggestedWords() as $oSuggestedWords)
			{
				self::$smarty->assign(array('oSuggestedWords' => $oSuggestedWords));
				$aItems[] = array(
					'value' => $oModule->displayAutocompleteDidYouMean($oSuggestedWords),
					'link' => $link->getPageLink('modules/prediggo/prediggo_search.php').'?q='.$oSuggestedWords->getWord(),
					'notificationId' => '',
					'isRecommendation' => false
				);
			}

			foreach($this->oPrediggoCall->getSuggestedProducts($oPrediggoResult, (int)self::$cookie->id_lang, (int)$this->oPrediggoSearchConfig->autocompletion_nb_items) as $aRecommendation)
			{
				self::$smarty->assign(array('aRecommendation' => $aRecommendation));
				$aItems[] = array(
					'value' => $oModule->displayAutocompleteProduct(),
					'link' => $aRecommendation['link'],
					'notificationId' => $aRecommendation['notificationId'],
					'isRecommendation' => true
				);
			}
		}
		/* If $sQuery is empty return the suggestion words defined by the client in the BO */
		elseif(strlen($this->sQuery) == 0)
		{
			global $link;

			if($aSuggestWords = explode(',',$this->oPrediggoSearchConfig->suggest_words[(int)self::$cookie->id_lang]))
			{
				require_once(_PS_MODULE_DIR_.'prediggo/prediggo.php');
				$oModule = new Prediggo();

				foreach($aSuggestWords as $sSuggestWord)
				{
					self::$smarty->assign(array('sSuggestWord' => trim($sSuggestWord)));
					$aItems[] = array(
						'value' => $oModule->displayAutocompleteSuggest(),
						'link' => $link->getPageLink('modules/prediggo/prediggo_search.php').'?q='.$sSuggestWord,
						'notificationId' => '',
						'isRecommendation' => false
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
			'cookie' => self::$cookie,
			'cart' => self::$cart,
			'query' => $this->sQuery,
			'nb_items' => (int)$nb_items,
			'option' => $this->sRefineOption
		);

		$oResult = $this->oPrediggoCall->getSearch($params);

		if($this->oPrediggoSearchConfig->logs_fo_file_generation)
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
			'cookie' => self::$cookie,
			'cart' => self::$cart,
			'query' => $this->sQuery
		);

		$oResult = $this->oPrediggoCall->getAutoComplete($params);

		if($this->oPrediggoSearchConfig->logs_fo_file_generation)
			$this->setSearchLogFile('Search', $this->oPrediggoCall->getLogs());

		return $oResult;
	}

	/**
	  * Display front office tpl
	  */
	public function displayContent()
	{
		parent::displayContent();
		self::$smarty->display(dirname(__FILE__).'/../search.tpl');
	}

	/**
	  * Get the current search products
	  *
	  * @return array list of products
	  */
	public function getProducts($oPrediggoResult)
	{
		return $this->oPrediggoCall->getProducts($oPrediggoResult, (int)self::$cookie->id_lang);
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

