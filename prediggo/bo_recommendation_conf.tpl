{*
* @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
* @copyright Croissance NET
* @version 1.0
*}

<div id="recommandation_conf">
	<form action="{$formAction}#recommandation_conf" method="post">
		<fieldset>
			<legend>{l s='Main recommendations settings' mod='prediggo'}</legend>

			<label>{l s='Logs storage activation' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="radio" value="1" name="prediggo_logs_fo_file_generation" {if $oPrediggoRecommendationConfig->logs_fo_file_generation}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Enable' mod='prediggo'}" src="../img/admin/enabled.gif"> {l s='Yes' mod='prediggo'}</label>
				<input type="radio" value="0" name="prediggo_logs_fo_file_generation" {if !$oPrediggoRecommendationConfig->logs_fo_file_generation}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Disable' mod='prediggo'}" src="../img/admin/disabled.gif"> {l s='No' mod='prediggo'}</label>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='URL of the recommendations server' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="text" size="60" name="prediggo_server_url_recommendations" value="{$oPrediggoRecommendationConfig->server_url_recommendations|escape:'htmlall':'UTF-8'}" />
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<div class="center">
				<input type="submit" name="mainRecommendationConfSubmit" value="{l s='Update' mod='prediggo'}" class="button" />
			</div>
		</fieldset>

		<fieldset style="margin-top: 10px;">
			<legend>{l s='Home page configuration' mod='prediggo'}</legend>

			<label>{l s='Display the recommendations block' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="radio" value="1" name="prediggo_home_recommendations" {if $oPrediggoRecommendationConfig->home_recommendations}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Enable' mod='prediggo'}" src="../img/admin/enabled.gif"> {l s='Yes' mod='prediggo'}</label>
				<input type="radio" value="0" name="prediggo_home_recommendations" {if !$oPrediggoRecommendationConfig->home_recommendations}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Disable' mod='prediggo'}" src="../img/admin/disabled.gif"> {l s='No' mod='prediggo'}</label>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Number of items in the recommendations block' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="text" size="4" name="prediggo_home_nb_items" value="{$oPrediggoRecommendationConfig->home_nb_items|intval}" />
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Title of the recommendation block' mod='prediggo'}</label>
			<div class="margin-form">
				{foreach from=$aLanguages item="aLanguage"}
				<div id="prediggo_home_block_title_{$aLanguage.id_lang|intval}" style="display: {if $aLanguage.id_lang|intval == $cookie->id_lang|intval} block {else} none{/if}; float: left;">
					{assign var='id_lang' value=$aLanguage.id_lang|intval}
					<input type="text" size="25" name="prediggo_home_block_title[{$id_lang}]" value="{$oPrediggoRecommendationConfig->home_block_title.$id_lang|escape:'htmlall':'UTF-8'}" />
				</div>
				{/foreach}
				{$oModule->displayFlags($aLanguages,$cookie->id_lang,'prediggo_home_block_title','prediggo_home_block_title')}
				<br class="clear"/>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<div class="center">
				<input type="submit" name="exportHomeRecommendationConfSubmit" value="{l s='Update' mod='prediggo'}" class="button" />
			</div>
		</fieldset>

		<fieldset style="margin-top: 10px;">
			<legend>{l s='404 page configuration' mod='prediggo'}</legend>

			<label>{l s='Display the recommendations block' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="radio" value="1" name="prediggo_error_recommendations" {if $oPrediggoRecommendationConfig->error_recommendations}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Enable' mod='prediggo'}" src="../img/admin/enabled.gif"> {l s='Yes' mod='prediggo'}</label>
				<input type="radio" value="0" name="prediggo_error_recommendations" {if !$oPrediggoRecommendationConfig->error_recommendations}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Disable' mod='prediggo'}" src="../img/admin/disabled.gif"> {l s='No' mod='prediggo'}</label>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Number of items in the recommendations block' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="text" size="4" name="prediggo_error_nb_items" value="{$oPrediggoRecommendationConfig->error_nb_items|intval}" />
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Title of the recommendation block' mod='prediggo'}</label>
			<div class="margin-form">
				{foreach from=$aLanguages item="aLanguage"}
				<div id="prediggo_error_block_title_{$aLanguage.id_lang|intval}" style="display: {if $aLanguage.id_lang|intval == $cookie->id_lang|intval} block {else} none{/if}; float: left;">
					{assign var='id_lang' value=$aLanguage.id_lang|intval}
					<input type="text" size="25" name="prediggo_error_block_title[{$id_lang}]" value="{$oPrediggoRecommendationConfig->error_block_title.$id_lang|escape:'htmlall':'UTF-8'}" />
				</div>
				{/foreach}
				{$oModule->displayFlags($aLanguages,$cookie->id_lang,'prediggo_error_block_title','prediggo_error_block_title')}
				<br class="clear"/>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<div class="center">
				<input type="submit" name="export404RecommendationConfSubmit" value="{l s='Update' mod='prediggo'}" class="button" />
			</div>
		</fieldset>

		<fieldset style="margin-top: 10px;">
			<legend>{l s='Product page configuration' mod='prediggo'}</legend>

			<label>{l s='Display the recommendations block' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="radio" value="1" name="prediggo_product_recommendations" {if $oPrediggoRecommendationConfig->product_recommendations}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Enable' mod='prediggo'}" src="../img/admin/enabled.gif"> {l s='Yes' mod='prediggo'}</label>
				<input type="radio" value="0" name="prediggo_product_recommendations" {if !$oPrediggoRecommendationConfig->product_recommendations}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Disable' mod='prediggo'}" src="../img/admin/disabled.gif"> {l s='No' mod='prediggo'}</label>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Number of items in the recommendations block' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="text" size="4" name="prediggo_product_nb_items" value="{$oPrediggoRecommendationConfig->product_nb_items|intval}" />
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Title of the recommendation block' mod='prediggo'}</label>
			<div class="margin-form">
				{foreach from=$aLanguages item="aLanguage"}
				<div id="prediggo_product_block_title_{$aLanguage.id_lang|intval}" style="display: {if $aLanguage.id_lang|intval == $cookie->id_lang|intval} block {else} none{/if}; float: left;">
					{assign var='id_lang' value=$aLanguage.id_lang|intval}
					<input type="text" size="25" name="prediggo_product_block_title[{$id_lang}]" value="{$oPrediggoRecommendationConfig->product_block_title.$id_lang|escape:'htmlall':'UTF-8'}" />
				</div>
				{/foreach}
				{$oModule->displayFlags($aLanguages,$cookie->id_lang,'prediggo_product_block_title','prediggo_product_block_title')}
				<br class="clear"/>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<div class="center">
				<input type="submit" name="exportProductRecommendationConfSubmit" value="{l s='Update' mod='prediggo'}" class="button" />
			</div>
		</fieldset>

		<fieldset style="margin-top: 10px;">
			<legend>{l s='Category page configuration' mod='prediggo'}</legend>

			<label>{l s='Display the recommendations block' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="radio" value="1" name="prediggo_category_recommendations" {if $oPrediggoRecommendationConfig->category_recommendations}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Enable' mod='prediggo'}" src="../img/admin/enabled.gif"> {l s='Yes' mod='prediggo'}</label>
				<input type="radio" value="0" name="prediggo_category_recommendations" {if !$oPrediggoRecommendationConfig->category_recommendations}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Disable' mod='prediggo'}" src="../img/admin/disabled.gif"> {l s='No' mod='prediggo'}</label>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Number of items in the recommendations block' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="text" size="4" name="prediggo_category_nb_items" value="{$oPrediggoRecommendationConfig->category_nb_items|intval}" />
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Title of the recommendation block' mod='prediggo'}</label>
			<div class="margin-form">
				{foreach from=$aLanguages item="aLanguage"}
				<div id="prediggo_category_block_title_{$aLanguage.id_lang|intval}" style="display: {if $aLanguage.id_lang|intval == $cookie->id_lang|intval} block {else} none{/if}; float: left;">
					{assign var='id_lang' value=$aLanguage.id_lang|intval}
					<input type="text" size="25" name="prediggo_category_block_title[{$id_lang}]" value="{$oPrediggoRecommendationConfig->category_block_title.$id_lang|escape:'htmlall':'UTF-8'}" />
				</div>
				{/foreach}
				{$oModule->displayFlags($aLanguages,$cookie->id_lang,'prediggo_category_block_title','prediggo_category_block_title')}
				<br class="clear"/>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<div class="center">
				<input type="submit" name="exportCategoryRecommendationConfSubmit" value="{l s='Update' mod='prediggo'}" class="button" />
			</div>
		</fieldset>

		<fieldset style="margin-top: 10px;">
			<legend>{l s='Customer page configuration' mod='prediggo'}</legend>

			<label>{l s='Display the recommendations block' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="radio" value="1" name="prediggo_customer_recommendations" {if $oPrediggoRecommendationConfig->customer_recommendations}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Enable' mod='prediggo'}" src="../img/admin/enabled.gif"> {l s='Yes' mod='prediggo'}</label>
				<input type="radio" value="0" name="prediggo_customer_recommendations" {if !$oPrediggoRecommendationConfig->customer_recommendations}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Disable' mod='prediggo'}" src="../img/admin/disabled.gif"> {l s='No' mod='prediggo'}</label>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Number of items in the recommendations block' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="text" size="4" name="prediggo_customer_nb_items" value="{$oPrediggoRecommendationConfig->customer_nb_items|intval}" />
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Title of the recommendation block' mod='prediggo'}</label>
			<div class="margin-form">
				{foreach from=$aLanguages item="aLanguage"}
				<div id="prediggo_customer_block_title_{$aLanguage.id_lang|intval}" style="display: {if $aLanguage.id_lang|intval == $cookie->id_lang|intval} block {else} none{/if}; float: left;">
					{assign var='id_lang' value=$aLanguage.id_lang|intval}
					<input type="text" size="25" name="prediggo_customer_block_title[{$id_lang}]" value="{$oPrediggoRecommendationConfig->customer_block_title.$id_lang|escape:'htmlall':'UTF-8'}" />
				</div>
				{/foreach}
				{$oModule->displayFlags($aLanguages,$cookie->id_lang,'prediggo_customer_block_title','prediggo_customer_block_title')}
				<br class="clear"/>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<div class="center">
				<input type="submit" name="exportCustomerRecommendationConfSubmit" value="{l s='Update' mod='prediggo'}" class="button" />
			</div>
		</fieldset>

		<fieldset style="margin-top: 10px;">
			<legend>{l s='Cart page configuration' mod='prediggo'}</legend>

			<label>{l s='Display the recommendations block' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="radio" value="1" name="prediggo_cart_recommendations" {if $oPrediggoRecommendationConfig->cart_recommendations}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Enable' mod='prediggo'}" src="../img/admin/enabled.gif"> {l s='Yes' mod='prediggo'}</label>
				<input type="radio" value="0" name="prediggo_cart_recommendations" {if !$oPrediggoRecommendationConfig->cart_recommendations}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Disable' mod='prediggo'}" src="../img/admin/disabled.gif"> {l s='No' mod='prediggo'}</label>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Number of items in the recommendations block' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="text" size="4" name="prediggo_cart_nb_items" value="{$oPrediggoRecommendationConfig->cart_nb_items|intval}" />
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Title of the recommendation block' mod='prediggo'}</label>
			<div class="margin-form">
				{foreach from=$aLanguages item="aLanguage"}
				<div id="prediggo_cart_block_title_{$aLanguage.id_lang|intval}" style="display: {if $aLanguage.id_lang|intval == $cookie->id_lang|intval} block {else} none{/if}; float: left;">
					{assign var='id_lang' value=$aLanguage.id_lang|intval}
					<input type="text" size="25" name="prediggo_cart_block_title[{$id_lang}]" value="{$oPrediggoRecommendationConfig->cart_block_title.$id_lang|escape:'htmlall':'UTF-8'}" />
				</div>
				{/foreach}
				{$oModule->displayFlags($aLanguages,$cookie->id_lang,'prediggo_cart_block_title','prediggo_cart_block_title')}
				<br class="clear"/>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<div class="center">
				<input type="submit" name="exportCartRecommendationConfSubmit" value="{l s='Update' mod='prediggo'}" class="button" />
			</div>
		</fieldset>

		<fieldset style="margin-top: 10px;">
			<legend>{l s='Block layered page configuration' mod='prediggo'}</legend>

			<label>{l s='Display the recommendations block' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="radio" value="1" name="prediggo_blocklayered_recommendations" {if $oPrediggoRecommendationConfig->blocklayered_recommendations}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Enable' mod='prediggo'}" src="../img/admin/enabled.gif"> {l s='Yes' mod='prediggo'}</label>
				<input type="radio" value="0" name="prediggo_blocklayered_recommendations" {if !$oPrediggoRecommendationConfig->blocklayered_recommendations}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Disable' mod='prediggo'}" src="../img/admin/disabled.gif"> {l s='No' mod='prediggo'}</label>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Number of items in the recommendations block' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="text" size="4" name="prediggo_blocklayered_nb_items" value="{$oPrediggoRecommendationConfig->blocklayered_nb_items|intval}" />
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Title of the recommendation block' mod='prediggo'}</label>
			<div class="margin-form">
				{foreach from=$aLanguages item="aLanguage"}
				<div id="prediggo_blocklayered_block_title_{$aLanguage.id_lang|intval}" style="display: {if $aLanguage.id_lang|intval == $cookie->id_lang|intval} block {else} none{/if}; float: left;">
					{assign var='id_lang' value=$cookie->id_lang|intval}
					<input type="text" size="25" name="prediggo_blocklayered_block_title[{$id_lang}]" value="{$oPrediggoRecommendationConfig->blocklayered_block_title.$id_lang|escape:'htmlall':'UTF-8'}" />
				</div>
				{/foreach}
				{$oModule->displayFlags($aLanguages,$cookie->id_lang,'prediggo_blocklayered_block_title','prediggo_blocklayered_block_title')}
				<br class="clear"/>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<div class="center">
				<input type="submit" name="exportBlocklayeredRecommendationConfSubmit" value="{l s='Update' mod='prediggo'}" class="button" />
			</div>
		</fieldset>

	</form>
</div>