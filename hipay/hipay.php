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
*  @copyright  2007-2011 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

define('DEV', 0);
define('PROD', 1);

class Hipay extends PaymentModule
{
	private $arrayCategories;
	private $env = PROD;
	
	protected $ws_client = false;

	const WS_SERVER = 'http://api.prestashop.com/';
	const WS_URL = 'http://api.prestashop.com/partner/hipay/hipay.php';

	public function __construct()
	{
		$this->name = 'hipay';
		$this->tab = 'payments_gateways';
		$this->version = '1.5.2';
		$this->module_key = 'e25bc8f4f9296ef084abf448bca4808a';

		$this->currencies = true;
		$this->currencies_mode = 'radio';
		$this->author = 'PrestaShop';

		parent::__construct();
		
		$this->displayName = $this->l('Hipay');
		$this->description = $this->l('Secure payement with Visa, Mastercard and European solutions.');

		$request = '
			SELECT iso_code
			FROM '._DB_PREFIX_.'country as c
			LEFT JOIN '._DB_PREFIX_.'zone as z
			ON z.id_zone = c.id_zone
			WHERE ';
		
		$result = Db::getInstance()->ExecuteS($request.$this->getRequestZones());

		foreach ($result as $num => $iso)
			$this->limited_countries[] = $iso['iso_code'];

		if ($this->id)
		{
			// Define extracted from mapi/mapi_defs.php
			if (!defined('HIPAY_GATEWAY_URL')) 
				define('HIPAY_GATEWAY_URL','https://'.($this->env ? '' : 'test.').'payment.hipay.com/order/');
		}

		/** Backward compatibility */
		require(_PS_MODULE_DIR_.'hipay/backward_compatibility/backward.php');
		
		if (!class_exists('SoapClient'))
			$this->warning .= $this->l('To work properly the module need the Soap library to be installed.');
		else
			$this->ws_client = $this->getWsClient();
	}
	
	public function install()
	{
		Configuration::updateValue('HIPAY_SALT', uniqid());

		if (!Configuration::get('HIPAY_UNIQID'))
			Configuration::updateValue('HIPAY_UNIQID', uniqid());
		if (!Configuration::get('HIPAY_RATING'))
			Configuration::updateValue('HIPAY_RATING', 'ALL');
		
		if (!(parent::install() AND $this->registerHook('payment')))
			return false;
		
		$result = Db::getInstance()->ExecuteS('
			SELECT `id_zone`, `name`
			FROM `'._DB_PREFIX_.'zone`
			WHERE `active` = 1
		');
		
		foreach ($result as $rowNumber => $rowValues)
		{
			Configuration::deleteByName('HIPAY_AZ_'.$rowValues['id_zone']);
			Configuration::deleteByName('HIPAY_AZ_ALL_'.$rowValues['id_zone']);
		}
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'module_country` WHERE `id_module` = '.(int)$this->id);
			
		return true;
	}

	/**
	 * Set shipping zone search
	 * 
	 * @param	string $searchField = 'z.id_zone'
	 * @param	int $defaultZone = 1
	 * @return	string
	 */
	private function getRequestZones($searchField='z.id_zone', $defaultZone = 1)
	{
		$result = Db::getInstance()->ExecuteS('
			SELECT `id_zone`, `name`
			FROM `'._DB_PREFIX_.'zone`
			WHERE `active` = 1
		');
		
		$tmp = null;
		foreach ($result as $rowNumber => $rowValues)
			if (strcmp(Configuration::get('HIPAY_AZ_'.$rowValues['id_zone']), 'ok') == 0)
				$tmp .= $searchField.' = '.$rowValues['id_zone'].' OR ';
		
		if ($tmp == null)
			$tmp = $searchField.' = '.$defaultZone;
		else
			$tmp = substr($tmp, 0, strlen($tmp) - strlen(' OR '));
			
		return $tmp;
	}
	
	public function hookPayment($params)
	{
		global $smarty, $cart;

		$currency = new Currency($this->getModuleCurrency($cart));
		$hipayAccount = Configuration::get('HIPAY_ACCOUNT_'.$currency->iso_code);
		$hipayPassword = Configuration::get('HIPAY_PASSWORD_'.$currency->iso_code);
		$hipaySiteId = Configuration::get('HIPAY_SITEID_'.$currency->iso_code);
		$hipayCategory = Configuration::get('HIPAY_CATEGORY_'.$currency->iso_code);
		
		$logo_suffix = strtoupper(Configuration::get('HIPAY_PAYMENT_BUTTON'));
		if (!in_array($logo_suffix, array('DE', 'FR', 'GB', 'BE', 'ES', 'IT', 'NL', 'PT', 'BR')))
			$logo_suffix = 'DEFAULT';

		if ($hipayAccount && $hipayPassword && $hipaySiteId && $hipayCategory && Configuration::get('HIPAY_RATING'))
		{
			$smarty->assign('hipay_prod', $this->env);
			$smarty->assign('logo_suffix', $logo_suffix);
			$smarty->assign(array('this_path' => $this->_path, 'this_path_ssl' => Tools::getShopDomainSsl(true).__PS_BASE_URI__.((int)Configuration::get('PS_REWRITING_SETTINGS') && isset($smarty->ps_language) && !empty($smarty->ps_language) ? $smarty->ps_language->iso_code.'/' : '').'modules/'.$this->name.'/'));
			return $this->display(__FILE__, 'payment.tpl');
		}
	}

	private function getModuleCurrency($cart)
	{
		$id_currency = (int)self::MysqlGetValue('SELECT id_currency FROM `'._DB_PREFIX_.'module_currency` WHERE id_module = '.(int)$this->id);
		
		if (!$id_currency OR $id_currency == -2)
			$id_currency = Configuration::get('PS_CURRENCY_DEFAULT');
		elseif ($id_currency == -1)
			$id_currency = $cart->id_currency;
			
		return $id_currency;
	}
	
	private function formatLanguageCode($language_code)
	{
		$languageCodeArray = preg_split('/-|_/', $language_code);
		if (!isset($languageCodeArray[1]))
			$languageCodeArray[1] = $languageCodeArray[0];
		return strtolower($languageCodeArray[0]).'_'.strtoupper($languageCodeArray[1]);
	}

	public function payment()
	{
		if (!$this->active)
			return;

		global $cart;

		$id_currency = (int)$this->getModuleCurrency($cart);
		// If the currency is forced to a different one than the current one, then the cart must be updated
		if ($cart->id_currency != $id_currency)
			if (Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'cart SET id_currency = '.(int)$id_currency.' WHERE id_cart = '.(int)$cart->id))
				$cart->id_currency = $id_currency;
		
		$currency = new Currency($id_currency);
		$language = new Language($cart->id_lang);
		$customer = new Customer($cart->id_customer);
		
		require_once(dirname(__FILE__).'/mapi/mapi_package.php');
		
		$hipayAccount = Configuration::get('HIPAY_ACCOUNT_'.$currency->iso_code);
		$hipayPassword = Configuration::get('HIPAY_PASSWORD_'.$currency->iso_code);
		$hipaySiteId = Configuration::get('HIPAY_SITEID_'.$currency->iso_code);
		$hipaycategory = Configuration::get('HIPAY_CATEGORY_'.$currency->iso_code);

		$paymentParams = new HIPAY_MAPI_PaymentParams();
		$paymentParams->setLogin($hipayAccount, $hipayPassword);
		$paymentParams->setAccounts($hipayAccount, $hipayAccount);
		// EN_us is not a standard format, but that's what Hipay uses 
		if (isset($language->language_code))
			$paymentParams->setLocale($this->formatLanguageCode($language->language_code));
		else
			$paymentParams->setLocale(strtoupper($language->iso_code).'_'.strtolower($language->iso_code));
		$paymentParams->setMedia('WEB');
		$paymentParams->setRating(Configuration::get('HIPAY_RATING'));
		$paymentParams->setPaymentMethod(HIPAY_MAPI_METHOD_SIMPLE);
		$paymentParams->setCaptureDay(HIPAY_MAPI_CAPTURE_IMMEDIATE);
		$paymentParams->setCurrency(strtoupper($currency->iso_code));
		$paymentParams->setIdForMerchant($cart->id);
		$paymentParams->setMerchantSiteId($hipaySiteId);
		$paymentParams->setIssuerAccountLogin($this->context->customer->email);
		$paymentParams->setUrlCancel(Tools::getShopDomainSsl(true).__PS_BASE_URI__.'order.php?step=3');
		$paymentParams->setUrlNok(Tools::getShopDomainSsl(true).__PS_BASE_URI__.'order-confirmation.php?id_cart='.(int)$cart->id.'&amp;id_module='.(int)$this->id.'&amp;secure_key='.$customer->secure_key);
		$paymentParams->setUrlOk(Tools::getShopDomainSsl(true).__PS_BASE_URI__.'order-confirmation.php?id_cart='.(int)$cart->id.'&amp;id_module='.(int)$this->id.'&amp;secure_key='.$customer->secure_key);
		$paymentParams->setUrlAck(Tools::getShopDomainSsl(true).__PS_BASE_URI__.'modules/'.$this->name.'/validation.php?token='.Tools::encrypt($cart->id.$cart->secure_key.Configuration::get('HIPAY_SALT')));
		$paymentParams->setBackgroundColor('#FFFFFF');

		if (!$paymentParams->check())
			return $this->l('[Hipay] Error: cannot create PaymentParams');

		$item = new HIPAY_MAPI_Product();
		$item->setName($this->l('Cart'));
		$item->setInfo('');
		$item->setquantity(1);
		$item->setRef($cart->id);
		$item->setCategory($hipaycategory);
		$item->setPrice($cart->getOrderTotal());
		
		try {
			if (!$item->check())
				return $this->l('[Hipay] Error: cannot create "Cart" Product');
		} catch (Exception $e) {
			return $this->l('[Hipay] Error: cannot create "Cart" Product');
		}
		
		$items = array($item);

		$order = new HIPAY_MAPI_Order();
		$order->setOrderTitle($this->l('Order total'));
		$order->setOrderCategory($hipaycategory);

		if (!$order->check())
			return $this->l('[Hipay] Error: cannot create Order');

		try {
			$commande = new HIPAY_MAPI_SimplePayment($paymentParams, $order, $items);
		} catch (Exception $e) {
			return $this->l('[Hipay] Error:').' '.$e->getMessage();
		}

		$xmlTx = $commande->getXML();
		$output = HIPAY_MAPI_SEND_XML::sendXML($xmlTx);
		$reply = HIPAY_MAPI_COMM_XML::analyzeResponseXML($output, $url, $err_msg, $err_keyword, $err_value, $err_code);

		if ($reply === true)
			Tools::redirectLink($url);
		else
		{
			global $smarty;
			include(dirname(__FILE__).'/../../header.php');
			
			$smarty->assign('errors', array('[Hipay] '.strval($err_msg).' ('.$output.')'));
			$_SERVER['HTTP_REFERER'] = Tools::getShopDomainSsl(true).__PS_BASE_URI__.'order.php?step=3';
			$smarty->display(_PS_THEME_DIR_.'errors.tpl');
			
			include(dirname(__FILE__).'/../../footer.php');
		}
	}

	public function validation()
	{
		if (!$this->active)
			return;
		if (!array_key_exists('xml', $_POST))
			return;

		if (_PS_MAGIC_QUOTES_GPC_)
			$_POST['xml'] = stripslashes($_POST['xml']);
		
		require_once(dirname(__FILE__).'/mapi/mapi_package.php');

		if (HIPAY_MAPI_COMM_XML::analyzeNotificationXML($_POST['xml'], $operation, $status, $date, $time, $transid, $amount, $currency, $id_cart, $data) === false)
		{
			file_put_contents('logs'.Configuration::get('HIPAY_UNIQID').'.txt', '['.date('Y-m-d H:i:s').'] Analysis error: '.htmlentities($_POST['xml'])."\n", FILE_APPEND);
			return false;
		}
		
		if (_PS_VERSION_ >= 1.5)
			Context::getContext()->cart = new Cart((int)$id_cart);
		
		$cart = new Cart((int)$id_cart);
		if (Tools::encrypt($cart->id.$cart->secure_key.Configuration::get('HIPAY_SALT')) != Tools::getValue('token'))
			file_put_contents('logs'.Configuration::get('HIPAY_UNIQID').'.txt', '['.date('Y-m-d H:i:s').'] Token error: '.htmlentities($_POST['xml'])."\n", FILE_APPEND);
		else
		{
			if (trim($operation) == 'capture' AND trim(strtolower($status)) == 'ok')
			{
				/* Paiement capturé sur Hipay = Paiement accepté sur Prestashop */
				$orderMessage = $operation.': '.$status.'\ndate: '.$date.' '.$time.'\ntransaction: '.$transid.'\namount: '.(float)$amount.' '.$currency.'\nid_cart: '.(int)$id_cart;
				$this->validateOrder((int)$id_cart, Configuration::get('PS_OS_PAYMENT'), (float)$amount, $this->displayName, $orderMessage, array(), NULL, false, $cart->secure_key);
			}
			elseif (trim($operation) == 'refund' AND trim(strtolower($status)) == 'ok')
			{
				/* Paiement remboursé sur Hipay */
				if (!($id_order = Order::getOrderByCartId((int)($id_cart))))
					die(Tools::displayError());
					
				$order = new Order((int)($id_order));
				if (!$order->valid OR $order->getCurrentState() === Configuration::get('PS_OS_REFUND'))
					die(Tools::displayError());
					
				$orderHistory = new OrderHistory();
				$orderHistory->id_order = (int)($order->id);
				$orderHistory->changeIdOrderState((int)(Configuration::get('PS_OS_REFUND')), (int)($id_order));
				$orderHistory->addWithemail();
			}
		}
	}

	/**
	 * Uninstall and clean the module settings
	 * 
	 * @return	bool
	 */
	public function uninstall()
	{
		parent::uninstall();
		
		$result = Db::getInstance()->ExecuteS('
			SELECT `id_zone`, `name`
			FROM `'._DB_PREFIX_.'zone`
			WHERE `active` = 1
		');
		
		foreach ($result as $rowValues)
		{
			Configuration::deleteByName('HIPAY_AZ_'.$rowValues['id_zone']);
			Configuration::deleteByName('HIPAY_AZ_ALL_'.$rowValues['id_zone']);
		}
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'module_country` WHERE `id_module` = '.(int)$this->id);
		
		return (true);
	}
	
	public function getContent()
	{
		global $currentIndex;
		$warnings = '';

		if ($currentIndex == '' && _PS_VERSION_ >= 1.5)
			$currentIndex = 'index.php?controller='.Tools::safeOutput(Tools::getValue('controller'));
		$currencies = DB::getInstance()->ExecuteS('SELECT c.iso_code, c.name, c.sign FROM '._DB_PREFIX_.'currency c');
		
		if (Tools::isSubmit('submitHipayAZ')) 
		{
			// Delete all configurated zones
			foreach ($_POST as $key => $val)
			{
				if (strncmp($key, 'HIPAY_AZ_ALL_', strlen('HIPAY_AZ_ALL_')) == 0) 
				{
					$id = substr($key, -(strlen($key) - strlen('HIPAY_AZ_ALL_')));
					Configuration::updateValue('HIPAY_AZ_'.$id, 'ko');
				}
			}
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'module_country` WHERE `id_module` = '.(int)$this->id);
			
			// Add the new configuration zones
			foreach ($_POST as $key => $val) 
			{
				if (strncmp($key, 'HIPAY_AZ_', strlen('HIPAY_AZ_')) == 0)
					Configuration::updateValue($key, 'ok');
			}
			$request = 'SELECT id_country FROM '._DB_PREFIX_.'country WHERE ';
			$results = Db::getInstance()->ExecuteS($request.$this->getRequestZones('id_zone'));

			foreach ($results as $rowValues)
				Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'module_country VALUE('.(int)$this->id.', '.(_PS_VERSION_ >= 1.5 ?  Context::getContext()->shop->id.',' : '').' '.(int)$rowValues['id_country'].')');
			
		}
		elseif (Tools::isSubmit('submitHipay'))
		{
			
			$accounts = array();
			foreach ($currencies as $currency)
			{
				if (Configuration::get('HIPAY_SITEID_'.$currency['iso_code']) != Tools::getValue('HIPAY_SITEID_'.$currency['iso_code']))
					Configuration::updateValue('HIPAY_CATEGORY_'.$currency['iso_code'], false);

				Configuration::updateValue('HIPAY_PASSWORD_'.$currency['iso_code'], trim(Tools::getValue('HIPAY_PASSWORD_'.$currency['iso_code'])));
				Configuration::updateValue('HIPAY_SITEID_'.$currency['iso_code'], trim(Tools::getValue('HIPAY_SITEID_'.$currency['iso_code'])));
				Configuration::updateValue('HIPAY_CATEGORY_'.$currency['iso_code'], Tools::getValue('HIPAY_CATEGORY_'.$currency['iso_code']));
				Configuration::updateValue('HIPAY_ACCOUNT_'.$currency['iso_code'], Tools::getValue('HIPAY_ACCOUNT_'.$currency['iso_code']));

				if ($this->env AND Tools::getValue('HIPAY_ACCOUNT_'.$currency['iso_code']))
					$accounts[Tools::getValue('HIPAY_ACCOUNT_'.$currency['iso_code'])] = 1;
			}
			
			$i = 1;
			$dataSync = 'http://www.prestashop.com/modules/hipay.png?mode='.($this->env ? 'prod' : 'test');
			foreach ($accounts as $account => $null)
				$dataSync .= '&account'.($i++).'='.urlencode($account);
			
			Configuration::updateValue('HIPAY_RATING', Tools::getValue('HIPAY_RATING'));
			
			$warnings .= $this->displayConfirmation($this->l('Configuration updated').'<img src="'.$dataSync.'" style="float:right" />');
		}
		elseif (Tools::isSubmit('submitHipayPaymentButton'))
		{
			Configuration::updateValue('HIPAY_PAYMENT_BUTTON', Tools::getValue('payment_button'));
		}
		
		// Check configuration
		$allow_url_fopen = ini_get('allow_url_fopen');
		$openssl = extension_loaded('openssl');
		$curl = extension_loaded('curl');
		$ping = ($allow_url_fopen AND $openssl AND $fd = fsockopen('payment.hipay.com', 443) AND fclose($fd));
		$online = (in_array(Tools::getRemoteAddr(), array('127.0.0.1', '::1')) ? false : true);
		$categories = true;
		$categoryRetrieval = true;
		
		foreach ($currencies as $currency)
		{
			$hipaySiteId = Configuration::get('HIPAY_SITEID_'.$currency['iso_code']);
			$hipayAccountId = Configuration::get('HIPAY_ACCOUNT_'.$currency['iso_code']);
			if ($hipaySiteId && $hipayAccountId && !count($this->getHipayCategories($hipaySiteId, $hipayAccountId)))
				$categoryRetrieval = false;

			if ((Configuration::get('HIPAY_SITEID_'.$currency['iso_code']) && !Configuration::get('HIPAY_CATEGORY_'.$currency['iso_code'])))
				$categories = false;
		}
		
		if (!$allow_url_fopen OR !$openssl OR !$curl OR !$ping OR !$categories OR !$categoryRetrieval OR !$online)
		{
			$warnings .= '
			<div class="warning warn">
				'.($allow_url_fopen ? '' : '<h3>'.$this->l('You are not allowed to open external URLs').'</h3>').'
				'.($curl ? '' : '<h3>'.$this->l('cURL is not enabled').'</h3>').'
				'.($openssl ? '' : '<h3>'.$this->l('OpenSSL is not enabled').'</h3>').'
				'.(($allow_url_fopen AND $openssl AND !$ping) ? '<h3>'.$this->l('Cannot access payment gateway').' '.HIPAY_GATEWAY_URL.' ('.$this->l('check your firewall').')</h3>' : '').'
				'.($online ? '' : '<h3>'.$this->l('Your shop is not online').'</h3>').'
				'.($categories ? '' : '<h3>'.$this->l('Hipay categories are not defined for each Site ID').'</h3>').'
				'.($categoryRetrieval ? '' : '<h3>'.$this->l('Impossible to retrieve Hipay categories. Please refer to your error log for more details.').'</h3>').'
			</div>';
		}
		
		// Get subscription form value
		$form_values = $this->getFormValues();

		// Lang of the button
		$iso_code = Context::getContext()->language->iso_code;
		if (!in_array($iso_code, array('fr', 'en', 'es', 'it')))
			$iso_code = 'en';

		$form_errors = '';
		$account_created = false;
		if (Tools::isSubmit('create_account_action'))
			$account_created = $this->processAccountCreation($form_errors);

		$link = $currentIndex.'&configure='.$this->name.'&token='.Tools::safeOutput(Tools::getValue('token'));
		$form = '
		<style>
			.hipay_label {float:none;font-weight:normal;padding:0;text-align:left;width:100%;line-height:30px}
			.hipay_help {vertical-align:middle}
			#hipay_table {border:1px solid #383838}
			#hipay_table td {border:1px solid #383838; width:250px; padding-left;8px; text-align:center}
			#hipay_table td.hipay_end {border-top:none}
			#hipay_table td.hipay_block {border-bottom:none}
			#hipay_steps_infos {border:none; margin-bottom:20px}
			/*#hipay_steps_infos td {border:none; width:70px; height:60px;padding-left;8px; text-align:left}*/
			#hipay_steps_infos td.tab2 {border:none; width:700px;; height:60px;padding-left;8px; text-align:left}
			#hipay_steps_infos td.hipay_end {border-top:none}
			#hipay_steps_infos td.hipay_block {border-bottom:none}
			#hipay_steps_infos td.hipay_block {border-bottom:none}
			#hipay_steps_infos .account-creation input[type=text], #hipay_steps_infos .account-creation select {width: 300px; margin-bottom: 5px}
			.hipay_subtitle {color: #777; font-weight: bold}
		</style>
	<fieldset>
		<legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->l('Hipay').'</legend>
		'.$warnings.'
		<span class="hipay_subtitle">'.$this->l('The fast, simple multimedia payment solution for everyone in France and Europe!').'</span><br />
		'.$this->l('Thanks to its adaptability and performance, Hipay has already won over 12,000 merchants and a million users. Its array of 15 of the most effective payment solutions in Europe offers your customers instant recognition and a reassuring guarantee for their consumer habits.').'
		<br />
		<br />'.$this->l('Once your account is activated you will receive more details by email.').'
		<br />'.$this->l('All merchant using Prestashop can benefit from special price by contacting the following email:').' <strong><a href="mailto:prestashop@hipay.com">prestashop@hipay.com</a></strong><br />
		<br /><strong>'.$this->l('Do not hesitate to contact us. The fees can decrease by 50%.').'</strong><br />
		<br />'.$this->l('Hipay boosts your sales Europe-wide thanks to:').'
		<ul>
			<li>'.$this->l('Payment solutions specific to each European country;').'</li>
			<li>'.$this->l('No subscription or installation charges;').'</li>
			<li>'.$this->l('Contacts with extensive experience of technical and financial issues;').'</li>
			<li>'.$this->l('Dedicated customer service;').'</li>
			<li>'.$this->l('Anti-fraud system and permanent monitoring for high-risk behaviour.').'</li>
		</ul>
		'.$this->l('Hipay is part of the Hi-Media Group (Allopass).').'
	</fieldset>
	<div class="clear">&nbsp;</div>
	<fieldset>
		<legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->l('Configuration').'</legend>
		'.$this->l('The configuration of Hipay is really easy and runs into 3 steps').'<br /><br />
		<table id="hipay_steps_infos" cellspacing="0" cellpadding="0">
			'.($account_created ? '<tr><td></td><td><div class="conf">'.$this->l('Account created!').'</div></td></tr>' : '').'
			<tr>
				<td valign="top"><img src="../modules/'.$this->name.'/1.png" alt="step 1" /></td>
				<td class="tab2">'.(Configuration::get('HIPAY_SITEID')
					? '<a href="https://www.hipay.com/auth" style="color:#D9263F;font-weight:700">'.$this->l('Log in to your merchant account').'</a><br />'
					: '<a id="account_creation" href="https://www.hipay.com/registration/register" style="color:#D9263F;font-weight:700"><img src="../modules/'.$this->name.'/button_'.$iso_code.'.jpg" alt="'.$this->l('Create a Hipay account').'" title="'.$this->l('Create a Hipay account').'" border="0" /></a>
					<br /><br />'.$this->l('If you already have an account you can go directly to step 2.')).'<br /><br />
				</td>
			</tr>
			<tr id="account_creation_form" style="'.(!Tools::isSubmit('create_account_action') || $account_created ? 'display: none;': '').'">
				<td></td>
				<td class="tab2">';
		if (!empty($form_errors))
		{
			$form .= '<div class="warning warn">';
			$form .= $form_errors;
			$form .= '</div>';
		}
		$form .= '
					<form class="account-creation" action="'.$currentIndex.'&configure='.$this->name.'&token='.Tools::safeOutput(Tools::getValue('token')).'" method="post">
						<div class="clear"><label for="email">'.$this->l('E-mail').'</label><input type="text" value="'.$form_values['email'].'" name="email" id="email"/></div>
						<div class="clear"><label for="firstname">'.$this->l('Firstname').'</label><input type="text" value="'.$form_values['firstname'].'" name="firstname" id="firstname"/></div>
						<div class="clear"><label for="lastname">'.$this->l('Lastname').'</label><input type="text" value="'.$form_values['lastname'].'" name="lastname" id="lastname"/></div>
						<div class="clear">
							<label for="currency">'.$this->l('Currency').'</label>
							<select name="currency" id="currency">
								<option value="EUR">'.$this->l('Euro').'</option>
								<option value="CAD">'.$this->l('Canadian dollar').'</option>
								<option value="USD">'.$this->l('United States Dollar').'</option>
								<option value="CHF">'.$this->l('Swiss franc').'</option>
								<option value="AUD">'.$this->l('Australian dollar').'</option>
								<option value="GBP">'.$this->l('British pound').'</option>
								<option value="SEK">'.$this->l('Swedish krona').'</option>
							</select>
						</div>
						<div class="clear">
							<label for="business-line">'.$this->l('Business line').'</label>
							<select name="business-line" id="business-line">';
		foreach ($this->getBusinessLine() as $business)
			if ($business->id == $form_values['business_line'])
				$form .= '<option value="'.$business->id.'" selected="selected">'.$business->label.'</option>';
			else
				$form .= '<option value="'.$business->id.'">'.$business->label.'</option>';
		$form .= '
							</select>
						</div>
						<div class="clear">
							<label for="website-topic">'.$this->l('Website topic').'</label>
							<select id="website-topic" name="website-topic"></select>
						</div>
						<div class="clear"><label for="contact-email">'.$this->l('Website contact e-mail').'</label><input type="text" value="'.$form_values['contact_email'].'" name="contact-email" id="contact-email"/></div>
						<div class="clear"><label for="website-name">'.$this->l('Website name').'</label><input type="text" value="'.$form_values['website_name'].'" name="website-name" id="website-name"/></div>
						<div class="clear"><label for="website-url">'.$this->l('Website URL').'</label><input type="text" value="'.$form_values['website_url'].'" name="website-url" id="website-url"/></div>
						<div class="clear"><label for="website-password">'.$this->l('Website merchant password').'</label><input type="text"  value="'.$form_values['password'].'"name="website-password" id="website-password"/></div>
						<div class="clear"><input type="submit" name="create_account_action"/></div>
					</form>
				</td>
			</tr>
			<tr>
				<td><img src="../modules/'.$this->name.'/2.png" alt="step 2" /></td>
				<td class="tab2">'.$this->l('Activate the Hipay solution in your Prestashop, it\'s free!').'</td>
			</tr>
			<tr><td></td><td>
		
		<form action="'.$link.'" method="post">
		<table id="hipay_table" cellspacing="0" cellpadding="0">
			<tr>
				<td style="">&nbsp;</td>
				<td style="height:40px;">Compte Hipay</td>
			</tr>';

		foreach ($currencies as $currency)
		{
			$form .= '<tr>
						<td class="hipay_block"><b>'.$this->l('Configuration in').' '.$currency['name'].' '.$currency['sign'].'</b></td>
						<td class="hipay_prod hipay_block" style="padding-left:10px">
							<label class="hipay_label" for="HIPAY_ACCOUNT_'.$currency['iso_code'].'">'.$this->l('Account number').' <a href="../modules/'.$this->name.'/screenshots/accountnumber.png" target="_blank"><img src="../modules/'.$this->name.'/help.png" class="hipay_help" /></a></label><br />
							<input type="text" id="HIPAY_ACCOUNT_'.$currency['iso_code'].'" name="HIPAY_ACCOUNT_'.$currency['iso_code'].'" value="'.Tools::safeOutput(Tools::getValue('HIPAY_ACCOUNT_'.$currency['iso_code'], Configuration::get('HIPAY_ACCOUNT_'.$currency['iso_code']))).'" /><br />
							<label class="hipay_label" for="HIPAY_PASSWORD_'.$currency['iso_code'].'">'.$this->l('Merchant password').' <a href="../modules/'.$this->name.'/screenshots/merchantpassword.png" target="_blank"><img src="../modules/'.$this->name.'/help.png" class="hipay_help" /></a></label><br />
							<input type="text" id="HIPAY_PASSWORD_'.$currency['iso_code'].'" name="HIPAY_PASSWORD_'.$currency['iso_code'].'" value="'.Tools::safeOutput(Tools::getValue('HIPAY_PASSWORD_'.$currency['iso_code'], Configuration::get('HIPAY_PASSWORD_'.$currency['iso_code']))).'" /><br />
							<label class="hipay_label" for="HIPAY_SITEID_'.$currency['iso_code'].'">'.$this->l('Site ID').' <a href="../modules/'.$this->name.'/screenshots/siteid.png" target="_blank"><img src="../modules/'.$this->name.'/help.png" class="hipay_help" /></a></label><br />
							<input type="text" id="HIPAY_SITEID_'.$currency['iso_code'].'" name="HIPAY_SITEID_'.$currency['iso_code'].'" value="'.Tools::safeOutput(Tools::getValue('HIPAY_SITEID_'.$currency['iso_code'], Configuration::get('HIPAY_SITEID_'.$currency['iso_code']))).'" /><br />';

			if ($ping && ($hipaySiteId = (int)Configuration::get('HIPAY_SITEID_'.$currency['iso_code'])) && ($hipayAccountId = (int)Configuration::get('HIPAY_ACCOUNT_'.$currency['iso_code'])))
			{
				$form .= '	<label for="HIPAY_CATEGORY_'.$currency['iso_code'].'" class="hipay_label">'.$this->l('Category').'</label><br />
							<select id="HIPAY_CATEGORY_'.$currency['iso_code'].'" name="HIPAY_CATEGORY_'.$currency['iso_code'].'">';
				foreach ($this->getHipayCategories($hipaySiteId, $hipayAccountId) as $id => $name)
					$form.= '	<option value="'.(int)$id.'" '.(Tools::getValue('HIPAY_CATEGORY_'.$currency['iso_code'], Configuration::get('HIPAY_CATEGORY_'.$currency['iso_code'])) == $id ? 'selected="selected"' : '').'>'.htmlentities($name, ENT_COMPAT, 'UTF-8').'</option>';
				$form .= '	</select><br />';
			}

			$form .= '	</td>
					</tr>
					<tr><td class="hipay_end">&nbsp;</td><td class="hipay_prod hipay_end">&nbsp;</td>';
			$form .= '</tr>';
		}
		
		$form .= '</table>
				<hr class="clear" />
				<label for="HIPAY_RATING">'.$this->l('Authorized age group').'</label>
				<div class="margin-form">
					<select id="HIPAY_RATING" name="HIPAY_RATING">
						<option value="ALL">'.$this->l('For all ages').'</option>
						<option value="+12" '.(Tools::getValue('HIPAY_RATING', Configuration::get('HIPAY_RATING')) == '+12' ? 'selected="selected"' : '').'>'.$this->l('For ages 12 and over').'</option>
						<option value="+16" '.(Tools::getValue('HIPAY_RATING', Configuration::get('HIPAY_RATING')) == '+16' ? 'selected="selected"' : '').'>'.$this->l('For ages 16 and over').'</option>
						<option value="+18" '.(Tools::getValue('HIPAY_RATING', Configuration::get('HIPAY_RATING')) == '+18' ? 'selected="selected"' : '').'>'.$this->l('For ages 18 and over').'</option>
					</select>
				</div>
				<hr class="clear" />
				<p>'.$this->l('Notice: please verify that the currency mode you have chosen in the payment tab is compatible with your Hipay account(s).').'</p>
				<input type="submit" name="submitHipay" value="'.$this->l('Update configuration').'" class="button" />
			</form>

				</td>
			</tr>
			<tr>
				<td><img src="../modules/'.$this->name.'/3.png" alt="step 3" /></td> 
				<td class="tab2">'.$this->l('Choose a set of buttons for your shop Hipay').'</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<form action="'.$currentIndex.'&configure='.$this->name.'&token='.Tools::safeOutput(Tools::getValue('token')).'" method="post">
						<table>
							<tr>
								<td>
									<input type="radio" name="payment_button" id="payment_button_be" value="be" '.(Configuration::get('HIPAY_PAYMENT_BUTTON') == 'be' ? 'checked="checked"' : '').'/>
								</td>
								<td>
									<label style="width: auto" for="payment_button_be"><img src="../modules/'.$this->name.'/payment_button/BE.png" /></label>
								</td>
								<td style="padding-left: 40px;">
									<input type="radio" name="payment_button" id="payment_button_de" value="de" '.(Configuration::get('HIPAY_PAYMENT_BUTTON') == 'de' ? 'checked="checked"' : '').'/>
								</td>
								<td>
									<label style="width: auto" for="payment_button_de"><img src="../modules/'.$this->name.'/payment_button/DE.png" /></label>
								</td>
							</tr>
							<tr>
								<td>
									<input type="radio" name="payment_button" id="payment_button_fr" value="fr" '.(Configuration::get('HIPAY_PAYMENT_BUTTON') == 'fr' ? 'checked="checked"' : '').'/>
								</td>
								<td>
									<label style="width: auto" for="payment_button_fr"><img src="../modules/'.$this->name.'/payment_button/FR.png" /></label>
								</td>
								<td style="padding-left: 40px;">
									<input type="radio" name="payment_button" id="payment_button_gb" value="gb" '.(Configuration::get('HIPAY_PAYMENT_BUTTON') == 'gb' ? 'checked="checked"' : '').'/>
								</td>
								<td>
									<label style="width: auto" for="payment_button_gb"><img src="../modules/'.$this->name.'/payment_button/GB.png" /></label>
								</td>
							</tr>
							<tr>
								<td>
									<input type="radio" name="payment_button" id="payment_button_it" value="it" '.(Configuration::get('HIPAY_PAYMENT_BUTTON') == 'it' ? 'checked="checked"' : '').'/>
								</td>
								<td>
									<label style="width: auto" for="payment_button_it"><img src="../modules/'.$this->name.'/payment_button/IT.png" /></label>
								</td>
								<td style="padding-left: 40px;">
									<input type="radio" name="payment_button" id="payment_button_nl" value="nl" '.(Configuration::get('HIPAY_PAYMENT_BUTTON') == 'nl' ? 'checked="checked"' : '').'/>
								</td>
								<td>
									<label style="width: auto" for="payment_button_nl"><img src="../modules/'.$this->name.'/payment_button/NL.png" /></label>
								</td>
							</tr>
							<tr>
								<td>
									<input type="radio" name="payment_button" id="payment_button_pt" value="pt" '.(Configuration::get('HIPAY_PAYMENT_BUTTON') == 'pt' ? 'checked="checked"' : '').'/>
								</td>
								<td>
									<label style="width: auto" for="payment_button_pt"><img src="../modules/'.$this->name.'/payment_button/PT.png" /></label>
								</td>
							</tr>
						</table>
						<input type="submit" name="submitHipayPaymentButton" value="'.$this->l('Update configuration').'" class="button" />
					</form>
				</td>
			</tr>
		</table>
		<script type="text/javascript">
			function loadWebsiteTopic()
			{
				var locale = "'.$this->formatLanguageCode(Context::getContext()->language->iso_code).'";
				var business_line = $("#business-line").val();
				$.ajax(
				{
					type: "POST",
					url: "'.__PS_BASE_URI__.'modules/hipay/ajax_websitetopic.php",
					data:
					{
						locale: locale,
						business_line: business_line,
						token: "'.substr(Tools::encrypt('hipay/websitetopic'), 0, 10).'"
					},
					success: function(result)
					{
						$("#website-topic").html(result);
					}
				});
			}
			$("#business-line").change(function() { loadWebsiteTopic() });
			loadWebsiteTopic();
		</script>
		</fieldset>
		<br />
		';
		
		$form .= '
		<fieldset>
			<legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->l('Zones restrictions').'</legend>
			'.$this->l('Select the authorized shipping zones').'<br /><br />
			<form action="'.$currentIndex.'&configure=hipay&token='.Tools::safeOutput(Tools::getValue('token')).'" method="post">
				<table cellspacing="0" cellpadding="0" class="table">
					<tr>
						<th class="center">'.$this->l('ID').'</th>
						<th>'.$this->l('Zones').'</th>
						<th align="center"><img src="../modules/'.$this->name.'/logo.gif" /></th>
					</tr>
		';
		
		$result = Db::getInstance()->ExecuteS('
			SELECT `id_zone`, `name`
			FROM '._DB_PREFIX_.'zone
			WHERE `active` = 1
		');

		foreach ($result as $rowNumber => $rowValues)
		{
			$form .= '<tr>';
			$form .= '<td>'.$rowValues['id_zone'].'</td>';
			$form .= '<td>'.$rowValues['name'].'</td>';
			$chk = null;
			if (Configuration::get('HIPAY_AZ_'.$rowValues['id_zone']) == 'ok')
				$chk = "checked ";
			
			$form .= '<td align="center"><input type="checkbox" name="HIPAY_AZ_'.$rowValues['id_zone'].'" value="ok" '.$chk.'/>';
			$form .= '<input type="hidden" name="HIPAY_AZ_ALL_'.$rowValues['id_zone'].'" value="ok" /></td>';
			$form .= '</tr>';
		}
		
		$form .= '
				</table><br>
				<input type="submit" name="submitHipayAZ" value="'.$this->l('Update zones').'" class="button" />
			</form>
		</fieldset>
		<script type="text/javascript">
			function switchHipayAccount(prod) {
				if (prod)
				{';
		foreach ($currencies as $currency)
			$form .= '
					$("#HIPAY_ACCOUNT_'.$currency['iso_code'].'").css("background-color", "#FFFFFF");
					$("#HIPAY_PASSWORD_'.$currency['iso_code'].'").css("background-color", "#FFFFFF");
					$("#HIPAY_SITEID_'.$currency['iso_code'].'").css("background-color", "#FFFFFF");';
		$form .= '	$(".hipay_prod").css("background-color", "#AADEAA");
					$(".hipay_test").css("background-color", "transparent");
					$(".hipay_prod_span").css("font-weight", "700");
					$(".hipay_test_span").css("font-weight", "200");
				}
				else
				{';
		foreach ($currencies as $currency)
			$form .= '
					$("#HIPAY_ACCOUNT_'.$currency['iso_code'].'").css("background-color", "#EEEEEE");
					$("#HIPAY_PASSWORD_'.$currency['iso_code'].'").css("background-color", "#EEEEEE");
					$("#HIPAY_SITEID_'.$currency['iso_code'].'").css("background-color", "#EEEEEE");';
		$form .= '	$(".hipay_prod").css("background-color", "transparent");
					$(".hipay_test").css("background-color", "#AADEAA");
					$(".hipay_prod_span").css("font-weight", "200");
					$(".hipay_test_span").css("font-weight", "700");
				}
			}
			switchHipayAccount('.(int)$this->env.');';
		
		if (class_exists('SoapClient'))
		{
			$form .= '
				$(\'#account_creation\').click(function() {
					$(\'#account_creation_form\').show();
					return false;
				});
			';
		}
		
		$form .= '
		</script>';
		
		if ($this->ws_client == false)
			return $this->displayError('To work properly the module need the Soap library to be installed.').$form;
		return $form;
	}
	
	public static function getWsClient()
	{
		$ws_client = null;
		if (class_exists('SoapClient'))
		{
			if (is_null($ws_client))
			{
				$options = array(
					'location' => self::WS_URL,
					'uri' => self::WS_SERVER
				);
				$ws_client = new SoapClient(null, $options);
			}
			return $ws_client;
		}
		return false;
	}
	
	protected function getFormValues()
	{
		$values = array();
		
		if (Tools::isSubmit('email'))
			$values['email'] = Tools::getValue('email');
		else
			$values['email'] = Configuration::get('PS_SHOP_EMAIL');
		
		if (Tools::isSubmit('firstname'))
			$values['firstname'] = Tools::getValue('firstname');
		else
			$values['firstname'] = Context::getContext()->employee->firstname;
		
		if (Tools::isSubmit('lastname'))
			$values['lastname'] = Tools::getValue('lastname');
		else
			$values['lastname'] = Context::getContext()->employee->lastname;
			
		$values['currency'] = Tools::getValue('currency');
		
		if (Tools::isSubmit('contact-email'))
			$values['contact_email'] = Tools::getValue('contact-email');
		else
			$values['contact_email'] = Configuration::get('PS_SHOP_EMAIL');
		
		if (Tools::isSubmit('website-name'))
			$values['website_name'] = Tools::getValue('website-name');
		else
			$values['website_name'] = Configuration::get('PS_SHOP_NAME');
			
		if (Tools::isSubmit('website-url'))
			$values['website_url'] = Tools::getValue('website-url');
		else
			$values['website_url'] = Configuration::get('PS_SHOP_DOMAIN');
			
		$values['business_line'] = Tools::getValue('business-line');
		
		$values['password'] = Tools::getValue('website-password');
		
		return $values;
	}
	
	protected function getBusinessLine()
	{
		try
		{
			$iso_lang = Context::getContext()->language->iso_code;
			$format_language = $this->formatLanguageCode($iso_lang);
			
			if ($this->ws_client !== false)
				$business_line = $this->ws_client->getBusinessLine($format_language);
		}
		catch (Exception $e)
		{
			return array();
		}

		if (isset($business_line) && ($business_line !== false))
			return $business_line;
		return array();
	}
	
	protected function processAccountCreation(&$form_errors)
	{
		$form_values = $this->getFormValues();

		// STEP 1: Check if the email is available in Hipay
		try
		{
			if ($this->ws_client !== false)
				$is_available = $this->ws_client->isAvailable($form_values['email']);
		}
		catch (Exception $e)
		{
			$form_errors = $this->l('Could not connect to host');
			return false;
		}

		if (!$is_available)
		{
			$form_errors = $this->l('An account already exists with this email address');
			return false;
		}

		// STEP 2: Account creation
		try
		{		
			if ($this->ws_client !== false)
				$return = $this->ws_client->createWithWebsite(
					array(
						'email' => $form_values['email'],
						'firstname' => $form_values['firstname'],
						'lastname' => $form_values['lastname'],
						'currency' => $form_values['currency'],
						'locale' => $this->formatLanguageCode(Context::getContext()->language->language_code),
						'ipAddress' => $_SERVER['REMOTE_ADDR'],
						'websiteBusinessLineId' => $form_values['business_line'],
						'websiteTopicId' => Tools::getValue('website-topic'),
						'websiteContactEmail' => $form_values['contact_email'],
						'websiteName' => $form_values['website_name'],
						'websiteUrl' => $form_values['website_url'],
						'websiteMerchantPassword' => $form_values['password']
				));
		}
		catch (Exception $e)
		{
			$form_errors = $this->l('Could not connect to host');
			return false;
		}

		if ($return !== false)
		{
			if ($return['error'] == 0)
			{
				Configuration::updateValue('HIPAY_ACCOUNT_'.$form_values['currency'], $return['account_id']);
				Configuration::updateValue('HIPAY_PASSWORD_'.$form_values['currency'], Tools::getValue('website-password'));
				Configuration::updateValue('HIPAY_SITEID_'.$form_values['currency'], $return['site_id']);
				return true;
			}

			if ($return['code'] == 1)
			{
				$fields = array(
					'firstname' => $this->l('firstname'),
					'lastname' => $this->l('lastname'),
					'email' => $this->l('email'),
					'currency' => $this->l('currency'),
					'websiteBusinessLineId' => $this->l('business line'),
					'websiteTopicId' => $this->l('website topic'),
					'websiteContactEmail' => $this->l('website contact email'),
					'websiteName' => $this->l('website name'),
					'websiteUrl' => $this->l('website url'),
					'websiteMerchantPassword' => $this->l('website merchant password'),
				);
				$fieldnames_error = array();
				foreach ($return['vars'] as $fieldtype)
					if(isset($fields[$fieldtype]))
						$fieldnames_error[] = $fields[$fieldtype];
					
				$form_errors = sprintf($this->l('Some fields are not correct. Please check the fields: %s'), implode(', ', $fieldnames_error));
				return false;
			}
			elseif ($return['code'] == 2)
			{
				$form_errors = sprintf($this->l('An error occurs durring the account creation: %s'), Tools::htmlentitiesUTF8($return['description']));
				return false;
			}
		}
			$form_errors = $this->l('Unknow error');
			return false;
	}

	private function getHipayCategories($hipaySiteId, $hipayAccountId)
	{
		try
		{
			if ($this->ws_client !== false)
				return $this->ws_client->getCategoryList(array('site_id' => $hipaySiteId, 'account_id' => $hipayAccountId));
		}
		catch (Exception $e)
		{
			return array();
		}
		return array();
	}
	
	// Retro compatibility with 1.2.5
	static private function MysqlGetValue($query)
	{
		$row = Db::getInstance()->getRow($query);
		return array_shift($row);
	}
}
