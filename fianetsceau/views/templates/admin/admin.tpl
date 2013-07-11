{*
* 2007-2013 PrestaShop
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
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{$head_message}
<fieldset>
	<legend><img src="{$image_path}" />FIA-NET - Sceau de Confiance</legend>

	<b>Le Sceau de Confiance FIA-NET, leader de la confiance sur le web, influence la d&eacute;cision de r&eacute;achat de 83 % des internautes.</b>

	<br /><br/>
	Le Sceau de Confiance FIA-NET, le plus connu en France, fait la preuve de vos performances. Il restitue les avis de vos clients gr&acirc;ce &agrave; l'envoi <b>de deux questionnaires de satisfaction</b> apr&egrave;s l'achat et apr&egrave;s la livraison.
	<br /><br />
	<b>L'extranet, un outil d'analyse de performance unique</b>, exploite les r&eacute;ponses de vos clients &agrave; ces questionnaires. Une aide inestimable qui vous permet de mieux connaitre vos clients et de piloter votre politique marketing et communication.
	<br /><br />

	<p>{l s='To sign in, check out:' mod='fianetsceau'}<u><a href="https://www.fia-net.com/marchands/devispartenaire.php?p=185" target="_blank">{l s='Fia-net Website' mod='fianetsceau'}</a></u></p>
</fieldset>
<br />

<form action="" method="post">
	<fieldset>
		<legend><img src="{$logo_account_path}" />{l s='Account settings' mod='fianetsceau'}</legend>
		<label>{l s='Login' mod='fianetsceau'}</label>
		<div class="margin-form">
			<input type="text" name="fianetsceau_login" value="{$fianetsceau_login}"/>
		</div>
		<label>{l s='Password' mod='fianetsceau'}</label>
		<div class="margin-form">
			<input type="text" name="fianetsceau_password" value="{$fianetsceau_password}"/>
		</div>
		<label>{l s='Site ID' mod='fianetsceau'}</label>
		<div class="margin-form">
			<input type="text" name="fianetsceau_siteid" value="{$fianetsceau_siteid}"/>
		</div>
		<label>{l s='Authkey' mod='fianetsceau'}</label>
		<div class="margin-form">
			<input type="text" name="fianetsceau_authkey" value="{$fianetsceau_authkey}"/>
		</div>
		<label>{l s='Production mode' mod='fianetsceau'}</label>
		<div class="margin-form">
			<select name="fianetsceau_status">
				{foreach from=$fianetsceau_statuses item=fianetsceau_status_name name=fianetsceau_status}
					<option value="{$fianetsceau_status_name|escape:'htmlall'}" {if $fianetsceau_status_name eq $fianetsceau_status}Selected{/if}>{l s=$fianetsceau_status_name|escape:'htmlall' mod='fianetsceau'}</option>
				{/foreach}
			</select>
		</div>
		<label>{l s='FIA-NET status on order detail' mod='fianetsceau'}</label>
		<div class="margin-form">
			<input name="fianetsceau_showstatus" type="checkbox" value="1" {if $fianetsceaushow_status eq '1'}Checked{/if} /> 
		</div>
	</fieldset>

	<br />

	<fieldset>
		<legend><img src="{$logo_payments_path}" />{l s='Payment modules settings' mod='fianetsceau'}</legend>
		<div class="margin-form">
			<table cellspacing="0" cellpadding="0" class="table">
				<thead>
					<tr>
						<th>{l s='Payment module' mod='fianetsceau'}</th>
						<th>{l s='Payment Type' mod='fianetsceau'}</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$payment_modules key=id_payment_module item=payment_module name=payment_modules}
						<tr>
							<td>{$payment_module.name|escape:'htmlall'}</td>
							<td>
								<select name="fianetsceau_{$id_payment_module}_payment_type">
									{foreach from=$fianetsceau_payment_types key=id_fianetsceau_payment_type item=fianetsceau_payment_type name=fianetsceau_payment_types}
										<option value="{$id_fianetsceau_payment_type|escape:'htmlall'}" {if $payment_module.fianetsceau_type eq $id_fianetsceau_payment_type}Selected{/if}>{$fianetsceau_payment_type|escape:'htmlall'}</option>
									{/foreach}
								</select>
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	</fieldset>

	<br />

	<fieldset>
		<legend><img src="{$logo_account_path}" />{l s='Logo settings' mod='fianetsceau'}</legend>
		<label>{l s='Logo position' mod='fianetsceau'}</label>
		<div class="margin-form">
			<select name="fianetsceau_logo_position">
				{foreach from=$fianetsceau_logo_positions key=fianetsceau_logo_position_key item=fianetsceau_logo_position_name name=fianetsceau_logo_positions}
					<option value="{$fianetsceau_logo_position_key|escape:'htmlall'}" {if $fianetsceau_logo_position_key eq $fianetsceau_logo_position}Selected{/if}>{l s=$fianetsceau_logo_position_name|escape:'htmlall' mod='fianetsceau'}</option>
				{/foreach}
			</select><br /><br />

			<table cellspacing="0" cellpadding="0" class="table">
				<tr>
					<th colspan="2">{l s='Logo size' mod='fianetsceau'}</th>
				</tr>
				{foreach from=$fianetsceau_logo_sizes key=fianetsceau_logo_size item=fianetsceau_logo_img}
					<tr>
						<td><input type="radio" name=fianetsceau_logo_sizes value="{$fianetsceau_logo_size|escape:'htmlall'}" {if $fianetsceau_logo_size eq $fianetsceau_logo}Checked{/if}></td><td><img src="{$fianetsceau_logo_img}" /></td>
						{/foreach}
				</tr>
			</table>
		</div>
	</fieldset>

	<br/>

	<fieldset>
		<legend><img src="{$logo_account_path}" />{l s='Widget settings' mod='fianetsceau'}</legend>
		<label>{l s='Widget position' mod='fianetsceau'}</label>
		<div class="margin-form">
			<select name="fianetsceau_widget_position">
				{foreach from=$fianetsceau_widget_positions key=fianetsceau_widget_position_key item=fianetsceau_widget_position_name name=fianetsceau_widget_positions}
				{if $i % 2 eq 1}{/if}
				{$i % 2}
				<option value="{$fianetsceau_widget_position_key|escape:'htmlall'}" {if $fianetsceau_widget_position_key eq $fianetsceau_widget_position}Selected{/if}>{l s=$fianetsceau_widget_position_name|escape:'htmlall' mod='fianetsceau'}</option>
				{$i++}
			{/foreach}
			</table>
		</select><br /><br />

		<table cellspacing="0" cellpadding="0" class="table">
			<tr>
				<th colspan="4">{l s='Widget type' mod='fianetsceau'}</th>
			</tr>
			<tr>
				<td colspan="2">{l s='White background' mod='fianetsceau'}</td>
				<td colspan="2">{l s='Transparent background' mod='fianetsceau'}</td>
			</tr>
			{$i = 1}
			{foreach from=$fianetsceau_widget_numbers item=fianetsceau_widget_number}
			{if $i mod 2 eq 1}<tr>{/if}
				<td><input type="radio" name=fianetsceau_widget_number value="{$fianetsceau_widget_number|escape:'htmlall'}" {if $fianetsceau_widget_number eq $widget_number}Checked{/if} /></td><td><p><img src="{$path_prefix}/{$fianetsceau_widget_number}.png" /></p></td>
			{if $i mod 2 eq 0}</tr>{/if}
			{$i = $i + 1}
		{/foreach}
</table>
</div>
</fieldset>
<br/>
<center><input type="submit" name="submitSettings" value="{l s='Save' mod='fianetsceau'}" class="button" /></center>
</form>
<br/>
<center><input type="button" name="submitLog" onclick="ShowHideSceauLog();" value="{l s='Show/Hide FIA-NET Sceau log file' mod='fianetsceau'}" class="button" /></center>
<br/>
<center>
	<fieldset id="sceau_log" style="display:none;">
		<textarea cols="150" rows="10" readonly="readonly">{$log_content}</textarea>
		<br/>
	</fieldset>
</center>

