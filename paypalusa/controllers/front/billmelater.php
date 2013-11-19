<?php
/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
class PayPalusaBillMeLaterModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		//PAYPAL_USA_PAYMENT_BML
		$this->paypal_usa = new PayPalUSA();
		if ($this->paypal_usa->active && Configuration::get('PAYPAL_USA_PAYMENT_BML') == 1)
		{
			$pp_exp = 1 * (int)Tools::getValue('pp_bml_initial') + 2 * (int)Tools::getValue('pp_bml_checkout') + 3 * (int)Tools::getValue('pp_bml_payment');
			

			switch ($pp_exp)
			{
				/* Step 1 - Called the 1st time customer is clicking on the PayPal BillMeLater Checkout button */
				case 1:
					$this->_bmlCheckoutInitial();
					break;
				/* Step 2 - Called by PayPal when the customer is redirected back from PayPal to the store (to retrieve the customer address and details) */
				case 2:
					$this->_bmlCheckout();
					break;
				/* Step 3 - Called when the customer is placing his/her order / making his payment */
				case 3:
					$this->_bmlCheckoutPayment();
					break;
				default :
					$this->_bmlCheckoutInitial();
			}
		}
	}

	/**
	 * Upon a click on the "PayPal Express Checkout" button, this function creates a PayPal payment request
	 * If the customer was coming from a product page, it will also add the product to his/her shopping cart.
	 * Eventually it will redirect the customer to PayPal (to log-in or to fill his/her credit card info)
	 */
	private function _bmlCheckoutInitial()
	{
		
		/* If the customer has no cart yet, we need to create an empty one */
		if (!$this->context->cart->id)
		{	
			if ($this->context->cookie->id_guest)
			{
				$guest = new Guest((int)$this->context->cookie->id_guest);
				$this->context->cart->mobile_theme = $guest->mobile_theme;
			}
			$this->context->cart->add();
			if ($this->context->cart->id)
				$this->context->cookie->id_cart = (int)$this->context->cart->id;
		}

		/* If the customer is coming from a product page, we need to add his/her product to the cart */
		if (Tools::getValue('paypal_billmelater_checkout_id_product') != '')
			$this->context->cart->updateQty((int)Tools::getValue('paypal_billmelater_checkout_quantity'), (int)Tools::getValue('paypal_billmelater_checkout_id_product'), (int)Tools::getValue('paypal_billmelater_checkout_id_product_attribute'));

		$nvp_request = '';
		$i = 0;
		$totalToPay = (float)$this->context->cart->getOrderTotal(true);
		$totalToPayWithoutTaxes = (float)$this->context->cart->getOrderTotal(false);
		$total_price = 0;
		$total_product = 0;
		foreach ($this->context->cart->getProducts() as $product)
		{
			$nvp_request .= '&L_PAYMENTREQUEST_0_NAME'.$i.'='.urlencode($product['name']).
					'&L_PAYMENTREQUEST_0_NUMBER'.$i.'='.urlencode((int)$product['id_product']).
					'&L_PAYMENTREQUEST_0_DESC'.$i.'='.urlencode(strip_tags(Tools::truncate($product['description_short'], 80))).
					'&L_PAYMENTREQUEST_0_AMT'.$i.'='.urlencode((float)$product['price']).
					'&L_PAYMENTREQUEST_0_QTY'.$i.'='.urlencode((int)$product['cart_quantity']);
			$total_product += (float)$product['price'] * (int)$product['cart_quantity'];
			$i++;
		}
		
		$cart_discount = current($this->context->cart->getCartRules(CartRule::FILTER_ACTION_REDUCTION));
		

		
		$shipping_cost = 0;
		

		$shipping_cost = $this->context->cart->getTotalShippingCost();
		
		$express_url = '';
		$order_url = '';

		$express_url = $this->context->link->getModuleLink('paypalusa', 'billmelater', array('pp_bml_checkout' => 1,));
		$order_url = $this->context->link->getPageLink('order.php');
	
		
		if (($totalToPay - ($total_product + $shipping_cost+ ($totalToPay - $totalToPayWithoutTaxes))) != 0)
		{
			$nvp_request .= '&L_PAYMENTREQUEST_0_NAME'.$i.'='.urlencode($this->paypal_usa->l('Coupon')).
					'&L_PAYMENTREQUEST_0_DESC'.$i.'='.urlencode(strip_tags(Tools::truncate($cart_discount['description'], 80))).
					'&L_PAYMENTREQUEST_0_AMT'.$i.'='.urlencode(number_format($totalToPay - ($total_product + $shipping_cost+ ($totalToPay - $totalToPayWithoutTaxes)),2)).
					'&L_PAYMENTREQUEST_0_QTY'.$i.'=1';
			$i++;
		}
		$nvp_request .= '&L_PAYMENTREQUEST_0_NAME'.$i.'='.urlencode($this->paypal_usa->l('Shipping fees')).
				'&L_PAYMENTREQUEST_0_AMT'.$i.'='.urlencode((float)$shipping_cost).
				'&L_PAYMENTREQUEST_0_QTY'.$i.'=1'.
				'&PAYMENTREQUEST_0_ITEMAMT='.(float)$totalToPayWithoutTaxes.
				'&PAYMENTREQUEST_0_TAXAMT='.(float)($totalToPay - $totalToPayWithoutTaxes);

		/* Create a PayPal payment request and redirect the customer to PayPal (to log-in or to fill his/her credit card info) */
		$currency = new Currency((int)$this->context->cart->id_currency);		
		$nvp_request .= '&UserSelectedFundingSource=BML';
		$nvp_request .= '&SOLUTIONTYPE=SOLE';

		$result = $this->paypal_usa->postToPayPal('SetExpressCheckout', (Configuration::get('PAYPAL_USA_EXP_CHK_BORDER_COLOR') != '' ? '&CARTBORDERCOLOR='.Tools::substr(str_replace('#', '', Configuration::get('PAYPAL_USA_EXP_CHK_BORDER_COLOR')), 0, 6) : '').'&PAYMENTREQUEST_0_AMT='.$totalToPay.'&PAYMENTREQUEST_0_PAYMENTACTION=Sale&RETURNURL='.urlencode($express_url).'&CANCELURL='.urlencode($order_url).'&PAYMENTREQUEST_0_CURRENCYCODE='.urlencode($currency->iso_code).$nvp_request);
		
		
		
		
		
		if (Tools::strtoupper($result['ACK']) == 'SUCCESS' || Tools::strtoupper($result['ACK']) == 'SUCCESSWITHWARNING')
		{
		//_xclick
			Tools::redirect('https://www.'.(Configuration::get('PAYPAL_USA_SANDBOX') ? 'sandbox.' : '').'paypal.com/'.(Configuration::get('PAYPAL_USA_SANDBOX') ? '' : 'cgi-bin/').'webscr?cmd=_express-checkout&token='.urldecode($result['TOKEN']));
			exit;
		}
		else
		{
			foreach ($result as $key => $val)
				$result[$key] = urldecode($val);

			$this->context->smarty->assign('paypal_usa_errors', $result);
	
			$this->setTemplate('express-checkout-messages.tpl');
			
		}
	}

	/**
	 * When the customer is back from PayPal after filling his/her credit card info or credentials, this function is preparing the order
	 * PayPal is providing us with the customer info (E-mail address, billing address) and we are trying to find a matching customer in the Shop database.
	 * If no customer is found, we create a new one and we simulate a logged customer session.
	 * Eventually it will redirect the customer to the "Shipping" step/page of the order process
	 */
	private function _bmlCheckout()
	{
	
	

		$express_url = '';
		$order_url = '';

		$express_url = $this->context->link->getModuleLink('paypalusa', 'billmelater', array('pp_exp_checkout' => 1,));
		$order_url = $this->context->link->getPageLink('order.php', false, null, array('step' => '3'));
		

	
		/* We need to double-check that the token provided by PayPal is the one expected */
		$result = $this->paypal_usa->postToPayPal('GetExpressCheckoutDetails', '&TOKEN='.urlencode(Tools::getValue('token')));
	

		if ((Tools::strtoupper($result['ACK']) == 'SUCCESS' || Tools::strtoupper($result['ACK']) == 'SUCCESSWITHWARNING') && $result['TOKEN'] == Tools::getValue('token') && $result['PAYERID'] == Tools::getValue('PayerID'))
		{
			/* Checks if a customer already exists for this e-mail address */
			if (Validate::isEmail($result['EMAIL']))
			{
				$customer = new Customer();
				$customer->getByEmail($result['EMAIL']);
			}

			/* If the customer does not exist yet, create a new one */
			if (!Validate::isLoadedObject($customer))
			{
				$customer = new Customer();
				$customer->email = $result['EMAIL'];
				$customer->firstname = $result['FIRSTNAME'];
				$customer->lastname = $result['LASTNAME'];
				$customer->passwd = Tools::encrypt(Tools::passwdGen());
				$customer->add();
			}
		
			/* Look for an existing PayPal address for this customer */
			$addresses = $customer->getAddresses((int)Configuration::get('PS_LANG_DEFAULT'));
			foreach ($addresses as $address)
				if ($address['alias'] == 'PayPal')
				{
					$id_address = (int)$address['id_address'];
					break;
				}

			/* Create or update a PayPal address for this customer */
			$address = new Address(isset($id_address) ? (int)$id_address : 0);
			$address->id_customer = (int)$customer->id;
			$address->id_country = (int)Country::getByIso($result['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE']);
			$address->id_state = (int)State::getIdByIso($result['PAYMENTREQUEST_0_SHIPTOSTATE'], (int)$address->id_country);
			$address->alias = 'PayPal';
			$address->lastname = Tools::substr($result['PAYMENTREQUEST_0_SHIPTONAME'], 0, strpos($result['PAYMENTREQUEST_0_SHIPTONAME'], ' '));
			$address->firstname = Tools::substr($result['PAYMENTREQUEST_0_SHIPTONAME'], strpos($result['PAYMENTREQUEST_0_SHIPTONAME'], ' '), Tools::strlen($result['PAYMENTREQUEST_0_SHIPTONAME']) - Tools::strlen($address->lastname));
			$address->address1 = $result['PAYMENTREQUEST_0_SHIPTOSTREET'];
			if (!empty($result['PAYMENTREQUEST_0_SHIPTOSTREET2'] ))
				$address->address2 = $result['PAYMENTREQUEST_0_SHIPTOSTREET2'];
			$address->city = $result['PAYMENTREQUEST_0_SHIPTOCITY'];
			$address->postcode = $result['PAYMENTREQUEST_0_SHIPTOZIP'];
			$address->save();

			/* Update the cart billing and delivery addresses */
			$this->context->cart->id_address_delivery = (int)$address->id;
			$this->context->cart->id_address_invoice = (int)$address->id;
			$this->context->cart->update();
	
			/* Update the customer cookie to simulate a logged-in session */
			$this->context->cookie->id_customer = (int)$customer->id;
			$this->context->cookie->customer_lastname = $customer->lastname;
			$this->context->cookie->customer_firstname = $customer->firstname;
		
			$this->context->cookie->passwd = $customer->passwd; 
			$this->context->cookie->email = $customer->email;
			
			$this->context->cookie->is_guest = $customer->isGuest();
			$this->context->cookie->logged = 1;

			/* Save the Payer ID and Checkout token for later use (during the payment step/page) */
			$this->context->cookie->paypal_bml_checkout_token = $result['TOKEN'];
			$this->context->cookie->paypal_bml_checkout_payer_id = $result['PAYERID'];

			Module::hookExec('authentication');

			/* Redirect the use to the "Shipping" step/page of the order process */
			Tools::redirectLink($order_url);
			exit;
		}
		else
		{
			foreach ($result as $key => $val)
				$result[$key] = urldecode($val);

			$this->context->smarty->assign('paypal_usa_errors', $result);
			$this->setTemplate('express-checkout-messages.tpl');		
		}
	}

	/**
	 * When the customer has clicked on the PayPal Express Checkout button (on the payment step/page) to complete his/her payment,
	 * this function is verifying the Payer ID and Checkout tokens and confirming the payment to PayPal
	 * Eventually it will create the order and redirect the customer to the Order confirmatione page
	 */
	private function _bmlCheckoutPayment()
	{
		$express_url = '';
		$order_url = '';
	
		$express_url = $this->context->link->getModuleLink('paypalusa', 'billmelater', array('pp_exp_initial' => 1,));
		$order_url = $this->context->link->getPageLink('order.php', false, null, array('step' => '3'));
		

		/* Verifying the Payer ID and Checkout tokens (stored in the customer cookie during step 2) */
		if (isset($this->context->cookie->paypal_bml_checkout_token) && !empty($this->context->cookie->paypal_bml_checkout_token))
		{
			/* Confirm the payment to PayPal */
			$currency = new Currency((int)$this->context->cart->id_currency);
			$result = $this->paypal_usa->postToPayPal('DoExpressCheckoutPayment', '&TOKEN='.urlencode($this->context->cookie->paypal_bml_checkout_token).'&PAYERID='.urlencode($this->context->cookie->paypal_bml_checkout_payer_id).'&PAYMENTREQUEST_0_PAYMENTACTION=Sale&PAYMENTREQUEST_0_AMT='.$this->context->cart->getOrderTotal(true).'&PAYMENTREQUEST_0_CURRENCYCODE='.urlencode($currency->iso_code).'&IPADDRESS='.urlencode($_SERVER['SERVER_NAME']));

			if (Tools::strtoupper($result['ACK']) == 'SUCCESS' || Tools::strtoupper($result['ACK']) == 'SUCCESSWITHWARNING')
			{
				/* Prepare the order status, in accordance with the response from PayPal */
				if (Tools::strtoupper($result['PAYMENTINFO_0_PAYMENTSTATUS']) == 'COMPLETED')
					$order_status = (int)Configuration::get('PS_OS_PAYMENT');
				elseif (Tools::strtoupper($result['PAYMENTINFO_0_PAYMENTSTATUS']) == 'PENDING')
					$order_status = (int)Configuration::get('PS_OS_PAYPAL');
				else
					$order_status = (int)Configuration::get('PS_OS_ERROR');

				/* Prepare the transaction details that will appear in the Back-office on the order details page */
				$message = 'Transaction ID: '.$result['PAYMENTINFO_0_TRANSACTIONID'].'
				Transaction type: '.$result['PAYMENTINFO_0_TRANSACTIONTYPE'].'
				Payment type: '.$result['PAYMENTINFO_0_PAYMENTTYPE'].'
				Order time: '.$result['PAYMENTINFO_0_ORDERTIME'].'
				Final amount charged: '.$result['PAYMENTINFO_0_AMT'].'
				Currency code: '.$result['PAYMENTINFO_0_CURRENCYCODE'].'
				PayPal fees:  '.$result['PAYMENTINFO_0_FEEAMT'];

				if (isset($result['PAYMENTINFO_0_EXCHANGERATE']) && !empty($result['PAYMENTINFO_0_EXCHANGERATE']))
					$message .= 'Exchange rate: '.$result['PAYMENTINFO_0_EXCHANGERATE'].'
				Settled amount (after conversion): '.$result['PAYMENTINFO_0_SETTLEAMT'];

				$pending_reasons = array(
					'address' => 'Customer did not include a confirmed shipping address and your Payment Receiving Preferences is set such that you want to manually accept or deny each of these payments.',
					'echeck' => 'The payment is pending because it was made by an eCheck that has not yet cleared.',
					'intl' => 'You hold a non-U.S. account and do not have a withdrawal mechanism. You must manually accept or deny this payment from your Account Overview.',
					'multi-currency' => 'You do not have a balance in the currency sent, and you do not have your Payment Receiving Preferences set to automatically convert and accept this payment. You must manually accept or deny this payment.',
					'verify' => 'You are not yet verified, you have to verify your account before you can accept this payment.',
					'other' => 'Unknown, for more information, please contact PayPal customer service.');

				if (isset($result['PAYMENTINFO_0_PENDINGREASON']) && !empty($result['PAYMENTINFO_0_PENDINGREASON']) && isset($pending_reasons[$result['PAYMENTINFO_0_PENDINGREASON']]))
					$message .= "\n".'Pending reason: '.$pending_reasons[$result['PAYMENTINFO_0_PENDINGREASON']];
	
				/* Creating the order */
				$customer = new Customer((int)$this->context->cart->id_customer);
	
				if ($this->paypal_usa->validateOrder((int)$this->context->cart->id, (int)$order_status, (float)$result['PAYMENTINFO_0_AMT'], $this->paypal_usa->displayName, $message, array(), null, false, false))
				{			
					/* Store transaction ID and details */
					$this->paypal_usa->addTransactionId((int)$this->paypal_usa->currentOrder, $result['PAYMENTINFO_0_TRANSACTIONID']);
					$this->paypal_usa->addTransaction('payment', array('source' => 'express', 'id_shop' => (int)$this->context->cart->id_shop, 'id_customer' => (int)$this->context->cart->id_customer, 'id_cart' => (int)$this->context->cart->id,
						'id_order' => (int)$this->paypal_usa->currentOrder, 'id_transaction' => $result['PAYMENTINFO_0_TRANSACTIONID'], 'amount' => $result['PAYMENTINFO_0_AMT'],
						'currency' => $result['PAYMENTINFO_0_CURRENCYCODE'], 'cc_type' => '', 'cc_exp' => '', 'cc_last_digits' => '', 'cvc_check' => 0,
						'fee' => $result['PAYMENTINFO_0_FEEAMT']));

					/* Reset the PayPal's token so the customer will be able to place a new order in the future */
					unset($this->context->cookie->paypal_bml_checkout_token, $this->context->cookie->paypal_bml_checkout_payer_id);
				}

				/* Redirect the customer to the Order confirmation page */
			
				Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$this->context->cart->id.'&id_module='.(int)$this->paypal_usa->id.'&id_order='.(int)$this->paypal_usa->currentOrder.'&key='.$customer->secure_key);

			}
			else
			{
				foreach ($result as $key => $val)
					$result[$key] = urldecode($val);

				/* If PayPal is returning an error code equal to 10486, it means either that:
				 *
				 * - Billing address could not be confirmed
				 * - Transaction exceeds the card limit
				 * - Transaction denied by the card issuer
				 * - The funding source has no funds remaining
				 *
				 * Therefore, we are displaying a new PayPal Express Checkout button and a warning message to the customer
				 * He/she will have to go back to PayPal to select another funding source or resolve the payment error
				 */
				 
				//*bugfix 2013-11-1 clear token if payment got error
				unset($this->context->cookie->paypal_express_checkout_token, $this->context->cookie->paypal_express_checkout_payer_id);
				$this->context->smarty->assign('paypal_usa_action', $express_url);

				$this->context->smarty->assign('paypal_usa_errors', $result);

				$this->setTemplate('express-checkout-messages.tpl');
			
			}
		}
	}

}


