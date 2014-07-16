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

<fieldset>
	<legend>{l s='History of labels creation' mod='mondialrelay'}</legend>
	<div style="overflow-x: auto;overflow-y: scroller; padding-top: 0.6em;" >
		<form method="post" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" class="MR_form_admintab">
			<table class="table" id="PS_MRHistoriqueTableList">
				<tbody>
					<tr>
						<th><input type="checkbox" id="toggleStatusHistoryList" /></th>
						<th>{l s='Order ID' mod='mondialrelay'}</th>
						<th>{l s='Exp num' mod='mondialrelay'}</th>
						<th>{l s='Print stick A4' mod='mondialrelay'}</th>
						<th>{l s='Print stick A5' mod='mondialrelay'}</th>
						<th>{l s='Print stick 10x15' mod='mondialrelay'}</th>
					</tr>
				{foreach from=$MR_histories key=num_history item=history}
					<tr id="detailHistory_{$history.order|intval}">
						<td>
							<input type="checkbox" id="PS_MRHistoryId_{$history.id|intval}" class="history_id_list" name="history_id_list[]" value="{$history.id|intval}" />
						</td>
						<td>{$history.order|intval}</td>
						<td id="expeditionNumber_{$history.order|intval}">
							{$history.exp|escape:'htmlall':'UTF-8'}
						</td>
						<td id="URLA4_{$history.order|intval}">
							<a href="{$history.url_a4|escape:'htmlall':'UTF-8'}" target="a4"><img width="20" src="{$new_base_dir|escape:'htmlall':'UTF-8'}img/pdf_icon.jpg" /></a>
						</td>
						<td id="URLA5_{$history.order|intval}">
							<a href="{$history.url_a5|escape:'htmlall':'UTF-8'}" target="a5"><img width="20" src="{$new_base_dir|escape:'htmlall':'UTF-8'}img/pdf_icon.jpg" /></a>
						</td>
						<td id="URL10x15_{$history.order|intval}">
							<a href="{$history.url_10x15|escape:'htmlall':'UTF-8'}" target="a5"><img width="20" src="{$new_base_dir|escape:'htmlall':'UTF-8'}img/pdf_icon.jpg" /></a>
						</td>
					</tr>
				{/foreach}
				</tbody>
			</table>
		{if !$MR_histories|count}
			<div id="MR_error_histories">
				<h3 style="color:red;">{l s='No histories available' mod='mondialrelay'}</h3>
			</div>
			{else}
			<div class="PS_MRSubmitButton">
				<input type="button" id="PS_MRSubmitButtonPrintSelectedA4" name="printSelectedA4" value="{l s='Print selected stick A4' mod='mondialrelay'}" class="button" />
				<input type="button" id="PS_MRSubmitButtonPrintSelectedA5" name="printSelectedA5" value="{l s='Print selected stick A5' mod='mondialrelay'}" class="button" />
				<input type="button" id="PS_MRSubmitButtonPrintSelected10x15" name="printSelected10x15" value="{l s='Print selected stick 10x15' mod='mondialrelay'}" class="button" />
				<input type="button" id="PS_MRSubmitButtonDeleteHistories" name="deleteSelectedHistories" value="{l s='Delete selected history' mod='mondialrelay'}" class="button" />
				<div class="PS_MRLoader" id="PS_MRSubmitDeleteHistoriesLoader">
					<img src="{$new_base_dir|escape:'htmlall':'UTF-8'}img/getTickets.gif"
				</div>
			</div>
		{/if}
		</form>
	</div>
</fieldset>

<br />