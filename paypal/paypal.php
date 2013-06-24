<?php
/*
* 2007-2013 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

include_once(_PS_MODULE_DIR_.'/paypal/api/paypal_lib.php');
include_once(_PS_MODULE_DIR_.'/paypal/paypal_logos.php');
include_once(_PS_MODULE_DIR_.'/paypal/paypal_orders.php');
include_once(_PS_MODULE_DIR_.'/paypal/paypal_tools.php');

define('WPS', 1);
define('HSS', 2);
define('ECS', 4);

define('TRACKING_CODE', 'FR_PRESTASHOP_H3S');
define('SMARTPHONE_TRACKING_CODE', 'Prestashop_Cart_smartphone_EC');
define('TABLET_TRACKING_CODE', 'Prestashop_Cart_tablet_EC');

define('_PAYPAL_LOGO_XML_', 'logos.xml');
define('_PAYPAL_MODULE_DIRNAME_', 'paypal');
define('_PAYPAL_TRANSLATIONS_XML_', 'translations.xml');

class PayPal extends PaymentModule
{
	protected $_html = '';

	public $_errors	= array();

	public $context;
	public $iso_code;
	public $default_country;

	public $paypal_logos;

	public $module_key = '646dcec2b7ca20c4e9a5aebbbad98d7e';

	const BACKWARD_REQUIREMENT = '0.4';
	const DEFAULT_COUNTRY_ISO = 'GB';

	const ONLY_PRODUCTS	= 1;
	const ONLY_DISCOUNTS = 2;
	const BOTH = 3;
	const BOTH_WITHOUT_SHIPPING	= 4;
	const ONLY_SHIPPING	= 5;
	const ONLY_WRAPPING	= 6;
	const ONLY_PRODUCTS_WITHOUT_SHIPPING = 7;

	public function __construct()
	{
		$this->name = 'paypal';
		$this->tab = 'payments_gateways';
		$this->version = '3.5.5';

		$this->currencies = true;
		$this->currencies_mode = 'radio';

		parent::__construct();

		$this->displayName = $this->l('PayPal');
		$this->description = $this->l('Accepts payments by credit cards (CB, Visa, MasterCard, Amex, Aurore, Cofinoga, 4 stars) with PayPal.');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details?');

		$this->page = basename(__FILE__, '.php');

		if (_PS_VERSION_ < '1.5')
		{
			$mobile_enabled = (int)Configuration::get('PS_MOBILE_DEVICE');
			require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
		}
		else
			$mobile_enabled = (int)Configuration::get('PS_ALLOW_MOBILE_DEVICE');

		if (self::isInstalled($this->name))
		{
			$this->loadDefaults();
			if ($mobile_enabled && $this->active)
				$this->checkMobileCredentials();
			elseif ($mobile_enabled && !$this->active)
				$this->checkMobileNeeds();
		}
		else
			$this->checkMobileNeeds();
	}

	public function install()
	{
		if (!parent::install() || !$this->registerHook('payment') || !$this->registerHook('paymentReturn') ||
		!$this->registerHook('shoppingCartExtra') || !$this->registerHook('backBeforePayment') || !$this->registerHook('rightColumn') ||
		!$this->registerHook('cancelProduct') || !$this->registerHook('productFooter') || !$this->registerHook('header') ||
		!$this->registerHook('adminOrder') || !$this->registerHook('backOfficeHeader'))
			return false;

		if ((_PS_VERSION_ >= '1.5') && (!$this->registerHook('displayMobileHeader') ||
		!$this->registerHook('displayMobileShoppingCartTop') || !$this->registerHook('displayMobileAddToCartTop')))
			return false;

		include_once(_PS_MODULE_DIR_.'/'.$this->name.'/paypal_install.php');
		$paypal_install = new PayPalInstall();
		$paypal_install->createTables();
		$paypal_install->updateConfiguration($this->version);
		$paypal_install->createOrderState();

		$paypal_tools = new PayPalTools($this->name);
		$paypal_tools->moveTopPayments(1);
		$paypal_tools->moveRightColumn(3);

		$this->runUpgrades(true);

		return true;
	}

	public function uninstall()
	{
		include_once(_PS_MODULE_DIR_.'/'.$this->name.'/paypal_install.php');
		$paypal_install = new PayPalInstall();
		$paypal_install->deleteConfiguration();
		return parent::uninstall();
	}

	/**
	 * Launch upgrade process
	 */
	public function runUpgrades($install = false)
	{
		if (file_exists(_PS_MODULE_DIR_.'/paypalapi/paypalapi.php') && !Configuration::get('PAYPAL_NEW'))
		{
			include_once(_PS_MODULE_DIR_.'/paypalapi/paypalapi.php');
			new PaypalAPI();

			if (_PS_VERSION_ < '1.5')
				foreach (array('2.8', '3.0') as $version)
				{
					$file = dirname(__FILE__).'/upgrade/install-'.$version.'.php';
					if (Configuration::get('PAYPAL_VERSION') < $version && file_exists($file))
					{
						include_once($file);
						call_user_func('upgrade_module_'.str_replace('.', '_', $version), $this, $install);
					}
				}
		}
	}

	private function compatibilityCheck()
	{
		if (file_exists(_PS_MODULE_DIR_.'/paypalapi/paypalapi.php') && $this->active)
			$this->warning = $this->l('All features of Paypal API module are included in the new Paypal module. In order to do not have any conflict, please do not use and remove PayPalAPI module.').'<br />';

		/* For 1.4.3 and less compatibility */
		$updateConfig = array('PS_OS_CHEQUE' => 1, 'PS_OS_PAYMENT' => 2, 'PS_OS_PREPARATION' => 3, 'PS_OS_SHIPPING' => 4,
		'PS_OS_DELIVERED' => 5, 'PS_OS_CANCELED' => 6, 'PS_OS_REFUND' => 7, 'PS_OS_ERROR' => 8, 'PS_OS_OUTOFSTOCK' => 9,
		'PS_OS_BANKWIRE' => 10, 'PS_OS_PAYPAL' => 11, 'PS_OS_WS_PAYMENT' => 12);

		foreach ($updateConfig as $key => $value)
			if (!Configuration::get($key) || (int)Configuration::get($key) < 1)
			{
				if (defined('_'.$key.'_') && (int)constant('_'.$key.'_') > 0)
					Configuration::updateValue($key, constant('_'.$key.'_'));
				else
					Configuration::updateValue($key, $value);
			}
	}

	public function isPayPalAPIAvailable()
	{
		$payment_method = Configuration::get('PAYPAL_PAYMENT_METHOD');

		if ($payment_method != HSS && !is_null(Configuration::get('PAYPAL_API_USER')) &&
		!is_null(Configuration::get('PAYPAL_API_PASSWORD')) && !is_null(Configuration::get('PAYPAL_API_SIGNATURE')))
			return true;
		elseif ($payment_method == HSS && !is_null(Configuration::get('PAYPAL_BUSINESS_ACCOUNT')))
			return true;

		return false;
	}

	/**
	 * Initialize default values
	 */
	protected function loadDefaults()
	{
		$this->loadLangDefault();
		$this->paypal_logos = new PayPalLogos($this->iso_code);
		$payment_method = Configuration::get('PAYPAL_PAYMENT_METHOD');
		$order_process_type = (int)Configuration::get('PS_ORDER_PROCESS_TYPE');

		if (Tools::getValue('paypal_ec_canceled') || $this->context->cart === false)
			unset($this->context->cookie->express_checkout);

		if (_PS_VERSION_ >= '1.5.0.2')
		{
			$version = Db::getInstance()->getValue('SELECT version FROM `'._DB_PREFIX_.'module` WHERE name = \''.$this->name.'\'');
			if (empty($version) === true)
			{
				Db::getInstance()->execute('
					UPDATE `'._DB_PREFIX_.'module` m
					SET m.version = \''.bqSQL($this->version).'\'
					WHERE m.name = \''.bqSQL($this->name).'\'');
			}
		}

		if (defined('_PS_ADMIN_DIR_'))
		{
			/* Backward compatibility */
			if (_PS_VERSION_ < '1.5')
				$this->backwardCompatibilityChecks();

			/* Upgrade and compatibility checks */
			$this->runUpgrades();
			$this->compatibilityCheck();
			$this->warningsCheck();
		}
		else
		{
			if (isset($this->context->cookie->express_checkout))
				$this->context->smarty->assign('paypal_authorization', true);

			if (($order_process_type == 1) && ((int)$payment_method == HSS) && !$this->useMobile())
				$this->context->smarty->assign('paypal_order_opc', true);
			elseif (($order_process_type == 1) && ((bool)Tools::getValue('isPaymentStep') == true))
			{
				$shop_url = PayPal::getShopDomainSsl(true, true);
				if (_PS_VERSION_ < '1.5')
				{
					$link = $shop_url._MODULE_DIR_.$this->name.'/express_checkout/payment.php';
					$this->context->smarty->assign('paypal_confirmation', $link.'?'.http_build_query(array('get_confirmation' => true), '', '&'));
				}
				else
				{
					$values = array('fc' => 'module', 'module' => 'paypal', 'controller' => 'confirm', 'get_confirmation' => true);
					$this->context->smarty->assign('paypal_confirmation', $shop_url.__PS_BASE_URI__.'?'.http_build_query($values));
				}
			}
		}
	}

	protected function checkMobileCredentials()
	{
		$payment_method = Configuration::get('PAYPAL_PAYMENT_METHOD');

		if (((int)$payment_method == HSS) && (
			(!(bool)Configuration::get('PAYPAL_API_USER')) &&
			(!(bool)Configuration::get('PAYPAL_API_PASSWORD')) &&
			(!(bool)Configuration::get('PAYPAL_API_SIGNATURE'))))
			$this->warning .= $this->l('You must set your PayPal Integral credentials in order to have the mobile theme work correctly.').'<br />';
	}

	protected function checkMobileNeeds()
	{
		$iso_code = Country::getIsoById((int)Configuration::get('PS_COUNTRY_DEFAULT'));
		$paypal_countries = array('ES', 'FR', 'PL', 'IT');

		if (method_exists($this->context->shop, 'getTheme'))
		{
			if (($this->context->shop->getTheme() == 'default') && in_array($iso_code, $paypal_countries))
				$this->warning .= $this->l('The mobile theme only works with the PayPal\'s payment module at this time. Please activate the module to enable payments.').'<br />';
		}
		else
			$this->warning .= $this->l('In order to use the module you need to install the backward compatibility.').'<br />';
	}

	/* Check status of backward compatibility module*/
	protected function backwardCompatibilityChecks()
	{
		if (Module::isInstalled('backwardcompatibility'))
		{
			$backward_module = Module::getInstanceByName('backwardcompatibility');
			if (!$backward_module->active)
				$this->warning .= $this->l('To work properly the module requires the backward compatibility module enabled').'<br />';
			elseif ($backward_module->version < PayPal::BACKWARD_REQUIREMENT)
				$this->warning .= $this->l('To work properly the module requires at least the backward compatibility module v').PayPal::BACKWARD_REQUIREMENT.'.<br />';
		}
		else
			$this->warning .= $this->l('In order to use the module you need to install the backward compatibility.').'<br />';
	}

	public function getContent()
	{
		$this->_postProcess();

		if (($id_lang = Language::getIdByIso('EN')) == 0)
			$english_language_id = (int)$this->context->employee->id_lang;
		else
			$english_language_id = (int)$id_lang;

		$this->context->smarty->assign(array(
			'PayPal_WPS' => (int)WPS,
			'PayPal_HSS' => (int)HSS,
			'PayPal_ECS' => (int)ECS,
			'PP_errors' => $this->_errors,
			'PayPal_logo' => $this->paypal_logos->getLogos(),
			'PayPal_allowed_methods' => $this->getPaymentMethods(),
			'PayPal_country' => Country::getNameById((int)$english_language_id, (int)$this->default_country),
			'PayPal_country_id' => (int)$this->default_country,
			'PayPal_business' => Configuration::get('PAYPAL_BUSINESS'),
			'PayPal_payment_method'	=> (int)Configuration::get('PAYPAL_PAYMENT_METHOD'),
			'PayPal_api_username' => Configuration::get('PAYPAL_API_USER'),
			'PayPal_api_password' => Configuration::get('PAYPAL_API_PASSWORD'),
			'PayPal_api_signature' => Configuration::get('PAYPAL_API_SIGNATURE'),
			'PayPal_api_business_account' => Configuration::get('PAYPAL_BUSINESS_ACCOUNT'),
			'PayPal_express_checkout_shortcut' => (int)Configuration::get('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT'),
			'PayPal_sandbox_mode' => (int)Configuration::get('PAYPAL_SANDBOX'),
			'PayPal_payment_capture' => (int)Configuration::get('PAYPAL_CAPTURE'),
			'PayPal_country_default' => (int)$this->default_country,
			'PayPal_change_country_url' => 'index.php?tab=AdminCountries&token='.Tools::getAdminTokenLite('AdminCountries').'#footer',
			'Countries'	=> Country::getCountries($english_language_id),
			'One_Page_Checkout'	=> (int)Configuration::get('PS_ORDER_PROCESS_TYPE'))
		);

		$this->getTranslations();

		$output = $this->fetchTemplate('/views/templates/back/back_office.tpl');

		if ($this->active == false)
			return $output.$this->hookBackOfficeHeader();

		return $output;
	}

	/**
	 * Hooks methods
	 */
	public function hookHeader()
	{
		if ($this->useMobile())
		{
			$id_hook = (int)Configuration::get('PS_MOBILE_HOOK_HEADER_ID');
			if ($id_hook > 0)
			{
				$module = Hook::getModuleFromHook($id_hook, $this->id);
				if (!$module)
					$this->registerHook('displayMobileHeader');
			}
		}

		if (isset($this->context->cart) && $this->context->cart->id)
			$this->context->smarty->assign('id_cart', (int)$this->context->cart->id);

		/* Added for PrestaBox */
		if (method_exists($this->context->controller, 'addCSS'))
			$this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/css/paypal.css');
		else
			Tools::addCSS(_MODULE_DIR_.$this->name.'/css/paypal.css');

		return '<script type="text/javascript">'.$this->fetchTemplate('paypal.js').'</script>';
	}

	public function hookDisplayMobileHeader()
	{
		return $this->hookHeader();
	}

	public function hookDisplayMobileShoppingCartTop()
	{
		return $this->renderExpressCheckoutButton('cart').$this->renderExpressCheckoutForm('cart');
	}

	public function hookDisplayMobileAddToCartTop()
	{
		return $this->renderExpressCheckoutButton('cart');
	}

	public function hookProductFooter()
	{
		$content = (!$this->useMobile()) ? $this->renderExpressCheckoutButton('product') : null;
		return $content.$this->renderExpressCheckoutForm('product');
	}

	public function hookPayment($params)
	{
		if (!$this->active)
			return;

		$use_mobile = $this->useMobile();

		if ($use_mobile)
			$method = ECS;
		else
			$method = (int)Configuration::get('PAYPAL_PAYMENT_METHOD');

		if (isset($this->context->cookie->express_checkout))
			$this->redirectToConfirmation();

		$this->context->smarty->assign(array(
			'logos' => $this->paypal_logos->getLogos(),
			'sandbox_mode' => Configuration::get('PAYPAL_SANDBOX'),
			'use_mobile' => $use_mobile,
			'PayPal_lang_code' => (isset($iso_lang[$this->context->language->iso_code])) ? $iso_lang[$this->context->language->iso_code] : 'en_US'
		));

		if ($method == HSS)
		{
			$billing_address = new Address($this->context->cart->id_address_invoice);
			$delivery_address = new Address($this->context->cart->id_address_delivery);
			$billing_address->country = new Country($billing_address->id_country);
			$delivery_address->country = new Country($delivery_address->id_country);
			$billing_address->state	= new State($billing_address->id_state);
			$delivery_address->state = new State($delivery_address->id_state);

			$cart = $this->context->cart;
			$cart_details = $cart->getSummaryDetails(null, true);

			if ((int)Configuration::get('PAYPAL_SANDBOX') == 1)
				$action_url = 'https://securepayments.sandbox.paypal.com/acquiringweb';
			else
				$action_url = 'https://securepayments.paypal.com/acquiringweb';

			$shop_url = PayPal::getShopDomainSsl(true, true);

			$this->context->smarty->assign(array(
				'action_url' => $action_url,
				'cart' => $cart,
				'cart_details' => $cart_details,
				'currency' => new Currency((int)$cart->id_currency),
				'customer' => $this->context->customer,
				'business_account' => Configuration::get('PAYPAL_BUSINESS_ACCOUNT'),
				'custom' => Tools::jsonEncode(array('id_cart' => $cart->id, 'hash' => sha1(serialize($cart->nbProducts())))),
				'gift_price' => (float)$this->getGiftWrappingPrice(),
				'billing_address' => $billing_address,
				'delivery_address' => $delivery_address,
				'shipping' => $cart_details['total_shipping_tax_exc'],
				'subtotal' => $cart_details['total_price_without_tax'] - $cart_details['total_shipping_tax_exc'],
				'time' => time(),
				'cancel_return' => $this->context->link->getPageLink('order.php'),
				'notify_url' => $shop_url._MODULE_DIR_.$this->name.'/integral_evolution/notifier.php',
				'return_url' => $shop_url._MODULE_DIR_.$this->name.'/integral_evolution/submit.php?id_cart='.(int)$cart->id,
				'tracking_code' => $this->getTrackingCode(), 
				'iso_code' => strtoupper($this->context->language->iso_code)
			));

			return $this->fetchTemplate('integral_evolution_payment.tpl');
		}
		elseif ($method == WPS || $method == ECS)
		{
			$this->getTranslations();
			$this->context->smarty->assign(array(
				'PayPal_integral' => WPS,
				'PayPal_express_checkout' => ECS,
				'PayPal_payment_method' => $method,
				'PayPal_payment_type' => 'payment_cart',
				'PayPal_current_page' => $this->getCurrentUrl(),
				'PayPal_tracking_code' => $this->getTrackingCode()));

			return $this->fetchTemplate('express_checkout_payment.tpl');
		}

		return null;
	}

	public function hookShoppingCartExtra()
	{
		// No active
		if (!$this->active || (((int)Configuration::get('PAYPAL_PAYMENT_METHOD') == HSS) && !$this->context->getMobileDevice()) ||
			!Configuration::get('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT') || !in_array(ECS, $this->getPaymentMethods()) || isset($this->context->cookie->express_checkout))
			return null;

		$values = array('en' => 'en_US', 'fr' => 'fr_FR');
		$this->context->smarty->assign(array(
			'PayPal_payment_type' => 'cart',
			'PayPal_current_page' => $this->getCurrentUrl(),
			'PayPal_lang_code' => (isset($values[$this->context->language->iso_code]) ? $values[$this->context->language->iso_code] : 'en_US'),
			'PayPal_tracking_code' => $this->getTrackingCode(),
			'include_form' => true,
			'template_dir' => dirname(__FILE__).'/views/templates/hook/'));

		return $this->fetchTemplate('express_checkout_shortcut_button.tpl');
	}

	public function hookPaymentReturn()
	{
		if (!$this->active)
			return null;

		return $this->fetchTemplate('confirmation.tpl');
	}

	public function hookRightColumn()
	{
		$this->context->smarty->assign('logo', $this->paypal_logos->getCardsLogo(true));
		return $this->fetchTemplate('column.tpl');
	}

	public function hookLeftColumn()
	{
		return $this->hookRightColumn();
	}

	public function hookBackBeforePayment($params)
	{
		if (!$this->active)
			return null;

		/* Only execute if you use PayPal API for payment */
		if (((int)Configuration::get('PAYPAL_PAYMENT_METHOD') != HSS) && $this->isPayPalAPIAvailable())
		{
			if ($params['module'] != $this->name || !$this->context->cookie->paypal_token || !$this->context->cookie->paypal_payer_id)
				return false;
			Tools::redirect('modules/'.$this->name.'/express_checkout/submit.php?confirm=1&token='.$this->context->cookie->paypal_token.'&payerID='.$this->context->cookie->paypal_payer_id);
		}
	}

	public function hookAdminOrder($params)
	{
		if (Tools::isSubmit('submitPayPalCapture'))
			$this->_doCapture($params['id_order']);
		elseif (Tools::isSubmit('submitPayPalRefund'))
			$this->_doTotalRefund($params['id_order']);

		$adminTemplates = array();
		if ($this->isPayPalAPIAvailable())
		{
			if ($this->_needValidation((int)$params['id_order']))
				$adminTemplates[] = 'validation';
			if ($this->_needCapture((int)$params['id_order']))
				$adminTemplates[] = 'capture';
			if ($this->_canRefund((int)$params['id_order']))
				$adminTemplates[] = 'refund';
		}

		if (count($adminTemplates) > 0)
		{
			$order = new Order((int)$params['id_order']);

			if (_PS_VERSION_ >= '1.5')
				$order_state = $order->current_state;
			else
				$order_state = OrderHistory::getLastOrderState($order->id);

			$this->context->smarty->assign(
				array(
					'authorization' => (int)Configuration::get('PAYPAL_OS_AUTHORIZATION'),
					'base_url' => _PS_BASE_URL_.__PS_BASE_URI__,
					'module_name' => $this->name,
					'order_state' => $order_state,
					'params' => $params,
					'ps_version' => _PS_VERSION_
				)
			);

			foreach ($adminTemplates as $adminTemplate)
			{
				$this->_html .= $this->fetchTemplate('/views/templates/back/admin_order/'.$adminTemplate.'.tpl');
				$this->_postProcess();
				$this->_html .= '</fieldset>';
			}
		}

		return $this->_html;
	}

	public function hookCancelProduct($params)
	{
		if (Tools::isSubmit('generateDiscount') || !$this->isPayPalAPIAvailable())
			return false;
		elseif ($params['order']->module != $this->name || !($order = $params['order']) || !Validate::isLoadedObject($order))
			return false;
		elseif (!$order->hasBeenPaid())
			return false;

		$order_detail = new OrderDetail((int)$params['id_order_detail']);
		if (!$order_detail || !Validate::isLoadedObject($order_detail))
			return false;

		$paypal_order = PayPalOrder::getOrderById((int)$order->id);
		if (!$paypal_order)
			return false;

		$products = $order->getProducts();
		$cancel_quantity = Tools::getValue('cancelQuantity');
		$message = $this->l('Cancel products result:').'<br>';

		$amount = (float)($products[(int)$order_detail->id]['product_price_wt'] * (int)$cancel_quantity[(int)$order_detail->id]);
		$refund = $this->_makeRefund($paypal_order->id_transaction, (int)$order->id, $amount);
		$this->formatMessage($refund, $message);
		$this->_addNewPrivateMessage((int)$order->id, $message);
	}

	public function hookBackOfficeHeader()
	{
		if ((int)strcmp((_PS_VERSION_ < '1.5' ? Tools::getValue('configure') : Tools::getValue('module_name')), $this->name) == 0)
		{
			if (_PS_VERSION_ < '1.5')
			{
				$output =  '<script type="text/javascript" src="'.__PS_BASE_URI__.'js/jquery/jquery-ui-1.8.10.custom.min.js"></script>
					<script type="text/javascript" src="'.__PS_BASE_URI__.'js/jquery/jquery.fancybox-1.3.4.js"></script>
					<link type="text/css" rel="stylesheet" href="'.__PS_BASE_URI__.'css/jquery.fancybox-1.3.4.css" />
					<link type="text/css" rel="stylesheet" href="'._MODULE_DIR_.$this->name.'/css/paypal.css" />';
			}
			else
			{
				$this->context->controller->addJquery();
				$this->context->controller->addJQueryPlugin('fancybox');
				$this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/css/paypal.css');
			}

			$this->context->smarty->assign(array(
				'PayPal_module_dir' => _MODULE_DIR_.$this->name,
				'PayPal_WPS' => (int)WPS,
				'PayPal_HSS' => (int)HSS,
				'PayPal_ECS' => (int)ECS
			));

			return (isset($output) ? $output : null).$this->fetchTemplate('/views/templates/back/header.tpl');
		}
		return null;
	}

	public function renderExpressCheckoutButton($type)
	{
		if ((!Configuration::get('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT') && !$this->useMobile()))
			return null;

		if (!in_array(ECS, $this->getPaymentMethods()) || (((int)Configuration::get('PAYPAL_BUSINESS') == 1) &&
		(int)Configuration::get('PAYPAL_PAYMENT_METHOD') == HSS) && !$this->useMobile())
			return null;

		$iso_lang = array(
			'en' => 'en_US',
			'fr' => 'fr_FR'
		);

		$this->context->smarty->assign(array(
			'use_mobile' => (bool) $this->useMobile(),
			'PayPal_payment_type' => $type,
			'PayPal_current_page' => $this->getCurrentUrl(),
			'PayPal_lang_code' => (isset($iso_lang[$this->context->language->iso_code])) ? $iso_lang[$this->context->language->iso_code] : 'en_US',
			'PayPal_tracking_code' => $this->getTrackingCode())
		);

		return $this->fetchTemplate('express_checkout_shortcut_button.tpl');
	}

	public function renderExpressCheckoutForm($type)
	{
		if ((!Configuration::get('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT') && !$this->useMobile()) || !in_array(ECS, $this->getPaymentMethods()) ||
		(((int)Configuration::get('PAYPAL_BUSINESS') == 1) && ((int)Configuration::get('PAYPAL_PAYMENT_METHOD') == HSS) && !$this->useMobile()))
			return;

		$this->context->smarty->assign(array(
			'PayPal_payment_type' => $type,
			'PayPal_current_page' => $this->getCurrentUrl(),
			'PayPal_tracking_code' => $this->getTrackingCode())
		);

		return $this->fetchTemplate('express_checkout_shortcut_form.tpl');
	}

	public function useMobile()
	{
		if ((method_exists($this->context, 'getMobileDevice') && $this->context->getMobileDevice()) || Tools::getValue('ps_mobile_site'))
			return true;
		return false;
	}

	public function getTrackingCode()
	{
		if ((_PS_VERSION_ < '1.5') && (_THEME_NAME_ == 'prestashop_mobile' || (isset($_GET['ps_mobile_site']) && $_GET['ps_mobile_site'] == 1)))
		{
			if (_PS_MOBILE_TABLET_)
				return TABLET_TRACKING_CODE;
			elseif (_PS_MOBILE_PHONE_)
				return SMARTPHONE_TRACKING_CODE;
		}
		if (isset($this->context->mobile_detect))
		{
			if ($this->context->mobile_detect->isTablet())
				return TABLET_TRACKING_CODE;
			elseif ($this->context->mobile_detect->isMobile())
				return SMARTPHONE_TRACKING_CODE;
		}
		return TRACKING_CODE;
	}

	public function getTranslations()
	{
		$file = dirname(__FILE__).'/'._PAYPAL_TRANSLATIONS_XML_;
		if (file_exists($file))
		{
			$xml = simplexml_load_file($file);
			if (isset($xml) && $xml)
			{
				$index = -1;
				$content = $default = array();

				while (isset($xml->country[++$index]))
				{
					$country = $xml->country[$index];
					$country_iso = $country->attributes()->iso_code;

					if (($this->iso_code != 'default') && ($country_iso == $this->iso_code))
						$content = (array)$country;
					elseif ($country_iso == 'default')
						$default = (array)$country;
				}

				$content += $default;
				$this->context->smarty->assign('PayPal_content', $content);

				return true;
			}
		}
		return false;
	}

	public function getPayPalURL()
	{
		return 'www'.(Configuration::get('PAYPAL_SANDBOX') ? '.sandbox' : '').'.paypal.com';
	}

	public function getPaypalIntegralEvolutionUrl()
	{
		if (Configuration::get('PAYPAL_SANDBOX'))
			return 'https://'.$this->getPayPalURL().'/cgi-bin/acquiringweb';
		return 'https://securepayments.paypal.com/acquiringweb?cmd=_hosted-payment';
	}

	public function getPaypalStandardUrl()
	{
		return 'https://'.$this->getPayPalURL().'/cgi-bin/webscr';
	}

	public function getAPIURL()
	{
		return 'api-3t'.(Configuration::get('PAYPAL_SANDBOX') ? '.sandbox' : '').'.paypal.com';
	}

	public function getAPIScript()
	{
		return '/nvp';
	}

	public function getCountryDependency($iso_code)
	{
		$localizations = array(
			'AU' => array('AU'), 'BE' => array('BE'), 'CN' => array('CN', 'MO'), 'CZ' => array('CZ'), 'DE' => array('DE'), 'ES' => array('ES'),
			'FR' => array('FR'), 'GB' => array('GB'), 'HK' => array('HK'), 'IL' => array('IL'), 'IN' => array('IN'), 'IT' => array('IT', 'VA'),
			'JP' => array('JP'), 'MY' => array('MY'), 'NL' => array('AN', 'NL'), 'NZ' => array('NZ'), 'PL' => array('PL'), 'PT' => array('PT', 'BR'),
			'RA' => array('AF', 'AS', 'BD', 'BN', 'BT', 'CC', 'CK', 'CX', 'FM', 'HM', 'ID', 'KH', 'KI', 'KN', 'KP', 'KR', 'KZ',	'LA', 'LK', 'MH',
				'MM', 'MN', 'MV', 'MX', 'NF', 'NP', 'NU', 'OM', 'PG', 'PH', 'PW', 'QA', 'SB', 'TJ', 'TK', 'TL', 'TM', 'TO', 'TV', 'TZ', 'UZ', 'VN',
				'VU', 'WF', 'WS'),
			'RE' => array('IE', 'ZA', 'GP', 'GG', 'JE', 'MC', 'MS', 'MP', 'PA', 'PY', 'PE', 'PN', 'PR', 'LC', 'SR', 'TT',
				'UY', 'VE', 'VI', 'AG', 'AR', 'CA', 'BO', 'BS', 'BB', 'BZ', 'CL', 'CO', 'CR', 'CU', 'SV', 'GD', 'GT', 'HN', 'JM', 'NI', 'AD', 'AE',
				'AI', 'AL', 'AM', 'AO', 'AQ', 'AT', 'AW', 'AX', 'AZ', 'BA', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BL', 'BM', 'BV', 'BW', 'BY', 'CD', 'CF', 'CG',
				'CH', 'CI', 'CM', 'CV', 'CY', 'DJ', 'DK', 'DM', 'DO', 'DZ', 'EC', 'EE', 'EG', 'EH', 'ER', 'ET', 'FI', 'FJ', 'FK', 'FO', 'GA', 'GE', 'GF',
				'GH', 'GI', 'GL', 'GM', 'GN', 'GQ', 'GR', 'GS', 'GU', 'GW', 'GY', 'HR', 'HT', 'HU', 'IM', 'IO', 'IQ', 'IR', 'IS', 'JO', 'KE', 'KM', 'KW',
				'KY', 'LB', 'LI', 'LR', 'LS', 'LT', 'LU', 'LV', 'LY', 'MA', 'MD', 'ME', 'MF', 'MG', 'MK', 'ML', 'MQ', 'MR', 'MT', 'MU', 'MW', 'MZ', 'NA',
				'NC', 'NE', 'NG', 'NO', 'NR', 'PF', 'PK', 'PM', 'PS', 'RE', 'RO', 'RS', 'RU', 'RW', 'SA', 'SC', 'SD', 'SE', 'SI', 'SJ', 'SK', 'SL',
				'SM', 'SN', 'SO', 'ST', 'SY', 'SZ', 'TC', 'TD', 'TF', 'TG', 'TN', 'UA', 'UG', 'VC', 'VG', 'YE', 'YT', 'ZM', 'ZW'),
			'SG' => array('SG'), 'TH' => array('TH'), 'TR' => array('TR'), 'TW' => array('TW'), 'US' => array('US'));

		foreach ($localizations as $key => $value)
			if (in_array($iso_code, $value))
				return $key;

		return $this->getCountryDependency(self::DEFAULT_COUNTRY_ISO);
	}

	public function getPaymentMethods()
	{
		// WPS -> Web Payment Standard
		// HSS -> Web Payment Pro / Integral Evolution
		// ECS -> Express Checkout Solution

		$paymentMethod = array('AU' => array(WPS, HSS, ECS), 'BE' => array(WPS, ECS), 'CN' => array(WPS, ECS), 'CZ' => array(), 'DE' => array(WPS),
		'ES' => array(WPS, HSS, ECS), 'FR' => array(WPS, HSS, ECS), 'GB' => array(WPS, HSS, ECS), 'HK' => array(WPS, HSS, ECS),
		'IL' => array(WPS, ECS), 'IN' => array(WPS, ECS), 'IT' => array(WPS, HSS, ECS), 'JP' => array(WPS, HSS, ECS), 'MY' => array(WPS, ECS),
		'NL' => array(WPS, ECS), 'NZ' => array(WPS, ECS), 'PL' => array(WPS, ECS), 'PT' => array(WPS, ECS), 'RA' => array(WPS, ECS), 'RE' => array(WPS, ECS),
		'SG' => array(WPS, ECS), 'TH' => array(WPS, ECS), 'TR' => array(WPS, ECS), 'TW' => array(WPS, ECS), 'US' => array(WPS, ECS),
		'ZA' => array(WPS, ECS));

		return isset($paymentMethod[$this->iso_code]) ? $paymentMethod[$this->iso_code] : $paymentMethod[self::DEFAULT_COUNTRY_ISO];
	}

	public function getCountryCode()
	{
		$cart = new Cart((int)$this->context->cookie->id_cart);
		$address = new Address((int)$cart->id_address_invoice);
		$country = new Country((int)$address->id_country);

		return $country->iso_code;
	}

	public function displayPayPalAPIError($message, $log = false)
	{
		$send = true;
		// Sanitize log
		foreach ($log as $key => $string)
		{
			if ($string == 'ACK -> Success')
				$send = false;
			elseif (substr($string, 0, 6) == 'METHOD')
			{
				$values = explode('&', $string);
				foreach ($values as $key2 => $value)
				{
					$values2 = explode('=', $value);
					foreach ($values2 as $key3 => $value2)
						if ($value2 == 'PWD' || $value2 == 'SIGNATURE')
							$values2[$key3 + 1] = '*********';
					$values[$key2] = implode('=', $values2);
				}
				$log[$key] = implode('&', $values);
			}
		}

		$this->context->smarty->assign(array('message' => $message, 'logs' => $log));

		if ($send)
		{
			$id_lang = (int)$this->context->cookie->id_lang;
			$iso_lang = Language::getIsoById($id_lang);

			if (!is_dir(dirname(__FILE__).'/mails/'.strtolower($iso_lang)))
				$id_lang = Language::getIdByIso('en');

			Mail::Send($id_lang, 'error_reporting', Mail::l('Error reporting from your PayPal module',
			(int)$this->context->cookie->id_lang), array('{logs}' => implode('<br />', $log)), Configuration::get('PS_SHOP_EMAIL'),
			null, null, null, null, null, _PS_MODULE_DIR_.$this->name.'/mails/');
		}

		return $this->fetchTemplate('error.tpl');
	}

	private function _canRefund($id_order)
	{
		if (!(bool)$id_order)
			return false;

		$paypal_order = Db::getInstance()->getRow('
			SELECT `payment_status`, `capture`
			FROM `'._DB_PREFIX_.'paypal_order`
			WHERE `id_order` = '.(int)$id_order);

		return $paypal_order && $paypal_order['payment_status'] == 'Completed' && $paypal_order['capture'] == 0;
	}

	private function _needValidation($id_order)
	{
		if (!(int)$id_order)
			return false;

		$order = Db::getInstance()->getRow('
			SELECT `payment_method`, `payment_status`
			FROM `'._DB_PREFIX_.'paypal_order`
			WHERE `id_order` = '.(int)$id_order);

		return $order && $order['payment_method'] != HSS && $order['payment_status'] == 'Pending_validation';
	}

	private function _needCapture($id_order)
	{
		if (!(int)$id_order)
			return false;

		$result = Db::getInstance()->getRow('
			SELECT `payment_method`, `payment_status`
			FROM `'._DB_PREFIX_.'paypal_order`
			WHERE `id_order` = '.(int)$id_order.' AND `capture` = 1');

		return $result && $result['payment_method'] != HSS && $result['payment_status'] == 'Pending_capture';
	}

	private function _preProcess()
	{
		if (Tools::isSubmit('submitPaypal'))
		{
			$business = Tools::getValue('business') !== false ? (int)Tools::getValue('business') : false;
			$payment_method = Tools::getValue('paypal_payment_method') !== false ? (int)Tools::getValue('paypal_payment_method') : false;
			$payment_capture = Tools::getValue('payment_capture') !== false ? (int)Tools::getValue('payment_capture') : false;
			$sandbox_mode = Tools::getValue('sandbox_mode') !== false ? (int)Tools::getValue('sandbox_mode') : false;

			if ($this->default_country === false || $sandbox_mode === false || $payment_capture === false || $business === false || $payment_method === false)
				$this->_errors[] = $this->l('Some fields are empty.');
			elseif (($business == 0 || ($business == 1 && $payment_method != HSS)) && (!Tools::getValue('api_username') || !Tools::getValue('api_password') || !Tools::getValue('api_signature')))
				$this->_errors[] = $this->l('Credentials fields cannot be empty');
			elseif ($business == 1 && $payment_method == HSS && !Tools::getValue('api_business_account'))
				$this->_errors[] = $this->l('Business e-mail field cannot be empty');
		}

		return !count($this->_errors);
	}

	private function _postProcess()
	{
		if (Tools::isSubmit('submitPaypal'))
		{
			if (Tools::getValue('paypal_country_only'))
				Configuration::updateValue('PAYPAL_COUNTRY_DEFAULT', (int)Tools::getValue('paypal_country_only'));
			elseif ($this->_preProcess())
			{
				Configuration::updateValue('PAYPAL_BUSINESS', (int)Tools::getValue('business'));
				Configuration::updateValue('PAYPAL_PAYMENT_METHOD', (int)Tools::getValue('paypal_payment_method'));
				Configuration::updateValue('PAYPAL_API_USER', trim(Tools::getValue('api_username')));
				Configuration::updateValue('PAYPAL_API_PASSWORD', trim(Tools::getValue('api_password')));
				Configuration::updateValue('PAYPAL_API_SIGNATURE', trim(Tools::getValue('api_signature')));
				Configuration::updateValue('PAYPAL_BUSINESS_ACCOUNT', trim(Tools::getValue('api_business_account')));
				Configuration::updateValue('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT', (int)Tools::getValue('express_checkout_shortcut'));
				Configuration::updateValue('PAYPAL_SANDBOX', (int)Tools::getValue('sandbox_mode'));
				Configuration::updateValue('PAYPAL_CAPTURE', (int)Tools::getValue('payment_capture'));

				$this->context->smarty->assign('PayPal_save_success', true);
			}
			else
			{
				$this->_html = $this->displayError(implode('<br />', $this->_errors)); // Not displayed at this time
				$this->context->smarty->assign('PayPal_save_failure', true);
			}
		}

		return $this->loadLangDefault();
	}

	private function _makeRefund($id_transaction, $id_order, $amt = false)
	{
		if (!$this->isPayPalAPIAvailable())
			die(Tools::displayError('Fatal Error: no API Credentials are available'));
		elseif (!$id_transaction)
			die(Tools::displayError('Fatal Error: id_transaction is null'));

		if (!$amt)
			$params = array('TRANSACTIONID' => $id_transaction, 'REFUNDTYPE' => 'Full');
		else
		{
			$isoCurrency = Db::getInstance()->getValue('
				SELECT `iso_code`
				FROM `'._DB_PREFIX_.'orders` o
				LEFT JOIN `'._DB_PREFIX_.'currency` c ON (o.`id_currency` = c.`id_currency`)
				WHERE o.`id_order` = '.(int)$id_order);

			$params = array('TRANSACTIONID'	=> $id_transaction,	'REFUNDTYPE' => 'Partial', 'AMT' => (float)$amt, 'CURRENCYCODE' => Tools::strtoupper($isoCurrency));
		}

		$paypal_lib	= new PaypalLib();

		return $paypal_lib->makeCall($this->getAPIURL(), $this->getAPIScript(), 'RefundTransaction', '&'.http_build_query($params, '', '&'));
	}

	public function _addNewPrivateMessage($id_order, $message)
	{
		if (!(bool)$id_order)
			return false;

		$new_message = new Message();
		$message = strip_tags($message, '<br>');

		if (!Validate::isCleanHtml($message))
			$message = $this->l('Payment message is not valid, please check your module.');

		$new_message->message = $message;
		$new_message->id_order = (int)$id_order;
		$new_message->private = 1;

		return $new_message->add();
	}

	private function _doTotalRefund($id_order)
	{
		$paypal_order = PayPalOrder::getOrderById((int)$id_order);
		if (!$this->isPayPalAPIAvailable() || !$paypal_order)
			return false;

		$order = new Order((int)$id_order);
		if (!Validate::isLoadedObject($order))
			return false;

		$products = $order->getProducts();
		$currency = new Currency((int)$order->id_currency);
		if (!Validate::isLoadedObject($currency))
			$this->_errors[] = $this->l('Not a valid currency');

		if (count($this->_errors))
			return false;

		$decimals = (is_array($currency) ? (int)$currency['decimals'] : (int)$currency->decimals) * _PS_PRICE_DISPLAY_PRECISION_;

		// Amount for refund
		$amt = 0.00;

		foreach ($products as $product)
			$amt += (float)($product['product_price_wt']) * ($product['product_quantity'] - $product['product_quantity_refunded']);
		$amt += (float)($order->total_shipping) + (float)($order->total_wrapping) - (float)($order->total_discounts);

		// check if total or partial
		if (Tools::ps_round($order->total_paid_real, $decimals) == Tools::ps_round($amt, $decimals))
			$response = $this->_makeRefund($paypal_order['id_transaction'], $id_order);
		else
			$response = $this->_makeRefund($paypal_order['id_transaction'], $id_order, (float)($amt));

		$message = $this->l('Refund operation result:').'<br>';
		foreach ($response as $key => $value)
			$message .= $key.': '.$value.'<br>';

		if (array_key_exists('ACK', $response) && $response['ACK'] == 'Success' && $response['REFUNDTRANSACTIONID'] != '')
		{
			$message .= $this->l('PayPal refund successful!');
			if (!Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'paypal_order` SET `payment_status` = \'Refunded\' WHERE `id_order` = '.(int)$id_order))
				die(Tools::displayError('Error when updating PayPal database'));

			$history = new OrderHistory();
			$history->id_order = (int)$id_order;
			$history->changeIdOrderState((int)Configuration::get('PS_OS_REFUND'), $history->id_order);
			$history->addWithemail();
			$history->save();
		}
		else
			$message .= $this->l('Transaction error!');

		$this->_addNewPrivateMessage((int)$id_order, $message);

		Tools::redirect($_SERVER['HTTP_REFERER']);
	}

	private function _doCapture($id_order)
	{
		$paypal_order = PayPalOrder::getOrderById((int)$id_order);
		if (!$this->isPayPalAPIAvailable() || !$paypal_order)
			return false;

		$order = new Order((int)$id_order);
		$currency = new Currency((int)$order->id_currency);

		$paypal_lib	= new PaypalLib();
		$response = $paypal_lib->makeCall($this->getAPIURL(), $this->getAPIScript(), 'DoCapture',
			'&'.http_build_query(array('AMT' => (float)$order->total_paid, 'AUTHORIZATIONID' => $paypal_order['id_transaction'],
			'CURRENCYCODE' => $currency->iso_code, 'COMPLETETYPE' => 'Complete'), '', '&'));
		$message = $this->l('Capture operation result:').'<br>';

		foreach ($response as $key => $value)
			$message .= $key.': '.$value.'<br>';

		if ((array_key_exists('ACK', $response)) && ($response['ACK'] == 'Success') && ($response['PAYMENTSTATUS'] == 'Completed'))
		{
			$order_history = new OrderHistory();
			$order_history->id_order = (int)$id_order;

			if (_PS_VERSION_ < '1.5')
				$order_history->changeIdOrderState(Configuration::get('PS_OS_WS_PAYMENT'), (int)$id_order);
			else
				$order_history->changeIdOrderState(Configuration::get('PS_OS_WS_PAYMENT'), $order);
			$order_history->addWithemail();
			$message .= $this->l('Order finished with PayPal!');
		}
		elseif (isset($response['PAYMENTSTATUS']))
			$message .= $this->l('Transaction error!');

		if (!Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'paypal_order`
			SET `capture` = 0, `payment_status` = \''.pSQL($response['PAYMENTSTATUS']).'\', `id_transaction` = \''.pSQL($response['TRANSACTIONID']).'\'
			WHERE `id_order` = '.(int)$id_order))
			die(Tools::displayError('Error when updating PayPal database'));

		$this->_addNewPrivateMessage((int)$id_order, $message);

		Tools::redirect($_SERVER['HTTP_REFERER']);
	}

	private function _updatePaymentStatusOfOrder($id_order)
	{
		if (!(bool)$id_order || !$this->isPayPalAPIAvailable())
			return false;

		$paypal_order = PayPalOrder::getOrderById((int)$id_order);
		if (!$paypal_order)
			return false;

		$paypal_lib	= new PaypalLib();
		$response = $paypal_lib->makeCall($this->getAPIURL(), $this->getAPIScript(), 'GetTransactionDetails',
			'&'.http_build_query(array('TRANSACTIONID' => $paypal_order->id_transaction), '', '&'));

		if (array_key_exists('ACK', $response))
		{
			if ($response['ACK'] == 'Success' && isset($response['PAYMENTSTATUS']))
			{
				$history = new OrderHistory();
				$history->id_order = (int)$id_order;

				if ($response['PAYMENTSTATUS'] == 'Completed')
					$history->changeIdOrderState(Configuration::get('PS_OS_PAYMENT'), (int)$id_order);
				elseif (($response['PAYMENTSTATUS'] == 'Pending') && ($response['PENDINGREASON'] == 'authorization'))
					$history->changeIdOrderState((int)(Configuration::get('PAYPAL_OS_AUTHORIZATION')), (int)$id_order);
				elseif ($response['PAYMENTSTATUS'] == 'Reversed')
					$history->changeIdOrderState(Configuration::get('PS_OS_ERROR'), (int)$id_order);
				$history->addWithemail();

				if (!Db::getInstance()->Execute('
				UPDATE `'._DB_PREFIX_.'paypal_order`
				SET `payment_status` = \''.pSQL($response['PAYMENTSTATUS']).($response['PENDINGREASON'] == 'authorization' ? '_authorization' : '').'\'
				WHERE `id_order` = '.(int)$id_order))
					die(Tools::displayError('Error when updating PayPal database'));
			}

			$message = $this->l('Verification status :').'<br>';
			$this->formatMessage($response, $message);
			$this->_addNewPrivateMessage((int)$id_order, $message);

			return $response;
		}

		return false;
	}

	public function fetchTemplate($name)
	{
		if (_PS_VERSION_ < '1.4')
			$this->context->smarty->currentTemplate = $name;
		elseif (_PS_VERSION_ < '1.5')
		{
			$views = 'views/templates/';
			if (@filemtime(dirname(__FILE__).'/'.$name))
				return $this->display(__FILE__, $name);
			elseif (@filemtime(dirname(__FILE__).'/'.$views.'hook/'.$name))
				return $this->display(__FILE__, $views.'hook/'.$name);
			elseif (@filemtime(dirname(__FILE__).'/'.$views.'front/'.$name))
				return $this->display(__FILE__, $views.'front/'.$name);
			elseif (@filemtime(dirname(__FILE__).'/'.$views.'back/'.$name))
				return $this->display(__FILE__, $views.'back/'.$name);
		}

		return $this->display(__FILE__, $name);
	}

	public static function getPayPalCustomerIdByEmail($email)
	{
		return Db::getInstance()->getValue('
			SELECT `id_customer`
			FROM `'._DB_PREFIX_.'paypal_customer`
			WHERE paypal_email = \''.pSQL($email).'\'');
	}

	public static function getPayPalEmailByIdCustomer($id_customer)
	{
		return Db::getInstance()->getValue('
			SELECT `paypal_email`
			FROM `'._DB_PREFIX_.'paypal_customer`
			WHERE `id_customer` = '.(int)$id_customer);
	}

	public static function addPayPalCustomer($id_customer, $email)
	{
		if (!PayPal::getPayPalEmailByIdCustomer($id_customer))
		{
			Db::getInstance()->Execute('
				INSERT INTO `'._DB_PREFIX_.'paypal_customer` (`id_customer`, `paypal_email`)
				VALUES('.(int)$id_customer.', \''.pSQL($email).'\')');

			return Db::getInstance()->Insert_ID();
		}

		return false;
	}

	private function warningsCheck()
	{
		if (Configuration::get('PAYPAL_PAYMENT_METHOD') == HSS && Configuration::get('PAYPAL_BUSINESS_ACCOUNT') == 'paypal@prestashop.com')
			$this->warning = $this->l('You are currently using the default PayPal e-mail address, please enter your own e-mail address.').'<br />';

		/* Check preactivation warning */
		if (Configuration::get('PS_PREACTIVATION_PAYPAL_WARNING'))
			$this->warning .= (!empty($this->warning)) ? ', ' : Configuration::get('PS_PREACTIVATION_PAYPAL_WARNING').'<br />';
	}

	private function loadLangDefault()
	{
		$paypal_country_default	= (int)Configuration::get('PAYPAL_COUNTRY_DEFAULT');
		$this->default_country	= ($paypal_country_default ? (int)$paypal_country_default : (int)Configuration::get('PS_COUNTRY_DEFAULT'));
		$this->iso_code	= $this->getCountryDependency(strtoupper($this->context->language->iso_code));
	}

	public function formatMessage($response, &$message)
	{
		foreach ($response as $key => $value)
			$message .= $key.': '.$value.'<br>';
	}

	private function checkCurrency($cart)
	{
		$currency_module = $this->getCurrency((int)$cart->id_currency);

		if ((int)$cart->id_currency == (int)$currency_module->id)
			return true;
		else
			return false;
	}

	public static function getShopDomainSsl($http = false, $entities = false)
	{
		if (method_exists('Tools', 'getShopDomainSsl'))
			return Tools::getShopDomainSsl($http, $entities);
		else
		{
			if (!($domain = Configuration::get('PS_SHOP_DOMAIN_SSL')))
				$domain = self::getHttpHost();
			if ($entities)
				$domain = htmlspecialchars($domain, ENT_COMPAT, 'UTF-8');
			if ($http)
				$domain = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$domain;
			return $domain;
		}
	}

	public function validateOrder($id_cart, $id_order_state, $amountPaid, $paymentMethod = 'Unknown', $message = null, $transaction = array(), $currency_special = null, $dont_touch_amount = false, $secure_key = false, Shop $shop = null)
	{
		if ($this->active)
		{
			// Set transaction details if pcc is defined in PaymentModule class_exists
			if (isset($this->pcc))
				$this->pcc->transaction_id = (isset($transaction['transaction_id']) ? $transaction['transaction_id'] : '');

			if (_PS_VERSION_ < '1.5')
				parent::validateOrder((int)$id_cart, (int)$id_order_state, (float)$amountPaid, $paymentMethod, $message, $transaction, $currency_special, $dont_touch_amount, $secure_key);
			else
				parent::validateOrder((int)$id_cart, (int)$id_order_state, (float)$amountPaid, $paymentMethod, $message, $transaction, $currency_special, $dont_touch_amount, $secure_key, $shop);

			if (count($transaction) > 0)
				PayPalOrder::saveOrder((int)$this->currentOrder, $transaction);
		}
	}

	protected function getGiftWrappingPrice()
	{
		if (_PS_VERSION_ >= '1.5')
			$wrapping_fees_tax_inc = $this->context->cart->getGiftWrappingPrice();
		else
		{
			$wrapping_fees = (float)(Configuration::get('PS_GIFT_WRAPPING_PRICE'));
			$wrapping_fees_tax = new Tax((int)(Configuration::get('PS_GIFT_WRAPPING_TAX')));
			$wrapping_fees_tax_inc = $wrapping_fees * (1 + (((float)($wrapping_fees_tax->rate) / 100)));
		}

		return (float)Tools::convertPrice($wrapping_fees_tax_inc, $this->context->currency);
	}

	public function redirectToConfirmation()
	{
		$shop_url = PayPal::getShopDomainSsl(true, true);

		// Check if user went through the payment preparation detail and completed it
		$detail = unserialize($this->context->cookie->express_checkout);

		if (!empty($detail['payer_id']) && !empty($detail['token']))
		{
			$values = array('get_confirmation' => true);
			$link = $shop_url._MODULE_DIR_.$this->name.'/express_checkout/payment.php';

			if (_PS_VERSION_ < '1.5')
				Tools::redirectLink($link.'?'.http_build_query($values, '', '&'));
			else
				Tools::redirect(Context::getContext()->link->getModuleLink('paypal', 'confirm', $values));
		}
	}

	protected function getCurrentUrl()
	{
		$protocol_link = Tools::usingSecureMode() ? 'https://' : 'http://';
		$request = $_SERVER['REQUEST_URI'];
		$pos = strpos($request, '?');
		
		if (($pos !== false) && ($pos >= 0))
			$request = substr($request, 0, $pos);

		$params = urlencode($_SERVER['QUERY_STRING']);

		return $protocol_link.Tools::getShopDomainSsl().$request.'?'.$params;
	}
}
