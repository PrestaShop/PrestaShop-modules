<?php

/**
 * @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
 * @copyright Croissance NET
 * @version 1.0
 */

class PrediggoSearchConfig
{
	/** @var PrediggoSearchConfig Singleton Object */
	private static $instance;

	/** @var bool is log generation enabled */
	public $logs_fo_file_generation;

	/** @var string Search server Url */
	public $server_url_search;

	/** @var bool is search enabled */
	public $search_active;

	/** @var integer Number of products to get on the search */
	public $search_nb_items;

	/** @var integer Number minimum of chars to launch a search */
	public $search_nb_min_chars;

	/** @var bool is prediggo searchandizing enabled */
	public $searchandizing_active;

	/** @var bool is layered block inclusion enabled */
	public $layered_navigation_active;


	/** @var bool is autocompletion enabled */
	public $autocompletion_active;

	/** @var integer Number of products to get on the autocompletion */
	public $autocompletion_nb_items;

	/** @var bool is suggestion list enabled */
	public $suggest_active;

	/** @var string list of the suggestions words separated by comma */
	public $suggest_words;

	/**
	  * Initialise the object variables
	  */
	private function __construct()
	{
		global $cookie;

		$aLanguages = Language::getLanguages();

		$this->search_active = pSQL(Configuration::get('PREDIGGO_SEARCH_ACTIVE'));
		$this->search_nb_items = pSQL(Configuration::get('PREDIGGO_SEARCH_NB_ITEMS'));
		$this->search_nb_min_chars = (int)Configuration::get('PREDIGGO_SEARCH_NB_MIN_CHARS');
		$this->logs_fo_file_generation = (int)Configuration::get('PREDIGGO_FO_LOGS_SEARCH');
		$this->server_url_search = pSQL(Configuration::get('PREDIGGO_SERVER_URL_SEARCH'));
		$this->searchandizing_active = pSQL(Configuration::get('PREDIGGO_SEARCHANDIZING_ACTIVE'));
		$this->layered_navigation_active = pSQL(Configuration::get('PREDIGGO_LAYERED_NAV_ACTIVE'));

		$this->autocompletion_active = pSQL(Configuration::get('PREDIGGO_AUTOCOMPLETION_ACTIVE'));
		$this->autocompletion_nb_items = pSQL(Configuration::get('PREDIGGO_AUTOCOMPLETION_NB_ITEMS'));
		$this->suggest_active = pSQL(Configuration::get('PREDIGGO_SUGGEST_ACTIVE'));
		$this->suggest_words = array();


		// Set the multilingual configurations
		foreach($aLanguages as $aLanguage)
			$this->suggest_words[(int)$aLanguage['id_lang']] = Configuration::get('PREDIGGO_SUGGEST_WORDS', (int)$aLanguage['id_lang']);

	}

    /**
	 * Get PrediggoSearchConfig object instance (Singleton)
	 *
	 * @return object PrediggoSearchConfig instance
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
	 * Set the Search Configuration vars, executed by the main module object once its installation
	 *
	 * @return bool success or false
	 */
	public function install()
	{
		return 	(Configuration::updateValue('PREDIGGO_SEARCH_ACTIVE', 1)
		&& Configuration::updateValue('PREDIGGO_SEARCH_NB_ITEMS', 10)
		&& Configuration::updateValue('PREDIGGO_SEARCH_NB_MIN_CHARS', 3)
		&& Configuration::updateValue('PREDIGGO_FO_LOGS_SEARCH', 1)
		&& Configuration::updateValue('PREDIGGO_SERVER_URL_SEARCH', 'http://liasg2.epfl.ch:8091')
		&& Configuration::updateValue('PREDIGGO_SEARCHANDIZING_ACTIVE', 1)
		&& Configuration::updateValue('PREDIGGO_LAYERED_NAV_ACTIVE', 1)

		&& Configuration::updateValue('PREDIGGO_AUTOCOMPLETION_ACTIVE', 1)
		&& Configuration::updateValue('PREDIGGO_AUTOCOMPLETION_NB_ITEMS', 6)
		&& Configuration::updateValue('PREDIGGO_SUGGEST_ACTIVE', 1)
		&& Configuration::updateValue('PREDIGGO_SUGGEST_WORDS', array(1 => 'iPad 2, iPhone 4S, iPhone', 2 => 'iPad 2, iPhone 4S, iPhone'))
		);
	}

	/**
	 * Delete the Search Configuration vars, executed by the main module object once its uninstallation
	 *
	 * @return bool success or false
	 */
	public function uninstall()
	{
		return 	(Configuration::deleteByName('PREDIGGO_SEARCH_ACTIVE')
		&& Configuration::deleteByName('PREDIGGO_SEARCH_NB_ITEMS')
		&& Configuration::deleteByName('PREDIGGO_SEARCH_NB_MIN_CHARS')
		&& Configuration::deleteByName('PREDIGGO_FO_LOGS_SEARCH')
		&& Configuration::deleteByName('PREDIGGO_SERVER_URL_SEARCH')
		&& Configuration::deleteByName('PREDIGGO_SEARCHANDIZING_ACTIVE')
		&& Configuration::deleteByName('PREDIGGO_LAYERED_NAV_ACTIVE')

		&& Configuration::deleteByName('PREDIGGO_AUTOCOMPLETION_ACTIVE')
		&& Configuration::deleteByName('PREDIGGO_AUTOCOMPLETION_NB_ITEMS')
		&& Configuration::deleteByName('PREDIGGO_SUGGEST_ACTIVE')
		&& Configuration::deleteByName('PREDIGGO_SUGGEST_WORDS')
		);
	}

	/**
	 * Update the Search Configuration vars
	 *
	 * @return bool success or false
	 */
	public function save()
	{
		return(Configuration::updateValue('PREDIGGO_SEARCH_ACTIVE', (int)$this->search_active)
		&& Configuration::updateValue('PREDIGGO_SEARCH_NB_ITEMS', (int)$this->search_nb_items)
		&& Configuration::updateValue('PREDIGGO_SEARCH_NB_MIN_CHARS', (int)$this->search_nb_min_chars)
		&& Configuration::updateValue('PREDIGGO_FO_LOGS_SEARCH', (int)$this->logs_fo_file_generation)
		&& Configuration::updateValue('PREDIGGO_SERVER_URL_SEARCH', pSQL($this->server_url_search))
		&& Configuration::updateValue('PREDIGGO_SEARCHANDIZING_ACTIVE', (int)$this->searchandizing_active)
		&& Configuration::updateValue('PREDIGGO_LAYERED_NAV_ACTIVE', (int)$this->layered_navigation_active)

		&& Configuration::updateValue('PREDIGGO_AUTOCOMPLETION_ACTIVE', (int)$this->autocompletion_active)
		&& Configuration::updateValue('PREDIGGO_AUTOCOMPLETION_NB_ITEMS', (int)$this->autocompletion_nb_items)
		&& Configuration::updateValue('PREDIGGO_SUGGEST_ACTIVE', (int)$this->suggest_active)
		&& Configuration::updateValue('PREDIGGO_SUGGEST_WORDS', (array)$this->suggest_words)
		);
	}
}