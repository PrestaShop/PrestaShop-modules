<!--
 * @author    NetReviews (www.avis-verifies.com) - Contact: contact@avis-verifies.com
 * @category  traduction
 * @copyright NetReviews
 * @license   NetReviews 
 * @date 09/04/2014
 -->
<li>
	<a href="#idTabavisverifies" class="avisverifies_tab" id="tab_avisverifies">
		{$count_reviews|intval}
		{if $count_reviews > 1}
			{l s='Reviews' mod='netreviews'} 
		{else}
			{l s='Review' mod='netreviews'} 
		{/if} 
	</a>
</li>