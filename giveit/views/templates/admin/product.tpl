{*
* 2013 Give.it
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to support@give.it so we can send you a copy immediately.
*
* @author JSC INVERTUS www.invertus.lt <help@invertus.lt>
* @copyright 2013 Give.it
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* International Registered Trademark & Property of Give.it
*}
<script>
    var giveit_ajax_url = '{$smarty.const._GIVEIT_AJAX_URL_|escape:'html':'UTF-8'}';
    var giveit_token = '{$module_token|escape:'html':'UTF-8'}';
    var id_product = '{$smarty.get.id_product|escape:'html':'UTF-8'}';
    var id_shop = '{$id_shop|escape:'html':'UTF-8'}';
</script>

<h4>{l s='Give.it settings' mod='giveit'}</h4>
<div class="separation"></div>
<p class="list info">
    {l s='Here you can enable or explicitly disable the Give.it button for a product and its combinations. This can also be done in the "Product settings" of the Give.it module configuration' mod='giveit'}
</p>
<div id="giveit_messages_container">
    <div class="conf alert alert-success" style="display:none"></div>
    <div class="module_error alert alert-danger" style="display:none"></div>
</div>

<table cellspacing="0" cellpadding="0" style="width: 100%; margin-bottom:10px;" class="table">
    <colgroup>
        <col width="10px">
        <col>
        <col width="70px">
    </colgroup>
    <thead>
    <tr style="height: 40px" class="nodrag nodrop">
        <th class="center"></th>
        <th class="left"><span class="title_box">{if $combinations|@count == 1 && $combinations[0].id_product_attribute == 0}{l s='Product' mod='giveit'}{else}{l s='Attributes' mod='giveit'}{/if}</span></th>
        <th class="left"></th>
    </tr>
    </thead>
    <tbody>
        {foreach from=$combinations item=combination}
        <tr class="row_hover">
            <td class="center"></td>
            <td class="left">{$combination.attributes|escape:'html':'UTF-8'}</td>
            <td class="left col-lg-2">
                <select name="combinations[{$combination.id_product_attribute|escape:'html':'UTF-8'}]">
                    <option value=""{if $combination.display_button === ''} selected="selected"{/if}>{l s='Use global settings' mod='giveit'}</option>
                    <option value="1"{if $combination.display_button === 1} selected="selected"{/if}>{l s='Display Give.it button' mod='giveit'}</option>
                    <option value="0"{if $combination.display_button === 0} selected="selected"{/if}>{l s='Hide Give.it button' mod='giveit'}</option>
                </select>
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>

{if version_compare($smarty.const._PS_VERSION_,'1.6','>=')}
<button type="button" name="saveGiveItProductSettings" class="btn btn-default pull-right">
    <i class="process-icon-save"></i>
    {l s='Update' mod='giveit'}
</button>
{else}
<input type="button" name="saveGiveItProductSettings" class="button" value="{l s='Update' mod='giveit'}" />
{/if}