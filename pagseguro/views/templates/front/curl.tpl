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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2014 PrestaShop SA
*  @version  Release: $Revision: 6594 $
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if $version == 6}
    <style type="text/css" media="all">{literal}div#center_column{ width: 75%; }{/literal}</style>
{else if $version == 5}
    <style type="text/css" media="all">{literal}div#center_column{ width: 757px; }{/literal}</style>
{else if $version == 4}
    <style type="text/css" media="all">{literal}div#center_column{ width: 535px; }{/literal}</style>
{/if}

<link type="text/css" rel="stylesheet" href="{$css_version|escape:'none'}" />
<script type="text/javascript" src="{$module_dir|escape:'none'}assets/js/jquery.min.js"></script>

<div>
	<form class="psplugin" id="psplugin">
		<h1>
		        <img src="{$module_dir|escape:'none'}assets/images/logops_228x56.png" />
		</h1> 
		<div id="tabList">
			<div class="tabItem selected">
				<h2>cUrl.</h2>
				<p><small>Ops! Ocorreu um erro.</small></p>
				<div class="module_error error">CURL can't connect: {$err|escape:'none'}</div>
			</div>
		</div>
	</form>
</div>

<script type="text/javascript">
    {literal}

        jQuery( document ).ready(function() {
            jQuery('#content').removeClass("nobootstrap"); 
            jQuery('#content').addClass("nobootstrap-ps"); 
        });
    {/literal}
</script>