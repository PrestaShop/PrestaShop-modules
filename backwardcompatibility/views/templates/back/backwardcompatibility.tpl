<fieldset name="configuration">

	<legend>{l s='Configuration' mod='backwardcompatibility'}</legend>

	{l s='Here is a list of modules that are available on this shop. All of these modules are using the Backward Compatibility tool.' mod='backwardcompatibility'}<br />
	{l s='To work properly some of these modules have to be updated.' mod='backwardcompatibility'}

	<table width="100%" style="margin: 40px 0; border:1px solid #CCC" cellspacing="0" cellpadding="4px 10px">
		<thead style="background: #DDD">
			<tr>
				<th width="40%">{l s='Module' mod='backwardcompatibility'}</th>
				<th width="20%">{l s='Backward C. version' mod='backwardcompatibility'}</th>
				<th width="20%">{l s='Writable' mod='backwardcompatibility'}</th>
				<th width="20%">{l s='Update' mod='backwardcompatibility'}</th>
			</tr>
		</thead>

		<tbody>
			{foreach from=$modules item=module}
				{assign var='module_name' value=$module.name}
				{assign var='module_display_name' value=$module.display_name}
				<tr>
					<td>{$module_display_name}</td>
					<td>{if $module.version}{$module.version}{/if}</td>
					<td>
						{if $module.writable == true}
							<img src="{$image_dir}/accept.png" /> <span style="color: #0A0;">{l s='Writable' mod='backwardcompatibility'}</span>
						{else}
							<img src="{$image_dir}/exclamation.png" /> <span style="color: #A00;">{l s='Not writable' mod='backwardcompatibility'}</span>
						{/if}
					</td>
					<td>
						{if isset($update_results) && isset($update_results.$module_name)}
							{if $update_results.$module_name == true}
								<img src="{$image_dir}/accept.png" /> <span style="color: #0A0;">{l s='Update succeed' mod='backwardcompatibility'}</span>
							{else}
								<img src="{$image_dir}/exclamation.png" /> <span style="color: #A00;">{l s='Update failed' mod='backwardcompatibility'}</span>
							{/if}
						{/if}
					</td>
				</tr>
			{/foreach}
		</tbody>
	</table>

	<form action="" method="POST" style="text-align: center">
		<input type="submit" class="button" name="submit" value="{l s='Update modules' mod='backwardcompatibility'}" />
	</form>

</fieldset>
