<link rel="stylesheet" type="text/css" href="{$module_url}views/css/ebay.css" />
<table border="0" cellpadding="0" cellspacing="0" class="ProductPrestashop">
<tbody>
	<tr class="headerProductPrestashop">
		<td class="headerLeftProductPrestashop"><img src="{$shop_logo}" alt="{$shop_name}" /></td>
		<td class="headerCenterProductPrestashop">{literal}{SLOGAN}{/literal}</td>
		<td class="headerRightProductPrestashop">
			<a href="http://feedback.ebay.fr/ws/eBayISAPI.dll?ViewFeedback2&userid={literal}{EBAY_IDENTIFIER}{/literal}&sspagename=VIP:feedback&ftab=FeedbackAsSeller">{l s='See our ratings' mod='ebay'} <img src="{$module_url}views/img/stats.png" alt="{l s='See our ratings' mod='ebay'}" border="0" /></a><br />
			<a href="http://my.ebay.fr/ws/eBayISAPI.dll?AcceptSavedSeller&sellerid={literal}{EBAY_IDENTIFIER}{/literal}&ssPageName=STRK:MEFS:ADDSTR">{l s='Add this shop to my favorites' mod='ebay'} <img src="{$module_url}views/img/favorite.png" alt="{l s='Add this shop to my favorites' mod='ebay'}" border="0" /></a><br /><br />
			<form action="http://stores.ebay.fr/{literal}{EBAY_SHOP}{/literal}/_i.html" method="GET">
				<input type="text" name="_nkw" class="headerSearchProductPrestashop" value="" />
				<input type="hidden" name="_armrs" value="1" />
				<input type="hidden" name="_from" value="" />
				<input type="hidden" name="_ipg" value="" />
				<input type="hidden" name="_sasi" value="1" />
			</form>
		</td>
	</tr>
	<tr>
		<td class="leftProductPrestashop">
			<br />{literal}{MAIN_IMAGE}{/literal}<br />
			{literal}{MEDIUM_IMAGE_1} {MEDIUM_IMAGE_2} {MEDIUM_IMAGE_3}{/literal}<br clear="left" /><br />
		</td>
		<td colspan="2" class="bodyProductPrestashop">
			<br /><br />			
			<span class="bodyNameProductPrestashop">{literal}{PRODUCT_NAME}{/literal}</span><br /><br />
			<span class="bodyPriceProductPrestashop">{literal}{PRODUCT_PRICE} {PRODUCT_PRICE_DISCOUNT}{/literal}</span><br /><br />
			{l s='Availability' mod='ebay'}: <b>{l s='in stock' mod='ebay'}</b><br /><br /><br />

			<span class="bodyDescriptionProductPrestashop">{literal}{DESCRIPTION}{/literal}</span>
		</td>
	</tr>
	<tr class="footerProductPrestashop"><td colspan="3">&nbsp;</td></tr>
</tbody>
</table>
