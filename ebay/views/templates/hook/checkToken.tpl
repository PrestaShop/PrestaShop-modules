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
<script>
	function checkToken()
	{ldelim}
		$.ajax({ldelim}
			url: '{$url}',
			cache: false,
			success: function(data)
			{ldelim}
				if (data == 'OK')
					window.location.href = '{$window_location_href}';
				else
					setTimeout ("checkToken()", 5000);
			{rdelim}
		{rdelim});
	{rdelim}
	checkToken();
</script>
	<p align="center" class="warning"><a href="{$request_uri}&action=logged&relogin=1" target="_blank" class="button">{l s='If you\'ve been logged out of eBay and not redirected to the configuration page, please click here' mod='ebay'}</a></p>
	<p align="center"><img src="{$path}views/img/loading.gif" alt="{l s='Loading' mod='ebay'}" title="{l s='Loading' mod='ebay'}" /></p>
	<p align="center">{l s='Once you sign in via the new eBay window, the module will automatically finish the installation' mod='ebay'}</p>
