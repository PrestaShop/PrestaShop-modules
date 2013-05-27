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
		<ConditionID>{$condition_id}</ConditionID>
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
				{foreach from=$pictures item=picture}
					<PictureURL>{$picture}</PictureURL>
				{/foreach}
			</PictureDetails>
		{/if}
		<SKU>{$sku}</SKU>
		<DispatchTimeMax>{$dispatch_time_max}</DispatchTimeMax>
		<ListingDuration>{$listing_duration}</ListingDuration>
		<Quantity>{$quantity}</Quantity>
		{if $price_update}
			<StartPrice>{$start_price}</StartPrice>
		{/if}
		{if $resynchronize}
			<Title>{$title}</Title>
			<Description><![CDATA[{$description}]]></Description>
			<ShippingDetails>{$shipping_details}</ShippingDetails>
			{$buyer_requirements_details}
		{/if}
		{if isset($value)}
			<ItemSpecifics>
				<NameValueList>
					<Name>Marque</Name>
					<Value><![CDATA[{$value}]]></Value>
				</NameValueList>
			</ItemSpecifics>
		{/if}
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