{*
* Prestashop PaymentSense Re-Directed Payment Module
* Copyright (C) 2013 PaymentSense. 
*
* This program is free software: you can redistribute it and/or modify it under the terms
* of the AFL Academic Free License as published by the Free Software Foundation, either
* version 3 of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
* without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
* See the AFL Academic Free License for more details. You should have received a copy of the
* AFL Academic Free License along with this program. If not, see <http://opensource.org/licenses/AFL-3.0/>.
*
*  @author PaymentSense <devsupport@paymentsense.com>
*  @copyright  2013 PaymentSense
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<p class="payment_module" style="font-size:16px; font-weight:bold;">
  <a href="javascript:$('#paymentsense_FORM').submit();" title="{l s='Debit and Credit Card Payments via PaymentSense' mod='paymentsense'}">
     {l s='Click here to complete your payment' mod='paymentsense'} <br>
    <br><img src="{$module_template_dir|escape:'htmlall':'UTF-8'}img/PaymentSenseCards.png" width="400" style="width:400px;" alt="{l s='Debit and Credit Card Payments via PaymentSense' mod='paymentsense'}" />
  </a>
</p>

<form action="{$form_target|escape:'htmlall':'UTF-8'}" method="post" id="paymentsense_FORM" class="hidden">
{foreach from=$parameters key=parameter_name item=parameter_value}
   <input type="hidden" name="{$parameter_name|escape:'htmlall':'UTF-8'}" value="{$parameter_value|escape:'htmlall':'UTF-8'}" />
{/foreach}
</form>