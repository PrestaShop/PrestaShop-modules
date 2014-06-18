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

{if $smarty.const._PS_VERSION_ >= 1.6}
<div class="row">
	<div class="col-lg-12">
		<div class="panel">
			<div class="panel-heading"><img src="{$base_url}modules/{$module_name}/logo.gif" alt="" /> {l s='PayPal Capture' mod='paypal'}</div>
			<form method="post" action="{$smarty.server.REQUEST_URI|escape:htmlall}">
				<input type="hidden" name="id_order" value="{$params.id_order}" />
				<p><b>{l s='Information:' mod='paypal'}</b> {l s='Funds ready to be captured before shipping' mod='paypal'}</p>
				<p class="center">
					<button type="submit" class="btn btn-default" name="submitPayPalCapture" onclick="if (!confirm('{l s='Are you sure you want to capture?' mod='paypal'}'))return false;">
						
						{l s='Get the money' mod='paypal'}
					</button>
				</p>
			</form>
		</div>
	</div>
</div>
{else}
<br />
<fieldset {if isset($ps_version) && ($ps_version < '1.5')}style="width: 400px"{/if}>
	<legend><img src="{$base_url}modules/{$module_name}/logo.gif" alt="" />{l s='PayPal Capture' mod='paypal'}</legend>
	<p><b>{l s='Information:' mod='paypal'}</b> {l s='Funds ready to be captured before shipping' mod='paypal'}</p>
	<form method="post" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}">
		<input type="hidden" name="id_order" value="{$params.id_order|intval}" />
		<p class="center"><input type="submit" class="button" name="submitPayPalCapture" value="{l s='Get the money' mod='paypal'}" onclick="return confirm('{l s='Are you sure you want to capture?' mod='paypal'}');" /></p>
	</form>
</fieldset>
{/if}
