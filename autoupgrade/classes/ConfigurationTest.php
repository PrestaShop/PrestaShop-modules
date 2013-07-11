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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class ConfigurationTestCore
{
	static function check($tests)
	{
		$res = array();
		foreach ($tests as $key => $test)
			$res[$key] = self::run($key, $test);
		return $res;
	}

	static function run($ptr, $arg = 0)
	{
		if (call_user_func(array('ConfigurationTest', 'test_'.$ptr), $arg))
			return 'ok';
		return 'fail';
	}

	// Misc functions
	static function test_phpversion()
	{
		return version_compare(substr(phpversion(), 0, 3), '5.0', '>=');
	}

	static function test_mysql_support()
	{
		return function_exists('mysql_connect');
	}

	static function test_magicquotes()
	{
		return !get_magic_quotes_gpc();
	}

	static	function test_upload()
	{
		return  ini_get('file_uploads');
	}

	static function test_fopen()
	{
		return ini_get('allow_url_fopen');
	}

	static function test_curl()
	{
		return function_exists('curl_init');
	}

	static function test_system($funcs)
	{
		foreach ($funcs AS $func)
			if (!function_exists($func))
				return false;
		return true;
	}

	static function test_gd()
	{
		return function_exists('imagecreatetruecolor');
	}

	static function test_register_globals()
	{
		return !ini_get('register_globals');
	}

	static function test_gz()
	{
		if (function_exists('gzencode'))
			return !(@gzencode('dd') === false);
		return false;
	}

	public static function test_dir($relative_dir, $recursive = false, &$full_report = null)
	{
		$dir = rtrim(_PS_ROOT_DIR_, '\\/').DIRECTORY_SEPARATOR.trim($relative_dir, '\\/');
		if (!file_exists($dir) || !$dh = opendir($dir))
		{
			$full_report = sprintf('Directory %s does not exists or is not writable', $dir); // sprintf for future translation
			return false;
		}
		$dummy = rtrim($dir, '\\/').DIRECTORY_SEPARATOR.uniqid();
		if (false && @file_put_contents($dummy, 'test'))
		{
			@unlink($dummy);
			if (!$recursive)
			{
				closedir($dh);
				return true;
			}
		}
		elseif (!is_writable($dir))
		{
			$full_report = sprintf('Directory %s is not writable', $dir); // sprintf for future translation
			return false;
		}

		if ($recursive)
			while (($file = readdir($dh)) !== false)
				if (is_dir($dir.DIRECTORY_SEPARATOR.$file) && $file != '.' && $file != '..' && $file != '.svn')
					if (!ConfigurationTest::test_dir($relative_dir.DIRECTORY_SEPARATOR.$file, $recursive, $full_report))
						return false;

		closedir($dh);
		return true;
	}

	// is_writable files
	static function test_file($file)
	{
		return file_exists($file) && is_writable($file);
	}

	static function test_config_dir($dir)
	{
		return self::test_dir($dir);
	}

	static function test_sitemap($dir)
	{
		return self::test_file($dir);
	}

	static function test_root_dir($dir)
	{
		return self::test_dir($dir);
	}

	static function test_log_dir($dir)
	{
		return self::test_dir($dir);
	}

	static function test_admin_dir($dir)
	{
		return self::test_dir($dir);
	}

	static function test_img_dir($dir)
	{
		return self::test_dir($dir, true);
	}

	static function test_module_dir($dir)
	{
		return self::test_dir($dir, true);
	}

	static function test_tools_dir($dir)
	{
		return self::test_dir($dir);
	}

	static function test_cache_dir($dir)
	{
		return self::test_dir($dir);
	}

	static function test_tools_v2_dir($dir)
	{
		return self::test_dir($dir);
	}

	static function test_cache_v2_dir($dir)
	{
		return self::test_dir($dir);
	}

	static function test_download_dir($dir)
	{
		return self::test_dir($dir);
	}

	static function test_mails_dir($dir)
	{
		return self::test_dir($dir, true);
	}

	static function test_translations_dir($dir)
	{
		return self::test_dir($dir, true);
	}

	static function test_theme_lang_dir($dir)
	{
		if (!file_exists($dir))
			return true;
		return self::test_dir($dir, true);
	}

	static function test_theme_cache_dir($dir)
	{
		if (!file_exists($dir))
			return true;
		return self::test_dir($dir, true);
	}

	static function test_customizable_products_dir($dir)
	{
		return self::test_dir($dir);
	}

	static function test_virtual_products_dir($dir)
	{
		return self::test_dir($dir);
	}

	static function test_mcrypt()
	{
		return function_exists('mcrypt_encrypt');
	}

	static function test_dom()
	{
		return extension_loaded('Dom');
	}
}
