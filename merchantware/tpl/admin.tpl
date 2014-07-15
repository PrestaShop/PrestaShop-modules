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
	<a href="http://merchantwarehouse.com/prestashop-merchants?cpao=754" class="merchant-warehouse-logo"><img src="{$logo|escape:'htmlall':'UTF-8'}" alt="Mercahnt Warehouse" border="0" /></a>
	<p class="merchant-warehouse-intro">{l s='Merchant Warehouse makes it easy to immediately start accepting secure online payments from customers all over the world.' mod='merchantware'}<br />
	<a href="http://merchantwarehouse.com/prestashop-merchants?cpao=754">{l s='Create a free account' mod='merchantware'}</a></p>
	<div class="merchant-warehouse-content">
		<div class="merchant-warehouse-leftCol">
			<h3>{l s='Why you\'ll love Merchant Warehouse:' mod='merchantware'}</h3>
			<ul>
				<li>{l s='Free payment gateway' mod='merchantware'}</li>
				<li>{l s='No set-up, or additional transaction, fees' mod='merchantware'}</li>
				<li>{l s='Free 24/7 support is provided by an award-winning, in-house team' mod='merchantware'}</li>
				<li>{l s='Fast and easy account setup' mod='merchantware'}</li>
				<li>{l s='PCI-DSS certified' mod='merchantware'}</li>
				<li>{l s='Seamless integration with PrestaShop' mod='merchantware'}</li>
				<li>{l s='In depth transaction and report management tools' mod='merchantware'}</li>
				<li>{l s='No contracts or hidden fees' mod='merchantware'}</li>
			</ul>
			<a href="http://merchantwarehouse.com/prestashop-merchants?cpao=754" class="merchant-warehouse-link">{l s='Create an account' mod='merchantware'}</a>
			<p class="H3">{l s='Accept secure payments worldwide using all major credit cards!' mod='merchantware'}</p>
			<img src="{$module_dir}img/cc-icons.png" alt="Credit Cards" class="cc-icons" />
		</div>
		<div class="merchant-warehouse-video">
			<p>{l s='Merchant Warehouse is already trusted by hundreds-of-thousands of businesses nationwide because they provide exactly what they promise, the most innovative online payment processing solution at a price everyone can afford.' mod='merchantware'}</p>
			<a href="http://www.youtube.com/embed/xfQBeyAsU1A" class="merchant-warehouse-video-btn"><img src="{$module_dir}img/video-screen.jpg" alt="Merchant Warehouse screencast" /><img src="{$module_dir}img/btn-video.png" alt="" class="video-icon" /></a>
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