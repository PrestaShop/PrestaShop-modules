<?php

/**
 * @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
 * @copyright Croissance NET
 * @version 1.0
 */

class PrediggoExportConfig
{
	/** @var PrediggoExportConfig Singleton Object */
	private static $instance;

	/** @var bool is products export enabled */
	public $products_file_generation;

	/** @var bool is orders export enabled */
	public $orders_file_generation;

	/** @var bool is customers export enabled */
	public $customers_file_generation;

	/** @var bool is log generation enabled */
	public $logs_file_generation;


	/** @var bool is Product picture url included into the export */
	public $export_product_image;

	/** @var bool is Product description included into the export */
	public $export_product_description;

	/** @var integer Number minimum of stock to export a product */
	public $export_product_min_quantity;

	/** @var integer Number of days to define that an order can be exported since its invoice_date */
	public $nb_days_order_valide;

	/** @var integer Number of days to define that a customer can be exported since its last visit */
	public $nb_days_customer_last_visit_valide;


	/** @var string HTPASSWD User */
	public $htpasswd_user;

	/** @var string HTPASSWD Password */
	public $htpasswd_pwd;


	/** @var array List of attributes groups which can be exported */
	public $attributes_groups_ids;

	/** @var array List of features which can be exported */
	public $features_ids;


	/** @var array List of products not allowed to be retrieved as recommendations */
	public $products_ids_not_recommendable;

  	/** @var array List of products not allowed to be retrieved as search result */
	public $products_ids_not_searchable;

	/**
	  * Initialise the object variables
	  */
	private function __construct()
	{
		// The FG Corresponds to FILE_GENERATION
		$this->products_file_generation =(int)Configuration::get('PREDIGGO_PRODUCTS_FG');
		$this->orders_file_generation = (int)Configuration::get('PREDIGGO_ORDERS_FG');
		$this->customers_file_generation = (int)Configuration::get('PREDIGGO_CUSTOMERS_FG');
		$this->logs_file_generation = (int)Configuration::get('PREDIGGO_LOGS_FG');

		$this->export_product_image = (int)Configuration::get('PREDIGGO_EXPORT_PRODUCT_IMG');
		$this->export_product_description = (int)Configuration::get('PREDIGGO_EXPORT_PRODUCT_DESC');
		$this->export_product_min_quantity = (int)Configuration::get('PREDIGGO_EXPORT_PRODUCT_MIN_QTY');
		$this->nb_days_order_valide = (int)Configuration::get('PREDIGGO_NB_DAYS_ORDER');
		$this->nb_days_customer_last_visit_valide = (int)Configuration::get('PREDIGGO_NB_DAYS_CUSTOMER');

		$this->htpasswd_user = pSQL(Configuration::get('PREDIGGO_HTPASSWD_USER'));
		$this->htpasswd_pwd = pSQL(Configuration::get('PREDIGGO_HTPASSWD_PWD'));

		$this->attributes_groups_ids = pSQL(Configuration::get('PREDIGGO_ATTRIBUTES_GROUPS_IDS'));
		$this->features_ids = pSQL(Configuration::get('PREDIGGO_FEATURES_IDS'));

		$this->products_ids_not_recommendable = pSQL(Configuration::get('PREDIGGO_PRODUCTS_NOT_RECO'));
		$this->products_ids_not_searchable = pSQL(Configuration::get('PREDIGGO_PRODUCTS_NOT_SEARCH'));

	}

    /**
	 * Get PrediggoExportConfig object instance (Singleton)
	 *
	 * @return object PrediggoExportConfig instance
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
	 * Set the Export Configuration vars, executed by the main module object once its installation
	 *
	 * @return bool success or false
	 */
	public function install()
	{
		return 	(Configuration::updateValue('PREDIGGO_PRODUCTS_FG', 1)
				&& Configuration::updateValue('PREDIGGO_ORDERS_FG', 1)
				&& Configuration::updateValue('PREDIGGO_CUSTOMERS_FG', 1)
				&& Configuration::updateValue('PREDIGGO_LOGS_FG', 1)

				&& Configuration::updateValue('PREDIGGO_EXPORT_PRODUCT_IMG', 1)
				&& Configuration::updateValue('PREDIGGO_EXPORT_PRODUCT_DESC', 1)
				&& Configuration::updateValue('PREDIGGO_EXPORT_PRODUCT_MIN_QTY', 1)
				&& Configuration::updateValue('PREDIGGO_NB_DAYS_ORDER', 180)
				&& Configuration::updateValue('PREDIGGO_NB_DAYS_CUSTOMER', 180)

				&& Configuration::updateValue('PREDIGGO_HTPASSWD_USER', '')
				&& Configuration::updateValue('PREDIGGO_HTPASSWD_PWD', '')

				&& Configuration::updateValue('PREDIGGO_ATTRIBUTES_GROUPS_IDS', '')
				&& Configuration::updateValue('PREDIGGO_FEATURES_IDS', '')

				&& Configuration::updateValue('PREDIGGO_PRODUCTS_NOT_RECO', '')
				&& Configuration::updateValue('PREDIGGO_PRODUCTS_NOT_SEARCH', ''));
	}

	/**
	 * Delete the Export Configuration vars, executed by the main module object once its uninstallation
	 *
	 * @return bool success or false
	 */
	public function uninstall()
	{
		return 	(Configuration::deleteByName('PREDIGGO_PRODUCTS_FG')
				&& Configuration::deleteByName('PREDIGGO_ORDERS_FG')
				&& Configuration::deleteByName('PREDIGGO_CUSTOMERS_FG')
				&& Configuration::deleteByName('PREDIGGO_LOGS_FG')

				&& Configuration::deleteByName('PREDIGGO_EXPORT_PRODUCT_IMG')
				&& Configuration::deleteByName('PREDIGGO_EXPORT_PRODUCT_DESC')
				&& Configuration::deleteByName('PREDIGGO_EXPORT_PRODUCT_MIN_QTY')
				&& Configuration::deleteByName('PREDIGGO_NB_DAYS_ORDER')
				&& Configuration::deleteByName('PREDIGGO_NB_DAYS_CUSTOMER')

				&& Configuration::deleteByName('PREDIGGO_HTPASSWD_USER')
				&& Configuration::deleteByName('PREDIGGO_HTPASSWD_PWD')

				&& Configuration::deleteByName('PREDIGGO_ATTRIBUTES_GROUPS_IDS')
				&& Configuration::deleteByName('PREDIGGO_FEATURES_IDS')

				&& Configuration::deleteByName('PREDIGGO_PRODUCTS_NOT_RECO')
				&& Configuration::deleteByName('PREDIGGO_PRODUCTS_NOT_SEARCH'));
	}

	/**
	 * Update the Export Configuration vars, executed by the main module object once its uninstallation
	 *
	 * @return bool success or false
	 */
	public function save()
	{
		return(Configuration::updateValue('PREDIGGO_PRODUCTS_FG', (int)$this->products_file_generation)
				&& Configuration::updateValue('PREDIGGO_ORDERS_FG', (int)$this->orders_file_generation)
				&& Configuration::updateValue('PREDIGGO_CUSTOMERS_FG', (int)$this->customers_file_generation)
				&& Configuration::updateValue('PREDIGGO_LOGS_FG', (int)$this->logs_file_generation)

				&& Configuration::updateValue('PREDIGGO_EXPORT_PRODUCT_IMG', (int)$this->export_product_image)
				&& Configuration::updateValue('PREDIGGO_EXPORT_PRODUCT_DESC', (int)$this->export_product_description)
				&& Configuration::updateValue('PREDIGGO_EXPORT_PRODUCT_MIN_QTY', (int)$this->export_product_min_quantity)
				&& Configuration::updateValue('PREDIGGO_NB_DAYS_ORDER', (int)$this->nb_days_order_valide)
				&& Configuration::updateValue('PREDIGGO_NB_DAYS_CUSTOMER', (int)$this->nb_days_customer_last_visit_valide)

				&& Configuration::updateValue('PREDIGGO_HTPASSWD_USER', pSQL($this->htpasswd_user))
				&& Configuration::updateValue('PREDIGGO_HTPASSWD_PWD', pSQL($this->htpasswd_pwd))

				&& Configuration::updateValue('PREDIGGO_ATTRIBUTES_GROUPS_IDS', pSQL($this->attributes_groups_ids))
				&& Configuration::updateValue('PREDIGGO_FEATURES_IDS', pSQL($this->features_ids))

				&& Configuration::updateValue('PREDIGGO_PRODUCTS_NOT_RECO', pSQL($this->products_ids_not_recommendable))
				&& Configuration::updateValue('PREDIGGO_PRODUCTS_NOT_SEARCH', pSQL($this->products_ids_not_searchable)));
	}
}