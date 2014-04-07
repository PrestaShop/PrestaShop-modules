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
<table name="list_table" class="table_grid">
	<tbody>
		<tr>
			<td style="vertical-align: bottom;">
				<span style="float: left;">
					{if $page > 1}
						<input type="image" src="../img/admin/list-prev2.gif" onclick="getProductsByPage(1)"/>&nbsp;
						<input type="image" src="../img/admin/list-prev.gif" onclick="getProductsByPage({$page|escape:'htmlall':'UTF-8' - 1})"/>
					{/if}
					{l s='Page' mod='giveit'} <b>{$page|escape:'htmlall':'UTF-8'}</b> / {$total_pages|escape:'htmlall':'UTF-8'}
					{if $page < $total_pages}
						<input type="image" src="../img/admin/list-next.gif" onclick="getProductsByPage({$page|escape:'htmlall':'UTF-8' + 1})"/>&nbsp;
						<input type="image" src="../img/admin/list-next2.gif" onclick="getProductsByPage({$total_pages|escape:'htmlall':'UTF-8'})"/>
					{/if}
					| {l s='Display' mod='giveit'}
					<select name="pagination">
						{foreach $pagination AS $value}
							<option value="{$value|intval|escape:'htmlall':'UTF-8'}"{if $selected_pagination == $value} selected="selected" {elseif $selected_pagination == NULL && $value == $pagination[1]} selected="selected2"{/if}>{$value|intval|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
					/ {$list_total|escape:'htmlall':'UTF-8'} {l s='result(s)' mod='giveit'}
				</span>
				<span style="float: right;">
					<input type="submit" class="button" value="Reset" name="submitResetproductsList">
					<input type="submit" class="button" value="Filter" name="submitFilter" id="submitFilterButtonproductsList">
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
                        <col>
                        <col>
                        <col width="70px">
					</colgroup>
					<thead>
						<tr style="height: 40px" class="nodrag nodrop">
							<th class="center">
								<input type="checkbox" onclick="checkDelBoxes(this.form, 'productsListBox[]', this.checked)" class="noborder" name="checkme">
							</th>
							<th class="center">
								<span class="title_box">
									{l s='ID' mod='giveit'}
								</span>
								<br>
								<a onclick="orderTable('id_product', 'desc')">
									{if isset($cookie_order_by) && isset($cookie_order_way) && $cookie_order_by == 'id_product' && $cookie_order_way == 'desc'}
										<img border="0" src="../img/admin/down_d.gif">
									{else}
										<img border="0" src="../img/admin/down.gif">
									{/if}
								</a>
								<a onclick="orderTable('id_product', 'asc')">
									{if isset($cookie_order_by) && isset($cookie_order_way) && $cookie_order_by == 'id_product' && $cookie_order_way == 'asc'}
										<img border="0" src="../img/admin/up_d.gif">
									{else}
										<img border="0" src="../img/admin/up.gif">
									{/if}
								</a>
							</th>
							<th class="center">
								<span class="title_box">
									{l s='Photo' mod='giveit'}
								</span>
							</th>
							<th class="center">
								<span class="title_box">
									{l s='Name' mod='giveit'}
								</span>
								<br>
								<a onclick="orderTable('name', 'desc')">
									{if isset($cookie_order_by) && isset($cookie_order_way) && $cookie_order_by == 'name' && $cookie_order_way == 'desc'}
										<img border="0" src="../img/admin/down_d.gif">
									{else}
										<img border="0" src="../img/admin/down.gif">
									{/if}
								</a>
								<a onclick="orderTable('name', 'asc')">
									{if isset($cookie_order_by) && isset($cookie_order_way) && $cookie_order_by == 'name' && $cookie_order_way == 'asc'}
										<img border="0" src="../img/admin/up_d.gif">
									{else}
										<img border="0" src="../img/admin/up.gif">
									{/if}
								</a>
							</th>
                            <th class="center">
                                {l s='Product combinations' mod='giveit'}
                            </th>
                            <th class="center">
                                {l s='Give.it settings' mod='giveit'}
                            </th>
                            <th class="center">
                                
                            </th>
						</tr>
						<tr style="height: 35px;" class="nodrag nodrop filter row_hover">
							<td class="center">
								--
							</td>
							<td class="center">
								<input type="text" style="width:50px" value="{if isset($cookie_productsListFilter_id_product)}{$cookie_productsListFilter_id_product|escape:'htmlall':'UTF-8'}{/if}" name="p.id_product" class="filter">
							</td>
							<td class="center">
								--
							</td>
							<td class="center">
								<input type="text" style="width:95%" value="{if isset($cookie_productsListFilter_name)}{$cookie_productsListFilter_name|escape:'htmlall':'UTF-8'}{/if}" name="pl.name" class="filter">
							</td>
                            <td class="center">
                                --
                            </td>
                            <td class="center">
                                --
                            </td>
                            <td class="center">
                                --
                            </td>
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
                                    <td class="center">
                                        <select name="combination_select">
                                            {if !empty($products[ii].combinations)}
                                                {foreach from=$products[ii].combinations item=attribute}
                                                    <option value="{$attribute.id_product_attribute|escape:'htmlall':'UTF-8'}">{$attribute.attributes|escape:'htmlall':'UTF-8'}</option>
                                                {/foreach}
                                            {else}
                                                <option value="0">{$products[ii].name|escape:'htmlall':'UTF-8'}</option>
                                            {/if}
                                        </select>
                                    </td>
                                    <td class="center">
                                        <select name="combination_setting">
                                            <option value="">{l s='Use global settings' mod='giveit'}</option>
                                            <option value="1">{l s='Display Give.it button' mod='giveit'}</option>
                                            <option value="0">{l s='Hide Give.it button' mod='giveit'}</option>
                                        </select>
                                    </td>
                                    <td class="center">
                                        <input type="button" class="button update_product_setting" value="Update" />
                                    </td>
								</tr>
							{/section}
						{else}
								<tr>
									<td colspan="6" class="center">
										{l s='No items found' mod='giveit'}
									</td>
								</tr>
							{/if}
					</tbody>
				</table>
			</td>
		</tr>
	</tbody>
</table>