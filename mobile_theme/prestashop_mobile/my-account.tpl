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

{* In case we are on the 'Options' page, we do not diplsay breadcrumb *}
{if !isset($options)}
{capture name=path}{l s='My account'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}
{/if}

<div id="jqm_my_account" class="block">
  <div class="block_content">
    <ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="{$ps_mobile_styles.PS_MOBILE_THEME_LIST_HEADERS}">
      <li data-role="list-divider">{l s='Welcome to your account'}</li>
	  <li><a href="{$link->getPageLink('identity.php', true)}" title="{l s='My personal information'}">{l s='My personal information'}</a></li>
      <li><a href="{$link->getPageLink('history.php', true)}" title="{l s='Orders'}">{l s='My orders '}</a></li>
      <li><a href="{$link->getPageLink('addresses.php', true)}" title="{l s='Addresses'}">{l s='My adresses'}</a></li>
      {if isset($voucherAllowed) && $voucherAllowed}
      <li><a href="{$link->getPageLink('discount.php', true)}" title="{l s='Vouchers'}">{l s='My vouchers'}</a></li>
      {/if}
    </ul>

    <ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="{$ps_mobile_styles.PS_MOBILE_THEME_LIST_HEADERS}">
      <li>
		<a href="{$base_dir}?mylogout">{l s='Logout'}</a>
      </li>
    </ul>
  </div>
</div>

{include file="$tpl_dir./footer-page.tpl"}