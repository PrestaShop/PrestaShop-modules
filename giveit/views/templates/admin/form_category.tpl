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

{assign var=categories value=$category_values}
{if count($categories) && isset($categories)}
	<script type="text/javascript">
		var inputName = '{$categories.input_name|escape:'htmlall':'UTF-8'}';
		var use_radio = {if $categories.use_radio}1{else}0{/if};
		var selectedCat = {if !empty($categories.selected_cat)}'{$categories.selected_cat.0|escape:'htmlall':'UTF-8'}'{else}''{/if};
		var selectedLabel = '{$categories.trads.selected|escape:'htmlall':'UTF-8'}';
		var home = '{$categories.trads.Root.name|escape:'htmlall':'UTF-8'}';
		var use_radio = {if $categories.use_radio}1{else}0{/if};
		var use_context = {if isset($categories.use_context)}1{else}0{/if};
		$(document).ready(function(){
			buildTreeView(use_context);
		});
	</script>

	<div class="category-filter">
		<span><a href="#" id="collapse_all" >{$categories.trads['Collapse All']|escape:'htmlall':'UTF-8'}</a>
		 |</span>
		 <span><a href="#" id="expand_all" >{$categories.trads['Expand All']|escape:'htmlall':'UTF-8'}</a>
		{if !$categories.use_radio}
		 |</span>
		 <span></span><a href="#" id="check_all" >{$categories.trads['Check All']|escape:'htmlall':'UTF-8'}</a>
		 |</span>
		 <span></span><a href="#" id="uncheck_all" >{$categories.trads['Uncheck All']|escape:'htmlall':'UTF-8'}</a></span>
		 {/if}
		{if $categories.use_search}
			<span style="margin-left:20px">
				{$categories.trads.search|escape:'htmlall':'UTF-8'} :
				<form method="post" id="filternameForm">
					<input type="text" name="search_cat" id="search_cat">
				</form>
			</span>
		{/if}
	</div>

	{assign var=home_is_selected value=false}

	{foreach $categories.selected_cat AS $cat}
		{if is_array($cat)}
			{if $cat.id_category != $categories.trads.Root.id_category}
				<input {if in_array($cat.id_category, $categories.disabled_categories)}disabled="disabled"{/if} type="hidden" name="{$categories.input_name|escape:'htmlall':'UTF-8'}" value="{$cat.id_category|escape:'htmlall':'UTF-8'}" >
			{else}
				{assign var=home_is_selected value=true}
			{/if}
		{else}
			{if $cat != $categories.trads.Root.id_category}
				<input {if in_array($cat, $categories.disabled_categories)}disabled="disabled"{/if} type="hidden" name="{$categories.input_name|escape:'htmlall':'UTF-8'}" value="{$cat|escape:'htmlall':'UTF-8'}" >
			{else}
				{assign var=home_is_selected value=true}
			{/if}
		{/if}
	{/foreach}
	<ul id="categories-treeview" class="filetree">
		<li id="{$categories.trads.Root.id_category|escape:'htmlall':'UTF-8'}" class="hasChildren">
			<span class="folder">
				{*if $categories.top_category->id != $categories.trads.Root.id_category*}
					<input type="{if !$categories.use_radio}checkbox{else}radio{/if}"
							name="{$categories.input_name|escape:'htmlall':'UTF-8'}"
							value="{$categories.trads.Root.id_category|escape:'htmlall':'UTF-8'}"
							{if $home_is_selected}checked{/if}
							onclick="clickOnCategoryBox($(this));" />
						<span class="category_label">{$categories.trads.Root.name|escape:'htmlall':'UTF-8'}</span>
				{*else}
					&nbsp;
				{/if*}
			</span>
			<ul>
				<li><span class="placeholder">&nbsp;</span></li>
		  	</ul>
		</li>
	</ul>
	{if $categories.use_radio}
	<script type="text/javascript">
		searchCategory();
	</script>
	{/if}
{/if}
<div id="products_list_to_select"></div>