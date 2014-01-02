{*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<style type="text/css">
	#box_fb
	{
		width: 500px;
		height: 250px;
		-webkit-border-radius: 7px;
		-moz-border-radius: 7px;
		border-radius: 7px;
	}

	#s_country { width: 200px; }
	#s_currency { width: 200px; }
	#rec
	{
		width: 100%;
		color: #1872D3;
		font-family: Verdana, Arial, Helvetica, sans-serif;
		font-size: 12px;
		font-weight: bold;
	}

	#b_submit
	{
		width: 200px;
		height: 30px;
		-webkit-border-radius: 7px;
		-moz-border-radius: 7px;
		border-radius: 7px;
		background-color: #1872D3;
		color: #FFF;
		font-weight: bold;
	}
</style>

<div id="interpay_block_right" class="block products_block">
	<p class="title_block">{l s='SHIP TO:' mod='gointerpay'}</p>
	<div class="block_content">
		({$gointerpay_country_name|escape:htmlall:'UTF-8'}) <input class="exclusive" type="button" value="{l s='CHANGE' mod='gointerpay'}" onclick="$('#interpay_inline').click();" style="margin-top: 10px;" />
	</div>
</div>

<div style="display: none;">
	<div id="interpay_popup" align="center" style="width: 500px;">
		<form name="f1" method="post">
			<table width="90%" border="0" cellspacing="1" cellpadding="1" align="center">
				<tr height="20px" align="center">
					<td colspan="2">
						<h2 style="margin: 0; padding-top: 10px; padding-bottom: 5px;">{l s='Welcome International Customers!' mod='gointerpay'}</h2><br />
						<p style="font-size: 14px; text-align: left; margin-bottom: 0px; padding: 0;">{l s='Choose your ship to and billing currency for your localized shopping experience.' mod='gointerpay'}</p><br />
					</td>
				</tr>
				<tr id="rec" align="left">
					<td>{l s='DESTINATION COUNTRY' mod='gointerpay'}</td>
					<td>{l s='PREFERRED CURRENCY' mod='gointerpay'}</td>  
				</tr>
				<tr align="left">
					<td>
						<select id="interpay_country_code" name="interpay_country_code" style="margin-top: 5px;" onchange="preselectInterpayCurrency();">
							{foreach from=$sqlcountries item=sql_country}
								<option rel="{$sql_country.currency_code|escape:htmlall:'UTF-8'}" value="{$sql_country.country_code|escape:htmlall:'UTF-8'}"{if $sql_country.country_code == $cookie->interpay_country_code}selected="selected"{/if}>{$sql_country.country_name|escape:htmlall:'UTF-8'}</option>
							{/foreach}	
						</select>
					</td>
					<td>
						<select id="interpay_currency_code" name="interpay_currency_code" style="margin-top: 5px;">
							{foreach from=$sqlcurrencies item=sql_currency}
								<option value="{$sql_currency.iso_code|escape:htmlall:'UTF-8'}" onclick="javascript:setCurrency({$sql_currency.id_currency});"{if $sql_currency.iso_code == $cookie->interpay_currency_code}selected="selected"{/if}>{$sql_currency.name|escape:htmlall:'UTF-8'}</option>
							{/foreach}		
						</select>
					</td>
				</tr>
				<tr><td colspan="2"><p style="margin-top: 10px; font-size: 14px;">{l s='Full landed cost in your currency will be shown at checkout.' mod='gointerpay'}</p></td></tr>
				<tr align="center" height="50px">
					<td colspan=2 ><input id="b_submit" type="submit" name="SubmitInterpay" value="{l s='Start Shopping' mod='gointerpay'}" onclick="hid()" /></td>
				</tr>
			</table>
		</form>
	</div>
</div>
<a id="interpay_inline" href="#interpay_popup"></a>

<script type="text/javascript">
	{literal}
		$(document).ready(function() {

			var interpay_not_for_export = Array();
			{/literal}
			{foreach from=$interpay_not_for_export item=itp}
				interpay_not_for_export[{$itp|intval}] = 1;
			{/foreach}
			{literal}
			
			var interpay_not_for_export_text = '<div class="warning" style="margin: 5px 3px;"><small>{/literal}{l s="This product is not available for shipping to the country you selected." js=1 mod="gointerpay"}{literal}</small></div>';
		
			/* Mark products not available for export */
			$('a[rel*=ajax_id_product_]').each(function(index, value) {
				rel = $(value).attr('rel').replace('ajax_id_product_', '');
				if (interpay_not_for_export[rel] && interpay_not_for_export[rel] == 1 && $('#interpay_country_code option:selected').val() != 'US')
					$(value).replaceWith(interpay_not_for_export_text);
			});		
		});
	
		function preselectInterpayCurrency()
		{
			$('#interpay_currency_code option').removeAttr('selected');

			$('#interpay_currency_code option').each(function() {
				if ($(this).val() == $('#interpay_country_code option:selected').attr('rel'))
					$(this).attr('selected', 'selected');
			});
		}

		$('#id_address_delivery').live('change', function()
		{			
			$.ajax({
				type: 'POST',
				url: baseDir + 'modules/gointerpay/states.php?check_address=1&id_address='+parseInt($('#id_address_delivery').val()),
				async: true,
				cache: false,
				dataType : 'html',
				success: function(result)
				{
					$('#interpay_not_supported').hide();

					if (result == 1 || result == -1)
					{
						$('.cart_navigation .exclusive').show();
						$('.cart_navigation .exclusive_interpay').hide();				
					}
					else
					{
						$('.cart_navigation .exclusive').hide();
						if ($('a.exclusive_interpay').length == 0)
							$('.cart_navigation').append("<a style='float:right;clear:right;width:170px;' class='exclusive exclusive_interpay' href='{/literal}{$pathSsl}{literal}payment.php' onclick='return checkAlerts();'>{/literal}{l s='International Checkout' mod='gointerpay'}{literal}</a>");
						$('.cart_navigation .exclusive_interpay').show();
					}
					
					if (result == -1)
						$('#interpay_not_supported').show();
					else
						$('#cart_block_shipping_cost').html("{/literal}{l s='At checkout' mod='gointerpay' js=1}{literal}");
				},
			});
		});

		$(document).ready(function()
		{
			$('#interpay_block_right').prependTo('#right_column');
			$('#interpay_inline').fancybox();
	{/literal}
	{if $interpay_show_popup}
		{literal}
			$('#interpay_inline').click();
		{/literal}
	{/if}
	{if !isset($cookie->interpay_currency_code)}
		preselectInterpayCurrency();
	{/if}
	{if isset($cookie->interpay_country_code) && $cookie->interpay_country_code != 'US'}
		$('#currencies_block_top').hide();
	{/if}		{literal}	$('#shopping_cart').hover(function() {		{/literal}		{if isset($cookie->interpay_country_code) && $cookie->interpay_country_code != 'US'}			$('#cart_block_shipping_cost').html("{l s='At checkout' mod='gointerpay' js=1}");		{/if}		{literal}	});	{/literal}
	{literal}});{/literal}
</script>
