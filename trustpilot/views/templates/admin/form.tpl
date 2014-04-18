{*}
/*
* 2007-2013 Profileo
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to contact@profileo.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade Profileo to newer
* versions in the future. If you wish to customize Profileo for your
* needs please refer to http://www.profileo.com for more information.
*
*  @author Profileo <contact@profileo.com>
*  @copyright  2007-2013 Profileo
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of Profileo
*/
{*}

<script type="text/javascript" src="../modules/{$module_name|escape:'htmlall':'UTF-8'}/js/trustpilot.js"></script>

<fieldset id="intro">
	<legend><img src="../modules/{$module_name|escape:'htmlall':'UTF-8'}/logo.gif" alt="" /> {l s='Introduction' mod='trustpilot'}</legend>
	<p style="text-align:justify;">
		{l s='To setup this module please connect to your Trustpilot Business account.' mod='trustpilot'}
		<br/>
		<ul>
			<li>{l s='This module will allow you to :' mod='trustpilot'}
				<ul>
					<li>{l s='Install the automatic feedback service' mod='trustpilot'}</li>
					<li>{l s='Extract your customer data to use the kickstart module' mod='trustpilot'}</li>
					<li>{l s='Show your reviews with the Trustbox Widget module' mod='trustpilot'}</li>
				</ul>
			</li>
		</ul>
		{l s='Do you need help? Please contact our technical support:' mod='trustpilot'}
		<a style="color:#268CCD;" href="mailto:{l s='support@trustpilot.com' mod='trustpilot'}">
			{l s='support@trustpilot.com' mod='trustpilot'}
		</a> /
		<b style="font-size:13px">{l s='UK 0800 011-9795 / US 877 941-0960' mod='trustpilot'}</b>
	</p>
</fieldset>

<form action="{$server_request}" method="post">
	<br />
	<fieldset>
		<legend><img src="../modules/{$module_name|escape:'htmlall':'UTF-8'}/logo.gif" alt="" /> {l s='Automatic mail service' mod='trustpilot'}</legend>
			<label for="tp_email">{l s='Email used by Trustpilot' mod='trustpilot'} <span style="color:#ff0000">*</span></label>
			<div class="margin-form">
				<input type="text" id="tp_email" name="tp_email" value="{$email|escape:'htmlall':'UTF-8'}" />
				<span class="hint">
					<span>
						{l s='Email like xxxxx@trustpilotservice.com you can find it in the automatic feedback module.' mod='trustpilot'}
					</span>
				</span>
			</div>
			<label for="tp_order_status">{l s='Order status' mod='trustpilot'}</label>
			<div class="margin-form">
				<select id="tp_order_status" name="tp_order_status[]" size="{$order_statuses|count}" multiple>
					{foreach from=$order_statuses key=status_id item=status}
						<option {if in_array(status_id, $tp_states)}selected="selected"{/if} value="{$status_id|intval}">{$status|escape:'htmlall':'UTF-8'}</option>
					{/foreach}
				</select>
				<span class="hint">
					<span style="width:224px;">
						{l s='Hold down the Ctrl key (Windows) / Commande (Mac) to select multiple options.' mod='trustpilot'}
					</span>
				</span>
			</div>
			<label for="tp_delay">{l s='Mailing delay' mod='trustpilot'}</label>
			<div class="margin-form">
				<input type="text" id="tp_delay" name="tp_delay" value="{$delay|escape:'htmlall':'UTF-8'}" />
				<span class="hint">
					<span>
						{l s='Enter in the number of days you wish to delay before the review invitations are sent.' mod='trustpilot'}
					</span>
				</span>
			</div>
			<label for="tp_domain">{l s='Domain name' mod='trustpilot'}</label>
			<div class="margin-form">
				<input type="text" id="tp_domain" name="tp_domain" value="{$domain|escape:'htmlall':'UTF-8'}" />
				<span class="hint">
					<span>
						{l s='Trustpilot Business Domain name. You can find this information on the top right of your Trustpiot business account.' mod='trustpilot'}
					</span>
				</span>
			</div>
	</fieldset>
	<p style="text-align:center;"><input type="submit" class="button" name="submitTrustPilotConfig" style="cursor:pointer" value="{l s='Save configuration' mod='trustpilot'}" /></p>
</form>

<!-- Kickstart form -->
<form action="{$server_request}" method="post">
	<fieldset>
		<legend><img src="../modules/{$module_name|escape:'htmlall':'UTF-8'}/logo.gif" alt="" /> {l s='Kickstart' mod='trustpilot'}</legend>
		<label for="tp_kstartnb">{l s='Number of clients' mod='trustpilot'}</label>
		<div class="margin-form">
			<input type="text" name="tp_kstartnb" id="tp_kstartnb" value=""/>
			<span class="hint">
				<span>
					{l s='Create your file to ask your previous customers to leave a review with the kickstart module.' mod='trustpilot'}
				</span>
			</span>
			<p><input class="button" type="submit" name="submitKickstart" id="tp_kstartsend" 
				value="{l s='Export list in kickstart format' mod='trustpilot'}"/>
			</p>
		</div>
	</fieldset>
</form>

<!-- Displays Widget 1 -->
<form action="{$server_request}" method="post">
	<p>&nbsp;</p>
	<fieldset>
		<legend><img src="../modules/{$module_name|escape:'htmlall':'UTF-8'}/logo.gif" alt="" /> {l s='Trustbox Widget' mod='trustpilot'}</legend>
		<table width="49%" style="float:left; border:1px solid #DFD5C3;">
			<caption>{l s='Widget 1' mod='trustpilot'}</caption>
			<tr>
				<td style="vertical-align:top;">
					<label for="tp_widget1" style="text-align:left;width:auto;">
						{l s='Enter the code provided by the Trustbox widget:' mod='trustpilot'}
					</label>
				</td>
				<td style="vertical-align:top;">
					<label for="tp_widget1_pos" style="text-align:left;">
						{l s='Assign this widget to the position(s):' mod='trustpilot'}
					</label>
				</td>
			</tr>
			<tr>
				<td style="vertical-align:top;">
					<textarea id="tp_widget1" cols="25" rows="10" name="tp_widget1" style="width:92%;">
						{$tp_widget1|escape:'htmlall':'UTF-8'}
					</textarea>
					<span class="hint" style="font-size:11px;">
						<span>
							{l s='Copy/paste the code generated with the Trustbox 2.0 module in order to display the reviews on your webshop.' mod='trustpilot'}
						</span>
					</span>
				</td>
				<td style="vertical-align:top;">
					<select id="tp_widget1_pos" name="tp_widget1_pos[]" multiple="multiple" style="width:170px;height:170px;">
						{foreach from=$arr_positions item=v key=k}
							<option value="{$k|escape:'htmlall':'UTF-8'}" {if (in_array($k, $arr_widget1_pos))}selected="selected"{/if}>{$v|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
					<span class="hint" style="font-size:11px;">
						<span>{l s='Assign this widget to the position(s).' mod='trustpilot'}</span>
					</span>
				</td>
			</tr>
		</table>
		
<!-- Displays Widget 2 -->
		<table width="48%" style="float:right; border:1px solid #DFD5C3;">
			<caption>{l s='Widget 2' mod='trustpilot'}</caption>
			<tr>
				<td style="vertical-align:top;">
					<label for="tp_widget2" style="text-align:left;width:auto;">
						{l s='Enter the code provided by the Trustbox widget:' mod='trustpilot'}
					</label>
				</td>
				<td style="vertical-align:top;">
					<label for="tp_widget2_pos" style="text-align:left;">
						{l s='Assign this widget to the position(s):' mod='trustpilot'}
					</label>
				</td>
			</tr>
			<tr>
				<td style="vertical-align:top;">
					<textarea id="tp_widget2" cols="25" rows="10" name="tp_widget2" style="width:92%;">
						{$tp_widget2|escape:'htmlall':'UTF-8'}
					</textarea>
					<span class="hint" style="width:136px;font-size:11px;">
						<span>
							{l s='Enter the code provided by the Trustbox widget' mod='trustpilot'}
						</span>
					</span>
				</td>
				<td style="vertical-align:top;">
					<select id="tp_widget2_pos" name="tp_widget2_pos[]" multiple="multiple" style="width:170px;height:170px;">
						{foreach from=$arr_positions item=v key=k}
							<option value="{$k|escape:'htmlall':'UTF-8'}" {if (in_array($k, $arr_widget2_pos))}selected="selected"{/if}>{$v|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
					<span class="hint" style="width:136px;font-size:11px;">
						<span>{l s='Assign this widget to the position(s).' mod='trustpilot'}</span>
					</span>
				</td>
			</tr>
		</table>
		<div class="clear"></div>
	</fieldset>
	
	<p style="text-align:center;">
		<input type="submit" class="button" name="submitTrustPilotTrustBox" style="cursor:pointer" value="{l s='Save widgets configuration' mod='trustpilot'}" />
	</p>
</form>
