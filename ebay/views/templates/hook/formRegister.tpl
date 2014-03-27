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

{if $relogin}

	<script>
		$(document).ready(function() {ldelim}
				win = window.location.href = '{$redirect_url}';
		{rdelim});
	</script>
{/if}

<fieldset>
	<legend><img src="{$path}logo.gif" alt="" title="" />{l s='Register the module on eBay' mod='ebay'}</legend>

{if $logged}
{$check_token_tpl}
{else}
	<style>
		{literal}
		.ebay_dl {margin: 0 0 10px 40px}
		.ebay_dl > * {float: left; margin: 10px 0 0 10px}
		.ebay_dl > dt {min-width: 100px; display: block; clear: both; text-align: left}
		#ebay_label {font-weight: normal; float: none}
		#button_ebay{background-image:url({/literal}{$path}{literal}views/img/ebay.png);background-repeat:no-repeat;background-position:center 90px;width:385px;height:191px;cursor:pointer;padding-bottom:70px;font-weight:bold;font-size:22px}
	input.primary {
		text-shadow: none;
		background: -webkit-gradient(linear, center top ,center bottom, from(#0055FF), to(#0055AA)) repeat scroll 0 0 transparent;
		background: -moz-gradient(linear, center top ,center bottom, from(#0055FF), to(#0055AA)) repeat scroll 0 0 transparent;
		color: white;
	}
	</style>
	<script>
		$(document).ready(function() {
			$('#ebayRegisterButton').click(function() {
				if ($('#eBayUsername').val() == '')
				{
					alert("{/literal}{l s='Please enter your eBay user ID' mod='ebay'}{literal}");
					return false;
				}
				else{

					var country = $("#ebay_countries").val();
					var link = $("option[value=" + country + "]").data("signin");

					window.open(link + "{/literal}{$window_open_url}{literal}");
				}
			});
		});
		{/literal}
	</script>
	<form action="{$action_url}" method="post">
		<strong style="margin-bottom:10px;width:100%;display:inline-block">{l s='Do you have an eBay business account?' mod='ebay'}</strong>
		<label for="eBayUsername">{l s='eBay User ID' mod='ebay'}</label>
		<div class="margin-form">
			<input id="eBayUsername" type="text" name="eBayUsername" value="{$ebay_username|escape:'htmlall':'UTF-8'}" />
		</div>
		<div class="clear both"></div>

		<label for="ebay_countries">{l s='Choose ebay site you want to listen' mod='ebay'}</label>
		<div class="margin-form">
			<select name="ebay_country" id="ebay_countries">
				{if isset($ebay_countries) && $ebay_countries && sizeof($ebay_countries)}
					{foreach from=$ebay_countries item='country' key='key'}
						<option value="{$key}" data-signin="{$country.signin}" {if $key == $default_country} selected{/if}>{if $country.subdomain}{$country.subdomain}.{/if}ebay.{$country.site_extension}</option>
					{/foreach}
				{/if}
			</select>
		</div>
		<div class="clear both"></div>

		<div class="margin-form">
			<input type="submit" id="ebayRegisterButton" class="primary button" value="{l s='Link your ebay account' mod='ebay'}" />
		</div>
		<div class="clear both"></div>

		<strong>{l s='You do not have a professional eBay account yet ?' mod='ebay'}</strong><br />
		<u><a href="{l s='https://scgi.ebay.com/ws/eBayISAPI.dll?RegisterEnterInfo' mod='ebay'}" target="_blank">{l s='Subscribe as a business seller on eBay' mod='ebay'}</a></u>
		<br /><br />
		<br /><u><a href="{l s='http://pages.ebay.com/help/sell/businessfees.html' mod='ebay'}" target="_blank">{l s='Review the eBay business seller fees page' mod='ebay'}</a></u>
		<br />{l s='Consult our "Help" section for more information' mod='ebay'}
	</form>
{/if}
</fieldset>
<script type="text/javascript">

</script>
