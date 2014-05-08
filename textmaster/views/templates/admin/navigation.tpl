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
<div class="toolbar-placeholder">
    <div class="toolbarBox toolbarHead">
        <ul class="cc_button">
            <li>
                <a id="new_project" href="{$new_project_link|escape:'htmlall':'UTF-8'}" class="toolbar_btn">
                    <span class="process-icon-new new"></span>
                    <div>{l s='New project' mod='textmaster'}</div>
                </a>
            </li>
            <li>
                <a id="translation_page" href="{$translation_page_url|escape:'htmlall':'UTF-8'}" class="toolbar_btn">
                    <span class="process-icon-translation translation"></span>
                    <div>{l s='Translation projects' mod='textmaster'}</div>
                </a>
            </li>
            <li>
                <a id="proofreading_page" href="{$proofreading_page_url|escape:'htmlall':'UTF-8'}" class="toolbar_btn">
                    <span class="process-icon-proofreading proofreading"></span>
                    <div>{l s='Proofreading projects' mod='textmaster'}</div>
                </a>
            </li>
            <li>
                <a id="settings_page" href="{$settings_page_url|escape:'htmlall':'UTF-8'}" class="toolbar_btn">
                    <span class="process-icon-settings settings"></span>
                    <div>{l s='Settings' mod='textmaster'}</div>
                </a>
            </li>
            <li>
                <a id="help_page" href="{$help_page_url|escape:'htmlall':'UTF-8'}" class="toolbar_btn">
                    <span class="process-icon-help help"></span>
                    <div>{l s='Help' mod='textmaster'}</div>
                </a>
            </li>
        </ul>
        <div class="pageTitle">
            <h3>
                <span id="current_obj" style="font-weight: normal;">
                    <span class="breadcrumb item-0 ">
                        {$module_display_name|escape:'htmlall':'UTF-8'}
                        {if isset($current_page_name)}
                            <img src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}textmaster/img/separator_breadcrumb.png" style="margin-right:5px" alt=">">
                            <span class="breadcrumb item-1">{$current_page_name|escape:'htmlall':'UTF-8'}</span>
                        {/if}
                        {if isset($current_inner_page_name) && $current_inner_page_name}
                            <img src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}textmaster/img/separator_breadcrumb.png" style="margin-right:5px" alt=">">
                            <span class="breadcrumb item-1">{$current_inner_page_name|escape:'htmlall':'UTF-8'}</span>
                        {/if}
                    </span>
                </span>
            </h3>
        </div>
    </div>
</div>