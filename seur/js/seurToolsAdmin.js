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
	var undefined;
    var configuration_menu = $('ul.configuration_menu');
    var configuration_tabs = $('ul.configuration_tabs');
    var current_tab = $('li[id="deliveries"]', configuration_tabs);
    
    $('li[id="deliveries"]', configuration_tabs).fadeIn('fast');
    $('li[id="packing_list"]', configuration_tabs).fadeOut('fast');
    $('li[id="pickups"]', configuration_tabs).fadeOut('fast');
    
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
       
    var now = new Date();

	var lang_iso_code = 'es';
	try{
		if(($("#langTmpIsoCode") != undefined) && ($("#langTmpIsoCode").val().length > 0)){
			lang_iso_code = $("#langTmpIsoCode").val();
		}
	}
	catch(e){}
	if(lang_iso_code == 'en'){
		$('#start_date').datepicker($.datepicker.regional['en-GB']); 
		$('#end_date').datepicker($.datepicker.regional['en-GB']); 
	}
	else{
		$.datepicker.regional['es'] = {
			closeText: 'Cerrar',
			prevText: '&#x3c;Ant',
			nextText: 'Sig&#x3e;',
			currentText: 'Hoy',
			monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
			monthNamesShort: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
			dayNames: ['Domingo','Lunes','Martes','Mi&eacute;rcoles','Jueves','Viernes','S&aacute;bado'],
			dayNamesShort: ['Dom','Lun','Mar','Mi&eacute;','Juv','Vie','S&aacute;b'],
			dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','S&aacute;'],
			weekHeader: 'Sm',
			dateFormat: 'dd-mm-yy',
			firstDay: 1,
			isRTL: false,
			showMonthAfterYear: false,
			yearSuffix: '',
			showAnim : 'slideDown',
			minDate : '-15d',
			maxDate : now.getDate() + '-' + (now.getMonth()+1) + '-' + now.getFullYear(),
			onSelect : function(){}
		};
		$.datepicker.setDefaults($.datepicker.regional['es']);
		$('#start_date').datepicker($.datepicker.regional['es']); 
		$('#end_date').datepicker($.datepicker.regional['es']); 
	}
	$('#start_date').datepicker({});
	$('#end_date').datepicker({
		showAnim : 'slideDown',
		maxDate : now.getDate() + '-' + (now.getMonth()+1) + '-' + now.getFullYear(),
		minDate : '-15d'
	});

    //  return date format dd-mm-yy with X days
    function sumDays(d, days, operand)
    {
        if( typeof d != 'object' )
            d = new Date(d);
        if( operand == '+' )
            d.setDate( d.getDate() + days )
        else
            d.setDate( d.getDate() - days )
        
        return( d.getDate() + '-' + (d.getMonth()+1) + '-' + d.getFullYear() );
    }

    /* * * * DATEPICKER * * * */
    $('input[class="datepicker"]').datepicker({ showAnim : 'slideDown', dateFormat : 'dd-mm-yy' });

    /* * * * Show details * * * */
    $('.verDetalles').fancybox({
        'type' : 'ajax',
        'hideOnContentClick': true,
        'transitionIn'  : 'elastic',
        'transitionOut' : 'elastic'
    });
});
