{*
*  Riskified payments security module for Prestashop. Riskified reviews, approves & guarantees transactions you would otherwise decline.
*
*  @author    riskified.com <support@riskified.com>
*  @copyright 2013-Now riskified.com
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Riskified 
*}
<div class="riskified-text">
	<div>
		<img src="../modules/riskified/img/riskified-logo.png" width="200" height="80">
	</div>
	<div id="riskified-desc">
		{l s='Riskified is a turnkey risk management solution using proprietary technology to review, approve and guarantee transactions, helping you grow your business. All Riskified approved transactions carry a 100% chargeback guarantee, allowing you to sell with confidence.' mod='riskified'}

		<h1 class="riskified-h1">Why choose Riskified?</h1>
		<ul id="riskified-ul">
			<li><b>{l s='Chargeback Insurance' mod='riskified'}:</b>{l s=' We offer a 100%% chargeback guarantee on every order we approve' mod='riskified'}.</li>
			<li><b>{l s='Simple and transparent pricing' mod='riskified'}:</b>{l s=' Pay only when we approve a transaction' mod='riskified'}</li>
			<li><b>{l s='Increase revenue' mod='riskified'}:</b>{l s=' Gain incremental sales by sending Riskified high-risk transactions ' mod='riskified'}</li>
			<li><b>{l s='Flexible Plans' mod='riskified'}:</b>{l s=' We offer a wide variety of payment plans to suit your shop\'s needs. Whether you submit all your orders, turn to us for a select few, or during key sales windows, we’ve got you covered.' mod='riskified'}</li>
			<li><b>{l s='Advanced Analytics' mod='riskified'}:</b>{l s=' Gain insights about your transactions using our dashboard and order data tool. You can easily view risk parameters and make better decisions.' mod='riskified'}</li>
		</ul>
		<h2 class="riskified-h2">{l s='Installation, fraud screening and decline analytics are free' mod='riskified'}.</h2>
		<a class="riskified-button" style="margin-bottom:30px" href="http://app.riskified.com" target="_blank">Go to Riskified App</a>
		<fieldset>
			<legend>{$riskified_api_settings|escape:'htmlall':'UTF-8'}</legend>
			<div id="module_configuration">
				<form action="{$post_action|escape:'htmlall':'UTF-8'}" method="post" name="form_configuration" id="form_configuration">
					<label style="width:280px;text-align:left;">Your Riskified Shop Domain:</label>
					<div class="margin-form">
						<input type="text" style="width:350px" name="shop_domain" value="{$riskified_shop_domain|escape:'htmlall':'UTF-8'}"/>
					</div>
					<label style="width:280px;text-align:left;">Your Authentication Token:</label>
					<div class="margin-form">
						<input type="text" style="width:350px" name="auth_token" value="{$riskified_auth_token|escape:'htmlall':'UTF-8'}"/>
					</div>
					<div class="margin-form" id="riskified_mode">
						<label for="riskified_mode">Mode:</label>
						<input type="radio" name="riskified_mode" value="1" style="vertical-align: middle;" {if $riskified_production_mode eq '1'} checked="checked" {/if}/>
						<span>Production</span>
						<input type="radio" name="riskified_mode" value="0" style="vertical-align: middle;" {if $riskified_production_mode neq '1'} checked="checked" {/if}/>
						<span>Sandbox</span>
					</div>
					<div class="clear">&nbsp;</div>
					<center><input type="submit" name="submitSettings" value="Save" class="button" /></center>
				</form>
			</div>
		</fieldset>
	</div>
</div>

