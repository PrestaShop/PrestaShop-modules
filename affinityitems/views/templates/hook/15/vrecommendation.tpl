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

<div class="ae-area ae-{$aeconfiguration->area|escape:'htmlall'}">

<div id="{$aeconfiguration->parentId|escape:'htmlall'}" class="{$aeconfiguration->classParent|escape:'htmlall'}">
	<h4 class="{$aeconfiguration->classTitle|escape:'htmlall'}">{$aeconfiguration->label}</h4>
	<div id="{$aeconfiguration->contentId|escape:'htmlall'}" class="{$aeconfiguration->classContent|escape:'htmlall'}">
		<ul id="{$aeconfiguration->listId|escape:'htmlall'}" class="{$aeconfiguration->classList|escape:'htmlall'}">
			{foreach from=$aeproducts item=product name=myLoop}
			<li id="{$aeconfiguration->elementListId|escape:'htmlall'}" class="{$aeconfiguration->classElementList|escape:'htmlall'} {if $smarty.foreach.myLoop.last} last_item{elseif $smarty.foreach.myLoop.first} first_item{else} item{/if}">
				<a href="{$product.link|escape:'html'}" rel="{$product.id_product|escape:'htmlall'}" title="{l s='About' mod='affinityitems'} {$product.name|escape:html:'UTF-8'}" class="{$aeconfiguration->classElementImage|escape:'htmlall'}">
					<img src="{$link->getImageLink($product.link_rewrite, $product.id_image, $aeconfiguration->imgSize)|escape:'html'}" height="{$size.height|escape:'htmlall'}" width="{$size.width|escape:'htmlall'}" alt="{$product.name|escape:html:'UTF-8'}" />
				</a>
				<div>
					<h5 class="{$aeconfiguration->classElementName|escape:'htmlall'}"><a href="{$product.link|escape:'html'}" rel="{$product.id_product|escape:'htmlall'}" title="{l s='About' mod='affinityitems'} {$product.name|escape:html:'UTF-8'}">{$product.name|truncate:14:'...'|escape:html:'UTF-8'}</a></h5>
					<p class="{$aeconfiguration->classElementDescription|escape:'htmlall'}"><a href="{$product.link|escape:'html'}" rel="{$product.id_product|escape:'htmlall'}" title="{l s='About' mod='affinityitems'} {$product.name|escape:html:'UTF-8'}">{$product.description_short|strip_tags:'UTF-8'|truncate:44}</a></p>
				</div>
			</li>
			{/foreach}
		</ul>
	</div>
</div>

</div>
