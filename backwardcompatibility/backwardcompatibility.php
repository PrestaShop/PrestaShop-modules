<?php
/*
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class BackwardCompatibility extends Module
{
	public function __construct()
	{
		$this->name = 'backwardcompatibility';
		$this->tab = 'compatibility_tools';
		$this->version = 0.6;
		$this->author = 'PrestaShop';
		$this->need_instance = 1;

		parent::__construct();

		$this->displayName = $this->l('Backward compatibility');
		$this->description = $this->l('Improve modules compatibility.');

		if ($this->active && defined('_PS_ADMIN_DIR_'))
			$this->addContext();
	}
	
	public function install()
	{
		if (version_compare(_PS_VERSION_, '1.5') >= 0)
			return false;
		
		return parent::install();
	}

	public function addContext()
	{
		$backward_file = dirname(__FILE__).'/backward_compatibility/backward.php';
		if (file_exists($backward_file))
			include($backward_file);
	}

	public function _postProcess()
	{
		$results = array();
		$modules = $this->getModulesList();
		$local_backward = dirname(__FILE__).'/backward_compatibility/';
		$files = $this->getFilesList($local_backward);

		foreach ($modules as $module)
			if (strcmp($module['name'], 'backwardcompatibility') != 0)
			{
				$module_backward = _PS_MODULE_DIR_.$module['name'].'/backward_compatibility';
				if (file_exists($module_backward) && is_writable($module_backward))
					$results[$module['name']] = $this->copyFiles($files, $module);
			}

		$this->context->smarty->assign('update_results', $results);
	}

	protected function getFilesList($from)
	{
		$iterator = new DirectoryIterator($from);
		foreach ($iterator as $file)
			if (!$file->isDot() && ($file->getFilename() != '.svn') && ($file->getFilename() != '.git'))
				$files[] = array('filename' => $file->getFilename(), 'pathname' => $file->getPathname());
		return $files;
	}

	protected function copyFiles($files, $module)
	{
		$result = true;
		$module_backward = _PS_MODULE_DIR_.$module['name'].'/backward_compatibility';

		foreach ($files as $file)
			$result = $result && @copy($file['pathname'], $module_backward.'/'.$file['filename']);
		return $result;
	}

	public function getContent()
	{
		if (Tools::getValue('submit'))
			$this->_postProcess();

		$this->context->smarty->assign(
			array(
				'modules' => $this->getModulesList(),
				'image_dir' => _MODULE_DIR_.$this->name.'/img/'
			)
		);

		return $this->context->smarty->fetch(dirname(__FILE__).'/views/templates/back/backwardcompatibility.tpl');
	}

	public function getModulesList()
	{
		$results = array();
		$modules = Module::getModulesDirOnDisk();

		foreach ($modules as $module)
		{
			$backward_directory = _PS_MODULE_DIR_.$module.'/backward_compatibility/';
			$module = Module::getInstanceByName($module);

			if (is_dir($backward_directory) && (strcmp($module->name, 'backwardcompatibility') != 0))
			{
				$ini_file = $backward_directory.'backward.ini';
				$version = $this->getVersion($ini_file);

				if (($handle = @fopen($ini_file, 'a+')) && (is_writable($backward_directory)))
				{
					@fclose($handle);
					$writable = true;
				}
				else
					$writable = false;

				$results[] = array(
					'name' => $module->name,
					'display_name' => $module->displayName,
					'version' => $version,
					'writable' => $writable
				);
			}
		}

		return $results;
	}

	protected function getVersion($ini_file = false)
	{
		if (!$ini_file)
			$ini_file = dirname(__FILE__).'/backward_compatibility/backward.ini';

		if (file_exists($ini_file))
		{
			$ini_values = parse_ini_file($ini_file);
			return array_shift($ini_values);
		}
		return false;
	}
}


