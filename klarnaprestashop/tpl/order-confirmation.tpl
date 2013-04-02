{if $klarna_order.valid == 1}
	<div class="conf confirmation">{l s='Congratulations, your payment has been approved and your order has been saved under' mod='cielo'} {l s='the reference' mod='klarnaprestashop'} <b>{$klarna_order.reference|escape:html:'UTF-8'}</b>.</div>
{else}
	<div class="error">{l s='Sorry, unfortunately an error occured during the transaction.' mod='klarnaprestashop'}<br /><br />
		{l s='Feel free to contact us to resolve this issue.' mod='klarnaprestashop'}<br /><br />
		({l s='Your Order\'s Reference:' mod='klarnaprestashop'} <b>{$klarna_order.reference|escape:html:'UTF-8'}</b>)
	</div>
{/if}
