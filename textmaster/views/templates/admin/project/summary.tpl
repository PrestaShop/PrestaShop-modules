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
<label>{l s='Total word count:' mod='textmaster'}</label>
<div class="margin-form">
    <span class="project_total">{$total_words|escape:'htmlall':'UTF-8'} {l s='words' mod='textmaster'}</span>
</div>

<label>{l s='Total project price:' mod='textmaster'}</label>
<div class="margin-form">
    <span class="project_total">{$total_price|escape:'htmlall':'UTF-8'}</span>
</div>

<div class="margin-form">
    <a href="{$module_link|escape:'htmlall':'UTF-8'}&step=properties">
        <input type="button" value="{l s='Change project properties >' mod='textmaster'}" class="button" />
    </a>
</div>
<div class="separation"></div>
<fieldset>
    {$ctype = $summary->getProjectData('ctype')|escape:'htmlall':'UTF-8'}
    <label>{l s='Type:' mod='textmaster'}</label>
    <div class="margin-form">{if $ctype == 'translation'}{l s='Translation' mod='textmaster'}{else}{l s='Proofreading' mod='textmaster'}{/if}</div>
    <div class="clear"></div>
    
    <label>{l s='Project name:' mod='textmaster'}</label>
    <div class="margin-form">{$summary->getProjectData('project_name')|escape:'htmlall':'UTF-8'}</div>
    <div class="clear"></div>
    <label>{l s='Source language:' mod='textmaster'}</label>
    <div class="margin-form">{$languages[$summary->getProjectData($ctype|cat:'_language_from')].value|escape:'htmlall':'UTF-8'}</div>
    <div class="clear"></div>
    
    {if $summary->getProjectData($ctype|cat:'_language_to')}
    <label>{l s='Target language:' mod='textmaster'}</label>
    <div class="margin-form">{$languages[$summary->getProjectData($ctype|cat:'_language_to')].value|escape:'htmlall':'UTF-8'}</div>
    <div class="clear"></div>
    <label>{l s='Category:' mod='textmaster'}</label>
    <div class="margin-form">
        {foreach from=$categories item=category}
            {if $category.code == $summary->getProjectData($ctype|cat:'_category')}
                {$category.value|escape:'htmlall':'UTF-8'}
            {/if}
        {/foreach}
    </div>
    <div class="clear"></div>
    {/if}
    
    <label>{l s='Briefing message:' mod='textmaster'}</label>
    <div class="margin-form">{$summary->getProjectData($ctype|cat:'_project_briefing')|escape:'htmlall':'UTF-8'}</div>
    <div class="clear"></div>
    
    <label>{l s='Project will be done by:' mod='textmaster'}</label>
    <div class="margin-form">
    {if $summary->getProjectData($ctype|cat:'_same_author_must_do_entire_project') === 0}
        {l s='Multiple authors' mod='textmaster'}
    {else}
        {l s='One author only' mod='textmaster'}
    {/if}
    </div>
    <div class="clear"></div>
    
    <label>{l s='Authors selected:' mod='textmaster'}</label>
    <div class="margin-form">{$summary->getSelectedAuthors()|count|escape:'htmlall':'UTF-8'}</div>
    <div class="clear"></div>
    <label>{l s='Level of service:' mod='textmaster'}</label>
    <div class="margin-form">
        {foreach from=$language_levels item=level}
            {if $level.name == $summary->getProjectData($ctype|cat:'_language_level')}
                {$level.value|escape:'htmlall':'UTF-8'}
            {/if}
        {/foreach}
    </div>
    <div class="clear"></div>
    
    <label>{l s='Quality control:' mod='textmaster'}</label>
    <div class="margin-form">{if $summary->getProjectData($ctype|cat:'_quality_on')}{l s='Yes' mod='textmaster'}{else}{l s='No' mod='textmaster'}{/if}</div>
    <div class="clear"></div>
    
    <label>{l s='Restrict this project to my TextMasters:' mod='textmaster'}</label>
    <div class="margin-form">{if $summary->getProjectData('restrict_to_textmasters')}{l s='Yes' mod='textmaster'}{else}{l s='No' mod='textmaster'}{/if}</div>
    <div class="clear"></div>
    
    <label>{l s='Expert:' mod='textmaster'}</label>
    <div class="margin-form">{if $summary->getProjectData($ctype|cat:'_expertise_on')}{l s='Yes' mod='textmaster'}{else}{l s='No' mod='textmaster'}{/if}</div>
    <div class="clear"></div>
        
    {if $summary->getProjectData($ctype|cat:'_vocabulary_type')}
    <label>{l s='Vocabulary level:' mod='textmaster'}</label>
    <div class="margin-form">
        {foreach from=$vocabulary_levels item=level}
            {if $level.name == $summary->getProjectData($ctype|cat:'_vocabulary_type')}
                {$level.value|escape:'htmlall':'UTF-8'}
            {/if}
        {/foreach}
    </div>
    <div class="clear"></div>
    {/if}
    
    <label>{l s='Target audience:' mod='textmaster'}</label>
    <div class="margin-form">
        {foreach from=$audiences item=audience}
            {if $audience.name == $summary->getProjectData($ctype|cat:'_target_reader_groups')}
                {$audience.value|escape:'htmlall':'UTF-8'}
            {/if}
        {/foreach}
    </div>
    <div class="clear"></div>
    
    {if $summary->getProjectData($ctype|cat:'_grammatical_person')}
    <label>{l s='Grammatical person:' mod='textmaster'}</label>
    <div class="margin-form">
        {foreach from=$grammatical_persons item=person}
            {if $person.name == $summary->getProjectData($ctype|cat:'_grammatical_person')}
                {$person.value|escape:'htmlall':'UTF-8'}
            {/if}
        {/foreach}
    </div>
    <div class="clear"></div>
    {/if}
</fieldset>

<div class="separation"></div>
<h4>{l s='Documents' mod='textmaster'}</h4>
{include file=$smarty.const.TEXTMASTER_TPL_DIR|cat:"admin/project/documents.tpl"}