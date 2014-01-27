<?php
/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http:// opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http:// www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license http:// opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

if (file_exists(dirname(__FILE__).'/classes/GlobKurier.php'))
	require_once (dirname(__FILE__).'/classes/GlobKurier.php');

class GlobKurier extends Module {

	private $login;
	private $password;
	private $apikey;
	private $arr_login_err;
	private $arr_login_result;
	private $arr_register_err;
	private $arr_register_result;
	private $arr_apikey_err;
	private $arr_apikey_result;
	private $arr_order_err;
	private $arr_order_result;
	private $arr_order_products;
	private $arr_addons_err;
	private $arr_addons_result;

	/**
	* Construct Method
	*/
	public function __construct()
	{
		$this->name = 'globkurier';
		$this->tab = 'shipping_logistics';
		$this->version = '1.1';
		$this->author = 'GlobKurier | Solik Tomasz <info[at]tomaszsolik.pl>';
		$this->need_instance = 0;

		parent::__construct();

		$this->displayName = $this->l('GlobKurier.pl');
		$this->description = $this->l('Save up to 80% off on UPS, DPD, K-EX, DHL with GlobKurier module!');
		$this->confirmUninstall = $this->l('Warning: all the data saved in your database will be deleted. Are you sure you want uninstall this module?');

		/* Backward compatibility */
		if (version_compare(_PS_VERSION_, '1.5.0.0', '<'))
			require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');

		// Custom value
		$this->login = Configuration::get($this->name.'_LOGIN');
		$this->password = Configuration::get($this->name.'_PASSWORD');
		$this->apikey = Configuration::get($this->name.'_API_KEY');
		$this->arr_login_err = array();
		$this->arr_login_result = array();
		$this->arr_register_err = array();
		$this->arr_register_result = array();
		$this->arr_apikey_err = array();
		$this->arr_apikey_result = array();
		$this->arr_order_err = array();
		$this->arr_order_result = array();
		$this->arr_order_products = array();
		$this->arr_addons_err = array();
		$this->arr_addons_result = array();
	}

	/**
	* GlobKurier module configuration page
	* 
	* @param void
	* @return templates view
	*/
	public function getContent()
	{
		// Login / Passwd / ApiKey data to save
		if (Tools::isSubmit('gk_save'))
		{
			Configuration::updateValue($this->name.'_LOGIN', GlobKurierTools::gkEncryptString(Tools::getValue('gk_login')));
			Configuration::updateValue($this->name.'_PASSWORD', GlobKurierTools::gkEncryptString(Tools::getValue('gk_password')));
			Configuration::updateValue($this->name.'_API_KEY', GlobKurierTools::gkEncryptString(Tools::getValue('gk_api_key')));
		}
		// Login
		$login = Configuration::get($this->name.'_LOGIN');
		$password = Configuration::get($this->name.'_PASSWORD');
		$apikey = Configuration::get($this->name.'_API_KEY');
		$this->loginAction($login, $password, $apikey);
		// Register
		$this->registrationAction();
		// View
		$this->context->controller->addCSS($this->_path.'css/main.css', 'all');
		$this->context->controller->addJquery();
		$this->context->controller->addJS($this->_path.'js/globkurier.js', 'all');
		$address = Configuration::get($this->name.'_S_ADDRESS_STREET').' '.Configuration::get($this->name.'_S_ADDRESS_HOME');

		if (Configuration::get($this->name.'_S_ADDRESS_LOCAL'))
			$address .= '/'.Configuration::get($this->name.'_S_ADDRESS_LOCAL');
		$this->smarty->assign(
						array(
						'login' => GlobKurierTools::gkDecryptString($login),
						'password' => GlobKurierTools::gkDecryptString($password),
						'apikey' => GlobKurierTools::gkDecryptString($apikey),
						'arr_login_err' => $this->arr_login_err,
						'arr_login_result' => $this->arr_login_result,
						'arr_register_err' => $this->arr_register_err,
						'arr_register_result' => $this->arr_register_result,
						'arr_apikey_err' => $this->arr_apikey_err,
						'arr_apikey_result' => $this->arr_apikey_result,
						'name' => Configuration::get($this->name.'_S_NAME').' '.Configuration::get($this->name.'_S_SURNAME'),
						'company' => Configuration::get($this->name.'_S_COMPANY').' '.Configuration::get($this->name.'_S_NIP'),
						'address' => $address,
						'address_cont' => Configuration::get($this->name.'_S_ADDRESS_CITY').' '.Configuration::get($this->name.'_S_ADDRESS_ZIPCODE'),
						'phone' => Configuration::get($this->name.'_S_PHONE'),
						'iban' => Configuration::get($this->name.'_S_IBAN'),
						'gk_api_email' => Tools::getValue('gk_api_email'),
						'gk_email' => Tools::getValue('gk_email'),
						'gk_rpassword' => Tools::getValue('gk_rpassword'),
						'gk_rpassword2' => Tools::getValue('gk_rpassword2'),
						'gk_type' => Tools::getValue('gk_type'),
						'gk_company' => Tools::getValue('gk_company'),
						'gk_nip' => Tools::getValue('gk_nip'),
						'gk_name' => Tools::getValue('gk_name'),
						'gk_surname' => Tools::getValue('gk_surname'),
						'gk_street' => Tools::getValue('gk_street'),
						'gk_house' => Tools::getValue('gk_house'),
						'gk_local' => Tools::getValue('gk_local'),
						'gk_city' => Tools::getValue('gk_city'),
						'gk_zip' => Tools::getValue('gk_zip'),
						'gk_phone' => Tools::getValue('gk_phone')
					));
		return $this->display(__FILE__, 'views/templates/admin/form_configuration.tpl');
	}

	/**
	* Order / DisplayAdminOrder
	* 
	* @param array $params - params of
	* @return templates view
	*/
	public function hookDisplayAdminOrder($params)
	{
		$obj_globkurier = new GlobKurierLogin($this->login, $this->password, $this->apikey);
		$obj_json_login = Tools::jsonDecode($obj_globkurier->sendData());
		if ($obj_json_login->status)
		{
			foreach ($obj_json_login->userParams as $key => $item)
				$this->arr_login_result[$key] = $item;
		}

		$obj_globkurier = new GlobKurierInsertOrder($this->login, $this->password, $this->apikey);
		$obj_delivery_address = new Address($params['cart']->id_address_delivery);
		$obj_customer = new Customer($params['cart']->id_customer);
		$arr_product_detail = $this->context->cart->getProducts();

		// Make order
		$this->doOrder($params);

		$this->context->controller->addCSS($this->_path.'css/main.css', 'all');
		$this->context->controller->addCSS($this->_path.'css/jquery-ui-1.8.14.custom.css', 'all');
		$this->context->controller->addCSS($this->_path.'css/content_picker.css', 'all');
		$this->context->controller->addCSS($this->_path.'css/time_picker.css', 'all');
		$this->context->controller->addJquery();
                $this->context->controller->addJS($this->_path.'js/rsv_validator.js', 'all');
		$this->context->controller->addJS($this->_path.'js/jquery-ui-1.8.14.custom.min.js', 'all');
		$this->context->controller->addJS($this->_path.'js/jquery.customDataPicker.js', 'all');
		$this->context->controller->addJS($this->_path.'js/pricing.js', 'all');
		$this->context->controller->addJS($this->_path.'js/content_picker.js', 'all');
		$this->context->controller->addJS($this->_path.'js/time_picker.js', 'all');
		$this->context->controller->addJS($this->_path.'js/globkurier.js', 'all');

		if (!$obj_json_login->status)
			return $this->display(__FILE__, 'views/templates/hook/no_access.tpl');

		$obj_order_gk = GlobKurierOrder::getByIdOrder($params['id_order']);
		if ($obj_order_gk != false && $obj_order_gk->flag == GlobKurierOrder::PS_ORDER_GK_PROCESS)
		{
			$this->smarty->assign(array('gk_number' => $obj_order_gk->gk_number));
			return $this->display(__FILE__, 'views/templates/hook/form_order_close.tpl');
		}

		$this->smarty->assign(
				array(
					'arr_login_result' => $this->arr_login_result,
					'arr_order_err' => $this->arr_order_err,
					'arr_order_result' => $this->arr_order_result,
					'arr_order_products' => $this->arr_order_products,
					'arr_addons_err' => $this->arr_addons_err,
					'arr_addons_result' => $this->arr_addons_result,
					'client_id' => Configuration::get($this->name.'_C_NUMBER'),
					'sender_name' => Configuration::get($this->name.'_S_COMPANY').' '.Configuration::get($this->name.'_S_NAME').' '.Configuration::get($this->name.'_S_SURNAME'),
					'sender_email' => GlobKurierTools::gkDecryptString(Configuration::get($this->name.'_LOGIN')),
					'sender_address1' => Configuration::get($this->name.'_S_ADDRESS_STREET'),
					'sender_address2' => Configuration::get($this->name.'_S_ADDRESS_HOME').'/'.Configuration::get($this->name.'_S_ADDRESS_LOCAL'),
					'sender_city' => Configuration::get($this->name.'_S_ADDRESS_CITY'),
					'sender_zipcode' => Configuration::get($this->name.'_S_ADDRESS_ZIPCODE'),
					'sender_country' => Tools::getValue('sender_country'),
					'sender_contact_person' => Configuration::get($this->name.'_S_NAME').' '.Configuration::get($this->name.'_S_SURNAME'),
					'sender_phone' => Configuration::get($this->name.'_S_PHONE'),
					'recipient_name' => ((Tools::getValue('recipient_name') == false) ? $obj_delivery_address->firstname.' '.$obj_delivery_address->lastname : Tools::getValue('recipient_name')),
					'recipient_email' => ((Tools::getValue('recipient_email') == false) ? $obj_customer->email : Tools::getValue('recipient_email')),
					'recipient_address1' => ((Tools::getValue('recipient_address1') == false) ? $obj_delivery_address->address1 : Tools::getValue('recipient_address1')),
					'recipient_address2' => ((Tools::getValue('recipient_address2') == false) ? $obj_delivery_address->address2 : Tools::getValue('recipient_address2')),
					'recipient_city' => ((Tools::getValue('recipient_city') == false) ? $obj_delivery_address->city : Tools::getValue('recipient_city')),
					'recipient_zipcode' => ((Tools::getValue('recipient_zipcode') == false) ? $obj_delivery_address->postcode : Tools::getValue('recipient_zipcode')),
					'recipient_country' => Tools::getValue('recipient_country'),
					'recipient_contact_person' => ((Tools::getValue('recipient_contact_person') == false) ? $obj_delivery_address->firstname.' '.$obj_delivery_address->lastname : Tools::getValue('recipient_contact_person')),
					'recipient_phone' => ((Tools::getValue('recipient_phone') == false) ? $obj_delivery_address->phone_mobile : Tools::getValue('recipient_phone')),
					'iban' => Configuration::get($this->name.'_S_IBAN'),
					'country' => GlobKurierCountry::getAllCountry(),
					'parcel_count' => Tools::getValue('parcel_count'),
					'parcel_weight' => Tools::getValue('parcel_weight'),
					'parcel_lenght' => Tools::getValue('parcel_lenght'),
					'parcel_width' => Tools::getValue('parcel_width'),
					'parcel_height' => Tools::getValue('parcel_height'),
					'parcel_content' => Tools::getValue('parcel_content'),
					'pickup_date' => ((Tools::getValue('pickup_date') == false) ? GlobKurierTools::getCorrectDate() : Tools::getValue('pickup_date')),
					'pickup_time_from' => Tools::getValue('pickup_time_from'),
					'pickup_time_to' => Tools::getValue('pickup_time_to')
				)
		);
		return $this->display(__FILE__, 'views/templates/hook/form_order_open.tpl');
	}

	/**
	* Process Order
	* 
	* @param array $params
	* @return void
	*/
	public function doOrder($params)
	{
		if (Tools::isSubmit('gk_process_order'))
		{
			if (Tools::getValue('declared-value') != false && Tools::getValue('declared-value') == 0)
				$this->arr_order_err[] = $this->l('Please fill the declared value.');

			$obj_order = new Order((int)$params['cart']->id);
			$obj_order_gk = GlobKurierOrder::getByIdOrder($params['id_order']);

			if (!$obj_order_gk)
				$order_number = GlobKurierTools::getOrderNumber();
			else
				$order_number = $obj_order_gk->order_number;

			$obj_json_save_order = new GlobKurierSaveOrder($this->login, $this->password, $this->apikey);
			// Basic
			$obj_json_save_order->setIntIdCart((int)$params['cart']->id);
			$obj_json_save_order->setIntIdCustomer((int)$params['cart']->id_customer);
			$obj_json_save_order->setStrShopKey((string)Configuration::get($this->name.'_SHOP_KEY'));
			$obj_json_save_order->setStrSecureKey((string)$params['cart']->secure_key);
			$obj_json_save_order->setStrReference((string)$obj_order->reference);
			$obj_json_save_order->setStrOrderNumber((string)$order_number);
			$obj_json_save_order->setStrBaseService((string)Tools::getValue('base_service'));
			$obj_json_save_order->setStrDate((string)$params['cart']->date_upd);
			$obj_json_save_order->setStrPickupDate((string)Tools::getValue('pickup_date'));
			$obj_json_save_order->setStrPickupTimeFrom((string)Tools::getValue('pickup_time_from'));
			$obj_json_save_order->setStrPickupTimeTo((string)Tools::getValue('pickup_time_to'));
			$obj_json_save_order->setPaymentType((string)Tools::getValue('payment'));
			// Parcel params
			$obj_json_save_order->setStrContent((string)Tools::getValue('parcel_content'));
			$obj_json_save_order->setFloLength(((float)Tools::getValue('parcel_lenght')));
			$obj_json_save_order->setFloWidth((float)Tools::getValue('parcel_width'));
			$obj_json_save_order->setFloHeight((float)Tools::getValue('parcel_height'));
			$obj_json_save_order->setFloWeight((float)Tools::getValue('parcel_weight'));
			$obj_json_save_order->setIntParcels((int)Tools::getValue('parcel_count'));
			// Addons
			$obj_json_save_order->setArrAddons(Tools::getValue('additional_services'));
			$obj_json_save_order->setFloCodAmount((float)Tools::getValue('cod_amount'));
			$obj_json_save_order->setStrCodAccount((string)Tools::getValue('cod_account_number'));
			$obj_json_save_order->setFloInsuranceAmount((float)Tools::getValue('insurance_amount'));
			$obj_json_save_order->setFloDeclaredValue((float)Tools::getValue('declared_value'));
			// Sender
			$obj_json_save_order->setStrSenderName((string)Tools::getValue('sender_name'));
			$obj_json_save_order->setStrSenderAddress1((string)Tools::getValue('sender_address1'));
			$obj_json_save_order->setStrSenderAddress2((string)Tools::getValue('sender_address2'));
			$obj_json_save_order->setStrSenderZipCode((string)Tools::getValue('sender_zipcode'));
			$obj_json_save_order->setStrSenderCity((string)Tools::getValue('sender_city'));
			$obj_json_save_order->setStrSenderCountry((int)Tools::getValue('sender_country'));
			$obj_json_save_order->setStrSenderPhone((string)Tools::getValue('sender_phone'));
			$obj_json_save_order->setStrSenderMail((string)Tools::getValue('sender_email'));
			// Recipient
			$obj_json_save_order->setStrRecipientName((string)Tools::getValue('recipient_name'));
			$obj_json_save_order->setStrRecipientAddress1((string)Tools::getValue('recipient_address1'));
			$obj_json_save_order->setStrRecipientAddress2((string)Tools::getValue('recipient_address2'));
			$obj_json_save_order->setStrRecipientZipCode((string)Tools::getValue('recipient_zipcode'));
			$obj_json_save_order->setStrRecipientCity((string)Tools::getValue('recipient_city'));
			$obj_json_save_order->setStrRecipientCountry((int)Tools::getValue('recipient_country'));
			$obj_json_save_order->setStrRecipientPhone((string)Tools::getValue('recipient_phone'));
			$obj_json_save_order->setStrRecipientMail((string)Tools::getValue('recipient_email'));
			// Token
			$obj_json_save_order->setStrToken(sha1((string)Tools::getValue('sender_name').$this->apikey));
			$obj_json_save_order = Tools::jsonDecode($obj_json_save_order->sendData());
			if ($obj_json_save_order->status == true)
			{
				if (!$obj_order_gk)
				{
					Db::getInstance()->insert('globkurier_order', array(
						'id_order'  => (int)$params['id_order'],
						'id_cart'  => (int)$params['cart']->id,
						'id_customer'  => (int)$params['cart']->id_customer,
						'order_number'  => pSQL($order_number),
						'gk_number'  => $obj_json_save_order->params->nrgk,
						'flag'  => GlobKurierOrder::PS_ORDER_GK_PROCESS
					));
				}
				else
				{
					Db::getInstance()->update('globkurier_order', array(
						'flag' => GlobKurierOrder::PS_ORDER_GK_PROCESS,
						'gk_number' => $obj_json_save_order->params->nrgk,
					), 'order_number = '.pSQL($order_number));
				}
			}
			else
				$this->arr_order_err[] = $obj_json_save_order->error;
		}
	}

	/**
	* ActionValidateOrder
	* 
	* @param array $params
	* @return void
	*/
	public function hookActionValidateOrder($params)
	{
		$order_number = GlobKurierTools::getOrderNumber();

		$obj_globkurier_order = new GlobKurierOrder();
		$obj_globkurier_order->id_order = (int)$params['order']->id;
		$obj_globkurier_order->id_cart = (int)$params['cart']->id;
		$obj_globkurier_order->id_customer = (int)$params['cart']->id_customer;
		$obj_globkurier_order->order_number = (string)$order_number;
		$obj_globkurier_order->flag = GlobKurierOrder::PS_ORDER_NOT_SYNC;

		$obj_globkurier = new GlobKurierLogin($this->login, $this->password, $this->apikey);
		$obj_json_login = Tools::jsonDecode($obj_globkurier->sendData());

		if ($obj_json_login->status == true)
		{
			$obj_delivery_address = new Address($params['cart']->id_address_delivery);
			$obj_globkurier_insert_order = new GlobKurierInsertOrder($this->login, $this->password, $this->apikey);
			$obj_globkurier_insert_order->setIntIdCart((int)$params['cart']->id);
			$obj_globkurier_insert_order->setStrReference((string)$params['order']->reference);
			$obj_globkurier_insert_order->setStrOrderNumber((string)$order_number);
			$obj_globkurier_insert_order->setIntIdCustomer((int)$params['cart']->id_customer);
			$obj_globkurier_insert_order->setStrSecureKey((string)$params['cart']->secure_key);
			$obj_globkurier_insert_order->setStrShopKey(Configuration::get('GLOBKURIER_SHOP_KEY'));
			$obj_globkurier_insert_order->setStrDate((string)$params['cart']->date_upd);
			$obj_globkurier_insert_order->setStrRecipientName((string)$obj_delivery_address->company.' '.$obj_delivery_address->lastname.' '.$obj_delivery_address->firstname);
			$obj_globkurier_insert_order->setStrRecipientAddress1((string)$obj_delivery_address->address1);
			$obj_globkurier_insert_order->setStrRecipientAddress2((string)$obj_delivery_address->address2);
			$obj_globkurier_insert_order->setStrRecipientZipCode((string)$obj_delivery_address->postcode);
			$obj_globkurier_insert_order->setStrRecipientCity((string)$obj_delivery_address->city);
			$obj_globkurier_insert_order->setStrRecipientPhone((string)$obj_delivery_address->phone_mobile);
			$obj_globkurier_insert_order->setStrRecipientMail((string)$params['customer']->email);
			$obj_globkurier_insert_order->setStrToken((string)sha1($obj_delivery_address->company.' '.$obj_delivery_address->lastname.' '.$obj_delivery_address->firstname.$obj_delivery_address->address1.$obj_delivery_address->address2.$obj_delivery_address->postcode.$obj_delivery_address->city.$obj_delivery_address->phone_mobile.GlobKurierTools::gkDecryptString($this->login).GlobKurierTools::gkDecryptString($this->apikey)));
			$obj_json_insert_order = Tools::jsonDecode($obj_globkurier_insert_order->sendData());

			if ($obj_json_insert_order->status == true)
				$obj_globkurier_order->flag = GlobKurierOrder::PS_ORDER_SYNC;
		}
		$obj_globkurier_order->save();
	}

	/**
	* Login action
	* 
	* @param string $login
	* @param string $password
	* @param string $apikey
	* @return void
	*/
	private function loginAction($login, $password, $apikey)
	{
		// Validate
		if (Tools::isSubmit('gk_save'))
		{
			// Validate forms over PrestaShop
			$arr_login_err = array();
			if (GlobKurierValidator::isEmpty(Tools::getValue('gk_login')))
				$this->arr_login_err[] = $this->l('Invalid login.');
			if (GlobKurierValidator::isEmpty(Tools::getValue('gk_password')))
				$this->arr_login_err[] = $this->l('Incorrect password.');
			if (GlobKurierValidator::isEmpty(Tools::getValue('gk_api_key')))
				$this->arr_login_err[] = $this->l('Invalid API key.');
			// Validate data over GlobKurier
			if (count($this->arr_login_err) == 0)
			{
				$obj_globkurier = new GlobKurierLogin(GlobKurierTools::gkEncryptString(Tools::getValue('gk_login')),
													GlobKurierTools::gkEncryptString(Tools::getValue('gk_password')),
													GlobKurierTools::gkEncryptString(Tools::getValue('gk_api_key')));
				$obj_json_login = Tools::jsonDecode($obj_globkurier->sendData());
			}
			if (isset($obj_json_login) && $obj_json_login->status == true && $obj_json_login->userParams->get_data == true)
			{
				Configuration::updateValue($this->name.'_C_NUMBER', $obj_json_login->userParams->numer_klienta);
				Configuration::updateValue($this->name.'_S_NAME', $obj_json_login->userParams->imie);
				Configuration::updateValue($this->name.'_S_SURNAME', $obj_json_login->userParams->nazwisko);
				Configuration::updateValue($this->name.'_S_COMPANY', $obj_json_login->userParams->firma);
				Configuration::updateValue($this->name.'_S_NIP', $obj_json_login->userParams->nip);
				Configuration::updateValue($this->name.'_S_ADDRESS_STREET', $obj_json_login->userParams->adres);
				Configuration::updateValue($this->name.'_S_ADDRESS_HOME', $obj_json_login->userParams->adres_dom);
				Configuration::updateValue($this->name.'_S_ADDRESS_LOCAL', $obj_json_login->userParams->adres_lokal);
				Configuration::updateValue($this->name.'_S_ADDRESS_CITY', $obj_json_login->userParams->miasto);
				Configuration::updateValue($this->name.'_S_ADDRESS_ZIPCODE', $obj_json_login->userParams->kod);
				Configuration::updateValue($this->name.'_S_PHONE', $obj_json_login->userParams->telefon);
				Configuration::updateValue($this->name.'_S_IBAN', $obj_json_login->userParams->nrkonta);
				Configuration::updateValue($this->name.'_AD_INSURANCE', GlobKurierConfig::PS_GK_AD_INSURANCE);
				Configuration::updateValue($this->name.'_AD_COD', GlobKurierConfig::PS_GK_AD_COD);
				Configuration::updateValue($this->name.'_AD_COD3', GlobKurierConfig::PS_GK_AD_COD3);
			}
		}
		if ($login && $password && $apikey)
		{
			$obj_globkurier = new GlobKurierLogin($login, $password, $apikey);
			$json_decode = Tools::jsonDecode($obj_globkurier->sendData());
			if (isset($json_decode))
			{
				if ($json_decode->status)
					foreach ($json_decode->userParams as $key => $user)
						$this->arr_login_result[$key] = $user;
				else
					$this->arr_login_err[] = $json_decode->error;
			}
			else
				$this->arr_login_err[] = $this->l('Invalid login.');
		}
	}

	/**
	* Registration action w/ api
	* 
	* @param void
	* @return void
	*/
	private function registrationAction()
	{
		// Validate form register
		if (Tools::isSubmit('gk_register'))
		{
			$arr_register_err = array();
			if (GlobKurierValidator::isEmpty(Tools::getValue('gk_email')) || GlobKurierValidator::isEmail(Tools::getValue('gk_email')) === false)
				$this->arr_register_err[] = $this->l('Invalid email address.');
			if (GlobKurierValidator::isShort(Tools::getValue('gk_rpassword')))
				$this->arr_register_err[] = $this->l('Password is too short (minimum is 6 characters).');
			if (GlobKurierValidator::isEmpty(Tools::getValue('gk_rpassword')))
				$this->arr_register_err[] = $this->l('Invalid pasword.');
			if (Tools::getValue('gk_rpassword') != Tools::getValue('gk_rpassword2'))
				$this->arr_register_err[] = $this->l('Passwords not match.');
			if (GlobKurierValidator::isEmpty(Tools::getValue('gk_type')))
				$this->arr_register_err[] = $this->l('Select an account type.');
			if (GlobKurierValidator::isEmpty(Tools::getValue('gk_name')))
				$this->arr_register_err[] = $this->l('Please enter a name.');
			if (GlobKurierValidator::isEmpty(Tools::getValue('gk_surname')))
				$this->arr_register_err[] = $this->l('Please enter a surname.');
			if (GlobKurierValidator::isEmpty(Tools::getValue('gk_street')))
				$this->arr_register_err[] = $this->l('Please enter a street.');
			if (GlobKurierValidator::isEmpty(Tools::getValue('gk_house')))
				$this->arr_register_err[] = $this->l('Please enter a house number.');
			if (GlobKurierValidator::isEmpty(Tools::getValue('gk_city')))
				$this->arr_register_err[] = $this->l('Please enter a city.');
			if (GlobKurierValidator::isEmpty(Tools::getValue('gk_zip')))
				$this->arr_register_err[] = $this->l('Please enter a postalcode.');
			if (GlobKurierValidator::isEmpty(Tools::getValue('gk_phone')))
				$this->arr_register_err[] = $this->l('Please enter a phone.');

			// Validate data over GlobKurier and insert
			if (count($this->arr_register_err) == 0)
			{
				$obj_globkurier = new GlobKurierRegister();
				// Set data
				$obj_globkurier->setStrEmail(Tools::getValue('gk_email'));
				$obj_globkurier->setStrPassword(Tools::getValue('gk_rpassword'));
				$obj_globkurier->setIntCustomerType(Tools::getValue('gk_type'));
				$obj_globkurier->setStrCompany(Tools::getValue('gk_company'));
				$obj_globkurier->setStrNip(Tools::getValue('gk_nip'));
				$obj_globkurier->setStrName(Tools::getValue('gk_name'));
				$obj_globkurier->setStrSurname(Tools::getValue('gk_surname'));
				$obj_globkurier->setStrStreet(Tools::getValue('gk_street'));
				$obj_globkurier->setStrHouse(Tools::getValue('gk_house'));
				$obj_globkurier->setStrLocal(Tools::getValue('gk_local'));
				$obj_globkurier->setStrCity(Tools::getValue('gk_city'));
				$obj_globkurier->setStrZip(Tools::getValue('gk_zip'));
				$obj_globkurier->setStrPhone(Tools::getValue('gk_phone'));
				$json_decode_register = Tools::jsonDecode($obj_globkurier->sendData());
				if (isset($json_decode_register))
				{
					if ($json_decode_register->status)
						$this->arr_register_result['status'] = true;
					else
						$this->arr_register_err[] = $json_decode_register->error;
				}
				else
					$this->arr_register_err[] = $this->l('Invalid insetrt data.');
			}
		}

		// Validate form api
		if (Tools::isSubmit('gk_get_api'))
		{
			if (GlobKurierValidator::isEmpty(Tools::getValue('gk_api_email')) || GlobKurierValidator::isEmail(Tools::getValue('gk_api_email')) === false)
				$this->arr_apikey_err[] = $this->l('Invalid address email.');

			if (count($this->arr_apikey_err) == 0)
			{
				$obj_globkurier_api_key = new GlobKurierApiKey();
				$obj_globkurier_api_key->setLogin(Tools::getValue('gk_api_email'));
				$json_decode_api = Tools::jsonDecode($obj_globkurier_api_key->sendData());
				if (isset($json_decode_api))
				{
					if ($json_decode_api->status)
						$this->arr_apikey_result['status'] = true;
					else
						$this->arr_apikey_err[] = $json_decode_api->error;
				}
				else
					$this->arr_apikey_err[] = $this->l('Invalid insetrt data.');
			}
		}
	}

	/**
	* Installation
	* 
	* @param void
	* @return boolean
	*/
	public function install()
	{
		if (!function_exists('curl_version'))
			return false;

		if (!parent::install())
			return false;

		// Install SQL
		include (dirname(__FILE__).'/sql/sql-install.php');
		foreach ($sql as $s)
		{
			if (!Db::getInstance()->Execute($s)
			|| !$this->registerHook('DisplayAdminOrder')
			|| !$this->registerHook('DisplayBackOfficeTop')
			|| !$this->registerHook('actionValidateOrder')
			|| !$this->registerHook('actionCarrierProcess')
			|| !$this->registerHook('displayOrderDetail')
			|| !$this->registerHook('actionOrderStatusUpdate')
			|| !Configuration::updateValue('GLOBKURIER_SHOP_KEY', sha1(time().rand(1, 9999)))
			|| !Configuration::updateValue('GLOBKURIER_SECURITY_TOKEN', Tools::getValue('token')))
				return false;
		}
		$result = Db::getInstance()->getRow('
			SELECT `id_tab`
			FROM `'._DB_PREFIX_.'tab`
			WHERE `class_name` = "AdminGlobKurier"');
		if (!$result)
		{
			// Tab install
			$tab = new Tab();
			$tab->class_name = 'AdminGlobKurier';
			$tab->id_parent = (int)Tab::getIdFromClassName('AdminOrders');
			$tab->module = 'globkurier';
			$tab->name[(int)Configuration::get('PS_LANG_DEFAULT')] = $this->l('GlobKurier');
			$tab->add();
		}
		return true;
	}

	/**
	* Uninstallation
	* 
	* @param void
	* @return boolean
	*/
	public function uninstall()
	{
		if (!parent::uninstall())
			return false;

		// Uninstall SQL
		include (dirname(__FILE__).'/sql/sql-uninstall.php');
		foreach ($sql as $s)
		{
			if (!Db::getInstance()->Execute($s))
				return false;
		}
		// Delete configs
		if (!Configuration::deleteByName($this->name.'_LOGIN')
		|| !Configuration::deleteByName($this->name.'_PASSWORD')
		|| !Configuration::deleteByName($this->name.'_API_KEY')
		|| !Configuration::deleteByName('GLOBKURIER_SHOP_KEY')
		|| !Configuration::deleteByName('GLOBKURIER_SECURITY_TOKEN')
		|| !Configuration::deleteByName($this->name.'_C_NUMBER')
		|| !Configuration::deleteByName($this->name.'_S_NAME')
		|| !Configuration::deleteByName($this->name.'_S_SURNAME')
		|| !Configuration::deleteByName($this->name.'_S_COMPANY')
		|| !Configuration::deleteByName($this->name.'_S_NIP')
		|| !Configuration::deleteByName($this->name.'_S_ADDRESS_STREET')
		|| !Configuration::deleteByName($this->name.'_S_ADDRESS_HOME')
		|| !Configuration::deleteByName($this->name.'_S_ADDRESS_LOCAL')
		|| !Configuration::deleteByName($this->name.'_S_ADDRESS_CITY')
		|| !Configuration::deleteByName($this->name.'_S_ADDRESS_ZIPCODE')
		|| !Configuration::deleteByName($this->name.'_S_PHONE')
		|| !Configuration::deleteByName($this->name.'_S_IBAN')
		|| !Configuration::deleteByName($this->name.'_AD_INSURANCE')
		|| !Configuration::deleteByName($this->name.'_AD_COD')
		|| !Configuration::deleteByName($this->name.'_AD_COD3'))
			return false;

		// Uninstall tab
		$tab = new Tab(Tab::getIdFromClassName('AdminGlobKurier'));
		if (!$tab->delete())
			return false;

		return true;
	}
}
