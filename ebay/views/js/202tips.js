
$(function() {
	$j("[data-dialoghelp], [data-inlinehelp]").each(function(){
		var attr = $j(this).attr('data-dialoghelp');
		var tooltip = $j(this).attr('data-inlinehelp');
		
		if (attr != undefined || tooltip != undefined){
			// Fancybox
			var fancybox = (attr != undefined && attr.length > 0 && attr[0] == "#");

			if (attr != undefined)
				attr = 'href="' + attr +'"';
			else
				attr = '';

			var content = "";
			// Img
			content += '<a ' + attr + ' class="' + (fancybox === true ? 'fancybox' : '') + ' ' + (tooltip ? 'tooltip' : '')  + '" title="' + (tooltip ? tooltip : '') + '" target="_blank">';
			content += ' <img src="../img/admin/help.png" alt="" />';
			content += '</a>';
			// Insert
			$j(this).after(content);
			// Init
			$j(".fancybox").fancybox({
    		});
			$j('.tooltip').tooltipster({
	    		position : 'right'
	    	});
		}
	});
});