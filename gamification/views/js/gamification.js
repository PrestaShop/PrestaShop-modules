$(document).ready( function () {
	gamificationTasks();
});

function gamificationTasks()
{
	$('#gamification_notif').remove();
	$('#customer_messages_notif').after('<div id="gamification_notif" class="notifs"></div>');
	$.ajax({
		type: 'POST',
		url: admin_gamification_ajax_url,
		dataType: 'json',
		data: {
			controller : 'AdminGamification',
			action : 'gamificationTasks',
			ajax : true,
			id_tab : current_id_tab
		},
		success: function(jsonData)
		{
			for (var i in jsonData.advices_to_display.advices)
				if (jsonData.advices_to_display.advices[i].location == 'after')
					$(jsonData.advices_to_display.advices[i].selector).after(jsonData.advices_to_display.advices[i].html);
				else
					$(jsonData.advices_to_display.advices[i].selector).before(jsonData.advices_to_display.advices[i].html);
			
			initHeaderNotification(jsonData.header_notification);
			
			$('.gamification_fancybox').fancybox();
		}
	});
}

function initHeaderNotification(html)
{
	$('#gamification_notif').remove();
	$('#customer_messages_notif').after(html);
	$('#gamification_notif').click(function () {
		if ($('#gamification_notif_wrapper').css('display') == 'block')
		{
			$('#gamification_notif_wrapper').hide();
		}
		else
		{
			disabledGamificationNotification();
			$('.notifs_wrapper').hide();
			$('#gamification_notif_number_wrapper').hide();
			$('#gamification_notif_wrapper').show();
			$('#gamification_progressbar').progressbar({
				change: function() {
			        if (current_level_percent)
			        	$( ".gamification_progress-label" ).html( gamification_level+' '+current_level+' : '+$('#gamification_progressbar').progressbar( "value" ) + "%" );
			        else
			        	$( ".gamification_progress-label" ).html('');
			      },
	     	});
			$('#gamification_progressbar').progressbar("value", current_level_percent );
		}
	});
}


function disabledGamificationNotification()
{
	$.ajax({
		type: 'POST',
		url: admin_gamification_ajax_url,
		data: {
			controller : 'AdminGamification',
			action : 'disableNotification',
			ajax : true
		},
		success: function(jsonData)
		{
			$('#gamification_notif_number_wrapper').hide();
		}
	});
}

function initBubbleDescription()
{
	$('.badge_square').each( function () {
		if ($(this).children('.gamification_badges_description').text().length)
		{
			$(this).CreateBubblePopup({
				position : 'top',
				openingDelay:0,
				alwaysVisible: false,
				align	 : 'center',
				innerHtml: $(this).children('.gamification_badges_description').text(),
				innerHtmlStyle: { color:'#000',  'text-align':'center' },
				themeName: 'black',
				themePath: '../modules/gamification/views/jquerybubblepopup-themes'		 
			});
		}
	});
}


function filterBadge(type)
{
	group = '.'+$('#group_select_'+type+' option:selected').val();
	status = '.'+$('#status_select_'+type+' option:selected').val();
	level = '.'+$('#level_select_'+type+' option:selected').val();

	if (group == '.undefined')
		group = '';
	if (status == '.undefined')
		status = '';
	if (level == '.undefined')
		level = '';
	
	$('#list_'+type).isotope({filter: '.badge_square'+group+status+level, animationEngine : 'css'});
	
	if (!$('#list_'+type+' li').not('.isotope-hidden').length)
		$('#no_badge_'+type).fadeIn();
	else
		$('#no_badge_'+type).fadeOut();
}



