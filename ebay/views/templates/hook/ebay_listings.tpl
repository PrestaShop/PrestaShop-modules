<div id="ebayListings">
	<p class="center">
		<button class="button">{l s='See eBay listings' mod='ebay'}</button>
	</p>
</div>
<script type="text/javascript">
	// <![CDATA[
	var content_ebay_listings = $("#ebayListings");
	content_ebay_listings.bind('click', 'button', function(){
		$.ajax({
			url: module_dir+'ebay/ajax/getEbayListings.php',
			data: "token="+ebay_token+"&id_employee={$id_employee}",
			success: function(data)
			{
				content_ebay_listings.fadeOut(400, function(){
					$(this).html(data).fadeIn();
				})
			}
		});
	})
	var ebay_listings = parseInt("{$ebay_listings}");
	if (ebay_listings >= 1)
		$("#menuTab9").addClass('success');
	else
		$("#menuTab9").addClass('wrong');
	//]]>
</script>