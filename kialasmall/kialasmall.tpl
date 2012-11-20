{*
* 2007-2011 PrestaShop
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
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 11467 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<style type="text/css">
{* Infile CSS because external CSS is not always retrieved with hook extra carrier *}
{literal}
	.picture img {width:100px;}
	.picture {float:left; margin:5px}
	.kiala_description {float:left; margin:10px; width:70%}
	.kiala_point {margin:10px}
	.point_selected {background:yellow}
	#map {margin: 10px 0 0 0}
{/literal}
</style>
<script type="text/javascript" src="{$module_dir}kialasmall.js"></script>
{if $ks_compatibility_mode}
	<script type="text/javascript" src="{$module_dir}kialasmall-1-4.js"></script>
{/if}
<script type="text/javascript">
	// 1.4 compatibility vars
	var KS_KIALA_MODULE_DIR = "{$ks_kiala_module_dir}";
	var KS_KIALA_TOKEN = "{$ks_kiala_token}";
	var KS_KIALA_ID_CARRIER = "{$ks_kiala_carrier_id}";
	var KS_PAGE_NAME = "{$ks_page_name}";
	var KS_IS_OPC = {$ks_is_opc};
</script>
{$ks_content}
