<link rel="stylesheet" href="{$css_dir}global.css" type="text/css" media="all">
{if $valid}
	<center>
		<table style="width: 42%;">
			<tr align="center">
				<th colspan="2"><h1 style="text-align: center;">{l s='Purchase Data' mod='payulatam'}</h1></th>
			</tr>
			<tr align="left">
				<td>{l s='Transaction State' mod='payulatam'}</td>
				<td>{$estadoTx}</td>
			</tr>
			<tr align="left">
				<td>{l s='Transaction ID' mod='payulatam'}</td>
				<td>{$transactionId}</td>
			</tr>		
			<tr align="left">
				<td>{l s='Purchase Reference' mod='payulatam'}</td>
				<td>{$reference_pol}</td>
			</tr>		
			<tr align="left">
				<td>{l s='Transaction Reference' mod='payulatam'}</td>
				<td>{$referenceCode}</td>
			</tr>	
			{if $pseBank!=null}
				<tr align="left">
					<td>CUS</td>
					<td>{$cus}</td>
				</tr>
				<tr align="left">
					<td>{l s='Bank' mod='payulatam'}</td>
					<td>{$pseBank}</td>
				</tr>
			{/if}
			<tr align="left">
				<td>{l s='Total Value' mod='payulatam'}</td>
				<td>${$value}</td>
			</tr>
			<tr align="left">
				<td>{l s='Currency' mod='payulatam'}</td>
				<td>{$currency}</td>
			</tr>
			<tr align="left">
				<td>{l s='Description' mod='payulatam'}</td>
				<td>{$description}</td>
			</tr>
			<tr align="left">
				<td>{l s='Entity' mod='payulatam'}</td>
				<td>{$lapPaymentMethod}</td>
			</tr>
		</table>
		<p/>
		<h1>{$message}</h1>
	</center>
{else}
	<h1><center>{l s='The request is incorrect! There is an error in the digital signature.' mod='payulatam'}</center></h1>
{/if}