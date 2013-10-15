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

<h3>{l s='Sorry!' mod='paymentsense'}</h3>
<p><b>{l s='Sorry, your transaction didn\'t go through.' mod='paymentsense'}</b><br /><br />
{l s='If you would like to try again please' mod='paymentsense'} {$advice|escape:'htmlall':'UTF-8'}.<br /><br />
{l s='If you are having problems please' mod='paymentsense'} <a href="{$contactURL|escape:'htmlall':'UTF-8'}">{l s='get in touch' mod='paymentsense'}</a>.