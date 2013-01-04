<?php
/*
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @version  Release: $Revision: 7040 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class StripeJs extends PaymentModule
{
	public $limited_countries = array('us', 'ca');
	public $limited_currencies = array('USD', 'CAD');
	protected $backward = false;

	public function __construct()
	{
		$this->name = 'stripejs';
		$this->tab = 'payments_gateways';
		$this->version = '0.9.2';
		$this->author = 'PrestaShop';
		$this->need_instance = 0;

		parent::__construct();

		$this->displayName = $this->l('Stripe');
		$this->description = $this->l('Accept payments by Credit Card with Stripe (Visa, Mastercard, Amex, Discover & Diners Club)');
		$this->confirmUninstall = $this->l('Warning: all the Stripe customers credit cards and transaction details saved in your database will be deleted. Are you sure you want uninstall this module?');

		/* Backward compatibility */
		if (_PS_VERSION_ < '1.5')
		{
			$this->backward_error = $this->l('In order to work properly in PrestaShop v1.4, the Stripe module requiers the backward compatibility module at least v0.3.').'<br />'.
				$this->l('You can download this module for free here: http://addons.prestashop.com/en/modules-prestashop/6222-backwardcompatibility.html');
			if (file_exists(_PS_MODULE_DIR_.'backwardcompatibility/backward_compatibility/backward.php'))
			{
				include(_PS_MODULE_DIR_.'backwardcompatibility/backward_compatibility/backward.php');
				$this->backward = true;
			}
			else
				$this->warning = $this->backward_error;
		}
		else
			$this->backward = true;
	}

	/**
	 * Stripe's module installation
	 *
	 * @return boolean Install result
	 */
	public function install()
	{
		if (!$this->backward && _PS_VERSION_ < 1.5)
		{
			echo '<div class="error">'.Tools::safeOutput($this->backward_error).'</div>';
			return false;
		}

		/* For 1.4.3 and less compatibility */
		$updateConfig = array(
			'PS_OS_CHEQUE' => 1,
			'PS_OS_PAYMENT' => 2,
			'PS_OS_PREPARATION' => 3,
			'PS_OS_SHIPPING' => 4,
			'PS_OS_DELIVERED' => 5,
			'PS_OS_CANCELED' => 6,
			'PS_OS_REFUND' => 7,
			'PS_OS_ERROR' => 8,
			'PS_OS_OUTOFSTOCK' => 9,
			'PS_OS_BANKWIRE' => 10,
			'PS_OS_PAYPAL' => 11,
			'PS_OS_WS_PAYMENT' => 12);

		foreach ($updateConfig as $u => $v)
			if (!Configuration::get($u) || (int)Configuration::get($u) < 1)
			{
				if (defined('_'.$u.'_') && (int)constant('_'.$u.'_') > 0)
					Configuration::updateValue($u, constant('_'.$u.'_'));
				else
					Configuration::updateValue($u, $v);
			}

		$ret = parent::install() && $this->registerHook('payment') && $this->registerHook('header') && $this->registerHook('backOfficeHeader') &&
		Configuration::updateValue('STRIPE_MODE', 0) && Configuration::updateValue('STRIPE_SAVE_TOKENS', 1) &&
		Configuration::updateValue('STRIPE_SAVE_TOKENS_ASK', 1) && Configuration::updateValue('STRIPE_PENDING_ORDER_STATUS', (int)Configuration::get('PS_OS_PAYMENT')) &&
		Configuration::updateValue('STRIPE_PAYMENT_ORDER_STATUS', (int)Configuration::get('PS_OS_PAYMENT')) &&
		Configuration::updateValue('STRIPE_CHARGEBACKS_ORDER_STATUS', (int)Configuration::get('PS_OS_ERROR')) &&
		Configuration::updateValue('STRIPE_WEBHOOK_TOKEN', md5(Tools::passwdGen())) && $this->installDb();

		/* The hook "displayMobileHeader" has been introduced in v1.5.x - Called separately to fail silently if the hook does not exist */
		$this->registerHook('displayMobileHeader');

		return $ret;
	}

	/**
	 * Stripe's module database tables installation
	 *
	 * @return boolean Database tables installation result
	 */
	public function installDb()
	{
		return Db::getInstance()->Execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'stripe_customer` (`id_stripe_customer` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`stripe_customer_id` varchar(32) NOT NULL, `token` varchar(32) NOT NULL, `id_customer` int(10) unsigned NOT NULL,
		`cc_last_digits` int(11) NOT NULL, `date_add` datetime NOT NULL, PRIMARY KEY (`id_stripe_customer`), KEY `id_customer` (`id_customer`),
		KEY `token` (`token`)) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1') &&
		Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'stripe_transaction` (`id_stripe_transaction` int(11) NOT NULL AUTO_INCREMENT,
		`type` enum(\'payment\',\'refund\') NOT NULL, `id_stripe_customer` int(10) unsigned NOT NULL, `id_cart` int(10) unsigned NOT NULL,
		`id_order` int(10) unsigned NOT NULL, `id_transaction` varchar(32) NOT NULL, `amount` decimal(10,2) NOT NULL, `status` enum(\'paid\',\'unpaid\') NOT NULL,
		`currency` varchar(3) NOT NULL, `cc_type` varchar(16) NOT NULL, `cc_exp` varchar(8) NOT NULL, `cc_last_digits` int(11) NOT NULL,
		`cvc_check` tinyint(1) NOT NULL DEFAULT \'0\', `fee` decimal(10,2) NOT NULL, `mode` enum(\'live\',\'test\') NOT NULL,
		`date_add` datetime NOT NULL, `charge_back` tinyint(1) NOT NULL DEFAULT \'0\', PRIMARY KEY (`id_stripe_transaction`), KEY `idx_transaction` (`type`,`id_order`,`status`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');
	}

	/**
	 * Stripe's module uninstallation (Configuration values, database tables...)
	 *
	 * @return boolean Uninstall result
	 */
	public function uninstall()
	{
		return parent::uninstall() && Configuration::deleteByName('STRIPE_PUBLIC_KEY_TEST') && Configuration::deleteByName('STRIPE_PUBLIC_KEY_LIVE')
		&& Configuration::deleteByName('STRIPE_MODE') && Configuration::deleteByName('STRIPE_PRIVATE_KEY_TEST') && Configuration::deleteByName('STRIPE_PRIVATE_KEY_LIVE') &&
		Configuration::deleteByName('STRIPE_SAVE_TOKENS') && Configuration::deleteByName('STRIPE_SAVE_TOKENS_ASK') && Configuration::deleteByName('STRIPE_CHARGEBACKS_ORDER_STATUS') &&
		Configuration::deleteByName('STRIPE_PENDING_ORDER_STATUS') && Configuration::deleteByName('STRIPE_PAYMENT_ORDER_STATUS') && Configuration::deleteByName('STRIPE_WEBHOOK_TOKEN') &&
		Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'stripe_customer`') && Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'stripe_transaction`');
	}

	public function hookDisplayMobileHeader()
	{
		return $this->hookHeader();
	}

	/**
	 * Load Javascripts and CSS related to the Stripe's module
	 * Only loaded during the checkout process
	 *
	 * @return string HTML/JS Content
	 */
	public function hookHeader()
	{
		/* If 1.4 and no backward, then leave */
		if (!$this->backward)
			return;

		if (!in_array($this->context->currency->iso_code, $this->limited_currencies))
			return ;

		/* Continue only if we are in the checkout process */
		if (Tools::getValue('controller') != 'order-opc' && (!($_SERVER['PHP_SELF'] == 'order.php' || Tools::getValue('controller') == 'order' || Tools::getValue('controller') == 'orderopc' || Tools::getValue('step') == 3)))
			return;

		/* Load JS and CSS files through CCC */
		$this->context->controller->addJS($this->_path.'stripe-prestashop.js');
		$this->context->controller->addCSS($this->_path.'stripe-prestashop.css');

		/* If the address check has been enabled by the merchant, we will transmitt the billing address to Stripe */
		if (isset($this->context->cart->id_address_invoice))
		{
			$billing_address = new Address((int)$this->context->cart->id_address_invoice);
			if ($billing_address->id_state)
			{
				$state = new State((int)$billing_address->id_state);
				if (Validate::isLoadedObject($state))
					$billing_address->state = $state->iso_code;
			}
		}

		return '
		<script type="text/javascript" src="https://js.stripe.com/v1/"></script>
		<script type="text/javascript">
			var stripe_public_key = \''.addslashes(Configuration::get('STRIPE_MODE') ? Configuration::get('STRIPE_PUBLIC_KEY_LIVE') : Configuration::get('STRIPE_PUBLIC_KEY_TEST')).'\';
			'.((isset($billing_address) && Validate::isLoadedObject($billing_address)) ? 'var stripe_billing_address = '.json_encode($billing_address).';' : '').'
			var stripe_secure_key = \''.addslashes($this->context->customer->secure_key).'\';
		</script>';
	}

	/**
	 * Display the Stripe's payment form
	 *
	 * @return string Stripe's Smarty template content
	 */
	public function hookPayment($params)
	{
		/* If 1.4 and no backward then leave */
		if (!$this->backward)
			return;

		/* If the currency is not supported, then leave */
		if (!in_array($this->context->currency->iso_code, $this->limited_currencies))
			return ;

		/* If the merchant has enabled the option to store credit cards, retrieve the most recent one for this customer */
		if (Configuration::get('STRIPE_SAVE_TOKENS'))
		{
			if (Configuration::get('STRIPE_SAVE_TOKENS_ASK'))
				$this->smarty->assign('stripe_save_tokens_ask', true);

			/* Retrieve the most recent customer's credit card */
			$customer_credit_card = Db::getInstance()->getValue('SELECT cc_last_digits FROM '._DB_PREFIX_.'stripe_customer WHERE id_customer = '.(int)$this->context->cookie->id_customer);
			if ($customer_credit_card)
				$this->smarty->assign('stripe_credit_card', (int)$customer_credit_card);
		}

		return $this->display(__FILE__, 'payment.tpl');
	}

	/**
	 * Display the two fieldsets containing Stripe's transactions details
	 * Visible on the Order's detail page in the Back-office only
	 *
	 * @return string HTML/JS Content
	 */
	public function hookBackOfficeHeader()
	{
		/* If 1.4 and no backward, then leave */
		if (!$this->backward)
			return;

		/* Continue only if we are on the order's details page (Back-office) */
		if (!isset($_GET['vieworder']) || !isset($_GET['id_order']))
			return;

		/* If the "Refund" button has been clicked, check if we can perform a partial or full refund on this order */
		if (Tools::isSubmit('SubmitStripeRefund') && isset($_POST['stripe_amount_to_refund']) && isset($_POST['id_transaction_stripe']))
		{
			/* Get transaction details and make sure the token is valid */
			$stripe_transaction_details = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'stripe_transaction WHERE id_order = '.(int)$_GET['id_order'].' AND type = \'payment\' AND status = \'paid\'');
			if (isset($stripe_transaction_details['id_transaction']) && $stripe_transaction_details['id_transaction'] === $_POST['id_transaction_stripe'])
			{
				/* Check how much has been refunded already on this order */
				$stripe_refunded = Db::getInstance()->getValue('SELECT SUM(amount) FROM '._DB_PREFIX_.'stripe_transaction WHERE id_order = '.(int)$_GET['id_order'].' AND type = \'refund\' AND status = \'paid\'');
				if ($_POST['stripe_amount_to_refund'] <= number_format($stripe_transaction_details['amount'] - $stripe_refunded, 2, '.', ''))
					$this->processRefund($_POST['id_transaction_stripe'], (float)$_POST['stripe_amount_to_refund'], $stripe_transaction_details);
				else
					$this->_errors['stripe_refund_error'] = $this->l('You cannot refund more than').' '.Tools::displayPrice($stripe_transaction_details['amount'] - $stripe_refunded).' '.$this->l('on this order');
			}
		}

		/* Check if the order was paid with Stripe and display the transaction details */
		if (Db::getInstance()->getValue('SELECT module FROM '._DB_PREFIX_.'orders WHERE id_order = '.(int)$_GET['id_order']) == $this->name)
		{
			/* Get the transaction details */
			$stripe_transaction_details = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'stripe_transaction WHERE id_order = '.(int)$_GET['id_order'].' AND type = \'payment\' AND status = \'paid\'');

			/* Get all the refunds previously made (to build a list and determine if another refund is still possible) */
			$stripe_refunded = 0;
			$output_refund = '';
			$stripe_refund_details = Db::getInstance()->ExecuteS('SELECT amount, status, date_add FROM '._DB_PREFIX_.'stripe_transaction
			WHERE id_order = '.(int)$_GET['id_order'].' AND type = \'refund\' ORDER BY date_add DESC');
			foreach ($stripe_refund_details as $stripe_refund_detail)
			{
				$stripe_refunded += ($stripe_refund_detail['status'] == 'paid' ? $stripe_refund_detail['amount'] : 0);
				$output_refund .= '<tr'.($stripe_refund_detail['status'] != 'paid' ? ' style="background: #FFBBAA;"': '').'><td>'.
				Tools::safeOutput($stripe_refund_detail['date_add']).'</td><td style="text-align: right;">'.Tools::displayPrice($stripe_refund_detail['amount']).
				'</td><td>'.($stripe_refund_detail['status'] == 'paid' ? $this->l('Processed') : $this->l('Error')).'</td></tr>';
			}

			$output = '
			<script type="text/javascript">
				$(document).ready(function() {
					$(\'<fieldset'.(_PS_VERSION_ < 1.5 ? ' style="width: 400px;"' : '').'><legend><img src="../img/admin/money.gif" alt="" />'.$this->l('Stripe Payment Details').'</legend>';

			if (isset($stripe_transaction_details['id_transaction']))
				$output .= $this->l('Stripe Transaction ID:').' '.Tools::safeOutput($stripe_transaction_details['id_transaction']).'<br /><br />'.
				$this->l('Status:').' <span style="font-weight: bold; color: '.($stripe_transaction_details['status'] == 'paid' ? 'green;">'.$this->l('Paid') : '#CC0000;">'.$this->l('Unpaid')).'</span><br />'.
				$this->l('Amount:').' '.Tools::displayPrice($stripe_transaction_details['amount']).'<br />'.
				$this->l('Processed on:').' '.Tools::safeOutput($stripe_transaction_details['date_add']).'<br />'.
				$this->l('Credit card:').' '.Tools::safeOutput($stripe_transaction_details['cc_type']).' ('.$this->l('Exp.:').' '.Tools::safeOutput($stripe_transaction_details['cc_exp']).')<br />'.
				$this->l('Last 4 digits:').' '.sprintf('%04d', $stripe_transaction_details['cc_last_digits']).' ('.$this->l('CVC Check:').' '.($stripe_transaction_details['cvc_check'] ? $this->l('OK') : '<span style="color: #CC0000; font-weight: bold;">'.$this->l('FAILED').'</span>').')<br />'.
				$this->l('Processing Fee:').' '.Tools::displayPrice($stripe_transaction_details['fee']).'<br /><br />'.
				$this->l('Mode:').' <span style="font-weight: bold; color: '.($stripe_transaction_details['mode'] == 'live' ? 'green;">'.$this->l('Live') : '#CC0000;">'.$this->l('Test (You will not receive any payment, until you enable the "Live" mode)')).'</span>';
			else
				$output .= '<b style="color: #CC0000;">'.$this->l('Warning:').'</b> '.$this->l('The customer paid using Stripe and an error occured (check details at the bottom of this page)');

			$output .= '</fieldset><br /><fieldset'.(_PS_VERSION_ < 1.5 ? ' style="width: 400px;"' : '').'><legend><img src="../img/admin/money.gif" alt="" />'.$this->l('Proceed to a full or partial refund via Stripe').'</legend>'.
			((empty($this->_errors['stripe_refund_error']) && isset($_POST['id_transaction_stripe'])) ? '<div class="conf confirmation">'.$this->l('Your refund was successfully processed').'</div>' : '').
			(!empty($this->_errors['stripe_refund_error']) ? '<span style="color: #CC0000; font-weight: bold;">'.$this->l('Error:').' '.Tools::safeOutput($this->_errors['stripe_refund_error']).'</span><br /><br />' : '').
			$this->l('Already refunded:').' <b>'.Tools::displayPrice($stripe_refunded).'</b><br /><br />'.($stripe_refunded ? '<table class="table" cellpadding="0" cellspacing="0" style="font-size: 12px;"><tr><th>'.$this->l('Date').'</th><th>'.$this->l('Amount refunded').'</th><th>'.$this->l('Status').'</th></tr>'.$output_refund.'</table><br />' : '').
			($stripe_transaction_details['amount'] > $stripe_refunded ? '<form action="" method="post">'.$this->l('Refund:').' $ <input type="text" value="'.number_format($stripe_transaction_details['amount'] - $stripe_refunded, 2, '.', '').
			'" name="stripe_amount_to_refund" style="text-align: right; width: 45px;" /> <input type="hidden" name="id_transaction_stripe" value="'.
			Tools::safeOutput($stripe_transaction_details['id_transaction']).'" /><input type="submit" class="button" onclick="return confirm(\\\''.addslashes($this->l('Do you want to proceed to this refund?')).'\\\');" name="SubmitStripeRefund" value="'.
			$this->l('Process Refund').'" /></form>' : '').'</fieldset><br />\').insertBefore($(\'select[name=id_order_state]\').parent().parent().find(\'fieldset\').first());
				});
			</script>';

			return $output;
		}
	}

	/**
	 * Process a partial or full refund
	 *
	 * @param string $id_transaction_stripe Stripe Transaction ID (token)
	 * @param float $amount Amount to refund
	 * @param array $original_transaction Original transaction details
	 */
	public function processRefund($id_transaction_stripe, $amount, $original_transaction)
	{
		/* If 1.4 and no backward, then leave */
		if (!$this->backward)
			return;

		include(dirname(__FILE__).'/lib/Stripe.php');
		Stripe::setApiKey(Configuration::get('STRIPE_MODE') ? Configuration::get('STRIPE_PRIVATE_KEY_LIVE') : Configuration::get('STRIPE_PRIVATE_KEY_TEST'));

		/* Try to process the refund and catch any error message */
		try
		{
			$charge = Stripe_Charge::retrieve($id_transaction_stripe);
			$result_json = json_decode($charge->refund(array('amount' => $amount * 100)));
		}
		catch (Exception $e)
		{
			$this->_errors['stripe_refund_error'] = $e->getMessage();
			if (class_exists('Logger'))
				Logger::addLog($this->l('Stripe - Refund transaction failed').' '.$e->getMessage(), 2, null, 'Cart', (int)$this->context->cart->id, true);
		}

		/* Store the refund details */
		Db::getInstance()->Execute('
		INSERT INTO '._DB_PREFIX_.'stripe_transaction (type, id_stripe_customer, id_cart, id_order,
		id_transaction, amount, status, currency, cc_type, cc_exp, cc_last_digits, fee, mode, date_add)
		VALUES (\'refund\', '.(int)$original_transaction['id_stripe_customer'].', '.(int)$original_transaction['id_cart'].', '.
		(int)$original_transaction['id_order'].', \''.pSQL($id_transaction_stripe).'\',
		\''.(float)$amount.'\', \''.(!isset($this->_errors['stripe_refund_error']) ? 'paid' : 'unpaid').'\', \''.pSQL($result_json->currency).'\',
		\'\', \'\', 0, 0, \''.(Configuration::get('STRIPE_MODE') ? 'live' : 'test').'\', NOW())');
	}

	/**
	 * Process a payment
	 *
	 * @param string $token Stripe Transaction ID (token)
	 */
	public function processPayment($token)
	{
		/* If 1.4 and no backward, then leave */
		if (!$this->backward)
			return;

		include(dirname(__FILE__).'/lib/Stripe.php');
		Stripe::setApiKey(Configuration::get('STRIPE_MODE') ? Configuration::get('STRIPE_PRIVATE_KEY_LIVE') : Configuration::get('STRIPE_PRIVATE_KEY_TEST'));

		/* Case 1: Charge an existing customer (or create it and charge it) */
		/* Case 2: Just process the transaction, do not save Stripe customer's details */
		if ((Configuration::get('STRIPE_SAVE_TOKENS') && !Configuration::get('STRIPE_SAVE_TOKENS_ASK')) ||
		(Configuration::get('STRIPE_SAVE_TOKENS') && Configuration::get('STRIPE_SAVE_TOKENS_ASK') && isset($_POST['stripe_save_token']) && $_POST['stripe_save_token']))
		{
			/* Get or Create a Stripe Customer */
			$stripe_customer = Db::getInstance()->getRow('
			SELECT id_stripe_customer, stripe_customer_id, token
			FROM '._DB_PREFIX_.'stripe_customer
			WHERE id_customer = '.(int)$this->context->cookie->id_customer);

			if (!isset($stripe_customer['id_stripe_customer']))
			{
				try
				{
					$stripe_customer_exists = false;
					$customer_stripe = Stripe_Customer::create(array('card' => $token, 'description' => $this->l('PrestaShop Customer ID:').' '.(int)$this->context->cookie->id_customer));
					$stripe_customer['stripe_customer_id'] = $customer_stripe->id;
				}
				catch (Exception $e)
				{
					/* If the Credit card is invalid */
					$this->_errors['invalid_customer_card'] = true;
					if (class_exists('Logger'))
						Logger::addLog($this->l('Stripe - Invalid Credit Card'), 1, null, 'Cart', (int)$this->context->cart->id, true);
				}
			}
			else
			{
				$stripe_customer_exists = true;

				/* Update the credit card in the database */
				if ($token && $token != $stripe_customer['token'])
				{
					try
					{
						$cu = Stripe_Customer::retrieve($stripe_customer['stripe_customer_id']);
						$cu->card = $token;
						$cu->save();

						Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'stripe_customer SET token = \''.pSQL($token).'\' WHERE id_customer_stripe = '.(int)$stripe_customer['id_stripe_customer']);
					}
					catch (Exception $e)
					{
						/* If the new Credit card is invalid, do not replace the old one - no warning or error message required */
						$this->_errors['invalid_customer_card'] = true;
						if (class_exists('Logger'))
							Logger::addLog($this->l('Stripe - Invalid Credit Card (replacing an old card)'), 1, null, 'Cart', (int)$this->context->cart->id, true);
					}
				}
			}
		}

		try
		{
			$charge_details = array('amount' => $this->context->cart->getOrderTotal() * 100, 'currency' => $this->context->currency->iso_code, 'description' => $this->l('PrestaShop Customer ID:').
			' '.(int)$this->context->cookie->id_customer.' - '.$this->l('PrestaShop Cart ID:').' '.(int)$this->context->cart->id);

			/* If we have a Stripe's customer ID for this buyer, charge the customer instead of the card */
			if (isset($stripe_customer['stripe_customer_id']) && !isset($this->_errors['invalid_customer_card']))
				$charge_details['customer'] = $stripe_customer['stripe_customer_id'];
			else
				$charge_details['card'] = $token;

			$result_json = json_decode(Stripe_Charge::create($charge_details));

			/* Save the Customer ID in PrestaShop to re-use it later */
			if (isset($stripe_customer_exists) && !$stripe_customer_exists)
				Db::getInstance()->Execute('
				INSERT INTO '._DB_PREFIX_.'stripe_customer (id_stripe_customer, stripe_customer_id, token, id_customer, cc_last_digits, date_add)
				VALUES (NULL, \''.pSQL($stripe_customer['stripe_customer_id']).'\', \''.pSQL($token).'\', '.(int)$this->context->cookie->id_customer.', '.(int)substr(Tools::getValue('StripLastDigits'), 0, 4).', \'NOW()\')');
		}
		catch (Exception $e)
		{
			$message = $e->getMessage();
			if (class_exists('Logger'))
				Logger::addLog($this->l('Stripe - Payment transaction failed').' '.$message, 1, null, 'Cart', (int)$this->context->cart->id, true);

			/* If it's not a critical error, display the payment form again */
			if ($e->getCode() != 'card_declined')
			{
				$controller = Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc' : 'order';
				header('Location: '.$this->context->link->getPageLink($controller).(strpos($controller, '?') !== false ? '&' : '&').'step=3&stripe_error='.base64_encode($e->getMessage()).'#stripe_error');
				exit;
			}
		}

		/* Log Transaction details */
		if (!isset($message))
		{
			$order_status = (int)Configuration::get('STRIPE_PAYMENT_ORDER_STATUS');
			$message = $this->l('Stripe Transaction Details:')."\n\n".
			$this->l('Stripe Transaction ID:').' '.$result_json->id."\n".
			$this->l('Amount:').' '.($result_json->amount * 0.01)."\n".
			$this->l('Status:').' '.($result_json->paid == 'true' ? $this->l('Paid') : $this->l('Unpaid'))."\n".
			$this->l('Processed on:').' '.strftime('%Y-%m-%d %H:%M:%S', $result_json->created)."\n".
			$this->l('Currency:').' '.strtoupper($result_json->currency)."\n".
			$this->l('Credit card:').' '.$result_json->card->type.' ('.$this->l('Exp.:').' '.$result_json->card->exp_month.'/'.$result_json->card->exp_year.')'."\n".
			$this->l('Last 4 digits:').' '.sprintf('%04d', $result_json->card->last4).' ('.$this->l('CVC Check:').' '.($result_json->card->cvc_check == 'pass' ? $this->l('OK') : $this->l('NOT OK')).')'."\n".
			$this->l('Processing Fee:').' '.($result_json->fee * 0.01)."\n".
			$this->l('Mode:').' '.($result_json->livemode == 'true' ? $this->l('Live') : $this->l('Test'))."\n";

			/* In case of successful payment, the address / zip-code can however fail */
			if (isset($result_json->card->address_line1_check) && $result_json->card->address_line1_check == 'fail')
			{
				$message .= "\n".$this->l('Warning: Address line 1 check failed');
				$order_status = (int)Configuration::get('STRIPE_PENDING_ORDER_STATUS');
			}
			if (isset($result_json->card->address_zip_check) && $result_json->card->address_zip_check == 'fail')
			{
				$message .= "\n".$this->l('Warning: Address zip-code check failed');
				$order_status = (int)Configuration::get('STRIPE_PENDING_ORDER_STATUS');
			}
		}
		else
			$order_status = (int)Configuration::get('PS_OS_ERROR');

		/* Create the PrestaShop order in database */
		$this->validateOrder((int)$this->context->cart->id, (int)$order_status, ($result_json->amount * 0.01), $this->l('Stripe (Credit Card)'), $message, array(), null, false, $this->context->customer->secure_key);

		/** @since 1.5.0 Attach the Stripe Transaction ID to this Order */
		if (version_compare(_PS_VERSION_, '1.5', '>='))
		{
			$new_order = new Order((int)$this->currentOrder);
			if (Validate::isLoadedObject($new_order))
			{
				$payment = $new_order->getOrderPaymentCollection();
				if (isset($payment[0]))
				{
					$payment[0]->transaction_id = pSQL($result_json->id);
					$payment[0]->save();
				}
			}
		}

		/* Store the transaction details */
		if (isset($result_json->id))
			Db::getInstance()->Execute('
			INSERT INTO '._DB_PREFIX_.'stripe_transaction (type, id_stripe_customer, id_cart, id_order,
			id_transaction, amount, status, currency, cc_type, cc_exp, cc_last_digits, cvc_check, fee, mode, date_add)
			VALUES (\'payment\', '.(isset($stripe_customer['id_stripe_customer']) ? (int)$stripe_customer['id_stripe_customer'] : 0).', '.(int)$this->context->cart->id.', '.(int)$this->currentOrder.', \''.pSQL($result_json->id).'\',
			\''.($result_json->amount * 0.01).'\', \''.($result_json->paid == 'true' ? 'paid' : 'unpaid').'\', \''.pSQL($result_json->currency).'\',
			\''.pSQL($result_json->card->type).'\', \''.(int)$result_json->card->exp_month.'/'.(int)$result_json->card->exp_year.'\', '.(int)$result_json->card->last4.',
			'.($result_json->card->cvc_check == 'pass' ? 1 : 0).', \''.($result_json->fee * 0.01).'\', \''.($result_json->livemode == 'true' ? 'live' : 'test').'\', NOW())');

		/* Redirect the user to the order confirmation page / history */
		if (_PS_VERSION_ < 1.4)
			Tools::redirect('order-confirmation.php?id_cart='.(int)$this->context->cart->id.'&id_module='.(int)$this->id.'&id_order='.(int)$this->currentOrder.'&key='.$this->context->customer->secure_key);
		else
			Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$this->context->cart->id.'&id_module='.(int)$this->id.'&id_order='.(int)$this->currentOrder.'&key='.$this->context->customer->secure_key);
		exit;
	}

	/**
	 * Delete a Customer's Credit Card
	 *
	 * @return integer Credit Card deletion result (1 = worked, 0 = did not worked)
	 */
	public function deleteCreditCard()
	{
		if (isset($this->context->cookie->id_customer) && $this->context->cookie->id_customer)
			return (int)Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'stripe_customer WHERE id_customer = '.(int)$this->context->cookie->id_customer);

		return 0;
	}

	/**
	 * Check settings requirements to make sure the Stripe's module will work properly
	 *
	 * @return boolean Check result
	 */
	public function checkSettings()
	{
		if (Configuration::get('STRIPE_MODE'))
			return Configuration::get('STRIPE_PUBLIC_KEY_LIVE') != '' && Configuration::get('STRIPE_PRIVATE_KEY_LIVE') != '';
		else
			return Configuration::get('STRIPE_PUBLIC_KEY_TEST') != '' && Configuration::get('STRIPE_PRIVATE_KEY_TEST') != '';
	}

	/**
	 * Check technical requirements to make sure the Stripe's module will work properly
	 *
	 * @return array Requirements tests results
	 */
	public function checkRequirements()
	{
		$tests = array('result' => true);
		$tests['curl'] = array('name' => $this->l('PHP cURL extension must be enabled on your server'), 'result' => is_callable('curl_exec'));
		if (Configuration::get('STRIPE_MODE'))
			$tests['ssl'] = array('name' => $this->l('SSL must be enabled on your store (before entering Live mode)'), 'result' => Configuration::get('PS_SSL_ENABLED') || (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off'));
		$tests['currencies'] = array('name' => $this->l('The currency USD or CAD must be enabled on your store'), 'result' => Currency::exists('USD', 0) || Currency::exists('CAD', 0));
		$tests['php52'] = array('name' => $this->l('Your server must run PHP 5.2 or greater'), 'result' => version_compare(PHP_VERSION, '5.2.0', '>='));
		$tests['configuration'] = array('name' => $this->l('Your must sign-up for Stripe and configure your account settings in the module (publishable key, secret key...etc.)'), 'result' => $this->checkSettings());

		if (_PS_VERSION_ < 1.5)
		{
			$tests['backward'] = array('name' => $this->l('You are using the backward compatibility module'), 'result' => $this->backward, 'resolution' => $this->backward_error);
			$tmp = Module::getInstanceByName('mobile_theme');
			if ($tmp && isset($tmp->version) && !version_compare($tmp->version, '0.3.8', '>='))
				$tests['mobile_version'] = array('name' => $this->l('You are currently using the default mobile template, the minimum version required is v0.3.8').' (v'.$tmp->version.' '.$this->l('detected').' - <a target="_blank" href="http://addons.prestashop.com/en/mobile-iphone/6165-prestashop-mobile-template.html">'.$this->l('Please Upgrade').'</a>)', 'result' => version_compare($tmp->version, '0.3.8', '>='));
		}

		foreach ($tests as $k => $test)
			if ($k != 'result' && !$test['result'])
				$tests['result'] = false;

		return $tests;
	}

	/**
	 * Display the Back-office interface of the Stripe's module
	 *
	 * @return string HTML/JS Content
	 */
	public function getContent()
	{
		$output = '';
		if (version_compare(_PS_VERSION_,'1.5','>'))
			$this->context->controller->addJQueryPlugin('fancybox');
		else
			$output .= '
			<script type="text/javascript" src="'.__PS_BASE_URI__.'js/jquery/jquery.fancybox-1.3.4.js"></script>
		  	<link type="text/css" rel="stylesheet" href="'.__PS_BASE_URI__.'css/jquery.fancybox-1.3.4.css" />';

		/* Update Configuration Values when settings are updated */
		if (Tools::isSubmit('SubmitStripe'))
		{
			$configuration_values = array('STRIPE_MODE' => $_POST['stripe_mode'], 'STRIPE_SAVE_TOKENS' => $_POST['stripe_save_tokens'],
			'STRIPE_SAVE_TOKENS_ASK' => $_POST['stripe_save_tokens_ask'], 'STRIPE_PUBLIC_KEY_TEST' => $_POST['stripe_public_key_test'],
			'STRIPE_PUBLIC_KEY_LIVE' => $_POST['stripe_public_key_live'], 'STRIPE_PRIVATE_KEY_TEST' => $_POST['stripe_private_key_test'],
			'STRIPE_PRIVATE_KEY_LIVE' => $_POST['stripe_private_key_live'], 'STRIPE_PENDING_ORDER_STATUS' => (int)$_POST['stripe_pending_status'],
			'STRIPE_PAYMENT_ORDER_STATUS' => (int)$_POST['stripe_payment_status'], 'STRIPE_CHARGEBACKS_ORDER_STATUS' => (int)$_POST['stripe_chargebacks_status']);

			foreach ($configuration_values as $configuration_key => $configuration_value)
				Configuration::updateValue($configuration_key, $configuration_value);
		}

		$requirements = $this->checkRequirements();

		$output .= '
		<script type="text/javascript">
			/* Fancybox */
			$(\'a.stripe-module-video-btn\').live(\'click\', function(){
			    $.fancybox({\'type\' : \'iframe\', \'href\' : this.href.replace(new RegExp(\'watch\\?v=\', \'i\'), \'embed/\') + \'?rel=0&autoplay=1\',
			    \'swf\': {\'allowfullscreen\':\'true\', \'wmode\':\'transparent\'}, \'overlayShow\' : true, \'centerOnScroll\' : true,
			    \'speedIn\' : 100, \'speedOut\' : 50, \'width\' : 853, \'height\' : 480 });
			    return false;
			});
		</script>
		<link href="'.$this->_path.'stripe-prestashop-admin.css" rel="stylesheet" type="text/css" media="all" />
		<div class="stripe-module-wrapper">
			'.(Tools::isSubmit('SubmitStripe') ? '<div class="conf confirmation">'.$this->l('Settings successfully saved').'<img src="http://www.prestashop.com/modules/'.$this->name.'.png?api_user='.urlencode($_SERVER['HTTP_HOST']).'" style="display: none;" /></div>' : '').'
			<div class="stripe-module-header">
				<a href="https://stripe.com/signup" rel="external"><img src="'.$this->_path.'img/stripe-logo.gif" alt="stripe" class="stripe-logo" /></a>
				<span class="stripe-module-intro">'.$this->l('Stripe makes it easy to start accepting credit cards on the web today.').'</span>
				<a href="https://stripe.com/signup" rel="external" class="stripe-module-create-btn"><span>'.$this->l('Create an Account').'</span></a>
			</div>
			<div class="stripe-module-wrap">
				<div class="stripe-module-col1 floatRight">
					<div class="stripe-module-wrap-video">
						<h3>'.$this->l('Easy to setup').'</h3>
						<p>'.$this->l('Follow these simple steps to setup your module and start accepting payments with Stripe.').'</p>
						<a href="http://www.youtube.com/embed/A-cLaIHgSeA?hd=1" class="stripe-module-video-btn"><img src="'.$this->_path.'img/stripe-dashboard.png" alt="stripe dashboard" class="stripe-dashboard" /><img src="'.$this->_path.'img/stripe-btn-video.png" alt="" class="stripe-video-btn" /></a>
					</div>
				</div>
				<div class="stripe-module-col2">
					<div class="stripe-module-col1inner">
						<h3>'.$this->l('You\'ll love to use Stripe').'</h3>
						<ul>
							<li>'.$this->l('Ability to store credit card aliases').'</li>
							<li>'.$this->l('Ability to handle chargebacks/disputes').'</li>
							<li>'.$this->l('Address & zip-code checked against fraud').'</li>
							<li>'.$this->l('Full transactions details (Back Office)').'</li>
							<li>'.$this->l('Ability to perform full or partial refunds').'</li>
						</ul>
					</div>
					<div class="stripe-module-col1inner floatRight">
						<h3>'.$this->l('Pricing like it should be').'</h3>
						<p><strong>'.$this->l('2.9% + 30 cents per successful charge.').'</strong></p>
						<p>'.$this->l('No setup fees, no monthly fees, no card storage fees, no hidden costs: you only get charged when you earn money.').'</p>
					</div>
					<div class="stripe-module-col2inner">
						<h3>'.$this->l('Accept payments worldwide using all major credit cards').'</h3>
						<p><img src="'.$this->_path.'img/stripe-cc.png" alt="stripe" class="stripe-cc" /> <a href="https://stripe.com/signup" class="stripe-module-btn"><strong>'.$this->l('Create a FREE Account!').'</strong></a></p>
					</div>
				</div>
			</div>
			<fieldset>
				<legend><img src="'.$this->_path.'img/checks-icon.gif" alt="" />'.$this->l('Technical Checks').'</legend>
				<div class="'.($requirements['result'] ? 'conf">'.$this->l('Good news! All the checks were successfully performed. You can now configure your module and start using Stripe.') :
				'warn">'.$this->l('Unfortunately, at least one issue is preventing you from using Stripe. Please fix the issue and reload this page.')).'</div>
				<table cellspacing="0" cellpadding="0" class="stripe-technical">';
				foreach ($requirements as $k => $requirement)
					if ($k != 'result')
						$output .= '
						<tr>
							<td><img src="../img/admin/'.($requirement['result'] ? 'ok' : 'forbbiden').'.gif" alt="" /></td>
							<td>'.$requirement['name'].(!$requirement['result'] && isset($requirement['resolution']) ? '<br />'.Tools::safeOutput($requirement['resolution'], true) : '').'</td>
						</tr>';
				$output .= '
				</table>
			</fieldset>
		<br />';

		/* If 1.4 and no backward, then leave */
		if (!$this->backward)
			return $output;

		$statuses = OrderState::getOrderStates((int)$this->context->cookie->id_lang);
		$output .= '
		<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post">
			<fieldset class="stripe-settings">
				<legend><img src="'.$this->_path.'img/technical-icon.gif" alt="" />'.$this->l('Settings').'</legend>
				<label>'.$this->l('Mode').'</label>
				<input type="radio" name="stripe_mode" value="0"'.(!Configuration::get('STRIPE_MODE') ? ' checked="checked"' : '').' /> Test
				<input type="radio" name="stripe_mode" value="1"'.(Configuration::get('STRIPE_MODE') ? ' checked="checked"' : '').' /> Live
				<br /><br />
				<table cellspacing="0" cellpadding="0" class="stripe-settings">
					<tr>
						<td align="right" valign="middle" width="50%"><label>'.$this->l('Enable credit card information to be stored (aliases)').'</label></td>
						<td align="left" valign="middle" class="td-right">
							<input type="radio" name="stripe_save_tokens" value="1"'.(Configuration::get('STRIPE_SAVE_TOKENS') ? ' checked="checked"' : '').' /> Yes
							<input type="radio" name="stripe_save_tokens" value="0"'.(!Configuration::get('STRIPE_SAVE_TOKENS') ? ' checked="checked"' : '').' /> No</td>
					</tr>
					<tr class="stripe_save_token_tr">
						<td align="right" valign="middle"><label>'.$this->l('Give customers the option to choose whether or not to store their credit card aliases').'</label></td>
						<td align="left" valign="middle" class="td-right">
							<input type="radio" name="stripe_save_tokens_ask" value="1"'.(Configuration::get('STRIPE_SAVE_TOKENS_ASK') ? ' checked="checked"' : '').' /> Yes
							<input type="radio" name="stripe_save_tokens_ask" value="0"'.(!Configuration::get('STRIPE_SAVE_TOKENS_ASK') ? ' checked="checked"' : '').' /> No</td>
					</tr>
					<tr>
						<td align="center" valign="middle" colspan="2">
							<table cellspacing="0" cellpadding="0" class="innerTable">
								<tr>
									<td align="right" valign="middle">'.$this->l('Test Publishable Key').'</td>
									<td align="left" valign="middle"><input type="text" name="stripe_public_key_test" value="'.Tools::safeOutput(Configuration::get('STRIPE_PUBLIC_KEY_TEST')).'" /></td>
									<td width="15"></td>
									<td width="15" class="vertBorder"></td>
									<td align="left" valign="middle">'.$this->l('Live Publishable Key').'</td>
									<td align="left" valign="middle"><input type="text" name="stripe_public_key_live" value="'.Tools::safeOutput(Configuration::get('STRIPE_PUBLIC_KEY_LIVE')).'" /></td>
								</tr>
								<tr>
									<td align="right" valign="middle">'.$this->l('Test Secret Key').'</td>
									<td align="left" valign="middle"><input type="password" name="stripe_private_key_test" value="'.Tools::safeOutput(Configuration::get('STRIPE_PRIVATE_KEY_TEST')).'" /></td>
									<td width="15"></td>
									<td width="15" class="vertBorder"></td>
									<td align="left" valign="middle">'.$this->l('Live Secret Key').'</td>
									<td align="left" valign="middle"><input type="password" name="stripe_private_key_live" value="'.Tools::safeOutput(Configuration::get('STRIPE_PRIVATE_KEY_LIVE')).'" /></td>
								</tr>
							</table>
						</td>
					</tr>';

					$statuses_options = array(array('name' => 'stripe_payment_status', 'label' => $this->l('Order status in case of sucessfull payment:'), 'current_value' => Configuration::get('STRIPE_PAYMENT_ORDER_STATUS')),
					array('name' => 'stripe_pending_status', 'label' => $this->l('Order status in case of unsucessfull address/zip-code check:'), 'current_value' => Configuration::get('STRIPE_PENDING_ORDER_STATUS')),
					array('name' => 'stripe_chargebacks_status', 'label' => $this->l('Order status in case of a chargeback (dispute):'), 'current_value' => Configuration::get('STRIPE_CHARGEBACKS_ORDER_STATUS')));
					foreach ($statuses_options as $status_options)
					{
						$output .= '
						<tr>
							<td align="right" valign="middle"><label>'.$status_options['label'].'</label></td>
							<td align="left" valign="middle" class="td-right">
								<select name="'.$status_options['name'].'">';
									foreach ($statuses as $status)
										$output .= '<option value="'.(int)$status['id_order_state'].'"'.($status['id_order_state'] == $status_options['current_value'] ? ' selected="selected"' : '').'>'.Tools::safeOutput($status['name']).'</option>';
						$output .= '
								</select>
							</td>
						</tr>';
					}

					$output .= '
					<tr>
						<td colspan="2" class="td-noborder save"><input type="submit" class="button" name="SubmitStripe" value="'.$this->l('Save Settings').'" /></td>
					</tr>
				</table>
			</fieldset>
			<fieldset class="stripe-cc-numbers">
				<legend><img src="'.$this->_path.'img/cc-icon.gif" alt="" />'.$this->l('Test Credit Card Numbers').'</legend>
				<table cellspacing="0" cellpadding="0" class="stripe-cc-numbers">
				  <thead>
					<tr>
					  <th>'.$this->l('Number').'</th>
					  <th>'.$this->l('Card type').'</th>
					</tr>
				  </thead>
				  <tbody>
					<tr><td class="number"><code>4242424242424242</code></td><td>Visa</td></tr>
					<tr><td class="number"><code>5555555555554444</code></td><td>MasterCard</td></tr>
					<tr><td class="number"><code>378282246310005</code></td><td>American Express</td></tr>
					<tr><td class="number"><code>6011111111111117</code></td><td>Discover</td></tr>
					<tr><td class="number"><code>30569309025904</code></td><td>Diner\'s Club</td></tr>
					<tr><td class="number last"><code>3530111333300000</code></td><td class="last">JCB</td></tr>
				  </tbody>
				</table>
			</fieldset>
			<div class="clear"></div>
			<br />
			<fieldset>
				<legend><img src="'.$this->_path.'img/checks-icon.gif" alt="" />'.$this->l('Webhooks').'</legend>
				'.$this->l('In order to receive chargeback information from Stripe, you must provide a Webhook link in Stripe\'s admin panel.').'<br />
				'.$this->l('To get started, please visit Stripe and setup the following Webhook:').'<br /><br />
			  <strong>'.(Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/stripejs/webhooks.php?token='.Tools::safeOutput(Configuration::get('STRIPE_WEBHOOK_TOKEN')).'</strong>
			</fieldset>

		</div>
		</form>
		<script type="text/javascript">
			function updateStripeSettings()
			{
				if ($(\'input:radio[name=stripe_mode]:checked\').val() == 1)
					$(\'fieldset.stripe-cc-numbers\').hide();
				else
					$(\'fieldset.stripe-cc-numbers\').show(1000);

				if ($(\'input:radio[name=stripe_save_tokens]:checked\').val() == 1)
					$(\'tr.stripe_save_token_tr\').show(1000);
				else
					$(\'tr.stripe_save_token_tr\').hide();
			}

			$(\'input:radio[name=stripe_mode]\').click(function() { updateStripeSettings(); });
			$(\'input:radio[name=stripe_save_tokens]\').click(function() { updateStripeSettings(); });
			$(document).ready(function() { updateStripeSettings(); });
		</script>';

		return $output;
	}
}
