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
* @copyright  2007-2014 PrestaShop SA
* @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

require_once(_PS_MODULE_DIR_.'addshoppers/AddshoppersClient.php');

class Addshoppers extends Module
{
	protected $client;
	protected $output = '';

	public function __construct()
	{
		$this->name = 'addshoppers';
		$this->tab = 'advertising_marketing';
		$this->version = '1.1.4';
		$this->author = 'PrestaShop';
		$this->need_instance = 1;

		$this->client = new AddshoppersClient('prestashop');

		parent::__construct();

		if ($this->id && Configuration::get('ADDSHOPPERS_SHOP_ID') == '')
			$this->warning = $this->l('You have not provided your AddShoppers account details');

		$this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
		$this->displayName = $this->l('AddShoppers Social Sharing Buttons');
		$this->description = $this->l('FREE - Adds sharing buttons from Facebook, Twitter, Pinterest, and many more to increase sales from customer referrals. Tracks ROI.');
	}

	public function install()
	{
		if (!function_exists('curl_init'))
			$this->_errors[] = $this->l('AddShoppers needs the PHP Curl extension, please ask your hosting provider to enable it prior to install this module.');

		if (count($this->_errors) || !parent::install() || !$this->registerHook('productfooter') || !$this->registerHook('header') || !$this->registerHook('top') || !$this->registerHook('orderConfirmation'))
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!Configuration::deleteByName('ADDSHOPPERS_SHOP_ID') || !Configuration::deleteByName('ADDSHOPPERS_API_KEY') || !Configuration::deleteByName('ADDSHOPPERS_BUTTONS') ||
			!Configuration::deleteByName('ADDSHOPPERS_OPENGRAPH') || !Configuration::deleteByName('ADDSHOPPERS_EMAIL') || !parent::uninstall())
			return false;
		return true;
	}

	public function hookHeader($params)
	{
		$shop_id = $this->getShopId();
		$buttons_code = $this->client->getButtonsCode();

		$this->context->controller->addCSS($this->_path.'/static/css/shop.css', 'all');
		
		if (Configuration::get('ADDSHOPPERS_OPENGRAPH') == '1')
			$this->context->smarty->assign('buttons_opengraph', $buttons_code['buttons']['open-graph']);
		if (Configuration::get('ADDSHOPPERS_BUTTONS') == '1')
			$this->context->smarty->assign('buttons_social', $buttons_code['buttons']['button2']);

		$id_lang = (int)Tools::getValue('id_lang', (int)Configuration::get('PS_LANG_DEFAULT'));

		$this->context->smarty->assign(array(
			'shop_id' => Tools::safeOutput($shop_id),
			'default_account' => $shop_id == $this->client->getDefaultShopId(),
			'social' => Tools::safeOutput(Configuration::get('ADDSHOPPERS_BUTTONS')),
			'floating_buttons' => Tools::safeOutput(Configuration::get('ADDSHOPPERS_FLOATING_BUTTONS')),
			'opengraph' => Tools::safeOutput(Configuration::get('ADDSHOPPERS_OPENGRAPH')),
			'actual_url' => Tools::safeOutput($this->_getCurrentUrl()),
			'absolute_base_url' => Tools::safeOutput($this->_getAbsoluteBaseUrl()),
			'id_lang' => (int)$id_lang
		));

		if (Tools::isSubmit('id_product'))
		{
			$product = new Product((int)Tools::getValue('id_product'));
			if (Validate::isLoadedObject($product))
			{
				$currency = new Currency((int)$this->context->cookie->id_currency);

				$this->context->smarty->assign(array(
						'id_product' => (int)$product->id,
						'stock' => isset($product->available_now) ? Tools::safeOutput(AddshoppersClient::WIDGET_STOCK_IN_STOCK) : Tools::safeOutput(AddshoppersClient::WIDGET_STOCK_OUT_OF_STOCK),
						'price' => Tools::safeOutput($currency->sign).number_format((float)$product->getPrice(), 2),
						'product_name' => Tools::safeOutput($product->name[$id_lang]),
						'product_description' => Tools::safeOutput($product->description[$id_lang]),
						'is_product_page' => true
				));

				$quantity = (int)StockAvailable::getQuantityAvailableByProduct((int)$product->id);
				if ($quantity > 0)
					$this->context->smarty->assign('instock', (int)$quantity);

				$images = Image::getImages((int)$id_lang, (int)$product->id);
				$id_image = Product::getCover($product->id);
				// get Image by id
				if (sizeof($id_image) > 0)
				{
					$image = new Image($id_image['id_image']);
					// get image full URL
					$image_url = _PS_BASE_URL_._THEME_PROD_DIR_.$image->getExistingImgPath().".jpg";
					$this->context->smarty->assign('image_url', $image_url);
				}
			}
			else
				$this->context->smarty->assign('is_product_page', false);
		}
		else
			$this->context->smarty->assign('is_product_page', false);

		return $this->display(__FILE__, 'header.tpl');
	}

	public function hookOrderConfirmation($params)
	{
		$cart = new Cart((int)$params['objOrder']->id_cart);

		$this->context->smarty->assign(array('order' => (int)Order::getOrderByCartId((int)$params['objOrder']->id_cart),
				'value' => (float)$cart->getOrderTotal(), 'shop_id' => Tools::safeOutput($this->getShopId())));

		return $this->display(__FILE__, 'orderConfirmation.tpl');
	}

	public function hookProductFooter($params)
	{
		$product = new Product((int)Tools::getValue('id_product'));
		
		$lang_id = (int)(int)$this->context->cookie->id_lang;

		$images = $product->getImages($lang_id);

		foreach ($images as $image)
		{
			if ($image['cover'])
			{
				$cover = $image;
				$cover['id_image'] = Configuration::get('PS_LEGACY_IMAGES') ? ($this->product->id . '-' . $image['id_image']) : $image['id_image'];
			}
		}

		if (!isset($cover))
		{
			$cover = array('id_image' => $this->context->language->iso_code . '-default');
		}

		$prod = new stdClass;
		$prod->name = $product->name[$lang_id];
		$prod->description = $product->description[$lang_id];
		$prod->link_rewrite = $product->link_rewrite[$lang_id];
		$prod->price = $product->price;

		$this->context->smarty->assign(array('product' => $prod, 'cover' => $cover['id_image']));

		return $this->display(__FILE__, 'product.tpl');
	}

	public function getContent()
	{
		if (!function_exists('curl_init'))
			return '<div class="error">'.$this->l('AddShoppers needs the PHP Curl extension, please ask your hosting provider to enable it prior to use this module.').'</div>';

		$this->context->controller->addCSS($this->_path.'/static/css/admin.css', 'all');

		$this->_processRegistrationForm();
		$this->_processLoginForm();
		$this->_processKeysForm();
		$this->_processSettingsForm();

		$this->context->smarty->assign(array('output' => $this->_displayForm(),	'account_is_configured' => Configuration::get('ADDSHOPPERS_SHOP_ID') != ''));
		return $this->display(__FILE__, 'content.tpl');
	}

	protected function _displayGetStartedForm()
	{
		$categories = array(
			'Select Category', 'Apparel & Clothing', 'Arts & Antiques',
			'Automotive & Vehicles', 'Collectibles',	'Crafts & Hobbies',
			'Baby & Children',	'Business & Industrial', 'Cameras & Optics',
			'Electronics', 'Entertainment & Media', 'Food, Beverages, & Tobacco',
			'Furniture', 'General Merchandise', 'Gifts',
			'Hardware', 'Health & Beauty', 'Holiday',
			'Home & Garden', 'Jewelry', 'Luggage & Bags',
			'Mature / Adult', 'Music', 'Novelty',
			'Office Supplies', 'Pets & Animals', 'Software',
			'Sporting Goods & Outdoors', 'Toys & Games', 'Travel', 'Other'
		);

		foreach ($categories as &$category)
			$category = array('value' => $category, 'label' => $this->l($category));

		$this->context->smarty->assign(
			array(
				'action' => Tools::safeOutput($_SERVER['REQUEST_URI']),
				'email' => Tools::safeOutput(Tools::getValue('addshoppers_email')),
				'password' => Tools::safeOutput(Tools::getValue('addshoppers_password')),
				'confirmPassword' => Tools::safeOutput(Tools::getValue('addshoppers_confirm_password')),
				'category' => Tools::getValue('addshoppers_category'),
				'phone' => Tools::safeOutput(Tools::getValue('addshoppers_phone')),
				'categories' => $categories
			));

		$this->output .= $this->display(__FILE__, 'registrationForm.tpl');
		$this->output .= $this->display(__FILE__, 'loginForm.tpl');

		return $this->output;
	}

	protected function _displaySettingsForm()
	{
		$help_message = $this->l('These Apps are designed to work with default theme.') . ' ';
		$help_message .= $this->l('If you have another theme or would like further customizations, please <a href="%s" target="_blank">follow the instructions here</a>.');
		$help_message = sprintf($help_message, 'http://help.addshoppers.com/customer/portal/articles/692501--prestashop-installation-instructions');
		$help_message = html_entity_decode($help_message);

		$stay_tuned = $this->l('Follow us for updates on new features:');
		$stay_tuned = html_entity_decode($stay_tuned);

		$this->context->smarty->assign(
			array(
				'action' => Tools::safeOutput($_SERVER['REQUEST_URI']),
				'email' => $this->_getSafeRequestOrConfig('addshoppers_email', 'ADDSHOPPERS_EMAIL'),
				'api_key' => $this->_getSafeRequestOrConfig('addshoppers_api_key', 'ADDSHOPPERS_API_KEY'),
				'shop_id' => $this->_getSafeRequestOrConfig('addshoppers_shop_id', 'ADDSHOPPERS_SHOP_ID'),
				'buttons' => $this->_getSafeRequestOrConfig('addshoppers_buttons', 'ADDSHOPPERS_BUTTONS'),
				'floating_buttons' => $this->_getSafeRequestOrConfig('addshoppers_floating_buttons', 'ADDSHOPPERS_FLOATING_BUTTONS'),
				'opengraph' => $this->_getSafeRequestOrConfig('addshoppers_opengraph', 'ADDSHOPPERS_OPENGRAPH'),
				'password' => Tools::safeOutput(Tools::getValue('addshoppers_password')),
				'help_message' => $help_message,
				'stay_tuned' => $stay_tuned,
			));

		$this->output .= $this->display(__FILE__, 'keysForm.tpl');
		$this->output .= $this->display(__FILE__, 'settingsForm.tpl');

		return $this->output;
	}

	protected function _displayForm()
	{
		return Configuration::get('ADDSHOPPERS_SHOP_ID') == '' ? $this->_displayGetStartedForm() : $this->_displaySettingsForm();
	}

	protected function _processSettingsForm()
	{
		if (isset($_POST['addshoppers_settings']))
		{
			$this->_updateConfiguration(Tools::getValue('addshoppers_buttons'), Tools::getValue('addshoppers_opengraph'), Tools::getValue('addshoppers_floating_buttons'));
			$this->_prepareSuccess();
		}
	}

	protected function _processKeysForm()
	{
		if (isset($_POST['addshoppers_keys']))
		{
			Configuration::updateValue('ADDSHOPPERS_SHOP_ID', Tools::getValue('addshoppers_shop_id'));
			Configuration::updateValue('ADDSHOPPERS_API_KEY', Tools::getValue('addshoppers_api_key'));
			Configuration::updateValue('ADDSHOPPERS_EMAIL', Tools::getValue('addshoppers_email'));

			$this->_prepareSuccess();
		}
	}

	protected function _processLoginForm()
	{
		if (isset($_POST['addshoppers_login']))
		{
			$email = Tools::getValue('addshoppers_email');

			if ($email === false)
				return $this->_prepareError($this->l('Provide valid email address'));

			$response = $this->client->sendLoginRequest($_POST);
			if ($response['result'] == AddshoppersClient::LOGIN_ACCOUNT_CREATED || $response['result'] == AddshoppersClient::LOGIN_SITE_EXISTS)
			{
				Configuration::updateValue('ADDSHOPPERS_SHOP_ID', $response['shopid']);
				Configuration::updateValue('ADDSHOPPERS_API_KEY', $response['api_key']);
				Configuration::updateValue('ADDSHOPPERS_EMAIL', $email);

				$this->_updateConfiguration();

				return $this->_prepareSuccess($this->l('You were authenticated successfully'));
			}
			$this->_prepareError(sprintf($this->l('Error occured: %s'),	Tools::safeOutput($this->client->registrationMessages[(int)$response['result']])));
		}
	}

	protected function _processRegistrationForm()
	{
		if (isset($_POST['addshoppers_registration']))
		{
			$category = Tools::getValue('addshoppers_category');
			$email = Tools::getValue('addshoppers_email');
			$password = Tools::getValue('addshoppers_password');
			$confirm = Tools::getValue('addshoppers_confirm_password');

			if ($category === false)
				return $this->_prepareError($this->l('Please select a category'));

			if ($email === false)
				return $this->_prepareError($this->l('Provide valid email address'));

			if ($password === false || $password != $confirm)
				return $this->_prepareError($this->l('Passwords are not identical'));

			$response = $this->client->sendRegistrationRequest($_POST);
			if ($response['result'] == AddshoppersClient::REG_ACCOUNT_CREATED)
			{
				Configuration::updateValue('ADDSHOPPERS_SHOP_ID', $response['shopid']);
				Configuration::updateValue('ADDSHOPPERS_API_KEY', $response['api_key']);
				Configuration::updateValue('ADDSHOPPERS_EMAIL', $email);

				$this->_updateConfiguration();

				return $this->_prepareSuccess($this->l('Account successfully created'));
			}

			$this->_prepareError(sprintf($this->l('Error occured: %s'),	Tools::safeOutput($this->client->registrationMessages[(int)$response['result']])));
		}
	}

	protected function _updateConfiguration($buttons = 0, $opengraph = 0, $floating_buttons = 1)
	{
		Configuration::updateValue('ADDSHOPPERS_BUTTONS', $buttons);
		Configuration::updateValue('ADDSHOPPERS_OPENGRAPH', $opengraph);
		Configuration::updateValue('ADDSHOPPERS_FLOATING_BUTTONS', $floating_buttons);
	}

	protected function _prepareError($message = '')
	{
		$this->output .= sprintf('<div class="conf error">%s</div>', $message == '' ? $this->l('Error occured') : $message);
	}

	protected function _prepareSuccess($message = '')
	{
		$this->output .= sprintf('<div class="conf confirm">%s</div>', $message == '' ? $this->l('Settings updated') : $message);
	}

	protected function _getSafeRequestOrConfig($key, $configKey)
	{
		return Tools::safeOutput(Tools::getValue($key, Configuration::get($configKey)));
	}

	protected function _getCurrentUrl()
	{
		return $this->_getAbsoluteBaseUrl($_SERVER['REQUEST_URI']);
	}

	protected function _getAbsoluteBaseUrl($path = __PS_BASE_URI__)
	{
		$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://%1$s%3$s%2$s' : 'http://%1$s%2$s';

		return sprintf($url, htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8'), $path, $_SERVER['SERVER_PORT'] != '' ? ':' . $_SERVER['SERVER_PORT'] : '');
	}

	protected function getShopId()
	{
		$shop_id = Configuration::get('ADDSHOPPERS_SHOP_ID');

		return $shop_id == '' ? $this->client->getDefaultShopId() : $shop_id;
	}
}
