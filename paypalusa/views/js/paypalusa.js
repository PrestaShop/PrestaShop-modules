$(document).ready(function()
{
	$('.colorSelector').each(function() {
		var obj = $(this);
		obj.css('background-color', obj.val());
		$(this).ColorPicker({
			color: '#8f478f',
			onShow: function(colpkr) {
				$(colpkr).fadeIn(500);
				return false;
			},
			onHide: function(colpkr) {
				$(colpkr).fadeOut(500);
				return false;
			},
			onChange: function(hsb, hex, rgb) {
				obj.val('#' + hex);
				obj.css('background-color', '#' + hex);
			}
		});
	});

	var height = 0;
	$('.fixCol').each(function() {
		if (height < $(this).height())
			height = $(this).height();
	});

	$('.fixCol').css({'height': $('.fixCol').css('height', height + 40 + 'px')});

	$('.paypal-usa-threecol input:radio, .paypal-usa-onecol input:checkbox').live('click', function() {

		if ($(this).is(':checked')) {
			if ($(this).is('#paypal_usa_payment_advanced, #paypal_usa_payflow_link')) {
				$('.paypal-usa-product').removeClass('paypal-usa-product-active');
				$('#paypal-usa-advanced-settings').parent('form').fadeIn(500);
			} else
				$('#paypal-usa-advanced-settings').parent('form').fadeOut(500);

			if ($(this).is('#paypal_usa_express_checkout')) {
				$('#paypal_usa_express_checkout_config').fadeIn(500);
			} else
				$('#paypal_usa_express_checkout_config').fadeOut(500);

			$(this).parent().parent().addClass('paypal-usa-product-active');
		} else {
			$(this).parent().parent().removeClass('paypal-usa-product-active');
			$('#paypal-usa-advanced-settings').fadeOut(500);
			$('#paypal_usa_express_checkout_config').fadeOut(500);
		}
	});
	
	$('fieldset input:button').live('click', function() {
		$('input[name=paypal_usa_products]').prop('checked', false);
		$('#paypal_usa_express_checkout').prop('checked', true);
		$('.paypal-usa-product').removeClass('paypal-usa-product-active');
		$('#paypal_usa_express_checkout').parent().parent().addClass('paypal-usa-product-active');
		$('#paypal_usa_express_checkout_config').fadeIn(500);
		$('#paypal-usa-advanced-settings').parent('form').fadeOut(500);
	});
})