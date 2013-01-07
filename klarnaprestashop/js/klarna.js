$('li[class^="klarna_flag_"]').click(function()
	{
		var country = $(this).attr('class').replace('klarna_flag_', '');
		$('.klarna_form_'+country).toggle();
		if ($('.klarna_form_'+country).is(":visible"))
		$('.klarna_form_'+country).append('<input type="hidden" name="activate'+country+'" value="on" id="klarna_activate'+country+'"/>');
	else
		$('#klarna_activate'+country).remove();
});

$(document).ready(function(){
    var height = 0;
    $('.klarna-blockSmall').each(function(){
	if (height < $(this).height())
	    height = $(this).height();
    });

    $('.klarna-blockSmall').css({'height' : $('.klarna-blockSmall').css('height', height+'px')});
});