$(document).ready(function()
{
	// Display Kiala service description
	$('#more_info').fancybox({
			'width'				: 600,
			'height'			: 400,
			'onStart' : function(){$('#info_content').show();},
			'onCleanup' : function(){$('#info_content').hide();}
	});
});