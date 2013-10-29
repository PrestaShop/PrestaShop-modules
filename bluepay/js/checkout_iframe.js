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

function getParameter(name, message) {
                name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
                var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
                results = regex.exec(message);
                return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

var socket = '';

function iframeSocket(url) {
    socket = new easyXDM.Socket({
    remote: url,
    container: document.getElementById("container"),
    onMessage: function(message, origin) {
	$('#error-text').hide();
	$('#error-text').text('');
	var https = "https";
        if (origin != https + '://secure.bluepay.com')
		return false;
	if (getParameter('Result', message) == 'APPROVED' && getParameter('MESSAGE', message) != "DUPLICATE") {
	    var post_data = { iframe_transaction_approved:1,
				token:$('#token').val(),
				transaction_id:getParameter('TRANS_ID', message),
				payment_type:getParameter('PAYMENT_TYPE', message),
				invoice_id:getParameter('INVOICE_ID', message),
				name:getParameter('NAME', message),
				email:getParameter('EMAIL', message),
				amount:getParameter('AMOUNT', message),
				transaction_type:getParameter('TRANSACTION_TYPE', message),
				payment_account:getParameter('PAYMENT_ACCOUNT', message),
				card_type:getParameter('CARD_TYPE', message),
				card_expiration:getParameter('CARD_EXPIRE', message),
				save_payment_information:getParameter('SAVE_PAYMENT_INFORMATION', message),
				stored_payment_account:getParameter('STORED_PAYMENT_ACCOUNT', message),
				message:getParameter('MESSAGE', message)}
            $.ajax({
	    type: "POST",
            url: 'modules/bluepay/validation.php',
            data: post_data,
	    timeout: 15000,
            success: function(data) {
            	window.location.href = data;
		return true;
            },
    	    error: function(jqXHR, textStatus, errorThrown) {
        	if(textStatus === "timeout")
           	    alert("Timeout error.");
    	    }
            });
	} else if (message != '' && getParameter('STATUS', message) == '0') {
		$('#error-text').show();
                $('#click_bluepay').click();
                $('#error-text').text('This transaction has been declined by your bank.');
		return false;
	} else if (message != '' && getParameter('MESSAGE', message) == 'ROUTING NUMBER NOT VALID') {
		$('#error-text').show();
                $('#click_bluepay').click();
                $('#error-text').text('Invalid routing number entered. Please check your routing number again.');
                return false;
	} else if (message != '' && getParameter('MESSAGE', message) == '') {
		$('#error-text').show();
                $('#click_bluepay').click();
                $('#error-text').text(message);
                return false;
	} else if (message != '' && getParameter('MESSAGE', message) != 'Card Expired') {
		$('#error-text').show();
                $('#click_bluepay').click();
		$('#error-text').text(getParameter('MESSAGE', message));
		return false;
	} else {
		if (getParameter('MESSAGE', message) != '') {
                	$('#error-text').show();
                	$('#click_bluepay').click();
			$('#error-text').text(getParameter('MESSAGE', message));
			if (getParameter('MESSAGE', message) == "Missing NAME")
				$('#error-text').text("Please enter the cardholder's full name.");
			else if (getParameter('MESSAGE', message) == "Missing CC_NUM" || getParameter('MESSAGE', message) == "CARD ACCOUNT NOT VALID")
				$('#error-text').text("Please enter a valid credit card number.");
			else if (getParameter('MESSAGE', message) == "Card%20Expired")
				$('#error-text').text("The card month/year entered is expired.");
			else if (getParameter('MESSAGE', message) == "ACH_ROUTING and ACH_ACCOUNT required")
				$('#error-text').text("Please check your account and routing number and try again."); 
			else if (getParameter('STATUS', message) == '0')
                		$('#error-text').text('The transaction has been declined by your bank.');
        		else if (getParameter('MESSAGE', message) == 'DUPLICATE')
                		$('#error-text').text('This transaction has already been processed. Please try again later.');
        		else if (getParameter('MESSAGE', message) == 'Card Expired')
                		$('#error-text').text('The card entered is expired.');
        		else if (getParameterByName('MESSAGE', message) != '')
                		$('#error-text').text('Error: ' + getParameterByName('error'));
		return false;
		}
	}
    },
    onReady: function() {
	$("iframe").each(function() { 
		if ($('#allow_stored_payments').val() == 'Yes')
                        if ($('#require_cvv2').val() == 'Yes') {
                                $('#left_sidebar').css('height', '280px');
				$(this).height('280px');
                         } else {
                                $('#left_sidebar').css('height', '230px');
				$(this).height('235px');
			}
                else
                        if ($('#require_cvv2').val() == 'Yes') {
                                $('#left_sidebar').css('height', '220px');
				$(this).height('225px');
                         } else {
                                $('#left_sidebar').css('height', '195px');
				$(this).height('190px'); 
			}
	});
    }
});
    //}
}
$(document).ready(function () {
		$('iframe').css({"color":"green"});
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
	if ($('#pay_type').val() != 'ACH')
	{
		$('#ach_fields').hide();
	}

	$('#payment_type').change(function () {
                if ($(this).val() == 'CC') {
                        $('#ach_fields').fadeToggle('fast', function() {
                                $('#cc_fields').fadeToggle();
                        });
                } else {
                        $('#cc_fields').fadeToggle('fast', function() {
                                $('#ach_fields').fadeToggle();
                        });
                }
        });

	$('#submit_payment').click(function()
	{
		socket.postMessage("Submit form");
	});
	if (getParameterByName('error') == 'Declined') {
		$('#click_bluepay').click();
		$('#error-text').show();
                $('#error-text').text('The transaction has been declined by your bank.');
	} else if (getParameterByName('error') == 'Duplicate Transaction') {
                $('#click_bluepay').click();
		$('#error-text').show();
                $('#error-text').text('This transaction has already been processed. Please try again later.');
        } else if (getParameterByName('error') == 'Card Expired') {
                $('#click_bluepay').click();
		$('#error-text').show();
                $('#error-text').text('The card entered is expired.');
        } else if (getParameterByName('error') != '') {
                $('#click_bluepay').click();
		$('#error-text').show();
                $('#error-text').text('Error: ' + getParameterByName('error'));
        }
})
