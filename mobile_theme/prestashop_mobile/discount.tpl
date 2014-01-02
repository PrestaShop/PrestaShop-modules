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

{capture name=path}<a href="{$link->getPageLink('my-account.php', true)}">{l s='My account'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='My Vouchers'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<div class="ui-body ui-body-b">
  <h2>{l s='My Vouchers'}</h2>

  {if isset($discount) && count($discount) && $nbDiscounts}

  <div data-role="collapsible-set" data-theme="c" data-content-theme="d">

    {foreach from=$discount item=discountDetail name=myLoop}
    <div data-role="collapsible">
      <h3>{$discountDetail.description|escape:'htmlall':'UTF-8'} ({$discountDetail.name|escape:'htmlall':'UTF-8'})</h3>
      <p>
	<table>

	  <tr>
	    <td>{l s='Quantity:'}</td>
	    <td>{$discountDetail.quantity_for_user|intval}</td>
	  </tr>

	  <tr>
	    <td>{l s='Value:'}</td>
	    <td>
	      {if $discountDetail.id_discount_type == 1}
	        {$discountDetail.value|escape:'htmlall':'UTF-8'}% ({l s='Tax included'})
	      {elseif $discountDetail.id_discount_type == 2}
	        {convertPrice price=$discountDetail.value|intval}  ({l s='Tax included'})
	      {else}
	        {l s='Free shipping'}
	      {/if}
	    </td>
	  </tr>

	  <tr>
	    <td>{l s='Minimum:'}</td>
	    <td>
	      {if $discountDetail.minimal == 0}
	        {l s='none'}
	      {else}
	        {convertPrice price=$discountDetail.minimal}
	      {/if}
	    </td>
	  </tr>

	  <tr>
	    <td>{l s='Cumulative:'}</td>
	    <td>{if $discountDetail.cumulable == 1}{l s='Yes'}{else}{l s='No'}{/if}</td>
	  </tr>

	  <tr>
	    <td>{l s='Expiration date:'}</td>
	    <td>{dateFormat date=$discountDetail.date_to|escape:'htmlall':'UTF-8'}</td>
	  </tr>
	</table>
      </p>
    </div>
    {/foreach}

  </div>

  {else}
  <p>{l s='You do not possess any vouchers.'}</p>
  {/if}

</div>

{include file="$tpl_dir./footer-page.tpl"}
