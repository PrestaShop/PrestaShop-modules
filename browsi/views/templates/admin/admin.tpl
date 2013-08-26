{*
* 2013 Brow.si
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
*  @author MySiteApp Ltd. <support@mysiteapp.com>
*  @copyright  2013 MySiteApp Ltd.
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of MySiteApp Ltd
*}
<link href="{$module_dir}css/browsi.css" rel="stylesheet" type="text/css">
<img src="{$browsi_tracking|escape:'htmlall':'UTF-8'}" alt="" style="display: none;"/>
<div class="browsi-wrap">
    {$browsi_message}
    <div class="br-header">
        <div class="br-content">
            <div class="br-device"></div>
            <div class="br-logo">Brow.si</div>
            <p class="br-tagline">{l s ='Drive more customer engagement, generate more traffic and sell more on mobile with Brow.si.' mod='browsi'}</p>
            <a href="{$browsi_register_link|escape:'htmlall':'UTF-8'}" class="br-button" title="{l s='Register to Brow.si for free' mod='browsi'}" target="_blank">{l s='Register for free' mod='browsi'}</a>
        </div>
        <div class="br-features" {if version_compare($smarty.const._PS_VERSION_,'1.5','<')}style="padding: 1em 1em"{/if}>
            <div class="br-features-list">
                <div class="br-col">
                    <div class="br-feature"><div class="br-i br-i-cart"></div><div class="br-li-caption">{l s='Always on shopping cart' mod='browsi'}</div></div>
                    <div class="br-feature"><div class="br-i br-i-cloud"></div><div class="br-li-caption">{l s='Continuous engagement across desktop and mobile' mod='browsi'}</div></div>
                </div>
                <div class="br-col">
                    <div class="br-feature"><div class="br-i br-i-share"></div><div class="br-li-caption">{l s='Superior social sharing' mod='browsi'}</div></div>
                    <div class="br-feature"><div class="br-i br-i-push"></div><div class="br-li-caption">{l s='Stay in touch with customers through push notifications' mod='browsi'}</div></div>
                </div>
                <div class="br-col">
                    <div class="br-feature"><div class="br-i br-i-analytics"></div><div class="br-li-caption">{l s='Front-end analytics' mod='browsi'}</div></div>
                    <div class="br-feature"><div class="br-i br-i-customizability"></div><div class="br-li-caption">{l s='Customizable and localized' mod='browsi'}</div></div>
                </div>
            </div>
        </div>
    </div>
    <form action="{$browsi_form|escape:'htmlall':'UTF-8'}" id="browsi-settings" method="post">
        <fieldset class="br-fieldset">
            <legend>{l s='Configuration' mod='browsi'}</legend>
            <div class="br-col br-form-col">
                <p class="MB10">{l s='Brow.si works out-of-the-box, no further action required.' mod='browsi'}
                    <strong>{l s='Want to customize your Brow.si, access analytics and get updates on new features? Then, please register your site with us. It\'s Free!' mod='browsi'}</strong>
                </p>
                <label for="browsi_site_id">{l s='Brow.si Site ID:' mod='browsi'}</label>
                <div class="margin-form">
                    <input type="text" class="text" name="browsi_site_id" id="browsi_site_id" value="{$browsi_site_id|escape:'htmlall':'UTF-8'}" />
                </div>
                <div class="margin-form">
                    <input type="submit" class="button" name="submitBrowsi" value="{l s='Save' mod='browsi'}" />
                </div>
            </div>
            <div class="br-col br-form-col br-sep">
                <h4>{l s='How to get your Brow.si Site ID?' mod='browsi'}</h4>
                <ol class="br-steps-list">
                    <li><p><a href="{$browsi_register_link|escape:'htmlall':'UTF-8'}" target="_blank">{l s='Register - It\'s Free!' mod='browsi'}</a></p></li>
                    <li><p>{l s='When prompted enter your website\'s URL.' mod='browsi'}</p></li>
                    <li><p>{l s='Copy your unique ID and paste it into the Brow.si Site ID field on this page. Your unique ID is under Dashboard > Website Info > Website Brow.si Site ID.' mod='browsi'}</p></li>
                </ol>
                <p><small>({l s='To register a new site, simply sign in to Brow.si and add the site from your' mod='browsi'}
 <a href="https://brow.si/dashboard" target="_blank">{l s='dashboard' mod='browsi'}</a>)</small></p>
            </div>
        </fieldset>
    </form>
</div>