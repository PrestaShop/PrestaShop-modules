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
* @copyright 2007-2014 PrestaShop SA
* @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/
 
if (!defined('_PS_VERSION_'))
	exit;

require_once(dirname(__FILE__).'/classes/PrediggoConfig.php');
require_once(_PS_MODULE_DIR_.'prediggo/controllers/DataExtractorController.php');
require_once(_PS_MODULE_DIR_.'prediggo/controllers/PrediggoCallController.php');

class Prediggo extends Module
{
	/** @var array list of errors */
	public $_errors = array();

	/** @var array list of confirmations */
	public $_confirmations = array();

	/** @var array list of warnings */
	public $_warnings = array();
	
	/** @var PrediggoConfig Object PrediggoConfig */
	public $oPrediggoConfig;
	
	/** @var DataExtractorController Object DataExtractorController */
	public $oDataExtractorController;
	
	/** @var PrediggoCallController Object PrediggoCallController */
	public $oPrediggoCallController;
	
	/** @var array list of Products by hook */
	public $aRecommendations;

	/**
	 * Constructor
	*/
	public function __construct()
	{
		$this->name = 'prediggo';
		$this->tab = 'advertising_marketing';
		$this->version = '1.2';
		$this->author = 'PrestaShop';
		$this->need_instance = 0;
		$this->_html = '';
		$this->multishop_context = true;

		parent::__construct();

		$this->displayName = $this->l('Prediggo');
		$this->description = $this->l('Offers interactive products recommendations in the front office');

		$this->_warnings = array();
		$this->_confirmations = array();
		$this->_errors = array();
		
		/* Set the Configuration Object */
		$this->oPrediggoConfig = new PrediggoConfig($this->context);
		
		/* Set the main controllers */
		$this->oDataExtractorController = new DataExtractorController($this);
		$this->oPrediggoCallController = new PrediggoCallController();
		
		$this->aRecommendations = array();
	}

	/**
	 * Install Procedure
	 */
	public function install()
	{
		return 	($this->oPrediggoConfig->install()
				&& parent::install()
				&& $this->registerHook('displayHeader')
				&& $this->registerHook('displayTop')
				&& $this->registerHook('displayLeftColumn')
				&& $this->registerHook('displayRightColumn')
		        && $this->registerHook('displayFooter')
		        && $this->registerHook('actionAuthentication')
				&& $this->registerHook('actionCustomerAccountAdd')
				&& $this->registerHook('displayPaymentTop')
		);
	}

	/**
	 * Uninstall Procedure
	 */
	public function uninstall()
	{
		return	($this->oPrediggoConfig->uninstall()
				&& parent::uninstall()
		);
	}
	
	/**
	 * Hook Header : Add Media CSS & JS
	 *
	 * @param array $params list of specific data
	 */
	public function hookDisplayHeader($params)
	{
		if (!isset($params['cookie']->id_guest))
			Guest::setNewGuest($params['cookie']);
		
		if(!$this->oPrediggoConfig->web_site_id_checked)
			return false;
	
		// Check if prediggo module can be executed in this page
		if($this->oPrediggoCallController->isPageAccessible()
		|| $this->oPrediggoCallController->getPageName() == 'prediggo_search'
		|| $this->oPrediggoConfig->search_active)
		{
			$this->context->controller->addCSS(($this->_path).'css/front/'.($this->name).'.css', 'all');
			$this->context->controller->addJS(array(
				($this->_path).'js/front/prediggo_autocomplete.js',
				($this->_path).'js/front/'.($this->name).'.js'
			));
		}
	}
	
	/**
	 * Hook Top : Display the prediggo search block
	 *
	 * @param array $params list of specific data
	 */
	public function hookDisplayTop($params)
	{
		return $this->displaySearchBlock($params);
	}
	
	/**
	 * Hook Left Column : Display the recommendations
	 *
	 * @param array $params list of specific data
	 */
	public function hookDisplayLeftColumn($params)
	{
		// Get list of recommendations
		return $this->displaySearchFilterBlock($params).$this->displayRecommendations('left_column', $params);
	}
	
	/**
	 * Hook Right Column : Display the recommendations
	 *
	 * @param array $params list of specific data
	 */
	public function hookDisplayRightColumn($params)
	{
		// Get list of recommendations
		return $this->displayRecommendations('right_column', $params);
	}
	
	/**
	 * Hook Footer : Display the recommendations
	 *
	 * @param array $params list of specific data
	 */
	public function hookDisplayFooter($params)
	{
		// Get list of recommendations
		return $this->displayRecommendations('footer', $params);
	}
	
	/**
	 * Hook Authentication : Notify prediggo that the user is authenticated
	 *
	 * @param array $params list of specific data
	 */
	public function hookActionAuthentication($params)
	{
		if(!$this->oPrediggoConfig->web_site_id_checked)
			return false;
		$params['customer'] = $this->context->customer;
		$this->oPrediggoCallController->notifyPrediggo('user', $params);
	}
	 
	/**
	 * Hook Payment Top : Notify prediggo that the user is authenticated
	 *
	 * @param array $params list of specific data
	 */
	public function hookDisplayPaymentTop($params)
	{
		if(!$this->oPrediggoConfig->web_site_id_checked)
			return false;
		
		$params['customer'] = $this->context->customer;
		$this->oPrediggoCallController->notifyPrediggo('user', $params);
	}
	 
	/**
	 * Hook Create Account : Notify prediggo that the user is authenticated
	 *
	 * @param array $params list of specific data
	 */
	public function hookActionCustomerAccountAdd($params)
	{
		if(!$this->oPrediggoConfig->web_site_id_checked)
			return false;
		$params['customer'] = $this->context->customer;
		$this->oPrediggoCallController->notifyPrediggo('user', $params);
	}
	
	/**
	 * Display the recommendations by hook
	 *
	 * @param string $sHookName Hook Name
	 * @param array $params list of specific data
	 * @return string Html
	 */
	private function displayRecommendations($sHookName, $params)
	{
		if(!$this->oPrediggoConfig->web_site_id_checked)
			return false;
		
		$params['customer'] = $this->context->customer;
		if(!$this->aRecommendations[$sHookName] = $this->oPrediggoCallController->getListOfRecommendations($sHookName, $params))
			return false;
		
		// Display Main Configuration management
		$this->smarty->assign(array(
			'hook_name' 		=> $sHookName,
			'aRecommendations' 	=> $this->aRecommendations,

			'tax_enabled' 		=> (int)Configuration::get('PS_TAX'),
			'display_qties' 	=> (int)Configuration::get('PS_DISPLAY_QTIES'),
			'display_ht' 		=> !Tax::excludeTaxeOption(),
			'sImageType' 		=> (Tools::version_compare(_PS_VERSION_, '1.5.1', '>=')?'home_default':'home'),
		));
	
		return $this->display(__FILE__, 'list_recommendations.tpl');
	}
	
	/**
	 * Hook Authentication : Notify prediggo that a recommendations has been clicked
	 *
	 * @param array $params list of specific data
	 */
	public function setProductNotification($params)
	{
		if(!$this->oPrediggoConfig->web_site_id_checked)
			return false;
		$params['customer'] = $this->context->customer;
		$this->oPrediggoCallController->notifyPrediggo('product', $params);
	}
	
	/**
	 * Display the search Filters block
	 *
	 * @param array $params list of specific data
	 * @return string Html
	 */
	private function displaySearchFilterBlock($params)
	{
		if($this->oPrediggoConfig->web_site_id_checked
		&& $this->oPrediggoConfig->layered_navigation_active)
			return $this->display(__FILE__, 'views/templates/front/search_filters_block.tpl');
	}
	
	/**
	 * Display the search block
	 *
	 * @param array $params list of specific data
	 * @return string Html
	 */
	private function displaySearchBlock($params)
	{
		if($this->oPrediggoConfig->web_site_id_checked
		&& $this->oPrediggoConfig->search_active)
			return $this->display(__FILE__, 'search_block.tpl');
	}
	
	/**
	 * Display the did you mean suggestion
	 *
	 * @return string Html
	 */
	public function displayAutocompleteDidYouMean()
	{
		if($this->oPrediggoConfig->web_site_id_checked
		&& $this->oPrediggoConfig->search_active
		&& $this->oPrediggoConfig->autocompletion_active)
		{
			$this->smarty->assign(array(
				'sImageType' 		=> (Tools::version_compare(_PS_VERSION_, '1.5.1', '>=')?'home_default':'home'),
			));
			return $this->display(__FILE__, 'views/templates/hook/autocomplete_dum.tpl');
		}
	}
	
	/**
	 * Display the autocompletion products
	 *
	 * @return string Html
	 */
	public function displayAutocompleteProduct()
	{
		if($this->oPrediggoConfig->web_site_id_checked
		&& $this->oPrediggoConfig->search_active
		&& $this->oPrediggoConfig->autocompletion_active)
			return $this->display(__FILE__, 'views/templates/hook/autocomplete_product.tpl');
	}
	
	/**
	 * Display the autocompletion suggestions
	 *
	 * @return string Html
	 */
	public function displayAutocompleteSuggest()
	{
		if($this->oPrediggoConfig->web_site_id_checked
		&& $this->oPrediggoConfig->search_active
		&& $this->oPrediggoConfig->autocompletion_active)
			return $this->display(__FILE__, 'views/templates/hook/autocomplete_suggest.tpl');
	}
	
	/**
	 * Get the recommendations from the blocklayered filters
	 *
	 * @param array $params list of specific data
	 * @return array $aData containing the front office block
	 */
	public function getBlockLayeredRecommendations($params)
	{
		if(!$this->oPrediggoConfig->web_site_id_checked)
			return false;
		
		$sHookName = 'blocklayered';
		$this->oPrediggoCallController->_setPageName($sHookName);
		$params['filters'] = $this->getSelectedFilters();
		$params['customer'] = $this->context->customer;
		
		if(!$this->aRecommendations[$sHookName] = $this->oPrediggoCallController->getListOfRecommendations($sHookName, $params))
			return false;
	
		// Display Main Configuration management
		$this->smarty->assign(array(
			'hook_name' 		=> $sHookName,
			'page_name' 		=> 'category',
			'aRecommendations' 	=> $this->aRecommendations,
			'tax_enabled'		=> (int)Configuration::get('PS_TAX'),
			'display_qties' 	=> (int)Configuration::get('PS_DISPLAY_QTIES'),
			'display_ht' 		=> !Tax::excludeTaxeOption(),
			'sImageType' 		=> (Tools::version_compare(_PS_VERSION_, '1.5.1', '>=')?'home_default':'home'),
		));
	
		return $this->display(__FILE__, 'list_recommendations.tpl');
	}
	
	/**
	 * Get the blocklayered filters
	 *
	 * @return array $selectedFilters list of filters
	 */
	private function getSelectedFilters()
	{
		$id_parent = (int)Tools::getValue('id_category', Tools::getValue('id_category_layered', 1));
		if ($id_parent == 1)
			return;
		
		// Force attributes selection (by url '.../2-mycategory/color-blue' or by get parameter 'selected_filters')
		if (basename($_SERVER['SCRIPT_FILENAME'], 'xhr.php') === false || Tools::getValue('selected_filters') !== false)
		{
			if (Tools::getValue('selected_filters'))
				$url = Tools::getValue('selected_filters');
			else
				$url = preg_replace('/\/(?:\w*)\/(?:[0-9]+[-\w]*)([^\?]*)\??.*/', '$1', Tools::safeOutput($_SERVER['REQUEST_URI'], true));
			
			$url_attributes = explode('/', ltrim($url, '/'));
			$selected_filters = array('category' => array());
			if (!empty($url_attributes))
			{
				foreach ($url_attributes as $url_attribute)
				{
					$url_parameters = explode('-', $url_attribute);
					$attribute_name  = array_shift($url_parameters);
					if ($attribute_name == 'page')
						$this->page = (int)$url_parameters[0];
					elseif (in_array($attribute_name, array('price', 'weight')))
						$selected_filters[$attribute_name] = array($url_parameters[0], $url_parameters[1]);
					else
					{
						foreach ($url_parameters as $url_parameter)
						{
							$data = Db::getInstance()->getValue('SELECT data FROM `'._DB_PREFIX_.'layered_friendly_url` WHERE `url_key` = \''.md5('/'.$attribute_name.'-'.$url_parameter).'\'');
							if ($data)
								foreach (self::unSerialize($data) as $key_params => $params)
								{
									if (!isset($selected_filters[$key_params]))
										$selected_filters[$key_params] = array();
									foreach ($params as $key_param => $param)
									{
										if (!isset($selected_filters[$key_params][$key_param]))
											$selected_filters[$key_params][$key_param] = array();
										$selected_filters[$key_params][$key_param] = $param;
									}
								}
						}
					}
				}
				return $selected_filters;
			}
		}

		/* Analyze all the filters selected by the user and store them into a tab */
		$selected_filters = array('category' => array(), 'manufacturer' => array(), 'quantity' => array(), 'condition' => array());
		foreach ($_GET as $key => $value)
			if (substr($key, 0, 8) == 'layered_')
			{
				preg_match('/^(.*)_([0-9]+|new|used|refurbished|slider)$/', substr($key, 8, strlen($key) - 8), $res);
				if (isset($res[1]))
				{
					$tmp_tab = explode('_', $value);
					$value = $tmp_tab[0];
					$id_key = false;
					if (isset($tmp_tab[1]))
						$id_key = $tmp_tab[1];
					if ($res[1] == 'condition' && in_array($value, array('new', 'used', 'refurbished')))
						$selected_filters['condition'][] = $value;
					elseif ($res[1] == 'quantity' && (!$value || $value == 1))
						$selected_filters['quantity'][] = $value;
					elseif (in_array($res[1], array('category', 'manufacturer')))
					{
						if (!isset($selected_filters[$res[1].($id_key ? '_'.$id_key : '')]))
							$selected_filters[$res[1].($id_key ? '_'.$id_key : '')] = array();
						$selected_filters[$res[1].($id_key ? '_'.$id_key : '')][] = (int)$value;
					}
					elseif (in_array($res[1], array('id_attribute_group', 'id_feature')))
					{
						if (!isset($selected_filters[$res[1]]))
							$selected_filters[$res[1]] = array();
						$selected_filters[$res[1]][(int)$value] = $id_key.'_'.(int)$value;
					}
					elseif ($res[1] == 'weight')
						$selected_filters[$res[1]] = $tmp_tab;
					elseif ($res[1] == 'price')
						$selected_filters[$res[1]] = $tmp_tab;
				}
			}
		return $selected_filters;
	}

	/**
	 * BO main function
	 *
	 * @return string Html
	 */
	public function getContent()
	{
		// Web site id verification for older version
		if(!$this->oPrediggoConfig->web_site_id_checked
		&& !Configuration::hasKey('PREDIGGO_WEB_SITE_ID_CHECKED'))
			$this->checkWebSiteId();
		
		if (count($_POST))
			$this->_postProcess();

		// Check Intermediary Database
		$this->checkModuleConstraints();

		// Display forms
		$this->_displayForm();

		return $this->_html;
	}
	
	/**
	 * Check the client web site id
	 */
	private function checkWebSiteId()
	{
		$this->oPrediggoConfig->web_site_id_checked = (int)$this->oPrediggoCallController->checkWebSiteId();
		if(!$this->oPrediggoConfig->save())
			$this->_errors[] = $this->l('An error occurred while updating the main configuration settings');
	}

	/**
	 * Check the server configuration variables
	 */
	public function checkModuleConstraints()
	{
		if(!extension_loaded('dom'))
			$this->_errors[] = $this->l('Please activate the PHP extension "DOM" to allow the use of the module.');

		if(!extension_loaded('curl'))
			$this->_errors[] = $this->l('Please activate the PHP extension "curl" to allow the use of the module.');
		
		if(!$this->oPrediggoConfig->web_site_id_checked)
			$this->_warnings[] = $this->l('Please update the field "Web Site ID", in the "Main Configuration" tab.');
			
		// API can't be call if curl extension is not installed on PHP config.
		if((int)ini_get('max_execution_time') < 3000)
			$this->_warnings[] = $this->l('Please update the PHP option "max_execution_time" to a minimum of "3000". (Current value : ').(int)ini_get('max_execution_time').$this->l(')');

		if((int)ini_get('max_input_time') < 3000)
			$this->_warnings[] = $this->l('Please update the PHP option "max_input_time" to a minimum of "3000". (Current value : ').(int)ini_get('max_input_time').$this->l(')');

		if((int)ini_get('memory_limit') < 384)
			$this->_warnings[] = $this->l('Please update the PHP option "memory_limit" to a minimum of "384M". (Current value : ').ini_get('memory_limit').$this->l(')');
		
		if((int)(Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP))
			$this->_warnings[] = $this->l('Please select a shop on the top block to configure the specific settings.');
	}
	
	/**
	 * Set the data once an updated is processed in the BO
	 */
	private function _postProcess()
	{
		// Set the main configuration
		if(Tools::isSubmit('mainConfSubmit'))
		{
			$this->oPrediggoConfig->web_site_id = Tools::safeOutput(Tools::getValue('web_site_id'));
			if($this->oPrediggoConfig->save())
			{
				$this->checkWebSiteId();
				$this->_confirmations[] = $this->l('Main settings updated');
			}
			else
				$this->_errors[] = $this->l('An error occurred while updating the main configuration settings');
			
		}
		
		// Set the export configuration
		if(Tools::isSubmit('exportConfSubmit'))
		{
			$this->oPrediggoConfig->products_file_generation 			= Tools::safeOutput(Tools::getValue('products_file_generation'));
			$this->oPrediggoConfig->orders_file_generation 				= Tools::safeOutput(Tools::getValue('orders_file_generation'));
			$this->oPrediggoConfig->customers_file_generation 			= Tools::safeOutput(Tools::getValue('customers_file_generation'));
			$this->oPrediggoConfig->logs_file_generation 				= Tools::safeOutput(Tools::getValue('logs_file_generation'));
			$this->oPrediggoConfig->export_product_image 				= Tools::safeOutput(Tools::getValue('export_product_image'));
			$this->oPrediggoConfig->export_product_description 			= Tools::safeOutput(Tools::getValue('export_product_description'));
			$this->oPrediggoConfig->export_product_min_quantity 		= Tools::safeOutput(Tools::getValue('export_product_min_quantity'));
			$this->oPrediggoConfig->nb_days_order_valide 				= Tools::safeOutput(Tools::getValue('nb_days_order_valide'));
			$this->oPrediggoConfig->nb_days_customer_last_visit_valide 	= Tools::safeOutput(Tools::getValue('nb_days_customer_last_visit_valide'));
			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Export settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the export configuration settings');
		}
		
		// Launch the file export
		if(Tools::isSubmit('manualExportSubmit')
		&& !sizeof($this->_errors))
		{
			$this->oDataExtractorController->launchExport();
		}
		
		// Set the export attributes
		if(Tools::isSubmit('exportPrediggoAttributesSubmit'))
		{
			if(is_array(Tools::getValue('attributes_groups_ids')))
				$this->oPrediggoConfig->attributes_groups_ids 	= Tools::safeOutput(join(',', array_map('intval',Tools::getValue('attributes_groups_ids'))));
			
			if(is_array(Tools::getValue('features_ids')))
				$this->oPrediggoConfig->features_ids 	= Tools::safeOutput(join(',', array_map('intval',Tools::getValue('features_ids'))));
			
			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Product attributes settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the product attributes configuration settings');
		}
		
		// Set the black list of recommendations
		if(Tools::isSubmit('exportNotRecoSubmit'))
		{
			$this->oPrediggoConfig->products_ids_not_recommendable = Tools::safeOutput(substr(Tools::getValue('input_products_ids_not_recommendable'), 0, -1));
			
			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Black list of recommendations updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the black list of recommendations');
		}
		
		// Set the black list of recommendations
		if(Tools::isSubmit('exportNotSearchSubmit'))
		{
			$this->oPrediggoConfig->products_ids_not_searchable = Tools::safeOutput(substr(Tools::getValue('input_products_ids_not_searchable'), 0, -1));
				
			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Black list of searchs updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the black list of searchs');
		}
		
		// Set the protection configuration
		if(Tools::isSubmit('exportProtectionConfSubmit'))
		{
			if($aIds = $this->oDataExtractorController->setRepositoryProtection(Tools::getValue('htpasswd_user'), Tools::getValue('htpasswd_pwd')))
			{
				if(empty($aIds['user']))
					$this->_confirmations[] = $this->l('Protection has been disactivated');
				else
					$this->_confirmations[] = $this->l('Protection has been activated');
			
				$this->oPrediggoConfig->htpasswd_user 	= Tools::safeOutput(Tools::getValue('htpasswd_user'));
				$this->oPrediggoConfig->htpasswd_pwd 	= Tools::safeOutput(Tools::getValue('htpasswd_pwd'));
				if($this->oPrediggoConfig->save())
					$this->_confirmations[] = $this->l('Protection settings updated');
				else
					$this->_errors[] = $this->l('An error occurred while updating the protection configuration settings');
			}
			else
				$this->_errors[] = $this->l('An error occurred when activating the protection');
		}
		
		// Set the recommendations main configuration
		if(Tools::isSubmit('mainRecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->logs_reco_file_generation 	= Tools::safeOutput(Tools::getValue('logs_reco_file_generation'));
			$this->oPrediggoConfig->server_url_recommendations 	= Tools::safeOutput(Tools::getValue('server_url_recommendations'));
			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Recommendations main configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the main configuration of recommendations settings');
		}
		
		// Set the homepage recommendations block configuration
		if(Tools::isSubmit('exportHomeRecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->home_recommendations 	= Tools::safeOutput(Tools::getValue('home_recommendations'));
			$this->oPrediggoConfig->home_nb_items 			= (int)Tools::safeOutput(Tools::getValue('home_nb_items'));
			
			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->home_block_title[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('home_block_title_'.(int)$aLanguage['id_lang']));
						
			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Homepage recommendations block configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the homepage recommendations block configuration of recommendations settings');
		}
		
		// Set the 404 page recommendations block configuration
		if(Tools::isSubmit('export404RecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->error_recommendations 	= Tools::safeOutput(Tools::getValue('error_recommendations'));
			$this->oPrediggoConfig->error_nb_items 			= (int)Tools::safeOutput(Tools::getValue('error_nb_items'));

			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->error_block_title[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('error_block_title_'.(int)$aLanguage['id_lang']));
			
			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('404 page recommendations block configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the 404 page recommendations block configuration of recommendations settings');
		}
		
		// Set the products pages recommendations block configuration
		if(Tools::isSubmit('exportProductRecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->product_recommendations 	= Tools::safeOutput(Tools::getValue('product_recommendations'));
			$this->oPrediggoConfig->product_nb_items 			= (int)Tools::safeOutput(Tools::getValue('product_nb_items'));
			
			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->product_block_title[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('product_block_title_'.(int)$aLanguage['id_lang']));
			
			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Products pages recommendations block configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the products pages recommendations block configuration of recommendations settings');
		}
		
		// Set the categories pages recommendations block configuration
		if(Tools::isSubmit('exportCategoryRecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->category_recommendations 	= Tools::safeOutput(Tools::getValue('category_recommendations'));
			$this->oPrediggoConfig->category_nb_items 			= (int)Tools::safeOutput(Tools::getValue('category_nb_items'));
			
			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->category_block_title[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('category_block_title_'.(int)$aLanguage['id_lang']));
			
			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Categories pages recommendations block configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the categories pages recommendations block configuration of recommendations settings');
		}
		
		// Set the customers pages recommendations block configuration
		if(Tools::isSubmit('exportCustomerRecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->customer_recommendations 	= Tools::safeOutput(Tools::getValue('customer_recommendations'));
			$this->oPrediggoConfig->customer_nb_items 			= (int)Tools::safeOutput(Tools::getValue('customer_nb_items'));
			
			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->customer_block_title[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('customer_block_title_'.(int)$aLanguage['id_lang']));
			
			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Customers pages recommendations block configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the customers pages recommendations block configuration of recommendations settings');
		}
		
		// Set the cart page recommendations block configuration
		if(Tools::isSubmit('exportCartRecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->cart_recommendations 	= Tools::safeOutput(Tools::getValue('cart_recommendations'));
			$this->oPrediggoConfig->cart_nb_items 			= (int)Tools::safeOutput(Tools::getValue('cart_nb_items'));
			
			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->cart_block_title[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('cart_block_title_'.(int)$aLanguage['id_lang']));
			
			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Cart page recommendations block configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the cart page recommendations block configuration of recommendations settings');
		}
		
		// Set the blocklayered module recommendations block configuration
		if(Tools::isSubmit('exportBlocklayeredRecommendationConfSubmit'))
		{
			$this->oPrediggoConfig->blocklayered_recommendations 	= Tools::safeOutput(Tools::getValue('blocklayered_recommendations'));
			$this->oPrediggoConfig->blocklayered_nb_items 			= (int)Tools::safeOutput(Tools::getValue('blocklayered_nb_items'));
			
			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->blocklayered_block_title[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('blocklayered_block_title_'.(int)$aLanguage['id_lang']));
			
			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Block layered module recommendations block configuration settings updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the block layered module recommendations block configuration of recommendations settings');
		}
		
		// Set the searchs main configuration
		if(Tools::isSubmit('mainSearchConfSubmit'))
		{
			$this->oPrediggoConfig->search_active 					= (int)Tools::safeOutput(Tools::getValue('search_active'));
			$this->oPrediggoConfig->search_nb_items 				= (int)Tools::safeOutput(Tools::getValue('search_nb_items'));
			$this->oPrediggoConfig->search_nb_min_chars 			= (int)Tools::safeOutput(Tools::getValue('search_nb_min_chars'));
			$this->oPrediggoConfig->logs_search_file_generation 	= (int)Tools::safeOutput(Tools::getValue('logs_search_file_generation'));
			$this->oPrediggoConfig->server_url_search 				= Tools::safeOutput(Tools::getValue('server_url_search'));
			$this->oPrediggoConfig->searchandizing_active 			= (int)Tools::safeOutput(Tools::getValue('searchandizing_active'));
			$this->oPrediggoConfig->layered_navigation_active 		= (int)Tools::safeOutput(Tools::getValue('layered_navigation_active'));
				
			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Main configuration settings of searchs updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the main configuration of searchs settings');
		}
		
		// Set the searchs autocompletion configuration
		if(Tools::isSubmit('exportSearchAutocompletionConfSubmit'))
		{
			$this->oPrediggoConfig->autocompletion_active 		= (int)Tools::safeOutput(Tools::getValue('autocompletion_active'));
			$this->oPrediggoConfig->autocompletion_nb_items 	= (int)Tools::safeOutput(Tools::getValue('autocompletion_nb_items'));
			$this->oPrediggoConfig->suggest_active 				= (int)Tools::safeOutput(Tools::getValue('suggest_active'));
			
			foreach($this->context->controller->getLanguages() as $aLanguage)
				$this->oPrediggoConfig->suggest_words[(int)$aLanguage['id_lang']] = Tools::safeOutput(Tools::getValue('suggest_words_'.(int)$aLanguage['id_lang']));
			
			if($this->oPrediggoConfig->save())
				$this->_confirmations[] = $this->l('Main configuration settings of search autocompletion updated');
			else
				$this->_errors[] = $this->l('An error occurred while updating the main configuration of search autocompletion settings');
		}
	}

	/**
	 * Display the BO html with smart templates
	 */
	public function _displayForm()
	{		
		// Add the specific jquery ui plugins, module JS & CSS
		$this->context->controller->addJqueryUI('ui.tabs');
		$this->context->controller->addJqueryPlugin('autocomplete');
		$this->context->controller->addJs(($this->_path).'js/admin/'.$this->name.'.js');
		$this->context->controller->addCss(array(
			($this->_path).'css/admin/'.$this->name.'.css' => 'all',
			_PS_JS_DIR_.'jquery/ui/themes/base/jquery.ui.all.css'
		));
		
		/* Display the errors / warnings / confirmations */
		$this->context->smarty->assign(array(
			'aPrediggoWarnings' 		=> $this->_warnings,
			'aPrediggoConfirmations' 	=> $this->_confirmations,
			'aPrediggoErrors' 			=> $this->_errors,
			'path'						=> $this->_path,
			'lang_iso' 					=> $this->context->language->iso_code,
		));
		
		// Display the errors
		$this->_html .= $this->display(__FILE__, 'views/templates/admin/errors.tpl');
		
		// Display the tabs
		$this->_html .= $this->display(__FILE__, 'views/templates/admin/tabs.tpl');
		
		$this->_display = 'index';
		
		$iShopContext = (int)(Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP);
		
		/*
		 * MAIN CONFIGURATION
		 */
		$this->fields_form['main_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Main Configuration'),
				'image' => _PS_ADMIN_IMG_.'employee.gif'
			),
			'input' => array(
				array(
					'label' 	=> $this->l('Web Site ID'),
					'type' 		=> 'text',
					'name' 		=> 'web_site_id',
					'size'		=> 50,
					'required'	=> true,
					'desc' 		=> $this->l('Login of the Prediggo solution')
				),
				array(
					'label' 	=> $this->l('Profil ID'),
					'type' 		=> 'text',
					'name' 		=> 'gateway_profil_id',
					'size'		=> 50,
					'disabled'	=> 'disabled',
					'desc' 		=> $this->l('Profile ID of the current shop')
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'mainConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
				),
			),
		);
		
		$this->fields_value['web_site_id'] 			= $this->oPrediggoConfig->web_site_id;
		$this->fields_value['gateway_profil_id'] 	= (int)$this->context->shop->id;
		
		/*
		 * EXPORT MAIN CONFIGURATION
		 */
		$sCronFilePath	= Tools::getShopDomain(true).str_replace(_PS_ROOT_DIR_.'/', __PS_BASE_URI__, $this->_path).'crons/export.php?token='.Tools::getAdminToken('DataExtractorController');
		
		$this->fields_form['export_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Export Configuration'),
				'image' => _PS_ADMIN_IMG_.'cog.gif'
			),
			'input' => array(
				array(
					'label' 	=> $this->l('Logs storage activation'),
					'type' 		=> 'radio',
					'name' 		=> 'logs_file_generation',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'logs_file_generation_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'logs_file_generation_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('The logs files are stored in the folder "logs" of the module'),
				),
				array(
					'label' 	=> $this->l('Products file generation activation'),
					'type' 		=> 'radio',
					'name' 		=> 'products_file_generation',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'products_file_generation_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'products_file_generation_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Define if the products can be exported into a zip file for the use of the prediggo solution'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Orders file generation activation'),
					'type' 		=> 'radio',
					'name' 		=> 'orders_file_generation',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'orders_file_generation_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'orders_file_generation_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Define if the orders can be exported into a zip file for the use of the prediggo solution'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Customers file generation activation'),
					'type' 		=> 'radio',
					'name' 		=> 'customers_file_generation',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'customers_file_generation_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'customers_file_generation_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Define if the customers can be exported into a zip file for the use of the prediggo solution'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Product\'s image cover export activation'),
					'type' 		=> 'radio',
					'name' 		=> 'export_product_image',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'export_product_image_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'export_product_image_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Define if the product\'s image covers are included in the export of the products'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Product\'s description export activation'),
					'type' 		=> 'radio',
					'name' 		=> 'export_product_description',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'export_product_description_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'export_product_description_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Define if the product\'s descriptions are included in the export of the products'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Product minimum quantity:'),
					'type' 		=> 'text',
					'name' 		=> 'export_product_min_quantity',
					'required'	=> true,
					'desc' 		=> $this->l('Minimum quantity of a product to be exported'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of days considering that an order can be exported:'),
					'type' 		=> 'text',
					'name' 		=> 'nb_days_order_valide',
					'required'	=> true,
					'desc' 		=> $this->l('Number of days to select orders into the export by their date of creation'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of days considering that a customer can be exported:'),
					'type' 		=> 'text',
					'name' 		=> 'nb_days_customer_last_visit_valide',
					'required'	=> true,
					'desc' 		=> $this->l('Number of days to select customers into the export by their date of creation'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'hint',
					'name' 		=> 'protection_xml_path',
					'content' 	=> $this->l('If you want to execute the export by a cron, use the following link:').
									' <a href="'.$sCronFilePath.'">'.$sCronFilePath.'</a>.',
				),
				array(
					'label' 	=> $this->l('Launch the files export by clicking on the following button:'),
					'type' 		=> 'button',
					'name' 		=> 'manualExportSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Export files   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':'')
				),
			)
		);
		
		$this->fields_value['products_file_generation'] 			= (int)$this->oPrediggoConfig->products_file_generation;
		$this->fields_value['orders_file_generation'] 				= (int)$this->oPrediggoConfig->orders_file_generation;
		$this->fields_value['customers_file_generation'] 			= (int)$this->oPrediggoConfig->customers_file_generation;
		$this->fields_value['logs_file_generation'] 				= (int)$this->oPrediggoConfig->logs_file_generation;
		$this->fields_value['export_product_image'] 				= (int)$this->oPrediggoConfig->export_product_image;
		$this->fields_value['export_product_description'] 			= (int)$this->oPrediggoConfig->export_product_description;
		$this->fields_value['export_product_min_quantity'] 			= (int)$this->oPrediggoConfig->export_product_min_quantity;
		$this->fields_value['nb_days_order_valide']			 		= (int)$this->oPrediggoConfig->nb_days_order_valide;
		$this->fields_value['nb_days_customer_last_visit_valide'] 	= (int)$this->oPrediggoConfig->nb_days_customer_last_visit_valide;
		
		/*
		 * ATTRIBUTE SELECTION
		 */
		$this->fields_form['attributes_selection']['form'] = array(
			'legend' => array(
				'title' => $this->l('Prediggo attributes selection'),
				'image' => _PS_ADMIN_IMG_.'quick.gif'
			),
			'input' => array(
				array(
					'label' => $this->l('Attributes:'),
					'type' 	=> 'attribute_selector',
					'name' 	=> 'attribute_selector',
					'names' 	=> array(
						'attributes' 	=> 'attributes_groups_ids[]',
						'features'		=> 'features_ids[]'
					),
					'values' 	=> array(
						'attributes' 	=> AttributeGroup::getAttributesGroups((int)$this->context->cookie->id_lang),
						'features'		=> Feature::getFeatures((int)$this->context->cookie->id_lang)
					),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportPrediggoAttributesSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save the prediggo attributes   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);
				
		$this->fields_value['attribute_selector'] 	= array(
			'attributes' 	=> explode(',',$this->oPrediggoConfig->attributes_groups_ids),
			'features'		=> explode(',',$this->oPrediggoConfig->features_ids),
		);
		
		/*
		 * RECOMMANDATIONS BLACK LIST
		 */
		$this->fields_form['black_list_reco']['form'] = array(
			'legend' => array(
				'title' => $this->l('Products not included in the recommendations'),
				'image' => _PS_ADMIN_IMG_.'nav-logout.gif'
			),
			'input' => array(
				array(
					'label' 	=> $this->l('Product:'),
					'type' 		=> 'autocomplete',
					'name' 		=> 'products_ids_not_recommendable',
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportNotRecoSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save the black list   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);
		
		$this->fields_value['products_ids_not_recommendable'] = array();
		if(!empty($this->oPrediggoConfig->products_ids_not_recommendable))
			foreach(explode(',',$this->oPrediggoConfig->products_ids_not_recommendable) as $iID)
			{
				$oProduct = new Product((int)$iID, false, (int)$this->context->cookie->id_lang, (int)$this->context->shop->id, $this->context);
				$this->fields_value['products_ids_not_recommendable'][] = array(
					'id' 	=> (int)$iID,
					'name' 	=> $oProduct->name
				);
				unset($oProduct);
			}
			
		/*
		 * SEARCH BLACK LIST
		 */
		$this->fields_form['black_list_search']['form'] = array(
				'legend' => array(
					'title' => $this->l('Products not included in the searchs'),
					'image' => _PS_ADMIN_IMG_.'nav-logout.gif'
				),
				'input' => array(
					array(
						'label' 	=> $this->l('Product:'),
						'type' 		=> 'autocomplete',
						'name' 		=> 'products_ids_not_searchable',
						'disabled'	=> ((int)($iShopContext)?'disabled':''),
					),
					array(
						'type' 		=> 'button',
						'name' 		=> 'exportNotSearchSubmit',
						'class' 	=> 'button',
						'title' 	=> $this->l('   Save the black list   '),
						'disabled'	=> ((int)($iShopContext)?'disabled':''),
					),
				),
		);
		
		$this->fields_value['products_ids_not_searchable'] = array();
		if(!empty($this->oPrediggoConfig->products_ids_not_searchable))
			foreach(explode(',',$this->oPrediggoConfig->products_ids_not_searchable) as $iID)
			{
				$oProduct = new Product((int)$iID, false, (int)$this->context->cookie->id_lang, (int)$this->context->shop->id, $this->context);
				$this->fields_value['products_ids_not_searchable'][] = array(
					'id' 	=> (int)$iID,
					'name' 	=> $oProduct->name
				);
				unset($oProduct);
			}
		
		/* 
		 * HTACCESS PROTECTION 
		 */
		$sExportRepositoryPath	= Tools::getShopDomain(true).str_replace(_PS_ROOT_DIR_.'/', __PS_BASE_URI__, $this->_path).'xmlfiles/';
		
		$this->fields_form['htaccess_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Export File protection'),
				'image' => _PS_ADMIN_IMG_.'access.png'
			),
			'input' => array(
				array(
					'type' 		=> 'hint',
					'name' 		=> 'export_cron',
					'content' 	=> $this->l('Your files are created into your PrestaShop plateform into the following folder :').
					' <a href="'.$sExportRepositoryPath.'">'.$sExportRepositoryPath.'</a>.<br/>'.
					$this->l('The data contained into these files are critical and require a protection.').
					$this->l('Please restrict the access to these files by applying a protection with an authentication requiring a login and a password!').'<br/>'.
					$this->l('The Basic Authentication module is not configured by default on Nginx web server. Please, refer to this documentation to configure it : http://wiki.nginx.org/HttpAuthBasicModule.'),
				),
				array(
					'label' 	=> $this->l('Login:'),
					'type' 		=> 'text',
					'name' 		=> 'htpasswd_user',
					'required'	=> true,
					'desc' 		=> $this->l('Login of the export folder protection')
				),
				array(
					'label' 	=> $this->l('Password:'),
					'type' 		=> 'text',
					'name' 		=> 'htpasswd_pwd',
					'required'	=> true,
					'desc' 		=> $this->l('Password of the export folder protection')
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportProtectionConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
				),
			),
		);
		
		$this->fields_value['htpasswd_user'] 	= $this->oPrediggoConfig->htpasswd_user;
		$this->fields_value['htpasswd_pwd'] 	= $this->oPrediggoConfig->htpasswd_pwd;
		
		/* 
		 * RECOMMENDATIONS MAIN CONFIGURATION 
		 */
		$this->fields_form['recommendation_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Main recommendations settings'),
				'image' => _PS_ADMIN_IMG_.'page_world.png'
			),
			'input' => array(
				array(
					'label' => $this->l('Logs storage activation:'),
					'type' 		=> 'radio',
					'name' 		=> 'logs_reco_file_generation',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'logs_reco_file_generation_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'logs_reco_file_generation_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('The logs files are stored in the folder "logs" of the module'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('URL of the recommendations server:'),
					'type' 		=> 'text',
					'name' 		=> 'server_url_recommendations',
					'size'		=> 50,
					'required'	=> true,
					'desc' 		=> $this->l('Url called to get the recommendations from the prediggo solution'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'mainRecommendationConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);
		
		$this->fields_value['logs_reco_file_generation'] 	= (int)$this->oPrediggoConfig->logs_reco_file_generation;
		$this->fields_value['server_url_recommendations'] 	= $this->oPrediggoConfig->server_url_recommendations;
		
		/* 
		 * HOMEPAGE RECOMMENDATIONS 
		 */
		$this->fields_form['home_reco_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Home page configuration'),
				'image' => _PS_ADMIN_IMG_.'picture.gif'
			),
			'input' => array(
				array(
					'label' => $this->l('Display the recommendations block:'),
					'type' 		=> 'radio',
					'name' 		=> 'home_recommendations',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'home_recommendations_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'home_recommendations_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Add a block of recommended products in the homepage of your website'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of items in the recommendations block:'),
					'type' 		=> 'text',
					'name' 		=> 'home_nb_items',
					'desc' 		=> $this->l('Number of recommended products in the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Title of the recommendation block :'),
					'type' 		=> 'text',
					'size'		=> 50,
					'name' 		=> 'home_block_title',
					'lang' 		=> true,
					'desc' 		=> $this->l('Title of the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportHomeRecommendationConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);
		
		$this->fields_value['home_recommendations'] = (int)$this->oPrediggoConfig->home_recommendations;
		$this->fields_value['home_nb_items'] 		= (int)$this->oPrediggoConfig->home_nb_items;
		$this->fields_value['home_block_title'] 	= $this->oPrediggoConfig->home_block_title;

		/*
		 * 404 PAGE RECOMMENDATIONS
		 */
		$this->fields_form['error_reco_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('404 page configuration'),
				'image' => _PS_ADMIN_IMG_.'picture.gif'
			),
			'input' => array(
				array(
					'label' => $this->l('Display the recommendations block:'),
					'type' 		=> 'radio',
					'name' 		=> 'error_recommendations',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'error_recommendations_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'error_recommendations_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Add a block of recommended products in the 404 page of your website'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of items in the recommendations block:'),
					'type' 		=> 'text',
					'name' 		=> 'error_nb_items',
					'desc' 		=> $this->l('Number of recommended products in the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Title of the recommendation block :'),
					'type' 		=> 'text',
					'name' 		=> 'error_block_title',
					'size'		=> 50,
					'lang' 		=> true,
					'desc' 		=> $this->l('Title of the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'export404RecommendationConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);
		
		$this->fields_value['error_recommendations']	= (int)$this->oPrediggoConfig->error_recommendations;
		$this->fields_value['error_nb_items'] 			= (int)$this->oPrediggoConfig->error_nb_items;
		$this->fields_value['error_block_title'] 		= $this->oPrediggoConfig->error_block_title;
		
		/*
		 * PRODUCT PAGE RECOMMENDATIONS
		 */
		$this->fields_form['product_reco_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Product page configuration'),
				'image' => _PS_ADMIN_IMG_.'picture.gif'
			),
			'input' => array(
				array(
					'label' => $this->l('Display the recommendations block:'),
					'type' 		=> 'radio',
					'name' 		=> 'product_recommendations',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'product_recommendations_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'product_recommendations_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Add a block of recommended products in the products pages of your website'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of items in the recommendations block:'),
					'type' 		=> 'text',
					'name' 		=> 'product_nb_items',
					'desc' 		=> $this->l('Number of recommended products in the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Title of the recommendation block :'),
					'type' 		=> 'text',
					'name' 		=> 'product_block_title',
					'size'		=> 50,
					'lang' 		=> true,
					'desc' 		=> $this->l('Title of the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportProductRecommendationConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);
		
		$this->fields_value['product_recommendations']	= (int)$this->oPrediggoConfig->product_recommendations;
		$this->fields_value['product_nb_items'] 		= (int)$this->oPrediggoConfig->product_nb_items;
		$this->fields_value['product_block_title'] 		= $this->oPrediggoConfig->product_block_title;
		
		/*
		 * CATEGORY PAGE RECOMMENDATIONS
		 */
		$this->fields_form['category_reco_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Category page configuration'),
				'image' => _PS_ADMIN_IMG_.'picture.gif'
			),
			'input' => array(
				array(
					'label' => $this->l('Display the recommendations block:'),
					'type' 		=> 'radio',
					'name' 		=> 'category_recommendations',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'category_recommendations_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'category_recommendations_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Add a block of recommended products in the categories pages of your website'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of items in the recommendations block:'),
					'type' 		=> 'text',
					'name' 		=> 'category_nb_items',
					'desc' 		=> $this->l('Number of recommended products in the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Title of the recommendation block :'),
					'type' 		=> 'text',
					'name' 		=> 'category_block_title',
					'size'		=> 50,
					'lang' 		=> true,
					'desc' 		=> $this->l('Title of the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportCategoryRecommendationConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);
		
		$this->fields_value['category_recommendations']	= (int)$this->oPrediggoConfig->category_recommendations;
		$this->fields_value['category_nb_items'] 		= (int)$this->oPrediggoConfig->category_nb_items;
		$this->fields_value['category_block_title'] 	= $this->oPrediggoConfig->category_block_title;
		
		/*
		 * CUSTOMER PAGE RECOMMENDATIONS
		 */
		$this->fields_form['customer_reco_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Customers pages configuration'),
				'image' => _PS_ADMIN_IMG_.'picture.gif'
			),
			'input' => array(
				array(
					'label' => $this->l('Display the recommendations block:'),
					'type' 		=> 'radio',
					'name' 		=> 'customer_recommendations',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'customer_recommendations_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'customer_recommendations_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Add a block of recommended products in the customers pages of your website'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of items in the recommendations block:'),
					'type' 		=> 'text',
					'name' 		=> 'customer_nb_items',
					'desc' 		=> $this->l('Number of recommended products in the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label'		=> $this->l('Title of the recommendation block :'),
					'type' 		=> 'text',
					'name' 		=> 'customer_block_title',
					'size'		=> 50,
					'lang' 		=> true,
					'desc' 		=> $this->l('Title of the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportCustomerRecommendationConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);
		
		$this->fields_value['customer_recommendations']	= (int)$this->oPrediggoConfig->customer_recommendations;
		$this->fields_value['customer_nb_items'] 		= (int)$this->oPrediggoConfig->customer_nb_items;
		$this->fields_value['customer_block_title'] 	= $this->oPrediggoConfig->customer_block_title;
		
		/*
		 * CART PAGE RECOMMENDATIONS
		 */
		$this->fields_form['cart_reco_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Cart page configuration'),
				'image' => _PS_ADMIN_IMG_.'picture.gif'
			),
			'input' => array(
				array(
					'label' => $this->l('Display the recommendations block:'),
					'type' 		=> 'radio',
					'name' 		=> 'cart_recommendations',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'cart_recommendations_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'cart_recommendations_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Add a block of recommended products in the cart page of your website'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of items in the recommendations block:'),
					'type' 		=> 'text',
					'name' 		=> 'cart_nb_items',
					'desc' 		=> $this->l('Number of recommended products in the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Title of the recommendation block :'),
					'type' 		=> 'text',
					'name' 		=> 'cart_block_title',
					'size'		=> 50,
					'lang' 		=> true,
					'desc' 		=> $this->l('Title of the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportCartRecommendationConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);
		
		$this->fields_value['cart_recommendations']	= (int)$this->oPrediggoConfig->cart_recommendations;
		$this->fields_value['cart_nb_items'] 		= (int)$this->oPrediggoConfig->cart_nb_items;
		$this->fields_value['cart_block_title'] 	= $this->oPrediggoConfig->cart_block_title;
		
		/*
		 * BLOCK LAYERED MODULE RECOMMENDATIONS
		 */
		$this->fields_form['blocklayered_reco_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Block layered module configuration'),
				'image' => _PS_ADMIN_IMG_.'picture.gif'
			),
			'input' => array(
				array(
					'label' 	=> $this->l('Display the recommendations block:'),
					'type' 		=> 'radio',
					'name' 		=> 'blocklayered_recommendations',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'blocklayered_recommendations_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'blocklayered_recommendations_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Add a block of recommended products in the block layered module'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of items in the recommendations block:'),
					'type' 		=> 'text',
					'name' 		=> 'blocklayered_nb_items',
					'desc' 		=> $this->l('Number of recommended products in the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label'		=> $this->l('Title of the recommendation block :'),
					'type' 		=> 'text',
					'name' 		=> 'blocklayered_block_title',
					'size'		=> 50,
					'lang' 		=> true,
					'desc' 		=> $this->l('Title of the block'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'exportBlocklayeredRecommendationConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);
		
		$this->fields_value['blocklayered_recommendations']	= (int)$this->oPrediggoConfig->blocklayered_recommendations;
		$this->fields_value['blocklayered_nb_items'] 		= (int)$this->oPrediggoConfig->blocklayered_nb_items;
		$this->fields_value['blocklayered_block_title'] 	= $this->oPrediggoConfig->blocklayered_block_title;
		
		/*
		 * SEARCH MAIN CONFIGURATION
		 */
		$this->fields_form['search_conf']['form'] = array(
			'legend' => array(
				'title' => $this->l('Main search settings'),
				'image' => _PS_ADMIN_IMG_.'search.gif'
			),
			'input' => array(	
				array(
					'label' => $this->l('Display the search block:'),
					'type' 		=> 'radio',
					'name' 		=> 'search_active',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'search_active_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'search_active_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Enable the search block in the front office'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Number of items per page:'),
					'type' 		=> 'text',
					'name' 		=> 'search_nb_items',
					'desc' 		=> $this->l('Number of products per page'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('Minimum number of chars to launch a search:'),
					'type' 		=> 'text',
					'name' 		=> 'search_nb_min_chars',
					'desc' 		=> $this->l('Minimum number of character to allow the user to execute a search'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' => $this->l('Logs storage activation:'),
					'type' 		=> 'radio',
					'name' 		=> 'logs_search_file_generation',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'logs_search_file_generation_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'logs_search_file_generation_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('The logs files are stored in the folder "logs" of the module'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' 	=> $this->l('URL of the search server:'),
					'type' 		=> 'text',
					'name' 		=> 'server_url_search',
					'desc' 		=> $this->l('Url called to get the search from the prediggo solution'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' => $this->l('Searchandizing activation:'),
					'type' 		=> 'radio',
					'name' 		=> 'searchandizing_active',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'searchandizing_active_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'searchandizing_active_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Enable the searchandizing block in the front office'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'label' => $this->l('Layered navigation activation:'),
					'type' 		=> 'radio',
					'name' 		=> 'layered_navigation_active',
					'class' 	=> 't',
					'is_bool' 	=> true,
					'values' 	=> array(
						array(
							'id' 	=> 'layered_navigation_active_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'layered_navigation_active_off',
							'value' => 0,
							'label' => $this->l('No')
						),
					),
					'required'	=> true,
					'desc' 		=> $this->l('Enable the prediggo layered navigation in the search page of the front office'),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
				array(
					'type' 		=> 'button',
					'name' 		=> 'mainSearchConfSubmit',
					'class' 	=> 'button',
					'title' 	=> $this->l('   Save   '),
					'disabled'	=> ((int)($iShopContext)?'disabled':''),
				),
			),
		);
		
		$this->fields_value['search_active']				= (int)$this->oPrediggoConfig->search_active;
		$this->fields_value['search_nb_items'] 				= (int)$this->oPrediggoConfig->search_nb_items;
		$this->fields_value['search_nb_min_chars'] 			= (int)$this->oPrediggoConfig->search_nb_min_chars;		
		$this->fields_value['logs_search_file_generation'] 	= (int)$this->oPrediggoConfig->logs_search_file_generation;
		$this->fields_value['server_url_search'] 			= $this->oPrediggoConfig->server_url_search;
		$this->fields_value['searchandizing_active'] 		= (int)$this->oPrediggoConfig->searchandizing_active;
		$this->fields_value['layered_navigation_active'] 	= (int)$this->oPrediggoConfig->layered_navigation_active;
		
		/*
		 * SEARCH AUTOCOMPLETION CONFIGURATION
		*/
		$this->fields_form['search_autocompletion_conf']['form'] = array(
				'legend' => array(
					'title' => $this->l('Autocompletion configuration'),
					'image' => _PS_ADMIN_IMG_.'download_page.png'
				),
				'input' => array(
					array(
						'label' => $this->l('Autocompletion activation:'),
						'type' 		=> 'radio',
						'name' 		=> 'autocompletion_active',
						'class' 	=> 't',
						'is_bool' 	=> true,
						'values' 	=> array(
							array(
								'id' 	=> 'autocompletion_active_on',
								'value' => 1,
								'label' => $this->l('Yes')
							),
							array(
								'id' 	=> 'autocompletion_active_off',
								'value' => 0,
								'label' => $this->l('No')
							),
						),
						'required'	=> true,
						'desc' 		=> $this->l('Enable the prediggo autocompletion to propose words when the customer is typing his search'),
						'disabled'	=> ((int)($iShopContext)?'disabled':''),
					),
					array(
						'label' 	=> $this->l('Number of items in the search autocompletion:'),
						'type' 		=> 'text',
						'name' 		=> 'autocompletion_nb_items',
						'desc' 		=> $this->l('Number of products displayed in the prediggo autocompletion'),
						'disabled'	=> ((int)($iShopContext)?'disabled':''),
					),
					array(
						'label' => $this->l('Suggestion activation:'),
						'type' 		=> 'radio',
						'name' 		=> 'suggest_active',
						'class' 	=> 't',
						'is_bool' 	=> true,
						'values' 	=> array(
							array(
								'id' 	=> 'suggest_active_on',
								'value' => 1,
								'label' => $this->l('Yes')
							),
							array(
								'id' 	=> 'suggest_active_off',
								'value' => 0,
								'label' => $this->l('No')
							),
						),
						'required'	=> true,
						'desc' 		=> $this->l('Enable the prediggo suggestion to propose words when the customer is typing his search'),
						'disabled'	=> ((int)($iShopContext)?'disabled':''),
					),
					array(
						'label' 	=> $this->l('List of suggestion:'),
						'type' 		=> 'text',
						'name' 		=> 'suggest_words',
						'lang' 		=> true,
						'desc' 		=> $this->l('List of keywords separated by comma (iPad 2, iPhone 4S, iPhone)'),
						'disabled'	=> ((int)($iShopContext)?'disabled':''),
					),
					array(
						'type' 		=> 'button',
						'name' 		=> 'exportSearchAutocompletionConfSubmit',
						'class' 	=> 'button',
						'title' 	=> $this->l('   Save   '),
						'disabled'	=> ((int)($iShopContext)?'disabled':''),
					),
				),
		);
		
		$this->fields_value['autocompletion_active']	= (int)$this->oPrediggoConfig->autocompletion_active;
		$this->fields_value['autocompletion_nb_items'] 	= (int)$this->oPrediggoConfig->autocompletion_nb_items;
		$this->fields_value['suggest_active'] 			= (int)$this->oPrediggoConfig->suggest_active;
		$this->fields_value['suggest_words'] 			= $this->oPrediggoConfig->suggest_words;
		
		$this->context->controller->getLanguages();
		$helper = $this->initForm();
		$helper->submit_action = '';
		
		$helper->title = $this->l('Prediggo configuration');
		if (Shop::getContext() == Shop::CONTEXT_SHOP)
			$helper->title .= ' [ '.$this->l('Shop').' : '.$this->context->shop->name.' ]';
		else
			$helper->title .= ' [ '.$this->l('All shops').' ]';
		
		
		
		$helper->fields_value = $this->fields_value;
		$this->_html .= $helper->generateForm($this->fields_form);
	}
	
	private function initForm()
	{
		$helper = new HelperForm();
	
		$helper->module = $this;
		$helper->name_controller = 'prediggo';
		$helper->identifier = $this->identifier;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->languages = $this->context->controller->_languages;
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		$helper->default_form_language = $this->context->controller->default_form_language;
		$helper->allow_employee_form_lang = $this->context->controller->allow_employee_form_lang;
	
		return $helper;
	}
}