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
*  @license	http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class alliance3 extends PaymentModule
{
	private $_postErrors = array();
	private $_warnings = array();
	
	/**
	 * @brief Constructor
	 */
	public function __construct()
	{
		$this->name = 'alliance3';
		$this->tab = 'payments_gateways';
		$this->version = '1.0.0';
		$this->author = 'go4.fi';
		$this->className = 'alliance3';
		
		parent::__construct();

		$this->displayName = $this->l('Alliance Processing');
		$this->description = $this->l('Experienced leader in High Risk ecommerce merchants with both  onshore and offshore solutions.');

		$this->confirmUninstall =	$this->l('Are you sure you want to delete your details?');
		if (!extension_loaded('soap'))
			$this->_warnings[] = $this->l('In order to use your module, please activate Soap (PHP extension)');
		if (!extension_loaded('openssl'))
			$this->_warnings[] = $this->l('In order to use your module, please activate OpenSsl (PHP extension)');
		if (!function_exists('curl_init'))
			$this->_warnings[] = $this->l('In order to use your module, please activate cURL (PHP extension)');
		
		/* Backward compatibility */
		require(_PS_MODULE_DIR_.'alliance3/backward_compatibility/backward.php');
		$this->context->smarty->assign('base_dir', __PS_BASE_URI__);
	}

	/**
	 * @brief Install method
	 *
	 * @return Success or failure
	 */
	public function install()
	{
		return parent::install() &&
			$this->registerHook('orderConfirmation') &&
			$this->registerHook('payment') &&
			$this->registerHook('header') &&
			Configuration::updateValue('ALLIANCE_DEMO', 1) &&
			Configuration::updateValue('ALLIANCE_HOLD_REVIEW_OS', _PS_OS_ERROR_);
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

		Configuration::deleteByName('ALLIANCE_DEMO');
		Configuration::deleteByName('ALLIANCE_HOLD_REVIEW_OS');
		Configuration::deleteByName('ALLIANCE_LOGIN_ID');
		Configuration::deleteByName('ALLIANCE_KEY');

		return true;
	}

	/**
	 * @brief Main Form Method
	 *
	 * @return Rendered form
	 */
	public function hookBackOfficeHeader()
	{
		if (version_compare(_PS_VERSION_, '1.5', '>='))
			$this->context->controller->addJQueryPlugin('fancybox');
		else
			return '<script type="text/javascript" src="'.__PS_BASE_URI__.'js/jquery/jquery.fancybox-1.3.4.js"></script>
			 	<link type="text/css" rel="stylesheet" href="'.__PS_BASE_URI__.'css/jquery.fancybox-1.3.4.css" />';
	}

	public function getContent()
	{
		$html = '';
		
		if (count($this->_warnings))
			$html .= $this->_displayWarning();
		
		if (Tools::isSubmit('submitMerchantWare') || Tools::isSubmit('submitLayoutMerchantWare') || Tools::isSubmit('subscribeMerchantWare'))
		{
			$this->_postValidation();
			if (!count($this->_postErrors))
			{
				if (Tools::isSubmit('submitMerchantWare'))
					$this->_postProcessCredentials();
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
		$this->context->smarty->assign(
			array(
				'tab' => array(
					'intro' => array(
						'title' => $this->l('Registration'),
						'content' => $this->_displayIntroTpl(),
						'icon' => '../modules/alliance3/img/registration.png',
						'tab' => 1,
						'selected' => (Tools::isSubmit('subscribeMerchantWare') || Tools::isSubmit('subscribeMerchantWare') ? false : true)&&((!Tools::isSubmit('submitMerchantWare') ? true : false)&&(!Tools::isSubmit('submitLayoutMerchantWare') ? true : false)),
					),
					'credential' => array(
						'title' => $this->l('Credit Card Credentials'),
						'content' => $this->_displayCredentialTpl(),
						'icon' => '../modules/alliance3/img/credentials.png',
						'tab' => 2,
						'selected' => (Tools::isSubmit('submitMerchantWare') ? true : false),
					),
					'layout' => array(
						'title' => $this->l('ACH Credentials'),
						'content' => $this->_displayLayoutTpl(),
						'icon' => '../modules/alliance3/img/layout.png',
						'tab' => 3,
						'selected' => (Tools::isSubmit('submitLayoutMerchantWare') ? true : false),
					),
				),
				'logo' => '../modules/alliance3/img/logo.png',
				'script' => array('../modules/alliance3/js/alliance3.js'),
				'css' => '../modules/alliance3/css/alliance3.css'
			)
		);

		return $this->display(__FILE__, 'views/templates/admin/admin.tpl');
	}

	private function _displayIntroTpl()
	{
		$this->context->smarty->assign(
			array(
				'formCredential' => './index.php?tab=AdminModules&configure=alliance3&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.$this->tab.'&module_name=alliance3&subscribeMerchantWare',
				'states' => State::getStatesByIdCountry(Country::getByIso('US'))
			)
		);
		
		return $this->display(__FILE__, 'views/templates/admin/intro.tpl');
	}

	/**
	 * @brief Credentials Form Method
	 *
	 * @return Rendered form
	 */
	private function _displayCredentialTpl()
	{
		$orderStates = OrderState::getOrderStates((int)$this->context->cookie->id_lang);
		if(Configuration::get('ALLIANCE_CARD_DISCOVER')!='off')
			$ALLIANCE_CARD_DISCOVER='on';
		else 
			$ALLIANCE_CARD_DISCOVER=Configuration::get('ALLIANCE_CARD_DISCOVER');
		
		if(Configuration::get('ALLIANCE_CARD_VISA')!='off')
			$ALLIANCE_CARD_VISA='on';
		else 
			$ALLIANCE_CARD_VISA=Configuration::get('ALLIANCE_CARD_VISA');
		
		if(Configuration::get('ALLIANCE_CARD_MASTERCARD')!='off')
			$ALLIANCE_CARD_MASTERCARD='on';
		else 
			$ALLIANCE_CARD_MASTERCARD=Configuration::get('ALLIANCE_CARD_MASTERCARD');
		
		if(Configuration::get('ALLIANCE_CARD_AX')!='off')
			$ALLIANCE_CARD_AX='on';
		else 
			$ALLIANCE_CARD_AX=Configuration::get('ALLIANCE_CARD_AX');

		$this->context->smarty->assign(
			array(
				'formCredential' => './index.php?tab=AdminModules&configure=alliance3&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.$this->tab.'&module_name=alliance3&submitMerchantWare',
				'credentialTitle' => $this->l('Log in'),
				'credentialText' => $this->l('In order to use this module, please fill out the form with the logins provided to you by Alliance Processing.'),
					'ALLIANCE_ENABLE'=>Tools::safeOutput(Configuration::get('ALLIANCE_ENABLE')),
					'ALLIANCE_LOGIN_ID'=>Tools::safeOutput(Configuration::get('ALLIANCE_LOGIN_ID')),
					'ALLIANCE_KEY'=>Tools::safeOutput(Configuration::get('ALLIANCE_KEY')),
					'ALLIANCE_DEMO'=>Tools::safeOutput(Configuration::get('ALLIANCE_DEMO')),
				'ALLIANCE_CARD_AX'=>$ALLIANCE_CARD_AX,
				'ALLIANCE_CARD_VISA'=>$ALLIANCE_CARD_VISA,
				'ALLIANCE_CARD_MASTERCARD'=>$ALLIANCE_CARD_MASTERCARD,
				'ALLIANCE_CARD_DISCOVER'=>$ALLIANCE_CARD_DISCOVER,
				'ALLIANCE_HOLD_REVIEW_OS'=>Configuration::get('ALLIANCE_HOLD_REVIEW_OS'),
				'os_order'=>$orderStates,
				'credentialInputVar' => array(
					'merchantKey' => array(
						'name' => 'merchantKey',
						'required' => true,
						'value' => (Tools::getValue('merchantKey') ? Tools::safeOutput(Tools::getValue('merchantKey')) : Tools::safeOutput(Configuration::get('MERCHANTWARE_KEY'))),
						'type' => 'text',
						'label' => $this->l('Merchant Key'),
						'desc' => $this->l('The software key or password for the site accessing a Merchantware account.'),
					)
				)
			)
		);
		
		return $this->display(__FILE__, 'views/templates/admin/credential.tpl');
	}

	/**
	 * @brief Layout Form Method
	 *
	 * @return Rendered form
	 */
	private function _displayLayoutTpl()
	{
		$this->context->smarty->assign(
			array(
				'formLayout' => './index.php?tab=AdminModules&configure=alliance3&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.$this->tab.'&module_name=alliance3&submitLayoutMerchantWare',
				'layoutTitle' => $this->l('ACH / Electronic Checking Processing  Credentials'),
				'layoutText' => $this->l('Please enter you ACH/Electronic Check Processing credentials provided to you by Alliance Processing.	Please remember, Orders on which ACH processing has occurred must be manually set to “Payment Approved” status within your shopping cart.  ACH payments generally clear within 3 to 5 business days, and can be validated through you ACH gateway.'),
 				'ALLIANCEACH_ENABLE'=>Tools::safeOutput(Configuration::get('ALLIANCEACH_ENABLE')),
				'ALLIANCEACH_LOGIN'=>Configuration::get('ALLIANCEACH_LOGIN'),
				'ALLIANCEACH_PASS'=>Configuration::get('ALLIANCEACH_PASS'),
				'ALLIANCEACH_TERMINAL'=>Configuration::get('ALLIANCEACH_TERMINAL'),
				'ALLIANCEACH_IDENTITY'=>Configuration::get('ALLIANCEACH_IDENTITY'),
				'ALLIANCEACH_DRIVER'=>Configuration::get('ALLIANCEACH_DRIVER'),
			)
		);
		
		return $this->display(__FILE__, 'views/templates/admin/layout.tpl');
	}

	/**
	 * @brief Validate Method
	 *
	 * @return update the module depending
	 */
	private function _postValidation()
	{
		if (Tools::isSubmit('subscribeMerchantWare'))
			$this->_postValidationSubscription();
		elseif (Tools::isSubmit('submitMerchantWare'))
			$this->_postValidationCredentials();
		else
			$this->_postValidationLayout();
	}

	private function _postValidationLayout()
	{
		Configuration::updateValue('ALLIANCEACH_ENABLE', Tools::getvalue('allianceach_enable'));

		Configuration::updateValue('ALLIANCEACH_LOGIN', Tools::getvalue('allianceach_login'));
		Configuration::updateValue('ALLIANCEACH_PASS', Tools::getvalue('allianceach_pass'));
		Configuration::updateValue('ALLIANCEACH_TERMINAL', Tools::getvalue('allianceach_terminal'));

		Configuration::updateValue('ALLIANCEACH_IDENTITY', Tools::getvalue('allianceach_identity'));
		Configuration::updateValue('ALLIANCEACH_DRIVER', Tools::getvalue('allianceach_driver'));
		
		$this->displayConfirmation($this->l('Checking Settings Updated'));
	}

	private function _postValidationCredentials()
	{
		$this->displayConfirmation($this->l('Credit Card Settings Updated'));
	}

	private function _postValidationSubscription()
	{
		$url="https://www.sales-exec.net/LeadReceiver/LeadInterface.asmx/ReceiveLead?LID=10983&VID=22324&CompanyName=".urlencode(Tools::safeOutput(Tools::getValue('company')))."&CID=21229&FirstName=".urlencode(Tools::safeOutput(Tools::getValue('firstname')))."&LastName=".urlencode(Tools::safeOutput(Tools::getValue('lastname')))."&Address=".urlencode(Tools::safeOutput(Tools::getValue('address')))."&City=".urlencode(Tools::safeOutput(Tools::getValue('city')))."&State=".urlencode(Tools::safeOutput(Tools::getValue('state_id')))."&Zip=".urlencode(Tools::safeOutput(Tools::getValue('zipcode')))."&Phone=".urlencode(Tools::safeOutput(Tools::getValue('phone')))."&Email=".urlencode(Tools::safeOutput(Tools::getValue('email')))."&customer_notes=".urlencode(Tools::safeOutput(Tools::getValue('comments')));
		
		//plain post
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, false);
		$output = curl_exec($ch);
		curl_close($ch);

		if (!substr_count($output,'true'))
		{
			$xml = new SimpleXMLElement($output);
			$error = $xml->PostResponse->ResponseDetails.";";
			$this->_postErrors = explode(';', Tools::substr(Tools::safeOutput($error), 0, -1));
		}
		else
			$this->displayConfirmation($this->l('Application Submitted'));
	}

	private function _postProcessCredentials()
	{
		if (Tools::getvalue('authorizeaim_card_discover') == '')
			$authorizeaim_card_discover = 'off';
		else
			$authorizeaim_card_discover = Tools::getvalue('authorizeaim_card_discover');
			
		if (Tools::getvalue('authorizeaim_card_mastercard') == '')
			$authorizeaim_card_mastercard = 'off';
		else
			$authorizeaim_card_mastercard = Tools::getvalue('authorizeaim_card_mastercard');
		
		if (Tools::getvalue('authorizeaim_card_visa') == '')
			$authorizeaim_card_visa = 'off';
		else
			$authorizeaim_card_visa = Tools::getvalue('authorizeaim_card_visa');
			
		if(Tools::getvalue('authorizeaim_card_ax') == '')
			$authorizeaim_card_ax = 'off';
		else
			$authorizeaim_card_ax = Tools::getvalue('authorizeaim_card_ax');
			
		Configuration::updateValue('ALLIANCE_ENABLE', Tools::getvalue('authorizeaim_enable'));
		Configuration::updateValue('ALLIANCE_LOGIN_ID', Tools::getvalue('authorizeaim_login_id'));
		Configuration::updateValue('ALLIANCE_KEY', Tools::getvalue('authorizeaim_key'));
		Configuration::updateValue('ALLIANCE_DEMO', Tools::getvalue('authorizeaim_demo_mode'));
		Configuration::updateValue('ALLIANCE_CARD_VISA', $authorizeaim_card_visa);
		Configuration::updateValue('ALLIANCE_CARD_MASTERCARD', $authorizeaim_card_mastercard);
		Configuration::updateValue('ALLIANCE_CARD_DISCOVER', $authorizeaim_card_discover);
		Configuration::updateValue('ALLIANCE_CARD_AX', $authorizeaim_card_ax);
		Configuration::updateValue('ALLIANCE_HOLD_REVIEW_OS', Tools::getvalue('authorizeaim_hold_review_os'));

		$this->displayConfirmation($this->l('Configuration updated'));
	}

	private function _displayErrors()
	{
		$this->context->smarty->assign('postErrors', $this->_postErrors);
		return $this->display(__FILE__, 'views/templates/front/error.tpl');
	}

	private function _displayValidation()
	{
		$this->context->smarty->assign('postValidation', array($this->l('Updated succesfully')));
		return $this->display(__FILE__, 'views/templates/front/validation.tpl');
	}

	private function _displayWarning()
	{
		$this->context->smarty->assign('warnings', $this->_warnings);
		return $this->display(__FILE__, 'views/templates/front/warning.tpl');
	}

	/**
	 * @brief to display the payment option, so the customer will pay by merchant ware
	 */
	public function hookPayment($params)
	{
		$currency = Currency::getCurrencyInstance($this->context->cookie->id_currency);
		
		if (!Validate::isLoadedObject($currency) || $currency->iso_code != 'USD')
			return false;
		
		$demo = 1;
		
		if ($demo == 1 || Configuration::get('PS_SSL_ENABLED') || (!empty($_SERVER['HTTPS']) && Tools::strtolower($_SERVER['HTTPS']) != 'off'))
		{
			$isFailed = Tools::getValue('aimerror');
			$cards = array();
			$achsetting = array();
			$midtype=array();

			$cards['visa'] = Configuration::get('ALLIANCE_CARD_VISA') == 'on';
			$cards['mastercard'] = Configuration::get('ALLIANCE_CARD_MASTERCARD') == 'on';
			$cards['discover'] = Configuration::get('ALLIANCE_CARD_DISCOVER') == 'on';
			$cards['ax'] = Configuration::get('ALLIANCE_CARD_AX') == 'on';

			$achsetting['identity'] = Configuration::get('ALLIANCEACH_IDENTITY');
			$achsetting['driver'] = Configuration::get('ALLIANCEACH_DRIVER');

			$midtype['authnet'] = Configuration::get('ALLIANCE_ENABLE');
			$midtype['achmod'] = Configuration::get('ALLIANCEACH_ENABLE');

			if (method_exists('Tools', 'getShopDomainSsl'))
				$url = 'https://'.Tools::getShopDomainSsl().__PS_BASE_URI__.'/modules/'.$this->name.'/';
			else
				$url = 'https://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/'.$this->name.'/';

			$this->context->smarty->assign('x_invoice_num', (int)$params['cart']->id);
			$this->context->smarty->assign('cards', $cards);
			$this->context->smarty->assign('achsetting', $achsetting);
			$this->context->smarty->assign('midtype', $midtype);
			$this->context->smarty->assign('isFailed', $isFailed);
			$this->context->smarty->assign('new_base_dir', $url);
			
			return $this->display(__FILE__, 'views/templates/front/allianceaim.tpl');
		}
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

		return $this->display(__FILE__, 'views/templates/admin/adminOrder.tpl');
	}

	/**
	 * @brief Validate a payment, verify if everything is right
	 */
	public function validation()
	{
		$token = (int)Tools::getValue('Token');
		$id_cart = (int)Tools::getValue('TransactionID');

		$this->context->cart = new Cart($id_cart);
		$this->context->link = new Link();

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
		
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			$url = 'order-confirmation.php?';

		Tools::redirect('location:'.__PS_BASE_URI__.$url.'id_module='.(int)$this->id.'&id_cart='.(int)$this->context->cart->id.'&key='.$this->context->customer->secure_key);
		exit;
	}
	
	public function setTransactionDetail($response)
	{
		// If Exist we can store the details
		if (isset($this->pcc))
		{
			$this->pcc->transaction_id = (string)$response[6];

			// 50 => Card number (XXXX0000)
			$this->pcc->card_number = (string)Tools::substr($response[50], -4);

			// 51 => Card Mark (Visa, Master card)
			$this->pcc->card_brand = (string)$response[51];

			$this->pcc->card_expiration = (string)Tools::getValue('x_exp_date');

			// 68 => Owner name
			$this->pcc->card_holder = (string)$response[68];
		}
	}

}
