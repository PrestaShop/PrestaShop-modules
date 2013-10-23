<?php
/*
* Shopgate GmbH
*
* URHEBERRECHTSHINWEIS
*
* Dieses Plugin ist urheberrechtlich geschützt. Es darf ausschließlich von Kunden der Shopgate GmbH
* zum Zwecke der eigenen Kommunikation zwischen dem IT-System des Kunden mit dem IT-System der
* Shopgate GmbH über www.shopgate.com verwendet werden. Eine darüber hinausgehende Vervielfältigung, Verbreitung,
* öffentliche Zugänglichmachung, Bearbeitung oder Weitergabe an Dritte ist nur mit unserer vorherigen
* schriftlichen Zustimmung zulässig. Die Regelungen der §§ 69 d Abs. 2, 3 und 69 e UrhG bleiben hiervon unberührt.
*
* COPYRIGHT NOTICE
*
* This plugin is the subject of copyright protection. It is only for the use of Shopgate GmbH customers,
* for the purpose of facilitating communication between the IT system of the customer and the IT system
* of Shopgate GmbH via www.shopgate.com. Any reproduction, dissemination, public propagation, processing or
* transfer to third parties is only permitted where we previously consented thereto in writing. The provisions
* of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain unaffected.
*
*  @author Shopgate GmbH <interfaces@shopgate.com>
*/

/**
 * Manages configuration for library _and_ plugin options.
 *
 * This class is used to save general library settings and specific settings for your plugin.
 *
 * To add your own specific settings
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgateConfig extends ShopgateContainer implements ShopgateConfigInterface {
	/**
	 * @var string The path to the folder where the config file(s) are saved.
	 */
	protected $config_folder_path;
	
	/**
	 * @var array<string, string> List of field names (index) that must have a value according to their validation regex (value)
	 */
	protected $coreValidations = array(
		'customer_number' => '/^[0-9]{5,}$/', // at least 5 digits
		'shop_number' => '/^[0-9]{5,}$/', // at least 5 digits
		'apikey' => '/^[0-9a-f]{20}$/', // exactly 20 hexadecimal digits
		'alias' => '/^[0-9a-zA-Z]+(([\.]?|[\-]+)[0-9a-zA-Z]+)*$/', // start and end with alpha-numerical characters, multiple dashes and single dots in between are ok
		'cname' => '/^(http:\/\/\S+)?$/i', // empty or a string beginning with "http://" followed by any number of non-whitespace characters
		'server' => '/^(live|pg|sl|custom)$/', // "live" or "pg" or "sl" or "custom"
		'api_url' => '/^(https?:\/\/\S+)?$/i', // empty or a string beginning with "http://" or "https://" followed by any number of non-whitespace characters (this is used for testing only, thus the lose validation)
	);
	
	/**
	 * @var array<string, string> List of field names (index) that must have a value according to their validation regex (value)
	 */
	protected $customValidations = array();
	
	/**
	 * @var string The name of the plugin / shop system the plugin is for.
	 */
	protected $plugin_name;
	
	/**
	 * @var bool true to activate the Shopgate error handler.
	 */
	protected $use_custom_error_handler;
	
	
	##################################################################################
	### basic shop information necessary for use of the APIs, mobile redirect etc. ###
	##################################################################################
	/**
	 * @var int Shopgate customer number (at least 5 digits)
	 */
	protected $customer_number;
	
	/**
	 * @var int Shopgate shop number (at least 5 digits)
	 */
	protected $shop_number;
	
	/**
	 * @var string API key (exactly 20 hexadecimal digits)
	 */
	protected $apikey;
	
	/**
	 * @var string Alias of a shop for mobile redirect (start and end with alpha-numerical characters, dashes in between are ok)
	 */
	protected $alias;
	
	/**
	 * @var string Custom URL that to redirect to if a mobile device visits a shop (begin with "http://" or "https://" followed by any number of non-whitespace characters)
	 */
	protected $cname;
	
	/**
	 * @var string The server to use for Shopgate Merchant API communication ("live" or "pg" or "custom")
	 */
	protected $server;
	
	/**
	 * @var string If $server is set to custom, Shopgate Merchant API calls will be made to this URL (empty or a string beginning with "http://" or "https://" followed by any number of non-whitespace characters)
	 */
	protected $api_url;
	
	/**
	 * @var bool true to indicate a shop has been activated by Shopgate
	 */
	protected $shop_is_active;
	
	/**
	 * @var bool true to always use SSL / HTTPS urls for download of external content (such as graphics for the mobile header button)
	 */
	protected $always_use_ssl;
	
	/**
	 * @var bool true to enable updates of keywords that identify mobile devices
	 */
	protected $enable_redirect_keyword_update;
	
	/**
	 * @var bool true to enable default redirect for mobile devices from content sites to mobile website (welcome page)
	 */
	protected $enable_default_redirect;
	
	/**
	 * @var string the encoding the shop system is using internally
	 */
	protected $encoding;
	
	/**
	 * @var bool true to enable automatic encoding conversion to utf-8 during export
	 */
	protected $export_convert_encoding;
	
	
	##############################################################
	### Indicators to (de)activate Shopgate Plugin API actions ###
	##############################################################
	/**
	 * @var bool
	 */
	protected $enable_ping;
	
	/**
	 * @var bool
	 */
	protected $enable_add_order;
	
	/**
	 * @var bool
	 */
	protected $enable_update_order;
	
	/**
	 * @var bool
	 */
	protected $enable_check_cart;
	
	/**
	 * @var bool
	 */
	protected $enable_redeem_coupons;
	
	/**
	 * @var bool
	 */
	protected $enable_get_orders;
	
	/**
	 * @var bool
	 */
	protected $enable_get_customer;
	
	/**
	 * @var bool
	 */
	protected $enable_get_items_csv;
	
	/**
	 * @var bool
	 */
	protected $enable_get_categories_csv;
	
	/**
	 * @var bool
	 */
	protected $enable_get_reviews_csv;
	
	/**
	 * @var bool
	 */
	protected $enable_get_pages_csv;
	
	/**
	 * @var bool
	 */
	protected $enable_get_log_file;
	
	/**
	 * @var bool
	 */
	protected $enable_mobile_website;
	
	/**
	 * @var bool
	 */
	protected $enable_cron;
	
	/**
	 * @var bool
	 */
	protected $enable_clear_log_file;
	
	/**
	 * @var bool
	 */
	protected $enable_clear_cache;
	
	/**
	 * @var bool
	 */
	protected $enable_get_settings;
	
	#######################################################
	### Options regarding shop system specific settings ###
	#######################################################
	/**
	 * @var string The ISO 3166 ALPHA-2 code of the country the plugin uses for export.
	 */
	protected $country;
	
	/**
	 * @var string The ISO 639 code of the language the plugin uses for export.
	 */
	protected $language;
	
	/**
	 * @var string The ISO 4217 code of the currency the plugin uses for export.
	 */
	protected $currency;
	
	/**
	 * @var string CSS style identifier for the parent element the Mobile Header should be attached to.
	 */
	protected $mobile_header_parent;
	
	/**
	 * @var bool True to insert the Mobile Header as first child element, false to append it.
	 */
	protected $mobile_header_prepend;
	
	/**
	 * @var int The capacity (number of lines) of the buffer used for the export actions.
	 */
	protected $export_buffer_capacity;
	
	/**
	 * @var int The maximum number of attributes per product that are created. If the number is exceeded, attributes should be converted to options.
	 */
	protected $max_attributes;
	
	/**
	 * @var string The path to the folder where the export CSV files are stored and retrieved from.
	 */
	protected $export_folder_path;
	
	/**
	 * @var string The path to the folder where the log files are stored and retrieved from.
	 */
	protected $log_folder_path;
	
	/**
	 * @var string The path to the folder where cache files are stored and retrieved from.
	 */
	protected $cache_folder_path;
	
	/**
	 * @var string The name of the items CSV file.
	 */
	protected $items_csv_filename;
	
	/**
	 * @var string The name of the categories CSV file.
	 */
	protected $categories_csv_filename;
	
	/**
	 * @var string The name of the reviews CSV file.
	 */
	protected $reviews_csv_filename;
	
	/**
	 * @var string The name of the pages CSV file.
	 */
	protected $pages_csv_filename;
	
	/**
	 * @var string The name of the access log file.
	 */
	protected $access_log_filename;
	
	/**
	 * @var string The name of the request log file.
	 */
	protected $request_log_filename;
	
	/**
	 * @var string The name of the error log file.
	 */
	protected $error_log_filename;
	
	/**
	 * @var string The name of the debug log file.
	 */
	protected $debug_log_filename;
	
	/**
	 * @var string The name of the cache file for mobile device detection keywords.
	 */
	protected $redirect_keyword_cache_filename;

	/**
	 * @var string The name of the cache file for mobile device skip detection keywords.
	 */
	protected $redirect_skip_keyword_cache_filename;

	/**
	 * @var bool True if the plugin is an adapter between Shopgate's and a third-party-API and servers multiple shops on both ends.
	 */
	protected $is_shopgate_adapter;
	
	/**
	 * @var array<string, mixed> Additional shop system specific settings that cannot (or should not) be generalized and thus be defined by a plugin itself.
	 */
	protected $additionalSettings = array();
	
	
	###################################################
	### Initialization, loading, saving, validating ###
	###################################################
	
	public final function __construct(array $data = array()) {
		// parent constructor not called on purpose, because we need special
		// initialization behaviour here (e.g. loading via array or file)
		
		// default values
		$this->plugin_name = 'not set';
		$this->use_custom_error_handler = 0;
		$this->customer_number = null;
		$this->shop_number = null;
		$this->apikey = null;
		$this->alias = 'my-shop';
		$this->cname = '';
		$this->server = 'live';
		$this->api_url = '';
		$this->shop_is_active = 0;
		$this->always_use_ssl = 0;
		$this->enable_redirect_keyword_update = 0;
		$this->enable_default_redirect = 1;
		$this->encoding = 'UTF-8';
		$this->export_convert_encoding = 1;
		
		$this->enable_ping = 1;
		$this->enable_add_order = 0;
		$this->enable_update_order = 0;
		$this->enable_check_cart = 0;
		$this->enable_redeem_coupons = 0;
		$this->enable_get_orders = 0;
		$this->enable_get_customer = 0;
		$this->enable_get_items_csv = 0;
		$this->enable_get_categories_csv = 0;
		$this->enable_get_reviews_csv = 0;
		$this->enable_get_pages_csv = 0;
		$this->enable_get_log_file = 1;
		$this->enable_mobile_website = 0;
		$this->enable_cron = 0;
		$this->enable_clear_log_file = 1;
		$this->enable_clear_cache = 1;
		$this->enable_get_settings = 0;
		
		$this->country = 'DE';
		$this->language = 'de';
		$this->currency = 'EUR';
		
		$this->mobile_header_parent = 'body';
		$this->mobile_header_prepend = true;
		
		$this->export_buffer_capacity = 100;
		$this->max_attributes = 50;
		
		$this->config_folder_path = SHOPGATE_BASE_DIR.DS.'config';
		
		$this->export_folder_path = SHOPGATE_BASE_DIR.DS.'temp';
		$this->log_folder_path = SHOPGATE_BASE_DIR.DS.'temp'.DS.'logs';
		$this->cache_folder_path = SHOPGATE_BASE_DIR.DS.'temp'.DS.'cache';
		
		$this->items_csv_filename = ShopgateConfigInterface::SHOPGATE_FILE_PREFIX.'items.csv';
		$this->categories_csv_filename = ShopgateConfigInterface::SHOPGATE_FILE_PREFIX.'categories.csv';
		$this->reviews_csv_filename = ShopgateConfigInterface::SHOPGATE_FILE_PREFIX.'reviews.csv';
		$this->pages_csv_filename = ShopgateConfigInterface::SHOPGATE_FILE_PREFIX.'pages.csv';
		
		$this->access_log_filename = ShopgateConfigInterface::SHOPGATE_FILE_PREFIX.'access.log';
		$this->request_log_filename = ShopgateConfigInterface::SHOPGATE_FILE_PREFIX.'request.log';
		$this->error_log_filename = ShopgateConfigInterface::SHOPGATE_FILE_PREFIX.'error.log';
		$this->debug_log_filename = ShopgateConfigInterface::SHOPGATE_FILE_PREFIX.'debug.log';
		
		$this->redirect_keyword_cache_filename = ShopgateConfigInterface::SHOPGATE_FILE_PREFIX.'redirect_keywords.txt';
		$this->redirect_skip_keyword_cache_filename = ShopgateConfigInterface::SHOPGATE_FILE_PREFIX.'skip_redirect_keywords.txt';
		
		$this->is_shopgate_adapter = false;
		
		// call possible sub class' startup()
		if (!$this->startup()) {
			$this->loadArray($data);
		}
	}
	
	/**
	 * Inititialization for sub classes
	 *
	 * This can be overwritten by subclasses to initialize further default values or overwrite the library defaults.
	 * It gets called after default value initialization of the library and before initialization by file or array.
	 *
	 * @return bool false if initialization should be done by ShopgateConfig, true if it has already been done.
	 */
	protected function startup() {
		// nothing to do here
		return false;
	}
	
	/**
	 * Tries to assign the values of an array to the configuration fields or load it from a file.
	 *
	 * This overrides ShopgateContainer::loadArray() which is called on object instantiation. It tries to assign
	 * the values of $data to the class attributes by $data's keys. If a key is not the name of a
	 * class attribute it's appended to $this->additionalSettings.<br />
	 * <br />
	 * If $data is empty or not an array, the method calls $this->loadFile().
	 *
	 * @param $data array<string, mixed> The data to be assigned to the configuration.
	 */
	public function loadArray(array $data = array()) {
		// if no $data was passed try loading the default configuration file
		if (empty($data)) {
			$this->loadFile();
			return;
		}
		
		// if data was passed, map via setters
		$unmappedData = parent::loadArray($data);
		
		// put the rest into $this->additionalSettings
		$this->mapAdditionalSettings($unmappedData);
	}
	
	public function loadFile($path = null) {
		$config = null;
		
		// try loading files
		if (!empty($path) && file_exists($path)) {
			// try $path
			$config = $this->includeFile($path);
			
			if (!$config) {
				throw new ShopgateLibraryException(ShopgateLibraryException::CONFIG_READ_WRITE_ERROR, 'The passed configuration file "'.$path.'" does not exist or does not define the $shopgate_config variable.');
			}
		} else {
			// try myconfig.php
			$config = $this->includeFile($this->config_folder_path.DS.'myconfig.php');
			
			// if unsuccessful, use default configuration values
			if (!$config) {
				return;
			}
		}
		
		// if we got here, we have a $shopgate_config to load
		$unmappedData = parent::loadArray($config);
		$this->mapAdditionalSettings($unmappedData);
	}
	
	public function loadByShopNumber($shopNumber) {
		if (empty($shopNumber) || !preg_match($this->coreValidations['shop_number'], $shopNumber)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::CONFIG_READ_WRITE_ERROR, 'configuration file cannot be found without shop number');
		}
		
		// find all config files
		$configFile = null;
		$files = scandir($this->config_folder_path);
		ob_start();
		foreach ($files as $file) {
			if (!is_file($this->config_folder_path.DS.$file)) {
				continue;
			}
			
			$shopgate_config = null;
			include($this->config_folder_path.DS.$file);
			if (isset($shopgate_config) && isset($shopgate_config['shop_number']) && ($shopgate_config['shop_number'] == $shopNumber)) {
				$configFile = $this->config_folder_path.DS.$file;
				break;
			}
		}
		ob_end_clean();
		if (empty($configFile)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::CONFIG_READ_WRITE_ERROR, 'no configuration file found for shop number "'.$shopNumber.'"', true, false);
		}
		
		$this->loadFile($configFile);
		$this->initFileNames();
	}
	
	public function loadByLanguage($language) {
		if (!is_null($language) && !preg_match('/[a-z]{2}/', $language)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::CONFIG_READ_WRITE_ERROR, 'invalid language code "'.$language.'"', true, false);
		}
		
		$this->loadFile($this->config_folder_path.DS.'myconfig-'.$language.'.php');
		$this->initFileNames();
	}
	
	/**
	 * Sets the file names according to the language of the configuration.
	 */
	protected function initFileNames() {
		$this->items_csv_filename = 'items-'.$this->language.'.csv';
		$this->categories_csv_filename = 'categories-'.$this->language.'.csv';
		$this->reviews_csv_filename = 'reviews-'.$this->language.'.csv';
		$this->pages_csv_filename = 'pages-'.$this->language.'.csv';
	
		$this->access_log_filename = 'access-'.$this->language.'.log';
		$this->request_log_filename = 'request-'.$this->language.'.log';
		$this->error_log_filename = 'error-'.$this->language.'.log';
		$this->debug_log_filename = 'debug-'.$this->language.'.log';
	
		$this->redirect_keyword_cache_filename = 'redirect_keywords-'.$this->language.'.txt';
		$this->redirect_skip_keyword_cache_filename = 'skip_redirect_keywords-'.$this->language.'.txt';
	}
	
	public function saveFile(array $fieldList, $path = null, $validate = true) {
		// if desired, validate before doing anything else
		if ($validate) {
			$this->validate($fieldList);
		}
		
		// preserve values of the fields to save
		$saveFields = array();
		$currentConfig = $this->toArray();
		foreach ($fieldList as $field) {
			$saveFields[$field] = (isset($currentConfig[$field])) ? $currentConfig[$field] : null;
		}
		
		// load the current configuration file
		try {
			$this->loadFile($path);
		} catch (ShopgateLibraryException $e) {
			ShopgateLogger::getInstance()->log('-- Don\'t worry about the "error reading or writing configuration", that was just a routine check during saving.');
		}
		
		// merge old config with new values
		$newConfig = array_merge($this->toArray(), $saveFields);
		
		// if necessary point $path to  myconfig.php
		if (empty($path)) {
			$path = $this->config_folder_path.DS.'myconfig.php';
		}
		
		// create the array definition string and save it to the file
		$shopgateConfigFile = "<?php\n\$shopgate_config = ".var_export($newConfig, true).';';
		if (!@file_put_contents($path, $shopgateConfigFile)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::CONFIG_READ_WRITE_ERROR, 'The configuration file "'.$path.'" could not be saved.');
		}
	}
	
	public function saveFileForLanguage(array $fieldList, $language = null, $validate = true) {
		$fileName = null;
		if (!is_null($language)) {
			$this->setLanguage($language);
			$fieldList[] = 'language';
			$fileName = $this->config_folder_path.DS.'myconfig-'.$language.'.php';
		}
		
		$this->saveFile($fieldList, $fileName, $validate);
	}
	
	public function checkDuplicates() {
		$shopNumbers = array();
		$files = scandir($this->config_folder_path);
		
		foreach ($files as $file) {
			if (!is_file($this->config_folder_path.DS.$file)) {
				continue;
			}
				
			$shopgate_config = null;
			include($this->config_folder_path.DS.$file);
			if (isset($shopgate_config) && isset($shopgate_config['shop_number'])) {
				if (in_array($shopgate_config['shop_number'], $shopNumbers)) {
					return true;
				} else {
					$shopNumbers[] = $shopgate_config['shop_number'];
				}
			}
		}
		
		return false;
	}
	
	public function checkMultipleConfigs() {
		$files = scandir($this->config_folder_path);
		$counter = 0;
		
		foreach ($files as $file) {
			if (!is_file($this->config_folder_path.DS.$file)) {
				continue;
			}
			$counter++;
		}
		
		return ($counter > 1);
	}

	public function checkUseGlobalFor($language) {
		return !file_exists($this->config_folder_path.DS.'myconfig-'.$language.'.php');
	}
	
	public function useGlobalFor($language) {
		$fileName = $this->config_folder_path.DS.'myconfig-'.$language.'.php';
		if (file_exists($fileName)) {
			if (!@unlink($fileName)) {
				throw new ShopgateLibraryException(ShopgateLibraryException::CONFIG_READ_WRITE_ERROR, 'Error deleting configuration file "'.$fileName."'.");
			}
		}
	}
	
	public final function validate(array $fieldList = array()) {
		$properties = $this->buildProperties();
		
		if (empty($fieldList)) {
			$coreFields = array_keys($properties);
			$additionalFields = array_keys($this->additionalSettings);
			$fieldList = array_merge($coreFields, $additionalFields);
		}
		
		$validations = array_merge($this->customValidations, $this->coreValidations);
		$failedFields = array();
		foreach ($fieldList as $field) {
			if (empty($validations[$field]) || preg_match($validations[$field], $properties[$field])) {
				continue;
			} else {
				$failedFields[] = $field;
			}
		}
		
		// run custom validations
		$failedCustomFields = $this->validateCustom($fieldList);
		if (!empty($failedCustomFields) && is_array($failedCustomFields)) {
			$failedFields = array_merge($failedCustomFields, $failedFields);
		}
		
		if (!empty($failedFields)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::CONFIG_INVALID_VALUE, implode(',', $failedFields));
		}
	}
	
	/**
	 * Validates the configuration values.
	 *
	 * @param string[] $fieldList The list of fields to be validated.
	 * @return string[] The list of fields that failed validation or an empty array if validation was successful.
	 */
	protected function validateCustom(array $fieldList = array()) {
		return array();
	}
	
	
	###############
	### Getters ###
	###############
	public function getPluginName() {
		return $this->plugin_name;
	}
	
	public function getUseCustomErrorHandler() {
		return $this->use_custom_error_handler;
	}
	
	public function getCustomerNumber() {
		return $this->customer_number;
	}
	
	public function getShopNumber() {
		return $this->shop_number;
	}
	
	public function getApikey() {
		return $this->apikey;
	}
	
	public function getAlias() {
		return $this->alias;
	}
	
	public function getCname() {
		return rtrim($this->cname, '/');
	}
	
	public function getServer() {
		return $this->server;
	}
	
	public function getApiUrl() {
		switch ($this->getServer()) {
			default: // fall through to 'live'
			case 'live':   return ShopgateConfigInterface::SHOPGATE_API_URL_LIVE;
			case 'sl':     return ShopgateConfigInterface::SHOPGATE_API_URL_SL;
			case 'pg':     return ShopgateConfigInterface::SHOPGATE_API_URL_PG;
			case 'custom': return $this->api_url;
		}
	}
	
	public function getShopIsActive() {
		return $this->shop_is_active;
	}
	
	public function getAlwaysUseSsl() {
		return $this->always_use_ssl;
	}
	
	public function getEnableRedirectKeywordUpdate() {
		return $this->enable_redirect_keyword_update;
	}
	
	public function getEnableDefaultRedirect() {
		return $this->enable_default_redirect;
	}
	
	public function getEncoding() {
		return $this->encoding;
	}
	
	public function getExportConvertEncoding() {
		return $this->export_convert_encoding;
	}
	
	public function getEnablePing() {
		return $this->enable_ping;
	}
	
	public function getEnableAddOrder() {
		return $this->enable_add_order;
	}
	
	public function getEnableUpdateOrder() {
		return $this->enable_update_order;
	}
	
	public function getEnableCheckCart() {
		return $this->enable_check_cart;
	}
	
	public function getEnableRedeemCoupons() {
		return $this->enable_redeem_coupons;
	}
	
	public function getEnableGetOrders() {
		return $this->enable_get_orders;
	}
	
	public function getEnableGetCustomer() {
		return $this->enable_get_customer;
	}
	
	public function getEnableGetItemsCsv() {
		return $this->enable_get_items_csv;
	}
	
	public function getEnableGetCategoriesCsv() {
		return $this->enable_get_categories_csv;
	}
	
	public function getEnableGetReviewsCsv() {
		return $this->enable_get_reviews_csv;
	}
	
	public function getEnableGetPagesCsv() {
		return $this->enable_get_pages_csv;
	}
	
	public function getEnableGetLogFile() {
		return $this->enable_get_log_file;
	}
	
	public function getEnableMobileWebsite() {
		return $this->enable_mobile_website;
	}
	
	public function getEnableCron() {
		return $this->enable_cron;
	}
	
	public function getEnableClearLogFile() {
		return $this->enable_clear_log_file;
	}
	
	public function getEnableClearCache() {
		return $this->enable_clear_cache;
	}
	
	public function getEnableGetSettings() {
		return $this->enable_get_settings;
	}
	
	public function getCountry() {
		return strtoupper($this->country);
	}
	
	public function getLanguage() {
		return strtolower($this->language);
	}
	
	public function getCurrency() {
		return $this->currency;
	}
	
	public function getMobileHeaderParent() {
		return $this->mobile_header_parent;
	}
	
	public function getMobileHeaderPrepend() {
		return $this->mobile_header_prepend;
	}
	
	public function getExportBufferCapacity() {
		return $this->export_buffer_capacity;
	}
	
	public function getMaxAttributes() {
		return $this->max_attributes;
	}
	
	public function getExportFolderPath() {
		return $this->export_folder_path;
	}
	
	public function getLogFolderPath() {
		return $this->log_folder_path;
	}
	
	public function getCacheFolderPath() {
		return $this->cache_folder_path;
	}
	
	public function getItemsCsvFilename() {
		return $this->items_csv_filename;
	}
	
	public function getCategoriesCsvFilename() {
		return $this->categories_csv_filename;
	}
	
	public function getReviewsCsvFilename() {
		return $this->reviews_csv_filename;
	}
	
	public function getPagesCsvFilename() {
		return $this->pages_csv_filename;
	}
	
	public function getAccessLogFilename() {
		return $this->access_log_filename;
	}
	
	public function getRequestLogFilename() {
		return $this->request_log_filename;
	}
	
	public function getErrorLogFilename() {
		return $this->error_log_filename;
	}
	
	public function getDebugLogFilename() {
		return $this->debug_log_filename;
	}
	
	public function getRedirectKeywordCacheFilename() {
		return $this->redirect_keyword_cache_filename;
	}
	
	public function getRedirectSkipKeywordCacheFilename() {
		return $this->redirect_skip_keyword_cache_filename;
	}
	
	public function getItemsCsvPath() {
		return rtrim($this->export_folder_path.DS.$this->items_csv_filename, DS);
	}
	
	public function getCategoriesCsvPath() {
		return rtrim($this->export_folder_path.DS.$this->categories_csv_filename, DS);
	}
	
	public function getReviewsCsvPath() {
		return rtrim($this->export_folder_path.DS.$this->reviews_csv_filename, DS);
	}
	
	public function getPagesCsvPath() {
		return rtrim($this->export_folder_path.DS.$this->pages_csv_filename, DS);
	}
	
	public function getAccessLogPath() {
		return rtrim($this->log_folder_path.DS.$this->access_log_filename, DS);
	}
	
	public function getRequestLogPath() {
		return rtrim($this->log_folder_path.DS.$this->request_log_filename, DS);
	}
	
	public function getErrorLogPath() {
		return rtrim($this->log_folder_path.DS.$this->error_log_filename, DS);
	}
	
	public function getDebugLogPath() {
		return rtrim($this->log_folder_path.DS.$this->debug_log_filename, DS);
	}
	
	public function getRedirectKeywordCachePath() {
		return rtrim($this->cache_folder_path.DS.$this->redirect_keyword_cache_filename, DS);
	}
	
	public function getRedirectSkipKeywordCachePath() {
		return rtrim($this->cache_folder_path.DS.$this->redirect_skip_keyword_cache_filename, DS);
	}
	
	public function getIsShopgateAdapter() {
		return $this->is_shopgate_adapter;
	}
	
	
	###############
	### Setters ###
	###############
	public function setPluginName($value) {
		$this->plugin_name = $value;
	}
	
	public function setUseCustomErrorHandler($value) {
		$this->use_custom_error_handler = $value;
	}
	
	public function setCustomerNumber($value) {
		$this->customer_number = $value;
	}
	
	public function setShopNumber($value) {
		$this->shop_number = $value;
	}
	
	public function setApikey($value) {
		$this->apikey = $value;
	}
	
	public function setAlias($value) {
		$this->alias = $value;
	}
	
	public function setCname($value) {
		$this->cname = rtrim($value, '/');
	}
	
	public function setServer($value) {
		$this->server = $value;
	}
	
	public function setApiUrl($value) {
		$this->api_url = $value;
	}
	
	public function setShopIsActive($value) {
		$this->shop_is_active = $value;
	}
	
	public function setAlwaysUseSsl($value) {
		$this->always_use_ssl = $value;
	}
	
	public function setEnableRedirectKeywordUpdate($value) {
		$this->enable_redirect_keyword_update = $value;
	}
	
	public function setEnableDefaultRedirect($value) {
		$this->enable_default_redirect = $value;
	}
	
	public function setEncoding($value) {
		$this->encoding = $value;
	}
	
	public function setExportConvertEncoding($value) {
		$this->export_convert_encoding = $value;
	}
	
	public function setEnablePing($value) {
		$this->enable_ping = $value;
	}
	
	public function setEnableAddOrder($value) {
		$this->enable_add_order = $value;
	}
	
	public function setEnableUpdateOrder($value) {
		$this->enable_update_order = $value;
	}

	public function setEnableCheckCart($value) {
		$this->enable_check_cart = $value;
	}
	
	public function setEnableRedeemCoupons($value) {
		$this->enable_redeem_coupons = $value;
	}
	
	public function setEnableGetOrders($value) {
		$this->enable_get_orders = $value;
	}
	
	public function setEnableGetCustomer($value) {
		$this->enable_get_customer = $value;
	}
	
	public function setEnableGetItemsCsv($value) {
		$this->enable_get_items_csv = $value;
	}
	
	public function setEnableGetCategoriesCsv($value) {
		$this->enable_get_categories_csv = $value;
	}
	
	public function setEnableGetReviewsCsv($value) {
		$this->enable_get_reviews_csv = $value;
	}
	
	public function setEnableGetPagesCsv($value) {
		$this->enable_get_pages_csv = $value;
	}
	
	public function setEnableGetLogFile($value) {
		$this->enable_get_log_file = $value;
	}
	
	public function setEnableMobileWebsite($value) {
		$this->enable_mobile_website = $value;
	}
	
	public function setEnableCron($value) {
		$this->enable_cron = $value;
	}
	
	public function setEnableClearLogFile($value) {
		$this->enable_clear_log_file = $value;
	}
	
	public function setEnableClearCache($value) {
		$this->enable_clear_cache = $value;
	}
	
	public function setEnableGetSettings($value) {
		$this->enable_get_settings = $value;
	}
	
	public function setCountry($value) {
		$this->country = strtoupper($value);
	}
	
	public function setLanguage($value) {
		$this->language = strtolower($value);
	}
	
	public function setCurrency($value) {
		$this->currency = $value;
	}
	
	public function setMobileHeaderParent($value) {
		$this->mobile_header_parent = $value;
	}
	
	public function setMobileHeaderPrepend($value) {
		$this->mobile_header_prepend = $value;
	}
	
	public function setExportBufferCapacity($value) {
		$this->export_buffer_capacity = $value;
	}
	
	public function setMaxAttributes($value) {
		$this->max_attributes = $value;
	}
	
	public function setExportFolderPath($value) {
		$this->export_folder_path = $value;
	}
	
	public function setLogFolderPath($value) {
		$this->log_folder_path = $value;
	}
	
	public function setCacheFolderPath($value) {
		$this->cache_folder_path = $value;
	}
	
	public function setItemsCsvFilename($value) {
		$this->items_csv_filename = $value;
	}
	
	public function setCategoriesCsvFilename($value) {
		$this->categories_csv_filename = $value;
	}
	
	public function setReviewsCsvFilename($value) {
		$this->reviews_csv_filename = $value;
	}
	
	public function setPagesCsvFilename($value) {
		$this->pages_csv_filename = $value;
	}
	
	public function setAccessLogFilename($value) {
		$this->access_log_filename = $value;
	}
	
	public function setRequestLogFilename($value) {
		$this->request_log_filename = $value;
	}
	
	public function setErrorLogFilename($value) {
		$this->error_log_filename = $value;
	}
	
	public function setDebugLogFilename($value) {
		$this->debug_log_filename = $value;
	}
	
	public function setRedirectKeywordCacheFilename($value) {
		$this->redirect_keyword_cache_filename = $value;
	}
	
	public function setRedirectSkipKeywordCacheFilename($value) {
		$this->redirect_skip_keyword_cache_filename = $value;
	}
	
	public function setItemsCsvPath($value) {
		$dir = dirname($value);
		$file = basename($value);
		
		if (!empty($dir) && !empty($file)) {
			$this->export_folder_path = $dir;
			$this->items_csv_filename = $file;
		}
	}
	
	public function setCategoriesCsvPath($value) {
		$dir = dirname($value);
		$file = basename($value);
		
		if (!empty($dir) && !empty($file)) {
			$this->export_folder_path = $dir;
			$this->categories_csv_filename = $file;
		}
	}
	
	public function setReviewsCsvPath($value) {
		$dir = dirname($value);
		$file = basename($value);
		
		if (!empty($dir) && !empty($file)) {
			$this->export_folder_path = $dir;
			$this->reviews_csv_filename = $file;
		}
	}
	
	public function setPagesCsvPath($value) {
		$dir = dirname($value);
		$file = basename($value);
		
		if (!empty($dir) && !empty($file)) {
			$this->export_folder_path = $dir;
			$this->pages_csv_filename = $file;
		}
	}
	
	public function setAccessLogPath($value) {
		$dir = dirname($value);
		$file = basename($value);
		
		if (!empty($dir) && !empty($file)) {
			$this->log_folder_path = $dir;
			$this->access_log_filename = $file;
		}
	}
	
	public function setRequestLogPath($value) {
		$dir = dirname($value);
		$file = basename($value);
		
		if (!empty($dir) && !empty($file)) {
			$this->log_folder_path = $dir;
			$this->request_log_filename = $file;
		}
	}
	
	public function setErrorLogPath($value) {
		$dir = dirname($value);
		$file = basename($value);
		
		if (!empty($dir) && !empty($file)) {
			$this->log_folder_path = $dir;
			$this->error_log_filename = $file;
		}
	}
	
	public function setDebugLogPath($value) {
		$dir = dirname($value);
		$file = basename($value);
		
		if (!empty($dir) && !empty($file)) {
			$this->log_folder_path = $dir;
			$this->debug_log_filename = $file;
		}
	}
	
	public function setRedirectKeywordCachePath($value) {
		$dir = dirname($value);
		$file = basename($value);
		
		if (!empty($dir) && !empty($file)) {
			$this->cache_folder_path = $dir;
			$this->redirect_keyword_cache_filename = $file;
		}
	}
	
	public function setRedirectSkipKeywordCachePath($value) {
		$dir = dirname($value);
		$file = basename($value);
		
		if (!empty($dir) && !empty($file)) {
			$this->cache_folder_path = $dir;
			$this->redirect_skip_keyword_cache_filename = $file;
		}
	}
	
	public function setIsShopgateAdapter($value) {
		$this->is_shopgate_adapter = $value;
	}
	
	
	###############
	### Helpers ###
	###############
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitConfig($this);
	}
	
	public function returnAdditionalSetting($setting) {
		return (isset($this->additionalSettings[$setting])) ? $this->additionalSettings[$setting] : null;
	}
	
	public function returnAdditionalSettings() {
		return $this->additionalSettings;
	}
	
	public function buildProperties() {
		$properties = parent::buildProperties();
		
		// append the file paths
		$properties['items_csv_path'] = $this->getItemsCsvPath();
		$properties['categories_csv_path'] = $this->getCategoriesCsvPath();
		$properties['reviews_csv_path'] = $this->getReviewsCsvPath();
		$properties['pages_csv_path'] = $this->getPagesCsvPath();
		
		$properties['access_log_path'] = $this->getAccessLogPath();
		$properties['request_log_path'] = $this->getRequestLogPath();
		$properties['error_log_path'] = $this->getErrorLogPath();
		$properties['debug_log_path'] = $this->getDebugLogPath();
		
		$properties['redirect_keyword_cache_path'] = $this->getRedirectKeywordCachePath();
		$properties['redirect_skip_keyword_cache_path'] = $this->getRedirectSkipKeywordCachePath();
		
		return $properties;
	}
	
	/**
	 * Tries to include the specified file and check for $shopgate_config.
	 *
	 * @param string $path The path to the configuration file.
	 * @return mixed[]|bool The $shopgate_config array if the file was included and defined $shopgate_config, false otherwise.
	 */
	private function includeFile($path) {
		$shopgate_config = null;
		
		// try including the file
		if (file_exists($path)) {
			ob_start();
			include($path);
			ob_end_clean();
		} else {
			return false;
		}
		
		// check $shopgate_config
		if (!isset($shopgate_config) || !is_array($shopgate_config)) {
			return false;
		} else {
			return $shopgate_config;
		}
	}
	
	/**
	 * Maps the passed data to the additional settings array.
	 *
	 * @param array<string, mixed> $data The data to map.
	 */
	private function mapAdditionalSettings($data = array()) {
		foreach ($data as $key => $value) {
			$this->additionalSettings[$key] = $value;
		}
	}
	
	
	##################################
	### Deprecated / Compatibility ###
	##################################
	/**
	 * Routes static calls to ShopgateConfigOld (the former ShopgateConfig class).
	 *
	 * This is for compatibility reasons only. The use of ShopgateConfigOld is deprecated!
	 *
	 * @deprecated
	 * @param string $name Method name.
	 * @param mixed[] $arguments Arguments to call the method with.
	 * @return mixed The return value of the called method.
	 * @throws ShopgateLibraryException whenever a ShopgateLibraryException is thrown by ShopgateConfigOld.
	 */
	public static function __callStatic($name, $arguments) {
		return call_user_func_array(array('ShopgateConfigOld', $name), $arguments);
	}
	
	/**
	 * This is for compatibility reasons only. The use of ShopgateConfigOld is deprecated!
	 *
	 * @deprecated
	 * @throws ShopgateLibraryException whenever a ShopgateLibraryException is thrown by ShopgateConfigOld's method.
	 */
	public static function setConfig(array $newConfig, $validate = true) {
		return ShopgateConfigOld::setConfig($newConfig, $validate);
	}
	
	/**
	 * This is for compatibility reasons only. The use of ShopgateConfigOld is deprecated!
	 *
	 * @deprecated
	 * @throws ShopgateLibraryException whenever a ShopgateLibraryException is thrown by ShopgateConfigOld's method.
	 */
	public static function validateAndReturnConfig() {
		return ShopgateConfigOld::validateAndReturnConfig();
	}
	
	/**
	 * This is for compatibility reasons only. The use of ShopgateConfigOld is deprecated!
	 *
	 * @deprecated
	 * @throws ShopgateLibraryException whenever a ShopgateLibraryException is thrown by ShopgateConfigOld's method.
	 */
	public static function getConfig() {
		return ShopgateConfigOld::getConfig();
	}
	
	/**
	 * This is for compatibility reasons only. The use of ShopgateConfigOld is deprecated!
	 *
	 * @deprecated
	 * @throws ShopgateLibraryException whenever a ShopgateLibraryException is thrown by ShopgateConfigOld's method.
	 */
	public static function getConfigField($field) {
		return ShopgateConfigOld::getConfigField($field);
	}
	
	/**
	 * This is for compatibility reasons only. The use of ShopgateConfigOld is deprecated!
	 *
	 * @deprecated
	 * @throws ShopgateLibraryException whenever a ShopgateLibraryException is thrown by ShopgateConfigOld's method.
	 */
	public static function getLogFilePath($type = ShopgateLogger::LOGTYPE_ERROR) {
		return ShopgateConfigOld::getLogFilePath($type = ShopgateLogger::LOGTYPE_ERROR);
	}
	
	/**
	 * This is for compatibility reasons only. The use of ShopgateConfigOld is deprecated!
	 *
	 * @deprecated
	 * @throws ShopgateLibraryException whenever a ShopgateLibraryException is thrown by ShopgateConfigOld's method.
	 */
	public static function getItemsCsvFilePath() {
		return ShopgateConfigOld::getItemsCsvFilePath();
	}
	
	/**
	 * This is for compatibility reasons only. The use of ShopgateConfigOld is deprecated!
	 *
	 * @deprecated
	 * @throws ShopgateLibraryException whenever a ShopgateLibraryException is thrown by ShopgateConfigOld's method.
	 */
	public static function getCategoriesCsvFilePath() {
		return ShopgateConfigOld::getCategoriesCsvFilePath();
	}
	
	/**
	 * This is for compatibility reasons only. The use of ShopgateConfigOld is deprecated!
	 *
	 * @deprecated
	 * @throws ShopgateLibraryException whenever a ShopgateLibraryException is thrown by ShopgateConfigOld's method.
	 */
	public static function getReviewsCsvFilePath() {
		return ShopgateConfigOld::getReviewsCsvFilePath();
	}
	
	/**
	 * This is for compatibility reasons only. The use of ShopgateConfigOld is deprecated!
	 *
	 * @deprecated
	 * @throws ShopgateLibraryException whenever a ShopgateLibraryException is thrown by ShopgateConfigOld's method.
	 */
	public static function getPagesCsvFilePath() {
		return ShopgateConfigOld::getPagesCsvFilePath();
	}
	
	/**
	 * This is for compatibility reasons only. The use of ShopgateConfigOld is deprecated!
	 *
	 * @deprecated
	 * @throws ShopgateLibraryException whenever a ShopgateLibraryException is thrown by ShopgateConfigOld's method.
	 */
	public static function getRedirectKeywordsFilePath() {
		return ShopgateConfigOld::getRedirectKeywordsFilePath();
	}
	
	/**
	 * This is for compatibility reasons only. The use of ShopgateConfigOld is deprecated!
	 *
	 * @deprecated
	 * @throws ShopgateLibraryException whenever a ShopgateLibraryException is thrown by ShopgateConfigOld's method.
	 */
	public static function getSkipRedirectKeywordsFilePath() {
		return ShopgateConfigOld::getSkipRedirectKeywordsFilePath();
	}
	
	/**
	 * This is for compatibility reasons only. The use of ShopgateConfigOld is deprecated!
	 *
	 * @deprecated
	 * @throws ShopgateLibraryException whenever a ShopgateLibraryException is thrown by ShopgateConfigOld's method.
	 */
	public static function saveConfig() {
		return ShopgateConfigOld::saveConfig();
	}
}

/**
 * Einstellungen für das Framework
 *
 * @version 1.0.0
 * @deprecated
 * @see ShopgateConfig
 */
class ShopgateConfigOld extends ShopgateObject {

	/**
	 * Die Standardeinstellungen.
	 *
	 * Die hier festgelegten Einstellungen werden aus der Datei
	 * config.php bzw. myconfig.php überschrieben und erweitert
	 *
	 * - api_url -> Die URL zum Shopgate-Server.
	 * - customer_number -> Die Kundennummer des Händleraccounts
	 * - apikey -> Der API-Key des Händlers. Dieser muss nach änderung angepasst werden.
	 * - shop_number -> Die Nummer des Shops.
	 * - server -> An welchen Server die Daten gesendet werden.
	 * - plugin -> Das PlugIn, welches verwendet werden soll.
	 * - plugin_language -> Spracheinstellung für das Plugin. Zur Zeit nur DE.
	 * - plugin_currency -> Währungseinstellung für das Plugin. Zur Zeit nur EUR.
	 * - plugin_root_dir -> Das Basisverzeichniss für das PlugIn.
	 * - enable_ping -> Ping erlaubt.
	 * - enable_cron -> Cron erlaubt.
	 * - enable_get_shop_info -> Infos ueber das Shopsystem abholen
	 * - enable_add_order -> Übergeben von bestelldaten erlaubt.
	 * - enable_update_order -> Übergeben von bestelldaten erlaubt.
	 * - enable_connect -> Shopgate Connect erlaubt.
	 * - enable_get_items_csv -> Abholen der Produkt-CSV erlaubt.
	 * - enable_get_reviews_csv -> Abholen der Review-CSV erlaubt.
	 * - enable_get_pages_csv -> Abholen der Pages-CSV erlaubt.
	 * - enable_get_log_file -> Abholen der Log-Files erlaubt
	 * - generate_items_csv_on_the_fly -> Die CSV direkt beim Download erstellen
	 *
	 * @var array
	 */
	private static $config =  array(
		'api_url' => 'https://api.shopgate.com/merchant/',
		'customer_number' => 'THE_CUSTOMER_NUMBER',
		'shop_number' => 'THE_SHOP_NUMBER',
		'apikey' => 'THE_API_KEY',
		'alias' => 'my-shop',
		'cname' => '',
		'server' => 'live',
		'plugin' => 'example',
		'plugin_language' => 'DE',
		'plugin_currency' => 'EUR',
		'plugin_root_dir' => "",
		'enable_ping' => true,
		'enable_cron' => true,
		'enable_add_order' => true,
		'enable_update_order' => true,
		'enable_get_customer' => true,
		'enable_get_categories_csv' => true,
		'enable_get_orders' => true,
		'enable_get_items_csv' => true,
		'enable_get_reviews_csv' => true,
		'enable_get_pages_csv' => true,
		'enable_get_log_file' => true,
		'enable_clear_log_file' => true,
		'enable_mobile_website' => true,
		'generate_items_csv_on_the_fly' => true,
		'max_attributes' => 50,
		'use_custom_error_handler' => false,
		'encoding' => 'UTF-8',
	);

	/**
	 * Übergeben und überprüfen der Einstellungen.
	 *
	 * @deprecated
	 * @param array $newConfig
	 */
	public static final function setConfig(array $newConfig, $validate = true) {
		self::deprecated(__METHOD__);
		
		if($validate) {
			self::validateConfig($newConfig);
		}
		self::$config = array_merge(self::$config, $newConfig);
	}

	/**
	 * Gibt das Konfigurations-Array zurück.
	 *
	 * @deprecated
	 */
	public static final function validateAndReturnConfig() {
		self::deprecated(__METHOD__);
		
		try {
			self::validateConfig(self::$config);
		} catch (ShopgateLibraryException $e) { throw $e; }

		return self::getConfig();
	}

	/**
	 *
	 * Returnd the configuration without validating
	 *
	 * @deprecated
	 * @return array
	 */
	public static function getConfig() {
		self::deprecated(__METHOD__);
		
		return self::$config;
	}

	public static function getConfigField($field) {
		self::deprecated(__METHOD__);
		
		if(isset(self::$config[$field])) return self::$config[$field];
		else return null;
	}

	public static final function getPluginName() {
		self::deprecated(__METHOD__);
		
		return self::$config["plugin"];
	}

	/**
	 * Gibt den Pfad zur Error-Log-Datei zurück.
	 * Für diese Datei sollten Schreib- und leserechte gewährt werden.
	 *
	 * @deprecated
	 */
	public static final function getLogFilePath($type = ShopgateLogger::LOGTYPE_ERROR) {
		self::deprecated(__METHOD__);
		
		switch (strtolower($type)) {
			default: $type = 'error';
			case "access": case "request": case "request": case "debug":
		}

		if(isset(self::$config['path_to_'.strtolower($type).'_log_file'])) {
			return self::$config['path_to_'.strtolower($type).'_log_file'];
		} else {
			return SHOPGATE_BASE_DIR.'/temp/logs/'.strtolower($type).'.log';
		}
	}

	/**
	 * Gibt den Pfad zur items-csv-Datei zurück.
	 * Für diese Datei sollten Schreib- und leserechte gewährt werden.
	 *
	 * @deprecated
	 */
	public static final function getItemsCsvFilePath() {
		self::deprecated(__METHOD__);
		
		if(isset(self::$config['path_to_items_csv_file'])) {
			return self::$config['path_to_items_csv_file'];
		} else {
			return SHOPGATE_BASE_DIR.'/temp/items.csv';
		}
	}

	/**
	 * @deprecated
	 */
	public static final function getCategoriesCsvFilePath() {
		self::deprecated(__METHOD__);
		
		if(isset(self::$config['path_to_categories_csv_file'])) {
			return self::$config['path_to_categories_csv_file'];
		} else {
			return SHOPGATE_BASE_DIR.'/temp/categories.csv';
		}
	}

	/**
	 * Gibt den Pfad zur review-csv-Datei zurück
	 * Für diese Datei sollten Schreib- und leserechte gewährt werden
	 *
	 * @deprecated
	 */
	public static final function getReviewsCsvFilePath() {
		self::deprecated(__METHOD__);
		
		if(isset(self::$config['path_to_reviews_csv_file'])) {
			return self::$config['path_to_reviews_csv_file'];
		} else {
			return SHOPGATE_BASE_DIR.'/temp/reviews.csv';
		}
	}

	/**
	 * Gibt den Pfad zur pages-csv-Datei zurück.
	 * Für diese Datei sollten Schreib- und leserechte gewährt werden.
	 *
	 * @deprecated
	 */
	public static final function getPagesCsvFilePath() {
		self::deprecated(__METHOD__);
		
		if(isset(self::$config['path_to_pages_csv_file'])) {
			return self::$config['path_to_pages_csv_file'];
		} else {
			return SHOPGATE_BASE_DIR.'/temp/pages.csv';
		}
	}

	/**
	 * @return the absolute Path for the Redirect-Keywords-Caching-File
	 * @deprecated
	 */
	public static final function getRedirectKeywordsFilePath() {
		self::deprecated(__METHOD__);
		
		if(isset(self::$config['path_to_redirect_keywords_file'])) {
			return self::$config['path_to_redirect_keywords_file'];
		} else {
			return SHOPGATE_BASE_DIR.'/temp/cache/redirect_keywords.txt';
		}
	}

	/**
	 * @return the absolute Path for the Skip-Redirect-Keywords-Caching-File
	 * @deprecated
	 */
	public static final function getSkipRedirectKeywordsFilePath() {
		self::deprecated(__METHOD__);
		
		if(isset(self::$config['path_to_skip_redirect_keywords_file'])) {
			return self::$config['path_to_skip_redirect_keywords_file'];
		} else {
			return SHOPGATE_BASE_DIR.'/temp/cache/skip_redirect_keywords.txt';
		}
	}

	/**
	 * Prüft, ob alle Pflichtfelder gesetzt sind und setzt die api_url.
	 *
	 * @deprecated
	 * @param array $newConfig
	 * @throws ShopgateLibraryException
	 */
	private static function validateConfig(array $newConfig) {
		self::deprecated(__METHOD__);
		
		//Pflichtfelder überprüfen
		if (!preg_match("/^\S+/", $newConfig['apikey'])) {
			throw new ShopgateLibraryException(
				ShopgateLibraryException::CONFIG_INVALID_VALUE,
				"Field 'apikey' contains invalid value '{$newConfig['apikey']}'."
			);
		}
		if(!preg_match("/^\d{5,}$/", $newConfig['customer_number'])){
			throw new ShopgateLibraryException(
				ShopgateLibraryException::CONFIG_INVALID_VALUE,
				"Field 'customer_number' contains invalid value '{$newConfig['customer_number']}'."
			);
		}
		if (!preg_match("/^\d{5,}$/", $newConfig['shop_number'])) {
			throw new ShopgateLibraryException(
				ShopgateLibraryException::CONFIG_INVALID_VALUE,
				"Field 'shop_number' contains invalid value '{$newConfig['shop_number']}'."
			);
		}

		////////////////////////////////////////////////////////////////////////
		// Server URL setzen
		////////////////////////////////////////////////////////////////////////
		if(!empty($newConfig["server"]) && $newConfig["server"] === "pg") {
			// Playground?
			self::$config["api_url"] = "https://api.shopgatepg.com/merchant/";
		} else if(!empty($newConfig["server"]) && $newConfig["server"] === "custom"
		&& !empty($newConfig["server_custom_url"])) {
			// Eigener Test-Server?
			self::$config["api_url"] = $newConfig["server_custom_url"];
		} else {
			// Live-Server?
			self::$config["api_url"] = "https://api.shopgate.com/merchant/";
		}
	}

	/**
	 * @deprecated
	 * @throws ShopgateLibraryException
	 */
	public static function saveConfig() {
		self::deprecated(__METHOD__);
		
		$config = self::getConfig();

		$returnString  = "<?php"."\r\n";

		$returnString .= "\$shopgate_config = array();\r\n";

		foreach($config as $key => $field)
		{
			if($key != 'save')
			{
				if(is_bool($field) || $field === "true" || $field === "false") {
					if($field === "true") $field = true;
					if($field === "false") $field = false;

					$returnString .= '$shopgate_config["'.$key.'"] = '.($field?'true':'false').';'."\r\n";
				}
				else if(is_numeric($field)) {
					$returnString .= '$shopgate_config["'.$key.'"] = '.$field.';'."\r\n";
				}
				else {
					$returnString .= '$shopgate_config["'.$key.'"] = "'.$field.'";'."\r\n";
				}
			}
		}

		$message = "";
		$handle = @fopen(dirname(__FILE__).'/../config/myconfig.php', 'w+');
		if($handle == false){
			throw new ShopgateLibraryException(ShopgateLibraryException::CONFIG_READ_WRITE_ERROR);
			fclose($handle);
		}else{
			if(!fwrite($handle, $returnString))
			throw new ShopgateLibraryException(ShopgateLibraryException::CONFIG_READ_WRITE_ERROR);
		}

		fclose($handle);
	}
	
	/**
	 * Issues a PHP deprecated warning and log entry for calls to deprecated ShopgateConfigOld methods.
	 *
	 * @param string $methodName The name of the called method.
	 */
	private static function deprecated($methodName) {
		$message = 'Use of '.$methodName.' and the whole ShopgateConfigOld class is deprecated.';
		trigger_error($message, E_USER_DEPRECATED);
		ShopgateLogger::getInstance()->log($message);
	}
}

/**
 * Manages configuration for library _and_ plugin options.
 *
 * This class is used to save general library settings and specific settings for your plugin.
 *
 * To add your own specific settings
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
interface ShopgateConfigInterface {
	const SHOPGATE_API_URL_LIVE = 'https://api.shopgate.com/merchant/';
	const SHOPGATE_API_URL_SL   = 'https://api.shopgatesl.com/merchant/';
	const SHOPGATE_API_URL_PG   = 'https://api.shopgatepg.com/merchant/';
	
	const SHOPGATE_FILE_PREFIX = 'shopgate_';
	
	/**
	 * Tries to load the configuration from a file.
	 *
	 * If a $path is passed, this method tries to include the file. If that fails an exception is thrown.<br />
	 * <br />
	 * If $path is empty it tries to load .../shopgate_library/config/myconfig.php or if that fails,
	 * .../shopgate_library/config/config.php is tried to be loaded. If that fails too, an exception is
	 * thrown.<br />
	 * <br />
	 * The configuration file must be a PHP script defining an indexed array called $shopgate_config
	 * containing the desired configuration values to set. If that is not the case, an exception is thrown
	 *
	 * @param string $path The path to the configuration file or nothing to load the default Shopgate Library configuration files.
	 * @throws ShopgateLibraryException in case a configuration file could not be loaded or the $shopgate_config is not set.
	 */
	public function loadFile($path = null);
	
	/**
	 * Loads the configuration file by a given language or the global configuration file.
	 *
	 * @param string|null $language the ISO-639 code of the language or null to load global configuration
	 */
	public function loadByLanguage($language);

	/**
	 * Loads the configuration file for a given Shopgate shop number.
	 *
	 * @param string $shopNumber The shop number.
	 * @throws ShopgateLibraryException in case the $shopNumber is empty or no configuration file can be found.
	 */
	public function loadByShopNumber($shopNumber);
	
	/**
	 * Saves the desired configuration fields to the specified file or myconfig.php.
	 *
	 * This calls $this->loadFile() with the given $path to load the current configuration. In case that fails, the $shopgate_config
	 * array is initialized empty. The values defined in $fieldList are then validated (if desired), assigned to $shopgate_config and
	 * saved to the specified file or myconfig.php.
	 *
	 * In case the file cannot be (over)written or created, an exception with code ShopgateLibrary::CONFIG_READ_WRITE_ERROR is thrown.
	 *
	 * In case the validation fails for one or more fields, an exception with code ShopgateLibrary::CONFIG_ is thrown. The failed
	 * fields are appended as additional information in form of a comma-separated list.
	 *
	 * @param string[] $fieldList The list of fieldnames that should be saved to the configuration file.
	 * @param string $path The path to the configuration file or empty to use .../shopgate_library/config/myconfig.php.
	 * @param bool $validate True to validate the fields that should be set.
	 * @throws ShopgateLibraryException in case the configuration can't be loaded or saved.
	 */
	public function saveFile(array $fieldList, $path = null, $validate = true);
	
	/**
	 * Saves the desired fields to the configuration file for a given language or global configuration
	 *
	 * @param string[] $fieldList the list of fieldnames that should be saved to the configuration file.
	 * @param string $language the ISO-639 code of the language or null to save to global configuration
	 * @param bool $validate true to validate the fields that should be set.
	 *
	 * @throws ShopgateLibraryException in case the configuration can't be loaded or saved.
	 */
	public function saveFileForLanguage(array $fieldList, $language = null, $validate = true);
	
	/**
	 * Checks for duplicate shop numbers in multiple configurations.
	 *
	 * This checks all files in the configuration folder and shop numbers in all
	 * configuration files.
	 *
	 * @param string $shopNumber The shop number to test or null to test all shop numbers found.
	 * @return bool true if there are duplicates, false otherwise.
	 */
	public function checkDuplicates();
	
	/**
	 * Checks if there is more than one configuration file available.
	 *
	 * @return bool true if multiple configuration files are available, false otherwise.
	 */
	public function checkMultipleConfigs();
	
	/**
	 * Checks if there is a configuration for the language requested.
	 *
	 * @param string $language the ISO-639 code of the language or null to load global configuration
	 * @return bool true if global configuration should be used, false if the language has separate configuration
	 */
	public function checkUseGlobalFor($language);
	
	/**
	 * Removes the configuration for the language requested.
	 *
	 * @param string $language the ISO-639 code of the language or null to load global configuration
	 * @throws ShopgateLibraryException in case the file exists but cannot be deleted.
	 */
	public function useGlobalFor($language);
	
	/**
	 * Validates the configuration values.
	 *
	 * If $fieldList contains values, only these values will be validated. If it's empty, all values that have a validation
	 * rule will be validated.
	 *
	 * In case one or more validations fail an exception is thrown. The failed fields are appended as additonal information
	 * in form of a comma-separated list.
	 *
	 * @param string[] $fieldList The list of fields to be validated or empty, to validate all fields.
	 */
	public function validate(array $fieldList = array());

	/**
	 * @return string The name of the plugin / shop system the plugin is for.
	 */
	public function getPluginName();

	/**
	 * @return bool true to activate the Shopgate error handler.
	 */
	public function getUseCustomErrorHandler();

	/**
	 * @return int Shopgate customer number (at least 5 digits)
	 */
	public function getCustomerNumber();

	/**
	 * @return int Shopgate shop number (at least 5 digits)
	 */
	public function getShopNumber();

	/**
	 * @return string API key (exactly 20 hexadecimal digits)
	 */
	public function getApikey();

	/**
	 * @return string Alias of a shop for mobile redirect (start and end with alpha-numerical characters, dashes in between are ok)
	 */
	public function getAlias();

	/**
	 * @return string Custom URL that to redirect to if a mobile device visits a shop (begin with "http://" or "https://" followed by any number of non-whitespace characters)
	 */
	public function getCname();

	/**
	 * @return string The server to use for Shopgate Merchant API communication ("live" or "pg" or "custom")
	 */
	public function getServer();

	/**
	 * @return string If getServer() returns "live", ShopgateConfigInterface::SHOPGATE_API_URL_LIVE is returned.<br />
	 *                 If getServer() returns "pg", ShopgateConfigInterface::SHOPGATE_API_URL_PG is returned.<br />
	 *                 If getServer() returns "custom": A custom API url (empty or a string beginning with "http://" or "https://" followed by any number of non-whitespace characters) is returned.<br />
	 *                 If getServer() returns a different value than the above, ShopgateConfigInterface::SHOPGATE_API_URL_LIVE is returned.
	 */
	public function getApiUrl();

	/**
	 * @return bool true to indicate a shop has been activated by Shopgate
	 */
	public function getShopIsActive();

	/**
	 * @return bool true to always use SSL / HTTPS urls for download of external content (such as graphics for the mobile header button)
	 */
	public function getAlwaysUseSsl();

	/**
	 * @return bool true to enable updates of keywords that identify mobile devices
	 */
	public function getEnableRedirectKeywordUpdate();
	
	/**
	 * @return bool true to enable default redirect for mobile devices from content sites to mobile website (welcome page)
	 */
	public function getEnableDefaultRedirect();
	
	/**
	 * @return string The encoding the shop system is using internally.
	 */
	public function getEncoding();

	/**
	 * @return bool true to enable automatic encoding conversion to utf-8 during export
	 */
	public function getExportConvertEncoding();

	/**
	 * @return bool
	 */
	public function getEnablePing();

	/**
	 * @return bool
	 */
	public function getEnableAddOrder();

	/**
	 * @return bool
	 */
	public function getEnableUpdateOrder();
	
	/**
	 * @return bool
	 */
	public function getEnableCheckCart();

	/**
	 * @return bool
	 */
	public function getEnableRedeemCoupons();
	
	/**
	 * @return bool
	 */
	public function getEnableGetOrders();

	/**
	 * @return bool
	 */
	public function getEnableGetCustomer();

	/**
	 * @return bool
	 */
	public function getEnableGetItemsCsv();

	/**
	 * @return bool
	 */
	public function getEnableGetCategoriesCsv();

	/**
	 * @return bool
	 */
	public function getEnableGetReviewsCsv();

	/**
	 * @return bool
	 */
	public function getEnableGetPagesCsv();

	/**
	 * @return bool
	 */
	public function getEnableGetLogFile();

	/**
	 * @return bool
	 */
	public function getEnableMobileWebsite();
	
	/**
	 * @return bool
	 */
	public function getEnableCron();

	/**
	 * @return bool
	 */
	public function getEnableClearLogFile();
	
	/**
	 * @return bool
	 */
	public function getEnableClearCache();
	
	/**
	 * @return string The ISO 3166 ALPHA-2 code of the country the plugin uses for export.
	 */
	public function getCountry();

	/**
	 * @return string The ISO 3166 ALPHA-2 code of the language the plugin uses for export.
	 */
	public function getLanguage();
	
	/**
	 * @return string The ISO 4217 code of the currency the plugin uses for export.
	 */
	public function getCurrency();
	
	/**
	 * @return string CSS style identifier for the parent element the Mobile Header should be attached to.
	 */
	public function getMobileHeaderParent();
	
	/**
	 * @return bool True to insert the Mobile Header as first child element, false to append it.
	 */
	public function getMobileHeaderPrepend();

	/**
	 * @return int The capacity (number of lines) of the buffer used for the export actions.
	 */
	public function getExportBufferCapacity();

	/**
	 * @return int The maximum number of attributes per product that are created. If the number is exceeded, attributes should be converted to options.
	 */
	public function getMaxAttributes();
	
	/**
	 * @return string The path to the folder where the export CSV files are stored and retrieved from.
	 */
	public function getExportFolderPath();
	
	/**
	 * @return string The path to the folder where the log files are stored and retrieved from.
	 */
	public function getLogFolderPath();
	
	/**
	 * @return string The path to the folder where the cache files are stored and retrieved from.
	 */
	public function getCacheFolderPath();
	
	/**
	 * @return string The name of the items CSV file.
	 */
	public function getItemsCsvFilename();
	
	/**
	 * @return string The name of the categories CSV file.
	 */
	public function getCategoriesCsvFilename();
	
	/**
	 * @return string The name of the reviews CSV file.
	 */
	public function getReviewsCsvFilename();
	
	/**
	 * @return string The name of the pages CSV file.
	 */
	public function getPagesCsvFilename();
	
	/**
	 * @return string The name of the access log file.
	 */
	public function getAccessLogFilename();
	
	/**
	 * @return string The name of the request log file.
	 */
	public function getRequestLogFilename();
	
	/**
	 * @return string The name of the error log file.
	 */
	public function getErrorLogFilename();
	
	/**
	 * @return string The name of the debug log file.
	 */
	public function getDebugLogFilename();
	
	/**
	 * @return string The name of the cache file for mobile device detection keywords.
	 */
	public function getRedirectKeywordCacheFilename();
	
	/**
	 * @return string The name of the cache file for mobile device skip detection keywords.
	 */
	public function getRedirectSkipKeywordCacheFilename();
	
	/**
	 * @return string The path to where the items CSV file is stored and retrieved from.
	 */
	public function getItemsCsvPath();

	/**
	 * @return string The path to where the categories CSV file is stored and retrieved from.
	 */
	public function getCategoriesCsvPath();

	/**
	 * @return string The path to where the reviews CSV file is stored and retrieved from.
	 */
	public function getReviewsCsvPath();

	/**
	 * @return string The path to where the pages CSV file is stored and retrieved from.
	 */
	public function getPagesCsvPath();

	/**
	 * @return string The path to the access log file.
	 */
	public function getAccessLogPath();

	/**
	 * @return string The path to the request log file.
	 */
	public function getRequestLogPath();

	/**
	 * @return string The path to the error log file.
	 */
	public function getErrorLogPath();

	/**
	 * @return string The path to the debug log file.
	 */
	public function getDebugLogPath();

	/**
	 * @return string The path to the cache file for mobile device detection keywords.
	 */
	public function getRedirectKeywordCachePath();

	/**
	 * @return string The path to the cache file for mobile device skip detection keywords.
	 */
	public function getRedirectSkipKeywordCachePath();
	
	/**
	 * @return bool True if the plugin is an adapter between Shopgate's and a third-party-API and servers multiple shops on both ends.
	 */
	public function getIsShopgateAdapter();

	/**
	 * @param string $value The name of the plugin / shop system the plugin is for.
	 */
	public function setPluginName($value);

	/**
	 * @param bool $value true to activate the Shopgate error handler.
	 */
	public function setUseCustomErrorHandler($value);

	/**
	 * @param int $value Shopgate customer number (at least 5 digits)
	 */
	public function setCustomerNumber($value);

	/**
	 * @param int $value Shopgate shop number (at least 5 digits)
	 */
	public function setShopNumber($value);

	/**
	 * @param string $value API key (exactly 20 hexadecimal digits)
	 */
	public function setApikey($value);

	/**
	 * @param string $value Alias of a shop for mobile redirect (start and end with alpha-numerical characters, dashes in between are ok)
	 */
	public function setAlias($value);

	/**
	 * @param string $value Custom URL that to redirect to if a mobile device visits a shop (begin with "http://" or "https://" followed by any number of non-whitespace characters)
	 */
	public function setCname($value);

	/**
	 * @param string $value The server to use for Shopgate Merchant API communication ("live" or "pg" or "custom")
	 */
	public function setServer($value);

	/**
	 * @param string $value If $server is set to custom, Shopgate Merchant API calls will be made to this URL (empty or a string beginning with "http://" or "https://" followed by any number of non-whitespace characters)
	 */
	public function setApiUrl($value);

	/**
	 * @param bool $value true to indicate a shop has been activated by Shopgate
	 */
	public function setShopIsActive($value);

	/**
	 * @param bool $value true to always use SSL / HTTPS urls for download of external content (such as graphics for the mobile header button)
	 */
	public function setAlwaysUseSsl($value);

	/**
	 * @param bool $value true to enable updates of keywords that identify mobile devices
	 */
	public function setEnableRedirectKeywordUpdate($value);
	
	/**
	 * @param bool true to enable default redirect for mobile devices from content sites to mobile website (welcome page)
	 */
	public function setEnableDefaultRedirect($value);
	
	/**
	 * @param string $value The encoding the shop system is using internally.
	 */
	public function setEncoding($value);
	
	/**
	 * @param bool $value true to enable automatic encoding conversion to utf-8 during export
	 */
	public function setExportConvertEncoding($value);

	/**
	 * @param bool $value
	 */
	public function setEnablePing($value);

	/**
	 * @param bool $value
	 */
	public function setEnableAddOrder($value);

	/**
	 * @param bool $value
	 */
	public function setEnableUpdateOrder($value);
	
	/**
	 * @param bool $value
	 */
	public function setEnableCheckCart($value);

	/**
	 * @param bool $value
	 */
	public function setEnableRedeemCoupons($value);
	
	/**
	 * @param bool $value
	 */
	public function setEnableGetOrders($value);

	/**
	 * @param bool $value
	 */
	public function setEnableGetCustomer($value);

	/**
	 * @param bool $value
	 */
	public function setEnableGetItemsCsv($value);

	/**
	 * @param bool $value
	 */
	public function setEnableGetCategoriesCsv($value);

	/**
	 * @param bool $value
	 */
	public function setEnableGetReviewsCsv($value);

	/**
	 * @param bool $value
	 */
	public function setEnableGetPagesCsv($value);

	/**
	 * @param bool $value
	 */
	public function setEnableGetLogFile($value);

	/**
	 * @param bool $value
	 */
	public function setEnableMobileWebsite($value);
	
	/**
	 * @param bool $value
	 */
	public function setEnableCron($value);
	
	/**
	 * @param bool $value
	 */
	public function setEnableClearLogFile($value);
	
	/**
	 * @param bool $value
	 */
	public function setEnableClearCache($value);
	
	/**
	 * @param string The ISO 3166 ALPHA-2 code of the country the plugin uses for export.
	 */
	public function setCountry($value);
	
	/**
	 * @param string $value The ISO 3166 ALPHA-2 code of the language the plugin uses for export.
	 */
	public function setLanguage($value);
	
	/**
	 * @param string $value The ISO 4217 code of the currency the plugin uses for export.
	 */
	public function setCurrency($value);
	
	/**
	 * @param string $value CSS style identifier for the parent element the Mobile Header should be attached to.
	 */
	public function setMobileHeaderParent($value);
	
	/**
	 * @return bool $value True to insert the Mobile Header as first child element, false to append it.
	 */
	public function setMobileHeaderPrepend($value);
	
	/**
	 * @param int $value The capacity (number of lines) of the buffer used for the export actions.
	 */
	public function setExportBufferCapacity($value);
	
	/**
	 * @param int $value The maximum number of attributes per product that are created. If the number is exceeded, attributes should be converted to options.
	 */
	public function setMaxAttributes($value);
	
	/**
	 * @param string $value The path to the folder where the export CSV files are stored and retrieved from.
	 */
	public function setExportFolderPath($value);
	
	/**
	 * @param string $value The path to the folder where the log files are stored and retrieved from.
	 */
	public function setLogFolderPath($value);
	
	/**
	 * @param string $value The path to the folder where the cache files are stored and retrieved from.
	 */
	public function setCacheFolderPath($value);
	
	/**
	 * @param string $value The name of the items CSV file.
	 */
	public function setItemsCsvFilename($value);
	
	/**
	 * @param string $value The name of the categories CSV file.
	 */
	public function setCategoriesCsvFilename($value);
	
	/**
	 * @param string $value The name of the reviews CSV file.
	 */
	public function setReviewsCsvFilename($value);
	
	/**
	 * @param string $value The name of the pages CSV file.
	 */
	public function setPagesCsvFilename($value);
	
	/**
	 * @param string $value The name of the access log file.
	 */
	public function setAccessLogFilename($value);
	
	/**
	 * @param string $value The name of the request log file.
	 */
	public function setRequestLogFilename($value);
	
	/**
	 * @param string $value The name of the error log file.
	 */
	public function setErrorLogFilename($value);
	
	/**
	 * @param string $value The name of the debug log file.
	 */
	public function setDebugLogFilename($value);
	
	/**
	 * @param string $value The name of the cache file for mobile device detection keywords.
	 */
	public function setRedirectKeywordCacheFilename($value);
	
	/**
	 * @param string $value The name of the cache file for mobile device skip detection keywords.
	 */
	public function setRedirectSkipKeywordCacheFilename($value);
	
	/**
	 * @param string $value The path to where the items CSV file is stored and retrieved from.
	 */
	public function setItemsCsvPath($value);
	
	/**
	 * @param string $value The path to where the categories CSV file is stored and retrieved from.
	 */
	public function setCategoriesCsvPath($value);
	
	/**
	 * @param string $value The path to where the reviews CSV file is stored and retrieved from.
	 */
	public function setReviewsCsvPath($value);
	
	/**
	 * @param string $value The path to where the pages CSV file is stored and retrieved from.
	 */
	public function setPagesCsvPath($value);
	
	/**
	 * @param string $value The path to the access log file.
	 */
	public function setAccessLogPath($value);
	
	/**
	 * @param string $value The path to the request log file.
	 */
	public function setRequestLogPath($value);
	
	/**
	 * @param string $value The path to the error log file.
	 */
	public function setErrorLogPath($value);
	
	/**
	 * @param string $value The path to the debug log file.
	 */
	public function setDebugLogPath($value);
	
	/**
	 * @param string $value The path to the cache file for mobile device detection keywords.
	 */
	public function setRedirectKeywordCachePath($value);
	
	/**
	 * @param string $value The path to the cache file for mobile device skip detection keywords.
	 */
	public function setRedirectSkipKeywordCachePath($value);
	
	/**
	 *  @param bool $value True if the plugin is an adapter between Shopgate's and a third-party-API and servers multiple shops on both ends.
	 */
	public function setIsShopgateAdapter($value);
	
	/**
	 * Returns an additional setting.
	 *
	 * @param string $setting The name of the setting.
	 */
	public function returnAdditionalSetting($setting);
	
	/**
	 * Returns the additional settings array.
	 *
	 * The naming of this method doesn't follow the getter/setter naming convention because $this->additionalSettings
	 * is not a regular property.
	 *
	 * @return array<string, mixed> The additional settings a plugin may have defined.
	 */
	public function returnAdditionalSettings();
	
	/**
	 * Returns the configuration as an array.
	 *
	 * All properties are included as well as the additional settings. Additional settings must be represented as if
	 * they were properties, e.g. the additional settings array looking like this
	 *
	 * array('setting1' => 'value1', 'setting2' => 'value2')
	 *
	 * appears in the returned array like this:
	 *
	 * array('plugin_name' => 'abc', 'use_custom_error_handler' => 0, ......., 'setting1' => 'value1', 'setting2' => 'value2').
	 *
	 * Properties overwrite additional settings.
	 *
	 * @return array<string, mixed> The configuration as an array of key-value-pairs.
	 */
	public function toArray();
	
	/**
	 * Creates an array of all properties that have getters.
	 *
	 * @return mixed[]
	 */
	public function buildProperties();
}