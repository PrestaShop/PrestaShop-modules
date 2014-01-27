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
*  @version  Release: $Revision: 9702 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if ( !defined( '_PS_VERSION_' ) )
  exit;

 class Itembase extends Module
 {
	/**
	 * Construct object
	 */
	public function __construct()
	{
		$this->name = 'itembase';
		$this->tab = 'others';
		include_once(rtrim(_PS_MODULE_DIR_, '/').'/itembase/plugindata.php');
		$this->version = PS_ITEMBASE_PLUGIN_VERSION;
		$this->author = 'itembase';
		$this->limited_countries = array('ch','de','gb');
		$this->need_instance = 0;

		parent::__construct();

		$this->displayName = $this->l('itembase');
		$this->description = $this->l('itembase - Personal Shopping Manager and Inventory (SEO links, Brand Booster, Traffic Source, Affiliate Channel)');
		
		if (self::isInstalled($this->name)){
			$warnings = array();
			if (!extension_loaded('openssl'))
				$warnings[] = $this->l('openssl php extension is missing');
			if (!ini_get('allow_url_fopen'))
				$warnings[] = $this->l('allow_url_fopen option must be enabled');
			if (count($warnings))
				$this->warning .= implode(', ', $warnings);
		}
	}

	/**
	 * Install module
	 */
	public function install()
	{
		if (!(parent::install() && $this->registerHook('orderConfirmation')))
			return false;
		Configuration::updateValue('PS_ITEMBASE_DEBUG', 1, false, 0, 0);
		Configuration::updateValue('PS_ITEMBASE_TOP', 1, false, 0, 0);
		return true;
	}
	
	/**
	 * Uninstall module
	 */
	public function uninstall()
	{
		parent::uninstall();
	}
	
	/**
	 * Get content
	 */
	public function getContent()
	{
		global $cookie;
		include_once(rtrim(_PS_MODULE_DIR_, '/').'/itembase/plugindata.php');
		$language = Language::getLanguage((int)$cookie->id_lang);
		$multishop = class_exists('Shop', false) && (method_exists('Shop', 'getContextID') || method_exists('Shop', 'getContextShopID'));
		$html = '<h2>'.$this->displayName.'</h2>';

		// configuration data gathering and saving
		$configurationHtml = $this->getConfigurationOutput($multishop);
		
		// registration data gathering and saving
		$user = $this->getRegistrationDataUser();
		$shops = $this->getRegistrationDataShops($multishop);
		if (Tools::getValue('itembaseRegistration')) {
			$responseData = $this->jsonDecode(base64_decode(Tools::getValue('itembaseRegistration')));
			if (isset($responseData['errors'])) {
				$html .= $this->displayError($this->l('Registration error.').'<br />'.str_replace('[br]', '<br />', Tools::safeOutput(implode('[br]', $responseData['errors']))));
				$user['email'] = $responseData['user']['email'];
				$user['firstname'] = $responseData['user']['firstname'];
				$user['lastname'] = $responseData['user']['lastname'];
			} else {
				$this->saveConfiguration($responseData);
				$html .= $this->displayConfirmation($this->l('Registration completed.').'<br />'.Tools::safeOutput($responseData['success']).' <a href="'.preg_replace('/(\&|\?|\&amp;)itembaseRegistration=[a-z0-9]*/i', '', Tools::safeOutput($_SERVER['REQUEST_URI'])).'">'.$this->l('Click here to continue.').'</a>');
				return $html;
			}
		}
		
		if ($shops) {
			// registration data sending
			if (Tools::isSubmit('submitItembaseRegistration')) {
				$data = array(
					'user' => $user = Tools::getValue('user'),
					'shops' => $shops = Tools::getValue('shops'),
					'shop_software' => Tools::getValue('shop_software'),
					'return' => 'json',
					'lang' => $language['iso_code'],
				);
				$header[] = 'Authorization: OAuth Content-Type: application/x-www-form-urlencoded';
				$ibCurl = curl_init();
				curl_setopt($ibCurl, CURLOPT_HEADER, false);
				curl_setopt($ibCurl, CURLOPT_HTTPHEADER, $header);
				curl_setopt($ibCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($ibCurl, CURLOPT_SSL_VERIFYHOST, 2);
				curl_setopt($ibCurl, CURLOPT_URL, PS_ITEMBASE_SERVER_HOST.'/api/register_retailer');
				curl_setopt($ibCurl, CURLOPT_POST, true);
				curl_setopt($ibCurl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ibCurl, CURLOPT_POSTFIELDS, http_build_query($data));
				$jsonResponse = curl_exec($ibCurl);
				if ($jsonResponse === FALSE)
					$html .= $this->displayError($this->l('Curl error.').'<br />'.curl_error($ibCurl));
				else {
					$responseData = $this->jsonDecode($jsonResponse);
					if (isset($responseData['errors']))
						$html .= $this->displayError($this->l('Registration error.').'<br />'.str_replace('[br]', '<br />', Tools::safeOutput(implode('[br]', $responseData['errors']))));
					else {
						$this->saveConfiguration($responseData);
						$html .= $this->displayConfirmation($this->l('Registration completed.').'<br />'.Tools::safeOutput($responseData['success']).' <a href="'.preg_replace('/(\&|\?|\&amp;)itembaseRegistration=[a-z0-9]*/i', '', Tools::safeOutput($_SERVER['REQUEST_URI'])).'">'.$this->l('Click here to continue.').'</a>');
						return $html;
					}
				}
				curl_close($ibCurl);
			}
			$html .= $this->getRegistrationOutput($multishop, $user, $shops, $language);
		}
		
		return $html.$configurationHtml;
	}
	
	/**
	 * Get user data
	 * 
	 * @return mixed
	 */
	private function getRegistrationDataUser()
	{
		global $cookie;
		$employee = new Employee(intval($cookie->id_employee));
		return array(
			'email' => Configuration::get('PS_SHOP_EMAIL'),
			'firstname' => $employee->firstname,
			'lastname' => $employee->lastname,
			'street' => Configuration::get('PS_SHOP_ADDR1').(Configuration::get('PS_SHOP_ADDR1') ? ' '.Configuration::get('PS_SHOP_ADDR1') : ''),
			'zip' => Configuration::get('PS_SHOP_CODE'),
			'town' => Configuration::get('PS_SHOP_CITY'),
			'state' => Configuration::get('PS_SHOP_STATE'),
			'country' => Configuration::get('PS_SHOP_COUNTRY'),
			'telephone' => Configuration::get('PS_SHOP_PHONE'),
			'fax' => Configuration::get('PS_SHOP_FAX'),
		);
	}
	
	/**
	 * Get shops data
	 * 
	 * @param boolean $multishop
	 * @return mixed
	 */
	private function getRegistrationDataShops($multishop)
	{
		$shops = array();
		if ($multishop) {
			foreach (Shop::getShops(false) as $shop) {
				if (Configuration::get('PS_ITEMBASE_APIKEY', NULL, NULL, $shop['id_shop']) === false) {
					$shops[] = array(
						'shop_id' => $shop['id_shop'],
						'shop_name' => $shop['name'],
						'shop_url' => 'http://'.$shop['domain'].$shop['uri'],
						'register' => 1,
						'street' => Configuration::get('PS_SHOP_ADDR1', null, null, $shop['id_shop']).(Configuration::get('PS_SHOP_ADDR1', null, null, $shop['id_shop']) ? ' '.Configuration::get('PS_SHOP_ADDR1', null, null, $shop['id_shop']) : ''),
						'zip' => Configuration::get('PS_SHOP_CODE', null, null, $shop['id_shop']),
						'town' => Configuration::get('PS_SHOP_CITY', null, null, $shop['id_shop']),
						'state' => Configuration::get('PS_SHOP_STATE', null, null, $shop['id_shop']),
						'country' => Configuration::get('PS_SHOP_COUNTRY', null, null, $shop['id_shop']),
						'telephone' => Configuration::get('PS_SHOP_PHONE', null, null, $shop['id_shop']),
						'fax' => Configuration::get('PS_SHOP_FAX', null, null, $shop['id_shop']),
						'email' => Configuration::get('PS_SHOP_EMAIL', null, null, $shop['id_shop']),
					);
				}
			}
		} else {
			if (Configuration::get('PS_ITEMBASE_APIKEY') === false) {
				$shops[] = array(
					'shop_id' => 0,
					'shop_name' => Configuration::get('PS_SHOP_NAME'),
					'shop_url' => 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__,
					'register' => 1,
					'street' => Configuration::get('PS_SHOP_ADDR1').(Configuration::get('PS_SHOP_ADDR1') ? ' '.Configuration::get('PS_SHOP_ADDR1') : ''),
					'zip' => Configuration::get('PS_SHOP_CODE'),
					'town' => Configuration::get('PS_SHOP_CITY'),
					'state' => Configuration::get('PS_SHOP_STATE'),
					'country' => Configuration::get('PS_SHOP_COUNTRY'),
					'telephone' => Configuration::get('PS_SHOP_PHONE'),
					'fax' => Configuration::get('PS_SHOP_FAX'),
					'email' => Configuration::get('PS_SHOP_EMAIL'),
				);
			}
		}
		return $shops;
	}
	
	/**
	 * Get registration output
	 * 
	 * @param boolean $multishop
	 * @param mixed $user
	 * @param mixed $shops
	 * @param mixed $language
	 * @return string
	 */
	private function getRegistrationOutput($multishop, $user, $shops, $language)
	{
		$registrationHtml = '
			<form action="'.(extension_loaded('curl') ? preg_replace('/(\&|\?)itembaseRegistration=[a-z0-9]*/i', '', $_SERVER['REQUEST_URI']) : PS_ITEMBASE_SERVER_HOST.'/api/register_retailer').'" method="post">';
		if (Configuration::get('ITEMBASE_EMAIL') === false) {
			$registrationHtml .= '
				<div class="itembase-reg-left">
					<label>'.$this->l('Email').'</label>
					<input type="text" name="user[email]" value="'.Tools::safeOutput($user['email']).'" />
					<label>'.$this->l('First Name').'</label>
					<input type="text" name="user[firstname]" value="'.Tools::safeOutput($user['firstname']).'" />
					<label>'.$this->l('Last Name').'</label>
					<input type="text" name="user[lastname]" value="'.Tools::safeOutput($user['lastname']).'" />
					<input type="hidden" name="user[street]" value="'.Tools::safeOutput($user['street']).'" />
					<input type="hidden" name="user[zip]" value="'.Tools::safeOutput($user['zip']).'" />
					<input type="hidden" name="user[town]" value="'.Tools::safeOutput($user['town']).'" />
					<input type="hidden" name="user[state]" value="'.Tools::safeOutput($user['state']).'" />
					<input type="hidden" name="user[country]" value="'.Tools::safeOutput($user['country']).'" />
					<input type="hidden" name="user[telephone]" value="'.Tools::safeOutput($user['telephone']).'" />
					<input type="hidden" name="user[fax]" value="'.Tools::safeOutput($user['fax']).'" />
				</div>';
		} else {
			$registrationHtml .= '
				<div class="itembase-reg-left">
					<label>'.$this->l('Email').'</label>
					<input type="text" value="'.Tools::safeOutput(Configuration::get('ITEMBASE_EMAIL')).'" disabled="disabled" />
					<input type="hidden" name="user[email]" value="'.Tools::safeOutput(Configuration::get('ITEMBASE_EMAIL')).'" />
				</div>';
		}
		$registrationHtml .= '
				<div class="itembase-reg-right">
					<label class="itembase-form-label-reg">'.$this->l('Register shop').'</label>';
		foreach ($shops as $shop) {
			$registrationHtml .= '
					<div class="itembase-shop">
						<input class="input-check" type="checkbox" name="shops['.$shop['shop_id'].'][register]" '.(isset($shop['register']) && $shop['register'] ? ' checked="checked"' : '').' /><span class="itembase-shopname">'.$shop['shop_name'].'</span>
						<input type="hidden" name="shops['.$shop['shop_id'].'][shop_id]" value="'.Tools::safeOutput($shop['shop_id']).'" />
						<input type="hidden" name="shops['.$shop['shop_id'].'][shop_name]" value="'.Tools::safeOutput($shop['shop_name']).'" />
						<input type="hidden" name="shops['.$shop['shop_id'].'][shop_url]" value="'.Tools::safeOutput($shop['shop_url']).'" />
						<input type="hidden" name="shops['.$shop['shop_id'].'][street]" value="'.Tools::safeOutput($shop['street']).'" />
						<input type="hidden" name="shops['.$shop['shop_id'].'][zip]" value="'.Tools::safeOutput($shop['zip']).'" />
						<input type="hidden" name="shops['.$shop['shop_id'].'][town]" value="'.Tools::safeOutput($shop['town']).'" />
						<input type="hidden" name="shops['.$shop['shop_id'].'][state]" value="'.Tools::safeOutput($shop['state']).'" />
						<input type="hidden" name="shops['.$shop['shop_id'].'][country]" value="'.Tools::safeOutput($shop['country']).'" />
						<input type="hidden" name="shops['.$shop['shop_id'].'][telephone]" value="'.Tools::safeOutput($shop['telephone']).'" />
						<input type="hidden" name="shops['.$shop['shop_id'].'][fax]" value="'.Tools::safeOutput($shop['fax']).'" />
					</div>';
		}
		$registrationHtml .= '
					<input type="hidden" name="shop_software" value="UHJlc3Rhc2hvcDFfNA" />
					<input type="hidden" name="lang" value="'.Tools::safeOutput($language['iso_code']).'" />
				</div>
				<div class="itembase-reg-final">
					<input type="checkbox" id="confirmItembaseTAC" /><span class="itembase-shopname">'.$this->l('I accept the itembase <a href=\'http://partners.itembase.com/docs/tac.pdf\' target=\'_blank\' style=\'text-decoration:underline;\'>Terms</a>').'</span>
					<input class="itembase-button-green" type="submit" name="submitItembaseRegistration" value="'.$this->l('Register').'" onclick="if(!document.getElementById(\'confirmItembaseTAC\').checked){ alert(\''.$this->l('Accept itembase Terms first.').'\'); return false; }" />
				</div>
			</form>';
		return str_replace('[form]', $registrationHtml, file_get_contents(PS_ITEMBASE_SERVER_EMBED.'/embed/registration?shop_software=UHJlc3Rhc2hvcDFfNA&lang='.$language['iso_code'], false, stream_context_create(array('http' => array('ignore_errors' => true)))));
	}

	/**
	 * Get configuration output
	 * 
	 * @param boolean $multishop
	 * @return string
	 */
	private function getConfigurationOutput($multishop)
	{
		$api_key = Configuration::get('PS_ITEMBASE_APIKEY');
		$secret = Configuration::get('PS_ITEMBASE_SECRET');
		$debug = Configuration::get('PS_ITEMBASE_DEBUG');
		$top = Configuration::get('PS_ITEMBASE_TOP');
		$configurationHtml = '';
		if (Tools::isSubmit('submitItembaseSettings')) {
			$api_key = trim(Tools::getValue('api_key'));
			$secret = trim(Tools::getValue('secret'));
			$debug = Tools::getValue('debug');
			$top = Tools::getValue('top');
			$errors = '';
			if (!$this->isValidSetting($api_key))
				$errors .= $this->displayError($this->l('Please enter the API key.'));
			else {
				$api_key_exists = false;
				if ($multishop) {
					foreach (Shop::getShops(false) as $shop) {
						if ($shop['id_shop'] == (method_exists('Shop', 'getContextID') ? Shop::getContextID() : (method_exists('Shop', 'getContextShopID') ? Shop::getContextShopID() : '')))
							continue;
						if (Configuration::get('PS_ITEMBASE_APIKEY', NULL, NULL, $shop['id_shop']) == $api_key) {
							$api_key_exists = true;
							$errors .= $this->displayError($this->l('This API Key is allready used for other shop.'));
						}
					}
				}
			}
			if (!$this->isValidSetting($secret))
				$errors .= $this->displayError($this->l('Please enter the Secret.'));
			if ($errors)
				$configurationHtml .= $errors;
			else {
				Configuration::updateValue('PS_ITEMBASE_APIKEY', $api_key);
				Configuration::updateValue('PS_ITEMBASE_SECRET', $secret);
				Configuration::updateValue('PS_ITEMBASE_DEBUG', $debug);
				Configuration::updateValue('PS_ITEMBASE_TOP', $top);
				$configurationHtml .= $this->displayConfirmation($this->l('Saving settings succeeded.'));
			}
		}
		$configurationHtml .= '
		<form action="'.preg_replace('/(\&|\?)itembaseRegistration=[a-z0-9]*/i', '', $_SERVER['REQUEST_URI']).'" method="post">
			<fieldset>
				<legend>'.$this->l('Settings').'</legend>'.(
				$multishop && ((method_exists('Shop', 'getContextID') && Shop::getContextID()) || (method_exists('Shop', 'getContextShopID') && Shop::getContextShopID())) || !$multishop ?
				'<label>'.$this->l('API key').'</label>
				<div class="margin-form">
					<input type="text" name="api_key" value="'.Tools::safeOutput($api_key).'" />
				</div>
				<label>'.$this->l('Secret').'</label>
				<div class="margin-form">
					<input type="text" name="secret" value="'.Tools::safeOutput($secret).'" />
				</div>
				<label>'.$this->l('Enable Debug mode').'</label>
				<div class="margin-form">
					<input type="text" name="debug" value="'.Tools::safeOutput($debug).'" />
				</div>
				<label class="clear">'.$this->l('Optimized Placement').'</label>
				<div class="margin-form">
					<input type="checkbox" name="top" '.($top ? 'checked="checked"' : '').'" />
				</div>
				<div class="clear center">
				<p>&nbsp;</p>
				<input class="button" type="submit" name="submitItembaseSettings" value="'.$this->l('Save').'" />
				</div>'
				:
				'<div class="clear center">'.$this->l('Please select your shop before changing settings.').'</div>'
				).'
			</fieldset>
		</form>';
		return $configurationHtml;
	}
	
	/**
	 * Execute hook
	 * 
	 * @param mixed $params
	 */
	public function hookOrderConfirmation($params)
	{
		global $smarty, $cookie, $link;
		
		if (!$this->active)
			return;
		
		// seting error handler
		eval('function itembaseErrorHandler($errno, $errstr, $errfile, $errline) {
'.((bool)Configuration::get('PS_ITEMBASE_DEBUG') ? 'echo "
<!--ITEMBASE
".print_r(array($errno, $errstr, $errfile, $errline), true)."ITEMBASE-->
";' : '').'
	return true;
}');
		set_error_handler('itembaseErrorHandler', E_ALL);
		
		try {
			include_once(rtrim(_PS_MODULE_DIR_, '/').'/itembase/plugindata.php');
			include_once(rtrim(_PS_MODULE_DIR_, '/').'/itembase/oauth.php');
			// geting access token
			$responseArray = $this->jsonDecode(authenticateClient(Configuration::get('PS_ITEMBASE_APIKEY'), Configuration::get('PS_ITEMBASE_SECRET')));
			if (!isset($responseArray['access_token']))
				itembaseErrorHandler(0, 'no access_token for '.Tools::safeOutput(Configuration::get('PS_ITEMBASE_APIKEY')).' '.substr(Tools::safeOutput(Configuration::get('PS_ITEMBASE_SECRET')), 0, 4).'... '.PS_ITEMBASE_SERVER_OAUTH.' '.print_r($responseArray, true), __FILE__, __LINE__ - 1);
			// order data gathering
			$order = new Order($params['objOrder']->id, NULL);
			$currency = Currency::getCurrency((int)$order->id_currency);
			$carrier = new Carrier((int)$order->id_carrier);
			$language = Language::getLanguage((int)$cookie->id_lang);
			$customer = new Customer((int)$order->id_customer);
			$address = $customer->getAddresses($cookie->id_lang);
			if (is_object($address))
				$address = (array) $address;
			if (isset($address['0']))
				$address = $address['0'];
			// products data gathering
			$allProducts = array();
			foreach ($order->getProductsDetail() as $order_detail) {
				$product_id = $order_detail['product_id'];
				$product = new Product($product_id, true, null);
				$cover = Product::getCover($product_id);
				$product_img = $link->getImageLink($product->link_rewrite, $product_id.'-'.$cover['id_image']);
				if (strpos($product_img, 'http') !== 0)
					$product_img = Tools::getHttpHost(true).$product_img;
				$category = new Category($product->id_category_default);
				
				$allProducts [] = array(
					'id' => $order_detail['product_id'],
					'category' => $category->name,
					'name' => $product->name,
					'quantity' => $order_detail['product_quantity'],
					'price' => $product->getPrice(true, NULL, 2),
					'ean' => $product->ean13,
					'isbn' => '',
					'asin' => '',
					'description' => $product->description_short,
					'pic_thumb' => $product_img,
					'pic_medium' => $product_img,
					'pic_large' => $product_img,
					'url' => $product->getLink(),
					'presta_lang_id' => $language['id_lang'],/*to select translated texts*/
				);
			}

			$dataForItembase = array(
				'access_token' => $responseArray['access_token'],
				'email' => $customer->email,
				'firstname' => $customer->firstname,
				'lastname' => $customer->lastname,
				'street' => $address['address1'].($address['address2'] ? ' '.$address['address2'] : ''),
				'zip' => $address['postcode'],
				'city' => $address['city'],
				'country' => $address['country'],
				'phone' => $address['phone'],
				'lang' => $language['iso_code'],
				'purchase_date' => $order->date_add,
				'currency' => $currency['iso_code'],
				'total' => $order->total_products_wt,
				'order_number' => $order->id,
				'customer_id' => $order->id_customer,
				'invoice_number' => $order->invoice_number,
				'shipping_cost' => $order->total_shipping,
				'carrier' => $carrier->name,
				'payment_option' => $order->payment,
				'is_opt_in' => $customer->newsletter,
				'shop_name' => class_exists('Context', false) ? Context::getContext()->shop->name : Configuration::get('PS_SHOP_NAME'),
				'products' => $allProducts,
			);
			// encoding data
			utf8EncodeRecursive($dataForItembase);
			
			$smarty->assign('ibdata', $dataForItembase);
			$smarty->assign('ibdatajson', $this->jsonEncode($dataForItembase));
			$smarty->assign('ibembedserver', PS_ITEMBASE_SERVER_EMBED);
			$smarty->assign('ibhostserver', PS_ITEMBASE_SERVER_HOST);
			$smarty->assign('ibpluginversion', PS_ITEMBASE_PLUGIN_VERSION);
			$smarty->assign('ibtop', Configuration::get('PS_ITEMBASE_TOP'));
		} catch(Exception $e) {
			itembaseErrorHandler($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
		}
		// restoring error handler
		restore_error_handler();

		return $this->display(__FILE__, 'views/templates/front/checkout_plugin.tpl');
	}

	/**
	 * Validate value
	 * 
	 * @param string $setting
	 * @return boolean
	 */
	private function isValidSetting($setting)
	{
		return !empty($setting) && $setting != null && strlen($setting) > 5;
	}

	/**
	 * Save configuration
	 * 
	 * @param mixed $responseData
	 */
	private function saveConfiguration($responseData)
	{
		if (Configuration::get('ITEMBASE_EMAIL') === false)
			Configuration::updateValue('ITEMBASE_EMAIL', $responseData['user']['email'], false, 0, 0);
		foreach ($responseData['shops'] as $shop) {
			if (!$shop['api_key']) {
				Configuration::updateValue('PS_ITEMBASE_APIKEY', ' ', false, null, $shop['shop_id']);
				Configuration::updateValue('PS_ITEMBASE_SECRET', ' ', false, null, $shop['shop_id']);
			}
			Configuration::updateValue('PS_ITEMBASE_APIKEY', $shop['api_key'], false, null, $shop['shop_id']);
			Configuration::updateValue('PS_ITEMBASE_SECRET', $shop['secret'], false, null, $shop['shop_id']);
		}
	}

	/**
	 * JSON decode
	 *
	 * @param string $data
	 * @return mixed
	 */
	private function jsonDecode($data)
	{
		if (function_exists('json_decode'))
			$result = json_decode($data, true);
		else {
			include_once(rtrim(_PS_MODULE_DIR_, '/').'/itembase/json.php');
			$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
			$result = $json->decode($data);
		}
		return $result;
	}

	/**
	 * JSON encode
	 * 
	 * @param mixed $data
	 * @return string
	 */
	private function jsonEncode($data)
	{
		if (function_exists('json_encode')) {
			$result = json_encode($data);
			if (is_callable('json_last_error'))
				if (json_last_error() != JSON_ERROR_NONE)
					itembaseErrorHandler(0, 'json_encode error '.json_last_error(), __FILE__, __LINE__ - 1);
		} else {
			include_once(rtrim(_PS_MODULE_DIR_, '/').'/itembase/json.php');
			$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
			$result = $json->encode($data);
		}
		return $result;
	}
}
