<?php
/**
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2014 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

include_once dirname(__FILE__) . '/features/PagSeguroLibrary/PagSeguroLibrary.php';
include_once dirname(__FILE__) . '/features/modules/pagsegurofactoryinstallmodule.php';
include_once dirname(__FILE__) . '/features/util/encryptionIdPagSeguro.php';

if (! defined('_PS_VERSION_')) {
    exit();
}

class PagSeguro extends PaymentModule
{
    private $modulo;

    protected $errors = array();

    private $html;

    public $context;

    private $menuTab = 'menuTab1';

    public function __construct()
    {
        $this->name = 'pagseguro';
        $this->tab = 'payments_gateways';
        $this->version = '1.8';
        $this->author = 'PagSeguro Internet LTDA.';
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        
        parent::__construct();

        if(!function_exists('curl_init')) { throw new Exception("PagSeguroLibrary: cURL library is required."); }
        
        $this->displayName = $this->l('PagSeguro');
        $this->description = $this->l('Receba pagamentos por cartão de crédito, transferência bancária e boleto.');
        $this->confirmUninstall = $this->l('Tem certeza que deseja remover este módulo?');
        
        if (version_compare(_PS_VERSION_, '1.5.0.2', '<')) {
            include_once (dirname(__FILE__) . '/backward_compatibility/backward.php');
        }

        $this->setContext();
        
        $this->modulo = PagSeguroFactoryInstallModule::createModule(_PS_VERSION_);
        
    }

    private function setContext() {
        $this->context = Context::getContext();
    }
    
    public function install()
    {
        if (version_compare(PagSeguroLibrary::getVersion(), '2.1.8', '<=')) {
            if (! $this->validatePagSeguroRequirements()) {
                return false;
            }
        }
        
        if (! $this->validatePagSeguroId()) {
            return  false;
        }
        
        if (! $this->validateOrderMessage()) {
            return false;
        }
        
        if (! $this->generatePagSeguroOrderStatus()) {
            return false;
        }
        
        if (! $this->createTables()) {
            return false;
        }
        
        if (! $this->modulo->installConfiguration()) {
            return false;
        }
        
        if (! parent::install() or
            ! $this->registerHook('payment') or
            ! $this->registerHook('paymentReturn') or
            ! Configuration::updateValue('PAGSEGURO_EMAIL', '') or
            ! Configuration::updateValue('PAGSEGURO_TOKEN', '') or
            ! Configuration::updateValue('PAGSEGURO_URL_REDIRECT', '') or
            ! Configuration::updateValue('PAGSEGURO_NOTIFICATION_URL', '') or
            ! Configuration::updateValue('PAGSEGURO_CHARSET', PagSeguroConfig::getData('application', 'charset')) or
            ! Configuration::updateValue('PAGSEGURO_LOG_ACTIVE', PagSeguroConfig::getData('log', 'active')) or
            ! Configuration::updateValue('PAGSEGURO_RECOVERY_ACTIVE', false) or
            ! Configuration::updateValue('PAGSEGURO_DAYS_RECOVERY', 1) or
            ! Configuration::updateValue('PAGSEGURO_CHECKOUT', false) or
            ! Configuration::updateValue(
                'PAGSEGURO_LOG_FILELOCATION',
                PagSeguroConfig::getData('log', 'fileLocation')
            )) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
    	if (! $this->uninstallOrderMessage()) {
    		return false;
    	}
    	
        if (! $this->modulo->uninstallConfiguration()) {
            return false;
        }
        
        if (! Configuration::deleteByName('PAGSEGURO_EMAIL')
        or ! Configuration::deleteByName('PAGSEGURO_TOKEN')
        or ! Configuration::deleteByName('PAGSEGURO_URL_REDIRECT')
        or ! Configuration::deleteByName('PAGSEGURO_NOTIFICATION_URL')
        or ! Configuration::deleteByName('PAGSEGURO_CHARSET')
        or ! Configuration::deleteByName('PAGSEGURO_LOG_ACTIVE')
        or ! Configuration::deleteByName('PAGSEGURO_RECOVERY_ACTIVE')
        or ! Configuration::deleteByName('PAGSEGURO_DAYS_RECOVERY')
        or ! Configuration::deleteByName('PAGSEGURO_LOG_FILELOCATION')
        or ! Configuration::deleteByName('PS_OS_PAGSEGURO')
        or ! Configuration::deleteByName('PAGSEGURO_CHECKOUT')
        or ! parent::uninstall()) {
            return false;
        }

        return true;
    }

    public function getContent()
    {
    	if (Tools::isSubmit('btnSubmit')) {
            
            $this->postValidation();
            
            if (! count($this->errors)) {
                $this->postProcess();
            } else {
                foreach ($this->errors as $error) {
                    $this->html .= '<div class="module_error alert error" '.Util::getWidthVersion(_PS_VERSION_).'">'
                        . $error . '</div>';
                }
            }
        }
        
        $this->html .= $this->displayForm();
        
        return $this->html;
    }
    
    private function getConfigurationTabHtml()
    {
        
        $this->context->smarty->assign('titulo', $this->l('Configuração'));

        $charset = Util::getCharsetOptions();
        $selection = array_search(Configuration::get('PAGSEGURO_CHARSET'), $charset);

        $this->context->smarty->assign('keychartset', array_keys($charset));
        $this->context->smarty->assign('valueschartset', array_values($charset));
        $this->context->smarty->assign('escolhacharset', $selection);

        $checkout = Util::getTypeCheckout();

        $this->context->smarty->assign('keycheckout', array_keys($checkout));
        $this->context->smarty->assign('valuescheckout', array_values($checkout));
        $this->context->smarty->assign('escolhacheckout', Configuration::get('PAGSEGURO_CHECKOUT'));

        $this->context->smarty->assign('email', Tools::safeOutput(Configuration::get('PAGSEGURO_EMAIL')));
        $this->context->smarty->assign('token', Tools::safeOutput(Configuration::get('PAGSEGURO_TOKEN')));

        return $this->display(__PS_BASE_URI__ . 'modules/pagseguro', '/views/templates/menu/settings.tpl');
    
    }
    
    private function getExtrasTabHtml()
    {

        $this->context->smarty->assign('titulo', $this->l('Extras'));
        
        $active = Util::getActive();

        $this->context->smarty->assign('keylogactive', array_keys($active));
        $this->context->smarty->assign('valueslogactive', array_values($active));
        $this->context->smarty->assign('escolhalogactive', Configuration::get('PAGSEGURO_LOG_ACTIVE'));

        $this->context->smarty->assign('keyrecoveryactive', array_keys($active));
        $this->context->smarty->assign('valuesrecoveryactive', array_values($active));
        $this->context->smarty->assign('escolharecoveryactive', Configuration::get('PAGSEGURO_RECOVERY_ACTIVE'));

        $days_to_recovery = Util::getDaysRecovery();

        $this->context->smarty->assign('keydaystorecovery', array_keys($days_to_recovery));
        $this->context->smarty->assign('valuesdaystorecovery', array_values($days_to_recovery));
        $this->context->smarty->assign('escolhadaystorecovery', Tools::safeOutput(Configuration::get('PAGSEGURO_DAYS_RECOVERY')));

        $this->context->smarty->assign('urlNotification', $this->getNotificationUrl());
        $this->context->smarty->assign('urlRedirection', $this->getDefaultRedirectionUrl());
        $this->context->smarty->assign('fileLocation', Tools::safeOutput(Configuration::get('PAGSEGURO_LOG_FILELOCATION')));

        return $this->display(__PS_BASE_URI__ . 'modules/pagseguro', '/views/templates/menu/extras.tpl');
    }
    
    private function getConciliationTabHtml()
    {
        
        $dias = "";
        
        foreach (Util::getDaysSearch() as $value) {
            $dias .= "<option value='" . $value . "'>" . $value . "</option>";
        }
        
        $adminToken = Tools::getAdminTokenLite('AdminOrders');
        $this->context->smarty->assign('dias', $dias);
        $this->context->smarty->assign('urlAdminOrder', $_SERVER['SCRIPT_NAME'].'?tab=AdminOrders');
        $this->context->smarty->assign('adminToken', $adminToken);
        $this->context->smarty->assign('tableResult', '');
        $this->context->smarty->assign('titulo', $this->l('Conciliação'));
        if ( Configuration::get('PAGSEGURO_EMAIL') and 
        	 Configuration::get('PAGSEGURO_TOKEN') )
        	$this->context->smarty->assign('regError', false);
		else
			$this->context->smarty->assign('regError', true);
        $conteudo = "";
        $conteudo = $this->display(
            __PS_BASE_URI__ . 'modules/pagseguro',
            '/views/templates/menu/conciliation.tpl'
        );
        
        return $conteudo;
    
    }

    public function getAbandonedTabHtml()
    {

        $adminToken = Tools::getAdminTokenLite('AdminOrders');

        $days_recovery = Tools::safeOutput(Configuration::get('PAGSEGURO_DAYS_RECOVERY'));

    
        $tableResult = include_once(dirname(__FILE__) . '/features/abandoned/abandoned.php');

        $this->context->smarty->assign('urlAdminOrder', $_SERVER['SCRIPT_NAME'].'?tab=AdminOrders');
        $this->context->smarty->assign('adminToken', $adminToken);
        $this->context->smarty->assign('tableResult', $tableResult['table']);
        $this->context->smarty->assign('errorMsg', $tableResult['errorMsg']);
        $this->context->smarty->assign('is_recovery_cart', Configuration::get('PAGSEGURO_RECOVERY_ACTIVE'));
        $this->context->smarty->assign('days_recovery', $days_recovery);
        $this->context->smarty->assign('titulo', $this->l('Abandonadas'));

        $content = "";
        $content = $this->display(
            __PS_BASE_URI__ . 'modules/pagseguro', 
            '/views/templates/menu/abandoned.tpl'
        );

        return $content;
    
    }
    
    private function getRequirementsTabHtml()
    {

        $this->context->smarty->assign('titulo', $this->l('Requisitos'));
        
        $image = '../modules/pagseguro/assets/images/';
        $error = array();
    
        $validation = PagSeguroConfig::validateRequirements();
        foreach ($validation as $key => $value) {
            if (Tools::strlen($value) == 0) {
                $error[$key][0] = $image.'ok.png';
                $error[$key][1] = null;
            } else {
                $error[$key][0] = $image.'delete.png';
                $error[$key][1] = $value;
            }
        }
    
        $currency = self::returnIdCurrency();
        /** Currency validation */
        if (!$currency) {
            $error['moeda'][0] = $image.'delete.png';
            $error['moeda'][1] = $this->missedCurrencyMessage();
        } else {
            $error['moeda'][0] = $image.'ok.png';
            $error['moeda'][1] = "Moeda REAL instalada.";
        }

        $error['curl'][1] = (is_null($error['curl'][1]) ? "Biblioteca cURL instalada." : $error['curl'][1]);
        $error['dom'][1] = (is_null($error['dom'][1]) ? "DOM XML instalado." : $error['dom'][1]);
        $error['spl'][1] = (is_null($error['spl'][1]) ? "Biblioteca padrão do PHP(SPL) instalada." : $error['spl'][1]);
        $error['version'][1] =
                (is_null($error['version'][1]) ? "Versão do PHP superior à 5.3.3." : $error['version'][1]);

        $this->context->smarty->assign('error', $error);

        return $this->display(__PS_BASE_URI__ . 'modules/pagseguro', '/views/templates/menu/requirements.tpl');
    
    }
    
    private function displayForm()
    {

        $this->context->smarty->assign('module_dir', _PS_MODULE_DIR_ . 'pagseguro/');
        $this->context->smarty->assign('action_post', Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']));
        $this->context->smarty->assign('email_user', Tools::safeOutput(Configuration::get('PAGSEGURO_EMAIL')));
        $this->context->smarty->assign('token_user', Tools::safeOutput(Configuration::get('PAGSEGURO_TOKEN')));
        $this->context->smarty->assign('redirect_url', $this->getDefaultRedirectionUrl());
        $this->context->smarty->assign('notification_url', $this->getNotificationUrl());
        $this->context->smarty->assign('charset_options', Util::getCharsetOptions());
        $this->context->smarty->assign(
            'charset_selected',
            array_search(
                Configuration::get('PAGSEGURO_CHARSET'),
                Util::getCharsetOptions()
            )
        );
        $this->context->smarty->assign('active_log', Util::getActive());
        $this->context->smarty->assign('type_checkout', Util::getTypeCheckout());
        $this->context->smarty->assign('checkout_selected', Configuration::get('PAGSEGURO_CHECKOUT'));
        $this->context->smarty->assign('log_selected', Configuration::get('PAGSEGURO_LOG_ACTIVE'));
        $this->context->smarty->assign('recovery_selected', Configuration::get('PAGSEGURO_RECOVERY_ACTIVE'));
        $this->context->smarty->assign('diretorio_log', Tools::safeOutput(Configuration::get('PAGSEGURO_LOG_FILELOCATION')));
        $this->context->smarty->assign('days_recovery', Configuration::get('PAGSEGURO_DAYS_RECOVERY'));
        $this->context->smarty->assign('checkActiveSlide', Tools::safeOutput($this->checkActiveSlide()));
        $this->context->smarty->assign('css_version', $this->getCssDisplay());
        if (PHP_OS == "Linux")
			if (version_compare(_PS_VERSION_, '1.6.0.1', '>'))
        		$this->context->smarty->assign('cheats', true);

        $menuTabPost = Tools::getValue('menuTab');

        if (empty($menuTabPost)) {
            $this->context->smarty->assign('menu_tab', 'menuTab1');
        } else {
            $menuTab = Tools::getValue('menuTab');
            $this->context->smarty->assign('menu_tab', $menuTab);
        }

        $this->context->smarty->assign(array(
            'tab' => array(
                'config' => array(
                    'title' => $this->l('Configuração'),
                    'content' => $this->getConfigurationTabHtml(),
                    'icon' => '',
                    'tab' => 1,
                    'selected' => ($this->menuTab == 'menuTab1') ? true : false,
                ),
                'extras' => array(
                    'title' => $this->l('Extras'),
                    'content' => $this->getExtrasTabHtml(),
                    'icon' => '',
                    'tab' => 2,
                    'selected' => ($this->menuTab == 'menuTab2') ? true : false,
                ),
                'conciliation' => array(
                    'title' => $this->l('Conciliação'),
                    'content' => $this->getConciliationTabHtml(),
                    'icon' => '',
                    'tab' => 3,
                    'selected' => ($this->menuTab == 'menuTab3') ? true : false,
                ),
                'abandoned' => array(
                    'title' => $this->l('Abandonadas'),
                    'content' => $this->getAbandonedTabHtml(),
                    'icon' => '',
                    'tab' => 4,
                    'selected' => ($this->menuTab == 'menuTab4') ? true : false,
                ),
                'requirements' => array(
                    'title' => $this->l('Requisitos'),
                    'content' => $this->getRequirementsTabHtml(),
                    'icon' => '',
                    'tab' => 5,
                    'selected' => ($this->menuTab == 'menuTab5') ? true : false,
                ),
            )
        ));
    	
        return $this->display(__PS_BASE_URI__ . 'modules/pagseguro', 'views/templates/front/admin_pagseguro.tpl');
    }
    
    /***
     * Realize post validations according with PagSeguro standards
     * case any inconsistence, an item is added to $_postErrors
     */
    private function postValidation()
    {
        if (Tools::isSubmit('btnSubmit')) {
            
            $this->menuTab = Tools::getValue('menuTab');
            $email = Tools::getValue('pagseguro_email');
            $token = Tools::getValue('pagseguro_token');
            $pagseguro_url_redirect = Tools::getValue('pagseguro_url_redirect');
            $pagseguro_notification_url = Tools::getValue('pagseguro_notification_url');
            $charset = Tools::getValue('pagseguro_charset');
            $pagseguro_log = Tools::getValue('pagseguro_log');
            
            /** E-mail validation */
            if (! $email) {
                $this->errors[] = $this->errorMessage('E-MAIL');
            } elseif (Tools::strlen($email) > 60) {
                $this->errors[] = $this->invalidFieldSizeMessage('E-MAIL');
            } elseif (! Validate::isEmail($email)) {
                $this->errors[] = $this->invalidMailMessage('E-MAIL');
            }
            
            /** Token validation */
            if (! $token) {
                $this->errors[] = $this->errorMessage('TOKEN');
            } elseif (Tools::strlen($token) != 32) {
                $this->errors[] = $this->invalidFieldSizeMessage('TOKEN');
            }
            
            /** URL redirect validation */
            if ($pagseguro_url_redirect && ! filter_var($pagseguro_url_redirect, FILTER_VALIDATE_URL)) {
                $this->errors[] = $this->invalidUrl('URL DE REDIRECIONAMENTO');
            }
            
            /** Notification url validation */
            if ($pagseguro_notification_url && ! filter_var($pagseguro_notification_url, FILTER_VALIDATE_URL)) {
                $this->errors[] = $this->invalidUrl('URL DE NOTIFICAÇÃO');
            }
            
            /** Charset validation */
            if (! array_key_exists($charset, Util::getCharsetOptions())) {
                $this->errors[] = $this->invalidValue('CHARSET');
            }
            
            /** Log validation */
            if (! array_key_exists($pagseguro_log, Util::getActive())) {
                $this->errors[] = $this->invalidValue('LOG');
            }
        }
    }

    /***
     * Realize PagSeguro database keys values
     */
    private function postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            
            $charsets = Util::getCharsetOptions();
            
            Configuration::updateValue('PAGSEGURO_EMAIL', Tools::getValue('pagseguro_email'));
            Configuration::updateValue('PAGSEGURO_TOKEN', Tools::getValue('pagseguro_token'));
            Configuration::updateValue('PAGSEGURO_URL_REDIRECT', Tools::getValue('pagseguro_url_redirect'));
            Configuration::updateValue('PAGSEGURO_NOTIFICATION_URL', Tools::getValue('pagseguro_notification_url'));
            Configuration::updateValue('PAGSEGURO_CHARSET', $charsets[Tools::getValue('pagseguro_charset')]);
            Configuration::updateValue('PAGSEGURO_LOG_ACTIVE', Tools::getValue('pagseguro_log'));
            Configuration::updateValue('PAGSEGURO_CHECKOUT', Tools::getValue('pagseguro_checkout'));
            Configuration::updateValue('PAGSEGURO_LOG_FILELOCATION', Tools::getValue('pagseguro_log_dir'));
            Configuration::updateValue('PAGSEGURO_RECOVERY_ACTIVE', Tools::getValue('pagseguro_recovery'));
            Configuration::updateValue('PAGSEGURO_DAYS_RECOVERY', Tools::getValue('pagseguro_days_recovery'));

            /** Verify if log file exists, case not try create */
            if (Tools::getValue('pagseguro_log')) {
                $this->verifyLogFile(Tools::getValue('pagseguro_log_dir'));
            }
        }
        $this->html .= '<div class="module_confirmation conf confirm" '.Util::getWidthVersion(_PS_VERSION_).' ">'
            . $this->l('Dados atualizados com sucesso') . '</div>';
    }

    private function errorMessage($field)
    {
        return sprintf($this->l("O campo %s deve ser informado."), $field);
    }

    private function missedCurrencyMessage()
    {
        return sprintf(
            $this->l(
                'Verifique se a moeda REAL esta instalada e ativada.'
            )
        );
    }

    private function invalidMailMessage($field)
    {
        return sprintf($this->l('O campo %s deve ser conter um email válido.'), $field);
    }

    private function invalidFieldSizeMessage($field)
    {
        return sprintf($this->l('O campo %s está com um tamanho inválido'), $field);
    }

    private function invalidValue($field)
    {
        return sprintf($this->l('O campo %s contém um valor inválido.'), $field);
    }

    private function invalidUrl($field)
    {
        return sprintf($this->l('O campo %s deve conter uma url válida.'), $field);
    }

    private function checkActiveSlide()
    {
        return Tools::getValue('activeslide') ? Tools::getValue('activeslide') : 1;
    }

    public static function returnIdCurrency($value = 'BRL')
    {
        $sql = 'SELECT `id_currency`
        FROM `' . _DB_PREFIX_ . 'currency`
        WHERE `deleted` = 0 
        AND `iso_code` = "' . $value . '"';
        
        $id_currency = (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));
        return empty($id_currency) ? 0 : $id_currency[0]['id_currency'];
    }

    public function hookPayment($params)
    {
        
        if (! $this->active) {
            return;
        }
        
        $this->modulo->paymentConfiguration($params);
        
        if (version_compare(_PS_VERSION_, '1.6.0.1', '<'))
        	$bootstrap = true;
        else
        	$bootstrap = false;
        
        $this->context->smarty->assign('version', $bootstrap);
        
        return $this->display(__PS_BASE_URI__ . 'modules/pagseguro', '/views/templates/hook/payment.tpl');
    }

    public function hookPaymentReturn($params)
    {
        $this->modulo->returnPaymentConfiguration($params);
        return $this->display(__PS_BASE_URI__ . 'modules/pagseguro', '/views/templates/hook/payment_return.tpl');
    }

    private function validatePagSeguroRequirements()
    {
        $condional = true;
        
        foreach (PagSeguroConfig::validateRequirements() as $value) {
            if (! Tools::isEmpty($value)) {
                $condional = false;
                $this->errors[] = Tools::displayError($value);
            }
        }
        
        if (! $condional) {
            $this->html = $this->displayError(implode('<br />', $this->errors));
        }
        
        return $condional;
    }

    private function createTables()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pagseguro_order` (
            `id` int(11) unsigned NOT NULL auto_increment,
            `id_transaction` varchar(255) NOT NULL,
            `id_order` int(10) unsigned NOT NULL ,
            PRIMARY KEY  (`id`)
            ) ENGINE=' . _MYSQL_ENGINE_ .
             ' DEFAULT CHARSET=utf8  auto_increment=1;';
        
        if (! Db::getInstance()->Execute($sql)) {
            return false;
        }
        return true;
    }
    
    private function validatePagSeguroId()
    {
        $id = Configuration::get('PAGSEGURO_ID');
        if (empty($id)) {
            $id = EncryptionIdPagSeguro::idRandomGenerator();
            return Configuration::updateValue('PAGSEGURO_ID', $id);
        }
        return true;
    }
    
    private function validateOrderMessage()
    {
        $orderMensagem = new OrderMessage();
        
        foreach (Language::getLanguages(false) as $language) {
            $orderMensagem->name[(int) $language['id_lang']] = "cart recovery pagseguro";
            $orderMensagem->message[(int) $language['id_lang']] =
                "Verificamos que você não concluiu sua compra. Clique no link abaixo para dar prosseguimento.";
        }

        $orderMensagem->date_add = date('now');
        $orderMensagem->save();

        return Configuration::updateValue('PAGSEGURO_MESSAGE_ORDER_ID', $orderMensagem->id);
    }
    
	private function uninstallOrderMessage()
    {
    	$orders = array();
    	$sql = "SELECT `id_order_message` as id FROM `"._DB_PREFIX_."order_message_lang` WHERE `name` = 'cart recovery pagseguro'";
    	$result = (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));
    
    	if ($result) {
    
    		$bool = false;
    		foreach ($result as $order_message) {
    
    			if (!$bool) {
    
    				$orders[] = $order_message['id'];
    				$bool = true;
    			} else {
    
    				if ( array_search($order_message['id'], $orders) === false){
    					$orders[] = $order_message['id'];
    				}
    			}
    		}
    
    		for($i = 0; $i < count($orders) ;$i++){
    
    			$sql = "DELETE FROM `"._DB_PREFIX_."order_message` WHERE `id_order_message` = '".$orders[$i]."'";
    			Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);
    		}
    
    		for($i = 0; $i < count($result) ;$i++){
    			$id = $result[$i]['id'];
    			$sql = "DELETE FROM `"._DB_PREFIX_."order_message_lang` WHERE `id_order_message` = '".$id."'";
    			Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);
    		}
    		return true;
    	}
    	return false;
    }

    private function generatePagSeguroOrderStatus()
    {
        $orders_added = true;
        $name_state = null;
        $image = _PS_ROOT_DIR_ . '/modules/pagseguro/logo.gif';
        
        foreach (Util::getCustomOrderStatusPagSeguro() as $key => $statusPagSeguro) {
            
            $order_state = new OrderState();
            $order_state->module_name = 'pagseguro';
            $order_state->send_email = $statusPagSeguro['send_email'];
            $order_state->color = '#95D061';
            $order_state->hidden = $statusPagSeguro['hidden'];
            $order_state->delivery = $statusPagSeguro['delivery'];
            $order_state->logable = $statusPagSeguro['logable'];
            $order_state->invoice = $statusPagSeguro['invoice'];
            
            if (version_compare(_PS_VERSION_, '1.5', '>')) {
                $order_state->unremovable = $statusPagSeguro['unremovable'];
                $order_state->shipped = $statusPagSeguro['shipped'];
                $order_state->paid = $statusPagSeguro['paid'];
            }
            
            $order_state->name = array();
            $order_state->template = array();
            $continue = false;
            
            foreach (Language::getLanguages(false) as $language) {
                
                $list_states = $this->findOrderStates($language['id_lang']);
                
                $continue = $this->checkIfOrderStatusExists(
                    $language['id_lang'],
                    $statusPagSeguro['name'],
                    $list_states
                );
                
                if ($continue) {
                    $order_state->name[(int) $language['id_lang']] = $statusPagSeguro['name'];
                    $order_state->template[$language['id_lang']] = $statusPagSeguro['template'];
                }
                
                if ($key == 'WAITING_PAYMENT' or $key == 'IN_ANALYSIS') {
                    
                    $this->copyMailTo($statusPagSeguro['template'], $language['iso_code'], 'html');
                    $this->copyMailTo($statusPagSeguro['template'], $language['iso_code'], 'txt');
                }
                
            }
            
            if ($continue) {
                
                if ($order_state->add()) {
                    
                    $file = _PS_ROOT_DIR_ . '/img/os/' . (int) $order_state->id . '.gif';
                    copy($image, $file);
                    
                }
            }
            
            if ($key == 'INITIATED') {
                $name_state = $statusPagSeguro['name'];
            }
        }
        
        Configuration::updateValue('PS_OS_PAGSEGURO', $this->returnIdOrderByStatusPagSeguro($name_state));
        
        return $orders_added;
    }
    
    private function copyMailTo($name, $lang, $ext)
    {
        
        $template = _PS_MAIL_DIR_.$lang.'/'.$name.'.'.$ext;
        
        if (! file_exists($template)) {
            
            $templateToCopy = _PS_ROOT_DIR_ . '/modules/pagseguro/mails/' . $name .'.'. $ext;
            copy($templateToCopy, $template);
            
        }
    }
    
    private function findOrderStates($lang_id)
    {
        $sql = 'SELECT DISTINCT osl.`id_lang`, osl.`name`
            FROM `' . _DB_PREFIX_ . 'order_state` os
            INNER JOIN `' .
             _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state`)
            WHERE osl.`id_lang` = '."$lang_id".' AND osl.`name` in ("Iniciado","Aguardando pagamento",
            "Em análise", "Paga","Disponível","Em disputa","Devolvida","Cancelada") AND os.`id_order_state` <> 6';
        
        return (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));
    }

    private function returnIdOrderByStatusPagSeguro($nome_status)
    {
        
        $isDeleted = version_compare(_PS_VERSION_, '1.5', '<') ? '' : 'WHERE deleted = 0';
        
        $sql = 'SELECT distinct os.`id_order_state`
            FROM `' . _DB_PREFIX_ . 'order_state` os
            INNER JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl
            ON (os.`id_order_state` = osl.`id_order_state` AND osl.`name` = \'' .
             pSQL($nome_status) . '\')' . $isDeleted;
        
        $id_order_state = (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));
        
        return $id_order_state[0]['id_order_state'];
    }

    private function checkIfOrderStatusExists($id_lang, $status_name, $list_states)
    {
        
        if (Tools::isEmpty($list_states) or empty($list_states) or ! isset($list_states)) {
            return true;
        }
        
        $save = true;
        foreach ($list_states as $state) {
            
            if ($state['id_lang'] == $id_lang && $state['name'] == $status_name) {
                $save = false;
                break;
            }
        }

        return $save;
    }

    public function getNotificationUrl()
    {
        return $this->modulo->getNotificationUrl();
    }

    public function getDefaultRedirectionUrl()
    {
        return $this->modulo->getDefaultRedirectionUrl();
    }

    public function getJsBehavior()
    {
        return $this->modulo->getJsBehaviors();
    }

    public function getCssDisplay()
    {
        return $this->modulo->getCssDisplay();
    }

    /***
     * Verify if PagSeguro log file exists.
     * Case log file not exists, try create
     * else create PagSeguro.log into PagseguroLibrary folder into module
     */
    private function verifyLogFile($file)
    {
        try {
            $f = @fopen(_PS_ROOT_DIR_ . $file, 'a');
            fclose($f);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    
    private function _whichVersion()
    {
    	if(version_compare(_PS_VERSION_, '1.6.0.1', ">=")){
    		$version = '6';
    	} else if(version_compare(_PS_VERSION_, '1.5.0.1', "<")){
    		$version = '4';
    	} else {
    		$version = '5';
    	}
    	return $version;
    }
}
