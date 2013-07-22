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

{if isset($alerts) && !empty($alerts)}
	{$alerts}
{/if}
<p>
	<b>
		{l s='To list your products on eBay, you must associate each shop category to an eBay category. You can also define a price adjustment for your product price when listing on eBay.' mod='ebay'}
	</b>
</p>
<p>
	<b>
		{l s='You can impact price either by adding a fixed an amount or by increasing by percentage. If you choose to increase with percentage add "%" at the end of your number' mod='ebay'}
	</b>
</p>
<br />
<form action="index.php?{if $isOneDotFive}controller={$controller}{else}tab={$tab}{/if}&configure={$configure}&token={$token}&tab_module={$tab_module}&module_name={$module_name}&id_tab=2&section=category&action=suggestCategories" method="post" class="form" id="configForm2SuggestedCategories">
	<p>
		<b>
			{l s='You can use the button below to associate automatically the categories which have no association for the moment with an eBay suggested category.' mod='ebay'}
		</b><br/>
		<input class="button" name="submitSave" type="submit" value="{l s='Suggest Categories' mod='ebay'}" />
	</p><br />
</form>
<form action="index.php?{if $isOneDotFive}controller={$controller}{else}tab={$tab}{/if}&configure={$configure}&token={$token}&tab_module={$tab_module}&module_name={$module_name}&id_tab=2&section=category" method="post" class="form" id="configForm2">	<table class="table tableDnD" cellpadding="0" cellspacing="0" style="width: 100%;">
		<thead>
			<tr class="nodrag nodrop">
				<th style="width:110px;">
					{l s='Category' mod='ebay'}<br/>{l s='Quantity in stock' mod='ebay'}
				</th>
				<th>
					{l s='eBay Category' mod='ebay'}
				</th>
				<th style="width:128px;">
					{l s='Price adjustment' mod='ebay'}
					<a title="{l s='Help' mod='ebay'}" href="{$request_uri}{$tabHelp}" >
						<img src="{$_path}views/img/help.png" width="25" alt="help_picture"/>
					</a>
				</th>				
				<th class="center">
					{l s='Synchronize Product' mod='ebay'}
				</th>
				<th class="center">
					{l s='Extra Images' mod='ebay'}
				</th>				
			</tr>
		</thead>
		<tbody>
			<tr id="removeRow">
				<td class="center" colspan="3">
					<img src="{$_path}views/img/loading-small.gif" alt="" />
				</td>
			</tr>
		</tbody>
	</table>
	<div style="text-align: right; margin-top: 5px">
		{l s='Add more photos to your listing. Please note that this may incur extra costs.' mod='ebay'}<br/>
		{l s='Change extra pictures numbers for all products' mod='ebay'} <input type="number" id="all-extra-images-selection" value="0" min="0" max="99"> <input id="update-all-extra-images" type="button" value='Apply'>
		<input type="hidden" id="all-extra-images-value" name="all-extra-images-value" value="-1"/>
	</div>
	
	<div class="margin-form"><input class="button" name="submitSave" type="submit" value="{l s='Save' mod='ebay'}" /></div>
</form>

<p><b>{l s='Warning: Only default product categories are used for the configuration' mod='ebay'}</b></p><br />

<p align="left">
	* {l s='Some categories benefit from eBay\'s multi-variation feature which allows publishing one product with multiple versions.' mod='ebay'}<br />
	{l s='Warning: For categories that do not have this functionality, one listing will be added for each version of the product' mod='ebay'}<br />
	<a href="{l s='http://sellerupdate.ebay.fr/autumn2012/improvements-multi-variation-listings' mod='ebay'}" target="_blank">{l s='Click here for more informations on multi-variation listings' mod='ebay'}</a>
</p><br /><br />
<script type="text/javascript">
		
	var $selects = false;
	
	var module_dir = '{$_module_dir_}';
	var ebay_token = '{$configs.EBAY_SECURITY_TOKEN}';
	var module_time = '{$date}';
	var module_path = '{$_path}';
	var id_lang = '{$id_lang}';
	var ebay_l = {ldelim}
		'thank you for waiting': "{l s='Thank you for waiting while creating suggestions' mod='ebay'}",
		'no category selected' : "{l s='No category selected' mod='ebay'}",
		'No category found'		 : "{l s='No category found' mod='ebay'}",
		'You are not logged in': "{l s='You are not logged in' mod='ebay'}",
		'Settings updated'		 : "{l s='Settings updated' mod='ebay'}",
		'Unselect products'		: "{l s='Select products that you do NOT want to list on eBay' mod='ebay'}",
		'Unselect products clicked' : "{l s='Select products that you do NOT want to list on eBay' mod='ebay'}"
	{rdelim};
	
</script>
<script type="text/javascript" src="{$_module_dir_}ebay/views/js/categories.js?date={$date}"></script>