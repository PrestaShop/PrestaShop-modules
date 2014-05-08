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

<link rel="stylesheet" type="text/css" href="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}giveit/css/backoffice.css" />
<link rel="stylesheet" type="text/css" href="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}giveit/css/admin.css" />
<link rel="stylesheet" type="text/css" href="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}giveit/css/module.css" />

{if isset($smarty.get.menu) && ($smarty.get.menu == 'configuration_category' || $smarty.get.menu == 'configuration_product')}
    <script src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}giveit/js/jquery.treeview-categories.js" type="text/javascript"></script>
    <script src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}giveit/js/admin-categories-tree.js" type="text/javascript"></script>
    <script src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}giveit/js/jquery.treeview-categories.edit.js" type="text/javascript"></script>
    <script src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}giveit/js/jquery.treeview-categories.async.js" type="text/javascript"></script>

    <link rel="stylesheet" type="text/css" href="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}giveit/css/jquery.treeview-categories.css" />
{/if}
{if (isset($smarty.get.controller) && $smarty.get.controller == 'AdminProducts' || isset($smarty.get.tab) && $smarty.get.tab == 'AdminCatalog') && isset($smarty.get.id_product) && $smarty.get.id_product}
    <script src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}giveit/js/product.js" type="text/javascript"></script>
    <script src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}giveit/js/jquery.scrollTo.min.js" type="text/javascript"></script>
{/if}