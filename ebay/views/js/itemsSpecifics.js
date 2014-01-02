/*
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
*	@author PrestaShop SA <contact@prestashop.com>
*	@copyright	2007-2014 PrestaShop SA
*	@license	http://opensource.org/licenses/afl-3.0.php	Academic Free License (AFL 3.0)
*	International Registered Trademark & Property of PrestaShop SA
*/

var has_loaded_categories_items_specifics = false;
function loadCategoriesItemsSpecifics()
{
	if (has_loaded_categories_items_specifics)
		return;
	
	has_loaded_categories_items_specifics = true;	
	loadCategoryItemsSpecifics(0);
}

function loadCategoryItemsSpecifics(category_position)
{
	if(categories_to_load[category_position] == undefined)
		return;
		
	var category_id = categories_to_load[category_position];
	
	$.ajax({
		async: true,
		cache: false,
		dataType: 'json',
		url: module_dir + "ebay/ajax/loadItemsSpecificsAndConditions.php?token=" + ebay_token + "&ebay_category=" + category_id + "&id_lang=" + id_lang,
		success: function(data) {
			$('#specifics-' + category_id + '-loader').hide();
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
	
	// specifics
	var specifics = data.specifics;

	for (var i in specifics)
	{
		var specific = specifics[i];
		var tds = '<td>' + specific.name + '</td><td>';
		tds += '<select name="specific[' + specific.id + ']">';
	
		if (!parseInt(specific.required))
			tds += '<option value=""></option>';
	
		if (specific.selection_mode == 0)
		{
			if (!data.is_multi_sku || specific.can_variation)
			{
				tds += '<option disabled="disabled">' + l['Attributes'] + '</option>';
				tds += writeOptions('attr', possible_attributes, specific.id_attribute_group);		
			}
			
			tds += '<option disabled="disabled">' + l['Features'] + '</option>';
			tds += '<option value="brand-1" ' + (specific.is_brand == 1 ? 'selected' : '') + '>' + l['Brand'] + '</option>';
			tds += writeOptions('feat', possible_features, specific.id_feature);
		}

		tds += '<option disabled="disabled">' + l['eBay Specifications'] + '</option>';
		tds += writeOptions('spec', specific.values, specific.id_specific_value);
		
		tds += '</select></td>';

		if (parseInt(specific.required))
			trs += '<tr ' + (i % 2 == 0 ? 'class="alt_row"' : '')+ 'category="'+ category_id + '">' + tds + '</tr>';
		else
		{
			trs_optionals += '<tr class="optional" ' + (parseInt(specific.required) ? '' : 'style="display:none"') + ' ' + (i % 2 == 0 ? 'class="alt_row"' : '') + 'category="'+ category_id + '">' + tds + '</tr>';
			
			if (!has_optionals)
				has_optionals = true;
		}
	}
	
	// Item Conditions
	var ebay_conditions = data.conditions;
	var alt_row = true;

	for (var condition_type in conditions_data)
	{
		var condition_data = conditions_data[condition_type];
		var tds = '<td><select name="condition[' + category_id + '][' + condition_type + ']">';

		for (var id in ebay_conditions)
			tds += '<option value="' + id + '" ' + ($.inArray(condition_type, ebay_conditions[id].types) >= 0 ? 'selected' : '') + '>' + ebay_conditions[id].name + '</option>';

		tds += '</td><td>' + condition_data + '</td>';
		trs += '<tr ' + (alt_row ? 'class="alt_row"' : '')+ 'category="'+ category_id + '">' + tds + '</tr>';

		alt_row = !alt_row;
	}
	
	if (has_optionals)
		trs += '<tr id="switch-optionals-' + category_id + '"><td><a href="#" onclick="return showOptionals(' + category_id + ')">See optional items</a></td><td></td></tr>';

	var row = $('#specifics-' + category_id);
	row.children('td:nth-child(1)').attr('rowspan', $(trs).length + 1);
	$(trs + trs_optionals).insertAfter(row);
	
}

function writeOptions(value_prefix, options, selected_id)
{
	var str = '';

	for (var id in options)
		str += '<option value="' + value_prefix + '-' + id + '" ' + (id == selected_id ? 'selected' : '') + '>' + options[id] + '</option>';

	return str;
}

function showOptionals(category_id)
{
	var nb_rows_to_add = $('tr.optional[category=' + category_id + ']').length;

	var first_td = $('#specifics-' + category_id + ' td::nth-child(1)');
	first_td.attr('rowspan', parseInt(first_td.attr('rowspan')) + nb_rows_to_add - 1);

	$('tr[category=' + category_id + ']').show();
	$('#switch-optionals-' + category_id).hide();

	return false;
}