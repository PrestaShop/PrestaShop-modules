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

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<h3>{l s='Cheque payment' mod='cheque'}</h3>
<form action="{$this_path_ssl}validation.php" method="post">
	<p style="margin-top:20px;">
		- {l s='The total amount of your order is' mod='cheque'}
		<span id="amount" class="price">{displayPrice price=$total}</span>
		{if $use_taxes == 1}
    		{l s='(tax incl.)' mod='cheque'}
    	{/if}
	</p>
	<p>
		-
		{if isset($currencies) && $currencies|@count > 1}
			{l s='We accept several currencies for cheques.' mod='cheque'}
			<br /><br />
			{l s='Choose one of the following:' mod='cheque'}
			<select id="currency_payement" name="currency_payement" onchange="setCurrency($('#currency_payement').val());">
			{foreach from=$currencies item=currency}
				<option value="{$currency.id_currency}" {if isset($currencies) && $currency.id_currency == $cust_currency}selected="selected"{/if}>{$currency.name}</option>
			{/foreach}
			</select>
		{else}
			{l s='We accept the following currency to be sent by cheque:' mod='cheque'}&nbsp;<b>{$currencies.0.name}</b>
			<input type="hidden" name="currency_payement" value="{$currencies.0.id_currency}" />
		{/if}
	</p>
	<p style="margin-top: 20px;">
		{l s='Cheque owner and address information will be displayed on the next page.' mod='cheque'}
		<br /><br />
		<b>{l s='Please confirm your order by clicking \'I confirm my order\'' mod='cheque'}.</b><br />
	</p>
	<p class="cart_navigation submit" style="text-align: center; margin-top: 10px;">
		<a data-role="button" data-icon="back" data-posicon="left" data-mini="true" data-inline="true" href="{$link->getPageLink('order.php', true)}?step=3">{l s='Previous' mod='cheque'}</a>
		<input type="submit" name="submit" data-icon="check" data-mini="true" data-iconpos="right" data-theme="{$ps_mobile_styles.PS_MOBILE_THEME_BUTTONS}" data-inline="true" value="{l s='I confirm my order' mod='cheque'}" />
	</p>
</form>

{include file="$tpl_dir./footer-page.tpl"}