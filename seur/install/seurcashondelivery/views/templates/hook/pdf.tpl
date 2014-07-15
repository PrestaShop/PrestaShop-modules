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
<table style="width: 357px;">
        <tr>
            <td style="width: 27px;"></td>
            <td style="background-color: #4d4d4d; color: #ffffff"><strong>{l s='Concept' mod='seurcashondelivery'}</strong></td>
            <td style="background-color: #4d4d4d; color: #ffffff"></td>
            <td style="background-color: #4d4d4d; color: #ffffff"><strong>{l s='Quantity' mod='seurcashondelivery'}</strong></td>
        </tr>
        <tr>
            <td style="width: 27px;"></td>
            <td style="background-color: #dddddd;">{l s='Cash on delivery by SEUR' mod='seurcashondelivery'}</td>
            <td style="background-color: #dddddd;"></td>
            <td style="background-color: #dddddd;">{$reembolso_cargo|escape:'htmlall':'UTF-8'}<sup>*</sup></td>
        </tr>
        <br />
        <tr>
            <td style="width: 27px;height: 35px;margin-top: 5px;"></td>
            <td colspan="3"><p style="font-size:20px;margin:0;padding:0;"><sup>*</sup> {l s='This fee is included in delivery price.' mod='seurcashondelivery'}</p><br /></td>
        </tr>
</table>