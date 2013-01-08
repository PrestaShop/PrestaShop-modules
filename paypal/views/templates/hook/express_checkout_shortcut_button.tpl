<div id="container_express_checkout" style="float:right; margin: 10px 40px 0 0">
	{if isset($use_mobile) && $use_mobile}
		<div style="margin-left:30px">
			<img id="payment_paypal_express_checkout" src="{$base_dir_ssl}modules/paypal/img/logos/express_checkout_mobile/CO_{$PayPal_lang_code}_orange_295x43.png" alt="" />
		</div>
	{else}
		<img id="payment_paypal_express_checkout" src="https://www.paypal.com/{$PayPal_lang_code}/i/btn/btn_xpressCheckout.gif" alt="" />
	{/if}
	{if isset($include_form) && $include_form}
		{include file="$template_dir./express_checkout_shortcut_form.tpl"}
	{/if}
</div>
<div class="clearfix"></div>
