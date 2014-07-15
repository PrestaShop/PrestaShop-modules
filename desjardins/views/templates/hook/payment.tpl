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
 *  @version  Release: $Revision: 7040 $
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *}
<div class="payment_module">
	<form action="{$desjardins_params.api_url|escape:html:'UTF-8'}" method="post" id="desjardins-payment-form">
		{foreach from=$desjardins_params item=value key=k}
			<input type="hidden" name="{$k|escape:html:'UTF-8'}" value="{$value|escape:html:'UTF-8'}" />
		{/foreach}
	</form>
		<a href="#" onclick="$(this).parent().find('form').submit();"><img src="{$module_dir|escape:html:'UTF-8'}/img/desjardins-logo.png" style="float: left;" /><span style="float: left;  margin: 20px 0 0;">{l s='Pay with Desjardins' mod='desjardins'}</span><span style="clear: both;" /></a>
</div>