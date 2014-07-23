<?php
/**
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2014 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_'))
	exit;

class Prestastats extends Module
{
	public function __construct()
	{
		$this->name = 'prestastats';
		$this->tab = 'analytics_stats';
		$this->version = '2.1.2';
		$this->author = 'CDL Software Ltd.';
		$this->need_instance = 0;
		$this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l('PrestaStats - Analytics Dashboard and Abandoned Carts');
		$this->description = $this->l('PrestaStats is the leading Business Analytics
						Dashboard & Abandoned Carts Monitor enabling you to see business critical data from your PrestaShop store on any device, at any time.');
		$this->confirmUninstall = $this->l('Uninstall');
		/* Backward compatibility */
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
	}

	private function getErrorFields()
	{
		$error_fields = array();

		$api      =	Configuration::get('PS_PRESTASTATS_API_KEY');
		$username = Configuration::get('PS_PRESTASTATS_USER_LOGIN');
		$password = Configuration::get('PS_PRESTASTATS_USER_PASSWORD');

		if (!(ctype_alnum($api)) || Tools::strlen($api) > 32)
			$error_fields['api_error'] = $this->l('API value is not correct');

		if (!($username) || Tools::strlen($api) > 32)
			$error_fields['username_error'] = $this->l('Username value is not correct');

		if (!(ctype_alnum($password)) || Tools::strlen($api) > 32)
			$error_fields['password_error'] = $this->l('Password value is not correct');

		return $error_fields;
	}

	private function verifyConfiguration()
	{
		return !(ctype_alnum(Configuration::get('PS_PRESTASTATS_API_KEY')) && Configuration::get('PS_PRESTASTATS_USER_LOGIN')
			&& ctype_alnum(Configuration::get('PS_PRESTASTATS_USER_PASSWORD')))? 0 : 1;
	}

	private function baseUrl()
	{
		return 'https://my.prestastats.com/registerApi?';
	}

	private function loginUrl()
	{
		return 'https://my.prestastats.com/users/login';
	}

	private function shopName()
	{
		$shop_name = Db::getInstance()->executeS('SELECT value FROM '._DB_PREFIX_.'configuration WHERE name="PS_SHOP_NAME"');
		return isset($shop_name[0]['value']) ?  $shop_name[0]['value'] : '';
	}

	public function getContent()
	{
		/******************************************************
		 *               Module interface                     *
		 *                                                    *
		 *****************************************************/
		$redirect_url = '';
		$login_url = '';

		//assigning saved registered information
		$username = Tools::safeOutput(Configuration::get('PS_PRESTASTATS_USER_LOGIN'));
		if ($username != '')
		{
			$api = Tools::safeOutput(Configuration::get('PS_PRESTASTATS_API_KEY'));
					$password = Tools::safeOutput(Configuration::get('PS_PRESTASTATS_USER_PASSWORD'));
			$url = Tools::getHttpHost(true).__PS_BASE_URI__;

			if (version_compare(_PS_VERSION_, '1.4.11.0', '='))
				$name = $this->shopName();
			else
				$name = $this->context->shop->name;

			$r_url = 'api='.$api.'&username='.$username.
						'&password='.$password.'&shopurl='.$url.
						'&shopname='.$name;
			$redirect_url = $this->baseUrl().$r_url;
			$login_url = $this->loginUrl();
		}

		$output  = '<div class="prestastats-wrap bootstrap">';
		$details = $this->getDetails();
		$prestastats_link = '';
		if (Tools::isSubmit('submit'.$this->name))
		{
			$output .= $this->displayConfirmation($this->l('Settings saved'));
			Configuration::updateValue('PS_PRESTASTATS_API_KEY', pSQL(Tools::strtoupper(Tools::getValue('PRESTASTATS_api'))));
			Configuration::updateValue('PS_PRESTASTATS_USER_LOGIN', pSQL(Tools::strtolower(Tools::getValue('PRESTASTATS_user'))));
			Configuration::updateValue('PS_PRESTASTATS_USER_PASSWORD', pSQL(Tools::strtolower(Tools::getValue('PRESTASTATS_password'))));

			$api = Tools::safeOutput(Configuration::get('PS_PRESTASTATS_API_KEY'));
			$username = Tools::safeOutput(Configuration::get('PS_PRESTASTATS_USER_LOGIN'));
			$password = Tools::safeOutput(Configuration::get('PS_PRESTASTATS_USER_PASSWORD'));
			$url = Tools::getHttpHost(true).__PS_BASE_URI__;

			if (version_compare(_PS_VERSION_, '1.4.11.0', '='))
				$name = $this->shopName();
			else
				$name = $this->context->shop->name;

			$r_url = 'api='.$api.'&username='.$username.
						'&password='.$password.'&shopurl='.$url.
						'&shopname='.$name;
			$redirect_url = $this->baseUrl().$r_url;
			$login_url = $this->loginUrl();

		}

		if ($this->verifyConfiguration())
		{
			Configuration::updateValue('PS_PRESTASTATS_CONFIGURED', 1);
			Configuration::updateValue('PRESTASTATS_CONFIGURATION_OK', true);
		}
		elseif (isset($api))
		{
			Configuration::updateValue('PS_PRESTASTATS_CONFIGURED', 0);
			$output  = '<ul class="error_list">';
			foreach ($this->getErrorFields() as $i => $text)
				$output .= '<li class="error_item_'.$i.' error_msg">'.$text.'</li>';
			$output .= '</ul>';
			$output  = $this->displayError($this->l('Invalid_config_data').'<br/ >'.$output);
		}
		// close div wrap
		$wrap  = '</div>';
		if (version_compare(_PS_VERSION_, '1.4.11.0', '<'))
			return $this->versionCheck();
		else
			return $output.$details.$this->displayForm().$this->showReg($redirect_url, $login_url, $username).$prestastats_link.$wrap;
	}

	public function showReg($url, $lurl, $uname)
	{
		// display registration form
		$disabled = '';
		if ($uname == '')
			$disabled = 'in-active';

		$this->context->smarty->assign(array('disabled' => $disabled, 'url' => $url,
												'lurl' => $lurl
										));
		return $this->display(__FILE__, 'views/templates/admin/registration_form.tpl');
	}

	public function versionCheck()
	{
		return $this->l('Please update your pretashop version.');
	}

	public function getDetails()
	{
		// display information about Prestastats
		$this->context->smarty->assign('path_url', $this->_path);

		if (version_compare( Tools::substr(_PS_VERSION_, 0, 3), '1.5', '<='))
		{
			if (version_compare(_PS_VERSION_, '1.4.11.0', '='))
			{
				echo '<link href="'.$this->_path.'css/prestastats-1-5-later.css" rel="stylesheet" type="text/css">';
				echo '<script src="'.$this->_path.'js/random-key-generator.js" type="text/javascript"></script>';
			}
			else
				$this->context->controller->addCSS(($this->_path).'css/prestastats-1-5-later.css', 'all');
		}
		else
			$this->context->controller->addCSS(($this->_path).'css/prestastats-1-6.css', 'all');
		try{
			$this->context->controller->addJs(($this->_path).'js/random-key-generator.js', 'all');
		}
		catch (Exception $e)
		{
			echo 'Caught exception: ',  $e->getMessage();
		}
		return $this->display(__FILE__, 'views/templates/admin/details_block.tpl');
	}

	public function displayForm()
	{
		$this->context->smarty->assign(array('dfl' => array('action' => $_SERVER['REQUEST_URI']),
												'api' => Tools::safeOutput(Configuration::get('PS_PRESTASTATS_API_KEY')),
												'username' => Tools::safeOutput(Configuration::get('PS_PRESTASTATS_USER_LOGIN')),
												'password' => Tools::safeOutput(Configuration::get('PS_PRESTASTATS_USER_PASSWORD'))
										));

		return $this->display(__FILE__, 'views/templates/admin/display_form.tpl');
	}

	public function install()
	{
		return (parent::install() == false
			|| Configuration::updateValue('PS_PRESTASTATS_CONFIGURED', 0) == false
			|| Configuration::updateValue('PS_PRESTASTATS_VERSION', 1.0) == false)? false : true;
	}

	public function uninstall()
	{
		return (!parent::uninstall()
			|| !Configuration::deleteByName('PS_PRESTASTATS_CONFIGURED')
			|| !Configuration::deleteByName('PS_PRESTASTATS_VERSION')
			|| !Configuration::deleteByName('PS_PRESTASTATS_API_KEY')
			|| !Configuration::deleteByName('PS_PRESTASTATS_USER_LOGIN')
			|| !Configuration::deleteByName('PS_PRESTASTATS_USER_PASSWORD'))? false : true;
	}
}