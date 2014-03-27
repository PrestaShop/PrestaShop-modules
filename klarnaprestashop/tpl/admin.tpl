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
*  @version  Release: $Revision: 14011 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<link href="{$klarnaprestashopCss}" rel="stylesheet" type="text/css">
<script type="text/javascript">
	{literal}
	/* Fancybox */
	$('a.klarna-video-btn').live('click', function(){
		$.fancybox({
			'padding'		: 0,
			'autoScale'		: false,
			'transitionIn'	: 'none',
			'transitionOut'	: 'none',
			'title'			: this.title,
			'width'			: 640,
			'height'		: 360,
			'href'			: this.href.replace(new RegExp("([0-9])","i"),'moogaloop.swf?clip_id=$1'),
			'type'			: 'swf'
		});

		return false;
	});
	{/literal}
</script>

<div class="klarna-wrapper">
	<a href="https://merchants.klarna.com/signup?locale=en&partner_id=7c1923f1664865c9f6a43b598c3d39b43dd4d5eb&utm_campaign=Platform&utm_medium=Partners&utm_source=Prestashop" class="klarna-logo" target="_blank"><img src="{$klarnaprestashopLogo}" alt="Klarnae" border="0" /></a>
	<p class="klarna-intro">{l s='Increase your sales, risk free' mod='klarnaprestashop'} <a href="https://merchants.klarna.com/signup?locale=en&partner_id=7c1923f1664865c9f6a43b598c3d39b43dd4d5eb&utm_campaign=Platform&utm_medium=Partners&utm_source=Prestashop" class="klarna-btn" target="_blank">{l s='Sign up now!' mod='klarnaprestashop'}</a></p>
	<div class="klarna-content">		
		<div class="klarna-video">
			<h3>{l s='How does Klarna work?' mod='klarnaprestashop'}</h3>
			<a href="http://vimeo.com/20563405" class="klarna-video-btn"><img src="{$module_dir}img/video-screen.jpg" alt="How does Klarna work?" /><img src="{$module_dir}img/btn-video.png" alt="" class="video-icon" /></a>
		</div>
		<div class="klarna-leftCol">
			<h3>{l s='Four great reasons to offer Klarna' mod='klarnaprestashop'}</h3>
			<ul>
				<li><strong>{l s='Increase your sales' mod='klarnaprestashop'}</strong><br>
				{l s='When you offer Klarna in your e-commerce store, you open your doors to millions of potential consumers who prefer to pay by invoice.' mod='klarnaprestashop'}</li>
				<li><strong>{l s='Easy to get started' mod='klarnaprestashop'}</strong><br>
				{l s='In about five minutes, you\'ll be ready to offer one of Europe\'s most popular payment methods.' mod='klarnaprestashop'}</li>
				<li><strong>{l s='The safest and simplest solutions' mod='klarnaprestashop'}</strong><br>
				{l s='With Klarna, consumers will always get their goods before they pay, and you\'ll always get paid, regardless of the service.' mod='klarnaprestashop'}</li>
				<li><strong>{l s='No Risk' mod='klarnaprestashop'}</strong><br>
				{l s='No matter which payment option your customer chooses, Klarna assumes the risk, which means you\'ll always get paid, no matter what.' mod='klarnaprestashop'}</li>
			</ul>
			<h3>{l s='Sign up now, quick and easy!' mod='klarnaprestashop'}</h3>
			<p>{l s='The application will take about 3 minutes to complete' mod='klarnaprestashop'} <a href="https://merchants.klarna.com/signup?locale=en&partner_id=7c1923f1664865c9f6a43b598c3d39b43dd4d5eb&utm_campaign=Platform&utm_medium=Partners&utm_source=Prestashop" class="klarna-btn" target="_blank">{l s='Get Started!' mod='klarnaprestashop'}</a></p>
		</div>
	</div>
	
	<ul id="menuTab">
		{foreach from=$tab item=li}
		<li id="menuTab{$li.tab}" class="menuTabButton {if $li.selected}selected{/if}"><img src="{$li.icon}" alt="{$li.title}"/> {$li.title}</li>
		{/foreach}
	</ul>
	
	<div id="tabList">
		{foreach from=$tab item=div}
		<div id="menuTab{$div.tab}Sheet" class="tabItem {if $div.selected}selected{/if}">
			{$div.content}
		</div>
		{/foreach}
	</div>
</div>
{foreach from=$js item=link}
<script type="text/javascript" src="{$link}"></script>
{/foreach}