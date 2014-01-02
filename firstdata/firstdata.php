<?php
/*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @version  Release: $Revision: 1.2 $
*
*  International Registered Trademark & Property of PrestaShop SA
*/

/* Security */
if (!defined('_PS_VERSION_'))
	exit;

class Firstdata extends PaymentModule
{
	private $_postErrors = array();

	public function __construct()
	{
		$this->name = 'firstdata';
		$this->tab = 'payments_gateways';
		$this->version = '1.2.2';

		parent::__construct();

		$this->displayName = $this->l('First Data');
		$this->description = $this->l('Accept Credit card payments today with First Data (Visa, Mastercard, Amex, Discover, etc.)');
		
		/* Backward compatibility */
		if (_PS_VERSION_ < 1.5)
			require(_PS_MODULE_DIR_.'firstdata/backward_compatibility/backward.php');
	}

	public function install()
	{
		$this->registerHook('displayMobileHeader');
		
		return Db::getInstance()->Execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'firstdata` (
			`id_cart` int(10) NOT NULL,
			`authorization_num` varchar(11) DEFAULT NULL,
			`transaction_tag` int(11) DEFAULT NULL,
			`id_shop` int(10) DEFAULT NULL,
			`date_add` datetime NOT NULL,
			`date_cancel` datetime DEFAULT NULL,
			`date_refund` datetime DEFAULT NULL,
			PRIMARY KEY  (`id_cart`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;') && parent::install() && $this->registerHook('payment') && $this->registerHook('adminOrder') && $this->registerHook('orderConfirmation');
	}

	public function uninstall()
	{
		Configuration::deleteByName('FIRSTDATA_KEY_ID');
		Configuration::deleteByName('FIRSTDATA_KEY_HMAC');
		Configuration::deleteByName('FIRSTDATA_GATEWAY_ID');
		Configuration::deleteByName('FIRSTDATA_PASSWORD');

		return parent::uninstall();
	}

	public function getContent()
	{
		$html = '';
		if (Tools::isSubmit('submitFirstData') && isset($_POST['firstdata_key_id']) && isset($_POST['firstdata_key_hmac']) && isset($_POST['firstdata_gateway_id']) && isset($_POST['firstdata_password']) &&
			!empty($_POST['firstdata_key_id']) && !empty($_POST['firstdata_key_hmac']) && !empty($_POST['firstdata_gateway_id']) && !empty($_POST['firstdata_password']))
		{
			Configuration::updateValue('FIRSTDATA_KEY_ID', pSQL(Tools::getValue('firstdata_key_id')));
			Configuration::updateValue('FIRSTDATA_KEY_HMAC', pSQL(Tools::getValue('firstdata_key_hmac')));
			Configuration::updateValue('FIRSTDATA_GATEWAY_ID', pSQL(Tools::getValue('firstdata_gateway_id')));
			Configuration::updateValue('FIRSTDATA_PASSWORD', pSQL(Tools::getValue('firstdata_password')));
			$html = '<div class="conf confirm">'.$this->l('Configuration updated successfully').'</div>';
		}
		else if (Tools::isSubmit('submitFirstData'))
			$html = '<div class="error">'.$this->l('Please fill the required fields').'</div>';

		$this->context->smarty->assign(array(
		'firstdata_form' => './index.php?tab=AdminModules&configure=firstdata&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.$this->tab.'&module_name=firstdata',
		'firstdata_tracking' => 'http://www.prestashop.com/modules/firstdata.png?url_site='.Tools::safeOutput($_SERVER['SERVER_NAME']).'&amp;id_lang='.(int)$this->context->cookie->id_lang,
		'firstdata_key_id' => Configuration::get('FIRSTDATA_KEY_ID'),
		'firstdata_key_hmac' => Configuration::get('FIRSTDATA_KEY_HMAC'),
		'firstdata_gateway_id' => Configuration::get('FIRSTDATA_GATEWAY_ID'),
		'firstdata_password' => Configuration::get('FIRSTDATA_PASSWORD'),
		'firstdata_ssl' => Configuration::get('PS_SSL_ENABLED'),
		'firstdata_confirmation' => $html));

		return $this->display(__FILE__, 'tpl/admin.tpl');
	}

	public function hookDisplayMobileHeader()
	{
		return $this->hookHeader();
	}

	public function hookAdminOrder($params)
	{
		if (!$this->active)
			return false;
		$order = new Order((int)$params['id_order']);
		if ($order->module != $this->name)
			return false;
		if (!Validate::isLoadedObject($order))
			return false;

		/* Refund or cancel a transaction */
		if (Tools::isSubmit('firstDataCancel'))
		{
			$transaction_type = 33;
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'firstdata` SET date_cancel = NOW() WHERE `id_cart` = '.(int)$order->id_cart);
		}
		elseif (Tools::isSubmit('firstDataRefund'))
		{
			$transaction_type = 34;
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'firstdata` SET date_refund = NOW() WHERE `id_cart` = '.(int)$order->id_cart);
		}
		
		$transaction = $this->_getTransaction((int)$order->id_cart);
		
		if (isset($transaction_type))
		{
			$result = $this->_firstDataCall('{"gateway_id": "'.Configuration::get('FIRSTDATA_GATEWAY_ID').'", "password": "'.Configuration::get('FIRSTDATA_PASSWORD').'", "transaction_type": "'.$transaction_type.'", "amount": "'.(float)$order->getTotalPaid().'", "transaction_tag": "'.(int)$transaction['transaction_tag'].'", "authorization_num": "'.Tools::safeOutput($transaction['authorization_num']).'"}');
			$json_result = Tools::jsondecode($result);
			
			if (isset($json_result->transaction_approved) && $json_result->transaction_approved)
				$this->context->smarty->assign('firstdata_message', $transaction_type == 33 ? $this->l('Order successfully canceled.') : $this->l('Order successfully refunded.'));
			else
			{
				if (isset($json_result->transaction_approved) && !$json_result->transaction_approved && isset($json_result->bank_message) && $json_result->bank_message != '')
					$this->context->smarty->assign('firstdata_error', $json_result->bank_message);
				else
					$this->context->smarty->assign('firstdata_error', trim(substr($result, strpos($result, '-') + 1)));
			}
		}

		/* Retrieve transaction details */
		$transaction_details = Tools::jsondecode($this->_firstDataCall('{"gateway_id": "'.Configuration::get('FIRSTDATA_GATEWAY_ID').'", "password": "'.Configuration::get('FIRSTDATA_PASSWORD').'", "transaction_type": "CR", "transaction_tag": "'.(int)$transaction['transaction_tag'].'", "authorization_num": "'.Tools::safeOutput($transaction['authorization_num']).'"}'));

		$this->context->smarty->assign(array(
		'firstdata_form' => './index.php?tab=AdminOrders&id_order='.(int)$order->id.'&vieworder&token='.Tools::getAdminTokenLite('AdminOrders'),
		'firstdata_transaction_approved' => $transaction_details->transaction_approved,
		'firstdata_bank_message' => $transaction_details->bank_message,
		'firstdata_cc_number' => substr($transaction_details->cc_number, -4),
		'firstdata_credit_card_type' => $transaction_details->credit_card_type,
		'firstdata_amount' => $transaction_details->amount,
		'firstdata_cardholder_name' => $transaction_details->cardholder_name,
		'firstdata_cc_expiry' => $transaction_details->cc_expiry,
		'firstdata_transaction_tag' => $transaction_details->transaction_tag,
		'firstdata_authorization_num' => $transaction_details->authorization_num,
		'firstdata_currency_code' => $transaction_details->currency_code,
		'firstdata_date_add' => $transaction['date_add'],
		'firstdata_date_cancel' => $transaction['date_cancel'],
		'firstdata_date_refund' => $transaction['date_refund']));

		return $this->display(__FILE__, 'tpl/admin-order.tpl');
	}

	public function hookPayment($params)
	{
		$this->smarty->assign('firstdata_ps_version', _PS_VERSION_);
		
		$currency = new Currency((int)$params['cart']->id_currency);

		if (!$this->active || Configuration::get('FIRSTDATA_KEY_ID') == '' || Configuration::get('FIRSTDATA_KEY_HMAC') == '' && $currency->iso_code != 'USD')
			return false;

		return $this->display(__FILE__, 'tpl/payment.tpl');
	}
	
	/**
	 * Display a confirmation message after an order has been placed
	 *
	 * @param array Hook parameters
	 */
	public function hookOrderConfirmation($params)
	{
		if ($params['objOrder']->module != $this->name)
			return false;
		if ($params['objOrder'] && Validate::isLoadedObject($params['objOrder']) && isset($params['objOrder']->valid))
		{
			if (version_compare(_PS_VERSION_, '1.5', '>=') && isset($params['objOrder']->reference))
				$this->smarty->assign('firstdata_order', array('id' => $params['objOrder']->id, 'reference' => $params['objOrder']->reference, 'valid' => $params['objOrder']->valid));
			else
				$this->smarty->assign('firstdata_order', array('id' => $params['objOrder']->id, 'valid' => $params['objOrder']->valid));

			return $this->display(__FILE__, 'tpl/order-confirmation.tpl');
		}
	}

	private function _getTransaction($id_cart, $id_shop = null)
	{
		return Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'firstdata` WHERE `id_cart` = '.(int)$id_cart);
	}

	private function _insertTransaction($params)
	{
		return Db::getInstance()->insert('firstdata', $params);
	}

	public function validation()
	{
		$cart = $this->context->cart;
		if (Validate::isLoadedObject($cart) && !Order::getOrderByCartId((int)Tools::getValue('cart')))
		{
			$result = $this->_firstDataCall('{"gateway_id": "'.Configuration::get('FIRSTDATA_GATEWAY_ID').'", "password": "'.Configuration::get('FIRSTDATA_PASSWORD').'", "transaction_type": "00", "amount": "'.(float)$cart->getOrderTotal().'", "cc_number": "'.Tools::safeOutput(Tools::getValue('x_card_num')).'", "cc_expiry": "'.(Tools::getValue('x_exp_date_m') < 10 ? '0'.(int)Tools::getValue('x_exp_date_m') : (int)Tools::getValue('x_exp_date_m')).(int)Tools::getValue('x_exp_date_y').'", "cardholder_name": "'.Tools::safeOutput(Tools::getValue('firstdata_card_holder')).'"}');
			$json_result = Tools::jsondecode($result);
			if (isset($json_result->transaction_approved) && $json_result->transaction_approved)
			{
				$this->_insertTransaction(array('id_cart' => (int)$cart->id, 'authorization_num' => pSQL($json_result->authorization_num), 'transaction_tag' => (int)$json_result->transaction_tag, 'date_add' => date('Y-m-d H:i:s')));
				$this->validateOrder((int)$cart->id, (int)Configuration::get('PS_OS_PAYMENT'), (float)$json_result->amount, $this->displayName, pSQL($json_result->ctr), array(), null, false, $cart->secure_key);

				/** @since 1.5.0 Attach the First Data Transaction ID to this Order */
				if (version_compare(_PS_VERSION_, '1.5', '>='))
				{
					$new_order = new Order((int)$this->currentOrder);
					if (Validate::isLoadedObject($new_order))
					{
						$payment = $new_order->getOrderPaymentCollection();
						$payment[0]->transaction_id = (int)$json_result->transaction_tag;
						$payment[0]->save();
					}
				}
				
				/* Redirect the user to the order confirmation page / history */
				if (_PS_VERSION_ < 1.5)
					$redirect = __PS_BASE_URI__.'order-confirmation.php?id_cart='.(int)$this->context->cart->id.'&id_module='.(int)$this->id.'&id_order='.(int)$this->currentOrder.'&key='.$this->context->customer->secure_key;
				else
					$redirect = __PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.(int)$this->context->cart->id.'&id_module='.(int)$this->id.'&id_order='.(int)$this->currentOrder.'&key='.$this->context->customer->secure_key;

				header('Location: '.$redirect);
				exit;
			}
			else
			{
				if (isset($json_result->transaction_approved) && !$json_result->transaction_approved && isset($json_result->bank_message) && $json_result->bank_message != '')
					$error_msg = Tools::safeOutput($json_result->bank_message);
				else
					$error_msg = trim(substr($result, strpos($result, '-')));

				Logger::AddLog('[FirstData] '.Tools::safeOutput($error_msg), 2);
				$checkout_type = Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc' : 'order';
				$url = (_PS_VERSION_ >= '1.5' ? 'index.php?controller='.$checkout_type.'&' : $checkout_type.'.php?').'step=3&cgv=1&firstdataError='.$error_msg.'#firstdata-anchor';

				if (!isset($_SERVER['HTTP_REFERER']) ||	strstr($_SERVER['HTTP_REFERER'], 'order'))
					Tools::redirect($url);
				elseif (strstr($_SERVER['HTTP_REFERER'], '?'))
					Tools::redirect(Tools::safeOutput($_SERVER['HTTP_REFERER']).'&firstdataError='.$error_msg.'#firstdata-anchor', '');
				else
					Tools::redirect(Tools::safeOutput($_SERVER['HTTP_REFERER']).'?firstdataError='.$error_msg.'#firstdata-anchor', '');
			}
		}
		else
			die('Unfortunately your order could not be validated. Error: "Invalid Cart ID", please contact us.');
	}

	private function _firstDataCall($data_string)
	{
		$content_type = 'application/json; charset=UTF-8';
		$content_digest = sha1($data_string);
		$hashtime = gmdate('c');

		$hashstr = "POST\n".$content_type."\n".$content_digest."\n".$hashtime."\n/transaction/v12";
		$authstr = base64_encode(hash_hmac('sha1', $hashstr, Configuration::get('FIRSTDATA_KEY_HMAC'), true));

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://api.globalgatewaye4.firstdata.com/transaction/v12'); // Sandbox: https://api.demo.globalgatewaye4.firstdata.com/transaction/v12
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_NOPROGRESS, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: '.$content_type, 'Accept: application/json',
		'Authorization: GGE4_API '.Configuration::get('FIRSTDATA_KEY_ID').':'.$authstr, 'x-gge4-Date: '.$hashtime, 'x-GGe4-Content-SHA1: '.$content_digest));

		return curl_exec($ch);
	}
}