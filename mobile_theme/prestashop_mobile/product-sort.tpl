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

{if isset($orderby) && isset($orderway)}
<!-- Sort products -->
{if isset($smarty.get.id_category) && $smarty.get.id_category}
	{assign var='request' value=$link->getPaginationLink('category', $category, false, true)}
{elseif isset($smarty.get.id_manufacturer) && $smarty.get.id_manufacturer}
	{assign var='request' value=$link->getPaginationLink('manufacturer', $manufacturer, false, true)}
{elseif isset($smarty.get.id_supplier) && $smarty.get.id_supplier}
	{assign var='request' value=$link->getPaginationLink('supplier', $supplier, false, true)}
{else}
	{assign var='request' value=$link->getPaginationLink(false, false, false, true)}
{/if}

<script type="text/javascript">
//<![CDATA[
{literal}
$(document).ready(function()
{
	$('#selectPrductSort li').click(function()
	{
		var requestSortProducts = '{/literal}{$request}{literal}';
		var splitData = $(this).attr('rel').split(':');
		document.location.href = requestSortProducts + ((requestSortProducts.indexOf('?') < 0) ? '?' : '&') + 'orderby=' + splitData[0] + '&orderway=' + splitData[1];
	});
});
//]]>
{/literal}
</script>

</div>
<form class="productsSortForm" action="{$request|escape:'htmlall':'UTF-8'}">
	<div data-role="footer" data-theme="{$ps_mobile_styles.PS_MOBILE_THEME_FILTERING_BAR}" style="width: 100%;">
		<div data-role="navbar">
			<ul id="selectPrductSort">
				{if !$PS_CATALOG_MODE}
					<li rel="price:asc" style="line-height: 20px;"><a{if $orderby == 'price' && $orderway == 'asc'} class="ui-btn-active"{/if}>{l s='Price'} ↑</a></li>
					<li rel="price:desc" style="line-height: 20px;"><a{if $orderby == 'price' && $orderway == 'desc'} class="ui-btn-active"{/if}>↓ {l s='Price'}</a></li>
				{/if}
				<li rel="name:asc" style="line-height: 20px;"><a{if $orderby == 'name' && $orderway == 'asc'} class="ui-btn-active"{/if}>{l s='A to Z'}</a></li>
				<li rel="name:desc" style="line-height: 20px;"><a{if $orderby == 'name' && $orderway == 'desc'} class="ui-btn-active"{/if}>{l s='Z to A'}</a></li>
				{if !$PS_CATALOG_MODE && isset($stock_management) && $stock_management}
					<li rel="quantity:desc" style="line-height: 20px;"><a{if $orderby == 'quantity' && $orderway == 'desc'} class="ui-btn-active"{/if}>{l s='In stock'}</a></li>
				{/if}
			</ul>
		</div>
	</div>
</form>
<br />
<div class="center_column" data-role="content">
{/if}
