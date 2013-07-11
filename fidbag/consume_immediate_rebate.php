<?php

require('../../config/config.inc.php');
require_once(dirname(__FILE__)."/class/fidbagUser.php");
require_once(dirname(__FILE__)."/class/fidbagWebService.php");

$amount = (float)Tools::getValue('rebate');
$id_cart = (int)Tools::getValue('cart');

$cart = new Cart($id_cart);
$currency = new Currency((int)$cart->id_currency);
$token = Tools::encrypt(Tools::getValue('customer'));

if ((Tools::getValue('token') !== $token) || ($cart->id_customer != Tools::getValue('customer')))
	die(Tools::jsonEncode(array('error' => true, 'type' => 'user', 'value' => '0')));

$create = true;

unset($_POST['cart']);

/**
 * Get Fid'Bag account information
 **/
$fidbag_user = new FidbagUser($cart->id_customer);
$webService = new FidbagWebService();

$fidbag_user->getFidBagUser();
$return = $webService->action('GetImmediateRebateAmount',
			array(
				'CardNumber' => $fidbag_user->getCardNumber(),
				'MerchantCode' => Configuration::get('FIDBAG_MERCHANT_CODE'),
		)
);

$json_return = Tools::jsonDecode($return->GetImmediateRebateAmountResult);
$max_amount = $json_return->ImmediateRebateAmount;

if (_PS_VERSION_ >= '1.5')
{
	$context = Context::getContext();
	$context->cart = $cart;
	$context->currency = $currency;
}



/**
 * Check for errors
 **/
$cart_total = $cart->getOrderTotal();
$discounts_total = $cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS);
$shipping_total = $cart->getOrderTotal(true, Cart::ONLY_SHIPPING);

if ($amount > (float)$json_return->ImmediateRebateAmount)
{
	$value = $max_amount.' '.$currency->sign;
	die(Tools::jsonEncode(array('error' => true, 'type' => 'amount', 'value' => $value)));
}
elseif ($amount > (float)($cart_total + $discounts_total - $shipping_total))
{
	$value = ($cart_total + $discounts_total - $shipping_total).' '.$currency->sign;
	die(Tools::jsonEncode(array('error' => true, 'type' => 'amount', 'value' => $value)));
}

/**
 * Delete old voucher
 **/
if (_PS_VERSION_ >= '1.5')
	$discounts = $context->cart->getCartRules();
else
	$discounts = $cart->getDiscounts();

if (count($discounts))
{
	foreach ($discounts as $key => $val)
	{
		if (strcmp($val['name'], 'Fid\'Bag') === 0)
		{
			if (_PS_VERSION_ >= '1.5')
				$voucher = new CartRule($val['id_cart_rule']);
			else
				$voucher = new Discount($val['id_discount']);

			$voucher->delete();
		}
	}
}

/**
 * create voucher
 **/
if ($amount > 0)
{	
	if (_PS_VERSION_ >= '1.5')
	{	
		$voucher = new CartRule();
		
		$voucher->free_shipping = false;
		$voucher->reduction_percent = false;
		$voucher->reduction_amount = $amount;
		
		$voucher->name = array();
		$languages = Language::getLanguages(true);
	
		foreach ($languages as $language)
			$voucher->name[$language['id_lang']] = 'Fid\'Bag';
	
		$voucher->description = 'Discount Fid\'Bag';
		$voucher->id_customer = (int)$cart->id_customer;
		$voucher->reduction_currency = (int)$cart->id_currency;
		$voucher->quantity = 1;
		$voucher->quantity_per_user = 1;
		$voucher->cart_rule_restriction = 1;
	
		$voucher->cumulable_reduction = 1;
		$voucher->minimum_amount = (float)$voucher->reduction_amount;
		$voucher->reduction_tax = 1;
		$voucher->active = 1;
		$voucher->cart_display = 1;
		
		$now = time();
		$voucher->date_from = date('Y-m-d H:i:s', $now);
		$voucher->date_to = date('Y-m-d H:i:s', $now + (3600 * 24 * 365.25));
		
		if (!$voucher->validateFieldsLang(false) || !$voucher->add())
			die('0');
	
		$cart->addCartRule($voucher->id);
	}
	else
	{
		$voucher = new Discount();
		$voucher->id_discount_type = 2;
		$voucher->value = $amount;
	
		$languages = Language::getLanguages(true);
	
		$voucher->name = 'Fid\'Bag';
		$voucher->description = 'Discount Fid\'Bag';
		$voucher->id_customer = (int)$cart->id_customer;
		$voucher->id_currency = (int)$cart->id_currency;
		$voucher->quantity = 1;
		$voucher->quantity_per_user = 1;
		$voucher->cumulable = 1;
		$voucher->cumulable_reduction = 1;
		$voucher->minimal = (float)($voucher->value);
		$voucher->include_tax = 1;
		$voucher->active = 1;
		$voucher->cart_display = 1;
		
		$now = time();
		$voucher->date_from = date('Y-m-d H:i:s', $now);
		$voucher->date_to = date('Y-m-d H:i:s', $now + (3600 * 24 * 365.25));
		
		if (!$voucher->validateFieldsLang(false) || !$voucher->add())
			die('0');
	
		$cart->addDiscount($voucher->id);
		Discount::getVouchersToCartDisplay(1, $cart->id_customer);
	}
}

$values = array(
	'total' => Tools::ps_round($cart->getOrderTotal(), $currency->decimals),
	'discount' => $amount,
);

die(Tools::jsonEncode($values));

?>