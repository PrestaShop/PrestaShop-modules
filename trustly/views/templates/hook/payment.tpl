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

<p class="payment_module clearfix">
	<a href="{$trustly_url}" title="{l s='Pay with Trustly' mod='trustly'}" style="height:auto">
		<span style="float:left;height:40px; line-height: 38px;">
			<img src="{$module_dir}trustly.png" alt="{l s='Trustly' mod='trustly'}" style="margin-top:-8px;"/>
			{l s='New payment method!' mod='trustly'}
		</span>
		<br />
		<span style="float:left;text-align:justify">
			{l s='Achieve your payment through direct bank e-payment online without leaving this website. We will connect you with your online bank with maximum security. Trustly is ease to use and you don\'t need to sign-up or register.' mod='trustly'}<br />
			<br />
			{l s='Examples of banks available through Trustly: BBVA, La Caixa, Bankia, Sabadell, Banco Popular, Banco Pastor, Bankinter and ING Direct.' mod='trustly'}
		</span>
		<br class="clear" />
	</a>
</p>
