{**
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2014 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *
 *}

<div id="register-now">
	<fieldset id="xxx">
		<legend>
			<img src="../modules/prestastats/img/icon.png" alt="Register" width="16" height="16">
			{l s='Register' mod='prestastats'}
		</legend>
		{l s='When you have saved your details above in the Settings panel, you simply click on "Activate Your New PrestaStats Account" button below, Signup and your shop is automatically setup for you in your PrestaStats Dashboard.' mod='prestastats'}<br /><br />
		<a href="{$url|escape:'htmlall':'UTF-8'}" class="button {$disabled|escape:'htmlall':'UTF-8'}" target="_blank">
			{l s='Activate your New PrestaStats Account' mod='prestastats'} </a>
		<br/>
		<br/>
		{l s='If you have already registered an account, click' mod='prestastats'}
		<a href="{$lurl|escape:'htmlall':'UTF-8'}" class="button" target="_blank">{l s='here' mod='prestastats'}</a> {l s='to Login' mod='prestastats'}
	</fieldset>
</div>