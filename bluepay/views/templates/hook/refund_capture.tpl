{*
* 2013 BluePay
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
*  @author BluePay Processing, LLC
*  @copyright  2013 BluePay Processing, LLC
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<br />
{if $transaction_status == '1'}
        <h3><b><font color="green">Message from the BluePay gateway: {$transaction_message|escape:'htmlall':'UTF-8'}</font></b></h3>
	<br />
{elseif $transaction_status === '0' || $transaction_status == 'E'}
	<h3><b><font color="red">Error: {$transaction_message|escape:'htmlall':'UTF-8'}</font></b></h3>
	<br />
{/if}
{if $can_capture}
	<fieldset>
        	<legend><img src="{$base_url|escape:'htmlall':'UTF-8'}modules/{$module_name|escape:'htmlall':'UTF-8'}/logo.png" alt="" />{l s='BluePay Capture' mod='bluepay'}</legend>
		<p><b>{l s='Information:' mod='bluepay'}</b> {l s='This order has been authorized, but no capture has been processed.' mod='bluepay'}</b></p>
        	<p><b>{l s='Information:' mod='bluepay'}</b> {l s='Funds are ready to be captured before shipping.' mod='bluepay'}</b></p>
        	<form method="post" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}">
                	<input type="hidden" name="id_order" value="{$params.id_order|escape:'htmlall':'UTF-8'}" />
                	<p class="center"><input type="submit" class="button" name="submitBluePayCapture" value="{l s='Capture the authorization' mod='bluepay'}" /></p>
        	</form>
	</fieldset>
{/if}
{if $can_refund}
	</fieldset>
		<legend><img src="{$base_url|escape:'htmlall':'UTF-8'}modules/{$module_name|escape:'htmlall':'UTF-8'}/logo.png" alt="" />{l s='BluePay Refund' mod='bluepay'}</legend>
		<p><b>{l s='Information:' mod='bluepay'}</b> {l s='Payment has been accepted from customer.' mod='bluepay'}</b></p>
		<form method="post" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}">
			<input type="hidden" name="id_order" value="{$params.id_order|escape:'htmlall':'UTF-8'}" />
			<p class="center">
				<input type="submit" class="button" name="submitBluePayRefund" value="{l s='Refund total transaction' mod='bluepay'}" onclick="if (!confirm('{l s='Are you sure you want to refund this order?' mod='bluepay'}'))return false;" />
			</p>
		</form>
	</fieldset>
{/if}
