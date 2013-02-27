<script type="text/javascript" src="https://d3jaattgax33fv.cloudfront.net/js/jqplugin.js"></script>
<script type="text/javascript" charset="utf-8">
{literal}
var $itembase_jq = jQuery;

$itembase_jq(document).ready(function($itembase_jq) {

	var ibEmbedHost = '{/literal}{$ibembedserver}{literal}';
	var ibHost = '{/literal}{$ibhostserver}{literal}';
	var ibData = {/literal}{$ibdatajson};{literal}

	// Method to add click funcitonality
	function addClickListener() {
		$itembase_jq('#itembasecontainer .js-trackButton').click(function(event) {
			if (!$itembase_jq(this).hasClass('disabled')) {
				if ($itembase_jq('#itembase_data').length==0) {
					$itembase_jq('#itembasecontainer').append('<form id="itembaseform" method="post" action="'+ibHost+'/api/add_to_my_collection" target="_blank"><textarea id="itembase_data" name="data">{/literal}{$ibdatajson|addslashes}{literal}</textarea><input type="submit" value="" cols="1" rows="1" style="width=1px;height:1px;visibility:hidden;" /></form>');
					};
				document.getElementById('itembaseform').submit();
				$itembase_jq('#itembase_data').hide();
			} else {
				$itembase_jq('label.red').removeClass('hidden');
			}
			return false;
		});
	}

	// Method to add a single product entry
	function addProductEntry(currentTr, currentIndex, currentProduct) {
		var productName = currentProduct.name[currentProduct.presta_lang_id];
		if (productName.length > 25) {
			productName = productName.substr(0, 25) + '&hellip;';
		}
		var productPrice = parseFloat(currentProduct.price);
		productPrice = productPrice.toFixed(2);
		currentTr.attr('id', 'product'+currentIndex);
		
		$itembase_jq('#itembasecontainer .js-productTable').append(currentTr);
		$itembase_jq('#itembasecontainer #product'+currentIndex+' .js-ibShopName').html(ibData.shop_name.length > 13 ? ibData.shop_name.substr(0, 13)+'&hellip;' : ibData.shop_name);

		// Some product specific information is optional
		if (ibPluginOptions.display_product_names)
			$itembase_jq('#itembasecontainer #product'+currentIndex+' .js-ibProductName').html(productName);
		if (ibPluginOptions.display_product_images) {
			if (!(currentProduct.pic_thumb == '' || currentProduct.pic_thumb == null)) {
				$itembase_jq('#itembasecontainer #product'+currentIndex+' .js-ibProductImage img').attr('src', currentProduct.pic_thumb);
			}
		}
		if (ibPluginOptions.display_purchase_date)
			$itembase_jq('#itembasecontainer #product'+currentIndex+' .js-ibPurchaseDate').html(ibData.purchase_date);
		if (ibPluginOptions.display_product_prices)
			$itembase_jq('#itembasecontainer #product'+currentIndex+' .js-ibProductPrice').html(productPrice+' '+ibData.currency);
		if (ibPluginOptions.display_product_categories)
			$itembase_jq('#itembasecontainer #product'+currentIndex+' .js-ibProductCategory').html();
		if (ibPluginOptions.display_product_quantities)
			$itembase_jq('#itembasecontainer #product'+currentIndex+' .js-ibProductQuantity').html(currentProduct.quantity);
		if (ibPluginOptions.display_product_descriptions)
			$itembase_jq('#itembasecontainer #product'+currentIndex+' .js-ibProductDescription').html(currentProduct.description[currentProduct.presta_lang_id]);
	}

	// Method to add plugin data
	function renderPlugin(data) {
		{/literal}{if !$ibtop}$itembase_jq('#itembasemarker').parent().append('<div id="itembasecontainer"></div>');{/if}{literal}
		$itembase_jq('#itembasecontainer').html('');
		$itembase_jq('#itembasecontainer').append(data);

		// Set order data locally
		var trData = $itembase_jq('#itembasecontainer .js-productTable tr.js-productEntry');
		$itembase_jq('#itembasecontainer .js-productTable tr.js-productEntry').remove();

		if (ibPluginOptions.type == 'single') {
			addProductEntry($itembase_jq(trData), 0, ibData.products[0]);
			if (ibData.products.length > 1) {
				$itembase_jq('.js-ibMoreItemsNumber').html(ibData.products.length - 1);
				$itembase_jq('.js-ibMoreItems').attr('style', '');
			}
		} else {
			$itembase_jq.each(ibData.products, function(currentIndex, currentProduct) {
				addProductEntry($itembase_jq(trData), currentIndex, currentProduct);
			});
		}
		addClickListener();
	}

	// Method to load plugin data with JSONP
	function refreshPluginData() {
		$itembase_jq.ajax({
			data: {pluginVersion: '{/literal}{$ibpluginversion}{literal}'},
			dataType: 'jsonp',
			jsonp: 'ib_callback',
			url: ibEmbedHost + '/embed/confirm/{/literal}{$ibdata.access_token}{literal}/{/literal}{$ibdata.lang}{literal}',
			success: function (data) {
				renderPlugin(data.response);
				sendOrderData();
			}
		});
	}

	// Method to save order data
	function sendOrderData() {
		var orderData = new Object();
		for(i in ibData) {
			if(i != 'email' && i != 'firstname' && i != 'lastname' && i != 'street' && i != 'zip' && i != 'city' && i != 'state' && i != 'country' && i != 'phone' && i != 'fax' && i != 'customer_id') {
				orderData[i] = ibData[i];
			}
		}
		if(ibPluginOptions.send_email) {
			orderData['email'] = ibData['email'];
		}
		orderData['anonymouse_order'] = 1;
		$itembase_jq.ajax({
			type: 'POST',
			data: {data: orderData},
			url: ibHost + '/api/add_to_my_collection'
		});
	}

	refreshPluginData();
});

jQuery.noConflict(true);
{/literal}
</script>
<div id="{if $ibtop}itembasecontainer{else}itembasemarker{/if}"></div>