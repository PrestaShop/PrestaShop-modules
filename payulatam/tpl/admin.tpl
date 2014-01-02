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

<link href="{$css|escape:'htmlall':'UTF-8'}" rel="stylesheet" type="text/css">
<img src="{$tracking}" alt="tracking" style="display:none"/>
<div class="payu-module-wrapper">
	<div class="payu-module-inner-wrap">
		<img src="{$logo|escape:'htmlall':'UTF-8'}" alt="logo" class="payu-logo" />
		<p class="payu-module-intro">{l s='The payment solutions offered by PayU Latam adapt to any type of company or business: big or small, beginner or experienced, local or multinational, already selling online or willing to accept payments through the web.' mod='payulatam'}<br /><br />
		<a class="payu-button" href="http://www.latinamericanpayments.com/prestashop-{$lang}">{l s='Open your FREE account today!' mod='payulatam'}</a></p>
		<div class="payu-module-right-col">
			<h1>{l s='Start selling in Latin America now!' mod='payulatam'}</h1>
			<ul>
				<li>{l s='Accept' mod='payulatam'} <strong>{l s='different payment methods in a single platform:' mod='payulatam'}</strong> {l s='cash deposits, bank transfers and credit cards.' mod='payulatam'}</li>
				<li>{l s='Receive payments in' mod='payulatam'} <strong>{l s='several countries in Latin America' mod='payulatam'}</strong> {l s='with a single integration.' mod='payulatam'}</li>
				<li><strong>{l s='Multi-language' mod='payulatam'}</strong> {l s='and' mod='payulatam'} <strong>{l s='multi-currency' mod='payulatam'}</strong> {l s='options.' mod='payulatam'}</li>
				<li>{l s='Reduce the risk of selling online by a powerful' mod='payulatam'} <strong>{l s='Anti-Fraud system' mod='payulatam'}</strong> {l s='and transactions control.' mod='payulatam'}</li>
				<li>{l s='Prevent major investments in infrastructure, technic developments, maintenance and administration of the payment system.' mod='payulatam'}</li>
			</ul>
			<h2>{l s='Safety and Support' mod='payulatam'}</h2>
			<ul class="payu-3cols">
				<li><p class="payu-small-col1"><strong>{l s='Anti-fraud Control:' mod='payulatam'}</strong><br />{l s='Anti-fraud module for automatic validation of transactions and manual verification procedures, carried out by experts in analysis and identification of fraud.' mod='payulatam'}</p></li>
				<li><p class="payu-small-col2"><strong>{l s='Certified PCI DSS:' mod='payulatam'}</strong><br />{l s='With this certification, PayU Latam ensures the protection, confidentiality, and integrity of the card holder information.' mod='payulatam'}</p></li>
				<li><p class="payu-small-col3"><strong>{l s='Veracode Recognition:' mod='payulatam'}</strong><br />{l s='The unique award-winning company in Latin America thanks to its high safety standards in the development of its transactional platform and related services.' mod='payulatam'}</p></li>
			</ul>
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
