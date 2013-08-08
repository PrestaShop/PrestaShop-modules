{*
* 2007-2013 PrestaShop
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
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if $relogin}
	<script>
		$(document).ready(function() {
				win = window.redirect('{$redirect_url}');
		});
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
			<strong>{l s='Do you have an eBay business account?' mod='ebay'}</strong>

			<dl class="ebay_dl">
				<dt><label for="eBayUsername" id="ebay_label">{l s='eBay User ID' mod='ebay'}</label></dt>
				<dd><input id="eBayUsername" type="text" name="eBayUsername" value="{$ebay_username|escape:'htmlall':'UTF-8'}" /></dd>
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
	</form>
{/if}
</fieldset>
