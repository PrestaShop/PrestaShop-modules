<?php

require_once(_PS_MODULE_DIR_."/buyster/classes/BuysterWebService.php");
require_once(_PS_MODULE_DIR_."/buyster/classes/BuysterOperation.php");

if (!defined('_PS_VERSION_'))
	exit;

class Buyster extends PaymentModule
{
	private $_html = '';
	private $_postErrors = array();
	private $_moduleName = 'buyster';
	
	const LEFT_COLUMN = 0;
	const RIGHT_COLUMN = 1;
	const DISABLE = -1;
	
	public function __construct()
	{
		$this->name = 'buyster';
		$this->tab = 'payments_gateways';
		$this->version = '1.4.1';
		$this->author = 'PrestaShop';
		$this->limited_countries = array('fr');

		parent::__construct();
		
		$this->displayName = $this->l('Buyster');
		$this->description = $this->l('The new online payment solution approved by the Bank of France, which combines simplicity and security through mobile.');

		if (self::isInstalled($this->name))
		{	
			$warning = array();
			$this->loadingConfigurationVariablesNeeded();

			foreach ($this->_fieldsList as $keyConfiguration => $name)
				if (!Configuration::get($keyConfiguration) && !empty($name))
					$warning[] = '\''.$name.'\' ';
			
			if (count($warning) > 1)
				$this->warning .= implode(' , ',$warning).$this->l('must be configured to use this module correctly.').' ';
            if (count($warning) == 1)
				$this->warning .= implode(' , ',$warning).$this->l('has to be configured to use this module correctly.').' ';
		}
		
		$updateConfig = array('PS_OS_CHEQUE' => 1, 'PS_OS_PAYMENT' => 2, 'PS_OS_PREPARATION' => 3, 'PS_OS_SHIPPING' => 4, 'PS_OS_DELIVERED' => 5, 'PS_OS_CANCELED' => 6,
				      'PS_OS_REFUND' => 7, 'PS_OS_ERROR' => 8, 'PS_OS_OUTOFSTOCK' => 9, 'PS_OS_BANKWIRE' => 10, 'PS_OS_PAYPAL' => 11, 'PS_OS_WS_PAYMENT' => 12);
		foreach ($updateConfig as $u => $v)
			if (!Configuration::get($u) || (int)Configuration::get($u) < 1)
			{
				if (defined('_'.$u.'_') && (int)constant('_'.$u.'_') > 0)
					Configuration::updateValue($u, constant('_'.$u.'_'));
				else
					Configuration::updateValue($u, $v);
			}
	}
	
	public function install()
	{
		include(dirname(__FILE__).'/sql-install.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->Execute($s))
				return false;
		if (!(parent::install() AND $this->registerHook('payment') AND $this->registerHook('paymentReturn') AND $this->registerHook('adminOrder')))
			return false;
		Configuration::updateValue('BUYSTER_PAYMENT_RETURN_URL', 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'history.php');
		Configuration::updateValue('BUYSTER_PAYMENT_PRODUCTION', '0');
		Configuration::updateValue('BUYSTER_PAYMENT_TRANSACTION_TYPE', 'payment');
		Configuration::updateValue('BUYSTER_PAYMENT_VALIDATION', __PS_BASE_URI__.'order-confirmation.php');
		
		if (!Configuration::get('BUYSTER_PAYMENT_STATE'))
			Configuration::updateValue('BUYSTER_PAYMENT_STATE', $this->addState('En attente du paiement par Buyster', 'Waiting payment from Buyster'));
		if (!Configuration::get('BUYSTER_PAYMENT_STATE_VALIDATION'))
			Configuration::updateValue('BUYSTER_PAYMENT_STATE_VALIDATION', $this->addState('En attente de validation par '.Configuration::get('PS_SHOP_NAME'), 'Waiting for validation from '.Configuration::get('PS_SHOP_NAME')));
		Configuration::updateValue('BUYSTER_PAYMENT_TOKEN', md5(rand()));
		return true;
	}
	
	private function addState($fr, $en)
	{
		$orderState = new OrderState();
		$orderState->name = array();
		foreach (Language::getLanguages() AS $language)
		{
			if (strtolower($language['iso_code']) == 'fr')
				$orderState->name[$language['id_lang']] = $fr;
			else
				$orderState->name[$language['id_lang']] = $en;
		}
		$orderState->send_email = false;
		$orderState->color = '#DDEEFF';
		$orderState->hidden = false;
		$orderState->delivery = false;
		$orderState->logable = true;
		if ($orderState->add())
			copy(dirname(__FILE__).'/logo_mini.png', dirname(__FILE__).'/../../img/os/'.(int)$orderState->id.'.gif');
		return $orderState->id;
	}
	
	public function uninstall()
	{
		foreach ($this->_fieldsList as $keyConfiguration => $name)
		{
			if ($keyConfiguration != 'BUYSTER_PAYMENT_STATE' && $keyConfiguration != 'BUYSTER_PAYMENT_STATE_VALIDATION')
				if (!Configuration::deleteByName($keyConfiguration))
					return false;	
		}
		include(dirname(__FILE__).'/sql-uninstall.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->Execute($s))
				return false;
		if (!parent::uninstall())
			return false;
		return true;
	}
	
	public function loadingConfigurationVariablesNeeded()
	{
		$this->_fieldsList = array(
			'BUYSTER_PAYMENT_ID' => $this->l('Buyster Login'),
			'BUYSTER_PAYMENT_PASSWORD' => $this->l('Buyster Password'),
			'BUYSTER_PAYMENT_SIGNATURE' => $this->l('Buyster Signature'),
			'BUYSTER_PAYMENT_RETURN_URL' => '',
			'BUYSTER_PAYMENT_PRODUCTION' => '',
			'BUYSTER_PAYMENT_TRANSACTION_TYPE' => '',
			'BUYSTER_PAYMENT_DAYS_DELAYED' => '',
			'BUYSTER_PAYMENT_VALIDATION_DAYS' => '',
			'BUYSTER_PAYMENT_TIME_PAYMENT' => '',
			'BUYSTER_PAYMENT_PERIOD_PAYMENT' => '',
			'BUYSTER_PAYMENT_INITIAL_AMOUNT' => '',
			'BUYSTER_PAYMENT_SEVERAL_PAYMENT' => '',
			'BUYSTER_PAYMENT_DELAYED_SEVERAL' => '',
			'BUYSTER_PAYMENT_STATE' => '',
			'BUYSTER_PAYMENT_STATE_VALIDATION' => '',
			'BUYSTER_PAYMENT_TOKEN' => ''
		);
	}
	
	public function getContent()
	{
		$this->_html .= '<h2><a href="http://buyster.fr/solution-de-paiement-en-ligne-securisee-par-mobile-pour-votre-e-commerce?format=Pro"><img src="'.$this->_path.'logo.png" alt="" /></a></h2>';
		if (!empty($_POST) AND Tools::isSubmit('submitSave'))
		{
			$this->_postValidation();
			if (!sizeof($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors AS $err)
					$this->_html .= '<div class="error"><img src="'._PS_IMG_.'admin/forbbiden.gif" alt="nok" />&nbsp;'.$err.'</div>';
		}
		
		if (Tools::getValue('submitLogo'))
		{
			foreach(array('leftColumn', 'rightColumn') as $hookName)
				if ($this->isRegisteredInHook($hookName))
					$this->unregisterHook(Hook::get($hookName));
			if (Tools::getValue('logo_position') == self::LEFT_COLUMN)
				$this->registerHook('leftColumn');
			else if (Tools::getValue('logo_position') == self::RIGHT_COLUMN)
				$this->registerHook('rightColumn');
		}
		
		$this->_displayForm();
		return $this->_html;
	}
	
	private function _displayForm()
	{
		global $smarty;
		
		$globalVar = array(
		'tab' => Tools::safeOutput(Tools::getValue('tab')),
		'configure' => Tools::safeOutput(Tools::getValue('configure')),
		'token' => Tools::safeOutput(Tools::getValue('token')),
		'tab_module' => Tools::safeOutput(Tools::getValue('tab_module')),
		'module_name' => Tools::safeOutput(Tools::getValue('module_name')));
		
		$smarty->assign('global', $globalVar);
		
		$this->_html .= '<fieldset>
			<legend>'.$this->l('Buyster Module Status').'</legend>';
		
		$alert = array();
		if (!Configuration::get('BUYSTER_PAYMENT_ID') || !Configuration::get('BUYSTER_PAYMENT_PASSWORD') || !Configuration::get('BUYSTER_PAYMENT_SIGNATURE'))
			$alert['account'] = 1;
		if (!count($alert))
			$this->_html .= '<img src="'._PS_IMG_.'admin/module_install.png" /><strong>'.$this->l('Buyster module is configured !').'</strong>';
		else
		{
			$this->_html .= '<img src="'._PS_IMG_.'admin/warn2.png" /><strong>'.$this->l('Buyster module is not configured yet, please make sure you have a Buyster account').'</strong>';
			//$this->_html .= '<ul>'.(isset($alert['account']) ? '<img src="'._PS_IMG_.'admin/warn2.png" />' : '<img src="'._PS_IMG_.'admin/module_install.png" />').' '.$this->l('Make sure you have a Buyster account.');
		}

			
		$this->_html .= '</fieldset><div class="clear">&nbsp;</div>';
		$this->_html .= $this->_displayFormConfig();
	}
	
	private function _displayFormConfig()
	{
		global $smarty;
		$var = array('account' => $this->_displayFormAccount(), 'parameter' => $this->_displayFormParameters(), 'info' => $this->_displayInfo(),
				'manage' => $this->_displayManage(), 
				'logo' => ( _PS_VERSION_ >= 1.4 ? $this->_displayLogo() : ''));
		$smarty->assign('varMain', $var);
		$html = $this->display( __FILE__, 'tpl/main.tpl' );
		if (isset($_GET['id_tab']))
			$html .= '<script type="text/javascript">
				  $(".menuTabButton.selected").removeClass("selected");
				  $("#menuTab'.Tools::safeOutput(Tools::getValue('id_tab')).'").addClass("selected");
				  $(".tabItem.selected").removeClass("selected");
				  $("#menuTab'.Tools::safeOutput(Tools::getValue('id_tab')).'Sheet").addClass("selected");
			</script>';
		return $html;
	}
	
	private function _displayInfo()
	{
		global $smarty;
		return $this->display( __FILE__, 'tpl/info.tpl' );
	}
	
	private function _displayFormAccount()
	{
		global $smarty;
		$var = array('login' => (Tools::safeOutput(Tools::getValue('buyster_payment_id')) ? Tools::safeOutput(Tools::getValue('buyster_payment_id')) : Tools::safeOutput(Configuration::get('BUYSTER_PAYMENT_ID'))), 'password' => (Tools::safeOutput(Tools::getValue('buyster_payment_password')) ? Tools::safeOutput(Tools::getValue('buyster_payment_password')) : Tools::safeOutput(Configuration::get('BUYSTER_PAYMENT_PASSWORD'))),
					'account' => (Tools::safeOutput(Tools::getValue('buyster_payment_signature')) ? Tools::safeOutput(Tools::getValue('buyster_payment_signature')) : Tools::safeOutput(Configuration::get('BUYSTER_PAYMENT_SIGNATURE'))));
		$smarty->assign('varAccount', $var);		
		return $this->display( __FILE__, 'tpl/accountForm.tpl' );
	}
	
	private function _displayFormParameters()
	{
		global $smarty;
		$var = array('production' => Tools::getValue('buyster_payment_production', Configuration::get('BUYSTER_PAYMENT_PRODUCTION')),
					'returnUrl' => Configuration::get('BUYSTER_PAYMENT_RETURN_URL'), 'payment' => Configuration::get('BUYSTER_PAYMENT_TRANSACTION_TYPE'), 'daysDelayed' => Configuration::get('BUYSTER_PAYMENT_DAYS_DELAYED'),
					'validationDelayed' => Configuration::get('BUYSTER_PAYMENT_VALIDATION_DAYS'), 'timePayment' => Configuration::get('BUYSTER_PAYMENT_TIME_PAYMENT'), 'periodPayment' => Configuration::get('BUYSTER_PAYMENT_PERIOD_PAYMENT'), 'initAmount' => Configuration::get('BUYSTER_PAYMENT_INITIAL_AMOUNT'),
					'severalPayment' => Configuration::get('BUYSTER_PAYMENT_SEVERAL_PAYMENT'), 'daysDelayedSeveral' => Configuration::get('BUYSTER_PAYMENT_DELAYED_SEVERAL'));
		
		$smarty->assign('varParameters', $var);
		return $this->display( __FILE__, 'tpl/parametersForm.tpl' );
	}
	
	private function _displayLogo()
	{
		global $smarty;
		
		$blockPositionList = array(
			self::DISABLE => $this->l('Disable'),
			self::LEFT_COLUMN => $this->l('Left Column'),
			self::RIGHT_COLUMN => $this->l('Right Column'));

		$currentLogoBlockPosition = ($this->isRegisteredInHook('leftColumn')) ? self::LEFT_COLUMN :
			(($this->isRegisteredInHook('rightColumn')) ? self::RIGHT_COLUMN : -1);
		
		$option = '';
		foreach($blockPositionList as $position => $translation)
		{
			$selected = ($currentLogoBlockPosition == $position) ? 'selected="selected"' : '';
			$option .= '<option value="'.$position.'" '.$selected.'>'.$translation.'</option>';
		}
		$link = new Link();		
		$admin_dir =  substr(_PS_ADMIN_DIR_, strrpos(_PS_ADMIN_DIR_,'/') + 1);
		$smarty->assign('option', $option);
		$smarty->assign('link', $link->getPageLink('index.php').'?live_edit&ad='.$admin_dir.'&liveToken='.sha1($admin_dir._COOKIE_KEY_));
		return $this->display( __FILE__, 'tpl/logo.tpl' );
	}
	
	private function _displayManage()
	{
		global $smarty;
		
		return $this->display( __FILE__, 'tpl/manage.tpl' );
	}
	
	private function _displayFormDiagnostic()
	{
		global $smarty;
		return $this->display( __FILE__, 'tpl/diagnosticForm.tpl' );
	}
	
	private function _postValidation()
	{
		if (Tools::getValue('section') == 'account')
			$this->_postValidationAccount();
		elseif (Tools::getValue('section') == 'parameters')
			$this->_postValidationParameters();
	}
	
	private function _postValidationAccount()
	{
		$id = Tools::getValue('buyster_payment_id');
		$password = Tools::getValue('buyster_payment_password');
		$signature = Tools::getValue('buyster_payment_signature');
		if (!$id || !$password || !$signature)
			$this->_postErrors[] = $this->l('All the fields are required');
	}
	
	private function _postValidationParameters()
	{
		
	}
	
	private function _postProcess()
	{
		if (Tools::getValue('section') == 'account')
			$this->_postProcessAccount();
		elseif (Tools::getValue('section') == 'parameters')
			$this->_postProcessParameters();
	}
	
	private function _postProcessAccount()
	{
		$id = Tools::getValue('buyster_payment_id');
		$password = Tools::getValue('buyster_payment_password');
		$signature = Tools::getValue('buyster_payment_signature');
		
		Configuration::updateValue('BUYSTER_PAYMENT_ID', Tools::safeOutput($id));
		Configuration::updateValue('BUYSTER_PAYMENT_PASSWORD', Tools::safeOutput($password));
		Configuration::updateValue('BUYSTER_PAYMENT_SIGNATURE', Tools::safeOutput($signature));
	}
	
	private function _postProcessParameters()
	{
		Configuration::updateValue('BUYSTER_PAYMENT_PRODUCTION', Tools::safeOutput(Tools::getValue('buyster_payment_production')));
		Configuration::updateValue('BUYSTER_PAYMENT_RETURN_URL', Tools::safeOutput(Tools::getValue('buyster_payment_return_url')));
		Configuration::updateValue('BUYSTER_PAYMENT_TRANSACTION_TYPE', Tools::safeOutput(Tools::getValue('buyster_payment_transaction_type')));
		Configuration::updateValue('BUYSTER_PAYMENT_DAYS_DELAYED', ((int)Tools::getValue('buyster_payment_days_delayed') > 6 ? 6 : (int)Tools::getValue('buyster_payment_days_delayed')));
		Configuration::updateValue('BUYSTER_PAYMENT_VALIDATION_DAYS', ((int)Tools::getValue('buyster_payment_validation_delayed') > 30 ? 30 : (int)Tools::getValue('buyster_payment_validation_delayed')));
		
		Configuration::updateValue('BUYSTER_PAYMENT_SEVERAL_PAYMENT', Tools::safeOutput(Tools::getValue('buyster_payment_several_payment')));
		Configuration::updateValue('BUYSTER_PAYMENT_DELAYED_SEVERAL', ((int)Tools::getValue('buyster_payment_delayed_several') > 6 ? 6 : (int)Tools::getValue('buyster_payment_delayed_several')));
		
		Configuration::updateValue('BUYSTER_PAYMENT_TIME_PAYMENT', ((int)Tools::getValue('buyster_payment_time_payment') > 3 ? 3 : (int)Tools::getValue('buyster_payment_time_payment')));
		Configuration::updateValue('BUYSTER_PAYMENT_PERIOD_PAYMENT', ((int)Tools::getValue('buyster_payment_period_payment') > 30 ? 30 : (int)Tools::getValue('buyster_payment_period_payment')));
		Configuration::updateValue('BUYSTER_PAYMENT_INITIAL_AMOUNT', (float)Tools::getValue('buyster_payment_initial_amount'));
	}
	
	public function verifAccount()
	{
		if (Configuration::get('BUYSTER_PAYMENT_ID') && Configuration::get('BUYSTER_PAYMENT_PASSWORD') && Configuration::get('BUYSTER_PAYMENT_SIGNATURE'))
			return true;
		return false;
	}
	
	public function hookPayment($params)
	{
		if (!$this->active)
			return ;
		global $smarty, $cart;
		$currency = new Currency($params['cart']->id_currency);
		if ($currency->iso_code != 'EUR')
			return ;
		
		if (!$this->verifAccount())
			return ;
		$cart = new Cart($params['cart']->id);
		$total = $cart->getOrderTotal();
		if ((int)$total < 1 && (int)$total > 1800)
			return;
		
		$times = Configuration::get('BUYSTER_PAYMENT_TIME_PAYMENT');
		$initAccount = Configuration::get('BUYSTER_PAYMENT_INITIAL_AMOUNT');
		
		if ($total > $initAccount)
			$paymentN = Configuration::get('BUYSTER_PAYMENT_SEVERAL_PAYMENT');
		else
			$payment = 0;
		
		$var = array('path' => $this->_path, 'this_path_ssl' => (_PS_VERSION_ >= 1.4 ? Tools::getShopDomainSsl(true, true) : '' ).__PS_BASE_URI__.'modules/'.$this->_moduleName.'/', 'paymentN' => $paymentN,
				'times' => $times, 'period' => Configuration::get('BUYSTER_PAYMENT_PERIOD_PAYMENT'), 'initAccount' => $initAccount, 'restAmount' => ($total - Configuration::get('BUYSTER_PAYMENT_INITIAL_AMOUNT')) / ((int)$times - 1));
		$smarty->assign('var', $var);
		return $this->display( __FILE__, 'tpl/payment.tpl' );
	}
	
	public function hookPaymentReturn($params)
	{
		if (!$this->active)
			return ;
		global $smarty;
		$smarty->assign(array('total_payed' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false)));
		return $this->display(__FILE__, 'tpl/payment_return.tpl');
	}
	
	public function hookAdminOrder($params)
	{	
		if (!$this->active)
			return ;
		
		$order = new Order($params['id_order']);
		if ($order->module != $this->name)
			return;
		
		global $smarty, $currentIndex;
		
		$resultWebServiceBuyster = '';
		
		if (!empty($_POST) AND !(empty($_POST['actionBuyster'])))
		{
			$action = Tools::safeOutput($_POST['actionBuyster']);
			$param = (isset($_POST['paramBuyster']) && $_POST['paramBuyster'] != '' ? Tools::safeOutput($_POST['paramBuyster']) : NULL);
			$resultWebServiceBuyster = $this->_dealerAction($action, $param, $params['id_order']);
		}
		
		$returnWebService = '';
		if (Tools::getValue('returnWebService'))
			$returnWebService = Tools::safeOutput(Tools::getValue('returnWebService'));
		$smarty->assign('returnWebService', $returnWebService);
		$smarty->assign('order_id_buyster', $params['id_order']);
		$smarty->assign('index', 'index.php?tab=AdminOrders');
		$smarty->assign('token', Tools::safeOutput(Tools::getValue('token')));
		$smarty->assign('resultWebServiceBuyster', $resultWebServiceBuyster);
		$smarty->assign('buyster_token', Configuration::get('BUYSTER_PAYMENT_TOKEN'));
		
		return $this->display(__FILE__, 'tpl/waitAdminOrder.tpl');
	}
	
	private function _displayLogoBlock($position)
	{
		$imgPath = __PS_BASE_URI__.'modules/buyster/logobuyster.png';
		return '<div style="text-align:center;"><img src="'.$imgPath.'" width=150 /></div>';
	}
	
	public function hookRightColumn($params)
	{
		return $this->_displayLogoBlock(self::RIGHT_COLUMN);
	}

	public function hookLeftColumn($params)
	{
		return $this->_displayLogoBlock(self::LEFT_COLUMN);
	}

	
	public function getContentAdminOrder($orderId)
	{
		
		$order = new Order($orderId);
		$webService = new BuysterWebService();
		$ref = BuysterOperation::getReferenceId($order->id_cart);
		$operation = BuysterOperation::getOperationId($order->id_cart);
		$result = $webService->operation("DIAGNOSTIC", $ref);
		if (!isset($result['status']))
			return;
		$statusText = array('CANCELLED' => $this->l('"Cancel" Transaction totally canceled'),
					'CAPTURED' => $this->l('"Funded" Financing transaction sent'),
					'REFUNDED' => $this->l('"Refunded" Transaction fully repaid'),
					'CREDITED' => $this->l('"Refunded" Transaction fully repaid'),
					'EXPIRED' => $this->l('"Expired" Transaction expired'),
					'REFUSED' => $this->l('"Refused" Transaction refused'),
					'FAILED' => $this->l('"Failed" Transaction failed because the customer did not made a full payment'),
					'TO_CREDIT' => $this->l('"To credit" Transaction is pending to refund the customers'),
					'TO_CAPTURE' => $this->l('"To fund" Transaction is pending financing sends'),
					'TO_VALIDATE' => $this->l('"To validate" Transaction awaiting approval of the trader'),
					'TO_REFUND' => $this->l('"To refund" Pending transaction for sending in reimbursement'));
		/*$statusText = array('CANCELLED' => '"Cancel" Transaction totally canceled',
							'CAPTURED' => '"Funded" Financing transaction sent',
							'REFUNDED' => '"Refunded" Transaction fully repaid',
							'EXPIRED' => '"Expired" Transaction expired',
							'REFUSED' => '"Refused" Transaction refused',
							'FAILED' => '"Failed" Transaction failed because the customer did not made a full payment',
							'TO_CREDIT' => '"To credit" Transaction is pending to refund the customers',
							'TO_CAPTURE' => '"To fund" Transaction is pending financing sends',
							'TO_VALIDATE' => '"To validate" Transaction awaiting approval of the trader',
							'TO_REFUND' => '"To refund" Pending transaction for sending in reimbursement');*/
					
		$cancel = false;
		$duplication = false;
		$refund = false;
		$validation = false;
		$action = '';
		
		if ($result['status'] == 'TO_CAPTURE')
		{
			$cancel = true;
			$action = 'CANCEL';
		}
		if ($result['status'] == 'TO_VALIDATE')
		{
			$validation = true;
			//$cancel = true;
		}
		if ($result['status'] == 'CAPTURED')
		{
			$refund = true;
			$action = 'REFUND';
		}
		$cart = new Cart($order->id_cart);
		if ($order->invoice_date == "0000-00-00 00:00:00")
			$date = $cart->date_upd;
		else
			$date = $order->invoice_date;
		if ($result['status'] != 'FAILED'  && (int)(strtotime($date." + 13 months") - strtotime("now")) > 0)
			$duplication = true;
		
		$content = array('status' => $result['status'], 'status_text' => $statusText[$result['status']], 'ref' => $ref, 'price' => $order->total_paid, 'newRef' => "BuysterRef".date('Ymdhis').$order->id_cart,
				'cancel' => $cancel, 'validation' => $validation, 'refund' => $refund, 'duplication' => $duplication, 'operation' => $operation, 'action' => $action);
		return $content;
	}
	
	private function _dealerAction($action, $param, $orderId)
	{
		$order = new Order($orderId);
		$webService = new BuysterWebService();
		$reference = BuysterOperation::getReferenceId($order->id_cart);
		$price = $order->total_paid;
		
		if ($action == "DUPLICATE")
		{
			$parametre = 'fromTransactionReference='.$reference.';';
			$result = $webService->operation($action, $param, $price, $parametre);
		}
		else if ($action == "VALIDATE")
		{
		$parametre = 'operationCaptureNewDelay='.$param.';';
		$result = $webService->operation($action, $reference, $price, $parametre);
		}
		else
		{
			$parametre = NULL;
			$result = $webService->operation($action, $reference, $price, $parametre);
		}

		if ($result['responseCode'] == "00")
		{
			$history = new OrderHistory();
			$history->id_order = (int)$orderId;
			
			if ($action == "DUPLICATE")
			{
				$operation = BuysterOperation::getOperationId($order->id_cart);
				if ($operation == 'paymentValidation')
					$history->changeIdOrderState((int)Configuration::get('BUYSTER_PAYMENT_STATE_VALIDATION'), (int)$orderId);
				else
					$history->changeIdOrderState((int)Configuration::get('BUYSTER_PAYMENT_STATE'), (int)$orderId);
				BuysterOperation::setReferenceReference($param, $reference);
				$reference = $param;
			}
			if ($action == "VALIDATE")
				$history->changeIdOrderState((int)Configuration::get('PS_OS_PAYMENT'), (int)$orderId);
			if ($action == "REFUND")
				$history->changeIdOrderState((int)Configuration::get('PS_OS_REFUND'), (int)$orderId);
			if ($action == "CANCEL")
				$history->changeIdOrderState((int)Configuration::get('PS_OS_CANCELED'), (int)$orderId);
			$history->addWithemail();
		}

		$return = '';
		if ($result['responseCode'] == "99")
			$return = '<span style="color:red">Probl&egrave;me technique au niveau du serveur Buyster</span><br/>';
		if ($result['responseCode'] == "00")
		{
			$return .= '<span style="color:green">L\'&eacute;tat de votre commande a &eacute;t&eacute; modifi&eacute;.</span><br/>';
		}
		else if ($result['responseCode'] == "24")
			$return = '<span style="color:red">Op&eacuteration impossible. L\'op&eacuteration que vous souhaitez r&eacute;aliser n\'est pas compatible avec l\'&eacute;tat de la transaction.</span><br/>';
		else
			$return .= $result['responseDescription'].'<br/>';
		
		return $return;
	}
}

?>