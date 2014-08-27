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
{literal}
<script>
function fianetsceau_pagination(mode){
	
	var product_id = $("#fianetsceau_product_id").val();
	var total_page = $("#fianetsceau_total_pages").val();
	var current_page = $("#fianetsceau_current_page").text();
		
	var current_limit_min = $("#fianetsceau_current_limit_min").val();
	var nb_max_per_page = $("#fianetsceau_nb_max_comments_per_page").val();
	var total_page = $("#fianetsceau_total_pages").attr('value');		
	var token = $("#fianetsceau_token").attr('value');
	var url = $("#fianetsceau_script_path").attr('value');
	
	if(mode == 'next'){
		if(current_page == total_page){
			return false;
		}
		current_page++;
		current_limit_min = parseInt(current_limit_min)+parseInt(nb_max_per_page);	
	}
	else{
		if(current_page == 1){
			return false;
		}
		current_page--;
		current_limit_min = parseInt(current_limit_min)-parseInt(nb_max_per_page);
	}

	$("#fianetsceau_loader").show();
	$("#fianetsceau_current_page").empty();
	$("#fianetsceau_current_page").append(current_page);
	$("#fianetsceau_current_limit_min").val(current_limit_min);
	
	$.ajax({
		url: url, 
		type:'POST', 
		data: "traitement=pagination&limit_min="+current_limit_min+"&limit_max="+nb_max_per_page+"&product_id="+product_id+"&token="+token,
		cache:false, 
		success:function(reponse){
				$("#fianetsceau_loader").hide();
				$("#fianetsceau_comments").empty();
				$("#fianetsceau_comments").append(reponse);
		}
	})
}	
</script>
{/literal}

<br/>
<div id="fianetsceau_general_content">
	<div id="fianetsceau_global_note">
	<div class="fianetsceau_left_content_global_note"><span class="fianetsceau_global_note_text">{l s='Reviews collected by' mod='fianetsceau'}</span><br/><img src='{$logo_path|strval}' alt='' /></div>
	<div class="fianetsceau_center_content_global_note1"></div>
	<div class="fianetsceau_center_content_global_note2"><span class="fianetsceau_global_note_text">{l s='Overall rating' mod='fianetsceau'}</span><div class="fianetsceau_space"></div>{$view_global_note|strval}<div class="fianetsceau_space"></div>{l s='on' mod='fianetsceau'} {$nb_comments} {l s='customer reviews' mod='fianetsceau'}</div>
	<div class="fianetsceau_right_content_global_note"><span class="fianetsceau_positive_note">{if $entier_note == null}{$global_note|intval}{else}{$entier_note|intval}{/if}</span><span class="fianetsceau_positive_demie_note">{if $decimal_note|intval <> null},{$decimal_note|intval}{/if}</span>/5</div>
	</div>
	{$i = 1}
	<div id="fianetsceau_comments">
		{foreach from=$product_comment item=comment name=product_comment}
		<div class="fianetsceau_content">
			<div class="fianetsceau_left_content">
				{$comment.view_note|strval}<br/> {l s='by' mod='fianetsceau'} <span class="fianetsceau_capitalize">{$comment.firstname|strval}</span> <span class="fianetsceau_capitalize">{$comment.name|strval}</span><br/>{l s='at' mod='fianetsceau'} {$comment.date|strval}
			</div>
			<div class="fianetsceau_right_content">{$comment.comment|strval}</div>
		</div>
		{if $i != $size_array}
		<hr class="fianetsceau_hr" />
		{/if}
		{$i = $i+1}
		{/foreach}
	</div>
</div>

{if $nb_total_pages <> 1}
	<div id="fianetsceau_general_pagination">
		<div id="fianetsceau_pagination_next" class="fianetsceau_pagination_next" onclick="fianetsceau_pagination('next');">></div>
		<div id="fianetsceau_pagination_page">page <span id="fianetsceau_current_page">1</span>/{$nb_total_pages|intval}</div>
		<div id="fianetsceau_pagination_prev" class="fianetsceau_pagination_prev" onclick="fianetsceau_pagination('prev');"><</div>
		<div id="fianetsceau_loader" style="display: none;"><img src="{$img_loader|strval}" /></div>
	</div>
{/if}

<input id="fianetsceau_script_path" type="hidden" value="{$script_path|strval}" />
<input id="fianetsceau_token" type="hidden" value="{$token|strval}" />
<input id="fianetsceau_total_pages" type="hidden" value="{$nb_total_pages|intval}" />
<input id="fianetsceau_current_limit_min" type="hidden" value="{$current_limit_min|intval}" />
<input id="fianetsceau_nb_max_comments_per_page" type="hidden" value="{$nb_max_comments_per_page|intval}" />
<input id="fianetsceau_product_id" type="hidden" value="{$product_id|intval}" />