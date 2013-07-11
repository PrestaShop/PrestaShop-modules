<?php

/*
 * 2007-2013 PrestaShop
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
 *  @copyright  2007-2013 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

require_once 'lib/includes/includes.inc.php';

class FianetSceau extends Module
{

	/**
	 * Table of differents order statuses 
	 * @var Array 
	 */
	private $_fianetsceau_order_statuses = array(
		1 => 'waiting payment',
		2 => 'sent',
		3 => 'error'
	);

	/**
	 * Table of differents status 
	 * @var Array 
	 */
	private $_fianetsceau_statuses = array(
		'test',
		'prod',
	);

	/**
	 * Table of differents payment type 
	 * @var Array 
	 */
	private $_payment_types = array(
		1 => 'Carte',
		2 => 'Chèque',
		3 => 'Contre remboursement',
		4 => 'Virement',
		5 => 'CB en n fois',
		6 => 'Paypal',
		7 => '1euro.com',
		8 => 'Kwixo',
	);

	/**
	 * Table of differents logo and widget position 
	 * @var Array 
	 */
	private $_fianetsceau_positions = array('en' =>
		array(
			'nothing' => 'Select a position',
			'top' => 'Top of page',
			'bottom' => 'Bottom of page',
			'left' => 'In the left column',
			'right' => 'In the right column'
		),
		'fr' => array(
			'nothing' => 'Sélectionnez une position',
			'top' => 'Haut de page',
			'bottom' => 'Bas de page',
			'left' => 'Colonne de gauche',
			'right' => 'Colonne de droite'
		)
	);

	/**
	 * Table of differents logo size 
	 * @var Array 
	 */
	private $_fianetsceau_logo_sizes = array(
		'120' => 'logo_120.png',
		'150' => 'logo_150.png',
		'170' => 'logo_170.png',
		'190' => 'logo_190.png',
	);

	/**
	 * Table of differents widgets type 
	 * @var Array 
	 */
	private $_fianetsceau_widgets = array(
		1 => array('background' => 'blanc', 'shape' => 'squared'),
		2 => array('background' => 'transparent', 'shape' => 'squared'),
		3 => array('background' => 'blanc', 'shape' => 'circle'),
		4 => array('background' => 'transparent', 'shape' => 'circle'),
		5 => array('background' => 'blanc', 'shape' => 'comment'),
		6 => array('background' => 'transparent', 'shape' => 'comment'),
	);

	const SCEAU_ORDER_TABLE_NAME = 'fianetsceau_order';
	const SCEAU_STATE_TABLE_NAME = 'fianetsceau_state';

	public function __construct()
	{
		$this->name = 'fianetsceau';
		$this->version = '2.0';
		$this->tab = 'front_office_features';
		$this->author = 'Fia-Net';
		$this->displayName = $this->l('Fia-Net - Sceau de Confiance');
		$this->description = $this->l('Turn your visitors into buyers by creating confidence in your site.');

		/* Backward compatibility */
		if (_PS_VERSION_ < '1.5')
		{
			$this->backward_error = $this->l('In order to work properly in PrestaShop v1.4, the FIA-NET Sceau de confiance module requiers the backward compatibility module at least v0.3.').'<br />';
			$this->backward_error .= $this->l('You can download this module for free here: http://addons.prestashop.com/en/modules-prestashop/6222-backwardcompatibility.html');
			if (file_exists(_PS_MODULE_DIR_.'backwardcompatibility/backward_compatibility/backward.php'))
			{
				include(_PS_MODULE_DIR_.'backwardcompatibility/backward_compatibility/backward.php');
				$this->backward = true;
			}
			else
				$this->warning = $this->backward_error;
		}
		else
			$this->backward = true;
		parent::__construct();
	}

	public function install()
	{
		//create log file
		SceauLogger::insertLogSceau(__METHOD__." : ".__LINE__, "Création du fichier de log");

		/** database tables creation * */
		$sqlfile = dirname(__FILE__).'/install.sql';
		if (!file_exists($sqlfile) || !($sql = Tools::file_get_contents($sqlfile)))
			return false;

		$sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
		$sql = str_replace('SCEAU_ORDER_TABLE_NAME', self::SCEAU_ORDER_TABLE_NAME, $sql);
		$sql = str_replace('SCEAU_STATE_TABLE_NAME', self::SCEAU_STATE_TABLE_NAME, $sql);
		$queries = preg_split("/;\s*[\r\n]+/", $sql);
		foreach ($queries as $query)
			if (!Db::getInstance()->Execute(trim($query)))
			{
				SceauLogger::insertLogSceau(__METHOD__." : ".__LINE__, "Installation failed, database creation failed : ".Db::getInstance()->getMsgError());
				return false;
			}

		//Sceau order statuses insertion
		foreach ($this->_fianetsceau_order_statuses as $id => $label)
		{
			$sql = "INSERT INTO `"._DB_PREFIX_.self::SCEAU_STATE_TABLE_NAME."` (`id_fianetsceau_state`,`label`) VALUES ('".(int) $id."','".pSQL((string) $label)."')";
			$insert = Db::getInstance()->execute($sql);
			if (!$insert)
				SceauLogger::insertLogSceau(__METHOD__." : ".__LINE__, "Insertion state $id.$label échouée : ".Db::getInstance()->getMsgError());
		}

		//tabs creation
		$tab_admin_id_order = Tab::getIdFromClassName('AdminOrders');

		//AdminSceauController registration
		$tab_controller_main = new Tab();
		$tab_controller_main->active = 1;
		$tab_controller_main->class_name = "AdminSceau";
		foreach (Language::getLanguages() as $language)
			$tab_controller_main->name[$language['id_lang']] = "Sceau de Confiance";
		$tab_controller_main->id_parent = $tab_admin_id_order;
		$tab_controller_main->module = $this->name;
		$tab_controller_main->add();
		$tab_controller_main->move(Tab::getNewLastPosition(0));

		//Hook register
		return (parent::install()
			&& $this->registerHook('newOrder')
			&& $this->registerHook('paymentConfirm')
			&& $this->registerHook('adminOrder')
			&& $this->registerHook('backOfficeHeader')
			&& $this->registerHook('top')
			&& $this->registerHook('footer')
			&& $this->registerHook('leftColumn')
			&& $this->registerHook('rightColumn')
			);
	}

	public function uninstall()
	{
		if (!parent::uninstall())
			return false;
		else
		{
			//delete sceau tab
			$id = Tab::getIdFromClassName('AdminSceau');
			$tab = new Tab($id);
			$tab->delete();

			//drop sceau state table
			Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.self::SCEAU_STATE_TABLE_NAME.'`');
			return true;
		}
	}

	/**
	 * Load css and javascript file
	 * 
	 * @param type $params
	 * @return html 
	 */
	public function hookbackOfficeHeader($params)
	{
		$html = '<link rel="stylesheet" type="text/css" href="'.$this->_path.'css/toolbarAdmin.css" />';
		$html .= '<script type="text/javascript" src="'.$this->_path.'js/javascript.js"></script>';

		return $html;
	}

	/**
	 * insert new order into fianetsceau_order table with fields in parameters
	 *
	 * @param int $id_order
	 * @param array $fields
	 * @return boolean
	 */
	private function insertOrder($id_order, array $fields)
	{

		$fieldnames = "";
		$fieldvalues = "";
		foreach ($fields as $key_field => $elem)
		{
			$fieldnames .= "`".bqSQL($key_field)."`,";
			$fieldvalues .= "'".bqSQL($elem)."',";
		}

		$fieldvalues = substr($fieldvalues, 0, -1);
		$fieldnames = substr($fieldnames, 0, -1);

		$sql = "INSERT INTO `"._DB_PREFIX_.self::SCEAU_ORDER_TABLE_NAME."` (".$fieldnames.") VALUES (".$fieldvalues.")";

		$inserted = Db::getInstance()->execute($sql);
		if (!$inserted)
		{
			SceauLogger::insertLogSceau(__METHOD__." : ".__LINE__, "Insertion $id_order failed : ".Db::getInstance()->getMsgError());
			SceauLogger::insertLogSceau(__METHOD__." : ".__LINE__, "Insertion $id_order failed : ".$sql);
			return false;
		}

		return true;
	}

	/**
	 * update fianetsceau_order table with fields in parameters
	 * 
	 * @param int $id_order
	 * @param array $fields
	 * @return boolean 
	 */
	private function updateOrder($id_order, array $fields)
	{
		$set_string = "";
		foreach ($fields as $fieldname => $fieldvalue)
		{
			$set_string .= "`".bqSQL ($fieldname)."` = '".bqSQL ($fieldvalue)."', ";
		}
		$set_string = substr($set_string, 0, '-2');

		$sql = "UPDATE `"._DB_PREFIX_.self::SCEAU_ORDER_TABLE_NAME."` SET ".$set_string." WHERE `id_order` = '".(int) $id_order."'";
		
		SceauLogger::insertLogSceau(__METHOD__." : ".__LINE__, $sql);
		
		$updated = Db::getInstance()->execute($sql);
		if (!$updated)
		{
			SceauLogger::insertLogSceau(__METHOD__." : ".__LINE__, "Update $id_order failed : ".Db::getInstance()->getMsgError());
			return false;
		}
		return true;
	}

	/**
	 * returns the IP address of the customer who passed the order $id_order
	 *
	 * @param int $id_order
	 * @return string
	 */
	private function getCustomerIP($id_order)
	{
		$sql = "SELECT `customer_ip_address` FROM `"._DB_PREFIX_.self::SCEAU_ORDER_TABLE_NAME."` WHERE `id_order` = '".(int) $id_order."'";
		$query_result = Db::getInstance()->getRow($sql);
		return($query_result['customer_ip_address']);
	}

	//
	/**
	 * return Fianet payment type used for payment
	 *
	 * @param int $id_order
	 * @return int
	 */
	private function getPaymentFianetType($id_order)
	{
		$order = new Order($id_order);
		$payments = $this->loadPaymentMethods();
		foreach ($payments as $element)
			if ($order->module == $element['name'])
				return ($element['fianetsceau_type']);
	}

	/**
	 * build xml with customer's and order's data et send it to FIA-NET
	 * 
	 * @param int $id_order
	 * @return boolean 
	 */
	public function sendXML($id_order)
	{
		$order = new Order($id_order);
		$montant = $order->total_paid;
		$date = $order->date_add;

		$customer = new Customer($order->id_customer);
		$lastname = $customer->lastname;
		$firstname = $customer->firstname;
		$id_currency = $order->id_currency;
		$currency = new Currency($id_currency);

		if (_PS_VERSION_ < '1.5')
		{
			$civility = $customer->id_gender;

			$accepted_civility = array("1", "2", "3");
			if (!(in_array($civility, $accepted_civility)))
				$civility = '1';
		}
		else
		{
			$gender = new Gender($customer->id_gender);
			$id_lang = Language::getIdByIso('en');
			$civility = $gender->name[$id_lang] == 'Mr.' ? 1 : 2;
		}

		//xml construction
		if (_PS_VERSION_ < '1.5')
			$fianetsceau = new Sceau();
		else
			$fianetsceau = new Sceau((int) $order->id_shop);

		$utilisateur = new SceauXMLElement('<utilisateur></utilisateur>');
		$nom = $utilisateur->childNom($customer->lastname);
		$nom->addAttribute('titre', $civility);
		$utilisateur->childPrenom($customer->firstname);
		$utilisateur->childEmail(strtolower($customer->email));

		$infocommande = new SceauXMLElement('<infocommande></infocommande>');
		$infocommande->childSiteid($fianetsceau->getSiteid());
		$infocommande->childRefid($id_order);
		$montant = $infocommande->childMontant($order->total_paid);
		$montant->addAttribute('devise', $currency->iso_code);
		$ip = new SceauXMLElement('<ip>'.$this->getCustomerIP($id_order).'</ip>');
		$ip->addAttribute('timestamp', $order->date_add);
		$infocommande->childIp($ip);

		$paiement = new SceauXMLElement('<paiement></paiement>');
		$paiement->childType($this->getPaymentFianetType($id_order));

		$lang = Language::getIsoById($order->id_lang);
		$langue = $infocommande->childLangue($lang);

		$xml_order = new SceauControl();
		$xml_order->childUtilisateur($utilisateur);
		$xml_order->childInfocommande($infocommande);
		$xml_order->childPaiement($paiement);

		$result = $fianetsceau->sendSendrating($xml_order);
		if (!($result === false))
		{
			$resxml = new SceauXMLElement($result);
			if ($resxml->getAttribute('type') != "OK")
			{
				//update fianetsceau_state 3:error
				$this->updateOrder($id_order, array('id_fianetsceau_state' => '3'));
				SceauLogger::insertLogSceau(__METHOD__." : ".__LINE__, 'Order '.$id_order.' XML Send error : '.$resxml->getChildByName('detail')->getValue());
				return false;
			}
			else
			//update fianetsceau_state 2:sent
				$this->updateOrder($id_order, array('id_fianetsceau_state' => '2'));
			return true;
		}
		else
		{
			//update fianetsceau_state 3:error
			$this->updateOrder($id_order, array('id_fianetsceau_state' => '3'));
			SceauLogger::insertLogSceau(__METHOD__." : ".__LINE__, 'Order '.$id_order.' XML Send error : '.$resxml->getChildByName('detail')->getValue());
			return false;
		}
	}

	/**
	 * Show FIA-NET Sceau status on detail order if FIANETSCEAU_SHOW_STATUS_ORDER is enabled
	 * 
	 * 
	 * @param type $params
	 * @return type 
	 */
	public function hookAdminOrder($params)
	{
		//check if show status option is enabled in module settings
		if (!Configuration::get('FIANETSCEAU_SHOW_STATUS_ORDER'))
			return false;

		$id_order = $params['id_order'];

		//retrieve order's status label
		$sql = "
			SELECT fs.`label` 
			FROM `"._DB_PREFIX_.self::SCEAU_STATE_TABLE_NAME."` fs 
				INNER JOIN `"._DB_PREFIX_.self::SCEAU_ORDER_TABLE_NAME."` fo 
				ON fo.`id_fianetsceau_state`=fs.`id_fianetsceau_state` 
			WHERE  fo.`id_order`='".(int) $id_order."'";

		$order_state_label = Db::getInstance()->getValue($sql);

		//if label exist, we load the corresponding tpl
		if (!($order_state_label === false))
		{
			$tpl_name = preg_replace('#[^a-zA-Z0-9]#', '_', $order_state_label);
			$base_url = __PS_BASE_URI__;

			if ($tpl_name == 'sent')
				$img = 'sent.gif';
			elseif ($tpl_name == 'waiting_payment')
				$img = 'waiting.gif';
			elseif ($tpl_name == 'error')
				$img = 'not_concerned.png';

			$link = "index.php?tab=AdminSceau&action=ResendOrder&id_order=".(int) $id_order."&token=".Tools::getAdminTokenLite('AdminSceau');

			$this->smarty->assign(array(
				'fianetsceau_img' => __PS_BASE_URI__.'modules/'.$this->name.'/img/'.$img,
				'link' => $link,
				'resend_img' => __PS_BASE_URI__.'modules/'.$this->name.'/img/sceauresend14.png',
				'logo_img' => __PS_BASE_URI__.'modules/'.$this->name.'/img/logo.gif',
			));

			return $this->display(__FILE__, '/views/templates/admin/'.$tpl_name.'.tpl');
		} else
		{
			//if label doesn't exist, we load nosend.tpl, order was sent before FIA-NET Sceau installation
			$img = 'error.gif';
			$this->smarty->assign(array(
				'fianetsceau_img' => __PS_BASE_URI__.'modules/'.$this->name.'/img/'.$img,
				'logo_img' => __PS_BASE_URI__.'modules/'.$this->name.'/img/logo.gif',
			));


			return $this->display(__FILE__, '/views/templates/admin/nosend.tpl');
		}
	}

	/**
	 * Insert a new order on id_fianetsceau_state table when a new order arrives
	 * 
	 * @param type Array 
	 */
	public function hookNewOrder($params)
	{
		//insert data into id_fianetsceau_order when new order arrives
		$order = $params['order'];
		$this->insertOrder((int) $order->id, array('id_order' => (int) $order->id, 'id_fianetsceau_state' => '1', 'customer_ip_address' => Tools::getRemoteAddr(), 'date' => $order->date_add));
	}

	/**
	 * Send xml data to FIA-NET when payment's order was confirmed
	 * 
	 * @param type Array 
	 */
	public function hookpaymentConfirm($params)
	{
		//send xml to FIA-NET when payment is confirmed
		$id_order = $params['id_order'];
		$this->sendXML((int) $id_order);
	}

	/**
	 * Load logo or/and widget tpl with top position
	 * 
	 * @return hmtl 
	 */
	public function hookTop()
	{
		$html = '';
		if (Configuration::get('FIANETSCEAU_LOGO_POSITION') == 'top')
			$html = $this->loadLogoTPL();
		if (Configuration::get('FIANETSCEAU_WIDGET_POSITION') == 'top')
			$html .= $this->loadWidgetTPL();

		return $html == '' ? false : $html;
	}

	/**
	 * Load logo or/and widget tpl with bottom position
	 * 
	 * @return html 
	 */
	public function hookFooter()
	{
		$html = '';
		if (Configuration::get('FIANETSCEAU_LOGO_POSITION') == 'bottom')
			$html = $this->loadLogoTPL();
		if (Configuration::get('FIANETSCEAU_WIDGET_POSITION') == 'bottom')
			$html .= $this->loadWidgetTPL();

		return $html == '' ? false : $html;
	}

	/**
	 * Load logo or/and widget tpl with left position
	 * 
	 * @return html 
	 */
	public function hookLeftColumn()
	{
		$html = '';
		if (Configuration::get('FIANETSCEAU_LOGO_POSITION') == 'left')
			$html = $this->loadLogoTPL();
		if (Configuration::get('FIANETSCEAU_WIDGET_POSITION') == 'left')
			$html .= $this->loadWidgetTPL();

		return $html == '' ? false : $html;
	}

	/**
	 * Load logo or/and widget tpl with right position
	 * 
	 * @return html 
	 */
	public function hookRightColumn()
	{
		$html = '';
		if (Configuration::get('FIANETSCEAU_LOGO_POSITION') == 'right')
			$html = $this->loadLogoTPL();
		if (Configuration::get('FIANETSCEAU_WIDGET_POSITION') == 'right')
			$html .= $this->loadWidgetTPL();

		return $html == '' ? false : $html;
	}

	/**
	 * load front-office logo's template
	 * 
	 * @return type 
	 */
	private function loadLogoTPL()
	{
		//retrieve logo size on database and load the corresponding image
		$this->smarty->assign(array(
			'siteid' => Configuration::get('FIANETSCEAU_SITEID'),
			'fianetsceau_img' => __PS_BASE_URI__.'modules/'.$this->name.'/img/'.$this->_fianetsceau_logo_sizes[Configuration::get('FIANETSCEAU_LOGO_SIZE')]
		));

		return $this->display(__FILE__, '/views/templates/front/fianetsceau.tpl');
	}

	/**
	 * load front-office widget's template
	 *
	 * @return type 
	 */
	private function loadWidgetTPL()
	{

		//retrieve widget number on database and load the corresponding widget
		$this->smarty->assign(array(
			'siteid' => Configuration::get('FIANETSCEAU_SITEID'),
			'shape' => $this->_fianetsceau_widgets[Configuration::get('FIANETSCEAU_WIDGET_NUMBER')]['shape'],
			'background' => $this->_fianetsceau_widgets[Configuration::get('FIANETSCEAU_WIDGET_NUMBER')]['background'],
		));

		if ($this->_fianetsceau_widgets[Configuration::get('FIANETSCEAU_WIDGET_NUMBER')]['shape'] == 'comment')
			return $this->display(__FILE__, '/views/templates/front/fianetsceau_widget_comments.tpl');
		else
			return $this->display(__FILE__, '/views/templates/front/fianetsceau_widget.tpl');
	}

	/**
	 * save all admin settings on database
	 *
	 * @return boolean 
	 */
	protected function processForm()
	{
		if (!$this->formIsValid())
			return false;

		//update configuration
		Configuration::updateValue('FIANETSCEAU_LOGIN', Tools::getValue('fianetsceau_login'));
		Configuration::updateValue('FIANETSCEAU_PASSWORD', Tools::getValue('fianetsceau_password'));
		Configuration::updateValue('FIANETSCEAU_SITEID', Tools::getValue('fianetsceau_siteid'));
		Configuration::updateValue('FIANETSCEAU_AUTHKEY', Tools::getValue('fianetsceau_authkey'));
		Configuration::updateValue('FIANETSCEAU_STATUS', Tools::getValue('fianetsceau_status'));
		Configuration::updateValue('FIANETSCEAU_LOGO_POSITION', Tools::getValue('fianetsceau_logo_position'));
		Configuration::updateValue('FIANETSCEAU_LOGO_SIZE', Tools::getValue('fianetsceau_logo_sizes'));
		Configuration::updateValue('FIANETSCEAU_WIDGET_POSITION', Tools::getValue('fianetsceau_widget_position'));
		Configuration::updateValue('FIANETSCEAU_WIDGET_NUMBER', Tools::getValue('fianetsceau_widget_number'));
		Configuration::updateValue('FIANETSCEAU_SHOW_STATUS_ORDER', ((int) Tools::getValue('fianetsceau_showstatus') == 1 ? '1' : '0'));

		/** update payment means settings * */
		//list of payment means of the shop
		$payment_modules = $this->loadPaymentMethods();
		foreach (array_keys($payment_modules) as $id)
			Configuration::updateValue('FIANETSCEAU_'.$id.'_PAYMENT_TYPE', Tools::getValue('fianetsceau_'.$id.'_payment_type'));

		SceauLogger::insertLogSceau(__METHOD__." : ".__LINE__, "Configuration module mise à jour");
		return true;
	}

	/**
	 * returns true if the form is valid, false otherwise
	 * 
	 * @return boolean
	 */
	private function formIsValid()
	{

		$iso_lang_current = Language::getIsoById($this->context->language->id);

		//check login
		if (strlen(Tools::getValue('fianetsceau_login')) < 1)
			$this->_errors[] = $this->l("Login can't be empty");

		//check password
		if (strlen(Tools::getValue('fianetsceau_password')) < 1)
			$this->_errors[] = $this->l("Password can't be empty");

		//check site ID
		if (strlen(Tools::getValue('fianetsceau_siteid')) < 1)
			$this->_errors[] = $this->l("Siteid can't be empty");

		if (!preg_match('#^[0-9]+$#', Tools::getValue('fianetsceau_siteid')))
			$this->_errors[] = $this->l("Siteid has to be integer. ".gettype(Tools::getValue('fianetsceau_siteid'))." given.");

		//check authkey
		if (strlen(Tools::getValue('fianetsceau_authkey')) < 1)
			$this->_errors[] = $this->l("Authkey can't be empty");

		//check status
		if (!in_array(Tools::getValue('fianetsceau_status'), $this->_fianetsceau_statuses))
			$this->_errors[] = $this->l("You must give a correct status.");

		//check logo and widget position
		if (Tools::getValue('fianetsceau_logo_position') == "nothing" && Tools::getValue('fianetsceau_widget_position') == "nothing")
			$this->_errors[] = $this->l("You must select a logo or a widget position");

		//payment means check
		$shop_payments = $this->loadPaymentMethods();
		foreach ($shop_payments as $id => $shop_payment)
		{
			if (!in_array(Tools::getValue('fianetsceau_'.$id.'_payment_type'), array_keys($this->_payment_types)))
				$this->_errors[] = $this->l("The payment type for '".$shop_payment['name']."' is not valid");
		}

		//check sceau show status
		if (!in_array(Tools::getValue('fianetsceau_showstatus'), array('0', '1')))
			$this->_errors[] = $this->l('You must give a correct FIA-NET show status value');

		//check logo position		
		if (!in_array(Tools::getValue('fianetsceau_logo_position'), array_keys($this->_fianetsceau_positions[$iso_lang_current])))
			$this->_errors[] = $this->l('You must give a correct logo position');

		//check widget position
		if (!in_array(Tools::getValue('fianetsceau_widget_position'), array_keys($this->_fianetsceau_positions[$iso_lang_current])))
			$this->_errors[] = $this->l('You must give a correct widget position');

		//check logo size
		if (!in_array(Tools::getValue('fianetsceau_logo_sizes'), array_keys($this->_fianetsceau_logo_sizes)))
			$this->_errors[] = $this->l("You must give a correct logo size");

		//check widget type
		if (!in_array(Tools::getValue('fianetsceau_widget_number'), array_keys($this->_fianetsceau_widgets)))
			$this->_errors[] = $this->l("You must give a correct widget type");

		return empty($this->_errors);
	}

	public function getContent()
	{
		$head_msg = '';
		//if some POST datas are found
		//Get log file
		$log_content = htmlentities(SceauLogger::getLogContent());

		if (Tools::isSubmit('submitSettings'))
		{
			//if the form is correctly saved
			if ($this->processForm())
			//adds a confirmation message
				$head_msg = $this->displayConfirmation($this->l('Configuration updated.'));
			else
			{ //if errors have been encountered while validating the form
				//add an error message informing about errors that have been encountered
				$error_msg = $this->l('Some errors have been encoutered while updating configuration.');
				$error_msg .= '<ul>';
				foreach ($this->_errors as $error_label)
					$error_msg .= '<li>'.$error_label.'</li>';

				$error_msg .= '</ul>';
				$head_msg = $this->displayError($error_msg);
			}
			//if submit form, we save data form
			$login = (Tools::isSubmit('fianetsceau_login') ? Tools::getValue('fianetsceau_login') : $fianetsceau->getLogin());
			$password = (Tools::isSubmit('fianetsceau_password') ? Tools::getValue('fianetsceau_password') : $fianetsceau->getPassword());
			$siteid = (Tools::isSubmit('fianetsceau_siteid') ? Tools::getValue('fianetsceau_siteid') : $fianetsceau->getSiteid());
			$authkey = (Tools::isSubmit('fianetsceau_authkey') ? Tools::getValue('fianetsceau_authkey') : $fianetsceau->getAuthkey());
			$status = (Tools::isSubmit('fianetsceau_status') ? Tools::getValue('fianetsceau_status') : $fianetsceau->getStatus());

			$fianetsceau_logo_position = Tools::getValue('fianetsceau_logo_position');
			$fianetsceau_widget_position = Tools::getValue('fianetsceau_widget_position');
			$fianetsceau_logo = Tools::getValue('fianetsceau_logo_sizes');
			$widget_number = Tools::getValue('fianetsceau_widget_number');
			$show_status = Tools::getValue('fianetsceau_showstatus');
		} else
		{
			//if no submit form and 0 value in database, we put a defaut value
			$login = (Configuration::get('FIANETSCEAU_LOGIN') === false ? '' : Configuration::get('FIANETSCEAU_LOGIN'));
			$password = (Configuration::get('FIANETSCEAU_PASSWORD') === false ? '' : Configuration::get('FIANETSCEAU_PASSWORD'));
			$siteid = (Configuration::get('FIANETSCEAU_SITEID') === false ? '' : Configuration::get('FIANETSCEAU_SITEID'));
			$authkey = (Configuration::get('FIANETSCEAU_AUTHKEY') === false ? '' : Configuration::get('FIANETSCEAU_AUTHKEY'));
			$status = (Configuration::get('FIANETSCEAU_STATUS') === false ? 'test' : Configuration::get('FIANETSCEAU_STATUS'));

			$fianetsceau_logo_position = (Configuration::get('FIANETSCEAU_LOGO_POSITION') === false ? 'left' : Configuration::get('FIANETSCEAU_LOGO_POSITION'));
			$fianetsceau_widget_position = (Configuration::get('FIANETSCEAU_WIDGET_POSITION') === false ? 'left' : Configuration::get('FIANETSCEAU_WIDGET_POSITION'));
			$fianetsceau_logo = (Configuration::get('FIANETSCEAU_LOGO_SIZE') === false ? '120' : Configuration::get('FIANETSCEAU_LOGO_SIZE'));
			$widget_number = (Configuration::get('FIANETSCEAU_WIDGET_NUMBER') === false ? '1' : Configuration::get('FIANETSCEAU_WIDGET_NUMBER'));
			$show_status = (Configuration::get('FIANETSCEAU_SHOW_STATUS_ORDER') === false ? '0' : Configuration::get('FIANETSCEAU_SHOW_STATUS_ORDER'));
		}

		$fianetsceau = new Sceau();

		//listing payment type of the shop
		$payment_modules = $this->loadPaymentMethods();
		$base_url = __PS_BASE_URI__;
		$logo_sizes = array();
		foreach ($this->_fianetsceau_logo_sizes as $size => $img_name)
			$logo_sizes[$size] = $base_url.'modules/'.$this->name.'/img/'.$img_name;

		$iso_lang_current = $this->context->language->iso_code;

		//give smarty data to the view
		$this->smarty->assign(array(
			'head_message' => $head_msg,
			'image_path' => $base_url.'modules/'.$this->name.'/img/sceaudeconfiance.png',
			'fianetsceau_login' => Tools::safeOutput($login),
			'fianetsceau_password' => Tools::safeOutput($password),
			'fianetsceau_siteid' => Tools::safeOutput($siteid),
			'fianetsceau_authkey' => Tools::safeOutput($authkey),
			'fianetsceau_status' => Tools::safeOutput($status),
			'fianetsceau_statuses' => $this->_fianetsceau_statuses,
			'payment_modules' => $payment_modules,
			'fianetsceau_payment_types' => $this->_payment_types,
			'logo_account_path' => $base_url.'modules/'.$this->name.'/img/account.gif',
			'logo_payments_path' => $base_url.'modules/'.$this->name.'/img/payments.gif',
			'fianetsceau_logo_positions' => $this->_fianetsceau_positions[$iso_lang_current],
			'fianetsceau_logo_position' => Tools::safeOutput($fianetsceau_logo_position),
			'fianetsceau_logo_sizes' => $logo_sizes,
			'fianetsceau_logo' => (int) $fianetsceau_logo,
			'fianetsceau_widget_positions' => $this->_fianetsceau_positions[$iso_lang_current],
			'fianetsceau_widget_position' => Tools::safeOutput($fianetsceau_widget_position),
			'fianetsceau_widget_numbers' => array_keys($this->_fianetsceau_widgets),
			'widget_number' => (int) $widget_number,
			'path_prefix' => $base_url.'modules/'.$this->name.'/img',
			'fianetsceaushow_status' => (int) $show_status,
			'log_content' => $log_content,
		));

		//view selection by prestashop 1.4 or 1.5

		return $this->display(__FILE__, '/views/templates/admin/admin.tpl');
	}

	/**
	 * returns payment means of the shop in an array which have module ID in indice with module's name and FIA-NET payment type. 
	 * 
	 * @return array
	 */
	private function loadPaymentMethods()
	{
		if (_PS_VERSION_ >= '1.5')
			$payments = Module::getPaymentModules();
		else
			$payments = PaymentModuleCore::getInstalledPaymentModules();

		$payment_modules = array();

		foreach ($payments as $payment)
		{
			$module = Module::getInstanceById($payment['id_module']);
			$payment_modules[$payment['id_module']] = array(
				'name' => $module->displayName,
				'fianetsceau_type' => Configuration::get('FIANETSCEAU_'.$payment['id_module'].'_PAYMENT_TYPE'),
			);
		}
		return $payment_modules;
	}

}