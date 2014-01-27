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

<script type="text/javascript">
$(document).ready(function()
{literal}
{
	var link = new Array();
{/literal}
{foreach from=$links item=link key=id}
	{if $version15}
		link['{$id}'] = '{$link}';
	{else}
		link[{$id}] = '{$link}';
	{/if}
{/foreach}
{literal}
$('td[class=history_method]').each(function(){
    if ($(this).html() == 'Go Interpay')
    {
	var id = $(this).prev().prev().prev().children('a').html();
{/literal}
{if $version15}
	$(this).next().next().children('a').attr('href', link[id.replace('#', '')].replace('&amp;', '&'));
{else}
	$(this).next().next().children('a').attr('href', link[parseFloat(id.replace('#', ''))].replace('&amp;', '&'));
{/if}
{literal}
	}
});
{/literal}
{if $button}
{literal}
	$('.cart_navigation .exclusive').hide();
	$('.cart_navigation').append("<a style='float:right;clear:right;width:170px;' class='exclusive exclusive_interpay' href='{/literal}{$pathSsl}{literal}payment.php' onclick='return checkAlerts();'>{/literal}{l s='International Checkout' mod='gointerpay'}{literal}</a>");
{/literal}
{/if}

{literal}
$("<div id='interpay_not_supported' class='warning' style='display: none; clear: both; margin: 20px 0;'>{/literal}{l s='The country you selected is not supported by GoInterpay however you will be able to place an order using our standard checkout.' js=1 mod='gointerpay'}{literal}</div>").insertAfter('#order_step');
{/literal}
{if isset($interpay_not_supported) && $interpay_not_supported}
	$('#interpay_not_supported').show();
{else}
	$('#cart_block_shipping_cost').html('{l s='At checkout' mod='gointerpay' js=1}');
{/if}

{$disable_add_to_cart}
{literal}
});

function checkAlerts()
{
	{/literal}
	{if isset($alert)}
	    alert('{$alert}');
	    return false;
	{/if}
	{literal}
	return true;
}
{/literal}
</script>