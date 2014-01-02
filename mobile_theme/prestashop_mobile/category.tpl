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

{if !isset($smarty.get.id_category)}
{assign var='id_category' value=0}
{else}
{assign var='id_category' value=$smarty.get.id_category|intval}
{/if}

{include file="$tpl_dir./header-page.tpl" page=$id_category}

{include file="$tpl_dir./breadcrumb.tpl"}
{include file="$tpl_dir./errors.tpl"}

{if isset($category)}
	{if $category->id AND $category->active}

		<div class="ui-body ui-body-{$ps_mobile_styles.PS_MOBILE_THEME_HEADINGS}" data-theme="{$ps_mobile_styles.PS_MOBILE_THEME_HEADINGS}">
			<h1>{$category->name|escape:'htmlall':'UTF-8'}{if isset($categoryNameComplement)} {$categoryNameComplement|escape:'htmlall':'UTF-8'}{/if}{if $products|@count} - {$products|@count} {l s='product(s)'}{/if}</h1>
			{if isset($category->description)}<p>{$category->description}</p>{/if}
		</div>

		{if isset($subcategories)}
		<ul data-role="listview" data-inset="true" data-theme="{$ps_mobile_styles.PS_MOBILE_THEME_LIST_HEADERS}" data-dividertheme="{$ps_mobile_styles.PS_MOBILE_THEME_LIST_HEADERS}">
			<li data-role="list-divider">{l s='Subcategories'}</li>
			{foreach from=$subcategories item=subcategory}
			<li>
				<a data-transition="slide" href="{$link->getCategoryLink($subcategory.id_category, $subcategory.link_rewrite)|escape:'htmlall':'UTF-8'}" title="{$subcategory.name|escape:'htmlall':'UTF-8'}">{$subcategory.name|escape:'htmlall':'UTF-8'}</a>
			</li>
			{/foreach}
		</ul>
		<br class="clear"/>
		{/if}

		{if $products}
				{include file="$tpl_dir./product-sort.tpl"}
				{include file="$tpl_dir./product-list.tpl" products=$products}
				{include file="$tpl_dir./pagination.tpl"}
		{elseif !isset($subcategories)}
				<p class="warning">{l s='There are no products in this category.'}</p>
		{/if}
	{elseif $category->id}
		<p class="warning">{l s='This category is currently unavailable.'}</p>
	{/if}
{/if}

{include file="$tpl_dir./footer-page.tpl" page=$id_category}
