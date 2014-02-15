<?php
/*
* 2013 TextMaster
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to info@textmaster.com so we can send you a copy immediately.
*
* @author JSC INVERTUS www.invertus.lt <help@invertus.lt>
* @copyright 2013 TextMaster
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* International Registered Trademark & Property of TextMaster
*/
if (!defined('_PS_VERSION_'))
    exit;

if (!defined('_TEXTMASTER_CLASSES_DIR_'))
    define('_TEXTMASTER_CLASSES_DIR_', _PS_MODULE_DIR_.'textmaster/classes/');

if (!defined('TEXTMASTER_TPL_DIR'))
    define('TEXTMASTER_TPL_DIR', _PS_MODULE_DIR_.'textmaster/views/templates/');

if (!defined('TEXTMASTER_MODELS_DIR'))
    define('TEXTMASTER_MODELS_DIR', _PS_MODULE_DIR_.'textmaster/models/');

if (version_compare(_PS_VERSION_, '1.5', '<'))
{
    require_once(_PS_MODULE_DIR_.'textmaster/backward_compatibility/backward.php');
}

require_once(_TEXTMASTER_CLASSES_DIR_.'view.php');
require_once(TEXTMASTER_MODELS_DIR.'ObjectModel.php');
require_once(TEXTMASTER_MODELS_DIR.'Configuration.php');
require_once(TEXTMASTER_MODELS_DIR.'Project.php');
require_once(TEXTMASTER_MODELS_DIR.'Document.php');
require_once(TEXTMASTER_MODELS_DIR.'Communication.php');

class TextMaster extends Module
{
    private $_html = '';

    public $error;
    private $api_instance;
    private $textmaster_data_with_cookies_manager_obj;
    public $available_statuses = array();

    const CURRENT_INDEX = 'index.php?tab=AdminModules&configure=textmaster&token=';

    function __construct()
    {
        $this->name = 'textmaster';
        $this->tab = 'i18n_localization';
        $this->version = '1.0.2';
        $this->author = 'TextMaster';
        $this->need_instance = 1;

        parent::__construct();

        $this->displayName = $this->l('TextMaster');
        $this->description = $this->l('Your ultimate one-stop-shop for online professional translation and proofreading');

        if (!function_exists('curl_init'))
            $this->warning = $this->l('CURL should be installed');

        if (version_compare(_PS_VERSION_, '1.5', '<'))
        {
            $this->context = new Context;
            $this->smarty = $this->context->smarty;

            $this->context->smarty->assign('ps14', true);
        }

        $this->textmaster_data_with_cookies_manager_obj = new TextMasterDataWithCookiesManager();
        if (!$this->textmaster_data_with_cookies_manager_obj->checkCurrentShop() && Tools::getValue('menu') == 'create_project')
            Tools::redirectAdmin(self::CURRENT_INDEX.Tools::getValue('token').'&configure='.$this->name.'&menu=create_project&step=products&token='.Tools::getAdminTokenLite('AdminModules'));

        $this->available_statuses = array(
            'in_creation',
            'waiting_assignment',
            'in_progress',
            'in_review',
            'completed',
            'incomplete',
            'paused',
            'canceled',
            'copyscape',
            'counting_words',
            'quality_control',
            'default'
        );

        include_once(_PS_MODULE_DIR_.'textmaster/textmaster.class.php');

        if (!defined('TEXTMASTER_IMG_DIR'))
            define('TEXTMASTER_IMG_DIR', $this->_path.'views/img/');
    }

    function install()
    {
        if (!function_exists('curl_init'))
            return false;

        $sql = "
			CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."textmaster_configuration` (
			  `id_shop` int(11) unsigned DEFAULT NULL,
			  `name` varchar(50) NOT NULL,
			  `value` text,
			  `date_upd` datetime NOT NULL,
			  KEY `name` (`name`),
			  KEY `id_shop` (`id_shop`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        if (!Db::getInstance()->execute($sql)) return false;

        $sql = "
			CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."textmaster_project` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`id_project_api` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
				`id_shop` int(5) NOT NULL,
				`date_add` datetime DEFAULT NULL,
				`date_upd` datetime DEFAULT NULL,
				`type` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
				`name` text COLLATE utf8_unicode_ci NOT NULL,
				`language_from` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
				`language_to` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
				`status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				`launch` tinyint(1) NOT NULL,
				PRIMARY KEY (`id`),
				UNIQUE KEY `id_project_api` (`id_project_api`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

        if (!Db::getInstance()->execute($sql)) return false;

        $sql = "
			CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."textmaster_document` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `id_project` int(11) NOT NULL,
			  `id_document_api` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
			  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `id_product` int(11) DEFAULT NULL,
			  `date_add` datetime DEFAULT NULL,
			  `date_upd` datetime DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

        if (!Db::getInstance()->execute($sql)) return false;

        return parent::install() && $this->registerHook('displayAdminProductsExtra');
    }

    public function registerHook($hook_name, $shop_list = NULL)
    {
        if (version_compare(_PS_VERSION_, '1.5', '<'))
        {
            $hook_name = 'backOfficeTop';
        }

        return parent::registerHook($hook_name, $shop_list);
    }

    public function uninstall()
    {
        if (!parent::uninstall() || !$this->deleteTables() ||
            !$this->unregisterHook('displayAdminProductsExtra'))
            return false;
        return true;
    }

    private function deleteTables()
    {
        return Db::getInstance()->execute('
			DROP TABLE IF EXISTS
			`'._DB_PREFIX_.'textmaster_configuration`,
			`'._DB_PREFIX_.'textmaster_project`,
			`'._DB_PREFIX_.'textmaster_document`
		');
    }

    /**
     * module configuration page
     * @return page HTML code
     */

    function getContent()
    {
        if (TEXTMASTER_SANDBOX_ENVIRONMENT)
			$this->_html .= $this->displayWarnings(array($this->l('Module is in SANDBOX mode')));
		elseif (TEXTMASTER_STAGIGN_ENVIRONMENT)
			$this->_html .= $this->displayWarnings(array($this->l('Module is in STAGING mode')));

        if (Tools::isSubmit('reset_cookie'))
            $this->textmaster_data_with_cookies_manager_obj->deleteAllProjectDataFromCookie();

        $this->displayFlashMessagesIfIsset();

        if (version_compare(_PS_VERSION_, '1.5', '<'))
        {
            echo '<link href="'.$this->_path.'css/admin.css" rel="stylesheet" type="text/css">';
            echo '<link href="'.$this->_path.'css/toolbar.css" rel="stylesheet" type="text/css">';
            echo '<link href="'.$this->_path.'css/textmaster.css" rel="stylesheet" type="text/css">';
            echo '<script src="'.$this->_path.'js/textmaster.js" type="text/javascript"></script>';
        }
        else
        {
            $this->context->controller->addCSS($this->_path.'css/textmaster.css', 'all');
            $this->context->controller->addJS($this->_path.'js/textmaster.js');
        }

        $menu = Tools::getValue('menu', 'translation');

        /* if connection to API cannot be established, settings will be the only page, that user can access */
        if(!$this->getAPIConnection())
            $menu = 'textmaster_web_page';

        //if (Tools::isSubmit('updateproject'))
        //$this->addProjectDataToForm();

        if (Tools::isSubmit('deleteproject'))
            $this->deleteProject();

        if (Tools::isSubmit('launch_project'))
            $this->launchProject();

        if (Tools::isSubmit('viewproject'))
            $menu = 'viewproject';

        if (Tools::isSubmit('duplicateproject'))
        {
            $this->textmaster_data_with_cookies_manager_obj->deleteAllProjectDataFromCookie();
            $this->getProjectData();
        }

        if (Tools::isSubmit('updateproject'))
        {
            $this->textmaster_data_with_cookies_manager_obj->deleteAllProjectDataFromCookie();
            $this->getProjectData('updateproject');
        }

        $this->context->smarty->assign('module_display_name', $this->displayName);

        switch($menu)
        {
            case 'create_project':
                if (Tools::isSubmit('saveproject') or Tools::isSubmit('saveprojectnext'))
                    $this->addDataToProject();

                if (Tools::isSubmit('deletedocument'))
                    $this->deleteDocument();

                $this->context->smarty->assign('current_page_name', $this->l('Manage project'));
                $this->displayNavigation();
                $this->displayProjectCreationForm();
                break;
            case 'translation':
            default:
                $this->context->smarty->assign('current_page_name', $this->l('Translation projects'));
                $this->displayNavigation();
                $this->displayInfoBlock();
                $this->displayTranslationProjectsList();
                break;
            case 'proofreading':
                $this->context->smarty->assign('current_page_name', $this->l('Proofreading projects'));
                $this->displayNavigation();
                $this->displayInfoBlock();
                $this->displayProofreadingProjectsList();
                break;
            case 'copywriting':
                $this->context->smarty->assign('current_page_name', $this->l('Copywriting projects'));
                $this->displayNavigation();
                $this->displayInfoBlock();
                $this->displayCopywritingProjectsList();
                break;
            case 'settings':
                $this->context->smarty->assign('current_page_name', $this->l('Settings'));
                $this->displayNavigation();
                if (Tools::isSubmit('savesettings'))
                    $this->saveSettings();
                $this->displaySettingsForm();
                break;
            case 'viewproject':
                if (Tools::isSubmit('approveDocument'))
                    $this->approveDocument();
                $this->displayResults();
                break;
            case 'help':
                $this->context->smarty->assign('current_page_name', $this->l('Module help'));
                $this->displayNavigation();
                $this->displayHelp();
                break;
            case 'textmaster_web_page':
                $this->textmasterWebPageActions();
                $this->context->smarty->assign('current_page_name', $this->l('Login / Create New Account'));
                $this->displayNavigation();
                $this->displayTextmasterWebPage();
                break;
        }
        return $this->_html;
    }

    private function displayNavigation()
    {
        $token = Tools::getAdminTokenLite('AdminModules');
        $this->context->smarty->assign(array(
            'new_project_link' => self::CURRENT_INDEX.Tools::getValue('token').'&configure='.$this->name.'&menu=create_project&reset_cookie&token='.$token,
            'translation_page_url' => self::CURRENT_INDEX.Tools::getValue('token').'&configure='.$this->name.'&menu=translation&token='.$token,
            'proofreading_page_url' => self::CURRENT_INDEX.Tools::getValue('token').'&configure='.$this->name.'&menu=proofreading&token='.$token,
            'settings_page_url' => self::CURRENT_INDEX.Tools::getValue('token').'&configure='.$this->name.'&menu=settings&token='.$token,
            'help_page_url' => self::CURRENT_INDEX.Tools::getValue('token').'&configure='.$this->name.'&menu=help&token='.$token
        ));
        $this->_html .= $this->context->smarty->fetch(TEXTMASTER_TPL_DIR.'/admin/navigation.tpl');
    }

    private function displayInfoBlock()
    {
        $textmaster_api_obj = new TextMasterAPI($this);
        $user_info = $textmaster_api_obj->getUserInfo();
        if (!isset($user_info['wallet']['current_money']) || !isset($user_info['wallet']['currency_code']))
            return;

        $this->context->smarty->assign(array(
            'credits' => Tools::ps_round($user_info['wallet']['current_money'], 2).' '.$user_info['wallet']['currency_code'],
            'url' => TEXTMASTER_EU_URI.'/clients/credits/new'
        ));
        if (version_compare(_PS_VERSION_, '1.5', '<'))
            $this->context->smarty->assign('ps14', true);
        $this->_html .= $this->context->smarty->fetch(TEXTMASTER_TPL_DIR.'admin/info_block.tpl');
    }

    private function getProjectData($action = null)
    {
        $id_project = (int) Tools::getValue('id_project');
        $project_obj = new TextMasterProject((int) $id_project);
        $project_data = $project_obj->getProjectData();
        $configuration = new TextMasterConfiguration();

        $ctypes = array('translation', 'proofreading', 'copywriting');

        $results = array();

        foreach ($ctypes AS $ctype => $ctype_value)
            foreach ($configuration->getData() AS $item => $item_value)
                if (strpos($item, $ctype_value) !== false)
                    $results[$item] = $item_value;

        $results['project_step'] = 'summary';
        $results['project_name'] = isset($project_data['name']) ? $project_data['name'] : '';
        $results['ctype'] = isset($project_data['ctype']) ? $project_data['ctype'] : 'translation';

        foreach ($results AS $result => $result_value)
            foreach ($project_data AS $item => $item_value)
                if ($result == $results['ctype'].'_'.$item)
                    $results[$result] = $item_value;

        $selected_products = DB::getInstance()->executeS("
			SELECT `id_product`
			FROM `"._DB_PREFIX_."textmaster_document`
			WHERE `id_project` = '".(int) $id_project."'
			GROUP BY `id_product`
		");

        $selected_names = DB::getInstance()->executeS("
			SELECT `name`
			FROM `"._DB_PREFIX_."textmaster_document`
			WHERE `id_project` = '".(int) $id_project."'
			GROUP BY `name`
		");

        if (!$selected_products)
            $selected_products = array();

        if (!$selected_names)
            $selected_names = array();

        $selected_products_array = array();
        $selected_names_array = array();
        foreach ($selected_products AS $product => $id)
            $selected_products_array[] = $id['id_product'];

        foreach ($selected_names AS $name => $value)
            $selected_names_array[] = $value['name'];

        if (!empty($selected_products_array))
            $results['selected_products_ids'] = $selected_products_array;

        $additional_info = '';
        if ($action == 'updateproject')
        {
            $results['id_edit_project'] = (int) $id_project;
            $additional_info = '&editTextmasterProject&id_project='.(int) $id_project;
        }

        $textmasters = array();
        if (isset($project_data['textmasters']) && !empty($project_data['textmasters']))
        {
            foreach ($project_data['textmasters'] AS $textmaster)
                $textmasters[] = $textmaster['author_id'];
            $results['authors'] = $textmasters;
        }

        $results['restrict_to_textmasters'] 				= (int) isset($project_data['authors']);
        $results[$project_data['ctype'].'_expertise_on'] 	= (int) isset($project_data['options']['expertise']);
        $results[$project_data['ctype'].'_language_level'] 	= $project_data['options']['language_level'];
        $results[$project_data['ctype'].'_quality_on'] 		= (int) isset($project_data['options']['quality']);

        $this->addFlashWarning($this->l('Please reselect TextMasters for this project'));

        $textmasterAPI = $this->getAPIConnection();

        $this->checkLanguages($results[$project_data['ctype'].'_language_from'], $textmasterAPI->getLanguages());
        if ($project_data['ctype'] == 'translation')
            $this->checkLanguages($results[$project_data['ctype'].'_language_to'], $textmasterAPI->getLanguages());

        $results['project_data'] = $selected_names_array;

        $this->textmaster_data_with_cookies_manager_obj->setAllProject($results);
        Tools::redirectAdmin(self::CURRENT_INDEX.Tools::getValue('token').'&configure='.$this->name.$additional_info.'&menu=create_project&step=products&token='.Tools::getAdminTokenLite('AdminModules'));
    }

    private function checkLanguages($language, $languages = array())
    {
        $available = false;
        foreach ($languages as $iso_code => $lang)
            if ($language == $lang['code'])
                $available = true;

        if (!$available)
            $this->addFlashWarning($this->l('Selected language is missing:').' '.$language);
    }

    private function textmasterWebPageActions()
    {
        if (Tools::getValue('login_to_textmaster_system_action'))
        {
            Tools::safePostVars();
            $email = Tools::getValue('login_email', '');
            $password = Tools::getValue('login_password', '');

            $errors = array();
            if ($email == '')
                $errors[] = $this->l('Email is required');
            elseif (!Validate::isEmail($email))
                $errors[] = $this->l('Email must be valid');

            if ($password == '')
                $errors[] = $this->l('Password is required');
            elseif (!Validate::isPasswd($password))
                $errors[] = $this->l('Password must be valid');

            if (!empty($errors))
                return $this->_html .= $this->displayErrors($errors);

            $result = $this->getTextMasterOAuth2Token($email, $password);
            $result = Tools::jsonDecode($result, true);

            if (!isset($result['access_token']))
                return $this->_html .= $this->displayErrors(array($this->l('Wrong login / password')));

            $result = $this->getTextMasterAPIKeys($result['access_token']);
            $result = Tools::jsonDecode($result, true);

            if (!isset($result['api_info']['api_key']) || !isset($result['api_info']['api_secret']))
                return $this->_html .= $this->displayErrors(array($this->l('Could not get API key / secret')));

            $textmaster_settings_obj = new TextMasterConfiguration();
            $textmaster_settings_obj->api_key = $result['api_info']['api_key'];
            $textmaster_settings_obj->api_secret = $result['api_info']['api_secret'];

            if ($textmaster_settings_obj->updateConfiguration())
                Tools::redirectAdmin(self::CURRENT_INDEX.Tools::getValue('token').'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
            else
                return $this->_html .= $this->displayErrors(array($this->l('Could not save API key / secret')));
        }

        if (Tools::getValue('register_to_textmaster_system'))
        {
            Tools::safePostVars();

            $email = Tools::getValue('register_email', '');
            $password = Tools::getValue('register_password', '');
            $password_confirm = Tools::getValue('register_password_confirm', '');
            $register_phone = Tools::getValue('register_phone', '');

            $errors = array();
            if ($email == '')
                $errors[] = $this->l('Email is required');
            elseif (!Validate::isEmail($email))
                $errors[] = $this->l('Email must be valid');

            if ($password == '')
                $errors[] = $this->l('Password is required');
            elseif ($password !== $password_confirm)
                $errors[] = $this->l('Password and Confirm password must be the same');
            elseif (!Validate::isPasswd($password))
                $errors[] = $this->l('Password must be valid');

            if ($register_phone != '')
                if (!Validate::isPhoneNumber($register_phone))
                    $errors[] = $this->l('Phone number must be valid');

            if (!empty($errors))
                return $this->_html .= $this->displayErrors($errors);

            $token = $this->getTextMasterOAuth2TokenForUserCreation();
            $token = Tools::jsonDecode($token, true);
            if (!isset($token['access_token']))
                return $this->_html .= $this->displayErrors(array($this->l('Could not get access token')));

            $user_info = $this->createNewTextMasterUser($token['access_token'], $email, $password);
            $user_info = Tools::jsonDecode($user_info, true);

            $errors = array();
            if (isset($user_info['errors']))
                foreach ($user_info['errors'] AS $key => $error)
                    foreach ($error AS $error_key => $value)
                        $errors[] = $key.' '.$this->l('-').' '.$value;
            if (!empty($errors))
                return $this->_html .= $this->displayErrors($errors);

            if (!isset($user_info['api_info']['api_key']) || !isset($user_info['api_info']['api_secret']))
                return $this->_html .= $this->displayErrors(array('Could not get API key / secret'));

            $textmaster_settings_obj = new TextMasterConfiguration();
            $textmaster_settings_obj->api_key = $user_info['api_info']['api_key'];
            $textmaster_settings_obj->api_secret = $user_info['api_info']['api_secret'];
            if ($textmaster_settings_obj->updateConfiguration())
                Tools::redirectAdmin(self::CURRENT_INDEX.Tools::getValue('token').'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
            else
                return $this->_html .= $this->displayErrors(array($this->l('Could not save API key / secret')));
        }
    }

    function getTextMasterOAuth2TokenForUserCreation()
    {
        $uri = TEXTMASTER_EU_URI.'/oauth/token';
        $header = "grant_type=client_credentials"
            . "&client_id=".TEXTMASTER_CLIENT_ID
            . "&client_secret=".TEXTMASTER_CLIENT_SECRET;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $uri);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10); //timeout in seconds
        curl_setopt ($curl, CURLOPT_POSTFIELDS, $header);
        return curl_exec($curl);
    }

    function createNewTextMasterUser($oAuthTokenNew, $email, $password, $phone = null)
    {
        $uri      = TEXTMASTER_API_URI.'/admin/users';

        $header   = array(
            "Content-Type: application/json",
            "Authorization: Bearer {$oAuthTokenNew}",
            "Accept: application/json",
            "AGENT: tm-prestashop-app/agent v1.0"
        );

        $post_arr = $phone ? array(
            'user'=>array(
                'locale' => $this->getFullLocale(true),
                'email'=>$email,
                'password'=>$password,
                'referer_tracker_id'=>TEXTMASTER_TRACKER_ID,
                'group' => 'clients',
                'contact_information_attributes'=>array(
                    'phone_number'=>$phone
                )
            )
        ) : array(
            'user'=>array(
                'locale' => $this->getFullLocale(true),
                'email'=>$email,
                'referer_tracker_id'=>TEXTMASTER_TRACKER_ID,
                'password'=>$password,
                'group' => 'clients'
            )
        );
        $post     = Tools::jsonEncode($post_arr);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $uri);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10); //timeout in seconds
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt ($curl, CURLOPT_POSTFIELDS, $post);
        return curl_exec($curl);
    }

    public function getFullLocale($registration = false)
    {
        if (!$registration)
        {
            $textmaster_api_obj = new TextMasterAPI($this);
            $user_info = $textmaster_api_obj->getUserInfo();
            return $user_info['locale'];
        }
        
        $locales_from_api = array();
        $textmaster_api_obj = new TextMasterAPI();
        foreach ($textmaster_api_obj->getLocales() AS $row => $locale_info)
            $locales_from_api[] = $locale_info['code'];

        $registration_locale = $this->getRegistrationLocale();
        
        if (in_array($registration_locale, $locales_from_api))
            return $registration_locale;
        
        return TEXTMASTER_DEFAULT_LOCALE;
    }
    
    private function getRegistrationLocale()
    {
        $country = new Country((int)Configuration::get('PS_COUNTRY_DEFAULT'));
        $country_iso_code = $country->iso_code;
        $language_iso_code = Tools::strtolower($this->context->language->iso_code);
        
        if ($language_iso_code == 'en' || $language_iso_code == 'pt')
        {
            switch ($country_iso_code)
            {
                case 'US':
                    $locale = 'en-US';
                    break;
                case 'BR':
                    $locale = 'pt-BR';
                    break;
                case 'PT':
                    $locale = 'pt-PT';
                    break;
                default:
                    $locale = TEXTMASTER_DEFAULT_LOCALE;
                    break;
            }
        }
        else
        {
            switch ($language_iso_code)
            {
                case 'es':
                    $locale = 'es-ES';
                    break;
                case 'fr':
                    $locale = 'fr-FR';
                    break;
                case 'it':
                    $locale = 'it-IT';
                    break;
                case 'de':
                    $locale = 'de-DE';
                    break;
                default:
                    $locale = TEXTMASTER_DEFAULT_LOCALE;
                    break;
            }
        }
        
        return $locale;
    }

    /*function createNewTextMasterUser($oAuthTokenNew, $email, $password)
    {
        $uri      = TEXTMASTER_API_URI.'/admin/users';
        $header   = array("Authorization: Bearer {$oAuthTokenNew}", "AGENT: tm-prestashop-app/agent v1.0");
        $post_arr = array('user'=>array('email'=>$email, 'password'=>$password));
        $post     = Tools::jsonEncode($post_arr);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $uri);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10); //timeout in seconds
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt ($curl, CURLOPT_POSTFIELDS, $post);
        return curl_exec($curl);
    }*/

    private function getTextMasterOAuth2Token($email, $password)
    {
        $uri = TEXTMASTER_EU_URI.'/oauth/token';
        $header = "grant_type=password"
            . "&user[email]={$email}"
            . "&user[password]={$password}"
            . "&client_id=".TEXTMASTER_CLIENT_ID
            . "&client_secret=".TEXTMASTER_CLIENT_SECRET;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $uri);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10); //timeout in seconds
        curl_setopt ($curl, CURLOPT_POSTFIELDS, $header);
        return curl_exec($curl);
    }

    private function getTextMasterAPIKeys($oAuthToken)
    {
        $uri    = TEXTMASTER_API_URI.'/admin/users/me';
        $header = array("Authorization: Bearer {$oAuthToken}");

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $uri);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10); //timeout in seconds
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        return curl_exec($curl);
    }

    private function displayTextmasterWebPage()
    {
        $login_form_url = self::CURRENT_INDEX.Tools::getValue('token').'&configure='.$this->name.'&login_to_textmaster_system_action=1&token='.Tools::getAdminTokenLite('AdminModules');
        $register_form_url = self::CURRENT_INDEX.Tools::getValue('token').'&configure='.$this->name.'&register_to_textmaster_system_action=1&token='.Tools::getAdminTokenLite('AdminModules');
        $this->context->smarty->assign(array(
            'login_form_url' => $login_form_url,
            'register_form_url' => $register_form_url
        ));

        return $this->_html .= $this->context->smarty->fetch(TEXTMASTER_TPL_DIR . 'admin/textmaster_web_page.tpl');
    }

    public function getAPIConnection()
    {
        if ($this->api_instance)
            return $this->api_instance;

        $api_key = TextMasterConfiguration::get('api_key');
        $api_secret = TextMasterConfiguration::get('api_secret');

        /* checks connection to API if at least one of API codes is set */
        if ($api_key or $api_secret)
        {
            $textmasterAPI = new TextMasterAPI($this, $api_key, $api_secret);
            if (!$textmasterAPI->isConnected())
            {
                $this->_html .= $this->displayWarnings(array($this->l('Please login / register')));
                return false;
            }
        }
        else
        {
            return false;
        }
        $this->api_instance = $textmasterAPI;

        return $this->api_instance;
    }

    private function addDataToProject()
    {
        $errors = array();
        $step = Tools::getValue('project_step');
        unset($_POST['tab'], $_POST['saveproject'], $_POST['id_category']); // unnecessary data in POST array has to be removed

        $url = self::CURRENT_INDEX.Tools::getValue('token').'&configure='.$this->name.'&token='.Tools::getValue('token')."&menu=create_project".(($id_project = Tools::getvalue('id_project')) ? "&id_project=$id_project" : '');

        switch ($step)
        {
            case 'products':
                if ($selected_products = Tools::getValue('selected_products_ids'))
                {
                    $this->textmaster_data_with_cookies_manager_obj->setSelectedProductsIds(Tools::getValue('selected_products_ids', array()));
                    $url.='&step=properties';
                }
                else
                    $errors[] = $this->l('Please select at least one product');
                break;
            case 'properties':
                if ($this->textmaster_data_with_cookies_manager_obj->projectDataExists('id_edit_project'))
                    $id_current_project = (int) $this->textmaster_data_with_cookies_manager_obj->getProjectData('id_edit_project');
                $selected_products = $this->textmaster_data_with_cookies_manager_obj->getSelectedProductsIds();
                $this->textmaster_data_with_cookies_manager_obj->setAllProject($_POST);

                $ctype = $this->textmaster_data_with_cookies_manager_obj->getProjectData('ctype');

                $this->textmaster_data_with_cookies_manager_obj->setProjectField($ctype.'_expertise_on', (int) isset($_POST[$ctype.'_expertise_on']));
                $this->textmaster_data_with_cookies_manager_obj->setProjectField('restrict_to_textmasters', (int) isset($_POST['restrict_to_textmasters']));
                $this->textmaster_data_with_cookies_manager_obj->setProjectField($ctype.'_quality_on', (int) isset($_POST[$ctype.'_quality_on']));

                if (isset($id_current_project))
                    $this->textmaster_data_with_cookies_manager_obj->setProjectField('id_edit_project', (int) $id_current_project);

                $this->textmaster_data_with_cookies_manager_obj->setSelectedProductsIds($selected_products);
                $url.='&step=summary';

                $language_from = $this->textmaster_data_with_cookies_manager_obj->getProjectData($ctype.'_language_from');

                if (!Language::isInstalled($language_from))
                    $errors[] = sprintf($this->l('Source language %s is not installed in PrestaShop'), $language_from);

                if (!$this->textmaster_data_with_cookies_manager_obj->getProjectData('project_name'))
                    $errors[] = $this->l('Project name is required');

                if (!$this->textmaster_data_with_cookies_manager_obj->getProjectData('project_data'))
                    $errors[] = $this->l('Select at least one product element to work with');

                if ($ctype == 'translation')
                {
                    $language_to = $this->textmaster_data_with_cookies_manager_obj->getProjectData($ctype.'_language_to');

                    if (!Language::isInstalled($language_to))
                        $errors[] = sprintf($this->l('Source language %s is not installed in PrestaShop'), $language_to);

                    if ($language_from == $language_to)
                        $errors[] = $this->l('Source language cannot be the same as target language');
                }

                break;
            case 'summary':
                $errors[] = $this->finishProjectCreation();
                break;
        }

        $this->textmaster_data_with_cookies_manager_obj->setProjectField('project_step', Tools::getValue('project_step', 'products'));

        /* if editing project, step is not summary and button 'next' was not pressed. This statement is ment for only 'save' button in header */
        /*		if ($id_project && $step != 'summary' && !Tools::isSubmit('saveprojectnext'))
                {
                    $this->addProjectDataToSession();
                    $errors[] = $this->finishProjectCreation();
                }*/

        if (!count($errors))
        {
            //$this->addProjectDataToSession();
            Tools::redirectAdmin($url);
        }
        else
            $this->_html .= $this->displayErrors($errors);
    }

    private function addProjectDataToSession()
    {
        $textmaster_project = $this->textmaster_data_with_cookies_manager_obj->getAllProject();
        $textmaster_project = array_merge($textmaster_project, $_POST);
        //$this->textmaster_data_with_cookies_manager_obj->setAllProject($textmaster_project); // data is saved into SESSION
    }

    private function finishProjectCreation()
    {
        $textmaster_project = $this->textmaster_data_with_cookies_manager_obj->getAllProject();
        $_POST = $textmaster_project;
        $args = $this->formatProjectArguments();
        $result = $this->saveProject($args, null, $this->textmaster_data_with_cookies_manager_obj->getSelectedProductsIds());

        if ($result === true)
        {
            $ctype = $this->textmaster_data_with_cookies_manager_obj->getProjectData('ctype');
            $this->textmaster_data_with_cookies_manager_obj->deleteAllProjectData();
            Tools::redirectAdmin(self::CURRENT_INDEX.Tools::getValue('token').'&configure='.$this->name.'&menu='.$ctype.'&token='.Tools::getAdminTokenLite('AdminModules'));
        }
        else // project was not created
            return $this->error;
    }

    private function addProjectDataToForm()
    {
        $id_project = Tools::getValue('id_project');
        $project = new TextMasterProject($id_project);

        //$this->textmaster_data_with_cookies_manager_obj->setAllProject($project->getProjectData());
        $this->textmaster_data_with_cookies_manager_obj->setProjectField('id', $project->id_project_api);
        $this->textmaster_data_with_cookies_manager_obj->setProjectField('project_step', 'summary');
        $language_level = $this->textmaster_data_with_cookies_manager_obj->getProjectData('language_level'). '_language_level';
        $this->textmaster_data_with_cookies_manager_obj->setProjectField('language_level', $language_level);
        $this->textmaster_data_with_cookies_manager_obj->setProjectField('same_author_must_do_entire_project', 0);

        $documents = TextMasterDocument::getDocuments($id_project, $project->id_project_api);
        $this->textmaster_data_with_cookies_manager_obj->setProjectDocuments($documents);
        $ctype = $this->textmaster_data_with_cookies_manager_obj->getProjectData('ctype');

        Tools::redirectAdmin(self::CURRENT_INDEX.Tools::getValue('token').'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&menu=create_project&ctype='.$ctype."&id_project=$id_project");
    }

    private function deleteDocument()
    {
        $id_document = Tools::getValue('id_document');
        $id_project = Tools::getvalue('id_project', 0);

        if ($this->textmaster_data_with_cookies_manager_obj->documentExists($id_document))
        {
            if ($id_project)
            {
                $document = new TextMasterDocument($id_document);
                if (!$document->delete())
                {
                    $this->_html .= $this->displayError($this->l('Unable to delete document'));
                    return;
                }
            }

            $this->textmaster_data_with_cookies_manager_obj->deleteDocument($id_document);
            $this->addFlashMessage($this->l('Document was successfully deleted'));
            Tools::redirectAdmin(self::CURRENT_INDEX.Tools::getValue('token').'&configure='.$this->name.'&menu=create_project&ctype='.Tools::getValue('ctype').'&step=documents&token='.Tools::getAdminTokenLite('AdminModules') . (($id_project = Tools::getvalue('id_project')) ? "&id_project=$id_project" : ''));
        }
        else
            $this->_html .= $this->displayError($this->l('Document doesn\'t exists'));
    }

    private function launchProject()
    {
        $id_project = Tools::getValue('id_project');
        $project = new TextMasterProject($id_project);

        $result = $project->launch();
        if ($result === true)
        {
            $this->addFlashMessage($this->l('Project was successfully launched'));
            Tools::redirectAdmin(self::CURRENT_INDEX.Tools::getValue('token').'&configure='.$this->name.'&menu='.Tools::getValue('menu').'&token='.Tools::getAdminTokenLite('AdminModules'));
        }
        else
            $this->_html .= $this->displayError($result);
    }

    private function deleteProjectAfterEdit($id_project)
    {
        $project = new TextMasterProject((int) $id_project);

        $result = $project->delete();
        if ($result === true)
            return true;
        else
            return $result;
    }

    private function deleteProject()
    {
        $id_project = Tools::getValue('id_project');
        $project = new TextMasterProject($id_project);

        $result = $project->delete();
        if ($result === true)
        {
            $this->addFlashMessage($this->l('Project was successfully deleted'));
            Tools::redirectAdmin(self::CURRENT_INDEX.Tools::getValue('token').'&configure='.$this->name.'&menu='.Tools::getValue('menu').'&token='.Tools::getAdminTokenLite('AdminModules'));
        }
        else
            $this->_html .= $this->displayError(sprintf($this->l('Project could not be deleted: %s'), $result));
    }

    public function saveProject($args, $project_name = null, $id_product = false)
    {
        $product = null;

        if (isset($args['project_data']) && !$args['project_data'])
        {
            $this->error = $this->l('Select at least one product element to work with');
            return false;
        }

        //$id_project = Tools::getValue('id_project', null);
        $textMasterProject = new TextMasterProject(null);
        $textMasterProject->ctype = $textMasterProject->type = $args['ctype'];

        if (!Language::isInstalled($args['language_from']))
        {
            $this->error = sprintf($this->l('Source language %s is not installed in PrestaShop'), $args['language_from']);
            return false;
        }

        if (isset($args['project_name']))
            $project_name = $args['project_name'];

        if (!is_array($id_product) && $id_product)
            $documents = $this->collectProductData($args, $id_product, $textMasterProject, $project_name);
        elseif(is_array($id_product))
            $documents = $this->collectProductData($args, $id_product, $textMasterProject, $project_name);

        $textMasterProject->name = pSQL($project_name);
        if (isset($args['same_author_must_do_entire_project']))
            $textMasterProject->same_author_must_do_entire_project = $args['same_author_must_do_entire_project'];

        $textMasterProject->language_level = $args['language_level'];
        $textMasterProject->quality = $args['quality'];
        $textMasterProject->expertise = $args['expertise'];
        $textMasterProject->language_from = $args['language_from'];
        $textMasterProject->category = $args['category'];
        $textMasterProject->project_briefing = pSQL($args['project_briefing']);
        $textMasterProject->vocabulary_type = $args['vocabulary_type'];
        $textMasterProject->target_reader_groups = $args['target_reader_groups'];
        $textMasterProject->grammatical_person = $args['grammatical_person'];
        $textMasterProject->textmasters = $args['textmasters'];

        $textMasterProject->status = $this->available_statuses[0];

        if ($textMasterProject->type == 'translation')
        {
            $textMasterProject->language_to = $args['language_to'];

            if (!Language::isInstalled($args['language_to']))
            {
                $this->error = sprintf($this->l('Target language %s is not installed in PrestaShop'), $args['language_to']);
                return false;
            }

            if ($args['language_from'] == $args['language_to'])
            {
                $this->error = $this->l('Source language cannot be the same as target language');
                return false;
            }

        }

        /*@todo Should be refactored*/
        $textMasterProject->__set('language_from', $args['language_from']);
        $textMasterProject->__set('language_to', $args['language_to']);
        $textMasterProject->__set('name', pSQL($project_name));

        if ($this->textmaster_data_with_cookies_manager_obj->projectDataExists('id_edit_project'))
        {
            $delete_current_project = $this->deleteProjectAfterEdit((int) $this->textmaster_data_with_cookies_manager_obj->getProjectData('id_edit_project'));
            if ($delete_current_project !== true)
            {
                $this->error = $delete_current_project;
                return false;
            }
        }


        /* trying to save project */
        $result = $textMasterProject->save();
        if(is_bool($result))
        {
            if ($result === false)
            {
                $this->error = $this->l('Project was successfully created but could not be added to prestashop database. Therefore it can only be managed via Textmaster website.');
                return false;
            }
            else
            {
                /* saving documents to database after project was successfully created */
                foreach ($documents as $document)
                {
                    $textMasterDocument = new TextMasterDocument();

                    $textMasterDocument->setApiData($document);
                    $textMasterDocument->id_project_api = $textMasterProject->id_project_api;
                    $textMasterDocument->id_project = $textMasterProject->id;

                    if (!$textMasterDocument->save())
                        return false;
                }

                /* lauches newly created project */
                if (!isset($id_project) || !$id_project)
                {
                    $result = $textMasterProject->launch();
                    if (!is_bool($result))
                    {
                        $this->addFlashWarning($this->l('Project was successfully created, but could not be launched: ') . $result);
                        return true;
                    }
                }
            }
        }
        else // if response is not in boolean form, then error message was returned
        {
            $this->error = $result;
            return false;
        }

        $this->addFlashMessage($this->l('Project was successfully created'));
        return true;
    }

    private function collectProductData($args, $products, &$textMasterProject, &$project_name, $source_language_id = null)
    {
        if (!$source_language_id)
            $source_language_id = Language::getIdByIso($args['language_from']);

        if (!isset($textMasterProject->documents))
            $textMasterProject->documents = array();

        if (is_array($products))
        {
            $documents = array();
            foreach ($products as $id_product)
                $documents = array_merge($documents, $this->collectProductData($args, $id_product, $textMasterProject, $project_name, $source_language_id));
            return $documents;
        }
        else
        {
            $id_product = $products;
            $product = new Product((int)$id_product, true);

            if (!$project_name && !isset($args['project_name']))
                $project_name = pSQL($product->name[Configuration::get('PS_LANG_DEFAULT')]);
            elseif (!$project_name && isset($args['project_name']))
                $project_name = pSQL($args['project_name']);

            $word_count = 0;

            $content_hash = array(); // contents of documents

            /* loops through selected product elements */
            foreach ($args['project_data'] as $element)
            {
                $text = '';
                if (isset($product->$element))
                {
                    $text = $product->$element;
                    if (isset($text[$source_language_id]))
                    {
                        $text = $text[$source_language_id];
                        if (is_array($text)) // element 'tags' will be array. We need to turn it to string
                            $text = implode(',', $text);
                    }
                }

                if (!empty($text))
                {
                    $content_hash[$element] = array('original_phrase' => $text);
                    $word_count+=str_word_count($text);
                }
            }

            $document_args = array(
                'title' => pSQL($product->name[Configuration::get('PS_LANG_DEFAULT')]),
                'id_product' => (int)$id_product,
                'type' => 'key_value',
                'word_count' => $word_count,
                'word_count_rule' => (isset($args['word_count_rule'])) ? $args['word_count_rule'] : 1,
                'original_content' => $content_hash,
                'instructions' => isset($args['instructions']) ? pSQL($args['instructions']) : '',
                'keyword_list' => isset($args['keyword_list']) ? pSQL($args['keyword_list']) : '',
                'keywords_repeat_count' => isset($args['keywords_repeat_count']) ? (int)$args['keywords_repeat_count'] : 0
            );

            return array($document_args);
        }
    }

    public function quoteProject($args, $project_name = null, $id_product = false)
    {
        $textMasterProject = new TextMasterProject();

        $textMasterProject->ctype = $textMasterProject->type = $args['ctype'];

        if (!is_array($id_product) && $id_product)
        {
            if (version_compare(_PS_VERSION_, '1.5', '<'))
            {
                $current_product = new Product((int) $id_product);
                $project_name = $current_product->name[(int) $this->context->language->id];
            }
            else
                $project_name = Product::getProductName($id_product, null, $this->context->language->id);
        }

        $textMasterProject->name = pSQL($project_name);
        if (isset($args['same_author_must_do_entire_project']))
            $textMasterProject->same_author_must_do_entire_project = $args['same_author_must_do_entire_project'];

        $textMasterProject->language_level = $args['language_level'];
        $textMasterProject->quality = $args['quality'];
        $textMasterProject->expertise = $args['expertise'];
        $textMasterProject->language_from = $args['language_from'];
        $textMasterProject->category = $args['category'];
        $textMasterProject->project_briefing = pSQL($args['project_briefing']);
        $textMasterProject->vocabulary_type = $args['vocabulary_type'];
        $textMasterProject->target_reader_groups = $args['target_reader_groups'];
        $textMasterProject->grammatical_person = $args['grammatical_person'];
        $textMasterProject->textmasters = $args['textmasters'];

        if ($textMasterProject->type == 'translation')
            $textMasterProject->language_to = $args['language_to'];

        $words = 0;

        if ($args['project_data'])
        {
            $word_counts = $this->countProductWords($id_product);

            foreach($args['project_data'] as $element)
                if (isset($word_counts[$element]) && isset($word_counts[$element][$args['language_from']]))
                    $words += $word_counts[$element][$args['language_from']];
        }

        $textMasterProject->total_word_count = $words;

        return $textMasterProject->quote();
    }

    private function formatDocument($data, $id_project, $id_project_api)
    {
        $id_document = (isset($data['id_document'])) ? $data['id_document'] : null;
        $document = new TextMasterDocument($id_document);
        $document->title = $document->name = pSQL($data['title']);
        $document->type = 'key_value';
        $document->word_count = $data['word_count'];
        $document->word_count_rule = (isset($data['word_count_rule'])) ? $data['word_count_rule'] : 1;
        $document->original_content = (isset($data['original_content'])) ? pSQL($data['original_content'], true) : '';

        $document->instructions = isset($data['instructions']) ? pSQL($data['instructions']) : '';
        $document->keyword_list = isset($data['keyword_list']) ? pSQL($data['keyword_list']) : '';
        $document->keywords_repeat_count = isset($data['keywords_repeat_count']) ? (int)$data['keywords_repeat_count'] : 0;

        if (!$id_project or !$id_project_api) return $document;

        $result = $document->save($id_project, $id_project_api);

        if($result === false)
        {
            $this->error = $this->l('Document was successfully created but could not be added into prestashop database.');
            return false;
        }
        elseif($result === true)
        {
            return $document->id;
        }
        else
        {
            $this->error = $result;
            return false;
        }
    }
    
    private function approveDocument()
    {
        $update_product_only = (bool)Tools::getValue('update_product_only');
        
        if ($update_product_only)
        {
            $error_message = $this->l('Product data could not be updated');
            $success_message = $this->l('Product data was successfully updated');
        }
        else
        {
            $error_message = $this->l('Document could not be approved');
            $success_message = $this->l('Document was successfully approved');
        }
        
        $id_document = (int)Tools::getValue('id_document');
        $id_product = (int)Tools::getValue('id_product');
        
        
        $document = new TextMasterDocument((int)$id_document);
        if (!Validate::isLoadedObject($document))
            return $this->displayError($error_message);
        
        $document_data = $document->getApiData();
        if (!$document_data || !isset($document_data['author_work']) || !isset($document_data['project_id']))
            return $this->displayError($error_message);
        
        $product = new Product((int)$id_product);
        if (!Validate::isLoadedObject($product))
            return $this->displayError($error_message);
        
        $project = TextMasterProject::getProjectByApiId($document_data['project_id'], true);
        if (!Validate::isLoadedObject($project))
            return $this->displayError($error_message);
        
        $project_data = $project->getProjectData();
        if (!isset($project_data['language_to']))
            return $this->displayError($error_message);
        
        $id_lang = Language::getIdByIso($project_data['language_to']);
        if (!$id_lang)
            return $this->displayError($error_message);
        
        if (!$update_product_only)
            if (!$document->approve())
                return $this->displayError($error_message);
        
        if (!$this->updateProductData($document_data, $product, $id_lang))
            return $this->displayError($error_message);
        
        $this->addFlashMessage($success_message);
        Tools::redirectAdmin(self::CURRENT_INDEX.Tools::getValue('token').'&configure='.$this->name.'&menu='.Tools::getValue('menu').'&id_project='.Tools::getValue('id_project').'&viewproject&token='.Tools::getAdminTokenLite('AdminModules'));
    }
    
    private function updateProductData($document_data, $product, $id_lang)
    {
        foreach ($document_data['author_work'] as $element => $content)
            $product->{$element}[$id_lang] = $content;

        if (!$product->update())
            return false;
        
        return true;
    }

    private function displayResults()
    {
        $id_project = (int)Tools::getValue('id_project');
        $project = new TextMasterProject($id_project);
        $menu = Tools::getValue('menu', 'translation');
        if ($menu == 'proofreading')
            $menu = $this->l('Proofreading projects');
        elseif ($menu == 'copywriting')
            $menu = $this->l('Copywriting projects');
        else
            $menu = $this->l('Translation projects');
        /* menu and toolbar begin */

        /*required for new navigation (template)*/
        $this->context->smarty->assign(array(
            'current_page_name' => $menu,
            'current_inner_page_name' => $project->name
        ));

        $this->displayNavigation();

        $statuses = array(
            'in_creation' 		=> $this->l('In creation'),
            'waiting_assignment'=> $this->l('Waiting assignment'),
            'in_progress' 		=> $this->l('In progress'),
            'in_review' 		=> $this->l('In review'),
            'completed' 		=> $this->l('Completed'),
            'incomplete' 		=> $this->l('Incomplete'),
            'paused' 			=> $this->l('Paused'),
            'canceled' 			=> $this->l('Cancelled'),
            'copyscape' 		=> $this->l('Copyscape'),
            'counting_words' 	=> $this->l('Counting_words'),
            'quality_control' 	=> $this->l('Quality control'));

        include_once(_TEXTMASTER_CLASSES_DIR_.'project.view.php');
        $view = new TextMasterProjectView($this);
        //$helper = $view->initForm();
        $this->context->smarty->assign(array('project' => $project,
            'documents' => TextMasterDocument::getDocuments($id_project, $project->id_project_api),
            'view' => $view,
            'statuses' => $statuses,
            'module_url' => self::CURRENT_INDEX.Tools::getValue('token').'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules') . "&menu=".Tools::getValue('menu') . "&id_project=".Tools::getValue('id_project')."&viewproject",
            'token' => sha1(_COOKIE_KEY_.$this->name)));

        $this->_html .= $this->context->smarty->fetch(TEXTMASTER_TPL_DIR . 'admin/project/view.tpl');
    }

    private function displayProjectCreationForm()
    {
        if (Tools::isSubmit('submitFilterdocument') && Tools::getValue('pagination'))
        {
            $_GET['submitFilterdocument'] = Tools::getValue('submitFilterdocument');
            $_GET['pagination'] = Tools::getValue('pagination');
        }

        $id_project = Tools::getvalue('id_project', 0);
        $steps = array('products' => 0, 'properties' => 1, 'summary' => 2);

        $current_step = Tools::getValue('step', 'products');
        /* creates space for project data in the first step */

        if ($current_step == 'products' && !$this->textmaster_data_with_cookies_manager_obj->projectExists())
        {
            $this->textmaster_data_with_cookies_manager_obj->setAllProject(array());
            $this->textmaster_data_with_cookies_manager_obj->setProjectField('project_step', '');
        }

        /* project creation form begin */
        include_once(_TEXTMASTER_CLASSES_DIR_.'project.view.php');
        $view = new TextMasterProjectView($this);
        /* project creation form end */

        switch ($current_step)
        {
            case 'products':
                $view->getProductsSelectForm();
                $view->getProductsSelectedProductsForm();
                break;
            case 'properties':
                if (version_compare(_PS_VERSION_, '1.5', '<'))
                {
                    if (version_compare(_PS_VERSION_, '1.5', '<'))
                        echo '<link href="'.$this->_path.'css/product.css" rel="stylesheet" type="text/css">';
                    else
                        $this->context->controller->addCSS($this->_path.'css/product.css', 'all');
                }
                $view->getProjectProperties();
                break;
            case 'summary':
                $view->getProjectSummary();
                break;
        }
        $textmasterAPI = new TextMasterAPI($this);
        $this->context->smarty->assign(array('textmaster_token' => sha1(_COOKIE_KEY_.$this->name),
            'view' => $view,
            'module_link' => self::CURRENT_INDEX.Tools::getValue('token').'&configure='.$this->name.'&menu=create_project&token='.Tools::getValue('token') . (($id_project) ? "&id_project=$id_project" : ''),
            'steps' => $steps,
            'id_lang' => $this->context->language->id,
            'project_step' => $this->textmaster_data_with_cookies_manager_obj->getProjectData('project_step'),
            'authors' => $textmasterAPI->authors['my_authors']
        ));

        $this->_html .= $this->context->smarty->fetch(TEXTMASTER_TPL_DIR . 'admin/project/index.tpl');
    }

    private function displayTranslationProjectsList()
    {
        include_once(_TEXTMASTER_CLASSES_DIR_.'translation_projects.view.php');
        $view = new TextMasterTranslationProjectsView($this);
        $this->_html .= $view->initList();
    }

    private function displayProofreadingProjectsList()
    {
        include_once(_TEXTMASTER_CLASSES_DIR_.'proofreading_projects.view.php');
        $view = new TextMasterProofreadingProjectsView($this);
        $this->_html .= $view->initList();
    }

    private function displayCopywritingProjectsList()
    {
        include_once(_TEXTMASTER_CLASSES_DIR_.'copywriting_projects.view.php');
        $view = new TextMasterCopywritingProjectsView($this);
        $helper = $view->initList();
        $this->_html .= $helper->generateList($view->getData(), $this->fields_list);
    }

    private function displayHelp()
    {
        $textmasterAPI = $this->getAPIConnection();
        $documentation = $textmasterAPI->getDocumentation();
        if (isset($documentation['web_content']))
            $this->_html .= '<fieldset>'.$documentation['web_content'] . '</fieldset>';
    }

    /**
     * displays settings form
     * @return settings page HTML code
     */

    private function displaySettingsForm()
    {
        include_once(_TEXTMASTER_CLASSES_DIR_.'settings.view.php');
        $view = new TextMasterSettingsView($this);
        //$helper = $view->initForm();
        $this->_html .= $view->displayForm();
    }

    /**
     * Updates configuration values
     */

    private function saveSettings()
    {
        $configuration = new TextMasterConfiguration();
        /* geting rid of unecessary values in POST */
        unset($_POST['tab']);
        unset($_POST['savesettings']);

        /* checkboxe values has to be nulled, because in case they are unchecked, they won't be added into POST array at all.
         * If POST array features only 2 elements, that means only api key and api secret codes should be updated.
        */
        if (count($_POST) != 2)
        {
            $configuration->copywriting_on =
            $configuration->proofreading_on =
            $configuration->translation_on =
            $configuration->copywriting_quality_on =
            $configuration->proofreading_quality_on =
            $configuration->translation_quality_on =
            $configuration->copywriting_expertise_on =
            $configuration->proofreading_expertise_on =
            $configuration->translation_expertise_on = 0;
        }

        foreach($_POST as $name => $value)
            $configuration->$name = pSQL($value); // new configuration values are being assigned

        if($configuration->updateConfiguration())
        {
            $this->addFlashMessage($this->l('Setting were successfully updated'));
            Tools::redirectAdmin(self::CURRENT_INDEX.Tools::getValue('token').'&configure='.$this->name.'&menu=settings&token='.Tools::getAdminTokenLite('AdminModules'));
            exit;
        }
        else
            $this->_html .= $this->displayError($this->l('Error! Settings were not updated'));

    }

    /* adds success message into session */
    private function addFlashMessage($msg)
    {
        $textmaster_data_with_cookie_manage_obj = new TextMasterDataWithCookiesManager();
        $textmaster_data_with_cookie_manage_obj->setSuccessMessage($msg);
    }

    public function addFlashWarning($msg)
    {
        $textmaster_data_with_cookie_manage_obj = new TextMasterDataWithCookiesManager();
        $textmaster_data_with_cookie_manage_obj->setWarningMessage($msg);
    }

    private function addFlashError($msg)
    {
        $textmaster_data_with_cookie_manage_obj = new TextMasterDataWithCookiesManager();
        $textmaster_data_with_cookie_manage_obj->setErrorMessage($msg);
    }

    /* displays success message only untill page reload */
    private function displayFlashMessagesIfIsset()
    {
        $textmaster_data_with_cookie_manage_obj = new TextMasterDataWithCookiesManager();

        if ($success_message = $textmaster_data_with_cookie_manage_obj->getSuccessMessage())
            $this->_html .= $this->displayConfirmation($success_message);

        if ($warning_message = $textmaster_data_with_cookie_manage_obj->getWarningMessage())
            $this->_html .= $this->displayWarnings($warning_message);

        if ($error_message = $textmaster_data_with_cookie_manage_obj->getErrorMessage())
            $this->_html .= $this->displayError($error_message);
    }

    private function displayErrors($errors)
    {
        $this->context->smarty->assign('errors', $errors);
        return $this->context->smarty->fetch(TEXTMASTER_TPL_DIR . 'admin/errors.tpl');
    }

    private function displayWarnings($warnings)
    {
        $this->context->smarty->assign('warnings', $warnings);
        return $this->context->smarty->fetch(TEXTMASTER_TPL_DIR . 'admin/warnings.tpl');
    }

    private function getAdminLink($tab)
    {
        # the ps15 way
        if (method_exists($this->context->link, 'getAdminLink'))
            return $this->context->link->getAdminLink('AdminModules');

        # the ps14 way
        return 'index.php?tab=AdminModules&configure='.$this->name.'&token='.Tools::getAdminToken('AdminModules'.(int)(Tab::getIdFromClassName('AdminModules')).(int)$this->context->cookie->id_employee);
    }

    public function formatProjectArguments()
    {
        $ctype = Tools::getValue('ctype');

        $args = array(
            'project_data' => Tools::getValue('project_data', 0),
            'ctype' => $ctype,
            'language_from' => Tools::getValue($ctype.'_language_from'),
            'language_level' => Tools::getValue("{$ctype}_language_level"),
            'quality' => Tools::getValue("{$ctype}_quality", Tools::getValue("{$ctype}_quality_on")),
            'expertise' => Tools::getValue("{$ctype}_expertise", Tools::getValue("{$ctype}_expertise_on")),
            'category' => Tools::getValue("{$ctype}_category"),
            'project_briefing' => Tools::getValue("{$ctype}_project_briefing"),
            'vocabulary_type' => Tools::getValue("{$ctype}_vocabulary_type"),
            'target_reader_groups' => Tools::getValue("{$ctype}_target_reader_groups"),
            'grammatical_person' => Tools::getValue("{$ctype}_grammatical_person"),
            'language_to' => Tools::getValue('translation_language_to'),
            'same_author_must_do_entire_project' => Tools::getValue("{$ctype}_same_author_must_do_entire_project", 1),
            'textmasters' => Tools::getValue('textmasters')
        );

        if ($project_name = Tools::getValue('project_name'))
            $args['project_name'] = $project_name;
        return $args;
    }

    private function array_merge_sum($arr1, $arr2)
    {
        foreach ($arr1 as $key1 => &$row1)
            if (isset($arr2[$key1]))
                foreach ($row1 as $lang1 => &$value1)
                    if (isset($arr2[$key1][$lang1]))
                        $value1+=$arr2[$key1][$lang1];
        return $arr1;
    }

    public function countProductWords($products)
    {
        if (!$products) return array();

        $counts = array();

        if (is_array($products))
        {
            foreach ($products as $id_product)
            {
                $counts = $this->array_merge_sum($this->countProductWords($id_product), $counts);
            }
            return $counts;
        }
        else
        {
            $id_product = $products;
        }

        $product = new Product($id_product, true);

        foreach ($product->name as $id_lang => $name)
            $counts['name'][LanguageCore::getIsoById($id_lang)] = str_word_count($name);

        foreach ($product->description as $id_lang => $description)
            $counts['description'][LanguageCore::getIsoById($id_lang)] = str_word_count($description);

        foreach ($product->description_short as $id_lang => $description_short)
            $counts['description_short'][LanguageCore::getIsoById($id_lang)] = str_word_count($description_short);

        foreach ($product->meta_title as $id_lang => $meta_title)
            $counts['meta_title'][LanguageCore::getIsoById($id_lang)] = str_word_count($meta_title);

        foreach ($product->meta_description as $id_lang => $meta_description)
            $counts['meta_description'][LanguageCore::getIsoById($id_lang)] = str_word_count($meta_description);

        foreach ($product->meta_keywords as $id_lang => $meta_keywords)
            $counts['meta_keywords'][LanguageCore::getIsoById($id_lang)] = str_word_count($meta_keywords);

        foreach ($product->link_rewrite as $id_lang => $link_rewrite)
            $counts['link_rewrite'][LanguageCore::getIsoById($id_lang)] = str_word_count($link_rewrite);

        if ($product->tags)
        {
            foreach ($product->tags as $id_lang => $tags)
            {
                $tags_count = 0;
                foreach ($tags as $tag)
                    $tags_count+=str_word_count($tag);
                $counts['tags'][LanguageCore::getIsoById($id_lang)] = $tags_count;
            }
        }
        return $counts;
    }

    public function collectProjectPropertiesData($products = array(), $get_content = false)
    {
        include_once(_TEXTMASTER_CLASSES_DIR_.'settings.view.php');
        $view = new TextMasterSettingsView($this);
        $view->collectTemplateData();
        $this->context->smarty->assign(array(
            'settings' => $view->settings_obj,
            'counts' => $this->countProductWords($products),
            'connected' => true,
            'token' => sha1(_COOKIE_KEY_.$this->name)
        ));
    }

    function hookDisplayAdminProductsExtra($params)
    {
        if (($id_product = Tools::getValue('id_product')) && $this->getAPIConnection())
        {
            $this->collectProjectPropertiesData($id_product);
        }

        $this->context->smarty->assign('module_url', $this->getAdminLink('AdminModules'));

        return $this->context->smarty->fetch(TEXTMASTER_TPL_DIR . 'admin/product.tpl');
    }

    function hookBackOfficeTop($params)
    {
        if (Tools::getValue('tab') == 'AdminCatalog' && $id_product = Tools::getValue('id_product', 0) && (Tools::isSubmit('updateproduct') || Tools::isSubmit('addproduct')))
        {
            if ($id_product)
            {
                $this->context->smarty->assign('content', $this->hookDisplayAdminProductsExtra($params));
                return $this->context->smarty->fetch(TEXTMASTER_TPL_DIR . 'admin/product_ps14.tpl');
            }
        }
    }

    public function getProductsByCategory($id_category, $count = false, $orderBy = '', $orderWay = '', $filter = '', $start = '', $pagination = '')
    {
        if ($orderBy && $orderWay)
        {
            $_GET['productsListOrderby'] = pSQL($orderBy);
            $_GET['productsListOrderway'] = pSQL($orderWay);
        }
        else
        {
            $orderBy = 'id_product';
            $orderWay = 'ASC';
        }

        $filtering = '';
        $filter_array = array();
        if ($filter)
        {
            $filtering = 'HAVING';
            foreach ($filter AS $row => $item)
            {
                if ($filtering == 'HAVING')
                    $filtering .= ' `'.pSQL($row).'` LIKE \'%'.pSQL($item).'%\'';
                else
                    $filtering .= ' AND `'.pSQL($row).'` LIKE \'%'.pSQL($item).'%\'';
                $this->context->cookie->__set('productsListFilter_'.pSQL($row), pSQL($item));
                array_push($filter_array, pSQL($row));
            }
        }

        if (!$count && (!$orderBy && !$orderWay))
        {
            $keys_array = array('id_product', 'reference', 'price', 'category', 'quantity', 'active', 'name', 'final_price');
            foreach ($keys_array AS $key => $value)
                if ($this->context->cookie->__isset('productsListFilter_'.$value) && !in_array($value, $filter_array))
                    $this->context->cookie->__unset('productsListFilter_'.$value);
        }

        $id_shop = $this->context->shop->id;
        if (version_compare(_PS_VERSION_, '1.5', '<'))
            $products = DB::getInstance()->executeS("
				SELECT
					p.`id_product` 		AS `id_product`,
					IFNULL((SELECT SUM(`at`.`quantity`) FROM `ps_product_attribute` `at` WHERE `at`.`id_product` = `cp`.`id_product`), p.`quantity`) AS `quantity`,
					p.`reference` 		AS `reference`,
					p.`price` 			AS `price`,
					cl.`name` 			AS `category`,
					p.`active` 			AS `active`,
					pl.`name` 			AS `name`,
					pl.`link_rewrite` 	AS `link_rewrite`,
					i.`id_image` 		AS `id_image`,
					p.`price` 			AS `final_price`
				FROM `"._DB_PREFIX_."category_product` cp
				LEFT JOIN `"._DB_PREFIX_."product` p ON (p.`id_product` = cp.`id_product`)
				LEFT JOIN `"._DB_PREFIX_."image` i ON (i.`id_product` = cp.`id_product` AND i.`cover` = '1')
				LEFT JOIN `"._DB_PREFIX_."category_lang` cl ON (cl.`id_category` = cp.`id_category` AND cl.`id_lang` = '".(int) $this->context->language->id."')
				LEFT JOIN `"._DB_PREFIX_."product_lang` pl ON (pl.`id_product` = cp.`id_product` AND pl.`id_lang` = '".(int) $this->context->language->id."')

				WHERE cp.`id_category` = '".(int)$id_category."' ".pSQL($filtering)."
                ORDER BY `".pSQL($orderBy)."` ".pSQL($orderWay).
                (($start && $pagination) ? " LIMIT ".(int)$start.", ".(int)$pagination : '')
            );
        else
            $products = DB::getInstance()->executeS("
				SELECT
					p.`id_product` 		AS `id_product`,
					p.`reference` 		AS `reference`,
					p.`price` 			AS `price`,
					psh.`id_shop`   	AS `id_shop`,
					cl.`name` 			AS `category`,
					sav.`quantity` 		AS `quantity`,
					p.`active` 			AS `active`,
					pl.`name` 			AS `name`,
					pl.`link_rewrite` 	AS `link_rewrite`,
					i.`id_image` 		AS `id_image`,
					p.`price` 			AS `final_price`
				FROM `"._DB_PREFIX_."category_product` cp
				LEFT JOIN `"._DB_PREFIX_."product` p ON (p.`id_product` = cp.`id_product`)
				LEFT JOIN `"._DB_PREFIX_."product_shop` psh ON (psh.`id_product` = p.`id_product`)
				LEFT JOIN `"._DB_PREFIX_."image` i ON (i.`id_product` = cp.`id_product` AND i.`cover` = '1')
				LEFT JOIN `"._DB_PREFIX_."category_lang` cl ON (cl.`id_category` = cp.`id_category` AND cl.`id_lang` = '".(int) $this->context->language->id."' AND cl.`id_shop` = '".(int) $this->context->shop->id."')
				LEFT JOIN `"._DB_PREFIX_."product_lang` pl ON (pl.`id_product` = cp.`id_product` AND pl.`id_lang` = '".(int) $this->context->language->id."' AND pl.`id_shop` = '".(int) $this->context->shop->id."')
				LEFT JOIN `"._DB_PREFIX_."stock_available` sav ON (sav.`id_product` = cp.`id_product` AND sav.`id_product_attribute` = 0 ".StockAvailable::addSqlShopRestriction(null, null, "sav").")
				WHERE cp.`id_category` = '".(int)$id_category."' ".pSQL($filtering)." AND psh.`id_shop` = " .(int)$id_shop . "
				ORDER BY `".pSQL($orderBy)."` ".pSQL($orderWay).
                (($start && $pagination) ? " LIMIT ".(int)$start.", ".(int)$pagination : '')
            );

        if (!$products)
            $products = array();

        foreach ($products AS $row => &$product)
        {
            $product['price'] = Tools::displayPrice($product['price']);
            $product['image'] = $this->getImage($product['id_product'], $product['id_image'], $product['link_rewrite']);
            $product['final_price'] = Tools::displayPrice(Product::getPriceStatic($product['id_product'], true, null, 2, null, false, true, 1, true));
        }

        return $products;
    }

    private function getImage($id_product, $id_image, $link_rewrite)
    {
        $id_image = Product::defineProductImage(array('id_image' => $id_image, 'id_product' => $id_product), $this->context->language->id);
        $img_profile = (version_compare(_PS_VERSION_, '1.5', '<')) ? 'small' : 'small_default';
        $image_link = $this->context->link->getImageLink($link_rewrite, $id_image, $img_profile);
        if (!$image_link)
            return $this->l('Image');
        else
            return $image_link;
    }

    public function getSelectedProducts($ids, $start = '', $pagination = '')
    {
        if (!$ids)
            $ids = array(); //to avoid errors

        if (!is_array($ids))
            $ids = explode(',', $ids);

        $ids = array_reverse($ids);
        $ids = implode(',', $ids);

        if (!$ids)
            $ids = 0;

        if (version_compare(_PS_VERSION_, '1.5', '<'))
            $products = DB::getInstance()->executeS("
				SELECT p.`id_product`, pl.`name` AS `name`, i.`id_image`, pl.`link_rewrite`
				FROM `"._DB_PREFIX_."product` p
				LEFT JOIN `"._DB_PREFIX_."product_lang` pl ON (pl.`id_product` = p.`id_product` AND pl.`id_lang` = '".(int) $this->context->language->id."')
				LEFT JOIN `"._DB_PREFIX_."image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = '1')
				WHERE p.`id_product` IN (".pSQL($ids).")
				ORDER BY FIELD (p.`id_product`, ".pSQL($ids).")".
                (($start && $pagination) ? " LIMIT ".(int)$start.", ".(int)$pagination : '')
            );
        else
            $products = DB::getInstance()->executeS("
				SELECT p.`id_product`, pl.`name` AS `name`, i.`id_image`, pl.`link_rewrite`
				FROM `"._DB_PREFIX_."product` p
				LEFT JOIN `"._DB_PREFIX_."product_lang` pl ON (pl.`id_product` = p.`id_product` AND pl.`id_lang` = '".(int) $this->context->language->id."' AND pl.`id_shop` = '".(int) $this->context->shop->id."')
				LEFT JOIN `"._DB_PREFIX_."image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = '1')
				WHERE p.`id_product` IN (".pSQL($ids).")
				ORDER BY FIELD (p.`id_product`, ".pSQL($ids).")".
                (($start && $pagination) ? " LIMIT ".(int)$start.", ".(int)$pagination : ''));

        if (!$products)
            $products = array();

        foreach ($products AS $row => &$product)
            $product['image'] = $this->getImage($product['id_product'], $product['id_image'], $product['link_rewrite']);

        return $products;
    }

    public function getProductTitle($id_product)
    {
        if (version_compare(_PS_VERSION_, '1.5', '<'))
            return DB::getInstance()->getValue("
				SELECT `name`
				FROM `"._DB_PREFIX_."product_lang`
				WHERE `id_product` = '".(int) $id_product."'
					AND `id_lang` = '".(int) $this->context->language->id."'
			");
        else
            return DB::getInstance()->getValue("
				SELECT `name`
				FROM `"._DB_PREFIX_."product_lang`
				WHERE `id_product` = '".(int) $id_product."'
					AND `id_lang` = '".(int) $this->context->language->id."'
					AND `id_shop` = '".(int) $this->context->shop->id."'
			");
    }

/*    public function getProjectsListDataByType($type)
    {
        return DB::getInstance()->executeS("
			SELECT
				`id_project_api` AS `id`,
				`type` AS `ctype`
			FROM `"._DB_PREFIX_."textmaster_project`
			WHERE `type` = '".pSQL($type)."' AND `id_shop` = " . (int)Context::getContext()->shop->id . "
		");
    }*/

    public function getTopCategory($id_lang = null)
    {
        if (is_null($id_lang))
            $id_lang = $this->context->language->id;
        $id_category = Db::getInstance()->getValue('
		SELECT `id_category`
		FROM `'._DB_PREFIX_.'category`
		WHERE `id_parent` = 0');
        return new Category($id_category, $id_lang);
    }

    public static function getJson($array)
    {
        return Tools::jsonEncode($array);
    }
}