<script type="text/javascript">
	{if universal_analytics eq true}
		{literal}
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
		{/literal}
			
		ga('create', '{$ganalytics_id}'{if isset($pageTrack)}, '{$pageTrack}'{/if});
		
		{if $isOrder eq true}
			ga('require', 'ecommerce', 'ecommerce.js'); 
		{else}
			ga('send', 'pageview');	
		{/if}

		{if $isOrder eq true}
			ga('ecommerce:addTransaction', {
				'id': '{$trans.id}',
				'store': '{$trans.store}',
				'total': '{$trans.total}',
				'tax': '{$trans.tax}',
				'shipping': '{$trans.shipping}',
				'city': '{$trans.city}',
				'state':'{$trans.state}',
				'country': '{$trans.country}',
				'currency': 'EUR'
			});

			{foreach from=$items item=item}
				ga('ecommerce:addItem', {
				   'id': '{$item.OrderId}',
				   'sku': '{$item.SKU}',
				   'name': '{$item.Product}',
				   'category': '{$item.Category}',
				   'price': '{$item.Price}',
				   'quantity': '{$item.Quantity}'
				});
			{/foreach}
			ga('ecommerce:send');
		{/if}
	{else}
		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', '{$ganalytics_id}']);
		// Recommended value by Google doc and has to before the trackPageView
		_gaq.push(['_setSiteSpeedSampleRate', 5]);
		
		_gaq.push(['_trackPageview'{if isset($pageTrack)}, '{$pageTrack}'{/if}]);
		
		{if $isOrder eq true}			{* If it's an order we need more data for stats *}
			_gaq.push(['_addTrans',
				'{$trans.id}', {* order ID - required *}
				'{$trans.store}', {* affiliation or store name *}
				'{$trans.total}', {* total - required *}
				'{$trans.tax}', {* tax *}
				'{$trans.shipping}', {* shipping *}
				'{$trans.city}', {* city *}
				'{$trans.state}', {* state or province *}
				'{$trans.country}' {* country *}
			  ]);
			
				{foreach from=$items item=item}
					_gaq.push(['_addItem',
						'{$item.OrderId}', {* order ID - required *}
						'{$item.SKU}', {* SKU/code - required *}
						'{$item.Product}', {* product name *}
						'{$item.Category}', {* category or variation *}
						'{$item.Price}', {* unit price - required *}
						'{$item.Quantity}' {* quantity - required *}
					]);
				{/foreach}
				{* submits transaction to the Analytics servers *}
			{literal}
			  _gaq.push(['_trackTrans']);	
			{/literal}
		{/if}
		{literal}
			(function() {
				var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
				ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
				var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
			})();
		{/literal}
	{/if}
</script>
