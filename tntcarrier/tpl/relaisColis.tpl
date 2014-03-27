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
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<link rel="stylesheet" href="{$shop_url}/modules/tntcarrier/css/ui.tabs.css" type="text/css" />
<link rel="stylesheet" href="{$shop_url}/modules/tntcarrier/css/ui.dialog.css" type="text/css" />
{if isset($js_include)}
    <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>
    <script type="text/javascript" src="{$shop_url}/modules/tntcarrier/js/relais.js"></script>
    <script type="text/javascript" src="{$shop_url}/js/jquery/jquery-ui-1.8.10.custom.min.js"></script>
{/if}
<script type="text/javascript">

var id_carrier = new Array();
var option_carrier = new Array();
var date_carrier = new Array();
var i = 0;
{foreach from=$services item=foo}
id_carrier[i] = '{$foo.id_carrier}';
option_carrier[i] = '{$foo.option}';
date_carrier[{$foo.id_carrier}] = '{$dueDate[$foo.id_carrier]}';
i++;
{/foreach}

{if $version < '1.5'}
{literal}
$().ready(function()
{
	$("[id*='id_carrier']").each(function(){
			var id_array = $(this).val();
			var indexTab = jQuery.inArray(id_array, id_carrier);
			if (indexTab >= 0)
			{
				if(option_carrier[indexTab].length > 1)
				{
					if (option_carrier[indexTab].charAt(1) == 'Z')
						$("#id_carrier"+$(this).val()).parent().parent().children(".carrier_infos").append("<br/><span onclick=\'displayHelpCarrier(\"http://www.tnt.fr/BtoC/page_domicile.html\")\' style=\'font-style:italic;cursor:pointer;color:blue;text-decoration:underline\'>+ d\'infos</span>");
					else if (option_carrier[indexTab].charAt(1) == 'D')
						$("#id_carrier"+$(this).val()).parent().parent().children(".carrier_infos").append("<br/><span onclick=\'displayHelpCarrier(\"http://www.tnt.fr/BtoC/page_relais-colis.html\")\' style=\'font-style:italic;cursor:pointer;color:blue;text-decoration:underline\'>+ d\'infos</span>");

					else
						$("#id_carrier"+$(this).val()).parent().parent().children(".carrier_infos").append("<br/><span onclick=\'displayHelpCarrier(\"http://www.tnt.fr/BtoC/page_popup.html\")\' style=\'font-style:italic;cursor:pointer;color:blue;text-decoration:underline\'>+ d\'infos</span>");
				}
				else
					$("#id_carrier"+$(this).val()).parent().parent().children(".carrier_infos").append("<br/><span onclick=\'displayHelpCarrier(\"http://www.tnt.fr/BtoC/page_popup.html\")\' style=\'font-style:italic;cursor:pointer;color:blue;text-decoration:underline\'>+ d\'infos</span>");
			}
		});
});

$("input[name='id_carrier']").click(function() {
    idcarrier = $(this).val();
    if (date_carrier[idcarrier] != undefined)
    {
	idCart = document.getElementById("cartRelaisColis").value;
	$.ajax({
	    type: "POST",
	    url: "{/literal}{$shop_url}{literal}/modules/tntcarrier/relaisColis/postRelaisData.php",
	    data: "id_cart="+idCart+"&due_date="+date_carrier[idcarrier]
	});
    }
    getAjaxRelais($("input[name='id_carrier']:checked").val());
    if (document.getElementById("tr_carrier_relais"))
    {
        var node = document.getElementById("tr_carrier_relais").parentNode;
        var father = node.parentNode;
        father.removeChild(node);
    }
});

function displayNewTable(response, id)
{
    var display = false
    for (x in id_carrier)
	if (id_carrier[x] == id)
	    display = true;
    if (!display)
	return false;
    $("#id_carrier"+id).parent().parent().after("<tr><td colspan='4' style='display:none' id='tr_carrier_relais'></td></tr>");
    $("#tr_carrier_relais").html(response);
    $("#tr_carrier_relais").slideDown('slow');
    tntRCInitMap();
    tntRCgetCommunes();
}
		
{/literal}
{else}
{literal}
$(document).ready(function()
{
	var chosenCarrier = $("input[name*='delivery_option[']:checked").val().split(',');
	getAjaxRelais(chosenCarrier[0]);
		$("[id*='delivery_option_']").each(function(){
			var id_array = $(this).val().split(',');
			var indexTab = jQuery.inArray(id_array[0], id_carrier);
			if (indexTab >= 0 && $("#tnt_popup"+id_array[0]).length <= 0)
			{
				if(option_carrier[indexTab].length > 1)
				{
					if (option_carrier[indexTab].charAt(1) == 'Z')
						$("[for='"+$(this).attr('id')+"'] .delivery_option_delay").append(" <span id=\'tnt_popup"+id_array[0]+"\' onclick=\'displayHelpCarrier(\"http://www.tnt.fr/BtoC/page_domicile.html\");return false;\' style=\'font-style:italic;cursor:pointer;color:blue;text-decoration:underline\'>+ d\'infos</span>");
					else if (option_carrier[indexTab].charAt(1) == 'D')
						$("[for='"+$(this).attr('id')+"'] .delivery_option_delay").append(" <span id=\'tnt_popup"+id_array[0]+"\' onclick=\'displayHelpCarrier(\"http://www.tnt.fr/BtoC/page_relais-colis.html\");return false;\' style=\'font-style:italic;cursor:pointer;color:blue;text-decoration:underline\'>+ d\'infos</span>");
					else
						$("[for='"+$(this).attr('id')+"'] .delivery_option_delay").append(" <span id=\'tnt_popup"+id_array[0]+"\' onclick=\'displayHelpCarrier(\"http://www.tnt.fr/BtoC/page_popup.html\");return false;\' style=\'font-style:italic;cursor:pointer;color:blue;text-decoration:underline\'>+ d\'infos</span>");
				}
				else
					$("[for='"+$(this).attr('id')+"'] .delivery_option_delay").append(" <span id=\'tnt_popup"+id_array[0]+"\' onclick=\'displayHelpCarrier(\"http://www.tnt.fr/BtoC/page_popup.html\");return false;\' style=\'font-style:italic;cursor:pointer;color:blue;text-decoration:underline\'>+ d\'infos</span>");
			}
		});
	});
	
	if ($('.opc-main-block').length == 0) {
		$("input[name*='delivery_option[']").click(function() {
			var id_array = $("input[name*='delivery_option[']:checked").val().split(',');
			if (document.getElementById("tr_carrier_relais"))
				$("#tr_carrier_relais").remove();
			getAjaxRelais(id_array[0]);
		});
	}
	function displayNewTable(response, id)
		{
			$("[id*='delivery_option_']").each(function(){
				var id_array = $(this).val().split(',');
				if (id_array[0] == id && $("#tr_carrier_relais").length <= 0)
				{
					$(this).next("[for*='delivery_option_']").after("<div style='display:none' id='tr_carrier_relais'></div>");
					$("#tr_carrier_relais").html(response);
					$("#tr_carrier_relais").css('padding', '4px');
					$("#tr_carrier_relais h3").css('margin', '0 -5px');
					$("#tr_carrier_relais").slideDown('slow');
					tntRCInitMap();
					tntRCgetCommunes();
				}
				});
		}
{/literal}
{/if}
{literal}
	function getAjaxRelais(id)
	{
		$.get(
			"{/literal}{$shop_url}{literal}/modules/tntcarrier/relaisColis.php?id_carrier="+id+"&idcart="+$("#cartRelaisColis").val(),
			function(response, status, xhr) 
			{
				/*if (status == "error") 
					$("#tr_carrier_relais").html(xhr.status + " " + xhr.statusText);*/
				$("#loadingRelais"+id).hide();
				if (status == 'success' && response != 'none')
				{
					displayNewTable(response, id);
				}
			}
		);
	}
	
	function displayHelpCarrier(src)
	{
		$("#tntHelpCarrier").css('height', $(document).height()+'px');
		$("#helpCarrierFrame").attr('src', src);
		$("#helpCarrierBlock").css('top', $(window).scrollTop()+'px');
		if ($(window).height() > 500)
		{
			var h = ($(window).height() - 520) / 2+'px';
			
			$("#helpCarrierBlock").css('margin-top', h);
		}
		else
			$("#HelpCarrierBlock").css('margin-top', '20px');
		$(".opc-main-block").css('position', 'static');
		$("#tntHelpCarrier").show();
	}
	
	function hideHelpCarrier()
	{
		$("#tntHelpCarrier").hide();
		$(".opc-main-block").css('position', 'relative');
	}
	
	function selectCities(token)
	{
		$.get(
			"{/literal}{$shop_url}{literal}/modules/tntcarrier/changeCity.php?city="+$("#citiesGuide").val()+"&id="+$("#cartRelaisColis").val()+"&token="+token,
			function(response, status, xhr) 
			{
				if (status == 'success' && response != 'none')
				{
					window.location.href = $("#reload_link").val();
				}
				else
					return false;
			}
		);
	}
{/literal}
</script>
<div id="tntHelpCarrier" style="display:none;position:absolute;width:100%;top:0px;left:0px;background:url('{$shop_url}img/macFFBgHack.png');z-index:10000">
	<div id="helpCarrierBlock" style="text-align:center;position:relative">
		<div style="width:720px;margin:auto;background-color:white">
		<span style="cursor:pointer;color:blue;text-decoration:underline;" onclick="hideHelpCarrier()">{l s='Close' mod='tntcarrier'}</span><br/>
		<iframe id="helpCarrierFrame" style="height:500px;width:700px;border:none;margin-top:5px">
		</iframe>
		</div>
	</div>
</div>
<input type="hidden" id="cartRelaisColis" value="{$id_cart}" name="cartRelaisColis" />

{if isset($error)}
{if isset($cityError)}
<div style="background-color: #FAE2E3;border: 1px solid #EC9B9B;line-height: 20px;margin: 0 0 10px;padding: 10px 15px;">{$cityError}</div>
{else}
	<h3>{$error}</h3>
	{l s='Postal Code' mod='tntcarrier'} : {$postalCode}
	<select id="citiesGuide" style="width:130px" onchange="selectCities('{$tnt_token}')">
		<option selected="selected">{l s='Choose' mod='tntcarrier'}</option>
	 {foreach from=$cities item=v}
		<option value='{$v}'>{$v}</option>
	 {/foreach}
	</select>
	{if isset($link)}
	<input type="hidden" value="{$redirect}" id="reload_link" name="reload_link"/>
	{/if}
{/if}
{/if}
