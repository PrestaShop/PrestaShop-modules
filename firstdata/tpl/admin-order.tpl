<br />
<fieldset>
	<legend style="line-height: 24px;"><img src="../modules/firstdata/logo.gif" alt="" />{l s='First Data' mod='firstdata'}</legend>
	{if isset($firstdata_message)}<div class="conf">{$firstdata_message|escape:'htmlall':'UTF-8'}</div>{/if}
	{if isset($firstdata_error)}<div class="error">{$firstdata_error|escape:'htmlall':'UTF-8'}</div>{/if}
	{l s='This order has been placed using First Data, here are the transaction details:' mod='firstdata'}<br /><br />
	<table class="table" cellpadding="0" cellspacing="0" style="width: 300px; float: left; margin-right: 20px;">
		<tr>
			<td>{l s='Status:' mod='firstdata'}</td>
			<td><span style="font-weight: bold; color: {if $firstdata_transaction_approved}green;">{l s='Approved' mod='firstdata'}{else}red;">{l s='Declined' mod='firstdata'}{/if}</span></td>
		</tr>
		<tr>
			<td>{l s='Bank message:' mod='firstdata'}</td>
			<td>"{$firstdata_bank_message|escape:'htmlall':'UTF-8'}"</td>
		</tr>
		<tr>
			<td>{l s='Amount:' mod='firstdata'}</td>
			<td><span style="font-weight: bold;">{$firstdata_amount|escape:'htmlall':'UTF-8'} {$firstdata_currency_code|escape:'htmlall':'UTF-8'}</span></td>
		</tr>
		<tr>
			<td>{l s='Card:' mod='firstdata'}</td>
			<td>***{$firstdata_cc_number|escape:'htmlall':'UTF-8'} ({$firstdata_credit_card_type|escape:'htmlall':'UTF-8'})</td>
		</tr>
		<tr>
			<td>{l s='Card expiry:' mod='firstdata'}</td>
			<td>{$firstdata_cc_expiry|escape:'htmlall':'UTF-8'}</td>
		</tr>
		<tr>
			<td>{l s='Card Holder:' mod='firstdata'}</td>
			<td>{$firstdata_cardholder_name|escape:'htmlall':'UTF-8'}</td>
		</tr>
		<tr>
			<td>{l s='Authorization Number:' mod='firstdata'}</td>
			<td>{$firstdata_authorization_num|escape:'htmlall':'UTF-8'}</td>
		</tr>
		<tr>
			<td>{l s='Transaction Tag:' mod='firstdata'}</td>
			<td>{$firstdata_transaction_tag|escape:'htmlall':'UTF-8'}</td>
		</tr>
		<tr>
			<td>{l s='Transaction Date:' mod='firstdata'}</td>
			<td>{$firstdata_date_add|escape:'htmlall':'UTF-8'}</td>
		</tr>
		{if $firstdata_date_cancel != ''}
		<tr>
			<td>{l s='Cancelation attempt:' mod='firstdata'}</td>
			<td>{$firstdata_date_cancel|escape:'htmlall':'UTF-8'}</td>
		</tr>
		{/if}
		{if $firstdata_date_refund != ''}
		<tr>
			<td>{l s='Refund attempt:' mod='firstdata'}</td>
			<td>{$firstdata_date_refund|escape:'htmlall':'UTF-8'}</td>
		</tr>
		{/if}
	</table>
	<div cellpadding="0" cellspacing="0" style="width: 200px; padding: 10px; border: 1px solid #BBB; float: left; background: #FFF8C6;">
		{if $firstdata_transaction_approved && ($firstdata_date_cancel == '' || $firstdata_date_refund == '')}
			<form action="{$firstdata_form|escape:'htmlall':'UTF-8'}" method="post">
				<ul>
					{if $firstdata_date_cancel == ''}
					<li>{l s='If the order has been placed today and is unsettled, you can void it:' mod='firstdata'}<br />
					<br /><input class="button" onclick="return confirm('{l s='Are you sure you want to cancel this order?' mod='firstdata' js=1}');" type="submit" name="firstDataCancel" value="{l s='Cancel/void this order' mod='firstdata'}" /><br /><br /></li>
					{/if}
					{if $firstdata_date_refund == ''}
					<li>{l s='Else, if the order is already settled, you can proceed to a refund:' mod='firstdata'}<br />
					<br /><input class="button" onclick="return confirm('{l s='Are you sure you want to refund this order?' mod='firstdata' js=1}');" type="submit" name="firstDataRefund" value="{l s='Refund this order' mod='firstdata'}" /><br /><br /></li>
					{/if}
				</ul>			
			</form>
		{else}
			{l s='This transaction was declined by First Data or you already canceled/refunded it.'}
		{/if}
	</div>
	<div class="clear"></div>
	<p style="clear: both;"><b>{l s='Information retrieved on:' mod='firstdata'} {$smarty.now|date_format:"%Y-%m-%d %H:%M:%S"}</b></p>
</fieldset>