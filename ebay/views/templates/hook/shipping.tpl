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
	

	function addShippingFee(show, internationalOnly, idEbayCarrier, idPSCarrier, additionalFee){
		var currentShippingService = -1;
		if(internationalOnly == 1){
			var lastId = $('#internationalCarrier .internationalShipping').length;
			internationsuffix = '_international';
		}
		else{
			var lastId = $('#nationalCarrier tr').length;
			internationsuffix = '';
		}
			



		if(additionalFee == undefined)
			additionalFee = '';

		var stringShippingFee = "<tr>";
		stringShippingFee += "<td>{l s='Choose your eBay carrier'}</td>";
		stringShippingFee += 	"<td>";
		stringShippingFee += 		"<select name='ebayCarrier"+internationsuffix+"["+lastId+"]' id='ebayCarrier_"+lastId+"'>";
		{foreach from=$eBayCarrier item=carrier}
		currentShippingService = {$carrier.shippingServiceID};
		//check for international
		if((internationalOnly == 1 && '{$carrier.InternationalService}'=== 'true') || internationalOnly == 0)
		{
			if(currentShippingService == idEbayCarrier){
				stringShippingFee += 		"<option value='{$carrier.shippingServiceID}' selected='selected'>{$carrier.description}</option>";
			}
			else{
				stringShippingFee += 		"<option value='{$carrier.shippingServiceID}'>{$carrier.description}</option>";
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
		if(currentShippingService == idPSCarrier){
			stringShippingFee += 		"<option value='{$carrier.id_carrier}' selected='selected'>{$carrier.name}</option>";
		}
		else{
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

	

	function getExcludeShippingLocation(jQueryparent, idExisting, lastId, lastIDExcludedLocation){
		if(lastId == undefined)
			var lastId = jQueryparent.attr('data-id');
		if(lastIDExcludedLocation == undefined)
			var lastIDExcludedLocation = jQueryparent.find('.internationalExcludedShippingLocation').length;
		


		var string = "";
		string += "<select name='internationalExcludedShippingLocation["+lastId+"]["+lastIDExcludedLocation+"]' style='margin-right:10px' class='internationalExcludedShippingLocation'>";
		string += "<option value='-1'>No excluded zone</option>";	
		{foreach from=$excludeShippingLocation item=shippingRegion key=region}
		string += "<option value='{$region}''>{$region}</option>";
			{foreach from=$shippingRegion item=shippingLocation}
				if(idExisting == '{$shippingLocation.location}')
					string += "<option value='{$shippingLocation.location}' selected='selected'>---{$shippingLocation.description}</option>";
				else
					string += "<option value='{$shippingLocation.location}'>--- {$shippingLocation.description}</option>";
			{/foreach}
		{/foreach}
		string += "</select>";
		return string;
	}

	function getShippingLocation(lastId, zone){
		var string = '';
		{foreach from=$internationalShippingLocation item=shippingLocation}
			if(zone != undefined && zone.indexOf('{$shippingLocation.location}') != -1){
			string += '<div class="shippinglocationOption"><input type="checkbox" checked="checked" name="internationalShippingLocation['+lastId+'][{$shippingLocation.location}] value="{$shippingLocation.location}">{$shippingLocation.description}</option></div>';
			}
			else{
			string += '<div class="shippinglocationOption"><input type="checkbox" name="internationalShippingLocation['+lastId+'][{$shippingLocation.location}] value="{$shippingLocation.location}">{$shippingLocation.description}</option></div>';
			}
			
		{/foreach}
		return string;
	}


	function addInternationalShippingFee(idEbayCarrier, idPSCarrier, additionalFee, zone, zoneExcluded)
	{
		var lastId = $('#internationalCarrier .internationalShipping').length;

		var string = "<div class='internationalShipping' data-id='"+lastId+"'>";
		string += "<table class='table'>";
		string += "<tr>";
		string += addShippingFee(0, 1, idEbayCarrier, idPSCarrier, additionalFee);
		string += "</tr>";
		string += "</table>";
		string += "<label>{l s='Add eBay zone for this carrier' mod='ebay'}</label>";
		string += "<div class='margin-form'	>"+getShippingLocation(lastId, zone)+"</div>";
		string += "<div style='width:100%;clear:both'></div>";
		string += "<label style='clear:both;'>{l s='Exclude eBay zone for the carrier'}</label>";
		if(zoneExcluded != undefined)
			for(i = 0; i < zoneExcluded.length; i++)
				string += getExcludeShippingLocation(null, zoneExcluded[i], lastId, i);
		string += "<span style='font-size:18px;font-weight:bold;padding-right:5px;clear:both;cursor:pointer;' class='addExcludedZone' title='{l s='Add a new excluded zone' mod='ebay'}'>+</span>";
		string += '</div>';

		$('#internationalCarrier div.internationalShipping:last').after(string);
	}


	jQuery(document).ready(function($) {
		/* INIT */
		{foreach from=$existingNationalCarrier item=nationalCarrier}
			addShippingFee(1, 0, {$nationalCarrier.ebay_carrier}, {$nationalCarrier.ps_carrier}, {$nationalCarrier.extra_fee});
		{/foreach}
		var zone = new Array();
		var zoneExcluded = new Array();
		{foreach from=$existingInternationalCarrier item=internationalCarrier}
			zone = [];
			zoneExcluded = [];
			{foreach from=$internationalCarrier.shippingLocation item=shippingLocation}
				zone.push('{$shippingLocation.id_ebay_zone}');
			{/foreach}
			{foreach from=$internationalCarrier.excludedShippingLocation item=excludedShippingLocation}
				zoneExcluded.push('{$excludedShippingLocation.id_ebay_zone_excluded}');
			{/foreach}

			addInternationalShippingFee({$internationalCarrier.ebay_carrier}, {$internationalCarrier.ps_carrier}, {$internationalCarrier.extra_fee}, zone, zoneExcluded);
		{/foreach}

		/* EVENTS */
		$('#addNationalCarrier').click(function(){
			addShippingFee(1, 0);
			bindElements();
		});
		
		$('#addInternationalCarrier').click(function(){
			addInternationalShippingFee();
			bindElements();
		});

		bindElements();
	});

	function bindElements(){
		$('#internationalCarrier .deleteCarrier').unbind().click(function(){
			$(this).parent().parent().parent().parent().parent().remove();
		});
		$('#nationalCarrier .deleteCarrier').unbind().click(function(){
			$(this).parent().parent().remove();
		});
		$('.addExcludedZone').unbind().click(function(){
			excludedButton = $(this);
			excluded = getExcludeShippingLocation(excludedButton.parent());
			$(this).before(excluded);
		});

	}
</script>

<style>
.internationalShipping{
	background-color: #FFF;
	border: 1px solid #AAA;
	margin-bottom: 10px;
}

	

.table tr td {
	border-bottom:none;
}

.internationalShipping input[type="checkbox"]{
	margin-right: 2px;
	margin-left: 10px;
	margin-top: -3px;
}

.internationalShipping .shippinglocationOption:first-child  input[type="checkbox"]{
	margin-left:0px;
}

.shippinglocationOption{
	float: left;
	padding-top: 2px;
	margin-top: 3px;
}
</style>



<fieldset style="margin-top:10px;">
	<legend>{l s='Shipping Method for National Shipping' mod='ebay'}</legend>

	<table id="nationalCarrier" class="table">
		<tr>
		</tr>
	</table>
	
	<div class="margin-form" id="addNationalCarrier" style="cursor:pointer;">
	<span style="font-size:18px;font-weight:bold;padding-right:5px;">+</span>{l s='Add a new Carrier option in eBay'}
	</div>
</fieldset>

<fieldset style="margin-top:10px">
	<legend>{l s='Shipping Method for Interational Shipping' mod='ebay'}</legend>	
	<div id="internationalCarrier">
		<div class="internationalShipping"></div>
	</div>
	<div class="margin-form" id="addInternationalCarrier" style="cursor:pointer;">
	<span style="font-size:18px;font-weight:bold;padding-right:5px;">+</span>{l s='Add a new international Carrier option in eBay'}
</fieldset>

<div class="margin-form" id="buttonEbayShipping" style="margin-top:20px;">
	<input class="button" name="submitSave" type="submit" id="save_ebay_shipping" value="Sauvegarder"/>
</div>


</form>
