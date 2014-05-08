{*
 * Ferbuy payment extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @category	Payment
 * @package	 Ferbuy
 * @author	  FerBuy, <info@ferbuy.com>
 * @copyright   Copyright (c) 2013 (http://www.ferbuy.com)
 * @license	 http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *}

<img src="{$img_ferbuylogo|escape:'htmlall':'UTF-8'}" alt="ferbuy" />

<h2>{l s='FerBuy (Buy Now, Pay Later)' mod='ferbuy'}</h2>

<fieldset>
	<legend><img src="../img/admin/information.png" />{l s='Information' mod='ferbuy'}</legend>
	<div class="margin-form">{l s='Module version:' mod='ferbuy'} {$version|escape:'htmlall':'UTF-8'}</div>
	<label>{l s='Verification URL' mod='ferbuy'}</label>
	<div class="margin-form"><input type="text" size="50" name="url" value="{$verification_url|escape:'htmlall':'UTF-8'}" /></div>
</fieldset>

<form action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post" style="clear: both; margin-top: 10px;">
	<fieldset>
		<legend><img src="../img/admin/submenu-configuration.gif" />{l s='Settings' mod='ferbuy'}</legend>

		{if  $errors|@count gt 0}
		<div class="error">
		{foreach from=$errors item=error}
			<p>{$error|escape:'htmlall':'UTF-8'}</p>
		{/foreach}
		</div>
		{/if}

		<label for="mode">{l s='Choose Demo or Live mode' mod='ferbuy'}</label>
		<div class="margin-form">
			<select id="mode" name="mode">
				<option value="demo" {if $data_mode == 'demo'}selected{/if}>{l s='Demo Mode' mod='ferbuy'}</option>
				<option value="live" {if $data_mode == 'live'}selected{/if}>{l s='Live Mode' mod='ferbuy'}</option>
			</select>
			<p>{l s='Choose between Demo or Live mode for the FerBuy payment module.' mod='ferbuy'}</p>
		</div>

		<label for="site_id">{l s='Site ID' mod='ferbuy'}</label>
		<div class="margin-form">
			<input id="site_id" type="text" size="33" name="site_id" value="{$data_site_id|escape:'htmlall':'UTF-8'}" />
			<p>{l s='Enter your Site ID. You can find this in My FerBuy.' mod='ferbuy'}</p>
		</div>

		<label for="secret">{l s='Secret Code' mod='ferbuy'}</label>
		<div class="margin-form" style="margin-bottom: 30px;">
			<input id="secret" type="text" size="33" name="secret" value="{$data_secret|escape:'htmlall':'UTF-8'}" />
			<p>{l s='Enter the Secret for your site. You can find this in My FerBuy.' mod='ferbuy'}</p>
		</div>

		<div class="margin-form">
			<input type="submit" name="ferbuy_updateSettings" value="{l s='Save Settings' mod='ferbuy'}" class="button" style="cursor: pointer; display:" />
		</div>
	</fieldset>
</form>