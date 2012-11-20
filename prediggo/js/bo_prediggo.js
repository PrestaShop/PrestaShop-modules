/**
 * @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
 * @copyright Croissance NET
 * @version 1.0
 */

$(document).ready(function(){
	$('input#product_reco_autocomplete')
		.autocomplete('ajax_products_list.php', {
			minChars: 1,
			autoFill: true,
			max:20,
			matchContains: true,
			mustMatch:true,
			scroll:false
		})
		.result(addProductToBlackListReco);

	$('input#product_search_autocomplete')
	.autocomplete('ajax_products_list.php', {
		minChars: 1,
		autoFill: true,
		max:20,
		matchContains: true,
		mustMatch:true,
		scroll:false
	})
	.result(addProductToBlackListSearch);

	$('ul#prediggo_black_list_reco li img, ul#prediggo_black_list_search li img').bind('click', function(e){
		$(this).parent().remove();
	});

	if ($().jquery > "1.3")
		$("#prediggo_conf").tabs({cache:false});
	else
		$("#prediggo_conf > ul").tabs({cache:false});
});

function addProductToBlackListReco(event, data, formatted)
{
	addToBlackList('ul#prediggo_black_list_reco', 'prediggo_products_ids_not_recommendable', data);
}

function addProductToBlackListSearch(event, data, formatted)
{
	addToBlackList('ul#prediggo_black_list_search', 'prediggo_products_ids_not_searchable', data);
}

function addToBlackList(idBlackList, nameEl, data)
{
	if($(idBlackList+' li[data="'+parseInt(data[1])+'"]').length > 0)
		return;

	var deleteEl = $('<img>').attr({
		'src' : '../img/admin/delete.gif',
		'style' : 'cursor:pointer'
	}).bind('click', function(e){
		$(this).parent().remove();
	});

	var idProductEl = $('<input>').attr({
		'name' : nameEl+'[]',
		'type':'hidden',
		'value':parseInt(data[1])
	});

	var containerEl = $('<li>')
	.attr('data', parseInt(data[1]))
	.html(data[0])
	.append(idProductEl)
	.append(deleteEl);

	$(idBlackList).append(containerEl);
}