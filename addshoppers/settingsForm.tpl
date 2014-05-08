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
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<form action="{$action}" method="post">
        <fieldset class="width2" id="tab">
                <legend><img src="{$module_dir}../../img/admin/cog.gif" alt="" class="middle" />{l s='Default Social Apps' mod='addshoppers'}</legend>

                <p>{$help_message}</p>

                <label>{l s='Use floating social buttons' mod='addshoppers'}</label>
                <div class="margin-form">
                        <input type="checkbox" name="addshoppers_floating_buttons" value="1" {if $floating_buttons}checked="checked"{/if} />
                </div>

                <center><input type="submit" name="addshoppers_settings" value="{l s='Save' mod='addshoppers'}" class="button" /></center>

                <p></p>
                <p>{l s='Follow us for updates on new features:' mod='addshoppers'}</p>

                <center class="social-links">
                    <a href="https://twitter.com/addshoppers" class="twitter-follow-button" data-show-count="false" data-size="large">{l s='Follow @addshoppers' mod='addshoppers'}</a>

                    <div class="fb-like" data-href="https://www.facebook.com/addshoppers" data-send="false" data-layout="button_count" data-show-faces="true"></div>

                	<div class="g-plusone" data-size="medium" data-href="//plus.google.com/112540297435892482797?rel=publisher"></div>
					<script type="text/javascript">
					  (function() {
						var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
						po.src = 'https://apis.google.com/js/plusone.js';
						var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
					  })();
					</script>

					<a style="vertical-align:middle" href="http://feeds.feedburner.com/addshoppers" rel="alternate" title="Subscribe to my feed" type="application/rss+xml">
						<img alt="" src="http://www.feedburner.com/fb/images/pub/feed-icon16x16.png" style="vertical-align:middle;margin-top:-2px;" />
					</a>
                </center>

                {literal}
                  <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
                {/literal}
        </fieldset>
</form>
