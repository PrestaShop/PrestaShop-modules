{*
* @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
* @copyright Croissance NET
* @version 1.0
*}

<fieldset style="margin-top: 10px;">
	<legend>{l s='Products not included in the recommendations' mod='prediggo'}</legend>

	<ul id="prediggo_black_list_reco">
		{if !empty($aPrediggoProductsNotRecommendable)}
			{foreach from=$aPrediggoProductsNotRecommendable item="aPrediggoProductNotRecommendable"}
				<li data="{$aPrediggoProductNotRecommendable->id|intval}">
					{$aPrediggoProductNotRecommendable->name|escape:'htmlall':'UTF-8'}
					<input type="hidden" name="prediggo_products_ids_not_recommendable[]" value="{$aPrediggoProductNotRecommendable->id|intval}">
					<img src="../img/admin/delete.gif" style="cursor:pointer">
				</li>
			{/foreach}
		{/if}
	</ul>

	<div>
		{l s='Begin typing the first letters of the product name, then select the product from the drop-down list:' mod='prediggo'}
		<br/>
		<input type="text" value="" id="product_reco_autocomplete" />
	</div>

	<div class="center">
		<input type="submit" name="exportNotRecoSubmit" value="{l s='Save the black list' mod='prediggo'}" class="button" />
	</div>
</fieldset>

<fieldset style="margin-top: 10px;">
	<legend>{l s='Products not included in the searchs' mod='prediggo'}</legend>

	<ul id="prediggo_black_list_search">
		{if !empty($aPrediggoProductsNotSearchable)}
			{foreach from=$aPrediggoProductsNotSearchable item="aPrediggoProductNotSearchable"}
				<li data="{$aPrediggoProductNotSearchable->id|intval}">
					{$aPrediggoProductNotSearchable->name|escape:'htmlall':'UTF-8'}
					<input type="hidden" name="prediggo_products_ids_not_searchable[]" value="{$aPrediggoProductNotSearchable->id|intval}">
					<img src="../img/admin/delete.gif" style="cursor:pointer">
				</li>
			{/foreach}
		{/if}
	</ul>

	<div>
		{l s='Begin typing the first letters of the product name, then select the product from the drop-down list:' mod='prediggo'}
		<br/>
		<input type="text" value="" id="product_search_autocomplete" />
	</div>

	<div class="center">
		<input type="submit" name="exportNotSearchSubmit" value="{l s='Save the black list' mod='prediggo'}" class="button" />
	</div>
</fieldset>