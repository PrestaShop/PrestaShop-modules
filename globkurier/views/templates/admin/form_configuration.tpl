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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2014 PrestaShop SA
 *  @license	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *}
 
 <table class="gk-main-table" border="0" cellpadding="0" cellspacing="0">
 	<tr>
	 	<td valign="top" width="50%">
			<div class="gk-header-box">
				<h1><img src="../modules/globkurier/img/logo_mini.png" /></h1>
			</div>
		</td>
		<td>
			<div class="gk-header-box">
				<span style="float:right; width:40%;"><a href="https://www.globkurier.pl/rejestracja.html" target="blank"><span class="gk-custom-button-register">{l s='Sign up' mod='globkurier'}</span></a></span>
				<span style="float:left; width:60%;"><p class="font1">{l s='Save up to 80&#37; off on UPS, DPD, K-EX, DHL with GlobKurier module' mod='globkurier'}</p></span>
			</div>
		</td>
 	</tr>
 	<tr>
		<td align="center" valign="top" width="50%">
			<div class="gk-info-box-1">
			<h3 class="font1">{l s='Shipping with GlobKurier is fast and easy' mod='globkurier'}</h3>
			<p class="font2">{l s='You save time, because courier shipments automatically generate' mod='globkurier'}</p>
			<h4 class="font1">{l s='Benefits of GlobKurier' mod='globkurier'}:</h4>
			<ul>
				<li>&#9679;<span>{l s='Savings on you domestic and international parcels' mod='globkurier'}</span></li>
				<li>&#9679;<span>{l s='Fast C.O.D return' mod='globkurier'}</span></li>
				<li>&#9679;<span>{l s='Free account' mod='globkurier'}</span></li>
				<li>&#9679;<span>{l s='Professional air, road and sea export and import services' mod='globkurier'}</span></li>
			</ul>
			<a href="https://www.globkurier.pl/rejestracja.html" target="blank"><span class="gk-custom-button-register">{l s='Sign up' mod='globkurier'}</span></a>
			</div>
		</td>
		<td valign="top">
			<div class="gk-info-box-2">
			<p class="font2">{l s='Why to ship only with one courier service when you can have them all and cheaper?' mod='globkurier'}</p>
			<img src="../modules/globkurier/img/child.png" />
			</div>
		</td>
	</tr>
	<tr>
		<td valign="top" width="50%">
			<fieldset class="gk-main-wrapper">
				<legend><img src="../modules/globkurier/img/cog.png" />{l s='Log in' mod='globkurier'}</legend>
				{if !empty($arr_login_err)}
					{foreach from=$arr_login_err item=i}
						<div class="gk-order-errors">
							<div class="error">{l s={$i|escape:'htmlall':'UTF-8'} mod='globkurier'}</div>
						</div>
					{/foreach}
				{/if}
				{if !empty($arr_login_result)}
					{if $arr_login_result.get_data eq true}
						<div class="gk-order-errors">
							<div class="conf">{l s='GlobKurier module configured properly.' mod='globkurier'}</div>
						</div>
					{/if}
				{/if}
				<div class="box60" style="width: 100%; float: left; padding-top: 0px;">
					<form class="gk-form" action="" method="post">
						<table class="form-table" border="0" cellpadding="0" cellspacing="0" width="100%">
							<tr align="center">
								<td><label class="font2">{l s='Email (Login)' mod='globkurier'}:</label></td>
								<td align="left"><input class="gk-input-txt" type="text" name="gk_login" value="{$login|escape:'htmlall':'UTF-8'}" /></td>
							</tr>
							<tr align="center">
								<td><label class="font2">{l s='Password' mod='globkurier'}:</label></td>
								<td align="left"><input class="gk-input-txt" type="password" name="gk_password" value="{$password|escape:'htmlall':'UTF-8'}" /></td>
							</tr>
							<tr align="center">
								<td><label class="font2">{l s='API key' mod='globkurier'}:</label></td>
								<td align="left"><input class="gk-input-txt" type="text" name="gk_api_key" value="{$apikey|escape:'htmlall':'UTF-8'}" /></td>
							</tr>
							<tr align="center">
								<td COLSPAN=2 >&nbsp;</td>
							</tr>
							<tr align="center">
								<td COLSPAN=2 ><input type="submit" name="gk_save" value="{l s='Save' mod='globkurier'}" class="gk-custom-button" /></td>
							</tr>
						</table>
					</form>
				</div>
				{if !empty($arr_login_result)}
					{if $arr_login_result.get_data eq true}
						<div class="box60" style="width: 100%; float: left;">
							<div class="gk-login">
								<p class="font2">
									<span class="gk-login-label">{l s='Name' mod='globkurier'}: </span>
									<span class="gk-login-data">{$name|escape:'htmlall':'UTF-8'}</span>
								</p>
								{if !empty($company) or $company != ''}
									<p class="font2">
										<span class="gk-login-label">{l s='Company' mod='globkurier'}: </span>
										<span class="gk-login-data">{$company|escape:'htmlall':'UTF-8'}</span>
									</p>
								{/if}
								<p class="font2">
									<span class="gk-login-label">{l s='Address' mod='globkurier'}: </span>
									<span class="gk-login-data">{$address|escape:'htmlall':'UTF-8'}</span>
								</p>
								<p class="font2">
									<span class="gk-login-label">{l s='Address cont.' mod='globkurier'}: </span>
									<span class="gk-login-data">{$address_cont|escape:'htmlall':'UTF-8'}</span>
								</p>
								<p class="font2">
									<span class="gk-login-label">{l s='Phone' mod='globkurier'}: </span>
									<span class="gk-login-data">{$phone|escape:'htmlall':'UTF-8'}</span>
								</p>
								{if !empty($iban)}
									<p class="font2">
										<span class="gk-login-label">{l s='Iban' mod='globkurier'}: </span>
										<span class="gk-login-data">{$iban|escape:'htmlall':'UTF-8'}</span>
									</p>
								{/if}
							</div>
						</div>
					{/if}
				{/if}
				<div class="box60" style="width: 100%;">
					<div class="gk-login-infos">
						<p class="font2"><span class="gk-login-label">{l s='Have a question ? ' mod='globkurier'}<span class="pomoc">api@globkurier.pl<span></span></p>
					</div>
				</div>
			</fieldset>
		</td>
		<td valign="top">
			<p class="gk-header-how-to">{l s='How to start?' mod='globkurier'}</p>
			<div class="gk-info-box-3">
				<p class="font2">{l s='1. Get your API key through' mod='globkurier'}</p>
				<p class="font2">{l s='a) Your current email address in GlobKurier or' mod='globkurier'}</p>
				<p class="font2">{l s='b) Create an account below' mod='globkurier'}</p>
				<p class="font2">{l s='2. Wait for the email with your API key' mod='globkurier'}</p>
				<p class="font2">{l s='3. Complete the form on the left.' mod='globkurier'}</p>
				<p class="font2">{l s='It’s done !' mod='globkurier'}</p>
			</div>
		</td>
	</tr>
 </table>
 {if empty($arr_login_result)}
 <table class="gk-main-table" border="0" >
 	<tr>
 		<td valign="top">
	 		<fieldset class="gk-main-wrapper-left">
	 			<legend><img src="../modules/globkurier/img/cog.png" />{l s='Do you have an account in GlobKurier?' mod='globkurier'}</legend>
	 			{if !empty($arr_apikey_err)}
					{foreach from=$arr_apikey_err item=i}
						<div class="gk-order-errors">
							<div class="error">{l s={$i|escape:'htmlall':'UTF-8'} mod='globkurier'}</div>
						</div>
					{/foreach}
				{/if}
				{if !empty($arr_apikey_result)}
					{if $arr_apikey_result.status eq true}
						<div class="gk-order-errors">
							<div class="conf">{l s='Api key was sent. Have a question? presta@globkurier.pl' mod='globkurier'}</div>
						</div>
					{/if}
				{/if}
	 			<form class="gk-form" action="" method="post">
	 				<p class="font2">{l s='Have an account? Enter your email address, we will send you an API key to activate the module.' mod='globkurier'}</p>
	 				<table class="form-table" border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr align="center">
							<td><label class="font2">{l s='Email (Login)' mod='globkurier'}:</label></td>
							<td align="left"><input class="gk-input-txt" type="text" name="gk_api_email" value="{$gk_api_email|escape:'htmlall':'UTF-8'}" /></td>
						</tr>
						<tr align="center">
							<td COLSPAN=2 >&nbsp;</td>
						</tr>
						<tr align="center">
							<td COLSPAN=2 ><input type="submit" name="gk_get_api" value="{l s='Send' mod='globkurier'}" class="gk-custom-button" /></td>
						</tr>
					</table>
	 			</form>
	 		</fieldset>
 		</td>
 		<td valign="top" width="50%">
 			<fieldset class="gk-main-wrapper-right" style="margin-left:2.8%">
 				<legend><img src="../modules/globkurier/img/cog.png" />{l s='Dont have an account? Sign up.' mod='globkurier'}</legend>
 				{if !empty($arr_register_err)}
					{foreach from=$arr_register_err item=i}
						<div class="gk-order-errors">
							<div class="error">{l s={$i|escape:'htmlall':'UTF-8'} mod='globkurier'}</div>
						</div>
					{/foreach}
				{/if}
				{if !empty($arr_register_result)}
					{if $arr_register_result.status eq true}
						<div class="gk-order-errors">
							<div class="conf">{l s='Activation link was sent. Help Center - Asking a Question: presta@globkurier.pl' mod='globkurier'}</div>
						</div>
					{/if}
				{/if}
 				<form class="gk-form" action="" method="post">
 					<table class="form-table" border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr align="center">
							<td><label class="font2">{l s='Email (Login)' mod='globkurier'}:</label></td>
							<td align="left"><input class="gk-input-txt" type="text" name="gk_email" value="{$gk_email|escape:'htmlall':'UTF-8'}" /></td>
						</tr>
						<tr align="center">
							<td><label class="font2">{l s='Password' mod='globkurier'}:</label></td>
							<td align="left"><input class="gk-input-txt" type="password" name="gk_rpassword" value="{$gk_rpassword|escape:'htmlall':'UTF-8'}" /></td>
						</tr>
						<tr align="center">
							<td><label class="font2">{l s='Repeat password' mod='globkurier'}:</label></td>
							<td align="left"><input class="gk-input-txt" type="password" name="gk_rpassword2" value="{$gk_rpassword2|escape:'htmlall':'UTF-8'}" /></td>
						</tr>
						<tr align="center">
							<td><label class="font2">{l s='Type' mod='globkurier'}:</label></td>
							<td align="left">
								<p style="width:100%; text-align: left;">
									<input type="radio" name="gk_type" value="1" {if $gk_type eq 1}checked{else}{/if} /><span class="font3">{l s='Company' mod='globkurier'}</span>
				   					<input type="radio" name="gk_type" value="2" {if $gk_type eq 2}checked{else}{/if} /><span class="font3">{l s='Individual' mod='globkurier'}</span>
				   				</p>
							</td>
						</tr>
							<tr align="center" class="gk-box-company" style="display:none">
								<td><label class="font2">{l s='Company name' mod='globkurier'}:</label></td>
								<td align="left"><input class="gk-input-txt" type="text" name="gk_company" value="{$gk_company|escape:'htmlall':'UTF-8'}" /></td>
							</tr>
							<tr align="center" class="gk-box-company" style="display:none">
								<td><label class="font2">{l s='VAT ID' mod='globkurier'}:</label></td>
								<td align="left"><input class="gk-input-txt" type="text" name="gk_nip" value="{$gk_nip|escape:'htmlall':'UTF-8'}" /></td>
							</tr>
						<tr align="center">
							<td><label class="font2">{l s='Name' mod='globkurier'}:</label></td>
							<td align="left"><input class="gk-input-txt" type="text" name="gk_name" value="{$gk_name|escape:'htmlall':'UTF-8'}" /></td>
						</tr>
						<tr align="center">
							<td><label class="font2">{l s='Surname' mod='globkurier'}:</label></td>
							<td align="left"><input class="gk-input-txt" type="text" name="gk_surname" value="{$gk_surname|escape:'htmlall':'UTF-8'}" /></td>
						</tr>
						<tr align="center">
							<td><label class="font2">{l s='Street' mod='globkurier'}:</label></td>
							<td align="left"><input class="gk-input-txt" type="text" name="gk_street" value="{$gk_street|escape:'htmlall':'UTF-8'}" /></td>
						</tr>
						<tr align="center">
							<td><label class="font2">{l s='Home number' mod='globkurier'}:</label></td>
							<td align="left"><input class="gk-input-txt" type="text" name="gk_house" value="{$gk_house|escape:'htmlall':'UTF-8'}" /></td>
						</tr>
						<tr align="center">
							<td><label class="font2">{l s='Local number' mod='globkurier'}:</label></td>
							<td align="left"><input class="gk-input-txt" type="text" name="gk_local" value="{$gk_local|escape:'htmlall':'UTF-8'}" /></td>
						</tr>
						<tr align="center">
							<td><label class="font2">{l s='City' mod='globkurier'}:</label></td>
							<td align="left"><input class="gk-input-txt" type="text" name="gk_city" value="{$gk_city|escape:'htmlall':'UTF-8'}" /></td>
						</tr>
						<tr align="center">
							<td><label class="font2">{l s='Postal code' mod='globkurier'}:</label></td>
							<td align="left"><input class="gk-input-txt" type="text" name="gk_zip" value="{$gk_zip|escape:'htmlall':'UTF-8'}" /></td>
						</tr>
						<tr align="center">
							<td><label class="font2">{l s='Phone' mod='globkurier'}:</label></td>
							<td align="left"><input class="gk-input-txt" type="text" name="gk_phone" value="{$gk_phone|escape:'htmlall':'UTF-8'}" /></td>
						</tr>
						<tr align="center">
							<td COLSPAN=2 >&nbsp;</td>
						</tr>
						<tr align="center">
							<td COLSPAN=2 ><input type="submit" name="gk_register" value="{l s='Register' mod='globkurier'}" class="gk-custom-button" /></td>
						</tr>
					</table>
 				</form>
 			</fieldset>
 		</td>
 	</tr>
 </table>
 {/if}
