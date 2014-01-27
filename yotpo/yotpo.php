<?php

if (!defined('_PS_VERSION_'))
	exit;

class Yotpo extends Module
{
	const PAST_ORDERS_DAYS_BACK = 90;
	const PAST_ORDERS_LIMIT = 10000;
	const BULK_SIZE = 1000;	
	private $_html = '';
	private $_httpClient = null;
	private $_yotpo_module_path = '';
	private static $_MAP_STATUS = null;

	private $_required_files = array('/YotpoHttpClient.php', '/YotpoSnippetCache.php'); 
	
	private $_is_smarty_product_vars_assigned = false;
	
	public function __construct()
	{
		$version_mask = explode('.', _PS_VERSION_, 3);
		$version_test = $version_mask[0] > 0 && $version_mask[1] > 4;
		$this->name = 'yotpo';
		$this->tab = $version_test ? 'advertising_marketing' : 'Reviews';
		$this->version = '1.3.5';
		if ($version_test)
			$this->author = 'Yotpo';
		$this->need_instance = 1;

		parent::__construct();
		 
		$this->displayName = $this->l('Yotpo - Social Reviews and Testimonials');
		$this->description = $this->l('The #1 reviews add-on for SMBs. Generate beautiful, trusted reviews for your shop.');
		$this->_yotpo_module_path = _PS_MODULE_DIR_.$this->name;

		if (!Configuration::get('yotpo_app_key'))
			$this->warning = $this->l('Set your API key in order the Yotpo module to work correctly');	

		if (!defined('_PS_BASE_URL_'))
			define('_PS_BASE_URL_', 'http://'.(isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST']));
		if(file_exists($this->_yotpo_module_path . '/YotpoSnippetCache.php')) {
			include_once($this->_yotpo_module_path.'/YotpoSnippetCache.php');	
		}			
	}

	public static function getAcceptedMapStatuses()
	{
		if (is_null(self::$_MAP_STATUS))
		{
			self::$_MAP_STATUS = array();
			$statuses = array('PS_OS_WS_PAYMENT', 'PS_OS_PAYMENT', 'PS_OS_DELIVERED', 'PS_OS_SHIPPING');
			foreach ($statuses as $status)
			{
				if (defined($status))
					self::$_MAP_STATUS[] = (int)Configuration::get($status);
				elseif (defined('_'.$status.'_')) 
					self::$_MAP_STATUS[] = constant('_'.$status.'_');
			}
		}
		return self::$_MAP_STATUS;
	}

	public function install()
	{
		if (!function_exists('curl_init'))
			$this->setError($this->l('Yotpo needs the PHP Curl extension, please ask your hosting provider to enable it prior to install this module.'));

		$version_mask = explode('.', _PS_VERSION_, 3);
		if($version_mask[0] == 0 || $version_mask[1] < 3)
			$this->setError($this->l('Minimum version required for Yotpo module is Prestashop 1.3'));

		foreach ($this->_required_files as $file)
			if(!file_exists($this->_yotpo_module_path .$file))
				$this->setError($this->l('Can\'t include file '.$this->_yotpo_module_path .$file));

		if ((is_array($this->_errors) && count($this->_errors) > 0) || parent::install() == false 	||
			!$this->registerHook('productfooter') 	|| !$this->registerHook('postUpdateOrderStatus')||
			!$this->registerHook('extraLeft') 		|| !$this->registerHook('extraRight') 			||
			!$this->registerHook('productTab') 		|| !$this->registerHook('productTabContent') 	|| 
			!$this->registerHook('header')			|| !$this->registerHook('orderConfirmation')	|| !YotpoSnippetCache::createDB()) 
			return false;

		/* Default language: English; Default widget location: Product page Footer; Default widget tab name: "Reviews" 
		 * Default bottom line location: product page left column Default bottom line enabled : true*/	
		
		Configuration::updateValue('yotpo_language', 'en', false);
		Configuration::updateValue('yotpo_widget_location', 'footer', false);
		Configuration::updateValue('yotpo_widget_tab_name', 'Reviews', false);
		Configuration::updateValue('yotpo_bottom_line_enabled', 1, false);
		Configuration::updateValue('yotpo_bottom_line_location', 'left_column', false);
		Configuration::updateValue('yotpo_widget_language_code', 'en', false);
		Configuration::updateValue('yotpo_language_as_site', 0, false);
		Configuration::updateValue('yotpo_rich_snippets', 1, false);
		
		Configuration::updateValue('yotpo_rich_snippet_cache_created', 1, true);
		return true;
	}

	public function hookheader($params)
	{
		global $smarty;
		$smarty->assign(array('yotpoAppkey' => Configuration::get('yotpo_app_key'), 
							  'yotpoDomain' => $this->getShopDomain(),
							  'yotpoLanguage' => $this->getLanguage()));
		
		if(isset($this->context) && isset($this->context->controller) && method_exists($this->context->controller, 'addJS')) {
			$this->context->controller->addJS(($this->_path).'headerScript.js');
		}
		else {
			return '<script type="text/javascript" src="'.$this->_path.'headerScript.js"></script>';				
		}
	}

	public function hookproductfooter($params)
	{
		$widgetLocation = Configuration::get('yotpo_widget_location');
		return ($widgetLocation == 'footer' || $widgetLocation == 'other') ? $this->showWidget($params['product']) : null;
	}

	public function hookpostUpdateOrderStatus($params)
	{
		if (in_array($params['newOrderStatus']->id, self::getAcceptedMapStatuses()))
		{
			$data = $this->prepareMapData($params);
			if (Configuration::get('yotpo_app_key') != '' && Configuration::get('yotpo_oauth_token') != '' && !is_null($data))
				$this->httpClient()->makeMapRequest($data, Configuration::get('yotpo_app_key'), Configuration::get('yotpo_oauth_token'));				
		}
	}

	public function hookProductTab($params)
	{
		if ($this->parseProductId() != null && Configuration::get('yotpo_widget_location') == 'tab')
			return '<li><a href="#idTab-yotpo">'.Configuration::get('yotpo_widget_tab_name').'</a></li>';
		return null;
	}

	public function hookProductTabContent($params)
	{
		$product = $this->getPageProduct(null);
		if ($product != null && Configuration::get('yotpo_widget_location') == 'tab')
			return '<div id="idTab-yotpo">'.$this->showWidget($product).'</div>';
	}

	public function hookextraLeft($params)
	{
		return $this->showBottomLine('left_column');	
	}
	
	public function hookextraRight($params)
	{		
		return $this->showBottomLine('right_column');	
	}
		
	public function hookorderConfirmation($params)
	{
		$app_key = Configuration::get('yotpo_app_key');
		$order_id = !empty($params['objOrder']) && !empty($params['objOrder']->id) ? $params['objOrder']->id : null;
		$order_amount = !empty($params['total_to_pay']) ? $params['total_to_pay'] : '';
		$order_currency = !empty($params['currencyObj']) && !empty($params['currencyObj']->iso_code) ? $params['currencyObj']->iso_code : '';

		if(!empty($app_key) && !is_null($order_id)) {
			global $smarty;
			$conversion_params = "app_key="      .$app_key.
                 				 "&order_id="    .$order_id.
                 				 "&order_amount=".$order_amount.
                 				 "&order_currency="  .$order_currency;
			$conversion_url = "https://api.yotpo.com/conversion_tracking.gif?$conversion_params";
			$smarty->assign('yotpoConversionUrl', $conversion_url);
			return $this->display(__FILE__,'tpl/conversionImage.tpl');
		}
	}

	public function uninstall()
	{
		Configuration::deleteByName('yotpo_app_key');
		Configuration::deleteByName('yotpo_oauth_token');
		Configuration::deleteByName('yotpo_widget_location');
		Configuration::deleteByName('yotpo_widget_tab_name');
		Configuration::deleteByName('yotpo_past_orders');
	    Configuration::deleteByName('yotpo_language');
    	Configuration::deleteByName('yotpo_language_as_site');
    	Configuration::deleteByName('yotpo_rich_snippets');
    	Configuration::deleteByName('yotpo_rich_snippet_cache_created');
    	
    	YotpoSnippetCache::dropDB();    	
		return parent::uninstall();
	}
	
	public function getContent()
	{
		if (isset($this->context) && isset($this->context->controller) && method_exists($this->context->controller, 'addCSS'))
			$this->context->controller->addCSS($this->_path.'/css/form.css', 'all');		
		else
			echo '<link rel="stylesheet" type="text/css" href="../modules/yotpo/css/form.css" />';	

		$force_settings = $this->processRegistrationForm() == 'b2c';
		$this->processSettingsForm();
		$this->displayForm($force_settings);

		return '<img src="http://www.prestashop.com/modules/yotpo.png?url_site='.Tools::safeOutput($_SERVER['SERVER_NAME']).'" alt="" style="display: none;" />'.$this->_html;
	}

	private function getProductImageUrl($id_product)
	{
		$id_image = Product::getCover($id_product);
		if (count($id_image) > 0)
		{
			$image = new Image($id_image['id_image']);
			return $image_url = method_exists($image, 'getExistingImgPath') ? _PS_BASE_URL_._THEME_PROD_DIR_.$image->getExistingImgPath().".jpg" : $this->getExistingImgPath($image);
		}
		return null;
	}

	private function getExistingImgPath($image)
	{
		if (!$image->id)
			return null;
		if (file_exists(_PS_PROD_IMG_DIR_.(int)$image->id_product.'-'.(int)$image->id.'.jpg'))
			return _PS_BASE_URL_._THEME_PROD_DIR_.(int)$image->id_product.'-'.(int)$image->id.'.'.'jpg';	
	}

	private function getProductLink($product_id, $link_rewrite = null)
	{
		global $link;
		if (isset($link) && method_exists($link, 'getProductLink'))
			return $link->getProductLink((int)$product_id);
		else
		{
			$link = new Link();
			return $link->getProductLink((int)$product_id);	
		}
	}

	private function getDescritpion($product,$lang_id)
	{
		if (!empty($product['description_short']))
			return strip_tags($product['description_short']);

		$full_product = new Product((int)$product['id_product'], false, (int)$lang_id);
		return strip_tags($full_product->description);
	}

	private function setError($error)
	{
		if (!$this->_errors)
			$this->_errors = array();
		$this->_errors[] = $error;
	}

	private function httpClient()
	{
		if (is_null($this->_httpClient))
		{
			include_once($this->_yotpo_module_path.'/YotpoHttpClient.php');
			$this->_httpClient = new YotpoHttpClient($this->name);
		}
		return $this->_httpClient;
	}

	private function parseProductId()
	{
		$product_id = (int)Tools::getValue('id_product');

		if (!empty($product_id))
			return (int)$product_id;
		else
		{
			parse_str($_SERVER['QUERY_STRING'], $query);
			if (!empty($query['id_product']))
				return (int)$query['id_product'];
		}
		return null;
	}

	private function showWidget($product)
	{
		$rich_snippets = '';
		if(Configuration::get('yotpo_rich_snippets') == true) {
			$rich_snippets .= $this->getRichSnippet($this->parseProductId());
		}
		global $smarty;			
		$smarty->assign('richSnippetsCode', $rich_snippets);
		
		$this->assignProductVars($product);
		if (Configuration::get('yotpo_widget_location') != 'other')
			return $this->display(__FILE__, 'tpl/widgetDiv.tpl');

		return null;
	}

	private function assignProductVars($product = null)
	{
		if(!$this->_is_smarty_product_vars_assigned)
		{
			if (is_null($product))
			$product = $this->getPageProduct();
			$this->_is_smarty_product_vars_assigned = true;

			global $smarty;
			$smarty->assign(array('yotpoProductId' => (int)$product->id,
			'yotpoProductName' => strip_tags($product->name),
			'yotpoProductDescription' => strip_tags($product->description),
			'yotpoProductModel' => $this->getProductModel($product),
			'yotpoProductImageUrl' => $this->getProductImageUrl($product->id),
			'yotpoProductBreadCrumbs' => $this->getBreadCrumbs($product),
			'yotpoProductLink' => $this->getProductLink((int)$product->id, $product->link_rewrite),
			'yotpoLanguage' => $this->getLanguage()));
		}
	}
	
	private function showBottomLine($bottom_line_location)
	{		
		if(Configuration::get('yotpo_bottom_line_enabled') == true && Configuration::get('yotpo_bottom_line_location') === $bottom_line_location
			&& Configuration::get('yotpo_bottom_line_location') != 'other')
		{
			$this->assignProductVars(null);
			
			return $this->display(__FILE__,'tpl/bottomLineDiv.tpl');
		}
	}
	
	private function getShopDomain()
	{
		return method_exists('Tools', 'getShopDomain') ? Tools::getShopDomain(false,false) : str_replace('www.', '', $_SERVER['HTTP_HOST']);
	}

	private function processRegistrationForm()
	{
		if (Tools::isSubmit('yotpo_register'))
		{
			$email = Tools::getValue('yotpo_user_email');
			$name = Tools::getValue('yotpo_user_name');
			$password = Tools::getValue('yotpo_user_password');
			$confirm = Tools::getValue('yotpo_user_confirm_password');
			if ($email === false || $email === '')
				return $this->prepareError($this->l('Provide valid email address'));	
			if (strlen($password) < 6 || strlen($password) > 128)
				return $this->prepareError($this->l('Password must be at least 6 characters'));	
			if ($password != $confirm)
				return $this->prepareError($this->l('Passwords are not identical'));	
			if ($name === false || $name === '')
				return $this->prepareError($this->l('Name is missing'));	

				
				
			$is_mail_valid = $this->httpClient()->checkeMailAvailability($email);
			if ($is_mail_valid['status_code'] == 200 && 
			  	($is_mail_valid['json'] == true && $is_mail_valid['response']['available'] == true) || 
			  	($is_mail_valid['json'] == false && preg_match("/available[\W]*(true)/",$is_mail_valid['response']) == 1))
			{
				$response = $this->httpClient()->check_if_b2c_user($email);
                if (empty($response['response']['data']))
                {
					$registerResponse = $this->httpClient()->register($email, $name, $password, _PS_BASE_URL_);
					
					if ($registerResponse['status_code'] == 200)
					{
						$app_key ='';
						$secret = '';
						if ($registerResponse['json'] == true)
							$app_key = $registerResponse['response']['app_key'];
						else 
						{
							preg_match("/app_key[\W]*[\"'](.*?)[\"']/",$registerResponse['response'], $matches);
							$app_key = $matches[1];
							unset($matches);
						}
						$secret ='';
						if ($registerResponse['json'] == true)
							$secret = $registerResponse['response']['secret'];
						else 
						{
							preg_match("/secret[\W]*[\"'](.*?)[\"']/",$registerResponse['response'], $matches);
							$secret = $matches[1];
						}					
						$accountPlatformResponse = $this->httpClient()->createAcountPlatform($app_key, $secret, _PS_BASE_URL_);
						if ($accountPlatformResponse['status_code'] == 200)
						{
							Configuration::updateValue('yotpo_app_key', $app_key, false);
							Configuration::updateValue('yotpo_oauth_token', $secret, false);
							return $this->prepareSuccess($this->l('Account successfully created'));
						}
						else
							return $this->prepareError($accountPlatformResponse['status_message']);	
					}
					else
						return $this->prepareError($registerResponse['status_message']);
                }
                else
                {
                    $id = $response['response']['data']['id'];
                    $data = array(
                        'password'=> $password,
                        'display_name'=> $name,
                        'account' => array(
                            'url' => _PS_BASE_URL_,
                            'custom_platform_name'=>null,
                            'install_step'=>8,
                            'account_platform' => array(
                                'shop_domain'=> _PS_BASE_URL_,
                                'platform_type_id'=>8,
                            )
                        )
                    );
                    $this->httpClient()->create_user_migration($id,$data);
                    $this->httpClient()->notify_user_migration($id);
                    $this->prepareError($this->l('We have sent you a confirmation email. Please check and click on the link to get your app key and secret token to fill out below.'));
                    return 'b2c';
                }
			}
			else
				return $is_mail_valid['status_code'] == 200 ? $this->prepareError($this->l('This e-mail address is already taken.')) : $this->prepareError();
		}
	}

	private function processSettingsForm()
	{
		if (Tools::isSubmit('yotpo_settings'))
		{
			$api_key = Tools::getValue('yotpo_app_key');
			$secret_token = Tools::getValue('yotpo_oauth_token');
			$location = Tools::getValue('yotpo_widget_location');
			$tabName = Tools::getValue('yotpo_widget_tab_name');
			$bottomLineEnabled = Tools::getValue('yotpo_bottom_line_enabled');
			$bottomLineLocation = Tools::getValue('yotpo_bottom_line_location');
		    $language_as_site = Tools::getValue('yotpo_language_as_site');
		    $widget_language_code = Tools::getValue('yotpo_widget_language_code');
			$rich_snippet = Tools::getValue('yotpo_rich_snippets');  			
			if ($api_key == '')
				return $this->prepareError($this->l('Api key is missing'));	
			if ($secret_token == '')
				return $this->prepareError($this->l('Please fill out the secret token'));
			Configuration::updateValue('yotpo_app_key', Tools::getValue('yotpo_app_key'), false);
			Configuration::updateValue('yotpo_oauth_token', Tools::getValue('yotpo_oauth_token'), false);
			Configuration::updateValue('yotpo_widget_location', $location, false);
			Configuration::updateValue('yotpo_widget_tab_name', $tabName, false);
			Configuration::updateValue('yotpo_bottom_line_enabled', $bottomLineEnabled, false);
			Configuration::updateValue('yotpo_bottom_line_location', $bottomLineLocation, false);	
	        Configuration::updateValue('yotpo_language', $widget_language_code, false);
            Configuration::updateValue('yotpo_language_as_site', $language_as_site, false); 		
            Configuration::updateValue('yotpo_rich_snippets', $rich_snippet, false);
			return $this->prepareSuccess();
		}
		elseif (Tools::isSubmit('yotpo_past_orders'))
		{
			$api_key = Tools::getValue('yotpo_app_key');
			$secret_token = Tools::getValue('yotpo_oauth_token');
			if ($api_key != '' && $secret_token != '')
			{
				$past_orders = $this->getPastOrders();
				$is_success = true;
				foreach ($past_orders as $post_bulk) 
					if (!is_null($post_bulk))
					{
						$response = $this->httpClient()->makePastOrdersRequest($post_bulk, $api_key, $secret_token);
						if ($response['status_code'] != 200 && $is_success)
						{
							$is_success = false;
							$this->prepareError($this->l($response['status_message']));
						}
					}

				if ($is_success)
				{
					Configuration::updateValue('yotpo_past_orders', 1, false);
					$this->prepareSuccess('Past orders sent successfully');
				}	
			}
			else
				$this->prepareError($this->l('You need to set your app key and secret token to post past orders'));
		}
	}

	private function displayForm($force_settings = false)
	{
		global $smarty;

		$smarty->assign(array('yotpo_finishedRegistration' => false, 'yotpo_allreadyUsingYotpo' => false));
		if (Tools::isSubmit('log_in_button'))
		{
			$smarty->assign('yotpo_allreadyUsingYotpo', true);
			return $this->displaySettingsForm();
		}
		if (Tools::isSubmit('yotpo_register'))
			$smarty->assign('yotpo_finishedRegistration', true);

		return Configuration::get('yotpo_app_key') != '' || $force_settings ? $this->displaySettingsForm() : $this->displayRegistrationForm();
	}

	private function displayRegistrationForm()
	{
		global $smarty;

		$smarty->assign(array('yotpo_action' => $_SERVER['REQUEST_URI'], 'yotpo_email' => Tools::getValue('yotpo_user_email'),
		'yotpo_userName' => Tools::getValue('yotpo_user_name')));

		$this->_html .= $this->display(__FILE__, 'tpl/registrationForm.tpl');

		return $this->_html;
	}

	private function displaySettingsForm()
	{
		if(!Configuration::get('yotpo_rich_snippet_cache_created')) {
			$created = YotpoSnippetCache::createDB();
			Configuration::updateValue('yotpo_rich_snippet_cache_created', 1, $created);
		}
		global $smarty;
	
		$smarty->assign(array(
		'yotpo_action' => $_SERVER['REQUEST_URI'],
		'yotpo_appKey' => Tools::getValue('yotpo_app_key',Configuration::get('yotpo_app_key')),
		'yotpo_oauthToken' => Tools::getValue('yotpo_oauth_token',Configuration::get('yotpo_oauth_token')),      
		'yotpo_widgetLocation' => Configuration::get('yotpo_widget_location'),
		'yotpo_showPastOrdersButton' => Configuration::get('yotpo_past_orders') != 1 ? true : false,         
		'yotpo_tabName' => Configuration::get('yotpo_widget_tab_name'),
		'yotpo_bottomLineEnabled' => Configuration::get('yotpo_bottom_line_enabled'), 
		'yotpo_bottomLineLocation' => Configuration::get('yotpo_bottom_line_location'),
	    'yotpo_widget_language_code' => Configuration::get('yotpo_language'),
	    'yotpo_language_as_site' => Configuration::get('yotpo_language_as_site'),
		'yotpo_rich_snippets' => Configuration::get('yotpo_rich_snippets')));

		$settings_template = $this->display(__FILE__, 'tpl/settingsForm.tpl');
		if (strpos($settings_template, 'yotpo_map_enabled') != false || strpos($settings_template, 'yotpo_language_as_site') == false || strpos($settings_template, 'yotpo_rich_snippets') == false)
		{
			if(method_exists($smarty, 'clearCompiledTemplate'))
			{
				$smarty->clearCompiledTemplate(_PS_MODULE_DIR_ . $this->name .'/tpl/settingsForm.tpl');	
				$settings_template = $this->display(__FILE__, 'tpl/settingsForm.tpl');
			}
			elseif (method_exists($smarty, 'clear_compiled_tpl'))
			{
				$smarty->clear_compiled_tpl(_PS_MODULE_DIR_ . $this->name .'/tpl/settingsForm.tpl');
				$settings_template = $this->display(__FILE__, 'tpl/settingsForm.tpl');
			}
			elseif (isset($smarty->force_compile)) {
				$value = $smarty->force_compile;
				$smarty->force_compile = true;
				$settings_template = $this->display(__FILE__, 'tpl/settingsForm.tpl');
				$smarty->force_compile = $value;
			}
		}
		$this->_html .= $settings_template;
	}

	private function getProductModel($product)
	{
		if (Validate::isEan13($product->ean13))
			return $product->ean13;
		elseif (Validate::isUpc($product->upc))
			return $product->upc;

		return null;
	}

	private function getBreadCrumbs($product)
	{
		if (!method_exists('Product', 'getProductCategoriesFull'))
			return '';	

		$result = array();
		$lang_id;
		if (isset($this->context))
			$lang_id = (int)$this->context->language->id; 
		else 
		{
			global $cookie;
			$lang_id = (int)$cookie->id_lang; 
		}
		$all_product_subs = Product::getProductCategoriesFull((int)$product->id, (int)$lang_id);
		if (isset($all_product_subs) && count($all_product_subs) > 0)
			foreach($all_product_subs as $subcat)
			{
				$sub_category = new Category((int)$subcat['id_category'], (int)$lang_id);
				$sub_category_path = $sub_category->getParentsCategories();
				foreach ($sub_category_path as $key)
					$result[] = $key['name'];
			}

		return implode(';', $result);
	}

	private function prepareError($message = '')
	{
		$this->_html .= sprintf('<div class="alert">%s</div>', $message == '' ? $this->l('Error occured') : $message);
	}

	private function prepareSuccess($message = '')
	{
		$this->_html .= sprintf('<div class="conf confirm">%s</div>', $message == '' ? $this->l('Settings updated') : $message);
	}

	private function prepareMapData($params)
	{
		$order = new Order((int)$params['id_order']);
		$customer = new Customer((int)$order->id_customer);
		$id_lang = !is_null($params['cookie']) && !is_null($params['cookie']->id_lang) ? (int)$params['cookie']->id_lang : (int)Configuration::get('PS_LANG_DEFAULT');
		if (Validate::isLoadedObject($order) && Validate::isLoadedObject($customer))
		{
			$singleMapParams = array('id_order' => (int)$params['id_order'], 'date_add' => $order->date_add,
			'email' => $customer->email, 'firstname'=> $customer->firstname, 'lastname' => $customer->lastname,
			'id_lang' => $id_lang);

			$result = $this->getSingleMapData($singleMapParams);
			if (!is_null($result) && is_array($result))
			{
				$result['platform'] = 'prestashop';
				return $result;
			}
		}
	 	return null;
	}

	private function getSingleMapData($params)
	{
		$cart = Cart::getCartByOrderId((int)$params['id_order']);
		if(Validate::isLoadedObject($cart))
		{
			$products = $cart->getProducts();
			if(count($products) == 0 && method_exists('Shop','getContextShopID') && Shop::getContextShopID() != (int)$cart->id_shop) 
			{
				Shop::initialize();
				$products = $cart->getProducts(true);
			}					
			$currency = Currency::getCurrencyInstance((int)$cart->id_currency);
			if (!is_null($products) && is_array($products) && Validate::isLoadedObject($currency))
			{
				$data = array();
				$data['order_date'] = $params['date_add'];
				$data['email'] = $params['email'];
				$data['customer_name'] = $params['firstname'].' '.$params['lastname'];
				$data['order_id'] = (int)$params['id_order'];
				$data['currency_iso'] = $currency->iso_code;			    
				$products_arr = array();
				foreach ($products as $product) 
				{
					$product_data = array();    
					$product_data['url'] = $this->getProductLink($product['id_product'], $product['link_rewrite']); 
					$product_data['name'] = $product['name'];
					$product_data['image'] = $this->getProductImageUrl((int)$product['id_product']);
					$product_data['description'] = $this->getDescritpion($product, (int)$params['id_lang']);
					$product_data['price'] = $product['price'];
					$products_arr[(int)$product['id_product']] = $product_data;
				}
				$data['products'] = $products_arr;
				return $data;
			}
		}
	 	return null;
	}	
	
	private function getPastOrders()
	{
		$result = Db::getInstance()->ExecuteS('SELECT  o.`id_order`,o.`id_lang`, o.`date_add`, c.`firstname`, c.`lastname`, c.`email` 
		FROM `'._DB_PREFIX_.'order_history` oh
		LEFT JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_order` = oh.`id_order`)
		LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.`id_customer` = o.`id_customer`)
		WHERE oh.`id_order_history` IN (SELECT MAX(`id_order_history`) FROM `'._DB_PREFIX_.'order_history` GROUP BY `id_order`) AND
		o.`date_add` <  NOW() AND 
		DATE_SUB(NOW(), INTERVAL '.self::PAST_ORDERS_DAYS_BACK.' day) < o.`date_add` AND 
		oh.`id_order_state` IN ('.join(',', self::getAcceptedMapStatuses()).')
		LIMIT 0,'.self::PAST_ORDERS_LIMIT.'');

		if (is_array($result))
		{
			$orders = array();
			foreach ($result as $singleMap)
			{
				$res = $this->getSingleMapData($singleMap);
				if (!is_null($res))
					$orders[] = $res;
			}
			$post_bulk_orders = array_chunk($orders, self::BULK_SIZE);
			$data = array();
			foreach ($post_bulk_orders as $index => $bulk)
			{
				$data[$index] = array();
				$data[$index]['orders'] = $bulk;
				$data[$index]['platform'] = 'prestashop';			
			}
			return $data;
		}
		return null;
	}

	private function getPageProduct($product_id = null)
	{
		if($product_id == null)
			$product_id = $this->parseProductId();
			
		$product = new Product((int)($product_id), false, Configuration::get('PS_LANG_DEFAULT'));
		if(Validate::isLoadedObject($product))
			return $product;
			
		return null;
	}
	
	private function getLanguage() {
		$language = Configuration::get('yotpo_language');
		if (Configuration::get('yotpo_language_as_site') == true) {
			if (isset($this->context) && isset($this->context->language) && isset($this->context->language->iso_code)) {
				$language = $this->context->language->iso_code;
			}
			else {
				global $cookie;
				$language = Language::getIsoById( (int)$cookie->id_lang );
			}	
		}
		return $language;
	}
	
	private function getRichSnippet($product_id) {
		$result = '';		
		if (Configuration::get('yotpo_app_key') != '' && Configuration::get('yotpo_oauth_token') != '' && is_int($product_id)) {
			try {
				$result = YotpoSnippetCache::getRichSnippet($product_id);
				$should_update_row = is_array($result) && !YotpoSnippetCache::isValidCache($result); 			
				if($result == false || $should_update_row) {			
					$result = '';
					$expiration_time = null;
					$request_result = $this->httpClient()->makeRichSnippetRequest(Configuration::get('yotpo_app_key'), Configuration::get('yotpo_oauth_token'),$product_id);
					if($request_result['status_code'] == 200) {
						if ($request_result['json'] == true) {
							$result .= $request_result['response']['rich_snippet']['html_code'];
							$expiration_time = $request_result['response']['rich_snippet']['ttl'];
						}
						else 
						{
							preg_match("/html_code[\"']:[\"'](.*)[\"'],[\"']ttl/",$request_result['response'], $matches);
							$result = $matches[1];
							unset($matches);
							$result = str_replace('\"','"',$result);
							$result = str_replace('\n','',$result);
							
							preg_match("/ttl[\"']:(.*)}/",$request_result['response'], $matches);
							$expiration_time = $matches[1];						 
							unset($matches);
						}	
						if(strlen($result) > 0 && strlen($expiration_time) > 0 && is_numeric($expiration_time)) {
							if($should_update_row) {
								YotpoSnippetCache::updateCahce($product_id, $result, $expiration_time);
							}
							else {
								YotpoSnippetCache::addRichSnippetToCahce($product_id, $result, $expiration_time);	
							}
								
						}
					}
				}
				elseif (is_array($result) && !$should_update_row) {
					$result = $result['rich_snippet_code'];
				}
			}
			catch (Exception $e) {
				error_log($e->getMessage());
			}				
		}
		return $result;		
	}		
}
