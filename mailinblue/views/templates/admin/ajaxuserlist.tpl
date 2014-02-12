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
* @author PrestaShop SA <contact@prestashop.com>
* @copyright  2007-2014 PrestaShop SA
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
			{if $sourceInfo.id_customer!= "Nclient"}
				yes
			{else}
				No
			{/if}
			
		</td>
		<td>
			
            {foreach from=$smsdata key=jk item=smsInfo}
            	{if $jk == $sourceInfo.phone_mobile}
                	{$smsInfo}
                {/if}
            {/foreach}
			
		</td>
		<td class="tipTd">
			
			{assign var=emailid value=$sourceInfo.email}
			
			{if isset($data[$emailid]) && $data[$emailid] ===1}
				{assign var=pstatus value=1}
			{elseif isset($data[$emailid]) && $data[$emailid] ===0}
				{assign var=pstatus value=0}
			{else}
				{assign var=pstatus value=1}
			{/if}
			
			<a href="javascript:void(0)" class="ajax_contacts_href" email="{$sourceInfo.email}" status="{$pstatus}">
			{if $pstatus==1}
			<img class="toolTip1 imgstatus" title="{l s='Subscribe the contact' mod='sendinblue'}" id="ajax_contact_status_{$counter}" src="../img/admin/disabled.gif" />
			{else}
			<img class="toolTip1 imgstatus" title="{l s='Unsubscribe the contact' mod='sendinblue'}" id="ajax_contact_status_{$counter}" src="../img/admin/enabled.gif" />
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
					<li p='1' class='active'>{l s='First' mod='sendinblue'}</li>
				{elseif $first_btn}
					<li p='1' class='inactive'>{l s='First' mod='sendinblue'}</li>
				{/if}
	
				{if $previous_btn && $cur_page > 1}
					{assign var=pre value=$cur_page-1}
					<li p='{$pre}' class='active'>{l s='Previous' mod='sendinblue'}</li>
				{elseif $previous_btn}
					<li class='inactive'>{l s='Previous' mod='sendinblue'}</li>
				{/if}
	
				{section name=cu start=$start_loop loop=$end_loop+1 step=1}
					{if $cur_page == $smarty.section.cu.index}
						<li p='{$smarty.section.cu.index}' style='color:#fff;background-color:#000;' class='active'>{$smarty.section.cu.index}</li>
					{else}
						<li p='{$smarty.section.cu.index}' class='active'>{$smarty.section.cu.index}</li>
					{/if}
					
				{/section}
				
	
				{if $last_btn && $cur_page < $no_of_paginations}
					{assign var=nex value=$cur_page+1}
					<li p='{$nex}' class='active'>{l s='Next' mod='sendinblue'}</li>
				{elseif $next_btn}
					<li class='inactive'>{l s='Next' mod='sendinblue'}</li>
				{/if}
				
				{if $last_btn && $cur_page < $no_of_paginations}
					<li p='{$no_of_paginations}' class='active'>{l s='Last' mod='sendinblue'}</li>
				{elseif $last_btn}
					<li p='{$no_of_paginations}' class='inactive'>{l s='Last' mod='sendinblue'}</li>
				{/if}
			</ul>
		</div>
	</td>
</tr>
