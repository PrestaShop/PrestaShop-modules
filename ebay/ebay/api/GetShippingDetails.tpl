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
{foreach from=$excluded_zones item=zone}
	<ExcludeShipToLocation>{$zone.location}</ExcludeShipToLocation>
{/foreach} 

{foreach from=$national_services key=service_name item=service}
	{if $service.serviceCosts !== fase}
		<ShippingServiceOptions>
			<ShippingServicePriority>{$service.servicePriority}</ShippingServicePriority>
			<ShippingService>{$service_name}</ShippingService>
			<FreeShipping>false</FreeShipping>
			<ShippingServiceCost currencyID="{$currency_id}">{$service.serviceCosts}</ShippingServiceCost>
			<ShippingServiceAdditionalCost>{$service.serviceAdditionalCosts}</ShippingServiceAdditionalCost>
		</ShippingServiceOptions>
	{/if}
{/foreach}

{foreach from=$international_services key=service_name item=service}
	{if $service.serviceCosts !== false}
		<InternationalShippingServiceOption>
			<ShippingServicePriority>{$service.servicePriority}</ShippingServicePriority>
			<ShippingService>{$service_name}</ShippingService>
			<ShippingServiceCost currencyID="{$currency_id}">{$service.serviceCosts}</ShippingServiceCost>
			<ShippingServiceAdditionalCost>{$service.serviceAdditionalCosts}</ShippingServiceAdditionalCost>
			{foreach from=$service.locationsToShip item=location}
				<ShipToLocation>{$location.id_ebay_zone}</ShipToLocation>
			{/foreach}
		</InternationalShippingServiceOption>
	{/if}
{/foreach}