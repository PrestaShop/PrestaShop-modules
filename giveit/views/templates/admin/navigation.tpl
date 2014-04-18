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
{if isset($ps_16) && $ps_16}
    <nav class="navbar navbar-default" role="navigation">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse"
               data-target="#navbar-collapse" style="float: left;">
               <span class="sr-only"></span>
               <span class="icon-bar"></span>
               <span class="icon-bar"></span>
               <span class="icon-bar"></span>
            </button>
         </div>
         <div class="collapse navbar-collapse" id="navbar-collapse">
            <ul class="nav navbar-nav">
                {foreach $meniutabs key=numStep item=tab}
                    <li class="{if $tab.active}active{/if}">
                        <a id="{$tab.short|escape:'htmlall':'UTF-8'}" href="{$tab.href|escape:'htmlall':'UTF-8'}">
                            <span class="{$tab.imgclass|escape:'htmlall':'UTF-8'}" style="margin-right: 5px;">

                            </span>
                            {$tab.desc|escape:'htmlall':'UTF-8'}
                        </a>
                    </li>
                {/foreach}
            </ul>
        </div>
    </nav>
{else}
    <div class="toolbar-placeholder">
        <div class="toolbarBox toolbarHead">
            <ul class="cc_button">
                <li>
                    <a id="main_settings_page" href="{$module_link|escape:'htmlall':'UTF-8'}&menu=configuration" class="toolbar_btn">
                        <span class="process-icon-main_settings main_settings"></span>
                        <div>{l s='Main settings' mod='giveit'}</div>
                    </a>
                </li>
                <li>
                    <a id="category_settings_page" href="{$module_link|escape:'htmlall':'UTF-8'}&menu=configuration_category" class="toolbar_btn">
                        <span class="process-icon-category_settings category_settings"></span>
                        <div>{l s='Category settings' mod='giveit'}</div>
                    </a>
                </li>
                <li>
                    <a id="product_settings_page" href="{$module_link|escape:'htmlall':'UTF-8'}&menu=configuration_product" class="toolbar_btn">
                        <span class="process-icon-product_settings product_settings"></span>
                        <div>{l s='Product settings' mod='giveit'}</div>
                    </a>
                </li>
                <li>
                    <a id="shipping_prices_page" href="{$module_link|escape:'htmlall':'UTF-8'}&menu=shipping_prices" class="toolbar_btn">
                        <span class="process-icon-shipping_prices help"></span>
                        <div>{l s='Shipping prices' mod='giveit'}</div>
                    </a>
                </li>
                <li>
                    <a id="help_page" href="{$module_link|escape:'htmlall':'UTF-8'}&menu=help" class="toolbar_btn">
                        <span class="process-icon-help help"></span>
                        <div>{l s='Help' mod='giveit'}</div>
                    </a>
                </li>
            </ul>
            <div class="pageTitle">
                <h3>
                    <span id="current_obj" style="font-weight: normal;">
                        <span class="breadcrumb item-0 ">
                            {$module_display_name|escape:'htmlall':'UTF-8'}
                            {if isset($current_page_name)}
                                <img src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}giveit/img/separator_breadcrumb.png" style="margin-right:5px" alt=">">
                                <span class="breadcrumb item-1">{$current_page_name|escape:'htmlall':'UTF-8'}</span>
                            {/if}
                        </span>
                    </span>
                </h3>
            </div>
        </div>
    </div>
{/if}