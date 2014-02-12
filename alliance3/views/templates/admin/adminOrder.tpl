{*
* 2007-2013 PrestaShop
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

<br/>
<fieldset {if $version == 0} style="width:400px"{/if}>
  <legend><img src="../modules/merchantware/logo.gif" />{l s='Merchant Ware Option' mod='alliance3'}</legend>
  <p>{l s='Change the status into "Cancel" to void the payment, The order will also be cancelled' mod='alliance3'}</p>
  <p>{l s='Change the status into "Refund" to refund the total price of the order' mod='alliance3'}</p>
  {if isset($error)}
  <p style="color:red">{$error|escape:'htmlall':'UTF-8'}</p>
  {/if}
  {if isset($message)}
  <p style="color:green">{$message|escape:'htmlall':'UTF-8'}</p>
  {/if}
</fieldset>
