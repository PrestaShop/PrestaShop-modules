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
<link href="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}textmaster/css/product.css" rel="stylesheet" type="text/css">
<script src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}textmaster/js/jquery.scrollTo.min.js" type="text/javascript"></script>
<style>
    #tabPane1 .tab
    {
        width: 95px;
    }
</style>
<script>
    $('document').ready(function(){
        $('#tabPane1').append('<div id="product-tab-content-ModuleTextmaster" class="tab-page"><h4 class="tab">{l s='TextMaster' mod='textmaster'}</h4></div>');
        $textmaster_product_content = $('#textmaster_product_content');
        $textmaster_product_content.find('script').remove();
        $('#product-tab-content-ModuleTextmaster').append($textmaster_product_content.html());
        $textmaster_product_content.remove();
        
        $('#hideError').live('click', function(){
            $(this).parent().parent().hide().find('div').html('');
            return false;
        });
    });
</script>

<div id="textmaster_product_content" style="display:none">
    {include file=$smarty.const.TEXTMASTER_TPL_DIR|cat:"admin/product.tpl"}
</div>