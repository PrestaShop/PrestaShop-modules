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
		{l s='Match eBay Items\' Specifics with PrestasShop Characteristics' mod='ebay'}
	</b>
</p>
<form action="index.php?{if $isOneDotFive}controller={$controller}{else}tab={$tab}{/if}&configure={$configure}&token={$token}&tab_module={$tab_module}&module_name={$module_name}&id_tab=8&section=category" method="post" class="form" id="configForm8">	<table class="table tableDnD" cellpadding="0" cellspacing="0" style="width: 100%;">
		<thead>
			<tr class="nodrag nodrop">
				<th style="width:110px;">
					{l s='eBay Configured Category' mod='ebay'}
				</th>
				<th>
					{l s='eBay Items\' Specifics' mod='ebay'}
				</th>
				<th style="width:128px;">
					{l s='PrestaShop Matching' mod='ebay'}
				</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$ebay_categories item=category}
				<tr id="specifics-{$category.id}">
					<td style="vertical-align: top">{$category.name}</td>
					<td>
						<img id="specifics-{$category.id}-loader" src="{$_path}views/img/loading-small.gif" alt="" />
					</td>
					<td></td>
				</tr>
			{/foreach}
		</tbody>
	</table>
	<div class="margin-form"><input class="button" name="submitSave" type="submit" value="{l s='Save' mod='ebay'}" /></div>
</form>

{literal}
	<script type="text/javascript">

		var categories_to_load = new Array();
		{/literal}
		{foreach from=$ebay_categories item=category}
			categories_to_load.push({$category.id});
		{/foreach}
		{literal}		

		function loadCategoryItemsSpecifics(category_position)
		{
			if(categories_to_load[category_position] == undefined)
				return;
				
			var category_id = categories_to_load[category_position];
			
			$.ajax({
				async: true,
				cache: false,
				dataType: 'json',
				url: "{/literal}{$_module_dir_}{literal}ebay/ajax/loadItemsSpecifics.php?ebay_category=" + category_id + "&id_lang={/literal}{$id_lang}{literal}",
				success: function(data) {
					$('#specifics-' + category_id + '-loader').hide();
					if (data.length)
						insertCategoryRow(category_id, data);
					loadCategoryItemsSpecifics(++category_position);
				}
			});
		}
		
		function insertCategoryRow(category_id, data)
		{
			var has_optionals = false;
			var trs = '';
			var trs_optionals = '';		
			for (var i in data)
			{
				var specific = data[i];
				
				var specific_values = new Array();
				if (specific.selection_mode == 0)
				{
					{/literal}
					{foreach from=$possible_attributes item=attribute}
						specific_values.push("{$attribute.name}");
					{/foreach}
					{foreach from=$possible_features item=feature}
						specific_values.push("{$feature.name}");
					{/foreach}
					{literal}
				}
				for (var j in specific.values)
					specific_values.push(specific.values[j].value);
				
				var tds = '<td>' + specific.name + '</td><td><select>';
				for (var j in specific_values)
				{
					tds += '<option>' + specific_values[j] + '</option>';
				}	
				tds += '</select></td>';

				if (parseInt(specific.required))
					trs += '<tr ' + (i % 2 == 0 ? 'class="alt_row"' : '')+ 'category="'+ category_id + '>' + tds + '</tr>';
				else
				{
					trs_optionals += '<tr ' + (parseInt(specific.required) ? '' : 'style="display:none"') + ' ' + (i % 2 == 0 ? 'class="alt_row"' : '') + 'category="'+ category_id + '">' + tds + '</tr>';
					if (!has_optionals)
						has_optionals = true;
				}
			}
			
			var nb_rows = 0;
			if (has_optionals)
			{
				nb_rows = $(trs).length + 2;
				trs += '<tr id="switch-optionals-' + category_id + '"><td><a href="#" onclick="return showOptionals(' + category_id + ')">See optional items</a></td><td></td></tr>';
			} else
				nb_rows = $(trs).length + 1;

			var row = $('#specifics-' + category_id);
			row.children('td::nth-child(1)').attr('rowspan', nb_rows);
			$(trs + trs_optionals).insertAfter(row);
		}
		
		function showOptionals(category_id)
		{
			var nb_rows_to_add = $('tr[category=' + category_id + ']').length;

			var first_td = $('#specifics-' + category_id + ' td::nth-child(1)');
			first_td.attr('rowspan', parseInt(first_td.attr('rowspan')) + nb_rows_to_add - 1);

			$('tr[category=' + category_id + ']').show();

			$('#switch-optionals-' + category_id).hide();
			return false;
		}
		
		$('#menuTab8').click(function() {
			loadCategoryItemsSpecifics(0);
		})
	</script>
{/literal}