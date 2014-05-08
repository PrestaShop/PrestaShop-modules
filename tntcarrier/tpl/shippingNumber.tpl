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
<fieldset style="width:400px">
			<legend><img src="../img/admin/delivery.gif" />{l s='Shipping information'}</legend>
			{if isset($weight)}
			{l s='Please make sure each package weight a maximum of' mod='tntcarrier'} {$weight} {l s='Kg' mod='tntcarrier'}.<br/><br/>
			{l s='In order to have a shipping number, please change the order\'s status to Shipped' mod='tntcarrier'}<br/><br/>
			{/if}
			{$var.error}
			{if $var.shipping_numbers && $var.sticker}
			<span style='font-weight:bold'>{l s='ShippingNumber' mod='tntcarrier'} : </span>
			<div style="text-align:right">
			{foreach from=$var.shipping_numbers item=v}
			{if $v.shipping_number}
			{$v.shipping_number}<br/>
			{/if}
			{/foreach}
			</div>
			<span style='font-weight:bold'>{l s='Sticker' mod='tntcarrier'} : </span><a style="color:blue" href="{$var.sticker}">{l s="PDF File"}</a><br/>
			<span style='font-weight:bold'>{l s='Expedition' mod='tntcarrier'} : </span>{$var.date}<br/><br/>
			<span style='font-weight:bold'>{l s='Shipping address' mod='tntcarrier'} :</span><br/>
			{$var.place}<br/><br/>
			<!--<span style='font-weight:bold'>{l s='Customer address' mod='tntcarrier'} :</span><br/>
			{$var.customer}<br/><br/>-->
			{if $var.relay != ''}
			<span style='font-weight:bold'>{l s='Relay Package address' mod='tntcarrier'} :</span><br/>
			{$var.relay}
			{/if}
			{/if}
</fieldset>
