{*
* 2013 TextMaster
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to info@textmaster.com so we can send you a copy immediately.
*
* @author JSC INVERTUS www.invertus.lt <help@invertus.lt>
* @copyright 2013 TextMaster
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* International Registered Trademark & Property of TextMaster
*}
<div id="products_selection_container">
    <div id="selected_products_ids_container">
		{if isset($selected_products_ids)}
			{foreach from=$selected_products_ids item=selected_product_id}
				<input type="hidden" name="selected_products_ids[]" value="{$selected_product_id|escape:'htmlall':'UTF-8'}">
			{/foreach}
		{/if}
    </div>
    <fieldset id="fieldset_0">
        <div id="selected_products_form">
            <div id="selected_products_form">
                <div class="leadin"></div>
                <input type="hidden" value="0" name="submitFilterproductsList" id="submitFilterproductsList">
                <table name="list_table" class="table_grid">
                    <tbody>
                        <tr>
                            <td style="vertical-align: bottom;">
                                <span style="float: left;">
                                    {if $page > 1}
                                        <input type="image" src="../img/admin/list-prev2.gif" onclick="getE('submitFilterproject').value=1"/>&nbsp;
                                        <input type="image" src="../img/admin/list-prev.gif" onclick="getE('submitFilterproject').value={$page|escape:'htmlall':'UTF-8' - 1}"/>
                                    {/if}
                                    {l s='Page' mod='textmaster'} <b>{$page|escape:'htmlall':'UTF-8'}</b> / {$total_pages|escape:'htmlall':'UTF-8'}
                                    {if $page < $total_pages}
                                        <input type="image" src="../img/admin/list-next.gif" onclick="getE('submitFilterproject').value={$page|escape:'htmlall':'UTF-8' + 1}"/>&nbsp;
                                        <input type="image" src="../img/admin/list-next2.gif" onclick="getE('submitFilterproject').value={$total_pages|escape:'htmlall':'UTF-8'}"/>
                                    {/if}
                                    | {l s='Display' mod='textmaster'}
                                    <select name="pagination" onchange="submit()">
                                        {foreach $pagination AS $value}
                                            <option value="{$value|intval|escape:'htmlall':'UTF-8'}"{if $selected_pagination == $value} selected="selected" {elseif $selected_pagination == NULL && $value == $pagination[1]} selected="selected2"{/if}>{$value|intval|escape:'htmlall':'UTF-8'}</option>
                                        {/foreach}
                                    </select>
                                    / {$list_total|escape:'htmlall':'UTF-8'} {l s='result(s)' mod='textmaster'}
                                </span>
                                <span class="clear"></span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table cellspacing="0" cellpadding="0" style="width: 100%; margin-bottom:10px;" class="table  productsList">
                                    <colgroup>
                                        <col width="10px">
                                        <col width="50px">
                                        <col width="70px">
                                        <col>
                                    </colgroup>
                                    <thead>
                                        <tr style="height: 40px" class="nodrag nodrop">
                                            <th class="center">
                                                <input type="checkbox" onclick="checkDelBoxes(this.form, 'productsListBox[]', this.checked)" class="noborder" name="checkme">
                                            </th>
                                            <th class="center">
                                                <span class="title_box">
                                                    {l s='ID' mod='textmaster'}
                                                </span>
                                            </th>
                                            <th class="center">
                                                <span class="title_box">
                                                    {l s='Photo' mod='textmaster'}
                                                </span>
                                            </th>
                                            <th class="center">
                                                <span class="title_box">
                                                    {l s='Name' mod='textmaster'}
                                                </span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {if !empty($products)}
                                            {section name=ii loop=$products}
                                                <tr class="row_hover">
                                                    <td class="center">
                                                        <input type="checkbox" class="noborder" value="{$products[ii].id_product|escape:'htmlall':'UTF-8'}" name="productsListBox[]">
                                                    </td>
                                                    <td class="center">
                                                        {$products[ii].id_product|escape:'htmlall':'UTF-8'}
                                                    </td>
                                                    <td class="center">
                                                        <img src="{$products[ii].image|escape:'htmlall':'UTF-8'}" alt="image" />
                                                    </td>
                                                    <td class="center">
                                                        {$products[ii].name|escape:'htmlall':'UTF-8'}
                                                    </td>
                                                </tr>
                                            {/section}
                                        {else}
                                            <tr>
                                                <td colspan="4" class="center">
                                                    {l s='No items found' mod='textmaster'}
                                                </td>
                                            </tr>
                                        {/if}
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </fieldset>
</div>