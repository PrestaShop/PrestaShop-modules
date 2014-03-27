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
	<li id="menuTab1" class="menuTabButton selected">1. {l s='Account settings' mod='ebay'}</li>
	<li id="menuTab2" class="menuTabButton">2. {l s='Categories and pricing' mod='ebay'}</li>
	<li id="menuTab8" class="menuTabButton">3. {l s='Item specifics' mod='ebay'}</li>
	<li id="menuTab3" class="menuTabButton">4. {l s='Dispatch and Shipping' mod='ebay'}</li>
	<li id="menuTab4" class="menuTabButton">5. {l s='Template manager' mod='ebay'}</li>
	<li id="menuTab5" class="menuTabButton">6. {l s='List products' mod='ebay'}</li>
	<li id="menuTab6" class="menuTabButton">7. {l s='Order history' mod='ebay'}</li>
	<li id="menuTab7" class="menuTabButton">8. {l s='Help' mod='ebay'}</li>
</ul>
<div id="tabList" class="{$class_general}">
	<div id="menuTab1Sheet" class="tabItem selected">{$form_parameters}</div>
	<div id="menuTab2Sheet" class="tabItem">{$form_category}</div>
	<div id="menuTab8Sheet" class="tabItem">{$form_items_specifics}</div>
	<div id="menuTab3Sheet" class="tabItem">{$form_shipping}</div>
	<div id="menuTab4Sheet" class="tabItem">{$form_template_manager}</div>
	<div id="menuTab5Sheet" class="tabItem">{$form_ebay_sync}</div>
	<div id="menuTab6Sheet" class="tabItem">{$orders_history}</div>
	<div id="menuTab7Sheet" class="tabItem">{$help}</div>
</div>
<br clear="left" />
<br />
<style>{literal}
	#menuTab { float: left; padding: 0; margin: 0; text-align: left; }
	#menuTab li { text-align: left; float: left; display: inline; padding: 5px; padding-right: 10px; background: #EFEFEF; font-weight: bold; cursor: pointer; border-left: 1px solid #EFEFEF; border-right: 1px solid #EFEFEF; border-top: 1px solid #EFEFEF; }
	#menuTab li.menuTabButton.selected { background: #FFF6D3; border-left: 1px solid #CCCCCC; border-right: 1px solid #CCCCCC; border-top: 1px solid #CCCCCC; }
	#tabList { clear: left; }
	.tabItem { display: none; }
	.tabItem.selected { display: block; background: #FFFFF0; border: 1px solid #CCCCCC; padding: 10px; padding-top: 20px; }
	{/literal}
</style>
<script>
	{literal}
	$(".menuTabButton").click(function () {
		$(".menuTabButton.selected").removeClass("selected");
		$(this).addClass("selected");
		$(".tabItem.selected").removeClass("selected");
		$("#" + this.id + "Sheet").addClass("selected");
	});
	{/literal}
</script>
{if $id_tab}
	<script>
		$(".menuTabButton.selected").removeClass("selected");
		$("#menuTab{$id_tab}").addClass("selected");
		$(".tabItem.selected").removeClass("selected");
		$("#menuTab{$id_tab}Sheet").addClass("selected");
	</script>
{/if}


<div id="helpertexts" style="display:none;">
	<div id="returnsAccepted" style="width:300px">
		{l s='All sellers on eBay must specify a returns policy for their items, whether your policy is to accept returns or not. If you don\'t specify a returns policy, eBay will select a default returns policy for you.' mod='ebay'}
	</div>
	<div id="dispatchTime" style="width:300px">
		{l s='The dispatch time is the time between the buyer’s payment clearing and you sending the item. Buyers are increasingly expecting short dispatch times, ideally next day, but preferably within 3 working days. ' mod='ebay'}
	</div>
	<div id="DomShipp" style="width:300px">
		{l s='To add a shipping method, map your PrestaShop options with one offered by eBay.' mod='ebay'}
	</div>
	<div id="tagsTemplate" style="width:300px">
		{ldelim}MAIN_IMAGE{rdelim}<br/>
		{ldelim}MEDIUM_IMAGE_1{rdelim}<br/>
		{ldelim}MEDIUM_IMAGE_2{rdelim}<br/>
		{ldelim}MEDIUM_IMAGE_3{rdelim}<br/>
		{ldelim}PRODUCT_PRICE{rdelim}<br/>
		{ldelim}PRODUCT_PRICE_DISCOUNT{rdelim}<br/>
		{ldelim}DESCRIPTION_SHORT{rdelim}<br/>
		{ldelim}DESCRIPTION{rdelim}<br/>
		{ldelim}FEATURES{rdelim}<br/>
		{ldelim}EBAY_IDENTIFIER{rdelim}<br/>
		{ldelim}EBAY_SHOP{rdelim}<br/>
		{ldelim}SLOGAN{rdelim}<br/>
		{ldelim}PRODUCT_NAME{rdelim}
	</div>
	<div id="categoriesProgression" style="overflow: auto;width: 200px;height: 100px;text-align: center;font-size: 16px;padding-top: 30px;"></div>
</div>

