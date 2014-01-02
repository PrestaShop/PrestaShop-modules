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

<div class="bp-wrapper">
	<a href="http://www.bluepay.com/prestashop-partner-page" target="_blank" class="bp-logo"><img src="{$module_dir|escape:'htmlall':'UTF-8'}bluepay/img/bluepay.png" alt="BluePay" border="0" /></a>
	<p class="bp-intro">{l s='When you partner with BluePay, you receive high levels of service and security from a credit card and E-Check processing company that knows the payment industry inside and out.' mod='bluepay'}<br /><br />
	<a href="http://www.bluepay.com/prestashop-partner-page" target="_blank" class="bp-link">{l s='Open An Account Now' mod='bluepay'}</a></p>
	<div class="bp-content">
		<h3>{l s='Enjoy the benefits of BluePay Merchant Credit Card and E-Check Processing:' mod='bluepay'}</h3>
		<table>
                        <tr>
				<th>{l s='FREE Payment Gateway' mod='bluepay'}</th>
				<th>{l s='All-in-One Credit Card and E-Check Processing Company' mod='bluepay'}</th>
			</tr>
			<tr>
				<td>Make quick, secure, efficient payments</td>
				<td>{l s='Eliminate 3rd party gateway hassles' mod='bluepay'}</td>
			</tr>
			<tr>
                                <th>{l s='Security and Support you can count on' mod='bluepay'}</th>
                                <th>{l s='Robust Comprehensive Reporting' mod='bluepay'}</th>
                        </tr>
			<tr>
				<td class="lower">The Only Credit Card and E-Check Payment module partnered with <br>PrestaShop that supports a Secure iframe payment form <br>within your checkout page to reduce your PCI scope</td>
				<td class="lower">{l s='FREE 24/7 online transaction reporting tool tracking transactions<br> from point of inception to settlement into the bank account of your choice' mod='bluepay'}</td>
			</tr>
                </table>
		<br /><br />
			<div class="title-center">
				<h3>{l s='Accept Payments in the U.S. and Canada' mod='bluepay'}</h3>
			</div>
			<div class="bluepay-logos">
				<img src="{$module_dir|escape:'htmlall':'UTF-8'}bluepay/img/visa.png" alt="{l s='Visa Logo' mod='bluepay'}" style="vertical-align: middle;" />
				<img src="{$module_dir|escape:'htmlall':'UTF-8'}bluepay/img/mastercard.png" alt="{l s='MasterCard Logo' mod='bluepay'}" style="vertical-align: middle;" />
				<img src="{$module_dir|escape:'htmlall':'UTF-8'}bluepay/img/discover-network.png" alt="{l s='Discover Logo' mod='bluepay'}" style="vertical-align: middle;" />
				<img src="{$module_dir|escape:'htmlall':'UTF-8'}bluepay/img/american-express.png" alt="{l s='Amex Logo' mod='bluepay'}" style="vertical-align: middle;" />
				<img src="{$module_dir|escape:'htmlall':'UTF-8'}bluepay/img/echeck.png" alt="{l s='E-check Logo' mod='bluepay'}" style="vertical-align: middle;" />
				<img src="{$module_dir|escape:'htmlall':'UTF-8'}bluepay/img/diners-club.png" alt="{l s='Diners Logo' mod='bluepay'}" style="vertical-align: middle;" />
				<img src="{$module_dir|escape:'htmlall':'UTF-8'}bluepay/img/jcb.png" alt="{l s='JCB Logo' mod='bluepay'}" style="vertical-align: middle;" />
				<img src="{$module_dir|escape:'htmlall':'UTF-8'}bluepay/img/union-pay.png" alt="{l s='UnionPay Logo' mod='bluepay'}" style="vertical-align: middle;" />
				<img src="{$module_dir|escape:'htmlall':'UTF-8'}bluepay/img/bc-card.png" alt="{l s='BC Card Logo' mod='bluepay'}" style="vertical-align: middle;" />
				<img src="{$module_dir|escape:'htmlall':'UTF-8'}bluepay/img/dina-card.png" alt="{l s='DinaCard Logo' mod='bluepay'}" style="vertical-align: middle;" />
			</div>
		</div>
	</div>

	{if $show_toolbar}
		{include file="toolbar.tpl" toolbar_btn=$toolbar_btn toolbar_scroll=$toolbar_scroll title=$title}
		<div class="leadin">{block name="leadin"}{/block}</div>
	{/if}
</div>

{if isset($fields.title)}<h2>{$fields.title|escape:'htmlall':'UTF-8'}</h2>{/if}
{block name="defaultForm"}
<form id="{$table|escape:'htmlall':'UTF-8'}_form" class="defaultForm {$name_controller|escape:'htmlall':'UTF-8'}" action="{$current|escape:'htmlall':'UTF-8'}&{if !empty($submit_action)}{$submit_action|escape:'htmlall':'UTF-8'}=1{/if}&token={$token|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data" {if isset($style)}style="{$style|escape:'htmlall':'UTF-8'}"{/if}>
	{if $form_id}
		<input type="hidden" name="{$identifier|escape:'htmlall':'UTF-8'}" id="{$identifier|escape:'htmlall':'UTF-8'}" value="{$form_id|escape:'htmlall':'UTF-8'}" />
	{/if}
	{foreach $fields as $f => $fieldset}
		<fieldset id="fieldset_{$f|escape:'htmlall':'UTF-8'}">
			{foreach $fieldset.form as $key => $field}
				{if $key == 'legend'}
					<legend>
						{if isset($field.image)}<img src="{$field.image|escape:'htmlall':'UTF-8'}" alt="{$field.title|escape:'htmlall':'UTF-8'}" />{/if}
						{$field.title|escape:'htmlall':'UTF-8'}
					</legend>
				{elseif $key == 'description' && $field}
					<p class="description">{$field|escape:'htmlall':'UTF-8'}</p>
				{elseif $key == 'input'}
					{foreach $field as $input}
						{if $input.type == 'hidden'}
							<input type="hidden" name="{$input.name|escape:'htmlall':'UTF-8'}" id="{$input.name|escape:'htmlall':'UTF-8'}" value="{$fields_value[$input.name]|escape:'htmlall':'UTF-8'}" />
						{else}
							{if $input.name == 'id_state'}
								<div id="contains_states" {if !$contains_states}style="display:none;"{/if}>
							{/if}
							{block name="label"}
								{if isset($input.label)}<label>{$input.label|escape:'htmlall':'UTF-8'} </label>{/if}
							{/block}
							{block name="field"}
								<div class="margin-form">
								{block name="input"}
								{if $input.type == 'text' || $input.type == 'tags'}
									{if isset($input.lang) AND $input.lang}
										<div class="translatable">
											{foreach $languages as $language}
												cape:'htmlall':'UTF-8'div class="lang_{$language.id_lang|escape:'htmlall':'UTF-8'}" style="display:{if $language.id_lang == $defaultFormLanguage}block{else}none{/if}; float: left;">
													{if $input.type == 'tags'}
														{literal}
														<script type="text/javascript">
															$().ready(function () {
																var input_id = '{/literal}{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}{/if}{literal}';
																$('#'+input_id).tagify({addTagPrompt: '{/literal}{l s='Add tag' js=1}{literal}'});
																$({/literal}'#{$table|escape:'htmlall':'UTF-8'}{literal}_form').submit( function() {
																	$(this).find('#'+input_id).val($('#'+input_id).tagify('serialize'));
																});
															});
														</script>
														{/literal}
													{/if}
													{assign var='value_text' value=$fields_value[$input.name][$language.id_lang]}
													<input type="text"
															name="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}"
															id="{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}{/if}"
															value="{if isset($input.string_format) && $input.string_format}{$value_text|string_format:$input.string_format|escape:'htmlall':'UTF-8'}{else}{$value_text|escape:'htmlall':'UTF-8'}{/if}"
															class="{if $input.type == 'tags'}tagify {/if}{if isset($input.class)}{$input.class|escape:'htmlall':'UTF-8'}{/if}"
															{if isset($input.size)}size="{$input.size|escape:'htmlall':'UTF-8'}"{/if}
															{if isset($input.maxlength)}maxlength="{$input.maxlength|escape:'htmlall':'UTF-8'}"{/if}
															{if isset($input.readonly) && $input.readonly}readonly="readonly"{/if}
															{if isset($input.disabled) && $input.disabled}disabled="disabled"{/if}
															{if isset($input.autocomplete) && !$input.autocomplete}autocomplete="off"{/if} />
													{if !empty($input.hint)}<span class="hint" name="help_box">{$input.hint|escape:'htmlall':'UTF-8'}<span class="hint-pointer">&nbsp;</span></span>{/if}
												</div>
											{/foreach}
										</div>
									{else}
										{if $input.type == 'tags'}
											{literal}
											<script type="text/javascript">
												$().ready(function () {
													var input_id = '{/literal}{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}{literal}';
													$('#'+input_id).tagify();
													$('#'+input_id).tagify({addTagPrompt: '{/literal}{l s='Add tag' mod='bluepay'}{literal}'});
													$({/literal}'#{$table|escape:'htmlall':'UTF-8'}{literal}_form').submit( function() {
														$(this).find('#'+input_id).val($('#'+input_id).tagify('serialize'));
													});
												});
											</script>
											{/literal}
										{/if}
										{assign var='value_text' value=$fields_value[$input.name]}
										<input type="text"
												name="{$input.name|escape:'htmlall':'UTF-8'}"
												id="{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}"
												value="{if isset($input.string_format) && $input.string_format}{$value_text|string_format:$input.string_format|escape:'htmlall':'UTF-8'}{else}{$value_text|escape:'htmlall':'UTF-8'}{/if}"
												class="{if $input.type == 'tags'}tagify {/if}{if isset($input.class)}{$input.class|escape:'htmlall':'UTF-8'}{/if}"
												{if isset($input.size)}size="{$input.size|escape:'htmlall':'UTF-8'}"{/if}
												{if isset($input.maxlength)}maxlength="{$input.maxlength|escape:'htmlall':'UTF-8'}"{/if}
												{if isset($input.class)}class="{$input.class|escape:'htmlall':'UTF-8'}"{/if}
												{if isset($input.readonly) && $input.readonly}readonly="readonly"{/if}
												{if isset($input.disabled) && $input.disabled}disabled="disabled"{/if}
												{if isset($input.autocomplete) && !$input.autocomplete}autocomplete="off"{/if} />
										{if isset($input.suffix)}<a href=# class=help><img src="{$module_dir|escape:'htmlall':'UTF-8'}bluepay/img/help.png" alt="help"><div class=test><span><p>{$input.suffix|escape:'htmlall':'UTF-8'}</p></span></div></a>{/if}
										{if !empty($input.hint)}<span class="hint" name="help_box">{$input.hint|escape:'htmlall':'UTF-8'}<span class="hint-pointer">&nbsp;</span></span>{/if}
									{/if}
								{elseif $input.type == 'select'}
									{if isset($input.options.query) && !$input.options.query && isset($input.empty_message)}
										{$input.empty_message|escape:'htmlall':'UTF-8'}
										{$input.required = false|escape:'htmlall':'UTF-8'}
										{$input.desc = null|escape:'htmlall':'UTF-8'}
									{else}
										<select name="{$input.name|escape:'htmlall':'UTF-8'}" class="{if isset($input.class)}{$input.class|escape:'htmlall':'UTF-8'}{/if}"
												id="{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}"
												{if isset($input.multiple)}multiple="multiple" {/if}
												{if isset($input.size)}size="{$input.size|escape:'htmlall':'UTF-8'}"{/if}
												{if isset($input.onchange)}onchange="{$input.onchange|escape:'htmlall':'UTF-8'}"{/if}>
											{if isset($input.options.default)}
												<option value="{$input.options.default.value|escape:'htmlall':'UTF-8'}">{$input.options.default.label|escape:'htmlall':'UTF-8'}</option>
											{/if}
											{if isset($input.options.optiongroup)}
												{foreach $input.options.optiongroup.query AS $optiongroup}
													<optgroup label="{$optiongroup[$input.options.optiongroup.label]|escape:'htmlall':'UTF-8'}">
														{foreach $optiongroup[$input.options.options.query] as $option}
															<option value="{$option[$input.options.options.id]|escape:'htmlall':'UTF-8'}"
																{if isset($input.multiple)}
																	{foreach $fields_value[$input.name] as $field_value}
																		{if $field_value == $option[$input.options.options.id]|escape:'htmlall':'UTF-8'}selected="selected"{/if}
																	{/foreach}
																{else}
																	{if $fields_value[$input.name] == $option[$input.options.options.id]|escape:'htmlall':'UTF-8'}selected="selected"{/if}
																{/if}
															>{$option[$input.options.options.name]|escape:'htmlall':'UTF-8'}</option>
														{/foreach}
													</optgroup>
												{/foreach}
											{else}
												{foreach $input.options.query AS $option}
													{if is_object($option)}
														<option value="{$option->$input.options.id|escape:'htmlall':'UTF-8'}"
															{if isset($input.multiple)}
																{foreach $fields_value[$input.name] as $field_value}
																	{if $field_value == $option->$input.options.id}
																		selected="selected"
																	{/if}
																{/foreach}
															{else}
																{if $fields_value[$input.name] == $option->$input.options.id}
																	selected="selected"
																{/if}
															{/if}
														>{$option->$input.options.name|escape:'htmlall':'UTF-8'}</option>
													{elseif $option == "-"}
														<option value="">--</option>
													{else}
														<option value="{$option[$input.options.id]|escape:'htmlall':'UTF-8'}"
															{if isset($input.multiple)}
																{foreach $fields_value[$input.name] as $field_value}
																	{if $field_value == $option[$input.options.id]}
																		selected="selected"
																	{/if}
																{/foreach}
															{else}
																{if $fields_value[$input.name] == $option[$input.options.id]}
																	selected="selected"
																{/if}
															{/if}
														>{$option[$input.options.name]|escape:'htmlall':'UTF-8'}</option>

													{/if}
												{/foreach}
											{/if}
										</select>
										{if !empty($input.hint)}<span class="hint" name="help_box">{$input.hint|escape:'htmlall':'UTF-8'}<span class="hint-pointer">&nbsp;</span></span>{/if}
									{/if}
								{elseif $input.type == 'radio'}
									{foreach $input.values as $value}
										<input type="radio"	name="{$input.name|escape:'htmlall':'UTF-8'}"id="{$value.id|escape:'htmlall':'UTF-8'}" value="{$value.value|escape:'htmlall':'UTF-8'}"
												{if $fields_value[$input.name] == $value.value}checked="checked"{/if}
												{if isset($input.disabled) && $input.disabled}disabled="disabled"{/if} />
										<label {if isset($input.class)}class="{$input.class|escape:'htmlall':'UTF-8'}"{/if} for="{$value.id|escape:'htmlall':'UTF-8'}">
										 {if isset($input.is_bool) && $input.is_bool == true}
											{if $value.value == 1}
												<img src="../img/admin/enabled.gif" alt="{$value.label|escape:'htmlall':'UTF-8'}" title="{$value.label|escape:'htmlall':'UTF-8'}" />
											{else}
												<img src="../img/admin/disabled.gif" alt="{$value.label|escape:'htmlall':'UTF-8'}" title="{$value.label|escape:'htmlall':'UTF-8'}" />
											{/if}
										 {else}
											{$value.label|escape:'htmlall':'UTF-8'}
										 {/if}
										</label>
										{if isset($input.br) && $input.br}<br />{/if}
										{if isset($value.p) && $value.p}<p>{$value.p|escape:'htmlall':'UTF-8'}</p>{/if}
									{/foreach}
								{elseif $input.type == 'textarea'}
									{if isset($input.lang) AND $input.lang}
										<div class="translatable">
											{foreach $languages as $language}
												<div class="lang_{$language.id_lang|escape:'htmlall':'UTF-8'}" id="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}" style="display:{if $language.id_lang == $defaultFormLanguage}block{else}none{/if}; float: left;">
													<textarea cols="{$input.cols|escape:'htmlall':'UTF-8'}" rows="{$input.rows|escape:'htmlall':'UTF-8'}" name="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}" {if isset($input.autoload_rte) && $input.autoload_rte}class="rte autoload_rte {if isset($input.class)}{$input.class|escape:'htmlall':'UTF-8'}{/if}"{/if} >{$fields_value[$input.name][$language.id_lang]|escape:'htmlall':'UTF-8'}</textarea>
												</div>
											{/foreach}
										</div>
									{else}
										<textarea name="{$input.name|escape:'htmlall':'UTF-8'}" id="{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}" cols="{$input.cols|escape:'htmlall':'UTF-8'}" rows="{$input.rows|escape:'htmlall':'UTF-8'}" {if isset($input.autoload_rte) && $input.autoload_rte}class="rte autoload_rte {if isset($input.class)}{$input.class|escape:'htmlall':'UTF-8'}{/if}"{/if}>{$fields_value[$input.name]|escape:'htmlall':'UTF-8'}</textarea>
									{/if}
								{elseif $input.type == 'checkbox'}
									{foreach $input.values.query as $value}
										{assign var=id_checkbox value=$input.name|cat:'_'|cat:$value[$input.values.id]}
										<input type="checkbox"
											name="{$id_checkbox|escape:'htmlall':'UTF-8'}"
											id="{$id_checkbox|escape:'htmlall':'UTF-8'}"
											class="{if isset($input.class)}{$input.class|escape:'htmlall':'UTF-8'}{/if}"
											{if isset($value.val)}value="{$value.val|escape:'htmlall':'UTF-8'}"{/if}
											{if isset($fields_value[$id_checkbox]) && $fields_value[$id_checkbox]}checked="checked"{/if} />
										<label for="{$id_checkbox|escape:'htmlall':'UTF-8'}" class="t"><strong>{$value[$input.values.name]|escape:'htmlall':'UTF-8'}</strong></label><br />
									{/foreach}
								{elseif $input.type == 'file'}
									{if isset($input.display_image) && $input.display_image}
										{if isset($fields_value.image) && $fields_value.image}
											<div id="image">
												{$fields_value.image|escape:'htmlall':'UTF-8'}
												<p align="center">{l s='File size' mod='bluepay'} {$fields_value.size|escape:'htmlall':'UTF-8'}kb</p>
												<a href="{$current|escape:'htmlall':'UTF-8'}&{$identifier|escape:'htmlall':'UTF-8'}={$form_id|escape:'htmlall':'UTF-8'}&token={$token|escape:'htmlall':'UTF-8'}&deleteImage=1">
													<img src="../img/admin/delete.gif" alt="{l s='Delete' mod='bluepay'}" /> {l s='Delete' mod='bluepay'}
												</a>
											</div><br />
										{/if}
									{/if}
									<input type="file" name="{$input.name|escape:'htmlall':'UTF-8'}" {if isset($input.id)}id="{$input.id|escape:'htmlall':'UTF-8'}"{/if} />
									{if !empty($input.hint)}<span class="hint" name="help_box">{$input.hint|escape:'htmlall':'UTF-8'}<span class="hint-pointer">&nbsp;</span></span>{/if}
								{elseif $input.type == 'password'}
									<input type="password"
											name="{$input.name|escape:'htmlall':'UTF-8'}"
											size="{$input.size|escape:'htmlall':'UTF-8'}"
											class="{if isset($input.class)}{$input.class|escape:'htmlall':'UTF-8'}{/if}"
											value=""
											{if isset($input.autocomplete) && !$input.autocomplete}autocomplete="off"{/if} />
								{elseif $input.type == 'birthday'}
									{foreach $input.options as $key => $select}
										<select name="{$key|escape:'htmlall':'UTF-8'}" class="{if isset($input.class)}{$input.class|escape:'htmlall':'UTF-8'}{/if}">
											<option value="">-</option>
											{if $key == 'months'}
												{*
													This comment is useful to the translator tools /!\ do not remove them
													{l s='January' mod='bluepay'}
													{l s='February' mod='bluepay'}
													{l s='March' mod='bluepay'}
													{l s='April' mod='bluepay'}
													{l s='May' mod='bluepay'}
													{l s='June' mod='bluepay'}
													{l s='July' mod='bluepay'}
													{l s='August' mod='bluepay'}
													{l s='September' mod='bluepay'}
													{l s='October' mod='bluepay'}
													{l s='November' mod='bluepay'}
													{l s='December' mod='bluepay'}
												*}
												{foreach $select as $k => $v}
													<option value="{$k|escape:'htmlall':'UTF-8'}" {if $k == $fields_value[$key]}selected="selected"{/if}>{l s=$v|escape:'htmlall':'UTF-8'}</option>
												{/foreach}
											{else}
												{foreach $select as $v}
													<option value="{$v|escape:'htmlall':'UTF-8'}" {if $v == $fields_value[$key]}selected="selected"{/if}>{$v|escape:'htmlall':'UTF-8'}</option>
												{/foreach}
											{/if}

										</select>
									{/foreach}
								{elseif $input.type == 'group'}
									{assign var=groups value=$input.values|escape:'htmlall':'UTF-8'}
									{include file='helpers/form/form_group.tpl'}
								{elseif $input.type == 'shop'}
									{$input.html|escape:'htmlall':'UTF-8'}
								{elseif $input.type == 'categories'}
									{include file='helpers/form/form_category.tpl' categories=$input.values|escape:'htmlall':'UTF-8'}
								{elseif $input.type == 'categories_select'}
									{$input.category_tree|escape:'htmlall':'UTF-8'}
								{elseif $input.type == 'asso_shop' && isset($asso_shop) && $asso_shop}
										{$asso_shop|escape:'htmlall':'UTF-8'}
								{elseif $input.type == 'color'}
									<input type="color"
										size="{$input.size|escape:'htmlall':'UTF-8'}"
										data-hex="true"
										{if isset($input.class)}class="{$input.class|escape:'htmlall':'UTF-8'}"
										{else}class="color mColorPickerInput"{/if}
										name="{$input.name|escape:'htmlall':'UTF-8'}"
										class="{if isset($input.class)}{$input.class|escape:'htmlall':'UTF-8'}{/if}"
										value="{$fields_value[$input.name]|escape:'htmlall':'UTF-8'}" />
								{elseif $input.type == 'date'}
									<input type="text"
										size="{$input.size|escape:'htmlall':'UTF-8'}"
										data-hex="true"
										{if isset($input.class)}class="{$input.class|escape:'htmlall':'UTF-8'}"
										{else}class="datepicker"{/if}
										name="{$input.name|escape:'htmlall':'UTF-8'}"
										value="{$fields_value[$input.name]|escape:'htmlall':'UTF-8'}" />
								{elseif $input.type == 'free'}
									<br /><b>{$fields_value[$input.name]|escape:'htmlall':'UTF-8'}</b><a href="http://www.prestashop.com/blog/en/guest-blogger-series-prestashop-ssl-installation-troubleshooting/">Click here</a>
								{/if}
								{if isset($input.required) && $input.required && $input.type != 'radio'} <sup>*</sup>{/if}
								{/block}{* end block input *}
								{block name="description"}
									{if isset($input.desc) && !empty($input.desc)}
										<p class="preference_description">
											{if is_array($input.desc)}
												{foreach $input.desc as $p}
													{if is_array($p)}
														<span id="{$p.id|escape:'htmlall':'UTF-8'}">{$p.text|escape:'htmlall':'UTF-8'}</span><br />
													{else}
														{$p|escape:'htmlall':'UTF-8'}<br />
													{/if}
												{/foreach}
											{else}
												{$input.desc|escape:'htmlall':'UTF-8'}
											{/if}
										</p>
									{/if}
								{/block}
								{if isset($input.lang) && isset($languages)}<div class="clear"></div>{/if}
								</div>
								<div class="clear"></div>
							{/block}{* end block field *}
							{if $input.name == 'id_state'}
								</div>
							{/if}
						{/if}
					{/foreach}
					{hook h='displayAdminForm'}
					{if isset($name_controller)}
						{capture name=hookName assign=hookName}display{$name_controller|ucfirst|escape:'htmlall':'UTF-8'}Form{/capture}
						{hook h=$hookName}
					{elseif isset($smarty.get.controller)}
						{capture name=hookName assign=hookName}display{$smarty.get.controller|ucfirst|htmlentities|escape:'htmlall':'UTF-8'}Form{/capture}
						{hook h=$hookName}
					{/if}
				{elseif $key == 'submit'}
					<div class="margin-form">
						<input type="submit"
							id="{if isset($field.id)}{$field.id|escape:'htmlall':'UTF-8'}{else}{$table|escape:'htmlall':'UTF-8'}_form_submit_btn{/if}"
							value="{$field.title|escape:'htmlall':'UTF-8'}"
							name="{if isset($field.name)}{$field.name|escape:'htmlall':'UTF-8'}{else}{$submit_action|escape:'htmlall':'UTF-8'}{/if}{if isset($field.stay) && $field.stay}AndStay{/if}"
							{if isset($field.class)}class="{$field.class|escape:'htmlall':'UTF-8'}"{/if} />
					</div>
				{elseif $key == 'desc'}
					<p class="clear">
						{if is_array($field)}
							{foreach $field as $k => $p}
								{if is_array($p)}
									<span id="{$p.id|escape:'htmlall':'UTF-8'}">{$p.text|escape:'htmlall':'UTF-8'}</span><br />
								{else}
									{$p|escape:'htmlall':'UTF-8'}
									{if isset($field[$k+1])}<br />{/if}
								{/if}
							{/foreach}
						{else}
							{$field|escape:'htmlall':'UTF-8'}
						{/if}
					</p>
				{/if}
				{block name="other_input"}{/block}
			{/foreach}
			{if $required_fields}
				<div class="small"><sup>*</sup> {l s='Required field' mod='bluepay'}</div>
			{/if}
		</fieldset>
		{block name="other_fieldsets"}{/block}
		{if isset($fields[$f+1])}<br />{/if}
	{/foreach}
</form>
{/block}
{block name="after"}{/block}

{if isset($tinymce) && $tinymce}
	<script type="text/javascript">

	var iso = '{$iso|escape:'htmlall':'UTF-8'}';
	var pathCSS = '{$smarty.const._THEME_CSS_DIR_|escape:'htmlall':'UTF-8'}';
	var ad = '{$ad|escape:'htmlall':'UTF-8'}';

	$(document).ready(function(){
		{block name="autoload_tinyMCE"}
			tinySetup({
				editor_selector :"autoload_rte"
			});
		{/block}
	});
	</script>
{/if}
{if $firstCall}
	<script type="text/javascript">
		var module_dir = '{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}';
		var id_language = {$defaultFormLanguage|escape:'htmlall':'UTF-8'};
		var languages = new Array();
		var vat_number = {if $vat_number}1{else}0{/if};
		// Multilang field setup must happen before document is ready so that calls to displayFlags() to avoid
		// precedence conflicts with other document.ready() blocks
		{foreach $languages as $k => $language}
			languages[{$k|escape:'htmlall':'UTF-8'}] = {
				id_lang: {$language.id_lang|escape:'htmlall':'UTF-8'},
				iso_code: '{$language.iso_code|escape:'htmlall':'UTF-8'}',
				name: '{$language.name|escape:'htmlall':'UTF-8'}',
				is_default: '{$language.is_default|escape:'htmlall':'UTF-8'}'
			};
		{/foreach}
		// we need allowEmployeeFormLang var in ajax request
		allowEmployeeFormLang = {$allowEmployeeFormLang|escape:'htmlall':'UTF-8'};
		displayFlags(languages, id_language, allowEmployeeFormLang);

		$(document).ready(function() {
			{if isset($fields_value.id_state)}
				if ($('#id_country') && $('#id_state'))
				{
					ajaxStates({$fields_value.id_state|escape:'htmlall':'UTF-8'});
					$('#id_country').change(function() {
						ajaxStates();
					});
				}
			{/if}

			if ($(".datepicker").length > 0)
				$(".datepicker").datepicker({
					prevText: '',
					nextText: '',
					dateFormat: 'yy-mm-dd'
				});

		});
	{block name="script"}{/block}
	</script>
{/if}

{block name="after"}
	<p class="bp-footer">
		<br />Thank you for choosing BluePay for your processing needs!
	</div>
{/block}
