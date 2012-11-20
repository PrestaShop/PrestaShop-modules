{if $status == 'ok'}
	<p>{l s='Your order on' mod='buyster'} <span class="bold">{$shop_name}</span> {l s='is complete.' mod='buyster'}
		<br /><br />
		{l s='You have just payed the exact amount of ' mod='buyster'}
		<br /><br />- {l s='an amount of' mod='buyster'} <span class="price">{$total_payed}</span>
		
		<br /><br />{l s='An e-mail has been sent to you with this information.' mod='buyster'}
		<br /><br /><span class="bold">{l s='Your order will be sent as soon as we receive we can.' mod='buyster'}</span>
		<br /><br />{l s='For any questions or for further information, please contact our' mod='buyster'} <a href="{$link->getPageLink('contact-form.php', true)}">{l s='customer support' mod='buyster'}</a>.
	</p>
{else}
	<p class="warning">
		{l s='We noticed a problem with your order. If you think this is an error, you can contact our' mod='buyster'} 
		<a href="{$link->getPageLink('contact-form.php', true)}">{l s='customer support' mod='buyster'}</a>.
	</p>
{/if}