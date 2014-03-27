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
<style>
    .innactive-link
    {
        cursor: default;
    }
</style>

<script>
    $(document).ready(function(){
        $('.innactive-link').click(function(){
            return false;
        });
    });
</script>
<div>
    <div class="productTabs">
        <ul class="tab">
            <li class="tab-row">
                <a href="{$module_link|escape:'htmlall':'UTF-8'}&step=products" class="{if !isset($smarty.get.id_project) and (!$project_step or $steps['products']-1>$steps[$project_step])}innactive-link {/if}tab-page{if !isset($smarty.get.step) OR (isset($smarty.get.step) AND $smarty.get.step == 'products')} selected{/if}">
                    1. {l s='Select products' mod='textmaster'}
                </a>
            </li>
            <li class="tab-row">
                <a href="{$module_link|escape:'htmlall':'UTF-8'}&step=properties" class="{if !isset($smarty.get.id_project) and (!$project_step or $steps['properties']-1>$steps[$project_step])}innactive-link {/if}tab-page{if isset($smarty.get.step) AND $smarty.get.step == 'properties'} selected{/if}">
                    2. {l s='Project properties' mod='textmaster'}
                </a>
            </li>
            <li class="tab-row">
                <a href="{$module_link|escape:'htmlall':'UTF-8'}&step=summary" class="{if !isset($smarty.get.id_project) and (!$project_step or $steps['summary']-1>$steps[$project_step])}innactive-link {/if}tab-page{if isset($smarty.get.step) AND $smarty.get.step == 'summary'} selected{/if}">
                    3. {l s='Summary' mod='textmaster'}
                </a>
            </li>
        </ul>
    </div>
</div>