{*
* @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
* @copyright Croissance NET
* @version 1.0
*}

<a href="{$aRecommendation.link}" alt="{$aRecommendation.name|escape:'htmlall':'UTF-8'}" title="{$aRecommendation.name|escape:'htmlall':'UTF-8'}">
	<img src="{$link->getImageLink($aRecommendation.link_rewrite, $aRecommendation.id_image, 'home')}" alt="{$aRecommendation.name|escape:'htmlall':'UTF-8'}" title="{$aRecommendation.name|escape:'htmlall':'UTF-8'}" />
</a>
<h5 class="clear">
	<a href="{$aRecommendation.link}" alt="{$aRecommendation.name|escape:'htmlall':'UTF-8'}" title="{$aRecommendation.name|escape:'htmlall':'UTF-8'}">
		{$aRecommendation.name|escape:'htmlall':'UTF-8'}
	</a>
</h5>
<div class="price_container">
	{if $aRecommendation.specific_prices}
		{assign var='specific_prices' value=$aRecommendation.specific_prices}
		{if $specific_prices.reduction_type == 'percentage' && ($specific_prices.from == $specific_prices.to OR ($smarty.now|date_format:'%Y-%m-%d %H:%M:%S' <= $specific_prices.to && $smarty.now|date_format:'%Y-%m-%d %H:%M:%S' >= $specific_prices.from))}
 			<span class="reduction">(-{$specific_prices.reduction*100|floatval}%)</span>
		{/if}
	{/if}
	<span class="price">{if !$priceDisplay}{displayWtPrice p=$aRecommendation.price}{else}{displayWtPrice p=$aRecommendation.price_tax_exc}{/if}</span>
</div>