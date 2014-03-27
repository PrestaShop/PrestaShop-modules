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

<script type="text/javascript">id_language = Number({$id_lang_default|escape:'htmlall':'UTF-8'})</script>

<div class="Bloc">
		{l s='Note, that if default currency will be changed, all prices must be updated according to the new default currency.' mod='giveit'}
</div>
<br>
<form method="post" action="{$page_url|escape:'htmlall':'UTF-8'}">
		<fieldset id="shipping_prices">
				<legend>
						<img src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}giveit/img/settings.png" alt="{l s='Settings |' mod='giveit'}" />
						{l s='Shipping Prices' mod='giveit'}
				</legend>

				<label>
						{l s='Price' mod='giveit'}
				</label>
				<div class="margin-form">
						<input type="text" name="shipping_price" id="shipping_price" value="{if isset($smarty.post.shipping_price)}{$smarty.post.shipping_price|escape:'htmlall':'UTF-8'}{elseif isset($shipping)}{$shipping->price|escape:'htmlall':'UTF-8'}{/if}" />  {$currency->sign|escape:'htmlall':'UTF-8'}
						<p class="preference_description">
								{l s='Enter shipping price' mod='giveit'}
						</p>
				</div>
		<label>
						{l s='Free above' mod='giveit'}
				</label>
				<div class="margin-form">
						<input type="text" name="free_above" id="free_above" value="{if isset($smarty.post.free_above)}{$smarty.post.free_above|escape:'htmlall':'UTF-8'}{elseif isset($shipping)}{$shipping->free_above|escape:'htmlall':'UTF-8'}{/if}" />  {$currency->sign|escape:'htmlall':'UTF-8'}
						<p class="preference_description">
								{l s='Free above *note: 0 means no free above rate' mod='giveit'}
						</p>
				</div>

<label>
						{l s='Tax percentage' mod='giveit'}
				</label>
				<div class="margin-form">
						<input type="text" name="tax_percent" id="tax_percent" value="{if isset($smarty.post.tax_percent)}{$smarty.post.tax_percent|escape:'htmlall':'UTF-8'}{elseif isset($shipping)}{$shipping->tax_percent|escape:'htmlall':'UTF-8'}{/if}" />
						<p class="preference_description">
								{l s='only if you charge taxes' mod='giveit'}
						</p>
				</div>



				<label>
						{l s='Shipping method' mod='giveit'}
				</label>
				<div class="margin-form">
						{foreach from=$languages item=language}
								{assign var="title_lang" value='title_'|cat:$language['id_lang']}
								<div id="{$title_lang|escape:'htmlall':'UTF-8'}" style="display: {if $language.id_lang == $id_lang_default}block{else}none{/if};float: left;">
										<input type="text"
													name="{$title_lang|escape:'htmlall':'UTF-8'}"
													id="title_{$language.id_lang|escape:'htmlall':'UTF-8'}"
													value="{if isset($smarty.post.$title_lang)}{$smarty.post.$title_lang|escape:'htmlall':'UTF-8'}{elseif isset($shipping)}{$shipping->title[$language.id_lang]|escape:'htmlall':'UTF-8'}{/if}" />
								</div>
						{/foreach}

						<div class="clear"></div>
						<p class="preference_description">
								{l s='NOTE: always provide at least one shipping method for your default language' mod='giveit'} {l s='(' mod='giveit'}{$default_language_notice|escape:'htmlall':'UTF-8'}{l s=')' mod='giveit'}
						</p>
				</div>

				<label>
						{l s='Region' mod='giveit'}
				</label>
				<div class="margin-form">
		<select name="iso_code" id="iso_code">
																{foreach from=$countries item=country}
																				<option value="{$country.iso_code|escape:'htmlall':'UTF-8'}" {if isset($shipping)}{if $shipping->iso_code == $country.iso_code}selected="selected"{/if}{/if}>{$country.name|escape:'htmlall':'UTF-8'}</option>
																{/foreach}
		</select>
	</div>
	<div class="margin-form">
						<input type="submit" class="button" name="saveShippingData" value="{l s='Add or update shipping method' mod='giveit'}" />
				</div>

				<div class="separation"></div>

				<div id="shipping_methods_table_container">
						<table name="list_table" class="table_grid">
								<tbody>
										<tr>
												<td style="border:none;">
														<table cellspacing="0" cellpadding="0" style="width: 100%; margin-bottom:10px;" class="table shipping_prices">
																<colgroup>
																		<col width="10px">
																		<col>
																		<col width="125px">
																</colgroup>
																<thead>
																		<tr style="height: 40px" class="nodrag nodrop">
																				<th class="center"></th>
																				<th><span class="title_box">{l s='Shipping method' mod='giveit'}</span></th>
																				<th><span class="title_box">{l s='Region' mod='giveit'}</span></th>
																				<th width="200"><span class="title_box">{l s='Price' mod='giveit'}</span></th>
																				<th width="200"><span class="title_box">{l s='Free above' mod='giveit'}</span></th>
																				<th width="200"><span class="title_box">{l s='Tax percentage' mod='giveit'}</span></th>
																				<th class="center" width="45"><span class="title_box">{l s='Currency' mod='giveit'}</span></th>
																				<th class="center"><span class="title_box">{l s='Actions' mod='giveit'}</span></th>
																		</tr>
																</thead>
																<tbody>
																		{if !empty($shipping_rules)}
																				{foreach from=$shipping_rules item=rule}
																						<tr class="row_hover">
																								<td class="center"></td>
																								<td>{$rule.title|escape:'htmlall':'UTF-8'}</td>
																								<td>

																		{foreach from=$countries item=country}
																						{if $rule.iso_code == $country.iso_code}{$country.name|escape:'htmlall':'UTF-8'}{/if}
						{/foreach}
						</td>
																								<td width="200">{$rule.price|escape:'htmlall':'UTF-8'}</td>
																								<td width="200">{$rule.free_above|escape:'htmlall':'UTF-8'}</td>
																								<td width="200">{$rule.tax_percent|escape:'htmlall':'UTF-8'}</td>
																								<td width="45" class="center">{$rule.currency_sign|escape:'htmlall':'UTF-8'}</td>
																								<td class="center">
																										<a title="{l s='Edit' mod='giveit'}" class="edit" href="{$page_url|escape:'htmlall':'UTF-8'}&edit_rule={$rule.id_giveit_shipping|escape:'htmlall':'UTF-8'}">
																												<img alt="{l s='Edit' mod='giveit'}" src="../img/admin/edit.gif">
																										</a>
																										<a title="{l s='Delete' mod='giveit'}" onclick="if (!confirm('{l s='Delete selected item?' mod='giveit'}')){ return false; }" class="delete" href="{$page_url|escape:'htmlall':'UTF-8'}&delete_rule={$rule.id_giveit_shipping|escape:'htmlall':'UTF-8'}">
																												<img alt="{l s='Delete' mod='giveit'}" src="../img/admin/delete.gif">
																										</a>
																								</td>
																						</tr>
																				{/foreach}
																		{else}
																				<tr class="row_hover">
																						<td colspan="10" class="center"><i>{l s='Empty' mod='giveit'}</i></td>
																				</tr>
																		{/if}
																</tbody>
														</table>
												</td>
										</tr>
								</tbody>
						</table>
				</div>
		</fieldset>
</form>
