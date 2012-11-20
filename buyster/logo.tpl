<form method="post" action="index.php?tab={$global.tab}&configure={$global.configure}&token={$global.token}&tab_module={$global.tab_module}&module_name={$global.module_name}&id_tab=5&section=logo" class='form' id="formLogo">
	<fieldset style="border: 0px;">
	<p style='font-size:15px'>{l s='You can display the Buyster logo on your shop, this may reassure your customers about the fact that you are a serious merchant.' mod='buyster'}</p><br/>
			<label>{l s='Select the logo position' mod='buyster'} :</label>
			<div class='margin-form'>
			<select name="logo_position">
			{$option}
			</select>
			</div>
			<p class='margin-form' style='font-size:15px;color:black'>
			{l s='Change your logo position in the Front Office. Works with' mod='buyster'} <a style='color:blue' href='{$link}'>{l s='Live edit.' mod='buyster'}</a>
			</p>
			<div class='margin-form'><input type="submit" class='button' name="submitLogo" value="{l s='Save' mod='buyster'}" /></div>
	</fieldset>
</form>