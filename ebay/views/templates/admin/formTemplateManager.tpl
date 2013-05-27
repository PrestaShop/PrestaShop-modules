<form action="{$action_url}" method="post" class="form" id="configForm3">
	<fieldset style="border: 0">
		<h4>{l s='You can customise the template for your products page on eBay' mod='ebay'} :</h4>
		<p>{l s='On eBay, your products are presented in templates which you can design. A good template should:' mod='ebay'}</p>
		<ul style="padding-left:15px;">
			<li>{l s='Look more professional to consumers helping to differentiate from other merchants' mod='ebay'}</li>
			<li>{l s='Give customers all needed information' mod='ebay'}</li>
		</ul>
		<br/>
		<textarea class="rte" cols="100" rows="50" name="ebay_product_template">{$ebay_product_template}</textarea><br />
		{if $is_one_dot_three}
			<script type="text/javascript" src="{$base_uri}js/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
			<script type="text/javascript">
					tinyMCE.init({
						mode : "textareas",
						theme : "advanced",
						plugins : "safari,pagebreak,style,layer,table,advimage,advlink,inlinepopups,media,searchreplace,contextmenu,paste,directionality,fullscreen",
						// Theme options
						theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
						theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,,|,forecolor,backcolor",
						theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,media,|,ltr,rtl,|,fullscreen",
						theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,pagebreak",
						theme_advanced_toolbar_location : "top",
						theme_advanced_toolbar_align : "left",
						theme_advanced_statusbar_location : "bottom",
						theme_advanced_resizing : false,
						content_css : "{$base_uri}themes/{$theme_name}/css/global.css",
						document_base_url : "{$base_uri}",
						width: "850",
						height: "800",
						font_size_style_values : "8pt, 10pt, 12pt, 14pt, 18pt, 24pt, 36pt",
						// Drop lists for link/image/media/template dialogs
						template_external_list_url : "lists/template_list.js",
						external_link_list_url : "lists/link_list.js",
						external_image_list_url : "lists/image_list.js",
						media_external_list_url : "lists/media_list.js",
						elements : "nourlconvert",
						convert_urls : false,
						language : "{$language}"
					});
			</script>			
		{elseif $is_one_dot_five}
			<script type="text/javascript">	
				var iso = '{$iso}';
				var pathCSS = '{$theme_css_dir}';
				var ad = '{$ad}';
			</script>
			<script type="text/javascript" src="{$base_uri}js/tiny_mce/tiny_mce.js"></script>
			<script type="text/javascript" src="{$base_uri}js/tinymce.inc.js"></script>
			<script type="text/javascript">
				$(document).ready(function(){
					tinySetup({
						editor_selector :"rte",
						theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull|cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,undo,redo",
						theme_advanced_buttons2 : "link,unlink,anchor,image,cleanup,code,|,forecolor,backcolor,|,hr,removeformat,visualaid,|,charmap,media,|,ltr,rtl,|,fullscreen",
						theme_advanced_buttons3 : "",
						theme_advanced_buttons4 : ""
					});
				});
			</script>
		{else}
			<script type="text/javascript">
				var iso = '{$is_type_mce}';
				var pathCSS = '{$theme_css_dir}';
				var ad = '{$ad}';
			</script>
			<script type="text/javascript" src="{$ps_js_dir}/tiny_mce/tiny_mce.js"></script>
			<script type="text/javascript" src="{$ps_js_dir}/tinymce.inc.js"></script>
			<script>
				tinyMCE.settings.width = 850;
				tinyMCE.settings.height = 800;
				tinyMCE.settings.extended_valid_elements = "iframe[id|class|title|style|align|frameborder|height|longdesc|marginheight|marginwidth|name|scrolling|src|width]";
				tinyMCE.settings.extended_valid_elements = "link[href|type|rel|id|class|title|style|align|frameborder|height|longdesc|marginheight|marginwidth|name|scrolling|src|width]";
			</script>			
		{/if}
	</fieldset>
	<div class="margin-form"><input class="button" name="submitSave" value="{l s='Save' mod='ebay'}" type="submit"></div>
</form>		