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
	<fieldset>
		<h4>{l s='Create your account TODAY by filling out the form below!' mod='merchantware'}</h4>
		<label for="company">{l s='Company' mod='merchantware'} <sup>*</sup></label>
		<div class="margin-form">
			<input id="company" name="company" type="text" class="text" value="" />
		</div>
		<label for="firstname">{l s='First Name' mod='merchantware'} <sup>*</sup></label>
		<div class="margin-form">
			<input id="firstname" name="firstname" type="text" class="text" value="" />
		</div>
		<label for="lastname">{l s='Last Name' mod='merchantware'} <sup>*</sup></label>
		<div class="margin-form">
			<input id="lastname" name="lastname" type="text" class="text" value="" />
		</div>
		<label for="email">{l s='Email' mod='merchantware'} <sup>*</sup></label>
		<div class="margin-form">
			<input id="email" name="email" type="text" class="text" value="" />
		</div>
		<label for="address">{l s='Address' mod='merchantware'}</label>
		<div class="margin-form">
			<input id="address" name="address" type="text" class="text" value="" />
		</div>
		<label for="city">{l s='City' mod='merchantware'}</label>
		<div class="margin-form">
			<input id="city" name="city" type="text" class="text" value="" />
		</div>
		<label for="state">{l s='State' mod='merchantware'}</label>
		<div class="margin-form">
			<select id="state" name="state_id">
			{foreach from=$states item=state}
				<option value='{$state.id_state|intval}'>{$state.name|escape:'htmlall':'UTF-8'}</option>
			{/foreach}
			</select>
		</div>
		<label for="zipcode">{l s='Zip Code' mod='merchantware'}</label>
		<div class="margin-form">
			<input id="zipcode" name="zipcode" type="text" class="text" value="" />
		</div>
		<label for="phone">{l s='Phone Number' mod='merchantware'} </label>
		<div class="margin-form">
			<input id="phone" name="phone" type="text" class="text" value="" />
		</div>
		<label for="phone2">{l s='Home Phone Number' mod='merchantware'} </label>
		<div class="margin-form">
			<input id="phone2" name="phone2" type="text" class="text" value="" />
		</div>
		<label for="comments">{l s='Products sold' mod='merchantware'}<br />{l s='or Services provided' mod='merchantware'} </label>
		<div class="margin-form">
			<textarea name="comments" id="comments" cols="30" rows="6" class="textarea"></textarea>
		</div>
		<div class="margin-form">
			<input type="submit" id="validForm" name="validForm" value="{l s='Register' mod='merchantware'}" />
		</div>
	</fieldset>
</form>
