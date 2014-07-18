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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @version  Release: $Revision: 7732 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

        <form name="syspay_form" data-ajax="false"  action="{$syspay_link|escape:'htmlall':'UTF-8'}" method="POST">
                {foreach from=$syspay_params key=syspay_key item=syspay_value}
                        <input type="hidden" name="{$syspay_key|escape:'htmlall':'UTF-8'}" value="{$syspay_value|escape:'htmlall':'UTF-8'}" />
                {/foreach}
                <input type="submit" data-icon="arrow-r" data-iconpos="right"  value="Carte bancaire (avec Syspay)" />
        </form>