/**
 * @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
 * @copyright Croissance NET
 * @version 1.0
 */

$(document).ready(function(){
	// Bind all recommendations clics
	// Bind all search clics
	$('ul.prediggo_products a, ul#product_list a').bind('click',function(e){
		var notificationId = $(this).parents('li').find('input[type="hidden"].notification_id').val();
		notifyPrediggo(notificationId);
	});

	if($('div#prediggo_search input[type="text"]').length > 0)
	{
		$('div#prediggo_search input[type="text"]')
		.prediggo_autocomplete(
			baseDir+'modules/prediggo/ajax/ajax.php',
			{
				minChars: 0,
				max: 10,
				width: $('div#prediggo_search p').width()-2,
				selectFirst: false,
				scroll: false,
				dataType: "json",
				delay : 300,
				formatItem: function(data, i, max, value, term) {
					return value;
				},
				parse: function(data) {
					var atab = new Array();
					for (var i = 0; i < data.length; i++)
						atab[atab.length] = { data: data[i], value: data[i].value};
					return atab;
				}
			}
		).result(function(event, data, formatted) {
			if(data.notificationId != '')
				notifyPrediggo(data.notificationId);
			document.location.href = data.link;
		});
	}
});

function notifyPrediggo(notificationId)
{
	$.ajax({
		type: 'POST',
		async: false,
		dataType: 'json',
		url: baseDir+'modules/prediggo/ajax/ajax.php',
		data: {'nId':notificationId}
	});
}

var base_reloadContent = reloadContent;
function reloadContent(params_plus)
{
	for(i = 0; i < ajaxQueries.length; i++)
		ajaxQueries[i].abort();
	ajaxQueries = new Array();

	if (!ajaxLoaderOn)
	{
		$('#product_list').prepend($('#layered_ajax_loader').html());
		$('#product_list').css('opacity', '0.7');
		ajaxLoaderOn = 1;
	}

	data = $('#layered_form').serialize();
	$('.layered_slider').each( function () {
		var sliderStart = $(this).slider('values', 0);
		var sliderStop = $(this).slider('values', 1);
		if (typeof(sliderStart) == 'number' && typeof(sliderStop) == 'number')
			data += '&'+$(this).attr('id')+'='+sliderStart+'_'+sliderStop;
	});

	if ($('#selectPrductSort').length)
	{
		var splitData = $('#selectPrductSort').val().split(':');
		data += '&orderby='+splitData[0]+'&orderway='+splitData[1];
	}

	var slideUp = true;
	if (params_plus == undefined)
	{
		params_plus = '';
		slideUp = false;
	}

	// Get nb items per page
	var n = '';
	$('#pagination #nb_item').children().each(function(it, option) {
		if (option.selected)
			n = '&n='+option.value;
	});

	ajaxQuery = $.ajax(
	{
		type: 'GET',
		url: baseDir + 'modules/prediggo/ajax/ajax.php',
		data: data+params_plus+n,
		dataType: 'json',
		success: function(result)
		{
			$('#layered_block_left').after('<div id="tmp_layered_block_left"></div>').remove();
			$('#tmp_layered_block_left').html(result.filtersBlock).attr('id', 'layered_block_left');

			$('.category-product-count').html(result.categoryCount);

			$('#product_list').replaceWith(result.productList);
			$('#product_list').css('opacity', '1');
			$('div#pagination').html(result.pagination);
			paginationButton();
			ajaxLoaderOn = 0;

			// On submiting nb items form, relaod with the good nb of items
			$('#pagination form').submit(function() {
				val = $('#pagination #nb_item').val();
				$('#pagination #nb_item').children().each(function(it, option) {
					if (option.value == val)
						$(option).attr('selected', 'selected');
					else
						$(option).removeAttr('selected');
				});
				// Reload products and pagination
				reloadContent();
				return false;
			});
			if (typeof(ajaxCart) != "undefined")
				ajaxCart.overrideButtonsInThePage();

			if (typeof(reloadProductComparison) == 'function')
				reloadProductComparison();
			initSliders();

			// Currente page url
			if (typeof(current_friendly_url) == 'undefined')
				current_friendly_url = '#';

			// Get all sliders value
			$(['price', 'weight']).each(function(it, sliderType)
			{
				if ($('#layered_'+sliderType+'_slider'))
				{
					// Check if slider is enable & if slider is used
					if(typeof($('#layered_'+sliderType+'_slider').slider('values', 0)) != 'object')
						if ($('#layered_'+sliderType+'_slider').slider('values', 0) != $('#layered_'+sliderType+'_slider').slider('option' , 'min')
						|| $('#layered_'+sliderType+'_slider').slider('values', 1) != $('#layered_'+sliderType+'_slider').slider('option' , 'max'))
							current_friendly_url += '/'+sliderType+'-'+$('#layered_'+sliderType+'_slider').slider('values', 0)+'-'+$('#layered_'+sliderType+'_slider').slider('values', 1)
				}
			});
			if (current_friendly_url == '#')
				current_friendly_url = '#/';
			window.location = current_friendly_url;
			lockLocationChecking = true;

			if(slideUp)
				$.scrollTo('#product_list', 400);
		}
	});
	ajaxQueries.push(ajaxQuery);
}




