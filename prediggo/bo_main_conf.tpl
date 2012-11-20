{*
* @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
* @copyright Croissance NET
* @version 1.0
*}

<div id="main_conf">
	<form action="{$formAction}#main_conf" method="post">
		<fieldset>
			<label>{l s='Web Site ID' mod='prediggo'}</label>

			<div class="margin-form">
				<input type="text" size="20" name="prediggo_web_site_id" value="{$oPrediggoConfig->web_site_id|escape:'htmlall':'UTF-8'}" />
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Store Code ID' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="text" size="20" name="prediggo_store_code_id" value="{$oPrediggoConfig->store_code_id|escape:'htmlall':'UTF-8'}" />
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Default Profile' mod='prediggo'}</label>
			<div class="margin-form">
				<select name="prediggo_default_profile_id">
					{foreach from=$aLanguages item="aLanguage"}
						<option value="{$aLanguage.id_lang|intval}" {if $oPrediggoConfig->default_profile_id|intval == $aLanguage.id_lang|intval}selected="selected"{/if}>{$aLanguage.id_lang|intval} [{$aLanguage.iso_code}]</option>
					{/foreach}
				</select>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<div class="center">
				<input type="submit" name="mainConfSubmit" value="{l s='Update' mod='prediggo'}" class="button" />
			</div>
		</fieldset>
	</form>
</div>