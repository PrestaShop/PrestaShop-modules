<?php
/*
* 2007-2012 PrestaShop
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
*  @copyright  2007-2012 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include_once(dirname(__FILE__).'/../../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../../init.php');

include_once(_PS_MODULE_DIR_.'paypal/paypal.php');

define('TIMEOUT', 15);

define('INVALID', 'INVALID');
define('VERIFIED', 'VERIFIED');

define('DEBUG_FILE', 'debug.log');

/*
 * Instant payment notification class.
 * (wait for PayPal payment confirmation, then validate order)
 */
class PayPalNotifier extends PayPal
{
	/**
	 * Debugging
	 */
	protected $logger = false;

	public function __construct()
	{
		parent::__construct();
	}

	public function confirmOrder($custom)
	{
		$cart = new Cart((int)$custom['id_cart']);
		$cart_details = $cart->getSummaryDetails(null, true);
		$cart_hash = sha1(serialize($cart->nbProducts()));
		
		$this->context->cart = $cart;
		
		$address = new Address((int)$cart->id_address_invoice);
		$this->context->country = new Country((int)$address->id_country);
		$this->context->customer = new Customer((int)$cart->id_customer);
		$this->context->language = new Language((int)$cart->id_lang);
		$this->context->currency = new Currency((int)$cart->id_currency);
		
		if (isset($cart->id_shop))
			$this->context->shop = new Shop($cart->id_shop);

		// Debug only
		$this->createLog($cart->getProducts(true));

		$mc_gross = Tools::getValue('mc_gross');
		$total_price = Tools::ps_round($cart_details['total_price'], 2);

		$message = null;
		$result = $this->verify();

		if (strcmp($result, VERIFIED) == 0)
		{
			if ($mc_gross != $total_price)
			{
				$payment = (int)Configuration::get('PS_OS_ERROR');
				$message = $this->l('Price payed on paypal is not the same that on PrestaShop.').'<br />';
			}
			elseif ($custom['hash'] != $cart_hash)
			{
				$payment = (int)Configuration::get('PS_OS_ERROR');
				$message = $this->l('Cart changed, please retry.').'<br />';
			}
			else
			{
				$payment = (int)Configuration::get('PS_OS_PAYMENT');
				$message = $this->l('Payment accepted.').'<br />';
			}

			$customer = new Customer((int)$cart->id_customer);
			$id_order = (int)Order::getOrderByCartId((int)$cart->id);
			$transaction = PayPalOrder::getTransactionDetails(false);
			
			if (_PS_VERSION_ < '1.5')
				$shop = false;
			else
			{
				$shop_id = $this->context->shop->id;
				$shop = new Shop($shop_id);
			}
			
			$this->validateOrder($cart->id, $payment, $total_price, $this->displayName, $message, $transaction, $cart->id_currency, false, $customer->secure_key, $shop);
		}
	}

	public function verify()
	{
		$url = $this->getPaypalStandardUrl();
		$array = array_merge(array('cmd' => '_notify-validate'), $_POST);
		$data = http_build_query($array, '', '&');

		/* Get confirmation from PayPal */
		return $this->fetchResponse($url, $data);
	}

	public function fetchResponse($url, $data)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, TIMEOUT);

		$result = curl_exec($ch);

		curl_close($ch);

		return $result;
	}

	/**
	 * Integral Evolution log file generation
	 */
	public function createLog($data, $file = false)
	{
		if ($this->logger == false)
			return;
	
		ob_start();
		var_dump($data);
		$buff = ob_get_contents();
		ob_end_clean();

		$file = $file ? $file : 'log.txt';
		$handle = @fopen($file, 'w+');
		fwrite($handle, $buff);
		fclose($handle);
	}

}

if ($custom = Tools::getValue('custom'))
{
	$notifier = new PayPalNotifier();
	$result = Tools::jsonDecode($custom, true);
	$notifier->confirmOrder($result);
}
