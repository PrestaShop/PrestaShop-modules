<div class="authorizeaim-wrapper">
<a href="http://reseller.authorize.net/application/prestashop/" class="authorizeaim-logo" target="_blank"><img src="{$module_dir}img/logo_authorize.png" alt="Authorize.net" border="0" /></a>
<p class="authorizeaim-intro">{l s='Start accepting payments through your PrestaShop store with Authorize.Net, the pioneering provider of ecommerce payment services.  Authorize.Net makes accepting payments safe, easy and affordable.' mod='authorizeaim'}</p>
<p class="authorizeaim-sign-up">{l s='Do you require a payment gateway account? ' mod='authorizeaim'}<a href="http://reseller.authorize.net/application/prestashop/" target="_blank">{l s='Sign Up Now' mod='authorizeaim'}</a></p>
<div class="authorizeaim-content">
	<div class="authorizeaim-leftCol">
		<h3>{l s='Why Choose Authorize.Net?' mod='authorizeaim'}</h3>
		<ul>
			<li>{l s='Leading payment gateway since 1996 with 400,000+ active merchants' mod='authorizeaim'}</li>
			<li>{l s='Multiple currency acceptance' mod='authorizeaim'}</li>
			<li>{l s='FREE award-winning customer support via telephone, email and online chat' mod='authorizeaim'}</li>
			<li>{l s='FREE Virtual Terminal for mail order/telephone order transactions' mod='authorizeaim'}</li>
			<li>{l s='No Contracts or long term commitments ' mod='authorizeaim'}</li>
			<li>{l s='Additional services include: ' mod='authorizeaim'}
				<ul class="none">
					<li>{l s='- Advanced Fraud Detection Suite™' mod='authorizeaim'}</li>
					<li>{l s='- Automated Recurring Billing ™' mod='authorizeaim'}</li>
					<li>{l s='- Customer Information Manager' mod='authorizeaim'}</li>
				</ul>
			</li>
			<li>{l s='Gateway and merchant account set up available' mod='authorizeaim'}</li>
			<li>{l s='Simple setup process' mod='authorizeaim'}
		</li>
		</ul>
		<ul class="none" style = "display: inline; font-size: 13px;">
			<li><a href="http://reseller.authorize.net/application/prestashop/" target="_blank" class="authorizeaim-link">{l s='Sign up Now' mod='authorizeaim'}</a></li>
		</ul>		
	</div>
	<div class="authorizeaim-video">
		<p>{l s='Have you ever wondered how credit card payments work? Connecting a payment application to the credit card processing networks is difficult, expensive and beyond the resources of most businesses. Authorize.Net provides the complex infrastructure and security necessary to ensure secure, fast and reliable transactions. See How:' mod='authorizeaim'}</p>
		<a href="http://www.youtube.com/watch?v=8SQ3qst0_Pk" class="authorizeaim-video-btn">
			<img src="{$module_dir}img/video-screen.jpg" alt="Merchant Warehouse screencast" />
			<img src="{$module_dir}img/btn-video.png" alt="" class="video-icon" />
		</a>
	</div>
</div>

<form action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post">
	<fieldset>
		<legend>{l s='Configure your existing Authorize.Net Accounts' mod='authorizeaim'}</legend>

		{* Determine which currencies are enabled on the store and supported by Authorize.net & list one credentials section per available currency *}
		{foreach from=$currencies item='currency'}
			{if (in_array($currency.iso_code, $available_currencies))}
				{assign var='configuration_id_name' value="AUTHORIZE_AIM_LOGIN_ID_"|cat:$currency.iso_code}
 				{assign var='configuration_key_name' value="AUTHORIZE_AIM_KEY_"|cat:$currency.iso_code}
				<table>
					<tr>
						<td>
							<p>{l s='Credentials for' mod='authorizeaim'}<b> {$currency.iso_code}</b> {l s='currency' mod='authorizeaim'}</p>
							<label for="authorizeaim_login_id">{l s='Login ID' mod='authorizeaim'}:</label>
							<div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="20" id="authorizeaim_login_id_{$currency.iso_code}" name="authorizeaim_login_id_{$currency.iso_code}" value="{${$configuration_id_name}}" /></div>
							<label for="authorizeaim_key">{l s='Key' mod='authorizeaim'}:</label>
							<div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="20" id="authorizeaim_key_{$currency.iso_code}" name="authorizeaim_key_{$currency.iso_code}" value="{${$configuration_key_name}}" /></div>
						</td>
					</tr>
				<table><br />
				<hr size="1" style="background: #BBB; margin: 0; height: 1px;" noshade /><br />
			{/if}
		{/foreach}

		<label for="authorizeaim_demo_mode">{l s='Mode:' mod='authorizeaim'}</label>
		<div class="margin-form" id="authorizeaim_demo">
			<input type="radio" name="authorizeaim_demo_mode" value="0" style="vertical-align: middle;" {if !$AUTHORIZE_AIM_DEMO}checked="checked"{/if} />
			<span style="color: #080;">{l s='Production' mod='authorizeaim'}</span>
			<input type="radio" name="authorizeaim_demo_mode" value="1" style="vertical-align: middle;" {if $AUTHORIZE_AIM_DEMO}checked="checked"{/if} />
			<span style="color: #900;">{l s='Sandbox' mod='authorizeaim'}</span>
		</div>
		<label for="authorizeaim_cards">{l s='Cards* :' mod='authorizeaim'}</label>
		<div class="margin-form" id="authorizeaim_cards">
			<input type="checkbox" name="authorizeaim_card_visa" {if $AUTHORIZE_AIM_CARD_VISA}checked="checked"{/if} />
				<img src="{$module_dir}/cards/visa.gif" alt="visa" />
			<input type="checkbox" name="authorizeaim_card_mastercard" {if $AUTHORIZE_AIM_CARD_MASTERCARD}checked="checked"{/if} />
				<img src="{$module_dir}/cards/mastercard.gif" alt="visa" />
			<input type="checkbox" name="authorizeaim_card_discover" {if $AUTHORIZE_AIM_CARD_DISCOVER}checked="checked"{/if} />
				<img src="{$module_dir}/cards/discover.gif" alt="visa" />
			<input type="checkbox" name="authorizeaim_card_ax" {if $AUTHORIZE_AIM_CARD_AX}checked="checked"{/if} />
				<img src="{$module_dir}/cards/ax.gif" alt="visa" />
		</div>

		<label for="authorizeaim_hold_review_os">{l s='Order status:  "Hold for Review" ' mod='authorizeaim'}</label>
		<div class="margin-form">
			<select id="authorizeaim_hold_review_os" name="authorizeaim_hold_review_os">';
				// Hold for Review order state selection
				{foreach from=$order_states item='os'}
					<option value="{if $os.id_order_state|intval}" {((int)$os.id_order_state == $AUTHORIZE_AIM_HOLD_REVIEW_OS)} selected{/if}>
						{$os.name|stripslashes}
					</option>
				{/foreach}
			</select>
		</div>
		<br />
		<center>
			<input type="submit" name="submitModule" value="{l s='Update settings' mod='authorizeaim'}" class="button" />
		</center>
		<sub>{l s='* Subject to region' mod='authorizeaim'}</sub>
	</fieldset>
</form>
</div>