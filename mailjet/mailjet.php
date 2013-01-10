<?php
/*
* 2007-2011 PrestaShop
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
*  @copyright  2007-2011 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

// Security
if (!defined('_PS_VERSION_'))
	exit;

// Include
require_once(dirname(__FILE__).'/mailjet.class.php');

class Mailjet extends Module
{
	// Html output var
	private $_html = '';

	/*
	** Construct Method
	**
	*/

	public function __construct()
	{
		// Default module variable
		$this->name = 'mailjet';
		$this->version = '1.3';
		$this->displayName = 'Mailjet';
		$this->module_key = '59cce32ad9a4b86c46e41ac95f298076';

		// Associate to module category depending of PrestaShop version
		if (version_compare(_PS_VERSION_, '1.5', '>'))
			$this->tab = 'emailing';
		else
			$this->tab = 'advertising_marketing';

		// Parent constructor
		parent::__construct();

		// Module description
		$this->description = $this->l('This modules sends through Mailjet all email coming from your Prestashop installation');

		// Detecting warnings
		if (Configuration::get('MAILJET_ACTIVATE') == 1 && (strlen(Configuration::get('MAILJET_API_KEY')) < 3 || strlen(Configuration::get('MAILJET_SECRET_KEY')) < 3))
			$this->warning = $this->l('The module is activated but api key or secret key are not correctly set.');

		// Backward compatibility
		//require(dirname(__FILE__).'/backward_compatibility/backward.php');

		// Defines ajax lang variables in way to translate them
		$this->l('Mailjet Test E-mail');
		$this->l('Hello');
		$this->l('This E-mail confirms you that Mailjet has successfully been installed on your shop.');
		$this->l('The E-mail was not successfully sent');
	}


	/*
	** Install / Uninstall Methods
	**
	*/

	public function install()
	{
		// Can't do anything else for retrocompatibility
		if (_PS_VERSION_ < '1.5')
		{
			if (md5_file(dirname(__FILE__).'/mailjet_override/Message_14.php') != md5_file(dirname(__FILE__).'/../../tools/swift/Swift/Message.php'))
			{
				@copy(dirname(__FILE__).'/mailjet_override/Message-mailjet_14.php', dirname(__FILE__).'/../../tools/swift/Swift/Message.php');
			}
		}
		else
		{
			if (md5_file(dirname(__FILE__).'/mailjet_override/Message_15.php') != md5_file(dirname(__FILE__).'/../../tools/swift/Swift/Message.php'))
			{
				@copy(dirname(__FILE__).'/mailjet_override/Message-mailjet_15.php', dirname(__FILE__).'/../../tools/swift/Swift/Message.php');
			}
		}

		// Install module
		if (!parent::install())
			return false;

		// Register on Hook displayEmailConfiguration (optional / only available in 1.5)
		$this->registerHook('displayEmailConfiguration');

		return true;
	}

	public function uninstall()
	{
		// Can't do anything else for retrocompatibility
		if (_PS_VERSION_ < '1.5')
		{
			if (md5_file(dirname(__FILE__).'/mailjet_override/Message-mailjet_14.php') == md5_file(dirname(__FILE__).'/../../tools/swift/Swift/Message.php'))
			{
				@copy(dirname(__FILE__).'/mailjet_override/Message_14.php', dirname(__FILE__).'/../../tools/swift/Swift/Message.php');
			}
		}
		else
		{
			if (md5_file(dirname(__FILE__).'/mailjet_override/Message-mailjet_15.php') == md5_file(dirname(__FILE__).'/../../tools/swift/Swift/Message.php'))
			{
				@copy(dirname(__FILE__).'/mailjet_override/Message_15.php', dirname(__FILE__).'/../../tools/swift/Swift/Message.php');
			}
		}

		// Disable tab
		$id_tab = (int)Db::getInstance()->getValue('SELECT `id_tab` FROM `'._DB_PREFIX_.'tab` WHERE `class_name` = \'AdminMailjet\'');
		if ($id_tab)
			Db::getInstance()->autoExecute(_DB_PREFIX_.'tab', array('active' => 0), 'UPDATE', '`id_tab` = '.(int)$id_tab);

		// Unregister on Hook displayEmailConfiguration (optional / only available in 1.5)
		$this->unregisterHook('displayEmailConfiguration');

		// Uninstall module
		Configuration::deleteByName('PS_MAIL_METHOD');
		Configuration::deleteByName('PS_MAIL_SERVER');
		Configuration::deleteByName('PS_MAIL_USER');
		Configuration::deleteByName('PS_MAIL_PASSWD');
		Configuration::deleteByName('PS_MAIL_SMTP_ENCRYPTION');
		Configuration::deleteByName('PS_MAIL_SMTP_PORT');
		Configuration::updateValue('PS_MAIL_METHOD', 1);
		Configuration::updateValue('PS_MAIL_SMTP_PORT', 25);
		if (!Configuration::deleteByName('MAILJET_AJAX_TOKEN') OR !Configuration::deleteByName('MAILJET_ACTIVATE') OR
		    !Configuration::deleteByName('MAILJET_API_KEY') OR !Configuration::deleteByName('MAILJET_SECRET_KEY') OR
		    !Configuration::deleteByName('MAILJET_TOKEN') OR !Configuration::deleteByName('MAILJET_TOKEN_IP') OR
		    !parent::uninstall())
			return false;

		return true;
	}


	/*
	** Form Config Methods
	**
	*/

	public function getContent()
	{
		// Display logo
		$this->_html .= '<p style="margin-bottom: 5px;"><img src="'.__PS_BASE_URI__.'modules/'.$this->name.'/logo-mailjet.jpg" alt="" /></p>';

		// Checking Extension
		if (!extension_loaded('curl') || !ini_get('allow_url_fopen'))
		{
			if (!extension_loaded('curl') && !ini_get('allow_url_fopen'))
				return $this->_html.$this->displayError($this->l('You must enable cURL extension and allow_url_fopen option on your server if you want to use this module.'));
			else if (!extension_loaded('curl'))
				return $this->_html.$this->displayError($this->l('You must enable cURL extension on your server if you want to use this module.'));
			else if (!ini_get('allow_url_fopen'))
				return $this->_html.$this->displayError($this->l('You must enable allow_url_fopen option on your server if you want to use this module.'));
		}

		// Post process
		$this->_postProcess();

		// Display form
		$this->_displayForm();

                return $this->_html;
        }



	/*
	** Post Process Methods
	**
	*/

	private function _postProcess()
	{
		$this->_postProcessCheckToken();
		if (Tools::isSubmit('submitMailjetConfiguration'))
			$this->_postProcessConfiguration();
		if (Tools::isSubmit('submitMailjetAuthenticationSubscribe'))
			$this->_postProcessAuthenticationSubscribe();
		if (Tools::isSubmit('submitMailjetAuthenticationLogin'))
			$this->_postProcessAuthenticationLogin();
	}

	private function _postProcessConfiguration()
	{
		global $cookie;
		
		$employee = new Employee((int)($cookie->id_employee));

		Configuration::updateValue('MAILJET_API_KEY', pSQL(Tools::getValue('mailjet_api_key')));
		Configuration::updateValue('MAILJET_SECRET_KEY', pSQL(Tools::getValue('mailjet_secret_key')));
                        
		// If mailjet activation, let's configure
		if ((int)Tools::getValue('mailjet_activation') == 1)
		{
			// Test multiple configuration
			$configTab = array(
				array('server' => 'in.mailjet.com', 'port' => 465, 'protocol' => 'tls'),
				array('server' => 'in.mailjet.com', 'port' => 465, 'protocol' => 'ssl'),
				array('server' => 'in.mailjet.com', 'port' => 587, 'protocol' => 'tcp'),
				array('server' => 'in-v4.mailjet.com', 'port' => 465, 'protocol' => 'ssl'),
				array('server' => 'in-v4.mailjet.com', 'port' => 588, 'protocol' => 'tcp'),
			);

			$result = false;
			$email = $employee->email;
			$subject = $this->l('Mailjet Test E-mail');
			$message = $this->l('Hello').",\r\n\r\n".$this->l('This E-mail confirms you that Mailjet has successfully been installed on your shop.');
			foreach ($configTab as $config)
				if ($result !== true)
				{
					$result = Mail::sendMailTest(true, $config['server'], $message, $subject, "text/plain", $email, $email, Configuration::get('MAILJET_API_KEY'), Configuration::get('MAILJET_SECRET_KEY'), $config['port'], $config['protocol']);
					if ($result === true)
					{
						Configuration::updateValue('MAILJET_ACTIVATE', (int)(Tools::getValue('mailjet_activation')));
						Configuration::updateValue('PS_MAIL_METHOD', 2);
						Configuration::updateValue('PS_MAIL_SERVER', pSQL($config['server']));
						Configuration::updateValue('PS_MAIL_USER', pSQL(Configuration::get('MAILJET_API_KEY')));
						Configuration::updateValue('PS_MAIL_PASSWD', pSQL(Configuration::get('MAILJET_SECRET_KEY')));
						Configuration::updateValue('PS_MAIL_SMTP_ENCRYPTION', pSQL($config['protocol']));
						Configuration::updateValue('PS_MAIL_SMTP_PORT', pSQL($config['port']));
						$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
					}
				}
			if ($result !== true)
				$this->_html .= $this->displayError($this->l('Settings failed'));
		}
		else
		{
			Configuration::updateValue('PS_MAIL_METHOD', 1);
			Configuration::updateValue('PS_MAIL_SERVER', "");
			Configuration::updateValue('PS_MAIL_USER', "");
			Configuration::updateValue('PS_MAIL_PASSWD', "");
			Configuration::updateValue('PS_MAIL_SMTP_ENCRYPTION', "");
			Configuration::updateValue('PS_MAIL_SMTP_PORT', 25);
		}
	}


	private function _postProcessAuthenticationSubscribe()
	{
		// Check password
		if (Tools::getValue('mailjet_password') == '' || Tools::getValue('mailjet_password') != Tools::getValue('mailjet_password_confirmation'))
		{
			$this->_html .= $this->displayError($this->l('Your password and confirmation password are differents, please check again.'));
			return false;
		}

		// Building request
		$currency = new Currency((int)Configuration::get('PS_CURRENCY_DEFAULT'));
		$employee = new Employee((int)(Context::getContext()->cookie->id_employee));
		$localesList = array('us' => 'en_US', 'gb' => 'en_GB', 'uk' => 'en_GB', 'fr' => 'fr_FR', 'de' => 'de_DE');

		$mailjet = new MailjetAPI();
		$params = array(
			'email' => $employee->email,
			'password' => Tools::getValue('mailjet_password'),
			'confirm_password' => Tools::getValue('mailjet_password_confirmation'),
			'locale' => $localesList[strtolower(Configuration::get('PS_LOCALE_COUNTRY'))],
			'currency' => $currency->iso_code,
			'timezone' => Configuration::get('PS_TIMEZONE'),
			'firstname' => $employee->firstname,
			'lastname' => $employee->lastname,
			'address_country' => Configuration::get('PS_LOCALE_COUNTRY'),
		);

		// Display error or confirmation message
		if (!$mailjet->sendRequest('userRegister', $params, 'POST'))
			$this->_html .= $this->displayError($this->l('MailJet could not perform the subscribtion request, please check your data or try again later.'));
		else
			$this->_html .= $this->displayConfirmation($this->l('Your subscribtion have been performed successfully, you will received an e-mail shortly.')).'<script>$(document).ready(function() { $("#subscribeFormDiv").fadeOut("slow"); });</script>';
	}

	private function _postProcessAuthenticationLogin()
	{
		// Test credentials
		$mailjet = new MailjetAPI();
		$mailjet->apiKey = pSQL(Tools::getValue('mailjet_api_key'));
		$mailjet->secretKey = pSQL(Tools::getValue('mailjet_secret_key'));
		$params = array(
			'apikey' => $mailjet->apiKey,
			'allowed_access' => array('campaigns', 'contacts', 'stats', 'preferences'),
		);

		// Display error or confirmation message
		if (!$mailjet->sendRequest('apiKeyauthenticate', $params, 'POST'))
			$this->_html .= $this->displayError($this->l('MailJet did not recognised your credentials, please try again.'));
		else
		{
			// Confirmation message
			$this->_html .= $this->displayConfirmation($this->l('Your credentials are correct, your module is now enabled'));

			// Save configuration
			Configuration::updateValue('MAILJET_ACTIVATE', 0);
			Configuration::updateValue('MAILJET_API_KEY', pSQL(Tools::getValue('mailjet_api_key')));
			Configuration::updateValue('MAILJET_SECRET_KEY', pSQL(Tools::getValue('mailjet_secret_key')));
			Configuration::updateValue('MAILJET_TOKEN', pSQL($mailjet->_response->token));
			Configuration::updateValue('MAILJET_TOKEN_IP', pSQL($_SERVER['REMOTE_ADDR']));

			// Create or enable tab
			$id_tab = (int)Db::getInstance()->getValue('SELECT `id_tab` FROM `'._DB_PREFIX_.'tab` WHERE `class_name` = \'AdminMailjet\'');
			if ($id_tab)
				Db::getInstance()->autoExecute(_DB_PREFIX_.'tab', array('active' => 1), 'UPDATE', '`id_tab` = '.(int)$id_tab);
			else
			{
				$tab = new Tab();
				$tab->class_name = 'AdminMailjet';
				$tab->id_parent = Db::getInstance()->getValue('SELECT `id_tab` FROM `'._DB_PREFIX_.'tab` WHERE `class_name` = \'AdminCustomers\'');
				$tab->module = 'mailjet';
				$tab->name[(int)(Configuration::get('PS_LANG_DEFAULT'))] = 'Mailjet';
				$tab->add();
			}
		}
	}

	private function _postProcessCheckToken()
	{
		// If credentials are set and remote addr do not match with the one in database, we regenerate token
		if (Configuration::get('MAILJET_API_KEY') != '' && Configuration::get('MAILJET_SECRET_KEY') != '' &&
		    Configuration::get('MAILJET_TOKEN_IP') != pSQL($_SERVER['REMOTE_ADDR']))
		{
			// Test credentials
			$mailjet = new MailjetAPI();
			$mailjet->apiKey = pSQL(Configuration::get('MAILJET_API_KEY'));
			$mailjet->secretKey = pSQL(Configuration::get('MAILJET_SECRET_KEY'));
			$params = array(
				'src' => (version_compare(_PS_VERSION_, '1.5', '>') ? 'prestashop-1.5' : 'prestashop-1.4'),
				'apikey' => $mailjet->apiKey,
				'allowed_access' => array('campaigns', 'contacts', 'stats', 'preferences'),
			);

			// Display error or confirmation message
			if (!$mailjet->sendRequest('apiKeyauthenticate', $params, 'POST'))
				$this->_html .= $this->displayError($this->l('MailJet did not recognised your credentials, please try again.'));
			else
			{
				Configuration::updateValue('MAILJET_TOKEN', pSQL($mailjet->_response->token));
				Configuration::updateValue('MAILJET_TOKEN_IP', pSQL($_SERVER['REMOTE_ADDR']));
			}
		}
	}


	/*
	** Display Form Methods
	**
	*/

	private function _displayForm()
	{
		//if (Configuration::get('MAILJET_API_KEY'))
			$this->_displayConfigurationForm();
		/*else
		{
			if (Tools::isSubmit('submitMailjetAuthenticationForm') || Tools::isSubmit('submitMailjetAuthenticationLogin') || Tools::isSubmit('submitMailjetAuthenticationSubscribe'))
				$this->_displayAuthenticationForm();
			else
				$this->_displayActivationForm();
		}*/
	}

	private function _displayActivationForm()
	{
		$priceLinkTab = array(
			'fr' => 'https://fr.mailjet.com/pricing',
			'us' => 'https://www.mailjet.com/pricing',
			'de' => 'https://de.mailjet.com/pricing',
			'uk' => 'https://uk.mailjet.com/pricing',
		);
		$priceLink = $priceLinkTab['us'];
		if (isset($priceLinkTab[strtolower(Configuration::get('PS_LOCALE_COUNTRY'))]))
			$priceLink = $priceLinkTab[strtolower(Configuration::get('PS_LOCALE_COUNTRY'))];

		$this->_html .= '
		<div>
			<form action="'.htmlentities($_SERVER['REQUEST_URI']).'" method="post">
				<fieldset>
					<legend><img src="../img/admin/cog.gif" alt="" class="middle" />'.$this->l('Activation du module').'</legend>
					<p><b>'.$this->l('Cette solution est gratuite mais exige l’activation d’un compte Mailjet.').'</b></p>
					<p><input type="submit" name="submitMailjetAuthenticationForm" value="'.$this->l('Activer un compte MailJet maintenant').'" class="button" /></p>
					<br />
					<p>
						'.$this->l('L’activation d’un compte Mailjet vous permettra d’envoyer gratuitement 200 emails par jour. Les comptes payant de Mailjet vous').'<br />
						'.$this->l('permettent d’envoyer de plus grosses campagnes.').' <a href="'.$priceLink.'" target="_blank">'.$this->l('Click here').'</a> '.$this->l('pour plus de détails sur les comptes payants.').'
					</p>
					<p><b>'.$this->l('Avec un compte Mailjet vous pourrez profiter pleinement de ce module Email :').'</b></p>
					<ul style="list-style:circle inside">
						<li>'.$this->l('Créer des campagnes d’emails marketing facilement, sans connaissances graphiques ou Html.').'<br />
						'.$this->l('Pour les utilisateurs avancés, vous pourrez également importer et gérer votre code html immédiatement dans l’interface.').'</li>
						<li>'.$this->l('Gérer vos listes de contacts et segments clients en les importants directement depuis votre interface Contact PrestaShop').'</li>
						<li>'.$this->l('Analyser les statistiques d’ouvertures et de clicks de vos campagnes').'</li>
						<li>'.$this->l('Importer de manière automatique les désinscriptions, erreurs, mise en courrier indésirable suite à l’envoi d’une campagne').'<br />
						'.$this->l('Activer un compte Mailjet maintenant').'</li>
					</ul>
					<br />
					<p><input type="submit" name="submitMailjetAuthenticationForm" value="'.$this->l('Activer un compte MailJet maintenant').'" class="button" /></p>
				</fieldset>
			</form>
		</div>';
	}

	private function _displayAuthenticationForm()
	{
		global $cookie;
		
		$employee = new Employee((int)($cookie->id_employee));
		$this->_html .= '
		<div>
			<form action="'.htmlentities($_SERVER['REQUEST_URI']).'" method="post">
				<fieldset>
					<legend><img src="../img/admin/cog.gif" alt="" class="middle" />'.$this->l('Inscription ou authentification').'</legend>
					<div style="float:left;width:48%">
						<h3 style="padding-left:110px">'.$this->l('I already have a MailJet account !').'</h3><br />
						<label><b>'.$this->l('Your MailJet API Key :').'</b></label><div class="margin-form"><input size="25" type="password" name="mailjet_api_key" /></div>
						<label><b>'.$this->l('Your MailJet Secret Key :').'</b></label><div class="margin-form"><input size="25" type="password" name="mailjet_secret_key" /></div>
						<p style="padding-left:120px"><input type="submit" name="submitMailjetAuthenticationLogin" value="'.$this->l('Log with my MailJet credentials').'" class="button" /></p>
					</div>
					<div style="float:left;width:48%" id="subscribeFormDiv">
						<h3 style="padding-left:110px">'.$this->l('Create a MailJet account !').'</h3><br />
						<label><b>'.$this->l('Firstname :').'</b></label><div class="margin-form"><input size="25" style="color:black;background-color:lightgrey" type="text" value="'.$employee->firstname.'" disabled="disabled" /></div>
						<label><b>'.$this->l('Lastname :').'</b></label><div class="margin-form"><input size="25" style="color:black;background-color:lightgrey" type="text" value="'.$employee->lastname.'" disabled="disabled" /></div>
						<label><b>'.$this->l('E-mail :').'</b></label><div class="margin-form"><input size="25" style="color:black;background-color:lightgrey" type="text" value="'.$employee->email.'" disabled="disabled" /></div>
						<label><b>'.$this->l('Your website :').'</b></label><div class="margin-form"><input size="25" style="color:black;background-color:lightgrey" type="text" value="'.$_SERVER['SERVER_NAME'].'" disabled="disabled" /></div>
						<label><b>'.$this->l('Your password :').'</b></label><div class="margin-form"><input size="25" type="password" name="mailjet_password" /></div>
						<label><b>'.$this->l('Your password confirmation :').'</b></label><div class="margin-form"><input size="25" type="password" name="mailjet_password_confirmation" /></div>
						<p style="padding-left:120px"><input type="submit" name="submitMailjetAuthenticationSubscribe" value="'.$this->l('Create a MailJet account').'" class="button" /></p>
					</div>
					<br clear="left" />
				</fieldset>
			</form>
		</div>';
	}

	private function _displayConfigurationForm()
	{
		// Set Token
		// On défini le token a cet endroit pour qu'il soit lié au context shop actuel
		Configuration::updateValue('MAILJET_AJAX_TOKEN', md5(rand()), false);
		
		$mailjet_activate = false;
		if ((int)(Tools::getValue('mailjet_activation', Configuration::get('MAILJET_ACTIVATE'))) == 1)
			$mailjet_activate = true;
		$index = 'index.php?controller='.Tools::getValue('controller').'&tab='.Tools::getValue('tab').'&configure='.Tools::getValue('configure').'&token='.Tools::getValue('token').'&tab_module='.Tools::getValue('tab_module').'&module_name=mailjet';

		$this->_html .= '<div>
		<p style="margin-bottom:10px;">
			<b>'.$this->l('This module sends through Mailjet all email coming from your Prestashop installation (and most third party modules)').'.</b>
		</p>';

		if (in_array(Tools::getValue('see'), array('campaigns', 'contacts', 'stats', 'preferences')))
			$this->_html .= '<fieldset><legend><img src="../img/admin/cog.gif" alt="" class="middle" />'.ucfirst(Tools::getValue('see')).'</legend><iframe border="0" style="border:0px" width="100%" height="800px" src="https://www.mailjet.com/'.Tools::getValue('see').'?t='.Configuration::get('MAILJET_TOKEN').'"></iframe></fieldset>';
		else
			$this->_html .= '<script type="text/javascript" src="'.__PS_BASE_URI__.'modules/'.$this->name.'/ajax.js"></script>
				<form action="'.htmlentities($_SERVER['REQUEST_URI']).'" method="post">
					<fieldset>
						<legend><img src="../img/admin/cog.gif" alt="" class="middle" />'.$this->l('Settings').'</legend>
						<label>'.$this->l('Mailjet API Key:').'</label>
						<div class="margin-form">
							<input type="text" name="mailjet_api_key" id="mailjet_api_key" size="30" value="'.htmlentities(Tools::getValue('mailjet_api_key', Configuration::get('MAILJET_API_KEY'))).'" />
						</div>
						<hr size="1" style="margin-bottom: 20px;" noshade />
						<label>'.$this->l('Mailjet Secret Key').'</label>
						<div class="margin-form">
							<input type="text" name="mailjet_secret_key" id="mailjet_secret_key" size="30" value="'.htmlentities(Tools::getValue('mailjet_secret_key', Configuration::get('MAILJET_SECRET_KEY'))).'" />
						</div>
						<hr size="1" style="margin-bottom: 20px;" noshade />
						<label style="vertical-align: middle;">'.$this->l('Send Email through Mailjet:').'</label>
						<div class="margin-form" style="margin-top: 5px;">
							<input type="radio" name="mailjet_activation" value="1" style="vertical-align: middle;" '.($mailjet_activate ? 'checked="checked"' : '').' /> '.$this->l('Yes').'&nbsp;
							<input type="radio" name="mailjet_activation" id="mailjet_activation_no" value="0" style="vertical-align: middle;" '.($mailjet_activate ? '' : 'checked="checked"').' /> '.$this->l('No').'
						</div>
						<hr size="1" style="margin-bottom: 20px;" noshade />
						<div class="conf confirm" id="mailjet_test_ok" style="display:none">
							'.$this->l('Authentication successful ! Your configuration is correct.').'
						</div>
						<div class="conf error" id="mailjet_test_ko" style="display:none">
							'.$this->l('An Error has occured : ').'<span id="mailjet_error_message"></span>
							<p>'.$this->l('If you don\'t understand this error please contact').' <a href="http://fr.mailjet.com/support" target="_blank">Mailjet Support</a></p>
						</div>
						<div id="div_email_test" style="display:none">
							<p style="text-align:center">'.$this->l('E-mail From / to :').'&nbsp;<input type="text" id="email_from" value="'.htmlentities(Configuration::get('PS_SHOP_EMAIL')).'" size="40" />&nbsp;<input type="button" name="sendTestMailjet" value="'.$this->l('Send').'" class="button" rel="'.htmlentities(Configuration::get('MAILJET_AJAX_TOKEN')).'" id="button_send_mailjet" /></p>
							<hr size="1" style="margin-bottom: 20px;" noshade />
						</div>
						<center>
							'.($mailjet_activate ? '<input type="button" name="testMailjet" value="'.$this->l('Test Configuration').'" class="button" id="button_test_mailjet" /><img src="'.__PS_BASE_URI__.'modules/'.$this->name.'/ajax-mailjet.gif" id="image_ajax_mailjet" style="display:none" />&nbsp;' : '').'
							<input type="submit" name="submitMailjetConfiguration" value="'.$this->l('Save settings').'" class="button" />
						</center>
					</fieldset>
				</form>';
		$this->_html .= '</div>';
	}


}

