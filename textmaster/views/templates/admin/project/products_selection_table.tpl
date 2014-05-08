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
				<span style="float: right;">
					<input type="submit" class="button" value="{l s='Reset' mod='textmaster'}" name="submitResetproductsList">
					<input type="submit" class="button" value="{l s='Filter' mod='textmaster'}" name="submitFilter" id="submitFilterButtonproductsList">
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
						<col>
						<col>
						<col>
						<col width="20px">
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
								<br>
								<a href="&amp;configure=textmaster&amp;productsListOrderby=id_product&amp;productsListOrderway=desc&amp;token=">
									{if isset($cookie_order_by) && isset($cookie_order_way) && $cookie_order_by == 'id_product' && $cookie_order_way == 'desc'}
										<img border="0" src="../img/admin/down_d.gif">
									{else}
										<img border="0" src="../img/admin/down.gif">
									{/if}
								</a>
								<a href="&amp;configure=textmaster&amp;productsListOrderby=id_product&amp;productsListOrderway=asc&amp;token=">
									{if isset($cookie_order_by) && isset($cookie_order_way) && $cookie_order_by == 'id_product' && $cookie_order_way == 'asc'}
										<img border="0" src="../img/admin/up_d.gif">
									{else}
										<img border="0" src="../img/admin/up.gif">
									{/if}
								</a>
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
								<br>
								<a href="&amp;configure=textmaster&amp;productsListOrderby=name&amp;productsListOrderway=desc&amp;token=">
									{if isset($cookie_order_by) && isset($cookie_order_way) && $cookie_order_by == 'name' && $cookie_order_way == 'desc'}
										<img border="0" src="../img/admin/down_d.gif">
									{else}
										<img border="0" src="../img/admin/down.gif">
									{/if}
								</a>
								<a href="&amp;configure=textmaster&amp;productsListOrderby=name&amp;productsListOrderway=asc&amp;token=">
									{if isset($cookie_order_by) && isset($cookie_order_way) && $cookie_order_by == 'name' && $cookie_order_way == 'asc'}
										<img border="0" src="../img/admin/up_d.gif">
									{else}
										<img border="0" src="../img/admin/up.gif">
									{/if}
								</a>
							</th>
							<th class="center">
								<span class="title_box">
									{l s='Reference' mod='textmaster'}
								</span>
								<br>
								<a href="&amp;configure=textmaster&amp;productsListOrderby=reference&amp;productsListOrderway=desc&amp;token=">
									{if isset($cookie_order_by) && isset($cookie_order_way) && $cookie_order_by == 'reference' && $cookie_order_way == 'desc'}
										<img border="0" src="../img/admin/down_d.gif">
									{else}
										<img border="0" src="../img/admin/down.gif">
									{/if}
								</a>
								<a href="&amp;configure=textmaster&amp;productsListOrderby=reference&amp;productsListOrderway=asc&amp;token=">
									{if isset($cookie_order_by) && isset($cookie_order_way) && $cookie_order_by == 'reference' && $cookie_order_way == 'asc'}
										<img border="0" src="../img/admin/up_d.gif">
									{else}
										<img border="0" src="../img/admin/up.gif">
									{/if}
								</a>
							</th>
							<th class="center">
								<span class="title_box">
									{l s='Category' mod='textmaster'}
								</span>
								<br>
								<a href="&amp;configure=textmaster&amp;productsListOrderby=category&amp;productsListOrderway=desc&amp;token=">
									{if isset($cookie_order_by) && isset($cookie_order_way) && $cookie_order_by == 'category' && $cookie_order_way == 'desc'}
										<img border="0" src="../img/admin/down_d.gif">
									{else}
										<img border="0" src="../img/admin/down.gif">
									{/if}
								</a>
								<a href="&amp;configure=textmaster&amp;productsListOrderby=category&amp;productsListOrderway=asc&amp;token=">
									{if isset($cookie_order_by) && isset($cookie_order_way) && $cookie_order_by == 'category' && $cookie_order_way == 'asc'}
										<img border="0" src="../img/admin/up_d.gif">
									{else}
										<img border="0" src="../img/admin/up.gif">
									{/if}
								</a>
							</th>
							<th class="center">
								<span class="title_box">
									{l s='Base price' mod='textmaster'}
								</span>
								<br>
								<a href="&amp;configure=textmaster&amp;productsListOrderby=price&amp;productsListOrderway=desc&amp;token=">
									{if isset($cookie_order_by) && isset($cookie_order_way) && $cookie_order_by == 'price' && $cookie_order_way == 'desc'}
										<img border="0" src="../img/admin/down_d.gif">
									{else}
										<img border="0" src="../img/admin/down.gif">
									{/if}
								</a>
								<a href="&amp;configure=textmaster&amp;productsListOrderby=price&amp;productsListOrderway=asc&amp;token=">
									{if isset($cookie_order_by) && isset($cookie_order_way) && $cookie_order_by == 'price' && $cookie_order_way == 'asc'}
										<img border="0" src="../img/admin/up_d.gif">
									{else}
										<img border="0" src="../img/admin/up.gif">
									{/if}
								</a>
							</th>
							<th class="center">
								<span class="title_box">
									{l s='Final price' mod='textmaster'}
								</span>
								<br>
								<a href="&amp;configure=textmaster&amp;productsListOrderby=final_price&amp;productsListOrderway=desc&amp;token=">
									{if isset($cookie_order_by) && isset($cookie_order_way) && $cookie_order_by == 'final_price' && $cookie_order_way == 'desc'}
										<img border="0" src="../img/admin/down_d.gif">
									{else}
										<img border="0" src="../img/admin/down.gif">
									{/if}
								</a>
								<a href="&amp;configure=textmaster&amp;productsListOrderby=final_price&amp;productsListOrderway=asc&amp;token=">
									{if isset($cookie_order_by) && isset($cookie_order_way) && $cookie_order_by == 'final_price' && $cookie_order_way == 'asc'}
										<img border="0" src="../img/admin/up_d.gif">
									{else}
										<img border="0" src="../img/admin/up.gif">
									{/if}
								</a>
							</th>
							<th class="center">
								<span class="title_box">
									{l s='Quantity' mod='textmaster'}
								</span>
								<br>
								<a href="&amp;configure=textmaster&amp;productsListOrderby=quantity&amp;productsListOrderway=desc&amp;token=">
									{if isset($cookie_order_by) && isset($cookie_order_way) && $cookie_order_by == 'quantity' && $cookie_order_way == 'desc'}
										<img border="0" src="../img/admin/down_d.gif">
									{else}
										<img border="0" src="../img/admin/down.gif">
									{/if}
								</a>
								<a href="&amp;configure=textmaster&amp;productsListOrderby=quantity&amp;productsListOrderway=asc&amp;token=">
									{if isset($cookie_order_by) && isset($cookie_order_way) && $cookie_order_by == 'quantity' && $cookie_order_way == 'asc'}
										<img border="0" src="../img/admin/up_d.gif">
									{else}
										<img border="0" src="../img/admin/up.gif">
									{/if}
								</a>
							</th>
							<th class="center">
								<span class="title_box">
									{l s='Status' mod='textmaster'}
								</span>
							</th>
						</tr>
						<tr style="height: 35px;" class="nodrag nodrop filter row_hover">
							<td class="center">
								--
							</td>
							<td class="center">
								<input type="text" style="width:50px" value="{if isset($cookie_productsListFilter_id_product)}{$cookie_productsListFilter_id_product|escape:'htmlall':'UTF-8'}{/if}" name="productsListFilter_id_product" class="filter">
							</td>
							<td class="center">
								--
							</td>
							<td class="center">
								<input type="text" style="width:95%" value="{if isset($cookie_productsListFilter_name)}{$cookie_productsListFilter_name|escape:'htmlall':'UTF-8'}{/if}" name="productsListFilter_name" class="filter">
							</td>
							<td class="center">
								<input type="text" style="width:95%" value="{if isset($cookie_productsListFilter_reference)}{$cookie_productsListFilter_reference|escape:'htmlall':'UTF-8'}{/if}" name="productsListFilter_reference" class="filter">
							</td>
							<td class="center">
								<input type="text" style="width:95%" value="{if isset($cookie_productsListFilter_category)}{$cookie_productsListFilter_category|escape:'htmlall':'UTF-8'}{/if}" name="productsListFilter_category" class="filter">
							</td>
							<td class="center">
								<input type="text" style="width:95%" value="{if isset($cookie_productsListFilter_price)}{$cookie_productsListFilter_price|escape:'htmlall':'UTF-8'}{/if}" name="productsListFilter_price" class="filter">
							</td>
							<td class="center">
								<input type="text" style="width:95%" value="{if isset($cookie_productsListFilter_final_price)}{$cookie_productsListFilter_final_price|escape:'htmlall':'UTF-8'}{/if}" name="productsListFilter_final_price" class="filter">
							</td>
							<td class="center">
								<input type="text" style="width:95%" value="{if isset($cookie_productsListFilter_quantity)}{$cookie_productsListFilter_quantity|escape:'htmlall':'UTF-8'}{/if}" name="productsListFilter_quantity" class="filter">
							</td>
							
							<td class="center">
								<select name="productsListFilter_active" onchange="$('#submitFilterButtonproductsList').focus();$('#submitFilterButtonproductsList').click();">
									<option value="">--</option>
									<option {if isset($cookie_productsListFilter_active) && $cookie_productsListFilter_active}selected="selected" {/if}value="1">{l s='Yes' mod='textmaster'}</option>
									<option {if isset($cookie_productsListFilter_active) && !$cookie_productsListFilter_active}selected="selected" {/if}value="0">{l s='No' mod='textmaster'}</option>
								</select>
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
										{$products[ii].reference|escape:'htmlall':'UTF-8'}
									</td>
									<td class="center">
										{$products[ii].category|escape:'htmlall':'UTF-8'}
									</td>
									<td class="center">
										{$products[ii].price|escape:'htmlall':'UTF-8'}
									</td>
									<td class="center">
										{$products[ii].final_price|escape:'htmlall':'UTF-8'}
									</td>
									<td class="center">
										{$products[ii].quantity|escape:'htmlall':'UTF-8'}
									</td>
									<td class="center">
										{if $products[ii].active}
											<img alt="Enabled" src="../img/admin/enabled.gif">
										{else}
											<img alt="Enabled" src="../img/admin/disabled.gif">
										{/if}
									</td>
								</tr>
							{/section}
						{else}
								<tr>
									<td colspan="10" class="center">
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