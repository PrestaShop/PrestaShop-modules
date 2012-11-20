{*
* @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
* @copyright Croissance NET
* @version 1.0
*}

{if sizeof($aChangePageLinks)}
	<ul class="pagination clear">
		{foreach from=$aChangePageLinks item="oChangePageOption"}
		<li {if $oChangePageOption->getLabel() == 'back'}id="pagination_previous"{elseif $oChangePageOption->getLabel() == 'next'}id="pagination_next"{/if} {if $oSearchStatistics->getCurrentPageNumber()|intval == $oChangePageOption->getLabel()|intval}class="current"{/if}>
			{if $oSearchStatistics->getCurrentPageNumber()|intval == $oChangePageOption->getLabel()|intval}
				<span>{$oChangePageOption->getLabel()}</span>
			{else}
				<a href="?q={$sPrediggoQuery}&refineOption={$oChangePageOption->getSearchRefiningOption()}">{$oChangePageOption->getLabel()}</a>
			{/if}
		</li>
		{/foreach}
	</ul>
{/if}