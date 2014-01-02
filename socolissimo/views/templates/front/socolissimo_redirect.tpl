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
<script type="text/javascript">
	var soInputs = new Object();
	var soBwdCompat = "{$SOBWD_C}";
	var initialCost_label = "{$initialCost_label}"
	var initialCost = "{$initialCost}";
	var soCarrierId = "{$id_carrier}";
	var baseDir = '{$content_dir}';

	{foreach from=$inputs item=input key=name name=myLoop}
		soInputs.{$name} = "{$input|strip_tags|addslashes}";
	{/foreach}

	{literal}
	function change_action_form()
	{
		if(!soBwdCompat) {
			if ($('#id_carrier{/literal}{$id_carrier}{literal}').is(':not(:checked)'))
				$('#form').attr("action", 'order.php');
			else
				$('#form').attr("action", baseDir+'modules/socolissimo/redirect.php' + serialiseInput(soInputs));
		} else {
			if ($("input[name*='delivery_option[']:checked").val().replace(",", "") != soCarrierId) {
				$('#form').attr("action", 'order.php');
			} else {
				$('#form').attr("action", baseDir+'modules/socolissimo/redirect.php' + serialiseInput(soInputs));
			}
		}
	}

	$(document).ready(function() {
         $('.delivery_option').each(function( ) {
                if ($(this).children('.delivery_option_radio').val() == '{/literal}{$id_carrier_seller}{literal},') {
                    $(this).remove();
                            }
           });
        $('#id_carrier{/literal}{$id_carrier_seller}{literal}').parent().parent().remove();
		if(!soBwdCompat){
			$($('#carrierTable input#id_carrier'+soCarrierId).parent().parent()).find('.carrier_price .price').html(initialCost_label+'<br/>'+initialCost);
			$($('#carrierTable input#id_carrier'+soCarrierId).parent().parent()).find('.carrier_price').css('white-space','nowrap');
		} else {
			$('input.delivery_option_radio').each(function() {
				if($(this).val() == soCarrierId+',') {
					$(this).next().children().children().find('div.delivery_option_price').html(initialCost_label+'<br/>'+initialCost+' TTC');
				}
			});
		}

		$('input[name=id_carrier]').change(function() {
			change_action_form();
		});
		change_action_form();
       });

	function serialiseInput(inputs) {
		var str = '?first_call=1&';
		for ( var cle in inputs ) {
			str += cle + '=' + inputs[cle] + '&';
                }
		return (str + 'gift=' + $('#gift').attr('checked') + '&gift_message='+ $('#gift_message').attr('value'));
	}
	{/literal}

</script>

