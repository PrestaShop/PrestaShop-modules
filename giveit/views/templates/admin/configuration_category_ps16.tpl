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

<form id="configuration_form" action="{$saveAction|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data">
	<div id="configuration_form" class="panel">
		<h3>
			<i class="icon-cog"></i>
			{l s='Category settings' mod='giveit'}
		</h3>

		<div class="form-group">
			<div class="alert alert-info">
				{l s='Select categories for which you would like to enable or disable the Give.it button. This overrides the global settings.' mod='giveit'}
			</div>
		</div>

		<div class="form-group">
			{include file=$smarty.const._PS_BO_ALL_THEMES_DIR_|cat:Context::getContext()->employee->bo_theme|cat:'/template/helpers/tree/tree_categories.tpl'}
		</div>

		<div class="form-group">
			<p class="help-block">
				{l s='If category is unchecked then button will not be displayed in that category unless product has it\'s own give.it settings then it depends on those settings.' mod='giveit'}
			</p>
		</div>

		<div class="panel-footer">
			<button class="btn btn-default pull-right" name="saveCategorySettings" type="submit">
				<i class="process-icon-save"></i>
				{l s='Save' mod='giveit'}
			</button>
		</div>
	</div>
</form>