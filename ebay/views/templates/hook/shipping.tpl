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

