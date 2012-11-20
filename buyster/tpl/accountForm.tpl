<style>
{literal}
#configFormAccount label
{
	font-size:13px;
}
{/literal}
</style>
<p style="font-size:15px">{l s='The following parameters were provided to you by Buyster' mod='buyster'}. {l s='If you are not yet registered, click ' mod='buyster'} <a style="color:blue;text-decoration:underline" href="http://buyster.fr/solution-de-paiement-en-ligne-securisee-par-mobile-pour-votre-e-commerce?format=Pro">{l s='here' mod='buyster'}</a></p>
<form action="index.php?tab={$global.tab}&configure={$global.configure}&token={$global.token}&tab_module={$global.tab_module}&module_name={$global.module_name}&id_tab=2&section=account" method="post" class="form" id="configFormAccount">
	<fieldset style="border: 0px;">
		<h4>{l s='Buyster Account' mod='buyster'} :</h4>
		<label>{l s='Buyster MerchantID' mod='buyster'} : </label>
		<div class="margin-form"><input type="text" size="20" name="buyster_payment_id" value="{$varAccount.login}" /></div>
		<label>{l s='Password' mod='buyster'} : </label>
		<div class="margin-form"><input type="password" size="20" name="buyster_payment_password" value="{$varAccount.password}" /></div>
		<label>{l s='Signature' mod='buyster'} : </label>
		<div class="margin-form"><input type="text" size="20" name="buyster_payment_signature" value="{$varAccount.account}" /><br/><br/><span style='font-size:15px;color:black'>{l s='To obtain your signature, connect to your extranet and go to your Administration tab.' mod='buyster'}</span></div>
	</fieldset>
	<div class="margin-form"><input class="button" name="submitSave" type="submit" value={l s='Save' mod='buyster'}></div>
</form>
