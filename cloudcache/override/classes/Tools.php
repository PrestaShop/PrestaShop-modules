<?php
/*
* 2007-2012 PrestaShop
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
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 12823 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class Tools extends ToolsCore
{
	/** @var _totalServerCount Total count of all servers */
	private static $_totalServerCount = 0;
	/** @var _servers Array containing all the media servers by type */
	private static $_servers = null;
	/** @var _serversCount Array countaining the count of servers by type */
	private static $_serversCount = null;
	/** @var _fileTypes Available file types */
	private static $_fileTypes = null;
	/** @var _activatedModule Flag weither or not the module is active */
	private static $_activatedModule = false;

	public static $id_shop_group = 1;
	public static $id_shop = 1;

	/** @var _isActive Flag to know if the module is active or note */
	public static $_isActive = -1;

	private static function _selectProtocol(&$url)
	{
		$useSSL = (Configuration::get('PS_SSL_ENABLED') && Tools::usingSecureMode());

		if (preg_match('#(.*)\.'.Configuration::get('CLOUDCACHE_API_COMPANY_ID').'\.netdna-cdn.com$#', $url, $matches) && $useSSL)
			$url = $matches[1].'-'.Configuration::get('CLOUDCACHE_API_COMPANY_ID').'.netdna-ssl.com';
		return $useSSL ? 'https://' : 'http://';
	}

	/**
	 * @brief Init the statics needed by getMediaServer
	 */
	private static function _initServers()
	{
		require_once(dirname(__FILE__).'/../../modules/cloudcache/backward_compatibility/backward.php');

		$context = Context::getContext();
		self::$id_shop = $context->shop->id;
		self::$id_shop_group = $context->shop->id_shop_group;

		// Init the statics
		self::$_servers = array();
		self::$_serversCount = array();
		self::$_fileTypes = array(CLOUDCACHE_FILE_TYPE_IMG, CLOUDCACHE_FILE_TYPE_JS,
												CLOUDCACHE_FILE_TYPE_CSS, CLOUDCACHE_FILE_TYPE_OTHER,
												CLOUDCACHE_FILE_TYPE_ALL);

		// check if the module is active
		self::$_activatedModule = Configuration::get('CLOUDCACHE_API_ACTIVE');

		foreach (self::$_fileTypes as $type)
		{
			self::$_servers[$type] = array();
			self::$_serversCount[$type] = 0;
		}

		$d = Db::getInstance()->executeS('SELECT `cdn_url`, `file_type`
								 FROM `'._DB_PREFIX_.'cloudcache_zone`
								 WHERE `file_type` != \''.CLOUDCACHE_FILE_TYPE_UNASSOCIATED.'\' AND `id_shop` = '.(int)self::$id_shop);

		$allOnly = false;
		foreach ($d as $line)
			if ($line['file_type'] == CLOUDCACHE_FILE_TYPE_ALL)
			{
				$protocol = self::_selectProtocol($line['cdn_url']); // Must be there because _SelectProtocol updates the url
				self::$_servers[CLOUDCACHE_FILE_TYPE_ALL][] = array('url' => $line['cdn_url'], 'protocol' => $protocol);
				self::$_serversCount[CLOUDCACHE_FILE_TYPE_ALL]++;
				self::$_totalServerCount++;
				$allOnly = true;
			}

		foreach ($d as $line)
			if ($line['file_type'] && !$allOnly)
			{
				$protocol = self::_selectProtocol($line['cdn_url']); // Must be there because _SelectProtocol updates the url
				self::$_servers[$line['file_type']][] = array('url' => $line['cdn_url'], 'protocol' => $protocol);
				self::$_serversCount[$line['file_type']]++;
				self::$_totalServerCount++;
			}
	}

	/**
	 * @brief Temper with JS files
	 *
	 * @param js_uri URI of the JS
	 *
	 * @note 1.4 only
	 */
	public static function addJS($js_uri)
	{
		parent::addJS($js_uri);

		if (!self::_isActive())
			return ;

		global $js_files;

		foreach ($js_files as &$file)
			if (!preg_match('/^http(s?):\/\//i', $file))
			{
				$proto = 'http://';
				$file = self::getMediaServer($file, $proto).$file;
				$file = $proto.$file;
			}
	}

	/**
	 * @brief Temper with CSS files
	 *
	 * @param css_uri URI of the CSS
	 * @param css_media_type Type of the css
	 *
	 * @note 1.4 only
	 */
	public static function addCSS($css_uri, $css_media_type = 'all')
	{
		parent::addCSS($css_uri, $css_media_type);

		if (!self::_isActive())
			return ;

		global $css_files;

		$new = array();
		foreach ($css_files as $key => $file)
		{
			if (!preg_match('/^http(s?):\/\//i', $key))
			{
				$proto = 'http://';
				$key = self::getMediaServer($key, $proto).$key; // Pass as reference, do not move $proto
				$key = $proto.$key;
			}
			$new[$key] = $file;
		}
		$css_files = $new;
	}

	public static function getProtocol($use_ssl = null)
	{
		if (self::_isActive())
			return (Configuration::get('PS_SSL_ENABLED') && self::usingSecureMode()) ? 'https://' : 'http://';
		return parent::getProtocol($use_ssl);
	}

	private static function _isActive()
	{
		if (self::$_isActive == -1)
		{
			// This override is part of the cloudcache module, so the cloudcache.php file exists
			require_once(dirname(__FILE__).'/../../modules/cloudcache/cloudcache.php');
			$module = new CloudCache();
			self::$_isActive = $module->active;
		}

		return self::$_isActive;
	}

	/**
	 * @brief Retrieve the media server to use
	 *
	 * @param filename Name of the file to serve (acually, part of the path)
	 *
	 * @todo Check performences
	 *
	 * @return URL of the server to use.
	 */
	public static function getMediaServer($filename, &$protocol = NULL)
	{
		if (!self::_isActive())
			return parent::getMediaServer($filename);

		// Init the server list if needed
		if (!self::$_servers)
			self::_initServers();

		if (!self::$_activatedModule)
			return parent::getMediaServer($filename);


		// If there is a least one ALL server, then use one of them
		if (self::$_serversCount[CLOUDCACHE_FILE_TYPE_ALL])
		{
			$server = self::$_servers[CLOUDCACHE_FILE_TYPE_ALL][(abs(crc32($filename)) %
					self::$_serversCount[CLOUDCACHE_FILE_TYPE_ALL])];
			if ($protocol)
				$protocol = $server['protocol'];
			return $server['url'];
		}


		// If there is servers, then use them
		if (self::$_totalServerCount)
		{
			// Loop on the file types to find the current one
			foreach (self::$_fileTypes as $type)
				// If we find the type in the filename, then it is our
				if (strstr($filename, $type) && self::$_serversCount[$type])
				{
					// Return one of those server
					$server = (self::$_servers[$type][(abs(crc32($filename)) %
								self::$_serversCount[$type])]);
					if ($protocol)
						$protocol = $server['protocol'];
					return $server['url'];
				}

			// If no file type found, then it is 'other'
			// If there is server setted for the 'other' type, use it
			if (self::$_serversCount[CLOUDCACHE_FILE_TYPE_OTHER])
			{
				// Return one of the server setted up
				$server = (self::$_servers[$type][(abs(crc32($filename)) %
											self::$_serversCount[$type])]);
				if ($protocol)
					$protocol = $server['protocol'];
				return $server['url'];
			}
		}

		// If there is no server setted up, then use the parent method
		return parent::getMediaServer($filename);
	}
}
