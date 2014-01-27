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

require_once(_PS_MODULE_DIR_.'prediggo/classes/PrediggoConfig.php');
require_once(_PS_MODULE_DIR_.'prediggo/classes/CustomerExtractorToXML.php');
require_once(_PS_MODULE_DIR_.'prediggo/classes/OrderExtractorToXML.php');
require_once(_PS_MODULE_DIR_.'prediggo/classes/ProductExtractorToXML.php');

class DataExtractorController
{
	/** @var PrediggoConfig Object PrediggoConfig */
	public $oPrediggoConfig;

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
		$this->oPrediggoConfig = new PrediggoConfig(Context::getContext());
		
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
		// Addition of a secured token
		if(!Tools::getValue('token')
		|| Tools::getValue('token') != Tools::getAdminToken('DataExtractorController'))
			return;
		
		@ini_set('max_execution_time', '3000');
		@ini_set('max_input_time', '3000');
		@ini_set('memory_limit', '384M');

		$oDataExtractor = false;

		$aPrediggoConfigs = array();
		$oContext = Context::getContext();
		foreach(Shop::getCompleteListOfShopsID() as $iIDShop)
		{
			$oContext->shop = new Shop((int)$iIDShop);
			$aPrediggoConfigs[(int)$iIDShop] = new PrediggoConfig($oContext);
		}
		
		// Launch Customers export process
		$params = array(
			'aPrediggoConfigs' 	=> array(),
		);
		foreach($aPrediggoConfigs as $iIDShop => $oPrediggoConfig)
			if($oPrediggoConfig->customers_file_generation)
				$params['aPrediggoConfigs'][$iIDShop] = $oPrediggoConfig;
		
		if(count($params['aPrediggoConfigs']))
		{
			$oDataExtractor = new CustomerExtractorToXML($this->sRepositoryPath, $params, (int)$this->oPrediggoConfig->logs_file_generation);
			$this->lauchFileExport($oDataExtractor);
		}
		
		// Launch Orders export process
		$params = array(
			'aPrediggoConfigs' 	=> array(),
		);
		foreach($aPrediggoConfigs as $iIDShop => $oPrediggoConfig)
			if($oPrediggoConfig->orders_file_generation)
				$params['aPrediggoConfigs'][$iIDShop] = $oPrediggoConfig;
		
		if(count($params['aPrediggoConfigs']))
		{
			$oDataExtractor = new OrderExtractorToXML($this->sRepositoryPath, $params, (int)$this->oPrediggoConfig->logs_file_generation);
			$this->lauchFileExport($oDataExtractor);
		}
		
		// Launch Products export process
		$params = array(
			'aPrediggoConfigs' 	=> array(),
		);
		foreach($aPrediggoConfigs as $iIDShop => $oPrediggoConfig)
			if($oPrediggoConfig->products_file_generation)
				$params['aPrediggoConfigs'][$iIDShop] = $oPrediggoConfig;
		
		if(count($params['aPrediggoConfigs']))
		{
			$oDataExtractor = new ProductExtractorToXML($this->sRepositoryPath, $params, (int)$this->oPrediggoConfig->logs_file_generation);
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
		$this->module->_errors = array_merge($oDataExtractor->_errors, $this->module->_errors);
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
		require_once(dirname(__FILE__).'/../../../tools/pclzip/pclzip.lib.php');
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
}