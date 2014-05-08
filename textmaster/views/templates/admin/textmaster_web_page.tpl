{*
* 2013 TextMaster
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to info@textmaster.com so we can send you a copy immediately.
*
* @author JSC INVERTUS www.invertus.lt <help@invertus.lt>
* @copyright 2013 TextMaster
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* International Registered Trademark & Property of TextMaster
*}
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge;chrome=1">
<meta name="viewport" content="initial-scale=1.0, width=device-width, maximum-scale=1, user-scalable=no">
<link rel="stylesheet" type="text/css" href="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}textmaster/css/layout.css" media="screen" />
    <div class="action-log">
      <div class="wrap{if isset($ps14)} ps14{/if}">
        <a href="#" class="logo hide_replace">{l s='TextMaster' mod='textmaster'}</a>
        <span class="baseline">{l s='Translate your online store directly through PrestaShop' mod='textmaster'}</span>
        <div class="get-started">
          <h1 class="title">{l s='High quality translation in' mod='textmaster'} <strong>{l s='4 simple steps' mod='textmaster'}</strong></h1>
          <div class="process{if isset($ps14)} ps14{/if}"></div>
          <ul{if isset($ps14)} class="ps14"{/if}>
            <li><p>{l s='Setup your TextMaster module' mod='textmaster'} <br /> {l s='and account' mod='textmaster'}</p></li>
            <li><p>{l s='Select the language and level for your product sheet translation' mod='textmaster'}</p></li>
            <li><p>{l s='Send your order' mod='textmaster'}</p></li>
            <li class="last"><p>{l s='Ask for as many changes as needed before validating your order' mod='textmaster'}</p></li>
            <div class="cb"></div>
          </ul>
          <h2>{l s='That’s all there is to it! Your ﬁnal order is automatically integrated into PrestaShop.' mod='textmaster'}</h2>
        </div>
      </div>
      <div class="reassurance">
        <div class="wrap{if isset($ps14)} ps14{/if}">
          <div class="left">
            <form enctype="multipart/form-data" method="post" action="{$login_form_url|escape:'htmlall':'UTF-8'}" class="defaultForm login_textmaster" id="textmaster_login_form">
              <fieldset id="fieldset_0">
                  <legend>
                      <img alt="Login" src="{$smarty.const.__PS_BASE_URI__}img/admin/cog.gif"> {l s='Login' mod='textmaster'}
                  </legend>
                  <label>
                      {l s='Email Address' mod='textmaster'}
                  </label>		
                  <div class="margin-form">
                      <input type="text" size="18" class="" value="" id="login_email" name="login_email">
                  </div>
                  <div class="clear"></div>
                  <label>
                      {l s='Password' mod='textmaster'}
                  </label>							
                  <div class="margin-form">
                      <input type="password" value="" class="" size="18" name="login_password">
                  </div>
                  <div class="clear"></div>
                  <div class="margin-form">
                      <input type="submit" class="button" name="login_to_textmaster_system" value="{l s='Login' mod='textmaster'}" id="textmaster_login_form_submit_btn">
                  </div>
              </fieldset>
            </form>
          </div>
          <div class="right">
            <form enctype="multipart/form-data" method="post" action="{$register_form_url|escape:'htmlall':'UTF-8'}" class="defaultForm login_textmaster" id="textmaster_register_form">
              <fieldset id="fieldset_0">
                  <legend>
                      <img alt="Create New Account" src="{$smarty.const.__PS_BASE_URI__}img/admin/cog.gif"> {l s='Create New Account' mod='textmaster'}
                  </legend>
                  <label>
                      {l s='Email Address' mod='textmaster'}
                  </label>
                  <div class="margin-form">
                      <input type="text" size="18" class="" value="" id="register_email" name="register_email">
                  </div>
                  <div class="clear"></div>
                  <label>
                      {l s='Password' mod='textmaster'}
                  </label>
                  <div class="margin-form">
                      <input type="password" value="" class="" size="18" name="register_password">
                  </div>
                  <div class="clear"></div>
                  <label>
                      {l s='Confirm Password' mod='textmaster'}
                  </label>
                  <div class="margin-form">
                      <input type="password" value="" class="" size="18" name="register_password_confirm">
                  </div>
                  <div class="clear"></div>
                  <label>
                      {l s='Phone (optional)' mod='textmaster'}
                  </label>
                  <div class="margin-form">
                      <input type="text" value="" class="" size="18" name="register_phone">
                  </div>
                  <div class="clear"></div>
                  <div class="margin-form">
                      <input type="submit" class="button" name="register_to_textmaster_system" value="{l s='Create Free Account' mod='textmaster'}" id="textmaster_register_form_submit_btn">
                  </div>
              </fieldset>
            </form>
          </div>
        </div>
        <div class="cb"></div>
        <div class="wrap{if isset($ps14)} ps14{/if}">
          <h3>{l s='Why choose TextMaster? Here are a few ways that we make your life easier' mod='textmaster'}</h3>
          <ul{if isset($ps14)} class="ps14_"{/if}>
            <li class="quality">
              <i></i>
              <p>{l s='High quality translation from certiﬁed professionals' mod='textmaster'}</p>
            </li>
            <li class="available">
              <i></i>
              <p>{l s='Our community of 60,000 translators are available 24/7' mod='textmaster'}</p>
            </li>
            <li class="store">
              <i></i>
              <p>{l s='Your store can be translated into 10 languages' mod='textmaster'}</p>
            </li>
            <li class="proof">
              <i></i>
              <p>{l s='Our proofreading services ensure the quality of your content' mod='textmaster'}</p>
            </li>
            <li class="money">
              <i></i>
              <p>{l s='Flexible rates based on the size and frequency of your orders' mod='textmaster'}</p>
            </li>
            <div class="cb"></div>
          </ul>
        </div>
      </div>
    </div>