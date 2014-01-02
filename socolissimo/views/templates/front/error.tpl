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
{if isset($error_list)}
	<div class="alert error">
		{l s='Socolissimo errors list:' mod='socolissimo'}
		<ul style="margin-top: 10px;">
			{foreach from=$error_list item=current_error}
				<li>{$current_error|escape:'htmlall':'UTF-8'}</li>
			{/foreach}
		</ul>
	</div>
{/if}
{if isset($so_url_back)}
	<a href="{$so_url_back}step=2&cgv=1" class="button_small" title="{l s='Back' mod='socolissimo'}">Back</a>
{/if}
