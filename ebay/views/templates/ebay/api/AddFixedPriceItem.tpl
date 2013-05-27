<?xml version="1.0" encoding="utf-8"?>
<AddFixedPriceItem xmlns="urn:ebay:apis:eBLBaseComponents">
	<ErrorLanguage>{$error_language}</ErrorLanguage>
	<WarningLevel>High</WarningLevel>
	<Item>
		{if isset($sku)}
			<SKU>{$sku}</SKU>
		{/if}
		<Title>{$title}</Title>
		{if count($pictures)}
			<PictureDetails>
				<GalleryType>Gallery</GalleryType>
				{foreach from=$pictures item=picture}
					<PictureURL>{$picture}</PictureURL>
				{/foreach}
			</PictureDetails>
		{/if}
		<Description><![CDATA[{$description}]]></Description>
		<PrimaryCategory>
			<CategoryID>{$category_id}</CategoryID>
		</PrimaryCategory>
		<ConditionID>{$condition_id}</ConditionID>
		{if $price_update && isset($start_price)}
			<StartPrice>{$start_price}</StartPrice>
		{/if}
		<CategoryMappingAllowed>true</CategoryMappingAllowed>
		<Country>{$country}</Country>
		<Currency>{$country_currency}</Currency>
		<DispatchTimeMax>{$dispatch_time_max}</DispatchTimeMax>
		<ListingDuration>{$listing_duration}</ListingDuration>
		<ListingType>FixedPriceItem</ListingType>
		<PaymentMethods>PayPal</PaymentMethods>
		<PayPalEmailAddress>{$pay_pal_email_address}</PayPalEmailAddress>
		<PostalCode>{$postal_code}</PostalCode>
		{if isset($quantity)}
			<Quantity>{$quantity}</Quantity>
		{/if}
		<ItemSpecifics>
			<NameValueList>
				<Name>Marque</Name>
				<Value><![CDATA[{$value}]]></Value>
			</NameValueList>
			{if isset($attributes)}
				{foreach from=$attributes key=name item=attribute}
					<NameValueList>
						<Name>{$name}</Name>
						<Value>{$attribute}</Value>
					</NameValueList>
				{/foreach}
			{/if}
		</ItemSpecifics>
		{$return_policy}
		{if isset($variations)}
			{$variations}
		{/if}
		<ShippingDetails>{$shipping_details}</ShippingDetails>
		{$buyer_requirements_details}
		<Site>{$site}</Site>
	</Item>
	<RequesterCredentials>
		<eBayAuthToken>{$ebay_auth_token}</eBayAuthToken>
	</RequesterCredentials>
</AddFixedPriceItem>