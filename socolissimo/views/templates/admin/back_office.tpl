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
{if isset($supcostbelg)}{assign var=supcostbelgttc value=$supcostbelg*(1+($taxrate/100))}{/if}
<div class="warn">  <p>{l s='Warning, usage of this module in opc mobile theme is not recommended in production mode for your website.' mod='socolissimo'}</p></div>
<form action="{$smarty.server.REQUEST_URI|escape:'htmlall'}" method="post" class="form">
    <input type="hidden" value={if isset($taxrate)}{$taxrate}{else}0{/if} class="taxrate" name="taxrate" />
    <fieldset><legend><img src="{$moduleDir}/logo.gif" alt="" />{l s='Description' mod='socolissimo'}</legend>
        {l s='SoColissimo is a service offered by La Poste, which allows you to offer buyers 5 modes of delivery.' mod='socolissimo'} :
        <br/><br/><ul style ="list-style:disc outside none;margin-left:30px;">
            <li>{l s='Home delivery' mod='socolissimo'}.</li>
            <li>{l s='Home delivery (with appointment) between 5pm and 9:30pm ' mod='socolissimo'}.</li>
            <li>{l s='Delivery in one of 31 Cityssimo locations 24/7' mod='socolissimo'}.</li>
            <li>{l s='Delivery in one of 10 000 post offices ' mod='socolissimo'}.</li>
            <li>{l s='Delivery in one of the many pickup points of the La Poste partner network' mod='socolissimo'}.</li>
        </ul>
        <p>{l s='This module is free and allows you to activate the offer on your store.' mod='socolissimo'}</p>
    </fieldset>
    <div class="clear">&nbsp;</div>
    <fieldset><legend><img src="{$moduleDir}/logo.gif" alt="" />{l s='Settings' mod='socolissimo'}</legend>
        <label style="color:#CC0000;text-decoration : underline;">{l s='Important' mod='socolissimo'} : </label>
        <div class="margin-form">
            <p  style="width:500px">{l s='To open your SoColissimo account, please contact "La Poste" at this phone number: 3634 (French phone number).' mod='socolissimo'}</p>
        </div>
        <label>{l s='ID So' mod='socolissimo'} : </label>
        <div class="margin-form">
            <input type="text" name="id_user" value="{if isset($id_user)}{$id_user}{/if}" />
            <p>{l s='Id user for back office SoColissimo.' mod='socolissimo'}</p>
        </div>
        <label>{l s='Key' mod='socolissimo'} : </label>
        <div class="margin-form">
            <input type="text" name="key" value="{if isset($key)}{$key}{/if}" />
            <p>{l s='Secure key for back office SoColissimo.' mod='socolissimo'}</p>
        </div>
        <label>{l s='Preparation time' mod='socolissimo'}: </label>
        <div class="margin-form">
            <input type="text" size="5" name="dypreparationtime" value="{if isset($dypreparationtime)}{$dypreparationtime}{else}0{/if}" />{l s='Day(s)' mod='socolissimo'}
            <p>{l s='Average time for preparing your orders.' mod='socolissimo'} <br><span style="color:red">
                    {l s='Average time must match that of Coliposte back office.' mod='socolissimo'}</span></p>
        </div>
        <label>{l s='Seller expedition cost in France' mod='socolissimo'} : </label>
        <div class="margin-form">
            <input type="radio" name="costseller" id="sel_on" value="1" {if isset($costseller) && $costseller}checked="checked" {/if}'/>
                   <label class="t" for="sel_on"> <img src="../img/admin/enabled.gif" alt="{l s='Enabled' mod='socolissimo'}" title="{l s='Enabled' mod='socolissimo'}" /></label>
            <input type="radio" name="costseller" id="sel_off" value="0" {if  isset($costseller) && !$costseller} checked="checked" {/if}/>
            <label class="t" for="sel_off"> <img src="../img/admin/disabled.gif" alt="{l s='Disabled' mod='socolissimo'}'" title="{l s='Disabled' mod='socolissimo'}" /></label>
            <p>{l s='Seller expedition cost in France' mod='socolissimo'} <br><span style="color:red">
                    {l s='This cost override the normal cost for seller delivery.' mod='socolissimo'}</span></p>
        </div>
        <label>{l s='Expedition in belgium' mod='socolissimo'}: </label>
        <div class="margin-form">
            <input type="radio" name="exp_bel_active" id="exp_on" value="1" {if isset($exp_bel_activ) && $exp_bel_activ}checked="checked" {/if}'/>
                   <label class="t" for="exp_on"> <img src="../img/admin/enabled.gif" alt="{l s='Enabled' mod='socolissimo'}" title="{l s='Enabled' mod='socolissimo'}" /></label>
            <input type="radio" name="exp_bel_active" id="exp_off" value="0" {if  isset($exp_bel_activ) && !$exp_bel_activ} checked="checked" {/if}/>
            <label class="t" for="exp_off"> <img src="../img/admin/disabled.gif" alt="{l s='Disabled' mod='socolissimo'}'" title="{l s='Disabled' mod='socolissimo'}" /></label>
            <p>{l s='Enable or disable expedition in belgium.' mod='socolissimo'}</p>
        </div>
        <label>{l s='Overcost for Belgium' mod='socolissimo'}: </label>
        <div class="margin-form">
            <input type="text" size="5" class="supcostbelg" name="supcostbelg" onkeyup="this.value = this.value.replace(/,/g, '.');" value="{if isset($supcostbelg)}{$supcostbelg}{else}0{/if}" /> HT
            <input type="text" size="5" name="costbelgttc" class="costbelgttc" value="{if isset($supcostbelgttc)}{$supcostbelgttc|number_format:2:".":""}{else}0{/if}" readonly/> TTC
            <p>{l s='Overcost for Belgium' mod='socolissimo'} <br><span style="color:red">
                    {l s='Additional cost for Belgium must match that of Coliposte back office.' mod='socolissimo'}</span></p>
        </div>
        <label>{l s='Additional cost' mod='socolissimo'} : </label>
        <div class="margin-form">
            <input size="11" type="text" size="5" name="overcost" onkeyup="this.value = this.value.replace(/,/g, '.');"
                   value="{if isset($overcost)}{$overcost}{else}0{/if}" />
            <p>{l s='Additional cost of delivery with appointment.' mod='socolissimo'} <br><span style="color:red">
                    {l s='Additional cost must match that of Coliposte back office.' mod='socolissimo'}</span></p>
        </div>
        <div class="margin-form">
            <p>--------------------------------------------------------------------------------------------------------</p>
            <span style="color:red">
                {l s='Be VERY CAREFUL with these settings, any changes may cause the module to malfunction.' mod='socolissimo'}
            </span>
        </div>
        <label>{l s='Url So' mod='socolissimo'} : </label>
        <div class="margin-form">
            <input type="text" size="45" name="url_so" value="{if isset($url_so)}{$url_so|escape:'htmlall':'UTF-8'}{/if}" />
            <p>{l s='Url of back office SoColissimo.' mod='socolissimo'}</p>
        </div>
        <label>{l s='Url So Mobile' mod='socolissimo'} : </label>
        <div class="margin-form">
            <input type="text" size="45" name="url_so_mobile" value="{if isset($url_so_mobile)}{$url_so_mobile|escape:'htmlall':'UTF-8'}{/if}" />
            <p>{l s='Url of back office SoColissimo Mobile. Customers with smartphones or ipad will be redirect there. Warning, this url do not allow delivery in belgium' mod='socolissimo'}</p>

        </div>
        <label>{l s='Display Mode' mod='socolissimo'} : </label>
        <div class="margin-form">
            <input type="radio" name="display_type" id="classic_on" value="0" {if isset($display_type) && !$display_type} checked="checked" {/if}/>
            <label class="t" for="classic_on"> Classic </label>
            <input type="radio" name="display_type" id="fancybox_on" value="1" {if isset($display_type) && $display_type == 1} checked="checked" {/if}/>
            <label class="t" for="fancybox_on"> Fancybox </label>
            <input type="radio" name="display_type" id="iframe_on" value="2" {if isset($display_type) && $display_type == 2} checked="checked"{/if}/>
            <label class="t" for="iframe_on"> iFrame </label>
            <p>{l s='Choose your display mode for windows Socolissimo' mod='socolissimo'}</p>
        </div>
        <label>{l s='Supervision' mod='socolissimo'} : </label>
        <div class="margin-form">
            <input type="radio" name="sup_active" id="active_on" value="1" {if isset($sup_active) && $sup_active}checked="checked" {/if}/>
            <label class="t" for="active_on"> <img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" /></label>
            <input type="radio" name="sup_active" id="active_off" value="0" ' .{if isset($sup_active) && !$sup_active}checked="checked"{/if}/>
                   <label class="t" for="active_off"> <img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" /></label>
            <p>{l s='Enable or disable the check availability  of SoColissimo service.' mod='socolissimo'}</p>
        </div>
        <label>{l s='Url Supervision' mod='socolissimo'} : </label>
        <div class="margin-form">
            <input type="text" size="45" name="url_sup" value="{if isset($url_sup)}{$url_sup|escape:'htmlall':'UTF-8'}{/if}" />
            <p>{l s='The monitor URL is to ensure the availability of the socolissimo service. We strongly recommend that you do not disable it' mod='socolissimo'}</p>
        </div>
        <label>{l s='Allocation socolissimo' mod='socolissimo'} : </label>
        <div class="margin-form">
            <select name="id_socolissimo_allocation">
                {foreach $carrier_socolissimo as $carrier}
                      {if $carrier.id_carrier == $id_socolissimo}
                        <option value="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}" selected>{$carrier.id_carrier|escape:'htmlall':'UTF-8'} - {$carrier.name|escape:'htmlall':'UTF-8'}</option>
                    {else}
                        <option value="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}">{$carrier.id_carrier|escape:'htmlall':'UTF-8'} - {$carrier.name|escape:'htmlall':'UTF-8'}</option>
                    {/if}
                {/foreach}
            </select>
            <p>{l s='Re allocation of SoColissimo id carrier.' mod='socolissimo'}</p>
        </div>
        <label>{l s='Allocation socolissimo CC' mod='socolissimo'} : </label>
        <div class="margin-form">
            <select name="id_socolissimocc_allocation">
                {foreach $carrier_socolissimo_cc as $carrier}
                    {if $carrier.id_carrier == $id_socolissimo_cc}
                        <option value="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}" selected>{$carrier.id_carrier|escape:'htmlall':'UTF-8'} - {$carrier.name|escape:'htmlall':'UTF-8'}</option>
                    {else}
                        <option value="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}">{$carrier.id_carrier|escape:'htmlall':'UTF-8'} - {$carrier.name|escape:'htmlall':'UTF-8'}</option>
                    {/if}
                {/foreach}
            </select>
            <p>{l s='Re allocation of SoColissimo CC id carrier.' mod='socolissimo'}</p>
        </div>
        <div class="margin-form">
            <input type="submit" value="{l s='Save' mod='socolissimo'}" name="submitSave" class="button" style="margin:10px 0px 0px 25px;" />
        </div>
    </fieldset></form>
<div class="clear">&nbsp;</div>
<fieldset><legend><img src="{$moduleDir}/logo.gif" alt="" />{l s='Information' mod='socolissimo'}</legend>
    <p>{l s='Please fill in these two addresses in your Back Office SoColissimo.' mod='socolissimo'} : </p><br>
    <label>{l s='Validation url' mod='socolissimo'} : </label>
    <div class="margin-form">
        <p>{if isset($validation_url)}{$validation_url}{/if}</p>
    </div>
    <label>{l s='Return url' mod='socolissimo'} : </label>
    <div class="margin-form">
        <p>{if isset($return_url)}{$return_url}{/if}</p>
    </div>
</fieldset>
{literal}
    <script type="text/javascript">
    $( document ).ready(function(){
    $( ".supcostbelg" ).change(function(){
    var ttc = $( ".supcostbelg" ).val() *(1+($( ".taxrate" ).val()/100));
    ttc = Math.round(ttc *100)/100;
    $( ".costbelgttc" ).val(ttc);
        });
    });
    </script>
{/literal}