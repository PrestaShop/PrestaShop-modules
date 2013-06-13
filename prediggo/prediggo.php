<?php

/**
 * @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
 * @copyright Croissance NET
 * @version 1.0
 */

if (!defined('_PS_VERSION_'))
  exit;

require_once(_PS_MODULE_DIR_.'prediggo/classes/PrediggoConfig.php');
require_once(_PS_MODULE_DIR_.'prediggo/classes/PrediggoExportConfig.php');
require_once(_PS_MODULE_DIR_.'prediggo/classes/PrediggoRecommendationConfig.php');
require_once(_PS_MODULE_DIR_.'prediggo/classes/PrediggoSearchConfig.php');
require_once(_PS_MODULE_DIR_.'prediggo/controllers/DataExtractorController.php');
require_once(_PS_MODULE_DIR_.'prediggo/controllers/PrediggoCallController.php');

class Prediggo extends Module
{
	/** @var PrediggoConfig Object PrediggoConfig */
	public $oPrediggoConfig;

	/** @var PrediggoExportConfig Object PrediggoExportConfig */
	public $oPrediggoExportConfig;

	/** @var PrediggoRecommendationConfig Object PrediggoRecommendationConfig */
	public $oPrediggoRecommendationConfig;

	/** @var PrediggoSearchConfig Object PrediggoSearchConfig */
	public $oPrediggoSearchConfig;

	/** @var DataExtractorController Object DataExtractorController */
	public $oDataExtractorController;

	/** @var PrediggoCallController Object PrediggoCallController */
	public $oPrediggoCallController;

	/** @var array list of errors */
	public $_errors = array();

	/** @var array list of confirmations */
	public $_confirmations = array();

	/** @var array list of warnings */
	public $_warnings = array();

	/** @var array list of Products by hook */
	public $aRecommendations;

	/**
	  * Initialise the object variables
	  */
	public function __construct()
	{
		$this->name = 'prediggo';
		$this->tab = 'front_office_features';
		$this->version = '1.8';
		$this->author = 'Croissance Net';
		$this->need_instance = 1;

		parent::__construct();

		$this->displayName = $this->l('Prediggo');
		$this->description = $this->l('Offers interactive products recommendations in the front office');

		/* Set the Configuration Object */
		$this->oPrediggoConfig = PrediggoConfig::singleton();
		$this->oPrediggoExportConfig = PrediggoExportConfig::singleton();
		$this->oPrediggoRecommendationConfig = PrediggoRecommendationConfig::singleton();
		$this->oPrediggoSearchConfig = PrediggoSearchConfig::singleton();

		/* Set the main controllers */
		$this->oDataExtractorController = new DataExtractorController($this);
		$this->oPrediggoCallController = new PrediggoCallController();

		$this->aRecommendations = array();

		// Check configuration of the server
		$this->checkServerConfiguration();
	}

	/**
	 * Install Procedure
	 */
	public function install()
	{

		// Set an url rewriting for prediggo search page
		$oMeta = new Meta();
		$oMeta->page = 'search_prediggo';
		$oMeta->title = array(1 => '', 2 => '');
		$oMeta->description = array(1 => '', 2 => '');
		$oMeta->keywords = array(1 => '', 2 => '');
		$oMeta->url_rewrite = array(1 => 'prediggo_search', 2 => 'prediggo_recherche');

		// Set the hook registration
		return 	($oMeta->save()
				&& Db::getInstance()->autoExecute(_DB_PREFIX_.'meta', array('page' => 'modules/prediggo/prediggo_search'), 'UPDATE', '`page` = "search_prediggo"')
				&& Tools::generateHtaccess(dirname(__FILE__).'/../../.htaccess', Configuration::get('PS_REWRITING_SETTINGS'), Configuration::get('PS_HTACCESS_CACHE_CONTROL'), Configuration::get('PS_HTACCESS_SPECIFIC'))
				&& parent::install()
				&& $this->oPrediggoConfig->install()
				&& $this->oPrediggoExportConfig->install()
				&& $this->oPrediggoRecommendationConfig->install()
				&& $this->oPrediggoSearchConfig->install()
				&& $this->registerHook('header')
				&& $this->registerHook('top')
				&& $this->registerHook('leftColumn')
				&& $this->registerHook('rightColumn')
		        && $this->registerHook('footer')
		        && $this->registerHook('authentication')
				&& $this->registerHook('createAccount')
				&& $this->registerHook('backOfficeHeader')
				&& $this->registerHook('paymentTop')
				);
  	}

	/**
	 * Uninstall Procedure
	 */
	public function uninstall()
	{
	    return	($this->oPrediggoConfig->uninstall()
	    		&& $this->oPrediggoExportConfig->uninstall()
	    		&& $this->oPrediggoRecommendationConfig->uninstall()
	    		&& $this->oPrediggoSearchConfig->uninstall()
	    		&& Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'meta` WHERE `page` = "modules/prediggo/search"')
				&& parent::uninstall()
	        	);
  	}

  	/**
	 * Hook Header : Add Media CSS & JS
	 *
	 * @param array $params list of specific data
	 */
	public function hookHeader($params)
  	{
  		if (!isset($params['cookie']->id_guest))
  			Guest::setNewGuest($params['cookie']);
  		
  		
  		// Check if prediggo module can be executed in this page
  		if($this->oPrediggoCallController->isPageAccessible()
  		|| $this->oPrediggoCallController->getPageName() == 'prediggo_search'
  		|| $this->oPrediggoSearchConfig->search_active)
  		{
  			Tools::addCSS(($this->_path).'css/'.($this->name).'.css', 'all');
  			Tools::addJS(array(
  				($this->_path).'js/prediggo_autocomplete.js',
  				($this->_path).'js/'.($this->name).'.js'
  			));
  		}
  	}

  	/**
	 * Hook Top : Display the prediggo search block
	 *
	 * @param array $params list of specific data
	 */
	public function hookTop($params)
  	{
  		return $this->displaySearchBlock($params);
  	}

  	/**
	 * Hook Left Column : Display the recommendations
	 *
	 * @param array $params list of specific data
	 */
	public function hookLeftColumn($params)
  	{
  		// Get list of recommendations
  		return $this->displayRecommendations('left_column', $params).$this->displaySearchFilterBlock($params);
  	}

  	/**
	 * Hook Right Column : Display the recommendations
	 *
	 * @param array $params list of specific data
	 */
	public function hookRightColumn($params)
  	{
  		// Get list of recommendations
  		return $this->displayRecommendations('right_column', $params);
  	}

  	/**
	 * Hook Footer : Display the recommendations
	 *
	 * @param array $params list of specific data
	 */
	public function hookFooter($params)
  	{
  		// Get list of recommendations
  		return $this->displayRecommendations('footer', $params);
  	}

	/**
	 * Hook Authentication : Notify prediggo that the user is authenticated
	 *
	 * @param array $params list of specific data
	 */
	public function hookAuthentication($params)
  	{
  		$this->oPrediggoCallController->notifyPrediggo('user', $params);
  	}
  	
	/**
	 * Hook Payment Top : Notify prediggo that the user is authenticated
	 *
	 * @param array $params list of specific data
	 */
	public function hookPaymentTop($params)
  	{
  		$this->oPrediggoCallController->notifyPrediggo('user', $params);
  	}
  	
  	/**
  	 * Hook Create Account : Notify prediggo that the user is authenticated
  	 *
  	 * @param array $params list of specific data
  	 */
  	public function hookCreateAccount($params)
  	{
  		$this->oPrediggoCallController->notifyPrediggo('user', $params);
  	}

  	/**
	 * Hook Authentication : Notify prediggo that a recommendations has been clicked
	 *
	 * @param array $params list of specific data
	 */
	public function setProductNotification($params)
  	{
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
  		if(!$this->oPrediggoSearchConfig->layered_navigation_active)
  			return;

  		global $smarty;

  		$smarty->assign(array(
  			'varTranslated' => array(
		  		'SEARCH_SORTING_CODE_0' => $this->l('SEARCH_SORTING_CODE_0'),
		  		'SEARCH_SORTING_CODE_1' => $this->l('SEARCH_SORTING_CODE_1'),
		  		'SEARCH_SORTING_CODE_2' => $this->l('SEARCH_SORTING_CODE_2'),
		  		'SEARCH_SORTING_CODE_3' => $this->l('SEARCH_SORTING_CODE_3'),
		  		'SEARCH_SORTING_CODE_4' => $this->l('SEARCH_SORTING_CODE_4'),
		  		'SEARCH_SORTING_CODE_5' => $this->l('SEARCH_SORTING_CODE_5'),
		  		'SEARCH_SORTING_CODE_6' => $this->l('SEARCH_SORTING_CODE_6'),
		  		'SEARCH_SORTING_CODE_7' => $this->l('SEARCH_SORTING_CODE_7'),
		  		'SEARCH_SORTING_CODE_8' => $this->l('SEARCH_SORTING_CODE_8'),
		  		'SEARCH_SORTING_CODE_9' => $this->l('SEARCH_SORTING_CODE_9'),
		  		'SEARCH_SORTING_CODE_10' => $this->l('SEARCH_SORTING_CODE_10'),
		  		'SEARCH_SORTING_CODE_11' => $this->l('SEARCH_SORTING_CODE_11'),
		  		'SEARCH_SORTING_CODE_12' => $this->l('SEARCH_SORTING_CODE_12'),
		  		'SEARCH_SORTING_CODE_13' => $this->l('SEARCH_SORTING_CODE_13'),
		  		'SEARCH_SORTING_CODE_14' => $this->l('SEARCH_SORTING_CODE_14'),
		  		'SEARCH_SORTING_CODE_15' => $this->l('SEARCH_SORTING_CODE_15'),
		  		'SEARCH_SORTING_CODE_16' => $this->l('SEARCH_SORTING_CODE_16'),
		  		'SEARCH_SORTING_CODE_17' => $this->l('SEARCH_SORTING_CODE_17'),
		  		'SEARCH_SORTING_CODE_18' => $this->l('SEARCH_SORTING_CODE_18'),
		  		'SEARCH_SORTING_CODE_19' => $this->l('SEARCH_SORTING_CODE_19'),
		  		'SEARCH_SORTING_CODE_20' => $this->l('SEARCH_SORTING_CODE_20'),
				'sellingprice' => $this->l('sellingprice'),
		  		'genre' => $this->l('genre'),
		  		'brand' => $this->l('brand')
		  	)
  		));

  		return $this->display(__FILE__, 'search_filters_block.tpl');
  	}

  	/**
	 * Display the search block
	 *
	 * @param array $params list of specific data
	 * @return string Html
	 */
	private function displaySearchBlock($params)
  	{
  		if($this->oPrediggoSearchConfig->search_active)
  			return $this->display(__FILE__, 'search_block.tpl');
  	}

  	/**
	 * Display the did you mean suggestion
	 *
	 * @return string Html
	 */
	public function displayAutocompleteDidYouMean()
  	{
  		if($this->oPrediggoSearchConfig->search_active
  		&& $this->oPrediggoSearchConfig->autocompletion_active)
  			return $this->display(__FILE__, 'autocomplete_dum.tpl');
  	}

  	/**
	 * Display the autocompletion products
	 *
	 * @return string Html
	 */
	public function displayAutocompleteProduct()
  	{
  		if($this->oPrediggoSearchConfig->search_active
  		&& $this->oPrediggoSearchConfig->autocompletion_active)
  			return $this->display(__FILE__, 'autocomplete_product.tpl');
  	}

  	/**
	 * Display the autocompletion suggestions
	 *
	 * @return string Html
	 */
	public function displayAutocompleteSuggest()
  	{
  		if($this->oPrediggoSearchConfig->search_active
  		&& $this->oPrediggoSearchConfig->autocompletion_active)
  			return $this->display(__FILE__, 'autocomplete_suggest.tpl');
  	}

  	/**
	 * Get the recommendations from the blocklayered filters
	 *
	 * @param array $params list of specific data
	 * @return array $aData containing the front office block
	 */
	public function getBlockLayeredRecommendations($params)
  	{
		require_once(dirname(__FILE__).'/../blocklayered/blocklayered.php');
		$oBlockLayered = new BlockLayered();
		// Check if the current version the stable one
		if($oBlockLayered->version < 1.4)
			return;

		$aData = Tools::jsonDecode($oBlockLayered->ajaxCall());

		$sHookName = 'blocklayered';
		$this->oPrediggoCallController->_setPageName($sHookName);
		$params['filters'] = $this->getSelectedFilters();

  		if(!$this->aRecommendations[$sHookName] = $this->oPrediggoCallController->getListOfRecommendations($sHookName, $params))
  			return false;

  		global $smarty;
  		// Display Main Configuration management
  		$smarty->assign(array(
  		  	'hook_name' => $sHookName,
  		  	'page_name' => 'category',
  		  	'aRecommendations' => $this->aRecommendations,
  		  	'tax_enabled' => (int)Configuration::get('PS_TAX'),
  		  	'display_qties' => (int)Configuration::get('PS_DISPLAY_QTIES'),
  		  	'display_ht' => !Tax::excludeTaxeOption()
  		));

  		$aData->productList = $this->display(__FILE__, 'fo_list_reco.tpl').$aData->productList;
  		return $aData;
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
		if (strpos($_SERVER['SCRIPT_FILENAME'], 'ajax.php') === false || Tools::getValue('selected_filters') !== false)
		{
			if (Tools::getValue('selected_filters'))
				$url = Tools::safeOutput(Tools::getValue('selected_filters'));
			else
				$url = preg_replace('/\/(?:\w*)\/(?:[0-9]+[-\w]*)([^\?]*)\??.*/', '$1', Tools::safeOutput($_SERVER['REQUEST_URI'], true));

			$urlAttributes = explode('/', $url);
			array_shift($urlAttributes);
			$selectedFilters = array('category' => array());
			if (!empty($urlAttributes))
			{
				foreach ($urlAttributes as $urlAttribute)
				{
					$urlParameters = explode('-', $urlAttribute);
					$attributeName  = array_shift($urlParameters);
					if (in_array($attributeName, array('price', 'weight')))
						$selectedFilters[$attributeName] = array($urlParameters[0], $urlParameters[1]);
					else
					{
						foreach ($urlParameters as $urlParameter)
						{
							$data = Db::getInstance()->getValue('SELECT data FROM `'._DB_PREFIX_.'layered_friendly_url` WHERE `url_key` = \''.md5('/'.$attributeName.'-'.$urlParameter).'\'');
							if ($data)
								foreach (unserialize($data) as $keyParams => $params)
								{
									if (!isset($selectedFilters[$keyParams]))
										$selectedFilters[$keyParams] = array();
									foreach ($params as $keyParam => $param)
									{
										if (!isset($selectedFilters[$keyParams][$keyParam]))
											$selectedFilters[$keyParams][$keyParam] = array();
										$selectedFilters[$keyParams][$keyParam] = $param;
									}
								}
						}
					}
				}
				return $selectedFilters;
			}
		}

		/* Analyze all the filters selected by the user and store them into a tab */
		$selectedFilters = array('category' => array(), 'manufacturer' => array(), 'quantity' => array(), 'condition' => array());
		foreach ($_GET as $key => $value)
			if (substr($key, 0, 8) == 'layered_')
			{
				preg_match('/^(.*)_[0-9|new|used|refurbished|slider]+$/', substr($key, 8, strlen($key) - 8), $res);
				if (isset($res[1]))
				{
					$tmpTab = explode('_', $value);
					$value = $tmpTab[0];
					$id_key = false;
					if (isset($tmpTab[1]))
						$id_key = $tmpTab[1];
					if ($res[1] == 'condition' && in_array($value, array('new', 'used', 'refurbished')))
						$selectedFilters['condition'][] = $value;
					else if ($res[1] == 'quantity' && (!$value || $value == 1))
						$selectedFilters['quantity'][] = $value;
					else if (in_array($res[1], array('category', 'manufacturer')))
					{
						if (!isset($selectedFilters[$res[1].($id_key ? '_'.$id_key : '')]))
							$selectedFilters[$res[1].($id_key ? '_'.$id_key : '')] = array();
						$selectedFilters[$res[1].($id_key ? '_'.$id_key : '')][] = (int)$value;
					}
					else if (in_array($res[1], array('id_attribute_group', 'id_feature')))
					{
						if (!isset($selectedFilters[$res[1]]))
							$selectedFilters[$res[1]] = array();
						$selectedFilters[$res[1]][(int)$value] = $id_key.'_'.(int)$value;
					}
					else if ($res[1] == 'weight')
						$selectedFilters[$res[1]] = $tmpTab;
					else if ($res[1] == 'price')
						$selectedFilters[$res[1]] = $tmpTab;
				}
			}
		return $selectedFilters;
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
  		if(!$this->aRecommendations[$sHookName] = $this->oPrediggoCallController->getListOfRecommendations($sHookName, $params))
  			return false;

  		global $smarty;

  		// Display Main Configuration management
  		$smarty->assign(array(
  		  	'hook_name' => $sHookName,
  		  	'aRecommendations' => $this->aRecommendations,

  		  	'tax_enabled' => (int)Configuration::get('PS_TAX'),
  		  	'display_qties' => (int)Configuration::get('PS_DISPLAY_QTIES'),
  		  	'display_ht' => !Tax::excludeTaxeOption()
  		));

  		return $this->display(__FILE__, 'fo_list_reco.tpl');
  	}

  	/**
	 * Hook Back office Header : Add Media for the administration panel
	 *
	 * @param array $params list of specific data
	 * @return string Html
	 */
	public function hookBackOfficeHeader($params)
  	{
  		if (_PS_VERSION_ >= 1.4) {
			$sHTML = '
			<link type="text/css" rel="stylesheet" href="'.$this->_path.'js/jqueryui/1.8.9/themes/smoothness/jquery-ui-1.8.9.custom.css" />
			<script type="text/javascript" src="'.$this->_path.'js/jqueryui/1.8.9/jquery-ui-1.8.9.custom.min.js"></script>
			';
		}
		else {
			$sHTML = '<link type="text/css" rel="stylesheet" href="'.$this->_path.'js/jqueryui/themes/default/ui.all.css" />
  	  		<script type="text/javascript" src="'.$this->_path.'js/ui.core.min.js"></script>
			<script type="text/javascript" src="'.$this->_path.'js/ui.tabs.min.js"></script>
			<script type="text/javascript" src="'.$this->_path.'js/bo_prediggo.js"></script>
  	  		';
		}

		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));

		return $sHTML.'
			<link rel="stylesheet" type="text/css" href="'.$this->_path.'css/jquery.autocomplete.css" />
  	  		<script type="text/javascript" src="'.$this->_path.'js/jquery.autocomplete.js"></script>
			<script type="text/javascript" src="'.$this->_path.'js/bo_prediggo.js"></script>
			<script type="text/javascript">
				var id_language = Number('.$defaultLanguage.');
			</script>
			';
  	}

  	/**
	 * Check the server configuration variables
	 */
	public function checkServerConfiguration()
  	{
  		$this->_warnings = array();
  		$this->_errors = array();

  		// API can't be call if curl extension is not installed on PHP config.
  		if (!extension_loaded('curl'))
  			$this->_warnings[] = $this->l('Please activate the PHP extension "curl" to allow use of Prediggo.');

  		if (!extension_loaded('dom'))
  			$this->_warnings[] = $this->l('Please activate the PHP extension "dom" to allow use of Prediggo.');

  		if((int)ini_get('max_execution_time') < 3000)
  			$this->_warnings[] = $this->l('Please update the PHP option "max_execution_time" to a minimum of "3000". (Current value : ').(int)ini_get('max_execution_time').$this->l(')');

  		if((int)ini_get('max_input_time') < 3000)
  			$this->_warnings[] = $this->l('Please update the PHP option "max_execution_time" to a minimum of "3000". (Current value : ').(int)ini_get('max_input_time').$this->l(')');

  		if((int)ini_get('memory_limit') < 384)
  			$this->_warnings[] = $this->l('Please update the PHP option "max_execution_time" to a minimum of "384M". (Current value : ').ini_get('memory_limit').$this->l(')');

  		if(empty($this->oPrediggoExportConfig->htpasswd_user))
  			$this->_errors[] = $this->l('The export folder is not protected!').'<br/>'.$this->l('Please set a protection from the part "Export File protection" of the module configuration.').'<br/> => '.$this->oDataExtractorController->getRepositoryPath();


  		// Set a new warning to display on the top of the list of module in AdminModules Tab
  		$this->warning = '';
  		foreach($this->_warnings as $sWarning)
  			$this->warning .= '[ '.$sWarning.' ] ';

  		// Check if blocklayered is installed =>
  		// so put prediggo registration after layered registration due to js override
  		if($oModule = Module::getInstanceByName('blocklayered'))
  		{
  			if (_PS_VERSION_ >= '1.5')
  				$id_hook = (int)Hook::getIdByName('displayHeader');
	  		else
	  			$id_hook = (int)Hook::get('header');
	  		if(($iPos = (int)$oModule->getPosition((int)$id_hook)) &&
			(int)$this->getPosition((int)$id_hook) < (int)$iPos)
				$this->updatePosition((int)$id_hook, true, (int)$iPos);
  		}
  	}

	/**
	 * BO main function
	 *
	 * @return string Html
	 */
	public function getContent()
  	{
  		if (!empty($_POST))
	    {
			// Process POST data verfication
	    	$postErrors = $this->_postValidation();
			if (!count($postErrors))
			{
				// Check configuration of the server
				$this->checkServerConfiguration();

				// Process POST data storage and execute actions
				$this->_postProcess();
			}
			else
			{
				$this->_errors['postErrors'] = $this->l('The settings cannot be updated due to settings contents errors :');
				foreach($postErrors as $postError)
					$this->_errors['postErrors'] .= '<br/>'.$postError;
			}
	    }

		// Display forms
	    $this->_displayForm();

	    return $this->_html;
  	}

  	/**
	 * Validate the data once an updated is processed in the BO
	 *
	 * @return array $postErrors list of errors
	 */
	private function _postValidation()
  	{
  		$postErrors = array();

  		if (Tools::isSubmit('mainConfSubmit'))
	    {
	      	if (!Tools::getValue('prediggo_web_site_id')
	      	|| !Validate::isGenericName(Tools::getValue('prediggo_default_profile_id')))
	        	$postErrors[] = $this->l('Web Site ID is required.');
	     	if (!Tools::getValue('prediggo_store_code_id')
	     	|| !Validate::isGenericName(Tools::getValue('prediggo_default_profile_id')))
	        	$postErrors[] = $this->l('Store Code ID key is required.');
	      	if (!Tools::getValue('prediggo_default_profile_id')
	      	|| !Validate::isUnsignedId(Tools::getValue('prediggo_default_profile_id')))
	        	$postErrors[] = $this->l('Default Profile is required.');
	    }

	    if(Tools::isSubmit('exportConfSubmit')
	    || Tools::isSubmit('manualExportSubmit'))
	    {
	    	if (!Tools::getValue('prediggo_export_product_min_quantity')
	    	|| !Validate::isUnsignedId(Tools::getValue('prediggo_export_product_min_quantity')))
	    		$postErrors[] = $this->l('The minimum quantity has to be an integer.');
	    	if (!Tools::getValue('prediggo_nb_days_order_valide')
	    	|| !Validate::isUnsignedId(Tools::getValue('prediggo_nb_days_order_valide')))
	    		$postErrors[] = $this->l('The number of days considering that an order can be exported has to be an integer.');
	    	if (!Tools::getValue('prediggo_nb_days_customer_last_visit_valide')
	    	|| !Validate::isUnsignedId(Tools::getValue('prediggo_nb_days_customer_last_visit_valide')))
	    		$postErrors[] = $this->l('The number of days considering that a customer can be exported has to be an integer.');
	    }

	    return $postErrors;
  	}

  	/**
	 * Set the data once an updated is processed in the BO
	 */
	private function _postProcess()
  	{
	    // Set the main configuration
  		if(Tools::isSubmit('mainConfSubmit'))
	    {
	    	$this->oPrediggoConfig->web_site_id = Tools::safeOutput(Tools::getValue('prediggo_web_site_id'));
	    	$this->oPrediggoConfig->store_code_id = Tools::safeOutput(Tools::getValue('prediggo_store_code_id'));
	    	$this->oPrediggoConfig->default_profile_id = (int)Tools::getValue('prediggo_default_profile_id');
	    	if($this->oPrediggoConfig->save())
	    		$this->_confirmations[] = $this->l('Main settings updated');
	    	else
	    		$this->_errors[] = $this->l('An error occurred while updating your main configuration settings');
	    }

	    // Set the export file configuration
	    if(Tools::isSubmit('exportConfSubmit')
	    || Tools::isSubmit('manualExportSubmit'))
	    {
	    	$this->oPrediggoExportConfig->products_file_generation = (int)Tools::getValue('prediggo_products_file_generation');
	    	$this->oPrediggoExportConfig->orders_file_generation = (int)Tools::getValue('prediggo_orders_file_generation');
	    	$this->oPrediggoExportConfig->customers_file_generation = (int)Tools::getValue('prediggo_customers_file_generation');
	    	$this->oPrediggoExportConfig->logs_file_generation = (int)Tools::getValue('prediggo_logs_file_generation');

	    	$this->oPrediggoExportConfig->export_product_image = (int)Tools::getValue('prediggo_export_product_image');
	    	$this->oPrediggoExportConfig->export_product_description = (int)Tools::getValue('prediggo_export_product_description');
	    	$this->oPrediggoExportConfig->export_product_min_quantity = (int)Tools::getValue('prediggo_export_product_min_quantity');
	    	$this->oPrediggoExportConfig->nb_days_order_valide = (int)Tools::getValue('prediggo_nb_days_order_valide');
	    	$this->oPrediggoExportConfig->nb_days_customer_last_visit_valide = (int)Tools::getValue('prediggo_nb_days_customer_last_visit_valide');

	    	if($this->oPrediggoExportConfig->save())
	    		$this->_confirmations[] = $this->l('Export settings updated');
	    	else
	    		$this->_errors[] = $this->l('An error occurred while updating your export configuration settings');
	    }

	    // Set the export file configuration
	    if(Tools::isSubmit('exportProtectionConfSubmit'))
	    {
	    	if($aIds = $this->oDataExtractorController->setRepositoryProtection(Tools::getValue('prediggo_htpasswd_user'), Tools::getValue('prediggo_htpasswd_pwd')))
	  		{
	  			if(empty($aIds['user']))
	  				$this->_confirmations[] = $this->l('Protection has been disactivated');
	  			else
	  				$this->_confirmations[] = $this->l('Protection has been activated');

	  			$this->oPrediggoExportConfig->htpasswd_user = $aIds['user'];
	  			$this->oPrediggoExportConfig->htpasswd_pwd = $aIds['pwd'];
	  			if($this->oPrediggoExportConfig->save())
	  				$this->_confirmations[] = $this->l('Protection settings updated');
	  			else
	  				$this->_errors[] = $this->l('An error occurred when updating your protection settings');
	  		}
	  		else
	  			$this->_errors[] = $this->l('An error occurred when activating the protection');
	    }

	    if(Tools::isSubmit('exportPrediggoAttributesSubmit'))
	    {
	    	$sAttributesGroupsIds = '';
	    	if(is_array(Tools::getValue('prediggo_attributes_groups_ids')))
	    		$sAttributesGroupsIds = join(',', array_map('intval',Tools::getValue('prediggo_attributes_groups_ids')));

	    	$sFeaturesIds = '';
	    	if(is_array(Tools::getValue('prediggo_features_ids')))
	    		$sFeaturesIds = join(',', array_map('intval',Tools::getValue('prediggo_features_ids')));

	    	$this->oPrediggoExportConfig->attributes_groups_ids = $sAttributesGroupsIds;
	    	$this->oPrediggoExportConfig->features_ids = $sFeaturesIds;

	    	if($this->oPrediggoExportConfig->save())
	    		$this->_confirmations[] = $this->l('Protection settings updated');
	    	else
	    		$this->_errors[] = $this->l('An error occurred when updating your protection settings');
	    }

	    if(Tools::isSubmit('exportNotRecoSubmit'))
	    {
	    	$sProductsIds = '';
	    	if(is_array(Tools::getValue('prediggo_products_ids_not_recommendable')))
	    		$sProductsIds = join(',',array_map('intval',Tools::getValue('prediggo_products_ids_not_recommendable')));

	    	$this->oPrediggoExportConfig->products_ids_not_recommendable = $sProductsIds;

	    	if($this->oPrediggoExportConfig->save())
	    		$this->_confirmations[] = $this->l('The black list of products recommendations has been updated');
	    	else
	    		$this->_errors[] = $this->l('An error occurred when updating the black list of products recommendations');
	    }

	    if(Tools::isSubmit('exportNotSearchSubmit'))
	    {
	    	$sProductsIds = '';
	    	if(is_array(Tools::getValue('prediggo_products_ids_not_searchable')))
	    		$sProductsIds = join(',',array_map('intval',Tools::getValue('prediggo_products_ids_not_searchable')));

	    	$this->oPrediggoExportConfig->products_ids_not_searchable = $sProductsIds;

	    	if($this->oPrediggoExportConfig->save())
	    		$this->_confirmations[] = $this->l('The black list of products search has been updated');
	    	else
	    		$this->_errors[] = $this->l('An error occurred when updating the black list of products search');
	    }

  		// Launch the file export
	    if(Tools::isSubmit('manualExportSubmit')
	    && !sizeof($this->_errors))
	    {
	  		$this->oDataExtractorController->launchExport();
	    }

	    // Set the recommendation log file activation configuration
	    if(Tools::isSubmit('mainRecommendationConfSubmit'))
	    {
	    	$this->oPrediggoRecommendationConfig->logs_fo_file_generation = (int)Tools::getValue('prediggo_logs_fo_file_generation');
	    	$this->oPrediggoRecommendationConfig->server_url_recommendations = pSQL(Tools::getValue('prediggo_server_url_recommendations'));

	    	if($this->oPrediggoRecommendationConfig->save())
	    		$this->_confirmations[] = $this->l('Main recommendations settings updated');
	    	else
	    		$this->_errors[] = $this->l('An error occurred when updating your main recommendations settings');
	    }

	    // Set the Home page recommendation configuration
	    if(Tools::isSubmit('exportHomeRecommendationConfSubmit'))
	    {
	    	$this->oPrediggoRecommendationConfig->home_recommendations = (int)Tools::getValue('prediggo_home_recommendations');
	    	$this->oPrediggoRecommendationConfig->home_nb_items = (int)Tools::getValue('prediggo_home_nb_items');
	    	$this->oPrediggoRecommendationConfig->home_block_title = (array)Tools::getValue('prediggo_home_block_title');

	    	if($this->oPrediggoRecommendationConfig->save())
	    		$this->_confirmations[] = $this->l('Home recommendations settings updated');
	    	else
	    		$this->_errors[] = $this->l('An error occurred when updating your home recommendations block settings');
	    }

	    // Set the 404 page recommendation configuration
	    if(Tools::isSubmit('export404RecommendationConfSubmit'))
	    {
	    	$this->oPrediggoRecommendationConfig->error_recommendations = (int)Tools::getValue('prediggo_error_recommendations');
	    	$this->oPrediggoRecommendationConfig->error_nb_items = (int)Tools::getValue('prediggo_error_nb_items');
	    	$this->oPrediggoRecommendationConfig->error_block_title = (array)Tools::getValue('prediggo_error_block_title');

	    	if($this->oPrediggoRecommendationConfig->save())
	    		$this->_confirmations[] = $this->l('404 recommendations settings updated');
	    	else
	    		$this->_errors[] = $this->l('An error occurred when updating your 404 recommendations block settings');
	    }

	    // Set the product page recommendation configuration
	    if(Tools::isSubmit('exportProductRecommendationConfSubmit'))
	    {
	    	$this->oPrediggoRecommendationConfig->product_recommendations = (int)Tools::getValue('prediggo_product_recommendations');
	    	$this->oPrediggoRecommendationConfig->product_nb_items = (int)Tools::getValue('prediggo_product_nb_items');
	    	$this->oPrediggoRecommendationConfig->product_block_title = (array)Tools::getValue('prediggo_product_block_title');

	    	if($this->oPrediggoRecommendationConfig->save())
	    		$this->_confirmations[] = $this->l('Product recommendations settings updated');
	    	else
	    		$this->_errors[] = $this->l('An error occurred when updating your product recommendations block settings');
	    }

	    // Set the category page recommendation configuration
	    if(Tools::isSubmit('exportCategoryRecommendationConfSubmit'))
	    {
	    	$this->oPrediggoRecommendationConfig->category_recommendations = (int)Tools::getValue('prediggo_category_recommendations');
	    	$this->oPrediggoRecommendationConfig->category_nb_items = (int)Tools::getValue('prediggo_category_nb_items');
	    	$this->oPrediggoRecommendationConfig->category_block_title = (array)Tools::getValue('prediggo_category_block_title');

	    	if($this->oPrediggoRecommendationConfig->save())
	    		$this->_confirmations[] = $this->l('Category recommendations settings updated');
	    	else
	    		$this->_errors[] = $this->l('An error occurred when updating your category recommendations block settings');
	    }

	    // Set the customer pages recommendation configuration
	    if(Tools::isSubmit('exportCustomerRecommendationConfSubmit'))
	    {
	    	$this->oPrediggoRecommendationConfig->customer_recommendations = (int)Tools::getValue('prediggo_customer_recommendations');
	    	$this->oPrediggoRecommendationConfig->customer_nb_items = (int)Tools::getValue('prediggo_customer_nb_items');
	    	$this->oPrediggoRecommendationConfig->customer_block_title = (array)Tools::getValue('prediggo_customer_block_title');

	    	if($this->oPrediggoRecommendationConfig->save())
	    		$this->_confirmations[] = $this->l('Customer recommendations settings updated');
	    	else
	    		$this->_errors[] = $this->l('An error occurred when updating your customer recommendations block settings');
	    }

	    // Set the cart page recommendation configuration
	    if(Tools::isSubmit('exportCartRecommendationConfSubmit'))
	    {
	    	$this->oPrediggoRecommendationConfig->cart_recommendations = (int)Tools::getValue('prediggo_cart_recommendations');
	    	$this->oPrediggoRecommendationConfig->cart_nb_items = (int)Tools::getValue('prediggo_cart_nb_items');
	    	$this->oPrediggoRecommendationConfig->cart_block_title = (array)Tools::getValue('prediggo_cart_block_title');

	    	if($this->oPrediggoRecommendationConfig->save())
	    		$this->_confirmations[] = $this->l('Cart recommendations settings updated');
	    	else
	 		 	$this->_errors[] = $this->l('An error occurred when updating your cart recommendations block settings');
	    }

	    // Set the blocklayered page recommendation configuration
	    if(Tools::isSubmit('exportBlocklayeredRecommendationConfSubmit'))
	    {
	    	$this->oPrediggoRecommendationConfig->blocklayered_recommendations = (int)Tools::getValue('prediggo_blocklayered_recommendations');
	    	$this->oPrediggoRecommendationConfig->blocklayered_nb_items = (int)Tools::getValue('prediggo_blocklayered_nb_items');
	    	$this->oPrediggoRecommendationConfig->blocklayered_block_title = (array)Tools::getValue('prediggo_blocklayered_block_title');

	    	if($this->oPrediggoRecommendationConfig->save())
	    		$this->_confirmations[] = $this->l('Blocklayered recommendations settings updated');
	    	else
	 		 	$this->_errors[] = $this->l('An error occurred when updating your Blocklayered recommendations block settings');
	    }

	    // Set the recommendation log file activation configuration
	    if(Tools::isSubmit('mainSearchConfSubmit'))
	    {
	    	$this->oPrediggoSearchConfig->search_active = (int)Tools::getValue('prediggo_search_active');
	    	$this->oPrediggoSearchConfig->search_nb_items = (int)Tools::getValue('prediggo_search_nb_items');
	    	$this->oPrediggoSearchConfig->search_nb_min_chars = (int)Tools::getValue('prediggo_search_nb_min_chars');
	    	$this->oPrediggoSearchConfig->logs_fo_file_generation = (int)Tools::getValue('prediggo_logs_fo_file_generation');
	    	$this->oPrediggoSearchConfig->server_url_search = pSQL(Tools::getValue('prediggo_server_url_search'));
	    	$this->oPrediggoSearchConfig->searchandizing_active = pSQL(Tools::getValue('prediggo_searchandizing_active'));
	    	$this->oPrediggoSearchConfig->layered_navigation_active = pSQL(Tools::getValue('prediggo_layered_navigation_active'));

	    	if($this->oPrediggoSearchConfig->save())
	    		$this->_confirmations[] = $this->l('Main search settings updated');
	    	else
	    		$this->_errors[] = $this->l('An error occurred when updating your main search settings');
	    }


	    // Set the Search Autocompletion configuration
	    if(Tools::isSubmit('exportSearchAutocompletionConfSubmit'))
	    {
	    	$this->oPrediggoSearchConfig->autocompletion_active = (int)Tools::getValue('prediggo_autocompletion_active');
	    	$this->oPrediggoSearchConfig->autocompletion_nb_items = (int)Tools::getValue('prediggo_autocompletion_nb_items');
	    	$this->oPrediggoSearchConfig->suggest_active = (int)Tools::getValue('prediggo_suggest_active');
	    	$this->oPrediggoSearchConfig->suggest_words = (array)Tools::getValue('prediggo_suggest_words');

	    	if($this->oPrediggoSearchConfig->save())
	    		$this->_confirmations[] = $this->l('Search Autocompletion settings updated');
	    	else
	    		$this->_errors[] = $this->l('An error occurred when updating your search autocompletion settings');
	    }
  	}

  	/**
	 * Display the BO html with smart templates
	 */
	public function _displayForm()
  	{
  		global $smarty, $cookie;

  		$smarty->assign(array(
  		  	'oModule' => $this,
  		  	'cookie' => $cookie
  		));

  		$this->_html .= '<div id="prediggo_conf">';

  		// Display errors
  		$smarty->assign(array(
  			'aPrediggoWarnings' => $this->_warnings,
  			'aPrediggoConfirmations' => $this->_confirmations,
  	  		'aPrediggoErrors' => $this->_errors
  		));
  		$this->_html .= $this->display(__FILE__, 'bo_errors.tpl');

  		$this->_html .= $this->display(__FILE__, 'bo_tabs.tpl');

  		// Display Main Configuration management
  		$smarty->assign(array(
  	  		'formAction' => Tools::safeOutput($_SERVER['REQUEST_URI']),
  	  		'oPrediggoConfig' => $this->oPrediggoConfig,
  	  		'aLanguages' => Language::getLanguages(false)
  		));
  		$this->_html .= $this->display(__FILE__, 'bo_main_conf.tpl');

  		// Get Export Attribute manager
  		$aPrediggoAttributesGroups = explode(',',$this->oPrediggoExportConfig->attributes_groups_ids);
  		$aPrediggoFeatures = explode(',',$this->oPrediggoExportConfig->features_ids);
  		$smarty->assign(array(
  			'oPrediggoExportConfig' => $this->oPrediggoExportConfig,
  			'aPrediggoAttributesGroups' => $aPrediggoAttributesGroups,
  			'aPrediggoFeatures' => $aPrediggoFeatures,
  			'aGroupAttributes' => AttributeGroup::getAttributesGroups((int)$cookie->id_lang),
  			'aFeatures' => Feature::getFeatures((int)$cookie->id_lang)
  		));
  		$sAttributeManager = $this->display(__FILE__, 'bo_export_attribute_manager.tpl');

  		// Get Recommendations black list
  		$aPrediggoProductsNotRecommendable = $this->oPrediggoExportConfig->products_ids_not_recommendable;
  		if(!empty($aPrediggoProductsNotRecommendable))
  		{
  			$aPrediggoProductsNotRecommendable = explode(',',$aPrediggoProductsNotRecommendable);
	  		foreach($aPrediggoProductsNotRecommendable as $k => $iID)
	  			$aPrediggoProductsNotRecommendable[$k] = new Product((int)$iID, false, (int)$cookie->id_lang);
  		}

  		// Get Search black list
  		$aPrediggoProductsNotSearchable = $this->oPrediggoExportConfig->products_ids_not_searchable;
  		if(!empty($aPrediggoProductsNotSearchable))
  		{
  			$aPrediggoProductsNotSearchable = explode(',',$aPrediggoProductsNotSearchable);
  			foreach($aPrediggoProductsNotSearchable as $k => $iID)
  				$aPrediggoProductsNotSearchable[$k] = new Product((int)$iID, false, (int)$cookie->id_lang);
  		}

  		$smarty->assign(array(
  		  	'aPrediggoProductsNotRecommendable' => $aPrediggoProductsNotRecommendable,
  		  	'aPrediggoProductsNotSearchable' => $aPrediggoProductsNotSearchable
  		));
  		$sBackListManager = $this->display(__FILE__, 'bo_black_lists.tpl');

  		// Display Export Configuration management
  		$smarty->assign(array(
			'sAttributeManager' => $sAttributeManager,
  			'sBackListManager' => $sBackListManager,
			'sExportRepositoryPath' => $this->oDataExtractorController->getRepositoryPath(),
			'sCronFilePath' => Tools::getShopDomain(true).str_replace(_PS_ROOT_DIR_.'/', __PS_BASE_URI__, $this->_path).'cron_export.php'
  		));
  		$this->_html .= $this->display(__FILE__, 'bo_export_conf.tpl');


  		// Display Recommendations Configuration management
  		$smarty->assign(array(
  			'oPrediggoRecommendationConfig' => $this->oPrediggoRecommendationConfig
  		));
  		$this->_html .= $this->display(__FILE__, 'bo_recommendation_conf.tpl');

  		// Display Recommendations Configuration management
  		$smarty->assign(array(
  			'oPrediggoSearchConfig' => $this->oPrediggoSearchConfig
  		));
  		$this->_html .= $this->display(__FILE__, 'bo_search_conf.tpl');

  		$this->_html .= '</div>';
  	}
}
