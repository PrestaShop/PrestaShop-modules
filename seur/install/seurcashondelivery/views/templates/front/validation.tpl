{*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2014 PrestaShop SA
*  @version  Release: 0.4.4
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{capture name=path}
    {l s='Shipping' mod='seurcashondelivery'}
{/capture}
{literal}
<style>
#module-seurcashondelivery-validation #center_column {width: 757px;}
</style>
{/literal}
{include file="$tpl_dir./breadcrumb.tpl"}
<h2>{l s='Order summation' mod='seurcashondelivery'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<h3>{l s='Cash on delivery by SEUR' mod='seurcashondelivery'}</h3>

<form action="{$link->getModuleLink('seurcashondelivery', 'validation', [], true)|escape:'htmlall':'UTF-8'}" method="post">
        <input type="hidden" name="confirm" value="1" />
        <p>
                <img src="{$this_path|escape:'htmlall':'UTF-8'}img/logo.gif" alt="{l s='Cash on delivery by SEUR' mod='seurcashondelivery'}" style="float:left; margin: 0px 10px 5px 0px;" />
                {l s='You have chosen the cash on delivery method by SEUR.' mod='seurcashondelivery'}
                <br/><br />
                {l s='The total of your order is:' mod='seurcashondelivery'}
                <span id="amount_coste" class="price">{convertPrice price=$coste|floatval}</span><br /><br />
                {l s='The total fee is:' mod='seurcashondelivery'}
                <span id="amount_cargo" class="price">{convertPrice price=$cargo|floatval}</span><br /><br />
                {l s='The total amount is:' mod='seurcashondelivery'}
                <span id="amount_total" class="price">{convertPrice price=$total|floatval}</span>
        </p>
        <p>
                <br /><br />
                <br /><br />
                <b>{l s='Please confirm your order by clicking on "confirm my order".' mod='seurcashondelivery'}</b>
        </p>
        <p class="cart_navigation">
                <a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}order.php?step=3" class="button_large">{l s='Other payment methods.' mod='seurcashondelivery'}</a>
                <input type="submit" name="submit" value="{l s='Confirm my order' mod='seurcashondelivery'}" class="exclusive_large" />
        </p>
</form>