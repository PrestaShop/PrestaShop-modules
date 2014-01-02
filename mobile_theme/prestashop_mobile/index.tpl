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

{include file="$tpl_dir./modules/homefeatured/homefeatured.tpl"}
{include file="$tpl_dir./modules/blockcategories/blockcategories.tpl"}

<div id="jqm_sitemap_bottom_block" class="block">
  <ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="{$ps_mobile_styles.PS_MOBILE_THEME_LIST_HEADERS}">
    <li data-role="list-divider">{l s='Site map'}</li>
    {if isset($logged) && $logged}
    <li><a data-ajax="false" href="{$link->getPageLink('my-account.php', true)}">{l s='My Account'}</a></li>
    <li><a data-ajax="false" href="{$base_dir}?mylogout">{l s='Logout'}</a></li>
    {else}
    <li><a data-ajax="false" href="{$link->getPageLink('authentication.php', true)}">{l s='Login / Register'}</a></li>
    {/if}
	<li><a href="{$link->getPageLink('sitemap.php', true)}">{l s='Sitemap'}</a></li>
  </ul>
</div>
{include file="$tpl_dir./footer-page.tpl"}