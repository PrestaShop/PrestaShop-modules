<?php
/**
 * 2013 Give.it
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@give.it so we can send you a copy immediately.
 *
 * @author    JSC INVERTUS www.invertus.lt <help@invertus.lt>
 * @copyright 2013 Give.it
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of Give.it
 */

if (!defined('_PS_VERSION_'))
	exit;

require_once dirname(__FILE__).'/config.api.php';
require_once dirname(__FILE__).'/sdk/sdk.php';
require_once dirname(__FILE__).'/giveit.class.php';
require_once (_GIVEIT_MODELS_DIR_.'ObjectModel.php');
require_once (_GIVEIT_MODELS_DIR_.'Product.php');
require_once (_GIVEIT_MODELS_DIR_.'Communication.php');
require_once (_GIVEIT_MODELS_DIR_.'Category.php');
require_once (_GIVEIT_MODELS_DIR_.'Shipping.php');
require_once (_GIVEIT_CLASSES_DIR_.'configuration.view.php');
require_once (_GIVEIT_CLASSES_DIR_.'shipping_prices.view.php');

if (version_compare(_PS_VERSION_, '1.5', '<'))
	require_once (_PS_MODULE_DIR_.'giveit/backward_compatibility/backward.php');

class GiveIt extends Module {
	private $html = '';

	private $api;

	private $available_positions = array();

	private static $giveit_front_office_javascript = '';

	public $module_url = '';

	const EXTRA_LEFT = 'extra_left';
	const PRODUCT_ACTIONS = 'product_actions';
	const EXTRA_RIGHT = 'extra_right';
	const PRODUCT_FOOTER = 'product_footer';
	const CUSTOM_POSITION = 'custom_position';

	const PUBLIC_KEY = 'GIVE_IT_PUBLIC_KEY';
	const DATA_KEY = 'GIVE_IT_DATA_KEY';
	const PRIVATE_KEY = 'GIVE_IT_PRIVATE_KEY';
	const BUTTON_ACTIVE = 'GIVE_IT_BUTTON_ACTIVE';
	const BUTTON_POSITION = 'GIVE_IT_BUTTON_POSITION';
	
	const MODE = 'GIVE_IT_MODE';
	
	const DEBUG = 'GIVE_IT_DEBUG_MODE';
	const PRODUCTION = 'GIVE_IT_PRODUCTION_MODE';

	const CURRENT_INDEX = 'index.php?tab=AdminModules&configure=giveit&module_name=giveit&token=';

	public function __construct()
	{
		$this->name = 'giveit';
		$this->tab = 'advertising_marketing';
		$this->version = '1.3.1';
		$this->author = 'Give.it';

		parent::__construct();

		$this->displayName = $this->l('Give.it');
		$this->description = $this->l('The gifting add-on for web stores.');

		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			$this->context = Context::getContext();

			$this->context->smarty->assign('ps14', true);
			$this->context->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
		}

		if (defined('_PS_ADMIN_DIR_'))
			$this->module_url = self::CURRENT_INDEX.Tools::getAdminTokenLite('AdminModules');

		$this->available_positions = array(self::EXTRA_LEFT, self::PRODUCT_ACTIONS, self::EXTRA_RIGHT, self::PRODUCT_FOOTER, self::CUSTOM_POSITION);

		if (version_compare(_PS_VERSION_, '1.6', '>='))
			$this->bootstrap = true;
	}

	public function install()
	{
		if (!function_exists('curl_init'))
			return false;

		if (!function_exists('mcrypt_encrypt'))
			return false;

		$sql = '
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'giveit_category` (
				`id_give_it_category` int(11) NOT NULL AUTO_INCREMENT,
				`id_shop` int(11) NOT NULL,
				`id_category` int(11) NOT NULL,
				`date_add` datetime DEFAULT NULL,
				`date_upd` datetime DEFAULT NULL,
				PRIMARY KEY (`id_give_it_category`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8';

		if (!DB::getInstance()->execute($sql))
			return false;

		$sql = '
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'giveit_product` (
				`id_giveit_product` int(10) NOT NULL AUTO_INCREMENT,
				`id_product` int(10) NOT NULL,
				`id_product_attribute` int(10) NOT NULL,
				`display_button` tinyint(1) NOT NULL DEFAULT "1",
				`id_shop` int(10) NOT NULL,
				`date_add` datetime NOT NULL,
				`date_upd` datetime NOT NULL,
				PRIMARY KEY (`id_giveit_product`),
				UNIQUE KEY `id_product` (`id_product`,`id_product_attribute`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8';

		if (!DB::getInstance()->execute($sql))
			return false;

		$sql = '
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'giveit_shipping` (
				`id_giveit_shipping` int(10) NOT NULL AUTO_INCREMENT,
				`price` decimal(17,6) NOT NULL,
				`free_above` decimal(17,6) NOT NULL,
				`tax_percent` decimal(17,6) NOT NULL,
				`id_currency` int(10) NOT NULL,
				`iso_code` varchar(255) NOT NULL,
				`id_shop` int(10) NOT NULL,
				`date_add` datetime NOT NULL,
				`date_upd` datetime NOT NULL,
				PRIMARY KEY (`id_giveit_shipping`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8';

		if (!DB::getInstance()->execute($sql))
			return false;

		$sql = '
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'giveit_shipping_lang` (
				`id_giveit_shipping` int(10) NOT NULL,
				`title` varchar(255) NOT NULL,
				`id_lang` int(10) NOT NULL,
				PRIMARY KEY (`id_giveit_shipping`,`id_lang`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8';

		if (!DB::getInstance()->execute($sql))
			return false;

		return (
			parent::install() && $this->registerHook('displayAdminProductsExtra')
			&& $this->registerHook('header') && $this->registerHook('extraLeft') && $this->registerHook('productActions')
			&& $this->registerHook('extraRight') && $this->registerHook('productfooter') && $this->registerHook('backOfficeHeader')
			&& $this->setDefaultConfiguration()
		);
	}

	public function uninstall()
	{
		if (!parent::uninstall() || !$this->deleteTables() || !$this->deleteConfigurationSettings() || !$this->unregisterHook('displayAdminProductsExtra'))
			return false;
		return true;
	}

	private function setDefaultConfiguration()
	{
		$categories = Category::getCategories();
		$categories_ids = array();
		foreach ($categories as $category => $id_category)
			foreach ($id_category as $key => $value)
				$categories_ids[] = $key;

		GiveItCategory::clearCategory();

		foreach ($categories_ids as $key => $id)
		{
			if (version_compare(_PS_VERSION_, '1.5', '<'))
			{
				if (!$this->setDefaultCategorySetting((int)$id, (int)Context::getContext()->shop->id))
					return false;
			}
			else
			{
				foreach (array_keys(Shop::getShops(false)) as $id_shop)
					if (!$this->setDefaultCategorySetting($id, $id_shop))
						return false;
			}
		}
		
		return Configuration::updateValue(self::BUTTON_ACTIVE, 0) &&
			Configuration::updateValue(self::BUTTON_POSITION, self::PRODUCT_FOOTER) &&
			Configuration::updateValue(self::MODE, self::PRODUCTION);
	}

	private function setDefaultCategorySetting($id_category, $id_shop)
	{
		$give_it_category_obj = new GiveItCategory();
		$give_it_category_obj->id_category = (int)$id_category;
		$give_it_category_obj->id_shop = (int)$id_shop;
		return $give_it_category_obj->save();
	}

	public function registerHook($hook_name, $shop_list = null)
	{
		if ($hook_name == 'displayAdminProductsExtra' && version_compare(_PS_VERSION_, '1.5', '<'))
			$hook_name = 'backOfficeTop';

		return parent::registerHook($hook_name, $shop_list);
	}

	private function deleteTables()
	{
		return Db::getInstance()->execute('DROP TABLE IF EXISTS`'._DB_PREFIX_.'giveit_category`')
				&& Db::getInstance()->execute('DROP TABLE IF EXISTS`'._DB_PREFIX_.'giveit_product`')
				&& Db::getInstance()->execute('DROP TABLE IF EXISTS`'._DB_PREFIX_.'giveit_shipping`')
				&& Db::getInstance()->execute('DROP TABLE IF EXISTS`'._DB_PREFIX_.'giveit_shipping_lang`');
	}

	private function deleteConfigurationSettings()
	{
		return Configuration::deleteByName(self::PUBLIC_KEY)
				&& Configuration::deleteByName(self::DATA_KEY)
				&& Configuration::deleteByName(self::PRIVATE_KEY)
				&& Configuration::deleteByName(self::BUTTON_ACTIVE)
				&& Configuration::deleteByName(self::BUTTON_POSITION);
	}

	/* ps14 and ps15 hooks */

	public function hookExtraLeft()
	{
		if (Configuration::get(self::BUTTON_POSITION) == self::EXTRA_LEFT)
			return $this->displayButton();
	}

	public function hookHeader()
	{
		if (isset($this->context->smarty->tpl_vars['page_name'])
		&& $this->context->smarty->tpl_vars['page_name']->value == 'product'
		&& ($id_product = Tools::getValue('id_product')))
		{
			if ($this->buttonEnabledForProduct(new Product($id_product, false, $this->context->language->id)))
			{
				$this->api = new GiveItAPI(Configuration::get(self::PUBLIC_KEY), Configuration::get(self::DATA_KEY), Configuration::get(self::PRIVATE_KEY));

				if (version_compare(_PS_VERSION_, '1.5', '<'))
					Tools::addJS($this->_path.'js/giveit.js');
				else
					$this->context->controller->addJS($this->_path.'js/giveit.js');
				
				$js = $this->api->client->getButtonJS();

				if (Configuration::get(self::BUTTON_POSITION) == self::CUSTOM_POSITION)
					$this->context->smarty->assign('display_give_it_button', $this->displayButton());

				return $js;
			}
		}
	}

	/* ps14 hooks */

	public function hookBackOfficeTop($params)
	{
		if (Tools::getValue('tab') == 'AdminCatalog'
			&& $id_product = Tools::getValue('id_product', 0)
			&& (Tools::isSubmit('updateproduct') || Tools::isSubmit('addproduct'))
		)
		{
			if ($id_product)
			{
				$this->hookDisplayAdminProductsExtra($params);
				return $this->display(__FILE__, 'views/templates/admin/product_ps14.tpl');
			}
		}
	}

	public function hookProductActions()
	{
		if (Configuration::get(self::BUTTON_POSITION) == self::PRODUCT_ACTIONS)
			return $this->displayButton();
	}

	public function hookExtraRight()
	{
		if (Configuration::get(self::BUTTON_POSITION) == self::EXTRA_RIGHT)
			return $this->displayButton();
	}

	public function hookProductfooter()
	{
		if (Configuration::get(self::BUTTON_POSITION) == self::PRODUCT_FOOTER)
			return $this->displayButton();
	}

	/* ps15 hooks */

	public function hookDisplayAdminProductsExtra()
	{
		if ((Tools::getValue('controller') == 'AdminProducts' || Tools::getValue('tab') == 'AdminCatalog') && ($id_product = Tools::getValue('id_product')))
		{
			if (!$this->testConnection())
				return $this->displayWarnings(array($this->l('Your entered API keys appears to be invalid')));
			$product = new Product($id_product, null, $this->context->language->id);
			$this->context->smarty->assign(array(
				'combinations' => $this->getProductCombinations($product),
				'id_shop' => $this->context->shop->id,
				'module_token' => sha1(_COOKIE_KEY_.$this->name)
			));
			return $this->display(__FILE__, 'views/templates/admin/product.tpl');
		}
		else
			return $this->displayWarnings(array($this->l('You must save this product first')));
	}

	public function hookDisplayProductButtons($params)
	{
		return $this->hookProductActions($params);
	}

	public function hookDisplayRightColumnProduct($params)
	{
		return $this->hookExtraRight($params);
	}

	public function hookDisplayFooterProduct($params)
	{
		return $this->hookProductfooter($params);
	}

	private function displayButton()
	{
		if ($this->api)
		{
			$html_tag = (Configuration::get(self::BUTTON_POSITION) == self::EXTRA_LEFT) ? 'li' : 'div';
			$product = new Product((int)Tools::getValue('id_product'), false, $this->context->language->id);
			$combinations = $this->getProductCombinations($product);
			$html = '';
			foreach ($combinations as $id_product_attribute => $combination)
			{
				$button_enabled_for_product = $this->buttonEnabledForProduct($product, $id_product_attribute, $combinations);
				if ($combination['quantity'] && $button_enabled_for_product === true)
				{
					$this->api->setProduct($product, $combination);

					if ($button_html = $this->api->getButton())
						$html .= sprintf('<%s class="giveit_button_container" rel="'.$id_product_attribute
													.'" style="display:'.($id_product_attribute ? 'none' : 'block').'">', $html_tag)
									.$button_html.sprintf('</%s>', $html_tag);
				}
				else
				{
					if (Configuration::get(self::MODE) == self::DEBUG)
						$html .=  sprintf('<%s class="giveit_button_container" rel="'.$id_product_attribute
													.'" style="display:'.($id_product_attribute ? 'none' : 'block').'">', $html_tag)
									.'<p class="error alert alert-danger">'.sprintf($this->l('Give.it button was not displayed - %s'), $button_enabled_for_product).'</p>'.sprintf('</%s>', $html_tag);
					else
						$html .= '<!-- '.sprintf($this->l('Give.it button was not displayed - %s'), $button_enabled_for_product).' -->';
				}
			}

			return $html;
		}
	}

	private function buttonEnabledForProduct($product, $id_product_attribute = null, $combinations = null)
	{
		/* button is disabled globaly */
		if (!Configuration::get(self::BUTTON_ACTIVE))
			return $this->l('button is turned off in module settings page');

		if (!$combinations)
			$combinations = $this->getProductCombinations($product);

		/* checks if button is enabled for any of product combinations */
		foreach ($combinations as $combination)
		{
			/* button should be display for combination */
			if (($id_product_attribute === null || (   $id_product_attribute !== null
													&& $combination['id_product_attribute'] == $id_product_attribute))
													&& $combination['display_button'] == 1)
				return true;
			/* button should be displayed if product category is enabled to display button */
			elseif ($id_product_attribute !== null
					&& $combination['id_product_attribute'] == $id_product_attribute
					&& $combination['display_button'] === '')
				return $this->isProductCategoryEnabledToDisplayButton($product->id);
			/* button should not be displayed in any case */
			elseif ($id_product_attribute !== null && $combination['id_product_attribute'] == $id_product_attribute)
				return $this->l('disabled product combination');
		}

		return $this->isProductCategoryEnabledToDisplayButton($product->id);
	}

	private function isProductCategoryEnabledToDisplayButton($id_product)
	{
		/* checks if button is enabled for product category */
		if (!$categories = GiveItCategory::getCategories())
			return $this->l('one or more categores to which belongs product are disabled and button uses global settings');

		$category_shop = version_compare(_PS_VERSION_, '1.5', '<') ? '' : ' JOIN `'
						._DB_PREFIX_.'category_shop` cs ON (cs.`id_shop`='
						.(int)$this->context->shop->id.' AND cs.`id_category`=cp.`id_category`) ';

		$total_category_count_products = Db::getInstance()->getValue('
                        SELECT COUNT(*)
                        FROM `'._DB_PREFIX_.'category_product` cp
                        '.$category_shop.'
                        WHERE cp.`id_product`='.(int)$id_product
                );

		$active_product_categories = Db::getInstance()->getValue('
			SELECT COUNT(*)
			FROM `'._DB_PREFIX_.'category_product` cp
			'.$category_shop.'
			WHERE cp.`id_product`='.(int)$id_product.' AND cp.`id_category` IN ('.implode(',', $categories).')
		');
		/* if amount of categories for this product differs from amount of categories enabled hide button */

		if ($total_category_count_products==$active_product_categories)
			return true;
		else
			return $this->l('one or more categores to which belongs product are disabled and button uses global settings');
	}

	/* captures give.it javascript code in order to return it in header hook rather than echoing it there */
	private static function captureJS($html)
	{
		self::$giveit_front_office_javascript = $html;
		return '';
	}

	public function getProductCombinations($product)
	{
		/* Build attributes combinations */
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			$combinations = $product->getAttributeCombinaisons((int)$this->context->language->id);
		else
			$combinations = $product->getAttributeCombinations((int)$this->context->language->id);

		$comb_array = array();

		if (is_array($combinations))
		{
			foreach ($combinations as $k => $combination)
			{
				$comb_array[$combination['id_product_attribute']]['id_product_attribute'] = $combination['id_product_attribute'];
				$comb_array[$combination['id_product_attribute']]['attributes'][] = array($combination['group_name'],
																						$combination['attribute_name'],
																						$combination['id_attribute']);
			}
		}

		if ($comb_array)
		{
			foreach ($comb_array as $id_product_attribute => $product_attribute)
			{
				$list = '';

				/* In order to keep the same attributes order */
				asort($product_attribute['attributes']);

				foreach ($product_attribute['attributes'] as $attribute)
					$list .= $attribute[0].' - '.$attribute[1].', ';

				$list = rtrim($list, ', ');
				$comb_array[$id_product_attribute]['attributes'] = $list;

				$display_button = GiveItProduct::buttonIsDisplayed($product->id, $id_product_attribute);
				$comb_array[$id_product_attribute]['display_button'] = ($display_button === false) ? '' : (int)$display_button;
				$comb_array[$id_product_attribute]['quantity'] = Product::getQuantity($product->id, $id_product_attribute);
			}
		} else {
			$display_button = GiveItProduct::buttonIsDisplayed($product->id, 0);
			$display_button = ($display_button === false) ? '' : (int)$display_button;
			$comb_array[0] = array(
									'id_product_attribute' => 0,
									'attributes' => $product->name,
									'display_button' => $display_button,
									'quantity' => Product::getQuantity($product->id));
		}

		return $comb_array;
	}

	public function getAttributeCombinations($id_lang)
	{
		if (!Combination::isFeatureActive())
			return array();

		$sql = 'SELECT pa.*, product_attribute_shop.*, ag.`id_attribute_group`, ag.`is_color_group`, agl.`name` AS group_name, al.`name` AS attribute_name,
					a.`id_attribute`, pa.`unit_price_impact`
				FROM `'._DB_PREFIX_.'product_attribute` pa
				'.Shop::addSqlAssociation('product_attribute', 'pa').'
				LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`
				LEFT JOIN `'._DB_PREFIX_.'attribute` a ON a.`id_attribute` = pac.`id_attribute`
				LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
				LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = '.(int)$id_lang.')
				LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = '.(int)$id_lang.')
				WHERE pa.`id_product` = '.(int)$this->id.'
				GROUP BY pa.`id_product_attribute`, ag.`id_attribute_group`
				ORDER BY pa.`id_product_attribute`';

		$res = Db::getInstance()->executeS($sql);

		//Get quantity of each variations
		foreach ($res as $key => $row)
		{
			$cache_key = $row['id_product'].'_'.$row['id_product_attribute'].'_quantity';

			if (!Cache::isStored($cache_key))
				Cache::store($cache_key, StockAvailable::getQuantityAvailableByProduct($row['id_product'], $row['id_product_attribute']));

			$res[$key]['quantity'] = Cache::retrieve($cache_key);
		}

		return $res;
	}

	private function displayShippingPricesPage()
	{
		$languages = Language::getLanguages(false);
		$countries = Country::getCountries($this->context->language->id);
		$zones = Zone::getZones(false);
		// add zones to the list and use zone_id as iso_code
		foreach (array_reverse($zones) as $zone)
			array_unshift($countries, array('id_country' => $zone['id_zone'], 'iso_code' => $zone['id_zone'], 'name' => $zone['name']));

		if (Tools::isSubmit('saveShippingData'))
		{
			Tools::safePostVars();
			$shipping = new GiveItShipping(Tools::getValue('edit_rule', null));
			$id_default_language = (int)Configuration::get('PS_LANG_DEFAULT');
			
			if (Tools::getValue('title_'.(int)$id_default_language) === '')
				$this->html .= $this->displayError($this->l('Error. Please always provide a shippng method title for you default language.'));
			else
			{
				foreach ($languages as $language)
					$shipping->title[$language['id_lang']] = pSQL(Tools::getValue('title_'.$language['id_lang']));
	
				$shipping->price = pSQL(Tools::getValue('shipping_price'));
				$shipping->free_above = pSQL(Tools::getValue('free_above'));
				$shipping->tax_percent = pSQL(Tools::getValue('tax_percent'));
				$shipping->id_currency = $this->context->currency->id;
				$shipping->iso_code = pSQL(Tools::getValue('iso_code'));
	
				if ((($error = $shipping->validateFields(false, true)) === true) && ($error = $shipping->validateFieldsLang(false, true)) === true)
				{
					if ($shipping->save())
					{
						$this->addFlashMessage($this->l('Shipping rule was successfully saved'));
						Tools::redirectAdmin(self::CURRENT_INDEX.Tools::getValue('token').'&menu='.Tools::getValue('menu'));
					}
					else
						$this->html .= $this->displayError($this->l('Error. Shipping rule could not be saved'));
				}
				else
					$this->html .= $this->displayError($error);
			}
		}

		if ($id_shipping_rule = Tools::getValue('edit_rule'))
		{
			$shipping = new GiveItShipping((int)$id_shipping_rule);
			$this->context->smarty->assign('shipping', $shipping);
		}

		if ($id_shipping_rule = Tools::getValue('delete_rule'))
		{
			$shipping = new GiveItShipping((int)$id_shipping_rule);

			if ($shipping->delete())
			{
				$this->addFlashMessage($this->l('Shipping rule was successfully deleted'));
				Tools::redirectAdmin(self::CURRENT_INDEX.Tools::getValue('token').'&menu='.Tools::getValue('menu'));
			} else
				$this->html .= $this->displayError($this->l('Error. Shipping rule could not be deleted'));
		}

		$this->context->smarty->assign(array(
			'currency' => $this->context->currency,
			'module' => $this,
			'id_lang_default' => Configuration::get('PS_LANG_DEFAULT'),
			'countries' => $countries,
			'languages' => $languages
		));

		$view = new GiveItShippingPricesView($this);
		$this->html .= $view->getPageContent();
	}

	private function testConnection()
	{
		if (Configuration::get(self::PUBLIC_KEY) === ''
		|| Configuration::get(self::PUBLIC_KEY) === false
		|| Configuration::get(self::PUBLIC_KEY) === null
		|| Configuration::get(self::PRIVATE_KEY) === ''
		|| Configuration::get(self::PRIVATE_KEY) === false
		|| Configuration::get(self::PRIVATE_KEY) === null
		|| Configuration::get(self::DATA_KEY) === ''
		|| Configuration::get(self::DATA_KEY) === false
		|| Configuration::get(self::DATA_KEY) === null)
			return false;
		return true;
	}

	/**
	 * module configuration page
	 * @return page HTML code
	 */

	public function getContent()
	{
		$this->checkConfigurationActions();
		$this->checkConfigurationCategoryActions();

		$this->displayFlashMessagesIfIsset();

		$menu = Tools::getValue('menu');

		if (!$this->testConnection())
		{
			$menu = 'technical_settings';
			$this->context->smarty->assign('base_dir', _PS_BASE_URL_.__PS_BASE_URI__);
			$this->context->smarty->assign('shop_name', Configuration::get('PS_SHOP_NAME'));
			$this->context->smarty->assign('first_name', Context::getContext()->employee->firstname);
			$this->context->smarty->assign('last_name', Context::getContext()->employee->lastname);
			$this->context->smarty->assign('email', Context::getContext()->employee->email);
			$this->context->smarty->assign('shop_id', Context::getContext()->shop->id);
			$default_currency = new Currency((int)Configuration::get('PS_CURRENCY_DEFAULT'));
			$this->context->smarty->assign('iso_code', $default_currency->iso_code);
		}

		$this->context->smarty->assign('menu', $menu);

		if (!version_compare(_PS_VERSION_, '1.5', '<'))
		{
			if (Shop::getContext() != Shop::CONTEXT_SHOP)
			{
				$this->addFlashWarning($this->l('It is not possible to manage module configuration when you are managing ALL SHOPS or a GROUP of shops. Please select a shop to configure the module.'));
				$this->displayFlashMessagesIfIsset();
				return $this->html;
			}
		}

		switch ($menu)
		{
			case 'configuration' :
			default :
				$this->context->smarty->assign('current_page_name', $this->l('Main settings'));
				$this->displayNavigation();
				$give_it_configuration_view_obj = new GiveItConfigurationView();
				$this->html .= $give_it_configuration_view_obj->getConfigurationForm();
				break;
			case 'configuration_category' :
				$this->context->smarty->assign('current_page_name', $this->l('Category settings'));
				$this->displayNavigation();
				$give_it_configuration_view_obj = new GiveItConfigurationView();
				$this->html .= $give_it_configuration_view_obj->getConfigurationCategoryForm();
				break;
			case 'configuration_product' :
				$this->context->smarty->assign('current_page_name', $this->l('Product settings'));
				$this->displayNavigation();
				if (!version_compare(_PS_VERSION_, '1.5', '<'))
				{
					if (Shop::getContext() != Shop::CONTEXT_SHOP)
					{
						$this->addFlashWarning($this->l('It is not possible to manage product settings when You are currently managing all of your shops or group of shops.'));
						$this->displayFlashMessagesIfIsset();
						break;
					}
				}
				$give_it_configuration_view_obj = new GiveItConfigurationView();
				$this->html .= $give_it_configuration_view_obj->getConfigurationProductForm();
				break;
			case 'shipping_prices' :
				$this->context->smarty->assign('current_page_name', $this->l('Shipping prices'));
				$this->displayNavigation();
				$this->displayShippingPricesPage();
				break;
			case 'help' :
				$this->context->smarty->assign('current_page_name', $this->l('Help'));
				$this->displayNavigation();
				$this->html .= $this->display(__FILE__, 'views/templates/admin/help.tpl');
				break;
		}
		return $this->html;
	}

	public static function addCSS($css_uri, $css_media_type = 'all')
	{
		$tmp = $css_media_type; // XXX: comply with PrestaShop validation warning
		echo '<link href="'.$css_uri.'" rel="stylesheet" type="text/css">';
	}

	public static function addJS($js_uri)
	{
		echo '<script src="'.$js_uri.'" type="text/javascript"></script>';
	}

	private function checkConfigurationActions()
	{
		if (Tools::isSubmit('saveApiKeys'))
			$this->saveConfiguration();

		if (Tools::isSubmit('saveConfiguration'))
			$this->saveConfiguration(true);
	}

	private function checkConfigurationCategoryActions()
	{
		if (Tools::isSubmit('saveCategorySettings'))
			$this->saveCategorySettings();
	}

	private function saveCategorySettings()
	{
		Tools::safePostVars();
		$selected_ids = Tools::getValue('categoryBox', array());

		$give_it_category_obj = new GiveItCategory();
		GiveItCategory::clearCategory();

		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			foreach ($selected_ids as $key => $id)
				if (!$this->saveSingleCategory((int)$id, (int)$this->context->shop->id))
					return $this->addFlashError($this->l('Could not save category settings'));

			return $this->addFlashMessage($this->l('Category settings saved successfully'));
		}

		$shop_context = Shop::getContext();

		foreach ($selected_ids as $key => $id)
		{
			if ($shop_context == Shop::CONTEXT_SHOP)
			{
				if (!$this->saveSingleCategory((int)$id, (int)$this->context->shop->id))
					$this->addFlashError($this->l('Could not save category settings'));
			} else {
				$id_shop_group = (Shop::getContext() == Shop::CONTEXT_GROUP) ? Shop::getContextShopGroupID() : null;
				$shop_ids = Shop::getShops(false, $id_shop_group, true);

				foreach ($shop_ids as $item => $id_shop)
				{
					if (!$this->saveSingleCategory((int)$id, (int)$id_shop))
						return $this->addFlashError($this->l('Could not save category settings'));
				}
			}

		}
		return $this->addFlashMessage($this->l('Category settings saved successfully'));
	}

	private function saveSingleCategory($id_category, $id_shop)
	{
		$give_it_category_obj = new GiveItCategory();
		$give_it_category_obj->id_category = (int)$id_category;
		$give_it_category_obj->id_shop = (int)$id_shop;
		return $give_it_category_obj->save();
	}

	private function saveConfiguration($save_all = false)
	{
		Tools::safePostVars();
		$public_key = Tools::getValue('public_key', '');
		$data_key = Tools::getValue('data_key', '');
		$private_key = Tools::getValue('private_key', '');

		Configuration::updateValue(self::PUBLIC_KEY, $public_key);
		Configuration::updateValue(self::DATA_KEY, $data_key);
		Configuration::updateValue(self::PRIVATE_KEY, $private_key);

		if ($save_all)
		{
			if (version_compare(_PS_VERSION_, '1.6', '>='))
				$button_active = (int)Tools::getValue('button_active');
			else
				$button_active = (int)Tools::isSubmit('button_active');

			Configuration::updateValue(self::BUTTON_ACTIVE, $button_active);
			if (Tools::getValue(self::MODE))
				Configuration::updateValue(self::MODE, Tools::getValue(self::MODE));

			$button_position = Tools::getValue('button_position');

			if (!in_array($button_position, $this->available_positions))
				return $this->addFlashError($this->l('Could not save button position'));

			Configuration::updateValue(self::BUTTON_POSITION, $button_position);
		}

		if (!$this->testConnection())
		{
			$this->html .= $this->displayError($this->l('Data is successfully saved, but your API keys appear to be invalid. Please check your keys.'));
			return;
		}
		else
			Configuration::updateValue('GIVEIT_CONFIGURATION_OK', true);

		$this->addFlashMessage($this->l('Settings were successfully saved'));
	}

	private function displayNavigation()
	{
		$this->context->smarty->assign(array('module_link' => $this->module_url, 'module_display_name' => $this->displayName, ));
		if ($this->testConnection())
		{
			if (version_compare(_PS_VERSION_, '1.6', '>='))
				$this->setNavigationVariables();

			$this->html .= $this->display(__FILE__, 'views/templates/admin/navigation.tpl');
		}
	}

	private function setNavigationVariables()
	{
		$meniu_tabs =  array(
			'configuration' => array(
				'short' => 'Main_settings',
				'desc' => $this->l('Main settings'),
				'href' => $this->module_url.'&menu=configuration',
				'active' => false,
				'imgclass' => 'icon-cog'
			),
			'configuration_category' => array(
				'short' => 'Category_settings',
				'desc' => $this->l('Category settings'),
				'href' => $this->module_url.'&menu=configuration_category',
				'active' => false,
				'imgclass' => 'icon-th-list'
			),
			'configuration_product' => array(
				'short' => 'Product_settings',
				'desc' => $this->l('Product settings'),
				'href' => $this->module_url.'&menu=configuration_product',
				'active' => false,
				'imgclass' => 'icon-th-large'
			),
			'shipping_prices' => array(
				'short' => 'Shipping_prices',
				'desc' => $this->l('Shipping prices'),
				'href' => $this->module_url.'&menu=shipping_prices',
				'active' => false,
				'imgclass' => 'icon-money'
			),
			'help' => array(
				'short' => 'Help',
				'desc' => $this->l('Help'),
				'href' => $this->module_url.'&menu=help',
				'active' => false,
				'imgclass' => 'icon-question-circle'
			),
		);

		$selected_tab = Tools::getValue('menu') ? Tools::getValue('menu') : 'configuration';
		$meniu_tabs[$selected_tab]['active'] = true;

		$this->context->smarty->assign(array(
			'meniutabs' => $meniu_tabs,
			'ps_16' => true
		));
	}

	/* adds success message into session */
	private function addFlashMessage($msg)
	{
		$give_it_data_with_cookie_manage_obj = new GiveItDataWithCookiesManager();
		$give_it_data_with_cookie_manage_obj->setSuccessMessage($msg);
	}

	public function addFlashWarning($msg)
	{
		$give_it_data_with_cookie_manage_obj = new GiveItDataWithCookiesManager();
		$give_it_data_with_cookie_manage_obj->setWarningMessage($msg);
	}

	private function addFlashError($msg)
	{
		$give_it_data_with_cookie_manage_obj = new GiveItDataWithCookiesManager();
		$give_it_data_with_cookie_manage_obj->setErrorMessage($msg);
	}

	/* displays success message only untill page reload */
	private function displayFlashMessagesIfIsset()
	{
		$give_it_data_with_cookie_manage_obj = new GiveItDataWithCookiesManager();

		if ($success_message = $give_it_data_with_cookie_manage_obj->getSuccessMessage())
			$this->html .= $this->displayConfirmation($success_message);

		if ($warning_message = $give_it_data_with_cookie_manage_obj->getWarningMessage())
			$this->html .= $this->displayWarnings($warning_message);

		if ($error_message = $give_it_data_with_cookie_manage_obj->getErrorMessage())
			$this->html .= $this->displayError($error_message);
	}

	private function displayErrors($errors)
	{
		$this->context->smarty->assign('errors', $errors);
		return $this->display(__FILE__, 'views/templates/admin/errors.tpl');
	}

	private function displayWarnings($warnings)
	{
		$this->context->smarty->assign('warnings', $warnings);
		return $this->display(__FILE__, 'views/templates/admin/warnings.tpl');
	}

	public function hookBackOfficeHeader($params)
	{
		$menu = Tools::getValue('menu');
		
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			return $this->display(__FILE__, 'views/templates/hook/backOfficeHeader.tpl');
		elseif (!version_compare(_PS_VERSION_, '1.6', '<'))
		{
			$this->context->controller->addCSS(_GIVEIT_CSS_URI_.'backoffice_16.css', 'all');
			$this->context->controller->addCSS(_GIVEIT_CSS_URI_.'backoffice.css', 'all');
			
			if ($menu == 'configuration_category' || $menu == 'configuration_product')
			{
				$this->context->controller->addJS(_MODULE_DIR_.$this->name.'/js/jquery.treeview-categories.js');
				$this->context->controller->addJS(_MODULE_DIR_.$this->name.'/js/admin-categories-tree.js');
				$this->context->controller->addJS(_MODULE_DIR_.$this->name.'/js/jquery.treeview-categories.edit.js');
				$this->context->controller->addJS(_MODULE_DIR_.$this->name.'/js/jquery.treeview-categories.async.js');
				
				$this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/css/jquery.treeview-categories.css');
			}
			
			if ((Tools::getValue('controller') == 'AdminProducts' || Tools::getValue('tab') == 'AdminCatalog') && (Tools::getValue('id_product')))
			{
				$this->context->controller->addJS(_MODULE_DIR_.$this->name.'/js/product.js');
				$this->context->controller->addJS(_MODULE_DIR_.$this->name.'/js/jquery.scrollTo.min.js');
			}
		}
		else
		{
			$this->context->controller->addCSS(_GIVEIT_CSS_URI_.'backoffice.css', 'all');
			
			if ($menu == 'configuration_category' || $menu == 'configuration_product')
			{
				$this->context->controller->addJS(_MODULE_DIR_.$this->name.'/js/jquery.treeview-categories.js');
				$this->context->controller->addJS(_MODULE_DIR_.$this->name.'/js/admin-categories-tree.js');
				$this->context->controller->addJS(_MODULE_DIR_.$this->name.'/js/jquery.treeview-categories.edit.js');
				$this->context->controller->addJS(_MODULE_DIR_.$this->name.'/js/jquery.treeview-categories.async.js');
				
				$this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/css/jquery.treeview-categories.css');
			}
			
			if ((Tools::getValue('controller') == 'AdminProducts' || Tools::getValue('tab') == 'AdminCatalog') && (Tools::getValue('id_product')))
			{
				$this->context->controller->addJS(_MODULE_DIR_.$this->name.'/js/product.js');
				$this->context->controller->addJS(_MODULE_DIR_.$this->name.'/js/jquery.scrollTo.min.js');
			}
		}
	}
}
