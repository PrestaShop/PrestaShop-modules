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
{if $smarty.const._PS_VERSION_ < 1.5 && isset($use_mobile) && $use_mobile}
	{include file="$tpl_dir./modules/paypal/views/templates/front/order-summary.tpl"}
{else}
	{capture name=path}<a href="order.php">{l s='Your shopping cart' mod='paypal'}</a><span class="navigation-pipe"> {$navigationPipe|escape:'htmlall':'UTF-8'} </span> {l s='PayPal' mod='paypal'}{/capture}
	{if $smarty.const._PS_VERSION_ < 1.6}
	{include file="$tpl_dir./breadcrumb.tpl"}
	{/if}
	<h1>{l s='Order summary' mod='paypal'}</h1>

	{assign var='current_step' value='payment'}
	{include file="$tpl_dir./order-steps.tpl"}

	<h3>{l s='PayPal payment' mod='paypal'}</h3>
	<form action="{$form_action|escape:'htmlall':'UTF-8'}" method="post" data-ajax="false">
		<p>
			<img src="{$logos.LocalPayPalLogoMedium}" alt="{l s='PayPal' mod='paypal'}" style="margin-bottom: 5px" />
			<br />{l s='You have chosen to pay with PayPal.' mod='paypal'}
			<br/><br />
		{l s='Here is a short summary of your order:' mod='paypal'}
		</p>
		<p style="margin-top:20px;">
			- {l s='The total amount of your order is' mod='paypal'}
			<span id="amount" class="price"><strong>{$total|escape:'htmlall':'UTF-8'}</strong></span> {if $use_taxes == 1}{l s='(tax incl.)' mod='paypal'}{/if}
		</p>
		<p>
			- {l s='We accept the following currency to be sent by PayPal:' mod='paypal'}&nbsp;<b>{$currency->name|escape:'htmlall':'UTF-8'}</b>
		</p>
		<p>
			<b>{l s='Please confirm your order by clicking \'I confirm my order\'' mod='paypal'}.</b>
		</p>
		<p class="cart_navigation">
			<input type="submit" name="confirmation" value="{l s='I confirm my order' mod='paypal'}" class="exclusive_large" />
		</p>
	</form>
{/if}
