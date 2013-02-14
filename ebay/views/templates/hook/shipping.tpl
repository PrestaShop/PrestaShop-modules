<fieldset>
	<legend>{l s='Global Configuration' mod='ebay'}</legend>
	<form action="{$formUrl}" method="post">	
		<label>{l s='Delivery Time' mod='ebay'}</label>
		<div class="margin-form">
			<select name="deliveryTime" id="deliveryTime">
				{foreach from=$deliveryTimeOptions item=deliveryTimeOption}
					<option value="{$deliveryTimeOption.DispatchTimeMax}" {if $deliveryTimeOption.DispatchTimeMax == $deliveryTime} selected="selected"{/if}>{$deliveryTimeOption.description}</option>
				{/foreach}
			</select>
		</div>


		<div class="margin-form" id="buttonEbayShipping">
			<input type="hidden" name="submitSaveShippingGlobal" value="1"/>
			<input class="button" name="submitSave" type="submit" id="save_ebay_shipping" value="Sauvegarder"/>
		</div>
	</form>
</fieldset>

{debug}
<script type="text/javascript">
	function nationalShippingFee(idEbayCarrier, idPSCarrier, additionalFee){
		var stringNaionalShippingFee = "<tr>
											<td>{l s='Choose your eBay carrier'}</td>
											<td>
												<select name="" id="">
													{foreach from=$eBayCarrier item=$carrier}
													<option value='{$carrier.shippingServiceID}'>{$carrier.description}</option>
													{/foreach}
												</select>
											</td>
											<td>
												{l s='Associate it with a PrestaShop carrier'}
											</td>
											<td>
												<select name="" id="">
													{foreach from=$psCarrier item=$carrier}
													<option value='{$carrier.id}'>{$carrier.name}</option>
													{/foreach}
												</select>
											</td>
											<td>
												{l s='Add extra fee for this carrier'}
											<td>
											<td>
												<input type='text' name='extrafee'>
											</td>
										</tr>";


	}
</script>


<form action="{$formUrl}" method="post">
<fieldset>
	<legend>{l s='Shipping Method for National Shipping' mod='ebay'}</legend>

	<table id="nationalCarrier">
		
	</table>
	
	<div class="margin-form">
	<span style="font-size:18px;font-weight:bold;">+</span>{l s='Add a new Carrier option in eBay'}
	</div>

</fieldset>
</form>
