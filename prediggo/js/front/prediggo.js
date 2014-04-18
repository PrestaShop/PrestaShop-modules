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
* @author PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2014 PrestaShop SA
* @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
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
			baseDir+'modules/prediggo/xhr/xhr.php',
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
	
	$(document).ajaxComplete(function(event, jqxhr, settings){
		if (settings.url != undefined
		&& settings.url.indexOf('blocklayered-ajax.php') >= 0
		&& settings.url.indexOf('id_category_layered=') >= 0)
		{
			var iPos = settings.url.indexOf('?');
			if(iPos >= 0)
				$.ajax(
				{
					type: 'GET',
					url: baseDir + 'modules/prediggo/xhr/xhr.php',
					data: settings.url.substr(iPos+1),
					dataType: 'json',
					success: function(result)
					{
						$('#prediggo_reco_category_blocklayered').remove();
						$('ul#product_list').before(result);
					}
				});
		}
	});
});

function notifyPrediggo(notificationId)
{
	$.ajax({
		type: 'POST',
		async: false,
		dataType: 'json',
		url: baseDir+'modules/prediggo/xhr/xhr.php',
		data: {'nId':notificationId}
	});
}