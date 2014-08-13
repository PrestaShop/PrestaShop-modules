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
<div class="ae-area ae-{$aeconfiguration->area|escape:'htmlall'}"><div id="aereco" style="display:none;"><div id="{$aeconfiguration->parentId|escape:'htmlall'}" class="{$aeconfiguration->classParent|escape:'htmlall'}"><h4 class="{$aeconfiguration->classTitle|escape:'htmlall'}">{$aeconfiguration->label}</h4>{if isset($aeproducts) AND $aeproducts}<div id="{$aeconfiguration->contentId|escape:'htmlall'}" class="{$aeconfiguration->classContent|escape:'htmlall'}">{assign var='liHeight' value=235}{assign var='nbItemsPerLine' value=4}{assign var='nbLi' value=$aeproducts|@count}{math equation="nbLi/nbItemsPerLine" nbLi=$nbLi nbItemsPerLine=$nbItemsPerLine assign=nbLines}{math equation="nbLines*liHeight" nbLines=$nbLines|ceil liHeight=$liHeight assign=ulHeight}<ul id="{$aeconfiguration->listId|escape:'htmlall'}" class="{$aeconfiguration->classList|escape:'htmlall'}" style="height:{$ulHeight|escape:'htmlall'}px;">{foreach from=$aeproducts item=product name=affinityItemsProducts}<li id="{$aeconfiguration->elementListId|escape:'htmlall'}" class="{$aeconfiguration->classElementList|escape:'htmlall'} {if $smarty.foreach.affinityItemsProducts.first} first_item {elseif $smarty.foreach.affinityItemsProducts.last} last_item {else} item {/if} {if $smarty.foreach.affinityItemsProducts.iteration%$nbItemsPerLine == 0} last_item_of_line {elseif $smarty.foreach.affinityItemsProducts.iteration%$nbItemsPerLine == 1} clear {/if} {if $smarty.foreach.affinityItemsProducts.iteration > ($smarty.foreach.affinityItemsProducts.total - ($smarty.foreach.affinityItemsProducts.total % $nbItemsPerLine))} last_line {/if}"><a {if isset($product.link)} href="{$product.link|escape:'htmlall'}"{/if} rel="{$product.id_product|escape:'htmlall'}" title="{$product.name|escape:html:'UTF-8'}"><img class="{$aeconfiguration->classElementImage|escape:'htmlall'}" src="{$link->getImageLink($product.link_rewrite, $product.id_image, $aeconfiguration->imgSize)}" height="{$size.height|escape:'htmlall'}" width="{$size.width|escape:'htmlall'}" alt="{$product.name|escape:html:'UTF-8'}" /></a><h5 class="{$aeconfiguration->classElementName|escape:'htmlall'}"><a {if isset($product.link)} href="{$product.link}" {/if} rel="{$product.id_product|escape:'htmlall'}" title="{$product.name|truncate:32:'...'|escape:'htmlall':'UTF-8'}">{$product.name|truncate:27:'...'|escape:'htmlall':'UTF-8'}</a></h5><div>{if isset($product.show_price) && $product.show_price AND !isset($restricted_country_mode) AND !$PS_CATALOG_MODE}<p class="{$aeconfiguration->classPriceContainer|escape:'htmlall'}"><span class="{$aeconfiguration->classPrice|escape:'htmlall'}">{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if} </span></p> {else} <div style="height:21px;"></div>{/if}</div></li>{/foreach}</ul></div>{/if}</div></div></div>