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

<form action="{$formCredential|escape:'htmlall':'UTF-8'}" method="POST">
	<fieldset class="merchant-warehouse-fixFiedlset">
		<h4>{$credentialTitle|escape:'htmlall':'UTF-8'}</h4>
		<p>{$credentialText|escape:'htmlall':'UTF-8'}</p>
		<img src="{$module_dir}img/partner.png" class="merchant-warehouse-badge">
		{foreach from=$credentialInputVar item=input}
		<label from="{$input.name|escape:'htmlall':'UTF-8'}">{$input.label|escape:'htmlall':'UTF-8'}</label>
		<div class="margin-form">
			<input type="{$input.type|escape:'htmlall':'UTF-8'}" name="{$input.name|escape:'htmlall':'UTF-8'}" id="{$input.name|escape:'htmlall':'UTF-8'}" value="{$input.value|escape:'htmlall':'UTF-8'}" /> {if $input.required}<sup>*</sup>{/if} {$input.desc|escape:'htmlall':'UTF-8'}
		</div>
		{/foreach}
		<div class="margin-form">
			<input type="submit" class="button" value="{l s='Save' mod='merchantware'}" />
		</div>
	</fieldset>
</form>
