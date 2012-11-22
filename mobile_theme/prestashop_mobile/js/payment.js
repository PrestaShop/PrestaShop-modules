$('#jqm_page_order').live('pageshow', function() {
	
	$('#paypal_payment_form').prev().attr('rel', 'paypal');

	var found = false;
	$('#HOOK_PAYMENT').children().each(function() {
		if ($(this).attr('id') != 'paypal_payment_form' && $(this).attr('rel') != 'paypal')
			$(this).html('');
		else
			found = true;
	});
	if (!found) {
		$('#HOOK_PAYMENT').html(translate_nopaymentmodule);
		$('#jqm_page_order h4').html('');
	}
});
