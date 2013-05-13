<?php
/*
* 2007-2013 PrestaShop
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
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class Autoupgrade extends Module
{
	public function __construct()
	{
		$this->name = 'autoupgrade';
		$this->tab = 'administration';
		$this->version = '1.0.20';
		
		if (version_compare(_PS_VERSION_, '1.5.0.0 ', '>='))
			$this->multishop_context = Shop::CONTEXT_ALL;

		if (!defined('_PS_ADMIN_DIR_'))
		{
			if (defined('PS_ADMIN_DIR'))
				define('_PS_ADMIN_DIR_', PS_ADMIN_DIR);
			else
				$this->_errors[] = $this->l('This version of PrestaShop cannot be upgraded : PS_ADMIN_DIR constant is missing');
		}

		parent::__construct();

		$this->displayName = $this->l('1-click Upgrade');
		$this->description = $this->l('Provides an automated method to upgrade your shop to the latest PrestaShop version');
		$autoupgrade_dir = _PS_ADMIN_DIR_.DIRECTORY_SEPARATOR.'autoupgrade';	
		@copy(dirname(__FILE__).DIRECTORY_SEPARATOR.'ajax-upgradetab.php', $autoupgrade_dir.DIRECTORY_SEPARATOR.'ajax-upgradetab.php');			
	}

	public function install()
	{		
		/* Before creating a new tab "AdminSelfUpgrade" we need to remove any existing "AdminUpgrade" tab (present in v1.4.4.0 and v1.4.4.1) */
		if ($id_tab = Tab::getIdFromClassName('AdminUpgrade'))
		{
			$tab = new Tab((int)$id_tab);
			if (!$tab->delete())
				$this->_errors[] = sprintf($this->l('Unable to delete outdated AdminUpgrade tab %d'), (int)$id_tab);
		}

		/* If the "AdminSelfUpgrade" tab does not exist yet, create it */
		if (!$id_tab = Tab::getIdFromClassName('AdminSelfUpgrade'))
		{
			$tab = new Tab();
			$tab->class_name = 'AdminSelfUpgrade';
			$tab->module = 'autoupgrade';
			$tab->id_parent = (int)Tab::getIdFromClassName('AdminTools');
			foreach (Language::getLanguages(false) as $lang)
				$tab->name[(int)$lang['id_lang']] = '1-Click Upgrade';
			if (!$tab->save())
				return $this->_abortInstall($this->l('Unable to create the "AdminSelfUpgrade" tab'));
			if (!@copy(dirname(__FILE__).DIRECTORY_SEPARATOR.'logo.gif', _PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'t'.DIRECTORY_SEPARATOR.'AdminSelfUpgrade.gif'))
				return $this->_abortInstall(sprintf($this->l('Unable to copy logo.gif in %s'), _PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'t'.DIRECTORY_SEPARATOR));
		}
		else
			$tab = new Tab((int)$id_tab);

		/* Update the "AdminSelfUpgrade" tab id in database or exit */
		if (Validate::isLoadedObject($tab))
			Configuration::updateValue('PS_AUTOUPDATE_MODULE_IDTAB', (int)$tab->id);
		else
			return $this->_abortInstall($this->l('Unable to load the "AdminSelfUpgrade" tab'));

		/* Check that the 1-click upgrade working directory is existing or create it */
		$autoupgrade_dir = _PS_ADMIN_DIR_.DIRECTORY_SEPARATOR.'autoupgrade';
		if (!file_exists($autoupgrade_dir) && !@mkdir($autoupgrade_dir, 0755))
			return $this->_abortInstall(sprintf($this->l('Unable to create the directory "%s"'), $autoupgrade_dir));
		
		/* Make sure that the 1-click upgrade working directory is writeable */
		if (!is_writable($autoupgrade_dir))
			return $this->_abortInstall(sprintf($this->l('Unable to write in the directory "%s"'), $autoupgrade_dir));

		/* If a previous version of ajax-upgradetab.php exists, delete it */
		if (file_exists($autoupgrade_dir.DIRECTORY_SEPARATOR.'ajax-upgradetab.php'))
			@unlink($autoupgrade_dir.DIRECTORY_SEPARATOR.'ajax-upgradetab.php');
		
		/* Then, try to copy the newest version from the module's directory */
		if (!@copy(dirname(__FILE__).DIRECTORY_SEPARATOR.'ajax-upgradetab.php', $autoupgrade_dir.DIRECTORY_SEPARATOR.'ajax-upgradetab.php'))
			return $this->_abortInstall(sprintf($this->l('Unable to copy ajax-upgradetab.php in %s'), $autoupgrade_dir));

		/* Make sure that the XML config directory exists */
		if (!file_exists(_PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'xml') &&
		!@mkdir(_PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'xml', 0755))
			return $this->_abortInstall(sprintf($this->l('Unable to create the directory "%s"'), _PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'xml'));
		
		/* Create a dummy index.php file in the XML config directory to avoid directory listing */
		if (!file_exists(_PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'index.php') &&
		(file_exists(_PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'index.php') &&
		!@copy(_PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'index.php', _PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'index.php')))
			return $this->_abortInstall(sprintf($this->l('Unable to create the directory "%s"'), _PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'xml'));

		return parent::install();
	}

	public function uninstall()
	{
		/* Delete the 1-click upgrade Back-office tab */
		if ($id_tab = Tab::getIdFromClassName('AdminSelfUpgrade'))
		{
			$tab = new Tab((int)$id_tab);
			$tab->delete();
		}

		/* Remove the 1-click upgrade working directory */
		self::_removeDirectory(_PS_ADMIN_DIR_.DIRECTORY_SEPARATOR.'autoupgrade');
		
		return parent::uninstall();
	}

	public function getContent()
	{
		global $cookie;
		header('Location: index.php?tab=AdminSelfUpgrade&token='.md5(pSQL(_COOKIE_KEY_.'AdminSelfUpgrade'.(int)Tab::getIdFromClassName('AdminSelfUpgrade').(int)$cookie->id_employee)));
		exit;
	}
	
	/**
	* Set installation errors and return false
	*
	* @param string $error Installation abortion reason
	* @return boolean Always false
	*/
	protected function _abortInstall($error)
	{
		if (version_compare(_PS_VERSION_, '1.5.0.0 ', '>='))
			$this->_errors[] = $error;
		else
			echo '<div class="error">'.strip_tags($error).'</div>';

		return false;
	}
	
	private static function _removeDirectory($dir)
	{     
		if ($handle = @opendir($dir))
		{
			while (false !== ($entry = @readdir($handle)))
				if ($entry != '.' && $entry != '..')
				{
					if (is_dir($dir.DIRECTORY_SEPARATOR.$entry) === true)
						self::_removeDirectory($dir.DIRECTORY_SEPARATOR.$entry);
					else
						@unlink($dir.DIRECTORY_SEPARATOR.$entry);
				}

			@closedir($handle);
			@rmdir($dir);
		}
	}
}
