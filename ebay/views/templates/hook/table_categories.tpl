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

{if $categoryList === false || sizeof($categoryList) === 0}
	<tr>
		<td colspan="3" class="center">{$noCatFound}</td>
	</tr>
{else}
	{foreach from=$categoryList key=k  item=c}
		<tr{if $k % 2 !== 0} class="alt_row"{/if} id="category-{$c.id_category}">
			<td><a id="show-products-switch-{$c.id_category}" showing="0" class="show-products" href="javascript:showProducts({$c.id_category})">&#9654;</a> {$c.name} ({if isset($getCatInStock[$c.id_category])}
				{$getCatInStock[$c.id_category]}
				{/if})
			</td>
			<td id="categoryPath{$c.id_category}">
				{if isset($categoryConfigList[$c.id_category]) && isset($categoryConfigList[$c.id_category].var)}
					{$categoryConfigList[$c.id_category].var}
				{else}
					<select name="category[{$c.id_category}]" id="categoryLevel1-{$c.id_category}" rel="{$c.id_category}" style="font-size: 12px; width: 160px;" OnChange="changeCategoryMatch(1, {$c.id_category});">
						<option value="0">{$noCatSelected}</option>
						{foreach from=$eBayCategoryList item=ec}
							<option value="{$ec.id_ebay_category}">{$ec.name}{if $ec.is_multi_sku == 1} *{/if}</option>
						{/foreach}
					</select>
				{/if}
			</td>
			<td>
				<select name="percent[{$c.id_category}][sign]">
					<option{if isset($categoryConfigList[$c.id_category].percent.sign) && $categoryConfigList[$c.id_category].percent.sign == ''} selected{/if}>+</option>
					<option{if isset($categoryConfigList[$c.id_category].percent.sign) && $categoryConfigList[$c.id_category].percent.sign == '-'} selected{/if}>-</option>
				</select>
				<input type="text" size="3" maxlength="3" name="percent[{$c.id_category}][value]" id="percent{$c.id_category}" rel="{$c.id_category}" style="font-size: 12px;" value="{if isset($categoryConfigList[$c.id_category]) && isset($categoryConfigList[$c.id_category].var)}{$categoryConfigList[$c.id_category].percent.value}{/if}" />
				<select name="percent[{$c.id_category}][type]">
					<option value="currency"{if isset($categoryConfigList[$c.id_category].percent.type) && $categoryConfigList[$c.id_category].percent.type == ''} selected{/if}>{$currencySign|html_entity_decode:2:"UTF-8"}</option>
					<option value="percent"{if isset($categoryConfigList[$c.id_category].percent.type) && $categoryConfigList[$c.id_category].percent.type == '%'} selected{/if}>%</option>
				</select>
			</td>
			<td colspan="2" class="show-products" style="text-align:center"><a  id="show-products-switch-string{$c.id_category}" href="javascript:showProducts({$c.id_category})">{l s='Unselect products that you do NOT want to list on eBay' mod='ebay'}</a></td>			
		</tr>
	{/foreach}
{/if}