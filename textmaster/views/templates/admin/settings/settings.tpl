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
{if isset($smarty.get.menu) && $smarty.get.menu == 'settings'}
    {assign var=settings_view value=1}
{else}
    {assign var=settings_view value=0}
{/if}

{if !isset($values)}
    {assign var=values value=$settings}
{elseif !isset($settings)}
    {assign var=settings value=$values}
{/if}

{if $settings_view}
<form id="settings_form" class="defaultForm" action="{$saveAction|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data">
{/if}
    {if $settings_view}
    <fieldset id="fieldset_prices">
        <legend>{l s='Prices' mod='textmaster'}</legend>
        <p class="description"><a href="{$TEXTMASTER_PRICING_URL|escape:'htmlall':'UTF-8'}" target="_blank">{$TEXTMASTER_PRICING_URL|escape:'htmlall':'UTF-8'}</a></p>
    </fieldset>
    
    <fieldset id="fieldset_api">
        <legend>{l s='API access' mod='textmaster'}</legend> <label>{l s='API key:' mod='textmaster'}</label>
    
        <div class="margin-form">
            <input type="text" name="api_key" id="api_key" value="{$settings->api_key|escape:'htmlall':'UTF-8'}">
        </div>
    
        <div class="clear"></div><label>{l s='API secret' mod='textmaster'}</label>
    
        <div class="margin-form">
            <input type="text" name="api_secret" id="api_secret" value="{$settings->api_secret|escape:'htmlall':'UTF-8'}" />
        </div>
    
        <div class="clear"></div>
    
        <p class="description">{l s='You will find the api_key and api_secret on the website, in the plugins section' mod='textmaster'}</p>
        
        <div class="margin-form">
            <input type="submit" value="{l s='Save' mod='textmaster'}" name="savesettings" class="button" />
        </div>
    </fieldset>
    
    <fieldset id="fieldset_activities">
        <legend>{l s='Activities' mod='textmaster'}</legend>
    
        <div class="margin-form">
            <input type="checkbox" name="proofreading_on" id="proofreading_on" value="1" {if $settings->proofreading_on}checked="checked" {/if}/>
            <label for="proofreading_on" class="t"><strong>{l s='Proofreading' mod='textmaster'}</strong></label><br>
        </div>
    
        <div class="clear"></div>
    
        <div class="margin-form">
            <input type="checkbox" name="translation_on" id="translation_on" class="" value="1" {if $settings->translation_on}checked="checked" {/if}>
            <label for="translation_on" class="t"><strong>{l s='Translation' mod='textmaster'}</strong></label><br>
        </div>
    
        <div class="clear"></div>
    
        <p class="description">{l s='You can select for what type of job you want to activate the TextMaster module' mod='textmaster'}</p>
        
        <div class="margin-form">
            <input type="submit" value="{l s='Save' mod='textmaster'}" name="savesettings" class="button" />
        </div>
    </fieldset>
    {/if}
    
    {include file=$smarty.const._PS_MODULE_DIR_|cat:"textmaster/views/templates/admin/settings/proofreading.tpl"}
    {include file=$smarty.const._PS_MODULE_DIR_|cat:"textmaster/views/templates/admin/settings/translation.tpl"}
    
{if $settings_view}
</form>
{/if}