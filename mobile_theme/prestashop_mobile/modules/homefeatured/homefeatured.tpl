{*
* 2007-2014 PrestaShop
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
*  @copyright  2007-2014 PrestaShop SA

*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<!-- MODULE Home Featured Products -->
<div id="featured-products_block_center_mobile" style="text-align: center;">
{if isset($products) && $products}
	<div id="slider_home" style="padding: 0; margin: 10px 0 0 0;">
		<ul>
		{foreach from=$products item=product name=homeFeaturedProducts}
			<li {if !$smarty.foreach.homeFeaturedProducts.first}style="display: none;"{/if}>
				<a{if isset($product.link)} href="{$product.link}"{/if} title="{$product.name|escape:html:'UTF-8'}" style="text-decoration: none;">
					<img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'large')}" alt="{$product.name|escape:html:'UTF-8'}" style="border-radius: 15px; width: 100%" />
				</a>
				<p style="position: relative; bottom: 25px; background: black; opacity: 0.80; height: 25px; line-height: 25px; color: white; border-radius: 15px; width: 99%;">
					{$product.name|escape:html:'UTF-8'}
					{if isset($product.show_price ) && $product.show_price && !isset($restricted_country_mode) && !$PS_CATALOG_MODE} - <span class="price">{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}</span>
					{/if}
				</p>
			</li>
		{/foreach}
		</ul>
	</div>
	<nav style="padding: 0; margin: 0; position: relative; bottom: 15px;">
		<span id="position" style="font-size: 10px;">
		{foreach from=$products item=product name=homeFeaturedProducts}
		    <a href="#" style="font-size: 25px; letter-spacing: 5px; text-decoration: none" rel="{$smarty.foreach.homeFeaturedProducts.index}" {if $smarty.foreach.homeFeaturedProducts.index == 0}class="on"{/if}>&bull;</a>
		{/foreach}
		</span>
	</nav>
</div>
{literal}
<script type="text/javascript">
var slider_home;
$('#jqm_page_index').live('pageshow', function() {
	slider_home = new Swipe(document.getElementById('slider_home'), {
	    auto: 3000,
	    speed: 400,
	    callback: function(event, index, elem) {
		$('#position a').removeClass('on');
		$('#position a[rel=' + index + ']').addClass('on');
	    }});
	$('#position a').click(function() {
	    slider_home.slide($(this).attr('rel'))
	});
});
</script>
{/literal}
{/if}
<!-- /MODULE Home Featured Products -->