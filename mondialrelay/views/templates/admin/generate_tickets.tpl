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
*  @version  Release: $Revision: 10285 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if $MR_errors_type.error|count}
<div class="MR_error">
	{l s='Please kindly correct the following errors on' mod='mondialrelay'}
	<a href="index.php?tab={$MR_token_admin_contact.controller_name|escape:'htmlall':'UTF-8'}&token={$MR_token_admin_contact.token|escape:'htmlall':'UTF-8'}" style="color:#f00;">
		{l s='the contact page:' mod='mondialrelay'}
	</a>
	<ul>
		{foreach from=$MR_errors_type.error key=name item=message}
			<li>{$name|escape:'htmlall':'UTF-8'}: {$message|escape:'htmlall':'UTF-8'}</li>
		{/foreach}
	</ul>
</div>
{/if}
{if $MR_errors_type.warn|count}
<div class="MR_warn">
	{l s='Please take a look to this following warning, maybe the ticket won\'t be generated' mod='mondialrelay'}
	<ul>
		{foreach from=$MR_errors_type.warn key=name item=message}
			<li>{$name|escape:'htmlall':'UTF-8'}: {$message|escape:'htmlall':'UTF-8'}</li>
		{/foreach}
	</ul>
</div>
{/if}

<p>
{l s='All orders which have the state' mod='mondialrelay'} "<b>{$MR_order_state.name|escape:'htmlall':'UTF-8'}</b>" {l s='will be available for creation of labels' mod='mondialrelay'}
</p>
<div class="bootstrap">
	<div class="PS_MRErrorList error alert alert-danger" id="otherErrors">
		<span></span>
	</div>
</div>

<fieldset>
	<legend>{l s='Orders list' mod='mondialrelay'}</legend>
	<form method="post" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" class="MR_form_admintab">
		<table class="table" id='orders'>
			<tr>
				<th><input type="checkbox" id="toggleStatusOrderList" /></th>
				<th>{l s='Order ID' mod='mondialrelay'}</th>
				<th>{l s='Customer' mod='mondialrelay'}</th>
				<th>{l s='Total price' mod='mondialrelay'}</th>
				<th>{l s='Total shipping costs' mod='mondialrelay'}</th>
				<th>{l s='Date' mod='mondialrelay'}</th>
				<th>{l s='Insert weight (grams)' mod='mondialrelay'}</th>
				<th>{l s='Choose an insurance' mod='mondialrelay'}</th>
				<th>{l s='MR Number' mod='mondialrelay'}</th>
				<th>{l s='MR Country' mod='mondialrelay'}</th>
				<th>{l s='Exp Number' mod='mondialrelay'}</th>
				<th>{l s='Detail' mod='mondialrelay'}</th>
			</tr>
		{foreach from=$MR_orders key=case_num item=order}
			<tr id="PS_MRLineOrderInformation-{$order.id_order|intval}">
				<td><input type="checkbox" class="order_id_list" name="order_id_list[]" id="order_id_list" value="{$order.id_order|intval}" /></td>
				<td>{$order.id_order|intval}</td>
				<td>{$order.customer|escape:'htmlall':'UTF-8'}</td>
				<td>{$order.display_total_price|floatval}</td>
				<td>{$order.display_shipping_price|floatval}</td>
				<td>{$order.display_date|escape:'htmlall':'UTF-8'}</td>
				<td>
					<input type="text" name="weight_{$order.id_order|intval}" id="weight_{$order.id_order|intval}" size="7" value="{$order.weight}" />
				</td>
				<td>
					<select name="MR_insurance_{$order.id_order|intval}" id="insurance_{$order.id_order|intval}" style="width:200px">
						<option value="0" {if $order.mr_insurance == 0}selected="selected"{/if}>0 : {l s='No insurance' mod='mondialrelay'}</option>
						<option value="1" {if $order.mr_insurance == 1}selected="selected"{/if}>1 : {l s='Complementary Insurance Lv1' mod='mondialrelay'}</option>
						<option value="2" {if $order.mr_insurance == 2}selected="selected"{/if}>2 : {l s='Complementary Insurance Lv2' mod='mondialrelay'}</option>
						<option value="3" {if $order.mr_insurance == 3}selected="selected"{/if}>3 : {l s='Complementary Insurance Lv3' mod='mondialrelay'}</option>
						<option value="4" {if $order.mr_insurance == 4}selected="selected"{/if}>4 : {l s='Complementary Insurance Lv4' mod='mondialrelay'}</option>
						<option value="5" {if $order.mr_insurance == 5}selected="selected"{/if}>5 : {l s='Complementary Insurance Lv5' mod='mondialrelay'}</option>
					</select>
				</td>
				<td>{$order.MR_Selected_Num|escape:'htmlall':'UTF-8'}</td>
				<td>{$order.MR_Selected_Pays|escape:'htmlall':'UTF-8'}</td>
				<td>{$order.exp_number|escape:'htmlall':'UTF-8'}</td>

				<td class="center">
					<a href="index.php?tab=AdminOrders&id_order={$order.id_order|intval}&vieworder&token={$MR_token_admin_orders}">
						<img border="0" title="{l s='View' mod='mondialrelay'}" alt="{l s='View' mod='mondialrelay'}" src="{$new_base_dir|escape:'htmlall':'UTF-8'}img/details.gif"/>
					</a>
				</td>
			</tr>
			<tr class="PS_MRErrorList error" id="errorCreatingTicket_{$order.id_order|intval}" style="display:none;">
				<td colspan="12" style="background: #f2dede">
					<span></span>
				</td>
			</tr>
			<tr class="PS_MRSuccessList" id="successCreatingTicket_{$order.id_order|intval}" style="display:none;">
				<td>{$order.id_order|intval}</td>
				<td colspan="11" style="background: #DFFAD3;">
					{l s='Operation successful' mod='mondialrelay'}
					<span></span>
				</td>
			</tr>
		{/foreach}
		</table>
	{if !$MR_orders|count}
		<h3 style="color:red;">{l s='No orders with this state.' mod='mondialrelay'}</h3>
		{else}
		<div class="submit_button">
			<div class="PS_MRSubmitButton" id="PS_MRSubmitButtonGenerateTicket">
				<input type="button" name="generate" id="generate" value="{l s='Generate' mod='mondialrelay'}" class="button" />
			</div>
			<div class="PS_MRLoader" id="PS_MRSubmitGenerateLoader"><img src="{$new_base_dir|escape:'htmlall':'UTF-8'}img/getTickets.gif"</div>
		</div>
	{/if}
	</form>
</fieldset>

<br />