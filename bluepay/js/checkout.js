/*
* 2013 BluePay
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author BluePay Processing, LLC
*  @copyright  2013 BluePay Processing, LLC
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

function getParameterByName(name) {
                name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
                var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
                results = regex.exec(location.search);
                return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
        }
var card_error = "Please check your credit card information (Credit card type, number and expiration date)";
var cvv2_error = "Please enter your Card Verification Value";
var name_error = "Please specify your Full Name";
var account_error = "Please enter a valid Account Number";
var routing_error = "Invalid Routing Number entered.";

$(document).ready(function () {
		$('#use_saved_payment_information').prop('checked', false);
                        $('#cc_fields').find(':input').prop('disabled', false);
                        $('#ach_fields').find(':input').prop('disabled', false);
                        $('#click_bluepay').click(function(e) {
				$('html,body').animate({scrollTop: $('#bluepay_form').offset().top});
                                e.preventDefault();
                                $('#click_bluepay').fadeOut("fast",function(){
                                        $("#payment_fields").show();
                                        $('#click_bluepay').fadeIn('fast');
                                });
                                $('#click_bluepay').unbind();
                                $('#click_bluepay').click(function(e){
                                        e.preventDefault();
                                });
                        });

                        $('#cvv2_help').click(function(){
                                $("#cvv2_help_img").show();
                                $('#cvv2_help').unbind();
                        });

	$('#payment_type').val('CC');

	if ($('#pay_type').val() == 'BOTH') {
		if ($('#allow_stored_payments').val() == 'Yes')
			if ($('#require_cvv2').val() == 'Yes')
				$('#left_sidebar').css('height', '250px');
			else
				$('#left_sidebar').css('height', '210px');
		else
			if ($('#require_cvv2').val() == 'Yes'){
				$('#left_sidebar').css('height', '200px');}
			else
				$('#left_sidebar').css('height', '165px');
        } else if($('#pay_type').val() == 'CC') {
		if ($('#allow_stored_payments').val() == 'Yes')
			if ($('#require_cvv2').val() == 'Yes')
				$('#left_sidebar').css('height', '220px');
			else
				$('#left_sidebar').css('height', '200px');
		else
			if ($('#require_cvv2').val() == 'Yes')
				$('#left_sidebar').css('height', '185px');
			else
				$('#left_sidebar').css('height', '150px');
	} else {
		if ($('#allow_stored_payments').val() == 'Yes')
			$('#left_sidebar').css('height', '210px');
		else {
			$('#left_sidebar').css('height', '175px');
		}
	}

	if ($('#pay_type').val() != 'ACH')
	{
		$('#ach_fields').hide();
	}

	$('#payment_type').change(function () {
                if ($(this).val() == 'CC') {
                        $('#ach_fields').fadeToggle('fast', function() {
                                $('#cc_fields').fadeToggle();
				if ($('#allow_stored_payments').val() == 'Yes')
					if($('#require_cvv2').val() == 'Yes')
						$('#left_sidebar').css('height', '250px');
					else
						$('#left_sidebar').css('height', '210px');
				else
					if($('#require_cvv2').val() == 'Yes')
						$('#left_sidebar').css('height', '180px');
					else
						$('#left_sidebar').css('height', '150px');
                        });
                } else {
                        $('#cc_fields').fadeToggle('fast', function() {
                                $('#ach_fields').fadeToggle();
				if ($('#allow_stored_payments').val() == 'Yes')
					$('#left_sidebar').css('height', '220px');
				else
					$('#left_sidebar').css('height', '175px');
                        });
                }
        });
	$('#submit_payment').click(function()
	{
		if ($('#fullname').val() == '')
		{
			$('#error-text').text(name_error);
			$('#error-text').show();
			return false;
		}
		else
		{
			if ($('#payment_type').val() == 'CC' || ($('#pay_type').val() != 'ACH'))
			{
				if (!$('#use_saved_payment_information').prop('checked') && !validateCC($('#card_number').val(), $('#card_type').val()))
				{
					$('#error-text').show();
					$('#error-text').text(card_error);
					return false;
				}
				if ($('#require_cvv2').val() == 'Yes' && $('#cvv2').val() == '')
				{
					$('#error-text').show();
					$('#error-text').text(cvv2_error);
					return false;
				}
				$('#ach_fields').find(':input').prop('disabled', true);
			} 
			else
			{
				if ($('#ach_account').val() == '')
				{
					$('#error-text').show();
					$('#error-text').text(account_error);
					return false;
				}
				if ($('#ach_routing').val().length != 9)
				{
					$('#error-text').show();
					$('#error-text').text(routing_error);
					return false;
				}
				$('#cc_fields').find(':input').prop('disabled', true);
			}
			if ($('#allow_stored_payments').val() == 'Yes' && $('#has_saved_payment_information').val())
			{
				$('#save_payment_information').val('Yes');
			}
			$('#bluepay_form').submit();
		}
		return false;
	});

	if (getParameterByName('error') != '') {
		$('#error-text').show();
		$('#click_bluepay').click();
		if (getParameterByName('error') == 'Declined')
			$('#error-text').text('The transaction has been declined by your bank.'); 
		else if (getParameterByName('error') == 'Duplicate Transaction')
			$('#error-text').text('This transaction has already been processed. Please try again later.');
		else if (getParameterByName('error') == 'Card Expired')
			$('#error-text').text('The card entered is expired.');
		else if (getParameterByName('error') != '')
			$('#error-text').text('Error: ' + getParameterByName('error'));
	}
})
