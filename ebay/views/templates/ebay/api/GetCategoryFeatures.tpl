<?xml version="1.0" encoding="utf-8"?>
<GetCategoryFeatures xmlns="urn:ebay:apis:eBLBaseComponents">
	<RequesterCredentials>
		<eBayAuthToken>{$ebay_auth_token}</eBayAuthToken>
	</RequesterCredentials>
	<DetailLevel>ReturnAll</DetailLevel>
	{if isset($feature_id)}
		<FeatureID>{$feature_id}</FeatureID>
	{/if}
	{if isset($category_id)}
		<CategoryID>{$category_id}</CategoryID>
	{/if}
	<ErrorLanguage>{$error_language}</ErrorLanguage>
	<Version>{$version}</Version>
	<WarningLevel>High</WarningLevel>
	<ViewAllNodes>true</ViewAllNodes>
</GetCategoryFeatures>