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

<p>{l s='The payment for your order on' mod='adyen'} <span class="bold">{$shop_name|escape:'htmlall':'UTF-8'}</span> {l s='has been refused.' mod='adyen'}
	<br /><br />{l s='For any questions or for further information, please contact our' mod='adyen'} <a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}contact-form.php">{l s='customer support' mod='adyen'}</a>.
</p>
