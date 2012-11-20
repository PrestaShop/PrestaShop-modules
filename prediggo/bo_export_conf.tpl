{*
* @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
* @copyright Croissance NET
* @version 1.0
*}

<div id="export_conf">
	<form action="{$formAction}#export_conf" method="post">
		<fieldset>
			<legend>{l s='Manual Generation' mod='prediggo'}</legend>

			<label>{l s='Products file generation activation' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="radio" value="1" name="prediggo_products_file_generation" {if $oPrediggoExportConfig->products_file_generation}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Enable' mod='prediggo'}" src="../img/admin/enabled.gif"> {l s='Yes' mod='prediggo'}</label>
				<input type="radio" value="0" name="prediggo_products_file_generation" {if !$oPrediggoExportConfig->products_file_generation}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Disable' mod='prediggo'}" src="../img/admin/disabled.gif"> {l s='No' mod='prediggo'}</label>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Orders file generation activation' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="radio" value="1" name="prediggo_orders_file_generation" {if $oPrediggoExportConfig->orders_file_generation}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Enable' mod='prediggo'}" src="../img/admin/enabled.gif"> {l s='Yes' mod='prediggo'}</label>
				<input type="radio" value="0" name="prediggo_orders_file_generation" {if !$oPrediggoExportConfig->orders_file_generation}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Disable' mod='prediggo'}" src="../img/admin/disabled.gif"> {l s='No' mod='prediggo'}</label>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Customers file generation activation' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="radio" value="1" name="prediggo_customers_file_generation" {if $oPrediggoExportConfig->customers_file_generation}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Enable' mod='prediggo'}" src="../img/admin/enabled.gif"> {l s='Yes' mod='prediggo'}</label>
				<input type="radio" value="0" name="prediggo_customers_file_generation" {if !$oPrediggoExportConfig->customers_file_generation}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Disable' mod='prediggo'}" src="../img/admin/disabled.gif"> {l s='No' mod='prediggo'}</label>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Logs storage activation' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="radio" value="1" name="prediggo_logs_file_generation" {if $oPrediggoExportConfig->logs_file_generation}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Enable' mod='prediggo'}" src="../img/admin/enabled.gif"> {l s='Yes' mod='prediggo'}</label>
				<input type="radio" value="0" name="prediggo_logs_file_generation" {if !$oPrediggoExportConfig->logs_file_generation}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Disable' mod='prediggo'}" src="../img/admin/disabled.gif"> {l s='No' mod='prediggo'}</label>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Product\'s image cover export activation' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="radio" value="1" name="prediggo_export_product_image" {if $oPrediggoExportConfig->export_product_image}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Enable' mod='prediggo'}" src="../img/admin/enabled.gif"> {l s='Yes' mod='prediggo'}</label>
				<input type="radio" value="0" name="prediggo_export_product_image" {if !$oPrediggoExportConfig->export_product_image}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Disable' mod='prediggo'}" src="../img/admin/disabled.gif"> {l s='No' mod='prediggo'}</label>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Product\'s description export activation' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="radio" value="1" name="prediggo_export_product_description" {if $oPrediggoExportConfig->export_product_description}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Enable' mod='prediggo'}" src="../img/admin/enabled.gif"> {l s='Yes' mod='prediggo'}</label>
				<input type="radio" value="0" name="prediggo_export_product_description" {if !$oPrediggoExportConfig->export_product_description}checked="checked"{/if}>
				<label class="t"><img alt="{l s='Disable' mod='prediggo'}" src="../img/admin/disabled.gif"> {l s='No' mod='prediggo'}</label>
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Product minimum quantity' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="text" size="20" name="prediggo_export_product_min_quantity" value="{$oPrediggoExportConfig->export_product_min_quantity|intval}" />
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Number of days considering that an order can be exported' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="text" size="20" name="prediggo_nb_days_order_valide" value="{$oPrediggoExportConfig->nb_days_order_valide|intval}" />
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Number of days considering that a customer can be exported' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="text" size="20" name="prediggo_nb_days_customer_last_visit_valide" value="{$oPrediggoExportConfig->nb_days_customer_last_visit_valide|intval}" />
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<div class="center">
				<input type="submit" name="exportConfSubmit" value="{l s='Update' mod='prediggo'}" class="button" />
			</div>

			<br/>

			<p>
				{l s='Launch the files export by clicking on the following button:' mod='prediggo'}
				<input type="submit" name="manualExportSubmit" value="{l s='Export files' mod='prediggo'}" class="button" />
			</p>

			<div class="hint" style="display:block;">
				{l s='If you want to execute the export by a cron, use the following link:' mod='prediggo'} <br/>{$sCronFilePath}
			</div>
		</fieldset>

		{$sAttributeManager}

		{$sBackListManager}

		<fieldset style="margin-top: 10px;">
			<legend>{l s='Export File protection' mod='prediggo'}</legend>
			<div class="hint" style="display:block;margin-bottom:20px;">
				{l s='Your files are created into your Prestashop plateform into the following folder :' mod='prediggo'}<a href="{$sExportRepositoryPath}">{$sExportRepositoryPath}</a>.<br/>
				{l s='The data contained into these files are critical and require a protection.' mod='prediggo'}<br/>
				{l s='Please restrict the access to these files by applying a protection with an authentication requiring a login and a password!' mod='prediggo'}
			</div>
			<label>{l s='Login' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="text" size="20" name="prediggo_htpasswd_user" value="{$oPrediggoExportConfig->htpasswd_user}" />
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<label>{l s='Password' mod='prediggo'}</label>
			<div class="margin-form">
				<input type="text" size="20" name="prediggo_htpasswd_pwd" value="{$oPrediggoExportConfig->htpasswd_pwd}" />
				<p>{l s='Description' mod='prediggo'}</p>
			</div>

			<div class="center">
				<input type="submit" name="exportProtectionConfSubmit" value="{l s='Set the Protection' mod='prediggo'}" class="button" />
			</div>
		</fieldset>
	</form>
</div>
