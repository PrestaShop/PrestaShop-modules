{*
* @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
* @copyright Croissance NET
* @version 1.0
*}

<div id="search_conf">
	<form action="{$formAction}#search_conf" method="post">
		<fieldset>
			<legend>{l s='Main search settings' mod='prediggo'}</legend>

			<label>{l s='Display the search block' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="radio" value="1" name="prediggo_search_active" {if $oPrediggoSearchConfig->search_active}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Enable' mod='prediggo'}" src="../img/admin/enabled.gif"> {l s='Yes' mod='prediggo'}</label>
				<input type="radio" value="0" name="prediggo_search_active" {if !$oPrediggoSearchConfig->search_active}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Disable' mod='prediggo'}" src="../img/admin/disabled.gif"> {l s='No' mod='prediggo'}</label>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Number of items per page' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="text" size="4" name="prediggo_search_nb_items" value="{$oPrediggoSearchConfig->search_nb_items|intval}" />
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Minimum number of chars to launch a search' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="text" size="4" name="prediggo_search_nb_min_chars" value="{$oPrediggoSearchConfig->search_nb_min_chars|intval}" />
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Logs storage activation' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="radio" value="1" name="prediggo_logs_fo_file_generation" {if $oPrediggoSearchConfig->logs_fo_file_generation}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Enable' mod='prediggo'}" src="../img/admin/enabled.gif"> {l s='Yes' mod='prediggo'}</label>
				<input type="radio" value="0" name="prediggo_logs_fo_file_generation" {if !$oPrediggoSearchConfig->logs_fo_file_generation}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Disable' mod='prediggo'}" src="../img/admin/disabled.gif"> {l s='No' mod='prediggo'}</label>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='URL of the search server' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="text" size="60" name="prediggo_server_url_search" value="{$oPrediggoSearchConfig->server_url_search|escape:'htmlall':'UTF-8'}" />
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Searchandizing activation' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="radio" value="1" name="prediggo_searchandizing_active" {if $oPrediggoSearchConfig->searchandizing_active}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Enable' mod='prediggo'}" src="../img/admin/enabled.gif"> {l s='Yes' mod='prediggo'}</label>
				<input type="radio" value="0" name="prediggo_searchandizing_active" {if !$oPrediggoSearchConfig->searchandizing_active}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Disable' mod='prediggo'}" src="../img/admin/disabled.gif"> {l s='No' mod='prediggo'}</label>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Layered navigation activation' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="radio" value="1" name="prediggo_layered_navigation_active" {if $oPrediggoSearchConfig->layered_navigation_active}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Enable' mod='prediggo'}" src="../img/admin/enabled.gif"> {l s='Yes' mod='prediggo'}</label>
				<input type="radio" value="0" name="prediggo_layered_navigation_active" {if !$oPrediggoSearchConfig->layered_navigation_active}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Disable' mod='prediggo'}" src="../img/admin/disabled.gif"> {l s='No' mod='prediggo'}</label>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<div class="center">
				<input type="submit" name="mainSearchConfSubmit" value="{l s='Update' mod='prediggo'}" class="button" />
			</div>
		</fieldset>

		<fieldset style="margin-top: 10px;">
			<legend>{l s='Autocompletion configuration' mod='prediggo'}</legend>

			<label>{l s='Autocompletion activation' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="radio" value="1" name="prediggo_autocompletion_active" {if $oPrediggoSearchConfig->autocompletion_active}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Enable' mod='prediggo'}" src="../img/admin/enabled.gif"> {l s='Yes' mod='prediggo'}</label>
				<input type="radio" value="0" name="prediggo_autocompletion_active" {if !$oPrediggoSearchConfig->autocompletion_active}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Disable' mod='prediggo'}" src="../img/admin/disabled.gif"> {l s='No' mod='prediggo'}</label>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Number of items in the search autocompletion' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="text" size="4" name="prediggo_autocompletion_nb_items" value="{$oPrediggoSearchConfig->autocompletion_nb_items|intval}" />
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Suggestion activation' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="radio" value="1" name="prediggo_suggest_active" {if $oPrediggoSearchConfig->suggest_active}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Enable' mod='prediggo'}" src="../img/admin/enabled.gif"> {l s='Yes' mod='prediggo'}</label>
				<input type="radio" value="0" name="prediggo_suggest_active" {if !$oPrediggoSearchConfig->suggest_active}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Disable' mod='prediggo'}" src="../img/admin/disabled.gif"> {l s='No' mod='prediggo'}</label>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='List of suggestion' mod='prediggo'}</label>
			<div class="margin-form">
				{foreach from=$aLanguages item="aLanguage"}
				<div id="prediggo_suggest_words_{$aLanguage.id_lang|intval}" style="display: {if $aLanguage.id_lang|intval == $cookie->id_lang|intval} block {else} none{/if}; float: left;">
					{assign var='id_lang' value=$aLanguage.id_lang|intval}
					<input type="text" size="25" name="prediggo_suggest_words[{$id_lang}]" value="{$oPrediggoSearchConfig->suggest_words.$id_lang|escape:'htmlall':'UTF-8'}" />
				</div>
				{/foreach}
				{$oModule->displayFlags($aLanguages,$cookie->id_lang,'prediggo_suggest_words','prediggo_suggest_words')}
				<br class="clear"/>
				<p>{l s='List of keywords separated by comma (iPad 2, iPhone 4S, iPhone)' mod='prediggo'}</p>
			</div>

			<div class="center">
				<input type="submit" name="exportSearchAutocompletionConfSubmit" value="{l s='Update' mod='prediggo'}" class="button" />
			</div>
		</fieldset>
	</form>
</div>