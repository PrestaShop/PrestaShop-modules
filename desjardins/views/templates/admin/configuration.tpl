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
<div class="desjardins">
	<div class="desjardins-header">
		<a rel="external" href="#" target="_blank" class="L desjardins-logo"><img alt="" src="{$module_dir|escape:html:'UTF-8'}/img/desjardins_{$admin_lang|escape:html:'UTF-8'}.png" /></a>
		<span class="desjardins-intro L">
			<h1>{l s='ENJOY A TURNKEY SOLUTION WITH DESJARDINS! ' mod='desjardins'}</h1>
			<p class="H3 L">
				{l s='With Desjardins and PrestaShop, all it takes is a few clicks to integrate payment into your online store. No technical know-how is required with this turnkey solution because it\'s pre-installed in PrestaShop tools!' mod='desjardins'}
			</p>
		</span>
		<a class="desjardins-create-btn R" rel="external" href="#" target="_blank"><span>{l s='Create your account' mod='desjardins'}</span></a>
	</div>
	<div class="desjardins-content">
		<div class="desjardins-leftCol">
			<h3>{l s='DOING E-BUSINESS WITH DESJARDINS MEANS:' mod='desjardins'}</h3>
			<ul>
				<li>{l s='Using effective, robust solutions.' mod='desjardins'}</li>
				<li>{l s='Mitigating risk through the use of fraud protection tools and processes.' mod='desjardins'}</li>
				<li>{l s='Enjoying pricing without hidden fees or surcharges so you can budget.' mod='desjardins'}</li>
				<li>{l s='Having access to value-added management tools.' mod='desjardins'}</li>
				<li>{l s='Enjoying excellent customer service.' mod='desjardins'}</li>
			</ul>
			<a href="#" class="desjardins-link">{l s='Create an account' mod='desjardins'}</a>
			<div style="clear: right"></div> 
			<p class="H3">{l s='ENJOY A TURNKEY SOLUTION WITH DESJARDINS!' mod='desjardins'}</p>
			<p>{l s='With Desjardins, all it takes is a few clicks to integrate payment into your online store.' mod='desjardins'}</p>
			<p class="H3">{l s='0$ per month' mod='desjardins'}</p>
			<p>{l s='COMPETITIVE PRICING EXCLUSIVELY FOR PRESTASHOP USERS' mod='desjardins'}</p>
		</div>
		<div class="desjardins-video">
			<p>{l s='desjardins video message text' mod='desjardins'}</p>
			<a href="#" class="desjardins-video-btn"><img src="{$module_dir|escape:html:'UTF-8'}img/video-screen.jpg" alt="Desjardins screencast" /><img src="{$module_dir|escape:html:'UTF-8'}img/btn-video.png" alt="" class="video-icon" /></a>
		</div>
	</div>
	<div class="info col-lg-7">
		<p style="margin-left: 5px;">
			<strong>{l s='Complete your Setup' mod='desjardins'}</strong>
			<br />
			<br />
			{l s='Please e-mail Desjardins at' mod='desjardins'} <a href="mailto:online_support@scd.desjardins.com">online_support@scd.desjardins.com</a> {l s='and provide them the following URL as your "Return Interface URL"' mod='desjardins'}
			<br />
			<br />
			<input type="text" onclick="$(this).select()" value="{$desjardins_url|escape:html:'UTF-8'}" />
		</p>
	</div>
	<div style="clear: both;"></div>
	<br />
	<br />
</div>