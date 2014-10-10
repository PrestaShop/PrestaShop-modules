{*
* 2007-2012 PrestaShop
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
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 6594 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{if $aRecommendations.$hook_name.items}
    {if $page_name == 'order'}
      <div id="generic" class="generic2">
        {if $hook_name == 'shopping_cart'}
        <h2 class="titleType01"><span>{$aRecommendations.$hook_name.block_title}</span></h2>
        {/if}
        <article class="slider produits">
            <div id="produits">
                {foreach from=$aRecommendations.$hook_name.items item="aRecommendation" name="aRecommendationLoop"}
                <div class="col">
                    <article>
                        <a href="{$aRecommendation.link}"><img style="width: 85%; margin-left: 19px;" src="{$link->getImageLink($aRecommendation.link_rewrite, $aRecommendation.id_image, 'home_default')|escape:'html'}" alt="Contenu alternatif" class="packshot"/></a>
                        <h3>{$aRecommendation.name}, <span class="unit">70cl</span></h3>
                        <div class="commercial">
                            {if $aRecommendation.specific_prices}
                                <div class="reduc" style="top: 253px;">
                                    {if $aRecommendation.specific_prices.reduction_type == 'percentage'}
                                        -{$aRecommendation.specific_prices.reduction*100}%
                                    {elseif $aRecommendation.specific_prices.reduction_type == 'amount'}
                                        -{$aRecommendation.specific_prices.reduction_int}<sup>{$currency->sign}{$aRecommendation.specific_prices.reduction_decimal}</sup>
                                    {/if}
                                </div>
                            {/if}
                            <!-- Price -->
                            <div class="price">
                                {$aRecommendation.price_int}<sup>{$currency->sign}{$aRecommendation.price_decimal}</sup>
                                <br />
                            </div>
                            <!-- Discount -->
                            {if $aRecommendation.specific_prices}
                                <span style="top: 253px;" class="oldPrice">{$aRecommendation.price_without_reduction_int}<sup>{$currency->sign}{$aRecommendation.price_without_reduction_decimal}</sup></span>
                            {/if}
                        </div>
                        <a href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$aRecommendation.id_product|intval}&amp;token={$static_token}", false)|escape:'html'}"  class="addBasketButton">Ajouter au panier</a>
                        <a href="#" class="infoBt toolTip">Informations</a>
                        <p class="dataToolTip">{$aRecommendation.description_short|strip_tags:'UTF-8'|truncate:360:'...'}</p>
                    </article> 
                </div>
                {/foreach}
            </div>
            <a href="#" rel="nofollow" class="prev">prev</a>
            <a href="#" rel="nofollow" class="next">next</a>
        </article>
    </div>
    {else if $hook_name == 'right_column_product'}
      <h4 class="titleType02"><span>{$aRecommendations.$hook_name.block_title}</span></h4>
      {foreach from=$aRecommendations.$hook_name.items item="aRecommendation" name="aRecommendationLoop"}
        <div class="serviceItem">
          <a href="{$aRecommendation.link}">
              <img src="{$link->getImageLink($aRecommendation.link_rewrite, $aRecommendation.id_image, 'services_product_right')|escape:'html'}" alt="Contenu alternatif" class="packshot"/>
          </a>
            <h5>{$aRecommendation.name}</h5>
            <p>{$aRecommendation.description_short|strip_tags:'UTF-8'}</strong></p>
            <a href="{$aRecommendation.link}" class="blackButton whiteI"><span class="btIco"><span class="ico"></span>En savoir plus</span></a>
            <div>
                <a href="{$link->getCategoryLink($aRecommendation.id_category_default)|escape:'htmlall':'UTF-8'}" class="simpleLink">Voir tous les services premium</a>
            </div>
        </div>
      {/foreach}
    {else}
      <section id="crossPanelType1">
            <div class="container">
                <h4 class="titleType01"><span><strong>{$aRecommendations.$hook_name.block_title}</strong> BARPREMIUM</span></h4>
                <div class="threeCol">
                    {foreach from=$aRecommendations.$hook_name.items item="aRecommendation" name="aRecommendationLoop"}
                        <div class="col prediggo-item">
                            <article class="listProdType1">
                                <a href="{$aRecommendation.link}">
                                    <img src="{$link->getImageLink($aRecommendation.link_rewrite, $aRecommendation.id_image, 'home_default')|escape:'html'}" alt="Contenu alternatif" class="packshot"/>
                                </a>
                                <h3>{$aRecommendation.name}, 
                                <span class="unit">
                                </span>
                                <div class="commercial">
                                    <!-- Reduction -->
                                    {if $aRecommendation.specific_prices}
                                        <div class="reduc">
                                            {if $aRecommendation.specific_prices.reduction_type == 'percentage'}
                                                -{$aRecommendation.specific_prices.reduction*100}%
                                            {elseif $aRecommendation.specific_prices.reduction_type == 'amount'}
                                                -{$aRecommendation.specific_prices.reduction_int}<sup>{$currency->sign}{$aRecommendation.specific_prices.reduction_decimal}</sup>
                                            {/if}
                                        </div>
                                    {/if}
                                    <!-- Price -->
                                    <div class="price">
                                        {$aRecommendation.price_int}<sup>{$currency->sign}{$aRecommendation.price_decimal}</sup>
                                        <br />
                                    </div>
                                    <!-- Discount -->
                                    {if $aRecommendation.specific_prices}
                                        <div class="lineOldPrice">
                                            <span class="oldPrice">{$aRecommendation.price_without_reduction_int}<sup>{$currency->sign}{$aRecommendation.price_without_reduction_decimal}</sup></span>
                                        </div>
                                    {/if}
                                </div>
                                {if ($aRecommendation.allow_oosp || $aRecommendation.quantity > 0)}
                                    {if isset($static_token)}
                                        <a class="addBasketButton" 
                                           rel="ajax_id_product_{$aRecommendation.id_product|intval}" 
                                           href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$aRecommendation.id_product|intval}&amp;token={$static_token}", false)|escape:'html'}" 
                                           title="{l s='Add to cart'}">
                                               <span></span>
                                               {l s='Add to cart'}
                                        </a>
                                    {else}
                                        <a class="addBasketButton" 
                                           rel="ajax_id_product_{$aRecommendation.id_product|intval}" 
                                           href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$aRecommendation.id_product|intval}", false)|escape:'html'}" 
                                           title="{l s='Add to cart'}">
                                               <span></span>
                                               {l s='Add to cart'}
                                           </a>
                                    {/if} 
                                {else}
                                    <div class="availability">
                                        {if (!$aRecommendation.allow_oosp && $aRecommendation.quantity == 0)}
                                            <span class="warning_inline">{l s='Stock épuisé'}</span>
                                        {/if}
                                    </div>
                                {/if}
                                <a href="#" class="infoBt toolTip">Informations</a>
                                <p class="dataToolTip">{$aRecommendation.description_short|strip_tags:'UTF-8'|truncate:360:'...'}</p>
                            </article>
                            <aside>
                                <a href="{$link->getManufacturerLink($aRecommendation.id_manufacturer)|escape:'htmlall':'UTF-8'}" class="top doubleTransparentButton">Voir la gamme {$aRecommendation.manufacturer_name}</a>
                                <a href="{$link->getCategoryLink($aRecommendation.id_category_default)|escape:'htmlall':'UTF-8'}" class="bot doubleTransparentButton">Voir tout : {$aRecommendation.category}</a>
                            </aside>
                        </div>
                    {/foreach}
                </div>
            </div>
        </section>
    {/if}
{/if}