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

{* Assign a value to 'current_step' to display current style *}
{if !$opc}
<!-- Steps -->
</div>
<div data-role="footer" style="width: 100%;">
	<div data-role="navbar">
		<ul>
			<li><a data-theme="{$ps_mobile_styles.PS_MOBILE_THEME_PROCESS_BAR}" data-ajax="false" class="{if $current_step=='summary'}ui-btn-active{else}{if $current_step=='payment' || $current_step=='shipping' || $current_step=='address' || $current_step=='login'}step_done{else}step_todo{/if}{/if}" href="{if $current_step=='payment' || $current_step=='shipping' || $current_step=='address' || $current_step=='login'}{$link->getPageLink('order.php', true)}{if isset($back) && $back}?back={$back}{/if}{else}#{/if}" data-icon="grid">{l s='Cart'}</a></li>
			<li><a data-theme="{$ps_mobile_styles.PS_MOBILE_THEME_PROCESS_BAR}" data-ajax="false" class="{if $current_step=='login'}ui-btn-active{else}{if $current_step=='payment' || $current_step=='shipping' || $current_step=='address'}step_done{else}step_todo{/if}{/if}" data-icon="arrow-r" href="{if $current_step=='payment' || $current_step=='shipping' || $current_step=='address'}{$link->getPageLink('order.php', true)}?step=1{if isset($back) && $back}&amp;back={$back}{/if}{else}#{/if}">{l s='Login'}</a></li>
			<li><a data-theme="{$ps_mobile_styles.PS_MOBILE_THEME_PROCESS_BAR}" data-ajax="false" class="{if $current_step=='address'}ui-btn-active{else}{if $current_step=='payment' || $current_step=='shipping'}step_done{else}step_todo{/if}{/if}" href="{if $current_step=='payment' || $current_step=='shipping'}{$link->getPageLink('order.php', true)}?step=1{if isset($back) && $back}&amp;back={$back}{/if}{else}#{/if}" data-icon="arrow-r">{l s='Address'}</a></li>
			<li><a data-theme="{$ps_mobile_styles.PS_MOBILE_THEME_PROCESS_BAR}" data-ajax="false" class="{if $current_step=='shipping'}ui-btn-active{else}{if $current_step=='payment'}step_done{else}step_todo{/if}{/if}" data-icon="arrow-r" href="{if $current_step=='payment'}{$link->getPageLink('order.php', true)}?step=2{if isset($back) && $back}&amp;back={$back}{/if}{else}#{/if}">{l s='Shipping'}</a></li>
			<li><a data-theme="{$ps_mobile_styles.PS_MOBILE_THEME_PROCESS_BAR}" data-ajax="false" class="{if $current_step=='payment'}ui-btn-active{else}step_todo{/if}" href="#" data-icon="check">{l s='Payment'}</a></li>
		</ul>
	</div>
</div>
<div class="center_column" data-role="content">
<!-- /Steps -->
{/if}
