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

{capture name=path}{l s='Suppliers'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h1>{l s='Suppliers'}</h1>

{if isset($errors) AND $errors}
	{include file="$tpl_dir./errors.tpl"}
{else}
	<p>{strip}
		<span class="bold">
			{if $nbSuppliers == 0}{l s='There are no suppliers.'}
			{else}
				{if $nbSuppliers == 1}{l s='There is'}{else}{l s='There are'}{/if}&#160;
				{$nbSuppliers}&#160;
				{if $nbSuppliers == 1}{l s='supplier.'}{else}{l s='suppliers.'}{/if}
			{/if}
		</span>{/strip}
	</p>

	{if $nbSuppliers > 0}
		<ul id="suppliers_list" class="clear" data-role="listview">
			{foreach from=$suppliers item=supplier name=suppliers}
			<li style="height: 81px;">
				<a href="{$link->getsupplierLink($supplier.id_supplier, $supplier.link_rewrite)|escape:'htmlall':'UTF-8'}" title="{$supplier.name|escape:'htmlall':'UTF-8'}">
					<img src="{$img_sup_dir}{$supplier.image|escape:'htmlall':'UTF-8'}-medium.jpg" alt="" width="{$mediumSize.width}" height="{$mediumSize.height}" />
					<h2 style="margin-top: 4px;">{$supplier.name|escape:'htmlall':'UTF-8'}</h2>
					<p>{$supplier.description|strip_tags:'UTF-8'}</p>
					<div>
						<span class="price" style="display: inline;">{$supplier.nb_products} {if $supplier.nb_products > 1}{l s='products'}{else}{l s='product'}{/if}</span>
					</div>
				</a>
			</li>
			{/foreach}
		</ul>
		<br class="clear"/>
		{include file="$tpl_dir./pagination.tpl"}
	{/if}
{/if}

{include file="$tpl_dir./footer-page.tpl"}