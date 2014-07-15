{*
 * 2007-2014 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2014 PrestaShop SA
 *  @version  Release: 0.4.4
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *}
<img src="{$logo|escape:'htmlall':'UTF-8'}" alt="{l s='Seur' mod='seur'}" border="0" />
<div class="table_block">
	<table class="detail_step_by_step std">
		<thead>
			<tr>
				<th class="first_item">{l s='Reference' mod='seur'}</th>
				<th class="item">{l s='Expedition' mod='seur'}</th>
				<th class="item">{l s='Estate' mod='seur'}</th>
				<th class="last_item">{l s='Date' mod='seur'}</th>
			</tr>
		</thead>
		<tbody>
			<tr class="first_item item">
				<td>{$reference|escape:'htmlall':'UTF-8'}</td>
				<td>{$delivery|escape:'htmlall':'UTF-8'}</td>
				<td>{$seur_order_state|escape:'htmlall':'UTF-8'}</td>
				<td>{$date|escape:'htmlall':'UTF-8'}</td>
			</tr>
		</tbody>
	</table>
</div>