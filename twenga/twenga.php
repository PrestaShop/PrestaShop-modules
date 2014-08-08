<?php
/**
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2014 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

/**
 * Twenga module allow to use the Twenga API to :
 * 1. subscribe to their Ready to Sell engine,
 * 2. activate a tracking for order process if user has been used twenga engine,
 * 3. submit a xml feed of shop products to Twenga.
 * @version 2.0
 */

if (!defined('_PS_VERSION_')) exit;

class Twenga extends PaymentModule
{
	/**
	 * path to load each needed files
	 * @var string
	 */
	private static $base_dir;

	/**
	 * Url path to access of module file.
	 * @var string
	 */
	private static $base_path;
	/**
	 * @var TwengaObj
	 */
	private static $obj_twenga;

	/**
	 * @var PrestashopStats
	 */
	private static $obj_ps_stats;

	/**
	 * @var string url used for the subscription to Twenga and prestashop
	 */
	private $site_url;

	/**
	 * @var string url to acces of the product list for Twenga
	 */
	private $feed_url;

	/**
	 * @var string url returned by Twenga API
	 */
	private $inscription_url;

	/**
	 * @var string used for displaying html
	 */
	private $_html;

	/**
	 * @var string
	 */
	private $current_index;

	/**
	 * @var string
	 */
	private $token;

	/**
	 * Countries where Twenga works.
	 * need to be in lowercase
	 * @var array
	 */
	public $limited_countries = array('fr', 'de', 'gb', 'uk', 'it', 'es', 'nl');

	private $_allowToWork = true;

	private $_currentIsoCodeCountry = NULL;

	const ONLY_PRODUCTS = 1;
	const ONLY_DISCOUNTS = 2;
	const BOTH = 3;
	const BOTH_WITHOUT_SHIPPING = 4;
	const ONLY_SHIPPING = 5;
	const ONLY_WRAPPING = 6;
	const ONLY_PRODUCTS_WITHOUT_SHIPPING = 7;

	/**
	 * The current country iso code for the shop.
	 * @var string
	 */
	private static $shop_country;

	public function __construct()
	{
		// Basic vars
		global $currentIndex;
		$this->current_index = $currentIndex;
		$this->token = Tools::getValue('token');
	 	$this->name = 'twenga';
	 	$this->tab = 'smart_shopping';
	 	$this->version = '2.0';
		$this->author = 'PrestaShop';

	 	parent::__construct();

		$this->displayName = $this->l('Twenga Module');
		$this->description = $this->l('Export your products to Twenga Shopping Search Engine and get new online buyers immediately.');

   		/* Backward compatibility */
    	if (_PS_VERSION_ < '1.5')
        	require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
		
		// For Twenga subscription
		$protocol = 'http://';
		if (isset($_SERVER['https']) && $_SERVER['https'] != 'off')
			$protocol = 'https://';
		$this->site_url = Tools::htmlentitiesutf8($protocol.$_SERVER['HTTP_HOST'].__PS_BASE_URI__);
		self::$base_dir = _PS_ROOT_DIR_.'/modules/twenga/';
		self::$base_path = $this->site_url.'/modules/twenga/';
		$this->feed_url = self::$base_path.'export.php?twenga_token='.sha1(Configuration::get('TWENGA_TOKEN')._COOKIE_KEY_);

		self::$shop_country = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));

		require_once realpath(self::$base_dir.'/lib/PrestashopStats.php');
		require_once realpath(self::$base_dir.'/lib/TwengaObj.php');

		// set the base dir to load files needed for the TwengaObj class
		TwengaObj::$base_dir = self::$base_dir.'/lib';

		TwengaObj::setTranslationObject($this);
		TwengaException::setTranslationObject($this);
		if (!in_array(strtolower(self::$shop_country), $this->limited_countries))
		{
			$this->_allowToWork = false;
			$this->warning = $this->l('Twenga module works only in specific countries (iso code list:').' '.implode(', ',$this->limited_countries).').';;
			return false;
		}

		// instanciate (just once) the TwengaObj and PrestashopStats
		if (self::$obj_twenga === NULL)
			self::$obj_twenga = new TwengaObj();
		if (self::$obj_ps_stats === NULL)
			self::$obj_ps_stats = new PrestashopStats($this->site_url);
		$this->_initCurrentIsoCodeCountry();
		
		if (!extension_loaded('openssl'))
		$this->warning = $this->l('OpenSSL should be activated on your PHP configuration to use all functionalities of Twenga.');
		
	}

	public function install()
	{
		if (Configuration::updateValue('TWENGA_TOKEN', Tools::passwdGen()))
			return parent::install();
		return false;
	}

	/**
	 * For uninstall just need to delete the Merchant Login.
	 * @return bool see parent class.
	 */
	public function uninstall()
	{
		if (!parent::uninstall() || !self::$obj_twenga->deleteMerchantLogin())
			return false;
		return true;
	}

	private function _initCurrentIsoCodeCountry()
	{
		global $cookie;

		$country = Db::getInstance()->ExecuteS('
			SELECT c.iso_code as iso
			FROM '._DB_PREFIX_.'country as c
			LEFT JOIN '._DB_PREFIX_.'country_lang as c_l
			ON c_l.id_country = c.id_country
			WHERE c_l.id_lang = '.(int)$cookie->id_lang.'
			AND c.id_country = '.	Configuration::get('PS_COUNTRY_DEFAULT'));

		if (isset($country[0]['iso']))
			$this->_currentIsoCodeCountry = $country[0]['iso'];
	}

	public function ajaxRequestType()
	{
		if (isset($_POST) && isset($_POST['type']) && isset($_POST['base']))
		{
			$link = 'http://addons.prestashop.com/'.Language::getIsoById($_POST['id_lang']).
				'/2053-twenga-ready-to-sell.html';

			$type = (($_POST['type'] == 'desactive') ? $this->l('Disable') :
				(($_POST['type'] == 'reset') ? $this->l('Reset') :
				(($_POST['type'] == 'uninstall') ? $this->l('Uninstall') : $this->l('Delete'))));

			if ($_POST['type'] == 'delete')
				$_POST['type'] = 'deleteModule';
			$url = $_POST['base'].'&token='.$_POST['token'].'&module_name='.
				$_POST['module_name'].'&tab_module='.$_POST['tab_module'].'&'.
				$_POST['type'].'='.$_POST['module_name'];

			$msg = '
				<style>
					#mainContent {
						border:1px solid #B0C4DE;
						background-color:#E2EBEE;
						-moz-border-radius:10px;
						-webkit-border-radius:10px;
						line-height:18px;
						font-size:14px; }

					#mainContent a { text-decoration:none; color:#268CCD;}
			</style>
			<div id="mainContent" >
				<p>'.$this->l('If you subscribe on Twenga, the activation of this module is mandatory.').
				'<br /><br />'.$this->l('If there\'s a problem, uninstall this module, install the newer version here and enter the Twenga hashkey again and log in.').'
				<br /><br />'.$this->l('To unsubscribe or for any question, please contact Twenga on your account.').'
				<div style="margin: 10px 0 5px 0; font-size:14px; color:#FFF; text-align:center;">
				<b><a '.(($_POST['type'] == 'uninstall') ?
				'onClick="$.fancybox.close(); window.location=\''.Tools::safeOutput($url).'\' '.
				$this->_getAjaxScript('send_mail.php', Tools::safeOutput($_POST['type']), Tools::safeOutput($url), false).'"' : ' ') .
				'href="'.Tools::safeOutput($url).'">'.Tools::safeOutput($type).'</a></b>  -
				<b><a href="'.Tools::safeOutput($link).'">'.
				$this->l('Newer version').'</a></b> -
				<b><a href="javacript:void(0);"i onclick="$.fancybox.close(); return false;">'.
				$this->l('Cancel').'</a></b>
				</div></p>';
			echo $msg;
		}
	}

	/*
	 ** Get the javascript code to fetch a distant file
	 ** href will be automatically split cause of its '&'
	 */
	private function _getAjaxScript($file, $type, $href, $displayMsg = true)
	{
		global $cookie;

		return '
				$.ajax({
						type: \'POST\',
						url: \''._MODULE_DIR_.'twenga/'.$file.'\',
						data: \'type='.$type.'&base='.$href.'&twenga_token='.sha1(Configuration::get('TWENGA_TOKEN')._COOKIE_KEY_).'&id_lang='.(int)$cookie->id_lang.'\',
						success: function(msg) {
							'.(($displayMsg) ? '
							$.fancybox(msg, {
								\'autoDimensions\'	: false,
								\'width\'						: 450,
								\'height\'					: \'auto\',
								\'transitionIn\'		: \'none\',
								\'transitionOut\'		: \'none\'	});'
								: '') . '
						}
		});
		return false;';
	}

	/**
	 * Method for beeing redirected to Twenga subscription
	 */
	private static function redirectTwengaSubscription($link)
	{
		echo '<script type="text/javascript" language="javascript">window.open("'.$link.'");</script>';
	}

	private function submitTwengaLogin()
	{
		if (!self::$obj_twenga->setHashkey($_POST['twenga_hashkey']))
			$this->_errors[] = $this->l('Your hashkey is invalid. Please check the e-mail already sent by Twenga.');
		if (!self::$obj_twenga->setUserName($_POST['twenga_user_name']))
			$this->_errors[] = $this->l('Your user name is invalid. Please check the e-mail already sent by Twenga.');
		if (!self::$obj_twenga->setPassword($_POST['twenga_password']))
			$this->_errors[] = $this->l('Your password is invalid. Please check the e-mail already sent by Twenga.');

		if (empty($this->_errors))
		{
			$bool_save = false;
			try
			{
				$bool_save = self::$obj_twenga->saveMerchantLogin();
				self::$obj_ps_stats->validateSubscription();
				if (!$bool_save)
					$this->_errors[] = $this->l('Authentication failed.')."<br />\n"
						.$this->l('Please review the e-mail sent by Twenga after subscription. If error still occurred, contact Twenga service.');
				else
				{
					self::$obj_twenga->addFeed(array('feed_url' => $this->feed_url));
					$this->_html = '
						<div class="conf feed_url">
						'.$this->l('Your product export URL has successfully been created and shared with the Twenga team:').'<br /> <a href="'.$this->feed_url.'" target="_blank">'.$this->feed_url.'</a>
						</div>
					';
					Configuration::updateValue('TWENGA_CONFIGURATION_OK', true);
				}
			}
			catch (Exception $e)
			{
				$this->_errors[] = nl2br($e->getMessage());
			}
		}
	}

	private function submitTwengaActivateTracking()
	{
		$activate = false;

		// Use TwengaObj::siteActivate() method to activate tracking.
		try
		{
		   $activate = self::$obj_twenga->siteActivate();
		}
		catch (Exception $e)
		{
			$this->_errors[] = $e->getMessage();
		}
		if ($activate)
		{
			$this->_html = '
				<div class="conf">
				'.$this->l('Your sales tracking is enabled.').'</a>
				</div>
			';
			
			$this->registerHook('displayHome');
			$this->registerHook('displayProductButtons');
			$this->registerHook('displayShoppingCart');
			$this->registerHook('displayPayment');
			$this->registerHook('payment');
			$this->registerHook('orderConfirmation');
		}
	}

	private function submitTwengaDisableTracking()
	{
		$return = Db::getInstance()->ExecuteS('SELECT `id_hook` FROM `'._DB_PREFIX_.'hook_module` WHERE `id_module` = \''.pSQL($this->id).'\'');
		foreach ($return as $hook)
			$this->unregisterHook($hook['id_hook']);
	}

	public function preProcess()
	{
		if (isset($_POST['submitTwengaSubscription']))
		   $this->submitTwengaSubscription();
		if (isset($_POST['submitTwengaLogin']))
			$this->submitTwengaLogin();
		if (isset($_POST['submitTwengaActivateTracking']))
			$this->submitTwengaActivateTracking();
		if (isset($_POST['submitTwengaDisableTracking']))
			$this->submitTwengaDisableTracking();
	}

	/**
	 * Function HOOK Home
	 */
	public function hookDisplayHome($params)
	{
		return $this->doHook($params, 'home');
	}

	/**
	 * Function HOOK Product
	 */
	public function hookDisplayProductButtons($params)
	{
		return $this->doHook($params, 'product');
	}

	/**
	 * Function HOOK Basket
	 */
	public function hookDisplayShoppingCart($params)
	{
		return $this->doHook($params, 'basket');
	}

	/**
	 * Function HOOK Transaction
	 */
	public function hookPayment($params)
	{
		return $this->doHook($params, 'basket');
	}
	
	public function hookOrderConfirmation($params){
		return $this->doHook($params, 'transaction');
	}

	/**
	 * Function DO HOOK
	 */
	public function doHook($aParams, $sEvent = '')
	{
		if ($this->_allowToWork == false) return;
		
		$oCustomer = new Customer($aParams['cart']->id_customer);
		$oCurrency = new Currency($aParams['cart']->id_currency);
		$aAddress = $oCustomer->getAddresses($aParams['cart']->id_lang);

		$aAddress = $aAddress[0];
		
		$sUserCountry = '';
		if(isset($aAddress['id_country']) && !empty($aAddress['id_country'])){
			$sUserCountry = Country::getIsoById($aAddress['id_country']);
		}

		// for 1.3 compatibility
		$tva = false;
		if(isset($aParams['objOrder']) && !empty($aParams['objOrder'])){
			$tax = ($aParams['objOrder']->total_paid_tax_incl-$aParams['objOrder']->total_shipping_tax_incl) - ($aParams['objOrder']->total_paid_tax_excl-$aParams['objOrder']->total_shipping_tax_excl);
			$tva = $aParams['objOrder']->carrier_tax_rate;
		}else{
			$tax = $aParams['cart']->getOrderTotal(true, Twenga::ONLY_PRODUCTS_WITHOUT_SHIPPING)-$aParams['cart']->getOrderTotal(false, Twenga::ONLY_PRODUCTS_WITHOUT_SHIPPING);
			if($aParams['cart']->getOrderTotal(false, Twenga::ONLY_PRODUCTS_WITHOUT_SHIPPING)>0){
				$tva = ($tax * 100) / $aParams['cart']->getOrderTotal(false, Twenga::ONLY_PRODUCTS_WITHOUT_SHIPPING);
			}
		}
		
		$aParamsToTwenga = array();
		$aParamsToTwenga['event'] = $sEvent;
		
		$aParamsToTwenga['user_id'] = $aParams['cart']->id_customer;
		$aParamsToTwenga['user_global_id'] = md5($oCustomer->email);
		$aParamsToTwenga['user_email'] = $oCustomer->email;
		$aParamsToTwenga['user_firstname'] = $oCustomer->firstname;
		$aParamsToTwenga['user_city'] = ($aParams['cart']->id_customer)? $aAddress['city'] : '';
		$aParamsToTwenga['user_state'] = ($aParams['cart']->id_customer)? $aAddress['state'] : '';
		$aParamsToTwenga['user_country'] = ($aParams['cart']->id_customer)? $sUserCountry : '';
		$aParamsToTwenga['user_segment'] = '';
		$aParamsToTwenga['user_is_customer'] = 1;
		$aParamsToTwenga['ecommerce_platform'] = 'Prestashop';
		$aParamsToTwenga['tag_platform'] = '';
		
		$aParamsToTwenga['basket_id'] = $aParams['cart']->id;
		$aParamsToTwenga['currency'] = $oCurrency->iso_code;
		$aParamsToTwenga['total_ht'] = isset($aParams['objOrder']) ? $aParams['objOrder']->total_paid_tax_excl-$aParams['objOrder']->total_shipping_tax_excl : $aParams['cart']->getOrderTotal(false, Twenga::ONLY_PRODUCTS_WITHOUT_SHIPPING);
		$aParamsToTwenga['tva'] = ($tva !== false) ? Tools::ps_round($tva, 2) : '';
		$aParamsToTwenga['total_ttc'] = isset($aParams['objOrder']) ? $aParams['objOrder']->total_paid_tax_incl-$aParams['objOrder']->total_shipping_tax_incl : $aParams['cart']->getOrderTotal(true, Twenga::ONLY_PRODUCTS_WITHOUT_SHIPPING);
		$aParamsToTwenga['shipping'] = isset($aParams['objOrder']) ? $aParams['objOrder']->total_shipping_tax_incl : $aParams['cart']->getOrderTotal(true, Twenga::ONLY_SHIPPING);
		$aParamsToTwenga['tax'] = $tax;
		
		if(isset($aParams['objOrder']) && !empty($aParams['objOrder'])){
			$aParamsToTwenga['order_id'] = $aParams['objOrder']->id;
		}
		
		$aParamsToTwenga['items'] = array();
		if($sEvent == 'product' && (isset($_POST['id_product'])) || isset($_GET['id_product'])){
			$iIdProduct = (isset($_POST['id_product'])) ? $_POST['id_product'] : $_GET['id_product'];
			$oProduct = new Product($iIdProduct);
			if($oProduct){
				$oCategory = new Category($oProduct->id_category_default);
				if($oCategory){
					$arr_item = array();
					$arr_item['price'] = $oProduct->price;
					$arr_item['quantity'] = '';
					$arr_item['ref_id'] = $oProduct->reference;
					$arr_item['item_id'] = $iIdProduct;
					$arr_item['name'] = $oProduct->name[1];
					$arr_item['category_name'] = $oCategory->name;
					$aParamsToTwenga['items'][] = $arr_item;
				}
			}
		}elseif(isset($aParams['objOrder']) && !empty($aParams['objOrder'])){
			foreach ($aParams['objOrder']->getProducts() as $product)
			{
				$oCategory = new Category($product['id_category_default']);
				$arr_item = array();
				if ($product['unit_price_tax_excl']!= '')
					$arr_item['price'] = (float)$product['unit_price_tax_excl'];
				if ($product['product_quantity'] != '')
					$arr_item['quantity'] = (int)$product['product_quantity'];
				if ($product['reference'] != '')
					$arr_item['ref_id'] = (string)$product['reference'];
				if ($product['id_product'] != '')
					$arr_item['item_id'] = (string)$product['id_product'];
				if ($product['product_name'] != '')
					$arr_item['name'] = (string)$product['product_name'];
				if (isset($oCategory) && !empty($oCategory))
					$arr_item['category_name'] = $oCategory->name;
				$aParamsToTwenga['items'][] = $arr_item;
			}			
		}else{
			foreach ($aParams['cart']->getProducts() as $product)
			{
				$arr_item = array();
				if ($product['price']!= '')
					$arr_item['price'] = (float)$product['price'];
				if ($product['cart_quantity'] != '')
					$arr_item['quantity'] = (int)$product['cart_quantity'];
				if ($product['reference'] != '')
					$arr_item['ref_id'] = (string)$product['reference'];					
				if ($product['id_product'] != '')
					$arr_item['item_id'] = (string)$product['id_product'];
				if ($product['name'] != '')
					$arr_item['name'] = (string)$product['name'];
				if ($product['category'])
					$arr_item['category_name'] = (string)$product['category'];
				$aParamsToTwenga['items'][] = $arr_item;
			}
		}

		$aParamsToTwenga = array_filter($aParamsToTwenga);
		try
		{
			$tracking_code = self::$obj_twenga->getTrackingScript($aParamsToTwenga);
			return $tracking_code;
		}
		catch (TwengaFieldsException $e)
		{
			return $this->l('Error occurred when params passed in Twenga API').' : <br />'.$e->getMessage();
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}
	}

	/*
	 ** Get the current country name used literaly
	 */
	public static function getCurrentCountryName()
	{
		global $cookie;

		$id_lang = ((isset($cookie->id_lang)) ? (int)$cookie->id_lang :
			((isset($_POST['id_lang'])) ? (int)$_POST['id_lang'] : NULL));

		if ($id_lang === NULL)
			return 'Undefined id_lang';
		$country = Db::getInstance()->ExecuteS('
			SELECT c.name as name
			FROM '._DB_PREFIX_.'country_lang as c
			WHERE c.id_lang = '.(int)$id_lang.'
			AND c.id_country = '.(int)Configuration::get('PS_COUNTRY_DEFAULT'));

		if (!isset($country[0]['name']))
			$country[0]['name'] = 'Undefined';
		return $country[0]['name'];
	}

	/*
	 ** Check if the default country if available with the restricted ones
	 */
	private function _checkCurrentCountrie()
	{
		global $cookie;

		if (!in_array(strtolower($this->_currentIsoCodeCountry), $this->limited_countries))
		{
			$query = '
				SELECT c_l.name as name
				FROM '._DB_PREFIX_.'country_lang as c_l
				LEFT JOIN '._DB_PREFIX_.'country as c
				ON c_l.id_country = c.id_country
				WHERE c_l.id_lang = '.(int)$cookie->id_lang.'
				AND c.iso_code IN (';
			foreach ($this->limited_countries as $iso)
				$query .= "'".strtoupper($iso)."', ";
			$query = rtrim($query, ', ').')';
			$countriesName = Db::getInstance()->ExecuteS($query);
			$htmlError = '
				<div class="error">
					<p>'.$this->l('Your default country is').' : '.Twenga::getCurrentCountryName().'</p>
					<p>'.$this->l('Please select one of these available countries approved by Twenga').' :</p>
					<ul>';
			foreach ($countriesName as $c)
				$htmlError .= '<li>'.$c['name'].'</li>';
			$url = Tools::getShopDomain(true).$_SERVER['PHP_SELF'].'?tab=AdminCountries&token='.
				Tools::getAdminTokenLite('AdminCountries').'#Countries';
			$htmlError .= '
					</ul>
					'.$this->l('Follow this link to change the country').
					' : <a style="color:#0282dc;" href="'.$url.'">here</a>
				</div>';
			throw new Exception($htmlError);
		}
	}

	public function getContent()
	{
		try
		{
			$this->_checkCurrentCountrie();
			if ((Configuration::get('PS_ORDER_PROCESS_TYPE') == 1 && $this->isRegisteredInHook('displayPayment')) ||
				(Configuration::get('PS_ORDER_PROCESS_TYPE') == 1 && $this->isRegisteredInHook('Payment')))
			{
				$this->submitTwengaActivateTracking();
			}
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}

		// API can't be call if curl extension is not installed on PHP config.
		if (!extension_loaded('curl'))
		{
			$this->_errors[] = $this->l('Please activate the PHP extension \'curl\' to allow use of Twenga webservice library.');
			return $this->displayErrors();
		}
		$this->preProcess();

		if ($this->_hasConfigTwenga() && !$this->isRegisteredInHook('displayPayment') && !$this->isRegisteredInHook('Payment')){
			$this->_html .= $this->displayEnableTracker();
		}

		$this->_html .= '<h2>'.$this->displayName.'</h2>';
		$this->_html .= $this->displayTwengaIntro();
		if (!$this->_hasConfigTwenga()){
			$this->_html .= $this->displayTwengaHowTo();
		}
		$this->_html .= $this->displayTwengaLogin();

		if ($this->_hasConfigTwenga()){
			if($this->isRegisteredInHook('displayPayment') || $this->isRegisteredInHook('Payment')){
				$this->_html .= $this->displayDisableTracker();
			}
			$this->_html .= $this->displayFeedUrl();
		}

		$this->_html .= $this->displayTwengaTestimony();

		return $this->displayErrors().$this->_html;
	}

	public function displayTwengaHowTo()
	{
		global $cookie;
		$isoUser = strtolower(Language::getIsoById(intval($cookie->id_lang)));

		if ($isoUser == 'gb' || $isoUser == 'en')
			$link_tools = 'https://rts.twenga.co.uk/account';
		elseif ($isoUser == 'es')
			$link_tools = 'https://rts.twenga.es/account';
		elseif ($isoUser == 'it')
			$link_tools = 'https://rts.twenga.it/account';
		elseif ($isoUser == 'de')
			$link_tools = 'https://rts.twenga.de/account';
		else
			$link_tools = 'https://rts.twenga.fr/account';
		$str_return = '

			<fieldset class="moduleTwenga-referencement">
				<legend><img src="../modules/'.$this->name.'/img/logo-small.png" width="16" height="16" class="middle" /> '.$this->l('A - List your website on Twenga').'</legend>

				<div class="no-inscr">
					<img src="../modules/'.$this->name.'/img/puce.png" width="16" height="16" alt="Twenga" /> <a href="#" class="display">'.$this->l('You have never had a Twenga Ready to Sell account').'</a>
					<ol>
						<li><a href="'.$this->inscription_url.'" target="_blank">'.$this->l('Fill out the Twenga Ready to Sell application forms.').'</a></li>
						<li>'.$this->l('Once you have received your Twenga key/hashkey via e-mail, come back to this page and copy it into the "Hashkey" field below.').'</li>
						<li>'.$this->l('Click on Activate my Twenga Export to create your export product feed.').'</li>
					</ol>
				</div>
				<div class="inscr">
					<img src="../modules/'.$this->name.'/img/puce.png" width="16" height="16" alt="Twenga" /> <a href="#" class="display">'.$this->l('You are already registered to the Twenga Ready to Sell programme').'</a>
					<ol>
						<li>'.$this->l('Get your Twenga key directly from your').' <a href="'.$link_tools.'" target="_blank"> '.$this->l('Twenga Ready to Sell interface.').'</a></li>
						<li>'.$this->l('Return to this page and copy your key into the "Hashkey" field below.').'</li>
						<li>'.$this->l('Click on Activate my Twenga Export to create your export product feed.').'</li>
					</ol>
				</div>
			</fieldset>
		';
		return $str_return;
	}

	public function displayTwengaIntro()
	{
		global $cookie;

		$isoUser = strtolower(Language::getIsoById(intval($cookie->id_lang)));
		$defaultIsoCountry = strtolower($this->_currentIsoCodeCountry);

		$errors = array();
		try
		{
			$return = self::$obj_twenga->getSubscriptionLink(array('site_url' => $this->site_url, 'feed_url' => $this->feed_url, 'country' => $isoUser, 'module_version' => (string)$this->version, 'platform_version' => (string)_PS_VERSION_));
			$this->inscription_url= $return['message'];

		}
		catch (TwengaFieldsException $e)
		{
			$errors[] = $e->getMessage();
		}
		catch (TwengaException $e)
		{
			$errors[] = $e->getMessage();
		}
		if (!empty($errors))
		{
			$str_error = $this->l('Errors occurred with the Twenga API subscription link:');
			$str_error .=  '<ol>';
			foreach ($errors as $error)
				$str_error .= '<li><em>'.$error.'</em></li>';
			$str_error .= '</ol>';
			$this->_errors[] = $str_error;
		}

		if ($isoUser == 'gb' || $isoUser == 'en')
			$tarifs_link = 'https://rts.twenga.co.uk/ratecard';
		elseif ($isoUser == 'es')
			$tarifs_link = 'https://rts.twenga.es/ratecard';
		elseif ($isoUser == 'it')
			$tarifs_link = 'https://rts.twenga.it/ratecard';
		elseif ($isoUser == 'de')
			$tarifs_link = 'https://rts.twenga.de/ratecard';
		else
			$tarifs_link = 'https://rts.twenga.fr/ratecard';

		$tarif_arr = array(950, 565);
		if (extension_loaded('openssl') && file_exists($tarifs_link))
			$tarif_arr = @getimagesize($tarifs_link);

		$str_return = '
			<script type="text/javascript">
			$().ready(function()
			{
				$("#twenga_tarif").click(function(e){
					e.preventDefault();
					window.open("'.$tarifs_link.'", "", "width='.$tarif_arr[0].', height='.$tarif_arr[1].', scrollbars=no, menubar=no, status=no" );
				});
				$(".moduleTwenga-referencement ol").hide();
				$(".display").click(function(){
					$(this).next(".moduleTwenga-referencement ol").slideToggle();
					$(this).prev(".moduleTwenga-referencement img").toggleClass("rotate");
					return false;
				});
			});
			</script>
			<link type="text/css" rel="stylesheet" href="../modules/'.$this->name.'/css/module-twenga.css" />

			<div class="module-twenga">
				<div class="moduleTwenga-intro" style="background-image:url(../modules/'.$this->name.'/img/cible-twenga.png);")>
					<p><img src="../modules/'.$this->name.'/img/logo-twenga.png" width="200" height="43" alt="Twenga" /></p>
					<p class="module-title">'.$this->l('List your products and increase your sales.').'</p>
					<dl>
						<dt>'.$this->l('Manage your budget:').'</dt>
						<dd>'.$this->l('You pay for the traffic that Twenga sends you using CPC. Joining Twenga and setting up your account is free of charge.').'<br /> '.$this->l('If needs be, you can set up a monthly budget limit.').'</dd>
						<dt>'.$this->l('Manage your activity and sales:').'</dt>
						<dd>'.$this->l('Dedicated merchant interface including detailed traffic & sales support tools.').'</dd>
						<dt>'.$this->l('Manage your visibility:').'</dt>
						<dd>'.$this->l('Boost product visibility whenever you like!').'</dd>
					</dl>
					<p class="module-title">'.$this->l('More than 15,000 merchants have already joined Twenga Ready to Sell.').'</p>
				</div>
				';
		return $str_return;
	}

	public function displayFeedUrl()
	{
		$output = '
		<br />
		<fieldset class="moduleTwenga-referencement">
			<legend><img src="../modules/'.$this->name.'/img/logo-small.png" width="16" height="16" class="middle" /> '.$this->l('Your Twenga Product Export').'</legend>
			<p>'.$this->l('Your product export URL has successfully been created and shared with the Twenga team:').'</p>
			<p>'.$this->feed_url.'</p>
		</fieldset>
		';
		return $output;
	}

	/**
	 * @return string html form for log to Twenga API.
	 */
	private function displayTwengaLogin()
	{
		global $cookie;

		$isoUser = strtolower(Language::getIsoById(intval($cookie->id_lang)));
		if ($isoUser == 'gb' || $isoUser == 'en')
			$lost_link = 'https://rts.twenga.co.uk/lostpassword';
		else
			$lost_link = 'https://rts.twenga.'.$isoUser.'/lostpassword';

		if ($isoUser == 'gb' || $isoUser == 'en')
			$tarifs_link = 'https://rts.twenga.co.uk/ratecard';
		elseif ($isoUser == 'es')
			$tarifs_link = 'https://rts.twenga.es/ratecard';
		elseif ($isoUser == 'it')
			$tarifs_link = 'https://rts.twenga.it/ratecard';
		elseif ($isoUser == 'de')
			$tarifs_link = 'https://rts.twenga.de/ratecard';
		else
			$tarifs_link = 'https://rts.twenga.fr/ratecard';

		$output = '
			<form name="form_set_hashkey" action="" method="post">
				<fieldset class="moduleTwenga-installation">
				<legend>
					<img src="../modules/'.$this->name.'/img/logo-small.png" width="16" height="16" class="middle" /> ';

		if (((self::$obj_twenga->getHashKey() === NULL || self::$obj_twenga->getHashKey() === '')) &&
			((self::$obj_twenga->getUserName() === NULL || self::$obj_twenga->getUserName() === '')) &&
			((self::$obj_twenga->getPassword() === NULL || self::$obj_twenga->getPassword() === '')))
			$output .=  $this->l('B - Installation of Sales Tracking');
		else
			$output .=  $this->l('B - Configuration');

		$output .= '</legend>';

		if(self::$obj_twenga->getHashKey() === NULL || self::$obj_twenga->getHashKey() === '')
		{
			$output .= '<p class="marginBottom">'.$this->l('Enter here your Twenga Ready to Sell hashkey and Login / Pass').'</p>';
		}

		$output .= '
			<div class="moduleTwenga-form">
					<div class="field"><label for="key-twenga"> '.$this->l('Twenga key/HashKey').' <sup>*</sup> :</label> <input id="key-twenga" type="text" size="38" maxlength="32" name="twenga_hashkey" value="'.Tools::safeOutput(self::$obj_twenga->getHashKey()).'"/></div>
					<div class="field"><label for="login-twenga">'.$this->l('Twenga Ready to Sell username (your email)').' <sup>*</sup> :</label> <input id="login-twenga" type="text" size="38" maxlength="64" name="twenga_user_name" value="'.Tools::safeOutput(self::$obj_twenga->getUserName()).'"/></div>
					<div class="field"><label for="pass-twenga">'.$this->l('Twenga Ready to Sell password').' <sup>*</sup> :</label> <input id="pass-twenga" type="password" size="38" maxlength="64" name="twenga_password" value="'.Tools::safeOutput(self::$obj_twenga->getPassword()).'"/>&nbsp; <a href="'.$lost_link.'" target="_blank">'.$this->l('Forgotten your password?').'</a></div>'
					.'<p><input type="submit" value="'.$this->l('Activate my Twenga export').'" name="submitTwengaLogin" class="button"/></p>
				</div>

				<p class="marginBottom">'.$this->l('Your catalogue will be listed in approximately 72 hours.').'</p>
				<p class="marginBottom">'.$this->l('Once your products appear on Twenga, you will receive additional traffic which will by billed using CPC (cost per click).').'<br /> '.$this->l('You can consult').' <a href="'.$tarifs_link.'">'.$this->l('our CPC rates by category.').'</a></p>
				<p class="marginBottom">'.$this->l('You will benefit from detailed traffic & sales supports tools, allowing you total control on your activity on Twenga.').'</p>

						</fieldset>
					</form>
				</div>';

		return $output;
	}

	/**
	 * @return string html testimony
	 */
	private function displayTwengaTestimony()
	{
		global $cookie;
		$isoUser = strtolower(Language::getIsoById(intval($cookie->id_lang)));

		if ($isoUser == 'gb' || $isoUser == 'en')
			$link_banner = 'banner-en';
		elseif ($isoUser == 'es')
			$link_banner = 'banner-es';
		elseif ($isoUser == 'it')
			$link_banner = 'banner-it';
		elseif ($isoUser == 'de')
			$link_banner = 'banner-de';
		else
			$link_banner = 'banner-fr';
		$output = '<div class="banner-twenga"><img src="../modules/'.$this->name.'/img/'.$link_banner.'.png" width="1000" height="150" alt="Awards" /></div>';
		return $output;
	}

	/**
	 * @return bool if user has config twenga ok
	 */
	private function _hasConfigTwenga()
	{
		if (   (self::$obj_twenga->getHashKey() === NULL || self::$obj_twenga->getHashKey() === '')
			|| (self::$obj_twenga->getUserName() === NULL || self::$obj_twenga->getUserName() === '')
			|| (self::$obj_twenga->getPassword() === NULL || self::$obj_twenga->getPassword() === '')){
			return false;
		}else{
			return true;
		}
	}

	/**
	 * @return string html form for activate or disable the Twenga tracking
	 */
	private function displayEnableTracker()
	{
		global $cookie;
		$isoUser = strtolower(Language::getIsoById(intval($cookie->id_lang)));
		if ($isoUser == 'gb' || $isoUser == 'en')
			$site_link = 'https://rts.twenga.co.uk/';
		elseif ($isoUser == 'es')
			$site_link = 'https://rts.twenga.es/';
		elseif ($isoUser == 'it')
			$site_link = 'https://rts.twenga.it/';
		elseif ($isoUser == 'de')
			$site_link = 'https://rts.twenga.de/';
		else
			$site_link = 'https://rts.twenga.fr/';
		$str = '
		<form name="form_twenga_activate" method="post" action="">
			<fieldset class="moduleTwenga-activation">
				<legend><img src="../modules/'.$this->name.'/img/logo-small.png" width="16" height="16" class="middle" />%s</legend>
				<p>'.$this->l('Only one more step to go!').' <br />
				%s</p>
				<p><input type="submit" name="%s" class="button" value="%s" /></p>
				<p>'.$this->l('Your statistics tracking is available from').' <a href="'.$site_link.'" target="_blank">'.$this->l('your Twenga Ready to Sell interface').'</a>.</p>
			</fieldset>
		</form>';

		if ( !$this->isRegisteredInHook('displayCarrierList') && !$this->isRegisteredInHook('displayPayment') && !$this->isRegisteredInHook('Payment') ){
			$str = sprintf($str, $this->l('Activate Tracking'), $this->l('To activate tracking, click on the following button :'), 'submitTwengaActivateTracking', $this->l('Install Twenga sales tracking in just 1 click'));
		}
		return $str;
	}

	/**
	 * @return string html form for activate or disable the Twenga tracking
	 */
	private function displayDisableTracker()
	{
		global $cookie;
		$isoUser = strtolower(Language::getIsoById(intval($cookie->id_lang)));
		if ($isoUser == 'gb' || $isoUser == 'en')
			$site_link = 'https://rts.twenga.co.uk/';
		elseif ($isoUser == 'es')
			$site_link = 'https://rts.twenga.es/';
		elseif ($isoUser == 'it')
			$site_link = 'https://rts.twenga.it/';
		elseif ($isoUser == 'de')
			$site_link = 'https://rts.twenga.de/';
		else
			$site_link = 'https://rts.twenga.fr/';
		$str = '
		<div class="disable-twenga"><form name="form_twenga_activate" method="post" action="">
			<fieldset>
				<legend><img src="../modules/'.$this->name.'/img/logo-small.png" width="16" height="16" class="middle" />%s</legend>
				<p>%s</p>
				<p><input type="submit" name="%s" class="button" value="%s" /></p>
				<p>'.$this->l('Your statistics tracking is available from').' <a target="_blank" href="'.$site_link.'">'.$this->l('your Twenga Ready to Sell interface').'</a>.</p>

			</fieldset>
		</form></div>';

		if ( $this->isRegisteredInHook('displayCarrierList') || $this->isRegisteredInHook('displayPayment') || $this->isRegisteredInHook('Payment') ){
			$str = sprintf($str, $this->l('Your Twenga sales tracking'), $this->l('Your sales tracking allow you to measure conversion and benefit from optimised traffic.'), 'submitTwengaDisableTracking', $this->l('Uninstall my Twenga Sales Tracking'));
		}
		return $str;
	}

	/**
	 * Just set in one method the displaying error message in PrestaShop back-office.
	 */
	private function displayErrors()
	{
		$string = '';
		if (!empty($this->_errors))
			foreach ($this->_errors as $error)
				$string .= $this->displayError($error);
		return $string;
	}

	/**
	 * Used by export.php to build the feed required by Twenga.
	 * See detailed comments in the body of the method
	 * @see Twenga::preparedValues() to see how needed tags for feed are filled
	 */
	public function buildXML()
	{
		// this check if the module is installed and if the site is registered at Twenga
		$bool_site_exists = true;
		if (self::$obj_twenga->getHashkey() === NULL)
		{
			$this->_errors[] = $this->l('The hash key must be set for used Twenga API.');
			$bool_site_exists = false;
		}
		if ($bool_site_exists)
		{
			try
			{
				$bool_site_exists = self::$obj_twenga->siteExist();
			}
			catch (Exception $e)
			{
				$this->_errors[] = $e->getMessage().$this->l('Some parameters missing, or the site doesn\'t exist');
				$bool_site_exists = false;
			}
		}
		if (!$bool_site_exists)
			return $this->displayErrors();

		// Now method build the XML
		echo '<?xml version="1.0" encoding="utf-8"?><catalog>';

		$parameters = Configuration::getMultiple(array('PS_REWRITING_SETTINGS', 'PS_LANG_DEFAULT', 'PS_SHIPPING_FREE_PRICE', 'PS_SHIPPING_HANDLING', 'PS_SHIPPING_METHOD', 'PS_SHIPPING_FREE_WEIGHT', 'PS_COUNTRY_DEFAULT'));
		$lang = (int)$parameters['PS_LANG_DEFAULT'];
		$language = new Language($lang);
		$carrier = new Carrier(Configuration::get('PS_CARRIER_DEFAULT'), $language->id);
		$defaultCountry = new Country(Configuration::get('PS_COUNTRY_DEFAULT'), $language->id);

		$link = new Link();

		$result = Db::getInstance()->ExecuteS('
		SELECT `id_product` FROM `'._DB_PREFIX_.'product` WHERE `active` = 1');

		foreach ($result AS $k => $row)
		{
			$product = new Product((int)$row['id_product']);

			if (Validate::isLoadedObject($product) AND $product->active)
			{
				// Check if product declinations exist.
				$combinations = $this->getCombinations($product, $lang);

				// Set an empty value even no combinations was found to make foreach usable.
				if (empty($combinations))
					$combinations[] = array();

				foreach ($combinations as $combination)
				{
					// prepared values before insert it in node structure.
					// In this way we can structure code with checking method and displaying method for more lisibility.
					$product_values = $this->preparedValues($product, $combination, $lang, $link, $carrier);
					foreach ($product_values as $k => $v)
						$product_values[$k] = str_replace(array('&lt;![CDATA[', ']]&gt;', '&#13;'), array('<![CDATA[', ']]>', ''), $v);

					// create the product node for each products and declinations
					echo '<product>';

					// required Fields
					echo '<product_url><![CDATA['.$product_values['product_url'].']]></product_url>';
					echo '<designation><![CDATA['.$product_values['designation'].']]></designation>';
					echo '<price><![CDATA['.$product_values['price'].']]></price>';
					echo '<category><![CDATA['.$product_values['category'].']]></category>';
					echo '<image_url><![CDATA['.$product_values['image_url'].']]></image_url>';
					echo '<description><![CDATA['.$product_values['description'].']]></description>';
					echo '<brand><![CDATA['.$product_values['brand'].']]></brand>';

					// optionnals fields
					echo '<merchant_ref><![CDATA['.$product_values['merchant_ref'].']]></merchant_ref>';
					echo '<merchant_id><![CDATA['.$product_values['merchant_id'].']]></merchant_id>';
					echo '<manufacturer_id><![CDATA['.$product_values['manufacturer_id'].']]></manufacturer_id>';
					echo '<shipping_cost><![CDATA['.$product_values['shipping_cost'].']]></shipping_cost>';
					echo '<in_stock><![CDATA['.$product_values['in_stock'].']]></in_stock>';
					echo '<stock_detail><![CDATA['.$product_values['stock_detail'].']]></stock_detail>';
					echo '<condition><![CDATA['.$product_values['condition'].']]></condition>';
					echo '<upc_ean><![CDATA['.$product_values['upc_ean'].']]></upc_ean>';
					echo '<product_type><![CDATA['.$product_values['product_type'].']]></product_type>';
					echo '<isbn><![CDATA['.$product_values['isbn'].']]></isbn>';
					echo '<eco_tax><![CDATA['.$product_values['eco_tax'].']]></eco_tax>';

					echo '</product>';
				}
			}
		}
		echo '</catalog>';
		return true;
	}
	/**
	 * @param Product $product to get the product properties
	 * @param array $combination to get particular properties from a declination
	 * @param int $lang id lang to take all text in good language
	 * @param Link $link to set the link of the product and its images.
	 * @param Carrier $carrier not used now, but usable for next version, needed for calculate the shipping cost,
	 * 		  But for now it's not sure enough.
	 * @return array with good value for the XML.
	 */
	private function preparedValues(Product $product, $combination, $lang, Link $link, Carrier $carrier)
	{
		$arr_return = array();
		$str_features = array();
		$model = array();
		$version = str_replace('.', '', _PS_VERSION_);

		// To build description and model tags.
		if (isset($combination['attributes']))
			foreach ($combination['attributes'] as $attribut)
			{
				$str_features[] = $attribut['group_name'].' : '.$attribut['name'];
				$model[] = $attribut['name'];
			}
		if (isset($combination['weight']) && (int)$combination['weight'] !== 0)
			$str_features[] = 'weight : '.$combination['weight'];
		elseif ($product->weight !== 0)
			$str_features[] = 'weight : '.$product->weight;

		$features = $product->getFrontFeatures($lang);
		foreach ($features as $feature)
			$str_features[] = $feature['name'].' : '.$feature['value'];

		// Category tag
		$category = new Category((int)$product->id_category_default, $lang);
		$category_path = ((isset($category->id) AND $category->id) ? Tools::getFullPath((int)($category->id), $product->name[$lang]) : Tools::getFullPath((int)($product->id_category_default), $product->name[$lang]));
		$category_path = (Configuration::get('PS_NAVIGATION_PIPE') != false && Configuration::get('PS_NAVIGATION_PIPE') !== '>' ) ? str_replace(Configuration::get('PS_NAVIGATION_PIPE'), '>', $category_path) : $category_path;
		// image tag
		$id_image = (isset($combination['id_image'])) ? $combination['id_image'] : 0;
		if ($id_image === 0 || $id_image < 0)
		{
			$image = $product->getCover((int)$product->id);
			$id_image = $image['id_image'];
		}

		$quantity = Product::getQuantity($product->id, (isset($combination['id_combination']) ? $combination['id_combination'] : NULL));

		$condition = '';
		 if (strlen((string)$version) < 2)
			 $version = (string)$version.'0';
		if ((int)substr($version, 0, 2) >= 14)
			$condition = (($product->condition === 'new') ? 0 : 1);

		$price = $product->getPrice(true, (isset($combination['id_combination']) ? $combination['id_combination'] : NULL), 2);
		$upc_ean = strlen((string)$product->ean13) == 13 ? $product->ean13 : '';

		$arr_return['product_url'] = $link->getProductLink((int)$product->id, $product->link_rewrite[$lang], $product->ean13, $lang);
		$arr_return['designation'] = Tools::htmlentitiesUTF8($product->name[$lang].' '.Manufacturer::getNameById($product->id_manufacturer).' '.implode(' ', $model));
		$arr_return['price'] = $price;
		$arr_return['category'] = Tools::htmlentitiesUTF8(strip_tags($category_path));

		if (substr(_PS_VERSION_, 0, 3) == '1.3')
		{
			if (!Configuration::get('PS_SHOP_DOMAIN'))
				Configuration::updateValue('PS_SHOP_DOMAIN', $_SERVER['HTTP_HOST']);
			$prefix =  'http://'.Configuration::get('PS_SHOP_DOMAIN').'/';
			$arr_return['image_url'] = $prefix.$link->getImageLink('', $product->id.'-'.$id_image, 'large');
		}else if(substr(_PS_VERSION_, 0, 3) == '1.4'){
			$arr_return['image_url'] = $prefix.$link->getImageLink('', $product->id.'-'.$id_image, 'large');
		}else
			$arr_return['image_url'] = $link->getImageLink($product->link_rewrite[$lang], $product->id.'-'.$id_image, 'large_default');


		// Must description added since Twenga-module v1.1
		$arr_return['description'] = is_array($product->description) ? strip_tags($product->description[$lang]) : strip_tags($product->description);
		$arr_return['description'] = trim($arr_return['description'].' '.strip_tags(implode(', ', $str_features)));
		$arr_return['description'] = Tools::htmlentitiesUTF8($arr_return['description']);
		$arr_return['brand'] = Manufacturer::getNameById($product->id_manufacturer);
		$arr_return['merchant_ref'] = $product->reference;
		$arr_return['merchant_id'] = $product->id;
		$arr_return['manufacturer_id'] = $product->id_manufacturer;
		$arr_return['shipping_cost'] = 'NC';
		$arr_return['in_stock'] = $quantity > 0 ? 'Y' : 'N';
		$arr_return['stock_detail'] = $quantity;
		$arr_return['condition'] = $condition;
		$arr_return['upc_ean'] = $upc_ean;
		$arr_return['eco_tax'] = $product->ecotax;

		// for prestashop 1.4 and previous version these fields are not managed.
		// So default values are set.
		$arr_return['product_type'] = '1';
		$arr_return['isbn'] = '';

		return $arr_return;
	}
	/**
	 * @param Product $product
	 * @param int $lang id of a language
	 * @return array of a product declinations.
	 */
	private function getCombinations(Product $product, $lang)
	{
		$attributesGroups = $product->getAttributesGroups((int)$lang);
		$combinations = array();
		if ($attributesGroups && is_array($attributesGroups))
		{
			$combinationImages = $product->getCombinationImages((int)$lang);
			foreach ($attributesGroups AS $k => $row)
			{
				$combinations[$row['id_product_attribute']]['id_combination'] = $row['id_product_attribute'];
				$combinations[$row['id_product_attribute']]['attributes'][$row['id_attribute_group']] = array('name'=>$row['attribute_name'], 'group_name'=>$row['public_group_name'], 'id_attribute'=>(int)$row['id_attribute']);
				$combinations[$row['id_product_attribute']]['price'] = (float)($row['price']);
				$combinations[$row['id_product_attribute']]['ecotax'] = (float)($row['ecotax']);
				$combinations[$row['id_product_attribute']]['weight'] = (float)($row['weight']);
				$combinations[$row['id_product_attribute']]['quantity'] = (int)($row['quantity']);
				$combinations[$row['id_product_attribute']]['reference'] = $row['reference'];
				if (isset($row['unit_price_impact']))
					$combinations[$row['id_product_attribute']]['unit_impact'] = $row['unit_price_impact'];
				$combinations[$row['id_product_attribute']]['id_image'] = isset($combinationImages[$row['id_product_attribute']][0]['id_image']) ? $combinationImages[$row['id_product_attribute']][0]['id_image'] : -1;
			}
		}
		return $combinations;
	}
}
