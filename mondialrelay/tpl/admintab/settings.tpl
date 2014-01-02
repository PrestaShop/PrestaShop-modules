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

{include file="$MR_local_path/tpl/post_action.tpl"}

{*
** Basic Settings
*}
<div>
	<form action="{$smarty.server.REQUEST_URI}" method="post" class="form">
		<fieldset>
			<legend><img src="../modules/mondialrelay/images/logo.gif" />{l s='Admin Tab Settings' mod='mondialrelay'}</legend>
			<label for="id_order_state">{l s='Order state' mod='mondialrelay'}</label>
			<div class="margin-form">
				<select id="id_order_state" name="id_order_state" style="width:250px">
				{foreach from=$MR_orders_states_list key=num_state item=order_state}
					{assign var='selected_option' value=''}
					{if $order_state.id_order_state == $MR_order_state.id_order_state}
						{assign var='selected_option' value='selected="selected"'}
					{/if}
					<option value="{$order_state.id_order_state}" style="background-color:{$order_state.color};" {$selected_option}>{$order_state.name}</option>
				{/foreach}
				</select>
				<p>
				{l s='Choose the order state for labels.' mod='mondialrelay'}
				</p>
			</div>

			<div class="clear"></div>
			<div class="margin-form">
				<input type="submit" name="submit_order_state"  value="{l s='Save' mod='mondialrelay'}" class="button" />
			</div>
		</fieldset>
		<input type="hidden" name="MR_action_name" value="{l s='Settings'}" />
	</form>
</div>

<br />