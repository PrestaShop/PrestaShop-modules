<style>
{literal}
#optionOrderBuyster
{
    padding:0;
}
    #optionOrderBuyster li
{
    float:left;
    list-style-type:none;
    width:50%;
}
    #optionOrderBuyster button
{
    width:100%;
}
{/literal}
</style>
<span style='font-weight: bold;'>{l s='Order status' mod='buyster'}</span> : {$content.status_text}<br/><br/>
<span style='font-weight: bold;'>{l s='Order reference' mod='buyster'}</span> : {$content.ref} <br/>
<ul id="optionOrderBuyster">
	<li><button onclick="orderAction('{$content.action}', 'null')" {if !$content.cancel && !$content.refund}disabled="disabled"{/if}>{l s='CANCEL OR REFUND' mod='buyster'}</button></li>
	<li><button onclick="document.getElementById('validation_buyster').style.display=''" {if $content.operation != 'paymentValidation'}style="display:none"{/if} {if !$content.validation}disabled="disabled"{/if}>{l s='VALIDATION' mod='buyster'}</button></li>
</ul>
<br/>
<div id='validation_buyster' style='display:none'>
	<label>{l s='Days before transaction :' mod='buyster'}</label>
	<select id="buyster_payment_days_delayed">
		<option value="0">0</option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option>
	</select> {l s='day(s)' mod='buyster'}
	<input type='button' class='button' value="{l s='Validate' mod='buyster'}" onclick="valideOrder('VALIDATE');"/>
</div>