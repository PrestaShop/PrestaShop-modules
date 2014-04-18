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

{if isset($alerts) && !empty($alerts)}
	{$alerts}
{/if}
<div>
	<p>
		{l s='Item specifics are the details that buyers use to search for products, such as brand, size, colour and are category specific. The more item specifics you add, the easier it is for buyers to find your products. Please also specify the item condition from the options.' mod='ebay'}
	</p>
	<!---------------------------->
	<p>
		<b data-inlinehelp="{l s='Make it easier for buyers to find your products by adding eBay item specifics. Please wait until the page is loaded - this may take a few minutes.' mod='ebay'}">
			{l s='Match your PrestaShop characteristics to eBay item specifics.' mod='ebay'}
		</b>
	</p>
</div>
<form action="index.php?{if $isOneDotFive}controller={$controller}{else}tab={$tab}{/if}&configure={$configure}&token={$token}&tab_module={$tab_module}&module_name={$module_name}&id_tab=8&section=specifics" method="post" class="form" id="configForm8">
	<table class="table tableDnD" cellpadding="0" cellspacing="0" style="width: 100%;">
		<thead>
			<tr class="nodrag nodrop">
				<th style="width:30%">
					{l s='eBay category' mod='ebay'}
				</th>
				<th style="width:20%">
					<span data-inlinehelp="{l s='The first item specifics are required by eBay and you won’t be able to list your item without adding them. You can also add optional item specifics that will help buyers find your item. Specify the condition of your item in the second box.' mod='ebay'}">{l s='Item specifics' mod='ebay'}</span>
				</th>
				<th style="width:50%">
					{l s='PrestaShop characteristics' mod='ebay'}
				</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$ebay_categories item=category}
				<tr id="specifics-{$category.id}">
					<td style="vertical-align: top">{$category.name}</td>
					<td>
						<img id="specifics-{$category.id}-loader" src="{$_path}views/img/loading-small.gif" alt="" style="height:20px;" />
					</td>
					<td></td>
				</tr>
			{/foreach}
		</tbody>
	</table>
	<div class="margin-form" id="buttonEbayShipping" style="margin-top:5px;">
		<input class="primary button" name="submitSave" type="submit" id="save_ebay_shipping" value="{l s='Save and continue' mod='ebay'}"/>
	</div>
</form>

<script type="text/javascript">
	var module_dir = "{$_module_dir_}";
	var id_lang = "{$id_lang}";
	var ebay_token = "{$ebay_token}";
	
	var l = {ldelim}
		'Attributes'				 : "{l s="Attributes" mod='ebay'}",
		'Features'  				 : "{l s="Features" mod='ebay'}",
		'eBay Specifications': "{l s="eBay Specifications" mod='ebay'}",
		'Brand'							 : "{l s="Brand" mod='ebay'}"
	{rdelim};

	var categories_to_load = new Array();

	{foreach from=$ebay_categories item=category}
		categories_to_load.push({$category.id});
	{/foreach}
	
	var conditions_data = new Array();
	{foreach from=$conditions key=type item=condition}
		conditions_data[{$type}] = "{$condition}";
	{/foreach}
	
	var possible_attributes = new Array();
	{foreach from=$possible_attributes item=attribute}
		possible_attributes[{$attribute.id_attribute_group}] = "{$attribute.name}";
	{/foreach}		
		
	var possible_features = new Array();
	{foreach from=$possible_features item=feature}
		possible_features[{$feature.id_feature}] = "{$feature.name}";
	{/foreach}

	{literal}			
	$('#menuTab8').click(function() {
		loadCategoriesItemsSpecifics();
	})

	$(document).ready(function() 
	{
		{/literal}{if $id_tab == 8}
			loadCategoriesItemsSpecifics();
		{/if}
	{rdelim})
	
</script>
<script type="text/javascript" src="{$_module_dir_}ebay/views/js/itemsSpecifics.js?date={$date}"></script>