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

<ul id="menuTab">
	<li id="menuTab1" class="menuTabButton selected">{l s='Account settings' mod='tntcarrier'}</li>
	{if !isset($account_set) || $account_set === true}
		<li id="menuTab2" class="menuTabButton">{l s='Shipping Settings' mod='tntcarrier'}</li>
		<li id="menuTab3" class="menuTabButton">{l s='Service Settings' mod='tntcarrier'}</li>
	{/if}
</ul>
<div id="tabList">
	<div id="menuTab1Sheet" class="tabItem selected">{$varMain.account}</div>
	<div id="menuTab2Sheet" class="tabItem"><div>{$varMain.shipping}</div></div>
	<div id="menuTab3Sheet" class="tabItem">{$varMain.service}</br>{$varMain.country}<br/>{$varMain.info}</div>
</div>
<br clear="left" />
<br />
{literal}
<style>
	#menuTab { float: left; padding: 0; margin: 0; text-align: left; }
	#menuTab li { text-align: left; float: left; display: inline; padding: 5px; padding-right: 10px; background: #EFEFEF; font-weight: bold; cursor: pointer; border-left: 1px solid #CCCCCC; border-right: 1px solid #CCCCCC; border-top: 1px solid #CCCCCC; }
	#menuTab li.menuTabButton.selected { background: #FFF6D3; border-left: 1px solid #CCCCCC; border-right: 1px solid #CCCCCC; border-top: 1px solid #CCCCCC; }
	#tabList { clear: left; }
	.tabItem { display: none; }
	.tabItem.selected { display: block; background: #FFFFF0; border: 1px solid #CCCCCC; padding: 10px; padding-top: 20px; }
</style>
<script type="text/javascript">
	$(".menuTabButton").click(function () {
		$(".menuTabButton.selected").removeClass("selected");
		$(this).addClass("selected");
		$(".tabItem.selected").removeClass("selected");
		$("#" + this.id + "Sheet").addClass("selected");
	});
</script>
{/literal}
