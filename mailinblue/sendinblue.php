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
* @author PrestaShop SA <contact@prestashop.com>
* @copyright  2007-2014 PrestaShop SA
* @license	http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/

if (! defined('_PS_VERSION_'))
exit();

include_once(_PS_CLASS_DIR_.'/../classes/Customer.php');
include(dirname(__FILE__).'/config.php');

class Sendinblue extends Module {

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
		global $cookie;
		$this->langid = $cookie->id_lang;
		$this->name = 'sendinblue';
		$this->tab = 'advertising_marketing';
		$this->author = 'SendinBlue';
		$this->version = 1.4;
		$pathconfig = new Pathfind();
		$this->path = $pathconfig->pathdisp();
		parent::__construct();

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('SendinBlue');
		$this->description = $this->l('Synchronize your PrestaShop contacts with SendinBlue platform, track customer\'s orders and send transactional emails easily to your customers.');
		$this->confirmUninstall = $this->l('Are you sure you want to remove the SendinBlue module? N.B: we will enable php mail() send function (If you were using SMTP info before using SendinBlue SMTP, please update your configuration for the emails)');
		$this->langCookie = $cookie;
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
		//Call the callhookRegister method to send an email to the SendinBlue user
		//when someone registers.
		$this->callhookRegister();
	}

	/**
	*  Function to set the SendinBlue SMTP and tracking code status to 0
	*/
	public function checkSmtpStatus()
	{
		//If the SendinBlue tracking code status is empty we set the status to 0
		if (Configuration::get('Sendin_Tracking_Status') == '')
			Configuration::updateValue('Sendin_Tracking_Status', 0);
		//If the Sendin SMTP status is empty we set the status to 0
		if (Configuration::get('Sendin_Api_Smtp_Status') == '')
			Configuration::updateValue('Sendin_Api_Smtp_Status', 0);
		//If module is disabled, we set the default value for PrestaShop SMTP

	}
	/**
	* When a subscriber registers we send an email to the SendinBlue user informing
	* that a new registration has happened.
	*/
	public function callhookRegister()
	{
		global $cookie;

            if ( _PS_VERSION_ >= 1.5 && Dispatcher::getInstance()->getController() == 'identity')
                {
                    if (Module::getInstanceByName('blocknewsletter')->active == 0)
                    {
                        Module::getInstanceByName('blocknewsletter')->active=1;
                        echo '<script type="text/javascript">
                            window.onload=function(){
                               jQuery("#newsletter").closest("p.checkbox").hide();
                               jQuery("#optin").closest("p.checkbox").hide();
                            };
                            </script>';
                    }
                    $this->newsletter = Tools::getValue('newsletter');
                    $this->email = Tools::getValue('email');
                    $id_country = Tools::getValue('id_country');
                    $phone_mobile = Tools::getValue('phone_mobile');
                    $this->first_name = Tools::getValue('firstname');
                    $this->last_name = Tools::getValue('lastname');
                    // Load customer data for logged in user so that we can register his/her with sendinblue

		$customer_data = $this->getCustomersByEmail($this->email);                    
			// Check if client have records in customer table
			if (count($customer_data) > 0 && !empty($customer_data[0]['id_customer']))
			{
				$this->newsletter = !empty($customer_data[0]['newsletter'])?$customer_data[0]['newsletter'] : '';
				$this->email = !empty($customer_data[0]['email'])?$customer_data[0]['email'] : '';
				$this->first_name = !empty($customer_data[0]['firstname'])?$customer_data[0]['firstname'] : '';
				$this->last_name = !empty($customer_data[0]['lastname'])?$customer_data[0]['lastname'] : '';

				// If logged in user register with newsletter
				if (isset($this->newsletter) && $this->newsletter == 1)
				{
					$id_customer = $customer_data[0]['id_customer'];
					$customer = new CustomerCore((int)$id_customer);

					// Code to get address of logged in user
					$customer_address = $customer->getAddresses((int)$customer_data[0]['id_lang']);
					$phone_mobile = '';
					$id_country = '';
					// Check if user have address data
					if ($customer_address && count($customer_address) > 0)
					{
					// Code to get latest phone number of logged in user
						$count_address = count($customer_address);
						for ($i = $count_address; $i >= 0; $i--)
						{
						$temp = 0;
						foreach ($customer_address as $select_address)
						{
								if ($temp < $select_address['date_upd'] && !empty($select_address['phone_mobile']))
								{
								$temp = $select_address['date_upd'];
								$phone_mobile = $select_address['phone_mobile'];
								$id_country = $select_address['id_country'];
								}
						}
						}
					}
					// Check if logged in user have phone number
					if (!empty($phone_mobile))
					{
						// Code to get country prefix
						$result = Db::getInstance()->getRow('SELECT `call_prefix` FROM '._DB_PREFIX_.
							'country WHERE `id_country` = \''.(int)$id_country.'\'');

						/**
						* Code to validate phone number (if we have '00' or '+' then it'll add '00' without country prefix,
						* if we have '0' then it'll add '00' with country prefix)
						*/
						$phone_mobile = $this->checkMobileNumber($phone_mobile, (!empty($result['call_prefix'])?$result['call_prefix']:''));
						$phone_mobile = (!empty($phone_mobile)) ? $phone_mobile : '';
					}
					// Code to update sendinblue with logged in user data.
					$this->subscribeByruntimeRegister($this->email, $this->first_name, $this->last_name, $phone_mobile);
				}
			}
                }
                else{
		$this->newsletter = Tools::getValue('newsletter');
		$this->email = Tools::getValue('email');
		$id_country = Tools::getValue('id_country');
		$phone_mobile = Tools::getValue('phone_mobile');
		$this->first_name = Tools::getValue('customer_firstname');
		$this->last_name = Tools::getValue('customer_lastname');

		if (isset($this->newsletter) && $this->newsletter == 1 && $this->email != '')
		{
			$result = Db::getInstance()->getRow('SELECT `call_prefix` FROM '._DB_PREFIX_.
			'country WHERE `id_country` = \''.(int)$id_country.'\'');
			$phone_mobile = $this->checkMobileNumber($phone_mobile, $result['call_prefix']);

			$phone_mobile = (!empty($phone_mobile)) ? $phone_mobile : '';

			if (isset($this->newsletter) && $this->newsletter == 1)
				$this->subscribeByruntimeRegister($this->email, $this->first_name, $this->last_name, $phone_mobile);
		}
		else
		{
			// Load customer data for logged in user so that we can register his/her with sendinblue
			$customer_data = $this->getCustomersByEmail($cookie->email);
			
			// Check if client have records in customer table
			if (count($customer_data) > 0 && !empty($customer_data[0]['id_customer']))
			{
				$this->newsletter = !empty($customer_data[0]['newsletter'])?$customer_data[0]['newsletter'] : '';
				$this->email = !empty($customer_data[0]['email'])?$customer_data[0]['email'] : '';
				$this->first_name = !empty($customer_data[0]['firstname'])?$customer_data[0]['firstname'] : '';
				$this->last_name = !empty($customer_data[0]['lastname'])?$customer_data[0]['lastname'] : '';

				// If logged in user register with newsletter
				if (isset($this->newsletter) && $this->newsletter == 1)
				{
					$id_customer = $customer_data[0]['id_customer'];
					$customer = new CustomerCore((int)$id_customer);

					// Code to get address of logged in user
					$customer_address = $customer->getAddresses((int)$cookie->id_lang);
					$phone_mobile = '';
					$id_country = '';
					// Check if user have address data
					if ($customer_address && count($customer_address) > 0)
					{
					// Code to get latest phone number of logged in user
						$count_address = count($customer_address);
						for ($i = $count_address; $i >= 0; $i--)
						{
						$temp = 0;
						foreach ($customer_address as $select_address)
						{
								if ($temp < $select_address['date_upd'] && !empty($select_address['phone_mobile']))
								{
								$temp = $select_address['date_upd'];
								$phone_mobile = $select_address['phone_mobile'];
								$id_country = $select_address['id_country'];
								}
						}
						}
					}
					// Check if logged in user have phone number
					if (!empty($phone_mobile))
					{
						// Code to get country prefix
						$result = Db::getInstance()->getRow('SELECT `call_prefix` FROM '._DB_PREFIX_.
							'country WHERE `id_country` = \''.(int)$id_country.'\'');

						/**
						* Code to validate phone number (if we have '00' or '+' then it'll add '00' without country prefix,
						* if we have '0' then it'll add '00' with country prefix)
						*/
						$phone_mobile = $this->checkMobileNumber($phone_mobile, (!empty($result['call_prefix'])?$result['call_prefix']:''));
						$phone_mobile = (!empty($phone_mobile)) ? $phone_mobile : '';
					}
					// Code to update sendinblue with logged in user data.
					$this->subscribeByruntimeRegister($this->email, $this->first_name, $this->last_name, $phone_mobile);
				}
			}
		}
                }
		$cookie->sms_message_land_id = $cookie->id_lang;
		Configuration::updateValue('Sendin_Sms_Message_Land_Id', $cookie->id_lang);
	}

	/**
	* Remove the default newsletter block so that we can accomodate the
	* newsletter block of SendinBlue
	*/
	public function removeBlocknewsletterBlock()
	{
		if (_PS_VERSION_ <= '1.4.1.0')
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'module` SET active = 0 WHERE name = "blocknewsletter"');
		else
			Module::disableByName('blocknewsletter');
	}

	/**
	* To restore the default PrestaShop newsletter block.
	*/
	public function restoreBlocknewsletterBlock()
	{
		if (_PS_VERSION_ <= '1.4.1.0')
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'module` SET active = 1 WHERE name = "blocknewsletter"');
		else
			Module::enableByName('blocknewsletter');
	}

	/**
	* This method is called when installing the SendinBlue plugin.
	*/
	public function install()
	{
		if (parent::install() == false
			|| $this->registerHook('OrderConfirmation') === false
			|| $this->registerHook('leftColumn') === false
			|| $this->registerHook('createAccount') === false
			|| $this->registerHook('createAccountForm') === false
			|| $this->registerHook('updateOrderStatus') === false)
			return false;

			Configuration::updateValue('Sendin_Newsletter_table', 1);
			Configuration::updateValue('Sendin_Notify_Cron_Executed', 0);

			if (Db::getInstance()->Execute('
				CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'sendin_newsletter`(
				`id` int(6) NOT NULL AUTO_INCREMENT,
				`email` varchar(255) NOT NULL,
				`newsletter_date_add` DATETIME NULL,
				`ip_registration_newsletter` varchar(15) NOT NULL,
				`http_referer` VARCHAR(255) NULL,
				`active` TINYINT(1) NOT NULL DEFAULT 1,
				PRIMARY KEY(`id`)
			) ENGINE='._MYSQL_ENGINE_.' default CHARSET=utf8'))
				return true;

		return false;
	}

	/**
	*  We create our own table and import the unregisterd emails from the default
	*  newsletter table to the ps_sendin_newsletter table. This is used when you install
	* the SendinBlue PS plugin.
	*/
	public function getOldNewsletterEmails()
	{
		Db::getInstance()->Execute('TRUNCATE table  '._DB_PREFIX_.'sendin_newsletter');

		if (_PS_VERSION_ >= '1.5.3.0')
			Db::getInstance()->Execute('INSERT INTO  '._DB_PREFIX_.'sendin_newsletter
			(email, newsletter_date_add, ip_registration_newsletter, http_referer, active)
			SELECT email, newsletter_date_add, ip_registration_newsletter, http_referer, active FROM '._DB_PREFIX_.'newsletter');
		else
			Db::getInstance()->Execute('INSERT INTO  '._DB_PREFIX_.'sendin_newsletter
			(email, newsletter_date_add, ip_registration_newsletter, http_referer)
			SELECT email, newsletter_date_add, ip_registration_newsletter, http_referer FROM '._DB_PREFIX_.'newsletter');
	}

	/**
	*  This method restores the subscribers from the ps_sendin_newsletter table to the default table.
	* This is used when you uninstall the SendinBlue PS Plugin.
	*/
	public function getRestoreOldNewsletteremails()
	{
		if (Configuration::get('Sendin_Api_Key_Status'))
				Db::getInstance()->Execute('TRUNCATE table  '._DB_PREFIX_.'newsletter');
		if (_PS_VERSION_ >= '1.5.3.0')
			Db::getInstance()->Execute('INSERT INTO  '._DB_PREFIX_.'newsletter
			(email, newsletter_date_add, ip_registration_newsletter, http_referer, active)
			SELECT email, newsletter_date_add, ip_registration_newsletter, http_referer, active FROM '._DB_PREFIX_.'sendin_newsletter');
		else
			Db::getInstance()->Execute('INSERT INTO  '._DB_PREFIX_.'newsletter
			(email, newsletter_date_add, ip_registration_newsletter, http_referer)
			SELECT email, newsletter_date_add, ip_registration_newsletter, http_referer FROM '._DB_PREFIX_.'sendin_newsletter');
	}

	/**
	*  This method is used to fetch all users from the default customer table to list
	* them in the SendinBlue PS plugin.
	*/
	public function getNewsletterEmails($start, $page)
	{
		return Db::getInstance()->ExecuteS('
										SELECT C.email, C.newsletter AS newsletter, '._DB_PREFIX_.'country.call_prefix, PSA.phone_mobile, C.id_customer, PSA.date_upd
											FROM '._DB_PREFIX_.'customer as C LEFT JOIN '._DB_PREFIX_.'address PSA ON (C.id_customer = PSA.id_customer and (PSA.id_customer, PSA.date_upd) IN 
											(SELECT id_customer, MAX(date_upd) upd  FROM '._DB_PREFIX_.'address GROUP BY '._DB_PREFIX_.'address.id_customer))
											LEFT JOIN '._DB_PREFIX_.'country ON '._DB_PREFIX_.'country.id_country = PSA.id_country              
											GROUP BY C.id_customer
											UNION
											(SELECT A.email, A.active AS newsletter, NULL AS call_prefix, 
											NULL AS phone_mobile, "Nclient" AS id_customer, NULL AS date_upd
											FROM '._DB_PREFIX_.'sendin_newsletter AS A)  LIMIT '.(int)$start.','.(int)$page);
	}

	/**
	*  Get the total count of the registered users including both subscribed
	* and unsubscribed in the default customer table.
	*/
	public function getTotalEmail()
	{
		$customer_count = Db::getInstance()->getValue('SELECT count(*) AS Total FROM '._DB_PREFIX_.'customer');
		$newsletter_count = Db::getInstance()->getValue('SELECT count(A.email) AS Total FROM '._DB_PREFIX_.'sendin_newsletter AS A');
		return ($customer_count + $newsletter_count);
	}

	/**
	*  Get the total count of the subscribed and unregistered users in the default customer table.
	*/
	public function getTotalSubUnReg()
	{
		return Db::getInstance()->getValue('SELECT  count(*) as Total FROM '._DB_PREFIX_.'sendin_newsletter where active = 1');
	}

	/**
	*  Get the total count of the unsubscribed and unregistered users in the default customer table.
	*/
	public function getTotalUnSubUnReg()
	{
		return Db::getInstance()->getValue('SELECT  count(*) as Total FROM '._DB_PREFIX_.'sendin_newsletter where active = 0');
	}

	/**
	*  Update a subscriber's status both on SendinBlue and PrestaShop.
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
				$data = $this->getUpdateUserData($this->email);
				if (!empty($data['phone_mobile']) || $data['phone_mobile'] != '')
				{
					$result = Db::getInstance()->getRow('SELECT `call_prefix` FROM '._DB_PREFIX_.
							'country WHERE `id_country` = \''.(int)$data['id_country'].'\'');
					$mobile = $this->checkMobileNumber($data['phone_mobile'], $result['call_prefix']);

				}
				else
					$mobile = '';
				$subresult = $this->isEmailRegistered($this->email, $mobile);
				$status = 1;
			}

			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'sendin_newsletter`
												SET active="'.pSQL($status).'",
												newsletter_date_add = "'.pSQL(date('Y-m-d H:i:s')).'"
												WHERE email = "'.pSQL($this->email).'"');
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'customer`
												SET newsletter="'.pSQL($status).'",
												newsletter_date_add = "'.pSQL(date('Y-m-d H:i:s')).'" 
												WHERE email = "'.pSQL($this->email).'"');
		}
	}

	public function getUpdateUserData($email)
	{
		//Load customer data for logged in user so that we can register his/her with sendinblue
		$customer_data = $this->getCustomersByEmail($email);
		// Check if client have records in customer table
		if (count($customer_data) > 0 && !empty($customer_data[0]['id_customer']))
		{
			$this->newsletter = !empty($customer_data[0]['newsletter'])?$customer_data[0]['newsletter'] : '';
			$this->email = !empty($customer_data[0]['email'])?$customer_data[0]['email'] : '';
			$this->first_name = !empty($customer_data[0]['firstname'])?$customer_data[0]['firstname'] : '';
			$this->last_name = !empty($customer_data[0]['lastname'])?$customer_data[0]['lastname'] : '';
				// If logged in user register with newsletter
					$id_customer = $customer_data[0]['id_customer'];
					$customer = new CustomerCore((int)$id_customer);

					// Code to get address of logged in user
					$customer_address = $customer->getAddresses((int)$customer_data[0]['id_lang']);
					$phone_mobile = '';
					$id_country = '';
					// Check if user have address data
					if ($customer_address && count($customer_address) > 0)
					{
						// Code to get latest phone number of logged in user
						$count_address = count($customer_address);
						for ($i = $count_address; $i >= 0; $i--)
						{
						$temp = 0;
						foreach ($customer_address as $select_address)
						{
								if ($temp < $select_address['date_upd'] && !empty($select_address['phone_mobile']))
								$temp = $select_address['date_upd'];
						}
						return $select_address;
						}
					}
		}
	}
	/**
	*   Display user's newsletter subscription
	*   This function displays both Sendin's and PrestaShop's newsletter subscription status.
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

		if (isset($page) && Configuration::get('Sendin_Api_Key_Status') == 1)
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
			$data = $this->checkUserSendinStatus($result);
			$smsdata = $this->fixCountyCodeinSmsCol($result);
			$smarty->assign('smsdata', $smsdata);
			$smarty->assign('result', $result);
			$smarty->assign('data', array_key_exists('result', $data) ? $data['result'] : '');

			echo $this->display(__FILE__, 'views/templates/admin/ajaxuserlist.tpl');
		}
	}
	/**
	* This method is used to fix country code in SendinBlue
	*/
	public function fixCountyCodeinSmsCol($result)
	{
		$smsdetail = array();
		if (!empty($result) && is_array($result))
		{
			foreach ($result as $detail)
			{
				if (isset($detail['phone_mobile']) && !empty($detail['phone_mobile']))
				$smsdetail[$detail['phone_mobile']] = $this->checkMobileNumber($detail['phone_mobile'], $detail['call_prefix']);
			}
		}
		return $smsdetail;
	}

	/**
	* This method is used to check the subscriber's newsletter subscription status in SendinBlue
	*/
	public function checkUserSendinStatus($result)
	{
		$data = array();
		$userstatus = array();
		if (!empty($result) && is_array($result))
			foreach ($result as $value)
				$userstatus[] = $value['email'];

		$email = implode(',', $userstatus);
		$data['key'] = trim(Configuration::get('Sendin_Api_Key'));
		$data['webaction'] = 'USERS-STATUS';
		$data['email'] = $email;

		return Tools::jsonDecode($this->curlRequest($data), true);
	}

	/**
	*  Returns the list of active registered and unregistered user details
	* from both the default customer table and SendinBlue newsletter table.
	*/
	public function getBothNewsletteremails()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT P.email, P.newsletter as newsletter,
			if (P.id_customer is null, 0, "customer_table") as table_type
			from '._DB_PREFIX_.'customer AS P UNION select Q.email,
			Q.active as newsletter,
			if (Q.newsletter_date_add is null, 0, "sendin_newsletter_table") as table_type
			from '._DB_PREFIX_.'sendin_newsletter AS Q ');
	}

	/**
	* Fetches the subscriber's details viz email address, dateime of subscription, status and returns the same
	* in array format.
	*/
	public function addNewUsersToDefaultList()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT P.email,
			P.newsletter as newsletter, P.date_upd as date_add
			from '._DB_PREFIX_.'customer AS P UNION select Q.email,
			Q.active as newsletter, Q.newsletter_date_add as date_add
			from '._DB_PREFIX_.'sendin_newsletter AS Q');
	}

	/**
	* We send an array of subscriber's email address along with the local timestamp to the SendinBlue API server
	* and based on the same the SendinBlue API server sends us a response with the current
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
		$data['key'] = trim(Configuration::get('Sendin_Api_Key'));
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
		if (_PS_VERSION_ <= '1.4.8.3')
			return Db::getInstance()->getValue('SELECT `active` FROM `'._DB_PREFIX_.'module`
				WHERE `name` = \''.pSQL('sendinblue').'\'');
		elseif (!Module::isEnabled('sendinblue'))
			return false;
		return true;
	}

	/**
	* Checks whether the SendinBlue API key and the SendinBlue subscription form is enabled
	* and returns the true|false accordingly.
	*/
	public function syncSetting()
	{
		if (Configuration::get('Sendin_Api_Key_Status') == 0 || Configuration::get('Sendin_Subscribe_Setting') == 0)
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
					$result = Db::getInstance()->Execute('UPDATE  `'._DB_PREFIX_.'sendin_newsletter`
														SET active="'.pSQL($value['blacklisted']).'",
														newsletter_date_add = "'.pSQL($value['modified']).'" 
														WHERE email = "'.pSQL($value['email']).'" ');
				}// end foreach
			}// end foreach
		}
	}

	/**
	* Fetches all the subscribers of PrestaShop and adds them to the SendinBlue database.
	*/
	private function autoSubscribeAfterInstallation()
	{
				// select only newly added users and registered user
		$register_result = Db::getInstance()->ExecuteS('SELECT  C.id_customer, C.newsletter, C.email, C.firstname, C.lastname, PSA.id_address, PSA.date_upd, PSA.phone_mobile, '._DB_PREFIX_.'country.call_prefix
													  FROM '._DB_PREFIX_.'customer as C LEFT JOIN '._DB_PREFIX_.'address PSA ON (C.id_customer = PSA.id_customer and (PSA.id_customer, PSA.date_upd) IN 
													  (SELECT id_customer, MAX(date_upd) upd  FROM ps_address GROUP BY ps_address.id_customer))
													  LEFT JOIN '._DB_PREFIX_.'country ON '._DB_PREFIX_.'country.id_country =  PSA.id_country
													  WHERE C.newsletter=1 
													  GROUP BY C.id_customer');

		$unregister_result = Db::getInstance()->ExecuteS('SELECT email FROM '._DB_PREFIX_.'sendin_newsletter WHERE active = 1');

		// registered user store in array
		if ($register_result)
			foreach ($register_result as $register_row)
			{
				if (!empty($register_row['phone_mobile']) && $register_row['phone_mobile'] != '')
						echo $mobile = $this->checkMobileNumber($register_row['phone_mobile'], $register_row['call_prefix']);
					else
						$mobile = '';

				$register_email[] = array('EMAIL'=>$register_row['email'],
											'PRENOM'=>$register_row['firstname'],
											'NOM'=>$register_row['lastname'],
											'CLIENT'=>1,
											'SMS'=>$mobile);
			}

		// unregistered user store in array
		if ($unregister_result)
			foreach ($unregister_result as $unregister_row)
				$register_email[] = array('EMAIL'=>$unregister_row['email'], 'PRENOM'=>'', 'NOM'=>'', 'CLIENT'=>0);

		return Tools::jsonEncode($register_email);
	}

	/**
	* Resets the default SMTP settings for PrestaShop.
	*/
	public function resetConfigSendinSmtp()
	{
		Configuration::updateValue('Sendin_Api_Smtp_Status', 0);
		Configuration::updateValue('PS_MAIL_METHOD', 1);
		Configuration::updateValue('PS_MAIL_SERVER', '');
		Configuration::updateValue('PS_MAIL_USER', '');
		Configuration::updateValue('PS_MAIL_PASSWD', '');
		Configuration::updateValue('PS_MAIL_SMTP_ENCRYPTION', '');
		Configuration::updateValue('PS_MAIL_SMTP_PORT', 25);
	}

	/**
	* This method is called when the user sets the API key and hits the submit button.
	* It adds the necessary configurations for SendinBlue in PrestaShop which allows
	* PrestaShop to use the SendinBlue settings.
	*/
	public function postProcessConfiguration()
	{
		$result_smtp = $this->trackingResult();

		// If Sendinsmtp activation, let's configure
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
			Configuration::updateValue('Sendin_Api_Smtp_Status', 1);

			return $this->l('Setting updated');
		}
		else
		{
			$this->resetConfigSendinSmtp();
			return $this->l('Your SMTP account is not activated and	therefore you can\'t use SendinBlue SMTP. For more informations
			, please contact our support to: contact@sendinblue.com');
		}
	}
	/**
	 * This method is called when the user sets the OrderSms and hits the submit button.
	 * It adds the necessary configurations for SendinBlue in PrestaShop which allows
	 * PrestaShop to use sms service the SendinBlue settings.
	 */
	public function saveSmsOrder()
	{
		// If Sendinsmtp activation, let's configure
		$sender_order = Tools::getValue('sender_order');
		$sender_order_message = Tools::getValue('sender_order_message');
		if (isset($sender_order) && $sender_order == '')
			return $this->redirectPage($this->l('Please fill the sender field'), 'ERROR');
		else if ($sender_order_message == '')
			return $this->redirectPage($this->l('Please fill the message field'), 'ERROR');
		else
		{
			Configuration::updateValue('Sendin_Sender_Order', Tools::getValue('sender_order'));
			Configuration::updateValue('Sendin_Sender_Order_Message', Tools::getValue('sender_order_message'));
			return $this->redirectPage($this->l('Setting updated'), 'SUCCESS');
		}
	}
	/**
	 * This method is called when the user want notification after having few credit.
	 * It adds the necessary configurations for SendinBlue in PrestaShop which allows
	 */
	public function sendSmsNotify()
	{
		Configuration::updateValue('Sendin_Notify_Value', Tools::getValue('sendin_notify_value'));
		Configuration::updateValue('Sendin_Notify_Email', Tools::getValue('sendin_notify_email'));
		return $this->redirectPage($this->l('Setting updated'), 'SUCCESS');
	}
	/**
	 * This method is called when the user test order  Sms and hits the submit button.
	 */

	public function sendOrderTestSms($sender, $message, $number, $langvalue)
	{
		global $cookie;

		$arr = array();
		$send_langvalue = $langvalue;
		$charone = substr($number, 0, 1);
		$chartwo = substr($number, 0, 2);
		if ($charone == '0' && $chartwo == '00')
		$number = $number;
		else
		{
			$number = '+'.$number;
			$number = str_replace(' ', '', $number);
		}
		$arr['to'] = $number;
		$arr['from'] = $sender;
		$arr['text'] = $message;

		$result = $this->sendSmsApi($arr);

		if (isset($result->status) && $result->status == 'OK')
			echo $this->l('Message has been sent successfully');
		else
			echo $this->l('Message has not been sent successfully');
	}
	/**
	 * This method is called when the user test Shipment  Sms and hits the submit button.
	 */
	public function sendShipmentTestSms($sender, $message, $number, $langvalue)
	{
		global $cookie;
		$arr = array();
		$send_langvalue = $langvalue;
		$charone = substr($number, 0, 1);
		$chartwo = substr($number, 0, 2);
		if ($charone == '0' && $chartwo == '00')
		$number = $number;
		else
		{
			$number = '+'.$number;
			$number = str_replace(' ', '', $number);
		}
		$arr['to'] = $number;
		$arr['from'] = $sender;
		$arr['text'] = $message;
		$result = $this->sendSmsApi($arr);
		if (isset($result->status) && $result->status == 'OK')
		echo $this->l('Message has been sent successfully');

		else
		echo $this->l('Message has not been sent successfully');
	}
	/**
	 * This method is called when the user sets the Shiping Sms and hits the submit button.
	 * It adds the necessary configurations for SendinBlue in PrestaShop which allows
	 * PrestaShop to use sms service the SendinBlue settings.
	 */
	public function saveSmsShiping()
	{
		global $cookie;
		$sender_shipment = Tools::getValue('sender_shipment');
		$sender_shipment_message = Tools::getValue('sender_shipment_message');
		if (isset($sender_shipment) && $sender_shipment == '')
			return $this->redirectPage($this->l('Please fill the sender field'), 'ERROR');
		else if ($sender_shipment_message == '')
			return $this->redirectPage($this->l('Please fill the message field'), 'ERROR');
		else
		{
			Configuration::updateValue('Sendin_Sender_Shipment', $sender_shipment);
			Configuration::updateValue('Sendin_Sender_Shipment_Message', $sender_shipment_message);
			Configuration::updateValue('Sendin_Sms_Message_Land_Id', $cookie->id_lang);
			return $this->redirectPage($this->l('Setting updated'), 'SUCCESS');
		}
	}
	/**
	 * This method is called when the user test Campaign  Sms and hits the submit button.
	 */
	public function sendTestSmsCampaign($sender, $message, $number, $langvalue)
	{
			global $cookie;

			$charone = substr($number, 0, 1);
			$chartwo = substr($number, 0, 2);
			if ($charone == '0' && $chartwo == '00')
				$number = $number;
			else
			{
				$number = '+'.$number;
				$number = str_replace(' ', '', $number);
			}
			$sender_campaign = $sender;
			$sender_campaign_number = $number;
			$sender_campaign_message = $message;
			$sender_langvalue = $langvalue;

			$arr = array();
			$arr['to'] = $sender_campaign_number;
			$arr['from'] = $sender_campaign;
			$arr['text'] = $sender_campaign_message;
			$result = $this->sendSmsApi($arr);
			if (isset($result->status) && $result->status == 'OK')
				echo $this->l('Message has been sent successfully');
			else
			echo $this->l('Message has not been sent successfully');
			exit();
	}
	/**
	 * This method is called when the user sets the Campaign Sms and hits the submit button.
	 * It adds the necessary configurations for Sendin in PrestaShop which allows
	 * PrestaShop to use sms service the SendinBlue settings.
	 */
	public function sendSmsCampaign()
	{
		$sendin_sms_choice = Tools::getValue('Sendin_Sms_Choice');

		if ($sendin_sms_choice == 1)
			$this->singleChoiceCampaign();
		else if ($sendin_sms_choice == 0)
			$this->multipleChoiceCampaign();
		else
			$this->multipleChoiceSubCampaign();

	}
	/**
	 * This method is called when the user sets the Campaign single Choic eCampaign and hits the submit button.
	 */
	public function singleChoiceCampaign()
	{
		$sender_campaign = Tools::getValue('sender_campaign');
		$sender_campaign_number = Tools::getValue('singlechoice');
		$sender_campaign_message = Tools::getValue('sender_campaign_message');
		if (isset($sender_campaign) && $sender_campaign == '')
			return $this->redirectPage($this->l('Please fill the sender field'), 'ERROR');
		else if ($sender_campaign_number == '')
			return $this->redirectPage($this->l('Please fill the message field'), 'ERROR');
		else if ($sender_campaign_message == '')
			return $this->redirectPage($this->l('Please fill the message field'), 'ERROR');
		else
		{
			$arr = array();
			$arr['to'] = $sender_campaign_number;
			$arr['from'] = $sender_campaign;
			$arr['text'] = $sender_campaign_message;
			$result = $this->sendSmsApi($arr);
			if (isset($result->status) && $result->status == 'OK')
				return $this->redirectPage($this->l('Message has been sent successfully'), 'SUCCESS');
			else
			return $this->redirectPage($this->l('Message has not been sent successfully'), 'ERROR');
		}
	}
	/**
	 * This method is called when the user sets the Campaign multiple Choic eCampaign and hits the submit button.
	 */

	public function multipleChoiceCampaign()
	{
		$sender_campaign = Tools::getValue('sender_campaign');
		$sender_campaign_message = Tools::getValue('sender_campaign_message');
		if (isset($sender_campaign) && $sender_campaign == '')
			return $this->redirectPage($this->l('Please fill the sender field'), 'ERROR');
		else if ($sender_campaign_message == '')
			return $this->redirectPage($this->l('Please fill the message field'), 'ERROR');
		else
		{
			$arr = array();
			$arr['from'] = $sender_campaign;

			$response = $this->getMobileNumber();
			foreach ($response as $value)
			{
				if (isset($value['phone_mobile']) && !empty($value['phone_mobile']))
				{
					$result = Db::getInstance()->getRow('SELECT `call_prefix` FROM '._DB_PREFIX_.
							'country WHERE `id_country` = \''.(int)$value['id_country'].'\'');
					$number = $this->checkMobileNumber($value['phone_mobile'], (!empty($result['call_prefix'])?$result['call_prefix']:''));
					$first_name   = (isset($value['firstname'])) ? $value['firstname'] : '';
					$last_name    = (isset($value['lastname'])) ? $value['lastname'] : '';
					$customer_result = Db::getInstance()->ExecuteS('SELECT id_gender,firstname,lastname FROM '._DB_PREFIX_.'customer WHERE `id_customer` = '.(int)$value['id_customer']);
					if (strtolower($first_name) === strtolower($customer_result[0]['firstname']) && strtolower($last_name) === strtolower($customer_result[0]['lastname']))
					$civility_value = (isset($customer_result[0]['id_gender'])) ? $customer_result[0]['id_gender'] : '';
					else
					$civility_value = '';

					if ($civility_value == 1)
					$civility = 'Mr.';
					else if ($civility_value == 2)
					$civility = 'Ms.';
					else if ($civility_value == 3)
					$civility = 'Miss.';
					else
					$civility = '';

					$civility_data = str_replace('{civility}', $civility, $sender_campaign_message);
					$fname = str_replace('{first_name}', $first_name, $civility_data);
					$lname = str_replace('{last_name}', $last_name."\r\n", $fname);
					$arr['text'] = $lname;
					$arr['to'] = $number;
					$this->sendSmsApi($arr);
				}
			}
		}
			return $this->redirectPage($this->l('Message has been sent successfully'), 'SUCCESS');
	}
	/**
	 * This method is called when the user sets the Campaign multiple Choic eCampaign and hits subscribed user the submit button.
	 */
	public function multipleChoiceSubCampaign()
	{
		$sender_campaign = Tools::getValue('sender_campaign');
		$sender_campaign_message = Tools::getValue('sender_campaign_message');
		if (isset($sender_campaign) && $sender_campaign == '')
			return $this->redirectPage($this->l('Please fill the sender field'), 'ERROR');
		else if ($sender_campaign_message == '')
			return $this->redirectPage($this->l('Please fill the message field'), 'ERROR');
		else
		{
			$arr = array();
			$arr['from'] = $sender_campaign;
			$response = $this->geSubstMobileNumber();
			foreach ($response as $value)
			{
				if (isset($value['phone_mobile']) && !empty($value['phone_mobile']))
				{
					$result = Db::getInstance()->getRow('SELECT `call_prefix` FROM '._DB_PREFIX_.
							'country WHERE `id_country` = \''.(int)$value['id_country'].'\'');
					$number = $this->checkMobileNumber($value['phone_mobile'], (!empty($result['call_prefix'])?$result['call_prefix']:''));
					$first_name = (isset($value['firstname'])) ? $value['firstname'] : '';
					$last_name = (isset($value['lastname'])) ? $value['lastname'] : '';
					$customer_result = Db::getInstance()->ExecuteS('SELECT id_gender,firstname,lastname FROM '._DB_PREFIX_.
					'customer WHERE `id_customer` = '.(int)$value['id_customer']);

					if (strtolower($first_name) === strtolower($customer_result[0]['firstname']) && strtolower($last_name) === strtolower($customer_result[0]['lastname']))
					$civility_value = (isset($customer_result[0]['id_gender'])) ? $customer_result[0]['id_gender'] : '';
					else
					$civility_value = '';

					if ($civility_value == 1)
					$civility = 'Mr.';
					else if ($civility_value == 2)
					$civility = 'Ms.';
					else if ($civility_value == 3)
					$civility = 'Miss.';
					else
					$civility = '';

						$civility_data = str_replace('{civility}', $civility, $sender_campaign_message);
						$fname = str_replace('{first_name}', $first_name, $civility_data);
						$lname = str_replace('{last_name}', $last_name."\r\n", $fname);
						$arr['text'] = $lname;
						$arr['to'] = $number;
						$this->sendSmsApi($arr);
				}
			}
		}
			return $this->redirectPage($this->l('Message has been sent successfully'), 'SUCCESS');
	}
	/**
	*  This method is used to fetch all users from the default customer table to list
	* them in the SendinBlue PS plugin.
	*/
	public function getMobileNumber()
	{
			global $cookie;
			$customer_data = $this->getAllCustomers();

			foreach ($customer_data as $customer_detail)
			{
				$temp = 0;
				if (count($customer_detail) > 0 && !empty($customer_detail['id_customer']))
				{
					$id_customer = $customer_detail['id_customer'];
					$customer = new CustomerCore((int)$id_customer);
					$customer_address = $customer->getAddresses((int)$cookie->id_lang);

					// Check if user have address data
					if ($customer_address && count($customer_address) > 0)
					{
					// Code to get latest phone number of logged in user
						$count_address = count($customer_address);
						for ($i = $count_address; $i >= 0; $i--)
						{
						foreach ($customer_address as $select_address)
						{
								if ($temp < $select_address['date_upd'] && !empty($select_address['phone_mobile']))
								{
									$temp = $select_address['date_upd'];
									$address_mobilephone[$select_address['id_customer']] = $select_address;
								}
						}
						}
					}
				}
			}
				return $address_mobilephone;
	}
	/**
	*  This method is used to fetch all subsribed users from the default customer table to list
	* them in the SendinBlue PS plugin.
	*/
	public function geSubstMobileNumber()
	{
			global $cookie;
			$customer_data = $this->getAllCustomers();

			foreach ($customer_data as $customer_detail)
			{
				$temp = 0;
				if (count($customer_detail) > 0 && !empty($customer_detail['id_customer']) && $customer_detail['newsletter_date_add'] > 0)
				{
					$id_customer = $customer_detail['id_customer'];
					$customer = new CustomerCore((int)$id_customer);
					$customer_address = $customer->getAddresses((int)$cookie->id_lang);

					// Check if user have address data
					if ($customer_address && count($customer_address) > 0)
					{
					// Code to get latest phone number of logged in user
						$count_address = count($customer_address);
						for ($i = $count_address; $i >= 0; $i--)
						{
						foreach ($customer_address as $select_address)
						{
								if ($temp < $select_address['date_upd'] && !empty($select_address['phone_mobile']))
								{
									$temp = $select_address['date_upd'];
									$address_mobilephone[$select_address['id_customer']] = $select_address;
								}
						}
						}
					}
				}
			}
				return $address_mobilephone;
	}
	/**
	* Send SMS from SendinBlue.
	*/
	public function sendSmsApi($array)
	{
		$data = array();
		$data['key'] = Configuration::get('Sendin_Api_Key');
		$data['webaction'] = 'SENDSMS';
		$data['to'] = $array['to'];
		$data['from'] = $array['from'];
		$data['text'] = $array['text'];
		return Tools::jsonDecode($this->curlRequest($data));
	}
	/**
	* show  SMS  credit from SendinBlue.
	*/
	public function getSmsCredit()
	{
		$data = array();
		$data['key'] = Configuration::get('Sendin_Api_Key');
		$data['webaction'] = 'USER-CURRENT-PLAN';
		$sms_credit = $this->curlRequest($data);
		$result = Tools::jsonDecode($sms_credit);
		if ($result['1']->plan_type == 'SMS')
			return $result['1']->credits;
	}
	/**
	* Method is called by PrestaShop by default everytime the module is loaded. It checks for some
	* basic settings and extensions like CURL and and allow_url_fopen to be enabled in the server.
	*/
	public function getContent()
	{
		global $cookie;

		$this->_html .= $this->addCss();

		//We set the default status of SendinBlue SMTP and tracking code to 0
		$this->checkSmtpStatus();

		// send test mail to check if SMTP is working or not.
		if (Tools::isSubmit('sendTestMail'))
			$this->sendMailProcess();
		// send test sms to check if SMS is working or not.
		if (Tools::isSubmit('sender_order_submit'))
			$this->sendOrderTestSms();
		if (Tools::isSubmit('sender_order_save'))
			$this->saveSmsOrder();
			// send test sms to check if SMS is working or not.
		if (Tools::isSubmit('sender_shipment_submit'))
			$this->sendShipmentTestSms();
		if (Tools::isSubmit('sender_shipment_save'))
			$this->saveSmsShiping();
			// send test sms to check if SMS is working or not.
		if (Tools::isSubmit('sender_campaign_save'))
			$this->sendSmsCampaign();
			// send test sms to check if SMS is working or not.
		if (Tools::isSubmit('sender_campaign_test_submit'))
			$this->sendTestSmsCampaign();
			// send test sms to check if SMS is working or not.
		if (Tools::isSubmit('notify_sms_mail'))
			$this->sendSmsNotify();
		// update SMTP configuration in PrestaShop
		if (Tools::isSubmit('smtpupdate'))
		{
			Configuration::updateValue('Sendin_Smtp_Status', Tools::getValue('smtp'));
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
	* It adds the necessary configurations for SendinBlue in PrestaShop which allows
	* PrestaShop to use the SendinBlue settings.
	*/
	public function subscribeSettingPostProcess()
	{
		$this->postValidationFormSync();

		if (!count($this->post_errors))
		{
			if (Configuration::get('Sendin_Subscribe_Setting') == 1)
			{
				Configuration::updateValue('Sendin_dropdown', (int)Tools::getValue('sendinddl'));
				$display_list = Tools::getValue('display_list');
				if (!empty($display_list) && isset($display_list))
				{
					$display_list = implode('|', $display_list);
					Configuration::updateValue('Sendin_Selected_List_Data', $display_list);
				}
			}
			else
				Configuration::updateValue('Sendin_Subscribe_Setting', 0);
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
		$title = $this->l('[SendinBlue SMTP] test email');
		$smtp_result = Tools::jsonDecode(Configuration::get('Sendin_Smtp_Result'));
		if ($smtp_result->result->relay_data->status == 'enabled')
		{
			$data_sendinblue_smtpstatus = $this->realTimeSmtpResult();
			if ($data_sendinblue_smtpstatus->result->relay_data->status == 'enabled')
			{
			$test_email = Tools::getValue('testEmail');
            if ($this->sendMail($test_email, $title))
				$this->redirectPage($this->l('Mail sent'), 'SUCCESS');
			else
				$this->redirectPage($this->l('Mail not sent'), 'ERROR');
			}
			else
			$this->redirectPage($this->l('Your SMTP account is not activated and therefore you can\'t use SendinBlue SMTP. For more informations, Please contact our support to: contact@sendinblue.com'), 'ERROR');
			
		}
		else
			$this->redirectPage($this->l('Your SMTP account is not activated and therefore you can\'t use SendinBlue SMTP. For more informations, Please contact our support to: contact@sendinblue.com'), 'ERROR');
	}

	/**
	*This method is called when the user sets the API key and hits the submit button.
	*It adds the necessary configurations for SendinBlue in PrestaShop which allows
	*PrestaShop to use the SendinBlue settings.
	*/
	public function apiKeyPostProcessConfiguration()
	{
		//If a user enters a new API key, we remove all records that belongs to the
		//old API key.
		$new_api_key = trim(Tools::getValue('apikey'));  // New key
		$old_api_key = trim(Configuration::get('Sendin_Api_Key')); // Old key

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
				// Reset data for old key
				Configuration::deleteByName('Sendin_First_Request');
				Configuration::deleteByName('Sendin_Subscribe_Setting');
				Configuration::deleteByName('Sendin_dropdown');
				Configuration::deleteByName('Sendin_Tracking_Status');
				Configuration::deleteByName('Sendin_Smtp_Result');
				Configuration::deleteByName('Sendin_Api_Key');
				Configuration::deleteByName('Sendin_Api_Key_Status');
				Configuration::deleteByName('Sendin_Api_Smtp_Status');
				Configuration::deleteByName('Sendin_Selected_List_Data');
			}
		}

		// endif User put new key after having old key
		$this->postValidation();

		if (! count($this->post_errors))
		{
			//If the API key is valid, we activate the module, otherwise we deactivate it.
			$status = Tools::getValue('status');

			if (isset($status))
				Configuration::updateValue('Sendin_Api_Key_Status', $status);

			$apikey = Tools::getValue('apikey');

			if (isset($apikey))
				Configuration::updateValue('Sendin_Api_Key', $apikey);

			if (Configuration::get('Sendin_Api_Key') && $status == 1)
			{
				$res = $this->getResultListValue();
				$rowlist = Tools::jsonDecode($res);

				if (empty($rowlist->result))
				{
					//We reset all settings  in case the API key is invalid.
					Configuration::updateValue('Sendin_Api_Key_Status', 0);
					$this->resetDataBaseValue();
					$this->resetConfigSendinSmtp();
					$this->redirectPage($this->l('API key is invalid.'), 'ERROR');
				}
				else
				{
					if (Configuration::get('Sendin_Selected_List_Data') == '' && Configuration::get('Sendin_First_Request') == '')
					{
						$this->getOldNewsletterEmails();
						$this->createFolderName();
						Configuration::updateValue('Sendin_First_Request', 1);
						Configuration::updateValue('Sendin_Subscribe_Setting', 1);
						Configuration::updateValue('Sendin_dropdown', 0);

						//We remove the default newsletter block since we
						//have to add the Sendin newsletter block.
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
		Configuration::updateValue('Sendin_Tracking_Status', 0);
		Configuration::updateValue('Sendin_Api_Smtp_Status', 0);
		Configuration::updateValue('Sendin_Selected_List_Data', '');
		Configuration::updateValue('Sendin_First_Request', '');
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
	* Once we get all the list of the user from SendinBlue, we add them in
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
			title="'.$this->l('Select the contact list where you want to save the contacts of your site PrestaShop. By default, we have created a list PrestaShop in your SendinBlue account and we have selected it').'"  >
			&nbsp;</span></div></td>';

		return '<td><label>'.$this->l('Your lists').'</label></td>'.$checkbox;
	}
	/**
	*Selects the list options that were already selected and saved by the user.
	*/
	public function getSelectedvalue($value)
	{
		$result = explode('|', Configuration::get('Sendin_Selected_List_Data'));
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
		$data['key'] = Configuration::get('Sendin_Api_Key');
		$data['webaction'] = 'TRACKINGDATA';
		$res = $this->curlRequest($data);
		Configuration::updateValue('Sendin_Smtp_Result', $res);
		return Tools::jsonDecode($res);
	}
	/**
	* Fetches the SMTP status details for send test mail 
	*/
	public function realTimeSmtpResult()
	{
		$data = array();
		$data['key'] = Configuration::get('Sendin_Api_Key');
		$data['webaction'] = 'TRACKINGDATA';
		$res = $this->curlRequest($data);
		return Tools::jsonDecode($res);
	}
	/**
	* CURL function to send request to the SendinBlue API server
	*/
	public function curlRequest($data)
	{
		if (array_key_exists('campaign_short_code', $data))
		$url = 'https://www.sendinblue.com/ws/getamd/'; // WS URL
		else
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

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
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
	* Checks if a folder 'PrestaShop' and a list "PrestaShop" exits in the SendinBlue account.
	* If they do not exits, this method creates them.
	*/
	public function createFolderCaseTwo()
	{
		$result = array();
		$result = $this->checkFolderList();
		$list_name = 'prestashop';
		$key = Configuration::get('Sendin_Api_Key');
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
			// import old user to SendinBlue

			global $cookie;

			$lang = new Language((int)$cookie->id_lang);
			$allemail = $this->autoSubscribeAfterInstallation();
			$data['webaction'] = 'MULTI-USERCREADIT';
			$data['key'] = $key;
			$data['lang'] = $lang->iso_code;
			$data['attributes'] = $allemail;
			$data['listid'] = $list_id;
			// List id should be optional
			Configuration::updateValue('Sendin_Selected_List_Data', trim($list_id));
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
			// import old user to SendinBlue

			global $cookie;

			$lang = new Language((int)$cookie->id_lang);
			$allemail = $this->autoSubscribeAfterInstallation();
			$data['webaction'] = 'MULTI-USERCREADIT';
			$data['key'] = $key;
			$data['lang'] = $lang->iso_code;
			$data['attributes'] = $allemail;
			$data['listid'] = $list_id; // List id should be optional
			Configuration::updateValue('Sendin_Selected_List_Data', trim($list_id));
			$response = $this->curlRequest($data);
		}
	}

	/**
	* Creates a folder with the name 'prestashop' after checking it on SendinBlue platform
	* and making sure the folder name does not exists.
	*/
	public function createFolderName()
	{
		//Create the necessary attributes on the SendinBlue platform for PrestaShop
		$this->createAttributesName();
		// AMD services getambassador in SendinBlue
		$this->amdRequest();
		//Check if the folder exists or not on SendinBlue platform.
		$result = $this->checkFolderList();

		if (empty($result[1]))
		{
			$data = array();
			$data['key'] = Configuration::get('Sendin_Api_Key');
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
		// create list in SendinBlue
		//Create the partner's name i.e. PrestaShop on SendinBlue platform
		$this->partnerPrestashop();
	}

	/**
	* Creates a list by the name "prestashop" on user's SendinBlue account.
	*/
	public function createNewList($response, $exist_list)
	{
		if ($exist_list != '')
			$list_name = 'prestashop_'.date('dmY');
		else
			$list_name = 'prestashop';

		$data = array();
		$data['key'] = Configuration::get('Sendin_Api_Key');
		$data['listname'] = $list_name;
		$data['webaction'] = 'NEWLIST';
		$data['list_parent'] = $response;
		//folder id
		$list_response = $this->curlRequest($data);
		$res = Tools::jsonDecode($list_response);
		$this->sendAllMailIDToSendin($res->result);
	}

	/**
	* Fetches all folders and all list within each folder of the user's SendinBlue
	* account and displays them to the user.
	*/
	public function checkFolderList()
	{
		$data = array();
		$data['key'] = Configuration::get('Sendin_Api_Key');
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
	* Method is used getambassador  Services.
	*/
	public function amdRequest()
	{
		$data['key'] = Configuration::get('Sendin_Api_Key');
		$data['campaign_id'] = '2147';
		$data['campaign_short_code'] = 'qPf7';
		$list_response = $this->curlRequest($data);
	}

	/**
	* Method is used to add the partner's name in SendinBlue.
	* In this case its "PRESTASHOP".
	*/
	public function partnerPrestashop()
	{
		$data['key'] = Configuration::get('Sendin_Api_Key');
		$data['webaction'] = 'MAILIN-PARTNER';
		$data['partner'] = 'PRESTASHOP';
		$list_response = $this->curlRequest($data);

	}

	/**
	* Method is used to send all the subscribers from PrestaShop to
	* SendinBlue for adding / updating purpose.
	*/
	public function sendAllMailIDToSendin($list)
	{
		global $cookie;

		$lang = new Language((int)$cookie->id_lang);
		$allemail = $this->autoSubscribeAfterInstallation();
		$data = array();
		$data['webaction'] = 'MULTI-USERCREADIT';
		$data['key'] = Configuration::get('Sendin_Api_Key');
		$data['lang'] = $lang->iso_code;
		$data['attributes'] = $allemail;
		$data['listid'] = $list; // List id should be optional
		Configuration::updateValue('Sendin_Selected_List_Data', trim($list));
		$response = $this->curlRequest($data);
	}

	/**
	* Create Normal, Transactional, Calculated and Global attributes and their values
	* on SendinBlue platform. This is necessary for the PrestaShop to add subscriber's details.
	*/
	public function createAttributesName()
	{
		$data = array();

		$data['key'] = Configuration::get('Sendin_Api_Key');
		$data['webaction'] = 'ATTRIBUTES_CREATION';
		$data['normal_attributes'] = 'PRENOM,text|NOM,text|SMS,text|CLIENT,number';
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
	* Unsubscribe a subscriber from SendinBlue.
	*/
	public function unsubscribeByruntime($email)
	{
		if (!$this->syncSetting())
			return false;

		$data = array();
		$data['key'] = Configuration::get('Sendin_Api_Key');
		$data['webaction'] = 'EMAILBLACKLIST';
		$data['blacklisted'] = '0';
		$data['email'] = $email;

		return $this->curlRequest($data);
	}

	/**
	* Subscribe a subscriber from SendinBlue.
	*/
	public function subscribeByruntime($email)
	{
		if (!$this->syncSetting())
			return false;
		$fname = '';
		$lname = '';
		$client = 0;
		$customer_data = $this->getCustomersByEmail($email);
		if (!empty($customer_data[0]['id_customer']) && $customer_data[0]['id_customer'] > 0)
		{
		$client = 1;
		$fname = $customer_data[0]['firstname'];
		$lname = $customer_data[0]['lastname'];
		$attribute = $fname.'|'.$lname.'|'.$client;
		}		
		$attribute = $fname.'|'.$lname.'|'.$client;
		$data = array();
		$data['key'] = trim(Configuration::get('Sendin_Api_Key'));
		$data['webaction'] = 'USERCREADITM';
		$data['blacklisted'] = '';
		$data['attributes_name'] = 'PRENOM|NOM|CLIENT';
		$data['attributes_value'] = $attribute;
		$data['category'] = '';
		$data['email'] = $email;
		$data['listid'] = Configuration::get('Sendin_Selected_List_Data');

		return $this->curlRequest($data);
	}

	/**
	* Add / Modify subscribers with their full details like Firstname, Lastname etc.
	*/
	public function subscribeByruntimeRegister($email, $fname, $lname, $phone_mobile)
	{
		if (!$this->syncSetting())
			return false;

		$client = 1;
		$attribute = $fname.'|'.$lname.'|'.$client.'|'.$phone_mobile;
		$data = array();
		$data['key'] = trim(Configuration::get('Sendin_Api_Key'));
		$data['webaction'] = 'USERCREADITM';
		$data['email'] = $email;
		$data['blacklisted'] = '';
		$data['attributes_name'] = 'PRENOM|NOM|CLIENT|SMS';
		$data['attributes_value'] = $attribute;
		$data['category'] = '';
		$data['listid'] = Configuration::get('Sendin_Selected_List_Data');   
		$this->curlRequest($data);

	}

	/**
	* Checks whether a subscriber is registered in the sendin_newsletter table.
	* If they are registered, we subscriber them on SendinBlue.
	*/
	private function isEmailRegistered($customer_email, $mobile_number)
	{
		if (Db::getInstance()->getRow('SELECT `email` FROM '._DB_PREFIX_.'sendin_newsletter
										WHERE `email` = \''.pSQL($customer_email).'\''))
				$this->subscribeByruntime($customer_email);
		elseif ($registered = Db::getInstance()->getRow('SELECT firstname, lastname FROM
			'._DB_PREFIX_.'customer WHERE `email` = \''.pSQL($customer_email).'\''))
			$this->subscribeByruntimeRegister($customer_email, $registered['firstname'], $registered['lastname'], $mobile_number);
	}

	/**
	* Displays the tracking code in the code block.
	*/
	public function codeDeTracking()
	{
		$this->html_code_tracking .= '<br/>
			<table class="table hidetableblock form-data" style="margin-top:15px;" cellspacing="0" cellpadding="0" width="100%">
			<thead>
			<tr>
			<th colspan="2">'.$this->l('Code tracking').'</th>
			</tr>
			</thead>';

		return $this->html_code_tracking .= '
			<tr><td><label>
			'.$this->l('Do you want to install a tracking code when validating an order').'
			</label>
			<input type="radio" class="script radio_nospaceing" id="yesradio" name="script" value="1"
			'.(Configuration::get('Sendin_Tracking_Status') ? 'checked="checked" ' : '').'/>'.$this->l('Yes').'
			<input type="radio" class="script radio_spaceing2" id="noradio" 
			name="script" value="0" '.(! Configuration::get('Sendin_Tracking_Status') ? 'checked="checked" ' : '').'/>'
			.$this->l('No').'
			<span class="toolTip"
			title="'.$this->l('This feature will allow you to transfer all your customers orders from PrestaShop into SendinBlue to implement your email marketing strategy.').'">
			&nbsp;</span>
			</td></tr></table>';
	}

	/**
	*This method is used to show options to the user whether the user wants the plugin to manage
	*their subscribers automatically.
	*/
	public function syncronizeBlockCode()
	{
		global $cookie;

		$this->_second_block_code .= '<style type="text/css">.tableblock tr td{padding:5px; border-bottom:0px;}</style>
			<form method="post" action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'">
			<table class="table tableblock hidetableblock form-data" style="margin-top:15px;" cellspacing="0" cellpadding="0" width="100%">
			<thead>
			<tr>
			<th colspan="2">'.$this->l('Activate SendinBlue to manage subscribers').'</th>
			</tr>
			</thead>
			<tr>
			<td style="width:250px">
			<label> '.$this->l('Activate SendinBlue to manage subscribers').'
			</label>
			</td>
			<td><input type="radio" class="managesubscribe" id="yessmtp"
			style="margin-right:10px;" name="managesubscribe" value="1"
			'.(Configuration::get('Sendin_Subscribe_Setting') ? 'checked="checked" ' : '').'/>'.$this->l('Yes').'
			<input type="radio" class="managesubscribe"
			id="nosmtp" style="margin-left:20px;margin-right:10px;"
			name="managesubscribe" value="0" '.(! Configuration::get('Sendin_Subscribe_Setting') ? 'checked="checked" ' : '').'/>'.$this->l('No').'
			<span class="toolTip"
			title="'.$this->l('If you activate this feature, your new contacts will be automatically added to SendinBlue or unsubscribed from SendinBlue. To synchronize the other way (SendinBlue to PrestaShop), you should run the url (mentioned below) each day.').'">
			&nbsp;</span>
			</td>
			</tr>';
			$this->_second_block_code .= '<tr class="managesubscribeBlock">
			<td><label>'.$this->l('Manage unsubscription from Front-Office').'</label>
			</td>
			<td>
			<input type="radio"  name="sendinddl"
			style="margin-right:10px;" value="1" '.(Configuration::get('Sendin_dropdown') ? 'checked="checked" ' : '').'/>'
			.$this->l('Yes').'
			<input type="radio"
			style="margin-left:20px;margin-right:10px;"
			name="sendinddl" value="0" '.(! Configuration::get('Sendin_dropdown') ? 'checked="checked" ' : '').'/>'
			.$this->l('No').'
			<span class="toolTip"
			title="'.$this->l('If you activate this option, you will let your customers the possibility to unsubscribe from your newsletter using the newsletter block displayed in the home page.').'">&nbsp;</span>
			</td>
			</tr>';
			$this->_second_block_code .= '<tr class="managesubscribeBlock">'.$this->parselist().'</tr>';
			$this->_second_block_code .= '<tr class="managesubscribeBlock"><td>&nbsp;</td>
			<td>
			<input type="submit" name="submitForm2" value="'.$this->l('Update').'" class="button" />&nbsp;
			</td>
			</tr>';
			$this->_second_block_code .= '<tr class="managesubscribeBlock" ><td colspan="2">'.$this->l('To synchronize the emails of your customers from SendinBlue platform to your e-commerce website, you should run').'
			<a target="_blank" href="'.$this->path.'sendinblue/cron.php?token='.Tools::encrypt(Configuration::get('PS_SHOP_NAME')).'">
			'.$this->l('this link').'</a> ';
			$this->_second_block_code .= $this->l('each day.').'
			<span class="toolTip" title="'.$this->l('Note that if you change the name of your Shop (currently ').Configuration::get('PS_SHOP_NAME').$this->l(') the token value changes.').'">&nbsp;</span></td></tr></table></form>';

			return $this->_second_block_code;
	}

	/**
	* Displays the SMTP details in the SMTP block.
	*/
	public function mailSendBySmtp()
	{
		global $cookie;

		$this->_html_smtp_tracking .= '
			<table class="table tableblock hidetableblock form-data" style="margin-top:15px;"
			cellspacing="0" cellpadding="0" width="100%">
			<thead>
			<tr>
			<th colspan="2">'.$this->l('Activate SendinBlue SMTP for your transactional emails').'</th>
			</tr>
			</thead>';

		$this->_html_smtp_tracking .= '
		<tr><td><form method="post" action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'">
		<label>
		'.$this->l('Activate SendinBlue SMTP for your transactional emails').'
	</label>
		<input type="radio" class="smtptestclick radio_nospaceing" id="yessmtp"
		name="smtp"
		value="1" '.(Configuration::get('Sendin_Api_Smtp_Status') ? 'checked="checked" ' : '').'/>'.$this->l('Yes').'
		<input type="radio" class="smtptestclick radio_spaceing2" id="nosmtp"
		name="smtp" value="0"
		'.(! Configuration::get('Sendin_Api_Smtp_Status') ? 'checked="checked" ' : '').'/>'.$this->l('No').'
		<span class="toolTip" title="'.$this->l('Transactional email is an expected email because it is triggered automatically after a transaction or a specific event. Common examples of transactional email are : account opening and welcome message, order shipment confirmation, shipment tracking and purchase order status, registration via a contact form, account termination, payment confirmation, invoice etc.').'">&nbsp;</span>
		</form></td></tr>';

		if (Configuration::get('Sendin_Api_Smtp_Status'))
			$st = '';
		else
			$st = 'style="display:none;"';

		$this->_html_smtp_tracking .= '
		<form method="post" name="smtp" id="smtp" action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'">
		<tr id="smtptest" '.$st.' ><td colspan="2">
		<div id="div_email_test">
		<p style="text-align:center">'.$this->l('Send email test From / To').' :&nbsp;
		<input type="text" size="40" name="testEmail" value="'.Configuration::get('PS_SHOP_EMAIL').'" id="email_from">
		&nbsp;
		<input type="submit"  class="button" value="'.$this->l('Send').'" name="sendTestMail" id="sendTestMail"></p>
		</div>
		</td></tr></form></table>';

		return $this->_html_smtp_tracking;
	}
	/**
	* Displays the SMS details in the SMS block.
	*/
	public function mailSendBySms()
	{
		global $smarty;
		global $cookie;
		$smarty->assign('site_name', Configuration::get('PS_SHOP_NAME'));
		$smarty->assign('link', '<a target="_blank" href="'.$this->path.'sendinblue/smsnotifycron.php?lang='.
		$cookie->id_lang.'&token='.Tools::encrypt(Configuration::get('PS_SHOP_NAME')).'">'.$this->l('this link').'</a>');
		$smarty->assign('current_credits_sms', $this->getSmsCredit());
		$smarty->assign('sms_campaign_status', Configuration::get('Sendin_Api_Sms_Campaign_Status'));
		$smarty->assign('Sendin_Notify_Email', Configuration::get('Sendin_Notify_Email'));
		$smarty->assign('sms_shipment_status', Configuration::get('Sendin_Api_Sms_shipment_Status'));
		$smarty->assign('sms_order_status', Configuration::get('Sendin_Api_Sms_Order_Status'));
		$smarty->assign('sms_credit_status', Configuration::get('Sendin_Api_Sms_Credit'));
		$smarty->assign('prs_version', _PS_VERSION_);
		$smarty->assign('Sendin_Notify_Value', Configuration::get('Sendin_Notify_Value'));
		$smarty->assign('Sendin_Sender_Order', Configuration::get('Sendin_Sender_Order'));
		$smarty->assign('Sendin_Sender_Order_Message', Configuration::get('Sendin_Sender_Order_Message'));
		$smarty->assign('Sendin_Sender_Shipment', Configuration::get('Sendin_Sender_Shipment'));
		$smarty->assign('Sendin_Sender_Shipment_Message', Configuration::get('Sendin_Sender_Shipment_Message'));
		$smarty->assign('form_url', Tools::safeOutput($_SERVER['REQUEST_URI']));
		return $this->display(__FILE__, 'views/templates/admin/smssetting.tpl');
	}

	/**
	* Fetches all the list of the user from the Sendin platform.
	*/
	public function getResultListValue()
	{
		$data = array();

		$data['key'] = Configuration::get('Sendin_Api_Key');
		$data['webaction'] = 'DISPLAYLISTDATA';

		return $this->curlRequest($data);
	}

	private function displayBankWire()
	{
		$this->_html .= '<img src="'.$this->path.'sendinblue/img/'.$this->l('sendinblue.png').'"
		style="float:left; margin:0px 15px 30px 0px;"><div style="float:left;
		font-weight:bold; padding:25px 0px 0px 0px; color:#268CCD;">'.
		$this->l('SendinBlue : THE all-in-one plugin for your marketing and transactional emails.').'</div>
		<div class="clear"></div>';
	}

	private function displaySendin()
	{
		$this->_html .= $this->displayBankWire();
		$this->_html .= '
		<fieldset>
		<legend><img src="'.$this->path.$this->name.'/logo.gif" alt="" /> '.$this->l('SendinBlue').'</legend>
		<div style="float: right; width: 340px; height: 205px; border: dashed 1px #666; padding: 8px; margin-left: 12px; margin-top:-15px;">
		<h2 style="color:#268CCD;">'.$this->l('Contact SendinBlue Team').'</h2>
		<div style="clear: both;"></div>
		<p>'.$this->l(' Contact us :
').'<br /><br />'.$this->l('Email : ').'<a href="mailto:'.$this->l('contact@sendinblue.com').'" style="color:#268CCD;">'.
$this->l('contact@sendinblue.com').'</a><br />'.$this->l('Phone : 0899 25 30 61').'</p>
		<p style="padding-top:20px;"><b>'.$this->l('For further informations, please visit our website:').
		'</b><br /><a href="'.$this->l('https://www.sendinblue.com/').'" target="_blank"
		style="color:#268CCD;">'.$this->l('https://www.sendinblue.com/').'</a></p>
		</div>
		<p>'.$this->l('With the SendinBlue plugin, you can find everything you need to easily and efficiently send your emailing campaigns to your prospects and customers. ').'</p>
		<ul class="listt">
			<li>'.$this->l(' Synchronize your subscribers with SendinBlue (subscribed and unsubscribed contacts)').'</li>
			<li>'.$this->l(' Easily create good looking emailings').'</li>
			<li>'.$this->l(' Schedule your campaigns').'</li>
			<li>'.$this->l(' Track your results and optimize').'</li>
			<li>'.$this->l(' Monitor your transactional emails (purchase confirmation, password reset, etc) with a better deliverability and real-time analytics').'</li>
		</ul>
		<b>'.$this->l('Why should you use SendinBlue ?').'</b>
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
		// checkFolderStatus after removing from SendinBlue
		$this->createFolderCaseTwo();
		$lang = new Language((int)$cookie->id_lang);

		if (Configuration::get('Sendin_Api_Key_Status'))
				$str = '';
		else
				$str = 'style="display:none;"';

		$this->_html .= '<p style="margin:1.5em 0;">'.$this->displaySendin().'</p>';
		$this->_html .= '<style>.margin-form{padding: 0 0 2em 210px;}</style><fieldset style="margin-bottom:10px;">
		<legend><img src="'.$this->path.$this->name.'/logo.gif" alt="" />'.$this->l('Prerequisites').'</legend>';
		$this->_html .= '<label">-
		'.$this->l('You should have a SendinBlue account. You can create a free account here : ').
		'<a href="'.$this->l('https://www.sendinblue.com/').'" class="link_action"  target="_blank">&nbsp;'.$this->l('https://www.sendinblue.com/').'</a></label><br />';

		if (!extension_loaded('curl') || !ini_get('allow_url_fopen'))
			$this->_html .= '<label">-
			'.$this->l('You must enable CURL extension and allow_url_fopen option on your server if you want to use this module.').
			'</label>';

		$this->_html .= '</fieldset>
		<form method="post" action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'">
		<input type ="hidden" name="customtoken" id="customtoken" value="'.Tools::encrypt(Configuration::get('PS_SHOP_NAME')).'">
		<input type ="hidden" name="langvalue" id="langvalue" value="'.$cookie->id_lang.'">
		<input type ="hidden" name="page_no" id="page_no" value="1"><fieldset style="position:relative;" class="form-display">';
		$this->_html .= '<legend>
		<img src="'.$this->_path.'logo.gif" />'.$this->l('Settings').'</legend>
		<label>'.$this->l('Activate the SendinBlue module').'</label><div class="margin-form" style="padding-top:5px">
		<input type="radio" id="y" class="keyyes radio_spaceing" 
		 name="status" value="1"
		'.(Configuration::get('Sendin_Api_Key_Status') ? 'checked="checked" ' : '').'/>'.$this->l('Yes').'
		<input type="radio"  id="n" class="keyyes radio_spaceing2"
		 name="status" value="0"
		'.(! Configuration::get('Sendin_Api_Key_Status') ? 'checked="checked" ' : '').'/>'.$this->l('No').'
		</div><div class="clear"></div>';
		$this->_html .= '<div id="apikeybox"  '.$str.' ><label class="key">'.$this->l('API key').'</label>
		<div class="margin-form key">
		<input type="text" name="apikey" id="apikeys" value="'.Tools::safeOutput(Configuration::get('Sendin_Api_Key')).'" />&nbsp;
		<span class="toolTip"
		title="'.$this->l('Please enter your API key from your SendinBlue account and if you don\'t have it yet, please go to www.sendinblue.com and subscribe. You can then get the API key from https://my.sendinblue.com/advanced/apikey').'">
		&nbsp;</span>
		</div></div>';
		$this->_html .= '<div class="margin-form clear pspace">
		<input type="submit" name="submitUpdate" value="'.$this->l('Update').'" class="button" />&nbsp;
		</div><div class="clear"></div></fieldset></form>';

		if (Configuration::get('Sendin_Api_Key_Status') == 1)
		{
			$this->_html .= $this->syncronizeBlockCode();
			$this->_html .= $this->mailSendBySmtp();
			$this->_html .= $this->codeDeTracking();
			$this->_html .= $this->mailSendBySms();
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
	* Checks if an email address already exists in the sendin_newsletter table
	* and returns a value accordingly.
	*/
	private function isNewsletterRegistered($customer_email)
	{
		if (Db::getInstance()->getRow('SELECT `email` FROM '._DB_PREFIX_.'sendin_newsletter
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
	* Checks if an email address is already subscribed in the sendin_newsletter table
	* and returns true, otherwise returns false.
	*/
	private function isNewsletterRegisteredSub($customer_email)
	{
		if (Db::getInstance()->getRow('SELECT `email` FROM '._DB_PREFIX_.'sendin_newsletter
										WHERE `email` = \''.pSQL($customer_email).'\' and active=1'))
			return true;

		if (Db::getInstance()->getRow('SELECT `newsletter` FROM '._DB_PREFIX_.'customer
										WHERE `email` = \''.pSQL($customer_email).'\' and newsletter=1'))
			return true;

		return false;
	}

	/*
	* Checks if an email address is already unsubscribed in the sendin_newsletter table
	* and returns true, otherwise returns false.
	*/
	private function isNewsletterRegisteredUnsub($customer_email)
	{
		if (Db::getInstance()->getRow('SELECT `email` FROM '._DB_PREFIX_.'sendin_newsletter
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

		$ddl_value = Configuration::get('Sendin_dropdown');

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
				if (! Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'sendin_newsletter
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
					if (! Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'sendin_newsletter
												(email, newsletter_date_add, ip_registration_newsletter, http_referer)
												VALUES (\''.pSQL($this->email).'\', \''.$s_new_timestamp.'\', \''.pSQL(Tools::getRemoteAddr()).'\',
												(SELECT c.http_referer FROM '._DB_PREFIX_.'connections c WHERE c.id_guest = '.
												(int)$cookie->id_guest.' ORDER BY c.date_add DESC LIMIT 1))'))
					return $this->error = $this->l('Error during subscription');
				case 0:
					// email status send to remote server
					if (! Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'sendin_newsletter
												SET `active` = 1,
												newsletter_date_add = \''.$s_new_timestamp.'\'
												WHERE `email` = \''.pSQL($this->email).'\''))
					return $this->error = $this->l('Error during subscription');
				case 1:
					// email status send to remote server
					if (! Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'sendin_newsletter
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
	* Method is being called at the time of uninstalling the SendinBlue module.
	*/
	public function uninstall()
	{
		$this->unregisterHook('leftColumn');
		$this->unregisterHook('createAccount');
		$this->unregisterHook('createAccountForm');
		$this->unregisterHook('OrderConfirmation');
		Configuration::updateValue('Sendin_Api_Sms_Order_Status', 0);
		Configuration::updateValue('Sendin_Api_Sms_shipment_Status', 0);
		Configuration::updateValue('Sendin_Api_Sms_Campaign_Status', 0);
		Configuration::updateValue('Sendin_Sender_Shipment_Message', '');
		Configuration::updateValue('Sendin_Sender_Shipment', '');
		Configuration::updateValue('Sendin_Sender_Order', '');
		Configuration::updateValue('Sendin_Sender_Order_Message', '');
		Configuration::updateValue('Sendin_Notify_Value', '');
		Configuration::updateValue('Sendin_Notify_Email', '');
		Configuration::updateValue('Sendin_Api_Sms_Credit', 0);
		Configuration::updateValue('Sendin_Notify_Cron_Executed', 0);

		if (Configuration::get('Sendin_Api_Smtp_Status'))
			$this->resetConfigSendinSmtp();

		// Uninstall module
		Configuration::deleteByName('Sendin_First_Request');
		Configuration::deleteByName('Sendin_Subscribe_Setting');
		Configuration::deleteByName('Sendin_dropdown');
		Configuration::deleteByName('Sendin_Tracking_Status');
		Configuration::deleteByName('Sendin_Smtp_Result');
		Configuration::deleteByName('Sendin_Api_Key');
		Configuration::deleteByName('Sendin_Api_Smtp_Status');
		Configuration::deleteByName('Sendin_Selected_List_Data');

		if (Configuration::get('Sendin_Newsletter_table'))
		{
			$this->restoreBlocknewsletterBlock();
			$this->getRestoreOldNewsletteremails();

			Db::getInstance()->Execute('DROP TABLE  '._DB_PREFIX_.'sendin_newsletter');

			Configuration::deleteByName('Sendin_Newsletter_table');
			Configuration::deleteByName('Sendin_Api_Key_Status');
		}

		return parent::uninstall();
	}
	public function hookupdateOrderStatus()
	{
		global $cookie;
		$id_order_state = Tools::getValue('id_order_state');
		if ($id_order_state == 4 && Configuration::get('Sendin_Api_Sms_shipment_Status') == 1 && Configuration::get('Sendin_Sender_Shipment_Message') != '')
		{
			$order = new Order(Tools::getValue('id_order'));
			$address = new Address(intval($order->id_address_delivery));
			$currency = new Currency();
			$id_currency = $order->id_currency;
			$currency_data = $currency->getCurrency($id_currency);
			$customer_civility_result = Db::getInstance()->ExecuteS('SELECT id_gender,firstname,lastname FROM '._DB_PREFIX_.'customer WHERE `id_customer` = '.(int)$order->id_customer);
			$firstname = (isset($address->firstname)) ? $address->firstname : '';
			$lastname  = (isset($address->lastname)) ? $address->lastname : '';

			if (strtolower($firstname) === strtolower($customer_civility_result[0]['firstname']) && strtolower
			($lastname) === strtolower($customer_civility_result[0]['lastname']))
			$civility_value = (isset($customer_civility_result['0']['id_gender'])) ? $customer_civility_result['0']['id_gender'] : '';
			else
			$civility_value = '';

			if ($civility_value == 1)
					$civility = 'Mr.';
					else if ($civility_value == 2)
					$civility = 'Ms.';
					else if ($civility_value == 3)
					$civility = 'Miss.';
					else
					$civility = '';
			$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
															SELECT `call_prefix`
															FROM `'._DB_PREFIX_.'country`
															WHERE `id_country` = '.(int)$address->id_country);
			if (isset($address->phone_mobile) && !empty($address->phone_mobile))
			{
					$order_date = (isset($order->date_upd)) ? $order->date_upd : 0;
					if ($cookie->id_lang == 1)
					$ord_date = date('m/d/Y', strtotime($order_date));
					else
					$ord_date = date('d/m/Y', strtotime($order_date));

					$msgbody = Configuration::get('Sendin_Sender_Shipment_Message');
					$total_pay = (isset($order->total_paid)) ? $order->total_paid : 0;
					$total_pay = $total_pay.''.$currency_data['iso_code'];
					if (_PS_VERSION_ < '1.5.0.0')
					$ref_num = (isset($order->id)) ? $order->id : '';
					else
					$ref_num = (isset($order->reference)) ? $order->reference : '';

					$civility_data = str_replace('{civility}', $civility, $msgbody);
					$fname = str_replace('{first_name}', $firstname, $civility_data);
					$lname = str_replace('{last_name}', $lastname."\r\n", $fname);
					$product_price = str_replace('{order_price}', $total_pay, $lname);
					$order_date = str_replace('{order_date}', $ord_date."\r\n", $product_price);
					$msgbody = str_replace('{order_reference}', $ref_num, $order_date);
					$arr = array();
					$arr['to'] = $this->checkMobileNumber($address->phone_mobile, $result['call_prefix']);
					$arr['from'] = Configuration::get('Sendin_Sender_Shipment');
					$arr['text'] = $msgbody;

					$this->sendSmsApi($arr);
			}
		}
	}
	/**
	* Displays the newsletter on the front page of PrestaShop
	*/
	public function hookLeftColumn($params)
	{
		if (!$this->syncSetting())
			return false;

		global $smarty;

		$ddl_value = Configuration::get('Sendin_dropdown');

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
				$smarty->assign( array(
						'color' => 'red',
						'msg' => $this->error,
						'nw_value' => isset($this->email) ? $this->email : false,
						'nw_error' => true,
						'action' => $post_action
				) );
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
				$smarty->assign( array(
						'color' => 'green',
						'msg' => $this->valid,
						'nw_error' => false
					) );
			}
		}

		$smarty->assign('this_path', $this->_path);
		$smarty->assign('Sendin_dropdown', $ddl_value);

		return $this->display(__FILE__, 'views/templates/front/sendinblue.tpl');
	}
	/**
	* Displays the newsletter option on registration page of PrestaShop
	*/
	public function hookcreateAccountForm($params)
	{
	if (!$this->syncSetting())
			return false;

		global $smarty;
		$smarty->assign('params', $params);

		return $this->display(__FILE__, 'views/templates/front/newsletter.tpl');

	}
	/*
	* Displays the CSS for the SendinBlue module.
	*/
	public function addCss()
	{
		$min = $_SERVER['HTTP_HOST'] == 'localhost' ? '' : '.min';
		$so = $this->l('Select option');
		$selected = $this->l('selected');
		$html = '<script>  var selectoption = "'.$so.'"; </script>';
		$html .= '<script>  var base_url = "'.str_replace('modules/', '', $this->path).'"; </script>';
		$html .= '<script>  var selected = "'.$selected.'"; </script>';
		$sendin_js_path = $this->path.$this->name.'/js/'.$this->name.$min.'.js?_='.time();
		$js_ddl_list = $this->path.$this->name.'/js/jquery.multiselect.min.js';
		$liveclickquery = $this->path.$this->name.'/js/jquery.livequery.min.js';
		$s_css = $this->path.$this->name.'/css/'.$this->name.'.css?_='.time();

		$html .= '<link rel="stylesheet" type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/ui-lightness/jquery-ui.css" />
		<link "text/css" href="'.$s_css.'" rel="stylesheet" />
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>
		<script type="text/javascript" src="'.$js_ddl_list.'"></script>
		<script type="text/javascript" src="'.$liveclickquery.'"></script>
		<script type="text/javascript" src="'.$sendin_js_path.'"></script>';

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
		$currency = new Currency();
			$id_currency = $params['objOrder']->id_currency;
			$currency_data = $currency->getCurrency($id_currency);
		$customerid  = (isset($params['objOrder']->id_customer)) ? $params['objOrder']->id_customer : '';
		$customer_result = Db::getInstance()->ExecuteS('SELECT id_gender,firstname,lastname  FROM '._DB_PREFIX_.
		'customer WHERE `id_customer` = '.(int)$customerid);
		$id_delivery  = (isset($params['objOrder']->id_address_delivery)) ? $params['objOrder']->id_address_delivery : 0;
		$address_delivery = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'address WHERE `id_address` = '.(int)$id_delivery);
		if (_PS_VERSION_ < '1.5.0.0')
		$ref_num = (isset($params['objOrder']->id)) ? $params['objOrder']->id : 0;
		else
		$ref_num = (isset($params['objOrder']->reference)) ? $params['objOrder']->reference : 0;
		$total_to_pay = (isset($params['total_to_pay'])) ? $params['total_to_pay'] : 0;
		if (Configuration::get('Sendin_Api_Sms_Order_Status') && Configuration::get
		('Sendin_Sender_Order') && Configuration::get('Sendin_Sender_Order_Message'))
		{
				$data = array();
				$customer_info = $employee->getAddresses((int)$cookie->id_lang);

				if (isset($address_delivery[0]['phone_mobile']) && !empty($address_delivery[0]['phone_mobile']))
				{
					$result_code = Db::getInstance()->getRow('SELECT `call_prefix` FROM '._DB_PREFIX_.'country
															WHERE `id_country` = \''.pSQL($address_delivery[0]['id_country']).'\'');
					$number = $this->checkMobileNumber($address_delivery[0]['phone_mobile'], $result_code['call_prefix']);					

					$order_date = (isset($params['objOrder']->date_upd)) ? $params['objOrder']->date_upd : 0;
					if ($cookie->id_lang == 1)
					$ord_date = date('m/d/Y', strtotime($order_date));
					else
					$ord_date = date('d/m/Y', strtotime($order_date));
					$firstname = (isset($address_delivery[0]['firstname'])) ? $address_delivery[0]['firstname'] : '';
					$lastname  = (isset($address_delivery[0]['lastname'])) ? $address_delivery[0]['lastname'] : '';

					if (strtolower($firstname) === strtolower($customer_result[0]['firstname']) && strtolower
					($lastname) === strtolower($customer_result[0]['lastname']))
					$civility_value = (isset($employee->id_gender)) ? $employee->id_gender : '';
					else
					$civility_value = '';

					if ($civility_value == 1)
					$civility = 'Mr.';
					else if ($civility_value == 2)
					$civility = 'Ms.';
					else if ($civility_value == 3)
					$civility = 'Miss.';
					else
					$civility = '';

					$total_pay = $total_to_pay.''.$currency_data['iso_code'];
					$msgbody = Configuration::get('Sendin_Sender_Order_Message');
					$civility_data = str_replace('{civility}', $civility, $msgbody);
					$fname = str_replace('{first_name}', $firstname, $civility_data);
					$lname = str_replace('{last_name}', $lastname."\r\n", $fname);
					$product_price = str_replace('{order_price}', $total_pay, $lname);
					$order_date = str_replace('{order_date}', $ord_date."\r\n", $product_price);
					$msgbody = str_replace('{order_reference}', $ref_num, $order_date);
					$data['from'] = Configuration::get('Sendin_Sender_Order');
					$data['text'] = $msgbody;
					$data['to'] = $number;
					$this->sendSmsApi($data);
				}
		}

		if (Configuration::get('Sendin_Api_Key_Status') == 1 && Configuration::get('Sendin_Tracking_Status') == 1)
		{
			$this->tracking = $this->trackingResult();
			$date_value = $this->getApiConfigValue();			
			if ($date_value->date_format == 'dd-mm-yyyy')
			$date = date('d-m-Y');
			else
			$date = date('m-d-Y');
			
			$list = str_replace('|', ',', Configuration::get('Sendin_Selected_List_Data'));
			if (preg_match('/^[0-9,]+$/', $list))
				$list = $list;
			else
				$list = '';
			$code = '<script type="text/javascript">
					/**Code for NB tracking*/
					function loadScript(url,callback){var script=document.createElement("script");script.type="text/javascript";if(script.readyState){script.onreadystatechange=function(){
					if(script.readyState=="loaded"||script.readyState=="complete"){script.onreadystatechange=null;callback(url)}}}else{
					script.onload=function(){callback(url)}}script.src=url;if(document.body){document.body.appendChild(script)}else{
					document.head.appendChild(script)}}
					var nbJsURL = (("https:" == document.location.protocol) ? "https://my-tracking-orders.googlecode.com/files" : "http://my-tracking-orders.googlecode.com/files");
					var nbBaseURL = "http://tracking.mailin.fr/";
					loadScript(nbJsURL+"/nbv2.js",
					function(){
					/*You can put your custom variables here as shown in example.*/					
					try {
					var nbTracker = nb.getTracker(nbBaseURL , "'.Tools::safeOutput($this->tracking->result->tracking_data->site_id).'");
					var list = ['.$list.'];
					var attributes = ["EMAIL","PRENOM","NOM","ORDER_ID","ORDER_DATE","ORDER_PRICE"];
					var values = ["'.$employee->email.'",
									"'.$employee->firstname.'",
									"'.$employee->lastname.'",
									"'.$ref_num.'",
									"'.$date.'",
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
				'sendinsmtp_conf',
				Mail::l($title, (int)$cookie->id_lang),
				array('{title}'=>$title),
				$email,
				$toname,
				$this->l('contact@sendinblue.com'),
				$this->l('SendinBlue'),
				null,
				null,
				dirname(__FILE__).'/mails/'
			);
	}
	public function sendNotifySms($email, $id_lang)
	{
		$iso_code = Language::getIsoById((int)$id_lang);

		$file_lang = dirname(__FILE__).'/mails/'.$iso_code.'/lang.php';
		if (Tools::file_exists_cache($file_lang))
			include_once($file_lang);

		$title = 'Alert: You do not have enough credits SMS';
		$site_name = Configuration::get('PS_SHOP_NAME');
		$present_credit = $this->getSmsCredit();
		$toname = explode('@', $email);
		$toname = preg_replace('/[^a-zA-Z0-9]+/', ' ', $toname[0]);
		return Mail::Send(
				(int)$id_lang,
				'sendinsms_notify',
				Mail::l($title, (int)$id_lang),
				array('{title}'=>$title,'{present_credit}'=>$present_credit,'{site_name}'=>$site_name),
				$email,
				$toname,
				$this->l('contact@sendinblue.com'),
				$this->l('SendinBlue'),
				null,
				null,
				dirname(__FILE__).'/mails/'
			);

	}

	public function checkMobileNumber($number, $call_prefix)
	{
		$number = preg_replace('/\s+/', '', $number);
		$charone = substr($number, 0, 1);
		$chartwo = substr($number, 0, 2);
		if ($charone == '0' && $chartwo != '00')
			return '00'.$call_prefix.substr($number, 1);
		else if ($chartwo == '00')
			return $number;
		else if ($charone == '+')
			return '00'.substr($number, 1);
		else if ($charone != '0')
			return '00'.$call_prefix.$number;
	}
	/**
	* Retrieve customers by email address
	*
	* @static
	* @param $email
	* @return array
	*/
	public function getCustomersByEmail($email)
	{
		$sql = 'SELECT *
				FROM `'._DB_PREFIX_.'customer`
				WHERE `email` = \''.pSQL($email).'\'';
		return Db::getInstance()->ExecuteS($sql);
	}
	public function getAllCustomers()
	{
		$sql = 'SELECT `id_customer`, `email`, `id_gender`, `newsletter`
				, `newsletter_date_add` FROM `'._DB_PREFIX_.'customer` ';
		return Db::getInstance()->ExecuteS($sql);
	}
	/**
	* API config value from SendinBlue.
	*/
	public function getApiConfigValue()
	{
		$data = array();
		$data['key'] = Configuration::get('Sendin_Api_Key');
		$data['webaction'] = 'PLUGIN-CONFIG';
		$value_config = $this->curlRequest($data);
		$result = Tools::jsonDecode($value_config);
		return $result;
	}
}
