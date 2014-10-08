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

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/payulatam.php');

$payulatam = new PayuLatam();

$cart = Context::getContext()->cart;
$customer = Context::getContext()->customer;
$billing_address = new Address(Context::getContext()->cart->id_address_invoice);
$billing_address->country = new Country($billing_address->id_country);
$delivery_address = new Address(Context::getContext()->cart->id_address_delivery);
$delivery_address->country = new Country($delivery_address->id_country);
$products = $cart->getProducts();
$cart_details = $cart->getSummaryDetails(null, true);

$description = '';
foreach ($products as $product)
	$description .= $product['name'].',';

$currency = new Currency((int)$cart->id_currency);

$test = 0;
$gateway_url = 'https://gateway.payulatam.com/ppp-web-gateway';
if (Configuration::get('PAYU_LATAM_TEST') == 'true')
{
	$test = 1;
	$gateway_url = 'https://stg.gateway.payulatam.com/ppp-web-gateway';
}

if (!Validate::isLoadedObject($customer) || !Validate::isLoadedObject($billing_address) && !Validate::isLoadedObject($currency))
{
	Logger::addLog('Issue loading customer, address and/or currency data');
	die('An unrecoverable error occured while retrieving you data');
}

$signature = md5(Configuration::get('PAYU_LATAM_API_KEY').'~'.Configuration::get('PAYU_LATAM_MERCHANT_ID').'~'.(int)$cart->id.'~'.
$cart->getordertotal(true).'~'.$currency->iso_code);

if ($cart_details['total_tax'] != 0)
	$base = $cart_details['total_price_without_tax'] - $cart_details['total_shipping_tax_exc'];
else
	$base = 0;

if (Configuration::get('PS_SSL_ENABLED') || (!empty($_SERVER['HTTPS']) && Tools::strtolower($_SERVER['HTTPS']) != 'off'))
{
	if (method_exists('Tools', 'getShopDomainSsl'))
		$url = 'https://'.Tools::getShopDomainSsl().__PS_BASE_URI__.'/modules/'.$payulatam->name.'/';
	else
		$url = 'https://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/'.$payulatam->name.'/';
}
else
	$url = 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'/modules/'.$payulatam->name.'/';

?>

<center>
	<img src="<?php echo $url; ?>img/logo.png" height="50" width="135"/>
	</br>
	<?php echo $payulatam->l('You will redirect to gateway PayU Latam'); ?>
</center>

<?php
	if (_PS_VERSION_ < '1.5')
		$response_url = 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/payulatam/pages/response.php';
	else
		$response_url = 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'index.php?fc=module&module=payulatam&controller=response';

	$confirmation_url = 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.
	'modules/payulatam/pages/confirmation.php';
?>

<form class="md-form" id="payu_latam_form" name="payu_latam_form" method="post" action="<?php echo Tools::safeOutput($gateway_url); ?>">
	<input type="hidden" name="merchantId" id="merchantId" value="<?php echo Tools::safeOutput(Configuration::get('PAYU_LATAM_MERCHANT_ID')); ?>" />
	<input type="hidden" name="referenceCode" id="referenceCode" value="<?php echo Tools::safeOutput((int)$cart->id); ?>" />
	<input type="hidden" name="description" id="description" value="<?php echo Tools::safeOutput(trim($description, ',')); ?>" />
	<input type="hidden" name="amount" id="amount" value="<?php echo Tools::safeOutput($cart->getordertotal(true)); ?>" />
	<input type="hidden" name="tax" id="tax" value="<?php echo Tools::safeOutput($cart_details['total_tax']); ?>" />
	<input type="hidden" name="taxReturnBase" id="taxReturnBase" value="<?php echo Tools::safeOutput($base); ?>" />
	<input type="hidden" name="signature" id="signature" value="<?php echo Tools::safeOutput($signature); ?>" />
	<input type="hidden" name="accountId" id="accountId" value="<?php echo Tools::safeOutput(Configuration::get('PAYU_LATAM_ACCOUNT_ID')); ?>" />
	<input type="hidden" name="currency" id="currency" value="<?php echo Tools::safeOutput($currency->iso_code); ?>" />
	<input type="hidden" name="buyerEmail" id="buyerEmail" value="<?php echo Tools::safeOutput($customer->email); ?>" />
	<input type="hidden" name="test" id="test" value="<?php echo Tools::safeOutput($test); ?>" />
	<input type="hidden" name="extra1" id="extra1" value="<?php echo Tools::safeOutput('Prestashop '._PS_VERSION_); ?>" />
	<input type="hidden" name="responseUrl" id="responseUrl" value="<?php echo Tools::safeOutput($response_url); ?>" />
	<input type="hidden" name="confirmationUrl" id="confirmationUrl" value="<?php echo Tools::safeOutput($confirmation_url); ?>" />
	<input type="hidden" name="payerFullName" id="payerFullName" value="<?php echo Tools::safeOutput($customer->firstname.' '.$customer->lastname); ?>" />
	<input type="hidden" name="billingAddress" id="billingAddress" value="<?php echo Tools::safeOutput($billing_address->address1); ?>" />
	<input type="hidden" name="shippingAddress" id="shippingAddress" value="<?php echo Tools::safeOutput($delivery_address->address1); ?>" />
	<input type="hidden" name="telephone" id="telephone" value="<?php echo Tools::safeOutput($billing_address->phone); ?>" />
	<input type="hidden" name="billingCity" id="billingCity" value="<?php echo Tools::safeOutput($billing_address->city); ?>" />
	<input type="hidden" name="shippingCity" id="shippingCity" value="<?php echo Tools::safeOutput($delivery_address->city); ?>" />
	<input type="hidden" name="billingCountry" id="billingCountry" value="<?php echo Tools::safeOutput($billing_address->country->iso_code); ?>" />
	<input type="hidden" name="shippingCountry" id="shippingCountry" value="<?php echo Tools::safeOutput($delivery_address->country->iso_code); ?>" />
</form>

<script type="text/javascript">
	window.onload = function() {
		document.payu_latam_form.submit();
	};
</script>
