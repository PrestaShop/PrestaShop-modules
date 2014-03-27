{*
* Adyen Payment Module
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
*  @author Rik ter Beek <rikt@adyen.com>
*  @copyright  Copyright (c) 2013 Adyen (http://www.adyen.com)
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}



	{if $hpp_options|@count > 0}
		<form action="{$link->getModuleLink('adyen', 'validation', [], true)|escape:'htmlall':'UTF-8'}" method="post">
			
			<p>
				{if $currencies|@count > 1}
					{l s='We allow several currencies to be sent via Adyen.' mod='adyen'}
					<br /><br />
					{l s='Choose one of the following:' mod='adyen'}
					<select id="currency_payement" name="currency_payement" onchange="setCurrency($('#currency_payement').val());">
						{foreach from=$currencies item=currency}
							<option value="{$currency.id_currency|escape:'htmlall':'UTF-8'}" {if $currency.id_currency == $cust_currency}selected="selected"{/if}>{$currency.name|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
				{/if}
			</p>
			<p style="margin-top:20px;">
				- {l s='The total amount of your order is' mod='adyen'}
				<span id="amount" class="price">{displayPrice price=$total}</span>
				{if $use_taxes == 1}
			    	{l s='(tax incl.)' mod='adyen'}
			    {/if}
				</p>
			{if $hpp_options|@count > 0}
			
				<ul class="adyen-hpp-options">
				
				{foreach from=$hpp_options key=code item=hpp_option name=foo}
					
					<li>
						{if $smarty.foreach.foo.index == 0}
							<input class="hpp_type" type="radio" id="hpp_type_{$code|escape:'htmlall':'UTF-8'}" name="payment_type" value="{$code|escape:'htmlall':'UTF-8'}" checked="checked" />
						{else}
							<input class="hpp_type" type="radio" id="hpp_type_{$code|escape:'htmlall':'UTF-8'}" name="payment_type" value="{$code|escape:'htmlall':'UTF-8'}"/>
						{/if}
						
						<label for="hpp_type_{$code|escape:'htmlall':'UTF-8'}">
							<img src="{$content_dir|escape:'htmlall':'UTF-8'}modules/adyen/img/payment_types/{$code|escape:'htmlall':'UTF-8'}.png" alt="{$hpp_option['name']|escape:'htmlall':'UTF-8'}" />
							<span>{$hpp_option['name']|escape:'htmlall':'UTF-8'}</span>
						</label>	
						
						{if $code == 'ideal'}
							<ul class="payment_form_ideal" style="display:none;">
								{foreach from=$hpp_option['issuers'] key=bank_id item=ideal_option}
								<li>
									<input type="radio" id="hpp_ideal_type_{$bank_id|escape:'htmlall':'UTF-8'}" name="ideal_type" value="{$bank_id|escape:'htmlall':'UTF-8'}"/> 
									<label for="hpp_ideal_type_{$bank_id|escape:'htmlall':'UTF-8'}">
										<img src="{$content_dir|escape:'htmlall':'UTF-8'}modules/adyen/img/payment_types/{$ideal_option|replace:' ':''|upper|escape:'htmlall':'UTF-8'}.png" alt="{$ideal_option|escape:'htmlall':'UTF-8'}" />
										<span>{$ideal_option|escape:'htmlall':'UTF-8'}</span>
									</label>	
								</li>
								{/foreach}
							</ul>
						{/if}
					</li>
				{/foreach}
				</ul>
				
				<script type="text/javascript">
					
					$(".hpp_type").change(function() 
					{
						if($(this).val() == 'ideal') 
						{
							$(".payment_form_ideal").show();
						} else 
						{
							$(".payment_form_ideal").hide();
						}
						
					});
					
					if ($("#hpp_type_ideal").is(':checked')) 
					{
						$(".payment_form_ideal").show();
					}
				</script>
			
			{/if}
			
			<p class="adyen cart_navigation">
				<input type="submit" name="submit" value="{l s='Place my order' mod='adyen'}" class="exclusive_large left" />
			</p>
		</form>
	
	{else}

		<p class="payment_module">
			<a class="adyen-logo-link" href="{$link->getModuleLink('adyen', 'validation', [], true)|escape:'htmlall':'UTF-8'}" title="{l s='Pay with Adyen' mod='adyen'}">
			<img class="adyen-logo" src="{$this_path|escape:'htmlall':'UTF-8'}/img/adyen.png" alt="{l s='Pay with Adyen' mod='adyen'}" />
			{l s='Pay with Adyen' mod='adyen'}
			</a>
			
			
		</p>
	{/if}
	
