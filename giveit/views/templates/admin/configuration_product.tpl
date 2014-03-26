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
<script>
    var id_shop = "{$id_shop|escape:'htmlall':'UTF-8'}";
    var id_lang = "{$id_lang|escape:'htmlall':'UTF-8'}";
    var give_it_token = "{$give_it_token|escape:'htmlall':'UTF-8'}";
    var give_it_ajax_url = "{$smarty.const._GIVEIT_AJAX_URL_|escape:'htmlall':'UTF-8'}";
    var success_message = "{l s='Setting updated successfully' mod='giveit' js='1'}";
    var error_message = "{l s='Could not update setting' mod='giveit' js='1'}";
</script>
{if isset($ps14)}
    <div id="ajax_running" style="display: none;">
        <img alt="" src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}giveit/img/ajax-loader-yellow.gif">
        {l s='Loading...' mod='giveit'}
    </div>
{/if}
<form id="configuration_form" class="defaultForm" action="{$saveAction|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data">
    <fieldset id="product_settings">
        <p class="Bloc">
            {l s='Here you can disable or explicitly enable the Give.it button for a specific product and its combinations. This can also be set in the settings page of the product itself' mod='giveit'}
        </p>
	<br>
        <legend>
            <img src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}giveit/img/settings.png" alt="{l s='Settings |' mod='giveit'}" />
            {l s='Product settings' mod='giveit'}
        </legend>
		{include file=$smarty.const._GIVEIT_TPL_DIR_|escape:'htmlall':'UTF-8'|cat:'admin/form_category.tpl'}
        <div class="clear"></div>
        <div id="configuration_products_table_container">
            
        </div>
    </fieldset>
</form>
