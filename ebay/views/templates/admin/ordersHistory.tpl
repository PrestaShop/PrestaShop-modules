<h2>{l s='Review imported orders' mod='ebay'} :</h2>
<p>
	<b>{$date_last_import}</b>
</p>
<br />
<br />
<h2>{l s='Orders History' mod='ebay'} :</h2>

{if count($orders)}
	{foreach from=$orders item="order"}
		<style>
			{literal}
			.orderImportTd1 {border-right:1px solid #000}
			.orderImportTd2 {border-right:1px solid #000;border-top:1px solid #000}
			.orderImportTd3 {border-top:1px solid #000}
			{/literal}
		</style>
		<p>
		<b>{l s='Order Ref eBay' mod='ebay'} :</b> 
		{if $order->getIdOrderRef()}{$order->getIdOrderRef()}{/if}<br />
		<b>{l s='Id Order Seller' mod='ebay'} :</b> 
		{if $order->getIdOrderSeller()}{$order->getIdOrderSeller()}{/if}<br />
		<b>{l s='Amount' mod='ebay'} :</b> 
		{if $order->getAmount()}{$order->getAmount()}{/if}<br />
		<b>{l s='Status' mod='ebay'} :</b> 
		{if $order->getStatus()}{$order->getStatus()}{/if}<br />
		<b>{l s='Date' mod='ebay'} :</b>
		{if $order->getDate()}{$order->getDate()}{/if}<br />
		<b>{l s='E-mail' mod='ebay'} :</b>
		{if $order->getEmail()}{$order->getEmail()}{/if}<br />
		<b>{l s='Products' mod='ebay'} :</b><br />
		{if $order->getProducts() && count($order->getProducts()) > 0}
			<table border="0" cellpadding="4" cellspacing="0">
				<tr>
					<td class="orderImportTd1">
						<b>{l s='Id Product' mod='ebay'}</b>
					</td>
					<td class="orderImportTd1">
						<b>{l s='Id Product Attribute' mod='ebay'}</b>
					</td>
					<td class="orderImportTd1">
						<b>{l s='Quantity' mod='ebay'}</b>
					</td>
					<td>
						<b>{l s='Price' mod='ebay'}</b>
					</td>
				</tr>
				{foreach from=$order->getProducts() item="product"}
					<tr>
						<td class="orderImportTd2">{$product.id_product}</td>
						<td class="orderImportTd2">{$product.id_product}</td>
						<td class="orderImportTd2">{$product.quantity}</td>
						<td class="orderImportTd3">{$product.price}</td>
					</tr>
				{/foreach}
			</table>
		{/if}
		{if count($order->getErrorMessages()) }
			<b>{l s='Status Import' mod='ebay'} :</b> KO<br />
			<b>{l s='Failure details' mod='ebay'} :</b><br />
			{foreach from=$order->getErrorMessages() item="error"}
				{$error}t<br />
			{/foreach}
		{else}
			<b>{l s='Status Import' mod='ebay'} :</b> OK
		{/if}
		</p><br />
	{/foreach}
{/if}		
