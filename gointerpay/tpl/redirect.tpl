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

{if isset($error)}
	<p>{l s='Sorry, an error occurred, please try again later' mod='gointerpay'}</p>
{else}
	<form id="interpay_payment_form" class="payment_module" action="{$pathInterpaySsl|escape:htmlall:'UTF-8'}" method="get" style="border: 1px solid #595A5E;display: block;padding: 0.6em;text-decoration: none;margin:0.5em 0 0 0.7em;">
	      <input type="hidden" name="tempCartUUID" value="{$tempCartUUID|escape:htmlall:'UTF-8'}"/>
	      <input type="hidden" name="country" value="{$country|escape:htmlall:'UTF-8'}"/>
	      <input type="hidden" name="store" value="{$store|escape:htmlall:'UTF-8'}"/>
	      <div style="cursor: pointer;" onclick="$('#interpay_payment_form').submit()"><p style="text-align:center">{l s='You are now being redirected to GoInterpay, a third party partner we use for international orders who will collect payment and ship your order to you. By using GoInterpay you will be subject to their privacy policy.' mod='gointerpay'}</p></div>
	      <input type="submit" style="display: none;" />
	</form>
	<script type="text/javascript">
	$(document).ready(function()
	{
		setTimeout(function() {	$('#interpay_payment_form').submit(); }, 3000);
	});
	</script>
{/if}