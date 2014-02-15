{*
* 2007-2014 PrestaShop
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
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<img src="{$logo_kwixo}" />
<div class="warn">  
	<p>{l s='Warning, delivery meets the following priority rules:' mod='kwixo'}</p>
	<ul>
		<li>{l s='the maximum time configured for product categories will override any other configuration time (carrier or default)' mod='kwixo'}</li>
		<li>{l s='if timeout configured for product categories, it will be the delivery of the carrier used to control which prevail' mod='kwixo'}</li>
		<li>{l s='if timeout configured for product categories or by courier, delivery set default will be retained' mod='kwixo'}</li>
	</ul>
</div>
{$head_msg}
<fieldset>
	<legend><img src="{$icon_kwixo}" />{l s='FIA-NET - Kwixo' mod='kwixo'}</legend>
		{l s='Kwixo est une solution de paiement développée par le groupe Crédit Agricole et FIA-NET Europe.' mod='kwixo'}
	<br /><br/>
	{l s='Ce service permet à vos clients de régler leurs achats sur votre site très simplement.' mod='kwixo'}
	<br /><br/>
	{l s='Offrez à vos clients les modes de paiements suivants :' mod='kwixo'}
	<ul>
		<li>{l s='Kwixo en 1 fois par CB' mod='kwixo'}</li>
		<li>{l s='Kwixo en 1 fois par CB avec débit après réception' mod='kwixo'}</li>
		<li>{l s='Kwixo en plusieurs fois par CB avec débit après réception' mod='kwixo'}</li>
	</ul>
	<p>
	{l s='To sign in, check out: ' mod='kwixo'} <u><a href="http://www.fia-net-group.com/les-services-du-groupe-fia-net/solution-de-paiement/contact-kwixo/" target="_blank">{l s='Fia-net Website' mod='kwixo'}</a></u>
</p>
</fieldset>
<br/>
<form action="" method="post">
	<fieldset>

		<p>{l s='The following parameters are provided by FIA-NET.' mod='kwixo'}</p>
		<legend><img src="{$logo_account_path}" />{l s='Account settings' mod='kwixo'}</legend>
		<label>{l s='Login' mod='kwixo'}</label>
		<div class="margin-form">
			<input type="text" name="kwixo_login" value="{$kwixo_login}"/>
		</div>
		<label>{l s='Password' mod='kwixo'}</label>
		<div class="margin-form">
			<input type="text" name="kwixo_password" value="{$kwixo_password}"/>
		</div>
		<label>{l s='Site ID' mod='kwixo'}</label>
		<div class="margin-form">
			<input type="text" name="kwixo_siteid" value="{$kwixo_siteid}"/>
		</div>
		<label>{l s='Key' mod='kwixo'}</label>
		<div class="margin-form">
			<input type="text" name="kwixo_authkey" value="{$kwixo_authkey}"/>
		</div>
		<label>{l s='Mode' mod='kwixo'}</label>
		<div class="margin-form">
			<select name="kwixo_status">
				{foreach from=$kwixo_statuses item=kwixo_status_name name=kwixo_status}
					<option value="{$kwixo_status_name}" {if $kwixo_status_name eq $kwixo_status}Selected{/if}>{l s=$kwixo_status_name mod='kwixo'}</option>
				{/foreach}
			</select> {l s='In test mode, you will not receive payment. In production mode, you will receive real payment.' mod='kwixo'}
		</div>
		<label>{l s='Delivery' mod='kwixo'}</label>
		<div class="margin-form">
			<input type="text" name="kwixo_delivery" value="{$kwixo_delivery}"/> {l s='days' mod='kwixo'}
		</div>

		<label>{l s='Delivery max contract' mod='kwixo'}</label>
		<div class="margin-form">
			<select name="kwixo_max_delivery">
				{foreach from=$kwixo_deliveries item=kwixo_number_days name=kwixo_deliveries}
					<option value="{$kwixo_number_days}" {if $kwixo_number_days eq $kwixo_max_delivery}Selected{/if}>{l s=$kwixo_number_days mod='kwixo'}</option>
				{/foreach}
			</select> {l s='days' mod='kwixo'}
		</div>

		<label>{l s='Email test' mod='kwixo'}</label>
		<div class="margin-form">
			<input type="text" size="40" name="kwixo_email_test" value="{$kwixo_email_test}"/> {l s='You can put multiple addresses separated by a "," ' mod='kwixo'}
		</div>

		<label>{l s='Kwixo option' mod='kwixo'}</label>
		<div class="margin-form">
			<input name="kwixo_option_standard" type="checkbox" value="1" {if $kwixo_option_standard eq '1'}Checked{/if} /> {l s='Kwixo standard' mod='kwixo'}<br/> 
			<input name="kwixo_option_comptant" type="checkbox" value="1" {if $kwixo_option_comptant eq '1'}Checked{/if} /> {l s='Kwixo comptant' mod='kwixo'}<br/> 
			<input name="kwixo_option_credit" type="checkbox" value="1" {if $kwixo_option_credit eq '1'}Checked{/if} /> {l s='Kwixo crédit' mod='kwixo'}<br/> 
			<input name="kwixo_option_facturable" disabled="disabled" type="checkbox" value="0" /> {l s='Kwixo facturable' mod='kwixo'}<br/> 
		</div>
	</fieldset>

	<br />

	<fieldset>
		<legend><img src="{$logo_display_path}" />{l s='Display settings' mod='kwixo'}</legend>
		<p>{l s='The banner is a block of information displayed on your shop. It must match one of the offers that you subscribed.' mod='kwixo'}</p>
		<p>{l s='Choose a banner to display' mod='kwixo'} :</p>
		<label>{l s='Banner to enable' mod='kwixo'}</label>
		<div class="margin-form">
			{foreach from=$kwixo_banner_types key=kwixo_banner_type item=kwixo_banner_name name=kwixo_banners}
				<input type="radio" name="kwixo_banner_types" value="{$kwixo_banner_type}" {if $kwixo_banner_type eq $kwixo_banner}Checked{/if} /> {l s=$kwixo_banner_name mod='kwixo'}<br/>
			{/foreach}
		</div>

		<label>{l s='Banner size' mod='kwixo'}</label>
		<div class="margin-form">
			{foreach from=$kwixo_banner_sizes key=kwixo_banner_size item=kwixo_banner_name name=kwixo_banners_size}
				<input type="radio" name="kwixo_banner_sizes" value="{$kwixo_banner_size}" {if $kwixo_banner_size eq $kwixo_banner_size_saved}Checked{/if} /> {l s=$kwixo_banner_size mod='kwixo'}<br/>
			{/foreach}
		</div>

		<label>{l s='Banner position' mod='kwixo'}</label>
		<div class="margin-form">
			<select name="kwixo_banner_positions">
				{foreach from=$kwixo_banner_positions key=kwixo_banner_position_key item=kwixo_banner_position_name name=kwixo_banner_position}
					<option value="{$kwixo_banner_position_key}" {if $kwixo_banner_position_key eq $kwixo_banner_position}Selected{/if}>{l s=$kwixo_banner_position_name mod='kwixo'}</option>	
				{/foreach}
			</select><br />
		</div>
		<label>{l s='Enable simulator on products page' mod='kwixo'}</label>
		<div class="margin-form">
			<input name="kwixo_show_simulator" type="checkbox" value="1" {if $kwixo_show_simulator eq '1'}Checked{/if} /> 
		</div>
	</fieldset>

	<br/>	

	<fieldset>
		<legend><img src="{$logo_categories_path}" />{l s='Categories settings' mod='kwixo'}</legend>
		<p>{l s='For a better quality of service, Kwixo needs to know the types of products' mod='kwixo'} :</p>
		<label>{l s='Default Product Type' mod='kwixo'}</label>
		<div class="margin-form">
			<select name="kwixo_default_product_type">
				<option value="0">-- {l s='Choose' mod='kwixo'} --</option>
				{foreach from=$kwixo_product_types item=product_type key=id_product_type name=product_types}
					<option value="{$id_product_type}" {if $kwixo_default_product_type eq $id_product_type}Selected{/if}>{$product_type}</option>
				{/foreach}
			</select>
		</div>

		<div class="margin-form">
			<table class="table">
				<thead>
					<tr><th>{l s='Shop category' mod='kwixo'}</th><th>{l s='Kwixo category' mod='kwixo'}</th><th>{l s='Delivery' mod='kwixo'}</th></tr>
				</thead>
				<tbody>
					{foreach from=$shop_categories key=id item=shop_category name=shop_categories}
						<tr>
							<td>{$shop_category.name}</td>
							<td>
								<select name="kwixo_{$id}_product_type">
									<option value="0">-- {l s='Choose' mod='kwixo'} --</option>
									{foreach from=$kwixo_product_types item=product_type key=id_product_type name=product_types}
										<option value="{$id_product_type}" {if $shop_category.kwixo_type eq $id_product_type}Selected{/if}>{$product_type}</option>
									{/foreach}
								</select>
							</td>
							<td>
								<input align="center" size="2" name="kwixo_{$id}_product_type_delivery" type="text" value="{$shop_category.kwixo_delivery}" />
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	</fieldset>

	<br/>

	<fieldset>
		<legend><img src="{$logo_carriers_path}"/>{l s='Carrier settings' mod='kwixo'}</legend>
		<p>{l s='Thank you for selecting a type of carrier for each carrier of your shop' mod='kwixo'} :</p>		
		<label>{l s='Default Carrier Type' mod='kwixo'}</label>
		<div class="margin-form">
			<select name="kwixo_default_carrier_type">
				<option value="0">-- {l s='Choose' mod='kwixo'} --</option>
				{foreach from=$kwixo_carrier_types key=id_carrier_type item=kwixo_carrier_type name=kwixo_carrier_types}
					<option value="{$id_carrier_type}" {if $kwixo_default_carrier_type eq $id_carrier_type}Selected{/if}>{$kwixo_carrier_type}</option>
				{/foreach}
			</select>
			<select name="kwixo_default_carrier_speed">
				{foreach from=$kwixo_carrier_speeds key=id_carrier_speed item=kwixo_carrier_speed name=kwixo_carrier_speeds}
					<option value="{$id_carrier_speed}" {if $kwixo_default_carrier_speed eq $id_carrier_speed}Selected{/if}>{$kwixo_carrier_speed}</option>
				{/foreach}
			</select>
		</div>

		<div class="margin-form">
			<table cellspacing="0" cellpadding="0" class="table">
				<thead><tr><th>{l s='Carrier' mod='kwixo'}</th><th>{l s='Carrier Type' mod='kwixo'}</th><th>{l s='Carrier Speed' mod='kwixo'}</th><th>{l s='Delivery' mod='kwixo'}</th></tr></thead>
				<tbody>
					{foreach from=$shop_carriers key=id_shop_carrier item=shop_carrier name=shop_carriers}
						<tr>
							<td>{$shop_carrier.name}</td>
							<td>
								<select name="kwixo_{$id_shop_carrier}_carrier_type">
									<option value="0">-- {l s='Choose' mod='kwixo'} --</option>
									{foreach from=$kwixo_carrier_types key=id_carrier_type item=kwixo_carrier_type name=kwixo_carrier_types}
										<option value="{$id_carrier_type}" {if $shop_carrier.kwixo_type eq $id_carrier_type}Selected{/if}>{$kwixo_carrier_type}</option>
									{/foreach}
								</select>
							</td>
							<td>
								<select name="kwixo_{$id_shop_carrier}_carrier_speed">
									{foreach from=$kwixo_carrier_speeds key=id_carrier_speed item=kwixo_carrier_speed name=kwixo_carrier_speeds}
										<option value="{$id_carrier_speed}" {if $shop_carrier.kwixo_speed eq $id_carrier_speed}Selected{/if}>{$kwixo_carrier_speed}</option>
									{/foreach}
								</select>
							</td>
							<td>
								<input size="2" name="kwixo_{$id_shop_carrier}_carrier_delivery" type="text" value="{$shop_carrier.kwixo_delivery}" />
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>

		<br /><img src="{$logo_warning}"/>{l s='To use the withdrawal store, you must enter the address of your store.' mod='kwixo'} <a href="{$link_shop_setting}" target="_blank">{l s='Check the details of the shop here' mod='kwixo'}</a>.

	</fieldset>

	<br/>
	<center><input type="submit" name="submitSettings" value="{l s='Save' mod='kwixo'}" class="button" /></center>

</form>	
<br/>
<center><input type="button" name="submitLog" onclick="ShowHide();" value="{l s='Show/Hide Kwixo log file' mod='kwixo'}" class="button" /></center>
<br/>
<center>
	<fieldset id="kwixo_log" style="display:none;">
		<textarea cols="100%" rows="10">{$log_content}</textarea>
		<br/>
	</fieldset>
</center>
<br/>

<fieldset>
	<legend><img src="{$logo_information}"/>{l s='Manage your payments in the Kwixo administration interface' mod='kwixo'}</legend>
	{l s='Your administration interface' mod='kwixo'} : <a target='_blank' href='https://business.kwixo.com/merchantbo/login.htm'>{l s='https://business.kwixo.com/merchantbo/login.htm' mod='kwixo'}</a>.
	<br/><br/>{l s='The administration interface allows you Kwixo manage your payments: monitoring, cancellation, refund.' mod='kwixo'}
</fieldset>
