{*}
/*
* 2007-2013 Profileo
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to contact@profileo.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade Profileo to newer
* versions in the future. If you wish to customize Profileo for your
* needs please refer to http://www.profileo.com for more information.
*
*  @author Profileo <contact@profileo.com>
*  @copyright  2007-2013 Profileo
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of Profileo
*/
{*}
{if $widget1!=false}
	<div id="truspilot_widget1" class="truspilot_widget block {$position}">
		<div class="block_content">
			<div class="tp_-_box" data-tp-settings="domainId:{$tp_wg1_id_domaine}">
				<a href="http://{$tp_wg1_site_url}/review/{$tp_wg1_domaine}" rel="nofollow" hidden>{$tp_wg1_site_name} Avis</a>
			</div>
			{literal}<script type="text/javascript">
				(function () { var a = "https:" == document.location.protocol ? "https://ssl.trustpilot.com" : "http://s.trustpilot.com", b = document.createElement("script"); b.type = "text/javascript"; b.async = true; b.src = a + "/tpelements/tp_elements_all.js"; var c = document.getElementsByTagName("script")[0]; c.parentNode.insertBefore(b, c) })();
			</script>{/literal}
		</div>
	</div>
{/if}

{if $widget2!=false}
	<div id="truspilot_widget2" class="truspilot_widget block {$position}">
		<div class="block_content">
			<div class="tp_-_box" data-tp-settings="domainId:{$tp_wg2_id_domaine}">
				<a href="http://{$tp_wg2_site_url}/review/{$tp_wg2_domaine}" rel="nofollow" hidden>{$tp_wg2_site_name} Avis</a>
			</div>
			{literal}<script type="text/javascript">
				(function () { var a = "https:" == document.location.protocol ? "https://ssl.trustpilot.com" : "http://s.trustpilot.com", b = document.createElement("script"); b.type = "text/javascript"; b.async = true; b.src = a + "/tpelements/tp_elements_all.js"; var c = document.getElementsByTagName("script")[0]; c.parentNode.insertBefore(b, c) })();
			</script>{/literal}
		</div>
	</div>
{/if}
