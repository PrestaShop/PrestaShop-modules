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

<h3>{l s='What happens now?' mod='paymentsense'}</h3>
<p><b>{l s='Thanks, it takes a few minutes for our system to update your order status.' mod='paymentsense'}</b><br /><br />
{l s='If your payment went through you will receive confirmation via email shortly.' mod='paymentsense'}<br /><br />
{l s='If not you can' mod='paymentsense'} <a href="{$cartURL|escape:'htmlall':'UTF-8'}" target="_parent">{l s='try again' mod='paymentsense'}</a>{l s=', or if you need help' mod='paymentsense'}<br /><br />
<a href="{$contactURL|escape:'htmlall':'UTF-8'}" target="_parent">{l s='get in touch.' mod='paymentsense'}</a></p>