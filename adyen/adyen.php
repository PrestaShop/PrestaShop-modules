<?php
/*
* Adyen Payment Module
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
*  @author Rik ter Beek <rikt@adyen.com>
*  @copyright  Copyright (c) 2013 Adyen (http://www.adyen.com)
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

/* This checks for the existence of a PHP constant, and if it doesn't exist, it quits.
 * The sole purpose of this is to prevent visitors to load this file directly.
 */

if (!defined('_PS_VERSION_'))
	exit();

class Adyen extends PaymentModule
{
	const INSTALL_SQL_FILE = 'sql/install.sql';

	public function __construct()
	{
		$this->name = 'adyen';
		$this->tab = 'payments_gateways';
		$this->version = 2.2;
		$this->author = 'Adyen';
		$this->bootstrap = true;
		
		// The need_instance flag indicates whether to load the module's class when displaying the "Modules" page in the back-office
		$this->need_instance = 1;

		parent::__construct();

		$this->dependencies = array();

		$this->displayName = $this->l('Adyen');
		$this->description = $this->l('Accepts payments by Adyen\'s Hosted Payment Page.');
		
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
	}
	
	/*
	 * for installing the plugin
	 */
	public function install()
	{
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			$this->_errors[] = $this->l('Sorry, this module is not compatible with you version.');
			return false;
		}

		if (parent::install() == false || !$this->registerHook('displayBackOfficeHeader') || !$this->registerHook('payment') || !$this->registerHook('paymentReturn') || !$this->registerHook('displayHeader') || !$this->registerHook('displayAdminOrder'))
		{
			Logger::addLog('Adyen module: installation failed!', 4);
			return false;
		}
		
		// execute the sql file into database
		if (!file_exists(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE))
			return false;
		elseif (!$sql = Tools::file_get_contents(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE))
			return false;
		
		$sql = str_replace(array (
				'PREFIX_',
				'ENGINE_TYPE'
		), array (
				_DB_PREFIX_,
				_MYSQL_ENGINE_
		), $sql);
		$sql = preg_split('/;\s*[\r\n]+/', trim($sql));
		
		foreach ($sql as $query)
			if (!Db::getInstance()->execute(trim($query)))
				return false;
			
		/* insert new status */
		Db::getInstance()->Execute('
		INSERT INTO `'._DB_PREFIX_.'order_state` (`send_email`, `unremovable`, `color`) VALUES(0, 1, \'lightblue\')');
		$stateid = Db::getInstance()->Insert_ID();
		
		if ($stateid > 0)
		{
			Configuration::updateValue('ADYEN_NEW_STATUS', $stateid); // save value in configuration
			Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'order_state_lang` (`id_order_state`, `id_lang`, `name`)
			VALUES('.(int)$stateid.', 1, \'Adyen - Awaiting payment\')');
		}
		
		Db::getInstance()->Execute('
		INSERT INTO `'._DB_PREFIX_.'order_state` (`send_email`, `unremovable`, `color`) VALUES(0, 1, \'lightblue\')');
		$stateid2 = Db::getInstance()->Insert_ID();
		
		if ($stateid2 > 0)
		{
			Configuration::updateValue('ADYEN_STATUS_CANCELLED', $stateid2); // save value in configuration
			Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'order_state_lang` (`id_order_state`, `id_lang`, `name`)
			VALUES('.(int)$stateid2.', 1, \'Adyen - Payment Refused\')');
		}
		Logger::addLog('Adyen module: installation succeed');
		return true;
	}
	
	/*
	 * for uninstall the plugin
	 */
	public function uninstall()
	{
		// Delete old settings from adyen module
		$names = array (
			'ADYEN_COUNTRY_CODE_ISO',
			'ADYEN_LANGUAGE_LOCALE',
			'ADYEN_MERCHANT_ACCOUNT',
			'ADYEN_MODE',
			'ADYEN_NOTI_USERNAME',
			'ADYEN_NOTI_PASSWORD',
			'ADYEN_HPP_ENABLED',
			'ADYEN_SKIN_CODE',
			'ADYEN_HMAC_TEST',
			'ADYEN_HMAC_LIVE',
			'ADYEN_PAYMENT_FLOW',
			'ADYEN_DAYS_DELIVERY',
			'ADYEN_HPP_DISABLE',
			'ADYEN_NEW_STATUS',
			'ADYEN_STATUS_AUTHORIZED',
			'ADYEN_STATUS_CANCELLED',
			'ADYEN_IDEAL_ISSUERS_LIVE',
			'ADYEN_IDEAL_ISSUERS_TEST',
			'ADYEN_HPP_TYPES'
		);
		
		$this->deleteByNames($names);
		
		if (!$this->unregisterHook('displayPayment') || !$this->unregisterHook('displayPaymentReturn') || !$this->unregisterHook('displayHeader') || !$this->unregisterHook('displayAdminOrder') || !$this->unregisterHook('displayBackOfficeHeader') || !Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'adyen_event_data`') || !Db::getInstance()->Execute('DELETE `os`, `osl` FROM  `'._DB_PREFIX_.'order_state` AS os LEFT JOIN `'._DB_PREFIX_.'order_state_lang` AS osl ON os.id_order_state = osl.id_order_state WHERE osl.name = \'Adyen - Awaiting payment\' OR osl.name = \'Adyen - Payment Refused\'  ') || !parent::uninstall())
		{
			Logger::addLog('Adyen module: uninstall failed');
			return false;
		}
		
		Logger::addLog('Adyen module: uninstall succeed');
		return true;
	}

	public function deleteByNames($names)
	{
		foreach ($names as $name)
			Configuration::deleteByName($name);
	}
	
	/*
	 * shows the configuration page in the back-end
	 */
	public function getContent()
	{
		$output = null;
		
		if (Tools::isSubmit('submit'.$this->name))
		{
			// get post values
			$country_code_iso = (string)Tools::getValue('ADYEN_COUNTRY_CODE_ISO');
			$language_locale = (string)Tools::getValue('ADYEN_LANGUAGE_LOCALE');
			$merchant_account = (string)Tools::getValue('ADYEN_MERCHANT_ACCOUNT');
			$mode = (string)Tools::getValue('ADYEN_MODE');
			$notification_username = (string)Tools::getValue('ADYEN_NOTI_USERNAME');
			$notification_password = (string)Tools::getValue('ADYEN_NOTI_PASSWORD');
			$hpp_enabled = (string)Tools::getValue('ADYEN_HPP_ENABLED');
			$skin_code = (string)Tools::getValue('ADYEN_SKIN_CODE');
			$hmac_test = (string)Tools::getValue('ADYEN_HMAC_TEST');
			$hmac_live = (string)Tools::getValue('ADYEN_HMAC_LIVE');
			$payment_flow = (string)Tools::getValue('ADYEN_PAYMENT_FLOW');
			$days_delivery = (string)Tools::getValue('ADYEN_DAYS_DELIVERY');
			$hpp_disable = (string)Tools::getValue('ADYEN_HPP_DISABLE');
			$new_order_status = (string)Tools::getValue('ADYEN_NEW_STATUS');
			$status_authorized = (string)Tools::getValue('ADYEN_STATUS_AUTHORIZED');
			$status_cancelled = (string)Tools::getValue('ADYEN_STATUS_CANCELLED');
			
			// validating the input
			if (!$merchant_account || empty($merchant_account) || !Validate::isGenericName($merchant_account))
				$output .= $this->displayError($this->l('Invalid Configuration value for Merchant Account'));
			
			if (!$notification_username || empty($notification_username) || !Validate::isGenericName($notification_username))
				$output .= $this->displayError($this->l('Invalid Configuration value for Notification Username'));
			
			if (!$notification_password || empty($notification_password) || !Validate::isGenericName($notification_password))
				$output .= $this->displayError($this->l('Invalid Configuration value for Notification Password'));
				
				// validate HPP settings if it is enabled
			if ($hpp_enabled == true)
			{
				if (!$skin_code || empty($skin_code) || !Validate::isGenericName($skin_code))
					$output .= $this->displayError($this->l('Invalid Configuration value for Skin code'));
				
				if (!$hmac_test || empty($hmac_test) || !Validate::isGenericName($hmac_test))
					$output .= $this->displayError($this->l('Invalid Configuration value for HMAC Key for Test'));
				
				if (!$hmac_live || empty($hmac_live) || !Validate::isGenericName($hmac_live))
					$output .= $this->displayError($this->l('Invalid Configuration value for HMAC Key for Live'));
				
				if (!$payment_flow || empty($payment_flow) || !Validate::isGenericName($payment_flow))
					$output .= $this->displayError($this->l('Invalid Configuration value for Payment Flow Selection'));
				
				if (!$days_delivery || empty($days_delivery) || !Validate::isInt($days_delivery))
					$output .= $this->displayError($this->l('Invalid Configuration value for Days for Delivery'));
			}
			
			if ($output == null)
			{
				// store ADYEN_HPP_TYPES, IDEAL_ISSUERS_LIVE and ADYEN_IDEAL_ISSUERS_TEST checkbox checked values into their seperate array
				$hpp_result = array ();
				$ideal_issuers_live_result = array ();
				$ideal_issuers_test_result = array ();
				
				foreach ($_POST as $key => $value)
				{
					if (Tools::substr($key, 0, 16) == 'ADYEN_HPP_TYPES_')
						$hpp_result[] = $value;
					elseif (Tools::substr($key, 0, 25) == 'ADYEN_IDEAL_ISSUERS_LIVE_')
						$ideal_issuers_live_result[] = $value;
					elseif (Tools::substr($key, 0, 25) == 'ADYEN_IDEAL_ISSUERS_TEST_')
						$ideal_issuers_test_result[] = $value;
				}
				
				// update the checkbox values
				Configuration::updateValue('ADYEN_HPP_TYPES', implode(';', $hpp_result));
				Configuration::updateValue('ADYEN_IDEAL_ISSUERS_LIVE', implode(';', $ideal_issuers_live_result));
				Configuration::updateValue('ADYEN_IDEAL_ISSUERS_TEST', implode(';', $ideal_issuers_test_result));

				// no errors so update the values
				Configuration::updateValue('ADYEN_COUNTRY_CODE_ISO', $country_code_iso);
				Configuration::updateValue('ADYEN_LANGUAGE_LOCALE', $language_locale);
				Configuration::updateValue('ADYEN_MERCHANT_ACCOUNT', $merchant_account);
				Configuration::updateValue('ADYEN_MODE', $mode);
				Configuration::updateValue('ADYEN_NOTI_USERNAME', $notification_username);
				Configuration::updateValue('ADYEN_NOTI_PASSWORD', $notification_password);
				Configuration::updateValue('ADYEN_HPP_ENABLED', $hpp_enabled);
				Configuration::updateValue('ADYEN_SKIN_CODE', $skin_code);
				Configuration::updateValue('ADYEN_HMAC_TEST', $hmac_test);
				Configuration::updateValue('ADYEN_HMAC_LIVE', $hmac_live);
				Configuration::updateValue('ADYEN_PAYMENT_FLOW', $payment_flow);
				Configuration::updateValue('ADYEN_DAYS_DELIVERY', $days_delivery);
				Configuration::updateValue('ADYEN_HPP_DISABLE', $hpp_disable);
				Configuration::updateValue('ADYEN_NEW_STATUS', $new_order_status);
				Configuration::updateValue('ADYEN_STATUS_AUTHORIZED', $status_authorized);
				Configuration::updateValue('ADYEN_STATUS_CANCELLED', $status_cancelled);
				
				$output .= $this->displayConfirmation($this->l('Settings updated'));
			}
		}
		return $output.$this->displayForm();
	}
	public function displayForm()
	{
		// Get default Language
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
		
		// get the order state id for awaiting payment (can be different id foreach customer)
		$new_order_state_id = (int)Configuration::get('ADYEN_NEW_STATUS');
		
		// get the name of the order state
		$language_id = (int)$this->context->language->id;
		$rq = Db::getInstance()->getRow('
			SELECT `name`, `name`  FROM `'._DB_PREFIX_.'order_state_lang`
			WHERE id_lang = \''.$language_id.'\' AND  id_order_state = \''.pSQL($new_order_state_id).'\'');
		
		if ($rq && isset($rq['name']) && (string)$rq['name'] != '')
			$name = (string)$rq['name'];
		else 
			$name = (string)'Adyen - Awaiting payments';
		
		$new_order_status = array (
			'id' => $new_order_state_id,
			'name' => $name
		);
			
		// get all order statusses
		$order_states_db = OrderState::getOrderStates($this->context->language->id);
		$order_states = array ();

		foreach ($order_states_db as $order_state)
			$order_states[] = array (
				'id' => $order_state['id_order_state'],
				'name' => $order_state['name']
			);
			
			// Init Fields form array
		$fields_form[0]['form'] = array (
			'legend' => array (
				'title' => $this->l('General Settings'),
				'image' => '../img/admin/edit.gif'
			),
			'input' => array (
				array (
					'type' => 'text',
					'label' => $this->l('Merchant Account'),
					'name' => 'ADYEN_MERCHANT_ACCOUNT',
					'size' => 20,
					'required' => true
				),
				array (
					'type' => 'radio',
					'label' => $this->l('Test/Production Mode'),
					'name' => 'ADYEN_MODE',
					'class' => 't',
					'values' => array (
						array (
							'id' => 'prod',
							'value' => 'live',
							'label' => $this->l('Production')
						),
						array (
							'id' => 'test',
							'value' => 'test',
							'label' => $this->l('Test')
						)
					),
					'required' => true
				),
				array (
					'type' => 'select',
					'label' => $this->l('New order status'),
					'name' => 'ADYEN_NEW_STATUS',
					'required' => true,
					'options' => array (
						'query' => array (
								$new_order_status
						),
						'id' => 'id',
						'name' => 'name'
					)
				),
				array (
					'type' => 'select',
					'label' => $this->l('Order status authorised payment'),
					'name' => 'ADYEN_STATUS_AUTHORIZED',
					'required' => true,
					'options' => array (
						'query' => $order_states,
						'id' => 'id',
						'name' => 'name'
					)
				),
				array (
					'type' => 'select',
					'label' => $this->l('Order status cancelled payment'),
					'name' => 'ADYEN_STATUS_CANCELLED',
					'required' => true,
					'options' => array (
						'query' => $order_states,
						'id' => 'id',
						'name' => 'name'
					)
				),
				array (
					'type' => 'text',
					'label' => $this->l('Notification Username'),
					'name' => 'ADYEN_NOTI_USERNAME',
					'size' => 20,
					'required' => true
				),
				array (
					'type' => 'text',
					'label' => $this->l('Notification Password'),
					'name' => 'ADYEN_NOTI_PASSWORD',
					'size' => 20,
					'required' => true
				),
				array (
					'type' => 'text',
					'label' => $this->l('Country Code ISO'),
					'name' => 'ADYEN_COUNTRY_CODE_ISO',
					'size' => 20,
					'required' => false,
					'hint' => $this->l('Leave empty to let Adyen decide on IP-address (Ex: NL)')
				),
				array (
					'type' => 'text',
					'label' => $this->l('Language locale'),
					'name' => 'ADYEN_LANGUAGE_LOCALE',
					'size' => 20,
					'required' => false,
					'hint' => $this->l('Leave empty to let Prestashop decide (Ex: nl_NL)')
				)
			)
		);
		
		$fields_form[1]['form'] = array (
				'legend' => array (
					'title' => $this->l('HPP Settings'),
					'image' => '../img/admin/payment.gif'
				),
				'input' => array (
					array (
						'type' => 'radio',
						'label' => $this->l('Enabled'),
						'name' => 'ADYEN_HPP_ENABLED',
						'class' => 't',
						'values' => array (
							array (
								'id' => 'true',
								'value' => 1,
								'label' => $this->l('Yes')
							),
							array (
								'id' => 'false',
								'value' => 0,
								'label' => $this->l('No')
							)
						),
						'is_bool' => true,
						'required' => true
					),
					array (
						'type' => 'text',
						'label' => $this->l('Skin code'),
						'name' => 'ADYEN_SKIN_CODE',
						'required' => true
					),
					array (
						'type' => 'text',
						'label' => $this->l('HMAC Key for Test'),
						'name' => 'ADYEN_HMAC_TEST',
						'required' => true
					),
					array (
						'type' => 'text',
						'label' => $this->l('HMAC Key for Live'),
						'name' => 'ADYEN_HMAC_LIVE',
						'required' => true
					),
					array (
						'type' => 'select',
						'label' => $this->l('Payment Flow Selection:'),
						'name' => 'ADYEN_PAYMENT_FLOW',
						'required' => false,
						'options' => array (
							'query' => array (
								array (
									'id' => 'multi',
									'name' => $this->l('Multi-page Payment Routine ')
								),
								array (
									'id' => 'single',
									'name' => $this->l('Single Page Payment Routine')
								)
							),
							'id' => 'id',
							'name' => 'name'
						)
					),
					array (
						'type' => 'text',
						'label' => $this->l('Day\'s for delivery'),
						'name' => 'ADYEN_DAYS_DELIVERY',
						'required' => true
					),
					array (
						'type' => 'radio',
						'label' => $this->l('Disable HPP payment methods'),
						'name' => 'ADYEN_HPP_DISABLE',
						'class' => 't',
						'values' => array (
							array (
								'id' => 'true',
								'value' => 1,
								'label' => $this->l('yes')
							),
							array (
								'id' => 'false',
								'value' => 0,
								'label' => $this->l('no')
							)
						),
						'is_bool' => true,
						'required' => true
					),
				),
				'submit' => array (
					'title' => $this->l('Save'),
					'class' => 'btn btn-default pull-right'
				)
		);
		
		$helper = new HelperForm();
		
		// Module, token and currentIndex
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		
		// Language
		$helper->default_form_language = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;
		
		// Title and toolbar
		$helper->title = $this->displayName;
		$helper->show_toolbar = true; // false -> remove toolbar
		$helper->toolbar_scroll = true; // yes - > Toolbar is always visible on the top of the screen.
		$helper->submit_action = 'submit'.$this->name;
		$helper->toolbar_btn = array (
			'save' => array (
				'desc' => $this->l('Save'),
				'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules')
			),
			'back' => array (
				'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
				'desc' => $this->l('Back to list')
			)
		);
		
		if (Tools::isSubmit('submit'.$this->name))
		{
			// get settings from post because post can give errors and you want to keep values
			$country_code_iso = (string)Tools::getValue('ADYEN_COUNTRY_CODE_ISO');
			$language_locale = (string)Tools::getValue('ADYEN_LANGUAGE_LOCALE');
			$merchant_account = (string)Tools::getValue('ADYEN_MERCHANT_ACCOUNT');
			$mode = (string)Tools::getValue('ADYEN_MODE');
			$notification_username = (string)Tools::getValue('ADYEN_NOTI_USERNAME');
			$notification_password = (string)Tools::getValue('ADYEN_NOTI_PASSWORD');
			$hpp_enabled = (string)Tools::getValue('ADYEN_HPP_ENABLED');
			$skin_code = (string)Tools::getValue('ADYEN_SKIN_CODE');
			$hmac_test = (string)Tools::getValue('ADYEN_HMAC_TEST');
			$hmac_live = (string)Tools::getValue('ADYEN_HMAC_LIVE');
			$payment_flow = (string)Tools::getValue('ADYEN_PAYMENT_FLOW');
			$days_delivery = (string)Tools::getValue('ADYEN_DAYS_DELIVERY');
			$hpp_disable = (string)Tools::getValue('ADYEN_HPP_DISABLE');
			$status_new_order = (string)Tools::getValue('ADYEN_NEW_STATUS');
			$status_authorized = (string)Tools::getValue('ADYEN_STATUS_AUTHORIZED');
			$status_cancelled = (string)Tools::getValue('ADYEN_STATUS_CANCELLED');
			
			$ideal_issuers_live = explode(';', Configuration::get('ADYEN_IDEAL_ISSUERS_LIVE'));
			$ideal_issuers_test = (string)Tools::getValue('ADYEN_IDEAL_ISSUERS_TEST');
			
			// get post values and if their were checked, checked them again
			foreach ($_POST as $key => $value)
			{
				if (Tools::substr($key, 0, 16) == 'ADYEN_HPP_TYPES_')
					$helper->fields_value['ADYEN_HPP_TYPES_'.$value] = '1';
				elseif (Tools::substr($key, 0, 25) == 'ADYEN_IDEAL_ISSUERS_LIVE_')
					$helper->fields_value['ADYEN_IDEAL_ISSUERS_LIVE_'.$value] = '1';
				elseif (Tools::substr($key, 0, 25) == 'ADYEN_IDEAL_ISSUERS_TEST_')
					$helper->fields_value['ADYEN_IDEAL_ISSUERS_TEST_'.$value] = '1';
			}
		}
		else
		{
			$country_code_iso = Configuration::get('ADYEN_COUNTRY_CODE_ISO');
			$language_locale = Configuration::get('ADYEN_LANGUAGE_LOCALE');
			$merchant_account = Configuration::get('ADYEN_MERCHANT_ACCOUNT');
			$mode = Configuration::get('ADYEN_MODE');
			$notification_username = Configuration::get('ADYEN_NOTI_USERNAME');
			$notification_password = Configuration::get('ADYEN_NOTI_PASSWORD');
			$hpp_enabled = Configuration::get('ADYEN_HPP_ENABLED');
			$skin_code = Configuration::get('ADYEN_SKIN_CODE');
			$hmac_test = Configuration::get('ADYEN_HMAC_TEST');
			$hmac_live = Configuration::get('ADYEN_HMAC_LIVE');
			$payment_flow = Configuration::get('ADYEN_PAYMENT_FLOW');
			$days_delivery = Configuration::get('ADYEN_DAYS_DELIVERY');
			$hpp_disable = Configuration::get('ADYEN_HPP_DISABLE');
			$status_new_order = Configuration::get('ADYEN_NEW_STATUS');
			
			// get value from config if not exist set the default value to "PAYMENT ACCEPTED"
			if (Configuration::get('ADYEN_STATUS_AUTHORIZED') != '')
				$status_authorized = (string)Configuration::get('ADYEN_STATUS_AUTHORIZED');
			else
				$status_authorized = (string)Configuration::get('PS_OS_PAYMENT'); // default value is payment
			
			// get value from config if not exist set the default value to "Adyen - Payment Refused"
			if (Configuration::get('ADYEN_STATUS_CANCELLED') != '')
				$status_cancelled = (string)Configuration::get('ADYEN_STATUS_CANCELLED');
			
			foreach (explode(';', Configuration::get('ADYEN_HPP_TYPES')) as $value)
				$helper->fields_value['ADYEN_HPP_TYPES_'.$value] = '1';
			
			foreach (explode(';', Configuration::get('ADYEN_IDEAL_ISSUERS_LIVE')) as $value)
				$helper->fields_value['ADYEN_IDEAL_ISSUERS_LIVE_'.$value] = '1';
			
			foreach (explode(';', Configuration::get('ADYEN_IDEAL_ISSUERS_TEST')) as $value)
				$helper->fields_value['ADYEN_IDEAL_ISSUERS_TEST_'.$value] = '1';
		}
		
		// Load current value
		$helper->fields_value['ADYEN_COUNTRY_CODE_ISO'] = $country_code_iso;
		$helper->fields_value['ADYEN_LANGUAGE_LOCALE'] = $language_locale;
		$helper->fields_value['ADYEN_MERCHANT_ACCOUNT'] = $merchant_account;
		$helper->fields_value['ADYEN_MODE'] = $mode;
		$helper->fields_value['ADYEN_NOTI_USERNAME'] = $notification_username;
		$helper->fields_value['ADYEN_NOTI_PASSWORD'] = $notification_password;
		$helper->fields_value['ADYEN_HPP_ENABLED'] = $hpp_enabled;
		$helper->fields_value['ADYEN_SKIN_CODE'] = $skin_code;
		$helper->fields_value['ADYEN_HMAC_TEST'] = $hmac_test;
		$helper->fields_value['ADYEN_HMAC_LIVE'] = $hmac_live;
		$helper->fields_value['ADYEN_PAYMENT_FLOW'] = $payment_flow;
		$helper->fields_value['ADYEN_DAYS_DELIVERY'] = $days_delivery;
		$helper->fields_value['ADYEN_HPP_DISABLE'] = $hpp_disable;
		$helper->fields_value['ADYEN_NEW_STATUS'] = $status_new_order;
		$helper->fields_value['ADYEN_STATUS_AUTHORIZED'] = $status_authorized;
		$helper->fields_value['ADYEN_STATUS_CANCELLED'] = $status_cancelled;
		
		return $helper->generateForm($fields_form);
	}
	
	/*
	 * display payment with adyen
	 */
	public function hookDisplayPayment($params)
	{
		if (!$this->active)
			return;
			
			// HPP must be enabled to select
		if (Configuration::get('ADYEN_HPP_ENABLED') == true)
		{
			$cart = $this->context->cart;
			if (!$this->checkCurrency($cart))
				Tools::redirect('index.php?controller=order');
			
			$hpp_options = array ();
			if (Configuration::get('ADYEN_HPP_DISABLE') == false)
			{
				// GET HPP options from Adyen
				$result_array = array();
					
				// get the default config values
				$config = Configuration::getMultiple(array (
					'ADYEN_MERCHANT_ACCOUNT',
					'ADYEN_MODE',
					'ADYEN_SKIN_CODE',
					'ADYEN_COUNTRY_CODE_ISO',
				));
					
				$currency = $this->context->currency;
				$currency_code = (string)$currency->iso_code;
				$skin_code = (string)$config['ADYEN_SKIN_CODE'];
				$merchant_account = (string)$config['ADYEN_MERCHANT_ACCOUNT'];
				$payment_amount = number_format($cart->getOrderTotal(true, 3), 2, '', '');
				$session_validity = date(DATE_ATOM, mktime(date('H') + 1, date('i'), date('s'), date('m'), date('j'), date('Y')));
					
				if ($config['ADYEN_COUNTRY_CODE_ISO'] != '')
					$country_code = (string)$config['ADYEN_COUNTRY_CODE_ISO'];
				else
					$country_code = (string)$country->iso_code;
					
				$request = array(
					"paymentAmount" => $payment_amount,
					"currencyCode" => $currency_code,
					"merchantReference" => "Get Payment methods",
					"skinCode" => $skin_code,
					"merchantAccount" => $merchant_account,
					"sessionValidity" => $session_validity,
					"countryCode" => $country_code,
					"merchantSig" => "",
				);
					
				$hmac_data = $request['paymentAmount'] .
				$request['currencyCode'] .
				$request['merchantReference'] .
				$request['skinCode'] .
				$request['merchantAccount'] .
				$request['sessionValidity'];
					
				//Generate HMAC encrypted merchant signature
				$merchant_sig = base64_encode(pack('H*', $this->getHmacsha1($this->getHmac(), $hmac_data)));
				$request['merchantSig'] = $merchant_sig;
					
				$ch = curl_init();
					
				if ($config['ADYEN_MODE'] == 'live')
					curl_setopt($ch, CURLOPT_URL, "https://live.adyen.com/hpp/directory.shtml");
				else
					curl_setopt($ch, CURLOPT_URL, "https://test.adyen.com/hpp/directory.shtml");
				
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_POST,count($request));
				curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($request));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); // do not print results if you do curl_exec
					
				$results = curl_exec($ch);
					
				if ($results === false)
					echo "Error: " . curl_error($ch);
				else
				{
					/**
					 * The $result contains a JSON array containing
					 * the available payment methods for the merchant account.
					 */
					$results_json = json_decode($results);
				
					if($results_json == null)
						// no valid json so show the error
						echo $results;
				
					$payment_methods = $results_json->paymentMethods;
					 
					foreach($payment_methods as $payment_method) {
						$result_array[$payment_method->brandCode]['name'] = $payment_method->name;
				
						if (isset($payment_method->issuers))
						{
							// for ideal go through the issuers
							if(count($payment_method->issuers) > 0)
								foreach($payment_method->issuers as $issuer)
									$result_array[$payment_method->brandCode]['issuers'][$issuer->issuerId] = $issuer->name;

							ksort($result_array[$payment_method->brandCode]['issuers']); // sort on key
						}
					}
				}
				// get list of hpp options this has only the value
				$hpp_options = $result_array;
			} 
			
			$this->context->smarty->assign(array (
				'hpp_options' => $hpp_options,
				'ideal_options' => $ideal_issuers_key_value,
				'nbProducts' => $cart->nbProducts(),
				'cust_currency' => $cart->id_currency,
				'currencies' => $this->getCurrency((int)$cart->id_currency),
				'total' => $cart->getOrderTotal(true, Cart::BOTH),
				'this_path' => $this->getPathUri(),
				'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
			));
			
			return $this->display(__FILE__, '/views/templates/front/payment.tpl');
		}
	}

	public function hookDisplayPaymentReturn($params)
	{
		if (!$this->active)
			return;
			
		// Validate the details in the result url.
		$get_auth_result = Tools::getValue('authResult');
		$get_psp_reference = Tools::getValue('pspReference');
		$get_merchant_reference = Tools::getValue('merchantReference');
		$get_skin_code = Tools::getValue('skinCode');
		$get_merchant_sig = Tools::getValue('merchantSig');
		
		$auth_result = (isset($get_auth_result)) ? (string)Tools::getValue('authResult') : '';
		$psp_reference = (isset($get_psp_reference)) ? (string)Tools::getValue('pspReference') : '';
		$merchant_reference = (isset($get_merchant_reference)) ? (int)Tools::getValue('merchantReference') : '';
		$skin_code = (isset($get_skin_code)) ? (string)Tools::getValue('skinCode') : '';
		$merchant_sig = (isset($get_merchant_sig)) ? (string)Tools::getValue('merchantSig') : '';
		
		// Calculate the merchant signature from the return values.
		$hmac_data = $auth_result.$psp_reference.$merchant_reference.$skin_code;
		$calculated_merchant_sig = base64_encode(pack('H*', $this->getHmacsha1($this->getHmac(), $hmac_data)));
		
		// Both values must be the same.
		$template = 'error.tpl';

		if ($merchant_sig == $calculated_merchant_sig && $merchant_reference == $params['objOrder']->id)
		{
			switch ($auth_result)
			{
				case 'PENDING':
					$template = 'pending.tpl';
				case 'AUTHORISED':
					$template = 'authorised.tpl';
					break;
				case 'REFUSED':
					$this->cancelOrder($merchant_reference);
					$template = 'refused.tpl';
					break;
				case 'CANCELLED':
					$this->cancelOrder($merchant_reference);
					$template = 'cancelled.tpl';
					break;
				default:
					break;
			}
		}
		
		return $this->display(__FILE__, 'views/templates/front/result/'.$template);
	}

	public function cancelOrder($order_id)
	{
		$order = new Order($order_id);
		$history = new OrderHistory();
		$history->id_order = (int)$order->id;
		$history->changeIdOrderState((int)Configuration::get('ADYEN_STATUS_CANCELLED'), (int)($order->id));
		$history->add();
		Logger::addLog('Adyen module: order cancceled with id_order '.$order_id);
	}

	public function hookDisplayBackOfficeHeader()
	{
		$this->context->controller->addCSS($this->_path.'css/adyen_backend.css', 'all');
	}

	public function hookDisplayHeader()
	{
		$this->context->controller->addCSS($this->_path.'css/adyen.css', 'all');
	}

	public function hookDisplayAdminOrder()
	{
		// get psp_reference from event_data table
		$sql = 'SELECT * FROM '._DB_PREFIX_.'adyen_event_data
				WHERE id_order = '.(int)Tools::getValue('id_order').' ORDER BY created_at DESC';
		
		$psp_refence = '';
		
		if ($row = Db::getInstance()->getRow($sql))
			$psp_refence = $row['psp_reference'];
		
		if ($psp_refence != '')
		{
			// get psp reference url
			if (Configuration::get('ADYEN_MODE') == 'live')
				$psp_reference_text = str_replace('%s', $psp_refence, '<a href="https://ca-live.adyen.com/ca/ca/payments/searchPayments.shtml?query=%s&skipList=firstResult" target="__blank">%s</a>');
			else
				$psp_reference_text = str_replace('%s', $psp_refence, '<a href="https://ca-test.adyen.com/ca/ca/payments/searchPayments.shtml?query=%s&skipList=firstResult" target="__blank">%s</a>');
		}
		else
			$psp_reference_text = $this->l('Payment has not been processed yet.');
		
		echo '<br /><fieldset>
				<legend><img src="../img/admin/tab-payment.gif">Adyen Payment Information</legend>
					'.$this->l('Adyen PSP Reference:').' '.$psp_reference_text.'<br>'.$this->l('Order was placed using').' '.$this->context->currency->iso_code.'
			</fieldset>';
	}

	public function checkCurrency($cart)
	{
		$currency_order = new Currency($cart->id_currency);
		$currencies_module = $this->getCurrency($cart->id_currency);
		
		if (is_array($currencies_module))
			foreach ($currencies_module as $currency_module)
				if ($currency_order->id == $currency_module['id_currency'])
					return true;
		return false;
	}

	public function getHmac()
	{
		return Configuration::get('ADYEN_MODE') == 'live' ? Configuration::get('ADYEN_HMAC_LIVE') : Configuration::get('ADYEN_HMAC_TEST');
	}

	public function getHmacsha1($key, $data)
	{
		// this function is not always available if not calculate manual result is the same
		if (function_exists('hash_hmac'))
		{
			Logger::addLog('Adyen module: calculated hmacsha1 based on hash_hmac php function');
			return hash_hmac('sha1', $data, $key);
		}
		else
		{
			$blocksize = 64;
			$hashfunc = 'sha1';
			if (Tools::strlen($key) > $blocksize)
				$key = pack('H*', $hashfunc($key));
			
			$key = str_pad($key, $blocksize, chr(0x00));
			$ipad = str_repeat(chr(0x36), $blocksize);
			$opad = str_repeat(chr(0x5c), $blocksize);
			$hmac = pack('H*', $hashfunc(($key ^ $opad).pack('H*', $hashfunc(($key ^ $ipad).$data))));
			Logger::addLog('Adyen module: calculated hmacsha1 based on manual function');
			return bin2hex($hmac);
		}
	}
}