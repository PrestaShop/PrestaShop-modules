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
*  @version  Release: $Revision: 14011 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<form action="{$klarnaprestashopFormCredential}" method="POST">
	<fieldset class="klarna-blockSmall L">
		<legend><img src="{$module_dir}img/icon-mode.gif" alt="" /> {l s='Activation Settings' mod='klarnaprestashop'}</legend>
		<h4>{l s='Set the mode of your module' mod='klarnaprestashop'}</h4>
		<input type="radio" id="klarna_mod-beta" name="klarna_mod" {if $klarna_mod == 1}checked='checked'{/if} value="beta" /> <label for="klarna_mod-beta">{l s='Test' mod='klarnaprestashop'}</label>
		<input type="radio" id="klarna_mod-live" name="klarna_mod" {if $klarna_mod == 0}checked='checked'{/if} value="live" /> <label for="klarna_mod-live">{l s='Live' mod='klarnaprestashop'}</label>
	</fieldset>
	<fieldset class="klarna-blockSmall R">
		<legend><img src="{$module_dir}img/icon-modules.gif" alt="" /> {l s='Payment Options' mod='klarnaprestashop'}</legend>
		<input type="hidden" name="submitKlarna" value="1"/>
		<p><input type="checkbox" id="klarna_active_invoice" name="klarna_active_invoice" {if $klarna_active_invoice == 1}checked='checked'{/if} value="1" /> <label for="klarna_active_invoice">{l s='Klarna Invoice' mod='klarnaprestashop'}</label><br>
		<small>{l s='With Klarna Invoice, customers order goods using only \'top-of-mind\' information. They don\'t pay anything until after they receive their purchased goods.' mod='klarnaprestashop'}</small></p>
		<p><input type="checkbox" id="klarna_active_partpayment" name="klarna_active_partpayment" {if $klarna_active_partpayment == 1}checked='checked'{/if} value="1" /> <label for="klarna_active_partpayment">{l s='Klarna Account' mod='klarnaprestashop'}</label><br>
		<small>{l s='Klarna Account allows you to collect multiple purchases from different e-stores onto one convenient invoice you pay at the end of the month.' mod='klarnaprestashop'}</small></p>
		<p class="last"><input type="checkbox" id="klarna_email" name="klarna_email" {if $klarna_email == 1}checked='checked'{/if} value="1" /> <label for="klarna_email">{l s='Send out invoice by email' mod='klarnaprestashop'}</label></p>
	</fieldset>
	<div class="clear"></div>	
	<fieldset>
	<legend><img src="{$module_dir}img/icon-countries.gif" alt="" /> {$klarnaprestashopCredentialTitle}</legend>
		<h4>{$klarnaprestashopCredentialText}</h4>
		<ul class="klarna_list_click_country">
			{foreach from=$credentialInputVar key=name item=c}
			<li class="klarna_flag_{$name}"><img src="{$countryNames[$name].flag}" alt=""/> {$name|lower|capitalize}</li>
			{/foreach}
		</ul>
		<ul class="klarna_list_country">
			{foreach from=$credentialInputVar key=country_name item=country}
			<li class="klarna_form_{$country_name}">
				<fieldset>
					<p class="title"><img src="{$module_dir}img/flag_{$country_name}.png" alt="" />{$country_name|lower|capitalize}</p>
					<div class="fieldset-wrap">						
						{foreach from=$country item=input}
						{if $input.type == 'text'}
						<div id="klarnaInput{$input.name}" class="input-row">
							<span>{$input.label}</span>
							<input type="{$input.type}" name="{$input.name}" id="{$input.name}" value="{$input.value}" />{$input.desc}
						</div>
						{elseif $input.type == 'hidden'}
							<input type="{$input.type}" name="{$input.name}" id="{$input.name}" value="{$input.value}" />
						{elseif $input.type == 'select'}
							<div class="input-row">
								<span>{$input.label}</span>
								<select {if isset($input.id)}id="{$input.id}"{/if} {if isset($input.name)}name="{$input.name}"{/if}>
									<option>{l s='Choose' mod='klarnaprestashop'}</option>
									{foreach from=$input.option item=option}
									<option value="{$option}">{$option}</option>
									{/foreach}
								</select>
							</div>
						{/if}
						{/foreach}
					</div>
				</fieldset>
			</li>
			{/foreach}
		</ul>
		<small class="footnote">{$klarnaprestashopCredentialFootText}</small>
	</fieldset>
	<div class="center pspace"><input type="submit" class="button" value="{l s='Save | Update' mod='klarnaprestashop'}" /></div>
</form>
<h4>{l s='PCI Classes' mod='klarnaprestashop'}</h4>
<table class="table double-bottom-space" cellpadding="0" cellspacing="0" width="100%">
	<tr>
		<th>{l s='Id' mod='klarnaprestashop'}</th><th>{l s='Eid' mod='klarnaprestashop'}</th>
		<th>{l s='Country' mod='klarnaprestashop'}</th><th>{l s='Description' mod='klarnaprestashop'}</th>
		<th>{l s='Start fee' mod='klarnaprestashop'}</th><th>{l s='Invoice fee' mod='klarnaprestashop'}</th>
		<th>{l s='Interest' mod='klarnaprestashop'}</th><th>{l s='Minimum amount' mod='klarnaprestashop'}</th>
	</tr>
{foreach from=$klarna_pclass item=pclass key=k}
	<tr {if $k % 2 == 0}class="alt_row"{/if}>
		<td>{$pclass.id}</td>
		<td>{$pclass.eid}</td>
		<td>{$countryCodes[$pclass.country]}</td>
		<td>{$pclass.description}</td>
		<td>{$pclass.startfee}</td>
		<td>{$pclass.invoicefee}</td>
		<td>{$pclass.interestrate}</td>
		<td>{$pclass.minamount}</td>
	</tr>
{/foreach}
</table>

<script type="text/javascript">
    var activated = new Array();
	var i = 0;
	{foreach from=$activateCountry item=a}
		activated[i] = "{$a}";
		i++;
	{/foreach}

	function in_array(array, p_val) {
	    var l = array.length;
	    for(var i = 0; i < l; i++) {
	        if(array[i] == p_val) {
	            rowid = i;
	            return true;
	        }
	    }
	    return false;
	}
	
	$(document).ready(
	    function()
	    {
		$('li[class^="klarna_form_"]').hide();
		$("li[class^='klarna_form']").each(
		    function()
		    {
			var country = $(this).attr('class').replace('klarna_form_', '');
			if (in_array(activated, country))
			{
			    $('.klarna_form_'+country).show();
			    $('.klarna_form_'+country).append('<input type="hidden" name="activate'+country+'" value="on" id="klarna_activate'+country+'"/>');
			}
		    }
		);
	    }
	);
</script>
