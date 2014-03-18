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
*  @version  Release: $Revision: 15821 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

if (!class_exists('Klarna'))
	include_once(_PS_MODULE_DIR_.'klarnaprestashop/lib/Klarna.php');

if (!class_exists('xmlrpc_client'))
{
	include_once(_PS_MODULE_DIR_.'klarnaprestashop/lib/transport/xmlrpc-3.0.0.beta/lib/xmlrpc.inc');
	include_once(_PS_MODULE_DIR_.'klarnaprestashop/lib/transport/xmlrpc-3.0.0.beta/lib/xmlrpc_wrappers.inc');
}

include_once(_PS_MODULE_DIR_.'klarnaprestashop/class/klarnaintegration.php');

class KlarnaPrestaShop extends PaymentModule
{
	private $_postErrors = array();
	private $_postValidations = array();
	private $countries = array(
		'SE' => array('name' =>'SWEDEN', 'code' => KlarnaCountry::SE, 'langue' => KlarnaLanguage::SV, 'currency' => KlarnaCurrency::SEK),
		'NO' => array('name' =>'NORWAY', 'code' => KlarnaCountry::NO, 'langue' => KlarnaLanguage::NB, 'currency' => KlarnaCurrency::NOK),
		'DK' => array('name' =>'DENMARK', 'code' => KlarnaCountry::DK, 'langue' => KlarnaLanguage::DA, 'currency' => KlarnaCurrency::DKK),
		'FI' => array('name' =>'FINLAND', 'code' => KlarnaCountry::FI, 'langue' => KlarnaLanguage::FI, 'currency' => KlarnaCurrency::EUR),
		'DE' => array('name' =>'GERMANY', 'code' => KlarnaCountry::DE, 'langue' => KlarnaLanguage::DE, 'currency' => KlarnaCurrency::EUR),
		'NL' => array('name' =>'NETHERLANDS', 'code' => KlarnaCountry::NL, 'langue' => KlarnaLanguage::NL, 'currency' => KlarnaCurrency::EUR),
	);

	const RESERVED = 1;
	const SHIPPED = 2;
	const CANCEL = 3;

	/******************************************************************/
	/** Construct Method **********************************************/
	/******************************************************************/
	public function __construct()
	{
		$this->name = 'klarnaprestashop';
		$this->moduleName = 'klarnaprestashop';
		$this->tab = 'payments_gateways';
		$this->version = '1.7.5';
		$this->author = 'PrestaShop';

		$this->limited_countries = array('se', 'no', 'fi', 'dk', 'de', 'nl');

		parent::__construct();

		$this->displayName = $this->l('Klarna Payment');
		$this->description = $this->l('Klarna provides a revolutionary payment solution for online merchants.');

		/* Backward compatibility */
		require(_PS_MODULE_DIR_.$this->moduleName.'/backward_compatibility/backward.php');
		$this->context->smarty->assign('base_dir', __PS_BASE_URI__);
	}


	/******************************************************************/
	/** Install / Uninstall Methods ***********************************/
	/******************************************************************/
	public function install()
	{
		include(dirname(__FILE__).'/sql-install.php');
		foreach ($sql as $s)
		  if (!Db::getInstance()->Execute($s))
		    return false;

		if (!parent::install() || !$this->registerHook('payment') || !$this->registerHook('adminOrder') || !$this->registerHook('paymentReturn') || !$this->registerHook('orderConfirmation'))
			return false;

		$this->registerHook('displayPayment');
		$this->registerHook('rightColumn');
		$this->registerHook('extraRight');
		$this->registerHook('header');

		if (!Configuration::get('KLARNA_PAYMENT_ACCEPTED'))
			Configuration::updateValue('KLARNA_PAYMENT_ACCEPTED', $this->addState('Klarna: Payment accepted', '#DDEEFF'));
		if (!Configuration::get('KLARNA_PAYMENT_PENDING'))
			Configuration::updateValue('KLARNA_PAYMENT_PENDING', $this->addState('Klarna : payment in pending verification', '#DDEEFF'));

		/*auto install currencies*/
		$currencies = array(
			'Euro' => array('iso_code' => 'EUR', 'iso_code_num' => 978, 'symbole' => '€', 'format' => 2),
			'Danish Krone' => array('iso_code' => 'DKK', 'iso_code_num' => 208, 'symbole' => 'DAN kr.', 'format' => 2),
			'krone' => array('iso_code' => 'NOK', 'iso_code_num' => 578, 'symbole' => 'NOK kr', 'format' => 2),
			'Krona' => array('iso_code' => 'SEK', 'iso_code_num' => 752, 'symbole' => 'SEK kr', 'format' => 2)
		);

		$languages = array(
			'Swedish' => array('iso_code' => 'se', 'language_code' => 'sv', 'date_format_lite' => 'Y-m-d', 'date_format_full' => 'Y-m-d H:i:s' , 'flag' => 'sweden.png'),
			'Deutsch' => array('iso_code' => 'de', 'language_code' => 'de', 'date_format_lite' => 'Y-m-d', 'date_format_full' => 'Y-m-d H:i:s' , 'flag' => 'germany.png'),
			'Dutch' => array('iso_code' => 'nl', 'language_code' => 'nl', 'date_format_lite' => 'Y-m-d', 'date_format_full' => 'Y-m-d H:i:s' , 'flag' => 'netherlands.png'),
			'Finnish' => array('iso_code' => 'fi', 'language_code' => 'fi', 'date_format_lite' => 'Y-m-d', 'date_format_full' => 'Y-m-d H:i:s' , 'flag' => 'finland.jpg'),
			'Norwegian' => array('iso_code' => 'no', 'language_code' => 'no', 'date_format_lite' => 'Y-m-d', 'date_format_full' => 'Y-m-d H:i:s' , 'flag' => 'norway.png'),
			'Danish' => array('iso_code' => 'da', 'language_code' => 'da', 'date_format_lite' => 'Y-m-d', 'date_format_full' => 'Y-m-d H:i:s' , 'flag' => 'denmark.png'),
		);

		foreach ($currencies as $key => $val)
		{
			if (_PS_VERSION_ >= 1.5)
				$exists = Currency::exists($val['iso_code_num'], $val['iso_code_num']);
			else
				$exists = Currency::exists($val['iso_code_num']);
			if (!$exists)
			{
				$currency = new Currency();
				$currency->name = $key;
				$currency->iso_code = $val['iso_code'];
				$currency->iso_code_num = $val['iso_code_num'];
				$currency->sign = $val['symbole'];
				$currency->conversion_rate = 1;
				$currency->format = $val['format'];
				$currency->decimals = 1;
				$currency->active = true;
				$currency->add();
			}
		}
		
		Currency::refreshCurrencies();
		
		$version = str_replace('.', '', _PS_VERSION_);
		$version = substr($version, 0, 2);
		
		foreach ($languages as $key => $val)
		{
			$pack = @Tools::file_get_contents('http://api.prestashop.com/localization/'.$version.'/'.$val['language_code'].'.xml');

			if ($pack || $pack = @Tools::file_get_contents(dirname(__FILE__).'/../../localization/'.$val['language_code'].'.xml'))
			{
					$localizationPack = new LocalizationPack();
					$localizationPack->loadLocalisationPack($pack, array('taxes', 'languages'));
			}

			if (!Language::getIdByIso($val['language_code']))
			{
				if (_PS_VERSION_ >= 1.5)
				{
					Language::checkAndAddLanguage($val['language_code']);
				}
				else
				{
					$lang = new Language();
					$lang->name = $key;
					$lang->iso_code = $val['iso_code'];
					$lang->language_code = $val['language_code'];
					$lang->date_format_lite = $val['date_format_lite'];
					$lang->date_format_full = $val['date_format_full'];
					$lang->add();
					$insert_id = (int)$lang->id;
					$pack = Tools::file_get_contents('http://www.prestashop.com/download/localization/'.$val['iso_code'].'.xml');
					$lang_pack = Tools::jsonDecode(Tools::file_get_contents('http://www.prestashop.com/download/lang_packs/get_language_pack.php?version='._PS_VERSION_.'&iso_lang='.$val['iso_code']));
					if ($lang_pack)
					{
						$flag = Tools::file_get_contents('http://www.prestashop.com/download/lang_packs/flags/jpeg/'.$val['iso_code'].'.jpg');
						if ($flag != null && !preg_match('/<body>/', $flag))
						{
							$file = fopen(dirname(__FILE__).'/../../img/l/'.$insert_id.'.jpg', 'w');
							if ($file)
							{
								fwrite($file, $flag);
								fclose($file);
							}
						}
					}
				}
			}
		}

		foreach ($this->countries as $key => $val)
		{
			$country = new Country(Country::getByIso($key));
			$country->active = true;
			$country->update();
		}
		return true;
	}

	/******************************************************************/
	/** add payment state ***********************************/
	/******************************************************************/
	private function addState($en, $color)
	{
		$orderState = new OrderState();
		$orderState->name = array();
		foreach (Language::getLanguages() as $language)
			$orderState->name[$language['id_lang']] = $en;
		$orderState->send_email = false;
		$orderState->color = $color;
		$orderState->hidden = false;
		$orderState->delivery = false;
		$orderState->logable = true;
		if ($orderState->add())
			copy(dirname(__FILE__).'/logo.gif', dirname(__FILE__).'/../../img/os/'.(int)$orderState->id.'.gif');
		return $orderState->id;
	}

	/**
	 * @brief Uninstall function
	 *
	 * @return Success or failure
	 */
	public function uninstall()
	{
		// Uninstall parent and unregister Configuration
		if (!parent::uninstall() || !$this->unregisterHook('payment') || !$this->unregisterHook('adminOrder'))
			return false;
		return true;
	}

	/**
	 * @brief Main Form Method
	 *
	 * @return Rendered form
	 */
	public function getContent()
	{
		if (version_compare(_PS_VERSION_,'1.5','>'))
			$this->context->controller->addJQueryPlugin('fancybox');
		else
			$html .= '<script type="text/javascript" src="'.__PS_BASE_URI__.'js/jquery/jquery.fancybox-1.3.4.js"></script>                                                                                         
		  	<link type="text/css" rel="stylesheet" href="'.__PS_BASE_URI__.'css/jquery.fancybox-1.3.4.css" />';
		  	
		$html = '';

		if (!empty($_POST) && isset($_POST['submitKlarna']))
		{
			$this->_postValidation();
			if (sizeof($this->_postValidations))
				$html .= $this->_displayValidation();
			if (sizeof($this->_postErrors))
				$html .= $this->_displayErrors();
		}
		$html .= $this->_displayAdminTpl();
		return $html;
	}

	/**
	 * @brief Method that will displayed all the tabs in the configurations forms
	 *
	 * @return Rendered form
	 */
	private function _displayAdminTpl()
	{
		$smarty = $this->context->smarty;

		$tab = array(
			'credential' => array(
				'title' => $this->l('Settings'),
				'content' => $this->_displayCredentialTpl(),
				'icon' => '../modules/'.$this->moduleName.'/img/icon-settings.gif',
				'tab' => 1,
				'selected' => true, //other has to be false
			),
		);

		$smarty->assign('tab', $tab);
		$smarty->assign('moduleName', $this->moduleName);
		$smarty->assign($this->moduleName.'Logo', '../modules/'.$this->moduleName.'/img/logo.png');
		$smarty->assign('js', array('../modules/'.$this->moduleName.'/js/klarna.js'));
		$smarty->assign($this->moduleName.'Css', '../modules/'.$this->moduleName.'/css/klarna.css');

		return $this->display(__FILE__, 'tpl/admin.tpl');
	}

	/**
	 * @brief Credentials Form Method
	 *
	 * @return Rendered form
	 */
	private function _displayCredentialTpl()
	{
		$smarty = $this->context->smarty;
		$activateCountry = array();
		$currency = Currency::getCurrency((int)Configuration::get('PS_CURRENCY_DEFAULT'));
		foreach ($this->countries as $country)
		{
			$countryNames[$country['name']] = array('flag' => '../modules/'.$this->moduleName.'/img/flag_'.$country['name'].'.png',	'country_name' => $country['name']);
			$countryCodes[$country['code']] = $country['name'];

			$input_country[$country['name']]['eid_'.$country['name']] = array(
				'name' => 'klarnaStoreId'.$country['name'],
				'required' => true,
				'value' => Tools::safeOutput(Configuration::get('KLARNA_STORE_ID_'.$country['name'])),
				'type' => 'text',
				'label' => $this->l('E-store ID'),
				'desc' => $this->l(''),
			);
			$input_country[$country['name']]['secret_'.$country['name']] = array(
				'name' => 'klarnaSecret'.$country['name'],
				'required' => true,
				'value' => Tools::safeOutput(Configuration::get('KLARNA_SECRET_'.$country['name'])),
				'type' => 'text',
				'label' => $this->l('Secret'),
				'desc' => $this->l(''),
			);
			$input_country[$country['name']]['invoice_fee_'.$country['name']] = array(
				'name' => 'klarnaInvoiceFee'.$country['name'],
				'required' => false,
				'value' => Tools::safeOutput(Configuration::get('KLARNA_INVOICE_FEE_'.$country['name'])),
				'type' => 'text',
				'label' => $this->l('Invoice Fee ').'('.$currency['sign'].')',
				'desc' => $this->l(''),
			);
			$input_country[$country['name']]['minimum_value_'.$country['name']] = array(
				'name' => 'klarnaMinimumValue'.$country['name'],
				'required' => false,
				'value' => (float)Configuration::get('KLARNA_MINIMUM_VALUE_'.$country['name']),
				'type' => 'text',
				'label' => $this->l('Minimum Value ').'('.$currency['sign'].')',
				'desc' => $this->l(''),
			);
			$input_country[$country['name']]['maximum_value_'.$country['name']] = array(
				'name' => 'klarnaMaximumValue'.$country['name'],
				'required' => false,
				'value' => Configuration::get('KLARNA_MAXIMUM_VALUE_'.$country['name']) != 0 ? (float)Configuration::get('KLARNA_MAXIMUM_VALUE_'.$country['name']) : 99999,
				'type' => 'text',
				'label' => $this->l('Maximum Value ').'('.$currency['sign'].')',
				'desc' => $this->l(''),
			);

			if (Configuration::get('KLARNA_STORE_ID_'.$country['name']))
				$activateCountry[] = $country['name'];
		}

		$smarty->assign($this->moduleName.'FormCredential',	'./index.php?tab=AdminModules&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.$this->tab.'&module_name='.$this->name);
		$smarty->assign($this->moduleName.'CredentialTitle', $this->l('Location'));
		$smarty->assign($this->moduleName.'CredentialText', $this->l('In order to use the Klarna module, please select your host country and supply the appropriate credentials.'));
		$smarty->assign($this->moduleName.'CredentialFootText', $this->l('Please note: The selected currency and country must match the customers\' registration').'<br/>'.
			$this->l('E.g. Swedish customer, SEK, Sweden and Swedish.').'<br/>'.
			$this->l('In order for your customers to use Klarna, your customers must be located in the same country in which your e-store is registered.'));

		$smarty->assign(array(
				'klarna_pclass' => Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'klarna_payment_pclasses`'),
				'klarna_mod' => Configuration::get('KLARNA_MOD'),
				'klarna_active_invoice' => Configuration::get('KLARNA_ACTIVE_INVOICE'),
				'klarna_active_partpayment' => Configuration::get('KLARNA_ACTIVE_PARTPAYMENT'),
				'klarna_email' => Configuration::get('KLARNA_EMAIL'),
				'credentialInputVar' => $input_country,
				'countryNames' => $countryNames,
				'countryCodes' => $countryCodes,
				'img' => '../modules/'.$this->moduleName.'/img/',
				'activateCountry' => $activateCountry));
		return $this->display(__FILE__, 'tpl/credential.tpl');
	}


	/**
	 * @brief Validate Method
	 *
	 * @return update the module depending on klarna webservices
	 */

	private function _postValidation()
	{
		$klarna = new Klarna();
		if ($_POST['klarna_mod'] == 'live')
			Configuration::updateValue('KLARNA_MOD', Klarna::LIVE);
		else
			Configuration::updateValue('KLARNA_MOD', Klarna::BETA);

		if (isset($_POST['klarna_active_invoice']) && $_POST['klarna_active_invoice'])
			Configuration::updateValue('KLARNA_ACTIVE_INVOICE', true);
		else
			Configuration::deleteByName('KLARNA_ACTIVE_INVOICE');
		if (isset($_POST['klarna_active_partpayment']) && $_POST['klarna_active_partpayment'])
			Configuration::updateValue('KLARNA_ACTIVE_PARTPAYMENT', true);
		else
			Configuration::deleteByName('KLARNA_ACTIVE_PARTPAYMENT');
		if (isset($_POST['klarna_email']) && $_POST['klarna_email'])
			Configuration::updateValue('KLARNA_EMAIL', true);
		else
			Configuration::deleteByName('KLARNA_EMAIL');

		foreach ($this->countries as $country)
		{
			Db::getInstance()->delete(_DB_PREFIX_.'klarna_payment_pclasses', 'country = "'.(int)$country['code'].'"');
			Configuration::updateValue('KLARNA_STORE_ID_'.$country['name'], null);
			Configuration::updateValue('KLARNA_SECRET_'.$country['name'], null);
		}
		foreach ($this->countries as $key => $country)
		{
			if (isset($_POST['activate'.$country['name']]))
			{
				$storeId = (int)Tools::getValue('klarnaStoreId'.$country['name']);
				$secret = pSQL(Tools::getValue('klarnaSecret'.$country['name']));

				if (($storeId > 0 && $secret == '') || ($storeId <= 0 && $secret != ''))
					$this->_postErrors[] = $this->l('your credentials are incorrect and can\'t be used in ').$country['name'];
				elseif ($storeId >= 0 && $secret != '')
				{
					$error = false;
					try
					{
						$klarna->config(
							$storeId,               			// Merchant ID
							Tools::safeOutput($secret),			// Shared Secret
							$country['code'],					// Country
							$country['langue'],					// Language
							$country['currency'],				// Currency
							Configuration::get('KLARNA_MOD'),	// Server
							'mysql',//'json'					// PClass Storage
							$this->_getDb()//,  PClass Storage URI path
							//false,            SSL
							//true              Remote logging of response times of xmlrpc calls
						);
						$PClasses = $klarna->fetchPClasses($country['code']);
					}
					catch (Exception $e)
					{
						$error = true;
						$this->_postErrors[] = (int)$e->getCode().': '.Tools::safeOutput($e->getMessage());
					}

					if (!$error)
					{
						Configuration::updateValue('KLARNA_STORE_ID_'.$country['name'], $storeId);
						Configuration::updateValue('KLARNA_SECRET_'.$country['name'], $secret);
						Configuration::updateValue('KLARNA_INVOICE_FEE_'.$country['name'], (float)Tools::getValue('klarnaInvoiceFee'.$country['name']));
						Configuration::updateValue('KLARNA_MINIMUM_VALUE_'.$country['name'], (float)Tools::getValue('klarnaMinimumValue'.$country['name']));
						Configuration::updateValue('KLARNA_MAXIMUM_VALUE_'.$country['name'], ($_POST['klarnaMaximumValue'.$country['name']] != 0 ? (float)Tools::getValue('klarnaMaximumValue'.$country['name']) : 99999));
						$id_product = Db::getInstance()->getValue('SELECT `id_product` FROM `'._DB_PREFIX_.'product_lang` WHERE `name` = \'invoiceFee'.$country['name'].'\'');

						$taxeRules = TaxRulesGroup::getAssociatedTaxRatesByIdCountry(Country::getByIso($key));
						$maxiPrice = 0;
						$idTaxe = 0;
						foreach ($taxeRules as $key => $val)
							if ((int)$val > $maxiPrice)
							{
								$maxiPrice = (int)$val;
								$idTaxe = $key;
							}

						if ($id_product != null)
						{
							$productInvoicefee = new Product((int)$id_product);
							$productInvoicefee->price = (float)Tools::getValue('klarnaInvoiceFee'.$country['name']);
							if (_PS_VERSION_ >= 1.5)
								StockAvailable::setProductOutOfStock((int)$productInvoicefee->id, true, null, 0);
							if ($idTaxe != 0)
								$productInvoicefee->id_tax_rules_group = (int)$idTaxe;
							$productInvoicefee->update();
						}
						else
						{
							$productInvoicefee = new Product();
							$productInvoicefee->out_of_stock = 1;
							$productInvoicefee->available_for_order = true;
							$productInvoicefee->id_category_default = 2;
							if ($idTaxe != 0)
								$productInvoicefee->id_tax_rules_group = (int)$idTaxe;
							$languages = Language::getLanguages(false);
							foreach ($languages as $language)
							{
								$productInvoicefee->name[$language['id_lang']] = 'invoiceFee'.$country['name'];
								$productInvoicefee->link_rewrite[$language['id_lang']] = 'invoiceFee'.$country['name'];
							}
							$productInvoicefee->price = (float)Tools::getValue('klarnaInvoiceFee'.$country['name']);
							if (_PS_VERSION_ >= 1.5)
								$productInvoicefee->active = false;
							$productInvoicefee->add();
							if (_PS_VERSION_ >= 1.5)
								StockAvailable::setProductOutOfStock((int)$productInvoicefee->id, true, null, 0);
						}
						Configuration::updateValue('KLARNA_INV_FEE_ID_'.$country['name'], $productInvoicefee->id);
						$this->_postValidations[] = $this->l('Your account has been updated to be used in ').$country['name'];
					}
					$error = false;
				}
			}
		}
	}

	/**
	 * @brief Validation display Method
	 *
	 * @return
	 */
	private function _displayValidation()
	{
		$this->context->smarty->assign('klarnaValidation', $this->_postValidations);
		return $this->display(__FILE__, 'tpl/validation.tpl');
	}

	/**
	 * @brief Error display Method
	 *
	 * @return
	 */
	private function _displayErrors()
	{
		$this->context->smarty->assign('klarnaError', $this->_postErrors);
		return $this->display(__FILE__, 'tpl/error.tpl');
	}

	/**
	 * @brief verify if the currency and the country belong together
	 *
	 * @return true or false
	 */
	public function getCountries()
	{
		return $this->countries;
	}

	private function _getDb()
	{
		return array(
			'user' => _DB_USER_,
			'passwd' => _DB_PASSWD_,
			'dsn' => _DB_SERVER_,
			'db' => _DB_NAME_,
			'table' => _DB_PREFIX_.'klarna_payment_pclasses'
		);
	}

	public function verifCountryAndCurrency($country, $currency)
	{
		if (!isset($this->countries[$country->iso_code]))
			return false;
		$currency_code = KlarnaCurrency::fromCode($currency->iso_code);
		if ($currency_code === null)
			return false;
		if ($this->countries[$country->iso_code]['currency'] != $currency_code)
			return false;
		if (Configuration::get('KLARNA_STORE_ID_'.$this->countries[$country->iso_code]['name']) > 0
			&& Configuration::get('KLARNA_SECRET_'.$this->countries[$country->iso_code]['name']) != '')
			return true;
		return false;
	}


	/**
	 * @brief is the order was in pending verification by klarna
	 *
	 *
	 */
	public function orderHasBeenPending($order)
	{
		return sizeof($order->getHistory(
				(int)($order->id_lang),
				Configuration::get('KLARNA_PAYMENT_PENDING'))
		);
	}

	/**
	 * @brief is the order was accepted by klarna
	 *
	 *
	 */
	public function orderHasBeenAccepted($order)
	{
		return sizeof($order->getHistory(
				(int)($order->id_lang),
				Configuration::get('KLARNA_PAYMENT_ACCEPTED'))
		);
	}

	public function orderHasBeenDeclined($order)
	{
		return sizeof($order->getHistory(
				(int)($order->id_lang),
				(int)Configuration::get('PS_OS_CANCELED'))
		);
	}

	private function _verifCurrencyLanguage($currency, $language)
	{
		foreach ($this->countries as $key => $val)
			if ($val['langue'] == $language && $val['currency'] == $currency)
				return $key;
		return false;
	}

	public function hookExtraRight(Array $params)
	{
		if (!$this->active)
			return false;

		if (!Configuration::get('KLARNA_ACTIVE_PARTPAYMENT'))
			return false;

		if ($this->context->language->iso_code == 'no')
			$iso_code = $this->_verifCurrencyLanguage(KlarnaCurrency::fromCode($this->context->currency->iso_code), KlarnaLanguage::fromCode('nb'));
		else
			$iso_code = $this->_verifCurrencyLanguage(KlarnaCurrency::fromCode($this->context->currency->iso_code), KlarnaLanguage::fromCode($this->context->language->iso_code));

		$product = new Product((int)Tools::getValue('id_product'));
		if (Validate::isLoadedObject($params['cart']))
		{
			$cart = $params['cart'];
			$address_delivery = new Address($params['cart']->id_address_delivery);
			$country = new Country($address_delivery->id_country);
			$currency = new Currency($params['cart']->id_currency);
			if (!$this->verifCountryAndCurrency($country, $currency))
				return false;
		}
		else if ($iso_code)
		{
			$country = new Country(pSQL(Country::getByIso($iso_code)));
			$currency = new Currency($this->context->currency->id);
		}
		else
			return false;

		if ($currency->iso_code == 'SEK')
			$countryIsoCode = 'SE';
		else if ($currency->iso_code == 'DKK')
			$countryIsoCode = 'DK';
		else if ($currency->iso_code == 'NOK')
			$countryIsoCode = 'NO';
		else if ($currency->iso_code == 'EUR' && $this->context->language->iso_code == 'fi')
			$countryIsoCode = 'FI';
		else if ($currency->iso_code == 'EUR' && $this->context->language->iso_code == 'de')
		{
			if ((isset($cart) && $country->iso_code == 'DE') || ($this->context->country->iso_code == 'DE' && !isset($cart)))
				$countryIsoCode = 'DE';
			else
				return false;
		}
		else if ($currency->iso_code == 'EUR' && $this->context->language->iso_code == 'nl')
		{
			if ((isset($cart) && $country->iso_code == 'NL') || ($this->context->country->iso_code == 'NL' && !isset($cart)))
				$countryIsoCode = 'NL';
			else
				return false;
		}
		else
			return false;

		if (!Configuration::get('KLARNA_STORE_ID_'.$this->countries[$countryIsoCode]['name']))
			return false;

		$amount = $product->getPrice();

		$smarty = $this->context->smarty;
		$klarna = new Klarna();

		try
		{
			$klarna->config(
				Configuration::get('KLARNA_STORE_ID_'.$this->countries[$countryIsoCode]['name']),
				Configuration::get('KLARNA_SECRET_'.$this->countries[$countryIsoCode]['name']),
				$this->countries[$countryIsoCode]['code'],
				$this->countries[$countryIsoCode]['langue'],
				$this->countries[$countryIsoCode]['currency'],
				Configuration::get('KLARNA_MOD'),
				'mysql', $this->_getDb());
			$pclass = $klarna->getCheapestPClass((float)$amount, KlarnaFlags::PRODUCT_PAGE);
			if ($pclass)
				$value = KlarnaCalc::calc_monthly_cost((float)$amount, $pclass, KlarnaFlags::PRODUCT_PAGE);
			if (!isset($value) || $value == 0)
				return false;
			$pclasses = array_merge($klarna->getPClasses(KlarnaPClass::ACCOUNT), $klarna->getPClasses(KlarnaPClass::CAMPAIGN));
			$accountPrice = array();

			foreach ($pclasses as $val)
				$accountPrice[$val->getId()] = array('price' => KlarnaCalc::calc_monthly_cost((float)$amount, $val, KlarnaFlags::PRODUCT_PAGE),
																			 'month' => (int)$val->getMonths(), 'description' => htmlspecialchars_decode(Tools::safeOutput($val->getDescription())));
		}
		catch (Exception $e)
		{
			return false;
		}

		$this->context->smarty->assign(array(
				'minValue' => (float)$value,
				'accountPrices' => $accountPrice,
				'productcss' => './modules/klarnaprestashop/css/klarnaproduct.css',
				'country' => $countryIsoCode,
				'linkTermsCond' => 'https://online.klarna.com/account_'.strtolower($countryIsoCode).'.yaws?eid='.(int)Configuration::get('KLARNA_STORE_ID_'.$this->countries[$countryIsoCode]['name'])
			));
		return $this->display(__FILE__, 'tpl/product.tpl');
	}

	public function hookHeader($params)
	{
		if (!$this->active)
			return false;

		$namePending = Db::getInstance()->getValue('SELECT `name` FROM `'._DB_PREFIX_.'order_state_lang` WHERE `id_order_state` = \''.(int)Configuration::get('KLARNA_PAYMENT_PENDING').'\' AND id_lang = \''.(int)$this->context->language->id.'\'');

		$this->context->smarty->assign(array(
				'validateText' =>  $this->l('Klarna: Payment accepted'),
				'wrongText' => Tools::safeOutput($namePending),
				'moduleName' => $this->displayName));

		return $this->display(__FILE__, 'tpl/orderDetail.tpl');
	}

	public function hookRightColumn($params)
	{
		if (!$this->active)
			return false;
		if (isset($params['cart']) && isset($params['cart']->id_address_invoice))
		{
			$address_invoice = new Address((int)$params['cart']->id_address_invoice);
			$country = new Country((int)$address_invoice->id_country);
			if (file_exists('./modules/'.$this->moduleName.'/img/klarna_invoice_'.Tools::strtolower($country->iso_code).'.png') && Configuration::get('KLARNA_ACTIVE_INVOICE'))
				$logo = 'klarna_invoice_'.Tools::strtolower($country->iso_code).'.png';
			else
				$logo = 'logo.png';
			if (file_exists('./modules/'.$this->moduleName.'/img/klarna_account_'.Tools::strtolower($country->iso_code).'.png') && Configuration::get('KLARNA_ACTIVE_PARTPAYMENT'))
				$this->context->smarty->assign(array('logo_klarna_account' => Tools::safeOutput('klarna_account_'.Tools::strtolower($country->iso_code).'.png')));
		}
		else
			$logo = 'logo.png';

		$this->context->smarty->assign(array('path' => __PS_BASE_URI__.'modules/'.$this->moduleName, 'logo_klarna' => $logo));
		return $this->display(__FILE__, 'tpl/logo.tpl');
	}

	/**
	 * @brief generate the invoice when the order is ready to be shipped.
	 * the merchant can also cancel the order
	 *
	 */
	public function hookadminOrder($params)
	{
		if (!$this->active)
			return false;
		$order = new Order($params['id_order']);

		$klarna = new Klarna();
		$klarnaInt = new KlarnaIntegration($klarna);
		if ($order->module != $this->moduleName)
			return false;
		$address_invoice = new Address((int)$order->id_address_invoice);
		$country = new Country((int)$address_invoice->id_country);
		$currency = new Currency((int)$order->id_currency);

		$smarty = $this->context->smarty;

		$klarna->config(
			Configuration::get('KLARNA_STORE_ID_'.$this->countries[$country->iso_code]['name']),
			Configuration::get('KLARNA_SECRET_'.$this->countries[$country->iso_code]['name']),
			$this->countries[$country->iso_code]['code'],
			$this->countries[$country->iso_code]['langue'],
			$this->countries[$country->iso_code]['currency'],
			Configuration::get('KLARNA_MOD'),
			'mysql', $this->_getDb());

		$customer = new Customer($order->id_customer);
		$row = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'klarna_rno` WHERE `id_cart` = '.(int)$order->id_cart);

		$this->initReservation(
			$klarna,
			new Cart((int)$order->id_cart),
			$customer,
			htmlentities($row['house_number'], ENT_QUOTES, 'ISO-8859-1'),
			htmlentities($row['house_ext'], ENT_QUOTES, 'ISO-8859-1')
		);

		if ($country->iso_code == 'DE' || $country->iso_code  == 'NL')
			$gender = ($customer->id_gender == 1 ? 1 : 0);
		else
			$gender = null;

		if ($this->orderHasBeenPending($order) && !$this->orderHasBeenAccepted($order) && !$this->orderHasBeenDeclined($order))
		{
			$redirect = true;
			try
			{
				$result = $klarna->checkOrderStatus($row['rno'],0);
				$history = new OrderHistory();
				$history->id_order = (int)$order->id;
				$history->id_employee = (int)$this->context->employee->id;

				if ($result == KlarnaFlags::ACCEPTED)
				{
					$history->changeIdOrderState((int)Configuration::get('KLARNA_PAYMENT_ACCEPTED'), $order->id);
					$message = $this->l('Klarna has changed the status of this order to Klarna: Payment accepted');
				}
				elseif ($result == KlarnaFlags::PENDING)
				{
					$type = 'pending';
					$smarty->assign('shipped_state', (int)Configuration::get('PS_OS_SHIPPING'));
					$message = $this->l('Order still in pending verification, please try again later. Every time you open a pending order in Prestashop, a check for the current status will be made.');
					$noHistory = true;
				}
				elseif ($result == KlarnaFlags::DENIED)
				{
					$history->changeIdOrderState((int)Configuration::get('PS_OS_CANCELED'), $order->id);
					Db::getInstance()->autoExecute(_DB_PREFIX_.'klarna_rno', array('state' => self::CANCEL), 'UPDATE', '`id_cart` = '.(int)$order->id_cart);
					$type = 'denied';
					$message = $this->l('Klarna has changed the status of this order to Canceled.');
					$result = $klarnaInt->cancel($row['rno']);
				}
			}
			catch (Exception $e)
			{
				$smarty->assign('error', (int)$e->getCode().': '.Tools::safeOutput($e->getMessage()));
				$redirect = false;
			}
			if ($redirect)
			{
				if (!isset($noHistory))
					$history->add();
				$current_index = __PS_BASE_URI__.basename(_PS_ADMIN_DIR_).'/index.php'.(($controller = Tools::getValue('controller')) ? '?controller='.$controller : '');
				if ($back = Tools::getValue('back'))
					$current_index .= '&back='.urlencode($back);
				if (!Tools::getValue('message'))
					Tools::redirectAdmin($current_index.'&id_order='.$order->id.'&vieworder&conf=4&token='.Tools::getValue('token').'&message='.$message.(isset($type) ? '&type='.$type : '&wasPending'));
			}
		}

		if ($order->getCurrentState() == Configuration::get('PS_OS_CANCELED') && !$order->hasBeenShipped() && $row['state'] != self::CANCEL)
		{
			try
			{
				$result = $klarnaInt->cancel($row['rno']);
				$smarty->assign('message', $this->l('The order has been canceled in Prestashop and the reservation has been canceled at Klarna.'));
				Db::getInstance()->autoExecute(_DB_PREFIX_.'klarna_rno', array('state' => self::CANCEL), 'UPDATE', '`id_cart` = '.(int)$order->id_cart);
			}
			catch (Exception $e)
			{
				$smarty->assign('error', (int)$e->getCode().': '.Tools::safeOutput($e->getMessage()));
			}
		}

		if ($order->hasBeenShipped() && $row['invoice'] == '')
		{
			$pclass = ($row['type'] == 'invoice' ? KlarnaPClass::INVOICE : (int)$row['pclass']);
			try
			{
				$klarna->setEstoreInfo((int)$order->id);
				$result = $klarnaInt->activate(
					$row['pno'],
					$row['rno'],
					$gender,                // Gender.
					'',                     // OCR number to use if you have reserved one.
					KlarnaFlags::NO_FLAG,   // Flags to affect behavior.
					$pclass								  //KlarnaPClass::INVOICE
				);
			}
			catch (Exception $e)
			{
				$smarty->assign('error', (int)$e->getCode().': '.Tools::safeOutput($e->getMessage()));
			}

			if (isset($result) && $result[0] == 'ok')
			{
				Db::getInstance()->autoExecute(_DB_PREFIX_.'klarna_rno', array('invoice' => pSQL($result[1]), 'state' => self::SHIPPED), 'UPDATE', '`id_cart` = '.(int)$order->id_cart);
				$smarty->assign('invoiceLink', (substr($result[1], 0, 4) == 'http' ? Tools::safeOutput($result[1]) : 'https://online.klarna.com/invoices/'.Tools::safeOutput($result[1]).'.pdf'));
				if (Configuration::get('KLARNA_EMAIL'))
					$klarna->emailInvoice(Tools::safeOutput($result[1]));
			}
		}
		elseif ($order->hasBeenShipped())
			$smarty->assign('invoiceLink', 'https://online.klarna.com/invoices/'.Tools::safeOutput($row['invoice']).'.pdf');

		$smarty->assign('version', (_PS_VERSION_ >= 1.5 ? 1 : 0));

		if ($row['state'] == self::CANCEL)
		{
			$smarty->assign('denied', true);
			if (!Tools::getValue('message'))
				$smarty->assign('message', $this->l('The order has been canceled in Prestashop and the reservation has been canceled at Klarna.'));
		}

		if (Tools::getValue('wasPending'))
			$smarty->assign('wasPending', true);

		if (Tools::getValue('message'))
			$smarty->assign('message', Tools::safeOutput(Tools::getValue('message')));

		if (Tools::getValue('type'))
			$smarty->assign(Tools::safeOutput(Tools::getValue('type')), true);

		return $this->display(__FILE__, 'tpl/adminOrder.tpl');
	}

	private function _verifRange($price, $country)
	{
		if ($price >= Configuration::get('KLARNA_MINIMUM_VALUE_'.pSQL($country)) && $price <= Configuration::get('KLARNA_MAXIMUM_VALUE_'.pSQL($country)))
			return true;
		return false;
	}

	public function hookPayment($params)
	{
		return $this->hookdisplayPayment($params);
	}

	public function hookdisplayPayment($params)
	{
		if (!Configuration::get('KLARNA_ACTIVE_INVOICE') &&	!Configuration::get('KLARNA_ACTIVE_PARTPAYMENT'))
			return false;

		$smarty = $this->context->smarty;

		$klarna = new Klarna();
		$address_invoice = new Address((int)$params['cart']->id_address_invoice);
		$country = new Country((int)$address_invoice->id_country);
		$currency = new Currency((int)$params['cart']->id_currency);

		if (isset($this->countries[$country->iso_code]))
			$this->context->cart->deleteProduct((int)Configuration::get('KLARNA_INV_FEE_ID_'.$this->countries[$country->iso_code]['name']));

		if (!$this->verifCountryAndCurrency($country, $currency))
			return false;
		if (!$this->_verifRange($params['cart']->getOrderTotal(), $this->countries[$country->iso_code]['name']))
			return false;

		try
		{
			$klarna->config(
				Configuration::get('KLARNA_STORE_ID_'.$this->countries[$country->iso_code]['name']),
				Configuration::get('KLARNA_SECRET_'.$this->countries[$country->iso_code]['name']),
				$this->countries[$country->iso_code]['code'],
				$this->countries[$country->iso_code]['langue'],
				$this->countries[$country->iso_code]['currency'],
				Configuration::get('KLARNA_MOD'),
				'mysql', $this->_getDb());
			$pclass = $klarna->getCheapestPClass((float)$this->context->cart->getOrderTotal(), KlarnaFlags::CHECKOUT_PAGE);
			if ($pclass && $pclass->getMinAmount() < $this->context->cart->getOrderTotal())
			{
				if ($country->iso_code == 'NL' && $this->context->cart->getOrderTotal() > 250)
					return false;
				else
					$value = KlarnaCalc::calc_monthly_cost((float)$this->context->cart->getOrderTotal(), $pclass, KlarnaFlags::CHECKOUT_PAGE);
			}
			$pclassSpec = $klarna->getPClasses(KlarnaPClass::SPECIAL);
			if (count($pclassSpec) && $pclassSpec[0]->getExpire() > time())
				$smarty->assign('special', $pclassSpec[0]->getDescription());
		}
		catch (Exception $e)
		{
			return false;
		}

		$smarty->assign(array(
				'var' => array('path' => $this->_path, 'this_path_ssl' => (_PS_VERSION_ >= 1.4 ? Tools::getShopDomainSsl(true, true) : '' ).__PS_BASE_URI__.'modules/'.$this->moduleName.'/'),
				'iso_code' => strtolower($country->iso_code),
				'monthly_amount' => (float)$value,
				'invoiceActive' => Configuration::get('KLARNA_ACTIVE_INVOICE'),
				'accountActive' => Configuration::get('KLARNA_ACTIVE_PARTPAYMENT'),
				'specialActive' => true));
		return $this->display(__FILE__, 'tpl/payment.tpl');
	}

	public function klarnaEncode($str)
	{
		return iconv('UTF-8', 'ISO-8859-1', $str);
	}

	public function initReservation($klarna, $cart, $customer, $house = null, $ext = null)
	{
		$address_invoice = new Address((int)$cart->id_address_invoice);
		$carrier = new Carrier((int)$cart->id_carrier);
		$country = new Country((int)$address_invoice->id_country);
		$id_currency = (int)Validate::isLoadedObject($this->context->currency) ? (int)$this->context->currency->id : (int)Configuration::get('PS_CURRENCY_DEFAULT');

		$order_id = Order::getOrderByCartId((int)$cart->id);
		if ($order_id)
		{
			$order = new Order((int)$order_id);
			foreach ($order->getProducts() as $article)
			{
				$price_wt = (float)$article['product_price_wt'];
				$price = (float)$article['product_price'];
				if (empty($article['tax_rate']))
					$rate = round((($price_wt / $price) - 1.0) * 100);
				else
					$rate = $article['tax_rate'];
				$klarna->addArticle(
					(int)$article['product_quantity'],
					$this->klarnaEncode($article['product_id']),
					$this->klarnaEncode($article['product_name']),
					$price_wt,
					$rate,
					0,
					KlarnaFlags::INC_VAT | (substr($article['product_name'], 0, 10) == 'invoiceFee' ? KlarnaFlags::IS_HANDLING : 0));
			}
		}
		else
		{
			foreach ($cart->getProducts() as $article)
			{
				$price_wt = (float)$article['price_wt'];
				$price = (float)$article['price'];
				if (empty($article['rate']))
					$rate = round((($price_wt / $price) - 1.0) * 100);
				else
					$rate = $article['rate'];
				$klarna->addArticle(
					(int)$article['cart_quantity'],
					$this->klarnaEncode((int)$article['id_product']),
					$this->klarnaEncode($article['name'].(isset($article['attributes']) ? $article['attributes'] : '')),
					$price_wt,
					$rate,
					0,
					KlarnaFlags::INC_VAT | (substr($article['name'], 0, 10) == 'invoiceFee' ? KlarnaFlags::IS_HANDLING : 0));
			}
		}

		// Add discounts
		if (_PS_VERSION_ >= 1.5)
			$discounts = $cart->getCartRules();
		else
			$discounts = $cart->getDiscounts();

		foreach ($discounts as $discount)
		{
			$rate = 0;
			$incvat = 0;
			// Free shipping has a real value of '!'.
			if ($discount['value_real'] !== '!')
			{
				$incvat = $discount['value_real'];
				$extvat = $discount['value_tax_exc'];
				$rate = round((($incvat / $extvat) - 1.0) * 100);
			}
			$klarna->addArticle(
				1,
				'', // no article number for discounts
				$this->klarnaEncode($discount['description']),
				($incvat * -1),
				$rate,
				0,
				KlarnaFlags::INC_VAT
			);
		}

		$carrier = new Carrier((int)$cart->id_carrier);

		if ($carrier->active)
		{
			$taxrate = Tax::getCarrierTaxRate(
				(int)$carrier->id,
				(int)$cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}
			);

// Next we might want to add a shipment fee for the product
			if ($order_id)
			{
				$order = new Order((int)$order_id);
				$shippingPrice = $order->total_shipping_tax_incl;
			}
			else
				$shippingPrice = $cart->getTotalShippingCost();

			$klarna->addArticle(
				1,
				$this->klarnaEncode((int)$cart->id_carrier),
				$this->klarnaEncode($carrier->name),
				$shippingPrice,
				$taxrate,
				0,
				KlarnaFlags::INC_VAT | KlarnaFlags::IS_SHIPMENT
			);
		}

		if ($cart->gift == 1)
		{
			$rate = 0;
			$wrapping_fees_tax = new Tax(
				(int)Configuration::get('PS_GIFT_WRAPPING_TAX')
			);
			if ($wrapping_fees_tax->rate !== null)
				$rate = $wrapping_fees_tax->rate;

			$klarna->addArticle(
				1,
				'',
				$this->klarnaEncode($this->l('Gift wrapping fee')),
				$cart->getOrderTotal(true, Cart::ONLY_WRAPPING),
				$rate,
				0,
				KlarnaFlags::INC_VAT
			);
		}

// Create the address object and specify the values.
		$address_delivery = new Address((int)$cart->id_address_delivery);

// Next we tell the Klarna instance to use the address in the next order.
		$address = str_replace($house, '', $address_invoice->address1);
		$address = str_replace($ext, '', $address);

		$address2 = str_replace($house, '', $address_invoice->address2);
		$address2 = str_replace($ext, '', $address2);

		$klarna->setAddress(
			KlarnaFlags::IS_BILLING,
			new KlarnaAddr(
				$this->klarnaEncode($customer->email),
				$this->klarnaEncode($address_invoice->phone),
				$this->klarnaEncode($address_invoice->phone_mobile),
				$this->klarnaEncode($address_invoice->firstname),
				$this->klarnaEncode($address_invoice->lastname),
				$this->klarnaEncode($address_invoice->company),
				$this->klarnaEncode(trim($address).($address2 != '' ? ' '.trim($address2) : '')),
				$this->klarnaEncode($address_invoice->postcode),
				$this->klarnaEncode($address_invoice->city),
				$this->klarnaEncode($this->countries[$country->iso_code]['code']),
				trim($house),
				trim($ext)
			));  // Billing / invoice address

		$address = str_replace($house, '', $address_delivery->address1);
		$address = str_replace($ext, '', $address);

		$address2 = str_replace($house, '', $address_delivery->address2);
		$address2 = str_replace($ext, '', $address2);


		$klarna->setAddress(
			KlarnaFlags::IS_SHIPPING,
			new KlarnaAddr(
				$this->klarnaEncode($customer->email),
				$this->klarnaEncode($address_delivery->phone),
				$this->klarnaEncode($address_delivery->phone_mobile),
				$this->klarnaEncode($address_delivery->firstname),
				$this->klarnaEncode($address_delivery->lastname),
				$this->klarnaEncode($address_delivery->company),
				$this->klarnaEncode(trim($address).($address2 != '' ? ' '.trim($address2) : '')),
				$this->klarnaEncode($address_delivery->postcode),
				$this->klarnaEncode($address_delivery->city),
				$this->klarnaEncode($this->countries[$country->iso_code]['code']),
				trim($house),
				trim($ext)
			));  // Billing / invoice address
	}

	public function isInCart($cart, $id)
	{
		foreach ($cart->getProducts() as $article)
			if ($article['id_product'] == $id)
				return true;
		return false;
	}

	public function setPayment($type)
	{
		$address_invoice = new Address((int)$this->context->cart->id_address_invoice);
		$country = new Country((int)$address_invoice->id_country);
		$currency = new Currency((int)$this->context->cart->id_currency);
		if (!$this->verifCountryAndCurrency($country, $currency))
			return false;
		$klarna = new Klarna();
		$klarnaInt = new KlarnaIntegration($klarna);


		$klarna->config(
			Configuration::get('KLARNA_STORE_ID_'.$this->countries[$country->iso_code]['name']),
			Configuration::get('KLARNA_SECRET_'.$this->countries[$country->iso_code]['name']),
			$this->countries[$country->iso_code]['code'],
			$this->countries[$country->iso_code]['langue'],
			$this->countries[$country->iso_code]['currency'],
			Configuration::get('KLARNA_MOD'),
			'mysql', $this->_getDb());

		if ($type == 'invoice' && Configuration::get('KLARNA_INVOICE_FEE_'.$this->countries[$country->iso_code]['name']) > 0 && !$this->isInCart($this->context->cart, (int)Configuration::get('KLARNA_INV_FEE_ID_'.$this->countries[$country->iso_code]['name'])))
		{
			$this->context->cart->updateQty(1, (int)Configuration::get('KLARNA_INV_FEE_ID_'.$this->countries[$country->iso_code]['name']));
			$productInvoicefee = new Product((int)Configuration::get('KLARNA_INV_FEE_ID_'.$this->countries[$country->iso_code]['name']));
			$productInvoicefee->addStockMvt(1, 1);
			$productInvoicefee->update();
		}

		$this->initReservation(
			$klarna,
			$this->context->cart,
			$this->context->customer,
			(isset($_POST['klarna_house_number']) ? htmlentities($_POST['klarna_house_number'], ENT_QUOTES, 'ISO-8859-1') : null),
			(isset($_POST['klarna_house_ext']) ? htmlentities($_POST['klarna_house_ext'], ENT_QUOTES, 'ISO-8859-1') : null)
		);

		if (Tools::isSubmit('klarna_pno'))
			$pno = Tools::safeOutput(Tools::getValue('klarna_pno'));
		else
		{
			$day = ($_POST['klarna_pno_day'] < 10 ? '0'.(int)$_POST['klarna_pno_day'] : (int)$_POST['klarna_pno_day']);
			$month = ($_POST['klarna_pno_month'] < 10 ? '0'.(int)$_POST['klarna_pno_month'] : (int)$_POST['klarna_pno_month']);

			$pno = Tools::safeOutput($day.$month.Tools::getValue('klarna_pno_year'));
		}

		$pclass = ($type == 'invoice' ? KlarnaPClass::INVOICE : (int)Tools::getValue('paymentAccount'));

		try
		{
			if ($country->iso_code == 'DE' || $country->iso_code  == 'NL')
			{
				if ($this->context->customer->id_gender != 1 && $this->context->customer->id_gender != 2 && $this->context->customer->id_gender != 3)
				{
					$gender = (int)$_POST['klarna_gender'];
					$customer = new Customer($this->context->customer->id);
					$customer->id_gender = (int)$_POST['klarna_gender'];
					$Customer->birthday = (int)$_POST['klarna_pno_year'].'-'.$month.'-'.$day;
					$customer->update();
				}
				else
					$gender = $this->context->customer->id_gender == 1 ? 1 : 0;
			}
			else
				$gender = null;

			$result = $klarnaInt->reserve(
				$pno,
				$gender,
		        // Amount. -1 specifies that calculation should calculate the amount
		        // using the goods list
		        -1,
		        KlarnaFlags::NO_FLAG, // Flags to affect behavior.
		        // -1 notes that this is an invoice purchase, for part payment purchase
		        // you will have a pclass object on which you use getId().
		        (int)$pclass //KlarnaPClass::INVOICE
			);

			// Here we get the reservation number or invoice number
			$rno = $result[0];

			Db::getInstance()->autoExecute(
				_DB_PREFIX_.'klarna_rno',
				array(
					'id_cart' => (int)$this->context->cart->id,
					'rno' => pSQL($rno),
					'pno' => pSQL($pno),
					'house_number' => (isset($_POST['klarna_house_number']) ? pSQL($_POST['klarna_house_number']) : null),
					'house_ext' => (isset($_POST['klarna_house_ext']) ? pSQL($_POST['klarna_house_ext']) : null),
					'state' => self::RESERVED,
					'type' => pSQL($type),
					'pclass' => ($type == 'invoice' ? null : (int)Tools::getValue('paymentAccount'))
				),
				'INSERT');
			$updateResult = $klarnaInt->updateOrderNo($rno, (int)$this->context->cart->id);

			if ($result[1] == KlarnaFlags::PENDING)
				$this->validateOrder((int)$this->context->cart->id,
					Configuration::get('KLARNA_PAYMENT_PENDING'),
					(float)$this->context->cart->getOrderTotal(),
					$this->displayName,
					null,
					array(),
					null,
					false,
					$this->context->cart->secure_key);
			else if ($result[1] == KlarnaFlags::ACCEPTED)
				$this->validateOrder((int)$this->context->cart->id,
					Configuration::get('KLARNA_PAYMENT_ACCEPTED'),
					(float)$this->context->cart->getOrderTotal(),
					$this->displayName,
					null,
					array(),
					null,
					false,
					$this->context->cart->secure_key);

			$redirect = __PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.(int)$this->context->cart->id.'&id_module='.(int)$this->id.'&id_order='.(int)$this->currentOrder.'&key='.$this->context->cart->secure_key;
			header('Location: '.$redirect);
			exit;
		}
		catch (Exception $e)
		{
			/*remove invoiceFee if existe*/
			$this->context->cart->deleteProduct((int)Configuration::get('KLARNA_INV_FEE_ID_'.$this->countries[$country->iso_code]['name']));

 			return array('error' => true, 'message' => Tools::safeOutput(utf8_encode($e->getMessage())));
		}
	}

	public function hookDisplayPaymentReturn($params)
	{
		if ($params['objOrder']->module != $this->name)
			return false;
		$cart = new Cart((int)$params['objOrder']->id_cart);
		if (Validate::isLoadedObject($cart))
		{
			$address = new Address((int)$cart->id_address_invoice);
			$country = new Country((int)$address->id_country);
			$cart->deleteProduct((int)Configuration::get('KLARNA_INV_FEE_ID_'.$this->countries[$country->iso_code]['name']));
			$cart->save();
		}
	}

	public function hookDisplayOrderConfirmation($params)
	{
		if ($params['objOrder']->module != $this->name)
			return false;
		if ($params['objOrder'] && Validate::isLoadedObject($params['objOrder']) && isset($params['objOrder']->valid))
		{
			if (isset($params['objOrder']->reference))
				$this->smarty->assign('klarna_order', array('reference' => $params['objOrder']->reference, 'valid' => $params['objOrder']->valid));
			return $this->display(__FILE__, 'tpl/order-confirmation.tpl');
		}
	}

}
