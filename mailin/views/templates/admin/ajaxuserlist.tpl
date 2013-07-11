{*
* 2007-2013 PrestaShop
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
* @author PrestaShop SA <contact@prestashop.com>
* @copyright  2007-2013 PrestaShop SA
* @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}

{assign var=counter value=0}

{foreach from=$result item=sourceInfo}
	{assign var=counter value=$counter+1}
	<tr>
		<td>
			<div style="word-wrap:break-word; width:350px">{$sourceInfo.email}</div>
		</td>
		{assign var="emailtest" value=$sourceInfo.email}
		<td>
			{if $sourceInfo.table_type=="customer_table"}
				yes
			{else}
				No
			{/if}
		</td>
		<td class="tipTd">
			{if isset($data.{$sourceInfo.email}) && $data.{$sourceInfo.email} ===1}
				{assign var=pstatus value=1}
			{elseif isset($data.{$sourceInfo.email}) && $data.{$sourceInfo.email} ===0}
				{assign var=pstatus value=0}
			{else}
				{assign var=pstatus value=1}
			{/if}
			<a href="javascript:void(0)" class="ajax_contacts_href" email="{$sourceInfo.email}" status="{$pstatus}">
			{if $pstatus==1}
			<img class="toolTip1 imgstatus" title="{l s='Subscribe the contact' mod='mailin'}" id="ajax_contact_status_{$counter}" src="../img/admin/disabled.gif" />
			{else}
			<img class="toolTip1 imgstatus" title="{l s='Unsubscribe the contact' mod='mailin'}" id="ajax_contact_status_{$counter}" src="../img/admin/enabled.gif" />
			{/if}
			</a>
		</td>
		<td>
			{if $sourceInfo.newsletter==1}
			<img	class="imgstatus" src="../img/admin/enabled.gif" />
			{else}
			<img  class="imgstatus" src="../img/admin/disabled.gif" />
			{/if}
		</td>
	</tr>
{/foreach}

<tr>
	<td colspan='3'>
		<div class='pagination'>
			<ul>
				{if $first_btn && $cur_page > 1}
					<li p='1' class='active'>{l s='First' mod='mailin'}</li>
				{elseif $first_btn}
					<li p='1' class='inactive'>{l s='First' mod='mailin'}</li>
				{/if}
	
				{if $previous_btn && $cur_page > 1}
					{assign var=pre value=$cur_page -1}
					<li p='{$pre}' class='active'>{l s='Previous' mod='mailin'}</li>
				{elseif $previous_btn}
					<li class='inactive'>{l s='Previous' mod='mailin'}</li>
				{/if}
	
				{for $foo=$start_loop to $end_loop}
					{if $cur_page == $foo}
						<li p='{$foo}' style='color:#fff;background-color:#000;' class='active'>{$foo}</li>
					{else}
						<li p='{$foo}' class='active'>{$foo}</li>
					{/if}
				{/for}
	
				{if $last_btn && $cur_page < $no_of_paginations}
					{assign var=nex value=$cur_page + 1}
					<li p='{$nex}' class='active'>{l s='Next' mod='mailin'}</li>
				{elseif $next_btn}
					<li class='inactive'>{l s='Next' mod='mailin'}</li>
				{/if}
				
				{if $last_btn && $cur_page < $no_of_paginations}
					<li p='{$no_of_paginations}' class='active'>{l s='Last' mod='mailin'}</li>
				{elseif $last_btn}
					<li p='{$no_of_paginations}' class='inactive'>{l s='Last' mod='mailin'}</li>
				{/if}
			</ul>
		</div>
	</td>
</tr>
