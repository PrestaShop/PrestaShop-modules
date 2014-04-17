$(function(){ldelim}
	if($("#create-account_form").length > 0)
		$("#create-account_form").before('<div id="buttonPaypalLogin1"></div>');
	else
	{ldelim}

		$("#login_form").before('<div id="buttonPaypalLogin1"></div>');
		$("#buttonPaypalLogin1").css({ldelim}
			"clear"       : "both",	
			"margin-bottom" : "13px"
		{rdelim});
	{rdelim}

	$("#buttonPaypalLogin1").css({ldelim}
		"clear"       : "both",
		'margin-bottom' : '10px',	
	{rdelim});

	paypal.use( ["login"], function(login) {ldelim}
		login.render ({ldelim}
			"appid": "{$PAYPAL_LOGIN_CLIENT_ID}",
			{if $PAYPAL_SANDBOX == 1} "authend" : "sandbox",{/if}
			"scopes": "openid profile email address phone https://uri.paypal.com/services/paypalattributes https://uri.paypal.com/services/expresscheckout",
			"containerid": "buttonPaypalLogin1",
			{if $PAYPAL_LOGIN_TPL == 2} "theme" : "neutral", {/if}
			"returnurl": "{$PAYPAL_RETURN_LINK}?{$page_name}",
			'locale' : '{$paypal_locale}',
		{rdelim});
	{rdelim});
{rdelim});


