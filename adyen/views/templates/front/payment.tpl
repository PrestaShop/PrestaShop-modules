{*
* Adyen Payment Module
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
*  @author Rik ter Beek <rikt@adyen.com>
*  @copyright  Copyright (c) 2013 Adyen (http://www.adyen.com)
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<p class="payment_module">
	<a class="adyen-logo-link" href="{$link->getModuleLink('adyen', 'payment')|escape:'htmlall':'UTF-8'}" title="{l s='Pay with Adyen' mod='adyen'}">
		<img class="adyen-logo" src="{$this_path|escape:'htmlall':'UTF-8'}/img/adyen.png" alt="{l s='Pay with Adyen' mod='adyen'}" />
		{l s='Pay with Adyen' mod='adyen'}
	</a>
</p>