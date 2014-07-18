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
*  @version  Release: $Revision: 6594 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark  Property of PrestaShop SA
*}

{if isset($err)}
    <div class="error">
     <p>{l s='An error occurred during the preparation of the payment.' mod='syspay'}</p>
     <p>{l s='Please try again later or contact the support.' mod='syspay'}</p>
    </div>
{/if}
<div class="payment_module">
    <p style="display: inline-block">
        <a href="javascript:$('form[name=syspay_form_direct]').submit();" style="text-transform: none">
            <img style="padding-top: 12px" alt="CB" src="{$module_dir|escape:'htmlall':'UTF-8'}img/button.png">
        </a>
    </p>
    <form name="syspay_form_direct" action="{$syspay_link|escape:'htmlall':'UTF-8'}" method="POST" style="vertical-align: top; display: inline-block; width: 420px; {if $SYSPAY_REBILL == 1 && isset($card)}padding-top: 25px{/if}" >
        {if isset($card) && $SYSPAY_REBILL == 1}
        {else}
            <a href="javascript:$('form[name=syspay_form_direct]').submit();" style="text-transform: none">
                <span style="padding-top: 15px; padding-bottom: 10px; display: block">{l s=' ' mod='syspay'} {l s='Pay safely using your preferred payment method' mod='syspay'}</span>
            </a>
        {/if}
        <input type="hidden" name="direct_payment" value="1" />
        {if $SYSPAY_REBILL == 1 && !isset($card)}
            <input type="checkbox" name="SP_REBILL" />
            <label for="SP_REBILL" style="text-transform: none">{l s='Authorize SysPay to store data securely in your customer profile for future 1-Click payments' mod='syspay'}</label>
        {elseif $SYSPAY_REBILL == 1 && isset($card)}
            <input type="checkbox" name="rebill" value="{$card.id|escape:'htmlall':'UTF-8'}" checked="checked" />
            <span style="text-transform: none;">{l s=' ' mod='syspay'}
                {l s='Authorize SysPay to charge your card ending for ' mod='syspay'}
                {$card.display|escape:'htmlall':'UTF-8'}
                {l s='for this purchase' mod='syspay'}
                            </span>
        {/if}
        <input type="hidden" name="SP_AUTHORIZED" value="{$SYSPAY_AUTHORIZED_PAYMENT|escape:'htmlall':'UTF-8'}" checked="checked" />
        {foreach from=$syspay_params key=syspay_key item=syspay_value}
            <input type="hidden" name="{$syspay_key|escape:'htmlall':'UTF-8'}" value="{$syspay_value|escape:'htmlall':'UTF-8'}" />
        {/foreach}
    </form>
</div>