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
*  @version  Release: $Revision: 7732 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}



<script>
    $(document).ready(function() {

        $('form.container-command-top-spacing').submit(function(e) {
            var self = this;
            e.preventDefault();
            jQuery.fancybox($('#warn_refund').html(), {
                afterClose : function() {
                    $('form.container-command-top-spacing').append('<input type="hidden" name="partialRefund" />');
                    self.submit();
                }
            });
            return true;
        });
    });
</script>
<div id="warn_refund" style="width:400px;display: none;">
    <div>
        {l s='Remember once created the "Credit Slip" to process the refund via the dedicated form in the SysPay section!' mod='syspay'}
    </div>
</div>
<br />
<fieldset style="width: 900px; margin-bottom: 40px;">
	<legend><img src="../img/admin/money.gif" /> {l s='SysPay' mod='syspay'}</legend>
    <h4>{l s='Payments' mod='syspay'}</h4>
    {if isset($info_payment)}
	<table class="table" width="100%" cellspacing="0" cellpadding="0" id="">
		<thead>
			<tr>
				<th>{l s='ID' mod='syspay'}</th>
				<th>{l s='Reference' mod='syspay'}</th>
				<th>{l s='Amount' mod='syspay'}</th>
				<th>{l s='Currency' mod='syspay'}</th>
				<th>{l s='Statut' mod='syspay'}</th>
				<th>{l s='Date' mod='syspay'}</th>
				<th>{l s='Action' mod='syspay'}</th>
				</tr>
			</thead>
			<tbody>
				<tr>
                    <td>{$info_payment.id|escape:'htmlall':'UTF-8'}</td>
                    <td>{$info_payment.reference|escape:'htmlall':'UTF-8'}</td>
                    <td>{$info_payment.amount|escape:'htmlall':'UTF-8'}</td>
                    <td>{$info_payment.currency|escape:'htmlall':'UTF-8'}</td>
                    <td>{$info_payment.status|escape:'htmlall':'UTF-8'}</td>
                    <td>{$info_payment.pt|escape:'htmlall':'UTF-8'}</td>
                    <td>
                        {if isset($show_btn)}
                            <form method="post" action="{$currentIndex|escape:'htmlall':'UTF-8'}&vieworder&id_order={$id_order|intval}&token={$smarty.get.token|escape:'htmlall':'UTF-8'}">
                                <input type="hidden" name="sp_cancel_payment" value="2" />
                                <input type="submit" class="button" name="sp_action_cancel_payment" value="{l s='Cancel the payment' mod='syspay'}" />
                            </form>
                        {/if}
                    </td>
				</tr>
			</tbody>
		</table>
    {else}
        <p>
            {l s='Payments list unavailable' mod='syspay'}
        </p>
    {/if}

    <h4>{l s='Refunds' mod='syspay'}</h4>
    {if isset($info_refund)}
        <table class="table" width="100%" cellspacing="0" cellpadding="0" id="">
            <thead>
            <tr>
                <th>{l s='ID' mod='syspay'}</th>
                <th>{l s='Reference' mod='syspay'}</th>
                <th>{l s='Amount' mod='syspay'}</th>
                <th>{l s='Currency' mod='syspay'}</th>
                <th>{l s='Statut' mod='syspay'}</th>
                <th>{l s='Description' mod='syspay'}</th>
                <th>{l s='Date' mod='syspay'}</th>
            </tr>
            </thead>
            <tbody>
                {foreach from=$info_refund item=r}
                <tr>
                    <td>{$r.id|escape:'htmlall':'UTF-8'}</td>
                    <td>{$r.reference|escape:'htmlall':'UTF-8'}</td>
                    <td>-{$r.amount|escape:'htmlall':'UTF-8'}</td>
                    <td>{$r.currency|escape:'htmlall':'UTF-8'}</td>
                    <td>{$r.status|escape:'htmlall':'UTF-8'}</td>
                    <td>{$r.description|escape:'htmlall':'UTF-8'}</td>
                    <td>{$r.pt|escape:'htmlall':'UTF-8'}</td>
                </tr>
                 {/foreach}
           </tbody>
        </table>
    {else}
        <p>
            {l s='No refunds' mod='syspay'}
        </p>
    {/if}
    {if isset($show_refund)}
        <form style="margin-top: 10px"  method="post" action="{$currentIndex|escape:'htmlall':'UTF-8'}&vieworder&id_order={$id_order|intval}&token={$smarty.get.token|escape:'htmlall':'UTF-8'}">
            <label style="width: auto">{l s='Make a refund' mod='syspay'}</label><br style="clear: both" />
            <span>{l s='You cannot refund more than the initial payment' mod='syspay'}</span><br /><br />
            <input type="hidden" name="refund_form" value="2" />
            <br /><span>{l s='Amount' mod='syspay'}</span><br />
            <input type="text" name="refund_value" style="width: 120px;" />
            <span>{$info_payment.currency|escape:'htmlall':'UTF-8'}</span><br /><br/>
            <span>{l s='Reason' mod='syspay'}</span><br />
            <input type="text" name="refund_reason" />
            <input type="submit" class="button" name="make_refund" value="{l s='Refund' mod='syspay'}" />
        </form>
    {/if}
</fieldset>