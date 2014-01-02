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

<br/>
<fieldset>
	<legend><img src="../modules/gointerpay/logo.gif" alt="" />{l s='Interpay' mod='gointerpay'}</legend>
	<p>{l s='This order has been placed with Interpay, please use the following link to retrieve the invoice:' mod='gointerpay'}
	<b><a target="_blank" href="{$interpay_link|escape:htmlall:'UTF-8'}">{l s='Invoice for order #' mod='gointerpay'}{$interpay_order.orderId|escape:htmlall:'UTF-8'}</a></b> </p>
	{if isset($interpay_message)}<p class="info" style="margin-top: 10px;">{foreach from=$interpay_message item=interpay_val}{$interpay_val}<br/>{/foreach}</p>{/if}
	{if isset($interpay_error)}<p style="color: red;">{$interpay_error|escape:htmlall:'UTF-8'}</p>{/if}
	{if isset($interpay_validate)}<p style="color: green;">{$interpay_validate|escape:htmlall:'UTF-8'}</p>{/if}
	<p style="margin-top: 15px;">{l s='Please find below the original amounts in USD:' mod='gointerpay'}</p>
	<table cellpadding="0" cellspacing="0" class="table">
		<tr>
			<th>{l s='Info' mod='gointerpay'}</th>
			<th>{l s='Amount' mod='gointerpay'}</th>
		</tr>
		<tr>
			<td>{l s='Total products in USD' mod='gointerpay'}</td>
			<td>{convertPrice price=$interpay_order.products}</td>
		</tr>
		<tr>
			<td>{l s='Total shipping in USD' mod='gointerpay'}</td>
			<td>{convertPrice price=$interpay_order.shipping}</td>
		</tr>
		<tr>
			<td>{l s='Total taxes and duties in USD' mod='gointerpay'}</td>
			<td>{convertPrice price=$interpay_order.taxes}</td>
		</tr>
		<tr style="font-weight: bold;">
			<td>{l s='Grand Total in USD' mod='gointerpay'}</td>
			<td>{convertPrice price=$interpay_order.total}</td>
		</tr>
	</table>
</fieldset>
<script type="text/javascript">
{literal}
var json = {/literal}{$interpay_status}{literal};

$('select[name="id_order_state"] option').each(function(){
    var auth = false;
    for (var i = 0; i < json.available.length; i++)
	if (json.available[i] == $(this).val())
	    auth = true;
    if (auth == false)
	$(this).remove();
});
{/literal}
</script>