<style>
#product #paypal-billmelater-checkout-btn-product {
   border: none;
   position: relative;
   top: 16px;
   left: 28px;
}
#product .grid_9 #paypal-billmelater-checkout-btn-product {
  border: none;
  position: relative;
  top: -29px;
  left:3px;

}

</style>

{if ($page_name == 'order' && (!isset($paypal_usa_bml_checkout_no_token) || !$paypal_usa_bml_checkout_no_token) && ((isset($smarty.get.step) && $smarty.get.step > 1) || (isset($smarty.post.step) && $smarty.post.step > 1))) || ($page_name == 'order-opc' && $smarty.get.isPaymentStep == true && isset($paypal_usa_billmelater_checkout_hook_payment))}
	<p class="payment_module">
	<div id="paypal-billmelater-checkout">
		<form id="paypal-billmelater-checkout-form" action="{$paypal_usa_action}" method="post">
			{if $paypal_usa_merchant_country_is_mx}
				<input id="paypal-billmelater-checkout-btn" type="image" name="submit" src="{$module_dir}img/boton_terminar_compra.png" alt="" style="vertical-align: middle; margin-right: 10px;float: left;" /><p style="line-height: 50px; float: left;">{l s='Bill Me Later'}</p>
				<div style="clear: both;"></div>
			{else}	
				<input id="paypal-billmelater-checkout-btn" type="image" name="submit" src="https://www.paypalobjects.com/webstatic/{if $lang_iso == 'en'}en_US{else}{if $lang_iso == 'fr'}fr_CA{else}{if $lang_iso == 'es'}es_ES{else}en_US{/if}{/if}{/if}/btn/btn_bml_SM.png" alt="" style="vertical-align: middle; margin-right: 10px;" /> {l s='Complete your order with PayPal BillMeLater Checkout' mod='paypalusa'}
			{/if}
		</form>
	</div>
</p>
{else}
{if isset($paypal_usa_bml_checkout_no_token) && $paypal_usa_bml_checkout_no_token}<p class="payment_module">{/if}
<div id="paypal-billmelater-checkout" >
	<form id="paypal-billmelater-checkout-form" action="{$paypal_usa_action}" method="post" onsubmit="$('#paypal_billmelater_checkout_id_product_attribute').val($('#idCombination').val());
						$('#paypal_billmelater_checkout_quantity').val($('#quantity_wanted').val());">
		{if $page_name == 'product' && isset($smarty.get.id_product)}
			<input type="hidden" id="paypal_billmelater_checkout_id_product" name="paypal_billmelater_checkout_id_product" value="{$smarty.get.id_product|intval}" />
			<input type="hidden" id="paypal_billmelater_checkout_id_product_attribute" name="paypal_billmelater_checkout_id_product_attribute" value="0" />
			<input type="hidden" id="paypal_billmelater_checkout_quantity" name="paypal_billmelater_checkout_quantity" value="0" />
		{/if}
		{if $paypal_usa_merchant_country_is_mx}
			<input id="paypal-billmelater-checkout-btn-product" type="image" name="submit" src="{if isset($paypal_usa_bml_checkout_no_token) && $paypal_usa_bml_checkout_no_token}{$module_dir}/img/accpmark_tarjdeb_mx.png{else}{$module_dir}/img/bml_checkout_mx.png{/if}" alt="" style="float: left;"/>
		{else}
			<input id="paypal-billmelater-checkout-btn-product" type="image" name="submit" src="{if isset($paypal_usa_bml_checkout_no_token) && $paypal_usa_bml_checkout_no_token}https://www.paypalobjects.com/webstatic/{if $lang_iso == 'en'}en_US{else}{if $lang_iso == 'fr'}fr_CA{else}{if $lang_iso == 'es'}es_ES{else}en_US{/if}{/if}{/if}/btn/btn_bml_SM.png{else}https://www.paypalobjects.com/webstatic/{if $lang_iso == 'en'}en_US{else}{if $lang_iso == 'fr'}fr_CA{else}{if $lang_iso == 'es'}es_ES{else}en_US{/if}{/if}{/if}/btn/btn_bml_SM.png{/if}" alt="" />
		{/if}
	</form>
</div><div style="clear: both;"></div>
{if isset($paypal_usa_bml_checkout_no_token) && $paypal_usa_bml_checkout_no_token}</p>{/if}
{if !isset($paypal_usa_from_error)}
<script type="text/javascript">
	{literal}
		$(document).ready(function()
		{
	{/literal}
	{if $page_name == 'product'}
		{literal}
				$('#paypal-billmelater-checkout-form').insertAfter('#buy_block');
				$('#paypal-billmelater-checkout-btn-product').css('float', 'left');
				$('#paypal-billmelater-checkout-btn-product').css('margin-top', '-30px');
		{/literal}
	{else}
		{if !isset($paypal_usa_bml_checkout_no_token) || !$paypal_usa_bml_checkout_no_token}
			{literal}
					$('#paypal-billmelater-checkout').insertBefore('.cart_navigation .button_large');
					$('#paypal-billmelater-checkout-btn-product').css('float', 'right');
					$('.cart_navigation .button_large').css('margin-left', '5px');
			{/literal}
		{/if}
	{/if}
	{literal}
		});
	{/literal}
</script>
{/if}
{/if}
