<fieldset>
	{if $img_stats}
		<center><img src="{$path}{$img_stats}" alt="eBay stats"/></center><br />
	{/if}
	<u><a href="{l s='http://pages.ebay.fr/professionnels/index.html' mod='ebay' }" target="_blank">{l s='Click here to learn more about business selling on eBay' mod='ebay'}</a></u>
</fieldset>
<br />  
	
<fieldset>
	<legend><img src="{$path}logo.gif" alt="" />{l s='eBay Module Status' mod='ebay'}</legend>
	<div style="float: left; width: 45%">
	{if empty($alert)}
		<img src="../modules/ebay/views/img/valid.png" /><strong>{l s='eBay Module is configured and online!' mod='ebay'}</strong>
		{if $is_version_one_dot_five}
			{if $is_version_one_dot_five_dot_one and !$multishop}
				<br/><img src="../modules/ebay/views/img/warn.png" /><strong>{l s='You\'re using version 1.5.1 of PrestaShop. We invite you to upgrade to version 1.5.2  so you can use the eBay module properly.' mod='ebay'}</strong>
				<br/><strong>{l s='Please synchronize your eBay sales in your Prestashop front office' mod='ebay'}</strong>
			{elseif $multishop}
				<br/><strong>{l s='The eBay module does not support multishop. Stock and categories will be sent from the default Prestashop store' mod='ebay'}</strong>				
			{/if}
		{/if}
	{else}
		<img src="../modules/ebay/views/img/warn.png" /><strong>{l s='Please complete the following settings to configure the module' mod='ebay'}</strong>
		<br />{if in_array('registration', $alert)}<img src="../modules/ebay/views/img/warn.png" />{else}<img src="../modules/ebay/views/img/valid.png" />{/if} 1) {l s='Register the module on eBay' mod='ebay'}
		<br />{if in_array('allowurlfopen', $alert)}<img src="../modules/ebay/views/img/warn.png" />{else}<img src="../modules/ebay/views/img/valid.png" />{/if} 2) {l s='Allow url fopen' mod='ebay'}
		<br />{if in_array('curl', $alert)}<img src="../modules/ebay/views/img/warn.png" />{else}<img src="../modules/ebay/views/img/valid.png" />{/if} 3) {l s='Enable cURL' mod='ebay'}
		<br />{if in_array('SellerBusinessType', $alert)}<img src="../modules/ebay/views/img/warn.png" />{else}<img src="../modules/ebay/views/img/valid.png" />{/if} 4) {l s='Please register an eBay business seller account to configure the application' mod='ebay'}
	{/if}

	</div><div style="float: right; width: 45%">{$prestashop_content}<br>{l s='Connection to eBay.' mod='ebay'}{$site_extension}</div>
</fieldset><div class="clear">&nbsp;</div>