{*
 * 2007-2014 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2014 PrestaShop SA
 *  @version  Release: 0.4.4
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *}
<div id="new_customer">
	<div class="mostrarNoUsuario">
	   <p class="contactplease"> {l s='Please contact' mod='seur'} </p>
		<p> {l s='902 10 10 10' mod='seur'}</p>
		<p> {l s='www.seur.com' mod='seur'}</p>
		<p class="gracias"> {l s='Thank you' mod='seur'}</p>
	</div>
	
	<form action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post" name="merchant_form" class="ocultar">
		<fieldset>
			<legend id="legentNuevaAlta">
			  <img src="{$img_path|escape:'htmlall':'UTF-8'}new-user.png" alt="{l s='New user' mod='seur'}" title="{l s='New user' mod='seur'}" />{l s='New user' mod='seur'}
			</legend>
			
			<legend id="legendLogin">
				<img src="{$img_path|escape:'htmlall':'UTF-8'}login-on.png" alt="{l s='Login' mod='seur'}" title="{l s='Login' mod='seur'}" />{l s='Login' mod='seur'}
			</legend>
			
			<p class="check_required"><sup>*</sup> {l s='Required fields' mod='seur'}</p>
			
			<dl>
				<dt><label>{l s='NIF / CIF' mod='seur'}</label></dt>
				<dd class="required">
					<input type="text" name="nif_dni" />
					<sup name="nif_dni">*</sup>
					<span class="field-help">{l s='Enter the NIF / CIF Company. (Eg. 11111111-A)' mod='seur'}
				</dd>

				<dt><label>{l s='Name' mod='seur'}</label></dt>
				<dd class="required">
					<input type="text" name="name" />
					<sup name="name">*</sup>
					<span class="field-help">{l s='Name of the contact person.' mod='seur'}
				</dd>

				<dt><label>{l s='Firstname' mod='seur'}</label></dt>
				<dd class="required">
					<input type="text" name="first_name" />
					<sup name="first_name">*</sup>
					<span class="field-help">{l s='Firstname of the contact person.' mod='seur'}
				</dd>

				<dt><label>{l s='Company Name' mod='seur'}</label></dt>
				<dd class="required">
					<input type="text" name="company_name" />
					<sup name="company_name">*</sup>
					<span class="field-help">{l s='Legal name of the company.' mod='seur'}
				</dd>
				
				<dt><label>{l s='Street Type' mod='seur'}</label></dt>
				<dd class="required">
					<select name="street_type">
						<option value="">---</option>
						{foreach from=$street_types key='abbreviation' item='street_type'}
							<option value="{$abbreviation|escape:'htmlall':'UTF-8'}" id="{$abbreviation|escape:'htmlall':'UTF-8'}">{$street_type|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
					<sup name="street_type">*</sup>
				</dd>
				
				<dt><label>{l s='Name street' mod='seur'}</label></dt>
				<dd class="required">
					<input type="text" name="street_name" />
					<sup name="street_name">*</sup>
				</dd>
				
				<dt><label>{l s='Number' mod='seur'}</label></dt>
				<dd class="required">
					<input type="text" name="street_number" />
					<sup name="street_number">*</sup>
				</dd>
				
				<dt><label>{l s='Staircase' mod='seur'}</label></dt>
				<dd><input type="text" name="staircase" /></dd>
				
				<dt><label>{l s='Floor' mod='seur'}</label></dt>
				<dd><input type="text" name="floor" /></dd>
				
				<dt><label>{l s='Door' mod='seur'}</label></dt>
				<dd><input type="text" name="door" /></dd>
				
				<dt><label>{l s='Postal Code' mod='seur'}</label></dt>
				<dd class="required"><input type="text" name="post_code_cfg" />
					<sup name="post_code_cfg">*</sup>
					<span class="field-help">{l s='Postal code of the company.' mod='seur'}</span>
					<input type="hidden" name="token" value="{$token|escape:'htmlall':'UTF-8'}"/>
					<input type="hidden" name="id_employee" value="{$employee->id|escape:'htmlall':'UTF-8'}"/>
				</dd>

				<dt><label>{l s='City' mod='seur'}</label></dt>
				<dd class="required">
					<input type="text" name="town_cfg" value=""  />
					<sup name="town_cfg">*</sup>
				</dd>

				
				<dt><label>{l s='State' mod='seur'}</label></dt>
				<dd class="required">
					<input type="text" name="state_cfg" value="" />
					<sup name="state_cfg">*</sup>
				</dd>

				
				<dt><label>{l s='Country' mod='seur'}</label></dt>
				<dd class="required">
					<select name="country_cfg">
						<option value=""></option>
						{foreach from=$seur_countries key='abbreviation' item='seur_country'}
							<option value="{$abbreviation|escape:'htmlall':'UTF-8'}">{$seur_country|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
					<sup name="country">*</sup>
				</dd>

				
				<dt><label>{l s='Franchise' mod='seur'}</label></dt>
				<dd class="required">
					<input type="text" name="franchise_cfg" value=""/>
					<sup name="franchise_cfg">*</sup>
				</dd>
				
				<dt><label>{l s='Phone' mod='seur'}</label></dt>
				<dd class="required">
					<input type="text" name="phone" />
					<sup name="phone">*</sup>
				</dd>
				
				<dt><label>{l s='Fax' mod='seur'}</label></dt>
				<dd><input type="text" name="fax" /></dd>
				
				<dt><label>{l s='Email' mod='seur'}</label></dt>
				<dd class="required">
					<input type="text" name="email" />
					<sup name="email">*</sup>
				</dd>
				
				<dt><label>{l s='CCC' mod='seur'}</label></dt>
				<dd class="required">
					<input type="text" name="ccc_cfg" />
					<sup name="ccc_cfg">*</sup>
					<span class="field-help">{l s='The CCC will be provided by SEUR. It is a numeric code from 1 to 7 digits.' mod='seur'}
				</dd>
				
				<dt><label>{l s='CIT' mod='seur'}</label></dt>
				<dd class="required">
					<input type="text" name="ci" value="{$merchant_data.cit|escape:'htmlall':'UTF-8'}" />
					<sup name="ci">*</sup>
				</dd>
				
				<dt><label>{l s='User' mod='seur'}</label></dt>
				<dd class="required">
					<input type="text" name="user_cfg" value="{$merchant_data.user|escape:'htmlall':'UTF-8'}" /><sup name="user_cfg">*</sup>
					<input type="hidden" name="token_cfg" value="{$token|escape:'htmlall':'UTF-8'}"/>
					<input type="hidden" name="id_employee_cfg" value="{$employee->id|escape:'htmlall':'UTF-8'}"/>
				</dd>
				
				<dt><label>{l s='Password' mod='seur'}</label></dt>
				<dd class="required">
					<input type="password" name="pass_cfg" value="{$merchant_data.pass|escape:'htmlall':'UTF-8'}" /><sup name="pass_cfg">*</sup>
				</dd>
				
				<dt><label>{l s='User of www.seur.com' mod='seur'}</label></dt>
				<dd class="required">
					<input type="text" name="user_seurcom" value="{$user_seurcom|escape:'htmlall':'UTF-8'}" /><sup name="user_seurcom">*</sup>
					<input type="hidden" name="token_cfg" value="{$token|escape:'htmlall':'UTF-8'}"/>
				</dd>
				
			
				<dt><label>{l s='Password of www.seur.com' mod='seur'}</label></dt>
				<dd class="required">
					<input type="password" name="pass_seurcom" value="{$pass_seurcom|escape:'htmlall':'UTF-8'}" /><sup name="pass_seurcom">*</sup>
				</dd>
				
				
				<dt class="submit required">
					<h3>{l s='Privacy policy' mod='seur'}</h3>
				</dt>
				<dd>
					<span>
						{l s='In accordance with the Organic Law 15/1999, of December 13, Protection of Personal Data, we inform you that the personal data you provide will be treated confidentially.' mod='seur'}<br />
						{l s='By proceeding to be high, the system could send the information to a linked email SEUR SA.' mod='seur'}<br />
						{l s='SEUR SA undertakes to not sharing your personal data to third parties outside SEUR SA.' mod='seur'}<br />
						{l s='Save in those situations where in in accordance with the purpose for which the data were obtained, it becomes necessary to assign to any person acting for or on behalf of or in connection with the business of SEUR SA.' mod='seur'}
					</span>
					
					<p class="checkbox">
						<input type="checkbox" name="lopd" id="lopd" />
						{l s='Accept the terms' mod='seur'} <sup name="sup_lopd">*</sup>
					</p>
					
					<input type="submit" name="submitLogin" class="button" value="{l s='Send' mod='seur'}" />
				</dd>
		   </dl>
		</fieldset>
	</form>
</div>

<div id="seurJsTranslations" class="hidden">
	<input type="hidden" name="requiredField" value="{l s='Fill in the field.' mod='seur'}" />
	<input type="hidden" name="onlyNumbers" value="{l s='Please enter only numbers.' mod='seur'}" />
	<input type="hidden" name="onlyEmail" value="{l s='Malformed e-mail.' mod='seur'}" />
	<input type="hidden" name="onlyText" value="{l s='Enter only letters.' mod='seur'}" />
	<input type="hidden" name="requiredSelect" value="{l s='Select an option.' mod='seur'}" />
	<input type="hidden" name="acceptPrivacyPolicy" value="{l s='Accept the privacy policy.' mod='seur'}" />
</div>

<div id="outputData"></div>