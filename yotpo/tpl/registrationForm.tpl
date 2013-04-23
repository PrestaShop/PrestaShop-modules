<div class="y-wrapper">
	<div class="y-side-box">
		<div class="y-side-header">{l s='Yotpo makes it easy to generate beautiful reviews for your products. These in turn lead to higher sales and happier customers.' mod='yotpo'}</div>
		<hr />
		<div class="row-fluid y-features-list text-shadow">
			<ul>
				<li><i class="y-side-icon conversation-rate"></i>{l s='Increase conversion rate' mod='yotpo'}</li>
				<li><i class="y-side-icon multi-languages"></i>{l s='Multi languages' mod='yotpo'}</li>
				<li><i class="y-side-icon forever-free"></i>{l s='Forever free' mod='yotpo'}</li>
				<li><i class="y-side-icon social-engagement"></i>{l s='Increase social engagement' mod='yotpo'}</li>
				<li><i class="y-side-icon plug-play"></i>{l s='Plug &amp; play installation' mod='yotpo'}</li>
				<li><i class="y-side-icon full-customization"></i>{l s='Full customization' mod='yotpo'}</li>
				<li><i class="y-side-icon analytics"></i>{l s='Advanced analytics' mod='yotpo'}</li>
				<li><i class="y-side-icon seo"></i>{l s='SEO capabilities' mod='yotpo'}</li>
			</ul>
		</div>
	</div>
	<div class="y-white-box">
		<form action="{$yotpo_action|escape:'htmlall':'UTF-8'}" method="post">
			<div class="y-page-header"><i class="y-logo"></i>{l s='Create your Yotpo account' mod='yotpo'}</div>
			<fieldset id="y-fieldset">
				<div class="y-header">{l s='Generate more reviews, more engagement, and more sales.' mod='yotpo'}</div>
				<div class="y-label">{l s='Email address:' mod='yotpo'}</div>
				<div class="y-input"><input type="text" name="yotpo_user_email" value="{$yotpo_email|escape:'htmlall':'UTF-8'}" /></div>
				<div class="y-label">{l s='Name' mod='yotpo'}</div>
				<div class="y-input"><input type="text" name="yotpo_user_name" value="{$yotpo_userName|escape:'htmlall':'UTF-8'}" /></div>
				<div class="y-label">{l s='Password' mod='yotpo'}</div>
				<div class="y-input"><input type="password" name="yotpo_user_password" /></div>
				<div class="y-label">{l s='Confirm password' mod='yotpo'}</div>
				<div class="y-input"><input type="password" name="yotpo_user_confirm_password" /></div>
			</fieldset>
			<div class="y-footer"><input type="submit" name="yotpo_register" value="{l s='Register' mod='yotpo'}" class="y-submit-btn" /></div>
		</form>
		<form action="{$yotpo_action|escape:'htmlall':'UTF-8'}" method="post">
			<div class="y-footer">{l s='Already using Yotpo?' mod='yotpo'} <input type="submit" name="log_in_button" value="click here" class="y-already-logged-in" /></div>
		</form>
	</div>
</div>