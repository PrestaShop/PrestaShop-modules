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

<script type="text/javascript">

	// Global JS Value
	var PS_MRData = {$MR_Data};
	
	// literal for smarty v2 compatibility
	$(document).ready(function() {literal}{{/literal}
		PS_MRObject.initFront();
	{literal}}{/literal});

</script>

{if $MR_Data.PS_VERSION >= '1.5' && !$MR_Data.carrier}
	{l s='Mondial relay can\'t fetch any replay point due to prestashop error' mod='mondialrelay'}
{/if}