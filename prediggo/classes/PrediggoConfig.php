<?php

/**
 * @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
 * @copyright Croissance NET
 * @version 1.0
 */

class PrediggoConfig
{
	/** @var PrediggoConfig Singleton Object */
	private static $instance;
	/** @var string Web site id */
	public $web_site_id = false;
	/** @var string Store code id */
	public $store_code_id = false;
	/** @var int Default profile id = id_lang*/
	public $default_profile_id = false;


	/**
	  * Initialise the object variables
	  *
	  */
	private function __construct()
	{
		$this->web_site_id = pSQL(Configuration::get('PREDIGGO_WEB_SITE_ID'));
		$this->store_code_id = pSQL(Configuration::get('PREDIGGO_STORE_CODE_ID'));
		$this->default_profile_id = (int)Configuration::get('PREDIGGO_DEFAULT_PROFILE_ID');
	}

    /**
	 * Get PrediggoConfig object instance (Singleton)
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
	 * Set the Main Configuration vars, executed by the main module object once its installation
	 *
	 * @return bool success or false
	 */
	public function install()
	{
		return 	(Configuration::updateValue('PREDIGGO_WEB_SITE_ID', 'urech123shop')
				&& Configuration::updateValue('PREDIGGO_STORE_CODE_ID', 'Store Code ID')
				&& Configuration::updateValue('PREDIGGO_DEFAULT_PROFILE_ID', (int)Configuration::get('PS_LANG_DEFAULT')));
	}

	/**
	 * Delete the Main Configuration vars, executed by the main module object once its uninstallation
	 *
	 * @return bool success or false
	 */
	public function uninstall()
	{
		return 	(Configuration::deleteByName('PREDIGGO_WEB_SITE_ID')
				&& Configuration::deleteByName('PREDIGGO_STORE_CODE_ID')
				&& Configuration::deleteByName('PREDIGGO_DEFAULT_PROFILE_ID'));
	}

	/**
	 * Update the Main Configuration vars, executed by the main module object once its uninstallation
	 *
	 * @return bool success or false
	 */
	public function save()
	{
		if(!$this->web_site_id
		|| !$this->store_code_id
		|| !$this->default_profile_id
		)
			return false;
		return(Configuration::updateValue('PREDIGGO_WEB_SITE_ID', Tools::htmlentitiesUTF8($this->web_site_id, ENT_QUOTES))
				&& Configuration::updateValue('PREDIGGO_STORE_CODE_ID', Tools::htmlentitiesUTF8($this->store_code_id, ENT_QUOTES))
				&& Configuration::updateValue('PREDIGGO_DEFAULT_PROFILE_ID', (int)$this->default_profile_id));
	}
}