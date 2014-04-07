{*
* 2007-2013 PrestaShop
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
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<fieldset>
	Here you can see which stores have been configured.
	
	<table class="table tableDnD" cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<th>Shop</th>
				<th>Products Configured</th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$shops item='shop'}
			<tr>
				<td><a href="{$url_base}{$shop.id_shop}">{$shop.name}</a></td>
				<td>{{$shop.nb_products_synchronized}}</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
</fieldset>