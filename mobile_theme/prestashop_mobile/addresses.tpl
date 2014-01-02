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

{*
** Retro compatibility for PrestaShop version < 1.4.2.5 with a recent theme
*}

{* Two variable are necessaries to display the address with the new layout system *}
{* Will be deleted for 1.5 version and more *}
{if !isset($multipleAddresses)}
	{$ignoreList.0 = "id_address"}
	{$ignoreList.1 = "id_country"}
	{$ignoreList.2 = "id_state"}
	{$ignoreList.3 = "id_customer"}
	{$ignoreList.4 = "id_manufacturer"}
	{$ignoreList.5 = "id_supplier"}
	{$ignoreList.6 = "date_add"}
	{$ignoreList.7 = "date_upd"}
	{$ignoreList.8 = "active"}
	{$ignoreList.9 = "deleted"}

	{* PrestaShop < 1.4.2 compatibility *}
	{if isset($addresses)}
		{$address_number = 0}
		{foreach from=$addresses key=k item=address}
			{counter start=0 skip=1 assign=address_key_number}
			{foreach from=$address key=address_key item=address_content}
				{if !in_array($address_key, $ignoreList)}
					{$multipleAddresses.$address_number.ordered.$address_key_number = $address_key}
					{$multipleAddresses.$address_number.formated.$address_key = $address_content}
					{counter}
				{/if}
			{/foreach}
		{$multipleAddresses.$address_number.object = $address}
		{$address_number = $address_number  + 1}
		{/foreach}
	{/if}
{/if}

{* Define the style if it doesn't exist in the PrestaShop version*}
{* Will be deleted for 1.5 version and more *}
{if !isset($addresses_style)}
	{$addresses_style.company = 'address_company'}
	{$addresses_style.vat_number = 'address_company'}
	{$addresses_style.firstname = 'address_name'}
	{$addresses_style.lastname = 'address_name'}
	{$addresses_style.address1 = 'address_address1'}
	{$addresses_style.address2 = 'address_address2'}
	{$addresses_style.city = 'address_city'}
	{$addresses_style.country = 'address_country'}
	{$addresses_style.phone = 'address_phone'}
	{$addresses_style.phone_mobile = 'address_phone_mobile'}
	{$addresses_style.alias = 'address_title'}
{/if}

{* Mandatory because of stateManagement.js -- Unused on this page *}
<script type="text/javascript">
countries = new Array();
countriesNeedIDNumber = new Array();
countriesNeedZipCode = new Array();
</script>

{capture name=path}<a href="{$link->getPageLink('my-account.php', true)}">{l s='My account'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='My addresses'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<div class="ui-body ui-body-b">

<h2>{l s='My addresses'}</h2>

{if isset($multipleAddresses) && $multipleAddresses}

  <div data-role="collapsible-set" data-theme="{$ps_mobile_styles.PS_MOBILE_THEME_LIST_HEADERS}" data-content-theme="{$ps_mobile_styles.PS_MOBILE_THEME_HEADINGS}">

	{assign var="adrs_style" value=$addresses_style}
	{foreach from=$multipleAddresses item=address name=myLoop}
	<div data-role="collapsible">
	  <h3>{$address.object.alias}</h3>
	  <p>
	    <ul class="addresses">
	      {foreach from=$address.ordered name=adr_loop item=pattern}
	      {assign var=addressKey value=" "|explode:$pattern}
	      <li>
		{foreach from=$addressKey item=key name="word_loop"}
		<span class="{if isset($addresses_style[$key])}{$addresses_style[$key]}{/if}">
		  {$address.formated[$key]|escape:'htmlall':'UTF-8'}
		</span>
		{/foreach}
	      </li>
	      {/foreach}
	      <li><a href="{$link->getPageLink('address.php', true)}?id_address={$address.object.id|intval}" title="{l s='Update'}">{l s='Update'}</a> / <a href="{$link->getPageLink('address.php', true)}?id_address={$address.object.id|intval}&amp;delete" onclick="return confirm('{l s='Are you sure?'}');" title="{l s='Delete'}">{l s='Delete'}</a></li>
	    </ul>
	  </p>
	</div>
	{/foreach}
  </div>
{else}
<p class="warning">{l s='No addresses available.'}</p>
{/if}

</div>

<div class="clear address_add" style="margin-top: 50px;"><a data-role="button" data-theme="{$ps_mobile_styles.PS_MOBILE_THEME_BUTTONS}" href="{$link->getPageLink('address.php', true)}" title="{l s='Add an address'}" class="button_large">{l s='Add an address'}</a></div>

{include file="$tpl_dir./footer-page.tpl"}
