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
        <div class="riskified-desc" mod='riskified'>

            <h1 class="riskified-h1">{l s='Welcome to Riskified!' mod='riskified'}</h1>
            {l s='Riskified is a turnkey fraud prevention solution that reviews, approves and guarantees your orders. You decide which transactions to submit for review and Riskified provides clear, actionable, accept or decline decisions instantly. All Riskified approved transactions carry a 100%% chargeback guarantee, allowing you to sell with confidence.' mod='riskified'}

            <h1 class="riskified-h1">{l s='Why use Riskified?' mod='riskified'}</h1>
            <ul id="riskified-ul">
                <li>{l s='Never pay for a chargeback again' mod='riskified'}.</li>
                <li>{l s='Eliminate false positive declines' mod='riskified'}.</li>
                <li>{l s='Fast, constant review turnaround' mod='riskified'}.</li>
                <li>{l s='Frictionless customer experience' mod='riskified'}.</li>
                <li>{l s='Unlock new growth opportunities' mod='riskified'}.</li>
                <li>{l s='Reduce the cost of managing fraud' mod='riskified'}.</li>
            </ul>

            <h1 class="riskified-h1">{l s='How do I sign up for Riskified?' mod='riskified'}</h1>
            {l s='To start using Riskified, follow these two simple steps:' mod='riskified'}
            <ol id="riskified-ul">
                <li><a class="riskified-button" style="margin-bottom:30px" href="http://www.riskified.com/signup.html" target="_blank">{l s='Sign up now!' mod='riskified'}</a></li>
                <li>{l s='Connect the accounts: Once you have signed up successfully, you will receive an email with the authorization token to connect your Prestashop and Riskified accounts.' mod='riskified'}.
                    <ul class="riskified-ul-above-config">
                        <li>{l s='Copy and paste the token from the email (should be 32 characters long) into the box below.' mod='riskified'}.</li>
                        <li>{l s='Change the mode from `Sandbox` to `Production` by clicking the radio button.' mod='riskified'}</li>
                        <li>{l s='Don’t forget to click the ‘Save’ button below!' mod='riskified'}</li>
                    </ul>
                </li>
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
            <div >
                <h3 class="riskified-h1">{l s='That’s it! You’re now ready to start using Riskified.' mod='riskified'}</h3>
                <p class="riskified-desc">
                    {l s='If you have any problems installing Riskified, click ' mod='riskified'}<a href="http://www.riskified.com/documentation/prestashop.html" target="_blank">here</a>{l s=' to watch our installation guide or email ' mod='riskified'}<a href="mailto:support@riskified.com?Subject=Need help with Prestashop Module">support@riskified.com</a>
                    <br/><br/>
                    {l s='Interested in learning more about Riskified? Check out our ' mod='riskified'}<a href="http://riskified.com" target="_blank">{l s='website' mod='riskified'}</a>.
                    <br/>{l s='Already have a Riskified account?' mod='riskified'}
                    Click <a href="https://app.riskified.com/login" target="_blank">here</a>{l s=' to log in to our web app.' mod='riskified'}
                </p>
                <br/><br/><br/>
            </div>
            <div>
            </div>
        </div>

