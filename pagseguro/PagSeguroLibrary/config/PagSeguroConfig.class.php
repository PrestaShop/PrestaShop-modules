<?php if (!defined('PAGSEGURO_LIBRARY')) { die('No direct script access allowed'); }
/*
	************************************************************************
	Copyright [2011] [PagSeguro Internet Ltda.]

	Licensed under the Apache License, Version 2.0 (the "License");
	you may not use this file except in compliance with the License.
	You may obtain a copy of the License at

	   http://www.apache.org/licenses/LICENSE-2.0

	Unless required by applicable law or agreed to in writing, software
	distributed under the License is distributed on an "AS IS" BASIS,
	WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	See the License for the specific language governing permissions and
	limitations under the License.
	************************************************************************
*/

/*
 * Provides a means to retrieve configuration preferences.
 * These preferences can come from the default config file (PagSeguroLibrary/config/PagSeguroConfig.php).
*/
class PagSeguroConfig{
	
	private static $config;
	private static $data;
	const varName = 'PagSeguroConfig';
	
	private function __construct() {
		define('ALLOW_PAGSEGURO_CONFIG', TRUE);
		require_once PagSeguroLibrary::getPath().DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."PagSeguroConfig.php";
		$varName = self::varName;
		if (isset($$varName)) {
			self::$data = $$varName;
			unset($$varName);
		} else {
			throw new Exception("Config is undefined.");
		}
	}

	public static function init() {
		if (self::$config == null) {
			self::$config = new PagSeguroConfig();
		}
		return self::$config;
	}
	
	public static function getData($key1, $key2 = null) {
		if ($key2 != null) {
			if (isset(self::$data[$key1][$key2])) {
				return self::$data[$key1][$key2];
			} else {
				throw new Exception("Config keys {$key1}, {$key2} not found.");
			}
		} else {
			if (isset(self::$data[$key1])) {
				return self::$data[$key1];
			} else {
				throw new Exception("Config key {$key1} not found.");
			}
		}
	}
	
	public static function setData($key1, $key2, $value) {
		if (isset(self::$data[$key1][$key2])) {
			self::$data[$key1][$key2] = $value;
		} else {
			throw new Exception("Config keys {$key1}, {$key2} not found.");
		}
	}	
	
	public static function getAccountCredentials() {
		if (isset(self::$data['credentials']) && isset(self::$data['credentials']['email']) && isset(self::$data['credentials']['token'])) {
			return new PagSeguroAccountCredentials(self::$data['credentials']['email'], self::$data['credentials']['token']);
		} else {
			throw new Exception("Credentials not set.");
		}
	}
	
	public static function getEnvironment() {
		if (isset(self::$data['environment']) && isset(self::$data['environment']['environment'])) {
			return self::$data['environment']['environment'];
		} else {
			throw new Exception("Environment not set.");
		}
	}
	
	public static function getApplicationCharset() {
		if (isset(self::$data['application']) && isset(self::$data['application']['charset'])) {
			return self::$data['application']['charset'];
		} else {
			throw new Exception("Application charset not set.");
		}
	}
	
	public static function setApplicationCharset($charset) {
		self::setData('application', 'charset', $charset);
	}
	
	public static function logIsActive() {
		if (isset(self::$data['log']) && isset(self::$data['log']['active'])) {
			return (bool) self::$data['log']['active'];
		} else {
			throw new Exception("Log activation flag not set.");
		}
	}
	
	public static function activeLog($fileName = null) {
		self::setData('log', 'active', true);
		self::setData('log', 'fileLocation', $fileName ? $fileName : '');
		LogPagSeguro::reLoad();
	}
	
	public static function getLogFileLocation() {
		if (isset(self::$data['log']) && isset(self::$data['log']['fileLocation'])) {
			return self::$data['log']['fileLocation'];
		} else {
			throw new Exception("Log file location not set.");
		}
	}	
	
}
?>
