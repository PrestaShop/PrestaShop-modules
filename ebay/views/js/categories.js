function loadCategoryMatch(id_category) {
	$.ajax({
		async: false,
		url: module_dir + 'ebay/ajax/loadCategoryMatch.php?token=' + ebay_token + '&id_category=' + id_category + '&time=' + module_time + '&ch_cat_str=' + ebay_l['no category selected'],
		success: function(data) { $("#categoryPath" + id_category).html(data); }
	});
}

function changeCategoryMatch(level, id_category) {
	var levelParams = "&level1=" + $("#categoryLevel1-" + id_category).val();
	if (level > 1) levelParams += "&level2=" + $("#categoryLevel2-" + id_category).val();
	if (level > 2) levelParams += "&level3=" + $("#categoryLevel3-" + id_category).val();
	if (level > 3) levelParams += "&level4=" + $("#categoryLevel4-" + id_category).val();
	if (level > 4) levelParams += "&level5=" + $("#categoryLevel5-" + id_category).val();

	$.ajax({
		url: module_dir + 'ebay/ajax/changeCategoryMatch.php?token=' + ebay_token + '&id_category=' + id_category + '&time=' + module_time + '&level=' + level + levelParams + '&ch_cat_str=' + ebay_l['no category selected'],
		success: function(data) { $("#categoryPath" + id_category).html(data); }
	});
}

var loadedCategories = new Array();
function showProducts(id_category) {
	var elem = $('#show-products-switch-' + id_category);
	if (elem.attr('showing') == true) 
	{
		$('.product-row[category=' + id_category +']').hide();
		elem.attr('showing', 0);
		elem.html('&#9654;');
	} 
	else 
	{
		elem.attr('showing', 1);
		elem.html('&#9660;');
		
		if (loadedCategories[id_category])
			$('.product-row[category=' + id_category +']').show();
		else
		{
			$('<img src="' + module_path + 'views/img/loading-small.gif" id="loading-' + id_category +'" alt="" />').insertAfter(elem);

			$.ajax({
				dataType: 'json',
				url: module_dir + 'ebay/ajax/getProducts.php?category=' + id_category,
				success: function(products) { 
					loadedCategories[id_category] = true;
					for (var i in products)
					{
						product = products[i];

						$('#category-' + id_category).after('<tr class="product-row ' + (i%2 == 0 ? 'alt_row':'') + '" category="' + id_category + '"> \
							<td >' + product.name + '</td> \
							<td ></td> \
              <td></td> \
							<td class="center"> \
								<input name="showed_products[' + product.id + ']" type="hidden" value="1" /> \
								<input onchange="toggleSyncProduct(this)" class="sync-product" product="' + product.id + '" name="to_synchronize[' + product.id + ']" type="checkbox" ' + (product.blacklisted == 1 ? '' : 'checked') + ' /> \
							</td> \
              <td align="center"><input class="extra_images" id="extra_images_' + product.id + '" name="extra_images[' + product.id + ']" type="number" value="' + product.extra_images + '" min="0" max="99" /></td> \
						</tr>');
					}
					$('#loading-' + id_category).remove();							
				}
			});
		}
	}
}

function toggleSyncProduct(obj)
{
  var product_id = $(obj).attr('product');
  //$('#extra_images_' + product_id).prop('disabled', !$(obj).is(':checked'));
}

$(document).ready(function(){
	$.ajax({
		url: module_dir + "ebay/ajax/loadTableCategories.php?token=" + ebay_token + "&id_lang=" + id_lang + '&ch_cat_str=' + ebay_l['no category selected'] + '&ch_no_cat_str=' + ebay_l['no category found'] + '&not_logged_str=' + ebay_l['You are not logged in'],
		success : function(data) { $("form#configForm2 table tbody #removeRow").remove(); $("form#configForm2 table tbody").html(data); }
	});
	
	$("#configForm2SuggestedCategories input[type=submit]").click(function(){
		$('<div class="center"><img src="' + module_path + 'views/img/loading-small.gif" alt="" />' + ebay_l['thank you for waiting'] + '</div>').insertAfter($(this));
		$(this).fadeOut();
		$.ajax({
			url: module_dir + "ebay/ajax/suggestCategories.php?token=" + ebay_token + "&id_lang=" + id_lang + '&not_logged_str=' + ebay_l['You are not logged in'] + '&settings_updated_str=' + ebay_l['Settings updated'],
			success : function(data) { window.location.href = window.location.href + "&conf=6"; }
		});
		return false;
	});
  
  $('#update-all-extra-images').click(function() {
    var val = $('#all-extra-images-selection').val();
    $('#all-extra-images-value').val(val);
    $('.extra_images').val(val);
  })
  
});
