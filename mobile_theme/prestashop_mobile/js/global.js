/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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

*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

function jqm_change_currency(id_currency, url)
{
	$.ajax({
		type: 'POST',
		headers: { "cache-control": "no-cache" },		
		url: baseDir + 'changecurrency.php' + '?rand=' + new Date().getTime(),
		data: 'id_currency=' + parseInt(id_currency),
		success: function(msg)
		{
			location.replace(url);
		}
	});
}

function jqm_toggle_search_bar() {
	$('.jqm_search_block_top').toggle();
}

$('#jqm_page_authentication_login').live('pageshow', function() {
	$('.link_page_authenticate_login').addClass('ui-btn-active');
	$('.link_page_authenticate_register').removeClass('ui-btn-active');
});

$('#jqm_page_authentication_register').live('pageshow', function() {
	$('.link_page_authenticate_login').removeClass('ui-btn-active');
	$('.link_page_authenticate_register').addClass('ui-btn-active');
});

$('#jqm_page_order').live('pageshow', function() {
	$('#payment_paypal_express_checkout').click(function() { $('#paypal_payment_form').submit(); });
});

$(document).bind('pageshow', function() {
	$('form').attr('data-ajax', 'false');
	$('#HOOK_PAYMENT a').attr('data-ajax', 'false');
});

$('#jqm_page_p404, #jqm_page_index').live('pageshow', jqm_toggle_search_bar);