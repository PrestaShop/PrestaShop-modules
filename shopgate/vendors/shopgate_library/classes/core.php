<?php
/**
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
 * @author Shopgate GmbH <interfaces@shopgate.com>
 */

###################################################################################
# define constants
###################################################################################
define('SHOPGATE_LIBRARY_VERSION', '2.9.10');
define('SHOPGATE_LIBRARY_ENCODING' , 'UTF-8');
define('SHOPGATE_BASE_DIR', realpath(dirname(__FILE__).'/../'));

function shopgateGetErrorType($type) {
	switch($type) {
		case E_ERROR: // 1 //
			return 'E_ERROR';
		case E_WARNING: // 2 //
			return 'E_WARNING';
		case E_PARSE: // 4 //
			return 'E_PARSE';
		case E_NOTICE: // 8 //
			return 'E_NOTICE';
		case E_CORE_ERROR: // 16 //
			return 'E_CORE_ERROR';
		case E_CORE_WARNING: // 32 //
			return 'E_CORE_WARNING';
		case E_COMPILE_ERROR: // 64 //
			return 'E_COMPILE_ERROR';
		case E_COMPILE_WARNING: // 128 //
			return 'E_COMPILE_WARNING';
		case E_USER_ERROR: // 256 //
			return 'E_USER_ERROR';
		case E_USER_WARNING: // 512 //
			return 'E_USER_WARNING';
		case E_USER_NOTICE: // 1024 //
			return 'E_USER_NOTICE';
		case E_STRICT: // 2048 //
			return 'E_STRICT';
		case E_RECOVERABLE_ERROR: // 4096 //
			return 'E_RECOVERABLE_ERROR';
		case E_DEPRECATED: // 8192 //
			return 'E_DEPRECATED';
		case E_USER_DEPRECATED: // 16384 //
			return 'E_USER_DEPRECATED';
	}
	
	return "UNKWOWN_ERROR_CODE";
}

/**
 * register shutdown handler
 * @see http://de1.php.net/manual/en/function.register-shutdown-function.php
 */
function ShopgateShutdownHandler(){

	if (function_exists("error_get_last")){
		if (!is_null($e = error_get_last())) {
			$type = shopgateGetErrorType($e['type']);
			ShopgateLogger::getInstance()->log("{$e['message']} \n {$e['file']} : [{$e['line']}] , Type: {$type}", ShopgateLogger::LOGTYPE_ERROR);
		}
	}
}

/**
 * Error handler for PHP errors.
 *
 * To use the Shopgate error handler it must be activated in your configuration.
 *
 * @param int $errno
 * @param string $errstr
 * @param string $errfile
 * @param int $errline
 * @param array $errContext
 * @return boolean
 * @see http://php.net/manual/en/function.set-error-handler.php
 */
function ShopgateErrorHandler($errno, $errstr, $errfile, $errline, $errContext) {
	
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
	$msg .= (isset($errContext["printStackTrace"]) && $errContext["printStackTrace"]) ?  "\n". print_r(debug_backtrace(false), true) : "";
	
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
	
	const PLUGIN_API_INVALID_ACTION = 25;
	const PLUGIN_API_ADMIN_LOGIN_REQUIRED = 26;
	
	const PLUGIN_API_NO_ORDER_NUMBER = 30;
	const PLUGIN_API_NO_CART = 31;
	const PLUGIN_API_NO_AUTHORIZATION_CODE = 33;
	const PLUGIN_API_NO_USER = 35;
	const PLUGIN_API_NO_PASS = 36;
	const PLUGIN_API_NO_USER_DATA = 37;
	const PLUGIN_API_UNKNOWN_LOGTYPE = 38;
	const PLUGIN_API_CRON_NO_JOBS = 40;
	const PLUGIN_API_CRON_NO_JOB_NAME = 41;
	const PLUGIN_API_NO_ITEMS = 42;
	const PLUGIN_API_WRONG_ITEM_FORMAT = 43;
	const PLUGIN_API_NO_SHOPGATE_SETTINGS = 50;
	const PLUGIN_API_UNSUPPORTED_RESPONSE_TYPE = 51;

	// Plugin errors
	const PLUGIN_DUPLICATE_ORDER = 60;
	const PLUGIN_ORDER_NOT_FOUND = 61;
	const PLUGIN_NO_CUSTOMER_GROUP_FOUND = 62;
	const PLUGIN_ORDER_ITEM_NOT_FOUND = 63;
	const PLUGIN_ORDER_STATUS_IS_SENT = 64;
	const PLUGIN_ORDER_ALREADY_UP_TO_DATE = 65;
	const PLUGIN_REGISTER_CUSTOMER_ERROR = 66;
	
	const PLUGIN_NO_ADDRESSES_FOUND = 70;
	const PLUGIN_WRONG_USERNAME_OR_PASSWORD = 71;

	const PLUGIN_NO_CUSTOMER_TOKEN = 72;
	const PLUGIN_CUSTOMER_TOKEN_INVALID = 73;
	const PLUGIN_NO_CUSTOMER_LANGUAGE = 74;

	const PLUGIN_CUSTOMER_ACCOUNT_NOT_CONFIRMED = 75;
	const PLUGIN_CUSTOMER_UNKNOWN_ERROR = 76;
	const PLUGIN_MISSING_ACCOUNT_PERMISSIONS = 77;
	
	const PLUGIN_FILE_DELETE_ERROR = 79;
	const PLUGIN_FILE_NOT_FOUND = 80;
	const PLUGIN_FILE_OPEN_ERROR = 81;
	const PLUGIN_FILE_EMPTY_BUFFER = 82;
	const PLUGIN_DATABASE_ERROR = 83;
	const PLUGIN_UNKNOWN_COUNTRY_CODE = 84;
	const PLUGIN_UNKNOWN_STATE_CODE = 85;
	
	const PLUGIN_EMAIL_SEND_ERROR = 90;
	
	const PLUGIN_CRON_UNSUPPORTED_JOB = 91;
	
	// Merchant API errors
	const MERCHANT_API_NO_CONNECTION = 100;
	const MERCHANT_API_INVALID_RESPONSE = 101;
	const MERCHANT_API_ERROR_RECEIVED = 102;
	
	// OAuth errors
	const SHOPGATE_OAUTH_NO_CONNECTION = 115;
	const SHOPGATE_OAUTH_MISSING_ACCESS_TOKEN = 116;
	
	// Authentication errors
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
	
	const REGISTER_FAILED_TO_ADD_USER = 220;
	const REGISTER_USER_ALREADY_EXISTS = 221;
	
	// Cart Item Errors
	const CART_ITEM_OUT_OF_STOCK = 300;
	const CART_ITEM_PRODUCT_NOT_FOUND = 301;
	const CART_ITEM_REQUESTED_QUANTITY_NOT_AVAILABLE = 302;
	const CART_ITEM_INPUT_VALIDATION_FAILED = 303;
	const CART_ITEM_REQUESTED_QUANTITY_UNDER_MINIMUM_QUANTITY = 304;
	const CART_ITEM_REQUESTED_QUANTITY_OVER_MAXIMUM_QUANTITY = 305;
	
	//Helper class exception
	const SHOPGATE_HELPER_FUNCTION_NOT_FOUND_EXCEPTION = 310;
	
	// extended error code format that contains information on multiple errors
	const MULTIPLE_ERRORS = 998;
	
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
		
		self::PLUGIN_API_INVALID_ACTION => 'invalid action call',
		self::PLUGIN_API_ADMIN_LOGIN_REQUIRED => 'login/access rights required',
		
		self::PLUGIN_API_NO_ORDER_NUMBER => 'parameter "order_number" missing',
		self::PLUGIN_API_NO_CART => 'parameter "cart" missing',
		self::PLUGIN_API_NO_USER => 'parameter "user" missing',
		self::PLUGIN_API_NO_PASS => 'parameter "pass" missing',
		self::PLUGIN_API_NO_USER_DATA => 'parameter "user_data" missing',
		self::PLUGIN_API_UNKNOWN_LOGTYPE => 'unknown logtype',
		self::PLUGIN_API_CRON_NO_JOBS => 'parameter "jobs" missing',
		self::PLUGIN_API_CRON_NO_JOB_NAME => 'field "job_name" in parameter "jobs" missing',
		self::PLUGIN_API_NO_ITEMS => 'parameter "items" missing',
		self::PLUGIN_API_WRONG_ITEM_FORMAT => 'wrong item format',
		self::PLUGIN_API_NO_SHOPGATE_SETTINGS => 'parameter "shopgate_settings" missing',
		self::PLUGIN_API_UNSUPPORTED_RESPONSE_TYPE => 'parameter "response_type" contains an unsupported type',

		// Plugin errors
		self::PLUGIN_DUPLICATE_ORDER => 'duplicate order',
		self::PLUGIN_ORDER_NOT_FOUND => 'order not found',
		self::PLUGIN_NO_CUSTOMER_GROUP_FOUND => 'no customer group found for customer',
		self::PLUGIN_ORDER_ITEM_NOT_FOUND => 'order item not found',
		self::PLUGIN_ORDER_STATUS_IS_SENT => 'order status is "sent"',
		self::PLUGIN_ORDER_ALREADY_UP_TO_DATE => 'order is already up to date',
		self::PLUGIN_REGISTER_CUSTOMER_ERROR => 'error while registering new customer',
		
		self::PLUGIN_NO_ADDRESSES_FOUND => 'no addresses found for customer',
		self::PLUGIN_WRONG_USERNAME_OR_PASSWORD => 'wrong username or password',
			
		self::PLUGIN_NO_CUSTOMER_TOKEN => 'customer token missing',
		self::PLUGIN_CUSTOMER_TOKEN_INVALID => 'invalid customer token',
		self::PLUGIN_NO_CUSTOMER_LANGUAGE => 'customer language missing',
			
		self::PLUGIN_CUSTOMER_ACCOUNT_NOT_CONFIRMED => 'customer account not confirmed',
		self::PLUGIN_CUSTOMER_UNKNOWN_ERROR => 'unknown error while customer login',
		self::PLUGIN_MISSING_ACCOUNT_PERMISSIONS => 'missing account permissions',
		
		self::PLUGIN_FILE_DELETE_ERROR => 'cannot delete file(s)',
		self::PLUGIN_FILE_NOT_FOUND => 'file not found',
		self::PLUGIN_FILE_OPEN_ERROR => 'cannot open file',
		self::PLUGIN_FILE_EMPTY_BUFFER => 'buffer is empty',
		self::PLUGIN_DATABASE_ERROR => 'database error',
		self::PLUGIN_UNKNOWN_COUNTRY_CODE => 'unknown country code',
		self::PLUGIN_UNKNOWN_STATE_CODE => 'unknown state code',
		
		self::PLUGIN_EMAIL_SEND_ERROR => 'error sending email',
		
		self::PLUGIN_CRON_UNSUPPORTED_JOB => 'unsupported job',
		
		// Merchant API errors
		self::MERCHANT_API_NO_CONNECTION => 'no connection to server',
		self::MERCHANT_API_INVALID_RESPONSE => 'error parsing response',
		self::MERCHANT_API_ERROR_RECEIVED => 'error code received',

		// OAuth errors
		self::SHOPGATE_OAUTH_NO_CONNECTION => 'no connection to shopgate server',
		self::SHOPGATE_OAUTH_MISSING_ACCESS_TOKEN => 'no oauth access token received',
		
		// File errors
		self::FILE_READ_WRITE_ERROR => 'error reading or writing file',
		
		// Coupon Errors
		self::COUPON_NOT_VALID => 'invalid coupon',
		self::COUPON_CODE_NOT_VALID => 'invalid coupon code',
		self::COUPON_INVALID_PRODUCT => 'invalid product for coupon',
		self::COUPON_INVALID_ADDRESS => 'invalid address for coupon',
		self::COUPON_INVALID_USER => 'invalid user for coupon',
		self::COUPON_TOO_MANY_COUPONS => 'too many coupons in cart',
		
		self::REGISTER_FAILED_TO_ADD_USER => 'failed to add user',
		self::REGISTER_USER_ALREADY_EXISTS => 'the given username already exists',

		// Cart Item Errors
		self::CART_ITEM_OUT_OF_STOCK => 'product is not in stock',
		self::CART_ITEM_PRODUCT_NOT_FOUND => 'product not found',
		self::CART_ITEM_REQUESTED_QUANTITY_NOT_AVAILABLE => 'less stock available than requested',
		self::CART_ITEM_INPUT_VALIDATION_FAILED => 'cart item input validation failed',
		
		// Authentication errors
		self::AUTHENTICATION_FAILED => 'authentication failed',
		
		self::MULTIPLE_ERRORS => '',
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
	 * @param bool $appendAdditionalInformationToMessage Set true to output the additional information to the response. Set false to log it silently.
	 * @param bool $writeLog true to create a log entry in the error log, false otherwise.
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

		if ($appendAdditionalInformationToMessage){
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

		// in case of multiple errors the message should not have any other text attached to it
		if ($code == self::MULTIPLE_ERRORS) {
			$message = $additionalInformation;
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
	 * @return string
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
	 * @return string
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
			$line = (isset($btrace[$i]['line'])) ? $btrace[$i]['line'] : 'Unknown line';
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
	const UNKNOWN_ACTION = 101;
	const SHOP_NUMBER_NOT_SET = 103;
	const MERCHANT_NOT_FOUND = 110;
	const SHOP_NOT_FOUND = 113;
	const MISSING_PARAMETERS = 118;
	const FIELDS_OF_WRONG_TYPE = 123;
	const AUTHORIZATION_NOT_SET = 400;
	const AUTHORIZATION_USERNAME_INVALID = 401;
	const AUTHORIZATION_PASSWORD_INVALID = 402;
	
	const ORDER_NOT_FOUND = 201;
	const ORDER_ON_HOLD = 202;
	const ORDER_ALREADY_COMPLETED = 203;
	const ORDER_SHIPPING_STATUS_ALREADY_COMPLETED = 204;
	const ORDER_INVALID_SHIPPING_SERVICE_ID = 119;
	
	const CATEGORY_NAME_ALREADY_EXISTS = 142;
	const CATEGORY_NOT_FOUND = 205;
	const CATEGORY_PARENT_NOT_FOUND = 212;
	const CATEGORY_NUMBER_ALREADY_EXISTS = 216;
	const CATEGORY_CHILD_ITEMS_COULD_NOT_BE_MAPPED = 217;
	const CATEGORY_CANNOT_MAP_CHILD_ITEMS = 217;
	const CATEGORY_ERROR_DELETING_ITEM_MAPPING = 147;
	
	const ITEM_ERROR_DOWNLOADING_IMAGE = 149;
	const ITEM_NOT_FOUND = 206;
	const ITEM_CURRENCY_NOT_FOUND = 207;
	const ITEM_PARENT_NOT_FOUND = 220;
	const ITEM_ALREADY_EXISTS = 221;
	const ITEM_ERROR_DELETING = 129;
	
	const BATCH_ITEM_MULTIPLE_ERRORS = 166;
	const BATCH_ITEM_TOO_MANY_ELEMENTS = 167;
	
	
	const ORDER_ALREADY_CANCELLED = 222;
	const ORDER_CANCEL_INVALID_ITEM = 223;
	const ORDER_CANCEL_INVALID_ITEM_QUANTITY = 224;
	const ORDER_SHIPPING_COSTS_ALREADY_CANCELLED = 225;
	const ORDER_CANCEL_INVALID_ARGUMENTS = 226;
	
	const INTERNAL_ERROR_OCCURED_WHILE_SAVING = 803;
	const INTERNAL_ERROR_OCCURED_WHILE_DELETING = 804;
	
	const UNKNOWN_ERROR = 999;
	
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
	 * @var string
	 */
	private $memoryAnalyserLoggingSizeUnit;
	
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
		$this->memoryAnalyserLoggingSizeUnit = 'MB';
		$this->obfuscationFields = array('pass');
		$this->removeFields = array('cart');
	}
	
	
	/**
	 * @param string $accessLogPath
	 * @param string $requestLogPath
	 * @param string $errorLogPath
	 * @param string $debugLogPath
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
	 * Sets the unit in which the memory usage logger outputs its values in
	 * @param string $sizeUnit ('MB', 'BYTES', 'GB', 'KB', ...)
	 */
	public function setMemoryAnalyserLoggingSizeUnit($sizeUnit) {
		switch(strtoupper(trim($sizeUnit))) {
			case 'GB':
			case 'GIGABYTE':
			case 'GIGABYTES':
				$this->memoryAnalyserLoggingSizeUnit = 'GB';
				break;
			case 'MB':
			case 'MEGABYTE':
			case 'MEGABYTES':
				$this->memoryAnalyserLoggingSizeUnit = 'MB';
				break;
			case 'KB':
			case 'KILOBYTE':
			case 'KILOBYTES':
				$this->memoryAnalyserLoggingSizeUnit = 'KB';
				break;
// 			case 'BYTES':
// 			case 'BYTE':
			default:
				$this->memoryAnalyserLoggingSizeUnit = 'BYTES';
				break;
		}
	}
	
	/**
	 * returns the unit in which the memory usage logger outputs its values in
	 * @return string
	 */
	public function getMemoryAnalyserLoggingSizeUnit() {
		return $this->memoryAnalyserLoggingSizeUnit;
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
	 * @throws ShopgateLibraryException
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
		// -> MerchantAPI auth service (needs to be initialized first, since the config still can change along with the authentication information
		switch($this->config->getSmaAuthServiceClassName()) {
			case ShopgateConfigInterface::SHOPGATE_AUTH_SERVICE_CLASS_NAME_SHOPGATE:
				$smaAuthService = new ShopgateAuthenticationServiceShopgate($this->config->getCustomerNumber(), $this->config->getApikey());
				$smaAuthService->setup($this->config);
				$merchantApi = new ShopgateMerchantApi($smaAuthService, $this->config->getShopNumber(), $this->config->getApiUrl());
				break;
			case ShopgateConfigInterface::SHOPGATE_AUTH_SERVICE_CLASS_NAME_OAUTH:
				$smaAuthService = new ShopgateAuthenticationServiceOAuth($this->config->getOauthAccessToken());
				$smaAuthService->setup($this->config);
				$merchantApi = new ShopgateMerchantApi($smaAuthService, null, $this->config->getApiUrl());
				break;
			default:
				// undefined auth service
				trigger_error('Invalid SMA-Auth-Service defined - this should not happen with valid plugin code', E_USER_ERROR);
				break;
		}
		// -> PluginAPI auth service (currently the plugin API supports only one auth service)
		$spaAuthService = new ShopgateAuthenticationServiceShopgate($this->config->getCustomerNumber(), $this->config->getApikey());
		$pluginApi = new ShopgatePluginApi($this->config, $spaAuthService, $merchantApi, $plugin);
		
		// instantiate export file buffer
		if (!empty($_REQUEST['action']) && (($_REQUEST['action'] == 'get_items')
				|| ($_REQUEST['action'] == 'get_categories') || ($_REQUEST['action'] == 'get_reviews'))) {
			$xmlModelNames = array(
					'get_items' => 'Shopgate_Model_Catalog_Product',
					'get_categories' => 'Shopgate_Model_Catalog_Category',
					'get_reviews' => 'Shopgate_Model_Review'
			);
			
			if ($this->config->getExportConvertEncoding()) {
				array_splice(Shopgate_Model_AbstractExport::$allowedEncodings, 1, 0, $this->config->getEncoding());
				Shopgate_Model_AbstractExport::$allowedEncodings = array_unique(Shopgate_Model_AbstractExport::$allowedEncodings);
			}

			$format = (!empty($_REQUEST['response_type'])) ? $_REQUEST['response_type'] : '';
			switch ($format) {
				default: case 'xml':
					/* @var $xmlModel Shopgate_Model_AbstractExport */
					$xmlModel = new $xmlModelNames[$_REQUEST['action']]();
					$xmlNode = new Shopgate_Model_XmlResultObject($xmlModel->getItemNodeIdentifier());
					$fileBuffer = new ShopgateFileBufferXml($xmlModel, $xmlNode, $this->config->getExportBufferCapacity(), $this->config->getExportConvertEncoding(), $this->config->getEncoding());
				break;
				
				case 'json':
					$fileBuffer = new ShopgateFileBufferJson($this->config->getExportBufferCapacity(), $this->config->getExportConvertEncoding(), $this->config->getEncoding());
				break;
			}
		} else if (!empty($_REQUEST['action']) && (($_REQUEST['action'] == 'get_items_csv') || ($_REQUEST['action'] == 'get_categories_csv') || ($_REQUEST['action'] == 'get_reviews_csv'))) {
			$fileBuffer = new ShopgateFileBufferCsv($this->config->getExportBufferCapacity(), $this->config->getExportConvertEncoding(), $this->config->getEncoding());
		} else {
			$fileBuffer = new ShopgateFileBufferCsv($this->config->getExportBufferCapacity(), $this->config->getExportConvertEncoding(), $this->config->getEncoding());
		}
		
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
		$merchantApi = null;
		switch($smaAuthServiceClassName = $this->config->getSmaAuthServiceClassName()) {
			case ShopgateConfigInterface::SHOPGATE_AUTH_SERVICE_CLASS_NAME_SHOPGATE:
				$smaAuthService = new ShopgateAuthenticationServiceShopgate($this->config->getCustomerNumber(), $this->config->getApikey());
				$smaAuthService->setup($this->config);
				$merchantApi = new ShopgateMerchantApi($smaAuthService, $this->config->getShopNumber(), $this->config->getApiUrl());
				break;
			case ShopgateConfigInterface::SHOPGATE_AUTH_SERVICE_CLASS_NAME_OAUTH:
				$smaAuthService = new ShopgateAuthenticationServiceOAuth($this->config->getOauthAccessToken());
				$smaAuthService->setup($this->config);
				$merchantApi = new ShopgateMerchantApi($smaAuthService, null, $this->config->getApiUrl());
				break;
			default:
				// undefined auth service
				trigger_error('Invalid SMA-Auth-Service defined - this should not happen with valid plugin code', E_USER_ERROR);
				break;
		}
		
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
	 * @var array cache already camelized strings
	 */
	protected $camelizeCache = array();

	/**
	 * defines the name for the Shopgate datastructure helper
	 * @const HELPER_DATA_STRUCTURE
	 */
	const HELPER_DATASTRUCTURE = "DataStructure";

	/**
	 * defines the name for the Shopgate pricing helper
	 * @const HELPER_PRICING
	 */
	const HELPER_PRICING 		= "Pricing";

	/**
	 * defines the name for the Shopgate string helper
	 * @const HELPER_STRING
	 */
	const HELPER_STRING 		= "String";

	/**
	 * Save the already instantiated Helper Object to guarantee the only one instance is allocated
	 * 
	 * @var array of Shopgate_Helper_DataStructure|Shopgate_Helper_Pricing|Shopgate_Helper_String
	 */
	private $helperClassInstances = array(
		self::HELPER_DATASTRUCTURE 	=> null,
		self::HELPER_PRICING 		=> null,
		self::HELPER_STRING 		=> null,
	);

	/**
	 * get a instance of an Shopgate helper class depending on the committed name
	 *
	 * @param $helperName string defined by constants in this class(ShopgateObject)
	 *
	 * @return null|Shopgate_Helper_DataStructure|Shopgate_Helper_Pricing|Shopgate_Helper_String returns the requested helper instance or null
	 * @throws ShopgateLibraryException
	 */
	protected function getHelper($helperName){
		if(array_key_exists($helperName,$this->helperClassInstances)) {
			$helperClassName = "Shopgate_Helper_" . $helperName;
			if (!isset($this->helperClassInstances[$helperClassName])) {
				$this->helperClassInstances[$helperClassName] = new $helperClassName();
			}
			return $this->helperClassInstances[$helperClassName];
		}
		throw new ShopgateLibraryException("Helper function {$helperName} not found",ShopgateLibraryException::SHOPGATE_HELPER_FUNCTION_NOT_FOUND_EXCEPTION);
	}
	
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
		$hash = md5($str . $capitalizeFirst);
		if (empty($this->camelizeCache[$hash])) {
			$str = strtolower($str);
			if ($capitalizeFirst) {
				$str[0] = strtoupper($str[0]);
			}

			$this->camelizeCache[$hash] = preg_replace_callback('/_([a-z0-9])/', array($this, 'camelizeHelper'), $str);
		}

		return $this->camelizeCache[$hash];
	}
	
	private function camelizeHelper($matches) {
		return strtoupper($matches[1]);
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
			return json_encode($value);
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
	 * @param $json
	 * @param bool $assoc
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
	 * @param bool $useIconv True to use iconv instead of mb_convert_encoding even if the mb library is present.
	 * @return string The UTF-8 encoded string.
	 */
	public function stringToUtf8($string, $sourceEncoding = 'ISO-8859-15', $force = false, $useIconv = false) {
		$conditions =
			is_string($sourceEncoding) &&
			($sourceEncoding == SHOPGATE_LIBRARY_ENCODING) &&
			!$force;
		
		return ($conditions)
			? $string
			: $this->convertEncoding($string, SHOPGATE_LIBRARY_ENCODING, $sourceEncoding, $useIconv);
	}

	/**
	 * Decodes a string from UTF-8 to a given encoding.
	 *
	 * @param string $string The string to decode.
	 * @param string $destinationEncoding The desired encoding of the return value.
	 * @param bool $force Set this true to enforce encoding even if the destination encoding is set to UTF-8.
	 * @param bool $useIconv True to use iconv instead of mb_convert_encoding even if the mb library is present.
	 * @return string The UTF-8 decoded string.
	 */
	public function stringFromUtf8($string, $destinationEncoding = 'ISO-8859-15', $force = false, $useIconv = false) {
		return ($destinationEncoding == SHOPGATE_LIBRARY_ENCODING) && !$force
				? $string
				: $this->convertEncoding($string, $destinationEncoding, SHOPGATE_LIBRARY_ENCODING, $useIconv);
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
	 * @param bool $useIconv True to use iconv instead of mb_convert_encoding even if the mb library is present.
	 * @return string The UTF-8 decoded string.
	 */
	protected function convertEncoding($string, $destinationEncoding, $sourceEncoding, $useIconv = false) {
		if (function_exists('mb_convert_encoding') && !$useIconv) {
			$convertedString = mb_convert_encoding($string, $destinationEncoding, $sourceEncoding);
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
			
			$convertedString = @iconv($sourceEncoding, $destinationEncoding.'//IGNORE', $string);
		}
		
		return $convertedString;
	}

	/**
	 * Takes any big object that can contain recursion and dumps it to the output buffer
	 *
	 * @param mixed $subject
	 * @param array $ignore
	 * @param int $depth
	 * @param array $refChain
	 */
	protected function user_print_r($subject, $ignore = array(), $depth = 1, $refChain = array()){
		static $maxDepth = 5;
		if ($depth > 20) return;
		if (is_object($subject)) {
			foreach ($refChain as $refVal)
			if ($refVal === $subject) {
				echo "*RECURSION*\n";
				return;
			}
			array_push($refChain, $subject);
			echo get_class($subject) . " Object ( \n";
			$subject = (array) $subject;
			foreach ($subject as $key => $val)
			if (is_array($ignore) && !in_array($key, $ignore, 1)) {
				echo str_repeat(" ", $depth * 4) . '[';
				if ($key{0} == "\0") {
					$keyParts = explode("\0", $key);
					echo $keyParts[2] . (($keyParts[1] == '*')  ? ':protected' : ':private');
				} else
					echo $key;
				echo '] => ';
				if($depth == $maxDepth){
					return;
				}
				$this->user_print_r($val, $ignore, $depth + 1, $refChain);
			}
			echo str_repeat(" ", ($depth - 1) * 4) . ")\n";
			array_pop($refChain);
		} elseif (is_array($subject)) {
			echo "Array ( \n";
			foreach ($subject as $key => $val) {
				if (is_array($ignore) && !in_array($key, $ignore, 1)) {
					echo str_repeat(" ", $depth * 4) . '[' . $key . '] => ';
					if($depth==$maxDepth){
						return;
					}
					$this->user_print_r($val, $ignore, $depth + 1, $refChain);
				}
			}
			echo str_repeat(" ", ($depth - 1) * 4) . ")\n";
		} else {
			echo $subject . "\n";
		}
	}

	/**
	 * @param int $memoryLimit in MB
	 */
	public function setExportMemoryLimit($memoryLimit) {
		if(!empty($memoryLimit) && is_int($memoryLimit)){
			@ini_set('memory_limit', "{$memoryLimit}M");
		}
	}

	/**
	 * @param int $timeLimit in seconds
	 */
	public function setExportTimeLimit($timeLimit) {

		if(!empty($timeLimit) && (is_int($timeLimit)||is_float($timeLimit))){
			@set_time_limit($timeLimit);
			@ini_set('max_execution_time', $timeLimit);
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
	 * @var Shopgate_Model_Abstract
	 */
	protected $result_item_model = false;

	/** convert weight units **/
	const CONVERT_POUNDS_TO_GRAM_FACTOR = 453.59237;
	const CONVERT_OUNCES_TO_GRAM_FACTOR = 28.3495231;
	
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
	 * @var int the number of attributes in the item csv file header
	 */
	protected $defaultItemRowAttributeCount = 10;
	
	/**
	 * @var int the number of options in the item csv file header
	 */
	protected $defaultItemRowOptionCount = 10;
	
	/**
	 * @var int the number of inputs in the item csv file header
	 */
	protected $defaultItemRowInputCount = 10;
	
	/**
	 *
	 * @var boolean true use tax classes for export
	 */
	protected $useTaxClasses = false;
	
	
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
	 * @param ShopgateFileBufferInterface $buffer
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
	 * Wrapper method to fetch OAuth url from ShopgatePluginApi
	 *
	 * @param string $shopgateOAuthActionName
	 * @return string
	 */
	public function buildShopgateOAuthUrl($shopgateOAuthActionName) {
		return $this->pluginApi->buildShopgateOAuthUrl($shopgateOAuthActionName);
	}

	/**
	 * Checks the config for every 'enabled_<action-name>' setting and returns all active as an indexed list
	 * @return array
	 */
	public function getEnabledPluginActions() {
		$enabledActionsList = array();
		
		$configValues = $this->config->toArray();
		
		// find all settings that start with "enable_" in the config-value-name and collect all active ones
		$searchKeyPart = 'enable_';
		foreach($configValues as $key => $val) {
			if(substr($key, 0, strlen($searchKeyPart)) == $searchKeyPart) {
				if($val) {
					$enabledActionsList[$key] = $val;
				}
			}
		}
		
		return $enabledActionsList;
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
	 * Takes care of buffer and file handlers and calls ShopgatePlugin::createItemsCsv().
	 *
	 * @throws ShopgateLibraryException
	 */
	public final function startGetMediaCsv() {
		$this->buffer->setFile($this->config->getMediaCsvPath());
		$this->createMediaCsv();
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
	 * @param int $limit
	 * @param int $offset
	 * @param array $uids
	 * @param string $responseType
	 */
	public final function startGetItems($limit = null, $offset = null, array $uids = array(), $responseType = 'xml') {
		switch ($responseType) {
			default: case 'xml':
				$this->buffer->setFile($this->config->getItemsXmlPath());
				break;
				
			case 'json':
				$this->buffer->setFile($this->config->getItemsJsonPath());
				break;
		}

		$this->createItems($limit, $offset, $uids);
		$this->buffer->finish();
	}

	/**
	 * Takes care of buffer and file handlers and calls ShopgatePlugin::createCategories().
	 *
	 * @param int $limit
	 * @param int $offset
	 * @param array $uids
	 * @param string $responseType
	 */
	public final function startGetCategories($limit = null, $offset = null, array $uids = array(), $responseType = 'xml') {
		switch ($responseType) {
			default: case 'xml':
				$this->buffer->setFile($this->config->getCategoriesXmlPath());
				break;
				
			case 'json':
				$this->buffer->setFile($this->config->getCategoriesJsonPath());
				break;
		}

		$this->createCategories($limit, $offset, $uids);
		$this->buffer->finish();
	}

	/**
	 * Takes care of buffer and file handlers and calls ShopgatePlugin::createReviews().
	 *
	 * @param int $limit
	 * @param int $offset
	 * @param array $uids
	 * @param string $responseType
	 */
	public final function startGetReviews($limit = null, $offset = null, array $uids = array(), $responseType = 'xml') {
		switch ($responseType) {
			default:
			case 'xml':
				$this->buffer->setFile($this->config->getReviewsXmlPath());
				break;

			case 'json':
				$this->buffer->setFile($this->config->getReviewsJsonPath());
				break;
		}

		$this->createReviews($limit, $offset, $uids);
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
	 * Calls the addRow() method on the currently associated ShopgateFileBuffer
	 *
	 * @param Shopgate_Model_AbstractExport $object
	 * @throws ShopgateLibraryException if flushing the buffer fails.
	 */
	private final function addModel(Shopgate_Model_AbstractExport $object) {
		$this->buffer->addRow($object);
	}

	/**
	 * @deprecated Use ShopgatePlugin::addItemRow(), ::addCategoryRow() or ::addReviewRow().
	 */
	protected final function addItem($item) {
		$this->addRow($item);
	}
	
	/**
	 * @param Shopgate_Model_Catalog_Product $item
	 */
	protected final function addItemModel(Shopgate_Model_Catalog_Product $item) {
		$this->addModel($item);
	}

	/**
	 * @param mixed[] $item
	 */
	protected final function addItemRow($item) {
		$item = array_merge( $this->buildDefaultItemRow(), $item );
		
		$this->addRow( $item );
	}

	/**
	 * @param mixed[] $item
	 */
	protected final function addMediaRow($item) {
		$item = array_merge( $this->buildDefaultMediaRow(), $item );
		
		$this->addRow( $item );
	}

	/**
	 * @param Shopgate_Model_Catalog_Category $category
	 */
	protected final function addCategoryModel(Shopgate_Model_Catalog_Category $category) {
		$this->addModel($category);
	}
	
	/**
	 * @param mixed[] $category
	 */
	protected final function addCategoryRow($category) {
		$category = array_merge($this->buildDefaultCategoryRow(), $category);
		
		$this->addRow($category);
	}

	/**
	 * @param Shopgate_Model_Review $review
	 */
	protected final function addReviewModel(Shopgate_Model_Review $review) {
		$this->addModel($review);
	}

	/**
	 * @param mixed[] $review
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
	 * @return Shopgate_Model_Catalog_Category
	 * @see http://wiki.shopgate.com/get_categories
	 */
	protected function buildDefaultCategoryModel() {
		return new Shopgate_Model_Catalog_Category();
	}

	/**
	 * set the number of attributes to put in the csv head row
	 * @param int $attributeCount
	 */
	protected function setDefaultItemRowAttributeCount($attributeCount=10) {
		$this->defaultItemRowAttributeCount = max(1, $attributeCount);
	}
	
	/**
	 * get the number of attributes to put in the csv head row
	 * @return int
	 */
	protected function getDefaultItemRowAttributeCount() {
		return $this->defaultItemRowAttributeCount;
	}
	
	/**
	 * set the number of options to put in the csv head row
	 * @param int $optionCount
	 */
	protected function setDefaultItemRowOptionCount($optionCount=10) {
		$this->defaultItemRowOptionCount = max(1, $optionCount);
	}
	
	/**
	 * get the number of options to put in the csv head row
	 * @return int
	 */
	protected function getDefaultItemRowOptionCount() {
		return $this->defaultItemRowOptionCount;
	}
	
	/**
	 * set the number of inputs to put in the csv head row
	 * @param int $inputCount
	 */
	protected function setDefaultItemRowInputCount($inputCount=10) {
		$this->defaultItemRowInputCount = max(1, $inputCount);
	}
	
	/**
	 * get the number of inputs to put in the csv head row
	 * @return int
	 */
	protected function getDefaultItemRowInputCount() {
		return $this->defaultItemRowInputCount;
	}
	
	/**
	 * @deprecated Use ShopgatePlugin::buildDefaultItemRow().
	 */
	protected function buildDefaultProductRow() {
		return $this->buildDefaultItemRow();
	}
	
	/**
	 *
	 * @param boolean tax classes will be used
	 * @see http://wiki.shopgate.com/CSV_File_Items/
	 */
	protected function useTaxClasses(){
		$this->useTaxClasses = true;
	}
	
	/**
	 * @return string[] An array with the csv file field names as indices and empty strings as values.
	 * @see http://wiki.shopgate.com/CSV_File_Items/
	 */
	protected function buildDefaultItemRow() {

		// prepare attributes
		$attributes = array(
			'has_children' => '0',
			'parent_item_number' => ''
		);
		for ($attr = 1; $attr <= $this->defaultItemRowAttributeCount; $attr++) {
			$attributes['attribute_' . $attr] = '';
		}
		
		// prepare options
		$options = array('has_options' => '0');
		for ($opt = 1; $opt <= $this->defaultItemRowOptionCount; $opt++) {
			$options['option_' . $opt] = '';
			$options['option_' . $opt . '_values'] = '';
		}
		
		// prepare inputs
		$inputs = array('has_input_fields' => '0');
		for ($inp = 1; $inp <= $this->defaultItemRowInputCount; $inp++) {
			$inputs['input_field_' . $inp . '_number'] = '';
			$inputs['input_field_' . $inp . '_type'] = '';
			$inputs['input_field_' . $inp . '_label'] = '';
			$inputs['input_field_' . $inp . '_infotext'] = '';
			$inputs['input_field_' . $inp . '_required'] = '';
			$inputs['input_field_' . $inp . '_add_amount'] = '';
		}

		
		
		$rowHead = array(
			/* responsible fields */
			'item_number' 				=> "",
			'item_name' 				=> "",
		);
		
		if($this->useTaxClasses){
			$tax = array(
				'unit_amount_net' 			=> "",
				'tax_class'					=> "",
				'old_unit_amount_net'		=> "",
			);
		}else{
			$tax = array(
				'unit_amount'	 			=> "",
				'tax_percent'				=> "",
				'old_unit_amount'			=> "",
				
			);
		}
		
		$rowBody = array('currency'	=> "EUR",
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
			'related_shop_items'		=> "",
			'age_rating' 				=> "",
			'weight' 					=> "",
			'block_pricing' 			=> "",
			'weight_unit'				=> "",
			'is_hidden'					=> "",
			/* parent/child relationship */
		) ;
		
		$row =
			$rowHead +
			$tax +
			$rowBody+
			$attributes +
			$options +
			$inputs;
		
		return $row;
	}

	/**
	 * @return Shopgate_Model_Catalog_Product
	 * @see http://wiki.shopgate.com/get_items
	 */
	protected function buildDefaultItemModel() {
		return new Shopgate_Model_Catalog_Product();
	}

	/**
	 * @return string[] An array with the csv file field names as indices and empty strings as values.
	 * @see http://wiki.shopgate.com/CSV_File_Media
	 */
	protected function buildDefaultMediaRow() {
		return array(
			'item_number' => '',
			'type'		=>'',
			'parameter' => array(),
		);
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
	protected function removeTagsFromString($string, $removeTags = array(), $additionalAllowedTags = array())
	{
		$helper = $this->getHelper(self::HELPER_STRING);
		return $helper->removeTagsFromString($string, $removeTags, $additionalAllowedTags);	
	}

	/**
	 * Rounds and formats a price.
	 *
	 * @param float $price The price of an item.
	 * @param int $digits The number of digits after the decimal separator.
	 * @param string $decimalPoint The decimal separator.
	 * @param string $thousandPoints The thousands separator.
	 * @return float|string
	 */
	protected function formatPriceNumber($price, $digits = 2, $decimalPoint = ".", $thousandPoints = "")
	{
		$helper = $this->getHelper(self::HELPER_PRICING);
		return $helper->formatPriceNumber($price, $digits, $decimalPoint, $thousandPoints);
	}

	/**
	 * Takes an array of arrays that contain all elements which are taken to create a cross-product of all elements. The resulting array is an array-list with
	 * each possible combination as array. An Element itself can be anything (including a whole array that is not torn apart, but instead treated as a whole)
	 * By setting the second parameter to true, the keys of the source array is added as an array at the front of the resulting array
	 *
	 * Sample input: array(
	 * 		'group-1-key' => array('a', 'b'),
	 * 		'group-2-key' => array('x'),
	 * 		7 => array('l', 'm', 'n'),
	 * );
	 * Output of sample: Array (
	 * 		[0] => Array (
	 * 			[group-1-key] => a
	 * 			[group-2-key] => x
	 * 			[7] => l
	 * 		)
	 * 		[1] => Array (
	 * 			[group-1-key] => b
	 * 			[group-2-key] => x
	 * 			[7] => l
	 * 		)
	 * 		[2] => Array (
	 * 			[group-1-key] => a
	 * 			[group-2-key] => x
	 * 			[7] => m
	 * 		)
	 * 		[...] and so on ... (total of count(src[0])*count(src[1])*...*count(src[N]) elements) [=> 2*1*3 elements in this case]
	 * 	)
	 *
	 * @param array $src: The (at least) double dimensioned array input
	 * @param bool $enableFirstRow: Disabled by default
	 * @return array[][]:
	 */
	protected function arrayCross(array $src, $enableFirstRow = false)
	{
		$helper = $this->getHelper(self::HELPER_DATASTRUCTURE);
		return $helper->arrayCross($src, $enableFirstRow);
	}
	
	/**
	 * @param array $loaders
	 * @return mixed
	 * @throws ShopgateLibraryException
	 */
	protected final function executeLoaders(array $loaders)
	{
		$arguments = func_get_args();
		array_shift($arguments);

		foreach ($loaders as $method) {
			if (method_exists($this, $method)) {
				$this->log("Calling function \"{$method}\": Actual memory usage before method: " . $this->getMemoryUsageString(), ShopgateLogger::LOGTYPE_DEBUG);
				try {
					$result = call_user_func_array( array( $this, $method ), $arguments );
				} catch (ShopgateLibraryException $e) {
					// pass through known Shopgate Library Exceptions
					throw $e;
				} catch (Exception $e) {
					$msg = "An unknown exception has been thrown in loader method \"{$method}\". Memory usage "
						 . $this->getMemoryUsageString()." Exception '".get_class($e)."': [Code: {$e->getCode()}] {$e->getMessage()}";
					throw new ShopgateLibraryException(ShopgateLibraryException::UNKNOWN_ERROR_CODE, $msg, true);
				}

				if ($result) {
					$arguments[0] = $result;
				}
			}
		}

		return $arguments[0];
	}
	
	/**
	 * Creates an array of corresponding helper method names, based on the export type given
	 * @param string $subjectName
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
	 * Example: exportItemNumber, exportType
	 *
	 * @return array
	 */
	protected function getCreateMediaCsvLoaders() {
		return $this->getCreateCsvLoaders("media");
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

	/**
	 * Gets the used memory and real used memory and returns it as a string
	 *
	 * @return string
	 */
	protected function getMemoryUsageString() {
		switch(strtoupper(trim(ShopgateLogger::getInstance()->getMemoryAnalyserLoggingSizeUnit()))) {
			case 'GB':
				return (memory_get_usage()/(1024*1024*1024)) . " GB (real usage ".(memory_get_usage(true)/(1024*1024*1024))." GB)";
			case 'MB':
				return (memory_get_usage()/(1024*1024)) . " MB (real usage ".(memory_get_usage(true)/(1024*1024))." MB)";
			case 'KB':
				return (memory_get_usage()/1024) . " KB (real usage ".(memory_get_usage(true)/1024)." KB)";
			default:
				return memory_get_usage() . " Bytes (real usage ".memory_get_usage(true)." Bytes)";
		}
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
	 * Callback method for the PluginAPI to be able to retrieve the correct URI directing to a specific PluginAPI-action
	 * Override this method if the plugin does not have its own entry point (e.g. MVC-implementations that only provide a controller and controller-action as entry point)
	 *
	 * @param $pluginApiActionName
	 * @return string URL
	 */
	public function getActionUrl($pluginApiActionName) {
		return 'http'.(!empty($_SERVER['HTTPS']) ? 's' : '').'://'. trim($_SERVER['HTTP_HOST'], '/') . '/' . trim($_SERVER['SCRIPT_NAME'], '/');
	}

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
	 * Override this to append additional information about shop system to the response of the ping action.
	 *
	 * @return mixed[] An array with additional information.
	 */
	public function createPluginInfo() { return array(); }

	/**
	 * Callback function for the Shopgate Plugin API ping action.
	 * Override this to append additional information about shop system to the response of the ping action.
	 *
	 * @return mixed[] An array with additional information.
	 */
	public function createShopInfo() {
		$shopInfo = array(
			'category_count' => 0,
			'item_count' => 0,
		);
		
		if($this->config->getEnableGetReviewsCsv()) {
			$shopInfo['review_count'] = 0;
		}
		
		if($this->config->getEnableGetMediaCsv()) {
			$shopInfo['media_count'] = array();
		}
		
		return $shopInfo;
	}

	/**
	 * Callback function for the Shopgate Plugin API Debug action.
	 * Override this to append additional information about shop system to the response of the Debug action.
	 *
	 * @return mixed[] An string with additional information.
	 */
	public function getDebugInfo() { return ''; }

	/**
	 * This performs the necessary queries to build a ShopgateCustomer object for the given log in credentials.
	 * The method should not abort on soft errors like when the street or phone number of a customer can't be found.
	 *
	 * @see http://developer.shopgate.com/plugin_api/customers/get_customer
	 *
	 * @param string $user The user name the customer entered at Shopgate Connect.
	 * @param string $pass The password the customer entered at Shopgate Connect.
	 * @return ShopgateCustomer A ShopgateCustomer object.
	 * @throws ShopgateLibraryException on invalid log in data or hard errors like database failure.
	 */
	public abstract function getCustomer($user, $pass);
	
	/**
	 * This method creates a new user account / user addresses for a customer in the shop system's database
	 * The method should not abort on soft errors like when the street or phone number of a customer is not set.
	 *
	 * @see http://developer.shopgate.com/plugin_api/customers/register_customer
	 *
	 * @param string $user The user name the customer entered at Shopgate.
	 * @param string $pass The password the customer entered at Shopgate.
	 * @param ShopgateCustomer $customer A ShopgateCustomer object to be added to the shop system's database.
	 * @throws ShopgateLibraryException if an error occurs
	 */
	public abstract function registerCustomer($user, $pass, ShopgateCustomer $customer);
	
	/**
	 * Performs the necessary queries to add an order to the shop system's database.
	 *
	 * @see http://developer.shopgate.com/merchant_api/orders/get_orders
	 * @see http://developer.shopgate.com/plugin_api/orders/add_order
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
	 * @see http://developer.shopgate.com/merchant_api/orders/get_orders
	 * @see http://developer.shopgate.com/plugin_api/orders/update_order
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
	 * @see http://developer.shopgate.com/plugin_api/coupons
	 *
	 * @param ShopgateCart $cart The ShopgateCart object containing the coupons that should be redeemed.
	 * @return array('external_coupons' => ShopgateExternalCoupon[])
	 * @throws ShopgateLibraryException if an error occurs.
	 */
	public abstract function redeemCoupons(ShopgateCart $cart);
	
	/**
	 * Checks the content of a cart to be valid and returns necessary changes if applicable.
	 *
	 *
	 * @see http://developer.shopgate.com/plugin_api/cart
	 *
	 * @param ShopgateCart $cart The ShopgateCart object to be checked and validated.
	 * @return array(
	 *          <ul>
	 *          	<li>'external_coupons' => ShopgateExternalCoupon[], # list of all coupons</li>
	 *          	<li>'items' => ShopgateCartItem[], # list of item changes</li>
	 *          	<li>'shippings' => ShopgateShippingMethod[], # list of available shipping services for this cart</li>
	 *          </ul>)
	 * @throws ShopgateLibraryException if an error occurs.
	 */
	public abstract function checkCart(ShopgateCart $cart);
	
	/**
	 * Checks the items array and returns stock quantity for each item.
	 *
	 *
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_check_cart#API_Response
	 *
	 * @param ShopgateCart $cart The ShopgateCart object to be checked and validated.
	 *
	 * @return ShopgateCartItem[] list of item changes
	 * @throws ShopgateLibraryException if an error occurs.
	 */
	public abstract function checkStock(ShopgateCart $cart);
	
	/**
	 * Returns an array of certain settings of the shop. (Currently mainly tax settings.)
	 * 
	 * 
	 * @see http://developer.shopgate.com/plugin_api/system_information/get_settings
	 *
	 * @return array(
	 * 					<ul>
	 *						<li>'tax' => Contains the tax settings as follows:
	 *							<ul>
	 *								<li>'tax_classes_products' => A list of product tax class identifiers.</li>
	 *								<li>'tax_classes_customers' => A list of customer tax classes.</li>
	 *								<li>'tax_rates' => A list of tax rates.</li>
	 *								<li>'tax_rules' => A list of tax rule containers.</li>
	 *							</ul>
	 *						</li>
	 *					</ul>)
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
	 * @see http://developer.shopgate.com/file_formats/csv/products
	 * @see http://developer.shopgate.com/plugin_api/export/get_items_csv
	 *
	 * @throws ShopgateLibraryException
	 */
	protected abstract function createItemsCsv();
	
	/**
	 * Loads the Media file information to the products of the shop system's database and passes them to the buffer.
	 *
	 * Use ShopgatePlugin::buildDefaultMediaRow() to get the correct indices for the field names in a Shopgate media csv and
	 * use ShopgatePlugin::addMediaRow() to add it to the output buffer.
	 *
	 * @see http://wiki.shopgate.com/CSV_File_Media#Sample_Media_CSV_file
	 * @see http://developer.shopgate.com/plugin_api/export/get_media_csv
	 *
	 * @throws ShopgateLibraryException
	 */
	protected abstract function createMediaCsv();
	
	/**
	 * Loads the product categories of the shop system's database and passes them to the buffer.
	 *
	 * Use ShopgatePlugin::buildDefaultCategoryRow() to get the correct indices for the field names in a Shopgate categories csv and
	 * use ShopgatePlugin::addCategoryRow() to add it to the output buffer.
	 *
	 * @see http://developer.shopgate.com/file_formats/csv/categories
	 * @see http://developer.shopgate.com/plugin_api/export/get_categories_csv
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
	 * @see http://developer.shopgate.com/file_formats/csv/reviews
	 * @see http://developer.shopgate.com/plugin_api/export/get_reviews_csv
	 *
	 * @throws ShopgateLibraryException
	 */
	protected abstract function createReviewsCsv();

	/**
	 * Exports orders from the shop system's database to Shopgate.
	 *
	 * @see http://developer.shopgate.com/plugin_api/orders/get_orders
	 *
	 * @param string $customerToken
	 * @param string $customerLanguage
	 * @param int $limit
	 * @param int $offset
	 * @param string $orderDateFrom
	 * @param string $sortOrder
	 *
	 * @return ShopgateExternalOrder[] A list of ShopgateExternalOrder objects
	 *
	 * @throws ShopgateLibraryException
	 */
	public abstract function getOrders($customerToken, $customerLanguage, $limit = 10, $offset = 0, $orderDateFrom = '', $sortOrder = 'created_desc');
	
	/**
	 * Updates and returns synchronization information for the favourite list of a customer.
	 *
	 * @see http://developer.shopgate.com/plugin_api/customers/sync_favourite_list
	 *
	 * @param string $customerToken
	 * @param ShopgateSyncItem[] $items A list of ShopgateSyncItem objects that need to be synchronized
	 *
	 * @return ShopgateSyncItem[] The updated list of ShopgateSyncItem objects
	 */
	public abstract function syncFavouriteList($customerToken, $items);
	
	/**
	 * Loads the products of the shop system's database and passes them to the buffer.
	 *
	 * @param int $limit pagination limit; if not null, the number of exported items must be <= $limit
	 * @param int $offset pagination; if not null, start the export with the item at position $offset
	 * @param string[] $uids a list of item UIDs that should be exported
	 *
	 * @see http://developer.shopgate.com/plugin_api/export/get_items
	 *
	 * @throws ShopgateLibraryException
	 */
	protected abstract function createItems($limit = null, $offset = null, array $uids = array());
	
	/**
	 * Loads the product categories of the shop system's database and passes them to the buffer.
	 *
	 * @param int $limit pagination limit; if not null, the number of exported categories must be <= $limit
	 * @param int $offset pagination; if not null, start the export with the categories at position $offset
	 * @param string[] $uids a list of categories UIDs that should be exported
	 *
	 * @see http://developer.shopgate.com/plugin_api/export/get_categories
	 *
	 * @throws ShopgateLibraryException
	 */
	protected abstract function createCategories($limit = null, $offset = null, array $uids = array());

	/**
	 * Loads the product reviews of the shop system's database and passes them to the buffer.
	 *
	 * @param int      $limit  pagination limit; if not null, the number of exported reviews must be <= $limit
	 * @param int      $offset pagination; if not null, start the export with the reviews at position $offset
	 * @param string[] $uids   A list of products that should be fetched for the reviews.
	 *
	 * @see http://developer.shopgate.com/plugin_api/export/get_reviews
	 *
	 * @throws ShopgateLibraryException
	 */
	protected abstract function createReviews($limit = null, $offset = null, array $uids = array());
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

abstract class ShopgateFileBuffer extends ShopgateObject implements ShopgateFileBufferInterface {
	/**
	 * @var string[]
	 */
	protected $allowedEncodings;
	
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
	 * @param bool $convertEncoding true to enable automatic encoding conversion to utf-8
	 * @param string $sourceEncoding
	 */
	public function __construct($capacity, $convertEncoding = true, $sourceEncoding = null) {
		$this->timeStart = time();
		$this->buffer = array();
		$this->capacity = $capacity;
		$this->convertEncoding = $convertEncoding;
		
		$this->allowedEncodings = array(
			SHOPGATE_LIBRARY_ENCODING, 'ASCII', 'CP1252', 'ISO-8859-15', 'UTF-16LE', 'ISO-8859-1'
		);
		
		if (!empty($sourceEncoding)) {
			array_splice($this->allowedEncodings, 1, 0, $sourceEncoding);
		}
	}

	public function setFile($filePath) {
		$this->filePath = $filePath;
		$this->buffer = array();
		
		if (empty($this->fileHandle)) {
			if (!preg_match("/^php/", $filePath)) {
				$filePath = $this->filePath.".tmp";
			}
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
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_FILE_EMPTY_BUFFER, null, false, false);
		}
		
		// perform prerequisites on first call
		if (ftell($this->fileHandle) == 0) {
			$this->onStart();
		}

		// perform response type specific flushing
		$this->onFlush();

		// clear buffer
		$this->buffer = array();
	}

	/**
	 * Callback for deriving classes.
	 *
	 * This gets called when $this->flush() gets called for the first time and can be used to output headlines
	 * or any other necessary prerequisite.
	 */
	abstract protected function onStart();
	
	/**
	 * Callback for deriving classes.
	 *
	 * This gets called after checking for an empty buffer and before emptying $this->buffer and should
	 * flush all data to the given output file.
	 */
	abstract protected function onFlush();
	
	/**
	 * Callback for deriving classes.
	 *
	 * This gets called after all contents of the buffer have been flushed and before the temporary output file
	 * is renamed to its original name.
	 */
	abstract protected function onFinish();
	

	public function finish() {
		$this->flush();
		
		$this->onFinish();
		
		fclose($this->fileHandle);
		$this->fileHandle = null;
		
		if (!preg_match("/^php/", $this->filePath)) {
			// FIX for Windows Servers
			if (file_exists($this->filePath)) {
				unlink($this->filePath);
			}
			rename($this->filePath.".tmp", $this->filePath);
		}

		$this->log('Fertig, '.basename($this->filePath).' wurde erfolgreich erstellt', "access");
		$duration = time() - $this->timeStart;
		$this->log("Dauer: $duration Sekunden", "access");
	}
}

class ShopgateFileBufferCsv extends ShopgateFileBuffer {
	protected function onStart() {
		fputcsv($this->fileHandle, array_keys($this->buffer[0]), ';', '"');
	}
	
	protected function onFlush() {
		foreach ($this->buffer as $item) {
			if (!empty($this->convertEncoding)) {
				foreach ($item as &$field) {
					$field = $this->stringToUtf8($field, $this->allowedEncodings);
				}
			}
			
			fputcsv($this->fileHandle, $item, ";", "\"");
		}
	}
	
	protected function onFinish() { /* no finishing necessary for CSV files */ }
}

class ShopgateFileBufferJson extends ShopgateFileBuffer {
	protected function onStart() {
		fputs($this->fileHandle, '[');
	}
	
	protected function onFlush() {
		$result = array();
		
		foreach ($this->buffer as $item) {
			/* @var $item Shopgate_Model_AbstractExport */
			$result[] = $this->jsonEncode($item->asArray());
		}
		
		if (!empty($result)) {
			fputs($this->fileHandle, implode(',', $result).',');
		}
	}
	
	protected function onFinish() {
		fseek($this->fileHandle, -1, SEEK_END);
		fputs($this->fileHandle, ']');
	}
}

class ShopgateFileBufferXml extends ShopgateFileBuffer {
	/**
	 * @var Shopgate_Model_XmlResultObject
	 */
	protected $xmlNode;
	
	/**
	 * @var Shopgate_Model_AbstractExport
	 */
	protected $xmlModel;
	
	/**
	 * @param Shopgate_Model_Abstract $xmlModel
	 * @param Shopgate_Model_XmlResultObject $xmlNode
	 * @param null|string $capacity
	 * @param bool $convertEncoding
	 * @param null $sourceEncoding
	 */
	public function __construct(Shopgate_Model_Abstract $xmlModel, Shopgate_Model_XmlResultObject $xmlNode, $capacity, $convertEncoding = true, $sourceEncoding = null) {
		parent::__construct($capacity, $convertEncoding, $sourceEncoding);
		
		$this->xmlNode = $xmlNode;
		$this->xmlModel = $xmlModel;
	}
	
	protected function onStart() {
		fputs($this->fileHandle, sprintf(
			'<%s xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="%s">',
			$this->xmlModel->getIdentifier(),
			$this->xmlModel->getXsdFileLocation()
		));
	}
	
	protected function onFlush() {
		$itemsNode = clone $this->xmlNode;
		
		foreach ($this->buffer as $item) {
			/* @var $item Shopgate_Model_AbstractExport */
			$item->asXml($itemsNode);
		}
		
		foreach ($itemsNode as $xmlItem) {
			/* @var $xmlItem Shopgate_Model_XmlResultObject */
			fputs($this->fileHandle, $xmlItem->asXML());
		}
	}
	
	protected function onFinish() {
		fputs($this->fileHandle, '</'.$this->xmlModel->getIdentifier().'>');
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
	 * Compares values of two containers.
	 *
	 * @param ShopgateContainer $obj
	 * @param ShopgateContainer $obj2
	 * @param $whitelist
	 * @return bool
	 */
	public function compare($obj,$obj2,$whitelist){
		
		foreach($whitelist as $acceptedField){
			if($obj->{$this->camelize('get_'.$acceptedField)}() != $obj2->{$this->camelize('get_'.$acceptedField)}()){
				return false;
			}
		}
		return true;
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
	 * @param bool $useIconv True to use iconv instead of mb_convert_encoding even if the mb library is present.
	 * @return ShopgateContainer The new object with utf-8 encoded values.
	 */
	public function utf8Encode($sourceEncoding = 'ISO-8859-15', $force = false, $useIconv = false) {
		$visitor = new ShopgateContainerUtf8Visitor(ShopgateContainerUtf8Visitor::MODE_ENCODE, $sourceEncoding, $force, $useIconv);
		$visitor->visitContainer($this);
		return $visitor->getObject();
	}

	/**
	 * Creates a new object of the same type with every value recursively utf-8 decoded.
	 *
	 * @param String $destinationEncoding The destination Encoding for the strings
	 * @param bool $force Set this true to enforce encoding even if the destination encoding is set to UTF-8.
	 * @param bool $useIconv True to use iconv instead of mb_convert_encoding even if the mb library is present.
	 * @return ShopgateContainer The new object with utf-8 decoded values.
	 */
	public function utf8Decode($destinationEncoding = 'ISO-8859-15', $force = false, $useIconv = false) {
		$visitor = new ShopgateContainerUtf8Visitor(ShopgateContainerUtf8Visitor::MODE_DECODE, $destinationEncoding, $force, $useIconv);
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
	public function visitPlainObject(ShopgateContainer $c);
	public function visitCustomer(ShopgateCustomer $c);
	public function visitAddress(ShopgateAddress $a);
	public function visitCart(ShopgateCart $c);
	public function visitOrder(ShopgateOrder $o);
	public function visitExternalOrder(ShopgateExternalOrder $o);
	public function visitExternalOrderTax(ShopgateExternalOrderTax $t);
	public function visitExternalOrderExtraCost(ShopgateExternalOrderExtraCost $c);
	public function visitOrderItem(ShopgateOrderItem $i);
	public function visitExternalOrderItem(ShopgateExternalOrderItem $i);
	public function visitSyncItem(ShopgateSyncItem $i);
	public function visitOrderItemOption(ShopgateOrderItemOption $o);
	public function visitOrderItemInput(ShopgateOrderItemInput $i);
	public function visitOrderItemAttribute(ShopgateOrderItemAttribute $o);
	public function visitOrderCustomField(ShopgateOrderCustomField $c);
	public function visitShippingInfo(ShopgateShippingInfo $o);
	public function visitOrderDeliveryNote(ShopgateDeliveryNote $d);
	public function visitExternalCoupon(ShopgateExternalCoupon $c);
	public function visitShopgateCoupon(ShopgateShopgateCoupon $c);
	public function visitCategory(ShopgateCategory $d);
	public function visitItem(ShopgateItem $i);
	public function visitItemOption(ShopgateItemOption $i);
	public function visitItemOptionValue(ShopgateItemOptionValue $i);
	public function visitItemInput(ShopgateItemInput $i);
	public function visitConfig(ShopgateConfig $c);
	public function visitShippingMethod(ShopgateShippingMethod $c);
	public function visitPaymentMethod(ShopgatePaymentMethod $c);
	public function visitCartItem(ShopgateCartItem $c);
	public function visitCartCustomer(ShopgateCartCustomer $c);
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
	protected $useIconv;

	/**
	 * @param int $mode Set mode to one of the two class constants. Default is MODE_DECODE.
	 * @param string $encoding The source or destination encoding according to PHP's mb_convert_encoding().
	 * @param bool $force Set this true to enforce encoding even if the source or destination encoding is UTF-8.
	 * @param bool $useIconv True to use iconv instead of mb_convert_encoding even if the mb library is present.
	 * @see http://www.php.net/manual/en/function.mb-convert-encoding.php
	 */
	public function __construct($mode = self::MODE_DECODE, $encoding = 'ISO-8859-15', $force = false, $useIconv = false) {
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
		$this->useIconv = $useIconv;
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

	public function visitPlainObject(ShopgateContainer $c) {
		// get properties
		$properties = $c->buildProperties();

		// iterate the simple variables
		$this->iterateSimpleProperties($properties);

		// create new object with utf-8 en- / decoded data
		try {
			$className = get_class($c);
			$this->object = new $className($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitCustomer(ShopgateCustomer $c) {
		// get properties
		$properties = $c->buildProperties();

		// iterate the simple variables
		$this->iterateSimpleProperties($properties);

		// iterate ShopgateAddress objects
		$properties['custom_fields'] = $this->iterateObjectList($properties['custom_fields']);
		$properties['addresses'] = $this->iterateObjectList($properties['addresses']);
		$properties['customer_groups'] = $this->iterateObjectList($properties['customer_groups']);

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
		
		$properties['custom_fields'] = $this->iterateObjectList($properties['custom_fields']);

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

		// visit shipping_infos
		if (!empty($properties['shipping_infos']) && ($properties['shipping_infos'] instanceof ShopgateShippingInfo)) {
			$properties['shipping_infos']->accept($this);
			$properties['shipping_infos'] = $this->object;
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
		$properties['custom_fields'] = $this->iterateObjectList($properties['custom_fields']);
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

	public function visitExternalOrder(ShopgateExternalOrder $o) {
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
		
		// iterate lists of referred objects
		$properties['custom_fields'] = $this->iterateObjectList($properties['custom_fields']);
		$properties['external_coupons'] = $this->iterateObjectList($properties['external_coupons']);
		$properties['items'] = $this->iterateObjectList($properties['items']);
		$properties['delivery_notes'] = $this->iterateObjectList($properties['delivery_notes']);
		$properties['order_taxes'] = $this->iterateObjectList($properties['order_taxes']);
		$properties['extra_costs'] = $this->iterateObjectList($properties['extra_costs']);
		
		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateExternalOrder($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitExternalOrderTax(ShopgateExternalOrderTax $t) {
		$properties = $t->buildProperties();
		$this->iterateSimpleProperties($properties);
		
		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateExternalOrderTax($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}

	public function visitExternalOrderExtraCost(ShopgateExternalOrderExtraCost $c) {
		$properties = $c->buildProperties();
		$this->iterateSimpleProperties($properties);
		
		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateExternalOrderExtraCost($properties);
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
	
	public function visitSyncItem(ShopgateSyncItem $i) {
		// get properties
		$properties = $i->buildProperties();
	
		// iterate the simple variables
		$this->iterateSimpleProperties($properties);
	
		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateSyncItem($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}
	
	public function visitExternalOrderItem(ShopgateExternalOrderItem $i) {
		// get properties
		$properties = $i->buildProperties();
	
		// iterate the simple variables
		$this->iterateSimpleProperties($properties);
	
		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateExternalOrderItem($properties);
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
	
	public function visitOrderCustomField(ShopgateOrderCustomField $c) {
		$properties = $c->buildProperties();
		$this->iterateSimpleProperties($properties);
		
		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateOrderCustomField($properties);
		} catch (ShopgateLibraryException $e) {
			$this->object = null;
		}
	}
	
	public function visitShippingInfo(ShopgateShippingInfo $o) {
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
		$properties['options']    = $this->iterateObjectList($properties['options']);
		$properties['inputs']     = $this->iterateObjectList($properties['inputs']);
        $properties['attributes'] = $this->iterateObjectList($properties['attributes']);
        
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
			$this->object = new ShopgateItemOptionValue($properties);
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

    /**
     * @param ShopgateShippingMethod $c
     */
	public function visitShippingMethod(ShopgateShippingMethod $c) {
        $properties = $c->buildProperties();

        // iterate the simple variables
        $this->iterateSimpleProperties($properties);

        // create new object with utf-8 en- / decoded data
        try {
            $this->object = new ShopgateShippingMethod($properties);
        } catch (ShopgateLibraryException $e) {
            $this->object = null;
        }
    }

    /**
     * @param ShopgateCartItem $c
     */
	public function visitCartItem(ShopgateCartItem $c) {
        $properties = $c->buildProperties();

        // iterate the simple variables
        $this->iterateSimpleProperties($properties);

        // iterate the item options and inputs
        $properties['options']    = $this->iterateObjectList($properties['options']);
        $properties['inputs']     = $this->iterateObjectList($properties['inputs']);
        $properties['attributes'] = $this->iterateObjectList($properties['attributes']);

        // create new object with utf-8 en- / decoded data
        try {
            $this->object = new ShopgateCartItem($properties);
        } catch (ShopgateLibraryException $e) {
            $this->object = null;
        }
    }

    /**
     * @param ShopgatePaymentMethod $c
     */
	public function visitPaymentMethod(ShopgatePaymentMethod $c) {
        $properties = $c->buildProperties();

        // iterate the simple variables
        $this->iterateSimpleProperties($properties);

        // create new object with utf-8 en- / decoded data
        try {
            $this->object = new ShopgatePaymentMethod($properties);
        } catch (ShopgateLibraryException $e) {
            $this->object = null;
        }
    }

	/**
	 * @param ShopgateCartCustomer $c
	 */
	public function visitCartCustomer(ShopgateCartCustomer $c) {
		$properties = $c->buildProperties();

		// iterate the simple variables
		$this->iterateSimpleProperties($properties);

		// iterate the customer_groups
		$properties['customer_groups'] = $this->iterateObjectList($properties['customer_groups']);

		// create new object with utf-8 en- / decoded data
		try {
			$this->object = new ShopgateCartCustomer($properties);
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
				case self::MODE_ENCODE: $value = $this->firstObject->stringToUtf8($value, $this->encoding, $this->force, $this->useIconv); break;
				case self::MODE_DECODE: $value = $this->firstObject->stringFromUtf8($value, $this->encoding, $this->force, $this->useIconv); break;
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
		$properties['custom_fields'] = $this->iterateObjectList($properties['custom_fields']);
		$properties['addresses'] = $this->iterateObjectList($properties['addresses']);
		$properties['customer_groups'] = $this->iterateObjectList($properties['customer_groups']);

		// set last value to converted array
		$this->array = $properties;
	}

	public function visitPlainObject(ShopgateContainer $c) {
		// get properties
		$properties = $c->buildProperties();

		// iterate the simple variables
		$properties = $this->iterateSimpleProperties($properties);

		// set last value to converted array
		$this->array = $properties;
	}

	public function visitAddress(ShopgateAddress $a) {
		// get and iterate simple properties
		$properties = $this->iterateSimpleProperties($a->buildProperties());
		
		// iterate ShopgateOrderCustomField objects
		$properties['custom_fields'] = $this->iterateObjectList($properties['custom_fields']);
		
		// update array
		$this->array = $properties;
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

		// visit shipping info
		if (!empty($properties['shipping_infos']) && ($properties['shipping_infos'] instanceof ShopgateShippingInfo)) {
			$properties['shipping_infos']->accept($this);
			$properties['shipping_infos'] = $this->array;
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
		$properties['custom_fields'] = $this->iterateObjectList($properties['custom_fields']);
		$properties['external_coupons'] = $this->iterateObjectList($properties['external_coupons']);
		$properties['shopgate_coupons'] = $this->iterateObjectList($properties['shopgate_coupons']);
		$properties['items'] = $this->iterateObjectList($properties['items']);
		$properties['delivery_notes'] = $this->iterateObjectList($properties['delivery_notes']);
	
		// set last value to converted array
		$this->array = $properties;
	}

	public function visitExternalOrder(ShopgateExternalOrder $o) {
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
	
		// visit the items and delivery notes arrays
		$properties['custom_fields'] = $this->iterateObjectList($properties['custom_fields']);
		$properties['external_coupons'] = $this->iterateObjectList($properties['external_coupons']);
		$properties['items'] = $this->iterateObjectList($properties['items']);
		$properties['delivery_notes'] = $this->iterateObjectList($properties['delivery_notes']);
		$properties['order_taxes'] = $this->iterateObjectList($properties['order_taxes']);
		$properties['extra_costs'] = $this->iterateObjectList($properties['extra_costs']);
	
		// set last value to converted array
		$this->array = $properties;
	}
	
	public function visitExternalOrderTax(ShopgateExternalOrderTax $t) {
		// get properties and iterate (no complex types in ShopgateExternalOrderTax objects)
		$this->array = $this->iterateSimpleProperties($t->buildProperties());
	}
	public function visitExternalOrderExtraCost(ShopgateExternalOrderExtraCost $c) {
		// get properties and iterate (no complex types in ShopgateExternalOrderExtraCost objects)
		$this->array = $this->iterateSimpleProperties($c->buildProperties());
	}
	
	public function visitOrderItem(ShopgateOrderItem $i) {
		// get properties
		$properties = $i->buildProperties();
	
		// iterate the simple variables
		$properties = $this->iterateSimpleProperties($properties);
	
		// iterate options/attributes/input fields objects
		$properties['options']    = $this->iterateObjectList($properties['options']);
		$properties['inputs']     = $this->iterateObjectList($properties['inputs']);
		$properties['attributes'] = $this->iterateObjectList($properties['attributes']);

		// set last value to converted array
		$this->array = $properties;
	}
	
	public function visitSyncItem(ShopgateSyncItem $i) {
		// get properties and iterate (no options and inputs available in ShopgateSyncOrderItem objects)
		$this->array = $this->iterateSimpleProperties($i->buildProperties());
	}
	
	public function visitExternalOrderItem(ShopgateExternalOrderItem $i) {
		// get properties
		$properties = $i->buildProperties();
	
		// iterate the simple variables
		$properties = $this->iterateSimpleProperties($properties);
	
		// set last value to converted array
		$this->array = $properties;
	}
	
    /**
     * @param ShopgateShippingMethod $c
     */
	public function visitShippingMethod(ShopgateShippingMethod $c) {
        $properties = $c->buildProperties();

        // iterate the simple variables
        $properties = $this->iterateSimpleProperties($properties);

        // set last value to converted array
        $this->array = $properties;
    }

    /**
     * @param ShopgateCartItem $c
     */
	public function visitCartItem(ShopgateCartItem $c) {
        $properties = $c->buildProperties();

        // iterate the simple variables
        $properties = $this->iterateSimpleProperties($properties);

        // iterate ShopgateAddress objects
        $properties['options']    = $this->iterateObjectList($properties['options']);
        $properties['inputs']     = $this->iterateObjectList($properties['inputs']);
        $properties['attributes'] = $this->iterateObjectList($properties['attributes']);

        // set last value to converted array
        $this->array = $properties;
    }

	/**
	 * @param ShopgateCartCustomer $c
	 */
	public function visitCartCustomer(ShopgateCartCustomer $c) {
		$properties = $c->buildProperties();

		// iterate the simple variables
		$properties = $this->iterateSimpleProperties($properties);

		// iterate the customer_groups
		$properties['customer_groups'] = $this->iterateObjectList($properties['customer_groups']);

		// set last value to converted array
		$this->array = $properties;
	}

	/**
	 * @param ShopgatePaymentMethod $c
	 */
	public function visitPaymentMethod(ShopgatePaymentMethod $c) {
		$properties = $c->buildProperties();

        // iterate the simple variables
        $properties = $this->iterateSimpleProperties($properties);

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
	
	public function visitOrderCustomField(ShopgateOrderCustomField $c) {
		// get properties and iterate (no complex types in ShopgateOrderCustomField objects)
		$this->array = $this->iterateSimpleProperties($c->buildProperties());
	}
	
	public function visitShippingInfo(ShopgateShippingInfo $i) {
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
