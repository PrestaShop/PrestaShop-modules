<style>
{literal}
#configFormParameters label
{
	width:252px;
	font-size:15px;
}

#configFormParameters .margin-form 
{
    color: black;
    padding: 0 0 1em 262px;
	font-size:15px;
}
{/literal}
</style>
<script type="text/javascript" src="../modules/{$global.module_name}/js/parameters.js"></script>
<p style="font-size:15px">{l s='Buyster allows you to choose your environment(test or production) depending on whether you want to receive actual payments.' mod='buyster'}<br/>{l s='You can also choose three types of payment below: simple, delay or after validation' mod='buyster'}
</p>
<form action="index.php?tab={$global.tab}&configure={$global.configure}&token={$global.token}&tab_module={$global.tab_module}&module_name={$global.module_name}&id_tab=3&section=parameters" method="post" class="form" id="configFormParameters">
		<fieldset style="border: 0px;">
			<label for="buyster_payment_production">{l s='Environment' mod='buyster'} :</label>
			<div class="margin-form">
				<input type="radio" value="1" name="buyster_payment_production" {if $varParameters.production == 1} checked="checked" {/if} /> <label class="t">{l s='Production' mod='buyster'} </label>{l s='(in this mode, you will receive real payments)' mod='buyster'}<br/><br/>
				<input type="radio" value="0" name="buyster_payment_production" {if $varParameters.production == 0} checked="checked" {/if} /> <label class="t">{l s='Test' mod='buyster'} </label>{l s='(this mode only allows you to test if this Buyster module is working well but you will not receive payments)' mod='buyster'}
			</div>
			<br/>
			<label for="buyster_payment_return_url">{l s='Transaction Type' mod='buyster'} :</label>
			<div class="margin-form">
				<input type="radio"  name="buyster_payment_transaction_type" {if $varParameters.payment == "payment"} checked="checked" {/if} value="payment" onclick="clearAll()"/> <label class="t">{l s='Simple payment' mod='buyster'}</label> {l s='(Transactions are automatically sent in funding during the day)' mod='buyster'}<br/><br/>
				<input type="radio" name="buyster_payment_transaction_type" {if $varParameters.payment == "paymentDelayed"} checked="checked" {/if} value="paymentDelayed" onclick="displayDays('daysDelayed')"/> <label class="t">{l s='Payment delayed' mod='buyster'}</label> {l s='(Transactions are automatically sent in funding after 1 to 6 days after the time frame you choose)' mod='buyster'}<br/><br/>
				<input type="radio" name="buyster_payment_transaction_type" {if $varParameters.payment == "paymentValidation"} checked="checked" {/if} value="paymentValidation" onclick="displayDays('validationDelayed')"/> <label class="t">{l s='payment with validation' mod='buyster'}</label> {l s='(Transactions are automatically sent in funding after your validation in 30 days max)' mod='buyster'}<br/><br/>
			</div>
			<div id="daysDelayed" {if $varParameters.payment != "paymentDelayed"} style="display:none"{/if}>
				<label for="buyster_payment_days_delayed">{l s='Days before transaction' mod='buyster'} :</label>
				<div class="margin-form">
					<select name="buyster_payment_days_delayed" style="width:35px">
						{if $varParameters.daysDelayed}<option selected="selected" value="{$varParameters.daysDelayed}">{$varParameters.daysDelayed}</option>{/if}
						<option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option>
					</select> <label class="t">{l s='day(s)' mod='buyster'}</label>
				</div>
			</div>
			<div id="validationDelayed" {if $varParameters.payment != "paymentValidation"} style="display:none"{/if}>
				<label for="buyster_payment_validation_delayed">{l s='Days to validate a transaction' mod='buyster'} :</label>
				<div class="margin-form">
					<select name="buyster_payment_validation_delayed" style="width:40px">
						{if $varParameters.validationDelayed}<option selected="selected" value="{$varParameters.validationDelayed}">{$varParameters.validationDelayed}</option>
						{else}
						<option selected="selected" value="30">30</option>
						{/if}
						{section name=days start=1 loop=31}
							<option value="{$smarty.section.days.index}">{$smarty.section.days.index}</option>
						{/section}
					</select> <label class="t">{l s='day(s)' mod='buyster'}</label>
				</div>
			</div>
			<!--<label for="buyster_payment_several_payment"></label>	
			<div>
				<input type="checkbox" name="buyster_payment_several_payment" {if $varParameters.severalPayment == "on"} checked="checked" {/if} value="on" onclick="displaySeveral('severalsParameter')"/> {l s='enable your customers to pay in several time' mod='buyster'}<br/>
			</div>
			<br/>
			<div id="severalsParameter" {if $varParameters.severalPayment != "on"} style="display:none"{/if}>
				<label for="buyster_payment_time_payment">{l s='Payment in' mod='buyster'} :</label>
				<div class="margin-form">
					<select name="buyster_payment_time_payment">
						{if $varParameters.timePayment}<option selected="selected" value="{$varParameters.timePayment}">{$varParameters.timePayment}</option>{/if}
							<option value="2">2</option>
							<option value="3">3</option>
					</select> {l s='times' mod='buyster'}
				</div>
				<label for="buyster_payment_period_payment">{l s='Period between two payments' mod='buyster'}:</label>
				<div class="margin-form">
					<select name="buyster_payment_period_payment">
						{if $varParameters.periodPayment}<option selected="selected" value="{$varParameters.periodPayment}">{$varParameters.periodPayment}</option>{/if}
						{section name=days start=0 loop=31}
							<option value="{$smarty.section.days.index}">{$smarty.section.days.index}</option>
						{/section}
					</select> {l s='day(s)' mod='buyster'}
				</div>
				<label for="buyster_payment_initial_amount">{l s='Initial amount' mod='buyster'} :</label>
				<div class="margin-form">
					<input type="text" value="{$varParameters.initAmount}" name="buyster_payment_initial_amount"/>
				</div>
				<label for="buyster_payment_delayed_several">{l s='days before transaction' mod='buyster'} :</label>
				<div class="margin-form">
					<select name="buyster_payment_delayed_several">
						{if $varParameters.daysDelayedSeveral}<option selected="selected" value="{$varParameters.daysDelayedSeveral}">{$varParameters.daysDelayedSeveral}</option>{/if}
						<option value="0">0</option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option>
					</select> {l s='day(s)' mod='buyster'}
				</div>
			</div>-->
		</fieldset>
	<div class="margin-form"><input class="button" name="submitSave" value="{l s='Save' mod='buyster'}" type="submit"></div>
</form>