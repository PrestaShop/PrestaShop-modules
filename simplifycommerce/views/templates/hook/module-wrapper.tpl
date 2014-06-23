{*
 * Simplify Commerce module to start accepting payments now. It's that simple.
 *
 * Redistribution and use in source and binary forms, with or without modification, are
 * permitted provided that the following conditions are met:
 * Redistributions of source code must retain the above copyright notice, this list of
 * conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list of
 * conditions and the following disclaimer in the documentation and/or other materials
 * provided with the distribution.
 * Neither the name of the MasterCard International Incorporated nor the names of its
 * contributors may be used to endorse or promote products derived from this software
 * without specific prior written permission.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT
 * SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
 * TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
 * OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER
 * IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING
 * IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
 * SUCH DAMAGE.
 *
 *  @author    MasterCard (support@simplify.com)
 *  @version   Release: 1.0.3
 *  @copyright 2014, MasterCard International Incorporated. All rights reserved.
 *  @license   See licence.txt
 *}
<link href="{$module_dir|escape}css/style.css" rel="stylesheet" type="text/css" media="all" />
<link href="//fonts.googleapis.com/css?family=Lato:100,300,400,700,900" rel="stylesheet">
<div class="simplify-module-wrapper">
    <div class="simplify-module-header">
        <a href="https://www.simplify.com/" target="_blank" class="left">
            <img class="logo" src="//www.simplify.com/commerce/static/images/app-logo-pos.png" alt="Simplify Commerce Logo" width="150" height="64"></a>
        <div class="header-title left">
            <h1>Start accepting payments now.</h1>
            <h2>It’s that simple.</h2>
        </div>
        <a href="https://www.simplify.com/commerce/login/merchantSignup" target="_blank" class="btn right"><span>Sign up for free</span></a>
    </div>
    <div class="section">
        <div class="clearfix">
            <div class="marketing left">
                <div class="w-container features item">
                    <img class="features item icon" src="//www.simplify.com/commerce/static/images/feature_signup.jpg" alt="feature_signup.jpg">
                    <h1 class="features item h1">Easy sign up</h1>
                    <p>Click the "Sign up for free" button and become a Simplify merchant for free.</p>
                </div>
            </div>
            <div class="marketing left">
                <div class="w-container features item">
                    <img class="features item icon" src="//www.simplify.com/commerce/static/images/feature_price.jpg" alt="feature_signup.jpg">
                    <h1 class="features item h1">Simple pricing</h1>
                    <p>No setup fees.<br>No monthly fees.<br>No minimum.</p>
                </div>
            </div>
            <div class="marketing left">
                <div class="w-container features item">
                    <img class="features item icon" src="//www.simplify.com/commerce/static/images/feature_funding.jpg" alt="feature_signup.jpg">
                    <h1 class="features item h1">Two-day funding</h1>
                    <p>Deposits are made into your account in two business days for most transactions.</p>
                </div>
            </div>
        </div>
    </div>
    <div class="formContainer">
        <section class="technical-checks">
            <h2>Technical Checks</h2>
            <div class="{if $requirements['result']}conf">
                {l s='Good news! Everything looks to be in order. Start accepting credit card payments now.' mod='simplifycommerce'}
            {else}
                {l s='Unfortunately, at least one issue is preventing you from using Simplify Commerce.Please fix the issue and reload this page.' mod='simplifycommerce'}
            {/if}
            </div>

            <table cellspacing="0" cellpadding="0" class="simplify-technical">
                {foreach from=$requirements key=k item=requirement}
                    {if $k != 'result'}
                        <tr>
                            <td>
                                {if $requirement['result']}
                                    <img src="../img/admin/ok.gif" alt=""/>
                                {else}
                                    <img src="../img/admin/forbbiden.gif" alt=""/>
                                {/if}
                            </td>
                            <td>
                                {$requirement['name']|escape:'htmlall': 'UTF-8'}<br/>
                                {if !$requirement['result'] && isset($requirement['resolution'])}
                                    {Tools::safeOutput($requirement['resolution']|escape:'htmlall':'UTF-8',true)} <br/>
                                {/if}
                            </td>
                        </tr>
                    {/if}
                {/foreach}
            </table>
        </section>
        <br />
        {if (!is_backward)}
            /* If 1.4 and no backward, then leave */
        {else}
            <form action="{$request_uri|escape:'UTF-8'}" method="post">
            <section class="simplify-settings">
                <h2>API Key Mode</h2>
                <div class="half container">
                    <div class="keyModeContainer">
                        <input class="radioInput" type="radio" name="simplify_mode" value="0"
                                {if !$simplify_mode}
                                    checked="checked"
                                {/if}
                                /><span>Test Mode</span>
                        <input class="radioInput" type="radio" name="simplify_mode" value="1"
                                {if $simplify_mode}
                                    checked="checked"
                                {/if}
                                /><span>Live Mode</span>
                    </div>
                    <p><div class="bold">Test Mode</div> All transactions in test mode are test payments. You can test your installation using card numbers from our
                    <a href="https://www.simplify.com/commerce/docs/tutorial/index#testing" target="_blank">list of test card numbers</a>.
                    You cannot process real payments in test mode, so all other card numbers will be declined.</p>
                    <p><div class="bold">Live Mode</div> All transactions made in live mode are real payments and will be processed accordingly.</p>
                </div>
                <h2>Set Your API Keys</h2>
                <div class="account-mode container">
                    <p>If you have not already done so, you can create an account by clicking the 'Sign up for free' button in the top right corner.<br />
                        Obtain both your private and public API Keys from: Account Settings -> API Keys and supply them below.</p>
                </div>
                <div class="clearfix api-key-container">
                    <div class="clearfix api-key-title">
                        <div class="left"><h4 class="ng-binding">Test</h4></div>
                    </div>
                    <div class="api-keys">
                        <div class="api-key-header clearfix">
                            <div class="left api-key-key">Private Key</div>
                            <div class="left api-key-key">Public Key</div>
                        </div>
                        <div class="api-key-box clearfix">
                            <div class="left api-key-key api-key ng-binding"><input type="password" name="simplify_private_key_test"
                                                                                    value="{$private_key_test|escape:'htmlall':'UTF-8'}"/></div>
                            <div class="left api-key-key api-key ng-binding"><input type="text" name="simplify_public_key_test"
                                                                                    value="{$public_key_test|escape:'htmlall':'UTF-8'}"/></div>
                        </div>
                    </div>
                </div>

                <div class="clearfix api-key-container">
                    <div class="clearfix api-key-title">
                        <div class="left"><h4 class="ng-binding">Live</h4></div>
                    </div>
                    <div class="api-keys">
                        <div class="api-key-header clearfix">
                            <div class="left api-key-key">Private Key</div>
                            <div class="left api-key-key">Public Key</div>
                        </div>
                        <div class="api-key-box clearfix">
                            <div class="left api-key-key api-key ng-binding"><input type="password" name="simplify_private_key_live"
                                                                                    value="{$private_key_live|escape:'htmlall':'UTF-8'}"/></div>
                            <div class="left api-key-key api-key ng-binding"><input type="text" name="simplify_public_key_live"
                                                                                    value="{$public_key_live|escape:'htmlall':'UTF-8'}"/></div>
                        </div>
                    </div>
                </div>
                <div class="clearfix">
                    <div class="left half">
                        <h2>Save Customer Details</h2>
                        <div class="account-mode container">
                            <p>Enable customers to save their card details securely on Simplify's servers for future transactions.</p>
                            <div class="saveCustomerDetailsContainer">
                                <input class="radioInput" type="radio" name="simplify_save_csutomer_details" value="1"
                                        {if $save_customer_details == 1}
                                            checked="checked"
                                        {/if}
                                        /><span>Yes</span>
                                <input class="radioInput" type="radio" name="simplify_save_csutomer_details" value="0"
                                        {if $save_customer_details == 0}
                                            checked="checked"
                                        {/if}
                                        /><span>No</span>
                            </div>
                        </div>
                    </div>
                    <div class="half container left">
                        {foreach $statuses_options as $status_options}
                            <h2>{$status_options['label']|escape:'htmlall': 'UTF-8'}</h2>

                            <p>Choose the status for an order once the payment has been successfully processed by Simplify.</p>
                            <div>
                                <select name="{$status_options['name']|escape:'htmlall':'UTF-8'}">
                                    {foreach $statuses as $status}
                                        <option value="{$status['id_order_state']|escape:'htmlall':'UTF-8'}"
                                                {if $status['id_order_state'] == $status_options['current_value']}
                                                    selected="selected"
                                                {/if}
                                                >{$status['name']|escape:'htmlall': 'UTF-8'}</option>
                                    {/foreach}
                                </select>
                            </div>
                        {/foreach}
                        <div>
                        </div>
                    </div>
                </div>
                <div class="clearfix"><input type="submit" class="settings-btn btn right" name="SubmitSimplify" value="Save Settings" /></div></div>
                </section>
            </form>
        {/if}
</div>
<script type="text/javascript">
    function updateSimplifySettings()
    {
        if ($('input:radio[name=simplify_mode]:checked').val() == 1)
            $('fieldset.simplify-cc-numbers').hide();
        else
            $('fieldset.simplify-cc-numbers').show(1000);
    }
    $('input:radio[name=simplify_mode]').click(function() { updateSimplifySettings(); });
    $(document).ready(function() { updateSimplifySettings(); });
</script>
