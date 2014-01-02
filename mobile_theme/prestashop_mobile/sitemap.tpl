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

{capture name=path}{l s='Sitemap'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Sitemap'}</h2>

<div id="sitemap_content">

	<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="{$ps_mobile_styles.PS_MOBILE_THEME_LIST_HEADERS}">
      <li data-role="list-divider">{l s='Your Account'}</li>
      <li><a data-ajax="false" href="{$link->getPageLink('my-account.php', true)}">{l s='Your Account'}</a></li>
      <li><a href="{$link->getPageLink('identity.php', true)}">{l s='Personal information'}</a></li>
      <li><a href="{$link->getPageLink('addresses.php', true)}">{l s='Addresses'}</a></li>
      {if $voucherAllowed}<li><a href="{$link->getPageLink('discount.php', true)}">{l s='Discounts'}</a></li>{/if}
      <li><a href="{$link->getPageLink('history.php', true)}">{l s='Order history'}</a></li>
    </ul>

	 <ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="{$ps_mobile_styles.PS_MOBILE_THEME_LIST_HEADERS}">
      <li data-role="list-divider">{l s='Pages'}</li>
      {if isset($categoriescmsTree.children)}
      {foreach from=$categoriescmsTree.children item=child name=sitemapCmsTree}
      {if (isset($child.children) && $child.children|@count > 0) || $child.cms|@count > 0}
      {include file="$tpl_dir./category-cms-tree-branch.tpl" node=$child}
      {/if}
      {/foreach}
      {/if}
      {foreach from=$categoriescmsTree.cms item=cms name=cmsTree}
      <li><a href="{$cms.link|escape:'htmlall':'UTF-8'}" title="{$cms.meta_title|escape:'htmlall':'UTF-8'}">{$cms.meta_title|escape:'htmlall':'UTF-8'}</a></li>
      {/foreach}
      <li><a href="{$link->getPageLink('contact-form.php', true)}">{l s='Contact'}</a></li>
      {if $display_store}<li class="last"><a href="{$link->getPageLink('stores.php')}" title="{l s='Our stores'}">{l s='Our stores'}</a></li>{/if}
    </ul>
	
	<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="{$ps_mobile_styles.PS_MOBILE_THEME_LIST_HEADERS}">
      <li data-role="list-divider">{l s='Our offers'}</li>
      <li><a href="{$link->getPageLink('new-products.php')}">{l s='New products'}</a></li>
      {if !$PS_CATALOG_MODE}
      <li><a href="{$link->getPageLink('best-sales.php')}">{l s='Best sellers'}</a></li>
      <li><a href="{$link->getPageLink('prices-drop.php')}">{l s='Promotions'}</a></li>
      {/if}
      {if $display_manufacturer_link OR $PS_DISPLAY_SUPPLIERS}<li><a href="{$link->getPageLink('manufacturer.php')}">{l s='Manufacturers'}</a></li>{/if}
      {if $display_supplier_link OR $PS_DISPLAY_SUPPLIERS}<li><a href="{$link->getPageLink('supplier.php')}">{l s='Suppliers'}</a></li>{/if}
    </ul>
	
	{if isset($categoriesTree.children)}
    {assign var='block_category_mobile' value=$categoriesTree.children}
    <ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="{$ps_mobile_styles.PS_MOBILE_THEME_LIST_HEADERS}">
      <li data-role="list-divider">{l s='Categories'}</li>
      {foreach from=$block_category_mobile item=child name=blockCategTree}
      <li>
	<a href="{$child.link}" title="{$child.desc|escape:html:'UTF-8'}">{$child.name|escape:html:'UTF-8'}</a>
      </li>
      {/foreach}
    </ul>
    {/if}
</div>

{include file="$tpl_dir./footer-page.tpl"}