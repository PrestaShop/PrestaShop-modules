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

class Mobile_Theme extends Module
{
	public $_errors = array();
	public $_html = '';

	public function __construct()
	{
		$this->name = 'mobile_theme';
		$this->tab = (version_compare(_PS_VERSION_, 1.4) >= 0 ? 'administration' : 'Theme');
		$this->version = '0.5.3';

		parent::__construct();

		$this->displayName = $this->l('Mobile Template');
		$this->description = $this->l('Provides a mobile template compatible with iPhone, Android, etc.');
		$this->confirmUninstall = $this->l('Uninstalling this module will delete your mobile template and all the custom modifications that you may have done on it, are you sure?');

		/* Compatibility with old versions of PrestaShop */
		if (!defined('_PS_ROOT_DIR_'))
			define('_PS_ROOT_DIR_', dirname(__FILE__).'/../../');
	}

	public function install()
	{
		if (_PS_VERSION_ >= '1.5')
		{
			$this->_errors[] = $this->l('This module cannot be installed on this version of PrestaShop.');
			return false;
		}
		
		return Configuration::updateValue('PS_MOBILE_THEME_HEADINGS', 'b') && Configuration::updateValue('PS_MOBILE_THEME_FILTERING_BAR', 'a')
		&& Configuration::updateValue('PS_MOBILE_THEME_PROCESS_BAR', 'a') && Configuration::updateValue('PS_MOBILE_THEME_CONF_MSG', 'e')
		&& Configuration::updateValue('PS_MOBILE_THEME_ERROR_MSG', 'a') && Configuration::updateValue('PS_MOBILE_THEME_LIST_HEADERS', 'b')
		&& Configuration::updateValue('PS_MOBILE_THEME_BUTTONS', 'e') && Configuration::updateValue('PS_MOBILE_THEME_HEADER_FOOTER', 'a')
		&& Configuration::updateValue('PS_MOBILE_DOMAIN', 'm.'.Configuration::get('PS_SHOP_DOMAIN')) && Configuration::updateValue('PS_MOBILE_DEVICE', 0)
		&& Configuration::updateValue('PS_REDIRECT_MOBILE_DOMAIN', 0) && Configuration::updateValue('PS_MOBILE_MODULE_ENABLED', 1)
		&& $this->modifySettingsFile(true) && $this->installTheme(true) && parent::install() && $this->registerHook('header')
		&& $this->registerHook('home') && $this->registerHook('footer') && $this->registerHook('backOfficeTop') && $this->installHook();
	}

	public function uninstall()
	{
		return Configuration::deleteByName('PS_MOBILE_THEME_HEADINGS') && Configuration::deleteByName('PS_MOBILE_THEME_FILTERING_BAR')
		&& Configuration::deleteByName('PS_MOBILE_THEME_PROCESS_BAR') && Configuration::deleteByName('PS_MOBILE_THEME_CONF_MSG')
		&& Configuration::deleteByName('PS_MOBILE_THEME_ERROR_MSG') && Configuration::deleteByName('PS_MOBILE_THEME_LIST_HEADERS')
		&& Configuration::deleteByName('PS_MOBILE_THEME_BUTTONS') && Configuration::deleteByName('PS_MOBILE_THEME_HEADER_FOOTER')
		&& Configuration::deleteByName('PS_MOBILE_DOMAIN') && Configuration::deleteByName('PS_MOBILE_DEVICE')
		&& Configuration::deleteByName('PS_REDIRECT_MOBILE_DOMAIN') && Configuration::deleteByName('PS_MOBILE_MODULE_ENABLED')
		&& Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'hook` WHERE `id_hook` = '.(int)Configuration::get('PS_MOBILE_HOOK_HEADER_ID'))
		&& Configuration::deleteByName('PS_MOBILE_HOOK_HEADER_ID')
		&& $this->modifySettingsFile(false) && $this->installTheme(false) && parent::uninstall();
	}

	public function installHook()
	{
		return Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'hook` (`name`, `title`, `description`)
		VALUE (\'displayMobileHeader\', \''.pSQL($this->l('Header of mobile pages')).'\', \''.pSQL($this->l('A hook which allow you to do things in the header of each pages of the Mobile version')).'\')') &&
		Configuration::updateValue('PS_MOBILE_HOOK_HEADER_ID', Hook::get('displayMobileHeader'));
	}

	/**
	 * @brief Edit the Settings file
	 *
	 * @param boolean $install Whether we are installing or uninstalling
	 * @param array $params Array fof parameters, allowed value: 'mobile_domain' => string, 'device' => {0:'phone', 1:'tablet', 2:'both'}
	 *
	 * @return boolean Success/Failure
	 */
	public function editSettings($install = true, $params = array())
	{
		if (!is_writable(_PS_ROOT_DIR_.'/config/settings.inc.php'))
		{
			$this->_errors[] = $this->l('Error: Your settings file is not writable, please change the permissions on this file.');
			return false;
		}

		$ret = true;
		/** Push the Device detection to settings.inc.php */
		$current_content = file(_PS_ROOT_DIR_.'/config/settings.inc.php');
		$new_content = '';

		if ($install)
		{
			/* Check that the settings file has not been modified already */
			foreach ($current_content as $line)
				if (strstr($line, '/* PrestaShop Mobile */') !== false)
					return true;

			$default_mobile_domain = 'm.'.Configuration::get('PS_SHOP_DOMAIN');
			$default_device = 'both';

			foreach ($current_content as $line)
			{
				if (strstr($line, 'define(\'_THEME_NAME_\'') !== false)
				{
					$new_content .= '/* PrestaShop Mobile */ ';
					if (version_compare(_PS_VERSION_, '1.4', '<'))
						$new_content .= 'if (strpos($_SERVER[\'REQUEST_URI\'], \'ps_mobile_site=1\') !== false) $_GET[\'ps_mobile_site\'] = 1; if (strpos($_SERVER[\'REQUEST_URI\'], \'ps_full_site=1\') !== false) $_GET[\'ps_full_site\'] = 1; if (strpos($_SERVER[\'REQUEST_URI\'], \'mobile_iframe=1\') !== false) $_GET[\'mobile_iframe\'] = 1; ';
					$new_content .= 'if ((isset($_GET[\'ps_mobile_site\']) && $_GET[\'ps_mobile_site\'] == 1) || !isset($_GET[\'ps_full_site\']) || (!isset($_GET[\'ps_full_site\']) && $_SERVER[\'HTTP_HOST\'] == '.(isset($params['mobile_domain']) ? '\''.$params['mobile_domain'].'\'' : '\''.$default_mobile_domain.'\'').')) { include(dirname(__FILE__).\'/../modules/mobile_theme/Mobile_Detect.php\'); $mobile_detect = new Mobile_Detect(); define(\'_PS_MOBILE_TABLET_\', '.(isset($params['device']) && $params['device'] == 0 ? '0' : '(int)$mobile_detect->isTablet()').'); define(\'_PS_MOBILE_PHONE_\', '.(isset($params['device']) && $params['device'] == 1 ? '0' : 'isset($_GET[\'ps_mobile_site\']) ? 1 : (int)$mobile_detect->isMobile()').'); } else { define(\'_PS_MOBILE_TABLET_\', 0); define(\'_PS_MOBILE_PHONE_\', 0); } define(\'_PS_MOBILE_\', _PS_MOBILE_PHONE_ || _PS_MOBILE_TABLET_); if (_PS_MOBILE_) define(\'_THEME_NAME_\', \'prestashop_mobile\'); else'."\n";
				}
				$new_content .= $line;
			}
		}
		else
			foreach ($current_content as $line)
				if (strstr($line, '/* PrestaShop Mobile */') === false)
					$new_content .= $line;

		$ret &= (bool)file_put_contents(_PS_ROOT_DIR_.'/config/settings.inc.php', $new_content);

		if ($ret && $install)
			Configuration::updateValue('PS_MOBILE_MODULE_ENABLED', 1);

		return $ret;
	}

	/**
	 * @brief Modify the PrestaShop core file for the Mobile
	 *
	 * @param boolean $install Whether we install or uninstall
	 *
	 * @return boolean Success/Failure
	 */
	protected function modifySettingsFile($install = true)
	{
		/* Check that the settings file is writable */
		if (!is_writable(_PS_ROOT_DIR_.'/config/config.inc.php'))
		{
			echo '<div class="error">'.$this->l('Error: Your /config/settings.inc.php and/or your /config/config.inc.php files are not writable, please change the permissions on those files').'</div>';
			return false;
		}

		/* Check that the settings file is writable */
		if (!is_writable(_PS_ROOT_DIR_.'/header.php') || !is_writable(_PS_ROOT_DIR_.'/footer.php'))
		{
			echo '<div class="error">'.$this->l('Error: Your /header.php and/or your /footer.php files are not writable, please change the permissions on those files').'</div>';
			return false;
		}

		$ret = $this->editSettings($install);

		/** Push the Mobile includes into config.inc.php */
		$content = file(_PS_ROOT_DIR_.'/config/config.inc.php');
		if ($install)
		{
			$php_flag = false;

			/* Check that the settings file has not been modified already */
			foreach ($content as $line)
			{
				if (strstr($line, '/* PrestaShop Mobile */') !== false)
					return true;
				elseif (strstr($line, '<?') !== false || strstr($line, '?>') !== false)
					$php_flag = !$php_flag;
			}

			$new_content = implode($content)."\n".(!$php_flag ? '<?php ' : '').'/* PrestaShop Mobile */ if (file_exists(_PS_MODULE_DIR_.\'mobile_theme/mobile.config.inc.php\')) include(_PS_MODULE_DIR_.\'mobile_theme/mobile.config.inc.php\');'.(!$php_flag ? '?>' : '');
		}
		else
		{
			$new_content = '';
			foreach ($content as $line)
				if (strstr($line, '/* PrestaShop Mobile */') === false)
					$new_content .= $line;
		}

		$ret &= (bool)file_put_contents(_PS_ROOT_DIR_.'/config/config.inc.php', $new_content);


		/** Push the Payment mobile compatibility into header.php */
		$content = file(_PS_ROOT_DIR_.'/header.php');
		if ($install)
		{
			$php_flag = false;

			/* Check that the settings file has not been modified already */
			foreach ($content as $line)
			{
				if (strstr($line, '/* PrestaShop Mobile */') !== false)
					return true;
				elseif (strstr($line, '<?') !== false || strstr($line, '?>') !== false)
					$php_flag = !$php_flag;
			}
			$new_content = implode($content)."\n".(!$php_flag ? '<?php ' : '').'/* PrestaShop Mobile */ if (_THEME_NAME_ == \'prestashop_mobile\') { global $smarty; $smarty->display(_PS_THEME_DIR_.\'header-page.tpl\'); $smarty->assign(\'no_header\', 1); } '.(!$php_flag ? '?>' : '');
		}
		else
		{
			$new_content = '';
			foreach ($content as $line)
				if (strstr($line, '/* PrestaShop Mobile */') === false)
					$new_content .= $line;
		}

		$ret &= (bool)file_put_contents(_PS_ROOT_DIR_.'/header.php', $new_content);


		/** Push the Payment mobile compatibilty into footer.php */
		$content = file(_PS_ROOT_DIR_.'/footer.php');
		if ($install)
		{
			/* Check that the settings file has not been modified already */
			foreach ($content as $line)
				if (strstr($line, '/* PrestaShop Mobile */') !== false)
					return true;

			if (version_compare(_PS_VERSION_, '1.4', '<'))
				$new_content = '<?php if (isset($smarty)) { $smarty->assign(array(\'HOOK_RIGHT_COLUMN\' => Module::hookExec(\'rightColumn\'),
		\'HOOK_FOOTER\' => Module::hookExec(\'footer\'), \'content_only\' => intval(Tools::getValue(\'content_only\'))));
	  if (_THEME_NAME_ == \'prestashop_mobile\') { $smarty->display(_PS_THEME_DIR_.\'footer-page.tpl\'); $smarty->assign(\'no_footer\', 1); }
	  $smarty->display(_PS_THEME_DIR_.\'footer.tpl\'); }';
			else
				$new_content = '<?php $controller = new FrontController();'."\n".'/* PrestaShop Mobile */ if (_THEME_NAME_ == \'prestashop_mobile\') { global $smarty; $smarty->display(_PS_THEME_DIR_.\'footer-page.tpl\'); $smarty->assign(\'no_footer\', 1); }'."\n".'$controller->displayFooter();';
		}
		else
		{
			$new_content = '';
			foreach ($content as $line)
				if (strstr($line, '/* PrestaShop Mobile */') === false)
					$new_content .= $line;
		}

		$ret &= (bool)file_put_contents(_PS_ROOT_DIR_.'/footer.php', $new_content);

		return $ret;
	}

	/**
	 * @brief Copy the mobile theme into the PrestaShop theme directory
	 *
	 * @param string $install Flag whether we install or uninstall.
	 *
	 * @return boolean Success or Failure
	 */
	function installTheme($install = true)
	{
		/* During install, check if a theme with the same name already exists */
		if ($install && file_exists(_PS_ALL_THEMES_DIR_.'prestashop_mobile'))
			$this->_errors[] = $this->l('Error: Your "themes" directory is not writable or the theme "prestashop_mobile" already exists, please change the permissions on this file or remove/rename the "prestashop_mobile" theme.');

		/* Check that the settings file is writable */
		if (($install && !is_writable(_PS_ALL_THEMES_DIR_)) || (!$install && file_exists(_PS_ALL_THEMES_DIR_.'prestashop_mobile') && !is_writable(_PS_ALL_THEMES_DIR_.'prestashop_mobile')))
			$this->_errors[] = $this->l('Error: Your "themes" directory is not writable or the theme "prestashop_mobile" already exists, please change the permissions on this file or remove/rename the "prestashop_mobile" theme.');

		if ($this->_errors && count($this->_errors))
		{
			echo $this->displayError(implode('<br />', $this->_errors));
			return false;
		}

		return $install ? self::copy_recursive(dirname(__FILE__).'/prestashop_mobile', _PS_ALL_THEMES_DIR_.'prestashop_mobile') : self::rmdir_recursive(_PS_ALL_THEMES_DIR_.'prestashop_mobile');
	}

	/**
	 * @brief Check if user should be redirected to a specific site.
	 *
	 * @param array params Params array from hookHeader()
	 * @param string site_type Type of site to be redirected (allowed value: 'ps_full_site' and 'ps_mobile_site'
	 * @param boolean addjs_exists whether Tools::addJS() exists or not
	 *
	 */
	protected function _redirectSite($params, $site_type, $addjs_exists = true)
	{
		if ($site_type != 'ps_full_site' && $site_type != 'ps_mobile_site')
			return ;

		global $js_files;

		// Make sure order-opc is well redirected to order
		if (_THEME_NAME_ == 'prestashop_mobile' && $site_type == 'ps_mobile_site' && strpos($_SERVER['PHP_SELF'], 'order-opc.php') !== false)
		{
			global $link;

			$dest = $link->getPageLink('order.php', true);

			header('HTTP/1.0 302 Moved');
			header('Location: '.$dest.(strpos($dest, '?') !== false ? '&' : '?').'ps_mobile_site=1'.(isset($_GET['mobile_iframe']) ? '&mobile_iframe=1' : ''));
			exit;
		}


		$site_type_cookie = (int)($site_type == 'ps_full_site');
		if (isset($params['cookie']->full_site) && $params['cookie']->full_site == $site_type_cookie && !isset($_GET['ps_mobile_site']) && !isset($_GET['ps_full_site']))
		{
			$dest = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

			header('HTTP/1.0 302 Moved');
			header('Location: http://'.$dest.(strpos($dest, '?') !== false ? '&' : '?').$site_type.'=1'.(isset($_GET['mobile_iframe']) ? '&mobile_iframe=1' : ''));
			exit;
		}

		if (isset($_GET['mobile_iframe']))
			unset($params['cookie']->full_site);

		if ($site_type == 'ps_mobile_site' && !isset($_GET['ps_mobile_site']) && !isset($_GET['ps_full_site']))
		{
			if ($addjs_exists)
				Tools::addJS(__PS_BASE_URI__.'modules/mobile_theme/iframe_redirect.js');
			else
				array_push($js_files, __PS_BASE_URI__.'modules/mobile_theme/mobile_iframe.js');
		}

		if ($site_type == 'ps_full_site' && isset($_GET['mobile_iframe']))
		{
			if ($addjs_exists)
				Tools::addJS(__PS_BASE_URI__.'modules/mobile_theme/mobile_iframe.js');
			else
				array_push($js_files, __PS_BASE_URI__.'modules/mobile_theme/mobile_iframe.js');
		}
	}

	/**
	 * @brief Init the Cookie for scpecific site and rewrite all links.
	 *
	 * @param array $params params of the HookHeader()
	 * @param boolean $addjs_exists whether Tools::addJS() exists or not
	 *
	 */
	protected static function _initForceSite($params, $addjs_exists = true)
	{
		global $js_files, $smarty;

		if (!isset($js_files))
			$js_files = array();

		if (isset($_GET['ps_full_site']) && $_GET['ps_full_site'] == 1)
		{
			if ($addjs_exists)
				Tools::addJS(__PS_BASE_URI__.'modules/mobile_theme/full_site.js');
			else
			{
				array_push($js_files, __PS_BASE_URI__.'modules/mobile_theme/full_site.js');
				$smarty->assign('js_files_mobile', $js_files);
			}
			$params['cookie']->full_site = 1;
		}
		elseif (isset($_GET['ps_mobile_site']) && $_GET['ps_mobile_site'] == 1)
		{
			if ($addjs_exists)
				Tools::addJS(__PS_BASE_URI__.'modules/mobile_theme/mobile_site.js');
			else
			{
				array_push($js_files, __PS_BASE_URI__.'modules/mobile_theme/mobile_site.js');
				$smarty->assign('js_files_mobile', $js_files);
			}

			$params['cookie']->full_site = 0;
		}
	}

	/**
	 * @brief Remove former verison of JQuery
	 *
	 * Search for former version of JQuery and unset them in order to include the last one.
	 *
	 */
	protected static function _removeJQuery()
	{
		global $js_files;

		// Remove Jquery from the js list file, will be included in the template (also remove thickbox in 1.3)
		$jquery_names = array('jquery.js', 'jquery.pack.js', 'jquery.min.js', 'jquery-1.2.6.pack.js', 'jquery-1.4.4.min.js', 'jquery-1.6.2.min.js', 'jquery-1.7.2.min.js', 'thickbox-modified.js');
		foreach ($js_files as $k => $f)
			if (in_array(basename($f), $jquery_names))
				unset($js_files[$k]);
	}

	/**
	 * @brief Disable the module
	 *
	 * When disabling the module, we need to edit the settings to also disable the theme swtich.
	 * When enabling it, the settings will be rewritten automatically with the performances system.
	 *
	 */
	public function disable()
	{
		Configuration::updateValue('PS_MOBILE_MODULE_ENABLED', 0);
		$this->editSettings(false);
		return parent::disable();
	}

	public function hookBackOfficeTop($params)
	{
		// If the module as been disabled (manually or via performances update) rewrite the settings
		if (!Configuration::get('PS_MOBILE_MODULE_ENABLED'))
		{
			$this->editSettings(false);
			$this->editSettings(true, array('mobile_domain' => Configuration::get('PS_MOBILE_DOMAIN'), 'device' => (int)Configuration::get('PS_MOBILE_DEVICE')));
		}

		if (!defined('_PS_MOBILE_'))
			Configuration::updateValue('PS_MOBILE_MODULE_ENABLED', 0);

		// Helper function for the performance/db tab
		$this->_html .= '<script type="text/javascript">function addEditSettingsInput() { return \'<input type="hidden" name="ps_disable_mobile" value="1" id="ps_disable_mobile" />\'; }</script>'."\n";

		// Make sure we have JQuery
		if (version_compare(_PS_VERSION_, '1.0', '<'))
			$this->_html .= '<script type="text/javascript" src="'.__PS_BASE_URI__.'themes/prestashop_mobile/js/jquery.min.js"></script>';

		// Display warning message if the module is disabled (This occurs only in case of failure)
		if (!Configuration::get('PS_MOBILE_MODULE_ENABLED'))
			$this->_html .= '<script type="text/javascript">
				$(function() {
					$(\'.path_bar\').after(function() {
						return \'<div class="warn"><img src="../img/admin/warn2.png" alt="">'.$this->l('The Mobile Theme has been disabled').
						', <a href="index.php?tab=AdminModules&configure=mobile_theme&token='.Tools::getAdminTokenLite('AdminModules').
						'&ps_reenable_mobile=1">'.$this->l('click here to re-enable it').'</a></div>\';
					});
				});
				</script>';

		// If a form has been submitted with the 'ps_disable_mobile' param, revert the settings
		if (Tools::isSubmit('ps_disable_mobile'))
		{
			Configuration::updateValue('PS_MOBILE_MODULE_ENABLED', 0);
			$this->editSettings(false);
		}

		// Make sure to uninstall the module before deleting it
		if (Tools::isSubmit('ps_delete_mobile') && Tools::getValue('token') == Tools::getAdminTokenLite('AdminModules'))
		{
			$this->uninstall();
			$this->_html .= '<script type="text/javascript">
				$(function() {
								var tmp_url = $(\'#modgo_mobile_theme .action_module_delete\').attr(\'href\');
								setTimeout(function() {
												location.replace(tmp_url);
								}, 1000);
				 });</script>';
		}
		$this->_html .= '<script type="text/javascript">
		 		$(function() {
								$(\'#modgo_mobile_theme .action_module_delete\').attr(\'href\', window.location.href + \'&ps_delete_mobile\');
				});
				</script>';

		// Make sure to rewrite the settings with performaces and database tab. (add ps_disable_mobile params to the forms)
		if (isset($_GET['tab']) && $_GET['tab'] == 'AdminDb')
			$this->_html .= '<script type="text/javascript">
				$(function() {
					$(\'form\').find(\'input[name="db_server"]\').after(addEditSettingsInput);
				});
				</script>';
		elseif (isset($_GET['tab']) && $_GET['tab'] == 'AdminMeta')
			$this->_html .= '<script type="text/javascript">
				$(function() {
					$(\'form\').find(\'input[name="__PS_BASE_URI__"]\').after(addEditSettingsInput);
				});
				</script>';
		elseif (isset($_GET['tab']) && $_GET['tab'] == 'AdminPerformance')
			$this->_html .= '<script type="text/javascript">
				$(function() {
					$(\'form\').find(\'input[name="memcachedIp"]\').after(addEditSettingsInput);
					$(\'#PS_CIPHER_ALGORITHM_1, #caching_system, #_MEDIA_SERVER_1_\').after(addEditSettingsInput);
				});
				</script>';

		return $this->_html;
	}

	public function hookHeader($params)
	{
		global $js_files, $css_files, $smarty;

		$addjs_exists = method_exists('Tools', 'addJS');

		// Check if URL contain specific site data and change the site if needed
		self::_initForceSite($params, $addjs_exists);

		// If forced mobile site, need to make sure PrestaShop redirect well on mobile site
		$this->_redirectSite($params, 'ps_mobile_site', $addjs_exists);

		// If the theme is not the mobile one, we just stop here.
		if (_THEME_NAME_ != 'prestashop_mobile')
			return;

		// Load the hookMobileHeader for the registered modules
		$modules = Db::getInstance()->ExecuteS('
		SELECT `name`
		FROM `'._DB_PREFIX_.'module` m
		LEFT JOIN `'._DB_PREFIX_.'hook_module` hm ON (hm.id_module = m.id_module)
		WHERE hm.`id_hook` = '.(int)Configuration::get('PS_MOBILE_HOOK_HEADER_ID'));
		foreach ($modules as $m)
		{
			$tmp = Module::getInstanceByName($m['name']);
			$this->_html .= $tmp->hookDisplayMobileHeader();
		}

		// If forced full site, need to make sure PrestaShop redirect well on full site
		$this->_redirectSite($params, 'ps_full_site', $addjs_exists);

		self::_removeJQuery();
		if (strpos($_SERVER['PHP_SELF'], 'order.php') !== false)
		{
			$addr = new Address((int)$params['cart']->id_address_invoice);
			if (Validate::isLoadedObject($addr))
				if (in_array(Country::getIsoById((int)$addr->id_country), array('FR', 'PL', 'IT', 'ES')))
					array_push($js_files, _THEME_DIR_.'js/payment.js');
		}

		// Create a new Array of all JS and append the old one to it (Important for JQuery)
		$js_files_mobile = array_unique(array_merge(array(_THEME_DIR_.'js/jquery.min.js', _THEME_DIR_.'js/jquery.mobile.min.js', _THEME_DIR_.'js/global.js',
		 _THEME_DIR_.'js/swipe.js', _THEME_DIR_.'js/product.js', _THEME_DIR_.'js/tools.js', _THEME_DIR_.'js/order-address.js', _THEME_DIR_.'js/statesManagement.js'), $js_files));

		// Empty the former js files
		$js_files = array();

		if ($addjs_exists) // Consider that if Tools::addJS() exists, then Tools::addCSS() too.
		{
			Tools::addJS($js_files_mobile);
			Tools::addCSS(_THEME_DIR_.'css/jquery.mobile.min.css');
		}
		else
			$css_files[_THEME_DIR_.'css/jquery.mobile.min.css'] = 'all';

		$smarty->assign(array('mobile_theme_phone' => _PS_MOBILE_PHONE_, 'mobile_theme_tablet' => _PS_MOBILE_TABLET_,
		'js_files_mobile' => $js_files_mobile, 'css_files_mobile' => $css_files));

		/* Template Styles */
		$smarty->assign('ps_mobile_styles', Configuration::getMultiple(array('PS_MOBILE_THEME_HEADINGS', 'PS_MOBILE_THEME_FILTERING_BAR',
		'PS_MOBILE_THEME_PROCESS_BAR', 'PS_MOBILE_THEME_CONF_MSG', 'PS_MOBILE_THEME_ERROR_MSG', 'PS_MOBILE_THEME_LIST_HEADERS',
		'PS_MOBILE_THEME_BUTTONS', 'PS_MOBILE_THEME_HEADER_FOOTER')));


		$paypal = Module::getInstanceByName('paypal');
		if ($paypal && $paypal->active && version_compare($paypal->version, '3.2.0', '>='))
		{
			if (strpos($_SERVER['PHP_SELF'], 'product.php') !== false)
				$smarty->assign('paypal_product', $paypal->renderExpressCheckoutButton('product').$paypal->renderExpressCheckoutForm('product'));
			if (strpos($_SERVER['PHP_SELF'], 'order.php') !== false || strpos($_SERVER['PHP_SELF'], '.php') !== false)
				$smarty->assign('paypal_cart', $paypal->renderExpressCheckoutButton('cart').$paypal->renderExpressCheckoutForm('cart'));
		}

		// Display/assign specific content for pages
		self::_pageStore($params);

		// Add translation of JS message for the payment page
		if (strpos($_SERVER['PHP_SELF'], 'order.php') !== false)
			$smarty->assign('translate_nopayment', '<script type="text/javascript">var translate_nopaymentmodule = "'.$this->l('Sorry, no payment module is available in your country.').'";</script>');

		// The hookHeader is not called from the mobile template. Affect $smarty->HOOK_HEADER_MOBILE instead
		$smarty->assign('HOOK_HEADER_MOBILE', $this->_html);
	}

	/**
	 * @brief Assign missing smarty templates for Store page
	 *
	 * @param array $params params array from hookHeader
	 *
	 */
	protected static function _pageStore($params)
	{
		if (strpos($_SERVER['PHP_SELF'], 'stores.php') !== false)
		{
			global $smarty;

			Configuration::set('PS_STORES_SIMPLIFIED', 1);

			$stores = Db::getInstance()->ExecuteS('
			SELECT s.*, cl.`name` country, st.`iso_code` state
			FROM `'._DB_PREFIX_.'store` s
			LEFT JOIN `'._DB_PREFIX_.'country_lang` cl ON (cl.`id_country` = s.`id_country`)
			LEFT JOIN `'._DB_PREFIX_.'state` st ON (st.`id_state` = s.`id_state`)
			WHERE s.`active` = 1 AND cl.`id_lang` = '.(int)$params['cookie']->id_lang);

			foreach ($stores as &$store)
				$store['has_picture'] = file_exists(_PS_STORE_IMG_DIR_.(int)$store['id_store'].'.jpg');

			$smarty->assign('stores', $stores);
		}
	}

	public function hookFooter($params)
	{
		if ($params['cookie']->full_site == 1)
			return '<p style="text-align: center; margin: 10px auto;"><a id="ps_mobile_site" rel="external" href="'.(Configuration::get('PS_REDIRECT_MOBILE_DOMAIN') ? 'http://'.Configuration::get('PS_MOBILE_DOMAIN') : __PS_BASE_URI__.'?ps_mobile_site=1').'" style="text-decoration: none;">'.$this->l('View mobile site').'</a></p>';
	}

	public function hookHome($params)
	{
		if (_THEME_NAME_ != 'prestashop_mobile')
			return;

		global $smarty, $link;

		$id_customer = (int)($params['cookie']->id_customer);
		$id_lang = (int)$params['cookie']->id_lang;
		$groups = $id_customer ? implode(', ', Customer::getGroupsStatic($id_customer)) : (int)_PS_DEFAULT_CUSTOMER_GROUP_;

		$maxdepth = Configuration::get('BLOCK_CATEG_MAX_DEPTH');
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
			SELECT c.`id_parent`, c.`id_category`, cl.`name`, cl.`description` as `desc`, cl.`link_rewrite`
			FROM `'._DB_PREFIX_.'category` c
			LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (c.`id_category` = cl.`id_category` AND `id_lang` = '.(int)$id_lang.')
			LEFT JOIN `'._DB_PREFIX_.'category_group` cg ON (cg.`id_category` = c.`id_category`)
			WHERE (c.`active` = 1 AND c.`id_parent` = 1)
			'.((int)($maxdepth) != 0 ? ' AND `level_depth` <= '.(int)($maxdepth) : '').'
			AND cg.`id_group` IN ('.pSQL($groups).')
			GROUP BY id_category
			ORDER BY `level_depth` ASC, '.(Configuration::get('BLOCK_CATEG_SORT') ? 'cl.`name`' : 'c.`position`').' '.(Configuration::get('BLOCK_CATEG_SORT_WAY') ? 'DESC' : 'ASC'));

		if ($result)
		{
			foreach ($result as &$r)
				$r['link'] = $link->getCategoryLink((int)$r['id_category'], $r['link_rewrite']);

			$smarty->assign('block_category_mobile', $result);
		}

		$smarty->assign('meta_title', Configuration::get('PS_SHOP_NAME'));
	}

	public function displayConf()
	{
		echo '
		<div class="conf confirm" style="margin-bottom: 25px;">
			<img src="../img/admin/ok.gif" alt="" />
			'.$this->l('Settings updated').'
		</div>';
	}

	public function getContent()
	{
		if (!Configuration::get('PS_MOBILE_MODULE_ENABLED') || isset($_GET['ps_reenable_mobile']))
		{
			$this->editSettings(false);
			$this->editSettings(true, array('mobile_domain' => Configuration::get('PS_MOBILE_DOMAIN'), 'device' => (int)Configuration::get('PS_MOBILE_DEVICE')));
		}

		if (Tools::isSubmit('SubmitMobile'))
		{
			Configuration::updateValue('PS_MOBILE_THEME_HEADINGS', $_POST['mobile_color_8']);
			Configuration::updateValue('PS_MOBILE_THEME_FILTERING_BAR', $_POST['mobile_color_7']);
			Configuration::updateValue('PS_MOBILE_THEME_PROCESS_BAR', $_POST['mobile_color_6']);
			Configuration::updateValue('PS_MOBILE_THEME_CONF_MSG', $_POST['mobile_color_5']);
			Configuration::updateValue('PS_MOBILE_THEME_ERROR_MSG', $_POST['mobile_color_4']);
			Configuration::updateValue('PS_MOBILE_THEME_LIST_HEADERS', $_POST['mobile_color_3']);
			Configuration::updateValue('PS_MOBILE_THEME_BUTTONS', $_POST['mobile_color_2']);
			Configuration::updateValue('PS_MOBILE_THEME_HEADER_FOOTER', $_POST['mobile_color_1']);

			$this->displayConf();
		}
		elseif (Tools::isSubmit('SubmitMobileSettings'))
		{
			Configuration::updateValue('PS_MOBILE_DOMAIN', $_POST['mobile_domain']);

			$this->editSettings(false);
			$this->editSettings(true, array('mobile_domain' => Tools::safeOutput($_POST['mobile_domain']), 'device' => (int)$_POST['mobile_device']));
			Configuration::updateValue('PS_MOBILE_DEVICE', (int)$_POST['mobile_device']);
			Configuration::updateValue('PS_REDIRECT_MOBILE_DOMAIN', (int)$_POST['redirect_domain']);
			$this->displayConf();
		}

		$this->_html .= '
		<h2>'.$this->l('PrestaShop Mobile Template').'</h2>
		<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post">
		<fieldset style="margin-top: 10px; width: 900px;">
			<legend><img src="'.$this->_path.'/logo.gif" alt="" />'.$this->l('Customize your template').'</legend>
			<div style="float: left; width: 500px; padding-top: 15px;">';

		$elements = array(
		array('name' => $this->l('Header and Footer Background'), 'start_y' => -20, 'height' => 44, 'y_space' => 0.50, 'margin_div' => -17, 'conf_key' => 'PS_MOBILE_THEME_HEADER_FOOTER'),
		array('name' => $this->l('Action Buttons'), 'start_y' => -266, 'height' => 50, 'y_space' => 0.50, 'margin_div' => -20, 'conf_key' => 'PS_MOBILE_THEME_BUTTONS'),
		array('name' => $this->l('List Headers Background'), 'start_y' => -526, 'height' => 87, 'y_space' => 5.5, 'margin_div' => -38, 'conf_key' => 'PS_MOBILE_THEME_LIST_HEADERS'),
		array('name' => $this->l('Error messages'), 'start_y' => -1017, 'height' => 64, 'y_space' => 0, 'margin_div' => -23, 'conf_key' => 'PS_MOBILE_THEME_ERROR_MSG'),
		array('name' => $this->l('Confirmation Messages'), 'start_y' => -1359, 'height' => 64, 'y_space' => 0, 'margin_div' => -23, 'conf_key' => 'PS_MOBILE_THEME_CONF_MSG'),
		array('name' => $this->l('Checkout Process Bar'), 'start_y' => -1710, 'height' => 58, 'y_space' => 4, 'margin_div' => -28, 'conf_key' => 'PS_MOBILE_THEME_PROCESS_BAR'),
		array('name' => $this->l('Product Filtering Bar'), 'start_y' => -2035, 'height' => 41, 'y_space' => 21, 'margin_div' => -15, 'conf_key' => 'PS_MOBILE_THEME_FILTERING_BAR'),
		array('name' => $this->l('Headings Background'), 'start_y' => -2339, 'height' => 132, 'y_space' => 20, 'margin_div' => -60, 'conf_key' => 'PS_MOBILE_THEME_HEADINGS'));

		$i = 1;
		foreach ($elements as $element)
		{
			$configuration_value = Configuration::get($element['conf_key']);

			$this->_html .= '
			<h3 style="margin-bottom: 5px;">'.$element['name'].'</h3>
			<select class="mobile_color" style="width: 110px;" name="mobile_color_'.(int)$i.'" onchange="$(\'#color_sample_'.(int)$i.'\').css(\'background-position\', \'-5px \'+(-1 * $(this).find(\'option:selected\').attr(\'rel\')+'.(int)($element['start_y']).')+\'px\');">
				<option value="a"'.($configuration_value == 'a' ? ' selected="selected"' : '').' rel="0">'.$this->l('Theme').' A</option>
				<option value="b"'.($configuration_value == 'b' ? ' selected="selected"' : '').' rel="'.(int)($element['height'] + $element['y_space']).'">'.$this->l('Theme').' B</option>
				<option value="c"'.($configuration_value == 'c' ? ' selected="selected"' : '').' rel="'.(int)(($element['height'] + $element['y_space']) * 2).'">'.$this->l('Theme').' C</option>
				<option value="d"'.($configuration_value == 'd' ? ' selected="selected"' : '').' rel="'.(int)(($element['height'] + $element['y_space']) * 3).'">'.$this->l('Theme').' D</option>
				<option value="e"'.($configuration_value == 'e' ? ' selected="selected"' : '').' rel="'.(int)(($element['height'] + $element['y_space']) * 4).'">'.$this->l('Theme').' E</option>
			</select>
			<div id="color_sample_'.(int)$i.'" style="background: url(\''.__PS_BASE_URI__.'modules/'.$this->name.'/jqm-sprite.png\') -5px '.(int)$element['start_y'].'px no-repeat; width: 355px; height: '.(int)$element['height'].'px; display: inline-block; margin-bottom: '.(int)$element['margin_div'].'px; margin-left: 5px;"></div><br /><br />
			<br />';
			$i++;
		}

		$this->_html .= '
			<br /><script type="text/javascript">$(\'select.mobile_color\').change();</script></div>
			<div style="float: right; margin-top: -70px; padding-left: 34px; padding-top: 143px; background: url('.__PS_BASE_URI__.'modules/'.$this->name.'/iphone-bg.png) no-repeat; width: 350px; height: 615px;">
				<iframe id="mobile_iframe" src="'.__PS_BASE_URI__.'?ps_mobile_site=1&mobile_iframe=1" frameborder="0" width="320" height="459" marginheight="0" marginwidth="0" scrolling="auto"></iframe>
			</div>
				<div style="margin: 0 auto; text-align: center;">
					<p><input type="submit" name="SubmitMobile" class="button" style="font-size: 20px; padding: 15px 25px;" value="'.$this->l('Save changes').'" /></p>
				</div>
				<br class="clear" />
		</fieldset>
		</form>
		<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post">
		<fieldset style="margin-top: 30px; width: 500px;">
			<legend><img src="'.$this->_path.'/logo.gif" alt="" />'.$this->l('Other settings').'</legend>
			<label for="mobile_device">'.$this->l('Enable the Mobile Template for').'</label>
			<div class="margin-form">
				<select id="mobile_device" name="mobile_device">
					<option value="0"'.(Configuration::get('PS_MOBILE_DEVICE') == 0 ? ' selected="selected"' : '').'>'.$this->l('Mobile phones only').'</option>
					<option value="1"'.(Configuration::get('PS_MOBILE_DEVICE') == 1 ? ' selected="selected"' : '').'>'.$this->l('Tablets only').'</option>
					<option value="2"'.(Configuration::get('PS_MOBILE_DEVICE') == 2 ? ' selected="selected"' : '').'>'.$this->l('Both').'</option>
				</select>
			</div><br class="clear" />
			<label for="mobile_domain">'.$this->l('Your Mobile sub-domain (optional)').'</label>
			<div class="margin-form">
				<input type="text" name="mobile_domain" id="mobile_domain" value="'.Configuration::get('PS_MOBILE_DOMAIN').'" style="width: 250px;" />
				<p class="clear">'.$this->l('Example: m.myshop.com').'</p>
			</div>
			<label for="redirect_domain">'.$this->l('Use this domain for Mobile users').'</label>
			<div class="margin-form">
					<input type="radio" name="redirect_domain" value="1" style="vertical-align: middle;"'.(Tools::getValue('redirect_domain', Configuration::get('PS_REDIRECT_MOBILE_DOMAIN')) ? 'checked="checked"' : '').' />
					<span>'.$this->l('Yes').'</span>
					<input type="radio" name="redirect_domain" value="0" style="vertical-align: middle;"'.(!Tools::getValue('redirect_domain', Configuration::get('PS_REDIRECT_MOBILE_DOMAIN')) ? 'checked="checked"' : '').' />
					<span>'.$this->l('No').'</span>
			</div><br class="clear" />
			<center><input type="submit" class="button" name="SubmitMobileSettings" value="'.$this->l('   Save   ').'" /></center>
		</fieldset>
		</form><br />';

		return $this->_html;
	}


	/**
	 * @brief Utils in order to perform `rm -r`
	 *
	 * @param string $dir Directory to be removed
	 *
	 * @return boolean Success or Failure
	 */
	public static function rmdir_recursive($dir)
	{
		if (!file_exists($dir))
			return true;

		if (is_dir($dir))
		{
			$r = true;
			foreach (scandir($dir) as $file)
				if ($file != '.' && $file != '..')
					$r &= self::rmdir_recursive($dir.'/'.$file);
			return $r && rmdir($dir);
		}
		return (bool)@unlink($dir);
	}

	/**
	 * @brief Utils in order to perform `cp -r`
	 *
	 * @param string $src Source directory path
	 * @param string $dst Destination path
	 *
	 * @return boolean Success or Failure
	 */
	public static function copy_recursive($src, $dst)
	{
		self::rmdir_recursive($dst);
		if (is_dir($src))
		{
			$r = mkdir($dst, 0777, true);
			foreach (scandir($src) as $file)
				if ($file != '.' && $file != '..')
					$r &= self::copy_recursive($src.'/'.$file, $dst.'/'.$file);
			return $r;
		}
		return ((bool)@copy($src, $dst)) && (bool)@chmod($dst, 0777);
	}
}
