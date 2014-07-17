{**
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2014 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *
 *}


<div class="row secure-account">
	<div class="col-lg-12">
		<p>
			{l s='In order to create a Secure Account with PrestaStats, please complete the fields in the Settings panel below :' mod='prestastats'}<br /> {l s='By clicking the "Save" button you are creating secure connection details to your store. PrestaStats signup only begins when you click on' mod='prestastats'} <br /> {l s='"Activate your New PrestaStats Account" in the Register panel below. If you already have a PrestaStats account you can create a new shop within your account when you login, with the details in the Settings panel below.' mod='prestastats'}
			<br /><br /><br />
		</p>			
	</div>
</div>
<div class="row">
	<form method="post" id="prestastats-form" action="{$dfl.action|escape:'htmlall':'UTF-8'}">
		<fieldset>
			<legend><img src="../modules/prestastats/img/icon.png" width="16" height="16" alt="PrestaStats Settings" title="" /> {l s='Settings' mod='prestastats'}</legend>
			
			<label class="col-lg-2">{l s='Shop Reference Name' mod='prestastats'}</label>
			<div class="margin-form col-lg-8">
				<input type="text" name="PRESTASTATS_user" id="PRESTASTATS_user" value="{$username|escape:'htmlall':'UTF-8'}" /> 
				<span class="hint" name="help_box">{l s='This will be used to identify your Shop within your PrestaStats Dashboard.' mod='prestastats'}<span class="hint-pointer">&nbsp;</span></span><sup>*</sup>
			</div>
			<div class="clearfix"></div>
		        
			<label class="col-lg-2">{l s='Security Key' mod='prestastats'}</label>
			<div class="margin-form col-lg-8">
				<input type="text" name="PRESTASTATS_api" id="PRESTASTATS_api" value="{$api|escape:'htmlall':'UTF-8'}" />&nbsp;<sup>*</sup>&nbsp;&nbsp;
				<a href="#reg-form" class="underlined" onclick="randomString();">{l s='Generate API Key' mod='prestastats'}</a>
				<span class="hint" name="help_box">{l s='This will be used to create a secure encrypted connection for your Store. eg: 25F135CB94FE8B65ECCF0BD972672F73' mod='prestastats'} <br /> {l s='You can click the link to "Generate API Key" for you, or type your own one in.' mod='prestastats'} <br /> {l s='You do not need to remember it because it is only used during this setup.' mod='prestastats'}<span class="hint-pointer">&nbsp;</span></span>
			</div>
			<div class="clearfix"></div>
			
			<label class="col-lg-2">{l s='Password' mod='prestastats'}</label>
			<div class="margin-form col-lg-8">
				<input type="text" name="PRESTASTATS_password" id="PRESTASTATS_password" value="{$password|escape:'htmlall':'UTF-8'}" />&nbsp;<sup>*</sup>&nbsp;&nbsp;
				<a href="#reg-form" class="underlined" onclick="randomPass();">{l s='Generate Random Password' mod='prestastats'}</a>
				<span class="hint" name="help_box">{l s='This is used as an additional level of security to connect to your PrestaStats account, e.g. SecretPassword$123' mod='prestastats'}<span class="hint-pointer">&nbsp;</span></span> <br/>
			</div>
			<div class="clearfix"></div>

			<div class="col-lg-2"></div>
			<div class="margin-form col-lg-8">
				<input type="submit" id="_form_submit_btn" value="Save" name="submitprestastats" class="button"> <br/><br/>
				{l s='When you Click on Save, your details above are saved to your module. The PrestaStats Signup begins once you click on "Activate your New PrestaStats Account" button below in the Register Panel.' mod='prestastats'}
				<div class="small"><sup>*</sup> {l s='Required fields' mod='prestastats'}</div>
			</div>

		</fieldset>
	</form><br /><br />
</div>	