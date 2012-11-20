<script type='text/javascript'>
{literal}
function popupbuyster(){
	var win2 = window.open("http://www.buyster.fr", '_newtab');
}
{/literal}
</script>

<p class="payment_module">
	<a href="{$var.this_path_ssl}payment.php">
		<img src="{$var.path}logobuyster.png" alt="Buyster" style="float:left;width:115px"/>
		{l s='Buyster: payment solution on the Internet free, safe and convenient approved by the Bank of France.' mod='buyster'}<br/><span onclick='popupbuyster();return false;' style="display: block;margin-left: 125px;margin-top: 5px;"><u>{l s='more' mod='buyster'}</u></span><br/><br/>
	</a>
</p>