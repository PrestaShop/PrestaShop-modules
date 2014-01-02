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

<!DOCTYPE html>
<html>
	<head>
		<title>{$meta_title|escape:'htmlall':'UTF-8'}</title>
{if isset($meta_description) AND $meta_description}
		<meta name="description" content="{$meta_description|escape:html:'UTF-8'}" />
{/if}
{if isset($meta_keywords) AND $meta_keywords}
		<meta name="keywords" content="{$meta_keywords|escape:html:'UTF-8'}" />
{/if}
		<meta charset="utf-8">
		<meta name="generator" content="PrestaShop" />
		<link rel="icon" type="image/vnd.microsoft.icon" href="{$img_ps_dir}favicon.ico" />
		<link rel="shortcut icon" type="image/x-icon" href="{$img_ps_dir}favicon.ico" />
		<meta name="robots" content="{if isset($nobots)}no{/if}index,follow" />
		<link href="{$css_dir}global.css" rel="stylesheet" type="text/css" />
		<link href="{$css_dir}jquery.mobile.min.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="{$base_dir}js/jquery/jquery.min.js"></script>
		<script type="text/javascript" src="{$js_dir}jquery.mobile.min.js"></script>
		<meta name="viewport" content="width=device-width, initial-scale=1" />
	</head>
	<body>
		<div data-role="page">
			<div data-role="header" data-theme="{$ps_mobile_styles.PS_MOBILE_THEME_HEADER_FOOTER}">
				<h1>{$shop_name}</h1>
			</div>
			<div data-role="content">
				<div class="ui-overlay-shadow ui-body-e ui-corner-all" style="padding: 12px; margin: 15px 0;">
						<p>{l s='You cannot access our store from your country. We apologize for the inconvenience.'}</p>
				</div>
			</div>
			<div class="footer" data-role="footer" data-theme="{$ps_mobile_styles.PS_MOBILE_THEME_HEADER_FOOTER}">
			  <p>
				<a>{l s='View Full Site'}</a><br />
				{l s='Powered By'} <a href="http://www.prestashop.com">PrestaShop</a>
			  </p>
			</div>
		</div>
	</body>
</html>