{*
* @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
* @copyright Croissance NET
* @version 1.0
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
	<h3><span class="big">{$nbProducts|intval}</span>&nbsp;{if $nbProducts == 1}{l s='result has been found.' mod='prediggo'}{else}{l s='results have been found.' mod='prediggo'}{/if}</h3>
	{if !empty($aDidYouMeanWords)}
		{foreach from=$aDidYouMeanWords item="oDidYouMeanWord"}
			<span class="did_you_mean">{l s='Did you mean:' mod='prediggo'}</span>
			<a href="?q={$oDidYouMeanWord->getWord()|escape:'htmlall':'UTF-8'}&refineOption={$oDidYouMeanWord->getSearchRefiningOption()|escape:'htmlall':'UTF-8'}">{$oDidYouMeanWord->getWord()|escape:'htmlall':'UTF-8'}</a>
		{/foreach}
	{/if}
	{include file="$tpl_dir./product-compare.tpl"}
	{include file="$tpl_dir./../../modules/prediggo/product-list.tpl" products=$aPrediggoProducts}
	{include file="$tpl_dir./product-compare.tpl"}
	{include file="$tpl_dir./../../modules/prediggo/pagination.tpl"}

{/if}
