{*
* 2007-2012 PrestaShop
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
*  @copyright  2007-2012 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<style type="text/css">
	.soBackward_compat_tab {literal}{ text-align: center; }{/literal}
	.soBackward_compat_tab a {literal}{ margin: 10px; }{/literal}
</style>

<a href="#" style="display:none" id="soLink"></a>
{if isset($opc) && $opc}
<script type="text/javascript">
	var opc = true;
</script>
{else}
<script type="text/javascript">
	var opc = false;
</script>
{/if}
{if isset($already_select_delivery) && $already_select_delivery}
<script type="text/javascript">
	var already_select_delivery = true;
</script>
{else}
<script type="text/javascript">
	var already_select_delivery = false;
</script>
{/if}
<script type="text/javascript">
var soInputs = new Object();
var soBwdCompat = "{$SOBWD_C}";
var soCarrierId = "{$id_carrier}";
var soToken = "{$token}";

{foreach from=$inputs item=input key=name name=myLoop}
		soInputs.{$name} = "{$input|strip_tags|addslashes}";
{/foreach}

{literal}
	$('#soLink').fancybox({
			'width'					: 590,
			'height'				: 810,
		    'autoScale'     		: true,
		    'centerOnScroll'		: true,
		    'autoDimensions'		: false,
		    'transitionIn'			: 'none',
			'transitionOut'			: 'none',
			'hideOnOverlayClick'	: false,
			'hideOnContentClick'	: false,
			'showCloseButton'		: true,
			'showIframeLoading' 	: true,
			'enableEscapeButton'	: true,
			'type'					: 'iframe',
			onStart		:	function() {
				$('#soLink').attr('href', 'modules/socolissimo/redirect.php' + serialiseInput(soInputs))
			},
			onClosed    :   function() {
         	   $.ajax({
			       type: 'GET',
			       url: baseDir+'/modules/socolissimo/ajax.php',
			       async: false,
			       cache: false,
			       dataType : "json",
			       data: "token=" + soToken,
			       success: function(jsonData)
			       {
			       		if (jsonData && jsonData.answer && typeof jsonData.answer != undefined && !opc)
					      {
						      if (jsonData.answer)
							      $('#form').submit();
						      else if (jsonData.msg.length)
						        alert(jsonData.msg);
					      }
			       },
			       error: function(XMLHttpRequest, textStatus, errorThrown)
				   {
				   		alert('TECHNICAL ERROR\nDetails:\nError thrown: ' + XMLHttpRequest + '\n' + 'Text status: ' + textStatus);
				   }
			   });
        	}
		});

		$(document).ready(function()
		{
			var interval;

			// 1.4 way
			if (!soBwdCompat)
			{
				$('input[name=id_carrier]').change(function() {
					so_click();
				});
				so_click();
			}
			// 1.5 way
			else if (soCarrierId)
				so_click();
		});


	function so_click()
	{
		if (opc)
		{
			if (!already_select_delivery || !$('#edit_socolissimo').length)
				interval = setInterval(function()
					{
						modifyCarrierLine();
					},100);
		}
		else if ((!soBwdCompat && $('#id_carrier' + soCarrierId).is(':not(:checked)')) ||
			(soBwdCompat && soCarrierId == 0))
		{
			$('[name=processCarrier]').unbind('click').live('click', function () {
				return true;
			});
		}
		else
		{
			$('[name=processCarrier]').unbind('click').live('click', function () {
				if (($('#id_carrier' + soCarrierId).is(':checked')) || ($('.delivery_option_radio:checked').val() == soCarrierId+','))
				{
					if (acceptCGV())
						$("#soLink").trigger("click");
					return false;
				}
				return true;
			});
		}
	}

function modifyCarrierLine()
{
	var carrier = $('input.delivery_option_radio:checked');
	var container = '#id_carrier' + soCarrierId;

	if (soBwdCompat && soCarrierId > 0)
	{
		var carrier_block = carrier.parent('div.delivery_option');

			// Simulate 1.4 table to store the fetched relay point
			$(carrier_block).append(
				'<div><table width="' + $(carrier_block).width() + '"><tr>'
					+	  '<td class="soBackward_compat_tab"><input type="hidden" id="id_carrier' + soCarrierId + '" value="' + soCarrierId + '" /></td>'
					+ '</tr></table></div>');
	}

	if ($('#button_socolissimo').length != 0)
	{
		clearInterval(interval);
		// delete interval value
		interval = null;
	}

	$('#button_socolissimo').remove();

	if ((carrier.val() == soCarrierId) || (carrier.val() == soCarrierId+',')) {
		$(container).parent().prepend('<a style="margin-left:5px;" class="exclusive" id="button_socolissimo" href="#" onclick="redirect();return;" >{/literal}{$select_label}{literal}</a>');
	}

	if (already_select_delivery)
	{
		$(container).css('display', 'block');
		$(container).css('margin', 'auto');
		$(container).css('margin-top', '5px');
	}
	else
		$(container).css('display', 'none');
}

function redirect()
{
	$('#soLink').attr('href', 'modules/socolissimo/redirect.php' + serialiseInput(soInputs));
	$("#soLink").trigger("click");
	return false;
}

function serialiseInput(inputs)
{
	var str = '?first_call=1&';
	for ( var cle in inputs )
   		str += cle + '=' + inputs[cle] + '&';
	return (str + 'gift=' + $('#gift').attr('checked') + '&gift_message='+ $('#gift_message').attr('value'));
}

{/literal}
</script>
