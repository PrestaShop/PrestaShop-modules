<?php

/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
* @author PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2014 PrestaShop SA
* @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/

abstract class DataExtractorToXML
{
	/** @var string path of the XML repository */
	public $sRepositoryPath;

	/** @var string name of the final XML File without the current date */
	public $sFileNameBase;

	/** @var string name of the XML File containing the current date */
	public $sFileName;

	/** @var string name of the current entity */
	public $sEntity;

	/** @var string name of the current entity */
	public $sEntityRoot;

	/** @var array list of extraction logs */
	private $_logs;

	/** @var array list of errors */
	public $_errors;

	/** @var array list of confirmations */
	public $_confirmations;

	/** @var integer Number of entities to extract */
	public $nbEntities;

	/** @var integer Number of entities extracted */
	public $nbEntitiesTreated;

	/** @var integer Last of the extraction */
	public $execTime;
	
	/** @var boolean is log enable */
	public $bLogEnable;

	/**
	  * Initialise the object variables
	  *
	  * @param string $sRepositoryPath path of the XML repository
	  * @param array $params Specific parameters of the object
	  */
	public function __construct($sRepositoryPath, $params, $bLogEnable)
	{
		$this->sRepositoryPath 		= $sRepositoryPath;
		$this->bLogEnable 			= (int)$bLogEnable;
		$this->_logs 				= array();
		$this->_errors 				= array();
		$this->_confirmations 		= array();
		$this->nbEntities 			= 0;
		$this->nbEntitiesTreated 	= 0;
	}

	/**
	  * Extraction exectution
	  *
	  * @return bool if extraction success of failed
	  */
	public function extract()
	{
		$this->execTime = microtime(true);

		// Get the SQL Result containing the list of entities
		$oResult = $this->getEntities();

		// Set the number of entities to extract
		$this->nbEntities = DB::getInstance(_PS_USE_SQL_SLAVE_)->NumRows($oResult);

		// Initialise the number of entities extracted to 0
		$this->nbEntitiesTreated = 0;

		// Set the name of XML File
		$this->sFileName = $this->sFileNameBase.'-'.date('YmdHis').'.xml';
		
		// Open log file handler if needed
		if($this->bLogEnable)
		{
			$sEntityLogFileName = $this->sRepositoryPath.'../logs/log-'.$this->sEntity.'.txt';
			if(!($loghandle = fopen($sEntityLogFileName, 'a')))
			{
				$this->bLogEnable = false;
				$this->_errors[] = 'Error when creating the log file : '.$sEntityLogFileName;
			}
		}

		// Create the XML File
		if($handle = fopen($this->sRepositoryPath.$this->sFileName, 'a'))
			$sLog = '[BEGIN][OK] ACTION : CREATE FILE ['.$this->sFileName.'] - Entity : '.$this->sEntity;
		else
			$sLog = '[BEGIN][FAIL] ACTION : CREATE FILE ['.$this->sFileName.'] - Entity : '.$this->sEntity;
		
		// Add the first export log and test if log file is in writting mode
		if($this->bLogEnable && !fwrite($loghandle, $sLog."\n"))
		{
			$this->bLogEnable = false;
			$this->_errors[] = 'Error when writing in the log file : '.$sEntityLogFileName;
		}

		// Write XML HEADER & Root tag
		fwrite($handle, '<?xml version="1.0" encoding="utf-8"?>'."\n");
		fwrite($handle, '<'.$this->sEntityRoot.' date="'.date('c').'">');

		// Write File process
		if($oResult)
		{
			while ($aEntity = DB::getInstance(_PS_USE_SQL_SLAVE_)->nextRow($oResult))
			{
				// Format entity data to xml
				if($sContent = $this->formatEntityToXML($aEntity))
				{
					$sLog = '[OK] ACTION : FORMAT - Entity : '.$this->sEntity;
					if($this->bLogEnable)
						fwrite($loghandle, $sLog."\n");
					
					// Write xml as string into file
					if(fwrite($handle, str_replace(array("\n","\r"),' ',$sContent)."\n"))
					{
						$sLog = '[OK] ACTION : WRITE - Entity : '.$this->sEntity;
						$this->nbEntitiesTreated++;
					}
					else
						$sLog = '[FAIL] ACTION : WRITE - Entity : '.$this->sEntity.' - '.join(',',$aEntity);
					
					if($this->bLogEnable)
						fwrite($loghandle, $sLog."\n");
				}
				else
				{
					$sLog = '[FAIL] ACTION : FORMAT - Entity : '.$this->sEntity.' - '.join(',',$aEntity);
				
					if($this->bLogEnable)
						fwrite($loghandle, $sLog."\n");
				}
			}
			$sLog  = '[DATA] NB ENTITIES CREATED : '.$this->nbEntities;
			
			if($this->bLogEnable)
				fwrite($loghandle, $sLog."\n");
		}
		else
		{
			$sLog = '[FAIL] NO ENTITY TO EXPORT : '.$this->sEntity;
			if($this->bLogEnable)
				fwrite($loghandle, $sLog."\n");
		}

		// END Root tag
		fwrite($handle, '</'.$this->sEntityRoot.'>');

		// Close File
		if(fclose($handle))
			$sLog = '[OK] ACTION : CLOSE FILE ['.$this->sFileName.'] - Entity : '.$this->sEntity;
		else
			$sLog = '[FAIL] ACTION : CLOSE FILE ['.$this->sFileName.'] - Entity : '.$this->sEntity;
		
		if($this->bLogEnable)
			fwrite($loghandle, $sLog."\n");

		// Set the executino time
		$this->execTime = number_format(microtime(true) - $this->execTime, 3, '.', '');
		$sLog = '[END] [DATA] TOTAL EXPORT TIME ['.$this->sFileName.'] : '.$this->execTime.'s';
		
		if($this->bLogEnable)
			fwrite($loghandle, $sLog."\n");

		// Close log file handler
		if($this->bLogEnable)
			fclose($loghandle);
		
		if((int)$this->nbEntitiesTreated < (int)$this->nbEntities)
			return false;
		return true;
	}

	/**
	  * Get the list of entities by a sql result
	  *
	  * @return Object SQL Result
	  */
	public function getEntities(){}


	/**
	  * Get the path of the current Xml File
	  *
	  * @return string path
	  */
	public function getXMLFilePath()
	{
		return $this->sRepositoryPath.$this->sFileName;
	}

	/**
	  * Get the url of the current Xml File
	  *
	  * @return string url
	  */
	public function getXMLFileURL()
	{
		return Tools::getShopDomain(true).str_replace(_PS_ROOT_DIR_.'/', __PS_BASE_URI__, $this->getXMLFilePath());
	}

	/**
	  * Convert the entities data into an xml object and return the xml object as a string
	  *
	  * @param array $aEntity Entity data
	  */
	public function formatEntityToXML($aEntity){}


	/**
	  * Get the current extraction last
	  *
	  * @return float seconds
	  */
	public function getExecTime()
	{
		return 	$this->execTime;
	}

	/**
	  * Get the current extraction logs
	  *
	  * @return array _logs
	  */
	public function getLogs()
	{
		return $this->_logs;
	}
}