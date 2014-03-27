/* Fancybox */
$(document).ready(function(){
	$("a.authorizeaim-video-btn").live("click", function(){
	$.fancybox({
		"type" : "iframe",
		"href" : "//www.youtube.com/embed/8SQ3qst0_Pk?&rel=0&autoplay=1&origin=http://'.Configuration::get('PS_SHOP_DOMAIN').'",
		"swf": {"allowfullscreen":"true", "wmode":"transparent"},
		"overlayShow" : true,
		"centerOnScroll" : true,
		"speedIn" : 100,
		"speedOut" : 50,
		"width" : 853,
		"height" : 480
		});
	return false;
	});
})