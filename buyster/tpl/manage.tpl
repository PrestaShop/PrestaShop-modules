<style>
{literal}
	#manageList p
	{
		font-size:15px;
	}
	ol li
	{
		margin-bottom:10px;
		font-size:15px;
	}
{/literal}
</style>
<ul id="manageList">
	<li>
		<p>
		{l s='When an order is placed and payment is accepted by Buyster, the status is "payment accepted" except for validation payment that is "Waiting validation from your shop"' mod='buyster'}.<br/>
		</p>
	</li>
	<li>
		<p>
		{l s='In your order, you will see 2 buttons as below' mod='buyster'}:<br/>
		<p style='text-align:center'>
		<img src='../modules/buyster/capture.png' alt='capture'/>
		</p>
		<ol>
			<li>{l s='Cancel or Refund : This feature allows you to cancel or refund a Buyster transaction. If the transaction is not completed, a cancellation of payment (and of the order) will be made and visible by the customer. If the transaction has already been done, it\'s a full refund of the payment that will be made' mod='buyster'}.</li>
			<li>{l s='Validation : This feature allows you to trigger the sending of a financing transaction with or without delayed. It is only available to transaction that are awaiting approval' mod='buyster'}.</li>
			<!--<li>{l s='Duplicate : This feature allows you to duplicat a Buyster transation. It is only possible for 13 mounths after the creation of the transaction on every orders, excepted those in a "Failed" state' mod='buyster'}</li>-->
		</ol>
		</p>
	</li>
</ul>
