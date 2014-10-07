<?php
/**
* 2014 PAYU LATAM
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
*  @author    PAYU LATAM <sac@payulatam.com>
*  @copyright 2014 PAYU LATAM
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

include(dirname(__FILE__).'/../../../config/config.inc.php');
include(dirname(__FILE__).'/../../../init.php');
include(dirname(__FILE__).'/../payulatam.php');

if (isset($_REQUEST['sign']))
	$signature = $_REQUEST['sign'];
else
	$signature = $_REQUEST['firma'];

if (isset($_REQUEST['merchant_id']))
	$merchant_id = $_REQUEST['merchant_id'];
else
	$merchant_id = $_REQUEST['usuario_id'];

if (isset($_REQUEST['reference_sale']))
	$reference_code = $_REQUEST['reference_sale'];
else
	$reference_code = $_REQUEST['ref_venta'];

if (isset($_REQUEST['value']))
	$value = $_REQUEST['value'];
else
	$value = $_REQUEST['valor'];

if (isset($_REQUEST['currency']))
	$currency = $_REQUEST['currency'];
else
	$currency = $_REQUEST['moneda'];

if (isset($_REQUEST['state_pol']))
	$transaction_state = $_REQUEST['state_pol'];
else
	$transaction_state = $_REQUEST['estado_pol'];


$split = explode('.', $value);
$decimals = $split[1];
if ($decimals % 10 == 0)
	$value = number_format($value, 1, '.', '');

$payulatam = new PayuLatam();
$api_key = Configuration::get('PAYU_LATAM_API_KEY');
$signature_local = $api_key.'~'.$merchant_id.'~'.$reference_code.'~'.$value.'~'.$currency.'~'.$transaction_state;
$signature_md5 = md5($signature_local);

if (isset($_REQUEST['response_code_pol']))
	$pol_response_code = $_REQUEST['response_code_pol'];
else
	$pol_response_code = $_REQUEST['codigo_respuesta_pol'];

$cart = new Cart((int)$reference_code);
if (Tools::strtoupper($signature) == Tools::strtoupper($signature_md5))
{
	$this->context = Context::getContext();
	$state = 'PAYU_OS_FAILED';
	if ($transaction_state == 6 && $pol_response_code == 5)
		$state = 'PAYU_OS_FAILED';
	else if ($transaction_state == 6 && $pol_response_code == 4)
		$state = 'PAYU_OS_REJECTED';
	else if ($transaction_state == 12 && $pol_response_code == 9994)
		$state = 'PAYU_OS_PENDING';
	else if ($transaction_state == 4 && $pol_response_code == 1)
		$state = 'PS_OS_PAYMENT';
	
	if (!Validate::isLoadedObject($cart))
    $errors[] = $this->module->l('Invalid Cart ID');
	else
	{               
		$currency_cart = new Currency((int)$cart->id_currency);
		if ($currency != $currency_cart->iso_code)
			$errors[] = $this->module->l('Invalid Currency ID').' '.($currency.'|'.$currency_cart->iso_code);
		else
		{
			if ($cart->orderExists())
			{
				$order = new Order((int)Order::getOrderByCartId($cart->id));
				
				if (_PS_VERSION_ < '1.5')
				{
					$current_state = $order->getCurrentState();
					if ($current_state != Configuration::get('PS_OS_PAYMENT'))
					{
						$history = new OrderHistory();
						$history->id_order = (int)$order->id;
						$history->changeIdOrderState((int)Configuration::get($state), $order->id);
						$history->addWithemail(true);
					}
				}
				else
				{
					$current_state = $order->current_state;
					if ($current_state != Configuration::get('PS_OS_PAYMENT'))
					{
						$history = new OrderHistory();
						$history->id_order = (int)$order->id;
						$history->changeIdOrderState((int)Configuration::get($state), $order, true);
						$history->addWithemail(true);
					}
				}
			}
			else
			{
				$customer = new Customer((int)$cart->id_customer);
				$this->context->customer = $customer;
				$this->context->currency = $currency_cart;

				$payulatam->validateOrder((int)$cart->id, (int)Configuration::get($state), (float)$cart->getordertotal(true), 'PayU Latam', null, array(), (int)$currency_cart->id, false, $customer->secure_key);
				$order = new Order((int)Order::getOrderByCartId($cart->id));
			}
			if ($state != 'PS_OS_PAYMENT')
			{
				foreach ($order->getProductsDetail() as $product)
					StockAvailable::updateQuantity($product['product_id'], $product['product_attribute_id'], + (int)$product['product_quantity'], $order->id_shop);
			}
		}
	}
}
?>
