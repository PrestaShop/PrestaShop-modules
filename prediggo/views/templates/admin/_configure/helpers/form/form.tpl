{*
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
*  @author Prediggo SA <info@prediggo.com> / CeboWeb <dev@ceboweb.com>
*  @copyright  2008-2012 Prediggo SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Prediggo SA
*}

{extends file="helpers/form/form.tpl"}

{block name="input"}
    {if $input.type == 'button'}
    	<input type="submit" name="{$input.name}" 
    	{if isset($input.class) && $input.class}class="{$input.class}"{/if}  
    	{if isset($input.disabled) && $input.disabled}disabled="disabled"{/if} 
    	value="{$input.title}">
	{elseif $input.type == 'attribute_selector'}
		<ul id="prediggo_attributes">
			{foreach from=$input.values.attributes item="aGroupAttribute"}
				<li>
					<input type="checkbox" name="{$input.names.attributes}" value="{$aGroupAttribute.id_attribute_group|intval}" {if in_array($aGroupAttribute.id_attribute_group|intval, $fields_value[$input.name]['attributes'])}checked="checked"{/if} {if isset($input.disabled) && $input.disabled}disabled="disabled"{/if} />
					{l s='Group of Attributes:' mod='prediggo'} {$aGroupAttribute.name|escape:'htmlall':'UTF-8'}
				</li>
			{/foreach}
	
			{foreach from=$input.values.features item="aFeature"}
				<li>
					<input type="checkbox" name="{$input.names.features}" value="{$aFeature.id_feature|intval}" {if in_array($aFeature.id_feature|intval, $fields_value[$input.name]['features'])}checked="checked"{/if} {if isset($input.disabled) && $input.disabled}disabled="disabled"{/if} />
					{l s='Feature:' mod='prediggo'} {$aFeature.name|escape:'htmlall':'UTF-8'}
				</li>
			{/foreach}
		</ul>
	{elseif $input.type == 'autocomplete'}
		<input type="hidden" name="input_{$input.name}" id="input_{$input.name}" value="{foreach from=$fields_value[$input.name] item=element}{$element.id},{/foreach}" />

		<div id="ajax_choose_product">
			<p style="clear:both;margin-top:0;">
				<input type="text"
					name="{$input.name}"
					id="{if isset($input.id)}{$input.id}{else}{$input.name}{/if}"
					value=""
					{if isset($input.class)}class="{$input.class}"{/if}
					{if isset($input.size)}size="{$input.size}"{/if}
					{if isset($input.maxlength)}maxlength="{$input.maxlength}"{/if}
					{if isset($input.class)}class="{$input.class}"{/if}
					{if isset($input.disabled) && $input.disabled}disabled="disabled"{/if} />
				{l s='Begin typing the first letters, then select the element from the drop-down list' mod='prediggo'}
			</p>
			<p class="preference_description">{l s='(Do not forget to save the product afterward)' mod='prediggo'}</p>
		</div>
				
		<ul id="ul_{$input.name}">
			{if count($fields_value[$input.name])}
				{foreach from=$fields_value[$input.name] item=element}
					<li class="deleteElement" name="{$element.id}" style="cursor: pointer;">
						{$element.name|escape:'htmlall':'UTF-8'}
						<img src="../img/admin/delete.gif"/>
					</li>
				{/foreach}
			{/if}
		</ul>
	{else}
		{$smarty.block.parent}
    {/if}
{/block}

{block name="field"}
	{if $input.type == 'hint'}
		<div class="hint" style="display:block;margin-bottom:20px;">
			{$input.content}
		</div>
	{else}
		{$smarty.block.parent}
    {/if}
{/block}