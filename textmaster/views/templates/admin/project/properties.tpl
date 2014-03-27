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
<script src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}textmaster/js/jquery.bpopup.min.js" type="text/javascript"></script>
<style>
    #fieldset_copywriting, #fieldset_proofreading
    {
        display: none;
    }
</style>
<div id="product-tab-content-ModuleTextmaster">
    <h4>{l s='Project properties' mod='textmaster'}</h4>
    <div class="error" id="textMaster_error">
        <span style="float:right">
            <a id="hideError" href="#"><img alt="X" src="../img/admin/close.png" /></a>
        </span>
        <div></div>
    </div>
    <script>
        var page_reference = 'module';
        var textmaster_ajax_uri = '{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}textmaster/textmaster.ajax.php';
        var textmaster_module_url = '{$module_link|escape:'UTF-8'}';
        var textmaster_token = '{$token|escape:'htmlall':'UTF-8'}';
        var id_product = '{$id_product|escape:'UTF-8'}';
        var word_counts = {Textmaster::getJson($counts)}; 
        $(document).ready(function(){   
            if ($('#ajax_running').length == 0)
            {
                $('body').prepend('<div id="ajax_running"><img alt="" src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}textmaster/img/ajax-loader-yellow.gif"> {l s='Loading' mod='textmaster'}...</div>');
            }
            
            $('input[name="ctype"]').click(function(){
                $('#product-tab-content-ModuleTextmaster fieldset').hide(); // hides every service type options fieldset
                $('#fieldset_'+ $(this).val()).show();
            });
            
            {if isset($textmaster_project.ctype)}
                $('input[name="ctype"][value="{$textmaster_project.ctype|escape:'htmlall':'UTF-8'}"]').trigger('click');
            {else}
                {if !$settings->translation_on}
                    $('input[name="ctype"]:first').trigger('click');
                {/if}
            {/if}
            
            ctype = $('input[name=ctype]:checked').val();
            
            {if isset($textmaster_project_authors)}
                {section name=ii loop=$textmaster_project_authors}
                    $('#fieldset_'+ctype).prepend('<input type="hidden" name="authors[]" value="{$textmaster_project_authors[ii]|escape:'htmlall':'UTF-8'}">');
                {/section}
            {/if}
            
            updateWordCounts();
            updateTotalWordsCount();
            updateProjectPrice();
        });
    </script>
    {if $settings->copywriting_on OR $settings->proofreading_on OR $settings->translation_on}
    <div class="separation"></div>
    <label class="text"> {l s='Project name:' mod='textmaster'}</label>
    <input type="text" name="project_name" id="project_name" style="width:268px" value="{if isset($textmaster_project.project_name)}{$textmaster_project.project_name|escape:'htmlall':'UTF-8'}{/if}" />
    <br /><br />
    <div>
        <label class="text"> {l s='Type of service:' mod='textmaster'}</label>
        {if $settings->translation_on}
            <input type="radio" name="ctype" id="ctype_translation" value="translation" {if !isset($textmaster_project.ctype) || isset($textmaster_project.ctype) && $textmaster_project.ctype == 'translation'}CHECKED{/if} />
            <label class="radioCheck" for="ctype_translation">{l s='Translation' mod='textmaster'}</label>
        {/if}
        {if $settings->proofreading_on}
            <input type="radio" name="ctype" id="ctype_proofreading" value="proofreading" {if isset($textmaster_project.ctype) && $textmaster_project.ctype == 'proofreading'}CHECKED{/if} />
            <label class="radioCheck" for="ctype_proofreading">{l s='Proofreading' mod='textmaster'}</label>
        {/if}
        <br />
        <div class="separation"></div>
        <label class="text"> {l s='Project data:' mod='textmaster'}</label>
        <ul class="listForm" id="project_data_list">
            <li>
                <input type="checkbox" id="toggle_select_all" {if isset($textmaster_project.project_data) && $textmaster_project.project_data|@count == 8}CHECKED{/if} />
                <label class="t" placeholder="{l s='Deselect all' mod='textmaster'}">{l s='Select all' mod='textmaster'}</label>
            </li>
            <li>
                <label class="product_data_list_group">{l s='Product information:' mod='textmaster'}</label>
            </li>
            <li>
                <input type="checkbox" value="name" id="project_data_name" name="project_data[]" {if isset($textmaster_project.project_data) && in_array('name', $textmaster_project.project_data)}CHECKED{/if} DISABLED />
                <label class="t" for="project_data_name">{l s='Product title' mod='textmaster'}</label>
                <div class="word_count"><span class="word_count_value">0</span> {l s='words' mod='textmaster'}</div>
            </li>
            <li>
                <input type="checkbox" value="description" id="project_data_description" name="project_data[]" {if isset($textmaster_project.project_data) && in_array('description', $textmaster_project.project_data)}CHECKED{/if} DISABLED />
                <label class="t" for="project_data_description">{l s='Description' mod='textmaster'}</label>
                <div class="word_count"><span class="word_count_value">0</span> {l s='words' mod='textmaster'}</div>
            </li>
            <li>
                <input type="checkbox" value="description_short" id="project_data_description_short" name="project_data[]" {if isset($textmaster_project.project_data) && in_array('description_short', $textmaster_project.project_data)}CHECKED{/if} DISABLED />
                <label class="t" for="project_data_description_short">{l s='Short description' mod='textmaster'}</label>
                <div class="word_count"><span class="word_count_value">0</span> {l s='words' mod='textmaster'}</div>
            </li>
            <li>
                <input type="checkbox" value="tags" id="project_data_tags" name="project_data[]" {if isset($textmaster_project.project_data) && in_array('tags', $textmaster_project.project_data)}CHECKED{/if} DISABLED />
                <label class="t" for="project_data_tags">{l s='Tags' mod='textmaster'}</label>
                <div class="word_count"><span class="word_count_value">0</span> {l s='words' mod='textmaster'}</div>
            </li>
            <li>
                <label class="product_data_list_group">{l s='SEO:' mod='textmaster'}</label>
            </li>
            <li>
                <input type="checkbox" value="meta_title" id="project_meta_title" name="project_data[]" {if isset($textmaster_project.project_data) && in_array('meta_title', $textmaster_project.project_data)}CHECKED{/if} DISABLED />
                <label class="t" for="project_meta_title">{l s='Meta title' mod='textmaster'}</label>
                <div class="word_count"><span class="word_count_value">0</span> {l s='words' mod='textmaster'}</div>
            </li>
            <li>
                <input type="checkbox" value="meta_description" id="project_data_meta_description" name="project_data[]" {if isset($textmaster_project.project_data) && in_array('meta_description', $textmaster_project.project_data)}CHECKED{/if} DISABLED />
                <label class="t" for="project_data_meta_description">{l s='Meta description' mod='textmaster'}</label>
                <div class="word_count"><span class="word_count_value">0</span> {l s='words' mod='textmaster'}</div>
            </li>
            <li>
                <input type="checkbox" value="meta_keywords" id="project_data_meta_keywords" name="project_data[]" {if isset($textmaster_project.project_data) && in_array('meta_keywords', $textmaster_project.project_data)}CHECKED{/if} DISABLED />
                <label class="t" for="project_data_meta_keywords">{l s='Meta keywords' mod='textmaster'}</label>
                <div class="word_count"><span class="word_count_value">0</span> {l s='words' mod='textmaster'}</div>
            </li>
            <li>
                <input type="checkbox" value="link_rewrite" id="project_data_link_rewrite" name="project_data[]" {if isset($textmaster_project.project_data) && in_array('link_rewrite', $textmaster_project.project_data)}CHECKED{/if} DISABLED />
                <label class="t" for="project_data_link_rewrite">{l s='Friendly URL' mod='textmaster'}</label>
                <div class="word_count"><span class="word_count_value">0</span> {l s='words' mod='textmaster'}</div>
            </li>
        </ul>
        <div class="total_words_count_container">
            {l s='Total count:' mod='textmaster'} <span id="total_words">0</span> <span>{l s='words' mod='textmaster'}</span>
        </div>
        <div class="separation"></div>
        {include file=$smarty.const.TEXTMASTER_TPL_DIR|cat:"admin/settings/settings.tpl" values=$projectObj}
        <div class="separation"></div> 
    </div>
    {else}
        <div class="warn draft" >
            <p>
                {l s='All activities are disabled.' mod='textmaster'} <b><a href="{$link->getAdminLink('AdminModules')|escape:'htmlall':'UTF-8'}&configure=textmaster&menu=settings">{l s='Go to module settings' mod='textmaster'}</a></b>
            </p>
        </div>
    {/if}
</div>