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
<table width="100%;" cellspacing="0" cellpadding="0" id="" class="table">
	<thead>
	<tr>
		<th style="width:20%;">{l s='Author' mod='textmaster'}</th>
		<th style="width:15%">{l s='Date' mod='textmaster'}</th>
		<th>{l s='Message' mod='textmaster'}</th>
	</tr>
	</thead>
	<tbody>
        {if $messages}
        {foreach from=$messages item=message}
		<tr>	
            <td>{$message.author_ref|escape:'htmlall':'UTF-8'}</td>
            <td>{dateFormat date=$message.created_at.full|date_format:"Y-m-d H:i:s" full=true}</td>
            <td>{$message.message|escape:'htmlall':'UTF-8'}</td>
        </tr>
        {/foreach}
        {else}
            <tr><td colspan="10" class="center">{l s='There are no messages for this document' mod='textmaster'}</td></tr>
        {/if}
	</tbody>
</table>