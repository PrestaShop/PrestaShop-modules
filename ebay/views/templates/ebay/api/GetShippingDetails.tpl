{foreach from=$excluded_zones item=zone}
	<ExcludeShipToLocation>{$zone.location}</ExcludeShipToLocation>
{/foreach} 

{foreach from=$national_services key=service_name item=service}
	<ShippingServiceOptions>
		<ShippingServicePriority>{$service.servicePriority}</ShippingServicePriority>
		<ShippingService>{$service_name}</ShippingService>
		<FreeShipping>false</FreeShipping>
		<ShippingServiceCost currencyID="{$currency_id}">{$service.serviceCosts}</ShippingServiceCost>
		<ShippingServiceAdditionalCost>{$service.serviceAdditionalCosts}</ShippingServiceAdditionalCost>
	</ShippingServiceOptions>
{/foreach}

{foreach from=$international_services key=service_name item=service}
	<InternationalShippingServiceOption>
		<ShippingServicePriority>{$service.servicePriority}</ShippingServicePriority>
		<ShippingService>{$service_name}</ShippingService>
		<ShippingServiceCost currencyID="{$currency_id}">{$service.serviceCosts}</ShippingServiceCost>
		<ShippingServiceAdditionalCost>{$service.serviceAdditionalCosts}</ShippingServiceAdditionalCost>
		{foreach from=$service.locationsToShip item=location}
			<ShipToLocation>{$location.id_ebay_zone}</ShipToLocation>
		{/foreach}
	</InternationalShippingServiceOption>
{/foreach}