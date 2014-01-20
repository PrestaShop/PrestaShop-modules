<?php
/*
*  @author Coccinet <web@coccinet.com>
*  @copyright  2007-2013 Coccinet
*/
if (!defined('_PS_VERSION_'))
	exit;

class RealexRedirect extends PaymentModule
{
	private $html = '';
	private $post_errors = array();
	private $edit_account;
	public $merchant_id;
	public $shared_secret;
	public $settlement;
	public $realvault;
	public $cvn;
	public $url_validation;
	public $bout_valide;
	public $bout_suppr;
	public $liability;

	/**
	* Construct
	*/

	public function __construct()
	{
		$this->name = 'realexredirect';
		$this->tab = 'payments_gateways';
		$this->version = '1.3';
		$this->author = 'Coccinet';
		$this->bout_valide = $this->l('Validate');
		$this->bout_suppr = $this->l('Do you want to delete your stored card ?');
		$this->currencies = true;
		$this->currencies_mode = 'checkbox';

		$config = Configuration::getMultiple(array('REALEX_REDIRECT_MERCHANT_ID',
		'REALEX_REDIRECT_SHARED_SECRET',
		'REALEX_REDIRECT_SETTLEMENT',
		'REALEX_REDIRECT_SUBACCOUNT',
		'REALEX_REDIRECT_REALVAULT',
		'REALEX_REDIRECT_CVN',
		'REALEX_REDIRECT_LIABILITY'));
		if (isset($config['REALEX_REDIRECT_MERCHANT_ID']))
			$this->merchant_id = $config['REALEX_REDIRECT_MERCHANT_ID'];
		if (isset($config['REALEX_REDIRECT_SHARED_SECRET']))
			$this->shared_secret = $config['REALEX_REDIRECT_SHARED_SECRET'];
		if (isset($config['REALEX_REDIRECT_SETTLEMENT']))
			$this->settlement = $config['REALEX_REDIRECT_SETTLEMENT'];
		if (isset($config['REALEX_REDIRECT_REALVAULT']))
			$this->realvault = $config['REALEX_REDIRECT_REALVAULT'];
		if (isset($config['REALEX_REDIRECT_CVN']))
			$this->cvn = $config['REALEX_REDIRECT_CVN'];
		if (isset($config['REALEX_REDIRECT_LIABILITY']))
			$this->liability = $config['REALEX_REDIRECT_LIABILITY'];

		parent::__construct();

		if (Configuration::get('PS_SSL_ENABLED'))
			$this->url_validation = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'module/'.$this->name.'/validation';
		else
			$this->url_validation = Tools::getShopDomain(true, true).__PS_BASE_URI__.'module/'.$this->name.'/validation';
		$this->displayName = $this->l('Realex Payments');
		$this->description = $this->l('Use Realex Payments as your payments service provider.');
		$this->confirmUninstall = $this->l('Are you sure you want to delete this information?');
		if (!function_exists('curl_version'))
			$this->warning = $this->l('cURL librairy is not available.');
		elseif (!Configuration::get('PS_REWRITING_SETTINGS'))
			$this->warning = $this->l('URL Rewriting must be enabled before using this module.');
		elseif (!isset($this->merchant_id)
				|| empty($this->shared_secret)
				|| !isset($this->settlement)
				|| !isset($this->realvault)
				|| !isset($this->cvn)
				|| !isset($this->liability))
			$this->warning = $this->l('Realex Payment details must be configured before using this module.');
		elseif (!$this->getTableAccount())
			$this->warning = $this->l('You have to configure at least one subaccount');
		if (!count(Currency::checkPaymentCurrencies($this->id)))
			$this->warning = $this->l('No currency has been set for this module.');

		/** Backward compatibility */
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
	}
	/**
	* Install
	* Return bolean
	*/
	public function install()
	{
		if (!parent::install() || !$this->registerHook('payment') || !$this->registerHook('paymentReturn') || !$this->registerHook('header')
			|| !Db::getInstance()->Execute(
			'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'realex_payerref` (
				`id_realex_payerref` INT(10) NOT NULL AUTO_INCREMENT,
				`id_user_realex` INT(10) NULL DEFAULT NULL,
				`refuser_realex` VARCHAR(50) NULL DEFAULT NULL,
				`date_add` DATETIME NULL DEFAULT NULL,
				PRIMARY KEY (`id_realex_payerref`)
			)
			COLLATE="utf8_general_ci"
			ENGINE='._MYSQL_ENGINE_.';')
			|| !Db::getInstance()->Execute(
			'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'realex_paymentref` (
				`id_realex_paymentref` INT(10) NOT NULL AUTO_INCREMENT,
				`id_realex_payerref` INT(10) NULL DEFAULT NULL,
				`refpayment_realex` VARCHAR(50) NULL DEFAULT NULL,
				`paymentname_realex` VARCHAR(128) NULL DEFAULT NULL,
				`type_card_realex` VARCHAR(128) NULL DEFAULT NULL,
				`date_add` DATETIME NULL DEFAULT NULL,
				PRIMARY KEY (`id_realex_paymentref`)
			)
			COLLATE="utf8_general_ci"
			ENGINE='._MYSQL_ENGINE_.';')
			|| !Db::getInstance()->Execute(
			'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'realex_subaccount` (
				`id_realex_subaccount` INT(10) NULL AUTO_INCREMENT,
				`name_realex_subaccount` VARCHAR(50) NULL DEFAULT NULL,
				`threeds_realex_subaccount` INT(1) NULL DEFAULT "0",
				`dcc_realex_subaccount` INT(1) NULL DEFAULT "0",
				`dcc_choice_realex_subaccount` VARCHAR(50) NULL DEFAULT NULL,
				PRIMARY KEY (`id_realex_subaccount`)
			)
			COLLATE="utf8_general_ci"
			ENGINE='._MYSQL_ENGINE_.';')
			|| !Db::getInstance()->Execute(
			'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'realex_rel_card` (
				`id_realex_rel_card` INT(10) NOT NULL AUTO_INCREMENT,
				`id_realex_subaccount` INT(10) NOT NULL DEFAULT "0",
				`realex_card_name` VARCHAR(50) NOT NULL DEFAULT "0",
				PRIMARY KEY (`id_realex_rel_card`)
			)
			COLLATE="utf8_general_ci"
			ENGINE='._MYSQL_ENGINE_.';')
			|| !Configuration::updateValue('REALEX_REDIRECT_SETTLEMENT', 'auto')
			|| !Configuration::updateValue('REALEX_REDIRECT_REALVAULT', '0')
			|| !Configuration::updateValue('REALEX_REDIRECT_CVN', '0')
			|| !Configuration::updateValue('REALEX_REDIRECT_LIABILITY', '0'))
			return false;
		return true;
	}

	/**
	* Uninstall
	* Return bolean
	*/
	public function uninstall()
	{
		if (!Configuration::deleteByName('REALEX_REDIRECT_MERCHANT_ID')
				|| !Configuration::deleteByName('REALEX_REDIRECT_SHARED_SECRET')
				|| !Configuration::deleteByName('REALEX_REDIRECT_SETTLEMENT')
				|| !Configuration::deleteByName('REALEX_REDIRECT_SUBACCOUNT')
				|| !Configuration::deleteByName('REALEX_REDIRECT_REALVAULT')
				|| !Configuration::deleteByName('REALEX_REDIRECT_CVN')
				|| !Configuration::deleteByName('REALEX_REDIRECT_LIABILITY')
				|| !Configuration::deleteByName('REALEX_REDIRECT_NB_SUBACCOUNT')
				|| !Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'realex_payerref`')
				|| !Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'realex_paymentref`')
				|| !Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'realex_subaccount`')
				|| !Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'realex_rel_card`')
				|| !parent::uninstall())
			return false;
		return true;
	}

	/**
	* Module configuration post validation
	* Check errors on data submitted
	* @return string
	*/
	private function postValidation()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			$val_realvault = Tools::getValue('realvault');
			$val_cvn = Tools::getValue('cvn');
			$val_liability = Tools::getValue('cvn');
			if (!Configuration::get('PS_REWRITING_SETTINGS'))
				$this->post_errors[] = $this->l('URL Rewriting must be enabled before using this module.');
			elseif (!function_exists('curl_version'))
				$this->post_errors[] = $this->l('cURL librairy is not available.');
			elseif (!Tools::getValue('merchantId'))
				$this->post_errors[] = $this->l('Merchant ID is required.');
			elseif (!Tools::getValue('sharedSecret'))
				$this->post_errors[] = $this->l('Shared secret is required.');
			elseif (!Tools::getValue('settlement'))
				$this->post_errors[] = $this->l('Settlement type is required');
			elseif (!isset($val_realvault))
				$this->post_errors[] = $this->l('You must select whether you wish to use RealVault or not');
			elseif (!isset($val_liability))
				$this->post_errors[] = $this->l('You must indicate your Liability Shift expectation');
		}
		elseif (Tools::isSubmit('btnSubmitAccount') || Tools::isSubmit('btnSubmitEditAccount'))
		{
			if (!Tools::getValue('id_realex_subaccount'))
				$sql = 'SELECT COUNT(*) FROM '._DB_PREFIX_.'realex_subaccount WHERE name_realex_subaccount = "'.pSQL(Tools::getValue('subAccount')).'"';
			else
			{
				$sql = 'SELECT COUNT(*) FROM '._DB_PREFIX_.'realex_subaccount WHERE ';
				$sql .= 'name_realex_subaccount = "'.pSQL(Tools::getValue('subAccount')).'" AND id_realex_subaccount <> '.(int)Tools::getValue('id_realex_subaccount');
			}
			$name_exists = Db::getInstance()->getValue($sql);
			if (!Tools::getValue('subAccount'))
				$this->post_errors[] = $this->l('Subaccount name is required');
			elseif ($name_exists)
				$this->post_errors[] = $this->l('This Subaccount name is already being used');
			elseif (!Tools::getValue('type_card'))
				$this->post_errors[] = $this->l('Card type is required');
			else
			{
				foreach (Tools::getValue('type_card') as $card_name)
				{
					if (!Tools::getValue('id_realex_subaccount'))
						$sql2 = 'SELECT COUNT(*) FROM '._DB_PREFIX_.'realex_rel_card WHERE realex_card_name="'.pSQL($card_name).'"';
					else
					{
						$sql2 = 'SELECT COUNT(*) FROM '._DB_PREFIX_.'realex_rel_card';
						$sql2 .= ' WHERE realex_card_name="'.pSQL($card_name).'" AND id_realex_subaccount <> '.(int)Tools::getValue('id_realex_subaccount');
					}
					$card_exists = Db::getInstance()->getValue($sql2);
					if ($card_exists)
						$this->post_errors[] = $this->l('You have already added a subaccount to process this card').': '.$card_name;
				}
			}
		}
	}

	/**
	* Module configuration post action
	* Update datas on database
	* @return string
	*/
	private function postProcess()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			Configuration::updateValue('REALEX_REDIRECT_MERCHANT_ID', Tools::getValue('merchantId'));
			Configuration::updateValue('REALEX_REDIRECT_SHARED_SECRET', Tools::getValue('sharedSecret'));
			Configuration::updateValue('REALEX_REDIRECT_SETTLEMENT', Tools::getValue('settlement'));
			Configuration::updateValue('REALEX_REDIRECT_SUBACCOUNT', Tools::getValue('subAccount'));
			Configuration::updateValue('REALEX_REDIRECT_REALVAULT', Tools::getValue('realvault'));
			Configuration::updateValue('REALEX_REDIRECT_CVN', Tools::getValue('cvn'));
			Configuration::updateValue('REALEX_REDIRECT_LIABILITY', Tools::getValue('liability'));
		}
		elseif (Tools::isSubmit('btnSubmitAccount'))
		{
			if (Db::getInstance()->insert('realex_subaccount',
			array(
			'name_realex_subaccount' => pSQL(Tools::getValue('subAccount')),
			'threeds_realex_subaccount' => (int)Tools::getValue('threeds'),
			'dcc_realex_subaccount' => (int)Tools::getValue('dcc'),
			'dcc_choice_realex_subaccount' => pSQL(Tools::getValue('dcc_choice'))
			)))
			{
				$id_subaccount = Db::getInstance()->Insert_ID();
				foreach (Tools::getValue('type_card') as $card)
				{
					Db::getInstance()->insert('realex_rel_card',
					array(
					'id_realex_subaccount' => (int)$id_subaccount,
					'realex_card_name' => pSQL($card)
					));
				}
			}
			$this->html .= '<div class="conf confirm"> '.$this->l('Settings updated').'</div>';
		}
		elseif (Tools::isSubmit('delete_realex_account'))
		{
			Db::getInstance()->delete('realex_subaccount', 'id_realex_subaccount='.(int)Tools::getValue('id_subaccount'));
			Db::getInstance()->delete('realex_rel_card', 'id_realex_subaccount="'.(int)Tools::getValue('id_subaccount').'"');
			$this->html .= '<div class="conf confirm"> '.$this->l('Settings updated').'</div>';
		}
		elseif (Tools::isSubmit('edit_realex_account'))
			$this->displayEditForm((int)Tools::getValue('id_subaccount'));
		elseif (Tools::isSubmit('btnSubmitEditAccount'))
		{
			$id_subaccount = (int)Tools::getValue('id_realex_subaccount');
			$data = array('name_realex_subaccount'=>pSQL(Tools::getValue('subAccount')),
				'threeds_realex_subaccount'=>pSQL(Tools::getValue('threeds')),
				'dcc_realex_subaccount'=>pSQL(Tools::getValue('dcc')),
				'dcc_choice_realex_subaccount'=>pSQL(Tools::getValue('dcc_choice'))
			);
			if (Db::getInstance()->update('realex_subaccount', $data, 'id_realex_subaccount='.(int)$id_subaccount))
			{
				if (Db::getInstance()->delete('realex_rel_card', 'id_realex_subaccount = '.(int)$id_subaccount))
				{
					foreach (Tools::getValue('type_card') as $card)
					{
						Db::getInstance()->insert('realex_rel_card',
						array(
						'id_realex_subaccount' => (int)$id_subaccount,
						'realex_card_name' => pSQL($card)
						));
					}
				}
				$this->html .= '<div class="conf confirm"> '.$this->l('Settings updated').'</div>';
			}
		}
	}

	/**
	* Display payment information on top of the module
	* @return string
	*/
	private function displayRealex()
	{
		$this->html .= "<div style='margin:0px 0px 30px;border: 1px solid #394049;padding:20px; background:#F8F8F8'>";
		$this->html .= "<img src='".$this->_path.'img/realexredirect.jpg'."' style='float:left; margin-right:15px;'><strong><br/>";
		$this->html .= $this->l('Thanks for installing the Realex Payments Prestashop module.');
		$this->html .= '<br/>';
		$this->html .= $this->l('This module allows you to accept payments via the Realex \'Redirect\' payment method.').'</strong>';
		$this->html .= '<br/><br/><br/>';
		$this->html .= $this->l('If you don\'t already have a Realex account, you may apply for one on our website or call us on 020 3178 5370:');
		$this->html .= '<br/>';
		$this->html .= "<a href='http://www.realexpayments.co.uk/business-offering' target='_blank'>";
		$this->html .= 'http://www.realexpayments.co.uk/business-offering</a><br/><br/>';
		$this->html .= $this->l('If you have a Realex account, please contact your Realex support representative to obtain the credentials necessary to use this plugin.');
		$this->html .= '<br/><br/>';
		$this->html .= $this->l('They will also require the Request and Response URLs which can be found on the module configuration page.');
		$this->html .= '<br/><br/>';
		$this->html .= $this->l('Please also inform Realex if you wish to use any of the following services available with the module: ');
		$this->html .= '<br/>';
		$this->html .= '- '.$this->l('Dynamic Currency Conversion (DCC)').'<br/>';
		$this->html .= '- '.$this->l('RealVault').'<br/>';
		$this->html .= '- '.$this->l('3DSecure').'<br/>';
		$this->html .= '</div><div style="clear:both"></div>';
	}

	/**
	* Display form on module page configuration
	* @return string
	*/
	private function displayForm()
	{
		$checked_auto = '';
		$checked_delayed = '';
		$checked_realvault_yes = '';
		$checked_realvault_no = '';
		$checked_cvn_yes = '';
		$checked_cvn_no = '';
		$checked_liability_yes = '';
		$checked_liability_no = '';
		if (Tools::getValue('settlement') == 'auto')
			$checked_auto = "checked='checked'";
		elseif (Tools::getValue('settlement') == 'delayed')
			$checked_delayed = "checked='checked'";
		elseif ($this->settlement == 'auto')
			$checked_auto = "checked='checked'";
		elseif ($this->settlement == 'delayed')
			$checked_delayed = "checked='checked'";
		if (Tools::getValue('realvault') == '1')
			$checked_realvault_yes = "checked='checked'";
		elseif (Tools::getValue('realvault') != '' && Tools::getValue('realvault') == '0')
			$checked_realvault_no = "checked='checked'";
		elseif ($this->realvault == '1')
			$checked_realvault_yes = "checked='checked'";
		elseif ($this->realvault == '0')
			$checked_realvault_no = "checked='checked'";
		if (Tools::getValue('cvn') == '1')
			$checked_cvn_yes = "checked='checked'";
		elseif (Tools::getValue('cvn') != '' && Tools::getValue('cvn') == '0')
			$checked_cvn_no = "checked='checked'";
		elseif ($this->cvn == '1')
			$checked_cvn_yes = "checked='checked'";
		elseif ($this->cvn == '0')
			$checked_cvn_no = "checked='checked'";
		if (Tools::getValue('liability') == '1')
			$checked_liability_yes = "checked='checked'";
		elseif (Tools::getValue('liability') != '' && Tools::getValue('liability') == '0')
			$checked_liability_no = "checked='checked'";
		elseif ($this->liability == '1')
			$checked_liability_yes = "checked='checked'";
		elseif ($this->liability == '0')
			$checked_liability_no = "checked='checked'";		
		
		if (Configuration::get('PS_SSL_ENABLED'))
			$link_request = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'module/'.$this->name.'/payment';
		else
			$link_request = Tools::getShopDomain(true, true).__PS_BASE_URI__.'module/'.$this->name.'/payment';
		$link_response = $this->url_validation;
		$this->html .=	'<form action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" method="post">';
		$this->html .=	'<fieldset>';
		$this->html .=	'<legend><img src="../img/admin/contact.gif" />'.$this->l('Realex Payments information').'</legend>';
		$this->html .=	'<table border="0" width="500" cellpadding="5" cellspacing="0" id="form">';
		$this->html .=	'<tr><td colspan="2">'.$this->l('Please specify your realex account details.').'.<br /><br /></td></tr>';
		$this->html .=	'<tr><td width="130" style="height: 35px;vertical-align: top;">'.$this->l('Merchant ID').'</td>';
		$this->html .=	'<td style="vertical-align: top;">';
		$this->html .=	'<input type="text" name="merchantId" value="'.htmlentities(Tools::getValue('merchantId', $this->merchant_id), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" />';
		$this->html .=	'</td></tr>';
		$this->html .=	'<tr><td width="130" style="height: 35px;vertical-align: top;">'.$this->l('Shared secret').'</td>';
		$this->html .=	'<td style="vertical-align: top;">';
		$this->html .=	'<input type="password" name="sharedSecret" value="'.htmlentities(Tools::getValue('sharedSecret', $this->shared_secret), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" />';
		$this->html .=	'<br/><br/></td></tr>';
		$this->html .=	'<tr><td width="130" style="height: 35px;vertical-align: top;">'.$this->l('Settlement').'</td>';
		$this->html .=	'<td><input type="radio" name="settlement" '.$checked_auto.' value="auto" /> Auto';
		$this->html .=	'<br/><input type="radio" name="settlement" '.$checked_delayed.' value="delayed" />';
		$this->html .=	'Delayed<br/><em>'.$this->l('If you are using DCC the settlement type will be automatically set to "Auto"').'</em></td></tr>';
		$this->html .=	'<tr><td width="130" style="height: 35px;vertical-align: top;">'.$this->l('RealVault').'</td>';
		$this->html .=	'<td><input type="radio" name="realvault" '.$checked_realvault_yes.' value="1" /> Yes <br/>';
		$this->html .=	'<input type="radio" name="realvault" '.$checked_realvault_no.' value="0" /> No</td></tr>';
		$this->html .=	'<tr><td width="130" style="height: 35px;vertical-align: top;">'.$this->l('Request Security Code on tokenised transactions: ').'</td>';
		$this->html .=	'<td><input type="radio" name="cvn" '.$checked_cvn_yes.' value="1" /> Yes ';
		$this->html .=	'<br/><input type="radio" name="cvn" '.$checked_cvn_no.' value="0" /> No</td></tr>';
		$this->html .=	'<tr><td width="130" style="height: 35px;vertical-align: top;">'.$this->l('Require Liability Shift on 3DSecure transactions').'</td>';
		$this->html .=	'<td><input type="radio" name="liability" '.$checked_liability_yes.' value="1" /> Yes <br/>';
		$this->html .=	'<input type="radio" name="liability" '.$checked_liability_no.' value="0" /> No</td></tr>';
		$this->html .=	'<tr><td colspan="2" align="center">';
		$this->html .=	'<input class="button" name="btnSubmit" value="'.$this->l('Update settings').'" type="submit" /></td></tr>';
		$this->html .=	'<tr><td colspan="2"><br/><br/>';
		$this->html .=	'<strong>';
		$this->html .=	$this->l('Before you can use this module you must supply Realex with the following URL\'s:').'</strong><br/><br/>';
		$this->html .=	'<strong><u>Request</u></strong> = '.$link_request.'<br/><br/>';
		$this->html .=	'<strong><u>Response</u></strong> = '.$link_response.'<br/>';
		$this->html .=	'</td></tr>';
		$this->html .=	'</table>';
		$this->html .=	'</fieldset></form>';
		$this->html .= '<br/><br/>';
		$this->html .= '<fieldset><legend><img src="../img/admin/contact.gif" />'.$this->l('Realex Payments subaccounts').'</legend>';
			if (!empty($this->edit_account))
				$this->html .= $this->edit_account;
			else {
				$this->html .= $this->getAccount();
				$this->html .= '<br/><br/>';
				$this->html .= '<form action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" method="post">';
				$this->html .= '<table border="0" cellpadding="5" cellspacing="0"';
				$this->html .= 'id="form" style="padding:10px; border: 1px solid #606062; background:#F4F6F9">';
						$this->html .= '<tr><td style="height: 35px;vertical-align: top;" colspan="2"><strong><strong>'.$this->l('Add a sub-account').'</strong></strong></td></tr>';
						$this->html .= '<tr><td style="height: 35px;vertical-align: top;"><strong>'.$this->l('Sub-account').':</strong></td>';
						$this->html .= '<td style="vertical-align: top;"><input type="text" name="subAccount"  style="width: 300px;" /></tr>';
						$this->html .= '<tr><td><strong>'.$this->l('Cards').':</strong></td><td>';
						$this->html .= '<input type="checkbox" value="VISA" name="type_card[]" /> Visa - ';
						$this->html .= '<input type="checkbox" value="MC" name="type_card[]"/> MasterCard - ';
						$this->html .= '<input type="checkbox" value="LASER" name="type_card[]"/> Laser - ';
						$this->html .= '<input type="checkbox" value="SWITCH" name="type_card[]"/> Switch - ';
						$this->html .= '<input type="checkbox" value="AMEX" name="type_card[]"/> American Express - ';
						$this->html .= '<input type="checkbox" value="DELTA" name="type_card[]"/> Delta - ';
						$this->html .= '<input type="checkbox" value="DINERS" name="type_card[]"/> Diners - ';
						$this->html .= '<input type="checkbox" value="SOLO" name="type_card[]"/> Solo ';
						$this->html .= '&nbsp;  &nbsp;  &nbsp; </td></tr>';
						$this->html .= '<tr><td width="130" style="height: 35px;vertical-align: top;">';
						$this->html .= '<strong>'.$this->l('3D secure').':</strong></td>';
						$this->html .= '<td><input type="radio" name="threeds" value="0" checked="checked" /> '.$this->l('No').'<br/>';
						$this->html .= '<input type="radio" name="threeds" value="1" /> '.$this->l('Yes').' </td></tr>';
						$this->html .= '<tr><td width="130" style="height: 35px;vertical-align: top;">';
						$this->html .= '<strong>'.$this->l('Dynamic Currency Conversion (DCC)').':</strong></td>';
						$this->html .= '<td><input type="radio" name="dcc" value="0" checked="checked" /> '.$this->l('No').'<br/>';
						$this->html .= '<input type="radio" name="dcc" value="1" /> '.$this->l('Yes').' <br/><br/>';
						$this->html .= '<input type="radio" name="dcc_choice" value="fexco" checked="checked" /> '.$this->l('fexco').'<br/>';
						$this->html .= '<input type="radio" name="dcc_choice" value="euroconex" /> '.$this->l('euroconex').'</td></tr>';
						$this->html .= '<tr><td colspan="2" align="center"><br/>';
						$this->html .= '<input class="button" name="btnSubmitAccount" value="'.$this->l('Save').'" type="submit" /></td></tr>';
						$this->html .= '</table></form>';
			}
			$this->html .= '</fieldset>';
	}

	/**
	* Display module page configuration
	* @return string
	*/
	public function getContent()
	{
		$this->html = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('btnSubmit') || Tools::isSubmit('btnSubmitAccount') || Tools::isSubmit('delete_realex_account') || Tools::isSubmit('edit_realex_account') || Tools::isSubmit('btnSubmitEditAccount'))
		{
			$this->postValidation();
			if (!count($this->post_errors))
				$this->postProcess();
			else
				foreach ($this->post_errors as $err)
					$this->html .= '<div class="alert error">'.$err.'</div>';
		}
		else
			$this->html .= '<br />';
		$this->displayRealex();
		$this->displayForm();
		return $this->html;
	}

	/**
	* Attach the module to the hook "Payment"
	* Assigns variables to tpl "payment"
	*/
	public function hookPayment($params)
	{
		if (!$this->active)
			return;
		if (!$this->checkCurrency($params['cart']))
			return;
		$this->smarty->assign(array(
			'this_path' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
		));
		return $this->display(__FILE__, 'payment.tpl');
	}

	/**
	* Attach the module to the hook "PaymentReturn"
	* Assigns variables to tpl "payment_return"
	*/
	public function hookPaymentReturn($params)
	{
		if (!$this->active)
			return;

		$state = $params['objOrder']->getCurrentState();
		if ($state == Configuration::get('PS_OS_PAYMENT') || $state == Configuration::get('PS_OS_OUTOFSTOCK'))
		{
			$this->smarty->assign(array(
				'status' => 'ok',
				'id_order' => $params['objOrder']->id
			));
			if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference))
				$this->smarty->assign('reference', $params['objOrder']->reference);
		}
		else
			$this->smarty->assign('status', 'failed');
		return $this->display(__FILE__, 'payment_return.tpl');
	}
	/**
	* Check is currency is enabled for this module
	* @param object $cart Object
	* @return boolean
	*/
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

	/**
	* Attach the module to the hook "Header"
	* Add CSS
	*/
	public function hookHeader($params)
	{
		$this->context->controller->addCSS($this->_path.'css/realexredirect.css', 'all');
	}

	/**
	* Return translate transaction result message
	* @param string $result from realexredirect::manageOrder();
	* @return string
	*/
	public function getMsg($result = null)
	{
		if (empty($result)) $result = 999;
		switch ($result)
		{
			case '00':
				$retour = $this->l('Payment authorised successfully');
				break;
			case '101':
				$retour = $this->l('An error occured during payment.');
				break;
			case '102':
				$retour = $this->l('An error occured during payment.');
				break;
			case '103':
				$retour = $this->l('An error occured during payment.');
				break;
			case $result >= 200 && $result < 300:
				$retour = $this->l('An error occured during payment.');
				break;
			case $result >= 300 && $result < 400:
				$retour = $this->l('Error with Realex Payments systems');
				break;
			case $result >= 500 && $result < 600:
				$retour = $this->l('Incorrect XML message formation or content');
				break;
			case '666':
				$retour = $this->l('Client deactivated.');
				break;
			case '999':
				$retour = $this->l('An error occured during payment.');
				break;
			case 'fail_liability':
				$retour = $this->l('3D Secure authentication failure');
				break;
			default:
				$retour = $this->l('An error occured during payment.');
				break;
		}
		return $retour;
	}

	/**
	* Return translate AVS result message
	* @param string $response from realexredirect::manageOrder();
	* @return string
	*/
	public function getAVSresponse($response = null)
	{
		if (empty($response))
			$response = 'EE';
		switch ($response)
		{
			case 'M':
				$retour = $this->l('Matched');
				break;
			case 'N':
				$retour = $this->l('Not Matched');
				break;
			case 'I':
				$retour = $this->l('Problem with check');
				break;
			case 'U':
				$retour = $this->l('Unable to check (not certified etc)');
				break;
			case 'P':
				$retour = $this->l('Partial Match');
				break;
			case 'EE':
				$retour = $this->l('Error Occured');
				break;
		}
		return $retour;
	}

	/**
	* Return formatted amount without '.'
	* @param string $total from RealexRedirectPaymentModuleFrontController::initContent()
	* @return string
	*/
	public function getAmountFormat($total)
	{
		$tab = explode('.', $total);
		if (count($tab) == 1)
			return $tab[0].'00';
		else {
			if (Tools::strlen(($tab[1])) == 1)
				$total = $tab[0].$tab[1].'0';
			else
				$total = $tab[0].$tab[1];
		}
		return $total;
	}

	/**
	* Return list of all subaccounts set by the merchant for admin display
	* @return string
	*/
	private function getAccount()
	{
		$sql = 'SELECT * FROM `'._DB_PREFIX_.'realex_subaccount`';

		$liste = '<table border="1" cellpadding="5" cellspacing="5"><tr><td colspan="5"><strong>'.$this->l('My subaccounts').'</strong></td></tr>';
		if ($results = Db::getInstance()->ExecuteS($sql))
		{
			foreach ($results as $row)
			{
				$sql2 = 'SELECT realex_card_name FROM `'._DB_PREFIX_.'realex_rel_card` WHERE id_realex_subaccount='.(int)$row['id_realex_subaccount'];
				$cards = Db::getInstance()->ExecuteS($sql2);
				$card_list = '';
				foreach ($cards as $card)
					$card_list .= $card['realex_card_name'].',';
				$card_list = rtrim($card_list, ',');
				if (!$row['threeds_realex_subaccount'])
					$threeds = $this->l('No');
				else
					$threeds = $this->l('Yes');
				if (!$row['dcc_realex_subaccount'])
					$dcc = $this->l('No');
				else
					$dcc = $this->l('Yes').' ('.$row['dcc_choice_realex_subaccount'].')';
				$liste .= '<tr><td> <strong>'.$row['name_realex_subaccount'].'</strong></td><td>'.$card_list.'</td><td>3D Secure: '.$threeds.'</td><td>DCC: '.$dcc.'</td><td>';
					$liste .= "<form action='".Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI'])."' method='post'>
					<input type='submit' name='edit_realex_account' value='".$this->l('Edit')."' />
					<input type='submit' onclick='return(confirm(\"".$this->l('Do you want to delete your subaccount ?')."\"))' name='delete_realex_account' value='".$this->l('Delete')."' />
					<input type='hidden' name='id_subaccount' value='".$row['id_realex_subaccount']."' />
					</form>
				</td></tr>";
			}
		}
		else
			$liste .= '<tr><td colspan="5">'.$this->l('No account defined').'</td></tr>';
		$liste .= '</table>';
		return $liste;
	}
	/**
	* Display form to edit subaccount
	* @return string
	*/
	private function displayEditForm($id_subaccount)
	{
		$sql = 'SELECT * FROM '._DB_PREFIX_.'realex_subaccount WHERE id_realex_subaccount='.(int)$id_subaccount;
		if ($results = Db::getInstance()->getRow($sql))
		{
			$sql2 = 'SELECT realex_card_name FROM '._DB_PREFIX_.'realex_rel_card WHERE id_realex_subaccount='.(int)$id_subaccount;
			if ($cards = Db::getInstance()->ExecuteS($sql2))
			{
				$tab_card  = array();
				foreach ($cards as $card)
					$tab_card[] = $card['realex_card_name'];
				$edit = '<form action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" method="post">';
				$edit .= '<table border="0" cellpadding="5" cellspacing="0" id="form" style="margin-top:10px; padding:10px; border: 1px solid #606062; background:#fff">';
						$edit .= '<tr><td style="height: 35px;vertical-align: top;" colspan="2"><strong><strong>'.$this->l('Edit a sub-account').'</strong></strong></td></tr>';
						$edit .= '<tr><td style="height: 35px;vertical-align: top;"><strong>'.$this->l('Sub-account').':</strong></td>';
						$edit .= '<td style="vertical-align: top;"><input type="text" name="subAccount"  style="width: 300px;" value="'.$results['name_realex_subaccount'].'"/></tr>';
						$edit .= '<tr><td><strong>'.$this->l('Cards').':</strong></td><td>';
						$check = "checked='checked'";
						$check_visa = (in_array('VISA', $tab_card))?$check:'';
						$check_mc = (in_array('MC', $tab_card))?$check:'';
						$check_laser = (in_array('LASER', $tab_card))?$check:'';
						$check_switch = (in_array('SWITCH', $tab_card))?$check:'';
						$check_amex = (in_array('AMEX', $tab_card))?$check:'';
						$check_delta = (in_array('DELTA', $tab_card))?$check:'';
						$check_diners = (in_array('DINERS', $tab_card))?$check:'';
						$check_solo = (in_array('SOLO', $tab_card))?$check:'';
						$edit .= '<input type="checkbox" value="VISA" name="type_card[]" '.$check_visa.'/> Visa - ';
						$edit .= '<input type="checkbox" value="MC" name="type_card[]" '.$check_mc.'/> MasterCard - ';
						$edit .= '<input type="checkbox" value="LASER" name="type_card[]" '.$check_laser.'/> Laser - ';
						$edit .= '<input type="checkbox" value="SWITCH" name="type_card[]" '.$check_switch.'/> Switch - ';
						$edit .= '<input type="checkbox" value="AMEX" name="type_card[]" '.$check_amex.'/> American Express - ';
						$edit .= '<input type="checkbox" value="DELTA" name="type_card[]" '.$check_delta.'/> Delta - ';
						$edit .= '<input type="checkbox" value="DINERS" name="type_card[]" '.$check_diners.'/> Diners - ';
						$edit .= '<input type="checkbox" value="SOLO" name="type_card[]" '.$check_solo.'/> Solo ';
						$edit .= '&nbsp;  &nbsp;  &nbsp; </td></tr>';
						$selected = "selected='selected'";
						$check_3ds_yes = ($results['threeds_realex_subaccount'])?$check:'';
						$check_3ds_no = (!$results['threeds_realex_subaccount'])?$check:'';
						$edit .= '<tr><td width="130" style="height: 35px;vertical-align: top;"><strong>'.$this->l('3D secure').':</strong></td><td><input type="radio" name="threeds" value="0" '.$check_3ds_no.' /> '.$this->l('No').'<br/><input type="radio" name="threeds" value="1" '.$check_3ds_yes.'/> '.$this->l('Yes').' </td></tr>';
						$check_dcc_yes = ($results['dcc_realex_subaccount'])?$check:'';
						$check_dcc_no = (!$results['dcc_realex_subaccount'])?$check:'';
						$edit .= '<tr><td width="130" style="height: 35px;vertical-align: top;"><strong>'.$this->l('Dynamic Currency Conversion (DCC)').':</strong></td><td><input type="radio" name="dcc" value="0" '.$check_dcc_no.' /> '.$this->l('No').'<br/><input type="radio" name="dcc" value="1" '.$check_dcc_yes.'/> '.$this->l('Yes').' <br/><br/>';
						$check_fexco = ($results['dcc_choice_realex_subaccount'] == 'fexco')?$check:'';
						$check_euroconex = ($results['dcc_choice_realex_subaccount'] == 'euroconex')?$check:'';
						$edit .= '<input type="radio" name="dcc_choice" value="fexco" '.$check_fexco.' /> '.$this->l('fexco').'<br/>';
						$edit .= '<input type="radio" name="dcc_choice" value="euroconex" '.$check_euroconex.'/> '.$this->l('euroconex').'</td></tr>';
			$edit .= '<tr><td colspan="2" align="center"><br/>';
			$edit .= '<input class="button" name="CancelEditAccount" value="'.$this->l('Cancel').'" type="submit" />';
			$edit .= '<input type="hidden" name="id_realex_subaccount" value="'.$results['id_realex_subaccount'].'" />';
			$edit .= '<input class="button" name="btnSubmitEditAccount" value="'.$this->l('Update').'" type="submit" /></td></tr>';
			$edit .= '</table></form>';
			}
		}
		$this->edit_account = $edit;
	}
	/**
	* Return all subaccounts set by the merchant
	* @return array
	*/
	public function getTableAccount()
	{
		if ($this->active)
		{
			$sql = 'SELECT * FROM `'._DB_PREFIX_.'realex_subaccount` rs JOIN `'._DB_PREFIX_.'realex_rel_card` rc ON rs.id_realex_subaccount=rc.id_realex_subaccount';
			return $results = Db::getInstance()->ExecuteS($sql);
		}
		else return false;
	}

	/**
	* Return list of all cards type set by the merchant for customer display
	* @return array
	*/
	public function getSelectAccount()
	{
		$accounts = $this->getTableAccount();
		$tab = array();
		$temp = array();
		$i = 0;
		foreach ($accounts as $account)
		{
			$tab_card = explode(',', $account['realex_card_name']);
			foreach ($tab_card as $card)
			{
				if (!in_array($card, $temp))
				{
					$tab[$i]['card'] = $card;
					$tab[$i]['account'] = $account['name_realex_subaccount'];
					$temp[] = $card;
					$i++;
				}
			}
		}
		return $tab;
	}

	/**
	* Return result of 3ds enrolled verification
	* @param array $post from RealexRedirectValidationModuleFrontController::postProcess()
	* @return xml
	*/
	public function requestRealvault3dsVerifyenrolled($post)
	{
		$url 			= 'https://epage.payandshop.com/epage-remote-plugins.cgi';
		$amount 		= Tools::getValue('AMOUNT');
		$currency 		= Tools::getValue('CURRENCY');
		$merchantid 	= $this->merchant_id;
		$account 		= Tools::getValue('ACCOUNT');
		$payerref 		= Tools::getValue('PAYER_REF');
		$paymentmethod 	= Tools::getValue('PMT_REF');
		$timestamp 		= Tools::getValue('TIMESTAMP');
		$orderid 		= Tools::getValue('ORDER_ID');
		$sha1hash		= Tools::getValue('SHA1HASH');
		$autosettle		= Tools::getValue('AUTO_SETTLE_FLAG');
		$cvn			= Tools::getValue('cvn');
		$dcc			= Tools::getValue('dcc');
		if (isset($dcc) && $dcc != '0' && !empty($dcc))
			$is_dcc = true;
		else
			$is_dcc = false;
		$xml = "<request type='realvault-3ds-verifyenrolled' timestamp='$timestamp'>";
			$xml .= "<merchantid>$merchantid</merchantid>
			<account>$account</account>
			<orderid>$orderid</orderid>
			<amount currency='$currency'>$amount</amount>";
			if ($is_dcc)
				$xml .= "<autosettle flag='1' />";
			else
				$xml .= "<autosettle flag='$autosettle' />";
			$xml .= "<payerref>$payerref</payerref>
			<paymentmethod>$paymentmethod</paymentmethod>
			<sha1hash>$sha1hash</sha1hash>				
			</request>";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, 'payandshop.com php version 0.9');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		$response = curl_exec ($ch);
		curl_close ($ch);
		$xm = simplexml_load_string($response);
		$xm->addChild('currency', $currency);
		$xm->addChild('amount', $amount);
		$xm->addChild('dcc', $dcc);
		$xm->addChild('cvn', $cvn);
		$xm->addChild('payerref', $payerref);
		$xm->addChild('paymentmethod', $paymentmethod);
		$xm->addChild('autosettle', $autosettle);
		return $xm;
	}

	/**
	* Return result of 3ds authentication
	* @param array $post from RealexRedirectValidationModuleFrontController::postProcess()
	* @return xml
	*/
	public function requestRealvault3dsVerifysig($post)
	{
		$url 				= 'https://epage.payandshop.com/epage-remote-plugins.cgi';
		$pares 				= (string)Tools::getValue('PaRes');
		$merchantid 		= $this->merchant_id;		
		$md64 				= base64_decode(Tools::getValue('MD'));
		$blow 				= new BlowfishCore($this->shared_secret, $this->shared_secret);
		$decrypt 			= $blow->decrypt($md64);
		$infos 				= explode('$', $decrypt);
		$timestamp 			= $infos[0];
		$orderid 			= $infos[1];
		$currency 			= $infos[2];
		$amount 			= $infos[3];
		$payerref 			= $infos[4];
		$paymentmethod 		= $infos[5];
		$result 			= $infos[6];
		$message 			= $infos[7];
		$account 			= $infos[8];
		$cvn 				= $infos[9];
		$dcc 				= $infos[10];
		$dcc_choice 		= $infos[11];
		$autosettle			= $infos[12];
		$billing_code		= $infos[13];
		$billing_country	= $infos[14];
		$shipping_code		= $infos[15];
		$shipping_country	= $infos[16];
		$cardnumber		= '';
		$tmp 			= $timestamp.'.'.$merchantid.'.'.$orderid.'.'.$amount.'.'.$currency.'.'.$cardnumber;
		$sha1 			= sha1($tmp);
		$sha1			= sha1($sha1.'.'.$this->shared_secret);
		$xml = "<request timestamp='$timestamp' type='3ds-verifysig'>
				<merchantid>".$merchantid."</merchantid>
				<account>$account</account>
				<orderid>$orderid</orderid>
				<amount currency='$currency'>$amount</amount>
				<card> 
					<number></number>
					<expdate></expdate>
					<type></type> 
					<chname></chname> 
				</card>
				<payerref>$payerref</payerref>
				<paymentmethod>$paymentmethod</paymentmethod>
				<pares>$pares</pares>
				<sha1hash>$sha1</sha1hash>
			</request>";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, 'payandshop.com php version 0.9');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		$response = curl_exec ($ch);
		curl_close ($ch);
		$xm = simplexml_load_string($response);
		$xm->addChild('currency', (string)$currency);
		$xm->addChild('amount', (int)$amount);
		$xm->addChild('cvn', (int)$cvn);
		$xm->addChild('dcc', (int)$dcc);
		$xm->addChild('dcc_choice', (string)$dcc_choice);
		$xm->addChild('payerref', (string)$payerref);
		$xm->addChild('paymentmethod', (string)$paymentmethod);
		$xm->addChild('billing_code', (string)$billing_code);
		$xm->addChild('billing_country', (string)$billing_country);
		$xm->addChild('shipping_code', (string)$shipping_code);
		$xm->addChild('shipping_country', (string)$shipping_country);
		$xm->addChild('autosettle', (int)$autosettle);
		return $xm;
	}

	/**
	* Return result of payment via RealVault
	* @param xml $xm from RealexRedirectValidationModuleFrontController::postProcess()
	* @param boolean $ask_dcc (optional)
	* @param boolean $set_dcc (optional)
	* @return xml
	*/
	public function requestRealvaultReceiptIn($xm, $ask_dcc = true, $set_dcc = false)
	{
		$url 		= 'https://epage.payandshop.com/epage-remote-plugins.cgi';
		$tmp 		= $xm->attributes()->timestamp.'.'.$this->merchant_id.'.'.$xm->orderid.'.'.$xm->amount.'.'.$xm->currency.'.'.$xm->payerref;
		$sha1hash 	= sha1($tmp);
		$tmp 		= $sha1hash.'.'.$this->shared_secret;
		$sha1hash 	= sha1($tmp);
		$xm->addChild('sha1', $sha1hash);
		if ($xm->dcc != '0' && $ask_dcc)
			$xm_dcc = $this->requestRealvaultDccrate($xm);
		else
			$xm_dcc = false;
		if ($xm_dcc)
			exit;
		$xml = "<request type='receipt-in' timestamp='".$xm->attributes()->timestamp."'>";
		$xml .= '<merchantid>'.$this->merchant_id.'</merchantid>
			<account>'.$xm->account.'</account>
			<orderid>'.$xm->orderid.'</orderid>
			<amount currency="'.$xm->currency.'">'.$xm->amount.'</amount>';
		if (!empty($xm->cvn))
			$xml .= '<paymentdata>
					<cvn>
						<number>'.$xm->cvn.'</number>
					</cvn>
				</paymentdata>';
		if ($set_dcc)
			$xml .= '<autosettle flag="1" />';
		else
			$xml .= '<autosettle flag="'.$xm->autosettle.'" />';
		if (isset($xm->eci) && !empty($xm->eci))
		{
			$xml .= '<mpi>';
			if (isset($xm->cavv) && !empty($xm->cavv) && isset($xm->xid) && !empty($xm->xid))
			{
				$xml .= '<cavv>'.$xm->cavv.'</cavv>
				<xid>'.$xm->xid.'</xid>';
			}
			if (isset($xm->eci) && !empty($xm->eci))
				$xml .= '<eci>'.$xm->eci.'</eci></mpi>';
		}
		$xml .= '<payerref>'.$xm->payerref.'</payerref>
		<paymentmethod>'.$xm->paymentmethod.'</paymentmethod>';
		if ($set_dcc)
			$xml .= '
			<dccinfo>
				<ccp>'.$xm->dcc.'</ccp>
				<type>1</type>
				<rate>'.$xm->cardholderrate.'</rate>
				<ratetype>S</ratetype>
				<amount currency="'.$xm->cardholdercurrency.'">'.$xm->cardholderamount.'</amount>
			</dccinfo>';
		$xml .= '<md5hash />
			<sha1hash>'.$sha1hash.'</sha1hash>
			<comments>
				<comment id="1" />
				<comment id="2" />
			</comments>
			<tssinfo>
				<address type="billing">
					<code>'.$xm->billing_code.'</code>
					<country>'.$xm->billing_country.'</country>
				</address>
				<address type="shipping">
					<code>'.$xm->shipping_code.'</code>
					<country>'.$xm->shipping_country.'</country>
				</address>
				<custnum></custnum>
				<varref></varref>
				<prodid></prodid>
			</tssinfo>
		</request>';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, 'payandshop.com php version 0.9');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		$response = curl_exec ($ch);
		curl_close ($ch);
		$xm_receipt = simplexml_load_string($response);
		if (isset($xm->eci))
			$xm_receipt->addChild('eci', $xm->eci);
		if (isset($xm_receipt->dccinfo))
		{
			$xm_receipt->addChild('dcc', $xm->dcc);
			$xm_receipt->addChild('dcc_choice', $xm->dcc_choice);
			$xm_receipt->addChild('dcc_rate', $xm_receipt->dccinfo->cardholderrate);
			$xm_receipt->addChild('dcc_cardholder_amount', $xm_receipt->dccinfo->cardholderamount);
			$xm_receipt->addChild('dcc_cardholder_currency', $xm_receipt->dccinfo->cardholdercurrency);
			$xm_receipt->addChild('dcc_merchant_amount', $xm_receipt->dccinfo->merchantamount);
			$xm_receipt->addChild('dcc_merchant_currency', $xm_receipt->dccinfo->merchantcurrency);
		}
		return $xm_receipt;
	}

	/**
	* Return result of DCC enrolled verification
	* @param xml $xm  from RealexRedirectValidationModuleFrontController::postProcess()
	* Case 1 : Return false if verification failed
	* Case 2 : Display html form if verification success
	*/
	public function requestRealvaultDccrate($xm)
	{
		$dcc_choice = $xm->dcc_choice;
		$url = 'https://epage.payandshop.com/epage-remote-plugins.cgi';
		$xml = '<request type="realvault-dccrate" timestamp="'.$xm->attributes()->timestamp.'">
		<merchantid>'.$this->merchant_id.'</merchantid>
		<account>'.$xm->account.'</account>
		<orderid>'.$xm->orderid.'</orderid>
		<amount currency="'.$xm->currency.'">'.$xm->amount.'</amount>
		<payerref>'.$xm->payerref.'</payerref>
		<paymentmethod>'.$xm->paymentmethod.'</paymentmethod>
		<dccinfo>
			<ccp>'.$dcc_choice.'</ccp>
			<type>1</type>
		</dccinfo>
		<md5hash />
		<sha1hash>'.$xm->sha1.'</sha1hash>
		<comments>
			<comment id="1" />
			<comment id="2" />
			</comments>
		</request>';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, 'payandshop.com php version 0.9');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		$response = curl_exec ($ch);
		curl_close ($ch);
		$xm_dcc = simplexml_load_string($response);
		if ($xm_dcc->result == '00')
		{ ?>
			<form method="post" action="<?php echo $this->url_validation?>">
				<input type="hidden" name="choice_dcc" value="1">
				<input type="hidden" name="ACCOUNT" value="<?php echo $xm->account?>">
				<input type="hidden" name="SHA1HASH" value="<?php echo $xm->sha1?>">
				<input type="hidden" name="PMT_REF" value="<?php echo $xm->paymentmethod?>">
				<input type="hidden" name="PAYER_REF" value="<?php echo $xm->payerref?>">
				<input type="hidden" name="CURRENCY" value="<?php echo $xm->currency?>">
				<input type="hidden" name="MERCHANT_ID" value="<?php echo $this->merchant_id?>">
				<input type="hidden" name="ORDER_ID" value="<?php echo $xm->orderid?>">
				<input type="hidden" name="AMOUNT" value="<?php echo $xm->amount?>">
				<input type="hidden" name="TIMESTAMP" value="<?php echo $xm->attributes()->timestamp?>">
				<input type="hidden" name="AUTO_SETTLE_FLAG" value="1">
				<input type="hidden" name="BILLING_CODE" value="<?php echo $xm->billing_code?>">
				<input type="hidden" name="BILLING_CO" value="<?php echo $xm->billing_country?>">
				<input type="hidden" name="SHIPPING_CODE" value="<?php echo $xm->shipping_code?>">
				<input type="hidden" name="SHIPPING_CO" value="<?php echo $xm->shipping_country?>">
				<input type="hidden" name="cvn" value="<?php echo $xm->cvn?>">
				<input type="hidden" name="eci" value="<?php echo $xm->eci?>">
				<input type="hidden" name="cavv" value="<?php echo $xm->cavv?>">
				<input type="hidden" name="xid" value="<?php echo $xm->xid?>">
				<input type="hidden" name="DCCAUTHCARDHOLDERAMOUNT" value="<?php echo $xm_dcc->dccinfo->cardholderamount?>">
				<input type="hidden" name="DCCAUTHRATE" value="<?php echo $xm_dcc->dccinfo->cardholderrate?>">
				<input type="hidden" name="DCCAUTHCARDHOLDERCURRENCY" value="<?php echo $xm_dcc->dccinfo->cardholdercurrency?>">
				<input type="hidden" name="DCCAUTHMERCHANTCURRENCY" value="<?php echo $xm_dcc->dccinfo->merchantcurrency?>">
				<input type="hidden" name="DCCAUTHMERCHANTAMOUNT" value="<?php echo $xm_dcc->dccinfo->merchantamount?>">
				<input type="hidden" name="DCCCCP" value="<?php echo $xm->dcc_choice?>">
				<table cellpadding="4" cellspacing="0" align="center" border="0" id="mainBody" style="display: '';">
					<tr>
						<td>
							<table border="0" cellspacing="1" cellpadding="1">		
								<tr>
									<td class="cctd" align="center">
										<?php echo $this->l('The total amount due is')?> <?php echo $xm_dcc->dccinfo->merchantcurrency?> <?php echo (float)($xm_dcc->dccinfo->merchantamount / 100)?><br><br>										
										<?php echo $this->l('We notice that you have an')?> <?php echo $xm_dcc->dccinfo->cardholdercurrency?> <?php echo $this->l('card')?>. <br>
										<?php echo $this->l('For your convenience we can charge this to you as')?> <br>
										<?php echo $xm_dcc->dccinfo->cardholdercurrency?> <?php echo (float)($xm_dcc->dccinfo->cardholderamount / 100)?> <i>(<?php echo $this->l('transaction currency')?>)</i> <br><br>
										(<?php echo $this->l('Exchange rate used')?>: 1 
										<?php echo $xm_dcc->dccinfo->merchantcurrency?> = 
										<?php echo $xm_dcc->dccinfo->cardholderrate?> <?php echo $xm_dcc->dccinfo->cardholdercurrency?>)
										<br><br>
									</td>
								</tr>		
								<tr>
									<td class="cctd" align="center">
										<input type="submit" name="DCCCHOICE_yes" value="<?php echo $this->l('YES, Please charge me in')?> <?php echo $xm_dcc->dccinfo->cardholdercurrency?>">
									</td>
								</tr>												
								<tr>
									<td class="cctd" align="center">
										<?php echo $this->l('Exchange Rate based on')?>: <?php echo $xm_dcc->dccinfo->exchangeratesourcename?> Rate<br>
										<?php echo $this->l('International Conversion Margin')?>: <?php echo $xm_dcc->dccinfo->marginratepercentage?>%<br>
										<?php echo $this->l('Commission for Currency Conversion')?>: <?php echo $xm_dcc->dccinfo->commissionpercentage?>%<br>
									</td>
								</tr>										
								<tr>
									<td class="cctdsmall" align="center">
										<?php echo $this->l('I understand that I have been offered a choice of currencies for payment.<br>I accept the conversion rate and final amount and that the final selected transaction currency is')?> <?php echo $xm_dcc->dccinfo->cardholdercurrency?>; <br><?php echo $this->l('I understand that my choice is final')?>.
									</td>
								</tr>		
								<tr>
									<td class="cctd" align="center">
										<br><br>
									</td>
								</tr>		
								<tr>
									<td class="cctd" align="center">
										<input class="smallinput" type="submit" name="DCCCHOICE_no" value="<?php echo $this->l('NO,  Please charge me in')?> <?php echo $xm_dcc->dccinfo->merchantcurrency?>">
									</td>
								</tr>		
								<tr>
									<td class="cctd" align="center">
										<br>
									</td>
								</tr>							
							</table>
						</td>
					</tr>
				</table>
			</form>
		<?php exit;
		}
		else return false;
	}

	/**
	* Manage and finalize the order on prestashop side
	* @param xml $xm from RealexRedirectValidationModuleFrontController::postProcess()
	* @param boolean $viarealvault (optional)
	* Case 1 : Redirection to payment confirmation if $viarealvault
	* Case 2 : Display html if !$viarealvault
	*/
	public function manageOrder($xm, $viarealvault = true, $failed = false)
	{
		$link = $this->context->link;
		$result						= $xm->result;
		$pasref						= (int)$xm->pasref;
		$tss						= (int)$xm->tss->result;
		$orderid					= (string)$xm->orderid;
		$merchantid					= (string)$this->merchant_id;
		$message					= (string)$xm->message;
		$authcode					= (string)$xm->authcode;
		$sha1						= (string)$xm->sha1hash;
		$timestamp					= (string)$xm->attributes()->timestamp;
		$account					= (string)$xm->account;
		$currency					= (string)$xm->currency;
		$amount						= (string)$xm->amount;
		$cvn						= (string)$xm->cvn;
		$autosettle					= (string)$xm->autosettle;
		$rv							= (string)$xm->RV;
		$rv_saved_payer_ref			= (string)$xm->RVSavedPayerRef;
		$rv_saved_payment_ref		= (string)$xm->RVSavedPaymentRef;
		$rv_saved_payment_type		= (string)$xm->RVSavedPaymentType;
		$rv_pmt_response			= (string)$xm->RVPmtResponse;
		$rv_pmt_digits				= (string)$xm->RVPmtDigits;
		$rv_pmt_exp_format			= (string)$xm->RVPmtExpFormat;
		$tss						= (string)$xm->tss->result;
		$eci						= (string)$xm->eci;
		$avs_post_code_response		= (string)$xm->avspostcoderesponse;
		$avs_address_response		= (string)$xm->avsaddressresponse;
		$dcc						= (string)$xm->dcc;
		$dcc_choice					= (string)$xm->dcc_choice;
		$dcc_rate					= (string)$xm->dcc_rate;
		$dcc_cardholder_currency	= (string)$xm->dcc_cardholder_currency;
		$dcc_cardholder_amount		= (string)$xm->dcc_cardholder_amount;
		$dcc_merchant_currency		= (string)$xm->dcc_merchant_currency;
		$dcc_merchant_amount		= (string)$xm->dcc_merchant_amount;
		// ---------------- CREATION PANIER
		$id_cart 			= explode('-', $orderid);
		$cart 				= new Cart($id_cart[0]);
		if (!$viarealvault)
			$total				= (float)$amount / 100;
		else
			$total				= (float)$cart->getOrderTotal(true, Cart::BOTH);
		// ---------------- CREATION CLIENT
		$customer 			= new Customer((int)$cart->id_customer);
		// ---------------- CREATION MSG BACKEND
		if ($failed)
			$retour_msg 		= 'Status: '.$this->getMsg('fail_liability')." \r\n";
		else
			$retour_msg 		= 'Status: '.$this->getMsg($result)." \r\n";
		$retour_msg 			.= $message." \r\n";
		if ($viarealvault)
			$retour_msg 		.= "Via RealVault \r\n";
		if (isset($pasref) && $pasref)
			$retour_msg			.= 'Transaction reference: '.$pasref."\r\n";
		if (isset($tss) && !empty($tss))
			$retour_msg		.= 'TSS: '.$tss."\r\n";
		if (isset($eci) && !empty($eci))
			$retour_msg		.= 'ECI: '.$eci."\r\n";
		// ---------------- DCC Choice
		if (isset($dcc) && !empty($dcc) && isset($dcc_rate) && !empty($dcc_rate) && isset($dcc_choice) && !empty($dcc_choice))
		{
			$retour_msg		.= 'DCC type: '.$dcc."\r\n";
			$retour_msg		.= 'DCC choice: '.$dcc_choice."\r\n";
			$retour_msg		.= 'DCC rate: '.$dcc_rate."\r\n";
			$retour_msg		.= 'Card holder amount: '.(float)($dcc_cardholder_amount / 100).' '.$dcc_cardholder_currency."\r\n";
			$retour_msg		.= 'Merchant amount: '.(float)($dcc_merchant_amount / 100).' '.$dcc_merchant_currency."\r\n";
		}
		// ---------------- AVS RETURN
		if (!empty($avs_post_code_response) && !empty($avs_address_response))
		{
			$retour_msg		.= 'AVS PostCode Response: '.$this->getAVSresponse($avs_post_code_response)."\r\n";
			$retour_msg		.= 'AVS Address Response: '.$this->getAVSresponse($avs_address_response)."\r\n";
		}
		// ---------------- CONTROLES
		if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->active)
			die($this->l('This payment method is not available.', 'validation'));
		if (!Validate::isLoadedObject($customer))
			die($this->l('An error occured.', 'validation'));

		// ---------------- PAYMENT OK
		if ($result == '00' && !$failed)
		{
			// ---------------- CONTROLE SHA1
			$tmp = $timestamp.'.'.$merchantid.'.'.$orderid.'.'.$result.'.'.$message.'.'.$pasref.'.'.$authcode;
			$sha1hash = sha1($tmp);
			$tmp = $sha1hash.'.'.$this->shared_secret;
			$sha1hash = sha1($tmp);
			//Check to see if hashes match or not
			if ($sha1hash != $sha1)
				die($this->l("hashes don't match - response not authenticated!", 'validation'));
			// ----- REAL VAULT ACTIVE
			if ($this->realvault && !$viarealvault)
			{
				if ($rv && $rv_pmt_response == '00')
				{
					$date = new DateTime();
					$sql 			= 'SELECT `refuser_realex`,`id_realex_payerref` FROM `'._DB_PREFIX_.'realex_payerref` WHERE `id_user_realex` = '.$cart->id_customer;
					$payer_ref 		= Db::getInstance()->getRow($sql);
					if (empty($payer_ref))
					{
						Db::getInstance()->insert('realex_payerref', array(
						'id_user_realex' => (int)$cart->id_customer,
						'refuser_realex' => (int)$rv_saved_payer_ref,
						'date_add'	=> $date->format('Y-m-d h:i:s')
						));
						$id_realex_payerref = Db::getInstance()->Insert_ID();
					}
					else
						$id_realex_payerref = $payer_ref['id_realex_payerref'];
					Db::getInstance()->insert('realex_paymentref', array(
						'id_realex_payerref' => (int)$id_realex_payerref,
						'refpayment_realex' => (int)$rv_saved_payment_ref,
						'paymentname_realex' => pSQL($rv_pmt_digits.' - '.$rv_pmt_exp_format),
						'type_card_realex' => pSQL($rv_saved_payment_type),
						'date_add'	=> $date->format('Y-m-d h:i:s')
					));
					$retour_msg .= "RealVault: Succesfull \r\n";
				}
				elseif ($rv)
					$retour_msg .= "RealVault: Problem \r\n";
				else
					$retour_msg .= "RealVault: No \r\n";
			}
			$this->validateOrder($cart->id, Configuration::get('PS_OS_PAYMENT'), $total, $this->displayName, $retour_msg, null, (int)$cart->id_currency, false, $customer->secure_key);
		}
		// ---------------- PAYMENT PB
		elseif ($result != '00' || $failed)
			$this->validateOrder($cart->id, Configuration::get('PS_OS_ERROR'), $total, $this->displayName, $retour_msg, null, (int)$cart->id_currency, false, $customer->secure_key);
		if ($viarealvault)
			Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->id.'&id_order='.$this->currentOrder.'&key='.$customer->secure_key);
		else {
			// ---------------- BACK TO THE SHOP
			$shop_domain = Tools::getShopDomainSsl(true, true);
			$msg = str_replace('?', '&rsquo;', utf8_decode($this->getMsg($result)));
			$controller_link = $link->getPageLink('order-confirmation', true, null, 'id_cart='.$cart->id.'&id_module='.$this->id.'&id_order='.$this->currentOrder.'&key='.$customer->secure_key);
			echo '
			<html xmlns="http://www.w3.org/1999/xhtml">
				<head>
					<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
				   <title>'.$this->l('Realex Confirmation Payment').'</title>
				</head>
				<body>
					<center>
						<table border="0" width="100%" style="margin:auto; border: 1px solid #FFA51F" cellpadding="10" cellspacing="10">
							<tr>
								<td align="center">									
									<img src="'.$shop_domain.'/img/logo.jpg" />
								</td>			
							</tr>
							<tr style="border: 1px solid #FFA51F">
								<td align="center">
									<strong>'.$msg.'</strong>
								</td>			
							</tr>
							<tr>
								<td align="center">
									'.$this->l('Click').' <a href="'.$controller_link.'">'.$this->l('here').'</a> '.htmlentities($this->l('to come back to the merchant site')).'
								</td>
							</tr>
						</table>
					</center>
				</body>
			</html>';
			exit;
		}
	}
}
