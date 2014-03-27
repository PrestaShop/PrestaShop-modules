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

<form action="index.php?tab={$glob.tab}&configure={$glob.configure}&token={$glob.token}&tab_module={$glob.tab_module}&module_name={$glob.module_name}&id_tab=3&section=service&action=new" method="post" class="form" id="configFormService">
	{if $varServiceForm.id != null}
		<input type="hidden" name="service_id" value="{$varServiceForm.id}"/>
	{/if}
	<table class="table" cellspacing="0" cellpadding="0">
		<tr>
			<th>{l s='Name' mod='tntcarrier'}</th><th>{l s='Description' mod='tntcarrier'}</th><th>{l s='code' mod='tntcarrier'}</th><th>{l s='Additionnal Charge' mod='tntcarrier'}</th><th>{l s='Activated' mod='tntcarrier'}</th><th></th>
		</tr>
		<tr>
			<td><input type="text" name="tnt_carrier_service_name" size="20" value="{$varServiceForm.name}"/></td>
			<td><input type="text" name="tnt_carrier_service_description" size="20" value="{$varServiceForm.description}"/></td>
			<td><input type="text" name="tnt_carrier_service_code" size="5" value="{$varServiceForm.code}"/></td>
			<td><input type="text" name="tnt_carrier_service_charge" size="10" value="{$varServiceForm.charge}"/></td>
			<td><input type="radio" name="tnt_carrier_service_display" value="0" {if $varServiceForm.display == '1'} checked="checked"	{/if} /> <img src="../img/admin/disabled.gif" /><br/>
				<input type="radio" name="tnt_carrier_service_display" value="1" {if $varServiceForm.display == '0'} checked="checked"	{/if} /> <img src="../img/admin/enabled.gif" />
			</td>
			<td><input class="button" name="submitSave" type="submit" value="{l s='save' mod='tntcarrier'}"></td>
		</tr>
	</table>
</form>