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
<p id="loyalty" class="align_justify">
	{if $points}
		{l s='By buying this product you can collect up to' mod='fidbag'} <b><span id="loyalty_points">{$points}</span> 
		{if $points > 1}{l s='loyalty points' mod='fidbag'}{else}{l s='loyalty point' mod='fidbag'}{/if}</b>. 
		{l s='Your cart will total' mod='fidbag'} <b><span id="total_loyalty_points">{$total_points}</span> 
		{if $total_points > 1}{l s='points' mod='fidbag'}{else}{l s='point' mod='fidbag'}{/if}</b>
	{else}
		{if isset($no_pts_discounted) && $no_pts_discounted == 1}
			{l s='No reward points for this product because there\'s already a discount.' mod='fidbag'}
		{else}
			{l s='No reward points for this product.' mod='fidbag'}
		{/if}
	{/if}
</p>
<br class="clear" />