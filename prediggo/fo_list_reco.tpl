{*
* @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
* @copyright Croissance NET
* @version 1.0
*}

{if sizeof($aRecommendations.$hook_name.items)}
<div id="prediggo_reco_{$page_name}_{$hook_name}" class="block prediggo_reco_{$hook_name}">
	<h4>{$aRecommendations.$hook_name.block_title}</h4>
	<div class="block_content">
		<ul class="prediggo_products">
			{foreach from=$aRecommendations.$hook_name.items item="aRecommendation" name="aRecommendationLoop"}
			<li>

				<input type="hidden" value="{$aRecommendation.notificationId|escape:'htmlall':'UTF-8'}" class="notification_id" />
				<a href="{$aRecommendation.link}" alt="{$aRecommendation.name|escape:'htmlall':'UTF-8'}" title="{$aRecommendation.name|escape:'htmlall':'UTF-8'}">
					<img src="{$link->getImageLink($aRecommendation.link_rewrite, $aRecommendation.id_image, 'home')}" alt="{$aRecommendation.name|escape:'htmlall':'UTF-8'}" title="{$aRecommendation.name|escape:'htmlall':'UTF-8'}" />
				</a>
				<h5 class="clear">
					<a href="{$aRecommendation.link}" alt="{$aRecommendation.name|escape:'htmlall':'UTF-8'}" title="{$aRecommendation.name|escape:'htmlall':'UTF-8'}">
						{$aRecommendation.name|escape:'htmlall':'UTF-8'}
					</a>
				</h5>
				<div class="product_desc">
					<a href="{$aRecommendation.link}" alt="{$aRecommendation.name|escape:'htmlall':'UTF-8'}" title="{$aRecommendation.name|escape:'htmlall':'UTF-8'}">
						{$aRecommendation.description_short|strip_tags|escape:'htmlall':'UTF-8'|truncate:100:'...'}
					</a>
				</div>
				<div class="price_container">
					{if $aRecommendation.specific_prices}
        				{assign var='specific_prices' value=$aRecommendation.specific_prices}
        				{if $specific_prices.reduction_type == 'percentage' && ($specific_prices.from == $specific_prices.to OR ($smarty.now|date_format:'%Y-%m-%d %H:%M:%S' <= $specific_prices.to && $smarty.now|date_format:'%Y-%m-%d %H:%M:%S' >= $specific_prices.from))}
	        				<span class="reduction">(-{$specific_prices.reduction*100|floatval}%)</span>
	            		{/if}
	            	{/if}
					<span class="price">{if !$priceDisplay}{displayWtPrice p=$aRecommendation.price}{else}{displayWtPrice p=$aRecommendation.price_tax_exc}{/if}</span>
				</div>
			</li>
			{if !$smarty.foreach.aRecommendationLoop.last}
			<li class="separator"></li>
			{/if}
			{/foreach}
		</ul>
		<br class="clear"/>
	</div>
</div>
{/if}