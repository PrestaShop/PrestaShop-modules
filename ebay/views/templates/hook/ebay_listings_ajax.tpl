<table class="table" cellpadding="0" cellspacing="0">
	<tr>
		<th>{l s='Id product' mod='ebay'}</th>
		<th>{l s='Quantity' mod='ebay'}</th>
		<th>{l s='Product on Prestashop' mod='ebay'}</th>
		<th>{l s='Product on eBay (reference)' mod='ebay'}</th>
	</tr>
	{if $products_ebay_listings}
		{foreach from=$products_ebay_listings item=product name=loop}
			<tr class="row_hover{if $smarty.foreach.loop.index % 2} alt_row{/if}">
				<td style="text-align:center">{$product.id_product}</td>
				<td style="text-align:center">{$product.quantity}</td>
				<td><a href="{$product.link}">{$product.prestashop_title}</a></td>
				<td><a href="{$product.link_ebay}">{$product.ebay_title} ({$product.reference_ebay})</a></td>
			</tr>
		{/foreach}
	{/if}
</table>