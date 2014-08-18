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
<link rel="stylesheet" type="text/css" href="{$mod_dir|escape:html:'UTF-8'}css/MyFontsWebfontsKit.css">
<link rel="stylesheet" type="text/css" href="{$mod_dir|escape:html:'UTF-8'}css/configurations.css">

<div id="shopgateTeaser">

    <div id="shopgateTeaserHeader">
        <div>
            <div class="logo">
                <img src="{$mod_dir|escape:html:'UTF-8'}img/shopgate_logo.png"/>
            </div>
            <div class="devices">
                <img src="{$mod_dir|escape:html:'UTF-8'}img/devices.png"/>
            </div>
            <div class="register">
                <a href="{$offer_url|escape:html:'UTF-8'}" target="_blank" class="register">{l s='Register now​' mod='shopgate'}</a>
            </div>
        </div>
    </div>

    <div id="shopgateTeaserContent">

        <div id="shopgateTeaserSidebar">
            <h3>{l s='Recommended by Prestashop!' mod='shopgate'}</h3>
            <ul>
                <li>{l s='Mobile Website' mod='shopgate'}</li>
                <li>{l s='iPhone App' mod='shopgate'}</li>
                <li>{l s='iPad App' mod='shopgate'}</li>
                <li>{l s='Android App' mod='shopgate'}</li>
                <li>{l s='Android Tablet App' mod='shopgate'}</li>
                <li>{l s='200+ Features' mod='shopgate'}</li>
            </ul>
            <iframe width="330" height="168" src="{$video_url|escape:'all'}" frameborder="0" allowfullscreen></iframe>
        </div>

        <div id="shopgateTeaserMain">
            <h3>{l s='Shopgate - Mobile Commerce for Prestashop' mod='shopgate'}</h3>

            <p>{l s='With Shopgate you can sell your products quickly and easily via mobile devices. We will create a mobile-optimized webshop and innovative shopping apps with numerous features. Increase your sales and the customer\'s interest through targeted marketing!' mod='shopgate'}</p>

            <img class="contentImage" src="{$mod_dir|escape:html:'UTF-8'}img/content_image.png"/>

            <h4>{l s='Your advantages with Shopgate​:' mod='shopgate'}</h4>
            <ul>
                <li>{l s='Touch-optimized​' mod='shopgate'}</li>
                <li>{l s='Easy navigation' mod='shopgate'}</li>
                <li>{l s='High Conversion Rates​' mod='shopgate'}</li>
                <li>{l s='Active Conversion Optimization​' mod='shopgate'}</li>
                <li>{l s='SEO Optimized' mod='shopgate'}</li>
                <li>{l s='Push Marketing' mod='shopgate'}</li>
                <li>{l s='Barcode & QR-Scanner' mod='shopgate'}</li>
            </ul>

            <div class="register">
                <a href="{$offer_url|escape:html:'UTF-8'}" target="_blank" class="register">{l s='Register now' mod='shopgate'}</a>
            </div>
            <div class="registerText">
                {l s='Got questions?' mod='shopgate'}<br/>
                {l s='Give us a call at​: 06033 / 7470-100' mod='shopgate'}
            </div>
        </div>

    </div>

</div>


<div style="display:none">
    <h2>{l s='Shopgate' mod='shopgate'}</h2>

    <p>
        <img src="{$mod_dir|escape:html:'UTF-8'}img/logo_web.png"/>
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
        <legend><img title="" alt="" src="{$mod_dir|escape:html:'UTF-8'}img/logo.png">{l s='Configuration' mod='shopgate'}</legend>

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
            <select name="configs[language]">
                {foreach from=$langs key=key item=name}
                    <option value="{$key|escape:html:'UTF-8'}"
                            {if $key == $configs.language}selected="selected"{/if}>{$name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <label>{l s='Default shipping service' mod='shopgate'}</label>

        <div class="margin-form">
            <select name="settings[SHOPGATE_SHIPPING_SERVICE]">
                {foreach from=$shipping_service_list key=key item=name}
                    <option value="{$key|escape:html:'UTF-8'}"
                            {if $key == $settings.SHOPGATE_SHIPPING_SERVICE}selected="selected"{/if}>{$name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>

        <label>{l s='Subscribe mobile customer to newsletter' mod='shopgate'}</label>
        <div class="margin-form">
            <label class="t"><input type="radio" value="1"
                                    name="settings[SHOPGATE_SUBSCRIBE_NEWSLETTER]"{if $settings.SHOPGATE_SUBSCRIBE_NEWSLETTER} checked="checked"{/if}/>
                <img title="{l s='Enabled' mod='shopgate'}" alt="{l s='Enabled' mod='shopgate'}" src="../img/admin/enabled.gif"></label>
            <label class="t"><input type="radio" value="0"
                                    name="settings[SHOPGATE_SUBSCRIBE_NEWSLETTER]"{if !$settings.SHOPGATE_SUBSCRIBE_NEWSLETTER} checked="checked"{/if}/>
                <img title="{l s='Disabled' mod='shopgate'}" alt="{l s='Disabled' mod='shopgate'}" src="../img/admin/disabled.gif"></label>
        </div><p style="clear: both;">&nbsp;</p>

        <h2>{l s='Server' mod='shopgate'}</h2>

        <label>{l s='Server' mod='shopgate'}</label>

        <div class="margin-form">
            <select name="configs[server]" onchange="shopgate_settings_toggle_server(this);">
                {foreach from=$servers key=key item=name}
                    <option value="{$key|escape:html:'UTF-8'}"
                            {if $key == $configs.server}selected="selected"{/if}>{$name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
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

		<h2>{l s='Category export' mod='shopgate'}</h2>
		<label>{l s='Should the root category be exported' mod='shopgate'}</label>

		<div class="margin-form">
			<label class="t"><input type="radio" value="1"
									name="settings[SHOPGATE_EXPORT_ROOT_CATEGORIES]"{if $settings.SHOPGATE_EXPORT_ROOT_CATEGORIES} checked="checked"{/if}/>
				<img title="{l s='Enabled' mod='shopgate'}" alt="{l s='Enabled' mod='shopgate'}" src="../img/admin/enabled.gif"></label>
			<label class="t"><input type="radio" value="0"
									name="settings[SHOPGATE_EXPORT_ROOT_CATEGORIES]"{if !$settings.SHOPGATE_EXPORT_ROOT_CATEGORIES} checked="checked"{/if}/>
				<img title="{l s='Disabled' mod='shopgate'}" alt="{l s='Disabled' mod='shopgate'}" src="../img/admin/disabled.gif"></label>
		</div>

        <h2>{l s='Product export' mod='shopgate'}</h2>
        <label>{l s='Description' mod='shopgate'}</label>
        <div class="margin-form">
            <select name="settings[SHOPGATE_PRODUCT_DESCRIPTION]">
                {foreach from=$product_export_descriptions key=key item=name}
                    <option value="{$key|escape:html:'UTF-8'}"
                            {if $key == $settings.SHOPGATE_PRODUCT_DESCRIPTION}selected="selected"{/if}>{$name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>

        {if $carrier_list}
            <h2>{l s='Carrier mapping' mod='shopgate'}</h2>

            {foreach from=$carrier_list key=config_key item=carrier}
                <label>{$carrier.name|escape:'htmlall':'UTF-8'}</label>
                <div class="margin-form">
                    <select name="settings[{$config_key|escape:html:'UTF-8'}]">
                        {foreach from=$shipping_service_list key=key item=name}
                            <option value="{$key|escape:html:'UTF-8'}"
                                    {if $key == $settings.$config_key}selected="selected"{/if}>{$name|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                </div>
            {/foreach}
        {/if}

        <center><input class="button" type="submit" value="{l s='Save' mod='shopgate'}" name="saveConfigurations"></center>
    </fieldset>