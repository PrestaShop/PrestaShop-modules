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

###################################################################################
# define constants
###################################################################################
define('SHOPGATE_LIBRARY_VERSION', '2.3.6');
define('SHOPGATE_LIBRARY_ENCODING' , 'UTF-8');
define('SHOPGATE_BASE_DIR', realpath(dirname(__FILE__).'/../'));

/**
 * Error handler for PHP errors.
 *
 * To use the Shopgate error handler it must be activated in your configuration.
 *
 * @param int $errno
 * @param string $errstr
 * @param string $errfile
 * @param int $errline
 * @see http://php.net/manual/en/function.set-error-handler.php
 */
function ShopgateErrorHandler($errno, $errstr, $errfile, $errline) {
	switch ($errno) {
		case E_NOTICE:
		case E_USER_NOTICE:
			$severity = "Notice";
			break;
		case E_WARNING:
		case E_USER_WARNING:
			$severity = "Warning";
			break;
		case E_ERROR:
		case E_USER_ERROR:
			$severity = "Fatal Error";
			break;
		default:
			$severity = "Unknown Error";
			break;
	}

	$msg = "$severity [Nr. $errno : $errfile / $errline] ";
	$msg .= "$errstr";
	$msg .= "\n". print_r(debug_backtrace(false), true);

	ShopgateLogger::getInstance()->log($msg);

	return true;
}

/**
 * Exception type for errors within the Shopgate Library.
 *
 * This is used by the Shopgate Library and should be used by plugins and their components. Predefined error
 * codes and messages are to be used. If not suitable, a custom message can be passed which results in error
 * code 999 (unknown error code) with the message appended. Error code, message, time, additional information
 * and part of the stack trace will be logged automatically on construction of a ShopgateLibraryException.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgateLibraryException extends Exception {
	/**
	 * @var string
	 */
	private $additionalInformation;

	// Initizialization / instantiation of plugin failure
	//const INIT_EMPTY_CONFIG = 1;
	const INIT_LOGFILE_OPEN_ERROR = 2;

	// Configuration failure
	const CONFIG_INVALID_VALUE = 10;
	const CONFIG_READ_WRITE_ERROR = 11;
	const CONFIG_PLUGIN_NOT_ACTIVE = 12;

	// Plugin API errors
	const PLUGIN_API_NO_ACTION = 20;
	const PLUGIN_API_UNKNOWN_ACTION = 21;
	const PLUGIN_API_DISABLED_ACTION = 22;
	const PLUGIN_API_WRONG_RESPONSE_FORMAT = 23;

	const PLUGIN_API_UNKNOWN_SHOP_NUMBER = 24;
	
	const PLUGIN_API_NO_ORDER_NUMBER = 30;
	const PLUGIN_API_NO_CART = 31;
	const PLUGIN_API_NO_USER = 35;
	const PLUGIN_API_NO_PASS = 36;
	const PLUGIN_API_UNKNOWN_LOGTYPE = 38;
	const PLUGIN_API_CRON_NO_JOBS = 40;
	const PLUGIN_API_CRON_NO_JOB_NAME = 41;

	// Plugin errors
	const PLUGIN_DUPLICATE_ORDER = 60;
	const PLUGIN_ORDER_NOT_FOUND = 61;
	const PLUGIN_NO_CUSTOMER_GROUP_FOUND = 62;
	const PLUGIN_ORDER_ITEM_NOT_FOUND = 63;
	const PLUGIN_ORDER_STATUS_IS_SENT = 64;
	const PLUGIN_ORDER_ALREADY_UP_TO_DATE = 65;

	const PLUGIN_NO_ADDRESSES_FOUND = 70;
	const PLUGIN_WRONG_USERNAME_OR_PASSWORD = 71;
	
	const PLUGIN_FILE_DELETE_ERROR = 79;
	const PLUGIN_FILE_NOT_FOUND = 80;
	const PLUGIN_FILE_OPEN_ERROR = 81;
	const PLUGIN_FILE_EMPTY_BUFFER = 82;
	const PLUGIN_DATABASE_ERROR = 83;
	const PLUGIN_UNKNOWN_COUNTRY_CODE = 84;
	const PLUGIN_UNKNOWN_STATE_CODE = 85;

	const PLUGIN_CRON_UNSUPPORTED_JOB = 91;

	// Merchant API errors
	const MERCHANT_API_NO_CONNECTION = 100;
	const MERCHANT_API_INVALID_RESPONSE = 101;
	const MERCHANT_API_ERROR_RECEIVED = 102;

	// Authentification errors
	const AUTHENTICATION_FAILED = 120;

	// File errors
	const FILE_READ_WRITE_ERROR = 130;
	
	// Coupon Errors
	const COUPON_NOT_VALID = 200;
	const COUPON_CODE_NOT_VALID = 201;
	const COUPON_INVALID_PRODUCT = 202;
	const COUPON_INVALID_ADDRESS = 203;
	const COUPON_INVALID_USER = 204;
	const COUPON_TOO_MANY_COUPONS = 205;

	// Unknown error code (the value passed as code gets to be the message)
	const UNKNOWN_ERROR_CODE = 999;

	protected static $errorMessages = array(
		// Initizialization / instantiation of plugin failure
		//self::INIT_EMPTY_CONFIG => 'empty configuration',
		self::INIT_LOGFILE_OPEN_ERROR => 'cannot open/create logfile(s)',

		// Configuration failure
		self::CONFIG_INVALID_VALUE => 'invalid value in configuration',
		self::CONFIG_READ_WRITE_ERROR => 'error reading or writing configuration',
		self::CONFIG_PLUGIN_NOT_ACTIVE => 'plugin not activated',

		// Plugin API errors
		self::PLUGIN_API_NO_ACTION => 'no action specified',
		self::PLUGIN_API_UNKNOWN_ACTION  => 'unknown action requested',
		self::PLUGIN_API_DISABLED_ACTION => 'disabled action requested',
		self::PLUGIN_API_WRONG_RESPONSE_FORMAT => 'wrong response format',
		
		self::PLUGIN_API_UNKNOWN_SHOP_NUMBER => 'unknown shop number received',

		self::PLUGIN_API_NO_ORDER_NUMBER => 'parameter "order_number" missing',
		self::PLUGIN_API_NO_CART => 'parameter "cart" missing',
		self::PLUGIN_API_NO_USER => 'parameter "user" missing',
		self::PLUGIN_API_NO_PASS => 'parameter "pass" missing',
		self::PLUGIN_API_UNKNOWN_LOGTYPE => 'unknown logtype',
		self::PLUGIN_API_CRON_NO_JOBS => 'parameter "jobs" missing',
		self::PLUGIN_API_CRON_NO_JOB_NAME => 'field "job_name" in parameter "jobs" missing',

		// Plugin errors
		self::PLUGIN_DUPLICATE_ORDER => 'duplicate order',
		self::PLUGIN_ORDER_NOT_FOUND => 'order not found',
		self::PLUGIN_NO_CUSTOMER_GROUP_FOUND => 'no customer group found for customer',
		self::PLUGIN_ORDER_ITEM_NOT_FOUND => 'order item not found',
		self::PLUGIN_ORDER_STATUS_IS_SENT => 'order status is "sent"',
		self::PLUGIN_ORDER_ALREADY_UP_TO_DATE => 'order is already up to date',

		self::PLUGIN_NO_ADDRESSES_FOUND => 'no addresses found for customer',
		self::PLUGIN_WRONG_USERNAME_OR_PASSWORD => 'wrong username or password',

	
		self::PLUGIN_FILE_DELETE_ERROR => 'cannot delete file(s)',
		self::PLUGIN_FILE_NOT_FOUND => 'file not found',
		self::PLUGIN_FILE_OPEN_ERROR => 'cannot open file',
		self::PLUGIN_FILE_EMPTY_BUFFER => 'buffer is empty',
		self::PLUGIN_DATABASE_ERROR => 'database error',
		self::PLUGIN_UNKNOWN_COUNTRY_CODE => 'unknown country code',
		self::PLUGIN_UNKNOWN_STATE_CODE => 'unknown state code',

		self::PLUGIN_CRON_UNSUPPORTED_JOB => 'unsupported job',

		// Merchant API errors
		self::MERCHANT_API_NO_CONNECTION => 'no connection to server',
		self::MERCHANT_API_INVALID_RESPONSE => 'error parsing response',
		self::MERCHANT_API_ERROR_RECEIVED => 'error code received',

		// File errors
		self::FILE_READ_WRITE_ERROR => 'error reading or writing file',

		// Coupon Errors
		self::COUPON_NOT_VALID => 'invalid coupon',
		self::COUPON_CODE_NOT_VALID => 'invalid coupon code',
		self::COUPON_INVALID_PRODUCT => 'invalid product for coupon',
		self::COUPON_INVALID_ADDRESS => 'invalid address for coupon',
		self::COUPON_INVALID_USER => 'invalid user for coupon',
		self::COUPON_TOO_MANY_COUPONS => 'too many coupons in cart',
			
		// Authentification errors
		self::AUTHENTICATION_FAILED => 'authentication failed',
	);


	/**
	 * Exception type for errors within the Shopgate plugin and library.
	 *
	 * The general exception message is determined by the error code, the additionalInformation
	 * argument, if set, is appended.<br />
	 * <br />
	 * For compatiblity reasons, if an unknown error code is passed, the value is used as message
	 * and the code 999 (Unknown error code) is assigned. This should not be used anymore, though.
	 *
	 * @param int $code One of the constants defined in ShopgateLibraryException.
	 * @param string $additionalInformation More detailed information on what exactly went wrong.
	 * @param boolean $appendAdditionalInformationOnMessage Set true to output the additional information to the response. Set false to log it silently.
	 * @param boolean $writeLog true to create a log entry in the error log, false otherwise.
	 */
	public function __construct($code, $additionalInformation = null, $appendAdditionalInformationToMessage = false, $writeLog = true) {
		// Set code and message
		$logMessage = self::buildLogMessageFor($code, $additionalInformation);
		if (isset(self::$errorMessages[$code])) {
			$message = self::$errorMessages[$code];
		} else {
			$message = 'Unknown error code: "'.$code.'"';
			$code = self::UNKNOWN_ERROR_CODE;
		}

		if($appendAdditionalInformationToMessage){
			$message .= ': '.$additionalInformation;
		}

		// Save additional information
		$this->additionalInformation = $additionalInformation;

		// Log the error
		if (empty($writeLog)) {
			$message .= ' (logging disabled for this message)';
		} else {
			if (ShopgateLogger::getInstance()->log($code.' - '.$logMessage) === false) {
				$message .= ' (unable to log)';
			}
		}

		// Call default Exception class constructor
		parent::__construct($message, $code);
	}
	
	/**
	 * Returns the saved additional information.
	 *
	 * @return string
	 */
	public function getAdditionalInformation() {
		return (!is_null($this->additionalInformation) ? $this->additionalInformation : '');
	}

	/**
	 * Gets the error message for an error code.
	 *
	 * @param int $code One of the constants in this class.
	 */
	public static function getMessageFor($code) {
		if (isset(self::$errorMessages[$code])) {
			$message = self::$errorMessages[$code];
		} else {
			$message = 'Unknown error code: "'.$code.'"';
		}

		return $message;
	}

	/**
	 * Builds the message that would be logged if a ShopgateLibraryException was thrown with the same parameters and returns it.
	 *
	 * This is a convenience method for cases where logging is desired but the script should not abort. By using this function an empty
	 * try-catch-statement can be avoided. Just pass the returned string to ShopgateLogger::log().
	 *
	 * @param int $code One of the constants defined in ShopgateLibraryException.
	 * @param string $additionalInformation More detailed information on what exactly went wrong.
	 */
	public static function buildLogMessageFor($code, $additionalInformation) {
		$logMessage = self::getMessageFor($code);

		// Set additional information
		if (!empty($additionalInformation)) {
			$logMessage .= ' - Additional information: "'.$additionalInformation.'"';
		}
		
		$logMessage .= "\n\t";

		// Add tracing information to the message
		$btrace = debug_backtrace();
		for ($i = 1; $i < 6; $i++) {
			if (empty($btrace[$i+1])) break;
			
			$class = (isset($btrace[$i+1]['class'])) ? $btrace[$i+1]['class'].'::' : 'Unknown class - ';
			$function = (isset($btrace[$i+1]['function'])) ? $btrace[$i+1]['function'] : 'Unknown function';
			$file = ' in '.((isset($btrace[$i]['file'])) ? basename($btrace[$i]['file']) : 'Unknown file');
			$line = (isset($btrace[$i]['line'])) ? $btrace[$i]['line'] : 'Unkown line';
			$logMessage .= $class.$function.'()'.$file.':'.$line."\n\t";
		}

		return $logMessage;
	}
}

/**
 * Exception type for errors reported by the Shopgate Merchant API.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgateMerchantApiException extends Exception {
	const ORDER_NOT_FOUND = 201;
	const ORDER_ON_HOLD = 202;
	const ORDER_ALREADY_COMPLETED = 203;
	const ORDER_SHIPPING_STATUS_ALREADY_COMPLETED = 204;

	const INTERNAL_ERROR_OCCURED_WHILE_SAVING = 803;
	
	/**
	 * @var ShopgateMerchantApiResponse
	 */
	protected $response;

	/**
	 * Exception type for errors reported by the Shopgate Merchant API.
	 *
	 *
	 * @param int $code One of the constants defined in ShopgateMerchantApiException.
	 * @param string $additionalInformation More detailed information on what exactly went wrong.
	 * @param ShopgateMerchantApiResponse $response The response of the request that caused the exception to be thrown or null if the response was invalid.
	 */
	public function __construct($code, $additionalInformation, ShopgateMerchantApiResponse $response) {
		$this->response = $response;
		
		$message = $additionalInformation;
		$errors = $this->response->getErrors();
		if (!empty($errors)) {
			$message .= "\n".print_r($errors, true);
		}
		
		if (ShopgateLogger::getInstance()->log('SMA reports error: '.$code.' - '.$additionalInformation) === false) {
			$message .= ' (unable to log)';
		}
		
		parent::__construct($message, $code);
	}
	
	/**
	 * @return ShopgateMerchantApiResponse
	 */
	public function getResponse() {
		return $this->response;
	}
}

/**
 * Global class (Singleton) to manage log files.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgateLogger {
	const LOGTYPE_ACCESS = 'access';
	const LOGTYPE_REQUEST = 'request';
	const LOGTYPE_ERROR = 'error';
	const LOGTYPE_DEBUG = 'debug';

	const OBFUSCATION_STRING = 'XXXXXXXX';
	const REMOVED_STRING = '<removed>';

	/**
	 * @var bool
	 */
	private $debug;
	
	/**
	 * @var string[] Names of the fields that should be obfuscated on logging.
	 */
	private $obfuscationFields;
	
	/**
	 * @var string Names of the fields that should be removed from logging.
	 */
	private $removeFields;
	
	/**
	 * @var mixed[]
	 */
	private $files = array(
			self::LOGTYPE_ACCESS => array('path' => '', 'handle' => null, 'mode' => 'a+'),
			self::LOGTYPE_REQUEST => array('path' => '', 'handle' => null, 'mode' => 'a+'),
			self::LOGTYPE_ERROR => array('path' => '', 'handle' => null, 'mode' => 'a+'),
			self::LOGTYPE_DEBUG => array('path' => '', 'handle' => null, 'mode' => 'w+'),
	);

	/**
	 * @var ShopgateLogger
	 */
	private static $singleton;

	private function __construct() {
		$this->debug = false;
		$this->obfuscationFields = array('pass');
		$this->removeFields = array('cart');
	}
	
	/**
	 * @return ShopgateLogger
	 */
	public static function getInstance($accessLogPath = null, $requestLogPath = null, $errorLogPath = null, $debugLogPath = null) {
		if (empty(self::$singleton)) {
			self::$singleton = new self();
			
			// fall back to default log paths if none are specified
			if (empty($accessLogPath))  $accessLogPath  = SHOPGATE_BASE_DIR.DS.'temp'.DS.'logs'.DS.ShopgateConfigInterface::SHOPGATE_FILE_PREFIX.'access.log';
			if (empty($requestLogPath)) $requestLogPath = SHOPGATE_BASE_DIR.DS.'temp'.DS.'logs'.DS.ShopgateConfigInterface::SHOPGATE_FILE_PREFIX.'request.log';
			if (empty($errorLogPath))   $errorLogPath   = SHOPGATE_BASE_DIR.DS.'temp'.DS.'logs'.DS.ShopgateConfigInterface::SHOPGATE_FILE_PREFIX.'error.log';
			if (empty($debugLogPath))   $debugLogPath   = SHOPGATE_BASE_DIR.DS.'temp'.DS.'logs'.DS.ShopgateConfigInterface::SHOPGATE_FILE_PREFIX.'debug.log';
		}
		
		// set log file paths if requested
		self::$singleton->setLogFilePaths($accessLogPath, $requestLogPath, $errorLogPath, $debugLogPath);
		
		return self::$singleton;
	}
	
	/**
	 * Sets the paths to the log files.
	 *
	 * @param string $accessLogPath
	 * @param string $requestLogPath
	 * @param string $errorLogPath
	 * @param string $debugLogPath
	 */
	public function setLogFilePaths($accessLogPath, $requestLogPath, $errorLogPath, $debugLogPath) {
		if (!empty($accessLogPath)) {
			$this->files[self::LOGTYPE_ACCESS]['path'] = $accessLogPath;
		}
		
		if (!empty($requestLogPath)) {
			$this->files[self::LOGTYPE_REQUEST]['path'] = $requestLogPath;
		}
		
		if (!empty($errorLogPath)) {
			$this->files[self::LOGTYPE_ERROR]['path'] = $errorLogPath;
		}
		
		if (!empty($debugLogPath)) {
			$this->files[self::LOGTYPE_DEBUG]['path'] = $debugLogPath;
		}
	}
	
	/**
	 * Enables logging messages to debug log file.
	 */
	public function enableDebug() {
		$this->debug = true;
	}
	
	/**
	 * Disables logging messages to debug log file.
	 */
	public function disableDebug() {
		$this->debug = false;
	}
	
	/**
	 * @return true if logging messages to debug log file is enabled, false otherwise.
	 */
	public function isDebugEnabled() {
		return $this->debug;
	}
	
	/**
	 * Logs a message to the according log file.
	 *
	 * This produces a log entry of the form<br />
	 * <br />
	 * [date] [time]: [message]\n<br />
	 * <br />
	 * to the selected log file. If an unknown log type is passed the message will be logged to the error log file.<br />
	 * <br />
	 * Logging to LOGTYPE_DEBUG only is done after $this->enableDebug() has been called and $this->disableDebug() has not
	 * been called after that. The debug log file will be truncated on opening by default. To prevent this call
	 * $this->keepDebugLog(true).
	 *
	 * @param string $msg The error message.
	 * @param string $type The log type, that would be one of the ShopgateLogger::LOGTYPE_* constants.
	 * @return bool True on success, false on error.
	 */
	public function log($msg, $type = self::LOGTYPE_ERROR) {
		// build log message
		$msg = gmdate('d-m-Y H:i:s: ').$msg."\n";

		// determine log file type and append message
		switch (strtolower($type)) {
			// write to error log if type is unknown
			default: $type = self::LOGTYPE_ERROR;

			// allowed types:
			case self::LOGTYPE_ERROR:
			case self::LOGTYPE_ACCESS:
			case self::LOGTYPE_REQUEST:
			case self::LOGTYPE_DEBUG:
		}

		// if debug logging is requested but not activated, simply return
		if (($type === self::LOGTYPE_DEBUG) && !$this->debug) {
			return true;
		}

		// open log files if necessary
		if (!$this->openLogFileHandle($type)) {
			return false;
		}


		// try to log
		$success = false;
		if (fwrite($this->files[$type]['handle'], $msg) !== false) {
			$success = true;
		}

		return $success;
	}

	/**
	 * Set the file handler mode to a+ (keep) or to w+ (reverse) the debug log file
	 *
	 * @param bool $keep
	 */
	public function keepDebugLog($keep) {
		if ($keep)
			$this->files[self::LOGTYPE_DEBUG]["mode"]  = "a+";
		else
			$this->files[self::LOGTYPE_DEBUG]["mode"]  = "w+";
	}
	
	/**
	 * Returns the requested number of lines of the requested log file's end.
	 *
	 * @param string $type The log file to be read
	 * @param int $lines Number of lines to return
	 * @return string The requested log file content
	 *
	 * @see http://tekkie.flashbit.net/php/tail-functionality-in-php
	 */
	public function tail($type = self::LOGTYPE_ERROR, $lines = 20) {
		if (!isset($this->files[$type])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_UNKNOWN_LOGTYPE, 'Type: '.$type);
		}

		if (!$this->openLogFileHandle($type)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::INIT_LOGFILE_OPEN_ERROR, 'Type: '.$type);
		}

		if (empty($lines)) {
			$lines = 20;
		}

		$handle = $this->files[$type]['handle'];
		$lineCounter = $lines;
		$pos = -2;
		$beginning = false;
		$text = '';

		while ($lineCounter > 0) {
			$t = '';
			while ($t !== "\n") {
				if (@fseek($handle, $pos, SEEK_END) == -1) {
					$beginning = true;
					break;
				}
				$t = @fgetc($handle);
				$pos--;
			}

			$lineCounter--;
			if ($beginning) @rewind($handle);
			$text = @fgets($handle).$text;
			if ($beginning) break;
		}

		return $text;
	}
	
	/**
	 * Adds field names to the list of fields that should be obfuscated in the logs.
	 *
	 * @param string[] $fieldNames
	 */
	public function addObfuscationFields(array $fieldNames) {
		$this->obfuscationFields = array_merge($fieldNames, $this->obfuscationFields);
	}

	/**
	 * Adds field names to the list of fields that should be removed from the logs.
	 *
	 * @param string[] $fieldNames
	 */
	public function addRemoveFields(array $fieldNames) {
		$this->removeFields = array_merge($fieldNames, $this->removeFields);
	}

	/**
	 * Function to prepare the parameters of an API request for logging.
	 *
	 * Strips out critical request data like the password of a get_customer request.
	 *
	 * @param mixed[] $data The incoming request's parameters.
	 * @return string The cleaned parameters as string ready to log.
	 */
	public function cleanParamsForLog($data) {
		foreach ($data as $key => &$value) {
			if (in_array($key, $this->obfuscationFields)) {
				$value = self::OBFUSCATION_STRING;
			}
			
			if (in_array($key, $this->removeFields)) {
				$value = self::REMOVED_STRING;
			}
		}

		return print_r($data, true);
	}

	/**
	 * Opens log file handles for the requested log type if necessary.
	 *
	 * Already opened file handles will not be opened again.
	 *
	 * @param string $type The log type, that would be one of the ShopgateLogger::LOGTYPE_* constants.
	 * @return bool true if opening succeeds or the handle is already open; false on error.
	 */
	protected function openLogFileHandle($type) {
		// don't open file handle if already open
		if (!empty($this->files[$type]['handle'])) {
			return true;
		}

		// set the file handle
		$this->files[$type]['handle'] = @fopen($this->files[$type]['path'], $this->files[$type]['mode']);

		// if log files are not writeable continue silently to the next handle
		// TODO: This seems a bit too silent... How could we get notice of the error?
		if ($this->files[$type]['handle'] === false) {
			return false;
		}

		return true;
	}
}

/**
 * Builds the Shopgate Library object graphs for different purposes.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgateBuilder {
	/**
	 * @var ShopgateConfigInterface
	 */
	protected $config;
	
	/**
	 * Loads configuration and initializes the ShopgateLogger class.
	 *
	 * @param ShopgateConfigInterface $config
	 */
	public function __construct(ShopgateConfigInterface $config = null) {
		if (empty($config)) {
			$this->config = new ShopgateConfig();
		} else {
			$this->config = $config;
		}
		
		// set up logger
		ShopgateLogger::getInstance($this->config->getAccessLogPath(), $this->config->getRequestLogPath(), $this->config->getErrorLogPath(), $this->config->getDebugLogPath());
	}
	
	/**
	 * Builds the Shopgate Library object graph for a given ShopgatePlugin object.
	 *
	 * This initializes all necessary objects of the library, wires them together and injects them into
	 * the plugin class via its set* methods.
	 *
	 * @param ShopgatePlugin $plugin The ShopgatePlugin instance that should be wired to the framework.
	 */
	public function buildLibraryFor(ShopgatePlugin $plugin) {
		// set error handler if configured
		if ($this->config->getUseCustomErrorHandler()) {
			set_error_handler('ShopgateErrorHandler');
		}
		
		// instantiate API stuff
		$authService = new ShopgateAuthentificationService($this->config->getCustomerNumber(), $this->config->getApikey());
		$merchantApi = new ShopgateMerchantApi($authService, $this->config->getShopNumber(), $this->config->getApiUrl());
		$pluginApi = new ShopgatePluginApi($this->config, $authService, $merchantApi, $plugin);
		
		// instantiate export file buffer
		$fileBuffer = new ShopgateFileBuffer($this->config->getExportBufferCapacity(), $this->config->getExportConvertEncoding());
		
		// inject apis into plugin
		$plugin->setConfig($this->config);
		$plugin->setMerchantApi($merchantApi);
		$plugin->setPluginApi($pluginApi);
		$plugin->setBuffer($fileBuffer);
	}
	
	/**
	 * Builds the Shopgate Library object graph for ShopgateMerchantApi and returns the instance.
	 *
	 * @return ShopgateMerchantApi
	 */
	public function buildMerchantApi() {
		$authService = new ShopgateAuthentificationService($this->config->getCustomerNumber(), $this->config->getApikey());
		$merchantApi = new ShopgateMerchantApi($authService, $this->config->getShopNumber(), $this->config->getApiUrl());
		
		return $merchantApi;
	}
	
	/**
	 * Builds the Shopgate Library object graph for Shopgate mobile redirect and returns the instance.
	 *
	 * @return ShopgateMobileRedirect
	 */
	public function buildRedirect() {
		$merchantApi = $this->buildMerchantApi();
		$redirect = new ShopgateMobileRedirect(
				$this->config,
				$merchantApi
		);
		
		return $redirect;
	}
}

/**
 * ShopgateObject acts as root class of the Shopgate Library.
 *
 * It provides basic functionality like logging, camelization of strings, JSON de- and encoding etc.<br />
 * <br />
 * Almost all classes of the ShopgateLibrary except ShopgateLibraryException are derived from this class.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
abstract class ShopgateObject {
	/**
	 * Convenience method for logging to the ShopgateLogger.
	 *
	 * @param string $msg The error message.
	 * @param string $type The log type, that would be one of the ShopgateLogger::LOGTYPE_* constants.
	 * @return bool True on success, false on error.
	 */
	public function log($msg, $type = ShopgateLogger::LOGTYPE_ERROR) {
		return ShopgateLogger::getInstance()->log($msg, $type);
	}
	
	/**
	 * Converts a an underscored string to a camelized one.
	 *
	 * e.g.:<br />
	 * $this->camelize("get_categories_csv") returns "getCategoriesCsv"<br />
	 * $this->camelize("shopgate_library", true) returns "ShopgateLibrary"<br />
	 *
	 * @param string $str The underscored string.
	 * @param bool $capitalizeFirst Set true to capitalize the first letter (e.g. for class names). Default: false.
	 * @return string The camelized string.
	 */
	public function camelize($str, $capitalizeFirst = false) {
		$str = strtolower($str);
		if ($capitalizeFirst) {
			$str[0] = strtoupper($str[0]);
		}
		$func = create_function('$c', 'return strtoupper($c[1]);');
		return preg_replace_callback('/_([a-z0-9])/', $func, $str);
	}

	/**
	 * Creates a JSON string from any passed value.
	 *
	 * If json_encode() exists it's done by that, otherwise an external class provided with the Shopgate Library is used.
	 *
	 * @param mixed $value
	 * @return string
	 */
	public function jsonEncode($value) {
		// if json_encode exists use that
		if (extension_loaded('json') && function_exists('json_encode')) {
			return $string = json_encode($value);
		}

		// if not check if external class is loaded
		if (!class_exists('sgServicesJSON')) {
			require_once dirname(__FILE__).'/../vendors/JSON.php';
		}

		// encode via external class
		$jsonService = new sgServicesJSON(sgServicesJSON_LOOSE_TYPE);
		return $jsonService->encode($value);
	}

	/**
	 * Creates a variable, array or object from any passed JSON string.
	 *
	 * If json_encode() exists it's done by that, otherwise an external class provided with the Shopgate Library is used.
	 *
	 * @param string $value
	 * @return mixed
	 */
	public function jsonDecode($json, $assoc = false) {
		// if json_decode exists use that
		if (extension_loaded('json') && function_exists('json_decode')) {
			return json_decode($json, $assoc);
		}

		// if not check if external class is loaded
		if (!class_exists('sgServicesJSON')) {
			require_once dirname(__FILE__).'/../vendors/JSON.php';
		}

		// decode via external class
		$jsonService = new sgServicesJSON(($assoc) ? sgServicesJSON_LOOSE_TYPE : sgServicesJSON_IN_OBJ);
		return $jsonService->decode($json);
	}

	/**
	 * Encodes a string from a given encoding to UTF-8.
	 *
	 * @param string $string The string to encode.
	 * @param string|string[] $sourceEncoding The (possible) encoding(s) of $string.
	 * @param bool $force Set this true to enforce encoding even if the source encoding is already UTF-8.
	 * @return string The UTF-8 encoded string.
	 */
	public function stringToUtf8($string, $sourceEncoding = 'ISO-8859-15', $force = false) {
		$conditions =
			is_string($sourceEncoding) &&
			($sourceEncoding == SHOPGATE_LIBRARY_ENCODING) &&
			!$force;
		
		return ($conditions)
			? $string
			: $this->convertEncoding($string, SHOPGATE_LIBRARY_ENCODING, $sourceEncoding);
	}

	/**
	 * Decodes a string from UTF-8 to a given encoding.
	 *
	 * @param string $string The string to decode.
	 * @param string $destinationEncoding The desired encoding of the return value.
	 * @param bool $force Set this true to enforce encoding even if the destination encoding is set to UTF-8.
	 * @return string The UTF-8 decoded string.
	 */
	public function stringFromUtf8($string, $destinationEncoding = 'ISO-8859-15', $force = false) {
		return ($destinationEncoding == SHOPGATE_LIBRARY_ENCODING) && !$force
				? $string
				: $this->convertEncoding($string, $destinationEncoding, SHOPGATE_LIBRARY_ENCODING);
	}
	
	/**
	 * Converts a string's encoding to another.
	 *
	 * This wraps the mb_convert_encoding() and iconv() functions of PHP. If the mb_string extension is not installed,
	 * iconv() will be used instead.
	 *
	 * If iconv() must be used and an array is passed as $sourceEncoding all encodings will be tested and the (probably)
	 * best encoding will be used for conversion.
	 *
	 * @see http://php.net/manual/en/function.mb-convert-encoding.php
	 * @see http://php.net/manual/en/function.iconv.php
	 *
	 * @param string $string The string to decode.
	 * @param string $destinationEncoding The desired encoding of the return value.
	 * @param string|string[] $sourceEncoding The (possible) encoding(s) of $string.
	 * @return string The UTF-8 decoded string.
	 */
	protected function convertEncoding($string, $destinationEncoding, $sourceEncoding) {
		if (function_exists('mb_convert_encoding')) {
			return mb_convert_encoding($string, $destinationEncoding, $sourceEncoding);
		} else {
			// I have no excuse for the following. Please forgive me.
			if (is_array($sourceEncoding)) {
				$bestEncoding = '';
				$bestScore = null;
				foreach ($sourceEncoding as $encoding) {
					$score = abs(strlen($string) - strlen(@iconv($encoding, $destinationEncoding, $string)));
					if (is_null($bestScore) || ($score < $bestScore)) {
						$bestScore = $score;
						$bestEncoding = $encoding;
					}
				}
				
				$sourceEncoding = $bestEncoding;
			}
			
			return @iconv($sourceEncoding, $destinationEncoding.'//IGNORE', $string);
		}
	}
}

/**
 * This class acts as super class for plugin implementations and provides some basic functionality.
 *
 * A plugin implementation using the Shopgate Library must be derived from this class. The abstract methods are callback methods for
 * shop system specific operations such as retrieval of customer or order information, adding or updating orders etc.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
abstract class ShopgatePlugin extends ShopgateObject {
	const PRODUCT_STATUS_STOCK = 'stock';
	const PRODUCT_STATUS_ACTIVE = 'active';
	const PRODUCT_STATUS_INACTIVE = 'inactive';
	
	/**
	 * @var ShopgateBuilder
	 */
	protected $builder;
	
	/**
	 * @var ShopgateConfigInterface
	 */
	protected $config;
	
	/**
	 * @var ShopgateMerchantApiInterface
	 */
	protected $merchantApi;
	
	/**
	 * @var ShopgatePluginApiInterface
	 */
	protected $pluginApi;
	
	/**
	 * @var ShopgateFileBufferInterface
	 */
	protected $buffer;

	/**
	 * @var int
	 */
	protected $exportLimit;

	/**
	 * @var int
	 */
	protected $exportOffset;

	/**
	 * @var bool
	 */
	protected $splittedExport = false;

	/**
	 * @var double The exchange rate used for items export or orders import.
	 */
	protected $exchangeRate = 1;
	
	/**
	 * @param ShopgateBuilder $builder If empty, the default ShopgateBuilder will be instantiated.
	 */
	public final function __construct(ShopgateBuilder $builder = null) {
		// some default values
		$this->splittedExport = false;
		$this->exportOffset = 0;
		$this->exportLimit = 1000;
		
		// fire the plugin's startup callback
		try {
			$this->startup();
		} catch (ShopgateLibraryException $e) {
			// logging is done in exception constructor
		}
		
		// build the object graph and get needed objects injected via set* methods
		if (empty($builder)) $builder = new ShopgateBuilder($this->config);
		$builder->buildLibraryFor($this);
		
		// store the builder
		$this->builder = $builder;
	}
	
	/**
	 * @param bool $splitted True to activate partial export via limit and offset.
	 */
	public final function setSplittedExport($splitted) {
		$this->splittedExport = $splitted;
	}
	
	/**
	 * @param int $offset Offset to start export at.
	 */
	public final function setExportOffset($offset) {
		$this->exportOffset = $offset;
	}
	
	/**
	 * @param int $limit Maximum number of items to be exported.
	 */
	public final function setExportLimit($limit) {
		$this->exportLimit = $limit;
	}
	
	/**
	 * @param ShopgateConfigInterface $config
	 */
	public final function setConfig(ShopgateConfigInterface $config) {
		$this->config = $config;
	}
	
	public final function setMerchantApi(ShopgateMerchantApiInterface $merchantApi) {
		$this->merchantApi = $merchantApi;
	}
	
	/**
	 * @param ShopgatePluginApiInterface $pluginApi
	 */
	public final function setPluginApi(ShopgatePluginApiInterface $pluginApi) {
		$this->pluginApi = $pluginApi;
	}

	/**
	 * @param ShopgateFileBuffer $buffer
	 */
	public final function setBuffer(ShopgateFileBufferInterface $buffer) {
		$this->buffer = $buffer;
	}
	
	###################################################
	## Dispatching to Plugin API or export callbacks ##
	###################################################
	
	/**
	 * Convenience method to call ShopgatePluginApi::handleRequest() from $this.
	 *
	 * @param mixed[] $data The incoming request's parameters.
	 * @return bool false if an error occured, otherwise true.
	 */
	public final function handleRequest($data = array()) {
		return $this->pluginApi->handleRequest($data);
	}

	/**
	 * Takes care of buffer and file handlers and calls ShopgatePlugin::createItemsCsv().
	 *
	 * @throws ShopgateLibraryException
	 */
	public final function startGetItemsCsv() {
		$this->buffer->setFile($this->config->getItemsCsvPath());
		$this->createItemsCsv();
		$this->buffer->finish();
	}

	/**
	 * Takes care of buffer and file handlers and calls ShopgatePlugin::createCategoriesCsv().
	 *
	 * @throws ShopgateLibraryException
	 */
	public final function startGetCategoriesCsv() {
		$this->buffer->setFile($this->config->getCategoriesCsvPath());
		$this->createCategoriesCsv();
		$this->buffer->finish();
	}

	/**
	 * Takes care of buffer and file handlers and calls ShopgatePlugin::createReviewsCsv().
	 *
	 * @throws ShopgateLibraryException
	 */
	public final function startGetReviewsCsv() {
		$this->buffer->setFile($this->config->getReviewsCsvPath());
		$this->createReviewsCsv();
		$this->buffer->finish();
	}

	/**
	 * Takes care of buffer and file handlers and calls ShopgatePlugin::createPagesCsv().
	 *
	 * @throws ShopgateLibraryException
	 */
	public final function startGetPagesCsv() {
		$this->buffer->setFile($this->config->getReviewsCsvPath());
		$this->createPagesCsv();
		$this->buffer->finish();
	}
	
	
	#############
	## Helpers ##
	#############
		
	/**
	 * Calls the addRow() method on the currently associated ShopgateFileBuffer
	 *
	 * @param mixed[] $row
	 * @throws ShopgateLibraryException if flushing the buffer fails.
	 */
	private final function addRow($row) {
		$this->buffer->addRow($row);
	}
	
	/**
	 * @deprecated Use ShopgatePlugin::addItemRow(), ::addCategoryRow() or ::addReviewRow().
	 */
	protected final function addItem($item) {
		$this->addRow($item);
	}
	
	/**
	 * @param mixed[] $itemArr
	 */
	protected final function addItemRow($item) {
		$item = array_merge( $this->buildDefaultItemRow(), $item );
		
		$this->addRow( $item );
	}
	/**
	 * @param mixed[] $itemArr
	 */
	protected final function addCategoryRow($category) {
		$category = array_merge($this->buildDefaultCategoryRow(), $category);
		
		$this->addRow($category);
	}
	/**
	 * @param mixed[] $itemArr
	 */
	protected final function addReviewRow($review) {
		$review = array_merge($this->buildDefaultReviewRow(), $review);
		
		$this->addRow($review);
	}

	/**
	 * @return string[] An array with the csv file field names as indices and empty strings as values.
	 * @see http://wiki.shopgate.com/CSV_File_Categories/
	 */
	protected function buildDefaultCategoryRow() {
		$row = array(
			"category_number" => "",
			"parent_id" => "",
			"category_name" => "",
			"url_image" => "",
			"order_index" => "",
			"is_active" => 1,
			"url_deeplink" => ""
		);

		return $row;
	}

	/**
	 * @deprecated Use ShopgatePlugin::buildDefaultItemRow().
	 */
	protected function buildDefaultProductRow() {
		return $this->buildDefaultItemRow();
	}
	
	/**
	 * @return string[] An array with the csv file field names as indices and empty strings as values.
	 * @see http://wiki.shopgate.com/CSV_File_Items/
	 */
	protected function buildDefaultItemRow() {
		$row = array(
			/* responsible fields */
			'item_number' 				=> "",
			'item_name' 				=> "",
			'unit_amount'	 			=> "",
//			'unit_amount_net' 			=> "",
			'currency' 					=> "EUR",
			'tax_percent'				=> "",
//			'tax_class'					=> "",
			'description' 				=> "",
			'urls_images' 				=> "",
			'categories' 				=> "",
			'category_numbers'			=> "",
			'is_available' 				=> "1",
			'available_text' 			=> "",
			'manufacturer' 				=> "",
			'manufacturer_item_number' 	=> "",
			'url_deeplink' 				=> "",
			/* additional fields */
			'item_number_public'		=> "",
			'old_unit_amount'			=> "",
//			'old_unit_amount_net'		=> "",
			'properties'				=> "",
			'msrp' 						=> "",
			'shipping_costs_per_order' 	=> "0",
			'additional_shipping_costs_per_unit' => "0",
			'is_free_shipping'			=> "0",
			'basic_price' 				=> "",
			'use_stock' 				=> "0",
			'stock_quantity' 			=> "",
			'active_status'				=> self::PRODUCT_STATUS_STOCK,
			'minimum_order_quantity'	=> "0",
			'maximum_order_quantity'	=> "0",
			'minimum_order_amount'		=> "0.00",
			'ean' 						=> "",
			'isbn' 						=> "",
			'pzn'						=> "",
			'upc'						=> "",
			'last_update' 				=> "",
			'tags' 						=> "",
			'sort_order' 				=> "",
			'is_highlight'				=> "0",
			'highlight_order_index'		=> "0",
			'marketplace' 				=> "1",
			'internal_order_info' 		=> "",
			'related_shop_item_numbers' => "",
			'age_rating' 				=> "",
			'weight' 					=> "",
			'block_pricing' 			=> "",
			/* parent/child relationship */
			'has_children' 				=> "0",
			'parent_item_number' 		=> "",
			'attribute_1' 				=> "",
			'attribute_2' 				=> "",
			'attribute_3' 				=> "",
			'attribute_4' 				=> "",
			'attribute_5' 				=> "",
			'attribute_6' 				=> "",
			'attribute_7' 				=> "",
			'attribute_8' 				=> "",
			'attribute_9' 				=> "",
			'attribute_10' 				=> "",
			/* options */
			'has_options' 				=> "0",
			'option_1' 					=> "",
			'option_1_values' 			=> "",
			'option_2' 					=> "",
			'option_2_values' 			=> "",
			'option_3' 					=> "",
			'option_3_values' 			=> "",
			'option_4' 					=> "",
			'option_4_values' 			=> "",
			'option_5' 					=> "",
			'option_5_values' 			=> "",
			'option_6' 					=> "",
			'option_6_values' 			=> "",
			'option_7' 					=> "",
			'option_7_values' 			=> "",
			'option_8' 					=> "",
			'option_8_values' 			=> "",
			'option_9' 					=> "",
			'option_9_values' 			=> "",
			'option_10' 				=> "",
			'option_10_values' 			=> "",
			/* inputfields */
			'has_input_fields' 			=> "0",
			'input_field_1_number'		=> "",
			'input_field_1_type'		=> "",
			'input_field_1_label'		=> "",
			'input_field_1_infotext'	=> "",
			'input_field_1_required'	=> "",
			'input_field_1_add_amount'	=> "",
			'input_field_2_number'		=> "",
			'input_field_2_type'		=> "",
			'input_field_2_label'		=> "",
			'input_field_2_infotext'	=> "",
			'input_field_2_required'	=> "",
			'input_field_2_add_amount'	=> "",
			'input_field_3_number'		=> "",
			'input_field_3_type'		=> "",
			'input_field_3_label'		=> "",
			'input_field_3_infotext'	=> "",
			'input_field_3_required'	=> "",
			'input_field_3_add_amount'	=> "",
			'input_field_4_number'		=> "",
			'input_field_4_type'		=> "",
			'input_field_4_label'		=> "",
			'input_field_4_infotext'	=> "",
			'input_field_4_required'	=> "",
			'input_field_4_add_amount'	=> "",
			'input_field_5_number'		=> "",
			'input_field_5_type'		=> "",
			'input_field_5_label'		=> "",
			'input_field_5_infotext'	=> "",
			'input_field_5_required'	=> "",
			'input_field_5_add_amount'	=> "",
			'input_field_6_number'		=> "",
			'input_field_6_type'		=> "",
			'input_field_6_label'		=> "",
			'input_field_6_infotext'	=> "",
			'input_field_6_required'	=> "",
			'input_field_6_add_amount'	=> "",
			'input_field_7_number'		=> "",
			'input_field_7_type'		=> "",
			'input_field_7_label'		=> "",
			'input_field_7_infotext'	=> "",
			'input_field_7_required'	=> "",
			'input_field_7_add_amount'	=> "",
			'input_field_8_number'		=> "",
			'input_field_8_type'		=> "",
			'input_field_8_label'		=> "",
			'input_field_8_infotext'	=> "",
			'input_field_8_required'	=> "",
			'input_field_8_add_amount'	=> "",
			'input_field_9_number'		=> "",
			'input_field_9_type'		=> "",
			'input_field_9_label'		=> "",
			'input_field_9_infotext'	=> "",
			'input_field_9_required'	=> "",
			'input_field_9_add_amount'	=> "",
			'input_field_10_number'		=> "",
			'input_field_10_type'		=> "",
			'input_field_10_label'		=> "",
			'input_field_10_infotext'	=> "",
			'input_field_10_required'	=> "",
			'input_field_10_add_amount'	=> "",
		);

		return $row;
	}

	/**
	 * @return string[] An array with the csv file field names as indices and empty strings as values.
	 * @see http://wiki.shopgate.com/CSV_File_Reviews/
	 */
	protected function buildDefaultReviewRow() {
		$row = array(
			"item_number" => '',
			"update_review_id" => '',
			"score" => '',
			"name" => '',
			"date" => '',
			"title" => '',
			"text" => '',
		);

		return $row;
	}

	/**
	 * @see buildDefaultReviewRow
	 * @deprecated Use ShopgatePlugin::addReview().
	 */
	protected function buildDefaultReviewsRow() {
		return $this->buildDefaultReviewRow();
	}

	/**
	 * Rounds and formats a price.
	 *
	 * @param float $price The price of an item.
	 * @param int $digits The number of digits after the decimal separator.
	 * @param string $decimalPoint The decimal separator.
	 * @param string $thousandPoints The thousands separator.
	 */
	protected function formatPriceNumber($price, $digits = 2, $decimalPoint = ".", $thousandPoints = "") {
		$price = round($price, $digits);
		$price = number_format($price, $digits, $decimalPoint, $thousandPoints);
		return $price;
	}

	/**
	 * Removes all disallowed HTML tags from a given string.
	 *
	 * By default the following are allowed:
	 *
	 * "ADDRESS", "AREA", "A", "BASE", "BASEFONT", "BIG", "BLOCKQUOTE", "BODY", "BR",
	 * "B", "CAPTION", "CENTER", "CITE", "CODE", "DD", "DFN", "DIR", "DIV", "DL", "DT",
	 * "EM", "FONT", "FORM", "H1", "H2", "H3", "H4", "H5", "H6", "HEAD", "HR", "HTML",
	 * "ISINDEX", "I", "KBD", "LINK", "LI", "MAP", "MENU", "META", "OL", "OPTION", "PARAM", "PRE",
	 * "IMG", "INPUT", "P", "SAMP", "SELECT", "SMALL", "STRIKE", "STRONG", "STYLE", "SUB", "SUP",
	 * "TABLE", "TD", "TEXTAREA", "TH", "TITLE", "TR", "TT", "UL", "U", "VAR"
	 *
	 *
	 * @param string $string The input string to be filtered.
	 * @param string[] $removeTags The tags to be removed.
	 * @param string[] $additionalAllowedTags Additional tags to be allowed.
	 *
	 * @return string The sanititzed string.
	 */
	protected function removeTagsFromString($string, $removeTags = array(), $additionalAllowedTags = array()) {
		// all tags available
		$allowedTags = array("ADDRESS", "AREA", "A", "BASE", "BASEFONT", "BIG", "BLOCKQUOTE",
			"BODY", "BR", "B", "CAPTION", "CENTER", "CITE", "CODE", "DD", "DFN", "DIR", "DIV", "DL", "DT",
			"EM", "FONT", "FORM", "H1", "H2", "H3", "H4", "H5", "H6", "HEAD", "HR", "HTML", "IMG", "INPUT",
			"ISINDEX", "I", "KBD", "LINK", "LI", "MAP", "MENU", "META", "OL", "OPTION", "PARAM", "PRE",
			"P", "SAMP", "SELECT", "SMALL", "STRIKE", "STRONG", "STYLE", "SUB", "SUP",
			"TABLE", "TD", "TEXTAREA", "TH", "TITLE", "TR", "TT", "UL", "U", "VAR"
		);
		
		foreach ($allowedTags as &$t) $t = strtolower($t);
		foreach ($removeTags as &$t) $t = strtolower($t);
		foreach ($additionalAllowedTags as &$t) $t = strtolower($t);
		
		// some tags must be removed completely (including content)
		$string = preg_replace('#<script([^>]*?)>(.*?)</script>#is', '', $string);
		$string = preg_replace('#<style([^>]*?)>(.*?)</style>#is', '', $string);
		$string = preg_replace('#<link([^>]*?)>(.*?)</link>#is', '', $string);
		
		$string = preg_replace('#<script([^>]*?)/>#is', '', $string);
		$string = preg_replace('#<style([^>]*?)/>#is', '', $string);
		$string = preg_replace('#<link([^>]*?)/>#is', '', $string);

		// add the additional allowed tags to the list
		$allowedTags = array_merge($allowedTags, $additionalAllowedTags);

		// strip the disallowed tags from the list
		$allowedTags = array_diff($allowedTags, $removeTags);

		// add HTML brackets
		foreach ($allowedTags as &$t) $t = "<$t>";

		// let PHP sanitize the string and return it
		return strip_tags($string, implode(",", $allowedTags));
	}

	/**
	 *
	 * @param array $loaders
	 * @param array $shopgateItemArray
	 * @param mixed $dataObject or $dataArray to access
	 */
	protected final function executeLoaders(array $loaders/*, &$csvArray, $item[, ...]*/)
	{
		$arguments = func_get_args();
		array_shift($arguments);
	
		foreach ($loaders as $method) {
			if (method_exists($this, $method)) {
				$this->log("Call Function {$method}", ShopgateLogger::LOGTYPE_DEBUG);
				try {
					$result = call_user_func_array( array( $this, $method ), $arguments );
				} catch (Exception $e) {
					throw new ShopgateLibraryException("An exception has been thrown in loader method '{$method}'. Exception '".get_class($e)."': [Code: {$e->getCode()}] {$e->getMessage()}");
				}

 				if($result) {
 					// put back the result into argument-list (&$csvArray)
					$arguments[0] = $result;
 				}
			}
		}
		
		return $arguments[0];
	}
	
	/**
	 * Creates an array of corresponding helper method names, based on the export type given
	 * @param string $exportType
	 * @return array
	 */
	private final function getCreateCsvLoaders($subjectName) {
		$actions = array();
		$subjectName = trim($subjectName);
		if(!empty($subjectName)) {
			$methodName = 'buildDefault'.$this->camelize($subjectName, true).'Row';
			if(method_exists($this, $methodName)) {
				foreach(array_keys($this->{$methodName}() ) as $sKey) {
					$actions[] = $subjectName."Export" . $this->camelize($sKey, true);
				}
			}
		}
		
		return $actions;
	}
	
	/**
	 * Returns an array with the method names of all item-loaders
	 *
	 * Example: exportItemName, exportUnitAmount
	 *
	 * @return array
	 */
	protected function getCreateItemsCsvLoaders() {
		return $this->getCreateCsvLoaders("item");
	}
	
	/**
	 * Returns an array with the method names of all item-loaders
	 *
	 * Example: exportCategoryCategoryNumber, exportCategoryCategoryName
	 *
	 * @return array
	 */
	protected function getCreateCategoriesCsvLoaders() {
		return $this->getCreateCsvLoaders("category");
	}
	
	/**
	 * Returns an array with the method names of all item-loaders
	 *
	 * @return array
	 */
	protected function getCreateReviewsCsvLoaders() {
		return $this->getCreateCsvLoaders("review");
	}

	#################################################################################
	## Following methods are the callbacks that need to be implemented by plugins. ##
	#################################################################################

	/**
	 * Callback function for initialization by plugin implementations.
	 *
	 * This method gets called on instantiation of a ShopgatePlugin child class and serves as __construct() replacement.
	 *
	 * Important: Initialize $this->config here if you have your own config class.
	 *
	 * @see http://wiki.shopgate.com/Shopgate_Library#startup.28.29
	 */
	public abstract function startup();

	/**
	 * Executes a cron job with parameters.
	 *
	 * @param string $jobname The name of the job to execute.
	 * @param <string => mixed> $params Associative list of parameter names and values.
	 * @param string $message A reference to the variable the message is appended to.
	 * @param int $errorcount A reference to the error counter variable.
	 * @post $message contains a message of success or failure for the job.
	 * @post $errorcount contains the number of errors that occured during execution.
	 */
	public abstract function cron($jobname, $params, &$message, &$errorcount);

	/**
	 * Callback function for the Shopgate Plugin API ping action.
	 *
	 * Override this to append additional information about shop system to the response of the ping action.
	 *
	 * @return mixed[] An array with additional information.
	 */
	public function createPluginInfo() { return array(); }

	/**
	 * This performs the necessary queries to build a ShopgateCustomer object for the given log in credentials.
	 *
	 * The method should not abort on soft errors like when the street or phone number of a customer can't be found.
	 *
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_customer#API_Response
	 *
	 * @param string $user The user name the customer entered at Shopgate Connect.
	 * @param string $pass The password the customer entered at Shopgate Connect.
	 * @return ShopgateCustomer A ShopgateCustomer object.
	 * @throws ShopgateLibraryException on invalid log in data or hard errors like database failure.
	 */
	public abstract function getCustomer($user, $pass);

	/**
	 * Performs the necessary queries to add an order to the shop system's database.
	 *
	 * @see http://wiki.shopgate.com/Merchant_API_get_orders#API_Response
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_add_order#API_Response
	 *
	 * @param ShopgateOrder $order The ShopgateOrder object to be added to the shop system's database.
	 * @return array(
	 *          <ul>
	 *          	<li>'external_order_id' => <i>string</i>, # the ID of the order in your shop system's database</li>
	 *              <li>'external_order_number' => <i>string</i> # the number of the order in your shop system</li>
	 *          </ul>)
	 * @throws ShopgateLibraryException if an error occurs.
	 */
	public abstract function addOrder(ShopgateOrder $order);

	/**
	 * Performs the necessary queries to update an order in the shop system's database.
	 *
	 * @see http://wiki.shopgate.com/Merchant_API_get_orders#API_Response
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_update_order#API_Response
	 *
	 * @param ShopgateOrder $order The ShopgateOrder object to be updated in the shop system's database.
	 * @return array(
	 *          <ul>
	 *          	<li>'external_order_id' => <i>string</i>, # the ID of the order in your shop system's database</li>
	 *              <li>'external_order_number' => <i>string</i> # the number of the order in your shop system</li>
	 *          </ul>)
	 * @throws ShopgateLibraryException if an error occurs.
	 */
	public abstract function updateOrder(ShopgateOrder $order);
	
	/**
	 * Redeems coupons that are passed along with a ShopgateCart object.
	 *
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_redeem_coupons#API_Response
	 *
	 * @param ShopgateCart $cart The ShopgateCart object containing the coupons that should be redeemed.
	 * @return array('external_coupons' => ShopgateExternalCoupon[])
	 * @throws ShopgateLibraryException if an error occurs.
	 */
	public abstract function redeemCoupons(ShopgateCart $cart);
	
	/**
	 * Checks the content of a cart to be valid and returns necessary changes if applicable.
	 *
	 * This currently only supports the validation of coupons.
	 *
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_check_cart#API_Response
	 *
	 * @param ShopgateCart $cart The ShopgateCart object to be checked and validated.
	 * @return array(
	 *          <ul>
	 *          	<li>'external_coupons' => ShopgateExternalCoupon[], # list of all coupons</li>
	 *          	<li>'items' => array(...), # list of item changes (not supported yet)</li>
	 *          	<li>'shippings' => array(...), # list of available shipping services for this cart (not supported yet)</li>
	 *          </ul>)
	 * @throws ShopgateLibraryException if an error occurs.
	 */
	public abstract function checkCart(ShopgateCart $cart);
	
	/**
	 * Returns an array of certain settings of the shop. (Currently mainly tax settings.)
	 *
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_settings#API_Response
	 *
	 * @return array(
	 *          <ul>
	 *						<li>'tax' => Contains the tax settings as follows:
	 *							<ul>
	 *								<li>'tax_classes_products' => A list of product tax class identifiers.</li>
	 *								<li>'tax_classes_customers' => A list of customer tax classes.</li>
	 *								<li>'tax_rates' => A list of tax rates.</li>
	 *								<li>'tax_rules' => A list of tax rule containers.</li>
	 *							</ul>
	 *						</li>
	 *          </ul>)
	 * @throws ShopgateLibraryException on invalid log in data or hard errors like database failure.
	 */
	public abstract function getSettings();
	
	/**
	 * Loads the products of the shop system's database and passes them to the buffer.
	 *
	 * If $this->splittedExport is set to "true", you MUST regard $this->offset and $this->limit when fetching items from the database.
	 *
	 * Use ShopgatePlugin::buildDefaultItemRow() to get the correct indices for the field names in a Shopgate items csv and
	 * use ShopgatePlugin::addItemRow() to add it to the output buffer.
	 *
	 * @see http://wiki.shopgate.com/CSV_File_Items
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_items_csv
	 *
	 * @throws ShopgateLibraryException
	 */
	protected abstract function createItemsCsv();

	/**
	 * Loads the product categories of the shop system's database and passes them to the buffer.
	 *
	 * Use ShopgatePlugin::buildDefaultCategoryRow() to get the correct indices for the field names in a Shopgate categories csv and
	 * use ShopgatePlugin::addCategoryRow() to add it to the output buffer.
	 *
	 * @see http://wiki.shopgate.com/CSV_File_Categories
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_categories_csv
	 *
	 * @throws ShopgateLibraryException
	 */
	protected abstract function createCategoriesCsv();

	/**
	 * Loads the product reviews of the shop system's database and passes them to the buffer.
	 *
	 * Use ShopgatePlugin::buildDefaultReviewRow() to get the correct indices for the field names in a Shopgate reviews csv and
	 * use ShopgatePlugin::addReviewRow() to add it to the output buffer.
	 *
	 * @see http://wiki.shopgate.com/CSV_File_Reviews
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_reviews_csv
	 *
	 * @throws ShopgateLibraryException
	 */
	protected abstract function createReviewsCsv();

	/**
	 * Loads the product pages of the shop system's database and passes them to the buffer.
	 *
	 * @throws ShopgateLibraryException
	 */
	//protected abstract function getPagesCsv();
}

interface ShopgateFileBufferInterface {
	/**
	 * Creates a new write buffer for the file under "$filePath.tmp".
	 *
	 * @param string $filePath Path to the file (the .tmp extension is added automatically).
	 */
	public function setFile($filePath);
	
	/**
	 * Adds a line / row to the csv file buffer.
	 *
	 * @param mixed[] $row
	 * @throws ShopgateLibraryException if flushing the buffer fails.
	 */
	public function addRow($row);
	
	/**
	 * Closes the file and flushes the buffer.
	 *
	 * @throws ShopgateLibraryException if the buffer and file are empty.
	 */
	public function finish();
}

class ShopgateFileBuffer extends ShopgateObject implements ShopgateFileBufferInterface {
	/**
	 * @var string[]
	 */
	private $allowedEncodings = array(
			SHOPGATE_LIBRARY_ENCODING, 'ASCII', 'CP1252', 'ISO-8859-15', 'UTF-16LE','ISO-8859-1'
	);
	
	/**
	 * @var bool true to enable automatic encoding conversion to utf-8
	 */
	protected $convertEncoding;

	/**
	 * @var int (timestamp) time of the first call of addItem()
	 */
	protected $timeStart;
	
	/**
	 * @var string
	 */
	protected $filePath;
	
	/**
	 * @var resource
	 */
	protected $fileHandle;

	/**
	 * @var mixed[]
	 */
	protected $buffer;

	/**
	 * @var int
	 */
	protected $capacity;

	/**
	 * Creates the buffer object.
	 *
	 * The object is NOT ready to use. Call setFile() first to associate it with a file first.
	 *
	 * @param int $capacity
	 * @param bool $encoding true to enable automatic encoding conversion to utf-8
	 */
	public function __construct($capacity, $convertEncoding = true) {
		$this->timeStart = time();
		$this->buffer = array();
		$this->capacity = $capacity;
		$this->convertEncoding = $convertEncoding;
	}

	public function setFile($filePath) {
		$this->filePath = $filePath;
		$this->buffer = array();
		
		if (empty($this->fileHandle)) {
			$filePath = $this->filePath.".tmp";
			$this->log('Trying to create "'.basename($filePath).'". ', 'access');
			
			$this->fileHandle = @fopen($filePath, 'w');
			if (!$this->fileHandle) {
				throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_FILE_OPEN_ERROR, 'File: '.$filePath);
			}
		}
	}
	
	public function addRow($row) {
		$this->buffer[] = $row;

		if (count($this->buffer) > $this->capacity) {
			$this->flush();
		}
	}
	
	/**
	 * Flushes buffer to the currently opened file handle in $this->fileHandle.
	 *
	 * The data is converted to utf-8 if mb_convert_encoding() exists.
	 *
	 * @throws ShopgateLibraryException if the buffer and file are empty.
	 */
	protected function flush() {
		if (empty($this->buffer) && ftell($this->fileHandle) == 0) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_FILE_EMPTY_BUFFER);
		}
		
		// write headline if it's the beginning of the file
		if (ftell($this->fileHandle) == 0) {
			fputcsv($this->fileHandle, array_keys($this->buffer[0]), ';', '"');
		}

		foreach ($this->buffer as $item) {
			if (!empty($this->convertEncoding)) {
				foreach ($item as &$field) {
					$field = $this->stringToUtf8($field, $this->allowedEncodings);
				}
			}

			fputcsv($this->fileHandle, $item, ";", "\"");
		}

		$this->buffer = array();
	}

	public function finish() {
		$this->flush();
		fclose($this->fileHandle);
		$this->fileHandle = null;
		
		// FIX for Windows Servers
		if(file_exists($this->filePath)) {
			unlink($this->filePath);
		}
		rename($this->filePath.".tmp", $this->filePath);
		
		$this->log('Fertig, '.basename($this->filePath).' wurde erfolgreich erstellt', "access");
		$duration = time() - $this->timeStart;
		$this->log("Dauer: $duration Sekunden", "access");
	}
}

/**
 * This class provides basic functionality for the Shopgate Library's container objects.
 *
 * It provides initialization with an array, conversion to an array, utf-8 decoding of the container's properties etc.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
abstract class ShopgateContainer extends ShopgateObject {
	/**
	 * Initializes the object with the passed data.
	 *
	 * If no data is passed, an empty object is created. The passed data must be an array, it's indices must be the un-camelized,
	 * underscored names of the set* methods of the created object.
	 *
	 * @param array $data The data the container should be initialized with.
	 */
	public function __construct($data = array()) {
		$this->loadArray($data);
	}
	
	/**
	 * Tries to map an associative array to the object's attributes.
	 *
	 * The passed data must be an array, it's indices must be the un-camelized,
	 * underscored names of the set* methods of the object.
	 *
	 * Tha data that couldn't be mapped is returned as an array.
	 *
	 * @param array<string, mixed> $data The data that should be mapped to the container object.
	 * @return array<string, mixed> The part of the array that couldn't be mapped.
	 */
	public function loadArray(array $data = array()) {
		$unmappedData = array();
		
		if (is_array($data)) {
			$methods = get_class_methods($this);
			foreach ($data as $key => $value) {
				$setter = 'set'.$this->camelize($key, true);
				if (!in_array($setter, $methods)) {
					$unmappedData[$key] = $value;
					continue;
				}
				$this->$setter($value);
			}
		}
		
		return $unmappedData;
	}

	/**
	 * Converts the Container object recursively to an associative array.
	 *
	 * @return mixed[]
	 */
	public function toArray() {
 		$visitor = new ShopgateContainerToArrayVisitor();
 		$visitor->visitContainer($this);
 		return $visitor->getArray();
	}

	/**
	 * Creates a new object of the same type with every value recursively utf-8 encoded.
	 *
	 * @param String $sourceEncoding The source Encoding of the strings
	 * @param bool $force Set this true to enforce encoding even if the source encoding is already UTF-8.
	 * @return ShopgateContainer The new object with utf-8 encoded values.
	 */
	public function utf8Encode($sourceEncoding = 'ISO-8859-15', $force = false) {
		$visitor = new ShopgateContainerUtf8Visitor(ShopgateContainerUtf8Visitor::MODE_ENCODE, $sourceEncoding, $force);
		$visitor->visitContainer($this);
		return $visitor->getObject();
	}

	/**
	 * Creates a new object of the same type with every value recursively utf-8 decoded.
	 *
	 * @param String $destinationEncoding The destination Encoding for the strings
	 * @param bool $force Set this true to enforce encoding even if the destination encoding is set to UTF-8.
	 * @return ShopgateContainer The new object with utf-8 decoded values.
	 */
	public function utf8Decode($destinationEncoding = 'ISO-8859-15', $force = false) {
		$visitor = new ShopgateContainerUtf8Visitor(ShopgateContainerUtf8Visitor::MODE_DECODE, $destinationEncoding, $force);
		$visitor->visitContainer($this);
		return $visitor->getObject();
	}

	/**
	 * Creates an array of all properties that have getters.
	 *
	 * @return mixed[]
	 */
	public function buildProperties() {
		$methods = get_class_methods($this);
		$properties = get_object_vars($this);
		$filteredProperties = array();

		// only properties that have getters should be extracted
		foreach ($properties as $property => $value) {
			$getter = 'get'.$this->camelize($property, true);
			if (in_array($getter, $methods)) {
				$filteredProperties[$property] = $this->{$getter}();
			}
		}

		return $filteredProperties;
	}

	/**
	 * @param ShopgateContainerVisitor $v
	 */
	public abstract function accept(ShopgateContainerVisitor $v);
}

/**
 * Interface for visitors of ShopgateContainer objects.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
interface ShopgateContainerVisitor {
	public function visitContainer(ShopgateContainer $c);
	public function visitCustomer(ShopgateCustomer $c);
	public function visitAddress(ShopgateAddress $a);
	public function visitCart(ShopgateCart $c);
	public function visitOrder(ShopgateOrder $o);
	public function visitOrderItem(ShopgateOrderItem $i);
	public function visitOrderItemOption(ShopgateOrderItemOption $o);
	public function visitOrderItemInput(ShopgateOrderItemInput $i);
	public function visitOrderItemAttribute(ShopgateOrderItemAttribute $o);
	public function visitOrderShipping(ShopgateShippingInfo $o);
	public function visitOrderDeliveryNote(ShopgateDeliveryNote $d);
	public function visitExternalCoupon(ShopgateExternalCoupon $c);
	public function visitShopgateCoupon(ShopgateShopgateCoupon $c);
	public function visitCategory(ShopgateCategory $d);
	public function visitItem(ShopgateItem $i);
	public function visitItemOption(ShopgateItemOption $i);
	public function visitItemOptionValue(ShopgateItemOptionValue $i);
	public function visitItemInput(ShopgateItemInput $i);
	public function visitConfig(ShopgateConfig $c);
}

/**
 * Creates a new object with every value inside utf-8 de- / encoded.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgateContainerUtf8Visitor implements ShopgateContainerVisitor {
	const MODE_ENCODE = 1;
	const MODE_DECODE = 2;

	protected $firstObject;
	protected $object;
	protected $mode;
	protected $encoding;
	protected $force;

	/**
	 * @param int $mode Set mode to one of the two class constants. Default is MODE_DECODE.
	 * @param string $encoding The source or destination encoding according to PHP's mb_convert_encoding().
	 * @param bool $force Set this true to enforce encoding even if the source or destination encoding is UTF-8.
	 * @see http://www.php.net/manual/en/function.mb-convert-encoding.php
	 */
	public function __construct($mode = self::MODE_DECODE, $encoding = 'ISO-8859-15', $force = false) {
		switch ($mode) {
			// default mode
			default: $mode = self::MODE_DECODE;

			// allowed modes
			case self::MODE_ENCODE: case self::MODE_DECODE:
				$this->mode = $mode;
			break;
		}
		$this->encoding = $encoding;
		$this->force = $force;
	}

	/**
	 * @return ShopgateContainer the utf-8 de- / encoded newly built object.
	 */
	public function getObject() {
		return $this->object;
	}

	public function visitContainer(ShopgateContainer $c) {
		// this is awkward but we need an object as a workaround to call the stringTo/FromUtf8 methods of ShopgateObject
		$this->firstObject = &$c;
		$c->accept($this);
	}

	public function visitCustomer(ShopgateCustomer $c) {
		// get properties
		$properties = $c->buildProperties();

		// iterate the simple variables
		$this->iterateSimpleProperties($properties);

		// iterate ShopgateAddress objects
		$properties['addresses'] = $this->iterateObjectList($properties['addresses']);

		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateCustomer($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitAddress(ShopgateAddress $a) {
		$properties = $a->buildProperties();
		$this->iterateSimpleProperties($properties);

		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateAddress($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitCart(ShopgateCart $c) {
		// get properties
		$properties = $c->buildProperties();
	
		// iterate the simple variables and arrays with simple variables recursively
		$this->iterateSimpleProperties($properties);
	
		// visit delivery_address
		if (!empty($properties['delivery_address']) && ($properties['delivery_address'] instanceof ShopgateAddress)) {
			$properties['delivery_address']->accept($this);
			$properties['delivery_address'] = $this->object;
		}
	
		// visit invoice_address
		if (!empty($properties['invoice_address']) && ($properties['invoice_address'] instanceof ShopgateAddress)) {
			$properties['invoice_address']->accept($this);
			$properties['invoice_address'] = $this->object;
		}

		// iterate lists of referred objects
		$properties['external_coupons'] = $this->iterateObjectList($properties['external_coupons']);
		$properties['shopgate_coupons'] = $this->iterateObjectList($properties['shopgate_coupons']);
		$properties['items'] = $this->iterateObjectList($properties['items']);
	
		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateCart($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitOrder(ShopgateOrder $o) {
		// get properties
		$properties = $o->buildProperties();

		// iterate the simple variables and arrays with simple variables recursively
		$this->iterateSimpleProperties($properties);

		// visit delivery_address
		if (!empty($properties['delivery_address']) && ($properties['delivery_address'] instanceof ShopgateAddress)) {
			$properties['delivery_address']->accept($this);
			$properties['delivery_address'] = $this->object;
		}

		// visit invoice_address
		if (!empty($properties['invoice_address']) && ($properties['invoice_address'] instanceof ShopgateAddress)) {
			$properties['invoice_address']->accept($this);
			$properties['invoice_address'] = $this->object;
		}
		
		// visit shipping_infos
		if (!empty($properties['shipping_infos']) && ($properties['shipping_infos'] instanceof ShopgateShippingInfo)) {
			$properties['shipping_infos']->accept($this);
			$properties['shipping_infos'] = $this->object;
		}

		// iterate lists of referred objects
		$properties['external_coupons'] = $this->iterateObjectList($properties['external_coupons']);
		$properties['shopgate_coupons'] = $this->iterateObjectList($properties['shopgate_coupons']);
		$properties['items'] = $this->iterateObjectList($properties['items']);
		$properties['delivery_notes'] = $this->iterateObjectList($properties['delivery_notes']);

		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateOrder($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitOrderItem(ShopgateOrderItem $i) {
		// get properties
		$properties = $i->buildProperties();

		// iterate the simple variables
		$this->iterateSimpleProperties($properties);

		// iterate lists of referred objects
		$properties['options'] = $this->iterateObjectList($properties['options']);
		$properties['inputs'] = $this->iterateObjectList($properties['inputs']);

		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateOrderItem($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitOrderItemOption(ShopgateOrderItemOption $o) {
		$properties = $o->buildProperties();
		$this->iterateSimpleProperties($properties);

		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateOrderItemOption($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitOrderItemInput(ShopgateOrderItemInput $i) {
		$properties = $i->buildProperties();
		$this->iterateSimpleProperties($properties);
		
		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateOrderItemInput($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitOrderItemAttribute(ShopgateOrderItemAttribute $i) {
		$properties = $i->buildProperties();
		$this->iterateSimpleProperties($properties);
		
		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateOrderItemAttribute($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}
	
	public function visitOrderShipping(ShopgateShippingInfo $o) {
		$properties = $o->buildProperties();
		$this->iterateSimpleProperties($properties);
		
		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateShippingInfo($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitOrderDeliveryNote(ShopgateDeliveryNote $d) {
		$properties = $d->buildProperties();
		$this->iterateSimpleProperties($properties);

		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateDeliveryNote($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}
	
	public function visitExternalCoupon(ShopgateExternalCoupon $c) {
		$properties = $c->buildProperties();

		// iterate the simple variables
		$this->iterateSimpleProperties($properties);

		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateExternalCoupon($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}
	
	public function visitShopgateCoupon(ShopgateShopgateCoupon $c) {
		$properties = $c->buildProperties();
		
		// iterate the simple variables
		$this->iterateSimpleProperties($properties);
		
		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateShopgateCoupon($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}
	
	public function visitCategory(ShopgateCategory $c) {
		$properties = $c->buildProperties();

		// iterate the simple variables
		$this->iterateSimpleProperties($properties);

		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateCategory($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitItem(ShopgateItem $i) {
		$properties = $i->buildProperties();

		// iterate the simple variables
		$this->iterateSimpleProperties($properties);

		// iterate the item options and inputs
		$properties['options'] = $this->iterateObjectList($properties['options']);
		$properties['inputs'] = $this->iterateObjectList($properties['inputs']);

		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateItem($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitItemOption(ShopgateItemOption $i) {
		$properties = $i->buildProperties();

		// iterate the simple variables
		$this->iterateSimpleProperties($properties);

		// iterate the item option values
		$properties['option_values'] = $this->iterateObjectList($properties['option_values']);

		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateItemOption($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitItemOptionValue(ShopgateItemOptionValue $i) {
		$properties = $i->buildProperties();

		// iterate the simple variables
		$this->iterateSimpleProperties($properties);

		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateOptionValue($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitItemInput(ShopgateItemInput $i) {
		$properties = $i->buildProperties();

		// iterate the simple variables
		$this->iterateSimpleProperties($properties);

		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateItemInput($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}
	
	public function visitConfig(ShopgateConfig $c) {
		$properties = $c->buildProperties();

		// iterate the simple variables
		$this->iterateSimpleProperties($properties);

		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateConfig($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	protected function iterateSimpleProperties(array &$properties) {
		foreach ($properties as $key => &$value) {
			if (empty($value)) continue;

			// we only want the simple types
			if (is_object($value)) continue;

			// iterate through arrays recursively
			if (is_array($value)) {
				$this->iterateSimpleProperties($value);
				continue;
			}

			// perform encoding / decoding on simple types
			switch ($this->mode) {
				case self::MODE_ENCODE: $value = $this->firstObject->stringToUtf8($value, $this->encoding, $this->force); break;
				case self::MODE_DECODE: $value = $this->firstObject->stringFromUtf8($value, $this->encoding, $this->force); break;
			}
		}
	}

	protected function iterateObjectList($list = null) {
		$newList = array();

		if (!empty($list) && is_array($list)) {
			foreach ($list as $object) {
				if (!($object instanceof ShopgateContainer)) {
					ShopgateLogger::getInstance()->log('Encountered unknown type in what is supposed to be a list of ShopgateContainer objects: '.var_export($object, true));
					continue;
				}

				$object->accept($this);
				$newList[] = $this->object;
			}
		}

		return $newList;
	}
}

/**
 * Turns a ShopgateContainer or an array of ShopgateContainers into an array.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgateContainerToArrayVisitor implements ShopgateContainerVisitor {
	protected $array;

	/**
	 * mixed[] The array-turned object
	 */
	public function getArray() {
		return $this->array;
	}

	public function visitContainer(ShopgateContainer $c) {
		$c->accept($this);
	}

	public function visitCustomer(ShopgateCustomer $c) {
		// get properties
		$properties = $c->buildProperties();

		// iterate the simple variables
		$properties = $this->iterateSimpleProperties($properties);

		// iterate ShopgateAddress objects
		$properties['addresses'] = $this->iterateObjectList($properties['addresses']);

		// set last value to converted array
		$this->array = $properties;
	}

	public function visitAddress(ShopgateAddress $a) {
		// get properties and iterate (no complex types in ShopgateAddress objects)
		$this->array = $this->iterateSimpleProperties($a->buildProperties());
	}

	public function visitCart(ShopgateCart $c) {
			// get properties
		$properties = $c->buildProperties();
	
		// iterate the simple variables and arrays with simple variables recursively
		$this->iterateSimpleProperties($properties);
	
		// visit delivery_address
		if (!empty($properties['delivery_address']) && ($properties['delivery_address'] instanceof ShopgateAddress)) {
			$properties['delivery_address']->accept($this);
			$properties['delivery_address'] = $this->array;
		}
	
		// visit invoice_address
		if (!empty($properties['invoice_address']) && ($properties['invoice_address'] instanceof ShopgateAddress)) {
			$properties['invoice_address']->accept($this);
			$properties['invoice_address'] = $this->array;
		}

		// iterate lists of referred objects
		$properties['external_coupons'] = $this->iterateObjectList($properties['external_coupons']);
		$properties['shopgate_coupons'] = $this->iterateObjectList($properties['shopgate_coupons']);
		$properties['items'] = $this->iterateObjectList($properties['items']);
	
		$this->array = $properties;
	}

	public function visitOrder(ShopgateOrder $o) {
		// get properties
		$properties = $o->buildProperties();

		// iterate the simple variables
		$properties = $this->iterateSimpleProperties($properties);

		// visit invoice address
		if (!empty($properties['invoice_address']) && ($properties['invoice_address'] instanceof ShopgateAddress)) {
			$properties['invoice_address']->accept($this);
			$properties['invoice_address'] = $this->array;
		}

		// visit delivery address
		if (!empty($properties['delivery_address']) && ($properties['delivery_address'] instanceof ShopgateAddress)) {
			$properties['delivery_address']->accept($this);
			$properties['delivery_address'] = $this->array;
		}
		
		// visit shipping info
		if (!empty($properties['shipping_infos']) && ($properties['shipping_infos'] instanceof ShopgateShippingInfo)) {
			$properties['shipping_infos']->accept($this);
			$properties['shipping_infos'] = $this->array;
		}

		// visit the items and delivery notes arrays
		$properties['external_coupons'] = $this->iterateObjectList($properties['external_coupons']);
		$properties['shopgate_coupons'] = $this->iterateObjectList($properties['shopgate_coupons']);
		$properties['items'] = $this->iterateObjectList($properties['items']);
		$properties['delivery_notes'] = $this->iterateObjectList($properties['delivery_notes']);

		// set last value to converted array
		$this->array = $properties;
	}

	public function visitOrderItem(ShopgateOrderItem $i) {
		// get properties
		$properties = $i->buildProperties();

		// iterate the simple variables
		$properties = $this->iterateSimpleProperties($properties);

		// iterate ShopgateAddress objects
		$properties['options'] = $this->iterateObjectList($properties['options']);
		$properties['inputs'] = $this->iterateObjectList($properties['inputs']);

		// set last value to converted array
		$this->array = $properties;
	}

	public function visitOrderItemOption(ShopgateOrderItemOption $o) {
		// get properties and iterate (no complex types in ShopgateOrderItemOption objects)
		$this->array = $this->iterateSimpleProperties($o->buildProperties());
	}

	public function visitOrderItemInput(ShopgateOrderItemInput $i) {
		// get properties and iterate (no complex types in ShopgateOrderItemInput objects)
		$this->array = $this->iterateSimpleProperties($i->buildProperties());
	}

	public function visitOrderItemAttribute(ShopgateOrderItemAttribute $i) {
		// get properties and iterate (no complex types in ShopgateOrderItemAttribute objects)
		$this->array = $this->iterateSimpleProperties($i->buildProperties());
	}
	
	public function visitOrderShipping(ShopgateShippingInfo $i) {
		// get properties and iterate (no complex types in ShopgateOrderItemAttribute objects)
		$this->array = $this->iterateSimpleProperties($i->buildProperties());
	}

	public function visitOrderDeliveryNote(ShopgateDeliveryNote $d) {
		// get properties and iterate (no complex types in ShopgateDeliveryNote objects)
		$this->array = $this->iterateSimpleProperties($d->buildProperties());
	}

	public function visitExternalCoupon(ShopgateExternalCoupon $c) {
		// get properties and iterate (no complex types in ShopgateExternalCoupon objects)
		$this->array = $this->iterateSimpleProperties($c->buildProperties());
	}
	
	public function visitShopgateCoupon(ShopgateShopgateCoupon $c) {
		// get properties and iterate (no complex types in ShopgateShopgateCoupon objects)
		$this->array = $this->iterateSimpleProperties($c->buildProperties());
	}
	
	public function visitCategory(ShopgateCategory $d) {
		$this->array = $this->iterateSimpleProperties($d->buildProperties());
	}

	public function visitItem(ShopgateItem $i) {
		// get properties
		$properties = $i->buildProperties();

		// iterate the simple variables
		$properties = $this->iterateSimpleProperties($properties);

		// iterate ShopgateAddress objects
		$properties['options'] = $this->iterateObjectList($properties['options']);
		$properties['inputs'] = $this->iterateObjectList($properties['inputs']);

		// set last value to converted array
		$this->array = $properties;
	}

	public function visitItemOption(ShopgateItemOption $i) {
		// get properties
		$properties = $i->buildProperties();

		// iterate the simple variables
		$properties = $this->iterateSimpleProperties($properties);

		// iterate item option values
		$properties['option_values'] = $this->iterateObjectList($properties['option_values']);

		// set last value to converted array
		$this->array = $properties;
	}

	public function visitItemOptionValue(ShopgateItemOptionValue $i) {
		$this->array = $this->iterateSimpleProperties($i->buildProperties());
	}

	public function visitItemInput(ShopgateItemInput $d) {
		// get properties and iterate (no complex types in ShopgateDeliveryNote objects)
		$this->array = $this->iterateSimpleProperties($d->buildProperties());
	}

	public function visitConfig(ShopgateConfig $c) {
		$properties = $this->iterateSimpleProperties($c->buildProperties());
		$additionalSettings = $this->iterateSimpleProperties($c->returnAdditionalSettings());
		$this->array = array_merge($properties, $additionalSettings);
	}

	protected function iterateSimpleProperties(array $properties) {
		foreach ($properties as $key => &$value) {
			if (empty($value)) continue;

			// we only want the simple types
			if (is_object($value)) continue;

			// iterate through arrays recursively
			if (is_array($value)) {
				$this->iterateSimpleProperties($value);
				continue;
			}

			$value = $this->sanitizeSimpleVar($value);
		}

		return $properties;
	}

	protected function iterateObjectList($list = null) {
		$newList = array();

		if (!empty($list) && is_array($list)) {
			foreach ($list as $object) {
				if (!($object instanceof ShopgateContainer)) {
					ShopgateLogger::getInstance()->log('Encountered unknown type in what is supposed to be a list of ShopgateContainer objects: '.var_export($object, true));
					continue;
				}

				$object->accept($this);
				$newList[] = $this->array;
			}
		}

		return $newList;
	}

	protected function sanitizeSimpleVar($v) {
		if (is_bool($v)) {
			return (int) $v;
		} else {
			return $v;
		}
	}
}