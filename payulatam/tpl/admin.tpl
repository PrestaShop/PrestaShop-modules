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

<link href="{$css|escape:'htmlall':'UTF-8'}bootstrap.min.css" rel="stylesheet" type="text/css">
<link href="{$css|escape:'htmlall':'UTF-8'}style.css" rel="stylesheet" type="text/css">
<img src="{$tracking|escape:'htmlall':'UTF-8'}" alt="tracking" style="display:none"/>
<div class="md-contenedor row">
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

			<header class="md-header">
				<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
					<div class="md-log"><img src="{$img|escape:'htmlall':'UTF-8'}logo.png" alt=""></div>
					<div class="md-copy">{l s='Accept local payments on ' mod='payulatam'} <span class="md-blue-light">{l s='your website' mod='payulatam'}</span></div>
					<div class="md-btn-action">
						<a href="https://secure.payulatam.com/online_account/create_account.zul" target="_blank"><button class="md-btn">{l s='Open your PayU Account' mod='payulatam'}</button></a>
					</div>
				</div>
				<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 md-top">
					<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 md-left"><img src="{$img|escape:'htmlall':'UTF-8'}{l s='ico-pago.png' mod='payulatam'}" alt=""></div>
					<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 md-left"><img src="{$img|escape:'htmlall':'UTF-8'}{l s='ico-credito.png' mod='payulatam'}" alt=""></div>
					<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 md-left"><img src="{$img|escape:'htmlall':'UTF-8'}{l s='ico-trans.png' mod='payulatam'}" alt=""></div>
				</div>
				<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4"></div>
			</header>

			<section class="md-section">
				<div class="md-p1">Pay<span class="md-blue-light">U</span> Latam {l s='solutions will help you to' mod='payulatam'} <br> <span class="md-blue-light">{l s='increase your online sales' mod='payulatam'}</span> </div>
				<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
					<div class="md-p2"> {l s='PayU Latam is the leading online payment service provider in Latin America with more than 20,000 clients. With more than 10 years of experience in the market, PayU Latam has the most complete anti-fraud system in the region and offers the New Generation of Payment Solutions that allows its merchants to accept more than 70 payment options in Argentina, Brazil, Chile, Colombia, Mexico, Panama and Peru.' mod='payulatam'} </div>
				</div>
				<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
					<iframe width="80%" height="180" src="//www.youtube-nocookie.com/embed/ZyIlxKgcWKs" frameborder="1" allowfullscreen></iframe>
				</div>

					
			</section>

			<section class="md-section">
				<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 md-col">
					<h4 class="md-h4">{l s='Benefits' mod='payulatam'}</h4>
					<ul>
						<li>{l s='Accept different payment options in one platform: cash payments, credit cards (local and international) and bank transfers.' mod='payulatam'}</li>
						<li>{l s='With just one integration, you can receive payments in 7 countries in Latin America in local currency.' mod='payulatam'} </li>
						<li>{l s='Take advantage of the multi-language and multi-currency platform.' mod='payulatam'}</li>
						<li>{l s='Utilize the PayU Latam Checkout, which has been optimized to increase the number of completed transactions.' mod='payulatam'}</li>
						<li>{l s='Avoid large investments in infrastructure, technological developments, maintenance and management of the payment system.' mod='payulatam'}</li>
					</ul>
				</div>
				<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 md-col">
					<h4 class="md-h4">{l s='Security and Recognition' mod='payulatam'}</h4>
					<ul>
						<li>{l s='Anti-Fraud Control: The PayU Latam Anti-Fraud system automatically validates transactions and, when necessary, expert analysts manually verify transactions to minimize fraudulent transactions.' mod='payulatam'}   </li>
						<li>{l s='PCI DSS Certification: With this certification, PayU Latam adheres to its standards and ensures the cardholder will have the highest level of security, confidentiality and integrity.' mod='payulatam'}</li>
						<li>{l s='Veracode Recognition: PayU Latam is the only Latin American company recognized for its high security standards in the development of its transactional platform and associated services.' mod='payulatam'} </li>
					</ul>
				</div>
			</section>

			<section class="md-section">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 md-mg">
					<!-- Nav tabs -->
					<ul class="nav nav-tabs" role="tablist">
						<li class="active"><a href="#conf" role="tab" data-toggle="tab">{l s='Configuration' mod='payulatam'}</a></li>
						<li><a href="#crendeciales" role="tab" data-toggle="tab">{l s='Credentials' mod='payulatam'}</a></li>
					</ul>
					
					<!-- Tab panes -->
					<div class="tab-content">
						{foreach from=$tab item=div}
							 <div id="{$div.tab|escape:'htmlall':'UTF-8'}" class="tab-pane {if $div.selected}active{/if} md-pad">
								{$div.content}
							</div>
						{/foreach}
					</div>
				</div>
			</section>
		</div>
	</div>	
</div>
{foreach from=$script item=link}
<script type="text/javascript" src="{$link|escape:'htmlall':'UTF-8'}"></script>
{/foreach}
