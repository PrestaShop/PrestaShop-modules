{*
* @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
* @copyright Croissance NET
* @version 1.0
*}

<fieldset style="margin-top: 10px;">
	<legend>{l s='Prediggo attributes selection' mod='prediggo'}</legend>
	<ul id="prediggo_attributes">
		{foreach from=$aGroupAttributes item="aGroupAttribute"}
			<li>
				<input type="checkbox" name="prediggo_attributes_groups_ids[]" value="{$aGroupAttribute.id_attribute_group|intval}" {if in_array($aGroupAttribute.id_attribute_group|intval, $aPrediggoAttributesGroups)}checked="checked"{/if} />
				{l s='Group of Attributes:' mod='prediggo'} {$aGroupAttribute.name|escape:'htmlall':'UTF-8'}
			</li>
		{/foreach}

		{foreach from=$aFeatures item="aFeature"}
			<li>
				<input type="checkbox" name="prediggo_features_ids[]" value="{$aFeature.id_feature|intval}" {if in_array($aFeature.id_feature|intval, $aPrediggoFeatures)}checked="checked"{/if} />
				{l s='Feature:' mod='prediggo'} {$aFeature.name|escape:'htmlall':'UTF-8'}
			</li>
		{/foreach}
	</ul>

	<div class="center">
		<input type="submit" name="exportPrediggoAttributesSubmit" value="{l s='Save the prediggo attributes' mod='prediggo'}" class="button" />
	</div>
</fieldset>