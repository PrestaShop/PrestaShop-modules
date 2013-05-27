{if $relogin}
	<script>
		$(document).ready(function() {
				win = window.redirect('{$redirect_url}');
		});
	</script>
{/if}

{if $logged}
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
	<fieldset>
		<legend><img src="{$path}logo.gif" alt="" title="" />{l s='Register the module on eBay' mod='ebay'}</legend>
		<p align="center" class="warning"><a href="{$request_uri}&action=logged&relogin=1" target="_blank" class="button">{l s='If you\'ve been logged out of eBay and not redirected to the configuration page, please click here' mod='ebay'}</a></p>
		<p align="center"><img src="{$path}views/img/loading.gif" alt="{l s='Loading' mod='ebay'}" title="{l s='Loading' mod='ebay'}" /></p>
		<p align="center">{l s='Once you sign in via the new eBay window, the module will automatically finish the installation' mod='ebay'}</p>
	</fieldset>
{else}
	<style>
		{literal}
		.ebay_dl {margin: 0 0 10px 40px}
		.ebay_dl > * {float: left; margin: 10px 0 0 10px}
		.ebay_dl > dt {min-width: 100px; display: block; clear: both; text-align: left}
		#ebay_label {font-weight: normal; float: none}
		#button_ebay{background-image:url({/literal}{$path}{literal}views/img/ebay.png);background-repeat:no-repeat;background-position:center 90px;width:385px;height:191px;cursor:pointer;padding-bottom:70px;font-weight:bold;font-size:22px}
	</style>
	<script>
		$(document).ready(function() {
			$('#button_ebay').click(function() {
				if ($('#eBayUsername').val() == '')
				{
					alert("{/literal}{l s='Please enter your eBay user ID' mod='ebay'}{literal}");
					return false;
				}
				else
					window.open('{/literal}{$window_open_url}{literal}');
			});
		});
		{/literal}
	</script>
	<form action="{$action_url}" method="post">
		<fieldset>
			<legend><img src="{$path}logo.gif" alt="" title="" />{l s='Register the module on eBay' mod='ebay'}</legend>
			<strong>{l s='Do you have an eBay business account?' mod='ebay'}</strong>

			<dl class="ebay_dl">
				<dt><label for="eBayUsername" id="ebay_label">{l s='eBay User ID' mod='ebay'}</label></dt>
				<dd><input id="eBayUsername" type="text" name="eBayUsername" value="{$ebay_username}" /></dd>
				<dt>&nbsp;</dt>
				<dd><input type="submit" id="button_ebay" class="button" value="{l s='Register the module on eBay' mod='ebay'}" /></dd>
			</dl>

			<br class="clear" />
			<br />

			<strong>{l s='You do not have a professional eBay account yet ?' mod='ebay'}</strong><br />

			<dl class="ebay_dl">
				<dt><u><a href="{l s='https://scgi.ebay.com/ws/eBayISAPI.dll?RegisterEnterInfo' mod='ebay'}" target="_blank">{l s='Subscribe as a business seller on eBay' mod='ebay'}</a></u></dt>
				<dd></dd>
			</dl>
			<br /><br />
			<br /><u><a href="{l s='http://pages.ebay.com/help/sell/businessfees.html' mod='ebay'}" target="_blank">{l s='Review the eBay business seller fees page' mod='ebay'}</a></u>
			<br />{l s='Consult our "Help" section for more information' mod='ebay'}
		</fieldset>
	</form>
{/if}