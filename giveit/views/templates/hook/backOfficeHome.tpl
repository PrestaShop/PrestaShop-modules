{*
* 2013 Give.it
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to support@give.it so we can send you a copy immediately.
*
* @author JSC INVERTUS www.invertus.lt <help@invertus.lt>
* @copyright 2013 Give.it
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* International Registered Trademark & Property of Give.it
*}
<script>
$(document).ready(function(){
    {if isset($ps14)}
        $("#give_it").insertBefore($('#content .warning:first'));
    {else}
        $("#give_it").prependTo($("#column_left"));
    {/if}
});
</script>
<div id="give_it">
    <div class="warning warn">
        <b>{l s='Warning in give.it module:' mod='giveit'}</b><br />
        {if $notice_type == 'invalid_api_keys'}
            {l s='The Give.it module has not been configured correctly: Please ' mod='giveit'} <a href="javascript:window.location='{$module_url|escape:'htmlall':'UTF-8'}'">{l s='check your API settings' mod='giveit'}</a>
        {elseif $notice_type == 'default_currency_changed'}
            {l s='Shop default currency was changed, but shipping prices are still saved in old currency.' mod='giveit'} <a href="javascript:window.location='{$module_url|escape:'htmlall':'UTF-8'}&menu=shipping_prices'">{l s='Edit shipping prices list' mod='giveit'}</a>
        {/if}
    </div>
</div>
