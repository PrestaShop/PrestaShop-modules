/**
 * PrestaShop 1.4.x compatibility file
 */


function displayKialaPoint(content)
{
	$('#kiala').remove();
	$('#id_carrier' + KIALA_ID_CARRIER).parents('tr').after(content);
}

$('input[name=id_carrier]').change(function(event)
{
	if ($(this).val() == KIALA_ID_CARRIER)
		getKialaPoint();
	else
		displayKialaPoint('');
	if (!IS_OPC)
		updateCarrierInCart($(this).val());
});

function getKialaPoint()
{
	$.ajax(
	{
		type: 'POST',
		url: KIALA_MODULE_DIR + 'ajax.php',
		data: {'token' : KIALA_TOKEN,
			   'page_name' : PAGE_NAME},
		success: function(html)
		{
			displayKialaPoint(html);
		},
		error: function(xhr, ajaxOptions, thrownError)
		{
			//@TODO display error tpl with displaykialapoint()
			//console.log(thrownError);
			// Put debug to see error detail
		}
	});
}

function updateCarrierInCart(id_carrier)
{
	$.ajax(
	{
		type: 'POST',
		url: KIALA_MODULE_DIR + 'ajax.php',
		data: {'token' : KIALA_TOKEN,
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
		displayKialaPoint(
		  "<iframe src="+this.href+" width='540px' height='400px'>"
		  +"<p>Your browser does not support iframes.</p>"
		  +"</iframe>");

		/*$.fancybox(this.href
				,
				{
					//'autoDimensions'	: true,
					'width'						: 550,
					'height'					: 400,
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
				;*/

		event.preventDefault();
	});
});