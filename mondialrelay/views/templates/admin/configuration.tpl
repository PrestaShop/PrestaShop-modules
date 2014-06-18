{*
* 2007-2012 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 6844 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<script type="text/javascript">
	var PS_MR_SELECTED_TAB = '{$MR_tab_selected|escape:'htmlall':'UTF-8'}';
</script>
<h2>{l s='Mondial Relay Configuration' mod='mondialrelay'}</h2>

{include file="$MR_local_path/views/templates/admin/post_action.tpl"}

{*
** Menu bar
*}
<div id="MR_config_menu">
	<ul>
		<li {if $MR_tab_selected == 'account_form'} class="selected" {/if}>
			<a id="MR_account_form" href="javascript:void(0)">
				<img src="{$MR_base_dir|escape:'htmlall':'UTF-8'}img/icones/account_detail.png" />
				<p>{l s='Account details' mod='mondialrelay'}</p>
			</a>
		</li>
		<li {if $MR_tab_selected == 'supplier_form'} class="selected" {/if}>
			<a id="MR_supplier_form" href="javascript:void(0)">
				<img src="{$MR_base_dir|escape:'htmlall':'UTF-8'}img/icones/supplier.png" />
				<p>{l s='Shipping' mod='mondialrelay'}</p>
			</a>
		</li>
		<li {if $MR_tab_selected == 'settings_form'} class="selected" {/if}>
			<a id="MR_settings_form" href="javascript:void(0)">
				<img src="{$MR_base_dir|escape:'htmlall':'UTF-8'}img/icones/settings.png" />
				<p>{l s='Advanced settings' mod='mondialrelay'}</p>
			</a>
		</li>
		<li {if $MR_tab_selected == 'info_form'} class="selected" {/if}>
			<a id="MR_info_form" href="javascript:void(0)">
				<img src="{$MR_base_dir|escape:'htmlall':'UTF-8'}img/icones/info.png" />
				<p>{l s='Infos' mod='mondialrelay'}</p>
			</a>
		</li>
		<li {if $MR_tab_selected == 'contact_form'} class="selected" {/if}>
			<a id="MR_contact_form" href="javascript:void(0)">
				<img src="{$MR_base_dir|escape:'htmlall':'UTF-8'}img/icones/help.png" />
				<p>{l s='Contact us' mod='mondialrelay'}</p>
			</a>
		</li>
	</ul>
</div>

<div id="MR_error_account" class="PS_MRFormType MR_error">
{l s='Please set your Mondial Relay account settings' mod='mondialrelay'}
</div>

{if $MR_upgrade_detail|count}
<div class="PS_MRFormType MR_error">
	<ul>
		{foreach from=$MR_upgrade_detail item=message}
			<li>{$message|escape:'htmlall':'UTF-8'}</li>
		{/foreach}
	</ul>
</div>
{/if}

{*
** Contact
*}
<div id="MR_contact_form_block" class="PS_MRFormType">
	<fieldset>
		<legend>
			<img src="../modules/mondialrelay/img/logo.gif" />{l s='Contact us' mod='mondialrelay'}
		</legend>
		<ul>
			<li style="float:left; width:535px;">
				{l s='Mondial Relay Customer Service Team is available to assist you with freight enquiries 24/7. For general enquiries or to book please contact us by: ' mod='mondialrelay'}
				<br />
				<br />- {l s='Mail:' mod='mondialrelay'} <a href="mailto:servicecommercial@mondialrelay.com" style="color:#CA0046;">servicecommercial@mondialrelay.com</a>
				<br />- {l s='Tel:' mod='mondialrelay'} {l s='0892 707 617 (0,34€ TTC/min)' mod='mondialrelay'}
				<br />
				<br />
				<br />
				<b>{l s='For further information please see the FAQ section of our website:' mod='mondialrelay'}</b>
				<br /><a href="http://www.mondialrelay.fr/public/mr_faq.aspx" target="_blank" style="color:#CA0046;">http://www.mondialrelay.fr</a>
			</li>
			<li style="float:left; width:320px;">
				<div style="text-align:center;">
					<img src="http://www.mondialrelay.fr/img/FR/BLOCCPourtoi_FR.gif"/>
				</div>
			</li>
		</ul>
		<br clear="all" />
	</fieldset>
</div>
{*
** General information
*}
<div id="MR_info_form_block" class="PS_MRFormType">
	<div class="MR_warn">
		<a style="color:#383838;text-decoration:underline" href="index.php?tab=AdminPerformance&token={$MR_token_admin_performance|escape:'htmlall':'UTF-8'}">
		{l s='Try to turn off the cache and put the force compilation to on' mod='mondialrelay'}
		</a>
	{l s='if you have any problems with the module after an update' mod='mondialrelay'}
	</div>

	<div class="MR_hint">
	{l s='Have a look to the following HOW-TO to help you to configure the Mondial Relay module' mod='mondialrelay'}
		<b>
			<a href="{$MR_base_dir|escape:'htmlall':'UTF-8'}/docs/install.pdf">
				<img width="20" src="{$MR_base_dir|escape:'htmlall':'UTF-8'}img/pdf_icon.jpg" />
			</a>
		</b>
	</div>

	<br />

	<fieldset>
		<legend>
			<img src="../modules/mondialrelay/img/logo.gif" />{l s='To create a Mondial Relay carrier' mod='mondialrelay'}
		</legend>
		- {l s='Enter and save your Mondial Relay account settings' mod='mondialrelay'} <br />
		- {l s='Create a Carrier using the Shipping button' mod='mondialrelay'} <br />
		- {l s='Define a price for your carrier on' mod='mondialrelay'}
		<a href="index.php?tab=AdminCarriers&token={$MR_token_admin_carriers|escape:'htmlall':'UTF-8'}" class="green">{l s='The Carrier page' mod='mondialrelay'}</a> <br />
		- {l s='To generate labels, you must have a valid and registered address of your store on your' mod='mondialrelay'}
		<a href="index.php?tab={$MR_token_admin_contact.controller_name|escape:'htmlall':'UTF-8'}&token={$MR_token_admin_contact.token|escape:'htmlall':'UTF-8'}" class="green">{l s='contact page' mod='mondialrelay'}</a> <br />
	</fieldset>
</div>

{*
** Account settings form
*}
<div id="MR_account_form_block" class="PS_MRFormType">
	<form action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post" >
		<fieldset>
			<legend><img src="../modules/mondialrelay/img/logo.gif" />{l s='Mondial Relay Account Settings' mod='mondialrelay'}</legend>
			<div>
			{l s='These parameters are provided by Mondial Relay once you subscribed to their service' mod='mondialrelay'}
			</div>
			<ul>
				<li>
					<label for="MR_enseigne_webservice" class="mrLabel">{l s='Webservice Enseigne:' mod='mondialrelay'}</label>
					<input id="MR_enseigne_webservice" class="mrInput" type="text" name="MR_enseigne_webservice" value="{$MR_enseigne_webservice|escape:all}" />
					<sup>*</sup>
				</li>
				<li>
					<label for="MR_code_marque" class="mrLabel">
					{l s='Code marque:' mod='mondialrelay'}
					</label>
					<input id="MR_code_marque" class="mrInput" type="text" name="MR_code_marque" value="{$MR_code_marque|escape:all}" />
					<sup>*</sup>
				</li>
				<li>
					<label for="MR_webservice_key" class="mrLabel">{l s='Webservice Key:' mod='mondialrelay'}</label>
					<input id="MR_webservice_key" class="mrInput" type="text" name="MR_webservice_key" value="{$MR_webservice_key}" />
					<sup>*</sup>
				</li>
				<li>
					<label for="MR_language" class="mrLabel">
					{l s='Etiquette\'s Language:' mod='mondialrelay'}
					</label>
					<select id="MR_language" name="MR_language">
					{foreach from=$MR_available_languages key=num_language item=language}
						{assign var='selected_option' value=''}
						{if $language.iso_code|upper == $MR_selected_language}
							{assign var='selected_option' value='selected="selected"'}
						{/if}
						<option value="{$language.iso_code|upper}" {$selected_option}>{$language.name|escape:'htmlall':'UTF-8'}</option>
					{/foreach}
					</select>
					<sup>*</sup>
				</li>
				<li>
					<label for="MR_weight_coefficient" class="mrLabel">{l s='Weight Coefficient:' mod='mondialrelay'}</label>
					<input class="mrInput" type="text" name="MR_weight_coefficient" id="MR_weight_coefficient" style="width:45px; " value="{$MR_weight_coefficient}"/>
					<sup>*</sup>
					<span class="indication">{l s='grammes = 1 ' mod='mondialrelay'}{$MR_unit_weight_used}</span>
				</li>
				<li class="PS_MRSubmit">
					<input type="button" name="check_connexion" value="{l s='Check connexion' mod='mondialrelay'}" class="button" style="margin:0 60px 0 0;" onclick="return mr_checkConnexion();"/> 
					<input type="submit" name="submit_account_detail" value="{l s='Update Settings' mod='mondialrelay'}" class="button" />
				</li>
			</ul>
			<div class="small"><sup>*</sup>{l s='Required fields' mod='mondialrelay'}</div>
		</fieldset>
		<input type="hidden" name="MR_tab_name" value="account_form" />
	</form>
</div>

{if $MR_account_set}

{*
 ** Advanced settings
 *}
<div  id="MR_settings_form_block" class="PS_MRFormType">
	<form action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post" >
		<fieldset class="PS_MRFormStyle">
			<legend>
				<img src="../modules/mondialrelay/img/logo.gif" />{l s='Advanced Settings' mod='mondialrelay'}
			</legend>
			
			<ul>
				<li>
					<label for="MR_name" class="shipLabel">{l s='Mode:' mod='mondialrelay'}</label>
					<input type="radio" name="mode" value="widget" {if $MR_MONDIAL_RELAY_MODE == 'widget'}checked="checked"{/if} /> {l s='Widget' mod='mondialrelay'}
					<input type="radio" name="mode" value="normal" {if $MR_MONDIAL_RELAY_MODE == 'normal'}checked="checked"{/if} /> {l s='Normal' mod='mondialrelay'}
				</li>
			
				<li>
					{l s='URL Cron Task:' mod='mondialrelay'} 
					<br/>
					{$MR_CRON_URL|escape:'htmlall':'UTF-8'}
				</li>
				
				<li class="PS_MRSubmit">
					<input type="submit" name="submitAdvancedSettings" value="{l s='Update Advanced Settings' mod='mondialrelay'}" class="button" />
				</li>
			</ul>
		</fieldset>
		
		<input type="hidden" name="MR_tab_name" value="settings_form" />
	</form>
</div>

{*
 ** Add new shipping form
 *}
<div id="MR_supplier_form_block" class="PS_MRFormType">
	<form action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post" >
		<fieldset>
			<legend><img src="../modules/mondialrelay/img/logo.gif" alt="" />{l s='Add a Shipping Method' mod='mondialrelay'}</legend>
			<ul>
				<li>
					<label for="MR_name" class="shipLabel">{l s='Carrier\'s name' mod='mondialrelay'}</label>
					<input type="text" id="MR_name" name="MR_name" value="{$MR_name|escape:'htmlall':'UTF-8'}" style="width:190px;" />
					<sup>*</sup>
				</li>

				<li>
					<label for="MR_delay" class="shipLabel">{l s='Delay' mod='mondialrelay'}</label>
					<input type="text" id="MR_delay" name="MR_delay" value="{$MR_delay|escape:'htmlall':'UTF-8'}" style="width:190px;" />
					<sup>*</sup>
				</li>

				<li>
					<label for="MR_dlv_mode" class="shipLabel">{l s='Delivery mode' mod='mondialrelay'}</label>
					<select name="MR_dlv_mode" id="MR_dlv_mode" style="width:200px">
						<option value="24R">24R : {l s='Delivery to a relay point' mod='mondialrelay'}</option>
						<option value="DRI">DRI : {l s='Colis Drive delivery' mod='mondialrelay'}</option>
						<option value="LD1">LD1 : {l s='Home delivery RDC (1 person)' mod='mondialrelay'}</option>
						<option value="LDS">LDS : {l s='Special Home delivery (2 persons)' mod='mondialrelay'}</option>
						<option value="HOM">HOM : {l s='Special Home delivery' mod='mondialrelay'}</option>
					</select>
					<sup>*</sup>
				</li>

				<li>
					<label for="MR_insurance" class="shipLabel">{l s='Insurance' mod='mondialrelay'}</label>
					<select name="MR_insurance" id="MR_insurance" style="width:200px">
						<option value="0">0 : {l s='No insurance' mod='mondialrelay'}</option>
						<option value="1">1 : {l s='Complementary Insurance Lv1' mod='mondialrelay'}</option>
						<option value="2">2 : {l s='Complementary Insurance Lv2' mod='mondialrelay'}</option>
						<option value="3">3 : {l s='Complementary Insurance Lv3' mod='mondialrelay'}</option>
						<option value="4">4 : {l s='Complementary Insurance Lv4' mod='mondialrelay'}</option>
						<option value="5">5 : {l s='Complementary Insurance Lv5' mod='mondialrelay'}</option>
					</select>
					<sup>*</sup>
				</li>

				<li>
					<label for="MR_country_list" class="shipLabel">{l s='Delivery countries:' mod='mondialrelay'}<br /><br />
						<span style="font-size:10px; width:200px;float:left; color:forestgreen">
							{l s='You can choose several countries by pressing Ctrl while selecting countries' mod='mondialrelay'}
						</span>
					</label>
					<select name="MR_country_list[]" id="MR_country_list" multiple size="5" style="width:200px;">
						<option value="FR">{l s='France' mod='mondialrelay'}</option>
						<option value="BE">{l s='Belgium' mod='mondialrelay'}</option>
						<option value="LU">{l s='Luxembourg' mod='mondialrelay'}</option>
						<option value="ES">{l s='Spain' mod='mondialrelay'}</option>
					</select>
					<sup>*</sup>
				</li>

				<li class="PS_MRSubmit">
					<input type="submit" name="submit_add_shipping" value="{l s='Add a Shipping Method' mod='mondialrelay'}" class="button" />
				</li>
			</ul>
			<div class="small"><sup>*</sup>{l s='Required fields' mod='mondialrelay'}</div>
		</fieldset>
		<input type="hidden" name="MR_tab_name" value="supplier_form" />
	</form>

	<br />

{*
 ** Shipping List
 *}
		<fieldset class="shippingList">
			<legend><img src="../modules/mondialrelay/img/logo.gif" />{l s='Shipping Method\'s list' mod='mondialrelay'}</legend>
			
				{if $MR_carriers_list|count == 0}
					<ul><li>{l s='No shipping methods created' mod='mondialrelay'}</li></ul>
				{else}
				
<table class="table tableDnD carrier">
<tr>
	<th style="text-align:center">{l s='ID_MR' mod='mondialrelay'}</th>
	<th style="text-align:center">{l s='ID carrier' mod='mondialrelay'}</th>
	<th>{l s='Carrier' mod='mondialrelay'}</th>
	<th style="text-align:center">{l s='Delivery mode' mod='mondialrelay'}</th>
	<th style="text-align:center">{l s='Insurance' mod='mondialrelay'}</th>
	<th >{l s='Delivery countries' mod='mondialrelay'}</th>
	<th style="text-align:center">{l s='Delete' mod='mondialrelay'}</th>
	<th style="text-align:center">{l s='Edit' mod='mondialrelay'}</th>
</tr>
	
		
					{foreach from=$MR_carriers_list key=num_carrier item=carrier}
						<tr>
							<td width="5%" align="center">
								{$carrier.id_mr_method}
							</td>
							<td width="7%" align="center">
								{$carrier.id_carrier}
							</td>
							<td width="40%">
								{$carrier.name|escape:'htmlall':'UTF-8'} ({$carrier.col_mode|escape:'htmlall':'UTF-8'})								
							</td>
							<td width="15%" align="center">
								{$carrier.dlv_mode|escape:'htmlall':'UTF-8'}
							</td>
							<td width="15%" align="center">
								{$carrier.insurance|escape:'htmlall':'UTF-8'}
							</td>
							<td width="15%">
								{$carrier.country_list|escape:'htmlall':'UTF-8'}
							</td>
							<td align="center">						
								<form action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}&MR_tab_name=supplier_form" method="post">
									<input type="hidden" name="delete_mr" value="{$carrier.id_mr_method}" >
									<a class="send_disable_carrier_form" href="javascript:void(0)">
										<img src="../img/admin/disabled.gif" alt="{l s='Delete' mod='mondialrelay'}" title="{l s='Delete' mod='mondialrelay'}" />
									</a>
								</form>
							</td>
							<td align="center">
								<a href="index.php?tab=AdminCarriers&id_carrier={$carrier.id_carrier}&updatecarrier&token={$MR_token_admin_carriers|escape:'htmlall':'UTF-8'}">
									<img src="../img/admin/edit.gif" alt="{l s='Edit' mod='mondialrelay'}" title="{l s='Edit' mod='mondialrelay'}" />
								</a>
								
							</td>
						</tr>
					{/foreach}
				{/if}
</table>
		</fieldset>
</div>
{/if}
