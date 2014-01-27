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

{if !isset($smarty.get.id_product)}
{assign var='id_product' value=0}
{else}
{assign var='id_product' value=$smarty.get.id_product|intval}
{/if}

{include file="$tpl_dir./header-page.tpl" page=$id_product}

{include file="$tpl_dir./errors.tpl"}
{if $errors|@count == 0}
<script type="text/javascript">
// <![CDATA[

// PrestaShop internal settings
var currencySign = '{$currencySign|html_entity_decode:2:"UTF-8"}';
var currencyRate = '{$currencyRate|floatval}';
var currencyFormat = '{$currencyFormat|intval}';
var currencyBlank = '{$currencyBlank|intval}';
var taxRate = {$tax_rate|floatval};
var jqZoomEnabled = {if $jqZoomEnabled}true{else}false{/if};

//JS Hook
var oosHookJsCodeFunctions = new Array();

// Parameters
var id_product = '{$product->id|intval}';
var productHasAttributes = {if isset($groups)}true{else}false{/if};
var quantitiesDisplayAllowed = {if $display_qties == 1}true{else}false{/if};
var quantityAvailable = {if $display_qties == 1 && $product->quantity}{$product->quantity}{else}0{/if};
var allowBuyWhenOutOfStock = {if $allow_oosp == 1}true{else}false{/if};
var availableNowValue = '{$product->available_now|escape:'quotes':'UTF-8'}';
var availableLaterValue = '{$product->available_later|escape:'quotes':'UTF-8'}';
var productPriceTaxExcluded = {$product->getPriceWithoutReduct(true)|default:'null'} - {$product->ecotax};
var specific_currency = {if $product->specificPrice AND $product->specificPrice.id_currency}true{else}false{/if};
var reduction_percent = {if $product->specificPrice AND $product->specificPrice.reduction AND $product->specificPrice.reduction_type == 'percentage'}{$product->specificPrice.reduction*100}{else}0{/if};
var reduction_price = {if $product->specificPrice AND $product->specificPrice.reduction AND $product->specificPrice.reduction_type == 'amount'}(specific_currency ? {$product->specificPrice.reduction} : {$product->specificPrice.reduction} * currencyRate){else}0{/if};
var specific_price = {if $product->specificPrice AND $product->specificPrice.price}{$product->specificPrice.price}{else}0{/if};
var group_reduction = '{$group_reduction}';
var default_eco_tax = {$product->ecotax};
var ecotaxTax_rate = {if isset($ecotaxTax_rate)}{$ecotaxTax_rate}{else}0{/if};
var currentDate = '{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}';
var maxQuantityToAllowDisplayOfLastQuantityMessage = {$last_qties};
var noTaxForThisProduct = {if $no_tax == 1}true{else}false{/if};
var displayPrice = {if isset($priceDisplay)}{$priceDisplay}{else}1{/if};
var productReference = '{$product->reference|escape:'htmlall':'UTF-8'}';
var productAvailableForOrder = {if (isset($restricted_country_mode) AND $restricted_country_mode) OR $PS_CATALOG_MODE}'0'{else}'{$product->available_for_order}'{/if};
var productShowPrice = '{if !$PS_CATALOG_MODE}{$product->show_price}{else}0{/if}';
var productUnitPriceRatio = '{$product->unit_price_ratio}';
var idDefaultImage = {if isset($cover.id_image_only)}{$cover.id_image_only}{else}0{/if};
var ipa_default = {if isset($ipa_default)}{$ipa_default}{else}0{/if};

// Customizable field
var img_ps_dir = '{$img_ps_dir}';
var customizationFields = new Array();
{assign var='imgIndex' value=0}
{assign var='textFieldIndex' value=0}
{foreach from=$customizationFields item='field' name='customizationFields'}
	{assign var="key" value="pictures_`$product->id`_`$field.id_customization_field`"}
	customizationFields[{$smarty.foreach.customizationFields.index|intval}] = new Array();
	customizationFields[{$smarty.foreach.customizationFields.index|intval}][0] = '{if $field.type|intval == 0}img{$imgIndex++}{else}textField{$textFieldIndex++}{/if}';
	customizationFields[{$smarty.foreach.customizationFields.index|intval}][1] = {if $field.type|intval == 0 && isset($pictures.$key) && $pictures.$key}2{else}{$field.required|intval}{/if};
{/foreach}

// Images
var img_prod_dir = '{$img_prod_dir}';
var combinationImages = new Array();

{if isset($combinationImages)}
	{foreach from=$combinationImages item='combination' key='combinationId' name='f_combinationImages'}
		combinationImages[{$combinationId}] = new Array();
		{foreach from=$combination item='image' name='f_combinationImage'}
			combinationImages[{$combinationId}][{$smarty.foreach.f_combinationImage.index}] = {$image.id_image|intval};
		{/foreach}
	{/foreach}
{/if}

combinationImages[0] = new Array();
{if isset($images)}
	{foreach from=$images item='image' name='f_defaultImages'}
		combinationImages[0][{$smarty.foreach.f_defaultImages.index}] = {$image.id_image};
	{/foreach}
{/if}

// Translations
var doesntExist = '{l s='The product does not exist in this model. Please choose another one' js=1}';
var doesntExistNoMore = '{l s='This product is no longer in stock' js=1}';
var doesntExistNoMoreBut = '{l s='with those attributes but is available with others' js=1}';
var uploading_in_progress = '{l s='Uploading in progress, please wait...' js=1}';
var fieldRequired = '{l s='Please fill in all required fields, then save your customization.' js=1}';

{if isset($groups)}
	// Combinations
	{foreach from=$combinations key=idCombination item=combination}
		addCombination({$idCombination|intval}, new Array({$combination.list}), {$combination.quantity}, {$combination.price}, {$combination.ecotax}, {$combination.id_image}, '{$combination.reference|addslashes}', {$combination.unit_impact}, {$combination.minimal_quantity});
	{/foreach}
	// Colors
	{if $colors|@count > 0}
		{if $product->id_color_default}var id_color_default = {$product->id_color_default|intval};{/if}
	{/if}
{/if}
//]]>
</script>

{include file="$tpl_dir./breadcrumb.tpl"}

<div class="ui-body ui-body-{$ps_mobile_styles.PS_MOBILE_THEME_HEADINGS}" data-theme="{$ps_mobile_styles.PS_MOBILE_THEME_HEADINGS}" style="margin-bottom: 20px;">
	<h1>{$product->name|escape:'htmlall':'UTF-8'}</h1>
	<p id="product_reference" style="font-size: 11px; margin-top: 5px;{if isset($groups) || !$product->reference || $product->reference == ''} display: none;{/if}">{l s='Ref:'} {$product->reference|escape:'htmlall':'UTF-8'}</p>
</div>

<div id="image-block" style="-moz-border-radius: 15px; border-radius: 15px; background: #FFF; padding-left: 15px;">
	{if !$have_image}
		<img src="{$img_prod_dir}{$lang_iso}-default-large.jpg" id="bigpic" alt="" title="{$cover.legend|escape:'htmlall':'UTF-8'}" width="{$largeSize.width}" height="{$largeSize.height}" />
	{else}
		{if isset($images) && count($images) > 0}
		<div id="slider_product_{$id_product}">
			<ul id="thumbs_list_frame">
				{if isset($images)}
					{foreach from=$images item=image name=thumbnails}
					{assign var=imageIds value="`$product->id`-`$image.id_image`"}
					<li id="thumbnail_{$image.id_image}"{if !$smarty.foreach.thumbnails.first} style="display: none;"{/if}>
						<img{if $smarty.foreach.thumbnails.index == 0} id="bigpic"{else} id="thumb_{$image.id_image}"{/if} src="{$link->getImageLink($product->link_rewrite, $imageIds, 'large')}" alt="{$image.legend|htmlspecialchars}" class="thumbs" />
					</li>
					{/foreach}
				{/if}
			</ul>
		</div>
		{/if}
	{/if}
</div>
{if isset($images) && count($images) > 0}
<nav style="text-align: center; margin-top: 10px;">
	<span id="position">
	{foreach from=$images item=image name=images2}
		<a href="#" style="text-decoration: none; font-size: 25px; letter-spacing: 5px;" rel="{$smarty.foreach.images2.index}" {if $smarty.foreach.images2.index == 0}class="on"{/if}>&bull;</a>
    {/foreach}
	</span>
</nav>
{/if}

{literal}
<script type="text/javascript">
    var slider_product;

    $('#jqm_page_product_{/literal}{$id_product}{literal}').live('pageshow', function() {
	slider_product = new Swipe(document.getElementById('slider_product_{/literal}{$id_product}{literal}'), {
	    speed: 400,
	    auto: 3000,
	    callback: function(event, index, elem) {
		$('#position a').removeClass('on');
		$('#position a[rel="' + index + '"]').addClass('on');
	    }
	});

	$('#position a').click(function() {
	    slider_product.slide($(this).attr('rel'));
	});
    });
</script>
{/literal}

<div id="short_description_content" rte align_justify" style="margin: 15px 0;">
	{if $product->description_short}<b>{l s='Description:'}</b> {$product->description_short}{/if}
	<!-- availability -->
	{if !(($product->quantity <= 0 && !$product->available_later && $allow_oosp) OR ($product->quantity > 0 && !$product->available_now) OR !$product->available_for_order OR $PS_CATALOG_MODE)}<br /><br />{/if}
	<span id="availability_statut"{if ($product->quantity <= 0 && !$product->available_later && $allow_oosp) OR ($product->quantity > 0 && !$product->available_now) OR !$product->available_for_order OR $PS_CATALOG_MODE} style="display: none;"{/if}>
		<span id="availability_label" style="font-weight: bold;">{l s='Availability:'}</span>
		<span id="availability_value"{if $product->quantity <= 0} class="warning_inline"{/if}>
			{if $product->quantity <= 0}{if $allow_oosp}{$product->available_later}{else}{l s='This product is no longer in stock'}{/if}{else}{$product->available_now}{/if}
		</span>
	</span>
	{if isset($product->online_only) && $product->online_only}<br /><br /><span style="font-weight: bold;">{l s='This product/offer is available online exclusively'}<br /></span>{/if}
	<br class="clear" />
</div>

	{if $product->show_price AND !isset($restricted_country_mode) AND !$PS_CATALOG_MODE}

	{if !isset($priceDisplayPrecision)}{assign var='priceDisplayPrecision' value=2}{/if}
	{if !$priceDisplay || $priceDisplay == 2}
		{assign var='productPrice' value=$product->getPrice(true, $smarty.const.NULL, $priceDisplayPrecision)}
		{assign var='productPriceWithoutRedution' value=$product->getPriceWithoutReduct(false, $smarty.const.NULL)}
	{elseif $priceDisplay == 1}
		{assign var='productPrice' value=$product->getPrice(false, $smarty.const.NULL, $priceDisplayPrecision)}
		{assign var='productPriceWithoutRedution' value=$product->getPriceWithoutReduct(true, $smarty.const.NULL)}
	{/if}

	{/if}

{if isset($colors) && $colors}
<!-- colors -->
<div id="color_picker">
	<p>{l s='Pick a color:' js=1}</p>
	<div class="clear"></div>
	<ul id="color_to_pick_list">
	{foreach from=$colors key='id_attribute' item='color'}
		<li><a id="color_{$id_attribute|intval}" class="color_pick" style="background: {$color.value};" onclick="updateColorSelect({$id_attribute|intval});$('#wrapResetImages').show('slow');" title="{$color.name}">{if file_exists($col_img_dir|cat:$id_attribute|cat:'.jpg')}<img src="{$img_col_dir}{$id_attribute}.jpg" alt="{$color.name}" width="20" height="20" />{/if}</a></li>
	{/foreach}
	</ul>
	<div class="clear"></div>
</div>
<br class="clear" />
{/if}

{if ($product->show_price AND !isset($restricted_country_mode)) OR isset($groups) OR $product->reference OR (isset($HOOK_PRODUCT_ACTIONS) && $HOOK_PRODUCT_ACTIONS)}
<!-- add to cart form-->
<form id="buy_block" {if $PS_CATALOG_MODE AND !isset($groups) AND $product->quantity > 0}class="hidden"{/if} action="{$link->getPageLink('cart.php')}" method="post">

<!-- hidden datas -->
<p class="hidden">
	<input type="hidden" name="token" value="{$static_token}" />
	<input type="hidden" name="id_product" value="{$product->id|intval}" id="product_page_product_id" />
	<input type="hidden" name="add" value="1" />
	<input type="hidden" name="id_product_attribute" id="idCombination" value="" />
</p>

{if isset($groups)}
<!-- attributes -->
<div id="attributes" style="margin-bottom: 30px;">
{foreach from=$groups key=id_attribute_group item=group}
	{if $group.attributes|@count}
	<p style="margin-bottom: 10px;">
		{assign var="groupName" value="group_$id_attribute_group"}
		<select name="{$groupName}" id="group_{$id_attribute_group|intval}" onchange="javascript:findCombination();{if $colors|@count > 0}$('#wrapResetImages').show('slow');{/if};">
			{foreach from=$group.attributes key=id_attribute item=group_attribute}
				<option value="{$id_attribute|intval}"{if (isset($smarty.get.$groupName) && $smarty.get.$groupName|intval == $id_attribute) || $group.default == $id_attribute} selected="selected"{/if} title="{$group_attribute|escape:'htmlall':'UTF-8'}">{$group.name|escape:'htmlall':'UTF-8'} {$group_attribute|escape:'htmlall':'UTF-8'}</option>
			{/foreach}
		</select>
	</p>
	{/if}
{/foreach}
</div>
{/if}

<!-- minimal quantity wanted -->
<p id="minimal_quantity_wanted_p"{if $product->minimal_quantity <= 1 OR !$product->available_for_order OR $PS_CATALOG_MODE} style="display: none;"{/if}>{l s='You must add '} <b id="minimal_quantity_label">{$product->minimal_quantity}</b> {l s=' as a minimum quantity to buy this product.'}</p>
{if $product->minimal_quantity > 1}
<script type="text/javascript">
	checkMinimalQuantity();
</script>
{/if}

<!-- number of item in stock -->
{if ($display_qties == 1 && !$PS_CATALOG_MODE && $product->available_for_order)}
<p id="pQuantityAvailable"{if $product->quantity <= 0} style="display: none;"{/if}>
	<span id="quantityAvailable">{$product->quantity|intval}</span>
	<span {if $product->quantity > 1} style="display: none;"{/if} id="quantityAvailableTxt">{l s='item in stock'}<br /><br /></span>
	<span {if $product->quantity == 1} style="display: none;"{/if} id="quantityAvailableTxtMultiple">{l s='items in stock'}<br /><br /></span>
</p>
{/if}

<p class="warning_inline" id="last_quantities"{if ($product->quantity > $last_qties OR $product->quantity <= 0) OR $allow_oosp OR !$product->available_for_order OR $PS_CATALOG_MODE} style="display: none;"{/if} >{l s='Warning: Last items in stock!'}</p>
	<div class="ui-body ui-body-b">
		<ul data-role="listview" data-inset="true" data-theme="c">
			{if $product->specificPrice AND $product->specificPrice.reduction AND $productPriceWithoutRedution > $productPrice}
				<li><span>{l s='Previous Price'}</span><span id="old_price" class="ui-li-aside" style="text-decoration: line-through;">
				{if $priceDisplay >= 0 && $priceDisplay <= 2}
					{if $productPriceWithoutRedution > $productPrice}
						<span id="old_price_display">{convertPrice price=$productPriceWithoutRedution}</span>
					{/if}
				{/if}
				</span></li>
			{/if}
			<li style="height: 25px; line-height: 25px;">
			<span style="font-size: 13px;">
				{if $product->specificPrice AND $product->specificPrice.reduction AND $productPriceWithoutRedution > $productPrice}
					{l s='New Price'}
					{if $product->specificPrice AND $product->specificPrice.reduction_type == 'percentage'}
					<span id="reduction_percent"> (-<span id="reduction_percent_display">{$product->specificPrice.reduction*100}</span>%)</span>
					{/if}
					{else}
					{l s='Price'}
					{/if}
			</span><span class="ui-li-aside" style="font-size: 20px; margin-top: -4px;">
			<span class="our_price_display">
				{if $priceDisplay >= 0 && $priceDisplay <= 2}
					<span id="our_price_display" style="font-size: 15px; margin-top: 4px; display: block;">{convertPrice price=$productPrice}</span>
				{/if}
				</span>
				{if $priceDisplay == 2}
					<span id="pretaxe_price"><span id="pretaxe_price_display">{convertPrice price=$product->getPrice(false, $smarty.const.NULL, 2)}</span>&nbsp;{l s='tax excl.'}</span>
				{/if}</span></li>
				
			{if $product->ecotax != 0}
				<li><span>{l s='Ecotax'}</span><span class="ui-li-aside price-ecotax"><span id="ecotax_price_display">{if $priceDisplay == 2}{$ecotax_tax_exc|convertAndFormatPrice}{else}{$ecotax_tax_inc|convertAndFormatPrice}{/if}</span></span></li>
			{/if}
			
			{if $quantity_discounts}
			<li style="font-size: 13px;">{l s='Sliding scale reductions'}</li>
			<li id="quantityDiscount" style="padding-top: 0; border-top: none;">
				<table class="std" cellpadding="0" cellspacing="0">
					<tr>
						{foreach from=$quantity_discounts item='quantity_discount' name='quantity_discounts'}
							<th>{$quantity_discount.quantity|intval}
							{if $quantity_discount.quantity|intval > 1}
								{l s='qties'}
							{else}
								{l s='qty'}
							{/if}
							</th>
						{/foreach}
					</tr>
					<tr>
						{foreach from=$quantity_discounts item='quantity_discount' name='quantity_discounts'}
							<td>
							{if $quantity_discount.price != 0 OR $quantity_discount.reduction_type == 'amount'}
								-{convertPrice price=$quantity_discount.real_value|floatval}
							{else}
								-{$quantity_discount.real_value|floatval}%
							{/if}
							</td>
						{/foreach}
					</tr>
				</table>
			</li>
			{/if}
		</ul>
		<p style="display: block; padding: 5px 0 5px 7px;">
			{if $tax_enabled && $display_tax_label == 1}
				{if $priceDisplay == 1}<span>{l s='Prices are displayed excluding taxes.'}{else}{l s='Prices include taxes.'}</span>{/if}
			{/if}
		</p>	
		<fieldset class="ui-grid-a" data-type="horizontal">
			<div class="ui-block-a" style="width: 15%; margin-top: 1px;"><span id="quantity_wanted_p" data-inline="true"><input type="number" name="qty" id="quantity_wanted" class="text" value="{if isset($quantityBackup)}{$quantityBackup|intval}{else}{if $product->minimal_quantity > 1}{$product->minimal_quantity}{else}1{/if}{/if}" size="2" {if $product->minimal_quantity > 1}onkeyup="checkMinimalQuantity({$product->minimal_quantity});"{/if} /></span></div>
			<div class="ui-block-b ui-pos-right" style="width: 85%;"><input type="submit" name="Submit" value="{l s='Add to cart'}" data-theme="{$ps_mobile_styles.PS_MOBILE_THEME_BUTTONS}" data-icon="check" data-iconpos="right" /></div>
		</fieldset>
	</div>
	<br class="clear" />
</form>
{if isset($paypal_product)}<fieldset class="ui-grid-a" data-type="horizontal"><div class="ui-block-a" style="width: 100%; text-align: center;">{$paypal_product}</div></fieldset>{/if}
{/if}

<!-- description and features -->
{if $product->description || $features || $accessories}
<br class="clear" />
<div data-role="collapsible" data-collapsed="false" data-theme="{$ps_mobile_styles.PS_MOBILE_THEME_LIST_HEADERS}" data-content-theme="c" style="margin-bottom: 25px;">
	<h3>{l s='More info'}</h3>
	<p>{l s='Learn more about this product:'}</p>

	{if isset($product->description) && $product->description}
	<div data-role="collapsible" data-theme="c" data-content-theme="c">
		<h3>{l s='Full description'}</h3>
		<p class="rte">{$product->description}</p>
	</div>
	{/if}

	{if isset($features) && $features}
	<div data-role="collapsible" data-theme="c" data-content-theme="c">
		<h3>{l s='Product\'s Features'}</h3>
		<ul data-role="listview" data-inset="true">
			{foreach from=$features item=feature}
			<li><span>{$feature.name|escape:'htmlall':'UTF-8'}</span> <span class="ui-li-aside">{$feature.value|escape:'htmlall':'UTF-8'}</span></li>
			{/foreach}
		</ul>
	</div>
	{/if}

	{if isset($accessories) && $accessories}
	<div data-role="collapsible" data-theme="c" data-content-theme="c">
		<h3>{l s='Accessories'} ({$accessories|@count})</h3>
		{include file="$tpl_dir./product-list.tpl" products=$accessories}
	</div>
	{/if}
</div>
{/if}
{/if}

{include file="$tpl_dir./footer-page.tpl" page=$id_product}
