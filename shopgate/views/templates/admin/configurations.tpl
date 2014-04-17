{*
* Shopgate GmbH
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file AFL_license.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/AFL-3.0
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to interfaces@shopgate.com so we can send you a copy immediately.
*
* @author Shopgate GmbH, Schloßstraße 10, 35510 Butzbach <interfaces@shopgate.com>
* @copyright  Shopgate GmbH
* @license   http://opensource.org/licenses/AFL-3.0 Academic Free License ("AFL"), in the version 3.0
*}

<link rel='stylesheet' type='text/css' href='//fonts.googleapis.com/css?family=Open+Sans:400,600&subset=latin,latin-ext'/>
<link rel="stylesheet" type="text/css" href="{$mod_dir}assets/MyFontsWebfontsKit.css">
<link rel="stylesheet" type="text/css" href="{$mod_dir}assets/configurations.css">

<div id="shopgateTeaser">

	<div id="shopgateTeaserHeader">
		<div>
			<div class="logo">
				<img src="{$mod_dir}img/shopgate_logo.png"/>
			</div>
			<div class="devices">
				<img src="{$mod_dir}img/devices.png"/>
			</div>
			<div class="register">
				<a href="{$offer_url}" target="_blank" class="register">{l s='Jetzt registrieren' mod='shopgate'}</a>
			</div>
		</div>
	</div>

	<div id="shopgateTeaserContent">

		<div id="shopgateTeaserSidebar">
			<h3>{l s='Empfohlen von Prestashop!' mod='shopgate'}</h3>
			<ul>
				<li>{l s='Mobile Website' mod='shopgate'}</li>
				<li>{l s='iPhone App' mod='shopgate'}</li>
				<li>{l s='iPad App' mod='shopgate'}</li>
				<li>{l s='Android App' mod='shopgate'}</li>
				<li>{l s='Android Tablet App' mod='shopgate'}</li>
				<li>{l s='200+ Features' mod='shopgate'}</li>
			</ul>
			<a href="#" class="video">
				<img src="{$mod_dir}img/video_prev.png"/>
			</a>
		</div>

		<div id="shopgateTeaserMain">
			<h3>{l s='Shopgate - Mobile Commerce für Prestashop' mod='shopgate'}</h3>

			<p>{l s='Mit Shopgate können Sie Ihre Produkte schnell und einfach auch über mobile Geräte verkaufen. Wir erstellen für Sie einen mobil optimierten Webshop und innovative Shopping-Apps mit zahlreichen Features. Steigern Sie durch gezieltes Marketing das Interesse des Kunden und somit Ihren Umsatz' mod='shopgate'}</p>

			<img class="contentImage" src="{$mod_dir}img/content_image.png"/>

			<h4>{l s='Ihre Vorteile mit Shopgate:' mod='shopgate'}</h4>
			<ul>
				<li>{l s='Touch-optimiert' mod='shopgate'}</li>
				<li>{l s='Übersichtliche Navigation' mod='shopgate'}</li>
				<li>{l s='Hohe Conversion-Rate' mod='shopgate'}</li>
				<li>{l s='Aktive Conversion-Optimierung' mod='shopgate'}</li>
				<li>{l s='SEO Optimiert' mod='shopgate'}</li>
				<li>{l s='Push Marketing' mod='shopgate'}</li>
				<li>{l s='Barcode & QR-Scanner' mod='shopgate'}</li>
			</ul>

			<div class="register">
				<a href="{$offer_url}" target="_blank" class="register">{l s='Jetzt registrieren' mod='shopgate'}</a>
			</div>
			<div class="registerText">
				{l s='Haben Sie noch Fragen?' mod='shopgate'}<br/>
				{l s='Rufen Sie uns an: 06033 / 7470-100' mod='shopgate'}
			</div>
		</div>

	</div>


</div>


<div style="display:none">
	<h2>{l s='Shopgate' mod='shopgate'}</h2>

	<p>
		<img src="{$mod_dir}img/logo_web.png"/>
	</p>

	<p>
		{l s='Create fast and easy to own mobile shop. Shopgate developed for you from 19,- EUR a mobile web page and real apps for iPhone, Android, iPad and other mobile systems. About the module, products and inventories with the mobile shop and orders automatically synchronized from the mobile shop in your store Presto transfer. For more information, see' mod='shopgate'}
		<a href=https://www.shopgate.com/fr/prestashop_offer" target="_blank">www.shopgate.com</a>
	</p>
</div>
<p style="clear: both;">&nbsp;</p>

<script src="../js/jquery/jquery-colorpicker.js" type="text/javascript"></script>
<script type="text/javascript">
	{literal}
	function shopgate_settings_toggle_server(obj) {
		if ($(obj).val() == 'custom')
			$('#shopgate_server').slideDown();
		else
			$('#shopgate_server').slideUp();
	}
	{/literal}
</script>

<form method="post" action="">
	<fieldset>
		<legend><img title="" alt="" src="{$mod_dir}logo.gif">{l s='Configuration' mod='shopgate'}</legend>

		<h2>{l s='Info' mod='shopgate'}</h2>

		<label>{l s='API URL' mod='shopgate'}</label>

		<div class="margin-form">
			<input type="text" value="{$api_url|escape:'htmlall':'UTF-8'}" readonly="readonly" size="60" onclick="$(this).select();"
				   style="background-color: #EFEFEF;"/>

			<p>{l s='Use this URL in shopgate merchant settings' mod='shopgate'}</p>
		</div>

		<label>{l s='Currency' mod='shopgate'}</label>

		<div class="margin-form">
			<select name="configs[currency]">
				{foreach from=$currencies item=currency}
					<option value="{$currency.iso_code|@strtoupper}"
							{if $currency.iso_code|@strtoupper == $configs.currency}selected="selected"{/if}>{$currency.name|escape:'htmlall':'UTF-8'}</option>
				{/foreach}
			</select>
		</div>

		<h2>{l s='Basic' mod='shopgate'}</h2>

		<label>{l s='Customer number' mod='shopgate'}</label>

		<div class="margin-form">
			<input type="text" name="configs[customer_number]" value="{$configs.customer_number|escape:'htmlall':'UTF-8'}" size="4"/>
		</div>
		<label>{l s='Shop number' mod='shopgate'}</label>

		<div class="margin-form">
			<input type="text" name="configs[shop_number]" value="{$configs.shop_number|escape:'htmlall':'UTF-8'}" size="4"/>
		</div>
		<label>{l s='Api key' mod='shopgate'}</label>

		<div class="margin-form">
			<input type="text" name="configs[apikey]" value="{$configs.apikey|escape:'htmlall':'UTF-8'}" size="20"/>
		</div>
		<label>{l s='Language' mod='shopgate'}</label>

		<div class="margin-form">
			{html_options name='configs[language]' options=$langs selected={$configs.language|escape:'htmlall':'UTF-8'}}
		</div>
		<label>{l s='Default shipping service' mod='shopgate'}:</label>

		<div class="margin-form">
			{html_options name='settings[SHOPGATE_SHIPPING_SERVICE]' options=$shipping_service_list selected={$settings['SHOPGATE_SHIPPING_SERVICE']|escape:'htmlall':'UTF-8'}}
		</div>


		<h2>{l s='Server' mod='shopgate'}</h2>

		<label>{l s='Server' mod='shopgate'}</label>

		<div class="margin-form">
			{html_options name='configs[server]' options=$servers onchange="shopgate_settings_toggle_server(this);" selected={$configs.server|escape:'htmlall':'UTF-8'}}
		</div>
		<div id="shopgate_server" {if $configs.server !='custom'}style="display:none;"{/if}>
			<label>{l s='Custom API URL' mod='shopgate'}</label>

			<div class="margin-form">
				<input type="text" name="configs[api_url]" value="{$configs.api_url|escape:'htmlall':'UTF-8'}" size="40"/>
			</div>
		</div>
		<h2>{l s='Enable' mod='shopgate'}</h2>

		<label>{l s='Minimum quantity check' mod='shopgate'}</label>

		<div class="margin-form">
			<label class="t"><input type="radio" value="1"
									name="settings[SHOPGATE_MIN_QUANTITY_CHECK]"{if $settings.SHOPGATE_MIN_QUANTITY_CHECK} checked="checked"{/if}/>
				<img title="{l s='Enabled' mod='shopgate'}" alt="{l s='Enabled' mod='shopgate'}" src="../img/admin/enabled.gif"></label>
			<label class="t"><input type="radio" value="0"
									name="settings[SHOPGATE_MIN_QUANTITY_CHECK]"{if !$settings.SHOPGATE_MIN_QUANTITY_CHECK} checked="checked"{/if}/>
				<img title="{l s='Disabled' mod='shopgate'}" alt="{l s='Disabled' mod='shopgate'}" src="../img/admin/disabled.gif"></label>
		</div>
		<label>{l s='Out of stock check' mod='shopgate'}</label>

		<div class="margin-form">
			<label class="t"><input type="radio" value="1"
									name="settings[SHOPGATE_OUT_OF_STOCK_CHECK]"{if $settings.SHOPGATE_OUT_OF_STOCK_CHECK} checked="checked"{/if}/>
				<img title="{l s='Enabled' mod='shopgate'}" alt="{l s='Enabled' mod='shopgate'}" src="../img/admin/enabled.gif"></label>
			<label class="t"><input type="radio" value="0"
									name="settings[SHOPGATE_OUT_OF_STOCK_CHECK]"{if !$settings.SHOPGATE_OUT_OF_STOCK_CHECK} checked="checked"{/if}/>
				<img title="{l s='Disabled' mod='shopgate'}" alt="{l s='Disabled' mod='shopgate'}" src="../img/admin/disabled.gif"></label>
		</div>


		<h2>{l s='Mobile site' mod='shopgate'}</h2>

		<label>{l s='Accumulative forwarding' mod='shopgate'}</label>

		<div class="margin-form">
			<label class="t"><input type="radio" value="1"
									name="configs[enable_default_redirect]"{if $configs.enable_default_redirect} checked="checked"{/if}/>
				<img title="{l s='Enabled' mod='shopgate'}" alt="{l s='Enabled' mod='shopgate'}" src="../img/admin/enabled.gif"></label>
			<label class="t"><input type="radio" value="0"
									name="configs[enable_default_redirect]"{if !$configs.enable_default_redirect} checked="checked"{/if}/>
				<img title="{l s='Disabled' mod='shopgate'}" alt="{l s='Disabled' mod='shopgate'}" src="../img/admin/disabled.gif"></label>
		</div>
		<br/>
		<label>{l s='Alias' mod='shopgate'}</label>

		<div class="margin-form">
			<input type="text" name="configs[alias]" value="{$configs.alias|escape:'htmlall':'UTF-8'}" size="16"/>
		</div>
		<label>{l s='CName' mod='shopgate'}</label>

		<div class="margin-form">
			<input type="text" name="configs[cname]" value="{$configs.cname|escape:'htmlall':'UTF-8'}" size="16"/>
		</div>

		<center><input class="button" type="submit" value="{l s='Save' mod='shopgate'}" name="saveConfigurations"></center>
	</fieldset>