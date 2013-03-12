<?php

class CertissimLogger
{

	private static $_handle = null;

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
		$log_filename = SAC_ROOT_DIR.'/logs/'.sha1(_COOKIE_KEY_.'fianet_log').'.txt';

		//renames the log file and creates a new one if max allowed size reached
		if (file_exists($log_filename) && filesize($log_filename) > 100000)
		{
			$prefix = SAC_ROOT_DIR.'/logs/fianetlog-';
			$base = date('YmdHis');
			$sufix = '.txt';
			$filename = $prefix.$base.$sufix;

			for ($i = 0; file_exists($filename); $i++)
				$filename = $prefix.$base."-$i".$sufix;

			rename($log_filename, $filename);
		}

		self::$_handle = self::openFile($log_filename);
		register_shutdown_function('fclose', self::$_handle);
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
		if (is_null(self::$_handle))
		{
			self::openHandle();
		}


		//builds the entry string
		$entry = date('d-m-Y h:i:s')." | $from | $msg\r";
		//write the entry into the log file
		fwrite(self::$_handle, $entry);
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
		if (is_null(self::$_handle))
		{
			self::openHandle();
		}

		return fgets(self::$_handle);
	}

}