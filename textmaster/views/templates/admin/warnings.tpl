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
{if count($warnings)}
    <div class="warning">
        {if count($warnings) == 1}
            {$warnings[0]|escape:'htmlall':'UTF-8'}
        {else}
            {l s='%d warnings' mod='textmaster' sprintf=$warnings|count}
            <br/>
            <ol>
                {foreach from=$warnings item=warning}
                    <li>{$warning|escape:'htmlall':'UTF-8'}</li>
                {/foreach}
            </ol>
        {/if}
    </div>
{/if}