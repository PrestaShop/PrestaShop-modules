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
<link media="all" type="text/css" rel="stylesheet" href="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}textmaster/css/textmaster.css" />
<script src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}textmaster/js/jquery.bpopup.min.js" type="text/javascript"></script>
<script src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}textmaster/js/textmaster.js" type="text/javascript"></script>
<style>
    #fieldset_copywriting, #fieldset_proofreading
    {
        display: none;
    }
</style>
<h4>{l s='New TextMaster project' mod='textmaster'}</h4>
<input id="id_shop" type="hidden" value="{Context::getContext()->shop->id}" />
{if isset($smarty.get.id_product) && $smarty.get.id_product}
    {if !isset($connected)}
        <div class="warn draft" >
            <p>
                {l s='Please login / register' mod='textmaster'} <b><a href="{$module_url|escape:'UTF-8'}&configure=textmaster&menu=settings">{l s='Go to module settings' mod='textmaster'}</a></b>
            </p>
        </div>
    {else}
        <div class="error" id="textMaster_error">
            <span style="float:right">
                <a id="hideError" href="#"><img alt="X" src="../img/admin/close.png" /></a>
            </span>
            <div></div>
        </div>
        <script>
            var page_reference = 'product';
            var textmaster_ajax_uri = '{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}textmaster/textmaster.ajax.php';
            var textmaster_token = '{$token|escape:'htmlall':'UTF-8'}';
            var textmaster_module_url = '{$module_url|escape:'UTF-8'}';
            var id_product = '{$smarty.get.id_product|escape:'htmlall':'UTF-8'}';
            var word_counts = {Textmaster::getJson($counts)};    
            
            $(document).ready(function(){
                if ($('#ajax_running').length == 0)
                {
                    $('body').prepend('<div id="ajax_running"><img alt="" src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}textmaster/img/ajax-loader-yellow.gif"> {l s='Loading' mod='textmaster'}...</div>');
                }
                
                $("#_form").contents().unwrap(); // removes form element, created by FormHelper
                
                $('input[name="ctype"]').click(function(){
                    $('#product-tab-content-ModuleTextmaster fieldset').hide(); // hides every service type options fieldset
                    $('#fieldset_'+ $(this).val()).show();
                });
                
                {if !$settings->translation_on}
                $('input[name="ctype"]:first').trigger('click');
                {/if}
                
                ctype = $('input[name=ctype]').val();
                
                updateWordCounts();
                updateTotalWordsCount();
            });
        </script>
        {if $settings->copywriting_on OR $settings->proofreading_on OR $settings->translation_on}
        <div class="separation"></div>
        <div>
            <label class="text"> {l s='Type of service:' mod='textmaster'}</label>
            {if $settings->translation_on}
            <input type="radio" name="ctype" id="ctype_translation" value="translation" CHECKED />
            <label class="radioCheck" for="ctype_translation">{l s='Translation' mod='textmaster'}</label>
            {/if}
            {if $settings->proofreading_on}
            <input type="radio" name="ctype" id="ctype_proofreading" value="proofreading" />
            <label class="radioCheck" for="ctype_proofreading">{l s='Proofreading' mod='textmaster'}</label>
            {/if}
            {*if $settings->copywriting_on}
                <input type="radio" name="ctype" id="ctype_copywriting" value="copywriting" />
                <label class="radioCheck" for="ctype_copywriting">{l s='Copywriting' mod='textmaster'}</label>
            {/if*}
            <br />
            <div class="separation"></div>
            <label class="text"> {l s='Project data:' mod='textmaster'}</label>
            <ul class="listForm" id="project_data_list">
                <li>
                    <input type="checkbox" id="toggle_select_all"/>
                    <label class="t" placeholder="{l s='Deselect all' mod='textmaster'}">{l s='Select all' mod='textmaster'}</label>
                </li>
                <li>
                    <label class="product_data_list_group">{l s='Product information:' mod='textmaster'}</label>
                </li>
                <li>
                    <input type="checkbox" value="name" id="project_data_name" name="project_data[]" DISABLED />
                    <label class="t" for="project_data_name">{l s='Product title' mod='textmaster'}</label>
                    <div class="word_count"><span class="word_count_value">0</span> {l s='words' mod='textmaster'}</div>
                </li>
                <li>
                    <input type="checkbox" value="description" id="project_data_description" name="project_data[]" DISABLED />
                    <label class="t" for="project_data_description">{l s='Description' mod='textmaster'}</label>
                    <div class="word_count"><span class="word_count_value">0</span> {l s='words' mod='textmaster'}</div>
                </li>
                <li>
                    <input type="checkbox" value="description_short" id="project_data_description_short" name="project_data[]" DISABLED />
                    <label class="t" for="project_data_description_short">{l s='Short description' mod='textmaster'}</label>
                    <div class="word_count"><span class="word_count_value">0</span> {l s='words' mod='textmaster'}</div>
                </li>
                <li>
                    <input type="checkbox" value="tags" id="project_data_tags" name="project_data[]" DISABLED />
                    <label class="t" for="project_data_tags">{l s='Tags' mod='textmaster'}</label>
                    <div class="word_count"><span class="word_count_value">0</span> {l s='words' mod='textmaster'}</div>
                </li>
                <li>
                    <label class="product_data_list_group">{l s='SEO:' mod='textmaster'}</label>
                </li>
                <li>
                    <input type="checkbox" value="meta_title" id="project_meta_title" name="project_data[]" DISABLED />
                    <label class="t" for="project_meta_title">{l s='Meta title' mod='textmaster'}</label>
                    <div class="word_count"><span class="word_count_value">0</span> {l s='words' mod='textmaster'}</div>
                </li>
                <li>
                    <input type="checkbox" value="meta_description" id="project_data_meta_description" name="project_data[]" DISABLED />
                    <label class="t" for="project_data_meta_description">{l s='Meta description' mod='textmaster'}</label>
                    <div class="word_count"><span class="word_count_value">0</span> {l s='words' mod='textmaster'}</div>
                </li>
                <li>
                    <input type="checkbox" value="meta_keywords" id="project_data_meta_keywords" name="project_data[]" DISABLED />
                    <label class="t" for="project_data_meta_keywords">{l s='Meta keywords' mod='textmaster'}</label>
                    <div class="word_count"><span class="word_count_value">0</span> {l s='words' mod='textmaster'}</div>
                </li>
                <li>
                    <input type="checkbox" value="link_rewrite" id="project_data_link_rewrite" name="project_data[]" DISABLED />
                    <label class="t" for="project_data_link_rewrite">{l s='Friendly URL' mod='textmaster'}</label>
                    <div class="word_count"><span class="word_count_value">0</span> {l s='words' mod='textmaster'}</div>
                </li>
            </ul>
            <div class="total_words_count_container">
                {l s='Total count:' mod='textmaster'} <span id="total_words">0</span> <span>{l s='words' mod='textmaster'}</span>
            </div>
            <div class="separation"></div>
            {include file=$smarty.const.TEXTMASTER_TPL_DIR|cat:"admin/settings/settings.tpl"}
            <div class="separation"></div>
            <div class="margin-form">
                <input type="button" class="button" id="create_textmaster_project" value="{l s='Create project' mod='textmaster'}">
            </div>  
        </div>
        {else}
            <div class="warn draft" >
                <p>
                    {l s='All activities are disabled.' mod='textmaster'} <b><a href="{$link->getAdminLink('AdminModules')|escape:'htmlall':'UTF-8'}&configure=textmaster&menu=settings">{l s='Go to module settings' mod='textmaster'}</a></b>
                </p>
            </div>
        {/if}
    {/if}
{else}
    <div class="warn draft" >
        <p>
            {l s='Product has to be saved first' mod='textmaster'}
        </p>
    </div>
{/if}