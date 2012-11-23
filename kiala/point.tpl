{*
* 2007-2011 PrestaShop
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
*  @copyright  2007-2011 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div id="kiala">
	<h1>{l s='Kiala point' mod='kiala'}</h1>
	<div class="kiala_block">
		{l s='You have selected a parcel delivery to a Kiala point. Kiala allows parcels to be delivered to close by shops with wide opening hours and no waiting lines.' mod='kiala'}
		<a id="more_info" href="#info_content">{l s='(More info...)' mod='kiala'}</a>
		<br /><br />
		{if $point->status == "new_point"}
			{l s='Based on your address, we recommend this Kiala Point:' mod='kiala'}
		{elseif $point->status == "selection"}
			{l s='You selected this Kiala Point:' mod='kiala'}
		{elseif $point->status == "point_already_selected"}
			<div class="point_selected">{l s='Kiala Point you selected for your last order:' mod='kiala'}</div>
		{elseif $point->status == "point_unavailable"}
			<div class="point_selected">{l s='We apologize but the Kiala Point' mod='kiala'} {if isset($unavailable_point_name)}"{$unavailable_point_name|escape:'htmlall':'UTF-8'}" {/if}{l s=' that you selected for your last order is currently unavailable.' mod='kiala'}</div>
		{/if}
			<div class="kiala_point">
				{if $point->picture}
					<div class="picture"><img src="{$point->picture|escape:'htmlall':'UTF-8'}" alt="{$point->name|escape:'htmlall':'UTF-8'}" /></div>
				{else}
					<div class="picture"><img src="{$kiala_module_dir}default_picture.jpg" alt="{$point->name|escape:'htmlall':'UTF-8'}" /></div>
				{/if}
				<div class="kiala_description">
					{$point->name|escape:'htmlall':'UTF-8'}
					<br />
					{$point->street|escape:'htmlall':'UTF-8'}
					<br />
					{$point->zip|escape:'htmlall':'UTF-8'}
					{$point->city|escape:'htmlall':'UTF-8'}
					<br />
					{$point->location_hint|escape:'htmlall':'UTF-8'}
				</div>
			</div>
			<div style="clear:both"></div>
			<span class="before_map">{l s='You can select another Kiala point on the map below:' mod='kiala'}</span>
		<iframe id="map" src="{$search_link}" width='540px' height='400px'>
			<p>{l s='Your browser does not support iframes.' mod='kiala'}</p>
		</iframe>
		<input type="hidden" name="short_id" value="{$point->short_id|escape:'htmlall':'UTF-8'}"/>
	</div>
</div>

<div id='info_content' style='display:none;text-align:left'>
	{l s='You\'re not at home when the delivery man rings? Kiala has the perfect solution: get the package yourself, at a place of your choice, when it suits you.' mod='kiala'}
	<h3>{l s='Your package delivered next door' mod='kiala'}</h3>
	{l s='Your package is delivered in a neighborhood shop (grocery store, bookshop, dry cleaning...). There\'s always a Kiala point near your home, your workplace or your children\'s school.' mod='kiala'}
	<h3>{l s='Flexible opening hours' mod='kiala'}</h3>
	{l s='Early in the morning, at noon, in the evening until 7 or 8pm, on Saturdays and possibly even on Sundays: get your package where and when it suits you.' mod='kiala'}
	<h3>{l s='Instant notification' mod='kiala'}</h3>
	{l s='When your package is delivered you are instantly notified by e-mail, text or phone recording. Using the Kiala website you can follow the whereabouts of your package at each step of the shipping process.'}
	<h3>{l s='More safety' mod='kiala'}</h3>
	{l s='You have to provide ID when retrieving your package. You will also sign a receipt.' mod='kiala'}
	<h3>{l s='Fast' mod='kiala'}</h3>
	{l s='Your package is delivered in a hearbeat. No waiting lines. No stress.' mod='kiala'}
</div>
