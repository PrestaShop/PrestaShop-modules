<form action="{$formUrl}" method="post">

<fieldset>
	<legend>{l s='Global Configuration' mod='ebay'}</legend>
		<label>{l s='Delivery Time' mod='ebay'}</label>
		<div class="margin-form">
			<select name="deliveryTime" id="deliveryTime">
				{foreach from=$deliveryTimeOptions item=deliveryTimeOption}
					<option value="{$deliveryTimeOption.DispatchTimeMax}" {if $deliveryTimeOption.DispatchTimeMax == $deliveryTime} selected="selected"{/if}>{$deliveryTimeOption.description}</option>
				{/foreach}
			</select>
		</div>


		
</fieldset>
<script type="text/javascript">
	
	{literal}
	function addShippingFee(show, internationalOnly, idEbayCarrier, idPSCarrier, additionalFee){
	{/literal}
		var currentShippingService = -1;
		
	
		if(internationalOnly == 1)
		{literal}{{/literal}
			var lastId = $('#internationalCarrier .internationalShipping').length;
			internationsuffix = '_international';
		}
		else
		{literal}{{/literal}
			
			var lastId = $('#nationalCarrier tr').length;
			internationsuffix = '';
		}
			



		if(additionalFee == undefined)
			additionalFee = '';

		var stringShippingFee = "<tr class='onelineebaycarrier'>";
		stringShippingFee += "<td>{l s='Choose your eBay carrier'}</td>";
		stringShippingFee += 	"<td>";
		stringShippingFee += 		"<select name='ebayCarrier"+internationsuffix+"["+lastId+"]' id='ebayCarrier_"+lastId+"'>";
		{foreach from=$eBayCarrier item=carrier}
		currentShippingService = '{$carrier.shippingService}';
		//check for international
		if((internationalOnly == 1 && '{$carrier.InternationalService}'=== 'true') || internationalOnly == 0)
		{literal}{{/literal}
			if(currentShippingService == idEbayCarrier)
			{literal}{{/literal}
				stringShippingFee += 		"<option value='{$carrier.shippingService}' selected='selected'>{$carrier.description}</option>";
			}
			else
			{literal}{{/literal}
				stringShippingFee += 		"<option value='{$carrier.shippingService}'>{$carrier.description}</option>";
			}
		}
		{/foreach}
		stringShippingFee += 		"</select>";
		stringShippingFee += 	"</td>";
		stringShippingFee += 	"<td>";
		stringShippingFee += 		"{l s='Associate it with a PrestaShop carrier'}";
		stringShippingFee += 	"</td>";
		stringShippingFee += 	"<td>";
		stringShippingFee += 		"<select name='psCarrier"+internationsuffix+"["+lastId+"]' id='psCarrier_"+lastId+"'>";
												{foreach from=$psCarrier item=carrier}
		currentShippingService = {$carrier.id_carrier};
		if(currentShippingService == idPSCarrier)
		{literal}{{/literal}
			stringShippingFee += 		"<option value='{$carrier.id_carrier}' selected='selected'>{$carrier.name}</option>";
		}
		else
		{literal}{{/literal}
			stringShippingFee += 		"<option value='{$carrier.id_carrier}'>{$carrier.name}</option>";
		}
												{/foreach}
		stringShippingFee += 		"</select>";
		stringShippingFee += 	"</td>";
		stringShippingFee += 	"<td>";
		stringShippingFee += 		"{l s='Add extra fee for this carrier'}";
		stringShippingFee += 	"<td>";
		stringShippingFee += 	"<td>";
		stringShippingFee += 		"<input type='text' name='extrafee"+internationsuffix+"["+lastId+"]' value='"+additionalFee+"'>";
		stringShippingFee += 	"</td>";
		stringShippingFee += 	"<td>";
		stringShippingFee += 	"<img src='../img/admin/delete.gif' title='Delete' class='deleteCarrier' />";	
		stringShippingFee += 	"</td>";
		stringShippingFee += "</tr>";

		if(show == 1)
			$('#nationalCarrier tr:last').after(stringShippingFee);
		else
			return stringShippingFee;
	}

	
	function getShippingLocation(lastId, zone)
	{literal}{{/literal}
		var string = '';
		{foreach from=$internationalShippingLocation item=shippingLocation}
			if(zone != undefined && zone.indexOf('{$shippingLocation.location}') != -1)
			{literal}{{/literal}
			string += '<div class="shippinglocationOption"><input type="checkbox" checked="checked" name="internationalShippingLocation['+lastId+'][{$shippingLocation.location}] value="{$shippingLocation.location}">{$shippingLocation.description}</option></div>';
			}
			else
			{literal}{{/literal}
			string += '<div class="shippinglocationOption"><input type="checkbox" name="internationalShippingLocation['+lastId+'][{$shippingLocation.location}] value="{$shippingLocation.location}">{$shippingLocation.description}</option></div>';
			}
			
		{/foreach}
		return string;
	}


	function addInternationalShippingFee(idEbayCarrier, idPSCarrier, additionalFee, zone, zoneExcluded)
	{literal}{{/literal}
		var lastId = $('#internationalCarrier .internationalShipping').length;

		var string = "<div class='internationalShipping' data-id='"+lastId+"'>";
		string += "<table class='table'>";
		string += "<tr>";
		string += addShippingFee(0, 1, idEbayCarrier, idPSCarrier, additionalFee);
		string += "</tr>";
		string += "</table>";
		string += "<label>{l s='Add country you will ship to' mod='ebay'}</label>";
		string += "<div class='margin-form'	>"+getShippingLocation(lastId, zone)+"</div>";
		string += "<div style='width:100%;clear:both'></div>";
		string += '</div>';

		$('#internationalCarrier div.internationalShipping:last').after(string);
	}

	function excludeLocation()
	{literal}{{/literal}
		string = '<input type="hidden" value="1" name="excludeLocationHidden"/>'
		string += '<table class="allregion table">';
		
		{foreach from=$excludeShippingLocation.all item=region key=regionvalue name=count}
			{if $smarty.foreach.count.index % 4 == 0}
				string += "<tr>";
			{/if}
			string += "<td>";
			string += "<div class='excludedLocation'>";
			string += "<input type='checkbox' name='excludeLocation[{$regionvalue}]' {if in_array($regionvalue, $excludeShippingLocation.excluded)} checked='checked'{/if}/> {$region.description}<br/>";
			string += "<span class='showCountries' data-region='{$regionvalue}'>({l s='Show all countries'})</span>";
			string += "<div class='listcountry'></div>"
			string += "</div>";
			string += "</td>";

			{if $smarty.foreach.count.index % 4 == 3}
				string += "</tr>";
			{/if}
		{/foreach}
		string += "</table>";



		return string;
	}

	jQuery(document).ready(function($) 
	{literal}{{/literal}
		/* INIT */
		{foreach from=$existingNationalCarrier item=nationalCarrier}
			addShippingFee(1, 0, '{$nationalCarrier.ebay_carrier}', {$nationalCarrier.ps_carrier}, {$nationalCarrier.extra_fee});
		{/foreach}
		{literal}
		var zone = new Array();
		var zoneExcluded = new Array();
		{/literal}
		{foreach from=$existingInternationalCarrier item=internationalCarrier}
			zone = [];
			zoneExcluded = [];
			{foreach from=$internationalCarrier.shippingLocation item=shippingLocation}
				zone.push('{$shippingLocation.id_ebay_zone}');
			{/foreach}
			addInternationalShippingFee('{$internationalCarrier.ebay_carrier}', {$internationalCarrier.ps_carrier}, {$internationalCarrier.extra_fee}, zone, zoneExcluded);
		{/foreach}

		
		showExcludeLocation();


		/* EVENTS */
		bindElements();
	});


	function showExcludeLocation()
	{literal}{{/literal}
		$('#nolist').fadeOut('normal', function()
		{literal}{{/literal}
			$('#list').html(excludeLocation());	
			
		{literal}
		$('.showCountries').each(function()
		{
			var showcountries = $(this);
			$.ajax({
				url: '{/literal}{$module_dir}{literal}ajax/getCountriesLocation.php',
				type: 'POST',
				data: {region: $(this).attr('data-region')},
				complete: function(xhr, textStatus) {

				},
				success: function(data, textStatus, xhr) {
					showcountries.parent().find('.listcountry').html(data);
				},
				error: function(xhr, textStatus, errorThrown) {
				//called when there is an error
				}
			});
		});
		{/literal}

			bindElements();

		});


	}

	function bindElements()
	{literal}{{/literal}
		$('#internationalCarrier .deleteCarrier').unbind().click(function(){literal}{{/literal}
			$(this).parent().parent().parent().parent().parent().remove();
		});
		$('#nationalCarrier .deleteCarrier').unbind().click(function(){literal}{{/literal}
			$(this).parent().parent().remove();
		});
		$('.addExcludedZone').unbind().click(function(){literal}{{/literal}
			excludedButton = $(this);
			excluded = getExcludeShippingLocation(excludedButton.parent());
			$(this).before(excluded);
		});

		
		$('#addNationalCarrier').unbind().click(function(){literal}{{/literal}
			addShippingFee(1, 0);
			bindElements();
		});
		
		$('#addInternationalCarrier').unbind().click(function(){literal}{{/literal}
			addInternationalShippingFee();
			bindElements();
		});

		$('#createlist').unbind().click(function(){literal}{{/literal}
			showExcludeLocation();
		});

		$('.showCountries').unbind().click(function(){literal}{{/literal}
			$(this).hide().parent().find('.listcountry').show();
		});


	}
</script>
<style>
{literal}
	.internationalShipping{
		background-color: #FFF;
		border: 1px solid #AAA;
		margin-bottom: 10px;
	}

	.excludedLocation{
		float:left;
		min-height:30px;
		margin-right:10px;
		min-width:140px;
	}

	.excludeCountry{
		height:20px;
		padding-left:5px;
	}

	.excludeCountry input{
		margin-right:5px;
	}
	
	.allregion tr td{
		vertical-align: top;
	}
	
	.showCountries{
		color:blue;
		font-size:9px;
		padding-left:14px;
		cursor:pointer;
	}



	.listcountry{
		margin-top:10px;
		display:none;
	}

	.table tr td {
		border-bottom:none;
	}

	.internationalShipping input[type="checkbox"]{
		margin-right: 2px;
		margin-left: 0px;
		margin-top: -3px;
	}



	.shippinglocationOption{
		float: left;
		padding-top: 2px;
		margin-right:10px;
		margin-top: 3px;
		margin-bottom:5px;
	}


	.unquatre .onelineebaycarrier select{
		width:140px;
	}
{/literal}
</style>



<fieldset style="margin-top:10px;">
	<legend>{l s='Shipping method for domestic shipping' mod='ebay'}</legend>
	<p>{l s='Prestashop zone used to calculate shipping fees :' mod='ebay'}
		<select name="nationalZone" id="">
			{foreach from=$prestashopZone item=zone}
				<option value="{$zone.id_zone}" {if $zone.id_zone == $ebayZoneNational} selected="selected"{/if}>{$zone.name}</option>
			{/foreach}
		</select>
	</p>
	<table id="nationalCarrier" class="table">
		<tr>
		</tr>
	</table>
	
	<div class="margin-form" id="addNationalCarrier" style="cursor:pointer;">
	<span style="font-size:18px;font-weight:bold;padding-right:5px;">+</span>{l s='Add a new carrier option' mod='ebay'}
	</div>
</fieldset>

<fieldset style="margin-top:10px">
	<legend>{l s='Shipping Method for Interational Shipping' mod='ebay'}</legend>	
	<p>{l s='Prestashop zone used to calculate shipping fees  :' mod='ebay'}
		<select name="internationalZone" id="">
			{foreach from=$prestashopZone item=zone}
				<option value="{$zone.id_zone}" {if $zone.id_zone == $ebayZoneInternational} selected="selected"{/if}>{$zone.name}</option>
			{/foreach}
		</select>
	</p>
	<div id="internationalCarrier">
		<div class="internationalShipping"></div>
	</div>
	<div class="margin-form" id="addInternationalCarrier" style="cursor:pointer;">
	<span style="font-size:18px;font-weight:bold;padding-right:5px;">+</span>{l s='Add a new international carrier option' mod='ebay'}
</fieldset>

<fieldset style="margin-top:10px">
	<legend>{l s='Exclude shipping locations' mod='ebay'}</legend>
	<div id="nolist">
	</div>
	<div id="list">
		
	</div>
</fieldset>

<div class="margin-form" id="buttonEbayShipping" style="margin-top:20px;">
	<input class="button" name="submitSave" type="submit" id="save_ebay_shipping" value="Sauvegarder"/>
</div>


</form>
