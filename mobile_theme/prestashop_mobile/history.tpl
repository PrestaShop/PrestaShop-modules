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

{include file="$tpl_dir./header-page.tpl"}

{capture name=path}<a href="{$link->getPageLink('my-account.php', true)}">{l s='My account'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Order history'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}
{include file="$tpl_dir./errors.tpl"}

{if $slowValidation}<p class="warning">{l s='If you have just placed an order, it may take a few minutes for it to be validated. If your order does not appear please refresh the page.'}</p>{/if}

{if $orders && count($orders)}
<ul data-role="listview" data-inset="true">
<li data-role="list-divider">
  <table style="width: 100%">
	<tr>
	  <th style="width: 25%; text-align: center;">{l s='Order'}</th>
	  <th style="width: 25%; text-align: center;">{l s='Date'}</th>
	  <th style="width: 50%; text-align: center;">{l s='Status'}</th>
	</tr>
  </table>
</li>
{foreach from=$orders item=order name=myLoop}
<li>
  <table style="width: 100%">
	  <tr class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{else}item{/if} {if $smarty.foreach.myLoop.index % 2}alternate_item{/if}">
		<td  style="width: 25%;">
		  {if isset($order.invoice) && $order.invoice && isset($order.virtual) && $order.virtual}<img src="{$img_dir}icon/download_product.gif" class="icon" alt="{l s='Products to download'}" title="{l s='Products to download'}" />{/if}
		  {l s='#'}{$order.id_order|string_format:"%06d"}
		</td>
		<td style="width: 25%;">{dateFormat date=$order.date_add full=0}</td>
		<td style="width: 50%; text-align: center;">{if isset($order.order_state)}{$order.order_state|escape:'htmlall':'UTF-8'}{/if}</td>
	  </tr>
	  <tr>
		<td colspan="3">{l s='Total:'} {displayPrice price=$order.total_paid_real currency=$order.id_currency no_utf8=false convert=false}</td>
	  </tr>
	  <tr>
		<td colspan="3">{l s='Payment method:'} {$order.payment|escape:'htmlall':'UTF-8'} 
		{if (isset($order.invoice) && $order.invoice && isset($order.invoice_number) && $order.invoice_number) && isset($invoiceAllowed) && $invoiceAllowed == true}
		 - <a rel="{$link->getPageLink('pdf-invoice.php', true)}?id_order={$order.id_order|intval}" title="{l s='Invoice'}" data-ajax="false" onclick="location.replace(this.rel);">{l s='Invoice'}</a>
		{/if}
		</td>
	 </tr>
  </table>
</li>
{/foreach}
</ul>
{else}
<p class="warning">{l s='You have not placed any orders.'}</p>
{/if}

{include file="$tpl_dir./footer-page.tpl"}