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
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<link href="{$css|escape:'htmlall':'UTF-8'}" rel="stylesheet" type="text/css">

<script type="text/javascript">
	{literal}
	/* Fancybox */
	$('a.merchant-warehouse-video-btn').live('click', function(){
	    $.fancybox({
	        'type' : 'iframe',
	        'href' : this.href.replace(new RegExp("watch\\?v=", "i"), 'embed') + '?rel=0&autoplay=1',
	        'swf': {'allowfullscreen':'true', 'wmode':'transparent'},
	        'overlayShow' : true,
	        'centerOnScroll' : true,
	        'speedIn' : 100,
	        'speedOut' : 50,
	        'width' : 853,
	        'height' : 480
	    });
	    return false;
	});
	{/literal}
</script>

<div class="merchant-warehouse-wrapper">
	<a href="http://alliance-processing.com/prestashop.php?cpao=754" class="merchant-warehouse-logo"><img src="{$logo|escape:'htmlall':'UTF-8'}" alt="Mercahnt Warehouse" border="0" /></a>
	<p class="merchant-warehouse-intro">{l s='Alliance Processing is a Premiere Provider of Secure Online Credit Card Processing and ACH Check Processing for your business.' mod='alliance3'}<br />
	<a href="http://alliance-processing.com/prestashop.php?cpao=754">{l s='Create a free account' mod='alliance3'}</a></p>
	<div class="merchant-warehouse-content">
		<div class="merchant-warehouse-leftCol">
			<h3>{l s='Why you\'ll love Alliance Processing:' mod='alliance3'}</h3>
			<ul>
				<li>{l s='Same Day/Highest Approval Rates' mod='alliance3'}</li>
				<li>{l s='Only Ach Processing solution for Presta Shop' mod='alliance3'}</li>
				<li>{l s='Ability to place all types of High risk merchants' mod='alliance3'}</li>
				<li>{l s='Multiple in house banking solutions Domestic and International' mod='alliance3'}</li>
				<li>{l s='Rates starting at .55%  Match or Beat Any Rate or get a $100 Amex Gift Card' mod='alliance3'}</li>
				<li>{l s='No application, Contract or start up fees ' mod='alliance3'}</li>
				<li>{l s='In-depth Reporting and Analytics ' mod='alliance3'}</li>
				<li>{l s='Seamless Integration with Prestashop  ' mod='alliance3'}</li>
			</ul>
			<a href="http://alliance-processing.com/prestashop.php?cpao=754" class="merchant-warehouse-link">{l s='Create an account' mod='alliance3'}</a>
			<p class="H3">{l s='Accept secure payments worldwide using all major credit cards!' mod='alliance3'}</p>
			<img src="{$module_dir}img/cc-icons.png" alt="Credit Cards" class="cc-icons" />
		</div>
		<div class="merchant-warehouse-video">
			<p>{l s='Alliance Processing is a processor that truly understands high risk processing.  We offer flexible scalable solutions with a focus on keeping your accounts processing at all times.' mod='alliance3'}</p>
<img src="{$module_dir}img/Prestashop-Main-Image.jpg">

<!--			<a href="http://www.youtube.com/embed/xfQBeyAsU1A" class="merchant-warehouse-video-btn"><img src="{$module_dir}img/video-screen.jpg" alt="Merchant Warehouse screencast" /><img src="{$module_dir}img/btn-video.png" alt="" class="video-icon" /></a>-->
		</div>
	</div>
	<ul id="menuTab">
	{foreach from=$tab item=li}
		<li id="menuTab{$li.tab|escape:'htmlall':'UTF-8'}" class="menuTabButton {if $li.selected}selected{/if}">{if $li.icon != ''}<img src="{$li.icon|escape:'htmlall':'UTF-8'}" alt="{$li.title|escape:'htmlall':'UTF-8'}"/>{/if} {$li.title|escape:'htmlall':'UTF-8'}</li>
	{/foreach}
	</ul>
	<div id="tabList">
	{foreach from=$tab item=div}
		<div id="menuTab{$div.tab|escape:'htmlall':'UTF-8'}Sheet" class="tabItem {if $div.selected}selected{/if}">
			{$div.content}
		</div>
	{/foreach}
	</div>
</div>
{foreach from=$script item=link}
<script type="text/javascript" src="{$link|escape:'htmlall':'UTF-8'}"></script>
{/foreach}
