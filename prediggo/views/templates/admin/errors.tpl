{*
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
*  @author Prediggo SA <info@prediggo.com> / CeboWeb <dev@ceboweb.com>
*  @copyright  2008-2012 Prediggo SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Prediggo SA
*}

{if sizeof($aPrediggoWarnings)}
	{foreach from=$aPrediggoWarnings item="sPrediggoWarning"}
		<div class="warn">{$sPrediggoWarning|escape:'UTF-8'}</div>
	{/foreach}
{/if}
{if sizeof($aPrediggoConfirmations)}
	{foreach from=$aPrediggoConfirmations item="sPrediggoConfirmation"}
		<div class="conf confirm">{$sPrediggoConfirmation|escape:'UTF-8'}</div>
	{/foreach}
{/if}
{if sizeof($aPrediggoErrors)}
	{foreach from=$aPrediggoErrors item="sPrediggoError"}
		<div class="error">{$sPrediggoError|escape:'UTF-8'}</div>
	{/foreach}
{/if}