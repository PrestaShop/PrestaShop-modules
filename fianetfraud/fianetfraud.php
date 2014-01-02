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

require_once _PS_MODULE_DIR_.'fianetfraud/lib/includes/includes.inc.php';

class fianetfraud extends Module
{

	private $_product_types = array(
		1 => 'Alimentation & gastronomie',
		2 => 'Auto & moto',
		3 => 'Culture & divertissements',
		4 => 'Maison & jardin',
		5 => 'Electrom&eacute;nager',
		6 => 'Ench&egrave;res et achats group&eacute;s',
		7 => 'Fleurs & cadeaux',
		8 => 'Informatique & logiciels',
		9 => 'Sant&eacute; & beaut&eacute;',
		10 => 'Services aux particuliers',
		11 => 'Services aux professionnels',
		12 => 'Sport',
		13 => 'V&ecirc;tements & accessoires',
		14 => 'Voyage & tourisme',
		15 => 'Hifi, photo & vid&eacute;os',
		16 => 'T&eacute;l&eacute;phonie & communication',
		17 => 'Bijoux et m&eacute;taux pr&eacute;cieux',
		18 => 'Articles et accessoires pour b&eacute;b&eacute;',
		19 => 'Sonorisation & lumi&egrave;re'
	);
	private $_carrier_types = array(
		1 => 'Retrait de la marchandise chez le marchand',
		2 => 'Utilisation d\'un r&eacute;seau de points-retrait tiers (type kiala, alveol, etc.)',
		3 => 'Retrait dans un a&eacute;roport, une gare ou une agence de voyage',
		4 => 'Transporteur (La Poste, Colissimo, UPS, DHL... ou tout transporteur priv&eacute;)',
		5 => 'Emission d\'un billet &eacute;lectronique, t&eacute;l&eacute;chargements',
		6 => 'Module SoColissimo'
	);
	private $_carrier_speeds = array(
		2 => 'Standard',
		1 => 'Express (-24h)'
	);
	private $_payment_types = array(
		1 => 'carte',
		2 => 'cheque',
		3 => 'contre-remboursement',
		4 => 'virement',
		5 => 'cb en n fois',
		6 => 'paypal',
		7 => '1euro.com',
		8 => 'buyster',
		9 => 'bybox',
	);
	private $_certissim_states = array(
		'1' => 'not concerned',
		'2' => 'ready to send',
		'3' => 'sent',
		'4' => 'scored',
		'5' => 'error',
	);
	private $_certissim_statuses = array(
		'test',
		'prod',
	);

	const CERTISSIM_ORDER_TABLE_NAME = 'certissim_order';
	const CERTISSIM_STATE_TABLE_NAME = 'certissim_state';

	public function __construct()
	{
		$this->name = 'fianetfraud';
		$this->version = '3.7';
		$this->tab = 'payment_security';
		$this->author = 'Fia-Net';

		parent::__construct();

		$this->displayName = $this->l('Fia-Net - Certissim');
		$this->description = $this->l('Protect your shop against payment frauds.');

		/* Backward compatibility */
		if (_PS_VERSION_ < '1.5')
		{
			if (file_exists(_PS_MODULE_DIR_.'backwardcompatibility/backward_compatibility/backward.php'))
				include(_PS_MODULE_DIR_.'backwardcompatibility/backward_compatibility/backward.php');
			else
			{
				$this->warning = $this->l('In order to work properly in PrestaShop v1.4, the Fia-Net - Certissim module requiers the backward compatibility module at least v0.4.').'<br />';
				$this->warning .= $this->l('You can download this module for free here: http://addons.prestashop.com/en/modules-prestashop/6222-backwardcompatibility.html');
			}
		}
	}

	public function install()
	{
		CertissimLogger::insertLog(__METHOD__." : ".__LINE__, "Génération du fichier log");

		/** database tables creation * */
		$sqlfile = dirname(__FILE__).'/install.sql';
		if (!file_exists($sqlfile) || !($sql = file_get_contents($sqlfile)))
			return false;

		$sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
		$queries = preg_split("/;\s*[\r\n]+/", $sql);
		foreach ($queries as $query)
			if (!Db::getInstance()->Execute(trim($query)))
			{
				CertissimLogger::insertLog(__METHOD__." : ".__LINE__, "Install impossible, génération base échouée : ".Db::getInstance()->getMsgError());
				return false;
			}

		//Certissim order stats insertion
		foreach ($this->_certissim_states as $id => $label)
		{
			$sql = "INSERT INTO `"._DB_PREFIX_.self::CERTISSIM_STATE_TABLE_NAME."` (`id_certissim_state`,`label`) VALUES ('".(int) $id."','".(string) $label."')";
			$insert = Db::getInstance()->execute($sql);
			if (!$insert)
				CertissimLogger::insertLog(__METHOD__." : ".__LINE__, "Insertion state $id.$label échouée : ".Db::getInstance()->getMsgError());
		}

		$tab_admin_order_id = Tab::getIdFromClassName('AdminOrders');

		//AdminCertissimController registration
		$tab_controller_main = new Tab();
		$tab_controller_main->active = 1;
		$tab_controller_main->class_name = "AdminCertissim";
		foreach (Language::getLanguages() as $language)
			$tab_controller_main->name[$language['id_lang']] = "Certissim";
		$tab_controller_main->id_parent = $tab_admin_order_id;
		$tab_controller_main->module = $this->name;
		$tab_controller_main->add();
		$tab_controller_main->move($this->getNewLastPosition(0));

		return (parent::install()
			&& $this->registerHook('newOrder')
			&& $this->registerHook('paymentConfirm')
			&& $this->registerHook('adminOrder')
			&& $this->registerHook('backOfficeHeader')
			);
	}

	public function uninstall()
	{
		//uninstall tab
		$tab_controller_main_id = TabCore::getIdFromClassName('AdminCertissim');
		$tab_controller_main = new Tab($tab_controller_main_id);
		$tab_controller_main->delete();
		//drops certissim state table
		Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.self::CERTISSIM_STATE_TABLE_NAME.'`');

		return parent::uninstall();
	}

	/**
	 * returns the product categories list as an array indexed by category_id and containing the category name and the corresponding fia-net product family
	 * 
	 * @return array
	 */
	private function loadProductCategories()
	{
		$categories = Category::getSimpleCategories($this->context->language->id);

		$shop_categories = array();
		foreach ($categories as $category)
		{
			$certissim_type = Tools::isSubmit('certissim_'.$category['id_category'].'_product_type') ? Tools::getValue('certissim_'.$category['id_category'].'_product_type') : Configuration::get('CERTISSIM_'.$category['id_category'].'_PRODUCT_TYPE');
			$shop_categories[$category['id_category']] = array(
				'name' => $category['name'],
				'certissim_type' => $certissim_type
			);
		}
		return $shop_categories;
	}

	/**
	 * returns the carriers list as an array indexed by carrier_id and containing the carrier name and the corresponding fia-net carrier type
	 * 
	 * @return array
	 */
	private function loadCarriers()
	{
		$carriers = Carrier::getCarriers($this->context->language->id, false, false, false, null, ALL_CARRIERS);
		$shop_carriers = array();
		foreach ($carriers as $carrier)
		{
			$certissim_type = Tools::isSubmit('certissim_'.$carrier['id_carrier'].'_carrier_type') ? Tools::getValue('certissim_'.$carrier['id_carrier'].'_carrier_type') : Configuration::get('CERTISSIM_'.$carrier['id_carrier'].'_CARRIER_TYPE');
			$certissim_speed = Tools::isSubmit('certissim_'.$carrier['id_carrier'].'_carrier_speed') ? Tools::getValue('certissim_'.$carrier['id_carrier'].'_carrier_speed') : Configuration::get('CERTISSIM_'.$carrier['id_carrier'].'_CARRIER_SPEED');
			$shop_carriers[$carrier['id_carrier']] = array(
				'name' => $carrier['name'],
				'certissim_type' => $certissim_type,
				'certissim_speed' => $certissim_speed
			);
		}

		return $shop_carriers;
	}

	/**
	 * returns the payment modules list as an array indexed by module_id and containing the module name and the corresponding fia-net payment type
	 * 
	 * @return array
	 */
	private function loadPaymentMethods()
	{
		if (_PS_VERSION_ < '1.5')
			$payments = $this->getInstalledPaymentModules();
		else
			$payments = PaymentModule::getPaymentModules();
		$payment_modules = array();
		foreach ($payments as $payment)
		{
			$module = Module::getInstanceById($payment['id_module']);
			//reloads submitted values if exists, loads conf otherwise
			$certissim_type = Tools::isSubmit('certissim_'.$module->id.'_payment_type') ? Tools::getValue('certissim_'.$module->id.'_payment_type') : Configuration::get('CERTISSIM_'.$module->id.'_PAYMENT_TYPE');
			$certissim_enabled = Tools::isSubmit('certissim_'.$module->id.'_payment_enabled') ? Tools::getValue('certissim_'.$module->id.'_payment_enabled') : Configuration::get('CERTISSIM_'.$module->id.'_PAYMENT_ENABLED');

			$payment_modules[$module->id] = array(
				'name' => $module->displayName,
				'certissim_type' => $certissim_type,
				'enabled' => $certissim_enabled,
			);
		}
		return $payment_modules;
	}

	/**
	 * returns true if the form is valid, false otherwise
	 * 
	 * @return boolean
	 */
	private function formIsValid()
	{
		if (strlen(Tools::getValue('certissim_login')) < 1)
			$this->_errors[] = $this->l('Login can\'t be empty');
		if (strlen(Tools::getValue('certissim_password')) < 1)
			$this->_errors[] = $this->l('Password can\'t be empty');
		if (strlen(Tools::getValue('certissim_siteid')) < 1)
			$this->_errors[] = $this->l('Siteid can\'t be empty');
		if (!preg_match('#^[0-9]+$#', Tools::getValue('certissim_siteid')))
			$this->_errors[] = $this->l('Siteid has to be integer.');
		if (!in_array(Tools::getValue('certissim_status'), $this->_certissim_statuses))
			$this->_errors[] = $this->l('You must give a correct status');
		if (!in_array(Tools::getValue('certissim_default_product_type'), array_keys($this->_product_types)))
			$this->_errors[] = $this->l('You must configure a valid default product type');
		if (!in_array(Tools::getValue('certissim_default_carrier_type'), array_keys($this->_carrier_types)))
			$this->_errors[] = $this->l('You must configure a valid default carrier type');
		if (!in_array(Tools::getValue('certissim_default_carrier_speed'), array_keys($this->_carrier_speeds)))
			$this->_errors[] = $this->l('You must configure a valid default carrier speed');

		//categories check
		$shop_categories = $this->loadProductCategories();
		foreach ($shop_categories as $id => $shop_category)
			if (!in_array(Tools::getValue('certissim_'.$id.'_product_type'), array_merge(array_keys($this->_product_types), array('0'))))
				$this->_errors[] = $this->l('Invalid product type for category:')." '".$shop_category['name']."'";

		//carriers check
		$shop_carriers = $this->loadCarriers();
		$delivery_shop = false;

		foreach ($shop_carriers as $id => $shop_carrier)
		{
			if (!in_array(Tools::getValue('certissim_'.$id.'_carrier_type'), array_merge(array_keys($this->_carrier_types), array('0'))))
				$this->_errors[] = $this->l('Invalid carrier type for carrier:')." '".$shop_carrier['name']."'";

			if (!in_array(Tools::getValue('certissim_'.$id.'_carrier_speed'), array_merge(array_keys($this->_carrier_speeds), array('0'))))
				$this->_errors[] = $this->l('Invalid carrier speed for carrier:')." '".$shop_carrier['name']."'";


			if (Tools::getValue('certissim_'.$id.'_carrier_type') == 6)
			{
				if (_PS_VERSION_ >= '1.5')
				//check if socolissimo is enabled on PS 1.5
					$socolissimo_is_enabled = Module::isEnabled('socolissimo');
				else
				//check if socolissimo is enabled on PS 1.4
					$socolissimo_is_enabled = $this->checkModuleisEnabled('socolissimo');

				if (!Module::isInstalled('socolissimo') || !$socolissimo_is_enabled)
				{
					$this->_errors[] = $this->l('Invalid carrier type for carrier:')." '".$shop_carrier['name']."'. ".$this->l('SoColissimo module is not installed or not enabled');
				}
			}

			if (Tools::getValue('certissim_'.$id.'_carrier_type') == 1)
				$delivery_shop = true;
		}

		//payment types check
		$shop_payments = $this->loadPaymentMethods();
		foreach ($shop_payments as $id => $shop_payment)
			if (!in_array(Tools::getValue('certissim_'.$id.'_payment_type'), array_keys($this->_payment_types)))
				$this->_errors[] = $this->l('Invalid payment type for method:')." '".$shop_payment['name']."'";


		//check if shop address entered if selected carrier or default carrier selected is 1
		if (Tools::getValue('certissim_default_carrier_type') == 1 || $delivery_shop)
			$this->checkShopAddress();

		return empty($this->_errors);
	}

	private function processForm()
	{
		//if the form is valid
		if ($this->formIsValid())
		{
			//global parameters update
			/** Certissim paramaters * */
			Configuration::updateValue('CERTISSIM_LOGIN', Tools::getValue('certissim_login'));
			Configuration::updateValue('CERTISSIM_PASSWORD', Tools::getValue('certissim_password'));
			Configuration::updateValue('CERTISSIM_PASSWORDURLENCODED', urlencode(Tools::getValue('certissim_password')));
			Configuration::updateValue('CERTISSIM_SITEID', Tools::getValue('certissim_siteid'));
			Configuration::updateValue('CERTISSIM_STATUS', Tools::getValue('certissim_status'));

			/** shop configuration * */
			Configuration::updateValue('CERTISSIM_DEFAULT_PRODUCT_TYPE', Tools::getValue('certissim_default_product_type'));
			Configuration::updateValue('CERTISSIM_DEFAULT_CARRIER_TYPE', Tools::getValue('certissim_default_carrier_type'));
			Configuration::updateValue('CERTISSIM_DEFAULT_CARRIER_SPEED', Tools::getValue('certissim_default_carrier_speed'));

			/** categories configuration * */
			//lists all product categories
			$shop_categories = $this->loadProductCategories();
			foreach (array_keys($shop_categories) as $id)
				Configuration::updateValue('CERTISSIM_'.$id.'_PRODUCT_TYPE', Tools::getValue('certissim_'.$id.'_product_type'));

			/** carriers update * */
			//lists all carriers
			$shop_carriers = $this->loadCarriers();
			foreach (array_keys($shop_carriers) as $id)
			{
				Configuration::updateValue('CERTISSIM_'.$id.'_CARRIER_TYPE', Tools::getValue('certissim_'.$id.'_carrier_type'));
				Configuration::updateValue('CERTISSIM_'.$id.'_CARRIER_SPEED', Tools::getValue('certissim_'.$id.'_carrier_speed'));
			}

			/** payment types update * */
			//lists all payment modules
			$payment_modules = $this->loadPaymentMethods();
			foreach (array_keys($payment_modules) as $id)
			{
				Configuration::updateValue('CERTISSIM_'.$id.'_PAYMENT_TYPE', Tools::getValue('certissim_'.$id.'_payment_type'));
				Configuration::updateValue('CERTISSIM_'.$id.'_PAYMENT_ENABLED', ((int) Tools::getValue('certissim_'.$id.'_payment_enabled') == 1 ? '1' : '0'));
			}
			return true;
		}
		else //if form is not valid
			return false;
	}

	public function getContent()
	{
		$head_msg = '';
		//if some POST datas are found
		if (Tools::isSubmit('submitSettings'))
		{
			//if the form is correctly saved
			if ($this->processForm())
			//adds a confirmation message
				$head_msg = $this->displayConfirmation($this->l('Configuration updated.'));
			else
			{ //if errors have been encountered while validating the form
				//adds an error message informing about errors that have been encountered
				$error_msg = $this->l('Some errors have been encoutered while updating configuration.');
				$error_msg .= '<ul>';
				foreach ($this->_errors as $error_label)
				{
					$error_msg .= '<li>';
					$error_msg .= $error_label;
					$error_msg .= '</li>';
				}
				$error_msg .= '</ul>';
				$head_msg = $this->displayError($error_msg);
			}
		}
		//lists all categories
		$shop_categories = $this->loadProductCategories();

		//lists all carriers
		$shop_carriers = $this->loadCarriers();

		//lists all payment methods
		$payment_modules = $this->loadPaymentMethods();

		$certissim_login = Tools::isSubmit('certissim_login') ? Tools::getValue('certissim_login') : Configuration::get('CERTISSIM_LOGIN');
		$certissim_password = Tools::isSubmit('certissim_password') ? Tools::getValue('certissim_password') : Configuration::get('CERTISSIM_PASSWORD');
		$certissim_siteid = Tools::isSubmit('certissim_siteid') ? Tools::getValue('certissim_siteid') : Configuration::get('CERTISSIM_SITEID');
		$certissim_status = Tools::isSubmit('certissim_status') ? Tools::getValue('certissim_status') : Configuration::get('CERTISSIM_STATUS');
		$certissim_default_product_type = Tools::isSubmit('certissim_default_product_type') ? Tools::getValue('certissim_default_product_type') : Configuration::get('CERTISSIM_DEFAULT_PRODUCT_TYPE');
		$certissim_default_carrier_type = Tools::isSubmit('certissim_default_carrier_type') ? Tools::getValue('certissim_default_carrier_type') : Configuration::get('CERTISSIM_DEFAULT_CARRIER_TYPE');
		$certissim_default_carrier_speed = Tools::isSubmit('certissim_default_carrier_speed') ? Tools::getValue('certissim_default_carrier_speed') : Configuration::get('CERTISSIM_DEFAULT_CARRIER_SPEED');

		//admin shop address link and log file url
		if (_PS_VERSION_ < '1.5')
		{
			$url_log = 'index.php?tab=AdminCertissim&action=viewLog&token='.Tools::getAdminTokenLite('AdminCertissim');
			$link_shop_setting = 'index.php?tab=AdminContact&token='.Tools::getAdminTokenLite('AdminContact');
		} else
		{
			$url_log = $this->context->link->getAdminLink('AdminCertissim')."&action=viewLog";
			$link_shop_setting = $this->context->link->getAdminLink('AdminStores').'&token='.Tools::getAdminTokenLite('AdminStores');
		}

		$this->smarty->assign(array(
			'head_msg' => $head_msg,
			'url_log' => $url_log,
			'certissim_login' => Tools::safeOutput($certissim_login),
			'certissim_password' => Tools::safeOutput($certissim_password),
			'certissim_siteid' => Tools::safeOutput($certissim_siteid),
			'certissim_status' => Tools::safeOutput($certissim_status),
			'certissim_statuses' => $this->_certissim_statuses,
			'certissim_product_types' => $this->_product_types,
			'certissim_default_product_type' => Tools::safeOutput($certissim_default_product_type),
			'shop_categories' => $shop_categories,
			'certissim_carrier_types' => $this->_carrier_types,
			'certissim_carrier_speeds' => $this->_carrier_speeds,
			'certissim_default_carrier_type' => Tools::safeOutput($certissim_default_carrier_type),
			'certissim_default_carrier_speed' => Tools::safeOutput($certissim_default_carrier_speed),
			'shop_carriers' => $shop_carriers,
			'certissim_payment_types' => $this->_payment_types,
			'payment_modules' => $payment_modules,
			'image_path' => __PS_BASE_URI__.'modules/'.$this->name.'/img/certissim.png',
			'logo_path' => __PS_BASE_URI__.'modules/'.$this->name.'/img/logo.png',
			'logo_account_path' => __PS_BASE_URI__.'modules/'.$this->name.'/img/account.gif',
			'logo_categories_path' => __PS_BASE_URI__.'modules/'.$this->name.'/img/categories.gif',
			'logo_carriers_path' => __PS_BASE_URI__.'modules/'.$this->name.'/img/carriers.gif',
			'logo_payments_path' => __PS_BASE_URI__.'modules/'.$this->name.'/img/payments.gif',
			'logo_warning' => __PS_BASE_URI__.'modules/'.$this->name.'/img/no.gif',
			'link_shop_setting' => $link_shop_setting
		));

		if (version_compare(_PS_VERSION_, '1.5', '<'))
			return $this->smarty->display(_PS_MODULE_DIR_.$this->name.'/views/templates/admin/admin.tpl');

		return $this->display(__FILE__, '/views/templates/admin/admin.tpl');
	}

	/**
	 * Insert order in Certissim table, with the state concerned by the payment method and the configuration
	 *
	 * @param array $params
	 * @return boolean
	 */
	public function hookNewOrder($params)
	{
		//checks if the order already exists
		$sql_secure = "SELECT *
			FROM `"._DB_PREFIX_.self::CERTISSIM_ORDER_TABLE_NAME."`
			WHERE `id_order`=".$params['order']->id;
		$select = Db::getInstance()->execute($sql_secure);
		$count = Db::getInstance()->numRows();
		//if order exists, end of process
		if (!$select || $count > 0)
		{
			CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, "Erreur de verif existence order. Select = ".(string) $select." et erreur = ".Db::getInstance()->getMsgError()." ou commande déjà insérée (count =".$count.")");
			return false;
		}

		//lists all payment methods
		if (_PS_VERSION_ < '1.5')
			$payments = $this->getInstalledPaymentModules();
		else
			$payments = PaymentModule::getPaymentModules();
		//looks for the payment module used
		$found = false;
		$payment_name = $params['order']->module;
		while (!$found && $payment = array_pop($payments))
			$found = ($payment_name == $payment['name']);

		//if module found
		if ($found)
		{
			//if PS 1.4 or lower
			if (_PS_VERSION_ < '1.5')
			{
				/** gets the id of the module * */
				$module = Module::getInstanceByName($payment['name']);
				$id_module = $module->id;
			}
			else
				$id_module = $payment['id_module'];

			CertissimLogger::insertLog(__METHOD__." : ".__LINE__, "Module détecté : $payment_name, 'CERTISSIM_".$id_module."_PAYMENT_TYPE' = ".Configuration::get('CERTISSIM_'.$id_module.'_PAYMENT_TYPE').", 'CERTISSIM_".$id_module."_PAYMENT_ENABLED' = ".Configuration::get('CERTISSIM_'.$id_module.'_PAYMENT_ENABLED'));
			//defines the state label according to the status of the payment module (activated for Certissim or not)
			$state_label = (Configuration::get('CERTISSIM_'.$id_module.'_PAYMENT_ENABLED') == '1' ? 'ready to send' : 'not concerned');
			CertissimLogger::insertLog(__METHOD__." : ".__LINE__, "State sélectionné : $state_label");
		}
		else
		{ //if module not found, end of process with log
			CertissimLogger::insertLog(__METHOD__." : ".__LINE__, "Erreur module paiement : module $payment_name non référencé dans liste (".implode(', ', $payments)."). Insertion commande annulée.");
			return false;
		}

		//gets the certissim_state
		$state_sql = "SELECT `id_certissim_state` FROM `"._DB_PREFIX_.self::CERTISSIM_STATE_TABLE_NAME."` WHERE `label`='$state_label'";
		$state_id = Db::getInstance()->getValue($state_sql);

		//inserts the order into the certissim table with the state previously set
		self::insertCertissimOrder(array(
			'id_order' => (int) $params['order']->id,
			'id_certissim_state' => $state_id,
			'customer_ip_address' => Tools::getRemoteAddr(),
			'date' => date('Y-m-d H:i:s'),
		));

		return true;
	}

	/**
	 * sends the order when the payment is confirmed only if order has never been sent and payment method activated for Certissim
	 * 
	 * @param array $params
	 * @return boolean
	 */
	public function hookPaymentConfirm($params)
	{
		//gets the actual certissim_state
		$id_order = $params['id_order'];
		$sql = "SELECT s.`label` 
			FROM `"._DB_PREFIX_.self::CERTISSIM_STATE_TABLE_NAME."` s
			INNER JOIN `"._DB_PREFIX_.self::CERTISSIM_ORDER_TABLE_NAME."` o
			ON o.id_certissim_state=s.id_certissim_state 
			WHERE o.id_order=$id_order";
		$state = Db::getInstance()->getValue($sql);
		CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, "Commande $id_order en état $state");

		if ($state == 'ready to send')
		{
			//if order ready to send, sending
			$sent_to_certissim = $this->buildAndSend($id_order);
			if (!$sent_to_certissim)
			{
				CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, "L'envoi de la commande $id_order vers Certissim a échoué.");
				return false;
			}
		}
		else //if order not ready to be sent: log
			CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, "La commande $id_order n'est pas dans le bon état pour envoi : $state");

		return true;
	}

	/**
	 * finds the order evaluation
	 * display the right tpl
	 * 
	 * @param array $params
	 * @return boolean
	 */
	public function hookAdminOrder($params)
	{
		//gets the actual certissim order state
		$sql_order = "SELECT s.`label`
			FROM `"._DB_PREFIX_.self::CERTISSIM_STATE_TABLE_NAME."` s
			INNER JOIN `"._DB_PREFIX_.self::CERTISSIM_ORDER_TABLE_NAME."` o
			ON o.`id_certissim_state` = s.`id_certissim_state`
			WHERE o.`id_order`=".$params['id_order'];
		$order_label = Db::getInstance()->getValue($sql_order);
		CertissimLogger::insertLog(__METHOD__." : ".__LINE__, "Order label : $order_label");

		//builds the Order object
		$order = new Order($params['id_order']);

		if (_PS_VERSION_ >= "1.5" && Shop::isFeatureActive())
			$shop_id = $order->id_shop;
		else
			$shop_id = null;
		//initialization of Certissim service
		$sac = new CertissimSac($shop_id);
		//actions depending on the certissim order state
		switch ($order_label)
		{
			//if order has been sent: loads the template 'sent'
			case 'sent':
				//sets the tpl name
				$template_name = "sent";
				//defines the URL for the action that will allow user to checkout the score of the order
				if (_PS_VERSION_ < '1.5')
					$url_update = 'index.php?tab=AdminCertissim&action=checkoutScore&id_order='.$params['id_order'].'&token='.Tools::getAdminTokenLite('AdminCertissim');
				else
					$url_update = $this->context->link->getAdminLink('AdminCertissim')."&id_order=".$params['id_order']."&action=checkoutScore";

				//assign the URL previously defined
				$this->smarty->assign('url_get_eval', $url_update);
				break;

			//if order has already been scored: loads the template 'scored'
			case 'scored':
				$template_name = $this->loadScoredTemplate($params['id_order'], $sac);
				break;

			//if the analysis returned an error: loads the error template
			case 'error':
				$template_name = $this->loadErrorTemplate($params['id_order'], $sac);
				break;

			//if the order is ready to be sent: loads the template 'ready-to-send'
			case 'ready to send':
				$template_name = 'ready-to-send';
				break;

			//if the order is not concerned: loads the template 'not-concerned'
			case 'not concerned':
				$template_name = 'not-concerned';
				//checks the payment status of the order
				$order = new Order($params['id_order']);
				if (version_compare(_PS_VERSION_, '1.5', '<'))
					$paid = $order->hasBeenPaid();
				else
					$paid = $order->getCurrentOrderState()->paid;

				$this->smarty->assign('paid', $paid);

				//if the order has been paid, the template contains a link allowing admin user to send the order to Certissim
				if ($paid)
				{
					//defines the URL of the action that sends the order to Certissim
					if (_PS_VERSION_ < '1.5')
						$url_send_order = 'index.php?tab=AdminCertissim&action=sendOrder&id_order='.$params['id_order'].'&token='.Tools::getAdminTokenLite('AdminCertissim');
					else
						$url_send_order = $this->context->link->getAdminLink('AdminCertissim')."&action=sendOrder&id_order=".$params['id_order'];

					$this->smarty->assign('url_send_order', $url_send_order);

					//checks if an error occured while sending the order to Certissim
					$order_array = $this->orderToArray($params['id_order'], array('error'));
					//if an error has been logged: loads the error message into the tpl
					if (!is_null($order_array['error']) && $order_array['error'] != '')
						$this->smarty->assign('txt', $this->l('An error has been encountered when the order has been sent to Certissim: ').'\''.$order_array['error'].'\' '.$this->l('Please check your configuration and send this order again.'));
					//if no error: the order is not concerned by Certissim analysis
					else
						$this->smarty->assign('txt', $this->l('The order has been paid with a payment method that is not configured for fraud screening or an error occured.'));
				}
				else
					$this->smarty->assign('txt', $this->l('The order has not been paid yet, and the payment method used is not configured for fraud screening.'));
				break;

			//if the certissim state is unknown: end of process
			default:
				CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, "Statut '$order_label' non reconnu pour la commande ".$params['id_order']);
				return false;
				break;
		}

		$this->smarty->assign('logo_path', __PS_BASE_URI__.'modules/'.$this->name.'/img/certissim_mini.png');

		//defines the width of the fieldset according to the PS version
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			return $this->smarty->display(_PS_MODULE_DIR_.$this->name.'/views/templates/hook/'.$template_name.'.tpl');

		$this->smarty->assign('width', '');
		return $this->display(__FILE__, $template_name.'.tpl');
	}

	/**
	 * loads the params for the error template and returns the template name
	 * 
	 * @param int $id_order
	 * @return string
	 */
	private function loadErrorTemplate($id_order, CertissimSac $sac)
	{
		//loads order params as an array
		$array_order = $this->orderToArray($id_order, array('error', 'date'));
		//builds the URL that allow admin user to checkout the order evaluation. Depends on the PS version
		if (_PS_VERSION_ < '1.5')
			$url_update = 'index.php?tab=AdminCertissim&action=checkoutScore&id_order='.$id_order.'&token='.Tools::getAdminTokenLite('AdminCertissim');
		else
			$url_update = $this->context->link->getAdminLink('AdminCertissim')."&id_order=$id_order&action=checkoutScore";


		//Smarty datas assignment
		$this->smarty->assign(array(
			'error' => Tools::safeOutput($array_order['error']),
			'url_vcd' => $sac->getVisuCheckUrl($id_order),
			'url_update' => $url_update,
		));
		return "error";
	}

	/**
	 * loads the params for the scored template and returns the template name
	 * 
	 * @param int $id_order
	 * @return string
	 */
	private function loadScoredTemplate($id_order, CertissimSac $sac)
	{
		//loads order params as an array
		$array_order = $this->orderToArray($id_order, array('date', 'score', 'profil', 'detail'));
		//builds the URL that allow admin user to checkout the order evaluation. Depends on the PS version
		if (_PS_VERSION_ < '1.5')
			$url_update = 'index.php?tab=AdminCertissim&action=checkoutScore&id_order='.$id_order.'&token='.Tools::getAdminTokenLite('AdminCertissim');
		else
			$url_update = $this->context->link->getAdminLink('AdminCertissim')."&id_order=$id_order&action=checkoutScore";


		//Smarty datas assignment
		$this->smarty->assign(array(
			'url_vcd' => $sac->getVisuCheckUrl($id_order),
			'url_checkout' => $url_update,
			'path_to_picto' => _MODULE_DIR_.'fianetfraud/img/'.$array_order['score'].'.gif',
			'score' => Tools::safeOutput($array_order['score']),
			'profil' => Tools::safeOutput($array_order['profil']),
			'detail' => Tools::safeOutput($array_order['detail']),
		));

		return "scored";
	}

	/**
	 * calls Certissim and updates the order in the certissim table and returns the new certissim state label
	 * 
	 * @param int $id_order
	 * @return string
	 */
	public function updateOrder($id_order)
	{
		if (_PS_VERSION_ >= "1.5" && Shop::isFeatureActive())
		{
			$order = new Order($id_order);
			$shop_id = $order->id_shop;
		}
		else
			$shop_id = null;

		//Certissim initialization
		$sac = new CertissimSac($shop_id);
		//gets existing results for the given order
		$result = new CertissimResultResponse($sac->getValidation($id_order)->getXML());

		CertissimLogger::insertLog(__METHOD__." : ".__LINE__, "Résultat de l'appel pour la commande $id_order");
		CertissimLogger::insertLog(__METHOD__." : ".__LINE__, $result);

		$this->handleResult($result);
	}

	public function handleResult($result)
	{
		CertissimLogger::insertLog(__METHOD__." : ".__LINE__, "Résultat XML : $result");
		//if an error occured: logs the error then stop the process
		if ($result->hasError())
		{
			//updates the order in the certissim table
			self::updateCertissimOrder($result->returnRefid(), array('error' => $result->returnMessage()));
			//certissim order state switch to 'error'
			self::switchOrderToState($result->returnRefid(), 'error');
			CertissimLogger::insertLog(__METHOD__." : ".__LINE__, 'Erreur rencontrée : '.$result->returnMessage());
			return;
		}

		//if order not found: logs error then ends the process
		if (!$result->hasBeenFound())
		{
			//if the order has been sent but is not found it means an error occured
			self::updateCertissimOrder($result->returnRefid(), array('error' => 'la commande a été envoyée mais n\'est pas retrouvée'));

			if (!self::getCertissimOrderState($result->returnRefid(), 'sent'))
				self::switchOrderToState($result->returnRefid(), 'not concerned');
			else
				self::switchOrderToState($result->returnRefid(), 'error');
			CertissimLogger::insertLog(__METHOD__." : ".__LINE__, 'Aucune transaction trouvée pour id_order='.$result->returnRefid());
			CertissimLogger::insertLog(__METHOD__." : ".__LINE__, "Réponse de Certissim : $result");

			return;
		}

		/** gets the most recent transaction (in case of multi ref submission, which is not allowed theorically)  * */
		$newer_transaction = $result->getMostRecentTransaction();

		CertissimLogger::insertLog(__METHOD__." : ".__LINE__, "----------- Transaction ---------------");
		CertissimLogger::insertLog(__METHOD__." : ".__LINE__, $newer_transaction->getXML());
		CertissimLogger::insertLog(__METHOD__." : ".__LINE__, "---------------------------------------");

		//action depending on the avancement attribute
		switch ($newer_transaction->returnAvancement())
		{
			//if order under analysis:
			case 'encours':
				//set the avancement to 'encours'
				self::updateCertissimOrder($result->returnRefid(), array('avancement' => $newer_transaction->returnAvancement()));

				//sets the certissim order state to 'sent'
				self::switchOrderToState($result->returnRefid(), 'sent');
				break;

			//of order scored
			case 'traitee':
				//updates database entry with date, score, detail and profile
				self::updateCertissimOrder($result->returnRefid(), array(
					'avancement' => $newer_transaction->returnAvancement(),
					'date' => $newer_transaction->getEvalDate(),
					'score' => $newer_transaction->getEval(),
					'detail' => $newer_transaction->getDetail(),
					'profil' => $newer_transaction->getEvalInfo(),
				));

				self::switchOrderToState($result->returnRefid(), 'scored');
				break;

			//if an error occured during the analysis
			case 'error':
				//updates the databse entry
				self::updateCertissimOrder($result->returnRefid(), array('avancement' => $newer_transaction->returnAvancement(), 'error' => $newer_transaction->getDetail()));

				//swith the certisism order state to 'error'
				self::switchOrderToState($result->returnRefid(), 'error');
				break;

			default:
				CertissimLogger::insertLog(__METHOD__." : ".__LINE__, 'Avancement '.$newer_transaction->returnAvancement().' inconnu.');
				break;
		}
	}

	/**
	 * loads the order $id_order and returns the chosen parameters as an array
	 * 
	 * @param int $id_order
	 * @param array $params
	 * @return array
	 */
	public function orderToArray($id_order, array $params = array())
	{
		//building SQL query
		if (empty($params)) //select all if no params given
			$select = "*";
		else //builds a SELECT query with all the params given in parameter
			$select = "`".implode('`, `', $params)."`";

		//buils the entire SQL query
		$sql = "SELECT ".$select." FROM `"._DB_PREFIX_.self::CERTISSIM_ORDER_TABLE_NAME."` WHERE `id_order`=".(int) $id_order;

		//SQL query exec
		$order = Db::getInstance()->getRow($sql);

		//returns the array
		return $order;
	}

	/**
	 * builds the XML stream for the order $id_order and sends it to Certissim
	 * update the order in certissim table
	 * returns true if order sent, false otherwise
	 * 
	 * @param int $id_order
	 * @return boolean
	 */
	public function buildAndSend($id_order)
	{
		$xml_order = $this->buildXMLOrder($id_order);

		return $this->sendXMLOrder($xml_order, $id_order);
	}

	private function buildXMLOrder($id_order)
	{

		CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, 'construction du flux pour order '.$id_order);
		$order = new Order($id_order);
		//gets back the delivery address
		$address_delivery = new Address((int) ($order->id_address_delivery));
		//gets back the invoice address
		$address_invoice = new Address((int) ($order->id_address_invoice));
		//gets back the customer
		$customer = new Customer((int) ($order->id_customer));
		//initializatino of the XML root: <control>
		$xml_element_control = new CertissimControl();

		//gets the lang used in the order
		$id_lang = $order->id_lang;

		//sets the gender, depends on PS version
		if (_PS_VERSION_ < '1.5')
			$gender = ($customer->id_gender == 2 ? $this->l('Ms.') : $this->l('Mr.'));
		else
		{
			$customer_gender = new Gender($customer->id_gender);
			$lang_id = Language::getIdByIso('en');
			if (empty($lang_id))
				$lang_id = Language::getIdByIso('fr');
			CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, "id_gender = ".$customer->id_gender.", gender name =".$customer_gender->name[$lang_id]);
			$gender = $this->l($customer_gender->name[$lang_id]);
		}

		//gets back the carrier used for this order
		$cart = new Cart($order->id_cart);
		$carrier = new Carrier((int) ($cart->id_carrier));

		//initialization of the element <utilisateur type='facturation'...>
		$xml_element_invoice_customer = new CertissimUtilisateur(
				'facturation',
				$gender,
				$address_invoice->lastname,
				$address_invoice->firstname,
				$address_invoice->company,
				$address_invoice->phone,
				$address_invoice->phone_mobile,
				null,
				$customer->email
		);

		//gets customer stats
		$customer_stats = $customer->getStats();

		//gets already existing orders for the customer
		$all_orders = Order::getCustomerOrders((int) ($customer->id));

		//initialization of the element <siteconso>
		$xml_element_invoice_customer_stats = new CertissimSiteconso(
				$customer_stats['total_orders'],
				$customer_stats['nb_orders'],
				$all_orders[count($all_orders) - 1]['date_add'],
				(count($all_orders) > 1 ? $all_orders[1]['date_add'] : null)
		);

		//gets back the invoice country
		$country = new Country((int) ($address_invoice->id_country));

		//initialization of the element <adresse type="facturation" ...>
		$xml_element_invoice_address = new CertissimAdresse(
				'facturation',
				$address_invoice->address1,
				$address_invoice->address2,
				$address_invoice->postcode,
				$address_invoice->city,
				$country->name[$id_lang]
		);


		//gets the used currency
		$currency = new Currency((int) ($order->id_currency));

		if (_PS_VERSION_ >= '1.5' && Shop::isFeatureActive())
			$siteid = Configuration::get('CERTISSIM_SITEID', null, null, $order->id_shop);
		else
			$siteid = Configuration::get('CERTISSIM_SITEID');

		//initialize the element <infocommande>
		$xml_element_order_details = new CertissimInfocommande(
				$siteid,
				$order->id,
				(string) $order->total_paid,
				self::getIpByOrder((int) ($order->id)),
				date('Y-m-d H:i:s'),
				$currency->iso_code
		);

		//gets the order products
		$products = $order->getProducts();

		//define the default product type (depends on PS version)
		if (_PS_VERSION_ >= '1.5' && Shop::isFeatureActive())
			$default_product_type = Configuration::get('CERTISSIM_DEFAULT_PRODUCT_TYPE', null, null, $order->id_shop);
		else
			$default_product_type = Configuration::get('CERTISSIM_DEFAULT_PRODUCT_TYPE');

		//initialization of the element <list ...>
		$xml_element_products_list = new CertissimProductList();
		//initialize the boolean that says if all the products in the order are downloadables
		$alldownloadables = true;
		foreach ($products as $product)
		{
			//check if the visited product is downloadable and update the boolean value
			$alldownloadables = $alldownloadables && strlen($product['download_hash']) > 0;
			//gets the main product category
			$product_categories = Product::getProductCategories((int) ($product['product_id']));
			$product_category = array_pop($product_categories);

			//initilization of the element <produit ...>
			$xml_element_product = new CertissimXMLElement("<produit></produit>");

			//gets the product certissim category (depends on PS version)
			if (_PS_VERSION_ >= '1.5' && Shop::isFeatureActive())
				$product_type = Configuration::get('CERTISSIM'.$product_category.'_PRODUCT_TYPE', null, null, $order->id_shop);
			else
				$product_type = Configuration::get('CERTISSIM'.$product_category.'_PRODUCT_TYPE');

			//if a certissim category is set: the type attribute takes the product certissim type value
			if ($product_type)
				$xml_element_product->addAttribute('type', Configuration::get('CERTISSIM'.$product_category.'_PRODUCT_TYPE', null, null, $order->id_shop));
			else //if certissim category not set: the type attribute takes the default value
				$xml_element_product->addAttribute('type', $default_product_type);

			//sets the product reference that will be inserted into the XML stream
			//uses the product name by default
			$product_ref = $product['product_name'];
			//prefers ean13 if defined
			if (!empty($product['product_ean13']))
				$product_ref = $product['product_ean13'];
			//prefers local reference if defined
			if (!empty($product['product_reference']))
				$product_ref = $product['product_reference'];
			//adds attributes ref, nb, prixunit, and sets the value of the element <product> with the product name
			$xml_element_product->addAttribute('ref', CertissimTools::normalizeString($product_ref));
			$xml_element_product->addAttribute('nb', $product['product_quantity']);
			$xml_element_product->addAttribute('prixunit', $product['total_price']);
			$xml_element_product->setValue(($product['product_name']));

			//adds the element <product> to the element <list>
			$xml_element_products_list->addProduit($xml_element_product);
		}

		if ($alldownloadables)
			$real_carrier_type = '5';
		//defines the real certissim carrier type (depends on PS version)
		elseif (_PS_VERSION_ >= '1.5' && Shop::isFeatureActive())
		//if selected carrier fianet type is defined, the type used will be the one got in the Configuration
			if (in_array(Configuration::get('CERTISSIM_'.(string) ($carrier->id).'_CARRIER_TYPE', null, null, $order->id_shop), array_keys($this->_carrier_types)))
			{
				$real_carrier_type = Configuration::get('CERTISSIM_'.(string) ($carrier->id).'_CARRIER_TYPE', null, null, $order->id_shop);
				$real_carrier_speed = Configuration::get('CERTISSIM_'.(string) ($carrier->id).'_CARRIER_SPEED', null, null, $order->id_shop);
			}
			//if selected carrier fianet type not defined, uses the default one
			else
			{
				$real_carrier_type = Configuration::get('CERTISSIM_DEFAULT_CARRIER_TYPE', null, null, $order->id_shop);
				$real_carrier_speed = Configuration::get('CERTISSIM_DEFAULT_CARRIER_SPEED', null, null, $order->id_shop);
			}
		//if selected carrier fianet type is defined, the type used will be the one got in the Configuration
		elseif (in_array(Configuration::get('CERTISSIM_'.(string) ($carrier->id).'_CARRIER_TYPE'), array_keys($this->_carrier_types)))
		{
			$real_carrier_type = Configuration::get('CERTISSIM_'.(string) ($carrier->id).'_CARRIER_TYPE');
			$real_carrier_speed = Configuration::get('CERTISSIM_'.(string) ($carrier->id).'_CARRIER_SPEED');
		}
		//if selected carrier fianet type not defined, uses the default one
		else
		{
			$real_carrier_type = Configuration::get('CERTISSIM_DEFAULT_CARRIER_TYPE');
			$real_carrier_speed = Configuration::get('CERTISSIM_DEFAULT_CARRIER_SPEED');
		}


		switch ($real_carrier_type)
		{
			//if the order is to be delivered at home: element <utilisateur type="livraison"...> has to be added
			case '4':
				//initialization of the element <utilisateur type="livraison" ...>			
				$xml_element_delivery_customer = new CertissimUtilisateur(
						'livraison',
						$customer->id_gender == 2 ? $this->l('Miss') : $this->l('Mister'),
						$address_delivery->lastname,
						$address_delivery->firstname,
						$address_delivery->company,
						$address_delivery->phone,
						$address_delivery->phone_mobile,
						null,
						$customer->email);

				//gets back the delivery country
				$country = new Country((int) ($address_delivery->id_country));

				//initialization of the element <adresse type="livraison" ...>
				$xml_element_delivery_address = new CertissimAdresse(
						'livraison',
						$address_delivery->address1,
						$address_delivery->address2,
						$address_delivery->postcode,
						$address_delivery->city,
						$country->name[$id_lang],
						null
				);

				$xml_pointrelais = null;
				break;

			//if delivery mode is downloadable 
			case '5':

				$xml_pointrelais = null;
				break;

			//if delivery mode is socolissimo
			case '6':

				$socolissimoinfo = $this->getSoColissimoInfo($id_order);

				$socolissimo_installed_module = Module::getInstanceByName('socolissimo');

				if ($socolissimoinfo != false)
				{
					foreach ($socolissimoinfo as $info)
					{
						//get socolissimo informations
						$delivery_mode = $info['delivery_mode'];
						$firstname = $info['prfirstname'];
						$name = $info['prname'];
						$mobile_phone = $info['cephonenumber'];
						$company_name = $info['cecompanyname'];
						$email = $info['ceemail'];
						$address1 = $info['pradress1'];
						$address2 = $info['pradress2'];
						$address3 = $info['pradress3'];
						$address4 = $info['pradress4'];
						$zipcode = $info['przipcode'];
						$city = $info['prtown'];

						//data is retrieved differently and depending on the version of the module
						if ($socolissimo_installed_module->version < '2.8')
						{
							$address2 = $address1;
							$address1 = $name;
							$country = 'FR';
						} else
							$country = $info['cecountry'];
					}

					//if delivery mode is DOM or RDV, <adresse type="livraison" ...> and <utilisateur type="livraison" ...> added
					if ($delivery_mode == 'DOM' || $delivery_mode == 'RDV')
					{
						$xml_element_delivery_customer = new CertissimUtilisateur(
								'livraison',
								$customer->id_gender == 2 ? $this->l('Miss') : $this->l('Mister'),
								$name,
								$firstname,
								$company_name,
								null,
								$mobile_phone,
								null,
								$email);

						$xml_element_delivery_address = new CertissimAdresse(
								'livraison',
								$address3,
								$address4,
								$zipcode,
								$city,
								$country,
								null
						);

						$real_carrier_type = 4;
						$xml_pointrelais = null;
					} else
					{
						//<pointrelais> added if delivery mode is not BPR, A2P or CIT
						$adressepointrelais = new CertissimXMLElement('<adresse></adresse>');
						$adressepointrelais->childRue1($address2);
						$adressepointrelais->childCpostal($zipcode);
						$adressepointrelais->childVille($city);
						$adressepointrelais->childPays($country);
						$xml_pointrelais = new CertissimPointrelais(null, $address1, $adressepointrelais);
						$real_carrier_type = 2;
					}
				} else
				{
					CertissimLogger::insertLog(__METHOD__." : ".__LINE__, "Flux incorrect : Module SoColissimo non installé ou non activé");
				}

				break;

			default:

				//if delivery mode is store pick up
				if ($real_carrier_type == 1)
				{
					//get shop information
					$address1 = Configuration::get('PS_SHOP_ADDR1');
					$address2 = Configuration::get('PS_SHOP_ADDR2');
					$zipcode = Configuration::get('PS_SHOP_CODE');
					$city = Configuration::get('PS_SHOP_CITY');
					$country = Configuration::get('PS_SHOP_COUNTRY');
					$shop_name = Configuration::get('PS_SHOP_NAME');
				} else
				{
					//gt delivery information
					$address1 = $address_delivery->address1;
					$address2 = $address_delivery->address2;
					$zipcode = $address_delivery->postcode;
					$city = $address_delivery->city;
					$country = $country->name[$id_lang];
					$shop_name = null;
				}

				$adressepointrelais = new CertissimXMLElement('<adresse></adresse>');
				$adressepointrelais->childRue1($address1);
				$adressepointrelais->childRue2($address2);
				$adressepointrelais->childCpostal($zipcode);
				$adressepointrelais->childVille($city);
				$adressepointrelais->childPays($country);
				$xml_pointrelais = new CertissimPointrelais(null, $shop_name, $adressepointrelais);

				break;
		}


		//initialization of the element <transport>
		$xml_element_carrier = new CertissimTransport(
				$real_carrier_type,
				$alldownloadables ? 'Téléchargement' : Tools::htmlentitiesUTF8($carrier->name),
				$alldownloadables ? '1' : $real_carrier_speed,
				$xml_pointrelais
		);


		//find the id of the payment module used (depends on the PS version)
		if (_PS_VERSION_ >= '1.5')
			$id_payment_module = PaymentModule::getModuleIdByName($order->module);
		else
		{
			$payment_module = Module::getInstanceByName($order->module);
			$id_payment_module = $payment_module->id;
		}

		//initialization of the element <paiement>
		if (_PS_VERSION_ >= '1.5' && Shop::isFeatureActive())
			$payment_type = $this->_payment_types[Configuration::get('CERTISSIM_'.$id_payment_module.'_PAYMENT_TYPE', null, null, $order->id_shop)];
		else
			$payment_type = $this->_payment_types[Configuration::get('CERTISSIM_'.$id_payment_module.'_PAYMENT_TYPE')];

		$xml_element_payment = new CertissimPaiement($payment_type);

		//initialization of the element <stack>
		$stack = new CertissimXMLElement("<stack></stack>");

		//agregates each elements in a main stream
		$xml_element_invoice_customer->childSiteconso($xml_element_invoice_customer_stats);
		$xml_element_control->childUtilisateur($xml_element_invoice_customer);
		$xml_element_control->childAdresse($xml_element_invoice_address);
		if (isset($xml_element_delivery_customer))
			$xml_element_control->childUtilisateur($xml_element_delivery_customer);
		if (isset($xml_element_delivery_address))
			$xml_element_control->childAdresse($xml_element_delivery_address);
		$xml_element_order_details->childTransport($xml_element_carrier);
		$xml_element_order_details->childList($xml_element_products_list);
		$xml_element_control->childInfocommande($xml_element_order_details);
		$xml_element_control->childPaiement($xml_element_payment);

		//add CDATA sections to protect against encoding issues
		$xml_element_control->addCdataSections();

		//add the <control> element into <stack>
		$stack->childControl($xml_element_control);
		CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, "---- flux généré pour commande $id_order ----");
		CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, $xml_element_control->getXML());
		CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, "---------------------------------------");

		return $stack;
	}

	private function sendXMLOrder($stack, $id_order)
	{
		//initializes Certissim
		if (_PS_VERSION_ >= '1.5' && Shop::isFeatureActive())
		{
			$order = new Order($id_order);
			$sac = new CertissimSac($order->id_shop);
		}
		else
			$sac = new CertissimSac();

		//sends the stream and catches the response
		$res = $sac->sendStacking($stack);
		$validstack = new CertissimValidstackResponse($res->getXML());

		//if an error occured: log
		if ($res === false)
		{ //connection to Certissim failed
			CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, "Connexion échoué pour la commande ".(int) $order->id);
			return false;
		}
		if ($validstack->hasFatalError())
		{ //connexion to Certissim failed
			CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, "L'envoi a échoué pour la commande ".(int) $order->id);
			CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, "Retour du script :");
			CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, $res);

			//updates the databse entry
			self::updateCertissimOrder($id_order, array('avancement' => 'error', 'error' => $validstack->getError()));

			//swith the certisism order state to 'error'
			self::switchOrderToState($id_order, 'not concerned');

			return false;
		}

		CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, "---- Retour de Fia-Net pour la commande ".(int) $order->id." ----");
		CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, "$res");
		CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, "-----------------------------------------------------------------");

		//updates the order in the certissim table according to the certissim response
		foreach ($validstack->getResults() as $result)
		{
			CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, "------");
			CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, $result->returnAvancement());
			CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, $result);
			CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, "------");
			switch ($result->returnAvancement())
			{
				//if order under analysis:
				case 'encours':
					//set the avancement to 'encours'
					self::updateCertissimOrder($id_order, array('avancement' => $result->returnAvancement()));

					//sets the certissim order state to 'sent'
					self::switchOrderToState($id_order, 'sent');
					break;

				//if an error occured during the analysis
				case 'error':
					//updates the databse entry
					self::updateCertissimOrder($id_order, array('avancement' => $result->returnAvancement(), 'error' => $result->getDetail()));

					//swith the certisism order state to 'error'
					self::switchOrderToState($id_order, 'error');
					break;

				default:
					CertissimLogger::insertLog(__METHOD__." : ".__LINE__, 'Avancement '.$result->returnAvancement().' inconnu.');
					break;
			}
		}
		return true;
	}

	/**
	 * récupère l'adresse ip de l'utilisateur qui a passé la commande
	 * 
	 * @param int $id_order
	 * @return string
	 */
	static function getIpByOrder($id_order)
	{
		$sql = "SELECT `customer_ip_address`
			FROM `"._DB_PREFIX_.self::CERTISSIM_ORDER_TABLE_NAME."`
			WHERE `id_order`=".(int) $id_order;
		return Db::getInstance()->getValue($sql);
	}

	/**
	 * updates the certissim_state of the order
	 * 
	 * @param int $id_order
	 * @param string $state_label
	 */
	public static function switchOrderToState($id_order, $state_label)
	{
		$sql_state = "SELECT `id_certissim_state` FROM `"._DB_PREFIX_.self::CERTISSIM_STATE_TABLE_NAME."` WHERE `label`='$state_label'";
		$id_state = Db::getInstance()->getValue($sql_state);
		self::updateCertissimOrder($id_order, array('id_certissim_state' => $id_state));
	}

	/**
	 * returns the certissim order state label for order $id_order if param $state_label is null, else returns true if the certissim order state label for order $id_order is $state_label, returns false otherwise
	 * 
	 * @param int $id_order
	 * @param string $state_label
	 * @return mixed bool or string
	 */
	public static function getCertissimOrderState($id_order, $state_label = null)
	{
		$sql_order_state = "
			SELECT cs.`label` 
			FROM `"._DB_PREFIX_.self::CERTISSIM_ORDER_TABLE_NAME."` co 
			INNER JOIN `"._DB_PREFIX_.self::CERTISSIM_STATE_TABLE_NAME."` cs 
			ON cs.`id_certissim_state`=co.`id_certissim_state` 
			WHERE co.`id_order`='$id_order'";
		$actual_state_label = Db::getInstance()->getValue($sql_order_state);

		if (is_null($state_label))
			return $actual_state_label;
		else
			return $actual_state_label == $state_label;
	}

	public function hookBackOfficeHeader($params)
	{
		return '<link rel="stylesheet" type="text/css" href="'.__PS_BASE_URI__.'modules/'.$this->name.'/css/toolbarAdmin.css" />';
	}

	/**
	 * inserts a certissim order into the certissim_order table with the fields given as a parameter and return true if success, false otherwise
	 * 
	 * @param array $fields fields and values to insert
	 * @return boolean
	 */
	private static function insertCertissimOrder(array $fields)
	{
		$fields = array_map('pSQL', $fields); //sanitizes the values
		$fieldnames = implode("`,`", array_keys($fields)); //generates the part of the SQL string that defines the field names to insert
		$fieldvalues = implode("','", $fields); //generates the part of the SQL string that defines the field values
		$sql = "INSERT INTO `"._DB_PREFIX_.self::CERTISSIM_ORDER_TABLE_NAME."` (`$fieldnames`) VALUES ('$fieldvalues')"; //builds the total SQL string
		$inserted = Db::getInstance()->execute($sql); //execute the SQL query
		//log
		if (!$inserted)
			CertissimLogger::insertLog(__METHOD__." : ".__LINE__, "Insertion échouée pour la requête : $sql");
		else
			CertissimLogger::insertLog(__METHOD__." : ".__LINE__, "Insertion OK pour la requête : $sql");

		return $inserted;
	}

	/**
	 * updates a certissim order with the fields given as a paramater and returns true if success, false otherwiser
	 * 
	 * @param int $id_order id of the order to update
	 * @param array $fields
	 * @return boolean
	 */
	public static function updateCertissimOrder($id_order, array $fields)
	{
		//if no fields to update, end of process
		if (empty($fields))
		{
			CertissimLogger::insertLog(__METHOD__." : ".__LINE__, "Tableau de champs vide.");
			return false;
		}

		//initialization of the part of the SQL string that defines the updates
		$set_string = "";
		foreach ($fields as $fieldname => $fieldvalue)
			$set_string .= "`$fieldname`='".pSQL(CertissimTools::convert_encoding($fieldvalue, ini_get('default_charset')))."', ";
		$set_string = substr($set_string, 0, -2); //removes the ', ' at the end of the string

		$sql = "UPDATE `"._DB_PREFIX_.self::CERTISSIM_ORDER_TABLE_NAME."` SET $set_string WHERE `id_order`='$id_order'"; //builds the total SQL string

		$updated = Db::getInstance()->execute($sql); //executes the SQL query
		if (!$updated)
			CertissimLogger::insertLog(__METHOD__." : ".__LINE__, "Mise à jour échouée pour la requête : $sql");
		else
			CertissimLogger::insertLog(__METHOD__." : ".__LINE__, "Mise à jour OK pour la requête : $sql");

		return $updated;
	}

	/**
	 * List all installed and active payment modules
	 * @see Module::getPaymentModules() if you need a list of module related to the user context
	 *
	 * @since 1.4.5
	 * @return array module informations
	 */
	public static function getInstalledPaymentModules()
	{
		return Db::getInstance()->executeS('
			SELECT DISTINCT m.`id_module`, h.`id_hook`, m.`name`, hm.`position`
			FROM `'._DB_PREFIX_.'module` m
			LEFT JOIN `'._DB_PREFIX_.'hook_module` hm ON hm.`id_module` = m.`id_module`
			LEFT JOIN `'._DB_PREFIX_.'hook` h ON hm.`id_hook` = h.`id_hook`
			WHERE h.`name` = \'payment\'
			AND m.`active` = 1
		');
	}

	/**
	 * For Prestashop < 1.4.5
	 * Return an available position in subtab for parent $id_parent
	 * 
	 * @param int $id_parent
	 * @return int 
	 */
	public function getNewLastPosition($id_parent)
	{
		return Db::getInstance()->getValue('SELECT IFNULL(MAX(position),0)+1 FROM `'._DB_PREFIX_.'tab` WHERE `id_parent` = '.(int) $id_parent);
	}

	/**
	 * Get all SoColissimo delivery information
	 * 
	 * @param type $id_order
	 * @return array 
	 */
	public function getSoColissimoInfo($id_order)
	{
		if (_PS_VERSION_ >= '1.5')
		//check if socolissimo is enabled on PS 1.5
			$socolissimo_is_enabled = Module::isEnabled('socolissimo');
		else
		//check if socolissimo is enabled on PS 1.4
			$socolissimo_is_enabled = $this->checkModuleisEnabled('socolissimo');

		if (Module::isInstalled('socolissimo') || $socolissimo_is_enabled)
		{
			$sql = "SELECT * FROM `"._DB_PREFIX_."socolissimo_delivery_info` WHERE `id_cart`= ".(int) $id_order;
			$query_result = Db::getInstance()->executeS($sql);
			return $query_result;
		} else
		{
			CertissimLogger::insertLog(__METHOD__." : ".__LINE__, "Module SoColissimo non installé ou non activé");
			return false;
		}
	}

	/**
	 * Check if address shop is not empty
	 * 
	 * @return boolean
	 */
	public function checkShopAddress()
	{
		$check = true;
		if (Configuration::get('PS_SHOP_ADDR1') == false || Configuration::get('PS_SHOP_ADDR1') == NULL || Configuration::get('PS_SHOP_ADDR1') == '')
		{
			$this->_errors[] = $this->l('Shop address cannot be empty');
			$check = false;
		}

		if (Configuration::get('PS_SHOP_CITY') == false || Configuration::get('PS_SHOP_CITY') == NULL || Configuration::get('PS_SHOP_CITY') == '')
		{
			$this->_errors[] = $this->l('Shop city cannot be empty');
			$check = false;
		}

		if (Configuration::get('PS_SHOP_CODE') == false || Configuration::get('PS_SHOP_CODE') == NULL || Configuration::get('PS_SHOP_CODE') == '')
		{
			$this->_errors[] = $this->l('Shop zipcode cannot be empty');
			$check = false;
		}

		if (Configuration::get('PS_SHOP_COUNTRY') == false || Configuration::get('PS_SHOP_COUNTRY') == NULL || Configuration::get('PS_SHOP_COUNTRY') == '')
		{
			$this->_errors[] = $this->l('Shop country cannot be empty');
			$check = false;
		}

		if ($check == false)
			$this->_errors[] = $this->l('You must check the address of your store');

		return $check;
	}

	/**
	 * For Prestashop 1.4, check if module is enabled, from Module::isEnabled($module_name)
	 * 
	 * @param string $module_name
	 * 
	 */
	public function checkModuleisEnabled($module_name)
	{
		return (bool) Db::getInstance()->getValue('SELECT `active` FROM `'._DB_PREFIX_.'module` WHERE `name` = \''.pSQL($module_name).'\'');
	}

}

