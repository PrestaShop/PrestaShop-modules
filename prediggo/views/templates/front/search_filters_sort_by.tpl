{*
* 2007-2013 PrestaShop
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
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{*

{l s='1' mod='prediggo'}
{l s='2' mod='prediggo'}
{l s='3' mod='prediggo'}
{l s='4' mod='prediggo'}
{l s='5' mod='prediggo'}
{l s='6' mod='prediggo'}
{l s='7' mod='prediggo'}
{l s='8' mod='prediggo'}
{l s='9' mod='prediggo'}
{l s='10' mod='prediggo'}
{l s='11' mod='prediggo'}
{l s='12' mod='prediggo'}
{l s='13' mod='prediggo'}
{l s='14' mod='prediggo'}
{l s='15' mod='prediggo'}
{l s='16' mod='prediggo'}
{l s='17' mod='prediggo'}

*}

<form id="productsSortForm{if isset($aSortingOptions)}{/if}" action="{$request|escape:'htmlall':'UTF-8'}" class="productsSortForm">
    <p class="select">
        <label for="selectPrductSort{if isset($aSortingOptions)}{/if}">{l s='Sort by'}</label>
        <select id="selectPrductSort{if isset($aSortingOptions)}{/if}" class="selectProductSort" onChange="location = this.options[this.selectedIndex].value;">
            <option value="{$orderbydefault|escape:'htmlall':'UTF-8'}:{$orderwaydefault|escape:'htmlall':'UTF-8'}" {if $orderby eq $orderbydefault}selected="selected"{/if}>{l s='--'}</option>
            {foreach from=$aSortingOptions item="oSortingOptions"}
            <option value="?q={$sPrediggoQuery}&refineOption={$oSortingOptions->getSearchRefiningOption()}{if !$bRewriteEnabled}&fc=module&module=prediggo&controller=search{/if}" >{l s=$oSortingOptions->getClause() mod='prediggo'}</option>
            {/foreach}
        </select>
    </p>
</form>
<!-- /Sort products -->
