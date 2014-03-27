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

<div id="fb-root"></div>
<script type="text/javascript">(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<!--[if lt IE 9]>
<style>
	.top .wrap .adshoppers{
		width: 99.5%;
	}
	.top .wrap .box-holder .box{
		width: 32.65%;
	}
	.top .wrap .adshoppers{
		font-size: 22px;
	}
	.top .wrap, .bottom .wrap{
		min-width: 1000px;
	}
</style>
<![endif]-->
<div class="top">
        <div class="logo">
                <img src="../modules/addshoppers/static/img/logo.png">
        </div>
        <div class="wrap">
                <div class="adshoppers">
                        <span class="orange">{l s='FREE:' mod='addshoppers'}</span> 
                        {l s='Addshoppers adds social sharing buttons to your site that help your products get shared more,<br />along with detailed analytics that reveal the ROI of social sharing.' mod='addshoppers'}<br />
                        <a href="#addshoppers-setup" class="candy" style="text-decoration:underline">{l s='Get started' mod='addshoppers'}</a> {l s='or' mod='addshoppers'} <a href="http://www.addshoppers.com/prestashop" target="_blank" class="candy" style="text-decoration:underline">{l s='learn more' mod='addshoppers'}</a>.
                </div>
                <div class="box-holder">
                        <div class="box hand">
                                <div class="inner">
                                        <h2>{l s='Sharing Buttons' mod='addshoppers'}</h2>
                                        <div class="text">
                                                {l s='Ideal for sharing products, not just articles and content.' mod='addshoppers'}
                                                <ul>
                                                  <li>{l s='Facebook Want and Own buttons' mod='addshoppers'}</li>
                                                  <li>{l s='See your most Pinned products' mod='addshoppers'}</li>
                                                </ul>
                                        </div>
                                </div>
                        </div>
                        <div class="box dollar">
                                <div class="inner">
                                        <h2>{l s='Social Rewards' mod='addshoppers'}</h2>
                                        <div class="text">
                                                {l s='Increase customer engagement and lower shopping cart abandonment.' mod='addshoppers'}
                                                <ul>
                                                  <li>{l s='Increase sharing by over 2x' mod='addshoppers'}</li>
                                                  <li>{l s='Set different rewards and tips per source' mod='addshoppers'}</li>
                                                </ul>
                                        </div>
                                </div>
                        </div>
                        <div class="box navigation">
                                <div class="inner">
                                        <h2>{l s='Social Analytics' mod='addshoppers'}</h2>
                                        <div class="text">
                                                {l s='Measure the value of social to orders and revenue.' mod='addshoppers'}
                                                <ul>
                                                  <li>{l s='Stop guessing about social media ROI' mod='addshoppers'}</li>
                                                  <li>{l s='See which orders were socially influenced' mod='addshoppers'}</li>
                                                </ul>
                                        </div>
                                </div>
                        </div>
                        <div class="clear"></div>
                </div>
                <div class="adshoppers-free">
                        {if $account_is_configured }
                            <span style="color:green">{l s='Account is active.' mod='addshoppers'}</span> You're tracking stats! <a href="http://addshoppers.com" target="_blank">View your stats</a>.
                        {else}
                            <b><span class="dark candy">{l s='Addshoppers is completely <em>free</em>' mod='addshoppers'}</span></b> - <a href="#addshoppers-setup" class="candy" style="text-decoration:underline">{l s='get started' mod='addshoppers'}</a> {l s='by creating your account below.' mod='addshoppers'}
                        {/if}
                </div>
        </div>
</div>
<div class="bottom">
        <div class="wrap">
                <div class="left-nav">
                        {$output}
                </div>
                <div class="right-nav">
                        <div class="feeds">
                                <img src="../modules/addshoppers/static/img/feeds.png">
                        </div>
                        <div class="big-black">
                                {l s='100\'s of button styles available to match your site\'s look & feel. Place social buttons anywhere.' mod='addshoppers'}
                        </div>
                        <div class="lear-more">
                                <a href="http://help.addshoppers.com/knowledgebase/articles/98896-social-sharing-button-placement-examples" target="_blank">{l s='learn more' mod='addshoppers'}</a>
                        </div>

                        <div class="need-help">
                                <h2>{l s='Need help?' mod='addshoppers'}</h2>
                                <span class="url"><a href="http://forums.addshoppers.com" target="_blank">eCommerce Forums</a></span>
                        </div>

                        <div class="about">
                          <h2>{l s='Advanced integration instruction' mod='addshoppers'}</h2>
                          <p>{l s='To change button types or positioning on any theme:' mod='addshoppers'}</p>

                          <ol>
                            <li>1. {l s='Login to your' mod='addshoppers'} <a href="https://www.addshoppers.com/merchants" target="_blank">AddShoppers Merchant Admin</a>.</li>
                            <li>2. {l s='From the left navigation, go to' mod='addshoppers'} <i>Get Apps -> Sharing Buttons</i></li>
                            <li>3. {l s='Select the button you want and copy the div code.' mod='addshoppers'}</li>
                            <li>4. {l s='Find file <i>product.tpl</i> in <i>themes/prestashop</i>.' mod='addshoppers'}</li>
                            <li>5. {l s='Paste our code where you want the buttons to appear.' mod='addshoppers'}</li>
                            <li>6. {l s='Don\'t forget to create canonical links for products or install the appropriate PrestaShop module.' mod='addshoppers'}</li>
                          </ol>
                        </div>

                        <div class="about">
                                <h2>{l s='About AddShoppers' mod='addshoppers'}</h2>
                                {l s='AddShoppers is a free social sharing and analytics platform built for eCommerce.
                                We make it easy to add social sharing buttons to your site, measure the ROI of social at
                                the SKU level, and increase sharing by rewarding social actions. You\'ll discover the value
                                of social sharing, identify influencers, and decrease shopping cart abandonment by adding
                                AddShoppers social apps to your store.' mod='addshoppers'}

                                <div class="get-started">
                                        <a href="http://www.addshoppers.com" target="_blank">{l s='Get started with your free account at AddShoppers.com.' mod='addshoppers'}</a>
                                </div>
                        </div>
                        <div>
                                {l s='If you\'re a large enterprise retailer who needs a more custom solution, <a href="http://www.addshoppers.com/enterprise" target="_blank">learn more</a>.' mod='addshoppers'}
                        </div>
                </div>
                <div class="clear"></div>
<div style="margin-top: 25px; text-align: center;">
{l s='By installing the AddShoppers module you agree to our ' mod='addshoppers'}
<a href="http://www.addshoppers.com/terms" target="_blank" title="{l s='Terms' mod='addshoppers'}">{l s='Terms' mod='addshoppers'}</a>
 {l s='and' mod='addshoppers'} <a href="http://www.addshoppers.com/privacy" target="_blank" title="{l s='Privacy Policy' mod='addshoppers'}">{l s='Privacy Policy' mod='addshoppers'}</a>
</div>
        </div>
</div>
