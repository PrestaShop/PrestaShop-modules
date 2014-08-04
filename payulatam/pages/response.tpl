<link rel="stylesheet" href="{$css_dir}global.css" type="text/css" media="all">
{if $valid}
	<center>
		<table style="width: 42%;">
			<tr align="center">
				<th colspan="2"><h1 style="text-align: center;">{l s='Purchase Data'}</h1></th>
			</tr>
			<tr align="left">
				<td>{l s='Transaction State'}</td>
				<td>{$estadoTx}</td>
			</tr>
			<tr align="left">
				<td>{l s='Transaction ID'}</td>
				<td>{$transactionId}</td>
			</tr>		
			<tr align="left">
				<td>{l s='Purchase Reference'}</td>
				<td>{$reference_pol}</td>
			</tr>		
			<tr align="left">
				<td>{l s='Transaction Reference'}</td>
				<td>{$referenceCode}</td>
			</tr>	
			{if $pseBank!=null}
				<tr align="left">
					<td>CUS</td>
					<td>{$cus}</td>
				</tr>
				<tr align="left">
					<td>{l s='Bank'}</td>
					<td>{$pseBank}</td>
				</tr>
			{/if}
			<tr align="left">
				<td>{l s='Total Value'}</td>
				<td>${$value}</td>
			</tr>
			<tr align="left">
				<td>{l s='Currency'}</td>
				<td>{$currency}</td>
			</tr>
			<tr align="left">
				<td>{l s='Description'}</td>
				<td>{$description}</td>
			</tr>
			<tr align="left">
				<td>{l s='Entity'}</td>
				<td>{$lapPaymentMethod}</td>
			</tr>
		</table>
		<p/>
		<h1>{$message}</h1>
	</center>
{else}
	<h1><center>{l s='The request is incorrect! There is an error in the digital signature.'}</center></h1>
{/if}