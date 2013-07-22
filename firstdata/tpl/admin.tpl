<link href="{$module_dir}css/firstdata.css" rel="stylesheet" type="text/css">
<img src="{$firstdata_tracking|escape:'htmlall':'UTF-8'}" alt="" style="display: none;"/>
<div class="firstdata-wrap">
	{$firstdata_confirmation}
	<div class="firstdata-header">
		<a href="https://fdsnap.secure.force.com/gssb/prestashop" target="_blank"><img src="{$module_dir}img/logo.png" alt="First Data" class="firstdata-logo" /></a>
		<span class="firstdata-intro">{l s='Online Payment Processing' mod='firstdata'}<br />
		{l s='Fast - Secure - Reliable' mod='firstdata'}</span>
		<a href="https://fdsnap.secure.force.com/gssb/prestashop" target="_blank" class="firstdata-create-btn">{l s='Create an Account Now!' mod='firstdata'}</a>
	</div>
	<div class="firstdata-content">
		<div class="firstdata-half L">
			<h3>{l s='First Data offers the following benefits' mod='firstdata'}</h3>
			<ul>
				<li><strong>{l s='Increase customer payment options' mod='firstdata'}</strong><br />
				{l s='Visa®, MasterCard®, Diners Club®, American Express®, Discover® Network and JCB®, plus debit, gift cards and more' mod='firstdata'}</li>
				<li><strong>{l s='Help to improve cash flow' mod='firstdata'}</strong><br />
				{l s='Receive funds quickly from the bank of your choice' mod='firstdata'}</li>
				<li><strong>{l s='Enhanced security' mod='firstdata'}</strong><br />
				{l s='Multiple firewalls, encryption protocols and fraud protection' mod='firstdata'}</li>
				<li><strong>{l s='One-source solution' mod='firstdata'}</strong><br />
				{l s='Convenience of one invoice, one set of reports and one 24/7 customer service contact' mod='firstdata'}</li>
			</ul>
		</div>
		<div class="firstdata-half R">
			<h3>{l s='FREE First Data Global Gateway e4&#8480;' mod='firstdata'}<br />
			{l s='(Value of $400)' mod='firstdata'} <strong>*</strong></h3>
			<ul>
				<li>{l s='Simple, secure and reliable solution to process online payments' mod='firstdata'}</li>
				<li>{l s='Virtual Terminal' mod='firstdata'}</li>
				<li>{l s='Recurring Billing' mod='firstdata'}</li>
				<li>{l s='24/7/365 customer support' mod='firstdata'}</li>
				<li>{l s='Ability to perform full or partial refunds' mod='firstdata'}</li>
			</ul>
			<p class="firstdata-note"><strong>*</strong> {l s='New merchant account required and subject to credit approval. The free First Data Global Gateway e4&#8480; will be accessed through log in information provided via email within 48 hours of credit approval. Monthly fees for First Data Global Gateway e4&#8480; will apply.' mod='firstdata'}</p>
		</div>
		<div class="firstdata-full">
			<h3>{l s='Accept payments in the United States using all major credit cards' mod='firstdata'}</h3>
			<p><img src="{$module_dir}img/cc.png" alt="{l s='All majors credit cards' mod='firstdata'}" class="firstdata-cc" /><strong>{l s='For transactions in US Dollars (USD) only' mod='firstdata'}</strong><br />
			{l s='Call 888-368-4284 if you have any questions or need more information!' mod='firstdata'}</p>
		</div>
	</div>

{if !$firstdata_ssl}
	<div class="warn"><strong>{l s='SSL is not active on your shop.' mod='firstdata'}</strong><br/>
	{l s='We highly recommend you to enable SSL on your shop. Most customers will not place their order if SSL is not enabled.' mod='firstdata'}</div>
{/if}
	<form action="{$firstdata_form|escape:'htmlall':'UTF-8'}" id="firstdata-configuration" method="post">
		<fieldset>
			<legend><img src="{$module_dir}img/icon-config.gif" alt="" />{l s='Configuration' mod='firstdata'}</legend>
			<div class="firstdata-half L">
				<p class="MB10">{l s='In order to use this module, please fill out the form with the credentials provided to you by First Data' mod='firstdata'}</p>
				<label for="firstdata_key_id">{l s='API Access Key ID:' mod='firstdata'}</label>
				<div class="margin-form">
					<input type="text" class="text" name="firstdata_key_id" id="firstdata_key_id" value="{$firstdata_key_id|escape:'htmlall':'UTF-8'}" /> <sup>*</sup>
				</div>
				<label for="firstdata_key_hmac">{l s='API Access HMAC Key:' mod='firstdata'}</label>
				<div class="margin-form">
					<input type="password" class="text" name="firstdata_key_hmac" id="firstdata_key_hmac" value="{$firstdata_key_hmac|escape:'htmlall':'UTF-8'}" /> <sup>*</sup>
				</div>
				<label for="firstdata_gateway_id">{l s='Gateway ID:' mod='firstdata'}</label>
				<div class="margin-form">
					<input type="text" class="text" name="firstdata_gateway_id" id="firstdata_gateway_id" value="{$firstdata_gateway_id|escape:'htmlall':'UTF-8'}" /> <sup>*</sup>
				</div>
				<label for="firstdata_password">{l s='Password:' mod='firstdata'}</label>
				<div class="margin-form">
					<input type="password" class="text" name="firstdata_password" id="firstdata_password" value="{$firstdata_password|escape:'htmlall':'UTF-8'}" /> <sup>*</sup>
				</div>
				<div class="margin-form">
					<input type="submit" class="button" name="submitFirstData" value="{l s='Save' mod='firstdata'}" />
				</div>
				<span class="small"><sup>*</sup> {l s='Required Fields' mod='firstdata'}</span>
			</div>
			<div class="firstdata-half R">
				<h4>{l s='How to get your First Data credentials?' mod='firstdata'}</h4>
				<ol>
					<li><p>{l s='Contact First Data directly to' mod='firstdata'} <a href="http://www.empsebiz.com/prestashop/" target="_blank">{l s='apply for your First Data Global Gateway account.' mod='firstdata'}</a></p></li>
					<li><p><a href="https://globalgatewaye4.firstdata.com/" target="_blank">{l s='Login to your First Data Global Gateway e4 account.' mod='firstdata'}</a></p></li>
				    <li><p>{l s='From the main navigation click the Administration tab' mod='firstdata'}</p></li>
				    <li><p>{l s='In the main Administration screen, click the Terminals tab on the left (under the First Data logo)' mod='firstdata'}</p></li>
				    <li><p>{l s='Select the terminal that includes ECOMM by clicking on it.' mod='firstdata'}</p></li>
				    <li><p>{l s='In their terminals manager, make note of your Gateway ID for use in your Shop settings.' mod='firstdata'}</p></li>
				    <li><p>{l s='Click the API Access from the terminal manager navigation.' mod='firstdata'}</p></li>
				    <li><p>{l s='Click the Generate New Key link to get a new HMAC key' mod='firstdata'}</p></li>
				    <li><p>{l s='Copy the Key ID, a 3-5 digit number and the newly created HMAC key for use in your Shop settings.' mod='firstdata'}</p></li>
				</ol>
			</div>
		</fieldset>
	</form>
</div>