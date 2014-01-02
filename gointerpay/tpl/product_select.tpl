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

{if $category_products}
	<ul>
		{foreach from=$category_products item=products key=category name=main}
			<fieldset>
				<li style="background-color: #FFF; height: auto;">
					<label for="category_{$smarty.foreach.main.index}" style="text-align: left;">
						<input name="category_{$smarty.foreach.main.index}" type="checkbox" class="category_products" onclick="$(this).closest('fieldset').find(':checkbox').prop('checked', this.checked);" style="margin-right: 5px;">
						{$category}
					</label>
					<ul style="margin: 0 10px;">
						{foreach from=$products item=product name=products_list}
							<li style="padding: 2px 0; height: 20px;{if $smarty.foreach.products_list.last} border-bottom: 1px solid #CCCCCC;{/if}">
								<label for="prduct_{$smarty.foreach.main.index}_{$product['id_product']}" style="float: left; font-weight: bold;  text-align: left;  width: 450px; margin-right: 5px;">
									<input type="checkbox" name="id_product[]" value="{$product['value']}" id="prduct_{$smarty.foreach.main.index}_{$product['id_product']}" style="margin:3px 5px;" {if $product['checked']}checked="checked"{/if} />
									{$product['name']}
								</label>
							</li>
						{/foreach}	
					</ul>

				</li>
			</fieldset>
		{/foreach}	
	</ul>
{/if}	