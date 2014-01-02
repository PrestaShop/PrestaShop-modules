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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

include_once(_PS_MODULE_DIR_.'/merchantware/class/call.php');
include_once(_PS_MODULE_DIR_.'/merchantware/class/token.php');

class MerchantWare extends PaymentModule
{
	private $_postErrors = array();
	private $_warnings = array();
	/**
	 * @brief Constructor
	 */
	public function __construct()
	{
		$this->name = 'merchantware';
		$this->tab = 'payments_gateways';
		$this->version = '1.2.3';
		$this->author = 'PrestaShop';
		$this->className = 'Merchantware';

		parent::__construct();

		$this->displayName = $this->l('Merchant Warehouse');
		$this->description = $this->l('Eliminate expensive and unnecessary gateway fees by partnering with Merchant Warehouse for your payment processing needs!');

		$this->confirmUninstall =	$this->l('Are you sure you want to delete your details?');
		if (!extension_loaded('soap'))
			$this->_warnings[] = $this->l('In order to use your module, please activate Soap (PHP extension)');
		if (!extension_loaded('openssl'))
			$this->_warnings[] = $this->l('In order to use your module, please activate OpenSsl (PHP extension)');
		if (!function_exists('curl_init'))
			$this->_warnings[] = $this->l('In order to use your module, please activate cURL (PHP extension)');

		/* Backward compatibility */
		require(_PS_MODULE_DIR_.'merchantware/backward_compatibility/backward.php');
		$this->context->smarty->assign('base_dir', __PS_BASE_URI__);
	}

	/**
	 * @brief Install method
	 *
	 * @return Success or failure
	 */
	public function install()
	{
		if (!parent::install() || !$this->registerHook('payment') || !$this->registerHook('adminOrder') ||
			!Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'merchant_ware_token` (
			  `id_cart` int(10) NOT NULL,
			  `token` varchar(20) DEFAULT NULL,
        `status` varchar(20) DEFAULT NULL,
			  PRIMARY KEY  (`id_cart`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;') || !Configuration::updateValue('MERCHANT_WARE_MODE', 'prod')) // prod | test
			return false;
		return true;
	}

	/**
	 * @brief Uninstall function
	 *
	 * @return Success or failure
	 */
	public function uninstall()
	{
		// Uninstall parent and unregister Configuration

		if (!parent::uninstall())
			return false;
		Configuration::deleteByName('MERCHANTWARE_MERCHANT_NAME');
		Configuration::deleteByName('MERCHANTWARE_SITE_ID');
		Configuration::deleteByName('MERCHANTWARE_KEY');
		Configuration::deleteByName('MW_LOGO');
		Configuration::deleteByName('MW_SCREENBACKGROUNDCOLOR');
		Configuration::deleteByName('MW_CONTAINERBACKGROUNDCOLOR');
		Configuration::deleteByName('MW_CONTAINERFONTCOLOR');
		Configuration::deleteByName('MW_CONTAINERHELPFONTCOLOR');
		Configuration::deleteByName('MW_CONTAINERBORDERCOLOR');
		Configuration::deleteByName('MW_LOGOBACKGROUNDCOLOR');
		Configuration::deleteByName('MW_LOGOBORDERCOLOR');
		Configuration::deleteByName('MW_TOOLTIPBACKGROUNDCOLOR');
		Configuration::deleteByName('MW_TOOLTIPBORDERCOLOR');
		Configuration::deleteByName('MW_TOOLTIPFONTCOLOR');
		Configuration::deleteByName('MW_TEXTBOXBACKGROUNDCOLOR');
		Configuration::deleteByName('MW_TEXTBOXBORDERCOLOR');
		Configuration::deleteByName('MW_TEXTBOXFOCUSBACKGROUNDCOLOR');
		Configuration::deleteByName('MW_TEXTBOXFOCUSBORDERCOLOR');

		return true;
	}

	/**
	 * @brief Main Form Method
	 *
	 * @return Rendered form
	 */
	public function getContent()
	{
		$html = '';
		if (version_compare(_PS_VERSION_,'1.5','>'))
			$this->context->controller->addJQueryPlugin('fancybox');
		else
			$html .= '<script type="text/javascript" src="'.__PS_BASE_URI__.'js/jquery/jquery.fancybox-1.3.4.js"></script>
		  	<link type="text/css" rel="stylesheet" href="'.__PS_BASE_URI__.'css/jquery.fancybox-1.3.4.css" />';
		if (count($this->_warnings))
			$html .= $this->_displayWarning();
		if (Tools::isSubmit('submitMerchantWare') || Tools::isSubmit('submitLayoutMerchantWare') || Tools::isSubmit('subscribeMerchantWare'))
		{
			$this->_postValidation();
			if (!count($this->_postErrors))
			{
				$this->_postProcess();
				$html .= $this->_displayValidation();
			}
			else
				$html .= $this->_displayErrors();
		}
		return $html.$this->_displayAdminTpl();
	}

	/**
	 * @brief Method that will displayed all the tabs in the configurations forms
	 *
	 * @return Rendered form
	 */
	private function _displayAdminTpl()
	{
		$this->context->smarty->assign(array(
				'tab' => array(
					'intro' => array(
						'title' => $this->l('Registration'),
						'content' => $this->_displayIntroTpl(),
						'icon' => '../modules/merchantware/img/registration.png',
						'tab' => 1,
						'selected' => (Tools::isSubmit('submitLayoutMerchantWare') || Tools::isSubmit('submitMerchantWare') ? false : true),
					),
					'credential' => array(
						'title' => $this->l('Credentials'),
						'content' => $this->_displayCredentialTpl(),
						'icon' => '../modules/merchantware/img/credentials.png',
						'tab' => 2,
						'selected' => (Tools::isSubmit('submitMerchantWare') ? true : false),
					),
					'layout' => array(
						'title' => $this->l('Layout'),
						'content' => $this->_displayLayoutTpl(),
						'icon' => '../modules/merchantware/img/layout.png',
						'tab' => 3,
						'selected' => (Tools::isSubmit('submitLayoutMerchantWare') ? true : false),
					),
				),
				'logo' => '../modules/merchantware/img/logo.png',
				'script' => array('../modules/merchantware/js/merchantware.js'),
				'css' => '../modules/merchantware/css/merchantware.css'
			));

		return $this->display(__FILE__, 'tpl/admin.tpl');
	}

	private function _displayIntroTpl()
	{
		$this->context->smarty->assign(array(
				'formCredential' => './index.php?tab=AdminModules&configure=merchantware&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.$this->tab.'&module_name=merchantware&subscribeMerchantWare',
				'states' => State::getStatesByIdCountry(Country::getByIso('US'))));
		return $this->display(__FILE__, 'tpl/intro.tpl');
	}

	/**
	 * @brief Credentials Form Method
	 *
	 * @return Rendered form
	 */
	private function _displayCredentialTpl()
	{
		$this->context->smarty->assign(array(
				'formCredential' => './index.php?tab=AdminModules&configure=merchantware&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.$this->tab.'&module_name=merchantware&submitMerchantWare',
				'credentialTitle' => $this->l('Log in'),
				'credentialText' => $this->l('In order to use this module, please fill out the form with the logins provided to you by Merchant Warehouse.'),
				'credentialInputVar' => array(
					'merchantName' => array(
						'name' => 'merchantName',
						'required' => true,
						'value' => (Tools::getValue('merchantName') ? Tools::safeOutput(Tools::getValue('merchantName')) : Tools::safeOutput(Configuration::get('MERCHANTWARE_MERCHANT_NAME'))),
						'type' => 'text',
						'label' => $this->l('Merchant Name'),
						'desc' => $this->l('The name of the business or organization owning the Merchantware account.'),
					),
					'merchantSiteId' => array(
						'name' => 'merchantSiteId',
						'required' => true,
						'value' => (Tools::getValue('merchantSiteId') ? Tools::safeOutput(Tools::getValue('merchantSiteId')) : Tools::safeOutput(Configuration::get('MERCHANTWARE_SITE_ID'))),
						'type' => 'text',
						'label' => $this->l('Merchant Site ID'),
						'desc' => $this->l('The site identifier of a location or storefront owned by the Merchantware account owner.'),
					),
					'merchantKey' => array(
						'name' => 'merchantKey',
						'required' => true,
						'value' => (Tools::getValue('merchantKey') ? Tools::safeOutput(Tools::getValue('merchantKey')) : Tools::safeOutput(Configuration::get('MERCHANTWARE_KEY'))),
						'type' => 'text',
						'label' => $this->l('Merchant Key'),
						'desc' => $this->l('The software key or password for the site accessing a Merchantware account.'),
					))));
		return $this->display(__FILE__, 'tpl/credential.tpl');
	}


	/**
	 * @brief Layout Form Method
	 *
	 * @return Rendered form
	 */
	private function _displayLayoutTpl()
	{
		$this->context->smarty->assign(array(
				'formLayout' => './index.php?tab=AdminModules&configure=merchantware&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.$this->tab.'&module_name=merchantware&submitLayout'.$this->className,
				'layoutTitle' => $this->l('Keyed view and Swipe view'),
				'layoutText' => $this->l('The Display Colors structure provides customization of certain display elements on the Keyed view and Swipe view. All of the fields must be a valid hexadecimal HTML color code in RRGGBB format.'),
				'layoutInputVar'=> array(
					'logo' => array($this->l('Logo'), $this->l('An SSL-secured URL pointing to an image file representing the shop\'s logo. The logo may be up to 430 pixels wide.'), Tools::safeOutput(Configuration::get('MW_LOGO'))),
					'screenBackgroundColor' => array($this->l('Screen Background Color'), $this->l('The background color of the screen.'), Tools::safeOutput(Configuration::get('MW_SCREENBACKGROUNDCOLOR'))),
					'containerBackgroundColor' => array($this->l('Container Background Color'), $this->l('The background color of all standard display boxes.'), Tools::safeOutput(Configuration::get('MW_CONTAINERBACKGROUNDCOLOR'))),
					'containerFontColor' => array($this->l('Container Font Color'), $this->l('The color of the text within all standard display boxes.'), Tools::safeOutput(Configuration::get('MW_CONTAINERFONTCOLOR'))),
					'containerHelpFontColor' => array($this->l('Container Help Font Color'), $this->l('The color of help and tooltip text within all standard display boxes.'), Tools::safeOutput(Configuration::get('MW_CONTAINERHELPFONTCOLOR'))),
					'containerBorderColor' => array($this->l('Container Border Color'), $this->l('The border color for all standard display boxes.'), Tools::safeOutput(Configuration::get('MW_CONTAINERBORDERCOLOR'))),
					'logoBackgroundColor' => array($this->l('Logo Background Color'), $this->l('The background color for the box that contains a merchant supplied logo.'), Tools::safeOutput(Configuration::get('MW_LOGOBACKGROUNDCOLOR'))),
					'logoBorderColor' => array($this->l('Logo Border Color'), $this->l('The border color for the box that contains a merchant supplied logo.'), Tools::safeOutput(Configuration::get('MW_LOGOBORDERCOLOR'))),
					'tooltipBackgroundColor' => array($this->l('Tooltip Background Color'), $this->l('The background color for hovering tooltips.'), Tools::safeOutput(Configuration::get('MW_TOOLTIPBACKGROUNDCOLOR'))),
					'tooltipBorderColor' => array($this->l('Tooltip Border Color'), $this->l('The border color for hovering tooltips.'), Tools::safeOutput(Configuration::get('MW_TOOLTIPBORDERCOLOR'))),
					'tooltipFontColor' => array($this->l('Tooltip Font Color'), $this->l('The font color for hovering tooltips.'), Tools::safeOutput(Configuration::get('MW_TOOLTIPFONTCOLOR'))),
					'textboxBackgroundColor' => array($this->l('Textbox Background Color'), $this->l('The background color for text boxes where users enter information that do not have focus.'), Tools::safeOutput(Configuration::get('MW_TEXTBOXBACKGROUNDCOLOR'))),
					'textboxBorderColor' => array($this->l('Textbox Border Color'), $this->l('The border color for text boxes where users enter information that do not have focus.'), Tools::safeOutput(Configuration::get('MW_TEXTBOXBORDERCOLOR'))),
					'textboxFocusBackgroundColor' => array($this->l('Textbox Focus Background Color'), $this->l('The background color for text boxes where users enter information that have focus.'), Tools::safeOutput(Configuration::get('MW_TEXTBOXFOCUSBACKGROUNDCOLOR'))),
					'textboxFocusBorderColor' => array($this->l('Textbox Focus Border Color'), $this->l('The background color for text boxes where users enter information that have focus.'), Tools::safeOutput(Configuration::get('MW_TEXTBOXFOCUSBORDERCOLOR'))),
					'textboxFontColor' => array($this->l('Textbox Font Color'), $this->l('The color of the text within a textbox.'), Tools::safeOutput(Configuration::get('MW_TEXTBOXFONTCOLOR'))))
			));
		return $this->display(__FILE__, 'tpl/layout.tpl');
	}

	/**
	 * @brief Validate Method
	 *
	 * @return update the module depending
	 */
	private function _postValidation()
	{
		if (Tools::isSubmit('submitMerchantWare'))
			$this->_postValidationCredentials();
		else if (Tools::isSubmit('subscribeMerchantWare'))
			$this->_postValidationSubscription();
		else
			$this->_postValidationLayout();
	}

	private function _postValidationCredentials()
	{
		$merchantName = Tools::getValue('merchantName');
		$merchantSiteId = Tools::getValue('merchantSiteId');
		$merchantKey = Tools::getValue('merchantKey');

		if ($merchantName == '' || $merchantSiteId == '' || $merchantKey == '')
			$this->_postErrors[] = $this->l('Please fill out the entire form.');
		if (Tools::strlen($merchantName) > 160)
			$this->_postErrors[] = $this->l('Your Merchant Name has to be less than 160 characters.');
		if (Tools::strlen($merchantSiteId) > 160 || Tools::strlen($merchantSiteId) < 8)
			$this->_postErrors[] = $this->l('Your Merchant Site ID has to be less than 160 characters and more than 8 characters.');
		if (Tools::strlen($merchantKey) > 160)
			$this->_postErrors[] = $this->l('Your Merchant Key has to be less than 160 characters.');
	}

	private function _postValidationLayout()
	{
		$fields = array(
			'screenBackgroundColor' => $this->l('Screen Background Color'),
			'containerBackgroundColor' => $this->l('Container Background Color'),
			'containerFontColor' => $this->l('Container Font Color'),
			'containerHelpFontColor' => $this->l('Container Help Font Color'),
			'containerBorderColor' => $this->l('Container Border Color'),
			'logoBackgroundColor' => $this->l('Logo Background Color'),
			'logoBorderColor' => $this->l('Logo Border Color'),
			'tooltipBackgroundColor' => $this->l('Tooltip Background Color'),
			'tooltipBorderColor' => $this->l('Tooltip Border Color'),
			'tooltipFontColor' => $this->l('Tooltip Font Color'),
			'textboxBackgroundColor' => $this->l('Textbox Background Color'),
			'textboxBorderColor' => $this->l('Textbox Border Color'),
			'textboxFocusBackgroundColor' => $this->l('Textbox Focus Background Color'),
			'textboxFocusBorderColor' => $this->l('Textbox Focus Border Color'),
			'textboxFontColor' => $this->l('Textbox Font Color'));

		if (isset($_POST['logo']))
			Configuration::updateValue('MW_LOGO', pSQL(Tools::getValue('logo')));

		foreach ($fields as $key => $val)
			if (isset($_POST[$key]))
			{
				if ($_POST[$key] != '' && preg_match('#^[0-9A-Fa-f]{3,6}$#', $_POST[$key]))
					Configuration::updateValue('MW_'.Tools::strtoupper($key), pSQL(Tools::getValue($key)));
				elseif ($_POST[$key] == '')
					Configuration::updateValue('MW_'.Tools::strtoupper($key), '');
				else
					$this->_postErrors[] = $val.' '.$this->l('must be a valid hexadecimal HTML color code in RRGGBB format.');
			}
	}

	private function _postValidationSubscription()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://api.prestashop.com/modules/script-merchant-warehouse.php');
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $_POST);

		$output = curl_exec($ch);
		curl_close($ch);
		if ($output != 'sent')
		{
			$error = str_replace(array('<li>', '</li>'), array('', ';'), $output);
			$this->_postErrors = explode(';', substr(Tools::safeOutput($error), 0, -1));
		}
	}

	private function _postProcess()
	{
		if (Tools::isSubmit('submitMerchantWare'))
			$this->_postProcessCredentials();
	}

	private function _postProcessCredentials()
	{
		Configuration::updateValue('MERCHANTWARE_MERCHANT_NAME', Tools::getValue('merchantName'));
		Configuration::updateValue('MERCHANTWARE_SITE_ID', Tools::getValue('merchantSiteId'));
		Configuration::updateValue('MERCHANTWARE_KEY', Tools::getValue('merchantKey'));
	}

	private function _displayErrors()
	{
		$this->context->smarty->assign('postErrors', $this->_postErrors);
		return $this->display(__FILE__, 'tpl/error.tpl');
	}

	private function _displayValidation()
	{
		$this->context->smarty->assign('postValidation', array($this->l('Updated succesfully')));
		return $this->display(__FILE__, 'tpl/validation.tpl');
	}

	private function _displayWarning()
	{
		$this->context->smarty->assign('warnings', $this->_warnings);
		return $this->display(__FILE__, 'tpl/warning.tpl');
	}

	/**
	 * @brief to display the payment option, so the customer will pay by merchant ware
	 */
	public function hookPayment($params)
	{
		if (!$this->active || Configuration::get('MERCHANTWARE_MERCHANT_NAME') == '' || Configuration::get('MERCHANTWARE_SITE_ID') == '' || Configuration::get('MERCHANTWARE_KEY') == '')
			return false;

		$this->context->smarty->assign(array('pathSsl' => (_PS_VERSION_ >= 1.4 ? Tools::getShopDomainSsl(true, true) : '' ).__PS_BASE_URI__.'modules/merchantware/', 'modulePath'=> $this->_path));

		return $this->display(__FILE__, 'tpl/payment.tpl');
	}

	/**
	 * @brief The merchant can cancel the order or refund the customer
	 */
	public function hookadminOrder($params)
	{
		if (!$this->active)
			return false;

		$order = new Order($params['id_order']);
		$cart = new Cart($order->id_cart);
		$tokenTransaction = new TokenTransaction($cart->id);
		$token = $tokenTransaction->getToken();

		if ($token == null)
			return false;

		if ($order->getCurrentState() == Configuration::get('PS_OS_CANCELED') && $tokenTransaction->getStatus() != 'CANCEL')
		{
			$call = new Call();
			try
			{
				$result = $call->voidTransaction($token);
			}
			catch (Exception $e)
			{
				$this->context->smarty->assign('error', Tools::safeOutput($e->getMessage()));
			}
			if (isset($result->VoidResult->ApprovalStatus) && strrpos($result->VoidResult->ApprovalStatus, 'APPROVED') !== false)
			{
				$tokenTransaction->setStatus('CANCEL');
				$this->context->smarty->assign('message', $this->l('Action succeded.'));
			}
			else
				$this->context->smarty->assign('error', (isset($result->VoidResult->ApprovalStatus) ? Tools::safeOutput($result->VoidResult->ApprovalStatus) : $this->l('ERROR, please contact MerchantWare for Support assistance.')));
		}

		if ($order->getCurrentState() == Configuration::get('PS_OS_REFUND') && $tokenTransaction->getStatus() != 'REFUND')
		{
			$call = new Call();
			try
			{
				$result = $call->refundTransaction($token, $order->total_paid_real);
			}
			catch (Exception $e)
			{
				$this->context->smarty->assign('error', Tools::safeOutput($e->getMessage()));
			}
			if (isset($result->RefundResult->ApprovalStatus) && strrpos($result->RefundResult->ApprovalStatus, 'APPROVED') !== false)
			{
				$tokenTransaction->setStatus('REFUND');
				$this->context->smarty->assign('message', $this->l('Action succeded.'));
			}
			else
				$this->context->smarty->assign('error', (isset($result->RefundResult->ApprovalStatus) ? Tools::safeOutput($result->RefundResult->ApprovalStatus) : $this->l('ERROR, please contact Merchant Warehouse for Support assistance.')));
		}

		return $this->display(__FILE__, 'tpl/adminOrder.tpl');
	}

	/**
	 * @brief Validate a payment, verify if everything is right
	 */
	public function validation()
	{
		$token = (int)Tools::getValue('Token');
		$id_cart = (int)Tools::getValue('TransactionID');

		$link = new Link();

		$this->context->cart = new Cart($id_cart);
		$this->context->link = $link;

		if (Validate::isLoadedObject($this->context->cart))
		{
			$call = new Call();
			try
			{
				$result = $call->getTransaction($token);
			}
			catch (Exception $e)
			{
				Logger::AddLog('[MerchantWare] Problem to verify a payment. Cart id: '.$id_cart.', token: '.$token.'.', 2);
			}
			if (isset($result->TransactionsByReferenceResult->TransactionReference4->ApprovalStatus))
			{
				if ($result->TransactionsByReferenceResult->TransactionReference4->ApprovalStatus == 'APPROVED')
				{
					$amount = str_replace(',', '', $result->TransactionsByReferenceResult->TransactionReference4->Amount);
					$tokenTransaction = new TokenTransaction((int)$this->context->cart->id);
					$tokenTransaction->setToken($token);
					$this->validateOrder((int)$this->context->cart->id, Configuration::get('PS_OS_PAYMENT'), $amount, 'merchantware', NULL, array(), NULL, false,	$this->context->cart->secure_key);
				}
				else
					$this->validateOrder((int)$this->context->cart->id, Configuration::get('PS_OS_ERROR'), $amount, 'merchantware', NULL, array(), NULL, false,	$this->context->cart->secure_key);
			}
			else
				Logger::AddLog('[MerchantWare] Problem to verify a payment. Cart id: '.(int)$id_cart.', token: '.Tools::safeOutput($token).'.', 2);
		}
		else
			Logger::AddLog('[MerchantWare] The Shopping cart #'.(int)$id_cart.' was not found during the payment validation step.', 2);

		$url = 'index.php?controller=order-confirmation&';
		if (_PS_VERSION_ < '1.5')
			$url = 'order-confirmation.php?';

		header('location:'.__PS_BASE_URI__.$url.'id_module='.(int)$this->id.'&id_cart='.(int)$this->context->cart->id.'&key='.$this->context->customer->secure_key);
		exit;
	}
}
