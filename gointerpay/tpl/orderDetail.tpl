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

<div>
	<p style="line-height: 50px; font-size: 12px; border: 1px solid rgb(3,49,98); padding: 15px;">
		<img src="{$module_dir}img/logo-interpay.png" alt="" style="vertical-align: middle; margin-right: 15px;" width="135" height="50" /> 
		{l s='You can consult in real-time the status of this order on the GoInterpay website:' mod='gointerpay'} 
		<b><a target="_blank" href="{$interpay_link}">{l s='Order #' mod='gointerpay'}{$interpay_order|escape:htmlall:'UTF-8'}</a></b>
	</p>
</div><br />