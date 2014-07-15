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

<p class="payment_module">
	<a href="javascript:$('#adyen_form').submit();">{l s='You will now be redirected to Adyen. If this does not happen automatically, please press here.' mod='adyen'}</a>
</p>


<form id="adyen_form" action="{$adyenUrl|escape:'htmlall':'UTF-8'}" method="post">
		<input type="hidden" name="merchantAccount"   value="{$merchantAccount|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="currencyCode"      value="{$currencyCode|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="skinCode"          value="{$skinCode|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="shopperEmail"      value="{$shopperEmail|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="merchantReference" value="{$merchantReference|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="paymentAmount"     value="{$paymentAmount|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="shopperReference"  value="{$shopperReference|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="shipBeforeDate"    value="{$shipBeforeDate|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="sessionValidity"   value="{$sessionValidity|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="shopperLocale"     value="{$shopperLocale|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="countryCode"       value="{$countryCode|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="recurringContract" value="{$recurringContract|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="merchantSig"       value="{$merchantSig|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="orderData"         value="{$orderData|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="resURL"            value="{$resURL|escape:'htmlall':'UTF-8'}" />
		{if $brandCode == 'ideal' && $idealIssuerId != ''}
			<input type="hidden" name="brandCode"         value="{$brandCode|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="idealIssuerId"     value="{$idealIssuerId|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="skipSelection"     value="{$skipSelection|escape:'htmlall':'UTF-8'}" />
		{/if}
</form>
<script type="text/javascript">
	$('#adyen_form').submit();
</script>