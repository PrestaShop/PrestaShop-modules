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
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<link href="{$new_base_dir}css/style.css" rel="stylesheet" type="text/css" media="all" />

{if $MR_overload_current_jquery}
	{include file="$MR_local_path/tpl/jquery-overload.tpl"}
{/if}

<script type="text/javascript">
	var PS_MR_ACCOUNT_SET = {if $MR_account_set}true{else}false{/if};
	var _PS_MR_MODULE_DIR_ = "{$new_base_dir}";
	var mrtoken = "{$MR_token}";
</script>

<script type="text/javascript" src="{$new_base_dir}js/mondialrelay.js"></script>
