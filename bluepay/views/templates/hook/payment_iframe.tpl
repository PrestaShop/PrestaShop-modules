{*
* 2013 BluePay
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
*  @author BluePay Processing, LLC
*  @copyright  2013 BluePay Processing, LLC
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<p class="payment_module">
	{if $allow_stored_payments == 'Yes' && $has_saved_payment_information}
		{$customer = $saved_payment_information.customer_name|escape:'htmlall':'UTF-8'}
	{/if}
	<form name="bluepay_form" id="bluepay_form" action="#" method="post">
		<span style="border: 1px solid #595A5E;display: block;padding: 0.6em;text-decoration: none;margin-left: 0.7em;">
			<a id="click_bluepay" href="#" title="{l s='Pay with BluePay' mod='bluepay'}" style="display: block;text-decoration: none; font-weight: bold;">
				{if $payment_type != 'ACH'}
					{if $cards.visa == 1}<img src="{$module_dir|escape:'htmlall':'UTF-8'}img/visa-small.png" alt="{l s='Visa Logo' mod='bluepay'}" style="vertical-align: middle;" />{/if}
					{if $cards.mastercard == 1}<img src="{$module_dir|escape:'htmlall':'UTF-8'}img/mastercard-small.png" alt="{l s='Mastercard Logo' mod='bluepay'}" style="vertical-align: middle;" />{/if}
					{if $cards.amex == 1}<img src="{$module_dir|escape:'htmlall':'UTF-8'}img/amex.png" alt="{l s='American Express Logo' mod='bluepay'}" style="vertical-align: middle;" />{/if}
					{if $cards.discover == 1}<img src="{$module_dir|escape:'htmlall':'UTF-8'}img/discover.png" alt="{l s='Discover Logo' mod='bluepay'}" style="vertical-align: middle;" />{/if}
					{if $payment_type == 'BOTH'}
						<img src="{$module_dir|escape:'htmlall':'UTF-8'}img/echeck-small.png" alt="{l s='Check Logo' mod='bluepay'}" style="vertical-align: middle;" />
						&nbsp;&nbsp;{l s='Secure credit card/E-check payment with BluePay' mod='bluepay'}
					{else}
						&nbsp;&nbsp;{l s='Secure credit card payment with BluePay' mod='bluepay'}
					{/if}
				{else}
					<img src="{$module_dir|escape:'htmlall':'UTF-8'}img/echeck-small.png" alt="{l s='Check Logo' mod='bluepay'}" style="vertical-align: middle;" />
                                        &nbsp;&nbsp;{l s='Secure E-check payment with BluePay' mod='bluepay'}
				{/if}
			</a>
			<div id="payment_fields" style="display:none">
			<br /><br />
			<p class="error" id="error-text" style="display:none"></p>
			<input type="hidden" name="customer_name" id="customer_name" value="{$customer|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="invoice_id" id="invoice_id" value="{$invoice_id|escape:'htmlall':'UTF-8'}" />
                        <input type="hidden" name="master_id" id="master_id" value="" />
                        <input type="hidden" name="allow_stored_payments" id="allow_stored_payments" value="{$allow_stored_payments|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="require_cvv2" id="require_cvv2" value="{$require_cvv2|escape:'htmlall':'UTF-8'}" />
                        <input type="hidden" name="has_saved_payment_information" id="has_saved_payment_information" value="{$has_saved_payment_information|escape:'htmlall':'UTF-8'}" />
                        <input type="hidden" name="pay_type" id="pay_type" value="{$payment_type|escape:'htmlall':'UTF-8'}" />
                        <input type="hidden" name="expiration_mm" id="expiration_mm" value="" />
                        <input type="hidden" name="expiration_yy" id="expiration_yy" value="" />
                        <input type="hidden" name="cc_type" id="cc_type" value="" />
			<input type="hidden" name="cc_visa" id="cc_visa" value="{$cards.visa|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="cc_mc" id="cc_mc" value="{$cards.mastercard|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="cc_amex" id="cc_amex" value="{$cards.amex|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="cc_discover" id="cc_discover" value="{$cards.discover|escape:'htmlall':'UTF-8'}" />
			{if $display_logo == 'Yes'}
				<div id="left_sidebar" style="width: 136px; height: 260px; float: left; padding-top:40px; padding-right: 20px; border-right: 1px solid #DDD;">
					<img src="{$module_dir|escape:'htmlall':'UTF-8'}img/bluepay2.gif" alt="secure payment" />
				</div>
			{/if}
				<div id="container"></div>
				<br />	
				<input type="button" id="submit_payment" value="{l s='Process Order' mod='bluepay'}" style="margin-left: 129px; padding-left: 25px; padding-right: 25px; float: left;" class="button" />
				<br class="clear" /><br /><br />
				</div>
		</span>
	</form>
</p>
{literal}
<script>
	$(document).ready(function () {
		var https = "https";
		iframeSocket(https + "://secure.bluepay.com/interfaces/shpf?SHPF_FORM_ID=ps&USE_CVV2="+($('#require_cvv2').val())+
			"&KEY="+{/literal}'{$secret_key|escape:'htmlall':'UTF-8'}'{literal}+
			"&MERCHANT="+{/literal}'{$account_id|escape:'htmlall':'UTF-8'}'{literal}+
			"&TRANSACTION_TYPE="+{/literal}'{$transaction_type|escape:'htmlall':'UTF-8'}'{literal}+
			"&PAY_TYPES="+{/literal}'{$payment_type|escape:'htmlall':'UTF-8'}'{literal}+
			"&INVOICE_ID="+{/literal}'{$invoice_id|escape:'htmlall':'UTF-8'}'{literal}+
			"&AMOUNT="+{/literal}'{$total_price|escape:'htmlall':'UTF-8'}'{literal}+
			"&MODE="+{/literal}'{$mode|escape:'htmlall':'UTF-8'}'{literal}+
			"&CC_VISA="+($('#cc_visa').val())+
			"&CC_MC="+($('#cc_mc').val())+
			"&CC_AMEX="+($('#cc_amex').val())+
			"&CC_DISC="+($('#cc_discover').val())+
			"&STORED_PAYMENTS="+{/literal}'{$allow_stored_payments|escape:'htmlall':'UTF-8'}'{literal}+
			"&STORED_PAYMENT_ACCOUNT="+{/literal}'{$saved_payment_information.masked_payment_account|escape:'htmlall':'UTF-8'}'{literal}+
			"&STORED_PAYMENT_TYPE="+{/literal}'{$saved_payment_information.payment_type|escape:'htmlall':'UTF-8'}'{literal}+
			"&STORED_CARD_TYPE="+{/literal}'{$saved_payment_information.card_type|escape:'htmlall':'UTF-8'}'{literal}+
			"&STORED_CARD_MM="+{/literal}'{$saved_payment_information.expiration_date|substr:0:2|escape:'htmlall':'UTF-8'}'{literal}+
			"&STORED_CARD_YY="+{/literal}'{$saved_payment_information.expiration_date|substr:2:4|escape:'htmlall':'UTF-8'}'{literal}+
			"&MASTERID="+{/literal}'{$saved_payment_information.bluepay_customer_id|escape:'htmlall':'UTF-8'}'{literal}+
			"&NAME="+($('#customer_name').val())+
			"&ADDR1="+{/literal}'{$customer_address|escape:'htmlall':'UTF-8'}'{literal}+
			"&CITY="+{/literal}'{$customer_city|escape:'htmlall':'UTF-8'}'{literal}+
			"&STATE="+{/literal}'{$customer_state|escape:'htmlall':'UTF-8'}'{literal}+
			"&ZIPCODE="+{/literal}'{$customer_zip|escape:'htmlall':'UTF-8'}'{literal}+
			"&COUNTRY="+{/literal}'{$customer_country|escape:'htmlall':'UTF-8'}'{literal}+
			"&EMAIL="+{/literal}'{$customer_email|escape:'htmlall':'UTF-8'}'{literal}+
			"&COMMENT="+{/literal}'{$cart|escape:'htmlall':'UTF-8'}'{literal});
	});
</script>
<style>
	iframe {
		overflow: hidden;
                margin: 5px 20px;
		padding: 0px;
                width: 340px;
                height: 280px;
            }
</style>
{/literal}
