<script>
	function checkToken()
	{
		$.ajax({
			url: '{$url}',
			cache: false,
			success: function(data)
			{
				if (data == 'OK')
					window.location.href = '{$window_location_href}';
				else
					setTimeout ("checkToken()", 5000);
			}
		});
	}
	checkToken();
</script>
	<p align="center" class="warning"><a href="{$request_uri}&action=logged&relogin=1" target="_blank" class="button">{l s='If you\'ve been logged out of eBay and not redirected to the configuration page, please click here' mod='ebay'}</a></p>
	<p align="center"><img src="{$path}views/img/loading.gif" alt="{l s='Loading' mod='ebay'}" title="{l s='Loading' mod='ebay'}" /></p>
	<p align="center">{l s='Once you sign in via the new eBay window, the module will automatically finish the installation' mod='ebay'}</p>
