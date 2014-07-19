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
		
	$('a.buttongenerar').click(function(){
		$('input.buttonguardar').css('display', 'none').remove();
	});

	//------------------
	// ORDERS -
	//--------------------

	var fichaPedidoSeur = $('#fichaPedidoSeur');
	var btnDesgloseCosteEnvio = $('#btnDesgloseCosteEnvio', fichaPedidoSeur);
	var labelButton = $('#labelButton', fichaPedidoSeur);
	var desgloseCosteEnvio = $('#desgloseCosteEnvio', fichaPedidoSeur);
	var loader_desgloseCosteEnvio = $('tbody', desgloseCosteEnvio).html();
	var module_dir = $('#module_dir').val();

	//--------------------------------------
	// >> Mostrar desglose gastos de envio -
	//--------------------------------------
	btnDesgloseCosteEnvio.click(function(){
		$('#loaderTarifa').html('<img src="' + module_dir + 'img/ajax-loader-circle.gif" /><br />').fadeIn("fast");
		$(desgloseCosteEnvio).fadeOut();
		resultado = '';
		$.ajax({
			url: module_dir + 'ajax/getPrivateRateAjax.php',
			type: 'GET',
			data: rate_data_ajax,
			dataType: 'json',
			async: true,
			contentType:
				"application/json; charset=utf-8",
				error: function(xhr, ajaxOptions, thrownError){
					console.log("Status: "+xhr.status);
					console.log("Error: "+thrownError);
				},
				success: function(data){
					try{
						$.each(data, function(index, val) {
						if(index >= (data.length - 1)){
								resultado += "<tr class='bold'><td>"+val.concepto+"</td><td>"+val.importe+" &euro;</td></tr>";
							}
							else{
								resultado += "<tr><td>"+val.concepto+"</td><td>"+Math.round(val.importe*100)/100+" &euro;</td></tr>";
							}
						});
					}
					catch(e){
						alert("Error: "+e);
					}
				},
				complete: function(){
					$('#loaderTarifa').fadeOut("fast");
					$(desgloseCosteEnvio).fadeIn();
					$('table tbody', desgloseCosteEnvio).fadeOut("fast", function(){
					$(this).html(resultado);
					$(this).fadeIn('fast');
				});
			}
		});
	});

	labelButton.click(function(){
		var datos_etiquetas = document.getElementById('datos_etiquetas').innerHTML;
		document.getElementById('labelError').innerHTML ="";
		datos_etiquetas = $.parseJSON(datos_etiquetas);
		$('#labelLoader').html('<img src="' + module_dir + 'img/ajax-loader-bar.gif" />').fadeIn("fast");
		$.ajax({
			url: module_dir + 'ajax/createLabelAjax.php',
			type: 'GET',
			data: {
				pedido: encodeURIComponent(datos_etiquetas.pedido), 
				total_bultos: encodeURIComponent(datos_etiquetas.total_bultos), 
				total_kilos: encodeURIComponent(datos_etiquetas.total_kilos), 
				direccion_consignatario: encodeURIComponent(datos_etiquetas.direccion_consignatario), 
				consignee_town: encodeURIComponent(datos_etiquetas.consignee_town),
				codPostal_consignatario: encodeURIComponent(datos_etiquetas.codPostal_consignatario),
				telefono_consignatario:  encodeURIComponent(datos_etiquetas.telefono_consignatario),
				movil: encodeURIComponent(datos_etiquetas.movil),
				name: encodeURIComponent(datos_etiquetas.name),
				companyia: encodeURIComponent(datos_etiquetas.companyia),
				email_consignatario: encodeURIComponent(datos_etiquetas.email_consignatario),
				dni: encodeURIComponent(datos_etiquetas.dni),
				info_adicional: encodeURIComponent(datos_etiquetas.info_adicional),
				country: encodeURIComponent(datos_etiquetas.country),
				iso: encodeURIComponent(datos_etiquetas.iso),
				iso_merchant: encodeURIComponent(datos_etiquetas.iso_merchant),
				cod_centro: encodeURIComponent(datos_etiquetas.cod_centro),
				reembolso: encodeURIComponent(datos_etiquetas.reembolso),
				id_employee: encodeURIComponent(datos_etiquetas.id_employee),
				token: encodeURIComponent(datos_etiquetas.token),
				back: encodeURIComponent(datos_etiquetas.back)
			},
			dataType: 'html',
			async: true,
			error:function(xhr, textStatus, errorThrown){
				$('#labelError')
					.fadeIn('fast')
					.css({ color: 'red',font:'bold' })
					.html(xhr.status + ': ' + textStatus + ': ' + errorThrown)
				;
			}, 
			success: function(data) {
				if(data == 1) setTimeout(function() {location.reload();}, 2000);
			}, 
			complete: function(data){
				if(data == 1) setTimeout(function() {location.reload();}, 2000);
				$('#labelLoader').fadeOut("fast");
				printFile(datos_etiquetas.file);
			}
		});
	});

	$('table caption img', desgloseCosteEnvio).click(function(){
		desgloseCosteEnvio.fadeOut(function(){
			$('tbody', desgloseCosteEnvio).html(loader_desgloseCosteEnvio); 
		});
	});

});
