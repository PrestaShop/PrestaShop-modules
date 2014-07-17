<?php
/**
 * 	Riskified payments security module for Prestashop. Riskified reviews, approves & guarantees transactions you would otherwise decline.
 *
 *  @author    riskified.com <support@riskified.com>
 *  @copyright 2013-Now riskified.com
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Riskified 
 */

/** 
 * Copied from CertissimLogger.class.php prestashop fianetfraud module.
 */
class RiskifiedLogger
{

	private static $handle = null;

	/**
	 * creates a file localized @ $path and returns its ressource
	 * 
	 * @param string $path path to the file created, filename included
	 * @return ressource
	 */
	private static function openFile($path)
	{
		$h = fopen($path, 'a+');
		return $h;
	}

	/**
	 * creates the log file if it doesn't exist
	 * rename the log file then creates a new one if the existing one has reached the max allowed size (100Ko)
	 * open the log file
	 * assigns the log file handle to the local var $handle
	 * 
	 * @return void
	 */
	private static function openHandle()
	{
		if (!file_exists(RISKIFIED_ROOT_DIR.'/logs/'))
			if (!mkdir(RISKIFIED_ROOT_DIR.'/logs/'))
				die('Error creating logs folder');

		$log_filename = RISKIFIED_ROOT_DIR.'/logs/riskified.log';

		//renames the log file and creates a new one if max allowed size reached
		if (file_exists($log_filename) && filesize($log_filename) > 100000)
		{
			$prefix = RISKIFIED_ROOT_DIR.'/logs/riskifiedlog-';
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
	public static function insertLog($from, $msg)
	{
		//opens the log file if it's not openned yet
		if (is_null(self::$handle))
			self::openHandle();

		//builds the entry string
		$entry = date('d-m-Y h:i:s')." | $from | $msg\n";
		//write the entry into the log file
		fwrite(self::$handle, $entry);
		//fflush(self::$handle);
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
