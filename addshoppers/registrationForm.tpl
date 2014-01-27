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
<form action="{$action}" method="post">
        <fieldset class="width2" id="addshoppers-setup">
                <legend><img src="{$module_dir}../../img/admin/cog.gif" alt="" class="middle" />{l s='Create New Account' mod='addshoppers'}</legend>

                <label>{l s='Email Address' mod='addshoppers'}</label>
                <div class="margin-form">
                        <input type="text" name="addshoppers_email" value="{$email}" />
                </div>

                <label>{l s='Password' mod='addshoppers'}</label>
                <div class="margin-form">
                        <input type="password" name="addshoppers_password" value="{$password}" />
                </div>

                <label>{l s='Confirm Password' mod='addshoppers'}</label>
                <div class="margin-form">
                        <input type="password" name="addshoppers_confirm_password" value="{$confirmPassword}" />
                </div>

                <label>{l s='Category' mod='addshoppers'}</label>
                <div class="margin-form">
                        <select name="addshoppers_category" id="addshoppers_category">
                        {foreach item=item from=$categories}
                            {if $item.value eq $category}
                                <option value="{$item.value}" selected="selected">{$item.label}</option>
                            {else}
                                <option value="{$item.value}">{$item.label}</option>
                            {/if}
                        {/foreach}
                        </select>
                </div>

                <label>{l s='Phone (optional)' mod='addshoppers'}</label>
                <div class="margin-form">
                        <input type="text" name="addshoppers_phone" value="{$phone}" />
                </div>

                <center><input type="submit" name="addshoppers_registration" value="{l s='Create Free Account' mod='addshoppers'}" class="button" /></center>
        </fieldset>
</form>
