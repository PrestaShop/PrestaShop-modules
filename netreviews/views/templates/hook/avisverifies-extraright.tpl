<!--
 * @author    NetReviews (www.avis-verifies.com) - Contact: contact@avis-verifies.com
 * @category  traduction
 * @copyright NetReviews
 * @license   NetReviews 
 * @date 09/04/2014
 -->
<div id="av_product_award">

<div id="top">
	<div class="ratingWrapper">
    	<div class="ratingInner" style="width:{$av_rate_percent|intval}%;"></div>
    </div>
	<b>{$av_nb_reviews|intval} &nbsp;

	{if $av_nb_reviews > 1}
		{l s='reviews' mod='netreviews'}
	{else}
		{l s='review' mod='netreviews'}
	{/if}

	</b>
</div>
<div id="bottom"><a href="javascript:()" id="AV_button">{l s='See the reviews' mod='netreviews'}</a></div>
	<img id="sceau" src="{$modules_dir|escape:'htmlall'}netreviews/img/{l s='Sceau_100_en.png' mod='netreviews'}" />
</div>




	