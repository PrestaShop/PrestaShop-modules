<?php
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
 * PagSeguro Library Class
 * Version: 2.1.5
 * Date: 15/04/2013
 */
define('PAGSEGURO_LIBRARY', TRUE);
require_once "loader".DIRECTORY_SEPARATOR."PagSeguroAutoLoader.class.php";
class PagSeguroLibrary {
	
	const VERSION = "2.1.5";
	public static $resources;
	public static $config;
	public static $log;
	private static $path;
	private static $library;
	private static $module_version;
	private static $cms_version;
	
	private function __construct() {
		self::$path 	 = (dirname(__FILE__));
		PagSeguroAutoloader::init();
		self::$resources = PagSeguroResources::init();
		self::$config 	 = PagSeguroConfig::init();
		self::$log 	 = LogPagSeguro::init();
	}
	
	public static function init() {
		self::verifyDependencies();
		if (self::$library == null) {
			self::$library  = new PagSeguroLibrary();
		}
		return self::$library;
	}
	
	private static function verifyDependencies() {
		
		$dependencies = true;
		
		if (!function_exists('spl_autoload_register')) {
			throw new Exception("PagSeguroLibrary: Standard PHP Library (SPL) is required.");
			$dependencies = false;
		}
		
		if (!function_exists('curl_init')) {
			throw new Exception('PagSeguroLibrary: cURL library is required.');
			$dependencies = false;
		}
		
		if (!class_exists('DOMDocument')) {
			throw new Exception('PagSeguroLibrary: DOM XML extension is required.');
			$dependencies = false;
		}
		
		return $dependencies;
		
	}
	
	public final static function getVersion() {
		return self::VERSION;
	}
	
	public final static function getPath() {
		return self::$path;
	}
	
	public final static function getModuleVersion() {
		return self::$module_version;
	}
        
	public final static function setModuleVersion($version) {
		self::$module_version = $version;
	}
        
	public final static function getCMSVersion() {
		return self::$cms_version;
	}
        
	public final static function setCMSVersion($version) {
		self::$cms_version = $version;
	}
}
PagSeguroLibrary::init();
?>
