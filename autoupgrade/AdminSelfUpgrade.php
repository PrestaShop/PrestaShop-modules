<?php
/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*	@author PrestaShop SA <contact@prestashop.com>
*	@copyright	2007-2013 PrestaShop SA
*	@version	Release: $Revision: 11834 $
*	@license		http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*	International Registered Trademark & Property of PrestaShop SA
*/

// _PS_ADMIN_DIR_ is defined in ajax-upgradetab, but may be not defined in direct call
if (!defined('_PS_ADMIN_DIR_') && defined('PS_ADMIN_DIR'))
	define('_PS_ADMIN_DIR_', PS_ADMIN_DIR);

// Note : we cannot use the native AdminTab because
// we don't know the current PrestaShop version number
require_once(_PS_ROOT_DIR_.'/modules/autoupgrade/AdminSelfTab.php');

require_once(_PS_ROOT_DIR_.'/modules/autoupgrade/classes/Upgrader.php');

if (!class_exists('Upgrader', false))
{
	if (file_exists(_PS_ROOT_DIR_.'/override/classes/Upgrader.php'))
		require_once(_PS_ROOT_DIR_.'/override/classes/Upgrader.php');
	else
		eval('class Upgrader extends UpgraderCore{}');
}

require_once(_PS_ROOT_DIR_.'/modules/autoupgrade/classes/Tools14.php');
if (!class_exists('Tools', false))
	eval('class Tools extends Tools14{}');

class AdminSelfUpgrade extends AdminSelfTab
{
	public $multishop_context;
	public $multishop_context_group = false;
	public $_html = '';
	// used for translations
	public static $l_cache;
	// retrocompatibility
	public $noTabLink = array();
	public $id = -1;

	public $ajax = false;
	public $nextResponseType = 'json'; // json, xml
	public $next = 'N/A';

	public $upgrader = null;
	public $standalone = true;

	/**
	 * set to false if the current step is a loop
	 *
	 * @var boolean
	 */
	public $stepDone = true;
	public $status = true;
	public $warning_exists = false;
	public $error = '0';
	public $next_desc = '.';
	public $nextParams = array();
	public $nextQuickInfo = array();
	public $nextErrors = array();
	public $currentParams = array();
	/**
	 * @var array theses values will be automatically added in "nextParams"
	 * if their properties exists
	 */
	public $ajaxParams = array(
		// autoupgrade options
		'install_version',

		'backupName',
		'backupFilesFilename',
		'backupDbFilename',

		'restoreName',
		'restoreFilesFilename',
		'restoreDbFilenames',

		'installedLanguagesIso',
		'modules_addons',
		'warning_exists',
	);

	/**
	 * installedLanguagesIso is an array of iso_code of each installed languages
	 *
	 * @var array
	 * @access public
	 */
	public $installedLanguagesIso = array();

	/**
	 * modules_addons is an array of array(id_addons => name_module).
	 *
	 * @var array
	 * @access public
	 */
	public $modules_addons = array();

	public $autoupgradePath = null;
	public $downloadPath = null;
	public $backupPath = null;
	public $latestPath = null;
	public $tmpPath = null;

	/**
	 * autoupgradeDir
	 *
	 * @var string directory relative to admin dir
	 */
	public $autoupgradeDir = 'autoupgrade';
	public $latestRootDir = '';
	public $prodRootDir = '';
	public $adminDir = '';
	public $root_writable = null;
	public $module_version = null;

	public $lastAutoupgradeVersion = '';
	public $destDownloadFilename = 'prestashop.zip';

	/**
	 * configFilename contains all configuration specific to the autoupgrade module
	 *
	 * @var string
	 * @access public
	 */
	public $configFilename = 'config.var';
	/**
	 * during upgradeFiles process,
	 * this files contains the list of queries left to upgrade in a serialized array.
	 * (this file is deleted in init() method if you reload the page)
	 * @var string
	 */
	public $toUpgradeQueriesList = 'queriesToUpgrade.list';
	/**
	 * during upgradeFiles process,
	 * this files contains the list of files left to upgrade in a serialized array.
	 * (this file is deleted in init() method if you reload the page)
	 * @var string
	 */
	public $toUpgradeFileList = 'filesToUpgrade.list';
	/**
	 * during upgradeModules process,
	 * this files contains the list of modules left to upgrade in a serialized array.
	 * (this file is deleted in init() method if you reload the page)
	 * @var string
	 */
	public $toUpgradeModuleList = 'modulesToUpgrade.list';
	/**
	 * during upgradeFiles process,
	 * this files contains the list of files left to upgrade in a serialized array.
	 * (this file is deleted in init() method if you reload the page)
	 * @var string
	 */
	public $diffFileList = 'filesDiff.list';
	/**
	 * during backupFiles process,
	 * this files contains the list of files left to save in a serialized array.
	 * (this file is deleted in init() method if you reload the page)
	 * @var string
	 */
	public $toBackupFileList = 'filesToBackup.list';
	/**
	 * during backupDb process,
	 * this files contains the list of tables left to save in a serialized array.
	 * (this file is deleted in init() method if you reload the page)
	 * @var string
	 */
	public $toBackupDbList = 'tablesToBackup.list';
	/**
	 * during restoreDb process,
	 * this file contains a serialized array of queries which left to execute for restoring database
	 * (this file is deleted in init() method if you reload the page)
	 * @var string
	 */
	public $toRestoreQueryList = 'queryToRestore.list';
	/**
	 * during restoreFiles process,
	 * this file contains difference between files present in a backupFiles archive
	 * and files currently in directories, in a serialized array.
	 * (this file is deleted in init() method if you reload the page)
	 * @var string
	 */
	public $toRemoveFileList = 'filesToRemove.list';
	/**
	 * during restoreFiles process,
	 * contains list of files present in backupFiles archive
	 *
	 * @var string
	 */
	public $fromArchiveFileList = 'filesFromArchive.list';

	/**
	 * mailCustomList contains list of mails files which are customized,
	 * relative to original files for the current PrestaShop version
	 *
	 * @var string
	 */
	public $mailCustomList = 'mails-custom.list';

	/**
	 * tradCustomList contains list of mails files which are customized,
	 * relative to original files for the current PrestaShop version
	 *
	 * @var string
	 */
	public $tradCustomList = 'translations-custom.list';
	/**
	 * tmp_files contains an array of filename which will be removed
	 * at the beginning of the upgrade process
	 *
	 * @var array
	 */
	public $tmp_files = array(
		'toUpgradeFileList',
		'toUpgradeQueriesList',
		'diffFileList',
		'toBackupFileList',
		'toBackupDbList',
		'toRestoreQueryList',
		'toRemoveFileList',
		'fromArchiveFileList',
		'tradCustomList',
		'mailCustomList',
	);

	public $install_version;
	public $keepImages = null;
	public $updateDefaultTheme = null;
	public $keepMails = null;
	public $manualMode = null;
	public $deactivateCustomModule = null;

	public $sampleFileList = array();
	private $restoreIgnoreFiles = array();
	private $restoreIgnoreAbsoluteFiles = array();
	private $backupIgnoreFiles = array();
	private $backupIgnoreAbsoluteFiles = array();
	private $excludeFilesFromUpgrade = array();
	private $excludeAbsoluteFilesFromUpgrade = array();

	private $restoreName = null;
	private $backupName = null;
	private $backupFilesFilename = null;
	private $backupDbFilename = null;
	private $restoreFilesFilename = null;
	private $restoreDbFilenames = array();

	/**
	 * int loopBackupFiles : if your server has a low memory size, lower this value
	 */
	public static $loopBackupFiles = 500;
	/**
	 * int loopBackupDbTime : if your server has a low memory size, lower this value
	 */
	public static $loopBackupDbTime = 6;
	/**
	 * int max_written_allowed : if your server has a low memory size, lower this value
	 */
	public static $max_written_allowed = 4194304; // 4096 ko
	/**
	 * int loopUpgradeFiles : if your server has a low memory size, lower this value
	 */
	public static $loopUpgradeFiles = 1000;
	/**
	 * int loopRestoreFiles : if your server has a low memory size, lower this value
	 */
	public static $loopRestoreFiles = 500;
	/**
	 * int loopRestoreQueryTime : if your server has a low memory size, lower this value (in sec)
	 */
	public static $loopRestoreQueryTime = 6;
	/**
	 * int loopUpgradeModulesTime : if your server has a low memory size, lower this value (in sec)
	 */
	public static $loopUpgradeModulesTime = 6;
	/**
	 * int loopRemoveSamples : if your server has a low memory size, lower this value
	 */
	public static $loopRemoveSamples = 1000;

	/* usage :  key = the step you want to ski
	 * value = the next step you want instead
	 *	example : public static $skipAction = array();
	 *	initial order upgrade:
	 *		download, unzip, removeSamples, backupFiles, backupDb, upgradeFiles, upgradeDb, upgradeModules, cleanDatabase, upgradeComplete
	 * initial order rollback: rollback, restoreFiles, restoreDb, rollbackComplete
	 */
	public static $skipAction = array();

	/**
	 * if set to true, will use pclZip library
	 * even if ZipArchive is available
	 */
	public static $force_pclZip = false;

	protected $_includeContainer = true;

	public $_fieldsUpgradeOptions = array();
	public $_fieldsBackupOptions = array();
	/**
	 * replace tools encrypt
	 *
	 * @param mixed $string
	 * @return void
	 */
	public function encrypt($string)
	{
		return md5(_COOKIE_KEY_.$string);
	}

	public function checkToken()
	{
		// simple checkToken in ajax-mode, to be free of Cookie class (and no Tools::encrypt() too )
		if ($this->ajax)
			return ($_COOKIE['autoupgrade'] == $this->encrypt($_COOKIE['id_employee']));
		else
			return parent::checkToken();
	}

	/**
	 * create cookies id_employee, id_tab and autoupgrade (token)
	 */
	public function createCustomToken()
	{
		// ajax-mode for autoupgrade, we can't use the classic authentication
		// so, we'll create a cookie in admin dir, based on cookie key
		global $cookie;
		$id_employee = $cookie->id_employee;
		$iso_code = $_COOKIE['iso_code'] = Language::getIsoById($cookie->id_lang);

		$admin_dir = trim(str_replace($this->prodRootDir, '', $this->adminDir), DIRECTORY_SEPARATOR);
		$cookiePath = __PS_BASE_URI__.$admin_dir;
		setcookie('id_employee', $id_employee, time() + 7200, $cookiePath);
		setcookie('id_tab', $this->id, time() + 7200, $cookiePath);
		setcookie('iso_code', $iso_code, time() + 7200, $cookiePath);
		setcookie('autoupgrade', $this->encrypt($id_employee), time() + 7200, $cookiePath);
		return false;
	}

	public function viewAccess($disable = false)
	{
		if ($this->ajax)
			return true;
		else
		{
			// simple access : we'll allow only admin
			global $cookie;
			if ($cookie->profile == 1)
				return true;
		}
		return false;
	}

	public function __construct()
	{
		@set_time_limit(0);
		@ini_set('max_execution_time', '0');

		global $ajax, $currentIndex;

		if (!empty($ajax))
			$this->ajax = true;

		$this->init();
		// retrocompatibility when used in module : Tab can't work,
		// but we saved the tab id in a cookie.
		if (class_exists('Tab', false))
			parent::__construct();
		elseif (isset($_COOKIE['id_tab']))
			$this->id = $_COOKIE['id_tab'];

		// Database instanciation (need to be cached because there will be at least 100k calls in the upgrade process
		if (!class_exists('Db', false))
		{
			require_once(_PS_ROOT_DIR_.'/modules/autoupgrade/db/Db.php');
			eval('abstract class Db extends DbCore{}');
			require_once(_PS_ROOT_DIR_.'/modules/autoupgrade/db/MySQL.php');
			eval('class MySQL extends MySQLCore{}');
			require_once(_PS_ROOT_DIR_.'/modules/autoupgrade/db/DbMySQLi.php');
			eval('class DbMySQLi extends DbMySQLiCore{}');
			require_once(_PS_ROOT_DIR_.'/modules/autoupgrade/db/DbPDO.php');
			eval('class DbPDO extends DbPDOCore{}');
			require_once(_PS_ROOT_DIR_.'/modules/autoupgrade/db/DbQuery.php');
			eval('class DbQuery extends DbQueryCore{}');

			require_once(_PS_ROOT_DIR_.'/modules/autoupgrade/alias.php');
		}
		$this->db = Db::getInstance();

		// Performance settings
		$perf_array = array(
			'loopBackupFiles' => array(400, 800, 1600),
			'loopBackupDbTime' => array(6, 12, 25),
			'max_written_allowed' => array(4194304, 8388608, 16777216),
			'loopUpgradeFiles' => array(600, 1200, 2400),
			'loopRestoreFiles' => array(400, 800, 1600),
			'loopRestoreQueryTime' => array(6, 12, 25),
			'loopUpgradeModulesTime' => array(6, 12, 25),
			'loopRemoveSamples' => array(400, 800, 1600)
		);
		switch ($this->getConfig('PS_AUTOUP_PERFORMANCE'))
		{
			case 3:
				foreach ($perf_array as $property => $values)
					self::$$property = $values[2];
				break;
			case 2:
				foreach ($perf_array as $property => $values)
					self::$$property = $values[1];
				break;
			case 1:
			default:
				foreach ($perf_array as $property => $values)
					self::$$property = $values[0];
		}
		/* Bug with backwardcompatibility overrinding currentIndex */
		if (version_compare(_PS_VERSION_,'1.5.0.0','>'))	
			$this->currentIndex = $_SERVER['SCRIPT_NAME'].(($controller = Tools::getValue('controller')) ? '?controller='.$controller: '');
		else
		$this->currentIndex = $currentIndex;
	}

	protected function l($string, $class = 'AdminTab', $addslashes = FALSE, $htmlentities = TRUE)
	{
		// need to be called in order to populate $classInModule
		$str = self::findTranslation('autoupgrade', $string, 'AdminSelfUpgrade');
		$str = $htmlentities ? htmlentities($str, ENT_QUOTES, 'utf-8') : $str;
		return str_replace('"', '&quot;', ($addslashes ? addslashes($str) : stripslashes($str)));
	}

	/**
	 * findTranslation (initially in Module class), to make translations works
	 *
	 * @param string $name module name
	 * @param string $string string to translate
	 * @param string $source current class
	 * @return string translated string
	 */
	public static function findTranslation($name, $string, $source)
	{
		static $_MODULES;
		if (!is_array($_MODULES))
		{
			// note: $_COOKIE[iso_code] is set in createCustomToken();
			$file = _PS_MODULE_DIR_.'autoupgrade'.DIRECTORY_SEPARATOR.$_COOKIE['iso_code'].'.php';
			if (file_exists($file) && include($file))
				$_MODULES = !empty($_MODULES)?array_merge($_MODULES, $_MODULE):$_MODULE;
		}
		$cache_key = $name.'|'.$string.'|'.$source;

		if (!isset(self::$l_cache[$cache_key]))
		{
			if (!is_array($_MODULES))
				return $string;
			// set array key to lowercase for 1.3 compatibility
			$_MODULES = array_change_key_case($_MODULES);
			if (defined('_THEME_NAME_'))
				$currentKey = '<{'.strtolower($name).'}'.strtolower(_THEME_NAME_).'>'.strtolower($source).'_'.md5($string);
			else
				$currentKey = '<{'.strtolower($name).'}default>'.strtolower($source).'_'.md5($string);
			// note : we should use a variable to define the default theme (instead of "prestashop")
			$defaultKey = '<{'.strtolower($name).'}prestashop>'.strtolower($source).'_'.md5($string);
			$currentKey = $defaultKey;

			if (isset($_MODULES[$currentKey]))
				$ret = stripslashes($_MODULES[$currentKey]);
			elseif (isset($_MODULES[strtolower($currentKey)]))
				$ret = stripslashes($_MODULES[strtolower($currentKey)]);
			elseif (isset($_MODULES[$defaultKey]))
				$ret = stripslashes($_MODULES[$defaultKey]);
			elseif (isset($_MODULES[strtolower($defaultKey)]))
				$ret = stripslashes($_MODULES[strtolower($defaultKey)]);
			else
				$ret = stripslashes($string);

			self::$l_cache[$cache_key] = $ret;
		}
		return self::$l_cache[$cache_key];
	}

	/**
	 * function to set configuration fields display
	 *
	 * @return void
	 */
	private function _setFields()
	{
		$this->_fieldsBackupOptions['PS_AUTOUP_BACKUP'] = array(
			'title' => $this->l('Backup my files and database'), 'cast' => 'intval', 'validation' => 'isBool', 'defaultValue' => '1',
			'type' => 'bool', 'desc' => $this->l('Automatically backup your database and files in order to restore your shop if needed (experimental, an additional manual backup is still required)'),
		);
		$this->_fieldsBackupOptions['PS_AUTOUP_KEEP_IMAGES'] = array(
			'title' => $this->l('Backup my images'), 'cast' => 'intval', 'validation' => 'isBool', 'defaultValue' => '1',
			'type' => 'bool', 'desc' => $this->l('To save time, you can decide not to backup your images. Anyway, always make sure you backuped them manually'),
		);

		$this->_fieldsUpgradeOptions['PS_AUTOUP_PERFORMANCE'] = array(
			'title' => $this->l('Server performance'), 'cast' => 'intval', 'validation' => 'isInt', 'defaultValue' => '1',
			'type' => 'select', 'desc' => $this->l('Unless you are using a dedicated server, select "Low".').'<br />'.
			$this->l('A high value can cause the upgrade to fail if your server is not fast enough to process the upgrade tasks in a short amount of time.'),
			'choices' => array(1 => $this->l('Low (recommended)'), 2 => $this->l('Medium'), 3 => $this->l('High'))
		);
		
		$this->_fieldsUpgradeOptions['PS_AUTOUP_CUSTOM_MOD_DESACT'] = array(
			'title' => $this->l('Disable non-native modules'), 'cast' => 'intval', 'validation' => 'isBool',
			'type' => 'bool', 'desc' => $this->l('As non-native modules can experience some compatibility issues, we recommend to disable them by default.').'<br />'.
			$this->l('Keeping them enabled might prevent you from loading properly the "Modules" tab after the upgrade'),
		);

		$this->_fieldsUpgradeOptions['PS_AUTOUP_UPDATE_DEFAULT_THEME'] = array(
			'title' => $this->l('Upgrade the "default" theme'), 'cast' => 'intval', 'validation' => 'isBool', 'defaultValue' => '1',
			'type' => 'bool', 'desc' => $this->l('This will upgrade the theme named "default" (PrestaShop default theme).').'<br />'.$this->l('If you are using this theme and customized it, you will loose your modifications.'),
		);
		
		$this->_fieldsUpgradeOptions['PS_AUTOUP_KEEP_MAILS'] = array(
			'title' => $this->l('Upgrade the default e-mails'), 'cast' => 'intval', 'validation' => 'isBool',
			'type' => 'bool', 'desc' => $this->l('This will upgrade the default PrestaShop e-mails.').'<br />'.$this->l('If you customized them, you will loose your modifications.'),
		);
		
		/* Developers only options */
		if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_)
		{
			$this->_fieldsUpgradeOptions['PS_AUTOUP_MANUAL_MODE'] = array(
				'title' => $this->l('Step by step mode'), 'cast' => 'intval', 'validation' => 'isBool',
				'type' => 'bool', 'desc' => $this->l('Allows to perform the upgrade step by step (debug mode).'),
			);
			
			$this->_fieldsUpgradeOptions['PS_DISPLAY_ERRORS'] = array(
				'title' => $this->l('Display PHP errors'), 'cast' => 'intval', 'validation' => 'isBool', 'defaultValue' => '0',
				'type' => 'bool', 'desc' => $this->l('This option will keep "display_errors" to On (or force it).').'<br />'.
											$this->l('This is not recommended as the upgrade will immediately fail if a PHP error occurs during an ajax call.'),
			);
		}
	}

	public function configOk()
	{
		$allowed_array = $this->getCheckCurrentPsConfig();
		$allowed = array_product($allowed_array);
		return $allowed;
	}

	public function getCheckCurrentPsConfig()
	{
		static $allowed_array;

		if(empty($allowed_array))
		{
			$allowed_array = array();
			$allowed_array['fopen'] = ConfigurationTest::test_fopen() || ConfigurationTest::test_curl();
			$allowed_array['root_writable'] = $this->getRootWritable();
			$allowed_array['shop_deactivated'] = (!Configuration::get('PS_SHOP_ENABLE') || (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], array('127.0.0.1', 'localhost'))));
			$allowed_array['cache_deactivated'] = !(defined('_PS_CACHE_ENABLED_') && _PS_CACHE_ENABLED_);

			$allowed_array['module_version_ok'] = $this->checkAutoupgradeLastVersion();
		}
		return $allowed_array;
	}

	public function getRootWritable()
	{
		// Root directory permissions cannot be checked recursively anymore, it takes too much time
		$this->root_writable =  ConfigurationTest::test_dir('/', false, $report);
		$this->root_writable_report = $report;

		return $this->root_writable;
	}

	public function getModuleVersion()
	{
		if (is_null($this->module_version))
		{
			if (file_exists(_PS_ROOT_DIR_.'/modules/autoupgrade/config.xml')
				&& $xml_module_version = simplexml_load_file(_PS_ROOT_DIR_.'/modules/autoupgrade/config.xml')
			)
				$this->module_version = (string)$xml_module_version->version;
			else
				$this->module_version = false;
		}
		return $this->module_version;
	}

	public function checkAutoupgradeLastVersion()
	{
		if ($this->getModuleVersion())
			$this->lastAutoupgradeVersion = version_compare($this->module_version, $this->upgrader->autoupgrade_last_version, '>=');
		else
			$this->lastAutoupgradeVersion = true;

		return $this->lastAutoupgradeVersion;
	}

	public function cleanTmpFiles()
	{
		foreach($this->tmp_files as $tmp_file)
			if (file_exists($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->$tmp_file))
				@unlink($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->$tmp_file);
	}

	/**
	 * init to build informations we need
	 *
	 * @return void
	 */
	public function init()
	{
		// For later use, let's set up prodRootDir and adminDir
		// This way it will be easier to upgrade a different path if needed
		$this->prodRootDir = _PS_ROOT_DIR_;
		$this->adminDir = _PS_ADMIN_DIR_;
		if (!defined('__PS_BASE_URI__'))
		{
			// _PS_DIRECTORY_ replaces __PS_BASE_URI__ in 1.5
			if (defined('_PS_DIRECTORY_'))
				define('__PS_BASE_URI__', _PS_DIRECTORY_);
			else
				define('__PS_BASE_URI__', realpath(dirname($_SERVER['SCRIPT_NAME'])).'/../../');
		}
		// from $_POST or $_GET
		$this->action = empty($_REQUEST['action'])?null:$_REQUEST['action'];
		$this->currentParams = empty($_REQUEST['params'])?null:$_REQUEST['params'];
		// test writable recursively
		if(version_compare(_PS_VERSION_,'1.4.6.0','<') || !class_exists('ConfigurationTest', false))
		{
			require_once(dirname(__FILE__).'/classes/ConfigurationTest.php');
			if(!class_exists('ConfigurationTest', false) AND class_exists('ConfigurationTestCore'))
				eval('class ConfigurationTest extends ConfigurationTestCore{}');
		}
		$this->initPath();
		$upgrader = new Upgrader();
		preg_match('#([0-9]+\.[0-9]+)(?:\.[0-9]+){1,2}#', _PS_VERSION_, $matches);
		$upgrader->branch = $matches[1];
		$channel = $this->getConfig('channel');
		switch ($channel)
		{
			case 'archive':
				$this->install_version = $this->getConfig('archive.version_num');
				$this->destDownloadFilename = $this->getConfig('archive.filename');
				break;
			case 'directory';
			$this->install_version = $this->getConfig('directory.version_num');
			break;
			default:
				$upgrader->channel = $channel;
				if ($this->getConfig('channel') == 'private' && !$this->getConfig('private_allow_major'))
					$upgrader->checkPSVersion(false, array('private', 'minor'));
				else
					$upgrader->checkPSVersion(false, array('minor'));
				$this->install_version = $upgrader->version_num;
		}
		// If you have defined this somewhere, you know what you do
		/* load options from configuration if we're not in ajax mode */
		if (!$this->ajax)
		{
			$this->createCustomToken();

			$postData = 'version='._PS_VERSION_.'&method=listing&action=native&iso_code=all';
			$xml_local = $this->prodRootDir.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'modules_native_addons.xml';
			$xml = $upgrader->getApiAddons($xml_local, $postData, true);

			if (is_object($xml))
				foreach ($xml as $mod)
					$this->modules_addons[(string)$mod->id] = (string)$mod->name;

			// installedLanguagesIso is used to merge translations files
			$iso_ids = Language::getIsoIds(false);
			foreach($iso_ids as $v)
				$this->installedLanguagesIso[] = $v['iso_code'];

			$rand = dechex ( mt_rand(0, min(0xffffffff, mt_getrandmax() ) ) );
			$date = date('Ymd-His');
			$this->backupName = 'V'._PS_VERSION_.'_'.$date.'-'.$rand;
			$this->backupFilesFilename = 'auto-backupfiles_'.$this->backupName.'.zip';
			$this->backupDbFilename = 'auto-backupdb_XXXXXX_'.$this->backupName.'.sql';
			// removing temporary files
			$this->cleanTmpFiles();
		}
		else
		{
			foreach($this->ajaxParams as $prop)
				if(property_exists($this, $prop))
					$this->{$prop} = isset($this->currentParams[$prop])?$this->currentParams[$prop]:'';
		}

		$this->keepImages = $this->getConfig('PS_AUTOUP_KEEP_IMAGES');
		$this->updateDefaultTheme = $this->getConfig('PS_AUTOUP_UPDATE_DEFAULT_THEME');
		$this->keepMails = $this->getConfig('PS_AUTOUP_KEEP_MAILS');
		$this->manualMode = $this->getConfig('PS_AUTOUP_MANUAL_MODE');
		$this->deactivateCustomModule = $this->getConfig('PS_AUTOUP_CUSTOM_MOD_DESACT');

		// during restoration, do not remove :
		$this->restoreIgnoreAbsoluteFiles[] = '/config/settings.inc.php';
		$this->restoreIgnoreAbsoluteFiles[] = '/modules/autoupgrade';
		$this->restoreIgnoreAbsoluteFiles[] = '/admin/autoupgrade';
		$this->restoreIgnoreAbsoluteFiles[] = '.';
		$this->restoreIgnoreAbsoluteFiles[] = '..';

		// during backup, do not save
		$this->backupIgnoreAbsoluteFiles[] = '/tools/smarty_v2/compile';
		$this->backupIgnoreAbsoluteFiles[] = '/tools/smarty_v2/cache';
		$this->backupIgnoreAbsoluteFiles[] = '/tools/smarty/compile';
		$this->backupIgnoreAbsoluteFiles[] = '/tools/smarty/cache';
		$this->backupIgnoreAbsoluteFiles[] = '/cache/smarty/compile';
		$this->backupIgnoreAbsoluteFiles[] = '/cache/smarty/cache';
		$this->backupIgnoreAbsoluteFiles[] = '/cache/tcpdf';
		$this->backupIgnoreAbsoluteFiles[] = '/cache/cachefs';

		// do not care about the two autoupgrade dir we use;
		$this->backupIgnoreAbsoluteFiles[] = '/modules/autoupgrade';
		$this->backupIgnoreAbsoluteFiles[] = '/admin/autoupgrade';

		$this->backupIgnoreFiles[] = '.';
		$this->backupIgnoreFiles[] = '..';
		$this->backupIgnoreFiles[] = '.svn';
		$this->backupIgnoreFiles[] = 'autoupgrade';

		$this->excludeFilesFromUpgrade[] = '.';
		$this->excludeFilesFromUpgrade[] = '..';
		$this->excludeFilesFromUpgrade[] = '.svn';
		// do not copy install, neither settings.inc.php in case it would be present
		$this->excludeAbsoluteFilesFromUpgrade[] = '/config/settings.inc.php';
		$this->excludeAbsoluteFilesFromUpgrade[] = '/install';
		$this->excludeAbsoluteFilesFromUpgrade[] = '/install-dev';
		$this->excludeAbsoluteFilesFromUpgrade[] = '/config/modules_list.xml';
		$this->excludeAbsoluteFilesFromUpgrade[] = '/config/xml/modules_list.xml';		
		// this will exclude autoupgrade dir from admin, and autoupgrade from modules
		$this->excludeFilesFromUpgrade[] = 'autoupgrade';

		if ($this->keepImages === '0')
		{
			$this->backupIgnoreAbsoluteFiles[] = '/img';
			$this->restoreIgnoreAbsoluteFiles[] = '/img';
		}
		else
		{
			$this->backupIgnoreAbsoluteFiles[] = '/img/tmp';
			$this->restoreIgnoreAbsoluteFiles[] = '/img/tmp';
		}

		if (!$this->updateDefaultTheme) /* If set to false, we need to preserve the default themes */
		{
			$this->excludeAbsoluteFilesFromUpgrade[] = '/themes/prestashop';
			$this->excludeAbsoluteFilesFromUpgrade[] = '/themes/default';
		}
	}

	/**
	 * create some required directories if they does not exists
	 *
	 * Also set nextParams (removeList and filesToUpgrade) if they
	 * exists in currentParams
	 *
	 */
	public function initPath()
	{
		// If not exists in this sessions, "create"
		// session handling : from current to next params
		if (isset($this->currentParams['removeList']))
			$this->nextParams['removeList'] = $this->currentParams['removeList'];

		if (isset($this->currentParams['filesToUpgrade']))
			$this->nextParams['filesToUpgrade'] = $this->currentParams['filesToUpgrade'];

		if (isset($this->currentParams['modulesToUpgrade']))
			$this->nextParams['modulesToUpgrade'] = $this->currentParams['modulesToUpgrade'];

		// set autoupgradePath, to be used in backupFiles and backupDb config values
		$this->autoupgradePath = $this->adminDir.DIRECTORY_SEPARATOR.$this->autoupgradeDir;
		// directory missing
		if (!file_exists($this->autoupgradePath))
			if (!@mkdir($this->autoupgradePath))
				$this->_errors[] = sprintf($this->l('unable to create directory %s'),$this->autoupgradePath);

		$this->downloadPath = $this->autoupgradePath.DIRECTORY_SEPARATOR.'download';
		if (!file_exists($this->downloadPath))
			if (!@mkdir($this->downloadPath))
				$this->_errors[] = sprintf($this->l('unable to create directory %s'),$this->downloadPath);			

		$this->backupPath = $this->autoupgradePath.DIRECTORY_SEPARATOR.'backup';
		if (!file_exists($this->backupPath))
			if (!@mkdir($this->backupPath))
				$this->_errors[] = sprintf($this->l('unable to create directory %s'),$this->backupPath);				

		// directory missing
		$this->latestPath = $this->autoupgradePath.DIRECTORY_SEPARATOR.'latest';
		if (!file_exists($this->latestPath))
			if (!@mkdir($this->latestPath))
				$this->_errors[] = sprintf($this->l('unable to create directory %s'),$this->latestPath);				

		$this->tmpPath = $this->autoupgradePath.DIRECTORY_SEPARATOR.'tmp';
		if (!file_exists($this->tmpPath))
			if (!@mkdir($this->tmpPath))
				$this->_errors[] = sprintf($this->l('unable to create directory %s'),$this->tmpPath);				

		$this->latestRootDir = $this->latestPath.DIRECTORY_SEPARATOR.'prestashop';
	}

	/**
	 * getFilePath return the path to the zipfile containing prestashop.
	 *
	 * @return void
	 */
	private function getFilePath()
	{
		return $this->downloadPath.DIRECTORY_SEPARATOR.$this->destDownloadFilename;
	}

	public function postProcess()
	{
		$this->_setFields();

		// set default configuration to default channel & dafault configuration for backup and upgrade 
		// (can be modified in expert mode)
		$config = $this->getConfig('channel');
		if ($config === false)
		{
			$config = array();
			$config['channel'] = Upgrader::DEFAULT_CHANNEL;
			$this->writeConfig($config);
			if (class_exists('Configuration', false))
				Configuration::updateValue('PS_UPGRADE_CHANNEL', $config['channel']);
		
			$this->writeConfig(array (
				'PS_AUTOUP_PERFORMANCE' => '1',
				'PS_AUTOUP_CUSTOM_MOD_DESACT' => '0', 
				'PS_AUTOUP_UPDATE_DEFAULT_THEME' => '1',
				'PS_AUTOUP_KEEP_MAILS' => '0',
				'PS_AUTOUP_BACKUP' => '1',
				'PS_AUTOUP_KEEP_IMAGES' => '1'
				));
		}

		if (Tools14::isSubmit('putUnderMaintenance'))
			Configuration::updateValue('PS_SHOP_ENABLE', 0);
		
		if (Tools14::isSubmit('customSubmitAutoUpgrade'))
		{
			$config_keys = array_keys(array_merge($this->_fieldsUpgradeOptions, $this->_fieldsBackupOptions));
			$config = array();
			foreach ($config_keys as $key)
				if (isset($_POST[$key]))
					$config[$key] = $_POST[$key];
			$res = $this->writeConfig($config);
			if ($res)
				Tools14::redirectAdmin($this->currentIndex.'&conf=6&token='.Tools14::getValue('token'));
		}

		if (Tools14::isSubmit('deletebackup'))
		{
			$res = true;
			$name = Tools14::getValue('name');
			$filelist = scandir($this->backupPath);
			foreach($filelist as $filename)
				// the following will match file or dir related to the selected backup
				if (preg_match('#^.*'.preg_quote($name).'.*$#', $filename, $matches))
				{
					if (is_file($this->backupPath.DIRECTORY_SEPARATOR.$filename))
						$res &= @unlink($this->backupPath.DIRECTORY_SEPARATOR.$filename);

					if (!empty($name) && is_dir($this->backupPath.DIRECTORY_SEPARATOR.$name))
						self::deleteDirectory($this->backupPath.DIRECTORY_SEPARATOR.$name);
				}
			if ($res)
				Tools14::redirectAdmin($this->currentIndex.'&conf=1&token='.Tools14::getValue('token'));
			else
				$this->_errors[] = sprintf($this->l('Error when trying to delete backups %s'), $name);
		}
		parent::postProcess();
	}

	/**
	 * ends the rollback process
	 *
	 * @return void
	 */
	public function ajaxProcessRollbackComplete()
	{
		$this->next_desc = $this->l('Restoration process done. Congratulations ! You can now reactive your shop.');
		$this->next = '';
	}

	/**
	 * ends the upgrade process
	 *
	 * @return void
	 */
	public function ajaxProcessUpgradeComplete()
	{
		if (version_compare($this->install_version, '1.5.4.0', '>='))
		{
			// Upgrade languages
			if (!defined('_PS_TOOL_DIR_'))
				define('_PS_TOOL_DIR_', _PS_ROOT_DIR_.'/tools/');
			if (!defined('_PS_TRANSLATIONS_DIR_'))
				define('_PS_TRANSLATIONS_DIR_', _PS_ROOT_DIR_.'/translations/');
			if (!defined('_PS_MODULES_DIR_'))
				define('_PS_MODULES_DIR_', _PS_ROOT_DIR_.'/modules/');
			if (!defined('_PS_MAILS_DIR_'))
				define('_PS_MAILS_DIR_', _PS_ROOT_DIR_.'/mails/');
			$langs = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'lang WHERE active=1');
			require_once(_PS_TOOL_DIR_.'tar/Archive_Tar.php');
			foreach ($langs as $lang)
			{
				$lang_pack = Tools14::jsonDecode(Tools::file_get_contents('http'.(extension_loaded('openssl')? 's' : '').'://www.prestashop.com/download/lang_packs/get_language_pack.php?version='.$this->install_version.'&iso_lang='.$lang['iso_code']));

				if (!$lang_pack)
					continue;
				elseif ($content = Tools14::file_get_contents('http'.(extension_loaded('openssl')? 's' : '').'://translations.prestashop.com/download/lang_packs/gzip/'.$lang_pack->version.'/'.$lang['iso_code'].'.gzip'))
				{
					$file = _PS_TRANSLATIONS_DIR_.$lang['iso_code'].'.gzip';
					if ((bool)@file_put_contents($file, $content))
					{
						$gz = new Archive_Tar($file, true);
						$files_list = $gz->listContent();					
						if (!$this->keepMails)
						{
							foreach($files_list as $i => $file)
								if (preg_match('/^mails\/'.$lang['iso_code'].'\/.*/', $file['filename']))
									unset($files_list[$i]);
							foreach($files_list as $file)
							if (isset($file['filename']) && is_string($file['filename']))
								$files_listing[] = $file['filename'];
							if (is_array($files_listing) && !$gz->extractList($files_listing, _PS_TRANSLATIONS_DIR_.'../', ''))
								continue;
						}
						elseif (!$gz->extract(_PS_TRANSLATIONS_DIR_.'../', false))
								continue;
					}
				}
			}
			// Remove class_index Autoload cache
			@unlink(_PS_ROOT_DIR_.'/cache/class_index.php');
		}
		
		if (!$this->warning_exists)
			$this->next_desc = $this->l('Upgrade process done. Congratulations ! You can now reactive your shop.');
		else
			$this->next_desc = $this->l('Upgrade process done, but some warnings has been found.');
		$this->next = '';
		
		$conf_clear = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'configuration` WHERE `name` = \'PS_UPGRADE_CLEAR_CACHE\' ');
		if (!$conf_clear)//set this value to 1 after upgrade process to 
			Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'configuration` 
				(`name`, `value`, `date_add`, `date_upd`) 
				VALUES (\'PS_UPGRADE_CLEAR_CACHE\', 1, NOW(), NOW())');
		else
			Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'configuration` SET value=1 WHERE name=\'PS_UPGRADE_CLEAR_CACHE\' ');
	}

	// Simplification of _displayForm original function
	protected function _displayForm($name, $fields, $tabname, $size, $icon)
	{
		$confValues = $this->getConfig();
		$required = false;

		$this->_html .= '
			<fieldset id="'.$name.'Block"><legend><img src="../img/admin/'.strval($icon).'.gif" />'.$tabname.'</legend>';
		foreach ($fields as $key => $field)
		{
			if (isset($field['required']) && $field['required'])
				$required = true;

			if (isset($field['disabled']) && $field['disabled'])
				$disabled = true;
			else
				$disabled = false;


			if (isset($confValues[$key]))
				$val = $confValues[$key];
			else
				$val = isset($field['defaultValue'])?$field['defaultValue']:false;

			if (!in_array($field['type'], array('image', 'radio', 'container', 'container_end')) || isset($field['show']))
				$this->_html .= '<div style="clear: both; padding-top:15px;">'.($field['title'] ? '<label >'.$field['title'].'</label>' : '').'<div class="margin-form" style="padding-top:5px;">';

			/* Display the appropriate input type for each field */
			switch ($field['type'])
			{
				case 'disabled':
					$this->_html .= $field['disabled'];
					break;


				case 'bool':
					$this->_html .= '<label class="t" for="'.$key.'_on">
						<img src="../img/admin/enabled.gif" alt="'.$this->l('Yes').'" title="'.$this->l('Yes').'" /></label>
					<input type="radio" '.($disabled?'disabled="disabled"':'').' name="'.$key.'" id="'.$key.'_on" value="1"'.($val ? ' checked="checked"' : '').(isset($field['js']['on']) ? $field['js']['on'] : '').' />
					<label class="t" for="'.$key.'_on"> '.$this->l('Yes').'</label>
					<label class="t" for="'.$key.'_off"><img src="../img/admin/disabled.gif" alt="'.$this->l('No').'" title="'.$this->l('No').'" style="margin-left: 10px;" /></label>
					<input type="radio" '.($disabled?'disabled="disabled"':'').' name="'.$key.'" id="'.$key.'_off" value="0" '.(!$val ? 'checked="checked"' : '').(isset($field['js']['off']) ? $field['js']['off'] : '').'/>
					<label class="t" for="'.$key.'_off"> '.$this->l('No').'</label>';
					break;

				case 'radio':
					foreach ($field['choices'] as $cValue => $cKey)
						$this->_html .= '<input '.($disabled?'disabled="disabled"':'').' type="radio" name="'.$key.'" id="'.$key.$cValue.'_on" value="'.(int)($cValue).'"'.(($cValue == $val) ? ' checked="checked"' : '').(isset($field['js'][$cValue]) ? ' '.$field['js'][$cValue] : '').' /><label class="t" for="'.$key.$cValue.'_on"> '.$cKey.'</label><br />';
					$this->_html .= '<br />';
					break;

				case 'select':
					$this->_html .= '<select name='.$key.'>';
					foreach ($field['choices'] as $cValue => $cKey)
						$this->_html .= '<option value="'.(int)$cValue.'"'.(($cValue == $val) ? ' selected="selected"' : '').'>'.$cKey.'</option>';
					$this->_html .= '</select>';
					break;

				case 'textarea':
					$this->_html .= '<textarea '.($disabled?'disabled="disabled"':'').' name='.$key.' cols="'.$field['cols'].'" rows="'.$field['rows'].'">'.htmlentities($val, ENT_COMPAT, 'UTF-8').'</textarea>';
					break;

				case 'container':
					$this->_html .= '<div id="'.$key.'">';
					break;

				case 'container_end':
					$this->_html .= (isset($field['content']) === true ? $field['content'] : '').'</div>';
					break;

				case 'text':
				default:
					$this->_html .= '<input '.($disabled?'disabled="disabled"':'').' type="'.$field['type'].'"'.(isset($field['id']) === true ? ' id="'.$field['id'].'"' : '').' size="'.(isset($field['size']) ? (int)($field['size']) : 5).'" name="'.$key.'" value="'.($field['type'] == 'password' ? '' : htmlentities($val, ENT_COMPAT, 'UTF-8')).'" />'.(isset($field['next']) ? '&nbsp;'.strval($field['next']) : '');
			}
			$this->_html .= ((isset($field['required']) && $field['required'] && !in_array($field['type'], array('image', 'radio')))  ? ' <sup>*</sup>' : '');
			$this->_html .= (isset($field['desc']) ? '<p style="clear:both">'.((isset($field['thumb']) && $field['thumb'] && $field['thumb']['pos'] == 'after') ? '<img src="'.$field['thumb']['file'].'" alt="'.$field['title'].'" title="'.$field['title'].'" style="float:left;" />' : '' ).$field['desc'].'</p>' : '');
			if (!in_array($field['type'], array('image', 'radio', 'container', 'container_end')) || isset($field['show']))
				$this->_html .= '</div></div>';
		}

		$this->_html .= '	<div align="center" style="margin-top: 20px;">
					<input type="submit" value="'.$this->l('   Save   ', 'AdminPreferences').'" name="customSubmitAutoUpgrade" class="button" />
				</div>
				'.($required ? '<div class="small"><sup>*</sup> '.$this->l('Required field', 'AdminPreferences').'</div>' : '').'
			</fieldset>
			<br/>';
	}

	/**
	 * return the value of $key, configuration saved in $this->configFilename.
	 * if $key is empty, will return an array with all configuration;
	 *
	 * @param string $key
	 * @access public
	 * @return array or string
	 */
	public function getConfig($key = '')
	{
		static $config = array();
		if (count($config) == 0)
		{
			if (file_exists($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->configFilename))
			{
				$config_content = Tools14::file_get_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->configFilename);
				$config = unserialize($config_content);
			}
			else
				$config = array();
		}
		if (empty($key))
			return $config;
		elseif (isset($config[$key]))
			return trim($config[$key]);
		return false;
	}

	/**
	 * reset module configuration with $new_config values (previous config will be totally lost)
	 *
	 * @param array $new_config
	 * @return boolean true if success
	 */
	public function resetConfig($new_config)
	{
		return (bool)@file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->configFilename, serialize($new_config));
	}

	/**
	 * update module configuration (saved in file $this->configFilename) with $new_config
	 *
	 * @param array $new_config
	 * @return boolean true if success
	 */
	public function writeConfig($new_config)
	{
		if (!file_exists($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->configFilename))
			return $this->resetConfig($new_config);

		$config = file_get_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->configFilename);
		$config = unserialize($config);
		foreach($new_config as $key => $val)
			$config[$key] = $val;
		$this->next_desc = $this->l('Configuration successfully updated,').' <strong>'.$this->l('this page will now be reloaded and the module will check if a new version is available').'</strong>';
		return (bool)@file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->configFilename, serialize($config));
	}

	/**
	 * update configuration after validating the new values
	 *
	 * @access public
	 */
	public function ajaxProcessUpdateConfig()
	{
		$config = array();
		// nothing next
		$this->next = '';
		// update channel
		if (isset($this->currentParams['channel']))
			$config['channel'] = $this->currentParams['channel'];
		if (isset($this->currentParams['private_release_link']) && isset($this->currentParams['private_release_md5']))
		{
			$config['channel'] = 'private';
			$config['private_release_link'] = $this->currentParams['private_release_link'];
			$config['private_release_md5'] = $this->currentParams['private_release_md5'];
			$config['private_allow_major'] = $this->currentParams['private_allow_major'];
		}
		// if (!empty($this->currentParams['archive_name']) && !empty($this->currentParams['archive_num']))
		if (!empty($this->currentParams['archive_prestashop']))
		{
			$file = $this->currentParams['archive_prestashop'];
			if (!file_exists($this->downloadPath.DIRECTORY_SEPARATOR.$file))
			{
				$this->error = 1;
				$this->next_desc = sprintf($this->l('file %s does not exists. Unable to select that channel.'), $file);
				return false;
			}
			if (empty($this->currentParams['archive_num']))
			{
				$this->error = 1;
				$this->next_desc = sprintf($this->l('version number is missing. Unable to select that channel.'), $file);
				return false;
			}
			$config['channel'] = 'archive';
			$config['archive.filename'] = $this->currentParams['archive_prestashop'];
			$config['archive.version_num'] = $this->currentParams['archive_num'];
			// $config['archive_name'] = $this->currentParams['archive_name'];
			$this->next_desc = $this->l('Upgrade process will use archive.');
		}
		if (isset($this->currentParams['directory_num']))
		{
			$config['channel'] = 'directory';
			if (empty($this->currentParams['directory_num']))
			{
				$this->error = 1;
				$this->next_desc = sprintf($this->l('version number is missing. Unable to select that channel.'));
				return false;
			}

			$config['directory.version_num'] = $this->currentParams['directory_num'];
		}
		if (isset($this->currentParams['skip_backup']))
			$config['skip_backup'] = $this->currentParams['skip_backup'];

		if (!$this->writeConfig($config))
		{
			$this->error = 1;
			$this->next_desc = $this->l('Error on saving configuration');
		}

	}
	/** returns an array containing information related to the channel $channel
	 *
	 * @param string $channel name of the channel
	 * @return <array> available, version_num, version_name, link, md5, changelog
	 */
	public function getInfoForChannel($channel)
	{
		$upgrade_info = array();
		$public_channel = array('minor', 'major', 'rc', 'beta', 'alpha');
		$upgrader = new Upgrader();
		preg_match('#([0-9]+\.[0-9]+)(?:\.[0-9]+){1,2}#', _PS_VERSION_, $matches);
		$upgrader->branch = $matches[1];
		$upgrader->channel = $channel;
		if (in_array($channel, $public_channel))
		{
			if ($this->getConfig('channel') == 'private' && !$this->getConfig('private_allow_major'))
				$upgrader->checkPSVersion(false, array('private', 'minor'));
			else
				$upgrader->checkPSVersion(false, array('minor'));

			$upgrade_info = array();
			$upgrade_info['branch'] = $upgrader->branch;
			$upgrade_info['available'] = $upgrader->available;
			$upgrade_info['version_num'] = $upgrader->version_num;
			$upgrade_info['version_name'] = $upgrader->version_name;
			$upgrade_info['link'] = $upgrader->link;
			$upgrade_info['md5'] = $upgrader->md5;
			$upgrade_info['changelog'] = $upgrader->changelog;
		}
		else
		{
			switch ($channel)
			{
				case 'private':
					if (!$this->getConfig('private_allow_major'))
						$upgrader->checkPSVersion(false, array('private', 'minor'));
					else
						$upgrader->checkPSVersion(false, array('minor'));

					$upgrade_info['available'] = $upgrader->available;
					$upgrade_info['branch'] = $upgrader->branch;
					$upgrade_info['version_num'] = $upgrader->version_num;
					$upgrade_info['version_name'] = $upgrader->version_name;
					$upgrade_info['link'] = $this->getConfig('private_release_link');
					$upgrade_info['md5'] = $this->getConfig('private_release_md5');
					$upgrade_info['changelog'] = $upgrader->changelog;
					break;
				case 'archive':
					$upgrade_info['available'] = true;
					break;
				case 'directory':
					$upgrade_info['available'] = true;
					break;
			}
		}
		return $upgrade_info;
	}

	/**
	 * display informations related to the selected channel : link/changelog for remote channel,
	 * or configuration values for special channels
	 *
	 * @access public
	 */
	public function ajaxProcessGetChannelInfo()
	{
		// do nothing after this request (see javascript function doAjaxRequest )
		$this->next = '';

		$channel = $this->currentParams['channel'];
		$upgrade_info = $this->getInfoForChannel($channel);
		$this->nextParams['result']['available'] =  $upgrade_info['available'];

		$this->nextParams['result']['div'] = $this->divChannelInfos($upgrade_info);

	}

	/**
	 * get the list of all modified and deleted files between current version
	 * and target version (according to channel configuration)
	 *
	 * @access public
	 */
	public function ajaxProcessCompareReleases()
	{
		// do nothing after this request (see javascript function doAjaxRequest )
		$this->next = '';
		$channel = $this->getConfig('channel');
		$this->upgrader = new Upgrader();
		switch ($channel)
		{
			case 'archive':
				$version = $this->getConfig('archive.version_num');
				break;
			case 'directory':
				$version = $this->getConfig('directory.version_num');
				break;
			default:
				preg_match('#([0-9]+\.[0-9]+)(?:\.[0-9]+){1,2}#', _PS_VERSION_, $matches);
				$this->upgrader->branch = $matches[1];
				$this->upgrader->channel = $channel;
				if ($this->getConfig('channel') == 'private' && !$this->getConfig('private_allow_major'))
					$this->upgrader->checkPSVersion(false, array('private', 'minor'));
				else
					$this->upgrader->checkPSVersion(false, array('minor'));
				$version = $this->upgrader->version_num;
		}

		$diffFileList = $this->upgrader->getDiffFilesList(_PS_VERSION_, $version);
		if (!is_array($diffFileList))
		{
			$this->nextParams['status'] = 'error';
			$this->nextParams['msg'] = sprintf('Unable to generate diff file list between %1$s and %2$s.', _PS_VERSION_, $version);
		}
		else
		{
			@file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->diffFileList, serialize($diffFileList));
			if (count($diffFileList) > 0)
				$this->nextParams['msg'] = sprintf($this->l('%1$s files will be modified, %2$s files will be deleted (if they are found).'),
																	 count($diffFileList['modified']), count($diffFileList['deleted']));
			else
				$this->nextParams['msg'] = $this->l('No diff files found.');
			$this->nextParams['result'] = $diffFileList;
		}
	}

	/**
	 * list the files modified in the current installation regards to the original version
	 *
	 * @access public
	 */
	public function ajaxProcessCheckFilesVersion()
	{
		// do nothing after this request (see javascript function doAjaxRequest )
		$this->next = '';
		$this->upgrader = new Upgrader();

		$changedFileList = $this->upgrader->getChangedFilesList();
		if ($this->upgrader->isAuthenticPrestashopVersion() === true
			&& !is_array($changedFileList) )
		{
			$this->nextParams['status'] = 'error';
			$this->nextParams['msg'] = 'Unable to check files for the installed PrestaShop version';
			$testOrigCore = false;
		}
		else
		{
			if ($this->upgrader->isAuthenticPrestashopVersion() === true)
			{
				$this->nextParams['status'] = 'ok';
				$testOrigCore = true;
			}
			else
			{
				$testOrigCore = false;
				$this->nextParams['status'] = 'warn';
			}

			if (!isset($changedFileList['core']))
				$changedFileList['core'] = array();

			if (!isset($changedFileList['translation']))
				$changedFileList['translation'] = array();
			@file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->tradCustomList,serialize($changedFileList['translation']));

			if (!isset($changedFileList['mail']))
				$changedFileList['mail'] = array();
			@file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->mailCustomList,serialize($changedFileList['mail']));


			if ($changedFileList === false)
			{
				$changedFileList = array();
				$this->nextParams['msg'] = $this->l('Unable to check files');
				$this->nextParams['status'] = 'error';
			}
			else
			{
				$this->nextParams['msg'] = ($testOrigCore ? $this->l('Core files are ok') : sprintf($this->l('%1$s files modifications has been detected, including %2$s from core and native module:'), count(array_merge($changedFileList['core'], $changedFileList['mail'], $changedFileList['translation'])), count($changedFileList['core'])
																	 ));
			}
			$this->nextParams['result'] = $changedFileList;
		}
	}

	/**
	 * very first step of the upgrade process. The only thing done is the selection
	 * of the next step
	 *
	 * @access public
	 * @return void
	 */
	public function ajaxProcessUpgradeNow()
	{
		$this->next_desc = $this->l('Starting upgrade ...');

		$channel = $this->getConfig('channel');
		$this->next = 'download';
		if (!is_object($this->upgrader))
			$this->upgrader = new Upgrader();
		preg_match('#([0-9]+\.[0-9]+)(?:\.[0-9]+){1,2}#', _PS_VERSION_, $matches);
		$this->upgrader->branch = $matches[1];
		$this->upgrader->channel = $channel;
		if ($this->getConfig('channel') == 'private' && !$this->getConfig('private_allow_major'))
			$this->upgrader->checkPSVersion(false, array('private', 'minor'));
		else
			$this->upgrader->checkPSVersion(false, array('minor'));

		switch ($channel)
		{
			case 'directory' :
				// if channel directory is choosen, we assume it's "ready for use" (samples already removed for example)
				$this->next = 'removeSamples';
				$this->nextQuickInfo[] = $this->l('Skip download and unzip, will now remove samples');
				$this->next_desc = $this->l('Shop deactivated. removing sample files...');
				break;
			case 'archive' :
				$this->next = 'unzip';
				$this->nextQuickInfo[] = $this->l('Skip download step, go to unzip');
				$this->next_desc = $this->l('Shop deactivated. Extracting files ...');
				break;
			default :
				$this->next = 'download';
				$this->next_desc = $this->l('Shop deactivated. Now downloading (this can takes some times )...');
				if ($this->upgrader->channel == 'private')
				{
					$this->upgrader->link = $this->getConfig('private_release_link');
					$this->upgrader->md5 = $this->getConfig('private_release_md5');
				}
				$this->nextQuickInfo[] = sprintf($this->l('downloading from %s'), $this->upgrader->link);
				$this->nextQuickInfo[] = sprintf($this->l('md5 will be checked against %s'), $this->upgrader->md5);
		}
	}

	/**
	 * extract chosen version into $this->latestPath directory
	 *
	 * @return void
	 */
	public function ajaxProcessUnzip()
	{
		$filepath = $this->getFilePath();
		$destExtract = $this->latestPath;

		if (file_exists($destExtract))
		{
			self::deleteDirectory($destExtract, false);
			$this->nextQuickInfo[] = $this->l('latest directory has been emptied');
		}
		$relative_extract_path = str_replace(_PS_ROOT_DIR_, '', $destExtract);
		$report = '';
		if (ConfigurationTest::test_dir($relative_extract_path, false, $report))
		{
			if ($this->ZipExtract($filepath, $destExtract))
			{
				// Unsetting to force listing
				unset($this->nextParams['removeList']);
				$this->next = "removeSamples";
				$this->next_desc = $this->l('Extract complete. removing sample files...');
				return true;
			}
			else
			{
				$this->next = "error";
				$this->next_desc = sprintf($this->l('unable to extract %1$s into %2$s ...'), $filepath, $destExtract);
				return true;
			}
		}
		else
		{
			$this->next_desc = $this->l('Extract directory is not writeable ');
			$this->nextQuickInfo[] = 'Extract directory is not writeable ';
			$this->nextErrors[] = 'Extract directory is not writeable "'.$destExtract.'"';
			$this->next = 'error';
		}
	}


	/**
	 * _listSampleFiles will make a recursive call to scandir() function
	 * and list all file which match to the $fileext suffixe (this can be an extension or whole filename)
	 *
	 * @param string $dir directory to look in
	 * @param string $fileext suffixe filename
	 * @return void
	 */
	private function _listSampleFiles($dir, $fileext = '.jpg'){
		$res = true;
		$dir = rtrim($dir,'/').DIRECTORY_SEPARATOR;
		$toDel = scandir($dir);
		// copied (and kind of) adapted from AdminImages.php
		foreach ($toDel AS $file)
		{
			if ($file[0] != '.')
			{
				if (preg_match('#'.preg_quote($fileext,'#').'$#i',$file))
				{
					$this->sampleFileList[] = $dir.$file;
				}
				else if (is_dir($dir.$file))
				{
					$res &= $this->_listSampleFiles($dir.$file, $fileext);
				}
			}
		}
		return $res;
	}

	public function _listFilesInDir($dir, $way = 'backup', $list_directories = false)
	{
		$list = array();
		$allFiles = scandir($dir);
		foreach ($allFiles as $file)
			if ($file[0] != '.')
			{
				$fullPath = $dir.DIRECTORY_SEPARATOR.$file;
				if (!$this->_skipFile($file, $fullPath, $way))
				{
					if (is_dir($fullPath))
					{
						$list = array_merge($list, $this->_listFilesInDir($fullPath, $way, $list_directories));
						if ($list_directories)
							$list[] = $fullPath;
					}
					else
						$list[] = $fullPath;
				}
			}
		return $list;
	}


	/**
	 * this function list all files that will be remove to retrieve the filesystem states before the upgrade
	 *
	 * @access public
	 * @return void
	 */
	public function _listFilesToRemove()
	{
		$prev_version = preg_match('#auto-backupfiles_V([0-9.]*)_#', $this->restoreFilesFilename, $matches);
		if ($prev_version)
			$prev_version = $matches[1];

		if (!$this->upgrader)
			$this->upgrader = new Upgrader();

		$toRemove = false;
		// note : getDiffFilesList does not include files moved by upgrade scripts,
		// so this method can't be trusted to fully restore directory
		// $toRemove = $this->upgrader->getDiffFilesList(_PS_VERSION_, $prev_version, false);
		// if we can't find the diff file list corresponding to _PS_VERSION_ and prev_version,
		// let's assume to remove every files
		if (!$toRemove)
			$toRemove = $this->_listFilesInDir($this->prodRootDir, 'restore', true);

		$admin_dir = str_replace($this->prodRootDir, '', $this->adminDir);
		// if a file in "ToRemove" has been skipped during backup,
		// just keep it
		foreach ($toRemove as $key => $file)
		{
			$filename = substr($file, strrpos($file, '/')+1);
			$toRemove[$key] = preg_replace('#^/admin#', $admin_dir, $file);
			// this is a really sensitive part, so we add an extra checks: preserve everything that contains "autoupgrade"
			if ($this->_skipFile($filename, $file, 'backup') || strpos($file, 'autoupgrade'))
				unset($toRemove[$key]);
		}
		return $toRemove;
	}

	/**
	 * list files to upgrade and return it as array
	 *
	 * @param string $dir
	 * @return number of files found
	 */
	public function _listFilesToUpgrade($dir)
	{
		static $list = array();
		if (!is_dir($dir))
		{
			$this->nextQuickInfo[] = sprintf('[ERROR] %s doesn\'t exists or is not a directory', $dir);
			$this->nextErrors[] = sprintf('[ERROR] %s doesn\'t exists or is not a directory', $dir);
			$this->next_desc = $this->l('Nothing has been extracted. It seems the unzip step has been skipped.');
			$this->next = 'error';
			return false;
		}

		$allFiles = scandir($dir);
		foreach ($allFiles as $file)
		{
			$fullPath = $dir.DIRECTORY_SEPARATOR.$file;

			if (!$this->_skipFile($file, $fullPath, "upgrade"))
			{
				$list[] = str_replace($this->latestRootDir, '', $fullPath);
				// if is_dir, we will create it :)
				if (is_dir($fullPath))
					if (strpos($dir.DIRECTORY_SEPARATOR.$file, 'install') === false)
						$this->_listFilesToUpgrade($fullPath);
			}
		}
		return $list;
	}


	public function ajaxProcessUpgradeFiles()
	{
		$this->nextParams = $this->currentParams;

		$admin_dir = str_replace($this->prodRootDir.DIRECTORY_SEPARATOR, '', $this->adminDir);
		if (file_exists($this->latestRootDir.DIRECTORY_SEPARATOR.'admin'))
			rename($this->latestRootDir.DIRECTORY_SEPARATOR.'admin', $this->latestRootDir.DIRECTORY_SEPARATOR.$admin_dir);
		elseif (file_exists($this->latestRootDir.DIRECTORY_SEPARATOR.'admin-dev'))
			rename($this->latestRootDir.DIRECTORY_SEPARATOR.'admin-dev', $this->latestRootDir.DIRECTORY_SEPARATOR.$admin_dir);
		if (file_exists($this->latestRootDir.DIRECTORY_SEPARATOR.'install-dev'))
			rename($this->latestRootDir.DIRECTORY_SEPARATOR.'install-dev', $this->latestRootDir.DIRECTORY_SEPARATOR.'install');

		if (!isset($this->nextParams['filesToUpgrade']))
		{
			// list saved in $this->toUpgradeFileList
			// get files differences (previously generated)
			$admin_dir = trim(str_replace($this->prodRootDir, '', $this->adminDir), DIRECTORY_SEPARATOR);
			$filepath_list_diff = $this->autoupgradePath.DIRECTORY_SEPARATOR.$this->diffFileList;
			if (file_exists($filepath_list_diff))
			{
				$list_files_diff = unserialize(file_get_contents($filepath_list_diff));
				// only keep list of files to delete. The modified files will be listed with _listFilesToUpgrade
				$list_files_diff = $list_files_diff['deleted'];
				foreach ($list_files_diff as $k => $path)
					if (preg_match("#autoupgrade#", $path))
						unset($list_files_diff[$k]);
					else
						$list_files_diff[$k] = str_replace(DIRECTORY_SEPARATOR.'admin', DIRECTORY_SEPARATOR.$admin_dir, $path);
			}
			else
				$list_files_diff = array();

			if (!($list_files_to_upgrade = $this->_listFilesToUpgrade($this->latestRootDir)))
				return false;

			// also add files to remove
			$list_files_to_upgrade = array_merge($list_files_diff, $list_files_to_upgrade);
			// save in a serialized array in $this->toUpgradeFileList
			@file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toUpgradeFileList,serialize($list_files_to_upgrade));
			$this->nextParams['filesToUpgrade'] = $this->toUpgradeFileList;
			$total_files_to_upgrade = count($list_files_to_upgrade);

			if ($total_files_to_upgrade == 0)
			{
				$this->nextQuickInfo[] = '[ERROR] Unable to find files to upgrade.';
				$this->nextErrors[] = '[ERROR] Unable to find files to upgrade.';
				$this->next_desc = $this->l('Unable to list files to upgrade');
				$this->next = 'error';
				return false;
			}
			$this->nextQuickInfo[] = sprintf($this->l('%s files will be upgraded.'), $total_files_to_upgrade);

			$this->next_desc = sprintf($this->l('%s files will be upgraded.'), $total_files_to_upgrade);
			$this->next = 'upgradeFiles';
			return true;
		}

		// later we could choose between _PS_ROOT_DIR_ or _PS_TEST_DIR_
		$this->destUpgradePath = $this->prodRootDir;

		// upgrade files one by one like for the backup
		// with a 1000 loop because it's funny
		$this->next = 'upgradeFiles';
		$filesToUpgrade = @unserialize(file_get_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->nextParams['filesToUpgrade']));
		if (!is_array($filesToUpgrade))
		{
			$this->next = 'error';
			$this->next_desc = $this->l('filesToUpgrade is not an array');
			$this->nextQuickInfo[] = $this->l('filesToUpgrade is not an array');
			$this->nextErrors[] = $this->l('filesToUpgrade is not an array');
			return false;
		}

		// @TODO : does not upgrade files in modules, translations if they have not a correct md5 (or crc32, or whatever) from previous version
		for ($i = 0; $i < self::$loopUpgradeFiles; $i++)
		{
			if (count($filesToUpgrade) <= 0)
			{
				$this->next = 'upgradeDb';
				@unlink($this->nextParams['filesToUpgrade']);
				$this->next_desc = $this->l('All files upgraded. Now upgrading database');
				$this->nextResponseType = 'json';
				break;
			}

			$file = array_shift($filesToUpgrade);
			if (!$this->upgradeThisFile($file))
			{
				// put the file back to the begin of the list
				$totalFiles = array_unshift($filesToUpgrade, $file);
				$this->next = 'error';
				$this->nextQuickInfo[] = sprintf($this->l('error when trying to upgrade %s'), $file);
				$this->nextErrors[] = sprintf($this->l('error when trying to upgrade %s'), $file);
				break;
			}
		}
		@file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->nextParams['filesToUpgrade'], serialize($filesToUpgrade));
		if (count($filesToUpgrade) > 0)
		{
			$this->next_desc = sprintf($this->l('%1$s files left to upgrade.'), count($filesToUpgrade));
			$this->nextQuickInfo[] = sprintf($this->l('%2$s files left to upgrade.'), (isset($file)?$file:''), count($filesToUpgrade));
		}
		else
		{
			$this->next_desc = $this->l('all files has been upgraded. Now upgrading database. this can take a while ...');
			$this->nextQuickInfo[] = $this->l('all files has been upgraded. Now upgrading database. this can take a while ...');
		}
		return true;
	}

	private function createCacheFsDirectories($level_depth, $directory = false)
	{
		if (!$directory)
		{
			if (!defined('_PS_CACHEFS_DIRECTORY_'))
				define('_PS_CACHEFS_DIRECTORY_', $this->prodRootDir.'/cache/cachefs/');
			$directory = _PS_CACHEFS_DIRECTORY_;
		}
		$chars = '0123456789abcdef';
		for ($i = 0; $i < strlen($chars); $i++)
		{
			$new_dir = $directory.$chars[$i].'/';
			if (@mkdir($new_dir, 0775) && @chmod($new_dir, 0775) && $level_depth - 1 > 0)
				self::createCacheFsDirectories($level_depth - 1, $new_dir);
		}
	}

	/**
	 * list modules to upgrade and save them in a serialized array in $this->toUpgradeModuleList
	 *
	 * @param string $dir
	 * @return number of files found
	 */
	public function _listModulesToUpgrade()
	{
		static $list = array();

		$dir = $this->prodRootDir.DIRECTORY_SEPARATOR.'modules';

		if (!is_dir($dir))
		{
			$this->nextQuickInfo[] = sprintf('[ERROR] %s doesn\'t exists or is not a directory', $dir);
			$this->nextErrors[] = sprintf('[ERROR] %s doesn\'t exists or is not a directory', $dir);
			$this->next_desc = $this->l('Nothing has been extracted. It seems the unzip step has been skipped.');
			$this->next = 'error';
			return false;
		}

		$allModules = scandir($dir);
		foreach ($allModules as $module_name)
		{
			if (is_dir($dir.DIRECTORY_SEPARATOR.$module_name))
			{
				if(is_array($this->modules_addons))
					$id_addons = array_search($module_name, $this->modules_addons);
				if (isset($id_addons) && $id_addons)
					if ($module_name != 'autoupgrade')
						$list[] = array('id' => $id_addons, 'name' => $module_name);
			}
		}
		@file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toUpgradeModuleList,serialize($list));
		$this->nextParams['modulesToUpgrade'] = $this->toUpgradeModuleList;
		return count($list);
	}

	/**
	 * upgrade all partners modules according to the installed prestashop version
	 *
	 * @access public
	 * @return void
	 */
	public function ajaxProcessUpgradeModules()
	{
		$start_time = time();

		if (!isset($this->nextParams['modulesToUpgrade']))
		{
			// list saved in $this->toUpgradeFileList
			$total_modules_to_upgrade = $this->_listModulesToUpgrade();
			$this->nextQuickInfo[] = sprintf($this->l('%s modules will be upgraded.'), $total_modules_to_upgrade);

			$this->next_desc = sprintf($this->l('%s modules will be upgraded.'), $total_modules_to_upgrade);
			$this->next = 'upgradeModules';
			return true;
		}

		$this->next = 'upgradeModules';
		if (file_exists($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->nextParams['modulesToUpgrade']))
			$listModules = @unserialize(file_get_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->nextParams['modulesToUpgrade']));
		else
			$listModules = array();

		if (!is_array($listModules))
		{
			$this->next = 'upgradeComplete';
			$this->warning_exists = true;
			$this->next_desc = $this->l('upgradeModule step has not ended correctly.');
			$this->nextQuickInfo[] = $this->l('listModules is not an array. No module has been updated.');
			$this->nextErrors[] = $this->l('listModules is not an array. No module has been updated.');
			return true;
		}

		$time_elapsed = time() - $start_time;
		// module list
		if (count($listModules) > 0)
		{
			do
			{
				$module_info = array_shift($listModules);

				$this->upgradeThisModule($module_info['id'], $module_info['name']);
				$time_elapsed = time() - $start_time;
			}
			while (($time_elapsed < self::$loopUpgradeModulesTime) && count($listModules) > 0);

			$modules_left = count($listModules);
			@file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toUpgradeModuleList, serialize($listModules));
			unset($listModules);
			
			//deactivate backward_compatibility, not used in 1.5.X
			if (version_compare($this->install_version, '1.5.0.0', '>='))
			{
				Db::getInstance()->execute('DELETE ms.*, hm.*
				FROM `'._DB_PREFIX_.'module_shop` ms
				INNER JOIN `'._DB_PREFIX_.'hook_module` hm USING (`id_module`)
				INNER JOIN `'._DB_PREFIX_.'module` m USING (`id_module`)				
				WHERE m.`name` LIKE \'backwardcompatibility\'');
				Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'module` SET `active` = 0 WHERE `name` LIKE \'backwardcompatibility\'');

				$dibsPath = $this->prodRootDir.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'dibs'.DIRECTORY_SEPARATOR;
				if (file_exists($dibsPath.'dibs.php'))
                {
					if (Tools14::deleteDirectory($dibsPath))
						$this->nextQuickInfo[] = $this->l('Dibs module is not compatible with 1.5.X, it will be removed from your ftp.');
					else																			
						$this->nextErrors[] = $this->l('Dibs module is not compatible with 1.5.X, please remove it on your ftp.');
                }
			}

			$res = $this->writeConfig(array('PS_AUTOUP_MANUAL_MODE' => '0'));
			$this->next = 'upgradeModules';
			$this->next_desc = sprintf($this->l('%s modules left to upgrade'), $modules_left);
			$this->stepDone = false;
		}
		else
		{
			$this->stepDone = true;
			$this->status = 'ok';
			$this->next = 'cleanDatabase';
			$this->next_desc = $this->l('Addons modules files has been upgraded.');
			$this->nextQuickInfo[] = $this->l('Addons modules files has been upgraded.');
			return true;
		}
		return true;
	}

	/**
	 * upgrade module $name (identified by $id_module on addons server)
	 *
	 * @param mixed $id_module
	 * @param mixed $name
	 * @access public
	 * @return void
	 */
	public function upgradeThisModule($id_module, $name)
	{
		$zip_fullpath = $this->tmpPath.DIRECTORY_SEPARATOR.$name.'.zip';

		$dest_extract = $this->prodRootDir.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR;

		$addons_url = 'api.addons.prestashop.com';
		$protocolsList = array('https://' => 443, 'http://' => 80);
		if (!extension_loaded('openssl'))		
			unset($protocolsList['https://']);		
		$postData = 'version='.$this->install_version.'&method=module&id_module='.(int)$id_module;

		// Make the request
		$opts = array(
			'http'=>array(
				'method'=> 'POST',
				'content' => $postData,
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'timeout' => 5,
			)
		);
		$context = stream_context_create($opts);
		foreach ($protocolsList as $protocol => $port)
		{
			// file_get_contents can return false if https is not supported (or warning)
			$content = @Tools14::file_get_contents($protocol.$addons_url, false, $context);
			if ($content == false)
				continue;
			if ($content !== null)
			{
				if ((bool)@file_put_contents($zip_fullpath, $content))
				{
					// unzip in modules/[mod name] old files will be conserved
					if ($this->ZipExtract($zip_fullpath, $dest_extract))
					{
						$this->nextQuickInfo[] = sprintf($this->l('module %s files has been upgraded'), $name);
						@unlink($zip_fullpath);
					}
					else
					{
						$this->nextQuickInfo[] = sprintf($this->l('[WARNING] error when trying to upgrade module %s.'), $name);
						$this->nextErrors[] = sprintf($this->l('[WARNING] error when trying to upgrade module %s.'), $name);
						$this->warning_exists = 1;
					}
				}
				else
				{
					$this->nextQuickInfo[] = sprintf($this->l('[WARNING] unable to write in temporary directory.'), $name);
					$this->nextErrors[] = sprintf($this->l('[WARNING] unable to write in temporary directory.'), $name);
					$this->warning_exists = 1;
				}
			}
			else
			{
				$this->nextQuickInfo[] = sprintf($this->l('[WARNING] no response from addons server'));
				$this->nextErrors[] = sprintf($this->l('[WARNING] no response from addons server'));
				$this->warning_exists = 1;
			}

		}
		return true;
	}

	public function ajaxProcessUpgradeDb()
	{
		$this->nextParams = $this->currentParams;
		if (!$this->doUpgrade())
		{
			$this->next = 'error';
			$this->next_desc = $this->l('error during upgrade Db. You may need to restore your database');
			return false;
		}
		$this->next = 'upgradeModules';
		$this->next_desc = $this->l('Database upgraded. Now upgrading addons modules ...');
		return true;
	}

	/**
	 * Clean the database from unwanted entires
	 *
	 * @return void
	 */
	public function ajaxProcessCleanDatabase()
	{
		global $warningExists;

		$queries = array('DELETE FROM `'._DB_PREFIX_.'configuration_lang` WHERE `value` IS NULL AND `date_upd` IS NULL');

		$warningExist = false;
		foreach ($queries as $query)
			if (!$this->db->Execute($query, false))
			{
				$this->nextQuickInfo[] = '
						<div class="upgradeDbError">
						[ERROR] SQL Cleaning database'.$this->db->getNumberError().' in '.$query.': '.$this->db->getMsgError().'</div>';
				$this->nextErrors[] = '[ERROR] SQL Cleaning database '.$this->db->getNumberError().' in '.$query.': '.$this->db->getMsgError();
				$warningExist = true;
			}

		if (!$warningExist)
			$this->status = 'ok';
		$this->next = 'upgradeComplete';
		$this->next_desc = $this->l('The database has been cleaned.');
		$this->nextQuickInfo[] = $this->l('The database has been cleaned.');
	}

	/**
	 * This function now replaces doUpgrade.php or upgrade.php
	 *
	 * @return void
	 */
	public function doUpgrade()
	{
		// Initialize
		// setting the memory limit to 128M only if current is lower
		$memory_limit = ini_get('memory_limit');
		if ((substr($memory_limit,-1) != 'G')
			&& ((substr($memory_limit,-1) == 'M' AND substr($memory_limit,0,-1) < 128)
				|| is_numeric($memory_limit) AND (intval($memory_limit) < 131072))
		)
			@ini_set('memory_limit','128M');

		/* Redefine REQUEST_URI if empty (on some webservers...) */
		if (!isset($_SERVER['REQUEST_URI']) || empty($_SERVER['REQUEST_URI']))
		{
			if (!isset($_SERVER['SCRIPT_NAME']) && isset($_SERVER['SCRIPT_FILENAME']))
				$_SERVER['SCRIPT_NAME'] = $_SERVER['SCRIPT_FILENAME'];
			if (isset($_SERVER['SCRIPT_NAME']))
			{
				if (basename($_SERVER['SCRIPT_NAME']) == 'index.php' && empty($_SERVER['QUERY_STRING']))
					$_SERVER['REQUEST_URI'] = dirname($_SERVER['SCRIPT_NAME']).'/';
				else
				{
					$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
					if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']))
						$_SERVER['REQUEST_URI'] .= '?'.$_SERVER['QUERY_STRING'];
				}
			}
		}
		$_SERVER['REQUEST_URI'] = str_replace('//', '/', $_SERVER['REQUEST_URI']);

		define('INSTALL_VERSION', $this->install_version);
		// 1.4
		define('INSTALL_PATH', realpath($this->latestRootDir.DIRECTORY_SEPARATOR.'install'));
		// 1.5 ...
		define('_PS_INSTALL_PATH_', INSTALL_PATH.DIRECTORY_SEPARATOR);


		define('PS_INSTALLATION_IN_PROGRESS', true);
		define('SETTINGS_FILE', $this->prodRootDir . '/config/settings.inc.php');
		define('DEFINES_FILE', $this->prodRootDir .'/config/defines.inc.php');
		define('INSTALLER__PS_BASE_URI', substr($_SERVER['REQUEST_URI'], 0, -1 * (strlen($_SERVER['REQUEST_URI']) - strrpos($_SERVER['REQUEST_URI'], '/')) - strlen(substr(dirname($_SERVER['REQUEST_URI']), strrpos(dirname($_SERVER['REQUEST_URI']), '/')+1))));
		//	define('INSTALLER__PS_BASE_URI_ABSOLUTE', 'http://'.ToolsInstall::getHttpHost(false, true).INSTALLER__PS_BASE_URI);

		// XML Header
		// header('Content-Type: text/xml');

		$filePrefix = 'PREFIX_';
		$engineType = 'ENGINE_TYPE';

		$mysqlEngine = (defined('_MYSQL_ENGINE_') ? _MYSQL_ENGINE_ : 'MyISAM');

		if (function_exists('date_default_timezone_set'))
			date_default_timezone_set('Europe/Paris');

		// if _PS_ROOT_DIR_ is defined, use it instead of "guessing" the module dir.
		if (defined('_PS_ROOT_DIR_') AND !defined('_PS_MODULE_DIR_'))
			define('_PS_MODULE_DIR_', _PS_ROOT_DIR_.'/modules/');
		else if (!defined('_PS_MODULE_DIR_'))
			define('_PS_MODULE_DIR_', INSTALL_PATH.'/../modules/');

		$upgrade_dir_php = 'upgrade/php';
		if (!file_exists(INSTALL_PATH.DIRECTORY_SEPARATOR.$upgrade_dir_php))
		{
			$upgrade_dir_php = 'php';
			if (!file_exists(INSTALL_PATH.DIRECTORY_SEPARATOR.$upgrade_dir_php))
			{
				$this->next = 'error';
				$this->next_desc = $this->l('/install/upgrade/php directory is missing in archive or directory');
				$this->nextQuickInfo[] = '/install/upgrade/php directory is missing in archive or directory';
				$this->nextErrors[] = '/install/upgrade/php directory is missing in archive or directory.';
				return false;
			}
		}
		define('_PS_INSTALLER_PHP_UPGRADE_DIR_',  INSTALL_PATH.DIRECTORY_SEPARATOR.$upgrade_dir_php.DIRECTORY_SEPARATOR);

		//old version detection
		global $oldversion, $logger;
		$oldversion = false;
		if (file_exists(SETTINGS_FILE))
		{
			include_once(SETTINGS_FILE);
			// include_once(DEFINES_FILE);
			$oldversion = _PS_VERSION_;

		}
		else
		{
			$this->next = 'error';
			$this->nextQuickInfo[] = $this->l('The config/settings.inc.php file was not found.');
			$this->nextErrors[] = $this->l('The config/settings.inc.php file was not found.');
			return false;
			die('<action result="fail" error="30" />'."\n");
		}

		if (!defined('__PS_BASE_URI__'))
			define('__PS_BASE_URI__', realpath(dirname($_SERVER['SCRIPT_NAME'])).'/../../');

		if (!defined('_THEMES_DIR_'))
			define('_THEMES_DIR_', __PS_BASE_URI__.'themes/');

		$oldversion = _PS_VERSION_;
		$versionCompare =  version_compare(INSTALL_VERSION, $oldversion);

		if ($versionCompare == '-1')
		{
			$this->next = 'error';
			$this->nextQuickInfo[] = sprintf('current version : %1$s. install version : %2$s', $oldversion, INSTALL_VERSION);
			$this->nextErrors[] = sprintf('current version : %1$s. install version : %2$s', $oldversion, INSTALL_VERSION);
			$this->nextQuickInfo[] = '[ERROR] version to install is too old ';
			$this->nextErrors[] = '[ERROR] version to install is too old ';
			return false;
			// die('<action result="fail" error="27" />'."\n");
		}
		elseif ($versionCompare == 0)
		{
			$this->next = 'error';
			$this->nextQuickInfo[] = $this->l(sprintf('You already have the %s version.',INSTALL_VERSION));
			$this->nextErrors[] = $this->l(sprintf('You already have the %s version.',INSTALL_VERSION));
			return false;
			die('<action result="fail" error="28" />'."\n");
		}
		elseif ($versionCompare === false)
		{
			$this->next = 'error';
			$this->nextQuickInfo[] = $this->l('There is no older version. Did you delete or rename the config/settings.inc.php file?');
			$this->nextErrors[] = $this->l('There is no older version. Did you delete or rename the config/settings.inc.php file?');
			return false;
			die('<action result="fail" error="29" />'."\n");
		}

		//check DB access
		$this->db;
		error_reporting(E_ALL);
		$resultDB = MySql::tryToConnect(_DB_SERVER_, _DB_USER_, _DB_PASSWD_, _DB_NAME_);
		if ($resultDB !== 0)
		{
			// $logger->logError('Invalid database configuration.');
			$this->next = 'error';
			$this->nextQuickInfo[] = $this->l('Invalid database configuration');
			$this->nextErrors[] = $this->l('Invalid database configuration');
			return false;
			die("<action result='fail' error='".$resultDB."'/>\n");
		}

		//custom sql file creation
		$upgradeFiles = array();

		$upgrade_dir_sql = INSTALL_PATH.'/upgrade/sql';
		// if 1.4;
		if (!file_exists($upgrade_dir_sql))
			$upgrade_dir_sql = INSTALL_PATH.'/sql/upgrade';

		if (!file_exists($upgrade_dir_sql))
		{
			$this->next = 'error';
			$this->next_desc = $this->l('unable to find upgrade directory in the install path');
			return false;
		}

		if ($handle = opendir($upgrade_dir_sql))
		{
			while (false !== ($file = readdir($handle)))
				if ($file != '.' AND $file != '..')
					$upgradeFiles[] = str_replace(".sql", "", $file);
			closedir($handle);
		}
		if (empty($upgradeFiles))
		{
			$this->next = 'error';
			$this->nextQuickInfo[] = sprintf($this->l('Cannot find the sql upgrade files. Please verify that the %s folder is not empty'), $upgrade_dir_sql);
			$this->nextErrors[] = sprintf($this->l('Cannot find the sql upgrade files. Please verify that the %s folder is not empty'), $upgrade_dir_sql);
			// fail 31
			return false;
		}
		natcasesort($upgradeFiles);
		$neededUpgradeFiles = array();

		$arrayVersion = explode('.', $oldversion);
		$versionNumbers = count($arrayVersion);
		if ($versionNumbers != 4)
			$arrayVersion = array_pad($arrayVersion, 4, '0');

		$oldversion = implode('.', $arrayVersion);

		foreach ($upgradeFiles as $version)
			if (version_compare($version, $oldversion) == 1 && version_compare(INSTALL_VERSION, $version) != -1)
				$neededUpgradeFiles[] = $version;

		if (empty($neededUpgradeFiles) || count($neededUpgradeFiles) === 0)
		{
			$this->next = 'error';
			$this->nextQuickInfo[] = $this->l('No upgrade is possible.');
			$this->nextErrors[] = $this->l('No upgrade is possible.');
			return false;
		}

		$sqlContentVersion = array();
		if($this->deactivateCustomModule)
		{
			require_once(_PS_INSTALLER_PHP_UPGRADE_DIR_.'deactivate_custom_modules.php');
			deactivate_custom_modules();
		}

		foreach($neededUpgradeFiles as $version)
		{
			$file = $upgrade_dir_sql.DIRECTORY_SEPARATOR.$version.'.sql';
			if (!file_exists($file))
			{
				$this->next = 'error';
				$this->nextQuickInfo[] = sprintf($this->l('Error while loading sql upgrade file "%s.sql".'), $version);
				$this->nextErrors[] = sprintf($this->l('Error while loading sql upgrade file "%s.sql".'), $version);
				return false;
				$logger->logError('Error while loading sql upgrade file.');

				die('<action result="fail" error="33" />'."\n");
			}
			if (!$sqlContent = file_get_contents($file)."\n")
			{
				$this->next = 'error';
				$this->nextQuickInfo[] = $this->l(sprintf('Error while loading sql upgrade file %s.', $version));
				$this->nextErrors[] = $this->l(sprintf('Error while loading sql upgrade file %s.', $version));
				return false;
				$logger->logError(sprintf('Error while loading sql upgrade file %s.', $version));
				die('<action result="fail" error="33" />'."\n");
			}
			$sqlContent = str_replace(array($filePrefix, $engineType), array(_DB_PREFIX_, $mysqlEngine), $sqlContent);
			$sqlContent = preg_split("/;\s*[\r\n]+/",$sqlContent);
			$sqlContentVersion[$version] = $sqlContent;
		}


		//sql file execution
		global $requests, $warningExist;
		$requests = '';
		$warningExist = false;

		// Configuration::loadConfiguration();
		$request = '';

		foreach ($sqlContentVersion as $upgrade_file => $sqlContent)
			foreach ($sqlContent as $query)
		{
			$query = trim($query);
			if(!empty($query))
			{
				/* If php code have to be executed */
				if (strpos($query, '/* PHP:') !== false)
				{
					/* Parsing php code */
					$pos = strpos($query, '/* PHP:') + strlen('/* PHP:');
					$phpString = substr($query, $pos, strlen($query) - $pos - strlen(' */;'));
					$php = explode('::', $phpString);
					preg_match('/\((.*)\)/', $phpString, $pattern);
					$paramsString = trim($pattern[0], '()');
					preg_match_all('/([^,]+),? ?/', $paramsString, $parameters);
					if (isset($parameters[1]))
						$parameters = $parameters[1];
					else
						$parameters = array();
					if (is_array($parameters))
						foreach ($parameters AS &$parameter)
							$parameter = str_replace('\'', '', $parameter);

					// reset phpRes to a null value
					$phpRes = null;
					/* Call a simple function */
					if (strpos($phpString, '::') === false)
					{
						$func_name = str_replace($pattern[0], '', $php[0]);
						if (!file_exists(_PS_INSTALLER_PHP_UPGRADE_DIR_.strtolower($func_name).'.php'))
						{
							$this->nextQuickInfo[] = '<div class="upgradeDbError">[ERROR] '.$upgrade_file.' PHP - missing file '.$query.'</div>';
							$this->nextErrors[] = '[ERROR] '.$upgrade_file.' PHP - missing file '.$query;
							$warningExist = true;
						}
						else
						{
							require_once(_PS_INSTALLER_PHP_UPGRADE_DIR_.strtolower($func_name).'.php');
							$phpRes = call_user_func_array($func_name, $parameters);
						}
					}
					/* Or an object method */
					else
					{
						$func_name = array($php[0], str_replace($pattern[0], '', $php[1]));
						$this->nextQuickInfo[] = '<div class="upgradeDbError">[ERROR] '.$upgrade_file.' PHP - Object Method call is forbidden ( '.$php[0].'::'.str_replace($pattern[0], '', $php[1]).')</div>';
						$this->nextErrors[] = '[ERROR] '.$upgrade_file.' PHP - Object Method call is forbidden ('.$php[0].'::'.str_replace($pattern[0], '', $php[1]).')';
						$warningExist = true;
					}

					if (isset($phpRes) && (is_array($phpRes) && !empty($phpRes['error'])) || $phpRes === false)
					{
						// $this->next = 'error';
						$this->nextQuickInfo[] = '
							<div class="upgradeDbError">
								[ERROR] PHP '.$upgrade_file.' '.$query.'
								'.(empty($phpRes['error']) ? '' : $phpRes['error']).'
								'.(empty($phpRes['msg']) ? '' : ' - '.$phpRes['msg']).'
							</div>';
						$this->nextErrors[] = '
							[ERROR] PHP '.$upgrade_file.' '.$query.'
							'.(empty($phpRes['error']) ? '' : $phpRes['error']).'
							'.(empty($phpRes['msg']) ? '' : ' - '.$phpRes['msg']);
						$warningExist = true;
					}
					else
						$this->nextQuickInfo[] = '<div class="upgradeDbOk">[OK] PHP '.$upgrade_file.' : '.$query.'</div>';
				}
				elseif (!$this->db->execute($query, false))
				{
					$this->nextQuickInfo[] = '
						<div class="upgradeDbError">
						[ERROR] SQL '.$upgrade_file.'
						'.$this->db->getNumberError().' in '.$query.': '.$this->db->getMsgError().'</div>';
					$this->nextErrors[] = '[ERROR] SQL '.$upgrade_file.' ' . $this->db->getNumberError().' in '.$query.': '.$this->db->getMsgError();
					$warningExist = true;
				}
				else
					$this->nextQuickInfo[] = '<div class="upgradeDbOk">[OK] SQL '.$upgrade_file.' '.$query.'</div>';
			}
		}
		if ($this->next == 'error')
		{
			$this->next_desc = $this->l('An error happen during database upgrade');
			return false;
		}

		$this->nextQuickInfo[] = $this->l('Upgrade Db Ok'); // no error !

		# At this point, database upgrade is over.
			# Now we need to add all previous missing settings items, and reset cache and compile directories
			$this->writeNewSettings();							

		// Settings updated, compile and cache directories must be emptied
		$arrayToClean[] = $this->prodRootDir.'/tools/smarty/cache/';
		$arrayToClean[] = $this->prodRootDir.'/tools/smarty/compile/';
		$arrayToClean[] = $this->prodRootDir.'/tools/smarty_v2/cache/';
		$arrayToClean[] = $this->prodRootDir.'/tools/smarty_v2/compile/';

		foreach ($arrayToClean as $dir)
			if (!file_exists($dir))
			{
				$this->nextQuickInfo[] = sprintf($this->l('[SKIP] directory "%s" doesn\'t exist and cannot be emptied.'), str_replace($this->prodRootDir, '', $dir));
				continue;
			}
			else
				foreach (scandir($dir) as $file)
					if ($file[0] != '.' && $file != 'index.php' && $file != '.htaccess')
					{
						@unlink($dir.$file);
						$this->nextQuickInfo[] = sprintf($this->l('[cleaning cache] %s removed'), $file);
					}

		// delete cache filesystem if activated
		if (defined('_PS_CACHE_ENABLED_') && _PS_CACHE_ENABLED_)
		{
			$depth = (int)$this->db->getValue('SELECT value
				FROM '._DB_PREFIX_.'configuration
				WHERE name = "PS_CACHEFS_DIRECTORY_DEPTH"');
			if($depth)
			{
				if (!defined('_PS_CACHEFS_DIRECTORY_'))
					define('_PS_CACHEFS_DIRECTORY_', $this->prodRootDir.'/cache/cachefs/');
				self::deleteDirectory(_PS_CACHEFS_DIRECTORY_, false);
				if (class_exists('CacheFs', false))
					self::createCacheFsDirectories((int)$depth);
			}
		}
		// we do not use class Configuration because it's not loaded;
		$this->db->execute('UPDATE `'._DB_PREFIX_.'configuration`
			SET value="0" WHERE name = "PS_HIDE_OPTIMIZATION_TIS"', false);
		$this->db->execute('UPDATE `'._DB_PREFIX_.'configuration`
			SET value="1" WHERE name = "PS_NEED_REBUILD_INDEX"', false);
		$this->db->execute('UPDATE `'._DB_PREFIX_.'configuration`
			SET value="'.INSTALL_VERSION.'" WHERE name = "PS_VERSION_DB"', false);

		if ($warningExist)
		{
			$this->warning_exists = true;
			$this->nextQuickInfo[] = $this->l('Warning detected during upgrade.');
			$this->nextErrors[] = $this->l('Warning detected during upgrade.');
			$this->next_desc = $this->l('Warning detected during upgrade.');
		}
		else
			$this->next_desc = $this->l('Database upgrade completed');

		return true;
	}

	public function writeNewSettings()
	{
		// note : duplicated line
		$mysqlEngine = (defined('_MYSQL_ENGINE_') ? _MYSQL_ENGINE_ : 'MyISAM');

		$oldLevel = error_reporting(E_ALL);
		//refresh conf file
		require_once(_PS_ROOT_DIR_.'/modules/autoupgrade/classes/AddConfToFile.php');
		$confFile = new AddConfToFile(SETTINGS_FILE, 'w');
		if ($confFile->error)
		{
			$this->next = 'error';
			$this->next_desc = $this->l('Error when opening settings.inc.php file in write mode');
			$this->nextQuickInfo[] = $confFile->error;
			$this->nextErrors[] = $this->l('Error when opening settings.inc.php file in write mode').': '.$confFile->error;
			return false;
		}
		$datas = array(
			array('_DB_SERVER_', _DB_SERVER_),
			array('_DB_NAME_', _DB_NAME_),
			array('_DB_USER_', _DB_USER_),
			array('_DB_PASSWD_', _DB_PASSWD_),
			array('_DB_PREFIX_', _DB_PREFIX_),
			array('_MYSQL_ENGINE_', $mysqlEngine),
			array('_PS_CACHING_SYSTEM_', (defined('_PS_CACHING_SYSTEM_') AND _PS_CACHING_SYSTEM_ != 'CacheMemcache') ? _PS_CACHING_SYSTEM_ : 'CacheMemcache'),
			array('_PS_CACHE_ENABLED_', defined('_PS_CACHE_ENABLED_') ? _PS_CACHE_ENABLED_ : '0'),
			array('_MEDIA_SERVER_1_', defined('_MEDIA_SERVER_1_') ? _MEDIA_SERVER_1_ : ''),
			array('_MEDIA_SERVER_2_', defined('_MEDIA_SERVER_2_') ? _MEDIA_SERVER_2_ : ''),
			array('_MEDIA_SERVER_3_', defined('_MEDIA_SERVER_3_') ? _MEDIA_SERVER_3_ : ''),
			array('_COOKIE_KEY_', _COOKIE_KEY_),
			array('_COOKIE_IV_', _COOKIE_IV_),
			array('_PS_CREATION_DATE_', defined("_PS_CREATION_DATE_") ? _PS_CREATION_DATE_ : date('Y-m-d')),
			array('_PS_VERSION_', INSTALL_VERSION)
		);
		if (defined('_RIJNDAEL_KEY_'))
			$datas[] = array('_RIJNDAEL_KEY_', _RIJNDAEL_KEY_);
		if (defined('_RIJNDAEL_IV_'))
			$datas[] = array('_RIJNDAEL_IV_', _RIJNDAEL_IV_);
		if(!defined('_PS_CACHE_ENABLED_'))
			define('_PS_CACHE_ENABLED_', '0');
		if(!defined('_MYSQL_ENGINE_'))
			define('_MYSQL_ENGINE_', 'MyISAM');

		// if install version is before 1.5
		if (version_compare(INSTALL_VERSION, '1.5.0.0', '<='))
		{
			$datas[] = array('_DB_TYPE_', _DB_TYPE_);
			$datas[] = array('__PS_BASE_URI__', __PS_BASE_URI__);
			$datas[] = array('_THEME_NAME_', _THEME_NAME_);
		}
		else
			$datas[] = array('_PS_DIRECTORY_', __PS_BASE_URI__);

		foreach ($datas AS $data){
			$confFile->writeInFile($data[0], $data[1]);
		}

		if ($confFile->error != false)
		{
			$this->next = 'error';
			$this->next_desc = $this->l('Error when generating new settings.inc.php file.');
			$this->nextQuickInfo[] = $confFile->error;
			$this->nextErrors[] = $this->l('Error when generating new settings.inc.php file.').' '.$confFile->error;
			return false;
		}
		else
			$this->nextQuickInfo[] = $this->l('settings file updated');
		error_reporting($oldLevel);
	}	

	/**
	 * getTranslationFileType
	 *
	 * @param string $file filepath to check
	 * @access public
	 * @return string type of translation item
	 */
	public function getTranslationFileType($file)
	{
		$type = false;
		// line shorter
		$separator = addslashes(DIRECTORY_SEPARATOR);
		$translation_dir = $separator.'translations'.$separator;
		if (version_compare(_PS_VERSION_, '1.5.0.5', '<'))
			$regex_module = '#'.$separator.'modules'.$separator.'.*'.$separator.'('.implode('|', $this->installedLanguagesIso).')\.php#';
		else
			$regex_module = '#'.$separator.'modules'.$separator.'.*'.$translation_dir.'('.implode('|', $this->installedLanguagesIso).')\.php#';

		if (preg_match($regex_module, $file))
			$type = 'module';
		elseif (preg_match('#'.$translation_dir.'('.implode('|', $this->installedLanguagesIso).')'.$separator.'admin\.php#', $file))
			$type = 'back office';
		elseif (preg_match('#'.$translation_dir.'('.implode('|', $this->installedLanguagesIso).')'.$separator.'errors\.php#', $file))
			$type = 'error message';
		elseif (preg_match('#'.$translation_dir.'('.implode('|', $this->installedLanguagesIso).')'.$separator.'fields\.php#', $file))
			$type = 'field';
		elseif (preg_match('#'.$translation_dir.'('.implode('|', $this->installedLanguagesIso).')'.$separator.'pdf\.php#', $file))
			$type = 'pdf';
		elseif (preg_match('#'.$separator.'themes'.$separator.'(default|prestashop)'.$separator.'lang'.$separator.'('.implode('|', $this->installedLanguagesIso).')\.php#', $file))
			$type = 'front office';

		return $type;
	}

	/**
	 * return true if $file is a translation file
	 *
	 * @param string $file filepath (from prestashop root)
	 * @access public
	 * @return boolean
	 */
	public function isTranslationFile($file)
	{
		if ($this->getTranslationFileType($file) !== false)
			return true;

		return false;
	}

	/**
	 * merge the translations of $orig into $dest, according to the $type of translation file
	 *
	 * @param string $orig file from upgrade package
	 * @param string $dest filepath of destination
	 * @param string $type type of translation file (module, bo, fo, field, pdf, error)
	 * @access public
	 * @return boolean
	 */
	public function mergeTranslationFile($orig, $dest, $type)
	{
		switch ($type)
		{
			case 'front office':
				$var_name = '_LANG';
				break;
			case 'back office':
				$var_name = '_LANGADM';
				break;
			case 'error message':
				$var_name = '_ERRORS';
				break;
			case 'field':
				$var_name = '_FIELDS';
				break;
			case 'module':
				$var_name = '_MODULE';
				// if current version is before 1.5.0.5, module has no translations dir
				if (version_compare(_PS_VERSION_, '1.5.0.5', '<') && (version_compare($this->install_version, '1.5.0.5', '>')))
					$dest = str_replace(DIRECTORY_SEPARATOR.'translations', '', $dest);

				break;
			case 'pdf':
				$var_name = '_LANGPDF';
				break;
			case 'mail':
				$var_name = '_LANGMAIL';
				break;
			default:
				return false;
		}

		if (!file_exists($orig))
		{
			$this->nextQuickInfo[] = sprintf('[NOTICE] file %s does not exists, merge skipped', $orig);
			return true;
		}
		include($orig);
		if (!isset($$var_name))
		{
			$this->nextQuickInfo[] = sprintf('[WARNING] %1$s variable missing in file %2$s. merge skipped', $var_name, $orig);
			return true;
		}
		$var_orig = $$var_name;

		if (!file_exists($dest))
		{
			$this->nextQuickInfo[] = sprintf('[NOTICE] file %s does not exists, merge skipped', $dest);
			return false;
		}
		include($dest);
		if (!isset($$var_name))
		{
			// in that particular case : file exists, but variable missing, we need to delete that file
			// (if not, this invalid file will be copied in /translations during upgradeDb process)
			if ('module' == $type)
				@unlink($dest);
			$this->nextQuickInfo[] = sprintf('[WARNING] %1$s variable missing in file %2$s. file %2$s deleted and merge skipped.', $var_name, $dest);
			return false;
		}
		$var_dest = $$var_name;

		$merge = array_merge($var_orig, $var_dest);

		if ($fd = fopen($dest, 'w'))
		{
			fwrite($fd, "<?php\n\nglobal \$".$var_name.";\n\$".$var_name." = array();\n");
			foreach ($merge as $k => $v)
			{
				if (get_magic_quotes_gpc())
					$v = stripslashes($v);
				if ('mail' == $type)
					fwrite($fd, '$'.$var_name.'[\''.$this->db->escape($k).'\'] = \''.$this->db->escape($v).'\';'."\n");
				else
					fwrite($fd, '$'.$var_name.'[\''.$this->db->escape($k, true).'\'] = \''.$this->db->escape($v, true).'\';'."\n");
			}
			fwrite($fd, "\n?>");
			fclose($fd);
		}
		else
			return false;

		return true;
	}

	/**
	 * upgradeThisFile
	 *
	 * @param mixed $file
	 * @return void
	 */
	public function upgradeThisFile($file)
	{

		// note : keepMails is handled in skipFiles
		// translations_custom and mails_custom list are currently not used
		// later, we could handle customization with some kind of diff functions
		// for now, just copy $file in str_replace($this->latestRootDir,_PS_ROOT_DIR_)
		$orig = $this->latestRootDir.$file;
		$dest = $this->destUpgradePath.$file;

		if ($this->_skipFile($file, $dest, 'upgrade'))
		{
			$this->nextQuickInfo[] = sprintf($this->l('%s ignored'), $file);
			return true;
		}
		else
		{
			if (is_dir($orig))
			{
				// if $dest is not a directory (that can happen), just remove that file
				if (!is_dir($dest) AND file_exists($dest))
				{
					@unlink($dest);
					$this->nextQuickInfo[] = sprintf('[WARNING] file %1$s has been deleted.', $file);
				}
				if (!file_exists($dest))
				{
					if (@mkdir($dest))
					{
						$this->nextQuickInfo[] = sprintf($this->l('directory %1$s created.'), $file);
						return true;
					}
					else
					{
						$this->next = 'error';
						$this->nextQuickInfo[] = sprintf($this->l('error when creating directory %s'), $dest);
						$this->nextErrors[] = sprintf($this->l('error when creating directory %s'), $dest);
						$this->next_desc = sprintf($this->l('error when creating directory %s'), $dest);
						return false;
					}
				}
				else // directory already exists
				{
					$this->nextQuickInfo[] = sprintf($this->l('directory %1$s already exists.'), $file);
					return true;
				}
			}
			elseif (is_file($orig))
			{
				if ($this->isTranslationFile($file) && file_exists($dest))
				{
					$type_trad = $this->getTranslationFileType($file);
					$res = $this->mergeTranslationFile($orig, $dest, $type_trad);
					if ($res)
					{
						$this->nextQuickInfo[] = sprintf($this->l('[TRAD] translations has been merged for file %1$s'), $dest);
						return true;
					}
					else
					{
						$this->nextQuickInfo[] = sprintf($this->l('[TRAD] translations has not been merged for file %1$s. Switch to copy %2$s.'), $dest, $dest);
						$this->nextErrors[] = sprintf($this->l('[TRAD] translations has not been merged for file %1$s. Switch to copy %2$s.'), $dest, $dest);
					}
				}

				// upgrade exception were above. This part now process all files that have to be upgraded (means to modify or to remove)
				// delete before updating (and this will also remove deprecated files)
				if (copy($orig, $dest))
				{
					$this->nextQuickInfo[] = sprintf($this->l('copied %1$s.'), $file);
					return true;
				}
				else
				{
					$this->next = 'error';
					$this->nextQuickInfo[] = sprintf($this->l('error for copying %1$s'), $file);
					$this->nextErrors[] = sprintf($this->l('error for copying %1$s'), $file);
					$this->next_desc = sprintf($this->l('error for copying %1$s'), $file);
					return false;
				}
			}
			elseif (is_file($dest))
			{
				@unlink($dest);
				$this->nextQuickInfo[] = sprintf('removed file %1$s.', $file);
				return true;
			}
			elseif (is_dir($dest))
			{
				if (strpos($dest, DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR) === false)
					self::deleteDirectory($dest, true);
				$this->nextQuickInfo[] = sprintf('removed dir %1$s.', $file);
				return true;
			}
			else
				return true;
		}
	}

	public function ajaxProcessRollback()
	{
		// 1st, need to analyse what was wrong.
		$this->nextParams = $this->currentParams;
		$this->restoreFilesFilename = $this->restoreName;
		if (!empty($this->restoreName))
		{
			$files = scandir($this->backupPath);
			// find backup filenames, and be sure they exists
			foreach($files as $file)
				if (preg_match('#'.preg_quote('auto-backupfiles_'.$this->restoreName).'#', $file))
				{
					$this->restoreFilesFilename = $file;
					break;
				}
			if (!is_file($this->backupPath.DIRECTORY_SEPARATOR.$this->restoreFilesFilename))
			{
				$this->next = 'error';
				$this->nextQuickInfo[] = sprintf('[ERROR] file %s is missing : unable to restore files. Operation aborted.', $this->restoreFilesFilename);
				$this->nextErrors[] = sprintf('[ERROR] file %s is missing : unable to restore files. Operation aborted.', $this->restoreFilesFilename);
				$this->next_desc = sprintf($this->l('file %s does not exist. Files Restoration cannot be made.'), $this->restoreFilesFilename);
				return false;
			}
			$files = scandir($this->backupPath.DIRECTORY_SEPARATOR.$this->restoreName);
			foreach($files as $file)
				if (preg_match('#auto-backupdb_[0-9]{6}_'.preg_quote($this->restoreName).'#', $file))
					$this->restoreDbFilenames[] = $file;

			// order files is important !
			sort($this->restoreDbFilenames);
			if (count($this->restoreDbFilenames) == 0)
			{
				$this->next = 'error';
				$this->nextQuickInfo[] = sprintf('[ERROR] no backup db files found : it would be impossible to restore database. Operation aborted.');
				$this->nextErrors[] = sprintf('[ERROR] no backup db files found : it would be impossible to restore database. Operation aborted.');
				$this->next_desc = sprintf($this->l('no backup db files found. Database restoration cannot be made.'), count($this->restoreDbFilenames));
				return false;
			}

			$this->next = 'restoreFiles';
			$this->next_desc = $this->l('Restoring files ...');
			// remove tmp files related to restoreFiles
			if (file_exists($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->fromArchiveFileList))
				@unlink($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->fromArchiveFileList);
			if (file_exists($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toRemoveFileList))
				@unlink($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toRemoveFileList);
		}
		else
			$this->next = 'noRollbackFound';
	}

	public function ajaxProcessNoRollbackFound()
	{
		$this->next_desc = $this->l('Nothing to restore');
		$this->next = 'rollbackComplete';
	}

	/**
	 * ajaxProcessRestoreFiles restore the previously saved files,
	 * and delete files that weren't archived
	 *
	 * @return boolean true if succeed
	 */
	public function ajaxProcessRestoreFiles()
	{
		// loop
		$this->next = 'restoreFiles';
		if (!file_exists($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->fromArchiveFileList)
			|| !file_exists($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toRemoveFileList))
		{
			// cleanup current PS tree
			$fromArchive = $this->_listArchivedFiles($this->backupPath.DIRECTORY_SEPARATOR.$this->restoreFilesFilename);
			foreach($fromArchive as $k => $v)
				$fromArchive[$k] = '/'.$v;
			@file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->fromArchiveFileList, serialize($fromArchive));
			// get list of files to remove
			$toRemove = $this->_listFilesToRemove();
			// let's reverse the array in order to make possible to rmdir
			// remove fullpath. This will be added later in the loop.
			// we do that for avoiding fullpath to be revealed in a text file
			foreach ($toRemove as $k => $v)
				$toRemove[$k] = str_replace($this->prodRootDir, '', $v);

			$this->nextQuickInfo[] = sprintf($this->l('%s file(s) will be removed before restoring backup files'), count($toRemove));
			@file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toRemoveFileList, serialize($toRemove));

			if ($fromArchive === false || $toRemove === false)
			{
				if (!$fromArchive)
				{
					$this->nextQuickInfo[] = '[ERROR] '.sprintf($this->l('backup file %s does not exists'), $this->fromArchiveFileList);
					$this->nextErrors[] = '[ERROR] '.sprintf($this->l('backup file %s does not exists'), $this->fromArchiveFileList);
				}
				if (!$toRemove)
				{
					$this->nextQuickInfo[] = '[ERROR] '.sprintf($this->l('file "%s" does not exists'), $this->toRemoveFileList);
					$this->nextErrors[] = '[ERROR] '.sprintf($this->l('file "%s" does not exists'), $this->toRemoveFileList);
				}
				$this->next_desc = $this->l('Unable to remove upgraded files.');
				$this->next = 'error';
				return false;
			}
		}

		// first restoreFiles step
		if (!isset($toRemove))
			$toRemove = unserialize(file_get_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toRemoveFileList));

		if (count($toRemove) > 0)
		{
			for($i=0;$i<self::$loopRestoreFiles ;$i++)
			{
				if (count($toRemove) <= 0)
				{
					$this->stepDone = true;
					$this->status = 'ok';
					$this->next = 'restoreFiles';
					$this->next_desc = $this->l('Files from upgrade has been removed.');
					$this->nextQuickInfo[] = $this->l('files from upgrade has been removed.');
					@file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toRemoveFileList, serialize($toRemove));
					return true;
				}
				else
				{
					$filename = array_shift($toRemove);
					$file = rtrim($this->prodRootDir, DIRECTORY_SEPARATOR).$filename;
					if (file_exists($file))
					{
						if (is_file($file) && @unlink($file))
							$this->nextQuickInfo[] = sprintf('%s removed', $filename);
						elseif (is_dir($file))
						{
							if ($this->isDirEmpty($file))
							{
								self::deleteDirectory($file, true);
								$this->nextQuickInfo[] = sprintf('[NOTICE] %s directory deleted', $filename);
							}
							else
								$this->nextQuickInfo[] = sprintf('[NOTICE] %s directory skipped (directory not empty)', $filename);
						}
						else
						{
							$this->next = 'error';
							$this->next_desc = sprintf($this->l('error when removing %1$s'), $filename);
							$this->nextQuickInfo[] = sprintf($this->l('%s not removed'), $filename);
							$this->nextErrors[] = sprintf($this->l('%s not removed'), $filename);
							return false;
						}
					}
					else
						$this->nextQuickInfo[] = sprintf('[NOTICE] %s does not exists', $filename);
				}
			}
			@file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toRemoveFileList, serialize($toRemove));
			$this->next_desc = sprintf($this->l('%s left to remove'), count($toRemove));
			$this->next = 'restoreFiles';
			return true;
		}


		// very second restoreFiles step : extract backup
		// if (!isset($fromArchive))
		//	$fromArchive = unserialize(file_get_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->fromArchiveFileList));
		$filepath = $this->backupPath.DIRECTORY_SEPARATOR.$this->restoreFilesFilename;
		$destExtract = $this->prodRootDir;
		if ($this->ZipExtract($filepath, $destExtract))
		{
			$this->next = 'restoreDb';
			$this->next_desc = $this->l('Files restored. Now restoring database ...');
			// get new file list
			$this->nextQuickInfo[] = $this->l('Files restored.');
			// once it's restored, do not delete the archive file. This has to be done manually
			// and we do not empty the var, to avoid infinite loop.
			return true;
		}
		else
		{
			$this->next = "error";
			$this->next_desc = sprintf($this->l('unable to extract %1$s into %2$s .'), $filepath, $destExtract);
			return false;
		}
		return true;
	}

	public function isDirEmpty($dir, $ignore = array('.svn'))
	{
		$array_ignore = array_merge(array('.', '..'), $ignore);
		$content = scandir($dir);
		foreach($content as $filename)
			if (!in_array($filename, $array_ignore))
				return false;
		return true;
	}

	/**
	 * Delete directory and subdirectories
	 *
	 * @param string $dirname Directory name
	 */
	public static function deleteDirectory($dirname, $delete_self = true)
	{
		$dirname = rtrim($dirname, '/').'/';
		$files = scandir($dirname);
		foreach ($files as $file)
			if ($file != '.' AND $file != '..')
			{
				if (is_dir($dirname.$file))
					self::deleteDirectory($dirname.$file, true);
				elseif (file_exists($dirname.$file))
					@unlink($dirname.$file);
			}
		if ($delete_self && is_dir($dirname))
			rmdir($dirname);
	}
	/**
	 * try to restore db backup file
	 * @return type : hey , what you expect ? well mysql errors array .....
	 */
	public function ajaxProcessRestoreDb()
	{
		$skip_ignore_tables = false;
		$this->nextParams['dbStep'] = $this->currentParams['dbStep'];
		$start_time = time();
		$db = $this->db;
		// deal with the next files stored in restoreDbFilenames
		if (is_array($this->restoreDbFilenames) && count($this->restoreDbFilenames) > 0)
		{
			$currentDbFilename = array_shift($this->restoreDbFilenames);
			if (!preg_match('#auto-backupdb_([0-9]{6})_#', $currentDbFilename, $match))
			{
				$this->next = 'error';
				$this->next_desc = $this->l(sprintf('%s : File format does not match', $currentDbFilename));
				return false;
			}

			$this->nextParams['dbStep'] = $match[1];
			$backupdb_path = $this->backupPath.DIRECTORY_SEPARATOR.$this->restoreName;

			$dot_pos = strrpos($currentDbFilename, '.');
			$fileext = substr($currentDbFilename, $dot_pos+1);
			$requests = array();
			$errors = array();
			$content = '';
			switch ($fileext)
			{
				case 'bz':
				case 'bz2':
					$this->nextQuickInfo[] = 'opening backup db in bz mode';
					if ($fp = bzopen($backupdb_path.DIRECTORY_SEPARATOR.$currentDbFilename, 'r'))
					{
						while(!feof($fp))
							$content .= bzread($fp, 4096);
					}
					else
						die("error when trying to open in bzmode");
					break;
				case 'gz':
					$this->nextQuickInfo[] = 'opening backup db in gz mode';
					if ($fp = gzopen($backupdb_path.DIRECTORY_SEPARATOR.$currentDbFilename, 'r'))
					{
						while(!feof($fp))
							$content .= gzread($fp, 4096);
						gzclose($fp);
					}
					break;
					// default means sql ?
				default :
					$this->nextQuickInfo[] = 'opening backup db in txt mode';
					if ($fp = fopen($backupdb_path.DIRECTORY_SEPARATOR.$currentDbFilename, 'r'))
					{
						while(!feof($fp))
							$content .= fread($fp, 4096);
						fclose($fp);
					}
			}
			$currentDbFilename = '';

			if ($content == '')
			{
				$this->nextQuickInfo[] = $this->l('database backup is empty');
				$this->nextErrors[] = $this->l('database backup is empty');
				$this->next = 'rollback';
				return false;
			}

			// preg_match_all is better than preg_split (what is used in do Upgrade.php)
			// This way we avoid extra blank lines
			// option s (PCRE_DOTALL) added
			$listQuery = preg_split('/;[\n\r]+/Usm', $content);
			unset($content);
			// @TODO : drop all old tables (created in upgrade)
			// This part has to be executed only onces (if dbStep=0)
			if ($this->nextParams['dbStep'] == '1')
			{
				$all_tables = $this->db->executeS('SHOW TABLES LIKE "'._DB_PREFIX_.'%"', true, false);
				$ignore_stats_table = array(
					_DB_PREFIX_.'connections',
					_DB_PREFIX_.'connections_page',
					_DB_PREFIX_.'connections_source',
					_DB_PREFIX_.'guest',
					_DB_PREFIX_.'statssearch'
				);
				$drops = array();
				foreach ($all_tables as $k => $v)
				{
					$table = array_shift($v);
					$drops['drop table '.$k] = 'DROP TABLE IF EXISTS `'.bqSql($table).'`';
					$drops['drop view '.$k] = 'DROP VIEW IF EXISTS `'.bqSql($table).'`';
				}
				unset($all_tables);
				$listQuery = array_merge($drops, $listQuery);
			}
			@file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toRestoreQueryList, serialize($listQuery));
		}

		// handle current backup file
		if (!isset($listQuery))
			if (file_exists($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toRestoreQueryList))
				$listQuery = unserialize(file_get_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toRestoreQueryList));
			else
				$listQuery = array();

		// @todo : error if listQuery is not an array (that can happen if toRestoreQueryList is empty for example)
		$time_elapsed = time() - $start_time;
		if (is_array($listQuery) && (count($listQuery) > 0))
		{
			do
			{
				if (count($listQuery)<=0)
				{
					@unlink($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toRestoreQueryList);
					$currentDbFilename = '';
					if (count($this->restoreDbFilenames) > 0)
					{
						$this->stepDone = true;
						$this->status = 'ok';
						$this->next = 'restoreDb';
						$this->next_desc = sprintf($this->l('Database restoration file %1$s done. %2$s left ...'), $this->nextParams['dbStep'], count($this->restoreDbFilenames));
						$this->nextQuickInfo[] = sprintf('Database restoration file %1$s done. %2$s left ...', $this->nextParams['dbStep'], count($this->restoreDbFilenames));
						return true;
					}
					else
					{
						$this->stepDone = true;
						$this->status = 'ok';
						$this->next = 'rollbackComplete';
						$this->next_desc = $this->l('Database restoration done.');
						$this->nextQuickInfo[] = $this->l('database has been restored.');
						return true;
					}
				}
				// filesForBackup already contains all the correct files
				if (count($listQuery) == 0)
					continue;

				$query = array_shift($listQuery);
				if (!empty($query))
				{
					if (!$this->db->execute($query, false))
					{
						if (is_array($listQuery))
							$listQuery = array_unshift($listQuery, $query);
						$this->nextQuickInfo[] = '[SQL ERROR] '.$query.' - '.$this->db->getMsgError();
						$this->nextErrors[] = '[SQL ERROR] '.$query.' - '.$this->db->getMsgError();
						$this->next = 'error';
						$this->next_desc = $this->l('error during database restoration');
						return false;
					}
					// note : theses queries can be too big and can cause issues for display
					// else
					// $this->nextQuickInfo[] = '[OK] '.$query;
				}

				$time_elapsed = time() - $start_time;
			}
			while ($time_elapsed < self::$loopRestoreQueryTime);
			unset($query);
			$queries_left = count($listQuery);

			@file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toRestoreQueryList, serialize($listQuery));
			unset($listQuery);
			$this->next = 'restoreDb';
			$this->next_desc = sprintf($this->l('%1$s queries left for file %2$s...'), $queries_left, $this->nextParams['dbStep']);
		}
		else
		{
			$this->stepDone = true;
			$this->status = 'ok';
			$this->next = 'rollbackComplete';
			$this->next_desc = $this->l('Database restoration done.');
			$this->nextQuickInfo[] = $this->l('database has been restored.');
			return true;
		}

		return true;
	}

	public function ajaxProcessMergeTranslations()
	{
	}

	public function ajaxProcessBackupDb()
	{
		if (!$this->getConfig('PS_AUTOUP_BACKUP'))
		{
			$this->stepDone = true;
			$this->nextParams['dbStep'] = 0;
			$this->next_desc = sprintf($this->l('Database backup skipped. Now upgrading files ...'), $this->backupName);
			$this->next = 'upgradeFiles';
			return true;
		}

		$relative_backup_path = str_replace(_PS_ROOT_DIR_, '', $this->backupPath);
		$report = '';
		if (!ConfigurationTest::test_dir($relative_backup_path, false, $report))
		{
			$this->next_desc = $this->l('Backup directory is not writeable ');
			$this->nextQuickInfo[] = 'Backup directory is not writeable ';
			$this->nextErrors[] = 'Backup directory is not writeable "'.$this->backupPath.'"';
			$this->next = 'error';
			return false;
		}

		$this->stepDone = false;
		$this->next = 'backupDb';
		$this->nextParams = $this->currentParams;
		$start_time = time();

		$psBackupAll = true;
		$psBackupDropTable = true;
		if (!$psBackupAll)
		{
			$ignore_stats_table = array(_DB_PREFIX_.'connections',
														_DB_PREFIX_.'connections_page',
														_DB_PREFIX_.'connections_source',
														_DB_PREFIX_.'guest',
														_DB_PREFIX_.'statssearch');
		}
		else
			$ignore_stats_table = array();

		// INIT LOOP
		if (!file_exists($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toBackupDbList))
		{
			if (!is_dir($this->backupPath.DIRECTORY_SEPARATOR.$this->backupName))
			{
				mkdir($this->backupPath.DIRECTORY_SEPARATOR.$this->backupName);
			}
			$this->nextParams['dbStep'] = 0;
			$tablesToBackup = $this->db->executeS('SHOW TABLES LIKE "'._DB_PREFIX_.'%"', true, false);
			@file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toBackupDbList, serialize($tablesToBackup));
		}

		if (!isset($tablesToBackup))
			$tablesToBackup = unserialize(file_get_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toBackupDbList));
		$found = 0;
		$views = '';

		// MAIN BACKUP LOOP //
		$written = 0;
		do
		{
			if (!empty($this->nextParams['backup_table']))
			{
				// only insert (schema already done)
				$table = $this->nextParams['backup_table'];
				$lines = $this->nextParams['backup_lines'];
			}
			else
			{
				if (count($tablesToBackup) == 0)
					break;
				$table = current(array_shift($tablesToBackup));
				$this->nextParams['backup_loop_limit'] = 0;
			}

			if ($written == 0 || $written > self::$max_written_allowed)
			{
				// increment dbStep will increment filename each time here
				$this->nextParams['dbStep']++;
				// new file, new step
				$written = 0;
				if (isset($fp))
					fclose($fp);
				$backupfile = $this->backupPath.DIRECTORY_SEPARATOR.$this->backupName.DIRECTORY_SEPARATOR.$this->backupDbFilename;
				$backupfile = preg_replace("#_XXXXXX_#", '_'.str_pad($this->nextParams['dbStep'], 6, '0', STR_PAD_LEFT).'_', $backupfile);

				// start init file
				// Figure out what compression is available and open the file
				if (file_exists($backupfile))
				{
					$this->next = 'error';
					$this->nextQuickInfo[] = sprintf($this->l('backupfile %s already exists. Operation aborted.'), $backupfile);
					$this->nextErrors[] = sprintf($this->l('backupfile %s already exists. Operation aborted.'), $backupfile);
				}

				if (function_exists('bzopen'))
				{
					$backupfile .= '.bz2';
					$fp = bzopen($backupfile, 'w');
				}
				elseif (function_exists('gzopen'))
				{
					$backupfile .= '.gz';
					$fp = @gzopen($backupfile, 'w');
				}
				else
					$fp = @fopen($backupfile, 'w');

				if ($fp === false)
				{
					$this->nextQuickInfo[] = sprintf($this->l('Unable to create backup db file %s'), addslashes($backupfile));
					$this->nextErrors[] = sprintf($this->l('Unable to create backup db file %s'), addslashes($backupfile));
					$this->next = 'error';
					$this->next_desc = $this->l('Error during database backup.');
					return false;
				}

				$written += fwrite($fp, '/* Backup ' . $this->nextParams['dbStep'] . ' for ' . Tools14::getHttpHost(false, false) . __PS_BASE_URI__ . "\n *  at " . date('r') . "\n */\n");
				$written += fwrite($fp, "\n".'SET NAMES \'utf8\';'."\n\n");
				// end init file
			}


			// Skip tables which do not start with _DB_PREFIX_
			if (strlen($table) <= strlen(_DB_PREFIX_) || strncmp($table, _DB_PREFIX_, strlen(_DB_PREFIX_)) != 0)
				continue;

			// start schema : drop & create table only
			if (empty($this->currentParams['backup_table']))
			{
				// Export the table schema
				$schema = $this->db->executeS('SHOW CREATE TABLE `' . $table . '`', true, false);

				if (count($schema) != 1 ||
					!((isset($schema[0]['Table']) && isset($schema[0]['Create Table']))
						|| (isset($schema[0]['View']) && isset($schema[0]['Create View']))))
				{
					fclose($fp);
					@unlink($backupfile);
					$this->nextQuickInfo[] = sprintf($this->l('An error occurred while backing up. Unable to obtain the schema of %s'), $table);
					$this->nextErrors[] = sprintf($this->l('An error occurred while backing up. Unable to obtain the schema of %s'), $table);
					$this->next = 'error';
					$this->next_desc = $this->l('Error during database backup.');
					return false;
				}

				// case view
				if (isset($schema[0]['View']))
				{
					$views .= '/* Scheme for view' . $schema[0]['View'] . " */\n";
					if ($psBackupDropTable)
					{
						// If some *upgrade* transform a table in a view, drop both just in case
						$views .= 'DROP VIEW IF EXISTS `'.$schema[0]['View'].'`;'."\n";
						$views .= 'DROP TABLE IF EXISTS `'.$schema[0]['View'].'`;'."\n";
					}
					$views .= preg_replace('#DEFINER=[^\s]+\s#', 'DEFINER=CURRENT_USER ', $schema[0]['Create View']).";\n\n";
					$written += fwrite($fp, "\n".$views);
				}
				// case table
				elseif (isset($schema[0]['Table']))
				{
					// Case common table
					$written += fwrite($fp, '/* Scheme for table ' . $schema[0]['Table'] . " */\n");
					if ($psBackupDropTable && !in_array($schema[0]['Table'], $ignore_stats_table))
					{
						// If some *upgrade* transform a table in a view, drop both just in case
						$written += fwrite($fp, 'DROP VIEW IF EXISTS `'.$schema[0]['Table'].'`;'."\n");
						$written += fwrite($fp, 'DROP TABLE IF EXISTS `'.$schema[0]['Table'].'`;'."\n");
						// CREATE TABLE
						$written += fwrite($fp, $schema[0]['Create Table'] . ";\n\n");
					}
					// schema created, now we need to create the missing vars
					$this->nextParams['backup_table'] = $table;
					$lines = $this->nextParams['backup_lines'] = explode("\n", $schema[0]['Create Table']);
				}
			}
			// end of schema

			// POPULATE TABLE
			if (!in_array($table, $ignore_stats_table))
			{
				do
				{
					$backup_loop_limit = $this->nextParams['backup_loop_limit'];
					$data = $this->db->executeS('SELECT * FROM `'.$table.'` LIMIT '.(int)$backup_loop_limit.',200', false, false);
					$this->nextParams['backup_loop_limit'] += 200;
					$sizeof = $this->db->numRows();
					if ($data && ($sizeof > 0))
					{
						// Export the table data
						$written += fwrite($fp, 'INSERT INTO `'.$table."` VALUES\n");
						$i = 1;
						while ($row = $this->db->nextRow($data))
						{
							// this starts a row
							$s = '(';
							foreach ($row AS $field => $value)
							{
								$tmp = "'" . $this->db->escape($value, true) . "',";
								if ($tmp != "'',")
									$s .= $tmp;
								else
								{
									foreach ($lines as $line)
										if (strpos($line, '`'.$field.'`') !== false)
										{
											if (preg_match('/(.*NOT NULL.*)/Ui', $line))
												$s .= "'',";
											else
												$s .= 'NULL,';
											break;
										}
								}
							}
							$s = rtrim($s, ',');

							if ($i < $sizeof)
								$s .= "),\n";
							else
								$s .= ");\n";

							$written += fwrite($fp, $s);
							++$i;
						}
						$time_elapsed = time() - $start_time;
					}
					else
						break;
				}
				while(($time_elapsed < self::$loopBackupDbTime) || ($written < self::$max_written_allowed));
			}
			$found++;
			unset($this->nextParams['backup_table']);
			$time_elapsed = time() - $start_time;
			$this->nextQuickInfo[] = sprintf($this->l('%1$s table has been saved.'), $table);
		}
		while(($time_elapsed < self::$loopBackupDbTime) || ($written < self::$max_written_allowed));

		// end of loop
		if (isset($fp))
		{
			fclose($fp);
			unset($fp);
		}
		@file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toBackupDbList, serialize($tablesToBackup));
		if (count($tablesToBackup) > 0){
			$this->nextQuickInfo[] = sprintf($this->l('%1$s tables has been saved.'), $found);
			$this->next = 'backupDb';
			$this->stepDone = false;
			$this->next_desc = sprintf($this->l('database backup : %s table(s) left ...'), count($tablesToBackup));
			$this->nextQuickInfo[] = sprintf('database backup : %s table(s) left ...', count($tablesToBackup));
			return true;
		}
		if ($found == 0)
		{
			if (isset($backupfile))
				@unlink($backupfile);
			$this->nextQuickInfo[] = $this->l('No valid tables were found to backup. Backup cancelled.');
			$this->nextErrors[] = $this->l('No valid tables were found to backup. Backup cancelled.');
			$this->next = 'error';
			$this->next_desc = $this->l('Error during database backup.');
			return false;
		}
		else
		{
			unset($this->nextParams['backup_loop_limit']);
			unset($this->nextParams['backup_lines']);
			unset($this->nextParams['backup_table']);
			$this->nextQuickInfo[] = sprintf($this->l('%1$s tables has been saved.'), $found);
			$this->stepDone = true;
			// reset dbStep at the end of this step
			$this->nextParams['dbStep'] = 0;

			$this->next_desc = sprintf($this->l('database backup done in %s. Now upgrading files ...'), $this->backupName);
			$this->next = 'upgradeFiles';
			return true;
		}
		// for backup db, use autoupgrade/backup directory
		// @TODO : autoupgrade must not be static
		// maybe for big tables we should save them in more than one file ?
		// if an error occur, we assume the file is not saved
	}

	public function ajaxProcessBackupFiles()
	{
		if (!$this->getConfig('PS_AUTOUP_BACKUP'))
		{
			$this->stepDone = true;
			$this->next = 'backupDb';
			$this->next_desc = 'File backup skipped.';
			return true;
		}

		$this->nextParams = $this->currentParams;
		$this->stepDone = false;
		if (empty($this->backupFilesFilename))
		{
			$this->next = 'error';
			$this->next_desc = $this->l('error during backupFiles');
			$this->nextQuickInfo[] = '[ERROR] backupFiles filename has not been set';
			$this->nextErrors[] = '[ERROR] backupFiles filename has not been set';
			return false;
		}

		if (empty($this->nextParams['filesForBackup']))
		{
			// @todo : only add files and dir listed in "originalPrestashopVersion" list
			$filesToBackup = $this->_listFilesInDir($this->prodRootDir, 'backup', false);
			@file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toBackupFileList, serialize($filesToBackup));

			$this->nextQuickInfo[] = sprintf($this->l('%s Files to backup.'), count($this->toBackupFileList));
			$this->nextParams['filesForBackup'] = $this->toBackupFileList;

			// delete old backup, create new
			if (!empty($this->backupFilesFilename) && file_exists($this->backupPath.DIRECTORY_SEPARATOR.$this->backupFilesFilename))
				@unlink($this->backupPath.DIRECTORY_SEPARATOR.$this->backupFilesFilename);

			$this->nextQuickInfo[]	= sprintf($this->l('backup files initialized in %s'), $this->backupFilesFilename);
		}
		$filesToBackup = unserialize(file_get_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toBackupFileList));

		$this->next = 'backupFiles';
		$this->next_desc = sprintf($this->l('Backup files in progress. %d files left'), count($filesToBackup));
		if (is_array($filesToBackup))
		{
			if (!self::$force_pclZip && class_exists('ZipArchive', false))
			{
				$this->nextQuickInfo[] = $this->l('using class ZipArchive ...');
				$zip_archive = true;
				$zip = new ZipArchive();
				$zip->open($this->backupPath.DIRECTORY_SEPARATOR.$this->backupFilesFilename, ZIPARCHIVE::CREATE);
			}
			else
			{
				$zip_archive = false;
				$this->nextQuickInfo[] = $this->l('using class pclzip ...');
				// pclzip can be already loaded (server configuration)
				if (!class_exists('PclZip',false))
					require_once(dirname(__FILE__).'/classes/pclzip.lib.php');
				$zip = new PclZip($this->backupPath.DIRECTORY_SEPARATOR.$this->backupFilesFilename);
			}
			if ($zip)
			{
				$this->next = 'backupFiles';
				$this->stepDone = false;
				$files_to_add = array();
				for ($i = 0; $i < self::$loopBackupFiles; $i++)
				{
					if (count($filesToBackup) <= 0)
					{
						$this->stepDone = true;
						$this->status = 'ok';
						$this->next = 'backupDb';
						$this->next_desc = $this->l('All files saved. Now backup Database');
						$this->nextQuickInfo[] = $this->l('all files have been added to archive.', 'AdminSelfUpgrade', true);
						break;
					}
					// filesForBackup already contains all the correct files
					$file = array_shift($filesToBackup);

					$archiveFilename = ltrim(str_replace($this->prodRootDir, '', $file), DIRECTORY_SEPARATOR);
					if ($zip_archive)
					{
						$added_to_zip = $zip->addFile($file, $archiveFilename);
						if ($added_to_zip)
							$this->nextQuickInfo[] = sprintf($this->l('%1$s added to archive. %2$s left.', 'AdminSelfUpgrade', true), $archiveFilename, count($filesToBackup));
						else
						{
							// if an error occur, it's more safe to delete the corrupted backup
							$zip->close();
							if (file_exists($this->backupPath.DIRECTORY_SEPARATOR.$this->backupFilesFilename))
								@unlink($this->backupPath.DIRECTORY_SEPARATOR.$this->backupFilesFilename);
							$this->next = 'error';
							$this->next_desc = sprintf($this->l('error when trying to add %1$s to archive %2$s.', 'AdminSelfUpgrade', true),$file, $archiveFilename);
							break;
						}
					}
					else
					{
						$files_to_add[] = $file;
						$this->nextQuickInfo[] = sprintf($this->l('%1$s added to archive. %2$s left.', 'AdminSelfUpgrade', true), $archiveFilename, count($filesToBackup));
					}
				}

				if ($zip_archive)
					$zip->close();
				else
				{
					$added_to_zip = $zip->add($files_to_add, PCLZIP_OPT_REMOVE_PATH, $this->prodRootDir);
					$zip->privCloseFd();
					if (!$added_to_zip)
					{
						$this->nextQuickInfo[] = '[ERROR] error on backup using pclzip : '.$zip->errorInfo(true);
						$this->nextErrors[] = '[ERROR] error on backup using pclzip : '.$zip->errorInfo(true);
						$this->next = 'error';
					}
				}

				@file_put_contents($this->autoupgradePath.DIRECTORY_SEPARATOR.$this->toBackupFileList,serialize($filesToBackup));
				return true;
			}
			else{
				$this->next = 'error';
				$this->next_desc = $this->l('unable to open archive');
				return false;
			}
		}
		else
		{
			$this->stepDone = true;
			$this->next = 'backupDb';
			$this->next_desc = 'All files saved. Now backup Database';
			return true;
		}
		// 4) save for display.
	}


	private function _removeOneSample($removeList)
	{
		if (is_array($removeList) AND count($removeList) > 0)
		{
			if (file_exists($removeList[0]) AND @unlink($removeList[0]))
			{
				$item = str_replace($this->prodRootDir, '', array_shift($removeList));
				$this->next = 'removeSamples';
				$this->nextParams['removeList'] = $removeList;
				if(count($removeList) > 0)
					$this->nextQuickInfo[] = sprintf($this->l('%1$s removed. %2$s items left'), $item, count($removeList));
			}
			else
			{
				$this->next = 'error';
				$this->nextParams['removeList'] = $removeList;
				$this->nextQuickInfo[] = sprintf($this->l('error when removing %1$s, %2$s items left'), $removeList[0], count($removeList));
				$this->nextErrors[] = sprintf($this->l('error when removing %1$s, %2$s items left'), $removeList[0], count($removeList));
				return false;
			}
		}
		return true;
	}

	/**
	 * Remove all sample files.
	 *
	 * @return boolean true if succeed
	 */
	public function ajaxProcessRemoveSamples()
	{
		$this->stepDone = false;
		// remove all sample pics in img subdir
		if (!isset($this->currentParams['removeList']))
		{
			$this->_listSampleFiles($this->latestPath.'/prestashop/img/c', '.jpg');
			$this->_listSampleFiles($this->latestPath.'/prestashop/img/cms', '.jpg');
			$this->_listSampleFiles($this->latestPath.'/prestashop/img/l', '.jpg');
			$this->_listSampleFiles($this->latestPath.'/prestashop/img/m', '.jpg');
			$this->_listSampleFiles($this->latestPath.'/prestashop/img/os', '.jpg');
			$this->_listSampleFiles($this->latestPath.'/prestashop/img/p', '.jpg');
			$this->_listSampleFiles($this->latestPath.'/prestashop/img/s', '.jpg');
			$this->_listSampleFiles($this->latestPath.'/prestashop/img/scenes', '.jpg');
			$this->_listSampleFiles($this->latestPath.'/prestashop/img/st', '.jpg');
			$this->_listSampleFiles($this->latestPath.'/prestashop/img/su', '.jpg');
			$this->_listSampleFiles($this->latestPath.'/prestashop/img', '404.gif');
			$this->_listSampleFiles($this->latestPath.'/prestashop/img', 'favicon.ico');
			$this->_listSampleFiles($this->latestPath.'/prestashop/img', 'logo.jpg');
			$this->_listSampleFiles($this->latestPath.'/prestashop/img', 'logo_stores.gif');
			$this->_listSampleFiles($this->latestPath.'/prestashop/modules/editorial', 'homepage_logo.jpg');
			// remove all override present in the archive
			$this->_listSampleFiles($this->latestPath.'/prestashop/override', '.php');

			if (count($this->sampleFileList) > 0)
				$this->nextQuickInfo[] = sprintf($this->l('Starting to remove %1$s sample files'), count($this->sampleFileList));

			$this->nextParams['removeList'] = $this->sampleFileList;
		}

		$resRemove = true;
		for($i = 0; $i < self::$loopRemoveSamples; $i++)
		{
			if (count($this->nextParams['removeList']) <= 0 )
			{
				$this->stepDone = true;
				if ($this->getConfig('skip_backup'))
				{
					$this->next = 'upgradeFiles';
					$this->next_desc = $this->l('All sample files removed. Backup process skipped. Now upgrading Files.');
				}
				else
				{
					$this->next = 'backupFiles';
					$this->next_desc = $this->l('All sample files removed. Now backup files.');
				}
				// break the loop, all sample already removed
				return true;
			}
			$resRemove &= $this->_removeOneSample($this->nextParams['removeList']);
			if (!$resRemove)
				break;
		}

		return $resRemove;
	}

	/**
	 * download PrestaShop archive according to the chosen channel
	 *
	 * @access public
	 */
	public function ajaxProcessDownload()
	{
		if (ConfigurationTest::test_fopen() || ConfigurationTest::test_curl())
		{
			if (!is_object($this->upgrader))
				$this->upgrader = new Upgrader();
			// regex optimization
			preg_match('#([0-9]+\.[0-9]+)(?:\.[0-9]+){1,2}#', _PS_VERSION_, $matches);
			$this->upgrader->channel = $this->getConfig('channel');
			$this->upgrader->branch = $matches[1];
			if ($this->getConfig('channel') == 'private' && !$this->getConfig('private_allow_major'))
				$this->upgrader->checkPSVersion(false, array('private', 'minor'));
			else
				$this->upgrader->checkPSVersion(false, array('minor'));

			if ($this->upgrader->channel == 'private')
			{
				$this->upgrader->link = $this->getConfig('private_release_link');
				$this->upgrader->md5 = $this->getConfig('private_release_md5');
			}
			$this->nextQuickInfo[] = sprintf($this->l('downloading from %s'), $this->upgrader->link);
			$this->nextQuickInfo[] = sprintf($this->l('file will be saved in %s'), $this->downloadPath.DIRECTORY_SEPARATOR.$this->destDownloadFilename);
			
			$report = '';
			$relative_download_path = str_replace(_PS_ROOT_DIR_, '', $this->downloadPath);
			if (ConfigurationTest::test_dir($relative_download_path, false, $report))
			{
				$res = $this->upgrader->downloadLast($this->downloadPath, $this->destDownloadFilename);
				if ($res)
				{
					$md5file = md5_file(realpath($this->downloadPath).DIRECTORY_SEPARATOR.$this->destDownloadFilename);
				 	if ($md5file == $this->upgrader->md5)
					{
						$this->nextQuickInfo[] = $this->l('Download complete.');
						$this->next = 'unzip';
						$this->next_desc = $this->l('Download complete. Now extracting');
					}
					else
					{
						$this->nextQuickInfo[] = sprintf($this->l('Download complete but md5sum does not match (%s)'), $md5file);
						$this->nextErrors[] = sprintf($this->l('Download complete but md5sum does not match (%s)'), $md5file);
						$this->next = 'error';
						$this->next_desc = $this->l('Download complete but md5sum does not match. Operation aborted.');
					}
				}
				else
				{
					if ($this->upgrader->channel == 'private')
					{
						$this->next_desc = $this->l('Error during download. The private key may be incorrect.');
						$this->nextQuickInfo[] = $this->l('Error during download. The private key may be incorrect.');
						$this->nextErrors[] = $this->l('Error during download. The private key may be incorrect.');
					}
					else
					{
						$this->next_desc = $this->l('Error during download');
						$this->nextQuickInfo[] = $this->l('Error during download');
						$this->nextErrors[] = $this->l('Error during download');
					}
					$this->next = 'error';
				}
			}
			else
			{
				$this->next_desc = $this->l('Download directory is not writeable ');
				$this->nextQuickInfo[] = $this->l('Download directory is not writeable ');
				$this->nextErrors[] = $this->l('Download directory is not writeable ').$this->downloadPath;
				$this->next = 'error';
			}
		}
		else
		{
			$this->nextQuickInfo[] = $this->l('you need allow_url_fopen for automatic download.');
			$this->nextErrors[] = $this->l('you need allow_url_fopen for automatic download.');
			$this->next = 'error';
			$this->next_desc = sprintf($this->l('you need allow_url_fopen for automatic download. You can also manually upload it in %s'),$this->downloadPath.$this->destDownloadFilename);
		}
	}

	public function buildAjaxResult()
	{
		$return = array();

		$return['error'] = $this->error;
		$return['stepDone'] = $this->stepDone;
		$return['next'] = $this->next;
		$return['status'] = $this->next == 'error' ? 'error' : 'ok';
		$return['next_desc'] = $this->next_desc;

		$this->nextParams['config'] = $this->getConfig();

		foreach($this->ajaxParams as $v)
			if(property_exists($this,$v))
				$this->nextParams[$v] = $this->$v;
			else
				$this->nextQuickInfo[] = sprintf('[WARNING] property %s is missing', $v);

		$return['nextParams'] = $this->nextParams;
		if (!isset($return['nextParams']['dbStep']))
			$return['nextParams']['dbStep'] = 0;

		$return['nextParams']['typeResult'] = $this->nextResponseType;

		$return['nextQuickInfo'] = $this->nextQuickInfo;
		$return['nextErrors'] = $this->nextErrors;
		return Tools14::jsonEncode($return);
	}

	public function ajaxPreProcess()
	{
		/* PrestaShop demo mode */
		if (defined('_PS_MODE_DEMO_') && _PS_MODE_DEMO_)
			return;

		/* PrestaShop demo mode*/
		if (!empty($_POST['responseType']) && $_POST['responseType'] == 'json')
			header('Content-Type: application/json');

		if (!empty($_POST['action']))
		{
			$action = $_POST['action'];
			if (isset(self::$skipAction[$action]))
			{
				$this->next = self::$skipAction[$action];
				$this->next_desc = sprintf($this->l('action %s skipped'),$action);
				$this->nextQuickInfo[] = sprintf($this->l('action %s skipped'),$action);
				unset($_POST['action']);
			}
			else if (!method_exists(get_class($this), 'ajaxProcess'.$action))
			{
				$this->next_desc = sprintf($this->l('action "%1$s" not found'), $action);
				$this->next = 'error';
				$this->error = '1';
			}
		}

		if (!method_exists('Tools', 'apacheModExists') || Tools14::apacheModExists('evasive'))
			sleep(1);
	}

	private function _getJsErrorMsgs()
	{
		$INSTALL_VERSION = $this->install_version;
		$ret = '
var txtError = new Array();
txtError[0] = "'.$this->l('Required field').'";
txtError[1] = "'.$this->l('Too long!').'";
txtError[2] = "'.$this->l('Fields are different!').'";
txtError[3] = "'.$this->l('This email adress is wrong!').'";
txtError[4] = "'.$this->l('Impossible to send the email!').'";
txtError[5] = "'.$this->l('Cannot create settings file, if /config/settings.inc.php exists, please give the public write permissions to this file, else please create a file named settings.inc.php in config directory.').'";
txtError[6] = "'.$this->l('Cannot write settings file, please create a file named settings.inc.php in config directory.').'";
txtError[7] = "'.$this->l('Impossible to upload the file!').'";
txtError[8] = "'.$this->l('Data integrity is not valided. Hack attempt?').'";
txtError[9] = "'.$this->l('Impossible to read the content of a MySQL content file.').'";
txtError[10] = "'.$this->l('Impossible the access the a MySQL content file.').'";
txtError[11] = "'.$this->l('Error while inserting data in the database:').'";
txtError[12] = "'.$this->l('The password is incorrect (alphanumeric string at least 8 characters).').'";
txtError[14] = "'.$this->l('A Prestashop database already exists, please drop it or change the prefix.').'";
txtError[15] = "'.$this->l('This is not a valid file name.').'";
txtError[16] = "'.$this->l('This is not a valid image file.').'";
txtError[17] = "'.$this->l('Error while creating the /config/settings.inc.php file.').'";
txtError[18] = "'.$this->l('Error:').'";
txtError[19] = "'.$this->l('This PrestaShop database already exists. Please revalidate your authentication informations to the database.').'";
txtError[22] = "'.$this->l('An error occurred while resizing the picture.').'";
txtError[23] = "'.$this->l('Database connection is available!').'";
txtError[24] = "'.$this->l('Database Server is available but database is not found').'";
txtError[25] = "'.$this->l('Database Server is not found. Please verify the login, password and server fields.').'";
txtError[26] = "'.$this->l('An error occurred while sending email, please verify your parameters.').'";
txtError[37] = "'.$this->l('Impossible to write the image /img/logo.jpg. If this image already exists, please delete it.').'";
txtError[38] = "'.$this->l('The uploaded file exceeds the upload_max_filesize directive in php.ini').'";
txtError[39] = "'.$this->l('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form').'";
txtError[40] = "'.$this->l('The uploaded file was only partially uploaded').'";
txtError[41] = "'.$this->l('No file was uploaded.').'";
txtError[42] = "'.$this->l('Missing a temporary folder').'";
txtError[43] = "'.$this->l('Failed to write file to disk').'";
txtError[44] = "'.$this->l('File upload stopped by extension').'";
txtError[45] = "'.$this->l('Cannot convert your database\'s data to utf-8.').'";
txtError[46] = "'.$this->l('Invalid shop name').'";
txtError[47] = "'.$this->l('Your firstname contains some invalid characters').'";
txtError[48] = "'.$this->l('Your lastname contains some invalid characters').'";
txtError[49] = "'.$this->l('Your database server does not support the utf-8 charset.').'";
txtError[50] = "'.$this->l('Your MySQL server doesn\'t support this engine, please use another one like MyISAM').'";
txtError[51] = "'.$this->l('The file /img/logo.jpg is not writable, please CHMOD 755 this file or CHMOD 777').'";
txtError[52] = "'.$this->l('Invalid catalog mode').'";
txtError[999] = "'.$this->l('No error code available').'";
//upgrader
txtError[27] = "'.$this->l('This installer is too old.').'";
txtError[28] = "'.sprintf($this->l('You already have the %s version.'),$INSTALL_VERSION).'";
txtError[29] = "'.$this->l('There is no older version. Did you delete or rename the configsettings.inc.php file?').'";
txtError[30] = "'.$this->l('The config/settings.inc.php file was not found. Did you delete or rename this file?').'";
txtError[31] = "'.$this->l('Cannot find the sql upgrade files. Please verify that the /install/sql/upgrade folder is not empty)').'";
txtError[32] = "'.$this->l('No upgrade is possible.').'";
txtError[33] = "'.$this->l('Error while loading sql upgrade file.').'";
txtError[34] = "'.$this->l('Error while inserting content into the database').'";
txtError[35] = "'.$this->l('Unfortunately,').'";
txtError[36] = "'.$this->l('SQL errors have occurred.').'";
txtError[37] = "'.$this->l('The config/defines.inc.php file was not found. Where did you move it?').'";';
		return $ret;
	}

	public function displayAjax()
	{
		echo $this->buildAjaxResult();
	}

	protected function getBackupFilesAvailable()
	{
		$array = array();
		$files = scandir($this->backupPath);
		foreach ($files as $file)
			if ($file[0] != '.')
			{
				if (substr($file, 0, 16) == 'auto-backupfiles')
					$array[] = preg_replace('#^auto-backupfiles_(.*-[0-9a-f]{1,8})\..*$#', '$1', $file);
			}

		return $array;
	}

	protected function getBackupDbAvailable()
	{
		$array = array();

		$files = scandir($this->backupPath);

		foreach($files as $file)
			if ($file[0] == 'V' && is_dir($this->backupPath.DIRECTORY_SEPARATOR.$file))
			{
				$array[] = $file;
			}
		return $array;
	}

	protected function _displayRollbackForm()
	{
		$backup_available = array_intersect($this->getBackupDbAvailable(), $this->getBackupFilesAvailable());
		if (!count($backup_available))
			return;
		
		$this->_html .= '
		<fieldset style="margin-top:10px">
			<legend><img src="../img/admin/previous.gif"/>'.$this->l('Rollback').'</legend>
			<div id="rollbackForm">
				<p>
					'.$this->l('After upgrading your shop, you can rollback to the previously database and files. Use this function if your theme or an essential module is not working correctly.').'
				</p>
				<br/>
				<div id="rollbackContainer">
					<a disabled="disabled" class="upgradestep button" href="" id="rollback">'.$this->l('Rollback').'</a>
				</div>
				<br/>
				<div id="restoreBackupContainer">
					'.$this->l('Choose your backup:').'
					<select name="restoreName">
						<option value="0">'.$this->l('-- Choose a backup to restore --').'</option>';
						foreach ($backup_available as $backup_name)
							$this->_html .= '<option value="'.$backup_name.'">'.$backup_name.'</option>';
		$this->_html .= '</select>
				</div>
				<div class="clear">&nbsp;</div>
			</div>
		</fieldset>';
	}

	/** this returns fieldset containing the configuration points you need to use autoupgrade
	 * @return string
	 */
	private function _displayCurrentConfiguration()
	{	
		$current_ps_config = $this->getcheckCurrentPsConfig();
		
		$this->_html .= '
		<fieldset id="currentConfigurationBlock" class="width autoupgrade" style="float: left; width: 60%; margin-left: 30px;">
			<legend>'.$this->l('The pre-Upgrade checklist').'</legend>';
		if (!$this->configOk())
				$this->_html .= '<div class="clear"><br></div><p class="warn">'.$this->l('The checklist is not ok. You can not upgrade your shop until every indicator will not be green.').'</p>';
			
		$this->_html .= '<div id="currentConfiguration">
				<table class="table" cellpadding="0" cellspacing="0">
				<p>'.$this->l('Before starting the upgrade process, please make sure this checklist is all green.').'</p>';

		$pic_ok = '<img src="../img/admin/enabled.gif" alt="ok"/>';
		$pic_nok = '<img src="../img/admin/disabled.gif" alt="nok"/>';
		$pic_warn = '<img src="../img/admin/warning.gif" alt="warn"/>';
		// module version : checkAutoupgradeLastVersion
		$this->_html .= '
				<tr>
					<th>'.sprintf($this->l('The 1-click upgrade module is up-to-date (your current version is v%s)'), $this->getModuleVersion()).'</th>
					<td>'.($current_ps_config['module_version_ok'] ? $pic_ok : $pic_nok).'</td>
				</tr>';

		// root : getRootWritable()

		$this->_html .= '<th>'.$this->l('Your store root directory must be writeable (appropriate CHMOD permissions)').'</th>
			<td>'.($current_ps_config['root_writable'] ? $pic_ok : $pic_nok.' '.$this->root_writable_report).'</td></tr>';

		//check safe_mod
		$this->_html .= '<th>'.$this->l('The PHP "Safe mode" option must be turned off').'</th>
			<td>'.(!ini_get('safe_mode') ? $pic_ok : $pic_warn).'</td></tr>';

		$this->_html .= '<th>'.$this->l('The PHP "allow_url_fopen" option must be turned on or CURL must be installed').'</th>
			<td>'.((ConfigurationTest::test_fopen() || ConfigurationTest::test_curl()) ? $pic_ok : $pic_nok).'</td></tr>';

		// shop enabled
		$this->_html .= '<th>'.$this->l('You must put your store under maintenance').' '.(!$current_ps_config['shop_deactivated'] ? '<br><form method="post" action="'.$this->currentIndex.'&token='.$this->token.'"><input type="submit" class="button" name="putUnderMaintenance" value="'.$this->l('Click here to put your shop under maintenance').'"></form>' : '').'</th>
			<td>'.($current_ps_config['shop_deactivated'] ? $pic_ok : $pic_nok).'</td></tr>';

		$this->_html .= '<th>'.$this->l('You must disable the Caching features of PrestaShop').'</th>
			<td>'.($current_ps_config['cache_deactivated'] ? $pic_ok : $pic_nok).'</td></tr>';

		// for informaiton, display time limit
		$max_exec_time = ini_get('max_execution_time');
		$this->_html .= '<th>'.sprintf($this->l('The PHP time limit must be either high or disabled (Current value: %s)'), ($max_exec_time == 0 ? $this->l('unlimited') : $max_exec_time.' '.$this->l('seconds'))).'</th>
			<td>'.($max_exec_time == 0 ? $pic_ok : $pic_warn).'</td></tr>
				</table>
				<p>'.$this->l('Please also make sure you proceeded to a full manual backup of your files and database.').'</p>
			</div>
		</fieldset>';
	}

	public function divChannelInfos($upgrade_info)
	{
		if ($this->getConfig('channel') == 'private')
		{
			$upgrade_info['link'] = $this->getConfig('private_release_link');
			$upgrade_info['md5'] = $this->getConfig('private_release_md5');
		}
		$content = '<div id="channel-infos" ><br/>';
		if (isset($upgrade_info['branch']))
		{
			$content .= '<div style="clear:both">
				<label class="label-small">'.$this->l('branch:').'</label>
				<div class="margin-form margin-form-small" style="padding-top:5px">
					<span class="available">
						<img src="../img/admin/'.(!empty($upgrade_info['available'])?'enabled':'disabled').'.gif" />'
				.' '.(!empty($upgrade_info['available'])?$this->l('available'):$this->l('unavailable')).'
					</span>
				</div></div>';
		}
		$content .= '<div class="all-infos">';
		if (isset($upgrade_info['version_name']))
			$content .= '<div style="clear:both;">
			<label class="label-small">'.$this->l('name:').'</label>
				<div class="margin-form margin-form-small" style="padding-top:5px" >
				<span class="name">'.$upgrade_info['version_name'].'&nbsp;</span></div>
				</div>';
		if (isset($upgrade_info['version_number']))
			$content .= '<div style="clear:both;">
			<label class="label-small">'.$this->l('version number:').'</label>
				<div class="margin-form margin-form-small" style="padding-top:5px" >
				<span class="version">'.$upgrade_info['version_num'].'&nbsp;</span></div>
				</div>';
		if (!empty($upgrade_info['link']))
		{
			$content .= '<div style="clear:both;">
			<label class="label-small">'.$this->l('url:').'</label>
				<div class="margin-form margin-form-small" style="padding-top:5px" style="">
					<a class="url" href="'.$upgrade_info['link'].'">'.$upgrade_info['link'].'</a>
				</div>
				</div>';
		}
		if (!empty($upgrade_info['md5']))
			$content .= '<div style="clear:both;">
			<label class="label-small">'.$this->l('md5:').'</label>
				<div class="margin-form margin-form-small" style="padding-top:5px" style="">
				<span class="md5">'.$upgrade_info['md5'].'&nbsp;</span></div></div>';

		if (!empty($upgrade_info['changelog']))
			$content .= '<div style="clear:both;">
			<label class="label-small">'.$this->l('changelog:').'</label>
				<div class="margin-form margin-form-small" style="padding-top:5px" style="">
				<a class="changelog" href="'.$upgrade_info['changelog'].'">'.$this->l('see changelog').'</a>
				</div></div>';

		$content .= '</div></div>';
		return $content;
	}

	public function getBlockSelectChannel($channel = 'minor')
	{
		$admin_dir = trim(str_replace($this->prodRootDir, '', $this->adminDir), DIRECTORY_SEPARATOR);
		$content = '';
		$opt_channels = array();
		// Hey ! I'm really using a fieldset element to regroup fields ?! !
		$opt_channels[] = '<option id="useMajor" value="major" '.($channel == 'major'?'class="current" selected="selected">* ':'>')
			.$this->l('Major release').'</option>';
		$opt_channels[] = '<option id="useMinor" value="minor" '.($channel == 'minor'?'class="current" selected="selected">* ':'>')
			.$this->l('Minor release (recommended)').'</option>';
		$opt_channels[] = '<option id="useRC" value="rc" '.($channel == 'rc'?'class="current" selected="selected">* ':'>')
			.$this->l('Release candidates').'</option>';
		$opt_channels[] = '<option id="useBeta" value="beta" '.($channel == 'beta'?'class="current" selected="selected">* ':'>')
			.$this->l('Beta releases').'</option>';
		$opt_channels[] = '<option id="useAlpha" value="alpha" '.($channel == 'alpha'?'class="current" selected="selected">* ':'>')
			.$this->l('Alpha releases').'</option>';
		$opt_channels[] = '<option id="usePrivate" value="private" '.($channel == 'private'?'class="current" selected="selected">* ':'>')
			.$this->l('Private release (require link and md5 hashkey)').'</option>';
		$opt_channels[] = '<option id="useArchive" value="archive" '.($channel == 'archive'?'class="current" selected="selected">* ':'>')
			.$this->l('Local archive').'</option>';
		$opt_channels[] = '<option id="useDirectory" value="directory" '.($channel == 'directory'?'class="current" selected="selected">* ':'>')
			.$this->l('Local directory').'</option>';

		$content .= '<label class="label-small">'.$this->l('Channel:').'</label><select name="channel" >';
		$content .= implode('', $opt_channels);
		$content .= '</select>';
		$upgrade_info = $this->getInfoForChannel($channel);
		$content .= $this->divChannelInfos($upgrade_info);

		$content .= '<div id="for-useMinor" ><div class="margin-form margin-form-small">'.$this->l('This option regroup all stable versions.').'</div></div>';
		$content .= '<div id="for-usePrivate">
			<p><label class="label-small">'.$this->l('Link:').'</label>
			<input size="50" type="text" name="private_release_link" value="'.$this->getConfig('private_release_link').'"/> *
			</p>
			<p><label class="label-small">'.$this->l('Hash key:').'</label>
			<input size="32" type="text" name="private_release_md5" value="'.$this->getConfig('private_release_md5').'"/> *
			</p>
			<p><label class="label-small">'.$this->l('Allow major upgrade:').'</label>
			<input type="checkbox" name="private_allow_major" value="1" '.($this->getConfig('private_allow_major')?'checked="checked"':'').'"/>
			</p>

			</div>';

		$download = $this->downloadPath.DIRECTORY_SEPARATOR;
		$dir = glob($download.'*.zip');
		$content .= '<div id="for-useArchive">';
		if ($dir !== false && count($dir) > 0)
		{
			$archive_filename = $this->getConfig('archive.filename');
			$content .= '<label class="label-small">'.$this->l('Archive to use:').'</label><div><select name="archive_prestashop" >
				<option value="">'.$this->l('choose an archive').'</option>';
			foreach ($dir as $file)
				$content .= '<option '.($archive_filename ? 'selected="selected"' : '').' value="'.str_replace($download, '', $file).'">'.str_replace($download, '', $file).'</option>';
			$content .= '</select> '
				.$this->l('to upgrade for version').' <input type="text" size="10" name="archive_num"
				value="'.($this->getConfig('archive.version_num')?$this->getConfig('archive.version_num'):'').'" /> *
			 	</div>';
		}
		else
			$content .= '<div class="warn">'.$this->l('No archive found in your admin/autoupgrade/download directory').'</div>';

		$content .= '<div class="margin-form">'.$this->l('This option will skip download step').'</div></div>';
		// $directory_dirname = $this->getConfig('directory.dirname');
		$content .= '<div id="for-useDirectory">
			<p> '.
			sprintf($this->l('The directory %1$s will be used for upgrading to version '),
				'<b>/admin/autoupgrade/latest/prestashop/</b>' ).
			' <input type="text" size="10" name="directory_num"
			value="'.($this->getConfig('directory.version_num')?$this->getConfig('directory.version_num'):'').'" /> *
			<br/>
			<div class="margin-form">'
			.$this->l('This option will skip both download and unzip steps and will use admin/autoupgrde/download/prestashop/ as source.').'</div>
			</div>';
		// backupFiles
		// backupDb
		$content .= '<div style="clear:both;">
				<div class="margin-form" style="">
					<input type="button" class="button" value="'.$this->l('Save').'" name="submitConf-channel" />
				</div>
			</div>';
		$content .= '</form>';
		return $content;
	}

	public function getBlockConfigurationAdvanced()
	{
		$content = '
		<div>
			<input type="button" class="button" style="float:right" name="btn_adv" value="'.$this->l('More options (Expert mode)').'"/>
			</div>
			<div style="float: left; margin-top: 13px; display:none;" id="configResult">&nbsp;</div>
			<div class="clear" id="advanced">
				<h3>'.$this->l('Expert mode').'</3>
				<h4 style="margin-top: 0px;">'.$this->l('Please select your channel:').'</h4>
				<p>'.$this->l('Channels are offering you different ways to perform an upgrade. You can either upload the new version manually or let the 1-click upgrade module download it for you.').'<br />'.
				$this->l('Alpha, Beta and Private channels, give you the ability to upgrade to a non-official or unstable release (for testing purposes only).').'<br />'.
				$this->l('By default, you should use the "Minor release" channel which is offering the latest stable version available.').'</p><br />';

		$config = $this->getConfig();
		$channel = $config['channel'];
		if (empty($channel))
			$channel = Upgrader::DEFAULT_CHANNEL;

		$content .= $this->getBlockSelectChannel($channel).'
		</form>
		</div>';

		return $content;
	}

	public function displayDevTools()
	{
		$content = '';
		$content .= '<br class="clear"/>';
		$content .= '<fieldset class="autoupgradeSteps"><legend>'.$this->l('Step').'</legend>';
		$content .= '<h4>'.$this->l('Upgrade steps').'</h4>';
		$content .= '<div>';
		$content .= '<a href="" id="download" class="button upgradestep" >download</a>';
		$content .= '<a href="" id="unzip" class="button upgradestep" >unzip</a>'; // unzip in autoupgrade/latest
		$content .= '<a href="" id="removeSamples" class="button upgradestep" >removeSamples</a>'; // remove samples (iWheel images)
		$content .= '<a href="" id="backupFiles" class="button upgradestep" >backupFiles</a>'; // backup files
		$content .= '<a href="" id="backupDb" class="button upgradestep" >backupDb</a>';
		$content .= '<a href="" id="upgradeFiles" class="button upgradestep" >upgradeFiles</a>';
		$content .= '<a href="" id="upgradeDb" class="button upgradestep" >upgradeDb</a>';
		$content .= '<a href="" id="upgradeModules" class="button upgradestep" >upgradeModules</a>';
		$content .= '<a href="" id="cleanDatabase" class="button upgradestep" >cleanDb</a>';		
		$content .= '<a href="" id="upgradeComplete" class="button upgradestep" >upgradeComplete</a>';
		$content .= '</div>';

		return $content;
	}

	private function _displayComparisonBlock()
	{
		$this->_html .= '
		<fieldset id="comparisonBlock">
			<legend>'.$this->l('Version comparison').'</legend>
			<b>'.$this->l('PrestaShop Original version').':</b><br/>
			<span id="checkPrestaShopFilesVersion">
				<img id="pleaseWait" src="'.__PS_BASE_URI__.'img/loader.gif"/>
			</span><br/>
			<b>'.$this->l('differences between versions').':</b><br/>
			<span id="checkPrestaShopModifiedFiles">
				<img id="pleaseWait" src="'.__PS_BASE_URI__.'img/loader.gif"/>
			</span>
		</fieldset>';
	}

	private function _displayBlockActivityLog()
	{
		$this->_html .= '
			<fieldset id="activityLogBlock" style="display:none">
			<legend><img src="../img/admin/slip.gif" /> '.$this->l('Activity Log').'</legend>
			<p id="upgradeResultCheck"></p>
			<div id="upgradeResultToDoList"></div>
			<div id="currentlyProcessing" style="display:none;float:left">
			<h4 id="pleaseWait">'.$this->l('Currently processing').' <img class="pleaseWait" src="'.__PS_BASE_URI__.'img/loader.gif"/></h4>
			<div id="infoStep" class="processing" >'.$this->l('Analyzing the situation ...').'</div>
			</div>';
		// this block will show errors and important warnings that happens during upgrade
		$this->_html .= '
			<div id="errorDuringUpgrade" style="display:none;float:right">
			<h4>'.$this->l('Errors').'</h4>
			<div id="infoError" class="processing" ></div>';
		$this->_html .= '
			</div>
			<div class="clear">&nbsp;</div>
			<div id="quickInfo" class="processing"></div></fieldset>
			</fieldset>';
	}
	/**
	 * _displayBlockUpgradeButton
	 * display the summary current version / target vesrion + "Upgrade Now" button with a "more options" button
	 *
	 * @access private
	 * @return void
	 */
	private function _displayBlockUpgradeButton()
	{
		global $cookie;
		$admin_dir = trim(str_replace($this->prodRootDir, '', $this->adminDir), DIRECTORY_SEPARATOR);

		$this->_html .= '
		<fieldset id="upgradeButtonBlock">
			<legend>'.$this->l('Start your Upgrade').'</legend>
			<div class="blocOneClickUpgrade">';
		if (version_compare(_PS_VERSION_, $this->upgrader->version_num, '>='))
			$this->_html .= '<p>'.$this->l('Congratulations you are already using the latest version available !').'</p>';
		$this->_html .= '<table class="table" cellpadding="0" cellspacing="0"><tr><th>'.$this->l('Your current prestashop version').'</th><td>'._PS_VERSION_.'</td></tr>';

		$channel = $this->getConfig('channel');
		$this->_html .= '<tr><th>'.sprintf($this->l('Latest official version for channel %1$s'), $channel).'</th>';
		if (!in_array($channel, array('archive', 'directory')))
		{
			if (!empty($this->upgrader->version_num))
				$this->_html .= '<td><b>'.$this->upgrader->version_name.'</b> '.'('. $this->upgrader->version_num.')</td>';
		}
		else
			$this->_html .= '<td>'.$this->l('N/A').'</td>';

		$this->_html .= '</tr></table>
		</div>';

		/////////////////////////////////////////////////////////////////////////////////////////////////////////
		// decide to display "Start Upgrade" or not
		if ($this->configOk())
		{
			if (version_compare(_PS_VERSION_, $this->upgrader->version_num, '<'))
			{
				$show_big_button_new_version = false;
				$this->_html .= '<p class="clear"><a href="" id="upgradeNow" class="button-autoupgrade upgradestep">'.$this->l('Upgrade PrestaShop now !').'</a></p>';

				// smarty2 uses is a warning only, and will be displayed only if current version is 1.3 or 1.4 and target is <1.5;
				$use_smarty3 = !(Configuration::get('PS_FORCE_SMARTY_2') === '1' || Configuration::get('PS_FORCE_SMARTY_2') === false);
				if ($use_smarty3)
				{
					$srcShopStatus = '../img/admin/enabled.gif';
					$label = $this->l('You use Smarty 3');
				}
				else
				{
					$srcShopStatus = '../img/admin/warning.gif';
					$label = $this->l('Smarty 2 is deprecated in 1.4 and removed maintained in 1.5. You may need to upgrade your current theme or use a new one.');
				}

				// if current version is 1.4, we propose to edit now the configuration
				if (version_compare(_PS_VERSION_, '1.4.0.0', '<='))
				{
					if (method_exists('Tools','getAdminTokenLite'))
						$token_preferences = Tools14::getAdminTokenLite('AdminPreferences');
					else
						$token_preferences = Tools14::getAdminTokenLite('AdminPreferences');
					$this->_html .= '<div class="clear">&nbsp;</div><b>'.$this->l('Smarty 3 Usage:').'</b> <img src="'.$srcShopStatus.'" />'.$label;
					if (version_compare(_PS_VERSION_, '1.4.0.0', '>') && version_compare(_PS_VERSION_, '1.5.0.0', '<'))
						$this->_html .= '<div class="clear">&nbsp;</div>
							<a href="index.php?tab=AdminPreferences&token='.$token_preferences.'#PS_FORCE_SMARTY_2" class="button">'
							.$this->l('Edit your Smarty configuration').'</a>';
					$this->_html .= '<div class="clear">&nbsp;</div>';
				}
				if (!in_array($channel, array('archive', 'directory')))
				{
					if ($this->getConfig('channel') == 'private')
						$this->upgrader->link = $this->getConfig('private_release_link');

					$this->_html .= '<small><a href="'.$this->upgrader->link.'">'.sprintf($this->l('PrestaShop will be downloaded from %s'), $this->upgrader->link).'</a></small><br/>';
					$this->_html .= '<div class="clear">&nbsp;</div>';
					$this->_html .= '<small><a href="'.$this->upgrader->changelog.'" target="_blank" >'.$this->l('open changelog in a new window').'</a></small>';
					$this->_html .= '<div class="clear">&nbsp;</div>';
				}
				else
					$this->_html .= sprintf($this->l('No file will be downloaded (channel %s is used)'), $channel);

				// if skipActions property is used, we will handle that in the display :)
				if (count(AdminSelfUpgrade::$skipAction) > 0)
				{
					$this->_html .= '<div id="skipAction-list" class="warn" style="display:block;font-weight:normal">
						<img src="../img/admin/warning.gif"/>'
						.$this->l('The following action are automatically replaced')
						.'<ul>';
					foreach(AdminSelfUpgrade::$skipAction as $k => $v)
						$this->_html .= '<li>'
						.sprintf($this->l('%1$s will be replaced by %2$s'), '<b>'.$k.'</b>', '<b>'.$v.'</b>').'</li>';
					$this->_html .= '</ul><p>'.$this->l('To change this behavior, you need to manually edit your php files').'</p>
						</div>';
				}
			}
			else
				$show_big_button_new_version = true;
		}
		else
			$show_big_button_new_version = true;

		if ($show_big_button_new_version)
		{
			$this->_html .= 
				'<div class="clear"></div>
				<a class="button button-autoupgrade" 
					href="index.php?tab=AdminSelfUpgrade&token='.Tools14::getAdminToken('AdminSelfUpgrade'.(int)Tab::getIdFromClassName('AdminSelfUpgrade').(int)$cookie->id_employee)
				.'&refreshCurrentVersion=1">'.$this->l('Check if a new version is available').'</a>';
			
			$this->_html .= '<div><span style="font-style: italic; font-size: 11px;">'.sprintf($this->l('Last check: %s'), Configuration::get('PS_LAST_VERSION_CHECK') ? date('Y-m-d H:i:s', Configuration::get('PS_LAST_VERSION_CHECK')) : $this->l('never')).'</span></div>';
		}
		else
		{
			$this->_html .= '<div class="clear"></div><a class="button button-autoupgrade" href="index.php?tab=AdminSelfUpgrade&token='
				.Tools14::getAdminToken('AdminSelfUpgrade'
					.(int)Tab::getIdFromClassName('AdminSelfUpgrade')
					.(int)$cookie->id_employee)
				.'&refreshCurrentVersion=1">'.$this->l('refresh the page').'</a>';
			$this->_html .= '<div>
				<span>'.sprintf($this->l('last datetime check : %s '), date('Y-m-d H:i:s',Configuration::get('PS_LAST_VERSION_CHECK')))
				.'</span></div>';
		}

		$this->_html .= $this->getBlockConfigurationAdvanced();
		$this->_html .= '</fieldset>';

		if (defined('_PS_MODE_DEV_') AND _PS_MODE_DEV_ AND $this->manualMode)
			$this->_html .= $this->displayDevTools();


		// information to keep will be in #infoStep
		// temporary infoUpdate will be in #tmpInformation
	}

	public function display()
	{
		$this->_html .= '<script type="text/javascript">var jQueryVersionPS = parseFloat("."+$().jquery.replace(/\./g, ""));</script>
		<script type="text/javascript" src="'.__PS_BASE_URI__.'modules/autoupgrade/js/jquery-1.6.2.min.js"></script>
		<script type="text/javascript">if (jQueryVersionPS >= 0.162) jq162 = jQuery.noConflict(true);</script>';
		
		/* PrestaShop demo mode */
		if (defined('_PS_MODE_DEMO_') && _PS_MODE_DEMO_)
		{
			echo '<div class="error">'.$this->l('This functionnality has been disabled.').'</div>';
			return;
		}

		if (!file_exists($this->autoupgradePath.DIRECTORY_SEPARATOR.'ajax-upgradetab.php'))
		{
			echo '<div class="error">'.'<img src="../img/admin/warning.gif" /> [TECHNICAL ERROR] '.$this->l('ajax-upgradetab.php is missing. please reinstall or reset the module').'</div>';
			return false;
		}
		/* PrestaShop demo mode*/

		// in order to not use Tools class
		$upgrader = new Upgrader();
		preg_match('#([0-9]+\.[0-9]+)(?:\.[0-9]+){1,2}#', _PS_VERSION_, $matches);
		$upgrader->branch = $matches[1];
		$channel = $this->getConfig('channel');
		switch ($channel)
		{
			case 'archive':
				$upgrader->channel = 'archive';
				$upgrader->version_num = $this->getConfig('archive.version_num');
				break;
			case 'directory':
				$upgrader->channel = 'directory';
				$upgrader->version_num = $this->getConfig('directory.version_num');
				break;
			default:
				$upgrader->channel = $channel;
				if (isset($_GET['refreshCurrentVersion']))
				{
					// delete the potential xml files we saved in config/xml (from last release and from current)
					$upgrader->clearXmlMd5File(_PS_VERSION_);
					$upgrader->clearXmlMd5File($upgrader->version_num);
					if ($this->getConfig('channel') == 'private' && !$this->getConfig('private_allow_major'))
						$upgrader->checkPSVersion(true, array('private', 'minor'));
					else
						$upgrader->checkPSVersion(true, array('minor'));
					
					Tools14::redirectAdmin($this->currentIndex.'&conf=5&token='.Tools14::getValue('token'));
				}
				else
				{
					if ($this->getConfig('channel') == 'private' && !$this->getConfig('private_allow_major'))
						$upgrader->checkPSVersion(false, array('private', 'minor'));
					else
						$upgrader->checkPSVersion(false, array('minor'));
				}
		}


		$this->upgrader = $upgrader;

		$this->_html .= '<link type="text/css" rel="stylesheet" href="'.__PS_BASE_URI__.'modules/autoupgrade/css/styles.css" />';

		$this->_html .= '
		<h1>'.$this->l('1-click Upgrade').'</h1>
		<fieldset id="informationBlock" class="information" style="float: left; width: 30%;">
			<legend>'.$this->l('Welcome!').'</legend>
			<p>'.$this->l('With the PrestaShop 1-click upgrade module, upgrading your store to the latest version available has never been easier!').'<br /><br />
			<img src="../img/admin/warning.gif" alt=""/><strong>'.$this->l('This module is still in a "beta" version.').'</strong><br /><br /><span style="color: #CC0000; font-weight: bold;">'.
			$this->l('Please always perform a full manual backup of your files and database before starting any upgrade.').'</span><br />'.
			$this->l('Double-check the integrity of your backup and that you can easily manually roll-back if necessary.').'<br />'.
			$this->l('If you do not know how to proceed, ask your hosting provider.').'</p>			
		</fieldset>';
		
		/* Make sure the user has configured the upgrade options, or set default values */
		$configuration_keys = array('PS_AUTOUP_UPDATE_DEFAULT_THEME' => 1, 'PS_AUTOUP_KEEP_MAILS' => 0, 'PS_AUTOUP_CUSTOM_MOD_DESACT' => 1,
		'PS_AUTOUP_MANUAL_MODE' => 0, 'PS_AUTOUP_PERFORMANCE' => 1, 'PS_DISPLAY_ERRORS' => 0);
		foreach ($configuration_keys as $k => $default_value)
			if (Configuration::get($k) == '')
				Configuration::updateValue($k, $default_value);

		/* Checks/requirements and "Upgrade PrestaShop now" blocks */
		$this->_displayCurrentConfiguration();
		$this->_html .= '<div class="clear"></div>';
		$this->_displayBlockUpgradeButton();
		
		$this->_displayComparisonBlock();
		$this->_displayBlockActivityLog();

		$this->_displayRollbackForm();

		$this->_html .= '<br/>';
		$this->_html .= '<form action="'.$this->currentIndex.'&customSubmitAutoUpgrade=1&token='.$this->token.'"
			method="post" enctype="multipart/form-data">';
		$this->_displayForm('backupOptions',$this->_fieldsBackupOptions,'<a href="#" name="backup-options" id="backup-options">'.$this->l('Backup Options').'</a>', '','database_gear');
		$this->_displayForm('upgradeOptions',$this->_fieldsUpgradeOptions,'<a href="#" name="upgrade-options" id="upgrade-options">'.$this->l('Upgrade Options').'</a>', '','prefs');
		$this->_html .= '</form>';

		$this->_html .= '<script type="text/javascript" src="'.__PS_BASE_URI__.'modules/autoupgrade/js/jquery.xml2json.js"></script>';
		$this->_html .= '<script type="text/javascript">'.$this->_getJsInit().'</script>';
		echo $this->_html;
	}

	private function _getJsInit()
	{
		global $cookie;	
		$js = '';

		if (method_exists('Tools','getAdminTokenLite'))
			$token_preferences = Tools14::getAdminTokenLite('AdminPreferences');
		else
			$token_preferences = Tools14::getAdminTokenLite('AdminPreferences');

		$js .= '
function ucFirst(str) {
	if (str.length > 0) {
		return str[0].toUpperCase() + str.substring(1);
	}
	else {
		return str;
	}
}

function cleanInfo(){
	$("#infoStep").html("reset<br/>");
}

function updateInfoStep(msg){
	if (msg)
	{
		$("#infoStep").append(msg+"<div class=\"clear\"></div>");
		$("#infoStep").prop({ scrollTop: $("#infoStep").prop("scrollHeight")},1);
	}
}

function addError(arrError){
	if (typeof(arrError) != "undefined" && arrError.length)
	{
		$("#errorDuringUpgrade").show();
		for(i=0;i<arrError.length;i++)
			$("#infoError").append(arrError[i]+"<div class=\"clear\"></div>");
		// Note : jquery 1.6 make uses of prop() instead of attr()
		$("#infoError").prop({ scrollTop: $("#infoError").prop("scrollHeight")},1);
	}
}

function addQuickInfo(arrQuickInfo){
	if (arrQuickInfo)
	{
		$("#quickInfo").show();
		for(i=0;i<arrQuickInfo.length;i++)
			$("#quickInfo").append(arrQuickInfo[i]+"<div class=\"clear\"></div>");
		// Note : jquery 1.6 make uses of prop() instead of attr()
		$("#quickInfo").prop({ scrollTop: $("#quickInfo").prop("scrollHeight")},1);
	}
}';

		if ($this->manualMode)
			$js .= 'var manualMode = true;'."\n";
		else
			$js .= 'var manualMode = false;'."\n";

		// relative admin dir
		$admin_dir = trim(str_replace($this->prodRootDir, '', $this->adminDir), DIRECTORY_SEPARATOR);
		// _PS_MODE_DEV_ will be available in js
		if (defined('_PS_MODE_DEV_') AND _PS_MODE_DEV_)
			$js .= 'var _PS_MODE_DEV_ = true;'."\n";

		if ($this->getConfig('PS_AUTOUP_BACKUP'))
			$js .= 'var PS_AUTOUP_BACKUP = true;'."\n";

		$js .= $this->_getJsErrorMsgs();

		$js .= '
var firstTimeParams = '.$this->buildAjaxResult().';
firstTimeParams = firstTimeParams.nextParams;
firstTimeParams.firstTime = "1";

// js initialization : prepare upgrade and rollback buttons
$(document).ready(function(){

	$("select[name=channel]").change(function(e){
		$("select[name=channel]").find("option").each(function()
		{
			if ($(this).is(":selected"))
				$("#for-"+$(this).attr("id")).show();
			else
				$("#for-"+$(this).attr("id")).hide();
	});

		refreshChannelInfos();
	});

	function refreshChannelInfos()
	{
		val = $("select[name=channel]").find("option:selected").val();
		$.ajax({
			type:"POST",
			url : "'. __PS_BASE_URI__ . $admin_dir.'/autoupgrade/ajax-upgradetab.php",
			async: true,
			data : {
				dir:"'.$admin_dir.'",
				token : "'.$this->token.'",
				tab : "AdminSelfUpgrade",
				action : "getChannelInfo",
				ajaxMode : "1",
				params : { channel : val}
			},
			success : function(res,textStatus,jqXHR)
			{
				if (isJsonString(res))
					res = $.parseJSON(res);
				else
					res = {nextParams:{status:"error"}};

				answer = res.nextParams.result;
				if (typeof(answer) != "undefined")
				$("#channel-infos").replaceWith(answer.div);
				if (typeof(answer) != "undefined" && answer.available)
				{
					$("#channel-infos .all-infos").show();
				}
				else if (typeof(answer) != "undefined")
				{
					$("#channel-infos").html(answer.div);
					$("#channel-infos .all-infos").hide();
				}
			},
			error: function(res, textStatus, jqXHR)
			{
				if (textStatus == "timeout" && action == "download")
				{
					updateInfoStep("'.$this->l('Your server cannot download the file. Please upload it first by ftp in your admin/autoupgrade directory', 'AdminSelfUpgrade', true).'");
				}
				else
				{
					// technical error : no translation needed
					$("#checkPrestaShopFilesVersion").html("<img src=\"../img/admin/warning.gif\" /> Error Unable to check md5 files");
				}
			}
		})
	}

	$(document).ready(function(){
		$("div[id|=for]").hide();
		$("select[name=channel]").change();
	});

	// the following prevents to leave the page at the innappropriate time
	$.xhrPool = [];
	$.xhrPool.abortAll = function()
	{
		$.each(this, function(jqXHR)
		{
			if (jqXHR && (jqXHR.readystate != 4))
			{
				jqXHR.abort();
			}
		});
	}
	$(".upgradestep").click(function(e)
	{
		e.preventDefault();
		// $.scrollTo("#options")
	});

	// set timeout to 20 minutes (before aborting an ajax request)
	$.ajaxSetup({timeout:1200000});

	// prepare available button here, without params ?
	prepareNextButton("#upgradeNow",firstTimeParams);

	/**
	 * reset rollbackParams js array (used to init rollback button)
	 */
	$("select[name=restoreName]").change(function(){
		$(this).next().remove();
		// show delete button if the value is not 0
		if($(this).val() != 0)
		{
			$(this).after("<a class=\"button confirmBeforeDelete\" href=\"index.php?tab=AdminSelfUpgrade&token='
			.Tools14::getAdminToken('AdminSelfUpgrade'.(int)(Tab::getIdFromClassName('AdminSelfUpgrade')).(int)$cookie->id_employee)
			.'&amp;deletebackup&amp;name="+$(this).val()+"\">'
			.'<img src=\"../img/admin/disabled.gif\" />'.$this->l('Delete').'</a>");
			$(this).next().click(function(e){
				if (!confirm("'.$this->l('Are you sure you want to delete this backup ?').'"))
					e.preventDefault();
			});
		}

		if ($("select[name=restoreName]").val() != 0)
		{
			$("#rollback").removeAttr("disabled");
			rollbackParams = jQuery.extend(true, {}, firstTimeParams);

			delete rollbackParams.backupName;
			delete rollbackParams.backupFilesFilename;
			delete rollbackParams.backupDbFilename;
			delete rollbackParams.restoreFilesFilename;
			delete rollbackParams.restoreDbFilenames;

			// init new name to backup
			rollbackParams.restoreName = $("select[name=restoreName]").val();
			prepareNextButton("#rollback", rollbackParams);
			// Note : theses buttons have been removed.
			// they will be available in a future release (when DEV_MODE and MANUAL_MODE enabled)
			// prepareNextButton("#restoreDb", rollbackParams);
			// prepareNextButton("#restoreFiles", rollbackParams);
		}
		else
			$("#rollback").attr("disabled", "disabled");
	});

});

function showConfigResult(msg, type){
	if (type == null)
		type = "conf";
	$("#configResult").html("<div class=\""+type+"\">"+msg+"</div>").show();
	if (type == "conf")
	{
		$("#configResult").delay(3000).fadeOut("slow", function() {
			location.reload();
		});
	}
}

// reuse previousParams, and handle xml returns to calculate next step
// (and the correct next param array)
// a case has to be defined for each requests that returns xml


function afterUpdateConfig(res)
{
	params = res.nextParams
	config = params.config
	oldChannel = $("select[name=channel] option.current");
	if (config.channel != oldChannel.val())
	{
		newChannel = $("select[name=channel] option[value="+config.channel+"]");
		oldChannel.removeClass("current");
		oldChannel.html(oldChannel.html().substr(2));
		newChannel.addClass("current");
		newChannel.html("* "+newChannel.html());
	}
	showConfigResult(res.next_desc);
	$("#upgradeNow").unbind();
	$("#upgradeNow").replaceWith("<a class=\"button-autoupgrade\" href=\"'.$this->currentIndex.'&token='.$this->token.'\" >'.$this->l('Click to refresh the page and use the new configuration', 'AdminSelfUpgrade', true).'</a>");
}
function startProcess(type){
	if (type == "upgrade")
		msg = "'.$this->l('an upgrade is currently in progress ... Click "OK" to abort.', 'AdminTab', true, false).'";
	else
		msg = "'.$this->l('a restoration is currently in progress ... Click "OK" to abort.', true, false).'";


	// hide useless divs, show activity log
	$("#informationBlock,#comparisonBlock,#currentConfigurationBlock,#backupOptionsBlock,#upgradeOptionsBlock,#upgradeButtonBlock").slideUp("fast");
	$("#activityLogBlock").fadeIn("slow");

	$(window).bind("beforeunload", function(e)
	{
		if (confirm("'.$this->l('an update is currently in progress ... Click "OK" to abort.', 'AdminTab', true, false).'"))
		{
			$.xhrPool.abortAll();
			$(window).unbind("beforeunload");
			$("#rollback").click();
			return false;
		}
		else
		{
			if (type == "upgrade")
			{
				e.returnValue = false;
				e.cancelBubble = true;
				if (e.stopPropagation)
				{
					e.stopPropagation();
				}
				if (e.preventDefault)
				{
					e.preventDefault();
				}
			}
		}
	});
}

function afterUpgradeNow(res)
{
	startProcess("upgrade");
	$("#upgradeNow").unbind();
	$("#upgradeNow").replaceWith("<span id=\"upgradeNow\" class=\"button-autoupgrade\">'.$this->l('Upgrading PrestaShop', 'AdminSelfUpgrade', true).' ...</span>");
}

function afterUpgradeComplete(res)
{
	params = res.nextParams
	$("#pleaseWait").hide();
	if (params.warning_exists == "false")
	{
		$("#upgradeResultCheck")
			.addClass("conf")
			.removeClass("fail")
			.html("<p>'.$this->l('Upgrade complete').'</p>")
			.show();
		$("#infoStep").html("<h3>'.$this->l('Upgrade Complete !', 'AdminSelfUpgrade', true).'</h3>");
	}
	else
	{
		params = res.nextParams
		$("#pleaseWait").hide();
		$("#upgradeResultCheck")
			.addClass("fail")
			.removeClass("ok")
			.html("<p>'.$this->l('Upgrade complete, but warnings has been found.').'</p>")
			.show("slow");
		$("#infoStep").html("<h3>'.$this->l('Upgrade complete, but warnings has been found.', 'AdminSelfUpgrade', true).'</h3>");
	}

	todo_list = [
		"'.$this->l('Cookies have changed, you will need to log in again once you refreshed the page', 'AdminSelfUpgrade', true).'",
		"'.$this->l('Javascript and CSS files have changed, please clear your browser cache with CTRL-F5', 'AdminSelfUpgrade', true).'",
		"'.$this->l('Please check that your front office theme is functionnal (try to create an account, place an order...)', 'AdminSelfUpgrade', true).'",
		"'.$this->l('Product images does not appear in the front office? Try regenerating the thumbnails in Preferences > Images', 'AdminSelfUpgrade', true).'",
		"'.$this->l('Do not forget to reactivate your shop once you have checked everything!', 'AdminSelfUpgrade', true).'",
	];
		
	todo_ul = "<ul>";
	$("#upgradeResultToDoList")
		.addClass("hint clear")
		.html("<h3>'.$this->l('ToDo list:').'</h3>")
	for(var i in todo_list)
	{
		todo_ul += "<li>"+todo_list[i]+"</li>";
	}
	todo_ul += "</ul>";
	$("#upgradeResultToDoList").append(todo_ul)
	$("#upgradeResultToDoList").show();
	
	$(window).unbind("beforeunload");
}

function afterError(res)
{
	params = res.nextParams;
	if (params.next == "")
		$(window).unbind("beforeunload");
	$("#pleaseWait").hide();

	addQuickInfo(["unbind :) "]);
}

function afterRollback(res)
{
	startProcess("rollback");
}

function afterRollbackComplete(res)
{
	params = res.nextParams
	$("#pleaseWait").hide();
	$("#upgradeResultCheck")
		.addClass("ok")
		.removeClass("fail")
		.html("<p>'.$this->l('Restoration complete.').'</p>")
		.show("slow");
	updateInfoStep("<h3>'.$this->l('Restoration complete.').'</h3>");
	$(window).unbind();
}


function afterRestoreDb(params)
{
	// $("#restoreBackupContainer").hide();
}

function afterRestoreFiles(params)
{
	// $("#restoreFilesContainer").hide();
}

function afterBackupFiles(res)
{
	params = res.nextParams;
	// if (params.stepDone)
}

/**
 * afterBackupDb display the button
 *
 */
function afterBackupDb(res)
{
	params = res.nextParams;
	var PS_AUTOUP_BACKUP;
	if (res.stepDone && typeof(PS_AUTOUP_BACKUP) != "undefined" && PS_AUTOUP_BACKUP == "true")
	{
		$("#restoreBackupContainer").show();
		$("select[name=restoreName]").children("options").removeAttr("selected");
		$("select[name=restoreName]")
			.append("<option selected=\"selected\" value=\""+params.backupName+"\">"+params.backupName+"</option>")
		$("select[name=restoreName]").change();
	}
}


function call_function(func){
	this[func].apply(this, Array.prototype.slice.call(arguments, 1));
}

function doAjaxRequest(action, nextParams){
	var _PS_MODE_DEV_;
	if (typeof(_PS_MODE_DEV_) != "undefined" && _PS_MODE_DEV_ == "true")
		addQuickInfo(["[DEV] ajax request : "+action]);
	$("#pleaseWait").show();
	req = $.ajax({
		type:"POST",
		url : "'. __PS_BASE_URI__.$admin_dir.'/autoupgrade/ajax-upgradetab.php'.'",
		async: true,
		data : {
			dir:"'.$admin_dir.'",
			ajaxMode : "1",
			token : "'.$this->token.'",
			tab : "AdminSelfUpgrade",
			action : action,
			params : nextParams
		},
		beforeSend: function(jqXHR)
		{
			$.xhrPool.push(jqXHR);
		},
		complete: function(jqXHR)
		{
			// just remove the item to the "abort list"
			$.xhrPool.pop();
			// $(window).unbind("beforeunload");
		},
		success : function(res, textStatus, jqXHR)
		{
			$("#pleaseWait").hide();
			try{
				res = $.parseJSON(res);
			}
			catch(e){
				res = {status : "error", nextParams:nextParams};
				alert("'.$this->l('Javascript error (parseJSON) detected for action ', __CLASS__, true, false).'\""+action+"\".'
			.$this->l('Starting restoration...', __CLASS__, true, false).'");
			}
			addQuickInfo(res.nextQuickInfo);
			addError(res.nextErrors);
			updateInfoStep(res.next_desc);
			currentParams = res.nextParams;
			if (res.status == "ok")
			{
				$("#"+action).addClass("done");
				if (res.stepDone)
					$("#"+action).addClass("stepok");
				// if a function "after[action name]" exists, it should be called now.
				// This is used for enabling restore buttons for example
				funcName = "after"+ucFirst(action);
				if (typeof funcName == "string" && eval("typeof " + funcName) == "function")
					call_function(funcName, res);

				handleSuccess(res, action);
			}
			else
			{
				// display progression
				$("#"+action).addClass("done");
				$("#"+action).addClass("steperror");
				if (action != "rollback"
					&& action != "rollbackComplete"
					&& action != "restoreFiles"
					&& action != "restoreDb"
					&& action != "rollback"
					&& action != "noRollbackFound"
				)
					handleError(res, action);
				else
					alert("'.$this->l('Error detected during', __CLASS__, true, false).' ["+action+"].");
			}
		},
		error: function(jqXHR, textStatus, errorThrown)
		{
			$("#pleaseWait").hide();
			if (textStatus == "timeout")
			{
				if (action == "download")
					updateInfoStep("'.addslashes($this->l('Your server cannot download the file. Please upload it first by ftp in your admin/autoupgrade directory')).'");
				else
					updateInfoStep("[Server Error] Timeout:'.addslashes($this->l('The request exceeded the max_time_limit. Please change your server configuration.')).'");
			}
			else
				updateInfoStep("[Ajax / Server Error for action " + action + "] textStatus: \"" + textStatus + " \" errorThrown:\"" + errorThrown + " \" jqXHR: \" " + jqXHR.responseText + "\"");
		}
	});
	return req;
};

/**
 * prepareNextButton make the button button_selector available, and update the nextParams values
 *
 * @param button_selector $button_selector
 * @param nextParams $nextParams
 * @return void
 */
function prepareNextButton(button_selector, nextParams)
{
	$(button_selector).unbind();
	$(button_selector).click(function(e){
		e.preventDefault();
		$("#currentlyProcessing").show();';
		$js .= '
	action = button_selector.substr(1);
	res = doAjaxRequest(action, nextParams);
	});
}

/**
 * handleSuccess
 * res = {error:, next:, next_desc:, nextParams:, nextQuickInfo:,status:"ok"}
 * @param res $res
 * @return void
 */
function handleSuccess(res, action)
{
	if (res.next != "")
	{

		$("#"+res.next).addClass("nextStep");
		if (manualMode)
		{
			prepareNextButton("#"+res.next,res.nextParams);
			alert("'.sprintf($this->l('Manually go to %s button', __CLASS__, true, false), '"+res.next+"').'");
		}
		else
		{
			// if next is rollback, prepare nextParams with rollbackDbFilename and rollbackFilesFilename
			if ( res.next == "rollback")
			{
				res.nextParams.restoreName = ""
			}
			doAjaxRequest(res.next, res.nextParams);
			// 2) remove all step link (or show them only in dev mode)
			// 3) when steps link displayed, they should change color when passed if they are visible
		}
	}
	else
	{
		// Way To Go, end of upgrade process
		addQuickInfo(["End of process"]);
	}
}

// res = {nextParams, next_desc}
function handleError(res, action)
{
	// display error message in the main process thing
	// In case the rollback button has been deactivated, just re-enable it
	$("#rollback").removeAttr("disabled");
	// auto rollback only if current action is upgradeFiles or upgradeDb
	if (action == "upgradeFiles" || action == "upgradeDb" || action == "upgradeModules" )
	{
		$(".button-autoupgrade").html("'.$this->l('Operation cancelled. checking for restoration ...').'");
		res.nextParams.restoreName = res.nextParams.backupName;
		if (confirm("'.$this->l('Do you want to restore').' " + "'.$this->backupName.'" + " ?"))
			doAjaxRequest("rollback",res.nextParams);
	}
	else
	{
		$(".button-autoupgrade").html("'.$this->l('Operation cancelled. An error happens.').'");
		$(window).unbind();
	}
}';
// ajax to check md5 files
		$js .= 'function addModifiedFileList(title, fileList, css_class, container)
{
	subList = $("<ul class=\"changedFileList "+css_class+"\"></ul>");

	$(fileList).each(function(k,v){
		$(subList).append("<li>"+v+"</li>");
	});
	$(container).append("<h3><a class=\"toggleSublist\" href=\"#\" >"+title+"</a> (" + fileList.length + ")</h3>");
	$(container).append(subList);
	$(container).append("<br/>");

}';
		if(!file_exists($this->autoupgradePath.DIRECTORY_SEPARATOR.'ajax-upgradetab.php'))
			$js .= '$(document).ready(function(){
			$("#checkPrestaShopFilesVersion").html("<img src=\"../img/admin/warning.gif\" /> [TECHNICAL ERROR] ajax-upgradetab.php '.$this->l('is missing. please reinstall the module').'");
			})';
		else
			$js .= '
			function isJsonString(str) {
				try {
						typeof(str) != "undefined" && JSON.parse(str);
				} catch (e) {
						return false;
				}
				return true;
		}

$(document).ready(function(){
	$.ajax({
			type:"POST",
			url : "'. __PS_BASE_URI__ . $admin_dir.'/autoupgrade/ajax-upgradetab.php",
			async: true,
			data : {
				dir:"'.$admin_dir.'",
				token : "'.$this->token.'",
				tab : "'.get_class($this).'",
				action : "checkFilesVersion",
				ajaxMode : "1",
				params : {}
			},
			success : function(res,textStatus,jqXHR)
			{
				if (isJsonString(res))
					res = $.parseJSON(res);
				else
				{
					res = {nextParams:{status:"error"}};
				}
					answer = res.nextParams;
					$("#checkPrestaShopFilesVersion").html("<span> "+answer.msg+" </span> ");
					if ((answer.status == "error") || (typeof(answer.result) == "undefined"))
						$("#checkPrestaShopFilesVersion").prepend("<img src=\"../img/admin/warning.gif\" /> ");
					else
					{
						$("#checkPrestaShopFilesVersion").prepend("<img src=\"../img/admin/warning.gif\" /> ");
						$("#checkPrestaShopFilesVersion").append("<a id=\"toggleChangedList\" class=\"button\" href=\"\">'.$this->l('See or hide the list').'</a><br/>");
						$("#checkPrestaShopFilesVersion").append("<div id=\"changedList\" style=\"display:none \"><br/>");
						if(answer.result.core.length)
							addModifiedFileList("'.$this->l('Core file(s)').'", answer.result.core, "changedImportant", "#changedList");
						if(answer.result.mail.length)
							addModifiedFileList("'.$this->l('Mail file(s)').'", answer.result.mail, "changedNotice", "#changedList");
						if(answer.result.translation.length)
							addModifiedFileList("'.$this->l('Translation file(s)').'", answer.result.translation, "changedNotice", "#changedList");

						$("#toggleChangedList").bind("click",function(e){e.preventDefault();$("#changedList").toggle();});
						$(".toggleSublist").die().live("click",function(e){e.preventDefault();$(this).parent().next().toggle();});
					}
			}
			,
			error: function(res, textStatus, jqXHR)
			{
				if (textStatus == "timeout" && action == "download")
				{
					updateInfoStep("'.$this->l('Your server cannot download the file. Please upload it first by ftp in your admin/autoupgrade directory').'");
				}
				else
				{
					// technical error : no translation needed
					$("#checkPrestaShopFilesVersion").html("<img src=\"../img/admin/warning.gif\" /> Error: Unable to check md5 files");
				}
			}
		})
	$.ajax({
			type:"POST",
			url : "'. __PS_BASE_URI__ . $admin_dir.'/autoupgrade/ajax-upgradetab.php",
			async: true,
			data : {
				dir:"'.$admin_dir.'",
				token : "'.$this->token.'",
				tab : "'.get_class($this).'",
				action : "compareReleases",
				ajaxMode : "1",
				params : {}
			},
			success : function(res,textStatus,jqXHR)
			{
				if (isJsonString(res))
					res = $.parseJSON(res);
				else
				{
					res = {nextParams:{status:"error"}};
				}
				answer = res.nextParams;
				$("#checkPrestaShopModifiedFiles").html("<span> "+answer.msg+" </span> ");
				if ((answer.status == "error") || (typeof(answer.result) == "undefined"))
					$("#checkPrestaShopModifiedFiles").prepend("<img src=\"../img/admin/warning.gif\" /> ");
				else
				{
					$("#checkPrestaShopModifiedFiles").prepend("<img src=\"../img/admin/warning.gif\" /> ");
					$("#checkPrestaShopModifiedFiles").append("<a id=\"toggleDiffList\" class=\"button\" href=\"\">'.$this->l('See or hide the list').'</a><br/>");
					$("#checkPrestaShopModifiedFiles").append("<div id=\"diffList\" style=\"display:none \"><br/>");
						if(answer.result.deleted.length)
							addModifiedFileList("'.$this->l('Theses files will be deleted').'", answer.result.deleted, "diffImportant", "#diffList");
						if(answer.result.modified.length)
							addModifiedFileList("'.$this->l('Theses files will be modified').'", answer.result.modified, "diffImportant", "#diffList");

					$("#toggleDiffList").bind("click",function(e){e.preventDefault();$("#diffList").toggle();});
					$(".toggleSublist").die().live("click",function(e){
						e.preventDefault();
						// this=a, parent=h3, next=ul
						$(this).parent().next().toggle();
					});
				}
			},
			error: function(res, textStatus, jqXHR)
			{
				if (textStatus == "timeout" && action == "download")
				{
					updateInfoStep("'.$this->l('Your server cannot download the file. Please upload it first by ftp in your admin/autoupgrade directory').'");
				}
				else
				{
					// technical error : no translation needed
					$("#checkPrestaShopFilesVersion").html("<img src=\"../img/admin/warning.gif\" /> Error: Unable to check md5 files");
				}
			}
		})
	});';

		// advanced/normal mode
		$js .= '
	$("input[name=btn_adv]").click(function(e)
		{
			if ($("#advanced:visible").length)
				switch_to_normal();
			else
				switch_to_advanced();
		});

		function switch_to_advanced(){
			$("input[name=btn_adv]").val("'.$this->l('Less options', 'AdminTab', true, false).'");
			$("#advanced").show();
		}

		function switch_to_normal(){
			$("input[name=btn_adv]").val("'.$this->l('More options (Expert mode)', 'AdminTab', true, false).'");
			$("#advanced").hide();
		}

		$(document).ready(function(){
			$("#advanced").hide();
			$("#normal").show();
		});
	';
		$js .= '
$(document).ready(function()
{
	$("input[name|=submitConf]").bind("click", function(e){
		params = {};
		newChannel = $("select[name=channel] option:selected").val();
		oldChannel = $("select[name=channel] option.current").val();
		oldChannel = "";
		if (oldChannel != newChannel)
		{
			if( newChannel == "major"
				|| newChannel == "minor"
				|| newChannel == "rc"
				|| newChannel == "beta"
				|| newChannel == "alpha" )
				params.channel = newChannel;

			if(newChannel == "private")
			{
				if (($("input[name=private_release_link]").val() == "") || ($("input[name=private_release_md5]").val() == ""))
				{
					showConfigResult("'.$this->l('Link and MD5 hash cannot be empty').'", "error");
					return false;
				}
				params.channel = "private";
				params.private_release_link = $("input[name=private_release_link]").val();
				params.private_release_md5 = $("input[name=private_release_md5]").val();
				if ($("input[name=private_allow_major]").is(":checked"))
					params.private_allow_major = 1;
				else
					params.private_allow_major = 0;
			}
			if(newChannel == "archive")
			{
				archive_prestashop = $("select[name=archive_prestashop] option:selected").val();
				archive_num = $("input[name=archive_num]").val();
				if (archive_num == "")
				{
					showConfigResult("'.$this->l('You need to enter the version number associated to the archive.').'", "error");
					return false;
				}
				if (archive_prestashop == "")
				{
					showConfigResult("'.$this->l('No archive has been selected.').'", "error");
					return false;
				}
				params.channel = "archive";
				params.archive_prestashop = archive_prestashop;
				params.archive_num = archive_num;
			}
			if(newChannel == "directory")
			{
				params.channel = "directory";
				params.directory_prestashop = $("select[name=directory_prestashop] option:selected").val();
				directory_num = $("input[name=directory_num]").val();
				if (directory_num == "")
				{
					showConfigResult("'.$this->l('You need to enter the version number associated to the directory.').'", "error");
					return false;
				}
				params.directory_num = $("input[name=directory_num]").val();
			}
		}
		// note: skipBackup is currently not used
		if ($(this).attr("name") == "submitConf-skipBackup")
		{
			skipBackup = $("input[name=submitConf-skipBackup]:checked").length;
			if (skipBackup == 0 || confirm("'.$this->l('please confirm skip backup').'"))
				params.skip_backup = $("input[name=submitConf-skipBackup]:checked").length;
			else
			{
				$("input[name=submitConf-skipBackup]:checked").removeAttr("checked");
				return false;
			}
		}

		// note: preserveFiles is currently not used
		if ($(this).attr("name") == "submitConf-preserveFiles")
		{
			preserveFiles = $("input[name=submitConf-preserveFiles]:checked").length;
			if (confirm("'.$this->l('please confirm preserve files options').'"))
				params.preserve_files = $("input[name=submitConf-preserveFiles]:checked").length;
			else
			{
				$("input[name=submitConf-skipBackup]:checked").removeAttr("checked");
				return false;
			}
		}
		res = doAjaxRequest("updateConfig", params);
	});
});
';
		return $js;
	}


	/**
	 * @desc extract a zip file to the given directory
	 * @return bool success
	 * we need a copy of it to be able to restore without keeping Tools and Autoload stuff
	 */
	private function ZipExtract($from_file, $to_dir)
	{
		if (!is_file($from_file))
		{
			$this->next = 'error';
			$this->nextQuickInfo[] = sprintf($this->l('%s is not a file'), $from_file);
			$this->nextErrors[] = sprintf($this->l('%s is not a file'), $from_file);
			return false;
		}

		if (!file_exists($to_dir))
			if (!@mkdir($to_dir))
			{
				$this->next = 'error';
				$this->nextQuickInfo[] = sprintf($this->l('unable to create directory %s'), $to_dir);
				$this->nextErrors[] = sprintf($this->l('unable to create directory %s'), $to_dir);
				return false;
			}
			else
				@chmod($to_dir, 0775);

		if (!self::$force_pclZip && class_exists('ZipArchive', false))
		{
			$this->nextQuickInfo[] = $this->l('using class ZipArchive ...');
			$zip = new ZipArchive();
			if (@$zip->open($from_file) === true)
			{
				$extract_result = true;
				// We extract file by file, it is very fast
				for ($i = 0; $i < $zip->numFiles; $i++)
				{
					echo ' ';
					flush();
					$extract_result &= $zip->extractTo($to_dir, array($zip->getNameIndex($i)));
				}

				if ($extract_result)
				{
					$this->nextQuickInfo[] = $this->l('backup extracted');
					return true;
				}
				else
				{
					$this->nextQuickInfo[] = sprintf($this->l('zip->extractTo() : unable to use %s as extract destination.'), $to_dir);
					$this->nextErrors[] = sprintf($this->l('zip->extractTo() : unable to use %s as extract destination.'), $to_dir);
					return false;
				}
			}
			else
			{
				$this->nextQuickInfo[] = sprintf($this->l('Unable to open zipFile %s'), $from_file);
				$this->nextErrors[] = sprintf($this->l('Unable to open zipFile %s'), $from_file);
				return false;
			}
		}
		else
		{
			if (!class_exists('PclZip', false))
				require_once(_PS_ROOT_DIR_.'/modules/autoupgrade/classes/pclzip.lib.php');

			$this->nextQuickInfo[] = $this->l('using class pclzip ...');

			$zip = new PclZip($from_file);

			if (($file_list = $zip->listContent()) == 0)
			{
				$this->next = 'error';
				$this->nextQuickInfo[] = '[ERROR] error on extract using pclzip : '.$zip->errorInfo(true);
				return false;
			}

			// PCL is very slow, so we need to extract files 500 by 500
			$i = 0;
			$j = 1;
			foreach ($file_list as $file)
			{
				if (!isset($indexes[$i]))
					$indexes[$i] = array();
				$indexes[$i][] = $file['index'];
				if ($j++ % 500 == 0)
					$i++;
			}

			// replace also modified files
			foreach ($indexes as $index)
				if (($extract_result = $zip->extract(PCLZIP_OPT_BY_INDEX, $index, PCLZIP_OPT_PATH, $to_dir, PCLZIP_OPT_REPLACE_NEWER)) == 0)
				{
					$this->next = 'error';
					$this->nextErrors[] = '[ERROR] error on extract using pclzip : '.$zip->errorInfo(true);
					return false;
				}
				else
				{
					foreach ($extract_result as $extractedFile)
					{
						echo ' ';
						flush();
						$file = str_replace($this->prodRootDir, '', $extractedFile['filename']);
						if ($extractedFile['status'] != 'ok')
						{
							$this->nextQuickInfo[] = sprintf('[ERROR] %s has not been unzipped', $file);
							$this->nextErrors[] = sprintf('[ERROR] %s has not been unzipped', $file);
							$this->next = 'error';
						}
						else
							$this->nextQuickInfo[] = sprintf('%1$s unzipped into %2$s', $file, str_replace(_PS_ROOT_DIR_, '', $to_dir.'/'));
					}
					if ($this->next === 'error')
						return false;
				}
			return true;
		}
	}

	private function _listArchivedFiles($zipfile)
	{
		if (file_exists($zipfile))
		{
			if (!self::$force_pclZip && class_exists('ZipArchive', false))
			{
				$this->nextQuickInfo[] = $this->l('using class ZipArchive ...');
				$files = array();
				$zip = new ZipArchive();
				$zip->open($zipfile);
				if ($zip){
					for ($i = 0; $i < $zip->numFiles; $i++)
						$files[] = $zip->getNameIndex($i);
					return $files;
				}
				else
				{
					$this->nextQuickInfo[] = '[ERROR] Unable to list archived files';
					return false;
				}
			}
			else
			{
				$this->nextQuickInfo[] = $this->l('using class pclzip ...');
				if (!class_exists('PclZip',false))
					require_once(dirname(__FILE__).'/classes/pclzip.lib.php');
				if ($zip = new PclZip($zipfile));
				return $zip->listContent();
			}
		}
		return false;
	}

	/**
	 *	bool _skipFile : check whether a file is in backup or restore skip list
	 *
	 * @param type $file : current file or directory name eg:'.svn' , 'settings.inc.php'
	 * @param type $fullpath : current file or directory fullpath eg:'/home/web/www/prestashop/config/settings.inc.php'
	 * @param type $way : 'backup' , 'upgrade'
	 */
	protected function _skipFile($file, $fullpath, $way = 'backup')
	{
		$fullpath = str_replace('\\', '/', $fullpath); // wamp compliant
		$rootpath = str_replace('\\', '/', $this->prodRootDir);
		$admin_dir = str_replace($this->prodRootDir, '', $this->adminDir);
		switch ($way)
		{
			case 'backup':
				if (in_array($file, $this->backupIgnoreFiles))
					return true;

				foreach ($this->backupIgnoreAbsoluteFiles as $path)
				{
					$path = str_replace(DIRECTORY_SEPARATOR.'admin', DIRECTORY_SEPARATOR.$admin_dir, $path);
					if ($fullpath == $rootpath.$path)
						return true;
				}
				break;
				// restore or upgrade way : ignore the same files
				// note the restore process use skipFiles only if xml md5 files
				// are unavailable
			case 'restore':
				if (in_array($file, $this->restoreIgnoreFiles))
					return true;

				foreach ($this->restoreIgnoreAbsoluteFiles as $path)
				{
					$path = str_replace(DIRECTORY_SEPARATOR.'admin', DIRECTORY_SEPARATOR.$admin_dir, $path);
					if ($fullpath == $rootpath.$path)
						return true;
				}
				break;
			case 'upgrade':
				// keep mail : will skip only if already exists
				if (!$this->keepMails) /* If set to false, we will not upgrade/replace the "mails" directory */
				{
					if (strpos(str_replace('/', DIRECTORY_SEPARATOR, $fullpath), DIRECTORY_SEPARATOR.'mails'.DIRECTORY_SEPARATOR))
						return true;
				}
				if (in_array($file, $this->excludeFilesFromUpgrade))
				{
					if ($file[0] != '.')
					{
						$this->nextQuickInfo[] = sprintf($this->l('%s is preserved'), $file);
					}
					return true;
				}

				foreach ($this->excludeAbsoluteFilesFromUpgrade as $path)
				{
					$path = str_replace(DIRECTORY_SEPARATOR.'admin', DIRECTORY_SEPARATOR.$admin_dir, $path);
					if (strpos($fullpath, $rootpath.$path) !== false)
					{
						$this->nextQuickInfo[] = sprintf($this->l('%s is preserved'), $fullpath);
						return true;
					}
				}

				break;
				// default : if it's not a backup or an upgrade, do not skip the file
			default:
				return false;
		}
		// by default, don't skip
		return false;
	}

	public function optionDisplayErrors()
	{
		if ($this->getConfig('PS_DISPLAY_ERRORS'))
		{
			error_reporting(E_ALL);
			ini_set('display_errors', 'on');
		}
		else
			ini_set('display_errors', 'off');
	}
}