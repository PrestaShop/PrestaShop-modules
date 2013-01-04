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
*  @version  Release: $Revision: 6844 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

$(document).ready(function() {
	/* Set Stripe's publishable key */
	Stripe.setPublishableKey(stripe_public_key);

	/* Determine the Credit Card Type */
	$('.stripe-card-number').keyup(function() {
		if ($(this).val().length >= 2)
		{
			stripe_card_type = Stripe.cardType($('.stripe-card-number').val());
			$('.cc-icon').removeClass('enable');
			$('.cc-icon').removeClass('disable');
			$('.cc-icon').each(function() {
				if ($(this).attr('rel') == stripe_card_type)
					$(this).addClass('enable');
				else
					$(this).addClass('disable');
			});
		}
		else
		{
			$('.cc-icon').removeClass('enable');
			$('.cc-icon:not(.disable)').addClass('disable');
		}
	});

	$('#stripe-payment-form-cc').submit(function(event) {
		$('.stripe-payment-errors').hide();
		$('#stripe-payment-form-cc').hide();
		$('#stripe-ajax-loader').show();
		$('.stripe-submit-button-cc').attr('disabled', 'disabled'); /* Disable the submit button to prevent repeated clicks */
	});

	$('#stripe-payment-form').submit(function(event) {

		if (!Stripe.validateCardNumber($('.stripe-card-number').val()))
			$('.stripe-payment-errors').text($('#stripe-wrong-card').text() + ' ' + $('#stripe-please-fix').text());
		else if (!Stripe.validateExpiry($('.stripe-card-expiry-month').val(), $('.stripe-card-expiry-year').val()))
			$('.stripe-payment-errors').text($('#stripe-wrong-expiry').text() + ' ' + $('#stripe-please-fix').text());
		else if (!Stripe.validateCVC($('.stripe-card-cvc').val()))
			$('.stripe-payment-errors').text($('#stripe-wrong-cvc').text() + ' ' + $('#stripe-please-fix').text());
		else
		{
			$('.stripe-payment-errors').hide();
			$('#stripe-payment-form').hide();
			$('#stripe-ajax-loader').show();
			$('.stripe-submit-button').attr('disabled', 'disabled'); /* Disable the submit button to prevent repeated clicks */

			Stripe.createToken({
				number: $('.stripe-card-number').val(),
				cvc: $('.stripe-card-cvc').val(),
				exp_month: $('.stripe-card-expiry-month').val(),
				exp_year: $('.stripe-card-expiry-year').val(),
				name: stripe_billing_address.firstname + ' ' + stripe_billing_address.lastname,
				address_line1: stripe_billing_address.address1,
				address_line2: stripe_billing_address.address2,
				address_zip: stripe_billing_address.postcode,
				address_state: stripe_billing_address.state,
				address_country: stripe_billing_address.country
			}, stripeResponseHandler);

			return false; /* Prevent the form from submitting with the default action */
		}

		$('.stripe-payment-errors').fadeIn(1000);
		return false;
	});

	$('#stripe-replace-card').click(function() {
		$('#stripe-payment-form-cc').hide();
		$('#stripe-payment-form').fadeIn(1000);
	});

	$('#stripe-delete-card').click(function() {
		$.ajax({
			type: 'POST',
			url: baseDir + 'modules/stripejs/ajax.php',
			data: 'action=delete_card&token=' + stripe_secure_key
		}).done(function(msg)
		{
			if (msg == 1)
			{
				$('#stripe-payment-form-cc').hide();
				$('.stripe-card-deleted').text($('#stripe-card-del').text()).fadeIn(1000);
				$('#stripe-payment-form').fadeIn(1000);
			}
			else
				alert($('#stripe-card-del-error').text());
		});
	});
	
	/* Catch callback errors */
	if ($('.stripe-payment-errors').text())
		$('.stripe-payment-errors').fadeIn(1000);
	
});

function stripeResponseHandler(status, response)
{
	if (response.error)
	{
		$('.stripe-payment-errors').text(response.error.message).fadeIn(1000);
		$('.stripe-submit-button').removeAttr('disabled');
		$('#stripe-payment-form').show();
		$('#stripe-ajax-loader').hide();
	}
	else
	{
		$('.stripe-payment-errors').hide();
		$('#stripe-payment-form').append('<input type="hidden" name="stripeToken" value="' + escape(response['id']) + '" />');
		$('#stripe-payment-form').append('<input type="hidden" name="StripLastDigits" value="' + parseInt($('.stripe-card-number').val().slice(-4)) + '" />');
		$('#stripe-payment-form').get(0).submit();
	}
}
