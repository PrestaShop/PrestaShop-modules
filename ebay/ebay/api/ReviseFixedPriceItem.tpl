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
<?xml version="1.0" encoding="utf-8"?>
<ReviseFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
	<ErrorLanguage>{$error_language}</ErrorLanguage>
	<WarningLevel>High</WarningLevel>
	<Item>
		<ItemID>{$item_id}</ItemID>
		{if isset($country)}
			<Country>{$country}</Country>
		{/if}
		{if isset($country_currency)}
			<Currency>{$country_currency}</Currency>
		{/if}
		<ConditionID>{if $condition_id > 0}{$condition_id}{else}1000{/if}</ConditionID>
		{if isset($listing_type)}
			<ListingType>{$listing_type}</ListingType>
		{/if}
		{if isset($payment_method)}
			<PaymentMethods>{$payment_method}</PaymentMethods>
		{/if}
		{if isset($pay_pal_email_address)}
			<PayPalEmailAddress>{$pay_pal_email_address}</PayPalEmailAddress>
		{/if}
		{if isset($postal_code)}
			<PostalCode>{$postal_code}</PostalCode>
		{/if}
		{if isset($category_id)}
			<PrimaryCategory>
				<CategoryID>{$category_id}</CategoryID>
			</PrimaryCategory>		
		{/if}
		{if count($pictures)}
			<PictureDetails>
				<GalleryType>Gallery</GalleryType>
				{if $pictures|count > 1}
					<PhotoDisplay>PicturePack</PhotoDisplay>
				{/if}				
				{foreach from=$pictures item=picture}
					<PictureURL>{$picture}</PictureURL>
				{/foreach}
			</PictureDetails>
		{/if}
		{if isset($sku)}
			<SKU>{$sku}</SKU>
		{/if}
		<DispatchTimeMax>{$dispatch_time_max}</DispatchTimeMax>
		<ListingDuration>{$listing_duration}</ListingDuration>
		{if isset($quantity)}
			<Quantity>{$quantity}</Quantity>
		{/if}
		{if $price_update && isset($start_price)}
			<StartPrice>{$start_price}</StartPrice>
		{/if}
		{if $resynchronize}
			<Title>{$title}</Title>
			<Description><![CDATA[{$description}]]></Description>
			<ShippingDetails>{$shipping_details}</ShippingDetails>
			{$buyer_requirements_details}
		{/if}
			<ItemSpecifics>
				{foreach from=$item_specifics key=name item=value}
					<NameValueList>
						<Name><![CDATA[{$name}]]></Name>
						<Value><![CDATA[{$value}]]></Value>
					</NameValueList>
				{/foreach}				
			</ItemSpecifics>
		{$return_policy}
		{if isset($site)}
			<Site>{$site}</Site>
		{/if}
		{if isset($variations)}
			{$variations}
		{/if}
	</Item>
	<RequesterCredentials>
		<eBayAuthToken>{$ebay_auth_token}</eBayAuthToken>
	</RequesterCredentials>
</ReviseFixedPriceItemRequest>