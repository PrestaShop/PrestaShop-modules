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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2014 PrestaShop SA
 *  @license	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *}
 <fieldset class="gk-main-wrapper">
 	<legend><img src="../modules/globkurier/img/gk.png" />{l s='GlobKurier Quick Order' mod='globkurier'}</legend>
 	<p style="font-size:16px;">{l s='Order number' mod='globkurier'}: <b>{$gk_number|escape:'htmlall':'UTF-8'}</b></p>
 	<p>{l s='The order has been sent to the globkurier, if you select a payment method: bank transfer or online payment, please log in to www.globkurier.pl and make payment.' mod='globkurier'}</p>
 	<p style="text-decoration:underline;"><a target="_blank" href="https://www.globkurier.pl/mojekonto/platnosci.html">{l s="https://www.globkurier.pl/mojekonto/platnosci.html" mod='globkurier'}</a></p>
 	<p style="margin-top:10px;">{l s='Śledź przesylkę' mod='globkurier'}: <a style="text-decoration:underline;" target="_blank" href="https://www.globkurier.pl/sledz-przesylke/{$gk_number|escape:'htmlall':'UTF-8'}" />https://www.globkurier.pl/sledz-przesylke/{$gk_number|escape:'htmlall':'UTF-8'}</a></p>
 	<p style="margin-top:20px;">{l s='Have a question? presta@globkurier.pl' mod='globkurier'}</p>
 </fieldset>
 