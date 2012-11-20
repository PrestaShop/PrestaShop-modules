<?php

/**
 * @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
 * @copyright Croissance NET
 * @version 1.0
 */

require_once(_PS_MODULE_DIR_.'prediggo/classes/PrediggoExportConfig.php');
require_once(_PS_MODULE_DIR_.'prediggo/classes/CustomerExtractorToXML.php');
require_once(_PS_MODULE_DIR_.'prediggo/classes/OrderExtractorToXML.php');
require_once(_PS_MODULE_DIR_.'prediggo/classes/ProductExtractorToXML.php');

class DataExtractorController
{
	/** @var PrediggoExportConfig Object PrediggoExportConfig */
	public $oPrediggoExportConfig;

	/** @var Prediggo Object Prediggo */
	public $module;

	/** @var array list of logs */
	public $_logs;

	/** @var array list of errors */
	public $_errors;

	/** @var array list of confirmations */
	public $_confirmations;

	/** @var string path of the XML repository */
	public $sRepositoryPath;

	/**
	  * Initialise the object variables
	  *
	  * @param Prediggo $oModule Object Prediggo
	  */
	public function __construct($oModule = false)
	{
		$this->oPrediggoExportConfig = PrediggoExportConfig::singleton();

		$this->sRepositoryPath = _PS_MODULE_DIR_.'prediggo/xmlfiles/';

		// Set the module of the controller to get Content translations && set its errors / confirmations
		if($oModule)
			$this->module = $oModule;
		else
		{
			require_once(_PS_MODULE_DIR_.'prediggo/prediggo.php');
			$this->module = new Prediggo();
		}

		$this->_logs = array();
		$this->_errors = array();
		$this->_confirmations = array();
	}

	/**
	  * Launch the export process
	  */
	public function launchExport()
	{
		@ini_set('max_execution_time', '3000');
		@ini_set('max_input_time', '3000');
		@ini_set('memory_limit', '384M');

		$oDataExtractor = false;

		// Launch Customers export process
		if($this->oPrediggoExportConfig->customers_file_generation)
		{
			$params = array(
				'nbDaysCustomerValid' => (int)$this->oPrediggoExportConfig->nb_days_customer_last_visit_valide
			);

			$oDataExtractor = new CustomerExtractorToXML($this->sRepositoryPath, $params);
			$this->lauchFileExport($oDataExtractor);
		}

		// Launch Orders export process
		if($this->oPrediggoExportConfig->orders_file_generation)
		{
			$params = array(
				'nbDaysOrderValid' => (int)$this->oPrediggoExportConfig->nb_days_order_valide
			);
			$oDataExtractor = new OrderExtractorToXML($this->sRepositoryPath, $params);
			$this->lauchFileExport($oDataExtractor);
		}

		// Launch Products export process
		if($this->oPrediggoExportConfig->products_file_generation)
		{
			$params = array(
				'imageInExport' => $this->oPrediggoExportConfig->export_product_image,
				'descInExport' => $this->oPrediggoExportConfig->export_product_description,
				'productMinQuantity' => $this->oPrediggoExportConfig->export_product_min_quantity,
				'aAttributesGroupsIds' => explode(',',$this->oPrediggoExportConfig->attributes_groups_ids),
				'aFeaturesIds' => explode(',',$this->oPrediggoExportConfig->features_ids),
				'aProductsNotRecommendable' => explode(',',$this->oPrediggoExportConfig->products_ids_not_recommendable),
				'aProductsNotSearchable' => explode(',',$this->oPrediggoExportConfig->products_ids_not_searchable),
			);
			$oDataExtractor = new ProductExtractorToXML($this->sRepositoryPath, $params);
			$this->lauchFileExport($oDataExtractor);
		}
	}

	/**
	  * Execute an export of a specific item type (product, order, customer)
	  *
	  * @param DataExtractor $oDataExtractor Object DataExtractor
	  */
	public function lauchFileExport($oDataExtractor)
	{
		$sEntity = $oDataExtractor->sEntity;
		if(!$oDataExtractor->extract())
			$this->module->_errors[] = ucfirst($sEntity).' '.$this->module->l(': An error occurred while creating the XML File', get_class($this));
		if((int)$this->oPrediggoExportConfig->logs_file_generation)
			$this->setLogFile($oDataExtractor->getLogs(), $sEntity);

		$this->module->_confirmations[] = ucfirst($sEntity).' '.$this->module->l(': XML File created successfully : ', get_class($this)).$oDataExtractor->getXMLFileURL();

		// Create Zip File
		$sXMLFilePath = $this->copyXmlFileToNewFile($oDataExtractor, $sEntity);
		$sZipFilePath = $this->sRepositoryPath.$oDataExtractor->sFileNameBase.'.zip';
		if($this->zipXMLFile($sXMLFilePath, $sZipFilePath))
		{
			$this->module->_confirmations[] = ucfirst($sEntity).' '.$this->module->l(': XML File has been successfully zipped to file : ', get_class($this)).Tools::getShopDomain(true).str_replace(_PS_ROOT_DIR_.'/', __PS_BASE_URI__, $sZipFilePath);
			$this->module->_confirmations[] = ucfirst($sEntity).' '.$this->module->l(': File export process lasts : ', get_class($this)).$oDataExtractor->execTime.'s'.$this->module->l(', Nb Entities Treated : ', get_class($this)).$oDataExtractor->nbEntitiesTreated.'/'.$oDataExtractor->nbEntities;
		}
		else
			$this->module->_errors[] = ucfirst($sEntity).' '.$this->module->l(': An error occurred while zipping the File', get_class($this));
	}

	/**
	  * Copy the generated xml file (name with date) to a new xml file without the date in its name
	  *
	  * @param DataExtractor $oDataExtractor Object DataExtractor
	  * @param string $sEntity Name of the entity
	  * @return string $sNewXMLFilePath path of the new xml file
	  */
	public function copyXmlFileToNewFile($oDataExtractor, $sEntity)
	{
		//copy xml file to a file without date information
		$sXMLFilePath = $oDataExtractor->getXMLFilePath();
		$sNewXMLFilePath = $oDataExtractor->sRepositoryPath.$oDataExtractor->sFileNameBase.'.xml';
		if(copy($sXMLFilePath, $sNewXMLFilePath))
			return $sNewXMLFilePath;
	}

	/**
	  * Zip the xml file
	  *
	  * @param string $sXMLFilePath Path of the xml file (name without date)
	  * @param string $sZipFilePath path of the zip file
	  * @return string $sNewXMLFilePath path of the zip file
	  */
	public function zipXMLFile($sXMLFilePath, $sZipFilePath)
	{
		require_once(_PS_MODULE_DIR_.'prediggo/pclzip/pclzip.lib.php');
		$zip = new PclZip($sZipFilePath);
		return $zip->create($sXMLFilePath, PCLZIP_OPT_REMOVE_ALL_PATH);
	}

	/**
	  * Launch the protection activation or disactivation
	  *
	  * @param string $sUser HTPASSWD User
	  * @param string $sPwd HTPASSWD Password
	  * @return bool success of fail
	  */
	public function setRepositoryProtection($sUser, $sPwd)
	{
		require_once(_PS_MODULE_DIR_.'prediggo/classes/HtpasswdGenerator.php');
		$oHtpasswdGenerator = new HtpasswdGenerator($sUser, $sPwd, $this->sRepositoryPath);
		return $oHtpasswdGenerator->generate();
	}

	/**
	  * Get the xml repository
	  * @return string Path of the xml repository
	  */
	public function getRepositoryPath()
	{
		return Tools::getShopDomain(true).str_replace(_PS_ROOT_DIR_.'/', __PS_BASE_URI__, $this->sRepositoryPath);
	}

	/**
	  * Store the logs into the dedicated log file
	  *
	  * @param array $aLogs list of logs
	  * @param string $sEntity Name of the entity
	  */
	private function setLogFile($aLogs, $sEntity)
	{
		$sEntityLogFileName = $this->sRepositoryPath.'../logs/log-'.$sEntity.'.txt';
		if($handle = fopen($sEntityLogFileName, 'a'))
		{
			foreach($aLogs as $sLog)
				if(!fwrite($handle, $sLog."\n"))
					$this->module->_errors[] = $this->module->l('Error when writing in the log file').' : '.$sEntityLogFileName;
			fclose($handle);
		}
		else
			$this->module->_errors[] = $this->module->l('Error when creating the log file').' : '.$sEntityLogFileName;
	}
}