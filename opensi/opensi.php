<?php
/*
 * OpenSi Connect for Prestashop
 *
 * NOTICE OF LICENSE
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation; either version 2 of the License, or (at your
 * option) any later version.
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
 * for more details.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author Speedinfo SARL
 * @copyright 2003-2012 Speedinfo SARL
 * @contact contact@speedinfo.fr
 * @url http://www.speedinfo.fr
 *
 */

/* Security */
if (!defined('_PS_VERSION_'))
	exit;


/* OpenSi class module */
class opensi extends Module
{

	private $_html = '';
	private $_moduleUri = 'modules/opensi/';
	private $cachetimeLbl = 'OSI_CACHETIME_';
	private $lastrequestLbl = 'OSI_LASTREQUEST_';	

	private $wsGetList = '';
	private $wsPostList = '';
	private $WsReqListDefaultDelay = '';
	private $listFeatures = '';
	private $listAttributesGp = '';
	private $listOrderStates = '';

	public $urlws;
	public $portws;
	public $loginws;
	public $passwdws;
	public $servicecode;
	public $websitecode;
	public $depotcode;

	/*
	 * Constructor
	 */
	public function __construct() {
		$this->name = 'opensi';
		$this->tab = "billing_invoicing";
		$this->version = '1.1.0';
		$this->author = 'PrestaShop';
		$this->displayName = $this->l('OpenSi connector');
		$this->description = $this->l('Self-made Management Accounting Software for PrestaShop');
		$this->confirmUninstall = $this->l('Are you sure you want to delete OpenSi connector ?');
		parent::__construct();

		/* Check minimum version required of Prestashop */
		if(substr(_PS_VERSION_, 0, 5) < '1.3.2') {
			$this->warning = $this->l('Error : the module is not compatible with your version of Prestashop (minimum required is 1.3.2).');
		} else {
			/* If no configuration => display alert message */
			$urlws = Configuration::get('OSI_WS_URL');
			$portws = Configuration::get('OSI_WS_PORT');
			$loginws = Configuration::get('OSI_WS_LOGIN');
			$passwdws = Configuration::get('OSI_WS_PASSWD');
			$servicecode = Configuration::get('OSI_SERVICE_CODE');
			$websitecode = Configuration::get('OSI_WEBSITE_CODE');
			$depotcode = Configuration::get('OSI_DEPOSIT_CODE');

			if($urlws == '' || $portws == '' || $loginws == '' || $passwdws == '' || $servicecode == '' || $websitecode == '' || $depotcode == '')
				$this->warning = $this->l('The webservice access OpenSi and the cron must be configured.');
		}


		/*
		 * If the page is saved
		 * Frequencies are updated
		 */
		$actualConfigurationMode = Configuration::get('OSI_CONFIGURATION_MODE');

		/*
		 * Update min time info
		 */
		$CONF_WsReqListDefaultDelay = array("WSO-G002" => "1",
											"WSO-G003" => "1",
											"WSO-G008" => "1",
											"WSO-G009" => "1",
											"WSO-P005" => "1",
											"WSO-P006" => "1",
											"WSO-P010" => "1",
											"WSO-P011" => "1",
											"WSO-P015" => "1",
											"WSO-P025" => "1"
											);					// in minutes

		/*
		 * Page saved
		 */
		if(Tools::isSubmit('submitSettings')) {

			/* Check if the conf mode has changed */
			if(Tools::getValue('osi_configuration_mode') != $actualConfigurationMode) {
				/*
				 * Reset last dates request webservices to 0
				 * Not apply to P011 => create_commande_web & P015 => create_transaction_web
				 */
				Configuration::updateValue('OSI_LASTREQUEST_WSO-G002', 0);
				Configuration::updateValue('OSI_LASTREQUEST_WSO-G003', 0);
				Configuration::updateValue('OSI_LASTREQUEST_WSO-G008', 0);
				Configuration::updateValue('OSI_LASTREQUEST_WSO-G009', 0);
				Configuration::updateValue('OSI_LASTREQUEST_WSO-P005', 0);
				Configuration::updateValue('OSI_LASTREQUEST_WSO-P006', 0);
				Configuration::updateValue('OSI_LASTREQUEST_WSO-P010', 0);
				Configuration::updateValue('OSI_LASTREQUEST_WSO-P011', time());
				Configuration::updateValue('OSI_LASTREQUEST_WSO-P015', time());
				Configuration::updateValue('OSI_LASTREQUEST_WSO-P025', 0);
			}

		} else {

			$configuration_mode = Configuration::get('OSI_CONFIGURATION_MODE');

		}

		/* Loading config for this module */
		/*if(__PS_BASE_URI__ == "/") {
			include_once($_SERVER['DOCUMENT_ROOT']."/modules/".$this->name."/config.inc.php");
		} else {
			include_once($_SERVER['DOCUMENT_ROOT'].__PS_BASE_URI__."/modules/".$this->name."/config.inc.php");
		}*/
		include_once(_PS_MODULE_DIR_.$this->name."/config.inc.php");

		/* Definition */
		if(isset($CONF_CachetimeLbl)) {
			$this->cachetimeLbl = $CONF_CachetimeLbl;
			$this->lastrequestLbl = $CONF_LastrequestLbl;
			$this->wsGetList = $CONF_WsGetReqList;
			$this->wsPostList = $CONF_WsPostReqList;
			$this->WsReqListDefaultDelay = $CONF_WsReqListDefaultDelay;
		}

	}


	/* Install Method */
	public function install()
	{
		/* Install databases */
		include(dirname(__FILE__).'/sql/install.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->Execute($s))
				return false;

		/* Install module */
		if(!parent::install() ||
			!$this->registerHook('leftColumn') ||
			!$this->registerHook('AdminOrder') ||
			!Configuration::updateValue('OSI_ACTIVE_WSO-G002',1) ||
			!Configuration::updateValue('OSI_ACTIVE_WSO-G003',1) ||
			!Configuration::updateValue('OSI_ACTIVE_WSO-P005',1) ||
			!Configuration::updateValue('OSI_ACTIVE_WSO-P006',1) ||
			!Configuration::updateValue('OSI_ACTIVE_WSO-G008',1) ||
			!Configuration::updateValue('OSI_ACTIVE_WSO-G009',1) ||
			!Configuration::updateValue('OSI_ACTIVE_WSO-P010',1) ||
			!Configuration::updateValue('OSI_ACTIVE_WSO-P011',1) ||
			!Configuration::updateValue('OSI_ACTIVE_WSO-P015',1) ||
			!Configuration::updateValue('OSI_ACTIVE_WSO-P025',1) ||
			!Configuration::updateValue($this->cachetimeLbl.'WSO-G002','10') ||
			!Configuration::updateValue($this->cachetimeLbl.'WSO-G003','10') ||
			!Configuration::updateValue($this->cachetimeLbl.'WSO-P005','10') ||
			!Configuration::updateValue($this->cachetimeLbl.'WSO-P006','10') ||
			!Configuration::updateValue($this->cachetimeLbl.'WSO-G008','10') ||
			!Configuration::updateValue($this->cachetimeLbl.'WSO-G009','10') ||
			!Configuration::updateValue($this->cachetimeLbl.'WSO-P010','10') ||
			!Configuration::updateValue($this->cachetimeLbl.'WSO-P011','10') ||
			!Configuration::updateValue($this->cachetimeLbl.'WSO-P015','10') ||
			!Configuration::updateValue($this->cachetimeLbl.'WSO-P025','10') ||
			!Configuration::updateValue('OSI_CONFIGURATION_MODE',1) ||
			!Configuration::updateValue('OSI_DEFAULT_PRICE',1) ||
			!Configuration::updateValue('OSI_DEPOSIT_CODE','') ||
			!Configuration::updateValue('OSI_HOOK_ADMIN_ORDER',1) ||
			!Configuration::updateValue('OSI_INVOICE',0) ||
			!Configuration::updateValue($this->lastrequestLbl.'WSO-G002',0) ||
			!Configuration::updateValue($this->lastrequestLbl.'WSO-G003',0) ||
			!Configuration::updateValue($this->lastrequestLbl.'WSO-P005',0) ||
			!Configuration::updateValue($this->lastrequestLbl.'WSO-P006',0) ||
			!Configuration::updateValue($this->lastrequestLbl.'WSO-G008',0) ||
			!Configuration::updateValue($this->lastrequestLbl.'WSO-G009',0) ||
			!Configuration::updateValue($this->lastrequestLbl.'WSO-P010',0) ||
			!Configuration::updateValue($this->lastrequestLbl.'WSO-P011',time()) ||
			!Configuration::updateValue($this->lastrequestLbl.'WSO-P015',time()) ||
			!Configuration::updateValue($this->lastrequestLbl.'WSO-P025',0) ||
			!Configuration::updateValue('OSI_LINK_ATTRIBUTE1','') ||
			!Configuration::updateValue('OSI_LINK_ATTRIBUTE2','') ||
			!Configuration::updateValue('OSI_LINK_ATTRIBUTE3','') ||
			!Configuration::updateValue('OSI_LINK_ATTRIBUTE4','') ||
			!Configuration::updateValue('OSI_LINK_ATTRIBUTE5','') ||
			!Configuration::updateValue('OSI_LINK_ATTRIBUTE6','') ||
			!Configuration::updateValue('OSI_LINK_ATTRIBUTE1_ISFEATURE',1) ||
			!Configuration::updateValue('OSI_LINK_ATTRIBUTE2_ISFEATURE',1) ||
			!Configuration::updateValue('OSI_LINK_ATTRIBUTE3_ISFEATURE',1) ||
			!Configuration::updateValue('OSI_LINK_ATTRIBUTE4_ISFEATURE',1) ||
			!Configuration::updateValue('OSI_LINK_ATTRIBUTE5_ISFEATURE',1) ||
			!Configuration::updateValue('OSI_LINK_ATTRIBUTE6_ISFEATURE',1) ||
			!Configuration::updateValue('OSI_LINK_FEATURE_VOLUME','') ||
			!Configuration::updateValue('OSI_STATE_ID_CANCELED',6) ||
			!Configuration::updateValue('OSI_STATE_ID_ON_DELIVERY',4) |
			!Configuration::updateValue('OSI_STATE_ID_ON_PREPARATION',3) ||
			!Configuration::updateValue('OSI_SERVICE_CODE','') ||
			!Configuration::updateValue('OSI_VERSION', $this->version) ||
			!Configuration::updateValue('OSI_WEBSITE_CODE','') ||
			!Configuration::updateValue('OSI_WS_LOGIN','') ||
			!Configuration::updateValue('OSI_WS_PASSWD','') ||
			!Configuration::updateValue('OSI_WS_PORT',443) ||
			!Configuration::updateValue('OSI_WS_URL','https://webservices.opensi.eu/cows/Gateway'))
			return false;

		if(substr(_PS_VERSION_, 0, 3) > 1.3)
			$this->registerHook('OrderDetailDisplayed');
		return true;
	}


	/* Uninstall Method */
	public function uninstall()
	{
		/* Uninstall databases */
		include(dirname(__FILE__).'/sql/uninstall.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->Execute($s))
				return false;

		/* Uninstall module */
		if(!parent::uninstall() ||
			!$this->unregisterHook('leftColumn') ||
			!$this->unregisterHook('AdminOrder') ||
			!Configuration::deleteByName('OSI_ACTIVE_WSO-G002') ||
			!Configuration::deleteByName('OSI_ACTIVE_WSO-G003') ||
			!Configuration::deleteByName('OSI_ACTIVE_WSO-P005') ||
			!Configuration::deleteByName('OSI_ACTIVE_WSO-P006') ||
			!Configuration::deleteByName('OSI_ACTIVE_WSO-G008') ||
			!Configuration::deleteByName('OSI_ACTIVE_WSO-G009') ||
			!Configuration::deleteByName('OSI_ACTIVE_WSO-P010') ||
			!Configuration::deleteByName('OSI_ACTIVE_WSO-P011') ||
			!Configuration::deleteByName('OSI_ACTIVE_WSO-P015') ||
			!Configuration::deleteByName('OSI_ACTIVE_WSO-P025') ||
			!Configuration::deleteByName($this->cachetimeLbl.'WSO-G002') ||
			!Configuration::deleteByName($this->cachetimeLbl.'WSO-G003') ||
			!Configuration::deleteByName($this->cachetimeLbl.'WSO-P005') ||
			!Configuration::deleteByName($this->cachetimeLbl.'WSO-P006') ||
			!Configuration::deleteByName($this->cachetimeLbl.'WSO-G008') ||
			!Configuration::deleteByName($this->cachetimeLbl.'WSO-G009') ||
			!Configuration::deleteByName($this->cachetimeLbl.'WSO-P010') ||
			!Configuration::deleteByName($this->cachetimeLbl.'WSO-P011') ||
			!Configuration::deleteByName($this->cachetimeLbl.'WSO-P015') ||
			!Configuration::deleteByName($this->cachetimeLbl.'WSO-P025') ||
			!Configuration::deleteByName('OSI_CONFIGURATION_MODE') ||
			!Configuration::deleteByName('OSI_DEFAULT_PRICE') ||
			!Configuration::deleteByName('OSI_DEPOSIT_CODE') ||
			!Configuration::deleteByName('OSI_HOOK_ADMIN_ORDER') ||
			!Configuration::deleteByName('OSI_INVOICE') ||
			!Configuration::deleteByName($this->lastrequestLbl.'WSO-G002') ||
			!Configuration::deleteByName($this->lastrequestLbl.'WSO-G003') ||
			!Configuration::deleteByName($this->lastrequestLbl.'WSO-P005') ||
			!Configuration::deleteByName($this->lastrequestLbl.'WSO-P006') ||
			!Configuration::deleteByName($this->lastrequestLbl.'WSO-G008') ||
			!Configuration::deleteByName($this->lastrequestLbl.'WSO-G009') ||
			!Configuration::deleteByName($this->lastrequestLbl.'WSO-P010') ||
			!Configuration::deleteByName($this->lastrequestLbl.'WSO-P011') ||
			!Configuration::deleteByName($this->lastrequestLbl.'WSO-P015') ||
			!Configuration::deleteByName($this->lastrequestLbl.'WSO-P025') ||
			!Configuration::deleteByName('OSI_LINK_ATTRIBUTE1') ||
			!Configuration::deleteByName('OSI_LINK_ATTRIBUTE2') ||
			!Configuration::deleteByName('OSI_LINK_ATTRIBUTE3') ||
			!Configuration::deleteByName('OSI_LINK_ATTRIBUTE4') ||
			!Configuration::deleteByName('OSI_LINK_ATTRIBUTE5') ||
			!Configuration::deleteByName('OSI_LINK_ATTRIBUTE6') ||
			!Configuration::deleteByName('OSI_LINK_ATTRIBUTE1_ISFEATURE') ||
			!Configuration::deleteByName('OSI_LINK_ATTRIBUTE2_ISFEATURE') ||
			!Configuration::deleteByName('OSI_LINK_ATTRIBUTE3_ISFEATURE') ||
			!Configuration::deleteByName('OSI_LINK_ATTRIBUTE4_ISFEATURE') ||
			!Configuration::deleteByName('OSI_LINK_ATTRIBUTE5_ISFEATURE') ||
			!Configuration::deleteByName('OSI_LINK_ATTRIBUTE6_ISFEATURE') ||
			!Configuration::deleteByName('OSI_LINK_FEATURE_VOLUME') ||
			!Configuration::deleteByName('OSI_STATE_ID_CANCELED') ||
			!Configuration::deleteByName('OSI_STATE_ID_ON_DELIVERY') ||
			!Configuration::deleteByName('OSI_STATE_ID_ON_PREPARATION') ||
			!Configuration::deleteByName('OSI_SERVICE_CODE') ||
			!Configuration::deleteByName('OSI_VERSION', $this->version) ||
			!Configuration::deleteByName('OSI_WEBSITE_CODE') ||
			!Configuration::deleteByName('OSI_WS_LOGIN') ||
			!Configuration::deleteByName('OSI_WS_PASSWD') ||
			!Configuration::deleteByName('OSI_WS_PORT') ||
			!Configuration::deleteByName('OSI_WS_URL'))
			return false;

		if(substr(_PS_VERSION_, 0, 3) > 1.3)
			$this->unregisterHook('OrderDetailDisplayed');
		return true;
	}


	/* Get content method */
	public function getContent()
	{
		/*
		 * Check if upgrade is available
		 * Define global $upgrade to skip loop display on the installation (class loaded 2x on install)
		 */
		if (@ini_get('allow_url_fopen') AND $update = GlobalConfig::checkOpenSiVersion($this->version))
			$this->_html .= '<div class="warn"><h3>'.$this->l('New OpenSi version available').'<br /><a style="text-decoration: underline;" href="'.$update['link'].'">'.$this->l('Download').'&nbsp;'.$update['name'].'</a> !</h3></div>';

		/* html */
		$this->_html .= '
			<h2>'.$this->l('Config OpenSI connector module').'</h2>
			<p class="version">'.$this->l('Version of the module').' '.$this->version.'</p>
			<link rel="stylesheet" type="text/css" href="'._PS_BASE_URL_.__PS_BASE_URI__.$this->_moduleUri.'/css/opensi.css" />
			<script type="text/javascript" src="'._PS_BASE_URL_.__PS_BASE_URI__.$this->_moduleUri.'/js/opensi.js"></script>
		';

		/* Save configuration */
		if(Tools::isSubmit('submitSettings')) {
			/* Check for webservice duration big enough */
			if(!$this->utilCheckWsTimeValidity()){
				$this->_html .= $this->displayError($this->l('One of duration is not big enough'));
			} else {
				/* Get and parse links attributes */
				for($i=1; $i<=6; $i++){
					if(Tools::getValue('osi_link_attribute'.$i.'_isfeature') == 1) {
						$_POST['osi_link_attribute'.$i] = Tools::getValue('osi_link_attribute'.$i.'_features');
					} else {
						$_POST['osi_link_attribute'.$i] = Tools::getValue('osi_link_attribute'.$i.'_attributesgp');
					}
				}

				/* Update configuration */
				Configuration::updateValue('OSI_ACTIVE_WSO-G002', Tools::getValue('active_wso_g002'));
				Configuration::updateValue('OSI_ACTIVE_WSO-G003', Tools::getValue('active_wso_g003'));
				Configuration::updateValue('OSI_ACTIVE_WSO-G008', Tools::getValue('active_wso_g008'));
				Configuration::updateValue('OSI_ACTIVE_WSO-G009', Tools::getValue('active_wso_g009'));
				Configuration::updateValue('OSI_ACTIVE_WSO-P005', Tools::getValue('active_wso_p005'));
				Configuration::updateValue('OSI_ACTIVE_WSO-P006', Tools::getValue('active_wso_p006'));
				Configuration::updateValue('OSI_ACTIVE_WSO-P010', Tools::getValue('active_wso_p010'));
				Configuration::updateValue('OSI_ACTIVE_WSO-P011', Tools::getValue('active_wso_p011'));
				Configuration::updateValue('OSI_ACTIVE_WSO-P015', Tools::getValue('active_wso_p015'));
				Configuration::updateValue('OSI_ACTIVE_WSO-P025', Tools::getValue('active_wso_p025'));
				Configuration::updateValue('OSI_CONFIGURATION_MODE', Tools::getValue('osi_configuration_mode'));
				Configuration::updateValue('OSI_DEFAULT_PRICE', Tools::getValue('osi_default_price'));
				Configuration::updateValue('OSI_DEPOSIT_CODE', Tools::getValue('osi_deposit_code'));
				Configuration::updateValue('OSI_HOOK_ADMIN_ORDER', Tools::getValue('osi_hook_admin_order'));
				Configuration::updateValue('OSI_INVOICE', Tools::getValue('osi_invoice'));
				Configuration::updateValue('OSI_LINK_ATTRIBUTE1', Tools::getValue('osi_link_attribute1'));
				Configuration::updateValue('OSI_LINK_ATTRIBUTE2', Tools::getValue('osi_link_attribute2'));
				Configuration::updateValue('OSI_LINK_ATTRIBUTE3', Tools::getValue('osi_link_attribute3'));
				Configuration::updateValue('OSI_LINK_ATTRIBUTE4', Tools::getValue('osi_link_attribute4'));
				Configuration::updateValue('OSI_LINK_ATTRIBUTE5', Tools::getValue('osi_link_attribute5'));
				Configuration::updateValue('OSI_LINK_ATTRIBUTE6', Tools::getValue('osi_link_attribute6'));
				Configuration::updateValue('OSI_LINK_ATTRIBUTE1_ISFEATURE', Tools::getValue('osi_link_attribute1_isfeature'));
				Configuration::updateValue('OSI_LINK_ATTRIBUTE2_ISFEATURE', Tools::getValue('osi_link_attribute2_isfeature'));
				Configuration::updateValue('OSI_LINK_ATTRIBUTE3_ISFEATURE', Tools::getValue('osi_link_attribute3_isfeature'));
				Configuration::updateValue('OSI_LINK_ATTRIBUTE4_ISFEATURE', Tools::getValue('osi_link_attribute4_isfeature'));
				Configuration::updateValue('OSI_LINK_ATTRIBUTE5_ISFEATURE', Tools::getValue('osi_link_attribute5_isfeature'));
				Configuration::updateValue('OSI_LINK_ATTRIBUTE6_ISFEATURE', Tools::getValue('osi_link_attribute6_isfeature'));
				Configuration::updateValue('OSI_LINK_FEATURE_VOLUME', Tools::getValue('osi_link_feature_volume'));
				Configuration::updateValue('OSI_STATE_ID_CANCELED', Tools::getValue('osi_state_id_canceled'));
				Configuration::updateValue('OSI_STATE_ID_ON_DELIVERY', Tools::getValue('osi_state_id_on_delivery'));
				Configuration::updateValue('OSI_STATE_ID_ON_PREPARATION', Tools::getValue('osi_state_id_on_preparation'));
				Configuration::updateValue('OSI_SERVICE_CODE', Tools::getValue('osi_service_code'));
				Configuration::updateValue('OSI_WEBSITE_CODE', Tools::getValue('osi_website_code'));
				Configuration::updateValue('OSI_WS_LOGIN', Tools::getValue('osi_ws_login'));
				Configuration::updateValue('OSI_WS_PASSWD', Tools::getValue('osi_ws_passwd'));
				Configuration::updateValue('OSI_WS_PORT', Tools::getValue('osi_ws_port'));
				Configuration::updateValue('OSI_WS_URL', Tools::getValue('osi_ws_url'));

				foreach($this->wsGetList as $wsName){
					Configuration::updateValue($this->cachetimeLbl.$wsName, Tools::getValue(strtolower($this->cachetimeLbl.$wsName))); 
				}
				foreach($this->wsPostList as $wsName){
					Configuration::updateValue($this->cachetimeLbl.$wsName, Tools::getValue(strtolower($this->cachetimeLbl.$wsName))); 
				}
				$this->_html .= $this->displayConfirmation($this->l('Settings updated.'));
			}
		}

		/* If not compatible with at least Prestashop  1.3.2 */
		if(substr(_PS_VERSION_, 0, 5) < '1.3.2') {
			$this->_html .= '
				<fieldset>
					<legend><img src="'.$this->_path.'img/alert.png" alt="" />'.$this->l('Compatibility error').'</legend>'
					.$this->l('Error : the module is not compatible with your version of Prestashop.').'<br /><br />'
					.$this->l('Minimum version is 1.3.2').'<br />'
					.$this->l('Actual version :').' '._PS_VERSION_.'
				</fieldset>
			';
		} else {
			$this->_displayForm();
		}

		return $this->_html;
	}


	/* Form configuration method */
	private function _displayForm()
	{
		$this->_html .= '
			<form action="'.htmlentities($_SERVER['REQUEST_URI']).'" method="post">
				<ul id="menuTab">
					<li id="menuTab1" class="menuTabButton selected"><span>'.$this->l('Introduction').'</span></li>
					<li id="menuTab2" class="menuTabButton"><span>'.$this->l('General informations').'</span></li>
					<li id="menuTab3" class="menuTabButton"><span>'.$this->l('Synchronizations').'</span></li>
					<li id="menuTab4" class="menuTabButton"><span>'.$this->l('Links').'</span></li>
					<li id="menuTab5" class="menuTabButton"><span>'.$this->l('Journal of synchronizations').'</span></li>
					<li id="menuTab6" class="menuTabButton"><span>'.$this->l('Documentation').'</span></li>
					<li id="menuTab7" class="menuTabButton"><span>'.$this->l('Preferences').'</span></li>
				</ul>
				<div id="tabList">
					<div id="menuTab1Sheet" class="tabItem selected">'.$this->_displayPresentation().'</div>
					<div id="menuTab2Sheet" class="tabItem">'.$this->_displayInformations().'</div>
					<div id="menuTab3Sheet" class="tabItem">'.$this->_displaySync().'</div>
					<div id="menuTab4Sheet" class="tabItem">'.$this->_displayMapping().'</div>
					<div id="menuTab5Sheet" class="tabItem">'.$this->_displayJournal().'</div>
					<div id="menuTab6Sheet" class="tabItem">'.$this->_displayDoc().'</div>
					<div id="menuTab7Sheet" class="tabItem">'.$this->_displayPrefs().'</div>
				</div>
				<div class="clear"></div>
				<div class="button_save">
					<input class="button" type="submit" name="submitSettings" value="'.$this->l('   Save   ').'" />
				</div>
			</form>
		';

		return $this->_html; 
	}


	/* Presentation tab */
	private function _displayPresentation()
	{
		$html = '
			<fieldset>
				<legend><img src="'.$this->_path.'img/opensi.png" alt="" />'.$this->l('Introduction').'</legend>
				<strong>OpenSi E-Commerce : la solution de gestion commerciale et de comptabilit&eacute; pour votre site Prestashop</strong><br /><br />
				&Eacute;dit&eacute; par la soci&eacute;t&eacute; Speedinfo, le logiciel de gestion OpenSi E-Commerce apporte un nouveau degr&eacute; de performance dans la gestion des commandes Prestashop.<br />
				Connectable en standard avec la solution Prestashop, OpenSi E-Commerce permet de contr&ocirc;ler toute l&rsquo;activit&eacute; du site e-commerce.<br /><br />
				Gr&acirc;ce &agrave; une technologie innovante, les ventes e-commerce sont int&eacute;gr&eacute;es automatiquement dans le logiciel sans intervention de votre part. Vous ma&icirc;trisez ainsi toute votre activit&eacute; : commande client, comptabilit&eacute;, suivi des stocks, gestion des achats, exp&eacute;ditions, r&eacute;approvisionnements fournisseur... Sans aucune ressaisie !<br /><br /><br /><br />

				<center><a href="http://www.opensi.fr/ecommerce/boutiques-prestashop.html" target="_blank"><img src="'.$this->_path.'img/optez_opensi.png" alt="" /></a></center><br /><br /><br />

				<strong>Des fonctionnalit&eacute;s in&eacute;dites dans la gestion d&rsquo;une boutique Prestashop</strong><br /><br />
				Avec OpenSi E-Commerce, il est possible de traiter et de suivre tous les flux d&rsquo;un site Prestashop, d&rsquo;une commande client jusqu&rsquo;&agrave; la comptabilit&eacute; en passant par la gestion des achats. Disponible en ligne, le logiciel OpenSi E-Commerce est riche en fonctions innovantes pour g&eacute;rer votre boutique Prestashop :
				<ul>
					<li>Validation et pr&eacute;paration des commandes</li>
					<li>Gestion des exp&eacute;ditions</li>
					<li>Gestion du drop shipping (livraison directe fournisseur-client)</li>
					<li>Gestion du r&eacute;approvisionnement (manuel ou automatis&eacute;)</li>
					<li>Gestion de la comptabilit&eacute;</li>
					<li>Int&eacute;gration des flux des transporteurs (n&deg; tracking colis)</li>
					<li>Gestion multi boutiques</li>
					<li>Suivi des stocks (multi d&eacute;p&ocirc;ts, inventaire, transfert de stock...)</li>
					<li>Utilisation de douchette code barre</li>
				</ul><br />


				<center><iframe width="420" height="315" src="http://www.youtube.com/embed/QScWTsM714k" frameborder="0" allowfullscreen></iframe></center><br /><br /><br /><br />


				<strong>Des b&eacute;n&eacute;fices utilisateurs mesurables au quotidien</strong><br /><br />
				Utiliser le logiciel de gestion OpenSi E-Commerce au quotidien est l&rsquo;assurance d&rsquo;une ma&icirc;trise parfaite de l&rsquo;ensemble de votre gestion commerciale &amp; comptabilit&eacute; : gain de temps, optimisation financi&egrave;re...<br /><br /><br />
				<table width="100%" class="pres_ico">
					<tr>
						<td width="33%">
							<img src="'.$this->_path.'img/productivity_icon.jpg" alt="" />
						</td>
						<td width="33%">
							<img src="'.$this->_path.'img/subscription_icon.jpg" alt="" />
						</td>
						<td width="33%">
							<img src="'.$this->_path.'img/automated_icon.jpg" alt="" />
						</td>
					</tr>
					<tr>
						<td width="33%">
							<strong>Productivit&eacute;</strong><br />
							Gain de temps dans la pr&eacute;paration des commandes,<br />
							Automatisation du processus de r&eacute;approvisionnement,<br />
							Transfert en comptabilit&eacute;...
						</td>
						<td width="33%">
							<strong>Visibilit&eacute; financi&egrave;re</strong><br />
							Consultation des marges,<br />
							Suivi de la valorisation des stocks,<br />
							Contr&ocirc;le de la tr&eacute;sorerie...
						</td>
						<td width="33%">
							<strong>Qualit&eacute;</strong><br />
							Fiabilit&eacute; des informations stocks,<br />
							R&eacute;duction des d&eacute;lais de livraison,<br />
							Fiabilit&eacute; des produits exp&eacute;di&eacute;s...
						</td>
					</tr>
					<tr>
						<td colspan="3" class="mini grey">
							<i>* Tarif abonnement OpenSi E-Commerce pour traiter 600 commandes/mois.</i>
						</td>
					</tr>
				</table><br /><br />


				<strong>Un fonctionnement simple et performant</strong><br /><br />
				OpenSi E-Commerce b&eacute;n&eacute;ficie de toute l&rsquo;expertise des &eacute;quipes Speedinfo en mati&egrave;re de e-commerce et de gestion de flux.
				<ul>
					<li>Synchronisation du catalogue boutique et de la gestion commerciale (aucune ressaisie)</li>
					<li>R&eacute;cup&eacute;ration automatique des commandes pass&eacute;es sur la boutique</li>
					<li>Renvoi des statuts de commandes, de la facturation et des stocks vers la boutique</li>
					<li>G&eacute;n&eacute;ration automatique de documents de vente (bon de livraison, devis...)</li>
				</ul>
				&rarr; <a href="http://www.youtube.com/watch?v=QScWTsM714k" target="_blank">D&eacute;couvrez OpenSi E-Commerce en images</a><br /><br /><br /><br />


				<strong>Speedinfo, un &eacute;diteur de logiciels au croisement de la gestion et de l&rsquo;e-commerce</strong><br /><br />
				Partenaire privil&eacute;gi&eacute; des e-commer&ccedil;ants depuis bient&ocirc;t 10 ans, la vision de Speedinfo est de proposer des logiciels et des services innovants pour rendre la gestion d&rsquo;entreprise accessible &agrave; tous :
				<ul>
					<li>OpenSi E-Commerce</li>
					<li>OpenSi Itinerant</li>
					<li>OpenSi Gestion des Contacts</li>
					<li>...</li>
				</ul><br /><br />


				<strong>Ils nous font confiance...</strong><br /><br />
				La volont&eacute; de Speedinfo est d&rsquo;offrir des solutions de gestion d&rsquo;entreprise aux PME/PMI qui soient performantes, fiables et p&eacute;rennes.<br />
				De nombreuses soci&eacute;t&eacute;s ont d&eacute;j&agrave; opt&eacute; pour une nouvelle fa&ccedil;on de g&eacute;rer leur boutique Prestashop avec OpenSi E-Commerce :<br /><br />
				<img src="'.$this->_path.'img/customers.png" alt="" /><br /><br />
				&rarr; <a href="http://www.opensi.fr" target="_blank">Visitez le site officiel de la solution de gestion OpenSi</a>
			</fieldset>
		';

		return $html;
	}


	/* Informations tab */
	private function _displayInformations()
	{
		$html = '';

		/* Get configuration */
		$urlws = Configuration::get('OSI_WS_URL');
		$portws = Configuration::get('OSI_WS_PORT');
		$loginws = Configuration::get('OSI_WS_LOGIN');
		$passwdws = Configuration::get('OSI_WS_PASSWD');
		$servicecode = Configuration::get('OSI_SERVICE_CODE');
		$websitecode = Configuration::get('OSI_WEBSITE_CODE');
		$depotcode = Configuration::get('OSI_DEPOSIT_CODE');

		if($urlws == '' || $portws == '' || $loginws == '' || $passwdws == '' || $servicecode == '' || $websitecode == '' || $depotcode == '') {
			$html .= '<div class="error_box">'.$this->l('The webservice access OpenSi and the cron must be configured.');
			$html .= '<br />';
			$html .= $this->l('Absolute path to specify in your crontab').' : sh '.dirname(__FILE__).'/cron.sh</div>';
		}

		$html .= '
			<fieldset>
				<legend><img src="'.$this->_path.'img/access.png" alt="" />'.$this->l('Access configuration').'</legend>
				<label>'.$this->l('Login').' <sup>*</sup></label>
				<div class="margin-form">
					<input type="text" name="osi_ws_login" class="biginp" value="'.Tools::htmlentitiesUTF8(GlobalConfig::getWsLogin()).'" />
				</div>	
				<label>'.$this->l('Password').' <sup>*</sup></label>
				<div class="margin-form">
					<input type="password" name="osi_ws_passwd" class="biginp" value="'.Tools::htmlentitiesUTF8(GlobalConfig::getWsPasswd()).'" />
				</div>
				<label>'.$this->l('Service code').' <sup>*</sup></label>
				<div class="margin-form">
					<input type="text" name="osi_service_code" class="biginp" value="'.Tools::htmlentitiesUTF8(GlobalConfig::getServiceCode()).'" />
				</div>
				<label>'.$this->l('website code').' <sup>*</sup></label>
				<div class="margin-form">
					<input type="text" name="osi_website_code" class="biginp" value="'.Tools::htmlentitiesUTF8(GlobalConfig::getWebSiteCode()).'" />
				</div>
				<label>'.$this->l('deposit code').' <sup>*</sup></label>
				<div class="margin-form">
					<input type="text" name="osi_deposit_code" class="biginp" value="'.Tools::htmlentitiesUTF8(GlobalConfig::getDepositCode()).'" />
				</div>																		
			</fieldset>

			<br /><br />

			<fieldset>
				<legend><img src="'.$this->_path.'img/cron.png" alt="" />'.$this->l('Cron configuration').'</legend>
				<strong>D&eacute;finition</strong> : Le cron est un s&eacute;quenceur de t&acirc;ches syst&egrave;me UNIX.<br />
				<u>Son activation est n&eacute;cessaire</u> pour automatiser les flux (commandes, clients, stocks, prix, ...) entre OpenSi et votre boutique.<br />
				Pour installer le cron, vous devez proc&eacute;der de la mani&egrave;re suivante :<br /><br />

				<strong>1. Connection &agrave; votre serveur en SSH</strong>
				<div class="cron-details">
					Pour se connecter, ouvrir un terminal et taper la ligne de commande suivante :<br />
					<span class="courier">ssh user@host</span><br />
					<i class="courier">Ex : ssh martin@120.01.02.03</i><br />
					Appuyer sur la touche "entr&eacute;e".<br />
					Rentrer le mot de passe et appuyer &agrave; nouveau sur la touche "entr&eacute;e".<br />
					Vous devez maintenant &ecirc;tre connect&eacute; &agrave; votre serveur.<br /><br />
					<span class="grey">&rarr; L\'&eacute;diteur OpenSi reste &agrave; votre disposition pour vous assister dans <a href="http://www.opensi.fr/Contact_Prestashop.html" class="grey" target="_blank">la mise en place de ce cron</a>.</span>
				</div>

				<strong>2. &Eacute;dition de la crontab</strong>
				<div class="cron-details">
					Pour &eacute;diter la crontab, taper la ligne de commande suivante :<br />
					<span class="courier">crontab -e</span><br />
					et appuyer sur la touche "entr&eacute;e".
				</div>

				<strong>3. Configuration de la crontab</strong>
				<div class="cron-details">
					Ajouter la ligne suivante dans votre crontab, enregistrer et fermer le fichier.<br />
					<span class="courier">*/10 * * * * sh '.dirname(__FILE__).'/cron.sh</span>
				</div>

				Votre crontab est maintenant configur&eacute;e.
			</fieldset>

			<br /><br />

			<fieldset>
				<legend><img src="'.$this->_path.'img/parameters.png" alt="" />'.$this->l('OpenSi adminnistration').'</legend>
				<span class="mini brown"><img src="'.$this->_path.'img/warning.png" alt="" />'.$this->l('Do not change the value of the fields below.').'</span><br /><br />
				<label>'.$this->l('Url').' <sup>*</sup></label>
				<div class="margin-form">
					<input type="text" name="osi_ws_url" class="biginp" id="ws_url" value="'.Tools::htmlentitiesUTF8(GlobalConfig::getWsUrl()).'" />
				</div>
				<label>'.$this->l('Port').' <sup>*</sup></label>
				<div class="margin-form">
					<input type="text" name="osi_ws_port" class="biginp" value="'.Tools::htmlentitiesUTF8(GlobalConfig::getWsPort()).'" />
				</div>
			</fieldset>
		';

		return $html;
	}


	/* Synchronisations tab */
	private function _displaySync()
	{
		/*
		 * Number of products
		 * Parent without child = 1
		 * Parent with 2 child = 2 (don't add the parent if there are children)
		 */
		$nbProd = Db::getInstance()->getRow("SELECT count(*) FROM "._DB_PREFIX_."product p LEFT JOIN "._DB_PREFIX_."product_attribute pa ON p.id_product = pa.id_product");

		/* Number of parent products */
		$nbParentProd = Db::getInstance()->getRow("SELECT count(*) FROM "._DB_PREFIX_."product");

		/* Number of child products */
		$nbChildProd = $nbProd['count(*)'] - $nbParentProd['count(*)'];

		/* Number of products without reference */
		$nbProdNoRef = Db::getInstance()->getRow("SELECT count(*) FROM "._DB_PREFIX_."product p LEFT JOIN "._DB_PREFIX_."product_attribute pa ON p.id_product = pa.id_product where (pa.id_product_attribute is null and p.reference = '') or (p.reference='' and pa.reference='')");

		/* Number of orders */
		$nbOrder = Db::getInstance()->getRow("SELECT count(*) FROM  `"._DB_PREFIX_."orders`");

		/* Number of clients */
		$nbClient = Db::getInstance()->getRow("SELECT count(*) FROM  `"._DB_PREFIX_."customer`");

		/*
		 * Get configuration mode
		 * 1 => Production
		 * 2 => Test
		 */
		$confMod = Db::getInstance()->getRow("SELECT * FROM `"._DB_PREFIX_."configuration` WHERE name = 'OSI_CONFIGURATION_MODE'");
		$confMode = $confMod["value"];


		/* html */
		$html = '<fieldset><legend><img src="'.$this->_path.'img/mode.png" alt="" />'.$this->l('Configuration parameters').'</legend>';

		if($confMode == '1') {
			/* Production configuration mode */
			$html .= '
				<label>'.$this->l('Configuration mode').' <sup>*</sup></label>
				<div class="margin-form">
					<select style="width:360px" id="osi_configuration_mode" name="osi_configuration_mode">
						<option value="1" selected="selected">Production</option>
						<option value="2">Test</option>
					</select>
				</div>
			';
		} else {
			/* Test configuration mode */
			$html .= '
				<label>'.$this->l('Configuration mode').' <sup>*</sup></label>
				<div class="margin-form">
					<select style="width:510px" id="osi_configuration_mode" name="osi_configuration_mode" onchange="changeValueFrequencies()">
						<option value="1">Production</option>
						<option value="2" selected="selected">Test</option>
					</select>
				</div>
			';
		}

		$html .= '
			</fieldset>
			<br /><br />
			<fieldset>
				<legend><img src="'.$this->_path.'img/parameters.png" alt="" />'.$this->l('Synchronizations configuration').'</legend>
				<i class="mini">'.$this->l('Note : Frequencies are expressed in minutes').'.<br /><span class="brown">'.$this->l('We advise you to get closer to the editor module before changing synchronization settings.').'</span></i><br /><br />

				<label class="strong">'.$this->l('PRODUCTS').' ('.$nbProd['count(*)'].')</label><br />
		';

		if($nbProdNoRef['count(*)'] > 0)
			$html .= '<div class="mini clear info_box">'.$this->l('Warning, your catalog has').' <strong>'.$nbProdNoRef['count(*)'].' '.$this->l('products without references').'</strong> !<br />'.$this->l('Please note that any product without reference will not be synchronized with OpenSi.').'</div>';

		$html .= '
				<table width="100%" class="wso">
					<tr>
						<td>'.$this->l('Create products').' <sup>*</sup></td>
						<td class="sync-p">
							<input type="text" class="ws-freq" name="'.Tools::htmlentitiesUTF8(strtolower($this->cachetimeLbl.'WSO-P005')).'" value="'.Tools::htmlentitiesUTF8(configuration::get($this->cachetimeLbl.'WSO-P005',2)).'" /> <span class="mini grey">(Min : 1)</span>
						</td>
						<td width="120">
							<select name="active_wso_p005" class="tinysel active_wso">
								<option value="1"';
									if(Configuration::get('OSI_ACTIVE_WSO-P005') == 1)
										$html .= 'selected="selected"';
									$html .= '>'.$this->l('Enable').'</option>
								<option value="0"';
									if(Configuration::get('OSI_ACTIVE_WSO-P005') == 0)
										$html .= 'selected="selected"';
									$html .= '>'.$this->l('Disable').'</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>'.$this->l('Update products').' <sup>*</sup></td>
						<td class="sync-p">
							<input type="text" class="ws-freq" name="'.Tools::htmlentitiesUTF8(strtolower($this->cachetimeLbl.'WSO-P006')).'" value="'.Tools::htmlentitiesUTF8(configuration::get($this->cachetimeLbl.'WSO-P006',2)).'" /> <span class="mini grey">(Min : 1)</span>
						</td>
						<td width="120">
							<select name="active_wso_p006" class="tinysel active_wso">
								<option value="1"';
									if(Configuration::get('OSI_ACTIVE_WSO-P006') == 1)
										$html .= 'selected="selected"';
									$html .= '>'.$this->l('Enable').'</option>
								<option value="0"';
									if(Configuration::get('OSI_ACTIVE_WSO-P006') == 0)
										$html .= 'selected="selected"';
									$html .= '>'.$this->l('Disable').'</option>
							</select>
						</td>
					</tr>
					<tr>
						<td width="300">'.$this->l('Update stocks').' <sup>*</sup></td>
						<td class="sync-o">
							<input type="text" class="ws-freq" name="'.Tools::htmlentitiesUTF8(strtolower($this->cachetimeLbl.'WSO-G002')).'" value="'.Tools::htmlentitiesUTF8(configuration::get($this->cachetimeLbl.'WSO-G002',2)).'" /> <span class="mini grey">(Min : 1)</span>
						</td>
						<td width="120">
							<select name="active_wso_g002" class="tinysel active_wso">
								<option value="1"';
									if(Configuration::get('OSI_ACTIVE_WSO-G002') == 1)
										$html .= 'selected="selected"';
									$html .= '>'.$this->l('Enable').'</option>
								<option value="0"';
									if(Configuration::get('OSI_ACTIVE_WSO-G002') == 0)
										$html .= 'selected="selected"';
									$html .= '>'.$this->l('Disable').'</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>'.$this->l('Update prices').' <sup>*</sup></td>
						<td  class="sync-o">
							<input type="text" class="ws-freq" name="'.Tools::htmlentitiesUTF8(strtolower($this->cachetimeLbl.'WSO-G009')).'" value="'.Tools::htmlentitiesUTF8(configuration::get($this->cachetimeLbl.'WSO-G009',2)).'" /> <span class="mini grey">(Min : 1)</span>
						</td>
						<td width="120">
							<select name="active_wso_g009" class="tinysel active_wso">
								<option value="1"';
									if(Configuration::get('OSI_ACTIVE_WSO-G009') == 1)
										$html .= 'selected="selected"';
									$html .= '>'.$this->l('Enable').'</option>
								<option value="0"';
									if(Configuration::get('OSI_ACTIVE_WSO-G009') == 0)
										$html .= 'selected="selected"';
									$html .= '>'.$this->l('Disable').'</option>
							</select>
						</td>
					</tr>
				</table>


				<br /><hr /><br />


				<label class="strong">'.$this->l('ORDERS').' ('.$nbOrder['count(*)'].')</label><br /><br />	
				<table width="100%" class="wso">
					<tr>
						<td width="300">'.$this->l('Create orders').' <sup>*</sup></td>
						<td class="sync-p">
							<input type="text" class="ws-freq" name="'.Tools::htmlentitiesUTF8(strtolower($this->cachetimeLbl.'WSO-P011')).'" value="'.Tools::htmlentitiesUTF8(configuration::get($this->cachetimeLbl.'WSO-P011',2)).'" /> <span class="mini grey">(Min : 1)</span>
						</td>
						<td width="120">
							<select name="active_wso_p011" class="tinysel active_wso">
								<option value="1"';
									if(Configuration::get('OSI_ACTIVE_WSO-P011') == 1)
										$html .= 'selected="selected"';
									$html .= '>'.$this->l('Enable').'</option>
								<option value="0"';
									if(Configuration::get('OSI_ACTIVE_WSO-P011') == 0)
										$html .= 'selected="selected"';
									$html .= '>'.$this->l('Disable').'</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>'.$this->l('Create transactions').' <sup>*</sup></td>
						<td class="sync-p">
							<input type="text" class="ws-freq" name="'.Tools::htmlentitiesUTF8(strtolower($this->cachetimeLbl.'WSO-P015')).'" value="'.Tools::htmlentitiesUTF8(configuration::get($this->cachetimeLbl.'WSO-P015',2)).'" /> <span class="mini grey">(Min : 1)</span>
						</td>
						<td width="120">
							<select name="active_wso_p015" class="tinysel active_wso">
								<option value="1"';
									if(Configuration::get('OSI_ACTIVE_WSO-P015') == 1)
										$html .= 'selected="selected"';
									$html .= '>'.$this->l('Enable').'</option>
								<option value="0"';
									if(Configuration::get('OSI_ACTIVE_WSO-P015') == 0)
										$html .= 'selected="selected"';
									$html .= '>'.$this->l('Disable').'</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>'.$this->l('Update states orders').' <sup>*</sup></td>
						<td class="sync-o">
							<input type="text" class="ws-freq" name="'.Tools::htmlentitiesUTF8(strtolower($this->cachetimeLbl.'WSO-G008')).'" value="'.Tools::htmlentitiesUTF8(configuration::get($this->cachetimeLbl.'WSO-G008',2)).'" /> <span class="mini grey">(Min : 1)</span>
						</td>
						<td width="120">
							<select name="active_wso_g008" class="tinysel active_wso">
								<option value="1"';
									if(Configuration::get('OSI_ACTIVE_WSO-G008') == 1)
										$html .= 'selected="selected"';
									$html .= '>'.$this->l('Enable').'</option>
								<option value="0"';
									if(Configuration::get('OSI_ACTIVE_WSO-G008') == 0)
										$html .= 'selected="selected"';
									$html .= '>'.$this->l('Disable').'</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>'.$this->l('Update orders tracking codes').' <sup>*</sup></<td>
						<td class="sync-o">
							<input type="text" class="ws-freq" name="'.Tools::htmlentitiesUTF8(strtolower($this->cachetimeLbl.'WSO-G003')).'" value="'.Tools::htmlentitiesUTF8(configuration::get($this->cachetimeLbl.'WSO-G003',2)).'" /> <span class="mini grey">(Min : 1)</span>
						</td>
						<td width="120">
							<select name="active_wso_g003" class="tinysel active_wso">
								<option value="1"';
									if(Configuration::get('OSI_ACTIVE_WSO-G003') == 1)
										$html .= 'selected="selected"';
									$html .= '>'.$this->l('Enable').'</option>
								<option value="0"';
									if(Configuration::get('OSI_ACTIVE_WSO-G003') == 0)
										$html .= 'selected="selected"';
									$html .= '>'.$this->l('Disable').'</option>
							</select>
						</td>
					</tr>
				</table>


				<br /><hr /><br />


				<label class="strong">'.$this->l('CUSTOMERS').' ('.$nbClient['count(*)'].')</label><br /><br />	
				<table width="100%" class="wso">
					<tr>
						<td width="300">'.$this->l('Create customers').' <sup>*</sup></td>
						<td class="sync-p">
							<input type="text" class="ws-freq" name="'.Tools::htmlentitiesUTF8(strtolower($this->cachetimeLbl.'WSO-P010')).'" value="'.Tools::htmlentitiesUTF8(configuration::get($this->cachetimeLbl.'WSO-P010',2)).'" /> <span class="mini grey">(Min : 1)</span>
						</td>
						<td width="120">
							<select name="active_wso_p010" class="tinysel active_wso">
								<option value="1"';
									if(Configuration::get('OSI_ACTIVE_WSO-P010') == 1)
										$html .= 'selected="selected"';
									$html .= '>'.$this->l('Enable').'</option>
								<option value="0"';
									if(Configuration::get('OSI_ACTIVE_WSO-P010') == 0)
										$html .= 'selected="selected"';
									$html .= '>'.$this->l('Disable').'</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>'.$this->l('Update customers').' <sup>*</sup></td>
						<td class="sync-p">
							<input type="text" class="ws-freq" name="'.Tools::htmlentitiesUTF8(strtolower($this->cachetimeLbl.'WSO-P025')).'" value="'.Tools::htmlentitiesUTF8(configuration::get($this->cachetimeLbl.'WSO-P025',2)).'" /> <span class="mini grey">(Min : 1)</span>
						</td>
						<td width="120">
							<select name="active_wso_p025" class="tinysel active_wso">
								<option value="1"';
									if(Configuration::get('OSI_ACTIVE_WSO-P025') == 1)
										$html .= 'selected="selected"';
									$html .= '>'.$this->l('Enable').'</option>
								<option value="0"';
									if(Configuration::get('OSI_ACTIVE_WSO-P025') == 0)
										$html .= 'selected="selected"';
									$html .= '>'.$this->l('Disable').'</option>
							</select>
						</td>
					</tr>
				</table>


				<br /><hr />


				<table class="wso">
					<tr><td class="sync-o brown mini"><i>'.$this->l('Synchronization from OpenSi to Prestashop').'</i></td></tr>
					<tr><td class="sync-p brown mini"><i>'.$this->l('Synchronization from Prestashop to OpenSi').'</i></td></tr>
				</table>

			</fieldset>
		';

		return $html;
	}


	/* Mapping tab */
	private function _displayMapping()
	{
		global $cookie;

		/* GET LISTS */
		$db = Db::getInstance();

		/* Get list features */
		$query = 'SELECT fl.id_feature, fl.name FROM `'._DB_PREFIX_.'feature` as f, `'._DB_PREFIX_.'feature_lang` as fl WHERE f.id_feature=fl.id_feature AND fl.id_lang = "'.(int)GlobalConfig::getDefaultLangId().'"';
		$result = $db->ExecuteS($query);
		$this->listFeatures = $result;

		/* Get list Groups attributes */
		$query = 'SELECT agl.id_attribute_group, agl.public_name FROM `'._DB_PREFIX_.'attribute_group` as ag, `'._DB_PREFIX_.'attribute_group_lang` as agl WHERE ag.id_attribute_group=agl.id_attribute_group AND agl.id_lang = "'.(int)GlobalConfig::getDefaultLangId().'"';
		$result = $db->ExecuteS($query);
		$this->listAttributesGp = $result;

		/* Get list Order states */
		$query = 'SELECT osl.id_order_state, osl.name FROM `'._DB_PREFIX_.'order_state` as os, `'._DB_PREFIX_.'order_state_lang` as osl WHERE os.id_order_state=osl.id_order_state AND osl.id_lang = "'.(int)GlobalConfig::getDefaultLangId().'"';
		$result = $db->ExecuteS($query);
		$this->listOrderStates = $result;


		/* html */
		$html = '
			<fieldset>
				<legend><img src="'.$this->_path.'img/liaisons.png" alt="" />'.$this->l('Attributes links').'</legend>
				<p class="black">
					<a href="index.php?tab=AdminFeatures&token='.Tools::getAdminToken('AdminFeatures'.intval(Tab::getIdFromClassName('AdminFeatures')).intval($cookie->id_employee)).'"><img src="'.$this->_path.'img/add.gif" alt="" />'.$this->l('Add a new feature').'</a>
					&nbsp;&nbsp;
					<a href="index.php?tab=AdminAttributesGroups&token='.Tools::getAdminToken('AdminAttributesGroups'.intval(Tab::getIdFromClassName('AdminAttributesGroups')).intval($cookie->id_employee)).'"><img src="'.$this->_path.'img/add.gif" alt="" />'.$this->l('Add a new attribut').'</a>
				</p>
				<label>'.$this->l('Attribute 1').'</label>
				<div class="margin-form">
					'.$this->utilHtmlLinkAttribute(1).'
				</div>
				<label>'.$this->l('Attribute 2').'</label>
				<div class="margin-form">
					'.$this->utilHtmlLinkAttribute(2).'
				</div>
				<label>'.$this->l('Attribute 3').'</label>
				<div class="margin-form">
					'.$this->utilHtmlLinkAttribute(3).'
				</div>
				<label>'.$this->l('Attribute 4').'</label>
				<div class="margin-form">
					'.$this->utilHtmlLinkAttribute(4).'
				</div>
				<label>'.$this->l('Attribute 5').'</label>
				<div class="margin-form">
					'.$this->utilHtmlLinkAttribute(5).'
				</div>
				<label>'.$this->l('Attribute 6').'</label>
				<div class="margin-form">
					'.$this->utilHtmlLinkAttribute(6).'
				</div>
				<label>'.$this->l('Volume').'</label>
				<div class="margin-form">
					'.$this->utilHtmlLinkFeature('volume').'
				</div>
			</fieldset>

			<br /><br />

			<fieldset>
				<legend><img src="'.$this->_path.'img/orders.png" alt="" />'.$this->l('Orders status').'</legend>
				<p class="black">
					<a class="black" href="index.php?tab=AdminStatuses&token='.Tools::getAdminToken('AdminStatuses'.intval(Tab::getIdFromClassName('AdminStatuses')).intval($cookie->id_employee)).'"><img src="'.$this->_path.'img/add.gif" alt="" />'.$this->l('Add a new status').'</a>
				</p>
				<label>'.$this->l('ID - On preparation').' <sup>*</sup></label>
				<div class="margin-form">
					'.$this->utilHtmlOrderState('on_preparation').'
				</div>
				<label>'.$this->l('ID - On delivery').' <sup>*</sup></label>
				<div class="margin-form">
					'.$this->utilHtmlOrderState('on_delivery').'
				</div>
				<label>'.$this->l('ID - Canceled').' <sup>*</sup></label>
				<div class="margin-form">
					'.$this->utilHtmlOrderState('canceled').'
				</div>
			</fieldset>

			<br /><br />

			<fieldset>
				<legend><img src="'.$this->_path.'img/price.png" alt="" />'.$this->l('Price to use').'</legend>
				<i class="mini">'.$this->l('You can manage up to 5 different prices for the same item in OpenSi. Select the price you want to use below...').'</i><br />
				<i class="mini brown">'.$this->l('We advise you to get closer to the editor module before changing this parameter.').'</i><br /><br />
				<label>'.$this->l('Price used for this website').' <sup>*</sup></label>
				<div class="margin-form">
					'.$this->utilHtmlDefaultPrice().'
				</div>
			</fieldset>
		';

		return $html;
	}


	/* Journal tab */
	private function _displayJournal()
	{
		$now = date("d/m/Y");

		$html = '
			<fieldset>
				<legend><img src="'.$this->_path.'img/update.png" alt="" />'.$this->l('Last updates').'</legend>
				<span class="mini">'.$this->l('You can find below the dates of the last synchronization at the level of items, orders and customers...').'</span><br /><br />
				<strong>'.$this->l('PRODUCTS LAST UPDATES').'</strong><br /><br />
				<table class="table" width="100%" cellspacing="0" cellpadding="0">
					<tr>
						<th width="25%">'.$this->l('New products').'</th>
						<th width="25%">'.$this->l('Existing products').'</th>
						<th width="25%">'.$this->l('Stocks').'</th>
						<th width="25%">'.$this->l('Prices').'</th>
					</tr>
					<tr>
		';

		/* Last creation products */
		$LastReqWSOP005 = Configuration::get('OSI_LASTREQUEST_WSO-P005');
		if($LastReqWSOP005 == 0) {
			$html .= '<td>'.$this->l('Not synchronized').'</td>';
		} else {
			$LastReqWSOP005 = date("d/m/Y H:i:s", $LastReqWSOP005);
			if (substr($LastReqWSOP005, 0, 10) != $now) {
				$html .= '<td><span class="red">'.$LastReqWSOP005.'</span></td>';
			} else {
				$html .= '<td>'.$LastReqWSOP005.'</td>';
			}
		}

		/* Last update products */
		$LastReqWSOP006 = Configuration::get('OSI_LASTREQUEST_WSO-P006');
		if($LastReqWSOP006 == 0) {
			$html .= '<td>'.$this->l('Not synchronized').'</td>';
		} else {
			$LastReqWSOP006 = date("d/m/Y H:i:s", $LastReqWSOP006);
			if (substr($LastReqWSOP006, 0, 10) != $now) {
				$html .= '<td><span class="red">'.$LastReqWSOP006.'</span></td>';
			} else {
				$html .= '<td>'.$LastReqWSOP006.'</td>';
			}
		}

		/* Last update stock */
		$LastReqWSOG002 = Configuration::get('OSI_LASTREQUEST_WSO-G002');
		if($LastReqWSOG002 == 0) {
			$html .= '<td>'.$this->l('Not synchronized').'</td>';
		} else {
			$LastReqWSOG002 = date("d/m/Y H:i:s", $LastReqWSOG002);
			if (substr($LastReqWSOG002, 0, 10) != $now) {
				$html .= '<td><span class="red">'.$LastReqWSOG002.'</span></td>';
			} else {
				$html .= '<td>'.$LastReqWSOG002.'</td>';
			}
		}

		/* Last update prices */
		$LastReqWSOG009 = Configuration::get('OSI_LASTREQUEST_WSO-G009');
		if($LastReqWSOG009 == 0) {
			$html .= '<td>'.$this->l('Not synchronized').'</td>';
		} else {
			$LastReqWSOG009 = date("d/m/Y H:i:s", $LastReqWSOG009);
			if (substr($LastReqWSOG009, 0, 10) != $now) {
				$html .= '<td><span class="red">'.$LastReqWSOG009.'</span></td>';
			} else {
				$html .= '<td>'.$LastReqWSOG009.'</td>';
			}
		}

		$html .= '
					</tr>
				</table>

				<br /><br /><br />

				<strong>'.$this->l('ORDERS LAST UPDATES').'</strong><br /><br />
				<table class="table" width="100%" cellspacing="0" cellpadding="0">
					<tr>
						<th width="25%">'.$this->l('New orders').'</th>
						<th width="25%">'.$this->l('Transactions').'</th>
						<th width="25%">'.$this->l('Order statment').'</th>
						<th width="25%">'.$this->l('Tracking colis').'</th>
					</tr>
					<tr>
		';

        /* Last update orders */
		$LastReqWSOP011 = Configuration::get('OSI_LASTREQUEST_WSO-P011');
		if($LastReqWSOP011 == 0) {
			$html .= '<td>'.$this->l('Not synchronized').'</td>';
		} else {
			$LastReqWSOP011 = date("d/m/Y H:i:s", $LastReqWSOP011);
			if (substr($LastReqWSOP011, 0, 10) != $now) {
				$html .= '<td><span class="red">'.$LastReqWSOP011.'</span></td>';
			} else {
				$html .= '<td>'.$LastReqWSOP011.'</td>';
			}
		}

		/* Last update transaction */
		$LastReqWSOP015 = Configuration::get('OSI_LASTREQUEST_WSO-P015');
		if($LastReqWSOP015 == 0) {
			$html .= '<td>'.$this->l('Not synchronized').'</td>';
		} else {
			$LastReqWSOP015 = date("d/m/Y H:i:s", $LastReqWSOP015);
			if (substr($LastReqWSOP015, 0, 10) != $now) {
				$html .= '<td><span class="red">'.$LastReqWSOP015.'</span></td>';
			} else {
				$html .= '<td>'.$LastReqWSOP015.'</td>';
			}
		}

		/* Last update order statement */
		$LastReqWSOG008 = Configuration::get('OSI_LASTREQUEST_WSO-G008');
		if($LastReqWSOG008 == 0) {
			$html .= '<td>'.$this->l('Not synchronized').'</td>';
		} else {
			$LastReqWSOG008 = date("d/m/Y H:i:s", $LastReqWSOG008);
			if (substr($LastReqWSOG008, 0, 10) != $now) {
				$html .= '<td><span class="red">'.$LastReqWSOG008.'</span></td>';
			} else {
				$html .= '<td>'.$LastReqWSOG008.'</td>';
			}
		}

		/* Last update tracking colis */
		$LastReqWSOG003 = Configuration::get('OSI_LASTREQUEST_WSO-G003');
		if($LastReqWSOG003 == 0) {
			$html .= '<td>'.$this->l('Not synchronized').'</td>';
		} else {
			$LastReqWSOG003 = date("d/m/Y H:i:s", $LastReqWSOG003);
			if (substr($LastReqWSOG003, 0, 10) != $now) {
				$html .= '<td><span class="red">'.$LastReqWSOG003.'</span></td>';
			} else {
				$html .= '<td>'.$LastReqWSOG003.'</td>';
			}
		}

		$html .= '
					</tr>
				</table>

				<br /><br /><br />

				<strong>'.$this->l('CUSTOMERS LAST UPDATES').'</strong><br /><br />
				<table class="table" width="100%" cellspacing="0" cellpadding="0">
					<tr>
						<th width="25%">'.$this->l('New customers').'</th>
						<th width="75%">'.$this->l('Existing customers').'</th>
					</tr>
					<tr>
		';

        /* Last creation clients */
		$LastReqWSOP010 = Configuration::get('OSI_LASTREQUEST_WSO-P010');
		if($LastReqWSOP010 == 0) {
			$html .= '<td>'.$this->l('Not synchronized').'</td>';
		} else {
			$LastReqWSOP010 = date("d/m/Y H:i:s", $LastReqWSOP010);
			if (substr($LastReqWSOP010, 0, 10) != $now) {
				$html .= '<td><span class="red">'.$LastReqWSOP010.'</span></td>';
			} else {
				$html .= '<td>'.$LastReqWSOP010.'</td>';
			}
		}

		/* Last update clients */
		$LastReqWSOP025 = Configuration::get('OSI_LASTREQUEST_WSO-P025');
		if($LastReqWSOP025 == 0) {
			$html .= '<td>'.$this->l('Not synchronized').'</td>';
		} else {
			$LastReqWSOP025 = date("d/m/Y H:i:s", $LastReqWSOP025);
			if (substr($LastReqWSOP025, 0, 10) != $now) {
				$html .= '<td><span class="red">'.$LastReqWSOP025.'</span></td>';
			} else {
				$html .= '<td>'.$LastReqWSOP025.'</td>';
			}
		}

        $html .= '
					</tr>
				</table>
			</fieldset>
		';

		return $html;
	}


	/* Documentation tab */
	private function _displayDoc()
	{
		$html = '
			<img src="'.$this->_path.'img/osi_promo.jpg" alt="" />
			<br /><br /><br />
			<fieldset>
				<legend><img src="'.$this->_path.'img/pdf.png" alt="" />'.$this->l('Documentation').'</legend>
				'.$this->l('The module OpenSi Connect allows you to sync PrestaShop to OpenSi E-Commerce software business management and accounting.').'<br />
				'.$this->l('SpeedInfo has developed a connector for OpenSi E-Commerce & PrestaShop as module OpenSi Connect.').'<br />
				'.$this->l('OpenSi E-Commerce is a business management software and accounting dedicated to e-commerce.').'<br /><br />
				
				<a href="http://www.opensi.fr/connect/Guide_utilisateur_OpenSi_Connect_Prestashop.pdf">'.$this->l('User guide - OpenSi for Prestashop').'</a>

				<br /><br />

				'.$this->l('You should find in this guide the complete documentation to install, configure and use the OpenSi module for Prestashop.').'

				<br /><br />

				'.$this->l('More informations availble on').' <a href="http://www.opensi.fr" target="_blank">www.opensi.fr</a>.
			</fieldset>
		';

		return $html;
	}


	/* Preferences tab */
	private function _displayPrefs()
	{
		$html = '';

		/* Display only if Prestashop 1.4 minimum */
		if(substr(_PS_VERSION_, 0, 3) > 1.3) {
			$html .= '
				<fieldset>
					<legend><img src="'.$this->_path.'img/invoice.png" alt="" />'.$this->l('Manage invoices').'</legend>
					<div class="mini">
						'.$this->l('This option allow your customers to download the OpenSi invoices instead of the Prestashop invoices.').'<br />
						'.$this->l('In the order details on the customer account, the user can download the OpenSi invoice instead of the Prestashop invoice.').'
					</div>
					<br />
					<label>'.$this->l('Use OpenSi invoices').' <sup>*</sup></label>
					<div class="margin-form">
						<div class="divradio">
							<input type="radio" name="osi_invoice" value="0"';
							if(Configuration::get('OSI_INVOICE') == 0)
								$html .= 'checked="checked"';
							$html .= '> '.$this->l('No').'
							&nbsp;&nbsp;
							<input type="radio" name="osi_invoice" value="1"';
							if(Configuration::get('OSI_INVOICE') == 1)
								$html .= 'checked="checked"';
							$html .= '> '.$this->l('Yes').'
						</div>
					</div>
				</fieldset>

				<br /><br />
			';
		}

		$html .= '
			<fieldset>
				<legend><img src="'.$this->_path.'img/sync.png" alt="" />'.$this->l('Display synchronization with OpenSi').'</legend>
				<div class="mini">
					'.$this->l('This option allows you to see in the order details (admin panel) if it is synchronized with OpenSi or not, and the OpenSi invoices.').'
				</div>
				<br />
				<label>'.$this->l('Display synchronization with OpenSi').' <sup>*</sup></label>
				<div class="margin-form">
					<div class="divradio">
						<input type="radio" name="osi_hook_admin_order" value="0"';
						if(Configuration::get('OSI_HOOK_ADMIN_ORDER') == 0)
							$html .= 'checked="checked"';
						$html .= '> '.$this->l('No').'
						&nbsp;&nbsp;
						<input type="radio" name="osi_hook_admin_order" value="1"';
						if(Configuration::get('OSI_HOOK_ADMIN_ORDER') == 1)
							$html .= 'checked="checked"';
						$html .= '> '.$this->l('Yes').'
					</div>
				</div>
			</fieldset>
		';

		return $html;
	}


	/*
	 * Generate html listbox for order states
	 * @param $stateName
	 */
	public function utilHtmlOrderState($stateName){

		if ($stateName == 'on_preparation'){
			$stateValue =  GlobalConfig::getStateIdOnPreparation();
		} else  if ($stateName == 'on_delivery'){
			$stateValue =  GlobalConfig::getStateIdOnDelivery();
		} else  if ($stateName == 'canceled'){
			$stateValue =  GlobalConfig::getStateIdCanceled();
		}

		$selectOptions = '';
		foreach($this->listOrderStates as $state){
			$isSelected = ($state['id_order_state'] != "" && $state['id_order_state'] == $stateValue)?("selected"):("");
			$selectOptions .= "<option value='".$state['id_order_state']."' ".$isSelected.">".$state['name']."</option>";
		}

		$html = '<select name="osi_state_id_'.$stateName.'" style="width:360px">'.$selectOptions.'</select>';

		return $html;
	}


	/*
	 * Generate html two listbox for link attribute
	 * @param $numAttribute
	 */
	public function utilHtmlLinkAttribute($numAttribute){

		if($numAttribute == 1){
			$linkAttributeValue =  GlobalConfig::getLinkAttribute1();
			$isFeature = (GlobalConfig::getLinkAttribute1_isfeature() == 1)?(true):(false);
		} else  if ($numAttribute == 2){
			$linkAttributeValue =  GlobalConfig::getLinkAttribute2();
			$isFeature = (GlobalConfig::getLinkAttribute2_isfeature() == 1)?(true):(false);
		} else  if ($numAttribute == 3){
			$linkAttributeValue =  GlobalConfig::getLinkAttribute3();
			$isFeature = (GlobalConfig::getLinkAttribute3_isfeature() == 1)?(true):(false);
		} else  if ($numAttribute == 4){
			$linkAttributeValue =  GlobalConfig::getLinkAttribute4();
			$isFeature = (GlobalConfig::getLinkAttribute4_isfeature() == 1)?(true):(false);
		} else  if ($numAttribute == 5){
			$linkAttributeValue =  GlobalConfig::getLinkAttribute5();
			$isFeature = (GlobalConfig::getLinkAttribute5_isfeature() == 1)?(true):(false);
		} else  if ($numAttribute == 6){
			$linkAttributeValue =  GlobalConfig::getLinkAttribute6();
			$isFeature = (GlobalConfig::getLinkAttribute6_isfeature() == 1)?(true):(false);
		}

		$selectOptionsFeatures = "<option value=''>---</option>";
		foreach($this->listFeatures as $feature){
			$isSelected = ($isFeature && $feature['id_feature'] != "" && $feature['id_feature'] == $linkAttributeValue)?("selected"):("");
			$selectOptionsFeatures .= "<option value='".$feature['id_feature']."' ".$isSelected.">".$feature['name']."</option>";
		}

		$selectOptionsAtt = "<option value=''>---</option>";
		foreach($this->listAttributesGp as $attribGp){
			$isSelected = (!$isFeature && $attribGp['id_attribute_group'] != "" && $attribGp['id_attribute_group'] == $linkAttributeValue)?("selected"):("");
			$selectOptionsAtt .= "<option value='".$attribGp['id_attribute_group']."' ".$isSelected.">".$attribGp['public_name']."</option>";
		}

		$checkedFeature = ($isFeature)?("checked"):("");
		$checkedAttributesGp = ($isFeature)?(""):("checked");

		$disableFeature = (!$isFeature == true)?('disabled="disabled" style="background:#fffff7; width:200px"'):('style="width:200px"');
		$disableAttribut = ($isFeature == true)?('disabled="disabled" style="background:#fffff7; width:200px"'):('style="width:200px"');

		$html = '
				<select name="osi_link_attribute'.$numAttribute.'_features" class="radio_feature"'.$disableFeature.'>'.$selectOptionsFeatures.'</select>
				&nbsp;&nbsp;
				'.$this->l('Feat.').' <input type="radio" class="choose" name="osi_link_attribute'.$numAttribute.'_isfeature" value="1" '.$checkedFeature.'/>
				&nbsp;&nbsp;OU&nbsp;&nbsp;
				<input type="radio" class="choose" name="osi_link_attribute'.$numAttribute.'_isfeature" value="0" '.$checkedAttributesGp.'/>
				'.$this->l('Attrib.').'
				&nbsp;&nbsp;
				<select name="osi_link_attribute'.$numAttribute.'_attributesgp" class="radio_attribute" '.$disableAttribut.'>'.$selectOptionsAtt.'</select>
		';

		return $html;
	}


	/*
	 * Generate html listbox for link feature
	 * @param $featureName
	 */
	public function utilHtmlLinkFeature($featureName){

		if($featureName == 'volume'){
			$linkFeatureValue =  GlobalConfig::getLinkFeatureVolume();
		}

		$selectOptions = "<option value=''>---</option>";
		foreach($this->listFeatures as $feature){
			$isSelected = ($feature['id_feature'] != "" && $feature['id_feature'] == $linkFeatureValue)?("selected"):("");
			$selectOptions .= "<option value='".$feature['id_feature']."' ".$isSelected.">".$feature['name']."</option>";
		}

		$html = '<select name="osi_link_feature_'.$featureName.'" style="width:200px">'.$selectOptions.'</select>';

		return $html;
	}


	/*
	 * Check if the submit time for each webservice is big enough
	 * return boolean : true (is valid);  false (error, is not valid)
	 * @param $wsName is the name of webservice 
	 */
	public function utilCheckWsTimeValidity() {
		$fullListWs = array_merge($this->wsGetList, $this->wsPostList);
		$isOk = true;

		foreach($fullListWs as $wsName){
			$time = Tools::getValue(strtolower($this->cachetimeLbl.$wsName));
			if($time < $this->WsReqListDefaultDelay[$wsName]) {
				$isOk = false;
			}
		}

		return $isOk;
	}


	/*
	 * Generate html listbox for price
	 * @param $priceName
	 */
	public function utilHtmlDefaultPrice(){

		$defaultPriceValue = GlobalConfig::getDefaultPrice();
		$selectOptions = "";

		for ($i = 1; $i <= 5; $i++) {
			$isSelected = ($i == $defaultPriceValue)?("selected"):("");
			$selectOptions .= "<option value='".$i."' ".$isSelected.">".$this->l('Price')." ".$i."</option>";
		}

		$html = '<select name="osi_default_price" style="width:360px">'.$selectOptions.'</select>';

		return $html;
	}


	/* Display synchronisation state of the order with OpenSi */
	public function hookAdminOrder($params)
	{
		if(Configuration::get('OSI_HOOK_ADMIN_ORDER') == 1) {
			global $cookie;
			$order = new Order($params['id_order']);
			$return = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'opensi_order WHERE id_order = \''.(int)($order->id).'\' LIMIT 1');

			$html = '
				<br /><br />
				<fieldset style="width:400px">
					<legend><img src="'.$this->_path.'logo.gif" alt="" /> '.$this->l('Sync with OpenSi').'</legend>
			';

			/*
			 * Check if upgrade is available
			 */
			if (@ini_get('allow_url_fopen') AND $update = GlobalConfig::checkOpenSiVersion($this->version))
				$html .= '<div class="warn">'.$this->l('New OpenSi version available').'<br /><a style="text-decoration: underline;" href="'.$update['link'].'">'.$this->l('Download').'&nbsp;'.$update['name'].'</a> !</div>';

			/*
			 * Is the order synchronized with OpenSi ?
			 */
			if(isset($return[0]['id_order'])) {
				$html .= $this->l('Order synchronized with OpenSi the :').' '.Tools::displayDate($return[0]['date_order_synchro'], (int)($cookie->id_lang), true);
			} else {
				$html .= $this->l('Order not synchronized with OpenSi.');
			}

			/* Display links of OpenSi invoices if available */
			$query = 'SELECT * FROM '._DB_PREFIX_.'opensi_invoice WHERE id_order = \''.(int)($order->id).'\'';
			if ($links = Db::getInstance()->ExecuteS($query)) {
				$html .= '<br />';
				$i = 0;
				foreach ($links as $link) {
					$html .= '<br /><img src="'.$this->_path.'img/invoice.png" alt="" /> <a href="'._PS_BASE_URL_.__PS_BASE_URI__.$this->_moduleUri.'getInvoice.php?key='.$link['url_key'].'">';

					if($link['type'] == 'F') {
						$html .= $this->l('Invoice').' '.$link['number_invoice'].'.pdf';
					} else if($link['type'] == 'A') {
						$html .= $this->l('Credit').' '.$link['number_invoice'].'.pdf';
					}

					$html .= '</a>';
					$i++;
				}	
			}

			$html .= '</fieldset>';
			return $html;
		}
	}


	/* Display OpenSi invoice in the customer account */
	public function hookOrderDetailDisplayed($params)
	{
		if(Configuration::get('OSI_INVOICE') == 1) {
			/* Is the OpenSi invoice is active */
			$query = 'SELECT * FROM '._DB_PREFIX_.'opensi_invoice WHERE id_order = \''.(int)(Tools::getValue('id_order')).'\'';
		 	if ($links = Db::getInstance()->ExecuteS($query)) {
				$html = '
					<div class="table_block" style="clear:both">
						<table class="std">
							<thead>
								<tr>
									<th class="first_item">'.$this->l('Your invoices / credits').'</th>
								</tr>
							</thead>
							<tbody>';
								$i = 0;
								foreach ($links as $link) {
									$html .= ($i%2 == 0)?'<tr class="item">':'<tr class="item alternate_item">';
									$html .= '<td><img src="'.$this->_path.'img/invoice.png" alt="" /> <a href="'._PS_BASE_URL_.__PS_BASE_URI__.$this->_moduleUri.'getInvoice.php?key='.$link['url_key'].'">';

									if($link['type'] == 'F') {
										$html .= $this->l('Invoice').' '.$link['number_invoice'].'.pdf';
									} else if($link['type'] == 'A') {
										$html .= $this->l('Credit').' '.$link['number_invoice'].'.pdf';
									}

									$html .= '</a></td></tr>';
									$i++;
								}
							$html .= '</tbody>
						</table>
					</div>
				';
				return $html;
			}
		}
	}

}