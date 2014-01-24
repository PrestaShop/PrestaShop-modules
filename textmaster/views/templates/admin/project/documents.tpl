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
{foreach from=$documents item=document name=doc}
    {if $document.status == 'completed'}
        {$is_completed = "true"}
    {/if}
{/foreach}

<table name="list_table" class="table_grid">
    <tbody>
        <tr>
            <td style="border:none;">
                <table cellspacing="0" cellpadding="0" style="width: 100%; margin-bottom:10px;" class="table document">
                    <colgroup>
                        {if isset($smarty.get.viewproject)}
                        <col width="10px">
                        <col width="150px">
                        <col width="20px">
                        <col width="50px">
                        <col width="50px">
                        {if isset($is_completed) && $is_completed}
                            <col width="50px">
                        {/if}
                        {else}
                        <col width="10px">
                        <col>
                        <col width="125px">
                        <col width="125px">
                        {/if}
                    </colgroup>
                    <thead>
                        {if isset($smarty.get.viewproject)}
                        <tr style="height: 40px" class="nodrag nodrop">
                            <th class="center"></th>
                            <th><span class="title_box">{l s='Title' mod='textmaster'}</span></th>
                            <th class="center"><span class="title_box">{l s='Word count' mod='textmaster'}</span></th>
                            <th class="center"><span class="title_box">{l s='Status' mod='textmaster'}</span></th>
                            <th class="center"><span class="title_box">{l s='Approve' mod='textmaster'}</span></th>
                            {if isset($is_completed) && $is_completed}
                                <th class="center"><span class="title_box">{l s='Update product' mod='textmaster'}</span></th>
                            {/if}
                        </tr>
                        {else}
                        <tr style="height: 40px" class="nodrag nodrop">
                            <th class="center"></th>
                            <th><span class="title_box">{l s='Title' mod='textmaster'}</span></th>
                            <th class="center"><span class="title_box">{l s='Word count' mod='textmaster'}</span></th>
                            <th class="center"><span class="title_box">{l s='Price' mod='textmaster'}</span></th>
                        </tr>
                        {/if}
                    </thead>

                    <tbody>
                        {foreach from=$documents item=document name=doc}
                            {if isset($smarty.get.viewproject)}
                            <tr class=" row_hover" id="tr_{$smarty.foreach.doc.iteration|escape:'htmlall':'UTF-8'}_{$document.id|escape:'htmlall':'UTF-8'}_0">
                                <td class="center"></td>
                                <td>{$document.title|escape:'htmlall':'UTF-8'}</td>
                                <td class="center">{$document.word_count|escape:'htmlall':'UTF-8'}</td>
                                <td class="center"><img title="{$statuses[$document.status]|escape:'htmlall':'UTF-8'}" alt="{$statuses[$document.status]|escape:'htmlall':'UTF-8'}" src="../modules/textmaster/img/status/{$document.status|escape:'htmlall':'UTF-8'}.png"></td>
                                <td class="center">
                                {if $document.status == 'in_review'}
                                    <input type="button" class="button" style="cursor: pointer" onclick="approveDocument('{$document.id_product|escape:'htmlall':'UTF-8'}', '{$document.id|escape:'htmlall':'UTF-8'}', '{l s='Product fields will be automatically updated.' mod='textmaster' js=1}')" title="{l s='Approve' mod='textmaster'}" value="{l s='Approve' mod='textmaster'}" />
                                {elseif $document.status == 'completed'}
                                    <img class="approve" title="{l s='Approved' mod='textmaster'}" src="../modules/textmaster/img/approve.png" />
                                {/if}
                                </td>
                                {if $document.status == 'completed'}
                                    <td class="center">
                                        <input type="button" class="button" style="cursor: pointer" onclick="updateDocument('{$document.id_product|escape:'htmlall':'UTF-8'}', '{$document.id|escape:'htmlall':'UTF-8'}', '{l s='Product fields will be automatically updated.' mod='textmaster' js=1}')" title="{l s='Update product' mod='textmaster'}" value="{l s='Update product' mod='textmaster'}" />
                                    </td>
                                {/if}
                            </tr>
                            {else}
                            <tr class="row_hover">
                                <td class="center"></td>
                                <td class="">{$document.title|escape:'htmlall':'UTF-8'}</td>
                                <td class="center">{$document.word_count|escape:'htmlall':'UTF-8'}</td>
                                <td class="center">{$document.price|escape:'htmlall':'UTF-8'}</td>
                            </tr>
                            {/if}
                        {/foreach}
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>