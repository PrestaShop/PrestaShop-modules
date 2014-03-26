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

<form id="configuration_form" action="{$saveAction|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data">
	<div id="product_settings" class="panel">
		<h3>
			<i class="icon-cog"></i>
			{l s='Product settings' mod='giveit'}
		</h3>
		
		<div class="form-group">
			<div class="alert alert-info">
				{l s='Here you can disable or explicitly enable the Give.it button for a specific product and its combinations. This can also be set in the settings page of the product itself' mod='giveit'}
			</div>
		</div>
		
		<div class="form-group">
			{include file=$smarty.const._PS_BO_ALL_THEMES_DIR_|cat:Context::getContext()->employee->bo_theme|cat:'/template/helpers/tree/tree_categories.tpl'}
		</div>
	</div>
	
	<div id="product_settings" class="panel">
		<div id="configuration_products_table_container">
			
		</div>
	</div>
</form>