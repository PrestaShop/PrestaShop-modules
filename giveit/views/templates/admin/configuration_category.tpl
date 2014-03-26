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
<form id="configuration_form" class="defaultForm" action="{$saveAction|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data">
    <fieldset id="category_settings">
        <p class="Bloc">
            {l s='Select categories for which you would like to enable or disable the Give.it button. This overrides the global settings.' mod='giveit'}
        </p>
	<br>
        <legend>
            <img src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}giveit/img/settings.png" alt="{l s='Settings |' mod='giveit'}" />
            {l s='Category settings' mod='giveit'}
        </legend>
		{include file=$smarty.const._GIVEIT_TPL_DIR_|escape:'htmlall':'UTF-8'|cat:'admin/form_category.tpl'}
        <div class="clear"></div>
        <p class="preference_description">
            {l s='If category is unchecked then button will not be displayed in that category unless product has it\'s own give.it settings then it depends on those settings.' mod='giveit'}
        </p>
        <div class="clear"></div>
        <div class="margin-form">
            <input type="submit" class="button" name="saveCategorySettings" value="{l s='Save' mod='giveit'}" />
        </div>
    </fieldset>
</form>
