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

<br/>
<div class="panel">
	<fieldset>
		<legend><img src="{$logo_kwixo|escape:'htmlall':'UTF-8'}" width="16" height="16" alt=""/>{l s='Kwixo evaluation' mod='kwixo'}</legend>
		<div id="info_tagline">
			{l s='Kwixo transaction' mod='kwixo'} : <b>{$transaction_id|strval}</b><br/><br/>
			{l s='Payment type' mod='kwixo'} : <b>
			{if $payment_type|strval eq 'kwixo_standard'}{l s='Kwixo in one time payment' mod='kwixo'}{/if}
		{if $payment_type|strval eq 'kwixo_comptant'}{l s='Kwixo in one time payment option after receipt' mod='kwixo'}{/if}
	{if $payment_type|strval eq 'kwixo_credit'}{l s='Kwixo in installments with payment after receipt' mod='kwixo'}{/if}
</b><br/><br/>

{l s='Last evaluation request :' mod='kwixo'} : <span id="date_tagline">{if $show_last_tagline|intval eq '0'}{l s='nothing' mod='kwixo'}{else}{$date_tagline|strval}{/if}</span><br/>
{l s='Evaluation received' mod='kwixo'} : <span id="tag">{if $show_last_tagline|intval eq '0'}{l s='nothing' mod='kwixo'}{else}{$tag_tagline|intval} => {$kwixo_statuses[$tag_tagline]|strval}{/if}</span><br/><br/>

<input class="button" onclick="executeTagline();" type="button" value="{l s='Get evaluation' mod='kwixo'}" />
</div>
<div id="loader_tagline" style="display: none">{l s='Loading in progress' mod='kwixo'}...<br/><img width="16" height="11" src="{$img_loader|escape:'htmlall':'UTF-8'}" alt=""/></div>
<input name="id_order" id="id_order" type="hidden" value="{$id_order|intval}" />
<input name="tid" id="tid" type="hidden" value="{$tid|strval}" />
<input name="token" id="token_kwixo" type="hidden" value="{$token|strval}" />
</fieldset>
</div>
