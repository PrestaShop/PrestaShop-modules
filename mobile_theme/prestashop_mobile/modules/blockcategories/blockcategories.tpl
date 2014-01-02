{*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if isset($block_category_mobile) && $block_category_mobile|@count}
<!-- Block categories mobile module -->
<div id="categories_block_mobile" class="block">
	<div class="block_content">
		<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="{$ps_mobile_styles.PS_MOBILE_THEME_LIST_HEADERS}">
		<li data-role="list-divider">{l s='Categories' mod='blockcategories'}</li>
		{foreach from=$block_category_mobile item=child name=blockCategTree}
			<li>
				<a href="{$child.link}" title="{$child.desc|escape:html:'UTF-8'}">{$child.name|escape:html:'UTF-8'}</a>
			</li>
		{/foreach}
		</ul>
	</div>
</div>
<!-- /Block categories module -->
{/if}