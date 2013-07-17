{*
* 2007-2012 PrestaShop
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
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 6594 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{*
{l s='back' mod='prediggo'}
{l s='first' mod='prediggo'}
{l s='last' mod='prediggo'}
{l s='next' mod='prediggo'}
*}

{if sizeof($aChangePageLinks)}
	<div id="pagination" class="pagination">
		<ul class="pagination clear">
			{foreach from=$aChangePageLinks item="oChangePageOption"}
				{assign var="sChangePageLinksLabel" value=$oChangePageOption->getLabel()}
				<li {if $sChangePageLinksLabel == 'back'}id="pagination_previous"{elseif $sChangePageLinksLabel == 'next'}id="pagination_next"{elseif $sChangePageLinksLabel == 'first'}id="pagination_first"{elseif $sChangePageLinksLabel == 'last'}id="pagination_last"{/if} {if $oSearchStatistics->getCurrentPageNumber()|intval == $sChangePageLinksLabel|intval}class="current"{/if}>
					{if $oSearchStatistics->getCurrentPageNumber()|intval == $sChangePageLinksLabel|intval}
						<span>{l s=$sChangePageLinksLabel mod='prediggo'}</span>
					{else}
						<a href="?q={$sPrediggoQuery}&refineOption={$oChangePageOption->getSearchRefiningOption()}{if !$bRewriteEnabled}&fc=module&module=prediggo&controller=search{/if}">{l s=$sChangePageLinksLabel mod='prediggo'}</a>
					{/if}
				</li>
			{/foreach}
		</ul>
	</div>
{/if}