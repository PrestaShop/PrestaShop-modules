{*
* 2007-2013 PrestaShop
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
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if $smarty.const._PS_VERSION_ < 1.5 && isset($use_mobile) && $use_mobile}
	{include file="$tpl_dir./modules/paypal/views/templates/front/order-confirmation.tpl"}
{else}
	{capture name=path}{l s='Order confirmation' mod='paypal'}{/capture}
	{include file="$tpl_dir./breadcrumb.tpl"}

	<h1>{l s='Order confirmation' mod='paypal'}</h1>

	{assign var='current_step' value='payment'}
	{include file="$tpl_dir./order-steps.tpl"}

	{include file="$tpl_dir./errors.tpl"}

	{$HOOK_ORDER_CONFIRMATION}
	{$HOOK_PAYMENT_RETURN}

	<br />

	{if $order}
	<p>{l s='Total of the transaction (taxes incl.) :' mod='paypal'} <span class="bold">{$price}</span></p>
	<p>{l s='Your order ID is :' mod='paypal'} <span class="bold">{$order.id_order}</span></p>
	<p>{l s='Your PayPal transaction ID is :' mod='paypal'} <span class="bold">{$order.id_transaction}</span></p>
	{/if}
	<br />

	{if $is_guest}
		<a href="{$link->getPageLink('guest-tracking.php', true)}?id_order={$order_reference}" title="{l s='Follow my order' mod='paypal'}" data-ajax="false"><img src="{$img_dir}icon/order.gif" alt="{l s='Follow my order'}" class="icon" /></a>
		<a href="{$link->getPageLink('guest-tracking.php', true)}?id_order={$order_reference}" title="{l s='Follow my order' mod='paypal'}" data-ajax="false">{l s='Follow my order' mod='paypal'}</a>
	{else}
		<a href="{$link->getPageLink('history.php', true)}" title="{l s='Back to orders' mod='paypal'}" data-ajax="false"><img src="{$img_dir}icon/order.gif" alt="{l s='Back to orders'}" class="icon" /></a>
		<a href="{$link->getPageLink('history.php', true)}" title="{l s='Back to orders' mod='paypal'}" data-ajax="false">{l s='Back to orders' mod='paypal'}</a>
	{/if}
{/if}
