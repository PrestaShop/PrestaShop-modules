<br/>
<fieldset id="fieldset_order_buyster" style="width:400px;">
	<div style="position:relative;top:0px;left:0px">
		<div style="display:none;position:absolute;top:-12px;left:-10px;background:url('../img/macFFBgHack.png') repeat;width:420px;text-align:center" id="waitingBuysterOrder">
			<img id="loader_buyster_order" src='../img/loader.gif' alt='wait'/>
		</div>
	</div>
	<legend><img src="../img/admin/details.gif" />{l s='Buyster transaction state' mod='buyster'}</legend>
	{$resultWebServiceBuyster}<br/>
	<input type="hidden" value="{$order_id_buyster}" id="order_id_buyster"/>
	<input type="hidden" value="{$buyster_token}" id="buyster_token"/>
	{l s='You can cancel or refund the transaction (depending on whether it has already been funded or not) by clicking the button below. Ditto for the payment validation. The status of the command (above) will be automatically updated in all cases.' mod='buyster'}<br/>
	{$returnWebService}<br/>
	<div id="resultWebServiceBuyster"></div>
</fieldset>
<form id="refreshAdminOrder" action="./?tab=AdminOrders&id_order={$order_id_buyster}&vieworder&token={$token}" method="POST">
	<input type="hidden" name="actionBuyster" id="actionBuyster"/>
	<input type="hidden" name="paramBuyster" id="paramBuyster"/>
</form>
<script type="text/javascript">
{literal}
$(document).ready(function() {
    $("#waitingBuysterOrder").css('height', $("#fieldset_order_buyster").height());
	if ($("#waitingBuysterOrder").height() > 24)//$("#loader_buyster_order").height())
		$("#loader_buyster_order").css('margin-top', ($("#waitingBuysterOrder").height() - 24) / 2);
	$("#waitingBuysterOrder").show('fast');
	var id_order = $("#order_id_buyster").val();
	var token = $("#buyster_token").val();
	$("#resultWebServiceBuyster").load('../modules/buyster/adminOrder.php?id_order='+id_order+'&token='+token,
	function(response, status, xhr) 
		{
			document.getElementById("waitingBuysterOrder").style.display = 'none';
			if (status == "error") 
				$("#resultWebServiceBuyster").html(xhr.status + " " + xhr.statusText);
		}
	)
});


function valideOrder(action)
	{
		var days = document.getElementById('buyster_payment_days_delayed').value;
		orderAction(action, days);
	}

function orderAction(action, param)
{
	document.getElementById('actionBuyster').value = action;
	document.getElementById('paramBuyster').value = param;
	document.getElementById("refreshAdminOrder").submit();
}
{/literal}
</script>