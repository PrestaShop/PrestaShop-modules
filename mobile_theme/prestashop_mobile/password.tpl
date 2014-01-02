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


{capture name='forceback'}{$link->getPageLink('authentication.php')}{/capture}


{include file="$tpl_dir./header-page.tpl"}

{capture name=path}{l s='Forgot your password'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h1>{l s='Forgot your password'}</h1>

{include file="$tpl_dir./errors.tpl"}

{if isset($confirmation) && $confirmation == 1}
<div class="ui-overlay-shadow ui-body-e ui-corner-all" style="padding: 12px; margin: 15px 0;">{l s='Your password has been successfully reset and has been sent to your e-mail address:'} {$email|escape:'htmlall':'UTF-8'}</div>
{elseif isset($confirmation) && $confirmation == 2}
<div class="ui-overlay-shadow ui-body-e ui-corner-all" style="padding: 12px; margin: 15px 0;">{l s='A confirmation e-mail has been sent to your address:'} {$email|escape:'htmlall':'UTF-8'}</div>
{else}
<p style="margin: 10px 0;">{l s='Please enter the e-mail address used to register. We will e-mail you your new password.'}</p>
<form action="{$request_uri|escape:'htmlall':'UTF-8'}" method="post" class="std">
	<fieldset>
		<label for="email">{l s='E-mail:'}</label>
		<input type="text" id="email" name="email" value="{if isset($smarty.post.email)}{$smarty.post.email|escape:'htmlall':'UTF-8'|stripslashes}{/if}" />
		<input type="submit" data-theme="{$ps_mobile_styles.PS_MOBILE_THEME_BUTTONS}" data-icon="check" data-iconpos="right" value="{l s='Retrieve Password'}" />
	</fieldset>
</form>
{/if}

{include file="$tpl_dir./footer-page.tpl"}
