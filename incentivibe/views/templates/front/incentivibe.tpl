{*
* 2012-2013 Incentivibe
*
*  @author Incentivibe
*  @copyright  2012-2013 Incentivibe
*}

{if isset($iv_contest_token) and $iv_contest_token}

	{literal}
	<script type="text/javascript">
	var iv_tkn = '{/literal}{$iv_contest_token|escape:'htmlall':'UTF-8'}{literal}'; 
	var iv_currency = '{/literal}{$currency_sign|escape:'htmlall':'UTF-8'}{literal}';
	var iv_language = '{/literal}{$language_sign|escape:'htmlall':'UTF-8'}{literal}';
		(function() {
			var script_tag = document.getElementsByTagName('script')[0];
			var iv = document.createElement('script'); iv.type = 'text/javascript'; 
			iv.async = true; iv.src = "//cdn.incentivibe.com/assets/iv_client.js";
			script_tag.parentNode.insertBefore(iv, script_tag);
		})();
	</script>
	{/literal}

{/if}
