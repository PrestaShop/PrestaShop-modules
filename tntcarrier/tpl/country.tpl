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

<table class="table" cellspacing="0" cellpading="0">
	<tr>
		<th>{l s='Place' mod='tntcarrier'}</th><th>{l s='Additionnal charge(Euros)' mod='tntcarrier'}</th><th></th>
	</tr>
	<tr>
		<td>{$varCountry.country}</td><td>{$varCountry.overcost}</td>
		<td>
		<a href="index.php?tab={$glob.tab}&configure={$glob.configure}&token={$glob.token}&tab_module={$glob.tab_module}&module_name={$glob.module_name}&id_tab=3&section=country&action=edit&country={$varCountry.country}">
			<img src="../img/admin/edit.gif" alt="edit" title="{l s='edit' mod='tntcarrier'}"/></a>
		</td>
	</tr>
</table>
</table><br/><div id="divFormCountry">
{if ($varCountry.action == 'edit' || $varCountry.action == 'new') && $varCountry.section == 'country'}
{$varCountry.form}
{/if}
</div>