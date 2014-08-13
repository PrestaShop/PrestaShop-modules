<?php
/**
* 2014 Affinity-Engine
*
* NOTICE OF LICENSE
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade AffinityItems to newer
* versions in the future.If you wish to customize AffinityItems for your
* needs please refer to http://www.affinity-engine.fr for more information.
*
*  @author    Affinity-Engine SARL <contact@affinity-engine.fr>
*  @copyright 2014 Affinity-Engine SARL
*  @license   http://www.gnu.org/licenses/gpl-2.0.txt GNU GPL Version 2 (GPLv2)
*  International Registered Trademark & Property of Affinity Engine SARL
*/

require_once('loader.php');

if (!defined('_PS_VERSION_'))
	exit;

if (!defined('_MYSQL_ENGINE_'))
	define('_MYSQL_ENGINE_', 'MyISAM');

class AffinityItems extends Module {

	public $category_synchronize;

	public $product_synchronize;

	public $cart_synchronize;

	public $order_synchronize;

	public $action_synchronize;

	public $aecookie;

	public static $hook_list = array('Home', 'Left', 'Right', 'Cart', 'Product', 'Search', 'Category');

	public static $crawler_list = 'Google|msnbot|Rambler|Yahoo|AbachoBOT|accoona|AcioRobot|ASPSeek|CocoCrawler|Dumbot|FAST-WebCrawler
	|GeonaBot|Gigabot|Lycos|MSRBOT|Scooter|AltaVista|IDBot|eStyle|Scrubby|bot';

	public function __construct()
	{
		$this->name = 'affinityitems';
		$this->tab = 'advertising_marketing';
		$this->version = '1.0.0';
		$this->author = 'Affinity Engine';
		parent::__construct();

		if (!extension_loaded('curl') || !ini_get('allow_url_fopen'))
		{
			if (!extension_loaded('curl') && !ini_get('allow_url_fopen'))
				$this->warning = $this->l('You must enable cURL extension and allow_url_fopen option on your server if you want to use this module.');
			else if (!extension_loaded('curl'))
				$this->warning = $this->l('You must enable cURL extension on your server if you want to use this module.');
			else if (!ini_get('allow_url_fopen'))
				$this->warning = $this->l('You must enable allow_url_fopen option on your server if you want to use this module.');
		}

		if (_PS_VERSION_ < '1.5')
			require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');

		$this->categorySynchronize = new CategorySynchronize();
		$this->productSynchronize = new ProductSynchronize();
		$this->cartSynchronize = new CartSynchronize();
		$this->orderSynchronize = new OrderSynchronize();
		$this->actionSynchronize = new ActionSynchronize();
		$this->aecookie = AECookie::getInstance();

		Configuration::updateValue('AE_CONF_HOST', 'json.production.affinityitems.com');
		Configuration::updateValue('AE_CONF_PORT', 80);

		$this->displayName = $this->l('Affinity Items');
		$this->description = $this->l('Improve your sales by 10 to 60% with a personalized merchandizing: offer the appropriate products to each visitor.');
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
		$this->checkForUpdates();
	}


	private function _createAjaxController()
	{
		$tab = new Tab();
		$tab->active = 1;
		foreach (Language::getLanguages(false) as $language)
			$tab->name[$language['id_lang']] = 'AffinityItems';
		$tab->class_name = 'AEAjax';
		$tab->module = $this->name;
		$tab->id_parent = -1;
		if (!$tab->add())
			return false;
		return true;
	}

	private function _removeAjaxContoller()
	{
		$tab_id = (int)Tab::getIdFromClassName('AEAjax');
		if ($tab_id)
		{
			$tab = new Tab($tab_id);
			$tab->delete();
		}
		return true;
	}

	public static function hasKey($key)
	{
		if (version_compare(_PS_VERSION_, '1.5', '>='))
			return Configuration::hasKey($key);
		else
			return Configuration::get($key);
	}

	public static function updateGlobalValue($key, $value)
	{
		if (version_compare(_PS_VERSION_, '1.5', '>='))
			Configuration::updateGlobalValue($key, $value);
		else
			Configuration::updateValue($key, $value);
	}

	public function install()
	{
		$sql = array();
		$properties = array();
		$hook_list = array();

		include(dirname(__FILE__).'/configuration/hookList.php');
		include(dirname(__FILE__).'/configuration/properties.php');
		include(dirname(__FILE__).'/configuration/sqlinstall.php');

		if (parent::install())
		{
			foreach ($sql as $s)
			{
				if (!Db::getInstance()->execute($s))
					return false;
			}

			foreach ($properties as $key => $value)
			{
				if (!(self::hasKey($key)))
					self::updateGlobalValue($key, $value);
			}

			foreach ($hook_list as $hook)
			{
				if (!$this->registerHook($hook))
					return false;
			}

			if (version_compare(_PS_VERSION_, '1.5', '>='))
			{
				if (!$this->_createAjaxController())
					return false;
			}

			return true;
		}
		else
			return false;
	}

	public function uninstall()
	{
		$sql = array();
		$properties = array();
		$hook_list = array();

		include(dirname(__FILE__).'/configuration/hookList.php');
		include(dirname(__FILE__).'/configuration/properties.php');
		include(dirname(__FILE__).'/configuration/sqluninstall.php');

		if (parent::uninstall())
		{
			try {
				$disable_request = new DisableRequest((bool)Configuration::get('AE_BREAK_CONTRACT'));
				$disable_request->post();
			} catch(Exception $e)
			{
				AELogger::log('[INFO]', $e->getMessage());
			}

			foreach ($sql as $s)
			{
				if (!Db::getInstance()->execute($s))
					return false;
			}

			foreach (array_keys($properties) as $key)
				Configuration::deleteByName($key);

			foreach ($hook_list as $hook)
			{
				if (!$this->unregisterHook($hook))
					return false;
			}

			if (version_compare(_PS_VERSION_, '1.5', '>='))
			{
				if (!$this->_removeAjaxContoller())
					return false;
			}

			return true;
		}
		else
			return false;
	}

	public function hookBackOfficeHeader()
	{
		$this->generateGuest();
		ABTesting::init();
	}

	public function hookheader()
	{
		$this->context->controller->addJS(($this->_path).'resources/js/affinityitems.js');
		$this->context->controller->addCSS(($this->_path).'resources/css/'.Tools::substr(str_replace('.', '', _PS_VERSION_), 0, 2).'/aefront.css', 'all');
		if (isset($_SERVER['HTTP_USER_AGENT']))
		{
			if (!preg_match('/'.self::$crawler_list.'/i', $_SERVER['HTTP_USER_AGENT']) > 0)
			{
				$this->generateGuest();
				if (Tools::getValue('aeabtesting'))
					ABTesting::forceGroup(Tools::getValue('aeabtesting'));
				ABTesting::init();
			}
		}

		return $this->renderSpecialHook();
	}

	/*
	 *
	 * hook category part
	 *
	*/

	public function hookcategoryAddition()
	{
		if (self::isConfig() && self::isLastSync())
			$this->categorySynchronize->syncNewElement();
	}

	public function hookcategoryUpdate()
	{
		if (self::isConfig() && self::isLastSync())
			$this->categorySynchronize->syncUpdateElement();
	}

	public function hookcategoryDeletion()
	{
		if (self::isConfig() && self::isLastSync())
			$this->categorySynchronize->syncDeleteElement();
	}

	/*
	 *
	 * hook member part
	 *
	*/

	public function hookauthentication($params)
	{
		if ($this->aecookie->getCookie()->__isset('aeguest'))
		{
			try {
				$data = new stdClass();
				$data->guestId = (String)$this->aecookie->getCookie()->__get('aeguest');
				$data->memberId = (String)$params['cart']->id_customer;
				$request = new LinkGuestToMemberRequest($data);
				$request->post();
			} catch(Exception $e)
			{
				AELogger::log('[ERROR]', $e->getMessage());
			}
		}
	}

	/*
	 *
	 * hook product part
	 *
	*/

	public function hookaddproduct()
	{
		if (self::isConfig() && self::isLastSync())
			$this->productSynchronize->syncNewElement();
	}

	public function hookupdateproduct()
	{
		if (self::isConfig() && self::isLastSync())
			$this->productSynchronize->syncUpdateElement();
	}

	public function hookdeleteproduct()
	{
		if (self::isConfig() && self::isLastSync())
			$this->productSynchronize->syncDeleteElement();
	}

	public function hookupdateProductAttribute()
	{
		if (self::isConfig() && self::isLastSync())
			$this->productSynchronize->syncNewElement();
	}

	/*
	 *
	 * hook cart
	 *
	*/

	public function hookCart($params)
	{
		if (self::isConfig() && self::isLastSync())
		{
			$person = $this->getPerson();
			if (isset($params['cart']->id) && $person instanceof AEGuest)
			{
				AEAdapter::setCartGroup($params['cart']->id, $person->getGroup(), $person->getPersonId(), Tools::getRemoteAddr());
				$this->cartSynchronize->syncNewElement();
				$this->cartSynchronize->syncUpdateElement();
				$this->cartSynchronize->syncDeleteElement();
				$this->cartSynchronize->syncGuestCart($params['cart']->id);
			}
		}
	}

	/*
	 *
	 * hook new order
	 *
	*/

	public function hookactionObjectOrderAddAfter()
	{
		if (self::isConfig() && self::isLastSync())
			$this->orderSynchronize->syncNewElement();
	}

	/*
	 *
	 * hook recommendation
	 *
	*/

	public function getRecommendation($aecontext, $stack)
	{
		$recommendation = new Recommendation($aecontext, $this->context, $stack, true);
		$products = $recommendation->getRecommendation();
		return $products;
	}

	public function formatConfiguration($configuration, $hook_name)
	{
		$hook_configuration = new stdClass();
		foreach ($configuration as $key => $value)
		{
			$k = str_replace($hook_name, '', $key);
			$hook_configuration->{$k} = $value;
		}
		return $hook_configuration;
	}

	public function hookHome()
	{
		if (self::isConfig() && self::isLastSync() && (bool)Configuration::get('AE_RECOMMENDATION'))
		{
			$hook_configuration = unserialize(Configuration::get('AE_CONFIGURATION_HOME'));
			if ((bool)$hook_configuration->recoHome)
			{
				$aecontext = new stdClass();
				$aecontext->context = 'recoAll';
				$aecontext->area = 'HOME';
				$aecontext->size = (int)$hook_configuration->recoSizeHome;
				$products = $this->getRecommendation($aecontext, false);
				$hook_configuration = $this->formatConfiguration($hook_configuration, 'Home');
				if (!empty($products))
				{
					$this->smarty->assign(array(
						'aeproducts' => $products,
						'aeconfiguration' => $hook_configuration,
						'size' => Image::getSize($hook_configuration->imgSize)));
					return $this->display(__FILE__, '/views/templates/hook/'.Tools::substr(str_replace('.', '', _PS_VERSION_), 0, 2).'/hrecommendation.tpl');
				}
			}
		}
	}

	public function hookLeftColumn()
	{
		if (self::isConfig() && self::isLastSync() && (bool)Configuration::get('AE_RECOMMENDATION'))
		{
			$hook_configuration = unserialize(Configuration::get('AE_CONFIGURATION_LEFT'));
			if ((bool)$hook_configuration->recoLeft)
			{
				$aecontext = new stdClass();
				$aecontext->context = 'recoAll';
				$aecontext->area = 'LEFT';
				$aecontext->size = (int)$hook_configuration->recoSizeLeft;
				$products = $this->getRecommendation($aecontext, false);
				$hook_configuration = $this->formatConfiguration($hook_configuration, 'Left');
				if (!empty($products))
				{
					$this->smarty->assign(array(
						'aeproducts' => $products,
						'aeconfiguration' => $hook_configuration,
						'size' => Image::getSize($hook_configuration->imgSize)));
					return $this->display(__FILE__, '/views/templates/hook/'.Tools::substr(str_replace('.', '', _PS_VERSION_), 0, 2).'/vrecommendation.tpl');
				}
			}
		}
	}

	public function hookRightColumn()
	{
		if (self::isConfig() && self::isLastSync() && (bool)Configuration::get('AE_RECOMMENDATION'))
		{
			$hook_configuration = unserialize(Configuration::get('AE_CONFIGURATION_RIGHT'));
			if ((bool)$hook_configuration->recoRight)
			{
				$aecontext = new stdClass();
				$aecontext->context = 'recoAll';
				$aecontext->area = 'RIGHT';
				$aecontext->size = (int)$hook_configuration->recoSizeRight;
				$products = $this->getRecommendation($aecontext, false);
				$hook_configuration = $this->formatConfiguration($hook_configuration, 'Right');
				if (!empty($products))
				{
					$this->smarty->assign(array(
						'aeproducts' => $products,
						'aeconfiguration' => $hook_configuration,
						'size' => Image::getSize($hook_configuration->imgSize)));
					return $this->display(__FILE__, '/views/templates/hook/'.Tools::substr(str_replace('.', '', _PS_VERSION_), 0, 2).'/vrecommendation.tpl');
				}
			}
		}
	}

	public function hookProductFooter()
	{
		if (self::isConfig() && self::isLastSync() && (bool)Configuration::get('AE_RECOMMENDATION'))
		{
			$hook_configuration = unserialize(Configuration::get('AE_CONFIGURATION_PRODUCT'));
			if ((bool)$hook_configuration->recoProduct)
			{
				$aecontext = new stdClass();
				$aecontext->context = 'recoSimilar';
				$aecontext->size = (int)$hook_configuration->recoSizeProduct;
				$aecontext->productId = (string)Tools::getValue('id_product');
				$products = $this->getRecommendation($aecontext, true);
				$hook_configuration = $this->formatConfiguration($hook_configuration, 'Product');
				if (!empty($products))
				{
					$this->smarty->assign(array(
						'aeproducts' => $products,
						'aeconfiguration' => $hook_configuration,
						'size' => Image::getSize($hook_configuration->imgSize)));
					return $this->display(__FILE__, '/views/templates/hook/'.Tools::substr(str_replace('.', '', _PS_VERSION_), 0, 2).'/hrecommendation.tpl');
				}
			}
		}
	}


	public function hookShoppingCart($params)
	{
		if (self::isConfig() && self::isLastSync() && (bool)Configuration::get('AE_RECOMMENDATION'))
		{
			$hook_configuration = unserialize(Configuration::get('AE_CONFIGURATION_CART'));
			if ((bool)$hook_configuration->recoCart)
			{
				$aecontext = new stdClass();
				$aecontext->context = 'recoCart';
				$aecontext->orderLines = $this->getCartOrderLines($params);
				$aecontext->size = (int)$hook_configuration->recoSizeCart;
				$products = $this->getRecommendation($aecontext, false);
				$hook_configuration = $this->formatConfiguration($hook_configuration, 'Cart');
				if (!empty($products))
				{
					$this->smarty->assign(array(
						'aeproducts' => $products,
						'aeconfiguration' => $hook_configuration,
						'size' => Image::getSize($hook_configuration->imgSize)));
					return $this->display(__FILE__, '/views/templates/hook/'.Tools::substr(str_replace('.', '', _PS_VERSION_), 0, 2).'/hrecommendation.tpl');
				}
			}
		}
	}

	public function getCartOrderLines($params)
	{
		$order_lines = array();
		foreach ($params['products'] as $value)
		{
			$order_line = new stdClass();
			$order_line->productId = $value['id_product'];
			$order_line->attributeIds = $this->getCartsProductAttributes($value['id_product_attribute']);
			$order_line->quantity = $value['cart_quantity'];
			array_push($order_lines, $order_line);
		}
		return $order_lines;
	}

	public function getCartsProductAttributes($product_attribute_id)
	{
		$attribute_ids = array();
		$attributes = AEAdapter::getCartsProductAttributes($product_attribute_id);
		foreach ($attributes as $attribute)
			array_push($attribute_ids, $attribute['id_attribute']);
		return $attribute_ids;
	}

	public function renderCategory($category_id)
	{
		$render = '';
		if (self::isConfig() && self::isLastSync() && (bool)Configuration::get('AE_RECOMMENDATION'))
		{
			$hook_configuration = unserialize(Configuration::get('AE_CONFIGURATION_CATEGORY'));
			if ((bool)$hook_configuration->recoCategory)
			{
				$aecontext = new stdClass();
				$aecontext->context = 'recoCategory';
				$aecontext->categoryId = $category_id;
				$aecontext->size = (int)$hook_configuration->recoSizeCategory;
				$products = $this->getRecommendation($aecontext, false);
				$hook_configuration = $this->formatConfiguration($hook_configuration, 'Category');
				if (!empty($products))
				{
					$this->smarty->assign(array(
						'aeproducts' => $products,
						'aeconfiguration' => $hook_configuration,
						'size' => Image::getSize($hook_configuration->imgSize)));
					$render = $this->display(__FILE__, '/views/templates/hook/'.Tools::substr(str_replace('.', '', _PS_VERSION_), 0, 2).'/srecommendation.tpl');
				}
			}
		}
		return $render;
	}

	public function renderSearch($expr)
	{
		$render = '';
		if (self::isConfig() && self::isLastSync() && (bool)Configuration::get('AE_RECOMMENDATION'))
		{
			$hook_configuration = unserialize(Configuration::get('AE_CONFIGURATION_SEARCH'));
			if ((bool)$hook_configuration->recoSearch)
			{
				$aecontext = new stdClass();
				$aecontext->context = 'recoSearch';
				$aecontext->keywords = $expr;
				$aecontext->size = (int)$hook_configuration->recoSizeSearch;
				$products = $this->getRecommendation($aecontext, false);
				$hook_configuration = $this->formatConfiguration($hook_configuration, 'Search');
				if (!empty($products))
				{
					$this->smarty->assign(array(
						'aeproducts' => $products,
						'aeconfiguration' => $hook_configuration,
						'size' => Image::getSize($hook_configuration->imgSize)));
					$render = $this->display(__FILE__, '/views/templates/hook/'.Tools::substr(str_replace('.', '', _PS_VERSION_), 0, 2).'/srecommendation.tpl');
				}
			}
		}
		return $render;
	}

	/*
	 *
	 * backoffice part
	 *
	*/

	public static function getAffinityBackContent()
	{
		$instance = new AffinityItems();
		return $instance->getContent();
	}

	public function getContent()
	{
		return $this->_displayForm();
	}

	public function _displayForm()
	{
		$html = '';
		if (_PS_VERSION_ < '1.5')
		{
			$html .= "<link rel='stylesheet' type='text/css' href='".($this->_path)."resources/css/main.css' />";
			$html .= "<link rel='stylesheet' type='text/css' href='".($this->_path)."resources/css/14/aebackoffice.css' />";
			$html .= "<link rel='stylesheet' type='text/css' href='".($this->_path)."resources/css/ui.all.css' />";
			$html .= "<link rel='stylesheet' type='text/css' href='".($this->_path)."resources/css/jquery.nouislider.css' />";
			$html .= "<link rel='stylesheet' type='text/css' href='".($this->_path)."resources/css/jquery.powertip.min.css' />";
			$html .= "<script type='text/javascript' src='".($this->_path)."resources/js/ui.core.min.js'></script>";
			$html .= "<script type='text/javascript' src='".($this->_path)."resources/js/jquery.nouislider.min.js'></script>";
			$html .= "<script type='text/javascript' src='".($this->_path)."resources/js/jquery.powertip.min.js'></script>";
		}
		else
		{
			$this->context->controller->addCSS(($this->_path).'resources/css/main.css', 'all');
			$this->context->controller->addCSS(($this->_path).'resources/css/'.Tools::substr(str_replace('.', '', _PS_VERSION_), 0, 2).'/aebackoffice.css', 'all');
			$this->context->controller->addCSS(($this->_path).'resources/css/ui.all.css', 'all');
			$this->context->controller->addCSS(($this->_path).'resources/css/jquery.nouislider.css', 'all');
			$this->context->controller->addCSS(($this->_path).'resources/css/jquery.powertip.min.css', 'all');
			$this->context->controller->addJS(($this->_path).'resources/js/ui.core.min.js');
			$this->context->controller->addJS(($this->_path).'resources/js/jquery.nouislider.min.js');
			$this->context->controller->addJS(($this->_path).'resources/js/jquery.powertip.min.js');
		}
		if (self::isConfig())
			return $html.$this->getDashboard();
		else
			return $html.$this->getAuthentication();
	}

	public function getDashboard()
	{
		$html = '';
		if ($this->postProcess())
			$html .= Module::displayConfirmation($this->l('Settings updated.'));

		$configuration = array();
		$site_request = new SiteRequest(array());
		$data = $site_request->get();

		foreach (self::$hook_list as $hook)
		{
			$hook_configuration = unserialize(Configuration::get('AE_CONFIGURATION_'.Tools::strtoupper($hook)));
			$configuration[$hook] = $hook_configuration;
		}

		$this->context->smarty->assign(array(
			'baseUrl' =>  version_compare(_PS_VERSION_, '1.5', '>=') ? $this->context->shop->getBaseURL() : __PS_BASE_URI__,
			'siteId' =>Configuration::get('AE_SITE_ID'),
			'aetoken' => Configuration::get('AE_BACKOFFICE_TOKEN'),
			'data' => $data,
			'statistics' => isset($data->statistics) ? Tools::jsonDecode($data->statistics) : array(),
			'notifications' => $this->getNotification($data),
			'localHosts' => unserialize(Configuration::get('AE_HOST_LIST')),
			'abtestingPercentage' => Configuration::get('AE_A_TESTING'),
			'recommendation' => Configuration::get('AE_RECOMMENDATION'),
			'blacklist' => unserialize(Configuration::get('AE_AB_TESTING_BLACKLIST')),
			'breakContract' => Configuration::get('AE_BREAK_CONTRACT'),
			'syncDiff' => Configuration::get('AE_SYNC_DIFF'),
			'logs' => AELogger::getLog(),
			'hookList' => self::$hook_list,
			'configuration' => $configuration,
			'ajaxController' => version_compare(_PS_VERSION_, '1.5', '>=') ? true : false,
			'prestashopToken' => Tools::getAdminToken('AEAjax'.(int)Tab::getIdFromClassName('AEAjax').(int)$this->context->cookie->id_employee),
			'imgSizeList' => $this->getImageSize()
			));

		$html .= $this->display(($this->_path), '/views/templates/admin/dashboard.tpl');
		return $html;
	}

	public function getAuthentication()
	{
		$html = '';
		$this->context->smarty->assign(array(
			'aetoken' => Configuration::get('AE_BACKOFFICE_TOKEN'),
			'ajaxController' => version_compare(_PS_VERSION_, '1.5', '>=') ? true : false,
			'prestashopToken' => Tools::getAdminToken('AEAjax'.(int)Tab::getIdFromClassName('AEAjax').(int)$this->context->cookie->id_employee),
			'activity' => AEAdapter::getActivity()
		));
		$html .= $this->display(($this->_path), '/views/templates/admin/authentication.tpl');
		return $html;
	}

	public function postProcess()
	{
		if (Tools::isSubmit('configuration'))
		{
			Configuration::updateValue('AE_RECOMMENDATION', Tools::getValue('recommendation') ? 1 : 0);
			$configuration = new stdClass();
			foreach (self::$hook_list as $hook)
			{
				$object = new stdClass();
				foreach ($_POST as $key => $value)
				{
					if (preg_match( '/'.$hook.'/i', $key))
						$object->{$key} = $value;
				}
				$object->area = Tools::strtolower($hook);
				$configuration->{$hook} = $object;
			}
			foreach ($configuration as $key => $value)
			{
				try {
					Configuration::updateValue('AE_CONFIGURATION_'.Tools::strtoupper($key), serialize($value));
				} catch(Exception $e)
				{
					error_log($e);
				}
			}
			return true;
		}
		else if (Tools::isSubmit('syncDiff') && Tools::isSubmit('blacklist'))
		{
			Configuration::updateValue('AE_SYNC_DIFF', Tools::getValue('syncDiff'));
			Tools::getValue('breakContract') ? Configuration::updateValue('AE_BREAK_CONTRACT', 1) : Configuration::updateValue('AE_BREAK_CONTRACT', 0);
			self::setBlackList(Tools::safeOutput(Tools::getValue('blacklist')));
			return true;
		}
	}

	/*
	 *
	 * utils
	 *
	*/

	public function renderSpecialHook()
	{
		$hook_search_configuration = unserialize(Configuration::get('AE_CONFIGURATION_SEARCH'));
		$hook_category_configuration = unserialize(Configuration::get('AE_CONFIGURATION_CATEGORY'));

		if (Tools::getValue('id_category'))
			$render_category = $this->renderCategory(Tools::getValue('id_category'));
		else if (Tools::getValue('search_query'))
			$render_search = $this->renderSearch(Tools::getValue('search_query'));

		if (!$this->getPerson() instanceof stdClass)
		{
			$this->smarty->assign(array(
				'abtesting' => $this->getPerson()->getGroup(),
				'renderCategory' => isset($render_category) ? $render_category : '',
				'renderSearch' => isset($render_search) ? $render_search : '',
				'hookSearchConfiguration' => $hook_search_configuration,
				'hookCategoryConfiguration' => $hook_category_configuration));

			return $this->display(__FILE__, '/views/templates/hook/hook.tpl');
		}
	}

	public function getNotification($data)
	{
		if (isset($data->notifications))
		{
			if ($data->notifications = AENotification::convert(Tools::jsonDecode($data->notifications)))
			{
				$notifications = new AENotification($data->notifications);
				$notifications = $notifications->syncNewElement();
			}
		}
		return AEAdapter::getNotifications($this->context->language->id);
	}

	public function getImageSize()
	{
		return Db::getInstance()->executeS('
			SELECT name
			FROM `'._DB_PREFIX_.'image_type`
			WHERE (name LIKE "medium%"
				OR name LIKE "home%"
				OR name LIKE "small%"
				OR name LIKE "large%")'
		);
	}

	public static function setBlackList($ip_list = array())
	{
		$black_list = array();
		try {
			if ($exp = explode(';', $ip_list))
			{
				foreach ($exp as $ip)
				{
					if (preg_match(AELibrary::$check_ip, $ip))
						array_push($black_list, $ip);
				}
				Configuration::updateValue('AE_AB_TESTING_BLACKLIST', serialize($black_list));
			}
		} catch(Exception $e)
		{
			AELogger::log('[ERROR]', $e->getMessage());
		}
	}


	public static function isConfig()
	{
		return AEAdapter::isConfig();
	}

	public static function isLastSync()
	{
		if (!AELibrary::isEmpty(Configuration::get('AE_LAST_SYNC_END')))
			return true;
		return false;
	}

	public function getPerson()
	{
		$person = new stdClass();
		$guest_id = $this->aecookie->getCookie()->__isset('aeguest') ? $this->aecookie->getCookie()->__get('aeguest') : '';
		if (!AELibrary::isEmpty($guest_id))
			$person = new AEGuest($guest_id);
		return $person;
	}

	public function generateGuest()
	{
		if (!$this->aecookie->getCookie()->__isset('aeguest'))
		{
			$aeguest = str_replace('.', '', uniqid('ae', true));
			$this->aecookie->getCookie()->__set('aeguest', $aeguest);
			$this->aecookie->getCookie()->write();
		}
	}

	private function checkForUpdates()
	{
		if (version_compare(_PS_VERSION_, '1.5', '<') && self::isInstalled($this->name))
			foreach (array('1.0.0', '1.0.1') as $version)
			{
				$file = dirname(__FILE__).'/upgrade/install-'.$version.'.php';
				if (Configuration::get('AE_VERSION') < $version && file_exists($file))
				{
					include_once($file);
					call_user_func('upgrade_module_'.str_replace('.', '_', $version), $this);
				}
			}
	}

}