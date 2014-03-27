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

<form action="{$formShipping|escape:htmlall:'UTF-8'}" method="post">
	<input type="hidden" name="layout" value="shipping" />
	<fieldset>
		<h4>{$shippingTitle|escape:htmlall:'UTF-8'}</h4>
		<p>{$shippingText|escape:htmlall:'UTF-8'}</p>
		{foreach from=$shippingInputVar item=input}
		<label from="recipient{$input.name}" id="recipientLabel{$input.name|escape:htmlall:'UTF-8'}" name="recipient{$input.name|escape:htmlall:'UTF-8'}" {if isset($input.hidden) && $input.hidden}style="display:none"{/if}>{$input.label|escape:htmlall:'UTF-8'}</label>
		<div class="margin-form">
			{if $input.type == 'text'}
			<input type="{$input.type}" name="recipient{$input.name|escape:htmlall:'UTF-8'}" id="recipient{$input.name|escape:htmlall:'UTF-8'}" value="{$input.value|escape:htmlall:'UTF-8'}" /> {if $input.required}<span style="color:red">{l s='*' mod='gointerpay'}</span>{/if} {$input.desc}
			{else if $input.type == 'select'}
			<select name="recipient{$input.name|escape:htmlall:'UTF-8'}" id="recipient{$input.name|escape:htmlall:'UTF-8'}" {if isset($input.hidden) && $input.hidden}style="display:none"{/if}>
				{foreach from=$input.value item=option key=k}
				<option value="{$k}" {if $input.defaultValue == $k}selected='selected'{/if}>{$option.text|escape:htmlall:'UTF-8'}</option>
				{/foreach}
			</select>
			{$input.desc|escape:htmlall:'UTF-8'}
			{/if}
		</div>
		{/foreach}
		<div class="margin-form"><input type="submit" class="button" value="{l s='Save' mod='gointerpay'}" /></div>
	</fieldset>
</form>
    <script type="text/javascript">
    $('#recipientCountry, #senderCountry').change(function()
    {literal}
    {
	var select = $(this).attr('id').replace('Country', 'State');
	var label = $(this).attr('id').replace('Country', 'LabelState');
	var country = $(this).val();
	$.ajax({
	    type : 'GET',
	    url : '../modules/gointerpay/states.php?id_country='+$(this).val(),
	    dataType: 'JSON',
	    success: function(data)
	    {
			if (data != 0)
			{
				$.each(data[country], function(i, item){
				$('#'+select).append('<option value="id_state">'+item.name+'</option>');
				$('#'+select).show();
				$('#'+label).show();
				});
			}
			else
			{
				$('#'+select).hide();
				$('#'+label).hide();
			}
	    }
	});
    });
    {/literal}
</script>