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

<div id="export_product_list" style="display: none;">
	<table>
		<tr>
			<td class="col-left"><label>{$bullet_common_field}{l s='Enable for export' mod='gointerpay'}<br />{l s='by associated category' mod='gointerpay'}</label></td>
			<td style="padding-bottom:5px;">
				<ul class="listForm">
					{foreach from=$interpay_export item=export_product key=category name=list_index}
						<li>
							<p><input id="gointerpay_export_product{$smarty.foreach.list_index.index}" name="gointerpay_export[]" type="checkbox" value="{$export_product['value']}" {if $export_product['checked']}checked="checked"{/if} /> {$category|escape:htmlall:'UTF-8'}</p>
						</li>
					{/foreach}	
				</ul>
			</td>
		</tr>
	</table>
</div>

<script type="text/javascript">
	{literal}
	$(document).ready(function()
		{
			$('#export_product_list').appendTo($('input[name="upc"]').parents('table'));
			$('#export_product_list').show();
		});
	{/literal}
</script>