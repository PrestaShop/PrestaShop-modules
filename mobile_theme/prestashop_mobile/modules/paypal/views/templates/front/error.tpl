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

{include file="$tpl_dir./errors.tpl"}

{capture name=path}{l s='Error' mod='paypal'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{$message}</h2>
	{if isset($logs) && $logs}
		<div class="error" style="padding: 10px; margin: 15px">
			<p><b>{l s='Please refer to logs:' mod='paypal'}</b></p>
			<ol>
			{foreach from=$logs key=key item=log}
				<li>{$log}</li>
			{/foreach}
			</ol>
			<p><a href="{$base_dir}" class="button_small" title="{l s='Back' mod='paypal'}">&laquo; {l s='Back' mod='paypal'}</a></p>
		</div>
	{/if}
<br />

{include file="$tpl_dir./footer-page.tpl"}
