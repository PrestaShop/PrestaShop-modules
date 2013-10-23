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

class ShopgatePluginApi extends ShopgateObject implements ShopgatePluginApiInterface {
	/**
	 * @var ShopgatePlugin
	 */
	protected $plugin;

	/**
	 * @var ShopgateConfigInterface
	 */
	protected $config;

	/**
	 * @var ShopgateMerchantApiInterface
	 */
	protected $merchantApi;

	/**
	 * @var ShopgateAuthentificationServiceInterface
	 */
	protected $authService;

	/**
	 * Parameters passed along the action (usually per POST)
	 *
	 * @var mixed[]
	 */
	protected $params;

	/**
	 * @var string[]
	 */
	protected $actionWhitelist;
	
	/**
	 * @var mixed
	 */
	protected $responseData;

	/**
	 * @var ShopgatePluginApiResponse
	 */
	protected $response;
	
	/**
	 * @var string The trace ID of the incoming request.
	 */
	protected $trace_id;
	
	public function __construct(
			ShopgateConfigInterface $config,
			ShopgateAuthentificationServiceInterface $authService,
			ShopgateMerchantApiInterface $merchantApi,
			ShopgatePlugin $plugin,
			ShopgatePluginApiResponse $response = null
	) {
		$this->config = $config;
		$this->authService = $authService;
		$this->merchantApi = $merchantApi;
		$this->plugin = $plugin;
		$this->response = $response;
		$this->responseData = array();
		
		// initialize action whitelist
		$this->actionWhitelist = array(
				'ping',
				'cron',
				'add_order',
				'update_order',
				'get_customer',
				'get_items_csv',
				'get_categories_csv',
				'get_reviews_csv',
				'get_pages_csv',
				'get_log_file',
				'clear_log_file',
				'clear_cache',
				'check_cart',
				'redeem_coupons',
				'get_settings',
		);
	}

	public function handleRequest(array $data = array()) {
		// log incoming request
		$this->log(ShopgateLogger::getInstance()->cleanParamsForLog($data), ShopgateLogger::LOGTYPE_ACCESS);

		// save the params
		$this->params = $data;
		
		// save trace_id
		if (isset($this->params['trace_id'])) {
			$this->trace_id = $this->params['trace_id'];
		}
		
		try {
			$this->authService->checkAuthentification();

			// set error handler to Shopgate's handler if requested
			if (!empty($this->params['use_errorhandler'])) {
				set_error_handler('ShopgateErrorHandler');
			}
			
			// check if the request is for the correct shop number or an adapter-plugin
			if (
					!$this->config->getIsShopgateAdapter() &&
					!empty($this->params['shop_number']) &&
					($this->params['shop_number'] != $this->config->getShopNumber())
			) {
				throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_UNKNOWN_SHOP_NUMBER, "{$this->params['shop_number']}");
			}

			// check if an action to call has been passed, is known and enabled
			if (empty($this->params['action'])) {
				throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_ACTION, 'Passed parameters: '.var_export($data, true));
			}

			// check if the action is white-listed
			if (!in_array($this->params['action'], $this->actionWhitelist)) {
				throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_UNKNOWN_ACTION, "{$this->params['action']}");
			}

			// check if action is enabled in the config
			$configArray = $this->config->toArray();
			if (empty($configArray['enable_'.$this->params['action']])) {
				throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_DISABLED_ACTION, "{$this->params['action']}");
			}
			
			// enable debugging if requested
			if (!empty($data['debug_log'])) {
				ShopgateLogger::getInstance()->enableDebug();
				ShopgateLogger::getInstance()->keepDebugLog(!empty($data['keep_debug_log']));
			}
			// enable error handler if requested
			if (!empty($data['error_reporting'])) {
				error_reporting($data['error_reporting']);
			}
			
			// call the action
			$action = $this->camelize($this->params['action']);
			$this->{$action}();
		} catch (ShopgateLibraryException $e) {
			$error = $e->getCode();
			$errortext = $e->getMessage();
		} catch (ShopgateMerchantApiException $e) {
			$error = ShopgateLibraryException::MERCHANT_API_ERROR_RECEIVED;
			$errortext = ShopgateLibraryException::getMessageFor(ShopgateLibraryException::MERCHANT_API_ERROR_RECEIVED).': "'.$e->getCode() . ' - ' . $e->getMessage().'"';
		} catch (Exception $e) {
			$message  = "\n".get_class($e)."\n";
			$message .= 'with code:   '.$e->getCode()."\n";
			$message .= 'and message: \''.$e->getMessage()."'\n";

			// new ShopgateLibraryException to build proper error message and perform logging
			$se = new ShopgateLibraryException($message);
			$error = $se->getCode();
			$errortext = $se->getMessage();
		}

		// print out the response
		if (!empty($error)) {
			if (empty($this->response)) $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
			$this->response->markError($error, $errortext);
		}
		
		if (empty($this->response)) {
			trigger_error('No response object defined. This should _never_ happen.', E_USER_ERROR);
		}
		
		$this->response->setData($this->responseData);
		$this->response->send();
		
		// return true or false
		return (empty($error));
	}


	######################################################################
	## Following methods represent the Shopgate Plugin API's actions:   ##
	######################################################################

	/**
	 * Represents the "ping" action.
	 *
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_ping/
	 */
	protected function ping() {
		// obfuscate data relevant for authentication
		$config = $this->config->toArray();
		$config['customer_number']	= ShopgateLogger::OBFUSCATION_STRING;
		$config['shop_number']		= ShopgateLogger::OBFUSCATION_STRING;
		$config['apikey']			= ShopgateLogger::OBFUSCATION_STRING;

		// prepare response data array
		$this->responseData['pong'] = 'OK';
		$this->responseData['configuration'] = $config;
		$this->responseData['plugin_info'] = $this->plugin->createPluginInfo();
		$this->responseData['permissions'] = $this->getPermissions();
		$this->responseData['php_version'] = phpversion();
		$this->responseData['php_config'] = $this->getPhpSettings();
		$this->responseData['php_curl'] = function_exists('curl_version') ? curl_version() : 'No PHP-CURL installed';
		$this->responseData['php_extensions'] = get_loaded_extensions();
		$this->responseData['shopgate_library_version'] = SHOPGATE_LIBRARY_VERSION;
		$this->responseData['plugin_version'] = defined('SHOPGATE_PLUGIN_VERSION') ? SHOPGATE_PLUGIN_VERSION : 'UNKNOWN';
		
		// set data and return response
		if (empty($this->response)) $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
	}

	/**
	 * Represents the "add_order" action.
	 *
	 * @throws ShopgateLibraryException
	 */
	protected function cron() {
		if (empty($this->params['jobs']) || !is_array($this->params['jobs'])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_CRON_NO_JOBS);
		}

		// time tracking
		$starttime = microtime(true);

		// references
		$message = '';
		$errorcount = 0;

		// execute the jobs
		foreach ($this->params['jobs'] as $job) {
			if (empty($job['job_name'])) {
				throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_CRON_NO_JOB_NAME);
			}

			if (empty($job['job_params'])) {
				$job['job_params'] = array();
			}

			try {
				$jobErrorcount = 0;

				// job execution
				$this->plugin->cron($job['job_name'], $job['job_params'], $message, $jobErrorcount);

				// check error count
				if ($jobErrorcount > 0) {
					$message .= 'Errors happend in job: "'.$job['job_name'].'" ('.$jobErrorcount.' errors)';
					$errorcount += $jobErrorcount;
				}
			} catch (Exception $e) {
				$errorcount++;
				$message .= 'Job aborted: "'.$e->getMessage().'"';
			}
		}

		// time tracking
		$endtime = microtime(true);
		$runtime = $endtime - $starttime;
		$runtime = round($runtime, 4);

		// prepare response
		$responses = array();
		$responses['message'] = $message;
		$responses['execution_error_count'] = $errorcount;
		$responses['execution_time'] = $runtime;

		if (empty($this->response)) $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
		$this->responseData = $responses;
	}

	/**
	 * Represents the "add_order" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_add_order/
	 */
	protected function addOrder() {
		if (!isset($this->params['order_number'])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_ORDER_NUMBER);
		}

		$orders = $this->merchantApi->getOrders(array('order_numbers[0]'=>$this->params['order_number'], 'with_items' => 1))->getData();
		if (empty($orders)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE, '"orders" not set. Response: '.var_export($orders, true));
		}
		if (count($orders) > 1) {
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE, 'more than one order in response. Response: '.var_export($orders, true));
		}

		if (empty($this->response)) $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
		
		$orderData = $this->plugin->addOrder($orders[0]);
		if (is_array($orderData)) {
			$this->responseData = $orderData;
		} else {
			$this->responseData['external_order_id'] = $orderData;
			$this->responseData['external_order_number'] = null;
		}
	}

	/**
	 * Represents the "update_order" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_update_order/
	 */
	protected function updateOrder() {
		if (!isset($this->params['order_number'])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_ORDER_NUMBER);
		}

		$orders = $this->merchantApi->getOrders(array('order_numbers[0]'=>$this->params['order_number'], 'with_items' => 1))->getData();

		if (empty($orders)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE, '"order" not set. Response: '.var_export($orders, true));
		}

		if (count($orders) > 1) {
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE, 'more than one order in response. Response: '.var_export($orders, true));
		}
		
		$payment = 0;
		$shipping = 0;

		if (isset($this->params['payment'])) {
			$payment = (bool) $this->params['payment'];
		}
		if (isset($this->params['shipping'])) {
			$shipping = (bool) $this->params['shipping'];
		}

		$orders[0]->setUpdatePayment($payment);
		$orders[0]->setUpdateShipping($shipping);

		if (empty($this->response)) $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
		
		$orderData = $this->plugin->updateOrder($orders[0]);
		if (is_array($orderData)) {
			$this->responseData = $orderData;
		} else {
			$this->responseData['external_order_id'] = $orderData;
			$this->responseData['external_order_number'] = null;
		}
	}
	
	/**
	 * Represents the "redeem_coupons" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_redeem_coupons
	 */
	protected function redeemCoupons() {
		if (!isset($this->params['cart'])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_CART);
		}
		
		if (empty($this->response)) $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
		
		$cart = new ShopgateCart($this->params['cart']);
		$couponData = $this->plugin->redeemCoupons($cart);
		
		if(!is_array($couponData)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT, 'Plugin Response: '.var_export($couponData, true));
		}
		
		// Workaround:
		// $couponData was specified to be a ShopgateExternalCoupon[].
		// Now supports the same format as checkCart(), i.e. array('external_coupons' => ShopgateExternalCoupon[]).
		if (!empty($couponData['external_coupons']) && is_array($couponData['external_coupons'])) {
			$couponData = $couponData['external_coupons'];
		}
		
		$responseData = array("external_coupons" => array());
		foreach($couponData as $coupon) {
			if (!is_object($coupon) || !($coupon instanceof ShopgateExternalCoupon)) {
				throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT, 'Plugin Response: '.var_export($coupon, true));
			}
			
			$coupon = $coupon->toArray();
			unset($coupon["order_index"]);
			
			$responseData["external_coupons"][] = $coupon;
		}
		
		$this->responseData = $responseData;
	}
	
	/**
	 * Represents the "check_cart" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_check_cart
	 */
	protected function checkCart() {
		if (!isset($this->params['cart'])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_CART);
		}

		if (empty($this->response)) $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);

		$cart = new ShopgateCart($this->params['cart']);
		$cartData = $this->plugin->checkCart($cart);
		
		$responseData = array(
// 				"items" => array(),
				"external_coupons" => array(),
// 				"shippings" => array(),
		);
		
		if(!is_array($cartData)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT, 'Plugin Response: '.var_export($cartData, true));
		}
		
		$coupons = array();
		foreach($cartData["external_coupons"] as $coupon) {
			if (!is_object($coupon) || !($coupon instanceof ShopgateExternalCoupon)) {
				throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT, 'Plugin Response: '.var_export($coupon, true));
			}
			
			$coupon = $coupon->toArray();
			unset($coupon["order_index"]);
				
			$coupons[] = $coupon;
		}
		$responseData["external_coupons"] = $coupons;
		
		$this->responseData = $responseData;
	}

	/**
	 * Represents the "get_settings" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_check_cart
	 */
	protected function getSettings() {
		$this->responseData = $this->plugin->getSettings();
		
		// set data and return response
		if (empty($this->response)) $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
	}
	
	/**
	 * Represents the "get_customer" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_customer/
	 */
	protected function getCustomer() {
		if (!isset($this->params['user'])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_USER);
		}

		if (!isset($this->params['pass'])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_PASS);
		}

		$customer = $this->plugin->getCustomer($this->params['user'], $this->params['pass']);
		if (!is_object($customer) || !($customer instanceof ShopgateCustomer)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT, 'Plugin Response: '.var_export($customer, true));
		}

		$customerData = $customer->toArray();
		$addressList = $customerData['addresses'];
		unset($customerData['addresses']);

		if (empty($this->response)) $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
		$this->responseData["user_data"] = $customerData;
		$this->responseData["addresses"] = $addressList;
	}

	/**
	 * Represents the "get_items_csv" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_items_csv/
	 */
	protected function getItemsCsv() {
		if (isset($this->params['limit']) && isset($this->params['offset'])) {
			$this->plugin->setExportLimit((int) $this->params['limit']);
			$this->plugin->setExportOffset((int) $this->params['offset']);
			$this->plugin->setSplittedExport(true);
		}

		// generate / update items csv file if requested
		$this->plugin->startGetItemsCsv();

		if (empty($this->response)) $this->response = new ShopgatePluginApiResponseTextCsv($this->trace_id);
		$this->responseData = $this->config->getItemsCsvPath();
	}

	/**
	 * Represents the "get_categories_csv" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_categories_csv/
	 */
	protected function getCategoriesCsv() {
		// generate / update categories csv file
		$this->plugin->startGetCategoriesCsv();

		
		if (empty($this->response)) $this->response = new ShopgatePluginApiResponseTextCsv($this->trace_id);
		$this->responseData = $this->config->getCategoriesCsvPath();
	}

	/**
	 * Represents the "get_reviews_csv" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_reviews_csv/
	 */
	protected function getReviewsCsv() {
		if (isset($this->params['limit']) && isset($this->params['offset'])) {
			$this->plugin->setExportLimit((int) $this->params['limit']);
			$this->plugin->setExportOffset((int) $this->params['offset']);
			$this->plugin->setSplittedExport(true);
		}
		
		// generate / update reviews csv file
		$this->plugin->startGetReviewsCsv();

		if (empty($this->response)) $this->response = new ShopgatePluginApiResponseTextCsv($this->trace_id);
		$this->responseData = $this->config->getReviewsCsvPath();
	}

	/**
	 * Represents the "get_pages_csv" action.
	 *
	 * @todo
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_pages_csv/
	 */
	protected function getPagesCsv() {
		if (empty($this->response)) $this->response = new ShopgatePluginApiResponseTextCsv($this->trace_id);
		$this->responseData = $this->config->getPagesCsvPath();
	}
	
	/**
	 * Represents the "get_log_file" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_log_file/
	 */
	protected function getLogFile() {
		// disable debug log for this action
		$logger = ShopgateLogger::getInstance();
		$logger->disableDebug();
		$logger->keepDebugLog(true);
		
		$type = (empty($this->params['log_type'])) ? ShopgateLogger::LOGTYPE_ERROR : $this->params['log_type'];
		$lines = (!isset($this->params['lines'])) ? null : $this->params['lines'];

		$log = $logger->tail($type, $lines);

		// return the requested log file content and end the script
		if (empty($this->response)) $this->response = new ShopgatePluginApiResponseTextPlain($this->trace_id);
		$this->responseData = $log;
	}

	/**
	 * Represents the "clear_log_file" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_clear_log_file/
	 */
	private function clearLogFile() {
		if (empty($this->params['log_type'])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_UNKNOWN_LOGTYPE);
		}
		
		switch ($this->params['log_type']) {
			case ShopgateLogger::LOGTYPE_ACCESS:
				$logFilePath = $this->config->getAccessLogPath();
			break;
			case ShopgateLogger::LOGTYPE_REQUEST:
				$logFilePath = $this->config->getRequestLogPath();
			break;
			case ShopgateLogger::LOGTYPE_ERROR:
				$logFilePath = $this->config->getErrorLogPath();
			break;
			case ShopgateLogger::LOGTYPE_DEBUG:
				$logFilePath = $this->config->getDebugLogPath();
			break;
			default:
				throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_UNKNOWN_LOGTYPE);
		}
		
		$logFilePointer = @fopen($logFilePath, 'w');
		if ($logFilePointer === false) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_FILE_OPEN_ERROR, "File: $logFilePath", true);
		}
		fclose($logFilePointer);
		
		// return the path of the deleted log file
		if (empty($this->response)) $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
	}
	
	/**
	 * Represents the "clear_cache" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_clear_cache/
	 */
	private function clearCache() {
	
		$files = array();
		$files[] = $this->config->getRedirectKeywordCachePath();
		$files[] = $this->config->getRedirectSkipKeywordCachePath();
	
		$errorFiles = array();
		foreach($files as $file){
			if(@file_exists($file) && is_file($file)){
				if(!@unlink($file)){
					$errorFiles[] = $file;
				}
			}
		}
	
		if (!empty($errorFiles)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_FILE_DELETE_ERROR, "Cannot delete files (".implode(', ', $errorFiles).")", true);
		}
	
		if (empty($this->response)) $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
	}

	/**
	 * Represents the "get_orders" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_orders/
	 * @todo
	 */
	protected function getOrders() {
		/**** not yet implemented ****/

		//if (!empty($this->params['external_customer_number'])) {
	}

	
	###############
	### Helpers ###
	###############
	
	private function getPhpSettings() {
		$settingDetails = array();

		$allSettings = function_exists('ini_get_all') ? ini_get_all() : array();

		$settings = array(
				'max_execution_time',
				'memory_limit',
				'allow_call_time_pass_reference',
				'disable_functions',
				'display_errors',
				'file_uploads',
				'include_path',
				'register_globals',
				'safe_mode'
		);

		foreach($settings as $setting) {
				$settingDetails[$setting] = (!empty($allSettings[$setting]))
					? $allSettings[$setting]
					: 'undefined'
				;
		}

		return $settingDetails;
	}

	private function getPermissions() {
		$permissions = array();
		$files = array(
				# default paths
				SHOPGATE_BASE_DIR.'/config/myconfig.php',
				$this->config->getExportFolderPath(),
				$this->config->getLogFolderPath(),
				$this->config->getCacheFolderPath(),
				
				# csv files
				$this->config->getItemsCsvPath(),
				$this->config->getCategoriesCsvPath(),
				$this->config->getReviewsCsvPath(),
				
				# log files
				$this->config->getAccessLogPath(),
				$this->config->getRequestLogPath(),
				$this->config->getErrorLogPath(),
				$this->config->getDebugLogPath(),
				
				# cache files
				$this->config->getRedirectKeywordCachePath(),
				$this->config->getRedirectSkipKeywordCachePath(),
		);

		foreach ($files as $file) {
			$permissions[] = $this->_getFileMeta($file, 1);
		}

		return $permissions;
	}

	/**
	 * get meta data for given file.
	 * if file doesn't exists, move up to parent directory
	 *
	 * @param string $file (max numbers of parent directory lookups)
	 * @param number $parentLevel
	 * @return array with file meta data
	 */
	private function _getFileMeta($file, $parentLevel = 0) {
		$meta = array('file' => $file);

		if ($meta['exist'] = (bool) file_exists($file)) {
			$meta['writeable'] = (bool) is_writable($file);

			$uid = fileowner($file);
			if (function_exists('posix_getpwuid')) {
				$uinfo = posix_getpwuid($uid);
				$uid = $uinfo['name'];
			}

			$gid = filegroup($file);
			if (function_exists('posix_getgrgid')) {
				$ginfo = posix_getgrgid($gid);
				$gid = $ginfo['name'];
			}

			$meta['owner'] = $uid;
			$meta['group'] = $gid;
			$meta['permission'] = substr(sprintf('%o', fileperms($file)), -4);
			$meta['last_modification_time'] = date('d.m.Y H:i:s', filemtime($file));

			if (is_file($file)) {
				$meta['filesize'] = round(filesize($file)/(1024*1024), 4) .' MB';
			}
		}
		else if ($parentLevel > 0) {
			$fInfo = pathinfo($file);
			if (file_exists($fInfo['dirname'])) {
				$meta['parent_dir'] = $this->_getFileMeta($fInfo['dirname'], --$parentLevel);
			}
		}

		return $meta;
	}

}

class ShopgateMerchantApi extends ShopgateObject implements ShopgateMerchantApiInterface {
	/**
	 * @var ShopgateAuthentificationServiceInterface
	 */
	private $authService;
	
	/**
	 * @var string
	 */
	private $shopNumber;
	
	/**
	 * @var string
	 */
	private $apiUrl;
	
	public function __construct(ShopgateAuthentificationServiceInterface $authService, $shopNumber, $apiUrl) {
		$this->authService = $authService;
		$this->shopNumber = $shopNumber;
		$this->apiUrl = $apiUrl;
	}
	
	/**
	 * Returns an array of curl-options for requests
	 *
	 * @param mixed[] $override cURL options to override for this request.
	 * @return mixed[] The default cURL options for a Shopgate Merchant API request merged with the options in $override.
	 */
	protected function getCurlOptArray($override = array()) {
		$opt = array();
		
		$opt[CURLOPT_HEADER] = false;
		$opt[CURLOPT_USERAGENT] = 'ShopgatePlugin/'.(defined('SHOPGATE_PLUGIN_VERSION') ? SHOPGATE_PLUGIN_VERSION : 'called outside plugin');
		$opt[CURLOPT_SSL_VERIFYPEER] = false;
		$opt[CURLOPT_RETURNTRANSFER] = true;
		$opt[CURLOPT_HTTPHEADER] = array(
				'X-Shopgate-Library-Version: '. SHOPGATE_LIBRARY_VERSION,
				'X-Shopgate-Plugin-Version: '.(defined('SHOPGATE_PLUGIN_VERSION') ? SHOPGATE_PLUGIN_VERSION : 'called outside plugin'),
				$this->authService->buildAuthUserHeader(),
				$this->authService->buildMerchantApiAuthTokenHeader()
		);
		
		$opt[CURLOPT_TIMEOUT] = 30; // Default timeout 30sec
		$opt[CURLOPT_POST] = true;
		
		return ($override + $opt);
	}
	
	/**
	 * Prepares the request and sends it to the configured Shopgate Merchant API.
	 *
	 * @param mixed[] $parameters The parameters to send.
	 * @param mixed[] $curlOptOverride cURL options to override for this request.
	 * @return ShopgateMerchantApiResponse The response object.
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 */
	protected function sendRequest($parameters, $curlOptOverride = array()) {
		$parameters['shop_number'] = $this->shopNumber;
		$parameters['trace_id'] = 'spa-'.uniqid();
		
		$this->log('Sending request to "'.$this->apiUrl.'": '.ShopgateLogger::getInstance()->cleanParamsForLog($parameters), ShopgateLogger::LOGTYPE_REQUEST);
		
		// init new auth session and generate cURL options
		$this->authService->startNewSession();
		$curlOpt = $this->getCurlOptArray($curlOptOverride);
		
		// init cURL connection and send the request
		$curl = curl_init($this->apiUrl);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($parameters));
		curl_setopt_array($curl, $curlOpt);
		$response = curl_exec($curl);
		$info = curl_getinfo($curl);
		curl_close($curl);
		
		// check the result
		if (!$response) {
			// exception without logging - this might cause spamming your logs and we will know when our API is offline anyways
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_NO_CONNECTION, null, false, false);
		}
		
		$decodedResponse = $this->jsonDecode($response, true);
		
		if (empty($decodedResponse)) {
			// exception without logging - this might cause spamming your logs and we will know when our API is offline anyways
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE, 'Response: '.$response, true, false);
		}
		
		$responseObject = new ShopgateMerchantApiResponse($decodedResponse);

		if ($decodedResponse['error'] != 0) {
			throw new ShopgateMerchantApiException($decodedResponse['error'], $decodedResponse['error_text'], $responseObject);
		}
		
		return $responseObject;
	}
	
	
	######################################################################
	## Following methods represent the Shopgate Merchant API's actions: ##
	######################################################################
	
	######################################################################
	## Orders                                                           ##
	######################################################################
	public function getOrders($parameters) {
		$request = array(
				'action' => 'get_orders',
		);
		
		$request = array_merge($request, $parameters);
		$response = $this->sendRequest($request);
		
		// check and reorganize the data of the SMA response
		$data = $response->getData();
		if (empty($data['orders']) || !is_array($data['orders'])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE, '"orders" is not set or not an array. Response: '.var_export($data, true));
		}
		
		$orders = array();
		foreach ($data['orders'] as $order) {
			$orders[] = new ShopgateOrder($order);
		}
		
		// put the reorganized data into the response object and return ist
		$response->setData($orders);
		return $response;
	}
	
	public function addOrderDeliveryNote($orderNumber, $shippingServiceId, $trackingNumber, $markAsCompleted = false, $sendCustomerEmail = false) {
		$request = array(
			'action' => 'add_order_delivery_note',
			'order_number' => $orderNumber,
			'shipping_service_id' => $shippingServiceId,
			'tracking_number' => (string) $trackingNumber,
			'mark_as_completed' => $markAsCompleted,
			'send_customer_email' => $sendCustomerEmail,
		);
		
		return $this->sendRequest($request);
	}
	
	public function setOrderShippingCompleted($orderNumber, $sendCustomerEmail = false) {
		$request = array(
			'action' => 'set_order_shipping_completed',
			'order_number' => $orderNumber,
			'send_customer_email' => $sendCustomerEmail,
		);
		
		return $this->sendRequest($request);
	}
	
	public function cancelOrder($orderNumber, $cancelCompleteOrder = false, $cancellationItems = array(), $cancelShipping = false, $cancellationNote = '') {
		$request = array(
			'action' => 'cancel_order',
			'order_number' => $orderNumber,
			'cancel_complete_order' => $cancelCompleteOrder,
			'cancellation_items' => $cancellationItems,
			'cancel_shipping' => $cancelShipping,
			'cancellation_note' => $cancellationNote,
		);
		
		return $this->sendRequest($request);
	}
	
	######################################################################
	## Mobile Redirect                                                  ##
	######################################################################
	/*
	 * This method is deprecated, please use getMobileRedirectUserAgents().
	 * @deprecated
	 */
	public function getMobileRedirectKeywords() {
		$request = array(
				'action' => 'get_mobile_redirect_keywords',
		);
		
		$response = $this->sendRequest($request, array(CURLOPT_TIMEOUT => 1));
		return $response->getData();
	}
	
	public function getMobileRedirectUserAgents() {
		$request = array(
				'action' => 'get_mobile_redirect_user_agents',
		);
		
		$response = $this->sendRequest($request, array(CURLOPT_TIMEOUT => 1));
		
		$responseData = $response->getData();
		if(!isset($responseData["keywords"]) || !isset($responseData["skip_keywords"])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE, "\"keyword\" or \"skip_keyword\" is not set. Response: " . var_export($responseData, true));
		}
		
		return $response->getData();
	}
	
	######################################################################
	## Items                                                            ##
	######################################################################
	public function getItems($parameters) {
		$parameters['action'] = 'get_items';
		
		$response = $this->sendRequest($parameters);
		
		// check and reorganize the data of the SMA response
		$data = $response->getData();
		if (empty($data['items']) || !is_array($data['items'])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE, '"items" is not set or not an array. Response: '.var_export($data, true));
		}
		
		$items = array();
		foreach ($data['items'] as $item) {
			$items[] = new ShopgateItem($item);
		}
		
		// put the reorganized data into the response object and return ist
		$response->setData($items);
		return $response;
	}
	
	public function addItem($item) {
		$request = ($item instanceof ShopgateItem)
			? $item->toArray()
			: $item;
		
		$request['action'] = 'add_item';
		
		return $this->sendRequest($request);
	}
	
	public function updateItem($item) {
		$request = ($item instanceof ShopgateItem)
			? $item->toArray()
			: $item;
		
		$request['action'] = 'update_item';
		
		return $this->sendRequest($request);
	}
	
	public function deleteItem($itemNumber) {
		$request = array(
				'action' => 'delete_item',
				'item_number' => $itemNumber,
		);
		
		return $this->sendRequest($request);
	}
	
	public function batchAddItems($items) {
		$request = array(
				'items' => array(),
				'action' => 'batch_add_items',
		);
		
		foreach ($items as $item) {
			$request['items'][] = ($item instanceof ShopgateItem)
				? $item->toArray()
				: $item;
		}
		
		return $this->sendRequest($request);
	}
	
	public function batchUpdateItems($items) {
		$request = array(
				'items' => array(),
				'action' => 'batch_update_items',
		);
		
		foreach ($items as $item) {
			$request['items'][] = ($item instanceof ShopgateItem)
				? $item->toArray()
				: $item;
		}
		
		return $this->sendRequest($request);
	}
	
	######################################################################
	## Categories                                                       ##
	######################################################################
	public function getCategories($parameters) {
		$parameters['action'] = 'get_categories';
		
		$response = $this->sendRequest($parameters);
		
		// check and reorganize the data of the SMA response
		$data = $response->getData();
		if (empty($data['categories']) || !is_array($data['categories'])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE, '"categories" is not set or not an array. Response: '.var_export($data, true));
		}
		
		$categories = array();
		foreach ($data['categories'] as $category) {
			$categories[] = new ShopgateCategory($category);
		}
		
		// put the reorganized data into the response object and return ist
		$response->setData($categories);
		
		return $response;
	}
	
	public function addCategory($category) {
		$request = ($category instanceof ShopgateCategory)
			? $category->toArray()
			: $category;
		
		$request['action'] = 'add_category';
		
		return $this->sendRequest($request);
	}

	public function updateCategory($category) {
		$request = ($category instanceof ShopgateCategory)
			? $category->toArray()
			: $category;
		
		$request['action'] = 'update_category';
		
		return $this->sendRequest($request);
	}

	public function deleteCategory($categoryNumber, $deleteSubCategories = false, $deleteItems = false) {
		$request = array(
				'action' => 'delete_category',
				'category_number' => $categoryNumber,
				'delete_subcategories' => $deleteSubCategories ? 1 : 0,
				'delete_items' => $deleteItems ? 1 : 0,
		);

		return $this->sendRequest($request);
	}

	public function addItemToCategory($itemNumber, $categoryNumber, $orderIndex = null) {
		$request = array(
				'action' => 'add_item_to_category',
				'category_number' => $categoryNumber,
				'item_number' => $itemNumber,
		);

		if (isset($orderIndex)) {
			$request['order_index'] = $orderIndex;
		}

		return $this->sendRequest($request);
	}

	public function deleteItemFromCategory($itemNumber, $categoryNumber) {
		$request = array(
				'action' => 'delete_item_from_category',
				'category_number' => $categoryNumber,
				'item_number' => $itemNumber,
		);

		return $this->sendRequest($request);
	}
}

class ShopgateAuthentificationService extends ShopgateObject implements ShopgateAuthentificationServiceInterface {
	private $customerNumber;
	private $apiKey;
	private $timestamp;
	
	public function __construct($customerNumber, $apiKey) {
		$this->customerNumber = $customerNumber;
		$this->apiKey = $apiKey;
		
		$this->startNewSession();
	}
	
	public function startNewSession() {
		$this->timestamp = time();
	}
	
	public function buildAuthUser() {
		return $this->customerNumber.'-'.$this->getTimestamp();
	}
	
	public function buildAuthUserHeader() {
		return self::HEADER_X_SHOPGATE_AUTH_USER .': '. $this->buildAuthUser();
	}
	
	public function buildAuthToken($prefix = 'SMA') {
		return $this->buildCustomAuthToken($prefix, $this->customerNumber, $this->getTimestamp(), $this->apiKey);
	}
	
	public function buildAuthTokenHeader($prefix = 'SMA') {
		return self::HEADER_X_SHOPGATE_AUTH_TOKEN.': '.$this->buildAuthToken($prefix);
	}
	
	public function buildMerchantApiAuthTokenHeader() {
		return $this->buildAuthTokenHeader('SMA');
	}
	
	public function buildPluginApiAuthTokenHeader() {
		return $this->buildAuthTokenHeader('SPA');
	}

	public function checkAuthentification() {
		if(defined('SHOPGATE_DEBUG') && SHOPGATE_DEBUG === 1) return;

		if (empty($_SERVER[self::PHP_X_SHOPGATE_AUTH_USER]) || empty($_SERVER[self::PHP_X_SHOPGATE_AUTH_TOKEN])){
			throw new ShopgateLibraryException(ShopgateLibraryException::AUTHENTICATION_FAILED, 'No authentication data present.');
		}

		// for convenience
		$name = $_SERVER[self::PHP_X_SHOPGATE_AUTH_USER];
		$token = $_SERVER[self::PHP_X_SHOPGATE_AUTH_TOKEN];

		// extract customer number and timestamp from username
		$matches = array();
		if (!preg_match('/(?P<customer_number>[1-9][0-9]+)-(?P<timestamp>[1-9][0-9]+)/', $name, $matches)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::AUTHENTICATION_FAILED, 'Cannot parse: '.$name.'.');
		}

		// for convenience
		$customer_number = $matches['customer_number'];
		$timestamp = $matches['timestamp'];

		// request shouldn't be older than 30 minutes or more than 30 minutes in the future
		if (abs($this->getTimestamp() - $timestamp) > (30*60)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::AUTHENTICATION_FAILED, 'Request too old or too far in the future.');
		}
		
		// create the authentification-password
		$generatedPassword = $this->buildCustomAuthToken('SPA', $customer_number, $timestamp, $this->apiKey);

		// compare customer-number and auth-password and make sure, the API key was set in the configuration
		if (($customer_number != $this->customerNumber) || ($token != $generatedPassword) || (empty($this->apiKey))) {
			throw new ShopgateLibraryException(ShopgateLibraryException::AUTHENTICATION_FAILED, 'Invalid authentication data.');
		}
	}
	
	/**
	 * Return current timestamp
	 *
	 * @return int
	 */
	protected function getTimestamp() {
		return $this->timestamp;
	}
	
	/**
	 * Generates the auth token with the given parameters.
	 *
	 * @param string $prefix
	 * @param string $customerNumber
	 * @param int $timestamp
	 * @param string $apiKey
	 * @throws ShopgateLibraryException when no customer number or API key is set
	 * @return string The SHA-1 hash Auth Token for Shopgate's Authentication
	 */
	protected function buildCustomAuthToken($prefix, $customerNumber, $timestamp, $apiKey) {
		if (empty($customerNumber) || empty($apiKey)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::CONFIG_INVALID_VALUE, 'Shopgate customer number or  API key not set.', true, false);
		}
		
		return sha1("{$prefix}-{$customerNumber}-{$timestamp}-{$apiKey}");
	}
}

/**
 * Wrapper for responses by the Shopgate Plugin API.
 *
 * Each content type is represented by a subclass.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
abstract class ShopgatePluginApiResponse extends ShopgateObject {
	protected $error;
	protected $error_text;
	protected $trace_id;
	protected $version;
	protected $pluginVersion;
	protected $data;
	
	public function __construct($traceId, $version = SHOPGATE_LIBRARY_VERSION, $pluginVersion = null) {
		$this->error = 0;
		$this->error_text = null;
		$this->trace_id = $traceId;
		$this->version = $version;
		$this->pluginVersion = (empty($pluginVersion) && defined('SHOPGATE_PLUGIN_VERSION')) ? SHOPGATE_PLUGIN_VERSION : $pluginVersion;
	}
	
	/**
	 * Marks the response as error.
	 */
	public function markError($code, $message) {
		$this->error = $code;
		$this->error_text = $message;
	}
	
	public function setData($data) {
		$this->data = $data;
	}
	
	abstract public function send();
}

/**
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgatePluginApiResponseTextPlain extends ShopgatePluginApiResponse {
	public function send() {
		header('HTTP/1.0 200 OK');
		header('Content-Type: text/plain; charset=UTF-8');
		echo $this->data;
		exit;
	}
}

/**
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgatePluginApiResponseTextCsv extends ShopgatePluginApiResponse {
	public function setData($data) {
		if (!file_exists($data)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_FILE_NOT_FOUND, 'File: '.$data, true);
		}
		
		$this->data = $data;
	}
	
	public function send() {
		$fp = @fopen($this->data, 'r');
		if (!$fp) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_FILE_OPEN_ERROR, 'File: '.$this->data, true);
		}
		
		// output headers ...
		header('HTTP/1.0 200 OK');
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="'.basename($this->data).'"');
		
		// ... and csv file
		while ($line = fgets($fp)) echo $line;
		
		// clean up and leave
		fclose($fp);
		exit;
	}
}

/**
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgatePluginApiResponseAppJson extends ShopgatePluginApiResponse {
	public function send() {
		$data = array();
		$data['error'] = $this->error;
		$data['error_text'] = $this->error_text;
		$data['trace_id'] = $this->trace_id;
		$data['shopgate_library_version'] = $this->version;
		if (!empty($this->pluginVersion)) {
			$data['plugin_version'] = $this->pluginVersion;
		}
		$this->data = array_merge($data, $this->data);
		
		header("HTTP/1.0 200 OK");
		header("Content-Type: application/json");
		echo $this->jsonEncode($this->data);
	}
}

/**
 * Wrapper for responses by the Shopgate Merchant API
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgateMerchantApiResponse extends ShopgateContainer {
	protected $sma_version;
	protected $trace_id;
	protected $limit;
	protected $offset;
	protected $has_more_results;
	protected $errors;
	protected $data;
	
	public function __construct($data = array()) {
		$this->sma_version = '';
		$this->trace_id = '';
		$this->limit = 1;
		$this->offset = 1;
		$this->has_more_results = false;
		$this->errors = array();
		$this->data = array();
		
		$unmappedData = $this->loadArray($data);
		
		if (!empty($unmappedData)) {
			$this->data = $unmappedData;
		}
	}
	
	/**
	 * @param integer $value
	 */
	protected function setSmaVersion($value) {
		$this->sma_version = $value;
	}

	/**
	 * @param integer $value
	 */
	protected function setTraceId($value) {
		$this->trace_id = $value;
	}

	/**
	 * @param integer $value
	 */
	protected function setLimit($value) {
		$this->limit = $value;
	}

	/**
	 * @param integer $value
	 */
	protected function setOffset($value) {
		$this->offset = $value;
	}

	/**
	 * @param bool $value
	 */
	protected function setHasMoreResults($value) {
		$this->has_more_results = $value;
	}
	
	/**
	 *
	 * @param string[] $value
	 */
	protected function setErrors($value) {
		$this->errors = $value;
	}

	/**
	 * @param $value mixed
	 */
	public function setData($value) {
		$this->data = $value;
	}

	/**
	 * @return string
	 */
	public function getSmaVersion() {
		return $this->sma_version;
	}

	/**
	 * @return string
	 */
	public function getTraceId() {
		return $this->trace_id;
	}

	/**
	 * @return integer
	 */
	public function getLimit() {
		return $this->limit;
	}

	/**
	 * @return integer
	 */
	public function getOffset() {
		return $this->offset;
	}

	/**
	 * @return bool
	 */
	public function getHasMoreResults() {
		return $this->has_more_results;
	}
	
	/**
	 * @return mixed[]
	 */
	public function getErrors() {
		return $this->errors;
	}

	/**
	 * @return mixed
	 */
	public function getData() {
		return $this->data;
	}
	
	public function accept(ShopgateContainerVisitor $v) {
		return; // not implemented
	}
}

/**
 * This interface represents the Shopgate Plugin API as described in our wiki.
 *
 * It provides all available actions and calls the plugin implementation's callback methods for data retrieval if necessary.
 *
 * @see http://wiki.shopgate.com/Shopgate_Plugin_API/
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
interface ShopgatePluginApiInterface {
	/**
	 * Inspects an incoming request, performs the requested actions, prepares and prints out the response to the requesting entity.
	 *
	 * Note that the method usually returns true or false on completion, depending on the success of the operation. However, some actions such as
	 * the get_*_csv actions, might stop the script after execution to prevent invalid data being appended to the output.
	 *
	 * @param mixed[] $data The incoming request's parameters.
	 * @return bool false if an error occured, otherwise true.
	 */
	public function handleRequest(array $data = array());
}

/**
 * This class represents the Shopgate Merchant API as described in our wiki.
 *
 * It provides all available actions, calls to the configured API, retrieves, parses and formats the data.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
interface ShopgateMerchantApiInterface {
	######################################################################
	## Orders                                                           ##
	######################################################################
	/**
	 * Represents the "get_orders" action.
	 *
	 * @param mixed[] $parameters
	 *
	 * @return ShopgateMerchantApiResponse
	 *
	 * @throws ShopgateLibraryException in case the connection can't be established
	 * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
	 *
	 * @see http://wiki.shopgate.com/Merchant_API_get_orders/
	 */
	public function getOrders($parameters);
	
	/**
	 * Represents the "add_order_delivery_note" action.
	 *
	 * @param string $orderNumber
	 * @param string $shippingServiceId
	 * @param int $trackingNumber
	 * @param bool $markAsCompleted
	 * @param bool $sendCustomerMail
	 *
	 * @return ShopgateMerchantApiResponse
	 *
	 * @throws ShopgateLibraryException in case the connection can't be established
	 * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
	 *
	 * @see http://wiki.shopgate.com/Merchant_API_add_order_delivery_note/
	 */
	public function addOrderDeliveryNote($orderNumber, $shippingServiceId, $trackingNumber, $markAsCompleted = false, $sendCustomerMail = true);
	
	/**
	 * Represents the "set_order_shipping_completed" action.
	 *
	 * @param string $orderNumber
	 *
	 * @return ShopgateMerchantApiResponse
	 *
	 * @throws ShopgateLibraryException in case the connection can't be established
	 * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
	 *
	 * @see http://wiki.shopgate.com/Merchant_API_set_order_shipping_completed/
	 */
	public function setOrderShippingCompleted($orderNumber);
	
	/**
	 * Represents the "cancel_order" action.
	 *
	 * @param string $orderNumber
	 * @param bool $cancelCompleteOrder
	 * @param array('item_number' => string, 'quantity' => int)[] $cancellationItems
	 * @param bool $cancelShipping
	 * @param string $cancellationNote
	 *
	 * @return ShopgateMerchantApiResponse
	 *
	 * @throws ShopgateLibraryException in case the connection can't be established
	 * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
	 *
	 * @see http://wiki.shopgate.com/Merchant_API_cancel_order/
	 */
	public function cancelOrder($orderNumber, $cancelCompleteOrder = false, $cancellationItems = array(), $cancelShipping = false, $cancellationNote = '');
	
	######################################################################
	## Mobile Redirect                                                  ##
	######################################################################
	/**
	 * Represents the "get_mobile_redirect_keywords" action.
	 *
	 * This method is deprecated, please use getMobileRedirectUserAgents().
	 *
	 * @return array('keywords' => string[], 'skipKeywords' => string[])
	 *
	 * @throws ShopgateLibraryException in case the connection can't be established
	 * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
	 *
	 * @deprecated
	 */
	public function getMobileRedirectKeywords();
	
	/**
	 * Represents the "get_mobile_user_agents" action.
	 *
	 * @return array 'keywords' => string[], 'skip_keywords' => string[]
	 *
	 * @throws ShopgateLibraryException in case the connection can't be established
	 * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
	 *
	 * @see http://wiki.shopgate.com/Merchant_API_get_mobile_redirect_user_agents
	 */
	public function getMobileRedirectUserAgents();
	
	######################################################################
	## Items                                                            ##
	######################################################################
	/**
	 * Represents the "get_items" action.
	 *
	 * @param mixed[] $parameters
	 *
	 * @return ShopgateMerchantApiResponse
	 *
	 * @throws ShopgateLibraryException in case the connection can't be established
	 * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
	 *
	 * @see http://wiki.shopgate.com/Merchant_API_get_items/
	 */
	public function getItems($parameters);
	
	/**
	 * Represents the "add_item" action.
	 *
	 * @param mixed[]|ShopgateItem $item
	 *
	 * @return ShopgateMerchantApiResponse
	 *
	 * @throws ShopgateLibraryException in case the connection can't be established
	 * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
	 *
	 * @see http://wiki.shopgate.com/Merchant_API_add_item/
	 */
	public function addItem($item);
	
	/**
	 * Represents the "update_item" action.
	 *
	 * @param mixed[]|ShopgateItem $item
	 *
	 * @return ShopgateMerchantApiResponse
	 *
	 * @throws ShopgateLibraryException in case the connection can't be established
	 * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
	 *
	 * @see http://wiki.shopgate.com/Merchant_API_update_item/
	 */
	public function updateItem($item);
	
	/**
	 * Represents the "delete_item" action.
	 *
	 * @param string $itemNumber
	 *
	 * @return ShopgateMerchantApiResponse
	 *
	 * @throws ShopgateLibraryException in case the connection can't be established
	 * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
	 *
	 * @see http://wiki.shopgate.com/Merchant_API_delete_item/
	 */
	public function deleteItem($itemNumber);
	
	/**
	 * Represents the "batch_add_items" action.
	 *
	 * @param mixed[]|ShopgateItem[] $items
	 *
	 * @return ShopgateMerchantApiResponse
	 *
	 * @throws ShopgateLibraryException in case the connection can't be established
	 * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
	 *
	 * @see http://wiki.shopgate.com/Merchant_API_batch_add_items/
	 */
	public function batchAddItems($items);
	
	/**
	 * Represents the "batch_update_items" action.
	 *
	 * @param mixed[]|ShopgateItem[] $items
	 *
	 * @return ShopgateMerchantApiResponse
	 *
	 * @throws ShopgateLibraryException in case the connection can't be established
	 * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
	 *
	 * @see http://wiki.shopgate.com/Merchant_API_batch_update_items/
	 */
	public function batchUpdateItems($items);
	
	######################################################################
	## Categories                                                       ##
	######################################################################
	/**
	 * Represents the "get_categories" action.
	 *
	 * @param mixed[] $parameters
	 *
	 * @return ShopgateMerchantApiResponse
	 *
	 * @throws ShopgateLibraryException in case the connection can't be established
	 * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
	 *
	 * @see http://wiki.shopgate.com/Merchant_API_get_categories/
	 */
	public function getCategories($parameters);
	
	/**
	 * Represents the "add_category" action.
	 *
	 * @param mixed[]|ShopgateCategory $category
	 *
	 * @return ShopgateMerchantApiResponse
	 *
	 * @throws ShopgateLibraryException in case the connection can't be established
	 * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
	 *
	 * @see http://wiki.shopgate.com/Merchant_API_add_category/
	 */
	public function addCategory($category);
	
	/**
	 * Represents the "update_category" action.
	 *
	 * @param mixed[]|ShopgateCategory $category
	 *
	 * @return ShopgateMerchantApiResponse
	 *
	 * @throws ShopgateLibraryException in case the connection can't be established
	 * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
	 *
	 * @see http://wiki.shopgate.com/Merchant_API_update_category/
	 */
	public function updateCategory($category);
	
	/**
	 * Represents the "delete_category" action.
	 *
	 * @param string $categoryNumber
	 * @param bool $deleteSubCategories
	 * @param bool $deleteItems
	 *
	 * @return ShopgateMerchantApiResponse
	 *
	 * @throws ShopgateLibraryException in case the connection can't be established
	 * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
	 *
	 * @see http://wiki.shopgate.com/Merchant_API_delete_category/
	 */
	public function deleteCategory($categoryNumber, $deleteSubCategories = false, $deleteItems = false);
	
	/**
	 * Represents the "add_item_to_category" action.
	 *
	 * @param string $itemNumber
	 * @param string $categoryNumber
	 * @param int $orderIndex
	 *
	 * @return ShopgateMerchantApiResponse
	 *
	 * @throws ShopgateLibraryException in case the connection can't be established
	 * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
	 *
	 * @see http://wiki.shopgate.com/Merchant_API_add_item_to_category/
	 */
	public function addItemToCategory($itemNumber, $categoryNumber, $orderIndex = null);
	
	/**
	 * Represents the "delete_item_from_category" action.
	 *
	 * @param string $itemNumber
	 * @param string $categoryNumber
	 *
	 * @return ShopgateMerchantApiResponse
	 *
	 * @throws ShopgateLibraryException in case the connection can't be established
	 * @throws ShopgateMerchantApiException in case the response is invalid or an error occured
	 *
	 * @see http://wiki.shopgate.com/Merchant_API_delete_item_from_category
	 */
	public function deleteItemFromCategory($itemNumber, $categoryNumber);
}

/**
 * This class provides methods to check and generate authentification strings.
 *
 * It is used internally by the Shopgate Library to send requests or check incoming requests.
 *
 * To check authentication on incoming request it accesses the $_SERVER variable which should contain the required X header fields for
 * authentication.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
interface ShopgateAuthentificationServiceInterface {
	const HEADER_X_SHOPGATE_AUTH_USER  = 'X-Shopgate-Auth-User';
	const HEADER_X_SHOPGATE_AUTH_TOKEN = 'X-Shopgate-Auth-Token';
	const PHP_X_SHOPGATE_AUTH_USER  = 'HTTP_X_SHOPGATE_AUTH_USER';
	const PHP_X_SHOPGATE_AUTH_TOKEN = 'HTTP_X_SHOPGATE_AUTH_TOKEN';

	/**
	 * @return string The auth user string.
	 */
	public function buildAuthUser();
	
	/**
	 * @return string The X-Shopgate-Auth-User HTTP header for an outgoing request.
	 */
	public function buildAuthUserHeader();
	
	/**
	 * @param $prefix string SMA|SPA
	 * @return string The auth token string.
	 */
	public function buildAuthToken($prefix = 'SMA');

	/**
	 * @param $prefix string SMA|SPA
	 * @return string The X-Shopgate-Auth-Token HTTP header.
	 */
	public function buildAuthTokenHeader($prefix = 'SMA');

	/**
	 * @return string The X-Shopgate-Auth-Token HTTP header for an outgoing request.
	 */
	public function buildMerchantApiAuthTokenHeader();
	
	/**
	 * @return string The X-Shopgate-Auth-Token HTTP header for an incoming request.
	 */
	public function buildPluginApiAuthTokenHeader();
	
	/**
	 * @throws ShopgateLibraryException if authentication fails
	 */
	public function checkAuthentification();

	/**
	 * Start a new Authentication session
	 */
	public function startNewSession();
}