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

$(document).ready(function() {
	checkPaymentType();
});

function checkPaymentType() 
{
       	if ($('#BP_PAYMENT_TYPE').val() == 'ACH')
       	{
               	$("label:contains('Card Types Allowed')").css('font-style', 'italic');
               	$("label[for=BP_CARD_TYPES_VISA]").css('font-style', 'italic');
               	$("label[for=BP_CARD_TYPES_MC]").css('font-style', 'italic');
               	$("label[for=BP_CARD_TYPES_AMEX]").css('font-style', 'italic');
               	$("label[for=BP_CARD_TYPES_DISC]").css('font-style', 'italic');
               	$("label[for=BP_CARD_TYPES_DC]").css('font-style', 'italic');
               	$("label[for=BP_CARD_TYPES_JCB]").css('font-style', 'italic');
		$("label[for=BP_TRANSACTION_TYPE]").css('font-style', 'italic');
               	$("label:contains('Require CVV2?')").css('font-style', 'italic');
               	$('#BP_CARD_TYPES').attr('disabled', true);
               	$('#BP_CARD_TYPES_VISA').attr('disabled', true);
               	$('#BP_CARD_TYPES_MC').attr('disabled', true);
               	$('#BP_CARD_TYPES_AMEX').attr('disabled', true);
               	$('#BP_CARD_TYPES_DISC').attr('disabled', true);
               	$('#BP_CARD_TYPES_DC').attr('disabled', true);
               	$('#BP_CARD_TYPES_JCB').attr('disabled', true);
		$('#BP_TRANSACTION_TYPE').attr('disabled', true);
              	$('#BP_REQUIRE_CVV2').attr('disabled', true);
	}
	else
	{
		$("label:contains('Card Types Allowed')").css('font-style', 'normal');
		$("label[for=BP_CARD_TYPES_VISA]").css('font-style', 'normal');
		$("label[for=BP_CARD_TYPES_MC]").css('font-style', 'normal');
		$("label[for=BP_CARD_TYPES_DISC]").css('font-style', 'normal');
		$("label[for=BP_CARD_TYPES_AMEX]").css('font-style', 'normal');
		$("label[for=BP_CARD_TYPES_DC]").css('font-style', 'normal');
		$("label[for=BP_CARD_TYPES_JCB]").css('font-style', 'normal');
		$("label[for=BP_TRANSACTION_TYPE]").css('font-style', 'normal');
		$("label:contains('Require CVV2?')").css('font-style', 'normal');
		$('#BP_CARD_TYPES').attr('disabled', false);
		$('#BP_CARD_TYPES_VISA').attr('disabled', false);
		$('#BP_CARD_TYPES_MC').attr('disabled', false);
		$('#BP_CARD_TYPES_AMEX').attr('disabled', false);
		$('#BP_CARD_TYPES_DISC').attr('disabled', false);
		$('#BP_CARD_TYPES_DC').attr('disabled', false);
		$('#BP_CARD_TYPES_JCB').attr('disabled', false);
		$('#BP_TRANSACTION_TYPE').attr('disabled', false);
		$('#BP_REQUIRE_CVV2').attr('disabled', false);
	}
}
