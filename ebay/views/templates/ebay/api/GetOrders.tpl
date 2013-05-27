<?xml version="1.0" encoding="utf-8"?>
<GetOrdersRequest xmlns="urn:ebay:apis:eBLBaseComponents">
	<DetailLevel>ReturnAll</DetailLevel>
	<ErrorLanguage>{$error_language}</ErrorLanguage>
	<WarningLevel>High</WarningLevel>
	<CreateTimeFrom>{$create_time_from}</CreateTimeFrom>
	<CreateTimeTo>{$create_time_to}</CreateTimeTo>
	<OrderRole>Seller</OrderRole>
	<OrderStatus>Completed</OrderStatus>
	<Pagination>
		<EntriesPerPage>100</EntriesPerPage>
		<PageNumber>{$page_number}</PageNumber>
	</Pagination>
	<RequesterCredentials>
		<eBayAuthToken>{$ebay_auth_token}</eBayAuthToken>
	</RequesterCredentials>
</GetOrdersRequest>
