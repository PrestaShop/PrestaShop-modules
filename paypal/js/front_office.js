{literal}

$(document).ready( function() {

	$('#payment_paypal_express_checkout').click(function() {
		var nb = $('#quantity_wanted').val();
		var id = $('#idCombination').val();

		$('#paypal_payment_form input[name=quantity]').val(nb);
		$('#paypal_payment_form input[name=id_p_attr]').val(id);
		$('#paypal_payment_form').submit();
	});

	function displayExpressCheckoutShortcut() {
		var id_product = $('input[name="id_product"]').val();
		var id_product_attribute = $('input[name="id_product_attribute"]').val();

		$.ajax({
			type: "GET",
			url: baseDir+'/modules/paypal/express_checkout/submit.php',
			data: { get_qty: "1", id_product: id_product, id_product_attribute: id_product_attribute}
		}).success(function(result) {
			if (result == '1')
				$('#container_express_checkout').slideDown();
			else
				$('#container_express_checkout').slideUp();
			return true;
		});
	}

	$('select[name^="group_"]').change(function () {
		displayExpressCheckoutShortcut();
	});

	$('.color_pick').click(function () {
		displayExpressCheckoutShortcut();
	});
	
	{/literal}
	{if isset($paypal_authorization)}
	{literal}
	$('#container_express_checkout').hide();
	
	$('#cgv').click(function() {
		$(location).attr('href', '{/literal}{$paypal_authorization}{literal}');
	});
	{/literal}
	{/if}
	{literal}
	
	if ($('form[target="hss_iframe"]').length == 0) {
		if ($('select[name^="group_"]').length > 0)
			displayExpressCheckoutShortcut();
		return false;
	} else {
		var hostname = 'http://' + window.location.hostname + '{/literal}{$base_uri}{literal}';
		var modulePath = 'modules/paypal';
		var subFolder = '/integral_evolution';
		var fullPath = hostname + modulePath + subFolder;

		var confirmTimer = setInterval(getOrdersCount, 1000);
	}

	function getOrdersCount() {
		$.get(
			fullPath + '/confirm.php',
			{ id_cart: '{/literal}{$id_cart}{literal}' },
			function (data) {
				if (data && (data > 0)) {
					clearInterval(confirmTimer);
					window.location.replace(fullPath + '/submit.php?id_cart={/literal}{$id_cart}{literal}');
					$('p.payment_module, p.cart_navigation').hide();
				}
			}
		);
	}
});

{/literal}
