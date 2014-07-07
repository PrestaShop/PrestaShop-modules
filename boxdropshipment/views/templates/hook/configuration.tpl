{*
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
 * @author     boxdrop Group AG
 * @copyright  boxdrop Group AG
 * @license    http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of boxdrop Group AG
 *}

  <form action="{$form_url|escape:"quotes"}" method="post" id="module_form" class="defaultForm  form-horizontal">

    {if {$show_curl_warn}}
      <div class="warn">
        <strong>{l s='cURL missing!' mod='boxdropshipment'}</strong><br />
        {l s='Could not find the cURL extension. Please install / activate cURL for PHP in order to use this module.' mod='boxdropshipment'}
      </div>
    {/if}

    {if {$show_mcrypt_warn}}
      <div class="warn">
        <strong>{l s='mcrypt missing!' mod='boxdropshipment'}</strong><br />
        {l s='Could not find the mcrypt extension. Please install / activate mcrypt for PHP in order to use this module.' mod='boxdropshipment'}
      </div>
    {/if}

    <div id="fieldset_0" class="bootstrap panel">
      <div class="panel-heading">
        <img src="{$icon|escape:'htmlall':'UTF-8'}" alt="" title="" />
        {l s='Welcome to the world of boxdrop!' mod='boxdropshipment'}
      </div>
      <div class="form-group">
        <p>{l s='"boxdrop eLogistics ®" is the new module that allows you to connect your site to e-commerce directly with the best logistics company: DHL.' mod='boxdropshipment'}</p>
        <p>{l s='This module will make you forget all the shipment managing. With a simple click, it will automatically create all shipment letters suiting your order, and notify DHL to schedule a pick up!' mod='boxdropshipment'}</p>
        <p>{l s='The advantages are:' mod='boxdropshipment'}</p>
        <ol class="bshp-list">
          <li>{l s='boxdrop® offers a fully integrated process that automatically uses data from the order, creates the shipment letter, inserts the tracking number, and notifies the client about the status of delivery.' mod='boxdropshipment'}</li>
          <li>{l s='After selling your products you will not have to deal with anything annoying: boxdrop requests a pickup of the good at the address specified in the configuration and provides reports for all shipments at the end of the month' mod='boxdropshipment'}</li>
          <li>
            {l s='Forget about extra costs shipping rates are "all inclusive":' mod='boxdropshipment'}
            <ul class="bshp-list">
              <li>{l s='No fuel surcharges' mod='boxdropshipment'}</li>
              <li>{l s='No extra charges for private customers' mod='boxdropshipment'}</li>
              <li>{l s='No charges for deliveries in poorly covered areas' mod='boxdropshipment'}</li>
            </ul>
          </li>
          <li>{l s='Thanks to boxdrop® and the incredible efficiency of DHL your goods are be safe and your customers more satisfied.' mod='boxdropshipment'}</li>
        </ol>
        <p>{l s='But it does not end here!' mod='boxdropshipment'}</p>
        <p>{l s='With more than 200 (and growing) boxdrop® points in Italy it will soon be possible to activate the dropoff service, which will allow you to offer an additional service to your customers. They can pick up their good at a point of delivery when & where its most comfortable for them, helping you avoid any costs of storage and all actions related to it!' mod='boxdropshipment'}</p>
        <p>{l s='Try boxdrop® eLogistics!' mod='boxdropshipment'}</p>
        <p>{l s='Convenient, easy, secure!' mod='boxdropshipment'}</p>
        <a href="{$module_dir|escape:'htmlall':'UTF-8'}{$documentation_link|escape:'htmlall':'UTF-8'}" target="_blank">{l s='Do you need further help? Check out our manual!' mod='boxdropshipment'}</a>
      </div>
    </div>

    <div id="fieldset_1" class="bootstrap panel">
      <div class="panel-heading">
        <img src="{$icon|escape:'htmlall':'UTF-8'}" alt="" title="" />
        {l s='Sender address for shipments' mod='boxdropshipment'}
      </div>
      <div class="alert alert-info">
        {l s='The sender address data is the same than you configured in Settings -> Shop addresses. This will be the sender address for all shipments.' mod='boxdropshipment'}
      </div>

      <div class="form-group">
        <label for="PS_SHOP_NAME" class="control-label col-lg-3">{l s='Shop name' mod='boxdropshipment'} *</label>
        <div class="col-lg-9">
          <input type="text" class="fixed-width-xl" value="{$PS_SHOP_NAME|escape:'htmlall':'UTF-8'}" id="PS_SHOP_NAME" name="PS_SHOP_NAME">
        </div>
      </div>

      <div class="form-group">
        <label for="PS_SHOP_ADDR1" class="control-label col-lg-3">{l s='Address' mod='boxdropshipment'} *</label>
        <div class="col-lg-9">
          <input type="text" class="fixed-width-xl" value="{$PS_SHOP_ADDR1|escape:'htmlall':'UTF-8'}" id="PS_SHOP_ADDR1" name="PS_SHOP_ADDR1">
        </div>
      </div>

      <div class="form-group">
        <label for="PS_SHOP_ADDR2" class="control-label col-lg-3">{l s='Address 2' mod='boxdropshipment'}</label>
        <div class="col-lg-9">
          <input type="text" class="fixed-width-xl" value="{$PS_SHOP_ADDR2|escape:'htmlall':'UTF-8'}" id="PS_SHOP_ADDR2" name="PS_SHOP_ADDR2">
        </div>
      </div>

      <div class="form-group">
        <label for="PS_SHOP_CODE" class="control-label col-lg-3">{l s='Zipcode' mod='boxdropshipment'} *</label>
        <div class="col-lg-9">
          <input type="text" class="fixed-width-xl" value="{$PS_SHOP_CODE|escape:'htmlall':'UTF-8'}" id="PS_SHOP_CODE" name="PS_SHOP_CODE">
        </div>
      </div>

      <div class="form-group">
        <label for="PS_SHOP_CITY" class="control-label col-lg-3">{l s='City' mod='boxdropshipment'} *</label>
        <div class="col-lg-9">
          <input type="text" class="fixed-width-xl" value="{$PS_SHOP_CITY|escape:'htmlall':'UTF-8'}" id="PS_SHOP_CITY" name="PS_SHOP_CITY">
        </div>
      </div>

      <div class="form-group">
        <label for="PS_SHOP_PHONE" class="control-label col-lg-3">{l s='Phone number' mod='boxdropshipment'} *</label>
        <div class="col-lg-9">
          <input type="text" class="fixed-width-xl" value="{$PS_SHOP_PHONE|escape:'htmlall':'UTF-8'}" id="PS_SHOP_PHONE" name="PS_SHOP_PHONE">
        </div>
      </div>

      <div class="form-group">
        <label for="PS_SHOP_EMAIL" class="control-label col-lg-3">{l s='Email address' mod='boxdropshipment'} *</label>
        <div class="col-lg-9">
          <input type="text" class="fixed-width-xl" value="{$PS_SHOP_EMAIL|escape:'htmlall':'UTF-8'}" id="PS_SHOP_EMAIL" name="PS_SHOP_EMAIL">
        </div>
      </div>

      <div class="form-group">
        <div class="col-lg-3">
        </div>
        <div class="col-lg-9">
          <sup>*</sup> {l s='Mandatory field' mod='boxdropshipment'}
        </div>
      </div>
    </div>

    <div id="fieldset_2" class="bootstrap panel">
      <div class="panel-heading">
        <img src="{$icon|escape:'htmlall':'UTF-8'}" alt="" title="" />
        {l s='boxdrop API settings' mod='boxdropshipment'}
      </div>

      <div class="alert alert-info">
        {l s='These settings are enabling an encrypted and secure communication with the boxdrop systems.' mod='boxdropshipment'}<br /><br />
      </div>

      {if {$show_api_warn}}
        <div class="alert alert-danger">
          {l s='There are no API credentials saved yet.' mod='boxdropshipment'}<br />
          {l s='If you haven\'t yet registered for API usage, please follow this link to register an account:' mod='boxdropshipment'} <strong><a id="bshp-register-link" href="https://www.boxdrop.com/become/sellingBase/API-SIGNUP/it/it" target="_blank">{l s='Register now to use boxdrop eLogistics' mod='boxdropshipment'}</a></strong>
        </div>
      {/if}

      <div class="form-group">
        <label for="API_USER_ID" class="control-label col-lg-3">{l s='API User ID' mod='boxdropshipment'} *</label>
        <div class="col-lg-9">
          <input type="text" class="fixed-width-xl" value="{$API_USER_ID|escape:'htmlall':'UTF-8'}" id="API_USER_ID" name="API_USER_ID">
          <p class="help-block">
            {l s='Please enter the "API User ID" you received after registration for API usage' mod='boxdropshipment'}
          </p>
        </div>
      </div>

      <div class="form-group">
        <label for="API_PASS" class="control-label col-lg-3">{l s='API password' mod='boxdropshipment'} *</label>
        <div class="col-lg-9">
          <input type="text" class="fixed-width-xl" value="{$API_PASS|escape:'htmlall':'UTF-8'}" id="API_PASS" name="API_PASS">
          <p class="help-block">
            {l s='Please enter the "API password" you received after registration for API usage.' mod='boxdropshipment'}
          </p>
        </div>
      </div>

      <div class="form-group">
        <label for="API_HMAC_KEY" class="control-label col-lg-3">{l s='API HMAC key' mod='boxdropshipment'} *</label>
        <div class="col-lg-9">
          <input type="text" class="fixed-width-xl" value="{$API_HMAC_KEY|escape:'htmlall':'UTF-8'}" id="API_HMAC_KEY" name="API_HMAC_KEY">
          <p class="help-block">
            {l s='Please enter the "API HMAC key" you received after registration for API usage.' mod='boxdropshipment'}
          </p>
        </div>
      </div>

      <div class="form-group">
        <label for="API_COUNTRY" class="control-label col-lg-3">{l s='API country endpoint' mod='boxdropshipment'} *</label>
        <div class="col-lg-9">
          <select id="API_COUNTRY" class="fixed-width-xl" name="API_COUNTRY">
            <option value="">{l s='- please choose -' mod='boxdropshipment'}</option>
            <option value="it" {if {$API_COUNTRY} == 'it'}selected="selected"{/if}>Italy</option>
          </select>
          <p class="help-block">
            {l s='Please choose the "API country endpoint" you received after registration for API usage. This usually equals your country.' mod='boxdropshipment'}
          </p>
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-lg-3">{l s='API test mode' mod='boxdropshipment'}</label>
        <div class="col-lg-9">

          <select id="API_TEST_MODE" class="fixed-width-xl" name="API_TEST_MODE">
            <option value="1" {if {$API_TEST_MODE} == '1'}selected="selected"{/if}>test mode</option>
            <option value="0" {if {$API_TEST_MODE} == '0'}selected="selected"{/if}>LIVE mode</option>
          </select>

          <p class="help-block">
            {l s='Be careful: When set to "test mode", NO REAL shipments are being created! This should NEVER be "test mode" in production environment!' mod='boxdropshipment'}
          </p>
        </div>
      </div>

      <div class="form-group">
        <div class="col-lg-3">
        </div>
        <div class="col-lg-9">
          <sup>*</sup> {l s='Mandatory field' mod='boxdropshipment'}
        </div>
      </div>
    </div>

    <div id="fieldset_3" class="bootstrap panel">
      <div class="panel-heading">
        <img src="{$icon|escape:'htmlall':'UTF-8'}" alt="" title="" />
        {l s='Interaction settings' mod='boxdropshipment'}
      </div>

      <div class="alert alert-info">
        {l s='You can setup various automation tasks in this area' mod='boxdropshipment'}<br /><br />
      </div>

      <div class="form-group">
        <label class="control-label col-lg-3" for="SHIPPING_STATUS">{l s='Shipment status' mod='boxdropshipment'} *</label>
        <div class="col-lg-9">

          <select id="SHIPPING_STATUS" class="fixed-width-xl" name="SHIPPING_STATUS">
            <option value="0">{l s='- please choose -' mod='boxdropshipment'}</option>
            {foreach from=$order_states item="order_state"}
               <option value="{$order_state.id_order_state|escape:'htmlall':'UTF-8'}" {if {$SHIPPING_STATUS} == {$order_state.id_order_state|escape:'htmlall':'UTF-8'}}selected="selected"{/if}>
                 {$order_state.name|escape:'htmlall':'UTF-8'}
               </option>
            {/foreach}
          </select>

          <p class="help-block">
            {l s='After creating a shipment, the order status will automatically be updated to the selected status.' mod='boxdropshipment'}
          </p>
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-lg-3" for="AUTO_DOWNLOAD">{l s='Open shipment letter automatically' mod='boxdropshipment'}</label>
        <div class="col-lg-9">

          <select id="AUTO_DOWNLOAD" class="fixed-width-xl" name="AUTO_DOWNLOAD">
            <option value="1" {if {$AUTO_DOWNLOAD} == '1'}selected="selected"{/if}>{l s='yes' mod='boxdropshipment'}</option>
            <option value="0" {if {$AUTO_DOWNLOAD} == '0'}selected="selected"{/if}>{l s='no' mod='boxdropshipment'}</option>
          </select>

          <p class="help-block">
            {l s='When set to "yes", the shipment letter will automatically be opened after a shipment has been created.' mod='boxdropshipment'}
          </p>
        </div>
      </div>

      <div class="form-group">
        <div class="col-lg-3">
        </div>
        <div class="col-lg-9">
          <sup>*</sup> {l s='Mandatory field' mod='boxdropshipment'}
        </div>
      </div>

      <div class="panel-footer">
        <button class="btn btn-default pull-right" name="submitHomeFeatured" id="module_form_submit_btn" value="1" type="submit">
          <i class="process-icon-save"></i> Speichern
        </button>
      </div>
    </div>


    <div id="fieldset_4" class="bootstrap panel">
      <div class="panel-heading">
        <img src="{$icon|escape:'htmlall':'UTF-8'}" alt="" title="" />
        {l s='Shipping price presets' mod='boxdropshipment'}
      </div>

      <div class="form-group">

        <div class="alert alert-info">
          {l s='You may choose one of the following shipment cost presets' mod='boxdropshipment'}<br /><br />
        </div>

        <div class="col-lg-6 panel">

          <div class="bshp-align-height-panelinner">
            <br /><br /><br /><br /><br />

            <div class="bshp-centered">
              <input type="button" class="btn btn-default bshp-default-preset" name="submit" value="{l s='Pass prices to the end customer' mod='boxdropshipment'}" />
            </div>
          </div>

          <br />

          <div class="alert alert-warning bshp-align-height-warnings">
            <p>{l s='Clicking this button will immediately update the shipping prices for the boxdrop carriers to equal the prices boxdrop is invoicing to you.' mod='boxdropshipment'}</p>
            <p>{l s='You may change the prices afterwards in the carrier shipment cost table.' mod='boxdropshipment'}</p>
            <p><strong>{l s='This is the default setup.' mod='boxdropshipment'}</strong></p>
          </div>
        </div>

        <div class="col-lg-6 panel">

          <div class="bshp-align-height-panelinner">
            <div class="form-group bshp-centered">
              <label class="control-label col-lg-6" for="FREE_SHIPPING_ABOVE">{l s='Free shipping above order total' mod='boxdropshipment'}</label>
              <div class="col-lg-6">
                <input type="text" class="bshp-freeprice-minordertotal" value="" id="FREE_SHIPPING_ABOVE" name="FREE_SHIPPING_ABOVE" />
              </div>
            </div>

            <div class="form-group bshp-centered">
              <label class="control-label col-lg-6" for="SHIPPING_PRICE_NOT_FREE">{l s='Default shipping price' mod='boxdropshipment'}</label>
              <div class="col-lg-6">
                <input type="text" class="bshp-freeprice-defaultprice" value="" id="SHIPPING_PRICE_NOT_FREE" name="SHIPPING_PRICE_NOT_FREE" />
              </div>
            </div>

            <div class="bshp-centered">
              <input type="button" class="btn btn-default bshp-free-price-preset" name="submit" value="{l s='Free shipping mode' mod='boxdropshipment'}" />
            </div>
          </div>

          <br />

          <div class="alert alert-warning bshp-align-height-warnings">
            <p>{l s='Clicking this button will immediately update the shipping prices. All orders with a total above the specified order total will be free. All others will get the specified fee.' mod='boxdropshipment'}</p>
            <p>{l s='You may change the prices afterwards in the carrier shipment cost table.' mod='boxdropshipment'}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="clear"></div>

    <input type="hidden" name="submitboxdropshipment" value="1" />
  </form>

  <script type="text/javascript" src="{$module_dir|escape:'htmlall':'UTF-8'}js/boxdrop.js"></script>
  <script type="text/javascript">
    $(document).ready(function() {

      boxdrop.init('{$module_dir|escape:'htmlall':'UTF-8'}');
      boxdrop.configPage.init();
    });
  </script>
