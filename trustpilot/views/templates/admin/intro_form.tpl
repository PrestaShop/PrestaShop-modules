{*}
/*
* 2007-2013 Profileo
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to contact@profileo.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade Profileo to newer
* versions in the future. If you wish to customize Profileo for your
* needs please refer to http://www.profileo.com for more information.
*
*  @author Profileo <contact@profileo.com>
*  @copyright  2007-2013 Profileo
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of Profileo
*/
{*}

<table width="605px" cellpadding="5" style="margin:0 auto;color:#555555;padding:5px;">
	<tr>
		<td colspan="2" valign="top">
			{if $lang == 'fr'}
				<img alt="Trustpilot 1" src="../modules/{$module_name|escape:'htmlall':'UTF-8'}/img/bann_presta_FR.png" />
			{else}
				<img alt="Trustpilot 1" src="../modules/{$module_name|escape:'htmlall':'UTF-8'}/img/bann_presta_FR.png" />
			{/if}
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<h3 style="margin:0;font-size:20px;">{l s='Increase your sales with customer reviews' mod='trustpilot'}</h3>
		</td>
	</tr>
	<tr>
		<td colspan="2" valign="top">
			<p style="text-align:justify;line-height:20px;margin:15px 0;">
				{l s='Trustpilot is the global standard for online trust. Trustpilot enables customer centric businesses like yours to collect reviews, engage with consumers and share your most powerful marketing asset – real customers’ positive experiences.' mod='trustpilot'}
			</p>
			<p style="text-align:justify;line-height:20px;margin:15px 0;">
				{l s='Using the Trustpilot Solution to collect and display reviews is one of the most effective ways to build trust, confidence and credibility for your online store.' mod='trustpilot'}
			</p>
			<p style="text-align:center;font-size:16px;line-height:20px;margin:15px 0;"><b>
				{l s='Trustpilots agreement with Google means that the reviews you collect feed directly into your Google seller rating account.' mod='trustpilot'}</b>
			</p>
			<p style="text-align:justify;line-height:20px;margin:15px 0;">
				{l s='The power of displaying quality verified reviews in a prominent position through the customer journey via our easy to use widget is undeniable. Displaying reviews on site is proven to decrease shopping cart abandonment, increase basket sizes and most importantly increases sales conversions.' mod='trustpilot'}
			</p>
			<p style="text-align:justify;line-height:20px;margin:15px 0;">
				{l s='With a few simple steps, Trustpilots simple integration with Prestashop, means in a matter of minutes you too can share your real customer’s great shopping experiences to potential buyers.' mod='trustpilot'}
			</p>
		</td>
	</tr>
	<tr>
		<td valign="top" style="padding-bottom:0">
			<p style="background:url('{$this_path_ssl|escape:'htmlall':'UTF-8'}img/btn-sprite.png') no-repeat scroll 0 -461px transparent; font-size:14px;margin:0 auto;width:305px;" id="tp_accno">
				{if $lang == 'fr'}
					<a target="_blank" href="http://business.trustpilot.fr/forms/prestashop-fr" style="background:url('{$this_path_ssl|escape:'htmlall':'UTF-8'}img/btn-sprite.png') no-repeat scroll 100% -519px transparent;color: #FFFFFF;display: block;font-size: 13px;font-weight: bold;height: 30px;padding: 11px 5px 0;text-align: center;text-transform: uppercase;width: 300px;">
						{l s='Open a FREE account' mod='trustpilot'}
					</a>
				{else}
					<a target="_blank" href="http://business.trustpilot.com/forms/prestashop-en" style="background:url('{$this_path_ssl|escape:'htmlall':'UTF-8'}img/btn-sprite.png') no-repeat scroll 100% -519px transparent;color: #FFFFFF;display: block;font-size: 13px;font-weight: bold;height: 30px;padding: 11px 5px 0;text-align: center;text-transform: uppercase;width: 300px;">
						{l s='Open a FREE account' mod='trustpilot'}
					</a>
				{/if}
			</p>
		</td>
	</tr>
	<tr>
		<td valign="top" style="padding-top:0">
			<p id="tp_accyes" style="margin:0">
				<a href="{$currentIndex|escape:'htmlall':'UTF-8'}&configure={$module_name|escape:'htmlall':'UTF-8'}&token={$admin_token|escape:'htmlall':'UTF-8'}&alreadHasAcc" style="color:#555555;display:block;font-size:12px;font-weight:bold;height:30px;text-align:center;text-decoration:underline;">
					{l s='I already have a Trustpilot account (configure the module)' mod='trustpilot'}
				</a>
			</p>
		</td>
	</tr>
</table>