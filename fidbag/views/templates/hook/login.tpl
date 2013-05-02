<script type="text/javascript">
	var fidbag_login = "{$fidbag_login|escape:'htmlall':'UTF-8'}";
	var fidbag_password = "{$fidbag_password|escape:'htmlall':'UTF-8'}";
	var cart = parseInt({$glob.id_cart});
	var customer = parseInt({$glob.id_customer});
	var mainUrl = "{$main_url}";
{literal}

if ((fidbag_login != '') && (fidbag_password != ''))
$(document).ready(
		function() {
			$("#fidbag_client_remind").attr('checked', true);
			connectUserFidBag();
		}
);

function connectUserFidBag()
{
	$.ajax({
		url: mainUrl+"/modules/fidbag/login.php",
		type: "POST",
		data: {
			login : $("#fidbag_client_login").val(),
			password : $("#fidbag_client_password").val(),
			remind : true,
			customer : customer,
			cart : cart,
			token: "{/literal}{$fidbag_token|escape:'htmlall':'UTF-8'}{literal}",
		},
		dataType: "html",
		success: function(data) {
			var obj = jQuery.parseJSON(data);
			if ((obj.mCode != undefined) && (obj.mCode != '0'))
				$("#fidbag_submit_connect_result").html(obj.mMessage);
			else if (data != '0')
			{
				outPutInformationUser(obj);
				$("#fidbag_submit_connect_result").html();
			}
			else
				$("#fidbag_submit_connect_result").html("{/literal}{l s='Technical error, please try later' mod='fidbag'}{literal}");

		},
		error: function(er)
		{
			$("#fidbag_submit_connect_result").html("{/literal}{l s='Technical error, please try later' mod='fidbag'}{literal}");
		}
	});

}

function passwordUserFidBag()
{
	if ($("#fidbag_client_login").val() == '')
	{
		alert("Indiquer votre Login");
		return false;
	}
	$.ajax({
		url: mainUrl+"/modules/fidbag/lost_password.php?action=LostPassword&token="+token,
		type: "POST",
		data: {
			Login : $("#fidbag_client_login").val(),
			LanguageCode : "fr-FR",
			Token: "{/literal}{$fidbag_token|escape:'htmlall':'UTF-8'}{literal}",
		},
		dataType: "json",
		success: function(data)
		{
			if (data.mCode == 0)
				$("#fidbag_password_result").html("{/literal}{l s='Un e-mail vous a ete envoyé' mod='fidbag'}{literal}");
			else
				$("#fidbag_password_result").html("{/literal}{l s='Erreur' mod='fidbag'}{literal}");
		},
		error: function(er)
		{
			$("#fidbag_password_result").html("{/literal}{l s='Technical error, please try later' mod='fidbag'}{literal}");
		}
	});
}

{/literal}
</script>

<div>
<h4>{l s='I already have a Fid\'Bag account' mod='fidbag'}</h4><br/>
	<div class="div_form_fidbag">
		<label>{l s='Email (Fid\'Bag account)' mod='fidbag'}</label>
		<input type="text" size="30" name="fidbag_client_login" id="fidbag_client_login" value="{$fidbag_login|escape:'htmlall':'UTF-8'}"/><br/>
	</div>
	<div class="div_form_fidbag">
		<label>{l s='Fid\'Bag password' mod='fidbag'}</label>
		<input type="password" size="30" name="fidbag_client_password" id="fidbag_client_password" value="{$fidbag_password|escape:'htmlall':'UTF-8'}"/><br/>
	</div>
	<div class="div_form_fidbag submit-block">
		<input style="cursor:pointer;" name="submitSave" class="fidbag_button" type="submit" value="{l s='Log in' mod='fidbag'}" onclick="connectUserFidBag()" /><span id="fidbag_submit_connect_result"></span>
	</div>
</div>
<a id='fidbag_password_forget' onclick='passwordUserFidBag()'>{l s='Lost your password?' mod='fidbag'}</a> <span style="color:red" id="fidbag_password_result"></span>
