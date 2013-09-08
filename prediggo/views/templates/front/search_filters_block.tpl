{*
* 2007-2012 PrestaShop
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
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 6594 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{*

{l s='sellingprice' mod='prediggo'}
{l s='genre' mod='prediggo'}
{l s='brand' mod='prediggo'}

{l s='0' mod='prediggo'}
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
{l s='18' mod='prediggo'}
{l s='19' mod='prediggo'}
{l s='20' mod='prediggo'}

{l s='103' mod='prediggo'}
{l s='104' mod='prediggo'}

*}

{if isset($aSortingOptions)}
<div id="prediggo_search_filter_block" class="block">
	<h4>{l s='Prediggo search filters' mod='prediggo'}</h4>
	<div class="block_content">
		{if sizeof($aSortingOptions)}
		<ul>
			<li>{l s='Sortings' mod='prediggo'}</li>
			{foreach from=$aSortingOptions item="oSortingOptions"}
			<li {if $oSearchStatistics->getCurrentSorting() == $oSortingOptions->getClause()}class="current"{/if}>
				<a href="?q={$sPrediggoQuery}&refineOption={$oSortingOptions->getSearchRefiningOption()}{if !$bRewriteEnabled}&fc=module&module=prediggo&controller=search{/if}" >{l s=$oSortingOptions->getClause() mod='prediggo'}</a>
			</li>
			{/foreach}
		</ul>
		{/if}

		{if sizeof($aCancellableFiltersGroups)}
			<ul>
				<li>{l s='Filters selected' mod='prediggo'} <a href="?q={$sPrediggoQuery}" title="{l s='clear all' mod='prediggo'}" class="delete_filter"></a></li>
				{foreach from=$aCancellableFiltersGroups item="oCancellableOptionGroup"}
					{foreach from=$oCancellableOptionGroup->getFilteringOptions() item="oFilteringOption"}
					<li>
						<a href="?q={$sPrediggoQuery}&refineOption={$oFilteringOption->getSearchRefiningOption()}{if !$bRewriteEnabled}&fc=module&module=prediggo&controller=search{/if}" class="delete_filter"></a>
						{assign var='trans' value=$oCancellableOptionGroup->getFilteredAttributeName()}

						{if isset($varTranslated.$trans)}{$varTranslated.$trans}{else}{l s=$trans mod='prediggo'}{/if} :

						{if $oCancellableOptionGroup->getFilteredAttributeName() == 'sellingprice'}
							{displayPrice price=$oFilteringOption->getRangeValueMin()} {l s='-' mod='prediggo'}
							{displayPrice price=$oFilteringOption->getRangeValueMax()}
						{else}
							{$oFilteringOption->getTextValue()|ucfirst}
						{/if}
					</li>
					{/foreach}
				{/foreach}
			</ul>
		{/if}

		{if sizeof($aDrillDownGroups)}
			{foreach from=$aDrillDownGroups item="oDrillDownGroups"}
			<ul>
				<li>{l s=$oDrillDownGroups->getFilteredAttributeName() mod='prediggo'}</li>
				{foreach from=$oDrillDownGroups->getFilteringOptions() item="oFilteringOption"}
				<li>
					<a href="?q={$sPrediggoQuery}&refineOption={$oFilteringOption->getSearchRefiningOption()}{if !$bRewriteEnabled}&fc=module&module=prediggo&controller=search{/if}">
						{if $oDrillDownGroups->getFilteredAttributeName() == 'sellingprice'}
							{displayPrice price=$oFilteringOption->getRangeValueMin()} {l s='-' mod='prediggo'}
							{displayPrice price=$oFilteringOption->getRangeValueMax()}
						{else}
							{$oFilteringOption->getTextValue()|ucfirst}
						{/if}

						{l s='(' mod='prediggo'}{$oFilteringOption->getNbOccurences()|intval}{l s=')' mod='prediggo'}

					</a>
				</li>
				{/foreach}
			</ul>
			{/foreach}
		{/if}
	</div>
</div>
{/if}