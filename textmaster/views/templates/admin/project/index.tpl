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
<script>
    var textmaster_ajax_uri = '{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}textmaster/textmaster.ajax.php';
    var textmaster_token = '{$textmaster_token|escape:'htmlall':'UTF-8'}';
    var id_root_category = "{Category::getRootCategory()->id_category}";
    var id_lang = '{$id_lang|escape:'htmlall':'UTF-8'}';
    
    $(document).ready(function(){
        if ($('#ajax_running').length == 0)
        {
            $('body').prepend('<div id="ajax_running"><img alt="" src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}textmaster/img/ajax-loader-yellow.gif"> {l s='Loading' mod='textmaster' js=1}...</div>');
        }
    });
</script>
<div id="textmaster_project_view">
    <input id="id_shop" type="hidden" value="{Context::getContext()->shop->id}" />
    {include file=$smarty.const.TEXTMASTER_TPL_DIR|cat:"admin/project/menu.tpl"}
    <form enctype="multipart/form-data" method="post" action="" id="product_form">
        {if !isset($smarty.get.step) OR (isset($smarty.get.step) AND $smarty.get.step eq 'products')}
        <input type="hidden" name="project_step" value="products" />
        
        <div id="tab_products">
            <h4>{l s='Select products you want to add to your project:' mod='textmaster'}</h4>
            <div class="separation"></div>
            <div class="info">{l s='For faster loading times, we suggest uploading no more than 50 products at a time' mod='textmaster'}</div>
            {include file=$smarty.const.TEXTMASTER_TPL_DIR|cat:"admin/project/form_category.tpl"}
            <div class="separation"></div>
            
            <h4>{l s='Products you have selected:' mod='textmaster'}</h4>
            {include file=$smarty.const.TEXTMASTER_TPL_DIR|cat:"admin/project/products_to_select.tpl"}
        </div>
        {elseif $smarty.get.step eq 'properties'}
        <input type="hidden" name="project_step" value="properties" />
        <div id="tab_properties">
            {include file=$smarty.const.TEXTMASTER_TPL_DIR|cat:"admin/project/properties.tpl"}
        </div>
        {elseif $smarty.get.step eq 'summary'}
        <input type="hidden" name="project_step" value="summary" />
        <div id="tab_summary">
            <h4>{l s='Summary' mod='textmaster'}</h4>
            <div class="separation"></div>
            {include file=$smarty.const.TEXTMASTER_TPL_DIR|cat:"admin/project/summary.tpl"}
        </div>
        {/if}
        <div class="margin-form">
            <input onclick="$(this).hide(); $('#ajax_running').show();" type="submit" class="button" name="saveproject{if isset($smarty.get.id_project)}next{/if}" value="{if !isset($smarty.get.step) OR $smarty.get.step != 'summary'}{l s='Next >' mod='textmaster'}{else}{l s='Finish' mod='textmaster'}{/if}" />
        </div>
    </form>
</div>