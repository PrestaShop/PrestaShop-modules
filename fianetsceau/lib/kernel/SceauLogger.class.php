<?php

/**
 * Class matching with the pattern Singleton that allow log insertions
 * 
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class SceauLogger
{

	private static $_handle = null; //handle resource, defined when the log fil is opened

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

		if (!file_exists(SCEAU_ROOT_DIR.'/logs/'))
			if (!mkdir(SCEAU_ROOT_DIR.'/logs/'))
				die('Error creating logs folder');

		$log_filename = SCEAU_ROOT_DIR.'/logs/fianet_log.txt';
		
		//renames the log file and creates a new one if max allowed size reached
		if (file_exists($log_filename) && filesize($log_filename) > 100000)
		{
			$prefix = SCEAU_ROOT_DIR.'/logs/fianetlog-';
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
	public static function insertLogSceau($from, $msg)
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