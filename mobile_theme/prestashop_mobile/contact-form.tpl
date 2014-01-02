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

{include file="$tpl_dir./header-page.tpl"}

{capture name=path}{l s='Contact'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<div class="ui-body ui-body-b">
  <h2>{l s='Customer Service'} - {if isset($customerThread) && $customerThread}{l s='Your reply'}{else}{l s='Contact us'}{/if}</h2>

{if isset($confirmation)}
	<p>{l s='Your message has been successfully sent to our team.'}</p>
	<ul class="footer_links">
		<li><a href="{$base_dir}"><img class="icon" alt="" src="{$img_dir}icon/home.gif"/></a><a href="{$base_dir}">{l s='Home'}</a></li>
	</ul>
{elseif isset($alreadySent)}
	<p>{l s='Your message has already been sent.'}</p>
	<ul class="footer_links">
		<li><a href="{$base_dir}"><img class="icon" alt="" src="{$img_dir}icon/home.gif"/></a><a href="{$base_dir}">{l s='Home'}</a></li>
	</ul>
{else}
	<p class="bold">{l s='For questions about an order or for more information about our products'}.</p>
	{include file="$tpl_dir./errors.tpl"}
	<br />
	<form action="{$request_uri|escape:'htmlall':'UTF-8'}" method="post" class="std" enctype="multipart/form-data">
		<fieldset data-role="fieldcontain">
			<div data-role="fieldocontain">
				<label for="id_contact">{l s='Subject Heading'}</label>

			{if isset($customerThread.id_contact)}
				{foreach from=$contacts item=contact}
					{if $contact.id_contact == $customerThread.id_contact}
						<input type="text" id="contact_name" name="contact_name" value="{$contact.name|escape:'htmlall':'UTF-8'}" readonly="readonly" />
						<input type="hidden" name="id_contact" value="{$contact.id_contact}" />
					{/if}
				{/foreach}
			</div>
			{else}
				<select id="id_contact" name="id_contact" onchange="showElemFromSelect('id_contact', 'desc_contact')">
					<option value="0">{l s='-- Choose --'}</option>
				{foreach from=$contacts item=contact}
					<option value="{$contact.id_contact|intval}" {if isset($smarty.post.id_contact) && $smarty.post.id_contact == $contact.id_contact}selected="selected"{/if}>{$contact.name|escape:'htmlall':'UTF-8'}</option>
				{/foreach}
				</select>
			</div>
			<div data-role="fieldcontain" id="desc_contact0" class="desc_contact">&nbsp;</div>
				{foreach from=$contacts item=contact}
					<div data-role="fieldcontain" id="desc_contact{$contact.id_contact|intval}" class="desc_contact" style="display:none;">
						<label>&nbsp;</label>{$contact.description|escape:'htmlall':'UTF-8'}
					</div>
				{/foreach}
			{/if}
			<div data-role="fieldcontain">
				<label for="email">{l s='E-mail address'}</label>
				{if isset($customerThread.email)}
					<input type="text" id="email" name="from" value="{$customerThread.email}" readonly="readonly" />
				{else}
					<input type="text" id="email" name="from" value="{$email}" />
				{/if}
			</div>

		{if !$PS_CATALOG_MODE}
			{if !isset($customerThread.id_order) || $customerThread.id_order}
			<div data-role="fieldcontain">
				<label for="id_order">{l s='Order ID'}</label>
				{if !isset($customerThread.id_order) && isset($isLogged) && $isLogged == 1}
					<select id="id_order" name="id_order" ><option value="0">{l s='-- Choose --'}</option>{$orderList}</select>
				{elseif !isset($customerThread.id_order) && !isset($isLogged)}
					<input type="text" name="id_order" id="id_order" value="{if isset($customerThread.id_order) && $customerThread.id_order > 0}{$customerThread.id_order|intval}{else}{if isset($smarty.post.id_order)}{$smarty.post.id_order|intval}{/if}{/if}" />
				{elseif $customerThread.id_order}
					<input type="text" name="id_order" id="id_order" value="{$customerThread.id_order|intval}" readonly="readonly" />
				{/if}
			</div>
			{/if}

			{if isset($isLogged) && $isLogged}
			<div data-role="fieldcontain">
			  <label for="id_product">{l s='Product'}</label>
			  {if !isset($customerThread.id_product)}
				<select id="id_product" name="id_product" style="width:300px;"><option value="0">{l s='-- Choose --'}</option>{$orderedProductList}</select>
			  {elseif $customerThread.id_product}
				<input type="text" name="id_product" id="id_product" value="{$customerThread.id_product|intval}" readonly="readonly" />
			  {/if}
			</div>
			{/if}
		{/if}

		<div data-role="fieldcontain">
			<label for="message">{l s='Message'}</label>
			<textarea id="message" name="message" rows="15" cols="20">{if isset($message)}{$message|escape:'htmlall':'UTF-8'|stripslashes}{/if}</textarea>
		</div>
		<button data-theme="{$ps_mobile_styles.PS_MOBILE_THEME_BUTTONS}" data-icon="check" data-iconpos="right" type="submit" name="submitMessage" id="submitMessage" value="{l s='Send'}" class="button_large" onclick="$(this).hide();">{l s='Send'}</button>
	</fieldset>
</form>
{/if}
</div>

{include file="$tpl_dir./footer-page.tpl"}
