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

<style>
    #tabPane1 .tab
    {
        width: 95px;
    }
</style>
<script>
    $('document').ready(function(){
        $('#tabPane1').append('<div id="product-tab-content-ModuleGiveIt" class="tab-page"><h4 class="tab">{l s='Give.it' mod='giveit'}</h4></div>');
        $giveit_product_content = $('#giveit_product_content');
        $giveit_product_content.find('script').remove();
        $('#product-tab-content-ModuleGiveIt').append($giveit_product_content.html());
        $giveit_product_content.remove();
        
        $('#hideError').live('click', function(){
            $(this).parent().parent().hide().find('div').html('');
            return false;
        });
    });
</script>

<div id="giveit_product_content" style="display:none">
    {if isset($smarty.get.tab) && $smarty.get.tab == 'AdminCatalog' && isset($smarty.get.id_product)}
        {include file=$smarty.const._GIVEIT_TPL_DIR_|escape:'htmlall':'UTF-8'|cat:'admin/product.tpl'}
    {else}
        {capture assign=warning_message}
            {l s='You must save this product first' mod='giveit'}
        {/capture}
        {include file=$smarty.const._GIVEIT_TPL_DIR_|escape:'htmlall':'UTF-8'|cat:'admin/warnings.tpl' warnings=[$warning_message]}
    {/if}
</div>