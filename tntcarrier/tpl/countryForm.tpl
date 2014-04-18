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

<form action="index.php?tab={$glob.tab}&configure={$glob.configure}&token={$glob.token}&tab_module={$glob.tab_module}&module_name={$glob.module_name}&id_tab=3&section=country&action=edit" method="post" class="form" id="configFormCountry">
	<table class="table" cellspacing="0" cellpadding="0">
		<tr>
			<td><input type="hidden" name="tnt_carrier_country" size="20" value="{$varCountryForm.country}"/>{$varCountryForm.country}</td>
			<td><input type="text" name="tnt_carrier_{$varCountryForm.country}_overcost" size="20" value="{$varCountryForm.overcost}"/></td>
			<td><input class="button" name="submitSave" type="submit" value="{l s='save' mod='tntcarrier'}"></td>
		</tr>
	</table>
</form>