{*
* 2007-2013 PrestaShop
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
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<form action="{$url}" method="post" class="form" id="configForm1">
	<fieldset style="border: 0">
		<legend>{l s='Global Configuration' mod='ebay'}</legend>
		<h4>{l s='To list your products on eBay, you first need to create a business seller account on eBay and configure your eBay-Prestashop module' mod='ebay'}</h4>
		<label>{l s='eBay Identifier' mod='ebay'} : </label>
		<div class="margin-form">
			<input type="text" size="20" name="ebay_identifier" value="{$ebayIdentifier}"/>
		</div>
		<label>{l s='eBay shop' mod='ebay'} : </label>
		<div class="margin-form">
			<input type="text" size="20" name="ebay_shop" value="{$ebayShopValue}" /> 
			<p>
				{if $ebayShop!== false}
					<a href="http://stores.ebay.fr/{$ebayShop}" target="_blank">{l s='Your shop on eBay' mod='ebay'}</a>
				{else} 
					<a href="{$createShopUrl}" style="color:#7F7F7F;">
						{l s='Open your shop' mod='ebay'}
					</a>
				{/if}
			</p>
		</div>
		<label>{l s='Paypal Identifier (e-mail)' mod='ebay'} : </label>
		<div class="margin-form">

			<input type="text" size="20" name="ebay_paypal_email" value="{$ebay_paypal_email}" />
			<p>{l s='You have to set your PayPal e-mail account, it\'s the only payment available with this module' mod='ebay'}</p>
		</div>
		<label>{l s='Shop postal code' mod='ebay'} : </label>
		<div class="margin-form">
			<input type="text" size="20" name="ebay_shop_postalcode" value="{$shopPostalCode}" />
			<p>{l s='Your shop\'s postal code' mod='ebay'}</p>
		</div>
	</fieldset>
	
	<fieldset style="margin-top:10px;">
		<legend>{l s='Item Conditions' mod='ebay'}</legend>
		<p>{l s='Please align your item condition settings on PrestaShop and eBay' mod='ebay'}</p>
		<table class="table">
			<tr>
				<th>{l s='PrestaShop item condition' mod='ebay'}</th>
				<th>{l s='Ebay item condition' mod='ebay'}</th>
			</tr>
			<tr>
				<td>{l s='New' mod='ebay'}</td>
				<td>
					<select name="newConditionID" id="">
					{foreach from=$ebayItemConditions item=itemCondition key=key}
						<option value="{$key}" {if $key == $ebayConditionNew} selected="selected"{/if}>{$itemCondition}</option>
					{/foreach}
					</select>
				</td>					
			</tr>
			<tr>
				<td>{l s='Used' mod='ebay'}</td>
				<td>
					<select name="usedConditionID" id="">
					{foreach from=$ebayItemConditions item=itemCondition key=key}
						<option value="{$key}" {if $key == $ebayConditionUsed} selected="selected"{/if}>{$itemCondition}</option>
					{/foreach}
					</select>
				</td>					
			</tr>
			<tr>
				<td>{l s='Refurbished' mod='ebay'}</td>
				<td>
					<select name="refurbishedConditionID" id="">
					{foreach from=$ebayItemConditions item=itemCondition key=key}
						<option value="{$key}" {if $key == $ebayConditionRefurbished} selected="selected"{/if}>{$itemCondition}</option>
					{/foreach}
					</select>
				</td>
			</tr>
		</table>

		<p><a href="{l s='http://pages.ebay.com/help/sell/item-condition.html' mod='ebay'}">{l s='Learn more about eBay item condition: http://pages.ebay.com/help/sell/item-condition.html' mod='ebay'}</a></p>
	</fieldset>


   <fieldset style="margin-top:10px;">
		<legend>{l s='Return policy' mod='ebay'}</legend>
		<label>{l s='Please define your returns policy' mod='ebay'} : </label>
		<div class="margin-form">
			<select name="ebay_returns_accepted_option">';
			{foreach from=$policies item=policy}
				<option value="{$policy.value}" {if $returnsConditionAccepted == $policy.value} selected="selected"{/if}>{$policy.description}</option>
			{/foreach}							   
			</select>
		</div>
		<div style="clear:both;"></div>
		<label>{l s='Description' mod='ebay'} : </label>
		<div class="margin-form">
			<textarea name="ebay_returns_description" cols="120" rows="10">{$ebayReturns}</textarea>
		</div>
	</fieldset>

	<!-- Listing Durations -->
	<fieldset style="margin-top:10px;">
		<legend>{l s='Listing Durations' mod='ebay'}</legend>
		
		<label>
			{l s='Choose your listing duration' mod='ebay'}
		</label>
		<div class="margin-form">

			<select name="listingdurations">
				{foreach from=$listingDurations item=listing key=key}
					<option value="{$key}" {if $ebayListingDuration == $key}selected="selected" {/if}>{$listing}</option>
				{/foreach}
			</select>
		</div>

		<label for="">{l s='Do you want to automatically relist' mod='ebay'}</label>
		<div class="margin-form"><input type="checkbox" name="automaticallyrelist" {if $automaticallyRelist == 'on'} checked="checked" {/if} /></div>
	</fieldset>
		

	<div class="margin-form" id="buttonEbayParameters" style="margin-top:10px;"><input class="button" name="submitSave" type="submit" id="save_ebay_parameters" value="{l s='Save' mod='ebay'}" /></div>
	<div class="margin-form" id="categoriesProgression" style="font-weight: bold;"></div>

	<div id="ebayreturnshide" style="display:none;">{$ebayReturns}</div>

	{literal}
		<script>
			$(document).ready(function() {
				setTimeout(function(){tinyMCE.execCommand('mceRemoveControl', true, 'ebay_returns_description');$('#ebay_returns_description').val($('#ebayreturnshide').html());}, 1000);
			});
		</script>
	{/literal}

</form>

{if $catLoaded}
	{literal}
	<script>
		percent = 0;
		function checkCategories()
		{
			percent++;
			if (percent > 100)
				percent = 100;
			$("#categoriesProgression").html("{/literal}{l s='Categories loading' mod='ebay'}{literal} : " + percent + " %");
			if (percent < 100)
				setTimeout ("checkCategories()", 1000);
		}
		$(document).ready(function() {
			
			$("#save_ebay_parameters").click(function() {
				$("#buttonEbayParameters").hide();
				checkCategories();
			});
		});
	</script>
	{/literal}
{/if}
