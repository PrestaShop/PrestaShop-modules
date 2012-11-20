{*
* @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
* @copyright Croissance NET
* @version 1.0
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
				{assign var='trans' value=$oSortingOptions->getClause()}
				<a href="?q={$sPrediggoQuery}&refineOption={$oSortingOptions->getSearchRefiningOption()}" >{if $varTranslated.$trans}{$varTranslated.$trans}{else}{$trans}{/if}</a>
			</li>
			{/foreach}
		</ul>
		{/if}

		{if sizeof($aCancellableFiltersGroups)}
			<hr/>
			<ul>
				<li>{l s='Filters selected' mod='prediggo'} <a href="?q={$sPrediggoQuery}">{l s='clear all' mod='prediggo'}</a></li>
				{foreach from=$aCancellableFiltersGroups item="oCancellableOptionGroup"}
					{foreach from=$oCancellableOptionGroup->getFilteringOptions() item="oFilteringOption"}
					<li>
						{assign var='trans' value=$oCancellableOptionGroup->getFilteredAttributeName()}

						{if isset($varTranslated.$trans)}{$varTranslated.$trans}{else}{$trans}{/if} :

						{if $oCancellableOptionGroup->getFilteredAttributeName() == 'sellingprice'}
							{displayPrice price=$oFilteringOption->getRangeValueMin()} {l s='-' mod='prediggo'}
							{displayPrice price=$oFilteringOption->getRangeValueMax()}
						{else}
							{$oFilteringOption->getTextValue()|ucfirst}
						{/if}
						<a href="?q={$sPrediggoQuery}&refineOption={$oFilteringOption->getSearchRefiningOption()}">{l s='clear' mod='prediggo'}</a>
					</li>
					{/foreach}
				{/foreach}
			</ul>
		{/if}

		{if sizeof($aDrillDownGroups)}
			<hr/>
			{foreach from=$aDrillDownGroups item="oDrillDownGroups"}
			<ul>
				{assign var='trans' value=$oDrillDownGroups->getFilteredAttributeName()}
				<li>{if isset($varTranslated.$trans)}{$varTranslated.$trans}{else}{$trans}{/if}</li>
				{foreach from=$oDrillDownGroups->getFilteringOptions() item="oFilteringOption"}
				<li>
					<a href="?q={$sPrediggoQuery}&refineOption={$oFilteringOption->getSearchRefiningOption()}">
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
			<br/>
			{/foreach}
		{/if}
	</div>
</div>
{/if}