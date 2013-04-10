<div data-role="content" id="content" class="cart">
	{include file="$tpl_dir./errors.tpl"}

	<h2>{l s='Order confirmation' mod='paypal'}</h2>
	
	{assign var='current_step' value='payment'}

	{include file="$tpl_dir./errors.tpl"}

	{$HOOK_ORDER_CONFIRMATION}
	{$HOOK_PAYMENT_RETURN}

	<br />

	{if $order}
		<p>{l s='Total of the transaction (taxes incl.) :' mod='paypal'} <span class="bold">{$price}</span></p>
		<p>{l s='Your order ID is :' mod='paypal'} <span class="bold">{$order.id_order}</span></p>
		<p>{l s='Your PayPal transaction ID is :' mod='paypal'} <span class="bold">{$order.id_transaction}</span></p>
	{/if}
	
	<br />
	
	{if !$is_guest}
		<a href="{$link->getPageLink('index', true)}" data-role="button" data-theme="a" data-icon="back" data-ajax="false">{l s='Continue shopping'}</a>
	{else}
		<ul data-role="listview" data-inset="true" id="list_myaccount">
			<li data-theme="a" data-icon="check">
				<a href="{$link->getPageLink('index', true)}" data-ajax="false">{l s='Continue shopping'}</a>
			</li>
			<li data-theme="b" data-icon="back">
				<a href="{$link->getPageLink('history.php', true, NULL, 'step=1&amp;back={$back}')}" data-ajax="false">{l s='Back to orders' mod='paypal'}</a>
			</li>
		</ul>
	{/if}
	<br />
</div><!-- /content -->
