/*
* 2007-2013 PrestaShop
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
* @author PrestaShop SA <contact@prestashop.com>
* @copyright  2007-2013 PrestaShop SA
* @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/

$(document).ready(
	function() {
		$('#showUserlist').click(function(){
		
			if ($('#userDetails').is(':hidden'))
			{
				$('#Spantextless').show();
				$('#Spantextmore').hide();
			} else {
				$('#Spantextmore').show();
				$('#Spantextless').hide();
			}
			$('#userDetails').slideToggle();
		});

		$("#select").multiselect({
			header: false,
			checkall:false
		});
		
		$(".keyyes").click(function() {
		
		if (jQuery(this).val()==1) {
			jQuery("#apikeybox").show();
			jQuery(".hidetableblock").show();
			jQuery(".unsubscription").show();
			jQuery(".listData").show();
			
		} else {
			jQuery("#apikeybox").hide();
			jQuery(".hidetableblock").hide();
			jQuery(".unsubscription").hide();
			jQuery(".listData").hide();
		}
		
	});

	var base_url = getBaseURL();

	$(".script").click(function() {
		var script = jQuery(this).val();
		var token = jQuery("#customtoken").val();
		$.ajax({
			type : "POST",
			async : false,
			url : base_url + "modules/mailin/ajax.php",
			data : "script=" + script + "&token=" + token,
			beforeSend : function() {
			$('#ajax-busy').show();
			},
			success : function(msg) {

			$('#ajax-busy').hide();
			}
		});
	});

	$(".smtptestclick").click(function() {
		var smtptest = jQuery(this).val();
		var token = jQuery("#customtoken").val();
		if (smtptest == 0) {
			$('#smtptest').hide();
		}
		if (smtptest == 1) {
			$('#smtptest').show();
		}
		$.ajax({
			type : "POST",
			async : false,
			url : base_url+"modules/mailin/ajaxsmtpconfig.php",
			data : "smtptest=" + smtptest + "&token=" + token,
			beforeSend : function() {
			$('#ajax-busy').show();
			},
			success : function(msg) {
			
			$('#ajax-busy').hide();
			//$('#message').html(msg);
			}
		});
	});

	var radios = $('input:radio[name=managesubscribe]:checked').val();
	
	if (radios==0) { 
		$('.managesubscribeBlock').hide();
	} else { 
		$('.managesubscribeBlock').show();
	}
	
	$(".managesubscribe").click(function() {
		var managesubscribe = jQuery(this).val();
		var token = jQuery("#customtoken").val();
		
		if (managesubscribe == 0) {
			$('.managesubscribeBlock').hide();
		}
		if (managesubscribe == 1) {
			$('.managesubscribeBlock').show();
		}
		$.ajax({
			type : "POST",
			async : false,
			url : base_url + "modules/mailin/ajaxsubscribeconfig.php",
			data : "managesubscribe=" + managesubscribe + "&token=" + token,
			beforeSend : function() {
				$('#ajax-busy').show();
			},
			success : function(msg) {
				$('#ajax-busy').hide();
			}
		});
	});
	
	var token = jQuery("#customtoken").val();
	
	$('<div id="ajax-busy"/> loading..')
		.css(
			{
			opacity : 0.5,
			position : 'fixed',
			top : 0,
			left : 0,
			width : '100%',
			height : $(window).height() + 'px',
			background : 'white url('+base_url+'modules/mailin/img/loader.gif) no-repeat center'
			}).hide().appendTo('body');

	// get site base url
	function getBaseURL() {
		var sBase = location.href.substr(0, location.href.lastIndexOf("/") + 1);
		var sp = sBase.split('/');
		var lastFolder = sp[ sp.length - 2 ];
		return sBase.replace(lastFolder+'/', '');
	}

	$('.ajax_contacts_href').live('click', function(e){
		var sBase = location.href.substr(0, location.href.lastIndexOf("/") + 1);
		var sp = sBase.split('/');
		var lastFolder = sp[ sp.length - 2 ];
		var base_url = sBase.replace(lastFolder+'/', '');
		var email = $(this).attr('email');
		var status = $(this).attr('status');
		var token = jQuery("#customtoken").val();
		
		$.ajax({
			type : "POST",
			async : false,
			url : base_url + "modules/mailin/ajaxcall.php",
			data : "email=" + email + "&newsletter=" + status + "&token=" + token,
			beforeSend : function() {
				$('#ajax-busy').show();
			},
			success : function(msg) {
				$('#ajax-busy').hide();		
			}
		});
			
		var page_no = $('#page_no').val();	
		loadData(page_no, token); // For first time page load
	});

	function loadData(page, token) {
		$.ajax({
			type : "POST",
			async : false,
			url : base_url
				+ "modules/mailin/ajaxemailresult.php",
			data : "page=" + page + "&token=" + token,
			beforeSend : function() {
				$('#ajax-busy').show();
			},
			success : function(msg) {
				$('#ajax-busy').hide();
				$(".midleft").html(msg);
				$(".midleft").ajaxComplete(
					function(event, request, settings) {
						$(".midleft").html(msg);
				});
			}
		});
	}

	loadData(1, token); // For first time page load
	// default
	// results

	$('.pagination li.active').livequery('click',function() {
		var page = $(this).attr('p');
		$('#page_no').val(page);
		loadData(page, token);
	});
	
	$('.toolTip').live('mouseover mouseout', function(e) {
		var title = $(this).attr('title');
		var offset = $(this).offset();

		if (e.type=='mouseover') {
			$('body').append(
				'<div id="tipkk" style="top:'
					+ offset.top
					+ 'px; left:'
					+ offset.left
					+ 'px; ">' + title
					+ '</div>');
			var tipContentHeight = $('#tipkk')
				.height() + 25;
			$('#tipkk').css(
				'top',
				(offset.top - tipContentHeight)
					+ 'px');
			}
		else if (e.type=='mouseout') {	
			$('#tipkk').remove();
		}
	});

});
