{*
* 2013 Give.it
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to support@give.it so we can send you a copy immediately.
*
* @author JSC INVERTUS www.invertus.lt <help@invertus.lt>
* @copyright 2013 Give.it
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* International Registered Trademark & Property of Give.it
*}

{if $menu == 'technical_settings'}
    	<iframe src="{$smarty.const._GIVEIT_EXTERNAL_ACCESS_URI_}?shop_id={$shop_id|escape:'htmlall':'UTF-8'}&website={$base_dir|escape:'htmlall':'UTF-8'}&name={$shop_name|escape:'htmlall':'UTF-8'}&currency={$iso_code|escape:'htmlall':'UTF-8'}&user_first_name={$first_name|escape:'htmlall':'UTF-8'}&user_last_name={$last_name|escape:'htmlall':'UTF-8'}&user_email={$email|escape:'htmlall':'UTF-8'}" style="border: 1px solid #CCCED7;" width="100%" height="800" frameborder="0" scrolling="no" marginwidth="5" marginheight="5"></iframe>
    <br /><br />
{else}

    <fieldset id="portal">
        <legend>
            <img src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}giveit/img/settings.png" alt="{l s='Settings |' mod='giveit'}" />
            {l s='Settings & Orders' mod='giveit'}
        </legend>
        
        <p class='Bloc'>
            {l s='Log in to your personal Give.it portal to manage your sales and settings.' mod='giveit'}
            {l s='You can retrieve your keys from the settings page : Settings > Technical. Copy the keys here and press "save".' mod='giveit'}
        </p>
<br>
            <a class='button' href="{$smarty.const._GIVE_IT_LOGIN_URI_|escape:'htmlall':'UTF-8'}" target="_blank">{l s='Log in to the Give.it admin portal' mod='giveit'}</a>
</fieldset>
<br>
{/if}
<form id="configuration_form" class="defaultForm" action="{$saveAction|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data">
    <fieldset id="connectivity">
        <legend>
            <img src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}giveit/img/settings.png" alt="{l s='Settings |' mod='giveit'}" />
            {l s='Technical settings' mod='giveit'}
        </legend>
        
        <p class='Bloc'>
            {l s='These fields contain the "API keys" you received when signing up for Give.it. If you have forgotten your keys please' mod='giveit'}
            <a href="{$smarty.const._GIVE_IT_LOGIN_URI_|escape:'htmlall':'UTF-8'}" target="_blank">{l s='log in here' mod='giveit'}</a>
            {l s='and retrieve your keys from the settings page : Settings > Technical. Copy the keys here and press "save".' mod='giveit'}
        </p>
	<br>
	<label>
            {l s='Public Key' mod='giveit'}
        </label>
        <div class="margin-form">
            <input size=32" type="text" name="public_key" id="public_key" value="{$public_key|escape:'htmlall':'UTF-8'}">
        </div>
        <div class="clear"></div>
        
        <label>
            {l s='Data Key' mod='giveit'}
        </label>
        <div class="margin-form">
            <input size=32" type="text" name="data_key" id="data_key" value="{$data_key|escape:'htmlall':'UTF-8'}" />
        </div>
        <div class="clear"></div>
        
        <label>
            {l s='Private Key' mod='giveit'}
        </label>
        <div class="margin-form">
            <input size=32" type="text" name="private_key" id="private_key" value="{$private_key|escape:'htmlall':'UTF-8'}" />
        </div>
        <div class="clear"></div>
        <div class="margin-form">
            <input type="submit" class="button" name="{if $menu != 'technical_settings'}saveConfiguration{else}saveApiKeys{/if}" value="{l s='Save' mod='giveit'}" />
        </div>
    </fieldset>
    {if $menu != 'technical_settings'}
        <br />
        <fieldset id="global_settings">
            <legend>
                <img src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}giveit/img/settings.png" alt="{l s='Settings |' mod='giveit'}" />
                {l s='Global settings' mod='giveit'}
            </legend>
			
			<label>
                {l s='Mode' mod='giveit'}
            </label>
            <div class="margin-form">
				<input id="production_mode" type="radio" name="{GiveIt::MODE}" value="{GiveIt::PRODUCTION}" {if $mode == GiveIt::PRODUCTION}checked="checked" {/if} />
				<label class="t" for="production_mode">
					{l s='Production' mod='giveit'}
				</label>
				<input id="debug_mode" type="radio" name="{GiveIt::MODE}" value="{GiveIt::DEBUG}" {if $mode == GiveIt::DEBUG}checked="checked" {/if} />
				<label class="t" for="debug_mode">
					{l s='Debug' mod='giveit'}
				</label>
            </div>
            <div class="clear"></div>
			
            <label>
                {l s='Give.it button is enabled' mod='giveit'}
            </label>
            <div class="margin-form">
                <input type="checkbox" name="button_active" id="button_active" value="1" {if $button_active}checked="checked" {/if}/>
                <p class="preference_description">
                    {l s='With this setting you can disable the Give.it button in the current store.When the button is enabled you can still disable buttons for a category of products in the "Category settings" or for a single product in "Product settings"' mod='giveit'}
                </p>
            </div>
            <div class="clear"></div>
            <div class="margin-form">
                <input type="submit" class="button" name="saveConfiguration" value="{l s='Save' mod='giveit'}" />
            </div>
        </fieldset>
        <br />
        <fieldset id="button_position">
            <legend>
                <img src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}giveit/img/settings.png" alt="{l s='Settings |' mod='giveit'}" />
                {l s='Appearance' mod='giveit'}
            </legend>
            <p class="Bloc">
                {l s='By default the Give.it button can be positioned on your product description page on four different fixed locations. If you want more control on where the button should be positioned and how the button looks you can also manually change the template source code. Documentation for that can be found' mod='giveit' mod='giveit'}
                <a href="{$smarty.const._GIVE_IT_DOCUMENTATION_URI_|escape:'htmlall':'UTF-8'}" target="_blank">{l s='here' mod='giveit'}</a>
            </p>
            <br>
            <label>
                {l s='Left column underneath product' mod='giveit'}
            </label>
            <div class="margin-form">
                <input type="radio" name="button_position" value="{GiveIt::EXTRA_LEFT}" {if $button_position == GiveIt::EXTRA_LEFT}checked="checked" {/if} />
                <p class="preference_description">
                    {l s='This hook displays new elements in the left-hand column of the product page' mod='giveit'}
                </p>
            </div>
            <div class="clear"></div>
            
            <label>
                {l s='Product actions' mod='giveit'}
            </label>
            <div class="margin-form">
                <input type="radio" name="button_position" value="{GiveIt::PRODUCT_ACTIONS}" {if $button_position == GiveIt::PRODUCT_ACTIONS}checked="checked" {/if} />
                <p class="preference_description">
                    {l s='This hook adds new action buttons on the product page' mod='giveit'}
                </p>
            </div>
            <div class="clear"></div>
            
            <label>
                {l s='Right column underneath product' mod='giveit'}
            </label>
            <div class="margin-form">
                <input type="radio" name="button_position" value="{GiveIt::EXTRA_RIGHT}" {if $button_position == GiveIt::EXTRA_RIGHT}checked="checked" {/if} />
                <p class="preference_description">
                    {l s='This hook displays new elements in the right-hand column of the product page' mod='giveit'}
                </p>
            </div>
            <div class="clear"></div>
            
            <label>
                {l s='Footer' mod='giveit'}
            </label>
            <div class="margin-form">
                <input type="radio" name="button_position" value="{GiveIt::PRODUCT_FOOTER}" {if $button_position == GiveIt::PRODUCT_FOOTER}checked="checked" {/if} />
                <p class="preference_description">
                    {l s='This hook adds new blocks under the product\'s description' mod='giveit'}
                </p>
            </div>
            <div class="clear"></div>
            
             <label>
                {l s='Custom position' mod='giveit'}
            </label>
            <div class="margin-form">
                <input type="radio" name="button_position" value="{GiveIt::CUSTOM_POSITION}" {if $button_position == GiveIt::CUSTOM_POSITION}checked="checked" {/if} />
                <p class="preference_description">
                    {l s='Displays button in custom position' mod='giveit'}
                </p>
            </div>
            <div class="clear"></div>
            
            <div class="margin-form">
                <input type="submit" class="button" name="saveConfiguration" value="{l s='Save' mod='giveit'}" />
            </div>
            <div class="clear"></div>
        </fieldset>
    {/if}
</form>
