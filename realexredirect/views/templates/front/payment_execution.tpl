{*
*  @author Coccinet <web@coccinet.com>
*  @copyright  2007-2014 Coccinet
*}
{capture name=path}{l s='Order Payment' mod='realexredirect'}{/capture}

<h3>{l s='Order summary' mod='realexredirect'}</h3>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
	<p class="warning">{l s='Your shopping cart is empty.' mod='realexredirect'}</p>
{else}

{if isset($error)}
	<p>{$error|escape:'htmlall':'UTF-8'}</p>
{/if}
<p>
    <strong>{l s='You have chosen to pay by Credit or Debit card.' mod='realexredirect'}</strong>
</p>
<p>
	{l s='The total amount of your order is' mod='realexredirect'}
	<span id="amount" class="price">{displayPrice price=$total}</span>
	{if $use_taxes == 1}
		{l s='(tax incl.)' mod='realexredirect'}
	{/if}
</p>

{if $realvault=="1" && $payer_exists=="1"}
<div class="bloc_registered_card">
	<h4>{l s='Registered card' mod='realexredirect'}</h4>
	{if !empty($error)} <br/><span class="error">{$error|escape:'htmlall':'UTF-8'}</span><br/><br/>{/if}
	{if !empty($input_registered)}
	{$input_registered|escape:'':'UTF-8'}
	{else}
	{l s='No card registered' mod='realexredirect'}
	{/if}
</div>
{/if}

<div class="bloc_new_card">
	<form action="{$submit_new|escape:'htmlall':'UTF-8'}" method="post">
		<h4>{l s='New card' mod='realexredirect'}</h4>
		{l s='Please select your card type' mod='realexredirect'}<br/> 
		<select name='ACCOUNT'>
			{foreach from=$selectAccount item=account}
				<option value='{$account['account']|escape:'htmlall':'UTF-8'}'>
					{if $account['card']=="MC"}
						MASTERCARD
						{elseif $account['card']=="AMEX"}
						AMERICAN EXPRESS
						{else}
						{$account['card']|escape:'htmlall':'UTF-8'}
					{/if}
					
				</option>
			{/foreach}
		</select>
		{$input_new|escape:'':'UTF-8'}
	</form>
</div>

<div style="padding-top:10px; padding-bottom:10px">
<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'htmlall':'UTF-8'}" class="button_large">{l s='Other payment methods' mod='realexredirect'}</a>
</div>
{/if}
