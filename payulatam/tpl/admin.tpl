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

<link href="{$css|escape:'htmlall':'UTF-8'}main.css" rel="stylesheet" type="text/css">
<link href="{$css|escape:'htmlall':'UTF-8'}tabs.css" rel="stylesheet" type="text/css">
<img src="{$tracking|escape:'htmlall':'UTF-8'}" alt="tracking" style="display:none"/>
<div class="ctwrapper">
	<header>
		<div class="logo-py"><img src="{$img|escape:'htmlall':'UTF-8'}logo.png" alt="logo"></div>
		<div class="md-copy">{l s='Accept local payments on ' mod='payulatam'} <span class="tx-blue-ligth">{l s='your website' mod='payulatam'}</span></div>
		<div class="md-btnhd"> <a href="https://secure.payulatam.com/online_account/create_account.zul" class="md-btn">{l s='Open your PayU Account' mod='payulatam'}</a></div>
		<div class="md-icos">
			<ul>
				<li><img src="{$img|escape:'htmlall':'UTF-8'}{l s='ico-credito.png' mod='payulatam'}" alt="ico1"></li>
				<li><img src="{$img|escape:'htmlall':'UTF-8'}{l s='ico-pago.png' mod='payulatam'}" alt="ico2"></li>
				<li><img src="{$img|escape:'htmlall':'UTF-8'}{l s='ico-trans.png' mod='payulatam'}" alt="ico3"></li>
			</ul>
		</div>
	</header>
	
	<section>
		<div class="md-wrapper">
			<div class="md-tl">Pay<span class="tx-blue-ligth">U</span> Latam  {l s='solutions will help you to' mod='payulatam'}<span class="tx-blue-ligth"> {l s='increase your online sales' mod='payulatam'}</span></div>
			<p>{l s='PayU Latam is the leading online payment service provider in Latin America with more than 20,000 clients. With more than 10 years of experience in the market, PayU Latam has the most complete anti-fraud system in the region and offers the New Generation of Payment Solutions that allows its merchants to accept more than 70 payment options in Argentina, Brazil, Chile, Colombia, Mexico, Panama and Peru.' mod='payulatam'}</p>
			<div class="iframevd">
				<iframe width="100%" height="180" src="//www.youtube-nocookie.com/embed/ZyIlxKgcWKs" frameborder="1" allowfullscreen></iframe>
			</div>

			<div class="md-col">
				<h3>{l s='Benefits' mod='payulatam'}</h3>
					<ul>
						<li>{l s='Accept different payment options in one platform: cash payments, credit cards (local and international) and bank transfers.' mod='payulatam'}</li>
						<li>{l s='With just one integration, you can receive payments in 7 countries in Latin America in local currency.' mod='payulatam'}</li>
						<li>{l s='Take advantage of the multi-language and multi-currency platform.' mod='payulatam'}</li>
						<li>{l s='Utilize the PayU Latam Checkout, which has been optimized to increase the number of completed transactions.' mod='payulatam'}</li>
						<li>{l s='Avoid large investments in infrastructure, technological developments, maintenance and management of the payment system.' mod='payulatam'}</li>
					</ul>
			</div>

			<div class="md-col">
				<h3>{l s='Security and Recognition' mod='payulatam'}</h3>
					<ul>
						<li>{l s='Anti-Fraud Control: The PayU Latam Anti-Fraud system automatically validates transactions and, when necessary, expert analysts manually verify transactions to minimize fraudulent transactions.' mod='payulatam'}</li>
						<li>{l s='PCI DSS Certification: With this certification, PayU Latam adheres to its standards and ensures the cardholder will have the highest level of security, confidentiality and integrity.' mod='payulatam'}</li>
						<li>{l s='Veracode Recognition: PayU Latam is the only Latin American company recognized for its high security standards in the development of its transactional platform and associated services.' mod='payulatam'}</li>
					</ul>
			</div>

		</div>
	</section>
	
	<footer>
		<div class="md-wrapper">
		<section class="tabs">
			<input id="tab-1" type="radio" name="radio-set" class="tab-selector-1" checked="checked" />
			<label for="tab-1" class="tab-label-1">{l s='Configuration' mod='payulatam'}</label>
			
			<input id="tab-2" type="radio" name="radio-set" class="tab-selector-2" />
			<label for="tab-2" class="tab-label-2">{l s='Credentials' mod='payulatam'}</label>
				
			<div class="clear-shadow"></div>
			<!-- Tab panes -->
			<div class="content">
				{foreach from=$tab item=div}
					 <div id="{$div.tab|escape:'htmlall':'UTF-8'}" class="{$div.style}">
						{$div.content}
					</div>
				{/foreach}
			</div>
			
		</section>	
	</footer>
</div>
