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
<p>{l s='Your order on' mod='seurcashondelivery'} 
        <span class="bold">{$shop_name|escape:'htmlall':'UTF-8'}</span> 
        {l s='is complete.' mod='seurcashondelivery'}
	<br /><br />
	{l s='You have chosen the cash on delivery method by SEUR.' mod='seurcashondelivery'}
	<br /><br /><span class="bold">{l s='Your order will be sent very soon.' mod='seurcashondelivery'}</span>
	<br /><br />{l s='For any questions or for further information, please contact our.' mod='seurcashondelivery'} 
        <a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}contact-form.php">{l s='customer support' mod='seurcashondelivery'}</a>
</p>