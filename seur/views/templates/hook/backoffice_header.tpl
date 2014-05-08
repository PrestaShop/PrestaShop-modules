{*
 * 2007-2014 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2014 PrestaShop SA
 *  @version  Release: 0.4.4
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *}
<script src="{$smarty.const._PS_JS_DIR_|escape:'htmlall':'UTF-8'}jquery/jquery-ui-1.8.10.custom.min.js" type="text/javascript"></script>
<script src="{$smarty.const._PS_JS_DIR_|escape:'htmlall':'UTF-8'}jquery/jquery.fancybox-1.3.4.js" type="text/javascript"></script>
<link href="{$smarty.const._PS_CSS_DIR_|escape:'htmlall':'UTF-8'}jquery.fancybox-1.3.4.css" rel="stylesheet" type="text/css" />
<link href="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}seur/css/seur.css" rel="stylesheet" type="text/css" />

{if $tab == 'AdminSeur' || $tab == 'adminseur'}
	<script src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}seur/js/seurToolsAdmin.js" type="text/javascript"></script>
{/if}

{if $tab == 'AdminOrders' || $tab == 'adminorders'}
	<script src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}seur/js/seurToolsOrder.js" type="text/javascript"></script>
	<script src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}seur/js/html2canvas.js" type="text/javascript"></script>
	<script src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}seur/js/jquery.plugin.html2canvas.js" type="text/javascript"></script>
{/if}

{if Tools::getValue('configure') == 'seur' && $tab == 'adminmodules'}
	<script src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}seur/js/seurToolsConfig.js" type="text/javascript"></script>
{/if}