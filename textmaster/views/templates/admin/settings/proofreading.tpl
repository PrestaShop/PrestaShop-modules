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

<fieldset id="fieldset_proofreading">
    <legend>{l s='Default proofreading settings' mod='textmaster'}</legend>
    <label>{l s='Language:' mod='textmaster'}</label>

    <div class="margin-form">
        <select name="proofreading_language_from" id="proofreading_language_from">
        {foreach from=$languages item=language}
            <option value="{$language.code|escape:'htmlall':'UTF-8'}"{if $values->proofreading_language_from == $language.code} selected="selected"{/if}>
                {$language.value|escape:'htmlall':'UTF-8'}
            </option>
        {/foreach}
        </select>

        <p class="preference_description">{l s='Select the language of your text' mod='textmaster'}</p>
    </div>

    <div class="clear"></div><label>{l s='Category:' mod='textmaster'}</label>

    <div class="margin-form">
        <select name="proofreading_category" class="" id="proofreading_category">
        {foreach from=$categories item=category}
            <option value="{$category.code|escape:'htmlall':'UTF-8'}"{if $values->proofreading_category == $category.code} selected="selected"{/if}>
                {$category.value|escape:'htmlall':'UTF-8'}
            </option>
        {/foreach}
        </select>

        <p class="preference_description">{l s='Select a topic for your text' mod='textmaster'}</p>
    </div>

    <div class="clear"></div><label>{l s='Briefing message:' mod='textmaster'}</label>

    <div class="margin-form">
        <textarea name="proofreading_project_briefing" id="proofreading_project_briefing" cols="60" rows="10">{$values->proofreading_project_briefing|escape:'htmlall':'UTF-8'}</textarea>
        <p class="preference_description">{l s='This is where you have to provide the general instructions for the project. All the information must be common to all documents and allow the authors to carry out their task in the best possible conditions. Providing more details will increase your chances' mod='textmaster'}</p>
    </div>
    <div class="clear"></div>
    
    {if !$settings_view}
    <div class="separation"></div>
    <div class="total_price_container">
        <label>{l s='Total project price:' mod='textmaster'}</label>
        <div class="margin-form">													
            <span class="total_project_price">
                <span class="price_empty">{l s='Price cannot be evaluated yet' mod='textmaster'}</span>
                <span class="price_value"></span>
            </span>
            <p class="preference_description">
                {l s='No project data selected yet.' mod='textmaster'}
            </p>
        </div>
    </div>
    <div class="separation"></div>
    {/if}
    
    {if !$settings_view}<div class="level_service_wrapper">{/if}
        <label>{l s='Level of service:' mod='textmaster'}</label>
        <div class="margin-form">
            <select name="proofreading_language_level" class="" id="proofreading_language_level">
                {foreach from=$language_levels item=level}
                    <option value="{$level.name|escape:'htmlall':'UTF-8'}"{if $values->proofreading_language_level == $level.name} selected="selected"{/if}>
                        {$level.value|escape:'htmlall':'UTF-8'}: {$pricings.types.proofreading[$level['name']]|escape:'htmlall':'UTF-8'} {$pricings.code|escape:'htmlall':'UTF-8'}
                    </option>
                {/foreach}
            </select>
    
            <p class="preference_description">{l s='Select the level of your author: Regular> native speaker; Premium>Freelance professional;' mod='textmaster'}</p>
        </div>
        <div class="clear"></div>
    {if !$settings_view}</div>{/if}
    
    <label>{l s='Quality control:' mod='textmaster'}</label>
    <div class="margin-form">
        <input type="checkbox" name="proofreading_quality_on" id="proofreading_quality_on" value="1" {if $values->proofreading_language_level}checked="checked" {/if}/><br>
        <p class="preference_description">{l s='The work of your author will be controlled by TextMaster. +' mod='textmaster'} <span class="price">{$pricings.types.copywriting.quality|escape:'htmlall':'UTF-8'} {$pricings.code|escape:'htmlall':'UTF-8'}</span> {l s='per word (Option is not available if level of service selected is "Regular"' mod='textmaster'}</p>
    </div>
    <div class="clear"></div>
    
    {if !$settings_view}
    <label>{l s='Restrict this project to my TextMasters:' mod='textmaster'}</label>
    <div class="margin-form">
        <input type="checkbox" value="1" class="restrict_to_textmasters" name="restrict_to_textmasters" {if isset($textmaster_project) && isset($textmaster_project.restrict_to_textmasters) && isset($textmaster_project.ctype) && $textmaster_project.ctype == 'copywriting' && $textmaster_project.restrict_to_textmasters}CHECKED{/if} />
        <label class="t" for="restrict_to_textmasters"></label><br>
        <p class="preference_description">
            {l s='Restrict this project to my TextMasters.' mod='textmaster'} <span class="price">{l s='free' mod='textmaster'}</span>
        </p>
        <p class="preference_description authors_selection_description_container">
            <span class="selected_authors"><span class="selected_authors_value">{if isset($textmaster_project) && isset($textmaster_project.authors) && isset($textmaster_project.ctype) && $textmaster_project.ctype == 'copywriting'}{$textmaster_project.authors|@count|escape:'htmlall':'UTF-8'}{else}0{/if}</span> {l s='authors selected' mod='textmaster'}</span>
            <input type="button" onclick="displayAuthorsSelection(this)" value="{l s='Select textmasters' mod='textmaster'}" class="button" />
        </p>
        <div class="authors_selection_container" id="proofreading_authors_selection_container">
            <h4>{l s='My textmasters' mod='textmaster'}</h4>
            <div class="separation"></div>
            <div class="my_authors_container">
            {if isset($authors)}
                {foreach from=$authors item=author}
                    <input type="checkbox" value="{$author.author_id|escape:'htmlall':'UTF-8'}" {if isset($textmaster_project.authors) && isset($textmaster_project.ctype) && $textmaster_project.ctype == 'copywriting' && in_array($author.author_id, $textmaster_project.authors)}CHECKED{/if} />
                    <label style="float:none;width:auto">
                        {$author.author_ref|escape:'htmlall':'UTF-8'}
                    </label>
                    <p class="preference_description">
                        {$author.description|escape:'htmlall':'UTF-8'|truncate:75:'...'}
                    </p>
                {/foreach}
            {/if}
            </div>
            <div class="separation"></div>
            <input type="button" class="authors_cancel button" value="{l s='Cancel' mod='textmaster'}" style="float: left" />
            <input type="button" class="authors_confirm button" value="{l s='Confirm' mod='textmaster'}" style="float:right" />
            <div class="clear"></div>
        </div>
    </div>
    {/if}
    
    <label>{l s='Expert:' mod='textmaster'}</label>
    <div class="margin-form">
        <input type="checkbox" name="proofreading_expertise_on" id="proofreading_expertise_on" class="" value="1" {if $values->proofreading_expertise_on}checked="checked" {/if}/><br>
        <p class="preference_description">{l s='We provide you with an expert in the selected category. +' mod='textmaster'} <span class="price">{$pricings.types.proofreading.expertise|escape:'htmlall':'UTF-8'} {$pricings.code|escape:'htmlall':'UTF-8'}</span> {l s='per word' mod='textmaster'}</p>
    </div>
    <div class="clear"></div>

    {if $settings_view or isset($smarty.get.menu) && $smarty.get.menu=='create_project'}
    <div class="margin-form">
        <input type="radio" name="proofreading_same_author_must_do_entire_project" id="one_author_true" value="1"{if $values->proofreading_same_author_must_do_entire_project} checked="checked"{/if}>
        <label class="t" for="one_author_true">{l s='One author only' mod='textmaster'}</label>
        
        <input type="radio" name="proofreading_same_author_must_do_entire_project" id="one_author_false" value="0"{if !$values->proofreading_same_author_must_do_entire_project} checked="checked"{/if}>
        <label class="t" for="one_author_false">{l s='Multiple authors' mod='textmaster'}</label>

        <p class="preference_description">{l s='One author only > Slower but ensures editorial continuity; Multiple authors > Faster but without editorial continuity' mod='textmaster'}</p>
    </div>
    <div class="clear"></div>
    {/if}
    
    <label>{l s='Target audience:' mod='textmaster'}</label>
    <div class="margin-form">
        <select name="proofreading_target_reader_groups" id="proofreading_target_reader_groups">
            {foreach from=$audiences item=audience}
                <option value="{$audience.name|escape:'htmlall':'UTF-8'}"{if $values->proofreading_target_reader_groups == $audience.name} selected="selected"{/if}>
                    {$audience.value|escape:'htmlall':'UTF-8'}
                </option>
            {/foreach}
        </select>

        <p class="preference_description">{l s='Who is the target audience of your text?' mod='textmaster'}</p>
    </div>
    <div class="clear"></div>
    {if $settings_view}
    <div class="margin-form">
        <input type="submit" value="{l s='Save' mod='textmaster'}" name="savesettings" class="button" />
    </div>
    {/if}
</fieldset>