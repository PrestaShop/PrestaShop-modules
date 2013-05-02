<script type="text/javascript">
{literal}

$(document).ready(function(){
	if($("#fidbag_subs_cart").attr('checked'))
		$("#div_form_fidbag_card_number").show();
	else
		$("#div_form_fidbag_card_number").hide();
	$("#fidbag_subs_cart").click(function(){
		if ($(this).attr('checked'))
			$("#div_form_fidbag_card_number").show();
		else
		{
			$("#fidbag_subs_card_number").val('');
			$("#div_form_fidbag_card_number").hide();
		}
	});
});

function subscribeUserFidBag()
{
	if ($("#fidbag_subs_password").val() != $("#fidbag_subs_repassword").val() || $("#fidbag_subs_password").val() == '')
	{
		$("#fidbag_submit_subscription_result").html('<span style="color:red">{/literal}{l s='Passwords are required and must be the same' mod='fidbag'}{literal}</span>');
		return false;
	}
	$.ajax({
		url: mainUrl+"/modules/fidbag/subscription.php",
		type: "POST",
		data: {
			Civility : $("#fidbag_subs_civility").val(),
			LastName : $("#fidbag_subs_last_name").val(),
			FirstName : $("#fidbag_subs_first_name").val(),
			Email : $("#fidbag_subs_Email").val(),
			Address : $("#fidbag_subs_address").val(),
			ZipCode : $("#fidbag_subs_zip_code").val(),
			City : $("#fidbag_subs_city").val(),
			Password : $("#fidbag_subs_password").val(),
			FidcardNumber : $("#fidbag_subs_card_number").val(),
			LanguageCode : $("#fidbag_subs_language_code").val(),
			customer : customer,
			token: "{/literal}{$fidbag_token|escape:'htmlall':'UTF-8'}{literal}",
		},
		dataType: "json",
		success: function(data)
				{
					if ((data.returnInfos != undefined) && (data.returnInfos.mCode == 0)) {
						$('#fidbag_menuTab2').trigger('click');
						$("#fidbag_client_login").val($("#fidbag_subs_Email").val());
						$("#fidbag_client_password").val($("#fidbag_subs_password").val());
						$("input[name=submitSave]").trigger('click');
					} else if (data == '0') {
						$("#fidbag_submit_subscription_result").html("{/literal}{l s='Technical error, please try later' mod='fidbag'}{literal}");
					} else {
						$("#fidbag_submit_subscription_result").html('<span style="color:red">'+data.returnInfos.mMessage+'</span>');
					}
				},
		error: function(er)
		{
			$("#fidbag_submit_subscription_result").html("{/literal}{l s='Technical error, please try later' mod='fidbag'}{literal}");
		}
	});

}
{/literal}
</script>


<div id="fidbag-subscription-form">
	<h4>{l s='No account, subscribe now' mod='fidbag'}</h4><br/>
	<div class="div_form_fidbag">
		<label>{l s='Gender' mod='fidbag'} </label>
		<select name="fidbag_subs_civility" id="fidbag_subs_civility" />
			<option value="1" {if isset($sub_gender) && $sub_gender == 2}selected='selected'{/if}>{l s='Ms.' mod='fidbag'}</option>
			<option value="3" {if isset($sub_gender) && $sub_gender == 1}selected='selected'{/if}>{l s='Mr.' mod='fidbag'}</option>
			<option value="2" {if isset($sub_gender) && $sub_gender == 3}selected='selected'{/if}>{l s='Miss' mod='fidbag'}</option>
		</select><br/>
	</div>
	
	<div class="div_form_fidbag">
		<label>{l s='Last name' mod='fidbag'} </label>
		<input type="text" size="30" name="fidbag_subs_last_name" id="fidbag_subs_last_name" value="{$sub_lastname|escape:'htmlall':'UTF-8'}"/>
	</div>
	
	<div class="div_form_fidbag">
		<label>{l s='First name' mod='fidbag'} </label>
		<input type="text" size="30" name="fidbag_subs_first_name" id="fidbag_subs_first_name" value="{$sub_firstname|escape:'htmlall':'UTF-8'}"/>
	</div>
	
	<div class="div_form_fidbag">
		<label>{l s='Email (Fid\'Bag account)' mod='fidbag'} </label>
		<input type="text" size="30" name="fidbag_subs_Email" id="fidbag_subs_Email" value="{$sub_email|escape:'htmlall':'UTF-8'}"/>
	</div>
	
	<div class="div_form_fidbag">
		<label>{l s='Address' mod='fidbag'} </label>
		<input type="text" size="30" name="fidbag_subs_address" id="fidbag_subs_address" value="{$sub_address|escape:'htmlall':'UTF-8'}"/>
	</div>
	
	<div class="div_form_fidbag">
		<label>{l s='Zip code' mod='fidbag'} </label>
		<input type="text" size="6" name="fidbag_subs_zip_code" id="fidbag_subs_zip_code" value="{$sub_zipcode|escape:'htmlall':'UTF-8'}"/>
	</div>
	
	<div class="div_form_fidbag">
		<label>{l s='City' mod='fidbag'} </label>
		<input type="text" size="20" name="fidbag_subs_city" id="fidbag_subs_city" value="{$sub_city|escape:'htmlall':'UTF-8'}"/>
	</div>
	
	<div class="div_form_fidbag">
		<label>{l s='Fid\'Bag password' mod='fidbag'} </label>
		<input type="password" size="20" name="fidbag_subs_password" id="fidbag_subs_password"/>
	</div>
	
	<div class="div_form_fidbag">
		<label>{l s='Enter password confirmation' mod='fidbag'} </label>
		<input type="password" size="20" name="fidbag_subs_password" id="fidbag_subs_repassword"/>
	</div>
	
	<div class="div_form_fidbag">
		<label>{l s='Already got a Fid\'card?' mod='fidbag'} </label>
		<input type="checkbox" name="fidbag_subs_cart" id="fidbag_subs_cart"/>
	</div>
	
	<div id="div_form_fidbag_card_number" style="display:none">
		<div class="div_form_fidbag">
		<label>{l s='Enter the card number below' mod='fidbag'} </label>
			<input type="text" size="20" name="fidbag_subs_card_number" id="fidbag_subs_card_number"/>
		</div>
	</div>
	
	<input type="hidden" name="fidbag_subs_language_code" value="fr-FR" id="fidbag_subs_language_code"/>
	
	<div class="div_form_fidbag submit-block">
		<input name="submitSave" type="submit" class="fidbag_button" value="{l s='Create you account' mod='fidbag'}" onclick="subscribeUserFidBag()"/><br /><span id="fidbag_submit_subscription_result"></span>
	</div>
</div>
