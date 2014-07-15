/*
* 2007-2014 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

$(document).ready(function() {
	$.ajax({
		type: "POST",
		url: moduleDir + "ajax/get-order-states.php",
		data: {token:neteven_token, action:"display", field:"ORDER_STATE_BEFORE" },
		error:function(msg){
			alert("Error !: " + msg);
		},
		success:function(reponse){
			$("#state_before ul li").remove();
			$(reponse).appendTo("#state_before ul");
		}
	});
	
	$("#add_before").click(function() {
		var id 	 = $("#select_before option:selected").val();
		 $.ajax({
			type: "POST",
			url: moduleDir + "ajax/get-order-states.php",
			data: {token:neteven_token, id:id, action:"add", field:"ORDER_STATE_BEFORE" },
			error:function(msg){
				alert("Error !: " + msg);
			},
			success:function(reponse){
				$("#state_before ul li").remove();
				$(reponse).appendTo("#state_before ul");
			}
		});
	});
	
	$(".up_before").live("click", function() {
		var id 	 = $(this).parent().attr("data");
		$.ajax({
			type: "POST",
			url: moduleDir + "ajax/get-order-states.php",
			data: {token:neteven_token, id:id, action:"up", field:"ORDER_STATE_BEFORE" },
			error:function(msg){
				alert("Error !: " + msg);
			},
			success:function(reponse){
				$("#state_before ul li").remove();
				$(reponse).appendTo("#state_before ul");
			}
		});
	});
	$(".down_before").live("click", function() {
		var id 	 = $(this).parent().attr("data");
		$.ajax({
			type: "POST",
			url: moduleDir + "ajax/get-order-states.php",
			data: {token:neteven_token, id:id, action:"down", field:"ORDER_STATE_BEFORE" },
			error:function(msg){
				alert("Error !: " + msg);
			},
			success:function(reponse){
				$("#state_before ul li").remove();
				$(reponse).appendTo("#state_before ul");
			}
		});
	});
	$(".delete_before").live("click", function() {
		var id 	 = $(this).parent().attr("data");
		$.ajax({
			type: "POST",
			url: moduleDir + "ajax/get-order-states.php",
			data: {token:neteven_token, id:id, action:"delete", field:"ORDER_STATE_BEFORE" },
			error:function(msg){
				alert("Error !: " + msg);
			},
			success:function(reponse){
				$("#state_before ul li").remove();
				$(reponse).appendTo("#state_before ul");
			}
		});
	});
	
	$.ajax({
		type: "POST",
		url: moduleDir + "ajax/get-order-states.php",
		data: {token:neteven_token, action:"display", field:"ORDER_STATE_AFTER" },
		error:function(msg){
			alert("Error !: " + msg);
		},
		success:function(reponse){
			$("#state_after ul li").remove();
			$(reponse).appendTo("#state_after ul");
		}
	});
	
	$("#add_after").click(function() {
		if(!$("#select_after option:selected").hasClass("use")) {
			$("#select_after option:selected").addClass("use");
			var id 	 = $("#select_after option:selected").val();
			 $.ajax({
				type: "POST",
				url: moduleDir + "ajax/get-order-states.php",
				data: {token:neteven_token, id:id, action:"add", field:"ORDER_STATE_AFTER" },
				error:function(msg){
					alert("Error !: " + msg);
				},
				success:function(reponse){
					$("#state_after ul li").remove();
					$(reponse).appendTo("#state_after ul");
				}
			});
		}
	});
	
	$(".up_after").live("click", function() {
		var id 	 = $(this).parent().attr("data");
		
		$.ajax({
			type: "POST",
			url: moduleDir + "ajax/get-order-states.php",
			data: {token:neteven_token, id:id, action:"up", field:"ORDER_STATE_AFTER" },
			error:function(msg){
				alert("Error !: " + msg);
			},
			success:function(reponse){
				$("#state_after ul li").remove();
				$(reponse).appendTo("#state_after ul");
			}
		});
	});
	$(".down_after").live("click", function() {
		var id 	 = $(this).parent().attr("data");
		$.ajax({
			type: "POST",
			url: moduleDir + "ajax/get-order-states.php",
			data: {token:neteven_token, id:id, action:"down", field:"ORDER_STATE_AFTER" },
			error:function(msg){
				alert("Error !: " + msg);
			},
			success:function(reponse){
				$("#state_after ul li").remove();
				$(reponse).appendTo("#state_after ul");
			}
		});
	});
	$(".delete_after").live("click", function() {
		var id 	 = $(this).parent().attr("data");
		$.ajax({
			type: "POST",
			url: moduleDir + "ajax/get-order-states.php",
			data: {token:neteven_token, id:id, action:"delete", field:"ORDER_STATE_AFTER" },
			error:function(msg){
				alert("Error !: " + msg);
			},
			success:function(reponse){
				$("#state_after ul li").remove();
				$(reponse).appendTo("#state_after ul");
			}
		});
	});
	
	$.ajax({
		type: "POST",
		url: moduleDir + "ajax/get-feature-links.php",
		data: {token:neteven_token, action:"display" },
		error:function(msg){
			alert("Error !: " + msg);
		},
		success:function(reponse){
			$("#link li").remove();
			$(reponse).appendTo("#link ul");
		}
	});
	
	$("#add_link").click(function()
	{
		var id 	= $("#select_feature option:selected").val();
		var id_order_gateway_feature 	= $(".attr_neteven:visible").val();
		 $.ajax({
			type: "POST",
			url: moduleDir + "ajax/get-feature-links.php",
			data: {token:neteven_token, action:"add", id:id, id_order_gateway_feature:id_order_gateway_feature },
			error:function(msg){
				alert("Error !: " + msg);
			},
			success:function(reponse){
				$("#link li").remove();
				$(reponse).appendTo("#link ul");
			}
		});
	});
	
	$("#select_carac").change(function(){
		$("select.attr_neteven").css("display", "none");
		$("select[rel=\'"+$(this).val()+"\']").css("display", "block");
	});
	
	$(".delete_link").live("click", function(){
		var temp = $(this).parent().attr("id").split("-");
		var id = temp[0];
		var id_order_gateway_feature = temp[1];
		 $.ajax({
			type: "POST",
			url: moduleDir + "ajax/get-feature-links.php",
			data: {token:neteven_token, action:"delete", id:id, id_order_gateway_feature:id_order_gateway_feature},
			error:function(msg){
				alert("Error !: " + msg);
			},
			success:function(reponse){
				$("#link li").remove();
				$(reponse).appendTo("#link ul");
			}
		});
	});
	
	$('input[name="SHIPPING_BY_PRODUCT"]').change(function(){
		if ($(this).val() == 1)
			$('#shipping_fieldname_container').slideDown();
		else
			$('#shipping_fieldname_container').slideUp();
	});

	if ($('input[name="SHIPPING_BY_PRODUCT"]:checked').val() == 1)
		$('#shipping_fieldname_container').slideDown();
	else
		$('#shipping_fieldname_container').slideUp();

	$('#carrier_france').change(function() {
		var id_carrier 	= $(this).val();

		if($.trim(id_carrier) == "" || id_carrier == 0) {
			$('#zone_france').html('');
			return;
		}

		var default_val = SHIPPING_ZONE_FRANCE;
		$.ajax({
			type: "POST",
			url: moduleDir + "ajax/get-zone-by-carrier.php",
			data: {token:neteven_token, id_carrier:id_carrier, type : 'france', default_val : default_val},
			error:function(msg){
				alert("Error !: " + msg);
			},
			success:function(reponse){
				$('#zone_france').html(reponse);
			}
		});
	});

	$('#carrier_international').change(function() {
		var id_carrier 	= $(this).val();
		if($.trim(id_carrier) == "" || id_carrier == 0){
			$('#zone_international').html('');
			return;
		}

		var default_val = SHIPPING_CARRIER_INTERNATIONAL;
		$.ajax({
			type: "POST",
			url: moduleDir + "ajax/get-zone-by-carrier.php",
			data: {token:neteven_token, id_carrier:id_carrier, type : 'international', default_val : default_val},
			error:function(msg){
				alert("Error !: " + msg);
			},
			success:function(reponse){
				$('#zone_international').html(reponse);
			}
		});
	});

	$('#carrier_international').trigger('change');
	$('#carrier_france').trigger('change');
});

function addCustomizableField() {
	$('#customizable').append('<label>'+text_field_name+'</label><div class="margin-form"><input type="text" name="customizable_field_name[]" value="" /></div><label>'+text_value+'</label><div class="margin-form"><input type="text" name="customizable_field_value[]" value="" /></div><hr />');
}