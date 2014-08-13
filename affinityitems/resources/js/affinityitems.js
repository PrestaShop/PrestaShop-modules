/**
* 2014 Affinity-Engine
*
* NOTICE OF LICENSE
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade AffinityItems to newer
* versions in the future. If you wish to customize AffinityItems for your
* needs please refer to http://www.affinity-engine.fr for more information.
*
*  @author    Affinity-Engine SARL <contact@affinity-engine.fr>
*  @copyright 2014 Affinity-Engine SARL
*  @license   http://www.gnu.org/licenses/gpl-2.0.txt GNU GPL Version 2 (GPLv2)
*  International Registered Trademark & Property of Affinity Engine SARL
*/

function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function deleteCookie(name) {
    document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/';
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function postAction(action, object) {
	if(!object.recoType) {
		$.ajax({
			type: 'POST',
			url: baseDir + 'modules/affinityitems/ajax/action.php',
			data: {productId: object.productId, action: action },
			async:true,
			dataType: 'json',
			complete: function(e,t,n) {}
		});
	} else {
		$.ajax({
			type: 'POST',
			url: baseDir + 'modules/affinityitems/ajax/action.php',
			data: {recoType : object.recoType, productId: object.productId, action: action },
			async:true,
			dataType: 'json',
			complete: function(e,t,n) {}
		});
		deleteCookie('aelastreco');
	}
}

$(document).ready(function() {
	if(readCookie("aesync") != "true") {
		$.ajax({
			type: 'POST',
			url: baseDir + 'modules/affinityitems/ajax/synchronize.php',
			data: {"synchronize" : true},
			async:true,
			dataType: 'json',
			complete: function(e,t,n) {}
		});
		createCookie("aesync", "true", false);
	}

	/*	
		Read && Rebound
	*/

	var aetimestamp = readCookie('aetimestamp');
	var aelastreco = readCookie('aelastreco');
	var aenow = new Date().getTime();

	if($('#product_page_product_id').val()){
		var aetimer = setInterval((function(){
			clearInterval(aetimer);
			postAction("read", {productId : $('#product_page_product_id').val()});
		}), 4000);
		createCookie('aetimestamp', (aenow+"."+$('#product_page_product_id').val()), 1);
	}

	if(aetimestamp){
		aetimestamp = aetimestamp.split('.');
		var diff = aenow - aetimestamp[0];
		if(diff < 4000) {
			postAction("rebound", {productId : aetimestamp[1]});
		}
	}

	/*	
		Click on recommendations
	*/

	if(aelastreco) {
		aelastreco = aelastreco.split('.');
		postAction("trackRecoClick", {recoType : aelastreco[1], productId : aelastreco[2]});
	}

});