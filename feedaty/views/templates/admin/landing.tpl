{*
* 2007-2013 PrestaShop
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
*  @author Feedaty <info@feedaty.com>
*  @copyright  2012-2014 Feedaty
*  @version  Release: 1.1.135 $
*}
<div class="ps">
    <div class="ps_content">

        <div class="lf_clm">
            <p><img alt="Feedaty" src="../modules/feedaty/img/logo.png">{l s='Feedaty description landing' mod='feedaty'}</p></div>
        <div class="rg_clm">
            <div class="box"><h1>{l s='Benefits' mod='feedaty'}</h1>
                <ul>
                    <li>{l s='Increase in the rate of conversion' mod='feedaty'}</li>
                    <li>{l s='Increased user confidence' mod='feedaty'}</li>
                    <li>{l s='Improved SEO ranking' mod='feedaty'}</li>
                    <li>{l s='Improved online reputation' mod='feedaty'}</li>
                    <li>{l s='Improved customer services' mod='feedaty'}</li>
                </ul>

            </div>

            <div class="box"><h1>{l s='How it works' mod='feedaty'}</h1>
                <ul>
                    <li>{l s='Certified rating for store and products' mod='feedaty'}</li>
                    <li>{l s='Seal “Certified Rating” by Feedaty' mod='feedaty'}</li>
                    <li>{l s='Widget for publishing reviews on the site' mod='feedaty'}</li>
                    <li>{l s='Integration with social networks' mod='feedaty'}</li>
                    <li>{l s='Integration with Google' mod='feedaty'}</li>
                </ul>

            </div>

        </div>
        <div class="clearfix"></div>
        <div class="claim">{l s='Enable Feedaty for your Prestashop store.' mod='feedaty'} <strong>{l s='Collect and publish for free up to 300 reviews from your consumers.' mod='feedaty'}</strong></div>
        <div class="lf_clm">
            <div class="subscribe">
                <form id="new-account" method="post" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}">
                    <input type="hidden" name="act" value="requesttrial">
                    <p>{l s='To activate an account enter your email address and password, you will receive a confirmation email:' mod='feedaty'}</p>
                    <div class="label">{l s='Email:' mod='feedaty'}</div>
                    <input id="email" name="feedaty_email" type="text" value="{$email_default|escape:'htmlall':'UTF-8'}">
                    <div class="clear"></div>
                    <div class="label">{l s='Password:' mod='feedaty'}</div>
                    <input id="password" name="feedaty_password" type="password">
                    <div class="return">
                        {if $msg eq 1}{l s='Thank you for your request.' mod='feedaty'}<br>{l s='Within a few hours you will receive a welcome e-mail containing the Merchant Code that you can use to activate the plugin.' mod='feedaty'}{/if}
                        {if $msg eq 2}{l s='The request has not been sent.' mod='feedaty'}<br>{l s='Verify that the data are correct.' mod='feedaty'}{/if}
                    </div>
                    <div{if ($msg eq 1) or ($msg eq 2)} style="display:none"{/if}><input class="simple_btn" id="button" type="submit" value="{l s="Send" mod='feedaty'}"></div>
                    <input type="hidden" name="url" value="http://{$smarty.server.HTTP_HOST|escape:'htmlall':'UTF-8'}{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}">
                    <div class="clearfix"></div>
                </form>
                <div id="layered-ajax-loading" style="display:none">
                    <div style="">
                        <img src="../img/admin/ajax-loader-big.gif" alt=""><br>
                        <p style="color: white;">{l s='Loading' mod='feedaty'}...</p>
                    </div>
                </div>
                <form action="{$smarty.server.REQUEST_URI}" method="post">
                    <div class="line"><hr></div>
                    <p>{l s='Already have a Feedaty account?' mod='feedaty'}
                        <br>
                        {l s='Enter your Merchant Code:' mod='feedaty'}<br>
                        <span class="small">({l s='which you\'ll find it in your Feedaty partners area' mod='feedaty'})</span></p>
                    <div class="label">{l s='Merchant Code:' mod='feedaty'}</div>
                    <input id="Text3" type="text" name="code"><br>

                    <div><input class="simple_btn" id="button1" type="submit" name="submitModule" value="{l s='Send' mod='feedaty'}"></div>
                    <div class="clearfix"></div>

                </form>
            </div>
        </div>
        <div class="rg_clm">
            <div class="instructions">
                <p><strong>{l s='Just a few simple steps to begin to collect certified reviews with Feedaty:' mod='feedaty'}</strong></p>
                <ol>
                    <li><div class="num">1</div><div>{l s='Create your Feedaty account or enter your Merchant Code' mod='feedaty'}</div></li>
                    <li class="top"><div class="num">2</div><div>{l s='Sign in Feedaty partner area at' mod='feedaty'} <a href="http://partners.feedaty.com">http://partners.feedaty.com</a> {l s='with the login data that you chose.' mod='feedaty'}</div> </li>
                    <li><div class="num">3</div><div>{l s='Complete your profile information' mod='feedaty'}</div></li>
                    <li><div class="num">4</div><div>{l s='Import previous orders in Feedaty (follow the guide)' mod='feedaty'}</div></li>
                    <li class="top"><div class="num">5</div><div>{l s='After a few days you will see the first reviews from your satisfied customers, verified and certified with the seal Feedaty!' mod='feedaty'}</div>  </li>
                </ol>
                <div class="clearfix"></div>
                <h2>{l s='Want to learn more?' mod='feedaty'}</h2> <p>{l s='Visit our website' mod='feedaty'} <a href="http://www.feedaty.com">www.feedaty.com</a><br />{l s='or contact us by' mod='feedaty'} <a href="mailto:prova{* email *}@feedaty.com">prova{* email *}@feedaty.com</a></p>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
<script type="text/javascript">
    $(function(){
        $("#new-account .simple_btn").click(function(){
            /* Show loading div */
            $('#layered-ajax-loading').fadeIn();
            $('#layered-ajax-loading').css('top',-1*($("#new-account").height()+parseInt($(".ps .subscribe").css("padding-top"))))
                    .css('left',-1*parseInt($(".ps .subscribe").css("padding-left")))
                    .css('height',$("#new-account").height()+(1.4*parseInt($(".ps .subscribe").css("padding-top"))))
                    .css('width',$("#new-account").width()+(2*parseInt($(".ps .subscribe").css("padding-left"))));
            $('#layered-ajax-loading').css('margin-bottom',-1*$('#layered-ajax-loading').height());
            /* Send request for new account */
            $.ajax({
                url: $("#new-account").attr("action"),
                type: 'POST',
                dataType: "json",
                data: $("#new-account").serialize(),
                success:function(json){
                    if (json.success == 1) {
                        $("#new-account .return").html("{l s='Thank you for your request.' mod='feedaty'}<br>{l s='Within a few hours you will receive a welcome e-mail containing the Merchant Code that you can use to activate the plugin.' mod='feedaty'}");
                        $("#new-account .simple_btn").hide();
                    }
                    else
                        $("#new-account .return").html("{l s='The request has not been sent.' mod='feedaty'}<br>{l s='Verify that the data are correct.' mod='feedaty'}");
                    $('#layered-ajax-loading').fadeOut();
                }
            });

            $(document).ajaxError(function( event, jqxhr, settings, exception ) {
                if ( settings.url == $("#new-account").attr("action")+"a" ) {
                    alert("errore");
                }
            });

            return false;
        });
    });
</script>