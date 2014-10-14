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

{capture name=path}{l s='Prediggo Search' mod='prediggo'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}
{if isset($oSearchStatistics)}
	{assign var='nbProducts' value=$oSearchStatistics->getTotalSearchResults()}
{else}
	{assign var='nbProducts' value=0}
{/if}
<h1>
{l s='Search' mod='prediggo'}&nbsp;{if $nbProducts > 0}"{$sPrediggoQuery}"{/if}
</h1>
{include file="$tpl_dir./errors.tpl"}
{if !$nbProducts}
	<p class="warning">
		{if isset($sPrediggoQuery) && $sPrediggoQuery}
			{l s='No results found for your search' mod='prediggo'}&nbsp;"{if isset($sPrediggoQuery)}{$sPrediggoQuery|escape:'htmlall':'UTF-8'}{/if}"
		{else}
			{l s='Please type a search keyword' mod='prediggo'}
		{/if}
	</p>
{else}
	{if $aCustomRedirections|@sizeof>0 && $bSearchandizingActive}
		{foreach from=$aCustomRedirections item="oCustomRedirection"}
			<div class="searchandizingBox">
				<a href="{$oCustomRedirection->getTargetUrl()}">
					<p>
						<img src="{$oCustomRedirection->getPictureUrl()|escape:'htmlall':'UTF-8'}" alt="{$oCustomRedirection->getLabel()|escape:'htmlall':'UTF-8'}"  />
					</p>
					<p>
						<span>{$oCustomRedirection->getLabel()|escape:'htmlall':'UTF-8'}</span>
					</p>
				</a>
			</div>
		{/foreach}
	{/if}
	<h3 class="nbresult"><span class="big">{$nbProducts|intval}</span>&nbsp;{if $nbProducts == 1}{l s='result has been found.' mod='prediggo'}{else}{l s='results have been found.' mod='prediggo'}{/if}</h3>
	{if !empty($aDidYouMeanWords)}
		<span class="did_you_mean">{l s='Did you mean:' mod='prediggo'}</span>
		{foreach from=$aDidYouMeanWords item="oDidYouMeanWord" name=aDidYouMeanWordsLoop}
			<a href="?q={$oDidYouMeanWord->getWord()|escape:'htmlall':'UTF-8'}&refineOption={$oDidYouMeanWord->getSearchRefiningOption()|escape:'htmlall':'UTF-8'}">{$oDidYouMeanWord->getWord()|escape:'htmlall':'UTF-8'}</a>
			{if !$smarty.foreach.aDidYouMeanWordsLoop.last}, {/if}
		{/foreach}
	{/if}
	<div class="content_sortPagiBar">
		{include file="./pagination.tpl"}
		<div class="sortPagiBar">
            {include file="./search_filters_sort_by_cat.tpl"}
			{include file="$tpl_dir./product-compare.tpl"}
		</div>
	</div>
	{include file="$tpl_dir./product-list.tpl" products=$aPrediggoProducts}
	<div class="content_sortPagiBar">
		<div class="sortPagiBar">
            {include file="./search_filters_sort_by_cat.tpl"}
			{include file="$tpl_dir./product-compare.tpl"}
		</div>
		{include file="./pagination.tpl"}
	</div>

{/if}
