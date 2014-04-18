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
{if $desjardins_order.valid == 1}
<div class="conf confirmation">
	{l s='Congratulations! Your payment has been made, and your order has been saved under' mod='desjardins'}{if isset($desjardins_order.reference)} {l s='the reference' mod='desjardins'} <b>{$desjardins_order.reference|escape:html:'UTF-8'}</b>{else} {l s='the ID' mod='desjardins'} <b>{$desjardins_order.id|escape:html:'UTF-8'}</b>{/if}.
</div>
{else}
<div class="error">
	{l s='Unfortunately, an error occurred during the transaction.' mod='desjardins'}<br /><br />
	{l s='Please double-check your credit card details and try again. If you need further assistance, feel free to contact us anytime.' mod='desjardins'}<br /><br />
{if isset($desjardins_order.reference)}
	({l s='Your Order\'s Reference:' mod='desjardins'} <b>{$desjardins_order.reference|escape:html:'UTF-8'}</b>)
{else}
	({l s='Your Order\'s ID:' mod='desjardins'} <b>{$desjardins_order.id|escape:html:'UTF-8'}</b>)
{/if}
</div>
{/if}