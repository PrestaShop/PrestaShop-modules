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
<h3>{l s='Credentials' mod='payulatam'}</h3>
<form action="{$formCredential|escape:'htmlall':'UTF-8'}" method="POST">
	<input type="hidden" name="submitPayU" value="1" />
	{foreach from=$credentialInputVar item=input}
		{if $input.type == 'text'}
			<ul>
				<li><label class="label_payu">{$input.label|escape:'htmlall':'UTF-8'}</label></li>
				<li><input class="full input_payu" type="{$input.type|escape:'htmlall':'UTF-8'}" placeholder="{$input.label|escape:'htmlall':'UTF-8'}" id="{$input.name|escape:'htmlall':'UTF-8'}" name="{$input.name|escape:'htmlall':'UTF-8'}" value="{$input.value|escape:'htmlall':'UTF-8'}"/></li>
				<li><span class="caption">{$input.desc}</span></li>
			</ul>
		{elseif $input.type == 'radio'}
			<ul>
				<li><h4>{$input.label|escape:'htmlall':'UTF-8'}</h4></li>
				<li>
					{foreach from=$input.values item=val}
						{$val|escape:'htmlall':'UTF-8'}
						<input type="{$input.type|escape:'htmlall':'UTF-8'}" {if $val == $input.value}checked='checked'{/if} name="{$input.name|escape:'htmlall':'UTF-8'}" id="{$input.name|escape:'htmlall':'UTF-8'}{$val}" value="{$val|escape:'htmlall':'UTF-8'}" />
					{/foreach}
				</li>
				<li><input type="submit" class="md-btn button-form_payu" value="{l s='Save' mod='payulatam'}" /></li>
			</ul>
		{/if}
	{/foreach}
</form>
