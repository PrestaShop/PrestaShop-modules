{*
* Adyen Payment Module
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
*  @author Rik ter Beek <rikt@adyen.com>
*  @copyright  Copyright (c) 2013 Adyen (http://www.adyen.com)
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{extends file="helpers/form/form.tpl"}

{block name="leadin"}
	<div class="adyen-info">
		<img src="../modules/adyen/img/adyen_logo_large.png" width="400" height="132" alt="{l s='Adyen' mod='adyen'}"/>
			
		<div style="clear:both;"></div>
	
		<div class="adyen-payement-info left">
			<h2>Adyen Payment</h2>
			
			<p>
				Adyen is a global multichannel payment company offering businesses a fully outsourced payment solution which enables merchants accept payments from anywhere in the world and provides the next generation global payment solution for mid, large and enterprise e-commerce merchants. Adyen has built its platform by combining the latest technology with over ten year’s experience in running high-volume payment systems. Adyen's online payment platform hooks up to over 200 relevant payment methods across North America, Latin America, Europe, Asia Pacific and Oceania.
			</p>
			<p>
				Examples of payment types accepted by the Adyen payment gateway:
				<ul class="adyen-list">
					<li>Visa</li>
					<li>MasterCard</li>
					<li>Maestro International</li>
					<li>America express</li>
					<li>PayPal</li>
					<li>Bank transfer in most European countries</li>
					<li>Ideal</li>
					<li>Giro pay</li>
					<li>V PAY</li>
				</ul>
			</p>
		</div>
		
		<div class="adyen-payement-info right">
			<h2>Create eCommerce Account</h2>
			<p>
			 To receive a test account, please complete a short form.
			</p>
			<p>
			Our support team will set up your test account and contact you within one business day. 
			</p>
			
			<a target="_blank" class="adyen-green-button" href="https://www.adyen.com/signup/ecom/"> Request an eCommerce test account <i class="icon-caret-right"></i></a>
		</div>
		
		<div class="adyen-payement-info right">
			<h2>Support & Documentation</h2>
			<p>
				<a target="_blank" href="https://support.adyen.com/index.php?/Knowledgebase/Article/View/1711/0/plugin-for-prestashop">Click here to download the manual how to setup this plugin</a>
			</p>
			<p>
				If you have any questions please visit our website <a target="_blank" href="http://adyen.com">Adyen.com</a> or mail to <a href="mailto:support@adyen.com">support@adyen.com</a>
			</p>
			<p><a href="https://www.adyen.com/signup/" target="_blank">For more information about sign up at Adyen click here</a></p>
		</div>
		
		<div class="adyen-payement-info right-full">
			<h2>Online payments</h2>
			
			<p>
			Now that e-commerce has matured globally there are a few main industry themes merchants are facing. Internationalization. Optimization. And security. Adyen has designed an online payment solution that has a solid grip on these themes. A solution that will help you reach the next level in your international e-commerce activities.
			</p>
			
			<ul class="adyen-list">
				<li><a target="_blank" href="https://www.adyen.com/products/online-payments/skin-technology/">Skin technology</a></li>
    			<li><a target="_blank" href="https://www.adyen.com/products/online-payments/single-screen-payment-page/">Single Screen Payment Pages</a></li>
    			<li><a target="_blank" href="https://www.adyen.com/products/online-payments/one-click-payment/">One-click payment</a></li>
    			<li><a target="_blank" href="https://www.adyen.com/products/online-payments/conversion-analytics/">Conversion analytics</a></li>
    			<li><a target="_blank" href="https://www.adyen.com/products/online-payments/ab-testing-of-payment-pages/">A/B testing of payment pages</a></li>
			</ul>
			
		</div>
		
		<div style="clear:both;"></div>
		
	</div>
	
	
	
{/block}