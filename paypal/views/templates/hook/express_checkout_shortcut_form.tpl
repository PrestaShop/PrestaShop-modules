<form id="paypal_payment_form" action="{$base_dir_ssl}modules/paypal/express_checkout/payment.php" data-ajax="false" title="{l s='Pay with PayPal' mod='paypal'}" method="post" data-ajax="false">
	{if isset($smarty.get.id_product)}<input type="hidden" name="id_product" value="{$smarty.get.id_product}" />{/if}
	
	<!-- Change dynamicaly when the form is submitted -->
	<input type="hidden" name="quantity" value="1" />
	<input type="hidden" name="id_p_attr" value="" />
	<input type="hidden" name="express_checkout" value="{$PayPal_payment_type}"/>
	<input type="hidden" name="current_shop_url" value="{$PayPal_current_page}" />
	<input type="hidden" name="bn" value="{$PayPal_tracking_code}" />
</form>
