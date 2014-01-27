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

{capture name=path}{l s='Our stores'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}


<div class="ui-body ui-body-b">

	{if $stores|@count}
	<h3>{l s='Here is a list of our stores, feel free to contact us:'}</h3>

	<ul data-role="listview" data-inset="true">
	{foreach from=$stores item=store}
		<li>
		{if $store.has_picture}<img src="{$img_store_dir|escape:'htmlall':'UTF-8'}{$store.id_store|intval}-medium.jpg" alt="" width="{$mediumSize.width|intval}" height="{$mediumSize.height|intval}" />{/if}
			<b>{$store.name|escape:'htmlall':'UTF-8'}</b><br />
			{$store.address1|escape:'htmlall':'UTF-8'}<br />
			{if $store.address2}{$store.address2|escape:'htmlall':'UTF-8'}<br />{/if}
			{$store.postcode|escape:'htmlall':'UTF-8'} {$store.city|escape:'htmlall':'UTF-8'}{if $store.state}, {$store.state|escape:'htmlall':'UTF-8'}{/if}<br />
			{$store.country|escape:'htmlall':'UTF-8'}<br />
			{if $store.phone}{l s='Phone:'} {$store.phone|escape:'htmlall':'UTF-8'}{/if}
			{if isset($store.working_hours)}{$store.working_hours|escape:'htmlall':'UTF-8'}{/if}
		</li>
	{/foreach}
	</ul>
	{/if}
</div>


{include file="$tpl_dir./footer-page.tpl"}
