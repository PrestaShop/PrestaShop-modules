{*
 * 2007-2014 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2014 PrestaShop SA
 *  @version  Release: 0.4.4
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *}
<div id="downloadmanual">
	<a id="manual_download" href="{$module_path|escape:'htmlall':'UTF-8'}manual/seur_manual.pdf" target="_blank" >
		<img src="{$img_path|escape:'htmlall':'UTF-8'}ico_descargar.png" alt="{l s='Manual' mod='seur'}" /> {l s='Manual' mod='seur'}
	</a>
</div>

<div id="module_configuration">
	<ul class="configuration_menu">
		<li class="button btnTab active" tab="configuration" id="tab_configuration">
			<img src="{$img_path|escape:'htmlall':'UTF-8'}config.png" alt="{l s='Configuration' mod='seur'}" title="{l s='Configuration' mod='seur'}" /> {l s='Configuration' mod='seur'}
		</li>
		<li class="button btnTab" tab="merchant" id="tab_merchant">
			<img src="{$img_path|escape:'htmlall':'UTF-8'}merchant.png" alt="{l s='Merchant' mod='seur'}" title="{l s='Merchant' mod='seur'}" /> {l s='Merchant' mod='seur'}
		</li>
	</ul>

	<!--configuration-->
	<ul class="configuration_tabs">
		<li id="configuration" class="default">
			<form action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post" name="configuration_form">

				{assign var='exists_s_c_p' value=false}
				{assign var='exists_s_c_n' value=false}
				{assign var='exists_s_c_c_n' value=false}
				{assign var='exists_s_c_c_48' value=false}

				{foreach from=$seur_active_carriers item='carrier_seur'}
					{if $carrier_seur.type eq 'SEN'}
						{assign var='seur_carrier_normal' value=$carrier_seur}
					{elseif $carrier_seur.type eq 'SEP'}
						{assign var='seur_carrier_pos' value=$carrier_seur}
					{elseif $carrier_seur.type eq 'SCN'}
						{assign var='seur_carrier_canarias_n' value=$carrier_seur}
					{elseif $carrier_seur.type eq 'SCE'}
						{assign var='seur_carrier_canarias_48' value=$carrier_seur}
					{/if}
				{/foreach}
				{foreach from=$carriers item='carrier'}
					{if isset($seur_carrier_normal.id) && ($seur_carrier_normal.id eq $carrier.id_carrier)}
						{assign var='exists_s_c_n' value=true}
					{elseif isset($seur_carrier_pos.id) && ($seur_carrier_pos.id eq $carrier.id_carrier)}
						{assign var='exists_s_c_p' value=true}
					{elseif isset($seur_carrier_canarias_n.id) && ($seur_carrier_canarias_n.id eq $carrier.id_carrier)}
						{assign var='exists_s_c_c_n' value=true}
					{elseif isset($seur_carrier_canarias_48.id) && ($seur_carrier_canarias_48.id eq $carrier.id_carrier)}
						{assign var='exists_s_c_c_48' value=true}
					{/if}
				{/foreach}

				{if $price_configured eq false}
					<p class="alertaconfiguracion">{l s='The ranges are configured but they have no prices, fill them in the carrier\'s tab with the amounts that correspond. ' mod='seur'}</p>
				{/if}

				{if $exists_s_c_p eq false}
					{l s='The carrier Point of Sale has changed, select the correct one.' mod='seur'}
				{/if}
				<ul class="cols2">
					<li id="t_n">
						<label>{l s='SEUR Carrier' mod='seur'}</label>
						<select id="id_seur_carrier" name="id_seur_carrier"/>
							<option value="{$seur_carrier_normal.id|escape:'htmlall':'UTF-8'}">{l s='...' mod='seur'}</option>
							{foreach from=$carriers item='carrier'}
								{if $seur_carrier_normal.id eq $carrier.id_carrier}
									<option value="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}" id ="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}" selected="selected">{$carrier.name|escape:'htmlall':'UTF-8'}</option>
									{assign var='exists_s_c_n' value=true}
								{else}
									<option value="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}" id ="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}">{$carrier.name|escape:'htmlall':'UTF-8'}</option>
								{/if}
							{/foreach}
						</select>
						<span class="lihelp">{l s='Select Prestashop carrier to operate as SEUR.' mod='seur'}</span>
					</li>

					<li id="c_m">
						<label>{l s='SEUR Canary Islands M' mod='seur'}</label>
						<select id="id_seur_carrier_canarias_m" name="id_seur_carrier_canarias_m"/>
							<option value="{$seur_carrier_canarias_n.id|escape:'htmlall':'UTF-8'}">{l s='...' mod='seur'}</option>
							{foreach from=$carriers item='carrier'}
								{if $seur_carrier_canarias_n.id eq $carrier.id_carrier}
									<option value="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}" id ="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}" selected="selected">{$carrier.name|escape:'htmlall':'UTF-8'}</option>
									{assign var='exists_s_c_c_n' value=true}
								{else}
									<option value="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}" id ="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}">{$carrier.name|escape:'htmlall':'UTF-8'}</option>
								{/if}
							{/foreach}
						</select>
						<span class="lihelp">{l s='Select Prestashop carrier for shipment to Canarias.' mod='seur'}</span>
					</li>

				<li id="c_ur">
					<label>{l s='SEUR Canary Islands Express' mod='seur'}</label>
					<select id="id_seur_carrier_canarias_48" name="id_seur_carrier_canarias_48">
						<option value="{$seur_carrier_canarias_48.id|escape:'htmlall':'UTF-8'}>{l s='...' mod='seur'}</option>
						{foreach from=$carriers item='carrier'}
							{if $seur_carrier_canarias_48.id eq $carrier.id_carrier}
								<option value="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}" id ="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}" selected="selected">{$carrier.name|escape:'htmlall':'UTF-8'}</option>
								{assign var='exists_s_c_c_48' value=true}
							{else}
								<option value="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}" id ="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}">{$carrier.name|escape:'htmlall':'UTF-8'}</option>
							{/if}
						{/foreach}
					</select>
					<span class="lihelp">{l s='Select Prestashop carrier for express shipment to Canarias.' mod='seur'}</span>
				</li>

				<li class="clear"></li>
			</ul>

			<ul class="cols2">
				<li>
					<label>{l s='Enable Point of Sale (pos)' mod='seur'}</label>
					{if $configuration_table.pos eq 0}
						<input id ="pos_yes" type="radio" name="pos" value="1" />{l s='Yes' mod='seur'}
						<input checked="checked" id= "pos_no" type="radio" name="pos" value="0" />{l s='No' mod='seur'}
					{elseif $configuration_table.pos eq 1}
						<input checked="checked" id ="pos_yes" type="radio" name="pos" value="1" />{l s='Yes' mod='seur'}
						<input id= "pos_no" type="radio" name="pos" value="0" />{l s='No' mod='seur'}
					{/if}
					<span class="lihelp">{l s='Enable the payment process a map with different SEUR collection points.' mod='seur'}</span>
				</li>

				{if $configuration_table.pos eq 0}
					<li id="pos_carrier" class="invisible">
				{else}
					<li id="pos_carrier" class="invisible" style="opacity: 1;">
				{/if}
					<label>{l s='SEUR Carrier Point of Sale' mod='seur'}</label>
					<select id="id_seur_carrier_pos" name="id_seur_carrier_pos">
						<option value="{$seur_carrier_pos.id|escape:'htmlall':'UTF-8'}">{l s='...' mod='seur'}</option>
						{foreach from=$carriers item='carrier'}
							{if $seur_carrier_pos.id eq $carrier.id_carrier}
								<option value="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}" id ="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}" selected="selected">{$carrier.name|escape:'htmlall':'UTF-8'}</option>
								{assign var='exists_s_c_p' value=true}
							{else}
								<option value="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}" id ="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}">{$carrier.name|escape:'htmlall':'UTF-8'}</option>
							{/if}
						{/foreach}
					</select>
					<span class="lihelp">{l s='Select Prestashop carrier to operate as PUDO SEUR.' mod='seur'}</span>
				</li>

				<li class="clear"></li>
				<li class="international_orders">
					<label>{l s='Enable Internationals Orders' mod='seur'}</label>
					{if $configuration_table.international_orders eq 0}
						<input id ="inter_si" type="radio" name="international_orders" value="1"/>{l s='Yes' mod='seur'}
						<input checked="true" id= "inter_no" type="radio" name="international_orders" value="0"/>{l s='No' mod='seur'}
					{elseif $configuration_table.international_orders eq 1}
						<input checked="true" id ="inter_si" type="radio" name="international_orders" value="1"/>{l s='Yes' mod='seur'}
						<input id= "inter_no" type="radio" name="international_orders" value="0"/>{l s='No' mod='seur'}
					{/if}
					<span class="lihelp">{l s='Enables shipments outside Spain and Portugal.' mod='seur'}</span>
				</li>

				{if $configuration_table.international_orders eq 0}
					<li id="message_international_orders" class="invisible warn alertaconfiguracion">
				{else}
					<li id="message_international_orders" class="warn alertaconfiguracion">
				{/if}
					{l s='International shipments are to Europe excluding Spain, Portugal and Andorra' mod='seur'}
				</li>
				<li class="clear"></li>
				<li>
					<label>{l s='Enable Cash on delivery' mod='seur'}</label>
					{if $configuration_table.seur_cod eq 0}
						<input id ="contra_si" type="radio" name="seur_cod" value="1"/>{l s='Yes' mod='seur'}
						<input id= "contra_no" type="radio" name="seur_cod" value="0" checked="checked" />{l s='No' mod='seur'}
					{elseif $configuration_table.seur_cod eq 1}
						<input id ="contra_si" type="radio" name="seur_cod" value="1" checked="checked" />{l s='Yes' mod='seur'}
						<input id= "contra_no" type="radio" name="seur_cod" value="0"/>{l s='No' mod='seur'}
					{/if}
					<span class="lihelp">{l s='Enables payment on delivery, requires write permissions to the modules folder.' mod='seur'}</span>
				</li>


				{if $configuration_table.seur_cod eq 0}
					<li id="seur_cod_configuration" class="invisible">
				{else}
					<li id="seur_cod_configuration">
				{/if}
					<label>{l s='Percentage' mod='seur'}</label>
					<input type="text" name="contra_porcentaje" value="{$seur_remcar_cargo|floatval}" size="3" /> &#37;

					<br /><br />

					<label>{l s='Minium fee' mod='seur'}</label>
					<input type="text" name="contra_minimo" value="{$seur_remcar_cargo_min|floatval}" size="3" /> {$currency->sign|escape:'htmlall':'UTF-8'}
					<span class="lihelp">{l s='Enter a percentage to charge customers as a fee and / or a minimum amount should not reach the amount by percentage.' mod='seur'}</span>

				</li>

				<li class="clear"></li>

				<li>
					<label>{l s='Free Shipping' mod='seur'} </label>
					<span class="lihelp">{l s='Enables free shipping by weight or price. This function changes Prestashop drivers folder, the "override" must be writable.' mod='seur'}</span>
				</li>
				<li class="enviopeso">
					<label>{l s='By weigth' mod='seur'}</label>
					<input type="text" name="peso_gratis" value="{$seur_free_weight|floatval}" size="3"/> {$seur_weight_unit|escape:'htmlall':'UTF-8'}
				</li>
				<li class="envioprecio">
					<label>{l s='By price' mod='seur'}</label>
					<input type="text" name="precio_gratis" value="{$seur_free_price|escape:'htmlall':'UTF-8'}" size="3"/> {$currency->sign|escape:'htmlall':'UTF-8'}
				</li>
				<li id="msgenviosgratis" class="warn alertaconfiguracion">
					{l s='Put 0 value to disable.' mod='seur'}
				</li>

				<li class="clear"></li>

				<li id="advice_notification">
					<div class="notifications_div">
						<label>{l s='Advice notification' mod='seur'}</label>
						<input id ="advice_checkbox" type="checkbox" name="advice_checkbox" {if $configuration_table.advice_checkbox eq 1}checked="checked"{/if} />
					</div>
					<div id ="notification_advice_div" {if $configuration_table.advice_checkbox eq 1}style="opacity: 1;"{else}style="opacity: 0;"{/if}>
						{if $configuration_table.notification_advice_radio eq 0}
							<input type="radio" name="notification_advice_radio" id="email_advice" value="0" checked="true" />{l s='Email' mod='seur'}
							<input type="radio" name="notification_advice_radio" id="sms_advice" value="1" />{l s='SMS' mod='seur'}
						{elseif $configuration_table.notification_advice_radio eq 1}
							<input type="radio" name="notification_advice_radio" id="email_advice" value="0"/>{l s='Email' mod='seur'}
							<input type="radio" name="notification_advice_radio" id="sms_advice" value="1" checked="true"/>{l s='SMS' mod='seur'}
						{/if}
					</div>
					<span class="lihelp">{l s='SEUR will notify you when you have made shipping. To use this feature you must hire her previously.' mod='seur'}</span>
				</li>

				{if ($configuration_table.notification_advice_radio eq 0) || ($configuration_table.notification_distribution_radio eq 0)}
					<li id="aditional_cost_sms" class="invisible">
				{else}
					<li id="aditional_cost_sms">
				{/if}
					<p>
						<img src="../img/admin/help.png" />
						{l s='The SMS service has an additional cost, please contact SEUR.' mod='seur'}
					</p>
				</li>

				<li id="distribution_notification">
					<div class="notifications_div">
						<label>{l s='Distribution notification' mod='seur'}</label>
						<input id ="distribution_checkbox" type="checkbox" name="distribution_checkbox" {if $configuration_table.distribution_checkbox eq 1}checked="checked"{/if} />
					</div>
					<div id ="notification_distribution_div" {if $configuration_table.distribution_checkbox eq 1}style="opacity: 1;"{else}style="opacity: 0;"{/if}>
						{if $configuration_table.notification_distribution_radio eq 0}
							<input type="radio" name="notification_distribution_radio" id="email_distribution" value="0" checked="true" />{l s='Email' mod='seur'}
							<input type="radio" name="notification_distribution_radio" id="sms_distribution" value="1" />{l s='SMS' mod='seur'}
						{elseif $configuration_table.notification_distribution_radio eq 1}
							<input type="radio" name="notification_distribution_radio" id="email_distribution" value="0" />{l s='Email' mod='seur'}
							<input type="radio" name="notification_distribution_radio" id="sms_distribution" value="1" checked="true" />{l s='SMS' mod='seur'}
						{/if}
					</div>
					<span class="lihelp">{l s='SEUR will notify you when the package is in delivery. To use this feature you must hire her previously.' mod='seur'}</span>
				</li>

				<li class="clear"></li>

				<li>
					<label>{l s='Printing' mod='seur'}</label>
					{if $configuration_table.print_type eq 0}
						<input id ="print_type_pdf" type="radio" name="print_type" value="1"/>{l s='PDF' mod='seur'}
						<input checked="true" id="print_type_termica" type="radio" name="print_type" value="0"/>{l s='Label' mod='seur'}
					{elseif $configuration_table.print_type eq 1}
						<input checked="true" id ="print_type_pdf" type="radio" name="print_type" value="1"/>{l s='PDF' mod='seur'}
						<input id= "print_type_termica" type="radio" name="print_type" value="0"/>{l s='Label' mod='seur'}
					{/if}
					<span class="lihelp">{l s='Pdf to normal printer. The thermal printer is supposed to give SEUR.' mod='seur'}</span>
				</li>

				{if $configuration_table.print_type eq 1}
					<li id="printer_name" class="invisible">
				{else}
					<li id="printer_name">
				{/if}
					<label>{l s='Printer name:' mod='seur'}</label>
					<input type="text" name="printer_name" value="{$seur_printer_name|escape:'htmlall':'UTF-8'}" />
					<span class="lihelp">{l s='Your operating system designate thermal printer with a name, enter it.' mod='seur'}</span>
				</li>

				<li class="clear"></li>

				<li>
					<label>{l s='Pickup' mod='seur'}</label>
					{if $configuration_table.pickup eq 1}
						<input id ="pickup_auto" type="radio" name="pickup" value="0"/>{l s='Auto' mod='seur'}
						<input checked="true" id="pickup_fija" type="radio" name="pickup" value="1"/>{l s='Fixed' mod='seur'}
					{elseif $configuration_table.pickup eq 0}
						<input checked="true" id ="pickup_auto" type="radio" name="pickup" value="0"/>{l s='Auto' mod='seur'}
						<input id= "pickup_fija" type="radio" name="pickup" value="1"/>{l s='Fixed' mod='seur'}
					{/if}
					<span class="lihelp">{l s='The collection is generated automatically with the first order of the day. The fixed collection contracts with SEUR to pass every day.' mod='seur'}</span>
				</li>

				<li class="clear"></li>

				<li class="submit">
					<input type="submit" name="submitConfiguration" value="{l s='Save' mod='seur'}" class="button" />
				</li>
			</ul>
		</form>
	</li>

	<!--configuration end-->

	<!--merchant-->

	<li id="merchant">
		<form action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post" name="merchant_form">
			<ul class="cols3">
				<li class="required">
					<label>{l s='NIF / CIF' mod='seur'}</label>
					<input type="text" name="nif_dni" value="{$merchant_data.nif_dni|escape:'htmlall':'UTF-8'}"/>
					<sup name="nif_dni">*</sup>
					<span class="lihelp">{l s='Enter the NIF / CIF Company.' mod='seur'}</span>
				</li>
				<li class="required">
					<label>{l s='Name' mod='seur'}</label>
					<input type="text" name="name" value="{$merchant_data.name|escape:'htmlall':'UTF-8'}"/>
					<sup name="name">*</sup>
					 <span class="lihelp">{l s='Name of the contact person.' mod='seur'}</span>
				</li>
				<li class="required">
					<label>{l s='Firstname' mod='seur'}</label>
					<input type="text" name="first_name" value="{$merchant_data.first_name|escape:'htmlall':'UTF-8'}"/>
					<sup name="first_name">*</sup>
					<span class="lihelp">{l s='Firstname of the contact person.' mod='seur'}</span>
				</li>
				<li class="required">
					<label>{l s='Company name' mod='seur'}</label>
					<input type="text" name="company_name" value="{$merchant_data.company_name|escape:'htmlall':'UTF-8'}"/>
					<sup name="company_name">*</sup>
					<span class="lihelp">{l s='Legal name of the company.' mod='seur'}</span>
				</li>
				<li class="separator">
				</li>
				<li class="required">
					<label>{l s='Street type' mod='seur'}</label>
					<select name="street_type">
						<option value="">---</option>

						{foreach from=$street_types key='abbreviation' item='street_type'}
							{if $abbreviation eq $merchant_data.street_type}
								<option value="{$abbreviation|escape:'htmlall':'UTF-8'}" id="{$abbreviation|escape:'htmlall':'UTF-8'}" selected="selected">{$street_type|escape:'htmlall':'UTF-8'}</option>
							{else}
								<option value="{$abbreviation|escape:'htmlall':'UTF-8'}" id="{$abbreviation|escape:'htmlall':'UTF-8'}">{$street_type|escape:'htmlall':'UTF-8'}</option>
							{/if}
						{/foreach}
					</select>
				<sup name="street_type">*</sup>
				</li>
				<li class="required">
					<label>{l s='Street name' mod='seur'}</label>
					<input type="text" name="street_name" value="{$merchant_data.street_name|escape:'htmlall':'UTF-8'}"/>
					<sup name="street_name">*</sup>

				</li>
				<li class="required">
					<label>{l s='Number' mod='seur'}</label>
					<input type="text" name="street_number" value="{$merchant_data.street_number|escape:'htmlall':'UTF-8'}"/>
					<sup name="street_number">*</sup>
				</li>
				<li>
					<label>{l s='Stair' mod='seur'}</label>
					<input type="text" name="staircase" value="{$merchant_data.staircase|escape:'htmlall':'UTF-8'}"/>
				</li>
				<li>
					<label>{l s='Floor' mod='seur'}</label>
					<input type="text" name="floor" value="{$merchant_data.floor|escape:'htmlall':'UTF-8'}"/>
				</li>
				<li>
					<label>{l s='Door' mod='seur'}</label>
					<input type="text" name="door" value="{$merchant_data.door|escape:'htmlall':'UTF-8'}"/>
				</li>
				<li class="required">
					<label>{l s='Post Code' mod='seur'}</label>
					<input type="text" name="post_code_cfg" value="{$merchant_data.post_code|escape:'htmlall':'UTF-8'}"/>
					<input type="hidden" name="token" value="{$token|escape:'htmlall':'UTF-8'}"/>
					<input type="hidden" name="id_employee" value="{$employee->id|escape:'htmlall':'UTF-8'}"/>
					<sup name="post_code_cfg">*</sup>
				</li>
				<li class="required">
						<label>{l s='City' mod='seur'}</label>
						<input type="text" name="town_cfg" value="{$merchant_data.town|escape:'htmlall':'UTF-8'}"/>
						<sup name="town_cfg">*</sup>
				</li>
				<li class="required">
					<label>{l s='State' mod='seur'}</label>
					<input type="text" name="state_cfg" value="{$merchant_data.state|escape:'htmlall':'UTF-8'}" />
					<sup name="state_cfg">*</sup>
				</li>
				<li class="required">
					<label>{l s='Country' mod='seur'}</label>
					<select name="country_cfg">
						<option value=""></option>
						{foreach from=SeurLib::$seur_countries key='abbreviation' item='seur_country'}
							{if $abbreviation eq $merchant_data.country}
								<option value="{$abbreviation|escape:'htmlall':'UTF-8'}" selected="selected">{$seur_country|escape:'htmlall':'UTF-8'}</option>
							{else}
								<option value="{$abbreviation|escape:'htmlall':'UTF-8'}">{$seur_country|escape:'htmlall':'UTF-8'}</option>
							{/if}
						{/foreach}
					</select>
					<sup name="country_cfg">*</sup>
				</li>
				<li class="separator">
				</li>
				<li class="required">
					<label>{l s='Phone' mod='seur'}</label>
					<input type="text" name="phone" value="{$merchant_data.phone|escape:'htmlall':'UTF-8'}"/>
					<sup name="phone">*</sup>
				</li>
				<li>
					<label>{l s='Fax' mod='seur'}</label>
					<input type="text" name="fax" value="{$merchant_data.fax|escape:'htmlall':'UTF-8'}"/>
				</li>
				<li class="required">
					<label>{l s='Email' mod='seur'}</label>
					<input type="text" name="email" value="{$merchant_data.email|escape:'htmlall':'UTF-8'}"/>
					<sup name="email">*</sup>
				</li>
				<li class="required">
					<label>{l s='CIT' mod='seur'}</label>
					<input type="text" name="ci" value="{$merchant_data.cit|escape:'htmlall':'UTF-8'}" />
					<sup name="ci">*</sup>
				</li>
				<li class="required">
					<label>{l s='CCC' mod='seur'}</label>
					<input type="text" maxlength="5" name="ccc_cfg" value="{$merchant_data.ccc|escape:'htmlall':'UTF-8'}"/>
					<sup name="ccc_cfg">*</sup>
					<span class="lihelp">{l s='The CCC will be provided by SEUR. It is a numeric code from 1 to 5 digits.' mod='seur'}</span>
				</li>
				<li class="required">
					<label>{l s='Franchise' mod='seur'}</label>
					<input type="text" name="franchise_cfg" value="{$merchant_data.franchise|escape:'htmlall':'UTF-8'}" />
					<sup name="franchise_cfg">*</sup>
				</li>
				<li class="separator">
				</li>
				<li class="left required">
					<label>{l s='User' mod='seur'}</label>
					<input type="text" name="user_cfg" value="{$merchant_data.user|escape:'htmlall':'UTF-8'}" /><sup name="user_cfg">*</sup>
					<input type="hidden" name="token_cfg" value="{$token|escape:'htmlall':'UTF-8'}"/>
					<input type="hidden" name="id_employee_cfg" value="{$employee->id|escape:'htmlall':'UTF-8'}"/>
				</li>
				
				<li  class="left required">
					<label>{l s='User of www.seur.com' mod='seur'}</label>
					<input type="text" name="user_seurcom" value="{$user_seurcom|escape:'htmlall':'UTF-8'}" /><sup name="user_seurcom">*</sup>
					<input type="hidden" name="token_cfg" value="{$token|escape:'htmlall':'UTF-8'}"/>
				</li>
				<li class="li_clear"></li>
				<li class="left required">
					<label>{l s='Password' mod='seur'}</label>
					<input type="password" name="pass_cfg" value="{$merchant_data.pass|escape:'htmlall':'UTF-8'}" /><sup name="pass_cfg">*</sup>
				</li>
				
				
				<li  class="left required">
					<label>{l s='Password of www.seur.com' mod='seur'}</label>
					<input type="password" name="pass_seurcom" value="{$pass_seurcom|escape:'htmlall':'UTF-8'}" /><sup name="pass_seurcom">*</sup>
				</li>
				
				<li class="separator"></li>

				<li class="submit">
					<input type="submit" name="submitLogin" class="button" value="{l s='Send' mod='seur'}" />
				</li>
				{if empty($merchant_data.user) || empty($merchant_data.pass)}
					<li style="background-color:#FAE2E3 ; border: 1px solid #EC9B9B; padding:3px;">
						<img src="../img/admin/warning.gif" />
						{l s='The data does not correspond with the ones we have in our system. Please contact at 902101010 SEUR or via www.seur.com, Thank you.' mod='seur'}
					</li>
				{/if}
			</ul>
			<div id="seurJsTranslations" class="hidden">
				<input type="hidden" name="requiredField" value="{l s='Fill in the field.' mod='seur'}" />
				<input type="hidden" name="onlyNumbers" value="{l s='Please enter only numbers.' mod='seur'}" />
				<input type="hidden" name="onlyEmail" value="{l s='Malformed e-mail.' mod='seur'}" />
				<input type="hidden" name="onlyText" value="{l s='Enter only letters.' mod='seur'}" />
				<input type="hidden" name="requiredSelect" value="{l s='Select an option.' mod='seur'}" />
				<input type="hidden" name="acceptPrivacyPolicy" value="{l s='Accept the privacy policy.' mod='seur'}" />
			</div>
		</form>
	</li>

	<!--merchant end-->

</ul>
<!--configuration_tabs end-->
</div>