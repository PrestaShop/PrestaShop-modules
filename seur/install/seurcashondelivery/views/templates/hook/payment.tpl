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
<p class="payment_module" id="reembolsoSEUR"{if $visible neq 1} style="display:none"{/if}>
	<a href="{$enlace|escape:'htmlall':'UTF-8'}" title="{l s='Cash on delivery by SEUR' mod='seurcashondelivery'}">
		<img src="{$ruta|escape:'htmlall':'UTF-8'}img/logoSeur.png" alt="{l s='Cash on delivery by SEUR' mod='seurcashondelivery'}" width="86" height="49" border="0" />
		{l s='Cash on delivery by SEUR' mod='seurcashondelivery'}
		{l s='Cost:' mod='seurcashondelivery'} {convertPrice price=$coste|floatval}
		{l s='Fee:' mod='seurcashondelivery'} {convertPrice price=$cargo}
		<strong>{l s='Total:' mod='seurcashondelivery'} {convertPrice price=$total|floatval}</strong>
	</a>
</p>