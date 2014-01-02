<?php

/*
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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2014 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */



if (!defined('_PS_VERSION_'))
	exit;

require_once _PS_MODULE_DIR_.'kwixo/lib/includes/includes.inc.php';

class Kwixo extends PaymentModule
{

	// category defined by Kwixo
	public static $_product_types = array(
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
		6 => 'Module So Colissimo'
	);
	private $_carrier_speeds = array(
		2 => 'Standard',
		1 => 'Express (-24h)'
	);
	private $_kwixo_banner_types = array(
		'standard' => 'Kwixo standard',
		'comptant' => 'Kwixo comptant',
		'credit' => 'Kwixo cr&eacute;dit',
	);
	private $_kwixo_banner_positions = array('en' =>
		array(
			'nothing' => 'Select a position',
			'left' => 'Left',
			'right' => 'Right',
			'top' => 'Top',
			'bottom' => 'Bottom',
		),
		'fr' => array(
			'nothing' => 'Sélectionnez une position',
			'left' => 'Gauche',
			'right' => 'Droite',
			'top' => 'Haut',
			'bottom' => 'Bas',
		)
	);
	private $_kwixo_banner_sizes = array('en' =>
		array(
			'250x250' => '250x250 size',
			'160x600' => '160x600 size',
			'468x60' => '468x60 size',
			'728x90' => '728x90 size',
		),
		'fr' => array(
			'250x250' => 'Taille 250x250',
			'160x600' => 'Taille 160x600',
			'468x60' => 'Taille 468x60',
			'728x90' => 'Taille 728x90',
		)
	);
	private $_kwixo_statuses = array(
		'test',
		'prod',
	);
	private $popup_link_standard = 'https://www.kwixo.com/static/payflow/html/popup-1x.htm';
	private $popup_link_comptant = 'https://www.kwixo.com/static/payflow/html/popup-1x-rnp.htm';
	private $popup_link_credit = 'https://www.kwixo.com/static/payflow/html/popup-3x.htm';
	public $_kwixo_order_statuses = array(
		0 => 'Paiement abandonn&eacute;',
		1 => 'Paiement accept&eacute;',
		2 => 'Paiement refus&eacute;',
		3 => 'Controle Kwixo en cours',
		4 => 'Attente validation Kwixo',
		6 => 'Cr&eacute;dit Kwixo &agrave; l étude',
		10 => 'Paiement Kwixo accept&eacute;',
		11 => 'Controle FIA-NET KO (d&eacute;lai expir&eacute;)',
		12 => 'Controle FIA-NET KO (risque de fraude)',
		13 => 'Commande confirm&eacute;e avec controle FIA-NET en cours',
		14 => 'Commande confirm&eacute;e avec controle FIA-NET KO',
		100 => 'Livraison OK',
		101 => 'Commande annul&eacute;e',
	);
	private $kw_os_statuses = array(
		'KW_OS_WAITING' => 'Attente validation Kwixo',
		'KW_OS_CREDIT' => "Cr&eacute;dit Kwixo &agrave; l'&eacute;tude",
		'KW_OS_CONTROL' => "Controle Kwixo en cours",
	);
	private $kw_os_payment_green_status = array(
		'KW_OS_PAYMENT_GREEN' => 'Paiement Kwixo accept&eacute; - score vert',
	);
	private $kw_os_payment_red_status = array(
		'KW_OS_PAYMENT_RED' => "Paiement Kwixo accept&eacute; - score rouge",
	);

	const KWIXO_ORDER_TABLE_NAME = 'kwixo_order';

	public function __construct()
	{
		$this->name = 'kwixo';
		$this->version = '6.3';
		$this->tab = 'payments_gateways';

		parent::__construct();

		$this->displayName = $this->l('Kwixo');
		$this->description = $this->l('Accepts payments by "Kwixo"');

		/* Backward compatibility */

		if (_PS_VERSION_ < '1.5')
		{
			if (file_exists(_PS_MODULE_DIR_.'backwardcompatibility/backward_compatibility/backward.php'))
				include(_PS_MODULE_DIR_.'backwardcompatibility/backward_compatibility/backward.php');
			else
			{
				$this->warning = $this->l('In order to work properly in PrestaShop v1.4, the Fia-Net - kwixo module requiers the backward compatibility module at least v0.4.').'<br />';
				$this->warning .= $this->l('You can download this module for free here: http://addons.prestashop.com/en/modules-prestashop/6222-backwardcompatibility.html');
			}
		}
	}

	/**
	 * Operation on module installation
	 * 
	 * @return boolean
	 */
	public function install()
	{
		//create log file
		KwixoLogger::insertLogKwixo(__METHOD__." : ".__LINE__, "Création du fichier de log");

		/** database tables creation * */
		$sqlfile = dirname(__FILE__).'/install.sql';

		if (!file_exists($sqlfile) || !($sql = Tools::file_get_contents($sqlfile)))
			return false;

		$sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
		$sql = str_replace('KWIXO_ORDER_TABLE_NAME', self::KWIXO_ORDER_TABLE_NAME, $sql);

		$queries = preg_split("/;\s*[\r\n]+/", $sql);

		foreach ($queries as $query)
			if (!Db::getInstance()->Execute(trim($query)))
			{
				KwixoLogger::insertLogKwixo(__METHOD__." : ".__LINE__, "Installation échouée, création base échouée : ".Db::getInstance()->getMsgError());
				return false;
			}

		//waiting payment status creation
		$this->createKwixoPaymentStatus($this->kw_os_statuses, '#3333FF', '', false, false, '', false);

		//validate green payment status creation
		$this->createKwixoPaymentStatus($this->kw_os_payment_green_status, '#DDEEFF', 'payment', true, true, true, true);

		//validate red payment status creation
		$this->createKwixoPaymentStatus($this->kw_os_payment_red_status, '#DDEEFF', 'payment_error', false, true, false, true);

		//hook register
		return (parent::install() &&
			$this->registerHook('newOrder') &&
			$this->registerHook('paymentConfirm') &&
			$this->registerHook('adminOrder') &&
			$this->registerHook('header') &&
			$this->registerHook('leftColumn') &&
			$this->registerHook('rightColumn') &&
			$this->registerHook('payment') &&
			$this->registerHook('extraRight') &&
			$this->registerHook('paymentReturn') &&
			$this->registerHook('top') &&
			$this->registerHook('footer') &&
			$this->registerHook('backOfficeHeader')
			);
	}

	/**
	 * Uninstall module
	 * 
	 * @return boolean
	 */
	public function uninstall()
	{

		if (!parent::uninstall())
			return false;
		else
		{
			KwixoLogger::insertLogKwixo(__METHOD__." : ".__LINE__, "Désinstallation du module");
			return true;
		}
	}

	/**
	 * Load administration form values
	 * 
	 */
	public function LoadAdminFormValues()
	{
		if (Tools::isSubmit('submitSettings'))
		{
			//saving all data posted
			$kwixo_login = Tools::getValue('kwixo_login');
			$kwixo_password = Tools::getValue('kwixo_password');
			$kwixo_siteid = Tools::getValue('kwixo_siteid');
			$kwixo_authkey = Tools::getValue('kwixo_authkey');
			$kwixo_status = Tools::getValue('kwixo_status');
			$kwixo_delivery = Tools::getValue('kwixo_delivery');
			$kwixo_option_standard = Tools::getValue('kwixo_option_standard');
			$kwixo_option_comptant = Tools::getValue('kwixo_option_comptant');
			$kwixo_option_credit = Tools::getValue('kwixo_option_credit');
			$kwixo_option_facturable = Tools::getValue('kwixo_option_facturable');
			$kwixo_email_test = Tools::getValue('kwixo_email_test');
			$kwixo_banner = Tools::getValue('kwixo_banner_types');
			$kwixo_banner_position = Tools::getValue('kwixo_banner_positions');
			$kwixo_banner_size = Tools::getValue('kwixo_banner_sizes');
			$kwixo_simulator = Tools::getValue('kwixo_show_simulator');
			$kwixo_default_product_type = Tools::getValue('kwixo_default_product_type');
			$kwixo_default_carrier_type = Tools::getValue('kwixo_default_carrier_type');
			$kwixo_default_carrier_speed = Tools::getValue('kwixo_default_carrier_speed');
		} else
		{

			//take database values or fix defaut values
			$kwixo_login = (Configuration::get('KWIXO_LOGIN') === false ? '' : Configuration::get('KWIXO_LOGIN'));
			$kwixo_password = (Configuration::get('KWIXO_PASSWORD') === false ? '' : Configuration::get('KWIXO_PASSWORD'));
			$kwixo_siteid = (Configuration::get('KWIXO_SITEID') === false ? '' : Configuration::get('KWIXO_SITEID'));
			$kwixo_authkey = (Configuration::get('KWIXO_AUTHKEY') === false ? '' : Configuration::get('KWIXO_AUTHKEY'));
			$kwixo_status = (Configuration::get('KWIXO_STATUS') === false ? '' : Configuration::get('KWIXO_STATUS'));
			$kwixo_delivery = (Configuration::get('KWIXO_DELIVERY') === false ? '7' : Configuration::get('KWIXO_DELIVERY'));
			$kwixo_option_standard = (Configuration::get('KWIXO_OPTION_STANDARD') === false ? '0' : Configuration::get('KWIXO_OPTION_STANDARD'));
			$kwixo_option_comptant = (Configuration::get('KWIXO_OPTION_COMPTANT') === false ? '0' : Configuration::get('KWIXO_OPTION_COMPTANT'));
			$kwixo_option_credit = (Configuration::get('KWIXO_OPTION_CREDIT') === false ? '0' : Configuration::get('KWIXO_OPTION_CREDIT'));
			$kwixo_option_facturable = (Configuration::get('KWIXO_OPTION_FACTURABLE') === false ? '0' : Configuration::get('KWIXO_OPTION_FACTURABLE'));
			$kwixo_email_test = (Configuration::get('KWIXO_EMAILS_TEST') === false ? '' : Configuration::get('KWIXO_EMAILS_TEST'));
			$kwixo_banner = (Configuration::get('KWIXO_BANNER_ENABLED') === false ? 'standard' : Configuration::get('KWIXO_BANNER_ENABLED'));
			$kwixo_banner_position = (Configuration::get('KWIXO_BANNER_POSITION') === false ? 'nothing' : Configuration::get('KWIXO_BANNER_POSITION'));
			$kwixo_banner_size = (Configuration::get('KWIXO_BANNER_SIZE') === false ? '250x250' : Configuration::get('KWIXO_BANNER_SIZE'));
			$kwixo_simulator = (Configuration::get('KWIXO_SHOW_SIMULATOR') === false ? '0' : Configuration::get('KWIXO_SHOW_SIMULATOR'));
			$kwixo_default_product_type = (Configuration::get('KWIXO_DEFAULT_PRODUCT_TYPE') === false ? '1' : Configuration::get('KWIXO_DEFAULT_PRODUCT_TYPE'));
			$kwixo_default_carrier_type = (Configuration::get('KWIXO_DEFAULT_CARRIER_TYPE') === false ? '4' : Configuration::get('KWIXO_DEFAULT_CARRIER_TYPE'));
			$kwixo_default_carrier_speed = (Configuration::get('KWIXO_DEFAULT_CARRIER_SPEED') === false ? '2' : Configuration::get('KWIXO_DEFAULT_CARRIER_SPEED'));
		}

		$adminform_values = array(
			'kwixo_login' => Tools::safeOutput($kwixo_login),
			'kwixo_password' => Tools::safeOutput($kwixo_password),
			'kwixo_siteid' => Tools::safeOutput($kwixo_siteid),
			'kwixo_authkey' => Tools::safeOutput($kwixo_authkey),
			'kwixo_status' => Tools::safeOutput($kwixo_status),
			'kwixo_delivery' => Tools::safeOutput($kwixo_delivery),
			'kwixo_option_standard' => Tools::safeOutput($kwixo_option_standard),
			'kwixo_option_comptant' => Tools::safeOutput($kwixo_option_comptant),
			'kwixo_option_credit' => Tools::safeOutput($kwixo_option_credit),
			'kwixo_option_facturable' => Tools::safeOutput($kwixo_option_facturable),
			'kwixo_email_test' => Tools::safeOutput($kwixo_email_test),
			'kwixo_banner_size_saved' => Tools::safeOutput($kwixo_banner_size),
			'kwixo_banner' => Tools::safeOutput($kwixo_banner),
			'kwixo_banner_position' => $kwixo_banner_position,
			'kwixo_show_simulator' => $kwixo_simulator,
			'kwixo_default_product_type' => Tools::safeOutput($kwixo_default_product_type),
			'kwixo_default_carrier_type' => Tools::safeOutput($kwixo_default_carrier_type),
			'kwixo_default_carrier_speed' => Tools::safeOutput($kwixo_default_carrier_speed),
		);

		//return array values for admin.tpl
		return $adminform_values;
	}

	/**
	 * build admin configuration
	 * 
	 * @return type
	 */
	public function getContent()
	{

		$head_msg = '';
		$base_url = __PS_BASE_URI__;
		$iso_lang_current = Language::getIsoById($this->context->language->id);

		//lists all categories
		$shop_categories = $this->loadProductCategories();

		//lists all carriers
		$shop_carriers = $this->loadCarriers();

		//Get log file
		$log_content = KwixoLogger::getLogContent();

		//check if form is submit
		if (Tools::isSubmit('submitSettings'))
		{
			//if the form is correctly saved
			if ($this->processForm())

			//adds a confirmation message
				$head_msg = $this->displayConfirmation($this->l('Configuration updated.'));
			else
			{
				//if errors have been encountered while validating the form
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

		//load submitted or default values to administration form 
		$adminform_values = $this->LoadAdminFormValues();


		//admin shop address link and log file url
		if (_PS_VERSION_ < '1.5')
			$link_shop_setting = 'index.php?tab=AdminContact&token='.Tools::getAdminTokenLite('AdminContact');
		else
			$link_shop_setting = $this->context->link->getAdminLink('AdminStores').'&token='.Tools::getAdminTokenLite('AdminStores');


		$this->smarty->assign($adminform_values);

		$this->smarty->assign(array(
			'head_msg' => $head_msg,
			'kwixo_statuses' => $this->_kwixo_statuses,
			'kwixo_banner_types' => $this->_kwixo_banner_types,
			'kwixo_banner_sizes' => $this->_kwixo_banner_sizes[$iso_lang_current],
			'kwixo_banner_positions' => $this->_kwixo_banner_positions[$iso_lang_current],
			'shop_categories' => $shop_categories,
			'kwixo_product_types' => self::$_product_types,
			'shop_carriers' => $shop_carriers,
			'kwixo_carrier_types' => $this->_carrier_types,
			'kwixo_carrier_speeds' => $this->_carrier_speeds,
			'logo_account_path' => $base_url.'modules/'.$this->name.'/img/account.gif',
			'logo_categories_path' => __PS_BASE_URI__.'modules/'.$this->name.'/img/categories.gif',
			'logo_carriers_path' => __PS_BASE_URI__.'modules/'.$this->name.'/img/carriers.gif',
			'logo_display_path' => __PS_BASE_URI__.'modules/'.$this->name.'/img/photo.gif',
			'logo_kwixo' => __PS_BASE_URI__.'modules/'.$this->name.'/img/logo_kwixo.png',
			'icon_kwixo' => __PS_BASE_URI__.'modules/'.$this->name.'/img/kwixo.png',
			'logo_information' => __PS_BASE_URI__.'modules/'.$this->name.'/img/information.png',
			'logo_warning' => $base_url.'modules/'.$this->name.'/img/no.gif',
			'link_shop_setting' => $link_shop_setting,
			'log_content' => $log_content,
		));

		return $this->display(__FILE__, '/views/templates/admin/admin.tpl');
	}

	/**
	 * Returns true if the form is valid, false otherwise
	 * 
	 * @return type
	 */
	private function formIsValid()
	{

		$iso_lang_current = Language::getIsoById($this->context->language->id);

		//check fields form
		if (strlen(Tools::getValue('kwixo_login')) < 1)
			$this->_errors[] = $this->l('Login cannot be empty');

		if (strlen(Tools::getValue('kwixo_password')) < 1)
			$this->_errors[] = $this->l('Password cannot be empty');

		if (strlen(Tools::getValue('kwixo_siteid')) < 1)
			$this->_errors[] = $this->l('Siteid cannot be empty');

		if (strlen(Tools::getValue('kwixo_authkey')) < 1)
			$this->_errors[] = $this->l('Authkey cannot be empty');

		if (!preg_match('#^[0-9]+$#', Tools::getValue('kwixo_siteid')))
			$this->_errors[] = $this->l('Siteid has to be integer');

		if (!in_array(Tools::getValue('kwixo_status'), $this->_kwixo_statuses))
			$this->_errors[] = $this->l('You must give a correct status');

		//check kwixo option
		if (!in_array(Tools::getValue('kwixo_option_standard'), array('0', '1')))
			$this->_errors[] = $this->l('You must give a correct kwixo standard option');

		if (!in_array(Tools::getValue('kwixo_option_comptant'), array('0', '1')))
			$this->_errors[] = $this->l('You must give a correct kwixo comptant option');

		if (!in_array(Tools::getValue('kwixo_option_credit'), array('0', '1')))
			$this->_errors[] = $this->l('You must give a correct kwixo credit option');

		if (!in_array(Tools::getValue('kwixo_option_facturable'), array('0', '1')))
			$this->_errors[] = $this->l('You must give a correct kwixo facturable option');

		//check banner type
		if (!in_array(Tools::getValue('kwixo_banner_types'), array_keys($this->_kwixo_banner_types)))
			$this->_errors[] = $this->l('You must give a correct banner type');

		//check banner size
		if (!in_array(Tools::getValue('kwixo_banner_sizes'), array_keys($this->_kwixo_banner_sizes[$iso_lang_current])))
			$this->_errors[] = $this->l('You must give a correct banner size');

		//check banner position
		if (!in_array(Tools::getValue('kwixo_banner_positions'), array_keys($this->_kwixo_banner_positions[$iso_lang_current])))
			$this->_errors[] = $this->l('You must give a correct banner position');

		//check kwixo simulator
		if (!in_array(Tools::getValue('kwixo_show_simulator'), array('0', '1')))
			$this->_errors[] = $this->l('You must give a correct simulator status');

		//check defaut product type
		if (!in_array(Tools::getValue('kwixo_default_product_type'), array_keys(self::$_product_types)))
			$this->_errors[] = $this->l('You must configure a valid default product type');

		//check products type
		$shop_categories = $this->loadProductCategories();
		$product_type_error = false;
		foreach (array_keys($shop_categories) as $id)
			if (!in_array(Tools::getValue('kwixo_'.$id.'_product_type'), array_keys(self::$_product_types)) && Tools::getValue('kwixo_'.$id.'_product_type') != 0)
				$product_type_error = true;
		if ($product_type_error)
			$this->_errors[] = $this->l('You must configure a valid product type');

		//check defaut carrier type
		if (!in_array(Tools::getValue('kwixo_default_carrier_type'), array_keys($this->_carrier_types)))
			$this->_errors[] = $this->l('You must configure a valid default carrier type');

		//check defaut carrier speed
		if (!in_array(Tools::getValue('kwixo_default_carrier_speed'), array_keys($this->_carrier_speeds)))
			$this->_errors[] = $this->l('You must give a correct carrier speed');

		//check carrier type and carrier speed
		$shop_carriers = $this->loadCarriers();
		$carrier_type_error = false;
		$carrier_speed_error = false;
		$delivery_shop = false;
		foreach (array_keys($shop_carriers) as $id)
		{
			if (!in_array(Tools::getValue('kwixo_'.$id.'_carrier_type'), array_keys($this->_carrier_types)) && Tools::getValue('kwixo_'.$id.'_carrier_type') != 0)
				$carrier_type_error = true;
			if (!in_array(Tools::getValue('kwixo_'.$id.'_carrier_speed'), array_keys($this->_carrier_speeds)))
				$carrier_speed_error = true;

			if (Tools::getValue('kwixo_'.$id.'_carrier_type') == 6)
			{

				if (_PS_VERSION_ >= '1.5')
				//check if socolissimo is enabled on PS 1.5
					$socolissimo_is_enabled = Module::isEnabled('socolissimo');
				else
				//check if socolissimo is enabled on PS 1.4
					$socolissimo_is_enabled = $this->checkModuleisEnabled('socolissimo');

				if (!Module::isInstalled('socolissimo') || !$socolissimo_is_enabled)
				{
					$this->_errors[] = $this->l('Invalid carrier type for carrier:').$this->l('SoColissimo module is not installed or not enabled');
				}
			}


			if (Tools::getValue('kwixo_'.$id.'_carrier_type') == 1)
				$delivery_shop = true;
		}

		if ($carrier_type_error)
			$this->_errors[] = $this->l('You must configure a valid carrier type');

		if ($carrier_speed_error)
			$this->_errors[] = $this->l('You must configure a valid carrier speed');

		//check delivery
		if (strlen(Tools::getValue('kwixo_delivery')) < 1)
			$this->_errors[] = $this->l('Delivery cannot be empty');

		if (!preg_match('#^[0-9]+$#', Tools::getValue('kwixo_delivery')))
			$this->_errors[] = $this->l('Delivery has to be integer');

		//check correct banner size correspond to the right banner position
		if (in_array(Tools::getValue('kwixo_banner_positions'), array('right', 'left')) && in_array(Tools::getValue('kwixo_banner_sizes'), array('468x60', '728x90')))
			$this->_errors[] = $this->l('Incompatible position and size banner');

		if (in_array(Tools::getValue('kwixo_banner_positions'), array('top', 'bottom')) && in_array(Tools::getValue('kwixo_banner_sizes'), array('250x250', '160x600')))
			$this->_errors[] = $this->l('Incompatible position and size banner');

		//check if shop address entered if selected carrier or default carrier selected is 1
		if (Tools::getValue('kwixo_default_carrier_type') == 1 || $delivery_shop)
			$this->checkShopAddress();

		return empty($this->_errors);
	}

	/**
	 * Save all admin settings on database
	 * 
	 * @return boolean
	 */
	private function processForm()
	{
		//if the form is valid


		if ($this->formIsValid())
		{

			//global parameters update
			/** KWIXO paramaters * */
			Configuration::updateValue('KWIXO_LOGIN', Tools::getValue('kwixo_login'));
			Configuration::updateValue('KWIXO_PASSWORD', Tools::getValue('kwixo_password'));
			Configuration::updateValue('KWIXO_AUTHKEY', urlencode(Tools::getValue('kwixo_authkey')));
			Configuration::updateValue('KWIXO_SITEID', Tools::getValue('kwixo_siteid'));
			Configuration::updateValue('KWIXO_STATUS', Tools::getValue('kwixo_status'));
			Configuration::updateValue('KWIXO_DELIVERY', Tools::getValue('kwixo_delivery'));
			Configuration::updateValue('KWIXO_OPTION_STANDARD', ((int) Tools::getValue('kwixo_option_standard') == 1 ? '1' : '0'));
			Configuration::updateValue('KWIXO_OPTION_COMPTANT', ((int) Tools::getValue('kwixo_option_comptant') == 1 ? '1' : '0'));
			Configuration::updateValue('KWIXO_OPTION_CREDIT', ((int) Tools::getValue('kwixo_option_credit') == 1 ? '1' : '0'));
			Configuration::updateValue('KWIXO_OPTION_FACTURABLE', ((int) Tools::getValue('kwixo_option_facturable') == 1 ? '1' : '0'));
			Configuration::updateValue('KWIXO_EMAILS_TEST', htmlentities(Tools::getValue('kwixo_email_test')));
			Configuration::updateValue('KWIXO_BANNER_ENABLED', Tools::getValue('kwixo_banner_types'));
			Configuration::updateValue('KWIXO_BANNER_POSITION', Tools::getValue('kwixo_banner_positions'));
			Configuration::updateValue('KWIXO_BANNER_SIZE', Tools::getValue('kwixo_banner_sizes'));
			Configuration::updateValue('KWIXO_SHOW_SIMULATOR', ((int) Tools::getValue('kwixo_show_simulator') == 1 ? '1' : '0'));

			/** categories configuration * */
			//lists all product categories

			Configuration::updateValue('KWIXO_DEFAULT_PRODUCT_TYPE', Tools::getValue('kwixo_default_product_type'));
			$shop_categories = $this->loadProductCategories();

			foreach (array_keys($shop_categories) as $id)
				Configuration::updateValue('KWIXO_PRODUCT_TYPE_'.$id.'', Tools::getValue('kwixo_'.$id.'_product_type'));

			/** carriers update * */
			//lists all carriers

			Configuration::updateValue('KWIXO_DEFAULT_CARRIER_TYPE', Tools::getValue('kwixo_default_carrier_type'));
			Configuration::updateValue('KWIXO_DEFAULT_CARRIER_SPEED', Tools::getValue('kwixo_default_carrier_speed'));

			$shop_carriers = $this->loadCarriers();

			foreach (array_keys($shop_carriers) as $id)
			{
				Configuration::updateValue('KWIXO_CARRIER_TYPE_'.$id.'', Tools::getValue('kwixo_'.$id.'_carrier_type'));
				Configuration::updateValue('KWIXO_CARRIER_SPEED_'.$id.'', Tools::getValue('kwixo_'.$id.'_carrier_speed'));
			}

			KwixoLogger::insertLogKwixo(__METHOD__." : ".__LINE__, "Configuration module mise à jour");

			return true;
		}
		else
			return false;
	}

	/**
	 * Load shop product categories
	 * 
	 * @return type
	 */
	private function loadProductCategories()
	{
		$categories = Category::getSimpleCategories($this->context->language->id);
		$shop_categories = array();

		foreach ($categories as $category)
		{
			$kwixo_type = Tools::isSubmit('kwixo_'.$category['id_category'].'_product_type') ? Tools::getValue('kwixo_'.$category['id_category'].'_product_type') : Configuration::get('KWIXO_PRODUCT_TYPE_'.$category['id_category'].'');

			$shop_categories[$category['id_category']] = array(
				'name' => $category['name'],
				'kwixo_type' => $kwixo_type
			);
		}

		return $shop_categories;
	}

	/**
	 * Load shop carriers
	 * 
	 * @return type
	 */
	private function loadCarriers()
	{
		$carriers = Carrier::getCarriers($this->context->language->id, false, false, false, null, ALL_CARRIERS);
		$shop_carriers = array();

		foreach ($carriers as $carrier)
		{
			$kwixo_type = Tools::isSubmit('kwixo_'.$carrier['id_carrier'].'_carrier_type') ? Tools::getValue('kwixo_'.$carrier['id_carrier'].'_carrier_type') : Configuration::get('KWIXO_CARRIER_TYPE_'.$carrier['id_carrier'].'');
			$kwixo_speed = Tools::isSubmit('kwixo_'.$carrier['id_carrier'].'_carrier_speed') ? Tools::getValue('kwixo_'.$carrier['id_carrier'].'_carrier_speed') : Configuration::get('KWIXO_CARRIER_SPEED_'.$carrier['id_carrier'].'');
			$shop_carriers[$carrier['id_carrier']] = array(
				'name' => $carrier['name'],
				'kwixo_type' => $kwixo_type,
				'kwixo_speed' => $kwixo_speed
			);
		}

		return $shop_carriers;
	}

	public function hookbackOfficeHeader($params)
	{
		$html = '<script type="text/javascript" src="'.$this->_path.'js/javascript.js"></script>';

		return $html;
	}

	/**
	 * Load css and javascript files
	 * 
	 * @param type $params
	 */
	public function hookHeader($params)
	{
		if (_PS_VERSION_ < '1.5')
			Tools::addCSS($this->_path.'/css/kwixo.css', 'all');
		else
			$this->context->controller->addCSS($this->_path.'/css/kwixo.css', 'all');
	}

	/**
	 * Load banner tpl with right position
	 * 
	 * @return type
	 */
	public function hookRightColumn()
	{
		if (Configuration::get('KWIXO_BANNER_POSITION') == 'right')
			return $this->loadBannerTPL();
		return false;
	}

	/**
	 * Load banner tpl with left position
	 * 
	 * @return type
	 */
	public function hookLeftColumn()
	{
		if (Configuration::get('KWIXO_BANNER_POSITION') == 'left')
			return $this->loadBannerTPL();
		return false;
	}

	/**
	 * Load banner tpl with top position
	 * 
	 * @return type
	 */
	public function hookTop()
	{
		if (Configuration::get('KWIXO_BANNER_POSITION') == 'top')
			return $this->loadBannerTPL();
		return false;
	}

	/**
	 * Load banner tpl with bottom position
	 * 
	 * @return type
	 */
	public function hookFooter()
	{
		if (Configuration::get('KWIXO_BANNER_POSITION') == 'bottom')
			return $this->loadBannerTPL();
		return false;
	}

	/**
	 * Load banner tpl
	 * 
	 * @return type
	 */
	private function loadBannerTPL()
	{
		//retrieve logo size on database and load the corresponding image

		$size_tab = explode('x', Configuration::get('KWIXO_BANNER_SIZE'));

		$banner_width = $size_tab[0];
		$banner_height = $size_tab[1];

		if (_PS_VERSION_ < '1.5')
		{
			if ($banner_width == '250')
				$banner_width_custom = '100%';
			else
				$banner_width_custom = $banner_width;
		}
		else
			$banner_width_custom = $banner_width;

		if (Configuration::get('KWIXO_BANNER_ENABLED') == 'standard')
			$popup_link = $this->popup_link_standard;

		elseif (Configuration::get('KWIXO_BANNER_ENABLED') == 'comptant')
			$popup_link = $this->popup_link_comptant;

		elseif (Configuration::get('KWIXO_BANNER_ENABLED') == 'credit')
			$popup_link = $this->popup_link_credit;

		$this->smarty->assign(array(
			'banner' => htmlentities(__PS_BASE_URI__.'modules/'.$this->name.'/img/banners/kwixo_'.Configuration::get('KWIXO_BANNER_ENABLED').'_'.$banner_width.'x'.$banner_height.'.gif', ENT_QUOTES),
			'popup_link' => $popup_link,
			'banner_width' => $banner_width_custom,
			'banner_height' => $banner_height,
		));

		return $this->display(__FILE__, '/views/templates/hook/banner_img.tpl');
	}

	/**
	 * Show kwixo's payment on payment page
	 * 
	 * @param type $params
	 * @return boolean
	 */
	public function hookPayment($params)
	{
		if (!$this->active)
			return;

		$total_cart = $params['cart']->getOrderTotal(true);

		if (_PS_VERSION_ < '1.5')
			$kwixo = new KwixoPayment();
		else
			$kwixo = new KwixoPayment($params['cart']->id_shop);

		if ($kwixo->getStatus() == 'test')
		{
			$customer = new Customer((int) $params['cart']->id_customer);
			$customer_mail = $customer->email;

			if (Configuration::get('KWIXO_EMAILS_TEST') != '')
			{
				$mails_test = explode(',', str_replace(' ', '', Configuration::get('KWIXO_EMAILS_TEST')));
				if (!in_array($customer_mail, $mails_test))
				{
					KwixoLogger::insertLogKwixo(__METHOD__." : ".__LINE__, "L'adresse $customer_mail n'est pas autorisée à utiliser Kwixo en test.");
					KwixoLogger::insertLogKwixo(__METHOD__." : ".__LINE__, "Liste des adresses autorisées : ".implode(', ', $mails_test));

					return false;
				}
			}
		}

		$mobile_detect = new MobileDetect();
		$mobile = $mobile_detect->isMobile();

		$kwixo_standard = (Configuration::get('KWIXO_OPTION_STANDARD') == '1') ? '1' : '0';
		$kwixo_comptant = (Configuration::get('KWIXO_OPTION_COMPTANT') == '1') ? '1' : '0';
		$kwixo_facturable = (Configuration::get('KWIXO_OPTION_FACTURABLE') == '1') ? '1' : '0';
		$kwixo_credit = (Configuration::get('KWIXO_OPTION_CREDIT') == '1' && $total_cart >= 150 && $total_cart <= 4000 && !($mobile)) ? '1' : '0';

		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			//token security
			$token = Tools::getAdminToken($kwixo->getSiteid().$kwixo->getAuthkey());

			$kwixo_std_link = __PS_BASE_URI__.'modules/'.$this->name.'/sendtoKwixo.php?payment=1&token='.$token;
			$kwixo_cpt_link = __PS_BASE_URI__.'modules/'.$this->name.'/sendtoKwixo.php?payment=2&token='.$token;
			$kwixo_credit_link = __PS_BASE_URI__.'modules/'.$this->name.'/sendtoKwixo.php?payment=3&token='.$token;
			$kwixo_facturable_link = __PS_BASE_URI__.'modules/'.$this->name.'/sendtoKwixo.php?payment=4&token='.$token;
		} else
		{
			$link = new Link();
			$kwixo_std_link = $link->getModuleLink('kwixo', 'payment', array('payment' => '1'), true);
			$kwixo_cpt_link = $link->getModuleLink('kwixo', 'payment', array('payment' => '2'), true);
			$kwixo_credit_link = $link->getModuleLink('kwixo', 'payment', array('payment' => '3'), true);
			$kwixo_facturable_link = $link->getModuleLink('kwixo', 'payment', array('payment' => '4'), true);
		}

		$this->smarty->assign(array(
			'kwixo_standard' => $kwixo_standard,
			'kwixo_comptant' => $kwixo_comptant,
			'kwixo_credit' => $kwixo_credit,
			'kwixo_facturable' => $kwixo_facturable,
			'kwixo_std_link' => $kwixo_std_link,
			'kwixo_cpt_link' => $kwixo_cpt_link,
			'kwixo_credit_link' => $kwixo_credit_link,
			'kwixo_facturable_link' => $kwixo_facturable_link,
			'url_simul' => 'https://secure.kwixo.com/credit/calculator.htm?merchantId='.$kwixo->getSiteId().'&amount='.$total_cart,
			'logo_kwixo_standard' => __PS_BASE_URI__.'modules/'.$this->name.'/img/kwixo_standard.jpg',
			'logo_kwixo_comptant' => __PS_BASE_URI__.'modules/'.$this->name.'/img/kwixo_comptant.jpg',
			'logo_kwixo_credit' => __PS_BASE_URI__.'modules/'.$this->name.'/img/kwixo_credit.jpg',
		));

		return $this->display(__FILE__, '/views/templates/hook/payment_short_description.tpl');
	}

	/**
	 * Load simulator on product page if KWIXO_SHOW_SIMULATOR is enabled
	 * 
	 * @global type $smarty
	 * @param type $params
	 * @return boolean
	 */
	public function hookExtraRight($params)
	{
		if (Configuration::get('KWIXO_SHOW_SIMULATOR') == '1')
		{
			if (_PS_VERSION_ < '1.5')
				$kwixo = new KwixoPayment();
			else
				$kwixo = new KwixoPayment($params['cart']->id_shop);

			$product = new Product(Tools::getValue('id_product'));

			//check if price > 150 euro
			if ($product->getPrice() >= '150' && Configuration::get('KWIXO_OPTION_CREDIT') == '1')
			{
				$this->smarty->assign('urlsimul', "https://secure.kwixo.com/credit/calculator.htm?merchantId=".$kwixo->getSiteId()."&amount=".$product->getPrice());
				$this->smarty->assign('logo_simul_credit', __PS_BASE_URI__.'modules/'.$this->name.'/img/simulcred.jpg');
				return $this->display(__FILE__, '/views/templates/hook/simulcred.tpl');
			}
			else
				return false;
		}
		else
			return false;
	}

	/**
	 * Show Kwixo evaluation status on detail order if kwixo order table contains orders
	 * 
	 * @param type $params
	 * @return boolean
	 */
	public function hookAdminOrder($params)
	{
		$id_order = $params['id_order'];
		$info_order = $this->getInfoKwixoOrder($id_order);
		$order = new Order((int) $id_order);

		if (!$info_order === false)
		{
			foreach ($info_order as $info)
			{
				$kwixo_tagline_state = $info['kwixo_tagline_state'];
				$transaction_id = $info['kwixo_transaction_id'];
				$date_tagline = $info['date_tagline'];
			}

			$show_last_tagline = ($kwixo_tagline_state == NULL ? '0' : '1');

			if (_PS_VERSION_ < '1.5')
				$kwixo = new KwixoPayment();
			else
				$kwixo = new KwixoPayment($order->id_shop);

			$token = Tools::getAdminToken($kwixo->getSiteid().$kwixo->getAuthkey().$kwixo->getLogin());

			$this->smarty->assign(array(
				'date_tagline' => $date_tagline,
				'transaction_id' => $transaction_id,
				'tag_tagline' => $kwixo_tagline_state,
				'show_last_tagline' => $show_last_tagline,
				'kwixo_statuses' => $this->_kwixo_order_statuses,
				'id_order' => $id_order,
				'tid' => $transaction_id,
				'token' => $token,
				'logo_kwixo' => __PS_BASE_URI__.'modules/'.$this->name.'/img/kwixo.png',
				'img_loader' => __PS_BASE_URI__.'modules/'.$this->name.'/img/loader.gif',
			));

			return $this->display(__FILE__, '/views/templates/admin/tagline.tpl');
		}
		else
			return false;
	}

	/**
	 * Retrieve all kwixo information order and return it
	 * 
	 * @param int $id_order
	 * @return boolean
	 */
	public function getInfoKwixoOrder($id_order)
	{
		$sql = "SELECT * FROM `"._DB_PREFIX_.self::KWIXO_ORDER_TABLE_NAME."` WHERE `id_order`= ".(int) $id_order;
		$query_result = Db::getInstance()->executeS($sql);

		if (!$query_result === false)
		{
			KwixoLogger::insertLogKwixo(__METHOD__." : ".__LINE__, "Récupération infos commande réussie : ".$id_order);
			return $query_result;
		} else
		{
			KwixoLogger::insertLogKwixo(__METHOD__." : ".__LINE__, "Récupération infos commande échouée : ".$id_order);
			return false;
		}
	}

	/**
	 * Manage Kwixo table orders
	 * insert or update Kwixo orders in different mode : urlsys, urlcall or tagline
	 * 
	 * @param int $ref_id
	 * @param int $tag
	 * @param varchar $transaction_id
	 * @param int $id_cart
	 * @param varchar $mode
	 * @return boolean

	 */
	public function manageKwixoOrder($ref_id, $tag, $transaction_id, $id_cart, $mode)
	{
		$sql = "SELECT `id_order` FROM `"._DB_PREFIX_.self::KWIXO_ORDER_TABLE_NAME."` WHERE `id_order`= ".(int) $ref_id;
		$query_result = Db::getInstance()->getRow($sql);

		if ($query_result == false)
		{
			if ($mode == 'urlsys')
			//insert new kwixo order on database if urlsys script is called
				$sql = "INSERT INTO `"._DB_PREFIX_.self::KWIXO_ORDER_TABLE_NAME."` (`id_order`,`kwixo_urlsys_state`, `kwixo_transaction_id`, `id_cart`, `date_urlsys`) 
				VALUES (".(int) $ref_id.", ".(int) $tag.", '".pSQL((string) $transaction_id)."', ".(int) $id_cart.", '".pSQL((string) date('d-m-Y H:i:s'))."')";
			elseif ($mode == 'tagline')
			//insert new kwixo order on database if tagline script is called
				$sql = "INSERT INTO `"._DB_PREFIX_.self::KWIXO_ORDER_TABLE_NAME."` (`id_order`,`kwixo_tagline_state`, `kwixo_transaction_id`, `date_tagline`) 
				VALUES (".(int) $ref_id.", ".(int) $tag.", '".pSQL((string) $transaction_id)."', '".pSQL((string) date('d-m-Y H:i:s'))."')";
			else
			//insert new kwixo order on database if urlcall script is called
				$sql = "INSERT INTO `"._DB_PREFIX_.self::KWIXO_ORDER_TABLE_NAME."` (`id_order`,`id_cart`, `kwixo_transaction_id`) 
				VALUES (".(int) $ref_id.", ".(int) $id_cart.", '".pSQL((string) $transaction_id)."')";

			$insert = Db::getInstance()->execute($sql);

			if (!$insert)
				KwixoLogger::insertLogKwixo(__METHOD__." : ".__LINE__, "Insertion commande kwixo $ref_id échouée $sql: ".Db::getInstance()->getMsgError());

			else
				KwixoLogger::insertLogKwixo(__METHOD__." : ".__LINE__, "Insertion commande kwixo ".$ref_id);
		}
		else
		{
			if ($mode == 'urlsys')
			//update kwixo order on database if urlsys script is called
				$sql = "UPDATE `"._DB_PREFIX_.self::KWIXO_ORDER_TABLE_NAME."` SET `kwixo_urlsys_state` = ".(int) $tag.", `id_cart` = ".(int) $id_cart.", `date_urlsys` = '".pSQL((string) date('d-m-Y H:i:s'))."' WHERE `id_order` = ".(int) $ref_id."";
			elseif ($mode == 'tagline')
			//update kwixo order on database if tagline script is called
				$sql = "UPDATE `"._DB_PREFIX_.self::KWIXO_ORDER_TABLE_NAME."` SET `kwixo_tagline_state` = ".(int) $tag.", `date_tagline` = '".pSQL((string) date('d-m-Y H:i:s'))."' WHERE `id_order` = ".(int) $ref_id."";

			else
				return false;
			$update = Db::getInstance()->execute($sql);

			if (!$update)
				KwixoLogger::insertLogKwixo(__METHOD__." : ".__LINE__, "Update commande kwixo $ref_id échouée $sql: ".Db::getInstance()->getMsgError());
			else
				KwixoLogger::insertLogKwixo(__METHOD__." : ".__LINE__, "Update commande kwixo réussie : ".$ref_id);
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
	 *
	 * Get the last Kwixo tag for an order and return the correct payment status
	 * 
	 * @param TaglineResponse $tag
	 * @param Order $order
	 * @param string $tid
	 * 
	 */
	function manageKwixoTagline($tag, $order, $tid)
	{
		$id_order = $order->id;

		if ($tag->hasError())
			KwixoLogger::insertLogKwixo(__METHOD__." : ".__LINE__, "Appel Tagline sur commande kwixo $id_order échoué : ".$tag->getError());
		else
		{
			//get kwixo tag and kwixo score
			$kwixo_tag = $tag->getTagValue();
			$kwixo_score = $tag->getScore();

			KwixoLogger::insertLogKwixo(__METHOD__.' : '.__LINE__, 'Appel Tagline : id_order = '.$id_order.' | tag = '.$kwixo_tag);

			//insert or update kwixo order for tagline action
			$this->manageKwixoOrder($id_order, $kwixo_tag, $tid, '', 'tagline');


			if (in_array($order->getCurrentState(), array((int) Configuration::get('KW_OS_WAITING'), (int) Configuration::get('KW_OS_CREDIT'), (int) Configuration::get('KW_OS_CONTROL'))))
			{
				switch ($kwixo_tag)
				{
					//order under kwixo control
					case 3:
						$psosstatus = (int) Configuration::get('KW_OS_CONTROL');
						break;

					case 4:
						//if current state is diffrent of kwixo under control
						if (!in_array($order->getCurrentState(), array((int) Configuration::get('KW_OS_CONTROL'))))
							$psosstatus = (int) Configuration::get('KW_OS_WAITING');
						else
							return false;
						break;
					//order under credit waiting
					case 6:
						$psosstatus = (int) Configuration::get('KW_OS_CREDIT');
						break;
					//order on valid status
					case 1:
					case 13:
					case 14:
					case 10:
						if ($kwixo_score == 'positif')
							$psosstatus = (int) Configuration::get('KW_OS_PAYMENT_GREEN');
						elseif ($kwixo_score == 'negatif')
							$psosstatus = (int) Configuration::get('KW_OS_PAYMENT_RED');
						else
							$psosstatus = (int) _PS_OS_PAYMENT_;
						break;

					//order on payment refused
					case 0:
					case 2:
					case 11:
					case 12:
					case 101:
						$psosstatus = (int) _PS_OS_CANCELED_;
						break;

					//order on delivery done
					case 100:
						$psosstatus = (int) _PS_OS_PAYMENT_;
						break;

					default:
						break;
				}

				//return the correct payment status
				if ($order->getCurrentState() != $psosstatus)
				//update order history
					$order->setCurrentState($psosstatus);
			}
		}
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
		return (Db::getInstance()->getValue('SELECT IFNULL(MAX(position),0)+1 FROM `'._DB_PREFIX_.'tab` WHERE `id_parent` = '.(int) ($id_parent)));
	}

	/**
	 * Create Kwixo payments status 
	 * 
	 * @param array $array
	 * @param string $color
	 * @param string $template 
	 */
	public function createKwixoPaymentStatus($array, $color, $template, $invoice, $send_email, $paid, $logable)
	{
		foreach ($array as $key => $value)
		{
			$kw_ow_status = Configuration::get($key);
			if ($kw_ow_status === false)
			{
				$orderState = new OrderState();
				$orderState->id_order_state = (int) $key;
			}
			else
				$orderState = new OrderState((int) $kw_ow_status);

			$langs = Language::getLanguages();

			foreach ($langs AS $lang)
				$orderState->name[$lang['id_lang']] = utf8_encode(html_entity_decode($value));

			$orderState->invoice = $invoice;
			$orderState->send_email = $send_email;

			if ($template != '')
				$orderState->template = $template;

			if ($paid != '')
				$orderState->paid = $paid;
			$orderState->logable = $logable;
			$orderState->color = $color;
			$orderState->save();

			Configuration::updateValue($key, (int) $orderState->id);

			copy(dirname(__FILE__).'/img/'.$key.'.gif', dirname(__FILE__).'/../../img/os/'.(int) $orderState->id.'.gif');
		}
	}

	/**
	 * Check if xml parameters given on payment validation are right
	 * 
	 * @return xml_params given on payment validation
	 */
	public function checkUrlCallXMLParams()
	{

		$errors = array();
		$xml_params = array();

		if (!Tools::getValue('custom'))
		{
			$errors[] = $payment->displayName.' '.$payment->l('key "custom" not specified, cannot rely to cart')."\n";
			KwixoLogger::insertLogKwixo(__METHOD__." : ".__LINE__, 'clé custom non spécifiée dans xmlparams');
			return false;
		}
		else
			$xml_params['id_cart'] = (int) Tools::getValue('custom');

		if (!Tools::getValue('id_module'))
		{
			$errors[] = $payment->displayName.' '.$payment->l('key "module" not specified, cannot rely to payment module')."\n";
			KwixoLogger::insertLogKwixo(__METHOD__." : ".__LINE__, 'clé module non spécifiée dans xmlparams');
			return false;
		}
		else
			$xml_params['id_module'] = (int) Tools::getValue('id_module');

		if (!isset($_POST['amount']))
		{
			$errors[] = $payment->displayName.' '.$payment->l('"amount" not specified, cannot control the amount paid')."\n";
			KwixoLogger::insertLogKwixo(__METHOD__." : ".__LINE__, 'clé montant non spécifiée dans xmlparams');
			return false;
		}
		else
			$xml_params['amount'] = (float) Tools::getValue('amount');


		//payed cart instanciation
		$cart = new Cart((int) $xml_params['id_cart']);
		$order_created = Order::getOrderByCartId($xml_params['id_cart']);
		//if not founded cart
		if (!$cart->id)
		{
			$errors[] = $payment->l('cart not found')."\n";
			KwixoLogger::insertLogKwixo(__METHOD__." : ".__LINE__, 'Panier non trouvé');
			return false;
		}

		if (empty($errors))
		{
			$xml_params['errors'] = 0;
			$xml_params['order_created'] = $order_created;
			KwixoLogger::insertLogKwixo(__METHOD__." : ".__LINE__, 'Récupération xml_params réussie');
			return $xml_params;
		} else
		{
			$xml_params['errors'] = count($errors);
			return $xml_params;
		}
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
			KwixoLogger::insertLogKwixo(__METHOD__." : ".__LINE__, "Module So Colissimo non installé ou non activé");
			return false;
		}
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
