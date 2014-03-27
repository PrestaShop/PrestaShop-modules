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

<div class="panel-heading">
	{l s='Products' mod='giveit'}
	<span class="badge">{$list_total|escape:'htmlall':'UTF-8'}</span>
	
	<span class="panel-heading-action">
		<a id="desc-product-refresh" class="list-toolbar-btn" href="javascript:location.reload();">
			<span class="label-tooltip" data-html="true" data-original-title="{l s='Refresh list' mod='giveit'}" data-toggle="tooltip" title="">
				<i class="process-icon-refresh"></i>
			</span>
		</a>
	</span>
</div>

<div class="table-responsive clearfix">
	<table id="product" class="table tableDnD product" name="list_table">
		<thead>
			<tr class="nodrag nodrop">
				<th class="fixed-width-xs center">
					<span class="title_box{if isset($cookie_order_by) && isset($cookie_order_way) && $cookie_order_by == 'id_product'} active{/if}">
						{l s='ID' mod='giveit'}
						<a onclick="orderTable('id_product', 'desc')"{if isset($cookie_order_by) && isset($cookie_order_way) && $cookie_order_by == 'id_product' && $cookie_order_way == 'desc'} class="active"{/if}>
							<i class="icon-caret-down"></i>
						</a>
						<a onclick="orderTable('id_product', 'asc')"{if isset($cookie_order_by) && isset($cookie_order_way) && $cookie_order_by == 'id_product' && $cookie_order_way == 'asc'} class="active"{/if}>
							<i class="icon-caret-up"></i>
						</a>
					</span>
				</th>
				<th class="center">
					<span class="title_box ">
						{l s='Photo' mod='giveit'}
					</span>
				</th>
				<th class="center">
					<span class="title_box{if isset($cookie_order_by) && isset($cookie_order_way) && $cookie_order_by == 'name'} active{/if}">
						{l s='Name' mod='giveit'}
						<a onclick="orderTable('name', 'desc')"{if isset($cookie_order_by) && isset($cookie_order_way) && $cookie_order_by == 'name' && $cookie_order_way == 'desc'} class="active"{/if}>
							<i class="icon-caret-down"></i>
						</a>
						<a onclick="orderTable('name', 'asc')"{if isset($cookie_order_by) && isset($cookie_order_way) && $cookie_order_by == 'name' && $cookie_order_way == 'asc'} class="active"{/if}>
							<i class="icon-caret-up"></i>
						</a>
					</span>
				</th>
				<th class="center">
					<span class="title_box ">
						{l s='Product combinations' mod='giveit'}
					</span>
				</th>
				<th class="center">
					<span class="title_box ">
						{l s='Give.it settings' mod='giveit'}
					</span>
				</th>
				<th class="center">
					
				</th>
			</tr>
			<tr class="nodrag nodrop filter row_hover">
				<th class="center">
					<input class="filter" type="text" value="{if isset($cookie_productsListFilter_id_product)}{$cookie_productsListFilter_id_product|escape:'htmlall':'UTF-8'}{/if}" name="p.id_product">
				</th>
				<th class="text-center">
					--
				</th>
				<th class="center">
					<input class="filter" type="text" value="{if isset($cookie_productsListFilter_name)}{$cookie_productsListFilter_name|escape:'htmlall':'UTF-8'}{/if}" name="pl.name">
				</th>
				<th class="text-center">
					--
				</th>
				<th class="text-center">
					--
				</th>
				<th class="actions">
					<span class="pull-right">
						<button id="submitFilterButtonproductsList" class="btn btn-default" data-list-id="product" name="submitFilter" type="submit">
							<i class="icon-search"></i>
							{l s='Search' mod='giveit'}
						</button>
						{if isset($cookie_productsListFilter_id_product) || isset($cookie_productsListFilter_name)}
							<button class="btn btn-warning" name="submitResetproductsList" type="submit">
								<i class="icon-eraser"></i>
								{l s='Reset' mod='giveit'}
							</button>
						{/if}
					</span>
				</th>
			</tr>
		</thead>
		<tbody>
			{if !empty($products)}
				{section name=ii loop=$products}
					<tr{if $smarty.section.ii.index % 2 == 0} class="odd"{/if}>
						<td class="pointer fixed-width-xs center">
							{$products[ii].id_product|escape:'htmlall':'UTF-8'}
							<input type="hidden" value="{$products[ii].id_product|escape:'htmlall':'UTF-8'}" />
						</td>
						<td class="center">
							<img src="{$products[ii].image|escape:'htmlall':'UTF-8'}" alt="image" />
						</td>
						<td>
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
						<td class="text-right">
							<div class="btn-group-action">
								<button class="btn btn-default update_product_setting" type="button">
									<i class="icon-rotate-right"></i>
									{l s='Update' mod='giveit'}
								</button>
							</div>
						</td>
					</tr>
				{/section}
			{else}
				<tr>
					<td class="text-center text-muted" colspan="6">
						<i class="icon-warning-sign"></i>
						{l s='No items found' mod='giveit'}
					</td>
				</tr>
			{/if}
		</tbody>
	</table>
	
	<div class="row">
		<div class="col-lg-8">
			
		</div>
		{if $list_total > $pagination|min}
			<div class="col-lg-4">
				<span class="pagination">
					{l s='Display' mod='giveit'}: 
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
						{$selected_pagination|escape:'htmlall':'UTF-8'}
						<i class="icon-caret-down"></i>
					</button>
					<ul class="dropdown-menu">
					{foreach $pagination AS $value}
						<li>
							<a href="javascript:void(0);" class="pagination-items-page" data-items="{$value|intval}">{$value|escape:'htmlall':'UTF-8'}</a>
						</li>
					{/foreach}
					</ul>
					/ {$list_total|intval} {l s='result(s)' mod='giveit'}
					<input type="hidden" id="pagination-items-page" name="product_pagination" value="{$selected_pagination|intval}" />
				</span>
				<ul class="pagination pull-right">
					<li {if $page <= 1}class="disabled"{/if}>
						<a href="javascript:void(0);" class="pagination-link" data-page="1">
							<i class="icon-double-angle-left"></i>
						</a>
					</li>
					<li {if $page <= 1}class="disabled"{/if}>
						<a href="javascript:void(0);" class="pagination-link" data-page="{$page|intval - 1}">
							<i class="icon-angle-left"></i>
						</a>
					</li>
					{assign p 0}
					{while $p++ < $total_pages|intval}
						{if $p < $page-2}
							<li class="disabled">
								<a href="javascript:void(0);">&hellip;</a>
							</li>
							{assign p $page-3}
						{else if $p > $page+2}
							<li class="disabled">
								<a href="javascript:void(0);">&hellip;</a>
							</li>
							{assign p $total_pages|intval}
						{else}
							<li {if $p == $page}class="active"{/if}>
								<a href="javascript:void(0);" class="pagination-link" data-page="{$p|intval}">{$p|intval}</a>
							</li>
						{/if}
					{/while}
					<li {if $page >= $total_pages}class="disabled"{/if}>
						<a href="javascript:void(0);" class="pagination-link" data-page="{$page|intval + 1}">
							<i class="icon-angle-right"></i>
						</a>
					</li>
					<li {if $page >= $total_pages}class="disabled"{/if}>
						<a href="javascript:void(0);" class="pagination-link" data-page="{$total_pages|intval}">
							<i class="icon-double-angle-right"></i>
						</a>
					</li>
				</ul>
			</div>
		{/if}
	</div>
</div>