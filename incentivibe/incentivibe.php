<?php
/*
*  @author Incentivibe <info@incentivibe.com>
*  @copyright  2012-2013 Incentivibe
*  @version  Release: $Revision: 1.1 $
*
*/

if (!defined('_PS_VERSION_'))
	exit;

class Incentivibe extends Module
{
	private $incentivibe_api = null;

	public function __construct()
	{
		$this->name = 'incentivibe';
		$this->tab = 'administration';
		$this->version = 1.1;
		$this->author = 'Incentivibe';
		$this->need_instance = 0;
		$this->is_configurable = 1;
		
		$this->dependencies = array();

		parent::__construct();

		$this->display_name = 'Incentivibe';
		$this->description = $this->l("Virally grow email subscribers & fans by offering your visitors $500 prizes for only $25. 
			How? Incentivibe groups businesses together so you can offer a big contest prize (e.g. $500 visa) on your website by sharing 
			a fraction of the prize cost (e.g. $25) with other businesses.");

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

		$this->iv_contest_token = htmlentities(Configuration::get('IV_CONTEST_TOKEN'), ENT_QUOTES, 'UTF-8');
		$this->iv_auth_token = htmlentities(Configuration::get('IV_AUTH_TOKEN'), ENT_QUOTES, 'UTF-8');

		$this->iv_login_failed = htmlentities(Configuration::get('LOGIN_FAILED'), ENT_QUOTES, 'UTF-8');

		if (!Configuration::get('INCENTIVIBE'))
			$this->warning = $this->l('No name provided');

		if (!Configuration::get('MYMODULE_NAME'))
			$this->warning = $this->l('No name provided');
	}

	public function incentivibeApi()
	{
		if (is_null($this->incentivibe_api))
		{
			include_once(_PS_ROOT_DIR_.$this->_path.'/classes/IncentivibeApi.php');
			$this->incentivibe_api = new IncentivibeApi($this->name);
		}
		return $this->incentivibe_api;
	}

	public function install()
	{
		if (parent::install() == false || !$this->registerHook('footer'))
			return false;

		if (!function_exists('curl_version'))
			return false;

		Configuration::updateValue('IV_AUTH_TOKEN', '');
		Configuration::updateValue('IV_CONTEST_TOKEN', '');

		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall())
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'incentivibe`');
		parent::uninstall();
	}

	public function getContent()
	{
		if (Tools::isSubmit('incentivibe_login'))
			$this->processLogin();
		elseif (Tools::isSubmit('incentivibe_signup'))
			$this->processSingup();

		$this->assignConstants();

		if (isset($this->iv_auth_token) && ($this->iv_auth_token))
			return $this->display(__FILE__, 'views/templates/admin/settings.tpl');
		return $this->display(__FILE__, 'views/templates/admin/admin.tpl');
	}

	private function processLogin()
	{
		$user_email = Tools::getValue('user_email');
		$password = Tools::getValue('user_password');
		$response = $this->makeApiCall(
			'login', array('user_login' => $user_email, 'password' => $password)
		);

		Configuration::updateValue('LOGIN_ATTEMPT', 'true');

		if ($this->processResponse('login', $response))
		{
			// show settings
			Configuration::updateValue('LOGIN_FAILED', '');
			$this->iv_login_failed = '';
		} else
			Configuration::updateValue('LOGIN_FAILED', 'true');
	}

	private function processSingup()
	{
		$user_email = Tools::getValue('user_email');
		$password = Tools::getValue('user_password');

		$user_full_name = Tools::getValue('user_full_name');
		$user_company_name = Tools::getValue('user_company_name');
		$user_platform_id = Tools::getValue('user_platform_id');
		$user_shop = Tools::getValue('user_shop');

		Configuration::updateValue('LOGIN_FAILED', '');
		$this->iv_login_failed = '';

		$response = $this->makeApiCall(
			'register', array(
					'user[email]' => $user_email,
					'user[password]' => $password,
					'user[shop]' => $user_shop,
					'user[full_name]' => $user_full_name,
					'user[company_name]' => $user_company_name,
					'user[platform_id]' => $user_platform_id,
					'user[currency]' => $this->context->currency->iso_code,
					'user[language]' => $this->context->language->iso_code,
					'user_contest[company_name]' => $user_company_name,
					'user_contest[giveaway_url]' => $user_shop
			)
		);

		$this->processResponse('register', $response);
	}

	private function makeApiCall($action, $params = array())
	{
		$default_params = array(
			'iv_currency' => $this->context->currency->iso_code,
			'iv_language' => $this->context->language->iso_code
		);
		$params = array_merge($params, $default_params);

		return $this->incentivibeApi()->makeApiCall($action, $params);
	}

	private function processResponse($action, $response)
	{
		if (isset($response['errors']) && !empty($response['errors']))
		{
			$errors = $response['errors'];
			$this->context->smarty->assign(array($action.'_errors' => $errors));
			return false;
		}
		else
		{
			if ($response['auth_token'])
			{
				Configuration::updateValue('IV_AUTH_TOKEN', $response['auth_token']);
				Configuration::updateValue('IV_CONTEST_TOKEN', $response['iv_token']);

				$this->iv_contest_token = $response['iv_token'];
				$this->iv_auth_token = $response['auth_token'];
			}

			return true;
		}
	}

	public function hookDisplayFooter($params)
	{
		$params_copy = $params;
		$this->context->smarty->assign(array(
			'currency_sign' => $this->context->currency->iso_code,
			'language_sign' => $this->context->language->iso_code,
			'iv_contest_token'	=> $this->iv_contest_token
		));

		return $this->display(__FILE__, 'views/templates/front/incentivibe.tpl');
	}

	private function assignConstants()
	{
		$this->context->controller->addCSS($this->_path.'css/bootstrap.min.css');
		$this->context->controller->addCSS($this->_path.'css/home.css');
		$this->context->controller->addCSS($this->_path.'css/styles.css');

		$this->context->smarty->assign(array(
			'incentivibe_form_link'=> 'index.php?tab=AdminModules&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
			'platform_id' => 11,
			'iv_login_failed' => $this->iv_login_failed,
			'iv_auth_token' => $this->iv_auth_token,
			'user_shop' => Tools::getShopDomain(true, true)
		));
	}

	private function assignsubmitcode()
	{
		if ($code_i = Tools::getValue('iv_code'))
		{
			Configuration::updateValue('IV_CONTEST_TOKEN', $code_i);
			$this->iv_contest_token = htmlentities($code_i, ENT_QUOTES, 'UTF-8');
		}
	}

	public function isPresent($params, &$smarty)
	{
		$smarty_copy = $smarty;
		return isset($params['val']) && !is_empty($params['val']);
	}
}
