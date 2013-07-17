{*
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
*  @author Prediggo SA <info@prediggo.com> / CeboWeb <dev@ceboweb.com>
*  @copyright  2008-2012 Prediggo SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Prediggo SA
*}

<div id="prediggo_configuration">
	<ul>
		<li>
			<a href="#prediggo_presentation">
				{l s='Sell Smarter' mod='prediggo'}
			</a>
		</li>
		<li>
			<a href="#main_conf">
				{l s='Main Configuration' mod='prediggo'}
			</a>
		</li>
		<li>
			<a href="#export_conf">
				{l s='Export Configuration' mod='prediggo'}
			</a>
		</li>
		<li>
			<a href="#recommendation_conf">
				{l s='Recommendations Configuration' mod='prediggo'}
			</a>
		</li>
		<li>
			<a href="#search_conf">
				{l s='Search Configuration' mod='prediggo'}
			</a>
		</li>
	</ul>

	<div id="prediggo_presentation">
		<a href="http://www.prediggo.com" target="_blank" title="Prediggo">
			<img src="{$path}/img/logo.png" title="{l s='Prediggo' mod='prediggo'}" alt="{l s='Prediggo' mod='prediggo'}" /> 
		</a>
		<p>{l s='Through this module, Prediggo is offering you the possibility to increase your conversion rate by 30%. How?' mod='prediggo'} <b>{l s='Sell Smarter.' mod='prediggo'}</b></p>
		<p><span>{l s='Sell Smarter' mod='prediggo'}</span> {l s=' - Promote your products, your way.' mod='prediggo'}</p>
		<ul>
			<li>{l s='Present Up & Cross-Selling products, newest in stock, best sellers, etc...' mod='prediggo'}</li>
			<li>{l s='Automatically promote the products matching the visitor’s shopping behavior.' mod='prediggo'}</li>
			<li>{l s='Provide search and results navigation that understand shopping context.' mod='prediggo'}</li>
			<li>{l s='Bottom line, turn buyers into shoppers!' mod='prediggo'}</li>
		</ul>
		<p>{l s='As Prestashop did by using this module on their ' mod='prediggo'}
		<a href="http://addons.prestashop.com" target="_blank" title="{l s='Addons market place Addons' mod='prediggo'}">{l s='Addons market place Addons' mod='prediggo'}</a>
		{l s=', trust Prediggo to help you Sell Smarter with our ' mod='prediggo'} <b>{l s='two business critical solutions:' mod='prediggo'}</b></p>
		{assign var='lang_iso_forced' value='EN'}
		{if $lang_iso == 'fr'}
			{assign var='lang_iso_forced' value='FR'}
		{/if}
		<ul>
			<li>
				<a href="http://www.prediggo.com/tmp/addons/IntelligentSearch{$lang_iso_forced|escape:'htmlall':'UTF-8'|upper}.pdf" target="_blank" title="{l s='Very Easy & Powerful Onsite Search engine!' mod='prediggo'}">
					<img src="{$path}/img/LienIntelligentSearch{$lang_iso_forced|escape:'htmlall':'UTF-8'|upper}.png" title="{l s='Very Easy & Powerful Onsite Search engine!' mod='prediggo'}" alt="{l s='Very Easy & Powerful Onsite Search engine!' mod='prediggo'}" /> 
				</a>
			</li>
			<li>
				<a href="http://www.prediggo.com/tmp/addons/SemanticMerchandising{$lang_iso_forced|escape:'htmlall':'UTF-8'|upper}.pdf" target="_blank" title="{l s='A Personalized eMerchandising engine' mod='prediggo'}">
					<img src="{$path}/img/LienSemanticMerchandising{$lang_iso_forced|escape:'htmlall':'UTF-8'|upper}.png" title="{l s='A Personalized eMerchandising engine' mod='prediggo'}" alt="{l s='A Personalized eMerchandising engine' mod='prediggo'}" /> 
				</a>
			</li>
		</ul>
		<p>{l s='To inquire about our solutions and set up the module, it could not be easier!' mod='prediggo'}</p>
		<ol>
			<li>
				{l s='Contact us through our website ' mod='prediggo'} <a href="http://www.prediggo.com" target="_blank" title="{l s='Contact us through our website ' mod='prediggo'}">{l s='>> HERE <<' mod='prediggo'}</a>
			</li>
			<li>
				{l s='Or give us a call +41 (0) 21 550 51 35' mod='prediggo'}
			</li>
		</ol>
	</div>
	<div id="main_conf"></div>
	<div id="export_conf"></div>
	<div id="recommendation_conf"></div>
	<div id="search_conf"></div>
</div>