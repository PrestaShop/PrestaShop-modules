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
<p class="payment_module">
	{if $allow_stored_payments == 'Yes' && $has_saved_payment_information}
		{$customer = $saved_payment_information.customer_name|escape:'htmlall':'UTF-8'}
	{/if}
	<form name="bluepay_form" id="bluepay_form" action="{$module_dir|escape:'htmlall':'UTF-8'}validation.php" method="post">
		<span style="border: 1px solid #595A5E;display: block;padding: 0.6em;text-decoration: none;margin-left: 0.7em;">
			<a id="click_bluepay" href="#" title="{l s='Pay with BluePay' mod='bluepay'}" style="display: block;text-decoration: none; font-weight: bold;">
				{if $payment_type != 'ACH'}
					{if $cards.visa == 1}<img src="{$module_dir|escape:'htmlall':'UTF-8'}img/visa-small.png" alt="{l s='Visa Logo' mod='bluepay'}" style="vertical-align: middle;" />{/if}
					{if $cards.mastercard == 1}<img src="{$module_dir|escape:'htmlall':'UTF-8'}img/mastercard-small.png" alt="{l s='Mastercard Logo' mod='bluepay'}" style="vertical-align: middle;" />{/if}
					{if $cards.amex == 1}<img src="{$module_dir|escape:'htmlall':'UTF-8'}img/amex.png" alt="{l s='American Express Logo' mod='bluepay'}" style="vertical-align: middle;" />{/if}
					{if $cards.discover == 1}<img src="{$module_dir|escape:'htmlall':'UTF-8'}img/discover.png" alt="{l s='Discover Logo' mod='bluepay'}" style="vertical-align: middle;" />{/if}
					{if $payment_type == 'BOTH'}
						<img src="{$module_dir|escape:'htmlall':'UTF-8'}img/echeck-small.png" alt="{l s='Check Logo' mod='bluepay'}" style="vertical-align: middle;" />
						&nbsp;&nbsp;{l s='Secure credit card/E-check payment with BluePay' mod='bluepay'}
					{else}
						&nbsp;&nbsp;{l s='Secure credit card payment with BluePay' mod='bluepay'}
					{/if}
				{else}
					<img src="{$module_dir|escape:'htmlall':'UTF-8'}img/echeck-small.png" alt="{l s='Check Logo' mod='bluepay'}" style="vertical-align: middle;" />
                                        &nbsp;&nbsp;{l s='Secure E-check payment with BluePay' mod='bluepay'}
				{/if}
			</a>
			<div id="payment_fields" style="display:none">
			<br /><br />
			<p class="error" id="error-text" style="display:none"></p>
			<input type="hidden" name="invoice_id" id="invoice_id" value="{$invoice_id|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="master_id" id="master_id" value="" />
			<input type="hidden" name="require_cvv2" id="require_cvv2" value="{$require_cvv2|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="allow_stored_payments" id="allow_stored_payments" value="{$allow_stored_payments|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="has_saved_payment_information" id="has_saved_payment_information" value="{$has_saved_payment_information|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="pay_type" id="pay_type" value="{$payment_type|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="expiration_mm" id="expiration_mm" value="" />
			<input type="hidden" name="expiration_yy" id="expiration_yy" value="" />
			<input type="hidden" name="cc_type" id="cc_type" value="" />
			{if $display_logo == 'Yes'}
				{if $payment_type == 'BOTH'}
					<div id="left_sidebar" style="width: 136px; height: 210px; float: left; padding-top:40px; padding-right: 20px; border-right: 1px solid #DDD;">
				{else}
					<div id="left_sidebar" style="width: 136px; height: 240px; float: left; padding-top:20px; padding-right: 20px; border-right: 1px solid #DDD;">
				{/if}
					<img src="{$module_dir|escape:'htmlall':'UTF-8'}img/bluepay2.gif" alt="secure payment" />
				</div>
			{/if}
				{if $allow_stored_payments == 'Yes' && $has_saved_payment_information}
					<label for="use_saved_payment_information" style="margin-top: 4px; margin-left: 35px; display: block; width: 90px; float: left;">{l s='Use Stored Payment?' mod='bluepay'}</label>
                                	<input type="checkbox" name="use_saved_payment_information" id="use_saved_payment_information" value="Yes" style="margin-top: 4px; margin-left: 0px; display: block; float: left;" /><br /><br /><br />
				{/if}
				<label style="margin-top: 4px; margin-left: 35px;display: block;width: 90px;float: left;">{l s='Full name' mod='bluepay'}</label> <input type="text" name="name" id="fullname" size="24" maxlength="25S" value="{$customer|escape:'htmlall':'UTF-8'}"/><br /><br />
			{if $payment_type == 'BOTH'}
				<label style="margin-top: 4px; margin-left: 35px; display: block;width: 90px;float: left;">{l s='Payment Method' mod='bluepay'}</label>
				<select id="payment_type">
					<option value="CC">Credit Card</option>
					<option value="ACH">E-check</option>
				</select>
				<br /><br />
			{/if}
			{if $payment_type != 'ACH'}
				<div id="cc_fields">
					<label style="margin-top: 4px; margin-left: 35px; display: block;width: 90px;float: left;">{l s='Card Type' mod='bluepay'}</label>
					<select id="card_type">
						{if $cards.visa == 1}<option value="VISA">Visa</option>{/if}
						{if $cards.mastercard == 1}<option value="MC">MasterCard</option>{/if}
						{if $cards.amex == 1}<option value="AMEX">American Express</option>{/if}
						{if $cards.discover == 1}
							<option value="DISC">Discover</option>
							<option value="DC">Diners Club</option>
							<option value="JCB">JCB</option>
							<option value="Union">UnionPay</option>
							<option value="BC">BC Card</option>
							<option value="DinaCard">DinaCard</option>
						{/if}
					</select>
					<br /><br />

					<label style="margin-top: 4px; margin-left: 35px; display: block; width: 90px; float: left;">{l s='Card number' mod='bluepay'}</label> <input type="text" name="card_number" id="card_number" size="24" maxlength="16" autocomplete="Off" value="" /><br /><br />
					<label style="margin-top: 4px; margin-left: 35px; display: block; width: 90px; float: left;">{l s='Expiration date' mod='bluepay'}</label>
				 	{html_options name=card_expiration_mm options=$card_expiration_mm id=card_expiration_mm|escape:'htmlall':'UTF-8'}
					/
					{html_options name=card_expiration_yy options=$card_expiration_yy id=card_expiration_yy|escape:'htmlall':'UTF-8'}<br /><br />
					{if $require_cvv2 == 'Yes'}
						<label style="margin-top: 4px; margin-left: 35px; display: block; width: 90px; float: left;">{l s='CVV' mod='bluepay'}</label> <input type="text" name="cvv2" id="cvv2" size="4" maxlength="4"/>   <img src="{$module_dir|escape:'htmlall':'UTF-8'}img/help-small.png" id="cvv_help" title="{l s='The 3 or 4 digit Card Verification Value located on your credit card' mod='bluepay'}" alt="" /><br /><br />
					{/if}
				</div>
			{/if}
			{if $payment_type != 'CC'}
				{if $allow_stored_payments == 'Yes' && $has_saved_payment_information && !$has_saved_cc_payment_information}
					{assign var="saved_ach_information" value=":"|explode:$saved_payment_information.masked_payment_account|escape:'htmlall':'UTF-8'}
				{else}
					{assign var="saved_ach_information" value=array('','','')}
				{/if}
				<div id="ach_fields">
					<label id="ach_account_label" style="margin-top: 4px; margin-left: 35px; display: block; width: 90px; float: left;">{l s='Account Number' mod='bluepay'}</label> <input type="text" name="ach_account" id="ach_account" size="18" autocomplete="Off" value="" /><br /><br />
					<label id="ach_routing_label" style="margin-top: 4px; margin-left: 35px; display: block; width: 90px; float: left;">{l s='Routing Transit Number' mod='bluepay'}</label> <input type="text" name="ach_routing" id="ach_routing" size="18" value="" /><br /><br />
					<label id="ach_account_type_label" style="margin-top: 4px; margin-left: 35px; display: block;width: 90px;float: left;">{l s='Account Type' mod='bluepay'}</label>
					{html_options name=ach_account_type id=ach_account_type options=$ach_account_types}<br /><br /><br /><br />
				</div>
			{else}
				{assign var="saved_ach_information" value=array('','','')}
			{/if}
			{if $allow_stored_payments == 'Yes' && !$has_saved_payment_information}
				<label style="margin-top: 4px; margin-left: 35px; display: block; width: 90px; float: left;">{l s='Store Payment Information?' mod='bluepay'}</label> <input type="checkbox" name="save_payment_information" id="save_payment_information" value="Yes" />
				<img src="{$module_dir|escape:'htmlall':'UTF-8'}img/help-small.png" id="save_payment_help" title="{l s='By selecting this, you will be able to securely store your payment information for later purchases' mod='bluepay'}" alt="" /><br /><br /><br />
			{else}
				<input type="hidden" name="save_payment_information" id="save_payment_information" value="" />
			{/if}
				<input type="button" id="submit_payment" value="{l s='Process Order' mod='bluepay'}" style="margin-left: 129px; padding-left: 25px; padding-right: 25px; float: left;" class="button" />
				<br class="clear" /><br />
			</div>
		</span>
	</form>
</p>

{literal}
<script>
	$(document).ready(function () {
		$('#use_saved_payment_information').change(function() 
		{
			if (this.checked && {/literal}{$has_saved_cc_payment_information|escape:'htmlall':'UTF-8'}{literal} == 1)
			{
				var payment_type = ({/literal}'{$saved_payment_information.payment_type|escape:'htmlall':'UTF-8'}'{literal} == 'CREDIT') ?
					'CC' : 'ACH';
				if ($('#payment_type').val() != payment_type) $('#payment_type').val(payment_type);
				if ($('#payment_type').val() == 'CC' && $('#payment_type').val() != payment_type) {
                        		$('#ach_fields').fadeOut('fast', function() {
                                		$('#cc_fields').fadeIn();
                        		});
                		} else {
                        		$('#cc_fields').fadeIn('fast', function() {
                                		$('#ach_fields').fadeOut();
						if ($('#require_cvv2').val() == 'Yes')
							$('#left_sidebar').css('height', '220px');
						else
							$('#left_sidebar').css('height', '180px');
                        		});
                		}
				$('#card_number').val({/literal}'{$saved_payment_information.masked_payment_account|escape:'htmlall':'UTF-8'}'{literal});
				$('#master_id').val({/literal}'{$saved_payment_information.bluepay_customer_id|escape:'htmlall':'UTF-8'}'{literal});
				$('#card_expiration_mm').val({/literal}'{$saved_payment_information.expiration_date|substr:0:2|escape:'htmlall':'UTF-8'}'{literal});
				$('#card_expiration_yy').val({/literal}'{$saved_payment_information.expiration_date|substr:2:4|escape:'htmlall':'UTF-8'}'{literal});
				$('#card_type').val({/literal}'{$saved_payment_information.card_type|escape:'htmlall':'UTF-8'}'{literal});
				$('#payment_type').add($('#card_number')).add('#card_expiration_mm').add('#card_expiration_yy').add('#card_type').fadeTo('fast', 0.5, function() { 
					$('#card_number').prop('readonly', true);
					$('#expiration_mm').val($('#card_expiration_mm').val());
					$('#expiration_yy').val($('#card_expiration_yy').val());
					$('#cc_type').val($('#card_type').val());
					$('#payment_type').add($('#card_expiration_mm')).add($('#card_expiration_yy')).add($('#card_type')).prop('disabled', true);
				});
			} 
			else if(this.checked && {/literal}{$has_saved_cc_payment_information|escape:'htmlall':'UTF-8'}{literal} != 1)
			{
				var payment_type = ({/literal}'{$saved_payment_information.payment_type|escape:'htmlall':'UTF-8'}'{literal} == 'CREDIT') ?
                                        'CC' : 'ACH';
				if ($('#payment_type').val() != payment_type) $('#payment_type').val(payment_type);
                                if ($('#payment_type').val() == 'ACH' && $('#payment_type').val() != payment_type) {
                                        $('#cc_fields').fadeOut('fast', function() {
                                                $('#ach_fields').fadeIn();
                                        });
                                } else {
                                        $('#ach_fields').fadeIn('fast', function() {
                                                $('#cc_fields').fadeOut();
						if ($('#allow_stored_payments').val() == 'Yes')
                                                	$('#left_sidebar').css('height', '190px');
						else
							$('#left_sidebar').css('height', '170px');
                                        });
                                }
				$('#ach_account').val({/literal}'{$saved_ach_information[2]|escape:'htmlall':'UTF-8'}'{literal});
				$('#ach_routing').val({/literal}'{$saved_ach_information[1]|escape:'htmlall':'UTF-8'}'{literal});
                                $('#ach_account_type').val({/literal}'{$saved_ach_information[0]|escape:'htmlall':'UTF-8'}'{literal});
				$('#master_id').val({/literal}'{$saved_payment_information.bluepay_customer_id|escape:'htmlall':'UTF-8'}'{literal});
                                $('#payment_type').add($('#ach_account')).add($('#ach_routing')).add($('#ach_account_type')).fadeTo('fast', 0.5, function() { 
                                        $('#ach_account').prop('readonly', true);
					$('#ach_routing').prop('readonly', true);
                                        $('#ach_account_type_type').val($('#card_type').val());
                                        $('#payment_type').add($('#ach_account_type')).prop('disabled', true);
                                });
			}
			else
			{
				$('#card_number').val('');
				$('#ach_account').val('');
				$('#ach_routing').val('');
				$('#master_id').val('');
                                $('#card_expiration_mm').val($('#card_expiration_mm option:first').val());
                                $('#card_expiration_yy').val($('#card_expiration_yy option:first').val());
				$('#ach_account_type').val($('#ach_account_type option:first').val());
                                $('#card_type').val($('#card_type option:first').val());
                                $('#payment_type').add($('#card_number')).add($('#card_expiration_mm')).add($('#card_expiration_yy')).add($('#card_type')).
					add($('#ach_account')).add($('#ach_routing')).add($('#ach_account_type')).fadeTo('fast', 1.0, function() { 
					$('#card_number').prop('readonly', false);
					$('#ach_account').prop('readonly', false);
					$('#ach_routing').prop('readonly', false);
                                        $('#expiration_mm').val('');
                                        $('#expiration_yy').val('');
                                        $('#cc_type').val('');
                                        $('#payment_type').add($('#card_expiration_mm')).add($('#card_expiration_yy')).add($('#card_type')).
						add($('#ach_account_type')).prop('disabled', false);
				});
			}
		});
	});
</script>
{/literal}
