{*
* 2007-2013 PrestaShop
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
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

   <fieldset style="margin-top:10px;">
		<legend>{l s='Ebay Data Usage' mod='ebay'}</legend>
		<label>{l s='Help us improve the eBay Module by sending anonymous usage stats' mod='ebay'} : </label>
		<div class="margin-form">
            <form action="#" method="POST">
                <input type="hidden" name="stats" value="0">
                <input type="submit" name="submitSave" value="No thanks">
            </form>
            <form action="#" method="POST">
                <input type="hidden" name="stats" value="1">
                <input type="submit" name="submitSave" value="I agree">
            </form>
		</div>
		<div style="clear:both;"></div>
    </fieldset>