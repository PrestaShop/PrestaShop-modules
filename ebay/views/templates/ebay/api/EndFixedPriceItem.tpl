<?xml version="1.0" encoding="utf-8"?>
<EndFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
	<ErrorLanguage>{$error_language}</ErrorLanguage>
	<WarningLevel>High</WarningLevel>
	<ItemID>{$item_id}</ItemID>
	{if isset($sku)}
		<SKU>{$sku}</SKU>
	{/if}
	<EndingReason>NotAvailable</EndingReason>
	<RequesterCredentials>
		<eBayAuthToken>{$ebay_auth_token}</eBayAuthToken>
	</RequesterCredentials>
	<WarningLevel>High</WarningLevel>
</EndFixedPriceItemRequest>
