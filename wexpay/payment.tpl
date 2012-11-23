{*
* 2007-2010 PrestaShop 
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
*  @author Prestashop SA <contact@prestashop.com>
*  @copyright  2007-2010 Prestashop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*}

<p class="payment_module">
	<a href="javascript:document.wexpay_form.submit();" title="{l s='Pay with weXpay' mod='wexpay'}" style="height:48px">
		<img width="100px" src="{$module_dir}wexpay.png" alt="{l s='Wexpay logo' mod='wexpay'}" />{l s='Pay with weXpay' mod='wexpay'}
		<div style="clear:both;height:0;line-height:0">&nbsp;</div>
	</a>
	<div style="clear:both;height:0;line-height:0">&nbsp;</div>
</p>
<form name="wexpay_form" action="https://paiements.wexpay.fr" method="post">
	<input type="hidden" name="merchant_id" value="{$merchant_id|escape:'htmlall':'UTF-8'}" />
	<input type="hidden" name="ref_order" value="{$ref_order}" />
	<input type="hidden" name="amount" value="{$amount}" />
	<input type="hidden" name="urlNotification" value="{$urlNotification}" />
	<input type="hidden" name="urlError" value="{$urlError}" />
	<input type="hidden" name="urlReturn" value="{$urlReturn}" />
</form>