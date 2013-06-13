{*
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
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<hr style="border-top: 1px dotted rgb(204, 204, 204);" />

<iframe name="hss_iframe" width="556px" height="540px" style="overflow: hidden; border: none" class="payment_module"></iframe>

<form style="display: none" target="hss_iframe" id="paypal_hss_iframe" name="form_iframe" method="post" action="{$action_url}">
	<input type="hidden" name="cmd" value="_hosted-payment" />

	<input type="hidden" name="billing_first_name" value="{$billing_address->firstname}" />
	<input type="hidden" name="billing_last_name" value="{$billing_address->lastname}" />
	<input type="hidden" name="billing_address1" value="{$billing_address->address1}" />
	<input type="hidden" name="billing_address2" value="{$billing_address->address2}" />
	<input type="hidden" name="billing_city" value="{$billing_address->city}" />
	<input type="hidden" name="billing_zip" value="{$billing_address->postcode}" />
	<input type="hidden" name="billing_country" value="{$billing_address->country->iso_code}" />
	{if ($billing_address->id_state != 0)}
		<input type="hidden" name="billing_state" value="{$billing_address->state->name}" />
	{/if}
	<input type="hidden" name="first_name" value="{$delivery_address->firstname}" />
	<input type="hidden" name="last_name" value="{$delivery_address->lastname}" />
	<input type="hidden" name="buyer_email" value="{$customer->email}" />
	<input type="hidden" name="address1" value="{$delivery_address->address1}" />
	<input type="hidden" name="address2" value="{$delivery_address->address2}" />
	<input type="hidden" name="city" value="{$delivery_address->city}" />
	<input type="hidden" name="zip" value="{$delivery_address->postcode}" />
	<input type="hidden" name="country" value="{$delivery_address->country->iso_code}" />
	{if ($delivery_address->id_state != 0)}
		<input type="hidden" name="billing_state" value="{$delivery_address->state->name}" />
	{/if}

	<input type="hidden" name="address_override" value="true" />
	<input type="hidden" name="showShippingAddress" value="true" />

	<input type="hidden" name="currency_code" value="{$currency->iso_code}" />
	<input type="hidden" name="invoice" value="{$customer->id}_{$time}" />
	<input type="hidden" name="shipping" value="{$shipping}" />
	<input type="hidden" name="tax" value="{$cart_details.total_tax}" />
	<input type="hidden" name="subtotal" value="{$subtotal}" />

	<input type="hidden" name="custom" value="{$custom|escape:'htmlall'}" />
	<input type="hidden" name="notify_url" value="{$notify_url}" />
	<input type="hidden" name="paymentaction" value="sale" />
	<input type="hidden" name="business" value="{$business_account}" />
	<input type="hidden" name="template" value="templateD" />
	<input type="hidden" name="cbt" value="{l s='Return back to the merchant\'s website' mod='paypal'}" />
	<input type="hidden" name="cancel_return" value="{$cancel_return}" />
	<input type="hidden" name="return" value="{$return_url}" />
    <input type="hidden" name="bn" value="{$tracking_code}" />
    <input type="hidden" name="lc" value="{$iso_code}" />
</form>

{literal}
<script type="text/javascript">
	$(document).ready( function() {
		$('#paypal_hss_iframe').submit();
	});
</script>
{/literal}
