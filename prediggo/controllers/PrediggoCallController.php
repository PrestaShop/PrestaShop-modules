<?php

/**
 * @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
 * @copyright Croissance NET
 * @version 1.0
 */

require_once(_PS_MODULE_DIR_.'prediggo/classes/PrediggoConfig.php');
require_once(_PS_MODULE_DIR_.'prediggo/classes/PrediggoRecommendationConfig.php');
require_once(_PS_MODULE_DIR_.'prediggo/classes/PrediggoCall.php');

class PrediggoCallController
{
	/** @var PrediggoRecommendationConfig Object PrediggoRecommendationConfig */
	private $oPrediggoRecommendationConfig;
	/** @var array list of the page where prediggo will be displayed */
	private $aPagesAccessible;
	/** @var string current page name */
	private $sPageName;
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
		$this->oPrediggoConfig = PrediggoConfig::singleton();
		$this->oPrediggoRecommendationConfig = PrediggoRecommendationConfig::singleton();

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
		$this->oPrediggoCall = new PrediggoCall($this->oPrediggoConfig->web_site_id, $this->oPrediggoRecommendationConfig->server_url_recommendations);

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

		if($this->oPrediggoRecommendationConfig->logs_fo_file_generation)
			$this->setNotificationsLogFile($sType, $this->oPrediggoCall->getLogs());
	}

	/**
	  * Get the list of recommendations by hook
	  *
	  * @param string $sHookName Name of the hook
	  * @param array $params list of specific parameters
	  * @return array $aRecommendations list of products
	  */
	public function getListOfRecommendations($sHookName, $params)
	{
		if(!$this->isPageAccessible())
			return false;

		$this->oPrediggoCall = new PrediggoCall($this->oPrediggoConfig->web_site_id, $this->oPrediggoRecommendationConfig->server_url_recommendations);

		$aRecommendations = false;

		switch($this->sPageName)
		{
			case 'index' :
				$params['nb_items'] = (int)$this->oPrediggoRecommendationConfig->home_nb_items;
				$params['block_title'] = pSQL($this->oPrediggoRecommendationConfig->home_block_title[(int)$params['cookie']->id_lang]);
				$aRecommendations = $this->oPrediggoCall->getLandingPageRecommendations($params);
			break;

			case '404' :
				$params['nb_items'] = (int)$this->oPrediggoRecommendationConfig->error_nb_items;
				$params['block_title'] = pSQL($this->oPrediggoRecommendationConfig->error_block_title[(int)$params['cookie']->id_lang]);
				$aRecommendations = $this->oPrediggoCall->getLandingPageRecommendations($params);
			break;

			case 'product' :
				$params['nb_items'] = (int)$this->oPrediggoRecommendationConfig->product_nb_items;
				$params['block_title'] = pSQL($this->oPrediggoRecommendationConfig->product_block_title[(int)$params['cookie']->id_lang]);
				$params['id_product'] = (int)Tools::getValue('id_product');
				$aRecommendations = $this->oPrediggoCall->getProductRecommendations($params);
			break;

			case 'category' :
				$params['nb_items'] = (int)$this->oPrediggoRecommendationConfig->category_nb_items;
				$params['block_title'] = pSQL($this->oPrediggoRecommendationConfig->category_block_title[(int)$params['cookie']->id_lang]);
				$params['category'] = new Category((int)Tools::getValue('id_category'), (int)$params['cookie']->id_lang);
				$aRecommendations = $this->oPrediggoCall->getCategoyRecommendations($params);
			break;

			case 'blocklayered' :
				$params['nb_items'] = (int)$this->oPrediggoRecommendationConfig->blocklayered_nb_items;
				$params['block_title'] = pSQL($this->oPrediggoRecommendationConfig->blocklayered_block_title[(int)$params['cookie']->id_lang]);
				$params['category'] = new Category((int)$params['id_category_layered'], (int)$params['cookie']->id_lang);
				$aRecommendations = $this->oPrediggoCall->getBlockLayeredRecommendations($params);
			break;

			case 'my-account' :
			case 'addresses' :
			case 'history' :
			case 'order-return' :
				$params['nb_items'] = (int)$this->oPrediggoRecommendationConfig->customer_nb_items;
				$params['block_title'] = pSQL($this->oPrediggoRecommendationConfig->customer_block_title[(int)$params['cookie']->id_lang]);
				$aRecommendations = $this->oPrediggoCall->getCustomerRecommendations($params);
			break;

			case 'order' :
			case 'order-opc' :
				$params['nb_items'] = (int)$this->oPrediggoRecommendationConfig->cart_nb_items;
				$params['block_title'] = pSQL($this->oPrediggoRecommendationConfig->cart_block_title[(int)$params['cookie']->id_lang]);
				$aRecommendations = $this->oPrediggoCall->getCartRecommendations($params);
			break;

			case 'best-sales' :
				$params['nb_items'] = (int)$this->oPrediggoRecommendationConfig->best_sales_nb_items;
				$params['block_title'] = pSQL($this->oPrediggoRecommendationConfig->best_sales_block_title[(int)$params['cookie']->id_lang]);
				$aRecommendations = $this->oPrediggoCall->getBestSalesRecommendations($params);
			break;

			default : break;
		}

		if($this->oPrediggoRecommendationConfig->logs_fo_file_generation)
			$this->setRecommendationsLogFile($sHookName, $this->oPrediggoCall->getLogs());

		return $aRecommendations;
	}

	/**
	  * Set the array $aPagesAccessible with the page accessible
	  */
	public function setPagesAccessible()
	{
		$this->aPagesAccessible = array();

		if($this->oPrediggoRecommendationConfig->home_recommendations)
			$this->aPagesAccessible[] = 'index';
		if($this->oPrediggoRecommendationConfig->error_recommendations)
			$this->aPagesAccessible[] = '404';
		if($this->oPrediggoRecommendationConfig->product_recommendations)
			$this->aPagesAccessible[] = 'product';
		if($this->oPrediggoRecommendationConfig->category_recommendations)
			$this->aPagesAccessible[] = 'category';
		if($this->oPrediggoRecommendationConfig->customer_recommendations)
		{
			$this->aPagesAccessible[] = 'my-account';
			$this->aPagesAccessible[] = 'addresses';
			$this->aPagesAccessible[] = 'history';
			$this->aPagesAccessible[] = 'order-return';
		}
		if($this->oPrediggoRecommendationConfig->cart_recommendations)
		{
			$this->aPagesAccessible[] = 'order';
			$this->aPagesAccessible[] = 'order-opc';
		}
		if($this->oPrediggoRecommendationConfig->best_sales_recommendations)
			$this->aPagesAccessible[] = 'best-sales';
		if($this->oPrediggoRecommendationConfig->blocklayered_recommendations)
			$this->aPagesAccessible[] = 'blocklayered';
	}

	/**
	  * Set the current page name and store it to the object var $sPageName
	  */
	public function setPageName()
	{
		$this->sPageName = basename(preg_replace('/\.php$/', '', $_SERVER['PHP_SELF']));
		if (preg_match('#^'.__PS_BASE_URI__.'modules/([a-zA-Z0-9_-]+?)/(.*)$#', $_SERVER['REQUEST_URI'], $m))
			$this->sPageName = 'module-'.$m[1].'-'.str_replace(array('.php', '/'), array('', '-'), $m[2]);
	}

	/**
	  * Update the current $sPageName
	  */
	public function _setPageName($sPageName)
	{
		$this->sPageName = $sPageName;
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
}

