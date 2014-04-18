{*
* 2007-2013 PrestaShop
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
	<fieldset>
		<h4>{l s='Create your FREE credit card processing or ACH check processing account NOW by filling out the form below:' mod='alliance3'}</h4>
		<label for="company">{l s='Company' mod='alliance3'} <sup>*</sup></label>
		<div class="margin-form">
			<input id="company" name="company" type="text" class="text" value="" />
		</div>
		<label for="firstname">{l s='First Name' mod='alliance3'} <sup>*</sup></label>
		<div class="margin-form">
			<input id="firstname" name="firstname" type="text" class="text" value="" />
		</div>
		<label for="lastname">{l s='Last Name' mod='alliance3'} <sup>*</sup></label>
		<div class="margin-form">
			<input id="lastname" name="lastname" type="text" class="text" value="" />
		</div>
		<label for="email">{l s='Email' mod='alliance3'} <sup>*</sup></label>
		<div class="margin-form">
			<input id="email" name="email" type="text" class="text" value="" />
		</div>
		<label for="address">{l s='Address' mod='alliance3'}</label>
		<div class="margin-form">
			<input id="address" name="address" type="text" class="text" value="" />
		</div>
		<label for="city">{l s='City' mod='alliance3'}</label>
		<div class="margin-form">
			<input id="city" name="city" type="text" class="text" value="" />
		</div>
		<label for="state">{l s='State' mod='alliance3'}</label>
		<div class="margin-form">
			<select id="state" name="state_id">
			{foreach from=$states item=state}
				<option value='{$state.iso_code|escape:'htmlall':'UTF-8'}'>{$state.name|escape:'htmlall':'UTF-8'}</option>
			{/foreach}
			</select>
		</div>
		<label for="zipcode">{l s='Zip Code' mod='alliance3'}</label>
		<div class="margin-form">
			<input id="zipcode" name="zipcode" type="text" class="text" value="" />
		</div>
		<label for="phone">{l s='Office Phone Number' mod='alliance3'} </label>
		<div class="margin-form">
			<input id="phone" name="phone" type="text" class="text" value="" />
		</div>
		<label for="phone2">{l s='Cell Phone Number' mod='alliance3'} </label>
		<div class="margin-form">
			<input id="phone2" name="phone2" type="text" class="text" value="" />
		</div>
		<label for="comments">{l s='Products sold' mod='alliance3'}<br />{l s='or Services provided' mod='alliance3'} </label>
		<div class="margin-form">
			<textarea name="comments" id="comments" cols="30" rows="6" class="textarea"></textarea>
		</div>
		<div class="margin-form">
			<input type="submit" class="button" id="validForm" name="validForm" value="{l s='Register' mod='alliance3'}" />
		</div>
	</fieldset>
</form>
