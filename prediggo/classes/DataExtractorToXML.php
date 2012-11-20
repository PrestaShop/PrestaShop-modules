<?php

/**
 * @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
 * @copyright Croissance NET
 * @version 1.0
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

	/**
	  * Initialise the object variables
	  *
	  * @param string $sRepositoryPath path of the XML repository
	  * @param array $params Specific parameters of the object
	  */
	public function __construct($sRepositoryPath, $params)
	{
		$this->sRepositoryPath = $sRepositoryPath;
		$this->_logs = array();
		$this->_errors = array();
		$this->_confirmations = array();
		$this->nbEntities = 0;
		$this->nbEntitiesTreated = 0;
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

		// Create the XML File
		if($handle = fopen($this->sRepositoryPath.$this->sFileName, 'a'))
			$this->_logs[] = '[BEGIN][OK] ACTION : CREATE FILE ['.$this->sFileName.'] - Entity : '.$this->sEntity;
		else
			$this->_logs[] = '[BEGIN][FAIL] ACTION : CREATE FILE ['.$this->sFileName.'] - Entity : '.$this->sEntity;

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
					$this->_logs[] = '[OK] ACTION : FORMAT - Entity : '.$this->sEntity;
					// Write xml as string into file
					if(fwrite($handle, str_replace(array("\n","\r"),' ',$sContent)."\n"))
					{
						$this->_logs[] = '[OK] ACTION : WRITE - Entity : '.$this->sEntity;
						$this->nbEntitiesTreated++;
					}
					else
						$this->_logs[] = '[FAIL] ACTION : WRITE - Entity : '.$this->sEntity.' - '.join(',',$aEntity);
				}
				else
					$this->_logs[] = '[FAIL] ACTION : FORMAT - Entity : '.$this->sEntity.' - '.join(',',$aEntity);
			}
			$this->_logs[]  = '[DATA] NB ENTITIES CREATED : '.$this->nbEntities;
		}
		else
			$this->_logs[] = '[FAIL] NO ENTITY TO EXPORT : '.$this->sEntity;

		// END Root tag
		fwrite($handle, '</'.$this->sEntityRoot.'>');

		// Close File
		if(fclose($handle))
			$this->_logs[] = '[OK] ACTION : CLOSE FILE ['.$this->sFileName.'] - Entity : '.$this->sEntity;
		else
			$this->_logs[] = '[FAIL] ACTION : CLOSE FILE ['.$this->sFileName.'] - Entity : '.$this->sEntity;

		// Set the executino time
		$this->execTime = number_format(microtime(true) - $this->execTime, 3, '.', '');
		$this->_logs[] = '[END] [DATA] TOTAL EXPORT TIME ['.$this->sFileName.'] : '.$this->execTime.'s';

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