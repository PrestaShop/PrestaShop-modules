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
* @author PrestaShop SA <contact@prestashop.com>
* @copyright  2007-2013 PrestaShop SA
* @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}

<table width="100%" cellspacing="0" cellpadding="0" style="margin-top:15px;margin-bottom:15px;" class="table hidetableblock">
	<thead>
		<tr>
			<th colspan="2">{l s='Contacts list' mod='mailin'}</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td style="border-bottom:none;">{$middlelable}
			</td>
		</tr>
		<tr id="userDetails" style="display:none;">
			<td>
				<table class="table managesubscribeBlock" style="margin-top:20px;" cellspacing="0" cellpadding="0" width="100%">
					<thead>
						<tr>
							<th>Emails</th>
							<th width="20%">{l s='Client' mod='mailin'}</th>
							<th width="20%">{l s='Newsletter Mailin Status' mod='mailin'}<span class="toolTip" title="{l s='Click on the icon to subscribe / unsubscribe the contact from Mailin and PrestaShop.' mod='mailin'}">&nbsp;</span></th>
							<th width="20%">{l s='Newsletter PrestaShop Status' mod='mailin'}</th>
						</tr>
					</thead>
					<tbody class="midleft"> 
					</tbody>
				</table>
			</td>
		</tr>
	</tbody>
</table>
