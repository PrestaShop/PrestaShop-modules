/*
 * 2007-2014 PrestaShop
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
 *  @copyright  2007-2014 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
$(function() {

	$('input[name="pickup_time_from"]').timePicker({
			startTime: '08:00',
			endTime:  new Date(0, 0, 0, 13, 00, 0),
			separator: ':',
			step: 30
	});
	$('input[name="pickup_time_to"]').timePicker({
			startTime: '13:00',
			endTime:  new Date(0, 0, 0, 18, 00, 0),
			separator: ':',
			step: 30
	});
	$('input[name="parcel_content"]').contentPicker({});

	// Pseudo constans
	var btn_pricing = $('#btn_pricing');
	var div_addons = $('div.gk-addons');
	var div_datetime = $('div.gk-datetime');
	var div_payment = $('div.gk-payment');
	var div_process = $('div.gk-process');
	var url_product = 'http://www.globkurier.pl/wycen.php';
	var url_addons = 'http://www.globkurier.pl/producent_dodatki.php';

	// PRODUCTS - click
	btn_pricing.click(function(e) {
		e.preventDefault();
		// Get fresh values
		var number_of_parcels = $('input[name="number_of_parcels"]').val();
		var length = $('input[name="parcel_lenght"]').val();
		var width = $('input[name="parcel_width"]').val();
		var height = $('input[name="parcel_height"]').val();
		var weight = $('input[name="parcel_weight"]').val();
		var client_id = $('input[name="client_id"]').val();
		var count = $('input[name="parcel_count"]').val();
		var country_from = $('select[name="recipient_country"] option:selected').attr('country');
		var country_to = $('select[name="sender_country"] option:selected').attr('country');

		// Set data Domestic
		if (country_from == 'PL' && country_to == 'PL') {
			var parcel_data = {
				length : length,
				width : width,
				height : height,
				weight : weight,
				client_id : client_id,
				count : count
			};
		}
		// Set data Internaional import
		if (country_from == 'PL' && country_to != 'PL') {
			var parcel_data = {
				length : length,
				width : width,
				height : height,
				weight : weight,
				client_id : client_id,
				count : count,
				exp : country_to
			};
		}
		// Set data International export
		if (country_from != 'PL' && country_to == 'PL') {
			var parcel_data = {
				length : length,
				width : width,
				height : height,
				weight : weight,
				client_id : client_id,
				count : count,
				imp : country_from
			};
		}
		// Set data international over 2 diff countries
		if (country_from != 'PL' && country_to != 'PL') {
			var parcel_data = {
				length : length,
				width : width,
				height : height,
				weight : weight,
				client_id : client_id,
				count : count,
				imp : country_from,
				exp : country_to
			};
		}
		getProducts(parcel_data);
		div_addons.hide();
	});

	// PRODUCT <-> ADDONS
	$('div.product-box').live("change", function() {
		$('.product-box').removeClass("dropShadow");
		$(this).addClass("dropShadow");
		var base_service = $('input[name="base_service"]:checked').val();
		var nst_id = $('input[name="base_service"]:checked').attr('nstd');
		var client_id = $('input[name="client_id"]').val();
		var addons_data = {
			symbol : base_service,
			client_id : client_id
		};
		getAddons(addons_data, nst_id);
	});

	// ADDONS - click
	$('.addons-checkbox').live("click", function() {
		switch($(this).attr('category')) {
			case 'COD' :
				if ($(this).is(':checked')) {
					$(this).parent().parent().after(makeCodBox());
					$('.insurance-input').remove();
					$('.addons-checkbox').each(function() {
						if ($(this).attr('category') == 'UBEZPIECZENIE') {
							$(this).attr('checked', 'checked');
							return false;
						}
					});
					$('.addons-checkbox').each(function() {
						if ($(this).attr('category') == 'COD' || $(this).attr('category') == 'UBEZPIECZENIE') {
							if ($(this).is(':checked')) {
								$(this).removeAttr("disabled");
							} else {
								$(this).attr('disabled', true);
							}
						}
					});
				} else {
					$('.cod-input').remove();
					$('.insurance-input').remove();
					$('.addons-checkbox').each(function() {
						if ($(this).attr('category') == 'UBEZPIECZENIE') {
							$(this).attr('checked', false);
						}
					});
					$('.addons-checkbox').each(function() {
						if ($(this).attr('category') == 'COD' || $(this).attr('category') == 'UBEZPIECZENIE') {
							$(this).removeAttr("disabled");
						}
					});
				}
				break;
			case 'UBEZPIECZENIE' :
				if ($(this).is(':checked')) {
					$(this).parent().parent().after(makeInsuranceBox());
					$('.addons-checkbox').each(function() {
						if ($(this).attr('category') == 'UBEZPIECZENIE') {
							if ($(this).is(':checked')) {
								$(this).removeAttr("disabled");
							} else {
								$(this).attr('disabled', true);
							}
						}
					});
				} else {
					$('.cod-input').remove();
					$('.insurance-input').remove();
					$('.addons-checkbox').each(function() {
						if ($(this).attr('category') == 'COD') {
							$(this).attr('checked', false);
						}
					});
					$('.addons-checkbox').each(function() {
						if ($(this).attr('category') == 'COD' || $(this).attr('category') == 'UBEZPIECZENIE') {
							$(this).removeAttr("disabled");
						}
					});
				}
				break;
		}
	});

	$('.gk-order-payment-on').live("click", function() {
		$('.gk-order-payment-on').removeClass("dropShadow");
		$(this).addClass("dropShadow");
		$('div.gk-process').fadeIn('fast', 'swing');
	});

	/**
	 * Given product
	 *
	 * @param {Object}
	 * @return void
	 */
	function getProducts(data) {
		$.ajax({
			url : url_product,
			type : "POST",
			cache : false,
			data : data,
			beforeSend : function() {
				$('div.product-loading').show();
			},
			success : function(json) {
				if (json) {
					$('div.product-loading').hide();
					$('div.gk-message-box').html('');
					$('div.gk-products').html('');
					if (json) {
						var json = $.parseJSON(json);
						if (json.errors) {
							for (i in json.errors) {
								$('div.gk-message-box').append(makeWarningBox(json.errors[i]));
							}
						} else {
							$('div.gk-message-box').html('');
							for (i in json.products) {
								$('div.gk-products').append(makeProductBox(json.products[i]));
							}
						}
					}
				}
			},
			error : function(jqXHR, error, errorThrown, response) {
				$('div.product-loading').hide();
				if (jqXHR.status && jqXHR.status == 400) {
					console.log(jqXHR.responseText);
				} else {
					var r = jQuery.parseJSON(response.responseText);
					console.log("Message: " + r.Message);
					console.log("StackTrace: " + r.StackTrace);
					console.log("ExceptionType: " + r.ExceptionType);
				}
			}
		});
	}

	/**
	 * Given addons list
	 *
	 * @param {Object}
	 * @param serialized array addons_services
	 * @return void
	 */
	function getAddons(data, addons_services, nstd) {
		// Get addons
		div_addons.show();
		div_datetime.show();
		div_payment.show();
		var import_kraj = $('select[name="recipient_country"] option:selected').attr('country');
		var export_kraj = $('select[name="sender_country"] option:selected').attr('country');
		if (import_kraj == 'PL' && export_kraj == 'PL') {
			$.ajax({
				type : 'POST',
				url : url_addons,
				cache : false,
				data : data,
				beforeSend : function() {
					$('div.addons-loading').show();
				},
				success : function(json) {
					if (json) {
						$('div.addons-loading').hide();
						$('div.gk-addons-message-box').html('');
						$('div.gk-addons-message-box').hide();
						$('div.gk-addons-domestic').html('');
						$('div.gk-addons-international').html('');
						var json = $.parseJSON(json);
						if (json.errors) {
							for (i in json.errors) {
								$('div.message-box-addons').append(makeWarningBox(json.errors[i]));
							}
						} else {
							for (i in json.addons) {
								$('div.gk-addons-domestic').append(makeDomesticAddonsBox(json.addons[i], nstd));
							}
						}
					}
				}
			});
		} else {
			// Clear data
			$('div.addons-loading').hide();
			$('div.gk-addons-message-box').html('');
			$('div.gk-addons-message-box').hide();
			$('div.gk-addons-domestic').html('');
			$('div.gk-addons-international').html('');
			// Insert hardcode addons
			$('div.gk-addons-international').append(makeInternationalAddonsBox());
		}
	}

	/**
	 * Create product box
	 *
	 * @param {Object} json
	 * @param string base_service
	 * @return string html;
	 */
	function makeProductBox(json) {
		var box = '';
		box += '<label class="gk-product">';
		box += '<div class="product-box">';
		box += '<p class="name">' + json.product + '</p>';
		if (json.no_standard > 0) {
			box += '<p class="price_net">' + json.price_net + ' PLN (niestandard: ' + json.no_standard + 'PLN)</p>';
		} else {
			box += '<p class="price_net">' + json.price_net + ' PLN</p>';
		}
		if (json.area == 'domestic') {
			box += '<p class="img"><img src="../modules/globkurier/img/carriers/' + json.courier + '.png" /></p>';
		} else {
			switch(json.service) {
				case 'AH' :
					serwis = "priorytetowy";
					break;
				case 'ES' :
					serwis = "ekonomiczny";
					break;
			}
			box += '<p class="img">serwis: ' + serwis + '</p>';
			box += '<p class="img"><img src="../modules/globkurier/img/carriers/Globkurier.png" /></p>';
		}
		box += '<p class="input"><input type="radio" name="base_service" nstd="' + json.nst_id + '" value="' + json.symbol + '"/></p>';
		box += '</div>';
		box += '</label>';
		return box;
	}

	/**
	 * Create addons box
	 * @param {Object} json
	 * @param string addons_services
	 * @return string html;
	 */
	function makeDomesticAddonsBox(json, nstd) {
		var nstd_box = '';
		if (nstd == json.symbol) {
			disabled = 'disabled checked';
			nstd_box = '<input type="hidden" name="additional_services[]" value="' + json.symbol + '" />';
		} else {
			if (json.kategoria == 'NST')
				disabled = 'disabled';
			else
				disabled = '';
		}
		var box = '';
		box += '<div class="addons-box addons-' + json.kategoria + '">';
		box += '<label class="gk-addons">';
		box += '<p class="input"><input class="addons-checkbox" category="' + json.kategoria + '" type="checkbox" name="additional_services[]" value="' + json.symbol + '" ' + disabled + '/></p>';
		// Warning HARDCODE
		if (json.symbol == 'A607') {
			box += '<p class="name">' + json.nazwa + '<span class="price">' + parseFloat(json.cena * 10) + '% wartości ubezpieczenia</span></p>';
		} else {
			box += '<p class="name">' + json.nazwa + '<span class="price">' + json.cena + 'PLN</span></p>';
		}
		box += nstd_box;
		box += '</label>';
		box += '</div>';
		return box;
	}

	/**
	 * Create addons box
	 * @param void
	 * @return string html;
	 */
	function makeInternationalAddonsBox() {
		var box = '';
		box += '<div class="wartosc-deklarowana">';
		box += '<p class="dodatek">';
		box += '<label>Wartość deklarowana (pln)<span>*</span></label><br />';
		box += '<input class="long-input" type="text" name="declared_value" placeholder="Wartość deklarowana (pln)" />';
		box += '</p>';
		box += '</div>';
		return box;
	}

	/**
	 *Create warning box
	 * @param {Object} json
	 * @return string html;
	 */
	function makeWarningBox(json) {
		var box = '';
		box += '<div class="error">';
		box += json;
		box += '</div>';
		return box;
	}

	/**
	 * Create cod input's box
	 *
	 * @return string html;
	 */
	function makeCodBox() {
		var cod_amount_tmp = $('input[name="cod_amount_tmp"]').val();
		var cod_account_number_tmp = $('input[name="cod_account_number_tmp"]').val();
		var insurance_amount_tmp = $('input[name="insurance_amount_tmp"]').val();
		var box = '';
		box += '<p class="cod-input">';
		box += '<label>';
		box += 'Kwota pobrania';
		box += '<span>*</span>';
		box += '</label><br/>';
		box += '<input class="large-input" type="text" name="cod_amount" value="' + cod_amount_tmp + '" placeholder="Kwota pobrania"/>';
		box += '</p>';
		box += '<p class="cod-input">';
		box += '<label>';
		box += 'Kwota ubezpieczenia';
		box += '<span>*</span>';
		box += '</label><br/>';
		box += '<input class="large-input" type="text" name="insurance_amount" value="' + insurance_amount_tmp + '" placeholder="Kwota ubezpieczenia" />';
		box += '</p>';
		box += '<p class="cod-input">';
		box += '<label>';
		box += 'Nr konta';
		box += '<span>*</span>';
		box += '</label><br/>';
		box += '<input class="large-input" type="text" name="cod_account_number" value="' + cod_account_number_tmp + '" placeholder="Nr konta"/>';
		box += '</p>';
		return box;
	}

	/**
	 * Create insurance input box
	 *
	 * @return string html;
	 */
	function makeInsuranceBox() {
		var insurance_amount_tmp = $('input[name="insurance_amount_tmp"]').val();
		var box = '';
		box += '<p class="insurance-input">';
		box += '<label>';
		box += 'Kwota ubezpieczenia';
		box += '<span>*</span>';
		box += '</label><br/>';
		box += '<input class="large-input" type="text" name="insurance_amount" value="' + insurance_amount_tmp + '" placeholder="Kwota ubezpieczenia" />';
		box += '</p>';
		return box;
	}
});
