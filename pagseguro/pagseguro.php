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
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include_once(dirname(__FILE__).'/PagSeguroLibrary/PagSeguroLibrary.php');

if (!defined('_PS_VERSION_'))
	exit;

class PagSeguro extends PaymentModule
{
	protected $errors = array();
	private $_html;
	private $_charset_options = array('1' => 'ISO-8859-1', '2' =>'UTF-8');
	private $_active_log = array('0' => 'NÃO', '1' => 'SIM');
	private $order_status = array(
		'INITIATED' => array('br' => 'Iniciado', 'en' => 'Initiated'),
		'WAITING_PAYMENT' => array('br' => 'Aguardando pagamento', 'en' => 'Waiting payment'),
		'IN_ANALYSIS' => array('br' => 'Em análise', 'en' => 'In analysis'),
		'PAID' => array('br' => 'Paga', 'en' => 'Paid'),
		'AVAILABLE' => array('br' => 'Disponível', 'en' => 'Available'),
		'IN_DISPUTE' => array('br' => 'Em disputa', 'en' => 'In dispute'),
		'REFUNDED' => array('br' => 'Devolvida', 'en' => 'Refunded'),
		'CANCELLED' => array('br' => 'Cancelada', 'en' => 'Cancelled'));
	private $list_states = array();

	function __construct()
	{
		$this->name = 'pagseguro';
		$this->tab = 'payments_gateways';
		$this->version = '1.4';
		$this->author = 'PagSeguro Internet LTDA.';
		$this->currencies = true;
		$this->currencies_mode = 'checkbox';
		parent::__construct();
		$this->displayName = $this->l('PagSeguro');
		$this->description = $this->l('Receba pagamentos por cartão de crédito, transferência bancária e boleto.');
		$this->confirmUninstall = $this->l('Tem certeza que deseja remover este módulo ?');
	}

	/**
	* Perform instalation of PagSeguro module
	* 
	* @return boolean
	*/
	public function install()
	{		

		if (!parent::install() || !$this->registerHook('payment') || !$this->registerHook('paymentReturn') || !Configuration::updateValue('PAGSEGURO_EMAIL', '') ||
		!Configuration::updateValue('PAGSEGURO_TOKEN', '') || !Configuration::updateValue('PAGSEGURO_URL_REDIRECT', '') ||
		!Configuration::updateValue('PAGSEGURO_NOTIFICATION_URL', '') || !Configuration::updateValue('PAGSEGURO_CHARSET', PagSeguroConfig::getData('application', 'charset')) ||
		!Configuration::updateValue('PAGSEGURO_LOG_ACTIVE', PagSeguroConfig::getData('log', 'active')) ||
		!Configuration::updateValue('PAGSEGURO_LOG_FILELOCATION', PagSeguroConfig::getData('log', 'fileLocation')) ||
		!Configuration::updateValue('PS_OS_PAGSEGURO', 0) || !$this->_generatePagSeguroOrderStatus())
			return false;
		return true;
	}

	/**
	* Perform uninstalation of PagSeguro module
	* 
	* @return boolean
	*/
	public function uninstall()
	{
		if (!Configuration::deleteByName('PAGSEGURO_EMAIL') || !Configuration::deleteByName('PAGSEGURO_TOKEN') || !Configuration::deleteByName('PAGSEGURO_URL_REDIRECT') ||
		!Configuration::deleteByName('PAGSEGURO_NOTIFICATION_URL') || !Configuration::deleteByName('PAGSEGURO_CHARSET') || !Configuration::deleteByName('PAGSEGURO_LOG_ACTIVE') ||
		!Configuration::deleteByName('PAGSEGURO_LOG_FILELOCATION') || !Configuration::deleteByName('PS_OS_PAGSEGURO') || !parent::uninstall())
			return false;
		return true;
	}

	/**
	* Gets order states saved in the bank
	* @return boolean
	*/
	private function _findOrderStates()
	{
		return (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT osl.`id_lang`, osl.`name` 
			FROM `' . _DB_PREFIX_ . 'order_state` os
			INNER JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state`)
			WHERE os.`module_name` = \'pagseguro\''));
	}

	/**
	* Gets configuration view content
	* 
	* @return string
	*/
	public function getContent()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			$this->_postValidation();

			if (!count($this->errors))
			$this->_postProcess();
			else
			foreach ($this->errors as $error)
				$this->_html .= '<div class="alert error">'.$error.'</div>';
		}

		$this->_displayForm();

		return $this->_html;
	}

	/**
	* Realize post validations according with PagSeguro standards
	* case any inconsistence, an item is added to $_postErrors
	*/
	private function _postValidation()
	{
		if (Tools::isSubmit('btnSubmit')) 
		{
			$email = Tools::getValue('pagseguro_email');
			$token = Tools::getValue('pagseguro_token');
			$pagseguro_url_redirect = Tools::getValue('pagseguro_url_redirect');
			$pagseguro_notification_url = Tools::getValue('pagseguro_notification_url');
			$charset = Tools::getValue('pagseguro_charset');
			$pagseguro_log = Tools::getValue('pagseguro_log');

			/* E-mail validation */
			if (!$email)
				$this->errors[] = $this->_errorMessage('E-MAIL');
			elseif (strlen($email)> 60)
				$this->errors[] = $this->_invalidFieldSizeMessage('E-MAIL');
			elseif (!Validate::isEmail($email))
				$this->errors[] = $this->_invalidMailMessage('E-MAIL');

			/* Token validation */
			if (!$token)
				$this->errors[] = $this->_errorMessage('TOKEN');
			elseif (strlen($token)!= 32)
				$this->errors[] = $this->_invalidFieldSizeMessage('TOKEN');

			/* URL redirect validation */
			if ($pagseguro_url_redirect && !filter_var($pagseguro_url_redirect, FILTER_VALIDATE_URL))
				$this->errors[] = $this->_invalidUrl('URL DE REDIRECIONAMENTO');

			/* Notification url validation */
			if ($pagseguro_notification_url && !filter_var($pagseguro_notification_url, FILTER_VALIDATE_URL))
				$this->errors[] = $this->_invalidUrl('URL DE NOTIFICAÇÃO');

			/* Charset validation */
			if (!array_key_exists($charset, $this->_charset_options))
				$this->errors[] = $this->_invalidValue('CHARSET');

			/* Log validation */
			if (!array_key_exists($pagseguro_log, $this->_active_log))
				$this->errors[] = $this->_invalidValue('LOG');
		}
	}

	/**
	* Realize PagSeguro database keys values
	*/
	private function _postProcess()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			Configuration::updateValue('PAGSEGURO_EMAIL', Tools::getValue('pagseguro_email'));
			Configuration::updateValue('PAGSEGURO_TOKEN', Tools::getValue('pagseguro_token'));
			Configuration::updateValue('PAGSEGURO_URL_REDIRECT', Tools::getValue('pagseguro_url_redirect'));
			Configuration::updateValue('PAGSEGURO_NOTIFICATION_URL', Tools::getValue('pagseguro_notification_url'));
			Configuration::updateValue('PAGSEGURO_CHARSET', $this->_charset_options[Tools::getValue('pagseguro_charset')]);
			Configuration::updateValue('PAGSEGURO_LOG_ACTIVE', Tools::getValue('pagseguro_log'));
			Configuration::updateValue('PAGSEGURO_LOG_FILELOCATION', Tools::getValue('pagseguro_log_dir'));

			/* Verify if log file exists, case not try create */
			if (Tools::getValue('pagseguro_log'))
				$this->_verifyLogFile(Tools::getValue('pagseguro_log_dir'));
		}
		$this->_html .= '<div class="conf confirm">'.$this->l('Dados atualizados com sucesso').'</div>';
	}
	
	/**
	* Create error messages
	* 
	* @param String $field
	* @return String
	*/
	private function _errorMessage($field)
	{
		return sprintf($this->l('O campo <strong>%s</strong> deve ser informado.'), $field);
	}

	/**
	* Create invalid mail messages
	* 
	* @param String $field
	* @return String
	*/
	private function _invalidMailMessage($field)
	{
		return sprintf($this->l('O campo <strong>%s</strong> deve ser conter um email válido.'), $field);
	}
	
	/**
	* Create invalid field size messages
	* 
	* @param String $field
	* @return String
	*/
	private function _invalidFieldSizeMessage($field)
	{
		return sprintf($this->l('O campo <strong>%s</strong> está com um tamanho inválido'), $field);
	}

	/**
	* Create invalid value messages
	* 
	* @param String $field
	* @return String
	*/
	private function _invalidValue($field)
	{
		return sprintf($this->l('O campo <strong>%s</strong> contém um valor inválido.'), $field);
	}

	/**
	* Check if has the $_POST['activeslide']
	*/
	private function _checkActiveSlide()
	{
		return Tools::getValue('activeslide') ? Tools::getValue('activeslide') : 1;
	}

	/**
	* Create invalid url messages
	* 
	* @param String $field
	* @return String
	*/
	private function _invalidUrl($field)
	{
		return sprintf($this->l('O campo <strong>%s</strong> deve conter uma url válida.'), $field);
	}
	
	/**
	*  Display configuration form
	*/
	private function _displayForm()
	{
		$this->context->controller->addCSS($this->_path.'assets/css/styles.css');
		$this->context->controller->addJS($this->_path.'assets/js/behaviors.js');

		$this->_html .=
		'<form class="psplugin" id="psplugin" action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" method="POST">
			<h1>
				<img src="'.$this->_path.'assets/images/logops_228x56.png" />
				<span>'.$this->l('Mais de 23 milhões de brasileiros já utilizam o PagSeguro.').'<br />'.$this->l('Faça parte você também!').'</span>
			</h1>
			<div id="mainps">
				<ol>
					<li class="ps-slide1">
						<h2><span>'.$this->l('Como funciona').'</span></h2>
						<div>
							<h2>'.$this->l('Sem convênios. Sem taxa mínima, adesão ou mensalidade.').'</h2>
							<br />
							<p>'.$this->l('PagSeguro é a solução completa para pagamentos online, que garante a segurança de quem compra e de quem vende na web. Quem compra com PagSeguro tem a garantia de produto ou serviço entregue ou seu dinheiro de volta. Quem vende utilizando o serviço do PagSeguro tem o gerenciamento de risco de suas transações*. Quem integra lojas ao PagSeguro tem ferramentas, comissão e publicidade gratuita.').'</p>

							<p>'.$this->l('Não é necessário fazer convênios com operadoras. O PagSeguro é a única empresa no Brasil a oferecer todas as opções em um só pacote. O PagSeguro não cobra nenhuma taxa para você abrir sua conta, não cobra taxas mensais, não cobra multa caso você queira parar de usar os serviços.').'</p>

							<p>'.$this->l('Use PagSeguro para receber pagamentos de modo fácil e seguro. Comece a aceitar em alguns minutos, pagamentos por cartões de crédito, boletos e transferências bancárias online e alcance milhares de compradores. Mesmo que você já ofereça outros meios de pagamento, adicione o PagSeguro e ofereça a opção Carteira Eletrônica PagSeguro. Milhões de usuários já usam o Saldo PagSeguro para compras online, e compram com segurança, rapidez e comodidade.').'</p>


							<p class="small">* '.$this->l('Gerenciamento de risco de acordo com nossas').' <a href=\'https://pagseguro.uol.com.br/regras-de-uso.jhtml\' target=\'_blank\'>'.$this->l('Regras de uso').'</a>.</p>
						</div>
					</li>
					<li class="ps-slide2">
						<h2><span>'.$this->l('Crie sua conta').'</span></h2>
						<div>
							<h2>'.$this->l('A forma mais fácil de vender').'</h2>
							<br />
							<ul>
								<li>'.$this->l('Comece hoje a vender pela internet').'</li>
								<li>'.$this->l('Venda pela internet sem pagar mensalidade').'</li>
								<li>'.$this->l('Ofereça parcelamento com ou sem acréscimo').'</li>
								<li>'.$this->l('Venda parcelado e receba de uma única vez').'</li>
								<li>'.$this->l('Proteção total contra fraudes').'</li>
							</ul>
							<br />
							<a href="https://pagseguro.uol.com.br/registration/registration.jhtml?ep=5&tipo=cadastro#!vendedor" target="_blank" class="pagseguro-button green-theme normal">'.$this->l('Faça seu cadastro').'</a>
						</div>
					</li>
					<li class="ps-slide3">
						<h2><span>'.$this->l('Configurações').'</span></h2>
						<div>
							<label>'.$this->l('E-MAIL').'*</label><br />
							<input type="text" name="pagseguro_email" id="pagseguro_email" value="'.Tools::safeOutput(Configuration::get('PAGSEGURO_EMAIL')).'" maxlength="60"  hint="'.$this->l('Para oferecer o PagSeguro em sua loja é preciso ter uma conta do tipo vendedor ou empresarial. Se você ainda não tem uma conta PagSeguro').' <a href=\'https://pagseguro.uol.com.br/registration/registration.jhtml?ep=5&tipo=cadastro#!vendedor\' target=\'_blank\'>'.$this->l('clique aqui').'</a>'.$this->l(', caso contrário informe neste campo o e-mail associado à sua conta PagSeguro.').'" />
							<br/>
							<label>'.$this->l('TOKEN').'*</label><br />
							<input type="text" name="pagseguro_token" id="pagseguro_token" value="'.Tools::safeOutput(Configuration::get('PAGSEGURO_TOKEN')).'" maxlength="32"  hint="'.$this->l('Para utilizar qualquer serviço de integração do PagSeguro, é necessário ter um token de segurança. O token é um código único, gerado pelo PagSeguro. Caso não tenha um token,').' <a href=\'https://pagseguro.uol.com.br/integracao/token-de-seguranca.jhtml\' target=\'_blank\'>'.$this->l('clique aqui').'</a> '.$this->l('para gerar.').'" />
							<br />
							<label>'.$this->l('URL DE REDIRECIONAMENTO').'</label><br />
							<input type="text" name="pagseguro_url_redirect" id="pagseguro_url_redirect" value="'.Tools::safeOutput(Configuration::get('PAGSEGURO_URL_REDIRECT')).'" maxlength="255" hint="'.$this->l('Ao final do fluxo de pagamento no PagSeguro, seu cliente será redirecionado de volta para sua loja ou para a URL que você informar neste campo. Para utilizar essa funcionalidade você deve configurar sua conta para aceitar somente requisições de pagamentos gerados via API.').' <a href=\'https://pagseguro.uol.com.br/integracao/pagamentos-via-api.jhtml\' target=\'_blank\'>'.$this->l('Clique aqui</a> para ativar este serviço.').'" />
							<br />
							<label>'.$this->l('URL DE NOTIFICAÇÃO').'</label><br />
							<input type="text" name="pagseguro_notification_url" id="pagseguro_notification_url" value="'.Tools::safeOutput(Configuration::get('PAGSEGURO_NOTIFICATION_URL')).'" maxlength="255" hint="'.$this->l('Sempre que uma transação mudar de status, o PagSeguro envia uma notificação para sua loja ou para a URL que você informar neste campo.').'" />
							<br />
							<br />
							<p class="small">* '.$this->l('Campos obrigatórios').'</p>

							<div class="hintps _config"></div>
						</div>
					</li>
					<li class="ps-slide4">
						<h2><span>'.$this->l('Extras').'</span></h2>
						<div>
							<label>'.$this->l('CHARSET').'</label><br />
								'.$this->_generateSelectTag('pagseguro_charset', $this->_charset_options, array_search(Configuration::get('PAGSEGURO_CHARSET'), $this->_charset_options), 'class="select" hint="'.$this->l('Informe a codificação utilizada pelo seu sistema. Isso irá prevenir que as transações gerem possíveis erros ou quebras ou ainda que caracteres especiais possam ser apresentados de maneira diferente do habitual.').'"').'
							<br />
							<label>'.$this->l('LOG').'</label><br />
								'.$this->_generateSelectTag('pagseguro_log', $this->_active_log, Configuration::get('PAGSEGURO_LOG_ACTIVE'), 'class="select" hint="Deseja habilitar a geração de log?"').'
							<br />
							<span id="directory-log">
								<label>'.$this->l('DIRETÓRIO').'</label><br />
								<input type="text" id="pagseguro_log_dir" name="pagseguro_log_dir" value="'.Tools::safeOutput(Configuration::get('PAGSEGURO_LOG_FILELOCATION')).'" hint="'.$this->l('Diretório a partir da raíz de instalação do PrestaShop onde se deseja criar o arquivo de log. Ex.: /logs/log_ps.log').'" />
							</span>

							<div class="hintps _extras"></div>
						</div>
					</li>
				</ol>
				<noscript>
					<p>'.$this->l('Please enable JavaScript to get the full experience.').'</p>
				</noscript>
			</div>
			<br />
			
			<input type="hidden" name="activeslide" id="activeslide" value="'.Tools::safeOutput($this->_checkActiveSlide()).'" />

			<button id="update" class="pagseguro-button green-theme normal" name="btnSubmit">'.$this->l('Atualizar').'</button>
		</form>
		<script>
			$(\'#mainps\').liteAccordion({
				theme : \'ps\',
				rounded : true,
				firstSlide : parseInt($(\'#activeslide\').val()),
				containerHeight : 400,
				onTriggerSlide : function() {
					$(\'.hintps\').fadeOut(400);
					
				}
			});

			$(\'li[class*=ps-slide] h2\').on(
				\'click\',
				function(e) {
					var active = /ps-slide(\d)/;
					$(\'#activeslide\').val( active.exec($(this).parent().attr(\'class\'))[1] );
				}
			);

			$(\'#pagseguro_log\').on(
				\'change\',
				function(e) {
					$(\'#directory-log\').toggle(300);
				}
			);

			$(\'input, select\').on(
				\'focus\',
				function(e) {
					_$this = $(this);

					$(this).addClass(\'focus\');
					$(this).parent().parent().find(\'.hintps\').fadeOut(210, function() {
						$(this).html(_$this.attr(\'hint\')).fadeIn(210);
					});
				}
			);

			$(\'input, select\').on(
				\'blur\',
				function(e) {
					$(this).removeClass(\'focus\');
				}
			);

			$(\'#psplugin\').on(
				\'submit\',
				function(e) {
					//$(\'#mainps ol li:nth-child(3) h2\').trigger(\'click\');
				}
			);
			
			if ($(\'select#pagseguro_log\').val() == \'0\'){
				$(\'#directory-log\').hide();
			}

			$(\'.alert, .conf\').insertBefore(\'#mainps\');
			
			$(\'.alert, .conf\').on(
				\'click\',
				function() {
					$(this).fadeOut(450);
				}
			);

			setTimeout(function() {
				$(\'.conf\').fadeOut(450);
			}, 3000);
		</script>';
	}

	/**
	*  Perform Payment hook
	* 
	* @param array $params
	* @return string
	*/
	public function hookPayment($params)
	{
		if (!$this->active || !$this->checkCurrency($params['cart']))
			return;

		$this->smarty->assign(array(
			'image' => $this->getPathUri().'assets/images/logops_86x49.png',
			'this_path' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/'));

		return $this->display(__FILE__, 'payment.tpl');
	}
	
	/**
	*  Perform Payment Return hook
	* 
	* @param array $params
	* @return string
	*/
	public function hookPaymentReturn($params)
	{
		if (!$this->active)
			return;

		if (!Tools::isEmpty($params['objOrder']) && $params['objOrder']->module === $this->name)
		{
			$this->smarty->assign(array(
				'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
				'status' => 'ok',
				'id_order' => (int)$params['objOrder']->id));

			if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference))
				$this->smarty->assign('reference', $params['objOrder']->reference);
		}
		else
			$this->smarty->assign('status', 'failed');

		return $this->display(__FILE__, 'payment_return.tpl');
	}
	
	/**
	* Check the currency
	* 
	* @param Cart $cart
	* @return boolean
	*/
	public function checkCurrency($cart)
	{
		$currency_order = new Currency((int)$cart->id_currency);
		return PagSeguroCurrencies::checkCurrencyAvailabilityByIsoCode($currency_order->iso_code);
	}

	/**
	*  Generate select tag
	* 
	* @param string $id
	* @param array $options
	* @param string $selected
	* @param string $extra
	* @return string
	*/
	private function _generateSelectTag($id, Array $options, $selected = '', $extra = '')
	{		
		$select = '<select id="'.Tools::safeOutput($id).'" name="'.Tools::safeOutput($id).'" '.$extra.' >';
		foreach ($options as $key => $value)
		{
			$selected_attr = ($selected == $key) ? 'selected="selected" ' : '';
			$select .= '<option value="'.Tools::safeOutput($key).'" '.$selected_attr.'>'.Tools::safeOutput($value).'</option>';
		}
		return $select.'</select>';
	}

	/**
	* Verify if PagSeguro log file exists.
	* Case log file not exists, try create
	* else create PagSeguro.log into PagseguroLibrary folder into module
	*/
	private function _verifyLogFile($file)
	{
		try
		{
			$f = @fopen(_PS_ROOT_DIR_.$file, 'a');
			fclose($f);
		}
		catch (Exception $e)
		{
			die($e->getMessage());
		}
	}
	
	/**
	* Create PagSeguro order status into database
	* 
	* @return bool $ordersAdded
	*/
	private function _generatePagSeguroOrderStatus()
	{
		$orders_added = true;
		$name_state = null;
				$this->list_states = $this->_findOrderStates();
				
		foreach (array_keys($this->order_status) as $status)
		{
			$order_state = new OrderState();
			$order_state->module_name = $this->name;
			$order_state->send_email = false;
			$order_state->color = '#95D061';
			$order_state->hidden = false;
			$order_state->delivery = false;
			$order_state->logable = true;
			$order_state->invoice = true;
			$order_state->name = array();
						$continue = false;
			foreach (Language::getLanguages() as $language){
							$continue = $this->_checkIfOrderStatusExists($language['id_lang'], $this->getStatusTranslation($status, strtolower($language['iso_code'])));
							
								if($continue)
								   $order_state->name[$language['id_lang']] = $this->getStatusTranslation($status, strtolower($language['iso_code']));
						}
				
					   	if($continue){
						   $orders_added &= $order_state->add();
						   copy(dirname(__FILE__).'/logo.gif', dirname(__FILE__).'/../../img/os/'.(int)$order_state->id.'.gif');
					  	}
					  
					  	/* getting initial state id to update PS_OS_PAGSEGURO config */
			if ($status == 'WAITING_PAYMENT')
				$name_state = $this->getStatusTranslation($status, strtolower($language['iso_code']));
			  	}
			
		if ($name_state != null)
			Configuration::updateValue('PS_OS_PAGSEGURO', $this->_returnIdOrderByStatusPagSeguro($name_state));

		return $orders_added;
	}
		
	 /**
	 * Check if PagSeguro order status already exists on database
	 * @param String $status
	 * @return boolean
	 */
	 private function _checkIfOrderStatusExists($id_lang, $status_name){
		 if(Tools::isEmpty($this->list_states)){
			 return true;
		 }
			$save = true;
			foreach ($this->list_states as $state) {
			   if($state['id_lang'] == $id_lang && $state['name'] == $status_name)
				 $save = false;
			}
	   return $save;
	}
	
		/**
		 * Return current translation for infomed status and language iso code
		 * @param string $status
		 * @param string $lang_iso_code
		 * @return string
		 */
		private function getStatusTranslation($status, $lang_iso_code = 'br')
		{
			if (isset($this->order_status[$status][$lang_iso_code]))
				return $this->order_status[$status][$lang_iso_code];

			/* Default return in English */
			return $this->order_status[$status]['en'];
		}
		
	/**
	* Gets notification url
	* @return string
	*/
	public function getNotificationUrl()
	{
		return (!PagSeguroHelper::isEmpty(Configuration::get('PAGSEGURO_NOTIFICATION_URL'))) ? Configuration::get('PAGSEGURO_NOTIFICATION_URL')  : $this->_notificationURL();
	}
	
	/**
	* 
	* Notification Url
	* @return type
	*/
	private function _notificationURL()
	{
		return _PS_BASE_URL_.__PS_BASE_URI__.'index.php?fc=module&module=pagseguro&controller=notification';
	}
		
		   /**
	* Return Id Oder by Status PagSeguro
	* @param type $value
	* @return type
	*/
	private function _returnIdOrderByStatusPagSeguro($value)
	{
		$id_order_state = (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT distinct os.`id_order_state`
		FROM `' . _DB_PREFIX_ . 'order_state` os
		INNER JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`name` = \'' .pSQL($value). '\' AND os.`module_name` = \'pagseguro\')
		WHERE deleted = 0'));

		return $id_order_state[0]['id_order_state'];
	}
}
