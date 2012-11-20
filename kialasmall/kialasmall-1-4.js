
function ksDisplayKialaPoint(content)
{
	$('#kiala').remove();
	$('#id_carrier' + KS_KIALA_ID_CARRIER).parents('tr').after(content);
}

function ksGetKialaPoint()
{
	$.ajax(
	{
		type: 'POST',
		url: KS_KIALA_MODULE_DIR + 'ajax.php',
		data: {'token' : KS_KIALA_TOKEN,
			   'page_name' : KS_PAGE_NAME},
		success: function(html)
		{
			ksDisplayKialaPoint(html);
		},
		error: function(xhr, ajaxOptions, thrownError)
		{
			//console.log(thrownError);
			// Put debug to see error detail
		}
	});
}

function ksUpdateCarrierInCart(id_carrier)
{
	$.ajax(
	{
		type: 'POST',
		url: KS_KIALA_MODULE_DIR + 'ajax.php',
		data: {'token' : KS_KIALA_TOKEN,
			   'id_carrier' : id_carrier},
		error: function(xhr, ajaxOptions, thrownError)
		{
			//console.log(thrownError);
			// Put debug to see error detail
		}
	});
}

$(document).ready(function()
{
	$('#search_link').live('click', function(event)
	{
		$.fancybox(this.href
				,
				{
					//'autoDimensions'	: true,
					'width'						: 800,
					'height'					: 600,
					'transitionIn'		: 'none',
					'transitionOut'		: 'none',
					'type' 				: 'iframe',
					'onComplete'			: function()
						{
							// Rewrite some css properties of Fancybox
							$('#fancybox-wrap').css('width', '');
							$('#fancybox-content').css('background-color', '');
							$('#fancybox-content').css('border', '');
						}
				})
				;

		event.preventDefault();
	});

	$('input[name=id_carrier]').change(function(event)
	{
		if ($(this).val() == KS_KIALA_ID_CARRIER)
			ksGetKialaPoint();
		else
			ksDisplayKialaPoint('');
		if (!KS_IS_OPC)
			ksUpdateCarrierInCart($(this).val());
	});
});