/*
 * 2007-2013 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2013 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

// Validate order form
$(function() {
	// Constance
	var UPS = 7;
	var UPS_COD_PERCENT = 0.4;

	// Main service
	$('.gk-order-products').click(
			function() {
				$('.gk-order-products').removeClass("dropShadow");
				$(this).addClass("dropShadow");
				//
				if ($(this).attr('producent') == UPS) {
					$('span.B002').show();
					$('span.B002').text(
							"+" + UPS_COD_PERCENT + "% wartności pobrania.");
					$('span.B003').show();
					$('span.B003').text(
							"+" + UPS_COD_PERCENT + "% wartności pobrania.");
				} else {
					$('span.B002').hide();
					$('span.B002').text("");
					$('span.B003').hide();
					$('span.B003').text("");
				}
				$('.addons_all').fadeIn('slow', 'swing');
			});
	// Addons ?
	$('input[name="gk-addons"]').click(function() {
		if ($('input[name="gk-addons"]:checked').val() == 1) {
			$('.check_addons').fadeIn('slow', 'swing');
			$('.gk-process').hide();
			$('.gk-order-payment-on').removeClass("dropShadow");
			$('input[name="payment"]').removeAttr("checked");
			$('.payment_all').hide('fast');
		} else {
			$('.check_addons').hide('fast');
			$('.payment_all').fadeIn('slow', 'swing');
			$('input[name="B001"]').removeAttr("checked");
			$('input[name="B002"]').removeAttr("checked");
			$('input[name="B003"]').removeAttr("checked");
			$('.cod').hide();
			$('.insurance').hide();
		}
	});
	// Check addons
	// insurance
	$('input[name="B001"]').click(function() {
		if ($('input[name="B002"]').attr('checked')) {
			$('input[name="B002"]').removeAttr("checked");
		}
		if ($('input[name="B003"]').attr('checked')) {
			$('input[name="B003"]').removeAttr("checked");
		}
		// input
		if ($('input[name="B001"]').attr('checked')) {
			$('.insurance').fadeIn('fast', 'swing');
		} else {
			$('.insurance').fadeOut('fast', 'swing');
			$('.cod').fadeOut('fast', 'swing');
			$('input[name="B002"]').removeAttr("checked");
			$('input[name="B003"]').removeAttr("checked");
		}
	});
	// cod
	$('input[name="B002"]').click(function() {
		$('input[name="B001"]').attr('checked', true);
		if ($('input[name="B003"]').attr('checked')) {
			$('input[name="B003"]').removeAttr("checked");
		}
		// input
		if ($('input[name="B002"]').attr('checked')) {
			$('.cod').fadeIn('fast', 'swing');
			$('.insurance').fadeIn('fast', 'swing');
		} else {
			$('.cod').fadeOut('fast', 'swing');
			$('.insurance').fadeOut('fast', 'swing');
			$('input[name="B001"]').removeAttr("checked");
		}
	});
	// cod3
	$('input[name="B003"]').click(function() {
		$('input[name="B001"]').attr('checked', true);
		if ($('input[name="B002"]').attr('checked')) {
			$('input[name="B002"]').removeAttr("checked");
			$('.cod').fadeIn('slow', 'swing');
		}
		// input
		if ($('input[name="B003"]').attr('checked')) {
			$('.cod').fadeIn('fast', 'swing');
			$('.insurance').fadeIn('fast', 'swing');
		} else {
			$('.cod').fadeOut('fast', 'swing');
			$('.insurance').fadeOut('fast', 'swing');
			$('input[name="B001"]').removeAttr("checked");
		}
	});

	// Check value addons
	$('span.gk-next-local')
			.click(
					function() {
						var next = true;
						// insurance
						if ($('input[name="B001"]').attr('checked')) {
							$insurance_val = $('input[name="insurance-value"]')
									.val();
							$insurance_val = $insurance_val.replace(/,/g, '.');
							if ($insurance_val > 0 && $insurance_val < 500001) {
								var next = true;
								$('span.input-warning-insurance-value')
										.html('');
							} else {
								var next = false;
								$('span.input-warning-insurance-value')
										.html(
												'<span class="warningBox">Uzupełnij prawidłową kwotę ubezpieczenia.</span>');
							}

						}
						// cod / cod 3
						if ($('input[name="B002"]').attr('checked')
								|| $('input[name="B003"]').attr('checked')) {
							$cod_val = $('input[name="cod-value"]').val();
							$cod_val = $cod_val.replace(/,/g, '.');
							if ($cod_val > 0 && $cod_val < 10001) {
								var next = true;
								$('span.input-warning-cod-value').html('');
							} else {
								var next = false;
								$('span.input-warning-cod-value')
										.html(
												'<span class="warningBox">Uzupełnij prawidłową kwotę pobrania.</span>');
							}

							if (isValidIBAN($('input[name="cod-account"]')
									.val())) {
								var next = true;
								$('span.input-warning-cod-account').html('');
							} else {
								var next = false;
								$('span.input-warning-cod-account')
										.html(
												'<span class="warningBox">Wpisz prawidłowy numer konta.</span>');
							}
						}
						if (next) {
							$('.payment_all').fadeIn('slow', 'swing');
						} else {
							$('.payment_all').fadeOut('slow', 'swing')
						}

					});

	$('span.gk-next-mnd')
			.click(
					function() {
						var next = true;
						if ($('input[name="declared-value"]').val() > 0) {
							var next = true;
							$('span.input-warning-declared-value').html('');
						} else {
							var next = false;
							$('span.input-warning-declared-value')
									.html(
											'<span class="warningBox">Uzupełnij wartość deklarowaną.</span>');
						}
						if (next) {
							$('.payment_all').fadeIn('slow', 'swing');
						} else {
							$('.payment_all').fadeOut('slow', 'swing')
						}
					});

	$('.gk-order-payment-on').click(function() {
		$('.gk-order-payment-on').removeClass("dropShadow");
		$(this).addClass("dropShadow");
		$('.gk-process').fadeIn('slow', 'swing');
	});

	$('.show_more_products').click(function() {
		$('.product_timely').fadeIn('slow', 'swing');
		$('div.show_btn').hide();
	});

	// Register - Account Type
	if ($('input[name="gk_type"]:checked').val() == 1) {
		$('.gk-box-company').show('fast');
	} else {
		$('.gk-box-company').hide('fast');
	}
	$('input[name="gk_type"]').click(function() {
		if ($('input[name="gk_type"]:checked').val() == 1) {
			$('.gk-box-company').show('fast');
		} else {
			$('.gk-box-company').hide('fast');
		}
	});

	// Validate IBAN
	function isValidIBAN($v) { // This function check if the checksum if
								// correct
		$v = $v.replace(/-/g, '');
		$v = $v.replace(/ /g, '');
		$v = 'PL' + $v;
		$v = $v.replace(/^(.{4})(.*)$/, "$2$1"); //Move the first 4 chars from left to the right
		$v = $v.replace(/[A-Z]/g, function($e) {
			return $e.charCodeAt(0) - 'A'.charCodeAt(0) + 10
		}); //Convert A-Z to 10-25
		var $sum = 0;
		var $ei = 1; //First exponent 
		for ( var $i = $v.length - 1; $i >= 0; $i--) {
			$sum += $ei * parseInt($v.charAt($i), 10); //multiply the digit by it's exponent 
			$ei = ($ei * 10) % 97; //compute next base 10 exponent  in modulus 97
		}
		;
		return $sum % 97 == 1;
	}

});
