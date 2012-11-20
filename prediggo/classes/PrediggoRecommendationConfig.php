<?php

/**
 * @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
 * @copyright Croissance NET
 * @version 1.0
 */

class PrediggoRecommendationConfig
{
	/** @var PrediggoRecommendationConfig Singleton Object */
	private static $instance;

	/** @var bool is log generation enabled */
	public $logs_fo_file_generation;

	/** @var string Recommendations server Url */
	public $server_url_recommendations;


	/** @var bool is Home page recommendations enabled */
	public $home_recommendations;

	/** @var integer Number of recommendations to display in the block */
	public $home_nb_items;

	/** @var string Title of the block */
	public $home_block_title;


	/** @var bool is 404 page recommendations enabled */
	public $error_recommendations;

	/** @var integer Number of recommendations to display in the block */
	public $error_nb_items;

	/** @var string Title of the block */
	public $error_block_title;


	/** @var bool is Product page recommendations enabled */
	public $product_recommendations;

	/** @var integer Number of recommendations to display in the block */
	public $product_nb_items;

	/** @var string Title of the block */
	public $product_block_title;


	/** @var bool is Category page recommendations enabled */
	public $category_recommendations;

	/** @var integer Number of recommendations to display in the block */
	public $category_nb_items;

	/** @var string Title of the block */
	public $category_block_title;


	/** @var bool is Customers pages recommendations enabled */
	public $customer_recommendations;

	/** @var integer Number of recommendations to display in the block */
	public $customer_nb_items;

	/** @var string Title of the block */
	public $customer_block_title;


	/** @var bool is Cart page recommendations enabled */
	public $cart_recommendations;

	/** @var integer Number of recommendations to display in the block */
	public $cart_nb_items;

	/** @var string Title of the block */
	public $cart_block_title;


	/** @var bool is Best sales page recommendations enabled */
	public $best_sales_recommendations;

	/** @var integer Number of recommendations to display in the block */
	public $best_sales_nb_items;

	/** @var string Title of the block */
	public $best_sales_block_title;


	/** @var bool is Blocklayered recommendations enabled */
	public $blocklayered_recommendations;

	/** @var integer Number of recommendations to display in the block */
	public $blocklayered_nb_items;

	/** @var string Title of the block */
	public $blocklayered_block_title;

	/**
	  * Initialise the object variables
	  */
	private function __construct()
	{
		global $cookie;

		$aLanguages = Language::getLanguages();

		// The FG Corresponds to FILE_GENERATION
		$this->logs_fo_file_generation = (int)Configuration::get('PREDIGGO_FO_LOGS_FG');
		$this->server_url_recommendations = pSQL(Configuration::get('PREDIGGO_SERVER_URL_RECO'));

		$this->home_recommendations = (int)Configuration::get('PREDIGGO_HOME_RECO');
		$this->home_nb_items = (int)Configuration::get('PREDIGGO_HOME_NB_RECO');
		$this->home_block_title = array();

		$this->error_recommendations = (int)Configuration::get('PREDIGGO_ERROR_RECO');
		$this->error_nb_items = (int)Configuration::get('PREDIGGO_ERROR_NB_RECO');
		$this->error_block_title = array();

		$this->product_recommendations = (int)Configuration::get('PREDIGGO_PRODUCT_RECO');
		$this->product_nb_items = (int)Configuration::get('PREDIGGO_PRODUCT_NB_RECO');
		$this->product_block_title = array();

		$this->category_recommendations = (int)Configuration::get('PREDIGGO_CATEGORY_RECO');
		$this->category_nb_items = (int)Configuration::get('PREDIGGO_CATEGORY_NB_RECO');
		$this->category_block_title = array();

		$this->customer_recommendations = (int)Configuration::get('PREDIGGO_CUSTOMER_RECO');
		$this->customer_nb_items = (int)Configuration::get('PREDIGGO_CUSTOMER_NB_RECO');
		$this->customer_block_title = array();

		$this->cart_recommendations = (int)Configuration::get('PREDIGGO_CART_RECO');
		$this->cart_nb_items = (int)Configuration::get('PREDIGGO_CART_NB_RECO');
		$this->cart_block_title = array();

		$this->best_sales_recommendations = (int)Configuration::get('PREDIGGO_BEST_SALES_RECO');
		$this->best_sales_nb_items = (int)Configuration::get('PREDIGGO_BEST_SALES_NB_RECO');
		$this->best_sales_block_title = array();

		$this->blocklayered_recommendations = (int)Configuration::get('PREDIGGO_BLOCK_LAYERED_RECO');
		$this->blocklayered_nb_items = (int)Configuration::get('PREDIGGO_LAYERED_NB_RECO');
		$this->blocklayered_block_title = array();

		// Set the multilingual configurations
		foreach($aLanguages as $aLanguage)
		{
			$this->home_block_title[(int)$aLanguage['id_lang']] = Configuration::get('PREDIGGO_HOME_BLOCK_TITLE', (int)$aLanguage['id_lang']);
			$this->error_block_title[(int)$aLanguage['id_lang']] = Configuration::get('PREDIGGO_ERROR_BLOCK_TITLE', (int)$aLanguage['id_lang']);
			$this->product_block_title[(int)$aLanguage['id_lang']] = Configuration::get('PREDIGGO_PRODUCT_BLOCK_TITLE', (int)$aLanguage['id_lang']);
			$this->category_block_title[(int)$aLanguage['id_lang']] = Configuration::get('PREDIGGO_CATEGORY_BLOCK_TITLE', (int)$aLanguage['id_lang']);
			$this->customer_block_title[(int)$aLanguage['id_lang']] = Configuration::get('PREDIGGO_CUSTOMER_BLOCK_TITLE', (int)$aLanguage['id_lang']);
			$this->cart_block_title[(int)$aLanguage['id_lang']] = Configuration::get('PREDIGGO_CART_BLOCK_TITLE', (int)$aLanguage['id_lang']);
			$this->best_sales_block_title[(int)$aLanguage['id_lang']] = Configuration::get('PREDIGGO_BEST_SALES_BLOCK_TITLE', (int)$aLanguage['id_lang']);
			$this->blocklayered_block_title[(int)$aLanguage['id_lang']] = Configuration::get('PREDIGGO_LAYERED_BLOCK_TITLE', (int)$aLanguage['id_lang']);
		}

	}

    /**
	 * Get PrediggoRecommendationConfig object instance (Singleton)
	 *
	 * @return object PrediggoRecommendationConfig instance
	 */
    public static function singleton()
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }

	/**
	 * Set the Recommendations Configuration vars, executed by the main module object once its installation
	 *
	 * @return bool success or false
	 */
	public function install()
	{
		return 	(Configuration::updateValue('PREDIGGO_FO_LOGS_FG', 1)
		&& Configuration::updateValue('PREDIGGO_SERVER_URL_RECO', 'http://liasg2.epfl.ch:8091')

		&& Configuration::updateValue('PREDIGGO_HOME_RECO', 1)
		&& Configuration::updateValue('PREDIGGO_HOME_NB_RECO', 6)
		&& Configuration::updateValue('PREDIGGO_HOME_BLOCK_TITLE', array(1 => 'Prediggo', 2 => 'Prediggo'))

		&& Configuration::updateValue('PREDIGGO_ERROR_RECO', 1)
		&& Configuration::updateValue('PREDIGGO_ERROR_NB_RECO', 6)
		&& Configuration::updateValue('PREDIGGO_ERROR_BLOCK_TITLE', array(1 => 'Prediggo', 2 => 'Prediggo'))

		&& Configuration::updateValue('PREDIGGO_PRODUCT_RECO', 1)
		&& Configuration::updateValue('PREDIGGO_PRODUCT_NB_RECO', 6)
		&& Configuration::updateValue('PREDIGGO_PRODUCT_BLOCK_TITLE', array(1 => 'Prediggo', 2 => 'Prediggo'))

		&& Configuration::updateValue('PREDIGGO_CATEGORY_RECO', 1)
		&& Configuration::updateValue('PREDIGGO_CATEGORY_NB_RECO', 6)
		&& Configuration::updateValue('PREDIGGO_CATEGORY_BLOCK_TITLE', array(1 => 'Prediggo', 2 => 'Prediggo'))

		&& Configuration::updateValue('PREDIGGO_CUSTOMER_RECO', 1)
		&& Configuration::updateValue('PREDIGGO_CUSTOMER_NB_RECO', 6)
		&& Configuration::updateValue('PREDIGGO_CUSTOMER_BLOCK_TITLE', array(1 => 'Prediggo', 2 => 'Prediggo'))

		&& Configuration::updateValue('PREDIGGO_CART_RECO', 1)
		&& Configuration::updateValue('PREDIGGO_CART_NB_RECO', 6)
		&& Configuration::updateValue('PREDIGGO_CART_BLOCK_TITLE', array(1 => 'Prediggo', 2 => 'Prediggo'))

		&& Configuration::updateValue('PREDIGGO_BEST_SALES_RECO', 1)
		&& Configuration::updateValue('PREDIGGO_BEST_SALES_NB_RECO', 6)
		&& Configuration::updateValue('PREDIGGO_BEST_SALES_BLOCK_TITLE', array(1 => 'Prediggo', 2 => 'Prediggo'))

		&& Configuration::updateValue('PREDIGGO_LAYERED_RECO', 1)
		&& Configuration::updateValue('PREDIGGO_LAYERED_NB_RECO', 6)
		&& Configuration::updateValue('PREDIGGO_LAYERED_BLOCK_TITLE', array(1 => 'Prediggo', 2 => 'Prediggo'))
		);
	}

	/**
	 * Delete the Recommendations Configuration vars, executed by the main module object once its uninstallation
	 *
	 * @return bool success or false
	 */
	public function uninstall()
	{
		return 	(Configuration::deleteByName('PREDIGGO_FO_LOGS_FG')
		&& Configuration::deleteByName('PREDIGGO_SERVER_URL_RECO')

		&& Configuration::deleteByName('PREDIGGO_HOME_RECO')
		&& Configuration::deleteByName('PREDIGGO_HOME_NB_RECO')
		&& Configuration::deleteByName('PREDIGGO_HOME_BLOCK_TITLE')

		&& Configuration::deleteByName('PREDIGGO_ERROR_RECO')
		&& Configuration::deleteByName('PREDIGGO_ERROR_NB_RECO')
		&& Configuration::deleteByName('PREDIGGO_ERROR_BLOCK_TITLE')

		&& Configuration::deleteByName('PREDIGGO_PRODUCT_RECO')
		&& Configuration::deleteByName('PREDIGGO_PRODUCT_NB_RECO')
		&& Configuration::deleteByName('PREDIGGO_PRODUCT_BLOCK_TITLE')

		&& Configuration::deleteByName('PREDIGGO_CATEGORY_RECO')
		&& Configuration::deleteByName('PREDIGGO_CATEGORY_NB_RECO')
		&& Configuration::deleteByName('PREDIGGO_CATEGORY_BLOCK_TITLE')

		&& Configuration::deleteByName('PREDIGGO_CUSTOMER_RECO')
		&& Configuration::deleteByName('PREDIGGO_CUSTOMER_NB_RECO')
		&& Configuration::deleteByName('PREDIGGO_CUSTOMER_BLOCK_TITLE')

		&& Configuration::deleteByName('PREDIGGO_CART_RECO')
		&& Configuration::deleteByName('PREDIGGO_CART_NB_RECO')
		&& Configuration::deleteByName('PREDIGGO_CART_BLOCK_TITLE')

		&& Configuration::deleteByName('PREDIGGO_BEST_SALES_RECO')
		&& Configuration::deleteByName('PREDIGGO_BEST_SALES_NB_RECO')
		&& Configuration::deleteByName('PREDIGGO_BEST_SALES_BLOCK_TITLE')

		&& Configuration::deleteByName('PREDIGGO_LAYERED_RECO')
		&& Configuration::deleteByName('PREDIGGO_LAYERED_NB_RECO')
		&& Configuration::deleteByName('PREDIGGO_LAYERED_BLOCK_TITLE')
		);
	}

	/**
	 * Update the Recommendations Configuration vars, executed by the main module object once its installation
	 *
	 * @return bool success or false
	 */
	public function save()
	{
		return(Configuration::updateValue('PREDIGGO_FO_LOGS_FG', (int)$this->logs_fo_file_generation)
		&& Configuration::updateValue('PREDIGGO_SERVER_URL_RECO', pSQL($this->server_url_recommendations))

		&& Configuration::updateValue('PREDIGGO_HOME_RECO', (int)$this->home_recommendations)
		&& Configuration::updateValue('PREDIGGO_HOME_NB_RECO', (int)$this->home_nb_items)
		&& Configuration::updateValue('PREDIGGO_HOME_BLOCK_TITLE', (array)$this->home_block_title)

		&& Configuration::updateValue('PREDIGGO_ERROR_RECO', (int)$this->error_recommendations)
		&& Configuration::updateValue('PREDIGGO_ERROR_NB_RECO', (int)$this->error_nb_items)
		&& Configuration::updateValue('PREDIGGO_ERROR_BLOCK_TITLE', (array)$this->error_block_title)

		&& Configuration::updateValue('PREDIGGO_PRODUCT_RECO', (int)$this->product_recommendations)
		&& Configuration::updateValue('PREDIGGO_PRODUCT_NB_RECO', (int)$this->product_nb_items)
		&& Configuration::updateValue('PREDIGGO_PRODUCT_BLOCK_TITLE', (array)$this->product_block_title)

		&& Configuration::updateValue('PREDIGGO_CATEGORY_RECO', (int)$this->category_recommendations)
		&& Configuration::updateValue('PREDIGGO_CATEGORY_NB_RECO', (int)$this->category_nb_items)
		&& Configuration::updateValue('PREDIGGO_CATEGORY_BLOCK_TITLE', (array)$this->category_block_title)

		&& Configuration::updateValue('PREDIGGO_CUSTOMER_RECO', (int)$this->customer_recommendations)
		&& Configuration::updateValue('PREDIGGO_CUSTOMER_RECO', (int)$this->customer_nb_items)
		&& Configuration::updateValue('PREDIGGO_CUSTOMER_BLOCK_TITLE', (array)$this->customer_block_title)

		&& Configuration::updateValue('PREDIGGO_CART_RECO', (int)$this->cart_recommendations)
		&& Configuration::updateValue('PREDIGGO_CART_NB_RECO', (int)$this->cart_nb_items)
		&& Configuration::updateValue('PREDIGGO_CART_BLOCK_TITLE', (array)$this->cart_block_title)

		&& Configuration::updateValue('PREDIGGO_BEST_SALES_RECO', (int)$this->best_sales_recommendations)
		&& Configuration::updateValue('PREDIGGO_BEST_SALES_NB_RECO', (int)$this->best_sales_nb_items)
		&& Configuration::updateValue('PREDIGGO_BEST_SALES_BLOCK_TITLE', (array)$this->best_sales_block_title)

		&& Configuration::updateValue('PREDIGGO_LAYERED_RECO', (int)$this->blocklayered_recommendations)
		&& Configuration::updateValue('PREDIGGO_LAYERED_NB_RECO', (int)$this->blocklayered_nb_items)
		&& Configuration::updateValue('PREDIGGO_LAYERED_BLOCK_TITLE', (array)$this->blocklayered_block_title)

		);
	}
}