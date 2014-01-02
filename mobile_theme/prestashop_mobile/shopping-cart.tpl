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

{include file="$tpl_dir./header-page.tpl"}

{assign var='current_step' value='summary'}
{include file="$tpl_dir./order-steps.tpl"}
{capture name=path}{l s='Your shopping cart'} ({$cart_qties} {if $cart_qties == 1}{l s='product'}{else}{l s='products'}{/if}){/capture}
{include file="$tpl_dir./breadcrumb.tpl"}
{include file="$tpl_dir./errors.tpl"}

{if isset($empty)}
	<div class="ui-overlay-shadow ui-body-a ui-corner-all" style="padding: 12px; margin: 15px 0;">
		<p style="font-weight: bold;">{l s='Your shopping cart is empty.'}</p>
	</div>
{elseif $PS_CATALOG_MODE}
	<div class="ui-overlay-shadow ui-body-a ui-corner-all" style="padding: 12px; margin: 15px 0;">
		<p style="font-weight: bold;">{l s='This store has not accepted your new order.'}</p>
	</div>
{else}
	<script type="text/javascript">
	// <![CDATA[
	var currencySign = '{$currencySign|html_entity_decode:2:"UTF-8"}';
	var currencyRate = '{$currencyRate|floatval}';
	var currencyFormat = '{$currencyFormat|intval}';
	var currencyBlank = '{$currencyBlank|intval}';
	var txtProduct = "{l s='product'}";
	var txtProducts = "{l s='products'}";
	// ]]>
	</script>
	<p style="display:none" id="emptyCartWarning" class="warning">{l s='Your shopping cart is empty.'}</p>

	{if isset($products)}
	<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="{$ps_mobile_styles.PS_MOBILE_THEME_LIST_HEADERS}" data-split-theme="{$ps_mobile_styles.PS_MOBILE_THEME_LIST_HEADERS}" data-split-icon="delete">
		<li data-role="list-divider">{l s='Products'}</li>
		{foreach from=$products item=product}
			<li id="element_product_{$product.id_product|intval}" style="height: 81px;">
				<a>
					<input type="hidden" name="cart_product_id[]" value="{$product.id_product|intval}"/>
					<input type="hidden" id="cart_product_attribute_id_{$product.id_product|intval}" value="{$product.id_product_attribute|intval}"/>
					{if isset($product.id_address_delivery)}<input type="hidden" id="cart_product_address_delivery_id_{$product.id_product|intval}" value="{$product.id_address_delivery}"/>{/if}

					<img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'medium')}" alt="" />
					<h2 style="margin-top: 0px;">{$product.name|escape:'htmlall':'UTF-8'}</h2>
					<input type="number" style="width: 50px; float: left;" data-inline="true" class="qty-field cart_quantity_input" name="product_cart_quantity_{$product.id_product|intval}" rel="{$product.cart_quantity}" value="{$product.cart_quantity}" min="0" data-mini="true" data-initial-quantity="{$product.cart_quantity}" data-id-product="{$product.id_product}" data-id-product-attribute="{$product.id_product_attribute}" onchange="if (this.value > 0) {literal}{{/literal} location.replace('{$link->getPageLink('cart.php', true, NULL)}?add=1&amp;update=1&amp;token={$token_cart}&amp;op='+($(this).val() > $(this).attr('rel') ? 'up' : 'down')+'&amp;id_product={$product.id_product}{if $product.id_product_attribute}&amp;id_product_attribute={$product.id_product_attribute}{/if}&amp;qty='+Math.abs($(this).attr('rel') - $(this).val())); {literal}}{/literal}" /><span style="float: left; display: block; line-height: 35px; margin-left: 7px;"> x&nbsp; {displayPrice price=$product.price_wt}</span>
					<br class="clear" />
				</a>
				<a rel="nofollow" class="cart_quantity_delete" id="{$product.id_product|intval}_{$product.id_product_attribute|intval}_0_{if isset($product.id_address_delivery)}{$product.id_address_delivery|intval}{else}0{/if}" href="{$link->getPageLink('cart.php', true, NULL)}?delete=1&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}{if isset($product.id_address_delivery)}&amp;id_address_delivery={$product.id_address_delivery|intval}{/if}&amp;token={$token_cart}" data-ajax="false">{l s='Delete'}</a>
			</li>
		{/foreach}
		{if $discounts|@count}
		<li data-role="list-divider">{l s='Vouchers'}</li>
		{foreach from=$discounts item=discount name=discountLoop}
			<li class=" id="cart_discount_{$discount.id_discount}">
				<a>
					{l s='Code:'} {$discount.name|escape:'htmlall':'UTF-8'}<br />
					{$discount.description}<br />
					{l s='Discount:'} {if $discount.value_real > 0}{if !$priceDisplay}{displayPrice price=$discount.value_real*-1}{else}{displayPrice price=$discount.value_tax_exc*-1}{/if}{/if}
				</a>
				<a rel="nofollow" href="{if $opc}{$link->getPageLink('order-opc.php', true)}{else}{$link->getPageLink('order.php', true)}{/if}?deleteDiscount={$discount.id_discount}" title="{l s='Delete'}" data-ajax="false">{l s='Delete'}</a>
			</li>
		{/foreach}
		{/if}
	</ul>
	{/if}

	{if $voucherAllowed}
	<div id="cart_voucher" class="table_block">
		{if isset($errors_discount) && $errors_discount}
			<div class="ui-overlay-shadow ui-body-a ui-corner-all" style="padding: 12px; margin: 15px 0;">
				<p style="font-weight: bold;">{if $errors_discount|@count > 1}{l s='There are'}{else}{l s='There is'}{/if} {$errors_discount|@count} {if $errors_discount|@count > 1}{l s='errors'}{else}{l s='error'}{/if} :</p>
				<ol style="margin: 10px 0 0 18px;">
				{foreach from=$errors_discount key=k item=error}
					<li>{$error}</li>
				{/foreach}
				</ol>
			</div>
		{/if}

		<div data-role="collapsible" data-theme="c" data-content-theme="c">
			<h3 style="margin: 0;">{l s='Add a Voucher'}</h3>
			<div style="margin: 0;">
				<form action="{if $opc}{$link->getPageLink('order-opc.php', true)}{else}{$link->getPageLink('order.php', true)}{/if}" method="post" id="voucher">
						<fieldset class="ui-grid-a" data-type="horizontal">
							<div class="ui-block-a" style="width: 75%; margin-top: 1px;"><input type="text" id="discount_name" name="discount_name" {if !isset($smarty.get.search_query)} placeholder="{l s='Voucher code...'}"{/if} value="{if isset($discount_name) && $discount_name}{$discount_name}{/if}" /></div>
							<div class="ui-block-b" style="width: 25%;"><input type="submit" name="submitAddDiscount" value="{l s='Add'}" /></div>
							<input type="hidden" name="submitDiscount" />
						</fieldset>
				</form>
			</div>
		</div>

		{if $displayVouchers}
			<h4>{l s='Take advantage of our offers:'}</h4>
			<div id="display_cart_vouchers">
			{foreach from=$displayVouchers item=voucher}
				<span onclick="$('#discount_name').val('{$voucher.name}');return false;" class="voucher_name">{$voucher.name}</span> - {$voucher.description} <br />
			{/foreach}
			</div>
		{/if}
	</div>
	{/if}

	<ul data-role="listview" data-theme="c" data-dividertheme="{$ps_mobile_styles.PS_MOBILE_THEME_LIST_HEADERS}" data-inset="true">
		<li data-role="list-divider">{l s='Totals'}</li>
		<li>{l s='Products'}{if $use_taxes}{if $priceDisplay}{if $display_tax_label} {l s='(tax excl.)'}{/if}{else}{if $display_tax_label} {l s='(tax incl.)'}{/if}{/if}{/if}<span class="ui-li-aside">{if $use_taxes}{if $priceDisplay}{displayPrice price=$total_products}{else}{displayPrice price=$total_products_wt}{/if}{else}{displayPrice price=$total_products}{/if}</span></li>
		<li{if $total_discounts == 0} style="display: none;"{/if}>{l s='Vouchers'}{if $use_taxes}{if $priceDisplay}{if $display_tax_label} {l s='(tax excl.)'}{/if}{else}{if $display_tax_label} {l s='(tax incl.)'}{/if}{/if}{/if}<span class="ui-li-aside" id="total_discount">{if $use_taxes}{if $priceDisplay}{displayPrice price=$total_discounts_tax_exc}{else}{displayPrice price=$total_discounts}{/if}{else}{displayPrice price=$total_discounts_tax_exc}{/if}</span></li>
		<li class="cart_total_voucher" {if $total_wrapping == 0}style="display: none;"{/if}>{l s='Total gift-wrapping'}{if $use_taxes}{if $priceDisplay}{if $display_tax_label} {l s='(tax excl.)'}{/if}{else}{if $display_tax_label} {l s='(tax incl.)'}{/if}{/if}{/if}<span class="ui-li-aside" id="total_wrapping">{if $use_taxes}{if $priceDisplay}{displayPrice price=$total_wrapping_tax_exc}{else}{displayPrice price=$total_wrapping}{/if}{else}{displayPrice price=$total_wrapping_tax_exc}{/if}</span></li>
		<li class="cart_total_delivery" {if $shippingCost <= 0} style="display:none;"{/if}>{l s='Shipping'}{if $use_taxes}{if $priceDisplay}{if $display_tax_label} {l s='(tax excl.)'}{/if}{else}{if $display_tax_label} {l s='(tax incl.)'}{/if}{/if}{/if}<span class="ui-li-aside">{if $use_taxes}{if $priceDisplay}{displayPrice price=$shippingCostTaxExc}{else}{displayPrice price=$shippingCost}{/if}{else}{displayPrice price=$shippingCostTaxExc}{/if}</span></li>
		{if $use_taxes}
		<li class="cart_total_tax">{if $display_tax_label}{l s='Taxes:'}{else}{l s='Est. Sales Tax:'}{/if}<span class="ui-li-aside" id="total_tax">{displayPrice price=$total_tax}</span></li>
		<li class="cart_total_price">{l s='Total'}{if $display_tax_label} {l s='(tax incl.)'}{/if}<span class="ui-li-aside" id="total_price">{displayPrice price=$total_price}</span></li>
		{else}
		<li class="cart_total_price">{l s='Total'}<span class="ui-li-aside" id="total_price">{displayPrice price=$total_price_without_tax}</span></li>
		{/if}
	</ul>

	{if isset($paypal_cart)}<fieldset class="ui-grid-a" data-type="horizontal"><div class="ui-block-a" style="width: 100%; text-align: center; margin-bottom: 15px;">{$paypal_cart}</div></fieldset>{/if}

	<p style="text-align: center; margin-top: 10px;">
		<a data-role="button" data-icon="back" data-posicon="left" data-mini="true" data-inline="true" href="{if (isset($smarty.server.HTTP_REFERER) && strstr($smarty.server.HTTP_REFERER, $link->getPageLink('order.php'))) || !isset($smarty.server.HTTP_REFERER)}{$link->getPageLink('index.php')}{else}{$smarty.server.HTTP_REFERER|escape:'htmlall':'UTF-8'|secureReferrer}{/if}" class="button_large" title="{l s='Continue shopping'}">{l s='Continue shopping'}</a>
		{if !$opc}<a data-role="button" data-icon="check" data-mini="true" data-iconpos="right" data-theme="{$ps_mobile_styles.PS_MOBILE_THEME_BUTTONS}" data-inline="true" data-ajax="false" href="{$link->getPageLink('order.php', true)}?step=1{if $back}&amp;back={$back}{/if}" class="exclusive" title="{l s='Checkout'}">{l s='Checkout'}</a>{/if}
	</p>
{/if}

{include file="$tpl_dir./footer-page.tpl"}
