{**
 * $Id$
 *
 * sofortbanking Module
 *
 * Copyright (c) 2009 touchdesign
 *
 * @category Payment
 * @version 2.0
 * @copyright 19.08.2009, touchdesign
 * @author Christin Gruber, <www.touchdesign.de>
 * @link http://www.touchdesign.de/loesungen/prestashop/sofortueberweisung.htm
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * Description:
 *
 * Payment module sofortbanking
 *
 * --
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@touchdesign.de so we can send you a copy immediately.
 *
 *}

{literal}
<style type="text/css">
fieldset a {
	color:#0099ff;
	text-decoration:underline;"
}
fieldset a:hover {
	color:#000000;
	text-decoration:underline;"
}
</style>
{/literal}

<div><img src="{$sofort.dfl.img_path|escape:'htmlall':'UTF-8'}/sofortbanking.png" width="200" height="75" alt="sofortbanking.png" title="" /></div>
<form method="post" action="{$sofort.dfl.action|escape:'htmlall':'UTF-8'}">
<fieldset>
	<legend><img src="{$sofort.dfl.path|escape:'htmlall':'UTF-8'}/logo.gif" width="16" height="16" alt="logo.gif" title="" />{l s='Settings' mod='sofortbanking'}</legend>
	<label>{l s='sofortbanking user ID?' mod='sofortbanking'}</label>
	<div class="margin-form">
		<input type="text" name="SOFORTBANKING_USER_ID" value="{$sofort.config.SOFORTBANKING_USER_ID|escape:'htmlall':'UTF-8'}" />
		<p>{l s='Leave it blank for disabling' mod='sofortbanking'}</p>
	</div>
	<div class="clear"></div>
	<label>{l s='sofortbanking project ID?' mod='sofortbanking'}</label>
	<div class="margin-form">
		<input type="text" name="SOFORTBANKING_PROJECT_ID" value="{$sofort.config.SOFORTBANKING_PROJECT_ID|escape:'htmlall':'UTF-8'}" />
		<p>{l s='Leave it blank for disabling' mod='sofortbanking'}</p>
	</div>
	<div class="clear"></div>
	<label>{l s='sofortbanking project password?' mod='sofortbanking'}</label>
	<div class="margin-form">
		<input type="password" name="SOFORTBANKING_PROJECT_PW" value="{$sofort.config.SOFORTBANKING_PROJECT_PW|escape:'htmlall':'UTF-8'}" />
		<p>{l s='Leave it blank for disabling' mod='sofortbanking'}</p>
	</div>
	<div class="clear"></div>
	<label>{l s='sofortbanking notify password?' mod='sofortbanking'}</label>
	<div class="margin-form">
		<input type="password" name="SOFORTBANKING_NOTIFY_PW" value="{$sofort.config.SOFORTBANKING_NOTIFY_PW|escape:'htmlall':'UTF-8'}" />
		<p>{l s='Leave it blank for disabling' mod='sofortbanking'}</p>
	</div>
	<div class="clear"></div>
	<label>{l s='sofortbanking Logo?' mod='sofortbanking'}</label>
	<div class="margin-form">
		<select name="SOFORTBANKING_BLOCK_LOGO">
			<option {if $sofort.config.SOFORTBANKING_BLOCK_LOGO == "Y"}selected{/if} value="Y">{l s='Yes, display the logo (recommended)' mod='sofortbanking'}</option>
			<option {if $sofort.config.SOFORTBANKING_BLOCK_LOGO == "N"}selected{/if} value="N">{l s='No, do not display' mod='sofortbanking'}</option>
		</select>
		<p>{l s='Display logo and payment info block in left column' mod='sofortbanking'}</p>
	</div>
	<div class="clear"></div>
	<label>{l s='Customer protection active:' mod='sofortbanking'}</label>
	<div class="margin-form">
		<select name="SOFORTBANKING_CPROTECT">
			<option {if $sofort.config.SOFORTBANKING_CPROTECT == "Y"}selected{/if} value="Y">{l s='Yes' mod='sofortbanking'}</option>
			<option {if $sofort.config.SOFORTBANKING_CPROTECT == "N"}selected{/if} value="N">{l s='No' mod='sofortbanking'}</option>
		</select>
		<p>
			{l s='You need a bank account with' mod='sofortbanking'}
			<a target="_blank" href="http://www.sofort-bank.com" target="_blank">Sofort Bank</a>
			{l s='You need a bank account with and customer protection must be enabled in your project settings. Please check with' mod='sofortbanking'}
			<a target="_blank" href="https://kaeuferschutz.sofort-bank.com/consumerProtections/index/{$sofort.config.SOFORTBANKING_PROJECT_ID|escape:'htmlall':'UTF-8'}">{l s='this link' mod='sofortbanking'}</a>
			{l s='if customer protection is activated and enabled before enabling it here.' mod='sofortbanking'}
		</p>
	</div>
	<div class="clear"></div>
	<label>{l s='Force redirect?' mod='sofortbanking'}</label>
	<div class="margin-form">
		<select name="SOFORTBANKING_REDIRECT">
			<option {if $sofort.config.SOFORTBANKING_REDIRECT == "Y"}selected{/if} value="Y">{l s='Yes' mod='sofortbanking'}</option>
			<option {if $sofort.config.SOFORTBANKING_REDIRECT == "N"}selected{/if} value="N">{l s='No, let the customer confirm the order first.' mod='sofortbanking'}</option>
		</select>
		<p>{l s='Force redirect to soforbanking payment page (skip confirm page).' mod='sofortbanking'}</p>
	</div>
	<div class="clear"></div>
	<div class="margin-form clear pspace"><input type="submit" name="submitUpdate" value="{l s='Update' mod='sofortbanking'}" class="button" /></div>
</fieldset>
</form><br />

<fieldset>
	<legend><img src="{$sofort.dfl.path|escape:'htmlall':'UTF-8'}/logo.gif" width="16" height="16" alt="logo.gif" title="" />{l s='URLs' mod='sofortbanking'}</legend>
	<b>{l s='Confirmation-Url:' mod='sofortbanking'} {l s='(Method POST)' mod='sofortbanking'}</b><br /><textarea rows=1 style="width:98%;">{$sofort.link.validation|escape:'htmlall':'UTF-8'}</textarea>
	<br /><br />
	<b>{l s='Success-Url:' mod='sofortbanking'}</b><br /><textarea rows=1 style="width:98%;">{$sofort.link.success|escape:'htmlall':'UTF-8'}</textarea>
	<br /><br />
	<b>{l s='Cancel-Url:' mod='sofortbanking'}</b><br /><textarea rows=1 style="width:98%;">{$sofort.link.cancellation|escape:'htmlall':'UTF-8'}</textarea>
</fieldset>

<fieldset class="space">
	<legend><img src="../img/admin/unknown.gif" width="16" height="16" alt="unknown.gif" title="" />{l s='Help' mod='sofortbanking'}</legend>
	<b>{l s='@Link:' mod='sofortbanking'}</b> <a target="_blank" href="http://www.touchdesign.de/ico/paymentnetwork.htm">{l s='sofortbanking.com' mod='sofortbanking'}</a><br />
	{l s='@Author and Copyright:' mod='sofortbanking'} <a target="_blank" href="http://www.touchdesign.de">touchdesign</a><br />
	<b>{l s='@Description:' mod='sofortbanking'}</b><br /><br />
	{l s='sofortbanking is the direct payment method of Payment Network AG. sofortbanking allows you to directly and automatically trigger a credit transfer during your online purchase with your online banking information. A transfer order is instantly confirmed to merchant allowing an instant delivery of goods and services.' mod='sofortbanking'}
</fieldset><br />