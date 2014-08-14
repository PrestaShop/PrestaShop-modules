<!--
 * @author    NetReviews (www.avis-verifies.com) - Contact: contact@avis-verifies.com
 * @category  traduction
 * @copyright NetReviews
 * @license   NetReviews 
 * @date 09/04/2014
 -->
{foreach from=$reviews key=k_review item=review}	

	<div class="reviewAV">

		<ul class="reviewInfosAV">
			<li style="text-transform:capitalize">{$review['customer_name']|escape:'htmlall'}</li>
			<li>&nbsp;{l s='the' mod='netreviews'} {$review['horodate']|escape:'htmlall'}</li>
			<li class="rateAV"><img src="{$modules_dir}netreviews/img/etoile{$review['rate']|escape:'htmlall'}.png" width="80" height="15" /> {$review['rate']|escape:'htmlall'}/5</li>
		</ul>	

		<div class="triangle-border top">{$review['avis']|escape:'htmlall'}</div>

	{if $review['discussion']}
		{foreach from=$review['discussion'] key=k_discussion item=discussion}

		<div class="triangle-border top answer" {if $k_discussion > 0} review_number={$review['id_product_av']|escape:'htmlall'} style= "display: none" {/if}>
			<span>&rsaquo; {l s='Comment from' mod='netreviews'}  <b style="text-transform:capitalize; font-weight:normal">{$discussion['origine']|escape:'htmlall'}</b> {l s='the' mod='netreviews'} {$discussion['horodate']|escape:'html'}</span>
			<p class="answer-bodyAV">{$discussion['commentaire']|escape:'htmlall'}</p>
		</div>
		
			
		{/foreach}

		{if $k_discussion > 0}
			<a href="javascript:switchCommentsVisibility('{$review['id_product_av']|strip}')" style="padding-left: 6px;margin-left: 30px; display: block; font-style:italic" id="display{$review['id_product_av']|escape:strip}" class="display-all-comments" review_number={$review['id_product_av']|strip} >{l s='Show exchanges' mod='netreviews'}</a>

			<a href="javascript:switchCommentsVisibility('{$review['id_product_av']|strip}')" style="padding-left: 6px;margin-left: 30px; display: none; font-style:italic" id="hide{$review['id_product_av']|escape:strip}" class="display-all-comments" review_number={$review['id_product_av']|strip} >{l s='Hide exchanges' mod='netreviews'}</a>
			</a>
	  	{/if}
	{/if}

	</div>
{/foreach}








