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

<div class="alert alert-info">
		{l s='Note, that if default currency will be changed, all prices must be updated according to the new default currency.' mod='giveit'}
</div>
<form method="post" action="{$page_url|escape:'htmlall':'UTF-8'}">
	<div id="shipping_prices" class="panel form-horizontal">
		<h3>
			<i class="icon-cog"></i>
			{l s='Shipping Prices' mod='giveit'}
		</h3>

		<div class="form-group">
			<label for="shipping_price" class="control-label col-lg-3">
				{l s='Price' mod='giveit'}
			</label>
			<div class="col-lg-2 input-group">
				<span class="input-group-addon">{$currency->sign|escape:'htmlall':'UTF-8'}</span>
				<input type="text" name="shipping_price" id="shipping_price" value="{if isset($smarty.post.shipping_price)}{$smarty.post.shipping_price|escape:'htmlall':'UTF-8'}{elseif isset($shipping)}{$shipping->price|escape:'htmlall':'UTF-8'}{/if}" />
			</div>
			<div class="col-lg-9 col-lg-offset-3">
				<p class="help-block">{l s='Enter shipping price' mod='giveit'}</p>
			</div>
		</div>

		<div class="form-group">
			<label for="free_above" class="control-label col-lg-3">
				{l s='Free above' mod='giveit'}
			</label>
			<div class="col-lg-2 input-group">
				<span class="input-group-addon">{$currency->sign|escape:'htmlall':'UTF-8'}</span>
				<input type="text" name="free_above" id="free_above" value="{if isset($smarty.post.free_above)}{$smarty.post.free_above|escape:'htmlall':'UTF-8'}{elseif isset($shipping)}{$shipping->free_above|escape:'htmlall':'UTF-8'}{/if}" />
			</div>
			<div class="col-lg-9 col-lg-offset-3">
				<p class="help-block">{l s='Free above *note: 0 means no free above rate' mod='giveit'}</p>
			</div>
		</div>

		<div class="form-group">
			<label for="tax_percent" class="control-label col-lg-3">
				{l s='Tax percentage' mod='giveit'}
			</label>
			<div class="col-lg-3">
				<input type="text" name="tax_percent" id="tax_percent" value="{if isset($smarty.post.tax_percent)}{$smarty.post.tax_percent|escape:'htmlall':'UTF-8'}{elseif isset($shipping)}{$shipping->tax_percent|escape:'htmlall':'UTF-8'}{/if}" />
			</div>
			<div class="col-lg-9 col-lg-offset-3">
				<p class="help-block">{l s='only if you charge taxes' mod='giveit'}</p>
			</div>
		</div>
		<div class="form-group">
			<label for="tax_percent" class="control-label col-lg-3">
				{l s='Shipping method' mod='giveit'}
			</label>
				<div class="col-lg-3">
					<div class="row">
					{foreach $languages as $language}
						{assign var="title_lang" value='title_'|cat:$language['id_lang']}
						{if $languages|count > 1}
						<div class="translatable-field lang-{$language.id_lang|escape:'htmlall':'UTF-8'}" {if $language.id_lang == $id_lang_default}style="display:none;"{/if}>
							<div class="col-lg-9">
						{else}
						<div class="col-lg-12">
						{/if}
								<input type="text"
									name="{$title_lang|escape:'htmlall':'UTF-8'}"
									value="{if isset($smarty.post.$title_lang)}{$smarty.post.$title_lang|escape:'htmlall':'UTF-8'}{elseif isset($shipping)}{$shipping->title[$language.id_lang]|escape:'htmlall':'UTF-8'}{/if}" />
						{if $languages|count > 1}
							</div>
							<div class="col-lg-3">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
									{foreach $languages as $lang}
										{if $lang.id_lang == $language.id_lang}{$lang.iso_code|escape:'htmlall':'UTF-8'}{/if}
									{/foreach}
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									{foreach $languages as $language}
									<li>
										<a href="javascript:hideOtherLanguage({$language.id_lang|escape:'htmlall':'UTF-8'});">{$language.name|escape:'htmlall':'UTF-8'}</a>
									</li>
									{/foreach}
								</ul>
							</div>
						</div>
						{else}
						</div>
						{/if}
					{/foreach}
					</div>
				</div>
			<div class="col-lg-9 col-lg-offset-3">
				<p class="help-block">
						{l s='NOTE: always provide at least one shipping method for your default language' mod='giveit'} {l s='(' mod='giveit'}{$default_language_notice|escape:'htmlall':'UTF-8'}{l s=')' mod='giveit'}
				</p>
			</div>
		</div>
		<div class="form-group">
			<label for="iso_code" class="control-label col-lg-3">
				{l s='Region' mod='giveit'}
			</label>
			<div class="col-lg-3">
				<select name="iso_code" id="iso_code">
					{foreach from=$countries item=country}
						<option value="{$country.iso_code|escape:'htmlall':'UTF-8'}" {if isset($shipping)}{if $shipping->iso_code == $country.iso_code}selected="selected"{/if}{/if}>{$country.name|escape:'htmlall':'UTF-8'}</option>
					{/foreach}
				</select>
			</div>
		</div>

		<div class="panel-footer">
			<button class="btn btn-default pull-right" name="saveShippingData" type="submit">
				<i class="process-icon-save"></i>
				{l s='Add or update shipping method' mod='giveit'}
			</button>
		</div>
	</div>
</form>

<div class="panel">
	<div class="panel-heading">
		{l s='Shipping Prices List' mod='giveit'}
		<span class="badge">{$shipping_rules|count|intval}</span>
		
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
					<th class="center">
						<span class="title_box">
							{l s='Shipping method' mod='giveit'}
						</span>
					</th>
					<th class="center">
						<span class="title_box">
							{l s='Region' mod='giveit'}
						</span>
					</th>
					<th class="center">
						<span class="title_box">
							{l s='Price' mod='giveit'}
						</span>
					</th>
					<th class="center">
						<span class="title_box">
							{l s='Free above' mod='giveit'}
						</span>
					</th>
					<th class="center">
						<span class="title_box">
							{l s='Tax percentage' mod='giveit'}
						</span>
					</th>
					<th class="center">
						<span class="title_box">
							{l s='Currency' mod='giveit'}
						</span>
					</th>
					<th class="actions">
						
					</th>
				</tr>
			</thead>
			<tbody>
				{if !empty($shipping_rules)}
					{foreach from=$shipping_rules item=rule}
						<tr{if $smarty.section.ii.index % 2 == 0} class="odd"{/if}>
							<td class="center">
								{$rule.title|escape:'htmlall':'UTF-8'}
							</td>
							<td class="center">
								{foreach from=$countries item=country}
									{if $rule.iso_code == $country.iso_code}
										{$country.name|escape:'htmlall':'UTF-8'}
									{/if}
								{/foreach}
							</td>
							<td class="center">
								{$rule.price|escape:'htmlall':'UTF-8'}
							</td>
							<td class="center">
								{$rule.free_above|escape:'htmlall':'UTF-8'}
							</td>
							<td class="center">
								{$rule.tax_percent|escape:'htmlall':'UTF-8'}
							</td>
							<td class="center">
								{$rule.currency_sign|escape:'htmlall':'UTF-8'}
							</td>
							<td class="text-right">
								<div class="btn-group-action">
									<div class="btn-group pull-right">
										<a class="edit btn btn-default" title="{l s='Edit' mod='giveit'}" href="{$page_url|escape:'htmlall':'UTF-8'}&edit_rule={$rule.id_giveit_shipping|escape:'htmlall':'UTF-8'}">
											<i class="icon-pencil"></i>
											{l s='Edit' mod='giveit'}
										</a>
										<button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
											<span class="caret"></span>
										</button>
										<ul class="dropdown-menu">
											<li>
												<a class="delete" title="{l s='Delete' mod='giveit'}" onclick="if (!confirm('{l s='Delete selected item?' mod='giveit'}')){ return false; }" href="{$page_url|escape:'htmlall':'UTF-8'}&delete_rule={$rule.id_giveit_shipping|escape:'htmlall':'UTF-8'}">
													<i class="icon-trash"></i>
													{l s='Delete' mod='giveit'}
												</a>
											</li>
										</ul>
									</div>
								</div>
							</td>
						</tr>
					{/foreach}
				{else}
					<tr>
						<td class="text-center text-muted" colspan="7">
							<i class="icon-warning-sign"></i>
							{l s='No items found' mod='giveit'}
						</td>
					</tr>
				{/if}
			</tbody>
		</table>
	</div>
</div>
