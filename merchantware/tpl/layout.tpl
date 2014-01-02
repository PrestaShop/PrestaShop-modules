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

<form action="{$formLayout|escape:'htmlall':'UTF-8'}" method="POST" id="merchantWareHouseLayout">
<fieldset>
  <h4>{$layoutTitle|escape:'htmlall':'UTF-8'}</h4>
  <p>{$layoutText|escape:'htmlall':'UTF-8'}</p>
  {foreach from=$layoutInputVar item=input key=name}
  <label from="{$name|escape:'htmlall':'UTF-8'}">{$input.0|escape:'htmlall':'UTF-8'}</label>
  <div class="margin-form">
    <input type="text" name="{$name|escape:'htmlall':'UTF-8'}" id="{$name|escape:'htmlall':'UTF-8'}" value="{$input.2|escape:'htmlall':'UTF-8'}" /> {$input.1|escape:'htmlall':'UTF-8'}
  </div>
  {/foreach}
  <div class="margin-form">
    <input type="submit" class="button" value="{l s='Save' mod='merchantware'}" />
  </div>
</fieldset>
</form>
