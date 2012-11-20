<script type="text/javascript" src="../modules/{$global.module_name}/js/diagnostic.js"></script>
<div id="resultDiagnostic" style="float:right;width:500px"></div>
<form action="index.php?tab={$global.tab}&configure={$global.configure}&token={$global.token}&tab_module={$global.tab_module}&module_name={$global.module_name}&id_tab=3&section=diagnostic" method="post" class="form" id="configFormDiagnostic">
	<fieldset style="border: 0px;">
		<h4>{l s='Diagnostic'} :</h4>
		<label for="buyster_payment_diagnostic_reference">{l s='reference'} :</label>
		<div class="margin-form">
			<input type="text" name="buyster_payment_diagnostic_reference" id="buyster_payment_diagnostic_reference"/><br/>
		</div>
	</fieldset>
	<div class="margin-form"><input class="button" name="submitSave" type="submit" onclick="getDiagnostic('buyster_payment_diagnostic_reference');return false;"><img style="display:none;margin-left:50px" id="loaderDiagnostic" src="../img/loader.gif" alt="loading"/></div>
</form>