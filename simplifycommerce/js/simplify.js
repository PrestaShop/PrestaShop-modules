/**
 * Copyright (c) 2014, MasterCard International Incorporated
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are
 * permitted provided that the following conditions are met:
 *
 * Redistributions of source code must retain the above copyright notice, this list of
 * conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list of
 * conditions and the following disclaimer in the documentation and/or other materials
 * provided with the distribution.
 * Neither the name of the MasterCard International Incorporated nor the names of its
 * contributors may be used to endorse or promote products derived from this software
 * without specific prior written permission.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT
 * SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
 * TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
 * OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER
 * IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING
 * IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
 * SUCH DAMAGE.
 */

/**
 * Function to handle the form submission
 */
$(document).ready(function() {

	if ($('.simplify-payment-errors').text().length > 0) {
		$('.simplify-payment-errors').show();
	}

	// Check that the Simplify API Keys are set 
	if (window.simplifyPublicKey == undefined || window.simplifyPublicKey.length == 0) {
		$('#simplify-no-keys-msg').show();
		$('.simplify-submit-button').attr('disabled', 'disabled');
		return;
	}

	// Display warning message that this is a test payment as test api keys are being used.
	if (window.simplifyPublicKey.indexOf('sbpb_') !== -1) {
		$('#simplify-test-mode-msg').show();
	}

	$(".simplify-card-cvc").restrictNumeric();
	$('.simplify-card-number').formatCardNumber();

	/**
	 *  Function to watch the form of payment being used and
	 *  to show and hide the relevant form components.
	 */
	$("input[name='cc-type']").change(function() {
		var ccDetails = $("#simplify-cc-details");
		$('.card-type-container').removeClass('selected');
		$(this).parents('.card-type-container').addClass('selected');

		if ($("input[name='cc-type']:checked").val() == 'new') {
			if ($("#cc-deletion-msg").is(':visible')) {
				showSaveCardDetailsLabel(true);
			} else {
				showSaveCardDetailsLabel(false);
			}

			ccDetails.fadeIn();
		} else {
			ccDetails.fadeOut();
		}
	});

	/**
	 *	Function to show the confirm deletion container when the
	 *  trash icon is clicked.
	 */
	$('#trash-icon').click(function() {
		$('#cc-confirm-deletion').slideDown();
	});

	/**
	 *	Function to hide the credit card details option,
	 *  select the 'new card' option and provide the user
	 *  a control to undo the deletion.
	 */
	$('#confirm-cc-deletion').click(function() {
		$("#old-card-container").fadeOut('fast', function() {
			$("#new-card-container input[name='cc-type']").click();
			$("#cc-deletion-msg").slideDown(function() {
				showSaveCardDetailsLabel(true);
			});
		});

		$('#simplify-payment-form').append('<input id="deleteCustomerCard" type="hidden" name="deleteCustomerCard" value="true" />');
	});

	/**
	 *	Function to hide the confirm deletion container.
	 */
	$('#cancel-cc-deletion').click(function() {
		$('#cc-confirm-deletion').slideUp();
	});

	/**
	 *	Function to restore the save card details
	 *  form option.
	 */
	$('#cc-undo-deletion-lnk').click(function() {
		$("#cc-deletion-msg").hide();
		$('#cc-confirm-deletion').hide();
		$("#old-card-container").fadeIn('fast');
		$('#deleteCustomerCard').remove();
		showSaveCardDetailsLabel(false);
	});

	/**
	 *  Function to handle the form submission and either
	 *  generate a new card token for new cards or
	 *  charge an existing user's card.
	 */
	$('#simplify-payment-form').submit(function() {

		$('#simplify-ajax-loader').show();
		$('.simplify-payment-errors').hide();
		$('.simplify-submit-button').attr('disabled', 'disabled'); /* Disable the submit button to prevent repeated clicks */

		// Fetch a card token for new card details otherwise submit form with existing card details
		if ($("#simplify-cc-details").is(':visible')) {
			if (simplifyPublicKey.length == 0) {
				return false;
			}

			SimplifyCommerce.generateToken({
				key: simplifyPublicKey,
				card: {
					number: $(".simplify-card-number").val().trim().replace(/\s+/g, ''),
					cvc: $(".simplify-card-cvc").val(),
					expMonth: $("#simplify-cc-details select[name='Date_Month']").val(),
					expYear: $("#simplify-cc-details select[name='Date_Year']").val().substring(2),
					name: simplifyFirstname + ' ' + simplifyLastname,
					addressCity: simplifyCity,
					addressLine1: simplifyAddress1,
					addressLine2: simplifyAddress2,
					addressState: simplifyState,
					addressZip: simplifyPostcode
				}
			}, simplifyResponseHandler);

			return false; /* Prevent the form from submitting with the default action */
		} else {
			$('#simplify-payment-form')
				.append('<input type="hidden" name="chargeCustomerCard" value="true" />')
				.get(0).submit();
		}
	});

});

/**
 * Function to retrieve a cardholder detail or empty string if it doesn't exist.
 */
function getCardHolderDetail(detail) {
	return (typeof cardholderDetails[detail] !== 'undefined') ? cardholderDetails[detail] : '';
}

/**
 * Function to toggle the visibility of the the 'save card details' label
 */
function showSaveCardDetailsLabel(isSaveCardeDetailsLabelVisible) {
	var $saveCustomerLabel = $('#saveCustomerLabel'),
		$updateCustomerLabel = $('#updateCustomerLabel');

	if (isSaveCardeDetailsLabelVisible) {
		$saveCustomerLabel.show();
		$updateCustomerLabel.hide();
	} else {
		$updateCustomerLabel.show();
		$saveCustomerLabel.hide();
	}
}

/**
 * Function to handle the response from Simplify Commerce's tokenization call.
 */
function simplifyResponseHandler(data) {
	if (data.error) {
		var errorMessages = {
			'card.number': 'The credit card number you entered is invalid.',
			'card.expYear': 'The expiry year on the credit card is invalid.'
		};

		// Show any validation errors
		if (data.error.code == "validation") {
			var fieldErrors = data.error.fieldErrors,
				fieldErrorsLength = fieldErrors.length,
				errorList = "";

			for (var i = 0; i < fieldErrorsLength; i++) {
				errorList += "<div>" + errorMessages[fieldErrors[i].field] +
					" " + fieldErrors[i].message + ".</div>";
			}
			// Display the errors
			$('.simplify-payment-errors')
				.html(errorList)
				.show();
		}
		// Re-enable the submit button
		$('.simplify-submit-button').removeAttr('disabled');
		$('#simplify-payment-form').show();
		$('#simplify-ajax-loader').hide();
	} else {
		// Insert the token into the form so it gets submitted to the server
		$('#simplify-payment-form')
			.append('<input type="hidden" name="simplifyToken" value="' + data['id'] + '" />')
			.append('<input type="hidden" name="chargeCustomerCard" value="false" />')
			.get(0).submit();
	}
}