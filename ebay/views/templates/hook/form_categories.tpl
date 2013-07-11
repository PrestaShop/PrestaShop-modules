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
						<img src="{$_path}img/help.png" width="25" alt="help_picture"/>
					</a>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr id="removeRow">
				<td class="center" colspan="3">
					<img src="{$_path}img/loading-small.gif" alt="" />
				</td>
			</tr>
		</tbody>
	</table>
	<div class="margin-form"><input class="button" name="submitSave" type="submit" value="{l s='Save' mod='ebay'}" /></div>
</form>

<p><b>{l s='Warning: Only default product categories are used for the configuration' mod='ebay'}</b></p><br />

<p align="left">
	* {l s='Some categories benefit from eBay\'s multi-variation feature which allows publishing one product with multiple versions.' mod='ebay'}<br />
	{l s='Warning: For categories that do not have this functionality, one listing will be added for each version of the product' mod='ebay'}<br />
	<a href="{l s='http://sellerupdate.ebay.fr/autumn2012/improvements-multi-variation-listings' mod='ebay'}" target="_blank">{l s='Click here for more informations on multi-variation listings' mod='ebay'}</a>
</p><br /><br />
{literal}
	<script type="text/javascript">
			
		var $selects = false;
		
		function loadCategoryMatch(id_category) {
			$.ajax({
				async: false,
				url: "{/literal}{$_module_dir_}{literal}ebay/ajax/loadCategoryMatch.php?token={/literal}{$configs.EBAY_SECURITY_TOKEN}{literal}&id_category=" + id_category + "&time={/literal}{$date}{literal}",
				success: function(data) { $("#categoryPath" + id_category).html(data); }
			});
		}
		function changeCategoryMatch(level, id_category) {
			var levelParams = "&level1=" + $("#categoryLevel1-" + id_category).val();
			if (level > 1) levelParams += "&level2=" + $("#categoryLevel2-" + id_category).val();
			if (level > 2) levelParams += "&level3=" + $("#categoryLevel3-" + id_category).val();
			if (level > 3) levelParams += "&level4=" + $("#categoryLevel4-" + id_category).val();
			if (level > 4) levelParams += "&level5=" + $("#categoryLevel5-" + id_category).val();

			$.ajax({
				url: "{/literal}{$_module_dir_}{literal}ebay/ajax/changeCategoryMatch.php?token={/literal}{$configs.EBAY_SECURITY_TOKEN}{literal}&id_category=" + id_category + "&time={/literal}{$date}{literal}&level=" + level + levelParams,
				success: function(data) { $("#categoryPath" + id_category).html(data); }
			});
		}
		$(document).ready(function(){
			$.ajax({
				url: "{/literal}{$_module_dir_}{literal}ebay/ajax/loadTableCategories.php?token={/literal}{$configs.EBAY_SECURITY_TOKEN}{literal}&id_lang={/literal}{$id_lang}{literal}",
				success : function(data) { $("form#configForm2 table tbody #removeRow").remove(); $("form#configForm2 table tbody").html(data); }
			});
			
			$("#configForm2SuggestedCategories input[type=submit]").click(function(){
				$('<div class="center"><img src="{/literal}{$_path}{literal}img/loading-small.gif" alt="" />{/literal}{l s='Thank you for waiting while creating suggestions' mod='ebay'}{literal}</div>').insertAfter($(this));
				$(this).fadeOut();
				$.ajax({
					url: "{/literal}{$_module_dir_}{literal}ebay/ajax/suggestCategories.php?token={/literal}{$configs.EBAY_SECURITY_TOKEN}{literal}&id_lang={/literal}{$id_lang}{literal}",
					success : function(data) { window.location.href = window.location.href + "&conf=6"; }
				});
				return false;
			});
		});
	</script>
{/literal}