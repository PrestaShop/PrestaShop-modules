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
* @author PrestaShop SA <contact@prestashop.com>
* @copyright  2007-2013 PrestaShop SA
* @license	http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/

if (! defined('_PS_VERSION_'))
exit();

include_once(_PS_CLASS_DIR_.'/../classes/Customer.php');

class Mailin extends Module {

	private $post_errors = array();
	private $html_code_tracking;
	private $tracking;
	private $email;
	private $newsletter;
	private $last_name;
	private $first_name;
	
	public $error;
	
	/**
	 * class constructor
	 */
	public function __construct()
	{
		$this->name = 'mailin';
		$this->tab = 'advertising_marketing';
		$this->version = 1.0;
		
		parent::__construct();
		
		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Mailin');
		$this->description = $this->l('Synchronize your PrestaShop contacts with Mailin platform, track customer\'s orders and send transactional emails easily to your customers.');
		$this->confirmUninstall = $this->l('Are you sure you want to remove the Mailinblue module? N.B: we will enable php mail() send function (If you were using SMTP info before using Mailinblue SMTP, please update your configuration for the emails)');
		
		// Checking Extension
		if (!extension_loaded('curl') || !ini_get('allow_url_fopen'))
		{
			if (!extension_loaded('curl') && !ini_get('allow_url_fopen'))
			return $this->_html.$this->l('You must enable cURL extension and allow_url_fopen option on your server if you want to use this module.');
			else if (!extension_loaded('curl'))
			return $this->_html.$this->l('You must enable cURL extension on your server if you want to use this module.');
			else if (!ini_get('allow_url_fopen'))
			return $this->_html.$this->l('You must enable allow_url_fopen option on your server if you want to use this module.');
		}
		
		//Call the callhookRegister method to send an email to the Mailin user
		//when someone registers.
		$this->callhookRegister();
	
	}
	
	/**
	 *  Function to set the Mailin SMTP and tracking code status to 0
	 */
	public function checkSmtpStatus()
	{
		//If the Mailin tracking code status is empty we set the status to 0
		if (Configuration::get('Mailin_Tracking_Status') == '')
			Configuration::updateValue('Mailin_Tracking_Status', 0);
		//If the Mailin SMTP status is empty we set the status to 0
		if (Configuration::get('Mailin_Api_Smtp_Status') == '')
			Configuration::updateValue('Mailin_Api_Smtp_Status', 0);
		//If module is disabled, we set the default value for PrestaShop SMTP
		if (!$this->checkModuleStatus())
			$this->resetConfigMailinSmtp();
	}
	
	/**
	* When a subscriber registers we send an email to the Mailin user informing
	* that a new registration has happened.
	*/
	public function callhookRegister()
	{
		$this->newsletter = Tools::getValue('newsletter');
		$this->email = Tools::getValue('email');
		$this->first_name = Tools::getValue('customer_firstname');
		$this->last_name = Tools::getValue('customer_lastname');
		
		if (isset($this->newsletter) && $this->newsletter == 1)
			$this->subscribeByruntimeRegister($this->email, $this->first_name, $this->last_name);
	}
	
	/**
	 * Remove the default newsletter block so that we can accomodate the
	 * newsletter block of Mailin
	 */
	public function removeBlocknewsletterBlock()
	{
		if (_PS_VERSION_ <= '1.4.1.0')
			 Db::getInstance()->Execute('UPDATE  `'._DB_PREFIX_.'module` SET active = 0 WHERE name = "blocknewsletter"');
		else
			Module::disableByName('blocknewsletter');
	}
	
	/**
	* To restore the default PrestaShop newsletter block.
	*/
	public function restoreBlocknewsletterBlock()
	{
		if (_PS_VERSION_ <= '1.4.1.0')
			Db::getInstance()->Execute('UPDATE  `'._DB_PREFIX_.'module` SET active = 1 WHERE name = "blocknewsletter"');
		else
			Module::enableByName('blocknewsletter');
	}
	
	/**
	 * This method is called when installing the Mailin plugin.
	 */
	public function install()
	{
		if (parent::install() == false
			|| $this->registerHook('OrderConfirmation') === false
			|| $this->registerHook('leftColumn') === false
			|| $this->registerHook('createAccount') === false
			|| $this->registerHook('createAccountForm') === false)
			return false;
			
		if (parent::install())
		{
			Configuration::updateValue('Mailin_Newsletter_table', 1);
			
			if (Db::getInstance()->Execute('
				 CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mailin_newsletter`(
				`id` int(6) NOT NULL AUTO_INCREMENT,
				`email` varchar(255) NOT NULL,
				`newsletter_date_add` DATETIME NULL,
				`ip_registration_newsletter` varchar(15) NOT NULL,
				`http_referer` VARCHAR(255) NULL,
				`active` TINYINT(1) NOT NULL DEFAULT 1,
				PRIMARY KEY(`id`)
			) ENGINE='._MYSQL_ENGINE_.' default CHARSET=utf8'))
				return true;
		}
		
		return false;
	}
	
	/**
	 *  We create our own table and import the unregisterd emails from the default
	 *  newsletter table to the ps_mailin_newsletter table. This is used when you install
	 * the Mailin PS plugin.
	*/
	public function getOldNewsletterEmails()
	{
		Db::getInstance()->Execute('TRUNCATE table  '._DB_PREFIX_.'mailin_newsletter');
		
		if (_PS_VERSION_ >= '1.5.3.0')
			Db::getInstance()->Execute('INSERT INTO  '._DB_PREFIX_.'mailin_newsletter
			(email, newsletter_date_add, ip_registration_newsletter, http_referer, active)
			SELECT email, newsletter_date_add, ip_registration_newsletter, http_referer, active FROM '._DB_PREFIX_.'newsletter');
		else
			Db::getInstance()->Execute('INSERT INTO  '._DB_PREFIX_.'mailin_newsletter
			(email, newsletter_date_add, ip_registration_newsletter, http_referer)
			SELECT email, newsletter_date_add, ip_registration_newsletter, http_referer FROM '._DB_PREFIX_.'newsletter');
	}
	
	/**
	 *  This method restores the subscribers from the ps_mailin_newsletter table to the default table.
	 * This is used when you uninstall the Mailin PS Plugin.
	 */
	public function getRestoreOldNewsletteremails()
	{
		if (Configuration::get('Mailin_Api_Key_Status'))
				Db::getInstance()->Execute('TRUNCATE table  '._DB_PREFIX_.'newsletter');
		if (_PS_VERSION_ >= '1.5.3.0')
			Db::getInstance()->Execute('INSERT INTO  '._DB_PREFIX_.'newsletter
			(email, newsletter_date_add, ip_registration_newsletter, http_referer, active)
			SELECT email, newsletter_date_add, ip_registration_newsletter, http_referer, active FROM '._DB_PREFIX_.'mailin_newsletter');
		else
			Db::getInstance()->Execute('INSERT INTO  '._DB_PREFIX_.'newsletter
			(email, newsletter_date_add, ip_registration_newsletter, http_referer)
			SELECT email, newsletter_date_add, ip_registration_newsletter, http_referer FROM '._DB_PREFIX_.'mailin_newsletter');
	}
	
	/**
	 *  This method is used to fetch all users from the default customer table to list
	 * them in the Mailin PS plugin.
	 */
	public function getNewsletterEmails($start, $page)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT B.email, B.newsletter AS newsletter,
															 IF (B.id_customer IS NULL, 0, "customer_table") AS table_type
															 FROM '._DB_PREFIX_.'customer AS B UNION
															 SELECT A.email, A.active AS newsletter,
															 IF (A.newsletter_date_add IS NULL, 0, "mailin_newsletter_table") AS table_type
															 FROM '._DB_PREFIX_.'mailin_newsletter AS A LIMIT '.(int)$start.','.(int)$page);
	}
	
	/**
	 *  Get the total count of the registered users including both subscribed
	 * and unsubscribed in the default customer table.
	 */
	public function getTotalEmail()
	{
		return Db::getInstance()->getValue('
					SELECT count(*) AS Total
					FROM (SELECT B.email, B.newsletter AS newsletter,
					IF(B.id_customer IS NULL, 0, "customer_table") AS
					table_type FROM '._DB_PREFIX_.'customer AS B UNION
					SELECT A.email, A.active AS newsletter, IF (A.newsletter_date_add IS NULL, 0, "mailin_newsletter_table") AS
					table_type FROM '._DB_PREFIX_.'mailin_newsletter AS A) AS tbl');
	}
	
	/**
	 *  Get the total count of the subscribed and unregistered users in the default customer table.
	 */
	public function getTotalSubUnReg()
	{
		return Db::getInstance()->getValue('SELECT  count(*) as Total FROM '._DB_PREFIX_.'mailin_newsletter where active = 1');
	}
	
	/**
	 *  Get the total count of the unsubscribed and unregistered users in the default customer table.
	 */
	public function getTotalUnSubUnReg()
	{
		 return Db::getInstance()->getValue('SELECT  count(*) as Total FROM '._DB_PREFIX_.'mailin_newsletter where active = 0');
	}
	
	/**
	 *  Update a subscriber's status both on Mailin and PrestaShop.
	 */
	public function updateNewsletterStatus()
	{
		$this->newsletter = Tools::getValue('newsletter');
		$this->email = Tools::getValue('email');
		
		if (isset($this->newsletter) && $this->newsletter != '' && $this->email != '')
		{
			if ($this->newsletter == 0)
			{
				$unsubresult = $this->unsubscribeByruntime($this->email);
				$status = 0;
			}
			elseif ($this->newsletter == 1)
			{
				$subresult = $this->isEmailRegistered($this->email);
				$status = 1;
			}
			
			$result = Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'mailin_newsletter` 
												SET active="'.pSQL($status).'",
												newsletter_date_add = "'.pSQL(date('Y-m-d H:i:s')).'"
												  WHERE email = "'.pSQL($this->email).'"');
			$result = Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'customer` 
												 SET newsletter="'.pSQL($status).'",
												 newsletter_date_add = "'.pSQL(date('Y-m-d H:i:s')).'"  
												 WHERE email = "'.pSQL($this->email).'"');
		}
	}
	
	/**
	 *   Display user's newsletter subscription
	 *   This function displays both Mailin's and PrestaShop's newsletter subscription status.
	 *   It also allows you to change the newsletter subscription status.
	 */
	public function displayNewsletterEmail()
	{
		global $smarty;
		
		$sub_count = $this->totalsubscribedUser();
		$unsub_count = $this->totalUnsubscribedUser();
		$counter1 = $this->getTotalSubUnReg();
		$counter2 = $this->getTotalUnSubUnReg();
		$sub_count = $sub_count + $counter1;
		$unsub_count = $unsub_count + $counter2;
		
		$middlelabel = $this->l('You have ').' '.$sub_count.' '.
		$this->l(' contacts subscribed and ').' '.$unsub_count.' '.$this->l(' contacts unsubscribed from PrestaShop').
		'<span id="Spantextmore">'.$this->l('. For more details,   ').
		'</span><span id="Spantextless" style="display:none;">'.$this->l('. For less details,   ').
		'</span>  <a href="javascript:void(0);" id="showUserlist">'.$this->l('click here').'</a>';
		
		$smarty->assign('middlelable', $middlelabel);
		
		return $this->display(__FILE__, 'views/templates/admin/userlist.tpl');
	}
	
	public function ajaxDisplayNewsletterEmail()
	{
		global $smarty;
		
		$page = Tools::getValue('page');
		
		if (isset($page) && Configuration::get('Mailin_Api_Key_Status') == 1)
		{
			$page = (int)$page;
			$cur_page = $page;
			$page -= 1;
			$per_page = 20;
			$previous_btn = true;
			$next_btn = true;
			$first_btn = true;
			$last_btn = true;
			$start = $page * $per_page;
			$count = $this->getTotalEmail();
			$no_of_paginations = ceil($count / $per_page);
			
			if ($cur_page >= 7)
			{
				$start_loop = $cur_page - 3;
				if ($no_of_paginations > $cur_page + 3)
					$end_loop = $cur_page + 3;
				else if ($cur_page <= $no_of_paginations && $cur_page > $no_of_paginations - 6)
				{
					$start_loop = $no_of_paginations - 6;
					$end_loop = $no_of_paginations;
				} else
					$end_loop = $no_of_paginations;
			} else
			{
				$start_loop = 1;
				if ($no_of_paginations > 7)
					$end_loop = 7;
				 else
					$end_loop = $no_of_paginations;
			}
			
			$smarty->assign('previous_btn', $previous_btn);
			$smarty->assign('next_btn', $next_btn);
			$smarty->assign('cur_page', (int)$cur_page);
			$smarty->assign('first_btn', $first_btn);
			$smarty->assign('last_btn', $last_btn);
			$smarty->assign('start_loop', (int)$start_loop);
			$smarty->assign('end_loop', $end_loop);
			$smarty->assign('no_of_paginations', $no_of_paginations);
			$result = $this->getNewsletterEmails((int)$start, (int)$per_page);
			$data = $this->checkUserMailinStatus($result);
			$smarty->assign('result', $result);
			$smarty->assign('data', $data['result']);
			
			echo $this->display(__FILE__, 'views/templates/admin/ajaxuserlist.tpl');
		}
	}
	
	/**
	 * This method is used to check the subscriber's newsletter subscription status in Mailin
	 */
	public function checkUserMailinStatus($result)
	{
		$data = array();
		$userstatus = array();
		
		if (isset($result))
			foreach ($result as $value)
				$userstatus[] = $value['email'];
		
		$email = implode(',', $userstatus);
		$data['key'] = trim(Configuration::get('Mailin_Api_Key'));
		$data['webaction'] = 'USERS-STATUS';
		$data['email'] = $email;
		
		return Tools::jsonDecode($this->curlRequest($data), true);
	}
	
	/**
	 *  Returns the list of active registered and unregistered user details
	 * from both the default customer table and Mailin newsletter table.
	 */
	public function getBothNewsletteremails()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT ps_customer.email, ps_customer.newsletter as newsletter,
			if (ps_customer.id_customer is null, 0, "customer_table") as table_type
			from ps_customer UNION select ps_mailin_newsletter.email,
			ps_mailin_newsletter.active as newsletter,
			if (ps_mailin_newsletter.newsletter_date_add is null, 0, "mailin_newsletter_table") as table_type
			from ps_mailin_newsletter ');
	}
	
	/**
	 * Fetches the subscriber's details viz email address, dateime of subscription, status and returns the same 
	 * in array format.
	 */
	public function addNewUsersToDefaultList()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT ps_customer.email,
			 ps_customer.newsletter as newsletter, ps_customer.date_upd as date_add
			from ps_customer UNION select ps_mailin_newsletter.email,
			ps_mailin_newsletter.active as newsletter, ps_mailin_newsletter.newsletter_date_add as date_add
			from ps_mailin_newsletter ');
	}
	
	/**
	 * We send an array of subscriber's email address along with the local timestamp to the Mailin API server
	 * and based on the same the Mailin API server sends us a response with the current
	 * status of each of the email address.
	 */
	public function usersStatusTimeStamp()
	{
		$result = $this->addNewUsersToDefaultList();
		$timezone = date_default_timezone_get();
		$data = array();
		$userstatus = array();
		
		if (isset($result))
			foreach ($result as $value)
				$userstatus[] = implode(',', $value);
		
		$user_status = implode('|', $userstatus);
		$data['key'] = trim(Configuration::get('Mailin_Api_Key'));
		$data['webaction'] = 'UPDATE-USER-SUBSCRIPTION-STATUS';
		$data['timezone'] = $timezone;
		$data['user_status'] = $user_status;
		return Tools::jsonDecode($this->curlRequest($data), true);
	}
	
	/**
	 * Method is used to check the current status of the module whether its active or not.
	 */
	public function checkModuleStatus()
	{
		if (_PS_VERSION_ <= '1.4.8.2')
			return Db::getInstance()->getValue('SELECT `active` FROM `'._DB_PREFIX_.'module`
				WHERE `name` = \''.pSQL('mailin').'\'');
		elseif (!Module::isEnabled('mailin'))
			return false;
		return true;
	}
	
	/**
	 * Checks whether the Mailin API key and the Mailin subscription form is enabled
	 * and returns the true|false accordingly.
	 */
	public function syncSetting()
	{
		if (Configuration::get('Mailin_Api_Key_Status') == 0 || Configuration::get('Mailin_Subscribe_Setting') == 0)
			 return false;
		return $this->checkModuleStatus();
	}
	
	/**
	 * This is an automated version of the usersStatusTimeStamp method but is called using a CRON.
	 */
	public function userStatus()
	{
		if (! $this->syncSetting())
			return false;
		
		$result = $this->usersStatusTimeStamp();
		
		if (empty($result['errorMsg']))
		{
			foreach ($result as $valuearray)
			{
				foreach ($valuearray as $key => $value)
				{
					$result = Db::getInstance()->Execute('UPDATE  `'._DB_PREFIX_.'customer` 
														SET newsletter="'.pSQL($value['blacklisted']).'",
														newsletter_date_add = "'.pSQL($value['modified']).'"  
														WHERE email = "'.pSQL($value['email']).'" ');
					$result = Db::getInstance()->Execute('UPDATE  `'._DB_PREFIX_.'mailin_newsletter` 
														SET active="'.pSQL($value['blacklisted']).'",
														newsletter_date_add = "'.pSQL($value['modified']).'"  
														WHERE email = "'.pSQL($value['email']).'" ');
				}// end foreach
			}// end foreach
		}
	}
	
	/**
	 * Fetches all the subscribers of PrestaShop and adds them to the Mailin database.
	 */
	private function autoSubscribeAfterInstallation()
	{
		// select only newly added users and registered user
		$register_result = Db::getInstance()->ExecuteS('SELECT email,firstname,lastname FROM '._DB_PREFIX_.'customer WHERE newsletter = 1');
		$unregister_result = Db::getInstance()->ExecuteS('SELECT email FROM '._DB_PREFIX_.'mailin_newsletter WHERE active = 1');
		
		// registered user store in array
		if ($register_result)
			foreach ($register_result as $register_row)
					$register_email[] = array('EMAIL'=>$register_row['email'], 'PRENOM'=>$register_row['firstname'], 'NOM'=>$register_row['lastname'],'CLIENT'=>1);

		// unregistered user store in array
		if ($unregister_result)
			foreach ($unregister_result as $unregister_row)
				$register_email[] = array('EMAIL'=>$unregister_row['email'], 'PRENOM'=>'', 'NOM'=>'', 'CLIENT'=>0);

		return Tools::jsonEncode($register_email);
	}
	
	/**
	 * Resets the default SMTP settings for PrestaShop.
	 */
	public function resetConfigMailinSmtp()
	{
		Configuration::updateValue('Mailin_Api_Smtp_Status', 0);
		Configuration::updateValue('PS_MAIL_METHOD', 1);
		Configuration::updateValue('PS_MAIL_SERVER', '');
		Configuration::updateValue('PS_MAIL_USER', '');
		Configuration::updateValue('PS_MAIL_PASSWD', '');
		Configuration::updateValue('PS_MAIL_SMTP_ENCRYPTION', '');
		Configuration::updateValue('PS_MAIL_SMTP_PORT', 25);
	}
	
	/**
	 * This method is called when the user sets the API key and hits the submit button.
	 * It adds the necessary configurations for Mailin in PrestaShop which allows
	 * PrestaShop to use the Mailin settings.
	 */
	public function postProcessConfiguration()
	{
		$result_smtp = $this->trackingResult();
		
		// If mailinsmtp activation, let's configure
		if ($result_smtp->result->relay_data->status == 'enabled')
		{
			Configuration::updateValue('PS_MAIL_USER', $result_smtp->result->relay_data->data->username);
			Configuration::updateValue('PS_MAIL_PASSWD', $result_smtp->result->relay_data->data->password);
			// Test configuration
			$config = array(
				'server' => $result_smtp->result->relay_data->data->relay,
				'port' => $result_smtp->result->relay_data->data->port,
				'protocol' => 'off'
			);
			
			Configuration::updateValue('PS_MAIL_METHOD', 2);
			Configuration::updateValue('PS_MAIL_SERVER', $config['server']);
			Configuration::updateValue('PS_MAIL_SMTP_ENCRYPTION', $config['protocol']);
			Configuration::updateValue('PS_MAIL_SMTP_PORT', $config['port']);
			Configuration::updateValue('Mailin_Api_Smtp_Status', 1);
			
			return $this->l('Setting updated');
		}
		else
		{
			$this->resetConfigMailinSmtp();
			return $this->l('Your SMTP account is not activated and 
				therefore you can\'t use Mailinblue SMTP. For more informations, 
				please contact our support to: contact@mailinblue.com');
		}
	}
	
	/**
	 * Method is called by PrestaShop by default everytime the module is loaded. It checks for some
	 * basic settings and extensions like CURL and and allow_url_fopen to be enabled in the server.
	 */
	public function getContent()
	{
		global $cookie;
		
		$this->_html .= $this->addCss();
		
		//We set the default status of Mailin SMTP and tracking code to 0
		$this->checkSmtpStatus();
		
		// send test mail to check if SMTP is working or not.
		if (Tools::isSubmit('sendTestMail'))
			$this->sendMailProcess();
		
		// update SMTP configuration in PrestaShop
		if (Tools::isSubmit('smtpupdate'))
		{
			Configuration::updateValue('Mailin_Smtp_Status', Tools::getValue('smtp'));
			$this->postProcessConfiguration();
		}
		
		if (Tools::isSubmit('submitForm2'))
			$this->subscribeSettingPostProcess();
		if (Tools::isSubmit('submitUpdate'))
			$this->apiKeyPostProcessConfiguration();
		
		if (!empty($cookie->display_message) && !empty($cookie->display_message_type))
		{
			if ($cookie->display_message_type == 'ERROR')
				$this->_html .= $this->displayError($this->l($cookie->display_message));
			else
				$this->_html .= $this->displayConfirmation($this->l($cookie->display_message));
			unset($cookie->display_message, $cookie->display_message_type);
		}
		$this->displayForm();
		
		return $this->_html;
	}
	
	/**
	 * This method is called when the user sets the subscribe setting and hits the submit button.
	 * It adds the necessary configurations for Mailin in PrestaShop which allows
	 * PrestaShop to use the Mailin settings.
	 */
	public function subscribeSettingPostProcess()
	{
		$this->postValidationFormSync();
		
		if (!count($this->post_errors))
		{
			if (Configuration::get('Mailin_Subscribe_Setting') == 1)
			{
				Configuration::updateValue('Mailin_dropdown', (int)Tools::getValue('mailinddl'));
				$display_list = Tools::getValue('display_list');
				if (!empty($display_list) && isset($display_list))
				{
					$display_list = implode('|', $display_list);
					Configuration::updateValue('Mailin_Selected_List_Data', $display_list);
				}
			}
			else
				Configuration::updateValue('Mailin_Subscribe_Setting', 0);
		}
		else
		{
			$err_msg = '';
			
			foreach ($this->post_errors as $err)
				$err_msg .= '<p>'.$err.'</p>';
			
			$this->redirectPage($this->l($err_msg), 'ERROR');
		}
		$this->redirectPage($this->l('Successfully updated'), 'SUCCESS');
	}
	
	/**
	 * This method is called when the user send mail .
	 */
	public function sendMailProcess()
	{
		$title = $this->l('[Mailinblue SMTP] test email');
		$smtp_result = Tools::jsonDecode(Configuration::get('Mailin_Smtp_Result'));
		
		if ($smtp_result->result->relay_data->status == 'enabled')
		{
			$test_email = Tools::getValue('testEmail');
			
			if ($this->sendMail($test_email, $title))
				$this->redirectPage($this->l('Mail sent'), 'SUCCESS');
			else
				$this->redirectPage($this->l('Mail not sent'), 'ERROR');
		}
		else
			$this->redirectPage($this->l('Your SMTP account is not activated and therefore 
									you can\'t use Mailinblue SMTP. For more informations, 
									lease contact our support to: contact@mailinblue.com'), 'ERROR');
	}
	
	/**
	 * This method is called when the user sets the API key and hits the submit button.
	 * It adds the necessary configurations for Mailin in PrestaShop which allows
	 * PrestaShop to use the Mailin settings.
	 */
	public function apiKeyPostProcessConfiguration()
	{
		//If a user enters a new API key, we remove all records that belongs to the
		//old API key.
		$new_api_key = trim(Tools::getValue('apikey'));  // New key
		$old_api_key = trim(Configuration::get('Mailin_Api_Key')); // Old key
		
		if ($new_api_key != $old_api_key)
		{
			//Check if a key is valid
			$data = array();
			$data['key'] = $new_api_key;
			$data['webaction'] = 'DISPLAYLISTDATA';
			$res = $this->curlRequest($data);
			$rowlist = Tools::jsonDecode($res);
			if (!empty($rowlist->result))
			{
				//Reset the old SMTP configuration/settings
				$this->resetConfigMailinSmtp();
				// Reset data for old key
				Configuration::deleteByName('Mailin_First_Request');
				Configuration::deleteByName('Mailin_Subscribe_Setting');
				Configuration::deleteByName('Mailin_dropdown');
				Configuration::deleteByName('Mailin_Tracking_Status');
				Configuration::deleteByName('Mailin_Smtp_Result');
				Configuration::deleteByName('Mailin_Api_Key');
				Configuration::deleteByName('Mailin_Api_Key_Status');
				Configuration::deleteByName('Mailin_Api_Smtp_Status');
				Configuration::deleteByName('Mailin_Selected_List_Data');
			}
		}
		
		// endif User put new key after having old key
		$this->postValidation();
		
		if (! count($this->post_errors))
		{
			//If the API key is valid, we activate the module, otherwise we deactivate it.
			$status = Tools::getValue('status');
			
			if (isset($status))
				Configuration::updateValue('Mailin_Api_Key_Status', $status);
			
			$apikey = Tools::getValue('apikey');
			
			if (isset($apikey))
				Configuration::updateValue('Mailin_Api_Key', $apikey);
			
			if (Configuration::get('Mailin_Api_Key') && $status == 1)
			{
				$res = $this->getResultListValue();
				$rowlist = Tools::jsonDecode($res);
				
				if (empty($rowlist->result))
				{
					//We reset all settings  in case the API key is invalid.
					Configuration::updateValue('Mailin_Api_Key_Status', 0);
					$this->resetDataBaseValue();
					$this->resetConfigMailinSmtp();
					$this->redirectPage($this->l('API key is invalid.'), 'ERROR');
				}
				else
				{
					if (Configuration::get('Mailin_Selected_List_Data') == '' && Configuration::get('Mailin_First_Request') == '')
					{
						$this->getOldNewsletterEmails();
						$this->createFolderName();
						Configuration::updateValue('Mailin_First_Request', 1);
						Configuration::updateValue('Mailin_Subscribe_Setting', 1);
						Configuration::updateValue('Mailin_dropdown', 0);
						
						//We remove the default newsletter block since we
						//have to add the Mailin newsletter block.
						$this->removeBlocknewsletterBlock();
					}
					$this->redirectPage($this->l('Successfully updated'), 'SUCCESS');
				}
			}
		} else
		{
			$err_msg = '';
			
			foreach ($this->post_errors as $err)
				$err_msg .= '<p>'.$err.'</p>';
			
			$this->redirectPage($this->l($err_msg), 'ERROR');
		}
	}
	
	/**
	 * Redirect user to same page with message and message type (i.e. ERROR or SUCCESS)
	 */
	private function redirectPage($msg = '', $type = 'SUCCESS')
	{
		global $cookie;
		
		$cookie->display_message = $msg;
		$cookie->display_message_type = $type;
		$cookie->write();
		
		$s = empty($_SERVER['HTTPS']) ? '' : ($_SERVER['HTTPS'] == 'on') ? 's' : '';
		$sp = strtolower($_SERVER['SERVER_PROTOCOL']);
		$protocol = substr($sp, 0, strpos($sp, '/')).$s;
		$port = ($_SERVER['SERVER_PORT'] == '80') ? '' : (':'.$_SERVER['SERVER_PORT']);
		
		header('Location: '.$protocol.'://'.$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI']);
		exit;
	}
	
	/**
	 * Method to factory reset the database value
	 */
	public function resetDataBaseValue()
	{
		Configuration::updateValue('Mailin_Tracking_Status', 0);
		Configuration::updateValue('Mailin_Api_Smtp_Status', 0);
		Configuration::updateValue('Mailin_Selected_List_Data', '');
		Configuration::updateValue('Mailin_First_Request', '');
	}
	
	/**
	 * Checks if API key is specified or not.
	 */
	private function postValidation()
	{
		$apikey = Tools::getValue('apikey');
		$status = Tools::getValue('status');
	
		if (isset($apikey) && empty($apikey) && $status == 1)
			$this->post_errors[] = $this->l('API key is invalid.');
	}
	
	/**
	 * Checks if the user has selected at least one list.
	 */
	private function postValidationFormSync()
	{
		$display_list = Tools::getValue('display_list');
		
		if (isset($display_list) && empty($display_list))
			$this->post_errors[] = $this->l('Please choose atleast one list.');
	}
	
	/**
	 * Once we get all the list of the user from Mailin, we add them in
	 * multi select dropdown box.
	 */
	public function parselist()
	{
		$checkbox = '';
		$row = Tools::jsonDecode($this->getResultListValue());
		
		if (empty($row->result))
			return false;
		
		$checkbox .= '<td><div class="listData"  style="text-align:left;">
		<select id="select" name="display_list[]" multiple="multiple">';
		
		foreach ($row->result as $valuearray)
					$checkbox .= '<option value="'.(int)$valuearray->id.'" '.$this->getSelectedvalue($valuearray->id).' >
					<span style="margin-left:10px;"> '.Tools::safeOutput($valuearray->name).'</option>';
		
		$checkbox .= '</select>
		<span class="toolTip listData" 
			title="'.$this->l('Select the contact list where you want to save the contacts of your site PrestaShop. By default, we have created a list PrestaShop in your Mailinblue account and we have selected it').'"  >
			&nbsp;</span></div></td>';
		
		return '<td><label style="word-wrap:break-word; width:244px;" class="listData" >'.$this->l('Your lists').'</label></td>'.$checkbox;
	}
	
	/**
	 * Selects the list options that were already selected and saved by the user.
	 */
	public function getSelectedvalue($value)
	{
		$result = explode('|', Configuration::get('Mailin_Selected_List_Data'));
		if (in_array($value, $result))
			return 'selected="selected"';
		return false;
	}

	/**
	 * Fetches the SMTP and order tracking details
	 */
	public function trackingResult()
	{
		$data = array();
		$data['key'] = Configuration::get('Mailin_Api_Key');
		$data['webaction'] = 'TRACKINGDATA';
		$res = $this->curlRequest($data);
		Configuration::updateValue('Mailin_Smtp_Result', $res);
		return Tools::jsonDecode($res);
	}

	/**
	 * CURL function to send request to the Mailin API server
	 */
	public function curlRequest($data)
	{
		$url = 'http://ws.mailin.fr/'; // WS URL
		$ch = curl_init();
		// prepate data for curl post
		$ndata = '';

		if (is_array($data))
			foreach ($data as $key => $value)
				$ndata .= $key.'='.urlencode($value).'&';
		else
			$ndata = $data;
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Expect:'
			)
		);

		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $ndata);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Set curl to return the
		// data instead of
		// printing it to
		// the browser.
		curl_setopt($ch, CURLOPT_URL, $url);
		
		$data = curl_exec($ch);
		
		curl_close($ch);
		
		return $data;
	}
	
	/**
	 * Checks if a folder 'PrestaShop' and a list "PrestaShop" exits in the Mailin account.
	 * If they do not exits, this method creates them.
	 */
	public function createFolderCaseTwo()
	{
		$result = $this->checkFolderList();
		$list_name = 'prestashop';
		$key = Configuration::get('Mailin_Api_Key');
		if ($key == '')
		return false;
		$param = array();
		$data = array();
		$folder_id = $result[0];
		$exist_list = $result[2];
	
		if (!empty($key))
		{
			$res = $this->getResultListValue();
			$rowlist = Tools::jsonDecode($res);
			if (empty($rowlist->result))
				return false;
		}
	
		if (empty($result[1]))
		{
			// create folder
			$data['key'] = $key;
			$data['webaction'] = 'ADDFOLDER';
			$data['foldername'] = 'prestashop';
			$res = $this->curlRequest($data);
			$res = Tools::jsonDecode($res);
			$folder_id = $res->folder_id;
			// create list
			$param['key'] = $key;
			$param['listname'] = $list_name;
			$param['webaction'] = 'NEWLIST';
			$param['list_parent'] = $folder_id;
			//folder id
			$list_response = $this->curlRequest($param);
			$res = Tools::jsonDecode($list_response);
			$list_id = $res->result;
			// import old user to mailin
			
			global $cookie;
			
			$lang = new Language((int)$cookie->id_lang);
			$allemail = $this->autoSubscribeAfterInstallation();
			$data['webaction'] = 'MULTI-USERCREADIT';
			$data['key'] = $key;
			$data['lang'] = $lang->iso_code;
			$data['attributes'] = $allemail;
			$data['listid'] = $list_id;
			// List id should be optional
			Configuration::updateValue('Mailin_Selected_List_Data', trim($list_id));
			$response = $this->curlRequest($data);
		}
		elseif (empty($exist_list))
		{
			// create list
			$param['key'] = $key;
			$param['listname'] = $list_name;
			$param['webaction'] = 'NEWLIST';
			$param['list_parent'] = $folder_id;
			//folder id
			$list_response = $this->curlRequest($param);
			$res = Tools::jsonDecode($list_response);
			$list_id = $res->result;
			// import old user to mailin
			
			global $cookie;
			
			$lang = new Language((int)$cookie->id_lang);
			$allemail = $this->autoSubscribeAfterInstallation();
			$data['webaction'] = 'MULTI-USERCREADIT';
			$data['key'] = $key;
			$data['lang'] = $lang->iso_code;
			$data['attributes'] = $allemail;
			$data['listid'] = $list_id; // List id should be optional
			Configuration::updateValue('Mailin_Selected_List_Data', trim($list_id));
			$response = $this->curlRequest($data);
		}
	}
	
	/**
	 * Creates a folder with the name 'prestashop' after checking it on Mailin platform
	 * and making sure the folder name does not exists.
	 */
	public function createFolderName()
	{
		//Create the necessary attributes on the Mailin platform for PrestaShop
		$this->createAttributesName();
		//Check if the folder exists or not on Mailin platform.
		$result = $this->checkFolderList();
		
		if (empty($result[1]))
		{
			$data = array();
			$data['key'] = Configuration::get('Mailin_Api_Key');
			$data['webaction'] = 'ADDFOLDER';
			$data['foldername'] = 'prestashop';
			$res = $this->curlRequest($data);
			$res = Tools::jsonDecode($res);
			$folder_id = $res->folder_id;
			$exist_list = '';
		}
		else
		{
			$folder_id = $result[0];
			$exist_list = $result[2];
		}
		
		$this->createNewList($folder_id, $exist_list);
		// create list in mailin
		//Create the partner's name i.e. PrestaShop on Mailin platform
		$this->partnerPrestashop();
	}
	
	/**
	 * Creates a list by the name "prestashop" on user's Mailin account.
	 */
	public function createNewList($response, $exist_list)
	{
		if ($exist_list != '')
			$list_name = 'prestashop_'.date('dmY');
		else
			$list_name = 'prestashop';
			
		$data = array();
		$data['key'] = Configuration::get('Mailin_Api_Key');
		$data['listname'] = $list_name;
		$data['webaction'] = 'NEWLIST';
		$data['list_parent'] = $response;
		 //folder id
		$list_response = $this->curlRequest($data);
		$res = Tools::jsonDecode($list_response);
		$this->sendAllMailIDToMailin($res->result);
	}
	
	/**
	 * Fetches all folders and all list within each folder of the user's Mailin
	 * account and displays them to the user.
	 */
	public function checkFolderList()
	{
		$data = array();
		$data['key'] = Configuration::get('Mailin_Api_Key');
		$data['webaction'] = 'DISPLAY-FOLDERS-LISTS';
		
		if ($data['key'] == '')
			return false;
		else
		{
			$res = $this->getResultListValue();
			$rowlist = Tools::jsonDecode($res);
			if (empty($rowlist->result))
				return false;
		}
		
		$data['ids'] = '';
		//folder id
		$s_array = array();
		$list_response = $this->curlRequest($data);
		$res = Tools::jsonDecode($list_response, true);
		
		if (isset($res) && !empty($res))
		{
			foreach ($res as $key => $value)
			{
				if (strtolower($value['name']) == 'prestashop')
				{
						$s_array[] = $key;
						$s_array[] = $value['name'];
				}
				if (!empty($value['lists']) && isset($value['lists']))
				{
					foreach ($value['lists'] as $val)
						if (strtolower($val['name']) == 'prestashop')
							$s_array[] = $val['name'];
				}
			}
		}
		return $s_array;
	}
	
	/**
	 * Method is used to add the partner's name in Mailin.
	 * In this case its "PRESTASHOP".
	 */
	public function partnerPrestashop()
	{
		$data['key'] = Configuration::get('Mailin_Api_Key');
		$data['webaction'] = 'MAILIN-PARTNER';
		$data['partner'] = 'PRESTASHOP';
		$list_response = $this->curlRequest($data);
	}
	
	/**
	 * Method is used to send all the subscribers from PrestaShop to
	 * Mailin for adding / updating purpose.
	 */
	public function sendAllMailIDToMailin($list)
	{
		global $cookie;

		$lang = new Language((int)$cookie->id_lang);
		$allemail = $this->autoSubscribeAfterInstallation();
		$data = array();
		$data['webaction'] = 'MULTI-USERCREADIT';
		$data['key'] = Configuration::get('Mailin_Api_Key');
		$data['lang'] = $lang->iso_code;
		$data['attributes'] = $allemail;
		$data['listid'] = $list; // List id should be optional
		Configuration::updateValue('Mailin_Selected_List_Data', trim($list));
		$response = $this->curlRequest($data);
	}

	/**
	 * Create Normal, Transactional, Calculated and Global attributes and their values
	 * on Mailin platform. This is necessary for the PrestaShop to add subscriber's details.
	 */
	public function createAttributesName()
	{
		$data = array();

		$data['key'] = Configuration::get('Mailin_Api_Key');
		$data['webaction'] = 'ATTRIBUTES_CREATION';
		$data['normal_attributes'] = 'PRENOM,text|NOM,text|CLIENT,number';
		$data['transactional_attributes'] = 'ORDER_ID,id|ORDER_DATE,date|ORDER_PRICE,number';
		$data['calculated_value'] = 'PS_LAST_30_DAYS_CA, 
									 SUM[ORDER_PRICE,ORDER_DATE,>,NOW(-30)],true 
									 | PS_CA_USER, SUM[ORDER_PRICE],true | PS_ORDER_TOTAL, COUNT[ORDER_ID],true';
		$data['global_computation_value'] = 'PS_CA_LAST_30DAYS, 
											SUM[PS_LAST_30_DAYS_CA] 
											| PS_CA_TOTAL, 
											SUM[PS_CA_USER]| 
											PS_ORDERS_COUNT, 
											SUM[PS_ORDER_TOTAL]';
		return $this->curlRequest($data);
	}

	/**
	 * Unsubscribe a subscriber from Mailin.
	 */
	public function unsubscribeByruntime($email)
	{
		if (!$this->syncSetting())
			return false;

		$data = array();
		$data['key'] = Configuration::get('Mailin_Api_Key');
		$data['webaction'] = 'EMAILBLACKLIST';
		$data['blacklisted'] = '0';
		$data['email'] = $email;

		return $this->curlRequest($data);
	}

	/**
	 * Subscribe a subscriber from Mailin.
	 */
	public function subscribeByruntime($email)
	{
		if (!$this->syncSetting())
			return false;

		$fname = '';
		$lname = '';
		$client = 0;
		$attribute = $fname.'|'.$lname.'|'.$client;
		$data = array();
		$data['key'] = trim(Configuration::get('Mailin_Api_Key'));
		$data['webaction'] = 'USERCREADITM';
		$data['blacklisted'] = '';
		$data['attributes_name'] = 'PRENOM|NOM|CLIENT';
		$data['attributes_value'] = '';
		$data['category'] = '';
		$data['email'] = $email;
		$data['listid'] = Configuration::get('Mailin_Selected_List_Data');

		return $this->curlRequest($data);
	}

	/**
	 * Add / Modify subscribers with their full details like Firstname, Lastname etc.
	 */
	public function subscribeByruntimeRegister($email, $fname, $lname)
	{
		if (!$this->syncSetting())
			return false;

		$client = 1;
		$attribute = $fname.'|'.$lname.'|'.$client;
		$data = array();
		$data['key'] = trim(Configuration::get('Mailin_Api_Key'));
		$data['webaction'] = 'USERCREADITM';
		$data['email'] = $email;
		$data['blacklisted'] = '';
		$data['attributes_name'] = 'PRENOM|NOM|CLIENT';
		$data['attributes_value'] = $attribute;
		$data['category'] = '';
		$data['listid'] = Configuration::get('Mailin_Selected_List_Data');
		$this->curlRequest($data);
	}

	/**
	 * Checks whether a subscriber is registered in the mailin_newsletter table.
	 * If they are registered, we subscriber them on Mailin.
	 */
	private function isEmailRegistered($customer_email)
	{
		if (Db::getInstance()->getRow('SELECT `email` FROM '._DB_PREFIX_.'mailin_newsletter
										WHERE `email` = \''.pSQL($customer_email).'\''))
				$this->subscribeByruntime($customer_email);
		elseif ($registered = Db::getInstance()->getRow('SELECT firstname, lastname FROM
			'._DB_PREFIX_.'customer WHERE `email` = \''.pSQL($customer_email).'\''))
			$this->subscribeByruntimeRegister($customer_email, $registered['firstname'], $registered['lastname']);
	}

	/**
	 * Displays the tracking code in the code block.
	 */
	public function codeDeTracking()
	{
		$this->html_code_tracking .= '<br/>
			<table class="table hidetableblock" style="margin-top:15px;" cellspacing="0" cellpadding="0" width="100%">
			<thead>
			<tr>
			<th colspan="2">'.$this->l('Code tracking').'</th>
			</tr>
			</thead>';

		return $this->html_code_tracking .= '
			<tr><td><span style="word-wrap:break-word;text-align:left; width:auto;"> 
			'.$this->l('Do you want to install a tracking code when validating an order').'
			</span>
			<input type="radio" class="script" id="yesradio" style="margin-right:10px;"name="script" value="1"
			'.(Configuration::get('Mailin_Tracking_Status') ? 'checked="checked" ' : '').'/>'.$this->l('Yes').'
			<input type="radio" class="script" id="noradio" style="margin-left:20px;margin-right:10px;"
			name="script" value="0" '.(! Configuration::get('Mailin_Tracking_Status') ? 'checked="checked" ' : '').'/>'
			.$this->l('No').'
			<span class="toolTip" 
			title="'.$this->l('This feature will allow you to transfer all your customers orders from PrestaShop into Mailinblue to implement your email marketing strategy.').'">
			&nbsp;</span>
			</td></tr></table>';
	}
	
	/**
	 * This method is used to show options to the user whether the user wants the plugin to manage
	 * their subscribers automatically.
	 */
	public function syncronizeBlockCode()
	{
		global $cookie;

		$this->_second_block_code .= '<style type="text/css">.tableblock tr td{padding:5px; border-bottom:0px;}</style>
			<form method="post" action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'">
			<table class="table tableblock hidetableblock" style="margin-top:15px;" cellspacing="0" cellpadding="0" width="100%">
			<thead>
			<tr>
			<th colspan="2">'.$this->l('Activate Mailinblue to manage subscribers').'</th>
			</tr>
			</thead>
			<tr>
			<td style="width:250px">
			<label style="word-wrap:break-word; width:244px;"> '.$this->l('Activate Mailinblue to manage subscribers').'
			</label>
			</td>
			<td><input type="radio" class="managesubscribe" id="yessmtp" 
			style="margin-right:10px;" name="managesubscribe" value="1"
			 '.(Configuration::get('Mailin_Subscribe_Setting') ? 'checked="checked" ' : '').'/>'.$this->l('Yes').'
			<input type="radio" class="managesubscribe" 
			id="nosmtp" style="margin-left:20px;margin-right:10px;" 
			name="managesubscribe" value="0" '.(! Configuration::get('Mailin_Subscribe_Setting') ? 'checked="checked" ' : '').'/>'.$this->l('No').'
			<span class="toolTip"
			title="'.$this->l('If you activate this feature, your new contacts will be automatically added to Mailinblue or unsubscribed from Mailinblue. To synchronize the other way (Mailinblue to PrestaShop), you should run the url (mentioned below) each day.').'">
			&nbsp;</span>
			</td>
			</tr>';
			$this->_second_block_code .= '<tr class="managesubscribeBlock">
			<td><label style="word-wrap:break-word; width:244px;">'.$this->l('Manage unsubscription from Front-Office').'</label>
			</td>
			<td>
			<input type="radio"  name="mailinddl" 
			style="margin-right:10px;" value="1" '.(Configuration::get('Mailin_dropdown') ? 'checked="checked" ' : '').'/>'
			.$this->l('Yes').'
			<input type="radio" 
			 style="margin-left:20px;margin-right:10px;" 
			 name="mailinddl" value="0" '.(! Configuration::get('Mailin_dropdown') ? 'checked="checked" ' : '').'/>'
			 .$this->l('No').'
			<span class="toolTip" 
			title="'.$this->l('If you activate this option, you will let your customers the possiblity to unsubscribe from your newsletter using the newsletter block displayed in the home page.').'">&nbsp;</span>
			</td>
			</tr>';
			$this->_second_block_code .= '<tr class="managesubscribeBlock">'.$this->parselist().'</tr>';
			$this->_second_block_code .= '<tr class="managesubscribeBlock"><td>&nbsp;</td>
			<td>
			<input type="submit" name="submitForm2" value="'.$this->l('Update').'" class="button" />&nbsp;
			</td>
			</tr>';
			$this->_second_block_code .= '<tr class="managesubscribeBlock" ><td colspan="2">'.$this->l('To synchronize the emails of your customers from Mailinblue platform to your e-commerce website, you should run').'
			 <a target="_blank" href="'._PS_BASE_URL_.__PS_BASE_URI__.'modules/mailin/cron.php?token='.Tools::encrypt(Configuration::get('PS_SHOP_NAME')).'">
			 '.$this->l('this link').'</a> ';
			$this->_second_block_code .= $this->l('each day.').'
			 <span class="toolTip" title="'.$this->l('Note that if you change the name of your Shop (currently ').
			 Configuration::get('PS_SHOP_NAME').$this->l(') the token value changes.').'">&nbsp;</span></td></tr></table></form>';

			return $this->_second_block_code;
	}

	/**
	 * Displays the SMTP details in the SMTP block.
	 */
	public function mailSendBySmtp()
	{
		if (Configuration::get('Mailin_Api_Smtp_Status') == 0)
			$this->resetConfigMailinSmtp();

		global $cookie;

		$this->_html_smtp_tracking .= '
			<table class="table tableblock hidetableblock" style="margin-top:15px;" 
			cellspacing="0" cellpadding="0" width="100%">
			<thead>
			<tr>
			<th colspan="2">'.$this->l('Activate Mailinblue SMTP for your transactional emails').'</th>
			</tr>
			</thead>';
		
		$this->_html_smtp_tracking .= '
		<tr><td><form method="post" action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'">
		<span style="word-wrap:break-word;text-align:left; width:auto;"> 
		'.$this->l('Activate Mailinblue SMTP for your transactional emails').'
	   </span>
		<input type="radio" class="smtptestclick" id="yessmtp" 
		style="margin-right:10px;"name="smtp" 
		value="1" '.(Configuration::get('Mailin_Api_Smtp_Status') ? 'checked="checked" ' : '').'/>'.$this->l('Yes').'
		<input type="radio" class="smtptestclick" id="nosmtp" 
		style="margin-left:20px;margin-right:10px;" name="smtp" value="0" 
		'.(! Configuration::get('Mailin_Api_Smtp_Status') ? 'checked="checked" ' : '').'/>'.$this->l('No').'
		<span class="toolTip" title="'.$this->l('Transactional email is an expected email because it is triggered automatically after a transaction or a specific event. Common examples of transactional email are : account opening and welcome message, order shipment confirmation, shipment tracking and purchase order status, registration via a contact form, account termination, payment confirmation, invoice etc.').'">&nbsp;</span>
		</form></td></tr>';

		if (Configuration::get('Mailin_Api_Smtp_Status'))
			$st = '';
		else
			$st = 'style="display:none;"';

		$this->_html_smtp_tracking .= '
		<form method="post" name="smtp" action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'">
		<tr id="smtptest" '.$st.' ><td colspan="2">
		<div id="div_email_test">
		<p style="text-align:center">'.$this->l('Send email test From / To').' :&nbsp;
		<input type="text" size="40" name="testEmail" value="'.Configuration::get('PS_SHOP_EMAIL').'" id="email_from">
		&nbsp;
		<input type="submit"  class="button" value="'.$this->l('Send').'" name="sendTestMail"></p>
		</div>
		</td></tr></form></table>';

		return $this->_html_smtp_tracking;
	}

	/**
	 * Fetches all the list of the user from the Mailin platform.
	 */
	public function getResultListValue()
	{
		$data = array();

		$data['key'] = Configuration::get('Mailin_Api_Key');
		$data['webaction'] = 'DISPLAYLISTDATA';

		return $this->curlRequest($data);
	}

	private function displayBankWire()
	{
		$this->_html .= '<img src="../modules/mailin/img/'.$this->l('mailinblue.jpg').'" 
		style="float:left; margin:0px 15px 30px 0px;"><div style="float:left; 
		font-weight:bold; padding:25px 0px 0px 0px; color:#268CCD;">'.
		$this->l('Mailinblue : THE all-in-one plugin for your marketing and transactional emails.').'</div>
		<div class="clear"></div>';
	}

	private function displayMailin()
	{
		$this->_html .= $this->displayBankWire();
		$this->_html .= '
		<fieldset>
		<legend><img src="../modules/'.$this->name.'/logo.gif" alt="" /> '.$this->l('Mailinblue').'</legend>
		<div style="float: right; width: 340px; height: 205px; border: dashed 1px #666; padding: 8px; margin-left: 12px; margin-top:-15px;">
		<h2 style="color:#268CCD;">'.$this->l('Contact Mailinblue team').'</h2>
		<div style="clear: both;"></div>
		<p>'.$this->l(' Contact us : 
').'<br /><br />'.$this->l('Email : ').'<a href="mailto:'.$this->l('contact@mailinblue.com').'" style="color:#268CCD;">'.
$this->l('contact@mailinblue.com').'</a><br />'.$this->l('Phone : 0899 25 30 61').'</p>
		<p style="padding-top:20px;"><b>'.$this->l('For further informations, please visit our website:').
		'</b><br /><a href="'.$this->l('http://www.mailinblue.com/').'" target="_blank" 
		style="color:#268CCD;">'.$this->l('http://www.mailinblue.com/').'</a></p>
		</div>
		<p>'.$this->l('With the Mailinblue plugin, you can find everything you need to easily and efficiently send your emailing campains to your prospects and customers. ').'</p>
		<ul class="listt">
			<li>'.$this->l(' Synchronize your subscribers with Mailinblue (subscribed and unsubscribed contacts)').'</li>
			<li>'.$this->l(' Easily create good looking emailings').'</li>
			<li>'.$this->l(' Schedule your campaigns').'</li>
			<li>'.$this->l(' Track your results and optimize').'</li>
			<li>'.$this->l(' Monitor your transactional emails (purchase confirmation, password reset, etc) with a better deliverability and real-time analytics').'</li>
		</ul>
		<b>'.$this->l('Why should you use Mailinblue ?').'</b>
		<ul class="listt">
			<li>'.$this->l(' Optimized deliverability').'</li>
			<li>'.$this->l(' Unbeatable pricing – best value in the industry').'</li>
			<li>'.$this->l(' Technical support, by phone or by email').'</li>
		</ul><div style="clear:both;">&nbsp;</div>
		</fieldset>';
	}

	/**
	 * PrestaShop's default method that gets called when page loads.
	 */
	private function displayForm()
	{
		global $cookie;

		// checkFolderStatus after removing from mailin
		$this->createFolderCaseTwo();
		$lang = new Language((int)$cookie->id_lang);

		if (Configuration::get('Mailin_Api_Key_Status'))
				$str = '';
		else
				$str = 'style="display:none;"';

		$this->_html .= '<p style="margin:1.5em 0;">'.$this->displayMailin().'</p>';
		$this->_html .= '<style>.margin-form{padding: 0 0 2em 210px;}</style><fieldset style="margin-bottom:10px;">
		<legend><img src="../modules/'.$this->name.'/logo.gif" alt="" />'.$this->l('Prerequisites').'</legend>';
		$this->_html .= '<label">- 
		'.$this->l('You should have a Mailinblue account. You can create a free account here : ').
		'<a href="'.$this->l('http://www.mailinblue.com/').'"  target="_blank">&nbsp;'.$this->l('http://www.mailinblue.com/').'</a></label><br />';

		if (!extension_loaded('curl') || !ini_get('allow_url_fopen'))
			$this->_html .= '<label">- 
			'.$this->l('You must enable CURL extension and allow_url_fopen option on your server if you want to use this module.').
			'</label>';

		$this->_html .= '</fieldset>
		<form method="post" action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'">
		<input type ="hidden" name="customtoken" id="customtoken" value="'.Tools::encrypt(Configuration::get('PS_SHOP_NAME')).'">
		<input type ="hidden" name="page_no" id="page_no" value="1"><fieldset style="position:relative;">';
		$this->_html .= '<legend>
		<img src="'.$this->_path.'logo.gif" />'.$this->l('Settings').'</legend>
		<label>'.$this->l('Activate the Mailinblue module').'</label><div class="margin-form">
		<input type="radio" id="y" class="keyyes"  
		style="margin-right:10px;" name="status" value="1" 
		'.(Configuration::get('Mailin_Api_Key_Status') ? 'checked="checked" ' : '').'/>'.$this->l('Yes').'
		<input type="radio"  id="n" class="keyyes" 
		style="margin-left:20px;margin-right:10px;" name="status" value="0" 
		'.(! Configuration::get('Mailin_Api_Key_Status') ? 'checked="checked" ' : '').'/>'.$this->l('No').'
		</div><div class="clear"></div>';
		$this->_html .= '<div id="apikeybox"  '.$str.' ><label class="key">'.$this->l('API key').'</label>
		<div class="margin-form key">
		<input type="text" name="apikey" value="'.Tools::safeOutput(Configuration::get('Mailin_Api_Key')).'" />&nbsp;
		<span class="toolTip" 
		title="'.$this->l('Please enter your API key from your Mailinblue account and if you don\'t have it yet, please go to www.mailinblue.com and subscribe. You can then get the API key from https://my.mailinblue.com/advanced/apikey').'">
		&nbsp;</span>
		</div></div>';
		$this->_html .= '<div class="margin-form clear pspace">
		<input type="submit" name="submitUpdate" value="'.$this->l('Update').'" class="button" />&nbsp;
		</div><div class="clear"></div></fieldset></form>';

		if (Configuration::get('Mailin_Api_Key_Status') == 1)
		{
			// second block
			$this->_html .= $this->syncronizeBlockCode();
			// SMTP block
			$this->_html .= $this->mailSendBySmtp();
			// code tracking block
			$this->_html .= $this->codeDeTracking();
			$this->_html .= $this->displayNewsletterEmail();
		}

		return $this->_html;
	}

	/*
	 * Get the count of total unsubcribed registered users.
	 */
	public function totalUnsubscribedUser()
	{
		return Db::getInstance()->getValue('
			SELECT count(*) AS Total
			FROM `'._DB_PREFIX_.'customer`
			WHERE  `newsletter` = 0');
	}

	/*
	 * Get the count of total subcribed registered users.
	 */
	public function totalsubscribedUser()
	{
		return Db::getInstance()->getValue('
			SELECT count(*) AS Total
			FROM `'._DB_PREFIX_.'customer`
			WHERE  `newsletter` = 1');
	}

	/*
	 * Checks if an email address already exists in the mailin_newsletter table
	 * and returns a value accordingly.
	 */
	private function isNewsletterRegistered($customer_email)
	{
		if (Db::getInstance()->getRow('SELECT `email` FROM '._DB_PREFIX_.'mailin_newsletter 
										WHERE `email` = \''.pSQL($customer_email).'\''))
			return 1;

		if (! $registered = Db::getInstance()->getRow('SELECT `newsletter` FROM '._DB_PREFIX_.'customer 
														WHERE `email` = \''.pSQL($customer_email).'\''))
			return - 1;
		
		if ($registered['newsletter'] == '1')
			return 2;
		if ($registered['newsletter'] == '0')
			return 3;

		return 0;
	}

	/*
	 * Checks if an email address is already subscribed in the mailin_newsletter table
	 * and returns true, otherwise returns false.
	 */
	private function isNewsletterRegisteredSub($customer_email)
	{
		if (Db::getInstance()->getRow('SELECT `email` FROM '._DB_PREFIX_.'mailin_newsletter 
										WHERE `email` = \''.pSQL($customer_email).'\' and active=1'))
			return true;

		if (Db::getInstance()->getRow('SELECT `newsletter` FROM '._DB_PREFIX_.'customer 
										WHERE `email` = \''.pSQL($customer_email).'\' and newsletter=1'))
			return true;
		
		return false;
	}

	/*
	 * Checks if an email address is already unsubscribed in the mailin_newsletter table
	 * and returns true, otherwise returns false.
	 */
	private function isNewsletterRegisteredUnsub($customer_email)
	{
		if (Db::getInstance()->getRow('SELECT `email` FROM '._DB_PREFIX_.'mailin_newsletter 
										WHERE `email` = \''.pSQL($customer_email).'\' and active=0'))
			return true;
		
		if (Db::getInstance()->getRow('SELECT `newsletter` FROM '._DB_PREFIX_.'customer 
										WHERE `email` = \''.pSQL($customer_email).'\' and newsletter=0'))
			return true;
		
		return false;
	}
	
	/**
	 * This method is being called when a subsriber subscribes from the front end of PrestaShop.
	 */
	private function newsletterRegistration()
	{
		global $cookie;
		
		$ddl_value = Configuration::get('Mailin_dropdown');
		
		if ($ddl_value == 1)
			$post_action = Tools::getValue('action');
		else
			$post_action = 0;
		
		$s_new_timestamp = date('Y-m-d H:m:s');
		// get post email value
		$this->email = Tools::getValue('email');
		
		if (empty($this->email) || ! Validate::isEmail($this->email))
			return $this->error = $this->l('Invalid e-mail address');
		/* Unsubscription */
		elseif ($post_action == '1')
		{
				$register_status = $this->isNewsletterRegistered($this->email);
				$register_status_unsub = $this->isNewsletterRegisteredUnsub($this->email);
		
				if ($register_status == -1)
					return $this->error = $this->l('Email filled does not exist in our database');
				elseif ($register_status_unsub == -1)
					return $this->error = $this->l('E-mail address already unsubscribed');
		
			// update unsubscribe unregister
			if ($register_status == 1)
			{
				// email status send to remote server
				$this->unsubscribeByruntime($this->email);
				if (! Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'mailin_newsletter 
												SET `active` = 0, 
												newsletter_date_add = \''.$s_new_timestamp.'\' 
												WHERE `email` = \''.pSQL($this->email).'\''))
					return $this->error = $this->l('Error during subscription');
				return $this->valid = $this->l('Unsubscription successful');
			}
			elseif ($register_status == 2)
			{
				// email status send to remote server
				$this->unsubscribeByruntime($this->email);
				if (! Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'customer 
												SET `newsletter` = 0, 
												newsletter_date_add = \''.$s_new_timestamp.'\', 
												`ip_registration_newsletter` = \''.pSQL(Tools::getRemoteAddr()).'\' 
												WHERE `email` = \''.pSQL($this->email).'\''))
					return $this->error = $this->l('Error during subscription');
				return $this->valid = $this->l('Unsubscription successful');
			}
		}
		//To subscribe a user
		elseif ($post_action == '0')
		{
			$register_status = $this->isNewsletterRegistered($this->email);
			$register_status_sub = $this->isNewsletterRegisteredSub($this->email);
			
			if ($register_status_sub)
				return $this->error = $this->l('E-mail address already subscribed');
			
			switch ($register_status)
			{
				case -1:
					// email status send to remote server
					if (! Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'mailin_newsletter 
												(email, newsletter_date_add, ip_registration_newsletter, http_referer) 
												VALUES (\''.pSQL($this->email).'\', \''.$s_new_timestamp.'\', \''.pSQL(Tools::getRemoteAddr()).'\',
												(SELECT c.http_referer FROM '._DB_PREFIX_.'connections c WHERE c.id_guest = '.(int)$cookie->id_guest.' ORDER BY c.date_add DESC LIMIT 1))'))
					return $this->error = $this->l('Error during subscription');
				case 0:
					// email status send to remote server
					if (! Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'mailin_newsletter 
												SET `active` = 1,
												newsletter_date_add = \''.$s_new_timestamp.'\' 
												WHERE `email` = \''.pSQL($this->email).'\''))
					return $this->error = $this->l('Error during subscription');
				case 1:
					// email status send to remote server
					if (! Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'mailin_newsletter 
												SET `active` = 1,
												newsletter_date_add = \''.$s_new_timestamp.'\' 
												WHERE `email` = \''.pSQL($this->email).'\''))
					return $this->error = $this->l('Error during subscription');
				case 3:
					// email status send to remote server
					if (! Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'customer 
												SET `newsletter` = 1, 
												newsletter_date_add = \''.$s_new_timestamp.'\', 
												`ip_registration_newsletter` = \''.pSQL(Tools::getRemoteAddr()).'\' 
												WHERE `email` = \''.pSQL($this->email).'\''))
					return $this->error = $this->l('Error during subscription');
			}
			$this->subscribeByruntime($this->email);
			
			return $this->valid = $this->l('Subscription successful');
		}
	}
	
	/**
	 * Method is being called at the time of uninstalling the Mailin module.
	 */
	public function uninstall()
	{
		$this->unregisterHook('leftColumn');
		$this->unregisterHook('createAccount');
		$this->unregisterHook('createAccountForm');
		$this->unregisterHook('OrderConfirmation');
		
		if (Configuration::get('Mailin_Api_Smtp_Status'))
			$this->resetConfigMailinSmtp();
		
		// Uninstall module
		Configuration::deleteByName('Mailin_First_Request');
		Configuration::deleteByName('Mailin_Subscribe_Setting');
		Configuration::deleteByName('Mailin_dropdown');
		Configuration::deleteByName('Mailin_Tracking_Status');
		Configuration::deleteByName('Mailin_Smtp_Result');
		Configuration::deleteByName('Mailin_Api_Key');
		Configuration::deleteByName('Mailin_Api_Smtp_Status');
		Configuration::deleteByName('Mailin_Selected_List_Data');
		
		if (Configuration::get('Mailin_Newsletter_table'))
		{
			$this->restoreBlocknewsletterBlock();
			$this->getRestoreOldNewsletteremails();
			
			Db::getInstance()->Execute('DROP TABLE  '._DB_PREFIX_.'mailin_newsletter');
			
			Configuration::deleteByName('Mailin_Newsletter_table');
			Configuration::deleteByName('Mailin_Api_Key_Status');
		}
		
		return parent::uninstall();
	}

	/**
	 * Displays the newsletter on the front page of PrestaShop
	 */
	public function hookLeftColumn($params)
	{
		if (!$this->syncSetting())
			return false;

		global $smarty;
		
		$ddl_value = Configuration::get('Mailin_dropdown');
		
		if ($ddl_value == 1)
			$post_action = Tools::getValue('action');
		else
			$post_action = 1;
		
		if (Tools::isSubmit('submitNewsletter'))
		{
			$this->newsletterRegistration();
			$this->email = Tools::safeOutput(Tools::getValue('email'));
		
			if ($this->error)
			{
				$smarty->assign(array(
						'color' => 'red',
						'msg' => $this->error,
						'nw_value' => isset($this->email) ? $this->email : false,
						'nw_error' => true,
						'action' => $post_action
				));
			}
			elseif ($this->valid)
			{
				if (Configuration::get('NW_CONFIRMATION_EMAIL') && isset($post_action) && (int)$post_action == 0)
					Mail::Send((int)$params['cookie']->id_lang,
									'newsletter_conf',
									 Mail::l('Newsletter confirmation',
									 (int)$params['cookie']->id_lang),
									 array(),
									 $this->email,
									 null, null, null, null, null, dirname(__FILE__).'/mails/');
				$smarty->assign(
					array(
						'color' => 'green',
						'msg' => $this->valid,
						'nw_error' => false
					)
				);
			}
		}
		
		$smarty->assign('this_path', $this->_path);
		$smarty->assign('Mailin_dropdown', $ddl_value);
		
		return $this->display(__FILE__, 'views/templates/front/mailin.tpl');
	}
	
	/*
	 * Displays the CSS for the Mailin module.
	 */
	public function addCss()
	{
		$so = $this->l('Select option');
		$selected = $this->l('selected');
		$html = '<script>  var selectoption = "'.$so.'"; </script>';
		$html .= '<script>  var selected = "'.$selected.'"; </script>';
		$mailin_js_path = '../modules/'.$this->name.'/js/'.$this->name.'.min.js?_='.filemtime('../modules/'.$this->name.'/js/'.$this->name.'.min.js');
		$js_ddl_list = '../modules/'.$this->name.'/js/jquery.multiselect.min.js';
		$liveclickquery = '../modules/'.$this->name.'/js/jquery.livequery.min.js';
		$s_css = '../modules/'.$this->name.'/css/'.$this->name.'.css?_='.filemtime('../modules/'.$this->name.'/css/'.$this->name.'.css');

		$html .= '<link rel="stylesheet" type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/ui-lightness/jquery-ui.css" />
		<link "text/css" href="'.$s_css.'" rel="stylesheet" />
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>
		<script type="text/javascript" src="'.$js_ddl_list.'"></script>
		<script type="text/javascript" src="'.$liveclickquery.'"></script>
		<script type="text/javascript" src="'.$mailin_js_path.'"></script>';

		return $html;
	}

	/**
	 * When a user places an order, the tracking code integrates in the order confirmation page.
	 */
	public function hookOrderConfirmation($params)
	{
		if (!$this->checkModuleStatus())
			return false;

		global $cookie;

		$employee = new Customer((int)$cookie->id_customer);
		$total_to_pay = (isset($params['total_to_pay'])) ? $params['total_to_pay'] : 0;

		if (Configuration::get('Mailin_Api_Key_Status') == 1 && Configuration::get('Mailin_Tracking_Status') == 1)
		{
			$this->tracking = $this->trackingResult();
			$list = str_replace('|', ',', Configuration::get('Mailin_Selected_List_Data'));
			if(preg_match('/^[0-9,]+$/', $list))
				$list = $list;
			else
				$list = '';
			$code = '<script type="text/javascript" src="http://my-tracking-orders.googlecode.com/files/loadv2.js"></script>
					<script type="text/javascript">
					/**Code de suivi NB*/
					var nbBaseURL = (("https:" == document.location.protocol) ? "https://tracking.mailin.fr/" : "http://tracking.mailin.fr/");
					var nbJsURL = "http://my-tracking-orders.googlecode.com/files";
					loadScript(nbJsURL+"/nbv2.js",
					function(){
					/*Vous pouvez mettre vos variables personnalisées ici comme le montre l\'exemple.*/
					try {
					var nbTracker = nb.getTracker(nbBaseURL , "'.Tools::safeOutput($this->tracking->result->tracking_data->site_id).'");
					var list = ['.$list.'];
					var attributes = ["EMAIL","PRENOM","NOM","ORDER_ID","ORDER_DATE","ORDER_PRICE"];
					var values = ["'.$employee->email.'",
									"'.$employee->firstname.'",
									"'.$employee->lastname.'",
									"'.(int)Tools::getValue('id_order').'",
									"'.date('d-m-Y').'",
									"'.Tools::safeOutput($total_to_pay).'"];
					nbTracker.setListData(list);
					nbTracker.setTrackingData(attributes,values);
					nbTracker.trackPageView();
					} catch( err ) {}
					});
					</script>';

			echo html_entity_decode($code, ENT_COMPAT, 'UTF-8');
		}
	}
	
	/**
	 * Method is used to send test email to the user.
	 */
	private function sendMail($email, $title)
	{
		global $cookie;

		$toname = explode('@', $email);
		$toname = preg_replace('/[^a-zA-Z0-9]+/', ' ', $toname[0]);
		return Mail::Send(
				(int)$cookie->id_lang,
				'mailinsmtp_conf',
				Mail::l($title, (int)$cookie->id_lang),
				array('{title}'=>$title),
				$email,
				$toname,
				$this->l('contact@mailinblue.com'),
				$this->l('Mailinblue'),
				 null,
				 null,
				dirname(__FILE__).'/mails/'
			);
	}
}
