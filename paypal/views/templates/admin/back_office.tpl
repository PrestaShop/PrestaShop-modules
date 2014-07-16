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
*  @license	http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div id="paypal-wrapper">

	{* PayPal configuration page header *}

	<div class="box half left">
		{if isset($PayPal_logo.LocalPayPalLogoLarge)}
			<img src="{$PayPal_logo.LocalPayPalLogoLarge}" alt="" style="margin-bottom: -5px" />
		{/if}
		<p id="paypal-slogan"><span class="dark">{$PayPal_content.leader}</span> <span class="light">{$PayPal_content.online_payment}</span></p>
		<p>{$PayPal_content.tagline}</p>
	</div>

	<div class="box half right">
		<ul class="tick">{$PayPal_content.benefits}</ul>
	</div>
	
	{if $default_lang_iso == 'fr'}
	<div class="clear"></div><hr />
	<div class="box">
	{l s='Download the ' mod='paypal'}<a href="http://altfarm.mediaplex.com/ad/ck/3484-197941-8030-54"> {l s='Paypal Integration Guide' mod='paypal'}</a> {l s='on PrestaShop and follow the configuration step by step' mod='paypal'}
		
	</div>
	{else}
	<div class="clear"></div><hr />
	<div class="box">
	{l s='Download the ' mod='paypal'}<a href="http://altfarm.mediaplex.com/ad/ck/3484-197941-8030-169"> {l s='Paypal Integration Guide' mod='paypal'}</a> {l s='on PrestaShop and follow the configuration step by step' mod='paypal'}
		
	</div>

	{/if}
	<div class="clear"></div><hr>

	<form method="post" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" id="paypal_configuration">
		{* PayPal configuration blocks *}
		<div class="box">
			<div style="line-height: 18px;">{$PayPal_content.expectations}</div>
			<div style="line-height: 20px; margin-top: 8px">
				<div>
					<label>{$PayPal_content.your_country} :
						{$PayPal_country}&nbsp;&nbsp;&nbsp;<a href="#" id="paypal_country_change" class="small">{$PayPal_content.change_country}</a>
					</label>

					<div class="paypal-hide" id="paypal-country-form-content">
						<h3>{$PayPal_content.country_change_title} :</h3>

						<select name="paypal_country_default" id="paypal_country_default">
						{foreach from=$Countries item=country}
							<option value="{$country.id_country}" {if $country.id_country == $PayPal_country_id}selected="selected"{/if}>{$country.name}</option>
						{/foreach}
						</select>

						<br />
						<br />
					</div>
				</div>

				<label>{$PayPal_content.paypal_account} ?</label>
				<input type="radio" name="business" id="paypal_business_account_no" value="0" {if $PayPal_business == 0}checked="checked"{/if} /> <label for="paypal_business_account_no">{$PayPal_content.no}</label>
				<input type="radio" name="business" id="paypal_business_account_yes" value="1" style="margin-left: 14px" {if $PayPal_business == 1}checked="checked"{/if} /> <label for="paypal_business_account_yes">{$PayPal_content.yes}</label>
			</div>
		</div>

		<div class="clear"></div><hr />

		{* SELECT YOUR SOLUTION *}
		<div class="box">

			<div class="box right half" id="paypal-call-button">
				<div id="paypal-call" class="box right">{$PayPal_content.customer_support} {if !empty($PayPal_content.customer_support_image)}<img src="../modules/paypal/img/{$PayPal_content.customer_support_image}.png" width="14px" alt="Phone" />{/if}</div>
				<div id="paypal-call-foonote" class="box right clear">{$PayPal_content.support_foonote}</div>
			</div>

			<span class="paypal-section">1</span> <h3 class="inline">{$PayPal_content.select_solution}</h3> {$PayPal_content.learn_more}

			<br /><br /><br />

			{if (in_array($PayPal_WPS, $PayPal_allowed_methods) || in_array($PayPal_HSS, $PayPal_allowed_methods))}
				<h4 class="inline">{$PayPal_content.sole_solution_section_title}</h4> <img src="{$PayPal_logo.BackOfficeCards}" height="22px"/>
				<div class="clear"></div>
				<div class="form-block">
					{if (in_array($PayPal_WPS, $PayPal_allowed_methods))}
						{* WEBSITE PAYMENT STANDARD *}
						<label for="paypal_payment_wps">
							<input type="radio" name="paypal_payment_method" id="paypal_payment_wps" value='{$PayPal_WPS}' {if $PayPal_payment_method == $PayPal_WPS}checked="checked"{/if} />
							{$PayPal_content.choose} {$PayPal_content.website_payment_standard}
							<br />
							<span class="description">{$PayPal_content.website_payment_standard_tagline}</span>
						</label>
					{/if}

					{if (in_array($PayPal_HSS, $PayPal_allowed_methods))}
						{* WEBSITE PAYMENT PRO *}
						<br />
						<label for="paypal_payment_wpp">
							<input type="radio" name="paypal_payment_method" id="paypal_payment_wpp" value='{$PayPal_HSS}' {if $PayPal_payment_method == $PayPal_HSS}checked="checked"{/if} />
							{$PayPal_content.choose} {$PayPal_content.website_payment_pro}<br />
							<span class="description">{$PayPal_content.website_payment_pro_tagline}</span>
							<p class="toolbox">{$PayPal_content.website_payment_pro_disclaimer}</p>
						</label>
					{/if}
				</div>
			{/if}

			{if (in_array($PayPal_ECS, $PayPal_allowed_methods))}
			<h4 class="inline">{$PayPal_content.additional_solution_tagline}</h4> <img src="{$PayPal_logo.LocalPayPalMarkSmall}" />
			<div class="form-block">
				{* EXPRESS CHECKOUT SOLUTION *}
				<label for="paypal_payment_ecs">
					<input type="radio" name="paypal_payment_method" id="paypal_payment_ecs" value='{$PayPal_ECS}' {if $PayPal_payment_method == $PayPal_ECS}checked="checked"{/if} />
					{$PayPal_content.choose} {$PayPal_content.express_checkout}<br />
					<span class="description">{$PayPal_content.express_checkout_tagline}</span>
				</label>
			</div>
			{/if}

			<hr />
		</div>

		
		
		{* END OF USE PAYPAL LOGIN *}

		{* SUBSCRIBE OR OPEN YOUR PAYPAL BUSINESS ACCOUNT *}
		<div class="box" id="account">

			<span class="paypal-section">2</span> <h3 class="inline">{$PayPal_content.account_section_title}</h3>

			<br /><br />

			<div id="signup">
				{* Use cases 1 - 3 *}
				<a href="{if Validate::isCleanHTML($PayPal_content.u1->signUpRedirectLink)}{$PayPal_content.u1->signUpRedirectLink}{/if}" target="_blank" class="paypal-button paypal-signup-button" id="paypal-signup-button-u1">{if Validate::isCleanHTML($PayPal_content.u1->signUpCallButton)}{$PayPal_content.u1->signUpCallButton}{/if}</a>
				<a href="{if Validate::isCleanHTML($PayPal_content.u2->signUpRedirectLink)}{$PayPal_content.u2->signUpRedirectLink}{/if}" target="_blank" class="paypal-button paypal-signup-button" id="paypal-signup-button-u2">{if Validate::isCleanHTML($PayPal_content.u2->signUpCallButton)}{$PayPal_content.u2->signUpCallButton}{/if}</a>
				<a href="{if Validate::isCleanHTML($PayPal_content.u3->signUpRedirectLink)}{$PayPal_content.u3->signUpRedirectLink}{/if}" target="_blank" class="paypal-button paypal-signup-button" id="paypal-signup-button-u3">{if Validate::isCleanHTML($PayPal_content.u3->signUpCallButton)}{$PayPal_content.u3->signUpCallButton}{/if}</a>

				{* Use cases 4 - 6 *}
				{*<a href="{if Validate::isCleanHTML($PayPal_content.u4->signUpRedirectLink)}{$PayPal_content.u4->signUpRedirectLink}{/if}" target="_blank" class="paypal-button paypal-signup-button" id="paypal-signup-button-u4">{if Validate::isCleanHTML($PayPal_content.u4->signUpCallButton)}{$PayPal_content.u4->signUpCallButton}{/if}</a>*}
				<a href="{if Validate::isCleanHTML($PayPal_content.u5->signUpRedirectLink)}{$PayPal_content.u5->signUpRedirectLink}{/if}#" target="_blank" class="paypal-button paypal-signup-button" id="paypal-signup-button-u5">{if Validate::isCleanHTML($PayPal_content.u5->signUpCallButton)}{$PayPal_content.u5->signUpCallButton}{/if}</a>
				{*<a href="{if Validate::isCleanHTML($PayPal_content.u6->signUpRedirectLink)}{$PayPal_content.u6->signUpRedirectLink}{/if}" target="_blank" class="paypal-button paypal-signup-button" id="paypal-signup-button-u6">{if Validate::isCleanHTML($PayPal_content.u6->signUpCallButton)}{$PayPal_content.u6->signUpCallButton}{/if}</a>*}

				<br /><br />

				{* Use cases 1 - 3 *}
				<span class="paypal-signup-content" id="paypal-signup-content-u1">{if Validate::isCleanHTML($PayPal_content.u1->content)}{$PayPal_content.u1->content}{/if}</span>
				<span class="paypal-signup-content" id="paypal-signup-content-u2">{if Validate::isCleanHTML($PayPal_content.u2->content)}{$PayPal_content.u2->content}{/if}</span>
				<span class="paypal-signup-content" id="paypal-signup-content-u3">{if Validate::isCleanHTML($PayPal_content.u3->content)}{$PayPal_content.u3->content}{/if}</span>

				{* Use cases 4 - 6 *}
				<span class="paypal-signup-content" id="paypal-signup-content-u4">{if Validate::isCleanHTML($PayPal_content.u4->content)}{$PayPal_content.u4->content}{/if}</span>
				<span class="paypal-signup-content" id="paypal-signup-content-u5">{if Validate::isCleanHTML($PayPal_content.u5->content)}{$PayPal_content.u5->content}{/if}</span>
				<span class="paypal-signup-content" id="paypal-signup-content-u6">{if Validate::isCleanHTML($PayPal_content.u6->content)}{$PayPal_content.u6->content}{/if}</span>

			</div>

			<hr />

		</div>

		{* ENABLE YOUR ONLINE SHOP TO PROCESS PAYMENT *}
		<div class="box disabled" id="credentials">
			<span class="paypal-section">3</span> <h3 class="inline">{$PayPal_content.credentials_section_title|escape:'htmlall':'UTF-8'}</h3>

			<br /><br />

			{$PayPal_content.credentials_tagline|escape:'htmlall':'UTF-8'}

			<div class="paypal-hide" id="configuration">
				{* Credentials *}

				<div id="standard-credentials">
					<h4>{$PayPal_content.credentials_description|escape:'htmlall':'UTF-8'}</h4>

					<br />

					<a href="#" class="paypal-button" id="paypal-get-identification">
					{$PayPal_content.credentials_button|escape:'htmlall':'UTF-8'}<p class="toolbox">{$PayPal_content.credentials_button_disclaimer|escape:'htmlall':'UTF-8'}</p>
					</a>

					<br /><br />

					<dl>
						<dt><label for="api_username">{$PayPal_content.credentials_username|escape:'htmlall':'UTF-8'} : </label></dt>
						<dd><input type='text' name="api_username" id="api_username" value="{$PayPal_api_username|escape:'html':'UTF-8'}" autocomplete="off" size="85"/></dd>
						<dt><label for="api_password">{$PayPal_content.credentials_password|escape:'htmlall':'UTF-8'} : </label></dt>
						<dd><input type='password' size="85" name="api_password" id="api_password" value="{$PayPal_api_password|escape:'html':'UTF-8'}" autocomplete="off" /></dd>
						<dt><label for="api_signature">{$PayPal_content.credentials_signature|escape:'htmlall':'UTF-8'} : </label></dt>
						<dd><input type='text' size="85" name="api_signature" id="api_signature" value="{$PayPal_api_signature|escape:'html':'UTF-8'}" autocomplete="off" /></dd>
					</dl>
					<div class="clear"></div>
					<span class="description">{$PayPal_content.credentials_fields_disclaimer|escape:'htmlall':'UTF-8'}</span>
				</div>


				<div id="integral-credentials" class="paypal-hide">
					<h4>{$PayPal_content.credentials_integral_description|escape:'htmlall':'UTF-8'}</h4>

					<br />

					<dl>
						<dt><label for="api_business_account">{$PayPal_content.credentials_business_email|escape:'htmlall':'UTF-8'} : </label></dt>
						<dd><input type='text' name="api_business_account" id="api_business_account" value="{$PayPal_api_business_account|escape:'html':'UTF-8'}" autocomplete="off" /></dd>
					</dl>
				</div>

				<div class="clear"></div>

				<h4>{$PayPal_content.setup_finalize_title|escape:'htmlall':'UTF-8'} : </h4>
				<p><span class="bold">1.</span> {$PayPal_content.setup_reminder_1|escape:'htmlall':'UTF-8'}</p>
				<p><span class="bold">2.</span> {$PayPal_content.setup_reminder_2|escape:'htmlall':'UTF-8'}</p>

				<h4>{$PayPal_content.configuration_options_title|escape:'htmlall':'UTF-8'}</h4>
				<div id="integral_evolution_solution" class="paypal-hide">
					<p class="description">
						{$PayPal_content.integral_evolution_solution|escape:'htmlall':'UTF-8'}
					</p>
					<input type="radio" name="integral_evolution_solution" id="integral_evolution_solution_iframe" value="1" {if $PayPal_integral_evolution_solution == 1}checked="checked"{/if} /> <label for="integral_evolution_solution_iframe">{$PayPal_content.integral_evolution_solution_iframe|escape:'htmlall':'UTF-8'}</label><br />
					<input type="radio" name="integral_evolution_solution" id="integral_evolution_solution_no_iframe" value="0" {if $PayPal_integral_evolution_solution == 0}checked="checked"{/if} /> <label for="integral_evolution_solution_no_iframe">{$PayPal_content.integral_evolution_solution_no_iframe|escape:'htmlall':'UTF-8'}</label><br/>
					<div id="integral_evolution_template">
						<p class="description">
						{$PayPal_content.template_to_choose|escape:'htmlall':'UTF-8'}
						</p>
						<img src="../modules/paypal/img/template.png" alt=""><br/>
						<input type="radio" name="integral_evolution_template" id="integral_evolution_template_A" value="A" {if $PayPal_integral_evolution_template == "A"}checked="checked"{/if}  style="margin-left:60px"/> <label for="integral_evolution_template">A</label> &nbsp;&nbsp;&nbsp;&nbsp;
						&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="integral_evolution_template" id="integral_evolution_template_B" value="B" {if $PayPal_integral_evolution_template == "B"}checked="checked"{/if} style="margin-left:80px"/> <label for="integral_evolution_template">B</label>&nbsp;&nbsp;&nbsp;&nbsp;
						&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="integral_evolution_template" id="integral_evolution_template_C" value="C" {if $PayPal_integral_evolution_template == "C"}checked="checked"{/if} style="margin-left:70px" /> <label for="integral_evolution_template">C</label>&nbsp;&nbsp;&nbsp;&nbsp;
					</div>
					
					
				</div>

				<div id="express_checkout_shortcut" class="paypal-hide">
					<p>{$PayPal_content.express_checkout_shortcut_title|escape:'htmlall':'UTF-8'}</p>
					<p class="description">{$PayPal_content.express_checkout_shortcut_tagline|escape:'htmlall':'UTF-8'}</p>
					<input type="radio" name="express_checkout_shortcut" id="paypal_payment_ecs_no_shortcut" value="1" {if $PayPal_express_checkout_shortcut == 1}checked="checked"{/if} /> <label for="paypal_payment_ecs_no_shortcut">{$PayPal_content.yes|escape:'htmlall':'UTF-8'} {$PayPal_content.sandbox_recommended|escape:'htmlall':'UTF-8'}</label><br />
					<input type="radio" name="express_checkout_shortcut" id="paypal_payment_ecs_shortcut" value="0" {if $PayPal_express_checkout_shortcut == 0}checked="checked"{/if} /> <label for="paypal_payment_ecs_shortcut">{$PayPal_content.no|escape:'htmlall':'UTF-8'}</label>
				</div>
								<div>
					<p>{l s='Use the PayPal Login functionnality' mod='paypal'}{if $default_lang_iso == 'fr'}{l s='(*see the ' mod='paypal'} <a href="http://altfarm.mediaplex.com/ad/ck/3484-197941-8030-96"> {l s='integration guide' mod='paypal'} </a> {l s='and follow the steps' mod='paypal'}){else}{l s='(*see the ' mod='paypal'} <a href="http://altfarm.mediaplex.com/ad/ck/3484-197941-8030-170"> {l s='integration guide' mod='paypal'} </a> {l s='and follow the steps' mod='paypal'}){/if}</p>
					<p class="description">
						{l s='This function allows to your clients to connect with their PayPal credentials to shorten the check out' mod='paypal'}
					</p>
					<div id="paypal_login_yes_or_no" class="">
						<p class="description"></p>
						<input type="radio" name="paypal_login" id="paypal_login_yes" value="1" {if $PayPal_login == 1}checked="checked"{/if} /> <label for="paypal_login_yes">{l s='Yes' mod='paypal'} </label><br />
						<input type="radio" name="paypal_login" id="paypal_login_no" value="0" {if $PayPal_login == 0}checked="checked"{/if} /> <label for="paypal_login_no">{l s='No' mod='paypal'}</label>
					</div>
					<div id="paypal_login_configuration"{if $PayPal_login == 0} style="display: none;"{/if}>
						<p>
							{l s='Fill in the informations of your PayPal account' mod='paypal'}.{if $default_lang_iso == 'fr'}(* {l s='See' mod='paypal'} <a href="http://altfarm.mediaplex.com/ad/ck/3484-197941-8030-96">{l s='Integration Guide' mod='paypal'}</a>){/if}.
						</p>
						<dl>
							<dt>
								{$PayPal_content.client_id|escape:'htmlall':'UTF-8'}
							</dt>
							<dd>
								<input type="text" name="paypal_login_client_id" value="{$PayPal_login_client_id}" autocomplete="off" size="85">
							</dd>
							<dt>
								{$PayPal_content.secret|escape:'htmlall':'UTF-8'}
							</dt>
							<dd>
								<input type="text" name="paypal_login_client_secret" value="{$PayPal_login_secret}" autocomplete="off" size="85">
							</dd>
							
							<dt>
								{$PayPal_content.template_to_choose|escape:'htmlall':'UTF-8'}
								<p class="description" style="margin-top:-10px;">({$PayPal_content.translated_in_lang|escape:'htmlall':'UTF-8'})</p>
							</dt>
							<dd>
								<input type="radio" name="paypal_login_client_template" id="paypal_login_client_template_paypal_blue" value="1"{if $PayPal_login_tpl == 1} checked{/if} />
								<label for="paypal_login_client_template_paypal_blue">
									<img src="../modules/paypal/img/paypal_login_blue.png" alt=""> 
								</label>
								<br />
								<input type="radio" name="paypal_login_client_template" id="paypal_login_client_template_neutral" value="2"{if $PayPal_login_tpl == 2} checked{/if} />
								<label for="paypal_login_client_template_neutral">
									<img src="../modules/paypal/img/paypal_login_grey.png" alt=""> 
								</label>
							</dd>
						</dl>
						
						
						<div class="clear"></div>
					</div>
				</div>


				<p>{$PayPal_content.sandbox_title|escape:'htmlall':'UTF-8'}</p>
				<p class="description">{$PayPal_content.sandbox_tagline|escape:'htmlall':'UTF-8'} <a href="{$PayPal_content.sandbox_learn_more_link|escape:'htmlall':'UTF-8'}" target="_blank">{$PayPal_content.sandbox_learn_more|escape:'htmlall':'UTF-8'}</a></p>
				<input type="radio" name="sandbox_mode" id="paypal_payment_live_mode" value="0" {if $PayPal_sandbox_mode == 0}checked="checked"{/if} /> <label for="paypal_payment_live_mode">{$PayPal_content.sandbox_live_mode|escape:'htmlall':'UTF-8'}</label><br />
				<input type="radio" name="sandbox_mode" id="paypal_payment_test_mode" value="1" {if $PayPal_sandbox_mode == 1}checked="checked"{/if} /> <label for="paypal_payment_test_mode">{$PayPal_content.sandbox_test_mode|escape:'htmlall':'UTF-8'}</label>

				<br />

				<p>{$PayPal_content.payment_type_title|escape:'htmlall':'UTF-8'}</p>
				<p class="description">{$PayPal_content.payment_type_tagline|escape:'htmlall':'UTF-8'}</p>
				<input type="radio" name="payment_capture" id="paypal_direct_sale" value="0" {if $PayPal_payment_capture == 0}checked="checked"{/if} /> <label for="paypal_direct_sale">{$PayPal_content.payment_type_direct|escape:'htmlall':'UTF-8'}</label><br />
				<input type="radio" name="payment_capture" id="paypal_manual_capture" value="1" {if $PayPal_payment_capture == 1}checked="checked"{/if} /> <label for="paypal_manual_capture">{$PayPal_content.payment_type_manual|escape:'htmlall':'UTF-8'}</label>

				<br /><br />
			</div>

			<input type="hidden" name="submitPaypal" value="paypal_configuration" />
			<input type="submit" name="submitButton" value="{$PayPal_content.save_button|escape:'htmlall':'UTF-8'}" id="paypal_submit" />
			
			<div class="box paypal-hide" id="paypal-test-mode-confirmation">
				<h3>{$PayPal_content.sandbox_confirmation_title} :</h3>
				<ul>
					{$PayPal_content.sandbox_confirmation_content}
				</ul>

				<h4>{$PayPal_content.sandbox_confirmation_question}</h4>

				<div id="buttons">
					<button class="fancy_confirm" name="fancy_confirm" value="0">{$PayPal_content.no|escape:'htmlall':'UTF-8'}</button>
					<button class="fancy_confirm" name="fancy_confirm" value="1">{$PayPal_content.yes|escape:'htmlall':'UTF-8'}</button>
				</div>
			</div>

			{if isset($PayPal_save_success)}
			<div class="box paypal-hide" id="paypal-save-success">
				<h3>{$PayPal_content.congratulation_title|escape:'htmlall':'UTF-8'}</h3>
				{if $PayPal_sandbox_mode == 0}
				<p>{$PayPal_content.congratulation_live_mode|escape:'htmlall':'UTF-8'}</p>
				{elseif  $PayPal_sandbox_mode == 1}
				<p>{$PayPal_content.congratulation_test_mode|escape:'htmlall':'UTF-8'}</p>
				{/if}
			</div>
			{/if}
			{if isset($PayPal_save_failure)}
			<div class="box paypal-hide" id="paypal-save-failure">
				<h3>{l s='Error !' mod='paypal'}</h3>
				<p>{$PayPal_content.error_message|escape:'htmlall':'UTF-8'}</p>
			</div>
			{/if}

			<div class="box paypal-hide" id="js-paypal-save-failure">
				<h3>{l s='Error !' mod='paypal'}</h3>
				<p>{$PayPal_content.error_message|escape:'htmlall':'UTF-8'}</p>
			</div>

			<hr />
		</div>
	</form>

	<div class="box">
		<p class="description">
			{$PayPal_content.express_checkout_tagline_source|escape:'htmlall':'UTF-8'}
		</p>
	</div>

</div>
