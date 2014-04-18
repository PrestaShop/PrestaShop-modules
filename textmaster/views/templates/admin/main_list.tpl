{*
* 2013 TextMaster
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to info@textmaster.com so we can send you a copy immediately.
*
* @author JSC INVERTUS www.invertus.lt <help@invertus.lt>
* @copyright 2013 TextMaster
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* International Registered Trademark & Property of TextMaster
*}
{literal}
    <script>
        $(function() {
            if ($(".datepicker").length > 0)
            {
                $(".datepicker").datepicker({
                    prevText: '',
                    nextText: '',
                    dateFormat: 'yy-mm-dd'
                });
            }
        });

        $(document).ready(function() {
            $('table.project .filter').keypress(function(event){
                formSubmit(event, 'submitFilterButtonproject')
            })
        });

    </script>

    <style>
        .table th a
        {
            text-decoration: none !important;
        }
    </style>
{/literal}
<form class="form" action="{$full_url|escape:'htmlall':'UTF-8'}#project" method="post">
<input type="hidden" value="0" name="submitFilterproject" id="submitFilterproject">
<table name="list_table" class="table_grid">
<tbody>
<tr>
    <td style="vertical-align: bottom;">
                        <span style="float: left;">
                            {if $page > 1}
                                <input type="image" src="../img/admin/list-prev2.gif" onclick="getE('submitFilterproject').value=1"/>&nbsp;
                                <input type="image" src="../img/admin/list-prev.gif" onclick="getE('submitFilterproject').value={$page|escape:'htmlall':'UTF-8' - 1}"/>
                            {/if}
                            {l s='Page' mod='textmaster'} <b>{$page|escape:'htmlall':'UTF-8'}</b> / {$total_pages|escape:'htmlall':'UTF-8'}
                            {if $page < $total_pages}
                                <input type="image" src="../img/admin/list-next.gif" onclick="getE('submitFilterproject').value={$page|escape:'htmlall':'UTF-8' + 1}"/>&nbsp;
                                <input type="image" src="../img/admin/list-next2.gif" onclick="getE('submitFilterproject').value={$total_pages|escape:'htmlall':'UTF-8'}"/>
                            {/if}
                            | {l s='Display' mod='textmaster'}
                            <select name="pagination" onchange="submit()">
                                {* Choose number of results per page *}
                                {foreach from=$pagination item=value}
                                    <option value="{$value|intval|escape:'htmlall':'UTF-8'}"{if $selected_pagination == $value} selected="selected" {elseif $selected_pagination == NULL && $value == $pagination[1]} selected="selected2"{/if}>{$value|intval|escape:'htmlall':'UTF-8'}</option>
                                {/foreach}
                            </select>
                            / {$list_total|escape:'htmlall':'UTF-8'} {l s='result(s)' mod='textmaster'}
                        </span>
                        <span style="float: right;">
                            <input type="submit" class="button" value="{l s='Reset' mod='textmaster'}" name="submitResetproject">
                            <input type="submit" class="button" value="{l s='Filter' mod='textmaster'}" name="submitFilter" id="submitFilterButtonproject">
                        </span>
        <span class="clear"></span>
    </td>
</tr>
<tr>
<td>
<table cellspacing="0" cellpadding="0" style="width: 100%; margin-bottom:10px;" class="table  project">
<colgroup>
    <col width="10px">
    <col width="20px">
    <col>
    <col width="125px">
    <col width="125px">
    <col width="125px">
    <col width="125px">
    <col width="75px">
    <col width="20px">
    <col width="52px">
</colgroup>
<thead>
<tr style="height: 40px" class="nodrag nodrop">
    <th class="center">
    </th>
    <th class="center">
                                    <span class="title_box">
                                        {l s='Id' mod='textmaster'}
                                    </span>
        <br>
        <a href="{$full_url|escape:'htmlall':'UTF-8'}&projectOrderby=id_project&projectOrderway=desc">
            {if isset($smarty.get.projectOrderby) && isset($smarty.get.projectOrderway) && $smarty.get.projectOrderby == 'id_project' && $smarty.get.projectOrderway == 'desc'}
                <img border="0" src="../img/admin/down_d.gif">
            {else}
                <img border="0" src="../img/admin/down.gif">
            {/if}
        </a>
        <a href="{$full_url|escape:'htmlall':'UTF-8'}&projectOrderby=id_project&projectOrderway=asc">
            {if isset($smarty.get.projectOrderby) && isset($smarty.get.projectOrderway) && $smarty.get.projectOrderby == 'id_project' && $smarty.get.projectOrderway == 'asc'}
                <img border="0" src="../img/admin/up_d.gif">
            {else}
                <img border="0" src="../img/admin/up.gif">
            {/if}
        </a>
    </th>
    <th>
									<span class="title_box">
										{l s='Name' mod='textmaster'}
									</span>
        <br>
        <a href="{$full_url|escape:'htmlall':'UTF-8'}&projectOrderby=name&projectOrderway=desc">
            {if isset($smarty.get.projectOrderby) && isset($smarty.get.projectOrderway) && $smarty.get.projectOrderby == 'name' && $smarty.get.projectOrderway == 'desc'}
                <img border="0" src="../img/admin/down_d.gif">
            {else}
                <img border="0" src="../img/admin/down.gif">
            {/if}
        </a>
        <a href="{$full_url|escape:'htmlall':'UTF-8'}&projectOrderby=name&amp;projectOrderway=asc">
            {if isset($smarty.get.projectOrderby) && isset($smarty.get.projectOrderway) && $smarty.get.projectOrderby == 'name' && $smarty.get.projectOrderway == 'asc'}
                <img border="0" src="../img/admin/up_d.gif">
            {else}
                <img border="0" src="../img/admin/up.gif">
            {/if}
        </a>
    </th>
    <th class="center">
									<span class="title_box">
										{l s='Language from' mod='textmaster'}
									</span>
        <br>
        <a href="{$full_url|escape:'htmlall':'UTF-8'}&projectOrderby=language_from&projectOrderway=desc">
            {if isset($smarty.get.projectOrderby) && isset($smarty.get.projectOrderway) && $smarty.get.projectOrderby == 'language_from' && $smarty.get.projectOrderway == 'desc'}
                <img border="0" src="../img/admin/down_d.gif">
            {else}
                <img border="0" src="../img/admin/down.gif">
            {/if}
        </a>
        <a href="{$full_url|escape:'htmlall':'UTF-8'}&projectOrderby=language_from&projectOrderway=asc">
            {if isset($smarty.get.projectOrderby) && isset($smarty.get.projectOrderway) && $smarty.get.projectOrderby == 'language_from' && $smarty.get.projectOrderway == 'asc'}
                <img border="0" src="../img/admin/up_d.gif">
            {else}
                <img border="0" src="../img/admin/up.gif">
            {/if}
        </a>
    </th>
    <th class="center">
									<span class="title_box">
										{l s='Language to' mod='textmaster'}
									</span>
        <br>
        <a href="{$full_url|escape:'htmlall':'UTF-8'}&projectOrderby=language_to&projectOrderway=desc">
            {if isset($smarty.get.projectOrderby) && isset($smarty.get.projectOrderway) && $smarty.get.projectOrderby == 'language_to' && $smarty.get.projectOrderway == 'desc'}
                <img border="0" src="../img/admin/down_d.gif">
            {else}
                <img border="0" src="../img/admin/down.gif">
            {/if}
        </a>
        <a href="{$full_url|escape:'htmlall':'UTF-8'}&projectOrderby=language_to&projectOrderway=asc">
            {if isset($smarty.get.projectOrderby) && isset($smarty.get.projectOrderway) && $smarty.get.projectOrderby == 'language_to' && $smarty.get.projectOrderway == 'asc'}
                <img border="0" src="../img/admin/up_d.gif">
            {else}
                <img border="0" src="../img/admin/up.gif">
            {/if}
        </a>
    </th>
    <th class="right">
									<span class="title_box">
										{l s='Creation date' mod='textmaster'}
									</span>
        <br>
        <a href="{$full_url|escape:'htmlall':'UTF-8'}&projectOrderby=date_add&projectOrderway=desc">
            {if isset($smarty.get.projectOrderby) && isset($smarty.get.projectOrderway) && $smarty.get.projectOrderby == 'date_add' && $smarty.get.projectOrderway == 'desc'}
                <img border="0" src="../img/admin/down_d.gif">
            {else}
                <img border="0" src="../img/admin/down.gif">
            {/if}
        </a>
        <a href="{$full_url|escape:'htmlall':'UTF-8'}&projectOrderby=date_add&projectOrderway=asc">
            {if isset($smarty.get.projectOrderby) && isset($smarty.get.projectOrderway) && $smarty.get.projectOrderby == 'date_add' && $smarty.get.projectOrderway == 'asc'}
                <img border="0" src="../img/admin/up_d.gif">
            {else}
                <img border="0" src="../img/admin/up.gif">
            {/if}
        </a>
    </th>
    <th class="right">
									<span class="title_box">
										{l s='Date of last modification' mod='textmaster'}
									</span>
        <br>
        <a href="{$full_url|escape:'htmlall':'UTF-8'}&projectOrderby=date_upd&projectOrderway=desc">
            {if isset($smarty.get.projectOrderby) && isset($smarty.get.projectOrderway) && $smarty.get.projectOrderby == 'date_upd' && $smarty.get.projectOrderway == 'desc'}
                <img border="0" src="../img/admin/down_d.gif">
            {else}
                <img border="0" src="../img/admin/down.gif">
            {/if}
        </a>
        <a href="{$full_url|escape:'htmlall':'UTF-8'}&projectOrderby=date_upd&projectOrderway=asc">
            {if isset($smarty.get.projectOrderby) && isset($smarty.get.projectOrderway) && $smarty.get.projectOrderby == 'date_upd' && $smarty.get.projectOrderway == 'asc'}
                <img border="0" src="../img/admin/up_d.gif">
            {else}
                <img border="0" src="../img/admin/up.gif">
            {/if}
        </a>
    </th>
    <th class="center">
									<span class="title_box">
										{l s='Status' mod='textmaster'}
									</span>
        <br>
        <a href="{$full_url|escape:'htmlall':'UTF-8'}&projectOrderby=status&projectOrderway=desc">
            {if isset($smarty.get.projectOrderby) && isset($smarty.get.projectOrderway) && $smarty.get.projectOrderby == 'status' && $smarty.get.projectOrderway == 'desc'}
                <img border="0" src="../img/admin/down_d.gif">
            {else}
                <img border="0" src="../img/admin/down.gif">
            {/if}
        </a>
        <a href="{$full_url|escape:'htmlall':'UTF-8'}&projectOrderby=status&projectOrderway=asc">
            {if isset($smarty.get.projectOrderby) && isset($smarty.get.projectOrderway) && $smarty.get.projectOrderby == 'status' && $smarty.get.projectOrderway == 'asc'}
                <img border="0" src="../img/admin/up_d.gif">
            {else}
                <img border="0" src="../img/admin/up.gif">
            {/if}
        </a>
    </th>
    <th class="center">
									<span class="title_box">
										{l s='Launch' mod='textmaster'}
									</span>
        <br>
        <a href="{$full_url|escape:'htmlall':'UTF-8'}&projectOrderby=show_launch_button&projectOrderway=desc">
            {if isset($smarty.get.projectOrderby) && isset($smarty.get.projectOrderway) && $smarty.get.projectOrderby == 'show_launch_button' && $smarty.get.projectOrderway == 'desc'}
                <img border="0" src="../img/admin/down_d.gif">
            {else}
                <img border="0" src="../img/admin/down.gif">
            {/if}
        </a>
        <a href="{$full_url|escape:'htmlall':'UTF-8'}&projectOrderby=show_launch_button&projectOrderway=asc">
            {if isset($smarty.get.projectOrderby) && isset($smarty.get.projectOrderway) && $smarty.get.projectOrderby == 'show_launch_button' && $smarty.get.projectOrderway == 'asc'}
                <img border="0" src="../img/admin/up_d.gif">
            {else}
                <img border="0" src="../img/admin/up.gif">
            {/if}
        </a>
    </th>
    <th class="center">
        {l s='Actions' mod='textmaster'}<br>&nbsp;
    </th>
</tr>
<tr style="height: 35px;" class="nodrag nodrop filter row_hover">
    <td class="center">

    </td>
    <td class="center">
        <input type="text" style="width:20px" value="{if isset($cookie_projectFilter_id_project)}{$cookie_projectFilter_id_project|escape:'htmlall':'UTF-8'}{/if}" name="projectFilter_id_project" class="filter">
    </td>
    <td>
        <input type="text" style="width:95%" value="{if isset($cookie_projectFilter_name)}{$cookie_projectFilter_name|escape:'htmlall':'UTF-8'}{/if}" name="projectFilter_name" class="filter">
    </td>
    <td class="center">
        <input type="text" style="width:125px" value="{if isset($cookie_projectFilter_language_from)}{$cookie_projectFilter_language_from|escape:'htmlall':'UTF-8'}{/if}" name="projectFilter_language_from" class="filter">
    </td>
    <td class="center">
        <input type="text" style="width:125px" value="{if isset($cookie_projectFilter_language_to)}{$cookie_projectFilter_language_to|escape:'htmlall':'UTF-8'}{/if}" name="projectFilter_language_to" class="filter">
    </td>
    <td class="right">
        {l s='From' mod='textmaster'} <input type="text" style="width:70px" value="{if isset($cookie_projectFilter_date_add_0)}{$cookie_projectFilter_date_add_0|escape:'htmlall':'UTF-8'}{/if}" name="projectFilter_date_add[0]" id="projectFilter_date_add_0" class="filter datepicker">
        <br>
        {l s='To' mod='textmaster'} <input type="text" style="width:70px" value="{if isset($cookie_projectFilter_date_add_1)}{$cookie_projectFilter_date_add_1|escape:'htmlall':'UTF-8'}{/if}" name="projectFilter_date_add[1]" id="projectFilter_date_add_1" class="filter datepicker">
    </td>
    <td class="right">
        {l s='From' mod='textmaster'} <input type="text" style="width:70px" value="{if isset($cookie_projectFilter_date_upd_0)}{$cookie_projectFilter_date_upd_0|escape:'htmlall':'UTF-8'}{/if}" name="projectFilter_date_upd[0]" id="projectFilter_date_upd_0" class="filter datepicker">
        <br>
        {l s='To' mod='textmaster'} <input type="text" style="width:70px" value="{if isset($cookie_projectFilter_date_upd_1)}{$cookie_projectFilter_date_upd_1|escape:'htmlall':'UTF-8'}{/if}" name="projectFilter_date_upd[1]" id="projectFilter_date_upd_1" class="filter datepicker">
    </td>

    <td class="center">
        <select style="width:75px" name="projectFilter_status" onchange="$('#submitFilterButtonproject').focus();$('#submitFilterButtonproject').click();">
            <option {if !isset($cookie_projectFilter_status)}selected="selected"{/if} value="">--</option>
            <option {if isset($cookie_projectFilter_status) && $cookie_projectFilter_status == 'in_creation'}selected="selected" {/if}value="in_creation">{l s='In creation' mod='textmaster'}</option>
            <option {if isset($cookie_projectFilter_status) && $cookie_projectFilter_status == 'in_progress'}selected="selected" {/if}value="in_progress">{l s='In progress' mod='textmaster'}</option>
            <option {if isset($cookie_projectFilter_status) && $cookie_projectFilter_status == 'in_review'}selected="selected" {/if}value="in_review">{l s='In review' mod='textmaster'}</option>
            <option {if isset($cookie_projectFilter_status) && $cookie_projectFilter_status == 'paused'}selected="selected" {/if}value="paused">{l s='Paused' mod='textmaster'}</option>
            <option {if isset($cookie_projectFilter_status) && $cookie_projectFilter_status == 'completed'}selected="selected" {/if}value="completed">{l s='Completed' mod='textmaster'}</option>
            <option {if isset($cookie_projectFilter_status) && $cookie_projectFilter_status == 'canceled'}selected="selected" {/if}value="canceled">{l s='Cancelled' mod='textmaster'}</option>
        </select>
    </td>
    <td class="center">
        --
    </td>
    <td class="center">--</td>
</tr>
</thead>
<tbody>
{section name=ii loop=$projects}
    {if $projects[ii].status=='in_creation'}
        {capture assign="status_readable"}{l s='In creation' mod='textmaster'}{/capture}
    {elseif $projects[ii].status=='in_progress'}
        {capture assign="status_readable"}{l s='In progress' mod='textmaster'}{/capture}
    {elseif $projects[ii].status=='in_review'}
        {capture assign="status_readable"}{l s='In review' mod='textmaster'}{/capture}
    {elseif $projects[ii].status=='paused'}
        {capture assign="status_readable"}{l s='Paused' mod='textmaster'}{/capture}
    {elseif $projects[ii].status=='completed'}
        {capture assign="status_readable"}{l s='Completed' mod='textmaster'}{/capture}
    {elseif $projects[ii].status=='canceled'}
        {capture assign="status_readable"}{l s='Cancelled' mod='textmaster'}{/capture}
    {else}
        {capture assign="status_readable"}{l s='In creation' mod='textmaster'}{/capture}
    {/if}
    <tr class="row_hover" id="tr_{$smarty.section.ii.index|escape:'htmlall':'UTF-8' + 1}_{$projects[ii].id_project|escape:'htmlall':'UTF-8'}_0">
        <td class="center">
        </td>
        <td onclick="document.location = '{$full_url|escape:'htmlall':'UTF-8'}&id_project={$projects[ii].id_project|escape:'htmlall':'UTF-8'}&viewproject'" class="pointer center">
            {$projects[ii].id_project|escape:'htmlall':'UTF-8'}
        </td>
        <td onclick="document.location = '{$full_url|escape:'htmlall':'UTF-8'}&id_project={$projects[ii].id_project|escape:'htmlall':'UTF-8'}&viewproject'" class="pointer">
            {$projects[ii].name|escape:'htmlall':'UTF-8'}
        </td>
        <td onclick="document.location = '{$full_url|escape:'htmlall':'UTF-8'}&id_project={$projects[ii].id_project|escape:'htmlall':'UTF-8'}&viewproject'" class="pointer center">
            {$projects[ii].language_from|escape:'htmlall':'UTF-8'}
        </td>
        <td onclick="document.location = '{$full_url|escape:'htmlall':'UTF-8'}&id_project={$projects[ii].id_project|escape:'htmlall':'UTF-8'}&viewproject'" class="pointer center">
            {$projects[ii].language_to|escape:'htmlall':'UTF-8'}
        </td>
        <td onclick="document.location = '{$full_url|escape:'htmlall':'UTF-8'}&id_project={$projects[ii].id_project|escape:'htmlall':'UTF-8'}&viewproject'" class="pointer right">
            {$projects[ii].date_add|escape:'htmlall':'UTF-8'}
        </td>
        <td onclick="document.location = '{$full_url|escape:'htmlall':'UTF-8'}&id_project={$projects[ii].id_project|escape:'htmlall':'UTF-8'}&viewproject'" class="pointer right">
            {$projects[ii].date_upd|escape:'htmlall':'UTF-8'}
        </td>
        <td onclick="document.location = '{$full_url|escape:'htmlall':'UTF-8'}&id_project={$projects[ii].id_project|escape:'htmlall':'UTF-8'}&viewproject'" class="pointer center">
            <img title="{$status_readable|escape:'UTF-8'}" alt="{$status_readable|escape:'UTF-8'}" src="../img/admin/../../modules/textmaster/img/status/{$projects[ii].status|escape:'htmlall':'UTF-8'}.png">
        </td>
        <td class="pointer center">
            {if $projects[ii].status == 'in_creation'}
                <a title="{l s='Launch' mod='textmaster'}" href="{$full_url|escape:'htmlall':'UTF-8'}&id_project={$projects[ii].id_project|escape:'htmlall':'UTF-8'}&launch_project">
                    <img src="../modules/textmaster/img/launch.png">
                </a>
            {else}
                --
            {/if}
        </td>
        <td style="white-space: nowrap;" class="center">
            <a onclick="if (confirm('{l s='Are You sure?' mod='textmaster'}')) document.location = '{$full_url|escape:'htmlall':'UTF-8'}&id_project={$projects[ii].id_project|escape:'htmlall':'UTF-8'}&duplicateproject'; else document.location = '{$full_url|escape:'htmlall':'UTF-8'}&id_project={$projects[ii].id_project|escape:'htmlall':'UTF-8'}&duplicateproject&noimage=1';" title="{l s='Duplicate' mod='textmaster'}" class="pointer">
                <img alt="{l s='Duplicate' mod='textmaster'}" src="../img/admin/duplicate.png">
            </a>
            <a title="{l s='View' mod='textmaster'}" href="{$full_url|escape:'htmlall':'UTF-8'}&id_project={$projects[ii].id_project|escape:'htmlall':'UTF-8'}&viewproject">
                <img alt="{l s='View' mod='textmaster'}" src="../img/admin/details.gif">
            </a>
            {if $projects[ii].status == 'in_creation'}
                <a title="{l s='Edit' mod='textmaster'}" class="edit" href="{$full_url|escape:'htmlall':'UTF-8'}&id_project={$projects[ii].id_project|escape:'htmlall':'UTF-8'}&updateproject">
                    <img alt="{l s='Edit' mod='textmaster'}" src="../img/admin/edit.gif">
                </a>
            {/if}
            {if $projects[ii].status == 'in_creation' || $projects[ii].status == 'paused'}
                <a title="{l s='Cancel' mod='textmaster'}" onclick="{literal}if (confirm('{l s='Are you sure?' mod='textmaster'}')){ return true; }else{ event.stopPropagation(); event.preventDefault();};{/literal}" class="delete" href="{$full_url|escape:'htmlall':'UTF-8'}&id_project={$projects[ii].id_project|escape:'htmlall':'UTF-8'}&deleteproject">
                    <img alt="{l s='Cancel' mod='textmaster'}" src="../img/admin/delete.gif">
                </a>
            {/if}
        </td>
    </tr>
{/section}
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</form>