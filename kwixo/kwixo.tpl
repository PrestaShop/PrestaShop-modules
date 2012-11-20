{literal}
<script>
function popuprnp1xrnp(){
var win2 = window.open("http://www.kwixo.com/static/payflow/html/popup-1x-rnp.htm",'popup',
'height=705,width=610,status=no,scrollbars=no,menubar=no,resizable=no');
}

function popuprnp3xrnp(){
var win2 = window.open("http://www.kwixo.com/static/payflow/html/popup-3x.htm",'popup',
'height=905,width=800,status=no,scrollbars=no,menubar=no,resizable=no');
}

function popuprnpstrnp(){
var win2 = window.open("http://www.kwixo.com/static/payflow/html/popup-1x.htm",'popup',
'height=705,width=610,status=no,scrollbars=no,menubar=no,resizable=no');
}
</script>
{/literal}
{if isset($direct) && $direct}
	<p class="payment_module">
		<b>
		<a href="{$modules_dir}kwixo/sendtoRNP.php?payment=3">
		<img width="115" src="{$modules_dir}kwixo/logoKwixo_Co_V.png" /> 
		{l s='Kwixo once in a CB. It is simple, convenient and secure.' mod='kwixo'}
		<span style="display: block;margin-left: 125px;margin-top: 5px;" onClick="popuprnpstrnp();return false;"><u>{l s='Learn more' mod='kwixo'}</u></span>
		</a>
		</b>
	</p>
{/if}
{if isset($comptant) && $comptant}
	<p class="payment_module">
		<b><a href="{$modules_dir}kwixo/sendtoRNP.php?payment=1"><img width="115" src="{$modules_dir}kwixo/logoKwixo_PAR_V.png" /> 
			{l s='Kwixo once in a CB, service payment after receipt included.' mod='kwixo'}
			<span style="display: block;margin-left: 125px;margin-top: 5px;" onClick="popuprnp1xrnp();return false;"><u>{l s='Learn more' mod='kwixo'}</u></span>
		</a>
		</b>
	</p>
{/if}
{if isset($credit) && $credit}
	<p class="payment_module">
		<b>
		<a href="{$modules_dir}kwixo/sendtoRNP.php?payment=2">
		<img width="115" src="{$modules_dir}kwixo/logoKwixo_Cr_V.png" /> 
		{l s='Kwixo in installments, payment service receipt included.' mod='kwixo'}
		<span style="display: block;margin-left: 125px;margin-top: 5px;" onClick="popuprnp3xrnp();return false;"><u>{l s='Learn more' mod='kwixo'}</u></span>
		</a>
		</b>
	</p>
{/if}

