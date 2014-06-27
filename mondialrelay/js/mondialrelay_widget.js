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
*  @version  Release: $Revision: 16986 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
	function getNumeric(val) {
		var reg=new RegExp("[0-9]+","g");
		var res = reg.exec(val);
		if(isNaN(res)) 
			return 0;
		else 
			return res;
	}
	
	function checkToDisplayRelayList()
	{
		if (typeof PS_MRData != 'undefined')
		{
			//============================================================
			// auto display fancybox if radio already check
			//============================================================
			PS_MRSelectedRelayPoint['relayPointNum'] = PS_MRData.pre_selected_relay;
			// PS_VERSION < '1.5'
			if (PS_MRData.PS_VERSION < '1.5')
			{
				// Bind id_carrierX to an ajax call
				$.each(PS_MRData.carrier_list, function(i, carrier) {
					PS_MRCarrierMethodList[carrier.id_carrier] = carrier.id_mr_method;
					if ($('#id_carrier' + carrier.id_carrier).attr('checked')) {
						PS_MRSelectedRelayPoint['carrier_id'] = carrier.id_carrier;
						PS_MRDisplayWidget(carrier.id_carrier);
					}
				});
						
			}
			else if (PS_MRData.PS_VERSION >= '1.5' 
				&& typeof PS_MRData.carrier != 'undefined' 
			)
			{ // 1.5 way		
				var carrier_selected = $('input[class=delivery_option_radio]:checked').val();
				$.each(PS_MRData.carrier_list, function(i, carrier) {
					PS_MRCarrierMethodList[carrier.id_carrier] = carrier.id_mr_method;
					if (carrier.id_carrier+',' == carrier_selected || carrier.id_carrier == carrier_selected) {
					
						PS_MRSelectedRelayPoint['carrier_id'] = carrier.id_carrier; 
						PS_MRDisplayWidget(carrier.id_carrier);
					}
				});
			}
			//============================================================			
			// prevent propagation
			updateExtraCarrier = function(){
				return false;
			};
			// Handle input click of the other input to hide the previous relay point list displayed
			$('input[name=id_carrier], input.delivery_option_radio').click(function(e){
				
				if ( e.isPropagationStopped() ) {
					return false;
				}
				// refreshDeliveryOptions();
				overrideUpdateExtraCarrier($(this).val(), id_address);
				hideRelaySelectedBox($(this)); 
				e.stopPropagation(); 
			});
			
			// 1.5 OPC Validation - Warn user to select a relay point
			$('.payment_module a').live('click', function() {
				if (PS_MRData.PS_VERSION >= '1.5' && PS_MRData.carrier && PS_MRSelectedRelayPoint['carrier_id']!=0)
				{
					var _return = !(!PS_MRSelectedRelayPoint['carrier_id'] || !PS_MRSelectedRelayPoint['relayPointNum']);
					if (!_return)
						alert(PS_MRTranslationList['errorSelection']);
					return _return;
				}
			});
			
			// If MR carrier selected, check MR relay point is selected too
			$('input[name=processCarrier], button[name=processCarrier]').click(function(){
				var _return = !(PS_MRSelectedRelayPoint['carrier_id'] && !PS_MRSelectedRelayPoint['relayPointNum']);
				if (!_return)
					alert(PS_MRTranslationList['errorSelection']);
				return _return;
			});
		}
		return false;
	}
	
	function hideRelaySelectedBox(_this){
		// Hide MR input if one of them is not selected
		if (PS_MRCarrierMethodList[_this.val()] == undefined){
			// 1.5 way
			var id = getNumeric(_this.val());
			if(PS_MRCarrierMethodList[id] == undefined) { 
				displayPickupPlace(0);
				PS_MRSelectedRelayPoint['carrier_id'] = 0;
				PS_MRDisplayWidget(0);
				PS_MRSelectedRelayPoint['relayPointNum'] = 0;
			}
			else {
				PS_MRSelectedRelayPoint['carrier_id'] = id;
				PS_MRDisplayWidget(id);
			}
		}
		else {  
			PS_MRSelectedRelayPoint['carrier_id'] = _this.val();
			PS_MRDisplayWidget(_this.val());
		}
		return false;
	}
	
	function PS_MRDisplayWidget(carrier_id) {
		var dlv_mode = ''; 
		$.each(PS_MRData.carrier_list, function(i, carrier) {			
			if (carrier.id_carrier == carrier_id)
				dlv_mode = carrier.dlv_mode;
				PS_MRSelectedRelayPoint['relayPointNum'] = -1;		
				PS_MRAddSelectedCarrierInDB(carrier_id);				
			}
		);
		if(carrier_id) {				
			if(dlv_mode!='LD1' && dlv_mode!='LDS' && dlv_mode!='HOM') {
				loadMR_Map("#Zone_Widget", dlv_mode);
				$("#link_zone_widget").click();
				if(PS_MRSelectedRelayPoint['relayPointNum'] == -1)
					PS_MRSelectedRelayPoint['relayPointNum'] = 0;
			}
			else
				displayPickupPlace(0);
		}
		return false;
	}
	
	
	function overrideUpdateExtraCarrier(id_delivery_option, id_address)
	{
		var url = "";
		var method = 'updateExtraCarrier';
		var params = '';
		if(typeof(orderOpcUrl) !== 'undefined') {
			if(PS_MRData.PS_VERSION >= '1.5') {
				method = 'updateCarrierAndGetPayments';
				params += '&recyclable='+(($('#recyclable:checked').val() != undefined)? 1:0);
				params += '&gift='+(($('#gift:checked').val() != undefined)? 1:0);
				params += '&gift_message='+$('#gift_message').val();
				params += '&delivery_option['+id_address+']='+id_delivery_option;
			}
			url = orderOpcUrl;
		}
		else
			url = orderUrl;
		
		$.ajax({
			type: 'POST',
			headers: { "cache-control": "no-cache" },
			url: url + '?rand=' + new Date().getTime(),
			async: false,
			cache: false,
			dataType : "json",
			data: 'ajax=true'
				+'&method='+method
				+params
				+'&id_address='+id_address
				+'&id_delivery_option='+id_delivery_option
				+'&token='+static_token
				+'&allow_refresh=1',
			success: function(jsonData)
			{				
				$('#HOOK_EXTRACARRIER_'+id_address).html(jsonData['content']);
				return false;
			}
		});
		return false;
	}
	
	function displayPickupPlace(info) {
		var id = "relay_point_selected_box";
		
		if(!info) {
			$('#'+id).hide();
			return false;
		}
		
		if (PS_MRData.PS_VERSION < '1.5')
		{
			var block_carrier = $('#carrierTable');
		}
		else {
			var block_carrier = $('.delivery_options_address');
		}
		if($('#'+id).length !== 0) {
			$('#'+id).html('<h3>'+relay_point_selected_box_label+'</h3>'+info);
			$('#'+id).show();
		}
		else {
			$('<div id="'+id+'"><h3>'+relay_point_selected_box_label+'</h3>'+info+'</div>').insertAfter(block_carrier);
		}
		return false;
	}
	
	function PS_MRAddSelectedCarrierInDB(id_carrier)
	{
		// Make the request
		$.ajax({
			type: 'POST',
			url: _PS_MR_MODULE_DIR_ + 'ajax.php',
			data: {'method' : 'addSelectedCarrierToDB',
				'id_carrier' : id_carrier,
				'id_mr_method' : PS_MRCarrierMethodList[id_carrier],
				'mrtoken' : mrtoken},
			success: function(json)
			{
				return false;
			} 
		});
		return false;
	}