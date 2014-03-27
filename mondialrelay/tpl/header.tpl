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

{include file="$MR_local_path/tpl/jquery-overload.tpl"}

<link href="{$new_base_dir}css/style.css" rel="stylesheet" type="text/css" media="all" />
<script type="text/javascript">
	// Global JS Value
	var _PS_MR_MODULE_DIR_ = "{$new_base_dir}";
	var mrtoken = "{$MRToken}";
	var PS_MROPC = {$one_page_checkout};
	var PS_MRTranslationList = [];
	var PS_MRCarrierMethodList = [];
	var PS_MRSelectedRelayPoint = {literal}{{/literal}'carrier_id': 0, 'relayPointNum': 0{literal}}{/literal};
	var PS_MRWarningMessage = "{$warning_message}";
	
	PS_MRTranslationList['Select'] = "{l s='Select' mod='mondialrelay'}";
	PS_MRTranslationList['Selected'] = "{l s='Selected' mod='mondialrelay'}";
	PS_MRTranslationList['errorSelection'] = "{l s='Please choose a relay point' mod='mondialrelay'}";
	PS_MRTranslationList['openingRelay'] = "{l s='Opening hours' mod='mondialrelay'}";
	PS_MRTranslationList['moreDetails'] = "{l s='More details' mod='mondialrelay'}";
</script>

<script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=false"></script>
<script type="text/javascript" src="{$new_base_dir}js/mondialrelay.js"></script>
<script type="text/javascript" src="{$new_base_dir}js/gmap.js"></script>
