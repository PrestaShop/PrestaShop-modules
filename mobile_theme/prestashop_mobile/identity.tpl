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

{capture name=path}<a href="{$link->getPageLink('my-account.php', true)}">{l s='My account'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Your personal information'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}


{include file="$tpl_dir./errors.tpl"}

{if isset($confirmation) && $confirmation}
	<div class="ui-body ui-body-e">
		{l s='Your personal information has been successfully updated.'}
		{if isset($pwd_changed)}<br />{l s='Your password has been sent to your e-mail:'} {$email|escape:'htmlall':'UTF-8'}{/if}
	</div>
{else}
	<h3>{l s='Please do not hesitate to update your personal information if it has changed.'}</h3>


<div class="ui-body ui-body-b">
	<form action="{$link->getPageLink('identity.php', true)}" method="post" class="std">


	  <div data-role="fieldcontain">
		<fieldset data-role="controlgroup" data-type="horizontal">
		  <legend>{l s='Title'}</legend>
		  <input type="radio" name="id_gender" id="id_gender1" value="1" {if $smarty.post.id_gender == 1 OR !$smarty.post.id_gender}checked="checked"{/if} />
		  <label for="id_gender1">{l s='Mr.'}</label>
		  <input type="radio" name="id_gender" id="id_gender2" value="2" {if $smarty.post.id_gender == 2}checked="checked"{/if} />
		  <label for="id_gender2">{l s='Mrs.'}</label>
		</fieldset>
	  </div>


	  <div data-role="fieldcontain">
		<label for="firstname">{l s='First name'}<sup>*</sup></label>
		<input type="text" id="firstname" name="firstname" value="{$smarty.post.firstname}" />
	  </div>

	  <div data-role="fieldcontain">
		<label for="lastname">{l s='Last name'}<sup>*</sup></label>
		<input type="text" name="lastname" id="lastname" value="{$smarty.post.lastname}" />
	  </div>

	  <div data-role="fieldcontain">
		<label for="email">{l s='E-mail'}<sup>*</sup></label>
		<input type="text" name="email" id="email" value="{$smarty.post.email}" />
	  </div>

	  <div data-role="fieldcontain">
		<label for="old_passwd">{l s='Current Password'}<sup>*</sup></label>
		<input type="password" name="old_passwd" id="old_passwd" />
	  </div>

	  <div data-role="fieldcontain">
		<label for="passwd">{l s='New Password'}</label>
		<input type="password" name="passwd" id="passwd" />
	  </div>

	  <div data-role="fieldcontain">
		<label for="confirmation">{l s='Confirmation'}</label>
		<input type="password" name="confirmation" id="confirmation" />
	  </div>

{* The following lines allow translations in back-office and has to stay commented

	{l s='Monday'}
	{l s='Tuesday'}
	{l s='Wednesday'}
	{l s='Thursday'}
	{l s='Friday'}
	{l s='Saturday'}
	{l s='Sunday'}
*}


	  <div data-role="fieldcontain">
		<fieldset data-role="controlgroup" data-type="horizontal">
		  <legend>{l s='Date of Birth'}</legend>

		  <label for="days">{l s='Days'}</label>
		  <select id="days" name="days">
			<option value="">-</option>
			{foreach from=$days item=day}
			<option value="{$day|escape:'htmlall':'UTF-8'}" {if ($sl_day == $day)} selected="selected"{/if}>{$day|escape:'htmlall':'UTF-8'}&nbsp;&nbsp;</option>
			{/foreach}
		  </select>

		  <label for="months">{l s='Months'}</label>
		  <select id="months" name="months">
			<option value="">-</option>
			{foreach from=$months key=k item=month}
			<option value="{$k|escape:'htmlall':'UTF-8'}" {if ($sl_month == $k)} selected="selected"{/if}>{l s="$month"}&nbsp;</option>
			{/foreach}
		  </select>

		  <label for="years">{l s='Years'}</label>
		  <select id="years" name="years">
			<option value="">-</option>
			{foreach from=$years item=year}
			<option value="{$year|escape:'htmlall':'UTF-8'}" {if ($sl_year == $year)} selected="selected"{/if}>{$year|escape:'htmlall':'UTF-8'}&nbsp;&nbsp;</option>
			{/foreach}
		  </select>
		</fieldset>
	  </div>

	  {if isset($newsletter) && $newsletter}
	  <div  data-role="fieldcontain">
		<fieldset data-role="controlgroup">
		  <input type="checkbox" name="newsletter" id="newsletter" value="1" {if isset($smarty.post.newsletter) AND $smarty.post.newsletter == 1} checked="checked"{/if} />
		  <label for="newsletter">{l s='Sign up for our newsletter'}</label>

		  <input type="checkbox" name="optin" id="optin" value="1" {if isset($smarty.post.optin) AND $smarty.post.optin == 1} checked="checked"{/if} />
		  <label for="optin">{l s='Receive special offers from our partners'}</label>
		</fieldset>
	  </div>
	  {/if}

	  <button type="submit" name="submitIdentity" value="{l s='Save'}">{l s='Save'}</button>
	  <span><sup>*</sup>{l s='Required field'}</span>
	</form>
</div>
{/if}

{include file="$tpl_dir./footer-page.tpl"}