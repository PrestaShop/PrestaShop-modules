{*
* Shopgate GmbH
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file AFL_license.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/AFL-3.0
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to interfaces@shopgate.com so we can send you a copy immediately.
*
* @author Shopgate GmbH, Schloßstraße 10, 35510 Butzbach <interfaces@shopgate.com>
* @copyright  Shopgate GmbH
* @license   http://opensource.org/licenses/AFL-3.0 Academic Free License ("AFL"), in the version 3.0
*}

<br />
<form action="" method="post">
<fieldset style="width: 400px;float: left;">
	<legend><img src="{$sModDir|escape:'htmlall':'UTF-8'}/logo.gif">{l s='Shopgate information' mod='shopgate'}</legend>
	
	{if $shopgate_error}<span style="color:red; font-weight:bold;">{$shopgate_error|escape:'htmlall':'UTF-8'}</span>{/if}
	
	{if $sShopNumber}
		<label>{l s='Shop number' mod='shopgate'}:</label>
		<div class="margin-form">{$sShopNumber|escape:'htmlall':'UTF-8'}</div>
	{/if}
	{if $sOrder}
		<label>{l s='Order number' mod='shopgate'}:</label>
		<div class="margin-form">
			{$shopgateOrder->order_number|escape:'htmlall':'UTF-8'}
		</div>
		<label>{l s='Paid' mod='shopgate'}:</label>
		<div class="margin-form">
			<img src="../img/admin/{if $sOrder->getIsPaid()}enabled{else}disabled{/if}.gif">
		</div>
		<label>{l s='Shipping blocked' mod='shopgate'}:</label>
		<div class="margin-form">
			<img src="../img/admin/{if $sOrder->getIsShippingBlocked()}enabled{else}disabled{/if}.gif">
		</div>
		<label>{l s='Delivered' mod='shopgate'}:</label>
		<div class="margin-form">
			<img src="../img/admin/{if $sOrder->getIsShippingCompleted()}enabled{else}disabled{/if}.gif">
		</div>
		{if $sOrder->getShippingInfos()}
			<label>{l s='Shipping method' mod='shopgate'}:</label>
			<div class="margin-form">
				{$shippingInfos->getName()|escape:'htmlall':'UTF-8'}
			</div>
		{/if}
		{if $sOrder->getPaymentTransactionNumber()}
			<label>{l s='Payment Transaction Number' mod='shopgate'}:</label>
			<div class="margin-form">
			{$sOrder->getPaymentTransactionNumber()|escape:'htmlall':'UTF-8'}
			</div>	
		{/if}
		{if $sOrderComments}
			<label>{l s='Comments' mod='shopgate'}:</label>
			<br /><br />
			{foreach from=$sOrderComments item=comment}
				{$comment|escape:'htmlall':'UTF-8'}<br /><br />
			{/foreach}
		{/if}
		
		<h4 style="border-bottom:1px solid #E0D0B1">{l s='Payment information' mod='shopgate'}</h4>
		{if count($sOrderPaymentInfos)}
			{foreach key="key" from=$sOrderPaymentInfos item="paymentInfos"}
				<label>{if isset($paymentInfoStrings[$key])}{{$paymentInfoStrings[$key]|escape:'htmlall':'UTF-8'}}{else}{{$key|escape:'htmlall':'UTF-8'}}{/if}:</label>
				<div class="margin-form">
					{if is_bool($paymentInfos)}<img src="../img/admin/{if $data}enabled{else}disabled{/if}.gif">{else} {$paymentInfos|escape:'htmlall':'UTF-8'} {/if}
				</div>
			{/foreach}	
		{/if}
		
		<h4 style="border-bottom:1px solid #E0D0B1">{l s='Delivery notes' mod='shopgate'}</h4>
		
		{if count($sOrderDeliveryNotes)}
			<table class="table" cellspacing="0" cellpadding="0" style="width:400px">
			<tr>
				<th>{l s='Service' mod='shopgate'}</th>
				<th>{l s='Tracking number' mod='shopgate'}</th>
				<th>{l s='Time' mod='shopgate'}</th>
			</tr>
			{foreach key="key" from=$sOrderDeliveryNotes item=note}
				<tr>
					<td>{$shipping_service_list[$note.shipping_service_id]|escape:'htmlall':'UTF-8'}</td>
					<td>{$note.tracking_number|escape:'htmlall':'UTF-8'}</td>
					<td>{$note.shipping_time|escape:'htmlall':'UTF-8'}</td>
				</tr>
			{/foreach}
			</table>
		{else}
			{l s='No delivery notes' mod='shopgate'}
		{/if}
			
	
		<h4 style="border-bottom:1px solid #E0D0B1">{l s='Shipping settings' mod='shopgate'}</h4>
	
		<label>{l s='Shipping service' mod='shopgate'}:</label>
		<div class="margin-form">
			{html_options name='shopgateOrder[shipping_service]' options=$shipping_service_list selected={$shopgateOrder->shipping_service|escape:'htmlall':'UTF-8'}}
		</div>
		<label>{l s='Tracking number' mod='shopgate'}:</label>
		<div class="margin-form">
			<input type="text" name="shopgateOrder[tracking_number]" value="{$shopgateOrder->tracking_number|escape:'htmlall':'UTF-8'}">
		</div>
		<div class="margin-form">
			<input type="submit" class="button" name="updateShopgateOrder" value="{l s='Save' mod='shopgate'}">
		</div>
	
	{elseif !$shopgate_error}
		<span style="color:red; font-weight:bold;">{l s='Order not found in shopgate' mod='shopgate'}</span>
	{/if}
</fieldset>
</form>