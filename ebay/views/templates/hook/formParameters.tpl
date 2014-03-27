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
{if isset($relogin) && $relogin}
	{literal}
	<script>
		$(document).ready(function() {
			win = window.redirect('{$redirect_url}');

		});
	</script>
	{/literal}
{/if}
<script type="text/javascript">
	$(document).ready(function(){ldelim}
		if(regenerate_token_show)
		{ldelim}
			$('.regenerate_token_button').show();
			$('.regenerate_token_button label').css('color', 'red').html('{l s='You must regenerate your authentication token' mod='ebay'}');
			$('.regenerate_token_click').hide();
		{rdelim}
		$('.regenerate_token_click span').click(function()
		{ldelim}
			$('.regenerate_token_button').show();
			$('.regenerate_token_click').hide();
		{rdelim});
	})
</script>

	{if isset($check_token_tpl)}
	<fieldset id="regenerate_token">
		<legend>{l s='Token' mod='ebay'}</legend>
			{$check_token_tpl}	
	</fieldset>	
	{/if}
	
<form action="{$url}" method="post" class="form" id="configForm1">
	
	<fieldset style="margin-top:10px;">
		<legend>{l s='Account details' mod='ebay'}</legend>
		<h4>{l s='To list your products on eBay, you need to create' mod='ebay'} <a href="{l s='https://scgi.ebay.co.uk/ws/eBayISAPI.dll?RegisterEnterInfo&bizflow=2' mod='ebay'}">{l s='a business seller account' mod='ebay'}</a> {l s='and' mod='ebay'} <a href="https://www.paypal.com/">{l s='a PayPal account.' mod='ebay'}</a></h4>
		<label>{l s='eBay User ID' mod='ebay'} : </label>
		<div class="margin-form">
			<input type="text" size="20" name="ebay_identifier" value="{$ebayIdentifier}"/>
		</div>
		<label>{l s='eBay Shop' mod='ebay'} : </label>
		<div class="margin-form">
			<input type="text" size="20" name="ebay_shop" value="{$ebayShopValue}" data-dialoghelp="http://sellercentre.ebay.co.uk/ebay-shop" data-inlinehelp="{l s='An eBay shop subscription isn’t required but you may benefit. Find out if an eBay Shop is right for you.' mod='ebay'}"/> 
			<p>
				{if $ebayShop!== false}
					<a href="http://stores.{if $ebayCountry->getSiteSubdomain()}{$ebayCountry->getSiteSubdomain()}.{/if}ebay.{$ebayCountry->getSiteExtension()}/{$ebayShop}" target="_blank">{l s='Your shop on eBay' mod='ebay'}</a>
				{else} 
					<a href="{$createShopUrl}" style="color:#7F7F7F;">
						{l s='Open your shop' mod='ebay'}
					</a>
				{/if}
			</p>
		</div>
		<label>{l s='Paypal email address' mod='ebay'} : </label>
		<div class="margin-form">

			<input type="text" size="20" name="ebay_paypal_email" value="{$ebay_paypal_email}"/>
			<p>{l s='You have to set your PayPal e-mail account, it\'s the only payment available with this module' mod='ebay'}</p>
		</div>
		<label>{l s='Item location' mod='ebay'} : </label>
		<div class="margin-form">
			<input type="text" size="20" name="ebay_shop_postalcode" value="{$shopPostalCode}"/>
			<p>{l s='Your shop\'s postal code' mod='ebay'}</p>
		</div>

		<div class="show regenerate_token_click" style="display:block;text-align:center;cursor:pointer">
			<span data-inlinehelp="{l s='Use only if you get a message saying that your authentication is expired.' mod='ebay'}">{l s='Click here to generate a new authentication token.' mod='ebay'}</span>
		</div>
		<div class="hide regenerate_token_button" style="display:none;">
			<label>{l s='Regenerate Token' mod='ebay'} :</label>
			<a href="{$url}&action=regenerate_token">
				<input type="button" id="token-btn" class="button" value="{l s='Regenerate Token' mod='ebay'}" />
			</a>
		</div>
	</fieldset>
	
   <fieldset style="margin-top:10px;">
		<legend>{l s='Returns policy' mod='ebay'}</legend>
		<label>{l s='Please define your returns policy' mod='ebay'} : </label>
		<div class="margin-form">
			<select name="ebay_returns_accepted_option" data-dialoghelp="#returnsAccepted" data-inlinehelp="{l s='eBay business sellers must accept returns under the Distance Selling Regulations.' mod='ebay'}">
			{foreach from=$policies item=policy}
				<option value="{$policy.value}" {if $returnsConditionAccepted == $policy.value} selected="selected"{/if}>{$policy.description}</option>
			{/foreach}							   
			</select>
		</div>
		<div style="clear:both;"></div>
		<label>{l s='Returns within' mod='ebay'} :</label>
		<div class="margin-form">
			<select name="returnswithin" data-inlinehelp="{l s='eBay business sellers must offer a minimum of 14 days for buyers to return their items.' mod='ebay'}">
					{if isset($within_values) && $within_values && sizeof($within_values)}
						{foreach from=$within_values item='within_value'}
							<option value="{$within_value.value}"{if isset($within) && $within == $within_value.value} selected{/if}>{$within_value.description}</option>
						{/foreach}
					{/if}
			</select>
		</div>
		<div style="clear:both;"></div>
		<label>{l s='Who pays' mod='ebay'} :</label>
		<div class="margin-form">
			<select name="returnswhopays">
				{if isset($whopays_values) && $whopays_values && sizeof($whopays_values)}
					{foreach from=$whopays_values item='whopays_value'}
						<option value="{$whopays_value.value}"{if isset($whopays) && $whopays == $whopays_value.value} selected{/if}>{$whopays_value.description}</option>
					{/foreach}
				{/if}
			</select>
		</div>
		<label>{l s='Any other information' mod='ebay'} : </label>
		<div class="margin-form">
			<textarea name="ebay_returns_description" cols="120" rows="10" data-inlinehelp="{l s='This description will be displayed in the returns policy section of the listing page.' mod='ebay'}">{$ebayReturns|escape:'htmlall':'UTF-8'}</textarea>
		</div>
	</fieldset>

	<!-- Listing Durations -->
	<fieldset style="margin-top:10px;">
		<legend>{l s='Listing Duration' mod='ebay'}</legend>
		
		<label>
			{l s='Listing duration' mod='ebay'}
		</label>
		<div class="margin-form">

			<select name="listingdurations" data-dialoghelp="http://pages.ebay.com/help/sell/duration.html" data-inlinehelp="{l s='The listing duration is the length of time that your listing is active on eBay.co.uk. You can have it last 1, 3, 5, 7, 10, 30 days or Good \'Til Cancelled. Good \'Til Cancelled listings renew automatically every 30 days unless all of the items sell, you end the listing, or the listing breaches an eBay policy. Good \'Til Cancelled is the default setting here to save you time relisting your items.' mod='ebay'}">
				{foreach from=$listingDurations item=listing key=key}
					<option value="{$key}" {if $ebayListingDuration == $key}selected="selected" {/if}>{$listing|escape:'htmlall':'UTF-8'}</option>
				{/foreach}
			</select>
		</div>

		<label for="">{l s='Do you want to automatically relist' mod='ebay'}</label>
		<div class="margin-form"><input type="checkbox" name="automaticallyrelist" {if $automaticallyRelist == 'on'} checked="checked" {/if} /></div>
	</fieldset>

	<fieldset style="margin-top:10px;">
		<legend><span data-dialoghelp="http://sellerupdate.ebay.co.uk/autumn2013/picture-standards" data-inlinehelp="{l s='Select the size of your main photo and any photos you want to include in your description. Go to Preferences> images. Your images must comply with eBay’s photo standards.' mod='ebay'}">{l s='Photo sizes' mod='ebay'}</span></legend>

		<label>
			{l s='Default photo' mod='ebay'}
		</label>
		<div class="margin-form">
			<select name="sizedefault" data-inlinehelp="{l s='This will be the main photo and will appear on the search result and item pages.' mod='ebay'}">
				{if isset($sizes) && $sizes && sizeof($sizes)}
					{foreach from=$sizes item='size'}
						<option value="{$size.id_image_type}"{if $size.id_image_type == $sizedefault} selected{/if}>{$size.name}</option>
					{/foreach}
				{/if}
			</select>
		</div>
		<div class="clear both"></div>

		<label>
			{l s='Main photo' mod='ebay'}
		</label>
		<div class="margin-form">
			<select name="sizebig" data-inlinehelp="{l s='This photo will appear as default photo in your listing\'s description.' mod='ebay'}">
				{if isset($sizes) && $sizes && sizeof($sizes)}
					{foreach from=$sizes item='size'}
						<option value="{$size.id_image_type}"{if $size.id_image_type == $sizebig} selected{/if}>{$size.name}</option>
					{/foreach}
				{/if}
			</select>
		</div>
		<div class="clear both"></div>

		<label>
			{l s='Small photo' mod='ebay'}
		</label>
		<div class="margin-form">
			<select name="sizesmall" data-inlinehelp="{l s='This photo will appear as thumbnail in your listing\'s description.' mod='ebay'}">
				{if isset($sizes) && $sizes && sizeof($sizes)}
					{foreach from=$sizes item='size'}
						<option value="{$size.id_image_type}"{if $size.id_image_type == $sizesmall} selected{/if}>{$size.name}</option>
					{/foreach}
				{/if}
			</select>
		</div>
		<div style="clear:both;"></div>

	</fieldset>
		

	<div class="margin-form" id="buttonEbayParameters" style="margin-top:5px;">
		<a href="#categoriesProgression" {if $catLoaded}id="displayFancybox"{/if}>
			<input class="primary button" name="submitSave" type="hidden" value="{l s='Save and continue' mod='ebay'}" />
			<input class="primary button" type="submit" id="save_ebay_parameters" value="{l s='Save and continue' mod='ebay'}" />
		</a>
	</div>

	<div id="ebayreturnshide" style="display:none;">{$ebayReturns|escape:'htmlall':'UTF-8'}</div>

	{literal}
		<script>
			$(document).ready(function() {
				setTimeout(function(){					
					$('#ebay_returns_description').val($('#ebayreturnshide').html());
				}, 1000);
			});
			
			$('#token-btn').click(function() {
					window.open(module_dir + 'ebay/pages/getSession.php?token={/literal}{$ebay_token}{literal}');			
			});
		</script>
	{/literal}
</form>

{if $catLoaded}
	{literal}
	<script>
		var percent = 0;
		function checkCategories()
		{
			percent++;
			if (percent > 100)
				percent = 100;
			
			$("#categoriesProgression").html("{/literal}{l s='Categories loading' mod='ebay'}{literal}  <div>" + percent + " %</div>");
			if (percent < 100)
				setTimeout ("checkCategories()", 1000);
		}

		$(function(){
			$j("#displayFancybox").fancybox({
				beforeShow : function(){
					checkCategories();
					$("#save_ebay_parameters").parents('form').submit();
				},
				onStart : function(){
					checkCategories();
					$("#save_ebay_parameters").parents('form').submit();
				}
			});
		});
	</script>
	{/literal}
{/if}