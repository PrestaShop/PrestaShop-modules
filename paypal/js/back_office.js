$(document).ready( function() {
	var identificationButtonClicked = false;

	/* Display correct block according to different choices. */
	function displayConfiguration() {
		identificationButtonClicked = false;

		var paypal_business = $('input[name="business"]:checked').val();
		var paypal_payment_method = $('input[name="paypal_payment_method"]:checked').val();

		$('#signup span.paypal-signup-content').hide();
		$('#signup .paypal-signup-button').hide();

		switch (paypal_business) {
			case '0':
				$('#signup').slideDown();
				$('#account').removeClass('disabled');
				$('#credentials').addClass('disabled');
				$('input[type="submit"]').attr('disabled', 'disabled');

				switch (paypal_payment_method) {
					case PayPal_WPS:
						$('.toolbox').slideUp();
						$('#integral-credentials').slideUp();
						$('#standard-credentials').slideDown();
						$('#paypal-signup-button-u1').show();
						$('#paypal-signup-content-u1').show();
						$('#express_checkout_shortcut').slideDown();
						break;
					case PayPal_HSS:
						$('#signup').slideDown();
						$('#paypal-signup-button-u2').show();
						$('#paypal-signup-content-u2').show();
						$('#standard-credentials').slideUp();
						$('#account').removeClass('disabled');
						$('#standard-credentials').slideUp();
						$('#express_checkout_shortcut').slideUp();
						$('#integral-credentials').slideDown();
						$('label[for="paypal_payment_wpp"] .toolbox').slideDown();
						break;
					case PayPal_ECS:
						$('.toolbox').slideUp();
						$('#integral-credentials').slideUp();
						$('#standard-credentials').slideDown();
						$('#paypal-signup-button-u3').show();
						$('#paypal-signup-content-u3').show();
						$('#express_checkout_shortcut').slideDown();
						break;
				}
				break;
			case '1':
				$('#configuration').slideDown();
				$('#account').addClass('disabled');
				$('#credentials').removeClass('disabled');
				$('input[type="submit"]').removeAttr('disabled');

				switch (paypal_payment_method) {
					case PayPal_WPS:
						$('#signup').slideUp();
						$('#integral-credentials').slideUp();
						$('#standard-credentials').slideDown();
						$('#paypal-signup-button-u4').show();
						$('#express_checkout_shortcut').slideDown();
						break;
					case PayPal_HSS:
						$('#signup').slideDown();
						$('#paypal-signup-button-u5').show();
						$('#paypal-signup-content-u5').show();
						$('#account').removeClass('disabled');
						$('#standard-credentials').slideUp();
						$('#express_checkout_shortcut').slideUp();
						$('#integral-credentials').slideDown();
						$('label[for="paypal_payment_wpp"] .toolbox').slideDown();
						break;
					case PayPal_ECS:
						$('#signup').slideUp();
						$('#integral-credentials').slideUp();
						$('#standard-credentials').slideDown();
						$('#paypal-signup-button-u6').show();
						$('#express_checkout_shortcut').slideDown();
						break;
				}
				break;
		}

		displayCredentials();
		return;
	}

	if ($('#paypal-wrapper').length != 0) {
		$('.hide').hide();
		displayConfiguration();
	}

	if ($('input[name="paypal_payment_method"]').length == 1) {
		$('input[name="paypal_payment_method"]').attr('checked', 'checked');
	}

	function displayCredentials() {
		var paypal_business = $('input[name="business"]:checked').val();
		var paypal_payment_method = $('input[name="paypal_payment_method"]:checked').val();

		if (paypal_payment_method != PayPal_HSS &&
			($('input[name="api_username"]').val().length > 0 ||
			$('input[name="api_password"]').val().length > 0 ||
			$('input[name="api_signature"]').val().length > 0)) {
			$('#credentials').removeClass('disabled');
			$('#configuration').slideDown();
			$('input[type="submit"]').removeAttr('disabled');
			$('#standard-credentials').slideDown();
			$('#express_checkout_shortcut').slideDown();
			$('#integral-credentials').slideUp();
		}
		else if (paypal_payment_method == PayPal_HSS &&
			($('input[name="api_business_account"]').val().length > 0)) {
			$('#credentials').removeClass('disabled');
			$('#configuration').slideDown();
			$('input[type="submit"]').removeAttr('disabled');
			$('#standard-credentials').slideUp();
			$('#express_checkout_shortcut').slideUp();
			$('#integral-credentials').slideDown();
		}
		else if (paypal_business != 1) {
			$('#configuration').slideUp();
		}
	}

	$('input[name="business"], input[name="paypal_payment_method"]').live('change', function() {
		displayConfiguration();
	});

	$('label, a').live('mouseover', function() {
		$(this).children('.toolbox').show();
	}).live('mouseout', function() {
		var id = $(this).attr('for');
		var input = $('input#'+id);

		if (!input.is(':checked'))
			$(this).children('.toolbox').hide();
		if (($(this).attr('id') == 'paypal-get-identification') &&
			(identificationButtonClicked == false))
			$(this).children('.toolbox').hide();
	});

	$('a.paypal-signup-button, a#step3').live('click', function() {
		var paypal_business = $('input[name="business"]:checked').val();
		var paypal_payment_method = $('input[name="paypal_payment_method"]:checked').val();

		$('#credentials').removeClass('disabled');
		if ($(this).attr('id') != 'paypal-signup-button-u3')
			$('#account').addClass('disabled');

		$('#configuration').slideDown();
		if (paypal_payment_method == PayPal_HSS) {
			$('#standard-credentials').slideUp();
			$('#express_checkout_shortcut').slideUp();
			$('#integral-credentials').slideDown();
		} else {
			$('#standard-credentials').slideDown();
			$('#express_checkout_shortcut').slideDown();
			$('#integral-credentials').slideUp();
		}
		$('input[type="submit"]').removeAttr('disabled');

		if ($(this).is('#step3')) {
			return false;
		}
		return true;
	});

	if ($("#paypal-wrapper").length > 0) {
		$('input[type="submit"]').live('click', function() {
			var paypal_business = $('input[name="business"]:checked').val();
			var paypal_payment_method = $('input[name="paypal_payment_method"]:checked').val();

			if ((paypal_payment_method != PayPal_HSS &&
				(($('input[name="api_username"]').val().length <= 0) ||
				($('input[name="api_password"]').val().length <= 0) ||
				($('input[name="api_signature"]').val().length <= 0))) ||
				((paypal_payment_method == PayPal_HSS &&
				($('input[name="api_business_account"]').val().length <= 0)))) {
				$.fancybox({'content' : $('<div id="js-paypal-save-failure">').append($('#js-paypal-save-failure').clone().html())});
				return false;
			}
			return true;
		});

		$('input[name="sandbox_mode"]').live('change', function() {
			if ($('input[name="sandbox_mode"]:checked').val() == '1') {
				$('input[name="sandbox_mode"]').filter('[value="0"]').attr('checked', true);
				var div = $('<div id="paypal-test-mode-confirmation">');
				var inner = $('#paypal-test-mode-confirmation').clone().html();
				$.fancybox({'hideOnOverlayClick' : true, 'content' : div.append(inner)});
				return false;
			}
			return true;
		});

		$('button.fancy_confirm').live('click', function() {
			jQuery.fancybox.close();
			if ($(this).val() == '1') {
				$('input[name="sandbox_mode"]').filter('[value="1"]').attr('checked', true);
			} else {
				$('input[name="sandbox_mode"]').filter('[value="0"]').attr('checked', true);
			}
		});

		if ($('#paypal-save-success').length > 0)
			$.fancybox({'hideOnOverlayClick' : true, 'content' : $('<div id="paypal-save-success">').append($('#paypal-save-success').clone().html())});
		else if ($('#paypal-save-failure').length > 0)
			$.fancybox({'hideOnOverlayClick' : true, 'content' : $('<div id="paypal-save-failure">').append($('#paypal-save-failure').clone().html())});

		$('#paypal-get-identification').live('click', function() {
			identificationButtonClicked = true;
			var url = 'https://www.paypal.com/us/cgi-bin/webscr?cmd=_get-api-signature&generic-flow=true';
			var title = 'PayPal identification informations';
			window.open (url, title, config='height=500, width=360, toolbar=no, menubar=no, scrollbars=no, resizable=no, location=no, directories=no, status=no');
			return false;
		});

		$('a#paypal_country_change').live('click', function() {
			var div = $('<div id="paypal-country-form">');
			var inner = $('#paypal-country-form-content').clone().html();
			$.fancybox({'content' : div.append(inner)});
			return false;
		});

		$('#paypal_country_default').live('change', function() {
			var form = $('#paypal_configuration');
			form.append('<input type="hidden" name="paypal_country_only" value="'+$(this).val()+'" />');
			form.submit();
		});
	}

});
