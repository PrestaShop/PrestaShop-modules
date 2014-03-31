$(function(){
	if($("#create-account_form").length > 0)
		$("#create-account_form").before('<div id="buttonPaypalLogin1"></div>');
	else
	{

		$("#login_form").before('<div id="buttonPaypalLogin1"></div>');
		$("#buttonPaypalLogin1").css({
			"clear"       : "both",	
			"margin-bottom" : "13px"
		});
	}

	$("#buttonPaypalLogin1").css({
		"clear"       : "both",	
	});

	paypal.use( ["login"], function(login) {
		login.render ({
			"appid": "{Configuration::get('PAYPAL_LOGIN_CLIENT_ID')}",
			{if Configuration::get('PAYPAL_SANDBOX') == 1} "authend" : "sandbox",{/if}
			"scopes": "openid profile email address phone https://uri.paypal.com/services/paypalattributes https://uri.paypal.com/services/expresscheckout",
			"containerid": "buttonPaypalLogin1",
			{if Configuration::get('PAYPAL_LOGIN_TPL') == 2} "theme" : "neutral", {/if}
			"returnurl": "{PayPalLogin::getReturnLink()}?{$page_name}",
			'locale' : '{$paypal_locale}',
		});
	});
});


