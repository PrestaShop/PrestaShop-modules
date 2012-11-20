$(function() {
	$(".menuTabButton").click(function () {
	  $(".menuTabButton.selected").removeClass("selected");
	  $(this).addClass("selected");
	  $(".tabItem.selected").removeClass("selected");
	  $("#" + this.id + "Sheet").addClass("selected");
	});
	$("#osi_configuration_mode").change(function() {
		var mode = $("#osi_configuration_mode").val();
		if(mode == 2) {
			$("#ws_url").val("https://webservices-test.opensi.eu/cows/Gateway");
			$(".ws-freq").val("1");
		} else {
			$("#ws_url").val("https://webservices.opensi.eu/cows/Gateway");
			$(".ws-freq").val("10");
		}
	});
	$(".active_wso").change(function() {
	    var activewso = $(this).val();
	    if(activewso == 1) {
			$(this).parent().parent().css("color","#000");
			$(this).parent().parent().find(".ws-freq").css("background","#fff").css("color","#000");
	    } else {
			$(this).parent().parent().css("color","#999");
			$(this).parent().parent().find(".ws-freq").css("background","#fffff7").css("color","#999");
	    }
	});
	$(".active_wso").each(function() {
        var activewso = $(this).val();
        if(activewso == 1) {
			$(this).parent().parent().css("color","#000");
			$(this).parent().parent().find(".ws-freq").css("background","#fff").css("color","#000");
        } else {
			$(this).parent().parent().css("color","#999");
			$(this).parent().parent().find(".ws-freq").css("background","#fffff7").css("color","#999");
        }
	});
	$(".choose").click(function() {
		var whichradio = $(this).val();
		if(whichradio == 0) {
	        $(this).parent().find(".radio_attribute").removeAttr("disabled").css("background","#fff");
	        $(this).parent().find(".radio_feature").attr("disabled", "disabled").css("background","#fffff7");
		} else {
	        $(this).parent().find(".radio_attribute").attr("disabled", "disabled").css("background","#fffff7");
	        $(this).parent().find(".radio_feature").removeAttr("disabled").css("background","#fff");
		}
	});
});