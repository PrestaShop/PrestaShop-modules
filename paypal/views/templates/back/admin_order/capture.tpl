<br />
<fieldset {if isset($ps_version) && ($ps_version < '1.5')}style="width: 400px"{/if}>
	<legend><img src="{$base_url}modules/{$module_name}/logo.gif" alt="" />{l s='PayPal Capture' mod='paypal'}</legend>
	<p><b>{l s='Information:' mod='paypal'}</b> {l s='Funds ready to be captured before shipping' mod='paypal'}</p>
	<form method="post" action="{$smarty.server.REQUEST_URI|escape:htmlall}">
		<input type="hidden" name="id_order" value="{$params.id_order}" />
		<p class="center"><input type="submit" class="button" name="submitPayPalCapture" value="{l s='Get the money' mod='paypal'}" /></p>
	</form>
</fieldset>
