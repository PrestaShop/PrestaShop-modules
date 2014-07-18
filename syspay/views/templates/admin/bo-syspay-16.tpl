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
*  @version  Release: $Revision: 7732 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}



<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,600' rel='stylesheet' type='text/css'>
<link rel="stylesheet" media="all" type="text/css" href="{$module_dir|escape:'htmlall':'UTF-8'}css/syspay16.css" />
<script>

var texts = [	
	"{l s='This is often useful for merchants who have a delayed order fulfillment process. You can capture funds once the order has been shipped.' mod='syspay'}",
	"{l s='Choose the order status that will trigger the capture of funds for a deferred payment.' mod='syspay'}",
	"{l s='1-Click payment allows your customers to make purchases with a single click, with the payment information needed to complete the purchase already entered by your customer previously.' mod='syspay'}",
	"{l s='Website ID is provided by SysPay and it is not mandatory while in TRIAL mode' mod='syspay'}"
	];

</script>
<script type="text/javascript" src="{$module_dir|escape:'htmlall':'UTF-8'}js/jquery.qtip-1.0.0-rc3.min.js"></script>
<script type="text/javascript" src="{$module_dir|escape:'htmlall':'UTF-8'}js/admin.js"></script>
<div class="panel" style="border: 0; background: white;">
    <div id="syspay_wrapper">
        <div id="syspay_header">
            <img src="{$module_dir|escape:'htmlall':'UTF-8'}img/logo_bo.jpg" />
            <h1>{l s='Increase your sales!' mod='syspay'}</h1>
            <div style="clear: both"></div>
        </div>
        <div id="syspay_adv">
            <div id="bloc-left">
                <div id="adv1" class="adv">
                    <span class="adv-title">{l s='Easy & Immediate' mod='syspay'}</span>
                    <span class="adv-subtitle">{l s='No merchant account needed' mod='syspay'}</span>
                </div>
                <div id="adv2" class="adv">
                    <span class="adv-title">{l s='International' mod='syspay'}</span>
                    <span class="adv-subtitle">{l s='21 payment options 24 currencies' mod='syspay'}</span>
                </div>
                <div id="adv3" class="adv">
                    <span class="adv-title">{l s='Secure' mod='syspay'}</span>
                    <span class="adv-subtitle">{l s='Advanced fraud prevention solution' mod='syspay'}</span>
                </div>
                <div id="adv4" class="adv">
                    <span class="adv-title">{l s='Customizable' mod='syspay'}</span>
                    <span class="adv-subtitle">{l s='Your design and logo' mod='syspay'}</span>
                </div>
            </div>
            <div id="bloc-right">
                <div id="adv-trial">
                    <span class="trial-title">{l s='FREE' mod='syspay'}</span><br />
                    <span class="trial-title">{l s='Trial' mod='syspay'}</span><br />
                    <span class="trial-subtitle">{l s='(up to 2500€ sales)' mod='syspay'}</span><br />
                    <a id="trial-activate" href="https://app.syspay.com/register/prestashop" target="_blank">
                        {l s='Activate Now' mod='syspay'}
                    </a>
                    <span class="trial-subtitle"><a style="color: white" target="_blank" href="http://app.syspay.com/landing/prestashop_partner_50">{l s='Developers: earn 20€ cashback for each merchant activation' mod='syspay'}</a></span><br />
                </div>
            </div>
            <div style="clear: both"></div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="panel">
                    <h3>{l s='Technical checks' mod='syspay'}</h3>
                    {if $checks.total == 'ko'}
                        <div class="alert alert-warning">
                            {l s='Unfortunately, at leat one issue is preventing you from using Syspay. Please fix the issue and reload the page.' mod='syspay'}
                        </div>
                    {else}
                        <div class="alert alert-success">
                            {l s='You are now ready to make your first sale with Syspay.' mod='syspay'}
                        </div>
                    {/if}
                    <ul>
                        <li><img src="{$module_dir|escape:'htmlall':'UTF-8'}img/{$checks.curl}.gif" /> {l s='Enable the cURL extension'  mod='syspay'}</li>
                        <li><img src="{$module_dir|escape:'htmlall':'UTF-8'}img/{$checks.json}.gif" /> {l s='Enable the JSON extension'  mod='syspay'}</li>
                        <li><img src="{$module_dir|escape:'htmlall':'UTF-8'}img/{$checks.php}.gif" /> {l s='PHP version greater than 5.2'  mod='syspay'}</li>
                        <li><img src="{$module_dir|escape:'htmlall':'UTF-8'}img/{$checks.settings}.gif" /> {l s='Signup for SysPay and fill in the Settings form below' mod='syspay'}</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8" style="width: 67%;">
                <div class="panel" style="margin-top: 12px;">
                    <h3>{l s='Settings' mod='syspay'}</h3>
                    <form method="post" action="{$settings.formTarget|escape:'UTF-8'}" name="syspayForm">
                        <div class="bb">
							<div class="form-group">
                            <label class="control-label col-lg-4">{l s='Mode' mod='syspay'}</label>
							<div class="col-lg-3">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" id="mode_live" name="SYSPAY_MODE" value="1" {if $settings.SYSPAY_MODE == 1}checked="checked" {/if} />
									<label for="mode_live">
										{l s='Live' mod='syspay'}
									</label>
									<input type="radio" id="mode_test" name="SYSPAY_MODE" value="0" {if $settings.SYSPAY_MODE == 0}checked="checked" {/if} />
									<label for="mode_test">
										{l s='Test' mod='syspay'}
									</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
							<div class="clearfix"></div>
							</div>
                        </div>
                        <div class="bb pt">
                            <div class="split">
                                <label class="credentials">{l s='Sandbox login' mod='syspay'}</label>
                                <input type="text" name="SYSPAY_TEST_MID" value="{$settings.SYSPAY_TEST_MID|escape:'htmlall':'UTF-8'}" />
                                <br /><br />
                                <label class="credentials">{l s='Sandbox passphrase' mod='syspay'}</label>
                                <input type="text" name="SYSPAY_TEST_SHA1_PRIVATE" value="{$settings.SYSPAY_TEST_SHA1_PRIVATE|escape:'htmlall':'UTF-8'}" />
                                <div style="clear: both"></div>
                            </div>
                            <div class="split bl">
                                <label class="credentials">{l s='LIVE login' mod='syspay'}</label>
                                <input type="text" name="SYSPAY_LIVE_MID" value="{$settings.SYSPAY_LIVE_MID|escape:'htmlall':'UTF-8'}" />
                                <br /><br />
                                <label class="credentials">{l s='LIVE passphrase' mod='syspay'}</label>
                                <input type="text" name="SYSPAY_LIVE_SHA1_PRIVATE" value="{$settings.SYSPAY_LIVE_SHA1_PRIVATE|escape:'htmlall':'UTF-8'}" />
                                <div style="clear: both"></div>
                            </div>
                            <div style="clear: both"></div>
                        </div>
                        <div class="bb pt">
							<div class="form-group">
                            <label class="control-label col-lg-4">{l s='Website ID' mod='syspay'}</label>
							<div class="col-lg-4">
								<input class="form-control fixed-width-sm" type="text" name="SYSPAY_WEBSITE_ID" value="{$settings.SYSPAY_WEBSITE_ID|escape:'htmlall':'UTF-8'}" />
							</div>
                            <span class="help" id="t3" ><sup>?</sup></span>
							</div>
                        </div>
                        <div class="bb pt">
							<div class="form-group">
							<label class="control-label col-lg-4">{l s='Check availability of funds for a transaction but delay the capture of funds until a later time' mod='syspay'}</label>
							<div class="col-lg-4">
								<span class="switch prestashop-switch fixed-width-lg">
									<input id="mode_yes" type="radio" name="SYSPAY_AUTHORIZED_PAYMENT" value="1" {if $settings.SYSPAY_AUTHORIZED_PAYMENT == 1}checked="checked" {/if} />
									<label for="mode_yes">
										{l s='Yes' mod='syspay'}
									</label>
									<input id="mode_no" type="radio" name="SYSPAY_AUTHORIZED_PAYMENT" value="0" {if $settings.SYSPAY_AUTHORIZED_PAYMENT == 0}checked="checked" {/if} />
									<label for="mode_no">
										{l s='No' mod='syspay'}
									</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
							<span class="help" id="t0" ><sup>?</sup></span>
							<div class="clearfix"></div>
							</div>
                        </div>
                        <div class="bb pt">
                            <label>{l s='Order status triggering the Capture' mod='syspay'}</label>
                            <select name="SYSPAY_CAPTURE_OS">
                                {foreach from=$states item=os}
                                    <option value="{$os.id_order_state|intval}" {if $os.id_order_state == $settings.SYSPAY_CAPTURE_OS}selected="selected"{/if}>
                                        {$os.name|escape:'htmlall':'UTF-8'}
                                    </option>
                                {/foreach}
                            </select>
                            <span class="help" id="t1" ><sup>?</sup></span>
                            <div style="clear: both"></div>
                        </div>
                        <div class="bb pt">
							<div class="form-group">
							<label class="control-label col-lg-4">{l s='Enable 1-click payments for cards' mod='syspay'}</label>
							<div class="col-lg-4">
								<span class="switch prestashop-switch fixed-width-lg">
									<input id="mode_yes_1c" type="radio" name="SYSPAY_REBILL" value="1" {if $settings.SYSPAY_REBILL == 1}checked="checked" {/if} />
									<label for="mode_yes_1c">
										{l s='Yes' mod='syspay'}
									</label>
									<input id="mode_no_1c" type="radio" name="SYSPAY_REBILL" value="0" {if $settings.SYSPAY_REBILL == 0}checked="checked" {/if} />
									<label for="mode_no_1c">
										{l s='No' mod='syspay'}
									</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
							<span class="help" id="t2" ><sup>?</sup></span>
							<div class="clearfix"></div>
							</div>
                        </div>
                        <div class="pt save">
                            <input type="submit" class="btn btn-default syspay-btn" name="submitSyspay" value="{l s='Save Settings' mod='syspay'}" />
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-2" style="width: 31.8%; padding-right: 0; margin-left: 12px; margin-top: 12px; float: left">
                <div class="panel">
                    <h3>{l s='How to test?' mod='syspay'}</h3>
                    <form method="post" action="{$settings.formTarget|escape:'UTF-8'}" name="generateCard">
                        <span style="font-weight: bold">{l s='Some suggestions on how to test a card payment:' mod='syspay'}</span><br /><br />
                        <ul>
                            <li>{l s='- You need a valid card number (valid format only, not a real card)' mod='syspay'}</li>
                        </ul><br />
                        <input type="submit" name="generate-cb" value="{l s='Generate card' mod='syspay'}" class="btn btn-default syspay-btn" />
						<br /><br />
                        <span id="cb-test">{if isset($cbtest)}{$cbtest|escape:'htmlall':'UTF-8'}{/if}</span>
						<br /><br />
                        <ul>
                            <li>{l s='- You can use any 3 digit CV2' mod='syspay'}</li>
                            <li>{l s='- If you want to test a successful payment, use a January (01) expiry month' mod='syspay'}</li>
                            <li>{l s='- If you want to test a declined payment, use any other month for expiry month' mod='syspay'}</li>
                            <li>{l s='- If you want to test a 3ds transaction (VerifiedbyVisa or MasterCard SecureCode) use 2018 as expiry year' mod='syspay'}</li>
                        </ul>
                    </form>
                </div>
            </div>
            <div class="col-lg-2" style="width: 31.8%; padding-right: 0; margin-left: 12px; margin-top: 12px; float: left;">
                <div class="panel">
                    <h3>{l s='Exports' mod='syspay'}</h3>
                    <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
                    <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
                    <script>
                        $(function() {
                            $( "#from" ).datepicker({
                                defaultDate: "+1w",
                                changeMonth: true,
                                numberOfMonths: 3,
                                onClose: function( selectedDate ) {
                                    $( "#to" ).datepicker( "option", "minDate", selectedDate );
                                }
                            });
                            $( "#to" ).datepicker({
                                defaultDate: "+1w",
                                changeMonth: true,
                                numberOfMonths: 3,
                                onClose: function( selectedDate ) {
                                    $( "#from" ).datepicker( "option", "maxDate", selectedDate );
                                }
                            });
                        });
                    </script>
                    <form action="{$settings.formTarget|escape:'UTF-8'}" name="export_syspay" target="_blank" method="post" style="margin-bottom: 30px;">
                        <h4>{l s='Payments' mod='syspay'}</h4>
                        <span for="from" style="display: inline-block; width: 50px;">{l s='From' mod='syspay'}</span>
                        <input type="text" id="from" name="from" /><br />
                        <span for="to" style="display: inline-block; width: 50px;">{l s='To' mod='syspay'}</span>
                        <input type="text" id="to" name="to" /><br /><br />
                        <input {if isset($no_payments)}disabled{/if} class="btn btn-default syspay-btn" type="submit" name="export_transactions" value="{l s='Export payments' mod='syspay'}" /><br /><br />
                        <h4>{l s='Refunds' mod='syspay'}</h4>
                        <input {if isset($no_refunds)}disabled{/if} class="btn btn-default syspay-btn" type="submit" name="export_refunds" value="{l s='Export all refunds' mod='syspay'}" />
                        <input type="hidden" name="valid_export" value="1" />
                    </form>
                </div>
            </div>
            <script>
                $('#conf a').click(function (e) {
                    e.preventDefault()
                    $(this).tab('show')
                })
            </script>
        </div>
    </div>
</div>
