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
{if $posEnabled}

<script>
	$(document).ready(function(){
		initSeurCarriers();
	});
</script>

<input type="hidden" name="pos_selected" id="pos_selected" value="false" />
<input type="hidden" name="id_cart_seur" id="id_cart_seur" value="{$cookie->id_cart|intval}" />
<input type="hidden" name="id_seur_pos" id="id_seur_pos" value="{$id_seur_pos|intval}" />
<input type="hidden" name="ps_version" id="ps_version" value="{$ps_version|escape:'htmlall':'UTF-8'}" />
<input type="hidden" name="id_seur_RESTO" id="id_seur_RESTO" value="{$seur_resto|escape:'htmlall':'UTF-8'}" />
{if isset($id_address)}
	<input type="hidden" name="id_address_delivery" id="id_address_delivery" value="{$id_address|intval}" />
{/if}

<div id="seurPudoContainer">
	<div id="noSelectedPointInfo" style="display:none">{l s='Select a pick up point before proceeding with your order.' mod='seur'}</div>
	<div id="collectionPointInfo">
		<h2>{l s='Pick up point selected' mod='seur'}</h2>
		<ul>
			<li>
				<strong><label for="post_codeCompany">{l s='Company' mod='seur'}:</label></strong> <span id="post_codeCompany">{l s='Company' mod='seur'}</span>
			</li>
			<li>
				<strong><label for="post_codeAddress">{l s='Address' mod='seur'}:</label></strong> <span id="post_codeAddress">{l s='Address' mod='seur'}</span>
			</li>
			<li>
				<strong><label for="post_codeCity">{l s='City' mod='seur'}:</label></strong> <span id="post_codeCity">{l s='City' mod='seur'}</span>
			</li>
			<li>
				<strong><label for="post_codePostalCode">{l s='Postal Code' mod='seur'}:</label></strong> <span id="post_codePostalCode">{l s='Postal Code' mod='seur'}</span>
			</li>
			<li>
				<strong><label for="post_codeTimetable">{l s='Timetable' mod='seur'}:</label></strong> <span id="post_codeTimetable">{l s='Timetable' mod='seur'}</span>
			</li>
			<li>
				<strong><label for="post_codePhone">{l s='Phone' mod='seur'}:</label></strong> <span id="post_codePhone">{l s='Phone' mod='seur'}</span>
			</li>
		</ul>
	</div>
</div>
{/if}
