{*
* 2007-2013 PrestaShop
*
*
*  @author Feedaty <info@feedaty.com>
*  @copyright  2012-2014 Feedaty
*  @version  Release: 1.1.135 $
*}
<div id="feedaty_reviews" class="rte">
    {if count($data_review.Feedbacks) neq 0}
        {foreach $data_review.Feedbacks as $review}
            <p>
                <span class="stars">{$review.stars_html}</span>
                <span class="review">{$review.ProductReview|escape:'htmlall':'UTF-8'}</span>
            </p>
        {/foreach}
        <p>{$feedaty_link}</p>
    {else}
        <p>{l s='There are no reviews' mod='feedaty'}</p>
    {/if}

</div>