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

{if $MR_errors_type.error|count}
<div class="MR_error">
	{l s='Please kindly correct the following errors on' mod='mondialrelay'}
	<a href="index.php?tab={$MR_token_admin_contact.controller_name}&token={$MR_token_admin_contact.token}" style="color:#f00;">
		{l s='the contact page:' mod='mondialrelay'}
	</a>
	<ul>
		{foreach from=$MR_errors_type.error key=name item=message}
			<li>{$name}: {$message}</li>
		{/foreach}
	</ul>
</div>
{/if}
{if $MR_errors_type.warn|count}
<div class="MR_warn">
	{l s="Please take a look to this following warning, maybe the ticket won't be generated"}
	<ul>
		{foreach from=$MR_errors_type.warn key=name item=message}
			<li>{$name}: {$message}</li>
		{/foreach}
	</ul>
</div>
{/if}

<p>
{l s='All orders which have the state' mod='mondialrelay'} "<b>{$MR_order_state.name}</b>" {l s='will be available for creation of labels' mod='mondialrelay'}
</p>

<div class="PS_MRErrorList error" id="otherErrors">
	<span></span>
</div>

<fieldset>
	<legend>{l s='Orders list' mod='mondialrelay'}</legend>
	<form method="post" action="{$smarty.server.REQUEST_URI}" class="MR_form_admintab">
		<table class="table" id='orders'>
			<tr>
				<th><input type="checkbox" id="toggleStatusOrderList" /></th>
				<th>{l s='Order ID' mod='mondialrelay'}</th>
				<th>{l s='Customer' mod='mondialrelay'}</th>
				<th>{l s='Total price' mod='mondialrelay'}</th>
				<th>{l s='Total shipping costs' mod='mondialrelay'}</th>
				<th>{l s='Date' mod='mondialrelay'}</th>
				<th>{l s='Insert weight (grams)' mod='mondialrelay'}</th>
				<th>{l s='MR Number' mod='mondialrelay'}</th>
				<th>{l s='MR Country' mod='mondialrelay'}</th>
				<th>{l s='Exp Number' mod='mondialrelay'}</th>
				<th>{l s='Detail' mod='mondialrelay'}</th>
			</tr>
		{foreach from=$MR_orders key=case_num item=order}
			<tr id="PS_MRLineOrderInformation-{$order.id_order}">
				<td><input type="checkbox" class="order_id_list" name="order_id_list[]" id="order_id_list" value="{$order.id_order}" /></td>
				<td>{$order.id_order}</td>
				<td>{$order.customer}</td>
				<td>{$order.display_total_price}</td>
				<td>{$order.display_shipping_price}</td>
				<td>{$order.display_date}</td>
				<td>
					<input type="text" name="weight_{$order.id_order}" id="weight_{$order.id_order}" size="7" value="{$order.weight}" />
				</td>
				<td>{$order.MR_Selected_Num}</td>
				<td>{$order.MR_Selected_Pays}</td>
				<td>{$order.exp_number}</td>

				<td class="center">
					<a href="index.php?tab=AdminOrders&id_order={$order.id_order}&vieworder&token={$MR_token_admin_orders}">
						<img border="0" title="{l s='View' mod='mondialrelay'}" alt="{l s='View' mod='mondialrelay'}" src="{$new_base_dir}images/details.gif"/>
					</a>
				</td>
			</tr>
			<tr class="PS_MRErrorList error" id="errorCreatingTicket_{$order.id_order}" style="display:none;">
				<td colspan="11" style="background:url({$MR_PS_IMG_DIR_}admin/error2.png) 10px 10px no-repeat;">
					<span></span>
				</td>
			</tr>
			<tr class="PS_MRSuccessList" id="successCreatingTicket_{$order.id_order}" style="display:none;">
				<td>{$order.id_order}</td>
				<td colspan="10" style="background:url({$MR_PS_IMG_DIR_}admin/ok2.png) 10px 5px no-repeat #DFFAD3;">
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
			<div class="PS_MRLoader" id="PS_MRSubmitGenerateLoader"><img src="{$new_base_dir}images/getTickets.gif"</div>
		</div>
	{/if}
	</form>
</fieldset>

<br />