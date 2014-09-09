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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2014 PrestaShop SA
*  @version  Release: 0.4.4
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

$(document).ready(function(){
    /*
     * fields patterns
     */
    var pattern_email = /^[a-zA-Z0-9_\.\-]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$/;
    var pattern_numbers = /^[0-9]*/; 
    var pattern_text = /\D/;
    
    /*
     * global vars
     */
    var seur_module = $('#seur_module');
    var module_dir = $('#module_dir').val();
    
    /*
     * is seur customer
     */
    var is_customer = $('#is_customer');
    var new_customer_div = $('#new_customer');
    
    /*
     * ranges configuration
     */
    var range_configuration_div = $('#range_configuration');
    var yes_button = $('input[name="yes_button"]', range_configuration_div);
    var install_ranges_div = $('#install_ranges', range_configuration_div);
    
    /*
     * seur tabs menu
     */
    var configuration_menu = $('ul.configuration_menu');
    var configuration_tabs = $('ul.configuration_tabs');
    var current_tab = $('li.default', configuration_tabs);
    
    /*
     * seur tabs forms
     */
    var module_configuration_div = $('#module_configuration', seur_module);
    var merchant_form = $('form[name="merchant_form"]', module_configuration_div); 
    var configuration_form = $('form[name="configuration_form"]', module_configuration_div);
    
    /*
     * configuration form vars
     */
    var radio_pos = $('input[name="pos"]',configuration_form);
    var pos_carrier_li = $('li#pos_carrier',configuration_form);
    
    var radio_international_orders = $('input[name="international_orders"]',configuration_form);
    var message_international_orders_li = $('li#message_international_orders',configuration_form);
    
    var radio_seur_cod = $('input[name="seur_cod"]', configuration_form);
    var seur_cod_configuration_li = $('li#seur_cod_configuration', configuration_form);
    
    var advice_checkbox = $('input[name="advice_checkbox"]', configuration_form);
    var distribution_checkbox = $('input[name="distribution_checkbox"]', configuration_form);
    
    var aditional_cost_sms_li = $('li#aditional_cost_sms',configuration_form);
    
    var notification_advice_radio = $('input[name="notification_advice_radio"]',configuration_form);
    var notification_distribution_radio = $('input[name="notification_distribution_radio"]',configuration_form);
    
    var notification_advice_div = $('#notification_advice_div', configuration_form);
    var notification_distribution_div = $('#notification_distribution_div', configuration_form);
    
    var radio_print_type = $('input[name="print_type"]', configuration_form);
    var printer_name_li = $('li#printer_name', configuration_form);
    
    install_ranges_div.fadeOut(); 
    
    /*
     * check if is seur customer 
     */
    $('input[type="button"]', is_customer).click(function()
    {
        
            if( $(this).attr('name') == "yes_button" )
            {
                    $('#legentNuevaAlta').css('display', 'none');
                    $('#legendLogin').css('display', 'block');
                    $('.mostrarNoUsuario').css('display', 'none');
                    var ocultar = $('.ocultar', new_customer_div);
                    ocultar.removeClass('ocultar');
                    ocultar.addClass('require');

                    is_customer.fadeOut('fast', function()
                    { 
                            new_customer_div.fadeIn(); 
                    });
            }
            else if( $(this).attr('name') == "btnNo" )
            {
                    $('.ocultar', new_customer_div).css('display', 'none');
                    $('#legendLogin').css('display', 'none');
                    $('#legentNuevaAlta').css('display', 'block');

                    is_customer.fadeOut('fast', function()
                    { 
                            new_customer_div.fadeIn(); 
                    });
            }
    });
    
    /*
     * menu tabs
     */
    
    $('li[id="configuration"]', configuration_tabs).fadeIn('fast');
    $('li[id="merchant"]', configuration_tabs).fadeOut('fast');
    $('li[id="webservices"]', configuration_tabs).fadeOut('fast');
    
    $('li', configuration_menu).click(function() 
    {
            var selected_tab = $('#' + $(this).attr('tab'));
            
            if( current_tab.attr('id') != selected_tab.attr('id') )
            {
                    current_tab.fadeOut('fast');
                    selected_tab.fadeIn('fast');
                    
                    current_tab = selected_tab;

                    $('li.active', configuration_menu).removeClass('active');
                    $(this).addClass('active');
            }
    });
    
    /* 
     * show terms of service
     * */
    $('#tos').fancybox({
        'hideOnContentClick': true,
        'transitionIn'  : 'elastic',
        'transitionOut' : 'elastic'
    });

	/*
	* check merchant form 
	*/
	$('form[name="merchant_form"]', seur_module).submit(function(){
		var undefined;
		var tmpTxt = "";
		var send = true;
		inputsRequireds = $('.required input', $(this));
		selectsRequireds = $('.required select', $(this));
		$(".form_error").remove();

		$.each(inputsRequireds, function(index, input){
			if($(input).val() == ''){
				tmpTxt = "Fill in the field.";
				var tmpObj = $('#seurJsTranslations input[name="requiredField"]').first();
				if((tmpObj != undefined) && (tmpObj != null) && (tmpObj.length > 0)){ tmpTxt = tmpObj.val() }
				$('sup[name="'+input.name+'"]').after("<span class='form_error'>"+tmpTxt+"</span>");
				$(input).focus();
				send = false;
			}
		});
		$.each(selectsRequireds, function(index, input){
			if($(input).val() == '' ){
				tmpTxt = "Select an option.";
				var tmpObj = $('#seurJsTranslations input[name="requiredSelect"]').first();
				if((tmpObj != undefined) && (tmpObj != null) && (tmpObj.length > 0)){ tmpTxt = tmpObj.val() }
				$('sup[name="'+input.name+'"]').after("<span class='form_error'>"+tmpTxt+"</span>");
				$(input).focus();
				send = false;
			}
		});
		if($("#lopd").length && !$("#lopd").is(':checked')){
			tmpTxt = "Accept the privacy policy.";
			var tmpObj = $('#seurJsTranslations input[name="acceptPrivacyPolicy"]').first();
			if((tmpObj != undefined) && (tmpObj != null) && (tmpObj.length > 0)){ tmpTxt = tmpObj.val() }
			$('sup[name="sup_lopd"]').after("<span class='form_error'>"+tmpTxt+"</span>");
			$('sup[name="sup"]').after("<span class='form_error'>"+tmpTxt+"</span>");
			$("#lopd").focus();
			send = false;
		}
		return send;
	});


    yes_button.click(function()
    { 
            install_ranges_div.fadeIn(); 
    });

    radio_pos.change( function()
    {
            if( $(this).val() == '1' )
            {
                    pos_carrier_li.animate( { "opacity" : "1" }, "fast" );
            }
            else
            {
                    pos_carrier_li.animate( { "opacity" : "0" }, "fast" );
            }
    });

    radio_international_orders.change( function()
    {
            if( $(this).val() == '1' )
            {
                    message_international_orders_li.animate( { "opacity" : "1" }, "fast" );
            }
            else
            {
                    message_international_orders_li.animate( { "opacity" : "0" }, "fast" );
            }
    });
    
    radio_seur_cod.change( function()
    {
            if( $(this).val() == '1' )
            {
                    seur_cod_configuration_li.animate( { "opacity" : "1" }, "fast" );
            }
            else
            {
                    seur_cod_configuration_li.animate( { "opacity" : "0" }, "fast" );
            }
    });
        
    radio_print_type.change( function()
    {
            if( $(this).val() == '0' )
            {
                    printer_name_li.animate( { "opacity" : "1" }, "fast" );
            }
            else
            {
                    printer_name_li.animate( { "opacity" : "0" }, "fast" );
            }
    });

    if(aditional_cost_sms_li.is(":visible") && $('#sms_advice').is(':checked') && (advice_checkbox.is(':checked') || distribution_checkbox.is(':checked')))
    {
            aditional_cost_sms_li.animate( { "opacity" : "1" }, "fast" );
    }
    else
    {
            aditional_cost_sms_li.animate( { "opacity" : "0" }, "fast" );
    }

    advice_checkbox.change( function()
    {
            if( advice_checkbox.is(':checked') )
            {
                    notification_advice_div.animate( { "opacity" : "1" }, "fast" );
            }
            else
            {
                    notification_advice_div.animate( { "opacity" : "0" }, "fast" );
                    if ( !distribution_checkbox.is(':checked'))
                    {
                            aditional_cost_sms_li.animate( { "opacity" : "0" }, "fast" );
                    }
            }
    });

    distribution_checkbox.change( function()
    {
            if( distribution_checkbox.is(':checked') )
            {
                    notification_distribution_div.animate( { "opacity" : "1" }, "fast" );
            }
            else
            {
                    notification_distribution_div.animate( { "opacity" : "0" }, "fast" );
                    if ( !advice_checkbox.is(':checked'))
                    {
                            aditional_cost_sms_li.animate( { "opacity" : "0" }, "fast" );
                    }
            }
    });

/*
 * check jquery version to use .atrr or .prop
 */

if ($().jquery < "1.6") 
{
        notification_advice_radio.change(function()
        {
                if( $('#sms_advice').is(':checked') ) //#sms_advice checked
                {       
                        $('#email_distribution').attr('checked',false) 
                        $('#sms_distribution').attr('checked',true)
                        aditional_cost_sms_li.animate( { "opacity" : "1" }, "fast" );
                }
                if( $('#email_advice').is(':checked') )  //#email_advice checked
                {   
                        $('#email_distribution').attr('checked',true)
                        $('#sms_distribution').attr('checked',false)
                        aditional_cost_sms_li.animate( { "opacity" : "0" }, "fast" );
                }

        });
        
        notification_distribution_radio.change(function()
        {
                if( $(this).val() == '1' )//#sms_distribution checked
                {
                        $('#email_advice').attr('checked',false) 
                        $('#sms_advice').attr('checked',true)
                        aditional_cost_sms_li.animate( { "opacity" : "1" }, "fast" );
                }
                if( $(this).val() == '0' )  //#email_distribution checked
                {   
                        $('#email_advice').attr('checked',true)
                        $('#sms_advice').attr('checked',false) 
                        aditional_cost_sms_li.animate( { "opacity" : "0" }, "fast" );
                }
        });
}
else
{
    notification_advice_radio.change(function()
    {
            if( $('#sms_advice').is(':checked') ) //#sms_advice checked
            {       
                    $('#email_distribution').prop('checked',false) 
                    $('#sms_distribution').prop('checked',true)
                    aditional_cost_sms_li.animate( { "opacity" : "1" }, "fast" );
            }
            if( $('#email_advice').is(':checked') )  //#email_advice checked
            {   
                    $('#email_distribution').prop('checked',true)
                    $('#sms_distribution').prop('checked',false)
                    aditional_cost_sms_li.animate( { "opacity" : "0" }, "fast" );
            }
            
    });
    
    notification_distribution_radio.change(function()
    {
            if( $(this).val() == '1' )//#sms_distribution checked
            {
                    $('#email_advice').prop('checked',false) 
                    $('#sms_advice').prop('checked',true)
                    aditional_cost_sms_li.animate( { "opacity" : "1" }, "fast" );
            }
            if( $(this).val() == '0' )  //#email_distribution checked
            {   
                    $('#email_advice').prop('checked',true)
                    $('#sms_advice').prop('checked',false) 
                    aditional_cost_sms_li.animate( { "opacity" : "0" }, "fast" );
            }
    });
}

    $('.onlyNumbers').bind('propertychange input', function(e) {
        var key = e.charCode || e.keyCode || 0 || $(this).val();
        $(this).val($(this).val().replace(/[^0-9]+/g, ""));
        // allow backspace, tab, delete, arrows, numbers and keypad numbers ONLY
        // home, end, period, and numpad decimal
        return (
            key == 8 ||
            key == 9 ||
            key == 46 ||
            key == 110 ||
            key == 190 ||
            (key >= 35 && key <= 40) ||
            (key >= 48 && key <= 57) ||
            (key >= 96 && key <= 105))
    });
});