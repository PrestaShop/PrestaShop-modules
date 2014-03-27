{*
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
*  @author Quadra Informatique <modules@quadra-informatique.fr>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<style type="text/css">
	.soBackward_compat_tab {literal}{ text-align: center; }{/literal}
	.soBackward_compat_tab a {literal}{ margin: 10px; }{/literal}
</style>

<a href="#" style="display:none" class="fancybox fancybox.iframe" id="soLink"></a>
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
        var soSellerId = "{$id_carrier_seller}";
	var soToken = "{$token}";
	var initialCost_label = "{$initialCost_label}";
	var initialCost = "{$initialCost}";
	var baseDir = '{$content_dir}';

		{foreach from=$inputs item=input key=name name=myLoop}
			soInputs.{$name} = "{$input|strip_tags|addslashes}";
		{/foreach}

		{literal}
		$('#soLink').fancybox({
				'width'				: 590,
				'height'			: 810,
				'autoScale'	 		: true,
				'centerOnScroll'	: true,
				'autoDimensions'	: false,
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'hideOnOverlayClick': false,
				'hideOnContentClick': false,
				'showCloseButton'	: true,
				'showIframeLoading' : true,
				'enableEscapeButton': true,
				'type'				: 'iframe',
				onStart : function() {
					$('#soLink').attr('href', baseDir+'modules/socolissimo/redirect.php' + serialiseInput(soInputs))
				},
				onClosed : function() {
					$.ajax({
						type: 'GET',
						url: baseDir+'/modules/socolissimo/ajax.php',
						async: false,
						cache: false,
						dataType : "json",
						data: "token=" + soToken,
						success: function(jsonData) {
							if (jsonData && jsonData.answer && typeof jsonData.answer != undefined && !opc) {
								if (jsonData.answer)
									$('#form').submit();
								else if (jsonData.msg.length)
									alert(jsonData.msg);
							}
						},
						error: function(XMLHttpRequest, textStatus, errorThrown) {
							alert('TECHNICAL ERROR\nDetails:\nError thrown: ' + XMLHttpRequest + '\n' + 'Text status: ' + textStatus);
						}
					});
				}
			});

			$(document).ready(function()
			{
				var interval;
                        $('#soLink').attr('href', baseDir+'modules/socolissimo/redirect.php' + serialiseInput(soInputs));
				// 1.4 way
				if (!soBwdCompat)
				{
					$($('#carrierTable input#id_carrier'+soCarrierId).parent().parent()).find('.carrier_price .price').html(initialCost_label+'<br/>'+initialCost);
					$($('#carrierTable input#id_carrier'+soCarrierId).parent().parent()).find('.carrier_price').css('white-space','nowrap');
					$('input[name=id_carrier]').change(function() {
						so_click();
					});
					so_click();
				}
				// 1.5 way
				else {
					$('input.delivery_option_radio').each(function()
					{
						if($(this).val() == soCarrierId+','){
							$(this).next().children().children().find('div.delivery_option_price').html(initialCost_label+'<br/>'+initialCost+' TTC');
						}
					});
					if (soCarrierId)
						so_click();
				}
			$('.delivery_option').each(function( ) {
				if ($(this).children('.delivery_option_radio').val() == '{/literal}{$id_carrier_seller}{literal},') {
					$(this).remove();
				}
			});
			$('#id_carrier{/literal}{$id_carrier_seller}{literal}').parent().parent().remove();

			});


		function so_click()
		{
			if (opc) {
				if (!already_select_delivery || !$('#edit_socolissimo').length)
					modifyCarrierLine();
			}
			else if ((!soBwdCompat && $('#id_carrier' + soCarrierId).is(':not(:checked)')) ||
				(soBwdCompat && soCarrierId == 0)) {
				$('[name=processCarrier]').unbind('click').live('click', function () {
					return true;
				});
			} else {
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
		if(soBwdCompat)
			var carrier = $('input.delivery_option_radio:checked');

		else {
			var carrier = $('input[name=id_carrier]:checked');
				var container = '#id_carrier' + soCarrierId;
		}

		if ((carrier.val() == soCarrierId) || (carrier.val() == soCarrierId+',')) {
			if(soBwdCompat)
				carrier.next().children().children().find('div.delivery_option_delay').append('<div><a class="exclusive_large" id="button_socolissimo" href="#" onclick="redirect();return;" >{/literal}{$select_label}{literal}</a></div>');
			else
				$(container).parent().siblings('.carrier_infos').append('<a class="exclusive_large" id="button_socolissimo" href="#" onclick="redirect();return;" >{/literal}{$select_label}{literal}</a>');
		} else {
			$('#button_socolissimo').remove();
		}
		if (already_select_delivery)
		{
			$(container).css('display', 'block');
			$(container).css('margin', 'auto');
			$(container).css('margin-top', '5px');
		} else
			if(soBwdCompat)
				$(container).css('display', 'none');
	}

	function redirect()
	{
		$('#soLink').attr('href',  baseDir+'modules/socolissimo/redirect.php' + serialiseInput(soInputs));
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
