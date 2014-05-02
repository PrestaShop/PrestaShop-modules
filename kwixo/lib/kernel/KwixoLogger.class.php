<?php
/**
 * 2007-2014 PrestaShop
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2014 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

/**
 * Class matching with the pattern Singleton that allow log insertions
 * 
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class KwixoLogger
{

	private static $handle = null; /*handle resource, defined when the log fil is opened*/

	/**
	 * creates a file localized @ $path and returns its ressource
	 * 
	 * @param string $path path to the file created, filename included
	 * @return ressource
	 */
	private static function openFile($path)
	{
		$handle = fopen($path, 'a+');
		return $handle;
	}

	/**
	 * creates the log file if it doesn't exist
	 * rename the log file then creates a new one if the existing one has reached the max allowed size (100Ko)
	 * open the log file
	 * assigns the log file handle to the local var $_handle
	 * 
	 * @return void
	 */
	private static function openHandle()
	{
		if (!file_exists(KWIXO_ROOT_DIR.'/logs/'))
			if (!mkdir(KWIXO_ROOT_DIR.'/logs/'))
				die('Error creating logs folder');

		$log_filename = KWIXO_ROOT_DIR.'/logs/fianet_log.txt';

		//renames the log file and creates a new one if max allowed size reached
		if (file_exists($log_filename) && filesize($log_filename) > 100000)
		{
			$prefix = KWIXO_ROOT_DIR.'/logs/fianetlog-';
			$base = date('YmdHis');
			$sufix = '.txt';
			$filename = $prefix.$base.$sufix;

			for ($i = 0; file_exists($filename); $i++)
				$filename = $prefix.$base."-$i".$sufix;

			rename($log_filename, $filename);
		}

		self::$handle = self::openFile($log_filename);
		register_shutdown_function('fclose', self::$handle);
	}

	/**
	 * inserts a new log entry into the log file
	 * 
	 * @param string $from invoker information
	 * @param string $msg message to log
	 */
	public static function insertLogKwixo($from, $msg)
	{
		//opens the log file if it's not openned yet
		if (is_null(self::$handle))
			self::openHandle();
		//builds the entry string
		$entry = date('d-m-Y H:i:s')." | $from | $msg\r";
		//write the entry into the log file
		fwrite(self::$handle, $entry);
	}

	/**
	 * gets the content of the log file
	 * opens the log file if it's not already opened
	 * 
	 * @return string
	 */
	public static function getLogContent()
	{
		//opens the log file if it's not openned yet
		if (is_null(self::$handle))
			self::openHandle();
		return fgets(self::$handle);
	}

}
