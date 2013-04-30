<div style="clear:both" class="table_block">
	<table class="detail_step_by_step std">
		<thead>
			<tr>
				<th colspan="2">{l s='Fid\'Bag account' mod='fidbag'}</th>
			</tr>
		</thead>
		<tbody>
			{if isset($fidbag) && $fidbag->returnInfos->mCode == 0}
			<tr class="item">
				<td>{l s='Fid\'Bag Card number' mod='fidbag'}</td>
				<td>{$fidbag->FidBagCardNumber|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr class="item">
				<td>{l s='End of Validity' mod='fidbag'}</td>
				<td>{$fidbag->EndValidity|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr class="item">
				<td>{l s='Vertical Credit' mod='fidbag'}</td>
				<td>{$fidbag->VerticalCredit|escape:'htmlall':'UTF-8'}</td>
			</tr>
			{else if isset($fidbag) &&  $fidbag->returnInfos->mCode != 0}
			<tr class="item">
				<td>{l s='Error' mod='fidbag'}</td>
				<td>{$fidbag->returnInfos->mMessage|escape:'htmlall':'UTF-8'}</td>
			</tr>
			{else}
			<tr class="item">
				<td>{l s='Error' mod='fidbag'}</td>
				<td></td>
			</tr>
			{/if}
		</tbody>
	</table>
</div>
