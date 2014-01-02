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

{if !isset($no_header) || !$no_header}
<div id="{if isset($forcepage)}{$forcepage|escape:'html':'UTF-8'}{else}jqm_page_{if $page_name == '404'}p{/if}{$page_name|escape:'htmlall':'UTF-8'}{if isset($page) && $page}_{$page}{/if}{/if}" data-role="page" data-title="{$meta_title|escape:'htmlall':'UTF-8'}" {if !isset($smarty.capture.forceback)}data-add-back-btn="true" data-back-btn-text="{l s='Back'}"{/if}> <!-- Start of data-role="page" div for jQuery Mobile -->
  {if !$content_only}
  {if isset($restricted_country_mode) && $restricted_country_mode}
	<p>{l s='You cannot place a new order from your country.'} <span class="bold">{$geolocation_country|escape:'htmlall':'UTF-8'}</span></p>
  {/if}
  <div>
    <div data-role="header" data-theme="{$ps_mobile_styles.PS_MOBILE_THEME_HEADER_FOOTER}">
      <h1>{$meta_title|escape:'htmlall':'UTF-8'}</h1>
      {if isset($smarty.capture.forceback)}<a href="{$smarty.capture.forceback|escape:'html':'UTF-8'}" data-ajax="false" data-icon="back" class="ui-btn-left">{l s='Back'}</a>{/if}
      <a data-ajax="false" href="{$base_dir}" data-icon="home" data-iconpos="notext" class="ui-btn-right" data-transition="flip">{l s='Home'}</a>
	  <a href="#" onclick="jqm_toggle_search_bar();" data-icon="search" data-iconpos="notext" class="ui-btn-right jqm_toggle_search" style="margin-right: 35px;">{l s='Search'}</a>
    </div>
    <div data-role="content" class="main-content" {if $page_name == 'order-confirmation' || $page_name == 'module-bankwire-payment' || $page_name == 'module-cheque-payment' || $page_name == 'order' || strpos($page_name, 'module-paypal') !== false}style="padding-top: 0; padding-bottom: 0;"{/if}>
		{include file="$tpl_dir./modules/blocksearch/blocksearch-top.tpl"}
  {/if}
{/if}
{if (isset($smarty.get.id_product) || isset($smarty.post.id_product)) && $page_name == 'cart' && isset($errors) && $errors}
	{include file="$tpl_dir./errors.tpl"}
{/if}