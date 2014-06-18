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
*  @version  Release: $Revision: 10285 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="bootstrap">
{if $MR_error_list|count}
<div class="alert error">
	{$MR_error_list|count} {l s='error(s)' mod='mondialrelay'}
	<ul>
		{foreach from=$MR_error_list key=error_num item=error_message}
			<li>{$error_message|escape:'htmlall':'UTF-8'}</li>
		{/foreach}
	</ul>
</div>
	{elseif $MR_form_action.type|strlen != 0}
<div class="conf confirm alert alert-success">
	{$MR_form_action.message_success|escape:'htmlall':'UTF-8'}
</div>
{/if}
</div>